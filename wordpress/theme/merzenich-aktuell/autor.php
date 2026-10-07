<?php
/* Autorenseite /autor/<name>/ (Theme 21.16.0, Plugin autoren.php): Name,
   Funktion und Vorstellung, darunter die Meldungen wie in den Listen. Für
   Personen der Redaktion (Tivi, Ordin …) und Partner (Polizei, Gemeinde, Vereine). */
get_header();
$u = function_exists('ma_autor_konto') ? ma_autor_konto((string) get_query_var('ma_autor')) : null;
$au = $u ? ma_autor_anzeige($u) : ['name' => 'Redaktion Merzenich Aktuell', 'funktion' => 'Lokalredaktion', 'bio' => '', 'initialen' => 'MA', 'art' => 'redaktion'];
$verein = $u && function_exists('ma_verein_von_benutzer') && function_exists('ma_verein_profil') ? ma_verein_profil(ma_verein_von_benutzer((int) $u->ID)) : null;
global $wp_query;
$posts = array_values(array_filter((array) $wp_query->posts, fn($p) => $p instanceof WP_Post));
?>
<div class="page-head autor-kopf"><div class="shell"><div class="page-head-text">
  <nav class="crumbs" aria-label="Brotkrumen"><a href="<?php echo esc_url(home_url('/')); ?>">Start</a><span class="sep">›</span><a href="<?php echo esc_url(home_url('/redaktion/')); ?>">Redaktion</a><span class="sep">›</span><span aria-current="page"><?php echo esc_html($au['name']); ?></span></nav>
  <div class="autor-kopf__zeile"><span class="avatar autor-kopf__avatar" aria-hidden="true"><?php echo esc_html($au['initialen']); ?></span>
    <div><span class="eyebrow"><?php echo esc_html($au['funktion']); ?></span><h1><?php echo esc_html($au['name']); ?></h1></div></div>
  <?php if ($au['bio'] !== ''): ?><p class="desc"><?php echo nl2br(esc_html($au['bio'])); ?></p>
  <?php elseif ($au['art'] === 'organisation'): ?><p class="desc">Mitteilungen von <?php echo esc_html($au['name']); ?> auf Merzenich Aktuell. Die Redaktion prüft jeden Beitrag vor der Veröffentlichung.</p>
  <?php else: ?><p class="desc"><?php echo esc_html($au['funktion']); ?> bei Merzenich Aktuell, der Lokalzeitung für die Gemeinde Merzenich.</p><?php endif; ?>
  <p class="autor-kopf__wege"><?php if ($verein && $verein->post_status === 'publish'): ?><a href="<?php echo esc_url(get_permalink($verein)); ?>">Vereinsprofil</a> · <?php endif; ?><a href="<?php echo esc_url(home_url('/redaktion/')); ?>">Redaktion &amp; Kontakt</a> · <a href="<?php echo esc_url(home_url('/grundsaetze/')); ?>">Grundsätze</a></p>
</div></div></div>

<section class="section"><div class="shell">
  <div class="content-grid">
    <div class="feed">
<?php
if ($posts) echo ma21_feed_html('liste', $posts, !is_paged());
else echo '<p class="leer-hinweis">Noch keine veröffentlichten Meldungen.</p>';
the_posts_pagination(['mid_size' => 1, 'prev_text' => '‹ Neuere', 'next_text' => 'Ältere ›']);
?>
    </div>
    <aside class="sidebar"><h2 class="sr-only">Weitere Inhalte</h2><?php echo ma21_liste_seitenspalte(); ?></aside>
  </div>
</div></section>
<?php get_footer();
