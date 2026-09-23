<?php
if (!defined('ABSPATH')) { exit; }
$bild=ma_content_image(null,'medium_large');
$kind=(string)get_post_meta(get_the_ID(),'ma_tip_kind',true);
$labels=['tipp'=>'Tipp','sponsoring'=>'Sponsoring','anzeige'=>'Anzeige'];
$label=$labels[$kind]??'Tipp';
$sponsor=(string)get_post_meta(get_the_ID(),'ma_tip_sponsor',true);
?>
<article class="tip-card">
  <a class="tip-card__image<?php echo $bild['is_fallback']?' tip-card__image--symbol':''; ?>" href="<?php the_permalink(); ?>">
    <img src="<?php echo esc_url($bild['url']); ?>" alt="<?php echo esc_attr($bild['alt']); ?>" loading="lazy" decoding="async">
    <span class="image-credit"><?php echo esc_html(ma_image_caption($bild)); ?></span>
  </a>
  <div class="tip-card__body">
    <div class="tip-disclosure"><?php echo esc_html($label); ?> · bezahlte Platzierung<?php if($sponsor!==''): ?> · <?php echo esc_html($sponsor); ?><?php endif; ?></div>
    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <?php if(has_excerpt()): ?><p><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
    <div class="meta-line"><span><?php echo esc_html(get_the_date('d.m.Y')); ?></span><span><?php echo ma_theme_location_label(); ?></span></div>
    <a class="read-more" href="<?php the_permalink(); ?>">Tipp ansehen</a>
  </div>
</article>
