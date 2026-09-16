<?php
/**
 * Merzenich Aktuell v8 — Theme-Funktionen.
 *
 * Enthält: Theme-Setup, Assets, Inhaltstypen (Veranstaltung, Verein), Taxonomie Ortsteil,
 * Redaktionsfelder mit Publish-Check, Werbeplätze, SEO/Schema, News-Sitemap und Reichweitenzählung.
 *
 * @package MerzenichAktuell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MA8_VERSION', '18.0.0' );
define( 'MA8_TZ', 'Europe/Berlin' );

require_once get_template_directory() . '/inc/template-parts.php';
require_once get_template_directory() . '/inc/fussball-data.php';
require_once get_template_directory() . '/inc/fussball-parts.php';

/* -------------------------------------------------------------------------
 * Setup
 * ---------------------------------------------------------------------- */

function ma8_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_editor_style( 'style.css' );

	add_image_size( 'ma8-hero', 1600, 900, true );
	add_image_size( 'ma8-card', 960, 540, true );
	add_image_size( 'ma8-thumb', 500, 282, true );

	register_nav_menus(
		array(
			'primary' => __( 'Hauptnavigation (Ressorts)', 'merzenich-aktuell' ),
			'footer'  => __( 'Footer-Navigation', 'merzenich-aktuell' ),
		)
	);

	load_theme_textdomain( 'merzenich-aktuell', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'ma8_setup' );

function ma8_assets() {
	wp_enqueue_style( 'ma8-style', get_stylesheet_uri(), array(), MA8_VERSION );
	wp_enqueue_script( 'ma8-app', get_template_directory_uri() . '/assets/app.js', array(), MA8_VERSION, true );

	if ( is_search() || is_page_template( 'template-suche.php' ) ) {
		wp_localize_script( 'ma8-app', 'MA_INDEX', ma8_search_index() );
	}
}
add_action( 'wp_enqueue_scripts', 'ma8_assets' );

/** Fonts vorladen, damit die Schrift nicht nachspringt. */
function ma8_preload_fonts() {
	$dir = get_template_directory_uri() . '/assets/fonts/';
	foreach ( array( 'newsreader-latin-wght-normal.woff2', 'libre-franklin-latin-wght-normal.woff2' ) as $font ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $dir . $font ) );
	}
	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( get_template_directory_uri() . '/assets/favicon.svg' ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( get_template_directory_uri() . '/assets/avatar-1024.png' ) );
	echo '<meta name="theme-color" content="#0c0b0a">' . "\n";
}
add_action( 'wp_head', 'ma8_preload_fonts', 1 );

/* -------------------------------------------------------------------------
 * Inhaltstypen und Taxonomien
 * ---------------------------------------------------------------------- */

