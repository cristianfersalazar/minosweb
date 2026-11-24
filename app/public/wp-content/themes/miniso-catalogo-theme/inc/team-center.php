<?php
/**
 * MINISO 3.0 · Centro editorial para Marketing.
 * Interfaz única sin acceso a PHP o CSS; no crea ni modifica contenidos de muestra.
 * Las opciones existentes se guardan en sus claves originales para preservar compatibilidad.
 */
defined('ABSPATH') || exit;

function mct_team_groups() {
    $design = function_exists('mct_copy_design_fields') ? mct_copy_design_fields() : array();
    $ui = function_exists('mct_copy_ui_fields') ? mct_copy_ui_fields() : array();
    $extra = array(
        'portada' => array('mct_copy_banner'),
        'catalogo' => array('mct_copy_catalog'),
        'novedades' => array('mct_copy_news'),
        'lover' => array('mct_copy_lover'),
        'sucursales' => array('mct_copy_stores'),
        'redes' => array('mct_copy_social','mct_copy_video'),
        'general' => array('mct_copy_footer'),
    );
    $extra_labels = array(
        'mct_copy_catalog'=>array('collection_badge'=>'Palabra COLECCIÓN en tarjetas','catalog_card_cta'=>'Ver productos en tarjetas','catalog_all_label'=>'Filtro: Todo el catálogo','catalog_search_hint'=>'Buscador: indicación','catalog_search_button'=>'Botón Buscar','catalog_clear_button'=>'Botón Limpiar','catalog_no_results'=>'Aviso sin resultados'),
        'mct_copy_news'=>array('news_read'=>'Enlace Leer más','news_default_badge'=>'Etiqueta por defecto'),
        'mct_copy_video'=>array('video_button'=>'Botón Abrir Instagram','video_help'=>'Mensaje del video'),
        'mct_copy_products'=>array('product_card_button'=>'Botón de producto','product_note'=>'Aviso de disponibilidad','product_store_button'=>'Botón para sucursales'),
        'mct_copy_stores'=>array('locator_eyebrow'=>'Etiqueta página Sucursales','locator_results_phrase'=>'Frase directorio de sucursales'),
        'mct_copy_footer'=>array('mobile_more'=>'Menú móvil: Más','mobile_favorites'=>'Menú móvil: Favoritos','careers_footer_label'=>'Enlace pie: Trabaja con nosotros'),
    );
    $tabs = array();
    foreach($extra as $tab=>$sections) {
        $fields=array();
        foreach($sections as $section){
            foreach(($design[$section]??array()) as $key=>$label) $fields['design__'.$key]=array('label'=>$label,'kind'=>'design','key'=>$key,'type'=>preg_match('/text|description|Descripción|frase|introducci/i',$label)?'textarea':'text');
            foreach(($ui[$section]??array()) as $key=>$label) $fields['ui__'.$key]=array('label'=>$label,'kind'=>'ui','key'=>$key,'type'=>'text');
            foreach(($extra_labels[$section]??array()) as $key=>$label) $fields['copy__'.$key]=array('label'=>$label,'kind'=>'copy','key'=>$key,'type'=>in_array($key,array('product_note','catalog_no_results','video_help'),true)?'textarea':'text');
        }
        $tabs[$tab]=$fields;
    }
    foreach($extra_labels['mct_copy_products'] as $key=>$label) $tabs['catalogo']['copy__'.$key]=array('label'=>$label,'kind'=>'copy','key'=>$key,'type'=>($key==='product_note'?'textarea':'text'));
    $tabs['portada']['flag__mct_copy_sync_modular']=array('label'=>'Sincronizar títulos del Centro con los módulos de Inicio editable (no sobrescribe los bloques)','kind'=>'flag','key'=>'mct_copy_sync_modular','type'=>'checkbox');
    $tabs['apariencia']=array();
    $colors = array('primary'=>'Rojo principal','secondary'=>'Color secundario','background'=>'Fondo general','ink'=>'Texto general','licenses_bg'=>'Fondo Catálogo','news_bg'=>'Fondo Novedades','lover_bg'=>'Fondo Lover+','stores_bg'=>'Fondo Sucursales','social_bg'=>'Fondo Redes','video_bg'=>'Fondo Video','locator_color'=>'Fondo localizador','announcement_color'=>'Barra superior');
    foreach($colors as $key=>$label) $tabs['apariencia']['design__'.$key]=array('label'=>$label,'kind'=>'design','key'=>$key,'type'=>'color');
    foreach(array('miniso_faq_background'=>'Fondo de Ayuda','miniso_faq_cards'=>'Tarjetas de preguntas','miniso_faq_accent'=>'Iconos de Ayuda') as $key=>$label) $tabs['apariencia']['theme__'.$key]=array('label'=>$label,'kind'=>'theme','key'=>$key,'type'=>'color');
    $tabs['apariencia']['option__miniso_red_text_mode']=array('label'=>'Color de títulos sobre rojo MINISO','kind'=>'option','key'=>'miniso_red_text_mode','type'=>'select','choices'=>array('white'=>'Blanco','black'=>'Negro','off'=>'Automático / estilo original'));
    foreach(array('hero_url'=>'Destino botón banner principal','instagram_url'=>'Instagram','facebook_url'=>'Facebook','tiktok_url'=>'TikTok','video_url'=>'Reel de Instagram') as $key=>$label) $tabs['redes']['design__'.$key]=array('label'=>$label,'kind'=>'design','key'=>$key,'type'=>'url');
    $tabs['reclutamiento']=array();
    foreach(array('enabled'=>'Mostrar bloque de reclutamiento','footer_link'=>'Mostrar enlace en pie de página','eyebrow'=>'Etiqueta superior','title'=>'Título principal','description'=>'Descripción','tags'=>'Etiquetas de equipo (separadas por comas)','button'=>'Texto del botón','url'=>'Enlace LinkedIn / vacantes','note'=>'Mensaje complementario','panel_title'=>'Título del panel','panel_text'=>'Descripción del panel','background'=>'Color de fondo','accent'=>'Color destacado','text_color'=>'Color de texto') as $key=>$label) {
        $type=in_array($key,array('enabled','footer_link'),true)?'checkbox':(in_array($key,array('background','accent','text_color'),true)?'color':($key==='url'?'url':(in_array($key,array('description','note','panel_text'),true)?'textarea':'text')));
        $tabs['reclutamiento']['theme__mct_careers_'.$key]=array('label'=>$label,'kind'=>'theme','key'=>'mct_careers_'.$key,'type'=>$type);
    }
    $tabs['reclutamiento']['theme__mct_careers_image_id']=array('label'=>'Fotografía del equipo (Biblioteca de medios)','kind'=>'theme','key'=>'mct_careers_image_id','type'=>'media');
    $tabs['ayuda']=array();
    foreach((function_exists('mac_topics')?mac_topics():array()) as $key=>$label) $tabs['ayuda']['topic__'.$key]=array('label'=>'Categoría: '.$key,'kind'=>'topic','key'=>$key,'type'=>'text');
    return $tabs;
}

