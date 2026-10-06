<?php
/**
 * Spielstand-Ecke und Sportmodul auf /sport/ zur Laufzeit (21.13.0), Markup wie
 * deploy/sport-prerender.mjs. Bis dahin standen beide fest in der Vorlage vom
 * Bautag (vorlagen/sport-ecke.html, sport-modul.html) und zeigten am 06.10.2026
 * noch den Stand vom 23.09. (7. Platz), obwohl im Backend der 5. Platz und das
 * 5:0 vom 02.10. eingetragen waren. Daten: dieselben wie /api/sport-current.json
 * (ma21_sport_daten). Ohne Daten bleibt die Vorlage.
 */
if (!defined('ABSPATH')) { exit; }

/** Spielstand wie /api/sport-current.json: Backend, wenn mindestens so neu wie das Repository, sonst Repository bzw. Notkopie. */
function ma21_sport_daten(): ?array {
    $repo = json_decode(ma21_repo_datei('api/sport-current.json', 15 * MINUTE_IN_SECONDS), true);
    $backend = function_exists('ma_sport_als_json') ? ma_sport_als_json() : null;
    if (is_array($backend) && (!is_array($repo) || strtotime((string) $backend['generated']) >= strtotime((string) ($repo['generated'] ?? '')))) return $backend;
    if (!is_array($repo)) $repo = json_decode((string) get_option('ma21_sport_current_kopie', ''), true);
    return is_array($repo) && isset($repo['generated'], $repo['table']) ? $repo : null;
}

/** Zeile des SC 1919 Merzenich in der Tabelle. */
function ma21_sport_heim(array $d): ?array {
    foreach ((array) ($d['table'] ?? []) as $r) if (!empty($r['homeTeam']) || preg_match('/Merzenich/i', (string) ($r['team'] ?? ''))) return $r;
    return null;
}

function ma21_sport_ts(string $iso): int { return (int) strtotime($iso); }
function ma21_sport_stand(array $d): string { $t = ma21_sport_ts((string) $d['generated']); return 'Datenstand ' . wp_date('d.m.Y', $t) . ', ' . wp_date('H:i', $t) . ' Uhr'; }

/** Rechte Ecke im Seitenkopf: Platz, Punkte, Tore, nächstes Spiel. */
function ma21_sport_ecke(array $d, ?int $jetzt = null): string {
    $heim = ma21_sport_heim($d);
    if (!$heim) return '';
    $jetzt ??= time();
    $n = !empty($d['nextMatch']['date']) && ma21_sport_ts((string) $d['nextMatch']['date']) > $jetzt ? $d['nextMatch'] : null;
    $kachel = fn(string $wert, string $label): string => '<div class="sc-kachel"><span class="sc-wert">' . esc_html($wert) . '</span><span class="sc-label">' . $label . '</span></div>';
    $tage = ['So.', 'Mo.', 'Di.', 'Mi.', 'Do.', 'Fr.', 'Sa.'];
    $spiel = '';
    if ($n) {
        $t = ma21_sport_ts((string) $n['date']);
        $spiel = '<p class="sport-ecke-spiel"><b>Nächstes Spiel</b> <time datetime="' . esc_attr((string) $n['date']) . '">' . esc_html($tage[(int) wp_date('w', $t)] . ', ' . wp_date('d.m.', $t) . ', ' . wp_date('H:i', $t) . ' Uhr') . '</time><br>' . esc_html($n['home'] . ' gegen ' . $n['away']) . '</p>';
    }
    return '<aside class="sport-ecke" aria-label="SC 1919 Merzenich in der Kreisliga A"><p class="sport-ecke-kopf"><a href="/vereine/sc-1919-merzenich/">' . esc_html((string) $heim['team']) . '</a><span>Kreisliga A</span></p>'
        . '<div class="sc-stand">' . $kachel($heim['place'] . '.', 'Platz') . $kachel((string) $heim['points'], 'Punkte') . $kachel((string) $heim['goals'], 'Tore') . '</div>'
        . $spiel . '<p class="sport-ecke-quelle">' . esc_html(ma21_sport_stand($d) . ' · Quelle ' . ($d['source'] ?? 'FUSSBALL.DE')) . '</p><!--/sport-ecke--></aside>';
}

