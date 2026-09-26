<?php
/**
 * Unternehmen aus der Gemeinde Merzenich.
 *
 * Schlichte Uebersicht nach dem Vorbild "Unternehmen" auf Oberberg Aktuell.
 * Es erscheinen nur Betriebe mit Einwilligung (Sperre im Core-Plugin). Sind es
 * weniger als acht, fuellen gekennzeichnete Anfrage-Plaetze die Flaeche auf -
 * nie erfundene Firmen. Preise werden nicht genannt: Preis auf Anfrage.
 */
get_header();
$max_plaetze=8;
$eintraege=0;
$profil=static function(int $id): array {
    return function_exists('ma_business_profile') ? ma_business_profile($id) : [];
};
?>
<div class="wrap archive-page business-archive">
  <header class="archive-head">
    <div class="eyebrow">Merzenich Aktuell · Wirtschaft</div>
    <h1 class="section-title">Unternehmen aus der Gemeinde</h1>
    <p class="archive-description">Betriebe aus Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald stellen sich vor. Jedes Profil wird mit Einwilligung des Unternehmens veröffentlicht und von der Redaktion geprüft.</p>
  </header>

  <div class="business-grid">
    <?php if(have_posts()): while(have_posts()): the_post(); $eintraege++; $p=$profil((int)get_the_ID()); ?>
      <article class="business-card">
        <?php if(!empty($p['branche']) || !empty($p['ortsteil'])): ?>
          <div class="eyebrow"><?php echo esc_html(implode(' · ',array_filter([$p['branche']??'',$p['ortsteil']??'']))); ?></div>
        <?php endif; ?>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <?php if(has_excerpt()): ?><p><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
        <ul class="business-card__facts">
          <?php if(!empty($p['adresse'])): ?><li><?php echo esc_html($p['adresse']); ?></li><?php endif; ?>
          <?php if(!empty($p['telefon'])): ?><li><a href="<?php echo esc_url($p['telefon_href'],['tel']); ?>"><?php echo esc_html($p['telefon']); ?></a></li><?php endif; ?>
          <?php if(!empty($p['website'])): ?><li><a href="<?php echo esc_url($p['website']); ?>" target="_blank" rel="noopener">Website</a></li><?php endif; ?>
        </ul>
        <a class="read-more" href="<?php the_permalink(); ?>">Profil ansehen</a>
      </article>
    <?php endwhile; endif; ?>

    <?php $frei=is_paged()?0:max(0,$max_plaetze-$eintraege); for($i=0;$i<$frei;$i++): ?>
      <a class="business-card business-card--frei" href="#partner-antrag">
        <span class="eyebrow">Platz frei</span>
        <strong>Ihr Unternehmen hier – Platz anfragen</strong>
        <span>Profil mit Adresse, Öffnungszeiten und Kontakt. Preis auf Anfrage.</span>
      </a>
    <?php endfor; ?>
  </div>

  <?php the_posts_pagination(); ?>

  <?php if(shortcode_exists('ma_partner_antrag')): ?>
    <section class="archive-submit">
      <div class="eyebrow">Unternehmensprofil</div>
      <h2>Platz anfragen</h2>
      <p>Sie möchten Ihr Unternehmen hier vorstellen oder Anzeigen selbst einstellen? Senden Sie uns eine Anfrage. Die Redaktion meldet sich per E-Mail; jede Veröffentlichung wird vorher geprüft. Preis auf Anfrage.</p>
      <?php echo do_shortcode('[ma_partner_antrag typ="unternehmen"]'); ?>
    </section>
  <?php endif; ?>
</div>
<?php get_footer(); ?>
