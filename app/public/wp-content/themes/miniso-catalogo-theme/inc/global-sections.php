<?php
/**
 * MINISO · Sección global editable. Bloque dinámico, ilimitado y sin contenido precargado.
 * No cambia los contenidos que ya guardó el personal en la base de datos.
 */
defined('ABSPATH') || exit;

add_action('init', function () {
    wp_register_script('miniso-global-sections-editor',
        get_template_directory_uri() . '/assets/global-sections-editor.js',
        array('wp-blocks','wp-element','wp-components','wp-block-editor','wp-i18n'),
        wp_get_theme()->get('Version'), true);
    register_block_type('miniso/seccion', array(
        'api_version' => 2,
        'editor_script' => 'miniso-global-sections-editor',
        'render_callback' => 'mct_global_render_section',
        'attributes' => array(
            'kind' => array('type'=>'string','default'=>'contenido'),
            'background' => array('type'=>'string','default'=>'rosa'),
            'customColor' => array('type'=>'string','default'=>''),
            'heading' => array('type'=>'string','default'=>''),
            'intro' => array('type'=>'string','default'=>''),
            'body' => array('type'=>'string','default'=>''),
            'items' => array('type'=>'array','default'=>array()),
            'columns' => array('type'=>'number','default'=>3),
            'position' => array('type'=>'string','default'=>'after'),
        ),
        'supports' => array('html'=>false,'anchor'=>true,'align'=>false,'multiple'=>true)
    ));
});

/** All fields may be empty. An empty section produces no frontend markup. */
function mct_global_render_section($attributes, $content = '', $block = null) {
    $kind = sanitize_key($attributes['kind'] ?? 'contenido');
    if (!in_array($kind,array('contenido','tarjetas','galeria','premios','videos','preguntas','destacado'),true)) $kind='contenido';
    $heading = trim((string)($attributes['heading'] ?? ''));
    $intro = trim((string)($attributes['intro'] ?? ''));
    $body = trim((string)($attributes['body'] ?? ''));
    $items = is_array($attributes['items'] ?? null) ? $attributes['items'] : array();
    $valid_items = array();
    foreach ($items as $item) {
        if (!is_array($item)) continue;
        $keys=array('title','text','quantity','url','linkText','videoUrl','imageId');
        $filled=false;
        foreach ($keys as $key) { if (isset($item[$key]) && trim((string)$item[$key]) !== '' && trim((string)$item[$key])!=='0') { $filled=true;break; } }
        if ($kind === 'preguntas' && trim((string)($item['title'] ?? '')) === '') $filled=false;
        if ($kind === 'videos' && trim((string)($item['videoUrl'] ?? '')) === '') $filled=false;
        if ($kind === 'galeria' && absint($item['imageId'] ?? 0) === 0) $filled=false;
        if($filled)$valid_items[]=$item;
    }
    if ($heading === '' && $intro === '' && $body === '' && !$valid_items) return '';
    $background = sanitize_key($attributes['background'] ?? 'rosa');
    $palettes = array('rosa'=>'#ffe1e8','amarillo'=>'#ffe17a','azul'=>'#cfefff','verde'=>'#cff4d8','lila'=>'#e8d9ff','blanco'=>'#ffffff','rojo'=>'#e60020');
    $color = $palettes[$background] ?? $palettes['blanco'];
    if ($background === 'propio' && !empty($attributes['customColor'])) $color = sanitize_hex_color($attributes['customColor']) ?: '#ffffff';
    $text_color = function_exists('mct_contrast') ? mct_contrast($color) : '#191919';
    $columns = min(4, max(1, (int)($attributes['columns']??3)));
    $id = is_object($block) && isset($block->parsed_block['attrs']['anchor']) ? sanitize_title($block->parsed_block['attrs']['anchor']) : '';
    ob_start(); ?>
    <section <?php if ($id): ?>id="<?php echo esc_attr($id); ?>"<?php endif; ?> class="miniso-global-section miniso-global-section--<?php echo esc_attr($kind); ?>" style="--mgs-bg:<?php echo esc_attr($color); ?>;--mgs-text:<?php echo esc_attr($text_color); ?>;--mgs-cols:<?php echo $columns; ?>">
      <div class="miniso-global-section__inner">
        <?php if($heading || $intro || $body): ?>
        <header class="miniso-global-section__copy">
          <?php if ($heading): ?><h2><?php echo esc_html($heading); ?></h2><?php endif; ?>
          <?php if ($intro): ?><p class="miniso-global-section__intro"><?php echo nl2br(esc_html($intro)); ?></p><?php endif; ?>
          <?php if ($body): ?><div class="miniso-global-section__body"><?php echo wpautop(esc_html($body)); ?></div><?php endif; ?>
        </header>
        <?php endif; ?>
        <?php if ($valid_items): ?><div class="miniso-global-section__items">
          <?php foreach($valid_items as $item):
            $title = trim((string)($item['title']??''));
            $text = trim((string)($item['text']??''));
            $number=trim((string)($item['quantity']??''));
            $url=esc_url($item['url']??'');
            $linktext=trim((string)($item['linkText']??''));
            $imageid=absint($item['imageId']??0);
            $video = trim((string)($item['videoUrl']??''));
            $video_rendered = mct_global_section_video($video);
          ?>
            <?php if($kind==='preguntas'): ?>
              <details class="miniso-global-section__question">
                <summary><?php echo esc_html($title); ?></summary>
                <?php if($text): ?><div class="miniso-global-section__answer"><?php echo wpautop(esc_html($text)); ?></div><?php endif; ?>
              </details>
            <?php else: ?>
              <article class="miniso-global-section__item">
                <?php if($imageid): ?><div class="miniso-global-section__image"><?php echo wp_get_attachment_image($imageid,'large',false,array('loading'=>'lazy','alt'=>$title)); ?></div><?php endif; ?>
                <?php if($video_rendered): ?><div class="miniso-global-section__video"><?php echo $video_rendered; // safely created by this function ?></div><?php endif; ?>
                <?php if($number): ?><strong class="miniso-global-section__number"><?php echo esc_html($number); ?></strong><?php endif; ?>
                <?php if($title): ?><h3><?php echo esc_html($title); ?></h3><?php endif; ?>
                <?php if($text): ?><div class="miniso-global-section__item-text"><?php echo wpautop(esc_html($text)); ?></div><?php endif; ?>
                <?php if($url && $linktext): ?><a class="miniso-global-section__link" href="<?php echo $url; ?>"><?php echo esc_html($linktext); ?></a><?php endif; ?>
              </article>
            <?php endif; ?>
          <?php endforeach; ?>
        </div><?php endif; ?>
      </div>
    </section>
    <?php return ob_get_clean();
}

