<?php
get_header();
?>

<main id="content" class="site-main">
    <div class="container">
        <section class="error-404 not-found">
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e('Seite nicht gefunden', 'ahx_wp_lean'); ?></h1>
            </header>
            <div class="page-content">
                <p><?php esc_html_e('Die gesuchte Seite konnte nicht gefunden werden. Versuche es mit der Suche oder geh zurück zur Startseite.', 'ahx_wp_lean'); ?></p>
                <?php get_search_form(); ?>
            </div>
        </section>
    </div>
</main>

<?php
get_footer();
