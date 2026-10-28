<?php
/**
 * Dateien der Firmen-Entwürfe ohne Paket (28.10.2026, Plugin 1.36.1):
 * ma_firmen_datei nimmt data/<ordner>/<datei> aus dem Plugin, sonst einmal aus dem
 * Repository (Kopie unter uploads/ma-firmen/, nach einem Tag neu geholt). Fehlt die
 * Datei auch dort, eine Stunde Pause; eine alte Kopie gilt weiter. Nur Bild- und
 * Videodateien, kein Pfad außerhalb. Das Plugin-Paket enthält die Videos nicht.
 * Aufruf: php qa/wordpress/firmen-datei-test.php
 */
$tmp = sys_get_temp_dir() . '/ma-firmen-datei-' . getmypid();
@mkdir($tmp . '/plugin/data/banner', 0777, true);
@mkdir($tmp . '/uploads', 0777, true);
file_put_contents($tmp . '/plugin/data/banner/lokal.jpg', 'LOKAL');
define('ABSPATH', __DIR__ . '/');
define('MA_CORE_PATH', $tmp . '/plugin/');
define('DAY_IN_SECONDS', 86400); define('HOUR_IN_SECONDS', 3600); define('MB_IN_BYTES', 1048576);
function add_action(...$a) {}
function trailingslashit($s) { return rtrim($s, '/') . '/'; }
function wp_upload_dir($a = null, $b = true) { return ['basedir' => $GLOBALS['tmp'] . '/uploads']; }
function wp_mkdir_p($d) { return is_dir($d) || mkdir($d, 0777, true); }
function get_transient($k) { return $GLOBALS['transient'][$k] ?? false; }
function set_transient($k, $v, $t) { $GLOBALS['transient'][$k] = $v; return true; }
class WP_Error {}
function is_wp_error($x) { return $x instanceof WP_Error; }
function wp_remote_get($url, $o = []) { $GLOBALS['abrufe'][] = $url; return $GLOBALS['antworten'][$url] ?? ['code' => 404, 'body' => '']; }
function wp_remote_retrieve_response_code($r) { return $r['code']; }
function wp_remote_retrieve_body($r) { return $r['body']; }
require dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core/includes/firmen-entwuerfe.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-78s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
$R = MA_FIRMEN_REPO_DATEN;
$kopie = $tmp . '/uploads/ma-firmen/banner/merzlicht-1.mp4';

echo "Datei aus dem Plugin oder aus dem Repository\n";
pruefe('Quelle: Plugin-Daten auf main im Repository', $R, 'https://raw.githubusercontent.com/HKGrowthOperator/Merzenich-Aktuell/main/wordpress/plugin/merzenich-aktuell-core/data/');
pruefe('im Plugin vorhanden: Plugin-Pfad, kein Abruf', [ma_firmen_datei('banner', 'lokal.jpg'), $GLOBALS['abrufe'] ?? []], [$tmp . '/plugin/data/banner/lokal.jpg', []]);
$GLOBALS['antworten'][$R . 'banner/merzlicht-1.mp4'] = ['code' => 200, 'body' => 'VIDEO1'];
pruefe('fehlt im Plugin: einmal aus dem Repository, Kopie unter uploads/ma-firmen', [ma_firmen_datei('banner', 'merzlicht-1.mp4'), (string) @file_get_contents($kopie)], [$kopie, 'VIDEO1']);
ma_firmen_datei('banner', 'merzlicht-1.mp4');
pruefe('zweiter Aufruf: Kopie, kein neuer Abruf', count($GLOBALS['abrufe']), 1);
touch($kopie, time() - 2 * 86400);
$GLOBALS['antworten'][$R . 'banner/merzlicht-1.mp4'] = ['code' => 200, 'body' => 'VIDEO2'];
pruefe('Kopie älter als ein Tag: neu geholt', [ma_firmen_datei('banner', 'merzlicht-1.mp4'), file_get_contents($kopie), count($GLOBALS['abrufe'])], [$kopie, 'VIDEO2', 2]);
touch($kopie, time() - 2 * 86400);
$GLOBALS['antworten'][$R . 'banner/merzlicht-1.mp4'] = new WP_Error();
pruefe('GitHub antwortet nicht: alte Kopie gilt weiter', [ma_firmen_datei('banner', 'merzlicht-1.mp4'), file_get_contents($kopie)], [$kopie, 'VIDEO2']);
pruefe('fehlt überall: leer', ma_firmen_datei('banner', 'gibtsnicht.webm'), '');
$n = count($GLOBALS['abrufe']);
ma_firmen_datei('banner', 'gibtsnicht.webm');
pruefe('fehlt überall: eine Stunde kein neuer Abruf', count($GLOBALS['abrufe']), $n);
$n = count($GLOBALS['abrufe']);
pruefe('nur Bild und Video, kein Pfad nach oben, kein fremder Ordner', [ma_firmen_datei('banner', 'x.php'), ma_firmen_datei('banner', '../../wp-config.php'), ma_firmen_datei('../x', 'a.jpg'), count($GLOBALS['abrufe']) - $n], ['', '', '', 0]);
pruefe('basename: Ordnerangaben im Dateinamen zählen nicht', ma_firmen_datei('banner', 'irgendwo/lokal.jpg'), $tmp . '/plugin/data/banner/lokal.jpg');

echo "\nPaket und Medienübernahme\n";
$code = (string) file_get_contents(dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core/includes/firmen-entwuerfe.php');
pruefe('ma_firmen_medium und die Prüfsumme der Banner nutzen ma_firmen_datei', [str_contains($code, '$pfad = $datei !== \'\' ? ma_firmen_datei($ordner, $datei) : \'\';'), str_contains($code, "\$datei = fn(string \$d): string => \$d !== '' ? ma_firmen_datei('banner', \$d) : '';")], [true, true]);
$pakete = (string) file_get_contents(dirname(__DIR__, 2) . '/deploy/wp-pakete.mjs');
pruefe('wp-pakete.mjs: Anzeigenvideos nicht im Plugin-Paket', str_contains($pakete, "'*/data/banner/*.mp4', '*/data/banner/*.webm'"), true);
pruefe('wp-pakete.mjs: bricht ab, wenn ein Paket 8 MB erreicht', str_contains($pakete, '8 * 1024 * 1024'), true);

array_map('unlink', array_filter(glob($tmp . '/{,*/,*/*/,*/*/*/}*', GLOB_BRACE), 'is_file'));
echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
