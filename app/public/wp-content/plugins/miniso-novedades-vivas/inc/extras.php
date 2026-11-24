<?php
/**
 * Premios y videos reutilizables para cualquier novedad MINISO.
 * Se muestran únicamente cuando el personal guarda contenido explícito.
 */
defined('ABSPATH') || exit;

/** Devuelve información de premios solo si existe contenido. */
function mnv_news_prizes($post) {
    $post = get_post($post);
    if (!$post || get_post_meta($post->ID, 'mnv_prizes_disabled', true) === '1') return array();
    $raw = (string) get_post_meta($post->ID, 'mnv_prizes_items', true);
    $items = array();
    foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
        $line = trim($line);
        if (!$line) continue;
        $parts = array_map('trim', explode('|', $line, 2));
        if (count($parts) === 2 && $parts[0] !== '' && $parts[1] !== '') {
            $amount = sanitize_text_field($parts[0]);
            $description = sanitize_text_field($parts[1]);
        } else {
            $amount = '';
            $description = sanitize_text_field($line);
        }
        if ($description !== '') $items[] = array('amount' => $amount, 'description' => $description);
        if (count($items) >= 60) break;
    }
    if (!$items) return array();
    $heading = trim((string) get_post_meta($post->ID, 'mnv_prizes_heading', true));
    $intro = trim((string) get_post_meta($post->ID, 'mnv_prizes_intro', true));
    return array(
        'heading' => $heading,
        'intro' => $intro,
        'items' => $items,
    );
}

/** Valida medios de Instagram, YouTube o video MP4/WebM por HTTPS. */
function mnv_news_video_from_url($url) {
    $url = trim((string) $url);
    $parts = wp_parse_url($url);
    if (!is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) return array();
    $host = strtolower($parts['host']);
    $path = $parts['path'] ?? '/';
    if (in_array($host, array('www.instagram.com','instagram.com'), true)) {
        $normalized = mnv_news_validate_instagram_url($url);
        if (!$normalized) return array();
        return array('kind' => 'instagram', 'url' => $normalized, 'embed' => trailingslashit($normalized) . 'embed/', 'provider' => 'Instagram');
    }
    $youtube_id = '';
    if (in_array($host, array('youtube.com','www.youtube.com','m.youtube.com'), true)) {
        if ($path === '/watch') {
            parse_str($parts['query'] ?? '', $query);
            if (isset($query['v']) && is_string($query['v'])) $youtube_id = $query['v'];
        } elseif (preg_match('~^/(?:shorts|embed)/([a-zA-Z0-9_-]{11})/?$~', $path, $matches)) {
            $youtube_id = $matches[1];
        }
    } elseif ($host === 'youtu.be') {
        $youtube_id = trim($path, '/');
    }
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $youtube_id)) {
        return array('kind' => 'youtube', 'url' => 'https://www.youtube.com/watch?v=' . $youtube_id,
            'embed' => 'https://www.youtube-nocookie.com/embed/' . $youtube_id, 'provider' => 'YouTube');
    }
    // Archivos desde la biblioteca de medios o URLs HTTPS directas a un archivo de video.
    if (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), array('mp4','webm'), true)) {
        return array('kind' => 'file', 'url' => esc_url_raw($url, array('https')), 'embed' => '', 'provider' => 'video');
    }
    return array();
}

function mnv_news_videos($post) {
    $post = get_post($post);
    if (!$post || get_post_meta($post->ID, 'mct_news_disable_instagram', true) === '1') return array();
    $items = array();
    $seen = array();
    $main = mnv_news_instagram_url($post);
    if ($main) {
        $video = mnv_news_video_from_url($main);
        if ($video) { $video['title'] = 'Reel de Instagram'; $items[] = $video; $seen[$video['url']] = true; }
    }
    $raw = (string) get_post_meta($post->ID, 'mnv_videos_additional', true);
    foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
        $line = trim($line);
        if (!$line) continue;
        $pieces = array_map('trim', explode('|', $line, 2));
        $video = mnv_news_video_from_url(count($pieces) > 1 ? $pieces[1] : $pieces[0]);
        if (!$video || isset($seen[$video['url']])) continue;
        $video['title'] = count($pieces) > 1 && $pieces[0] !== '' ? sanitize_text_field($pieces[0]) : 'Video de ' . $video['provider'];
        $items[] = $video;
        $seen[$video['url']] = true;
        if (count($items) >= 30) break; // Lista flexible sin textos automáticos.
    }
    return $items;
}

function mnv_news_videos_text($post) {
    $post = get_post($post);
    if (!$post) return array('heading' => '', 'intro' => '');
    $heading = trim((string) get_post_meta($post->ID, 'mnv_videos_heading', true));
    $intro = trim((string) get_post_meta($post->ID, 'mnv_videos_intro', true));
    return array(
        'heading' => $heading,
        'intro' => $intro,
    );
}

