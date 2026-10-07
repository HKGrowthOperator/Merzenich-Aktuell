<?php
/* /unternehmen/ wie auf der statischen Seite (deploy/unternehmen.mjs): Karten je zwei
   nebeneinander, Nachladen beim Scrollen, rechts Kanäle, Angebote und Werbung. */
get_header();
global $wp_query;
$anzahl = (int) $wp_query->found_posts;
// Unternehmensprofile (ma_business, /betriebe/<slug>/), nach Ortsteil gruppiert.
$profile = get_posts(['post_type' => 'ma_business', 'post_status' => 'publish', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC']);
$nachOrt = [];
foreach ($profile as $u) { $o = (string) get_post_meta($u->ID, 'ma_business_ortsteil', true) ?: ma21_ort($u); $nachOrt[MA21_ORTE[$o] ?? 'Gemeinde Merzenich'][] = $u; }
ksort($nachOrt);
$assistent = fn($f) => home_url('/anzeigen/aufgeben/?art=Werbung&format=' . rawurlencode($f));
?>
<div class="page-head"><div class="shell"><nav class="crumbs" aria-label="Brotkrumen"><a href="<?php echo esc_url(home_url('/')); ?>">Start</a><span class="sep">›</span><span aria-current="page">Unternehmen</span></nav><h1>Unternehmen</h1><p class="desc">Beiträge der Unternehmen aus der Gemeinde Merzenich, als Anzeige gekennzeichnet. Nachrichten der Redaktion über Betriebe und Arbeit stehen unter <a href="<?php echo esc_url(home_url('/wirtschaft/')); ?>">Wirtschaft</a>.</p></div></div>
<section class="section"><div class="shell content-grid"><div class="u-liste">
  <div class="u-karten" data-u-karten><?php $i = 0; $proFirma = []; while (have_posts()): the_post(); $p = get_post(); $firma = (string) (get_post_meta($p->ID, 'ma_gesponsert_von', true) ?: $p->post_author); $proFirma[$firma] = ($proFirma[$firma] ?? 0) + 1; if ($proFirma[$firma] > 4) continue; echo ma21_u_karte($p, $i++); endwhile; ?></div>
  <?php if ($anzahl > 8): ?><p class="u-mehr"><button class="btn ghost" type="button" data-u-mehr>Weitere Meldungen laden</button></p><?php endif; ?>
  <?php if (!$anzahl): /* Vorgabe Betreiber 05.10.2026: keine leere Seite; hier steht nur Werbung von Unternehmen, also bis dahin Musterbeiträge, eine Musteranzeige und das Angebot (07.10.2026). */ ?>
  <?php echo ma21_musterbeitraege_html($assistent('Unternehmenskanal')); echo ma21_werbung('artikel'); ?>
  <div class="u-angebot">
    <p class="eyebrow">Für Unternehmen aus der Gemeinde</p>
    <h2>Ihr Unternehmen mit eigenen Beiträgen auf Merzenich Aktuell</h2>
    <p>Hier stellen sich Betriebe aus Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald mit eigenen Beiträgen vor: neue Angebote, Aktionen, Eröffnungen, Ausbildungsplätze. Jedes Unternehmen zeigt bis zu vier Beiträge gleichzeitig, klar als Anzeige gekennzeichnet und getrennt von den Nachrichten der Redaktion.</p>
    <ol class="u-schritte">
      <li><strong>Unternehmenskanal anfragen.</strong> Ein kurzes Formular genügt; die Redaktion meldet sich mit Angebot und Zugang.</li>
      <li><strong>Beiträge einreichen.</strong> Text und Foto über den eigenen Zugang. Die Redaktion prüft jeden Beitrag vor der Veröffentlichung.</li>
      <li><strong>Gesehen werden.</strong> Ihre Beiträge stehen hier, in der Suche und auf Ihrer Unternehmensseite.</li>
    </ol>
    <p class="u-angebot__knoepfe"><a class="btn" href="<?php echo esc_url($assistent('Unternehmenskanal')); ?>">Unternehmenskanal anfragen</a> <a class="btn ghost" href="<?php echo esc_url(home_url('/werben/')); ?>">Mediadaten ansehen</a></p>
  </div>
  <?php endif; ?>
</div><aside class="sidebar">
  <?php echo ma21_musterprofile_html(count($profile), $assistent('Unternehmenspräsenz')); ?>
  <?php if ($profile): ?><div class="sidebox u-box"><h3>Unternehmen nach Ortsteil</h3>
  <?php if ($profile): foreach ($nachOrt as $ortName => $liste): ?><p class="u-ort"><strong><?php echo esc_html($ortName); ?></strong></p><ul class="linklist"><?php foreach ($liste as $u): $branche = (string) get_post_meta($u->ID, 'ma_business_branche', true); ?><li><a href="<?php echo esc_url(get_permalink($u)); ?>"><?php echo esc_html(get_the_title($u)); ?></a><?php if ($branche !== ''): ?><small><?php echo esc_html($branche); ?></small><?php endif; ?></li><?php endforeach; ?></ul><?php endforeach;
  endif; ?>
  <p><a class="btn" href="<?php echo esc_url($assistent('Unternehmenskanal')); ?>">Eigenes Profil anfragen</a></p></div><?php endif; ?>
  <div class="sidebox u-box"><h3>Für Unternehmen</h3><ul class="linklist"><li><a href="<?php echo esc_url(home_url('/werben/')); ?>">Werben &amp; Mediadaten</a></li><li><a href="<?php echo esc_url($assistent('Unternehmenspräsenz')); ?>">Unternehmensporträt</a><small>Porträt mit Bild, Öffnungszeiten und Kontakt</small></li><li><a href="<?php echo esc_url(home_url('/betriebe/')); ?>">Branchenbuch: lokale Betriebe</a><small>einfacher Eintrag kostenlos</small></li><li><a href="<?php echo esc_url(home_url('/jobs/')); ?>">Stellenmarkt</a></li><li><a href="<?php echo esc_url(home_url('/immobilien/')); ?>">Immobilienmarkt</a></li></ul></div>
  <?php echo ma21_werbung('unternehmen'); ?>
</aside></div></section>
<script src="<?php echo esc_url(home_url('/assets/unternehmen.js')); ?>" defer></script>
<?php get_footer();
