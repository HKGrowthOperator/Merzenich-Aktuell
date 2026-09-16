<?php
/**
 * Template Name: Archiv nach Monaten
 *
 * Legt eine Seite an, wählt rechts dieses Template und die Übersicht baut sich selbst.
 *
 * @package MerzenichAktuell
 */

get_header();

$ma8_all = get_posts( array( 'post_type' => 'post', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC' ) );

// Beiträge ohne Veröffentlichungsdatum der Quelle (ma8_undated) werden aus der
// Monatsgruppierung herausgelöst und weiter unten separat gelistet – siehe die
// "dated"/"undated"-Trennung in archivePage() der Netlify-Referenz.
$ma8_dated   = array();
$ma8_undated = array();
foreach ( $ma8_all as $ma8_item ) {
	if ( get_post_meta( $ma8_item->ID, 'ma8_undated', true ) ) {
		$ma8_undated[] = $ma8_item;
	} else {
		$ma8_dated[] = $ma8_item;
	}
}

$ma8_groups = array();
foreach ( $ma8_dated as $ma8_item ) {
	$ma8_groups[ get_the_date( 'Y-m', $ma8_item ) ][] = $ma8_item;
}
$ma8_years = array();
foreach ( array_keys( $ma8_groups ) as $ma8_key ) {
	$ma8_years[ substr( $ma8_key, 0, 4 ) ] = true;
}
?>
<main id="main">
<div class="page-head"><div class="shell">
	<nav class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a><span class="sep">&rsaquo;</span><span><?php the_title(); ?></span></nav>
	<span class="eyebrow"><?php esc_html_e( 'Alle Jahrgänge', 'merzenich-aktuell' ); ?></span>
	<h1><?php the_title(); ?></h1>
	<p class="desc"><?php esc_html_e( 'Jede veröffentlichte Meldung, nach Monaten sortiert. Ältere Beiträge bleiben dauerhaft unter derselben Adresse erreichbar.', 'merzenich-aktuell' ); ?></p>
	<p class="count-line">
		<?php
		printf(
			esc_html( _n( '%1$d Meldung in %2$d Monat', '%1$d Meldungen in %2$d Monaten', count( $ma8_all ), 'merzenich-aktuell' ) ),
			count( $ma8_all ),
			count( $ma8_groups )
		);
		?>
	</p>
</div></div>

<section class="section"><div class="shell">
	<div class="content-grid">
		<div>
			<?php foreach ( array_keys( $ma8_years ) as $ma8_year ) : ?>
				<div class="archive-year">
					<h2><?php echo esc_html( $ma8_year ); ?></h2>
					<?php
					foreach ( $ma8_groups as $ma8_key => $ma8_list ) :
						if ( substr( $ma8_key, 0, 4 ) !== $ma8_year ) {
							continue;
						}
						$ma8_stamp = strtotime( $ma8_key . '-01' );
						?>
						<div class="archive-month">
							<div>
								<h3>
									<?php echo esc_html( wp_date( 'F', $ma8_stamp ) ); ?>
									<small><?php printf( esc_html( _n( '%d Meldung', '%d Meldungen', count( $ma8_list ), 'merzenich-aktuell' ) ), count( $ma8_list ) ); ?></small>
								</h3>
							</div>
							<ul class="archive-list">
								<?php foreach ( $ma8_list as $ma8_entry ) : $ma8_cats = get_the_category( $ma8_entry->ID ); ?>
									<li>
										<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $ma8_entry ) ); ?>"><?php echo esc_html( get_the_date( 'd.m.', $ma8_entry ) ); ?></time>
										<a href="<?php echo esc_url( get_permalink( $ma8_entry ) ); ?>"><?php echo esc_html( get_the_title( $ma8_entry ) ); ?></a>
										<?php if ( $ma8_cats ) : ?><span class="rs"><?php echo esc_html( $ma8_cats[0]->name ); ?></span><?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>

			<?php if ( $ma8_undated ) : ?>
				<div class="archive-year">
					<h2><?php esc_html_e( 'Ohne Datum', 'merzenich-aktuell' ); ?></h2>
					<div class="archive-month" id="ohne-datum">
						<h3>
							<?php esc_html_e( 'Pressemitteilungen der Gemeinde', 'merzenich-aktuell' ); ?>
							<small>
								<?php
								// Abrufdatum der ersten (jüngsten) undatierten Meldung als Sammel-Stand anzeigen.
								$ma8_retrieved = get_post_meta( $ma8_undated[0]->ID, 'ma8_retrieved', true );
								$ma8_retrieved = $ma8_retrieved ? strtotime( $ma8_retrieved ) : get_the_date( 'U', $ma8_undated[0] );
								printf(
									esc_html(
										/* translators: 1: Anzahl Meldungen, 2: Abrufdatum */
										_n( '%1$d Meldung · abgerufen %2$s', '%1$d Meldungen · abgerufen %2$s', count( $ma8_undated ), 'merzenich-aktuell' )
									),
									count( $ma8_undated ),
									esc_html( wp_date( 'd.m.Y', $ma8_retrieved ) )
								);
								?>
							</small>
						</h3>
						<ul class="archive-list">
							<?php foreach ( $ma8_undated as $ma8_entry ) : $ma8_cats = get_the_category( $ma8_entry->ID ); ?>
								<li>
									<span class="undated"><?php esc_html_e( 'o. D.', 'merzenich-aktuell' ); ?></span>
									<a href="<?php echo esc_url( get_permalink( $ma8_entry ) ); ?>"><?php echo esc_html( get_the_title( $ma8_entry ) ); ?></a>
									<?php if ( $ma8_cats ) : ?><span class="rs"><?php echo esc_html( $ma8_cats[0]->name ); ?></span><?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<aside class="sidebar">
			<?php
			ma8_box_weather();
			ma8_box_events( 5 );
			ma8_box_popular( 5 );
			?>
		</aside>
	</div>
</div></section>
</main>
<?php
get_footer();
