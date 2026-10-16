<?php
/**
 * Sportseite ohne Ergebnisse und Tabellen (Theme 21.20.0): unter den
 * Sportmeldungen alle Sportvereine aus dem Vereinsverzeichnis des Plugins
 * (ma21_sport_vereine in inc/sport.php), ohne WordPress.
 * Aufruf: php qa/wordpress/sport-vereine-test.php
 */
define('ABSPATH', __DIR__);
define('MA_CORE_PATH', __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/');
class WP_Post { public $ID = 7; public $post_status = 'publish'; public $post_type = 'ma_club'; }
function add_action(...$a) {} function add_filter(...$a) {} function register_post_status(...$a) {} function _n_noop($a, $b) { return [$a, $b]; }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return (string) $s; }
function remove_accents($s) { return $s; }
function home_url($p = '') { return 'https://merzenich-aktuell.de' . $p; }
function get_permalink($p) { return 'https://merzenich-aktuell.de/vereine/sc-1919-merzenich/'; }
function has_post_thumbnail($p) { return $GLOBALS['mit_bild'] ?? false; }
function get_post_thumbnail_id($p) { return 99; }
function wp_get_attachment_image_url($id, $g) { return 'https://merzenich-aktuell.de/wp-content/uploads/sc-logo-150x150.webp'; }
function get_posts($q) { return ($q['meta_value'] ?? '') === 'sc-1919-merzenich' ? [new WP_Post()] : []; }
function ma_current_partner_policy() { return null; }
const MA21_ORTE = ['merzenich' => 'Merzenich', 'golzheim' => 'Golzheim', 'girbelsrath' => 'Girbelsrath', 'morschenich' => 'Morschenich', 'buergewald' => 'Bürgewald'];
require MA_CORE_PATH . 'includes/redaktion.php';
require MA_CORE_PATH . 'includes/vereinszugaenge.php';
require MA_CORE_PATH . 'includes/vereine.php';
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/sport.php';
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ausbau.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

$sport = array_filter(ma_vereinsverzeichnis(), fn($v) => ($v['kategorie'] ?? '') === 'Sport');
$haupt = array_filter($sport, fn($v) => !ma_verein_ist_abteilung($v));
$h = ma21_sport_vereine();

echo "Vereinsliste\n";
pruefe('Jeder Sport-Hauptverein steht genau einmal in der Liste', [substr_count($h, '<li class="sv-verein">'), count($haupt)], [count($haupt), count($haupt)]);
$fehlt = array_values(array_filter($haupt, fn($v) => !str_contains($h, '<h4>' . esc_html($v['name']) . '</h4>')));
pruefe('Kein Hauptverein fehlt', array_column($fehlt, 'name'), []);
pruefe('Abteilungen stehen beim Hauptverein, nicht als eigener Eintrag', str_contains($h, '<h4>SC 1919 Merzenich e.V. – Tennisabteilung</h4>'), false);
preg_match('#<h4>SC 1919 Merzenich e\.V\.</h4>(.*?)</li></ul>#s', $h, $sc);
pruefe('SC 1919: Fußball, Tennis und Badminton (mit Website der Abteilung)', [str_contains($sc[1] ?? '', '<li>Fußball</li>'), str_contains($sc[1] ?? '', '<li>Tennis</li>'), str_contains($sc[1] ?? '', 'href="http://scm-badminton.de/"')], [true, true, true]);
pruefe('Gruppen in fester Reihenfolge', array_map(fn($m) => $m, (preg_match_all('#<div class="sv-gruppe" id="sv-([a-z]+)">#', $h, $m) ? $m[1] : [])), ['fussball', 'turnen', 'tisch', 'schiessen', 'weitere']);
pruefe('Sprungleiste mit Anzahl je Sportart', str_contains($h, '<a href="#sv-fussball">Fußball<span>4</span></a>'), true);
pruefe('Ortsteil als Etikett, ohne Ortsteil „Gemeinde Merzenich“', [str_contains($h, '<span class="sv-ort">Golzheim</span>'), str_contains($h, '<span class="sv-ort">Gemeinde Merzenich</span>')], [true, true]);
pruefe('Vereinsprofil verlinkt, wenn veröffentlicht', str_contains($h, '<a href="https://merzenich-aktuell.de/vereine/sc-1919-merzenich/">Vereinsprofil</a>'), true);
pruefe('Ohne Profilbild ein Kürzel als Zeichen', str_contains($h, '<span class="sv-zeichen" aria-hidden="true">SC</span>'), true);
$GLOBALS['mit_bild'] = true;
pruefe('Mit Profilbild das Bild', str_contains(ma21_sport_vereine(), '<img class="sv-zeichen" src="https://merzenich-aktuell.de/wp-content/uploads/sc-logo-150x150.webp"'), true);
$GLOBALS['mit_bild'] = false;

echo "\nKeine Pflegefelder, keine Recherchenotizen\n";
pruefe('Kein Platz, keine Punkte, keine Tabelle', [preg_match('/Platz|Punkte|Tabelle|sc-wert|league-/u', $h)], [0]);
pruefe('Hinweistexte aus dem Verzeichnis erscheinen nicht (TLS-Fehler, Ansprechpartner)', [str_contains($h, 'TLS'), str_contains($h, 'Ansprechpartner'), str_contains($h, 'Namensvarianten')], [false, false, false]);

echo "\nHilfen\n";
pruefe('Sportarten aus Klammern und Schrägstrich', ma21_sport_sparten('Turnen/Breitensport (u. a. Kinderturnen, Badminton, Tischtennis)'), ['Turnen', 'Breitensport', 'Kinderturnen', 'Badminton', 'Tischtennis']);
pruefe('Kürzel', array_map('ma21_sport_kuerzel', ['FC Golzheim 1928 e.V.', 'Tischtennisclub Merzenich 1972 e.V.', 'Billardsportclub Merzenich 1970 e.V. (BSC Merzenich)', 'UL-Aero-Club Morschenich e.V.', 'American Football Club Düren Demons e.V.']), ['FC', 'TTC', 'BSC', 'UL', 'AFC']);
pruefe('Gruppe: American Football ist kein Fußball', [ma21_sport_gruppe('American Football, Cheerleading'), ma21_sport_gruppe('Fußball (Alte Herren)')], ['weitere', 'fussball']);

echo "\nSportseite und Schalter\n";
$GLOBALS['optionen'] = [];
function get_option($k, $d = false) { return $GLOBALS['optionen'][$k] ?? $d; }
pruefe('Schalter „sport_daten“ ist im Standard aus', ma21_ausbau('sport_daten'), false);
$archiv = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/archive.php');
pruefe('archive.php: Ecke, Modul und Tabellenstand nur mit Schalter', [str_contains($archiv, "\$sportTabellen = \$sport && ma21_ausbau('sport_daten');"), str_contains($archiv, 'if ($sportTabellen && !is_paged() && function_exists(\'ma_sport_vereinsraster\'))'), str_contains($archiv, '$ecke = $sportTabellen ?')], [true, true, true]);
pruefe('archive.php: Vereine unter den Meldungen, Sport ohne Endlos-Scrollen', [str_contains($archiv, "if (\$sport && !\$sportTabellen && !is_paged()) echo ma21_sport_vereine();"), str_contains($archiv, 'if ((!$sport || $sportTabellen) && !$leereSuche) echo ma21_endlos_attr(')], [true, true]);
$ma21 = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php');
pruefe('Rechte Spalte wiederholt die Vereine auf Seite 1 nicht', str_contains($ma21, "if (\$n && !\$vereineUnten) \$h .= '<div class=\"sidebox sport-leiste\">"), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
