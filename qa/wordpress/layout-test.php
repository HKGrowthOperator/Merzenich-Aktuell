<?php
/**
 * Layout-Karte (includes/layout.php) und Box „Startseite“ (includes/startplatz.php) ohne WordPress.
 * Aufruf: php qa/wordpress/layout-test.php
 */
define('ABSPATH', __DIR__);
define('MA_CORE_VERSION', 'test');

class WP_Post { public $ID; public $post_type = 'post'; public $post_status = 'publish'; public $post_title = ''; public $post_date_gmt = '2026-10-01 10:00:00'; public $post_modified = '2026-10-01 10:00:00';
    function __construct(int $id, array $a = []) { $this->ID = $id; $this->post_title = "Meldung $id"; foreach ($a as $k => $v) $this->$k = $v; } }
class WP_Error { public $code; public $message; public $data; function __construct($c = '', $m = '', $d = null) { $this->code = $c; $this->message = $m; $this->data = $d; } function get_error_code() { return $this->code; } function get_error_message() { return $this->message; } }
function is_wp_error($x) { return $x instanceof WP_Error; }
$GLOBALS['opt'] = []; $GLOBALS['meta'] = []; $GLOBALS['posts'] = []; $GLOBALS['cats'] = []; $GLOBALS['bilder'] = []; $GLOBALS['verlauf'] = []; $GLOBALS['actions'] = [];
function add_action($h, $f, ...$a) { $GLOBALS['actions'][$h][] = $f; } function add_filter(...$a) {} function do_action(...$a) {}
function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function update_option($k, $v, $a = null) { $GLOBALS['opt'][$k] = $v; return true; }
function delete_option($k) { unset($GLOBALS['opt'][$k]); return true; }
function get_post_meta($id, $k, $s = false) { return $GLOBALS['meta'][$id][$k] ?? ''; }
function update_post_meta($id, $k, $v) { $GLOBALS['meta'][$id][$k] = $v; return true; }
function delete_post_meta($id, $k) { unset($GLOBALS['meta'][$id][$k]); return true; }
function get_post($id) { return $GLOBALS['posts'][(int) $id] ?? null; }
function get_post_type($id) { return $GLOBALS['posts'][(int) $id]->post_type ?? false; }
function get_post_status($id) { return $GLOBALS['posts'][(int) $id]->post_status ?? false; }
function get_the_title($id) { return $GLOBALS['posts'][(int) $id]->post_title ?? ''; }
function get_post_thumbnail_id($id) { return (int) ($GLOBALS['meta'][$id]['_thumbnail_id'] ?? 0); }
function wp_get_attachment_image_src($bild, $g) { return $GLOBALS['bilder'][$bild] ?? false; }
function has_category($slug, $id) { return in_array($slug, $GLOBALS['cats'][$id] ?? [], true); }
function get_current_user_id() { return 7; }
function get_posts(array $a) {
    $raus = [];
    foreach ($GLOBALS['posts'] as $p) {
        if ($p->post_type !== ($a['post_type'] ?? 'post')) continue;
        $st = $a['post_status'] ?? 'publish';
        if ($st !== 'any' && !in_array($p->post_status, (array) $st, true)) continue;
        if (!empty($a['post__not_in']) && in_array($p->ID, $a['post__not_in'], true)) continue;
        if (!empty($a['meta_key'])) {
            $v = $GLOBALS['meta'][$p->ID][$a['meta_key']] ?? null;
            $soll = $a['meta_value'];
            if (($a['meta_compare'] ?? '=') === 'IN') { if (!in_array($v, (array) $soll, true)) continue; }
            elseif ($v !== $soll) continue;
        }
        $raus[] = $p;
    }
    if (($a['orderby'] ?? '') === 'modified') usort($raus, fn($x, $y) => strcmp($y->post_modified, $x->post_modified));
    $n = (int) ($a['posts_per_page'] ?? -1);
    if ($n > 0) $raus = array_slice($raus, 0, $n);
    return ($a['fields'] ?? '') === 'ids' ? array_map(fn($p) => $p->ID, $raus) : $raus;
}
function ma_verlauf_eintragen(int $id, string $a, string $n = '', ?int $u = null): void { $GLOBALS['verlauf'][$id][] = $a; }

