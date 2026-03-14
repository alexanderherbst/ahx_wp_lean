<?php

// Theme-Support aktivieren
function ahx_lean_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('menus');
    load_theme_textdomain('ahx_wp_lean', get_stylesheet_directory() . '/languages');
    register_nav_menus([
        'main-menu' => __('Hauptmenü', 'ahx_wp_lean'),
    ]);
}
add_action('after_setup_theme', 'ahx_lean_theme_setup');

// Styles und Scripts laden
function ahx_lean_enqueue_assets() {
    $style_path = get_stylesheet_directory() . '/style.css';
    $script_path = get_stylesheet_directory() . '/script.js';
    $style_ver = file_exists($style_path) ? filemtime($style_path) : false;
    $script_ver = file_exists($script_path) ? filemtime($script_path) : false;
    wp_enqueue_style('ahx-style', get_stylesheet_uri(), [], $style_ver);
    wp_enqueue_script('ahx-script', get_template_directory_uri() . '/script.js', [], $script_ver, true);
}
add_action('wp_enqueue_scripts', 'ahx_lean_enqueue_assets');

// Menü: Login/Logout/Profil an `main-menu` anhängen (Barrierefreiheit & WP-Nav kompatibel)
function ahx_nav_menu_items($items, $args) {
    if (isset($args->theme_location) && $args->theme_location === 'main-menu') {
        if (is_user_logged_in()) {
            $logout_url = wp_logout_url(get_permalink());
            $edit_profile_url = get_edit_profile_url(get_current_user_id());
            $items .= '<li class="menu-item profile"><a href="' . esc_url($edit_profile_url) . '">' . esc_html__('Profil bearbeiten', 'ahx_wp_lean') . '</a></li>';
            $items .= '<li class="menu-item logout"><a href="' . esc_url($logout_url) . '">' . esc_html__('Abmelden', 'ahx_wp_lean') . '</a></li>';
        } else {
            $login_url = wp_login_url(get_permalink());
            $items .= '<li class="menu-item login"><a href="' . esc_url($login_url) . '">' . esc_html__('Anmelden', 'ahx_wp_lean') . '</a></li>';
        }
    }
    return $items;
}
add_filter('wp_nav_menu_items', 'ahx_nav_menu_items', 10, 2);

// Fallback: Seitenliste (verschachtelt) wenn kein Menü angelegt ist
function ahx_page_menu_fallback($args) {
    $menu_items = wp_list_pages([
        'title_li' => '',
        'echo' => 0,
        'depth' => 2,
        'sort_column' => 'menu_order,post_title',
    ]);

    if ($menu_items) {
        echo '<ul class="menu-list" id="primary-menu">' . $menu_items . '</ul>';
    }
}

if (file_exists(get_stylesheet_directory() . '/images/custom-logo.png')) {
    // Login-Logo anpassen
    function my_custom_login_logo() {
        echo '
        <style type="text/css">
            #login h1 a {
                background-image: url(' . get_stylesheet_directory_uri() . '/images/custom-logo.png);
                background-size: contain;
                width: 100%;
                height: 80px;
            }
        </style>';
    }
    add_action('login_head', 'my_custom_login_logo');
}

// Link-Ziel ändern
function my_custom_login_logo_url() {
    return home_url(); // oder individuelle URL
}
add_filter('login_headerurl', 'my_custom_login_logo_url');

// Tooltip beim Hover
function my_custom_login_logo_url_title() {
    return 'Zurück zur Startseite';
}
add_filter('login_headertext', 'my_custom_login_logo_url_title');
