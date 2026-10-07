<?php
/**
 * Ressort-Menü und Ressortseiten (Theme 21.14.0, inc/ma21.php) ohne WordPress:
 * Themen-Links ab einer Meldung (bis 21.13 erst ab drei, Menüs schrumpften),
 * Kartenraster nur für die Nachrichten-Ressorts, nicht für Sport und Vereine.
 * Aufruf: php qa/wordpress/ressort-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('DAY_IN_SECONDS', 86400);
function add_action(...$a) {} function add_filter(...$a) {} function remove_action(...$a) {} function add_image_size(...$a) {}
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return (string) $s; }
function home_url($p = '') { return 'https://merzenich-aktuell.de' . $p; }
class WP_Term { public $count = 0; } class WP_Query {} class WP_Post {}
$GLOBALS['themen'] = ['gemeinderat' => 1, 'haushalt' => 2, 'schule' => 3, 'leer' => 0];
function get_term_by($feld, $slug, $tax) { if (!isset($GLOBALS['themen'][$slug])) return false; $t = new WP_Term(); $t->count = $GLOBALS['themen'][$slug]; return $t; }
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-70s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Linkgruppen des Ressort-Menüs\n";
$g = ma21_menue_gruppen_pruefen([
    ['titel' => 'Gemeinde', 'links' => [['Gemeinderat', '/thema/gemeinderat/'], ['Haushalt', '/thema/haushalt/'], ['Schule', '/thema/schule/'], ['Ohne Meldung', '/thema/leer/'], ['Gibt es nicht', '/thema/unbekannt/'], ['Rathaus', '/service/']]],
    ['titel' => 'Leer', 'links' => [['Ohne Meldung', '/thema/leer/']]],
    ['titel' => 'Orte', 'links' => [['Golzheim', '/golzheim/']]],
]);
pruefe('Themen ab einer Meldung bleiben (vorher erst ab drei)', array_column($g[0]['links'], 0), ['Gemeinderat', 'Haushalt', 'Schule', 'Rathaus']);
pruefe('Gruppe ohne Link fällt weg, Ortsteil zeigt auf /ort/', [count($g), $g[1]['titel'], $g[1]['links'][0][1]], [2, 'Orte', '/ort/golzheim/']);

echo "\nKartenraster der Ressortseiten\n";
pruefe('Raster für Aktuell, Blaulicht, Rathaus, Leben, Wirtschaft, Menschen', array_map('ma21_raster_seite', ['ressort-nachrichten', 'ressort-blaulicht', 'ressort-rathaus', 'ressort-leben', 'ressort-wirtschaft', 'ressort-menschen']), [true, true, true, true, true, true]);
pruefe('Sport, Vereine, Ort, Thema und Suche behalten ihr Layout', array_map('ma21_raster_seite', ['ressort-sport', 'ressort-vereine', 'liste', '']), [false, false, false, false]);
pruefe('Aufmacher plus gerade Kartenzahl je Seite', [ma21_raster_anzahl('ressort-nachrichten') - 1, ma21_raster_anzahl('ressort-blaulicht') - 1], [20, 12]);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