require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/layout.php';
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/startplatz.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-66s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
function meldung(int $id, array $cats = [], ?array $bild = null, array $a = []): WP_Post {
    $p = new WP_Post($id, $a); $GLOBALS['posts'][$id] = $p; $GLOBALS['cats'][$id] = $cats;
    if ($bild) { $GLOBALS['meta'][$id]['_thumbnail_id'] = $id * 10; $GLOBALS['bilder'][$id * 10] = [$bild['src'] ?? "/b$id.jpg", $bild['w'] ?? 1200, $bild['h'] ?? 800]; $GLOBALS['meta'][$id * 10]['_wp_attachment_image_alt'] = $bild['alt'] ?? "Bild $id"; }
    return $p;
}
$GLOBALS['opt']['ma_layout_migration'] = '1';

echo "Plätze\n";
$s = ma_layout_slots('startseite');
pruefe('Startseite kennt Aufmacher, Bühne 4, Blaulicht groß 1, Gemeinde Zeile 4', isset($s['aufmacher'], $s['buehne-4'], $s['blaulicht.gross.1'], $s['gemeinde.zeilen.4']), true);
pruefe('Startseite hat 5 + 5 × 9 Plätze', count($s), 50);
pruefe('Sport hat Bildraster 6', isset(ma_layout_slots('ressort-sport')['raster.6']), true);
pruefe('Blaulicht hat kein Bildraster', isset(ma_layout_slots('ressort-blaulicht')['raster.1']), false);
pruefe('Unbekannte Seite: keine Plätze', ma_layout_slots('ressort-tipp'), []);
pruefe('Struktur der Startseite beginnt mit der Bühne', ma_layout_struktur('startseite')[0]['id'], 'oben');

