<?php
/**
 * MINISO · Constructor de portada 2.3.
 * Bloques dinámicos conectados al catálogo existente, sin textos prellenados.
 * Las publicaciones se administran en los tipos de contenido originales.
 */
defined('ABSPATH') || exit;

function mct_modular_home_enabled($page_id = 0) {
    $page_id = $page_id ? absint($page_id) : get_queried_object_id();
    return $page_id && get_page_template_slug($page_id) === 'templates/inicio-flexible.php';
}

add_action('init', function () {
    wp_register_script('mct-modular-home-editor',
        get_template_directory_uri() . '/assets/modular-home-editor.js',
        array('wp-blocks','wp-element','wp-components','wp-block-editor','wp-i18n'),
        wp_get_theme()->get('Version'), true);
    register_block_type('miniso/modulo', array(
        'api_version' => 2,
        'editor_script' => 'mct-modular-home-editor',
        'render_callback' => 'mct_render_module',
        'attributes' => array(
            'kind' => array('type'=>'string','default'=>'novedades'),
            'position' => array('type'=>'string','default'=>'after'),
            'enabled' => array('type'=>'boolean','default'=>true),
            'eyebrow' => array('type'=>'string','default'=>''),
            'heading' => array('type'=>'string','default'=>''),
            'description' => array('type'=>'string','default'=>''),
            'buttonText' => array('type'=>'string','default'=>''),
            'buttonUrl' => array('type'=>'string','default'=>''),
            'background' => array('type'=>'string','default'=>'blanco'),
            'customColor' => array('type'=>'string','default'=>''),
            'limit' => array('type'=>'number','default'=>6),
            'columns' => array('type'=>'number','default'=>3),
            'collectionGroup'=>array('type'=>'string','default'=>'licencias'),
            'collectionLayout'=>array('type'=>'string','default'=>'carousel'),
            'autoplay' => array('type'=>'boolean','default'=>true),
            'interval' => array('type'=>'number','default'=>5),
            'showCaptions' => array('type'=>'boolean','default'=>true),
            'imageId' => array('type'=>'number','default'=>0),
            'videoUrl' => array('type'=>'string','default'=>''),
            'instagramUrl' => array('type'=>'string','default'=>''),
            'facebookUrl' => array('type'=>'string','default'=>''),
            'tiktokUrl' => array('type'=>'string','default'=>''),
        ),
        'supports' => array('html'=>false,'multiple'=>true,'anchor'=>true)
    ));
});

function mct_modular_color($attributes) {
    $palette = array('blanco'=>'#FFFFFF','rosa'=>'#FFE1E8','amarillo'=>'#FFE17A',
        'verde'=>'#CFF4D8','azul'=>'#CFEFFF','lila'=>'#E8D9FF','rojo'=>'#E60020','oscuro'=>'#202020');
    $choice = sanitize_key($attributes['background'] ?? 'blanco');
    if ($choice === 'propio') return sanitize_hex_color($attributes['customColor'] ?? '') ?: '#FFFFFF';
    return $palette[$choice] ?? '#FFFFFF';
}

function mct_modular_news($limit) {
    $posts = get_posts(array('post_type'=>'miniso_novedad','post_status'=>'publish',
        'has_password'=>false,'numberposts'=>$limit,'orderby'=>'date','order'=>'DESC'));
    if (!$posts) return '';
    ob_start(); echo '<div class="miniso-module-grid miniso-module-news">';
    foreach ($posts as $item) {
        if (function_exists('mnv_news_card')) mnv_news_card($item);
        else mct_news_card($item);
    }
    echo '</div>'; return ob_get_clean();
}