function mct_team_get_value($field) {
    $kind=$field['kind']; $key=$field['key'];
    if($kind==='design') return (string)mct_design($key);
    if($kind==='ui') return (string)mct_ui_text($key);
    if($kind==='copy') return (string)mct_copy_text($key);
    if($kind==='theme'||$kind==='flag') {
        if(str_starts_with($key,'mct_careers_') && function_exists('mct_careers_defaults')) {
            $defaults=mct_careers_defaults(); $which=substr($key,strlen('mct_careers_'));
            return get_theme_mod($key,$defaults[$which]??'');
        }
        $colors=array('miniso_faq_background'=>'#CFEFFF','miniso_faq_cards'=>'#FFFFFF','miniso_faq_accent'=>'#E60020');
        return get_theme_mod($key,$colors[$key]??($field['type']==='checkbox'?false:''));
    }
    if($kind==='option') return get_option($key,'white');
    if($kind==='topic') { $topics=mac_topics();return $topics[$key]??''; }
    return '';
}

function mct_team_save_design($key,$value) {
    if(function_exists('miniso_design_schema') && is_array(miniso_design_schema()) && isset(miniso_design_schema()[$key])) {
        $option=get_option('miniso_design',array());
        if(!is_array($option)) $option=array();
        $option[$key]=function_exists('miniso_design_clean')?miniso_design_clean($value,$key):$value;
        update_option('miniso_design',$option);
        remove_theme_mod('mct_copy_override_'.$key);
    } else {
        set_theme_mod('mct_copy_override_'.$key,$value);
    }
}

