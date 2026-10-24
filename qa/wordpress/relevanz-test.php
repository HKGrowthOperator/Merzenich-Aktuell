<?php
/**
 * Relevanz in drei Stufen und Schnellfreigabe (includes/relevanz.php) ohne WordPress.
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
function get_current_user_id() { return 7; }
$GLOBALS['opt'] = [];
function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function update_option($k, $v, $a = null) { $GLOBALS['opt'][$k] = $v; return true; }
function current_time($f) { return '2026-09-30 12:00:00'; }
function get_post($id) { return $GLOBALS['posts'][$id] ?? null; }
function get_post_time($f, $gmt, $p) { return strtotime($p->post_date_gmt . ' UTC'); }
// Umstellung: alle Beiträge mit Relevanz, ohne gesicherten Altwert.
function get_posts($a) { return array_values(array_filter(array_keys($GLOBALS['meta']), fn($id) => ($GLOBALS['meta'][$id]['ma_relevanz'] ?? '') !== '' && !isset($GLOBALS['meta'][$id]['_ma_relevanz_alt']))); }

require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/relevanz.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-62s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Stufen\n";
pruefe('genau drei Stufen 1–3', array_map(fn($s) => $s['von'], ma_relevanz_stufen()), [1, 2, 3]);
pruefe('1 = Hoch, Aufmacher oder Bühne', [ma_relevanz_stufe(1)['name'], str_contains(ma_relevanz_stufe(1)['wo'], 'Aufmacher')], ['Hoch', true]);
pruefe('2 = Mitte', ma_relevanz_stufe(2)['name'], 'Mitte');
pruefe('3 = Niedrig', ma_relevanz_stufe(3)['name'], 'Niedrig');
pruefe('Punkte: Hoch 9, Mitte 6, Niedrig 2', [ma_relevanz_punkte(1), ma_relevanz_punkte(2), ma_relevanz_punkte(3)], [9.0, 6.0, 2.0]);
pruefe('Farbgruppen', [ma_relevanz_gruppe(1), ma_relevanz_gruppe(2), ma_relevanz_gruppe(3)], ['hoch', 'mitte', 'niedrig']);

echo "\nSäubern\n";
pruefe('4 ist ungültig (alte Skala)', ma_relevanz_saeubern('4'), 0);
pruefe('10 ist ungültig', ma_relevanz_saeubern(10), 0);
pruefe('0 ist ungültig', ma_relevanz_saeubern(0), 0);
pruefe('"3" wird 3', ma_relevanz_saeubern('3'), 3);
pruefe('ohne Angabe gilt Mitte (2)', ma_relevanz(99), 2);
update_post_meta(98, 'ma_relevanz', 1);
pruefe('gesetzte Stufe wird gelesen', ma_relevanz(98), 1);

echo "\nUmstellung 1–10 → 3 Stufen\n";
$GLOBALS['meta'] = [];
foreach ([501 => 10, 502 => 8, 503 => 7, 504 => 5, 505 => 4, 506 => 3, 507 => 1, 508 => 0] as $id => $alt) update_post_meta($id, 'ma_relevanz', $alt);
$z = ma_relevanz_stufen_umstellen();
pruefe('8–10 → 1', [get_post_meta(501, 'ma_relevanz'), get_post_meta(502, 'ma_relevanz')], [1, 1]);
pruefe('4–7 → 2', [get_post_meta(503, 'ma_relevanz'), get_post_meta(504, 'ma_relevanz'), get_post_meta(505, 'ma_relevanz')], [2, 2, 2]);
pruefe('1–3 → 3', [get_post_meta(506, 'ma_relevanz'), get_post_meta(507, 'ma_relevanz')], [3, 3]);
pruefe('0 wird „ohne Angabe“', get_post_meta(508, 'ma_relevanz'), '');
pruefe('alter Wert gesichert', get_post_meta(501, '_ma_relevanz_alt'), '10');
pruefe('Zählung je Stufe', $z, [1 => 2, 2 => 3, 3 => 2, 'ohne' => 1]);
pruefe('kein „Nur in der Rubrik“ durch die Umstellung', get_post_meta(507, 'ma_startplatz'), '');
pruefe('Option gesetzt', get_option('ma_relevanz_stufen'), '3');
pruefe('zweiter Lauf ändert nichts (1 bleibt 1)', [ma_relevanz_stufen_umstellen(), get_post_meta(501, 'ma_relevanz')], [[1 => 0, 2 => 0, 3 => 0, 'ohne' => 0], 1]);
unset($GLOBALS['opt']['ma_relevanz_stufen']);
update_post_meta(509, 'ma_relevanz', 9);
ma_relevanz_stufen_umstellen();
pruefe('abgebrochener Lauf: Umgerechnetes wird nicht doppelt umgerechnet', [get_post_meta(501, 'ma_relevanz'), get_post_meta(506, 'ma_relevanz'), get_post_meta(509, 'ma_relevanz')], [1, 3, 1]);

echo "\nFrisch\n";
$GLOBALS['posts'][1] = new WP_Post(1, gmdate('Y-m-d H:i:s', time() - 10 * 3600));
$GLOBALS['posts'][2] = new WP_Post(2, gmdate('Y-m-d H:i:s', time() - 80 * 3600));
pruefe('10 Stunden alt ist frisch', ma_relevanz_frisch(1), true);
pruefe('80 Stunden alt ist nicht mehr frisch', ma_relevanz_frisch(2), false);

echo "\nSchnellfreigabe\n";
$e = ma_schnellfreigabe(10, 1, true);
pruefe('Freigabe mit Startseite veröffentlicht', $e['ok'] && get_post_status(10) === 'publish', true);
pruefe('Stufe gespeichert', get_post_meta(10, 'ma_relevanz'), 1);
pruefe('Startseite ja = automatisch', get_post_meta(10, 'ma_startplatz'), 'auto');
pruefe('Prüfhaken gesetzt', get_post_meta(10, 'ma_human_reviewed') . get_post_meta(10, 'ma_source_verified'), '11');
pruefe('Prüfer vermerkt', get_post_meta(10, 'ma_reviewed_by'), 'Redaktion Test');
pruefe('Meldung nennt den Platz (Aufmacher/Bühne)', str_contains($e['meldung'], 'Bühne'), true);
$e = ma_schnellfreigabe(11, 1, false);
pruefe('Startseite nein = nur Rubrik', get_post_meta(11, 'ma_startplatz'), 'aus');
pruefe('Startseiten-Freigabe als eigenes Feld: nein', get_post_meta(11, 'ma_startseite_freigabe'), 'nein');
pruefe('Startseiten-Freigabe ja mit Person', [get_post_meta(10, 'ma_startseite_freigabe'), get_post_meta(10, 'ma_startseite_freigabe_von')['u'] ?? 0], ['ja', 7]);
pruefe('Meldung sagt nur Rubrik', str_contains($e['meldung'], 'nur in der Rubrik'), true);
update_post_meta(12, 'ma_startplatz', 'buehne-2');
ma_schnellfreigabe(12, 2, true);
pruefe('fester Platz bleibt bei Startseite ja', get_post_meta(12, 'ma_startplatz'), 'buehne-2');
update_post_meta(13, 'ma_startplatz', 'aus');
ma_schnellfreigabe(13, 2, true);
pruefe('„aus“ wird bei Startseite ja zu automatisch', get_post_meta(13, 'ma_startplatz'), 'auto');
$e = ma_schnellfreigabe(14, 0, true);
pruefe('ohne Stufe keine Freigabe', $e['ok'] || get_post_status(14) === 'publish', false);
pruefe('… und die Antwort nennt die drei Stufen', str_contains($e['meldung'], 'Hoch, Mitte oder Niedrig'), true);
$e = ma_schnellfreigabe(16, 8, true);
pruefe('alte Zahl 8 wird nicht als Stufe angenommen', $e['ok'] || get_post_status(16) === 'publish', false);
$GLOBALS['sperre'][15] = true;
$e = ma_schnellfreigabe(15, 2, true);
pruefe('Bild ohne geprüfte Rechte: bleibt Entwurf', $e['ok'] || get_post_status(15) === 'publish', false);
pruefe('… und die Antwort nennt den Grund', str_contains($e['meldung'], 'Bildrechte'), true);
$e = ma_schnellfreigabe(15, 2, true, true);
pruefe('mit Haken „Bildrechte geprüft“ veröffentlicht', $e['ok'] && get_post_status(15) === 'publish', true);

echo "\nPlatzierung\n";
$jetzt = time();
$neu = fn($id, $r, $platz, $stunden) => ($GLOBALS['posts'][$id] = new WP_Post($id, gmdate('Y-m-d H:i:s', $jetzt - $stunden * 3600))) && update_post_meta($id, 'ma_relevanz', $r) && update_post_meta($id, 'ma_startplatz', $platz);
$neu(201, 1, 'auto', 2); $neu(202, 1, 'aus', 2); $neu(203, 2, 'auto', 2); $neu(204, 3, 'auto', 2);
pruefe('Startseite ja, Hoch, frisch → Aufmacher-Kandidat', ma_relevanz_zone(201, $jetzt), 'hero');
pruefe('Startseite nein, Hoch → nicht auf der Startseite', ma_relevanz_zone(202, $jetzt), 'aus');
pruefe('… und keine Startseiten-Freigabe', ma_relevanz_startseite(202), false);
pruefe('Mitte → Blöcke (feed)', ma_relevanz_zone(203, $jetzt), 'feed');
pruefe('Niedrig → unten, aber auf der Startseite', [ma_relevanz_zone(204, $jetzt), ma_relevanz_startseite(204)], ['unten', true]);
$sortiert = fn() => array_map(fn($p) => $p->ID, ma_relevanz_sortieren([$GLOBALS['posts'][203], $GLOBALS['posts'][204], $GLOBALS['posts'][201]], $jetzt));
pruefe('Sortierung: Hoch, Mitte, Niedrig', $sortiert(), [201, 203, 204]);
update_post_meta(204, 'ma_relevanz', 1);
pruefe('Stufe ändern verschiebt die Platzierung', [array_search(204, $sortiert()) < array_search(203, $sortiert()), ma_relevanz_zone(204, $jetzt)], [true, 'hero']);
pruefe('… gleich bei erneutem Laden (stabil)', $sortiert(), $sortiert());
$neu(205, 1, 'auto', 24 * 4); $neu(206, 2, 'auto', 1);
pruefe('Alte Hoch (4 Tage) verdrängt frische Mitte nicht', array_map(fn($p) => $p->ID, ma_relevanz_sortieren([$GLOBALS['posts'][205], $GLOBALS['posts'][206]], $jetzt)), [206, 205]);
pruefe('Alte Hoch ist kein Aufmacher mehr (nicht frisch)', ma_relevanz_zone(205, $jetzt), 'oben');
$neu(209, 1, 'auto', 24 * 2); $neu(210, 2, 'auto', 1);
pruefe('Hoch von vorgestern steht noch vor frischer Mitte', array_map(fn($p) => $p->ID, ma_relevanz_sortieren([$GLOBALS['posts'][210], $GLOBALS['posts'][209]], $jetzt)), [209, 210]);
$neu(207, 2, 'auto', 5); $neu(208, 2, 'auto', 1);
pruefe('Gleiche Stufe: die neuere zuerst', array_map(fn($p) => $p->ID, ma_relevanz_sortieren([$GLOBALS['posts'][207], $GLOBALS['posts'][208]], $jetzt)), [208, 207]);
// Zwei getrennte Freigaben (KBS 01.10.2026): ohne Entscheidung nur Rubrik; Altbestand behält die bisherige Regel.
$GLOBALS['opt']['ma_startseite_freigabe_seit'] = $jetzt - 3600;
$neu(301, 2, '', 30); $neu(302, 2, '', 0);
pruefe('Altbestand ohne Entscheidung: Startseite wie bisher', ma_relevanz_startseite(301), true);
pruefe('neue Meldung ohne Entscheidung: nur Rubrik', ma_relevanz_startseite(302), false);
update_post_meta(302, 'ma_startseite_freigabe', 'ja');
pruefe('neue Meldung mit Startseiten-Freigabe: Startseite', ma_relevanz_startseite(302), true);
update_post_meta(301, 'ma_startseite_freigabe', 'nein');
pruefe('Altbestand mit „nein“: nur Rubrik', ma_relevanz_startseite(301), false);
pruefe('nicht vorhandene Meldung: nie Startseite', ma_relevanz_startseite(999), false);

echo "\nQuelltext\n";
$q = file_get_contents(__DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/relevanz.php');
pruefe('keine alte Umstellung „1–3 = Nur in der Rubrik“ mehr', str_contains($q, "ma_relevanz_umstellung"), false);
pruefe('keine 10er-Knopfreihe, keine Prioritätsauswahl', [str_contains($q, 'repeat(10'), str_contains($q, 'ma_prioritaet_auswahl')], [false, false]);
$lj = file_get_contents(__DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/assets/layout.js');
pruefe('Layout-Board: Auswahl 1–3, Vorgabe Mitte', [str_contains($lj, 'i <= 3'), str_contains($lj, "relevanz.value = '2'")], [true, true]);

echo $fehler ? "\n$fehler Fehler.\n" : "\nAlle Pruefungen bestanden.\n";
exit($fehler ? 1 : 0);
