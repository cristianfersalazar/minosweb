<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
    add_submenu_page( 'woocommerce', 'MINISO Catálogo', 'MINISO Catálogo', 'manage_woocommerce', 'miniso-catalogo', 'miniso_catalog_admin_page' );
    if ( taxonomy_exists( 'pa_licencia' ) ) add_submenu_page( 'edit.php?post_type=product', 'Licencias y portadas', 'Licencias y portadas', 'manage_product_terms', 'edit-tags.php?taxonomy=pa_licencia&post_type=product' );
} );
add_action( 'admin_enqueue_scripts', function () {
    $screen = get_current_screen();
    if ( ! $screen || ( 'pa_licencia' !== $screen->taxonomy && 'woocommerce_page_miniso-catalogo' !== $screen->id && ! in_array($screen->post_type,array('miniso_banner','miniso_novedad'),true) ) ) return;
    wp_enqueue_media();
    wp_enqueue_script( 'miniso-catalog-admin', MINISO_CATALOG_URL . 'assets/admin.js', array( 'jquery', 'media-views' ), MINISO_CATALOG_VERSION, true );
} );

function miniso_catalog_image_field( $name, $id ) {
    echo '<div class="miniso-image-field"><input type="hidden" name="' . esc_attr( $name ) . '" value="' . absint( $id ) . '">';
    echo '<div class="miniso-image-preview" style="max-width:280px;margin:10px 0">';
    if ( $id ) echo wp_get_attachment_image( $id, 'medium', false, array( 'style' => 'max-width:100%;height:auto' ) );
    echo '</div><button type="button" class="button miniso-select-image">Seleccionar imagen</button> <button type="button" class="button-link-delete miniso-remove-image">Quitar</button></div>';
}
function miniso_catalog_image_id( $value ) {
    $id = absint( $value );
    return $id && wp_attachment_is_image( $id ) ? $id : 0;
}
function miniso_catalog_admin_page() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) return;
    $s = miniso_catalog_settings();
    $terms = taxonomy_exists( 'pa_licencia' ) ? get_terms( array( 'taxonomy' => 'pa_licencia', 'hide_empty' => false ) ) : array();
    ?>
    <div class="wrap">
      <h1>MINISO Catálogo</h1>
      <p>Productos y galerías se administran en <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>">Productos</a>. Las compras en línea permanecen desactivadas mientras este complemento esté activo.</p>
      <?php if ( isset( $_GET['miniso_saved'] ) ) : ?><div class="notice notice-success"><p>Configuración guardada.</p></div><?php endif; ?>
      <?php if ( current_user_can( 'manage_options' ) ) : ?>
      <div style="background:white;border:1px solid #ddd;padding:20px;max-width:850px">
        <h2>1. Preparar el sitio</h2>
        <p>Crea las páginas, las portadas Naruto/Marvel y las 29 sucursales de tu borrador. Importa las cinco imágenes suministradas sin modificar los originales. No crea productos ficticios ni reemplaza contenido existente.</p>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
          <input type="hidden" name="action" value="miniso_catalog_setup">
          <?php wp_nonce_field( 'miniso_catalog_setup' ); ?>
          <p><label><input type="checkbox" name="set_front" value="1"> Usar la página Inicio MINISO como portada del sitio</label></p>
          <?php submit_button( 'Preparar páginas y portadas', 'secondary', 'submit', false ); ?>
        </form>
        <?php $errors = get_option( 'miniso_catalog_setup_errors', array() ); if ( $errors ) : ?>
          <div class="notice notice-warning"><p>Revisa estos elementos:</p><ul><?php foreach ( $errors as $error ) echo '<li>' . esc_html( $error ) . '</li>'; ?></ul></div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <h2>Diseño, movimiento y redes</h2><p><a class="button button-primary" href="<?php echo esc_url(admin_url('customize.php?autofocus[panel]=miniso_design_panel')); ?>">Personalizar el diseño completo</a></p><p>Colores, tipografía, tamaños, títulos, botones, orden de secciones, carrusel y enlaces sociales. Los artes se mantienen completos.</p><p><a href="<?php echo esc_url(admin_url('edit.php?post_type=miniso_banner')); ?>">Administrar banners</a> · <a href="<?php echo esc_url(admin_url('edit.php?post_type=miniso_novedad')); ?>">Administrar novedades</a></p><h2>2. Imágenes y textos de inicio</h2>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="miniso_catalog_save">
        <?php wp_nonce_field( 'miniso_catalog_save' ); ?>
        <table class="form-table"><tbody>
          <?php foreach ( array( 'logo' => 'Logo', 'hero' => 'Banner principal', 'hero_mobile' => 'Banner principal para móvil (opcional)', 'lover' => 'Arte Lover+' ) as $key => $label ) : ?>
          <tr><th><?php echo esc_html( $label ); ?></th><td><?php miniso_catalog_image_field( $key, $s[ $key ] ); ?></td></tr>
          <?php endforeach; ?>
          <?php foreach ( array( 'location' => 'Ubicación del encabezado', 'hero_text' => 'Texto bajo el banner', 'lover_title' => 'Título de beneficios', 'lover_text' => 'Texto de beneficios' ) as $key => $label ) : ?>
          <tr><th><label for="mc-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th><td><input class="large-text" id="mc-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $s[ $key ] ); ?>"></td></tr>
          <?php endforeach; ?>
          <tr><th>Portadas destacadas</th><td>
          <?php for ( $i = 0; $i < 12; $i++ ) : ?>
            <p><label>Posición <?php echo $i + 1; ?> <select name="featured[]"><option value="0">Ninguna</option>
            <?php if ( ! is_wp_error( $terms ) ) foreach ( $terms as $term ) : ?>
              <option value="<?php echo absint( $term->term_id ); ?>" <?php selected( $s['featured'][ $i ] ?? 0, $term->term_id ); ?>><?php echo esc_html( $term->name ); ?></option>
            <?php endforeach; ?></select></label></p>
          <?php endfor; ?>
          </td></tr>
          <tr><th><label for="mc-page-size">Productos por página</label></th><td><input id="mc-page-size" name="page_size" type="number" min="1" max="48" value="<?php echo absint( $s['page_size'] ); ?>"></td></tr>
        </tbody></table>
        <?php submit_button(); ?>
      </form>
      <h2>Portadas de prueba de Canadá</h2><p>Importa las 13 colaboraciones descargadas y agrega hasta 8 destacadas. Conserva imágenes y colores existentes. No crea productos ni confirma disponibilidad en Guatemala.</p><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="miniso_demo_covers"><?php wp_nonce_field('miniso_demo_covers'); submit_button('Importar portadas de prueba','secondary','submit',false); ?></form><h2>3. Trabajo diario</h2>
      <ul>
        <li><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=pa_licencia&post_type=product' ) ); ?>">Licencias y portadas</a>: una portada por colección. El atributo Licencia conecta automáticamente sus productos.</li>
        <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>">Productos</a>: nombre, SKU, código de barras, categorías, imagen principal y galería.</li>
        <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=miniso_sucursal' ) ); ?>">Sucursales</a>: grupo, dirección, horarios y enlace al mapa.</li>
        <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">Páginas</a>: textos de Lover+, Ayuda y encabezados de secciones.</li>
      </ul>
      <p>Importación: Productos → Importar → CSV UTF-8. Las columnas Licencia, Categoria y Subcategoria deben aparecer como MINISO. Los productos nuevos de la plantilla de ocho columnas quedan en borrador para revisión.</p>
    </div>
    <?php
}
add_action( 'admin_post_miniso_catalog_save', function () {
    if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'No tienes permiso.' );
    check_admin_referer( 'miniso_catalog_save' );
    $s = miniso_catalog_settings();
    foreach ( array( 'logo', 'hero', 'hero_mobile', 'lover' ) as $key ) $s[ $key ] = miniso_catalog_image_id( $_POST[ $key ] ?? 0 );
    foreach ( array( 'location', 'hero_text', 'lover_title', 'lover_text' ) as $key ) $s[ $key ] = sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) );
    $s['page_size'] = max( 1, min( 48, absint( $_POST['page_size'] ?? 12 ) ) );
    $s['featured'] = array();
    foreach ( array_slice( (array) ( $_POST['featured'] ?? array() ), 0, 12 ) as $id ) {
        $id = absint( $id );
        if ( $id && term_exists( $id, 'pa_licencia' ) && ! in_array( $id, $s['featured'], true ) ) $s['featured'][] = $id;
    }
    update_option( 'miniso_catalog_settings', $s, false );
    wp_safe_redirect( admin_url( 'admin.php?page=miniso-catalogo&miniso_saved=1' ) );
    exit;
} );

