<?php
/**
 * Kommende Termine (includes/termine-abfrage.php) ohne WordPress: dieselbe Regel wie die frühere
 * meta_query (Ende ab jetzt; ohne Ende Beginn ab jetzt), sortiert nach Beginn.
 * Aufruf: php qa/wordpress/termine-abfrage-test.php
 */
define('ABSPATH', __DIR__);
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/termine-abfrage.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-66s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

$jetzt = 1_000_000;
$t = [
    11 => ['start' => $jetzt + 7200, 'ende' => null],          // kommt, ohne Ende
    12 => ['start' => $jetzt - 7200, 'ende' => $jetzt + 3600],  // läuft gerade (Ende in einer Stunde)
    13 => ['start' => $jetzt - 7200, 'ende' => null],           // ohne Ende, Beginn vorbei
    14 => ['start' => $jetzt - 9000, 'ende' => $jetzt - 60],    // vorbei
    15 => ['start' => $jetzt + 3600, 'ende' => $jetzt + 7200],  // kommt
    16 => ['start' => 0, 'ende' => null],                        // ohne Beginn
    17 => ['start' => $jetzt + 3600, 'ende' => null],           // gleicher Beginn wie 15
];
$ids = ma_termine_kommend_filtern($t, $jetzt);
pruefe('Laufende und kommende Termine, vergangene und ohne Beginn nicht', array_values(array_diff([11, 12, 13, 14, 15, 16, 17], $ids)), [13, 14, 16]);
pruefe('Nach Beginn sortiert, bei gleichem Beginn nach ID', $ids, [12, 15, 17, 11]);
pruefe('Ende genau jetzt zählt noch', ma_termine_kommend_filtern([5 => ['start' => $jetzt - 10, 'ende' => $jetzt]], $jetzt), [5]);
pruefe('Ohne Termine leer', ma_termine_kommend_filtern([], $jetzt), []);

echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
