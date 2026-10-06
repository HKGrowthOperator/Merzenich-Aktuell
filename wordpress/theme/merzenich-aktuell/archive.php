<?php
/* Liste wie /blaulicht/ der statischen Seite: Seitenkopf, Aufmacher, Zeilen, Blättern.
   Sport (01.10.2026) wie die statische Sportseite: Spielstand-Ecke, Spiel- und
   Tabellenmodul aus demselben Datenstand (vorlagen/sport-*.html), nach dem
   Aufmacher ein Bildraster und rechts alle Sportvereine der Gemeinde.
   Märkte, Anzeigen, Tipps und Termine haben eigene Vorlagen (archive-ma_*.php). */
get_header();
[$eyebrow, $titel, $desc] = ma21_liste_kopf();
global $wp_query;
$anzahl = (int) $wp_query->found_posts;
$sport = is_category('sport');
// Spielstand aus dem Backend bzw. Repository (inc/sport.php), sonst die Vorlage vom Bautag.
$sportDaten = $sport ? ma21_sport_daten() : null;
$ecke = $sport ? (($sportDaten ? ma21_sport_ecke($sportDaten) : '') ?: trim(ma21_vorlage('sport-ecke.html'))) : '';
?>
<div class="page-head<?php echo $ecke !== '' ? ' mit-ecke' : ''; ?>"><div class="shell"><div class="page-head-text"><nav class="crumbs" aria-label="Brotkrumen"><a href="<?php echo esc_url(home_url('/')); ?>">Start</a><span class="sep">›</span><span aria-current="page"><?php echo esc_html($eyebrow === 'Ort' || $eyebrow === 'Thema' ? $titel : $eyebrow); ?></span></nav><span class="eyebrow"><?php echo esc_html($eyebrow); ?></span><h1><?php echo esc_html($titel); ?></h1><?php if ($desc): ?><p class="desc"><?php echo esc_html($desc); ?></p><?php endif; ?></div><?php echo $ecke; ?></div></div>

<section class="section"><div class="shell">
  <?php if ($sport && !is_paged()) echo ($sportDaten ? ma21_sport_modul($sportDaten) : '') ?: trim(ma21_vorlage('sport-modul.html')); ?>
  <?php if ($sport && !is_paged() && function_exists('ma_sport_vereinsraster')) echo ma_sport_vereinsraster(); ?>
  <div class="content-grid">
    <div class="feed"<?php $seiteKey = ma21_ressort_schluessel(); if ($seiteKey !== '' && !is_paged()) echo ' data-ma-seite="' . esc_attr($seiteKey) . '"'; ?>>
<?php
// Erste Seite nach der Layout-Karte (Aufmacher, Sport-Bildraster, Reihen; feste
// Plätze der Redaktion), weitere Seiten als Reihen. Ohne Layout-Schlüssel
// (Ort, Thema, Suche) wie bisher: erster Beitrag als Aufmacher, Rest Reihen.
global $wp_query;
$posts = array_values(array_filter((array) $wp_query->posts, fn($p) => $p instanceof WP_Post));
echo ma21_feed_html($seiteKey !== '' ? $seiteKey : ($sport ? 'ressort-sport' : 'liste'), $posts, !is_paged());
?>
      <?php the_posts_pagination(['mid_size' => 1, 'prev_text' => '‹ Neuere', 'next_text' => 'Ältere ›']); ?>
    </div>
    <aside class="sidebar"><h2 class="sr-only">Weitere Inhalte</h2><?php echo ma21_liste_seitenspalte(); if ($sport) echo ma21_werbung('sport'); ?></aside>
  </div>
</div></section>
<?php get_footer();
