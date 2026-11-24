<?php
defined( 'ABSPATH' ) || exit;
add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'woocommerce' );
} );
add_action( 'wp_enqueue_scripts', function () {
    $version = wp_get_theme()->get( 'Version' );
    wp_enqueue_style( 'miniso-catalogo', get_template_directory_uri() . '/assets/catalogo.css', array(), $version );
    wp_enqueue_style( 'miniso-catalog-groups',get_template_directory_uri().'/assets/catalog-groups.css',array('miniso-catalogo'),$version );
    wp_enqueue_script( 'miniso-catalogo', get_template_directory_uri() . '/assets/catalogo.js', array(), $version, array( 'strategy' => 'defer', 'in_footer' => true ) );
    wp_enqueue_script('miniso-experience',get_template_directory_uri().'/assets/experience.js',array('miniso-catalogo'),$version,array('strategy'=>'defer','in_footer'=>true));
    wp_localize_script( 'miniso-catalogo', 'minisoCatalog', array( 'storageKey' => 'miniso-colecciones-' . md5( home_url() ) ) );
} );
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

function mct_ready() { return function_exists( 'miniso_catalog_settings' ) && function_exists('miniso_design') && function_exists( 'wc_get_product' ); }
function mct_settings() { return mct_ready() ? miniso_catalog_settings() : array(); }
function mct_url( $key ) { return function_exists( 'miniso_catalog_page_url' ) ? miniso_catalog_page_url( $key ) : home_url( '/' ); }
/**
 * Detecta solo la página Lover+ configurada en WooCommerce → MINISO Catálogo.
 * El fallback por slug cubre instalaciones que aún no han guardado el enlace de página.
 */
