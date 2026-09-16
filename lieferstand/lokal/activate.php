<?php
/**
 * Schritt 2: Theme aktivieren, Grundeinstellungen setzen, Beispielinhalte von
 * WordPress entfernen. Muss VOR dem Import laufen, weil erst mit aktivem Theme
 * die Inhaltstypen ma_event, ma_club, ma_job und ma_business registriert sind.
 */
require __DIR__ . '/bootstrap.php';

switch_theme( 'merzenich-aktuell' );

update_option( 'blogname', 'Merzenich Aktuell' );
update_option( 'blogdescription', 'Nachrichten aus der Gemeinde Merzenich' );
update_option( 'timezone_string', 'Europe/Berlin' );
update_option( 'date_format', 'j. F Y' );
update_option( 'time_format', 'H:i' );
update_option( 'start_of_week', 1 );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'default_ping_status', 'closed' );
update_option( 'default_comment_status', 'closed' );

// Beispielinhalte von WordPress entfernen, damit sie nicht in den Listen stehen.
foreach ( array( 'Hello world!', 'Sample Page', 'Privacy Policy' ) as $title ) {
	$found = get_posts( array( 'post_type' => array( 'post', 'page' ), 'title' => $title, 'post_status' => 'any', 'numberposts' => -1 ) );
	foreach ( $found as $p ) {
		wp_delete_post( $p->ID, true );
		echo "entfernt: {$p->post_title}\n";
	}
}

echo "Theme aktiv: " . get_stylesheet() . "\n";
