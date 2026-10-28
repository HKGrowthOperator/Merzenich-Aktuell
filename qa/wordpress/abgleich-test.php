<?php
/**
 * Abgleich (includes/abgleich.php) ohne WordPress: Lesen der Import-Datei,
 * Entscheidung je Beitrag, Status neuer Beiträge, Bildquellen.
 * Aufruf: php qa/wordpress/abgleich-test.php
 */
define('ABSPATH', __DIR__); define('MA_CORE_VERSION', 'test'); define('MA_CORE_PATH', __DIR__ . '/');
define('MINUTE_IN_SECONDS', 60);
$GLOBALS['opt'] = [];
function add_action(...$a) {} function add_filter(...$a) {} function register_deactivation_hook(...$a) {}
function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function update_option($k, $v, $a = null) { $GLOBALS['opt'][$k] = $v; return true; }

require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/abgleich.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-70s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Abkürzung über den ETag der Import-Datei\n";
pruefe('ETag ohne W/ und Anführungszeichen', [ma_abgleich_etag_norm('W/"abc123"'), ma_abgleich_etag_norm(' "abc123" '), ma_abgleich_etag_norm('')], ['abc123', 'abc123', '']);
pruefe('Gleicher ETag, nichts offen, kein Fehler: voller Lauf entfällt', ma_abgleich_ueberspringen('W/"abc"', '"abc"', ['offen' => 0, 'fehler' => []]), true);
pruefe('Anderer ETag, offene Beiträge oder Fehler oder kein ETag: voller Lauf', [ma_abgleich_ueberspringen('"neu"', '"abc"', []), ma_abgleich_ueberspringen('"abc"', '"abc"', ['offen' => 3]), ma_abgleich_ueberspringen('"abc"', '"abc"', ['fehler' => ['x']]), ma_abgleich_ueberspringen('', '', [])], [false, false, false, false]);

