<?php
/* Vereinsprofil (/vereine/<slug>/) im Markup der statischen Vereinsseite
   (chatgpt-site/vereine/sc-1919-merzenich/). Daten aus dem Plugin
   (includes/vereine.php): Verzeichnisangaben, vom Verein vorgeschlagene und von
   der Redaktion freigegebene Ergänzungen, Meldungen und Termine des Vereins. */
get_header();
while (have_posts()): the_post();
    $p = get_post();
    $kurz = (string) get_post_meta($p->ID, 'ma_verein_kurz', true);
    $ort = ma21_ort($p);
    $hatOrt = (bool) get_the_terms($p->ID, 'ma_location');
    $kat = (string) get_post_meta($p->ID, 'ma_club_kategorie', true);
    $sport = (string) get_post_meta($p->ID, 'ma_club_sportarten', true);
    $web = (string) get_post_meta($p->ID, 'ma_club_website', true);
    $logo = (string) get_post_meta($p->ID, 'ma_club_logo', true);
    $b = ma21_bild($p);
    $abteilungen = array_filter(array_map('trim', explode("\n", (string) get_post_meta($p->ID, 'ma_club_abteilungen', true))));
    $fakten = array_filter([
        'Kategorie' => $sport ?: $kat,
        'Ortsteil' => $hatOrt ? MA21_ORTE[$ort] : 'gemeindeweit',
        'Gegründet' => (string) get_post_meta($p->ID, 'ma_club_gegruendet', true),
        'Adresse' => (string) get_post_meta($p->ID, 'ma_club_adresse', true),
        'Kontakt' => (string) get_post_meta($p->ID, 'ma_club_kontakt', true),
        'Telefon' => (string) get_post_meta($p->ID, 'ma_club_telefon', true),
    ]);
    $mail = (string) get_post_meta($p->ID, 'ma_club_email', true);
    $meldungen = $kurz !== '' && function_exists('ma_verein_beitraege') ? ma_verein_beitraege($kurz, 8) : [];
    $termine = [];
    if ($kurz !== '' && function_exists('ma21_kommende_termine')) foreach (ma21_kommende_termine(50) as $t) if ((string) get_post_meta($t->ID, 'ma_verein', true) === $kurz) $termine[] = $t;
    // Galerie: Bilder aus veröffentlichten Meldungen des Vereins mit geprüften Bildrechten.
    $galerie = [];
    foreach ($meldungen as $m) { $mb = ma21_bild($m); if ($mb && ma21_echtes_bild($mb) && get_post_meta($m->ID, 'ma_image_rights_verified', true) === '1') $galerie[$mb['id']] = [$mb, $m]; }
    $stand = (string) get_post_meta($p->ID, 'ma_club_text_stand', true) ?: (string) get_post_meta($p->ID, 'ma_club_geprueft', true);
    ?>
<article class="article entity-page">
<div class="article-head"><div class="shell">
  <nav class="crumbs" aria-label="Brotkrumen"><a href="<?php echo esc_url(home_url('/')); ?>">Start</a><span class="sep">›</span><a href="<?php echo esc_url(home_url('/vereine/')); ?>">Vereine</a><span class="sep">›</span><span aria-current="page"><?php the_title(); ?></span></nav>
  <div class="kick-row"><span class="kicker"><?php echo esc_html($sport ?: $kat ?: 'Verein'); ?><?php if ($hatOrt): ?><span class="dist"><?php echo esc_html(MA21_ORTE[$ort]); ?></span><?php endif; ?></span></div>
  <h1><?php the_title(); ?></h1>
  <?php if (has_excerpt()): ?><p class="dek"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
