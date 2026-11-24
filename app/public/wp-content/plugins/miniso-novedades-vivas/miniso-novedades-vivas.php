<?php
/**
 * Plugin Name: MINISO · Novedades Vivas
 * Description: Diseño editorial de Novedades MINISO, filtros, fichas de eventos y acceso a Reels de Instagram; conserva el tema y las publicaciones existentes.
 * Version: 1.7.0
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * License: GPL-2.0-or-later
 * Text Domain: miniso-novedades-vivas
 */
defined('ABSPATH') || exit;
require_once __DIR__ . '/inc/news.php';
require_once __DIR__ . '/inc/extras.php';
require_once __DIR__ . '/inc/activities.php';
require_once __DIR__ . '/inc/index.php';

/** Sólo sustituye la presentación cuando el tema MINISO original está activo. */
add_filter('template_include', function ($template) {
    if (!function_exists('mct_media') || !function_exists('mct_url')) return $template;
    if (is_singular('miniso_novedad')) return __DIR__ . '/templates/single-miniso_novedad.php';
    if (is_page_template('templates/novedades.php')) return __DIR__ . '/templates/novedades.php';
    return $template;
}, 30);

add_action('wp_enqueue_scripts', function () {
    if ( ! is_singular('miniso_novedad') && ! is_page_template('templates/novedades.php') ) return;
    wp_enqueue_style('miniso-novedades-vivas', plugins_url('assets/novedades.css', __FILE__), array('miniso-catalogo'), '1.7.0');
    wp_enqueue_script('miniso-novedades-motion', plugins_url('assets/novedades.js', __FILE__), array(), '1.7.0', true);
}, 20);

function mnv_news_card($post) {
    if (!function_exists('mct_media') || !function_exists('mct_accent_style')) return;
    $post = get_post($post);
    if (!$post) return;
    $url = get_permalink($post);
    $type = mnv_news_type($post);
    $color = sanitize_hex_color(get_post_meta($post->ID,'miniso_accent',true)) ?: '#e60020';
    echo '<article class="news-card mct-news-tile mct-news-tile-'.esc_attr($type).'" style="'.esc_attr(mct_accent_style($color)).'">';
    echo '<a href="'.esc_url($url).'" aria-label="Leer '.esc_attr($post->post_title).'">';
    mct_media(get_post_thumbnail_id($post->ID), $post->post_title,'news-art');
    echo '<div class="news-copy"><div class="mct-news-kicker"><span class="news-label mct-news-pill mct-type-'.esc_attr($type).'">'.esc_html(mnv_news_type_label($post)).'</span><span class="mct-news-date">'.esc_html(mnv_news_date_display($post)).'</span></div>';
    echo '<h3>'.esc_html($post->post_title).'</h3><p>'.esc_html(mnv_news_excerpt($post,24)).'</p><span class="news-read">Descubrir más <span aria-hidden="true">↗</span></span></div></a></article>';
}
