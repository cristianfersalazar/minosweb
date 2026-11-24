<?php
/**
 * MINISO 2.7 · Centraliza la edición del contenido textual en el Personalizador.
 * No cambia ni duplica valores guardados: reubica controles existentes.
 * Si la versión del plugin base carece de un campo, agrega un ajuste de respaldo del tema.
 */
defined('ABSPATH') || exit;

function mct_copy_defaults() {
    return array(
        'collection_badge'        => 'COLECCIÓN',
        'catalog_card_cta'        => 'Ver productos',
        'news_read'               => 'Leer más',
        'news_default_badge'      => 'Novedad',
        'video_button'            => 'Ver reel en Instagram',
        'video_help'              => 'Puedes reproducirlo aquí. Si Instagram no lo carga, abre el reel desde el botón.',
        'catalog_all_label'       => 'Todo el catálogo',
        'catalog_search_hint'     => 'Buscar colección o campaña',
        'catalog_search_button'   => 'Buscar',
        'catalog_clear_button'    => 'Limpiar',
        'catalog_no_results'      => 'No hay elementos publicados que coincidan con la búsqueda.',
        'product_card_button'     => 'Ver producto',
        'product_note'            => 'Consulta la disponibilidad, los precios vigentes y las condiciones de cada promoción en tu sucursal. Las imágenes no confirman existencias por tienda.',
        'product_store_button'    => 'Encontrar una sucursal',
        'locator_eyebrow'         => 'MINISO GUATEMALA',
        'locator_results_phrase'  => 'Encuentra tu próximo lugar favorito',
        'mobile_more'             => 'Más',
        'mobile_favorites'        => 'Favoritos',
        'careers_footer_label'    => 'Trabaja con nosotros',
    );
}
function mct_copy_text($key) {
    $defaults = mct_copy_defaults();
    if (!array_key_exists($key, $defaults)) return '';
    return get_theme_mod('mct_copy_'.$key, $defaults[$key]);
}

