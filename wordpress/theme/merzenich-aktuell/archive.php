<?php
get_header();
$market_types=['ma_property','ma_job','ma_obituary','ma_family_notice'];
?>
<div class="wrap archive-page">
  <header class="archive-head">
    <div class="eyebrow">Merzenich Aktuell</div>
    <h1 class="section-title"><?php the_archive_title(); ?></h1>
    <?php if($description=get_the_archive_description()): ?><div class="archive-description"><?php echo wp_kses_post($description); ?></div><?php endif; ?>
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
