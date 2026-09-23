<?php
/**
 * Prueft Sport-Haertung und JSON-Import ohne WordPress.
 * Aufruf: php qa/wordpress/sport-test.php
 */
define('ABSPATH', __DIR__);
$GLOBALS['opt'] = [];
function get_option($k, $d = []) { return $GLOBALS['opt'][$k] ?? $d; }
function wp_parse_args($a, $d) { return array_merge($d, (array)$a); }
function add_shortcode(...$a) {}
function esc_html($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
function esc_url($s) { return (string)$s; }
class WP_Error { public $m; function __construct($c, $m) { $this->m = $m; } function get_error_message() { return $this->m; } }
function is_wp_error($x) { return $x instanceof WP_Error; }

require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/sport.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) {
    global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++;
    printf("  %-60s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')');
}

echo "Kaputte Optionswerte bringen die Ausgabe nicht mehr um\n";
$GLOBALS['opt']['ma_sport_data'] = ['last_match' => 'kaputt', 'table' => 'auch kaputt', 'next_match' => ['home' => 'A']];
$d = ma_sport_data();
pruefe('last_match als String -> leeres Array', $d['last_match'], []);
pruefe('table als String -> leeres Array', $d['table'], []);
pruefe('next_match bleibt', $d['next_match']['home'] ?? null, 'A');
$html = ma_sport_shortcode();
pruefe('Shortcode rendert trotzdem', strpos($html, 'ma-sport-block') !== false, true);

$GLOBALS['opt']['ma_sport_data'] = ['table' => [null, 'x', ['team' => ''], ['team' => 'SC Merzenich', 'points' => 9]]];
pruefe('Tabellenzeilen ohne team werden verworfen', count(ma_sport_data()['table']), 1);

$GLOBALS['opt']['ma_sport_data'] = [];
pruefe('ohne Daten: Leerzustand statt Block', strpos(ma_sport_shortcode(), 'ma-empty') !== false, true);

echo "\nImport aus sport-current.json\n";
$json = file_get_contents(__DIR__ . '/../../chatgpt-site/api/sport-current.json');
$fixture = json_decode($json, true);
$r = ma_sport_import_from_json($json);
$lastDate = new DateTimeImmutable($fixture['lastMatch']['date']);
$lastDate = $lastDate->setTimezone(new DateTimeZone('Europe/Berlin'));
pruefe('Import liefert Array', is_array($r), true);
pruefe('bestaetigtes Ergebnis wird letztes Spiel', $r['last_match']['score'] ?? null, $fixture['lastMatch']['score'] ?? null);
pruefe('Datum lesbar formatiert', $r['last_match']['date'] ?? null, $lastDate->format('d.m.Y · H:i') . ' Uhr');
pruefe('naechstes Spiel uebernommen', $r['next_match']['home'] ?? null, $fixture['nextMatch']['home'] ?? null);
pruefe('Tabelle: alle Zeilen', count($r['table']), count($fixture['table'] ?? []));
pruefe('Tabellenzeile traegt rank/points/goals', isset($r['table'][0]['rank'], $r['table'][0]['points'], $r['table'][0]['goals']), true);
pruefe('Quelle uebernommen', $r['source_url'] !== '', true);

echo "\nUnbestaetigtes Ergebnis wird offenes Spiel\n";
$j = $fixture; $j['lastMatch']['confirmed'] = false;
$r2 = ma_sport_import_from_json(json_encode($j));
pruefe('kein letztes Spiel', $r2['last_match'], []);
pruefe('sondern pending', $r2['pending_match']['home'] ?? null, $fixture['lastMatch']['home'] ?? null);
pruefe('pending traegt kein Ergebnis', array_key_exists('score', $r2['pending_match']), false);

echo "\nFehlerfaelle\n";
pruefe('kaputtes JSON -> WP_Error', is_wp_error(ma_sport_import_from_json('{nein')), true);
pruefe('leere Tabelle bleibt leer', ma_sport_import_from_json('{"table":"x"}')['table'], []);

echo "\n" . ($fehler === 0 ? "Alle Pruefungen bestanden.\n" : "$fehler Pruefung(en) fehlgeschlagen.\n");
exit($fehler === 0 ? 0 : 1);
