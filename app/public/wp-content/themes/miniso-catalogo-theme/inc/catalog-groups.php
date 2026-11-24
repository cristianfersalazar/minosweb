<?php
/**
 * Catálogo MINISO: licencias permanentes, promociones y temporadas.
 * Una sola ficha WooCommerce puede pertenecer a una licencia y varias campañas.
 */
defined('ABSPATH') || exit;

function mct_catalog_types() {
    return array('licencias' => 'Licencias', 'promociones' => 'Promociones', 'temporadas' => 'Temporadas');
}
function mct_catalog_taxonomy_for_type($type) {
    return array('promociones'=>'miniso_campaign', 'temporadas'=>'miniso_season')[$type] ?? 'pa_licencia';
}
function mct_catalog_legacy_type($term) {
    if (!($term instanceof WP_Term)) return 'licencias';
    $saved = get_term_meta($term->term_id, 'mct_catalog_kind', true);
    if (in_array($saved, array_keys(mct_catalog_types()), true)) return $saved;
    $old = strtolower(remove_accents(trim((string) get_term_meta($term->term_id,'miniso_group',true))));
    if ('promociones' === $old || 'promocion' === $old) return 'promociones';
    if ('temporadas' === $old || 'temporada' === $old) return 'temporadas';
    return 'licencias';
}
add_action('init', function() {
    foreach (array(
        'miniso_campaign' => array('plural'=>'Campañas MINISO','singular'=>'Campaña MINISO','slug'=>'promociones'),
        'miniso_season' => array('plural'=>'Temporadas MINISO','singular'=>'Temporada MINISO','slug'=>'temporadas'),
    ) as $tax => $info) {
        register_taxonomy($tax, array('product'), array(
            'labels' => array(
                'name'=>$info['plural'], 'singular_name'=>$info['singular'],
                'search_items'=>'Buscar '.$info['plural'], 'all_items'=>'Todas las '.$info['plural'],
                'edit_item'=>'Editar '.$info['singular'], 'update_item'=>'Actualizar '.$info['singular'],
                'add_new_item'=>'Añadir '.$info['singular'], 'new_item_name'=>'Nombre de '.$info['singular'],
            ),
            'public'=>true, 'show_ui'=>true, 'show_in_rest'=>true,
            'show_admin_column'=>false, 'hierarchical'=>true,
            'meta_box_cb'=>'post_categories_meta_box',
            'rewrite'=>array('slug'=>'catalogo/'.$info['slug'],'with_front'=>false),
            'query_var'=>true,
        ));
    }
}, 30);

function mct_catalog_term_color($term) {
    $color = sanitize_hex_color(get_term_meta($term->term_id,'mct_catalog_color',true));
    if (!$color) $color = sanitize_hex_color(get_term_meta($term->term_id,'miniso_accent',true));
    if (!$color && 'pa_licencia' === $term->taxonomy && function_exists('miniso_collection_color')) $color = sanitize_hex_color(miniso_collection_color($term));
    return $color ?: '#E60020';
}
function mct_catalog_term_image($term) {
    return absint(get_term_meta($term->term_id,'miniso_cover_id',true));
}
function mct_catalog_date_valid($date) {
    if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) return false;
    $d=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
    return $d && $d->format('Y-m-d') === $date;
}
function mct_catalog_term_is_current($term) {
    if (!($term instanceof WP_Term)) return false;
    $start=get_term_meta($term->term_id,'mct_start_date',true);
    $end=get_term_meta($term->term_id,'mct_end_date',true);
    $today=wp_date('Y-m-d');
    if (mct_catalog_date_valid($start) && $today < $start) return false;
    if (mct_catalog_date_valid($end) && $today > $end) return false;
    $legacy_id=absint(get_term_meta($term->term_id,'mct_legacy_term_id',true));
    if ($legacy_id) {
        $legacy=get_term($legacy_id,'pa_licencia');
        if ($legacy instanceof WP_Term && mct_catalog_legacy_type($legacy) !== ('miniso_campaign' === $term->taxonomy ? 'promociones' : 'temporadas')) return false;
    }
    return true;
}

