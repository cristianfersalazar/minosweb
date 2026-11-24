<?php
/** Template Name: MINISO · Novedades */
defined('ABSPATH') || exit;
get_header();

// Esta página es un índice de publicaciones, NO el cuerpo de una noticia individual.
// Sus textos generales se administran en «MINISO · Presentación de Novedades».
$all_types = mnv_news_types();
$type = sanitize_key( mct_get( 'tipo' ) );
if ( ! isset( $all_types[ $type ] ) ) $type = '';
$per_page = min(30,max(1,(int)mnv_index_field(get_queried_object_id(),'per_page',8)));
$all_posts = mnv_news_filtered_posts( $type );
$total = count( $all_posts );
$total_pages = max( 1, (int)ceil($total / $per_page) );
$current_page = min($total_pages, max(1, absint(mct_get('mc_page'))));
$display = array_slice($all_posts, ($current_page - 1) * $per_page, $per_page);
$page_id = get_queried_object_id();
$base = get_permalink($page_id);
?>
<?php if(function_exists('mct_global_page_blocks')) echo mct_global_page_blocks($page_id,'before'); ?>
<div class="mct-news-archive mnv-news-index-v14">
  <header class="mct-news-index-hero mnv-news-home-hero" style="--mnv-hero-bg:<?php echo esc_attr(sanitize_hex_color((string)mnv_index_field($page_id,'color')) ?: '#FFE1E8'); ?>">
    <div class="mct-news-wrap">
      <?php if(mnv_index_field($page_id,'eyebrow')): ?><span class="mct-news-overline"><?php echo esc_html(mnv_index_field($page_id,'eyebrow')); ?></span><?php endif; ?>
      <h1><?php echo esc_html(get_the_title($page_id)); ?></h1>
      <?php if(mnv_index_intro($page_id)): ?><p class="mnv-news-home-lead"><?php echo nl2br(esc_html(mnv_index_intro($page_id))); ?></p><?php endif; ?>
    </div>
  </header>

  <div class="mct-news-wrap mct-news-archive-body">
    <?php $directory_title=mnv_index_field($page_id,'directory_title'); $directory_intro=mnv_index_field($page_id,'directory_intro'); ?>
    <?php if($directory_title || $directory_intro): ?>
    <div class="mnv-news-directory-head">
      <div>
        <?php if($directory_title): ?><h2><?php echo esc_html($directory_title); ?></h2><?php endif; ?>
        <?php if($directory_intro): ?><p><?php echo nl2br(esc_html($directory_intro)); ?></p><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
    <?php if(mnv_index_field($page_id,'filters_enabled','1')==='1'): ?>
    <nav class="mct-news-filters" aria-label="Filtrar novedades">
      <?php
      $filters = array('' => 'Todas') + $all_types;
      foreach ($filters as $key => $label) {
          $url = $key ? add_query_arg('tipo',$key,$base) : $base;
          echo '<a class="mct-news-filter' . ($key === $type ? ' is-active' : '') . '" href="' . esc_url($url) . '"' . ($key === $type ? ' aria-current="page"' : '') . '>' . esc_html($label === 'Novedad' ? 'Otras noticias' : $label) . '</a>';
      }
      ?>
    </nav>
    <?php endif; ?>

    <?php if ($display) : ?>
      <div class="news-grid mct-news-list-grid mnv-news-directory-grid" aria-label="Publicaciones de MINISO Guatemala">
        <?php foreach ($display as $item) mnv_news_card($item); ?>
      </div>
    <?php else : ?>
      <div class="mct-news-empty" aria-live="polite">
        <?php if ($type) : ?><a class="mct-news-link" href="<?php echo esc_url($base); ?>">Ver todas las novedades</a><?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($total_pages > 1) : ?>
      <nav class="pagination mct-news-pagination" aria-label="Páginas de novedades">
        <?php
        echo wp_kses_post(paginate_links(array(
          'base' => str_replace('999999', '%#%', add_query_arg(array_filter(array('mc_page' => 999999, 'tipo' => $type)), $base)),
          'format' => '',
          'current' => $current_page,
          'total' => $total_pages,
          'prev_text' => '← Anterior',
          'next_text' => 'Siguiente →',
        )));
        ?>
      </nav>
    <?php endif; ?>
  </div>
</div>
<?php if(function_exists('mct_global_page_blocks')) echo mct_global_page_blocks($page_id,'after'); ?>
<?php get_footer(); ?>
