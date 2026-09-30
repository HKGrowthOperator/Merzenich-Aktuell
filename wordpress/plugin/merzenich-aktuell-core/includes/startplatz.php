<?php
/**
 * Startplatz: Die Redaktion bestimmt, wo eine Meldung steht (30.09.2026).
 *
 * Meta ma_startplatz je Beitrag:
 *   auto        automatisch nach Relevanz 1–10 (includes/relevanz.php), sonst
 *               füllt die jüngste passende Meldung freie Plätze
 *   aufmacher   großer Aufmacher oben
 *   buehne-1…4  Bühne: 1–2 rechts neben dem Aufmacher, 3–4 darunter
 *               (der Platz unten rechts ist eine Anzeige)
 *   aus         nur in der eigenen Rubrik, nie auf der Startseite
 *
 * Jeder feste Platz hat genau eine Meldung. Wird er neu vergeben, fällt die
 * bisherige Meldung auf „automatisch“ zurück und steht wieder in ihrer
 * Rubrik (Theme: ma21_startseite_belegung()).
 *
 * Bedienung: Box „Startseite“ im Beitrag und die Übersicht
 * Merzenich Aktuell → Startseite. Partner sehen beides nicht.
 */
if (!defined('ABSPATH')) { exit; }

function ma_startplaetze(): array {
    return [
        'auto' => 'Automatisch',
        'aufmacher' => 'Aufmacher (groß oben)',
        'buehne-1' => 'Bühne 1 (rechts oben)',
        'buehne-2' => 'Bühne 2 (rechts unten)',
        'buehne-3' => 'Bühne 3 (unten links)',
        'buehne-4' => 'Bühne 4 (unten Mitte)',
        'aus' => 'Nur in der Rubrik (nicht auf der Startseite)',
    ];
}

function ma_startplatz_darf(): bool {
    if (function_exists('ma_current_partner_policy') && ma_current_partner_policy()) return false;
    return current_user_can('edit_others_posts');
}

/** Wer steht gerade auf einem festen Platz? */
function ma_startplatz_inhaber(string $platz): ?WP_Post {
    $q = get_posts(['post_type' => 'post', 'post_status' => ['publish', 'future', 'draft', 'pending'], 'posts_per_page' => 1,
        'meta_key' => 'ma_startplatz', 'meta_value' => $platz, 'orderby' => 'modified', 'order' => 'DESC']);
    return $q[0] ?? null;
}

