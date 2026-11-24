<?php defined( 'ABSPATH' ) || exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#contenido">Saltar al contenido</a>
<?php if(mct_design('announcement')): ?><div class="announcement"><?php echo esc_html(mct_design('announcement')); ?></div><?php endif; ?>
<header class="site-header"><div class="header-inner">
  <a class="brand" href="<?php echo esc_url( mct_url( 'inicio' ) ); ?>" aria-label="MINISO, inicio">
    <?php $s = mct_settings(); if ( ! empty( $s['logo'] ) ) mct_media( $s['logo'], 'MINISO', '', true ); else echo 'MINISO'; ?>
  </a>
  <span class="location"><?php echo esc_html( $s['location'] ?? 'Guatemala' ); ?></span>
  <nav class="desktop-nav" aria-label="Navegación principal"><?php mct_navigation(); ?></nav>
  <a class="favorite-link" href="<?php echo esc_url( mct_url( 'favoritos' ) ); ?>" aria-label="Mis favoritos"><?php echo mct_icon( 'heart' ); ?><span data-favorite-count class="js-only">0</span></a>
</div></header>
<main id="contenido">
