<?php
if (!defined('ABSPATH')) { exit; }
$type=get_post_type();
$meta=ma_theme_market_meta();
?>
<article class="market-card market-card--<?php echo esc_attr($type); ?>">
  <?php
  /* Traueranzeigen zeigen bewusst kein Bild. Fuer alles andere entscheidet
     ma_content_image(): geprueftes Bild, sonst gekennzeichnete Ersatzgrafik.
     Vorher blieb die Karte ohne Bild einfach leer - jetzt steht dort eine
     Flaeche, die selbst sagt, dass sie kein Foto des Objekts ist. */
  if ($type!=='ma_obituary'):
    $bild=ma_content_image(null,'medium_large'); ?>
    <a class="market-card__image<?php echo $bild['is_fallback']?' market-card__image--symbol':''; ?>" href="<?php the_permalink(); ?>">
      <img src="<?php echo esc_url($bild['url']); ?>" alt="<?php echo esc_attr($bild['alt']); ?>" loading="lazy" decoding="async">
      <span class="image-credit"><?php echo esc_html(ma_image_caption($bild)); ?></span>
    </a>
  <?php endif; ?>
  <div class="market-card__body">
    <div class="eyebrow"><?php echo esc_html(ma_theme_market_type_label($type)); ?> · <?php echo ma_theme_location_label(); ?></div>
    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <?php if($meta): ?><div class="market-meta"><?php foreach($meta as $item): ?><span><?php echo esc_html($item); ?></span><?php endforeach; ?></div><?php endif; ?>
    <?php if(has_excerpt()): ?><p><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
    <a class="read-more" href="<?php the_permalink(); ?>">Details ansehen</a>
  </div>
</article>
