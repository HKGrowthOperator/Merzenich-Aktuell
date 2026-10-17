<?php
/**
 * Höchstens zweimal dasselbe Foto je Seite (inc/ma21.php ma21_bild_seite,
 * Plugin includes/fotoschluessel.php, assets/endlos.js; 17.10.2026, Wunsch
 * Betreiber „oft das gleiche Bild 5 mal in einer Seite“): ein Foto-Schlüssel
 * über Anhänge und Breiten hinweg, gleich in Plugin, Theme und Skript; Karten
 * ab dem dritten Mal ohne Bild; Aufmacher und Meldungsseite behalten ihr Foto.
 * Aufruf: php qa/wordpress/bildwiederholung-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('DAY_IN_SECONDS', 86400);
const MINUTE_IN_SECONDS = 60, HOUR_IN_SECONDS = 3600;
$GLOBALS['hooks'] = [];
function add_action($n, $cb = null, ...$r) { $GLOBALS['hooks'][$n][] = $cb; } function add_filter($n, $cb = null, ...$r) { $GLOBALS['hooks'][$n][] = $cb; }
function remove_action(...$a) {} function add_image_size(...$a) {}
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return (string) $s; }
function home_url($p = '') { return 'https://merzenich-aktuell.de' . $p; }
class WP_Term { public $slug = ''; public $name = ''; public $term_id = 0; public $count = 0; }
class WP_Query { public $max_num_pages = 0; public $posts = []; }
class WP_Post { public $ID; public $post_type = 'post'; public $post_title = ''; public $post_excerpt = ''; public $post_content = ''; public $post_date = '2026-10-15 09:00:00'; public $post_name = ''; function __construct($id) { $this->ID = $id; $this->post_title = "Meldung $id"; $this->post_excerpt = "Anriss $id"; $this->post_name = "meldung-$id"; } }
// Abfrage-Zustand der Stub-Seite
$GLOBALS['qv'] = [];
$GLOBALS['ist'] = ['category' => false, 'tag' => false, 'tax' => false, 'search' => false, 'kat' => '', 'taxonomie' => 'ma_location', 'archiv' => '', '404' => false];
function get_query_var($k, $d = '') { return $GLOBALS['qv'][$k] ?? $d; }
function is_category($s = null) { return $GLOBALS['ist']['category'] && ($s === null || $s === $GLOBALS['ist']['kat']); }
function is_tag($s = null) { return $GLOBALS['ist']['tag']; }
function is_tax($t = null) { return $GLOBALS['ist']['tax'] && ($t === null || $t === $GLOBALS['ist']['taxonomie']); }
function is_post_type_archive($t = null) { return $GLOBALS['ist']['archiv'] !== '' && ($t === null || $t === $GLOBALS['ist']['archiv']); }
function is_404() { return $GLOBALS['ist']['404']; }
$GLOBALS['optionen'] = [];
function get_option($k, $d = false) { return $GLOBALS['optionen'][$k] ?? $d; }
// Abruf von GitHub (ma21_repo_datei): gezählt, nie echt
$GLOBALS['abrufe'] = 0; $GLOBALS['geplant'] = [];
function update_option($k, $v, $a = null) { $GLOBALS['optionen'][$k] = $v; return true; }
function is_wp_error($x) { return false; }
function wp_remote_get($u, $a = []) { $GLOBALS['abrufe']++; return ['body' => 'neu']; }
function wp_remote_retrieve_response_code($r) { return 200; } function wp_remote_retrieve_body($r) { return $r['body']; }
function wp_next_scheduled($h, $a = []) { return in_array([$h, $a], $GLOBALS['geplant'], true) ? time() : false; }
function wp_schedule_single_event($t, $h, $a = []) { $GLOBALS['geplant'][] = [$h, $a]; return true; }
function is_search() { return $GLOBALS['ist']['search']; }
function is_paged() { return (int) ($GLOBALS['qv']['paged'] ?? 0) > 1; }
// Blättern wie WordPress: get_pagenum_link behält die Abfrage der aktuellen Adresse (also auch ma_endlos).
$GLOBALS['anfrage'] = '/blaulicht/';
function get_pagenum_link($n = 1, $escape = true) { $q = parse_url($GLOBALS['anfrage'], PHP_URL_QUERY); $p = rtrim((string) parse_url($GLOBALS['anfrage'], PHP_URL_PATH), '/') . '/'; $p = preg_replace('#page/\d+/$#', '', $p); return home_url($p . ($n > 1 ? "page/$n/" : '') . ($q ? "?$q" : '')); }
function remove_query_arg($k, $url) { $t = parse_url($url); parse_str($t['query'] ?? '', $q); unset($q[$k]); return $t['scheme'] . '://' . $t['host'] . $t['path'] . ($q ? '?' . http_build_query($q) : ''); }
// Rendering der Karten und Zeilen
function get_permalink($p) { return '/blaulicht/meldung-' . $p->ID . '/'; }
function get_the_title($p) { return is_object($p) ? $p->post_title : "Foto $p"; }
function get_post_meta(...$a) { if (isset($GLOBALS['ameta'][$a[0]][$a[1]])) return $GLOBALS['ameta'][$a[0]][$a[1]]; return $a[1] === 'ma_startplatz' && in_array($a[0], $GLOBALS['nur_rubrik'] ?? [], true) ? 'aus' : ''; }
function get_the_terms(...$a) { return []; }
function get_the_category($id) { $c = new WP_Term(); $c->slug = 'blaulicht'; $c->name = 'Blaulicht'; return [$c]; }
function get_post_thumbnail_id($id) { return (int) ($GLOBALS['bild'][$id] ?? 0); }
function has_post_thumbnail($id) { return get_post_thumbnail_id($id) > 0; }
function get_the_post_thumbnail_url(...$a) { return false; }
function wp_get_attachment_image_src($id, $g = 'full') { return isset($GLOBALS['datei'][$id]) ? ['https://merzenich-aktuell.de/wp-content/uploads/' . $GLOBALS['datei'][$id], 1200, 800] : false; }
function wp_get_attachment_image_srcset(...$a) { return ''; }
function ma_credit_kurz($c, $l) { return ''; }
function wp_html_excerpt($s, $n, $mehr = '') { return mb_strlen($s) > $n ? mb_substr($s, 0, $n) . $mehr : $s; }
function wp_strip_all_tags($s) { return strip_tags((string) $s); }
function get_post_time($f = 'U', $gmt = false, $p = null) { return $f === 'U' ? 1791702000 : gmdate($f, 1791702000); }
function get_the_date($f = '', $p = null) { return gmdate($f ?: 'd.m.Y', 1791702000); }
function get_post_modified_time($f = 'U', $gmt = false, $p = null) { return get_post_time($f, $gmt, $p); }
function wp_date($f, $ts = null) { return gmdate($f, $ts ?? 1791702000); }
function get_post_field($f, $p) { return $p->$f ?? ''; }
function get_transient($k) { return false; } function set_transient(...$a) { return true; }
function apply_filters($n, $v) { return $v; }
function get_category_by_slug($s) { return false; }
// Sport: Kategorie oder (Plugin) Sportbezug im Text, für die sportfreie Startseite
$GLOBALS['sport'] = []; $GLOBALS['sportbezug'] = [];
function has_category($slug, $p) { return $slug === 'sport' && in_array($p->ID, $GLOBALS['sport'], true); }
function ma_is_sport_post($p) { return in_array($p->ID, $GLOBALS['sportbezug'], true); }
function get_template_directory() { return __DIR__ . '/../../wordpress/theme/merzenich-aktuell'; }
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/fotoschluessel.php';
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-72s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
// Anhänge wie live (Dateinamen vom 17.10.): dasselbe Foto als Abgleich-Anhang (ma_image_static_src) und als Pool-Anhang.
$GLOBALS['datei'] = [501 => '2026/10/kirchedetail-01-995c6c09d8-1200.webp', 502 => '2026/10/kirchedetail-01-995c6c09d8.jpg', 503 => '2026/10/kirchedetail-02-3cc89ae5d6-1200.webp',
    504 => '2026/09/buergewald-1440.webp', 505 => '2026/10/buergewald-720.webp', 506 => '2026/10/merzenich-st-laurentius-1440.webp', 507 => '2026/09/plakat.jpg', 508 => '2026/10/plakat.jpg'];
$GLOBALS['ameta'] = [501 => ['ma_image_static_src' => '/assets/editorial-pools/kirchedetail/kirchedetail-01-995c6c09d8-1200.webp'], 502 => ['_wp_attached_file' => '2026/10/kirchedetail-01-995c6c09d8.jpg'],
    504 => ['ma_image_static_src' => '/assets/places/buergewald-1440.webp'], 505 => ['ma_image_static_src' => '/assets/places/buergewald-720.webp'], 506 => ['ma_image_static_src' => '/assets/places/merzenich-st-laurentius-1440.webp'],
    507 => ['_wp_attached_file' => '2026/09/plakat.jpg'], 508 => ['_wp_attached_file' => '2026/10/plakat.jpg']];
$GLOBALS['bild'] = [];
$meldungen = function (array $bilder) { $l = []; foreach ($bilder as $i => $b) { $id = 100 + count($GLOBALS['bild']); $GLOBALS['bild'][$id] = $b; $l[] = new WP_Post($id); } return $l; };
$neueSeite = function () { $r = &ma21_bild_register(); $r = []; };

echo "Foto-Schlüssel (Plugin, Anhang)\n";
pruefe('Abgleich- und Pool-Anhang desselben Fotos: ein Schlüssel', [ma_foto_schluessel(501), ma_foto_schluessel(502)], ['h:kirchedetail-01-995c6c09d8', 'h:kirchedetail-01-995c6c09d8']);
pruefe('Ortsansicht in 1440 und 720 (verschiedene Anhänge): ein Schlüssel', [ma_foto_schluessel(504), ma_foto_schluessel(505)], ['p:places/buergewald', 'p:places/buergewald']);
pruefe('Andere Ansicht desselben Orts bleibt eigenes Foto', ma_foto_schluessel(506), 'p:places/merzenich-st-laurentius');
pruefe('Zwei Uploads „plakat.jpg“ (Monatsordner) bleiben zwei Fotos', [ma_foto_schluessel(507), ma_foto_schluessel(508)], ['a:507', 'a:508']);

echo "Foto-Schlüssel (Adresse): Plugin, Theme und endlos.js gleich\n";
$faelle = [
    '/assets/editorial-pools/kirchedetail/kirchedetail-01-995c6c09d8-1200.webp' => 'h:kirchedetail-01-995c6c09d8',
    'https://merzenich-aktuell.de/wp-content/uploads/2026/10/kirchedetail-01-995c6c09d8-1.jpg?ver=2' => 'h:kirchedetail-01-995c6c09d8',
    '/assets/editorial-pools/menschen/menschen-06-1835323b3c-scaled.webp' => 'h:menschen-06-1835323b3c',
    '/assets/responsive/37c79d0e603f3a-768.webp' => 'h:37c79d0e603f3a',
    '/assets/places/buergewald-1440.webp' => 'p:places/buergewald',
    '/assets/places/buergewald-720.webp' => 'p:places/buergewald',
    '/assets/places/golzheim-aachener-strasse-1440.webp' => 'p:places/golzheim-aachener-strasse',
    '/assets/uploads/leader-region.webp' => 'p:uploads/leader-region',
    '/assets/uploads/einsatz-118.webp' => 'p:uploads/einsatz-118',
    '/wp-content/uploads/2026/10/einsatz-118-300x200.jpg' => 'u:/wp-content/uploads/2026/10/einsatz-118',
    '/wp-content/uploads/2026/10/einsatz-121.jpg' => 'u:/wp-content/uploads/2026/10/einsatz-121',
    '/wp-content/uploads/2026/09/plakat.jpg' => 'u:/wp-content/uploads/2026/09/plakat',
    '/wp-content/uploads/2026/10/plakat-scaled.jpg' => 'u:/wp-content/uploads/2026/10/plakat',
    'https://merzenich-aktuell.de/wp-content/uploads/2026/10/foto-2026.jpg' => 'u:/wp-content/uploads/2026/10/foto-2026',
];
$theme = array_map('ma21_foto_schluessel', array_keys($faelle));
pruefe('Theme ma21_foto_schluessel: alle Fälle', $theme, array_values($faelle));
$plugin = array_map(fn($u) => ma_foto_schluessel_pfad($u) ?: 'u:', array_keys($faelle));
pruefe('Plugin ma_foto_schluessel_pfad: gleiche Schlüssel (ohne feste Kennung leer)', $plugin, array_map(fn($k) => str_starts_with($k, 'u:') ? 'u:' : $k, array_values($faelle)));
pruefe('Gleiche Breitenliste in Plugin und Theme', MA_FOTO_BREITEN, MA21_FOTO_BREITEN);
$js = (string) file_get_contents(__DIR__ . '/../../chatgpt-site/assets/endlos.js');
if (preg_match('/function fotoSchluessel\(src\) \{.*?\n  \}\n/s', $js, $fn) && preg_match("/const BREITEN = '([^']+)';/", $js, $br) && trim((string) shell_exec('command -v node')) !== '') {
    $skript = tempnam(sys_get_temp_dir(), 'fs') . '.js';
    file_put_contents($skript, "const BREITEN = '{$br[1]}';\n" . $fn[0] . "\nconsole.log(JSON.stringify(" . json_encode(array_keys($faelle)) . ".map(fotoSchluessel)));\n");
    pruefe('endlos.js fotoSchluessel: gleiche Schlüssel (node)', json_decode((string) shell_exec('node ' . escapeshellarg($skript)), true), array_values($faelle));
    @unlink($skript);
} else pruefe('endlos.js fotoSchluessel gefunden und node vorhanden', false, true);
pruefe('endlos.js: gleiche Grenze, Breiten und data-foto zuerst', [(bool) preg_match('/const BILD_MAX = ' . MA21_BILD_MAX . ';/', $js), ($br[1] ?? '') === MA21_FOTO_BREITEN, str_contains($js, "img.getAttribute('data-foto') || fotoSchluessel(")], [true, true, true]);
pruefe('endlos.js zählt Ortsbilder und Foto des Tages mit', str_contains($js, ".orte-buehne img, #foto-des-tages-bild'"), true);
pruefe('endlos.js begrenzt vor dem Einfügen', (bool) preg_match('/importNode\(k, true\)\);\s*bilderBegrenzen\(fertig\);/', $js), true);

echo "Kartenraster (/termine/, Ressorts)\n";
$neueSeite();
$h = ma21_feed_raster($meldungen([501, 502, 501, 503, 502, 501, 503]));
pruefe('Sieben Karten, alle da', substr_count($h, 'class="bildraster-karte'), 7);
pruefe('Kirchenfoto (zwei Anhänge) nur zweimal mit Bild', preg_match_all('#<img src="[^"]*kirchedetail-01-995c6c09d8#', $h), 2);
pruefe('Ab dem dritten Mal ohne Bild', substr_count($h, 'bildraster-karte--ohne-bild'), 3);
pruefe('Anderes Foto zweimal, beide mit Bild', preg_match_all('#<img src="[^"]*kirchedetail-02-3cc89ae5d6#', $h), 2);
pruefe('Bild trägt seinen Schlüssel für endlos.js (data-foto)', substr_count($h, 'data-foto="h:kirchedetail-01-995c6c09d8"'), 2);

echo "Liste: Aufmacher und Zeilen\n";
$neueSeite();
[$a, $b2, $c, $d] = $meldungen([504, 505, 504, 506]);
$lead = ma21_feed_lead($a);
$zeilen = ma21_feed_row($b2) . ma21_feed_row($c) . ma21_feed_row($d);
pruefe('Aufmacher behält sein Bild', [str_contains($lead, 'buergewald-1440'), str_contains($lead, 'no-media')], [true, false]);
pruefe('Ortsansicht 1440/720 zählt als ein Foto: dritte Zeile ohne Bild', substr_count($zeilen, 'no-media no-image'), 1);
pruefe('Andere Ansicht bleibt mit Bild', str_contains($zeilen, 'merzenich-st-laurentius'), true);
$neueSeite();
$h = ma21_feed_raster($meldungen([507, 508, 507, 508]));
pruefe('Zwei verschiedene „plakat.jpg“: je zweimal mit Bild, keine Karte ohne Bild', substr_count($h, 'bildraster-karte--ohne-bild'), 0);

echo "Meldungsseite\n";
$neueSeite();
[$haupt] = $meldungen([504]);
$hb = ma21_bild($haupt);
ma21_bild_frei($hb); // wie single.php
$karten = implode('', array_map('ma21_news_card', $meldungen([505, 504, 504])));
pruefe('Foto der Meldung zählt mit: Weiterlesen zeigt es nur noch einmal', preg_match_all('#<img src="[^"]*buergewald-#', $karten), 1);
pruefe('ma21_bild() (Meldungsseite, Vorladen) liefert das Foto weiter', ma21_bild($haupt)['src'] ?? '', 'https://merzenich-aktuell.de/wp-content/uploads/2026/09/buergewald-1440.webp');
$single = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/single.php');
pruefe('single.php zählt das Foto der Meldung', (bool) preg_match('/\$b = ma21_bild\(\$p\);.*?if \(\$b\) ma21_bild_frei\(\$b\);/s', $single), true);
$neueSeite();
pruefe('Neuer Seitenaufruf: Zähler wieder bei null', ma21_bild_seite($haupt) !== null, true);
pruefe('Ohne Beitragsbild: kein Eintrag im Zähler', [ma21_bild_seite(new WP_Post(9999)), count(ma21_bild_register())], [null, 1]);

echo "/heute/: Terminzeilen begrenzt der Server, /termine/ gibt alle aus\n";
if (!function_exists('trailingslashit')) { function trailingslashit($s) { return rtrim((string) $s, '/') . '/'; } }
require_once __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/termine.php';
$zeile = function (int $bild, bool $alle) use ($meldungen) { [$p] = $meldungen([$bild]); return ma21_termin_zeile(['post' => $p, 'url' => '/termine/x/', 'start' => 1792050000, 'ende' => 1792057200, 'ortSlug' => 'merzenich', 'kategorie' => 'Kirche', 'ortsteil' => 'Merzenich', 'titel' => 'Messe', 'ort' => '', 'veranstalter' => '', 'beschreibung' => '', 'quelle' => '', 'id' => $p->ID] + ['ganztags' => false, 'ohneEnde' => false], $alle); };
$neueSeite();
$heute = $zeile(501, false) . $zeile(502, false) . $zeile(501, false);
pruefe('/heute/: drittes Vorkommen desselben Fotos ohne Bild', [substr_count($heute, 'class="event-bild"'), substr_count($heute, 'event-row--bild')], [2, 2]);
$neueSeite();
$alle = $zeile(501, true) . $zeile(502, true) . $zeile(501, true);
pruefe('/termine/: alle Bilder (die Grenze rechnet v20.js nach dem Filtern)', substr_count($alle, 'class="event-bild"'), 3);
$termineSeite = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/termine.php');
pruefe('ma21_termine_seite ruft ma21_termin_zeile mit allen Bildern auf', str_contains($termineSeite, 'foreach ($kommend as $t) echo ma21_termin_zeile($t, true);'), true);

echo "/termine/: Grenze über die sichtbaren Zeilen (assets/v20.js)\n";
$termine = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/termine.php');
pruefe('ma21_termin_zeile: auf /termine/ alle Bilder (gefiltert wird im Browser), sonst begrenzt', str_contains($termine, "\$b = \$alleBilder ? ma21_bild(\$t['post']) : ma21_bild_seite(\$t['post']);"), true);
$v20 = (string) file_get_contents(__DIR__ . '/../../chatgpt-site/assets/v20.js');
if (preg_match('/const fotoGrenze=\(\)=>\{.*?\}\);\};/s', $v20, $fg) && trim((string) shell_exec('command -v node')) !== '') {
    // Zeilen wie auf /termine/: Foto A an Termin 1, 2, 4, 5; Filter blendet Termin 1 aus.
    $js = <<<'JS'
const zeile = (foto, hidden) => { const m = { hidden: false, dataset: {}, querySelector: () => ({ getAttribute: (a) => a === 'data-foto' ? foto : '' }) }; return { hidden, m, querySelector: () => m }; };
const rows = [zeile('A', false), zeile('A', false), zeile('B', false), zeile('A', false), zeile('A', false)];
JS;
    $js .= $fg[0] . "\nfotoGrenze(); const a = rows.map(r => r.m.hidden);\nrows[0].hidden = true; fotoGrenze(); const b = rows.map(r => r.m.hidden);\nrows[0].hidden = false; fotoGrenze(); const c = rows.map(r => r.m.hidden);\nconsole.log(JSON.stringify([a, b, c]));\n";
    $v20Datei = tempnam(sys_get_temp_dir(), 'v20') . '.js'; file_put_contents($v20Datei, $js);
    [$a, $b, $c] = json_decode((string) shell_exec('node ' . escapeshellarg($v20Datei)), true) ?: [[], [], []];
    @unlink($v20Datei);
    pruefe('Alle sichtbar: drittes und viertes A ohne Bild', $a, [false, false, false, true, true]);
    pruefe('Erster Termin ausgefiltert: nur noch das vierte A ohne Bild (die ausgeblendete Zeile bleibt unberührt)', $b, [false, false, false, false, true]);
    pruefe('Filter zurück: wieder wie vorher', $c, $a);
} else pruefe('v20.js fotoGrenze gefunden und node vorhanden', false, true);
$audit = (string) file_get_contents(__DIR__ . '/../../chatgpt-site/assets/editorial-audit.js');
pruefe('editorial-audit.js blendet laufende mehrtägige Termine (mit data-end) nicht aus', str_contains($audit, "const w=el.closest('[data-event-row]');if(w&&Number.isFinite(Date.parse(w.getAttribute('data-end')||'')))return;"), true);
pruefe('run() rechnet die Grenze nach jedem Filtern', str_contains($v20, "\$('[data-event-empty]').hidden=n>0;fotoGrenze();};"), true);

echo "Startseite: Bühne behält ihr Foto, Rubrikkarten höchstens zweimal (feste Plätze)\n";
function is_front_page() { return !empty($GLOBALS['startseite']); }
$neueSeite(); $GLOBALS['startseite'] = true;
[$s1, $s2, $s3, $s4] = $meldungen([504, 505, 501, 501]);
[$s5, $s6] = $meldungen([501, 502]);
$buehne = ma21_karte($s1, 'r') . ma21_karte($s3, 'xl') . ma21_karte($s4, 'u') . ma21_karte($s5, 'u');
pruefe('Aufmacher und Bühne: alle mit Bild, auch das dritte Kirchenfoto', substr_count($buehne, 'class="karte-bild"'), 4);
$rubrik = ma21_karte($s2, 'm') . ma21_karte($s6, 'l');
pruefe('Ortsbild der Orte-Bühne zählt vorab: Bürgewald in der Rubrik ohne Bild', str_contains(ma21_karte($s2, 'm'), 'desk-karte--ohne-bild'), true);
pruefe('Kirchenfoto schon dreimal oben: Rubrikkarte ohne Bild', substr_count($rubrik, 'desk-karte--ohne-bild'), 2);
$GLOBALS['startseite'] = false;

echo "Startseite\n";
$vorlage = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/vorlagen/startseite.html');
preg_match_all('#<span class="ort-bild"><img src="([^"]+)"#', $vorlage, $orte);
pruefe('Ortsbilder der Bühne haben die Schlüssel, die motiveVorab vorab zählt', array_map('ma21_foto_schluessel', $orte[1]), array_map(fn($o) => ma21_foto_schluessel("/assets/places/{$o}-720.webp"), array_keys(MA21_ORTE)));
pruefe('Ortsansicht als Abgleich-Anhang hat denselben Schlüssel wie das Ortsbild der Bühne', ma_foto_schluessel(504), ma21_foto_schluessel($orte[1][4] ?? ''));
$ma21 = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php');
pruefe('Foto des Tages trägt data-foto aus der ursprünglichen Adresse', str_contains($ma21, '<img id="foto-des-tages-bild" data-foto="\' . esc_attr($foto) . \'"') && str_contains($ma21, '$foto = ma21_foto_schluessel($src); $id = 0;'), true);
pruefe('Foto des Tages meidet Fotos der Startseite nach Foto-Schlüssel (auch Ortsbilder)', str_contains($ma21, "!in_array(ma21_foto_schluessel((string) \$r['src']), \$belegt, true)") && str_contains($ma21, '$motive = array_map(fn($o) => ma21_foto_schluessel("/assets/places/{$o}-720.webp"), array_keys(MA21_ORTE));'), true);
$fdt = (string) file_get_contents(__DIR__ . '/../../chatgpt-site/assets/foto-des-tages.js');
pruefe('foto-des-tages.js entfernt data-foto beim Tausch', str_contains($fdt, "img.removeAttribute('data-foto')"), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