function ma8_register_content() {
	register_post_type(
		'ma_event',
		array(
			'labels'        => array(
				'name'          => __( 'Veranstaltungen', 'merzenich-aktuell' ),
				'singular_name' => __( 'Veranstaltung', 'merzenich-aktuell' ),
				'add_new_item'  => __( 'Veranstaltung hinzufügen', 'merzenich-aktuell' ),
				'edit_item'     => __( 'Veranstaltung bearbeiten', 'merzenich-aktuell' ),
				'menu_name'     => __( 'Veranstaltungen', 'merzenich-aktuell' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-calendar-alt',
			'menu_position' => 5,
			'rewrite'       => array( 'slug' => 'veranstaltungen' ),
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'custom-fields' ),
		)
	);

	register_post_type(
		'ma_club',
		array(
			'labels'       => array(
				'name'          => __( 'Vereine', 'merzenich-aktuell' ),
				'singular_name' => __( 'Verein', 'merzenich-aktuell' ),
				'add_new_item'  => __( 'Verein hinzufügen', 'merzenich-aktuell' ),
				'menu_name'     => __( 'Vereine', 'merzenich-aktuell' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-groups',
			'rewrite'      => array( 'slug' => 'vereine' ),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
		)
	);

	register_post_type(
		'ma_job',
		array(
			'labels'       => array(
				'name'          => __( 'Stellenanzeigen', 'merzenich-aktuell' ),
				'singular_name' => __( 'Stellenanzeige', 'merzenich-aktuell' ),
				'add_new_item'  => __( 'Stellenanzeige hinzufügen', 'merzenich-aktuell' ),
				'edit_item'     => __( 'Stellenanzeige bearbeiten', 'merzenich-aktuell' ),
				'menu_name'     => __( 'Stellenanzeigen', 'merzenich-aktuell' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-businessman',
			'rewrite'      => array( 'slug' => 'jobs' ),
			'supports'     => array( 'title', 'editor', 'custom-fields', 'author' ),
		)
	);

	register_post_type(
		'ma_business',
		array(
			'labels'       => array(
				'name'          => __( 'Betriebe', 'merzenich-aktuell' ),
				'singular_name' => __( 'Betrieb', 'merzenich-aktuell' ),
				'add_new_item'  => __( 'Betrieb hinzufügen', 'merzenich-aktuell' ),
				'edit_item'     => __( 'Betrieb bearbeiten', 'merzenich-aktuell' ),
				'menu_name'     => __( 'Betriebe', 'merzenich-aktuell' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-store',
			'rewrite'      => array( 'slug' => 'betriebe' ),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		)
	);

	register_taxonomy(
		'ma_district',
		array( 'post', 'ma_event', 'ma_club', 'ma_job', 'ma_business' ),
		array(
			'labels'            => array(
				'name'          => __( 'Ortsteile', 'merzenich-aktuell' ),
				'singular_name' => __( 'Ortsteil', 'merzenich-aktuell' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'ortsteil' ),
		)
	);
}
add_action( 'init', 'ma8_register_content' );

/** Ortsteile und Ressorts beim ersten Aktivieren anlegen. */
function ma8_seed_terms() {
	// Ortsteile. Die Adressteile (Slugs) sind fest gesetzt, damit sie mit denen des
	// Startinhalts uebereinstimmen und keine doppelten Begriffe entstehen.
	$districts = array(
		'merzenich'   => array(
			'Merzenich',
			'Hauptort der Gemeinde mit Rathaus, Bürgerhaus, Pfarrkirche St. Laurentius, Heimatmuseum und S-Bahn-Halt an der Strecke Köln–Aachen.',
		),
		'golzheim'    => array(
			'Golzheim',
			'Ländlicher Ortsteil im Norden der Gemeinde mit Pfarrkirche St. Gregorius, eigener Grundschule und Schützenhalle.',
		),
		'girbelsrath' => array(
			'Girbelsrath',
			'Ortsteil im Süden der Gemeinde mit der Kirche St. Amandus, Sportplatz, eigener Löschgruppe und aktivem Karnevalsverein.',
		),
		'morschenich' => array(
			'Morschenich',
			'Der ab 2015 bezogene Umsiedlungsort „Zwischen den Höfen“ westlich von Merzenich, bis Juli 2024 Morschenich-Neu genannt, mit Bürgewaldzentrum.',
		),
		'buergewald'  => array(
			'Bürgewald',
			'Das alte Morschenich am Rand des Hambacher Forsts, seit 6. Juli 2024 Bürgewald genannt und als „Ort der Zukunft“ entwickelt.',
		),
	);
	foreach ( $districts as $slug => $data ) {
		if ( ! term_exists( $slug, 'ma_district' ) && ! term_exists( $data[0], 'ma_district' ) ) {
			wp_insert_term( $data[0], 'ma_district', array( 'slug' => $slug, 'description' => $data[1] ) );
		}
	}

	// Ressorts. Slug und Name muessen zum Startinhalt passen, sonst legt der Import
	// eine zweite Kategorie mit gleichem Namen an und die Navigation zeigt sie doppelt.
	$ressorts = array(
		'nachrichten' => 'Aktuell',
		'blaulicht'   => 'Blaulicht',
		'sport'       => 'Sport',
		'rathaus'     => 'Rathaus & Politik',
		'vereine'     => 'Vereine',
		'leben'       => 'Leben',
		'wirtschaft'  => 'Wirtschaft',
		'menschen'    => 'Menschen',
	);
	foreach ( $ressorts as $slug => $name ) {
		if ( ! term_exists( $slug, 'category' ) && ! term_exists( $name, 'category' ) ) {
			wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
	}
}

/* -------------------------------------------------------------------------
 * Redaktionsfelder
 * ---------------------------------------------------------------------- */

function ma8_editorial_fields() {
	return array(
		'ma8_kicker'         => __( 'Dachzeile (Kicker)', 'merzenich-aktuell' ),
		'ma8_dek'            => __( 'Unterzeile / Teaser', 'merzenich-aktuell' ),
		'ma8_facts'          => __( 'Das Wichtigste in Kürze (eine Zeile je Punkt)', 'merzenich-aktuell' ),
		'ma8_source_name'    => __( 'Quellenname', 'merzenich-aktuell' ),
		'ma8_source_url'     => __( 'Quellen-URL', 'merzenich-aktuell' ),
		'ma8_source_date'    => __( 'Datenstand der Quelle', 'merzenich-aktuell' ),
		'ma8_external_image' => __( 'Externes Quellenbild (URL)', 'merzenich-aktuell' ),
		'ma8_image_credit'   => __( 'Bildcredit', 'merzenich-aktuell' ),
		'ma8_image_alt'      => __( 'Bildbeschreibung (Alt-Text)', 'merzenich-aktuell' ),
		'ma8_seo_title'      => __( 'SEO-Titel (optional)', 'merzenich-aktuell' ),
		'ma8_seo_desc'       => __( 'Meta-Description (optional)', 'merzenich-aktuell' ),
	);
}

function ma8_image_types() {
	return array(
		'original' => __( 'Originalbild des Ereignisses', 'merzenich-aktuell' ),
		'event'    => __( 'Offizielles Veranstaltungsbild', 'merzenich-aktuell' ),
		'source'   => __( 'Quellenmotiv', 'merzenich-aktuell' ),
		'archive'  => __( 'Archivbild', 'merzenich-aktuell' ),
		'logo'     => __( 'Offizielles Vereinslogo', 'merzenich-aktuell' ),
	);
}

/** Redaktionelle Sonderformate für native Beiträge (post). */
function ma8_formats() {
	return array(
		''                => __( '— Standard —', 'merzenich-aktuell' ),
		'familienanzeige' => __( 'Familienanzeige', 'merzenich-aktuell' ),
		'nachruf'         => __( 'Nachruf', 'merzenich-aktuell' ),
		'kolumne'         => __( 'Kolumne', 'merzenich-aktuell' ),
		'tipps'           => __( 'Tipps', 'merzenich-aktuell' ),
		'interview'       => __( 'Interview', 'merzenich-aktuell' ),
		'firmenportraet'  => __( 'Firmenporträt', 'merzenich-aktuell' ),
	);
}

/** Anstellungsarten für Stellenanzeigen. */
function ma8_job_types() {
	return array(
		'Vollzeit'   => __( 'Vollzeit', 'merzenich-aktuell' ),
		'Teilzeit'   => __( 'Teilzeit', 'merzenich-aktuell' ),
		'Minijob'    => __( 'Minijob', 'merzenich-aktuell' ),
		'Ausbildung' => __( 'Ausbildung', 'merzenich-aktuell' ),
		'Ehrenamt'   => __( 'Ehrenamt', 'merzenich-aktuell' ),
	);
}

/** Formatbadge für die Kartenansichten, z. B. "Nachruf" oder "Interview". */
function ma8_format_badge( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$format  = get_post_meta( $post_id, 'ma8_format', true );
	$labels  = ma8_formats();
	if ( ! $format || empty( $labels[ $format ] ) ) {
		return '';
	}
	return '<span class="fbadge ' . esc_attr( $format ) . '">' . esc_html( $labels[ $format ] ) . '</span>';
}

function ma8_meta_boxes() {
	add_meta_box( 'ma8_editorial', __( 'Merzenich Aktuell — Redaktion, Quelle und SEO', 'merzenich-aktuell' ), 'ma8_editorial_box', array( 'post', 'ma_event', 'ma_club', 'ma_job', 'ma_business' ), 'normal', 'high' );
	add_meta_box( 'ma8_check', __( 'Publish-Check', 'merzenich-aktuell' ), 'ma8_check_box', array( 'post', 'ma_event' ), 'side', 'high' );
}
add_action( 'add_meta_boxes', 'ma8_meta_boxes' );

function ma8_editorial_box( $post ) {
	wp_nonce_field( 'ma8_save', 'ma8_nonce' );
	$multiline = array( 'ma8_dek', 'ma8_facts', 'ma8_seo_desc' );

	echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">';
	foreach ( ma8_editorial_fields() as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true );
		$full  = in_array( $key, $multiline, true );
		printf( '<p style="%s"><label for="%s"><strong>%s</strong></label><br>', $full ? 'grid-column:1/-1' : '', esc_attr( $key ), esc_html( $label ) );
		if ( $full ) {
			printf( '<textarea id="%s" name="%s" rows="3" style="width:100%%">%s</textarea>', esc_attr( $key ), esc_attr( $key ), esc_textarea( $value ) );
		} else {
			printf( '<input id="%s" name="%s" value="%s" style="width:100%%">', esc_attr( $key ), esc_attr( $key ), esc_attr( $value ) );
		}
		echo '</p>';
	}
	echo '</div>';

	$type = get_post_meta( $post->ID, 'ma8_image_type', true ) ? get_post_meta( $post->ID, 'ma8_image_type', true ) : 'source';
	echo '<p><label><strong>' . esc_html__( 'Bildtyp', 'merzenich-aktuell' ) . '</strong></label><br><select name="ma8_image_type">';
	foreach ( ma8_image_types() as $val => $label ) {
		printf( '<option value="%s" %s>%s</option>', esc_attr( $val ), selected( $type, $val, false ), esc_html( $label ) );
	}
	echo '</select></p>';

	printf(
		'<p><label><input type="checkbox" name="ma8_featured" value="1" %s> <strong>%s</strong></label> &nbsp;&nbsp; <label><input type="checkbox" name="ma8_breaking" value="1" %s> <strong>%s</strong></label> &nbsp;&nbsp; <label><input type="checkbox" name="ma8_lead" value="1" %s> <strong>%s</strong></label></p>',
		checked( get_post_meta( $post->ID, 'ma8_featured', true ), 1, false ),
		esc_html__( 'Top-Thema', 'merzenich-aktuell' ),
		checked( get_post_meta( $post->ID, 'ma8_breaking', true ), 1, false ),
		esc_html__( 'Eilmeldung im Ticker', 'merzenich-aktuell' ),
		checked( get_post_meta( $post->ID, 'ma8_lead', true ), 1, false ),
		esc_html__( 'Aufmacher der Startseite', 'merzenich-aktuell' )
	);

	if ( 'ma_event' === $post->post_type ) {
		echo '<hr><h3>' . esc_html__( 'Veranstaltungsdaten', 'merzenich-aktuell' ) . '</h3><div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">';
		$event_fields = array(
			'ma8_event_start'     => __( 'Beginn', 'merzenich-aktuell' ),
			'ma8_event_end'       => __( 'Ende (optional)', 'merzenich-aktuell' ),
			'ma8_event_location'  => __( 'Ort / Location', 'merzenich-aktuell' ),
			'ma8_event_organizer' => __( 'Veranstalter', 'merzenich-aktuell' ),
			'ma8_event_category'  => __( 'Kategorie (Sport, Fest, Rathaus …)', 'merzenich-aktuell' ),
		);
		foreach ( $event_fields as $key => $label ) {
			$value = get_post_meta( $post->ID, $key, true );
			$type_attr = in_array( $key, array( 'ma8_event_start', 'ma8_event_end' ), true ) ? 'datetime-local' : 'text';
			printf(
				'<p><label for="%1$s"><strong>%2$s</strong></label><br><input type="%3$s" id="%1$s" name="%1$s" value="%4$s" style="width:100%%"></p>',
				esc_attr( $key ),
				esc_html( $label ),
				esc_attr( $type_attr ),
				esc_attr( $value )
			);
		}
		echo '</div>';
	}

	if ( 'ma_club' === $post->post_type ) {
		echo '<hr><h3>' . esc_html__( 'Vereinsdaten', 'merzenich-aktuell' ) . '</h3><div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">';
		foreach ( array( 'ma8_club_category' => __( 'Sparte', 'merzenich-aktuell' ), 'ma8_club_url' => __( 'Offizielle Seite', 'merzenich-aktuell' ) ) as $key => $label ) {
			printf(
				'<p><label for="%1$s"><strong>%2$s</strong></label><br><input id="%1$s" name="%1$s" value="%3$s" style="width:100%%"></p>',
				esc_attr( $key ),
				esc_html( $label ),
				esc_attr( get_post_meta( $post->ID, $key, true ) )
			);
		}
		echo '</div>';
	}

	if ( 'post' === $post->post_type ) {
		echo '<hr><h3>' . esc_html__( 'Format &amp; Datierung', 'merzenich-aktuell' ) . '</h3><div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">';

		$format = get_post_meta( $post->ID, 'ma8_format', true );
		echo '<p><label for="ma8_format"><strong>' . esc_html__( 'Format', 'merzenich-aktuell' ) . '</strong></label><br><select id="ma8_format" name="ma8_format">';
		foreach ( ma8_formats() as $val => $label ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $val ), selected( $format, $val, false ), esc_html( $label ) );
		}
		echo '</select></p>';

		printf(
			'<p><label for="ma8_retrieved"><strong>%s</strong></label><br><input type="date" id="ma8_retrieved" name="ma8_retrieved" value="%s" style="width:100%%"></p>',
			esc_html__( 'Abrufdatum der Quelle', 'merzenich-aktuell' ),
			esc_attr( get_post_meta( $post->ID, 'ma8_retrieved', true ) )
		);
		echo '</div>';
	}

	if ( 'ma_job' === $post->post_type ) {
		echo '<hr><h3>' . esc_html__( 'Stellendaten', 'merzenich-aktuell' ) . '</h3><div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">';

		foreach (
			array(
				'ma8_job_company' => __( 'Betrieb', 'merzenich-aktuell' ),
				'ma8_job_start'   => __( 'Beginn (z. B. „sofort“)', 'merzenich-aktuell' ),
			) as $key => $label
		) {
			printf(
				'<p><label for="%1$s"><strong>%2$s</strong></label><br><input id="%1$s" name="%1$s" value="%3$s" style="width:100%%"></p>',
				esc_attr( $key ),
				esc_html( $label ),
				esc_attr( get_post_meta( $post->ID, $key, true ) )
			);
		}

		$job_art = get_post_meta( $post->ID, 'ma8_job_art', true );
		echo '<p><label for="ma8_job_art"><strong>' . esc_html__( 'Art der Stelle', 'merzenich-aktuell' ) . '</strong></label><br><select id="ma8_job_art" name="ma8_job_art">';
		foreach ( ma8_job_types() as $val => $label ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $val ), selected( $job_art, $val, false ), esc_html( $label ) );
		}
		echo '</select></p>';

		foreach (
			array(
				'ma8_job_posted'      => __( 'Veröffentlicht am', 'merzenich-aktuell' ),
				'ma8_job_valid_until' => __( 'Gültig bis', 'merzenich-aktuell' ),
			) as $key => $label
		) {
			printf(
				'<p><label for="%1$s"><strong>%2$s</strong></label><br><input type="date" id="%1$s" name="%1$s" value="%3$s" style="width:100%%"></p>',
				esc_attr( $key ),
				esc_html( $label ),
				esc_attr( get_post_meta( $post->ID, $key, true ) )
			);
		}
		echo '</div>';

		// Ehrenamtliche Stellen sind standardmäßig keine kostenpflichtige Anzeige.
		$sponsored_meta = get_post_meta( $post->ID, 'ma8_job_sponsored', true );
		$sponsored      = ( '' === $sponsored_meta ) ? ( 'Ehrenamt' !== $job_art ? 1 : 0 ) : $sponsored_meta;
		printf(
			'<p><label><input type="checkbox" name="ma8_job_sponsored" value="1" %s> <strong>%s</strong></label></p>',
			checked( $sponsored, 1, false ),
			esc_html__( 'Anzeige (kostenpflichtige Stellenanzeige)', 'merzenich-aktuell' )
		);
	}

	if ( 'ma_business' === $post->post_type ) {
		echo '<hr><h3>' . esc_html__( 'Betriebsdaten', 'merzenich-aktuell' ) . '</h3><div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">';

		foreach (
			array(
				'ma8_biz_category' => __( 'Branche / Kategorie', 'merzenich-aktuell' ),
				'ma8_biz_website'  => __( 'Website', 'merzenich-aktuell' ),
				'ma8_biz_phone'    => __( 'Telefon', 'merzenich-aktuell' ),
			) as $key => $label
		) {
			printf(
				'<p><label for="%1$s"><strong>%2$s</strong></label><br><input id="%1$s" name="%1$s" value="%3$s" style="width:100%%"></p>',
				esc_attr( $key ),
				esc_html( $label ),
				esc_attr( get_post_meta( $post->ID, $key, true ) )
			);
		}
		echo '</div>';

		printf(
			'<p><label><input type="checkbox" name="ma8_biz_sponsored" value="1" %s> <strong>%s</strong></label></p>',
			checked( get_post_meta( $post->ID, 'ma8_biz_sponsored', true ), 1, false ),
			esc_html__( 'Anzeige (gesponserter Eintrag)', 'merzenich-aktuell' )
		);
	}

	echo '<p style="background:#f6efdd;border-left:3px solid #c8a24e;padding:10px 12px;margin-top:14px"><strong>' . esc_html__( 'Redaktionsstandard:', 'merzenich-aktuell' ) . '</strong> ' .
		esc_html__( 'Reale Ereignisse nie mit generierten Ersatzbildern bebildern. Originalbild, Veranstaltungsbild, Quellenmotiv und Archivbild klar unterscheiden. Bei Übernahmen immer eigenen redaktionellen Mehrwert liefern.', 'merzenich-aktuell' ) . '</p>';
}

function ma8_check_box( $post ) {
	$checks = array(
		__( 'Quelle hinterlegt', 'merzenich-aktuell' )   => (bool) get_post_meta( $post->ID, 'ma8_source_url', true ),
		__( 'Bild vorhanden', 'merzenich-aktuell' )      => has_post_thumbnail( $post->ID ) || get_post_meta( $post->ID, 'ma8_external_image', true ),
		__( 'Bildcredit', 'merzenich-aktuell' )          => (bool) get_post_meta( $post->ID, 'ma8_image_credit', true ),
		__( 'Bildbeschreibung', 'merzenich-aktuell' )    => (bool) get_post_meta( $post->ID, 'ma8_image_alt', true ),
		__( 'Unterzeile', 'merzenich-aktuell' )          => get_post_meta( $post->ID, 'ma8_dek', true ) || has_excerpt( $post->ID ),
		__( 'Ressort zugeordnet', 'merzenich-aktuell' )  => has_category( '', $post->ID ),
		__( 'Ortsteil zugeordnet', 'merzenich-aktuell' ) => has_term( '', 'ma_district', $post->ID ),
	);

	echo '<ul style="margin:0">';
	foreach ( $checks as $label => $ok ) {
		printf(
			'<li style="margin:6px 0;color:%s">%s %s</li>',
			$ok ? '#18794e' : '#b32d2e',
			$ok ? '&#10003;' : '!',
			esc_html( $label )
		);
	}
	echo '</ul><p><small>' . esc_html__( 'Der Check blockiert nicht, zeigt aber fehlende Pflichtangaben sofort an.', 'merzenich-aktuell' ) . '</small></p>';
}

function ma8_save_meta( $post_id ) {
	if ( ! isset( $_POST['ma8_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['ma8_nonce'] ) ), 'ma8_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$textareas = array( 'ma8_dek', 'ma8_facts', 'ma8_seo_desc' );
	$urls      = array( 'ma8_source_url', 'ma8_external_image', 'ma8_club_url', 'ma8_biz_website' );
	$keys      = array_merge(
		array_keys( ma8_editorial_fields() ),
		array(
			'ma8_image_type',
			'ma8_event_start',
			'ma8_event_end',
			'ma8_event_location',
			'ma8_event_organizer',
			'ma8_event_category',
			'ma8_club_category',
			'ma8_club_url',
			'ma8_format',
			'ma8_retrieved',
			'ma8_job_company',
			'ma8_job_art',
			'ma8_job_posted',
			'ma8_job_valid_until',
			'ma8_job_start',
			'ma8_biz_category',
			'ma8_biz_website',
			'ma8_biz_phone',
		)
	);

	foreach ( $keys as $key ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] );
		if ( in_array( $key, $urls, true ) ) {
			$value = esc_url_raw( $raw );
		} elseif ( in_array( $key, $textareas, true ) ) {
			$value = sanitize_textarea_field( $raw );
		} else {
			$value = sanitize_text_field( $raw );
		}
		update_post_meta( $post_id, $key, $value );
	}

	foreach ( array( 'ma8_featured', 'ma8_breaking', 'ma8_lead' ) as $flag ) {
		update_post_meta( $post_id, $flag, empty( $_POST[ $flag ] ) ? 0 : 1 );
	}

	// Nur bei den jeweils passenden Inhaltstypen speichern, um keine fremden Meta-Felder zu erzeugen.
	$post_type = get_post_type( $post_id );
	if ( 'ma_job' === $post_type ) {
		update_post_meta( $post_id, 'ma8_job_sponsored', empty( $_POST['ma8_job_sponsored'] ) ? 0 : 1 );
	}
	if ( 'ma_business' === $post_type ) {
		update_post_meta( $post_id, 'ma8_biz_sponsored', empty( $_POST['ma8_biz_sponsored'] ) ? 0 : 1 );
	}

	// Nur ein Aufmacher gleichzeitig.
	if ( ! empty( $_POST['ma8_lead'] ) ) {
		$others = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => -1,
				'post__not_in'   => array( $post_id ),
				'meta_key'       => 'ma8_lead',
				'meta_value'     => 1,
				'fields'         => 'ids',
			)
		);
		foreach ( $others as $other ) {
			update_post_meta( $other, 'ma8_lead', 0 );
		}
	}
}
add_action( 'save_post', 'ma8_save_meta' );

/* -------------------------------------------------------------------------
 * Ausgabe-Helfer (Gegenstücke zu den Funktionen der statischen Version)
 * ---------------------------------------------------------------------- */

/**
 * Bildadresse aufloesen. Die mitgelieferten Startbilder stehen als relativer Pfad
 * /assets/uploads/... im Startinhalt und liegen im Theme; sie werden hier auf die
 * Theme-Adresse gehoben. Eigene Bilder aus der Mediathek und vollstaendige
 * externe Adressen bleiben unveraendert.
 */
function ma8_img_url( $url ) {
	$url = (string) $url;
	if ( '' === $url ) {
		return '';
	}
	if ( 0 === strpos( $url, '/assets/' ) ) {
		return get_theme_file_uri( ltrim( $url, '/' ) );
	}
	return $url;
}

function ma8_img( $post_id = null, $size = 'ma8-card' ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail_url( $post_id, $size );
	}
	return ma8_img_url( get_post_meta( $post_id, 'ma8_external_image', true ) );
}

function ma8_dek( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$dek     = get_post_meta( $post_id, 'ma8_dek', true );
	return $dek ? $dek : get_the_excerpt( $post_id );
}

function ma8_kicker( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$kicker  = get_post_meta( $post_id, 'ma8_kicker', true );
	if ( $kicker ) {
		return $kicker;
	}
	$cats = get_the_category( $post_id );
	return $cats ? $cats[0]->name : __( 'Aktuell', 'merzenich-aktuell' );
}

function ma8_district( $post_id = null ) {
	$terms = wp_get_post_terms( $post_id ? $post_id : get_the_ID(), 'ma_district' );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
}

function ma8_image_type_label( $post_id = null ) {
	$types = ma8_image_types();
	$value = get_post_meta( $post_id ? $post_id : get_the_ID(), 'ma8_image_type', true );
	$value = $value ? $value : 'source';
	$short = array( 'original' => __( 'Originalbild', 'merzenich-aktuell' ), 'event' => __( 'Offizielles Veranstaltungsbild', 'merzenich-aktuell' ), 'source' => __( 'Quellenmotiv', 'merzenich-aktuell' ), 'archive' => __( 'Archivbild', 'merzenich-aktuell' ), 'logo' => __( 'Offizielles Vereinslogo', 'merzenich-aktuell' ) );
	return isset( $short[ $value ] ) ? $short[ $value ] : $types['source'];
}

function ma8_image_alt( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$alt     = get_post_meta( $post_id, 'ma8_image_alt', true );
	return $alt ? $alt : get_the_title( $post_id );
}

/** Bildbaustein mit Platzhalter, falls ein externes Bild nicht mehr lädt. */
function ma8_media( $post_id = null, $size = 'ma8-card', $args = array() ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$src     = ma8_img( $post_id, $size );
	$classes = 'media';
	// Ein Wappen oder Logo darf nicht auf Formatfuellung beschnitten werden.
	// Die Netlify-Fassung liest dafuer das Feld fit aus dem Inhalt; hier steht
	// dieselbe Angabe als ma8_image_fit, ersatzweise entscheidet der Bildtyp.
	$ma8_fit = get_post_meta( $post_id, 'ma8_image_fit', true );
	if ( ! $ma8_fit && 'logo' === get_post_meta( $post_id, 'ma8_image_type', true ) ) {
		$ma8_fit = 'contain';
	}
	if ( ! empty( $args['contain'] ) || 'contain' === $ma8_fit ) {
		$classes .= ' contain';
	}
	// Ohne Bild wird keine leere Fläche gezeigt, sondern eine Textkachel mit Ressort und Quelle.
	if ( ! $src ) {
		$tk    = ! empty( $args['tk'] ) ? $args['tk'] : ma8_kicker( $post_id );
		$tksub = ! empty( $args['tksub'] ) ? $args['tksub'] : get_post_meta( $post_id, 'ma8_source_name', true );
		$tksub = $tksub ? $tksub : get_bloginfo( 'name' );
		return '<div class="' . esc_attr( $classes . ' textcard' ) . '"><span class="tk"><b>' . esc_html( $tk ) . '</b><span>' . esc_html( $tksub ) . '</span></span></div>';
	}
	$out = '<div class="' . esc_attr( $classes ) . '">';
	if ( $src ) {
		$out .= sprintf(
			'<img src="%s" alt="%s" loading="%s" decoding="async" referrerpolicy="no-referrer">',
			esc_url( $src ),
			esc_attr( ma8_image_alt( $post_id ) ),
			empty( $args['eager'] ) ? 'lazy' : 'eager'
		);
	}
	$out .= '<span class="ph">' . esc_html__( 'Bild folgt · Quelle prüfen', 'merzenich-aktuell' ) . '</span>';
	if ( ! empty( $args['badge'] ) ) {
		$out .= '<span class="badge">' . esc_html( $args['badge'] ) . '</span>';
	}
	return $out . '</div>';
}

function ma8_kicker_line( $post_id = null ) {
	$post_id  = $post_id ? $post_id : get_the_ID();
	$district = ma8_district( $post_id );
	$out      = '<span class="kicker">' . esc_html( ma8_kicker( $post_id ) );
	if ( $district ) {
		$out .= '<span class="dist">' . esc_html( $district ) . '</span>';
	}
	return $out . '</span>';
}

function ma8_source_link( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$url     = get_post_meta( $post_id, 'ma8_source_url', true );
	if ( ! $url ) {
		return '';
	}
	$name = get_post_meta( $post_id, 'ma8_source_name', true );
	$name = $name ? $name : __( 'Originalquelle', 'merzenich-aktuell' );
	return sprintf(
		'<span class="src"><a href="%s" target="_blank" rel="noopener nofollow">%s %s &#8599;</a></span>',
		esc_url( $url ),
		esc_html__( 'Quelle:', 'merzenich-aktuell' ),
		esc_html( $name )
	);
}

function ma8_credit_line( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$credit  = get_post_meta( $post_id, 'ma8_image_credit', true );
	$credit  = $credit ? $credit : __( 'Credit folgt', 'merzenich-aktuell' );
	$left    = has_post_thumbnail( $post_id ) ? '<span>' . esc_html( ma8_image_type_label( $post_id ) . ' · ' . $credit ) . '</span>' : '';
	return '<div class="creditline">' . $left . ma8_source_link( $post_id ) . '</div>';
}

function ma8_facts_list( $post_id = null ) {
	$raw = get_post_meta( $post_id ? $post_id : get_the_ID(), 'ma8_facts', true );
	if ( ! $raw ) {
		return '';
	}
	$lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) );
	if ( ! $lines ) {
		return '';
	}
	$out = '<div class="facts"><h2>' . esc_html__( 'Das Wichtigste in Kürze', 'merzenich-aktuell' ) . '</h2><ul>';
	foreach ( $lines as $line ) {
		$out .= '<li>' . esc_html( $line ) . '</li>';
	}
	return $out . '</ul></div>';
}

