<?php
/**
 * Plugin Name: MINISO · Poppins y movimiento
 * Description: Fuente Poppins local, colores y animaciones suaves para el tema MINISO Catálogo.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * License: GPL-2.0-or-later
 * Text Domain: miniso-estilo-poppins
 */
defined( 'ABSPATH' ) || exit;

// FUNCIÓN · Solo añade presentación al tema MINISO Catálogo o a un tema hijo suyo.
function miniso_poppins_active_theme() {
    return 'miniso-catalogo-theme' === get_template();
}

add_filter( 'body_class', function ( $classes ) {
    if ( miniso_poppins_active_theme() ) {
        $classes[] = 'miniso-poppins';
        if ( get_theme_mod( 'miniso_poppins_motion', true ) ) $classes[] = 'miniso-motion';
    }
    return $classes;
} );

add_action( 'wp_enqueue_scripts', function () {
    if ( ! miniso_poppins_active_theme() ) return;
    $url = plugin_dir_url( __FILE__ );
    $deps = wp_style_is( 'miniso-catalogo', 'registered' ) ? array( 'miniso-catalogo' ) : array();
    wp_enqueue_style( 'miniso-poppins', $url . 'assets/poppins.css', $deps, '1.0.0' );
    $primary = sanitize_hex_color( get_theme_mod( 'miniso_poppins_primary', '#e60020' ) ) ?: '#e60020';
    $secondary = sanitize_hex_color( get_theme_mod( 'miniso_poppins_secondary', '#007a91' ) ) ?: '#007a91';
    wp_add_inline_style( 'miniso-poppins', 'body.miniso-poppins{--mp-primary:' . $primary . ';--mp-secondary:' . $secondary . ';}' );
    if ( get_theme_mod( 'miniso_poppins_motion', true ) ) {
        wp_enqueue_script( 'miniso-poppins-motion', $url . 'assets/motion.js', array(), '1.0.0', array( 'strategy' => 'defer', 'in_footer' => true ) );
    }
}, 30 );

// FUNCIÓN · Precarga únicamente la fuente de lectura, servida desde tu WordPress.
add_filter( 'wp_preload_resources', function ( $resources ) {
    if ( miniso_poppins_active_theme() ) {
        $resources[] = array( 'href' => plugin_dir_url( __FILE__ ) . 'assets/fonts/poppins-regular.woff', 'as' => 'font', 'type' => 'font/woff', 'crossorigin' => 'anonymous' );
    }
    return $resources;
} );

// EDITAR · Estos mismos ajustes están en Apariencia → Personalizar.
add_action( 'customize_register', function ( $customizer ) {
    if ( ! miniso_poppins_active_theme() ) return;
    $customizer->add_section( 'miniso_poppins', array(
        'title' => 'MINISO · Poppins y movimiento',
        'description' => 'Poppins se aplica a la página pública. Ajusta los colores y el movimiento; los artes y logotipos conservan su imagen original.',
        'priority' => 35,
    ) );
    foreach ( array(
        'primary' => array( 'Color principal: títulos y botones', '#e60020' ),
        'secondary' => array( 'Color secundario: beneficios', '#007a91' ),
    ) as $key => $data ) {
        $setting = 'miniso_poppins_' . $key;
        $customizer->add_setting( $setting, array( 'default' => $data[1], 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh' ) );
        $customizer->add_control( new WP_Customize_Color_Control( $customizer, $setting, array( 'label' => $data[0], 'section' => 'miniso_poppins' ) ) );
    }
    $customizer->add_setting( 'miniso_poppins_motion', array( 'default' => true, 'sanitize_callback' => 'rest_sanitize_boolean', 'transport' => 'refresh' ) );
    $customizer->add_control( 'miniso_poppins_motion', array( 'label' => 'Activar animaciones suaves', 'description' => 'También se respeta la preferencia de reducir movimiento del dispositivo.', 'section' => 'miniso_poppins', 'type' => 'checkbox' ) );
} );

add_action( 'admin_notices', function () {
    if ( ! current_user_can( 'activate_plugins' ) || miniso_poppins_active_theme() ) return;
    echo '<div class="notice notice-info"><p>MINISO · Poppins y movimiento: activa el tema MINISO Catálogo para aplicar este diseño.</p></div>';
} );
