<?php
/* Meldung wie auf der statischen Seite (deploy/meldungen.mjs, Vorlage einsatz-118-rosspfad). */
get_header();
while (have_posts()): the_post();
    $p = get_post();
    [$ressort, $ressortLabel] = ma21_ressort($p);
    $ort = ma21_ort($p);
    $url = get_permalink($p);
    $titel = get_the_title($p);
    $kicker = get_post_meta($p->ID, 'ma_kicker', true) ?: $ressortLabel;
    $b = ma21_bild($p);
    $fakten = array_filter(array_map('trim', explode(' · ', (string) get_post_meta($p->ID, 'ma_facts', true))));
    $q_url = get_post_meta($p->ID, 'ma_source_url', true);
    $q_name = get_post_meta($p->ID, 'ma_source_publisher', true);
    $q_stand = get_post_meta($p->ID, 'ma_source_checked_at', true);
    $teilen = rawurlencode($url);
    $teilenText = rawurlencode($titel . ' ' . $url);
    ?>
<article class="article" data-meldung="<?php echo esc_attr(substr(md5($p->post_name), 0, 12)); ?>">
<div class="article-head"><div class="shell">
  <nav class="crumbs" aria-label="Brotkrumen"><a href="<?php echo esc_url(home_url('/')); ?>">Start</a><span class="sep">›</span><a href="<?php echo esc_url(home_url("/{$ressort}/")); ?>"><?php echo esc_html($ressortLabel); ?></a><?php if ($ort !== 'merzenich'): ?><span class="sep">›</span><a href="<?php echo esc_url(home_url("/{$ort}/")); ?>"><?php echo esc_html(MA21_ORTE[$ort]); ?></a><?php endif; ?><span class="sep">›</span><span aria-current="page"><?php echo esc_html($titel); ?></span></nav>
  <div class="kick-row"><div class="location-line"><span class="location-brand">MERZENICH</span><?php if ($ort !== 'merzenich') echo ' · ' . esc_html(mb_strtoupper(MA21_ORTE[$ort])); ?></div><span class="kicker"><?php echo esc_html($kicker); ?></span></div>
  <h1><?php echo esc_html($titel); ?></h1>
  <?php if (has_excerpt()): ?><p class="dek"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
  <?php if (function_exists('ma_ist_gesponsert') && ma_ist_gesponsert($p)): $von = (string) get_post_meta($p->ID, 'ma_gesponsert_von', true); ?><p class="gesponsert-hinweis"><span class="gesponsert">Anzeige · Gesponsert</span> Bezahlte Präsentation<?php echo $von !== '' ? ' von ' . esc_html($von) : ''; ?>, kein redaktioneller Beitrag.</p><?php endif; ?>
  <div class="byline">
    <span class="avatar" aria-hidden="true">MA</span>
    <span class="who"><b><a href="<?php echo esc_url(home_url('/redaktion/')); ?>" rel="author">Redaktion Merzenich Aktuell</a></b><span>Lokalredaktion</span></span>
    <span class="dates">Veröffentlicht <?php echo ma21_zeit($p, true); ?><br><span class="readtime"><?php echo ma21_lesezeit($p); ?> Min. Lesezeit</span></span>
  </div>
