<?php
/* Seite wie /impressum/ der statischen Seite: Seitenkopf, darunter Fließtext. */
get_header();
while (have_posts()): the_post(); $p = get_post(); ?>
<div class="page-head"><div class="shell"><nav class="crumbs" aria-label="Brotkrumen"><a href="<?php echo esc_url(home_url('/')); ?>">Start</a><span class="sep">›</span><span aria-current="page"><?php the_title(); ?></span></nav><?php $eyebrow = get_post_meta($p->ID, 'ma_eyebrow', true); if ($eyebrow): ?><span class="eyebrow"><?php echo esc_html($eyebrow); ?></span><?php endif; ?><h1><?php the_title(); ?></h1><?php if (has_excerpt()): ?><p class="desc"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?></div></div>
<section class="section"><div class="shell"><div class="narrow"><div class="info-prose"><?php the_content(); ?></div></div></div></section>
<?php endwhile; get_footer();
