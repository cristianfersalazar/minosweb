<?php
defined( 'ABSPATH' ) || exit;

// EDITAR · Valores iniciales; todos estos ajustes tienen controles en el Personalizador.
function miniso_design_schema() {
    return array(
        'primary' => array('identidad','Color principal','color','#e60023'),
        'secondary' => array('identidad','Color secundario','color','#146c52'),
        'background' => array('identidad','Fondo general','color','#ffffff'),
        'ink' => array('identidad','Texto general','color','#171717'),
        'font' => array('identidad','Fuente','select','poppins',array('poppins'=>'Poppins','system'=>'Sistema','arial'=>'Arial')),
        'body_size' => array('identidad','Tamaño del texto (px)','number',16,14,22),
        'width' => array('identidad','Ancho del contenido (px)','number',1360,960,1680),
        'radius' => array('identidad','Redondeado (px)','number',18,0,40),
        'motion' => array('identidad','Animaciones suaves','checkbox',true),
        'announcement' => array('identidad','Texto de la franja superior','text','LIFE IS FOR FUN · MINISO GUATEMALA'),
        'announcement_color' => array('identidad','Color de la franja superior','color','#e60023'),
        'footer_text' => array('identidad','Texto del pie','text','Descubre tus colecciones favoritas y vive MINISO en tu tienda más cercana.'),
        'hero_title' => array('banner','Título del banner principal','text','LIFE IS FOR FUN'),
        'hero_button' => array('banner','Texto del botón principal','text','Encuentra tu MINISO'),
        'hero_url' => array('banner','Enlace del botón (vacío: sucursales)','url',''),
        'hero_bg' => array('banner','Fondo del banner principal','color','#fff4e4'),
        'hero_height' => array('banner','Altura máxima de imagen en escritorio (px)','number',580,320,800),
        'hero_mobile_height' => array('banner','Altura de imagen en teléfono (px)','number',340,220,600),
        'hero_autoplay' => array('banner','Barrido automático del carrusel','checkbox',true),
        'hero_hover_pause' => array('banner','Pausar temporalmente al pasar el cursor','checkbox',false),
        'hero_interval' => array('banner','Segundos entre banners','number',7,4,15),
        'hero_collections' => array('banner','Usar colecciones destacadas si no hay banners publicados','checkbox',true),
        'show_licenses' => array('licencias','Mostrar licencias en Inicio','checkbox',true),
        'licenses_title' => array('licencias','Título de licencias','text','Tus mundos favoritos'),
        'licenses_text' => array('licencias','Texto de licencias','text','Descubre las colecciones que hacen tu día más divertido.'),
        'licenses_button' => array('licencias','Botón de todas las licencias','text','Ver todas las licencias'),
        'collection_button' => array('licencias','Botón de cada colección','text','Explorar colección'),
        'licenses_bg' => array('licencias','Fondo de la sección','color','#fff3fa'),
        'collection_ratio' => array('licencias','Espacio para la imagen completa','select','1 / 1',array('1 / 1'=>'Cuadrado 1:1','4 / 5'=>'Vertical 4:5','4 / 3'=>'Horizontal 4:3')),
        'collection_columns' => array('licencias','Columnas en la página Licencias','number',3,2,4),
        'caption_darkness' => array('licencias','Oscuridad del sombreado inferior (%)','number',92,65,100),
        'show_news' => array('novedades','Mostrar novedades en Inicio','checkbox',true),
        'news_title' => array('novedades','Título de novedades','text','Lo nuevo en MINISO'),
        'news_text' => array('novedades','Texto de novedades','text','Lanzamientos, promociones y momentos para compartir.'),
        'news_button' => array('novedades','Botón de todas las novedades','text','Ver novedades'),
        'news_bg' => array('novedades','Fondo de novedades','color','#ecf8f1'),
        'news_count' => array('novedades','Novedades en Inicio','number',3,1,6),
        'show_lover' => array('beneficios','Mostrar beneficios en Inicio','checkbox',true),
        'lover_bg' => array('beneficios','Fondo de Lover+','color','#d7edff'),
        'lover_button' => array('beneficios','Botón de beneficios','text','Conocer Lover+'),
        'show_stores' => array('beneficios','Mostrar invitación a visitar tiendas','checkbox',true),
        'stores_title' => array('beneficios','Título de tiendas','text','Lo mejor es vivirlo en persona.'),
        'stores_text' => array('beneficios','Texto de tiendas','text','Encuentra tu MINISO más cercano. Te esperamos.'),
        'stores_button' => array('beneficios','Botón de tiendas','text','Buscar sucursal'),
        'stores_bg' => array('beneficios','Fondo de tiendas','color','#ffe575'),
        'map_button' => array('beneficios','Texto del botón de mapa','text','Ver en el mapa'),
        'locator_title' => array('sucursales','Título del localizador','text','Encuentra tu MINISO'),
        'locator_text' => array('sucursales','Introducción del localizador','text','Tu próxima visita empieza aquí. Busca por tienda, centro comercial o dirección.'),
        'locator_color' => array('sucursales','Color del localizador','color','#e60023'),
        'locator_note' => array('sucursales','Aviso sobre horarios de referencia','text','Los horarios de referencia corresponden al centro comercial y pueden variar en MINISO y días festivos.'),
        'show_store_map' => array('sucursales','Mostrar mapa de la sucursal seleccionada','checkbox',true),
        'show_video' => array('video','Mostrar video en Inicio','checkbox',true),
        'video_title' => array('video','Título del apartado','text','Así se vive MINISO'),
        'video_text' => array('video','Texto del apartado','text','Colecciones, historias y momentos para compartir. Descubre lo que está pasando en MINISO Guatemala.'),
        'video_url' => array('video','Enlace de un reel público de Instagram','url','https://www.instagram.com/reel/Df44tkEh8ZZ/'),
        'video_bg' => array('video','Color de fondo','color','#fff3f5'),
        'video_position' => array('video','Ubicación en Inicio','select','before-stores',array('after-hero'=>'Después del banner','before-stores'=>'Antes de la invitación a tiendas','end'=>'Al final del contenido')),
        'social_effects' => array('redes','Efectos de fondo de cada red social','checkbox',true),
        'show_social' => array('redes','Mostrar sección de redes','checkbox',true),
        'social_title' => array('redes','Título de comunidad','text','La diversión sigue aquí'),
        'social_text' => array('redes','Texto de comunidad','text','Síguenos y comparte tus favoritos con MINISO Guatemala.'),
        'social_bg' => array('redes','Fondo de la sección','color','#f5edff'),
        'instagram_url' => array('redes','Enlace directo a Instagram','url','https://www.instagram.com/miniso.guatemalaoficial/'),
        'facebook_url' => array('redes','Enlace directo a Facebook','url','https://www.facebook.com/minisogua/'),
        'tiktok_url' => array('redes','Enlace directo a TikTok','url','https://www.tiktok.com/@minisogt'),
        'instagram_color' => array('redes','Color Instagram','color','#c72974'),
        'facebook_color' => array('redes','Color Facebook','color','#1469ce'),
        'tiktok_color' => array('redes','Color TikTok','color','#181818'),
        'home_order' => array('orden','Orden de secciones','select','licenses-news-lover-stores',array('licenses-news-lover-stores'=>'Licencias, novedades, Lover+, tiendas','news-licenses-lover-stores'=>'Novedades, licencias, Lover+, tiendas','licenses-lover-news-stores'=>'Licencias, Lover+, novedades, tiendas')),
        'nav_inicio' => array('orden','Menú: Inicio','text','Inicio'),
        'nav_licencias' => array('orden','Menú: Licencias','text','Licencias'),
        'nav_novedades' => array('orden','Menú: Novedades','text','Novedades'),
        'nav_sucursales' => array('orden','Menú: Sucursales','text','Sucursales'),
        'nav_lover' => array('orden','Menú: Lover+','text','Lover+'),
        'nav_ayuda' => array('orden','Menú: Ayuda','text','Ayuda'),
    );
}
function miniso_design( $key ) {
    $schema = miniso_design_schema();
    if ( ! isset($schema[$key]) ) return '';
    $saved = get_option('miniso_design', array());
    if ( isset($saved[$key]) ) return miniso_design_clean($saved[$key], $key);
    $legacy = array('primary'=>'miniso_poppins_primary','secondary'=>'miniso_poppins_secondary','motion'=>'miniso_poppins_motion');
    return isset($legacy[$key]) ? miniso_design_clean(get_theme_mod($legacy[$key],$schema[$key][3]),$key) : $schema[$key][3];
}
function miniso_design_clean( $value, $key ) {
    $f = miniso_design_schema()[$key] ?? null;
    if (!$f) return '';
    if (!is_scalar($value) && null !== $value) return $f[3];
    switch ($f[2]) {
        case 'color': return sanitize_hex_color($value) ?: $f[3];
        case 'checkbox': return rest_sanitize_boolean($value);
        case 'number': return max($f[4],min($f[5],absint($value)));
        case 'select': return array_key_exists((string)$value,$f[4]) ? $value : $f[3];
        case 'url': return 'video_url'===$key ? miniso_instagram_url($value) : esc_url_raw($value,array('http','https'));
        default: return sanitize_text_field($value);
    }
}
function miniso_collection_color( $term ) {
    $saved = sanitize_hex_color(get_term_meta($term->term_id,'miniso_color',true));
    if ($saved) return $saved;
    $colors = array('naruto'=>'#f5a623','marvel'=>'#117650','sanrio'=>'#ed8bbb','kpop-demon-hunters'=>'#9572df','one-piece'=>'#36a9dd','fifa'=>'#1e824c','star-wars'=>'#e7c85b','nommi'=>'#d7aed6','mofusand'=>'#9cbf92','chiikawa'=>'#a7d9ed','stitch'=>'#429dde','snoopy'=>'#eb7d64','winnie-the-pooh'=>'#efbf45','minions'=>'#f0d637');
    return $colors[$term->slug] ?? miniso_design('primary');
}

