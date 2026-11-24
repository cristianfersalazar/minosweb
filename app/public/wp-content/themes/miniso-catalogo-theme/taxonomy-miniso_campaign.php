<?php
defined('ABSPATH') || exit;
get_header();
if (!mct_ready()) mct_dependency_notice();
else mct_render_archive();
get_footer();
