<?php
get_header();
?>

<main id="content" class="site-main">
    <div class="container">
        <?php
        if (have_posts()) :
            while (have_posts()) : the_post();
                ?>
                <article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
                    <header class="entry-header">
                        <h1 class="entry-title"><?php the_title(); ?></h1>
                    </header>
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                    <footer class="entry-footer">
                        <p class="posted-on"><?php echo get_the_date(); ?></p>
                    </footer>
                </article>
                <?php
            endwhile;
        else :
            echo '<p>' . esc_html__('Kein Beitrag gefunden.', 'ahx_wp_lean') . '</p>';
        endif;
        ?>
    </div>
</main>

<?php
get_footer();
