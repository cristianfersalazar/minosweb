<?php
/**
 * MINISO | Datos editoriales adicionales para el tipo de contenido existente.
 * No registra de nuevo miniso_novedad: ese tipo lo administra el complemento.
 */
defined( 'ABSPATH' ) || exit;

function mnv_news_types() {
    return array(
        'apertura'    => 'Apertura',
        'preventa'    => 'Preventa',
        'lanzamiento' => 'Lanzamiento',
        'promocion'   => 'Promoción',
        'evento'      => 'Evento',
        'novedad'     => 'Novedad',
    );
}

/** Clasificación opcional con respaldo para las noticias ya publicadas. */
function mnv_news_type( $post ) {
    $post = get_post( $post );
    if ( ! $post ) return 'novedad';
    $saved = get_post_meta( $post->ID, 'mct_news_type', true );
    if ( isset( mnv_news_types()[ $saved ] ) ) return $saved;
    $label = strtolower( remove_accents( (string) get_post_meta( $post->ID, 'miniso_news_label', true ) ) );
    // No deducir eventos desde el título; el personal selecciona su categoría.
    foreach ( array(
        'apertura'    => array( 'apertura', 'inaugura', 'nueva tienda' ),
        'preventa'    => array( 'preventa', 'pre-lanzamiento' ),
        'lanzamiento' => array( 'lanzamiento', 'coleccion', 'nuevo ingreso' ),
        'promocion'   => array( 'promocion', 'oferta', 'descuento' ),
        'evento'      => array( 'evento', 'actividad', 'pop-up' ),
    ) as $key => $words ) {
        foreach ( $words as $word ) {
            if ( false !== strpos( $label, $word ) ) return $key;
        }
    }
    return 'novedad';
}

function mnv_news_type_label( $post ) {
    $types = mnv_news_types();
    $type = mnv_news_type( $post );
    return $types[ $type ] ?? 'Novedad';
}

function mnv_news_event_date( $post ) {
    $post = get_post( $post );
    if ( ! $post ) return '';
    $value = (string) get_post_meta( $post->ID, 'mct_news_event_date', true );
    return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
}

function mnv_news_date_display( $post ) {
    $post = get_post( $post );
    if ( ! $post ) return '';
    $event_date = mnv_news_event_date( $post );
    return $event_date
        ? wp_date( 'j \d\e F \d\e Y', strtotime( $event_date . ' 12:00:00' ) )
        : get_the_date( 'j \d\e F \d\e Y', $post );
}

function mnv_news_location( $post ) {
    $post = get_post( $post );
    if ( ! $post ) return '';
    $saved = (string) get_post_meta( $post->ID, 'mct_news_location', true );
    if ($saved) return $saved;
    return '';
}

/** Enlace a ubicación opcional, siempre establecido por el personal. */
function mnv_news_location_url( $post ) {
    $post = get_post( $post );
    if ( ! $post ) return '';
    $saved = trim( (string) get_post_meta( $post->ID, 'mct_news_location_url', true ) );
    if ( $saved ) return esc_url_raw( $saved, array( 'https', 'http' ) );
    return '';
}

/** URL de Instagram validada, exclusivamente guardada por el administrador. */
function mnv_news_instagram_url( $post ) {
    $post = get_post( $post );
    if ( ! $post || get_post_meta( $post->ID, 'mct_news_disable_instagram', true ) ) return '';
    $saved = (string) get_post_meta( $post->ID, 'mct_news_instagram_url', true );
    return mnv_news_validate_instagram_url( $saved );
}

function mnv_news_validate_instagram_url( $url ) {
    $url = trim( (string) $url );
    if ( ! $url ) return '';
    $parts = wp_parse_url( $url );
    if ( ! is_array( $parts ) || ! isset( $parts['host'], $parts['path'] ) ) return '';
    if ( ! in_array( strtolower( $parts['host'] ), array( 'instagram.com', 'www.instagram.com' ), true ) ) return '';
    if ( ! preg_match( '~^/(reel|p|tv)/[a-zA-Z0-9_-]+/?$~', $parts['path'] ) ) return '';
    return 'https://www.instagram.com' . trailingslashit( $parts['path'] );
}