function mct_modular_collections($limit, $layout = 'carousel', $columns = 3, $group = 'licencias') {
    if (!mct_ready()) return '';
    $groups=array('licencias','promociones','temporadas');
    $group=in_array($group,$groups,true)?$group:'licencias';
    $terms = $group === 'licencias' ? array_values(array_filter(mct_featured_terms(),fn($t)=>mct_catalog_legacy_type($t)==='licencias')) : mct_catalog_terms($group);
    $terms = array_slice($terms,0,$limit);
    if (!$terms) return '';
    $columns = min(4, max(1, (int)$columns));
    $layout = $layout === 'grid' ? 'grid' : 'carousel';
    ob_start();
    if ($layout === 'carousel') {
        // Same native horizontal rail and controls as the original MINISO homepage.
        echo '<div class="collection-rail miniso-module-collections" data-rail tabindex="0" aria-label="Colecciones destacadas, desliza horizontalmente" style="--rail-columns:'.$columns.'">';
    } else {
        echo '<div class="miniso-module-grid miniso-module-collections">';
    }
    foreach ($terms as $term) mct_catalog_card($term);
    echo '</div>';
    if ($layout === 'carousel' && count($terms) > 1) {
        echo '<div class="rail-controls js-only">';
        echo '<button class="icon-button" type="button" data-rail-prev aria-label="Colecciones anteriores">'.mct_icon('prev').'</button>';
        echo '<button class="icon-button" type="button" data-rail-next aria-label="Más colecciones">'.mct_icon('next').'</button>';
        echo '</div>';
    }
    return ob_get_clean();
}

function mct_modular_stores($limit) {
    $stores = get_posts(array('post_type'=>'miniso_sucursal','post_status'=>'publish',
        'has_password'=>false,'numberposts'=>$limit,'orderby'=>array('menu_order'=>'ASC','title'=>'ASC')));
    if (!$stores) return '';
    ob_start(); echo '<div class="miniso-module-grid miniso-module-stores">';
    foreach ($stores as $store) {
        $address = trim((string)get_post_meta($store->ID,'miniso_address',true));
        $hours = trim((string)get_post_meta($store->ID,'miniso_hours',true));
        $map = esc_url(get_post_meta($store->ID,'miniso_map',true));
        echo '<article class="miniso-module-store"><h3>'.esc_html($store->post_title).'</h3>';
        if($address) echo '<p>'.nl2br(esc_html($address)).'</p>';
        if($hours) echo '<p>'.nl2br(esc_html($hours)).'</p>';
        if($map) echo '<a href="'.$map.'" target="_blank" rel="noopener noreferrer">'.esc_html__('Ver ubicación','miniso-catalogo-theme').'</a>';
        echo '</article>';
    }
    echo '</div>'; return ob_get_clean();
}

function mct_modular_social($attributes) {
    $config = array('instagram'=>'Instagram','facebook'=>'Facebook','tiktok'=>'TikTok');
    $items = array();
    foreach($config as $key=>$label) {
        $url = esc_url($attributes[$key.'Url'] ?? '');
        if(!$url) $url = esc_url(mct_design($key.'_url'));
        if($url) $items[$key] = array('url'=>$url,'label'=>$label);
    }
    if(!$items) return '';
    ob_start(); echo '<div class="miniso-module-grid miniso-module-social">';
    foreach($items as $key=>$item) echo '<a class="miniso-module-social-link" href="'.esc_url($item['url']).'" target="_blank" rel="noopener noreferrer">'.mct_icon($key).'<span>'.esc_html($item['label']).'</span></a>';
    echo '</div>'; return ob_get_clean();
}