// FUNCIÓN · Noticias y banners se administran por separado del catálogo de productos.
add_action('init', function () {
    $caps = array();
    foreach (array('edit_posts','edit_others_posts','publish_posts','read_private_posts','delete_posts','delete_private_posts','delete_published_posts','delete_others_posts','edit_private_posts','edit_published_posts','create_posts') as $cap) $caps[$cap]='manage_woocommerce';
    foreach (array('miniso_banner'=>array('Banners MINISO','Banner',false),'miniso_novedad'=>array('Novedades MINISO','Novedad',true)) as $type=>$data) {
        register_post_type($type,array('labels'=>array('name'=>$data[0],'singular_name'=>$data[1],'add_new_item'=>'Agregar '.$data[1],'edit_item'=>'Editar '.$data[1]),'public'=>$data[2],'publicly_queryable'=>$data[2],'show_ui'=>true,'show_in_rest'=>true,'show_in_menu'=>'woocommerce','supports'=>array('title','editor','excerpt','thumbnail','page-attributes','revisions'),'capability_type'=>'post','map_meta_cap'=>true,'capabilities'=>$caps,'has_archive'=>false,'rewrite'=>$data[2]?array('slug'=>'novedad'):false));
    }
},25);
add_action('add_meta_boxes',function(){
    foreach(array('miniso_banner','miniso_novedad') as $type) add_meta_box('miniso-presentation','Presentación y enlace',function($post){
        wp_nonce_field('miniso_presentation','miniso_presentation_nonce');
        echo '<p>La imagen destacada se muestra completa. El título y el extracto aparecen debajo. El campo Orden define la posición de los banners.</p>';
        echo '<p><label>Color destacado <input type="color" name="miniso_accent" value="'.esc_attr(sanitize_hex_color(get_post_meta($post->ID,'miniso_accent',true))?:'#e60023').'"></label></p>';
        if ('miniso_banner'===$post->post_type) {
            echo '<p><label>Texto del botón <input class="widefat" name="miniso_button" value="'.esc_attr(get_post_meta($post->ID,'miniso_button',true)).'" placeholder="Explorar colección"></label></p><p><label>Enlace del botón <input type="url" class="widefat" name="miniso_link" value="'.esc_attr(get_post_meta($post->ID,'miniso_link',true)).'"></label></p><p>Imagen alternativa para teléfono (opcional):</p>';
            miniso_catalog_image_field('miniso_mobile_image',get_post_meta($post->ID,'miniso_mobile_image',true));
        } else {
            $label = get_post_meta($post->ID,'miniso_news_label',true) ?: 'Novedad';
            echo '<p><label>Tipo <select name="miniso_news_label">';
            foreach(array('Novedad','Lanzamiento','Promoción','Evento') as $option) echo '<option '.selected($label,$option,false).'>'.esc_html($option).'</option>';
            echo '</select></label></p><p>Publica para mostrar esta noticia. Los borradores no aparecen en la página.</p>';
        }
    },$type,'normal','high');
});
add_action('save_post',function($id){
    if (!in_array(get_post_type($id),array('miniso_banner','miniso_novedad'),true)||wp_is_post_revision($id)||wp_is_post_autosave($id)||!current_user_can('edit_post',$id)||empty($_POST['miniso_presentation_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['miniso_presentation_nonce'])),'miniso_presentation')) return;
    update_post_meta($id,'miniso_accent',sanitize_hex_color(wp_unslash($_POST['miniso_accent']??''))?:'#e60023');
    if ('miniso_banner'===get_post_type($id)) {
        update_post_meta($id,'miniso_button',sanitize_text_field(wp_unslash($_POST['miniso_button']??'')));
        update_post_meta($id,'miniso_link',esc_url_raw(wp_unslash($_POST['miniso_link']??''),array('http','https')));
        update_post_meta($id,'miniso_mobile_image',miniso_catalog_image_id($_POST['miniso_mobile_image']??0));
    } else {
        $label=sanitize_text_field(wp_unslash($_POST['miniso_news_label']??'Novedad'));
        update_post_meta($id,'miniso_news_label',in_array($label,array('Novedad','Lanzamiento','Promoción','Evento'),true)?$label:'Novedad');
    }
});

// FUNCIÓN · Migración conservadora: añade Licencias y solo actualiza textos iniciales sin editar.
function miniso_catalog_upgrade_design() {
    if (!get_option('miniso_catalog_pages')) return;
    $pages=get_option('miniso_catalog_pages',array());
    if (empty($pages['licencias']) || !get_post($pages['licencias']) || 'trash'===get_post_status($pages['licencias'])) {
        $existing=get_page_by_path('licencias');
        $id=$existing ? $existing->ID : wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>'Licencias','post_name'=>'licencias','post_content'=>'<p>Encuentra tu colección favorita y descubre todos sus productos.</p>','meta_input'=>array('_wp_page_template'=>'templates/licencias.php')),true);
        if (is_wp_error($id)) return;
        $pages['licencias']=$id;
        update_option('miniso_catalog_pages',$pages,false);
    }
    if (!empty($pages['novedades']) && '<p>Explora nuestras colecciones y descubre sus productos.</p>'===get_post_field('post_content',$pages['novedades'])) wp_update_post(array('ID'=>$pages['novedades'],'post_content'=>'<p>Lanzamientos, promociones y noticias de MINISO Guatemala.</p>'));
    update_option('miniso_catalog_design_version','2.0.0',false);
    flush_rewrite_rules(false);
}
add_action('admin_init',function(){if(current_user_can('manage_options') && '2.0.0'!==get_option('miniso_catalog_design_version')) miniso_catalog_upgrade_design();});

// FUNCIÓN · Carga opcional de las portadas descargadas; nunca reemplaza fotos o colores existentes.
function miniso_catalog_demo_covers(){
    if(!taxonomy_exists('pa_licencia'))return new WP_Error('miniso_no_license','Prepara primero el catálogo.');
    $records=json_decode(file_get_contents(MINISO_CATALOG_DIR.'official/collaborations.json'),true);
    $s=miniso_catalog_settings();$errors=array();
    foreach($records as $record){
        $term=get_term_by('slug',$record['slug'],'pa_licencia');
        if(!$term){$created=wp_insert_term($record['name'],'pa_licencia',array('slug'=>$record['slug']));if(is_wp_error($created)){$errors[]=$created->get_error_message();continue;}$term=get_term($created['term_id'],'pa_licencia');}
        if(!get_term_meta($term->term_id,'miniso_cover_id',true)){
            $id=miniso_catalog_import_official_image('collaborations/'.$record['file'],'Colección '.$record['name'].' × MINISO');
            if(is_wp_error($id)){$errors[]=$id->get_error_message();continue;}
            update_term_meta($term->term_id,'miniso_cover_id',$id);
        }
        if(!get_term_meta($term->term_id,'miniso_color',true))update_term_meta($term->term_id,'miniso_color',miniso_collection_color($term));
        if(count($s['featured'])<8 && !in_array((int)$term->term_id,array_map('intval',$s['featured']),true))$s['featured'][]=(int)$term->term_id;
    }
    update_option('miniso_catalog_settings',$s,false);
    return $errors;
}
add_action('admin_post_miniso_demo_covers',function(){
    if(!current_user_can('manage_woocommerce'))wp_die('No tienes permiso.');
    check_admin_referer('miniso_demo_covers');
    $result=miniso_catalog_demo_covers();
    update_option('miniso_catalog_setup_errors',is_wp_error($result)?array($result->get_error_message()):$result,false);
    wp_safe_redirect(admin_url('admin.php?page=miniso-catalogo&miniso_saved=1'));exit;
});