function miniso_catalog_term_fields( $term = null ) {
    $editing = $term instanceof WP_Term;
    $id = $editing ? $term->term_id : 0;
    $group = get_term_meta( $id, 'miniso_group', true ) ?: 'Licencias';
    if ( $editing ) echo '<tr class="form-field"><th>Portada de la colección</th><td>'; else echo '<div class="form-field"><label>Portada de la colección</label>';
    wp_nonce_field( 'miniso_catalog_term', 'miniso_catalog_term_nonce' );
    miniso_catalog_image_field( 'miniso_cover_id', get_term_meta( $id, 'miniso_cover_id', true ) );
    echo '<p>Se muestra completa. Preferencia: 1200 × 1200 px; también admite el material oficial con otra proporción.</p>';
    echo '<p><label>Grupo <select name="miniso_group">';
    foreach ( array( 'Licencias', 'MiniFamily', 'Promociones' ) as $option ) echo '<option ' . selected( $group, $option, false ) . '>' . esc_html( $option ) . '</option>';
    echo '</select></label></p>';
    $accent = $editing ? miniso_collection_color($term) : '#e60023';
    echo '<p><label>Color destacado <input type="color" name="miniso_color" value="'.esc_attr($accent).'"></label></p><p>Colorea el marco, el fondo y los detalles de esta colección. La imagen no se altera.</p>';
    echo $editing ? '</td></tr>' : '</div>';
}
add_action( 'pa_licencia_add_form_fields', 'miniso_catalog_term_fields' );
add_action( 'pa_licencia_edit_form_fields', 'miniso_catalog_term_fields' );
function miniso_catalog_save_term( $id ) {
    if ( ! current_user_can( 'manage_product_terms' ) || empty( $_POST['miniso_catalog_term_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['miniso_catalog_term_nonce'] ) ), 'miniso_catalog_term' ) ) return;
    update_term_meta($id,'miniso_color',sanitize_hex_color(wp_unslash($_POST['miniso_color']??''))?:'#e60023');
    update_term_meta( $id, 'miniso_cover_id', miniso_catalog_image_id( $_POST['miniso_cover_id'] ?? 0 ) );
    $group = sanitize_text_field( wp_unslash( $_POST['miniso_group'] ?? 'Licencias' ) );
    update_term_meta( $id, 'miniso_group', in_array( $group, array( 'Licencias', 'MiniFamily', 'Promociones' ), true ) ? $group : 'Licencias' );
}
add_action( 'created_pa_licencia', 'miniso_catalog_save_term' );
add_action( 'edited_pa_licencia', 'miniso_catalog_save_term' );

add_action( 'add_meta_boxes_miniso_sucursal', function () {
    add_meta_box( 'miniso-store', 'Ubicación de la sucursal', function ( $post ) {
        wp_nonce_field( 'miniso_store_save', 'miniso_store_nonce' );
        $region = get_post_meta( $post->ID, 'miniso_region', true );
        echo '<p><label>Grupo <select name="miniso_region"><option value="capital" ' . selected( $region, 'capital', false ) . '>Capital y alrededores</option><option value="interior" ' . selected( $region, 'interior', false ) . '>Interior del país</option></select></label></p>';
        echo '<p><label>Enlace de Google Maps <input type="url" class="widefat" name="miniso_map" value="' . esc_attr( get_post_meta( $post->ID, 'miniso_map', true ) ) . '"></label></p><p>Usa el bloque Datos de visita para dirección y horarios. El editor de contenido queda disponible para información adicional.</p>';
    } );
} );
add_action( 'save_post_miniso_sucursal', function ( $id ) {
    if ( wp_is_post_revision( $id ) || wp_is_post_autosave( $id ) || ! current_user_can( 'edit_post', $id ) || empty( $_POST['miniso_store_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['miniso_store_nonce'] ) ), 'miniso_store_save' ) ) return;
    update_post_meta( $id, 'miniso_region', 'interior' === ( $_POST['miniso_region'] ?? '' ) ? 'interior' : 'capital' );
    update_post_meta( $id, 'miniso_map', esc_url_raw( wp_unslash( $_POST['miniso_map'] ?? '' ), array( 'https', 'http' ) ) );
} );