/** Mapas de los ajustes existentes: se mueven, no se sustituyen por copias. */
function mct_copy_design_fields() {
    return array(
        'mct_copy_banner'   => array('hero_title'=>'Título del banner principal', 'hero_button'=>'Texto del botón principal', 'collection_button'=>'Texto del botón de banners automáticos'),
        'mct_copy_catalog'  => array('licenses_title'=>'Título grande · Tus mundos favoritos', 'licenses_text'=>'Descripción debajo del título', 'licenses_button'=>'Botón superior · Ver todas las licencias'),
        'mct_copy_news'     => array('news_title'=>'Título principal de Novedades', 'news_text'=>'Descripción de Novedades', 'news_button'=>'Botón · Ver todas las novedades'),
        'mct_copy_lover'    => array('lover_title'=>'Título principal de Lover+', 'lover_text'=>'Descripción de Lover+', 'lover_button'=>'Botón de Lover+'),
        'mct_copy_stores'   => array('stores_title'=>'Título del bloque Sucursales', 'stores_text'=>'Descripción del bloque Sucursales', 'stores_button'=>'Botón del bloque Sucursales', 'locator_title'=>'Título de la página Sucursales', 'locator_text'=>'Introducción de la página Sucursales', 'locator_note'=>'Aviso inferior de Sucursales', 'map_button'=>'Botón para consultar ubicación'),
        'mct_copy_social'   => array('social_title'=>'Título de Redes sociales', 'social_text'=>'Descripción de Redes sociales'),
        'mct_copy_video'    => array('video_title'=>'Título del video de Instagram', 'video_text'=>'Descripción del video de Instagram'),
        'mct_copy_footer'   => array('announcement'=>'Frase de la barra superior', 'footer_text'=>'Descripción del pie de página', 'nav_inicio'=>'Menú: Inicio', 'nav_licencias'=>'Menú: Catálogo', 'nav_novedades'=>'Menú: Novedades', 'nav_sucursales'=>'Menú: Sucursales', 'nav_lover'=>'Menú: Lover+', 'nav_ayuda'=>'Menú: Ayuda'),
    );
}
function mct_copy_ui_fields() {
    return array(
        'mct_copy_catalog'=>array('licenses_label'=>'Etiqueta pequeña de Catálogo'),
        'mct_copy_news'=>array('news_label'=>'Etiqueta pequeña de Novedades'),
        'mct_copy_lover'=>array('lover_label'=>'Etiqueta pequeña de Lover+'),
        'mct_copy_social'=>array('social_label'=>'Etiqueta de Redes', 'social_subtext'=>'Texto bajo las tarjetas de redes'),
        'mct_copy_video'=>array('video_label'=>'Etiqueta de Video', 'video_account'=>'Usuario de Instagram mostrado'),
        'mct_copy_footer'=>array('footer_brand'=>'Nombre de MINISO en el pie', 'footer_slogan'=>'Frase de marca (Life is for fun)'),
    );
}
add_action('customize_register', function($c) {
    $c->add_panel('mct_copy_panel', array(
        'title'=>'MINISO · Textos editables',
        'description'=>'Edita textos y botones desde un solo lugar. Se conservan los valores que ya configuraste. Los nombres de productos, licencias, banners individuales y noticias se cambian en sus respectivos editores; los módulos del Inicio editable se cambian en Páginas → Inicio.',
        'priority'=>29,
    ));
    $sections = array(
        'mct_copy_sync'   => array('00 · ¿Tu Inicio usa bloques?', 'Si tu portada se construyó con el editor MINISO · Inicio editable, puedes utilizar los textos de este panel de forma centralizada. Activa la sincronización solo si quieres que este panel tenga prioridad sobre los textos guardados en cada bloque. Al desactivarla, vuelven los textos originales de cada bloque, sin perderlos.'),
        'mct_copy_banner'  => array('01 · Banner principal', 'Aquí se edita solo el banner principal automático. Los demás banners se cambian en WooCommerce → Banners MINISO.'),
        'mct_copy_catalog' => array('02 · Catálogo y colecciones', 'Edita el título, la descripción, la etiqueta y el botón del bloque rojo de Inicio. Los nombres de las colecciones se editan en Productos → Licencias y portadas.'),
        'mct_copy_news'    => array('03 · Novedades', 'Textos generales del bloque de novedades. Cada noticia se edita por separado en WordPress.'),
        'mct_copy_lover'   => array('04 · Lover+', 'Textos de presentación del bloque Lover+ de Inicio.'),
        'mct_copy_stores'  => array('05 · Sucursales', 'Textos de Inicio y de la página de sucursales. Direcciones y horarios se editan en las sucursales.'),
        'mct_copy_social'  => array('06 · Redes sociales', 'Textos del apartado de redes, sin modificar sus enlaces ni sus colores.'),
        'mct_copy_video'   => array('07 · Video de Instagram', 'Textos del video y mensajes de ayuda.'),
        'mct_copy_products'=> array('08 · Productos y botones', 'Cambia las frases comunes. Los nombres, precios, descripciones y fotografías de productos se editan en Productos.'),
        'mct_copy_footer'  => array('09 · Barra superior, menú y pie', 'Textos de navegación y pie de página. Vacía una etiqueta opcional para ocultarla.'),
        'mct_copy_careers' => array('10 · Forma parte del equipo', 'Sección de LinkedIn y reclutamiento. Incluye sus textos, enlace e imagen oficial.'),
    );
    foreach ($sections as $id=>$info) {
        $c->add_section($id, array('title'=>$info[0], 'description'=>$info[1], 'panel'=>'mct_copy_panel'));
    }

    $c->add_setting('mct_copy_sync_modular',array(
        'type'=>'theme_mod','default'=>false,'sanitize_callback'=>'rest_sanitize_boolean',
        'capability'=>'edit_theme_options','transport'=>'refresh',
    ));
    $c->add_control('mct_copy_sync_modular',array(
        'type'=>'checkbox','section'=>'mct_copy_sync',
        'label'=>'Aplicar los textos de este panel también a los módulos del Inicio editable',
        'description'=>'No elimina ni cambia los textos guardados en los bloques. Para editar solo un módulo específico, deja esta casilla desactivada y ve a Páginas → Inicio → Editar.',
    ));

    // Reubicar los campos del complemento base y conservar su almacenamiento miniso_design[].
    foreach(mct_copy_design_fields() as $section=>$fields) {
        foreach($fields as $key=>$label) {
            $existing=$c->get_control('miniso_design['.$key.']');
            if ($existing) {
                $existing->section=$section;
                $existing->label=$label;
                continue;
            }
            // Plugin antiguo: el tema permite editar estos textos sin romper compatibilidad.
            $id='mct_copy_override_'.$key;
            $old=function_exists('miniso_design') ? miniso_design($key) : '';
            $c->add_setting($id,array('type'=>'theme_mod','default'=>$old,
                'sanitize_callback'=>'sanitize_text_field','capability'=>'edit_theme_options','transport'=>'refresh'));
            $c->add_control($id,array('type'=>'text','label'=>$label,'section'=>$section));
        }
    }

    // Estas etiquetas se guardaban como theme_mod: cambiar de sección no borra sus textos.
    foreach (mct_copy_ui_fields() as $section=>$fields) {
        foreach($fields as $key=>$label) {
            $control=$c->get_control('mct_ui_'.$key);
            if ($control) { $control->section=$section; $control->label=$label; }
        }
    }
    if ($c->get_section('mct_ui_structure')) $c->remove_section('mct_ui_structure');

    // Reubicar la sección Trabaja con nosotros (y sus ajustes ya guardados).
    $careers=$c->get_section('mct_careers_section');
    if ($careers) {
        foreach (array('enabled','footer_link','eyebrow','title','description','tags','button','url','note','panel_title','panel_text','background','accent','text_color','image_id') as $key) {
            $control=$c->get_control('mct_careers_'.$key);
            if ($control) $control->section='mct_copy_careers';
        }
        $c->remove_section('mct_careers_section');
    }

    $extra_fields=array(
        'mct_copy_catalog'=>array(
            'collection_badge'=>'Palabra superior de las tarjetas · COLECCIÓN',
            'catalog_card_cta'=>'Enlace de tarjetas · Ver productos',
            'catalog_all_label'=>'Filtro · Todo el catálogo',
            'catalog_search_hint'=>'Buscador de colecciones · Indicador de búsqueda',
            'catalog_search_button'=>'Botón del buscador · Buscar',
            'catalog_clear_button'=>'Botón del buscador · Limpiar',
            'catalog_no_results'=>'Aviso cuando no hay colecciones',
        ),
        'mct_copy_news'=>array('news_read'=>'Texto del enlace · Leer más','news_default_badge'=>'Etiqueta predeterminada · Novedad'),
        'mct_copy_video'=>array('video_button'=>'Texto del botón para abrir Instagram','video_help'=>'Mensaje inferior del video'),
        'mct_copy_products'=>array('product_card_button'=>'Botón de las tarjetas · Ver producto','product_note'=>'Aviso de disponibilidad en ficha de producto','product_store_button'=>'Botón de ficha · Encontrar una sucursal'),
        'mct_copy_stores'=>array('locator_eyebrow'=>'Etiqueta de la página de sucursales', 'locator_results_phrase'=>'Frase junto al número de sucursales'),
        'mct_copy_footer'=>array('mobile_more'=>'Menú móvil · Más','mobile_favorites'=>'Menú móvil · Favoritos','careers_footer_label'=>'Enlace del pie · Trabaja con nosotros'),
    );
    $defaults=mct_copy_defaults();
    foreach($extra_fields as $section=>$fields) {
        foreach($fields as $key=>$label) {
            $id='mct_copy_'.$key;
            $textarea=in_array($key,array('video_help','product_note','catalog_no_results'),true);
            $c->add_setting($id,array(
                'type'=>'theme_mod','default'=>$defaults[$key],
                'sanitize_callback'=>$textarea?'sanitize_textarea_field':'sanitize_text_field',
                'capability'=>'edit_theme_options','transport'=>'refresh',
            ));
            $c->add_control($id,array(
                'type'=>$textarea?'textarea':'text', 'label'=>$label,'section'=>$section,
            ));
        }
    }
}, 160);
