<?php
/**
 * Statische Seite (Impressum, Datenschutz, Werben, Quellen, Redaktion).
 *
 * @package MerzenichAktuell
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
<main id="main">
<div class="page-head"><div class="shell">
	<nav class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">&rsaquo;</span><span><?php the_title(); ?></span></nav>
	<span class="eyebrow"><?php echo esc_html( get_post_meta( get_the_ID(), 'ma8_kicker', true ) ? get_post_meta( get_the_ID(), 'ma8_kicker', true ) : get_bloginfo( 'name' ) ); ?></span>
	<h1><?php the_title(); ?></h1>
	<?php if ( ma8_dek( get_the_ID() ) ) : ?><p class="desc"><?php echo esc_html( ma8_dek( get_the_ID() ) ); ?></p><?php endif; ?>
</div></div>

<section class="section"><div class="shell">
	<div class="info-prose"><?php the_content(); ?></div>
</div></section>
</main>
	<?php
endwhile;

get_footer();
