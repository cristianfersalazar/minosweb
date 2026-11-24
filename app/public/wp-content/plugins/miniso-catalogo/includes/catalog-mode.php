<?php
defined( 'ABSPATH' ) || exit;

// FUNCIÓN · Bloqueo real de compra, también con enlaces directos y solicitudes del carrito.
add_filter( 'woocommerce_is_purchasable', '__return_false', PHP_INT_MAX );
add_filter( 'woocommerce_variation_is_purchasable', '__return_false', PHP_INT_MAX );
add_filter( 'woocommerce_add_to_cart_validation', '__return_false', PHP_INT_MAX );
add_filter( 'woocommerce_order_button_html', '__return_empty_string', PHP_INT_MAX );
add_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string', PHP_INT_MAX );
add_filter( 'woocommerce_get_price_html', '__return_empty_string', PHP_INT_MAX );
add_filter( 'woocommerce_available_payment_gateways', '__return_empty_array', PHP_INT_MAX );
add_action( 'woocommerce_checkout_process', function () {
    wc_add_notice( 'Este sitio funciona como catálogo. Consulta en una sucursal.', 'error' );
} );
add_action( 'woocommerce_store_api_validate_add_to_cart', function () {
    throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
        'miniso_catalog_only', 'Las compras en línea no están habilitadas.', 403
    );
}, PHP_INT_MAX );
// Bloquea escrituras en carrito/checkout de Store API; no bloquea la API administrativa.
add_filter( 'rest_pre_dispatch', function ( $result, $server, $request ) {
    if ( preg_match( '#^/wc/store/v[0-9]+/(cart|checkout)(/|$)#', $request->get_route() )
        && ! in_array( $request->get_method(), array( 'GET', 'HEAD', 'OPTIONS' ), true ) ) {
        return new WP_Error( 'miniso_catalog_only', 'Las compras en línea no están habilitadas.', array( 'status' => 403 ) );
    }
    return $result;
}, PHP_INT_MAX, 3 );
add_action( 'template_redirect', function () {
    if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
        wp_safe_redirect( miniso_catalog_page_url( 'novedades' ) );
        exit;
    }
} );
add_filter( 'woocommerce_structured_data_product', function ( $data ) {
    unset( $data['offers'] );
    return $data;
} );