// Edición visual desde Productos → Campañas/Temporadas y Productos → Licencias y portadas.
function mct_catalog_term_fields($term_or_tax) {
    $editing = $term_or_tax instanceof WP_Term;
    $tax = $editing ? $term_or_tax->taxonomy : (string)$term_or_tax;
    $id = $editing ? $term_or_tax->term_id : 0;
    $cover = $id ? mct_catalog_term_image($term_or_tax) : 0;
    $color = $id ? mct_catalog_term_color($term_or_tax) : '#E60020';
    $start = $id ? get_term_meta($id,'mct_start_date',true) : '';
    $end = $id ? get_term_meta($id,'mct_end_date',true) : '';
    ob_start();
    wp_nonce_field('mct_catalog_term_save','mct_catalog_term_nonce');
    if ($tax === 'pa_licencia') {
        $kind = $editing ? mct_catalog_legacy_type($term_or_tax) : 'licencias';
        echo '<p><label for="mct_catalog_kind"><strong>Tipo dentro de Catálogo</strong></label><br><select name="mct_catalog_kind" id="mct_catalog_kind">';
        foreach(mct_catalog_types() as $key=>$label) echo '<option value="'.esc_attr($key).'" '.selected($kind,$key,false).'>'.esc_html($label).'</option>';
        echo '</select><br><small>Licencias = colección permanente. Promociones/Temporadas = campaña vinculable a productos, sin duplicar fichas.</small></p>';
    } else {
        echo '<p><label for="mct_catalog_cover"><strong>Imagen de portada (opcional)</strong></label><br>';
        echo '<input type="hidden" id="mct_catalog_cover" name="mct_catalog_cover" value="'.absint($cover).'">';
        echo '<button type="button" class="button mct-select-cover">Seleccionar imagen</button> <button type="button" class="button mct-remove-cover">Quitar imagen</button><br>';
        echo '<span class="mct-cover-preview">'.($cover ? wp_get_attachment_image($cover,'thumbnail') : '').'</span></p>';
        echo '<p><label for="mct_catalog_color"><strong>Color destacado</strong></label><br><input type="color" id="mct_catalog_color" name="mct_catalog_color" value="'.esc_attr($color).'"></p>';
        echo '<p><label for="mct_start_date"><strong>Inicio de campaña (opcional)</strong></label><br><input type="date" id="mct_start_date" name="mct_start_date" value="'.esc_attr($start).'"></p>';
        echo '<p><label for="mct_end_date"><strong>Fin de campaña (opcional)</strong></label><br><input type="date" id="mct_end_date" name="mct_end_date" value="'.esc_attr($end).'"></p>';
        echo '<p><small>Las fechas controlan la visibilidad de la campaña. Los precios de oferta se programan por producto en WooCommerce.</small></p>';
    }
    $fields=ob_get_clean();
    if ($editing) echo '<tr class="form-field"><th scope="row">Presentación MINISO</th><td>'.$fields.'</td></tr>';
    else echo '<div class="form-field">'.$fields.'</div>';
}
foreach(array('pa_licencia','miniso_campaign','miniso_season') as $tax) {
    add_action($tax.'_add_form_fields', 'mct_catalog_term_fields');
    add_action($tax.'_edit_form_fields','mct_catalog_term_fields');
    add_action('created_'.$tax,'mct_catalog_save_term', 100);
    add_action('edited_'.$tax,'mct_catalog_save_term',100);
}
function mct_catalog_save_term($term_id) {
    if (!isset($_POST['mct_catalog_term_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mct_catalog_term_nonce'])),'mct_catalog_term_save')) return;
    if (!current_user_can('edit_term',$term_id)) return;
    $term=get_term($term_id);
    if (!($term instanceof WP_Term)) return;
    if ('pa_licencia' === $term->taxonomy && isset($_POST['mct_catalog_kind'])) {
        $kind=sanitize_key(wp_unslash($_POST['mct_catalog_kind']));
        if (isset(mct_catalog_types()[$kind])) update_term_meta($term_id,'mct_catalog_kind',$kind);
        mct_catalog_sync_legacy_term($term_id);
        return;
    }
    if (!in_array($term->taxonomy,array('miniso_campaign','miniso_season'),true)) return;
    if (isset($_POST['mct_catalog_cover'])) update_term_meta($term_id,'miniso_cover_id',absint($_POST['mct_catalog_cover']));
    if (isset($_POST['mct_catalog_color'])) {
        $color=sanitize_hex_color(wp_unslash($_POST['mct_catalog_color']));
        if ($color) update_term_meta($term_id,'mct_catalog_color',$color);
    }
    foreach(array('mct_start_date','mct_end_date') as $key) if(isset($_POST[$key])) {
        $date=sanitize_text_field(wp_unslash($_POST[$key]));
        if (!$date || mct_catalog_date_valid($date)) update_term_meta($term_id,$key,$date);
    }
}
add_action('admin_enqueue_scripts',function($hook) {
    if (!in_array($hook,array('edit-tags.php','term.php'),true)) return;
    $tax = isset($_GET['taxonomy']) ? sanitize_key(wp_unslash($_GET['taxonomy'])) : '';
    if (!in_array($tax,array('miniso_campaign','miniso_season'),true)) return;
    wp_enqueue_media();
    wp_enqueue_script('mct-catalog-tax-admin',get_template_directory_uri().'/assets/catalog-admin.js',array('jquery'),wp_get_theme()->get('Version'),true);
});

