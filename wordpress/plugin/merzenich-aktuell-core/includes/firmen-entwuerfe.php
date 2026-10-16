<?php
/**
 * Firmen-Entwürfe (16.10.2026, Auftrag Betreiber): drei echte Betriebe aus der
 * Gemeinde (data/firmen-entwuerfe.json) als Entwurf im vorhandenen System.
 *
 * - Unternehmensprofil (ma_business) mit Stammdaten, Quelle und Stand. Ohne
 *   Einwilligung bleibt es Entwurf (Sperre in business.php); die Redaktion setzt
 *   den Haken erst, wenn das Unternehmen zugestimmt hat.
 * - Ein Beitrag je Betrieb als gesponserter, kostenfreier Testeintrag
 *   (testeintrag.php), verknüpft mit dem Profil (ma_business_id), nicht auf der
 *   Startseite, als Entwurf.
 * - Logos werden nicht übernommen: Quelle und Rechtestand stehen am Profil
 *   (ma_business_logo_quelle, ma_business_logo_rechte = offen); bis zur Klärung
 *   zeigt die Seite das Kürzel der Firma.
 *
 * Merzenich Aktuell → Firmen-Entwürfe legt fehlende Entwürfe an. Vorhandenes
 * (auch im Papierkorb) wird nie überschrieben oder doppelt angelegt.
 */
if (!defined('ABSPATH')) { exit; }

function ma_firmen_entwuerfe_daten(): array {
    $d = json_decode((string) @file_get_contents(MA_CORE_PATH . 'data/firmen-entwuerfe.json'), true);
    return is_array($d) ? $d : ['firmen' => []];
}

/** Beitragstext: Absätze (einfaches HTML aus der Datei) und der Knopf zur Website des Unternehmens. */
function ma_firmen_beitrag_html(array $b): string {
    $h = '';
    foreach ((array) ($b['absaetze'] ?? []) as $a) $h .= '<p>' . wp_kses((string) $a, ['strong' => [], 'em' => []]) . "</p>\n";
    $k = (array) ($b['knopf'] ?? []);
    if (!empty($k['ziel']) && !empty($k['text'])) $h .= '<p class="cta-row"><a class="btn" href="' . esc_url((string) $k['ziel']) . '" target="_blank" rel="noopener sponsored">' . esc_html((string) $k['text']) . ' ↗</a></p>' . "\n";
    return $h;
}

/** Vorhandener Inhalt mit diesem Slug in jedem Status (auch In Prüfung, Abgelehnt, Archiv, Papierkorb), sonst 0. */
function ma_firmen_vorhanden(string $typ, string $slug): int {
    $q = get_posts(['post_type' => $typ, 'name' => $slug, 'post_status' => array_values(array_diff(get_post_stati(), ['auto-draft', 'inherit'])), 'posts_per_page' => 1, 'fields' => 'ids']);
    if ($q) return (int) $q[0];
    // Im Papierkorb trägt der Slug den Zusatz __trashed.
    $q = get_posts(['post_type' => $typ, 'name' => $slug . '__trashed', 'post_status' => 'trash', 'posts_per_page' => 1, 'fields' => 'ids']);
    return $q ? (int) $q[0] : 0;
}

