<?php
/** MINISO 3.0: noticias integradas. No se cargan si el complemento original sigue activo. */
defined('ABSPATH') || exit;
if (!function_exists('mnv_news_types')) {
foreach (array('news','extras','activities','index') as $file) require_once __DIR__ . '/novedades/' . $file . '.php';
add_action('wp_enqueue_scripts', function () {
 if (!is_singular('miniso_novedad') && !is_page_template('templates/novedades.php')) return;
 $v=wp_get_theme()->get('Version');
 wp_enqueue_style('miniso-novedades-vivas',get_template_directory_uri().'/assets/team-news-novedades.css',array('miniso-catalogo'),$v);
 wp_enqueue_script('miniso-novedades-motion',get_template_directory_uri().'/assets/team-news-novedades.js',array(),$v,true);
}, 20);
if (!function_exists('mnv_news_card')) {
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
    echo '<h3>'.esc_html($post->post_title).'</h3><p>'.esc_html(mnv_news_excerpt($post,24)).'</p><span class="news-read">'.esc_html(mct_copy_text('news_read')).' <span aria-hidden="true">↗</span></span></div></a></article>';
}

} // mnv_news_card
} // Proteccion ante duplicacion con plugins MINISO preexistentes.
