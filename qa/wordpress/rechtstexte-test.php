<?php
/**
 * Rechtstexte (includes/rechtstexte.php) ohne WordPress: anlegen, aktualisieren,
 * manuelle Änderung schützen, Fassung ausdrücklich einspielen.
 * Aufruf: php qa/wordpress/rechtstexte-test.php
 */
define('ABSPATH', __DIR__); define('OBJECT', 'OBJECT');
class WP_Post { public $ID; public $post_type = 'page'; public $post_status = 'publish'; public $post_name = ''; public $post_title = ''; public $post_content = ''; public $post_excerpt = '';
    function __construct(int $id, array $a = []) { $this->ID = $id; foreach ($a as $k => $v) $this->$k = $v; } }
class WP_Error {}
$GLOBALS['opt'] = []; $GLOBALS['posts'] = []; $GLOBALS['meta'] = []; $GLOBALS['naechste'] = 10;
function add_action(...$a) {} function add_filter(...$a) {}
function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function update_option($k, $v, $a = null) { $GLOBALS['opt'][$k] = $v; return true; }
function get_page_by_path($slug, $o = null, $t = 'page') { foreach ($GLOBALS['posts'] as $p) if ($p->post_name === $slug && $p->post_type === $t) return $p; return null; }
function wp_insert_post(array $d, $e = false) { $id = $GLOBALS['naechste']++; $GLOBALS['posts'][$id] = new WP_Post($id, $d); return $id; }
function wp_update_post(array $d, $e = false) { $p = $GLOBALS['posts'][$d['ID']]; foreach ($d as $k => $v) if ($k !== 'ID') $p->$k = $v; return $d['ID']; }
function update_post_meta($id, $k, $v) { $GLOBALS['meta'][$id][$k] = $v; return true; }
function current_user_can($c) { return true; }

require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/rechtstexte.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-62s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Inhalt\n";
$ds = ma_rechtstext_datenschutz(); $im = ma_rechtstext_impressum();
pruefe('Impressum nennt KBS Management GmbH und HRB 104692', str_contains($im, 'KBS Management GmbH') && str_contains($im, 'HRB 104692'), true);
pruefe('Impressum ohne erfundene Telefonnummer', preg_match('/Telefon|Tel\./', $im), 0);
pruefe('Impressum: verantwortliche Person nach § 18 MStV mit Name und Anschrift (Vorgabe Betreiber 06.10.2026)', str_contains($im, '§ 18 Abs. 2 MStV') && str_contains($im, 'Anto-Sutharsan Jesuthasan<br>Rheinstr. 78a<br>51371 Leverkusen') && !str_contains($im, 'sobald sie') && !str_contains($im, 'TODO'), true);
pruefe('Datenschutz: Besucherzählung mit Tages-Hash und 40 Tagen', str_contains($ds, '40 Tagen') && str_contains($ds, 'Prüfwert (Hash)'), true);
pruefe('Datenschutz: Anzeigenzählung, Wetter, Kommentare ohne IP', str_contains($ds, 'angeklickt') && str_contains($ds, 'Open-Meteo') && str_contains($ds, 'IP-Adresse wird beim Kommentieren nicht gespeichert'), true);
pruefe('Datenschutz: Hoster IONOS, fehlende Dateien vom eigenen Server, keine Vorschauseite mehr', [str_contains($ds, 'IONOS SE'), str_contains($ds, 'Ihr Browser verbindet sich dabei nicht mit GitHub'), str_contains($ds, 'hk-growthoperator')], [true, true, false]);
pruefe('Kein TODO auf den öffentlichen Seiten', str_contains($ds . $im, 'TODO') || str_contains($ds . $im, '[…]'), false);
pruefe('Offene Pflichtangaben nur im Backend (2 Punkte: AV-Vertrag, Mailversand)', count(ma_rechtstexte_offen()), 2);

echo "\nAnlegen und Pflegen\n";
pruefe('Status vor dem Anlegen: fehlt', ma_rechtstext_status('impressum'), 'fehlt');
ma_rechtstexte_abgleichen();
pruefe('Beide Seiten angelegt', [ma_rechtstext_status('impressum'), ma_rechtstext_status('datenschutz')], ['aktuell', 'aktuell']);
$p = ma_rechtstext_seite('datenschutz');
pruefe('Seite veröffentlicht, Slug, Kommentare zu', [$p->post_status, $p->post_name, $p->comment_status], ['publish', 'datenschutz', 'closed']);
pruefe('Eyebrow „Rechtliches“', $GLOBALS['meta'][$p->ID]['ma_eyebrow'] ?? '', 'Rechtliches');
pruefe('Guard gesetzt', get_option('ma_rechtstexte_geprueft'), MA_RECHTSTEXTE_VERSION);
$p->post_content .= '<p>Eigener Zusatz der Redaktion.</p>';
pruefe('Manuell geändert erkannt', ma_rechtstext_status('datenschutz'), 'manuell');
$GLOBALS['opt']['ma_rechtstexte_stand']['datenschutz']['version'] = '2026-01-01'; unset($GLOBALS['opt']['ma_rechtstexte_geprueft']);
ma_rechtstexte_abgleichen();
pruefe('Abgleich lässt manuelle Änderung stehen', str_contains(ma_rechtstext_seite('datenschutz')->post_content, 'Eigener Zusatz'), true);
ma_rechtstext_schreiben('datenschutz');
pruefe('Ausdrückliches Einspielen ersetzt den Text', [ma_rechtstext_status('datenschutz'), str_contains(ma_rechtstext_seite('datenschutz')->post_content, 'Eigener Zusatz')], ['aktuell', false]);
$GLOBALS['opt']['ma_rechtstexte_stand']['impressum']['version'] = '2026-01-01'; unset($GLOBALS['opt']['ma_rechtstexte_geprueft']);
pruefe('Ältere Fassung erkannt', ma_rechtstext_status('impressum'), 'veraltet');
ma_rechtstexte_abgleichen();
pruefe('Unveränderte ältere Fassung wird gehoben', ma_rechtstext_status('impressum'), 'aktuell');

echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