/** Copy legacy promotional terms to a product taxonomy once, preserving cover and old URLs. */
function mct_catalog_sync_legacy_term($id) {
    $legacy=get_term($id,'pa_licencia');
    if (!($legacy instanceof WP_Term)) return;
    $type=mct_catalog_legacy_type($legacy);
    if ('licencias' === $type) return;
    $tax=mct_catalog_taxonomy_for_type($type);
    if (!taxonomy_exists($tax)) return;
    $existing=get_term_by('slug',$legacy->slug,$tax);
    if (!($existing instanceof WP_Term)) {
        $created=wp_insert_term($legacy->name,$tax,array('slug'=>$legacy->slug,'description'=>$legacy->description));
        if (is_wp_error($created)) return;
        $existing=get_term($created['term_id'],$tax);
    }
    if (!($existing instanceof WP_Term)) return;
    update_term_meta($existing->term_id,'mct_legacy_term_id',$legacy->term_id);
    foreach(array('miniso_cover_id','miniso_accent') as $key) {
        if (get_term_meta($existing->term_id,$key,true)) continue;
        $value=get_term_meta($legacy->term_id,$key,true);
        if ($value) update_term_meta($existing->term_id,$key,$value);
    }
    $products=get_objects_in_term($legacy->term_id,'pa_licencia');
    if (is_array($products)) foreach($products as $product_id) {
        if ('product' === get_post_type($product_id)) wp_set_object_terms($product_id,array($existing->term_id),$tax,true);
    }
}
add_action('admin_init',function(){
    if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) return;
    if (!taxonomy_exists('pa_licencia')) return;
    if (get_option('mct_catalog_migrated_25') !== 'yes') {
        $terms=get_terms(array('taxonomy'=>'pa_licencia','hide_empty'=>false));
        if (!is_wp_error($terms)) foreach($terms as $term) mct_catalog_sync_legacy_term($term->term_id);
        update_option('mct_catalog_migrated_25','yes',false);
    }
    if (get_option('mct_catalog_rewrite_25') !== 'yes') {
        flush_rewrite_rules(false);
        update_option('mct_catalog_rewrite_25','yes',false);
    }
}, 30);

// Legacy URLs stay valid as redirects to the campaign/season, not broken links.
add_action('template_redirect',function() {
    if (!is_tax('pa_licencia')) return;
    $term=get_queried_object();
    if (!($term instanceof WP_Term)) return;
    $kind=mct_catalog_legacy_type($term);
    if ($kind === 'licencias') return;
    $new=get_term_by('slug',$term->slug,mct_catalog_taxonomy_for_type($kind));
    if ($new instanceof WP_Term) {
        $url=get_term_link($new);
        if (!is_wp_error($url)) { wp_safe_redirect($url,301);exit; }
    }
}, 3);

