<?php
defined( 'ABSPATH' ) || exit;
get_header();
if ( ! mct_ready() ) mct_dependency_notice();
elseif ( is_product() ) mct_render_product();
else mct_render_archive();
get_footer();
