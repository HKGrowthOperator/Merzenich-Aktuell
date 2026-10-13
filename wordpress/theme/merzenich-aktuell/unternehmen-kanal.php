<?php
/* Unternehmenskanal /unternehmen/<firma>/ (inc/unternehmen.php): Profil, Beiträge, Anzeige, weitere Kanäle (Vorbild Oberberg Aktuell, 13.10.2026). */
get_header();
$r = ma21_firmen_anfrage();
$f = $r['firma'];
$anfrage = home_url('/anzeigen/aufgeben/?art=Werbung&format=Unternehmenskanal');
?>
<div class="page-head u-kanal-kopf"><div class="shell">
  <nav class="crumbs" aria-label="Brotkrumen"><a href="<?php echo esc_url(home_url('/')); ?>">Start</a><span class="sep">›</span><a href="<?php echo esc_url(home_url('/unternehmen/')); ?>">Unternehmen</a><span class="sep">›</span><span aria-current="page"><?php echo esc_html($f['name']); ?></span></nav>
  <div class="u-kanal-kopf__zeile">
    <?php echo ma21_firma_logo_html($f, 'u-kanal-kopf__logo'); ?>
    <div>
      <p class="eyebrow">Unternehmenskanal · <?php echo esc_html($f['zeile']); ?></p>
      <h1><?php echo esc_html($f['name']); ?></h1>
      <?php if ($f['text'] !== ''): ?><p class="desc"><?php echo esc_html($f['text']); ?></p><?php endif; ?>
    </div>
  </div>
  <?php if ($f['muster']): ?><p class="u-muster-banner"><strong>Musterprofil.</strong> Dieses Unternehmen ist ausgedacht. Es zeigt, wie ein Unternehmenskanal auf Merzenich Aktuell aussieht: kein echter Betrieb, keine echten Angebote. <a href="<?php echo esc_url($anfrage); ?>">Eigenen Unternehmenskanal anfragen</a></p>
  <?php else: ?><p class="u-kanal-kopf__hinweis">Beiträge dieses Unternehmens sind Anzeigen: Für den Inhalt ist das Unternehmen verantwortlich, die Redaktion prüft jeden Beitrag vor der Veröffentlichung.</p><?php endif; ?>
</div></div>
<section class="section"><div class="shell content-grid"><div class="u-liste">
  <p class="eyebrow">Beiträge von <?php echo esc_html($f['name']); ?></p>
  <?php if ($f['beitraege']): ?>
  <div class="bildraster bildraster--ressort"><?php foreach ($f['beitraege'] as $i => $b) echo ma21_firmen_karte_html($f, $b, $i < 2); ?></div>
  <?php else: ?>
  <p class="u-kanal-leer">Noch keine veröffentlichten Beiträge.</p>
  <?php endif; ?>
</div><aside class="sidebar">
  <div class="sidebox u-box u-kanal-profil"><h3>Profil</h3>
    <dl class="anzeige-fakten"><?php foreach ($f['fakten'] as $label => $wert): ?><div><dt><?php echo esc_html($label); ?></dt><dd><?php echo esc_html((string) $wert); ?></dd></div><?php endforeach; ?></dl>
    <?php if (!$f['muster']): ?><p class="cta-row">
      <?php if (!empty($f['telefon_href'])): ?><a class="btn" href="<?php echo esc_url($f['telefon_href'], ['tel']); ?>">Anrufen</a><?php endif; ?>
      <?php if (!empty($f['website'])): ?><a class="btn ghost" href="<?php echo esc_url($f['website']); ?>" target="_blank" rel="noopener">Website ↗</a><?php endif; ?>
      <?php if ($f['profil'] !== ''): ?><a class="btn ghost" href="<?php echo esc_url($f['profil']); ?>">Eintrag im Branchenbuch</a><?php endif; ?>
    </p><?php endif; ?>
  </div>
  <?php echo ma21_firmen_anzeige_html($f); ?>
  <?php echo ma21_firmen_kacheln_html(ma21_alle_firmen(), 'Weitere Unternehmenskanäle', $f['slug']); ?>
  <div class="sidebox u-box"><h3>Für Unternehmen</h3><p>Ihr Betrieb mit eigenem Kanal, eigenen Beiträgen und Anzeigen: Zugang anfragen, Beiträge selbst schreiben, die Redaktion gibt frei.</p><p><a class="btn block" href="<?php echo esc_url($anfrage); ?>">Unternehmenskanal anfragen</a></p></div>
</aside></div></section>
<?php get_footer();
