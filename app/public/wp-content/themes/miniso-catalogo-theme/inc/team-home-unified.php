<?php
/** MINISO Equipo: portada unificada con datos existentes. */

defined('ABSPATH') || exit;
if (!function_exists('minu_home_page_id')) {

/**
 * Encuentra la página real de Inicio editable sin crear ni cambiar ninguna entrada.
 * Se prioriza el Inicio vinculado al catálogo, luego la portada estática y la página /inicio/.
 */
function minu_home_page_id() {
    if (!function_exists('mct_modular_home_enabled')) return 0;
    $ids = array();
    $catalog_pages = get_option('miniso_catalog_pages', array());
    if (is_array($catalog_pages) && !empty($catalog_pages['inicio'])) {
        $ids[] = absint($catalog_pages['inicio']);
    }
    $ids[] = absint(get_option('page_on_front'));
    $by_slug = get_page_by_path('inicio', OBJECT, 'page');
    if ($by_slug instanceof WP_Post) $ids[] = (int) $by_slug->ID;
    foreach (array_unique(array_filter($ids)) as $id) {
        $page = get_post($id);
        if ($page instanceof WP_Post && $page->post_type === 'page'
            && $page->post_status === 'publish' && mct_modular_home_enabled($id)) {
            return $id;
        }
    }
    return 0;
}

/** La raíz siempre muestra el contenido del mismo Inicio flexible. */
add_filter('template_include', function ($template) {
    if (!is_front_page() || !minu_home_page_id()) return $template;
    $candidate = get_template_directory() . '/templates/team-unified-front.php';
    return file_exists($candidate) ? $candidate : $template;
}, 99);

/** Evita que el menú enlace a una segunda versión /inicio/. */
add_filter('page_link', function ($link, $post_id) {
    $id = minu_home_page_id();
    return $id && (int) $post_id === $id ? home_url('/') : $link;
}, 20, 2);

/**
 * Redirección temporal mientras se prueba el sitio local. El editor y las vistas previas siguen intactos.
 * No se utiliza una redirección permanente para evitar cachés 301 difíciles de revertir.
 */
add_action('template_redirect', function () {
    if (is_admin() || is_preview() || (function_exists('is_customize_preview') && is_customize_preview()) || !is_page()) return;
    $id = minu_home_page_id();
    if (!$id || !is_page($id)) return;
    $current_path = wp_parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $root_path = wp_parse_url(home_url('/'), PHP_URL_PATH);
    if (!is_string($current_path) || !is_string($root_path)) return;
    if (untrailingslashit($current_path) === untrailingslashit($root_path)) return;
    wp_safe_redirect(home_url('/'), 302, 'MINISO Inicio unificado');
    exit;
}, 5);

/**
 * Ajuste visual de Inicio editable: información a la izquierda, video a la derecha.
 * Se carga después del CSS del tema; no altera contenido ni otras secciones.
 */
add_action('wp_enqueue_scripts', function () {
    if (!function_exists('mct_modular_home_enabled')) return;
    wp_enqueue_style(
        'miniso-inicio-unificado-instagram',
        get_template_directory_uri() . '/assets/team-instagram-layout.css',
        array(),
        '1.0.2'
    );
}, 100);

} // Proteccion ante duplicacion con plugins MINISO preexistentes.
