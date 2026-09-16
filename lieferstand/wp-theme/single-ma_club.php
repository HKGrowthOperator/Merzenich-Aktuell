<?php
/**
 * Einzelne Veranstaltung.
 *
 * @package MerzenichAktuell
 */

get_header();

while ( have_posts() ) :
	the_post();
	$ma8_id  = get_the_ID();
	$ma8_url = get_post_meta( $ma8_id, 'ma8_club_url', true );
	?>
<main id="main">
<article>
<div class="article-head"><div class="shell">
	<nav class="crumbs">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">&rsaquo;</span>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'ma_club' ) ); ?>"><?php esc_html_e( 'Vereine', 'merzenich-aktuell' ); ?></a>
	</nav>
	<span class="kicker"><?php echo esc_html( get_post_meta( $ma8_id, 'ma8_club_category', true ) ? get_post_meta( $ma8_id, 'ma8_club_category', true ) : __( 'Verein', 'merzenich-aktuell' ) ); ?><?php $d = ma8_district( $ma8_id ); echo $d ? '<span class="dist">' . esc_html( $d ) . '</span>' : ''; ?></span>
	<h1><?php the_title(); ?></h1>
	<?php if ( ma8_dek( $ma8_id ) ) : ?><p class="dek"><?php echo esc_html( ma8_dek( $ma8_id ) ); ?></p><?php endif; ?>
</div></div>

<div class="shell article-grid">
	<div class="article-body">
		<?php if ( ma8_img( $ma8_id ) ) : ?>
			<figure>
				<?php echo ma8_media( $ma8_id, 'ma8-hero', array( 'eager' => true ) ); ?>
				<figcaption>
					<span><span class="figure-badge"><?php echo esc_html( ma8_image_type_label( $ma8_id ) ); ?></span> · <?php echo esc_html( ma8_image_alt( $ma8_id ) ); ?></span>
					<span><?php echo esc_html( get_post_meta( $ma8_id, 'ma8_image_credit', true ) ); ?></span>
				</figcaption>
			</figure>
		<?php endif; ?>

		<?php echo ma8_facts_list( $ma8_id ); ?>

		<div class="prose"><?php the_content(); ?></div>

		<?php if ( $ma8_url ) : ?>
			<div class="source-box">
				<b><?php esc_html_e( 'Offizielle Seite.', 'merzenich-aktuell' ); ?></b>
				<a href="<?php echo esc_url( $ma8_url ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $ma8_url ); ?> &#8599;</a>
			</div>
		<?php endif; ?>
	</div>
	<aside class="sidebar">
		<?php ma8_box_events( 5 ); ?>
	</aside>
</div>
</article>
</main>
	<?php
endwhile;

get_footer();
