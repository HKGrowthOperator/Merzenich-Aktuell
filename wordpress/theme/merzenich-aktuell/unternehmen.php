<?php
/* /unternehmen/ wie auf der statischen Seite (deploy/unternehmen.mjs): Karten je zwei
   nebeneinander, Nachladen beim Scrollen, rechts Kanäle, Angebote und Werbung. */
get_header();
global $wp_query;
$anzahl = (int) $wp_query->found_posts;
$assistent = fn($f) => home_url('/anzeigen/aufgeben/?art=Werbung&format=' . rawurlencode($f));
?>
<div class="page-head"><div class="shell"><nav class="crumbs" aria-label="Brotkrumen"><a href="<?php echo esc_url(home_url('/')); ?>">Start</a><span class="sep">›</span><span aria-current="page">Unternehmen</span></nav><h1>Unternehmen</h1><p class="desc">Wirtschaft in Merzenich: Meldungen der Redaktion über Betriebe, Arbeit und Strukturwandel und die Kanäle der Unternehmen aus der Gemeinde. Beiträge von Unternehmen sind als Anzeige gekennzeichnet.</p><p class="count-line"><?php echo $anzahl; ?> Meldung<?php echo $anzahl === 1 ? '' : 'en'; ?> · 0 Unternehmenskanäle</p></div></div>
<section class="section"><div class="shell content-grid"><div class="u-liste">
  <div class="u-karten" data-u-karten><?php $i = 0; while (have_posts()): the_post(); echo ma21_u_karte(get_post(), $i++); endwhile; ?></div>
  <?php if ($anzahl > 8): ?><p class="u-mehr"><button class="btn ghost" type="button" data-u-mehr>Weitere Meldungen laden</button></p><?php endif; ?>
  <?php if (!$anzahl): ?><p class="no-result">Noch keine Unternehmensmeldungen.</p><?php endif; ?>
</div><aside class="sidebar">
  <div class="sidebox u-box"><h3>Unternehmenskanäle</h3><p class="u-leer">Noch kein Unternehmen hat einen eigenen Kanal. Hier erscheinen Unternehmen aus der Gemeinde mit ihren Beiträgen, jeder als Anzeige gekennzeichnet.</p><p><a class="btn" href="<?php echo esc_url($assistent('Unternehmenskanal')); ?>">Eigenen Kanal anfragen</a></p></div>
  <div class="sidebox u-box"><h3>Für Unternehmen</h3><ul class="linklist"><li><a href="<?php echo esc_url(home_url('/werben/')); ?>">Werben &amp; Mediadaten</a></li><li><a href="<?php echo esc_url($assistent('Unternehmenspräsenz')); ?>">Unternehmensporträt</a><small>Porträt mit Bild, Öffnungszeiten und Kontakt</small></li><li><a href="<?php echo esc_url(home_url('/betriebe/')); ?>">Branchenbuch: lokale Betriebe</a><small>einfacher Eintrag kostenlos</small></li><li><a href="<?php echo esc_url(home_url('/jobs/')); ?>">Stellenmarkt</a></li><li><a href="<?php echo esc_url(home_url('/immobilien/')); ?>">Immobilienmarkt</a></li></ul></div>
  <?php echo trim(ma21_vorlage('werbung-unternehmen.html')); ?>
</aside></div></section>
<script src="<?php echo esc_url(home_url('/assets/unternehmen.js')); ?>" defer></script>
<?php get_footer();
