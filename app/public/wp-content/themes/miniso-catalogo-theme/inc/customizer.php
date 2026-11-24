<?php
defined('ABSPATH') || exit;
function mct_design($key) {
    // Solo se usa el respaldo del tema cuando la versión del plugin base
    // no ofrece ese campo en el Personalizador.
    $fallback=get_theme_mod('mct_copy_override_'.$key, null);
    if (null !== $fallback) return $fallback;
    return function_exists('miniso_design') ? miniso_design($key) : '';
}
add_action('customize_register',function($c){
    if (!function_exists('miniso_design_schema')) return;
    $c->add_panel('miniso_design_panel',array('title'=>'MINISO · Diseño general','priority'=>30,'description'=>'Personaliza la presentación. Banners y noticias: WooCommerce. Licencias: Productos → Licencias y portadas. Promociones y temporadas: Productos → Campañas / Temporadas MINISO.'));
    foreach(array('identidad'=>'Identidad, fuente y apariencia','banner'=>'Banner y carrusel','licencias'=>'Catálogo y colecciones','novedades'=>'Novedades','beneficios'=>'Lover+ y sucursales','sucursales'=>'Localizador de sucursales','video'=>'Video de Instagram','redes'=>'Redes de Guatemala','orden'=>'Orden y menú') as $key=>$label) $c->add_section('miniso_design_'.$key,array('title'=>$label,'panel'=>'miniso_design_panel'));
    foreach(miniso_design_schema() as $key=>$f) {
        $id='miniso_design['.$key.']';
        $c->add_setting($id,array('type'=>'option','default'=>miniso_design($key),'sanitize_callback'=>function($v)use($key){return miniso_design_clean($v,$key);},'transport'=>'refresh','capability'=>'edit_theme_options'));
        $args=array('label'=>$f[1],'section'=>'miniso_design_'.$f[0],'type'=>$f[2]);
        if ('select'===$f[2]) $args['choices']=$f[4];
        if ('number'===$f[2]) $args['input_attrs']=array('min'=>$f[4],'max'=>$f[5],'step'=>1);
        if ('color'===$f[2]) $c->add_control(new WP_Customize_Color_Control($c,$id,$args)); else $c->add_control($id,$args);
    }
    // Compatibilidad: estos controles sustituyen el complemento de Poppins anterior.
    $c->remove_section('miniso_poppins');
},100);
add_filter('body_class',function($classes){
    $classes=array_diff($classes,array('miniso-poppins','miniso-motion'));
    $classes[]='miniso-v2';
    $pages=get_option('miniso_catalog_pages',array());
    if(is_front_page() || (!empty($pages['inicio']) && is_page($pages['inicio'])) || is_page_template('templates/inicio-flexible.php'))$classes[]='mct-home';
    if (mct_is_lover_page()) $classes[]='mct-lover-page';
    if (mct_design('social_effects')) $classes[]='miniso-social-effects';
    if (mct_design('motion')) $classes[]='miniso-animate';
    return $classes;
},100);
add_action('wp_enqueue_scripts',function(){
    // Una sola fuente y una sola animación, incluso con el complemento anterior activo.
    wp_dequeue_style('miniso-poppins'); wp_dequeue_script('miniso-poppins-motion');
    if (!function_exists('miniso_design_schema')) return;
    $vars=array('--red'=>mct_design('primary'),'--secondary'=>mct_design('secondary'),'--page-bg'=>mct_design('background'),'--ink'=>mct_design('ink'),'--radius'=>mct_design('radius').'px','--page-width'=>mct_design('width').'px','--body-size'=>mct_design('body_size').'px','--hero-height'=>mct_design('hero_height').'px','--hero-mobile-height'=>mct_design('hero_mobile_height').'px','--collection-ratio'=>mct_design('collection_ratio'),'--collection-columns'=>mct_design('collection_columns'),'--caption-opacity'=>mct_design('caption_darkness')/100,'--licenses-bg'=>mct_design('licenses_bg'),'--news-bg'=>mct_design('news_bg'),'--lover-bg'=>mct_design('lover_bg'),'--stores-bg'=>mct_design('stores_bg'),'--social-bg'=>mct_design('social_bg'),'--announcement-bg'=>mct_design('announcement_color'),'--locator-color'=>mct_design('locator_color'),'--video-bg'=>mct_design('video_bg'));
    $fonts=array('poppins'=>'Poppins,Arial,sans-serif','system'=>'system-ui,sans-serif','arial'=>'Arial,sans-serif');
    $vars['--font']=$fonts[mct_design('font')]??$fonts['poppins'];
    foreach(array('licenses','news','lover','stores','social','video') as $section)$vars['--'.$section.'-ink']=mct_contrast(mct_design($section.'_bg'));
    $vars['--locator-ink']=mct_contrast(mct_design('locator_color'));
    $vars['--primary-ink']=mct_contrast(mct_design('primary'));
    $vars['--announcement-ink']=mct_contrast(mct_design('announcement_color'));
    $css=':root{'; foreach($vars as $k=>$v) $css.=$k.':'.$v.';'; $css.='}';
    wp_add_inline_style('miniso-catalogo',$css);
},100);
// La fuente se carga como CSS web, sin distribuir archivos de tipografía.
add_action('wp_enqueue_scripts',function(){
    if ('poppins'===mct_design('font')) {
        wp_enqueue_style('mct-poppins-web','https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap',array(),null);
    }
},5);
