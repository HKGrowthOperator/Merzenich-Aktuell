<?php
/**
 * Ortsteilseite: Meldungen plus Termine des Ortsteils.
 *
 * @package MerzenichAktuell
 */

get_header();
$ma8_term = get_queried_object();
?>
<main id="main">
<div class="page-head"><div class="shell">
	<nav class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">&rsaquo;</span><span><?php echo esc_html( $ma8_term->name ); ?></span></nav>
	<span class="eyebrow"><?php esc_html_e( 'Ortsteil', 'merzenich-aktuell' ); ?></span>
	<h1><?php echo esc_html( $ma8_term->name ); ?></h1>
	<?php if ( $ma8_term->description ) : ?><p class="desc"><?php echo esc_html( $ma8_term->description ); ?></p><?php endif; ?>
	<p class="count-line"><?php printf( esc_html( _n( '%d Meldung', '%d Meldungen', (int) $GLOBALS['wp_query']->found_posts, 'merzenich-aktuell' ) ), (int) $GLOBALS['wp_query']->found_posts ); ?></p>
</div></div>

<section class="section"><div class="shell">
	<div class="content-grid">
		<div class="feed">
			<?php
			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();
					ma8_feed_row( get_the_ID() );
				}
				the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => __( '← Neuere', 'merzenich-aktuell' ), 'next_text' => __( 'Ältere →', 'merzenich-aktuell' ) ) );
			} else {
				echo '<p class="no-result">' . esc_html__( 'Für diesen Ortsteil ist aktuell kein Beitrag veröffentlicht. Verifizierte Termine erscheinen unten.', 'merzenich-aktuell' ) . '</p>';
			}
			?>
		</div>
		<aside class="sidebar">
			<?php
			// Kurzübersicht der letzten Meldungen aus diesem Ortsteil: nur Titel + Link, kein Bild.
			$ma8_kurz = get_posts(
				array(
					'post_type'      => 'post',
					'posts_per_page' => 4,
					'tax_query'      => array( array( 'taxonomy' => 'ma_district', 'field' => 'term_id', 'terms' => $ma8_term->term_id ) ),
				)
			);
			if ( $ma8_kurz ) :
				?>
				<div class="sidebox">
					<h3><?php printf( esc_html__( 'Kurz gemeldet aus %s', 'merzenich-aktuell' ), esc_html( $ma8_term->name ) ); ?></h3>
					<ul class="linklist">
						<?php foreach ( $ma8_kurz as $ma8_short ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $ma8_short ) ); ?>"><?php echo esc_html( get_the_title( $ma8_short ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<?php ma8_box_weather(); ma8_box_events( 5 ); ma8_box_popular( 5 ); ma8_box_service(); ?>
		</aside>
	</div>
</div></section>

<?php
$ma8_events = get_posts(
	array(
		'post_type'      => 'ma_event',
		'posts_per_page' => 4,
		'meta_key'       => 'ma8_event_start',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'tax_query'      => array( array( 'taxonomy' => 'ma_district', 'field' => 'term_id', 'terms' => $ma8_term->term_id ) ),
	)
);
if ( $ma8_events ) :
	?>
	<section class="section"><div class="shell">
		<div class="section-head">
			<div class="left"><span class="eyebrow"><?php esc_html_e( 'Kalender', 'merzenich-aktuell' ); ?></span><h2><?php printf( esc_html__( 'Termine in %s', 'merzenich-aktuell' ), esc_html( $ma8_term->name ) ); ?></h2></div>
			<a class="more" href="<?php echo esc_url( get_post_type_archive_link( 'ma_event' ) ); ?>"><?php esc_html_e( 'Alle Termine', 'merzenich-aktuell' ); ?></a>
		</div>
		<div class="events-grid"><?php foreach ( $ma8_events as $ma8_event ) { ma8_event_card( $ma8_event->ID ); } ?></div>
	</div></section>
<?php endif; ?>
</main>
<?php
get_footer();
