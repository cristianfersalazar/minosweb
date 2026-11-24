<?php
defined('ABSPATH') || exit;

// FUNCIÓN · Acepta solo enlaces públicos de publicaciones/reels de Instagram; nunca HTML arbitrario.
function miniso_instagram_url($value) {
    if (!is_string($value)) return '';
    $parts=wp_parse_url(trim($value));
    if (!$parts || !in_array(strtolower($parts['host']??''),array('instagram.com','www.instagram.com'),true)) return '';
    if (!in_array(strtolower($parts['scheme']??''),array('http','https'),true) || isset($parts['user']) || isset($parts['port'])) return '';
    if (!preg_match('~^/(?:[A-Za-z0-9_.]+/)?(reel|p|tv)/([A-Za-z0-9_-]{5,40})/?$~',$parts['path']??'',$m)) return '';
    return 'https://www.instagram.com/'.$m[1].'/'.$m[2].'/';
}

// EDITAR · WooCommerce → Sucursales → Datos de visita. El contenido anterior se conserva.
function miniso_store_fields() {
    return array(
        'miniso_mall'=>array('Centro comercial o lugar','text'),
        'miniso_department'=>array('Departamento','text'),
        'miniso_address'=>array('Dirección','textarea'),
        'miniso_hours'=>array('Horario (un renglón por grupo de días)','textarea'),
        'miniso_hours_basis'=>array('Este horario corresponde a','select'),
        'miniso_source'=>array('Fuente de la dirección / horario','url'),
        'miniso_checked'=>array('Fecha de revisión (AAAA-MM-DD)','date'),
        'miniso_store_note'=>array('Nota interna para marketing (no aparece en la página)','textarea'),
    );
}
function miniso_store_clean($key,$value) {
    if (!is_scalar($value)) return '';
    if ('miniso_source'===$key) return esc_url_raw($value,array('https','http'));
    if ('miniso_hours_basis'===$key) return in_array($value,array('mall','store','pending'),true)?$value:'pending';
    if ('miniso_checked'===$key) return preg_match('/^\d{4}-\d{2}-\d{2}$/',$value)?$value:'';
    return in_array($key,array('miniso_address','miniso_hours','miniso_store_note'),true)?sanitize_textarea_field($value):sanitize_text_field($value);
}
function miniso_store_save_details($id,$data) {
    foreach(miniso_store_fields() as $key=>$field) if(array_key_exists($key,$data)) update_post_meta($id,$key,miniso_store_clean($key,$data[$key]));
}
add_action('add_meta_boxes_miniso_sucursal',function(){
    add_meta_box('miniso-store-details','Datos de visita',function($post){
        wp_nonce_field('miniso_store_details','miniso_store_details_nonce');
        echo '<p>Dirección y horario del centro comercial como referencia. Cambia a «Tienda MINISO» cuando hayas confirmado el horario propio. Puedes dejar un campo vacío si falta confirmarlo.</p>';
        foreach(miniso_store_fields() as $key=>$field){
            $value=get_post_meta($post->ID,$key,true);
            echo '<p><label for="'.esc_attr($key).'"><strong>'.esc_html($field[0]).'</strong></label><br>';
            if ('textarea'===$field[1]) echo '<textarea class="widefat" rows="3" id="'.esc_attr($key).'" name="'.esc_attr($key).'">'.esc_textarea($value).'</textarea>';
            elseif ('select'===$field[1]) {
                echo '<select id="'.esc_attr($key).'" name="'.esc_attr($key).'">';
                foreach(array('mall'=>'Centro comercial (referencia)','store'=>'Tienda MINISO (confirmado)','pending'=>'Pendiente de confirmar') as $k=>$label) echo '<option value="'.esc_attr($k).'" '.selected($value?:'pending',$k,false).'>'.esc_html($label).'</option>';
                echo '</select>';
            } else echo '<input class="widefat" type="'.esc_attr($field[1]).'" id="'.esc_attr($key).'" name="'.esc_attr($key).'" value="'.esc_attr($value).'">';
            echo '</p>';
        }
    },'miniso_sucursal','normal','high');
});
add_action('save_post_miniso_sucursal',function($id){
    if (wp_is_post_revision($id)||wp_is_post_autosave($id)||!current_user_can('edit_post',$id)||empty($_POST['miniso_store_details_nonce'])||!is_string($_POST['miniso_store_details_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['miniso_store_details_nonce'])),'miniso_store_details')) return;
    miniso_store_save_details($id,wp_unslash($_POST));
});

// FUNCIÓN · Completa solo campos que nunca se han guardado. No repone valores vaciados por marketing.
function miniso_seed_store_references() {
    $records=json_decode(file_get_contents(MINISO_CATALOG_DIR.'data/stores-reference.json'),true);
    if (!is_array($records)) return;
    foreach($records as $record){
        $stores=get_posts(array('post_type'=>'miniso_sucursal','post_status'=>array('publish','draft','private','pending'),'name'=>$record['slug'],'numberposts'=>1,'fields'=>'ids'));
        if (!$stores) continue;
        $id=$stores[0];
        foreach(array('mall'=>'miniso_mall','address'=>'miniso_address','hours'=>'miniso_hours','department'=>'miniso_department','source'=>'miniso_source','checked'=>'miniso_checked','note'=>'miniso_store_note') as $key=>$meta) {
            if (!metadata_exists('post',$id,$meta)) add_post_meta($id,$meta,miniso_store_clean($meta,$record[$key]),true);
        }
        if (!metadata_exists('post',$id,'miniso_hours_basis')) add_post_meta($id,'miniso_hours_basis',$record['hours']?'mall':'pending',true);
    }
}

// FUNCIÓN · Agrega el banner entregado sin sustituir el principal ni las portadas existentes.
function miniso_seed_naruto_banner() {
    if (get_option('miniso_naruto_banner_seeded')) return;
    $image=miniso_catalog_import_official_image('bannernaruto.png','Naruto Shippuden · banner original');
    $mobile=miniso_catalog_import_official_image('naruto-mobile.jpg','Naruto × MINISO · arte vertical original');
    if (is_wp_error($image)||is_wp_error($mobile)) return;
    $term=get_term_by('slug','naruto','pa_licencia');
    $url=$term?get_term_link($term):miniso_catalog_page_url('licencias');
    if (is_wp_error($url)) $url=miniso_catalog_page_url('licencias');
    $id=wp_insert_post(array('post_type'=>'miniso_banner','post_status'=>'publish','post_title'=>'Naruto Shippuden × MINISO','post_excerpt'=>'Encuentra tus favoritos de la colección.','menu_order'=>5,'meta_input'=>array('_thumbnail_id'=>$image,'miniso_mobile_image'=>$mobile,'miniso_link'=>$url,'miniso_button'=>'Explorar colección','miniso_accent'=>'#f5a623','_miniso_seed_banner'=>1)),true);
    if (!is_wp_error($id)) update_option('miniso_naruto_banner_seeded',$id,false);
}
function miniso_catalog_upgrade_experience() {
    if (!get_option('miniso_catalog_pages') || !taxonomy_exists('pa_licencia')) return;
    miniso_seed_store_references();
    miniso_seed_naruto_banner();
    update_option('miniso_catalog_experience_version','2.1.0',false);
}
add_action('admin_init',function(){
    if(current_user_can('manage_options') && '2.1.0'!==get_option('miniso_catalog_experience_version')) miniso_catalog_upgrade_experience();
});
