<?php
/**
 * „Neu in Merzenich“ (Theme 21.26.0, Vorgabe Betreiber 03.11.2026: sortieren nach Wichtigkeit
 * „und trotzdem alle neuen Artikel in die News-Spalte“).
 *
 * Kopf der Spalte „Nachrichten aus Merzenich“ auf der Startseite: alle Meldungen der letzten
 * 48 Stunden, neueste zuerst, mindestens fünf, höchstens zehn. Auch was schon im Aufmacher, auf
 * der Bühne oder in einer Rubrik steht, denn die Liste ist der Überblick über das Neueste; die
 * Wichtigkeit (Relevanz) bestimmt weiter Aufmacher, Bühne und Rubrikflächen. Nur Text: kein Foto
 * doppelt auf der Seite. „Nur in der Rubrik“ (Startseiten-Freigabe aus) bleibt draußen.
 */
if (!defined('ABSPATH')) { exit; }

/** Wie viele Einträge einer nach Datum absteigenden Liste (Unix-Zeiten) gezeigt werden. Rein, ohne WordPress. */
function ma21_neueste_anzahl(array $zeiten, int $jetzt, int $stunden = 48, int $min = 5, int $max = 10): int {
    $n = 0;
    foreach (array_values($zeiten) as $i => $t) {
        if ($n >= $max) break;
        if ($i >= $min && $jetzt - (int) $t > $stunden * 3600) break;
        $n++;
    }
    return $n;
}

/** Zeitangabe der Liste: heute nur die Uhrzeit, gestern „gestern, HH:MM“, sonst „TT.MM., HH:MM“. Rein. */
function ma21_neueste_wann(int $t, int $jetzt, ?DateTimeZone $tz = null): string {
    $tz = $tz ?: new DateTimeZone('Europe/Berlin');
    $d = (new DateTimeImmutable('@' . $t))->setTimezone($tz);
    $heute = (new DateTimeImmutable('@' . $jetzt))->setTimezone($tz);
    if ($d->format('Y-m-d') === $heute->format('Y-m-d')) return $d->format('H:i') . ' Uhr';
    if ($d->format('Y-m-d') === $heute->modify('-1 day')->format('Y-m-d')) return 'gestern, ' . $d->format('H:i');
    return $d->format('d.m., H:i');
}

function ma21_neueste_html(): string {
    if (!function_exists('get_posts')) return '';
    $ps = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 30, 'orderby' => 'date', 'order' => 'DESC', 'suppress_filters' => false]);
    if (function_exists('ma21_startseite_frei')) $ps = array_values(array_filter($ps, 'ma21_startseite_frei'));
    $zeiten = array_map(fn($p) => (int) get_post_time('U', true, $p), $ps);
    $n = ma21_neueste_anzahl($zeiten, time());
    if (!$n) return '';
    $h = '';
    foreach (array_slice($ps, 0, $n) as $i => $p) {
        $rubrik = function_exists('ma21_ressort') ? ma21_ressort($p)[1] : '';
        $titel = html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8');
        $h .= '<li><time datetime="' . esc_attr((string) get_post_time('c', false, $p)) . '">' . esc_html(ma21_neueste_wann($zeiten[$i], time())) . '</time>'
            . ($rubrik !== '' ? '<span class="neueste-rubrik">' . esc_html($rubrik) . '</span>' : '')
            . '<a href="' . esc_url(get_permalink($p)) . '">' . esc_html($titel) . '</a></li>';
    }
    return '<div class="neueste"><h3 class="neueste-titel">Neu in Merzenich</h3><ol class="neueste-liste">' . $h . '</ol></div>';
}
