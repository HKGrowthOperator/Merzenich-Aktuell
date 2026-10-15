<?php
/**
 * Ausbaustufe (inc/ausbau.php, Wunsch Betreiber 15.10.2026) ohne WordPress:
 * Für die Vorstellung zeigt die Seite die einfache Fassung; alles dafür
 * Ausgeblendete bleibt gespeichert und kommt mit dem Schalter genau wie bis
 * zum 14.10.2026 zurück: Unterpunkte und Anzeige in den Aufklappmenüs,
 * Vereine in Leiste, Mehr-Menü und Schublade, die fünf Musterfirmen, Blättern
 * statt Endlos-Scrollen. Geprüft an den echten Daten (deploy/ressort-menue.json
 * über chatgpt-site/assets/ressort-menue.json, inc/unternehmen-muster.json,
 * vorlagen/*.html).
 * Aufruf: php qa/wordpress/ausbau-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('DAY_IN_SECONDS', 86400);
$wurzel = dirname(__DIR__, 2);
function add_action(...$a) {} function add_filter(...$a) {} function remove_action(...$a) {} function add_image_size(...$a) {}
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s, $p = null) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function home_url($p = '') { return 'https://merzenich-aktuell.de' . $p; }
function trailingslashit($s) { return rtrim($s, '/') . '/'; }
function get_template_directory() { return dirname(__DIR__, 2) . '/wordpress/theme/merzenich-aktuell'; }
function sanitize_title($s) { return strtolower(preg_replace('/[^a-z0-9\-]/i', '', (string) $s)); }
function wp_make_link_relative($u) { return (string) preg_replace('#^https?://[^/]+#', '', $u); }
function wp_date($f) { return '2026-10-15T12:00:00+02:00'; }
function get_transient($k) { return false; } function set_transient($k, $v, $t) { return true; }
function get_category_by_slug($s) { return false; }
const MINUTE_IN_SECONDS = 60;
// Sammelseiten (/rubrik/…/) haben Meldungen; „Neu im Ressort“ bleibt leer.
function get_posts($q) { return !empty($q['tag_slug__in']) ? [1] : []; } function get_users($q) { return []; }
function get_query_var($k, $d = '') { return $GLOBALS['qv'][$k] ?? $d; }
function get_header() {} function get_footer() {}
function url_to_postid($u) { return 0; } function get_post_status($id) { return false; }
function current_time($t) { return $t === 'timestamp' || $t === 'U' ? 1791712800 : '2026-10-15 12:00:00'; }
function get_post_meta(...$a) { return ''; } function get_permalink($p) { return '/'; } function get_the_title($p) { return ''; }
function ma_seo_kuerzen($t, $max = 160) { return mb_substr((string) $t, 0, $max); }
class WP_Term { public $count = 0; } class WP_Query {} class WP_Post {}
// Schlagwörter: jedes Thema hat eine Meldung, außer „diebstahl“ (prüft das Ausfiltern leerer Themen).
function get_term_by($feld, $slug, $tax) { $t = new WP_Term(); $t->count = $slug === 'diebstahl' ? 0 : 1; return $t; }
$GLOBALS['optionen'] = [];
function get_option($k, $d = false) { return $GLOBALS['optionen'][$k] ?? $d; }
function schalter(array $an) { $GLOBALS['optionen'] = ['ma21_ausbau' => $an]; }
require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/ma21.php';
require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/markt.php';
require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/unternehmen.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-76s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Schalter (inc/ausbau.php)\n";
pruefe('Vier Schalter, jeder mit Titel und Erklärung', [array_keys(MA21_AUSBAU), count(array_filter(MA21_AUSBAU, fn($s) => $s['titel'] !== '' && strlen($s['text']) > 60))], [['menue_voll', 'vereine', 'endlos', 'muster_alt'], 4]);
pruefe('Standard = einfache Fassung für die Vorstellung, Endlos-Scrollen an', [ma21_ausbau('menue_voll'), ma21_ausbau('vereine'), ma21_ausbau('endlos'), ma21_ausbau('muster_alt')], [false, false, true, false]);
pruefe('Unbekannter Schlüssel ist aus', ma21_ausbau('gibt-es-nicht'), false);
schalter(['menue_voll' => true, 'endlos' => false]);
pruefe('Gespeicherte Option geht vor dem Standard', [ma21_ausbau('menue_voll'), ma21_ausbau('endlos'), ma21_ausbau('vereine')], [true, false, false]);
schalter([]);

echo "\nAusgeblendete Elemente im HTML\n";
$h = '<div class="navscroll"><a href="/termine/">Termine</a><a href="/vereine/" data-ausbau="vereine">Vereine</a></div><ul><li data-ausbau="vereine"><a href="/vereine/">Vereine</a></li><li><a href="/x/">X</a></li></ul>';
pruefe('Aus: Link und Listenpunkt ganz weg, Rest bleibt', ma21_ausbau_html($h), '<div class="navscroll"><a href="/termine/">Termine</a></div><ul><li><a href="/x/">X</a></li></ul>');
schalter(['vereine' => true]);
pruefe('An: sichtbar, nur das Merkmal fällt weg', ma21_ausbau_html($h), '<div class="navscroll"><a href="/termine/">Termine</a><a href="/vereine/">Vereine</a></div><ul><li><a href="/vereine/">Vereine</a></li><li><a href="/x/">X</a></li></ul>');
schalter([]);
pruefe('HTML ohne Merkmal bleibt unverändert', ma21_ausbau_html('<p>x</p>'), '<p>x</p>');
foreach (['kopf.html', 'kopf-seite.html'] as $v) {
    $html = (string) file_get_contents(get_template_directory() . '/vorlagen/' . $v);
    pruefe("$v: alle Merkmale gehören zu bekannten Schaltern", array_values(array_diff(array_unique(preg_match_all('#data-ausbau="([^"]+)"#', $html, $m) ? $m[1] : []), array_keys(MA21_AUSBAU))), []);
}

echo "\nMenüdaten (statische Datei wie in WordPress)\n";
$quelle = json_decode((string) file_get_contents($wurzel . '/chatgpt-site/assets/ressort-menue.json'), true);
pruefe('Statische Datei trägt die gespeicherte volle Fassung und Vereine', [isset($quelle['ressorts']['/vereine/']), $quelle['ressorts']['/vereine/']['ausbau'] ?? '', count(array_filter($quelle['ressorts'], fn($r) => !empty($r['voll']['gruppen'])))], [true, 'vereine', 11]);
// Prüfsummen des Stands vom 14.10.2026 (Commit 7c56c388), damit die CI auch ohne
// Git-Verlauf (flacher Checkout) merkt, wenn gespeicherte Daten verloren gehen.
const STAND_1410_GRUPPEN = '905fd30f6aaf91f8c15eda84d9f1d90bab77c9ac22c556e0f0d18fbe0bb073ab';
const STAND_1410_FIRMEN = '8628fdd01327dc010f0d0c6e5d607133470f9d40c32a50b716340fc073d12466';
$summe = fn($x) => hash('sha256', json_encode($x, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
$gespeichert = [];
foreach (json_decode((string) file_get_contents($wurzel . '/deploy/ressort-menue.json'), true)['ressorts'] as $r) $gespeichert[$r['href']] = $r['voll']['gruppen'] ?? null;
pruefe('Gespeicherte Unterpunkte = Stand 14.10.2026 (Prüfsumme, auch ohne Git-Verlauf)', $summe($gespeichert), STAND_1410_GRUPPEN);
pruefe('Statische Menüdatei trägt dieselben Unterpunkte', $summe(array_map(fn($r) => $r['voll']['gruppen'] ?? null, $quelle['ressorts'])), STAND_1410_GRUPPEN);
$alt = json_decode((string) shell_exec('git -C ' . escapeshellarg($wurzel) . ' show 7c56c388:deploy/ressort-menue.json 2>/dev/null'), true);
if (is_array($alt)) {
    $vorher = array_column(array_map(fn($r) => ['href' => $r['href'], 'gruppen' => $r['gruppen']], $alt['ressorts']), 'gruppen', 'href');
    $jetzt = array_map(fn($r) => $r['voll']['gruppen'] ?? [], $quelle['ressorts']);
    pruefe('Gespeicherte Unterpunkte = Stand 14.10.2026 (Commit 7c56c388), nichts verloren', $jetzt == $vorher && array_keys($jetzt) == array_keys($vorher), true);
} else echo "  Vergleich mit Commit 7c56c388 übersprungen (Git-Verlauf fehlt, z. B. flacher Checkout)\n";
$einfach = ma21_ressort_menue_daten($quelle);
$json = json_encode($einfach, JSON_UNESCAPED_UNICODE);
pruefe('Einfach: kein Vereine-Ressort, keine Gruppen, keine Anzeige, nichts Gespeichertes', [isset($einfach['ressorts']['/vereine/']), str_contains($json, '"gruppen"'), str_contains($json, '"werbung"'), str_contains($json, '"voll"'), str_contains($json, '"ausbau"')], [false, false, false, false, false]);
pruefe('Einfach: Blaulicht nur Feuerwehr, Polizei, Verkehr', array_column($einfach['ressorts']['/blaulicht/']['punkte'], 'titel'), ['Feuerwehr', 'Polizei', 'Verkehr']);

schalter(['menue_voll' => true]);
$voll = ma21_ressort_menue_daten($quelle);
$bl = $voll['ressorts']['/blaulicht/'];
pruefe('Unterpunkte an: Blaulicht wieder mit vier Gruppen wie bis 14.10.', array_column($bl['gruppen'] ?? [], 'titel'), ['Feuerwehr', 'Rettung & Hilfe', 'Polizei', 'Verkehr']);
pruefe('Unterpunkte an: Themen ohne Meldung fallen weg (Diebstahl im Test)', in_array('Diebstahl', array_column(array_merge(...array_column($bl['gruppen'], 'links')), 0), true), false);
pruefe('Unterpunkte an: Sport mit „SC 1919: Tabelle & Spielplan“', in_array('SC 1919: Tabelle & Spielplan', array_column(array_merge(...array_column($voll['ressorts']['/sport/']['gruppen'], 'links')), 0), true), true);
pruefe('Unterpunkte an: Ortsteile zeigen auf /ort/…/', in_array(['Golzheim', '/ort/golzheim/'], array_merge(...array_column($voll['ressorts']['/nachrichten/']['gruppen'], 'links')), true), true);
pruefe('Unterpunkte an: alte Beschriftung „Alle Unternehmensmeldungen“ (einfach: „Alle Unternehmen“)', [$voll['ressorts']['/unternehmen/']['alle'], $einfach['ressorts']['/unternehmen/']['alle']], ['Alle Unternehmensmeldungen', 'Alle Unternehmen']);
pruefe('Unterpunkte an, Vereine aus: kein Vereine-Ressort, keine Anzeige', [isset($voll['ressorts']['/vereine/']), str_contains(json_encode($voll), '"werbung"')], [false, false]);
schalter(['vereine' => true, 'menue_voll' => true]);
$alles = ma21_ressort_menue_daten($quelle);
$soll = 0; foreach ($gespeichert as $gr) foreach ((array) $gr as $g) foreach ($g['links'] as $l) $soll += $l[1] === '/thema/diebstahl/' ? 0 : 1;
$ist = 0; foreach ($alles['ressorts'] as $r) foreach ($r['gruppen'] ?? [] as $g) $ist += count($g['links']);
pruefe("Alles an: jeder gespeicherte Link kommt zurück ($soll von 114, ohne das leere Thema im Test)", [$ist, $soll, count(array_filter($alles['ressorts'], fn($r) => !empty($r['gruppen'])))], [$soll, 113, 11]);
schalter(['vereine' => true]);
$v = ma21_ressort_menue_daten($quelle)['ressorts']['/vereine/'] ?? null;
pruefe('Vereine an: eigenes Menü mit roten Titeln, ohne Unterpunkte und Anzeige', [$v ? array_column($v['punkte'], 'titel') : null, isset($v['gruppen']), isset($v['werbung']), isset($v['ausbau'])], [['Vereinsleben', 'Ehrenamt', 'Verein eintragen'], false, false, false]);
schalter(['vereine' => true, 'menue_voll' => true]);
$v = ma21_ressort_menue_daten($quelle)['ressorts']['/vereine/'] ?? null;
pruefe('Beide an: Vereine wie bis 14.10. mit Gruppen und Anzeige', [array_column($v['gruppen'] ?? [], 'titel'), $v['werbung'] ?? null], [['Vereinsleben', 'Sport & Fans', 'Mitmachen'], true]);
pruefe('Unternehmen bekommt nie eine Anzeige (Auftrag 13.10.2026)', isset(ma21_ressort_menue_daten($quelle)['ressorts']['/unternehmen/']['werbung']), false);
schalter([]);

echo "\nMusterfirmen\n";
pruefe('Standard: drei Beispielfirmen', array_column(ma21_muster_firmen(), 'slug'), ['backhaus-morgenrot', 'cafe-feldlerche', 'holzwerkstatt-jahresring']);
schalter(['muster_alt' => true]);
$fruehere = ma21_muster_firmen();
pruefe('Fünf Musterfirmen = Stand 14.10.2026 (Prüfsumme, auch ohne Git-Verlauf)', $summe($fruehere), STAND_1410_FIRMEN);
pruefe('Schalter an: die fünf Musterfirmen bis 14.10.2026', array_column($fruehere, 'slug'), ['muster-backstube', 'muster-cafe-am-markt', 'muster-schreinerei', 'muster-steuerbuero', 'muster-immobilien']);
$altFirmen = json_decode((string) shell_exec('git -C ' . escapeshellarg($wurzel) . ' show 7c56c388:wordpress/theme/merzenich-aktuell/inc/unternehmen-muster.json 2>/dev/null'), true);
if (is_array($altFirmen)) pruefe('Gespeichert unverändert (Commit 7c56c388), samt Beiträgen und Bildern', $fruehere == $altFirmen['firmen'], true);
else echo "  Vergleich mit Commit 7c56c388 übersprungen (Git-Verlauf fehlt)\n";
pruefe('Alle Bilder der früheren Firmen sind vorhanden', count(array_filter(array_merge(...array_map(fn($f) => array_merge([$f['bild'], $f['anzeige']['bild'] ?? $f['bild']], array_column($f['beitraege'], 'bild')), $fruehere)), fn($id) => ma21_muster_bild((string) $id) === null)), 0);
schalter([]);
pruefe('Wieder aus: drei Beispielfirmen', count(ma21_muster_firmen()), 3);

echo "\nEndlos-Scrollen\n";
global $wp_query; $wp_query = new WP_Query(); $wp_query->max_num_pages = 3;
function get_pagenum_link($n = 1, $e = true) { return home_url('/blaulicht/page/' . $n . '/'); }
function remove_query_arg($k, $u) { return $u; }
$start = '<main><!-- fotodestages:end --><!-- endlos:start --><section class="weitere-meldungen">…</section><!-- endlos:end --><section class="reader-note"></section></main>';
pruefe('An (Standard): Liste mit Folgeseite, Startseite mit „Weitere Meldungen“', [ma21_endlos_attr('ressort-blaulicht') !== '', ma21_startseite_ausbau($start) === $start], [true, true]);
schalter(['endlos' => false]);
pruefe('Aus: Blättern wie bis 14.10. (kein Nachladen, kein Block auf der Startseite)', [ma21_endlos_attr('ressort-blaulicht'), ma21_startseite_ausbau($start)], ['', '<main><!-- fotodestages:end --><section class="reader-note"></section></main>']);
schalter([]);
$startseite = (string) file_get_contents(get_template_directory() . '/vorlagen/startseite.html');
pruefe('Startseiten-Vorlage hat genau einen Block „Weitere Meldungen“', [substr_count($startseite, '<!-- endlos:start -->'), substr_count($startseite, '<!-- endlos:end -->')], [1, 1]);

echo "\nVerdrahtung der Schalter\n";
$quelltext = fn($d) => (string) file_get_contents($wurzel . '/' . $d);
pruefe('Kopf: erst Ausbaustufe, dann Markierung (ma21_kopf → ma21_kopf_menue)', [str_contains($quelltext('wordpress/theme/merzenich-aktuell/inc/ma21.php'), '$html = ma21_kopf_menue($html);'), str_contains($quelltext('wordpress/theme/merzenich-aktuell/inc/ma21.php'), 'return ma21_kopf_aktuell(ma21_ausbau_html($html));')], [true, true]);
pruefe('Startseite: Block „Weitere Meldungen“ hängt am Schalter', str_contains($quelltext('wordpress/theme/merzenich-aktuell/inc/ma21.php'), '$html = ma21_startseite_ausbau($html);'), true);
pruefe('Endlos-Scrollen aus: <html data-endlos="aus"> und endlos.js hört darauf', [str_contains($quelltext('wordpress/theme/merzenich-aktuell/header.php'), "ma21_ausbau('endlos') ? '' : ' data-endlos=\"aus\"'"), str_contains($quelltext('chatgpt-site/assets/endlos.js'), "d.documentElement.dataset.endlos === 'aus'")], [true, true]);
pruefe('Speichern unter „Design“ mit edit_theme_options (options.php)', str_contains($quelltext('wordpress/theme/merzenich-aktuell/inc/ausbau.php'), "add_filter('option_page_capability_ma21_ausbau', fn() => 'edit_theme_options');"), true);
pruefe('functions.php lädt inc/ma21.php (und damit inc/ausbau.php)', [str_contains($quelltext('wordpress/theme/merzenich-aktuell/functions.php'), "require_once __DIR__ . '/inc/ma21.php';"), str_contains($quelltext('wordpress/theme/merzenich-aktuell/inc/ma21.php'), "require_once __DIR__ . '/ausbau.php';")], [true, true]);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
