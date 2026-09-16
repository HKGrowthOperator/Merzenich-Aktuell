<?php
/**
 * Einmalige Erstinstallation der lokalen Testumgebung.
 * Aufruf: php install.php
 */
define( 'WP_INSTALLING', true );
define( 'WP_USE_THEMES', false );
$ma_port = getenv( 'MA_PORT' ) ? getenv( 'MA_PORT' ) : '8080';
$_SERVER['HTTP_HOST']   = '127.0.0.1:' . $ma_port;
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SERVER_NAME'] = '127.0.0.1';
$_SERVER['SERVER_PORT'] = $ma_port;

require __DIR__ . '/wp/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

if ( is_blog_installed() ) {
	echo "Bereits installiert.\n";
	exit( 0 );
}

$result = wp_install(
	'Merzenich Aktuell',
	'redaktion',
	'redaktion@example.invalid',
	true,
	'',
	'merzenich'
);

echo "Installiert. Benutzer: redaktion / merzenich\n";
