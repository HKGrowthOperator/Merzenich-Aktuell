<?php
/**
 * Archiv für Ressorts, Ortsteile, Schlagworte und Autoren.
 *
 * @package MerzenichAktuell
 */

get_header();

$ma8_title   = sprintf( __( 'Suche nach „%s“', 'merzenich-aktuell' ), get_search_query() );
$ma8_desc    = '';
$ma8_eyebrow = __( 'Archiv', 'merzenich-aktuell' );
?>
<main id="main">
<div class="page-head"><div class="shell">
	<nav class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">&rsaquo;</span><span><?php echo esc_html( wp_strip_all_tags( $ma8_title ) ); ?></span></nav>
	<span class="eyebrow"><?php echo esc_html( $ma8_eyebrow ); ?></span>
	<h1><?php echo esc_html( wp_strip_all_tags( $ma8_title ) ); ?></h1>
	<div class="search-wrap">
		<form class="search-input" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Zum Beispiel Feuerwehr, Ortsfest, LEADER', 'merzenich-aktuell' ); ?>" aria-label="<?php esc_attr_e( 'Suchbegriff', 'merzenich-aktuell' ); ?>">
			<button class="btn" type="submit"><?php esc_html_e( 'Suchen', 'merzenich-aktuell' ); ?></button>
		</form>
	</div>
	<p class="count-line"><?php printf( esc_html( _n( '%d Treffer', '%d Treffer', (int) $GLOBALS['wp_query']->found_posts, 'merzenich-aktuell' ) ), (int) $GLOBALS['wp_query']->found_posts ); ?></p>
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
			} else {
				echo '<p class="no-result">' . esc_html__( 'Keine Treffer. Versuchen Sie ein anderes Stichwort, zum Beispiel Feuerwehr, Ortsfest oder LEADER.', 'merzenich-aktuell' ) . '</p>';
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
