<?php
get_header();
?>

<main id="content" class="site-main">
    <div class="container">
        <header class="archive-header">
            <h1 class="archive-title"><?php post_type_archive_title(); ?></h1>
        </header>

        <?php if (have_posts()) : ?>
            <div class="posts-list">
                <?php while (have_posts()) : the_post(); ?>
                    <article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
                        <header class="entry-header">
                            <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        </header>
                        <div class="entry-summary">
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            <div class="pagination">
                <?php the_posts_pagination(); ?>
            </div>
        <?php else : ?>
            <p><?php esc_html_e('Keine Beiträge gefunden.', 'ahx_wp_lean'); ?></p>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