echo "\nSetzen, Doppeln, Konflikt\n";
for ($i = 1; $i <= 12; $i++) meldung($i, [$i % 3 === 0 ? 'blaulicht' : ($i % 4 === 0 ? 'rathaus' : 'leben')], ['w' => 1200, 'h' => 800]);
meldung(13, ['sport'], ['w' => 1200, 'h' => 800]);
meldung(14, ['leben'], null, ['post_status' => 'draft']);
meldung(15, ['leben']); // ohne Bild
$e = ma_layout_set('startseite', 'aufmacher', 14);
pruefe('Entwurf: Rückfrage „vormerken?“ statt Einsetzen', is_wp_error($e) ? $e->get_error_code() : 'ok', 'ma_layout_entwurf');
$k = ma_layout_set('startseite', 'aufmacher', 1);
pruefe('Aufmacher gesetzt', $k['slots']['aufmacher']['post'] ?? 0, 1);
pruefe('Meta ma_startplatz gespiegelt', get_post_meta(1, 'ma_startplatz', true), 'aufmacher');
pruefe('rev zählt', $k['rev'], 1);
$k = ma_layout_set('startseite', 'aufmacher', 2);
pruefe('Neuer Aufmacher verdrängt den alten', $k['slots']['aufmacher']['post'], 2);
pruefe('Alter Aufmacher wieder automatisch (Meta)', get_post_meta(1, 'ma_startplatz', true), 'auto');
$e = ma_layout_set('startseite', 'blaulicht.gross.1', 2);
pruefe('Zweiter Platz ohne „doppelt“ abgelehnt', is_wp_error($e) ? $e->get_error_code() : 'ok', 'ma_layout_doppelt');
$k = ma_layout_set('startseite', 'blaulicht.gross.1', 2, ['doppelt' => true]);
pruefe('Mit „doppelt“ erlaubt, Flag gespeichert', $k['slots']['blaulicht.gross.1'] ?? null, ['post' => 2, 'doppelt' => true]);
$e = ma_layout_set('startseite', 'buehne-1', 3, ['rev' => 1]);
pruefe('Veraltete rev → Konflikt 409', is_wp_error($e) ? ($e->data['status'] ?? 0) : 0, 409);
$k = ma_layout_set('startseite', 'buehne-1', 3, ['rev' => $k['rev']]);
pruefe('Aktuelle rev → gespeichert', $k['slots']['buehne-1']['post'] ?? 0, 3);
pruefe('Bühne-Meta gespiegelt', get_post_meta(3, 'ma_startplatz', true), 'buehne-1');
pruefe('Verlauf geschrieben', end($GLOBALS['verlauf'][3]), 'Fester Platz: Startseite: Bühne 1 (rechts oben)');
$k = ma_layout_tauschen('startseite', 'buehne-1', 'buehne-3');
pruefe('Verschieben auf leeren Platz', [$k['slots']['buehne-3']['post'] ?? 0, isset($k['slots']['buehne-1'])], [3, false]);
pruefe('Meta folgt dem Platz', get_post_meta(3, 'ma_startplatz', true), 'buehne-3');
$k = ma_layout_tauschen('startseite', 'aufmacher', 'buehne-3');
pruefe('Tausch zweier fester Plätze', [$k['slots']['aufmacher']['post'], $k['slots']['buehne-3']['post']], [3, 2]);
$k = ma_layout_tauschen('startseite', 'buehne-2', 'buehne-4', 0, 5);
pruefe('Automatisch belegter Quellplatz: Beitrag wird fest am Ziel', [$k['slots']['buehne-4']['post'] ?? 0, isset($k['slots']['buehne-2'])], [5, false]);
$k = ma_layout_entfernen('startseite', 'buehne-4');
pruefe('Platz freigeben', isset($k['slots']['buehne-4']), false);
pruefe('Meta nach Freigabe automatisch', get_post_meta(5, 'ma_startplatz', true), 'auto');
ma_layout_set('ressort-sport', 'lead', 13);
pruefe('Beitrag auf zwei Seiten', array_keys(ma_layout_plaetze_von(2)), ['startseite']);
ma_layout_post_entfernen(2);
pruefe('post_entfernen räumt alle Plätze', ma_layout_plaetze_von(2), []);
pruefe('Doppelt-Partner bleibt', ma_layout_get('startseite')['slots']['aufmacher']['post'] ?? 0, 3);
$k = ma_layout_ersetzen('startseite', ['aufmacher' => ['post' => 4], 'rathaus.mittel.2' => 8, 'buehne-9' => 1, 'buehne-2' => 14]);
pruefe('Ersetzen filtert ungültige Plätze, behält Vormerkungen (Entwurf)', array_keys($k['slots']), ['aufmacher', 'buehne-2', 'rathaus.mittel.2']);
pruefe('Vormerkung erscheint nicht, solange Entwurf', isset(ma_layout_feste_plaetze('startseite')['buehne-2']), false);
ma_layout_entfernen('startseite', 'buehne-2');
pruefe('feste_plaetze liefert nur platzierbare', ma_layout_feste_plaetze('startseite'), ['aufmacher' => 4, 'rathaus.mittel.2' => 8]);
$GLOBALS['posts'][8]->post_status = 'draft';
pruefe('Entwurf fällt aus feste_plaetze', ma_layout_feste_plaetze('startseite'), ['aufmacher' => 4]);
$GLOBALS['posts'][8]->post_status = 'publish';