add_action('admin_post_mct_team_save',function(){
    if(!current_user_can('edit_pages')) wp_die(esc_html__('No autorizado.'));
    $tab=isset($_POST['mct_team_tab'])?sanitize_key(wp_unslash($_POST['mct_team_tab'])):'';
    $groups=mct_team_groups();
    if(!isset($groups[$tab])) wp_die('Sección inválida.');
    check_admin_referer('mct_team_save_'.$tab,'mct_team_nonce');
    $raw=isset($_POST['mct_team_fields'])&&is_array($_POST['mct_team_fields'])?wp_unslash($_POST['mct_team_fields']):array();
    $topics=function_exists('mac_topics')?mac_topics():array();
    foreach($groups[$tab] as $id=>$field){
        $input=$raw[$id]??'';
        if(!is_scalar($input)) continue;
        $input=(string)$input;
        switch($field['type']){
            case 'checkbox': $value=$input==='1';break;
            case 'color': $value=sanitize_hex_color($input)?:'';break;
            case 'url': $value=esc_url_raw($input,array('http','https'));break;
            case 'number': case 'media': $value=absint($input);break;
            case 'textarea': $value=sanitize_textarea_field($input);break;
            case 'select': $value=isset($field['choices'][$input])?$input:'white';break;
            default: $value=sanitize_text_field($input);
        }
        switch($field['kind']){
            case 'design': mct_team_save_design($field['key'],$value);break;
            case 'ui': set_theme_mod('mct_ui_'.$field['key'],$value);break;
            case 'copy': set_theme_mod('mct_copy_'.$field['key'],$value);break;
            case 'theme': case 'flag': if($field['key']==='mct_careers_url') $value=mct_careers_sanitize_https_url($value); set_theme_mod($field['key'],$value);break;
            case 'option': update_option($field['key'],$value);break;
            case 'topic': if($value!=='') $topics[$field['key']]=$value;break;
        }
    }
    if($tab==='ayuda' && $topics) update_option('mac_help_topics',$topics);
    wp_safe_redirect(add_query_arg(array('page'=>'miniso-centro','tab'=>$tab,'guardado'=>'1'),admin_url('admin.php'))); exit;
});

add_action('admin_menu',function(){
    add_menu_page('Centro MINISO','Centro MINISO','edit_pages','miniso-centro','mct_team_admin_page','dashicons-store',3);
},20);
add_action('admin_bar_menu',function($bar){
    if(current_user_can('edit_pages')) $bar->add_node(array('id'=>'mct-marketing','title'=>'Centro MINISO','href'=>admin_url('admin.php?page=miniso-centro')));
},90);
add_action('admin_enqueue_scripts',function($hook){
    if($hook!=='toplevel_page_miniso-centro') return;
    wp_enqueue_style('mct-team-admin',get_template_directory_uri().'/assets/team-admin.css',array(),wp_get_theme()->get('Version'));
    wp_enqueue_media();
    wp_enqueue_script('mct-team-admin',get_template_directory_uri().'/assets/team-admin.js',array('jquery'),wp_get_theme()->get('Version'),true);
});