function mnv_news_excerpt( $post, $words = 25 ) {
    $post = get_post( $post );
    if ( ! $post ) return '';
    $text = $post->post_excerpt ?: $post->post_content;
    return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $text ) ), $words, '…' );
}

/** Los filtros también incluyen las entradas anteriores que no tienen clasificación manual. */
function mnv_news_filtered_posts( $type = '' ) {
    $items = get_posts( array(
        'post_type' => 'miniso_novedad',
        'post_status' => 'publish',
        'has_password' => false,
        'numberposts' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'suppress_filters' => false,
    ) );
    if ( ! $type || ! isset( mnv_news_types()[ $type ] ) ) return $items;
    return array_values( array_filter( $items, static function ( $post ) use ( $type ) {
        return mnv_news_type( $post ) === $type;
    } ) );
}

function mnv_news_meta_box( $post ) {
    wp_nonce_field( 'mct_news_meta_save', 'mct_news_meta_nonce' );
    $type = (string) get_post_meta( $post->ID, 'mct_news_type', true );
    $date = mnv_news_event_date( $post );
    $location = mnv_news_location( $post );
    $location_url = mnv_news_location_url( $post );
    $reel = mnv_news_instagram_url( $post );
    $disable = get_post_meta( $post->ID, 'mct_news_disable_instagram', true );
    $collection = (string) get_post_meta( $post->ID, 'mct_news_collection_url', true );
    echo '<p>Estos datos aparecen en el encabezado de la noticia. Si no indicas fecha de evento, se muestra la fecha de publicación.</p>';
    echo '<p><label for="mct-news-type"><strong>Tipo de novedad</strong></label><br><select id="mct-news-type" name="mct_news_type" style="width:100%"><option value="">Sin seleccionar</option>';
    foreach ( mnv_news_types() as $key => $label ) {
        echo '<option value="' . esc_attr( $key ) . '" ' . selected( $type, $key, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select></p>';
    echo '<p><label for="mct-news-date"><strong>Fecha del evento (opcional)</strong></label><br><input id="mct-news-date" name="mct_news_event_date" type="date" value="' . esc_attr( $date ) . '" style="width:100%"></p>';
    echo '<p><label for="mct-news-location"><strong>Sucursal o ubicación (opcional)</strong></label><br><input id="mct-news-location" name="mct_news_location" type="text" value="' . esc_attr( $location ) . '" style="width:100%"></p>';
    echo '<p><label for="mct-news-map"><strong>Enlace de la ubicación (Google Maps)</strong></label><br><input id="mct-news-map" name="mct_news_location_url" type="url" value="' . esc_attr( $location_url ) . '" style="width:100%"></p>';
    echo '<p><label for="mct-news-reel"><strong>Enlace al Reel de Instagram</strong></label><br><input id="mct-news-reel" name="mct_news_instagram_url" type="url" value="' . esc_attr( $reel ) . '" style="width:100%"></p>';
    echo '<p><label><input type="checkbox" name="mct_news_disable_instagram" value="1" ' . checked( $disable, '1', false ) . '> No mostrar video en esta noticia</label></p>';
    echo '<p><label for="mct-news-collection"><strong>Enlace a la colección (opcional)</strong></label><br><input id="mct-news-collection" name="mct_news_collection_url" type="url" value="' . esc_attr( $collection ) . '" style="width:100%"></p>';
    echo '<p><label for="mnv-news-info-heading"><strong>Título del apartado de información (opcional)</strong></label><br><input class="widefat" id="mnv-news-info-heading" name="mnv_news_info_heading" value="'.esc_attr(get_post_meta($post->ID,'mnv_news_info_heading',true)).'"></p>';
    echo '<p><label for="mnv-news-related-heading"><strong>Título de otras publicaciones (opcional)</strong></label><br><input class="widefat" id="mnv-news-related-heading" name="mnv_news_related_heading" value="'.esc_attr(get_post_meta($post->ID,'mnv_news_related_heading',true)).'"></p>';
    echo '<p><label><input type="checkbox" name="mnv_news_related_enabled" value="1" '.checked(get_post_meta($post->ID,'mnv_news_related_enabled',true),'1',false).'> Mostrar otras publicaciones al final</label></p>';
    echo '<p class="description">Solo aparecerán los apartados que tengan datos guardados. No se insertan textos de prueba.</p>';
}

add_action( 'add_meta_boxes_miniso_novedad', function () {
    add_meta_box( 'mct-news-details', 'MINISO · Detalles y video de la noticia', 'mnv_news_meta_box', 'miniso_novedad', 'normal', 'default' );
} );

add_action( 'save_post_miniso_novedad', function ( $post_id ) {
    if ( ! isset( $_POST['mct_news_meta_nonce'] ) || ! is_scalar( $_POST['mct_news_meta_nonce'] ) ) return;
    $nonce = sanitize_text_field( wp_unslash( $_POST['mct_news_meta_nonce'] ) );
    if ( ! wp_verify_nonce( $nonce, 'mct_news_meta_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) return;

    $type = isset( $_POST['mct_news_type'] ) && is_scalar( $_POST['mct_news_type'] )
        ? sanitize_key( wp_unslash( $_POST['mct_news_type'] ) ) : '';
    if ( $type && isset( mnv_news_types()[ $type ] ) ) update_post_meta( $post_id, 'mct_news_type', $type );
    else delete_post_meta( $post_id, 'mct_news_type' );

    $date = isset( $_POST['mct_news_event_date'] ) && is_scalar( $_POST['mct_news_event_date'] )
        ? sanitize_text_field( wp_unslash( $_POST['mct_news_event_date'] ) ) : '';
    if ( $date && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) && false !== strtotime( $date ) ) update_post_meta( $post_id, 'mct_news_event_date', $date );
    else delete_post_meta( $post_id, 'mct_news_event_date' );

    $location = isset( $_POST['mct_news_location'] ) && is_scalar( $_POST['mct_news_location'] )
        ? sanitize_text_field( wp_unslash( $_POST['mct_news_location'] ) ) : '';
    if ( $location ) update_post_meta( $post_id, 'mct_news_location', $location );
    else delete_post_meta( $post_id, 'mct_news_location' );

    $location_url = isset( $_POST['mct_news_location_url'] ) && is_scalar( $_POST['mct_news_location_url'] )
        ? esc_url_raw( wp_unslash( $_POST['mct_news_location_url'] ), array( 'https', 'http' ) ) : '';
    if ( $location_url ) update_post_meta( $post_id, 'mct_news_location_url', $location_url );
    else delete_post_meta( $post_id, 'mct_news_location_url' );


    $reel = isset( $_POST['mct_news_instagram_url'] ) && is_scalar( $_POST['mct_news_instagram_url'] )
        ? mnv_news_validate_instagram_url( wp_unslash( $_POST['mct_news_instagram_url'] ) ) : '';
    if ( $reel ) update_post_meta( $post_id, 'mct_news_instagram_url', $reel );
    else delete_post_meta( $post_id, 'mct_news_instagram_url' );

    if ( ! empty( $_POST['mct_news_disable_instagram'] ) ) update_post_meta( $post_id, 'mct_news_disable_instagram', '1' );
    else delete_post_meta( $post_id, 'mct_news_disable_instagram' );

    $collection = isset( $_POST['mct_news_collection_url'] ) && is_scalar( $_POST['mct_news_collection_url'] )
        ? esc_url_raw( wp_unslash( $_POST['mct_news_collection_url'] ) ) : '';
    if ( $collection ) update_post_meta( $post_id, 'mct_news_collection_url', $collection );
    else delete_post_meta( $post_id, 'mct_news_collection_url' );
    foreach(array('mnv_news_info_heading','mnv_news_related_heading') as $key) {
        $v=isset($_POST[$key]) && is_scalar($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : '';
        if($v)update_post_meta($post_id,$key,$v);else delete_post_meta($post_id,$key);
    }
    update_post_meta($post_id,'mnv_news_related_enabled',empty($_POST['mnv_news_related_enabled'])?'0':'1');
} );

/** CSS-only focus states; special style only on dedicated news pages. */
add_filter( 'body_class', function ( $classes ) {
    if ( is_page_template( 'templates/novedades.php' ) ) $classes[] = 'mct-news-index-page';
    if ( is_singular( 'miniso_novedad' ) ) $classes[] = 'mct-news-single-page';
    return $classes;
} );
