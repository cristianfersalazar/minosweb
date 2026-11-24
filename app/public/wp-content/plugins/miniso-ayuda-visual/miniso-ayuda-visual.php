<?php
/**
 * Plugin Name: MINISO · Ayuda Visual Unificada
 * Description: Unifica los acordeones de Ayuda y las preguntas de secciones globales con el estilo visual de MINISO Lover+, sin modificar el contenido.
 * Version: 1.0.0
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Text Domain: miniso-ayuda-visual
 */

if (!defined('ABSPATH')) { exit; }

/** Add an exact page marker to avoid modifying unrelated WP screens. */
add_filter('body_class', static function ($classes) {
    if (!is_page()) { return $classes; }
    $page = get_queried_object();
    if ($page instanceof WP_Post && ($page->post_name === 'ayuda' || sanitize_title($page->post_title) === 'ayuda')) {
        $classes[] = 'miniso-faq-help';
    }
    return $classes;
});

/** All page content remains editable in Gutenberg; this plugin only handles style. */
add_action('wp_enqueue_scripts', static function () {
    if (is_admin()) { return; }
    wp_enqueue_style(
        'miniso-faq-unificado',
        plugin_dir_url(__FILE__) . 'assets/faq-unificado.css',
        array(),
        '1.0.0'
    );
    $background = get_theme_mod('miniso_faq_background', '#CFEFFF');
    $accent = get_theme_mod('miniso_faq_accent', '#E60020');
    $cards = get_theme_mod('miniso_faq_cards', '#FFFFFF');
    $background = sanitize_hex_color($background) ?: '#CFEFFF';
    $accent = sanitize_hex_color($accent) ?: '#E60020';
    $cards = sanitize_hex_color($cards) ?: '#FFFFFF';
    wp_add_inline_style('miniso-faq-unificado', ':root{--miniso-faq-bg:' . $background . ';--miniso-faq-accent:' . $accent . ';--miniso-faq-card:' . $cards . ';}');
}, 30);

/** Optional visual controls for the site administrator, not individual content editors. */
add_action('customize_register', static function ($wp_customize) {
    $wp_customize->add_section('miniso_faq_visual', array(
        'title' => 'MINISO · Preguntas y ayuda',
        'priority' => 75,
        'description' => 'Colores globales de Ayuda y de los acordeones reutilizables. Los textos se editan desde cada página.'
    ));
    foreach (array(
        'miniso_faq_background' => array('Fondo pastel', '#CFEFFF'),
        'miniso_faq_cards' => array('Fondo de tarjetas', '#FFFFFF'),
        'miniso_faq_accent' => array('Color de iconos y flechas', '#E60020')
    ) as $id => $props) {
        $wp_customize->add_setting($id, array(
            'default' => $props[1],
            'sanitize_callback' => 'sanitize_hex_color',
            'transport' => 'refresh'
        ));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, $id, array(
            'label' => $props[0],
            'section' => 'miniso_faq_visual',
            'settings' => $id
        )));
    }
});