function mct_catalog_terms($type,$search='') {
    $taxonomy=mct_catalog_taxonomy_for_type($type);
    if (!taxonomy_exists($taxonomy)) return array();
    $terms=get_terms(array('taxonomy'=>$taxonomy,'hide_empty'=>false,'orderby'=>'name','search'=>$search));
    if (is_wp_error($terms)) return array();
    if ('licencias' === $type) return array_values(array_filter($terms,fn($term)=>mct_catalog_legacy_type($term)==='licencias'));
    return array_values(array_filter($terms,'mct_catalog_term_is_current'));
}
function mct_catalog_card($term) {
    $url=get_term_link($term);
    if (is_wp_error($url)) return;
    $color=mct_catalog_term_color($term);
    $label='miniso_campaign' === $term->taxonomy ? 'PROMOCIÓN' : ('miniso_season' === $term->taxonomy ? 'TEMPORADA' : mct_copy_text('collection_badge'));
    echo '<article class="collection-card" style="'.esc_attr(mct_accent_style($color)).'">';
    echo '<a class="collection-link" href="'.esc_url($url).'">';
    if (mct_catalog_term_image($term)) mct_media(mct_catalog_term_image($term),$term->name,'collection-art');
    else echo '<div class="media collection-art mct-catalog-no-image" aria-hidden="true"></div>';
    echo '<div class="collection-caption"><span class="eyebrow">'.esc_html($label).'</span><h3>'.esc_html($term->name).'</h3><span class="collection-cta">'.esc_html(mct_copy_text('catalog_card_cta')).'</span></div></a></article>';
}
function mct_catalog_overview() {
    $type=mct_get('tipo');
    if (!isset(mct_catalog_types()[$type])) $type='';
    $search=mct_get('coleccion');
    $base=get_permalink();
    echo '<div class="mct-catalog-groups"><nav class="mct-catalog-tabs" aria-label="Tipos de catálogo">';
    $tabs=array(''=>mct_copy_text('catalog_all_label'))+mct_catalog_types();
    foreach($tabs as $slug=>$label) {
        $url=$slug?add_query_arg('tipo',$slug,$base):$base;
        echo '<a href="'.esc_url($url).'"'.($slug===$type?' aria-current="page"':'').'>'.esc_html($label).'</a>';
    }
    echo '</nav><form class="toolbar" method="get" action="'.esc_url($base).'">';
    if ($type) echo '<input type="hidden" name="tipo" value="'.esc_attr($type).'">';
    echo '<label for="buscar-coleccion" class="sr-only">Buscar en el catálogo</label><input type="search" id="buscar-coleccion" name="coleccion" value="'.esc_attr($search).'" placeholder="'.esc_attr(mct_copy_text('catalog_search_hint')).'">';
    echo '<button class="button" type="submit">'.esc_html(mct_copy_text('catalog_search_button')).'</button><a class="text-link clear-filters" href="'.esc_url($base).'">'.esc_html(mct_copy_text('catalog_clear_button')).'</a></form>';
    $any=false;
    foreach(mct_catalog_types() as $slug=>$label) {
        if ($type && $type!==$slug) continue;
        $terms=mct_catalog_terms($slug,$search);
        if (!$terms) continue;
        $any=true;
        echo '<section class="mct-catalog-type" id="catalogo-'.esc_attr($slug).'"><h2>'.esc_html($label).'</h2><div class="grid collection-grid">';
        foreach($terms as $term) mct_catalog_card($term);
        echo '</div></section>';
    }
    if (!$any) echo '<p class="empty">'.esc_html(mct_copy_text('catalog_no_results')).'</p>';
    echo '</div>';
}

function mct_catalog_sale_price_html($product) {
    if (!$product || !is_a($product,'WC_Product')) return '';
    if ($product->is_type('variable') || $product->is_type('grouped')) {
        $price=$product->get_price_html();
        return $price ? '<div class="mct-price mct-price-variable">'.wp_kses_post($price).'</div>' : '';
    }
    $regular=(float)$product->get_regular_price();
    $sale=(float)$product->get_sale_price();
    if ($product->is_on_sale() && $regular > 0 && $sale < $regular) {
        $shown_regular=wc_get_price_to_display($product,array('price'=>$regular));
        $shown_sale=wc_get_price_to_display($product,array('price'=>$sale));
        $percent=(int)round((($regular-$sale)/$regular)*100);
        return '<div class="mct-price"><del>'.wc_price($shown_regular).'</del><strong>'.wc_price($shown_sale).'</strong><span class="mct-offer-percent">−'.$percent.'%</span></div>';
    }
    $price=$product->get_price_html();
    return $price ? '<div class="mct-price">'.wp_kses_post($price).'</div>' : '';
}

