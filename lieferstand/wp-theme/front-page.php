<?php
/**
 * Startseite: Aufmacher, Schnellzeile, Nachrichtenstrom mit Sidebar,
 * Blaulicht-Bühne, Terminkalender, Sport, Ortsteile.
 *
 * @package MerzenichAktuell
 */

get_header();

// Aufmacher bestimmen: markierter Lead, sonst neueste Meldung.
$ma8_lead_query = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 1, 'meta_key' => 'ma8_lead', 'meta_value' => 1, 'ignore_sticky_posts' => true ) );
if ( ! $ma8_lead_query->have_posts() ) {
	$ma8_lead_query = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 1, 'ignore_sticky_posts' => true ) );
}
$ma8_lead_id = 0;
if ( $ma8_lead_query->have_posts() ) {
	$ma8_lead_query->the_post();
	$ma8_lead_id = get_the_ID();
}
wp_reset_postdata();

$ma8_exclude = $ma8_lead_id ? array( $ma8_lead_id ) : array();

/**
 * Register bereits gezeigter Meldungen.
 *
 * Ohne dieses Register zog jeder Block unabhaengig aus demselben Bestand: der
 * Aufmacher stand noch einmal in der Kurzleiste und ein drittes Mal als Karte
 * im Sportblock, waehrend ein Grossteil der vorhandenen Meldungen gar nicht
 * vorkam. Jede Abfrage schliesst jetzt aus, was weiter oben schon steht, und
 * traegt ihre eigene Auswahl nach. Gleiche Logik wie in der Netlify-Fassung.
 */
if ( ! function_exists( 'ma8_pick' ) ) :
function ma8_pick( array $args, array &$benutzt ) {
	$args['post_type']   = isset( $args['post_type'] ) ? $args['post_type'] : 'post';
	$args['post__not_in'] = array_merge( isset( $args['post__not_in'] ) ? $args['post__not_in'] : array(), $benutzt );
	$treffer = get_posts( $args );
	foreach ( $treffer as $ma8_p ) {
		$benutzt[] = $ma8_p->ID;
	}
	return $treffer;
}
endif;

/**
 * Wie ma8_pick, nimmt aber zuerst Beiträge mit Beitragsbild. Bildlose Meldungen
 * bekommen eine gesetzte Textkachel statt einer leeren Fläche; in den beiden
 * grossen Bildplätzen oben rechts standen dadurch aber zwei dieser Kacheln
 * übereinander. Reichen bebilderte Beiträge nicht, wird wie bisher aufgefüllt.
 */
if ( ! function_exists( 'ma8_pick_bebildert' ) ) :
function ma8_pick_bebildert( array $args, array &$benutzt ) {
	$mit = $args;
	// Bilder kommen entweder als Beitragsbild oder als Bildadresse im Feld
	// ma8_external_image (so legt der Starterinhalt sie an). Beide zaehlen.
	$mit['meta_query'] = array(
		'relation' => 'OR',
		array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ),
		array( 'key' => 'ma8_external_image', 'value' => '', 'compare' => '!=' ),
	);
	$treffer = ma8_pick( $mit, $benutzt );
	$fehlt   = (int) $args['posts_per_page'] - count( $treffer );
	if ( $fehlt > 0 ) {
		$rest = $args;
		$rest['posts_per_page'] = $fehlt;
		$treffer = array_merge( $treffer, ma8_pick( $rest, $benutzt ) );
	}
	return $treffer;
}
endif;

$ma8_sides = ma8_pick_bebildert( array( 'posts_per_page' => 2 ), $ma8_exclude );
$ma8_quick = ma8_pick( array( 'posts_per_page' => 4 ), $ma8_exclude );
$ma8_feed  = ma8_pick( array( 'posts_per_page' => 7 ), $ma8_exclude );
?>
<main id="main">

