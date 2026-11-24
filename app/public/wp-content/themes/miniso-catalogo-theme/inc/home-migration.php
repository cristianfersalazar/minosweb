<?php
/**
 * Opt-in migration: convert existing homepage settings into genuine, editable Gutenberg blocks.
 * Never runs on theme activation, never discards existing page content.
 */
defined('ABSPATH') || exit;

function mct_home_page_id() {
    $pages=get_option('miniso_catalog_pages',array());
    if(!empty($pages['inicio']) && get_post_type((int)$pages['inicio'])==='page') return (int)$pages['inicio'];
    $front=(int)get_option('page_on_front');
    return $front && get_post_type($front)==='page' ? $front : 0;
}
function mct_home_block_markup($attributes) {
    return serialize_block(array('blockName'=>'miniso/modulo','attrs'=>$attributes,
        'innerBlocks'=>array(),'innerHTML'=>'','innerContent'=>array()));
}
function mct_home_migrate_blocks() {
    $source=mct_settings();
    $out=array();
    $make=function($kind,$data=array())use(&$out) {
        $attributes=array_merge(array('kind'=>$kind,'enabled'=>true,'background'=>'blanco'),$data);
        $out[]=mct_home_block_markup($attributes);
    };
    if(mct_banner_slides()) {
        $make('banners',array('autoplay'=>(bool)mct_design('hero_autoplay'),
            'interval'=>max(2,min(20,(int)mct_design('hero_interval'))),
            'showCaptions'=>true));
    }
    $colors=array('licenses'=>'licencias','news'=>'novedades','lover'=>'lover','stores'=>'sucursales');
    $backgrounds=array('licenses'=>'licenses_bg','news'=>'news_bg','lover'=>'lover_bg','stores'=>'stores_bg');
    $default_buttons=array('licenses'=>'licencias','news'=>'novedades','lover'=>'lover','stores'=>'sucursales');
    $kind_by_section=array('licenses'=>'colecciones','news'=>'novedades','lover'=>'lover','stores'=>'sucursales');
    $labels=array('licenses'=>'licenses_label','news'=>'news_label','lover'=>'lover_label','stores'=>'');
    $order=explode('-', (string)mct_design('home_order'));
    foreach($order as $section) {
        if(!isset($kind_by_section[$section])) continue;
        if(!mct_design('show_'.$section)) continue;
        $key=$kind_by_section[$section];
        $attrs=array('eyebrow'=>$labels[$section] ? mct_ui_text($labels[$section]) : '',
            'heading'=>(string)mct_design($section.'_title'),
            'description'=>(string)mct_design($section.'_text'),
            'buttonText'=>(string)mct_design($section.'_button'),
            'buttonUrl'=>mct_url($default_buttons[$section]),
            'background'=>'propio',
            'customColor'=>sanitize_hex_color(mct_design($backgrounds[$section]))?:'#ffffff');
        if($section==='news') $attrs['limit']=max(1,min(30,(int)mct_design('news_count')));
        if($section==='lover') $attrs['imageId']=absint($source['lover']??0);
        $make($key,$attrs);
    }
    if(mct_design('show_video') && mct_design('video_url')) {
        $make('video',array('heading'=>(string)mct_design('video_title'),
            'eyebrow'=>mct_ui_text('video_label'),
            'description'=>(string)mct_design('video_text'),
            'videoUrl'=>(string)mct_design('video_url'),
            'background'=>'propio',
            'customColor'=>sanitize_hex_color(mct_design('video_bg'))?:'#ffffff'));
    }
    return implode("\n\n",$out);
}
add_action('admin_menu',function(){
    add_theme_page('MINISO · Inicio editable','MINISO · Inicio editable','edit_theme_options',
        'miniso-inicio-editable','mct_home_migration_page');
});
function mct_home_migration_page() {
    if(!current_user_can('edit_theme_options')) wp_die('No autorizado');
    $page_id=mct_home_page_id();
    $error=''; $success=false;
    if(isset($_POST['mct_migrate_home'])) {
        check_admin_referer('mct_migrate_home','mct_migrate_home_nonce');
        if(!$page_id) $error='No se encontró una página de Inicio configurada.';
        elseif(mct_modular_home_enabled($page_id)) $error='El Inicio ya utiliza la plantilla editable. No se duplicaron módulos.';
        elseif(!function_exists('serialize_block')) $error='Tu WordPress no permite serializar bloques.';
        else {
            $existing=(string)get_post_field('post_content',$page_id);
            $blocks=parse_blocks($existing);
            $has_module=false;
            $search=function($items)use(&$search,&$has_module){foreach($items as $item){
                if(($item['blockName']??'')==='miniso/modulo')$has_module=true;
                if(!empty($item['innerBlocks']))$search($item['innerBlocks']);
            }};
            $search($blocks);
            if($has_module) $error='Esta página ya contiene módulos. Edita Inicio para evitar duplicados.';
            else {
                $new=mct_home_migrate_blocks();
                if(!$new) $error='No hay módulos publicados para migrar. Puedes seleccionar la plantilla flexible manualmente.';
                else {
                    $result=wp_update_post(array('ID'=>$page_id,'post_content'=>$new."\n\n".$existing),true);
                    if(is_wp_error($result)) $error=$result->get_error_message();
                    else {update_post_meta($page_id,'_wp_page_template','templates/inicio-flexible.php');$success=true;}
                }
            }
        }
    }
    echo '<div class="wrap"><h1>MINISO · Inicio editable</h1>';
    echo '<p>Controla el orden, los colores y el contenido de la portada desde bloques visuales. Las publicaciones y los archivos multimedia se conservan en WordPress.</p>';
    if($error)echo '<div class="notice notice-error"><p>'.esc_html($error).'</p></div>';
    if($success)echo '<div class="notice notice-success"><p>Los módulos existentes se prepararon como bloques editables. Revisa la página Inicio.</p></div>';
    if(!$page_id) {echo '<p>Configura una página de Inicio en Ajustes → Lectura.</p></div>';return;}
    $front_id = (int)get_option('page_on_front');
    $front_mode = (string)get_option('show_on_front');
    if ($front_mode !== 'page' || $front_id !== $page_id) {
        echo '<div class="notice notice-warning inline"><p><strong>Hay dos versiones de Inicio:</strong> la raíz del sitio (/) no está vinculada a esta misma página editable. En <a href="'.esc_url(admin_url('options-reading.php')).'">Ajustes → Lectura</a>, elige «Una página estática» y selecciona como Portada la página <strong>'.esc_html(get_the_title($page_id)).'</strong>. No dupliques las páginas.</p></div>';
    }
    echo '<p><strong>Página:</strong> '.esc_html(get_the_title($page_id)).'</p>';
    echo '<p><a class="button button-primary" href="'.esc_url(get_edit_post_link($page_id,'')).'">Editar Inicio</a> <a class="button" href="'.esc_url(get_permalink($page_id)).'" target="_blank" rel="noopener noreferrer">Ver Inicio</a></p>';
    if(mct_modular_home_enabled($page_id)) {
        echo '<p>El modo flexible está activo. Agrega bloques <strong>MINISO · Módulo conectado</strong> y <strong>MINISO · Sección personalizable</strong> desde el botón + del editor.</p>';
    } else {
        echo '<h2>Preparar la portada actual</h2><p>Esta acción es opcional. Genera bloques editables a partir de la información que ya está publicada y conserva el contenido actual al final. Antes, haz una copia de seguridad y después revisa la portada.</p>';
        echo '<form method="post">';wp_nonce_field('mct_migrate_home','mct_migrate_home_nonce');
        echo '<button class="button button-primary" name="mct_migrate_home" value="1" type="submit" onclick="return confirm(\'¿Preparar los módulos existentes en Inicio? Se recomienda hacer una copia de seguridad antes.\')">Preparar Inicio editable</button></form>';
    }
    echo '<p><em>La plantilla anterior permanece disponible. No se cambian los demás apartados durante este proceso.</em></p></div>';
}