echo "\nImport-Datei lesen\n";
$xml = file_get_contents(__DIR__ . '/../../wordpress-delivery/merzenich-aktuell-import.xml');
$d = ma_abgleich_lesen($xml);
$typen = array_count_values(array_column($d['eintraege'], 'typ'));
pruefe('Stand aus dem Kopf der Datei (ISO-Zeit)', (bool) preg_match('/^\d{4}-\d\d-\d\dT/', $d['stand']), true);
pruefe('Meldungen und Termine gelesen, Seiten nicht', [$typen['post'] > 100, $typen['ma_event'] > 10, isset($typen['page'])], [true, true, false]);
pruefe('Anhänge je Import-ID mit Adresse, Nachweis und statischem Pfad', count($d['bilder']) > 50 && count(array_filter($d['bilder'], fn($b) => $b['url'] !== '' && $b['src'] !== '' && isset($b['meta']['ma_image_credit']))) === count($d['bilder']), true);
$e = array_values(array_filter($d['eintraege'], fn($e) => $e['typ'] === 'post'))[0];
pruefe('Meldung: Pfad, Slug, Titel, Datum, Entwurf, Kommentare offen', [$e['legacy'] !== '' && str_starts_with($e['legacy'], '/'), $e['slug'] !== '', $e['titel'] !== '', (bool) preg_match('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $e['datum_gmt']), $e['status'], $e['kommentare']], [true, true, true, true, 'draft', 'open']);
pruefe('Meldung: Inhalt als HTML, Auszug, Rubrik, Ort und Schlagworte', [str_contains($e['inhalt'], '<p>'), $e['auszug'] !== '', in_array('category', array_column($e['terme'], 'tax'), true), in_array('ma_location', array_column($e['terme'], 'tax'), true)], [true, true, true, true]);
pruefe('Meldung: Importfelder ohne _thumbnail_id, Bild über Import-ID erreichbar', [isset($e['meta']['_thumbnail_id']), isset($e['meta']['ma_source_url']), $e['bild'] > 0 && isset($d['bilder'][$e['bild']])], [false, true, true]);
pruefe('Jede Meldung hat einen Pfad und einen Hash', count(array_filter($d['eintraege'], fn($e) => $e['legacy'] === '' || strlen($e['hash']) !== 40)), 0);
pruefe('Pfade eindeutig', count(array_unique(array_column($d['eintraege'], 'legacy'))), count($d['eintraege']));
$t = array_values(array_filter($d['eintraege'], fn($e) => $e['typ'] === 'ma_event'))[0];
pruefe('Termin: veröffentlicht, Beginn im Importfeld, Kommentare zu', [$t['status'], isset($t['meta']['ma_event_start']), $t['kommentare']], ['publish', true, 'closed']);
pruefe('Lesen ist deterministisch', ma_abgleich_lesen($xml)['eintraege'][0]['hash'], $d['eintraege'][0]['hash']);
$x2 = str_replace($e['titel'], $e['titel'] . ' (geändert)', $xml);
$e2 = array_values(array_filter(ma_abgleich_lesen($x2)['eintraege'], fn($z) => $z['legacy'] === $e['legacy']))[0];
pruefe('Geänderter Titel ändert den Hash, Pfad bleibt', [$e2['hash'] !== $e['hash'], $e2['legacy']], [true, $e['legacy']]);
pruefe('Unlesbare Datei: keine Einträge, kein Abbruch', ma_abgleich_lesen('<html>kaputt')['eintraege'], []);
pruefe('Fremdes XML ohne Kanal: leer', ma_abgleich_lesen('<?xml version="1.0"?><rss/>')['eintraege'], []);

echo "\nEntscheidung je Beitrag\n";
pruefe('Noch nie abgeglichen: Stand übernehmen', ma_abgleich_entscheidung('neu', '', '', 'x'), 'uebernehmen');
pruefe('Gleicher Hash: unverändert', ma_abgleich_entscheidung('a', 'a', 't', 'anders'), 'unveraendert');
pruefe('Neuer Hash, Text in WordPress unverändert: aktualisieren', ma_abgleich_entscheidung('b', 'a', 't', 't'), 'aktualisieren');
pruefe('Neuer Hash, Text in WordPress von Hand geändert: nicht anfassen', ma_abgleich_entscheidung('b', 'a', 't', 't2'), 'von_hand');
pruefe('Texthash ignoriert Leerraum am Rand', ma_abgleich_texthash(' T ', "I\n", 'A'), ma_abgleich_texthash('T', 'I', 'A'));

echo "\nStatus und Quellen\n";
pruefe('Neue Meldung: Entwurf, mit Option sofort veröffentlicht', [ma_abgleich_status($e, false), ma_abgleich_status($e, true)], ['draft', 'publish']);
pruefe('Neuer Termin: wie geliefert (veröffentlicht), Unbekanntes wird Entwurf', [ma_abgleich_status($t, false), ma_abgleich_status(['typ' => 'ma_event', 'status' => 'inherit'], true)], ['publish', 'draft']);
pruefe('Import-Datei: Standard ist das Repository auf GitHub (main)', ma_abgleich_quelle(), 'https://raw.githubusercontent.com/HKGrowthOperator/Merzenich-Aktuell/main/wordpress-delivery/merzenich-aktuell-import.xml');
$GLOBALS['opt']['ma_abgleich_quelle'] = 'http://127.0.0.1:8300/x.xml';
pruefe('Import-Datei: eigene Adresse aus den Einstellungen', ma_abgleich_quelle(), 'http://127.0.0.1:8300/x.xml');
pruefe('Bilder: Repository als letzte Quelle, eigene Adresse zuerst', [array_slice(ma_abgleich_bildbasen(), -1)[0], (function () { $GLOBALS['opt']['ma_abgleich_bilder'] = 'http://127.0.0.1:8300/chatgpt-site/'; return ma_abgleich_bildbasen()[0]; })()], ['https://raw.githubusercontent.com/HKGrowthOperator/Merzenich-Aktuell/main/chatgpt-site', 'http://127.0.0.1:8300/chatgpt-site']);
pruefe('Abgleich ist standardmäßig an, sofortige Veröffentlichung aus', [ma_abgleich_aktiv(), ma_abgleich_sofort()], [true, false]);
$ki = ['typ' => 'post', 'meta' => ['ma_herkunft' => 'ki-redaktion']];
pruefe('KI-Redaktion (1.33.0): mit Option direkt veröffentlicht, ohne Option Entwurf', [ma_abgleich_status($ki, false, true), ma_abgleich_status($ki, false, false)], ['publish', 'draft']);
pruefe('KI-Option gilt nicht für andere Meldungen (Feuerwehr-Automatik, ChatGPT)', ma_abgleich_status($e, false, true), 'draft');
pruefe('KI-Option ändert Termine nicht', ma_abgleich_status(['typ' => 'ma_event', 'status' => 'draft', 'meta' => ['ma_herkunft' => 'ki-redaktion']], false, true), 'draft');
pruefe('KI direkt ist standardmäßig aus', ma_abgleich_ki_direkt(), false);
pruefe('KI-Häkchen setzen Quelle, Datum, Ort, Prüfung und Prüfer', [ma_abgleich_ki_haekchen()['ma_human_reviewed'], ma_abgleich_ki_haekchen()['ma_reviewed_by']], ['1', MA_ABGLEICH_KI_PRUEFER]);

echo "\nAktualisieren lässt Prüfhäkchen stehen (1.27.3)\n";
$importMeta = ['ma_legacy_url' => '/blaulicht/x/', 'ma_source_url' => 'https://example.org/q', 'ma_source_verified' => '1', 'ma_date_verified' => '0', 'ma_place_verified' => '0', 'ma_human_reviewed' => '0', 'ma_startseite_freigabe' => 'ja', 'ma_kicker' => 'Feuerwehr · 138/26'];
$upd = ma_abgleich_meta_fuer_update($importMeta);
pruefe('Datum, Ort, redaktionelle Prüfung und Startseiten-Freigabe fallen weg', array_intersect(['ma_date_verified', 'ma_place_verified', 'ma_human_reviewed', 'ma_startseite_freigabe'], array_keys($upd)), []);
pruefe('Übrige Importfelder bleiben unverändert', $upd, ['ma_legacy_url' => '/blaulicht/x/', 'ma_source_url' => 'https://example.org/q', 'ma_source_verified' => '1', 'ma_kicker' => 'Feuerwehr · 138/26']);
$echt = ma_abgleich_lesen($xml)['eintraege'];
$post = array_values(array_filter($echt, fn($x) => $x['typ'] === 'post'))[0] ?? ['meta' => []];
pruefe('Bildrechte: bestätigte 1 bleibt, 0 aus der Import-Datei wird nicht geschrieben', [ma_abgleich_meta_fuer_update(['ma_image_rights_verified' => '1'])['ma_image_rights_verified'] ?? null, array_key_exists('ma_image_rights_verified', ma_abgleich_meta_fuer_update(['ma_image_rights_verified' => '0']))], ['1', false]);
pruefe('Reparatur: veröffentlicht + Abgleich, fehlende Häkchen', ma_abgleich_haekchen_fehlend('publish', 'h', ['ma_date_verified' => '0', 'ma_place_verified' => '1', 'ma_human_reviewed' => '']), ['ma_date_verified', 'ma_human_reviewed']);
pruefe('Reparatur: Entwurf oder ohne Abgleich bleibt unberührt', [ma_abgleich_haekchen_fehlend('draft', 'h', []), ma_abgleich_haekchen_fehlend('publish', '', [])], [[], []]);
pruefe('Reparatur: alles gesetzt, nichts zu tun', ma_abgleich_haekchen_fehlend('publish', 'h', ['ma_date_verified' => '1', 'ma_place_verified' => '1', 'ma_human_reviewed' => '1']), []);
pruefe('Echte Import-Datei: Meldungen tragen die Häkchen beim Anlegen, das Update nicht', [isset($post['meta']['ma_human_reviewed']), isset(ma_abgleich_meta_fuer_update($post['meta'])['ma_human_reviewed'])], [true, false]);

echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
