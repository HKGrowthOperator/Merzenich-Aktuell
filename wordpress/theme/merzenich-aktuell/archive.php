<?php
/* Liste wie /blaulicht/ der statischen Seite: Seitenkopf, Aufmacher, Zeilen, Blättern.
   Märkte, Traueranzeigen, Familienanzeigen und Tipps behalten ihre bisherige Vorlage. */
if (ma21_legacy()) { require __DIR__ . '/archive-alt.php'; return; }
get_header();
[$eyebrow, $titel, $desc] = ma21_liste_kopf();
global $wp_query;
$anzahl = (int) $wp_query->found_posts;
?>
<div class="page-head"><div class="shell"><nav class="crumbs" aria-label="Brotkrumen"><a href="<?php echo esc_url(home_url('/')); ?>">Start</a><span class="sep">›</span><span aria-current="page"><?php echo esc_html($eyebrow === 'Ort' || $eyebrow === 'Thema' ? $titel : $eyebrow); ?></span></nav><span class="eyebrow"><?php echo esc_html($eyebrow); ?></span><h1><?php echo esc_html($titel); ?></h1><?php if ($desc): ?><p class="desc"><?php echo esc_html($desc); ?></p><?php endif; ?><p class="count-line"><?php echo $anzahl; ?> Meldung<?php echo $anzahl === 1 ? '' : 'en'; ?></p></div></div>

<section class="section"><div class="shell">
  <div class="content-grid">
    <div class="feed">
<?php
$i = 0;
if (have_posts()): while (have_posts()): the_post();
    echo ($i === 0 && !is_paged()) ? ma21_feed_lead(get_post()) : ma21_feed_row(get_post());
    echo "\n";
    $i++;
endwhile; else: ?>
      <p class="no-result">Hier gibt es noch keine Meldung. Sobald die erste Meldung vorliegt, steht sie an dieser Stelle.</p>
<?php endif; ?>
      <?php the_posts_pagination(['mid_size' => 1, 'prev_text' => '‹ Neuere', 'next_text' => 'Ältere ›']); ?>
    </div>
    <aside class="sidebar"></aside>
  </div>
</div></section>
<?php get_footer();
