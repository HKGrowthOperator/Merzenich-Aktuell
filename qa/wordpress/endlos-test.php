<?php
/**
 * Endlos-Scrollen (inc/ma21.php, 21.19.0, Wunsch Betreiber 15.10.2026) ohne
 * WordPress: Teilstück für ?ma_endlos=1 (Raster-Ressorts als Karten, alle
 * anderen Listen als Zeilen, nie ein Aufmacher), Adresse der Folgeseite ohne
 * ma_endlos, Datenattribute am .feed, rel=next/prev am Blättern, und welche
 * Seiten überhaupt endlos laufen (keine Termine, Märkte, Unternehmen).
 * Aufruf: php qa/wordpress/endlos-test.php
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
function get_the_title($p) { return $p->post_title; }
function get_post_meta(...$a) { return $a[1] === 'ma_startplatz' && in_array($a[0], $GLOBALS['nur_rubrik'] ?? [], true) ? 'aus' : ''; }
function get_the_terms(...$a) { return []; }
function get_the_category($id) { $c = new WP_Term(); $c->slug = 'blaulicht'; $c->name = 'Blaulicht'; return [$c]; }
function get_post_thumbnail_id(...$a) { return 0; }
function has_post_thumbnail(...$a) { return false; }
function get_the_post_thumbnail_url(...$a) { return false; }
function wp_get_attachment_image_src(...$a) { return false; }
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
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-72s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
$posts = array_map(fn($i) => new WP_Post($i), range(14, 26));

echo "Teilstück (?ma_endlos=1)\n";
$h = ma21_endlos_teil('ressort-blaulicht', $posts, '/blaulicht/page/3/');
pruefe('Raster-Ressort: Karten im Kartenraster, kein Aufmacher, keine Zeilen', [substr_count($h, 'class="bildraster bildraster--ressort"'), substr_count($h, 'class="bildraster-karte'), str_contains($h, 'feed-lead'), str_contains($h, 'feed-row')], [1, 13, false, false]);
pruefe('Raster: data-art und Folgeseite am Teilstück', [str_starts_with($h, '<div data-endlos-teil data-art="raster" data-naechste="/blaulicht/page/3/">'), str_ends_with($h, '</div></div>')], [true, true]);
pruefe('Jede Karte trägt data-story (Abgleich gegen Doppelte in endlos.js)', substr_count($h, ' data-story="'), 13);
$h = ma21_endlos_teil('ressort-sport', $posts, '');
pruefe('Sport (eigenes Layout) und letzte Seite: Zeilen, data-naechste leer', [substr_count($h, 'class="feed-row'), str_contains($h, 'bildraster'), str_contains($h, 'data-art="reihen" data-naechste=""')], [13, false, true]);
pruefe('Zeile ohne Bild: eine Spalte (no-image wie in der statischen Kopie)', substr_count($h, 'class="feed-row no-media no-image"'), 13);
$h = ma21_endlos_teil('liste', array_slice($posts, 0, 2), '/thema/feuerwehr/page/2/?x=1&y=2');
pruefe('Thema, Ort, Suche: Zeilen; Folgeadresse maskiert', [substr_count($h, 'class="feed-row'), str_contains($h, 'data-naechste="/thema/feuerwehr/page/2/?x=1&amp;y=2"')], [2, true]);

echo "\nFolgeseite\n";
global $wp_query; $wp_query = new WP_Query(); $wp_query->max_num_pages = 6;
$GLOBALS['anfrage'] = '/blaulicht/page/2/?ma_endlos=1'; $GLOBALS['qv'] = ['paged' => 2, 'ma_endlos' => '1'];
pruefe('Seite 2 von 6 → Seite 3, ohne ma_endlos (die Liste bleibt teilbar und zitierbar)', ma21_endlos_naechste(), 'https://merzenich-aktuell.de/blaulicht/page/3/');
$GLOBALS['anfrage'] = '/?s=feuerwehr&ma_endlos=1'; $GLOBALS['qv'] = ['paged' => 0];
pruefe('Suche: Abfrage bleibt, ma_endlos fällt weg', ma21_endlos_naechste(), 'https://merzenich-aktuell.de/page/2/?s=feuerwehr');
$GLOBALS['qv'] = ['paged' => 6];
pruefe('Letzte Seite: keine Folgeseite', ma21_endlos_naechste(), '');
pruefe('Letzte von mehreren Seiten: kein Attribut, Blättern zurück bleibt', ma21_endlos_attr('ressort-blaulicht'), '');
$wp_query->max_num_pages = 1; $GLOBALS['qv'] = [];
pruefe('Liste mit nur einer Seite: leeres Attribut, endlos.js zeigt den Endhinweis', [ma21_endlos_naechste(), ma21_endlos_attr('ressort-blaulicht')], ['', ' data-endlos-naechste="" data-endlos-art="raster"']);
$wp_query->max_num_pages = 0;
pruefe('Leere Liste (keine Treffer): kein Attribut, kein Endhinweis', ma21_endlos_attr('liste'), '');
$wp_query->max_num_pages = 1;
$wp_query->max_num_pages = 4; $GLOBALS['anfrage'] = '/sport/';
pruefe('Attribute am .feed: Folgeseite und Art', [ma21_endlos_attr('ressort-blaulicht'), ma21_endlos_attr('ressort-sport')], [' data-endlos-naechste="https://merzenich-aktuell.de/sport/page/2/" data-endlos-art="raster"', ' data-endlos-naechste="https://merzenich-aktuell.de/sport/page/2/" data-endlos-art="reihen"']);

echo "\nWelche Seiten endlos laufen\n";
$leer = ['category' => false, 'tag' => false, 'tax' => false, 'search' => false, 'kat' => '', 'taxonomie' => 'ma_location', 'archiv' => '', '404' => false];
$fall = function (array $ist, array $qv = []) use ($leer) { $GLOBALS['ist'] = $ist + $leer; $GLOBALS['qv'] = $qv; return ma21_endlos_liste(); };
pruefe('Ressort, Thema, Ortsteil, Suche, Aktuell, Autor, Sammelseite', [$fall(['category' => true]), $fall(['tag' => true]), $fall(['tax' => true]), $fall(['search' => true]), $fall([], ['ma_alle' => '1']), $fall([], ['ma_autor' => 'redaktion']), $fall([], ['ma_rubrik' => 'verkehr'])], [true, true, true, true, true, true, true]);
pruefe('Termine, Märkte, Unternehmen, Einzelseiten: nein', [$fall([]), $fall([], ['ma_rubrik' => 'gibt-es-nicht']), $fall(['archiv' => 'ma_event']), $fall(['archiv' => 'ma_job']), $fall([], ['ma_unternehmen' => '1']), $fall(['tax' => true, 'taxonomie' => 'ma_event_category'])], [false, false, false, false, false, false]);

echo "\nAntwort des Teilmodus (?ma_endlos=1)\n";
$GLOBALS['ist'] = ['category' => true, 'kat' => 'blaulicht'] + $leer;
$wp_query->max_num_pages = 4; $wp_query->posts = array_slice($posts, 0, 3); $GLOBALS['anfrage'] = '/blaulicht/page/2/?ma_endlos=1';
$GLOBALS['qv'] = ['paged' => 2];
pruefe('Ohne ?ma_endlos: keine Antwort (normale Seite)', ma21_endlos_antwort(), null);
$GLOBALS['qv'] = ['paged' => 2, 'ma_endlos' => '1'];
$a = ma21_endlos_antwort();
pruefe('Mit ?ma_endlos: 200 und nur das Teilstück mit Folgeseite', [$a[0] ?? null, str_starts_with($a[1] ?? '', '<div data-endlos-teil'), str_contains($a[1] ?? '', 'data-naechste="https://merzenich-aktuell.de/blaulicht/page/3/"'), str_contains($a[1] ?? '', '<html')], [200, true, true, false]);
$wp_query->posts = [];
pruefe('Hinter der letzten Seite: 404 ohne Inhalt', ma21_endlos_antwort(), [404, '']);
$wp_query->posts = array_slice($posts, 0, 3); $GLOBALS['ist']['404'] = true;
pruefe('WordPress meldet 404 (Seite zu groß): 404', ma21_endlos_antwort()[0] ?? null, 404);
$GLOBALS['ist']['404'] = false;
$GLOBALS['optionen'] = ['ma21_ausbau' => ['endlos' => false]];
pruefe('Endlos-Scrollen ausgeschaltet (Ausbaustufe): keine Antwort, Blättern wie bisher', [ma21_endlos_antwort(), ma21_endlos_attr('ressort-blaulicht')], [null, '']);
$GLOBALS['optionen'] = [];
$GLOBALS['ist'] = ['tax' => false] + $leer; $GLOBALS['anfrage'] = '/nachrichten/page/2/?ma_ohne_sport=1&ma_endlos=1';
$GLOBALS['qv'] = ['paged' => 2, 'ma_endlos' => '1', 'ma_alle' => '1', 'ma_ohne_sport' => '1'];
$wp_query->posts = array_slice($posts, 0, 4); $GLOBALS['sport'] = [$posts[0]->ID]; $GLOBALS['sportbezug'] = [$posts[2]->ID];
$a = ma21_endlos_antwort();
pruefe('Startseite (?ma_ohne_sport=1): Sport und Sportbezug fallen weg, Folgeseite behält den Filter', [$a[0] ?? null, substr_count($a[1] ?? '', ' data-story="'), str_contains($a[1] ?? '', 'data-naechste="https://merzenich-aktuell.de/nachrichten/page/3/?ma_ohne_sport=1"')], [200, 2, true]);
$GLOBALS['nur_rubrik'] = [$posts[1]->ID];
$a = ma21_endlos_antwort();
pruefe('Startseite (?ma_ohne_sport=1): „Nur in der Rubrik“ bleibt auch in „Weitere Meldungen“ draußen (Review 16.10.)', [$a[0] ?? null, substr_count($a[1] ?? '', ' data-story="')], [200, 1]);
$GLOBALS['nur_rubrik'] = [];
$GLOBALS['sport'] = array_map(fn($p) => $p->ID, $posts);
$a = ma21_endlos_antwort();
pruefe('Nur Sport auf der Seite: 200 ohne Meldungen, aber mit Folgeseite (endlos.js lädt weiter)', [$a[0] ?? null, substr_count($a[1] ?? '', ' data-story="'), str_contains($a[1] ?? '', 'page/3/')], [200, 0, true]);
$GLOBALS['sport'] = []; $GLOBALS['sportbezug'] = []; $GLOBALS['qv'] = ['paged' => 2, 'ma_endlos' => '1'];
pruefe('ma_ohne_sport ist eine erlaubte Abfragevariable', in_array('ma_ohne_sport', array_reduce($GLOBALS['hooks']['query_vars'] ?? [], fn($v, $cb) => $cb($v), []), true), true);
$GLOBALS['ist'] = ['archiv' => 'ma_event'] + $leer;
pruefe('Terminliste mit ?ma_endlos: keine Antwort', ma21_endlos_antwort(), null);
$GLOBALS['ist'] = $leer;

echo "\nBlättern bleibt für Suchmaschinen und ohne JavaScript\n";
$filter = null; foreach ($GLOBALS['hooks']['paginate_links_output'] ?? [] as $cb) $filter = $cb;
$b = $filter ? $filter('<a class="prev page-numbers" href="/blaulicht/">‹ Neuere</a><a class="next page-numbers" href="/blaulicht/page/3/">Ältere ›</a>') : '';
pruefe('rel="next" und rel="prev" an den Blätterlinks', [str_contains($b, '<a class="next page-numbers" rel="next"'), str_contains($b, '<a class="prev page-numbers" rel="prev"')], [true, true]);
pruefe('ma_endlos ist eine erlaubte Abfragevariable', in_array('ma_endlos', array_reduce($GLOBALS['hooks']['query_vars'] ?? [], fn($v, $cb) => $cb($v), []), true), true);
$vorlage = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/archive.php');
pruefe('archive.php und autor.php setzen die Attribute am .feed', [str_contains($vorlage, 'ma21_endlos_attr('), str_contains((string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/autor.php'), 'ma21_endlos_attr(')], [true, true]);

echo "\nDateien aus dem Repository ohne Warten (21.19.1)\n";
$k = 'ma21_repo_' . md5('index.html');
$GLOBALS['optionen'] = [$k . '_kopie' => 'alt', $k . '_zeit' => time() - 2 * HOUR_IN_SECONDS];
pruefe('Abgelaufen mit Kopie: sofort die Kopie, kein Abruf, Auffrischen im Hintergrund geplant', [ma21_repo_datei('index.html'), $GLOBALS['abrufe'], count($GLOBALS['geplant'])], ['alt', 0, 1]);
ma21_repo_datei('index.html');
pruefe('Zweiter Aufruf plant nicht noch einmal', count($GLOBALS['geplant']), 1);
$GLOBALS['optionen'] = [$k . '_kopie' => 'alt'];
pruefe('Kopie ohne Zeitpunkt (ältere Installation): einmal direkt holen, Zeit merken', [ma21_repo_datei('index.html'), $GLOBALS['abrufe'], isset($GLOBALS['optionen'][$k . '_zeit'])], ['neu', 1, true]);
$GLOBALS['optionen'] = [];
pruefe('Keine Kopie: direkt holen', [ma21_repo_datei('index.html'), $GLOBALS['abrufe']], ['neu', 2]);

echo "\nAnrisse am Wortende kürzen (21.19.1)\n";
pruefe('Kürzung endet am Wortende mit …', ma21_kurz('Familien sollen ihr Kind jetzt vormerken. Der Kreis nennt die Frist', 47), 'Familien sollen ihr Kind jetzt vormerken. Der …');
pruefe('Kurzer Text bleibt unverändert', ma21_kurz('Kurz und gut.', 150), 'Kurz und gut.');
pruefe('Ohne Leerzeichen: hart gekürzt', ma21_kurz(str_repeat('x', 20), 10), str_repeat('x', 10) . ' …');
echo "\nSuche: Begriff, Trefferzahl, Feld (21.20.1)\n";
pruefe('Leere Suche bittet um einen Begriff', ma21_suche_kopf('', 190), ['Suche', 'Suche', 'Wonach suchen Sie? Geben Sie einen Ort, ein Thema oder ein Stichwort ein.']);
pruefe('Mit Begriff: in Anführungszeichen, Trefferzahl', [ma21_suche_kopf('Schule', 13)[1], ma21_suche_kopf('Schule', 13)[2], ma21_suche_kopf('Kita', 1)[2], ma21_suche_kopf('xyz', 0)[2]], ['Suche: „Schule“', '13 Treffer', '1 Treffer', '0 Treffer']);
$feld = ma21_suchfeld('a"b<c');
pruefe('Suchfeld trägt den Begriff maskiert', str_contains($feld, 'name="s"') && str_contains($feld, 'value="a&quot;b&lt;c"'), true);
$archivQuelle = file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/archive.php');
pruefe('archive.php: ohne Begriff keine Liste, kein Blättern, kein Endlos', substr_count($archivQuelle, '!$leereSuche') === 3, true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
