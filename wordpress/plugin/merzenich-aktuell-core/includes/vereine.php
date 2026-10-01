<?php
/**
 * Vereinsplattform (01.10.2026): Vereinsprofile, Zuordnung von Redakteuren und
 * Beiträgen, mehrere Redakteure je Verein, Sperren, Passwort-Reset und ein
 * eigenes, einfaches Dashboard für Vereinsredakteure.
 *
 * Grundlage bleibt das geprüfte Vereinsverzeichnis (data/vereinsverzeichnis.json,
 * vereinszugaenge.php). Abteilungen („SC 1919 Merzenich e.V. – Tennisabteilung“)
 * bekommen kein eigenes Profil: Sie stehen als Abteilung im Profil des
 * Hauptvereins, und ihre Redakteure gehören zu diesem Verein.
 *
 * Profile (ma_club, Adresse /vereine/<slug>/) legt nur die Administration an
 * (Benutzer → Vereinszugänge → „Fehlende Vereinsprofile anlegen“). Sie enthalten
 * ausschließlich Angaben aus dem Verzeichnis: Name, Ortsteil, Kategorie,
 * Sportarten, Abteilungen, Website, Quelle. Beschreibung, Logo, Bilder und
 * Kontakt ergänzt der Verein über „Änderung vorschlagen“ (redaktion.php); die
 * Redaktion gibt frei.
 *
 * Zuordnung: user meta ma_verein_kurz (eigener Eintrag, ggf. Abteilung) und
 * ma_verein_club (Hauptverein). Beiträge und Termine eines Vereinsredakteurs
 * bekommen automatisch ma_verein = Hauptverein; die Redaktion kann die
 * Zuordnung im Kasten „Verein“ ändern.
 */
if (!defined('ABSPATH')) { exit; }

/* ---------------------------------------------------------- Verzeichnis: Hauptvereine und Abteilungen */

/** Hauptverein zu einem Verzeichniseintrag (bei Abteilungen der Verein vor „ – “). */
function ma_verein_haupt(array $v): array {
    static $namen = null;
    if ($namen === null) { $namen = []; foreach (ma_vereinsverzeichnis() as $e) $namen[$e['name']] = $e; }
    $teile = explode(' – ', (string) $v['name'], 2);
    return (count($teile) === 2 && isset($namen[$teile[0]])) ? $namen[$teile[0]] : $v;
}

function ma_verein_ist_abteilung(array $v): bool { return ma_verein_haupt($v)['name'] !== $v['name']; }

/** Hauptvereine mit ihren Abteilungen. Schlüssel: Kurzname des Hauptvereins. */
function ma_vereine_struktur(): array {
    $s = [];
    foreach (ma_vereinsverzeichnis() as $v) {
        if (empty($v['name'])) continue;
        $h = ma_verein_haupt($v); $k = ma_verein_kurz($h['name']);
        if (!isset($s[$k])) $s[$k] = ['verein' => $h, 'abteilungen' => []];
        if ($h['name'] !== $v['name']) $s[$k]['abteilungen'][] = $v;
    }
    return $s;
}

/** Kurzname des Hauptvereins eines Benutzers ('' = kein Vereinsredakteur). */
function ma_verein_von_benutzer(int $user_id): string {
    $club = (string) get_user_meta($user_id, 'ma_verein_club', true);
    if ($club !== '') return $club;
    $kurz = (string) get_user_meta($user_id, 'ma_verein_kurz', true);
    if ($kurz === '') return '';
    foreach (ma_vereinsverzeichnis() as $v) if (ma_verein_kurz($v['name']) === $kurz) return ma_verein_kurz(ma_verein_haupt($v)['name']);
    return $kurz;
}

function ma_verein_name(string $kurz): string {
    $s = ma_vereine_struktur();
    return isset($s[$kurz]) ? (string) $s[$kurz]['verein']['name'] : '';
}