/** Platz setzen und den bisherigen Inhaber auf „automatisch“ zurückstellen. */
function ma_startplatz_setzen(int $post_id, string $platz): void {
    if (!isset(ma_startplaetze()[$platz])) return;
    if (str_starts_with($platz, 'aufmacher') || str_starts_with($platz, 'buehne-')) {
        $alte = get_posts(['post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids',
            'meta_key' => 'ma_startplatz', 'meta_value' => $platz, 'post__not_in' => [$post_id]]);
        foreach ($alte as $id) update_post_meta($id, 'ma_startplatz', 'auto');
        if ($platz === 'aufmacher') {
            // Die alte Fixierung (ma_top_pinned) darf den neuen Aufmacher nicht überstimmen.
            foreach (get_posts(['post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => 'ma_top_pinned', 'meta_value' => '1', 'post__not_in' => [$post_id]]) as $id) update_post_meta($id, 'ma_top_pinned', '0');
        }
    }
    update_post_meta($post_id, 'ma_startplatz', $platz);
}

/* ---------------------------------------------------------- Box im Beitrag */
add_action('add_meta_boxes_post', function (): void {
    if (!ma_startplatz_darf()) return;
    add_meta_box('ma-startplatz', 'Startseite', 'ma_startplatz_box', 'post', 'side', 'high');
});

function ma_startplatz_box(WP_Post $p): void {
    wp_nonce_field('ma_startplatz', 'ma_startplatz_nonce');
    $jetzt = (string) get_post_meta($p->ID, 'ma_startplatz', true) ?: 'auto';
    echo '<p><label for="ma_startplatz"><strong>Wo steht diese Meldung?</strong></label></p><select id="ma_startplatz" name="ma_startplatz" style="width:100%">';
    foreach (ma_startplaetze() as $wert => $label) {
        $inhaber = ($wert !== 'auto' && $wert !== 'aus' && $wert !== $jetzt) ? ma_startplatz_inhaber($wert) : null;
        $zusatz = $inhaber ? ' – jetzt: ' . wp_trim_words(get_the_title($inhaber), 6, '…') : '';
        printf('<option value="%s"%s>%s%s</option>', esc_attr($wert), selected($jetzt, $wert, false), esc_html($label), esc_html($zusatz));
    }
    echo '</select><p class="description">Ein fester Platz verdrängt die Meldung, die dort stand; sie steht dann wieder in ihrer Rubrik. „Nur in der Rubrik“ hält die Meldung von der Startseite fern.</p>';
    // Relevanz 1–10 (includes/relevanz.php): steuert „Automatisch“.
    if (function_exists('ma_relevanz_auswahl')) {
        echo '<p><strong>Relevanz</strong> <span class="description">(bei „Automatisch“)</span></p>';
        echo ma_relevanz_auswahl('ma_relevanz', ma_relevanz_saeubern(get_post_meta($p->ID, 'ma_relevanz', true)));
    }
    printf('<p><a href="%s">Alle Plätze der Startseite ansehen</a></p>', esc_url(admin_url('admin.php?page=ma-startseite')));
}

add_action('save_post_post', function (int $post_id): void {
    if (!isset($_POST['ma_startplatz_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_startplatz_nonce'])), 'ma_startplatz')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!ma_startplatz_darf() || !current_user_can('edit_post', $post_id)) return;
    $platz = sanitize_key(wp_unslash($_POST['ma_startplatz'] ?? 'auto'));
    ma_startplatz_setzen($post_id, $platz);
    if (function_exists('ma_relevanz_saeubern') && isset($_POST['ma_relevanz'])) {
        $r = ma_relevanz_saeubern(wp_unslash($_POST['ma_relevanz']));
        if ($r) update_post_meta($post_id, 'ma_relevanz', $r);
    }
}, 20);

/* ---------------------------------------------------------- Spalte in der Beitragsliste */
add_filter('manage_post_posts_columns', function (array $c): array {
    if (!ma_startplatz_darf()) return $c;
    $neu = [];
    foreach ($c as $k => $v) { $neu[$k] = $v; if ($k === 'title') $neu['ma_startplatz'] = 'Startseite'; }
    return $neu;
});
add_action('manage_post_posts_custom_column', function (string $spalte, int $id): void {
    if ($spalte !== 'ma_startplatz') return;
    $v = (string) get_post_meta($id, 'ma_startplatz', true) ?: 'auto';
    echo esc_html(ma_startplaetze()[$v] ?? 'Automatisch');
}, 10, 2);

/* ---------------------------------------------------------- Übersicht Merzenich Aktuell -> Startseite */
add_action('admin_menu', function (): void {
    add_submenu_page('merzenich-aktuell', 'Startseite', 'Startseite', 'edit_others_posts', 'ma-startseite', 'ma_startseite_seite', 1);
}, 20);

function ma_startseite_seite(): void {
    if (!ma_startplatz_darf()) wp_die('Keine Berechtigung.');
    if (isset($_POST['ma_startseite_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_startseite_nonce'])), 'ma_startseite')) {
        foreach (ma_startplaetze() as $platz => $label) {
            if ($platz === 'auto' || $platz === 'aus' || !isset($_POST['platz'][$platz])) continue;
            $id = (int) $_POST['platz'][$platz];
            $bisher = ma_startplatz_inhaber($platz);
            if ($id && (!$bisher || $bisher->ID !== $id)) ma_startplatz_setzen($id, $platz);
            if (!$id && $bisher) update_post_meta($bisher->ID, 'ma_startplatz', 'auto');
        }
        echo '<div class="notice notice-success is-dismissible"><p>Startseite gespeichert. Verdrängte Meldungen stehen wieder in ihrer Rubrik.</p></div>';
    }
    $auswahl = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 80, 'orderby' => 'date', 'order' => 'DESC']);
    echo '<div class="wrap"><h1>Startseite</h1><p>Hier legst du fest, welche Meldung auf welchem Platz der Startseite steht. Leere Plätze füllt automatisch die jüngste passende Meldung. Die gleiche Auswahl gibt es in jeder Meldung in der Box „Startseite“.</p>';
    echo '<form method="post">';
    wp_nonce_field('ma_startseite', 'ma_startseite_nonce');
    echo '<table class="widefat striped" style="max-width:980px"><thead><tr><th>Platz</th><th>Meldung</th><th></th></tr></thead><tbody>';
    foreach (ma_startplaetze() as $platz => $label) {
        if ($platz === 'auto' || $platz === 'aus') continue;
        $inhaber = ma_startplatz_inhaber($platz);
        printf('<tr><td><strong>%s</strong></td><td><select name="platz[%s]" style="max-width:640px;width:100%%"><option value="0">– automatisch –</option>', esc_html($label), esc_attr($platz));
        foreach ($auswahl as $p) printf('<option value="%d"%s>%s · %s</option>', $p->ID, selected($inhaber ? $inhaber->ID : 0, $p->ID, false), esc_html(get_the_date('d.m.', $p)), esc_html(wp_trim_words(get_the_title($p), 12, '…')));
        echo '</select></td><td>' . ($inhaber ? '<a href="' . esc_url(get_edit_post_link($inhaber->ID)) . '">Bearbeiten</a>' : '') . '</td></tr>';
    }
    echo '</tbody></table><p><button class="button button-primary">Startseite speichern</button> <a class="button" href="' . esc_url(home_url('/')) . '" target="_blank">Startseite ansehen</a></p></form>';
    $aus = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 30, 'meta_key' => 'ma_startplatz', 'meta_value' => 'aus']);
    if ($aus) {
        echo '<h2>Nur in der Rubrik</h2><ul>';
        foreach ($aus as $p) printf('<li><a href="%s">%s</a></li>', esc_url(get_edit_post_link($p->ID)), esc_html(get_the_title($p)));
        echo '</ul>';
    }
    echo '</div>';
}
