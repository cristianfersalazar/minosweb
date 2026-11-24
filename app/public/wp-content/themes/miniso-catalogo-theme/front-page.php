<?php
/** Portada: conserva modo clásico hasta que se elija explícitamente el editor flexible. */
defined('ABSPATH') || exit;
get_header();
$mid = get_queried_object_id();
if (function_exists('mct_modular_home_enabled') && mct_modular_home_enabled($mid)) {
    while(have_posts()): the_post();
        echo '<div class="miniso-flexible-home">';
        the_content();
        echo '</div>';
    endwhile;
} else {
    echo mct_global_page_blocks($mid,'before');
    mct_home();
    echo mct_global_page_blocks($mid,'after');
}
get_footer();
