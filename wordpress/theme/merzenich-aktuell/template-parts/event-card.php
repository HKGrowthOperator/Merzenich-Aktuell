<?php
if (!defined('ABSPATH')) { exit; }
$start=function_exists('ma_event_timestamp')?ma_event_timestamp(get_the_ID(),'start'):0;
$end=function_exists('ma_event_timestamp')?ma_event_timestamp(get_the_ID(),'end'):0;
$place=(string)get_post_meta(get_the_ID(),'ma_event_place',true);
$organizer=(string)get_post_meta(get_the_ID(),'ma_event_organizer',true);
?>
<article class="event-card">
  <div class="event-card__date">
    <span class="event-card__day"><?php echo $start?esc_html(wp_date('d',$start)):'–'; ?></span>
    <span class="event-card__month"><?php echo $start?esc_html(wp_date('M',$start)):'Termin'; ?></span>
  </div>
  <div class="event-card__body">
    <div class="eyebrow"><?php echo ma_theme_location_label(); ?> · Termin</div>
    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <div class="event-card__meta">
      <?php if($start): ?><span><?php echo esc_html(wp_date('d.m.Y · H:i',$start)); ?> Uhr<?php if($end && wp_date('Y-m-d',$end)===wp_date('Y-m-d',$start)): ?>–<?php echo esc_html(wp_date('H:i',$end)); ?> Uhr<?php endif; ?></span><?php endif; ?>
      <?php if($place!==''): ?><span><?php echo esc_html($place); ?></span><?php endif; ?>
      <?php if($organizer!==''): ?><span><?php echo esc_html($organizer); ?></span><?php endif; ?>
    </div>
    <?php if(has_excerpt()): ?><p><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
    <a class="read-more" href="<?php the_permalink(); ?>">Termin ansehen</a>
  </div>
</article>