/** Datum in deutscher Ortszeit. */
function ma8_dt( $post_id = null, $format = 'd.m.Y · H:i' ) {
	return wp_date( $format, get_post_time( 'U', true, $post_id ? $post_id : get_the_ID() ) );
}

function ma8_event_ts( $post_id ) {
	$start = get_post_meta( $post_id, 'ma8_event_start', true );
	return $start ? strtotime( $start ) : get_post_time( 'U', true, $post_id );
}

/** Kommende Veranstaltungen, chronologisch. */
function ma8_upcoming_events( $limit = 5 ) {
	return get_posts(
		array(
			'post_type'      => 'ma_event',
			'posts_per_page' => $limit,
			'meta_key'       => 'ma8_event_start',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => 'ma8_event_start',
					'value'   => wp_date( 'Y-m-d\TH:i' ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
			),
		)
	);
}

/* -------------------------------------------------------------------------
 * Reichweite für "Meistgelesen" (datensparsam, ohne externe Dienste)
 * ---------------------------------------------------------------------- */

function ma8_track_view() {
	if ( ! is_singular( 'post' ) || is_admin() || wp_doing_ajax() || is_user_logged_in() ) {
		return;
	}
	$post_id = get_queried_object_id();
	$key     = 'ma8_views_' . wp_date( 'Ymd' );
	update_post_meta( $post_id, $key, (int) get_post_meta( $post_id, $key, true ) + 1 );
}
add_action( 'template_redirect', 'ma8_track_view', 20 );

function ma8_popular_ids( $limit = 5 ) {
	$candidates = get_posts(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 60,
			'date_query'     => array( array( 'after' => '21 days ago' ) ),
			'fields'         => 'ids',
		)
	);

	$keys   = array();
	for ( $i = 0; $i < 3; $i++ ) {
		$keys[] = 'ma8_views_' . wp_date( 'Ymd', strtotime( "-{$i} day" ) );
	}

	$scores = array();
	foreach ( $candidates as $id ) {
		$sum = 0;
		foreach ( $keys as $key ) {
			$sum += (int) get_post_meta( $id, $key, true );
		}
		$scores[ $id ] = $sum;
	}
	arsort( $scores );
	$top = array_slice( array_keys( $scores ), 0, $limit );

	// Solange keine Zugriffe vorliegen: Top-Themen der Redaktion zeigen.
	if ( ! array_sum( $scores ) ) {
		$featured = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => $limit,
				'meta_key'       => 'ma8_featured',
				'meta_value'     => 1,
				'fields'         => 'ids',
			)
		);
		return $featured ? $featured : array_slice( $candidates, 0, $limit );
	}
	return $top;
}

/* -------------------------------------------------------------------------
 * Werbeplätze — standardmäßig aus
 * ---------------------------------------------------------------------- */

function ma8_ad_slots() {
	return array(
		'home_top'      => __( 'Startseite Billboard (970 × 250)', 'merzenich-aktuell' ),
		'home_mid'      => __( 'Startseite Content-Banner (970 × 180)', 'merzenich-aktuell' ),
		'archive_mid'   => __( 'Ressortseiten', 'merzenich-aktuell' ),
		'sidebar'       => __( 'Sidebar (300 × 250 / 300 × 600)', 'merzenich-aktuell' ),
		'in_article'    => __( 'Im Artikel (728 × 90)', 'merzenich-aktuell' ),
		'footer_banner' => __( 'Vor dem Footer', 'merzenich-aktuell' ),
	);
}

/** Zwei unabhängige Werbekunden statt eines einzelnen Textfelds — jeder mit eigenem Namen, Ziel-URL und Bild. */
function ma8_ad_defaults() {
	return array(
		array(
			'name'  => 'KBS Management GmbH',
			'url'   => 'https://kbs-management.tv/',
			'image' => '',
		),
		array(
			'name'  => 'AJ Sports Entertainment',
			'url'   => '',
			'image' => '',
		),
	);
}

function ma8_settings() {
	$default_ads = ma8_ad_defaults();
	$defaults    = array(
		'enabled'         => 0,
		'ads'             => $default_ads,
		'whatsapp'        => '',
		'editorial_stand' => '',
		'form_email'      => '',
	);
	foreach ( array_keys( ma8_ad_slots() ) as $slot ) {
		$defaults[ $slot ]           = 0;
		$defaults[ $slot . '_code' ] = '';
	}

	$settings = wp_parse_args( (array) get_option( 'ma8_settings', array() ), $defaults );

	// Ältere Installationen kannten "ads" noch als kommagetrennten Text — auf zwei eigenständige Datensätze heben.
	if ( ! is_array( $settings['ads'] ) ) {
		$settings['ads'] = $default_ads;
	}
	for ( $i = 0; $i < 2; $i++ ) {
		$settings['ads'][ $i ] = wp_parse_args( isset( $settings['ads'][ $i ] ) ? (array) $settings['ads'][ $i ] : array(), $default_ads[ $i ] );
	}
	$settings['ads'] = array_slice( $settings['ads'], 0, 2 );

	return $settings;
}

function ma8_sanitize_settings( $input ) {
	$out            = ma8_settings();
	$out['enabled'] = empty( $input['enabled'] ) ? 0 : 1;

	$ads = array();
	for ( $i = 0; $i < 2; $i++ ) {
		$raw   = isset( $input['ads'][ $i ] ) ? (array) $input['ads'][ $i ] : array();
		$ads[] = array(
			'name'  => isset( $raw['name'] ) ? sanitize_text_field( $raw['name'] ) : '',
			'url'   => isset( $raw['url'] ) ? esc_url_raw( $raw['url'] ) : '',
			'image' => isset( $raw['image'] ) ? esc_url_raw( $raw['image'] ) : '',
		);
	}
	$out['ads'] = $ads;

	$out['whatsapp'] = isset( $input['whatsapp'] ) ? esc_url_raw( $input['whatsapp'] ) : '';
	$out['editorial_stand'] = isset( $input['editorial_stand'] ) ? sanitize_text_field( $input['editorial_stand'] ) : '';
	$out['form_email']      = isset( $input['form_email'] ) ? sanitize_email( $input['form_email'] ) : '';
	foreach ( array_keys( ma8_ad_slots() ) as $slot ) {
		$out[ $slot ]           = empty( $input[ $slot ] ) ? 0 : 1;
		$out[ $slot . '_code' ] = isset( $input[ $slot . '_code' ] ) ? wp_kses_post( $input[ $slot . '_code' ] ) : '';
	}
	return $out;
}

function ma8_register_settings() {
	register_setting( 'ma8_settings_group', 'ma8_settings', array( 'sanitize_callback' => 'ma8_sanitize_settings' ) );
}
add_action( 'admin_init', 'ma8_register_settings' );

/**
 * Werbeplatz ausgeben. Ein eigener Bannercode hat Vorrang; ohne Code erscheinen die beiden
 * Werbekunden als eigenständige, unabhängige Anzeigen nebeneinander (keine gemeinsame Leiste).
 */
function ma8_ad( $slot ) {
	$settings = ma8_settings();

	// Eigener Bannercode hat Vorrang, sobald er im Backend hinterlegt ist.
	if ( ! empty( $settings['enabled'] ) && ! empty( $settings[ $slot ] ) ) {
		$code = trim( $settings[ $slot . '_code' ] );
		if ( $code ) {
			echo '<aside class="ad-slot" data-slot="' . esc_attr( $slot ) . '"><span class="lbl">' . esc_html__( 'Werbung', 'merzenich-aktuell' ) . '</span>' . wp_kses_post( $code ) . '</aside>';
			return;
		}
	}

	// Ohne eigenen Code erscheinen die beiden Werbekunden als eigenständige Anzeigen.
	$ads = array_filter(
		(array) $settings['ads'],
		static function ( $ad ) {
			return ! empty( $ad['name'] );
		}
	);
	if ( ! $ads ) {
		return;
	}

	// "narrow" nur in der Seitenspalte, sonst zwei Anzeigen nebeneinander ("wide").
	$wide = ( 'sidebar' !== $slot );
	$w    = $wide ? 728 : 300;
	$h    = $wide ? 90 : 104;

	$out = '';
	foreach ( $ads as $ad ) {
		$name  = $ad['name'];
		$url   = ! empty( $ad['url'] ) ? $ad['url'] : '';
		$image = ! empty( $ad['image'] ) ? $ad['image'] : '';

		if ( $image ) {
			$inner = sprintf(
				'<img src="%s" alt="%s" loading="lazy" width="%d" height="%d">',
				esc_url( $image ),
				esc_attr( $name ),
				$w,
				$h
			);
		} else {
			$inner = '<span class="ad-name">' . esc_html( $name ) . '<small>' . esc_html__( 'Merzenich · Kreis Düren', 'merzenich-aktuell' ) . '</small></span>';
		}

		$out .= '<div class="ad"><span class="ad-label">' . esc_html__( 'Anzeige', 'merzenich-aktuell' ) . '</span>';
		if ( $url ) {
			$out .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener sponsored">' . $inner . '</a>';
		} else {
			$out .= '<div class="ad-in">' . $inner . '</div>';
		}
		$out .= '</div>';
	}

	echo '<div class="ad-row ' . esc_attr( $wide ? 'wide' : 'narrow' ) . '" data-slot="' . esc_attr( $slot ) . '">' . $out . '</div>';
}