/** Vereinsprofil (ma_club) zu einem Kurznamen. */
function ma_verein_profil(string $kurz): ?WP_Post {
    if ($kurz === '') return null;
    $q = get_posts(['post_type' => 'ma_club', 'post_status' => ['publish', 'draft', 'pending', 'future', 'private'], 'posts_per_page' => 1,
        'meta_key' => 'ma_verein_kurz', 'meta_value' => $kurz, 'meta_query' => [['key' => '_ma_aenderung_von', 'compare' => 'NOT EXISTS']]]);
    return $q[0] ?? null;
}

/** Alle Redakteure eines Vereins (Hauptverein und Abteilungen). */
function ma_verein_redakteure(string $kurz): array {
    return get_users(['meta_query' => ['relation' => 'OR', ['key' => 'ma_verein_club', 'value' => $kurz], ['key' => 'ma_verein_kurz', 'value' => $kurz]], 'orderby' => 'registered']);
}

/* ---------------------------------------------------------- Profile anlegen */

/** Geprüfte Profiltexte der statischen Seite (data/vereinsprofile.json, deploy/wp-theme.mjs). */
function ma_vereinsprofile_daten(): array {
    static $d = null;
    if ($d !== null) return $d;
    $pfad = MA_CORE_PATH . 'data/vereinsprofile.json';
    $j = is_readable($pfad) ? json_decode((string) file_get_contents($pfad), true) : null;
    return $d = is_array($j['profile'] ?? null) ? $j['profile'] : [];
}

/** Profiltext zu einem Verzeichniseintrag: über den Slug, sonst über den Namen. */
function ma_vereinsprofil_text(array $v): ?array {
    $d = ma_vereinsprofile_daten();
    if (!empty($v['profil']) && isset($d[$v['profil']])) return ['slug' => $v['profil']] + $d[$v['profil']];
    $norm = fn($s) => trim(preg_replace('/\s+/', ' ', str_ireplace(['e.V.', 'e. V.'], '', (string) $s)));
    foreach ($d as $slug => $e) if ($e['name'] !== '' && str_starts_with($norm($v['name']), $norm($e['name']))) return ['slug' => $slug] + $e;
    return null;
}

