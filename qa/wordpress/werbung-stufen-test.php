<?php
/**
 * Priorität der Werbemittel in drei Stufen wie die Relevanz (28.10.2026):
 * 1 Hoch, 2 Mitte, 3 Niedrig, ohne Wert Mitte. Einmalige Umstellung der
 * früheren freien Zahl (höhere gewann): ab 8 Hoch, 4 bis 7 Mitte, sonst Niedrig.
 * Im Backend eine Auswahl mit drei Stufen statt eines Zahlenfelds; die frühere
 * Priorität 1–10 der Meldungen (ma_prioritaet) ist nicht mehr registriert.
 * Aufruf: php qa/wordpress/werbung-stufen-test.php
 */
define('ABSPATH', __DIR__ . '/');
$W = dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core';
$GLOBALS['meta'] = []; $GLOBALS['opt'] = [];
function get_post_meta($id, $k, $s = false) { return $GLOBALS['meta'][$id][$k] ?? ''; }
function update_post_meta($id, $k, $v) { $GLOBALS['meta'][$id][$k] = $v; return true; }
function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function update_option($k, $v, $a = null) { $GLOBALS['opt'][$k] = $v; return true; }
function get_posts($q) { return array_keys(array_filter($GLOBALS['meta'], fn($m) => isset($m[$q['meta_key']]))); }
require $W . '/includes/ads.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Stufen\n";
$GLOBALS['meta'] = [1 => ['ma_ad_priority' => '1'], 2 => ['ma_ad_priority' => '3'], 3 => [], 4 => ['ma_ad_priority' => '7']];
pruefe('1 Hoch, 3 Niedrig, ohne Wert und ungültig: 2 Mitte', [ma_ad_stufe(1), ma_ad_stufe(2), ma_ad_stufe(3), ma_ad_stufe(4)], [1, 3, 2, 2]);

echo "\nUmstellung der alten Zahlen (einmalig)\n";
$GLOBALS['meta'] = [10 => ['ma_ad_priority' => '10'], 11 => ['ma_ad_priority' => '8'], 12 => ['ma_ad_priority' => '5'], 13 => ['ma_ad_priority' => '4'], 14 => ['ma_ad_priority' => '2'], 15 => ['ma_ad_priority' => '0'], 16 => []];
ma_ad_stufen_umstellen();
pruefe('10 und 8 → Hoch, 5 und 4 → Mitte, 2 und 0 → Niedrig, ohne Wert bleibt leer', array_map(fn($m) => $m['ma_ad_priority'] ?? '', $GLOBALS['meta']), [10 => '1', 11 => '1', 12 => '2', 13 => '2', 14 => '3', 15 => '3', 16 => '']);
$GLOBALS['meta'][20] = ['ma_ad_priority' => '1'];
ma_ad_stufen_umstellen();
pruefe('läuft nur einmal (neue Stufe 1 bleibt Hoch)', $GLOBALS['meta'][20]['ma_ad_priority'], '1');

echo "\nBackend und Rest\n";
$ca = (string) file_get_contents($W . '/includes/content-admin.php');
pruefe('Werbemittel: Auswahl Hoch/Mitte/Niedrig statt Zahlenfeld', [str_contains($ca, "['1'=>'1 · Hoch','2'=>'2 · Mitte','3'=>'3 · Niedrig']"), str_contains($ca, "ma_admin_input('ma_ad_priority'")], [true, false]);
pruefe('Speichern: nur 1, 2 oder 3', [str_contains($ca, "'ma_ad_priority'=>'stufe'"), str_contains($ca, "if (\$type==='stufe') return in_array((string)\$value,['1','2','3'],true) ? (string)\$value : '';")], [true, true]);
$rel = (string) file_get_contents($W . '/includes/relevanz.php');
pruefe('frühere Priorität 1–10 der Meldungen nicht mehr registriert', str_contains($rel, "register_post_meta('post', 'ma_prioritaet'"), false);
$js = (string) file_get_contents($W . '/assets/layout.js');
pruefe('Board, „Neue Meldung“: Relevanz 1 bis 3', str_contains($js, 'for (var i = 1; i <= 3; i++) relevanz.append'), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
