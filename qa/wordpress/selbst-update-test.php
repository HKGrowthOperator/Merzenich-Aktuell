<?php
/**
 * Selbst-Update (includes/selbst-update.php) ohne WordPress: release.json prüfen und
 * entscheiden, was installiert wird (nur neuere Versionen, nie zurück).
 * Aufruf: php qa/wordpress/selbst-update-test.php
 */
define('ABSPATH', __DIR__); define('MA_CORE_VERSION', '1.39.0'); define('MA_CORE_PATH', __DIR__ . '/');
function add_action(...$a) {} function add_filter(...$a) {} function register_deactivation_hook(...$a) {}

require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/selbst-update.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-66s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

$h = str_repeat('a', 64);
$m = ['plugin' => ['version' => '1.40.0', 'zip' => 'wordpress-delivery/merzenich-aktuell-core.zip', 'sha256' => $h],
      'theme' => ['version' => '21.24.2', 'zip' => 'wordpress-delivery/merzenich-aktuell-theme.zip', 'sha256' => $h]];

echo "Manifest\n";
pruefe('Vollständiges Manifest ist gültig', ma_su_manifest_gueltig($m), true);
pruefe('Kein Array: ungültig', ma_su_manifest_gueltig('x'), false);
$x = $m; unset($x['theme']); pruefe('Ohne Theme: ungültig', ma_su_manifest_gueltig($x), false);
$x = $m; $x['plugin']['sha256'] = 'ABC'; pruefe('Prüfsumme nicht 64 Hex: ungültig', ma_su_manifest_gueltig($x), false);
$x = $m; $x['plugin']['version'] = '1.40'; pruefe('Version nicht X.Y.Z: ungültig', ma_su_manifest_gueltig($x), false);
$x = $m; $x['plugin']['zip'] = '../wp-config.php'; pruefe('Pfad außerhalb wordpress-delivery: ungültig', ma_su_manifest_gueltig($x), false);
$x = $m; $x['plugin']['zip'] = 'https://fremd.example/x.zip'; pruefe('Fremde Adresse als Paket: ungültig', ma_su_manifest_gueltig($x), false);

echo "Was wird installiert\n";
pruefe('Neueres Plugin, gleiches Theme: nur Plugin', array_keys(ma_su_faellig($m, '1.39.0', '21.24.2')), ['plugin']);
pruefe('Beides neuer: Plugin vor Theme', array_keys(ma_su_faellig($m, '1.38.0', '21.24.1')), ['plugin', 'theme']);
pruefe('Gleiche Versionen: nichts', ma_su_faellig($m, '1.40.0', '21.24.2'), []);
pruefe('Installiert ist neuer: nie zurück', ma_su_faellig($m, '1.41.0', '21.25.0'), []);
pruefe('Zweistellige Teile richtig verglichen (1.40.0 > 1.9.9)', array_keys(ma_su_faellig($m, '1.9.9', '21.24.2')), ['plugin']);
pruefe('Theme nicht installiert: Theme nicht anfassen', array_keys(ma_su_faellig($m, '1.40.0', '')), []);

echo "Quelle\n";
pruefe('Nur aus dem eigenen Repository, Zweig main', MA_SU_ROH, 'https://raw.githubusercontent.com/HKGrowthOperator/merzenich-aktuell/main/');

echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
