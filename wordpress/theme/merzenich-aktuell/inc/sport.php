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

/* ------------------------------------------------------------------
 * Sportvereine der Gemeinde (16.10.2026): Unter den Sportmeldungen stehen alle
 * Sportvereine aus dem Vereinsverzeichnis des Plugins (ma_vereine_struktur,
 * Kategorie Sport; Abteilungen beim Hauptverein). Nur Angaben, die keine
 * laufende Pflege brauchen: Name, Ortsteil, Sportarten, Profil und Website.
 * Die Hinweise im Verzeichnis sind Recherchenotizen und erscheinen nicht.
 * ------------------------------------------------------------------ */

const MA21_SPORT_GRUPPEN = [
    'fussball' => 'Fußball',
    'turnen' => 'Turnen und Breitensport',
    'tisch' => 'Tischtennis und Billard',
    'schiessen' => 'Schießsport',
    'weitere' => 'Weitere Sportarten',
];

/** Gruppe nach der Sportart des Hauptvereins. */
function ma21_sport_gruppe(string $sportart): string {
    if (preg_match('/fußball|fussball/iu', $sportart) && !preg_match('/american/iu', $sportart)) return 'fussball';
    if (preg_match('/turnen|breitensport/iu', $sportart)) return 'turnen';
    if (preg_match('/tischtennis|billard/iu', $sportart)) return 'tisch';
    if (preg_match('/schie(ß|ss)/iu', $sportart)) return 'schiessen';
    return 'weitere';
}

/** „Turnen/Breitensport (u. a. Kinderturnen, Badminton)“ → [Turnen, Breitensport, Kinderturnen, Badminton]. */
function ma21_sport_sparten(string $sportart): array {
    $teile = [];
    $haupt = trim((string) preg_replace('/\(.*\)/u', '', $sportart));
    foreach (preg_split('/\s*[\/,]\s*/u', $haupt) ?: [] as $t) if (trim($t) !== '') $teile[] = trim($t);
    if (preg_match('/\((.*)\)/u', $sportart, $m)) {
        foreach (preg_split('/\s*,\s*/u', trim((string) preg_replace('/^u\.\s*a\.\s*/u', '', $m[1]))) ?: [] as $t) if (trim($t) !== '') $teile[] = trim($t);
    }
    return array_values(array_unique($teile));
}

/** Kürzel für das Vereinszeichen ohne Logo: SC, FC, TTC, BSC … */
function ma21_sport_kuerzel(string $name): string {
    if (preg_match('/\(([A-ZÄÖÜ]{2,4})\b/u', $name, $m)) return $m[1];
    $erstes = (string) preg_replace('/-.*/u', '', (string) strtok($name, ' '));
    if (preg_match('/^[A-ZÄÖÜ]{2,4}$/u', $erstes)) return $erstes;
    $bekannt = ['Tischtennisclub' => 'TTC', 'Turnverein' => 'TV', 'Schachverein' => 'SV'];
    if (isset($bekannt[$erstes])) return $bekannt[$erstes];
    $k = '';
    foreach (preg_split('/\s+/u', $name) ?: [] as $w) {
        if (preg_match('/^\d/u', $w) || mb_strlen($k) >= 3) break;
        if (preg_match('/^\p{Lu}/u', $w)) $k .= mb_substr($w, 0, 1);
    }
    return $k !== '' ? $k : mb_strtoupper(mb_substr($name, 0, 2));
}

