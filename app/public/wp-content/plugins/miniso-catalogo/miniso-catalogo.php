<?php
/**
 * Plugin Name: MINISO Catálogo
 * Description: Portadas por licencia, importación de la plantilla de marketing y catálogo sin compras en línea.
 * Version: 2.1.0
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * WC requires at least: 9.2
 * Text Domain: miniso-catalogo
 * License: GPL-2.0-or-later
 */
defined( 'ABSPATH' ) || exit;
define( 'MINISO_CATALOG_VERSION', '2.1.0' );
define( 'MINISO_CATALOG_DIR', plugin_dir_path( __FILE__ ) );
define( 'MINISO_CATALOG_URL', plugin_dir_url( __FILE__ ) );

// EDITAR · La presentación vive en el tema; estos datos sobreviven a un cambio de tema.
function miniso_catalog_settings() {
    return wp_parse_args( get_option( 'miniso_catalog_settings', array() ), array(
        'logo' => 0, 'hero' => 0, 'hero_mobile' => 0, 'lover' => 0, 'featured' => array(),
        'page_size' => 12, 'location' => 'Guatemala',
        'hero_text' => 'Descubre las colecciones de MINISO y encuentra tu sucursal.',
        'lover_title' => 'Tu programa de beneficios',
        'lover_text' => 'Conoce las categorías y beneficios de MINISO Lover+.',
    ) );
}
function miniso_catalog_page_url( $key ) {
    $pages = get_option( 'miniso_catalog_pages', array() );
    $id = absint( $pages[ $key ] ?? 0 );
    return $id && 'publish' === get_post_status( $id ) ? get_permalink( $id ) : home_url( '/' );
}

require_once MINISO_CATALOG_DIR . 'includes/catalog-mode.php';
require_once MINISO_CATALOG_DIR . 'includes/data.php';
require_once MINISO_CATALOG_DIR . 'includes/import.php';
require_once MINISO_CATALOG_DIR . 'includes/admin.php';
require_once MINISO_CATALOG_DIR . 'includes/setup.php';

register_activation_hook( __FILE__, function () {
    update_option( 'miniso_catalog_needs_attribute', 1, false );
} );
add_action( 'admin_notices', function () {
    if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'activate_plugins' ) ) return;
    if ( ! class_exists( 'WooCommerce' ) ) {
        echo '<div class="notice notice-error"><p>MINISO Catálogo necesita WooCommerce activo.</p></div>';
    } elseif ( ! get_option( 'miniso_catalog_pages' ) ) {
        echo '<div class="notice notice-info"><p>MINISO Catálogo: abre <a href="' . esc_url( admin_url( 'admin.php?page=miniso-catalogo' ) ) . '">la configuración inicial</a> para preparar las páginas y portadas.</p></div>';
    }
} );

require_once MINISO_CATALOG_DIR . 'includes/presentation.php';

require_once MINISO_CATALOG_DIR . 'includes/experience.php';
