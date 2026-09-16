<?php
/**
 * Einzelne Stellenanzeige.
 *
 * @package MerzenichAktuell
 */

get_header();

while ( have_posts() ) :
	the_post();
	$ma8_id        = get_the_ID();
	$ma8_company   = get_post_meta( $ma8_id, 'ma8_job_company', true );
	$ma8_art       = get_post_meta( $ma8_id, 'ma8_job_art', true );
	$ma8_posted    = get_post_meta( $ma8_id, 'ma8_job_posted', true );
	$ma8_valid     = get_post_meta( $ma8_id, 'ma8_job_valid_until', true );
	$ma8_start     = get_post_meta( $ma8_id, 'ma8_job_start', true );
	$ma8_sponsored = get_post_meta( $ma8_id, 'ma8_job_sponsored', true );

	// JobPosting-Schema.org (siehe jobLd() in der Referenzimplementierung).
	$ma8_employment_map = array(
		'Vollzeit'   => 'FULL_TIME',
		'Teilzeit'   => 'PART_TIME',
		'Ausbildung' => 'INTERN',
		'Minijob'    => 'PART_TIME',
		'Praktikum'  => 'INTERN',
		'Ehrenamt'   => 'VOLUNTEER',
	);
	$ma8_district_name  = ma8_district( $ma8_id );
	$ma8_posted_ts      = $ma8_posted ? strtotime( $ma8_posted ) : false;
	$ma8_valid_ts       = $ma8_valid ? strtotime( $ma8_valid ) : false;
	$ma8_job_ld         = array(
		'@context'            => 'https://schema.org',
		'@type'               => 'JobPosting',
		'title'               => get_the_title( $ma8_id ),
		'description'         => wp_strip_all_tags( get_the_content( null, false, $ma8_id ) ),
		'datePosted'          => $ma8_posted_ts ? wp_date( 'c', $ma8_posted_ts ) : wp_date( 'c', get_post_time( 'U', true, $ma8_id ) ),
		'employmentType'      => isset( $ma8_employment_map[ $ma8_art ] ) ? $ma8_employment_map[ $ma8_art ] : 'OTHER',
		'hiringOrganization'  => array(
			'@type' => 'Organization',
			'name'  => $ma8_company ? $ma8_company : get_bloginfo( 'name' ),
		),
		'jobLocation'         => array(
			'@type'   => 'Place',
			'address' => array(
				'@type'           => 'PostalAddress',
				'addressLocality' => $ma8_district_name ? $ma8_district_name : 'Merzenich',
				'postalCode'      => '52399',
				'addressRegion'   => 'NRW',
				'addressCountry'  => 'DE',
			),
		),
	);
	if ( $ma8_valid_ts ) {
		$ma8_job_ld['validThrough'] = wp_date( 'c', $ma8_valid_ts );
	}
	?>
<main id="main">
<article>
<div class="article-head"><div class="shell">
	<nav class="crumbs">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">&rsaquo;</span>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'ma_job' ) ); ?>"><?php esc_html_e( 'Jobs', 'merzenich-aktuell' ); ?></a>
	</nav>
	<span class="kicker"><?php echo esc_html( $ma8_art ? $ma8_art : __( 'Stelle', 'merzenich-aktuell' ) ); ?><?php echo $ma8_district_name ? '<span class="dist">' . esc_html( $ma8_district_name ) . '</span>' : ''; ?><?php echo $ma8_sponsored ? '<span class="fbadge anzeige">' . esc_html__( 'Anzeige', 'merzenich-aktuell' ) . '</span>' : ''; ?></span>
	<h1><?php the_title(); ?></h1>
	<p class="dek"><?php echo esc_html( $ma8_company ); ?></p>
</div></div>

<div class="shell article-grid">
	<div class="article-body">
		<div class="facts">
			<h2><?php esc_html_e( 'Stelle im Überblick', 'merzenich-aktuell' ); ?></h2>
			<ul>
				<li><?php printf( esc_html__( 'Betrieb: %s', 'merzenich-aktuell' ), esc_html( $ma8_company ) ); ?></li>
				<?php if ( $ma8_art ) : ?><li><?php printf( esc_html__( 'Art: %s', 'merzenich-aktuell' ), esc_html( $ma8_art ) ); ?></li><?php endif; ?>
				<?php if ( $ma8_posted_ts ) : ?><li><?php printf( esc_html__( 'Veröffentlicht: %s', 'merzenich-aktuell' ), esc_html( wp_date( 'j. F Y', $ma8_posted_ts ) ) ); ?></li><?php endif; ?>
				<?php if ( $ma8_valid_ts ) : ?><li><?php printf( esc_html__( 'Bewerbungsschluss: %s', 'merzenich-aktuell' ), esc_html( wp_date( 'j. F Y', $ma8_valid_ts ) ) ); ?></li><?php endif; ?>
				<?php if ( $ma8_start ) : ?><li><?php printf( esc_html__( 'Beginn: %s', 'merzenich-aktuell' ), esc_html( $ma8_start ) ); ?></li><?php endif; ?>
				<?php if ( $ma8_district_name ) : ?><li><?php printf( esc_html__( 'Ortsteil: %s', 'merzenich-aktuell' ), esc_html( $ma8_district_name ) ); ?></li><?php endif; ?>
			</ul>
		</div>

		<div class="prose"><?php the_content(); ?></div>

		<script type="application/ld+json"><?php echo wp_json_encode( $ma8_job_ld ); ?></script>
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
