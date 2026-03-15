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

// Beim Theme-Aktivieren: Default-Menu anlegen und Location zuweisen
function ahx_create_default_menu_on_activation() {
    // Wenn bereits ein Menü zur Location gesetzt ist, nichts tun
    if ( has_nav_menu('main-menu') ) {
        return;
    }

    $menu_name = __('Hauptmenü', 'ahx_wp_lean');
    $existing = wp_get_nav_menu_object($menu_name);

    if (!$existing) {
        $menu_id = wp_create_nav_menu($menu_name);
        if (!is_wp_error($menu_id)) {
            // Startseite als erster Menüpunkt
            wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title' => __('Startseite', 'ahx_wp_lean'),
                'menu-item-url' => home_url('/'),
                'menu-item-status' => 'publish'
            ));

            // Menu-Location zuweisen
            $locations = get_theme_mod('nav_menu_locations');
            if (!is_array($locations)) {
                $locations = array();
            }
            $locations['main-menu'] = $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }
    } else {
        // Falls ein Menü mit dem Namen existiert, Location sicherstellen
        $menu_id = $existing->term_id;
        $locations = get_theme_mod('nav_menu_locations');
        if (!is_array($locations)) {
            $locations = array();
        }
        $locations['main-menu'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }
}
add_action('after_switch_theme', 'ahx_create_default_menu_on_activation');

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

