<?php
/**
 * Archiv für Ressorts, Ortsteile, Schlagworte und Autoren.
 *
 * @package MerzenichAktuell
 */

get_header();

$ma8_title = get_the_archive_title();
$ma8_desc  = get_the_archive_description();
$ma8_eyebrow = __( 'Archiv', 'merzenich-aktuell' );
if ( is_category() ) {
	$ma8_eyebrow = __( 'Ressort', 'merzenich-aktuell' );
} elseif ( is_tax( 'ma_district' ) ) {
	$ma8_eyebrow = __( 'Ortsteil', 'merzenich-aktuell' );
}
?>
<main id="main">
<div class="page-head"><div class="shell">
	<nav class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">&rsaquo;</span><span><?php echo esc_html( wp_strip_all_tags( $ma8_title ) ); ?></span></nav>
	<span class="eyebrow"><?php echo esc_html( $ma8_eyebrow ); ?></span>
	<h1><?php echo esc_html( wp_strip_all_tags( $ma8_title ) ); ?></h1>
	<?php if ( $ma8_desc ) : ?><div class="desc"><?php echo wp_kses_post( $ma8_desc ); ?></div><?php endif; ?>
	<p class="count-line"><?php printf( esc_html( _n( '%d Meldung', '%d Meldungen', (int) $GLOBALS['wp_query']->found_posts, 'merzenich-aktuell' ) ), (int) $GLOBALS['wp_query']->found_posts ); ?></p>
</div></div>

<?php if ( is_category( 'sport' ) ) : $ma8_fb = ma8_fussball(); ?>
<section class="section"><div class="shell">
	<div class="section-head">
		<div class="left"><span class="eyebrow"><?php echo esc_html( $ma8_fb['liga'] . ' · Stand ' . wp_date( 'd.m.Y', strtotime( $ma8_fb['abgerufen'] ) ) ); ?></span><h2><?php echo esc_html( $ma8_fb['club'] ); ?></h2></div>
		<?php $ma8_sc = get_page_by_path( 'sc-merzenich' ); ?>
		<a class="more" href="<?php echo esc_url( $ma8_sc ? get_permalink( $ma8_sc ) : home_url( '/sc-merzenich/' ) ); ?>"><?php esc_html_e( 'Vereinsseite', 'merzenich-aktuell' ); ?></a>
	</div>
	<div class="sc-cols">
		<div><?php ma8_fb_table( 8 ); ?></div>
		<div>
			<h3 class="sc-sub"><?php esc_html_e( 'Nächste Spiele', 'merzenich-aktuell' ); ?></h3>
			<ul class="match-list"><?php foreach ( array_slice( $ma8_fb['spielplan'], 0, 4 ) as $ma8_m ) { ma8_fb_match( $ma8_m ); } ?></ul>
		</div>
	</div>
</div></section>
<?php endif; ?>

<section class="section"><div class="shell">
	<div class="content-grid">
		<div class="feed">
			<?php
			if ( have_posts() ) {
				$ma8_i = 0;
				while ( have_posts() ) {
					the_post();
					$ma8_i++;
					ma8_feed_row( get_the_ID() );
					// Native Anzeige nach dem vierten Eintrag, mitten im Feed statt nur am Rand.
					if ( 4 === $ma8_i ) {
						ma8_native_ad( (int) floor( $ma8_i / 4 ) );
					}
				}
			} else {
				echo '<p class="no-result">' . esc_html__( 'Für diesen Bereich ist aktuell keine Meldung veröffentlicht. Verifizierte Termine und Quellen erscheinen separat.', 'merzenich-aktuell' ) . '</p>';
			}
			?>
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 2,
					'prev_text' => __( '← Neuere', 'merzenich-aktuell' ),
					'next_text' => __( 'Ältere →', 'merzenich-aktuell' ),
				)
			);
			?>
		</div>
		<aside class="sidebar">
			<?php
			if ( is_category( 'sport' ) ) {
				ma8_box_sc();
			}
			ma8_box_weather();
			ma8_box_events( 5 );
			ma8_box_popular( 5 );
			ma8_box_service();
			ma8_ad( 'archive_mid' );
			?>
		</aside>
	</div>
</div></section>
</main>
<?php
get_footer();
