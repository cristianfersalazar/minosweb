<?php
defined('ABSPATH') || exit;

// FUNCIÓN · Negro o blanco según contraste del color elegido para fondos y botones.
function mct_contrast($hex) {
    $hex=ltrim($hex,'#'); if(strlen($hex)===3)$hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    $rgb=array_map('hexdec',str_split(str_pad($hex,6,'0'),2));
    $lum=0; foreach($rgb as $i=>$v){$v/=255;$lum+=($v<=0.04045?$v/12.92:pow(($v+0.055)/1.055,2.4))*array(0.2126,0.7152,0.0722)[$i];}
    return $lum>0.179?'#151515':'#ffffff';
}
function mct_accent_style($color) {return '--accent:'.$color.';--accent-ink:'.mct_contrast($color).';';}
function mct_featured_terms() {
    if (!mct_ready()) return array();
    $s=mct_settings();$terms=array();
    foreach($s['featured'] as $id){$term=get_term($id,'pa_licencia');if($term instanceof WP_Term && mct_catalog_legacy_type($term)==='licencias')$terms[]=$term;}
    if (!$terms){$terms=array_slice(mct_catalog_terms('licencias'),0,12);}
    return $terms;
}
function mct_banner_slides() {
    $s=mct_settings();$slides=array();
    if (!empty($s['hero'])) $slides[]=array('image'=>$s['hero'],'mobile'=>$s['hero_mobile']??0,'title'=>mct_design('hero_title'),'text'=>$s['hero_text'],'button'=>mct_design('hero_button'),'url'=>mct_design('hero_url')?:mct_url('sucursales'),'color'=>mct_design('hero_bg'));
    $posts=get_posts(array('post_type'=>'miniso_banner','post_status'=>'publish','has_password'=>false,'numberposts'=>12,'orderby'=>array('menu_order'=>'ASC','date'=>'DESC')));
    foreach($posts as $post){
        $image=get_post_thumbnail_id($post->ID); if(!$image)continue;
        $slides[]=array('image'=>$image,'mobile'=>get_post_meta($post->ID,'miniso_mobile_image',true),'title'=>$post->post_title,'text'=>wp_strip_all_tags($post->post_excerpt),'button'=>get_post_meta($post->ID,'miniso_button',true)?:mct_design('collection_button'),'url'=>get_post_meta($post->ID,'miniso_link',true)?:mct_url('licencias'),'color'=>sanitize_hex_color(get_post_meta($post->ID,'miniso_accent',true))?:'#e60023');
    }
    $custom_posts=array_filter($posts,function($post){return !get_post_meta($post->ID,'_miniso_seed_banner',true);});
    $seed_visible=(bool)array_filter($posts,function($post){return get_post_meta($post->ID,'_miniso_seed_banner',true);});
    if (!$custom_posts && mct_design('hero_collections')) foreach(array_slice(mct_featured_terms(),0,5) as $term){
        if ($seed_visible && 'naruto'===$term->slug) continue;
        $image=get_term_meta($term->term_id,'miniso_cover_id',true);$url=get_term_link($term);
        if($image&&!is_wp_error($url))$slides[]=array('image'=>$image,'mobile'=>0,'title'=>$term->name.' × MINISO','text'=>wp_trim_words(wp_strip_all_tags($term->description),18),'button'=>mct_design('collection_button'),'url'=>$url,'color'=>miniso_collection_color($term));
    }
    return $slides;
}
function mct_hero($overrides=array()) {
    $slides=mct_banner_slides();if(!$slides)return;
    $autoplay = array_key_exists('autoplay',$overrides) ? (bool)$overrides['autoplay'] : (bool)mct_design('hero_autoplay');
    $interval = array_key_exists('interval',$overrides) ? min(20,max(2,(int)$overrides['interval'])) : (int)mct_design('hero_interval');
    $show_captions = array_key_exists('showCaptions',$overrides) ? (bool)$overrides['showCaptions'] : true;
    echo '<section class="campaign-carousel full-bleed" data-carousel data-autoplay="'.($autoplay?'true':'false').'" data-hover-pause="'.(mct_design('hero_hover_pause')?'true':'false').'" data-interval="'.absint($interval*1000).'" aria-label="Banners destacados" aria-roledescription="carrusel">';
    echo '<div class="campaign-track" data-carousel-track tabindex="0" aria-label="Desliza para ver los banners">';
    foreach($slides as $i=>$s){
        echo '<article class="campaign-slide" data-carousel-slide role="group" aria-roledescription="diapositiva" aria-label="'.($i+1).' de '.count($slides).'" style="'.esc_attr(mct_accent_style($s['color'])).'">';
        echo '<a class="campaign-art" href="'.esc_url($s['url']).'" aria-label="'.esc_attr($s['title']).'"><picture>';
        if($s['mobile']){ $url=wp_get_attachment_image_url($s['mobile'],'full');if($url)echo '<source media="(max-width: 700px)" srcset="'.esc_url($url).'">'; }
        echo wp_get_attachment_image($s['image'],'full',false,array('alt'=>$s['title'],'loading'=>$i?'lazy':'eager','decoding'=>'async','fetchpriority'=>$i?'auto':'high','sizes'=>'100vw'));
        echo '</picture></a>';
        if($show_captions && ($s['title'] || $s['text'] || $s['button'])) {
            echo '<div class="campaign-caption"><div>';
            if($s['title']) echo '<'.($i?'h2':'h1').'>'.esc_html($s['title']).'</'.($i?'h2':'h1').'>';
            if($s['text']) echo '<p>'.esc_html($s['text']).'</p>';
            echo '</div>';
            if($s['button'] && $s['url']) echo '<a class="button contrast-button" href="'.esc_url($s['url']).'">'.esc_html($s['button']).'</a>';
            echo '</div>';
        }
        echo '</article>';
    }
    echo '</div>';
    if(count($slides)>1){
        echo '<div class="carousel-controls js-only"><button class="icon-button" data-carousel-prev aria-label="Banner anterior">'.mct_icon('prev').'</button><div class="carousel-dots">';
        foreach($slides as $i=>$s) echo '<button data-carousel-dot="'.$i.'" aria-label="Mostrar banner '.($i+1).': '.esc_attr($s['title']).'"'.(!$i?' aria-current="true"':'').'><span></span></button>';
        echo '</div><button class="icon-button" data-carousel-next aria-label="Banner siguiente">'.mct_icon('next').'</button><button class="icon-button carousel-play" data-carousel-pause aria-label="Pausar cambio automático">'.mct_icon('pause').'</button><span class="sr-only" data-carousel-status aria-live="polite"></span></div>';
    }
    echo '</section>';
}
function mct_collection_card($term) {
    $url=get_term_link($term);if(is_wp_error($url))return;
    $color=miniso_collection_color($term);
    echo '<article class="collection-card" data-collection="'.esc_attr($term->slug).'" style="'.esc_attr(mct_accent_style($color)).'">';
    echo '<a class="collection-link" href="'.esc_url($url).'">';
    mct_media(get_term_meta($term->term_id,'miniso_cover_id',true),'Colección '.$term->name,'collection-art');
    echo '<div class="collection-caption"><span class="eyebrow">MINISO ×</span><h3>'.esc_html($term->name).'</h3><span class="collection-cta">'.esc_html(mct_design('collection_button')).'</span></div></a>';
    echo '<button type="button" class="heart favorite-button" data-favorite="'.esc_attr($term->slug).'" data-name="'.esc_attr($term->name).'" aria-pressed="false" aria-label="Guardar '.esc_attr($term->name).'">'.mct_icon('heart').'</button></article>';
}
function mct_news_card($post) {
    $url=get_permalink($post);$color=sanitize_hex_color(get_post_meta($post->ID,'miniso_accent',true))?:'#146c52';
    echo '<article class="news-card" style="'.esc_attr(mct_accent_style($color)).'"><a href="'.esc_url($url).'">';
    if(has_post_thumbnail($post)) mct_media(get_post_thumbnail_id($post),'','news-art');
    echo '<div class="news-copy"><span class="news-label">'.esc_html(get_post_meta($post->ID,'miniso_news_label',true)?:mct_copy_text('news_default_badge')).'</span><h3>'.esc_html($post->post_title).'</h3><p>'.esc_html(wp_trim_words(wp_strip_all_tags($post->post_excerpt?:$post->post_content),22)).'</p><span class="news-read">'.esc_html(mct_copy_text('news_read')).'</span></div></a></article>';
}
function mct_home_section($section) {
    $s=mct_settings();
    if('licenses'===$section && mct_design('show_licenses')){
        echo '<section class="home-section licenses-section full-bleed" aria-labelledby="licenses-heading"><div class="section-inner"><div class="section-head"><div><span class="eyebrow">'.esc_html(mct_ui_text('licenses_label')).'</span><h2 id="licenses-heading">'.esc_html(mct_design('licenses_title')).'</h2><p>'.esc_html(mct_design('licenses_text')).'</p></div><a class="button outline" href="'.esc_url(mct_url('licencias')).'">'.esc_html(mct_design('licenses_button')).'</a></div><div class="collection-rail" data-rail style="--rail-columns:'.min(3,max(1,count(mct_featured_terms()))).'" tabindex="0" aria-label="Colecciones destacadas">';
        foreach(mct_featured_terms() as $term)mct_collection_card($term);
        echo '</div><div class="rail-controls js-only"><button class="icon-button" data-rail-prev aria-label="Colecciones anteriores">'.mct_icon('prev').'</button><button class="icon-button" data-rail-next aria-label="Más colecciones">'.mct_icon('next').'</button></div></div></section>';
    }
    if('news'===$section && mct_design('show_news')){
        echo '<section class="home-section news-section full-bleed" aria-labelledby="news-heading"><div class="section-inner"><div class="section-head"><div><span class="eyebrow">'.esc_html(mct_ui_text('news_label')).'</span><h2 id="news-heading">'.esc_html(mct_design('news_title')).'</h2><p>'.esc_html(mct_design('news_text')).'</p></div><a class="button outline" href="'.esc_url(mct_url('novedades')).'">'.esc_html(mct_design('news_button')).'</a></div><div class="news-grid">';
        $posts=get_posts(array('post_type'=>'miniso_novedad','post_status'=>'publish','has_password'=>false,'numberposts'=>mct_design('news_count')));
        foreach($posts as $post)mct_news_card($post);
        /* Publicaciones vacías: no mostrar contenido promocional de ejemplo. */
        echo '</div></div></section>';
    }
    if('lover'===$section && mct_design('show_lover')){
        echo '<section class="home-section lover-section full-bleed"><div class="section-inner loyalty">'; if(!empty($s['lover'])) mct_media($s['lover'],'MINISO Lover+');
        echo '<div><span class="eyebrow">'.esc_html(mct_ui_text('lover_label')).'</span><h2>'.esc_html(mct_design('lover_title')).'</h2><p>'.esc_html(mct_design('lover_text')).'</p><a class="button" href="'.esc_url(mct_url('lover')).'">'.esc_html(mct_design('lover_button')).'</a></div></div></section>';
    }
    if('stores'===$section && mct_design('show_stores')){
        echo '<section class="home-section stores-section full-bleed"><div class="section-inner stores-callout"><div>'.mct_icon('sucursales').'<h2>'.esc_html(mct_design('stores_title')).'</h2><p>'.esc_html(mct_design('stores_text')).'</p></div><a class="button dark-button" href="'.esc_url(mct_url('sucursales')).'">'.esc_html(mct_design('stores_button')).'</a></div></section>';
    }
}
function mct_home(){
    if(!mct_ready()){mct_dependency_notice();return;}
    mct_hero();
    $position=mct_design('video_position');
    if('after-hero'===$position)mct_instagram_video();
    foreach(explode('-',mct_design('home_order')) as $section){
        if('stores'===$section && 'before-stores'===$position)mct_instagram_video();
        mct_home_section($section);
    }
    if('end'===$position)mct_instagram_video();
}
function mct_social_links($cards=false) {
    foreach(array('instagram'=>'Instagram','facebook'=>'Facebook','tiktok'=>'TikTok') as $key=>$name){
        $url=mct_design($key.'_url');if(!$url)continue;
        echo '<a class="'.($cards?'social-card':'social-link').'" data-network="'.esc_attr($key).'" href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer" aria-label="'.esc_attr($name.' de MINISO Guatemala, abre en otra pestaña').'" style="'.esc_attr(mct_accent_style(mct_design($key.'_color'))).'">'.mct_icon($key).'<span>'.$name.'</span>'.($cards?'<small>'.esc_html(mct_ui_text('social_subtext')).'</small>':'').'</a>';
    }
}
function mct_social_section(){
    if(!mct_design('show_social'))return;
    if(!mct_design('instagram_url') && !mct_design('facebook_url') && !mct_design('tiktok_url'))return;
    echo '<section class="home-section social-section" aria-labelledby="social-heading"><div class="section-inner"><span class="eyebrow">'.esc_html(mct_ui_text('social_label')).'</span><h2 id="social-heading">'.esc_html(mct_design('social_title')).'</h2><p>'.esc_html(mct_design('social_text')).'</p><div class="social-grid">';mct_social_links(true);echo '</div></div></section>';
}