echo "\nBox „Startseite“ (startplatz.php)\n";
ma_startplatz_setzen(6, 'buehne-1');
pruefe('setzen: Karte und Meta', [ma_layout_get('startseite')['slots']['buehne-1']['post'] ?? 0, get_post_meta(6, 'ma_startplatz', true)], [6, 'buehne-1']);
ma_startplatz_setzen(6, 'wirtschaft.gross.2');
pruefe('Rubrikplatz über die Box: alter Platz frei, Meta auto', [isset(ma_layout_get('startseite')['slots']['buehne-1']), ma_layout_get('startseite')['slots']['wirtschaft.gross.2']['post'] ?? 0, get_post_meta(6, 'ma_startplatz', true)], [false, 6, 'auto']);
pruefe('ma_startplatz_von findet den Rubrikplatz', ma_startplatz_von(6), 'wirtschaft.gross.2');
ma_startplatz_setzen(6, 'aus');
pruefe('„aus“: von der Karte und Meta aus', [ma_startplatz_von(6), isset(ma_layout_get('startseite')['slots']['wirtschaft.gross.2'])], ['aus', false]);
ma_startplatz_setzen(14, 'buehne-2');
pruefe('Entwurf merkt sich den Platz im Meta', [get_post_meta(14, 'ma_startplatz', true), isset(ma_layout_get('startseite')['slots']['buehne-2'])], ['buehne-2', false]);
$GLOBALS['posts'][14]->post_status = 'publish';
foreach ($GLOBALS['actions']['transition_post_status'] as $f) $f('publish', 'draft', $GLOBALS['posts'][14]);
pruefe('Beim Veröffentlichen kommt er auf den Platz', ma_layout_get('startseite')['slots']['buehne-2']['post'] ?? 0, 14);
foreach ($GLOBALS['actions']['transition_post_status'] as $f) $f('draft', 'publish', $GLOBALS['posts'][14]);
pruefe('Zurückgezogen: Platz wieder frei', isset(ma_layout_get('startseite')['slots']['buehne-2']), false);
pruefe('Inhaber eines Platzes', ma_startplatz_inhaber('aufmacher')->ID ?? 0, 4);

echo "\nAlle Meldungen einsetzbar (1.25.0)\n";
meldung(18, ['leben']); $GLOBALS['meta'][18]['ma_startplatz'] = 'aus';
$k = ma_layout_set('ressort-leben', 'lead', 18);
pruefe('„Nur in der Rubrik“ auf der eigenen Ressortseite einsetzbar', is_wp_error($k) ? $k->get_error_code() : ($k['slots']['lead']['post'] ?? 0), 18);
pruefe('… und dort auch sichtbar', ma_layout_feste_plaetze('ressort-leben')['lead'] ?? 0, 18);
$e = ma_layout_set('startseite', 'buehne-3', 18);
pruefe('Startseite: Rückfrage „freigeben?“', is_wp_error($e) ? $e->get_error_code() : 'ok', 'ma_layout_nur_rubrik');
$k = ma_layout_set('startseite', 'buehne-3', 18, ['freigeben' => true]);
pruefe('Mit Freigabe eingesetzt, Meta nicht mehr „aus“', [is_wp_error($k) ? 'fehler' : ($k['slots']['buehne-3']['post'] ?? 0), get_post_meta(18, 'ma_startplatz', true) !== 'aus'], [18, true]);
meldung(19, ['leben'], null, ['post_status' => 'pending']);
$k = ma_layout_set('ressort-leben', 'reihe.1', 19, ['vormerken' => true]);
pruefe('Wartende Meldung vorgemerkt, noch nicht sichtbar', [is_wp_error($k) ? 'fehler' : ($k['slots']['reihe.1']['post'] ?? 0), isset(ma_layout_feste_plaetze('ressort-leben')['reihe.1'])], [19, false]);
$GLOBALS['posts'][19]->post_status = 'publish';
pruefe('Nach der Freigabe steht sie auf dem Platz', ma_layout_feste_plaetze('ressort-leben')['reihe.1'] ?? 0, 19);
$GLOBALS['posts'][20] = (object) ['ID' => 20, 'post_type' => 'post', 'post_status' => 'ma_archiv'];
$e = ma_layout_set('ressort-leben', 'reihe.2', 20, ['vormerken' => true]);
pruefe('Archivierte Meldung bleibt gesperrt', is_wp_error($e) ? $e->get_error_code() : 'ok', 'ma_layout_beitrag');