function mct_is_lover_page() {
    if ( ! is_page() ) return false;
    $pages = get_option( 'miniso_catalog_pages', array() );
    if ( is_array( $pages ) && ! empty( $pages['lover'] ) && is_page( absint( $pages['lover'] ) ) ) return true;
    $page = get_queried_object();
    return $page instanceof WP_Post
        && 'page' === $page->post_type
        && in_array( $page->post_name, array( 'lover', 'lover-plus', 'miniso-lover', 'miniso-lover-plus' ), true );
}
function mct_get( $name ) { return isset( $_GET[ $name ] ) && is_scalar( $_GET[ $name ] ) ? sanitize_text_field( wp_unslash( $_GET[ $name ] ) ) : ''; }
function mct_icon( $name ) {
    $paths = array(
        'inicio' => '<path d="M3 10 12 3l9 7M5 9v12h14V9M9 21v-7h6v7"/>',
        'sucursales' => '<path d="M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'novedades' => '<path d="m20 13-7 7a2 2 0 0 1-2.8 0L3 12.8V3h9.8L20 10.2a2 2 0 0 1 0 2.8Z"/><circle cx="7.5" cy="7.5" r="1.3"/>',
        'lover' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/>',
        'ayuda' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 0 1 5 0c0 1.5-2.5 2-2.5 4M12 16.5h.01"/>',
        'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/>',
        'licencias' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'prev' => '<path d="m14 6-6 6 6 6"/>',
        'next' => '<path d="m10 6 6 6-6 6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'pause' => '<path d="M9 5v14M15 5v14"/>',
        'more' => '<circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".7" fill="currentColor"/>',
        'facebook' => '<path d="M14 22V13h3l.5-4H14V7c0-1.2.3-2 2-2h2V1h-3c-4 0-5 2.5-5 6v2H7v4h3v9"/>',
        'tiktok' => '<path d="M14 3v12a5 5 0 1 1-5-5v4a1 1 0 1 0 1 1V3h4c1 3 2 4 5 4v4c-2 0-4-1-5-2"/>',
        'image' => '<rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 5-5 4 4 3-3 6 6"/>',
    );
    return '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true">' . ( $paths[ $name ] ?? $paths['image'] ) . '</svg>';
}
function mct_media( $id, $alt, $class = '', $eager = false ) {
    $image = $id ? wp_get_attachment_image( absint( $id ), 'full', false, array( 'alt' => $alt, 'loading' => $eager ? 'eager' : 'lazy', 'decoding' => 'async' ) ) : '';
    echo '<div class="media ' . esc_attr( $class ) . '">';
    if ( $image ) echo $image;
    else echo '<div class="media-fallback">' . mct_icon( 'image' ) . '<span>Fotografía pendiente</span></div>';
    echo '</div>';
}
function mct_navigation( $mobile = false ) {
    $keys = $mobile ? array('inicio','licencias','novedades','sucursales') : array('inicio','licencias','novedades','sucursales','lover','ayuda');
    $pages = get_option('miniso_catalog_pages',array());
    foreach($keys as $key){
        $active=(!empty($pages[$key]) && is_page($pages[$key])) || ('inicio'===$key && is_front_page());
        if ('licencias'===$key && ((function_exists('is_woocommerce') && is_woocommerce()) || is_tax(array('miniso_campaign','miniso_season'))))$active=true;
        if ('novedades'===$key && is_singular('miniso_novedad'))$active=true;
        $label=mct_design('nav_'.$key) ?: ucfirst($key);
        if ('licencias' === $key && in_array(trim($label),array('Licencias','licencias'),true)) $label = 'Catálogo';
        if ($mobile && 'sucursales'===$key && 'Sucursales'===$label)$label='Tiendas';
        echo '<a href="'.esc_url(mct_url($key)).'"'.($active?' class="active" aria-current="page"':'').'>'.($mobile?mct_icon($key):'').'<span>'.esc_html($label).'</span></a>';
    }
}
function mct_dependency_notice() {
    echo '<h1>Catálogo en preparación</h1>';
    if ( current_user_can( 'activate_plugins' ) ) echo '<p>Activa WooCommerce y actualiza MINISO Catálogo a la versión 2. Después completa WooCommerce → MINISO Catálogo.</p>';
}
function mct_form_hidden( $url ) {
    $query = wp_parse_url( $url, PHP_URL_QUERY );
    if ( ! $query ) return;
    parse_str( $query, $args );
    foreach ( $args as $key => $value ) if ( is_scalar( $value ) ) echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
}
function mct_product_card( $product ) {
    $url = $product->get_permalink();
    echo '<article class="card product-card"><a href="' . esc_url( $url ) . '" aria-label="Ver ' . esc_attr( $product->get_name() ) . '">';
    mct_media( $product->get_image_id(), $product->get_name(), 'product-image' );
    echo '</a><div class="card-body"><p class="sku">SKU: ' . esc_html( $product->get_sku() ?: 'Por confirmar' ) . '</p><h3><a href="' . esc_url( $url ) . '">' . esc_html( $product->get_name() ) . '</a></h3>';
    echo mct_catalog_sale_price_html($product);
    echo '<div class="card-actions"><a class="button light" href="' . esc_url( $url ) . '">'.esc_html(mct_copy_text('product_card_button')).'</a></div></div></article>';
}
function mct_render_archive() {
    if ( ! mct_ready() ) { mct_dependency_notice(); return; }
    $term = is_tax(array('pa_licencia','miniso_campaign','miniso_season')) ? get_queried_object() : null;
    $archive_term = ($term instanceof WP_Term || (function_exists('is_product_taxonomy') && is_product_taxonomy())) ? get_queried_object() : null;
    $base = $archive_term instanceof WP_Term ? get_term_link( $archive_term ) : wc_get_page_permalink( 'shop' );
    if ( is_wp_error( $base ) ) $base = home_url( '/' );
    echo '<nav class="breadcrumb" aria-label="Ruta"><a href="' . esc_url( mct_url( 'licencias' ) ) . '">Catálogo</a><span aria-hidden="true">/</span><span>' . esc_html( $archive_term->name ?? 'Todos los productos' ) . '</span></nav>';
    if ( $term ) {
        echo '<div class="collection-intro" style="'.esc_attr(mct_accent_style(mct_catalog_term_color($term))).'">';
        if (mct_catalog_term_image($term)) mct_media(mct_catalog_term_image($term), $term->name, 'cover', true);
        $kind = 'miniso_campaign'===$term->taxonomy ? 'Promoción' : ('miniso_season'===$term->taxonomy ? 'Temporada' : 'Colección');
        echo '<div><span class="tag">'.esc_html($kind).'</span><h1>' . esc_html( $term->name ) . '</h1><p>' . esc_html( $term->description ) . '</p></div></div>';
    } else echo '<h1>' . esc_html( $archive_term->name ?? 'Catálogo de productos' ) . '</h1>';
    $category = absint( mct_get( 'mc_categoria' ) );
    $subcategory = absint( mct_get( 'mc_subcategoria' ) );
    $category_term = $category ? get_term( $category, 'product_cat' ) : null;
    if ( ! $category_term || is_wp_error( $category_term ) || $category_term->parent ) $category = 0;
    if ( $subcategory ) {
        $child = get_term( $subcategory, 'product_cat' );
        if ( ! $category || ! $child || is_wp_error( $child ) || (int) $child->parent !== $category ) $subcategory = 0;
    }
    echo '<form method="get" action="' . esc_url( $base ) . '" class="toolbar">';
    mct_form_hidden( $base );
    echo '<label class="sr-only" for="mc-buscar">Buscar producto, SKU o código de barras</label><input type="search" id="mc-buscar" name="mc_buscar" placeholder="Buscar producto o código" value="' . esc_attr( mct_get( 'mc_buscar' ) ) . '">';
    foreach ( array( 'mc_categoria' => array( 'Todas las categorías', 0, $category ), 'mc_subcategoria' => array( 'Todas las subcategorías', $category, $subcategory ) ) as $key => $args ) {
        echo '<label class="sr-only" for="' . esc_attr( $key ) . '">' . esc_html( $args[0] ) . '</label><select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '"><option value="0">' . esc_html( $args[0] ) . '</option>';
        $terms = ( 'mc_subcategoria' === $key && ! $category ) ? array() : get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => $args[1] ) );
        if ( ! is_wp_error( $terms ) ) foreach ( $terms as $item ) echo '<option value="' . absint( $item->term_id ) . '" ' . selected( $args[2], $item->term_id, false ) . '>' . esc_html( $item->name ) . '</option>';
        echo '</select>';
    }
    echo '<button class="button" type="submit">Buscar</button><a class="text-link clear-filters" href="' . esc_url( $base ) . '">Limpiar</a></form>';
    $tax = miniso_catalog_visibility_query();
    if (!is_array($tax)) $tax = array();
    if ( $archive_term instanceof WP_Term ) $tax[] = array( 'taxonomy' => $archive_term->taxonomy, 'field' => 'term_id', 'terms' => $archive_term->term_id );
    if ( $category ) $tax[] = array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $subcategory ?: $category );
    $s = mct_settings();
    $page = max( 1, absint( mct_get( 'mc_page' ) ) );
    $query_args = array( 'post_type' => 'product', 'post_status' => 'publish', 'has_password' => false, 'posts_per_page' => max(1,absint($s['page_size'] ?? 16)), 'paged' => $page, 's' => mct_get( 'mc_buscar' ), 'miniso_catalog_search' => true, 'tax_query' => $tax, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
    if ($term instanceof WP_Term && in_array($term->taxonomy,array('miniso_campaign','miniso_season'),true) && !mct_catalog_term_is_current($term)) $query_args['post__in'] = array(0);
    if ($term instanceof WP_Term && $term->taxonomy === 'miniso_campaign') {
        $on_sale = function_exists('wc_get_product_ids_on_sale') ? array_map('absint', wc_get_product_ids_on_sale()) : array();
        $query_args['post__in'] = empty($on_sale) ? array(0) : array_values(array_unique($on_sale));
        if (!mct_catalog_term_is_current($term)) $query_args['post__in'] = array(0);
    }
    $query = new WP_Query($query_args);
    echo '<p class="muted">' . absint( $query->found_posts ) . ( 1 === (int) $query->found_posts ? ' producto' : ' productos' ) . '</p><div class="grid product-grid">';
    foreach ( $query->posts as $post ) {
        $product = wc_get_product( $post->ID );
        if ( $product ) mct_product_card( $product );
    }
    if ( ! $query->posts ) echo '<div class="empty"><h2>No hay productos para mostrar</h2><p>Revisa los filtros o consulta otros productos disponibles en el catálogo.</p></div>';
    echo '</div>';
    $args = array( 'mc_buscar' => mct_get( 'mc_buscar' ), 'mc_categoria' => $category, 'mc_subcategoria' => $subcategory, 'mc_page' => 999999 );
    echo '<nav class="pagination" aria-label="Páginas de productos">' . wp_kses_post( paginate_links( array( 'base' => str_replace( '999999', '%#%', add_query_arg( $args, $base ) ), 'format' => '', 'current' => $page, 'total' => $query->max_num_pages, 'prev_text' => 'Anterior', 'next_text' => 'Siguiente' ) ) ) . '</nav>';
    wp_reset_postdata();
}