</div></div>
<div class="shell article-grid">
  <div class="article-body" data-readable>
  <?php if ($b):
      $hinweis = MA21_HINWEIS[$b['typ']] ?? '';
      $symbol = in_array($b['typ'], ['symbol', 'place'], true); ?>
    <figure class="art-figure<?php echo $symbol ? ' art-figure--symbol' : ''; ?><?php echo $b['typ'] === 'place' ? ' art-figure--ortsansicht' : ''; ?>"><div class="media"><?php echo ma21_img($b, '(max-width: 760px) 100vw, 760px', true); ?></div><figcaption><span><?php if ($hinweis): ?><span class="figure-badge"><?php echo esc_html($hinweis); ?></span> · <?php endif; ?><?php echo esc_html($b['alt']); ?><?php echo $symbol ? '. Kein Foto vom Ereignis.' : ''; ?></span><?php if ($b['credit']): ?><span>Bild: <?php echo esc_html($b['credit']); ?></span><?php endif; ?></figcaption></figure>
  <?php endif; ?>
    <div class="share" role="group" aria-label="Teilen">
    <a href="https://api.whatsapp.com/send?text=<?php echo $teilenText; ?>" target="_blank" rel="noopener" aria-label="Per WhatsApp teilen" class="wa"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 00-8.6 15.1L2 22l5.1-1.3A10 10 0 1012 2zm0 2a8 8 0 016.9 12.1l-.3.5.6 2.2-2.3-.6-.5.3A8 8 0 1112 4zm-3.2 4c-.2 0-.5.1-.7.4-.3.3-.8.9-.8 1.9 0 1 .7 2 .8 2.1.1.2 1.4 2.3 3.5 3.1 1.7.7 2.1.6 2.5.5.5 0 1.3-.6 1.5-1.1.2-.6.2-1 .1-1.1l-1.5-.7c-.2-.1-.4-.1-.5.1l-.6.8c-.1.1-.3.2-.5.1-.6-.3-1.2-.6-1.7-1.3-.5-.6-.7-1-.9-1.2-.1-.2 0-.3.1-.4l.4-.5c.1-.2.1-.3 0-.5L9.4 8.3c-.1-.2-.3-.3-.6-.3z"/></svg></a>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $teilen; ?>" target="_blank" rel="noopener" aria-label="Auf Facebook teilen"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 21v-7h2.4l.4-2.8h-2.8V9.4c0-.8.2-1.4 1.4-1.4h1.5V5.4c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.1H8.1V14h2.4v7h3z"/></svg></a>
    <a href="mailto:?subject=<?php echo rawurlencode($titel); ?>&amp;body=<?php echo $teilen; ?>" aria-label="Per E-Mail teilen"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v14H3V5zm2 2v.5l7 4.2 7-4.2V7H5zm0 3v7h14v-7l-7 4.2L5 10z"/></svg></a>
    <button type="button" data-share aria-label="Link kopieren"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.6 13.4a1 1 0 001.4 0l3.5-3.5a2.5 2.5 0 10-3.5-3.5l-1 1 1.4 1.4 1-1a.7.7 0 011 1l-3.5 3.5a1 1 0 000 1.1zm2.8-2.8a1 1 0 00-1.4 0l-3.5 3.5a2.5 2.5 0 103.5 3.5l1-1-1.4-1.4-1 1a.7.7 0 01-1-1l3.5-3.5a1 1 0 000-1.1z"/></svg></button>
    <span class="sr-only" role="status" data-share-status></span>
    <button type="button" data-print aria-label="Drucken"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h12v5H6V3zm-2 6h16a2 2 0 012 2v6h-4v4H6v-4H2v-6a2 2 0 012-2zm4 6v4h8v-4H8z"/></svg></button>
  </div>
  <?php if ($fakten): ?><div class="facts"><h2>Das Wichtigste in Kürze</h2><ul><?php foreach ($fakten as $f) echo '<li>' . esc_html($f) . '</li>'; ?></ul></div><?php endif; ?>
    <div class="prose"><?php the_content(); ?></div>
    <?php echo ma21_werbung('artikel'); ?>
  <?php if ($q_url): ?>
    <div class="source-box"><b>Quelle &amp; Transparenz</b> Grundlage dieser Meldung: <a href="<?php echo esc_url($q_url); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html($q_name ?: parse_url($q_url, PHP_URL_HOST)); ?> ↗</a>.<?php if ($q_stand): ?> <span class="stand">Abgerufen am <?php echo esc_html(wp_date('d.m.Y', strtotime($q_stand))); ?>.</span><?php endif; ?> Die Redaktion gibt nur wieder, was in der Quelle steht. <a href="<?php echo esc_url(home_url('/korrekturen/')); ?>">Fehler melden</a></div>
  <?php endif; ?>
    <div class="tags"><?php foreach ((get_the_tags() ?: []) as $t) printf('<a href="%s" rel="tag">%s</a>', esc_url(get_tag_link($t)), esc_html($t->name));
      if ($ort !== 'merzenich') printf('<a href="%s" rel="tag">%s</a>', esc_url(home_url("/{$ort}/")), esc_html(MA21_ORTE[$ort])); ?></div>
    <div class="author-box"><span class="avatar" aria-hidden="true">MA</span><div class="b"><b><a href="<?php echo esc_url(home_url('/redaktion/')); ?>">Redaktion Merzenich Aktuell</a></b><p>Die Redaktion prüft jede Meldung gegen die Originalquelle, dokumentiert Bildtyp und Bildcredit und ergänzt eigene Einordnung. Kontakt: <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a></p></div></div>
    <div class="cta-row"><a class="btn ghost" href="<?php echo esc_url(home_url('/meldung-senden/')); ?>">Hinweis zu dieser Meldung senden</a><a class="btn ghost" href="<?php echo esc_url(home_url('/korrekturen/')); ?>">Fehler melden</a></div>
    <?php if (comments_open() || get_comments_number()) comments_template(); ?>
  </div>
  <aside class="sidebar"><h2 class="sr-only">Weitere Inhalte</h2></aside>
</div>
</article>
<?php
    $weiter = get_posts(['post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => [$p->ID], 'category_name' => $ressort]);
    if ($weiter): ?>
<section class="section"><div class="shell"><div class="section-head"><div class="left"><span class="eyebrow">Weiterlesen</span><h2>Das passt zum Thema</h2></div><a class="more" href="<?php echo esc_url(home_url("/{$ressort}/")); ?>">Alle Meldungen</a></div><div class="cards-3">
<?php foreach ($weiter as $w) echo ma21_news_card($w); ?>
</div></div></section>
<?php endif;
endwhile;
get_footer();