echo "\nWarnungen\n";
pruefe('Sport fest auf dem Aufmacher: Warnung sport', ma_layout_warnungen('startseite', 'aufmacher', 13), ['sport']);
pruefe('Ohne Bild auf groß: kein-bild', ma_layout_warnungen('startseite', 'blaulicht.gross.2', 15), ['kein-bild']);
pruefe('Ohne Bild in einer Zeile: keine Warnung', ma_layout_warnungen('startseite', 'blaulicht.zeilen.1', 15), []);
meldung(16, ['leben'], ['w' => 300, 'h' => 200, 'alt' => 'Wappen Merzenich']);
pruefe('Logo-Motiv', ma_layout_warnungen('startseite', 'buehne-1', 16), ['logo-motiv']);
meldung(17, ['leben'], ['w' => 300, 'h' => 200]);
pruefe('Bild zu klein für die Bühne', ma_layout_warnungen('startseite', 'buehne-1', 17), ['bild-klein']);
pruefe('Fremdes Ressort auf der Sportseite', ma_layout_warnungen('ressort-sport', 'lead', 17), ['nicht-im-ressort']);
pruefe('Alle Meldungen: kein Ressortzwang', ma_layout_warnungen('ressort-nachrichten', 'lead', 17), []);

echo "\nAuflösung Startseite\n";
$GLOBALS['opt'] = ['ma_layout_migration' => '1']; $GLOBALS['meta'] = []; $GLOBALS['posts'] = []; $GLOBALS['cats'] = []; $GLOBALS['bilder'] = [];
$alle = [];
for ($i = 1; $i <= 30; $i++) { $cat = ['blaulicht', 'rathaus', 'wirtschaft', 'vereine', 'leben'][$i % 5]; $alle[] = meldung($i, [$cat], ['w' => 1200, 'h' => 800]); }
$alle[] = meldung(31, ['sport'], ['w' => 1200, 'h' => 800]);
$alle[] = meldung(32, ['leben']); // ohne Bild
$regeln = [
    'buehnenTauglich' => fn($p) => !has_category('sport', $p->ID) && get_post_thumbnail_id($p->ID) > 0,
    'breite' => fn($p) => $GLOBALS['bilder'][get_post_thumbnail_id($p->ID)][1] ?? 0,
    'motiv' => fn($p) => get_post_thumbnail_id($p->ID), 'bildtyp' => fn($p) => '', 'echtesBild' => fn($p) => get_post_thumbnail_id($p->ID) > 0,
    'ressort' => fn($p) => $GLOBALS['cats'][$p->ID][0], 'kategorie' => fn($p, $s) => has_category($s, $p->ID), 'holen' => fn($id) => get_post($id),
    'sektionen' => [
        'blaulicht' => ['titel' => 'Blaulicht', 'nimm' => fn($p) => has_category('blaulicht', $p->ID)],
        'rathaus' => ['titel' => 'Rathaus', 'nimm' => fn($p) => has_category('rathaus', $p->ID)],
        'gemeinde' => ['titel' => 'Nachrichten', 'nimm' => fn($p) => !has_category('sport', $p->ID), 'jeRessort' => 3, 'fenster' => 44, 'zeilen' => 2],
    ],
];
$b = ma_layout_aufloesen('startseite', [], $alle, $regeln);
pruefe('Ohne feste Plätze: erster bühnentauglicher ist Aufmacher', $b['aufmacher']->ID, 1);
pruefe('Bühne voll (4 automatisch)', count(array_filter($b['neben'])), 4);
pruefe('Plätze-Liste meldet Automatik', $b['plaetze']['aufmacher'], ['post' => 1, 'fest' => false, 'warnungen' => []]);
pruefe('Blaulicht: 2 groß + 3 mittel', [count($b['sektionen']['blaulicht']['gross']), count($b['sektionen']['blaulicht']['mittel'])], [2, 3]);
$ids = []; foreach ($b['plaetze'] as $e) $ids[] = $e['post'];
pruefe('Keine Meldung zweimal', count($ids), count(array_unique($ids)));
$b = ma_layout_aufloesen('startseite', ['aufmacher' => 31, 'buehne-3' => 2], $alle, $regeln);
pruefe('Sportmeldung fest als Aufmacher trotz Regel', $b['aufmacher']->ID, 31);
pruefe('Warnung sport am Aufmacher', $b['plaetze']['aufmacher']['warnungen'], ['sport']);
pruefe('Bühne 3 fest, Bühne 1/2/4 automatisch, kein Rutschen', [$b['neben'][3]->ID, $b['plaetze']['buehne-1']['fest'], $b['plaetze']['buehne-3']['fest']], [2, false, true]);
$ids = []; foreach ($b['plaetze'] as $e) $ids[] = $e['post'];
pruefe('Feste Beiträge erscheinen nicht zusätzlich automatisch', count($ids), count(array_unique($ids)));
$b = ma_layout_aufloesen('startseite', ['blaulicht.gross.2' => 7, 'blaulicht.zeilen.1' => 32, 'gemeinde.zeilen.4' => 9], $alle, $regeln);
pruefe('Fester Platz groß 2 bleibt an Position 2', $b['sektionen']['blaulicht']['gross'][1]->ID ?? 0, 7);
pruefe('Groß 1 automatisch gefüllt', $b['plaetze']['blaulicht.gross.1']['fest'] ?? null, false);
pruefe('Meldung ohne Bild fest in Zeile 1', $b['sektionen']['blaulicht']['zeilen'][0]->ID ?? 0, 32);
pruefe('Gemeinde Zeile 4 fest: vier Zeilen', [count($b['sektionen']['gemeinde']['zeilen']), $b['sektionen']['gemeinde']['zeilen'][3]->ID], [4, 9]);
$wenig = array_slice($alle, 0, 6); // Blaulicht nur 5 und 10 → nicht genug für eine vollständige Reihe
$b = ma_layout_aufloesen('startseite', ['blaulicht.gross.1' => 5], $wenig, $regeln);
pruefe('Mit fester Karte wird die Reihe trotzdem ausgegeben', count($b['sektionen']['blaulicht']['gross']) >= 1 && $b['sektionen']['blaulicht']['gross'][0]->ID === 5, true);
pruefe('Warnung reihe-unvollstaendig', in_array('reihe-unvollstaendig', $b['plaetze']['blaulicht.gross.1']['warnungen'], true), true);
$b = ma_layout_aufloesen('startseite', [], $wenig, $regeln);
pruefe('Ohne feste Karte gilt die alte Vollständigkeitsregel', $b['sektionen']['blaulicht']['gross'], []);
$b = ma_layout_aufloesen('startseite', ['aufmacher' => 3, 'rathaus.mittel.1' => 3], $alle, $regeln);
pruefe('Doppelung auf zwei festen Plätzen', [$b['aufmacher']->ID, $b['sektionen']['rathaus']['mittel'][0]->ID], [3, 3]);
$b = ma_layout_aufloesen('startseite', ['aufmacher' => 999], $alle, $regeln);
pruefe('Unbekannter fester Beitrag: Platz automatisch', $b['aufmacher']->ID, 1);