function mct_render_product() {
    if ( post_password_required() ) { echo get_the_password_form(); return; }
    $product = wc_get_product( get_queried_object_id() );
    if ( ! $product ) { echo '<h1>Producto no encontrado</h1>'; return; }
    $licenses = get_the_terms( $product->get_id(), 'pa_licencia' );
    $licenses = is_array( $licenses ) ? $licenses : array();
    echo '<nav class="breadcrumb" aria-label="Ruta"><a href="' . esc_url( mct_url( 'licencias' ) ) . '">Catálogo</a>';
    if ( $licenses ) echo '<span>/</span><a href="' . esc_url( get_term_link( $licenses[0] ) ) . '">' . esc_html( $licenses[0]->name ) . '</a>';
    echo '<span>/</span><span aria-current="page">' . esc_html( $product->get_name() ) . '</span></nav><div class="product-detail">';
    $images = array_values( array_unique( array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) ) ) );
    echo '<section class="gallery" data-gallery aria-label="Galería del producto" tabindex="0">';
    if ( ! $images ) mct_media( 0, '', 'gallery-main' );
    foreach ( $images as $i => $id ) {
        echo '<div data-gallery-slide' . ( $i ? ' hidden' : '' ) . '>';
        mct_media( $id, $product->get_name() . ' · Vista ' . ( $i + 1 ), 'gallery-main', 0 === $i );
        echo '</div>';
    }
    if ( count( $images ) > 1 ) echo '<div class="gallery-controls js-only"><button type="button" class="icon-button" data-gallery-prev aria-label="Imagen anterior">←</button><p data-gallery-count aria-live="polite">1 / ' . count( $images ) . '</p><button type="button" class="icon-button" data-gallery-next aria-label="Imagen siguiente">→</button></div>';
    echo '<div class="gallery-thumbs">';
    foreach ( $images as $i => $id ) {
        echo '<a class="thumbnail" data-gallery-thumb="' . $i . '" href="' . esc_url( wp_get_attachment_url( $id ) ) . '" aria-label="Ver imagen ' . ( $i + 1 ) . '"' . ( 0 === $i ? ' aria-current="true"' : '' ) . '>';
        mct_media( $id, $product->get_name() . ' · Vista ' . ( $i + 1 ) );
        echo '<span class="thumbnail-number">' . ( $i + 1 ) . '</span></a>';
    }
    echo '</div></section><div class="product-copy"><span class="tag">' . esc_html( implode( ' · ', wp_list_pluck( $licenses, 'name' ) ) ) . '</span><h1>' . esc_html( $product->get_name() ) . '</h1>';
    echo mct_catalog_sale_price_html($product);
    echo '<div class="product-description">' . wp_kses_post( wpautop( $product->get_description() ?: $product->get_short_description() ) ) . '</div><dl class="product-meta"><dt>SKU</dt><dd>' . esc_html( $product->get_sku() ?: 'Por confirmar' ) . '</dd>';
    $barcode = method_exists( $product, 'get_global_unique_id' ) ? $product->get_global_unique_id() : '';
    if ( $barcode ) echo '<dt>Código de barras</dt><dd>' . esc_html( $barcode ) . '</dd>';
    $categories = wc_get_product_category_list( $product->get_id(), ', ' );
    if ( $categories ) echo '<dt>Categorías</dt><dd>' . wp_kses_post( $categories ) . '</dd>';
    echo '</dl><p class="product-note">'.esc_html(mct_copy_text('product_note')).'</p><a class="button" href="' . esc_url( mct_url( 'sucursales' ) ) . '">'.esc_html(mct_copy_text('product_store_button')).'</a></div></div>';
}

