<?php
/**
 * Kommende Termine für die Hauptabfrage von /termine/ und /termine/feed/ (1.45.1).
 *
 * Bis 1.45.0 setzte ma_filter_public_service_archives() eine meta_query mit OR, NOT EXISTS
 * und DATETIME-Vergleich über die Metatabelle. Gemessen am 03.11.2026: /termine/feed/
 * (13 KB) 2 bis 4 Sekunden, /feed/ 0,15 Sekunden. Jetzt: die IDs der kommenden Termine
 * werden einmal mit einfachen Abfragen bestimmt, 10 Minuten zwischengespeichert (neu bei
 * jeder Änderung eines Termins) und der Hauptabfrage als post__in übergeben. Die Regel ist
 * dieselbe: Ende ab jetzt, ohne Ende Beginn ab jetzt; sortiert nach Beginn.
 */
if (!defined('ABSPATH')) { exit; }

/**
 * Reine Funktion: $termine = [id => ['start' => Zeitstempel, 'ende' => Zeitstempel oder null]];
 * null heißt: kein Ende eingetragen. Liefert die IDs der kommenden Termine nach Beginn sortiert.
 */
function ma_termine_kommend_filtern(array $termine, int $jetzt): array {
    $kommend = array_filter($termine, fn($t) => (int) ($t['start'] ?? 0) > 0
        && ($t['ende'] !== null ? (int) $t['ende'] >= $jetzt : (int) $t['start'] >= $jetzt));
    uksort($kommend, fn($a, $b) => ((int) $kommend[$a]['start'] <=> (int) $kommend[$b]['start']) ?: ($a <=> $b));
    return array_map('intval', array_keys($kommend));
}

/** IDs der kommenden veröffentlichten Termine, 10 Minuten zwischengespeichert. */
function ma_termine_kommend_ids(): array {
    $schluessel = 'ma_termine_ids_' . md5((string) get_lastpostmodified('gmt', 'ma_event') . '|' . intdiv(time(), 600));
    $ids = get_transient($schluessel);
    if (is_array($ids)) return $ids;
    $alle = get_posts(['post_type' => 'ma_event', 'post_status' => 'publish', 'posts_per_page' => 1000, 'fields' => 'ids', 'no_found_rows' => true, 'suppress_filters' => true]);
    if ($alle) update_meta_cache('post', $alle);
    $termine = [];
    foreach ($alle as $id) {
        $endeRoh = (string) get_post_meta((int) $id, 'ma_event_end', true);
        $termine[(int) $id] = ['start' => ma_event_timestamp((int) $id, 'start'), 'ende' => $endeRoh !== '' ? ma_event_timestamp((int) $id, 'end') : null];
    }
    $ids = ma_termine_kommend_filtern($termine, time());
    set_transient($schluessel, $ids, 15 * MINUTE_IN_SECONDS);
    return $ids;
}
