<?php
/**
 * Redaktioneller Ablauf (01.10.2026): Zustände, Verlauf, Änderungen an
 * veröffentlichten Beiträgen.
 *
 * Zustände eines Beitrags (WordPress-Status):
 *   draft            Entwurf
 *   pending          Zur Prüfung eingereicht
 *   ma_in_pruefung   In redaktioneller Prüfung (Partner kann nicht mehr ändern)
 *   ma_aenderung     Änderungen angefordert (Partner überarbeitet, reicht neu ein)
 *   future           Freigegeben, Veröffentlichung geplant
 *   publish          Veröffentlicht
 *   ma_archiv        Archiviert / zurückgezogen (nicht mehr öffentlich)
 *   ma_abgelehnt     Abgelehnt (partner-notify.php)
 * Ein Partner kann nie selbst veröffentlichen: partners.php setzt jede
 * Einreichung auf pending, map_meta_cap sperrt fremde und veröffentlichte Inhalte.
 *
 * Verlauf: jede Statusänderung, Bearbeitung und Entscheidung landet mit Person
 * und Zeit in _ma_verlauf (Kasten „Verlauf“ im Beitrag, Freigabeseite).
 *
 * Änderungen an Veröffentlichtem: Ein Partner ändert seinen veröffentlichten
 * Beitrag (oder das Profil seines Vereins) nie direkt. „Änderung vorschlagen“
 * legt eine Arbeitskopie an (_ma_aenderung_von = Original). Die Live-Fassung
 * bleibt unverändert, bis die Redaktion die Kopie freigibt; dann werden Titel,
 * Text, Anriss, Bild, Orte und Profilfelder ins Original übernommen und die
 * Kopie gelöscht.
 */
if (!defined('ABSPATH')) { exit; }

const MA_STATUS_PRUEFUNG = 'ma_in_pruefung';
const MA_STATUS_AENDERUNG = 'ma_aenderung';
const MA_STATUS_ARCHIV = 'ma_archiv';
const MA_VERLAUF_META = '_ma_verlauf';
const MA_AENDERUNG_VON = '_ma_aenderung_von';
const MA_AENDERUNG_NOTIZ = '_ma_aenderung_notiz';

/** Inhaltsarten mit redaktionellem Ablauf. */
function ma_redaktion_typen(): array {
    return ['post', 'ma_event', 'ma_club', 'ma_business', 'ma_tip', 'ma_property', 'ma_ad'];
}

function ma_status_namen(): array {
    return [
        'auto-draft' => 'Entwurf', 'draft' => 'Entwurf', 'pending' => 'Zur Prüfung eingereicht',
        MA_STATUS_PRUEFUNG => 'In redaktioneller Prüfung', MA_STATUS_AENDERUNG => 'Änderungen angefordert',
        'future' => 'Freigegeben, geplant', 'publish' => 'Veröffentlicht', MA_STATUS_ARCHIV => 'Archiviert',
        'ma_abgelehnt' => 'Abgelehnt', 'trash' => 'Papierkorb', 'private' => 'Privat', 'new' => 'Neu',
    ];
}

function ma_status_name(string $s): string { return ma_status_namen()[$s] ?? $s; }

