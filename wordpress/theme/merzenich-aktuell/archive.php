<?php
/* Liste wie /blaulicht/ der statischen Seite: Seitenkopf, Aufmacher, Zeilen, Blättern.
   Sport (01.10.2026) wie die statische Sportseite: Spielstand-Ecke, Spiel- und
   Tabellenmodul aus demselben Datenstand (vorlagen/sport-*.html), nach dem
   Aufmacher ein Bildraster und rechts alle Sportvereine der Gemeinde.
   Märkte, Traueranzeigen, Familienanzeigen und Tipps behalten ihre bisherige Vorlage. */
if (ma21_legacy()) { require __DIR__ . '/archive-alt.php'; return; }
get_header();
[$eyebrow, $titel, $desc] = ma21_liste_kopf();
global $wp_query;
$anzahl = (int) $wp_query->found_posts;
$sport = is_category('sport');
$ecke = $sport ? trim(ma21_vorlage('sport-ecke.html')) : '';
?>
<div class="page-head<?php echo $ecke !== '' ? ' mit-ecke' : ''; ?>"><div class="shell"><div class="page-head-text"><nav class="crumbs" aria-label="Brotkrumen"><a href="<?php echo esc_url(home_url('/')); ?>">Start</a><span class="sep">›</span><span aria-current="page"><?php echo esc_html($eyebrow === 'Ort' || $eyebrow === 'Thema' ? $titel : $eyebrow); ?></span></nav><span class="eyebrow"><?php echo esc_html($eyebrow); ?></span><h1><?php echo esc_html($titel); ?></h1><?php if ($desc): ?><p class="desc"><?php echo esc_html($desc); ?></p><?php endif; ?><p class="count-line"><?php echo $anzahl; ?> Meldung<?php echo $anzahl === 1 ? '' : 'en'; ?></p></div><?php echo $ecke; ?></div></div>

<section class="section"><div class="shell">
  <?php if ($sport && !is_paged()) echo trim(ma21_vorlage('sport-modul.html')); ?>
  <?php if ($sport && !is_paged() && function_exists('ma_sport_vereinsraster')) echo ma_sport_vereinsraster(); ?>
  <div class="content-grid">
    <div class="feed">
<?php
$i = 0; $raster = [];
if (have_posts()): while (have_posts()): the_post();
    $p = get_post();
    if ($i === 0 && !is_paged()) echo ma21_feed_lead($p);
    // Sport: die nächsten Meldungen mit Bild als Bildraster (gleich hohe Karten).
    elseif ($sport && !is_paged() && count($raster) < 6 && ma21_echtes_bild(ma21_bild($p))) $raster[] = $p;
    else { if ($raster) { echo ma21_bildraster($raster); $raster = []; } echo ma21_feed_row($p); }
    echo "\n";
    $i++;
endwhile;
    echo $raster ? ma21_bildraster($raster) : "";
else: ?>
      <p class="no-result">Hier gibt es noch keine Meldung. Sobald die erste Meldung vorliegt, steht sie an dieser Stelle.</p>
<?php endif; ?>
      <?php the_posts_pagination(['mid_size' => 1, 'prev_text' => '‹ Neuere', 'next_text' => 'Ältere ›']); ?>
    </div>
    <aside class="sidebar"><h2 class="sr-only">Weitere Inhalte</h2><?php echo ma21_liste_seitenspalte(); ?></aside>
  </div>
</div></section>
<?php get_footer();
