<?php
/* Fehlerseite wie /404.html der statischen Seite (04.10.2026). Vorher gab es
   keine eigene Vorlage: eine unbekannte Adresse fiel in archive.php und zeigte
   „Alle Meldungen“ mit leerer Liste und „1 Meldung“. */
get_header();
$neu = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 5]);
?>
<div class="page-head"><div class="shell"><div class="page-head-text"><nav class="crumbs" aria-label="Brotkrumen"><a href="<?php echo esc_url(home_url('/')); ?>">Start</a><span class="sep">›</span><span aria-current="page">Seite nicht gefunden</span></nav><span class="eyebrow">Fehler 404</span><h1>Diese Seite gibt es nicht</h1><p class="desc">Vielleicht wurde die Meldung verschoben oder der Link enthält einen Tippfehler.</p></div></div></div>
<section class="section"><div class="shell"><div class="narrow">
  <form class="search-input" role="search" action="<?php echo esc_url(home_url('/')); ?>"><input type="search" name="s" placeholder="Ort, Thema, Nachricht …" aria-label="Suchbegriff"><button class="btn" type="submit">Suchen</button></form>
  <div class="info-prose">
    <?php if ($neu): ?><h2>Neueste Meldungen</h2><ul><?php foreach ($neu as $p) echo '<li><a href="' . esc_url(get_permalink($p)) . '">' . ma21_e(get_the_title($p)) . '</a></li>'; ?></ul><?php endif; ?>
    <h2>Ressorts</h2><ul><?php foreach (MA21_RESSORT as $slug => $label) echo '<li><a href="' . esc_url(home_url('/' . $slug . '/')) . '">' . ma21_e($label) . '</a></li>'; ?></ul>
    <p><a href="<?php echo esc_url(home_url('/')); ?>">Zur Startseite</a> · <a href="<?php echo esc_url(home_url('/nachrichten/')); ?>">Alle Meldungen</a> · <a href="<?php echo esc_url(home_url('/termine/')); ?>">Termine</a></p>
  </div>
</div></div></section>
<?php get_footer();
