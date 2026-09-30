<?php
/**
 * Redaktionszugänge für alle Vereine der Gemeinde (30.09.2026).
 *
 * Wunsch Betreiber: alle Vereine aus Merzenich, ausdrücklich auch alle
 * Sportvereine, bekommen einen eigenen Zugang. Damit reichen sie Meldungen,
 * Termine, Fotos und Ergänzungen zu ihrem Vereinsprofil ein; veröffentlicht
 * wird erst nach Freigabe durch die Redaktion (Partner-Rollen, partners.php).
 *
 * Grundlage ist data/vereinsverzeichnis.json (Kopie von
 * deploy/vereinsverzeichnis.json, belegt aus Bürgerbroschüre, Amtsblättern,
 * Heimat-Info und Vereinswebsites). Sportvereine erhalten die Rolle
 * Sport-Partner, alle anderen Vereins-Partner. Die Feuerwehr hat eigene
 * Partner-Rollen und bleibt hier außen vor.
 *
 * Benutzer → Vereinszugänge: fehlende Zugänge anlegen (E-Mail-Vorlage mit
 * {kurz}), Einladung je Verein einzeln senden. Ohne Einladung erfährt niemand
 * von seinem Zugang; das Passwort ist zufällig und niemandem bekannt.
 */
if (!defined('ABSPATH')) { exit; }

function ma_vereinsverzeichnis(): array {
    static $v = null;
    if ($v !== null) return $v;
    $pfad = MA_CORE_PATH . 'data/vereinsverzeichnis.json';
    $d = is_readable($pfad) ? json_decode((string) file_get_contents($pfad), true) : null;
    return $v = is_array($d['vereine'] ?? null) ? $d['vereine'] : [];
}

/** Kurzer, stabiler Schlüssel je Verein (für Login und E-Mail-Vorlage). */
function ma_verein_kurz(string $name): string {
    $s = remove_accents(str_replace(['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'], ['ae', 'oe', 'ue', 'ae', 'oe', 'ue', 'ss'], $name));
    $s = preg_replace('/\b(e\s*\.?\s*v\.?|gegr\.?|vor)\b/i', ' ', $s);
    $s = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-');
    return substr($s, 0, 40) ?: 'verein';
}

function ma_verein_rolle(array $v): string {
    return ($v['kategorie'] ?? '') === 'Sport' ? 'ma_sport_partner' : 'ma_vereine_partner';
}

/** Vereine, die einen Zugang bekommen (ohne Feuerwehr, die eigene Rollen hat). */
function ma_vereine_fuer_zugang(): array {
    return array_values(array_filter(ma_vereinsverzeichnis(), fn($v) => ($v['kategorie'] ?? '') !== 'Feuerwehr' && !empty($v['name'])));
}

function ma_verein_benutzer(string $kurz): ?WP_User {
    $u = get_users(['meta_key' => 'ma_verein_kurz', 'meta_value' => $kurz, 'number' => 1]);
    return $u[0] ?? null;
}

/**
 * Legt fehlende Vereinszugänge an. $vorlage z. B. "info+{kurz}@example.de".
 * Gibt [angelegt, vorhanden, fehler[]] zurück. Sendet keine Mail.
 */
function ma_vereinszugaenge_anlegen(string $vorlage): array {
    $angelegt = 0; $vorhanden = 0; $fehler = [];
    foreach (ma_vereine_fuer_zugang() as $v) {
        $kurz = ma_verein_kurz($v['name']);
        if (ma_verein_benutzer($kurz)) { $vorhanden++; continue; }
        $mail = sanitize_email(str_replace('{kurz}', $kurz, $vorlage));
        if (!is_email($mail)) { $fehler[] = $v['name'] . ': E-Mail ungültig'; continue; }
        if (email_exists($mail)) { $fehler[] = $v['name'] . ': E-Mail ' . $mail . ' ist schon vergeben'; continue; }
        $login = 'verein-' . substr($kurz, 0, 50); $i = 2;
        while (username_exists($login)) { $login = 'verein-' . substr($kurz, 0, 46) . '-' . $i++; }
        $id = wp_insert_user(['user_login' => $login, 'user_email' => $mail, 'display_name' => $v['name'], 'nickname' => $v['name'],
            'user_pass' => wp_generate_password(24, true, true), 'role' => ma_verein_rolle($v), 'user_url' => $v['website'] ?? '']);
        if (is_wp_error($id)) { $fehler[] = $v['name'] . ': ' . $id->get_error_message(); continue; }
        update_user_meta($id, 'ma_verein_kurz', $kurz);
        update_user_meta($id, 'ma_verein_name', $v['name']);
        update_user_meta($id, 'ma_verein_ort', $v['ort'] ?? '');
        update_user_meta($id, 'ma_verein_quelle', $v['quelle'] ?? '');
        $angelegt++;
    }
    return [$angelegt, $vorhanden, $fehler];
}

add_action('admin_menu', function (): void {
    add_users_page('Vereinszugänge', 'Vereinszugänge', 'manage_options', 'ma-vereinszugaenge', 'ma_vereinszugaenge_seite');
});