/** One-time catalog page title+slug migration. Existing Page ID/content remains unchanged. */
add_action('admin_init',function() {
    if (!current_user_can('edit_pages') || get_option('mct_catalog_page_renamed_25')==='yes') return;
    $pages=get_option('miniso_catalog_pages',array());
    $page_id=is_array($pages)?absint($pages['licencias']??0):0;
    $page=$page_id?get_post($page_id):null;
    if (!$page || $page->post_type!=='page') return;
    $changes=array('ID'=>$page_id);
    $changed=false;
    if (trim($page->post_title)==='Licencias') {$changes['post_title']='Catálogo';$changed=true;}
    if ($page->post_name==='licencias' && !get_page_by_path('catalogo')) {
        update_option('mct_catalog_previous_url_25',get_permalink($page_id),false);
        $changes['post_name']='catalogo';$changed=true;
    }
    if ($changed) {
        $out=wp_update_post(wp_slash($changes),true);
        if (is_wp_error($out)) return;
    }
    update_option('mct_catalog_page_renamed_25','yes',false);
},50);
add_action('template_redirect',function() {
    if (!is_404()) return;
    $previous=get_option('mct_catalog_previous_url_25','');
    if (!$previous) return;
    $requested=wp_parse_url(isset($_SERVER['REQUEST_URI'])?wp_unslash($_SERVER['REQUEST_URI']):'',PHP_URL_PATH);
    $old=wp_parse_url($previous,PHP_URL_PATH);
    if (!$requested || !$old || untrailingslashit($requested)!==untrailingslashit($old)) return;
    $new=mct_url('licencias');
    if ($new && untrailingslashit($new)!==untrailingslashit($previous)) {
        wp_safe_redirect($new,301);exit;
    }
},1);


/** Existing product tags "Black MINISO 2026" remain; matching campaigns gain membership. */
function mct_catalog_import_existing_promo_tags() {
    if (!taxonomy_exists('product_tag')) return;
    $campaigns=get_terms(array('taxonomy'=>'miniso_campaign','hide_empty'=>false));
    if (is_wp_error($campaigns)) return;
    foreach($campaigns as $campaign) {
        $slug=$campaign->slug;
        $aliases=array($slug,$slug.'-'.wp_date('Y'),$slug.'-'.(intval(wp_date('Y'))-1));
        foreach($aliases as $alias) {
            $tag=get_term_by('slug',$alias,'product_tag');
            if (!($tag instanceof WP_Term)) continue;
            $ids=get_objects_in_term($tag->term_id,'product_tag');
            if (!is_array($ids)) continue;
            foreach($ids as $pid) if ('product'===get_post_type($pid)) {
                wp_set_object_terms($pid,array($campaign->term_id),'miniso_campaign',true);
            }
        }
    }
}
add_action('admin_init',function(){
    if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) return;
    if (get_option('mct_catalog_import_tags_25')==='yes') return;
    mct_catalog_import_existing_promo_tags();
    update_option('mct_catalog_import_tags_25','yes',false);
},35);


// Evita que columnas adicionales estrechen excesivamente el listado de productos.
// Campañas y temporadas siguen editables dentro de cada producto.
add_action('admin_head-edit.php', function () {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'edit-product') return;
    echo '<style id="mct-product-table-layout">
      body.post-type-product .wp-list-table.products{table-layout:auto;min-width:1230px}
      body.post-type-product .wp-list-table.products th,
      body.post-type-product .wp-list-table.products td{word-break:normal;overflow-wrap:normal}
      body.post-type-product .wp-list-table.products .column-name{min-width:175px}
    </style>';
});
