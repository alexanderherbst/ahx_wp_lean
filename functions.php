<?php

// Theme-Support aktivieren
function ahx_lean_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('menus');
    register_nav_menus([
        'main-menu' => __('Hauptmenü', 'ahx_wp_lean'),
    ]);
}
add_action('after_setup_theme', 'ahx_lean_theme_setup');

// Styles und Scripts laden
function ahx_lean_enqueue_assets() {
    wp_enqueue_style('ahx-style', get_stylesheet_uri());
    wp_enqueue_script('ahx-script', get_template_directory_uri() . '/script.js', [], false, true);
}
add_action('wp_enqueue_scripts', 'ahx_lean_enqueue_assets');

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
