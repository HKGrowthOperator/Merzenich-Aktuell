<?php
/**
 * Beiträge-Liste übersichtlicher (Wunsch Betreiber 07.10.2026): Filter über
 * der Liste für Startseite, Relevanz, Bild und Bildrechte; Sortieren nach
 * Relevanz zeigt auch Beiträge ohne Relevanz (am Ende) und ordnet bei
 * gleicher Relevanz nach Datum. Bis 1.24 fielen Beiträge ohne Relevanz beim
 * Sortieren aus der Liste („da fällt was raus“).
 */
if (!defined('ABSPATH')) { exit; }

/** Filter und ihre Werte: Schlüssel => [Beschriftung, [Wert => Text]]. */
function ma_beitragsliste_filter(): array {
    return [
        'ma_sf' => ['Startseite: alle', ['ja' => 'Startseite freigegeben', 'offen' => 'Startseite noch offen', 'nur-rubrik' => 'Nur in der Rubrik']],
        'ma_rel' => ['Relevanz: alle', ['hoch' => 'Relevanz hoch (8–10)', 'mittel' => 'Relevanz mittel (4–7)', 'niedrig' => 'Relevanz niedrig (1–3)', 'ohne' => 'ohne Relevanz']],
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
        case 'hoch': $mq[] = ['key' => 'ma_relevanz', 'value' => 8, 'compare' => '>=', 'type' => 'NUMERIC']; break;
        case 'mittel': $mq[] = ['key' => 'ma_relevanz', 'value' => [4, 7], 'compare' => 'BETWEEN', 'type' => 'NUMERIC']; break;
        case 'niedrig': $mq[] = ['key' => 'ma_relevanz', 'value' => [1, 3], 'compare' => 'BETWEEN', 'type' => 'NUMERIC']; break;
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
        // Beiträge ohne Relevanz bleiben in der Liste; bei gleicher Relevanz das neueste zuerst.
        $mq['ma_rel_sort'] = ['relation' => 'OR', 'ma_rel_da' => ['key' => 'ma_relevanz', 'type' => 'NUMERIC', 'compare' => 'EXISTS'], ['key' => 'ma_relevanz', 'compare' => 'NOT EXISTS']];
        $q->set('orderby', ['ma_rel_da' => strtoupper((string) $q->get('order')) === 'ASC' ? 'ASC' : 'DESC', 'date' => 'DESC']);
    }
    if ($mq) $q->set('meta_query', ['relation' => 'AND'] + $mq);
});
