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
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES); } function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES); } function esc_url($s) { return (string) $s; }
function sanitize_title($s) { return strtolower(str_replace([' ', 'ü', 'ö', 'ä'], ['-', 'u', 'o', 'a'], (string) $s)); }

require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/seiten.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-66s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Inhalt\n";
$alle = '';
foreach (ma_seiten() as $slug => $s) { $h = ma_seite_html($slug); $alle .= $h; if ($h === '' || $s['anriss'] === '' || $s['eyebrow'] === '') { $fehler++; echo "  FEHLER: $slug leer\n"; } }
pruefe('20 Seiten mit Text, Anriss und Eyebrow (1.45.0: Merzenich in Zahlen)', count(ma_seiten()), 20);
pruefe('Keine Vorschau-Domain, kein Netlify-Formular, kein TODO', str_contains($alle, 'hk-growthoperator') || str_contains($alle, '/api/formular') || str_contains($alle, 'TODO') || str_contains($alle, 'kalender.ics'), false);
pruefe('Formulare: Kontakt, Meldung, Termin, Werbung, Korrektur; Anzeigen über die Auswahl', [str_contains(ma_seite_kontakt(), '[ma_formular typ="kontakt"]'), str_contains(ma_seite_meldung_senden(), 'typ="meldung"'), str_contains(ma_seite_termin_melden(), 'typ="termin"'), str_contains(ma_seite_werben(), 'typ="werbung"'), str_contains(ma_seite_korrekturen(), 'typ="korrektur"'), str_contains(ma_seite_aufgeben(), '[ma_anzeige_aufgeben]'), !str_contains(ma_seite_aufgeben(), '[ma_formular') ], [true, true, true, true, true, true, true]);
pruefe('Archiv, Diskussion, Redaktion und Themen nutzen ihre Shortcodes', [str_contains(ma_seite_archiv(), '[ma_archiv]'), str_contains(ma_seite_diskussion(), '[ma_diskussion]'), str_contains(ma_seite_redaktion(), '[ma_redaktion_meldungen]'), str_contains(ma_seite_themen(), '[ma_themen]')], [true, true, true, true]);
pruefe('Kommentarregeln beschreiben WordPress-Moderation, keinen Melden-Knopf', str_contains(ma_seite_kommentarregeln(), 'vor der Veröffentlichung von der Redaktion geprüft') && str_contains(ma_seite_kommentarregeln(), 'IP-Adresse wird beim Kommentieren nicht gespeichert') && !str_contains(ma_seite_kommentarregeln(), 'Melden“-Knopf'), true);
pruefe('Über uns ohne Behauptung laufender Bannerkunden; Finanzierung und Grundsätze verlinkt', !str_contains(ma_seite_ueber_uns(), 'AJ Sports') && str_contains(ma_seite_ueber_uns(), 'id="finanzierung"') && str_contains(ma_seite_ueber_uns(), '/grundsaetze/'), true);
pruefe('Grundsätze mit Ankern ethik und vielfalt (Organisations-Schema)', str_contains(ma_seite_grundsaetze(), 'id="ethik"') && str_contains(ma_seite_grundsaetze(), 'id="vielfalt"'), true);
pruefe('WhatsApp-Seite: Kanal wird eingerichtet (kein Link), RSS auf WordPress-Feeds', str_contains(ma_seite_whatsapp(), 'wird gerade eingerichtet') && !str_contains(ma_seite_whatsapp(), 'whatsapp.com') && str_contains(ma_seite_whatsapp(), '/blaulicht/feed/'), true);
pruefe('Unterstützen ohne „gibt es noch nicht“ und „sobald er startet“', [str_contains(ma_seite_unterstuetzen(), 'noch nicht'), str_contains(ma_seite_unterstuetzen(), 'sobald er startet')], [false, false]);
pruefe('Werben: sechs buchbare Flächen, Knopf zur Anfrage, Formular', [substr_count(ma_seite_werben(), '<li><strong>'), str_contains(ma_seite_werben(), 'href="#anfrage">Werbung anfragen'), str_contains(ma_seite_werben(), '[ma_formular typ="werbung"]')], [6, true, true]);
pruefe('Links nur auf WordPress-Adressen (kein /autor/redaktion/, /termine/melden/, feed.xml)', str_contains($alle, '/autor/redaktion/') || str_contains($alle, '/termine/melden/') || str_contains($alle, 'feed.xml'), false);
pruefe('Pfad mit Elternseite', ma_seite_pfad('aufgeben'), 'anzeigen/aufgeben');

