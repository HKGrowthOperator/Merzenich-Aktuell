<?php
/**
 * Ressort-Menü und Ressortseiten (inc/ma21.php) ohne WordPress: nur rote Titel
 * (21.19.0, Wunsch Betreiber 15.10.2026), Themen ab einer Meldung (bis 21.13
 * erst ab drei, Menüs schrumpften), Sammelseiten /rubrik/<slug>/ nur mit
 * Meldungen, Kartenraster nur für die Nachrichten-Ressorts.
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
$GLOBALS['transient'] = [];
function get_transient($k) { return $GLOBALS['transient'][$k] ?? false; }
function set_transient($k, $v, $t = 0) { $GLOBALS['transient'][$k] = $v; return true; }
const MINUTE_IN_SECONDS = 60;
// Kategorien und Meldungen je Abfrage: eine Meldung, wenn ein Thema im Stub-Bestand Meldungen hat.
function get_category_by_slug($s) { $ids = ['blaulicht' => 3, 'sport' => 4]; if (!isset($ids[$s])) return false; $c = new WP_Term(); $c->term_id = $ids[$s]; return $c; }
$GLOBALS['abfragen'] = [];
function get_posts($a) { $GLOBALS['abfragen'][] = $a; foreach ((array) ($a['tag_slug__in'] ?? []) as $t) if (($GLOBALS['themen'][$t] ?? 0) > 0) return [1]; return []; }
$GLOBALS['qv'] = [];
function get_query_var($k, $d = '') { return $GLOBALS['qv'][$k] ?? $d; }
// Themenseite (/thema/<slug>/): das Schlagwort der Anfrage, sonst keins
$GLOBALS['tag'] = null;
function is_tag() { return $GLOBALS['tag'] !== null; } function get_queried_object() { return $GLOBALS['tag']; }
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-70s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Rote Titel des Ressort-Menüs\n";
$p = ma21_menue_punkte_pruefen([
    ['titel' => 'Gemeinderat', 'href' => '/thema/gemeinderat/'],
    ['titel' => 'Ohne Meldung', 'href' => '/thema/leer/'],
    ['titel' => 'Gibt es nicht', 'href' => '/thema/unbekannt/'],
    ['titel' => 'Golzheim', 'href' => '/golzheim/'],
    ['titel' => 'Termine Golzheim', 'href' => '/termine/?ort=golzheim'],
    ['titel' => 'Geld & Planung', 'href' => '/rubrik/geld-und-planung/'],
    ['titel' => 'Unbekannte Sammelseite', 'href' => '/rubrik/gibt-es-nicht/'],
    ['titel' => 'Service', 'href' => '/service/'],
    ['titel' => '', 'href' => '/leer/'],
    ['titel' => 'Unterpunkte', 'href' => '/x/', 'links' => [['Diebstahl', '/thema/diebstahl/']]],
]);
pruefe('Themen ab einer Meldung bleiben, leere und unbekannte fallen weg', array_column($p, 'titel'), ['Gemeinderat', 'Golzheim', 'Termine Golzheim', 'Geld & Planung', 'Service', 'Unterpunkte']);
pruefe('Ortsteil zeigt auf /ort/, vorgefilterte Termine bleiben', [$p[1]['href'], $p[2]['href']], ['/ort/golzheim/', '/termine/?ort=golzheim']);
pruefe('Kleine Unterpunkte werden nie ausgeliefert (nur titel und href)', array_keys($p[5]), ['titel', 'href']);

echo "\nSammelseiten /rubrik/<slug>/\n";
$r = ma21_rubriken();
pruefe('Definitionen aus inc/rubriken.json (Verkehr, Gemeinde, Geld & Planung)', [isset($r['verkehr']), isset($r['gemeinde']), isset($r['geld-und-planung'])], [true, true, true]);
pruefe('Verkehr gehört zu Blaulicht', [$r['verkehr']['ressort'], $r['verkehr']['ressortTitel'], $r['verkehr']['kategorie']], ['/blaulicht/', 'Blaulicht', 'blaulicht']);
$a = ma21_rubrik_args($r['verkehr']);
pruefe('Abfrage Verkehr: Themen als ODER, nur Blaulicht', [$a['post_type'], in_array('verkehrsunfall', $a['tag_slug__in'], true), $a['category_name'], isset($a['category__not_in'])], ['post', true, 'blaulicht', false]);
$a = ma21_rubrik_args(['themen' => ['schule'], 'ohneKategorie' => ['blaulicht', 'gibt-es-nicht']]);
pruefe('Abfrage mit ausgeschlossenem Ressort (Familie & Schule ohne Blaulicht)', [$a['category__not_in'] ?? null, isset($a['category_name'])], [[3], false]);
pruefe('Sammelseite mit Meldung ja, unbekannte nein', [ma21_rubrik_hat_beitraege('geld-und-planung'), ma21_rubrik_hat_beitraege('gibt-es-nicht')], [true, false]);
$n = count($GLOBALS['abfragen']); ma21_rubrik_hat_beitraege('geld-und-planung');
pruefe('Zahl der Meldungen ist zwischengespeichert (keine zweite Abfrage)', count($GLOBALS['abfragen']), $n);
$GLOBALS['qv']['ma_rubrik'] = 'verkehr';
pruefe('Listenkopf der Sammelseite: Ressort, Titel, Beschreibung', array_slice(ma21_liste_kopf(), 0, 2), ['Blaulicht', 'Verkehr']);
pruefe('Brotkrumen: Start › Blaulicht › Verkehr', ma21_liste_eltern(), ['Blaulicht', 'https://merzenich-aktuell.de/blaulicht/']);
$GLOBALS['qv']['ma_rubrik'] = 'gibt-es-nicht';
pruefe('Unbekannte Sammelseite hat keinen Kopf', [ma21_rubrik_aktuell(), ma21_liste_eltern()], [null, null]);
unset($GLOBALS['qv']['ma_rubrik']);
$t = new WP_Term(); $t->slug = 'feuerwehr'; $GLOBALS['tag'] = $t;
pruefe('Themenseite aus dem Blaulicht-Menü: Start › Blaulicht › Feuerwehr', ma21_liste_eltern(), ['Blaulicht', 'https://merzenich-aktuell.de/blaulicht/']);
$t = new WP_Term(); $t->slug = 'irgendwas'; $GLOBALS['tag'] = $t;
pruefe('Andere Themenseite: keine Ressort-Brotkrume', ma21_liste_eltern(), null);
$GLOBALS['tag'] = null;
pruefe('Themen der roten Menütitel: Feuerwehr und Polizei bei Blaulicht, Fußball bei Sport', [ma21_thema_ressort('feuerwehr')['ressort'] ?? '', ma21_thema_ressort('polizei')['ressort'] ?? '', ma21_thema_ressort('fussball')['ressort'] ?? ''], ['/blaulicht/', '/blaulicht/', '/sport/']);

echo "\nKartenraster der Ressortseiten\n";
pruefe('Raster für Aktuell, Blaulicht, Rathaus, Leben, Wirtschaft, Menschen', array_map('ma21_raster_seite', ['ressort-nachrichten', 'ressort-blaulicht', 'ressort-rathaus', 'ressort-leben', 'ressort-wirtschaft', 'ressort-menschen']), [true, true, true, true, true, true]);
pruefe('Sport, Vereine, Ort, Thema und Suche behalten ihr Layout', array_map('ma21_raster_seite', ['ressort-sport', 'ressort-vereine', 'liste', '']), [false, false, false, false]);
pruefe('Aufmacher plus gerade Kartenzahl je Seite', [ma21_raster_anzahl('ressort-nachrichten') - 1, ma21_raster_anzahl('ressort-blaulicht') - 1], [20, 12]);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
