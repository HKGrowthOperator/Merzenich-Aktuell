<?php
/**
 * Stellenmarkt: chronologisch, neueste zuerst.
 *
 * @package MerzenichAktuell
 */

get_header();
$ma8_jobs = get_posts(
	array(
		'post_type'      => 'ma_job',
		'posts_per_page' => 200,
		'meta_key'       => 'ma8_job_posted',
		'orderby'        => 'meta_value',
		'order'          => 'DESC',
	)
);
?>
<main id="main">
<div class="page-head"><div class="shell">
	<nav class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">&rsaquo;</span><span><?php esc_html_e( 'Jobs', 'merzenich-aktuell' ); ?></span></nav>
	<span class="eyebrow"><?php esc_html_e( 'Stellenmarkt', 'merzenich-aktuell' ); ?></span>
	<h1><?php esc_html_e( 'Jobs in Merzenich und Umgebung', 'merzenich-aktuell' ); ?></h1>
	<p class="desc"><?php esc_html_e( 'Stellen, Ausbildungsplätze und Minijobs von Betrieben, Gemeinde, Kitas und Vereinen aus der Gemeinde Merzenich. Anzeigen sind gekennzeichnet; Ehrenamt und gemeinnützige Stellen sind kostenlos.', 'merzenich-aktuell' ); ?></p>
	<p class="count-line"><?php printf( esc_html( _n( '%d Stelle', '%d Stellen', count( $ma8_jobs ), 'merzenich-aktuell' ) ), count( $ma8_jobs ) ); ?> · <a href="<?php echo esc_url( home_url( '/werben/' ) ); ?>"><?php esc_html_e( 'Stelle inserieren', 'merzenich-aktuell' ); ?></a></p>
</div></div>

<section class="section"><div class="shell">
	<div class="content-grid">
		<div class="event-list">
			<h2 class="sr-only"><?php esc_html_e( 'Stellenangebote', 'merzenich-aktuell' ); ?></h2>
			<?php if ( $ma8_jobs ) : ?>
				<?php foreach ( $ma8_jobs as $ma8_job ) : ?>
					<?php
					$ma8_company = get_post_meta( $ma8_job->ID, 'ma8_job_company', true );
					$ma8_art     = get_post_meta( $ma8_job->ID, 'ma8_job_art', true );
					$ma8_posted  = get_post_meta( $ma8_job->ID, 'ma8_job_posted', true );
					$ma8_ts      = $ma8_posted ? strtotime( $ma8_posted ) : false;
					$d           = ma8_district( $ma8_job->ID );
					?>
					<article class="event-row job-row">
						<span class="d job-d"><b><?php echo esc_html( $ma8_art ? mb_substr( $ma8_art, 0, 9 ) : __( 'Job', 'merzenich-aktuell' ) ); ?></b></span>
						<div class="info">
							<span class="eyebrow"><?php echo esc_html( $ma8_company ); ?><?php echo $d ? ' · ' . esc_html( $d ) : ''; ?></span>
							<h3><a href="<?php echo esc_url( get_permalink( $ma8_job ) ); ?>"><?php echo esc_html( get_the_title( $ma8_job ) ); ?></a></h3>
							<div class="meta">
								<?php if ( $ma8_ts ) : ?><time datetime="<?php echo esc_attr( wp_date( DATE_W3C, $ma8_ts ) ); ?>"><?php printf( esc_html__( 'Veröffentlicht %s', 'merzenich-aktuell' ), esc_html( wp_date( 'j. F Y', $ma8_ts ) ) ); ?></time><?php endif; ?>
							</div>
							<p style="font-size:13.5px;color:var(--ink-soft);margin-top:5px"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $ma8_job ) ), 24, ' …' ) ); ?></p>
						</div>
						<div class="act">
							<a href="<?php echo esc_url( get_permalink( $ma8_job ) ); ?>"><?php esc_html_e( 'Details →', 'merzenich-aktuell' ); ?></a>
						</div>
					</article>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="empty">
					<?php
					printf(
						/* translators: %s: Link zur Werbeseite. */
						esc_html__( 'Noch keine Stellen eingetragen. Betriebe, Gemeinde und Vereine aus Merzenich inserieren hier; gemeinnützige Stellen und Ehrenamt kostenlos. %s', 'merzenich-aktuell' ),
						'<a href="' . esc_url( home_url( '/werben/' ) ) . '">' . esc_html__( 'Stelle inserieren', 'merzenich-aktuell' ) . '</a>'
					);
					?>
				</p>
			<?php endif; ?>
		</div>
		<aside class="sidebar">
			<?php ma8_box_events( 5 ); ma8_box_service(); ?>
		</aside>
	</div>
</div></section>
</main>
<?php
get_footer();