<?php if ( $ma8_lead_id ) : ?>
<section class="hero"><div class="shell hero-grid">
	<article class="lead-card">
		<a href="<?php echo esc_url( get_permalink( $ma8_lead_id ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			$ma8_credit = get_post_meta( $ma8_lead_id, 'ma8_image_credit', true );
			echo ma8_media( $ma8_lead_id, 'ma8-hero', array( 'eager' => true, 'badge' => ma8_image_type_label( $ma8_lead_id ) . ( $ma8_credit ? ' · ' . $ma8_credit : '' ) ) );
			?>
		</a>
		<div class="lead-copy">
			<?php echo ma8_kicker_line( $ma8_lead_id ); ?>
			<h1><a href="<?php echo esc_url( get_permalink( $ma8_lead_id ) ); ?>"><?php echo esc_html( get_the_title( $ma8_lead_id ) ); ?></a></h1>
			<p class="dek"><?php echo esc_html( ma8_dek( $ma8_lead_id ) ); ?></p>
			<div class="meta">
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $ma8_lead_id ) ); ?>"><?php echo esc_html( ma8_stamp( $ma8_lead_id ) ); ?></time>
				<span><?php echo esc_html( get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $ma8_lead_id ) ) ); ?></span>
			</div>
			<?php echo ma8_credit_line( $ma8_lead_id ); ?>
		</div>
	</article>
	<div class="side-stack">
		<?php foreach ( $ma8_sides as $ma8_side ) : ?>
			<article class="side-card">
				<a href="<?php echo esc_url( get_permalink( $ma8_side ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo ma8_media( $ma8_side->ID, 'ma8-card', array( 'eager' => true ) ); ?></a>
				<?php echo ma8_kicker_line( $ma8_side->ID ); ?>
				<h3><a href="<?php echo esc_url( get_permalink( $ma8_side ) ); ?>"><?php echo esc_html( get_the_title( $ma8_side ) ); ?></a></h3>
				<p><?php echo esc_html( wp_trim_words( (string) ma8_dek( $ma8_side->ID ), 16, ' …' ) ); ?></p>
				<div class="meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $ma8_side->ID ) ); ?>"><?php echo esc_html( ma8_dt( $ma8_side->ID, 'd.m. · H:i' ) ); ?></time></div>
			</article>
		<?php endforeach; ?>
	</div>
</div></section>
<?php endif; ?>

<?php if ( $ma8_quick ) : ?>
<section class="quickline"><div class="shell">
	<span class="quick-label"><?php esc_html_e( 'Kurz gemeldet', 'merzenich-aktuell' ); ?></span>
	<?php foreach ( $ma8_quick as $ma8_item ) : ?>
		<a class="quick-item" href="<?php echo esc_url( get_permalink( $ma8_item ) ); ?>">
			<b><?php echo esc_html( ma8_kicker( $ma8_item->ID ) ); ?></b>
			<span><?php echo esc_html( wp_trim_words( get_the_title( $ma8_item ), 10, ' …' ) ); ?></span>
		</a>
	<?php endforeach; ?>
</div></section>
<?php endif; ?>

<div class="shell"><?php ma8_ad( 'home_top' ); ?></div>

<section class="section"><div class="shell">
	<div class="section-head">
		<div class="left"><span class="eyebrow"><?php esc_html_e( 'Chronologisch', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Neu aus der Gemeinde', 'merzenich-aktuell' ); ?></h2></div>
		<a class="more" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'Alle Meldungen', 'merzenich-aktuell' ); ?></a>
	</div>
	<div class="content-grid">
		<div class="feed">
			<?php foreach ( $ma8_feed as $ma8_item ) { ma8_feed_row( $ma8_item->ID ); } ?>
		</div>
		<aside class="sidebar">
			<?php
			ma8_box_weather();
			ma8_box_calendar();
			ma8_box_events( 5 );
			ma8_box_popular( 5 );
			ma8_box_service();
			ma8_ad( 'sidebar' );
			?>
		</aside>
	</div>
</div></section>

