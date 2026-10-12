<?php
/**
 * Speicherzeit für REST-Antworten (includes/rest-cache.php) ohne WordPress:
 * anonymes GET fünf Minuten, andere Methoden nicht speichern, Angemeldete und
 * Endpunkte mit eigener Angabe unangetastet.
 * Aufruf: php qa/wordpress/rest-cache-test.php
 */
define('ABSPATH', __DIR__);
function add_action(...$a) {} function add_filter(...$a) {}
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/rest-cache.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-66s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
$t = 1791000000; // fester Zeitpunkt (Sat, 03 Oct 2026 04:00:00 GMT)
$ablauf = fn(int $s) => gmdate('D, d M Y H:i:s', $t + $s) . ' GMT';

echo "Anonyme Anfragen\n";
pruefe('GET: fünf Minuten öffentlich, Expires passend', ma_rest_cache_kopfzeilen([], 'GET', false, $t), ['Cache-Control' => 'public, max-age=300', 'Expires' => $ablauf(300)]);
pruefe('HEAD wie GET', ma_rest_cache_kopfzeilen([], 'HEAD', false, $t), ['Cache-Control' => 'public, max-age=300', 'Expires' => $ablauf(300)]);
pruefe('Kleingeschriebene Methode wird erkannt', ma_rest_cache_kopfzeilen([], 'get', false, $t), ['Cache-Control' => 'public, max-age=300', 'Expires' => $ablauf(300)]);
pruefe('POST (ma/v1/puls, ma/v1/push): nicht speichern', ma_rest_cache_kopfzeilen([], 'POST', false, $t), ['Cache-Control' => 'no-store, max-age=0', 'Expires' => $ablauf(0)]);
pruefe('OPTIONS: nicht speichern', ma_rest_cache_kopfzeilen([], 'OPTIONS', false, $t)['Cache-Control'], 'no-store, max-age=0');
pruefe('Expires im HTTP-Datumsformat', preg_match('/^[A-Z][a-z]{2}, \d{2} [A-Z][a-z]{2} \d{4} \d{2}:\d{2}:\d{2} GMT$/', ma_rest_cache_kopfzeilen([], 'GET', false, $t)['Expires']), 1);
pruefe('Expires für den Testzeitpunkt wörtlich', ma_rest_cache_kopfzeilen([], 'GET', false, $t)['Expires'], 'Sat, 03 Oct 2026 04:05:00 GMT');

echo "\nNichts anfassen\n";
pruefe('Angemeldet: WordPress setzt schon no-cache', ma_rest_cache_kopfzeilen([], 'GET', true, $t), []);
pruefe('Eigene Angabe des Endpunkts bleibt (ma/v1/live: no-store)', ma_rest_cache_kopfzeilen(['Cache-Control' => 'no-store'], 'GET', false, $t), []);
pruefe('Eigene Angabe auch in anderer Schreibweise', ma_rest_cache_kopfzeilen(['cache-control' => 'no-store', 'X-WP-Total' => '3'], 'GET', false, $t), []);
pruefe('Andere Kopfzeilen allein lösen nichts aus', ma_rest_cache_kopfzeilen(['X-WP-Total' => '3', 'Link' => '<x>; rel="next"'], 'GET', false, $t)['Cache-Control'], 'public, max-age=300');

echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