/** Einfaches Markdown der Profiltexte (Absätze, Links) in HTML. */
function ma_vereinsprofil_html(string $md): string {
    $absaetze = array_filter(array_map('trim', preg_split('/\n{2,}/', $md)));
    return implode("\n\n", array_map(fn($a) => '<p>' . preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', fn($m) => '<a href="' . esc_url($m[2]) . '">' . esc_html($m[1]) . '</a>', esc_html($a)) . '</p>', $absaetze));
}

/**
 * Legt fehlende Vereinsprofile aus dem Verzeichnis an (veröffentlicht, nur
 * belegte Angaben). Bestehende Profile bleiben unangetastet; ein Profil je
 * Hauptverein, Abteilungen stehen im Profil. Gibt [angelegt, vorhanden] zurück.
 */
function ma_vereinsprofile_anlegen(): array {
    $angelegt = 0; $vorhanden = 0;
    foreach (ma_vereine_struktur() as $kurz => $e) {
        if (ma_verein_profil($kurz)) { $vorhanden++; continue; }
        $v = $e['verein'];
        $text = ma_vereinsprofil_text($v);
        $slug = sanitize_title($text['slug'] ?? ($v['profil'] ?? '')) ?: sanitize_title(preg_replace('/\s*\(.*?\)\s*/', ' ', str_replace([' e.V.', ' e. V.'], '', $v['name'])));
        // Keine bestehende Meldung unter /vereine/<slug>/ verdecken.
        if (get_page_by_path($slug, OBJECT, 'post')) $slug .= '-verein';
        $sport = array_values(array_unique(array_filter(array_merge([$v['sportart'] ?? ''], array_map(fn($a) => $a['sportart'] ?? '', $e['abteilungen'])))));
        $id = wp_insert_post(['post_type' => 'ma_club', 'post_status' => 'publish', 'post_title' => $v['name'], 'post_name' => $slug,
            'post_content' => $text ? ma_vereinsprofil_html((string) $text['text']) : '', 'post_excerpt' => (string) ($text['beschreibung'] ?? ''), 'post_author' => get_current_user_id()], true);
        if (is_wp_error($id)) continue;
        update_post_meta($id, 'ma_verein_kurz', $kurz);
        update_post_meta($id, 'ma_club_kategorie', (string) ($v['kategorie'] ?? ''));
        if ($sport) update_post_meta($id, 'ma_club_sportarten', implode(', ', $sport));
        if (!empty($v['website'])) update_post_meta($id, 'ma_club_website', esc_url_raw($v['website']));
        if ($e['abteilungen']) update_post_meta($id, 'ma_club_abteilungen', implode("\n", array_map(fn($a) => trim(explode(' – ', $a['name'], 2)[1] ?? $a['name']) . (!empty($a['sportart']) ? ' (' . $a['sportart'] . ')' : '') . (!empty($a['website']) ? ' | ' . $a['website'] : ''), $e['abteilungen'])));
        update_post_meta($id, 'ma_club_quelle', (string) ($v['quelle'] ?? ''));
        update_post_meta($id, 'ma_club_geprueft', (string) ($v['geprueft'] ?? ''));
        if ($text) {
            foreach (['gegruendet' => 'ma_club_gegruendet', 'adresse' => 'ma_club_adresse'] as $k => $m) if (($text[$k] ?? '') !== '') update_post_meta($id, $m, (string) $text[$k]);
            if (!empty($text['bild']['src'])) { update_post_meta($id, 'ma_club_logo', esc_url_raw((string) $text['bild']['src'])); update_post_meta($id, 'ma_club_logo_credit', (string) ($text['bild']['credit'] ?? '')); }
            if (($text['stand'] ?? '') !== '') update_post_meta($id, 'ma_club_text_stand', (string) $text['stand']);
        }
        if (!empty($v['ort']) && term_exists($v['ort'], 'ma_location')) wp_set_object_terms($id, [$v['ort']], 'ma_location');
        if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Profil aus dem Vereinsverzeichnis angelegt', 'Quelle: ' . ($v['quelle'] ?? ''));
        $angelegt++;
    }
    return [$angelegt, $vorhanden];
}

add_action('admin_post_ma_vereinsprofile_anlegen', function (): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.', 403);
    check_admin_referer('ma_vereinsprofile_anlegen');
    [$a, $v] = ma_vereinsprofile_anlegen();
    wp_safe_redirect(add_query_arg('ma_profile', $a . '-' . $v, admin_url('users.php?page=ma-vereinszugaenge'))); exit;
});

/* ---------------------------------------------------------- Weitere Redakteure, Sperren, Passwort */

function ma_verein_redakteur_anlegen(string $kurz, string $name, string $email) {
    $s = ma_vereine_struktur();
    if (!isset($s[$kurz])) return new WP_Error('verein', 'Verein nicht im Verzeichnis.');
    $email = sanitize_email($email); $name = sanitize_text_field($name);
    if ($name === '' || !is_email($email)) return new WP_Error('daten', 'Bitte Name und gültige E-Mail angeben.');
    if (email_exists($email)) return new WP_Error('daten', 'Für diese E-Mail gibt es schon einen Zugang.');
    $base = 'verein-' . substr($kurz, 0, 40) . '-' . sanitize_user(strtolower(strtok($name, ' ')), true);
    $login = $base; $i = 2;
    while (username_exists($login)) $login = $base . '-' . $i++;
    $id = wp_insert_user(['user_login' => $login, 'user_email' => $email, 'display_name' => $name . ' (' . $s[$kurz]['verein']['name'] . ')',
        'user_pass' => wp_generate_password(24, true, true), 'role' => ma_verein_rolle($s[$kurz]['verein'])]);
    if (is_wp_error($id)) return $id;
    update_user_meta($id, 'ma_verein_kurz', $kurz);
    update_user_meta($id, 'ma_verein_club', $kurz);
    update_user_meta($id, 'ma_verein_name', $s[$kurz]['verein']['name']);
    update_user_meta($id, 'ma_verein_angelegt_von', (string) get_current_user_id());
    return (int) $id;
}

function ma_zugang_gesperrt(int $user_id): bool { return (string) get_user_meta($user_id, 'ma_zugang_gesperrt', true) === '1'; }

/** Gesperrte Zugänge können sich nicht anmelden; laufende Sitzungen enden beim Sperren. */
add_filter('authenticate', function ($user) {
    if ($user instanceof WP_User && ma_zugang_gesperrt($user->ID)) return new WP_Error('ma_gesperrt', 'Dieser Zugang ist zurzeit deaktiviert. Bitte wenden Sie sich an die Redaktion von Merzenich Aktuell.');
    return $user;
}, 99);

add_action('admin_post_ma_verein_zugang', function (): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.', 403);
    $aktion = sanitize_key($_REQUEST['aktion'] ?? '');
    $uid = (int) ($_REQUEST['user'] ?? 0);
    check_admin_referer('ma_verein_zugang_' . $aktion . '_' . $uid);
    $meldung = '';
    if ($aktion === 'neu') {
        $r = ma_verein_redakteur_anlegen(sanitize_key($_POST['verein'] ?? ''), (string) wp_unslash($_POST['name'] ?? ''), (string) wp_unslash($_POST['email'] ?? ''));
        $meldung = is_wp_error($r) ? 'Nicht angelegt: ' . $r->get_error_message() : 'Redakteur angelegt. Mit „Einladung senden“ erhält er seinen Zugang.';
    } elseif ($uid && get_user_meta($uid, 'ma_verein_kurz', true)) {
        $u = get_userdata($uid);
        if ($aktion === 'sperren') { update_user_meta($uid, 'ma_zugang_gesperrt', '1'); WP_Session_Tokens::get_instance($uid)->destroy_all(); $meldung = 'Zugang von ' . $u->display_name . ' deaktiviert.'; }
        if ($aktion === 'freigeben') { delete_user_meta($uid, 'ma_zugang_gesperrt'); $meldung = 'Zugang von ' . $u->display_name . ' wieder aktiv.'; }
        if ($aktion === 'passwort') { $r = retrieve_password($u->user_login); $meldung = $r === true ? 'Link zum Zurücksetzen des Passworts an ' . $u->user_email . ' gesendet.' : 'Mail konnte nicht gesendet werden.'; }
    }
    wp_safe_redirect(add_query_arg('ma_meldung', rawurlencode($meldung), admin_url('users.php?page=ma-vereinszugaenge'))); exit;
});