add_action('admin_post_ma_verein_einladen', function (): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.', 403);
    $id = (int) ($_GET['user'] ?? 0);
    check_admin_referer('ma_verein_einladen_' . $id);
    if ($id && get_user_meta($id, 'ma_verein_kurz', true)) {
        wp_send_new_user_notifications($id, 'user');
        update_user_meta($id, 'ma_verein_eingeladen', current_time('d.m.Y H:i'));
    }
    wp_safe_redirect(add_query_arg('ma_eingeladen', $id, admin_url('users.php?page=ma-vereinszugaenge'))); exit;
});

function ma_vereinszugaenge_seite(): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
    $meldung = '';
    if (isset($_POST['ma_vereinszugaenge'])) {
        check_admin_referer('ma_vereinszugaenge');
        $vorlage = sanitize_text_field(wp_unslash($_POST['vorlage'] ?? ''));
        if (!str_contains($vorlage, '{kurz}') || !str_contains($vorlage, '@')) $meldung = 'Die E-Mail-Vorlage braucht {kurz} und ein @, z. B. info+{kurz}@ihre-domain.de.';
        else {
            [$a, $v, $f] = ma_vereinszugaenge_anlegen($vorlage);
            update_option('ma_vereinszugang_vorlage', $vorlage, false);
            $meldung = "$a Zugänge angelegt, $v waren schon da." . ($f ? ' Nicht angelegt: ' . implode('; ', $f) : '') . ' Es wurde keine E-Mail verschickt.';
        }
    }
    if (!empty($_GET['ma_eingeladen'])) $meldung = 'Einladung an ' . esc_html(get_userdata((int) $_GET['ma_eingeladen'])->user_email ?? '') . ' gesendet.';
    $vereine = ma_vereine_fuer_zugang();
    echo '<div class="wrap"><h1>Vereinszugänge</h1>';
    echo '<p>Jeder Verein aus dem Vereinsverzeichnis der Gemeinde bekommt einen eigenen Redaktionszugang. Damit reicht er Meldungen, Termine, Fotos und Ergänzungen zu seinem Vereinsprofil ein. <strong>Veröffentlicht wird immer erst nach Freigabe</strong> unter <a href="' . esc_url(admin_url('admin.php?page=ma-freigaben')) . '">Merzenich Aktuell → Freigaben</a>. Wer Fotos hochlädt, muss vorher die Bildrechte bestätigen.</p>';
    if ($meldung !== '') echo '<div class="notice notice-info"><p>' . esc_html($meldung) . '</p></div>';
    echo '<form method="post" style="background:#fff;border:1px solid #dcdcde;padding:14px 16px;max-width:760px">';
    wp_nonce_field('ma_vereinszugaenge');
    printf('<p><label><strong>E-Mail-Vorlage für neue Zugänge</strong><br><input class="regular-text" name="vorlage" value="%s" placeholder="info+{kurz}@ihre-domain.de" required></label><br><span class="description">{kurz} wird durch den Kurznamen des Vereins ersetzt. Die Adresse lässt sich später im Benutzerprofil auf die echte Vereinsadresse ändern.</span></p>',
        esc_attr((string) get_option('ma_vereinszugang_vorlage', '')));
    echo '<p><button class="button button-primary" name="ma_vereinszugaenge" value="1">Fehlende Zugänge anlegen (ohne E-Mail)</button></p></form>';
    echo '<table class="widefat striped" style="margin-top:18px"><thead><tr><th>Verein</th><th>Ort</th><th>Rolle</th><th>Zugang</th><th></th></tr></thead><tbody>';
    $orte = ['merzenich' => 'Merzenich', 'golzheim' => 'Golzheim', 'girbelsrath' => 'Girbelsrath', 'morschenich' => 'Morschenich', 'buergewald' => 'Bürgewald'];
    $mit = 0;
    foreach ($vereine as $v) {
        $u = ma_verein_benutzer(ma_verein_kurz($v['name']));
        if ($u) $mit++;
        $rolle = ma_verein_rolle($v) === 'ma_sport_partner' ? 'Sport-Partner' : 'Vereins-Partner';
        $einladen = $u ? '<a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=ma_verein_einladen&user=' . $u->ID), 'ma_verein_einladen_' . $u->ID)) . '">Einladung senden</a>' : '';
        $eingeladen = $u ? (string) get_user_meta($u->ID, 'ma_verein_eingeladen', true) : '';
        $status = $u ? '<a href="' . esc_url(get_edit_user_link($u->ID)) . '">' . esc_html($u->user_login) . '</a><br><span class="description">' . esc_html($u->user_email) . ($eingeladen !== '' ? ' · eingeladen ' . esc_html($eingeladen) : ' · noch nicht eingeladen') . '</span>' : '<em>noch kein Zugang</em>';
        printf('<tr><td>%s%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', esc_html($v['name']),
            !empty($v['website']) ? '<br><a class="description" href="' . esc_url($v['website']) . '" target="_blank" rel="noopener">' . esc_html(preg_replace('#^https?://#', '', rtrim($v['website'], '/'))) . '</a>' : '',
            esc_html($orte[$v['ort'] ?? ''] ?? 'gemeindeweit'), esc_html($rolle . (($v['sportart'] ?? '') ? ' · ' . $v['sportart'] : '')), $status, $einladen);
    }
    echo '</tbody></table><p class="description">' . (int) $mit . ' von ' . count($vereine) . ' Vereinen haben einen Zugang. Quelle der Liste: Vereinsverzeichnis der Gemeinde (Bürgerbroschüre November 2025, Amtsblätter, Heimat-Info), Stand 30.09.2026.</p></div>';
}
