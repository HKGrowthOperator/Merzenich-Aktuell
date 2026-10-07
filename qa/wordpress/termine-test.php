<?php
/**
 * Termine im neuen Markup (inc/termine.php, Theme 21.12.0) und Webseiten-Symbole
 * (inc/ma21.php) ohne WordPress: Uhrzeit, Listenzeile mit Filterangaben und
 * Knöpfen, Umstellung weg von der alten Vorlage, Symbol-Dateien und Kopf-Links.
 * Aufruf: php qa/wordpress/termine-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('DAY_IN_SECONDS', 86400);
$GLOBALS['t'] = ['single' => '', 'archiv' => ''];
function add_action(...$a) {} function add_filter(...$a) {} function remove_action(...$a) {} function add_image_size(...$a) {}
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return (string) $s; }
function trailingslashit($s) { return rtrim((string) $s, '/') . '/'; }
function wp_timezone() { return new DateTimeZone('Europe/Berlin'); }
function wp_date($f, $ts = null) { return (new DateTimeImmutable('@' . ($ts ?? time())))->setTimezone(wp_timezone())->format($f); }
function is_singular($t = '') { return $GLOBALS['t']['single'] !== '' && in_array($GLOBALS['t']['single'], (array) $t, true); }
function is_post_type_archive($t = '') { return $GLOBALS['t']['archiv'] !== '' && in_array($GLOBALS['t']['archiv'], (array) $t, true); }
function home_url($p = '') { return 'https://merzenich-aktuell.de' . $p; }
function get_post_thumbnail_id($id) { return 0; } // Termine ohne Foto: Zeile ohne Bildfläche
class WP_Term {} class WP_Query {}
class WP_Post { public $ID = 7; public $post_name = 'msg-hitnight-golzheim-2026'; public $post_type = 'ma_event'; }
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php';
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/termine.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-70s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
$ts = fn(string $s): int => (new DateTimeImmutable($s, wp_timezone()))->getTimestamp();
$termin = fn(string $start, string $ende = '') => ['start' => $ts($start), 'ende' => $ende !== '' ? $ts($ende) : $ts(substr($start, 0, 10) . 'T23:59:59'), 'ohneEnde' => $ende === ''];

echo "Uhrzeit (wie deploy/termine.mjs)\n";
pruefe('Nur Beginn', ma21_termin_zeit($termin('2026-10-10T19:00')), '19:00 Uhr');
pruefe('Beginn und Ende am selben Tag', ma21_termin_zeit($termin('2026-10-06T16:00', '2026-10-06T20:00')), '16:00 Uhr bis 20:00 Uhr');
pruefe('Ganztägig ohne Uhrzeit (kein „00:00 Uhr“)', ma21_termin_zeit($termin('2026-11-01T00:00')), 'ganztägig');
pruefe('Über Mitternacht bis zum Folgetag', ma21_termin_zeit($termin('2026-10-31T19:48', '2026-11-01T00:30')), '19:48 Uhr bis 1.11.');

echo "\nListenzeile\n";
$t = $termin('2026-10-10T19:00') + ['post' => new WP_Post(), 'id' => 7, 'titel' => 'MSG Hitnight', 'url' => '/termine/msg-hitnight-golzheim-2026/', 'ort' => 'Schützenhalle Golzheim',
    'ortSlug' => 'golzheim', 'ortsteil' => 'Golzheim', 'kategorie' => 'Vereine', 'veranstalter' => 'Marianische Schützenbruderschaft', 'quelle' => 'https://example.org/termin', 'beschreibung' => 'Party in der Schützenhalle.'];
$z = ma21_termin_zeile($t);
pruefe('Filterangaben für assets/v20.js (Ort, Kategorie, Beginn)', [str_contains($z, 'data-place="golzheim"'), str_contains($z, 'data-category="Vereine"'), str_contains($z, 'data-start="2026-10-10T17:00:00.000Z"')], [true, true, true]);
pruefe('Wochentag · Kategorie · Ortsteil', str_contains($z, 'Samstag · Vereine · Golzheim'), true);
pruefe('Datum 10 Okt', str_contains($z, '<b>10</b><span>Okt</span>'), true);
pruefe('Knöpfe Details, Kalender (termin.ics), Quelle', [str_contains($z, '>Details<'), str_contains($z, '/termine/msg-hitnight-golzheim-2026/termin.ics" download>Kalender<'), str_contains($z, '>Quelle ↗<')], [true, true, true]);
pruefe('Ohne Quelle kein Quellen-Knopf', str_contains(ma21_termin_zeile(['quelle' => ''] + $t), 'Quelle'), false);

echo "\nNeues Markup statt alter Vorlage\n";
$theme = __DIR__ . '/../../wordpress/theme/merzenich-aktuell/';
pruefe('Alte Vorlagen und Stile entfernt (21.13.0)', array_map(fn($f) => file_exists($theme . $f), ['archive-alt.php', 'template-parts', 'assets/css/service.css', 'assets/js/site.js']), [false, false, false, false]);
pruefe('style.css nur noch Pflichtkopf', [str_contains((string) file_get_contents($theme . 'style.css'), 'Theme Name: Merzenich Aktuell'), filesize($theme . 'style.css') < 600], [true, true]);
pruefe('ma21_legacy() gibt es nicht mehr', function_exists('ma21_legacy'), false);
foreach (['ma_obituary', 'ma_family_notice', 'ma_job', 'ma_property', 'ma_tip', 'ma_business'] as $typ) {
    pruefe("Einzelseite $typ im neuen Markup (ma21_anzeige_einzel)", str_contains((string) file_get_contents($theme . "single-$typ.php"), 'ma21_anzeige_einzel(get_post())'), true);
}
pruefe('Vorlagen vorhanden', array_map(fn($f) => is_file($theme . $f), ['archive-ma_event.php', 'single-ma_event.php', 'archive-ma_obituary.php', 'archive-ma_family_notice.php', 'archive-ma_business.php']), [true, true, true, true, true]);
pruefe('/betriebe/ ohne die acht Kästen „Platz frei“', str_contains((string) file_get_contents($theme . 'archive-ma_business.php'), 'Platz frei'), false);

echo "\nWebseiten-Symbol (Meldung Betreiber 06.10.2026)\n";
$img = __DIR__ . '/../../chatgpt-site/assets/img/';
$ico = (string) file_get_contents($img . 'favicon.ico');
pruefe('favicon.ico ist ein Icon mit 3 Größen', [substr($ico, 0, 4) === "\0\0\1\0", unpack('v', substr($ico, 4, 2))[1]], [true, 3]);
foreach (['icon-180.png' => 180, 'icon-192.png' => 192, 'icon-512.png' => 512, 'favicon-32.png' => 32] as $datei => $n) {
    $g = getimagesize($img . $datei);
    pruefe("{$datei} ist {$n}×{$n}", [$g[0] ?? 0, $g[1] ?? 0], [$n, $n]);
}
pruefe('/favicon.ico und /apple-touch-icon.png direkt, nicht umgeleitet', [MA21_SYMBOLE['/favicon.ico'][0], MA21_SYMBOLE['/apple-touch-icon.png'][0]], ['img/favicon.ico', 'img/icon-180.png']);
$links = ma21_symbol_links();
pruefe('Kopf: Apple-Symbol 180 und Manifest', [str_contains($links, 'rel="apple-touch-icon" href="/apple-touch-icon.png?v=2" sizes="180x180"'), str_contains($links, 'rel="manifest"')], [true, true]);
$m = ma21_manifest();
pruefe('Manifest mit 192er und 512er Symbol', array_map(fn($i) => $i['sizes'], array_slice($m['icons'], 0, 2)), ['192x192', '512x512']);
$alt = (string) file_get_contents($theme . 'vorlagen/kopf-assets.html');
$ohne = (string) preg_replace('#<link rel="(?:icon|apple-touch-icon|manifest)"[^>]*>\n?#', '', $alt);
pruefe('Alte Symbol-Links der Vorlage werden ersetzt', [substr_count($alt, 'rel="icon"') > 0, str_contains($ohne, 'avatar-1024.png')], [true, false]);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
