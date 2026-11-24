<?php
/** Elementos de estructura visibles desde el Personalizador, sin editar PHP. */
defined('ABSPATH') || exit;
function mct_ui_text($name) {
    $defaults = array(
        'licenses_label'=>'LICENCIAS · COLABORACIONES',
        'news_label'=>'NOVEDADES',
        'lover_label'=>'MINISO ★ LOVER+',
        'social_label'=>'COMUNIDAD MINISO GUATEMALA',
        'video_label'=>'EN NUESTRA COMUNIDAD',
        'video_account'=>'@miniso.guatemalaoficial',
        'social_subtext'=>'MINISO Guatemala',
        'footer_brand'=>'MINISO Guatemala',
        'footer_slogan'=>'LIFE IS FOR FUN',
    );
    return get_theme_mod('mct_ui_'.$name,$defaults[$name]??'');
}
add_action('customize_register',function($wp_customize){
    $wp_customize->add_section('mct_ui_structure',array('title'=>'MINISO · Etiquetas y pie de página',
        'description'=>'Los cambios de estas opciones se reflejan en el sitio. Puedes vaciar una etiqueta para ocultarla.',
        'priority'=>34));
    $fields=array(
        'licenses_label'=>'Etiqueta del módulo Licencias',
        'news_label'=>'Etiqueta del módulo Novedades',
        'lover_label'=>'Etiqueta del módulo Lover+',
        'social_label'=>'Etiqueta de Redes',
        'video_label'=>'Etiqueta de Video',
        'video_account'=>'Usuario mostrado en Video',
        'social_subtext'=>'Texto debajo de cada red',
        'footer_brand'=>'Marca en el pie de página',
        'footer_slogan'=>'Frase del pie de página',
    );
    foreach($fields as $key=>$label) {
        $id='mct_ui_'.$key;
        $wp_customize->add_setting($id,array('default'=>mct_ui_text($key),
            'type'=>'theme_mod','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh'));
        $wp_customize->add_control($id,array('type'=>'text','section'=>'mct_ui_structure','label'=>$label));
    }
},110);
