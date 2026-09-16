<?php
/**
 * Wiederverwendbare Ausgabebausteine: Karten, Zeilen, Sidebar-Boxen.
 *
 * @package MerzenichAktuell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Große Zeile im Nachrichtenstrom. */
function ma8_feed_row( $post_id ) {
	?>
	<article class="feed-row">
		<a class="feed-img" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo ma8_media( $post_id, 'ma8-thumb' ); ?></a>
		<div class="feed-copy">
			<?php echo ma8_kicker_line( $post_id ); ?>
			<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
			<p class="dek"><?php echo esc_html( wp_trim_words( (string) ma8_dek( $post_id ), 26, ' …' ) ); ?></p>
			<div class="meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>"><?php echo esc_html( ma8_stamp( $post_id ) ); ?></time></div>
			<?php echo ma8_credit_line( $post_id ); ?>
		</div>
	</article>
	<?php
}

/** Karte im Dreierraster. */
function ma8_news_card( $post_id ) {
	?>
	<article class="news-card">
		<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo ma8_media( $post_id, 'ma8-card', array( 'badge' => ma8_image_type_label( $post_id ) ) ); ?></a>
		<div class="news-card-body">
			<?php echo ma8_kicker_line( $post_id ); ?>
			<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
			<p class="dek"><?php echo esc_html( wp_trim_words( (string) ma8_dek( $post_id ), 18, ' …' ) ); ?></p>
			<div class="meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>"><?php echo esc_html( ma8_stamp( $post_id ) ); ?></time></div>
		</div>
	</article>
	<?php
}

