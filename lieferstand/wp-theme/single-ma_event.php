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
	$ma8_ts  = ma8_event_ts( $ma8_id );
	$ma8_end = get_post_meta( $ma8_id, 'ma8_event_end', true );

	// Minimale .ics-Datei fürs Kalender-Abo, als data:-URI ausgeliefert (kein Server-Endpunkt nötig).
	$ma8_ics_lines = array(
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//Merzenich Aktuell//Termine//DE',
		'CALSCALE:GREGORIAN',
		'BEGIN:VEVENT',
		'UID:event-' . $ma8_id . '@' . wp_parse_url( home_url(), PHP_URL_HOST ),
		'DTSTAMP:' . wp_date( 'Ymd\THis\Z', time() ),
		'DTSTART:' . wp_date( 'Ymd\THis', $ma8_ts ),
	);
	if ( $ma8_end ) {
		$ma8_end_ts = strtotime( $ma8_end );
		if ( $ma8_end_ts ) {
			$ma8_ics_lines[] = 'DTEND:' . wp_date( 'Ymd\THis', $ma8_end_ts );
		}
	}
	$ma8_ics_escape   = function ( $text ) {
		$text = str_replace( array( '\\', ';', ',' ), array( '\\\\', '\\;', '\\,' ), (string) $text );
		return str_replace( array( "\r\n", "\n" ), '\\n', $text );
	};
	$ma8_ics_lines[] = 'SUMMARY:' . $ma8_ics_escape( get_the_title( $ma8_id ) );
	$ma8_ics_loc     = get_post_meta( $ma8_id, 'ma8_event_location', true );
	if ( $ma8_ics_loc ) {
		$ma8_ics_lines[] = 'LOCATION:' . $ma8_ics_escape( $ma8_ics_loc );
	}
	$ma8_ics_dek = ma8_dek( $ma8_id );
	if ( $ma8_ics_dek ) {
		$ma8_ics_lines[] = 'DESCRIPTION:' . $ma8_ics_escape( wp_strip_all_tags( (string) $ma8_ics_dek ) );
	}
	$ma8_ics_lines[] = 'URL:' . get_permalink( $ma8_id );
	$ma8_ics_lines[] = 'END:VEVENT';
	$ma8_ics_lines[] = 'END:VCALENDAR';
	$ma8_ics         = implode( "\r\n", $ma8_ics_lines ) . "\r\n";
	?>
<main id="main">
<article>
<div class="article-head"><div class="shell">
	<nav class="crumbs">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">&rsaquo;</span>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'ma_event' ) ); ?>"><?php esc_html_e( 'Veranstaltungen', 'merzenich-aktuell' ); ?></a>
	</nav>
	<span class="kicker"><?php echo esc_html( get_post_meta( $ma8_id, 'ma8_event_category', true ) ? get_post_meta( $ma8_id, 'ma8_event_category', true ) : __( 'Termin', 'merzenich-aktuell' ) ); ?><?php $d = ma8_district( $ma8_id ); echo $d ? '<span class="dist">' . esc_html( $d ) . '</span>' : ''; ?></span>
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
			<h2><?php esc_html_e( 'Termin im Überblick', 'merzenich-aktuell' ); ?></h2>
			<ul>
				<li><?php printf( esc_html__( 'Wann: %s Uhr', 'merzenich-aktuell' ), esc_html( wp_date( 'l, j. F Y, H:i', $ma8_ts ) ) ); ?><?php echo $ma8_end ? esc_html( ' bis ' . wp_date( 'H:i', strtotime( $ma8_end ) ) . ' Uhr' ) : ''; ?></li>
				<li><?php printf( esc_html__( 'Wo: %s', 'merzenich-aktuell' ), esc_html( get_post_meta( $ma8_id, 'ma8_event_location', true ) ) ); ?></li>
				<li><?php printf( esc_html__( 'Veranstalter: %s', 'merzenich-aktuell' ), esc_html( get_post_meta( $ma8_id, 'ma8_event_organizer', true ) ) ); ?></li>
			</ul>
		</div>

		<div class="cta-row">
			<a class="btn ghost" href="data:text/calendar;charset=utf-8,<?php echo rawurlencode( $ma8_ics ); ?>" download="termin.ics"><?php esc_html_e( 'Zum Kalender hinzufügen', 'merzenich-aktuell' ); ?></a>
		</div>

		<div class="prose"><?php the_content(); ?></div>

		<?php
		$ma8_src = get_post_meta( $ma8_id, 'ma8_source_url', true );
		if ( $ma8_src ) :
			?>
			<div class="source-box">
				<b><?php esc_html_e( 'Quelle.', 'merzenich-aktuell' ); ?></b>
				<?php esc_html_e( 'Dieser Termin ist veröffentlicht bei', 'merzenich-aktuell' ); ?>
				<a href="<?php echo esc_url( $ma8_src ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( get_post_meta( $ma8_id, 'ma8_source_name', true ) ? get_post_meta( $ma8_id, 'ma8_source_name', true ) : __( 'der Originalquelle', 'merzenich-aktuell' ) ); ?> &#8599;</a>.
				<?php esc_html_e( 'Änderungen sind möglich, im Zweifel beim Veranstalter nachfragen.', 'merzenich-aktuell' ); ?>
			</div>
		<?php endif; ?>
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
