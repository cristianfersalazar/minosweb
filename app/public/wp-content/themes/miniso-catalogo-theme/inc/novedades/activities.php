<?php
/** Actividades editables para toda novedad, sin plantillas prellenadas. */
defined('ABSPATH') || exit;

function mnv_news_activities($post) {
    $post = get_post($post);
    if (!$post) return array();
    $heading = trim((string)get_post_meta($post->ID, 'mnv_activities_heading', true));
    $intro = trim((string)get_post_meta($post->ID, 'mnv_activities_intro', true));
    $raw = get_post_meta($post->ID, 'mnv_activities_items', true);
    $items = array();
    if (is_array($raw)) foreach($raw as $entry) {
        if (!is_array($entry)) continue;
        $item = array(
            'title' => trim((string)($entry['title'] ?? '')),
            'description' => trim((string)($entry['description'] ?? '')),
            'image_id' => absint($entry['image_id'] ?? 0),
            'url' => trim((string)($entry['url'] ?? '')),
            'url_label' => trim((string)($entry['url_label'] ?? '')),
        );
        if ($item['title'] || $item['description'] || $item['image_id'] || $item['url']) $items[] = $item;
    }
    if (!$heading && !$intro && !$items) return array();
    return compact('heading','intro','items');
}

function mnv_activities_metabox($post) {
    wp_nonce_field('mnv_activities_save','mnv_activities_nonce');
    $heading = (string)get_post_meta($post->ID,'mnv_activities_heading',true);
    $intro = (string)get_post_meta($post->ID,'mnv_activities_intro',true);
    $items = get_post_meta($post->ID,'mnv_activities_items',true);
    if (!is_array($items)) $items = array();
    echo '<p>Estos campos están disponibles para todas las novedades. Los apartados sin información no se muestran.</p>';
    echo '<p><label for="mnv-activities-heading"><strong>Título (opcional)</strong></label><input class="widefat" id="mnv-activities-heading" type="text" name="mnv_activities_heading" value="'.esc_attr($heading).'" /></p>';
    echo '<p><label for="mnv-activities-intro"><strong>Descripción (opcional)</strong></label><textarea class="widefat" id="mnv-activities-intro" rows="3" name="mnv_activities_intro">'.esc_textarea($intro).'</textarea></p>';
    echo '<p><strong>Tarjetas de actividades</strong></p>';
    echo '<div class="mnv-activity-list">';
    foreach($items as $i=>$item) {
        mnv_activity_row($i,$item);
    }
    echo '</div><p><button type="button" class="button mnv-activity-add">+ Agregar actividad</button></p>';
    echo '<template id="mnv-activity-template">';
    mnv_activity_row('__INDEX__',array());
    echo '</template>';
}
function mnv_activity_row($index, $item) {
    $title=esc_attr($item['title']??'');
    $description=esc_textarea($item['description']??'');
    $image_id=absint($item['image_id']??0);
    $url=esc_attr($item['url']??'');
    $url_label=esc_attr($item['url_label']??'');
    $name='mnv_activities_items['.$index.']';
    $preview=$image_id?wp_get_attachment_image($image_id,'thumbnail',false,array('style'=>'max-width:100px;height:auto;')):'';
    echo '<div class="mnv-activity-row" style="border:1px solid #ddd;border-radius:8px;padding:14px;margin:12px 0;background:#fff">';
    echo '<p><label><strong>Nombre</strong><input class="widefat" name="'.$name.'[title]" value="'.$title.'"></label></p>';
    echo '<p><label><strong>Descripción</strong><textarea class="widefat" name="'.$name.'[description]" rows="3">'.$description.'</textarea></label></p>';
    echo '<p><label><strong>Imagen</strong></label> <span class="mnv-activity-preview">'.$preview.'</span><input type="hidden" class="mnv-activity-image-id" name="'.$name.'[image_id]" value="'.$image_id.'"> <button type="button" class="button mnv-activity-image">Seleccionar imagen</button> <button type="button" class="button mnv-activity-image-remove">Quitar imagen</button></p>';
    echo '<p><label><strong>Enlace (opcional)</strong><input class="widefat" type="url" name="'.$name.'[url]" value="'.$url.'"></label></p>';
    echo '<p><label><strong>Texto del enlace (opcional)</strong><input class="widefat" type="text" name="'.$name.'[url_label]" value="'.$url_label.'"></label></p>';
    echo '<button type="button" class="button-link-delete mnv-activity-remove">Eliminar actividad</button></div>';
}
add_action('add_meta_boxes_miniso_novedad', function() {
    add_meta_box('mnv-universal-activities', 'MINISO · Actividades y contenido adicional', 'mnv_activities_metabox','miniso_novedad','normal','default');
}, 15);
add_action('admin_enqueue_scripts', function($hook) {
    if (!in_array($hook,array('post.php','post-new.php'),true)) return;
    $screen=get_current_screen();
    if (!$screen || $screen->post_type!=='miniso_novedad') return;
    wp_enqueue_media();
    wp_enqueue_script('mnv-activities-editor',get_template_directory_uri() . '/assets/team-news-activities-admin.js',array('jquery'), '1.7.0',true);
});
add_action('save_post_miniso_novedad', function($post_id) {
    if (!isset($_POST['mnv_activities_nonce']) || !is_scalar($_POST['mnv_activities_nonce'])) return;
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mnv_activities_nonce'])),'mnv_activities_save')) return;
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id) || !current_user_can('edit_post',$post_id)) return;
    foreach(array('mnv_activities_heading'=>'text','mnv_activities_intro'=>'textarea') as $field=>$kind) {
        if (!isset($_POST[$field]) || !is_scalar($_POST[$field])) continue;
        $val=wp_unslash($_POST[$field]);
        $val=$kind==='text' ? sanitize_text_field($val) : sanitize_textarea_field($val);
        if (trim($val)==='') delete_post_meta($post_id,$field);
        else update_post_meta($post_id,$field,$val);
    }
    // Presence of nonce means a complete form is present, even with zero activities.
    $raw = isset($_POST['mnv_activities_items']) && is_array($_POST['mnv_activities_items']) ? wp_unslash($_POST['mnv_activities_items']) : array();
    $items=array();
    foreach($raw as $row) {
        if (!is_array($row)) continue;
        $title = sanitize_text_field(is_scalar($row['title']??null) ? $row['title'] : '');
        $description = sanitize_textarea_field(is_scalar($row['description']??null) ? $row['description'] : '');
        $image_id = absint($row['image_id']??0);
        $url = esc_url_raw(is_scalar($row['url']??null) ? $row['url'] : '',array('https','http'));
        $url_label = sanitize_text_field(is_scalar($row['url_label']??null) ? $row['url_label'] : '');
        if(!$title && !$description && !$image_id && !$url) continue;
        $items[]=compact('title','description','image_id','url','url_label');
        if(count($items)>=50) break;
    }
    if($items) update_post_meta($post_id,'mnv_activities_items',$items);
    else delete_post_meta($post_id,'mnv_activities_items');
}, 15);
