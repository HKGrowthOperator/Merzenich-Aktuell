<?php
/**
 * Template Name: SC 1919 Merzenich
 *
 * Vereinsseite mit Tabelle, Spielplan und den weiteren Mannschaften.
 * Bewusst nur für den SC 1919 Merzenich, andere Vereine erscheinen nur als Gegner.
 *
 * @package MerzenichAktuell
 */

get_header();

$ma8_fb   = ma8_fussball();
$ma8_logo = get_template_directory_uri() . '/' . $ma8_fb['logo'];
$ma8_news = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 6, 'tag' => 'sc-1919-merzenich' ) );
?>
<main id="main">

<div class="page-head"><div class="shell">
	<nav class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">›</span><span><?php echo esc_html( $ma8_fb['club'] ); ?></span></nav>
	<span class="eyebrow"><?php esc_html_e( 'Vereinsseite', 'merzenich-aktuell' ); ?></span>
	<h1><?php echo esc_html( $ma8_fb['club'] ); ?></h1>
	<p class="desc"><?php printf( esc_html__( 'Tabelle, Spielplan und Mannschaften des Merzenicher Fußballvereins. %1$d Mannschaften, davon %2$d im Juniorenbereich, %3$s, %4$s.', 'merzenich-aktuell' ), (int) $ma8_fb['teamsGesamt'], (int) $ma8_fb['teamsJunioren'], esc_html( $ma8_fb['verband'] ), esc_html( $ma8_fb['kreis'] ) ); ?></p>
	<p class="count-line"><?php printf( esc_html__( 'Stand %s · Quelle FUSSBALL.DE', 'merzenich-aktuell' ), esc_html( wp_date( 'd.m.Y', strtotime( $ma8_fb['abgerufen'] ) ) ) ); ?></p>
</div></div>

<section class="section"><div class="shell">
	<div class="sc-head">
		<div class="sc-crest"><img src="<?php echo esc_url( $ma8_logo ); ?>" alt="<?php esc_attr_e( 'Vereinswappen des SC 1919 Merzenich', 'merzenich-aktuell' ); ?>" width="240" height="240" decoding="async"></div>
		<div class="sc-facts">
			<span class="eyebrow"><?php echo esc_html( $ma8_fb['liga'] . ' · Saison ' . $ma8_fb['saison'] ); ?></span>
			<h2><?php esc_html_e( 'Erste Mannschaft', 'merzenich-aktuell' ); ?></h2>
			<?php ma8_fb_stats(); ?>
			<p class="sc-note"><?php esc_html_e( 'Einzelergebnisse gibt FUSSBALL.DE nur maschinenlesbar geschützt aus. Wir zeigen deshalb Paarungen und Termine; die Ergebnisse stecken in der Tabelle.', 'merzenich-aktuell' ); ?>
			<a href="<?php echo esc_url( $ma8_fb['teamUrl'] ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'Mannschaftsseite bei FUSSBALL.DE ↗', 'merzenich-aktuell' ); ?></a></p>
		</div>
	</div>
</div></section>

<div class="shell"><?php ma8_ad( 'sc_top' ); ?></div>

<section class="section"><div class="shell">
	<div class="section-head">
		<div class="left"><span class="eyebrow"><?php echo esc_html( $ma8_fb['liga'] ); ?></span><h2><?php esc_html_e( 'Tabelle', 'merzenich-aktuell' ); ?></h2></div>
		<a class="more" href="<?php echo esc_url( $ma8_fb['teamUrl'] ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'Bei FUSSBALL.DE ↗', 'merzenich-aktuell' ); ?></a>
	</div>
	<?php ma8_fb_table(); ?>
</div></section>

