<?php
/**
 * Schritt 3: Startinhalte einspielen, Permalinks neu schreiben, Menue anlegen.
 * Laeuft mit aktivem Theme, damit alle eigenen Inhaltstypen bekannt sind.
 */
require __DIR__ . '/bootstrap.php';

foreach ( array( 'ma_event', 'ma_club', 'ma_job', 'ma_business' ) as $pt ) {
	if ( ! post_type_exists( $pt ) ) {
		echo "FEHLER: Inhaltstyp $pt ist nicht registriert. Ist das Theme aktiv?\n";
		exit( 1 );
	}
}

$xml = get_theme_file_path( 'starter-content-v17.xml' );
if ( ! file_exists( $xml ) ) {
	echo "FEHLER: starter-content-v17.xml fehlt.\n";
	exit( 1 );
}

$already = get_posts( array( 'post_type' => 'post', 'numberposts' => 1, 'post_status' => 'any', 'meta_key' => 'ma8_kicker' ) );
if ( $already ) {
	echo "Inhalte bereits vorhanden, Import uebersprungen.\n";
} else {
	require_once WP_PLUGIN_DIR . '/wordpress-importer/wordpress-importer.php';
	if ( ! class_exists( 'WP_Import' ) ) {
		require_once WP_PLUGIN_DIR . '/wordpress-importer/class-wp-import.php';
	}
	$importer = new WP_Import();
	$importer->fetch_attachments = false;
	ob_start();
	$importer->import( $xml );
	file_put_contents( __DIR__ . '/import.log', wp_strip_all_tags( ob_get_clean() ) );
	echo "Import abgeschlossen (Protokoll in import.log).\n";
}

// Permalinks neu schreiben, jetzt mit allen Inhaltstypen und Taxonomien.
global $wp_rewrite;
$wp_rewrite->set_permalink_structure( '/%postname%/' );
$wp_rewrite->flush_rules( true );

foreach ( array( 'post', 'page', 'ma_event', 'ma_club', 'ma_job', 'ma_business' ) as $pt ) {
	$c = wp_count_posts( $pt );
	echo str_pad( $pt, 14 ) . ( isset( $c->publish ) ? (int) $c->publish : 0 ) . "\n";
}
foreach ( array( 'category' => 'Kategorien', 'ma_district' => 'Ortsteile' ) as $tax => $label ) {
	$n = taxonomy_exists( $tax ) ? wp_count_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) ) : null;
	echo str_pad( $label, 14 ) . ( is_null( $n ) ? 'Taxonomie fehlt' : ( is_wp_error( $n ) ? $n->get_error_message() : (int) $n ) ) . "\n";
}

// Hauptnavigation aus den Ressorts plus Verzeichnissen.
if ( ! wp_get_nav_menu_object( 'Hauptnavigation' ) ) {
	$menu_id = wp_create_nav_menu( 'Hauptnavigation' );
	foreach ( get_categories( array( 'hide_empty' => false, 'exclude' => array( get_option( 'default_category' ) ) ) ) as $cat ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => $cat->name,
			'menu-item-object'    => 'category',
			'menu-item-object-id' => $cat->term_id,
			'menu-item-type'      => 'taxonomy',
			'menu-item-status'    => 'publish',
		) );
	}
	// Beschriftungen bewusst anders als die Ressort-Kategorien, sonst steht
	// "Vereine" zweimal in der Navigation (einmal Ressort, einmal Verzeichnis).
	foreach ( array( 'ma_event' => 'Termine', 'ma_club' => 'Vereinsverzeichnis', 'ma_job' => 'Stellenmarkt', 'ma_business' => 'Betriebe' ) as $pt => $label ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'  => $label,
			'menu-item-url'    => get_post_type_archive_link( $pt ),
			'menu-item-status' => 'publish',
		) );
	}
	// Nur die Hauptnavigation belegen. Vorher wurde dasselbe Menue auf alle
	// registrierten Positionen gelegt, dadurch stand im Fuss unter der
	// Ueberschrift "Service" noch einmal die Ressortliste aus der Spalte
	// daneben. Bleibt die Fussposition leer, greift die kuratierte Liste
	// aus footer.php.
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
	echo "Menue angelegt.\n";
}
echo "Fertig.\n";
