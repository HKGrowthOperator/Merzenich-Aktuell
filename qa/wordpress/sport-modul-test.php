<?php
/**
 * Spielstand-Ecke und Sportmodul auf /sport/ zur Laufzeit (Theme 21.13.0,
 * inc/sport.php) ohne WordPress: Werte aus dem Datenstand statt aus der
 * Vorlage vom Bautag, offenes Spiel nach Anstoß, Tabellenauszug mit Heimteam.
 * Aufruf: php qa/wordpress/sport-modul-test.php
 */
define('ABSPATH', __DIR__ . '/');
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return (string) $s; }
function wp_date($f, $ts = null) { return (new DateTimeImmutable('@' . ($ts ?? time())))->setTimezone(new DateTimeZone('Europe/Berlin'))->format($f); }
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/sport.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-70s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

// Stand aus dem Backend am 05.10.2026 (gekürzt)
$d = ['generated' => '2026-10-05T16:08:00+02:00', 'source' => 'FUSSBALL.DE', 'sourceUrl' => 'https://www.fussball.de/mannschaft/sc', 'quelle' => 'backend',
    'lastMatch' => ['date' => '2026-10-02T19:00:00+02:00', 'home' => 'SC Salingia 08 Barmen', 'away' => 'SC 1919 Merzenich', 'score' => '0:5', 'confirmed' => true, 'reportUrl' => 'https://www.fussball.de/bericht'],
    'nextMatch' => ['date' => '2026-10-09T19:30:00+02:00', 'home' => 'Borussia Freialdenhoven', 'away' => 'SC 1919 Merzenich'],
    'table' => []];
foreach ([['SC Jülich', 19], ['FC Golzheim', 19], ['TuS Langerwehe', 16], ['Viktoria Huchem', 15], ['SG Inden', 15], ['SC 1919 Merzenich', 15], ['Fortuna Müddersheim', 12]] as $i => [$team, $pkt]) {
    $d['table'][] = ['place' => $i + 1, 'team' => $team, 'played' => 7, 'wins' => 5, 'draws' => 0, 'losses' => 2, 'goals' => $team === 'SC 1919 Merzenich' ? '26:12' : '10:10', 'diff' => $team === 'SC 1919 Merzenich' ? 14 : 0, 'points' => $pkt, 'homeTeam' => $team === 'SC 1919 Merzenich'];
}
$vorher = (new DateTimeImmutable('2026-10-06T12:00:00+02:00'))->getTimestamp();
$nachher = (new DateTimeImmutable('2026-10-10T08:00:00+02:00'))->getTimestamp();

echo "Spielstand-Ecke\n";
$e = ma21_sport_ecke($d, $vorher);
pruefe('Platz, Punkte, Tore aus dem Datenstand', [str_contains($e, '<span class="sc-wert">6.</span>'), str_contains($e, '<span class="sc-wert">15</span>'), str_contains($e, '<span class="sc-wert">26:12</span>')], [true, true, true]);
pruefe('Nächstes Spiel mit Wochentag und Uhrzeit', str_contains($e, 'Fr., 09.10., 19:30 Uhr</time><br>Borussia Freialdenhoven gegen SC 1919 Merzenich'), true);
pruefe('Datenstand in deutscher Zeit, Vereinslink ohne Umleitung', [str_contains($e, 'Datenstand 05.10.2026, 16:08 Uhr · Quelle FUSSBALL.DE'), str_contains($e, 'href="/vereine/sc-1919-merzenich/"')], [true, true]);
pruefe('Nach Anstoß kein „Nächstes Spiel“ mehr in der Ecke', str_contains(ma21_sport_ecke($d, $nachher), 'Nächstes Spiel'), false);
pruefe('Ohne Merzenich in der Tabelle keine Ecke', ma21_sport_ecke(['table' => [['team' => 'SC Jülich', 'place' => 1]]] + $d, $vorher), '');

echo "\nSportmodul\n";
$m = ma21_sport_modul($d, $vorher);
pruefe('Letztes Spiel 0:5 mit Spielbericht', [str_contains($m, '02.10.2026 · Endstand'), str_contains($m, '<strong class="match-score">0:5</strong>'), str_contains($m, 'Spielbericht bei FUSSBALL.DE')], [true, true, true]);
pruefe('Nächstes Spiel 09.10. 19:30 Uhr', str_contains($m, '<b>Nächstes Spiel</b><time datetime="2026-10-09T19:30:00+02:00">09.10.2026 · 19:30 Uhr</time>'), true);
pruefe('Nach Anstoß ohne Ergebnis: „Ergebnis noch offen“ mit Link zur Quelle', [str_contains(ma21_sport_modul($d, $nachher), 'Ergebnis noch offen'), str_contains(ma21_sport_modul($d, $nachher), 'Ergebnis und Spielplan bei FUSSBALL.DE')], [true, true]);
pruefe('Tabelle: erste fünf, Lücke, Merzenich als 6. hervorgehoben', [substr_count($m, '<tr><td>'), str_contains($m, 'league-gap'), str_contains($m, '<tr class="home-team"><td>6</td><th scope="row">SC 1919 Merzenich</th>')], [5, true, true]);
pruefe('Logo nur bei Merzenich', substr_count($m, 'sc-1919-merzenich-logo.webp'), 2);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
