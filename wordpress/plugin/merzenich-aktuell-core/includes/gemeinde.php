<?php
/**
 * Rathaus und Abfall aus derselben Quelle wie der statische Stand.
 *
 * data/gemeinde.json ist eine Kopie von deploy/gemeinde.json; deploy/
 * wordpress-import.mjs kopiert sie bei jedem Lauf der Kette, --check meldet
 * Abweichungen. So nennen /service/, das Startseiten-Cockpit und WordPress
 * dieselben Zeiten (Audit 28.09.2026: Dienstag geschlossen, Donnerstag bis
 * 18 Uhr; Muellabfuhr Schoenmackers 02237 9742-4502).
 *
 * Ausgabe: Shortcode [ma_gemeinde teil="rathaus"] bzw. teil="abfall".
 */
if (!defined('ABSPATH')) { exit; }

function ma_gemeinde_daten(): array {
    static $daten = null;
    if ($daten !== null) return $daten;
    $datei = dirname(__DIR__) . '/data/gemeinde.json';
    $json = is_readable($datei) ? json_decode((string)file_get_contents($datei), true) : null;
    return $daten = is_array($json) ? $json : [];
}

/** "Montag 8 bis 12:30 und 14 bis 16:30 Uhr, Dienstag geschlossen, ..." wie deploy/lib-gemeinde.mjs. */
function ma_gemeinde_zeiten_text(array $zeiten): string {
    $tage = [1 => 'Montag', 2 => 'Dienstag', 3 => 'Mittwoch', 4 => 'Donnerstag', 5 => 'Freitag'];
    $uhr = static function (string $s): string {
        [$h, $m] = array_pad(explode(':', $s), 2, '00');
        return $m === '00' ? (string)(int)$h : (int)$h . ':' . $m;
    };
    $teile = [];
    foreach ($tage as $nr => $name) {
        $z = $zeiten[$nr] ?? $zeiten[(string)$nr] ?? [];
        if (!is_array($z) || !$z) { $teile[] = $name . ' geschlossen'; continue; }
        $spannen = array_map(static fn($s) => $uhr((string)$s[0]) . ' bis ' . $uhr((string)$s[1]), $z);
        $teile[] = $name . ' ' . implode(' und ', $spannen) . ' Uhr';
    }
    return implode(', ', $teile);
}

function ma_gemeinde_stand(string $iso): string {
    return $iso !== '' ? implode('.', array_reverse(explode('-', $iso))) : '';
}

function ma_gemeinde_quelle(array $q): string {
    if (empty($q['url'])) return '';
    return '<p><small class="quelle">Quelle: <a href="' . esc_url($q['url']) . '" target="_blank" rel="noopener">'
        . esc_html($q['name'] ?? '') . '</a>, Stand ' . esc_html(ma_gemeinde_stand((string)($q['stand'] ?? ''))) . '.</small></p>';
}

function ma_gemeinde_shortcode($atts = []): string {
    $teil = is_array($atts) && isset($atts['teil']) ? (string)$atts['teil'] : 'rathaus';
    $d = ma_gemeinde_daten();
    if ($teil === 'abfall' && !empty($d['abfall'])) {
        $a = $d['abfall'];
        return '<div class="ma-gemeinde ma-gemeinde--abfall"><p>' . esc_html($a['text'] ?? '')
            . ' Telefon <a href="tel:' . esc_attr($a['telefonLink'] ?? '') . '">' . esc_html($a['telefon'] ?? '') . '</a>.'
            . ' Abfuhrtermine stehen im <a href="' . esc_url($a['kalender'] ?? '') . '" target="_blank" rel="noopener">Abfuhrkalender der Gemeinde</a>. '
            . esc_html($a['beratung'] ?? '') . '</p>' . ma_gemeinde_quelle((array)($a['quelle'] ?? [])) . '</div>';
    }
    if ($teil === 'rathaus' && !empty($d['rathaus'])) {
        $r = $d['rathaus'];
        return '<div class="ma-gemeinde ma-gemeinde--rathaus"><p>' . esc_html($r['adresse'] ?? '')
            . ' · Telefon <a href="tel:' . esc_attr($r['telefonLink'] ?? '') . '">' . esc_html($r['telefon'] ?? '') . '</a></p>'
            . '<p>Öffnungszeiten laut Gemeinde: ' . esc_html(ma_gemeinde_zeiten_text((array)($r['zeiten'] ?? []))) . '. '
            . esc_html($r['hinweis'] ?? '') . ' <a href="' . esc_url($r['termineOnline'] ?? '') . '" target="_blank" rel="noopener">Termin online buchen</a>.</p>'
            . ma_gemeinde_quelle((array)($r['quelle'] ?? [])) . '</div>';
    }
    if ($teil === 'notdienste') return ma_gemeinde_notdienste();
    return '';
}

/** Notrufe und Bereitschaftsdienste (bundesweit bzw. NRW), oben auf /service/ (1.21.0). */
function ma_gemeinde_notdienste(): string {
    $n = [
        ['112', '112', 'Feuerwehr und Rettungsdienst', 'Notruf bei Feuer, Unfall und lebensbedrohlichen Notfällen'],
        ['110', '110', 'Polizei', 'Notruf bei Straftaten und Gefahr'],
        ['116 117', '116117', 'Ärztlicher Bereitschaftsdienst', 'Wenn die Hausarztpraxis geschlossen ist: abends, nachts, am Wochenende und an Feiertagen'],
        ['0228 19240', '022819240', 'Giftnotruf NRW', 'Giftinformationszentrum Nordrhein-Westfalen in Bonn, rund um die Uhr'],
        ['0800 111 0 111', '08001110111', 'Telefonseelsorge', 'Kostenlos und anonym, rund um die Uhr'],
    ];
    $h = '<div class="ma-gemeinde ma-gemeinde--notdienste"><ul class="ma-notdienste">';
    foreach ($n as [$zeige, $tel, $titel, $text]) $h .= '<li><a href="tel:' . esc_attr($tel) . '"><b>' . esc_html($zeige) . '</b></a> <strong>' . esc_html($titel) . '</strong><br><span>' . esc_html($text) . '</span></li>';
    return $h . '</ul><p>Dienstbereite Apotheke in der Nähe: <a href="https://www.aponet.de/apotheke/notdienstsuche" target="_blank" rel="noopener">Notdienstsuche der Apotheken (aponet.de)</a>.</p></div>';
}

function ma_register_gemeinde_hooks(): void {
    add_shortcode('ma_gemeinde', 'ma_gemeinde_shortcode');
    // /service/ heißt im Menü „Notdienste & Rathaus“: Notrufe stehen oben, vor Rathaus und Abfall.
    add_filter('the_content', function (string $c): string {
        if (!is_page('service') || str_contains($c, 'ma-gemeinde--notdienste')) return $c;
        return '<h2 id="notdienste">Notrufe und Notdienste</h2>' . ma_gemeinde_notdienste() . $c;
    }, 12);
}
