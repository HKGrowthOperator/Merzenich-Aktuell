<?php
/**
 * Kopf des Themes (inc/ma21.php, 21.10.5) ohne WordPress: keine Ausgabe-Wahl,
 * Zeile „Merzenich · Jetzt“ und Markierung der besuchten Seite, geprüft an der
 * echten Vorlage vorlagen/kopf.html (deploy/wp-theme.mjs). Bricht die Vorlage
 * das Markup, greifen die Ersetzungen nicht mehr: dann schlägt dieser Test an.
 * Aufruf: php qa/wordpress/kopf-test.php
 */
define('ABSPATH', __DIR__ . '/');
$GLOBALS['t'] = ['tax' => false, 'obj' => null, 'single' => '', 'archiv' => ''];
function add_action(...$a) {} function add_filter(...$a) {} function remove_action(...$a) {} function add_image_size(...$a) {}
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return (string) $s; }
function is_tax($t) { return $GLOBALS['t']['tax']; }
function get_queried_object() { return $GLOBALS['t']['obj']; }
function is_singular($t) { return $GLOBALS['t']['single'] === $t; }
function is_post_type_archive($t) { return $GLOBALS['t']['archiv'] === $t; }
function wp_date($f, $ts = null) { return gmdate($f, $ts ?? 1791021600); } // 03.10.2026 10:00 UTC
function get_the_category($id) { return [(object) ['slug' => 'sport', 'name' => 'Sport']]; }
class WP_Term { public $slug; public $name; function __construct($s, $n) { $this->slug = $s; $this->name = $n; } }
class WP_Post { public $ID = 7; public $post_type = 'post'; }
class WP_Query {}
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-70s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
$vorlage = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/vorlagen/kopf.html');
pruefe('Vorlage hat Ortswahl, Jetzt-Zeile und Markierung', [str_contains($vorlage, 'class="ortswahl-zahl"'), str_contains($vorlage, 'class="jetzt-feld jetzt-neu"'), substr_count($vorlage, 'aria-current="page"') >= 1], [true, true, true]);

echo "Keine Ausgabe-Wahl oben (Vorgabe Betreiber 05.10.2026)\n";
$h = ma21_kopf_ortswahl($vorlage);
pruefe('Startseite: Wahl „Ausgabe“ weg, Jetzt-Zeile bleibt', [str_contains($h, 'ortswahl-schalter'), str_contains($h, '>Ausgabe<'), str_contains($h, 'Ausgabe waehlen'), str_contains($h, 'class="jetzt"')], [false, false, false, true]);
pruefe('Keine Zahl: kein ortswahl-zahl, kein „Meldungen“ im Kopf', [str_contains($h, 'ortswahl-zahl'), (bool) preg_match('#\d+ Meldungen#', $h)], [false, false]);
pruefe('Ortsteile bleiben (Mehr-Menü, Schublade) und zeigen auf /ort/…/', [preg_match('#href="/(merzenich|golzheim|girbelsrath|morschenich|buergewald)/"#', $h), substr_count($h, 'href="/ort/golzheim/"') >= 2, substr_count($h, 'href="/ort/buergewald/"') >= 2], [0, true, true]);
$seite = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/vorlagen/kopf-seite.html');
$h = ma21_kopf_ortswahl($seite);
pruefe('Unterseite: leere Leiste ganz weg, Ortsteile bleiben', [str_contains($h, 'class="ortswahl"'), str_contains($h, 'Ausgabe'), substr_count($h, 'href="/ort/morschenich/"') >= 2], [false, false, true]);
$GLOBALS['t'] = ['tax' => true, 'obj' => new WP_Term('golzheim', 'Golzheim'), 'single' => '', 'archiv' => ''];
$h = ma21_kopf_ortswahl($vorlage);
pruefe('Auf /ort/golzheim/: nur Golzheim markiert', [preg_match_all('#<a href="/ort/(?!golzheim)[a-z]+/" aria-current="page">#', $h), substr_count($h, '<a href="/ort/golzheim/" aria-current="page">') >= 2], [0, true]);
$GLOBALS['t'] = ['tax' => false, 'obj' => null, 'single' => '', 'archiv' => ''];

