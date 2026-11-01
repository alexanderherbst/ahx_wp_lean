<?php
if (PHP_SAPI !== 'cli') exit;
$load = getenv('AHX_WORDPRESS_LOAD');
if (!$load || !is_file($load)) throw new RuntimeException('Set AHX_WORDPRESS_LOAD.');
require $load;
if (!function_exists('ahx_lean_color_mode')) require dirname(__DIR__) . '/functions.php';

$admins = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID']);
if (!$admins) throw new RuntimeException('Existing administrator required.');
$user_id = (int) $admins[0];
$original_user = get_current_user_id();
$original_post = $_POST;
$stored = '';
$writes = 0;
$fail_write = false;
$checks = 0;
$assert = static function($condition, $message) use (&$checks) {
    ++$checks;
    if (!$condition) throw new RuntimeException($message);
};
// Intercept only the theme preference: no existing user data is changed.
$read = static function($value, $id, $key) use ($user_id, &$stored) {
    return $id === $user_id && $key === 'ahx_lean_color_mode' ? [$stored] : $value;
};
$write = static function($value, $id, $key, $mode) use ($user_id, &$stored, &$writes, &$fail_write) {
    if ($id !== $user_id || $key !== 'ahx_lean_color_mode') return $value;
    ++$writes;
    if ($fail_write) return false;
    $stored = $mode;
    return true;
};
$die_handler = static function() {
    return static function($message) {
        throw new RuntimeException($message instanceof WP_Error ? $message->get_error_message() : $message);
    };
};
add_filter('get_user_metadata', $read, 10, 3);
add_filter('update_user_metadata', $write, 10, 4);
add_filter('wp_die_handler', $die_handler);

try {
    wp_set_current_user(0);
    $assert(ahx_lean_color_mode() === 'auto', 'Guests must use the system setting.');
    wp_set_current_user($user_id);
    $assert(ahx_lean_color_mode() === 'auto', 'Missing preference must use the system setting.');
    $stored = 'invalid';
    $assert(ahx_lean_color_mode() === 'auto', 'Legacy invalid metadata must not reach HTML.');
    foreach (['auto', 'light', 'dark'] as $mode) {
        $_POST = ['ahx_lean_color_mode' => $mode,
            'ahx_lean_color_mode_nonce' => wp_create_nonce('ahx_lean_color_mode_' . $user_id)];
        $errors = new WP_Error();
        ahx_lean_validate_color_mode($errors, true, (object) ['ID' => $user_id]);
        $assert(!$errors->has_errors(), 'Valid preference rejected.');
        ahx_lean_save_color_mode($user_id);
        $assert($stored === $mode && ahx_lean_color_mode() === $mode, 'Preference must persist and resolve.');
        $before = $writes;
        ahx_lean_save_color_mode($user_id);
        $assert($writes === $before, 'Unchanged preference must not be treated as a failed write.');
    }
    $before = $writes;
    foreach (['invalid', ['dark'], 'DARK', ' dark'] as $invalid) {
        $_POST['ahx_lean_color_mode'] = $invalid;
        $errors = new WP_Error();
        ahx_lean_validate_color_mode($errors, true, (object) ['ID' => $user_id]);
        $assert($errors->get_error_code() === 'ahx_lean_color_mode_invalid', 'Invalid input needs an explicit error.');
    }
    $_POST['ahx_lean_color_mode'] = 'light';
    $_POST['ahx_lean_color_mode_nonce'] = 'forged';
    $errors = new WP_Error();
    ahx_lean_validate_color_mode($errors, true, (object) ['ID' => $user_id]);
    $assert($errors->has_errors(), 'Forged nonce accepted.');
    try {
        ahx_lean_save_color_mode($user_id);
        throw new LogicException('Invalid save was not rejected.');
    } catch (RuntimeException $error) {
        $assert($writes === $before, 'Invalid save changed metadata.');
    }
    $_POST['ahx_lean_color_mode_nonce'] = wp_create_nonce('ahx_lean_color_mode_' . $user_id);
    wp_set_current_user(0);
    $errors = new WP_Error();
    ahx_lean_validate_color_mode($errors, true, (object) ['ID' => $user_id]);
    $assert($errors->has_errors(), 'Unauthorized profile update accepted.');
    wp_set_current_user($user_id);
    $_POST['ahx_lean_color_mode_nonce'] = wp_create_nonce('ahx_lean_color_mode_' . $user_id);
    $fail_write = true;
    try {
        ahx_lean_save_color_mode($user_id);
        throw new LogicException('Database failure was not surfaced.');
    } catch (RuntimeException $error) {
        $assert($stored === 'dark' && $writes === $before + 1, 'Failed save must preserve the preference.');
    }
    ob_start();
    ahx_lean_color_mode_profile((object) ['ID' => $user_id]);
    $html = ob_get_clean();
    $assert(str_contains($html, 'value="dark"  selected='), 'Profile must reflect the saved selection.');
    foreach (['show_user_profile', 'edit_user_profile', 'personal_options_update', 'edit_user_profile_update'] as $hook) {
        $callback = str_ends_with($hook, 'update') ? 'ahx_lean_save_color_mode' : 'ahx_lean_color_mode_profile';
        $assert(has_action($hook, $callback) !== false, 'Profile hook missing: ' . $hook);
    }
    $_POST = [];
    ahx_lean_save_color_mode($user_id);
    $assert($stored === 'dark', 'Unrelated profile updates must preserve the preference.');
    echo "Passed: $checks color-mode checks (defaults, profile, persistence, validation, nonce, permissions, failures).\n";
} finally {
    remove_filter('get_user_metadata', $read, 10);
    remove_filter('update_user_metadata', $write, 10);
    remove_filter('wp_die_handler', $die_handler);
    wp_set_current_user($original_user);
    $_POST = $original_post;
}