/** Legt fehlende Entwürfe an. Ergebnis: Zeilen für die Rückmeldung. */
function ma_firmen_entwuerfe_anlegen(): array {
    $daten = ma_firmen_entwuerfe_daten();
    $stand = (string) ($daten['stand'] ?? '');
    $log = [];
    foreach ((array) ($daten['firmen'] ?? []) as $f) {
        $p = (array) ($f['profil'] ?? []); $b = (array) ($f['beitrag'] ?? []);
        if (empty($p['slug']) || empty($p['titel'])) continue;
        $pid = ma_firmen_vorhanden('ma_business', (string) $p['slug']);
        if (!$pid) {
            $logo = (array) ($p['logo'] ?? []);
            $r = wp_insert_post(wp_slash([
                'post_type' => 'ma_business', 'post_status' => 'draft', 'post_title' => (string) $p['titel'], 'post_name' => (string) $p['slug'],
                'post_excerpt' => (string) ($p['kurz'] ?? ''), 'post_content' => '<p>' . esc_html((string) ($p['text'] ?? '')) . '</p>',
                'meta_input' => array_filter([
                    'ma_business_branche' => (string) ($p['branche'] ?? ''), 'ma_business_adresse' => (string) ($p['adresse'] ?? ''),
                    'ma_business_telefon' => (string) ($p['telefon'] ?? ''), 'ma_business_email' => (string) ($p['email'] ?? ''),
                    'ma_business_website' => (string) ($p['website'] ?? ''), 'ma_business_oeffnungszeiten' => (string) ($p['oeffnungszeiten'] ?? ''),
                    'ma_business_ortsteil' => (string) ($p['ortsteil'] ?? ''), 'ma_business_quelle' => (string) ($p['quelle'] ?? ''), 'ma_business_stand' => $stand,
                    'ma_business_firmenname' => (string) ($p['firmenname'] ?? ''),
                    'ma_business_logo_quelle' => (string) ($logo['quelle'] ?? ''), 'ma_business_logo_rechte' => (string) ($logo['rechte'] ?? 'offen'), 'ma_business_logo_alt' => (string) ($logo['alt'] ?? ''),
                    'ma_business_karte_knopf' => (string) ($p['karte']['knopf'] ?? ''), 'ma_business_karte_ziel' => (string) ($p['karte']['ziel'] ?? ''),
                ], fn($v) => $v !== ''),
            ]), true);
            if (is_wp_error($r) || !$r) { $log[] = $p['titel'] . ': Profil nicht angelegt' . (is_wp_error($r) ? ' (' . $r->get_error_message() . ')' : ''); continue; }
            $pid = (int) $r;
            $log[] = $p['titel'] . ': Profil als Entwurf angelegt';
            if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($pid, 'Entwurf angelegt', 'Aus data/firmen-entwuerfe.json (Stand ' . $stand . '). Veröffentlichung erst mit Einwilligung des Unternehmens; Logo-Rechte offen.');
        } else $log[] = $p['titel'] . ': Profil schon vorhanden (' . get_post_status($pid) . ')';

        if (empty($b['slug']) || empty($b['titel'])) continue;
        if ($bid = ma_firmen_vorhanden('post', (string) $b['slug'])) { $log[] = $p['titel'] . ': Beitrag schon vorhanden (' . get_post_status($bid) . ')'; continue; }
        $kat = get_category_by_slug('wirtschaft');
        $r = wp_insert_post(wp_slash([
            'post_type' => 'post', 'post_status' => 'draft', 'post_title' => (string) $b['titel'], 'post_name' => (string) $b['slug'],
            'post_excerpt' => (string) ($b['anriss'] ?? ''), 'post_content' => ma_firmen_beitrag_html($b),
            'post_category' => $kat ? [(int) $kat->term_id] : [],
            'meta_input' => [
                'ma_gesponsert' => '1', 'ma_gesponsert_art' => 'test', 'ma_gesponsert_von' => (string) ($p['firmenname'] ?? '') ?: (string) $p['titel'],
                'ma_business_id' => (string) $pid, 'ma_seo_titel' => (string) ($b['seo_titel'] ?? ''), 'ma_seo_beschreibung' => (string) ($b['seo_beschreibung'] ?? ''),
                'ma_source_url' => (string) ($p['website'] ?? ''), 'ma_source_publisher' => (string) ($p['firmenname'] ?? '') ?: (string) $p['titel'],
                'ma_startseite_freigabe' => 'nein',
            ],
        ]), true);
        if (is_wp_error($r) || !$r) { $log[] = $p['titel'] . ': Beitrag nicht angelegt' . (is_wp_error($r) ? ' (' . $r->get_error_message() . ')' : ''); continue; }
        $bid = (int) $r;
        if (!empty($p['ortsteil']) && taxonomy_exists('ma_location')) wp_set_object_terms($bid, (string) $p['ortsteil'], 'ma_location', false);
        $log[] = $p['titel'] . ': Beitrag als Entwurf angelegt (kostenfreier Testeintrag)';
        if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($bid, 'Entwurf angelegt', 'Werbebeitrag als kostenfreier Testeintrag aus data/firmen-entwuerfe.json. Erst veröffentlichen, wenn das Unternehmen Text und Schaltung bestätigt hat.');
    }
    return $log;
}

