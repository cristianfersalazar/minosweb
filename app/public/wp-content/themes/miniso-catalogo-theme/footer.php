<?php defined('ABSPATH') || exit; ?>
<?php if (function_exists('mct_careers_auto_section')) echo mct_careers_auto_section(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render controlado ?>
</main>
<?php mct_social_section(); ?>
<footer class="site-footer"><div class="footer-inner"><div><?php if(mct_ui_text('footer_brand')): ?><strong><?php echo esc_html(mct_ui_text('footer_brand')); ?></strong><?php endif; ?><p><?php echo esc_html(mct_design('footer_text')); ?></p><?php if(mct_ui_text('footer_slogan')): ?><span><?php echo esc_html(mct_ui_text('footer_slogan')); ?></span><?php endif; ?></div><nav aria-label="Navegación del pie"><?php mct_navigation(); ?><?php if (function_exists('mct_careers_footer_link')) echo mct_careers_footer_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- enlace escapado ?></nav><div class="footer-socials"><?php mct_social_links(); ?></div></div></footer>
<nav class="bottom-nav" aria-label="Navegación móvil">
<?php mct_navigation(true); ?>
<details class="mobile-more"><summary><?php echo mct_icon('more'); ?><span><?php echo esc_html(mct_copy_text('mobile_more')); ?></span></summary><div class="mobile-more-panel"><a href="<?php echo esc_url(mct_url('lover')); ?>"><?php echo mct_icon('lover'); ?><span><?php echo esc_html(mct_design('nav_lover')); ?></span></a><a href="<?php echo esc_url(mct_url('ayuda')); ?>"><?php echo mct_icon('ayuda'); ?><span><?php echo esc_html(mct_design('nav_ayuda')); ?></span></a><a href="<?php echo esc_url(mct_url('favoritos')); ?>"><?php echo mct_icon('heart'); ?><span><?php echo esc_html(mct_copy_text('mobile_favorites')); ?></span></a><?php if (function_exists('mct_careers_footer_link')) echo mct_careers_footer_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- enlace escapado ?></div></details>
</nav>
<div id="message" role="status" aria-live="polite"></div>
<?php wp_footer(); ?>
</body></html>
