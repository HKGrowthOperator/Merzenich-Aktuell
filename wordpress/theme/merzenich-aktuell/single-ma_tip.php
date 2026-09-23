<?php
get_header();
while(have_posts()): the_post();
  $kind=(string)get_post_meta(get_the_ID(),'ma_tip_kind',true);
  $labels=['tipp'=>'Tipp','sponsoring'=>'Sponsoring','anzeige'=>'Anzeige'];
  $label=$labels[$kind]??'Tipp';
  $sponsor=(string)get_post_meta(get_the_ID(),'ma_tip_sponsor',true);
  $partner_url=(string)get_post_meta(get_the_ID(),'ma_tip_url',true);
  $bild=ma_content_image(null,'full');
?>
<article class="article wrap tip-article">
  <div class="tip-disclosure"><?php echo esc_html($label); ?> · bezahlte Platzierung<?php if($sponsor!==''): ?> · Auftraggeber: <?php echo esc_html($sponsor); ?><?php endif; ?></div>
  <div class="eyebrow"><?php echo ma_theme_location_label(); ?> · Tipp</div>
  <h1><?php the_title(); ?></h1>
  <?php if(has_excerpt()): ?><p class="article-dek"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
  <div class="meta-line"><span><?php echo esc_html(get_the_date('d.m.Y')); ?></span><a href="<?php echo esc_url(get_post_type_archive_link('ma_tip')); ?>">Alle Tipps</a></div>
  <figure class="article-hero<?php echo $bild['is_fallback']?' article-hero--symbol':''; ?>"><img src="<?php echo esc_url($bild['url']); ?>" alt="<?php echo esc_attr($bild['alt']); ?>" decoding="async"><figcaption><?php echo esc_html(ma_image_caption($bild)); ?></figcaption></figure>
  <div class="facts"><strong>Transparenz</strong><p>Dieser Inhalt ist eine bezahlte oder gesponserte Platzierung und kein redaktioneller Nachrichtenbeitrag.</p></div>
  <div class="article-body"><?php the_content(); ?></div>
  <?php if($partner_url!==''): ?><p><a class="btn" href="<?php echo esc_url($partner_url); ?>" target="_blank" rel="noopener sponsored">Zum Partner-Angebot</a></p><?php endif; ?>
  <div class="source-box"><strong>Werbliche Kennzeichnung</strong><br><?php echo esc_html($label); ?> · bezahlte Platzierung<?php if($sponsor!==''): ?> · <?php echo esc_html($sponsor); ?><?php endif; ?></div>
</article>
<?php endwhile; get_footer(); ?>