/** Sportmodul: letztes Spiel, nächstes (oder offenes) Spiel, Tabellenauszug. */
function ma21_sport_modul(array $d, ?int $jetzt = null): string {
    $jetzt ??= time();
    $quelle = (string) ($d['source'] ?? 'FUSSBALL.DE');
    $stand = ma21_sport_stand($d);
    $dmy = fn(string $iso): string => wp_date('d.m.Y', ma21_sport_ts($iso));
    $hm = fn(string $iso): string => wp_date('H:i', ma21_sport_ts($iso)) . ' Uhr';
    $team = fn(string $name): string => '<div class="match-team">' . (preg_match('/Merzenich/i', $name) ? '<img src="/assets/uploads/sc-1919-merzenich-logo.webp" width="58" height="58" alt="Vereinslogo SC 1919 Merzenich" loading="lazy">' : '') . '<b>' . esc_html($name) . '</b></div>';
    $kopf = fn(string $titel, string $iso, string $zusatz): string => '<div class="match-heading"><b>' . $titel . '</b><time datetime="' . esc_attr($iso) . '">' . esc_html($dmy($iso) . ' · ' . $zusatz) . '</time></div>';
    $h = '';
    if (!empty($d['lastMatch']['date'])) {
        $m = $d['lastMatch'];
        if (($m['confirmed'] ?? true) !== false && (string) ($m['score'] ?? '') !== '') {
            $bericht = !empty($m['reportUrl']) ? '<a class="match-source" href="' . esc_url((string) $m['reportUrl']) . '" target="_blank" rel="noopener">' . (preg_match('/fussball\.de/i', (string) $m['reportUrl']) ? 'Spielbericht bei FUSSBALL.DE' : 'Spielbericht beim Verein') . '</a>' : '';
            $h .= '<section class="match-panel" aria-label="Letztes Spielergebnis">' . $kopf('Letztes Spiel', (string) $m['date'], 'Endstand') . '<div class="match-grid">' . $team((string) $m['home']) . ' <strong class="match-score">' . esc_html((string) $m['score']) . '</strong> ' . $team((string) $m['away']) . '</div>' . $bericht . '</section>';
        } else {
            $h .= '<section class="match-panel offen" aria-label="Begegnung mit offenem Ergebnis">' . $kopf('Ergebnis noch offen', (string) $m['date'], $hm((string) $m['date'])) . '<div class="match-grid">' . $team((string) $m['home']) . ' <span class="match-versus">gegen</span> ' . $team((string) $m['away']) . '</div><span class="match-offen-hinweis">Ergebnis noch nicht bestätigt</span></section>';
        }
    }
    if (!empty($d['nextMatch']['date'])) {
        $m = $d['nextMatch'];
        if (ma21_sport_ts((string) $m['date']) < $jetzt) {
            // Angesetztes Spiel vorbei, Ergebnis noch nicht eingetragen: offen, mit Weg zur Quelle.
            $h .= '<section class="match-panel offen" aria-label="Begegnung mit offenem Ergebnis">' . $kopf('Ergebnis noch offen', (string) $m['date'], $hm((string) $m['date'])) . '<div class="match-grid">' . $team((string) $m['home']) . ' <span class="match-versus">gegen</span> ' . $team((string) $m['away']) . '</div><span class="match-offen-hinweis">Ergebnis noch nicht bestätigt</span><a class="match-source" href="' . esc_url((string) ($d['sourceUrl'] ?? 'https://www.fussball.de/')) . '" target="_blank" rel="noopener">Ergebnis und Spielplan bei ' . esc_html($quelle) . '</a></section>';
        } else {
            $h .= '<section class="match-panel" aria-label="Nächstes Spiel">' . $kopf('Nächstes Spiel', (string) $m['date'], $hm((string) $m['date'])) . '<div class="match-grid">' . $team((string) $m['home']) . ' <span class="match-versus">gegen</span> ' . $team((string) $m['away']) . '</div><a class="match-source" href="/vereine/sc-1919-merzenich/">Zum Vereinskanal</a></section>';
        }
    }
    $zeilen = (array) ($d['table'] ?? []);
    if ($zeilen) {
        $heim = ma21_sport_heim($d);
        $auszug = array_slice($zeilen, 0, 5);
        $ausserhalb = $heim && !in_array($heim, $auszug, true);
        $zeile = fn(array $r): string => '<tr' . ($r === $heim ? ' class="home-team"' : '') . '><td>' . (int) $r['place'] . '</td><th scope="row">' . esc_html((string) $r['team']) . '</th><td>' . (int) $r['played'] . '</td><td>' . (int) $r['wins'] . '</td><td>' . (int) $r['draws'] . '</td><td>' . (int) $r['losses'] . '</td><td>' . esc_html((string) $r['goals']) . '</td><td>' . ((int) $r['diff'] > 0 ? '+' : '') . (int) $r['diff'] . '</td><td>' . (int) $r['points'] . '</td></tr>';
        $body = implode('', array_map($zeile, $auszug)) . ($ausserhalb ? '<tr class="league-gap" aria-hidden="true"><td colspan="9">···</td></tr>' . $zeile($heim) : '');
        $beschreibung = $ausserhalb ? 'erste fünf Mannschaften und SC 1919 Merzenich' : 'erste fünf Mannschaften';
        $h .= '<section class="league-panel"><div class="league-heading"><h3>Kreisliga A · Tabellenauszug</h3><span>' . esc_html($stand) . '</span></div><div class="league-scroll" tabindex="0" role="region" aria-label="Fußballtabelle, horizontal scrollbar"><table class="league-table"><caption class="sr-only">Kreisliga A, ' . $beschreibung . '. ' . esc_html($stand) . '</caption><thead><tr><th scope="col">Pl.</th><th scope="col">Mannschaft</th><th scope="col">Sp.</th><th scope="col">S</th><th scope="col">U</th><th scope="col">N</th><th scope="col">Tore</th><th scope="col">Diff.</th><th scope="col">Pkt.</th></tr></thead><tbody>' . $body . '</tbody></table></div><a href="/vereine/sc-1919-merzenich/">Vollständige Tabelle &amp; Spielplan</a></section>';
    }
    return $h === '' ? '' : '<div class="sports-module" data-generated="' . esc_attr((string) $d['generated']) . '">' . $h . '</div>';
}