<?php
$ma8_blue = ma8_pick( array( 'posts_per_page' => 3, 'category_name' => 'blaulicht' ), $ma8_exclude );
if ( $ma8_blue ) :
	?>
	<section class="section dark"><div class="shell">
		<div class="section-head">
			<div class="left"><span class="eyebrow"><?php esc_html_e( 'Blaulicht', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Einsätze mit Originalbildern', 'merzenich-aktuell' ); ?></h2></div>
			<a class="more" href="<?php echo esc_url( get_category_link( get_cat_ID( 'Blaulicht' ) ) ); ?>"><?php esc_html_e( 'Alle Einsätze', 'merzenich-aktuell' ); ?></a>
		</div>
		<div class="fire-grid">
			<?php foreach ( $ma8_blue as $ma8_index => $ma8_item ) : ?>
				<a class="photo-story<?php echo $ma8_index ? ' small' : ''; ?>" href="<?php echo esc_url( get_permalink( $ma8_item ) ); ?>">
					<?php echo ma8_media( $ma8_item->ID, 'ma8-hero' ); ?>
					<span class="over">
						<span class="eyebrow"><?php echo esc_html( ma8_dt( $ma8_item->ID, 'd.m.Y' ) ); ?><?php $d = ma8_district( $ma8_item->ID ); echo $d ? ' · ' . esc_html( $d ) : ''; ?></span>
						<h3><?php echo esc_html( get_the_title( $ma8_item ) ); ?></h3>
						<?php if ( ! $ma8_index ) : ?><p><?php echo esc_html( wp_trim_words( (string) ma8_dek( $ma8_item->ID ), 16, ' …' ) ); ?></p><?php endif; ?>
						<span class="photo-credit"><?php echo esc_html( ma8_image_type_label( $ma8_item->ID ) . ' · ' . get_post_meta( $ma8_item->ID, 'ma8_image_credit', true ) ); ?></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div></section>
<?php endif; ?>

<?php
$ma8_events = ma8_upcoming_events( 4 );
if ( $ma8_events ) :
	?>
	<section class="section"><div class="shell">
		<div class="section-head">
			<div class="left"><span class="eyebrow"><?php esc_html_e( 'Kalender', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Was als Nächstes ansteht', 'merzenich-aktuell' ); ?></h2></div>
			<a class="more" href="<?php echo esc_url( get_post_type_archive_link( 'ma_event' ) ); ?>"><?php esc_html_e( 'Alle Termine', 'merzenich-aktuell' ); ?></a>
		</div>
		<div class="events-grid">
			<?php foreach ( $ma8_events as $ma8_event ) { ma8_event_card( $ma8_event->ID ); } ?>
		</div>
	</div></section>
<?php endif; ?>

<div class="shell"><?php ma8_ad( 'home_mid' ); ?></div>

<?php
$ma8_sport = ma8_pick( array( 'posts_per_page' => 3, 'category_name' => 'sport,vereine' ), $ma8_exclude );
if ( $ma8_sport ) :
	?>
	<section class="section"><div class="shell">
		<div class="section-head">
			<div class="left"><span class="eyebrow"><?php esc_html_e( 'Sport & Vereine', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Aus dem Vereinsleben', 'merzenich-aktuell' ); ?></h2></div>
			<a class="more" href="<?php echo esc_url( get_post_type_archive_link( 'ma_club' ) ); ?>"><?php esc_html_e( 'Vereine', 'merzenich-aktuell' ); ?></a>
		</div>
		<div class="cards-3">
			<?php foreach ( $ma8_sport as $ma8_item ) { ma8_news_card( $ma8_item->ID ); } ?>
		</div>
	</div></section>
<?php endif; ?>

<?php
// Ressortblöcke: Rathaus & Politik, Wirtschaft, Leben. Fehlende Kategorien werden übersprungen.
$ma8_ressorts = array(
	'rathaus'          => __( 'Rathaus & Politik', 'merzenich-aktuell' ),
	'wirtschaft'       => __( 'Wirtschaft', 'merzenich-aktuell' ),
	'leben'            => __( 'Leben', 'merzenich-aktuell' ),
);
$ma8_ressort_blocks = array();
foreach ( $ma8_ressorts as $ma8_ressort_slug => $ma8_ressort_label ) {
	$ma8_ressort_html = ma8_ressort_block( $ma8_ressort_slug, $ma8_ressort_label, $ma8_exclude );
	if ( $ma8_ressort_html ) {
		$ma8_ressort_blocks[] = $ma8_ressort_html;
	}
}
if ( $ma8_ressort_blocks ) :
	?>
	<section class="section"><div class="shell">
		<div class="section-head">
			<div class="left"><span class="eyebrow"><?php esc_html_e( 'Ressorts', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Rathaus, Wirtschaft, Leben', 'merzenich-aktuell' ); ?></h2></div>
		</div>
		<div class="rblock-grid">
			<?php foreach ( $ma8_ressort_blocks as $ma8_ressort_html ) { echo $ma8_ressort_html; } ?>
		</div>
	</div></section>
<?php endif; ?>

<?php
// Anzeigenmarkt: Stellenanzeigen und Familienanzeigen nebeneinander.
$ma8_jobs = array();
if ( post_type_exists( 'ma_job' ) ) {
	$ma8_jobs = get_posts( array( 'post_type' => 'ma_job', 'posts_per_page' => 3 ) );
}
$ma8_family = get_posts(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'meta_query'     => array(
			array(
				'key'     => 'ma8_format',
				'value'   => array( 'familienanzeige', 'nachruf' ),
				'compare' => 'IN',
			),
		),
	)
);
$ma8_menschen_cat = get_category_by_slug( 'menschen' );
?>
<section class="section markt"><div class="shell">
	<div class="markt-grid">
		<div class="markt-col">
			<div class="section-head">
				<div class="left"><span class="eyebrow"><?php esc_html_e( 'Anzeigenmarkt', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Stellenanzeigen', 'merzenich-aktuell' ); ?></h2></div>
				<?php if ( post_type_exists( 'ma_job' ) ) : ?>
					<a class="more" href="<?php echo esc_url( get_post_type_archive_link( 'ma_job' ) ); ?>"><?php esc_html_e( 'Alle Stellen', 'merzenich-aktuell' ); ?></a>
				<?php endif; ?>
			</div>
			<?php if ( $ma8_jobs ) : ?>
				<?php
				foreach ( $ma8_jobs as $ma8_job ) :
					$ma8_job_company = get_post_meta( $ma8_job->ID, 'ma8_job_company', true );
					if ( ! $ma8_job_company ) {
						$ma8_job_company = get_the_author_meta( 'display_name', (int) $ma8_job->post_author );
					}
					$ma8_job_date = get_post_meta( $ma8_job->ID, 'ma8_job_posted', true );
					?>
					<article class="markt-card">
						<span class="eyebrow"><?php echo esc_html( $ma8_job_company ); ?></span>
						<h3><a href="<?php echo esc_url( get_permalink( $ma8_job ) ); ?>"><?php echo esc_html( get_the_title( $ma8_job ) ); ?></a></h3>
						<p><?php echo esc_html( wp_trim_words( (string) $ma8_job->post_content, 18, ' …' ) ); ?></p>
						<div class="meta">
							<span><?php echo esc_html( $ma8_job_date ? wp_date( 'd.m.Y', strtotime( $ma8_job_date ) ) : get_the_date( 'd.m.Y', $ma8_job ) ); ?></span>
							<a class="btn-sm" href="<?php echo esc_url( get_permalink( $ma8_job ) ); ?>"><?php esc_html_e( 'Zur Anzeige', 'merzenich-aktuell' ); ?></a>
						</div>
					</article>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="markt-empty">
					<p><?php esc_html_e( 'Betriebe, Gemeinde, Kitas und Vereine aus Merzenich inserieren hier. Ehrenamt und gemeinnützige Stellen sind kostenlos.', 'merzenich-aktuell' ); ?></p>
					<a class="btn-sm" href="<?php echo esc_url( home_url( '/werben/' ) ); ?>"><?php esc_html_e( 'Stelle inserieren', 'merzenich-aktuell' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
		<div class="markt-col">
			<div class="section-head">
				<div class="left"><span class="eyebrow"><?php esc_html_e( 'Anzeigenmarkt', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Familienanzeigen', 'merzenich-aktuell' ); ?></h2></div>
				<?php if ( $ma8_menschen_cat ) : ?>
					<a class="more" href="<?php echo esc_url( get_category_link( $ma8_menschen_cat ) ); ?>"><?php esc_html_e( 'Ressort Menschen', 'merzenich-aktuell' ); ?></a>
				<?php endif; ?>
			</div>
			<?php if ( $ma8_family ) : ?>
				<?php
				foreach ( $ma8_family as $ma8_fam ) :
					$ma8_fam_format = get_post_meta( $ma8_fam->ID, 'ma8_format', true );
					?>
					<article class="markt-card">
						<span class="eyebrow"><?php echo esc_html( 'nachruf' === $ma8_fam_format ? __( 'Nachruf', 'merzenich-aktuell' ) : __( 'Familienanzeige', 'merzenich-aktuell' ) ); ?></span>
						<h3><a href="<?php echo esc_url( get_permalink( $ma8_fam ) ); ?>"><?php echo esc_html( get_the_title( $ma8_fam ) ); ?></a></h3>
						<p><?php echo esc_html( wp_trim_words( (string) ma8_dek( $ma8_fam->ID ), 18, ' …' ) ); ?></p>
						<div class="meta">
							<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $ma8_fam->ID ) ); ?>"><?php echo esc_html( ma8_stamp( $ma8_fam->ID ) ); ?></time>
							<a class="btn-sm" href="<?php echo esc_url( get_permalink( $ma8_fam ) ); ?>"><?php esc_html_e( 'Lesen', 'merzenich-aktuell' ); ?></a>
						</div>
					</article>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="markt-empty">
					<p><?php esc_html_e( 'Geburten, Jubiläen, Danksagungen und Nachrufe aus der Gemeinde. Familienanzeigen erscheinen gekennzeichnet im Ressort Menschen.', 'merzenich-aktuell' ); ?></p>
					<a class="btn-sm" href="<?php echo esc_url( $ma8_menschen_cat ? get_category_link( $ma8_menschen_cat ) : home_url( '/werben/' ) ); ?>"><?php echo esc_html( $ma8_menschen_cat ? __( 'Zum Ressort Menschen', 'merzenich-aktuell' ) : __( 'Jetzt werben', 'merzenich-aktuell' ) ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div></section>

<?php
$ma8_districts = get_terms( array( 'taxonomy' => 'ma_district', 'hide_empty' => false ) );
if ( $ma8_districts && ! is_wp_error( $ma8_districts ) ) :
	?>
	<section class="section"><div class="shell">
		<div class="section-head"><div class="left"><span class="eyebrow"><?php esc_html_e( 'Vor Ort', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Die Ortsteile', 'merzenich-aktuell' ); ?></h2></div></div>
		<div class="district-grid">
			<?php foreach ( $ma8_districts as $ma8_term ) : ?>
				<a class="district-tile" href="<?php echo esc_url( get_term_link( $ma8_term ) ); ?>">
					<h3><?php echo esc_html( $ma8_term->name ); ?></h3>
					<p><?php echo esc_html( wp_trim_words( $ma8_term->description, 14, ' …' ) ); ?></p>
					<p style="color:var(--gold);font-size:12px;font-weight:700"><?php echo esc_html( sprintf( _n( '%d Meldung', '%d Meldungen', $ma8_term->count, 'merzenich-aktuell' ), $ma8_term->count ) ); ?></p>
				</a>
			<?php endforeach; ?>
		</div>
	</div></section>
<?php endif; ?>


</main>
<?php
get_footer();