function ma_verein_zugang_link(string $aktion, int $uid): string {
    return wp_nonce_url(admin_url('admin-post.php?action=ma_verein_zugang&aktion=' . $aktion . '&user=' . $uid), 'ma_verein_zugang_' . $aktion . '_' . $uid);
}

/* ---------------------------------------------------------- Beiträge dem Verein zuordnen */

/** Partner eines Vereins: neuer Beitrag oder Termin bekommt den Verein automatisch. */
add_action('save_post', function (int $id, WP_Post $p): void {
    if (wp_is_post_revision($id) || !in_array($p->post_type, ['post', 'ma_event'], true)) return;
    if (!function_exists('ma_current_partner_policy') || !ma_current_partner_policy()) return;
    $kurz = ma_verein_von_benutzer(get_current_user_id());
    if ($kurz === '' || (string) get_post_meta($id, 'ma_verein', true) === $kurz) return;
    update_post_meta($id, 'ma_verein', $kurz);
}, 110, 2);

/** Kasten „Verein“ für die Redaktion an Meldungen und Terminen. */
add_action('add_meta_boxes', function (string $typ): void {
    if (!in_array($typ, ['post', 'ma_event'], true) || !current_user_can('edit_others_posts')) return;
    if (function_exists('ma_current_partner_policy') && ma_current_partner_policy()) return;
    add_meta_box('ma-verein', 'Verein', function (WP_Post $p): void {
        wp_nonce_field('ma_verein_zuordnen', 'ma_verein_nonce');
        $jetzt = (string) get_post_meta($p->ID, 'ma_verein', true);
        echo '<select name="ma_verein" style="width:100%"><option value="">– kein Verein –</option>';
        foreach (ma_vereine_struktur() as $k => $e) printf('<option value="%s"%s>%s</option>', esc_attr($k), selected($jetzt, $k, false), esc_html($e['verein']['name']));
        echo '</select><p class="description">Die Meldung erscheint dann auch im Profil des Vereins.</p>';
    }, $typ, 'side', 'default');
});
add_action('save_post', function (int $id): void {
    if (!isset($_POST['ma_verein_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_verein_nonce'])), 'ma_verein_zuordnen')) return;
    if (!current_user_can('edit_others_posts') || !current_user_can('edit_post', $id)) return;
    $k = sanitize_key(wp_unslash($_POST['ma_verein'] ?? ''));
    $k !== '' && isset(ma_vereine_struktur()[$k]) ? update_post_meta($id, 'ma_verein', $k) : delete_post_meta($id, 'ma_verein');
}, 20);

