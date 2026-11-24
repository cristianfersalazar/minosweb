<?php
defined( 'ABSPATH' ) || exit;

// FUNCIÓN · La preparación se ejecuta únicamente desde el botón del administrador.
function miniso_catalog_import_official_image( $filename, $title ) {
    $key = 'miniso_official_' . sanitize_key( $filename );
    $existing = absint( get_option( $key ) );
    if ( $existing && wp_attachment_is_image( $existing ) ) return $existing;
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $source = MINISO_CATALOG_DIR . 'official/' . $filename;
    if ( ! is_readable( $source ) ) return new WP_Error( 'miniso_image_missing', 'No se encontró ' . $filename );
    $upload = wp_upload_bits( 'miniso-' . basename($filename), null, file_get_contents( $source ) );
    if ( ! empty( $upload['error'] ) ) return new WP_Error( 'miniso_upload', $upload['error'] );
    $mime = wp_check_filetype( $filename );
    $id = wp_insert_attachment( array( 'post_mime_type' => $mime['type'], 'post_title' => $title, 'post_status' => 'inherit' ), $upload['file'], 0, true );
    if ( is_wp_error( $id ) ) return $id;
    wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
    update_post_meta( $id, '_wp_attachment_image_alt', $title );
    update_option( $key, $id, false );
    return $id;
}
function miniso_catalog_setup( $set_front = false ) {
    $errors = array();
    if ( ! taxonomy_exists( 'pa_licencia' ) ) {
        miniso_catalog_ensure_attribute();
        return new WP_Error( 'miniso_wait', 'El atributo Licencia quedó preparado. Recarga esta página y pulsa de nuevo el botón.' );
    }
    $settings = miniso_catalog_settings();
    $images = array();
    foreach ( array( 'logo.png' => 'MINISO', 'hero.jpg' => 'LIFE IS FOR FUN', 'lover.jpg' => 'MINISO Lover+', 'naruto.png' => 'Colección Naruto × MINISO', 'marvel.png' => 'Colección Marvel × MINISO' ) as $file => $title ) {
        $id = miniso_catalog_import_official_image( $file, $title );
        if ( is_wp_error( $id ) ) { $errors[] = $id->get_error_message(); continue; }
        $images[ pathinfo( $file, PATHINFO_FILENAME ) ] = $id;
    }
    foreach ( array( 'logo', 'hero', 'lover' ) as $key ) if ( ! $settings[ $key ] && ! empty( $images[ $key ] ) ) $settings[ $key ] = $images[ $key ];
    $featured = array();
    foreach ( array( 'naruto' => 'Naruto', 'marvel' => 'Marvel' ) as $slug => $name ) {
        $exists = get_term_by( 'slug', $slug, 'pa_licencia' );
        $term = $exists ? array( 'term_id' => $exists->term_id ) : wp_insert_term( $name, 'pa_licencia', array( 'slug' => $slug, 'description' => 'Explora los productos de la colección ' . $name . ' × MINISO.' ) );
        if ( is_wp_error( $term ) ) { $errors[] = $term->get_error_message(); continue; }
        $id = absint( $term['term_id'] );
        if ( ! get_term_meta( $id, 'miniso_cover_id', true ) && ! empty( $images[ $slug ] ) ) update_term_meta( $id, 'miniso_cover_id', $images[ $slug ] );
        if ( ! get_term_meta( $id, 'miniso_group', true ) ) update_term_meta( $id, 'miniso_group', 'Licencias' );
        $featured[] = $id;
    }
    if ( ! $settings['featured'] ) $settings['featured'] = $featured;
    update_option( 'miniso_catalog_settings', $settings, false );

    $lover = '<p>Conoce las categorías de nuestro programa.</p><div class="tiers"><div class="tier"><h2>PLATA</h2><strong>10%</strong><p>Compras de Q100 a Q299.99.</p></div><div class="tier gold"><h2>ORO</h2><strong>15%</strong><p>Compras de Q300 a Q499.99.</p></div><div class="tier black"><h2>BLACK</h2><strong>20%</strong><p>Compras mayores a Q500.</p></div></div><details><summary>¿Cómo puedo registrarme?</summary><p>Visita una sucursal y consulta con el equipo cómo registrarte.</p></details>';
    $help = '<details><summary>¿Puedo comprar desde esta página?</summary><p>Este catálogo permite conocer las colecciones y sus productos. Para realizar tu compra, visita una sucursal.</p></details><details><summary>¿Cómo veo los productos de una colección?</summary><p>Entra en Licencias y selecciona una portada. Puedes buscar productos, filtrar por categoría y abrir cada artículo para ver su galería.</p></details><details><summary>¿Cómo consulto la disponibilidad?</summary><p>Consulta directamente con tu sucursal. La presencia de un producto en el catálogo no confirma existencias en una tienda.</p></details><details><summary>¿Todas las colecciones tienen descuento Lover+?</summary><p>Consulta en tienda las condiciones del programa y las exclusiones de promociones y nuevos ingresos.</p></details>';
    $definitions = array(
        'inicio' => array( 'Inicio MINISO', 'templates/inicio.php', '' ),
        'licencias' => array('Licencias','templates/licencias.php','<p>Encuentra tu colección favorita y descubre todos sus productos.</p>'),
        'novedades' => array( 'Novedades', 'templates/novedades.php', '<p>Lanzamientos, promociones y noticias de MINISO Guatemala.</p>' ),
        'sucursales' => array( 'Sucursales', 'templates/sucursales.php', '<p>Encuentra tu sucursal y consulta directamente los horarios y la disponibilidad.</p>' ),
        'lover' => array( 'Lover+', '', $lover ),
        'ayuda' => array( 'Ayuda', '', $help ),
        'favoritos' => array( 'Mis favoritos', 'templates/favoritos.php', '<p>Las colecciones que guardaste en este navegador.</p>' ),
        'catalogo' => array( 'Catálogo', '', '' ),
    );
    $pages = get_option( 'miniso_catalog_pages', array() );
    foreach ( $definitions as $key => $page ) {
        if ( ! empty( $pages[ $key ] ) && get_post( $pages[ $key ] ) && 'trash' !== get_post_status( $pages[ $key ] ) ) continue;
        $id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $page[0], 'post_name' => $key, 'post_content' => $page[2], 'meta_input' => array( '_wp_page_template' => $page[1] ?: 'default' ) ), true );
        if ( is_wp_error( $id ) ) { $errors[] = $id->get_error_message(); continue; }
        $pages[ $key ] = $id;
    }
    update_option( 'miniso_catalog_pages', $pages, false );
    if ( ! get_option( 'woocommerce_shop_page_id' ) && ! empty( $pages['catalogo'] ) ) update_option( 'woocommerce_shop_page_id', $pages['catalogo'] );
    if ( $set_front && ! empty( $pages['inicio'] ) ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $pages['inicio'] );
    }
    $stores = array(
        'capital' => array( 'Miraflores', 'Oakland Place', 'Pradera Concepción', 'Sankris Mall', 'La Estación', 'Plaza Vidú', 'Metrocentro', 'Parque las Americas', 'Portales', 'Pradera zona 10', 'Vidére', 'Naranjo Mall', 'Pradera vistares', 'Spazio', 'Pacific Center', 'Santa Clara', 'Aeropuerto La Aurora' ),
        'interior' => array( 'Pradera Xela', 'Chimaltenango', 'Telares', 'Zona OX', '4 Caminos', 'Quiche', 'Las Alturas', 'Los Celajes', 'Mazatenango', 'Malacatan', 'Pradera Escuintla', 'Interplaza Escuintla' ),
    );
    foreach ( $stores as $region => $names ) foreach ( $names as $name ) {
        $existing = get_posts( array( 'post_type' => 'miniso_sucursal', 'post_status' => array( 'publish', 'draft', 'private', 'pending' ), 'name' => sanitize_title( $name ), 'numberposts' => 1, 'fields' => 'ids' ) );
        if ( $existing ) continue;
        $id = wp_insert_post( array( 'post_type' => 'miniso_sucursal', 'post_status' => 'publish', 'post_title' => $name, 'post_name' => sanitize_title( $name ), 'meta_input' => array( 'miniso_region' => $region ) ), true );
        if ( is_wp_error( $id ) ) $errors[] = $id->get_error_message();
    }
    if (function_exists('miniso_catalog_upgrade_experience')) miniso_catalog_upgrade_experience();
    update_option( 'miniso_catalog_setup_errors', $errors, false );
    flush_rewrite_rules( false );
    return $pages;
}
add_action( 'admin_post_miniso_catalog_setup', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'La preparación inicial requiere un administrador.' );
    check_admin_referer( 'miniso_catalog_setup' );
    $result = miniso_catalog_setup( ! empty( $_POST['set_front'] ) );
    if ( is_wp_error( $result ) ) update_option( 'miniso_catalog_setup_errors', array( $result->get_error_message() ), false );
    wp_safe_redirect( admin_url( 'admin.php?page=miniso-catalogo&miniso_saved=1' ) );
    exit;
} );
