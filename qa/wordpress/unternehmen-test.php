<?php
/**
 * Unternehmenskanäle im Theme (21.18.0, inc/unternehmen.php) ohne WordPress:
 * Musterfirmen vollständig und gekennzeichnet, jeder Musterbeitrag mit eigener
 * Seite (nie das Anfrageformular), Adressen und 404, Seiten für Kanal und
 * Musterbeitrag, Unternehmen-Menü ohne Anzeige und mit Firmen, Suchmaschinen
 * gesperrt, statisches Menü wie WordPress.
 * Aufruf: php qa/wordpress/unternehmen-test.php
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
function wp_date($f) { return '2026-10-13T12:00:00+02:00'; }
function get_transient($k) { return false; } function set_transient($k, $v, $t) { return true; }
function get_term_by(...$a) { return false; } function get_category_by_slug($s) { return false; }
function get_posts($q) { return []; } function get_users($q) { return []; }
function get_query_var($k) { return $GLOBALS['qv'][$k] ?? ''; }
function get_header() {} function get_footer() {}
function ma_seo_kuerzen($t, $max = 160) { return mb_substr((string) $t, 0, $max); }
class WP_Term { public $count = 0; } class WP_Query {} class WP_Post {}
require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/ma21.php';
require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/markt.php';
require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/unternehmen.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Musterfirmen (inc/unternehmen-muster.json)\n";
$roh = ma21_muster_firmen();
$bilder = ma21_werben_bilder();
pruefe('Fünf Musterfirmen', count($roh), 5);
pruefe('Slugs eindeutig und mit „muster-“', [count(array_unique(array_column($roh, 'slug'))), count(array_filter($roh, fn($m) => str_starts_with($m['slug'], 'muster-')))], [5, 5]);
pruefe('Namen beginnen mit „Muster-“ (nie wie ein echter Betrieb)', count(array_filter($roh, fn($m) => str_starts_with($m['name'], 'Muster-'))), 5);
pruefe('Orte sind Ortsteile der Gemeinde', count(array_filter($roh, fn($m) => isset(MA21_ORTE[$m['ort']]))), 5);
$ids = [];
foreach ($roh as $m) {
    $ids[] = $m['bild']; $ids[] = $m['anzeige']['bild'];
    pruefe($m['name'] . ': 2 bis 3 Beiträge mit 3 bis 5 Absätzen', [count($m['beitraege']) >= 2 && count($m['beitraege']) <= 3, count(array_filter($m['beitraege'], fn($b) => count($b['absaetze']) >= 3 && count($b['absaetze']) <= 5))], [true, count($m['beitraege'])]);
    pruefe($m['name'] . ': Testanzeige mit Titel, Text und Bild', [($m['anzeige']['titel'] ?? '') !== '', ($m['anzeige']['text'] ?? '') !== '', isset($bilder[$m['anzeige']['bild']])], [true, true, true]);
    foreach ($m['beitraege'] as $b) $ids[] = $b['bild'];
    pruefe($m['name'] . ': keine Preise oder Euro-Beträge', preg_match('/\d+\s*(€|Euro)/u', json_encode($m, JSON_UNESCAPED_UNICODE)), 0);
}
foreach (array_unique($ids) as $id) {
    $b = ma21_muster_bild($id);
    $datei = $wurzel . '/chatgpt-site' . ($b['src'] ?? '/fehlt');
    pruefe("Bild $id: Datei, 720er-Fassung, freie Lizenz, Urheber", [$b !== null && is_file($datei), $b !== null && is_file(preg_replace('/\.jpg$/', '-720.jpg', $datei)), $b !== null && preg_match('/^(CC0|Public domain|CC BY)/i', $b['lizenz']) === 1, ($b['foto'] ?? '') !== ''], [true, true, true, true]);
}

echo "\nAdressen (keine echten Kanäle: Musterfirmen aktiv)\n";
pruefe('Musterfirmen werden gezeigt', ma21_muster_aktiv(), true);
pruefe('Kanal einer Musterfirma', ma21_firmen_route('muster-backstube')['art'], 'kanal');
$r = ma21_firmen_route('muster-backstube', 'roggenbroetchen-aus-dem-steinofen');
pruefe('Musterbeitrag mit eigener Seite', [$r['art'], $r['nr'] ?? null], ['beitrag', 1]);
pruefe('Unbekannter Beitrag: 404', ma21_firmen_route('muster-backstube', 'gibt-es-nicht')['art'], '404');
pruefe('Unbekannte Firma: 404', ma21_firmen_route('unbekannt')['art'], '404');
pruefe('Reservierte Namen (page, feed): 404', [ma21_firmen_route('page')['art'], ma21_firmen_route('feed')['art']], ['404', '404']);

echo "\nÜbersicht /unternehmen/ und Kasten Musterprofile\n";
$html = ma21_musterbeitraege_html(6);
preg_match_all('#<article class="bildraster-karte[^"]*"#', $html, $karten);
pruefe('Sechs Musterbeiträge als Karten', count($karten[0]), 6);
pruefe('Jede Karte trägt „Musterbeitrag“', substr_count($html, '<span class="badge">Musterbeitrag</span>'), 6);
pruefe('Karten führen auf Beitragsseiten, nie auf das Anfrageformular', [str_contains($html, '/anzeigen/aufgeben/'), preg_match_all('#href="https://merzenich-aktuell\.de/unternehmen/muster-[a-z\-]+/[a-z0-9\-]+/"#', $html)], [false, 12]);
pruefe('Firma und Ort in jeder Karte', substr_count($html, 'Anzeige · <a href="https://merzenich-aktuell.de/unternehmen/muster-'), 6);
pruefe('Hinweis: keine echten Unternehmen', str_contains($html, 'keine echten Unternehmen'), true);
pruefe('Testanzeige unter den Karten, gekennzeichnet', [substr_count($html, '<span class="u-testanzeige__label">Testanzeige</span>'), str_contains($html, 'kein echtes Unternehmen')], [1, true]);
$kasten = ma21_musterprofile_html(0, 'https://merzenich-aktuell.de/anzeigen/aufgeben/?art=Werbung');
pruefe('Musterprofile führen in ihren Kanal', [substr_count($kasten, 'class="u-muster__link" href="https://merzenich-aktuell.de/unternehmen/muster-'), substr_count($kasten, 'Musterprofil</span>')], [5, 5]);
pruefe('Ab drei echten Profilen kein Kasten', ma21_musterprofile_html(3, ''), '');
$kacheln = ma21_firmen_kacheln_html(ma21_alle_firmen());
pruefe('Kacheln der Unternehmenskanäle', [substr_count($kacheln, 'class="u-kanal"'), substr_count($kacheln, '<em>Musterprofil</em>')], [5, 5]);

echo "\nSeite Musterbeitrag\n";
$GLOBALS['qv'] = ['ma_firma' => 'muster-cafe-am-markt', 'ma_firmenbeitrag' => 'torten-aus-eigener-herstellung'];
unset($GLOBALS['ma21_firmen_route']);
ob_start(); include $wurzel . '/wordpress/theme/merzenich-aktuell/unternehmen-beitrag.php'; $seite = ob_get_clean();
pruefe('Überschrift, Anriss und Absätze', [str_contains($seite, '<h1>Torten aus eigener Herstellung, auch auf Bestellung</h1>'), str_contains($seite, 'class="dek"'), substr_count($seite, '<p>Jeden Morgen') + substr_count($seite, '<p>Für Geburtstage') + substr_count($seite, '<p>Wer lieber')], [true, true, 3]);
pruefe('Kennzeichnung Musterbeitrag oben und unten', [str_contains($seite, '<span class="gesponsert">Musterbeitrag</span>'), str_contains($seite, 'Musterbeitrag von Muster-Café am Markt')], [true, true]);
pruefe('Bild mit Nachweis und Lizenzlink', [str_contains($seite, '<figure class="art-figure'), str_contains($seite, 'rel="noopener license"'), str_contains($seite, 'Wikimedia Commons')], [true, true, true]);
pruefe('Testanzeige der Firma und Weg in den Kanal', [str_contains($seite, 'Testanzeige'), str_contains($seite, 'href="https://merzenich-aktuell.de/unternehmen/muster-cafe-am-markt/">Zum Unternehmenskanal')], [true, true]);
pruefe('Keine Teilen-Knöpfe, keine Kommentare', [str_contains($seite, 'api.whatsapp.com'), str_contains($seite, 'comments')], [false, false]);

echo "\nSeite Kanal\n";
$GLOBALS['qv'] = ['ma_firma' => 'muster-schreinerei'];
unset($GLOBALS['ma21_firmen_route']);
ob_start(); include $wurzel . '/wordpress/theme/merzenich-aktuell/unternehmen-kanal.php'; $kanal = ob_get_clean();
pruefe('Name, Zeile und Hinweis Musterprofil', [str_contains($kanal, '<h1>Muster-Schreinerei</h1>'), str_contains($kanal, 'Handwerk · Girbelsrath'), str_contains($kanal, 'Dieses Unternehmen ist ausgedacht')], [true, true, true]);
pruefe('Alle drei Beiträge als Karten', substr_count($kanal, 'class="bildraster-karte u-firmen-karte'), 3);
pruefe('Testanzeige und weitere Kanäle', [str_contains($kanal, 'Testanzeige'), substr_count($kanal, 'class="u-kanal"')], [true, 4]);

echo "\nSuchmaschinen\n";
$basis = ['typ' => 'liste', 'titel' => '', 'beschreibung' => '', 'url' => '', 'index' => true, 'og' => 'website', 'krumen' => [], 'bild' => ['url' => '', 'w' => 0, 'h' => 0], 'liste' => []];
$k = ma21_firmen_seo($basis);
pruefe('Kanal einer Musterfirma: noindex, eigene Adresse, Titel', [$k['index'], $k['url'], $k['titel']], [false, 'https://merzenich-aktuell.de/unternehmen/muster-schreinerei/', 'Muster-Schreinerei (Musterprofil) · Unternehmenskanal']);
$GLOBALS['qv'] = ['ma_firma' => 'muster-immobilien', 'ma_firmenbeitrag' => 'grundrisse-lesen'];
unset($GLOBALS['ma21_firmen_route']);
$k = ma21_firmen_seo($basis);
pruefe('Musterbeitrag: noindex, Seite statt NewsArticle', [$k['index'], $k['typ'], str_ends_with($k['url'], '/unternehmen/muster-immobilien/grundrisse-lesen/')], [false, 'seite', true]);
$GLOBALS['qv'] = ['ma_firma' => 'unbekannt'];
unset($GLOBALS['ma21_firmen_route']);
pruefe('Unbekannte Adresse ändert nichts', ma21_firmen_seo($basis), $basis);
$GLOBALS['qv'] = [];

echo "\nUnternehmen-Menü\n";
$d = ma21_ressort_menue_daten(['ressorts' => ['/unternehmen/' => ['titel' => 'Unternehmen', 'werbung' => true, 'gruppen' => [['titel' => 'Für Unternehmen', 'links' => [['Werben', '/werben/']]]], 'neu' => []]]]);
$u = $d['ressorts']['/unternehmen/'];
pruefe('Keine Anzeige unter „Für Unternehmen“', array_key_exists('werbung', $u), false);
pruefe('Fünf Firmen mit je drei Beiträgen', [count($u['firmen']), array_sum(array_map(fn($f) => count($f['beitraege']), $u['firmen']))], [5, 15]);
pruefe('Firmen und Beiträge zeigen auf ihre Seiten', count(array_filter($u['firmen'], fn($f) => preg_match('#^/unternehmen/muster-[a-z\-]+/$#', $f['url']) && !array_filter($f['beitraege'], fn($b) => !str_starts_with($b['url'], $f['url'])))), 5);
pruefe('Beitragsbilder in kleiner Fassung mit Nachweis', count(array_filter(array_merge(...array_map(fn($f) => $f['beitraege'], $u['firmen'])), fn($b) => str_ends_with($b['bild']['src'], '-720.jpg') && str_starts_with($b['bild']['credit'], 'Foto: '))), 15);
pruefe('Rechts bleiben die Firmenbeiträge (vier Musterbeiträge)', [count($u['neu']), count(array_filter($u['neu'], fn($n) => !empty($n['muster']) && str_starts_with($n['url'], '/unternehmen/muster-')))], [4, 4]);

echo "\nStatisches Menü (deploy/ressort-menue.mjs) wie WordPress\n";
$statisch = json_decode((string) file_get_contents($wurzel . '/chatgpt-site/assets/ressort-menue.json'), true)['ressorts']['/unternehmen/'] ?? [];
pruefe('Ohne Anzeige, mit denselben Firmen und Beitragsadressen', [array_key_exists('werbung', $statisch), array_column($statisch['firmen'] ?? [], 'url'), array_merge([], ...array_map(fn($f) => array_column($f['beitraege'] ?? [], 'url'), $statisch['firmen'] ?? []))],
    [false, array_column($u['firmen'], 'url'), array_merge([], ...array_map(fn($f) => array_column($f['beitraege'], 'url'), $u['firmen']))]);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
