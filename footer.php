<footer class="site-footer">
    <p>&copy; <?php echo date("Y"); ?> <?php bloginfo('name'); ?></p>
    <?php if ((bool) get_theme_mod('ahx_privacy_enable_link', 1)) : ?>
        <p class="footer-privacy-link">
            <a href="<?php echo esc_url(ahx_get_privacy_policy_url()); ?>"><?php echo esc_html__('Datenschutz', 'ahx_wp_lean'); ?></a>
        </p>
    <?php endif; ?>
</footer>

<?php wp_footer(); ?>
</body>
</html>
