<?php
/**
 * Plugin Name: MINISO · Ayuda por Categorías
 * Description: Filtros de categorías para los desplegables existentes de Ayuda. La asignación se gestiona desde el editor sin HTML ni CSS.
 * Version: 1.0.0
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Text Domain: miniso-ayuda-categorias
 */
if (!defined('ABSPATH')) exit;

function mac_is_help_page($post) {
    return $post instanceof WP_Post && ($post->post_name === 'ayuda' || sanitize_title($post->post_title) === 'ayuda');
}

function mac_topics() {
    $default = array(
        'compras' => 'Compras y productos',
        'promociones' => 'Promociones y descuentos',
        'sucursales' => 'Sucursales y disponibilidad',
        'lover' => 'Lover+ y atención',
    );
    $stored = get_option('mac_help_topics');
    if (!is_array($stored) || !$stored) return $default;
    $result = array();
    foreach ($stored as $key => $label) {
        $key = sanitize_key($key);
        $label = sanitize_text_field($label);
        if ($key && $label) $result[$key] = $label;
    }
    return $result ?: $default;
}

function mac_normalize($txt) {
    $txt = html_entity_decode(wp_strip_all_tags((string) $txt), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $txt = preg_replace('/\s+/u', ' ', trim($txt));
    return trim($txt);
}

// Recorre HTML clásico, bloques Details de Gutenberg y las secciones MINISO de preguntas.
function mac_collect_questions($post) {
    $found = array();
    $add = static function ($text) use (&$found) {
        $text = mac_normalize($text);
        if ($text !== '' && !in_array($text, $found, true)) $found[] = $text;
    };
    $walk = static function ($blocks) use (&$walk, $add) {
        foreach ($blocks as $block) {
            if (($block['blockName'] ?? '') === 'miniso/seccion' && ($block['attrs']['kind'] ?? '') === 'preguntas') {
                foreach (($block['attrs']['items'] ?? array()) as $item) {
                    if (is_array($item)) $add($item['title'] ?? '');
                }
            }
            // Se buscan summaries en bloques serializados; incluye HTML personalizado y Details.
            foreach (($block['innerContent'] ?? array()) as $piece) {
                if (is_string($piece) && preg_match_all('~<summary\b[^>]*>(.*?)</summary>~isu', $piece, $matches)) {
                    foreach ($matches[1] as $summary) $add($summary);
                }
            }
            if (!empty($block['innerBlocks'])) $walk($block['innerBlocks']);
        }
    };
    $walk(parse_blocks((string) $post->post_content));
    // Respaldo para contenido clásico no estructurado.
    if (!$found && preg_match_all('~<summary\b[^>]*>(.*?)</summary>~isu', (string) $post->post_content, $m)) {
        foreach ($m[1] as $summary) $add($summary);
    }
    return $found;
}

add_action('add_meta_boxes_page', static function ($post) {
    if (!mac_is_help_page($post)) return;
    add_meta_box('mac-topic-mapping', 'MINISO · Categorías de preguntas', 'mac_render_mapping_box', 'page', 'normal', 'high');
});

function mac_render_mapping_box($post) {
    wp_nonce_field('mac_help_topics_save', 'mac_help_topics_nonce');
    $questions = mac_collect_questions($post);
    $mapping = get_post_meta($post->ID, '_mac_question_topics', true);
    $mapping = is_array($mapping) ? $mapping : array();
    $topics = mac_topics();
    echo '<p>Asigna a cada pregunta el apartado donde aparecerá. Puedes seguir editando el texto y las respuestas con los bloques habituales de WordPress.</p>';
    echo '<p><strong>Importante:</strong> al añadir preguntas nuevas, primero pulsa Actualizar; después vuelve aquí para clasificarlas.</p>';
    if (!$questions) {
        echo '<p><em>No se detectaron preguntas con el bloque «Detalles» o con «MINISO · Sección personalizable → Preguntas» en esta página. Si usas preguntas dentro de un editor externo, podrás necesitar convertirlas a bloques de WordPress.</em></p>';
        return;
    }
    echo '<table class="widefat striped"><thead><tr><th style="width:60%">Pregunta existente</th><th>Categoría</th></tr></thead><tbody>';
    foreach ($questions as $i => $question) {
        $key = md5($question);
        $selected = isset($mapping[$key]) ? $mapping[$key] : '';
        echo '<tr><td>' . esc_html($question) . '</td><td><select name="mac_question_topics[' . esc_attr($key) . ']" aria-label="Categoría de ' . esc_attr($question) . '">';
        echo '<option value="">Sin categoría (aparece solo en Todas)</option>';
        foreach ($topics as $topic_id => $label) {
            echo '<option value="' . esc_attr($topic_id) . '" ' . selected($selected, $topic_id, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select></td></tr>';
    }
    echo '</tbody></table>';
}

add_action('save_post_page', static function ($post_id, $post, $update) {
    if (!mac_is_help_page($post)) return;
    if (!isset($_POST['mac_help_topics_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mac_help_topics_nonce'])), 'mac_help_topics_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) return;
    $topics = mac_topics();
    $valid = array();
    foreach (mac_collect_questions($post) as $question) $valid[md5($question)] = true;
    $raw = isset($_POST['mac_question_topics']) && is_array($_POST['mac_question_topics']) ? wp_unslash($_POST['mac_question_topics']) : array();
    $mapping = array();
    foreach ($raw as $key => $topic) {
        $key = sanitize_text_field($key);
        $topic = sanitize_key($topic);
        if (isset($valid[$key]) && isset($topics[$topic])) $mapping[$key] = $topic;
    }
    update_post_meta($post_id, '_mac_question_topics', $mapping);
}, 10, 3);

add_action('admin_menu', static function () {
    add_options_page('Categorías de Ayuda MINISO', 'MINISO · Categorías de Ayuda', 'manage_options', 'miniso-ayuda-categorias', 'mac_settings_page');
});

function mac_settings_page() {
    if (!current_user_can('manage_options')) return;
    if (isset($_POST['mac_save_categories'])) {
        check_admin_referer('mac_topics_settings');
        $existing = mac_topics();
        $new = array();
        $labels = isset($_POST['mac_topic_labels']) && is_array($_POST['mac_topic_labels']) ? wp_unslash($_POST['mac_topic_labels']) : array();
        foreach ($labels as $key => $label) {
            $key = sanitize_key($key);
            $label = sanitize_text_field($label);
            if (isset($existing[$key]) && $label !== '') $new[$key] = $label;
        }
        $extras = isset($_POST['mac_new_topics']) ? (string) wp_unslash($_POST['mac_new_topics']) : '';
        foreach (preg_split('/\R/u', $extras) as $label) {
            $label = sanitize_text_field(trim($label));
            $key = sanitize_title($label);
            if ($key && $label && !isset($new[$key])) $new[$key] = $label;
        }
        if ($new) update_option('mac_help_topics', $new);
        echo '<div class="notice notice-success"><p>Categorías guardadas.</p></div>';
    }
    echo '<div class="wrap"><h1>Categorías de Ayuda MINISO</h1><p>Edita los nombres de las categorías. Las preguntas se asignan desde <strong>Páginas → Ayuda → Editar</strong>.</p><form method="post">';
    wp_nonce_field('mac_topics_settings');
    echo '<table class="form-table"><tbody>';
    foreach (mac_topics() as $key => $label) {
        echo '<tr><th scope="row">' . esc_html($key) . '</th><td><input class="regular-text" name="mac_topic_labels[' . esc_attr($key) . ']" value="' . esc_attr($label) . '" type="text"></td></tr>';
    }
    echo '<tr><th>Nuevas categorías</th><td><textarea name="mac_new_topics" rows="3" class="large-text" placeholder="Una categoría nueva por línea"></textarea></td></tr>';
    echo '</tbody></table><p><input type="submit" name="mac_save_categories" class="button button-primary" value="Guardar categorías"></p></form></div>';
}

add_action('wp_enqueue_scripts', static function () {
    if (!is_page() || !mac_is_help_page(get_queried_object())) return;
    $page = get_queried_object();
    $questions = mac_collect_questions($page);
    if (!$questions) return;
    $mapping = get_post_meta($page->ID, '_mac_question_topics', true);
    $mapping = is_array($mapping) ? $mapping : array();
    $assigned = array();
    foreach ($questions as $question) {
        $assigned[] = array('question' => $question, 'topic' => $mapping[md5($question)] ?? '');
    }
    wp_enqueue_style('mac-help-tabs', plugins_url('assets/help-tabs.css', __FILE__), array(), '1.0.0');
    wp_enqueue_script('mac-help-tabs', plugins_url('assets/help-tabs.js', __FILE__), array(), '1.0.0', true);
    wp_add_inline_script('mac-help-tabs', 'window.MINISO_HELP_TOPICS = ' . wp_json_encode(array('categories' => mac_topics(), 'questions' => $assigned), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';', 'before');
});