// EDITAR · Apariencia → Personalizar → MINISO · Diseño general → Video de Instagram.
function mct_instagram_video(){
    if(!function_exists('miniso_instagram_url') || !mct_design('show_video'))return;
    $url=miniso_instagram_url(mct_design('video_url'));
    if(!$url)return;
    echo '<section class="home-section video-section full-bleed" aria-labelledby="video-heading"><div class="section-inner video-layout"><div class="video-copy"><span class="eyebrow">'.esc_html(mct_ui_text('video_label')).'</span><h2 id="video-heading">'.esc_html(mct_design('video_title')).'</h2><p>'.esc_html(mct_design('video_text')).'</p><a class="video-account" href="'.esc_url(mct_design('instagram_url')).'" target="_blank" rel="noopener noreferrer">'.mct_icon('instagram').' '.esc_html(mct_ui_text('video_account')).'</a><p><a class="button dark-button" href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer">'.esc_html(mct_copy_text('video_button')).'</a></p><p class="video-help">'.esc_html(mct_copy_text('video_help')).'</p></div><div class="video-frame"><iframe title="Reel de MINISO Guatemala en Instagram" src="'.esc_url($url.'embed/').'" loading="lazy" allow="autoplay; encrypted-media; fullscreen; picture-in-picture" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div></div></section>';
}
