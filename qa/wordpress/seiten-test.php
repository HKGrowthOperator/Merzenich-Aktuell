<?php
/**
 * Redaktionsseiten (includes/seiten.php) ohne WordPress: Inhalte, Anlegen mit
 * Elternseite, Pflege wie die Rechtstexte.
 * Aufruf: php qa/wordpress/seiten-test.php
 */
define('ABSPATH', __DIR__); define('OBJECT', 'OBJECT');
class WP_Post { public $ID; public $post_type = 'page'; public $post_status = 'publish'; public $post_name = ''; public $post_title = ''; public $post_content = ''; public $post_excerpt = ''; public $post_parent = 0;
    function __construct(int $id, array $a = []) { $this->ID = $id; foreach ($a as $k => $v) $this->$k = $v; } }
class WP_Error {}
$GLOBALS['opt'] = []; $GLOBALS['posts'] = []; $GLOBALS['meta'] = []; $GLOBALS['naechste'] = 10;
function add_action(...$a) {} function add_filter(...$a) {} function add_shortcode(...$a) {}
function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function update_option($k, $v, $a = null) { $GLOBALS['opt'][$k] = $v; return true; }
function pfad_von(WP_Post $p): string { $t = $p->post_name; $e = $p->post_parent; while ($e && isset($GLOBALS['posts'][$e])) { $t = $GLOBALS['posts'][$e]->post_name . '/' . $t; $e = $GLOBALS['posts'][$e]->post_parent; } return $t; }
function get_page_by_path($pfad, $o = null, $t = 'page') { foreach ($GLOBALS['posts'] as $p) if ($p->post_type === $t && pfad_von($p) === $pfad) return $p; return null; }
function wp_insert_post(array $d, $e = false) { $id = $GLOBALS['naechste']++; $GLOBALS['posts'][$id] = new WP_Post($id, $d); return $id; }
function wp_update_post(array $d, $e = false) { $p = $GLOBALS['posts'][$d['ID']]; foreach ($d as $k => $v) if ($k !== 'ID') $p->$k = $v; return $d['ID']; }
function update_post_meta($id, $k, $v) { $GLOBALS['meta'][$id][$k] = $v; return true; }
function current_user_can($c) { return true; }

require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/seiten.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-66s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Inhalt\n";
$alle = '';
foreach (ma_seiten() as $slug => $s) { $h = ma_seite_html($slug); $alle .= $h; if ($h === '' || $s['anriss'] === '' || $s['eyebrow'] === '') { $fehler++; echo "  FEHLER: $slug leer\n"; } }
pruefe('17 Seiten mit Text, Anriss und Eyebrow', count(ma_seiten()), 17);
pruefe('Keine Vorschau-Domain, kein Netlify-Formular, kein TODO', str_contains($alle, 'hk-growthoperator') || str_contains($alle, '/api/formular') || str_contains($alle, 'TODO') || str_contains($alle, 'kalender.ics'), false);
pruefe('Formulare: Kontakt, Meldung, Termin, Werbung, Korrektur, Immobilie, Stelle, Trauer, Familie', [str_contains(ma_seite_kontakt(), '[ma_formular typ="kontakt"]'), str_contains(ma_seite_meldung_senden(), 'typ="meldung"'), str_contains(ma_seite_termin_melden(), 'typ="termin"'), str_contains(ma_seite_werben(), 'typ="werbung"'), str_contains(ma_seite_korrekturen(), 'typ="korrektur"'), substr_count(ma_seite_aufgeben(), '[ma_formular'), str_contains(ma_seite_aufgeben(), 'typ="stelle"') ], [true, true, true, true, true, 5, true]);
pruefe('Archiv, Diskussion, Redaktion und Themen nutzen ihre Shortcodes', [str_contains(ma_seite_archiv(), '[ma_archiv]'), str_contains(ma_seite_diskussion(), '[ma_diskussion]'), str_contains(ma_seite_redaktion(), '[ma_redaktion_meldungen]'), str_contains(ma_seite_themen(), '[ma_themen]')], [true, true, true, true]);
pruefe('Kommentarregeln beschreiben WordPress-Moderation, keinen Melden-Knopf', str_contains(ma_seite_kommentarregeln(), 'vor der Veröffentlichung von der Redaktion geprüft') && str_contains(ma_seite_kommentarregeln(), 'IP-Adresse wird beim Kommentieren nicht gespeichert') && !str_contains(ma_seite_kommentarregeln(), 'Melden“-Knopf'), true);
pruefe('Über uns ohne Behauptung laufender Bannerkunden; Finanzierung und Grundsätze verlinkt', !str_contains(ma_seite_ueber_uns(), 'AJ Sports') && str_contains(ma_seite_ueber_uns(), 'id="finanzierung"') && str_contains(ma_seite_ueber_uns(), '/grundsaetze/'), true);
pruefe('Grundsätze mit Ankern ethik und vielfalt (Organisations-Schema)', str_contains(ma_seite_grundsaetze(), 'id="ethik"') && str_contains(ma_seite_grundsaetze(), 'id="vielfalt"'), true);
pruefe('WhatsApp-Seite: Kanal noch nicht gestartet, RSS auf WordPress-Feeds', str_contains(ma_seite_whatsapp(), 'noch nicht gestartet') && str_contains(ma_seite_whatsapp(), '/blaulicht/feed/'), true);
pruefe('Links nur auf WordPress-Adressen (kein /autor/redaktion/, /termine/melden/, feed.xml)', str_contains($alle, '/autor/redaktion/') || str_contains($alle, '/termine/melden/') || str_contains($alle, 'feed.xml'), false);
pruefe('Pfad mit Elternseite', ma_seite_pfad('aufgeben'), 'anzeigen/aufgeben');