add_action('admin_menu', function (): void {
    add_submenu_page('merzenich-aktuell', 'Firmen-Entwürfe', 'Firmen-Entwürfe', 'edit_others_posts', 'ma-firmen-entwuerfe', 'ma_firmen_entwuerfe_seite');
});

add_action('admin_post_ma_firmen_entwuerfe', function (): void {
    if (!current_user_can('edit_others_posts')) wp_die('Keine Berechtigung.');
    check_admin_referer('ma_firmen_entwuerfe');
    set_transient('ma_firmen_entwuerfe_log_' . get_current_user_id(), ma_firmen_entwuerfe_anlegen(), 300);
    wp_safe_redirect(admin_url('admin.php?page=ma-firmen-entwuerfe&angelegt=1'));
    exit;
});

function ma_firmen_entwuerfe_seite(): void {
    $daten = ma_firmen_entwuerfe_daten();
    echo '<div class="wrap"><h1>Firmen-Entwürfe</h1><p>' . esc_html((string) ($daten['hinweis'] ?? '')) . '</p>';
    $log = get_transient('ma_firmen_entwuerfe_log_' . get_current_user_id());
    if (!empty($_GET['angelegt']) && is_array($log)) { echo '<div class="notice notice-success"><ul>'; foreach ($log as $z) echo '<li>' . esc_html((string) $z) . '</li>'; echo '</ul></div>'; }
    echo '<table class="widefat striped"><thead><tr><th>Betrieb</th><th>Profil</th><th>Einwilligung</th><th>Werbebeitrag</th><th>Logo</th></tr></thead><tbody>';
    foreach ((array) ($daten['firmen'] ?? []) as $f) {
        $p = (array) ($f['profil'] ?? []); $b = (array) ($f['beitrag'] ?? []);
        $pid = ma_firmen_vorhanden('ma_business', (string) ($p['slug'] ?? ''));
        $bid = ma_firmen_vorhanden('post', (string) ($b['slug'] ?? ''));
        $zelle = fn(int $id): string => $id ? '<a href="' . esc_url((string) get_edit_post_link($id)) . '">' . esc_html(function_exists('ma_status_name') ? ma_status_name((string) get_post_status($id)) : (string) get_post_status($id)) . '</a>' . (get_post_status($id) !== 'publish' ? ' · <a href="' . esc_url((string) get_preview_post_link($id)) . '" target="_blank">Vorschau</a>' : '') : 'fehlt';
        $ja = $pid && (string) get_post_meta($pid, 'ma_business_consent', true) === '1';
        $logo = (array) ($p['logo'] ?? []);
        echo '<tr><td><strong>' . esc_html((string) ($p['titel'] ?? '')) . '</strong><br><small>' . esc_html((string) ($p['adresse'] ?? '')) . '</small></td><td>' . $zelle($pid) . '</td><td>' . ($ja ? 'liegt vor' : '<strong>fehlt</strong>') . '</td><td>' . $zelle($bid) . '</td>'
            . '<td>' . (!empty($logo['quelle']) ? '<a href="' . esc_url((string) $logo['quelle']) . '" target="_blank" rel="noopener">Quelle</a>, ' : '') . 'Rechte ' . esc_html((string) ($logo['rechte'] ?? 'offen')) . '</td></tr>';
    }
    echo '</tbody></table><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top:16px"><input type="hidden" name="action" value="ma_firmen_entwuerfe">';
    wp_nonce_field('ma_firmen_entwuerfe');
    echo '<button class="button button-primary">Fehlende Entwürfe anlegen</button> <span class="description">Legt nur an, was fehlt. Nichts wird veröffentlicht.</span></form></div>';
}
