<?php
/** MINISO Equipo: sucursales aleatorias en Inicio. */

defined('ABSPATH') || exit;
if (!function_exists('msr_is_home_view')) {

/**
 * El usuario sigue usando la portada editable en / o en /inicio/.
 * Limitar el efecto a esa portada para no alterar el directorio de tiendas.
 */
function msr_is_home_view() {
    return !is_admin() && (is_front_page() || is_page('inicio'));
}

/**
 * Marcar únicamente la consulta realizada por el bloque conectado de sucursales.
 * No modificar las consultas de la página Sucursales ni de otros módulos.
 */
add_filter('pre_render_block', function ($pre_render, $parsed_block) {
    if (msr_is_home_view()
        && ($parsed_block['blockName'] ?? '') === 'miniso/modulo'
        && ($parsed_block['attrs']['kind'] ?? '') === 'sucursales'
        && (($parsed_block['attrs']['enabled'] ?? true) !== false)) {
        $GLOBALS['msr_inside_stores_block'] = true;
    }
    return $pre_render;
}, 10, 2);

/**
 * Cargar todas las sucursales publicadas, no solo las tres primeras.
 * El número de tarjetas visibles sigue dependiendo del módulo configurado en WordPress.
 */
add_action('pre_get_posts', function ($query) {
    if (empty($GLOBALS['msr_inside_stores_block']) || !msr_is_home_view()) return;
    if ($query->get('post_type') !== 'miniso_sucursal') return;
    if ($query->is_main_query()) return;

    $query->set('posts_per_page', -1);
    $query->set('nopaging', true);
});

/**
 * Conservar exactamente el HTML y los estilos del tema. Para usuarios sin JS,
 * las tarjetas configuradas inicialmente siguen visibles como respaldo.
 */
add_filter('render_block', function ($html, $block) {
    if (!msr_is_home_view()
        || ($block['blockName'] ?? '') !== 'miniso/modulo'
        || ($block['attrs']['kind'] ?? '') !== 'sucursales') return $html;

    $GLOBALS['msr_inside_stores_block'] = false;
    if (strpos($html, 'miniso-module-stores') === false) return $html;

    // La cantidad de tarjetas sigue siendo editable en Inicio → módulo de sucursales.
    $visible = isset($block['attrs']['limit']) ? absint($block['attrs']['limit']) : 3;
    $visible = max(1, min(30, $visible));

    $html = preg_replace(
        '/<div class="miniso-module-grid miniso-module-stores">/',
        '<div class="miniso-module-grid miniso-module-stores" data-miniso-rotate="1" data-miniso-count="' . $visible . '">',
        $html,
        1
    );

    $seen = 0;
    $html = preg_replace_callback(
        '/<article class="miniso-module-store">/',
        static function ($match) use (&$seen, $visible) {
            $seen++;
            return $seen <= $visible ? $match[0] : '<article class="miniso-module-store" hidden>';
        },
        $html
    );
    return $html;
}, 30, 2);

add_action('wp_enqueue_scripts', function () {
    if (!msr_is_home_view()) return;
    wp_enqueue_style(
        'miniso-sucursales-rotativas',
        get_template_directory_uri() . '/assets/team-sucursales-rotativas.css',
        array(),
        '1.0.0'
    );
    wp_enqueue_script(
        'miniso-sucursales-rotativas',
        get_template_directory_uri() . '/assets/team-sucursales-rotativas.js',
        array(),
        '1.0.0',
        true
    );
}, 100);

} // Proteccion ante duplicacion con plugins MINISO preexistentes.
