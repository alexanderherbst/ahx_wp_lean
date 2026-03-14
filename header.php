<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="theme-color" content="#007bff">

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Vokabeltrainer">

    <!-- iOS App Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/icon-192x192.png">
    <link rel="apple-touch-startup-image" href="/icons/icon-512x512.png">

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<a class="skip-link" href="#content"><?php echo esc_html__('Zum Inhalt springen', 'ahx_wp_lean'); ?></a>

<header class="site-header">
    <div class="site-title">
        <?php if (is_front_page() || is_home()) : ?>
            <h1><a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html(get_bloginfo('name')); ?></a></h1>
        <?php else : ?>
            <p><a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html(get_bloginfo('name')); ?></a></p>
        <?php endif; ?>
    </div>
    
    <nav id="site-navigation" class="nav-menu" role="navigation" aria-label="Hauptmenü">
        <button id="burger-menu" class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php echo esc_html__('Menü', 'ahx_wp_lean'); ?></button>
        <?php get_template_part('template-parts/navigation'); ?>
    </nav>
</header>
