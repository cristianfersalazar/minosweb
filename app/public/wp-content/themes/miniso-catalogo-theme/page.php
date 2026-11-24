<?php
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) {
    the_post();
    if ( mct_is_lover_page() ) {
        // Solo Lover+: el título recibe el mismo fondo rosa de la primera sección.
        // El contenido, los grupos Gutenberg y sus imágenes permanecen en WordPress.
        echo '<article class="page-content mct-lover-article"><div class="mct-lover-heading"><h1>';
        the_title();
        echo '</h1></div>';
        the_content();
        wp_link_pages();
        echo '</article>';
    } else {
        echo '<article class="page-content"><h1>';
        the_title();
        echo '</h1>';
        the_content();
        wp_link_pages();
        echo '</article>';
    }
}
get_footer();
