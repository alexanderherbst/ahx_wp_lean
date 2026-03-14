<?php
wp_nav_menu([
    'theme_location' => 'main-menu',
    'container' => false,
    'menu_class' => 'menu-list',
    'menu_id' => 'primary-menu',
    'depth' => 2,
    'fallback_cb' => 'ahx_page_menu_fallback',
]);
?>