/**
 * Liefert abwechselnd einen der beiden Werbekunden für die native Anzeige im Nachrichtenstrom
 * (statt wie zuvor immer nur den ersten Werbekunden zu zeigen).
 */
function ma8_native_ad_advertiser( $index = 0 ) {
	$settings = ma8_settings();
	$ads      = array_values(
		array_filter(
			(array) $settings['ads'],
			static function ( $ad ) {
				return ! empty( $ad['name'] );
			}
		)
	);
	if ( ! $ads ) {
		return null;
	}
	return $ads[ $index % count( $ads ) ];
}

/**
 * Native Anzeige im Nachrichtenstrom: EINE Anzeige im Look einer Meldungskarte
 * statt des zweispaltigen Banner-Systems aus ma8_ad(). $index steuert, welcher
 * der beiden Werbekunden gezeigt wird (siehe ma8_native_ad_advertiser()).
 */
function ma8_native_ad( $index = 0 ) {
	$ad = ma8_native_ad_advertiser( $index );
	if ( ! $ad ) {
		return;
	}
	$name  = $ad['name'];
	$url   = ! empty( $ad['url'] ) ? $ad['url'] : '';
	$image = ! empty( $ad['image'] ) ? $ad['image'] : '';
	$href  = $url ? $url : home_url( '/werben/' );
	$target = $url ? ' target="_blank" rel="noopener sponsored"' : '';

	$media = $image
		? sprintf( '<img src="%s" alt="%s" loading="lazy" width="728" height="90">', esc_url( $image ), esc_attr( $name ) )
		: '<span class="ad-name">' . esc_html( $name ) . '<small>' . esc_html__( 'Merzenich · Kreis Düren', 'merzenich-aktuell' ) . '</small></span>';
	?>
	<article class="feed-row native-ad">
		<a class="feed-img" href="<?php echo esc_url( $href ); ?>"<?php echo $target; ?> tabindex="-1" aria-hidden="true">
			<div class="media none ad-media"><?php echo $media; ?></div>
		</a>
		<div class="feed-copy">
			<span class="kicker"><?php esc_html_e( 'Anzeige', 'merzenich-aktuell' ); ?><span class="dist"><?php echo esc_html( $name ); ?></span></span>
			<h3><a href="<?php echo esc_url( $href ); ?>"<?php echo $target; ?>><?php echo esc_html( ! empty( $ad['headline'] ) ? $ad['headline'] : $name ); ?></a></h3>
			<p class="dek"><?php echo esc_html( ! empty( $ad['text'] ) ? $ad['text'] : __( 'Partner von Merzenich Aktuell. Gekennzeichnete Anzeige, ohne Einfluss auf die Redaktion.', 'merzenich-aktuell' ) ); ?></p>
			<div class="meta"><a href="<?php echo esc_url( home_url( '/werben/' ) ); ?>"><?php esc_html_e( 'Hier werben', 'merzenich-aktuell' ); ?></a></div>
		</div>
	</article>
	<?php
}

/* -------------------------------------------------------------------------
 * Adminmenü: News Desk und Einstellungen
 * ---------------------------------------------------------------------- */

function ma8_admin_menu() {
	add_menu_page( __( 'News Desk', 'merzenich-aktuell' ), __( 'News Desk', 'merzenich-aktuell' ), 'edit_posts', 'ma8-newsdesk', 'ma8_newsdesk_page', 'dashicons-megaphone', 3 );
	add_theme_page( __( 'Merzenich Aktuell', 'merzenich-aktuell' ), __( 'Merzenich Aktuell', 'merzenich-aktuell' ), 'manage_options', 'ma8-settings', 'ma8_settings_page' );
}
add_action( 'admin_menu', 'ma8_admin_menu' );

function ma8_newsdesk_page() {
	$incomplete = get_posts(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 20,
			'meta_query'     => array(
				'relation' => 'OR',
				array( 'key' => 'ma8_source_url', 'compare' => 'NOT EXISTS' ),
				array( 'key' => 'ma8_source_url', 'value' => '', 'compare' => '=' ),
			),
		)
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Merzenich Aktuell — News Desk', 'merzenich-aktuell' ); ?></h1>
		<p>
			<a class="button button-primary" href="post-new.php"><?php esc_html_e( 'Neue Meldung', 'merzenich-aktuell' ); ?></a>
			<a class="button" href="post-new.php?post_type=ma_event"><?php esc_html_e( 'Neuer Termin', 'merzenich-aktuell' ); ?></a>
			<a class="button" href="post-new.php?post_type=ma_club"><?php esc_html_e( 'Neuer Verein', 'merzenich-aktuell' ); ?></a>
			<a class="button" href="edit.php"><?php esc_html_e( 'Alle Meldungen', 'merzenich-aktuell' ); ?></a>
		</p>

		<h2><?php esc_html_e( 'Veröffentlichen in sechs Schritten', 'merzenich-aktuell' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'Quelle prüfen und im Feld Quellen-URL hinterlegen.', 'merzenich-aktuell' ); ?></li>
			<li><?php esc_html_e( 'Bild setzen, Bildtyp wählen, Credit und Bildbeschreibung eintragen.', 'merzenich-aktuell' ); ?></li>
			<li><?php esc_html_e( 'Dachzeile und eigene Unterzeile schreiben, nicht die Quelle kopieren.', 'merzenich-aktuell' ); ?></li>
			<li><?php esc_html_e( 'Ressort und Ortsteil zuordnen.', 'merzenich-aktuell' ); ?></li>
			<li><?php esc_html_e( 'Drei Punkte für Das Wichtigste in Kürze notieren.', 'merzenich-aktuell' ); ?></li>
			<li><?php esc_html_e( 'Nach dem Veröffentlichen Bild, Datum und Quelle im Frontend kontrollieren.', 'merzenich-aktuell' ); ?></li>
		</ol>

		<h2><?php esc_html_e( 'Meldungen ohne Quellenangabe', 'merzenich-aktuell' ); ?></h2>
		<?php if ( $incomplete ) : ?>
			<ul style="list-style:disc;padding-left:20px">
				<?php foreach ( $incomplete as $post ) : ?>
					<li><a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p><?php esc_html_e( 'Alle veröffentlichten Meldungen führen eine Quelle. Sehr gut.', 'merzenich-aktuell' ); ?></p>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Sichtbarkeit bei Google', 'merzenich-aktuell' ); ?></h2>
		<p><?php esc_html_e( 'Das Theme liefert NewsArticle- und Event-Schema, Breadcrumbs, OpenGraph, große Vorschaubilder, sichtbare Datums- und Autorenangaben, RSS und eine News-Sitemap unter /news-sitemap.xml. Jede Meldung braucht vor der Veröffentlichung ein Bild mit dokumentiertem Credit.', 'merzenich-aktuell' ); ?></p>
	</div>
	<?php
}

