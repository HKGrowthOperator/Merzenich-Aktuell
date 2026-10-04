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

echo "Import-Datei lesen\n";
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

echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
