<?php
/**
 * Branchenbuch: lokale Betriebe.
 *
 * @package MerzenichAktuell
 */

get_header();
$ma8_businesses = get_posts( array( 'post_type' => 'ma_business', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC' ) );
?>
<main id="main">
<div class="page-head"><div class="shell">
	<nav class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">&rsaquo;</span><span><?php esc_html_e( 'Lokale Betriebe', 'merzenich-aktuell' ); ?></span></nav>
	<span class="eyebrow"><?php esc_html_e( 'Branchenbuch', 'merzenich-aktuell' ); ?></span>
	<h1><?php esc_html_e( 'Lokale Betriebe in Merzenich', 'merzenich-aktuell' ); ?></h1>
	<p class="desc"><?php esc_html_e( 'Einkaufen, Handwerk, Gastronomie, Gesundheit und Dienstleistungen aus der Gemeinde Merzenich. Einträge sind kostenlos, hervorgehobene Einträge sind als Anzeige gekennzeichnet.', 'merzenich-aktuell' ); ?></p>
	<p class="count-line"><?php printf( esc_html( _n( '%d Betrieb im Branchenbuch', '%d Betriebe im Branchenbuch', count( $ma8_businesses ), 'merzenich-aktuell' ) ), count( $ma8_businesses ) ); ?> · <a href="<?php echo esc_url( home_url( '/werben/' ) ); ?>"><?php esc_html_e( 'Betrieb eintragen', 'merzenich-aktuell' ); ?></a></p>
</div></div>

<section class="section"><div class="shell">
	<div class="section-head">
		<div class="left"><span class="eyebrow"><?php esc_html_e( 'Branchenbuch', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Alle Betriebe', 'merzenich-aktuell' ); ?></h2></div>
		<a class="more" href="<?php echo esc_url( home_url( '/werben/' ) ); ?>"><?php esc_html_e( 'Betrieb eintragen', 'merzenich-aktuell' ); ?></a>
	</div>
	<?php if ( $ma8_businesses ) : ?>
		<div class="club-grid">
			<?php foreach ( $ma8_businesses as $ma8_biz ) : ?>
				<article class="club-card">
					<span class="cat"><?php echo esc_html( get_post_meta( $ma8_biz->ID, 'ma8_biz_category', true ) ); ?><?php $d = ma8_district( $ma8_biz->ID ); echo $d ? ' · ' . esc_html( $d ) : ''; ?></span>
					<h3><a href="<?php echo esc_url( get_permalink( $ma8_biz ) ); ?>"><?php echo esc_html( get_the_title( $ma8_biz ) ); ?></a></h3>
					<p><?php echo esc_html( wp_trim_words( (string) ma8_dek( $ma8_biz->ID ), 28, ' …' ) ); ?></p>
					<?php $ma8_url = get_post_meta( $ma8_biz->ID, 'ma8_biz_website', true ); ?>
					<?php if ( $ma8_url ) : ?><a class="ext" href="<?php echo esc_url( $ma8_url ); ?>" target="_blank" rel="noopener<?php echo get_post_meta( $ma8_biz->ID, 'ma8_biz_sponsored', true ) ? ' sponsored' : ' nofollow'; ?>"><?php esc_html_e( 'Website ↗', 'merzenich-aktuell' ); ?></a><?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<p class="empty">
			<?php
			printf(
				/* translators: %s: Link zur Werbeseite. */
				esc_html__( 'Branchenbuch ist noch leer. Erste Betriebe aus Merzenich können sich hier eintragen. %s', 'merzenich-aktuell' ),
				'<a href="' . esc_url( home_url( '/werben/' ) ) . '">' . esc_html__( 'Betrieb eintragen', 'merzenich-aktuell' ) . '</a>'
			);
			?>
		</p>
	<?php endif; ?>
</div></section>
</main>
<?php
get_footer();