function ma8_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$settings = ma8_settings();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Merzenich Aktuell — Einstellungen', 'merzenich-aktuell' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'ma8_settings_group' ); ?>

			<h2><?php esc_html_e( 'Kopfzeile', 'merzenich-aktuell' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><label for="ma8_whatsapp"><?php esc_html_e( 'WhatsApp-Kanal (URL)', 'merzenich-aktuell' ); ?></label></th>
					<td><input id="ma8_whatsapp" name="ma8_settings[whatsapp]" value="<?php echo esc_attr( $settings['whatsapp'] ); ?>" class="regular-text" placeholder="https://whatsapp.com/channel/…"></td>
				</tr>
				<tr>
					<th><label for="ma8_stand"><?php esc_html_e( 'Redaktionsstand-Hinweis', 'merzenich-aktuell' ); ?></label></th>
					<td><input id="ma8_stand" name="ma8_settings[editorial_stand]" value="<?php echo esc_attr( $settings['editorial_stand'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'leer lassen für automatische Uhrzeit', 'merzenich-aktuell' ); ?>"></td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Formulare', 'merzenich-aktuell' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><label for="ma8_form_email"><?php esc_html_e( 'Redaktionsadresse für Formulare', 'merzenich-aktuell' ); ?></label></th>
					<td>
						<input type="email" id="ma8_form_email" name="ma8_settings[form_email]" value="<?php echo esc_attr( $settings['form_email'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
						<p class="description"><?php esc_html_e( 'An diese Adresse gehen alle Einsendungen aus den Formularen. Leer lassen, dann wird die Administrationsadresse der Website verwendet.', 'merzenich-aktuell' ); ?></p>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Werbung', 'merzenich-aktuell' ); ?></h2>
			<p><label><input type="checkbox" name="ma8_settings[enabled]" value="1" <?php checked( $settings['enabled'], 1 ); ?>> <strong><?php esc_html_e( 'Werbung global aktivieren', 'merzenich-aktuell' ); ?></strong></label></p>

			<h3><?php esc_html_e( 'Werbekunden', 'merzenich-aktuell' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Jeder Werbekunde erscheint als eigenständige Anzeige mit eigenem Kennzeichen "Anzeige" — nicht als gemeinsame Namensleiste. In der nativen Anzeige im Nachrichtenstrom wechseln sich beide Werbekunden ab.', 'merzenich-aktuell' ); ?></p>
			<table class="form-table">
				<?php foreach ( $settings['ads'] as $i => $ad ) : ?>
					<tr>
						<th><?php printf( esc_html__( 'Werbekunde %d', 'merzenich-aktuell' ), $i + 1 ); ?></th>
						<td>
							<p>
								<label for="ma8_ad_<?php echo esc_attr( $i ); ?>_name"><?php esc_html_e( 'Name', 'merzenich-aktuell' ); ?></label><br>
								<input type="text" id="ma8_ad_<?php echo esc_attr( $i ); ?>_name" name="ma8_settings[ads][<?php echo esc_attr( $i ); ?>][name]" value="<?php echo esc_attr( $ad['name'] ); ?>" class="regular-text">
							</p>
							<p>
								<label for="ma8_ad_<?php echo esc_attr( $i ); ?>_url"><?php esc_html_e( 'Ziel-URL (optional)', 'merzenich-aktuell' ); ?></label><br>
								<input type="url" id="ma8_ad_<?php echo esc_attr( $i ); ?>_url" name="ma8_settings[ads][<?php echo esc_attr( $i ); ?>][url]" value="<?php echo esc_attr( $ad['url'] ); ?>" class="regular-text" placeholder="https://…">
							</p>
							<p>
								<label for="ma8_ad_<?php echo esc_attr( $i ); ?>_image"><?php esc_html_e( 'Bild-URL (optional)', 'merzenich-aktuell' ); ?></label><br>
								<input type="url" id="ma8_ad_<?php echo esc_attr( $i ); ?>_image" name="ma8_settings[ads][<?php echo esc_attr( $i ); ?>][image]" value="<?php echo esc_attr( $ad['image'] ); ?>" class="regular-text" placeholder="https://…">
							</p>
							<p class="description"><?php esc_html_e( 'Ohne Bild erscheint der Name als schlichte Textkachel.', 'merzenich-aktuell' ); ?></p>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>

			<h3><?php esc_html_e( 'Werbeplätze', 'merzenich-aktuell' ); ?></h3>
			<table class="form-table">
				<?php foreach ( ma8_ad_slots() as $slot => $label ) : ?>
					<tr>
						<th><?php echo esc_html( $label ); ?></th>
						<td>
							<label><input type="checkbox" name="ma8_settings[<?php echo esc_attr( $slot ); ?>]" value="1" <?php checked( $settings[ $slot ], 1 ); ?>> <?php esc_html_e( 'Platz aktiv', 'merzenich-aktuell' ); ?></label><br>
							<textarea rows="4" style="width:100%;max-width:760px" name="ma8_settings[<?php echo esc_attr( $slot ); ?>_code]"><?php echo esc_textarea( $settings[ $slot . '_code' ] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Ohne Banner-Code bleibt der Platz unsichtbar, es entsteht keine Lücke im Layout.', 'merzenich-aktuell' ); ?></p>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/** Zusatzspalten in der Beitragsliste. */
function ma8_admin_columns( $columns ) {
	$columns['ma8_source']   = __( 'Quelle', 'merzenich-aktuell' );
	$columns['ma8_image']    = __( 'Bildstatus', 'merzenich-aktuell' );
	$columns['ma8_district'] = __( 'Ortsteil', 'merzenich-aktuell' );
	return $columns;
}
add_filter( 'manage_posts_columns', 'ma8_admin_columns' );

function ma8_admin_column( $column, $post_id ) {
	if ( 'ma8_source' === $column ) {
		$name = get_post_meta( $post_id, 'ma8_source_name', true );
		echo $name ? esc_html( $name ) : '<strong style="color:#b32d2e">' . esc_html__( 'fehlt', 'merzenich-aktuell' ) . '</strong>';
	}
	if ( 'ma8_image' === $column ) {
		echo ma8_img( $post_id ) ? esc_html( ma8_image_type_label( $post_id ) ) : '<strong style="color:#b32d2e">' . esc_html__( 'kein Bild', 'merzenich-aktuell' ) . '</strong>';
	}
	if ( 'ma8_district' === $column ) {
		$district = ma8_district( $post_id );
		echo $district ? esc_html( $district ) : '&mdash;';
	}
}
add_action( 'manage_posts_custom_column', 'ma8_admin_column', 10, 2 );

/* -------------------------------------------------------------------------
 * SEO, OpenGraph und Schema.org
 * ---------------------------------------------------------------------- */

function ma8_meta_description() {
	if ( is_singular() ) {
		$post_id = get_queried_object_id();
		$desc    = get_post_meta( $post_id, 'ma8_seo_desc', true );
		return $desc ? $desc : ma8_dek( $post_id );
	}
	if ( is_home() || is_front_page() ) {
		return __( 'Nachrichten, Blaulicht, Sport, Veranstaltungen und Vereine aus Merzenich, Golzheim, Girbelsrath, Morschenich und dem Bürgewald. Jede Meldung mit Quelle und Bildcredit.', 'merzenich-aktuell' );
	}
	if ( is_category() || is_tax() ) {
		$desc = term_description();
		return $desc ? wp_strip_all_tags( $desc ) : get_bloginfo( 'description' );
	}
	return get_bloginfo( 'description' );
}

function ma8_organization_schema() {
	return array(
		'@type'       => 'NewsMediaOrganization',
		'@id'         => home_url( '/#organization' ),
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'email'       => get_option( 'admin_email' ),
		'logo'        => array(
			'@type'  => 'ImageObject',
			'url'    => get_template_directory_uri() . '/assets/logo-on-light.png',
			'width'  => 1200,
			'height' => 338,
		),
		'description' => __( 'Lokalredaktion für die Gemeinde Merzenich im Kreis Düren.', 'merzenich-aktuell' ),
		'areaServed'  => array( '@type' => 'AdministrativeArea', 'name' => 'Gemeinde Merzenich, Kreis Düren' ),
	);
}

function ma8_head_meta() {
	$description = wp_strip_all_tags( (string) ma8_meta_description() );
	$graph       = array( ma8_organization_schema() );

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	echo '<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1">' . "\n";
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	echo '<meta property="og:locale" content="de_DE">' . "\n";
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

	$default_image = get_template_directory_uri() . '/assets/og-default.jpg';

	if ( is_singular() ) {
		$post_id = get_queried_object_id();
		$image   = ma8_img( $post_id, 'ma8-hero' );
		$image   = $image ? $image : $default_image;
		$title   = get_post_meta( $post_id, 'ma8_seo_title', true );
		$title   = $title ? $title : get_the_title( $post_id );

		printf( '<meta property="og:type" content="article">' . "\n" );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( get_permalink( $post_id ) ) );
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		printf( '<meta property="og:image:alt" content="%s">' . "\n", esc_attr( ma8_image_alt( $post_id ) ) );
		printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );

		$tags = get_the_tags( $post_id );
		if ( $tags ) {
			printf( '<meta name="news_keywords" content="%s">' . "\n", esc_attr( implode( ', ', wp_list_pluck( $tags, 'name' ) ) ) );
		}

		if ( 'post' === get_post_type( $post_id ) ) {
			$cats    = get_the_category( $post_id );
			// Meldungen ohne Veröffentlichungsdatum der Quelle sind kein NewsArticle: Google verlangt dafür
			// ein belastbares datePublished, das hier gerade nicht vorliegt.
			$undated = (bool) get_post_meta( $post_id, 'ma8_undated', true );
			$article = array(
				'@type'            => $undated ? 'Article' : 'NewsArticle',
				'mainEntityOfPage' => array( '@type' => 'WebPage', '@id' => get_permalink( $post_id ) ),
				'headline'         => get_the_title( $post_id ),
				'description'      => $description,
				'image'            => array( $image ),
				'dateModified'     => get_the_modified_date( DATE_W3C, $post_id ),
				'author'           => array(
					'@type' => 'Person',
					'name'  => get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ),
					'url'   => get_author_posts_url( (int) get_post_field( 'post_author', $post_id ) ),
				),
				'publisher'        => array( '@id' => home_url( '/#organization' ) ),
				'articleSection'   => $cats ? $cats[0]->name : '',
				'inLanguage'       => 'de-DE',
				'isAccessibleForFree' => true,
			);
			if ( ! $undated ) {
				$article['datePublished'] = get_the_date( DATE_W3C, $post_id );
			}

			$source_url = get_post_meta( $post_id, 'ma8_source_url', true );
			if ( $source_url ) {
				$article['citation'] = array(
					'@type' => 'CreativeWork',
					'name'  => get_post_meta( $post_id, 'ma8_source_name', true ),
					'url'   => $source_url,
				);
			}

			$district = ma8_district( $post_id );
			if ( $district ) {
				$article['contentLocation'] = array(
					'@type'   => 'Place',
					'name'    => $district,
					'address' => array( '@type' => 'PostalAddress', 'addressLocality' => 'Merzenich', 'addressRegion' => 'NRW', 'postalCode' => '52399', 'addressCountry' => 'DE' ),
				);
			}
			$graph[] = $article;
		}

		if ( 'ma_event' === get_post_type( $post_id ) ) {
			$start = get_post_meta( $post_id, 'ma8_event_start', true );
			if ( $start ) {
				$location = get_post_meta( $post_id, 'ma8_event_location', true );
				$online   = (bool) preg_match( '/online|zoom/i', (string) $location );
				$graph[]  = array(
					'@type'               => 'Event',
					'name'                => get_the_title( $post_id ),
					'startDate'           => wp_date( DATE_W3C, strtotime( $start ) ),
					'endDate'             => get_post_meta( $post_id, 'ma8_event_end', true ) ? wp_date( DATE_W3C, strtotime( get_post_meta( $post_id, 'ma8_event_end', true ) ) ) : null,
					'eventStatus'         => 'https://schema.org/EventScheduled',
					'eventAttendanceMode' => $online ? 'https://schema.org/OnlineEventAttendanceMode' : 'https://schema.org/OfflineEventAttendanceMode',
					'location'            => $online
						? array( '@type' => 'VirtualLocation', 'url' => get_permalink( $post_id ) )
						: array(
							'@type'   => 'Place',
							'name'    => $location,
							'address' => array( '@type' => 'PostalAddress', 'streetAddress' => $location, 'addressLocality' => ma8_district( $post_id ) ? ma8_district( $post_id ) : 'Merzenich', 'postalCode' => '52399', 'addressCountry' => 'DE' ),
						),
					'description'         => $description,
					'organizer'           => array( '@type' => 'Organization', 'name' => get_post_meta( $post_id, 'ma8_event_organizer', true ) ),
					'image'               => array( $image ),
					'url'                 => get_permalink( $post_id ),
					'isAccessibleForFree' => true,
				);
			}
		}

		// Brotkrumen
		$items = array( array( '@type' => 'ListItem', 'position' => 1, 'name' => __( 'Start', 'merzenich-aktuell' ), 'item' => home_url( '/' ) ) );
		$cats  = get_the_category( $post_id );
		if ( $cats ) {
			$items[] = array( '@type' => 'ListItem', 'position' => 2, 'name' => $cats[0]->name, 'item' => get_category_link( $cats[0]->term_id ) );
		}
		$items[] = array( '@type' => 'ListItem', 'position' => count( $items ) + 1, 'name' => get_the_title( $post_id ), 'item' => get_permalink( $post_id ) );
		$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $items );

	} else {
		printf( '<meta property="og:type" content="website">' . "\n" );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( wp_get_document_title() ) );
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $default_image ) );
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( home_url( add_query_arg( array() ) ) ) );

		$graph[] = array(
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'name'            => get_bloginfo( 'name' ),
			'url'             => home_url( '/' ),
			'inLanguage'      => 'de-DE',
			'publisher'       => array( '@id' => home_url( '/#organization' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array( '@type' => 'EntryPoint', 'urlTemplate' => home_url( '/?s={search_term_string}' ) ),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	$payload = array( '@context' => 'https://schema.org', '@graph' => $graph );
	echo '<script type="application/ld+json">' . wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'ma8_head_meta', 2 );

/* -------------------------------------------------------------------------
 * News-Sitemap unter /news-sitemap.xml
 * ---------------------------------------------------------------------- */

function ma8_query_vars( $vars ) {
	$vars[] = 'ma8_news_sitemap';
	return $vars;
}
add_filter( 'query_vars', 'ma8_query_vars' );

function ma8_rewrites() {
	add_rewrite_rule( '^news-sitemap\.xml$', 'index.php?ma8_news_sitemap=1', 'top' );
}
add_action( 'init', 'ma8_rewrites' );

function ma8_render_news_sitemap() {
	if ( ! get_query_var( 'ma8_news_sitemap' ) ) {
		return;
	}
	status_header( 200 );
	header( 'Content-Type: application/xml; charset=UTF-8' );

	$query = new WP_Query(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 1000,
			'date_query'     => array( array( 'after' => '2 days ago' ) ),
			'orderby'        => 'date',
			'order'          => 'DESC',
			// Meldungen ohne Veröffentlichungsdatum der Quelle gehören nicht in die News-Sitemap.
			'meta_query'     => array(
				'relation' => 'OR',
				array( 'key' => 'ma8_undated', 'compare' => 'NOT EXISTS' ),
				array( 'key' => 'ma8_undated', 'value' => array( '', '0' ), 'compare' => 'IN' ),
			),
		)
	);

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$tags = get_the_tags();
		echo '<url><loc>' . esc_url( get_permalink() ) . '</loc><news:news><news:publication><news:name>' . esc_html( get_bloginfo( 'name' ) ) . '</news:name><news:language>de</news:language></news:publication>';
		echo '<news:publication_date>' . esc_html( get_the_date( DATE_W3C ) ) . '</news:publication_date><news:title>' . esc_html( get_the_title() ) . '</news:title>';
		if ( $tags ) {
			echo '<news:keywords>' . esc_html( implode( ', ', wp_list_pluck( $tags, 'name' ) ) ) . '</news:keywords>';
		}
		echo '</news:news></url>';
	}
	wp_reset_postdata();
	echo '</urlset>';
	exit;
}
add_action( 'template_redirect', 'ma8_render_news_sitemap' );

/**
 * WordPress haengt an Adressen ohne Dateiendung im Rewrite automatisch einen
 * Schraegstrich an und leitet mit 301 um. Fuer /news-sitemap.xml ist das falsch:
 * Suchmaschinen erwarten die Datei ohne abschliessenden Schraegstrich.
 */
function ma8_no_canonical_for_sitemap( $redirect_url ) {
	if ( get_query_var( 'ma8_news_sitemap' ) ) {
		return false;
	}
	return $redirect_url;
}
add_filter( 'redirect_canonical', 'ma8_no_canonical_for_sitemap' );

function ma8_robots_sitemap( $output ) {
	return $output . 'Sitemap: ' . home_url( '/news-sitemap.xml' ) . "\n";
}
add_filter( 'robots_txt', 'ma8_robots_sitemap' );

/* -------------------------------------------------------------------------
 * Suche
 * ---------------------------------------------------------------------- */

function ma8_search_index() {
	$index = array();
	$posts = get_posts( array( 'post_type' => array( 'post', 'ma_event', 'ma_club' ), 'posts_per_page' => 500 ) );
	foreach ( $posts as $post ) {
		$index[] = array(
			't' => get_the_title( $post ),
			'd' => wp_trim_words( (string) ma8_dek( $post->ID ), 30 ),
			'k' => ma8_kicker( $post->ID ),
			'g' => ma8_district( $post->ID ),
			'u' => get_permalink( $post ),
		);
	}
	return $index;
}

/** Auch Veranstaltungen und Vereine in der Suche berücksichtigen. */
function ma8_search_post_types( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		$query->set( 'post_type', array( 'post', 'ma_event', 'ma_club' ) );
	}
}
add_action( 'pre_get_posts', 'ma8_search_post_types' );

/* -------------------------------------------------------------------------
 * Aktivierung
 * ---------------------------------------------------------------------- */

function ma8_after_switch_theme() {
	ma8_register_content();
	ma8_seed_terms();
	ma8_rewrites();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'ma8_after_switch_theme' );

/**
 * Datumszeile. Meldungen ohne Veröffentlichungsdatum der Quelle werden als solche
 * gekennzeichnet, statt ein Datum zu suggerieren.
 */
function ma8_stamp( $post_id = null, $long = false ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( get_post_meta( $post_id, 'ma8_undated', true ) ) {
		// Ohne Abrufdatum der Quelle wird ersatzweise das Veröffentlichungsdatum des Posts gezeigt.
		$retrieved = get_post_meta( $post_id, 'ma8_retrieved', true );
		$ts        = $retrieved ? strtotime( $retrieved ) : false;
		$d         = $ts ? wp_date( 'd.m.Y', $ts ) : get_the_date( 'd.m.Y', $post_id );
		return sprintf( __( 'Ohne Datum · abgerufen %s', 'merzenich-aktuell' ), $d );
	}
	return ma8_dt( $post_id ) . ' Uhr';
}

/* ----------------------------------------------------------------------
 * Formulare — native Verarbeitung ohne Plugin
 *
 * Sieben Formulare (Meldung, Termin, Verein, Betrieb, Korrektur, Werbung,
 * Kontakt) werden über den Shortcode [ma_formular typ="meldung"] ausgegeben.
 * Die Felddefinitionen sind die einzige Quelle: Ausgabe, Prüfung und E-Mail
 * lesen aus derselben Tabelle, damit Beschriftung und Pflichtfeld nicht
 * auseinanderlaufen. Versand per wp_mail(), Schutz über Nonce, Honigtopf
 * und Zeitfalle. Es werden keine eigenen Datenbanktabellen angelegt.
 * ---------------------------------------------------------------------- */

/** Kürzeste Zeit in Sekunden, die ein Mensch zum Ausfüllen braucht. Darunter: Bot. */
define( 'MA8_FORM_MIN_SEKUNDEN', 3 );

/**
 * Stammdaten der sieben Formulare: Beschriftung, Danke-Seite, Betreffzeile,
 * Text der Absendeschaltfläche und der Hinweis unter dem Formular.
 */
