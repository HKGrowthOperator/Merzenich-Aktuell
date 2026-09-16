<?php
/**
 * Plugin Name: Merzenich Aktuell - lokales Postfach
 * Description: NUR FUER DIE LOKALE TESTUMGEBUNG. Faengt jede E-Mail ab und legt
 *              sie als Textdatei unter wp-content/postausgang/ ab, statt sie zu
 *              versenden. So lassen sich die Formulare ohne Mailserver pruefen.
 *              Diese Datei gehoert nicht auf den Livebetrieb.
 */

add_filter(
	'pre_wp_mail',
	function ( $null, $atts ) {
		$dir = WP_CONTENT_DIR . '/postausgang';
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		$to      = is_array( $atts['to'] ) ? implode( ', ', $atts['to'] ) : (string) $atts['to'];
		$headers = is_array( $atts['headers'] ) ? implode( "\n", $atts['headers'] ) : (string) $atts['headers'];
		$text    = "An: {$to}\nBetreff: {$atts['subject']}\n{$headers}\n\n{$atts['message']}\n";
		$name    = gmdate( 'Ymd-His' ) . '-' . substr( md5( $text . wp_rand() ), 0, 6 ) . '.txt';
		file_put_contents( trailingslashit( $dir ) . $name, $text );
		return true; // Als erfolgreich versendet melden.
	},
	10,
	2
);
