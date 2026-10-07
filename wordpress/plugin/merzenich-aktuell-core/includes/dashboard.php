<?php
/**
 * Dashboard übersichtlicher (Wunsch Betreiber 07.10.2026): oben die Kachel
 * „Heute zu tun“ mit je einer Zahl und einem Knopf (Freigaben, Eingang,
 * Kommentare, Sport, bald endende Anzeigen), darunter „Live“ und die
 * Redaktionskachel. Die WordPress-Kästen Site Health, Ereignisse & News und
 * Schneller Entwurf entfallen für Redaktion und Administration.
 */
if (!defined('ABSPATH')) { exit; }

/**
 * Veröffentlichte Anzeigen, die in den nächsten 7 Tagen enden. Werbung speichert
 * ihr Ende in ma_ad_end, alle anderen Anzeigenarten in ma_end_at.
 * @return int[]
 */
function ma_dashboard_bald_endend(): array {
    $zeitraum = [current_time('Y-m-d'), wp_date('Y-m-d', time() + 7 * DAY_IN_SECONDS)];
    $abfrage = fn(array $typen, string $feld): array => get_posts(['post_type' => $typen, 'post_status' => 'publish', 'posts_per_page' => 99, 'fields' => 'ids',
        'meta_query' => [['key' => $feld, 'value' => $zeitraum, 'compare' => 'BETWEEN', 'type' => 'DATE']]]);
    return array_values(array_unique(array_merge($abfrage(['ma_job', 'ma_property', 'ma_obituary', 'ma_family_notice', 'ma_tip'], 'ma_end_at'), $abfrage(['ma_ad'], 'ma_ad_end'))));
}

/** Liste für „Ansehen“: die Anzeigenart mit den meisten bald endenden Einträgen (sonst Stellen). */
function ma_dashboard_bald_typ(array $ids): string {
    $typen = array_count_values(array_map('get_post_type', $ids));
    arsort($typen);
    return (string) (array_key_first($typen) ?: 'ma_job');
}

/** Zahlen für „Heute zu tun“: [Schlüssel => [Zahl, Text, Knopf, Link, wichtig]]. */
function ma_dashboard_aufgaben(): array {
    $abgleich = function_exists('ma_freigaben_abgleich') ? count(ma_freigaben_abgleich(true)) : 0;
    $eingereicht = count(get_posts(['post_type' => function_exists('ma_freigabe_typen') ? ma_freigabe_typen() : 'post', 'post_status' => 'pending', 'posts_per_page' => 199, 'fields' => 'ids']));
    $eingang = (int) (wp_count_posts('ma_eingang')->ma_neu ?? 0);
    $kommentare = (int) (wp_count_comments()->moderated ?? 0);
    $sport = function_exists('ma_sport_ueberfaellig') ? ma_sport_ueberfaellig() : '';
    $bald = ma_dashboard_bald_endend();
    $freigaben = $abgleich + $eingereicht;
    return [
        'freigaben' => [$freigaben, $freigaben === 1 ? 'Meldung wartet auf Freigabe' : 'Meldungen warten auf Freigabe', 'Freigeben', admin_url('admin.php?page=ma-freigaben'), $freigaben > 0],
        'eingang' => [$eingang, $eingang === 1 ? 'neue Einsendung im Eingang' : 'neue Einsendungen im Eingang', 'Eingang öffnen', admin_url('edit.php?post_type=ma_eingang'), $eingang > 0],
        'kommentare' => [$kommentare, $kommentare === 1 ? 'Kommentar wartet' : 'Kommentare warten', 'Sammelfreigabe', admin_url('edit-comments.php?page=ma-kommentar-freigabe'), $kommentare > 0],
        'sport' => [$sport !== '' ? 1 : 0, $sport !== '' ? 'Sportergebnis fehlt' : 'Sport ist aktuell', 'Sport eintragen', admin_url('admin.php?page=ma-sport'), $sport !== ''],
        'anzeigen' => [count($bald), count($bald) === 1 ? 'Anzeige endet in 7 Tagen' : 'Anzeigen enden in 7 Tagen', 'Ansehen', admin_url('edit.php?post_type=' . ma_dashboard_bald_typ($bald)), false],
    ];
}

function ma_dashboard_heute(): void {
    $a = ma_dashboard_aufgaben();
    echo '<div class="ma-heute">';
    foreach ($a as $k => [$zahl, $text, $knopf, $link, $wichtig]) {
        printf('<a class="ma-heute__kachel%s" href="%s"><span class="ma-heute__zahl">%s</span><span class="ma-heute__text">%s</span><span class="button%s">%s</span></a>',
            $wichtig ? ' ist-wichtig' : '', esc_url($link), $k === 'sport' ? ($zahl ? '!' : '✓') : (int) $zahl, esc_html($text), $wichtig ? ' button-primary' : '', esc_html($knopf));
    }
    echo '</div><p class="ma-heute__wege"><a href="' . esc_url(admin_url('admin.php?page=ma-startseite')) . '">Startseite &amp; Ressorts anordnen</a> · <a href="' . esc_url(admin_url('post-new.php')) . '">Neue Meldung</a> · <a href="' . esc_url(admin_url('post-new.php?post_type=ma_event')) . '">Neuer Termin</a> · <a href="' . esc_url(home_url('/')) . '" target="_blank" rel="noopener">Seite ansehen ↗</a></p>';
    echo '<style>.ma-heute{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px}.ma-heute__kachel{display:flex;flex-direction:column;gap:6px;padding:12px;border:1px solid #dcdcde;border-radius:6px;background:#fff;color:#1d2327;text-decoration:none}.ma-heute__kachel:hover{border-color:#2271b1}.ma-heute__kachel.ist-wichtig{border-color:#b32d2e;background:#fcf0f1}.ma-heute__zahl{font-size:30px;font-weight:700;line-height:1}.ist-wichtig .ma-heute__zahl{color:#b32d2e}.ma-heute__text{font-size:13px;line-height:1.3;min-height:34px}.ma-heute__kachel .button{align-self:flex-start}.ma-heute__wege{margin:12px 0 0}</style>';
}

add_action('wp_dashboard_setup', function (): void {
    if (!current_user_can('edit_others_posts') || (function_exists('ma_current_partner_policy') && ma_current_partner_policy())) return;
    wp_add_dashboard_widget('ma_heute_widget', 'Heute zu tun', 'ma_dashboard_heute');
    // Weniger Kästen: Site Health, Ereignisse & News und Schneller Entwurf braucht die Redaktion nicht.
    remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
    remove_meta_box('dashboard_primary', 'dashboard', 'side');
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
}, 20);

/* Reihenfolge: „Heute zu tun“ zuerst, dann „Live“, dann die Redaktionskachel (vor den übrigen). */
add_action('wp_dashboard_setup', function (): void {
    global $wp_meta_boxes;
    $normal = $wp_meta_boxes['dashboard']['normal']['core'] ?? [];
    $vorne = [];
    foreach (['ma_heute_widget', 'ma_live_widget', 'ma_redaktion_widget'] as $id) if (isset($normal[$id])) { $vorne[$id] = $normal[$id]; unset($normal[$id]); }
    if ($vorne) $wp_meta_boxes['dashboard']['normal']['core'] = $vorne + $normal;
}, 99);