// Seitentitel pro Seite ein-/ausblenden
function ahx_add_page_title_meta_box() {
    add_meta_box(
        'ahx_page_title_visibility',
        __('Seitentitel', 'ahx_wp_lean'),
        'ahx_render_page_title_meta_box',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'ahx_add_page_title_meta_box');

function ahx_render_page_title_meta_box($post) {
    wp_nonce_field('ahx_save_page_title_visibility', 'ahx_page_title_visibility_nonce');
    $hide_title = get_post_meta($post->ID, '_ahx_hide_page_title', true);
    ?>
    <label for="ahx_hide_page_title">
        <input
            type="checkbox"
            id="ahx_hide_page_title"
            name="ahx_hide_page_title"
            value="1"
            <?php checked($hide_title, '1'); ?>
        />
        <?php esc_html_e('Seitentitel auf dieser Seite ausblenden', 'ahx_wp_lean'); ?>
    </label>
    <?php
}

function ahx_save_page_title_visibility($post_id) {
    if (!isset($_POST['ahx_page_title_visibility_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['ahx_page_title_visibility_nonce'], 'ahx_save_page_title_visibility')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    $hide_title = isset($_POST['ahx_hide_page_title']) ? '1' : '0';
    update_post_meta($post_id, '_ahx_hide_page_title', $hide_title);
}
add_action('save_post_page', 'ahx_save_page_title_visibility');

function ahx_should_show_page_title($post_id = 0) {
    $post_id = $post_id ? $post_id : get_the_ID();
    if (!$post_id) {
        return true;
    }

    return get_post_meta($post_id, '_ahx_hide_page_title', true) !== '1';
}

function ahx_sanitize_checkbox($value) {
    return !empty($value) ? 1 : 0;
}

function ahx_get_hosting_provider_options() {
    return [
        '' => __('Bitte auswählen', 'ahx_wp_lean'),
        'ionos' => 'IONOS',
        'strato' => 'STRATO',
        'all_inkl' => 'ALL-INKL',
        'hetzner' => 'Hetzner',
        'netcup' => 'netcup',
        'dogado' => 'dogado',
        'mittwald' => 'Mittwald',
        'host_europe' => 'Host Europe',
        'manitu' => 'manitu',
        'contabo' => 'Contabo',
        'other' => __('Sonstiger', 'ahx_wp_lean'),
    ];
}

function ahx_sanitize_hosting_provider_select($value) {
    $value = sanitize_key($value);
    $allowed = array_keys(ahx_get_hosting_provider_options());
    return in_array($value, $allowed, true) ? $value : '';
}

function ahx_show_custom_hosting_provider_input() {
    return get_theme_mod('ahx_privacy_hosting_provider_select', '') === 'other';
}

function ahx_get_selected_hosting_provider_label() {
    $selected = get_theme_mod('ahx_privacy_hosting_provider_select', '');
    $selected = ahx_sanitize_hosting_provider_select($selected);

    if ($selected === 'other') {
        return sanitize_text_field(get_theme_mod('ahx_privacy_hosting_provider_custom', ''));
    }

    $options = ahx_get_hosting_provider_options();
    if ($selected !== '' && isset($options[$selected])) {
        return $options[$selected];
    }

    // Backward compatibility for older free-text setting.
    return sanitize_text_field(get_theme_mod('ahx_privacy_hosting_provider', ''));
}

function ahx_get_analytics_provider_options() {
    return [
        'matomo' => 'Matomo',
        'google_analytics' => 'Google Analytics',
        'etracker' => 'etracker',
        'piwik_pro' => 'Piwik PRO',
        'adobe_analytics' => 'Adobe Analytics',
        'plausible' => 'Plausible Analytics',
        'fathom' => 'Fathom Analytics',
        'mixpanel' => 'Mixpanel',
        'posthog' => 'PostHog',
        'hotjar' => 'Hotjar',
        'other' => __('Sonstiger', 'ahx_wp_lean'),
    ];
}

function ahx_show_custom_analytics_provider_input() {
    return (bool) get_theme_mod('ahx_privacy_analytics_provider_other', 0);
}

function ahx_get_selected_analytics_provider_label() {
    $selected_providers = [];
    $options = ahx_get_analytics_provider_options();

    foreach ($options as $key => $label) {
        if ($key === 'other') {
            continue;
        }
        if ((bool) get_theme_mod('ahx_privacy_analytics_provider_' . $key, 0)) {
            $selected_providers[] = $label;
        }
    }

    if ((bool) get_theme_mod('ahx_privacy_analytics_provider_other', 0)) {
        $custom = sanitize_text_field(get_theme_mod('ahx_privacy_analytics_provider_custom', ''));
        if ($custom !== '') {
            $selected_providers[] = $custom;
        }
    }

    if (empty($selected_providers)) {
        $legacy_select = sanitize_key(get_theme_mod('ahx_privacy_analytics_provider_select', ''));
        if ($legacy_select !== '' && isset($options[$legacy_select])) {
            if ($legacy_select === 'other') {
                $legacy_custom = sanitize_text_field(get_theme_mod('ahx_privacy_analytics_provider_custom', ''));
                if ($legacy_custom !== '') {
                    $selected_providers[] = $legacy_custom;
                }
            } else {
                $selected_providers[] = $options[$legacy_select];
            }
        }

        $legacy_text = sanitize_text_field(get_theme_mod('ahx_privacy_analytics_provider', ''));
        if ($legacy_text !== '') {
            $selected_providers[] = $legacy_text;
        }
    }

    $selected_providers = array_unique(array_filter($selected_providers));
    return implode(', ', $selected_providers);
}

function ahx_get_maps_provider_options() {
    return [
        'google_maps' => 'Google Maps',
        'openstreetmap' => 'OpenStreetMap',
        'mapbox' => 'Mapbox',
        'here' => 'HERE',
        'bing_maps' => 'Bing Maps',
        'tomtom' => 'TomTom Maps',
        'arcgis' => 'ArcGIS',
        'maptiler' => 'MapTiler',
        'thunderforest' => 'Thunderforest',
        'carto' => 'CARTO',
        'other' => __('Sonstiger', 'ahx_wp_lean'),
    ];
}

function ahx_show_custom_maps_provider_input() {
    return (bool) get_theme_mod('ahx_privacy_maps_provider_other', 0);
}

function ahx_get_selected_maps_provider_label() {
    $selected_providers = [];
    $options = ahx_get_maps_provider_options();

    foreach ($options as $key => $label) {
        if ($key === 'other') {
            continue;
        }
        if ((bool) get_theme_mod('ahx_privacy_maps_provider_' . $key, 0)) {
            $selected_providers[] = $label;
        }
    }

    if ((bool) get_theme_mod('ahx_privacy_maps_provider_other', 0)) {
        $custom = sanitize_text_field(get_theme_mod('ahx_privacy_maps_provider_custom', ''));
        if ($custom !== '') {
            $selected_providers[] = $custom;
        }
    }

    if (empty($selected_providers)) {
        $legacy_select = sanitize_key(get_theme_mod('ahx_privacy_maps_provider_select', ''));
        if ($legacy_select !== '' && isset($options[$legacy_select])) {
            if ($legacy_select === 'other') {
                $legacy_custom = sanitize_text_field(get_theme_mod('ahx_privacy_maps_provider_custom', ''));
                if ($legacy_custom !== '') {
                    $selected_providers[] = $legacy_custom;
                }
            } else {
                $selected_providers[] = $options[$legacy_select];
            }
        }

        $legacy_text = sanitize_text_field(get_theme_mod('ahx_privacy_maps_provider', ''));
        if ($legacy_text !== '') {
            $selected_providers[] = $legacy_text;
        }
    }

    $selected_providers = array_unique(array_filter($selected_providers));
    return implode(', ', $selected_providers);
}

function ahx_get_spam_provider_options() {
    return [
        'akismet' => 'Akismet',
        'antispam_bee' => 'Antispam Bee',
        'cleantalk' => 'CleanTalk',
        'recaptcha' => 'Google reCAPTCHA',
        'hcaptcha' => 'hCaptcha',
        'turnstile' => 'Cloudflare Turnstile',
        'friendlycaptcha' => 'Friendly Captcha',
        'wp_armour' => 'WP Armour',
        'stopforumspam' => 'Stop Forum Spam',
        'oopspam' => 'OOPSpam',
        'other' => __('Sonstiger', 'ahx_wp_lean'),
    ];
}

function ahx_show_custom_spam_provider_input() {
    return (bool) get_theme_mod('ahx_privacy_spam_provider_other', 0);
}

function ahx_get_selected_spam_provider_label() {
    $selected_providers = [];
    $options = ahx_get_spam_provider_options();

    foreach ($options as $key => $label) {
        if ($key === 'other') {
            continue;
        }
        if ((bool) get_theme_mod('ahx_privacy_spam_provider_' . $key, 0)) {
            $selected_providers[] = $label;
        }
    }

    if ((bool) get_theme_mod('ahx_privacy_spam_provider_other', 0)) {
        $custom = sanitize_text_field(get_theme_mod('ahx_privacy_spam_provider_custom', ''));
        if ($custom !== '') {
            $selected_providers[] = $custom;
        }
    }

    if (empty($selected_providers)) {
        $legacy_select = sanitize_key(get_theme_mod('ahx_privacy_spam_provider_select', ''));
        if ($legacy_select !== '' && isset($options[$legacy_select])) {
            if ($legacy_select === 'other') {
                $legacy_custom = sanitize_text_field(get_theme_mod('ahx_privacy_spam_provider_custom', ''));
                if ($legacy_custom !== '') {
                    $selected_providers[] = $legacy_custom;
                }
            } else {
                $selected_providers[] = $options[$legacy_select];
            }
        }

        $legacy_text = sanitize_text_field(get_theme_mod('ahx_privacy_spam_provider', ''));
        if ($legacy_text !== '') {
            $selected_providers[] = $legacy_text;
        }
    }

    $selected_providers = array_unique(array_filter($selected_providers));
    return implode(', ', $selected_providers);
}

function ahx_privacy_checklist_items() {
    return [
        'server_logs' => __('Server-Logfiles beim Hosting', 'ahx_wp_lean'),
        'contact_form' => __('Kontaktformular und Kontakt per E-Mail', 'ahx_wp_lean'),
        'media_uploads' => __('Medien-Uploads (EXIF/GPS-Metadaten)', 'ahx_wp_lean'),
        'cookies_necessary' => __('Technisch notwendige Cookies', 'ahx_wp_lean'),
        'cookies_marketing' => __('Marketing-/Tracking-Cookies', 'ahx_wp_lean'),
        'user_accounts' => __('Benutzerkonten und Login', 'ahx_wp_lean'),
        'embedded_content' => __('Eingebettete Inhalte (z. B. YouTube, externe Fonts)', 'ahx_wp_lean'),
        'maps_services' => __('Kartendienste (z. B. Google Maps)', 'ahx_wp_lean'),
        'analytics' => __('Webanalyse (z. B. Matomo/Google Analytics)', 'ahx_wp_lean'),
        'comments' => __('Kommentarfunktion', 'ahx_wp_lean'),
        'newsletter' => __('Newsletter', 'ahx_wp_lean'),
        'spam_detection' => __('Automatisierte Spam-Erkennung', 'ahx_wp_lean'),
    ];
}

if (class_exists('WP_Customize_Control') && !class_exists('AHX_Customize_Heading_Control')) {
    class AHX_Customize_Heading_Control extends WP_Customize_Control {
        public $type = 'ahx_heading';

        public function render_content() {
            if (!empty($this->label)) {
                echo '<span class="customize-control-title" style="margin-top:10px;display:block;">' . esc_html($this->label) . '</span>';
            }
            if (!empty($this->description)) {
                echo '<span class="description customize-control-description">' . esc_html($this->description) . '</span>';
            }
        }
    }
}

function ahx_register_privacy_customizer($wp_customize) {
    $wp_customize->add_panel('ahx_privacy_panel', [
        'title' => __('Datenschutz', 'ahx_wp_lean'),
        'priority' => 170,
        'description' => __('Einstellungen für die automatisch erzeugte Datenschutzerklärung.', 'ahx_wp_lean'),
    ]);

    $wp_customize->add_section('ahx_privacy_section_basics', [
        'title' => __('1. Basis & Kontakt', 'ahx_wp_lean'),
        'panel' => 'ahx_privacy_panel',
    ]);

    $wp_customize->add_section('ahx_privacy_section_checklist', [
        'title' => __('2. Inhalte (Checkliste)', 'ahx_wp_lean'),
        'panel' => 'ahx_privacy_panel',
        'description' => __('Wähle aus, welche Funktionen auf der aktuellen Website tatsächlich genutzt werden.', 'ahx_wp_lean'),
    ]);

    $wp_customize->add_section('ahx_privacy_section_providers', [
        'title' => __('3. Dienstleister', 'ahx_wp_lean'),
        'panel' => 'ahx_privacy_panel',
    ]);

    $wp_customize->add_section('ahx_privacy_section_output', [
        'title' => __('4. Ausgabe & Hinweis', 'ahx_wp_lean'),
        'panel' => 'ahx_privacy_panel',
    ]);

    $wp_customize->add_setting('ahx_privacy_enable_link', [
        'default' => 1,
        'sanitize_callback' => 'ahx_sanitize_checkbox',
    ]);

    $wp_customize->add_control('ahx_privacy_enable_link', [
        'label' => __('Datenschutz-Link im Footer anzeigen', 'ahx_wp_lean'),
        'section' => 'ahx_privacy_section_basics',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('ahx_privacy_page_slug', [
        'default' => 'datenschutz',
        'sanitize_callback' => 'sanitize_title',
    ]);

    $wp_customize->add_control('ahx_privacy_page_slug', [
        'label' => __('Slug für Datenschutz-Seite', 'ahx_wp_lean'),
        'description' => __('Beispiel: datenschutz => /datenschutz/', 'ahx_wp_lean'),
        'section' => 'ahx_privacy_section_basics',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('ahx_privacy_responsible_name', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('ahx_privacy_responsible_name', [
        'label' => __('Verantwortliche Person / Betreiber', 'ahx_wp_lean'),
        'section' => 'ahx_privacy_section_basics',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('ahx_privacy_responsible_email', [
        'default' => '',
        'sanitize_callback' => 'sanitize_email',
    ]);

    $wp_customize->add_control('ahx_privacy_responsible_email', [
        'label' => __('Kontakt-E-Mail', 'ahx_wp_lean'),
        'section' => 'ahx_privacy_section_basics',
        'type' => 'email',
    ]);

    $wp_customize->add_setting('ahx_privacy_responsible_address', [
        'default' => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);

    $wp_customize->add_control('ahx_privacy_responsible_address', [
        'label' => __('Anschrift (optional)', 'ahx_wp_lean'),
        'section' => 'ahx_privacy_section_basics',
        'type' => 'textarea',
    ]);

    $wp_customize->add_setting('ahx_privacy_hosting_provider_select', [
        'default' => '',
        'sanitize_callback' => 'ahx_sanitize_hosting_provider_select',
    ]);

    $wp_customize->add_control('ahx_privacy_hosting_provider_select', [
        'label' => __('Hosting-Dienstleister', 'ahx_wp_lean'),
        'description' => __('Top 10 Anbieter in Deutschland plus Sonstiger.', 'ahx_wp_lean'),
        'section' => 'ahx_privacy_section_providers',
        'type' => 'select',
        'choices' => ahx_get_hosting_provider_options(),
    ]);

    $wp_customize->add_setting('ahx_privacy_hosting_provider_custom', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('ahx_privacy_hosting_provider_custom', [
        'label' => __('Sonstiger Hosting-Dienstleister', 'ahx_wp_lean'),
        'description' => __('Bitte Anbieter manuell eintragen.', 'ahx_wp_lean'),
        'section' => 'ahx_privacy_section_providers',
        'type' => 'text',
        'active_callback' => 'ahx_show_custom_hosting_provider_input',
    ]);

    $wp_customize->add_setting('ahx_privacy_heading_analytics', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control(new AHX_Customize_Heading_Control(
        $wp_customize,
        'ahx_privacy_heading_analytics',
        [
            'label' => __('Analytics-Anbieter', 'ahx_wp_lean'),
            'description' => __('Mehrfachauswahl möglich.', 'ahx_wp_lean'),
            'section' => 'ahx_privacy_section_providers',
        ]
    ));

    foreach (ahx_get_analytics_provider_options() as $key => $label) {
        $setting_id = 'ahx_privacy_analytics_provider_' . $key;
        $wp_customize->add_setting($setting_id, [
            'default' => 0,
            'sanitize_callback' => 'ahx_sanitize_checkbox',
        ]);

        $wp_customize->add_control($setting_id, [
            'label' => $label,
            'section' => 'ahx_privacy_section_providers',
            'type' => 'checkbox',
        ]);
    }

    $wp_customize->add_setting('ahx_privacy_analytics_provider_custom', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('ahx_privacy_analytics_provider_custom', [
        'label' => __('Sonstiges Analytics-Tool', 'ahx_wp_lean'),
        'description' => __('Bitte Anbieter manuell eintragen.', 'ahx_wp_lean'),
        'section' => 'ahx_privacy_section_providers',
        'type' => 'text',
        'active_callback' => 'ahx_show_custom_analytics_provider_input',
    ]);

    $wp_customize->add_setting('ahx_privacy_embeds_provider', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('ahx_privacy_embeds_provider', [
        'label' => __('Anbieter für eingebettete Inhalte (optional)', 'ahx_wp_lean'),
        'description' => __('Beispiel: YouTube, Vimeo, Google Fonts', 'ahx_wp_lean'),
        'section' => 'ahx_privacy_section_providers',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('ahx_privacy_heading_maps', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control(new AHX_Customize_Heading_Control(
        $wp_customize,
        'ahx_privacy_heading_maps',
        [
            'label' => __('Maps-Anbieter', 'ahx_wp_lean'),
            'description' => __('Mehrfachauswahl möglich.', 'ahx_wp_lean'),
            'section' => 'ahx_privacy_section_providers',
        ]
    ));

    foreach (ahx_get_maps_provider_options() as $key => $label) {
        $setting_id = 'ahx_privacy_maps_provider_' . $key;
        $wp_customize->add_setting($setting_id, [
            'default' => 0,
            'sanitize_callback' => 'ahx_sanitize_checkbox',
        ]);

        $wp_customize->add_control($setting_id, [
            'label' => $label,
            'section' => 'ahx_privacy_section_providers',
            'type' => 'checkbox',
        ]);
    }

    $wp_customize->add_setting('ahx_privacy_maps_provider_custom', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('ahx_privacy_maps_provider_custom', [
        'label' => __('Sonstiger Kartendienst', 'ahx_wp_lean'),
        'description' => __('Bitte Anbieter manuell eintragen.', 'ahx_wp_lean'),
        'section' => 'ahx_privacy_section_providers',
        'type' => 'text',
        'active_callback' => 'ahx_show_custom_maps_provider_input',
    ]);

    $wp_customize->add_setting('ahx_privacy_heading_spam', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control(new AHX_Customize_Heading_Control(
        $wp_customize,
        'ahx_privacy_heading_spam',
        [
            'label' => __('Spam-Anbieter', 'ahx_wp_lean'),
            'description' => __('Mehrfachauswahl möglich.', 'ahx_wp_lean'),
            'section' => 'ahx_privacy_section_providers',
        ]
    ));

    foreach (ahx_get_spam_provider_options() as $key => $label) {
        $setting_id = 'ahx_privacy_spam_provider_' . $key;
        $wp_customize->add_setting($setting_id, [
            'default' => 0,
            'sanitize_callback' => 'ahx_sanitize_checkbox',
        ]);

        $wp_customize->add_control($setting_id, [
            'label' => $label,
            'section' => 'ahx_privacy_section_providers',
            'type' => 'checkbox',
        ]);
    }

    $wp_customize->add_setting('ahx_privacy_spam_provider_custom', [
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('ahx_privacy_spam_provider_custom', [
        'label' => __('Sonstiger Spam-Erkennungsdienst', 'ahx_wp_lean'),
        'description' => __('Bitte Anbieter manuell eintragen.', 'ahx_wp_lean'),
        'section' => 'ahx_privacy_section_providers',
        'type' => 'text',
        'active_callback' => 'ahx_show_custom_spam_provider_input',
    ]);

    $wp_customize->add_setting('ahx_privacy_show_disclaimer', [
        'default' => 1,
        'sanitize_callback' => 'ahx_sanitize_checkbox',
    ]);

    $wp_customize->add_control('ahx_privacy_show_disclaimer', [
        'label' => __('Disclaimer-Block anzeigen', 'ahx_wp_lean'),
        'section' => 'ahx_privacy_section_output',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('ahx_privacy_disclaimer_text', [
        'default' => __('Hinweis: Diese automatisch erzeugte Vorlage ersetzt keine individuelle Rechtsberatung.', 'ahx_wp_lean'),
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);

    $wp_customize->add_control('ahx_privacy_disclaimer_text', [
        'label' => __('Disclaimer-Text', 'ahx_wp_lean'),
        'section' => 'ahx_privacy_section_output',
        'type' => 'textarea',
    ]);

    foreach (ahx_privacy_checklist_items() as $key => $label) {
        $setting_id = 'ahx_privacy_item_' . $key;

        $wp_customize->add_setting($setting_id, [
            'default' => 0,
            'sanitize_callback' => 'ahx_sanitize_checkbox',
        ]);

        $wp_customize->add_control($setting_id, [
            'label' => $label,
            'section' => 'ahx_privacy_section_checklist',
            'type' => 'checkbox',
        ]);
    }
}
add_action('customize_register', 'ahx_register_privacy_customizer');

function ahx_privacy_item_enabled($key) {
    return (bool) get_theme_mod('ahx_privacy_item_' . $key, 0);
}

function ahx_get_privacy_policy_url() {
    $slug = get_theme_mod('ahx_privacy_page_slug', 'datenschutz');
    $slug = sanitize_title($slug);
    if ($slug === '') {
        $slug = 'datenschutz';
    }

    return home_url('/' . $slug . '/');
}

function ahx_is_virtual_privacy_request() {
    if (is_admin()) {
        return false;
    }

    global $wp;
    if (!isset($wp->request)) {
        return false;
    }

    $slug = get_theme_mod('ahx_privacy_page_slug', 'datenschutz');
    $slug = sanitize_title($slug);
    if ($slug === '') {
        $slug = 'datenschutz';
    }

    return trim($wp->request, '/') === $slug;
}

function ahx_virtual_privacy_document_title($title) {
    if (ahx_is_virtual_privacy_request()) {
        return __('Datenschutzerklärung', 'ahx_wp_lean');
    }

    return $title;
}
add_filter('pre_get_document_title', 'ahx_virtual_privacy_document_title');

function ahx_generate_privacy_policy_html() {
    $site_name = get_bloginfo('name');
    $responsible_name = get_theme_mod('ahx_privacy_responsible_name', '');
    $responsible_email = get_theme_mod('ahx_privacy_responsible_email', '');
    $responsible_address = get_theme_mod('ahx_privacy_responsible_address', '');
    $hosting_provider = ahx_get_selected_hosting_provider_label();
    $analytics_provider = ahx_get_selected_analytics_provider_label();
    $embeds_provider = sanitize_text_field(get_theme_mod('ahx_privacy_embeds_provider', ''));
    $maps_provider = ahx_get_selected_maps_provider_label();
    $spam_provider = ahx_get_selected_spam_provider_label();
    $show_disclaimer = (bool) get_theme_mod('ahx_privacy_show_disclaimer', 1);
    $disclaimer_text = sanitize_textarea_field(
        get_theme_mod(
            'ahx_privacy_disclaimer_text',
            __('Hinweis: Diese automatisch erzeugte Vorlage ersetzt keine individuelle Rechtsberatung.', 'ahx_wp_lean')
        )
    );
    $has_processing_sections = ahx_privacy_item_enabled('server_logs')
        || ahx_privacy_item_enabled('contact_form')
        || ahx_privacy_item_enabled('comments')
        || ahx_privacy_item_enabled('media_uploads')
        || ahx_privacy_item_enabled('cookies_necessary')
        || ahx_privacy_item_enabled('cookies_marketing')
        || ahx_privacy_item_enabled('user_accounts')
        || ahx_privacy_item_enabled('embedded_content')
        || ahx_privacy_item_enabled('maps_services')
        || ahx_privacy_item_enabled('analytics')
        || ahx_privacy_item_enabled('newsletter');

    ob_start();
    ?>
    <h1><?php echo esc_html__('Datenschutzerklärung', 'ahx_wp_lean'); ?></h1>
    <p><?php echo esc_html__('Stand: ', 'ahx_wp_lean') . esc_html(date_i18n(get_option('date_format'))); ?></p>

    <div class="privacy-group privacy-group-basics">
        <h2><?php echo esc_html__('1. Basisangaben', 'ahx_wp_lean'); ?></h2>

        <h3><?php echo esc_html__('Wer wir sind', 'ahx_wp_lean'); ?></h3>
        <p>
            <?php
            echo esc_html__('Die Adresse unserer Website ist: ', 'ahx_wp_lean') . esc_url(home_url('/'));
            ?>
        </p>

        <h3><?php echo esc_html__('Verantwortliche Stelle', 'ahx_wp_lean'); ?></h3>
        <p>
            <?php
            echo esc_html($responsible_name !== '' ? $responsible_name : $site_name);
            if ($responsible_address !== '') {
                echo '<br>' . nl2br(esc_html($responsible_address));
            }
            if ($responsible_email !== '') {
                $safe_email = sanitize_email($responsible_email);
                if ($safe_email !== '') {
                    echo '<br>' . esc_html__('E-Mail: ', 'ahx_wp_lean');
                    echo '<a href="mailto:' . esc_attr(antispambot($safe_email, 1)) . '">' . antispambot($safe_email) . '</a>';
                }
            }
            ?>
        </p>
    </div>

    <?php if ($has_processing_sections) : ?>
        <div class="privacy-group privacy-group-processing">
            <h2><?php echo esc_html__('2. Verarbeitung auf dieser Website', 'ahx_wp_lean'); ?></h2>

            <?php if (ahx_privacy_item_enabled('server_logs')) : ?>
                <h3><?php echo esc_html__('Server-Logfiles', 'ahx_wp_lean'); ?></h3>
                <p><?php echo esc_html__('Wenn du diese Website besuchst, erhebt der Hoster automatisch technische Daten (z. B. IP-Adresse, Datum und Uhrzeit, Browser, Betriebssystem, Referrer und aufgerufene URL). Das ist notwendig, um die Website stabil und sicher bereitzustellen.', 'ahx_wp_lean'); ?></p>
                <?php if ($hosting_provider !== '') : ?>
                    <p><?php echo esc_html__('Eingesetzter Hosting-Dienstleister: ', 'ahx_wp_lean') . esc_html($hosting_provider); ?></p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (ahx_privacy_item_enabled('contact_form')) : ?>
                <h3><?php echo esc_html__('Kontaktformulare und E-Mail', 'ahx_wp_lean'); ?></h3>
                <p><?php echo esc_html__('Wenn du uns per Formular oder E-Mail kontaktierst, verarbeiten wir deine Angaben zur Bearbeitung der Anfrage.', 'ahx_wp_lean'); ?></p>
            <?php endif; ?>

            <?php if (ahx_privacy_item_enabled('comments')) : ?>
                <h3><?php echo esc_html__('Kommentare', 'ahx_wp_lean'); ?></h3>
                <p><?php echo esc_html__('Wenn Besucher Kommentare auf der Website schreiben, sammeln wir die Daten, die im Kommentar-Formular angezeigt werden, außerdem die IP-Adresse des Besuchers und den User-Agent-String (damit wird der Browser identifiziert), um die Erkennung von Spam zu unterstützen.', 'ahx_wp_lean'); ?></p>
                <p><?php echo esc_html__('Aus deiner E-Mail-Adresse kann eine anonymisierte Zeichenfolge erstellt (auch Hash genannt) und dem Gravatar-Dienst übergeben werden, um zu prüfen, ob du diesen benutzt. Die Datenschutzerklärung des Gravatar-Dienstes findest du hier: https://automattic.com/privacy/. Nachdem dein Kommentar freigegeben wurde, ist dein Profilbild öffentlich im Kontext deines Kommentars sichtbar.', 'ahx_wp_lean'); ?></p>
            <?php endif; ?>

            <?php if (ahx_privacy_item_enabled('media_uploads')) : ?>
                <h3><?php echo esc_html__('Medien', 'ahx_wp_lean'); ?></h3>
                <p><?php echo esc_html__('Wenn du ein registrierter Benutzer bist und Fotos auf diese Website lädst, solltest du vermeiden, Fotos mit einem EXIF-GPS-Standort hochzuladen. Besucher dieser Website könnten Fotos, die auf dieser Website gespeichert sind, herunterladen und deren Standort-Informationen extrahieren.', 'ahx_wp_lean'); ?></p>
            <?php endif; ?>

            <?php if (ahx_privacy_item_enabled('cookies_necessary') || ahx_privacy_item_enabled('cookies_marketing') || ahx_privacy_item_enabled('user_accounts') || ahx_privacy_item_enabled('comments')) : ?>
                <h3><?php echo esc_html__('Cookies', 'ahx_wp_lean'); ?></h3>

                <?php if (ahx_privacy_item_enabled('comments')) : ?>
                    <p><?php echo esc_html__('Wenn du einen Kommentar auf unserer Website schreibst, kann das eine Einwilligung sein, deinen Namen, E-Mail-Adresse und Website in Cookies zu speichern. Dies ist eine Komfortfunktion, damit du nicht, wenn du einen weiteren Kommentar schreibst, all diese Daten erneut eingeben musst. Diese Cookies werden ein Jahr lang gespeichert.', 'ahx_wp_lean'); ?></p>
                <?php endif; ?>

                <?php if (ahx_privacy_item_enabled('user_accounts')) : ?>
                    <p><?php echo esc_html__('Falls du ein Konto hast und dich auf dieser Website anmeldest, setzen wir ein temporäres Cookie, um festzustellen, ob dein Browser Cookies akzeptiert. Dieses Cookie enthält keine personenbezogenen Daten und wird verworfen, wenn du deinen Browser schließt.', 'ahx_wp_lean'); ?></p>
                    <p><?php echo esc_html__('Wenn du dich anmeldest, richten wir einige Cookies ein, um deine Anmeldeinformationen und Anzeigeoptionen zu speichern. Anmelde-Cookies verfallen nach zwei Tagen und Cookies für die Anzeigeoptionen nach einem Jahr. Falls du bei der Anmeldung „Angemeldet bleiben“ auswählst, wird deine Anmeldung zwei Wochen lang aufrechterhalten. Mit der Abmeldung aus deinem Konto werden die Anmelde-Cookies gelöscht.', 'ahx_wp_lean'); ?></p>
                    <p><?php echo esc_html__('Wenn du einen Artikel bearbeitest oder veröffentlichst, wird ein zusätzlicher Cookie in deinem Browser gespeichert. Dieser Cookie enthält keine personenbezogenen Daten und verweist nur auf die Beitrags-ID des Artikels, den du gerade bearbeitet hast. Der Cookie verfällt nach einem Tag.', 'ahx_wp_lean'); ?></p>
                <?php endif; ?>

                <?php if (ahx_privacy_item_enabled('cookies_necessary')) : ?>
                    <p><?php echo esc_html__('Zusätzlich setzen wir technisch notwendige Cookies, damit zentrale Funktionen der Website zuverlässig laufen.', 'ahx_wp_lean'); ?></p>
                <?php endif; ?>

                <?php if (ahx_privacy_item_enabled('cookies_marketing')) : ?>
                    <p><?php echo esc_html__('Nicht notwendige Cookies für Marketing oder Tracking setzen wir nur mit deiner ausdrücklichen Einwilligung. Eine erteilte Einwilligung kannst du jederzeit mit Wirkung für die Zukunft widerrufen.', 'ahx_wp_lean'); ?></p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (ahx_privacy_item_enabled('embedded_content')) : ?>
                <h3><?php echo esc_html__('Eingebettete Inhalte von anderen Websites', 'ahx_wp_lean'); ?></h3>
                <p><?php echo esc_html__('Beiträge auf dieser Website können eingebettete Inhalte beinhalten (z. B. Videos, Bilder, Beiträge etc.). Eingebettete Inhalte von anderen Websites verhalten sich exakt so, als ob du die andere Website besucht hättest.', 'ahx_wp_lean'); ?></p>
                <p><?php echo esc_html__('Diese Websites können Daten über dich sammeln, Cookies benutzen, zusätzliche Tracking-Dienste von Dritten einbetten und deine Interaktion mit dem eingebetteten Inhalt aufzeichnen, inklusive deiner Interaktion mit dem eingebetteten Inhalt, falls du ein Konto hast und auf dieser Website angemeldet bist.', 'ahx_wp_lean'); ?></p>
                <?php if ($embeds_provider !== '') : ?>
                    <p><?php echo esc_html__('Eingesetzte Anbieter: ', 'ahx_wp_lean') . esc_html($embeds_provider); ?></p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (ahx_privacy_item_enabled('maps_services')) : ?>
                <h3><?php echo esc_html__('Kartendienste', 'ahx_wp_lean'); ?></h3>
                <p><?php echo esc_html__('Zur Darstellung interaktiver Karten können Inhalte externer Anbieter geladen werden. Dabei können insbesondere IP-Adresse und Nutzungsdaten verarbeitet werden.', 'ahx_wp_lean'); ?></p>
                <?php if ($maps_provider !== '') : ?>
                    <p><?php echo esc_html__('Eingesetzter Kartendienst: ', 'ahx_wp_lean') . esc_html($maps_provider); ?></p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (ahx_privacy_item_enabled('analytics')) : ?>
                <h3><?php echo esc_html__('Webanalyse', 'ahx_wp_lean'); ?></h3>
                <p><?php echo esc_html__('Wenn wir Webanalyse einsetzen, dient das dazu, unser Angebot technisch und inhaltlich zu verbessern.', 'ahx_wp_lean'); ?></p>
                <?php if ($analytics_provider !== '') : ?>
                    <p><?php echo esc_html__('Eingesetztes Analyse-Tool / Dienstleister: ', 'ahx_wp_lean') . esc_html($analytics_provider); ?></p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (ahx_privacy_item_enabled('newsletter')) : ?>
                <h3><?php echo esc_html__('Newsletter', 'ahx_wp_lean'); ?></h3>
                <p><?php echo esc_html__('Wenn du dich für unseren Newsletter anmeldest, verarbeiten wir deine E-Mail-Adresse und ggf. weitere freiwillige Angaben auf Grundlage deiner Einwilligung.', 'ahx_wp_lean'); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="privacy-group privacy-group-rights">
        <h2><?php echo esc_html__('3. Speicherung, Weitergabe und Rechte', 'ahx_wp_lean'); ?></h2>

        <h3><?php echo esc_html__('Mit wem wir deine Daten teilen', 'ahx_wp_lean'); ?></h3>
        <p><?php echo esc_html__('Wenn du eine Zurücksetzung des Passworts beantragst, wird deine IP-Adresse in der E-Mail zur Zurücksetzung enthalten sein.', 'ahx_wp_lean'); ?></p>
        <?php if ($hosting_provider !== '' || $analytics_provider !== '' || $embeds_provider !== '' || $maps_provider !== '' || $spam_provider !== '') : ?>
            <p>
                <?php
                $service_providers = [];
                if ($hosting_provider !== '') {
                    $service_providers[] = $hosting_provider;
                }
                if ($analytics_provider !== '') {
                    $service_providers[] = $analytics_provider;
                }
                if ($embeds_provider !== '') {
                    $service_providers[] = $embeds_provider;
                }
                if ($maps_provider !== '') {
                    $service_providers[] = $maps_provider;
                }
                if ($spam_provider !== '') {
                    $service_providers[] = $spam_provider;
                }
                $service_providers = array_unique($service_providers);
                echo esc_html__('Konkret benannte Dienstleister: ', 'ahx_wp_lean') . esc_html(implode(', ', $service_providers));
                ?>
            </p>
        <?php endif; ?>

        <h3><?php echo esc_html__('Wie lange wir deine Daten speichern', 'ahx_wp_lean'); ?></h3>
        <?php if (ahx_privacy_item_enabled('comments')) : ?>
            <p><?php echo esc_html__('Wenn du einen Kommentar schreibst, wird dieser inklusive Metadaten zeitlich unbegrenzt gespeichert. Auf diese Art können wir Folgekommentare automatisch erkennen und freigeben, anstatt sie in einer Moderations-Warteschlange festzuhalten.', 'ahx_wp_lean'); ?></p>
        <?php endif; ?>
        <?php if (ahx_privacy_item_enabled('user_accounts')) : ?>
            <p><?php echo esc_html__('Für Benutzer, die sich auf unserer Website registrieren, speichern wir zusätzlich die persönlichen Informationen, die sie in ihren Benutzerprofilen angeben. Alle Benutzer können jederzeit ihre persönlichen Informationen einsehen, verändern oder löschen (der Benutzername kann nicht verändert werden). Administratoren der Website können diese Informationen ebenfalls einsehen und verändern.', 'ahx_wp_lean'); ?></p>
        <?php endif; ?>
        <?php if (!ahx_privacy_item_enabled('comments') && !ahx_privacy_item_enabled('user_accounts')) : ?>
            <p><?php echo esc_html__('Wir speichern personenbezogene Daten nur so lange, wie das für die jeweiligen Zwecke erforderlich ist oder gesetzliche Aufbewahrungspflichten bestehen.', 'ahx_wp_lean'); ?></p>
        <?php endif; ?>

        <h3><?php echo esc_html__('Welche Rechte du an deinen Daten hast', 'ahx_wp_lean'); ?></h3>
        <p><?php echo esc_html__('Wenn du ein Konto auf dieser Website besitzt oder Kommentare geschrieben hast, kannst du einen Export deiner personenbezogenen Daten bei uns anfordern, inklusive aller Daten, die du uns mitgeteilt hast. Darüber hinaus kannst du die Löschung aller personenbezogenen Daten, die wir von dir gespeichert haben, anfordern. Dies umfasst nicht die Daten, die wir aufgrund administrativer, rechtlicher oder sicherheitsrelevanter Notwendigkeiten aufbewahren müssen.', 'ahx_wp_lean'); ?></p>

        <?php if (ahx_privacy_item_enabled('spam_detection') || ahx_privacy_item_enabled('comments')) : ?>
            <h3><?php echo esc_html__('Wohin deine Daten gesendet werden', 'ahx_wp_lean'); ?></h3>
            <p><?php echo esc_html__('Besucher-Kommentare könnten von einem automatisierten Dienst zur Spam-Erkennung untersucht werden.', 'ahx_wp_lean'); ?></p>
            <?php if ($spam_provider !== '') : ?>
                <p><?php echo esc_html__('Eingesetzter Spam-Erkennungsdienst: ', 'ahx_wp_lean') . esc_html($spam_provider); ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($show_disclaimer && $disclaimer_text !== '') : ?>
            <p class="privacy-disclaimer"><em><?php echo nl2br(esc_html($disclaimer_text)); ?></em></p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function ahx_maybe_render_virtual_privacy_page() {
    if (!ahx_is_virtual_privacy_request()) {
        return;
    }

    global $wp_query;
    if (isset($wp_query)) {
        $wp_query->is_404 = false;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_home = false;
        $wp_query->is_archive = false;
    }

    status_header(200);
    nocache_headers();

    get_header();
    echo '<main id="content" class="site-main">';
    echo '<article class="privacy-policy generated-by-customizer">';
    echo wp_kses_post(ahx_generate_privacy_policy_html());
    echo '</article>';
    echo '</main>';
    get_footer();
    exit;
}
add_action('template_redirect', 'ahx_maybe_render_virtual_privacy_page');

/**
 * Show an admin notice on the WordPress Privacy settings page
 * pointing to the Theme Customizer instead.
 */
function ahx_privacy_admin_notice() {
    $screen = get_current_screen();
    if ( ! $screen ) {
        return;
    }
    // privacy.php (WP 5.3+) and options-privacy.php (older) both carry base 'privacy'
    if ( $screen->base !== 'privacy' && $screen->id !== 'options-privacy' ) {
        return;
    }
    $customizer_url = add_query_arg(
        array(
            'autofocus[panel]' => 'ahx_privacy_panel',
            'url'              => rawurlencode( home_url( '/?ahx_privacy=1' ) ),
        ),
        admin_url( 'customize.php' )
    );
    ?>
    <div class="notice notice-info" style="display:flex;align-items:flex-start;gap:14px;padding:14px 16px;">
        <span class="dashicons dashicons-shield" style="font-size:28px;margin-top:2px;color:#0073aa;flex-shrink:0;"></span>
        <div>
            <p style="margin:0 0 6px;font-size:14px;font-weight:600;">
                <?php esc_html_e( 'Datenschutzerklärung wird vom Theme verwaltet', 'ahx_wp_lean' ); ?>
            </p>
            <p style="margin:0;font-size:13px;">
                <?php
                printf(
                    /* translators: %s: link to customizer */
                    wp_kses(
                        __( 'Das aktive Theme <strong>ahx_wp_lean</strong> generiert die Datenschutzerklärung automatisch und stellt sie unter <code>/?ahx_privacy=1</code> bereit. Eine separate WordPress-Datenschutzseite ist daher nicht erforderlich. Alle Inhalte können im <a href="%s">Design &rarr; Anpassen &rarr; Datenschutz-Einstellungen</a> gepflegt werden.', 'ahx_wp_lean' ),
                        array(
                            'strong' => array(),
                            'code'   => array(),
                            'a'      => array( 'href' => array() ),
                        )
                    ),
                    esc_url( $customizer_url )
                );
                ?>
            </p>
        </div>
    </div>
    <?php
}
add_action( 'admin_notices', 'ahx_privacy_admin_notice' );