/** Terminkarte. */
function ma8_event_card( $post_id ) {
	$ts = ma8_event_ts( $post_id );
	?>
	<?php
	$ma8_has_img = has_post_thumbnail( $post_id );
	$ma8_cat     = get_post_meta( $post_id, 'ma8_event_category', true );
	// Das Datumsplättchen sitzt bei Karten mit Bild darauf, sonst als erstes
	// Element im Textteil. Beide Fälle nutzen dieselbe Klasse .event-date; eine
	// eigene Auszeichnung dafür gibt es im Design-System nicht.
	$ma8_datebox = '<span class="event-date"><b>' . esc_html( wp_date( 'j', $ts ) ) . '</b><span>' . esc_html( wp_date( 'M', $ts ) ) . '</span></span>';
	?>
	<article class="event-card<?php echo $ma8_has_img ? '' : ' noimg'; ?>">
		<?php if ( $ma8_has_img ) : ?>
		<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" tabindex="-1" aria-hidden="true"><div class="ev-media">
			<?php echo ma8_media( $post_id, 'ma8-card' ); ?>
			<?php echo $ma8_datebox; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div></a>
		<?php endif; ?>
		<div class="event-body">
			<?php
			if ( ! $ma8_has_img ) {
				echo $ma8_datebox; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
			<span class="eyebrow"><time datetime="<?php echo esc_attr( wp_date( 'c', $ts ) ); ?>"><?php echo esc_html( wp_date( 'D', $ts ) ); ?></time> · <?php echo esc_html( wp_date( 'H:i', $ts ) ); ?> Uhr<?php echo $ma8_cat ? ' · ' . esc_html( $ma8_cat ) : ''; ?></span>
			<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
			<p><?php echo esc_html( get_post_meta( $post_id, 'ma8_event_location', true ) ); ?></p>
		</div>
	</article>
	<?php
}

/** Sidebar-Box: nächste Termine. */
function ma8_box_events( $limit = 5 ) {
	$events = ma8_upcoming_events( $limit );
	if ( ! $events ) {
		return;
	}
	?>
	<div class="sidebox">
		<h3><?php esc_html_e( 'Nächste Termine', 'merzenich-aktuell' ); ?> <a href="<?php echo esc_url( get_post_type_archive_link( 'ma_event' ) ); ?>"><?php esc_html_e( 'alle', 'merzenich-aktuell' ); ?></a></h3>
		<ul>
			<?php foreach ( $events as $event ) : $ts = ma8_event_ts( $event->ID ); ?>
				<li class="termin">
					<span class="d"><b><?php echo esc_html( wp_date( 'j', $ts ) ); ?></b><span><?php echo esc_html( wp_date( 'M', $ts ) ); ?></span></span>
					<span class="t">
						<a href="<?php echo esc_url( get_permalink( $event ) ); ?>"><?php echo esc_html( get_the_title( $event ) ); ?></a>
						<small><?php echo esc_html( wp_date( 'H:i', $ts ) ); ?> Uhr · <?php echo esc_html( get_post_meta( $event->ID, 'ma8_event_location', true ) ); ?></small>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/** Sidebar-Box: meistgelesen. */
function ma8_box_popular( $limit = 5 ) {
	$ids = ma8_popular_ids( $limit );
	if ( ! $ids ) {
		return;
	}
	?>
	<div class="sidebox">
		<h3><?php esc_html_e( 'Meistgelesen', 'merzenich-aktuell' ); ?></h3>
		<ol class="ranked">
			<?php foreach ( $ids as $id ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a></li>
			<?php endforeach; ?>
		</ol>
	</div>
	<?php
}

/** Sidebar-Box: Wetter. Open-Meteo, ohne Schlüssel und ohne Cookies. */
function ma8_box_weather() {
	?>
	<div class="sidebox">
		<h3><?php esc_html_e( 'Wetter in Merzenich', 'merzenich-aktuell' ); ?></h3>
		<div class="weather" data-weather><p class="weather-skel"><?php esc_html_e( 'Wetterdaten werden geladen.', 'merzenich-aktuell' ); ?></p></div>
	</div>
	<?php
}

/** Lesezeit nach 200 Wörtern je Minute. */
function ma8_read_minutes( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$text    = get_post_field( 'post_content', $post_id ) . ' ' . ma8_dek( $post_id ) . ' ' . get_post_meta( $post_id, 'ma8_facts', true );
	$words   = str_word_count( wp_strip_all_tags( (string) $text ), 0, 'äöüÄÖÜßáéíóúàèìòùâêîôûñç' );
	return max( 1, (int) round( $words / 200 ) );
}

/** Sidebar-Box: Monatskalender mit markierten Terminen. */
function ma8_box_calendar() {
	$ma8_year  = (int) wp_date( 'Y' );
	$ma8_month = (int) wp_date( 'n' );
	$ma8_today = (int) wp_date( 'j' );
	$ma8_days  = (int) wp_date( 't' );

	$ma8_first_ts = mktime( 0, 0, 0, $ma8_month, 1, $ma8_year );
	// Kalenderversatz: Montag = 0 ... Sonntag = 6.
	$ma8_lead = ( (int) wp_date( 'N', $ma8_first_ts ) + 6 ) % 7;

	$ma8_month_end = wp_date( 'Y-m-d\T23:59', mktime( 0, 0, 0, $ma8_month, $ma8_days, $ma8_year ) );
	$ma8_now       = wp_date( 'Y-m-d\TH:i' );

	// Termine im laufenden Monat ab jetzt einsammeln, ein Eintrag je Tag reicht für die Markierung.
	$ma8_by_day     = array();
	$ma8_cal_events = new WP_Query(
		array(
			'post_type'      => 'ma_event',
			'posts_per_page' => -1,
			'meta_key'       => 'ma8_event_start',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			// Bewusst als Text verglichen (kein type=DATETIME): ma8_event_start liegt als
			// "Y-m-d\TH:i" vor, dasselbe Format wie im Rest des Themes (z. B. beim Sortieren
			// nach meta_value). Als Text ist das fest zu-null-gepolstert und damit lexikografisch
			// sortier- und vergleichbar; ein CAST(...AS DATETIME) muesste erst pruefen, ob MySQL
			// den "T"-Separator akzeptiert, und ist ohne Livesystem nicht verifizierbar.
			'meta_query'     => array(
				array(
					'key'     => 'ma8_event_start',
					'value'   => array( $ma8_now, $ma8_month_end ),
					'compare' => 'BETWEEN',
					'type'    => 'CHAR',
				),
			),
		)
	);
	foreach ( $ma8_cal_events->posts as $ma8_cal_event ) {
		$ma8_day = (int) wp_date( 'j', ma8_event_ts( $ma8_cal_event->ID ) );
		if ( ! isset( $ma8_by_day[ $ma8_day ] ) ) {
			$ma8_by_day[ $ma8_day ] = $ma8_cal_event->ID;
		}
	}
	wp_reset_postdata();

	$ma8_month_names = array(
		1  => __( 'Januar', 'merzenich-aktuell' ),
		2  => __( 'Februar', 'merzenich-aktuell' ),
		3  => __( 'März', 'merzenich-aktuell' ),
		4  => __( 'April', 'merzenich-aktuell' ),
		5  => __( 'Mai', 'merzenich-aktuell' ),
		6  => __( 'Juni', 'merzenich-aktuell' ),
		7  => __( 'Juli', 'merzenich-aktuell' ),
		8  => __( 'August', 'merzenich-aktuell' ),
		9  => __( 'September', 'merzenich-aktuell' ),
		10 => __( 'Oktober', 'merzenich-aktuell' ),
		11 => __( 'November', 'merzenich-aktuell' ),
		12 => __( 'Dezember', 'merzenich-aktuell' ),
	);
	$ma8_weekdays = array( 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So' );
	?>
	<div class="sidebox">
		<h3><?php esc_html_e( 'Monatskalender', 'merzenich-aktuell' ); ?></h3>
		<div class="mcal" role="group" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: Monatsname, 2: Jahr. */ __( 'Kalender %1$s %2$d', 'merzenich-aktuell' ), $ma8_month_names[ $ma8_month ], $ma8_year ) ); ?>">
			<div class="mcal-head"><b><?php echo esc_html( $ma8_month_names[ $ma8_month ] . ' ' . $ma8_year ); ?></b><span><?php esc_html_e( 'Tage mit Termin sind markiert', 'merzenich-aktuell' ); ?></span></div>
			<div class="mcal-grid">
				<?php foreach ( $ma8_weekdays as $ma8_wd ) : ?>
					<i><?php echo esc_html( $ma8_wd ); ?></i>
				<?php endforeach; ?>
				<?php for ( $ma8_i = 0; $ma8_i < $ma8_lead; $ma8_i++ ) : ?>
					<span></span>
				<?php endfor; ?>
				<?php for ( $ma8_d = 1; $ma8_d <= $ma8_days; $ma8_d++ ) : ?>
					<?php $ma8_is_today = ( $ma8_d === $ma8_today ); ?>
					<?php if ( isset( $ma8_by_day[ $ma8_d ] ) ) : ?>
						<a class="has<?php echo $ma8_is_today ? ' today' : ''; ?>" href="<?php echo esc_url( get_permalink( $ma8_by_day[ $ma8_d ] ) ); ?>" title="<?php echo esc_attr( get_the_title( $ma8_by_day[ $ma8_d ] ) ); ?>" aria-label="<?php echo esc_attr( $ma8_d . '. ' . $ma8_month_names[ $ma8_month ] . ': ' . get_the_title( $ma8_by_day[ $ma8_d ] ) ); ?>"><?php echo esc_html( (string) $ma8_d ); ?></a>
					<?php else : ?>
						<span<?php echo $ma8_is_today ? ' class="today"' : ''; ?>><?php echo esc_html( (string) $ma8_d ); ?></span>
					<?php endif; ?>
				<?php endfor; ?>
			</div>
		</div>
	</div>
	<?php
}

/** Ressortblock: Aufmacher plus vier Überschriften für ein Ressort (Kategorie). */
function ma8_ressort_block( $category_slug, $label, &$benutzt = null ) {
	$ma8_cat = get_category_by_slug( $category_slug );
	if ( ! $ma8_cat ) {
		return '';
	}
	// Meldungen, die weiter oben auf der Startseite schon stehen, werden hier
	// ausgeschlossen und die eigene Auswahl nachgetragen. Ohne das zeigte der
	// Ressortblock den Aufmacher ein zweites Mal.
	$ma8_args = array( 'post_type' => 'post', 'posts_per_page' => 5, 'category' => $ma8_cat->term_id );
	if ( is_array( $benutzt ) ) {
		$ma8_args['post__not_in'] = $benutzt;
	}
	$ma8_posts = get_posts( $ma8_args );
	if ( is_array( $benutzt ) ) {
		foreach ( $ma8_posts as $ma8_rp ) {
			$benutzt[] = $ma8_rp->ID;
		}
	}
	if ( ! $ma8_posts ) {
		return '';
	}
	$ma8_lead = array_shift( $ma8_posts );

	ob_start();
	?>
	<section class="rblock">
		<h3 class="rblock-h">
			<a href="<?php echo esc_url( get_category_link( $ma8_cat ) ); ?>"><?php echo esc_html( $label ); ?></a>
			<span><?php echo esc_html( sprintf( _n( '%d Meldung', '%d Meldungen', $ma8_cat->count, 'merzenich-aktuell' ), $ma8_cat->count ) ); ?></span>
		</h3>
		<article class="rblock-lead">
			<a href="<?php echo esc_url( get_permalink( $ma8_lead ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo ma8_media( $ma8_lead->ID, 'ma8-card' ); ?></a>
			<h4><a href="<?php echo esc_url( get_permalink( $ma8_lead ) ); ?>"><?php echo esc_html( get_the_title( $ma8_lead ) ); ?></a></h4>
			<p><?php echo esc_html( wp_trim_words( (string) ma8_dek( $ma8_lead->ID ), 16, ' …' ) ); ?></p>
			<div class="meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $ma8_lead->ID ) ); ?>"><?php echo esc_html( ma8_stamp( $ma8_lead->ID ) ); ?></time></div>
		</article>
		<?php if ( $ma8_posts ) : ?>
			<ul class="rblock-list">
				<?php foreach ( array_slice( $ma8_posts, 0, 4 ) as $ma8_item ) : ?>
					<li>
						<a href="<?php echo esc_url( get_permalink( $ma8_item ) ); ?>"><?php echo esc_html( get_the_title( $ma8_item ) ); ?></a>
						<span class="meta"><?php echo esc_html( ma8_stamp( $ma8_item->ID ) ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<a class="more" href="<?php echo esc_url( get_category_link( $ma8_cat ) ); ?>">
			<?php
			/* translators: %s: Ressortname, z. B. "Wirtschaft". */
			echo esc_html( sprintf( __( 'Alle %s-Meldungen', 'merzenich-aktuell' ), $label ) );
			?>
		</a>
	</section>
	<?php
	return ob_get_clean();
}

/** Sidebar-Box: Service-Nummern (über den Customizer pflegbar). */
function ma8_box_service() {
	$rows = array(
		array( __( 'Notruf Feuerwehr / Rettung', 'merzenich-aktuell' ), '112', '' ),
		array( __( 'Polizei', 'merzenich-aktuell' ), '110', '' ),
		array( __( 'Ärztlicher Bereitschaftsdienst', 'merzenich-aktuell' ), '116 117', '' ),
		array( __( 'Apotheken-Notdienst', 'merzenich-aktuell' ), 'aponet.de', 'https://www.aponet.de/apotheke/notdienstsuche' ),
		array( __( 'Rathaus Merzenich', 'merzenich-aktuell' ), 'Valdersweg 1', '' ),
		array( __( 'Telefon Rathaus', 'merzenich-aktuell' ), '02421 399-0', '' ),
	);
	?>
	<div class="sidebox">
		<h3><?php esc_html_e( 'Merzenich heute', 'merzenich-aktuell' ); ?></h3>
		<ul class="service">
			<?php foreach ( $rows as $row ) : ?>
				<li>
					<span class="k"><?php echo esc_html( $row[0] ); ?></span>
					<span class="v"><?php echo $row[2] ? '<a href="' . esc_url( $row[2] ) . '" target="_blank" rel="noopener">' . esc_html( $row[1] ) . '</a>' : esc_html( $row[1] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}
