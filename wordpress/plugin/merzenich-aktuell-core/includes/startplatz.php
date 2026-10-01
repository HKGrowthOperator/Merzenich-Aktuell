<?php
/**
 * Startplatz: Die Redaktion bestimmt, wo eine Meldung steht (30.09.2026,
 * seit 02.10.2026 über die Layout-Karte in includes/layout.php).
 *
 * Meta ma_startplatz je Beitrag:
 *   auto        automatisch nach Relevanz 1–10 (includes/relevanz.php), sonst
 *               füllt die jüngste passende Meldung freie Plätze
 *   aufmacher   großer Aufmacher oben
 *   buehne-1…4  Bühne: 1–2 rechts neben dem Aufmacher, 3–4 darunter
 *               (der Platz unten rechts ist eine Anzeige)
 *   aus         nur in der eigenen Rubrik, nie auf der Startseite
 *
 * Die Karte (ma_layout_*) ist die Wahrheit für feste Plätze, auch für die
 * Rubrikflächen und die Ressortseiten; das Meta spiegelt Aufmacher und Bühne
 * und trägt den Ausschluss „aus“. Jeder feste Platz hat genau eine Meldung.
 *
 * Bedienung: Box „Startseite“ im Beitrag und das Board
 * Merzenich Aktuell → Startseite & Ressorts (includes/layout-admin.php).
 * Partner sehen beides nicht.
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

/** Wer steht gerade auf einem festen Platz der Startseite? */
function ma_startplatz_inhaber(string $platz): ?WP_Post {
    $id = ma_layout_feste_plaetze('startseite')[$platz] ?? 0;
    return $id ? (get_post($id) ?: null) : null;
}

/** Auf welchem Platz der Startseite steht der Beitrag (erster Treffer), sonst Meta (auto/aus). */
function ma_startplatz_von(int $post_id): string {
    $slots = array_keys(array_filter(ma_layout_get('startseite')['slots'], fn($e) => $e['post'] === $post_id));
    if ($slots) return $slots[0];
    $v = (string) get_post_meta($post_id, 'ma_startplatz', true);
    return $v === 'aus' ? 'aus' : 'auto';
}

/**
 * Platz setzen: fester Platz in die Karte (der bisherige Inhaber wird frei),
 * „auto“ nimmt den Beitrag von allen festen Plätzen der Startseite, „aus“
 * schließt ihn aus. Ein noch nicht veröffentlichter Beitrag merkt sich den
 * Platz im Meta und kommt beim Veröffentlichen darauf (includes/layout.php).
 */
function ma_startplatz_setzen(int $post_id, string $platz): void {
    $slots = ma_layout_slots('startseite');
    if ($platz === 'auto' || $platz === 'aus') {
        ma_layout_post_entfernen($post_id, 'startseite', true);
        update_post_meta($post_id, 'ma_startplatz', $platz);
        return;
    }
    if (!isset($slots[$platz])) return;
    if (get_post_status($post_id) !== 'publish') {
        if (isset(ma_startplaetze()[$platz])) update_post_meta($post_id, 'ma_startplatz', $platz);
        return;
    }
    if ((string) get_post_meta($post_id, 'ma_startplatz', true) === 'aus') update_post_meta($post_id, 'ma_startplatz', 'auto');
    $jetzt = ma_startplatz_von($post_id);
    if ($jetzt !== 'auto' && $jetzt !== 'aus' && $jetzt !== $platz) ma_layout_entfernen('startseite', $jetzt, 0, true);
    ma_layout_set('startseite', $platz, $post_id, ['still' => true]);
}

/* ---------------------------------------------------------- Box im Beitrag */
add_action('add_meta_boxes_post', function (): void {
    if (!ma_startplatz_darf()) return;
    add_meta_box('ma-startplatz', 'Startseite', 'ma_startplatz_box', 'post', 'side', 'high');
});

/** Ressortseite des Beitrags (erste Rubrik mit Layout-Karte). */
function ma_startplatz_ressort(int $post_id): string {
    foreach (get_the_category($post_id) as $c) if (ma_layout_seite_gueltig('ressort-' . $c->slug)) return 'ressort-' . $c->slug;
    return '';
}

