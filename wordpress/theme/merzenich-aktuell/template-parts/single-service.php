<?php
if (!defined('ABSPATH')) { exit; }
$type=get_post_type();
$post_id=get_the_ID();
$source='';
?>
<article class="service-detail wrap service-detail--<?php echo esc_attr($type); ?>">
  <div class="eyebrow"><?php echo esc_html($type==='ma_event'?'Termin':ma_theme_market_type_label($type)); ?> · <?php echo ma_theme_location_label(); ?></div>
  <h1><?php the_title(); ?></h1>
  <?php if(has_excerpt()): ?><p class="article-dek"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>

  <?php if($type==='ma_event'): ?>
    <?php
      $start=ma_event_timestamp($post_id,'start');
      $end=ma_event_timestamp($post_id,'end');
      $place=(string)get_post_meta($post_id,'ma_event_place',true);
      $organizer=(string)get_post_meta($post_id,'ma_event_organizer',true);
      $price=(string)get_post_meta($post_id,'ma_event_price',true);
      $registration=(string)get_post_meta($post_id,'ma_event_registration',true);
      $source=(string)get_post_meta($post_id,'ma_event_source_url',true);
      if($source==='') $source=(string)get_post_meta($post_id,'ma_source_url',true);
    ?>
    <dl class="service-facts">
      <?php if($start): ?><div><dt>Beginn</dt><dd><?php echo esc_html(wp_date('d.m.Y · H:i',$start)); ?> Uhr</dd></div><?php endif; ?>
      <?php if($end && $end!==$start): ?><div><dt>Ende</dt><dd><?php echo esc_html(wp_date('d.m.Y · H:i',$end)); ?> Uhr</dd></div><?php endif; ?>
      <?php if($place!==''): ?><div><dt>Ort</dt><dd><?php echo esc_html($place); ?></dd></div><?php endif; ?>
      <?php if($organizer!==''): ?><div><dt>Veranstalter</dt><dd><?php echo esc_html($organizer); ?></dd></div><?php endif; ?>
      <?php if($price!==''): ?><div><dt>Eintritt</dt><dd><?php echo esc_html($price); ?></dd></div><?php endif; ?>
    </dl>
    <?php if($registration!==''): ?><div class="service-note"><strong>Anmeldung / Hinweise</strong><p><?php echo nl2br(esc_html($registration)); ?></p></div><?php endif; ?>

  <?php elseif($type==='ma_property'): ?>
    <?php
      $fields=[
        'Preis'=>get_post_meta($post_id,'ma_property_price',true),
        'Warmmiete'=>get_post_meta($post_id,'ma_property_warm_price',true),
        'Zimmer'=>get_post_meta($post_id,'ma_property_rooms',true),
        'Wohnfläche'=>get_post_meta($post_id,'ma_property_area',true),
        'Grundstück'=>get_post_meta($post_id,'ma_property_lot',true),
        'Verfügbar ab'=>get_post_meta($post_id,'ma_property_available_from',true),
        'Lage'=>get_post_meta($post_id,'ma_property_address',true),
        'Anbieter'=>get_post_meta($post_id,'ma_property_provider',true),
        'Kontakt'=>get_post_meta($post_id,'ma_property_contact',true),
      ];
      $source=(string)get_post_meta($post_id,'ma_property_url',true);
    ?>
    <dl class="service-facts"><?php foreach($fields as $label=>$value): if((string)$value==='') continue; ?><div><dt><?php echo esc_html($label); ?></dt><dd><?php echo esc_html((string)$value); ?><?php if($label==='Zimmer'): ?> Zimmer<?php endif; ?></dd></div><?php endforeach; ?></dl>
    <p class="service-verified"><?php echo esc_html(ma_theme_verified_line($post_id)); ?></p>

  <?php elseif($type==='ma_job'): ?>
    <?php
      $fields=[
        'Unternehmen'=>get_post_meta($post_id,'ma_job_company',true),
        'Inseriert von'=>(function() use ($post_id){
            $art=ma_theme_job_provider_label((string)get_post_meta($post_id,'ma_job_provider_type',true));
            $wer=(string)get_post_meta($post_id,'ma_job_provider',true);
            return $art==='' ? '' : ($wer!=='' && $art==='Personaldienstleister' ? $art.': '.$wer : $art);
        })(),
        'Arbeitsort'=>get_post_meta($post_id,'ma_job_location',true),
        'Adresse'=>get_post_meta($post_id,'ma_job_address',true),
        'Beschäftigungsart'=>ma_theme_job_type_label((string)get_post_meta($post_id,'ma_job_type',true)),
        'Arbeitszeit'=>get_post_meta($post_id,'ma_job_hours',true),
        'Beginn'=>get_post_meta($post_id,'ma_job_start_date',true),
        'Ansprechpartner'=>get_post_meta($post_id,'ma_job_contact',true),
      ];
      $source=(string)get_post_meta($post_id,'ma_job_apply_url',true);
    ?>
    <dl class="service-facts"><?php foreach($fields as $label=>$value): if((string)$value==='') continue; ?><div><dt><?php echo esc_html($label); ?></dt><dd><?php echo esc_html((string)$value); ?></dd></div><?php endforeach; ?></dl>
    <p class="service-verified"><?php echo esc_html(ma_theme_verified_line($post_id)); ?></p>

  <?php elseif($type==='ma_obituary'): ?>
    <?php
      $name=(string)get_post_meta($post_id,'ma_obituary_name',true);
      $birth=(string)get_post_meta($post_id,'ma_obituary_birth',true);
      $death=(string)get_post_meta($post_id,'ma_obituary_death',true);
      $place=(string)get_post_meta($post_id,'ma_obituary_place',true);
      $funeral=(string)get_post_meta($post_id,'ma_obituary_funeral',true);
      $family=(string)get_post_meta($post_id,'ma_obituary_family',true);
    ?>
    <dl class="service-facts service-facts--quiet">
      <?php if($name!==''): ?><div><dt>Name</dt><dd><?php echo esc_html($name); ?></dd></div><?php endif; ?>
      <?php if($birth!==''): ?><div><dt>Geboren</dt><dd><?php echo esc_html($birth); ?></dd></div><?php endif; ?>
      <?php if($death!==''): ?><div><dt>Verstorben</dt><dd><?php echo esc_html($death); ?></dd></div><?php endif; ?>
      <?php if($place!==''): ?><div><dt>Ort</dt><dd><?php echo esc_html($place); ?></dd></div><?php endif; ?>
    </dl>
    <?php if($funeral!==''): ?><div class="service-note"><strong>Trauerfeier / Bestattung</strong><p><?php echo nl2br(esc_html($funeral)); ?></p></div><?php endif; ?>
    <?php if($family!==''): ?><div class="service-note"><strong>Familie</strong><p><?php echo nl2br(esc_html($family)); ?></p></div><?php endif; ?>

  <?php elseif($type==='ma_family_notice'): ?>
    <?php
      $kind=ma_theme_family_kind_label((string)get_post_meta($post_id,'ma_family_kind',true));
      $date=(string)get_post_meta($post_id,'ma_family_date',true);
      $place=(string)get_post_meta($post_id,'ma_family_place',true);
    ?>
    <dl class="service-facts service-facts--quiet">
      <div><dt>Anlass</dt><dd><?php echo esc_html($kind); ?></dd></div>
      <?php if($date!==''): ?><div><dt>Datum</dt><dd><?php echo esc_html($date); ?></dd></div><?php endif; ?>
      <?php if($place!==''): ?><div><dt>Ort</dt><dd><?php echo esc_html($place); ?></dd></div><?php endif; ?>
    </dl>
  <?php endif; ?>

  <?php if($type!=='ma_obituary'): $bild=ma_content_image(null,'full'); ?>
    <figure class="service-detail__image<?php echo $bild['is_fallback']?' service-detail__image--symbol':''; ?>">
      <img src="<?php echo esc_url($bild['url']); ?>" alt="<?php echo esc_attr($bild['alt']); ?>" decoding="async">
      <figcaption><?php echo esc_html(ma_image_caption($bild)); ?></figcaption>
    </figure>
  <?php endif; ?>
  <div class="article-body service-detail__body"><?php the_content(); ?></div>

  <?php if($source!==''): ?>
    <p class="service-action"><a class="read-more" href="<?php echo esc_url($source); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($type==='ma_job'?'Zur Bewerbung':($type==='ma_property'?'Zum Angebot':'Originalquelle öffnen')); ?></a></p>
  <?php endif; ?>
</article>
