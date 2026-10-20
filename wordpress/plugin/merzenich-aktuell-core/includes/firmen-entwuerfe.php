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
 *
 * Zusage eintragen (20.10.2026): je Betrieb Einwilligung und Logo-Freigabe getrennt,
 * jeweils mit Nachweis (wer, wann, woher). Mit der Logo-Freigabe kommt das Logo
 * aus data/logos/ einmal in die Mediathek und wird Beitragsbild des Profils; mit
 * der Einwilligung wird das Profil veröffentlicht (die Sperre in business.php
 * bleibt die Prüfung). Den Werbebeitrag gibt die Redaktion danach wie jeden
 * Beitrag in den Freigaben frei (Prüfhäkchen, Sperre in freigabe.php).
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

/** Daten eines Betriebs aus der Datei nach Profil-Slug, sonst null. */
function ma_firmen_eintrag(string $slug): ?array {
    foreach ((array) (ma_firmen_entwuerfe_daten()['firmen'] ?? []) as $f) if ((string) ($f['profil']['slug'] ?? '') === $slug) return $f;
    return null;
}

/**
 * Logo aus data/logos/ einmal in die Mediathek übernehmen; ein zweiter Aufruf findet
 * das Medium wieder (ma_firmen_logo_datei). Gibt die Medien-ID zurück, 0 bei Fehler.
 */