/** Veröffentlichte Meldungen eines Vereins (für Profil und Statistik). */
function ma_verein_beitraege(string $kurz, int $n = 6, string $typ = 'post'): array {
    return get_posts(['post_type' => $typ, 'post_status' => 'publish', 'posts_per_page' => $n, 'meta_key' => 'ma_verein', 'meta_value' => $kurz, 'orderby' => 'date', 'order' => 'DESC']);
}

/* ---------------------------------------------------------- Dashboard für Vereinsredakteure */

add_action('wp_dashboard_setup', function (): void {
    if (!function_exists('ma_current_partner_policy') || !ma_current_partner_policy()) return;
    global $wp_meta_boxes;
    // Das volle WordPress-Dashboard ist für einen Verein zu viel: nur der eigene Bereich.
    $wp_meta_boxes['dashboard'] = [];
    wp_add_dashboard_widget('ma_partner_bereich', 'Ihr Bereich bei Merzenich Aktuell', 'ma_partner_dashboard');
}, 999);

/** Nach dem Anmelden direkt ins eigene Dashboard (der Hoster leitet /wp-admin/ sonst um). */
add_filter('login_redirect', function ($ziel, $angefordert, $user) {
    if (!($user instanceof WP_User) || !function_exists('ma_current_partner_policy') || !ma_current_partner_policy($user)) return $ziel;
    return in_array((string) $angefordert, ['', admin_url(), admin_url('/'), admin_url('index.php')], true) ? admin_url('index.php') : $ziel;
}, 999, 3);

