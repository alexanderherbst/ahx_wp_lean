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

<header class="site-header">
    <div class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a></div>
    
    <nav class="nav-menu">
        <button id="burger-menu" aria-label="Menü öffnen">&#9776;</button>
        <?php get_template_part('template-parts/navigation'); ?>
    </nav>
</header>