echo "\nAuflösung Ressortseite\n";
$sport = []; for ($i = 41; $i <= 52; $i++) $sport[] = meldung($i, ['sport'], $i % 4 === 0 ? null : ['w' => 1000, 'h' => 700]);
$rr = ['raster' => true, 'echtesBild' => fn($p) => get_post_thumbnail_id($p->ID) > 0, 'holen' => fn($id) => get_post($id)];
$b = ma_layout_aufloesen('ressort-sport', [], $sport, $rr);
pruefe('Lead = erster Beitrag', $b['lead']->ID, 41);
pruefe('Raster: 6 Beiträge mit Bild', [count($b['raster']), array_map(fn($p) => $p->ID, $b['raster'])], [6, [42, 43, 45, 46, 47, 49]]);
pruefe('Reihen: Rest in Reihenfolge', array_map(fn($p) => $p->ID, $b['reihen']), [44, 48, 50, 51, 52]);
$b = ma_layout_aufloesen('ressort-sport', ['lead' => 7, 'raster.2' => 50, 'reihe.3' => 43], $sport, $rr);
pruefe('Fester Lead außerhalb der Liste geholt', $b['lead']->ID, 7);
pruefe('Warnung: nicht im Ressort', $b['plaetze']['lead']['warnungen'], ['nicht-im-ressort']);
pruefe('Raster 2 fest', $b['raster'][1]->ID, 50);
pruefe('Reihe 3 fest, davor automatisch', [$b['reihen'][2]->ID, $b['reihen'][0]->ID], [43, 44]);
$ids = array_map(fn($p) => $p->ID, array_merge([$b['lead']], $b['raster'], $b['reihen']));
pruefe('Keine Doppelung auf der Ressortseite', count($ids), count(array_unique($ids)));
$b = ma_layout_aufloesen('ressort-blaulicht', [], $sport, ['echtesBild' => fn($p) => true, 'holen' => fn($id) => get_post($id)]);
pruefe('Ohne Raster: alles in Reihen', [count($b['raster']), count($b['reihen'])], [0, 11]);

