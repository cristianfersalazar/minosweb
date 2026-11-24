<?php
/**
 * MINISO Catálogo 2.6 · Sección de oportunidades profesionales nativa del tema.
 * Se edita desde Apariencia > Personalizar > MINISO · Diseño general > Trabaja con nosotros.
 * No consulta LinkedIn ni anuncia vacantes: solo enlaza al canal oficial.
 */
defined('ABSPATH') || exit;

function mct_careers_defaults() {
    return array(
        'enabled'       => true,
        'footer_link'   => true,
        'eyebrow'       => 'CRECE CON MINISO',
        'title'         => '¡Forma parte del equipo MINISO!',
        'description'   => 'Detrás de cada tienda hay personas que hacen posible la experiencia MINISO. Si te entusiasman el trabajo en equipo, el aprendizaje y el mundo del retail, conoce nuestra comunidad profesional.',
        'tags'          => 'Trabajo en equipo, Aprendizaje, Nuevas oportunidades',
        'button'        => 'Descubre oportunidades en LinkedIn',
        'url'           => 'https://www.linkedin.com/company/miniso-guatemala',
        'note'          => 'Consulta los anuncios y vacantes disponibles en nuestros canales oficiales. Su publicación puede variar.',
        'panel_title'   => 'Tu talento puede crecer aquí',
        'panel_text'    => 'Conoce a quienes hacen posible la experiencia MINISO Guatemala.',
        'background'    => '#fff0f2',
        'accent'        => '#e60023',
        'text_color'    => '#171717',
        'image_id'      => 0,
    );
}
function mct_careers_value($name) {
    $defaults = mct_careers_defaults();
    if (!array_key_exists($name, $defaults)) return '';
    return get_theme_mod('mct_careers_' . $name, $defaults[$name]);
}
function mct_careers_sanitize_https_url($value) {
    $url = esc_url_raw(trim((string) $value), array('https'));
    return $url && 'https' === wp_parse_url($url, PHP_URL_SCHEME)
        ? $url
        : mct_careers_defaults()['url'];
}

add_action('customize_register', function($customizer) {
    $section_args = array(
        'title' => 'Trabaja con nosotros · LinkedIn',
        'description' => 'Bloque integrado al tema. Se muestra en Inicio, antes de las redes sociales. No requiere plugin ni formularios de empleo.',
        'priority' => 62,
    );
    if ($customizer->get_panel('miniso_design_panel')) {
        $section_args['panel'] = 'miniso_design_panel';
    }
    $customizer->add_section('mct_careers_section', $section_args);
    $defaults = mct_careers_defaults();
    $fields = array(
        'enabled'      => array('Mostrar sección en Inicio', 'checkbox', 'rest_sanitize_boolean'),
        'footer_link'  => array('Mostrar enlace «Trabaja con nosotros» en el pie', 'checkbox', 'rest_sanitize_boolean'),
        'eyebrow'      => array('Etiqueta superior', 'text', 'sanitize_text_field'),
        'title'        => array('Título principal', 'text', 'sanitize_text_field'),
        'description'  => array('Descripción', 'textarea', 'sanitize_textarea_field'),
        'tags'         => array('Etiquetas (hasta 3, separadas por coma)', 'text', 'sanitize_text_field'),
        'button'       => array('Texto del botón', 'text', 'sanitize_text_field'),
        'url'          => array('Enlace de LinkedIn o portal oficial de empleo (https)', 'url', 'mct_careers_sanitize_https_url'),
        'note'         => array('Nota debajo del botón', 'textarea', 'sanitize_textarea_field'),
        'panel_title'  => array('Frase de la tarjeta roja', 'text', 'sanitize_text_field'),
        'panel_text'   => array('Descripción de la tarjeta roja', 'textarea', 'sanitize_textarea_field'),
        'background'   => array('Fondo de la sección', 'color', 'sanitize_hex_color'),
        'accent'       => array('Color destacado', 'color', 'sanitize_hex_color'),
        'text_color'   => array('Color del texto principal', 'color', 'sanitize_hex_color'),
        'image_id'     => array('Imagen oficial del equipo (opcional)', 'media', 'absint'),
    );
    foreach ($fields as $key => $field) {
        $setting = 'mct_careers_' . $key;
        $customizer->add_setting($setting, array(
            'default' => $defaults[$key],
            'type' => 'theme_mod',
            'sanitize_callback' => $field[2],
            'capability' => 'edit_theme_options',
            'transport' => 'refresh',
        ));
        $control_args = array('label' => $field[0], 'section' => 'mct_careers_section');
        if ('color' === $field[1]) {
            $customizer->add_control(new WP_Customize_Color_Control($customizer, $setting, $control_args));
        } elseif ('media' === $field[1]) {
            $control_args['mime_type'] = 'image';
            $customizer->add_control(new WP_Customize_Media_Control($customizer, $setting, $control_args));
        } else {
            $control_args['type'] = $field[1];
            $customizer->add_control($setting, $control_args);
        }
    }
}, 120);

