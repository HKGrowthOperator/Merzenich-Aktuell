<?php
/**
 * Kostenfreier Testeintrag (includes/testeintrag.php, 16.10.2026): Kennzeichnung,
 * Zeitraum ab der ersten Veröffentlichung, Ende nach 30 Tagen ohne Verlängerung.
 * Aufruf: php qa/wordpress/testeintrag-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('MA_STATUS_ARCHIV', 'ma_archiv');
$plugin = dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core/includes/';

class WP_Post { public $ID = 0; public $post_type = 'post'; public function __construct(array $a = []) { foreach ($a as $k => $v) $this->$k = $v; } }
$GLOBALS['meta'] = []; $GLOBALS['hooks'] = []; $GLOBALS['jetzt'] = '2026-10-20 09:00:00'; $GLOBALS['aenderungen'] = []; $GLOBALS['verlauf'] = [];
function add_action($hook, $fn, $prio = 10, $n = 1) { $GLOBALS['hooks'][$hook][] = $fn; }
function get_post_meta($id, $k, $single = false) { return $GLOBALS['meta'][$id][$k] ?? ''; }
function get_post_status($id) { return $GLOBALS['meta'][$id]['_status'] ?? 'draft'; }
function update_post_meta($id, $k, $v) { $GLOBALS['meta'][$id][$k] = $v; return true; }
function current_time($f) { return $GLOBALS['jetzt']; }
function mysql2date($f, $d) { return date($f, strtotime($d)); }
function wp_update_post($a) { $GLOBALS['aenderungen'][] = $a; return $a['ID']; }
function ma_verlauf_eintragen(int $id, string $aktion, string $notiz = '') { $GLOBALS['verlauf'][] = [$id, $aktion]; }
function get_posts($q) { return array_keys(array_filter($GLOBALS['meta'], fn($m) => ($m['_status'] ?? '') === 'publish' && ($m['ma_gesponsert_art'] ?? '') === 'test' && ($m['ma_test_ende'] ?? '') !== '' && $m['ma_test_ende'] <= $GLOBALS['jetzt'])); }
require $plugin . 'testeintrag.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
$gespeichert = $GLOBALS['hooks']['wp_after_insert_post'][0];

echo "Kennzeichnung\n";
$GLOBALS['meta'][1] = ['ma_gesponsert' => '1'];
$GLOBALS['meta'][2] = ['ma_gesponsert' => '1', 'ma_gesponsert_art' => 'test'];
$GLOBALS['meta'][3] = ['ma_gesponsert_art' => 'test'];
pruefe('bezahlter Beitrag: Anzeige · Gesponsert', ma_gesponsert_label(1), 'Anzeige · Gesponsert');
pruefe('bezahlter Beitrag: Bezahlte Präsentation', ma_gesponsert_hinweis(1), 'Bezahlte Präsentation');
pruefe('Testeintrag: Anzeige · kostenfreier Testeintrag', ma_gesponsert_label(2), 'Anzeige · kostenfreier Testeintrag');
pruefe('Testeintrag vor Veröffentlichung: ohne Enddatum', ma_gesponsert_hinweis(2), 'Kostenfreie Präsentation im Testzeitraum');
pruefe('ohne Haken „Gesponsert“ kein Testeintrag', ma_ist_testeintrag(3), false);
pruefe('Kennzeichnung mit WP_Post', ma_gesponsert_label(new WP_Post(['ID' => 2])), 'Anzeige · kostenfreier Testeintrag');
// Firmen-Entwürfe (24.10.2026): „anzeige“ und „eigen“ (Herausgeberin), ohne Testzeitraum.
$GLOBALS['meta'][7] = ['ma_gesponsert' => '1', 'ma_gesponsert_art' => 'anzeige'];
$GLOBALS['meta'][8] = ['ma_gesponsert' => '1', 'ma_gesponsert_art' => 'eigen'];
pruefe('Anzeige: Kennzeichnung und Hinweis, kein Testeintrag', [ma_gesponsert_label(7), ma_gesponsert_hinweis(7), ma_ist_testeintrag(7)], ['Anzeige', 'Werbliche Präsentation eines Unternehmens', false]);
pruefe('In eigener Sache: Kennzeichnung und Hinweis', [ma_gesponsert_label(8), ma_gesponsert_hinweis(8)], ['Anzeige · in eigener Sache', 'Präsentation der Herausgeberin von Merzenich Aktuell']);
$ws = (string) file_get_contents(dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core/includes/werbung-stat.php');
pruefe('Kasten „Kennzeichnung“: Speichern behält „anzeige“ und „eigen“', str_contains($ws, "!in_array(\$art, ['anzeige', 'eigen'], true)) delete_post_meta(\$id, 'ma_gesponsert_art')"), true);

echo "Zeitraum\n";
pruefe('Ende = Start + 30 Tage', ma_test_ende_berechnen('2026-10-20 09:00:00'), '2026-11-19 09:00:00');
pruefe('Ende über den Monatswechsel (Februar)', ma_test_ende_berechnen('2027-02-10 12:00:00', 30), '2027-03-12 12:00:00');
pruefe('ohne Start kein Ende', ma_test_ende_berechnen('kein Datum'), '');
pruefe('am letzten Tag noch nicht abgelaufen', ma_test_abgelaufen('2026-11-19 09:00:00', '2026-11-19 08:59:59'), false);
pruefe('zum Ende abgelaufen', ma_test_abgelaufen('2026-11-19 09:00:00', '2026-11-19 09:00:00'), true);
pruefe('ohne Ende nie abgelaufen', ma_test_abgelaufen('', '2030-01-01 00:00:00'), false);

echo "Start mit der ersten Veröffentlichung (nach dem Speichern der Felder)\n";
$p2 = new WP_Post(['ID' => 2]);
$gespeichert(2, $p2);
pruefe('Entwurf speichern startet nichts', $GLOBALS['meta'][2]['ma_test_ende'] ?? '', '');
$GLOBALS['meta'][2]['_status'] = 'publish';
$gespeichert(2, $p2);
pruefe('Veröffentlichen setzt den Start', $GLOBALS['meta'][2]['ma_test_start'], '2026-10-20 09:00:00');
pruefe('und das Ende 30 Tage später', $GLOBALS['meta'][2]['ma_test_ende'], '2026-11-19 09:00:00');
pruefe('Hinweis nennt das Ende', ma_gesponsert_hinweis(2), 'Kostenfreie Präsentation im Testzeitraum bis 19.11.2026');
$GLOBALS['jetzt'] = '2026-10-25 10:00:00';
$gespeichert(2, $p2);
pruefe('erneutes Speichern oder Veröffentlichen verlängert nicht', $GLOBALS['meta'][2]['ma_test_ende'], '2026-11-19 09:00:00');
$GLOBALS['meta'][1]['_status'] = 'publish';
$gespeichert(1, new WP_Post(['ID' => 1]));
pruefe('bezahlter Beitrag bekommt keinen Zeitraum', $GLOBALS['meta'][1]['ma_test_ende'] ?? '', '');
$GLOBALS['meta'][4] = ['ma_gesponsert' => '1', '_status' => 'publish'];
$gespeichert(4, new WP_Post(['ID' => 4]));
$GLOBALS['meta'][4]['ma_gesponsert_art'] = 'test';
$gespeichert(4, new WP_Post(['ID' => 4]));
pruefe('Haken an einem schon veröffentlichten Beitrag: Zeitraum beginnt jetzt', $GLOBALS['meta'][4]['ma_test_ende'] ?? '', '2026-11-24 10:00:00');
$GLOBALS['meta'][5] = ['ma_gesponsert' => '1', 'ma_gesponsert_art' => 'test', '_status' => 'publish'];
$gespeichert(5, new WP_Post(['ID' => 5, 'post_type' => 'ma_business']));
pruefe('nur Beiträge, keine Profile', $GLOBALS['meta'][5]['ma_test_ende'] ?? '', '');
unset($GLOBALS['meta'][4], $GLOBALS['meta'][5]);

echo "Täglicher Lauf\n";
$GLOBALS['meta'][2]['_status'] = 'publish';
$GLOBALS['jetzt'] = '2026-11-19 08:00:00';
pruefe('vor dem Ende: nichts archiviert', ma_testeintraege_pruefen(), 0);
$GLOBALS['jetzt'] = '2026-11-19 09:30:00';
pruefe('nach dem Ende: ein Beitrag archiviert', ma_testeintraege_pruefen(), 1);
pruefe('Status Archiv, keine Löschung', $GLOBALS['aenderungen'][0] ?? null, ['ID' => 2, 'post_status' => 'ma_archiv']);
pruefe('Verlauf vermerkt das Ende', end($GLOBALS['verlauf'])[1] ?? '', 'Testzeitraum beendet');
pruefe('Lauf ist täglich geplant', isset($GLOBALS['hooks'][MA_TEST_HOOK]), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