echo "\nMerzenich · Jetzt\n";
$d = ['neu' => ['iso' => '2026-10-03T14:02:00+02:00', 'zeit' => '03.10. · 14:02 Uhr', 'url' => '/leben/kirmes/', 'titel' => 'Kirmes & Markt'], 'daten' => ['2026-10-03T14:02:00+02:00'], 'termin' => ['url' => '/termine/kirmes/', 'titel' => 'Kirmes', 'wann' => '05.10., 14:00 Uhr']];
$h = ma21_kopf_jetzt($vorlage, $d);
pruefe('Neu: jüngste veröffentlichte Meldung mit Uhrzeit, Titel maskiert', str_contains($h, '<span class="jetzt-feld jetzt-neu">Neu <time datetime="2026-10-03T14:02:00+02:00">03.10. · 14:02 Uhr</time> <a href="/leben/kirmes/">Kirmes &amp; Markt</a></span>'), true);
pruefe('Entwurf der Vorlage (Stollenwerk) nicht mehr verlinkt', str_contains($h, 'stollenwerk'), false);
pruefe('Kein „Heute n neue Meldungen“, kein Feld für kopf.js zum Zählen', [str_contains($h, 'jetzt-heute'), str_contains($h, 'neue Meldung')], [false, false]);
pruefe('Nächster Termin mit Datum', str_contains($h, '<span class="jetzt-feld jetzt-termin">Nächster Termin <a href="/termine/kirmes/">Kirmes</a> · 05.10., 14:00 Uhr</span>'), true);
$h = ma21_kopf_jetzt($vorlage, ['neu' => null, 'daten' => [], 'termin' => null]);
pruefe('Ohne Meldung und Termin: Felder versteckt, kein Rest der Vorlage', [str_contains($h, '<span class="jetzt-feld jetzt-neu" hidden></span>'), str_contains($h, '<span class="jetzt-feld jetzt-termin" hidden></span>'), str_contains($h, 'Stollenwerk')], [true, true, false]);
pruefe('Vorlage ohne Jetzt-Zeile bleibt unverändert', ma21_kopf_jetzt('<nav>x</nav>', $d), '<nav>x</nav>');

echo "\nBesuchte Seite\n";
$_SERVER['REQUEST_URI'] = '/blaulicht/?x=1';
$h = ma21_kopf_aktuell($vorlage);
pruefe('Nur /blaulicht/ markiert (Leiste, Mehr-Menü, Schublade), „Aktuell“ nicht mehr', [substr_count($h, '<a href="/blaulicht/" aria-current="page">'), str_contains($h, '<a href="/nachrichten/" aria-current="page">')], [3, false]);
$_SERVER['REQUEST_URI'] = '/';
pruefe('Startseite: keine Markierung', substr_count(ma21_kopf_aktuell($vorlage), 'aria-current'), 0);
$_SERVER['REQUEST_URI'] = '/sport/sc-merzenich-siegt/';
$GLOBALS['t'] = ['tax' => false, 'obj' => new WP_Post(), 'single' => 'post', 'archiv' => ''];
pruefe('Meldung: ihr Ressort ist markiert', substr_count(ma21_kopf_aktuell($vorlage), '<a href="/sport/" aria-current="page">'), 3);
$GLOBALS['t'] = ['tax' => false, 'obj' => null, 'single' => 'ma_event', 'archiv' => ''];
$_SERVER['REQUEST_URI'] = '/termine/kirmes/';
pruefe('Termin: „Termine“ markiert', substr_count(ma21_kopf_aktuell($vorlage), '<a href="/termine/" aria-current="page">'), 3);
$_SERVER['REQUEST_URI'] = '/ort/golzheim/';
$GLOBALS['t'] = ['tax' => true, 'obj' => null, 'single' => '', 'archiv' => ''];
pruefe('Ortsseite: Ressortleiste ohne Markierung', substr_count(ma21_kopf_aktuell($vorlage), 'aria-current'), 0);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
