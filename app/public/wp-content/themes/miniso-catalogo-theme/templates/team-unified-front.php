<?php
/**
 * MINISO · Inicio unificado.
 * Renderiza los bloques editables de la página Inicio también en la portada raíz.
 * IMPORTANTE: no utilizar $page para un WP_Post. WordPress usa esa variable
 * global como índice numérico de paginación durante setup_postdata().
 */
defined('ABSPATH') || exit;

$home_page_id = minu_home_page_id();
$home_post = $home_page_id ? get_post($home_page_id) : null;
get_header();

if ($home_post instanceof WP_Post && $home_post->post_type === 'page') {
    $previous_post = $GLOBALS['post'] ?? null;
    $GLOBALS['post'] = $home_post;
    setup_postdata($home_post);

    echo '<div class="miniso-flexible-home">';
    echo apply_filters('the_content', $home_post->post_content); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo '</div>';

    wp_reset_postdata();
    if ($previous_post instanceof WP_Post) {
        $GLOBALS['post'] = $previous_post;
    } elseif ($previous_post === null) {
        unset($GLOBALS['post']);
    }
} elseif (function_exists('mct_home')) {
    // Respaldo del diseño original si no se encuentra la página editable.
    mct_home();
}

get_footer();
