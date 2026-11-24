<?php
/** Template Name: MINISO · Favoritos */
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) { the_post(); echo '<h1>'; the_title(); echo '</h1>'; the_content(); }
?>
<noscript><p>Activa JavaScript para consultar los favoritos guardados en este navegador.</p></noscript>
<div class="grid collection-grid" data-favorites-list hidden>
<?php
if ( mct_ready() ) {
    $terms = get_terms( array( 'taxonomy' => 'pa_licencia', 'hide_empty' => false ) );
    if ( ! is_wp_error( $terms ) ) foreach ( $terms as $term ) mct_collection_card( $term );
}
?>
</div>
<p data-favorites-empty hidden>Aún no has guardado colecciones. Pulsa el corazón de una portada para agregarla.</p>
<?php get_footer(); ?>