function ma_startplatz_box(WP_Post $p): void {
    wp_nonce_field('ma_startplatz', 'ma_startplatz_nonce');
    $jetzt = ma_startplatz_von($p->ID);
    if ($jetzt === 'auto') $jetzt = (string) get_post_meta($p->ID, 'ma_startplatz', true) ?: 'auto';
    if (!isset(ma_layout_slots('startseite')[$jetzt]) && $jetzt !== 'aus') $jetzt = 'auto';
    $fest = ma_layout_feste_plaetze('startseite');
    $zusatz = function (string $wert) use ($fest, $p): string {
        $id = $fest[$wert] ?? 0;
        return $id && $id !== $p->ID ? ' – jetzt: ' . wp_trim_words(get_the_title($id), 6, '…') : '';
    };
    echo '<p><label for="ma_startplatz"><strong>Wo steht diese Meldung?</strong></label></p><select id="ma_startplatz" name="ma_startplatz" style="width:100%">';
    foreach (ma_startplaetze() as $wert => $label) {
        if ($wert === 'aus') continue;
        printf('<option value="%s"%s>%s%s</option>', esc_attr($wert), selected($jetzt, $wert, false), esc_html($label), esc_html($wert === 'auto' ? '' : $zusatz($wert)));
    }
    echo '<optgroup label="Rubrikflächen">';
    foreach (ma_layout_slots('startseite') as $wert => $label) {
        if (isset(ma_startplaetze()[$wert])) continue;
        printf('<option value="%s"%s>%s%s</option>', esc_attr($wert), selected($jetzt, $wert, false), esc_html($label), esc_html($zusatz($wert)));
    }
    echo '</optgroup>';
    printf('<option value="aus"%s>%s</option>', selected($jetzt, 'aus', false), esc_html(ma_startplaetze()['aus']));
    echo '</select><p class="description">Ein fester Platz verdrängt die Meldung, die dort stand; sie steht dann wieder in ihrer Rubrik. „Nur in der Rubrik“ hält die Meldung von der Startseite fern.</p>';
    $ressort = ma_startplatz_ressort($p->ID);
    if ($ressort !== '') {
        $slotsR = ma_layout_slots($ressort);
        $jetztR = 'auto';
        foreach (array_keys(array_filter(ma_layout_get($ressort)['slots'], fn($e) => $e['post'] === $p->ID)) as $s) { $jetztR = $s; break; }
        printf('<p><label for="ma_ressortplatz"><strong>Auf der Seite „%s“</strong></label></p><select id="ma_ressortplatz" name="ma_ressortplatz" style="width:100%%"><option value="auto"%s>Automatisch (nach Datum)</option>', esc_html(ma_layout_seiten()[$ressort]), selected($jetztR, 'auto', false));
        foreach ($slotsR as $wert => $label) printf('<option value="%s"%s>%s</option>', esc_attr($wert), selected($jetztR, $wert, false), esc_html($label));
        echo '</select><input type="hidden" name="ma_ressortplatz_seite" value="' . esc_attr($ressort) . '">';
    }
    // Relevanz 1–10 (includes/relevanz.php): steuert „Automatisch“.
    if (function_exists('ma_relevanz_auswahl')) {
        echo '<p><strong>Relevanz</strong> <span class="description">(bei „Automatisch“)</span></p>';
        echo ma_relevanz_auswahl('ma_relevanz', ma_relevanz_saeubern(get_post_meta($p->ID, 'ma_relevanz', true)));
    }
    printf('<p><a href="%s">Startseite &amp; Ressorts anordnen</a></p>', esc_url(admin_url('admin.php?page=ma-startseite')));
}

add_action('save_post_post', function (int $post_id): void {
    if (!isset($_POST['ma_startplatz_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_startplatz_nonce'])), 'ma_startplatz')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!ma_startplatz_darf() || !current_user_can('edit_post', $post_id)) return;
    $platz = sanitize_text_field(wp_unslash($_POST['ma_startplatz'] ?? 'auto'));
    $alt = ma_startplatz_von($post_id);
    if ($alt === 'auto') $alt = (string) get_post_meta($post_id, 'ma_startplatz', true) ?: 'auto';
    if ($platz === 'auto' || $platz === 'aus' || isset(ma_layout_slots('startseite')[$platz])) {
        ma_startplatz_setzen($post_id, $platz);
        if ($alt !== $platz && function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($post_id, 'Startseite: ' . (ma_startplaetze()[$platz] ?? ma_layout_slots('startseite')[$platz] ?? $platz));
    }
    $seite = sanitize_key(wp_unslash($_POST['ma_ressortplatz_seite'] ?? ''));
    if ($seite !== '' && ma_layout_seite_gueltig($seite) && isset($_POST['ma_ressortplatz'])) {
        $pr = sanitize_text_field(wp_unslash($_POST['ma_ressortplatz']));
        $altR = 'auto';
        foreach (array_keys(array_filter(ma_layout_get($seite)['slots'], fn($e) => $e['post'] === $post_id)) as $s) { $altR = $s; break; }
        if ($pr !== $altR) {
            if ($pr === 'auto') ma_layout_post_entfernen($post_id, $seite, true);
            elseif (isset(ma_layout_slots($seite)[$pr]) && get_post_status($post_id) === 'publish') { if ($altR !== 'auto') ma_layout_entfernen($seite, $altR, 0, true); ma_layout_set($seite, $pr, $post_id, ['still' => true]); }
            if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($post_id, ma_layout_seiten()[$seite] . ': ' . ($pr === 'auto' ? 'automatisch' : (ma_layout_slots($seite)[$pr] ?? $pr)));
        }
    }
    if (function_exists('ma_relevanz_saeubern') && isset($_POST['ma_relevanz'])) {
        $r = ma_relevanz_saeubern(wp_unslash($_POST['ma_relevanz']));
        $vorher = (int) get_post_meta($post_id, 'ma_relevanz', true);
        if ($r) update_post_meta($post_id, 'ma_relevanz', $r);
        if ($r && $r !== $vorher && function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($post_id, 'Relevanz ' . $r . ($vorher ? ' (vorher ' . $vorher . ')' : ''));
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
    $v = ma_startplatz_von($id);
    echo esc_html(ma_startplaetze()[$v] ?? ma_layout_slots('startseite')[$v] ?? 'Automatisch');
}, 10, 2);