/** Abschnitt „Sportvereine in der Gemeinde“ unter den Sportmeldungen ('' ohne Plugin). */
function ma21_sport_vereine(): string {
    if (!function_exists('ma_vereine_struktur')) return '';
    $orte = ['merzenich' => 0, 'golzheim' => 1, 'girbelsrath' => 2, 'morschenich' => 3, 'buergewald' => 4];
    $gruppen = array_fill_keys(array_keys(MA21_SPORT_GRUPPEN), []);
    foreach (ma_vereine_struktur() as $kurz => $e) {
        $v = (array) ($e['verein'] ?? []);
        if (($v['kategorie'] ?? '') !== 'Sport' || empty($v['name'])) continue;
        $gruppen[ma21_sport_gruppe((string) ($v['sportart'] ?? ''))][] = [$kurz, $v, (array) ($e['abteilungen'] ?? [])];
    }
    $gruppen = array_filter($gruppen);
    if (!$gruppen) return '';
    $anzahl = 0; $sprung = ''; $inhalt = '';
    foreach ($gruppen as $g => $liste) {
        usort($liste, fn($a, $b) => [$orte[$a[1]['ort'] ?? ''] ?? 9, $a[1]['name']] <=> [$orte[$b[1]['ort'] ?? ''] ?? 9, $b[1]['name']]);
        $anzahl += count($liste);
        $sprung .= '<a href="#sv-' . $g . '">' . esc_html(MA21_SPORT_GRUPPEN[$g]) . '<span>' . count($liste) . '</span></a>';
        $karten = '';
        foreach ($liste as [$kurz, $v, $abteilungen]) {
            $profil = function_exists('ma_verein_profil') ? ma_verein_profil((string) $kurz) : null;
            $profil = $profil instanceof WP_Post && $profil->post_status === 'publish' ? $profil : null;
            $bild = $profil && has_post_thumbnail($profil) ? (string) wp_get_attachment_image_url((int) get_post_thumbnail_id($profil), 'thumbnail') : '';
            $zeichen = $bild !== ''
                ? '<img class="sv-zeichen" src="' . esc_url($bild) . '" alt="" width="56" height="56" loading="lazy" decoding="async">'
                : '<span class="sv-zeichen" aria-hidden="true">' . esc_html(ma21_sport_kuerzel((string) $v['name'])) . '</span>';
            $sparten = '';
            foreach (ma21_sport_sparten((string) ($v['sportart'] ?? '')) as $s) $sparten .= '<li>' . esc_html($s) . '</li>';
            foreach ($abteilungen as $a) {
                foreach (ma21_sport_sparten((string) ($a['sportart'] ?? '')) as $s) {
                    if (str_contains($sparten, '>' . esc_html($s) . '<')) continue;
                    $sparten .= '<li>' . (!empty($a['website']) ? '<a href="' . esc_url((string) $a['website']) . '" target="_blank" rel="noopener">' . esc_html($s) . ' ↗</a>' : esc_html($s)) . '</li>';
                }
            }
            $links = ($profil ? '<a href="' . esc_url(get_permalink($profil)) . '">Vereinsprofil</a>' : '')
                . (!empty($v['website']) ? '<a href="' . esc_url((string) $v['website']) . '" target="_blank" rel="noopener">Website ↗</a>' : '');
            $karten .= '<li class="sv-verein">' . $zeichen . '<div class="sv-text"><span class="sv-ort">' . esc_html(MA21_ORTE[$v['ort'] ?? ''] ?? 'Gemeinde Merzenich') . '</span>'
                . '<h4>' . esc_html(html_entity_decode((string) $v['name'], ENT_QUOTES, 'UTF-8')) . '</h4>'
                . ($sparten !== '' ? '<ul class="sv-sparten" aria-label="Sportarten">' . $sparten . '</ul>' : '')
                . ($links !== '' ? '<p class="sv-links">' . $links . '</p>' : '') . '</div></li>';
        }
        $inhalt .= '<div class="sv-gruppe" id="sv-' . $g . '"><h3>' . esc_html(MA21_SPORT_GRUPPEN[$g]) . '</h3><ul class="sv-raster">' . $karten . '</ul></div>';
    }
    return '<section class="sport-vereinsliste" aria-labelledby="sv-titel"><div class="sv-kopf"><span class="eyebrow">Vereinssport in der Gemeinde</span>'
        . '<h2 id="sv-titel">Sportvereine in Merzenich</h2>'
        . '<p class="sv-dek">' . $anzahl . ' Vereine in Merzenich, Golzheim, Girbelsrath und Morschenich. Wer mitmachen oder zuschauen möchte, findet hier den Weg zum Verein.</p>'
        . '<nav class="sv-sprung" aria-label="Sportarten">' . $sprung . '</nav></div>'
        . $inhalt
        . '<p class="sv-fuss">Quelle: Vereinsverzeichnis der Gemeinde Merzenich. Abteilungen stehen beim Hauptverein. Fehlt ein Verein oder stimmt eine Angabe nicht? <a href="' . esc_url(home_url('/meldung-senden/')) . '">Bitte der Redaktion melden</a>.</p></section>';
}
