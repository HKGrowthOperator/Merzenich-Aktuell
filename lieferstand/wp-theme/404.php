<?php
/**
 * Fehlerseite.
 *
 * @package MerzenichAktuell
 */

get_header();
?>
<main id="main">
<div class="page-head"><div class="shell">
	<span class="eyebrow"><?php esc_html_e( 'Fehler 404', 'merzenich-aktuell' ); ?></span>
	<h1><?php esc_html_e( 'Diese Seite gibt es nicht', 'merzenich-aktuell' ); ?></h1>
	<p class="desc"><?php esc_html_e( 'Vielleicht wurde sie verschoben oder der Link ist unvollständig. Diese Wege führen weiter:', 'merzenich-aktuell' ); ?></p>
	<p style="margin-top:18px;display:flex;gap:10px;flex-wrap:wrap">
		<a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Zur Startseite', 'merzenich-aktuell' ); ?></a>
		<a class="btn ghost" href="<?php echo esc_url( get_post_type_archive_link( 'ma_event' ) ); ?>"><?php esc_html_e( 'Termine', 'merzenich-aktuell' ); ?></a>
		<a class="btn ghost" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>"><?php esc_html_e( 'Suche', 'merzenich-aktuell' ); ?></a>
	</p>
</div></div>

<?php
$ma8_latest = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 3 ) );
if ( $ma8_latest ) :
	?>
	<section class="section"><div class="shell">
		<div class="section-head"><div class="left"><span class="eyebrow"><?php esc_html_e( 'Aktuell', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Die neuesten Meldungen', 'merzenich-aktuell' ); ?></h2></div></div>
		<div class="cards-3"><?php foreach ( $ma8_latest as $ma8_item ) { ma8_news_card( $ma8_item->ID ); } ?></div>
	</div></section>
<?php endif; ?>
</main>
<?php
get_footer();
