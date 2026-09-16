<?php
/**
 * Veranstaltungsarchiv: Karten oben, chronologische Liste nach Monaten darunter.
 *
 * @package MerzenichAktuell
 */

get_header();

$ma8_events = get_posts(
	array(
		'post_type'      => 'ma_event',
		'posts_per_page' => 200,
		'meta_key'       => 'ma8_event_start',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_query'     => array(
			array( 'key' => 'ma8_event_start', 'value' => wp_date( 'Y-m-d\TH:i' ), 'compare' => '>=', 'type' => 'DATETIME' ),
		),
	)
);

$ma8_groups = array();
foreach ( $ma8_events as $ma8_event ) {
	$ma8_groups[ wp_date( 'F Y', ma8_event_ts( $ma8_event->ID ) ) ][] = $ma8_event;
}
?>
<main id="main">
<div class="page-head"><div class="shell">
	<nav class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">&rsaquo;</span><span><?php esc_html_e( 'Veranstaltungen', 'merzenich-aktuell' ); ?></span></nav>
	<span class="eyebrow"><?php esc_html_e( 'Kalender', 'merzenich-aktuell' ); ?></span>
	<h1><?php esc_html_e( 'Was in Merzenich ansteht', 'merzenich-aktuell' ); ?></h1>
	<p class="desc"><?php esc_html_e( 'Nur Termine, die aktuell bei Gemeinde, Vereinen oder offiziellen Veranstaltern veröffentlicht sind. Jeder Eintrag nennt seine Quelle.', 'merzenich-aktuell' ); ?></p>
	<p class="count-line"><?php printf( esc_html( _n( '%d verifizierter Termin', '%d verifizierte Termine', count( $ma8_events ), 'merzenich-aktuell' ) ), count( $ma8_events ) ); ?></p>
</div></div>

<?php if ( $ma8_events ) : ?>
	<section class="section"><div class="shell">
		<div class="events-grid">
			<?php foreach ( array_slice( $ma8_events, 0, 4 ) as $ma8_event ) { ma8_event_card( $ma8_event->ID ); } ?>
		</div>
	</div></section>

	<section class="section"><div class="shell">
		<div class="section-head">
			<div class="left"><span class="eyebrow"><?php esc_html_e( 'Alle Termine', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Chronologisch', 'merzenich-aktuell' ); ?></h2></div>
			<a class="more" href="<?php echo esc_url( home_url( '/meldung-senden/' ) ); ?>"><?php esc_html_e( 'Termin melden', 'merzenich-aktuell' ); ?></a>
		</div>
		<div class="content-grid">
			<div class="event-list">
				<?php foreach ( $ma8_groups as $ma8_month => $ma8_list ) : ?>
					<h3 class="month-h"><?php echo esc_html( $ma8_month ); ?></h3>
					<?php foreach ( $ma8_list as $ma8_event ) : $ma8_ts = ma8_event_ts( $ma8_event->ID ); ?>
						<article class="event-row">
							<span class="d"><b><?php echo esc_html( wp_date( 'j', $ma8_ts ) ); ?></b><span><?php echo esc_html( wp_date( 'M', $ma8_ts ) ); ?></span></span>
							<div class="info">
								<h3><a href="<?php echo esc_url( get_permalink( $ma8_event ) ); ?>"><?php echo esc_html( get_the_title( $ma8_event ) ); ?></a></h3>
								<div class="meta">
									<time datetime="<?php echo esc_attr( wp_date( DATE_W3C, $ma8_ts ) ); ?>"><?php echo esc_html( wp_date( 'H:i', $ma8_ts ) ); ?> Uhr</time>
									<span><?php echo esc_html( get_post_meta( $ma8_event->ID, 'ma8_event_location', true ) ); ?></span>
									<span><?php echo esc_html( get_post_meta( $ma8_event->ID, 'ma8_event_organizer', true ) ); ?></span>
								</div>
								<p style="font-size:13.5px;color:var(--ink-soft);margin-top:5px"><?php echo esc_html( wp_trim_words( (string) ma8_dek( $ma8_event->ID ), 24, ' …' ) ); ?></p>
							</div>
							<div class="act">
								<a href="<?php echo esc_url( get_permalink( $ma8_event ) ); ?>"><?php esc_html_e( 'Details →', 'merzenich-aktuell' ); ?></a>
								<?php $ma8_src = get_post_meta( $ma8_event->ID, 'ma8_source_url', true ); ?>
								<?php if ( $ma8_src ) : ?><a href="<?php echo esc_url( $ma8_src ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'Quelle ↗', 'merzenich-aktuell' ); ?></a><?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</div>
			<aside class="sidebar">
				<?php ma8_box_popular( 5 ); ma8_box_service(); ?>
			</aside>
		</div>
	</div></section>
<?php else : ?>
	<section class="section"><div class="shell"><p class="no-result"><?php esc_html_e( 'Aktuell sind keine kommenden Termine eingetragen.', 'merzenich-aktuell' ); ?></p></div></section>
<?php endif; ?>
</main>
<?php
get_footer();