echo "\nAnlegen und Pflegen\n";
pruefe('Status vor dem Anlegen: fehlt', ma_seite_status('ueber-uns'), 'fehlt');
ma_seiten_abgleichen();
$stati = array_unique(array_map('ma_seite_status', array_keys(ma_seiten())));
pruefe('Alle Seiten angelegt und aktuell', $stati, ['aktuell']);
$a = ma_seite_seite('aufgeben');
pruefe('Anzeige aufgeben hängt unter Anzeigen', $a && $GLOBALS['posts'][$a->post_parent]->post_name === 'anzeigen' && pfad_von($a) === 'anzeigen/aufgeben', true);
$u = ma_seite_seite('ueber-uns');
pruefe('Seite veröffentlicht, Kommentare zu, Eyebrow, Anriss', [$u->post_status, $u->comment_status, $GLOBALS['meta'][$u->ID]['ma_eyebrow'], $u->post_excerpt !== ''], ['publish', 'closed', 'Redaktion', true]);
pruefe('Guard gesetzt', get_option('ma_seiten_geprueft'), MA_SEITEN_VERSION);
$k = ma_seite_seite('korrekturen');
$k->post_content = str_replace('Bislang gibt es keine dokumentierten Korrekturen.', '<li>02.10.2026: Uhrzeit korrigiert.</li>', $k->post_content);
pruefe('Manuell geändert erkannt (Korrekturliste gepflegt)', ma_seite_status('korrekturen'), 'manuell');
$GLOBALS['opt']['ma_seiten_stand']['korrekturen']['version'] = '2026-01-01'; unset($GLOBALS['opt']['ma_seiten_geprueft']);
ma_seiten_abgleichen();
pruefe('Abgleich lässt die gepflegte Korrekturliste stehen', str_contains(ma_seite_seite('korrekturen')->post_content, 'Uhrzeit korrigiert'), true);
$GLOBALS['opt']['ma_seiten_stand']['kontakt']['version'] = '2026-01-01'; unset($GLOBALS['opt']['ma_seiten_geprueft']);
pruefe('Ältere Fassung erkannt', ma_seite_status('kontakt'), 'veraltet');
ma_seiten_abgleichen();
pruefe('Unveränderte ältere Fassung wird gehoben', ma_seite_status('kontakt'), 'aktuell');
pruefe('Zweiter Abgleich legt nichts doppelt an', count($GLOBALS['posts']), 17);
// Neue Seite bei gleicher Fassung (Live-Befund 02.10.: /thema/ fehlte, weil der Guard schon stand): Anzahl-Guard greift.
$GLOBALS['opt']['ma_seiten_anzahl'] = 16; unset($GLOBALS['posts'][ma_seite_seite('thema')->ID]);
ma_seiten_abgleichen();
pruefe('Fehlende Seite wird trotz gesetztem Fassungs-Guard angelegt', [ma_seite_status('thema'), get_option('ma_seiten_anzahl')], ['aktuell', 17]);

echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
