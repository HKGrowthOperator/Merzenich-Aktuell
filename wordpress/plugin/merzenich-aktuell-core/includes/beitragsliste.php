<?php
/**
 * Beiträge-Liste übersichtlicher (Wunsch Betreiber 07.10.2026): Filter über
 * der Liste für Startseite, Relevanz, Bild und Bildrechte; Sortieren nach
 * Relevanz zeigt auch Beiträge ohne Relevanz (am Ende) und ordnet bei
 * gleicher Relevanz nach Datum. Bis 1.24 fielen Beiträge ohne Relevanz beim
 * Sortieren aus der Liste („da fällt was raus“).
 * Seit 1.32.0 drei Stufen (1 Hoch, 2 Mitte, 3 Niedrig): Aufsteigend sortiert
 * steht Hoch oben; ohne Angabe zählt wie Mitte.
 */
if (!defined('ABSPATH')) { exit; }

/** Filter und ihre Werte: Schlüssel => [Beschriftung, [Wert => Text]]. */
function ma_beitragsliste_filter(): array {
    return [
        'ma_sf' => ['Startseite: alle', ['ja' => 'Startseite freigegeben', 'offen' => 'Startseite noch offen', 'nur-rubrik' => 'Nur in der Rubrik']],
        'ma_rel' => ['Relevanz: alle', ['hoch' => 'Relevanz 1 · Hoch', 'mittel' => 'Relevanz 2 · Mitte', 'niedrig' => 'Relevanz 3 · Niedrig', 'ohne' => 'ohne Relevanz (zählt wie Mitte)']],
        'ma_bild' => ['Bild: alle', ['ohne' => 'ohne Beitragsbild', 'rechte-offen' => 'Bildrechte nicht bestätigt']],
    ];
}

/** Meta-Bedingungen für die gewählten Filter (pure Funktion, testbar). */
function ma_beitragsliste_meta_query(array $wahl): array {
    $mq = [];
    switch ($wahl['ma_sf'] ?? '') {
        case 'ja': $mq[] = ['key' => 'ma_startseite_freigabe', 'value' => 'ja']; break;
        case 'nur-rubrik': $mq[] = ['key' => 'ma_startplatz', 'value' => 'aus']; break;
        case 'offen': $mq[] = ['relation' => 'AND', ['relation' => 'OR', ['key' => 'ma_startseite_freigabe', 'compare' => 'NOT EXISTS'], ['key' => 'ma_startseite_freigabe', 'value' => '']],
            ['relation' => 'OR', ['key' => 'ma_startplatz', 'compare' => 'NOT EXISTS'], ['key' => 'ma_startplatz', 'value' => 'aus', 'compare' => '!=']]]; break;
    }
    switch ($wahl['ma_rel'] ?? '') {
        case 'hoch': $mq[] = ['key' => 'ma_relevanz', 'value' => 1, 'compare' => '=', 'type' => 'NUMERIC']; break;
        case 'mittel': $mq[] = ['key' => 'ma_relevanz', 'value' => 2, 'compare' => '=', 'type' => 'NUMERIC']; break;
        case 'niedrig': $mq[] = ['key' => 'ma_relevanz', 'value' => 3, 'compare' => '=', 'type' => 'NUMERIC']; break;
        case 'ohne': $mq[] = ['relation' => 'OR', ['key' => 'ma_relevanz', 'compare' => 'NOT EXISTS'], ['key' => 'ma_relevanz', 'value' => ['', '0'], 'compare' => 'IN']]; break;
    }
    switch ($wahl['ma_bild'] ?? '') {
        case 'ohne': $mq[] = ['key' => '_thumbnail_id', 'compare' => 'NOT EXISTS']; break;
        case 'rechte-offen': $mq[] = ['key' => '_thumbnail_id', 'compare' => 'EXISTS']; $mq[] = ['relation' => 'OR', ['key' => 'ma_image_rights_verified', 'compare' => 'NOT EXISTS'], ['key' => 'ma_image_rights_verified', 'value' => '1', 'compare' => '!=']]; break;
    }
    return $mq;
}

add_action('restrict_manage_posts', function (string $typ): void {
    if ($typ !== 'post') return;
    foreach (ma_beitragsliste_filter() as $name => [$alle, $werte]) {
        $wahl = sanitize_key(wp_unslash($_GET[$name] ?? ''));
        printf('<select name="%s" aria-label="%s"><option value="">%s</option>', esc_attr($name), esc_attr($alle), esc_html($alle));
        foreach ($werte as $w => $t) printf('<option value="%s"%s>%s</option>', esc_attr($w), selected($wahl, $w, false), esc_html($t));
        echo '</select>';
    }
});

add_action('pre_get_posts', function (WP_Query $q): void {
    if (!is_admin() || !$q->is_main_query() || ($GLOBALS['pagenow'] ?? '') !== 'edit.php' || ($q->get('post_type') ?: 'post') !== 'post') return;
    $wahl = [];
    foreach (array_keys(ma_beitragsliste_filter()) as $n) $wahl[$n] = sanitize_key(wp_unslash($_GET[$n] ?? ''));
    $mq = ma_beitragsliste_meta_query($wahl);
    if ($q->get('orderby') === 'ma_relevanz') {
        // Sortierung über posts_orderby (unten): ohne Angabe zählt wie Mitte, nichts fällt heraus.
        $q->set('ma_rel_sortieren', strtoupper((string) $q->get('order')) === 'DESC' ? 'DESC' : 'ASC');
        $q->set('orderby', 'date');
    }
    if ($mq) $q->set('meta_query', ['relation' => 'AND'] + $mq);
});

/** ORDER BY für die Relevanz-Sortierung: Stufe (ohne Angabe = 2), dann das neueste zuerst. */
function ma_beitragsliste_relevanz_orderby(string $posts, string $postmeta, string $richtung): string {
    $richtung = $richtung === 'DESC' ? 'DESC' : 'ASC';
    return "COALESCE((SELECT CAST(pm.meta_value AS UNSIGNED) FROM {$postmeta} pm WHERE pm.post_id = {$posts}.ID AND pm.meta_key = 'ma_relevanz' AND pm.meta_value IN ('1','2','3') LIMIT 1), 2) {$richtung}, {$posts}.post_date DESC";
}
add_filter('posts_orderby', function (string $orderby, WP_Query $q): string {
    $r = (string) $q->get('ma_rel_sortieren');
    if ($r === '') return $orderby;
    global $wpdb;
    return ma_beitragsliste_relevanz_orderby($wpdb->posts, $wpdb->postmeta, $r);
}, 10, 2);
