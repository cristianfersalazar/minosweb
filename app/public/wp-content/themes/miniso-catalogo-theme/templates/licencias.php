<?php
/** Template Name: MINISO · Catálogo */
defined('ABSPATH') || exit;
get_header();
if (!mct_ready()) { mct_dependency_notice(); get_footer(); return; }
while (have_posts()) {
    the_post();
    echo '<h1>'.esc_html(get_the_title()).'</h1>';
    the_content();
}
mct_catalog_overview();
get_footer();
