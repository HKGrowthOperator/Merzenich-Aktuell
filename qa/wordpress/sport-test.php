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
function wp_date($f, $ts = null) { return (new DateTimeImmutable('@' . ($ts ?? time())))->setTimezone(new DateTimeZone('Europe/Berlin'))->format($f); }
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

echo "\nBackend als einzige Quelle (1.21.0)\n";
pruefe('Datum aus Backend-Text', ma_sport_datum_iso('27.09.2026 · 15:00 Uhr'), '2026-09-27T15:00:00+02:00');
pruefe('Winterzeit', ma_sport_datum_iso('08.11.2026 · 14:30 Uhr'), '2026-11-08T14:30:00+01:00');
pruefe('Unlesbares Datum', ma_sport_datum_iso('31.02.2026'), '');
$GLOBALS['opt']['ma_sport_data'] = [];
pruefe('Ohne Backend-Daten: null (Repository gilt)', ma_sport_als_json(), null);
$GLOBALS['opt']['ma_sport_data'] = ['checked_at' => '05.10.2026 · 12:00 Uhr', 'source_url' => 'https://www.fussball.de/x',
    'last_match' => ['home' => 'SC Merzenich', 'away' => 'SG Nörvenich-Hochkirchen', 'score' => '8 : 2', 'date' => '27.09.2026 · 15:00 Uhr', 'report' => 'https://www.fussball.de/bericht'],
    'next_match' => ['home' => 'TuS Barmen', 'away' => 'SC Merzenich', 'date' => '04.10.2026 · 15:00 Uhr'],
    'table' => [['rank' => '1', 'team' => 'SC Merzenich', 'played' => '7', 'wins' => '5', 'draws' => '1', 'losses' => '1', 'points' => '16', 'goals' => '25:8']]];
$j = ma_sport_als_json();
pruefe('Format wie sport-current.json: generated ISO', $j['generated'], '2026-10-05T12:00:00+02:00');
pruefe('Letztes Spiel bestätigt mit Bericht', [$j['lastMatch']['score'], $j['lastMatch']['confirmed'], $j['lastMatch']['reportUrl']], ['8 : 2', true, 'https://www.fussball.de/bericht']);
pruefe('Tabelle mit S/U/N, Differenz und Heimteam', [$j['table'][0]['wins'], $j['table'][0]['diff'], $j['table'][0]['homeTeam'], $j['table'][0]['place']], [5, 17, true, 1]);
pruefe('Quelle FUSSBALL.DE', $j['source'], 'FUSSBALL.DE');
pruefe('Spiel vom 04.10. ohne Ergebnis: Hinweis', ma_sport_ueberfaellig(strtotime('2026-10-05T10:00:00+02:00')) !== '', true);
pruefe('Spiel läuft noch: kein Hinweis', ma_sport_ueberfaellig(strtotime('2026-10-04T16:00:00+02:00')), '');
$GLOBALS['opt']['ma_sport_data']['last_match']['date'] = '04.10.2026 · 15:00 Uhr';
pruefe('Ergebnis eingetragen: kein Hinweis', ma_sport_ueberfaellig(strtotime('2026-10-05T10:00:00+02:00')), '');
$GLOBALS['opt']['ma_sport_data'] = ['pending_match' => ['home' => 'A', 'away' => 'B', 'date' => '04.10.2026 · 15:00 Uhr']];
pruefe('Offenes Spiel: unbestätigt ohne Ergebnis', [ma_sport_als_json()['lastMatch']['confirmed'], ma_sport_als_json()['lastMatch']['score']], [false, '']);
$r3 = ma_sport_import_from_json($json);
pruefe('Import übernimmt S/U/N und Bericht', [isset($r3['table'][0]['wins']), ($r3['last_match']['report'] ?? '') !== ''], [true, true]);

echo "\n" . ($fehler === 0 ? "Alle Pruefungen bestanden.\n" : "$fehler Pruefung(en) fehlgeschlagen.\n");
exit($fehler === 0 ? 0 : 1);
