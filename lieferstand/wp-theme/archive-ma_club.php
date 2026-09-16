<?php
/**
 * Vereinsverzeichnis.
 *
 * @package MerzenichAktuell
 */

get_header();
$ma8_clubs = get_posts( array( 'post_type' => 'ma_club', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC' ) );

// A-Z-Register: Vereine nach Anfangsbuchstabe des Titels gruppieren.
$ma8_az_groups  = array();
$ma8_az_letters = array();
foreach ( $ma8_clubs as $ma8_club ) {
	$ma8_letter = mb_strtoupper( mb_substr( get_the_title( $ma8_club ), 0, 1 ) );
	if ( ! isset( $ma8_az_groups[ $ma8_letter ] ) ) {
		$ma8_az_groups[ $ma8_letter ]  = array();
		$ma8_az_letters[]              = $ma8_letter;
	}
	$ma8_az_groups[ $ma8_letter ][] = $ma8_club;
}
?>
<main id="main">
<div class="page-head"><div class="shell">
	<nav class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">&rsaquo;</span><span><?php esc_html_e( 'Vereine', 'merzenich-aktuell' ); ?></span></nav>
	<span class="eyebrow"><?php esc_html_e( 'Vereinsleben', 'merzenich-aktuell' ); ?></span>
	<h1><?php esc_html_e( 'Vereine in der Gemeinde', 'merzenich-aktuell' ); ?></h1>
	<p class="desc"><?php esc_html_e( 'Wer in Merzenich, Golzheim, Girbelsrath, Morschenich und im Bürgewald aktiv ist, mit Ansprechpartnern und offiziellen Seiten.', 'merzenich-aktuell' ); ?></p>
	<p class="count-line"><?php printf( esc_html( _n( '%d Verein im Verzeichnis', '%d Vereine im Verzeichnis', count( $ma8_clubs ), 'merzenich-aktuell' ) ), count( $ma8_clubs ) ); ?></p>
</div></div>

<section class="section"><div class="shell">
	<div class="section-head">
		<div class="left"><span class="eyebrow"><?php esc_html_e( 'Verzeichnis', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Alle Vereine', 'merzenich-aktuell' ); ?></h2></div>
		<a class="more" href="<?php echo esc_url( home_url( '/meldung-senden/' ) ); ?>"><?php esc_html_e( 'Verein melden', 'merzenich-aktuell' ); ?></a>
	</div>
	<?php if ( count( $ma8_az_letters ) > 1 ) : ?>
	<nav class="az" aria-label="<?php esc_attr_e( 'Alphabet', 'merzenich-aktuell' ); ?>">
		<?php foreach ( $ma8_az_letters as $ma8_letter ) : ?>
			<a href="#az-<?php echo esc_attr( $ma8_letter ); ?>"><?php echo esc_html( $ma8_letter ); ?></a>
		<?php endforeach; ?>
	</nav>
	<?php endif; ?>
	<div class="club-grid">
		<?php foreach ( $ma8_az_groups as $ma8_letter => $ma8_group ) : ?>
			<?php foreach ( $ma8_group as $ma8_i => $ma8_club ) : ?>
				<article class="club-card"<?php echo 0 === $ma8_i ? ' id="az-' . esc_attr( $ma8_letter ) . '"' : ''; ?>>
					<span class="cat"><?php echo esc_html( get_post_meta( $ma8_club->ID, 'ma8_club_category', true ) ); ?><?php $d = ma8_district( $ma8_club->ID ); echo $d ? ' · ' . esc_html( $d ) : ''; ?></span>
					<h3><a href="<?php echo esc_url( get_permalink( $ma8_club ) ); ?>"><?php echo esc_html( get_the_title( $ma8_club ) ); ?></a></h3>
					<p><?php echo esc_html( wp_trim_words( (string) ma8_dek( $ma8_club->ID ), 28, ' …' ) ); ?></p>
					<?php $ma8_url = get_post_meta( $ma8_club->ID, 'ma8_club_url', true ); ?>
					<?php if ( $ma8_url ) : ?><a class="ext" href="<?php echo esc_url( $ma8_url ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'Offizielle Seite ↗', 'merzenich-aktuell' ); ?></a><?php endif; ?>
				</article>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</div>
</div></section>

<?php
$ma8_news = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 5, 'category_name' => 'vereine' ) );
if ( $ma8_news ) :
	?>
	<section class="section"><div class="shell">
		<div class="section-head"><div class="left"><span class="eyebrow"><?php esc_html_e( 'Meldungen', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Aus den Vereinen', 'merzenich-aktuell' ); ?></h2></div></div>
		<div class="content-grid">
			<div class="feed"><?php foreach ( $ma8_news as $ma8_item ) { ma8_feed_row( $ma8_item->ID ); } ?></div>
			<aside class="sidebar"><?php ma8_box_events( 5 ); ?></aside>
		</div>
	</div></section>
<?php endif; ?>
</main>
<?php
get_footer();