add_action('init', function (): void {
    foreach ([MA_STATUS_PRUEFUNG => 'In Prüfung', MA_STATUS_AENDERUNG => 'Änderungen angefordert', MA_STATUS_ARCHIV => 'Archiviert'] as $s => $label) {
        register_post_status($s, [
            'label' => $label, 'public' => false, 'internal' => false, 'protected' => true, 'exclude_from_search' => true,
            'show_in_admin_all_list' => true, 'show_in_admin_status_list' => true,
            'label_count' => _n_noop($label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>'),
        ]);
    }
}, 5);

add_filter('display_post_states', function (array $st, WP_Post $p): array {
    if (in_array($p->post_status, [MA_STATUS_PRUEFUNG, MA_STATUS_AENDERUNG, MA_STATUS_ARCHIV], true)) $st[$p->post_status] = ma_status_name($p->post_status);
    if (get_post_meta($p->ID, MA_AENDERUNG_VON, true)) $st['ma_aenderung_kopie'] = 'Änderung an veröffentlichtem Inhalt';
    return $st;
}, 10, 2);

/* ---------------------------------------------------------- Verlauf */

function ma_verlauf_eintragen(int $id, string $aktion, string $notiz = '', ?int $user = null): void {
    if ($id <= 0) return;
    $l = get_post_meta($id, MA_VERLAUF_META, true);
    if (!is_array($l)) $l = [];
    $u = $user ?? (int) get_current_user_id();
    $ud = $u ? get_userdata($u) : null;
    $l[] = ['t' => time(), 'u' => $u, 'name' => $ud ? ($ud->display_name ?: $ud->user_login) : 'System', 'a' => $aktion, 'n' => $notiz];
    if (count($l) > 300) $l = array_slice($l, -300);
    update_post_meta($id, MA_VERLAUF_META, $l);
}

function ma_verlauf(int $id): array {
    $l = get_post_meta($id, MA_VERLAUF_META, true);
    return is_array($l) ? $l : [];
}

/** Text für einen Statuswechsel im Verlauf. */
function ma_verlauf_text(string $neu, string $alt, WP_Post $p): string {
    if ($neu === 'draft' && !empty($GLOBALS['ma_einreichung_angehalten'][$p->ID])) return 'Einreichung angehalten: Bild- und Nutzungsrechte nicht bestätigt';
    if ($neu === 'draft' && in_array($alt, ['new', 'auto-draft'], true)) return 'Entwurf angelegt';
    if ($neu === 'draft' && $alt === 'publish') return 'Zurückgezogen (wieder Entwurf)';
    if ($neu === 'draft') return 'Als Entwurf gespeichert';
    if ($neu === 'pending') return in_array($alt, [MA_STATUS_AENDERUNG, 'ma_abgelehnt'], true) ? 'Überarbeitet und erneut eingereicht' : 'Zur Prüfung eingereicht';
    if ($neu === 'future') return 'Freigegeben, Veröffentlichung geplant für ' . mysql2date('d.m.Y H:i', $p->post_date) . ' Uhr';
    if ($neu === 'publish') return $alt === 'future' ? 'Planmäßig veröffentlicht' : 'Freigegeben und veröffentlicht';
    return ma_status_name($neu);
}

add_action('transition_post_status', function (string $neu, string $alt, WP_Post $p): void {
    if ($neu === $alt || !in_array($p->post_type, ma_redaktion_typen(), true)) return;
    if ($neu === 'auto-draft' || wp_is_post_revision($p->ID) || !empty($GLOBALS['ma_verlauf_still'])) return;
    $notiz = '';
    if ($neu === MA_STATUS_AENDERUNG) $notiz = (string) get_post_meta($p->ID, MA_AENDERUNG_NOTIZ, true);
    if ($neu === 'ma_abgelehnt') $notiz = (string) get_post_meta($p->ID, '_ma_reject_reason', true);
    // Geplante Veröffentlichung läuft per Cron ohne angemeldete Person.
    ma_verlauf_eintragen($p->ID, ma_verlauf_text($neu, $alt, $p), $notiz, ($neu === 'publish' && $alt === 'future') ? 0 : null);
}, 5, 3);

/** Inhaltliche Bearbeitung festhalten, höchstens ein Eintrag je Person in 15 Minuten. */
add_action('post_updated', function (int $id, WP_Post $nach, WP_Post $vor): void {
    if (!in_array($nach->post_type, ma_redaktion_typen(), true) || wp_is_post_revision($id)) return;
    if ($nach->post_title === $vor->post_title && $nach->post_content === $vor->post_content && $nach->post_excerpt === $vor->post_excerpt) return;
    $u = (int) get_current_user_id();
    foreach (array_reverse(ma_verlauf($id)) as $e) {
        if (($e['u'] ?? 0) === $u && ($e['a'] ?? '') === 'Bearbeitet' && time() - (int) $e['t'] < 900) return;
        if (time() - (int) ($e['t'] ?? 0) >= 900) break;
    }
    ma_verlauf_eintragen($id, 'Bearbeitet');
}, 10, 3);

/** Kasten „Verlauf“: für die Redaktion und für den eigenen Autor. */
add_action('add_meta_boxes', function (string $typ, $post): void {
    if (!in_array($typ, ma_redaktion_typen(), true) || !$post instanceof WP_Post) return;
    if (!current_user_can('edit_others_posts') && (int) $post->post_author !== get_current_user_id()) return;
    add_meta_box('ma-verlauf', 'Verlauf', 'ma_verlauf_box', $typ, 'side', 'low');
}, 10, 2);

function ma_verlauf_html(int $id, int $max = 30): string {
    $l = array_reverse(ma_verlauf($id));
    if (!$l) return '<p class="description">Noch keine Einträge. Der Verlauf beginnt mit dieser Version des Plugins.</p>';
    $h = '<ol class="ma-verlauf">';
    foreach (array_slice($l, 0, $max) as $e) {
        $h .= '<li><span class="ma-verlauf__zeit">' . esc_html(wp_date('d.m.Y H:i', (int) $e['t'])) . '</span> <strong>' . esc_html((string) $e['a']) . '</strong>'
            . ' <span class="ma-verlauf__wer">' . esc_html((string) $e['name']) . '</span>'
            . (($e['n'] ?? '') !== '' ? '<br><span class="ma-verlauf__notiz">' . esc_html((string) $e['n']) . '</span>' : '') . '</li>';
    }
    return $h . '</ol>';
}

function ma_verlauf_box(WP_Post $p): void {
    echo '<style>.ma-verlauf{margin:0;padding:0;list-style:none}.ma-verlauf li{padding:5px 0;border-top:1px solid #f0f0f1;font-size:12px}.ma-verlauf__zeit,.ma-verlauf__wer{color:#646970}.ma-verlauf__notiz{display:block;margin-top:2px;color:#1d2327;white-space:pre-line}</style>';
    echo '<p><strong>Stand:</strong> ' . esc_html(ma_status_name($p->post_status)) . '</p>' . ma_verlauf_html($p->ID);
}

/* ---------------------------------------------------------- Rechte der Partner im Ablauf */

/**
 * Partner bearbeiten nur, was bei ihnen liegt: Entwurf, Änderungen angefordert,
 * Abgelehnt. In Prüfung, geplant, veröffentlicht oder archiviert ist gesperrt.
 */
add_filter('map_meta_cap', function (array $caps, string $cap, int $user_id, array $args): array {
    if (!in_array($cap, ['edit_post', 'delete_post'], true) || empty($args[0])) return $caps;
    $user = get_userdata($user_id);
    if (!$user || !function_exists('ma_current_partner_policy') || !ma_current_partner_policy($user)) return $caps;
    $p = get_post((int) $args[0]);
    if ($p && in_array($p->post_status, [MA_STATUS_PRUEFUNG, MA_STATUS_ARCHIV, 'future'], true)) return ['do_not_allow'];
    return $caps;
}, 30, 4);

/** Hinweis im Editor des Partners: was die Redaktion ändern möchte. */
add_action('admin_notices', function (): void {
    $p = $GLOBALS['post'] ?? null;
    if (!$p instanceof WP_Post || !function_exists('get_current_screen') || (get_current_screen()->base ?? '') !== 'post') return;
    if ($p->post_status === MA_STATUS_AENDERUNG) {
        $n = (string) get_post_meta($p->ID, MA_AENDERUNG_NOTIZ, true);
        echo '<div class="notice notice-warning"><p><strong>Die Redaktion bittet um Änderungen.</strong></p>' . ($n !== '' ? '<p style="white-space:pre-line">' . esc_html($n) . '</p>' : '')
            . '<p>Bitte überarbeiten und mit „Zur Prüfung einreichen“ erneut senden.</p></div>';
    }
    if ($von = (int) get_post_meta($p->ID, MA_AENDERUNG_VON, true)) {
        echo '<div class="notice notice-info"><p><strong>Änderungsvorschlag zu „' . esc_html(get_the_title($von)) . '“.</strong> Die veröffentlichte Fassung bleibt unverändert, bis die Redaktion diesen Vorschlag freigibt. '
            . (current_user_can('edit_others_posts') ? 'Mit „Freigeben“ unter Merzenich Aktuell → Freigaben (oder „Veröffentlichen“ hier) werden die Änderungen ins Original übernommen.' : '') . '</p></div>';
    }
});

/* ---------------------------------------------------------- Änderungen an Veröffentlichtem */

/** Darf $user zu $orig eine Änderung vorschlagen? Eigener Beitrag oder Profil des eigenen Vereins. */
function ma_aenderung_erlaubt(WP_Post $orig, WP_User $user): bool {
    if (!in_array($orig->post_status, ['publish', 'future'], true)) return false;
    if (get_post_meta($orig->ID, MA_AENDERUNG_VON, true)) return false;
    $policy = function_exists('ma_current_partner_policy') ? ma_current_partner_policy($user) : null;
    if (!$policy || !in_array($orig->post_type, $policy['post_types'], true)) return false;
    if ((int) $orig->post_author === (int) $user->ID) return true;
    if ($orig->post_type === 'ma_club' && function_exists('ma_verein_von_benutzer')) {
        $kurz = ma_verein_von_benutzer($user->ID);
        return $kurz !== '' && (string) get_post_meta($orig->ID, 'ma_verein_kurz', true) === $kurz;
    }
    return false;
}

/** Offene Kopie zu einem Original (eine je Person). */
function ma_aenderung_offen(int $orig, int $user): int {
    $q = get_posts(['post_type' => get_post_type($orig), 'post_status' => ['draft', 'pending', MA_STATUS_PRUEFUNG, MA_STATUS_AENDERUNG, 'ma_abgelehnt'], 'author' => $user,
        'meta_key' => MA_AENDERUNG_VON, 'meta_value' => (string) $orig, 'posts_per_page' => 1, 'fields' => 'ids']);
    return (int) ($q[0] ?? 0);
}

/** Profil- und Inhaltsfelder, die mit einer Änderung übernommen werden. */
function ma_aenderung_felder(string $typ): array {
    $f = ['ma_kicker', 'ma_image_credit', 'ma_image_license', 'ma_image_type'];
    if ($typ === 'ma_club') $f = array_merge($f, ['ma_club_website', 'ma_club_kontakt', 'ma_club_telefon', 'ma_club_email', 'ma_club_adresse', 'ma_club_abteilungen', 'ma_club_mannschaften', 'ma_club_galerie', 'ma_club_logo']);
    if ($typ === 'ma_event') $f = array_merge($f, ['ma_event_start', 'ma_event_end', 'ma_event_place', 'ma_event_organizer', 'ma_event_price', 'ma_event_registration']);
    return $f;
}

function ma_aenderung_anlegen(int $orig_id, int $user_id) {
    $orig = get_post($orig_id); $user = get_userdata($user_id);
    if (!$orig || !$user || !ma_aenderung_erlaubt($orig, $user)) return new WP_Error('ma_aenderung', 'Zu diesem Inhalt können Sie keine Änderung vorschlagen.');
    if ($offen = ma_aenderung_offen($orig_id, $user_id)) return $offen;
    $id = wp_insert_post(['post_type' => $orig->post_type, 'post_status' => 'draft', 'post_author' => $user_id, 'post_title' => $orig->post_title,
        'post_content' => $orig->post_content, 'post_excerpt' => $orig->post_excerpt], true);
    if (is_wp_error($id)) return $id;
    update_post_meta($id, MA_AENDERUNG_VON, (string) $orig_id);
    if ($t = get_post_thumbnail_id($orig_id)) set_post_thumbnail($id, $t);
    foreach (['category', 'ma_location', 'post_tag'] as $tax) {
        if (!is_object_in_taxonomy($orig->post_type, $tax)) continue;
        wp_set_object_terms($id, wp_get_object_terms($orig_id, $tax, ['fields' => 'ids']), $tax);
    }
    foreach (ma_aenderung_felder($orig->post_type) as $k) { $v = get_post_meta($orig_id, $k, true); if ($v !== '') update_post_meta($id, $k, $v); }
    if ($orig->post_type === 'ma_club') { $GLOBALS['ma_system_schreibt'] = true; update_post_meta($id, 'ma_verein_kurz', (string) get_post_meta($orig_id, 'ma_verein_kurz', true)); $GLOBALS['ma_system_schreibt'] = false; }
    ma_verlauf_eintragen($orig_id, 'Änderungsvorschlag begonnen', '', $user_id);
    return (int) $id;
}

/** Freigegebene Kopie ins Original übernehmen, Kopie löschen. Gibt die ID des Originals zurück. */
function ma_aenderung_uebernehmen(int $kopie_id): int {
    $k = get_post($kopie_id);
    $orig_id = $k ? (int) get_post_meta($kopie_id, MA_AENDERUNG_VON, true) : 0;
    $o = $orig_id ? get_post($orig_id) : null;
    if (!$k || !$o) return 0;
    wp_update_post(['ID' => $orig_id, 'post_title' => $k->post_title, 'post_content' => $k->post_content, 'post_excerpt' => $k->post_excerpt]);
    $t = (int) get_post_thumbnail_id($kopie_id);
    // Neues Bild: Die Prüfung der Bildrechte durch die Redaktion gilt nicht mehr automatisch.
    if ($t !== (int) get_post_thumbnail_id($orig_id)) delete_post_meta($orig_id, 'ma_image_rights_verified');
    if ($t) set_post_thumbnail($orig_id, $t); else delete_post_thumbnail($orig_id);
    foreach (['ma_location', 'post_tag'] as $tax) {
        if (is_object_in_taxonomy($o->post_type, $tax)) wp_set_object_terms($orig_id, wp_get_object_terms($kopie_id, $tax, ['fields' => 'ids']), $tax);
    }
    foreach (ma_aenderung_felder($o->post_type) as $f) { $v = get_post_meta($kopie_id, $f, true); $v === '' ? delete_post_meta($orig_id, $f) : update_post_meta($orig_id, $f, $v); }
    // Bildrechte-Nachweise des Einsenders wandern mit.
    foreach (get_post_meta($kopie_id, '_ma_bildrechte_log') ?: [] as $eintrag) add_post_meta($orig_id, '_ma_bildrechte_log', $eintrag);
    $autor = get_userdata((int) $k->post_author);
    ma_verlauf_eintragen($orig_id, 'Änderung übernommen', 'Vorgeschlagen von ' . ($autor ? ($autor->display_name ?: $autor->user_login) : 'unbekannt'));
    if ($autor && is_email($autor->user_email) && (int) $autor->ID !== get_current_user_id()) {
        wp_mail($autor->user_email, '[Merzenich Aktuell] Ihre Änderung ist übernommen',
            (function_exists('ma_partner_mail_greeting') ? ma_partner_mail_greeting($autor) : "Guten Tag,\n\n") . 'die Redaktion hat Ihre Änderung an „' . $o->post_title . '“ übernommen.' . "\n\n" . get_permalink($orig_id) . "\n\nRedaktion Merzenich Aktuell",
            function_exists('ma_partner_mail_headers') ? ma_partner_mail_headers() : []);
    }
    wp_delete_post($kopie_id, true);
    return $orig_id;
}

add_action('admin_post_ma_aenderung_vorschlagen', function (): void {
    $id = (int) ($_GET['post'] ?? 0);
    check_admin_referer('ma_aenderung_' . $id);
    $neu = ma_aenderung_anlegen($id, get_current_user_id());
    if (is_wp_error($neu)) wp_die(esc_html($neu->get_error_message()), 403);
    wp_safe_redirect(admin_url('post.php?post=' . (int) $neu . '&action=edit')); exit;
});

function ma_aenderung_link(int $id): string {
    return wp_nonce_url(admin_url('admin-post.php?action=ma_aenderung_vorschlagen&post=' . $id), 'ma_aenderung_' . $id);
}

foreach (['post_row_actions', 'page_row_actions'] as $f) {
    add_filter($f, function (array $a, WP_Post $p): array {
        $u = wp_get_current_user();
        if ($u->exists() && ma_aenderung_erlaubt($p, $u)) $a['ma_aenderung'] = '<a href="' . esc_url(ma_aenderung_link($p->ID)) . '">Änderung vorschlagen</a>';
        return $a;
    }, 10, 2);
}

/* Veröffentlicht die Redaktion eine Kopie direkt im Editor: übernehmen statt doppelt veröffentlichen. */
add_action('wp_after_insert_post', function (int $id, WP_Post $p): void {
    if ($p->post_status !== 'publish' || !get_post_meta($id, MA_AENDERUNG_VON, true)) return;
    if (function_exists('ma_current_partner_policy') && ma_current_partner_policy()) return;
    $orig = ma_aenderung_uebernehmen($id);
    if ($orig) $GLOBALS['ma_aenderung_ziel'] = $orig;
}, 50, 2);
add_filter('redirect_post_location', function (string $ziel): string {
    return !empty($GLOBALS['ma_aenderung_ziel']) ? admin_url('post.php?post=' . (int) $GLOBALS['ma_aenderung_ziel'] . '&action=edit&message=1') : $ziel;
});

/* ---------------------------------------------------------- Entscheidungen der Redaktion */

/**
 * Aktionen der Freigabeseite: pruefen, aenderung, ablehnen, archivieren.
 * Freigeben und Planen laufen über ma_schnellfreigabe() (relevanz.php).
 * @return array{ok:bool,meldung:string}
 */
function ma_redaktion_aktion(int $id, string $aktion, string $notiz = ''): array {
    $p = get_post($id);
    if (!$p || !in_array($p->post_type, ma_redaktion_typen(), true)) return ['ok' => false, 'meldung' => 'Inhalt nicht gefunden.'];
    if ($aktion === 'pruefen') {
        if (!in_array($p->post_status, ['pending', 'draft'], true)) return ['ok' => false, 'meldung' => 'Nur eingereichte Beiträge lassen sich in Prüfung nehmen.'];
        wp_update_post(['ID' => $id, 'post_status' => MA_STATUS_PRUEFUNG]);
        update_post_meta($id, '_ma_pruefer', (string) get_current_user_id());
        return ['ok' => true, 'meldung' => 'In Prüfung. Der Einsender kann bis zur Entscheidung nichts mehr ändern.'];
    }
    if ($aktion === 'aenderung') {
        if ($notiz === '') return ['ok' => false, 'meldung' => 'Bitte kurz schreiben, was geändert werden soll.'];
        if ($p->post_status === 'publish') return ['ok' => false, 'meldung' => 'Veröffentlichte Beiträge zuerst archivieren.'];
        update_post_meta($id, MA_AENDERUNG_NOTIZ, $notiz);
        wp_update_post(['ID' => $id, 'post_status' => MA_STATUS_AENDERUNG]);
        ma_redaktion_mail_aenderung($p, $notiz);
        return ['ok' => true, 'meldung' => 'Änderungen angefordert. Der Einsender ist per E-Mail informiert.'];
    }
    if ($aktion === 'ablehnen') {
        if ($p->post_status === 'publish') return ['ok' => false, 'meldung' => 'Veröffentlichte Beiträge bitte archivieren.'];
        $notiz !== '' ? update_post_meta($id, '_ma_reject_reason', $notiz) : delete_post_meta($id, '_ma_reject_reason');
        wp_update_post(['ID' => $id, 'post_status' => defined('MA_REJECTED_STATUS') ? MA_REJECTED_STATUS : 'ma_abgelehnt']);
        return ['ok' => true, 'meldung' => 'Abgelehnt. Der Einsender ist per E-Mail informiert.'];
    }
    if ($aktion === 'archivieren') {
        if ($p->post_status !== 'publish') return ['ok' => false, 'meldung' => 'Nur Veröffentlichtes lässt sich archivieren.'];
        if ($notiz !== '') ma_verlauf_eintragen($id, 'Grund der Archivierung', $notiz);
        wp_update_post(['ID' => $id, 'post_status' => MA_STATUS_ARCHIV]);
        return ['ok' => true, 'meldung' => 'Archiviert. Der Beitrag ist nicht mehr öffentlich.'];
    }
    return ['ok' => false, 'meldung' => 'Unbekannte Aktion.'];
}

function ma_redaktion_mail_aenderung(WP_Post $p, string $notiz): void {
    $autor = get_userdata((int) $p->post_author);
    if (!$autor || !is_email($autor->user_email) || (int) $autor->ID === get_current_user_id()) return;
    $body = (function_exists('ma_partner_mail_greeting') ? ma_partner_mail_greeting($autor) : "Guten Tag,\n\n")
        . 'die Redaktion bittet um Änderungen an Ihrem Beitrag „' . ($p->post_title ?: '(ohne Titel)') . "“:\n\n" . $notiz
        . "\n\nBitte überarbeiten Sie ihn in Ihrem Zugang und reichen Sie ihn erneut ein:\n" . admin_url('post.php?post=' . $p->ID . '&action=edit') . "\n\nRedaktion Merzenich Aktuell";
    wp_mail($autor->user_email, '[Merzenich Aktuell] Bitte um Änderungen an Ihrem Beitrag', $body, function_exists('ma_partner_mail_headers') ? ma_partner_mail_headers() : []);
}

add_action('wp_ajax_ma_redaktion_aktion', function (): void {
    check_ajax_referer('ma_relevanz', 'nonce');
    $id = (int) ($_POST['post'] ?? 0);
    $darf = function_exists('ma_relevanz_darf') ? ma_relevanz_darf() : current_user_can('edit_others_posts');
    if (!$darf || !$id || !current_user_can('edit_post', $id) || !current_user_can('publish_posts')) wp_send_json_error(['meldung' => 'Keine Berechtigung.'], 403);
    $e = ma_redaktion_aktion($id, sanitize_key(wp_unslash($_POST['aktion'] ?? '')), sanitize_textarea_field(wp_unslash($_POST['notiz'] ?? '')));
    $e['ok'] ? wp_send_json_success($e) : wp_send_json_error($e);
});

/* ---------------------------------------------------------- Sicherheit */

/**
 * Felder, die nur die Redaktion setzt. Partner schreiben sie weder über den
 * Editor noch über die Zwischenspeicherung (freigabe.php) noch über REST.
 */
function ma_redaktion_nur_felder(): array {
    return ['ma_editorial_priority', 'ma_top_until', 'ma_top_pinned', 'ma_reviewed_by', 'ma_reviewed_at', 'ma_editorial_responsibility', 'ma_relevanz', 'ma_startplatz', 'ma_gesponsert', 'ma_verein_kurz'];
}

add_filter('update_post_metadata', 'ma_redaktion_meta_sperre', 5, 3);
add_filter('add_post_metadata', 'ma_redaktion_meta_sperre', 5, 3);
function ma_redaktion_meta_sperre($check, $id, $key) {
    if (!in_array($key, ma_redaktion_nur_felder(), true) || !function_exists('ma_current_partner_policy') || !ma_current_partner_policy()) return $check;
    // Systemseitige Zuordnung (Vereinskennung einer Kopie oder eines neuen Beitrags) setzt ma_verein_kurz selbst.
    if ($key === 'ma_verein_kurz' && !empty($GLOBALS['ma_system_schreibt'])) return $check;
    return false;
}
