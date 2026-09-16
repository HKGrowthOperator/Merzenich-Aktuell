<?php
/**
 * Einzelner Betrieb.
 *
 * @package MerzenichAktuell
 */

get_header();

while ( have_posts() ) :
	the_post();
	$ma8_id         = get_the_ID();
	$ma8_category   = get_post_meta( $ma8_id, 'ma8_biz_category', true );
	$ma8_website    = get_post_meta( $ma8_id, 'ma8_biz_website', true );
	$ma8_phone      = get_post_meta( $ma8_id, 'ma8_biz_phone', true );
	$ma8_sponsored  = get_post_meta( $ma8_id, 'ma8_biz_sponsored', true );
	$ma8_district_n = ma8_district( $ma8_id );

	// LocalBusiness-Schema.org.
	$ma8_biz_ld = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'LocalBusiness',
		'name'        => get_the_title( $ma8_id ),
		'description' => wp_strip_all_tags( get_the_content( null, false, $ma8_id ) ),
		'address'     => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => $ma8_district_n ? $ma8_district_n : 'Merzenich',
			'postalCode'      => '52399',
			'addressRegion'   => 'NRW',
			'addressCountry'  => 'DE',
		),
	);
	if ( $ma8_phone ) {
		$ma8_biz_ld['telephone'] = $ma8_phone;
	}
	if ( $ma8_website ) {
		$ma8_biz_ld['url'] = $ma8_website;
	}
	?>
<main id="main">
<article>
<div class="article-head"><div class="shell">
	<nav class="crumbs">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">&rsaquo;</span>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'ma_business' ) ); ?>"><?php esc_html_e( 'Betriebe', 'merzenich-aktuell' ); ?></a>
	</nav>
	<span class="kicker"><?php echo esc_html( $ma8_category ? $ma8_category : __( 'Betrieb', 'merzenich-aktuell' ) ); ?><?php echo $ma8_district_n ? '<span class="dist">' . esc_html( $ma8_district_n ) . '</span>' : ''; ?><?php echo $ma8_sponsored ? '<span class="fbadge anzeige">' . esc_html__( 'Anzeige', 'merzenich-aktuell' ) . '</span>' : ''; ?></span>
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

		<div class="facts">
			<h2><?php esc_html_e( 'Auf einen Blick', 'merzenich-aktuell' ); ?></h2>
			<ul class="service">
				<?php if ( $ma8_category ) : ?><li><span class="k"><?php esc_html_e( 'Kategorie', 'merzenich-aktuell' ); ?></span><span class="v"><?php echo esc_html( $ma8_category ); ?></span></li><?php endif; ?>
				<?php if ( $ma8_district_n ) : ?><li><span class="k"><?php esc_html_e( 'Ortsteil', 'merzenich-aktuell' ); ?></span><span class="v"><?php echo esc_html( $ma8_district_n ); ?></span></li><?php endif; ?>
				<?php if ( $ma8_phone ) : ?><li><span class="k"><?php esc_html_e( 'Telefon', 'merzenich-aktuell' ); ?></span><span class="v"><a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $ma8_phone ) ); ?>"><?php echo esc_html( $ma8_phone ); ?></a></span></li><?php endif; ?>
				<?php if ( $ma8_website ) : ?><li><span class="k"><?php esc_html_e( 'Website', 'merzenich-aktuell' ); ?></span><span class="v"><a href="<?php echo esc_url( $ma8_website ); ?>" target="_blank" rel="noopener<?php echo $ma8_sponsored ? ' sponsored' : ''; ?>"><?php echo esc_html( preg_replace( '#^https?://#', '', untrailingslashit( $ma8_website ) ) ); ?> &#8599;</a></span></li><?php endif; ?>
			</ul>
		</div>

		<div class="prose"><?php the_content(); ?></div>

		<script type="application/ld+json"><?php echo wp_json_encode( $ma8_biz_ld ); ?></script>
	</div>
	<aside class="sidebar">
		<?php ma8_box_events( 5 ); ma8_box_service(); ?>
	</aside>
</div>
</article>
</main>
	<?php
endwhile;

get_footer();