require_once get_template_directory().'/inc/catalog-groups.php';
require_once get_template_directory().'/inc/customizer.php';
require_once get_template_directory().'/inc/presentation.php';

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('miniso-global-sections', get_template_directory_uri() . '/assets/global-sections.css', array('miniso-catalogo'), wp_get_theme()->get('Version'));
}, 25);

// Módulos editoriales globales para todas las páginas y publicaciones.
require_once get_template_directory() . "/inc/global-sections.php";

// Constructor de portada con módulos reales y configurables.
require_once get_template_directory() . "/inc/modular-home.php";
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('mct-modular-home',get_template_directory_uri().'/assets/modular-home.css',array('miniso-global-sections'),wp_get_theme()->get('Version'));},30);

require_once get_template_directory().'/inc/structure-controls.php';

require_once get_template_directory().'/inc/home-migration.php';

// Sección nativa de talento y oportunidades profesionales MINISO (v2.6).
require_once get_template_directory() . '/inc/careers.php';

// Edición centralizada de textos sin sustituir los datos guardados.
require_once get_template_directory() . '/inc/copy-editor.php';

// MINISO Equipo 3.0: complementos personalizados integrados en el tema.
// WooCommerce y el complemento base del catálogo siguen siendo dependencias independientes.
// Plugins existentes se cargan antes del tema. No registrar sus APIs por segunda vez.
// La comprobación debe realizarse ANTES de require_once: un return al principio de un
// PHP incluido no detiene la declaración anticipada de funciones no condicionales.
if (!function_exists('mnv_news_types') && !function_exists('mnv_news_card')) {
    require_once get_template_directory() . '/inc/team-novedades.php';
}
if (!function_exists('mac_is_help_page')) {
    require_once get_template_directory() . '/inc/team-help-categories.php';
}
require_once get_template_directory() . '/inc/team-help-visual.php';
require_once get_template_directory() . '/inc/team-contrast.php';
if (!function_exists('msr_is_home_view')) {
    require_once get_template_directory() . '/inc/team-stores-rotation.php';
}
if (!function_exists('minu_home_page_id')) {
    require_once get_template_directory() . '/inc/team-home-unified.php';
}
require_once get_template_directory() . '/inc/team-center.php';