<section class="section"><div class="shell">
	<div class="sc-cols">
		<div>
			<div class="section-head"><div class="left"><span class="eyebrow"><?php esc_html_e( 'Termine', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Nächste Spiele', 'merzenich-aktuell' ); ?></h2></div></div>
			<ul class="match-list"><?php foreach ( $ma8_fb['spielplan'] as $ma8_m ) { ma8_fb_match( $ma8_m ); } ?></ul>
		</div>
		<div>
			<div class="section-head"><div class="left"><span class="eyebrow"><?php esc_html_e( 'Bereits gespielt', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Saisonauftakt', 'merzenich-aktuell' ); ?></h2></div></div>
			<ul class="match-list past"><?php foreach ( array_reverse( $ma8_fb['gespielt'] ) as $ma8_m ) { ma8_fb_match( $ma8_m ); } ?></ul>
			<p class="sc-note"><?php printf( esc_html__( 'Der SC gewann beide Ligaspiele. Die Einzelergebnisse veröffentlicht FUSSBALL.DE nur geschützt, das Torverhältnis von %s steht in der Tabelle.', 'merzenich-aktuell' ), esc_html( $ma8_fb['stand']['tore'] ) ); ?></p>
		</div>
	</div>
</div></section>

<section class="section"><div class="shell">
	<div class="section-head"><div class="left"><span class="eyebrow"><?php esc_html_e( 'Weitere Teams', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Zweite und dritte Mannschaft', 'merzenich-aktuell' ); ?></h2></div></div>
	<div class="cards-3">
		<?php foreach ( $ma8_fb['reserven'] as $ma8_r ) : $ma8_ts = ma8_fb_ts( $ma8_r['naechstes']['start'] ); ?>
			<article class="club-card">
				<span class="cat"><?php echo esc_html( $ma8_r['liga'] ); ?></span>
				<h3><?php echo esc_html( $ma8_r['name'] ); ?></h3>
				<p><?php printf( esc_html__( 'Platz %1$d · %2$d Punkte · %3$s Tore.', 'merzenich-aktuell' ), (int) $ma8_r['platz'], (int) $ma8_r['punkte'], esc_html( $ma8_r['tore'] ) ); ?><br>
				<?php printf( esc_html__( 'Nächstes Spiel %1$s, %2$s Uhr %3$s %4$s.', 'merzenich-aktuell' ), esc_html( wp_date( 'd.m.Y', $ma8_ts ) ), esc_html( wp_date( 'H:i', $ma8_ts ) ), $ma8_r['naechstes']['heim'] ? esc_html__( 'gegen', 'merzenich-aktuell' ) : esc_html__( 'bei', 'merzenich-aktuell' ), esc_html( $ma8_r['naechstes']['gegner'] ) ); ?></p>
				<a class="ext" href="<?php echo esc_url( $ma8_r['url'] ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'Mannschaft bei FUSSBALL.DE ↗', 'merzenich-aktuell' ); ?></a>
			</article>
		<?php endforeach; ?>
		<article class="club-card">
			<span class="cat"><?php esc_html_e( 'Jugend', 'merzenich-aktuell' ); ?></span>
			<h3><?php printf( esc_html__( '%d Juniorenmannschaften', 'merzenich-aktuell' ), (int) $ma8_fb['teamsJunioren'] ); ?></h3>
			<p><?php printf( esc_html__( 'Von den A-Junioren bis zu den G-Junioren meldet der SC %1$d Teams. Insgesamt stellt der Verein %2$d Mannschaften.', 'merzenich-aktuell' ), (int) $ma8_fb['teamsJunioren'], (int) $ma8_fb['teamsGesamt'] ); ?></p>
			<a class="ext" href="<?php echo esc_url( $ma8_fb['vereinUrl'] ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'Alle Mannschaften ↗', 'merzenich-aktuell' ); ?></a>
		</article>
	</div>
</div></section>

<?php if ( $ma8_news ) : ?>
<section class="section"><div class="shell">
	<div class="section-head"><div class="left"><span class="eyebrow"><?php esc_html_e( 'Aus der Redaktion', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Meldungen zum SC', 'merzenich-aktuell' ); ?></h2></div></div>
	<div class="content-grid">
		<div class="feed"><?php foreach ( $ma8_news as $ma8_item ) { ma8_feed_row( $ma8_item->ID ); } ?></div>
		<aside class="sidebar"><?php ma8_box_events( 5 ); ma8_box_service(); ?></aside>
	</div>
</div></section>
<?php endif; ?>

</main>
<?php
get_footer();