function ma8_form_types() {
	return array(
		'meldung'   => array(
			'label'   => __( 'Meldung senden', 'merzenich-aktuell' ),
			'danke'   => 'meldung-senden/danke',
			'betreff' => __( 'Neue Meldung über das Formular', 'merzenich-aktuell' ),
			'button'  => __( 'Meldung absenden', 'merzenich-aktuell' ),
			'note'    => __( 'Die Redaktion prüft jede Einsendung gegen die Originalquelle. Wir melden uns bei Rückfragen per E-Mail.', 'merzenich-aktuell' ),
		),
		'termin'    => array(
			'label'   => __( 'Termin melden', 'merzenich-aktuell' ),
			'danke'   => 'termine-melden/danke',
			'betreff' => __( 'Neuer Termin über das Formular', 'merzenich-aktuell' ),
			'button'  => __( 'Termin einreichen', 'merzenich-aktuell' ),
			'note'    => __( 'Termine erscheinen nach Prüfung im Kalender, in der Regel innerhalb eines Werktags. Kostenlos für Vereine, Kirchen, Gemeinde und gemeinnützige Veranstalter.', 'merzenich-aktuell' ),
		),
		'verein'    => array(
			'label'   => __( 'Verein eintragen', 'merzenich-aktuell' ),
			'danke'   => 'vereine-eintragen/danke',
			'betreff' => __( 'Neuer Vereinseintrag über das Formular', 'merzenich-aktuell' ),
			'button'  => __( 'Verein eintragen', 'merzenich-aktuell' ),
			'note'    => __( 'Der Eintrag ist kostenlos und wird vor Veröffentlichung geprüft.', 'merzenich-aktuell' ),
		),
		'betrieb'   => array(
			'label'   => __( 'Betrieb eintragen', 'merzenich-aktuell' ),
			'danke'   => 'betriebe-eintragen/danke',
			'betreff' => __( 'Neuer Betriebseintrag über das Formular', 'merzenich-aktuell' ),
			'button'  => __( 'Betrieb eintragen', 'merzenich-aktuell' ),
			'note'    => __( 'Basis-Einträge sind kostenlos. Hervorgehobene Einträge und Firmenporträts sind als Anzeige gekennzeichnet.', 'merzenich-aktuell' ),
		),
		'korrektur' => array(
			'label'   => __( 'Korrektur melden', 'merzenich-aktuell' ),
			'danke'   => 'korrekturen/danke',
			'betreff' => __( 'Korrekturhinweis über das Formular', 'merzenich-aktuell' ),
			'button'  => __( 'Korrektur melden', 'merzenich-aktuell' ),
			'note'    => __( 'Wir antworten in der Regel innerhalb von 24 Stunden. Korrekturen werden im Artikel mit Datum ausgewiesen.', 'merzenich-aktuell' ),
		),
		'werbung'   => array(
			'label'   => __( 'Werbung anfragen', 'merzenich-aktuell' ),
			'danke'   => 'werben/danke',
			'betreff' => __( 'Werbeanfrage über das Formular', 'merzenich-aktuell' ),
			'button'  => __( 'Mediadaten anfragen', 'merzenich-aktuell' ),
			'note'    => '',
		),
		'kontakt'   => array(
			'label'   => __( 'Kontakt', 'merzenich-aktuell' ),
			'danke'   => 'ueber-uns/danke',
			'betreff' => __( 'Kontaktanfrage über das Formular', 'merzenich-aktuell' ),
			'button'  => __( 'Nachricht senden', 'merzenich-aktuell' ),
			'note'    => '',
		),
	);
}

/** Auswahlliste der Ortsteile aus der Taxonomie, ergänzt um die beiden Sammelwerte. */
function ma8_form_district_options() {
	$options = array();
	$terms   = get_terms(
		array(
			'taxonomy'   => 'ma_district',
			'hide_empty' => false,
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$options[] = $term->name;
		}
	}
	if ( ! $options ) {
		// Fallback, solange die Taxonomie noch nicht befüllt ist.
		$options = array( 'Merzenich', 'Golzheim', 'Girbelsrath', 'Morschenich', 'Bürgewald' );
	}
	$options[] = __( 'Gesamte Gemeinde', 'merzenich-aktuell' );
	$options[] = __( 'Außerhalb / Region', 'merzenich-aktuell' );

	return $options;
}

/** Link auf die Datenschutzseite für den Einwilligungstext. */
function ma8_form_privacy_url() {
	$url = get_privacy_policy_url();
	if ( $url ) {
		return $url;
	}
	$page = get_page_by_path( 'datenschutz' );
	return $page ? get_permalink( $page ) : home_url( '/datenschutz/' );
}

/** Einwilligung in die Datenverarbeitung — in jedem der sieben Formulare Pflicht. */
function ma8_form_field_consent() {
	return array(
		'name'     => 'einwilligung',
		'type'     => 'checkbox',
		'required' => true,
		'label'    => __( 'Einwilligung in die Datenverarbeitung', 'merzenich-aktuell' ),
		'text'     => sprintf(
			/* translators: %s: URL der Datenschutzseite. */
			__( 'Ich habe die <a href="%s">Datenschutzhinweise</a> gelesen und bin mit der Verarbeitung meiner Angaben zur Bearbeitung meiner Anfrage einverstanden.', 'merzenich-aktuell' ),
			esc_url( ma8_form_privacy_url() )
		),
	);
}

/** Bildrechte-Bestätigung, überall dort, wo ein Bild hochgeladen werden kann. */
function ma8_form_field_rights() {
	return array(
		'name'  => 'bildrechte',
		'type'  => 'checkbox',
		'label' => __( 'Bildrechte', 'merzenich-aktuell' ),
		'text'  => __( 'Ich habe das Bild selbst aufgenommen oder besitze die Rechte und erlaube die Veröffentlichung mit Namensnennung.', 'merzenich-aktuell' ),
	);
}

/**
 * Felddefinitionen je Formulartyp. Feldnamen, Beschriftungen, Pflichtfelder und
 * Auswahllisten entsprechen eins zu eins der Netlify-Fassung.
 *
 * Der Schlüssel "row" fasst Felder zu einer zweispaltigen Zeile (.grid2) zusammen.
 */
function ma8_form_fields( $type ) {
	$orte = array(
		'name'     => 'ortsteil',
		'type'     => 'select',
		'label'    => __( 'Ortsteil', 'merzenich-aktuell' ),
		'required' => true,
		'empty'    => __( 'Ortsteil wählen …', 'merzenich-aktuell' ),
		'options'  => ma8_form_district_options(),
	);

	switch ( $type ) {
		case 'meldung':
			return array(
				array(
					'name'         => 'name',
					'label'        => __( 'Ihr Name', 'merzenich-aktuell' ),
					'required'     => true,
					'autocomplete' => 'name',
					'row'          => 1,
				),
				array(
					'name'         => 'email',
					'type'         => 'email',
					'label'        => __( 'E-Mail', 'merzenich-aktuell' ),
					'required'     => true,
					'autocomplete' => 'email',
					'row'          => 1,
				),
				array(
					'name'         => 'telefon',
					'type'         => 'tel',
					'label'        => __( 'Telefon (optional, für Rückfragen)', 'merzenich-aktuell' ),
					'autocomplete' => 'tel',
					'row'          => 2,
				),
				array_merge( $orte, array( 'row' => 2 ) ),
				array(
					'name'     => 'art',
					'type'     => 'select',
					'label'    => __( 'Worum geht es?', 'merzenich-aktuell' ),
					'required' => true,
					'options'  => array(
						__( 'Hinweis auf ein Ereignis', 'merzenich-aktuell' ),
						__( 'Vereinsmeldung', 'merzenich-aktuell' ),
						__( 'Termin', 'merzenich-aktuell' ),
						__( 'Blaulicht / Einsatz', 'merzenich-aktuell' ),
						__( 'Leserbrief', 'merzenich-aktuell' ),
						__( 'Lob, Kritik, Korrektur', 'merzenich-aktuell' ),
						__( 'Sonstiges', 'merzenich-aktuell' ),
					),
				),
				array(
					'name'        => 'titel',
					'label'       => __( 'Überschrift', 'merzenich-aktuell' ),
					'required'    => true,
					'maxlength'   => 120,
					'placeholder' => __( 'Was ist passiert?', 'merzenich-aktuell' ),
				),
				array(
					'name'        => 'text',
					'type'        => 'textarea',
					'label'       => __( 'Ihre Meldung', 'merzenich-aktuell' ),
					'required'    => true,
					'rows'        => 8,
					'placeholder' => __( 'Wer, was, wann, wo, warum? Je konkreter, desto schneller können wir veröffentlichen.', 'merzenich-aktuell' ),
				),
				array(
					'name'        => 'quelle',
					'label'       => __( 'Quelle oder Link (optional)', 'merzenich-aktuell' ),
					'placeholder' => 'https://',
				),
				array(
					'name'   => 'bild',
					'type'   => 'file',
					'label'  => __( 'Bild (optional, JPG/PNG bis 8 MB)', 'merzenich-aktuell' ),
					'accept' => 'image/*',
				),
				ma8_form_field_rights(),
				array(
					'name'  => 'veroeffentlichung',
					'type'  => 'checkbox',
					'label' => __( 'Namensnennung', 'merzenich-aktuell' ),
					'text'  => __( 'Mein Name darf als Quelle genannt werden.', 'merzenich-aktuell' ),
				),
				ma8_form_field_consent(),
			);

		case 'termin':
			return array(
				array(
					'name'      => 'titel',
					'label'     => __( 'Titel der Veranstaltung', 'merzenich-aktuell' ),
					'required'  => true,
					'maxlength' => 120,
				),
				array(
					'name'     => 'beginn',
					'type'     => 'datetime-local',
					'label'    => __( 'Beginn', 'merzenich-aktuell' ),
					'required' => true,
					'row'      => 1,
				),
				array(
					'name'  => 'ende',
					'type'  => 'datetime-local',
					'label' => __( 'Ende (optional)', 'merzenich-aktuell' ),
					'row'   => 1,
				),
				array(
					'name'        => 'ort',
					'label'       => __( 'Veranstaltungsort', 'merzenich-aktuell' ),
					'required'    => true,
					'placeholder' => __( 'z. B. Schützenhalle Golzheim', 'merzenich-aktuell' ),
					'row'         => 2,
				),
				array_merge( $orte, array( 'row' => 2 ) ),
				array(
					'name'     => 'veranstalter',
					'label'    => __( 'Veranstalter', 'merzenich-aktuell' ),
					'required' => true,
					'row'      => 3,
				),
				array(
					'name'     => 'kategorie',
					'type'     => 'select',
					'label'    => __( 'Kategorie', 'merzenich-aktuell' ),
					'required' => true,
					'row'      => 3,
					'options'  => array(
						__( 'Fest', 'merzenich-aktuell' ),
						__( 'Sport', 'merzenich-aktuell' ),
						__( 'Kirche', 'merzenich-aktuell' ),
						__( 'Rathaus', 'merzenich-aktuell' ),
						__( 'Kultur', 'merzenich-aktuell' ),
						__( 'Musik', 'merzenich-aktuell' ),
						__( 'Karneval', 'merzenich-aktuell' ),
						__( 'Brauchtum', 'merzenich-aktuell' ),
						__( 'Kinder & Familie', 'merzenich-aktuell' ),
						__( 'Info', 'merzenich-aktuell' ),
						__( 'Markt', 'merzenich-aktuell' ),
						__( 'Umwelt', 'merzenich-aktuell' ),
						__( 'Senioren', 'merzenich-aktuell' ),
						__( 'Jugend', 'merzenich-aktuell' ),
						__( 'Sonstiges', 'merzenich-aktuell' ),
					),
				),
				array(
					'name'        => 'text',
					'type'        => 'textarea',
					'label'       => __( 'Beschreibung', 'merzenich-aktuell' ),
					'required'    => true,
					'rows'        => 5,
					'placeholder' => __( 'Programm, Eintritt, Anmeldung, Besonderheiten', 'merzenich-aktuell' ),
				),
				array(
					'name'        => 'eintritt',
					'label'       => __( 'Eintritt', 'merzenich-aktuell' ),
					'placeholder' => __( 'frei / 5 Euro', 'merzenich-aktuell' ),
					'row'         => 4,
				),
				array(
					'name'        => 'link',
					'label'       => __( 'Link zur Veranstaltung', 'merzenich-aktuell' ),
					'placeholder' => 'https://',
					'row'         => 4,
				),
				array(
					'name'         => 'email',
					'type'         => 'email',
					'label'        => __( 'Kontakt E-Mail', 'merzenich-aktuell' ),
					'required'     => true,
					'autocomplete' => 'email',
					'row'          => 5,
				),
				array(
					'name'         => 'telefon',
					'type'         => 'tel',
					'label'        => __( 'Kontakt Telefon (optional)', 'merzenich-aktuell' ),
					'autocomplete' => 'tel',
					'row'          => 5,
				),
				array(
					'name'   => 'bild',
					'type'   => 'file',
					'label'  => __( 'Plakat oder Bild (optional)', 'merzenich-aktuell' ),
					'accept' => 'image/*',
				),
				ma8_form_field_rights(),
				ma8_form_field_consent(),
			);

		case 'verein':
			return array(
				array(
					'name'     => 'verein',
					'label'    => __( 'Name des Vereins', 'merzenich-aktuell' ),
					'required' => true,
					'row'      => 1,
				),
				array_merge( $orte, array( 'row' => 1 ) ),
				array(
					'name'     => 'kategorie',
					'type'     => 'select',
					'label'    => __( 'Kategorie', 'merzenich-aktuell' ),
					'required' => true,
					'row'      => 2,
					'options'  => array(
						__( 'Fußball', 'merzenich-aktuell' ),
						__( 'Tischtennis', 'merzenich-aktuell' ),
						__( 'Sport', 'merzenich-aktuell' ),
						__( 'Schützen', 'merzenich-aktuell' ),
						__( 'Karneval', 'merzenich-aktuell' ),
						__( 'Feuerwehr', 'merzenich-aktuell' ),
						__( 'Kirche', 'merzenich-aktuell' ),
						__( 'Musik & Kultur', 'merzenich-aktuell' ),
						__( 'Heimat & Geschichte', 'merzenich-aktuell' ),
						__( 'Soziales', 'merzenich-aktuell' ),
						__( 'Jugend', 'merzenich-aktuell' ),
						__( 'Fanclub', 'merzenich-aktuell' ),
						__( 'Sonstiges', 'merzenich-aktuell' ),
					),
				),
				array(
					'name'      => 'gruendung',
					'label'     => __( 'Gründungsjahr (optional)', 'merzenich-aktuell' ),
					'inputmode' => 'numeric',
					'row'       => 2,
				),
				array(
					'name'     => 'text',
					'type'     => 'textarea',
					'label'    => __( 'Kurzbeschreibung (2 bis 4 Sätze)', 'merzenich-aktuell' ),
					'required' => true,
					'rows'     => 4,
				),
				array(
					'name'         => 'website',
					'label'        => __( 'Website (optional)', 'merzenich-aktuell' ),
					'placeholder'  => 'https://',
					'autocomplete' => 'url',
					'row'          => 3,
				),
				array(
					'name'         => 'ansprechpartner',
					'label'        => __( 'Ansprechpartner', 'merzenich-aktuell' ),
					'required'     => true,
					'autocomplete' => 'name',
					'row'          => 3,
				),
				array(
					'name'         => 'email',
					'type'         => 'email',
					'label'        => __( 'E-Mail', 'merzenich-aktuell' ),
					'required'     => true,
					'autocomplete' => 'email',
					'row'          => 4,
				),
				array(
					'name'         => 'telefon',
					'type'         => 'tel',
					'label'        => __( 'Telefon (optional)', 'merzenich-aktuell' ),
					'autocomplete' => 'tel',
					'row'          => 4,
				),
				array(
					'name'   => 'bild',
					'type'   => 'file',
					'label'  => __( 'Logo oder Foto (optional)', 'merzenich-aktuell' ),
					'accept' => 'image/*',
				),
				ma8_form_field_rights(),
				ma8_form_field_consent(),
			);

		case 'betrieb':
			return array(
				array(
					'name'     => 'betrieb',
					'label'    => __( 'Name des Betriebs', 'merzenich-aktuell' ),
					'required' => true,
					'row'      => 1,
				),
				array_merge( $orte, array( 'row' => 1 ) ),
				array(
					'name'     => 'branche',
					'type'     => 'select',
					'label'    => __( 'Branche', 'merzenich-aktuell' ),
					'required' => true,
					'row'      => 2,
					'options'  => array(
						__( 'Bäckerei & Lebensmittel', 'merzenich-aktuell' ),
						__( 'Gastronomie', 'merzenich-aktuell' ),
						__( 'Handwerk', 'merzenich-aktuell' ),
						__( 'Gesundheit & Pflege', 'merzenich-aktuell' ),
						__( 'Einzelhandel', 'merzenich-aktuell' ),
						__( 'Dienstleistung', 'merzenich-aktuell' ),
						__( 'Auto & Mobilität', 'merzenich-aktuell' ),
						__( 'Bau & Immobilien', 'merzenich-aktuell' ),
						__( 'Landwirtschaft & Hofladen', 'merzenich-aktuell' ),
						__( 'Sonstiges', 'merzenich-aktuell' ),
					),
				),
				array(
					'name'        => 'adresse',
					'label'       => __( 'Adresse', 'merzenich-aktuell' ),
					'required'    => true,
					'placeholder' => __( 'Straße Nr., 52399 Merzenich', 'merzenich-aktuell' ),
					'row'         => 2,
				),
				array(
					'name'     => 'text',
					'type'     => 'textarea',
					'label'    => __( 'Kurzbeschreibung', 'merzenich-aktuell' ),
					'required' => true,
					'rows'     => 4,
				),
				array(
					'name'        => 'oeffnungszeiten',
					'label'       => __( 'Öffnungszeiten', 'merzenich-aktuell' ),
					'placeholder' => __( 'Mo–Fr 8–18 Uhr', 'merzenich-aktuell' ),
					'row'         => 3,
				),
				array(
					'name'         => 'website',
					'label'        => __( 'Website (optional)', 'merzenich-aktuell' ),
					'placeholder'  => 'https://',
					'autocomplete' => 'url',
					'row'          => 3,
				),
				array(
					'name'         => 'email',
					'type'         => 'email',
					'label'        => __( 'E-Mail', 'merzenich-aktuell' ),
					'required'     => true,
					'autocomplete' => 'email',
					'row'          => 4,
				),
				array(
					'name'         => 'telefon',
					'type'         => 'tel',
					'label'        => __( 'Telefon', 'merzenich-aktuell' ),
					'autocomplete' => 'tel',
					'row'          => 4,
				),
				array(
					'name'   => 'bild',
					'type'   => 'file',
					'label'  => __( 'Logo oder Foto (optional)', 'merzenich-aktuell' ),
					'accept' => 'image/*',
				),
				array(
					'name'  => 'hervorheben',
					'type'  => 'checkbox',
					'label' => __( 'Hervorgehobener Eintrag', 'merzenich-aktuell' ),
					'text'  => __( 'Ich interessiere mich für einen hervorgehobenen Eintrag oder ein Firmenporträt (kostenpflichtig, Angebot folgt).', 'merzenich-aktuell' ),
				),
				ma8_form_field_rights(),
				ma8_form_field_consent(),
			);

		case 'korrektur':
			return array(
				array(
					'name'        => 'url',
					'type'        => 'url',
					'label'       => __( 'Link zur Meldung', 'merzenich-aktuell' ),
					'required'    => true,
					'placeholder' => 'https://merzenich-aktuell.de/...',
				),
				array(
					'name'     => 'text',
					'type'     => 'textarea',
					'label'    => __( 'Was ist falsch oder fehlt?', 'merzenich-aktuell' ),
					'required' => true,
					'rows'     => 6,
				),
				array(
					'name'        => 'quelle',
					'label'       => __( 'Beleg oder Quelle (optional)', 'merzenich-aktuell' ),
					'placeholder' => 'https://',
				),
				array(
					'name'         => 'name',
					'label'        => __( 'Name (optional)', 'merzenich-aktuell' ),
					'autocomplete' => 'name',
					'row'          => 1,
				),
				array(
					'name'         => 'email',
					'type'         => 'email',
					'label'        => __( 'E-Mail für Rückmeldung', 'merzenich-aktuell' ),
					'required'     => true,
					'autocomplete' => 'email',
					'row'          => 1,
				),
				ma8_form_field_consent(),
			);

		case 'werbung':
			return array(
				array(
					'name'         => 'firma',
					'label'        => __( 'Firma / Organisation', 'merzenich-aktuell' ),
					'required'     => true,
					'autocomplete' => 'organization',
					'row'          => 1,
				),
				array(
					'name'         => 'name',
					'label'        => __( 'Ansprechpartner', 'merzenich-aktuell' ),
					'required'     => true,
					'autocomplete' => 'name',
					'row'          => 1,
				),
				array(
					'name'         => 'email',
					'type'         => 'email',
					'label'        => __( 'E-Mail', 'merzenich-aktuell' ),
					'required'     => true,
					'autocomplete' => 'email',
					'row'          => 2,
				),
				array(
					'name'         => 'telefon',
					'type'         => 'tel',
					'label'        => __( 'Telefon', 'merzenich-aktuell' ),
					'autocomplete' => 'tel',
					'row'          => 2,
				),
				array(
					'name'     => 'format',
					'type'     => 'select',
					'label'    => __( 'Interesse an', 'merzenich-aktuell' ),
					'required' => true,
					'options'  => array(
						__( 'Banner Startseite', 'merzenich-aktuell' ),
						__( 'Sidebar-Anzeige', 'merzenich-aktuell' ),
						__( 'Firmenporträt (Sponsored Post)', 'merzenich-aktuell' ),
						__( 'Hervorgehobener Betriebseintrag', 'merzenich-aktuell' ),
						__( 'Stellenanzeige', 'merzenich-aktuell' ),
						__( 'Sonstiges', 'merzenich-aktuell' ),
					),
				),
				array(
					'name'        => 'text',
					'type'        => 'textarea',
					'label'       => __( 'Nachricht', 'merzenich-aktuell' ),
					'rows'        => 5,
					'placeholder' => __( 'Ziel, Zeitraum, Budgetrahmen', 'merzenich-aktuell' ),
				),
				ma8_form_field_consent(),
			);

		case 'kontakt':
			return array(
				array(
					'name'         => 'name',
					'label'        => __( 'Name', 'merzenich-aktuell' ),
					'required'     => true,
					'autocomplete' => 'name',
					'row'          => 1,
				),
				array(
					'name'         => 'email',
					'type'         => 'email',
					'label'        => __( 'E-Mail', 'merzenich-aktuell' ),
					'required'     => true,
					'autocomplete' => 'email',
					'row'          => 1,
				),
				array(
					'name'     => 'text',
					'type'     => 'textarea',
					'label'    => __( 'Nachricht', 'merzenich-aktuell' ),
					'required' => true,
					'rows'     => 6,
				),
				ma8_form_field_consent(),
			);
	}

	return array();
}

