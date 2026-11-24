<?php
/**
 * Plugin Name: MINISO · Contraste rojo
 * Description: Adapta el texto de los encabezados sobre fondos rojos MINISO sin alterar tarjetas blancas ni fondos pastel. Ajustable desde Apariencia > Personalizar.
 * Version: 1.1.0
 * Requires PHP: 7.4
 * Text Domain: miniso-contraste-rojo
 */
if (!defined('ABSPATH')) { exit; }

add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_section('miniso_red_text_section', array(
        'title' => 'MINISO · Texto sobre rojo',
        'description' => 'Aplica a encabezados de las secciones con fondo rojo. No altera las tarjetas blancas ni las secciones pastel.',
        'priority' => 42,
    ));
    $wp_customize->add_setting('miniso_red_text_mode', array(
        'type' => 'option', 'default' => 'white', 'transport' => 'refresh',
        'sanitize_callback' => function($value) {
            return in_array($value, array('white','black','off'), true) ? $value : 'white';
        },
        'capability' => 'edit_theme_options',
    ));
    $wp_customize->add_control('miniso_red_text_mode', array(
        'type' => 'select', 'section' => 'miniso_red_text_section',
        'label' => 'Color del texto sobre secciones rojas',
        'choices' => array(
            'white' => 'Blanco (recomendado)',
            'black' => 'Negro',
            'off' => 'Usar estilo original del tema',
        ),
    ));
});

add_action('wp_enqueue_scripts', function () {
    $version = '1.1.0';
    wp_enqueue_style('miniso-red-text-contrast', plugins_url('assets/contraste.css', __FILE__), array(), $version);
    wp_enqueue_script('miniso-red-text-contrast', plugins_url('assets/contraste.js', __FILE__), array(), $version, true);
    wp_add_inline_script('miniso-red-text-contrast', 'window.minisoRedTextMode = ' . wp_json_encode(get_option('miniso_red_text_mode', 'white')) . ';', 'before');
}, 100);
