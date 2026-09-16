<?php
if (!defined('ABSPATH')) { exit; }
$type=get_post_type();
$meta=ma_theme_market_meta();
?>
<article class="market-card market-card--<?php echo esc_attr($type); ?>">
  <?php if(has_post_thumbnail() && !in_array($type,['ma_obituary'],true)): ?>
    <a class="market-card__image" href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium_large',['loading'=>'lazy']); ?></a>
  <?php endif; ?>
  <div class="market-card__body">
    <div class="eyebrow"><?php echo esc_html(ma_theme_market_type_label($type)); ?> · <?php echo ma_theme_location_label(); ?></div>
    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <?php if($meta): ?><div class="market-meta"><?php foreach($meta as $item): ?><span><?php echo esc_html($item); ?></span><?php endforeach; ?></div><?php endif; ?>
    <?php if(has_excerpt()): ?><p><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
    <a class="read-more" href="<?php the_permalink(); ?>">Details ansehen</a>
  </div>
</article>
