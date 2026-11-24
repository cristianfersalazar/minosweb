<?php
defined( 'ABSPATH' ) || exit;

// EDITAR · Equivalencias de las ocho columnas de tu Excel con WooCommerce.
function miniso_catalog_csv_columns() {
    return array(
        'Nombre del Producto' => 'name', 'SKU' => 'sku',
        'Codigo de barra' => 'global_unique_id', 'Código de barra' => 'global_unique_id',
        'Código de barras' => 'global_unique_id', 'Descripción' => 'description', 'Descripcion' => 'description',
        'Licencia' => 'miniso_license', 'Categoria' => 'miniso_category', 'Categoría' => 'miniso_category',
        'Subcategoria' => 'miniso_subcategory', 'Subcategoría' => 'miniso_subcategory',
        'Imágenes del producto' => 'images', 'Imagenes del producto' => 'images',
    );
}
add_filter( 'woocommerce_csv_product_import_mapping_options', function ( $options ) {
    return array_merge( $options, array( 'miniso_license' => 'MINISO: Licencia', 'miniso_category' => 'MINISO: Categoría', 'miniso_subcategory' => 'MINISO: Subcategoría' ) );
} );
add_filter( 'woocommerce_csv_product_import_mapping_default_columns', function ( $columns ) {
    return array_merge( $columns, miniso_catalog_csv_columns() );
} );

function miniso_catalog_import_term( $name, $taxonomy, $parent = 0 ) {
    $name = trim( sanitize_text_field( $name ) );
    if ( '' === $name ) return 0;
    $exists = term_exists( $name, $taxonomy, $parent );
    if ( ! $exists ) $exists = wp_insert_term( $name, $taxonomy, array( 'parent' => $parent ) );
    if ( is_wp_error( $exists ) ) throw new Exception( $exists->get_error_message() );
    return absint( is_array( $exists ) ? $exists['term_id'] : $exists );
}
add_filter( 'woocommerce_product_import_pre_insert_product_object', function ( $product, $data ) {
    $is_miniso = array_key_exists( 'miniso_license', $data ) || array_key_exists( 'miniso_category', $data ) || array_key_exists( 'miniso_subcategory', $data );
    if ( ! $is_miniso ) return $product;
    if ( ! $product->get_sku() ) throw new Exception( 'La plantilla MINISO necesita un SKU único por producto.' );
    if ( ! $product->is_type( 'simple' ) ) throw new Exception( 'La plantilla MINISO de ocho columnas es para productos simples. Usa la plantilla nativa de WooCommerce para variaciones.' );
    $category = trim( (string) ( $data['miniso_category'] ?? '' ) );
    $subcategory = trim( (string) ( $data['miniso_subcategory'] ?? '' ) );
    if ( $subcategory && ! $category ) throw new Exception( 'Indica la categoría principal cuando incluyas una subcategoría.' );
    if ( $category ) {
        $parent = miniso_catalog_import_term( $category, 'product_cat' );
        $ids = array( $parent );
        if ( $subcategory ) $ids[] = miniso_catalog_import_term( $subcategory, 'product_cat', $parent );
        $product->set_category_ids( $ids );
    }
    $license = trim( (string) ( $data['miniso_license'] ?? '' ) );
    if ( $license ) {
        if ( ! taxonomy_exists( 'pa_licencia' ) ) throw new Exception( 'Completa primero la configuración inicial de MINISO Catálogo y vuelve a importar.' );
        $terms = array();
        foreach ( explode( '|', $license ) as $name ) {
            $term = miniso_catalog_import_term( $name, 'pa_licencia' );
            if ( $term ) $terms[] = $term;
        }
        $attributes = $product->get_attributes();
        $attribute = new WC_Product_Attribute();
        $attribute->set_id( wc_attribute_taxonomy_id_by_name( 'licencia' ) );
        $attribute->set_name( 'pa_licencia' );
        $attribute->set_options( $terms );
        $attribute->set_visible( true );
        $attribute->set_variation( false );
        $attributes['pa_licencia'] = $attribute;
        $product->set_attributes( $attributes );
    }
    // Sin columna Publicado, los artículos nuevos quedan en borrador para revisión.
    // Al actualizar por SKU, conserva el estado que ya tenía el producto.
    if ( ! isset( $data['status'] ) ) {
        $previous = wc_get_product_id_by_sku( $product->get_sku() );
        $status = $previous ? get_post_status( $previous ) : '';
        $product->set_status( in_array( $status, array( 'publish', 'draft', 'private', 'pending' ), true ) ? $status : 'draft' );
    }
    return $product;
}, 20, 2 );

// Exportar con estas columnas permite volver a editar la plantilla en Excel.
add_filter( 'woocommerce_product_export_column_names', 'miniso_catalog_export_columns' );
add_filter( 'woocommerce_product_export_product_default_columns', 'miniso_catalog_export_columns' );
function miniso_catalog_export_columns( $columns ) {
    $columns['miniso_license'] = 'Licencia';
    $columns['miniso_category'] = 'Categoria';
    $columns['miniso_subcategory'] = 'Subcategoria';
    return $columns;
}
add_filter( 'woocommerce_product_export_product_column_miniso_license', function ( $value, $product ) {
    $terms = get_the_terms( $product->get_id(), 'pa_licencia' );
    return $terms && ! is_wp_error( $terms ) ? implode( ' | ', wp_list_pluck( $terms, 'name' ) ) : '';
}, 10, 2 );
function miniso_catalog_export_category( $product, $child ) {
    $terms = get_the_terms( $product->get_id(), 'product_cat' );
    if ( ! $terms || is_wp_error( $terms ) ) return '';
    foreach ( $terms as $term ) {
        if ( $child && $term->parent ) return $term->name;
        if ( ! $child && ! $term->parent ) return $term->name;
    }
    return '';
}
add_filter( 'woocommerce_product_export_product_column_miniso_category', function ( $v, $p ) { return miniso_catalog_export_category( $p, false ); }, 10, 2 );
add_filter( 'woocommerce_product_export_product_column_miniso_subcategory', function ( $v, $p ) { return miniso_catalog_export_category( $p, true ); }, 10, 2 );