/** Instagram, YouTube and public media URLs only; never echo arbitrary HTML from users. */
function mct_global_section_video($url) {
    $url=trim($url);
    if (!$url) return '';
    $parts=wp_parse_url($url);
    if (!is_array($parts) || strtolower($parts['scheme']??'')!=='https') return '';
    $host=strtolower($parts['host']??''); $path=$parts['path']??'';
    $src='';
    if (in_array($host,array('www.instagram.com','instagram.com'),true) && preg_match('~^/(reel|p|tv)/([\w-]+)/?$~',$path,$m)) {
        $src='https://www.instagram.com/'.$m[1].'/'.$m[2].'/embed/';
    } else if (in_array($host,array('youtube.com','www.youtube.com','youtu.be','m.youtube.com'),true)) {
        $id='';
        if($host==='youtu.be')$id=trim($path,'/');
        else if (preg_match('~^/(?:shorts|embed)/([\w-]{11})/?$~',$path,$m)) $id=$m[1];
        else {parse_str($parts['query']??'',$query);$id=is_string($query['v']??null)?$query['v']:'';}
        if(preg_match('/^[\w-]{11}$/',$id))$src='https://www.youtube-nocookie.com/embed/'.$id;
    } else if (in_array(strtolower(pathinfo($path,PATHINFO_EXTENSION)),array('mp4','webm'),true)) {
        $src=esc_url($url);
        return '<video controls playsinline preload="metadata" src="'.$src.'"></video>';
    }
    if (!$src) return '';
    return '<iframe src="'.esc_url($src).'" loading="lazy" title="Video" allow="autoplay; encrypted-media; fullscreen; picture-in-picture" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe><a href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer" class="miniso-global-section__external">Abrir video</a>';
}

/** Hide only the exact obsolete Canada import menu item, if provided by an external plugin. */
add_action('admin_menu', function () {
    global $menu,$submenu;
    $label_is_old = static function($label) {
        $txt=trim(wp_strip_all_tags((string)$label));
        $txt=strtolower(remove_accents($txt));
        return (str_contains($txt,'canada') && (str_contains($txt,'catalogo') || str_contains($txt,'importar')));
    };
    foreach((array)$submenu as $parent=>$entries) {
        foreach((array)$entries as $index=>$entry) {
            if(!empty($entry[0]) && $label_is_old($entry[0])) unset($submenu[$parent][$index]);
        }
    }
    foreach((array)$menu as $index=>$entry) {
        if(!empty($entry[0]) && $label_is_old($entry[0])) unset($menu[$index]);
    }
}, 9999);

/** Render only these explicit MINISO blocks, never inject arbitrary page text into auto-generated headers. */
function mct_global_page_blocks($page_id, $position='after') {
    if (!$page_id) return '';
    $content=get_post_field('post_content',absint($page_id));
    if (!$content || !function_exists('parse_blocks')) return '';
    $blocks=parse_blocks($content);
    $html='';
    $walk=function($nodes) use (&$walk, &$html, $position) {
        foreach ((array)$nodes as $node) {
            if (in_array(($node['blockName']??''),array('miniso/seccion','miniso/modulo'),true)) {
                $where=$node['attrs']['position']??'after';
                if($where===$position)$html.=render_block($node);
            } else if(!empty($node['innerBlocks'])) $walk($node['innerBlocks']);
        }
    };
    $walk($blocks);
    return $html;
}
