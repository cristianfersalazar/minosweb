<?php
/**
 * Template Name: MINISO · Inicio editable
 * Template Post Type: page
 * Todas las secciones provienen de bloques creados por el administrador.
 */
defined('ABSPATH') || exit;
get_header();
while(have_posts()): the_post();
    echo '<div class="miniso-flexible-home">';
    the_content();
    echo '</div>';
endwhile;
get_footer();
