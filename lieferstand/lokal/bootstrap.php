<?php
/**
 * Gemeinsamer Kopf der Einrichtungsschritte: WordPress laden, ohne ein Theme
 * auszugeben, und mit einer festen Adresse, damit Permalinks stimmen.
 */
define( 'WP_USE_THEMES', false );
define( 'WP_LOAD_IMPORTERS', true );
$ma_port                = getenv( 'MA_PORT' ) ? getenv( 'MA_PORT' ) : '8080';
$_SERVER['HTTP_HOST']   = '127.0.0.1:' . $ma_port;
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SERVER_NAME'] = explode( ':', $_SERVER['HTTP_HOST'] )[0];
$_SERVER['SERVER_PORT'] = $ma_port;
require __DIR__ . '/wp/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';
