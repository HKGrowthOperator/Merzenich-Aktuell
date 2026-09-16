<?php
get_header();
$market_types=['ma_property','ma_job','ma_obituary','ma_family_notice'];
?>
<div class="wrap archive-page">
  <header class="archive-head">
    <div class="eyebrow">Merzenich Aktuell</div>
    <h1 class="section-title"><?php the_archive_title(); ?></h1>
    <?php if($description=get_the_archive_description()): ?><div class="archive-description"><?php echo wp_kses_post($description); ?></div><?php endif; ?>
    <?php /* Ortsteilseite: Adresse, Kurzinfo, Quelle und Bild vom Begriff selbst. */
    if(is_tax('ma_location') && function_exists('ma_ort_info') && ($term=get_queried_object()) instanceof WP_Term): $ort=ma_ort_info($term); ?>
      <div class="ort-head">
        <figure class="ort-head__image<?php echo $ort['is_symbol']?' ort-head__image--symbol':''; ?>"><img src="<?php echo esc_url($ort['image']); ?>" alt="<?php echo esc_attr($ort['image_alt']); ?>" loading="lazy" decoding="async"><figcaption class="image-credit"><?php echo esc_html($ort['credit']); ?></figcaption></figure>
        <div class="ort-head__facts">
          <?php if($ort['summary']!==''): ?><p><?php echo esc_html($ort['summary']); ?></p><?php endif; ?>
          <?php if($ort['address']!==''): ?><p class="ort-head__address"><?php echo esc_html($ort['address']); ?></p><?php endif; ?>
          <?php if($ort['source_url']!==''): ?><p><a class="read-more" href="<?php echo esc_url($ort['source_url']); ?>" target="_blank" rel="noopener noreferrer">Originalquelle</a></p><?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </header>

  <div class="archive-content-list">
    <?php if(have_posts()): while(have_posts()): the_post(); ?>
      <?php $type=get_post_type(); ?>
      <?php if($type==='ma_event'): ?>
        <?php get_template_part('template-parts/event-card'); ?>
      <?php elseif(in_array($type,$market_types,true)): ?>
        <?php get_template_part('template-parts/market-card'); ?>
      <?php else: ?>
        <?php get_template_part('template-parts/card'); ?>
      <?php endif; ?>
    <?php endwhile; else: ?>
      <div class="empty archive-empty">Aktuell liegen in diesem Bereich keine veröffentlichten Einträge vor.</div>
    <?php endif; ?>
  </div>

  <?php the_posts_pagination(); ?>
</div>
<?php get_footer(); ?>
