<?php
/**
 * Relevanz 1–10 und Schnellfreigabe (includes/relevanz.php) ohne WordPress.
 * Aufruf: php qa/wordpress/relevanz-test.php
 */
define('ABSPATH', __DIR__);
define('MA_CORE_VERSION', 'test');
define('HOUR_IN_SECONDS', 3600);

class WP_Post { public $ID; public $post_status = 'pending'; public $post_date_gmt; public function __construct($id, $zeit) { $this->ID = $id; $this->post_date_gmt = $zeit; } }
$GLOBALS['meta'] = []; $GLOBALS['status'] = []; $GLOBALS['posts'] = []; $GLOBALS['sperre'] = [];
function add_action(...$a) {} function add_filter(...$a) {}
function get_post_meta($id, $k, $s = false) { return $GLOBALS['meta'][$id][$k] ?? ''; }
function update_post_meta($id, $k, $v) { $GLOBALS['meta'][$id][$k] = $v; return true; }
function delete_post_meta($id, $k) { unset($GLOBALS['meta'][$id][$k]); return true; }
function get_post_type($id) { return 'post'; }
function get_post_status($id) { return $GLOBALS['status'][$id] ?? 'pending'; }
function wp_update_post($a) {
    // Nachbau der Veroeffentlichungssperre: Beitragsbild ohne bestaetigte Bildrechte bleibt Entwurf.
    $id = $a['ID'];
    if (!empty($GLOBALS['sperre'][$id]) && ($GLOBALS['meta'][$id]['ma_image_rights_verified'] ?? '') !== '1') { $GLOBALS['status'][$id] = 'draft'; $GLOBALS['meta'][$id]['_ma_gate_reason'] = 'Bildrechte geprüft fehlt'; return $id; }
    $GLOBALS['status'][$id] = $a['post_status']; return $id;
}
function wp_get_current_user() { return (object) ['display_name' => 'Redaktion Test']; }
function current_time($f) { return '2026-09-30 12:00:00'; }
function get_post($id) { return $GLOBALS['posts'][$id] ?? null; }
function get_post_time($f, $gmt, $p) { return strtotime($p->post_date_gmt . ' UTC'); }

require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/relevanz.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-62s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Stufen\n";
pruefe('10 ist Top-Thema', ma_relevanz_stufe(10)['name'], 'Top-Thema');
pruefe('9 ist Top-Thema', ma_relevanz_stufe(9)['name'], 'Top-Thema');
pruefe('8 ist Wichtig', ma_relevanz_stufe(8)['name'], 'Wichtig');
pruefe('7 ist Wichtig', ma_relevanz_stufe(7)['name'], 'Wichtig');
pruefe('6 ist Normal', ma_relevanz_stufe(6)['name'], 'Normal');
pruefe('4 ist Normal', ma_relevanz_stufe(4)['name'], 'Normal');
pruefe('3 ist Nur Rubrik', ma_relevanz_stufe(3)['name'], 'Nur Rubrik');
pruefe('1 ist Nur Rubrik', ma_relevanz_stufe(1)['name'], 'Nur Rubrik');
$abgedeckt = []; foreach (ma_relevanz_stufen() as $s) for ($i = $s['von']; $i <= $s['bis']; $i++) $abgedeckt[] = $i; sort($abgedeckt);
pruefe('Stufen decken 1–10 lückenlos und einmal ab', $abgedeckt, range(1, 10));

echo "\nWerte\n";
pruefe('11 ist ungültig', ma_relevanz_saeubern('11'), 0);
pruefe('0 ist ungültig', ma_relevanz_saeubern(0), 0);
pruefe('"7" wird 7', ma_relevanz_saeubern('7'), 7);
pruefe('ohne Angabe gilt 5', ma_relevanz(99), 5);
update_post_meta(98, 'ma_relevanz', 9);
pruefe('gesetzte Relevanz wird gelesen', ma_relevanz(98), 9);

echo "\nFrisch\n";
$GLOBALS['posts'][1] = new WP_Post(1, gmdate('Y-m-d H:i:s', time() - 10 * 3600));
$GLOBALS['posts'][2] = new WP_Post(2, gmdate('Y-m-d H:i:s', time() - 80 * 3600));
pruefe('10 Stunden alt ist frisch', ma_relevanz_frisch(1), true);
pruefe('80 Stunden alt ist nicht mehr frisch', ma_relevanz_frisch(2), false);

echo "\nSchnellfreigabe\n";
$e = ma_schnellfreigabe(10, 8, true);
pruefe('Freigabe mit Startseite veröffentlicht', $e['ok'] && get_post_status(10) === 'publish', true);
pruefe('Relevanz gespeichert', get_post_meta(10, 'ma_relevanz'), 8);
pruefe('Startseite ja = automatisch', get_post_meta(10, 'ma_startplatz'), 'auto');
pruefe('Prüfhaken gesetzt', get_post_meta(10, 'ma_human_reviewed') . get_post_meta(10, 'ma_source_verified'), '11');
pruefe('Prüfer vermerkt', get_post_meta(10, 'ma_reviewed_by'), 'Redaktion Test');
pruefe('Meldung nennt die Bühne', str_contains($e['meldung'], 'Bühne'), true);
$e = ma_schnellfreigabe(11, 9, false);
pruefe('Startseite nein = nur Rubrik', get_post_meta(11, 'ma_startplatz'), 'aus');
pruefe('Meldung sagt nur Rubrik', str_contains($e['meldung'], 'nur in der Rubrik'), true);
update_post_meta(12, 'ma_startplatz', 'buehne-2');
ma_schnellfreigabe(12, 5, true);
pruefe('fester Platz bleibt bei Startseite ja', get_post_meta(12, 'ma_startplatz'), 'buehne-2');
update_post_meta(13, 'ma_startplatz', 'aus');
ma_schnellfreigabe(13, 6, true);
pruefe('„aus“ wird bei Startseite ja zu automatisch', get_post_meta(13, 'ma_startplatz'), 'auto');
$e = ma_schnellfreigabe(14, 0, true);
pruefe('ohne Relevanz keine Freigabe', $e['ok'] || get_post_status(14) === 'publish', false);
$GLOBALS['sperre'][15] = true;
$e = ma_schnellfreigabe(15, 7, true);
pruefe('Bild ohne geprüfte Rechte: bleibt Entwurf', $e['ok'] || get_post_status(15) === 'publish', false);
pruefe('… und die Antwort nennt den Grund', str_contains($e['meldung'], 'Bildrechte'), true);
$e = ma_schnellfreigabe(15, 7, true, true);
pruefe('mit Haken „Bildrechte geprüft“ veröffentlicht', $e['ok'] && get_post_status(15) === 'publish', true);

echo $fehler ? "\n$fehler Fehler.\n" : "\nAlle Pruefungen bestanden.\n";
exit($fehler ? 1 : 0);
