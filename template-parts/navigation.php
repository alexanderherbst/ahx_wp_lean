<?php
$menu_items = wp_list_pages([
    'title_li' => '',
    'echo' => 0,
    'depth' => 2,
    'sort_column' => 'menu_order,post_title',
]);

$menu = '<ul class="menu-list">' . $menu_items;

if (is_user_logged_in()) {
    $logout_url = wp_logout_url(get_permalink()); // URL zum Abmelden
    $edit_profile_url = get_edit_profile_url(get_current_user_id()); // URL zum Profil bearbeiten
    // "Profil bearbeiten" und "Abmelden"-Link als letzte Listenpunkte anhängen
    $menu = preg_replace(
        '/<\/ul>$/',
        '<li class="menu-item profile"><a href="' . esc_url($edit_profile_url) . '">Profil bearbeiten</a></li>
         <li class="menu-item logout"><a href="' . esc_url($logout_url) . '">Abmelden</a></li></ul>',
        $menu
    );
} else {
    $login_url = wp_login_url(get_permalink()); // URL zum Anmelden
    // "Anmelden"-Link als letzten Listenpunkt anhängen
    $menu = preg_replace(
        '/<\/ul>$/',
        '<li class="menu-item login"><a href="' . esc_url($login_url) . '">Anmelden</a></li></ul>',
        $menu
    );
}

echo $menu;
?>
