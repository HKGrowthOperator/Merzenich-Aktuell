<?php
/**
 * Kostenfreier Testeintrag (16.10.2026, Auftrag Betreiber): ein gesponserter
 * Beitrag eines Unternehmens, der nichts kostet. Er bleibt Werbung und wird so
 * gekennzeichnet („Anzeige · kostenfreier Testeintrag“), aber nicht als
 * „Bezahlte Präsentation“.
 *
 * - Haken im Kasten „Kennzeichnung“ (werbung-stat.php): Beitrags-Meta
 *   ma_gesponsert_art = test.
 * - Der Zeitraum beginnt erst mit der Veröffentlichung (ma_test_start) und
 *   endet nach MA_TEST_TAGE Tagen (ma_test_ende). Danach archiviert ein
 *   täglicher Lauf den Beitrag: nicht mehr öffentlich, keine Verlängerung,
 *   keine Rechnung. Das Unternehmensprofil bleibt davon unberührt.
 *
 * Oben in der Datei nur Funktionen und add_action/add_filter: der Test
 * qa/wordpress/testeintrag-test.php lädt sie mit Ersatzfunktionen.
 */
if (!defined('ABSPATH')) { exit; }

const MA_TEST_TAGE = 30;
const MA_TEST_HOOK = 'ma_testeintraege_pruefen';

/** Ist der Beitrag ein kostenfreier Testeintrag? */
function ma_ist_testeintrag($post): bool {
    $id = $post instanceof WP_Post ? $post->ID : (int) $post;
    return $id > 0 && (string) get_post_meta($id, 'ma_gesponsert', true) === '1' && (string) get_post_meta($id, 'ma_gesponsert_art', true) === 'test';
}

/** Kennzeichnung auf der Seite: „Anzeige · kostenfreier Testeintrag“ oder wie bisher „Anzeige · Gesponsert“. */
function ma_gesponsert_label($post): string {
    return ma_ist_testeintrag($post) ? 'Anzeige · kostenfreier Testeintrag' : 'Anzeige · Gesponsert';
}

/** Zusatz im Hinweis unter der Überschrift. */
function ma_gesponsert_hinweis($post): string {
    if (!ma_ist_testeintrag($post)) return 'Bezahlte Präsentation';
    $id = $post instanceof WP_Post ? $post->ID : (int) $post;
    $ende = (string) get_post_meta($id, 'ma_test_ende', true);
    return 'Kostenfreie Präsentation im Testzeitraum' . ($ende !== '' && ($ts = strtotime($ende)) ? ' bis ' . gmdate('d.m.Y', $ts) : '');
}

/** Ende des Testzeitraums: Start plus Tage, im Format der Datenbank (Y-m-d H:i:s). */
function ma_test_ende_berechnen(string $start, int $tage = MA_TEST_TAGE): string {
    $ts = strtotime($start . ' UTC');
    return $ts ? gmdate('Y-m-d H:i:s', $ts + $tage * 86400) : '';
}

/** Ist der Zeitraum vorbei? Ohne Ende nie (noch nicht veröffentlicht). Beide Zeiten in Ortszeit der Installation. */
function ma_test_abgelaufen(string $ende, string $jetzt): bool {
    return $ende !== '' && strcmp($jetzt, $ende) >= 0;
}

/**
 * Zeitraum beginnt mit der ersten Veröffentlichung, nicht mit dem Anlegen des
 * Entwurfs. Einmal gesetzt, bleibt das Ende: erneutes Veröffentlichen
 * verlängert nicht.
 */
function ma_test_starten(int $id): bool {
    if (get_post_status($id) !== 'publish' || !ma_ist_testeintrag($id) || (string) get_post_meta($id, 'ma_test_ende', true) !== '') return false;
    $start = (string) current_time('mysql');
    update_post_meta($id, 'ma_test_start', $start);
    update_post_meta($id, 'ma_test_ende', ma_test_ende_berechnen($start));
    if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Testzeitraum begonnen', MA_TEST_TAGE . ' Tage, kostenfrei, endet am ' . mysql2date('d.m.Y', ma_test_ende_berechnen($start)) . ' von selbst.');
    return true;
}
/* Nach dem Speichern der Felder (save_post): Der Haken „Kostenfreier Testeintrag“
   kann im selben Speichern wie „Veröffentlichen“ gesetzt werden, im Block-Editor
   kommt der Kasten sogar erst in einer zweiten Anfrage. transition_post_status
   liefe davor und sähe den Haken noch nicht. */
add_action('wp_after_insert_post', function (int $id, $post): void {
    if (!$post instanceof WP_Post || $post->post_type !== 'post') return;
    ma_test_starten($id);
}, 10, 2);

/* Täglich: abgelaufene Testeinträge archivieren (nicht mehr öffentlich). */
add_action('init', function (): void {
    if (function_exists('wp_next_scheduled') && !wp_next_scheduled(MA_TEST_HOOK)) wp_schedule_event(time() + 600, 'daily', MA_TEST_HOOK);
});
add_action(MA_TEST_HOOK, 'ma_testeintraege_pruefen');
function ma_testeintraege_pruefen(): int {
    $jetzt = (string) current_time('mysql');
    $ids = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 100, 'fields' => 'ids',
        'meta_query' => [['key' => 'ma_gesponsert_art', 'value' => 'test'], ['key' => 'ma_test_ende', 'value' => $jetzt, 'compare' => '<=', 'type' => 'DATETIME']]]);
    $n = 0;
    foreach ((array) $ids as $id) {
        if (!ma_test_abgelaufen((string) get_post_meta((int) $id, 'ma_test_ende', true), $jetzt)) continue;
        $status = defined('MA_STATUS_ARCHIV') ? MA_STATUS_ARCHIV : 'draft';
        wp_update_post(['ID' => (int) $id, 'post_status' => $status]);
        if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen((int) $id, 'Testzeitraum beendet', 'Kostenfreier Testeintrag nach ' . MA_TEST_TAGE . ' Tagen archiviert; keine Verlängerung.');
        $n++;
    }
    return $n;
}
