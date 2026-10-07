<?php
/**
 * Vereinssport auf /sport/ (Plugin 1.25.0, includes/sport-vereine.php):
 * nur aktive Vereine, Reihenfolge der Sportarten, Lokalvergleich der ersten
 * Mannschaften und der mitgelieferte Datenstand data/sport-aktiv.json.
 * Aufruf: php qa/wordpress/sport-aktiv-test.php
 */
define('ABSPATH', __DIR__ . '/');
$wurzel = dirname(__DIR__, 2);
define('MA_CORE_PATH', "$wurzel/wordpress/plugin/merzenich-aktuell-core/");
function add_action(...$a) {} function add_filter(...$a) {}
$GLOBALS['opt'] = false;
function get_option($k, $d = false) { return $GLOBALS['opt']; }
require MA_CORE_PATH . 'includes/sport-vereine.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-72s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Gruppen nach Sportart\n";
$daten = ['stand' => '2026-10-07', 'vereine' => [
    ['name' => 'Schach A', 'sportart' => 'schach', 'teams' => [['mannschaft' => '1.', 'liga' => 'Bezirksliga', 'platz' => 8, 'punkte' => '0']]],
    ['name' => 'Ohne alles', 'sportart' => 'fussball', 'teams' => [], 'turniere' => []],
    ['name' => 'SC', 'sportart' => 'fussball', 'teams' => [['mannschaft' => 'Herren', 'liga' => 'Kreisliga A', 'platz' => 5, 'punkte' => '15']]],
    ['name' => 'Golzheim', 'sportart' => 'fussball', 'teams' => [['mannschaft' => 'Herren', 'liga' => 'Kreisliga A', 'platz' => 2, 'punkte' => '19']]],
    ['name' => 'Morschenich', 'sportart' => 'fussball', 'teams' => [['mannschaft' => 'Herren', 'liga' => 'Kreisliga B Staffel 2', 'platz' => 4, 'punkte' => '12']]],
    ['name' => 'TTC', 'sportart' => 'tischtennis', 'teams' => [['mannschaft' => 'Erwachsene', 'liga' => '2. Bezirksklasse', 'platz' => 1, 'punkte' => '13:3']]],
    ['name' => 'Billard', 'sportart' => 'billard', 'teams' => [], 'turniere' => [['titel' => 'Ausrichter', 'datum' => '2026-06-26']]],
]];
$g = ma_sport_aktiv_gruppen($daten);
pruefe('Fußball, Tischtennis, Billard, Schach in fester Reihenfolge', array_keys($g), ['fussball', 'tischtennis', 'billard', 'schach']);
pruefe('Verein ohne Mannschaft und Wettkampf fällt weg', array_column($g['fussball'], 'name'), ['SC', 'Golzheim', 'Morschenich']);
pruefe('Wettkampf allein reicht (Ausrichter, Meisterschaft)', count($g['billard']), 1);

echo "\nErste Mannschaften im Vergleich\n";
$v = ma_sport_aktiv_vergleich($g['fussball']);
pruefe('Kreisliga A vor B, innerhalb der Liga nach Platz', array_column($v, 'verein'), ['Golzheim', 'SC', 'Morschenich']);
pruefe('nur ein Verein: kein Vergleich', ma_sport_aktiv_vergleich($g['tischtennis']), []);
pruefe('Mannschaft ohne Platz zählt nicht', ma_sport_aktiv_vergleich([['name' => 'X', 'teams' => [['liga' => 'A', 'platz' => null, 'punkte' => '']]], ['name' => 'Y', 'teams' => [['liga' => 'A', 'platz' => 1, 'punkte' => '3']]]]), []);

echo "\nKinderfußball ohne Tabelle\n";
pruefe('Teams zusammengefasst', ma_sport_ohne_tabelle(['E-Junioren', 'E-Junioren II', 'E-Junioren III', 'F-Junioren', 'G-Junioren']), 'E-Junioren (3 Teams), F-Junioren, G-Junioren');

echo "\nMitgelieferter Datenstand\n";
$d = ma_sport_aktiv_daten();
pruefe('Datei gültig, Stand gesetzt', ma_sport_aktiv_gueltig($d), true);
$alle = array_merge(...array_values(ma_sport_aktiv_gruppen($d)));
pruefe('jeder Verein mit Quelle und Prüfdatum', count(array_filter($alle, fn($x) => str_starts_with((string) ($x['quelle'] ?? ''), 'https://') && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($x['geprueft'] ?? '')))), count($alle));
$ohneLink = 0;
foreach ($alle as $x) foreach ($x['teams'] as $t) if (!str_starts_with((string) $t['tabelleUrl'], 'https://')) $ohneLink++;
pruefe('jede Mannschaft mit Link zur Tabelle', $ohneLink, 0);
pruefe('keine nicht nachgewiesenen Vereine (Badminton, Luftsport, Discofox)', array_values(array_filter(array_column($alle, 'name'), fn($n) => preg_match('/Badminton|Aero|Discofox|Girbelsrath e\.V\.|Demons/u', $n))), []);
$GLOBALS['opt'] = ['stand' => '2099-01-01', 'vereine' => [['name' => 'neu', 'sportart' => 'schach', 'teams' => [['mannschaft' => '1']]]]];
pruefe('neuerer Stand aus dem Repository gewinnt', ma_sport_aktiv_daten()['stand'], '2099-01-01');
$GLOBALS['opt'] = ['stand' => '2000-01-01', 'vereine' => []];
pruefe('älterer geholter Stand wird ignoriert', ma_sport_aktiv_daten()['stand'], $d['stand']);
$GLOBALS['opt'] = ['stand' => 'Unsinn', 'vereine' => []];
pruefe('kaputter geholter Stand wird ignoriert', ma_sport_aktiv_daten()['stand'], $d['stand']);

echo "\nKeine selbstgemachten Grafiken\n";
pruefe('Ordner sportarten/*.svg entfernt', glob("$wurzel/wordpress/theme/merzenich-aktuell/assets/img/sportarten/*.svg"), []);
pruefe('Ersatzgrafiken ph-*.svg entfernt', glob("$wurzel/wordpress/theme/merzenich-aktuell/assets/img/ph-*.svg"), []);
pruefe('Sportbereich verweist auf keine SVG', str_contains((string) file_get_contents(MA_CORE_PATH . 'includes/sport-vereine.php'), '.svg'), false);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