add_action('wp_enqueue_scripts', function() {
    if (is_admin()) return;
    wp_enqueue_style('miniso-careers', get_template_directory_uri() . '/assets/careers.css', array('miniso-catalogo'), wp_get_theme()->get('Version'));
}, 32);

/** Permite usar el bloque en una página editorial con [miniso_equipo]. */
function mct_careers_render() {
    $GLOBALS['mct_careers_rendered'] = true;
    $tags = array_slice(array_filter(array_map('trim', explode(',', (string) mct_careers_value('tags')))), 0, 3);
    $url  = mct_careers_sanitize_https_url(mct_careers_value('url'));
    $background = sanitize_hex_color(mct_careers_value('background')) ?: '#fff0f2';
    $accent = sanitize_hex_color(mct_careers_value('accent')) ?: '#e60023';
    $ink = sanitize_hex_color(mct_careers_value('text_color')) ?: '#171717';
    $image_id = absint(mct_careers_value('image_id'));
    $image = $image_id ? wp_get_attachment_image($image_id, 'large', false, array(
        'alt' => 'Equipo MINISO Guatemala',
        'loading' => 'lazy',
        'decoding' => 'async',
        'class' => 'mct-careers-photo',
    )) : '';
    ob_start();
    ?>
    <section id="trabaja-con-nosotros" class="home-section mct-careers" aria-labelledby="mct-careers-title" style="--mct-careers-bg:<?php echo esc_attr($background); ?>;--mct-careers-accent:<?php echo esc_attr($accent); ?>;--mct-careers-ink:<?php echo esc_attr($ink); ?>;">
        <div class="section-inner mct-careers-layout">
            <div class="mct-careers-copy">
                <span class="mct-careers-eyebrow"><span class="mct-careers-eyebrow-dot" aria-hidden="true"></span><?php echo esc_html(mct_careers_value('eyebrow')); ?></span>
                <h2 id="mct-careers-title"><?php echo esc_html(mct_careers_value('title')); ?></h2>
                <p class="mct-careers-description"><?php echo nl2br(esc_html(mct_careers_value('description'))); ?></p>
                <?php if ($tags): ?>
                <ul class="mct-careers-tags" aria-label="Nuestra cultura de trabajo">
                    <?php foreach($tags as $tag): ?><li><?php echo esc_html($tag); ?></li><?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <a class="mct-careers-button" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr(mct_careers_value('button') . ', abre el sitio externo en otra pestaña'); ?>">
                    <span class="mct-careers-in" aria-hidden="true">in</span>
                    <span><?php echo esc_html(mct_careers_value('button')); ?></span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
                </a>
                <?php if (mct_careers_value('note')): ?><p class="mct-careers-note"><?php echo esc_html(mct_careers_value('note')); ?></p><?php endif; ?>
            </div>
            <div class="mct-careers-visual<?php echo $image ? ' mct-careers-has-image' : ''; ?>">
                <?php if ($image): ?>
                    <?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() produce HTML fiable. ?>
                    <div class="mct-careers-image-caption"><strong><?php echo esc_html(mct_careers_value('panel_title')); ?></strong><span><?php echo esc_html(mct_careers_value('panel_text')); ?></span></div>
                <?php else: ?>
                    <div class="mct-careers-visual-symbol" aria-hidden="true"><span>MINISO</span><span>Life is for fun</span></div>
                    <div class="mct-careers-visual-copy">
                        <strong><?php echo esc_html(mct_careers_value('panel_title')); ?></strong>
                        <p><?php echo esc_html(mct_careers_value('panel_text')); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
function mct_careers_is_home_context() {
    return is_front_page() || is_page_template('templates/inicio.php') || is_page_template('templates/inicio-flexible.php');
}
function mct_careers_auto_section() {
    if (!mct_careers_value('enabled') || !mct_careers_is_home_context() || !empty($GLOBALS['mct_careers_rendered'])) return '';
    return mct_careers_render();
}
function mct_careers_footer_link() {
    if (!mct_careers_value('enabled') || !mct_careers_value('footer_link')) return '';
    $href = mct_careers_is_home_context() ? '#trabaja-con-nosotros' : home_url('/#trabaja-con-nosotros');
    return '<a class="mct-careers-footer-link" href="' . esc_url($href) . '">'.esc_html(mct_copy_text('careers_footer_label')).'</a>';
}
add_shortcode('miniso_equipo', 'mct_careers_render');