echo "\nÜbernahme bestehender Plätze\n";
$GLOBALS['opt'] = []; $GLOBALS['verlauf'] = [];
update_post_meta(5, 'ma_startplatz', 'aufmacher'); update_post_meta(6, 'ma_startplatz', 'buehne-2'); update_post_meta(31, 'ma_startplatz', 'buehne-3');
update_post_meta(9, 'ma_top_pinned', '1');
ma_layout_migrieren();
$k = ma_layout_get('startseite');
pruefe('Metas übernommen', [$k['slots']['aufmacher']['post'] ?? 0, $k['slots']['buehne-2']['post'] ?? 0, $k['slots']['buehne-3']['post'] ?? 0], [5, 6, 31]);
pruefe('Guard gesetzt', get_option('ma_layout_migration'), '1');
pruefe('Metas unangetastet', get_post_meta(31, 'ma_startplatz', true), 'buehne-3');
pruefe('Verlauf je übernommenem Platz', count($GLOBALS['verlauf'][5] ?? []), 1);
update_post_meta(5, 'ma_startplatz', 'buehne-4');
ma_layout_migrieren();
pruefe('Zweiter Lauf ändert nichts', ma_layout_get('startseite')['slots']['aufmacher']['post'], 5);
$GLOBALS['opt'] = []; $GLOBALS['meta'][5]['ma_startplatz'] = 'auto'; $GLOBALS['meta'][6]['ma_startplatz'] = 'auto'; $GLOBALS['meta'][31]['ma_startplatz'] = 'auto';
pruefe('Abgleich hatte die alte Fixierung neben dem Aufmacher zurückgesetzt', get_post_meta(9, 'ma_top_pinned', true), '0');
update_post_meta(9, 'ma_top_pinned', '1');
ma_layout_migrieren();
pruefe('Rückweg (Optionen gelöscht): ma_top_pinned wird Aufmacher', ma_layout_get('startseite')['slots']['aufmacher']['post'] ?? 0, 9);
$GLOBALS['meta'][9]['ma_top_until'] = '2020-01-01 00:00:00'; $GLOBALS['meta'][9]['ma_startplatz'] = 'auto'; $GLOBALS['opt'] = [];
ma_layout_migrieren();
pruefe('Abgelaufene Fixierung wird nicht übernommen', ma_layout_get('startseite')['slots'], []);

echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
