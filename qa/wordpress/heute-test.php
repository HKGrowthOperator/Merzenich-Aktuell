<?php
/**
 * „Heute in Merzenich“ (Theme 21.17.0, inc/termine.php) und die Logo-H1 der
 * Startseite (inc/ma21.php) ohne WordPress: Auswahl der Termine für heute und
 * die nächsten sieben Tage, Links in Kopf und Fuß an den echten Vorlagen,
 * genau eine H1 im Startseitenkopf, Aufmacher als H2.
 * Aufruf: php qa/wordpress/heute-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('DAY_IN_SECONDS', 86400);
function add_action(...$a) {} function add_filter(...$a) {} function remove_action(...$a) {} function add_image_size(...$a) {}
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return (string) $s; }
function wp_timezone() { return new DateTimeZone('Europe/Berlin'); }
function wp_date($f, $ts = null) { return (new DateTimeImmutable('@' . ($ts ?? time())))->setTimezone(wp_timezone())->format($f); }
class WP_Term {} class WP_Query {} class WP_Post { public $ID = 7; public $post_type = 'post'; }
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php';
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/termine.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-72s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
$ts = fn(string $s): int => (new DateTimeImmutable($s, wp_timezone()))->getTimestamp();
$t = fn(string $titel, string $start, string $ende = '') => ['titel' => $titel, 'start' => $ts($start), 'ende' => $ende !== '' ? $ts($ende) : $ts(substr($start, 0, 10) . 'T23:59:59')];
$titel = fn(array $liste) => array_map(fn($x) => $x['titel'], $liste);

echo "Auswahl (Mittwoch, 07.10.2026, 14:00 Uhr)\n";
$jetzt = $ts('2026-10-07T14:00');
$termine = [
    $t('Vorbei heute Morgen', '2026-10-07T09:00', '2026-10-07T11:00'),
    $t('Heute Abend', '2026-10-07T19:00'),
    $t('Läuft gerade (Markt)', '2026-10-07T10:00', '2026-10-07T18:00'),
    $t('Mehrtägig seit Montag', '2026-10-05T10:00', '2026-10-09T18:00'),
    $t('Ganztägig heute', '2026-10-07T00:00'),
    $t('Morgen', '2026-10-08T18:00'),
    $t('In sieben Tagen, spät', '2026-10-14T22:00'),
    $t('In acht Tagen', '2026-10-15T09:00'),
    $t('Ohne Datum', '1970-01-01T00:00'),
];
$a = ma21_heute_auswahl($termine, $jetzt);
pruefe('Heute: laufend, mehrtägig, ganztägig und später, nach Beginn sortiert', $titel($a['heute']), ['Mehrtägig seit Montag', 'Ganztägig heute', 'Läuft gerade (Markt)', 'Heute Abend']);
pruefe('Nächste sieben Tage: ab morgen bis einschließlich 14.10.', $titel($a['woche']), ['Morgen', 'In sieben Tagen, spät']);
pruefe('„Die nächsten Termine“ bleibt leer, solange es heute oder diese Woche etwas gibt', $a['naechste'], []);
$leer = ma21_heute_auswahl([$t('Im November', '2026-11-20T19:00'), $t('Im Dezember', '2026-12-05T15:00')], $jetzt);
pruefe('Nichts heute und diese Woche: die nächsten Termine', [$leer['heute'], $leer['woche'], $titel($leer['naechste'])], [[], [], ['Im November', 'Im Dezember']]);
pruefe('Höchstens fünf nächste Termine', count(ma21_heute_auswahl(array_map(fn($i) => $t("T$i", '2026-11-' . (10 + $i) . 'T10:00'), range(1, 8)), $jetzt)['naechste']), 5);
pruefe('Kurz vor Mitternacht: Termin um 0:30 gehört zu morgen', $titel(ma21_heute_auswahl([$t('Nach Mitternacht', '2026-10-08T00:30')], $ts('2026-10-07T23:50'))['woche']), ['Nach Mitternacht']);
pruefe('Zeitumstellung (25.10.): Tagesgrenze in Ortszeit', $titel(ma21_heute_auswahl([$t('Sonntagabend', '2026-10-25T23:30'), $t('Montagfrüh', '2026-10-26T00:15')], $ts('2026-10-25T12:00'))['heute']), ['Sonntagabend']);
pruefe('Datumszeile', ma21_heute_datum($jetzt), 'Mittwoch, 7. Oktober 2026');

echo "\nLinks auf /heute/ an den echten Vorlagen\n";
$v = __DIR__ . '/../../wordpress/theme/merzenich-aktuell/vorlagen/';
foreach (['kopf.html', 'kopf-seite.html'] as $datei) {
    $h = ma21_heute_links((string) file_get_contents($v . $datei), 'kopf', true);
    pruefe("$datei: Mehr-Menü und Schublade (Service) je einmal", [substr_count($h, 'href="/heute/"'), str_contains($h, '<li><a href="/heute/">Heute in Merzenich</a></li><li><a href="/service/">'), str_contains($h, '<div class="grp">Service</div><a href="/heute/">')], [2, true, true]);
    pruefe("$datei: zweimal anwenden ändert nichts", ma21_heute_links($h, 'kopf', true), $h);
}
$fuss = ma21_heute_links((string) file_get_contents($v . 'fuss.html'), 'fuss', true);
pruefe('fuss.html: einmal, direkt nach „Termine“', [substr_count($fuss, 'href="/heute/"'), str_contains($fuss, '<a href="/termine/">Termine</a><a href="/heute/">Heute in Merzenich</a>')], [1, true]);
pruefe('Ohne angelegte Seite keine Links (sonst 404)', ma21_heute_links('<li><a href="/service/">x</a></li>', 'kopf', false), '<li><a href="/service/">x</a></li>');
pruefe('Cockpit „Merzenich jetzt“ steht so in der Startseiten-Vorlage', str_contains((string) file_get_contents($v . 'startseite.html'), '<h2 class="mj-ort" id="mj-titel">Merzenich <span>jetzt</span></h2>'), true);

echo "\nStartseite: genau eine H1, das Logo\n";
$kopf = ma21_kopf_logo_h1((string) file_get_contents($v . 'kopf.html'));
pruefe('Logo-Link steht in der H1 mit Namen und Thema als Alt-Text', [substr_count($kopf, '<h1'), str_contains($kopf, '<h1 class="logo-h1"><a class="logo" href="/"><img src="/assets/img/logo-on-light.png" alt="Merzenich Aktuell – Nachrichten aus der Gemeinde Merzenich" width="600" height="169"></a></h1>')], [1, true]);
pruefe('Unterseiten-Kopf bleibt ohne H1', substr_count((string) file_get_contents($v . 'kopf-seite.html'), '<h1'), 0);
pruefe('Startseiten-Vorlage hat selbst keine H1', substr_count((string) file_get_contents($v . 'startseite.html'), '<h1'), 0);
$quelle = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php');
pruefe('Aufmacher (Größe xl) ist eine H2', [str_contains($quelle, 'ma21_marke($p) . "<h2><a href=\"{$url}\">{$titel}</a></h2><p>"'), str_contains($quelle, '"<h1><a href=')], [true, false]);
$css = (string) file_get_contents(__DIR__ . '/../../chatgpt-site/assets/startseite.css');
preg_match_all('/body\.home (?:\.buehne )?\.front-lead h1(?: a(?::hover)?)?(?=[,{])/', $css, $h1);
preg_match_all('/body\.home (?:\.buehne )?\.front-lead h2(?: a(?::hover)?)?(?=[,{])/', $css, $h2);
pruefe('Jede Regel für den Aufmacher gilt auch für die H2', count($h2[0]), count($h1[0]));

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
