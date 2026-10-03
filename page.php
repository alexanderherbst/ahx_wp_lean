<?php
get_header();
?>

<main id="content" class="site-main">
    <?php
    if (have_posts()) :
        while (have_posts()) : the_post();
            ?>
            <article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
                <?php
                $has_post_thumbnail = has_post_thumbnail();
                $show_page_title = function_exists('ahx_should_show_page_title') && ahx_should_show_page_title(get_the_ID());
                if ($has_post_thumbnail) :
                    ?>
                    <div class="post-featured-image">
                        <?php the_post_thumbnail('full'); ?>
                        <?php if ($show_page_title) : ?>
                            <header class="entry-header post-featured-image-title">
                                <div class="container">
                                    <h1 class="entry-title"><?php the_title(); ?></h1>
                                </div>
                            </header>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="container">
                    <?php if (!$has_post_thumbnail && $show_page_title) : ?>
                        <header class="entry-header">
                            <h1 class="entry-title"><?php the_title(); ?></h1>
                        </header>
                    <?php endif; ?>
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </article>
            <?php
        endwhile;
    else :
        ?>
        <div class="container">
            <p><?php echo esc_html__('Keine Seite gefunden.', 'ahx_wp_lean'); ?></p>
        </div>
        <?php
    endif;
    ?>
</main>

<?php
get_footer();
