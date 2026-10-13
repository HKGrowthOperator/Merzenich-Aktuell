<?php
/*
 * Musterbeitrag /unternehmen/<muster>/<beitrag>/ (inc/unternehmen.php): aufgebaut
 * wie eine Meldung (single.php), aber überall als Musterbeitrag einer
 * ausgedachten Firma gekennzeichnet. Keine Teilen-Knöpfe, keine Kommentare,
 * nicht in Suchmaschinen. Beiträge echter Firmen sind normale Beiträge.
 */
get_header();
$r = ma21_firmen_anfrage();
$f = $r['firma'];
$b = $f['beitraege'][$r['nr']];
$anfrage = home_url('/anzeigen/aufgeben/?art=Werbung&format=Unternehmenskanal');
$weitere = array_values(array_filter($f['beitraege'], fn($x) => $x['slug'] !== $b['slug']));
?>
<article class="article u-muster-artikel">
<div class="article-head"><div class="shell">
  <nav class="crumbs" aria-label="Brotkrumen"><a href="<?php echo esc_url(home_url('/')); ?>">Start</a><span class="sep">›</span><a href="<?php echo esc_url(home_url('/unternehmen/')); ?>">Unternehmen</a><span class="sep">›</span><a href="<?php echo esc_url($f['url']); ?>"><?php echo esc_html($f['name']); ?></a><span class="sep">›</span><span aria-current="page"><?php echo esc_html($b['titel']); ?></span></nav>
  <div class="kick-row"><div class="location-line"><span class="location-brand">MERZENICH</span><?php if ($f['ort'] !== 'Merzenich') echo ' · ' . esc_html(mb_strtoupper($f['ort'])); ?></div><span class="kicker"><?php echo esc_html($f['name']); ?></span></div>
  <h1><?php echo esc_html($b['titel']); ?></h1>
  <?php if ($b['anriss'] !== ''): ?><p class="dek"><?php echo esc_html($b['anriss']); ?></p><?php endif; ?>
  <p class="gesponsert-hinweis" data-nosnippet><span class="gesponsert">Musterbeitrag</span> Ausgedachtes Beispiel eines Unternehmensbeitrags: kein echtes Unternehmen, kein echtes Angebot.</p>
  <div class="byline">
    <?php echo ma21_firma_logo_html($f, 'avatar'); ?>
    <span class="who"><b><a href="<?php echo esc_url($f['url']); ?>"><?php echo esc_html($f['name']); ?></a></b><span>Musterbeitrag · <?php echo esc_html($f['zeile']); ?></span></span>
  </div>
</div></div>
<div class="shell article-grid">
  <div class="article-body" data-readable>
  <?php if ($b['bild']): ?>
    <figure class="art-figure art-figure--symbol"><div class="media"><?php echo ma21_firmen_bild_html($b['bild'], '(max-width: 1099px) 100vw, 720px', true); ?></div><figcaption><span><span class="figure-badge">Symbolfoto</span> · <?php echo esc_html($b['bild']['alt']); ?></span><span><?php echo ma21_muster_nachweis($b['bild'], 'Foto: '); ?></span></figcaption></figure>
  <?php endif; ?>
    <div class="prose"><?php foreach ($b['absaetze'] as $absatz) echo '<p>' . esc_html((string) $absatz) . '</p>'; ?></div>
    <?php echo ma21_firmen_anzeige_html($f, 'band'); ?>
    <div class="author-box" data-nosnippet><?php echo ma21_firma_logo_html($f, 'avatar'); ?><div class="b"><b>Musterbeitrag von <?php echo esc_html($f['name']); ?></b><p>So erscheinen Beiträge von Unternehmen auf Merzenich Aktuell: als Anzeige gekennzeichnet, getrennt von den Nachrichten der Redaktion und vor der Veröffentlichung geprüft. Diese Firma und dieser Beitrag sind ausgedacht.</p>
      <p class="cta-row"><a class="btn" href="<?php echo esc_url($f['url']); ?>">Zum Unternehmenskanal</a><a class="btn ghost" href="<?php echo esc_url($anfrage); ?>">Eigenen Unternehmenskanal anfragen</a></p></div></div>
  </div>
  <aside class="sidebar"><h2 class="sr-only">Weitere Inhalte</h2>
    <?php if ($weitere): ?><div class="sidebox"><h3>Mehr von <?php echo esc_html($f['name']); ?><a href="<?php echo esc_url($f['url']); ?>">Kanal</a></h3><ul class="linklist"><?php foreach ($weitere as $w): ?><li><a href="<?php echo esc_url($w['url']); ?>"><?php echo esc_html($w['titel']); ?></a><small>Musterbeitrag</small></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php echo ma21_firmen_kacheln_html(ma21_alle_firmen(), 'Weitere Unternehmenskanäle', $f['slug']); ?>
  </aside>
</div>
</article>
<?php get_footer();