function ma_firmen_logo_medium(array $p, int $pid, string &$fehler = ''): int {
    $logo = (array) ($p['logo'] ?? []);
    $datei = basename((string) ($logo['datei'] ?? ''));
    $pfad = MA_CORE_PATH . 'data/logos/' . $datei;
    if ($datei === '' || !is_file($pfad)) { $fehler = 'Logo-Datei fehlt im Plugin'; return 0; }
    $da = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => 'ma_firmen_logo_datei', 'meta_value' => $datei]);
    if ($da) return (int) $da[0];
    if (!function_exists('media_handle_sideload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }
    $tmp = wp_tempnam($datei);
    if (!$tmp || !copy($pfad, $tmp)) { $fehler = 'Logo-Datei nicht lesbar'; return 0; }
    $id = media_handle_sideload(['name' => $datei, 'tmp_name' => $tmp], $pid, (string) ($logo['alt'] ?? ''));
    if (is_wp_error($id)) { @unlink($tmp); $fehler = $id->get_error_message(); return 0; }
    $id = (int) $id;
    update_post_meta($id, 'ma_firmen_logo_datei', $datei);
    update_post_meta($id, '_wp_attachment_image_alt', (string) ($logo['alt'] ?? ''));
    update_post_meta($id, 'ma_image_credit', 'Logo: ' . (string) ($p['titel'] ?? '') . ', Nutzung mit Freigabe des Unternehmens');
    update_post_meta($id, 'ma_image_original_url', (string) ($logo['quelle'] ?? ''));
    update_post_meta($id, 'ma_image_rights_verified', '1');
    return $id;
}

/**
 * Zusage eines Betriebs eintragen. $nachweis: woher die Zusage stammt (Pflicht).
 * Einwilligung und Logo getrennt; was schon eingetragen ist, bleibt unverändert.
 * Ergebnis: Zeilen für die Rückmeldung.
 */
function ma_firmen_zusage_eintragen(string $slug, string $nachweis, bool $einwilligung, bool $logo): array {
    $f = ma_firmen_eintrag($slug);
    if (!$f) return ['Unbekannter Betrieb.'];
    $p = (array) $f['profil']; $name = (string) ($p['titel'] ?? $slug);
    $nachweis = trim($nachweis);
    if ($nachweis === '') return [$name . ': Ohne Nachweis der Zusage wird nichts eingetragen.'];
    if (!$einwilligung && !$logo) return [$name . ': Kein Haken gesetzt, nichts eingetragen.'];
    $pid = ma_firmen_vorhanden('ma_business', (string) $p['slug']);
    if (!$pid || get_post_status($pid) === 'trash') return [$name . ': Profil fehlt. Zuerst „Fehlende Entwürfe anlegen“.'];
    $wer = wp_get_current_user();
    $wer = (string) ($wer->display_name ?? '') ?: (string) ($wer->user_login ?? '');
    $jetzt = (string) current_time('d.m.Y H:i');
    $log = [];
    if ($logo) {
        $fehler = '';
        $m = ma_firmen_logo_medium($p, $pid, $fehler);
        if (!$m) $log[] = $name . ': Logo nicht übernommen (' . $fehler . ').';
        else {
            set_post_thumbnail($pid, $m);
            update_post_meta($pid, 'ma_business_logo_rechte', 'geklaert');
            update_post_meta($pid, 'ma_business_logo_freigabe', $nachweis . ' · eingetragen von ' . $wer . ' am ' . $jetzt);
            $log[] = $name . ': Logo freigegeben und als Beitragsbild gesetzt.';
            if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($pid, 'Logo freigegeben', $nachweis);
        }
    }
    if ($einwilligung) {
        if ((string) get_post_meta($pid, 'ma_business_consent', true) !== '1') {
            update_post_meta($pid, 'ma_business_consent', '1');
            update_post_meta($pid, 'ma_business_consent_by', $wer);
            update_post_meta($pid, 'ma_business_consent_at', $jetzt);
            update_post_meta($pid, 'ma_business_consent_quelle', $nachweis);
            if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($pid, 'Einwilligung eingetragen', $nachweis);
        }
        if (get_post_status($pid) !== 'publish') {
            $r = wp_update_post(['ID' => $pid, 'post_status' => 'publish'], true);
            $log[] = $name . (!is_wp_error($r) && get_post_status($pid) === 'publish' ? ': Einwilligung eingetragen, Profil veröffentlicht. Den Werbebeitrag in den Freigaben freigeben.' : ': Einwilligung eingetragen, Profil aber nicht veröffentlicht.');
        } else $log[] = $name . ': Einwilligung eingetragen, Profil war schon veröffentlicht.';
    }
    return $log;
}

// Priorität 30: Das Hauptmenü „Merzenich Aktuell“ meldet admin.php erst bei plugins_loaded an (Priorität 10);
// vorher angemeldet, fehlte die Seite (403 „Keine Berechtigung“).
add_action('admin_menu', function (): void {
    add_submenu_page('merzenich-aktuell', 'Firmen-Entwürfe', 'Firmen-Entwürfe', 'edit_others_posts', 'ma-firmen-entwuerfe', 'ma_firmen_entwuerfe_seite');
}, 30);

add_action('admin_post_ma_firmen_zusage', function (): void {
    if (!current_user_can('edit_others_posts')) wp_die('Keine Berechtigung.');
    check_admin_referer('ma_firmen_zusage');
    $log = ma_firmen_zusage_eintragen(sanitize_title(wp_unslash($_POST['firma'] ?? '')), sanitize_text_field(wp_unslash($_POST['nachweis'] ?? '')), !empty($_POST['einwilligung']), !empty($_POST['logo']));
    set_transient('ma_firmen_entwuerfe_log_' . get_current_user_id(), $log, 300);
    wp_safe_redirect(admin_url('admin.php?page=ma-firmen-entwuerfe&angelegt=1'));
    exit;
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
    echo '<table class="widefat striped"><thead><tr><th>Betrieb</th><th>Profil</th><th>Einwilligung</th><th>Werbebeitrag</th><th>Logo</th><th>Zusage eintragen</th></tr></thead><tbody>';
    foreach ((array) ($daten['firmen'] ?? []) as $f) {
        $p = (array) ($f['profil'] ?? []); $b = (array) ($f['beitrag'] ?? []);
        $pid = ma_firmen_vorhanden('ma_business', (string) ($p['slug'] ?? ''));
        $bid = ma_firmen_vorhanden('post', (string) ($b['slug'] ?? ''));
        $zelle = fn(int $id): string => $id ? '<a href="' . esc_url((string) get_edit_post_link($id)) . '">' . esc_html(function_exists('ma_status_name') ? ma_status_name((string) get_post_status($id)) : (string) get_post_status($id)) . '</a>' . (get_post_status($id) !== 'publish' ? ' · <a href="' . esc_url((string) get_preview_post_link($id)) . '" target="_blank">Vorschau</a>' : '') : 'fehlt';
        $ja = $pid && (string) get_post_meta($pid, 'ma_business_consent', true) === '1';
        $logoJa = $pid && (string) get_post_meta($pid, 'ma_business_logo_rechte', true) === 'geklaert';
        $logo = (array) ($p['logo'] ?? []);
        $einw = $ja ? 'liegt vor<br><small>' . esc_html(trim((string) get_post_meta($pid, 'ma_business_consent_quelle', true) . ' · ' . get_post_meta($pid, 'ma_business_consent_by', true) . ', ' . get_post_meta($pid, 'ma_business_consent_at', true), ' ·,')) . '</small>' : '<strong>fehlt</strong>';
        $logoZelle = (!empty($logo['quelle']) ? '<a href="' . esc_url((string) $logo['quelle']) . '" target="_blank" rel="noopener">Quelle</a>, ' : '')
            . ($logoJa ? 'freigegeben<br><small>' . esc_html((string) get_post_meta($pid, 'ma_business_logo_freigabe', true)) . '</small>' : 'Rechte <strong>offen</strong>');
        $form = '';
        if ($pid && (!$ja || !$logoJa)) {
            $form = '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ma_firmen_zusage"><input type="hidden" name="firma" value="' . esc_attr((string) $p['slug']) . '">'
                . wp_nonce_field('ma_firmen_zusage', '_wpnonce', true, false)
                . '<label style="display:block">Nachweis <input type="text" name="nachweis" required class="regular-text" placeholder="z. B. E-Mail vom … / Zusage laut Betreiber am …"></label>'
                . (!$ja ? '<label style="display:block"><input type="checkbox" name="einwilligung" value="1"> Das Unternehmen hat der Veröffentlichung von Profil und Testeintrag zugestimmt.</label>' : '')
                . (!$logoJa ? '<label style="display:block"><input type="checkbox" name="logo" value="1"> Das Unternehmen hat die Nutzung seines Logos freigegeben.</label>' : '')
                . '<button class="button">Zusage eintragen</button></form>';
        }
        echo '<tr><td><strong>' . esc_html((string) ($p['titel'] ?? '')) . '</strong><br><small>' . esc_html((string) ($p['adresse'] ?? '')) . '</small></td><td>' . $zelle($pid) . '</td><td>' . $einw . '</td><td>' . $zelle($bid) . '</td>'
            . '<td>' . $logoZelle . '</td><td>' . ($form ?: ($pid ? 'eingetragen' : '–')) . '</td></tr>';
    }
    echo '</tbody></table><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top:16px"><input type="hidden" name="action" value="ma_firmen_entwuerfe">';
    wp_nonce_field('ma_firmen_entwuerfe');
    echo '<button class="button button-primary">Fehlende Entwürfe anlegen</button> <span class="description">Legt nur an, was fehlt. Nichts wird veröffentlicht.</span></form></div>';
}
