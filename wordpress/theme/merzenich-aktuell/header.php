<!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><?php wp_head(); ?></head><body <?php body_class(); ?>><?php wp_body_open(); ?>
<header class="site-header">
  <div class="wrap header-main">
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>">Merzenich <span>Aktuell</span></a>
    <form class="header-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>"><input type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="Ort, Thema, Nachricht …" aria-label="Suche"></form>
    <div class="header-meta"><?php $w=function_exists('ma_get_weather')?ma_get_weather():null; if($w): ?><span class="weather"><span><?php echo esc_html($w['icon']); ?></span><strong><?php echo esc_html($w['temp']); ?> °C</strong></span><?php endif; ?><span class="clock" data-ma-clock><?php echo esc_html(wp_date('H:i')); ?> Uhr</span><span class="date-long" data-ma-date><?php echo esc_html(wp_date('D., d.m.')); ?></span></div>
  </div>
  <div class="nav-row" data-ma-nav>
    <div class="wrap nav-inner"><button class="mobile-toggle" type="button" data-ma-menu aria-expanded="false">Menü</button><?php ma_theme_primary_nav(); ?><details class="more"><summary>Mehr</summary><div class="more-menu"><?php foreach(['Kontakt'=>'/kontakt/','Anzeigen'=>'/anzeigen/','Werben & Mediadaten'=>'/werben/','Unterstützen'=>'/unterstuetzen/','Archiv'=>'/archiv/','Über uns'=>'/ueber-uns/','Redaktion'=>'/redaktion/','Grundsätze'=>'/grundsaetze/','KI & Redaktion'=>'/ki-redaktion/'] as $l=>$u): ?><a href="<?php echo esc_url(home_url($u)); ?>"><?php echo esc_html($l); ?></a><?php endforeach; ?></div></details></div>
    <div class="sport-mega" data-ma-sport-mega aria-hidden="true">
      <div class="wrap sport-mega__inner">
        <div><span class="sport-mega__eyebrow">Sport in Merzenich</span><strong>Vereine, Spiele und Ergebnisse</strong><p>Sport bleibt als eigener Bereich gebündelt und erscheint nicht im Nachrichtenstrom der Startseite.</p></div>
        <nav aria-label="Sport Untermenü">
          <a href="<?php echo esc_url(home_url('/sport/')); ?>"><strong>Alle Sportmeldungen</strong><span>Nachrichten und Spielberichte</span></a>
          <a href="<?php echo esc_url(home_url('/sc-1919-merzenich/')); ?>"><strong>SC 1919 Merzenich</strong><span>Vereinskanal, Tabelle und Spielplan</span></a>
          <a href="<?php echo esc_url(home_url('/vereine/')); ?>"><strong>Vereine</strong><span>Sportvereine und Vereinsleben</span></a>
          <a href="<?php echo esc_url(home_url('/meldung-senden/')); ?>"><strong>Sportmeldung senden</strong><span>Zur Prüfung an die Redaktion</span></a>
        </nav>
      </div>
    </div>
  </div>
</header><main id="content">
