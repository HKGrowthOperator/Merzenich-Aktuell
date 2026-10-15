<?php
/**
 * Unternehmenskanäle im Theme (21.18.0/21.19.0, inc/unternehmen.php) ohne WordPress:
 * drei Beispielfirmen (Wunsch Betreiber 15.10.2026) vollständig, geprüft und gekennzeichnet, jeder Musterbeitrag mit eigener
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

echo "Beispielfirmen (inc/unternehmen-muster.json)\n";
$roh = ma21_muster_firmen();
$bilder = ma21_werben_bilder();
pruefe('Drei Beispielfirmen', [count($roh), array_column($roh, 'slug')], [3, ['backhaus-morgenrot', 'cafe-feldlerche', 'holzwerkstatt-jahresring']]);
pruefe('Slugs eindeutig', count(array_unique(array_column($roh, 'slug'))), 3);
pruefe('Jeder Name ist gegen echte Betriebe geprüft (Datum, Quellen, Ergebnis)', count(array_filter($roh, fn($m) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $m['pruefung']['datum'] ?? '') && count($m['pruefung']['quellen'] ?? []) >= 2 && ($m['pruefung']['ergebnis'] ?? '') !== '')), 3);
// Alle Texte der Firma ohne Adressen (slug) und Prüfprotokoll: keine Uhrzeiten, Wochentage,
// Adressen, Telefon- oder Mailwege, keine Bewerbungsaufrufe (Review 15.10.2026).
$texte = function ($x) use (&$texte): array { if (!is_array($x)) return is_string($x) ? [$x] : []; $l = []; foreach ($x as $k => $v) if ($k !== 'slug' && $k !== 'pruefung') $l = array_merge($l, $texte($v)); return $l; };
$kontakt = '/(straße|str\.|platz \d|\b0\d{3,}[\s\/-]?\d|\b\d{1,2}([:.]\d{2})?\s*uhr\b|\b(mo|di|mi|do|fr|sa|so)\.?\s*(bis|[–-])|\b(montag|dienstag|mittwoch|donnerstag|freitag|samstag|sonntag)(s|abend|morgen)?\b|telefon|anruf|e-?mail|@|lebenslauf|bewerb|\b\d{5}\s+\p{Lu})/iu';
$treffer = array_values(array_filter(array_merge(...array_map($texte, $roh)), fn($t) => preg_match($kontakt, $t)));
pruefe('Keine Öffnungszeiten, Uhrzeiten, Adressen, Telefon- oder Mailwege (niemand sucht ein echtes Geschäft)', [count(array_filter($roh, fn($m) => isset($m['info']))), $treffer], [0, []]);
pruefe('Die Prüfung erkennt solche Angaben (Gegenprobe)', [preg_match($kontakt, 'Frische Brötchen ab 6 Uhr'), preg_match($kontakt, 'sonntags abholbereit'), preg_match($kontakt, 'im Laden oder telefonisch'), preg_match($kontakt, 'Mo bis Sa'), preg_match($kontakt, 'Frische Brötchen am Morgen')], [1, 1, 1, 1, 0]);
pruefe('Orte sind Ortsteile der Gemeinde', count(array_filter($roh, fn($m) => isset(MA21_ORTE[$m['ort']]))), 3);
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
pruefe('Kanal einer Beispielfirma', ma21_firmen_route('backhaus-morgenrot')['art'], 'kanal');
$r = ma21_firmen_route('backhaus-morgenrot', 'roggenbroetchen-aus-dem-steinofen');
pruefe('Musterbeitrag mit eigener Seite', [$r['art'], $r['nr'] ?? null], ['beitrag', 1]);
pruefe('Unbekannter Beitrag: 404', ma21_firmen_route('backhaus-morgenrot', 'gibt-es-nicht')['art'], '404');
pruefe('Alte Musteradressen gibt es nicht mehr: 404', ma21_firmen_route('muster-backstube')['art'], '404');
pruefe('Unbekannte Firma: 404', ma21_firmen_route('unbekannt')['art'], '404');
pruefe('Reservierte Namen (page, feed): 404', [ma21_firmen_route('page')['art'], ma21_firmen_route('feed')['art']], ['404', '404']);

echo "\nÜbersicht /unternehmen/ und Kasten Musterprofile\n";
$html = ma21_musterbeitraege_html(6);
preg_match_all('#<article class="bildraster-karte[^"]*"#', $html, $karten);
pruefe('Sechs Musterbeiträge als Karten', count($karten[0]), 6);
pruefe('Jede Karte trägt „Musterbeitrag“', substr_count($html, '<span class="badge">Musterbeitrag</span>'), 6);
pruefe('Karten führen auf Beitragsseiten, nie auf das Anfrageformular', [str_contains($html, '/anzeigen/aufgeben/'), preg_match_all('#href="https://merzenich-aktuell\.de/unternehmen/(?:backhaus-morgenrot|cafe-feldlerche|holzwerkstatt-jahresring)/[a-z0-9\-]+/"#', $html)], [false, 12]);
pruefe('Firma und Ort in jeder Karte', substr_count($html, 'Musterbeitrag · <a href="https://merzenich-aktuell.de/unternehmen/'), 6);
pruefe('Hinweis: keine echten Unternehmen', str_contains($html, 'keine echten Unternehmen'), true);
pruefe('Testanzeige unter den Karten, gekennzeichnet', [substr_count($html, '<span class="u-testanzeige__label">Testanzeige</span>'), str_contains($html, 'kein echtes Unternehmen')], [1, true]);
$kasten = ma21_musterprofile_html(0, 'https://merzenich-aktuell.de/anzeigen/aufgeben/?art=Werbung');
pruefe('Beispielfirmen führen in ihren Kanal, jede gekennzeichnet', [substr_count($kasten, 'class="u-muster__link" href="https://merzenich-aktuell.de/unternehmen/'), substr_count($kasten, 'Beispielfirma</span>'), str_contains($kasten, 'u-muster__info')], [3, 3, false]);
pruefe('Ab drei echten Profilen kein Kasten', ma21_musterprofile_html(3, ''), '');
$kacheln = ma21_firmen_kacheln_html(ma21_alle_firmen());
pruefe('Kacheln der Unternehmenskanäle', [substr_count($kacheln, 'class="u-kanal"'), substr_count($kacheln, '<em>Beispielfirma</em>')], [3, 3]);

echo "\nSeite Musterbeitrag\n";
$GLOBALS['qv'] = ['ma_firma' => 'cafe-feldlerche', 'ma_firmenbeitrag' => 'torten-aus-eigener-herstellung'];
unset($GLOBALS['ma21_firmen_route']);
ob_start(); include $wurzel . '/wordpress/theme/merzenich-aktuell/unternehmen-beitrag.php'; $seite = ob_get_clean();
pruefe('Überschrift, Anriss und Absätze', [str_contains($seite, '<h1>Torten aus eigener Herstellung, auch auf Bestellung</h1>'), str_contains($seite, 'class="dek"'), substr_count($seite, '<p>Jeden Morgen') + substr_count($seite, '<p>Für Geburtstage') + substr_count($seite, '<p>Wer lieber')], [true, true, 3]);
pruefe('Kennzeichnung Musterbeitrag oben und unten', [str_contains($seite, '<span class="gesponsert">Musterbeitrag</span>'), str_contains($seite, 'Musterbeitrag von Café Feldlerche')], [true, true]);
pruefe('Bild mit Nachweis und Lizenzlink', [str_contains($seite, '<figure class="art-figure'), str_contains($seite, 'rel="noopener license"'), str_contains($seite, 'Wikimedia Commons')], [true, true, true]);
pruefe('Testanzeige der Firma und Weg in den Kanal', [str_contains($seite, 'Testanzeige'), str_contains($seite, 'href="https://merzenich-aktuell.de/unternehmen/cafe-feldlerche/">Zum Unternehmenskanal')], [true, true]);
pruefe('Keine Teilen-Knöpfe, keine Kommentare', [str_contains($seite, 'api.whatsapp.com'), str_contains($seite, 'comments')], [false, false]);

echo "\nSeite Kanal\n";
$GLOBALS['qv'] = ['ma_firma' => 'holzwerkstatt-jahresring'];
unset($GLOBALS['ma21_firmen_route']);
ob_start(); include $wurzel . '/wordpress/theme/merzenich-aktuell/unternehmen-kanal.php'; $kanal = ob_get_clean();
pruefe('Name, Zeile und Hinweis Beispielfirma', [str_contains($kanal, '<h1>Holzwerkstatt Jahresring</h1>'), str_contains($kanal, 'Handwerk · Girbelsrath'), str_contains($kanal, '<strong>Beispielfirma.</strong> Dieses Unternehmen ist ausgedacht')], [true, true, true]);
pruefe('Alle drei Beiträge als Karten', substr_count($kanal, 'class="bildraster-karte u-firmen-karte'), 3);
pruefe('Testanzeige und weitere Kanäle', [str_contains($kanal, 'Testanzeige'), substr_count($kanal, 'class="u-kanal"')], [true, 2]);

echo "\nSuchmaschinen\n";
$basis = ['typ' => 'liste', 'titel' => '', 'beschreibung' => '', 'url' => '', 'index' => true, 'og' => 'website', 'krumen' => [], 'bild' => ['url' => '', 'w' => 0, 'h' => 0], 'liste' => []];
$k = ma21_firmen_seo($basis);
pruefe('Kanal einer Beispielfirma: noindex, eigene Adresse, Titel', [$k['index'], $k['url'], $k['titel']], [false, 'https://merzenich-aktuell.de/unternehmen/holzwerkstatt-jahresring/', 'Holzwerkstatt Jahresring (Beispielfirma) · Unternehmenskanal']);
$GLOBALS['qv'] = ['ma_firma' => 'cafe-feldlerche', 'ma_firmenbeitrag' => 'suppe-der-woche'];
unset($GLOBALS['ma21_firmen_route']);
$k = ma21_firmen_seo($basis);
pruefe('Musterbeitrag: noindex, Seite statt NewsArticle', [$k['index'], $k['typ'], str_ends_with($k['url'], '/unternehmen/cafe-feldlerche/suppe-der-woche/')], [false, 'seite', true]);
$GLOBALS['qv'] = ['ma_firma' => 'unbekannt'];
unset($GLOBALS['ma21_firmen_route']);
pruefe('Unbekannte Adresse ändert nichts', ma21_firmen_seo($basis), $basis);
$GLOBALS['qv'] = [];

echo "\nBildadressen und Abfragewerte\n";
pruefe('Musterbild (relativ) bekommt die Domain', ma21_abs('/assets/werben/x-720.jpg'), 'https://merzenich-aktuell.de/assets/werben/x-720.jpg');
pruefe('Echtes Bild (absolut) bleibt, wie es ist', ma21_abs('https://merzenich-aktuell.de/wp-content/uploads/a.webp'), 'https://merzenich-aktuell.de/wp-content/uploads/a.webp');
$echt = ma21_firmen_bild_html(['src' => 'https://merzenich-aktuell.de/wp-content/uploads/a.webp', 'klein' => 'https://merzenich-aktuell.de/wp-content/uploads/a-480x360.webp', 'srcset' => 'https://merzenich-aktuell.de/wp-content/uploads/a-480x360.webp 480w', 'w' => 1600, 'h' => 1200, 'alt' => 'A'], '100vw');
pruefe('Echtes Kartenbild: keine doppelte Domain, srcset und Maße aus der Mediathek', [substr_count($echt, 'https://merzenich-aktuell.de/https'), str_contains($echt, ' 480w"'), str_contains($echt, 'width="1600" height="1200"')], [0, true, true]);
$GLOBALS['qv'] = ['ma_firma' => ['x'], 'ma_firmenbeitrag' => ['y']];
unset($GLOBALS['ma21_firmen_route']);
pruefe('?ma_firma[]=… gilt als leer (keine Warnung, keine Route)', [ma21_qv('ma_firma'), ma21_firmen_anfrage()], ['', null]);
$GLOBALS['qv'] = [];
unset($GLOBALS['ma21_firmen_route']);

echo "\nUnternehmen-Menü\n";
$d = ma21_ressort_menue_daten(['ressorts' => ['/unternehmen/' => ['titel' => 'Unternehmen', 'werbung' => true, 'punkte' => [['titel' => 'Für Unternehmen', 'href' => '/werben/']], 'neu' => []]]]);
$u = $d['ressorts']['/unternehmen/'];
pruefe('Keine Anzeige unter „Für Unternehmen“', array_key_exists('werbung', $u), false);
pruefe('Rote Titel bleiben, keine Unterpunkte', [$u['punkte'], array_key_exists('gruppen', $u)], [[['titel' => 'Für Unternehmen', 'href' => '/werben/']], false]);
pruefe('Drei Firmen mit je drei Beiträgen, als Beispielfirma markiert', [count($u['firmen']), array_sum(array_map(fn($f) => count($f['beitraege']), $u['firmen'])), count(array_filter($u['firmen'], fn($f) => $f['muster'] === true))], [3, 9, 3]);
pruefe('Firmen und Beiträge zeigen auf ihre Seiten', count(array_filter($u['firmen'], fn($f) => preg_match('#^/unternehmen/[a-z\-]+/$#', $f['url']) && !array_filter($f['beitraege'], fn($b) => !str_starts_with($b['url'], $f['url'])))), 3);
pruefe('Beitragsbilder in kleiner Fassung mit Nachweis', count(array_filter(array_merge(...array_map(fn($f) => $f['beitraege'], $u['firmen'])), fn($b) => str_ends_with($b['bild']['src'], '-720.jpg') && $b['bild']['foto'] !== '' && $b['bild']['lizenz'] !== '' && empty($b['gesponsert']))), 9);
pruefe('„Neu von Unternehmen“ mit Bildnachweis', count(array_filter($u['neu'], fn($n) => !empty($n['bild']['foto']) && !empty($n['bild']['lizenz']))), 4);
pruefe('Rechts bleiben die Firmenbeiträge (vier Musterbeiträge)', [count($u['neu']), count(array_filter($u['neu'], fn($n) => !empty($n['muster']) && preg_match('#^/unternehmen/(?:backhaus-morgenrot|cafe-feldlerche|holzwerkstatt-jahresring)/#', $n['url'])))], [4, 4]);

echo "\nStatisches Menü (deploy/ressort-menue.mjs) wie WordPress\n";
$statisch = json_decode((string) file_get_contents($wurzel . '/chatgpt-site/assets/ressort-menue.json'), true)['ressorts']['/unternehmen/'] ?? [];
pruefe('Ohne Anzeige, mit denselben Firmen und Beitragsadressen', [array_key_exists('werbung', $statisch), array_column($statisch['firmen'] ?? [], 'url'), array_merge([], ...array_map(fn($f) => array_column($f['beitraege'] ?? [], 'url'), $statisch['firmen'] ?? []))],
    [false, array_column($u['firmen'], 'url'), array_merge([], ...array_map(fn($f) => array_column($f['beitraege'], 'url'), $u['firmen']))]);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
