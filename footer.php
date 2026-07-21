<footer class="site-footer">
    <p>&copy; <?php echo date("Y"); ?> <?php bloginfo('name'); ?></p>

    <?php ahx_render_footer_ahx_inventory(); ?>

    <?php if ((bool) get_theme_mod('ahx_privacy_enable_link', 1)) : ?>
        <p class="footer-privacy-link">
            <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo esc_url(admin_url()); ?>"><?php echo esc_html__('Backend', 'ahx_wp_lean'); ?></a> |
            <?php endif; ?>
            <a href="<?php echo esc_url(ahx_get_privacy_policy_url()); ?>"><?php echo esc_html__('Datenschutz', 'ahx_wp_lean'); ?></a>
        </p>
    <?php endif; ?>
</footer>

<?php wp_footer(); ?>
</body>
</html>
