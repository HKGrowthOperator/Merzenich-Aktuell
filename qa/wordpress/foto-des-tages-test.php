<?php
/**
 * Foto des Tages auf der WordPress-Startseite (inc/ma21.php, Theme 21.10.6):
 * Wahl des Fotos ohne WordPress, geprüft an den echten Daten aus
 * chatgpt-site/assets/foto-des-tages.json (deploy/foto-des-tages.mjs).
 * Vorgabe Betreiber 05.10.2026: die Fläche fällt nie weg.
 * Aufruf: php qa/wordpress/foto-des-tages-test.php
 */
define('ABSPATH', __DIR__ . '/');
function add_action(...$a) {} function add_filter(...$a) {} function remove_action(...$a) {} function add_image_size(...$a) {}
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return (string) $s; }
class WP_Term {} class WP_Post {} class WP_Query {}
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-70s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Motiv-Schlüssel\n";
pruefe('Ortsansicht ohne Breite und Endung', ma21_motiv_schluessel('/assets/places/golzheim-1440.webp'), 'places/golzheim');
pruefe('720er Kachel ist dasselbe Motiv', ma21_motiv_schluessel('https://x.de/assets/places/golzheim-720.webp?v=1'), 'places/golzheim');
pruefe('Poolfoto', ma21_motiv_schluessel('/assets/editorial-pools/aktuell/aktuell-05-85b961cd5e.jpg'), 'editorial-pools/aktuell/aktuell-05-85b961cd5e');

$d = json_decode((string) file_get_contents(__DIR__ . '/../../chatgpt-site/assets/foto-des-tages.json'), true);
echo "\nDaten aus deploy/foto-des-tages.mjs\n";
pruefe('Volle Reihe „alle“ mit mindestens acht Fotos', is_array($d['alle'] ?? null) && count($d['alle']) >= 8, true);
pruefe('Jedes Foto mit Bild, Beschreibung, Ort und Nachweis', count(array_filter($d['alle'], fn($r) => !empty($r['src']) && !empty($r['alt']) && !empty($r['ort']) && str_contains((string) $r['credit'], ' · '))), count($d['alle']));

echo "\nWahl\n";
$a = ma21_foto_des_tages_wahl($d, '2026-10-05', []);
$b = ma21_foto_des_tages_wahl($d, '2026-10-06', []);
pruefe('Ohne Belegung: es gibt immer ein Foto', is_array($a) && !empty($a['src']), true);
pruefe('Wechselt täglich', $a['src'] !== $b['src'], true);
pruefe('Gleicher Tag, gleiches Foto', ma21_foto_des_tages_wahl($d, '2026-10-05', [])['src'], $a['src']);
$alle = array_map(fn($r) => ma21_motiv_schluessel($r['src']), $d['alle']);
$belegt = array_values(array_filter($alle, fn($k) => $k !== ma21_motiv_schluessel($d['alle'][2]['src'])));
pruefe('Motive der Startseite werden übersprungen', ma21_foto_des_tages_wahl($d, '2026-10-05', $belegt)['src'], $d['alle'][2]['src']);
pruefe('Alles belegt: trotzdem ein Foto (volle Reihe)', ma21_foto_des_tages_wahl($d, '2026-10-05', $alle)['src'], $a['src']);
$mitLeser = $d + []; $mitLeser['eintraege'] = [['datum' => '2026-10-05', 'src' => '/assets/leser/x.jpg', 'alt' => 'Sonnenuntergang', 'ort' => 'Golzheim', 'credit' => 'Leserin · mit Zustimmung']];
pruefe('Datierte Leser-Einsendung geht vor', ma21_foto_des_tages_wahl($mitLeser, '2026-10-05', [])['src'], '/assets/leser/x.jpg');
pruefe('Einsendung gilt nur an ihrem Tag', ma21_foto_des_tages_wahl($mitLeser, '2026-10-06', [])['src'], $b['src']);
pruefe('Leere Daten: kein Foto, kein Fehler', ma21_foto_des_tages_wahl([], '2026-10-05', []), null);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