echo "\nMerzenich in Zahlen (1.45.0)\n";
$mz = ma_seite_merzenich_in_zahlen();
pruefe('Faktenseite: Stand, Fragen als Überschrift, Quellen zu Rat und Bürgermeister', [str_contains($mz, 'Stand: 3. November 2026'), substr_count($mz, '<h2 id='), str_contains($mz, 'wahlergebnisse.nrw/kommunalwahlen/2025/aktuell/pdf/a358040kw25.pdf'), str_contains($mz, 'b358040kw25.pdf')], [true, 7, true, true]);
pruefe('Faktenseite: Zahlen wie in den Quellen (Einwohner, Sitze, Ergebnis)', [str_contains($mz, '10.509'), str_contains($mz, '26 Sitze'), str_contains($mz, '57,2 Prozent')], [true, true, true]);
pruefe('Faktenseite: keine Gedankenstriche', [str_contains($mz, ' – '), str_contains($mz, '—')], [false, false]);

echo "\nUmgebung (1.42.0)\n";
pruefe('Umgebungsseite nutzt den Shortcode und verlinkt Nachrichten, Blaulicht, Termine', [str_contains(ma_seite_umgebung(), '[ma_umgebung]'), str_contains(ma_seite_umgebung(), 'href="/blaulicht/"'), str_contains(ma_seite_umgebung(), 'href="/termine/"')], [true, true, true]);
$ug = ma_umgebung_html([
    ['ort' => 'Niederzier', 'rubrik' => 'Blaulicht', 'titel' => 'Brand in Huchem-Stammeln', 'url' => 'https://www.presseportal.de/blaulicht/pm/8/1', 'datum' => '2026-10-20', 'quelle' => 'Polizei Düren'],
    ['ort' => 'Düren', 'rubrik' => 'Verkehr', 'titel' => 'Ältere Meldung', 'url' => 'https://www.dueren.de/a', 'datum' => '2026-10-01', 'quelle' => 'Stadt Düren'],
    ['ort' => 'Düren', 'rubrik' => 'Blaulicht', 'titel' => 'Neuere Meldung <b>', 'url' => 'https://www.presseportal.de/blaulicht/pm/8/2', 'datum' => '2026-10-26', 'quelle' => 'Polizei Düren'],
    ['ort' => 'Jülich', 'rubrik' => 'X', 'titel' => 'Fremder Ort', 'url' => 'https://x.de/', 'datum' => '2026-10-26', 'quelle' => 'X'],
    ['ort' => 'Düren', 'rubrik' => 'X', 'titel' => 'Ohne https', 'url' => 'http://x.de/', 'datum' => '2026-10-26', 'quelle' => 'X'],
]);
pruefe('Je Ort eine Überschrift „<Ort> aktuell“, Düren vor Niederzier', [str_contains($ug, '<h2 id="duren">Düren aktuell</h2>'), strpos($ug, 'Düren aktuell') < strpos($ug, 'Niederzier aktuell')], [true, true]);
pruefe('Neueste zuerst, Datum TT.MM., Titel maskiert', [strpos($ug, 'Neuere Meldung') < strpos($ug, 'Ältere Meldung'), str_contains($ug, '>26.10.</time>'), str_contains($ug, 'Neuere Meldung &lt;b&gt;')], [true, true, true]);
pruefe('Fremde Orte und Links ohne https fallen weg', [str_contains($ug, 'Fremder Ort'), str_contains($ug, 'Ohne https')], [false, false]);
pruefe('Externe Links öffnen neu, ohne Referrer', str_contains($ug, 'target="_blank" rel="noopener noreferrer"'), true);
pruefe('Ohne Einträge ein Hinweis statt leerer Seite', str_contains(ma_umgebung_html([]), 'keine Meldungen aus der Umgebung'), true);

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
pruefe('Zweiter Abgleich legt nichts doppelt an', count($GLOBALS['posts']), 20);
// Neue Seite bei gleicher Fassung (Live-Befund 02.10.: /thema/ fehlte, weil der Guard schon stand): Anzahl-Guard greift.
$GLOBALS['opt']['ma_seiten_anzahl'] = 18; unset($GLOBALS['posts'][ma_seite_seite('thema')->ID]);
ma_seiten_abgleichen();
pruefe('Fehlende Seite wird trotz gesetztem Fassungs-Guard angelegt', [ma_seite_status('thema'), get_option('ma_seiten_anzahl')], ['aktuell', 20]);

echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
