<?php
/**
 * Speicherzeit für REST-Antworten (1.27.1).
 *
 * Der Server bei IONOS gibt JSON ohne eigene Kopfzeilen 28 Tage Speicherzeit
 * mit (mod_expires): anonyme Antworten unter /wp-json/ trugen
 * „Cache-Control: max-age=2419200“ (Prüfung 12.10.2026), während /api/latest.json,
 * die Feeds und /assets/ressort-menue.json längst eigene Angaben haben. Setzt PHP
 * selbst Cache-Control und Expires, fügt mod_expires nichts hinzu (so auch
 * ma21_cache() im Theme). Anonymes GET/HEAD bekommt fünf Minuten wie
 * /api/sport-current.json, alle anderen Methoden werden nicht gespeichert.
 * Angemeldete Benutzer bekommen von WordPress bereits no-cache; Endpunkte mit
 * eigener Angabe (ma/v1/live, ma/v1/layout) bleiben unverändert. Fehlerantworten
 * (ab 400, etwa 404 rest_no_route oder 401 an geschützten Endpunkten) werden
 * nicht gespeichert. Antworten mit ?_envelope verpackt WordPress nach diesem
 * Filter neu; dort greifen die Kopfzeilen nicht (bewusst nicht abgedeckt).
 */
if (!defined('ABSPATH')) { exit; }

const MA_REST_CACHE_SEKUNDEN = 300;

/**
 * Kopfzeilen, die eine REST-Antwort zusätzlich bekommt; leer, wenn nichts zu tun ist.
 * $vorhanden = Kopfzeilen der Antwort, $jetzt = Unix-Zeit für Expires.
 */
function ma_rest_cache_kopfzeilen(array $vorhanden, string $methode, bool $angemeldet, int $jetzt, int $status = 200): array {
    if ($angemeldet) return [];
    foreach (array_keys($vorhanden) as $name) {
        if (strtolower((string) $name) === 'cache-control') return [];
    }
    $sekunden = $status >= 200 && $status < 300 && in_array(strtoupper($methode), ['GET', 'HEAD'], true) ? MA_REST_CACHE_SEKUNDEN : 0;
    return [
        'Cache-Control' => $sekunden > 0 ? 'public, max-age=' . $sekunden : 'no-store, max-age=0',
        'Expires' => gmdate('D, d M Y H:i:s', $jetzt + $sekunden) . ' GMT',
    ];
}

add_filter('rest_post_dispatch', function ($antwort, $server, $anfrage) {
    if (!$antwort instanceof WP_REST_Response || !$anfrage instanceof WP_REST_Request) return $antwort;
    foreach (ma_rest_cache_kopfzeilen($antwort->get_headers(), (string) $anfrage->get_method(), is_user_logged_in(), time(), (int) $antwort->get_status()) as $name => $wert) {
        $antwort->header($name, $wert);
    }
    return $antwort;
}, 20, 3);