function mct_render_module($attributes) {
    if(empty($attributes['enabled'])) return '';
    $kind = sanitize_key($attributes['kind'] ?? 'novedades');
    if(!in_array($kind,array('banners','colecciones','novedades','sucursales','lover','video','redes'),true)) return '';
    $heading = trim((string)($attributes['heading']??''));
    $description = trim((string)($attributes['description']??''));
    $eyebrow = trim((string)($attributes['eyebrow']??''));
    $button = trim((string)($attributes['buttonText']??''));
    $button_url = esc_url($attributes['buttonUrl']??'');
    // Opt-in: una única fuente de textos para el Inicio clásico y el Inicio editable.
    // No sobrescribe post_content, y el modo anterior se restaura al desactivar la casilla.
    if (get_theme_mod('mct_copy_sync_modular', false)) {
        $map=array(
            'colecciones'=>array('licenses','licenses_label'),
            'novedades'=>array('news','news_label'),
            'sucursales'=>array('stores',''),
            'lover'=>array('lover','lover_label'),
            'video'=>array('video','video_label'),
            'redes'=>array('social','social_label'),
        );
        // El bloque de Promociones y Temporadas conserva sus títulos independientes.
        $sync_kind = 'colecciones' === $kind
            && 'licencias' !== ($attributes['collectionGroup'] ?? 'licencias') ? '' : $kind;
        if(isset($map[$sync_kind])) {
            list($design_group,$ui_label)=$map[$sync_kind];
            $heading=(string)mct_design($design_group.'_title');
            $description=(string)mct_design($design_group.'_text');
            if ($ui_label) $eyebrow=(string)mct_ui_text($ui_label);
            $new_button=(string)mct_design($design_group.'_button');
            // Evitar crear un botón que no tenía enlace en el bloque original.
            if($button_url) $button=$new_button;
        }
    }
    $limit = min(30,max(1,(int)($attributes['limit']??6)));
    $cols = min(4,max(1,(int)($attributes['columns']??3)));
    $color = mct_modular_color($attributes);
    $ink = function_exists('mct_contrast') ? mct_contrast($color) : '#171717';
    $payload = '';
    switch ($kind) {
        case 'banners':
            // Banners are managed by WooCommerce → Banners MINISO, never entered in code.
            if(!function_exists('mct_banner_slides') || !mct_banner_slides()) return '';
            ob_start();
            mct_hero(array('autoplay'=>!empty($attributes['autoplay']),
                'interval'=>min(20,max(2,(int)($attributes['interval']??5))),
                'showCaptions'=>!empty($attributes['showCaptions'])));
            $payload = ob_get_clean(); break;
        case 'colecciones': $payload = mct_modular_collections($limit, $attributes['collectionLayout'] ?? 'carousel', $cols, $attributes['collectionGroup'] ?? 'licencias'); break;
        case 'novedades': $payload = mct_modular_news($limit); break;
        case 'sucursales': $payload = mct_modular_stores($limit); break;
        case 'redes': $payload = mct_modular_social($attributes); break;
        case 'lover':
            $image = absint($attributes['imageId']??0);
            if(!$image && !$heading && !$eyebrow && !$description && !$button) return '';
            if($image) $payload = '<div class="miniso-module-image">'.wp_get_attachment_image($image,'large',false,array('loading'=>'lazy')).'</div>';
            break;
        case 'video':
            $url = trim((string)($attributes['videoUrl']??''));
            $embed = function_exists('mct_global_section_video') ? mct_global_section_video($url) : '';
            if(!$embed) return '';
            $payload = '<div class="miniso-module-video">'.$embed.'</div>'; break;
    }
    if (!$payload && !$heading && !$description && !$eyebrow && !($button && $button_url)) return '';
    if(!$payload && in_array($kind,array('colecciones','novedades','sucursales','redes','banners'),true)) return '';
    // An empty heading, description, image or button never generates filler content.
    $style = '--mct-module-bg:'.esc_attr($color).';--mct-module-ink:'.esc_attr($ink).';--mct-module-cols:'.$cols.';';
    $module_class = 'miniso-module-section miniso-module-section--'.$kind;
    if ($kind === 'lover' && $payload !== '') $module_class .= ' has-image';
    $wrap = '<section class="'.esc_attr($module_class).'" style="'.$style.'"><div class="miniso-module-inner">';
    if($eyebrow || $heading || $description || ($button && $button_url)) {
        $wrap .= '<div class="miniso-module-heading"><div>';
        if($eyebrow) $wrap .= '<span class="eyebrow">'.esc_html($eyebrow).'</span>';
        if($heading) $wrap .= '<h2>'.esc_html($heading).'</h2>';
        if($description) $wrap .= '<p>'.nl2br(esc_html($description)).'</p>';
        $wrap .= '</div>';
        if($button && $button_url) $wrap .= '<a class="miniso-module-cta" href="'.$button_url.'">'.esc_html($button).'</a>';
        $wrap .= '</div>';
    }
    // For carousel, the outer section must be full-width. No duplicate padding.
    $wrap .= $payload.'</div></section>';
    return $wrap;
}
