<?php
/**
 * Unternehmensprofil. Nur Angaben des Unternehmens, mit Quelle und Stand;
 * keine Preise (Preis auf Anfrage).
 */
get_header();
while(have_posts()): the_post();
  $p=function_exists('ma_business_profile') ? ma_business_profile((int)get_the_ID()) : [];
  $bild=ma_content_image(null,'large');
  $fakten=[
    'Branche'=>$p['branche']??'',
    'Ortsteil'=>$p['ortsteil']??'',
    'Adresse'=>$p['adresse']??'',
  ];
?>
<article class="service-detail wrap business-detail">
  <div class="eyebrow">Unternehmen · <?php echo ma_theme_location_label(); ?></div>
  <h1><?php the_title(); ?></h1>
  <?php if(has_excerpt()): ?><p class="article-dek"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>

  <dl class="service-facts">
    <?php foreach($fakten as $label=>$wert): if($wert==='') continue; ?>
      <div><dt><?php echo esc_html($label); ?></dt><dd><?php echo esc_html($wert); ?></dd></div>
    <?php endforeach; ?>
    <?php if(!empty($p['telefon'])): ?><div><dt>Telefon</dt><dd><a href="<?php echo esc_url($p['telefon_href'],['tel']); ?>"><?php echo esc_html($p['telefon']); ?></a></dd></div><?php endif; ?>
    <?php if(!empty($p['website'])): ?><div><dt>Website</dt><dd><a href="<?php echo esc_url($p['website']); ?>" target="_blank" rel="noopener"><?php echo esc_html(preg_replace('~^https?://(www\.)?~i','',rtrim($p['website'],'/'))); ?></a></dd></div><?php endif; ?>
  </dl>

  <?php if(!empty($p['oeffnungszeiten'])): ?>
    <div class="service-note"><strong>Öffnungszeiten</strong><p><?php echo nl2br(esc_html($p['oeffnungszeiten'])); ?></p></div>
  <?php endif; ?>

  <?php if(empty($bild['is_fallback'])): ?>
    <figure class="service-detail__image"><img src="<?php echo esc_url($bild['url']); ?>" alt="<?php echo esc_attr($bild['alt']); ?>" decoding="async"><figcaption class="image-credit"><?php echo esc_html(ma_image_caption($bild)); ?></figcaption></figure>
  <?php endif; ?>

  <div class="article-body service-detail__body"><?php the_content(); ?></div>

  <div class="source-box">
    <strong>Angaben &amp; Transparenz</strong><br>
    Veröffentlicht mit Einwilligung des Unternehmens.<?php if(!empty($p['quelle'])): ?> Quelle: <?php echo esc_html($p['quelle']); ?>.<?php endif; ?><?php if(!empty($p['stand'])): ?> Stand: <?php echo esc_html($p['stand']); ?>.<?php endif; ?>
  </div>

  <p class="service-action"><a class="read-more" href="<?php echo esc_url(get_post_type_archive_link('ma_business')?:home_url('/betriebe/')); ?>">Alle Unternehmen</a></p>
</article>
<?php endwhile; get_footer(); ?>
