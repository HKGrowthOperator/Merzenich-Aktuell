<?php
get_header();
$hero=ma_theme_hero_post();
$market_sections=[
  'ma_property'=>'Immobilien',
  'ma_job'=>'Stellen',
  'ma_obituary'=>'Traueranzeigen',
  'ma_family_notice'=>'Familienanzeigen',
];
?>
<div class="wrap home-grid">
  <aside class="service-column">
    <div class="service-stack">
      <details open>
        <summary>Veranstaltungen</summary>
        <div class="service-body">
          <?php $events=function_exists('ma_upcoming_events')?ma_upcoming_events(6):[]; ?>
          <?php if($events): foreach($events as $event): $ts=ma_event_timestamp($event->ID); ?>
            <a class="event-mini" href="<?php echo esc_url(get_permalink($event)); ?>">
              <span class="event-date"><?php echo $ts?esc_html(wp_date('d M',$ts)):'Termin'; ?></span>
              <span><strong><?php echo esc_html(get_the_title($event)); ?></strong><br><?php echo $ts?esc_html(wp_date('H:i',$ts).' Uhr · '):''; ?><?php echo esc_html((string)get_post_meta($event->ID,'ma_event_place',true)); ?></span>
            </a>
          <?php endforeach; ?>
            <a class="read-more" href="<?php echo esc_url(get_post_type_archive_link('ma_event')); ?>">Alle Veranstaltungen</a>
          <?php else: ?>
            <p class="empty">Aktuell keine freigegebenen Termine.</p>
          <?php endif; ?>
        </div>
      </details>

      <?php $weather=function_exists('ma_get_weather')?ma_get_weather():null; if($weather): ?>
        <details>
          <summary>Wetter</summary>
          <div class="service-body">
            <?php foreach($weather['days'] as $i=>$day): ?>
              <div class="weather-day">
                <span><strong><?php echo $i===0?'Heute':($i===1?'Morgen':'Übermorgen'); ?></strong><br><?php echo esc_html($day['icon'].' '.$day['label']); ?></span>
                <span><?php echo esc_html($day['max'].'° / '.$day['min'].'°'); ?><br><?php echo esc_html($day['rain'].' % Regen'); ?></span>
              </div>
            <?php endforeach; ?>
            <?php if(!empty($weather['stale'])): ?><p class="empty">Letzter gültiger Stand: <?php echo esc_html(wp_date('H:i',strtotime($weather['updated_at']))); ?> Uhr</p><?php endif; ?>
          </div>
        </details>
      <?php endif; ?>

      <?php foreach($market_sections as $post_type=>$label): $items=function_exists('ma_active_market_items')?ma_active_market_items($post_type,3):[]; ?>
        <details>
          <summary><?php echo esc_html($label); ?><?php if($items): ?><span class="summary-count"><?php echo count($items); ?></span><?php endif; ?></summary>
          <div class="service-body">
            <?php if($items): foreach($items as $item): $meta=ma_theme_market_meta($item->ID); ?>
              <a class="market-mini" href="<?php echo esc_url(get_permalink($item)); ?>">
                <strong><?php echo esc_html(get_the_title($item)); ?></strong>
                <?php if($meta): ?><span><?php echo esc_html(implode(' · ',$meta)); ?></span><?php endif; ?>
              </a>
            <?php endforeach; ?>
              <a class="read-more" href="<?php echo esc_url(get_post_type_archive_link($post_type)); ?>">Alle <?php echo esc_html($label); ?></a>
            <?php else: ?>
              <p class="empty">Aktuell keine veröffentlichten Einträge.</p>
            <?php endif; ?>
          </div>
        </details>
      <?php endforeach; ?>
    </div>
  </aside>

  <section class="main-feed">
    <?php if($hero): setup_postdata($GLOBALS['post']=$hero); ?>
      <article class="hero">
        <?php if(has_post_thumbnail()) the_post_thumbnail('full'); ?>
        <div class="eyebrow"><?php echo ma_theme_location_label(); ?> · <?php $category=get_the_category(); echo esc_html($category[0]->name??'Aktuell'); ?></div>
        <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
        <?php if(has_excerpt()): ?><p class="dek"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
        <div class="meta-line">
          <span><?php echo esc_html(get_the_date('d.m.Y · H:i')); ?> Uhr</span>
          <span><?php echo ma_theme_reading_time(); ?> Min. Lesezeit</span>
          <a href="<?php the_permalink(); ?>#comments"><?php echo (int)get_comments_number(); ?> Kommentare</a>
        </div>
        <a class="read-more" href="<?php the_permalink(); ?>">Mehr lesen</a>
      </article>
      <?php wp_reset_postdata(); ?>
    <?php endif; ?>

    <div class="news-list">
      <?php
      $exclude=$hero?[$hero->ID]:[];
      $news=new WP_Query(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>10,'post__not_in'=>$exclude]);
      $index=0;
      while($news->have_posts()):$news->the_post();
        get_template_part('template-parts/card');
        $index++;
        if($index===4) ma_theme_ad('homepage_feed_1');
      endwhile;
      wp_reset_postdata();
      ?>
    </div>
  </section>

  <aside class="home-aside">
    <div class="sidebar-heading">Lokale Anzeigen</div>
    <?php ma_theme_ad('homepage_sidebar_top'); ma_theme_ad('homepage_sidebar_middle'); ?>
    <div class="sidebar-heading">Sport</div>
    <?php echo do_shortcode('[ma_sport]'); ?>
  </aside>
</div>
<?php get_footer(); ?>
