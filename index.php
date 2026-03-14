<?php get_header(); ?>

<div id="process-overlay" style="display:none;">
    <div class="overlay-content">
        
        <span class="dashicons dashicons-translation overlay-icon"></span>
        <p class="overlay-text">
            Verarbeitung läuft ...
        </p>
        <div class="overlay-spinner"></div>
    </div>
</div>

<script>

    function showOverlay() {
        document.getElementById("process-overlay").style.display = "flex";
    }

    function hideOverlay() {
        document.getElementById("process-overlay").style.display = "none";
    }
    
</script>

<main id="content" class="site-main">
    <div class="container">
        <?php
        if (have_posts()) :
            while (have_posts()) : the_post();
                ?>
                <article <?php post_class(); ?>>
                    <!-- <h2><?php the_title(); ?></h2> -->
                    <div class="container-content"><?php the_content(); ?></div>
                </article>
                <?php
            endwhile;
        else :
            echo '<p>Keine Inhalte gefunden.</p>';
        endif;
        ?>
    </div>
</main>

<?php get_footer(); ?>
