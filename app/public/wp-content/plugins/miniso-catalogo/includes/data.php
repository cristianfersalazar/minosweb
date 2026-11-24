<?php
defined( 'ABSPATH' ) || exit;

function miniso_catalog_ensure_attribute() {
    if ( ! function_exists( 'wc_create_attribute' ) ) return;
    $id = wc_attribute_taxonomy_id_by_name( 'licencia' );
    if ( ! $id ) {
        $id = wc_create_attribute( array( 'name' => 'Licencia', 'slug' => 'licencia', 'type' => 'select', 'order_by' => 'name', 'has_archives' => true ) );
    } else {
        // Conserva el atributo existente; habilita sus páginas públicas de colección.
        $attribute = wc_get_attribute( $id );
        if ( $attribute && ! $attribute->has_archives ) wc_update_attribute( $id, array( 'has_archives' => true ) );
    }
    if ( ! is_wp_error( $id ) && $id ) delete_option( 'miniso_catalog_needs_attribute' );
    return $id;
}
add_action( 'init', function () {
    if ( get_option( 'miniso_catalog_needs_attribute' ) ) miniso_catalog_ensure_attribute();
}, 0 );

add_action( 'init', function () {
    register_post_type( 'miniso_sucursal', array(
        'labels' => array( 'name' => 'Sucursales MINISO', 'singular_name' => 'Sucursal', 'add_new_item' => 'Agregar sucursal', 'edit_item' => 'Editar sucursal' ),
        'public' => false, 'show_ui' => true, 'show_in_rest' => true,
        'show_in_menu' => 'woocommerce', 'supports' => array( 'title', 'editor', 'page-attributes' ),
        'capability_type' => 'post', 'map_meta_cap' => true,
        'capabilities' => array(
            'edit_posts' => 'manage_woocommerce', 'edit_others_posts' => 'manage_woocommerce',
            'publish_posts' => 'manage_woocommerce', 'read_private_posts' => 'manage_woocommerce',
            'delete_posts' => 'manage_woocommerce', 'delete_private_posts' => 'manage_woocommerce',
            'delete_published_posts' => 'manage_woocommerce', 'delete_others_posts' => 'manage_woocommerce',
            'edit_private_posts' => 'manage_woocommerce', 'edit_published_posts' => 'manage_woocommerce',
            'create_posts' => 'manage_woocommerce',
        ),
    ) );
    if ( taxonomy_exists( 'pa_licencia' ) ) {
        register_term_meta( 'pa_licencia', 'miniso_cover_id', array( 'type' => 'integer', 'single' => true, 'sanitize_callback' => 'absint' ) );
        register_term_meta( 'pa_licencia', 'miniso_group', array( 'type' => 'string', 'single' => true, 'sanitize_callback' => 'sanitize_text_field' ) );
    }
}, 20 );

add_action( 'created_pa_licencia', function ( $id ) {
    if ( ! get_term_meta( $id, 'miniso_group', true ) ) update_term_meta( $id, 'miniso_group', 'Licencias' );
} );

// FUNCIÓN · Búsqueda por texto, SKU y código de barras, limitada a nuestras consultas.
add_filter( 'posts_search', function ( $search, $query ) {
    if ( ! $query->get( 'miniso_catalog_search' ) ) return $search;
    global $wpdb;
    $text = (string) $query->get( 's' );
    if ( '' === $text ) return $search;
    $like = '%' . $wpdb->esc_like( $text ) . '%';
    return $wpdb->prepare(
        " AND ({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_content LIKE %s OR EXISTS (
            SELECT 1 FROM {$wpdb->postmeta} mc_search WHERE mc_search.post_id = {$wpdb->posts}.ID
            AND mc_search.meta_key IN ('_sku','_global_unique_id') AND mc_search.meta_value LIKE %s
        )) AND {$wpdb->posts}.post_password = '' ", $like, $like, $like
    );
}, 10, 2 );

function miniso_catalog_visibility_query() {
    if ( ! function_exists( 'wc_get_product_visibility_term_ids' ) ) return array();
    $visibility = wc_get_product_visibility_term_ids();
    $excluded = array_filter( array( $visibility['exclude-from-catalog'] ?? 0 ) );
    if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! empty( $visibility['outofstock'] ) ) $excluded[] = $visibility['outofstock'];
    return $excluded ? array( array( 'taxonomy' => 'product_visibility', 'field' => 'term_taxonomy_id', 'terms' => $excluded, 'operator' => 'NOT IN' ) ) : array();
}