/** Fehlende Schlüssel einer Felddefinition auffüllen, damit Ausgabe und Prüfung sicher zugreifen. */
function ma8_form_field_defaults( $field ) {
	return wp_parse_args(
		$field,
		array(
			'name'         => '',
			'type'         => 'text',
			'label'        => '',
			'required'     => false,
			'options'      => array(),
			'empty'        => __( 'Bitte wählen …', 'merzenich-aktuell' ),
			'placeholder'  => '',
			'autocomplete' => '',
			'inputmode'    => '',
			'maxlength'    => 0,
			'rows'         => 4,
			'accept'       => '',
			'text'         => '',
			'row'          => 0,
		)
	);
}

/* ----------------------------------------------------------------------
 * Ausgabe
 * ---------------------------------------------------------------------- */

/**
 * Ein einzelnes Feld ausgeben.
 *
 * Das Eingabeelement steht innerhalb der Beschriftung — so verlangt es das
 * vorhandene Stylesheet (.form label ist eine Spalte, .grid2 zählt die Labels
 * als Rasterzellen). Zusätzlich verweist "for" auf die Kennung des Elements,
 * damit die Zuordnung auch für Screenreader ausdrücklich benannt ist.
 */
function ma8_form_render_field( $field, $type ) {
	$f     = ma8_form_field_defaults( $field );
	$id    = 'ma8-' . $type . '-' . $f['name'];
	$req   = $f['required'] ? ' required aria-required="true"' : '';
	$star  = $f['required'] ? ' <span aria-hidden="true">*</span>' : '';
	$label = esc_html( $f['label'] );

	// Zustimmungsfelder tragen ihren erklärenden Text neben der Ankreuzbox.
	if ( 'checkbox' === $f['type'] ) {
		return sprintf(
			'<label class="check" for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="ja"%3$s><span>%4$s%5$s</span></label>',
			esc_attr( $id ),
			esc_attr( $f['name'] ),
			$req,
			wp_kses( $f['text'], array( 'a' => array( 'href' => array() ) ) ),
			$star
		);
	}

	$attr  = ' id="' . esc_attr( $id ) . '" name="' . esc_attr( $f['name'] ) . '"' . $req;
	$attr .= $f['placeholder'] ? ' placeholder="' . esc_attr( $f['placeholder'] ) . '"' : '';
	$attr .= $f['autocomplete'] ? ' autocomplete="' . esc_attr( $f['autocomplete'] ) . '"' : '';
	$attr .= $f['inputmode'] ? ' inputmode="' . esc_attr( $f['inputmode'] ) . '"' : '';
	$attr .= $f['maxlength'] ? ' maxlength="' . esc_attr( (string) $f['maxlength'] ) . '"' : '';

	switch ( $f['type'] ) {
		case 'textarea':
			$control = '<textarea' . $attr . ' rows="' . esc_attr( (string) $f['rows'] ) . '"></textarea>';
			break;

		case 'select':
			$options = '<option value="">' . esc_html( $f['empty'] ) . '</option>';
			foreach ( $f['options'] as $option ) {
				$options .= '<option value="' . esc_attr( $option ) . '">' . esc_html( $option ) . '</option>';
			}
			$control = '<select' . $attr . '>' . $options . '</select>';
			break;

		case 'file':
			$control = '<input type="file"' . $attr . ( $f['accept'] ? ' accept="' . esc_attr( $f['accept'] ) . '"' : '' ) . '>';
			break;

		default:
			$control = '<input type="' . esc_attr( $f['type'] ) . '"' . $attr . '>';
	}

	// Beschriftung und Pflichtstern kommen in EIN Element. Ohne diese Klammer sind
	// sie in .form label (display:flex; flex-direction:column) zwei Flex-Elemente,
	// und der Stern rutscht in eine eigene Zeile unter die Beschriftung.
	return '<label for="' . esc_attr( $id ) . '"><span class="lbl">' . $label . $star . '</span>' . $control . '</label>';
}

/**
 * Hinweisbox über dem Formular. Erfolg wird höflich gemeldet, Fehler werden
 * einzeln benannt, damit niemand raten muss, welches Feld gemeint ist.
 */
