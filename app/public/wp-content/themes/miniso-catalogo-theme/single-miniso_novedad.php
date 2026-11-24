<?php
/** Plantilla editorial de una novedad MINISO. */
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) : the_post();
    $post = get_post();
    $type = mnv_news_type( $post );
    $type_label = mnv_news_type_label( $post );
    $event_date = mnv_news_event_date( $post );
    $location = mnv_news_location( $post );
    $location_url = mnv_news_location_url( $post );
    $prizes = mnv_news_prizes( $post );
    $activities = mnv_news_activities( $post );
    $videos = mnv_news_videos( $post );
    $video_text = mnv_news_videos_text( $post );
    $collection = esc_url( get_post_meta( get_the_ID(), 'mct_news_collection_url', true ) );
?>
<article class="mct-news-article mct-news-article-<?php echo esc_attr( $type ); ?>">
  <header class="mct-news-story-head">
    <div class="mct-news-story-wrap">
      <nav class="mct-news-breadcrumb" aria-label="Ruta de navegación"><a href="<?php echo esc_url( mct_url( 'novedades' ) ); ?>">← Todas las novedades</a></nav>
      <div class="mct-news-story-meta">
        <span class="mct-news-pill mct-type-<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $type_label ); ?></span>
        <span class="mct-news-date"><?php echo esc_html( $event_date ? 'Fecha: ' : 'Publicado el ' ); ?><?php echo esc_html( mnv_news_date_display( $post ) ); ?></span>
      </div>
      <h1><?php the_title(); ?></h1>
      <?php if ( has_excerpt() ) : ?><p class="mct-news-story-lead"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
      <div class="mct-news-story-details">
        <?php if ( $location ) : ?>
          <?php if ( $location_url ) : ?>
            <a class="mct-news-location-link" href="<?php echo esc_url( $location_url ); ?>" target="_blank" rel="noopener noreferrer"><span aria-hidden="true">📍</span> <?php echo esc_html( $location ); ?> <span aria-hidden="true">↗</span></a>
          <?php else : ?><span><span aria-hidden="true">📍</span> <?php echo esc_html( $location ); ?></span><?php endif; ?>
        <?php endif; ?>
        <?php if ( $videos ) : ?><a href="#mnv-videos">▶ Ver <?php echo count($videos) === 1 ? 'video' : 'videos'; ?> <span aria-hidden="true">↓</span></a><?php endif; ?>
      </div>
    </div>
  </header>

  <div class="mct-news-story-wrap mct-news-story-body">
    <?php if ( post_password_required() ) :
      echo get_the_password_form();
    else : ?>
      <?php if ( has_post_thumbnail() ) : ?>
        <figure class="mct-news-story-cover">
          <?php mct_media( get_post_thumbnail_id(), get_the_title(), 'mct-news-story-cover-media', true ); ?>
        </figure>
      <?php endif; ?>
      <div class="mct-news-story-content">
        <?php the_content(); wp_link_pages(); ?>
      </div>
      <?php if ( $activities ) : ?>
        <section class="mnv-universal-activities" aria-label="Actividades de la publicación">
          <?php if ( $activities['heading'] || $activities['intro'] ) : ?>
            <div class="mnv-universal-activities__intro">
              <?php if ( $activities['heading'] ) : ?><h2><?php echo esc_html( $activities['heading'] ); ?></h2><?php endif; ?>
              <?php if ( $activities['intro'] ) : ?><p><?php echo nl2br( esc_html( $activities['intro'] ) ); ?></p><?php endif; ?>
            </div>
          <?php endif; ?>
          <?php if ( $activities['items'] ) : ?>
            <div class="mnv-universal-activities__grid">
              <?php foreach ( $activities['items'] as $activity ) : ?>
                <article class="mnv-universal-activities__card">
                  <?php if ( $activity['image_id'] ) : ?>
                    <?php echo wp_get_attachment_image( $activity['image_id'], 'medium_large', false, array( 'loading' => 'lazy' ) ); ?>
                  <?php endif; ?>
                  <?php if ( $activity['title'] ) : ?><h3><?php echo esc_html( $activity['title'] ); ?></h3><?php endif; ?>
                  <?php if ( $activity['description'] ) : ?><p><?php echo nl2br( esc_html( $activity['description'] ) ); ?></p><?php endif; ?>
                  <?php if ( $activity['url'] ) : ?><a href="<?php echo esc_url( $activity['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $activity['url_label'] ?: 'Ver más' ); ?></a><?php endif; ?>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      <?php endif; ?>
      <?php if ( $prizes ) : ?>
        <section class="mnv-news-prizes" aria-label="Premios de la publicación">
          <div class="mnv-news-prizes-head">
            <?php if ( $prizes['heading'] ) : ?><h2 id="mnv-prizes-title"><?php echo esc_html( $prizes['heading'] ); ?></h2><?php endif; ?>
            <?php if ( $prizes['intro'] ) : ?><p><?php echo esc_html( $prizes['intro'] ); ?></p><?php endif; ?>
          </div>
          <div class="mnv-gifts-grid" aria-label="Premios y sorpresas de la novedad">
            <?php foreach ( $prizes['items'] as $index => $prize ) : ?>
              <div class="mnv-gift-card mnv-gift-color-<?php echo esc_attr( $index % 4 ); ?>">
                <span class="mnv-gift-symbol" aria-hidden="true"><?php echo esc_html( array('🎁','💝','✨','🎉')[$index % 4] ); ?></span>
                <?php if ( $prize['amount'] !== '' ) : ?><strong><?php echo esc_html( $prize['amount'] ); ?></strong><?php endif; ?>
                <span><?php echo esc_html( $prize['description'] ); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
      <?php if ( $location || $event_date || $collection ) : ?>
        <aside class="mct-news-event-info" aria-label="Información de la novedad">
          <?php if(get_post_meta(get_the_ID(),'mnv_news_info_heading',true)): ?><div><h2><?php echo esc_html(get_post_meta(get_the_ID(),'mnv_news_info_heading',true)); ?></h2></div><?php endif; ?>
          <div class="mct-news-info-grid">
            <?php if ( $location ) : ?><div class="mct-news-info-card"><span class="mct-news-info-icon" aria-hidden="true">📍</span><strong>¿Dónde?</strong><p><?php if ( $location_url ) : ?><a href="<?php echo esc_url( $location_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $location ); ?> <span aria-hidden="true">↗</span></a><?php else : ?><?php echo esc_html( $location ); ?><?php endif; ?></p></div><?php endif; ?>
            <?php if ( $event_date ) : ?><div class="mct-news-info-card"><span class="mct-news-info-icon" aria-hidden="true">📅</span><strong>¿Cuándo?</strong><p><?php echo esc_html( mnv_news_date_display( $post ) ); ?></p></div><?php endif; ?>
            <?php if ( $collection ) : ?><div class="mct-news-info-card"><span class="mct-news-info-icon" aria-hidden="true">🛍️</span><strong>Conoce más</strong><p><a href="<?php echo esc_url( $collection ); ?>">Explorar colección ↗</a></p></div><?php endif; ?>
          </div>
        </aside>
      <?php endif; ?>
      <?php if ( $videos ) : ?>
        <section class="mct-news-instagram mnv-videos-section <?php echo count($videos) > 1 ? 'mnv-videos-many' : 'mnv-videos-one'; ?>" id="mnv-videos" aria-label="Videos de la publicación">
          <div class="mct-news-instagram-intro">
            <?php if ( $video_text['heading'] ) : ?><h2 id="mnv-videos-title"><?php echo esc_html( $video_text['heading'] ); ?></h2><?php endif; ?>
            <?php if ( $video_text['intro'] ) : ?><p><?php echo esc_html( $video_text['intro'] ); ?></p><?php endif; ?>
          </div>
          <div class="mnv-videos-grid">
            <?php foreach ( $videos as $video_index => $video ) : ?>
              <article class="mnv-video-card">
                <?php if ( count($videos) > 1 ) : ?><h3><?php echo esc_html($video['title']); ?></h3><?php endif; ?>
                <div class="mct-news-instagram-video mnv-video-<?php echo esc_attr($video['kind']); ?>">
                  <?php if ($video['kind'] === 'file') : ?>
                    <video controls playsinline preload="metadata" aria-label="<?php echo esc_attr($video['title']); ?>">
                      <source src="<?php echo esc_url($video['url']); ?>">
                      Tu navegador no admite este video.
                    </video>
                  <?php else : ?>
                    <iframe title="<?php echo esc_attr($video['title']); ?>" src="<?php echo esc_url($video['embed']); ?>" loading="lazy" allow="autoplay; encrypted-media; fullscreen; picture-in-picture" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                  <?php endif; ?>
                </div>
                <a class="mct-news-instagram-button" href="<?php echo esc_url($video['url']); ?>" target="_blank" rel="noopener noreferrer">
                  <?php echo $video['kind'] === 'file' ? 'Abrir video original' : 'Ver en ' . esc_html($video['provider']); ?> <span aria-hidden="true">↗</span>
                </a>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    <?php endif; ?>
    <div class="mct-news-story-end"><a class="mct-news-link" href="<?php echo esc_url( mct_url( 'novedades' ) ); ?>">← Volver a Novedades</a></div>
  </div>
</article>
<?php
endwhile;
$related_enabled = get_post_meta(get_queried_object_id(),'mnv_news_related_enabled',true)==='1';
$related = $related_enabled ? get_posts( array( 'post_type' => 'miniso_novedad', 'post_status' => 'publish', 'has_password' => false, 'exclude' => array( get_queried_object_id() ), 'numberposts' => 3 ) ) : array();
if ( $related ) : ?>
  <section class="mct-news-related"><div class="mct-news-wrap"><?php if(get_post_meta(get_queried_object_id(),'mnv_news_related_heading',true)): ?><h2><?php echo esc_html(get_post_meta(get_queried_object_id(),'mnv_news_related_heading',true)); ?></h2><?php endif; ?><div class="news-grid mct-news-list-grid"><?php foreach ( $related as $related_post ) mnv_news_card( $related_post ); ?></div></div></section>
<?php endif;
get_footer();
