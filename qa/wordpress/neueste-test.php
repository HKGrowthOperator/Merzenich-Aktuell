<?php
/**
 * „Neu in Merzenich“ (Theme 21.26.0, inc/neueste.php) ohne WordPress: Auswahl der neuesten
 * Meldungen für die Spalte „Nachrichten aus Merzenich“ und Zeitangabe.
 * Aufruf: php qa/wordpress/neueste-test.php
 */
define('ABSPATH', __DIR__ . '/');
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/neueste.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-72s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

$jetzt = strtotime('2026-11-03T12:00:00+01:00');
$h = fn(float $stunden) => (int) ($jetzt - $stunden * 3600);
echo "Auswahl\n";
pruefe('Zwölf Meldungen in 48 Stunden: höchstens zehn', ma21_neueste_anzahl(array_map($h, range(1, 12)), $jetzt), 10);
pruefe('Drei in 48 Stunden, Rest älter: mindestens fünf', ma21_neueste_anzahl([$h(2), $h(5), $h(30), $h(80), $h(100), $h(120), $h(200)], $jetzt), 5);
pruefe('Sieben in 48 Stunden, dann alt: alle sieben', ma21_neueste_anzahl([$h(1), $h(3), $h(6), $h(10), $h(20), $h(30), $h(47), $h(60), $h(90)], $jetzt), 7);
pruefe('Weniger als fünf Meldungen überhaupt: alle', ma21_neueste_anzahl([$h(100), $h(200)], $jetzt), 2);
pruefe('Keine Meldung: nichts', ma21_neueste_anzahl([], $jetzt), 0);
echo "\nZeitangabe (Europe/Berlin)\n";
pruefe('Heute: nur Uhrzeit', ma21_neueste_wann(strtotime('2026-11-03T09:16:00+01:00'), $jetzt), '09:16 Uhr');
pruefe('Gestern', ma21_neueste_wann(strtotime('2026-11-02T18:56:00+01:00'), $jetzt), 'gestern, 18:56');
pruefe('Älter: Tag und Monat', ma21_neueste_wann(strtotime('2026-10-28T08:55:00+01:00'), $jetzt), '28.10., 08:55');
pruefe('Kurz nach Mitternacht Ortszeit zählt zu heute (UTC noch gestern)', ma21_neueste_wann(strtotime('2026-11-02T23:13:00Z'), $jetzt), '00:13 Uhr');
echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