function ma_partner_dashboard(): void {
    $uid = get_current_user_id();
    $policy = ma_current_partner_policy();
    $kurz = ma_verein_von_benutzer($uid);
    $typen = $policy['post_types'];
    $status = ['draft' => 'Entwürfe', 'pending' => 'Eingereicht', 'ma_in_pruefung' => 'In Prüfung', 'ma_aenderung' => 'Änderungen angefordert', 'future' => 'Geplant', 'publish' => 'Veröffentlicht', 'ma_abgelehnt' => 'Abgelehnt'];
    echo '<style>.ma-pd__zahlen{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:8px;margin:0 0 14px}.ma-pd__zahl{border:1px solid #dcdcde;padding:8px 10px}.ma-pd__zahl strong{display:block;font-size:22px;font-variant-numeric:tabular-nums}.ma-pd__zahl.ist-wichtig{border-color:#b26200;background:#fcf9e8}.ma-pd__wege{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 14px}.ma-pd table{width:100%;border-collapse:collapse}.ma-pd td,.ma-pd th{text-align:left;padding:6px 8px 6px 0;border-top:1px solid #f0f0f1;vertical-align:top}.ma-pd__notiz{display:block;color:#8a2424;white-space:pre-line}</style><div class="ma-pd">';
    if ($kurz !== '') echo '<p><strong>' . esc_html(ma_verein_name($kurz) ?: $policy['label']) . '</strong> · ' . esc_html($policy['label']) . '</p>';
    echo '<div class="ma-pd__wege">';
    if (in_array('post', $typen, true)) echo '<a class="button button-primary" href="' . esc_url(admin_url('post-new.php')) . '">Neue Meldung schreiben</a>';
    if (in_array('ma_event', $typen, true)) echo '<a class="button" href="' . esc_url(admin_url('post-new.php?post_type=ma_event')) . '">Termin einreichen</a>';
    $profil = $kurz !== '' ? ma_verein_profil($kurz) : null;
    if ($profil && $profil->post_status === 'publish' && function_exists('ma_aenderung_link')) echo '<a class="button" href="' . esc_url(ma_aenderung_link($profil->ID)) . '">Vereinsprofil bearbeiten</a> <a class="button" href="' . esc_url(get_permalink($profil)) . '" target="_blank">Profil ansehen</a>';
    echo '</div><div class="ma-pd__zahlen">';
    $eigene = get_posts(['post_type' => $typen, 'author' => $uid, 'post_status' => array_keys($status), 'posts_per_page' => 200, 'orderby' => 'modified', 'order' => 'DESC']);
    $zahl = array_fill_keys(array_keys($status), 0);
    foreach ($eigene as $p) if (isset($zahl[$p->post_status])) $zahl[$p->post_status]++;
    foreach ($status as $s => $label) printf('<div class="ma-pd__zahl%s"><strong>%d</strong>%s</div>', $s === 'ma_aenderung' && $zahl[$s] ? ' ist-wichtig' : '', $zahl[$s], esc_html($label));
    echo '</div>';
    if (!$eigene) { echo '<p>Noch keine Beiträge. Mit „Neue Meldung schreiben“ geht es los; veröffentlicht wird nach Prüfung durch die Redaktion.</p></div>'; return; }
    $aufrufe = function_exists('ma_statistik_tabelle') ? ma_partner_aufrufe(array_map(fn($p) => $p->ID, $eigene)) : [];
    echo '<table><thead><tr><th>Beitrag</th><th>Stand</th><th>Aufrufe 30 Tage</th></tr></thead><tbody>';
    foreach (array_slice($eigene, 0, 15) as $p) {
        $notiz = $p->post_status === 'ma_aenderung' ? (string) get_post_meta($p->ID, '_ma_aenderung_notiz', true) : ($p->post_status === 'ma_abgelehnt' ? (string) get_post_meta($p->ID, '_ma_reject_reason', true) : '');
        $link = current_user_can('edit_post', $p->ID) ? get_edit_post_link($p->ID) : ($p->post_status === 'publish' ? get_permalink($p) : '');
        printf('<tr><td>%s%s</td><td>%s%s</td><td>%s</td></tr>', $link ? '<a href="' . esc_url($link) . '">' . esc_html(get_the_title($p) ?: '(ohne Titel)') . '</a>' : esc_html(get_the_title($p)),
            $p->post_status === 'publish' && function_exists('ma_aenderung_link') && ma_aenderung_erlaubt($p, wp_get_current_user()) ? '<br><a href="' . esc_url(ma_aenderung_link($p->ID)) . '">Änderung vorschlagen</a>' : '',
            esc_html(ma_status_name($p->post_status)), $notiz !== '' ? '<span class="ma-pd__notiz">Redaktion: ' . esc_html($notiz) . '</span>' : '',
            $p->post_status === 'publish' ? esc_html(number_format_i18n($aufrufe[$p->ID] ?? 0)) : '–');
    }
    echo '</tbody></table><p class="description">Aufrufe werden ohne Cookies gezählt; Sie sehen nur Ihre eigenen Beiträge.</p></div>';
}

/** Aufrufe eigener Beiträge der letzten 30 Tage (statistik.php). */
function ma_partner_aufrufe(array $ids): array {
    global $wpdb;
    $ids = array_filter(array_map('intval', $ids));
    if (!$ids) return [];
    $ab = gmdate('Y-m-d', strtotime(current_time('Y-m-d') . ' -30 days'));
    $rows = $wpdb->get_results($wpdb->prepare('SELECT objekt, SUM(aufrufe) n FROM ' . ma_statistik_tabelle() . " WHERE art = 'post' AND tag > %s AND objekt IN (" . implode(',', $ids) . ') GROUP BY objekt', $ab));
    $r = [];
    foreach ($rows as $z) $r[(int) $z->objekt] = (int) $z->n;
    return $r;
}
