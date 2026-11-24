<?php
/**
 * Encabezado del índice de Novedades administrable; no contiene texto prefijado.
 * El título general se edita como título de la página WordPress.
 */
defined('ABSPATH') || exit;
function mnv_index_field($id,$key,$default='') {
    return get_post_meta(absint($id),'mnv_index_'.$key,true) ?: $default;
}
function mnv_index_intro($id) {return (string)mnv_index_field($id,'intro');}
add_action('add_meta_boxes_page',function($page){
    if(!$page instanceof WP_Post || get_page_template_slug($page->ID)!=='templates/novedades.php') return;
    add_meta_box('mnv-index-settings','MINISO · Portada de Novedades',function($post){
        wp_nonce_field('mnv_index_save','mnv_index_nonce');
        echo '<p>Personaliza el listado completo. Todos los textos son opcionales; los campos vacíos no se muestran.</p>';
        foreach(array('eyebrow'=>'Texto pequeño del encabezado','intro'=>'Introducción','directory_title'=>'Título del listado','directory_intro'=>'Descripción del listado') as $key=>$label) {
            $value=(string)get_post_meta($post->ID,'mnv_index_'.$key,true);
            echo '<p><label for="mnv-index-'.esc_attr($key).'"><strong>'.esc_html($label).'</strong></label><br>';
            if(in_array($key,array('intro','directory_intro'),true)) echo '<textarea class="widefat" id="mnv-index-'.esc_attr($key).'" name="mnv_index_'.esc_attr($key).'" rows="3">'.esc_textarea($value).'</textarea>';
            else echo '<input class="widefat" id="mnv-index-'.esc_attr($key).'" name="mnv_index_'.esc_attr($key).'" value="'.esc_attr($value).'">';
            echo '</p>';
        }
        $color = sanitize_hex_color((string)get_post_meta($post->ID,'mnv_index_color',true)) ?: '#FFE1E8';
        echo '<p><label for="mnv-index-color"><strong>Color del encabezado</strong></label><br><input type="color" id="mnv-index-color" name="mnv_index_color" value="'.esc_attr($color).'"></p>';
        echo '<p><label><input type="checkbox" name="mnv_index_filters_enabled" value="1" '.checked(get_post_meta($post->ID,'mnv_index_filters_enabled',true) !== '0',true,false).'> Mostrar filtros por tipo</label></p>';
        $per=(int)get_post_meta($post->ID,'mnv_index_per_page',true);
        echo '<p><label for="mnv-index-per"><strong>Publicaciones por página</strong></label><br><input type="number" min="1" max="30" id="mnv-index-per" name="mnv_index_per_page" value="'.($per?:8).'"></p>';
        echo '<p><em>Para agregar galerías, premios y videos usa bloques «MINISO · Sección personalizable» en el contenido de la página.</em></p>';
    },'page','normal','high');
});
add_action('save_post_page',function($post_id){
    if(!isset($_POST['mnv_index_nonce']) || !is_scalar($_POST['mnv_index_nonce']))return;
    if(!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mnv_index_nonce'])),'mnv_index_save'))return;
    if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)||wp_is_post_revision($post_id)||!current_user_can('edit_post',$post_id))return;
    if(get_page_template_slug($post_id)!=='templates/novedades.php')return;
    foreach(array('eyebrow','intro','directory_title','directory_intro') as $key){
        $input='mnv_index_'.$key;
        $value=isset($_POST[$input])&&is_scalar($_POST[$input])?sanitize_textarea_field(wp_unslash($_POST[$input])):'';
        if($value)update_post_meta($post_id,$input,$value);else delete_post_meta($post_id,$input);
    }
    $color = isset($_POST['mnv_index_color']) && is_scalar($_POST['mnv_index_color']) ? sanitize_hex_color(wp_unslash($_POST['mnv_index_color'])) : '';
    if($color) update_post_meta($post_id,'mnv_index_color',$color); else delete_post_meta($post_id,'mnv_index_color');
    update_post_meta($post_id,'mnv_index_filters_enabled',empty($_POST['mnv_index_filters_enabled'])?'0':'1');
    $per = isset($_POST['mnv_index_per_page']) ? (int)$_POST['mnv_index_per_page'] : 8;
    update_post_meta($post_id,'mnv_index_per_page',min(30,max(1,$per)));
});