function mnv_news_extras_meta_box($post) {
    wp_nonce_field('mnv_news_extras_save', 'mnv_news_extras_nonce');
    $prizes = mnv_news_prizes($post);
    $prize_lines = (string) get_post_meta($post->ID, 'mnv_prizes_items', true);
    $heading = (string) get_post_meta($post->ID, 'mnv_prizes_heading', true);
    $intro = (string) get_post_meta($post->ID, 'mnv_prizes_intro', true);
    $extra_urls = (string) get_post_meta($post->ID, 'mnv_videos_additional', true);
    $video_heading = (string) get_post_meta($post->ID, 'mnv_videos_heading', true);
    $video_intro = (string) get_post_meta($post->ID, 'mnv_videos_intro', true);
    echo '<div style="padding:2px 0 8px">';
    echo '<h3 style="margin-bottom:6px">🎁 Sección de premios</h3>';
    echo '<p class="description">Disponible para todas las publicaciones. Si no ingresas premios, esta sección no aparece.</p>';
    echo '<p><label for="mnv-prizes-heading"><strong>Título de la sección</strong></label><br><input id="mnv-prizes-heading" name="mnv_prizes_heading" type="text" style="width:100%" value="' . esc_attr($heading) . '"></p>';
    echo '<p><label for="mnv-prizes-intro"><strong>Texto de introducción</strong></label><br><textarea id="mnv-prizes-intro" rows="3" name="mnv_prizes_intro" style="width:100%">' . esc_textarea($intro) . '</textarea></p>';
    echo '<p><label for="mnv-prizes-items"><strong>Premios (uno por línea)</strong></label><br><textarea id="mnv-prizes-items" rows="5" name="mnv_prizes_items" style="width:100%">' . esc_textarea($prize_lines) . '</textarea><br><small>Formato: <code>cantidad | descripción</code>. También puedes escribir solo una descripción. Hasta 60 tarjetas.</small></p>';
    echo '<p><label><input name="mnv_prizes_disabled" type="checkbox" value="1" ' . checked(get_post_meta($post->ID,'mnv_prizes_disabled',true), '1', false) . '> Ocultar premios en esta noticia</label></p>';
    echo '<hr style="margin:20px 0">';
    echo '<h3 style="margin-bottom:6px">🎬 Sección de videos</h3>';
    echo '<p class="description">El primer Reel de Instagram se administra en «MINISO · Detalles y video de la noticia». Aquí puedes agregar más videos, sin modificar el Reel anterior.</p>';
    echo '<p><label for="mnv-videos-heading"><strong>Título de la sección</strong></label><br><input id="mnv-videos-heading" name="mnv_videos_heading" type="text" style="width:100%" value="' . esc_attr($video_heading) . '"></p>';
    echo '<p><label for="mnv-videos-intro"><strong>Texto de introducción</strong></label><br><textarea id="mnv-videos-intro" name="mnv_videos_intro" rows="3" style="width:100%">' . esc_textarea($video_intro) . '</textarea></p>';
    echo '<p><label for="mnv-videos-additional"><strong>Videos adicionales (uno por línea)</strong></label><br><textarea id="mnv-videos-additional" name="mnv_videos_additional" rows="5" style="width:100%">' . esc_textarea($extra_urls) . '</textarea><br><small>Puedes usar <code>título | URL</code> o solo la URL. Se aceptan Reels/publicaciones de Instagram, YouTube y enlaces directos a MP4/WebM. Hasta 8 videos adicionales. Los videos se muestran dentro de la noticia cuando el proveedor lo permite.</small></p>';
    echo '<p class="description">Si dejas los campos de premios o videos vacíos, sus secciones no aparecerán. Para ocultar todos los videos, usa la casilla «No mostrar video en esta noticia» del apartado anterior.</p>';
    echo '</div>';
}

add_action('add_meta_boxes_miniso_novedad', function () {
    add_meta_box('mnv-news-extras', 'MINISO · Premios y videos', 'mnv_news_extras_meta_box', 'miniso_novedad', 'normal', 'default');
}, 20);

add_action('save_post_miniso_novedad', function ($post_id) {
    if (!isset($_POST['mnv_news_extras_nonce']) || !is_scalar($_POST['mnv_news_extras_nonce'])) return;
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mnv_news_extras_nonce'])), 'mnv_news_extras_save')) return;
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) return;
    $fields = array(
        'mnv_prizes_heading' => 'text',
        'mnv_prizes_intro' => 'textarea',
        'mnv_prizes_items' => 'textarea',
        'mnv_videos_heading' => 'text',
        'mnv_videos_intro' => 'textarea',
        'mnv_videos_additional' => 'textarea',
    );
    foreach ($fields as $key => $kind) {
        if (!isset($_POST[$key]) || !is_scalar($_POST[$key])) continue;
        $raw = wp_unslash($_POST[$key]);
        $value = $kind === 'textarea' ? sanitize_textarea_field($raw) : sanitize_text_field($raw);
        if ($value !== '') update_post_meta($post_id, $key, $value);
        else delete_post_meta($post_id, $key);
    }
    if (!empty($_POST['mnv_prizes_disabled'])) update_post_meta($post_id,'mnv_prizes_disabled','1');
    else delete_post_meta($post_id,'mnv_prizes_disabled');
}, 20);