function mct_team_admin_page(){
    if(!current_user_can('edit_pages')) wp_die('No autorizado');
    $tabs=array('inicio'=>'Panel','portada'=>'Portada','catalogo'=>'Catálogo','novedades'=>'Novedades','lover'=>'Lover+','sucursales'=>'Sucursales','redes'=>'Redes y video','general'=>'Textos generales','apariencia'=>'Colores','ayuda'=>'Ayuda','reclutamiento'=>'Equipo y LinkedIn','publicacion'=>'Publicación');
    $tab=isset($_GET['tab'])?sanitize_key(wp_unslash($_GET['tab'])):'inicio';
    if(!isset($tabs[$tab])) $tab='inicio';
    echo '<div class="wrap mct-team-admin"><div class="mct-team-top"><div><span class="mct-team-overline">MINISO GUATEMALA · EQUIPO EDITORIAL</span><h1>Centro MINISO</h1><p>Un lugar para actualizar el contenido. Los cambios se guardan en WordPress y mantienen el diseño de la marca.</p></div><a class="button button-primary" href="'.esc_url(home_url('/')).'" target="_blank" rel="noopener noreferrer">Ver sitio ↗</a></div>';
    echo '<nav class="mct-team-tabs" aria-label="Secciones de MINISO">';
    foreach($tabs as $k=>$label) echo '<a class="'.($k===$tab?'is-current':'').'" href="'.esc_url(add_query_arg(array('page'=>'miniso-centro','tab'=>$k),admin_url('admin.php'))).'">'.esc_html($label).'</a>';
    echo '</nav>';
    if(!empty($_GET['guardado'])) echo '<div class="notice notice-success is-dismissible"><p>Los cambios se guardaron correctamente. Comprueba el resultado con «Ver sitio».</p></div>';
    if($tab==='inicio'){mct_team_dashboard();}
    elseif($tab==='publicacion'){mct_team_workflow();}
    else {
        $groups=mct_team_groups();
        $fields=$groups[$tab]??array();
        echo '<div class="mct-team-panel"><h2>'.esc_html($tabs[$tab]).'</h2>';
        echo '<p>'.esc_html(mct_team_hint($tab)).'</p>';
        if($tab==='catalogo') mct_team_actions(array('Productos'=>admin_url('edit.php?post_type=product'),'Licencias'=>admin_url('edit-tags.php?taxonomy=pa_licencia&post_type=product')));
        if($tab==='novedades') mct_team_actions(array('Publicaciones de Novedades'=>admin_url('edit.php?post_type=miniso_novedad'),'Editar página Novedades'=>mct_team_edit_page('novedades')));
        if($tab==='sucursales') mct_team_actions(array('Administrar tiendas'=>admin_url('edit.php?post_type=miniso_sucursal')));
        if($tab==='ayuda') mct_team_actions(array('Editar preguntas'=>mct_team_edit_page('ayuda')));
        if($tab==='portada') mct_team_actions(array('Editar bloques de Inicio'=>mct_team_edit_page('inicio'),'Gestionar banners'=>admin_url('edit.php?post_type=miniso_banner')));
        if($tab==='lover') mct_team_actions(array('Editar página Lover+'=>mct_team_edit_page('lover')));
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
        wp_nonce_field('mct_team_save_'.$tab,'mct_team_nonce');
        echo '<input type="hidden" name="action" value="mct_team_save"><input type="hidden" name="mct_team_tab" value="'.esc_attr($tab).'">';
        echo '<div class="mct-team-fields">';
        foreach($fields as $id=>$field) mct_team_field($id,$field);
        echo '</div><p><button class="button button-primary button-hero" type="submit">Guardar cambios</button></p></form></div>';
    }
    echo '<footer class="mct-team-footer">MINISO · Entorno editorial · Los precios, campañas y condiciones deben verificarse antes de publicarse. <strong>Life is for fun</strong></footer></div>';
}
function mct_team_hint($tab) {
    $map=array('portada'=>'Edita la presentación general. Las imágenes y textos individuales de banners se cambian en Banners MINISO.','catalogo'=>'Ajusta titulares y botones. Los productos y las colecciones tienen sus propios editores.','novedades'=>'Personaliza la sección principal. Para fechas, videos, ubicaciones y campañas de una noticia, abre su ficha.','lover'=>'Revisa que los beneficios y restricciones coincidan con el programa vigente.','sucursales'=>'Los horarios y ubicaciones reales se cambian en cada sucursal.','redes'=>'Revisa los enlaces oficiales antes de compartirlos.','general'=>'Nombres del menú y mensajes globales.','apariencia'=>'Los contrastes deben ser legibles. Rojo MINISO + letras blancas es la combinación recomendada.','ayuda'=>'Renombra categorías. Para editar cada pregunta y respuesta abre la página Ayuda.','reclutamiento'=>'Texto institucional y botón hacia LinkedIn. Publica únicamente vacantes confirmadas.');
    return $map[$tab]??'';
}
function mct_team_field($id,$field) {
    $value=mct_team_get_value($field); $type=$field['type'];
    $field_id='mct-team-'.sanitize_html_class($id);
    echo '<div class="mct-team-field"><label for="'.esc_attr($field_id).'">'.esc_html($field['label']).'</label>';
    $name='mct_team_fields['.$id.']';
    if($type==='media') {
        $img=absint($value)?wp_get_attachment_image(absint($value),'thumbnail',false,array('class'=>'mct-team-media-preview')):'';
        echo '<div class="mct-team-media">';
        echo '<div class="mct-team-media-image">'.$img.'</div>';
        echo '<input type="hidden" id="'.esc_attr($field_id).'" class="mct-team-media-value" name="'.esc_attr($name).'" value="'.absint($value).'">';
        echo '<button type="button" class="button mct-team-media-pick">Seleccionar fotografía</button> <button type="button" class="button mct-team-media-clear">Quitar</button>';
        echo '</div>';
    } elseif($type==='textarea') echo '<textarea id="'.esc_attr($field_id).'" name="'.esc_attr($name).'" rows="3">'.esc_textarea((string)$value).'</textarea>';
    elseif($type==='checkbox') {
       echo '<input type="hidden" name="'.esc_attr($name).'" value="0"><label class="mct-team-check"><input type="checkbox" id="'.esc_attr($field_id).'" name="'.esc_attr($name).'" value="1" '.checked((bool)$value,true,false).'> Activar</label>';
    } elseif($type==='select'){
      echo '<select id="'.esc_attr($field_id).'" name="'.esc_attr($name).'">';
      foreach($field['choices'] as $key=>$label)echo '<option value="'.esc_attr($key).'" '.selected($value,$key,false).'>'.esc_html($label).'</option>';
      echo '</select>';
    } else echo '<input id="'.esc_attr($field_id).'" type="'.esc_attr($type).'" name="'.esc_attr($name).'" value="'.esc_attr((string)$value).'" '.($type==='number'?'min="0"':'').'>';
    echo '</div>';
}
function mct_team_edit_page($key){
    $pages=get_option('miniso_catalog_pages',array()); $page=0;
    if(is_array($pages)&&!empty($pages[$key]))$page=absint($pages[$key]);
    if(!$page){$slug=$key==='licencias'?'catalogo':$key; $post=get_page_by_path($slug);if($post instanceof WP_Post)$page=$post->ID;}
    return $page?get_edit_post_link($page,''):admin_url('edit.php?post_type=page');
}
function mct_team_actions($items){
    echo '<div class="mct-team-actions">';
    foreach($items as $label=>$link) echo '<a class="button button-secondary" href="'.esc_url($link).'">'.esc_html($label).' ↗</a>';
    echo '</div>';
}
function mct_team_dashboard(){
    $items=array(
       array('icon'=>'🏠','name'=>'Inicio y banners','desc'=>'Orden, títulos, módulos y campañas','link'=>mct_team_edit_page('inicio'),'label'=>'Editar Inicio'),
       array('icon'=>'🛍️','name'=>'Productos','desc'=>'Fichas, fotografías, precios y descuentos','link'=>admin_url('edit.php?post_type=product'),'label'=>'Ver productos'),
       array('icon'=>'🎨','name'=>'Colecciones','desc'=>'Licencias, campañas y temporadas','link'=>admin_url('edit-tags.php?taxonomy=pa_licencia&post_type=product'),'label'=>'Ver licencias'),
       array('icon'=>'✨','name'=>'Novedades','desc'=>'Lanzamientos, fechas, eventos y videos','link'=>admin_url('edit.php?post_type=miniso_novedad'),'label'=>'Ver novedades'),
       array('icon'=>'📍','name'=>'Sucursales','desc'=>'Ubicación, horarios y enlaces a Maps','link'=>admin_url('edit.php?post_type=miniso_sucursal'),'label'=>'Ver sucursales'),
       array('icon'=>'❓','name'=>'Ayuda','desc'=>'Preguntas desplegables y sus categorías','link'=>mct_team_edit_page('ayuda'),'label'=>'Editar Ayuda'),
       array('icon'=>'❤️','name'=>'Lover+','desc'=>'Beneficios, restricciones y categorías','link'=>mct_team_edit_page('lover'),'label'=>'Editar Lover+'),
       array('icon'=>'💼','name'=>'Trabaja con nosotros','desc'=>'Cultura MINISO, LinkedIn y botón','link'=>admin_url('admin.php?page=miniso-centro&tab=reclutamiento'),'label'=>'Editar sección'),
    );
    echo '<div class="mct-team-panel"><h2>¿Qué quieres actualizar hoy?</h2><p>Usa estos accesos para cambiar contenido. Para colores, botones y textos generales utiliza las pestañas superiores.</p><div class="mct-team-cards">';
    foreach($items as $item){ echo '<article class="mct-team-card"><div class="mct-team-icon" aria-hidden="true">'.esc_html($item['icon']).'</div><h3>'.esc_html($item['name']).'</h3><p>'.esc_html($item['desc']).'</p><a href="'.esc_url($item['link']).'">'.esc_html($item['label']).' →</a></article>'; }
    echo '</div></div>';
    echo '<div class="mct-team-panel"><h2>Comprobaciones de entrega</h2><ul class="mct-team-checklist">';
    $check=array(
      array(function_exists('wc_get_product'),'WooCommerce activo','Necesario para las fichas de productos'),
      array(function_exists('miniso_catalog_settings') && function_exists('miniso_design'),'MINISO Catálogo base activo','Necesario para banners, sucursales y configuración principal'),
      array((bool)mct_home_page_id(),'Página Inicio configurada','Revisa la portada estática en Ajustes → Lectura'),
    );
    foreach($check as $row) echo '<li><span class="'.($row[0]?'mct-ok':'mct-warn').'">'.($row[0]?'✓':'!').'</span><strong>'.esc_html($row[1]).'</strong><small>'.esc_html($row[2]).'</small></li>';
    echo '</ul><p><a class="button" href="'.esc_url(admin_url('admin.php?page=miniso-centro&tab=publicacion')).'">Ver guía de publicación</a></p></div>';
}
function mct_team_workflow(){
    echo '<div class="mct-team-panel"><h2>Flujo recomendado para el equipo</h2><div class="mct-team-cards">';
    foreach(array(
      array('1','Preparar','Crear o editar como borrador. Usar fotografías oficiales y revisar ortografía.'),
      array('2','Validar','Marketing revisa identidad, precios y vigencia; Operaciones confirma tiendas y horarios.'),
      array('3','Publicar','La persona autorizada publica y comprueba móvil, escritorio y los enlaces.'),
      array('4','Mantener','Revisar semanalmente promociones, contenidos vencidos y enlaces a redes.'),
    ) as $row) echo '<article class="mct-team-card"><div class="mct-team-number">'.esc_html($row[0]).'</div><h3>'.esc_html($row[1]).'</h3><p>'.esc_html($row[2]).'</p></article>';
    echo '</div><h3>Permisos recomendados</h3><p><strong>Administrador:</strong> temas, extensiones, usuarios y configuraciones sensibles. <strong>Editor:</strong> contenido general y textos del Centro MINISO. <strong>Gestor de tienda (WooCommerce):</strong> productos y promociones según autorizaciones de la empresa. No compartir contraseñas ni dar acceso de administrador a todos.</p><h3>Antes de compartir una campaña</h3><p>Verifica fotografía oficial, precios vigentes, fechas, exclusiones, enlaces, disponibilidad y aprobación de Marketing. Los informes de Analytics deben ser administrados por la empresa.</p><p><strong>Importante:</strong> este paquete no implementa aprobaciones automáticas ni conectores de inventario; los responsables validan el contenido antes de publicarlo.</p></div>';
}
