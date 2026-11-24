<?php
defined( 'ABSPATH' ) || exit;
get_header();
if ( have_posts() ) {
    while ( have_posts() ) { the_post(); echo '<article class="page-content"><h1>'; the_title(); echo '</h1>'; the_content(); echo '</article>'; }
    the_posts_pagination();
} else echo '<h1>No hay contenido para mostrar</h1>';
get_footer();