</div></div>
<div class="shell article-grid">
  <div class="article-body">
    <?php if ($b): ?>
    <figure class="art-figure"><div class="media"><?php echo ma21_img($b, MA21_SIZES['figur'], true); ?></div><?php if ($b['credit']): ?><figcaption><span><?php the_title(); ?></span><span>Bild: <?php echo esc_html($b['credit']); ?></span></figcaption><?php endif; ?></figure>
    <?php elseif ($logo !== ''): ?>
    <figure class="art-figure"><div class="media contain"><img src="<?php echo esc_url($logo); ?>" alt="<?php the_title_attribute(); ?>" loading="eager" decoding="async"></div><figcaption><span><?php the_title(); ?></span><?php $lc = (string) get_post_meta($p->ID, 'ma_club_logo_credit', true); if ($lc !== ''): ?><span>Bild: <?php echo esc_html($lc); ?></span><?php endif; ?></figcaption></figure>
    <?php endif; ?>
    <?php if (trim($p->post_content) !== ''): ?><div class="prose"><?php the_content(); ?></div><?php endif; ?>
    <?php if ($abteilungen): ?>
    <div class="facts"><h2>Abteilungen</h2><ul><?php foreach ($abteilungen as $a): [$name, $url] = array_pad(array_map('trim', explode('|', $a, 2)), 2, ''); ?><li><?php echo $url !== '' ? '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html($name) . '</a>' : esc_html($name); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if ($meldungen): ?>
    <div class="facts"><h2>Meldungen aus dem Verein</h2><ul><?php foreach ($meldungen as $m): ?><li><a href="<?php echo esc_url(get_permalink($m)); ?>"><?php echo esc_html(get_the_title($m)); ?></a> <small><?php echo esc_html(get_the_date('d.m.', $m)); ?></small></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if ($termine): ?>
    <div class="facts"><h2>Termine</h2><ul><?php foreach (array_slice($termine, 0, 6) as $t): $start = (string) get_post_meta($t->ID, 'ma_event_start', true); ?><li><a href="<?php echo esc_url(get_permalink($t)); ?>"><?php echo esc_html(get_the_title($t)); ?></a> <small><?php echo esc_html($start !== '' ? wp_date('d.m. H:i', strtotime($start)) . ' Uhr' : ''); ?></small></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if (count($galerie) >= 2): ?>
    <div class="verein-galerie"><h2>Bilder</h2><div class="verein-galerie__raster"><?php foreach (array_slice($galerie, 0, 6) as [$gb, $gm]): ?><a href="<?php echo esc_url(get_permalink($gm)); ?>"><div class="media"><?php echo ma21_img($gb, '(max-width: 760px) 50vw, 250px', false); ?></div></a><?php endforeach; ?></div></div>
    <?php endif; ?>
    <div class="source-box"><b>Eintrag.</b> Angaben laut Vereinsverzeichnis der Gemeinde Merzenich und Verein<?php echo $stand !== '' ? ', Stand ' . esc_html(wp_date('d.m.Y', strtotime($stand))) : ''; ?>. Vereine mit Redaktionszugang schlagen Änderungen im Backend vor; die Redaktion prüft sie vor der Veröffentlichung. Noch kein Zugang? <a href="<?php echo esc_url(home_url('/meldung-senden/')); ?>">Melden Sie sich bei der Redaktion</a>.</div>
  </div>
  <aside class="sidebar"><h2 class="sr-only">Weitere Inhalte</h2>
    <div class="sidebox"><h3>Auf einen Blick</h3><ul class="service">
      <?php foreach ($fakten as $k => $v): ?><li><span class="k"><?php echo esc_html($k); ?></span><span class="v"><?php echo esc_html($v); ?></span></li><?php endforeach; ?>
      <?php if ($web !== ''): ?><li><span class="k">Website</span><span class="v"><a href="<?php echo esc_url($web); ?>" target="_blank" rel="noopener"><?php echo esc_html(preg_replace('#^https?://#', '', rtrim($web, '/'))); ?></a></span></li><?php endif; ?>
      <?php if ($mail !== '' && is_email($mail)): ?><li><span class="k">E-Mail</span><span class="v"><a href="mailto:<?php echo esc_attr($mail); ?>"><?php echo esc_html($mail); ?></a></span></li><?php endif; ?>
    </ul></div>
    <?php if ($hatOrt): ?><div class="sidebox"><h3><?php echo esc_html(MA21_ORTE[$ort]); ?></h3><a class="btn ghost block" href="<?php echo esc_url(home_url('/' . $ort . '/')); ?>">Zur Ortsteilseite</a></div><?php endif; ?>
    <div class="sidebox dark"><h3>Neues aus dem Verein?</h3><p>Meldung, Termin, Foto vom Fest: Die Redaktion prüft jede Einsendung und veröffentlicht mit Quelle.</p><a class="btn gold block" href="<?php echo esc_url(home_url('/meldung-senden/')); ?>">Meldung senden</a><a class="btn ghost block on-dark" href="<?php echo esc_url(home_url('/termin-melden/')); ?>">Termin melden</a></div>
  </aside>
</div>
</article>
<?php endwhile;
get_footer();