function ma8_form_notice() {
	$out = '';

	if ( isset( $_GET['ma8_ok'] ) && '1' === $_GET['ma8_ok'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$out .= '<div class="notice" role="status"><strong>' . esc_html__( 'Vielen Dank, Ihre Nachricht ist bei uns eingegangen.', 'merzenich-aktuell' ) . '</strong> ' . esc_html__( 'Die Redaktion meldet sich bei Rückfragen per E-Mail.', 'merzenich-aktuell' ) . '</div>';
	}

	if ( isset( $_GET['ma8_fehler'] ) && '1' === $_GET['ma8_fehler'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$errors = array();
		if ( isset( $_GET['ma8_hinweis'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$token = sanitize_key( wp_unslash( $_GET['ma8_hinweis'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$saved = $token ? get_transient( 'ma8_form_fehler_' . $token ) : false;
			if ( is_array( $saved ) ) {
				$errors = $saved;
				delete_transient( 'ma8_form_fehler_' . $token );
			}
		}

		$out .= '<div class="notice" role="alert"><strong>' . esc_html__( 'Das Formular konnte nicht abgeschickt werden.', 'merzenich-aktuell' ) . '</strong>';
		if ( $errors ) {
			$out .= '<ul>';
			foreach ( $errors as $error ) {
				$out .= '<li>' . esc_html( $error ) . '</li>';
			}
			$out .= '</ul>';
		} else {
			$out .= ' ' . esc_html__( 'Bitte prüfen Sie Ihre Angaben und versuchen Sie es noch einmal.', 'merzenich-aktuell' );
		}
		$out .= '</div>';
	}

	return $out;
}

/**
 * Shortcode [ma_formular typ="meldung"].
 *
 * Gültige Typen: meldung, termin, verein, betrieb, korrektur, werbung, kontakt.
 */
function ma8_formular_shortcode( $atts ) {
	$atts  = shortcode_atts( array( 'typ' => 'kontakt' ), $atts, 'ma_formular' );
	$type  = sanitize_key( $atts['typ'] );
	$types = ma8_form_types();

	if ( ! isset( $types[ $type ] ) ) {
		return '';
	}

	$conf   = $types[ $type ];
	$fields = ma8_form_fields( $type );
	$anchor = 'ma8-formular-' . $type;

	$out  = '<div class="form-block" id="' . esc_attr( $anchor ) . '">';
	$out .= ma8_form_notice();
	$out .= '<form class="form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" enctype="multipart/form-data">';
	$out .= '<input type="hidden" name="action" value="ma8_formular">';
	$out .= '<input type="hidden" name="ma8_typ" value="' . esc_attr( $type ) . '">';
	$out .= '<input type="hidden" name="ma8_zurueck" value="' . esc_url( get_permalink() ? get_permalink() : home_url( '/' ) ) . '">';
	$out .= '<input type="hidden" name="ma8_zeit" value="' . esc_attr( (string) time() ) . '">';
	$out .= wp_nonce_field( 'ma8_formular', 'ma8_nonce', true, false );

	// Honigtopf: für Menschen unsichtbar und nicht anspringbar, für einfache Bots verlockend.
	$out .= '<p class="hp" aria-hidden="true"><label for="ma8-' . esc_attr( $type ) . '-bot">' . esc_html__( 'Website', 'merzenich-aktuell' );
	$out .= '<input type="text" id="ma8-' . esc_attr( $type ) . '-bot" name="bot-field" value="" tabindex="-1" autocomplete="off"></label></p>';

	// Felder ausgeben, zweispaltige Zeilen (row) zusammenfassen.
	$count = count( $fields );
	for ( $i = 0; $i < $count; $i++ ) {
		$row = isset( $fields[ $i ]['row'] ) ? (int) $fields[ $i ]['row'] : 0;
		if ( $row > 0 ) {
			$group = '';
			while ( $i < $count && isset( $fields[ $i ]['row'] ) && (int) $fields[ $i ]['row'] === $row ) {
				$group .= ma8_form_render_field( $fields[ $i ], $type );
				$i++;
			}
			$i--;
			$out .= '<div class="grid2">' . $group . '</div>';
			continue;
		}
		$out .= ma8_form_render_field( $fields[ $i ], $type );
	}

	$out .= '<button class="btn" type="submit">' . esc_html( $conf['button'] ) . '</button>';
	$out .= '<p class="form-note">' . esc_html__( 'Mit * markierte Felder sind Pflichtfelder.', 'merzenich-aktuell' ) . '</p>';
	if ( $conf['note'] ) {
		$out .= '<p class="form-note">' . esc_html( $conf['note'] ) . '</p>';
	}
	$out .= '</form></div>';

	return $out;
}
add_shortcode( 'ma_formular', 'ma8_formular_shortcode' );

/* ----------------------------------------------------------------------
 * Verarbeitung
 * ---------------------------------------------------------------------- */

/** Redaktionsadresse für Formulare, ersatzweise die Administrationsadresse der Website. */
function ma8_form_recipient() {
	$settings = ma8_settings();
	$address  = isset( $settings['form_email'] ) ? trim( (string) $settings['form_email'] ) : '';
	if ( $address && is_email( $address ) ) {
		return $address;
	}
	return get_option( 'admin_email' );
}

/** Ziel nach erfolgreichem Absenden: Danke-Seite, sonst zurück zum Formular mit Erfolgsmeldung. */
function ma8_form_success_url( $type, $back ) {
	$types = ma8_form_types();
	if ( isset( $types[ $type ]['danke'] ) ) {
		$page = get_page_by_path( $types[ $type ]['danke'] );
		if ( $page ) {
			return get_permalink( $page );
		}
	}
	return add_query_arg( 'ma8_ok', '1', $back ) . '#ma8-formular-' . $type;
}

/** Zurück zum Formular, mit Fehlerliste in einem kurzlebigen Transient. */
function ma8_form_fail( $type, $back, $errors ) {
	$token = wp_generate_password( 12, false, false );
	set_transient( 'ma8_form_fehler_' . $token, array_values( $errors ), 5 * MINUTE_IN_SECONDS );

	$url = add_query_arg(
		array(
			'ma8_fehler'  => '1',
			'ma8_hinweis' => $token,
		),
		$back
	);

	wp_safe_redirect( $url . '#ma8-formular-' . $type );
	exit;
}

/**
 * Optionales Bild entgegennehmen. Nur Bilddateien bis 8 MB, Ablage in der
 * Mediathek; die Adresse wandert in die E-Mail an die Redaktion.
 */
function ma8_form_handle_upload( $key ) {
	if ( ! apply_filters( 'ma8_form_allow_upload', true ) ) {
		return '';
	}
	if ( empty( $_FILES[ $key ]['name'] ) || ! isset( $_FILES[ $key ]['error'] ) || UPLOAD_ERR_OK !== (int) $_FILES[ $key ]['error'] ) {
		return '';
	}
	if ( isset( $_FILES[ $key ]['size'] ) && (int) $_FILES[ $key ]['size'] > 8 * MB_IN_BYTES ) {
		return '';
	}

	$name  = sanitize_file_name( (string) $_FILES[ $key ]['name'] );
	$check = wp_check_filetype( $name, array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'gif'          => 'image/gif',
		'webp'         => 'image/webp',
	) );
	if ( empty( $check['type'] ) ) {
		return '';
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$upload = wp_handle_upload(
		$_FILES[ $key ], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		array(
			'test_form' => false,
			'mimes'     => array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'png'          => 'image/png',
				'gif'          => 'image/gif',
				'webp'         => 'image/webp',
			),
		)
	);
	if ( ! is_array( $upload ) || ! empty( $upload['error'] ) ) {
		return '';
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $upload['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', wp_basename( $upload['file'] ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	if ( ! is_wp_error( $attachment_id ) && $attachment_id ) {
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	}

	return $upload['url'];
}

/**
 * Eingaben prüfen und säubern.
 *
 * @return array{werte:array,fehler:array} Gesäuberte Werte und Fehlermeldungen.
 */
function ma8_form_validate( $type ) {
	$values = array();
	$errors = array();

	foreach ( ma8_form_fields( $type ) as $field ) {
		$f   = ma8_form_field_defaults( $field );
		$key = $f['name'];
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput,WordPress.Security.NonceVerification.Missing
		$raw = is_array( $raw ) ? '' : (string) $raw;

		switch ( $f['type'] ) {
			case 'checkbox':
				$value = ( 'ja' === $raw ) ? __( 'Ja', 'merzenich-aktuell' ) : __( 'Nein', 'merzenich-aktuell' );
				if ( $f['required'] && 'ja' !== $raw ) {
					/* translators: %s: Beschriftung des Feldes. */
					$errors[] = sprintf( __( 'Bitte bestätigen Sie: %s', 'merzenich-aktuell' ), $f['label'] );
				}
				break;

			case 'email':
				$value = sanitize_email( $raw );
				if ( $f['required'] && '' === $value ) {
					/* translators: %s: Beschriftung des Feldes. */
					$errors[] = sprintf( __( 'Bitte füllen Sie das Feld „%s“ aus.', 'merzenich-aktuell' ), $f['label'] );
				} elseif ( '' !== $value && ! is_email( $value ) ) {
					/* translators: %s: Beschriftung des Feldes. */
					$errors[] = sprintf( __( 'Bitte geben Sie im Feld „%s“ eine gültige E-Mail-Adresse an.', 'merzenich-aktuell' ), $f['label'] );
					$value    = '';
				}
				break;

			case 'url':
				$value = esc_url_raw( $raw );
				if ( $f['required'] && '' === $value ) {
					/* translators: %s: Beschriftung des Feldes. */
					$errors[] = sprintf( __( 'Bitte geben Sie im Feld „%s“ eine vollständige Adresse an, beginnend mit https://', 'merzenich-aktuell' ), $f['label'] );
				}
				break;

			case 'textarea':
				$value = sanitize_textarea_field( $raw );
				if ( $f['required'] && '' === $value ) {
					/* translators: %s: Beschriftung des Feldes. */
					$errors[] = sprintf( __( 'Bitte füllen Sie das Feld „%s“ aus.', 'merzenich-aktuell' ), $f['label'] );
				}
				break;

			case 'select':
				$value = sanitize_text_field( $raw );
				// Nur Werte aus der eigenen Auswahlliste zulassen.
				if ( '' !== $value && ! in_array( $value, $f['options'], true ) ) {
					$value = '';
				}
				if ( $f['required'] && '' === $value ) {
					/* translators: %s: Beschriftung des Feldes. */
					$errors[] = sprintf( __( 'Bitte treffen Sie im Feld „%s“ eine Auswahl.', 'merzenich-aktuell' ), $f['label'] );
				}
				break;

			case 'file':
				$value = ma8_form_handle_upload( $key );
				break;

			default:
				$value = sanitize_text_field( $raw );
				if ( $f['required'] && '' === $value ) {
					/* translators: %s: Beschriftung des Feldes. */
					$errors[] = sprintf( __( 'Bitte füllen Sie das Feld „%s“ aus.', 'merzenich-aktuell' ), $f['label'] );
				}
				if ( $f['maxlength'] && mb_strlen( $value ) > (int) $f['maxlength'] ) {
					$errors[] = sprintf(
						/* translators: 1: Beschriftung des Feldes, 2: Höchstzahl der Zeichen. */
						__( 'Das Feld „%1$s“ darf höchstens %2$d Zeichen lang sein.', 'merzenich-aktuell' ),
						$f['label'],
						(int) $f['maxlength']
					);
				}
		}

		$values[ $key ] = array(
			'label' => $f['label'],
			'value' => $value,
		);
	}

	return array(
		'werte'  => $values,
		'fehler' => $errors,
	);
}

/** Klartext-E-Mail an die Redaktion zusammensetzen. Die IP wird bewusst nicht erfasst. */
function ma8_form_build_body( $type, $values, $back ) {
	$types = ma8_form_types();
	$label = isset( $types[ $type ]['label'] ) ? $types[ $type ]['label'] : $type;

	$lines   = array();
	$lines[] = sprintf( __( 'Formular: %s', 'merzenich-aktuell' ), $label );
	$lines[] = sprintf( __( 'Gesendet: %s Uhr', 'merzenich-aktuell' ), wp_date( 'd.m.Y H:i' ) );
	$lines[] = sprintf( __( 'Seite: %s', 'merzenich-aktuell' ), $back );
	$lines[] = '';

	foreach ( $values as $entry ) {
		$value = ( '' === $entry['value'] ) ? __( '— keine Angabe —', 'merzenich-aktuell' ) : $entry['value'];
		// Beschriftungen, die bereits mit einem Satzzeichen enden, bekommen keinen Doppelpunkt dazu.
		$name    = rtrim( $entry['label'], '?:' );
		$lines[] = $name . ': ' . $value;
	}

	$lines[] = '';
	$lines[] = '--';
	$lines[] = sprintf(
		/* translators: 1: Name des Formulars, 2: Name der Website. */
		__( 'Diese Nachricht kam über das Formular „%1$s“ auf %2$s.', 'merzenich-aktuell' ),
		$label,
		wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
	);
	$lines[] = __( 'Die IP-Adresse des Absenders wird aus Datenschutzgründen nicht gespeichert.', 'merzenich-aktuell' );

	return implode( "\n", $lines );
}

/**
 * Formular entgegennehmen: Nonce, Honigtopf, Zeitfalle, Prüfung, Versand, Weiterleitung.
 */
function ma8_form_handle() {
	check_admin_referer( 'ma8_formular', 'ma8_nonce' );

	$type  = isset( $_POST['ma8_typ'] ) ? sanitize_key( wp_unslash( $_POST['ma8_typ'] ) ) : '';
	$types = ma8_form_types();
	if ( ! isset( $types[ $type ] ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	$back = isset( $_POST['ma8_zurueck'] ) ? esc_url_raw( wp_unslash( $_POST['ma8_zurueck'] ) ) : '';
	$back = wp_validate_redirect( $back, home_url( '/' ) );

	// Honigtopf: gefüllt heißt Bot. Kommentarlos auf die Danke-Seite, damit er nichts lernt.
	$trap = isset( $_POST['bot-field'] ) ? trim( (string) wp_unslash( $_POST['bot-field'] ) ) : '';
	if ( '' !== $trap ) {
		wp_safe_redirect( ma8_form_success_url( $type, $back ) );
		exit;
	}

	// Zeitfalle: unter drei Sekunden hat das kein Mensch ausgefüllt.
	$started = isset( $_POST['ma8_zeit'] ) ? (int) wp_unslash( $_POST['ma8_zeit'] ) : 0;
	if ( $started <= 0 || ( time() - $started ) < MA8_FORM_MIN_SEKUNDEN ) {
		wp_safe_redirect( ma8_form_success_url( $type, $back ) );
		exit;
	}

	$checked = ma8_form_validate( $type );
	if ( $checked['fehler'] ) {
		ma8_form_fail( $type, $back, $checked['fehler'] );
	}

	$subject = sprintf(
		'[%1$s] %2$s',
		wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		$types[ $type ]['betreff']
	);

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( ! empty( $checked['werte']['email']['value'] ) && is_email( $checked['werte']['email']['value'] ) ) {
		$headers[] = 'Reply-To: ' . $checked['werte']['email']['value'];
	}

	$sent = wp_mail(
		ma8_form_recipient(),
		$subject,
		ma8_form_build_body( $type, $checked['werte'], $back ),
		$headers
	);

	if ( ! $sent ) {
		ma8_form_fail(
			$type,
			$back,
			array( __( 'Die Nachricht konnte gerade nicht versendet werden. Bitte versuchen Sie es später noch einmal oder schreiben Sie uns direkt per E-Mail.', 'merzenich-aktuell' ) )
		);
	}

	wp_safe_redirect( ma8_form_success_url( $type, $back ) );
	exit;
}
add_action( 'admin_post_nopriv_ma8_formular', 'ma8_form_handle' );
add_action( 'admin_post_ma8_formular', 'ma8_form_handle' );
