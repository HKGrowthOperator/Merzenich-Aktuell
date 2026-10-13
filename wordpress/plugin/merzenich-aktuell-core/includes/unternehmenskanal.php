<?php
/**
 * Unternehmenskanäle (1.28.0, Auftrag Betreiber 13.10.2026, Vorbild Oberberg
 * Aktuell): Jede Firma mit eigenem Zugang hat einen Kanal unter
 * /unternehmen/<profil-slug>/ mit ihrem Profil (ma_business) und ihren
 * Beiträgen. Kein eigenes System: Zugang ist die vorhandene Rolle
 * „Unternehmens-Partner“ (partners.php), Beiträge sind normale Beiträge mit
 * Einreichung und Freigabe, das Profil ist der Inhaltstyp „Unternehmen“.
 *
 * Neu ist nur die Verknüpfung, nach dem Muster der Vereinszugänge:
 * - Benutzer-Meta ma_business_id: Zugang ↔ Profil (setzt die Administration unter
 *   Benutzer → Partner-Zugänge oder am Benutzerprofil).
 * - Beitrags-Meta ma_business_id: Beitrag ↔ Profil. Beiträge, Anzeigen und Tipps
 *   eines verknüpften Zugangs bekommen sie beim Speichern automatisch; die
 *   Redaktion kann sie im Kasten „Kennzeichnung“ setzen. Der Zugang selbst kann
 *   sie nicht ändern (ma_redaktion_nur_felder, redaktion.php).
 *
 * Bearbeiten darf ein Zugang wie bisher nur eigene, nicht veröffentlichte
 * Inhalte; das veröffentlichte Profil ändert er über „Änderung vorschlagen“.
 */
if (!defined('ABSPATH')) { exit; }

const MA_FIRMA_META = 'ma_business_id';

/** Firmenprofil (ma_business) eines Zugangs; 0 = keins oder gelöscht. */
function ma_business_von_benutzer(int $uid): int {
    $id = (int) get_user_meta($uid, MA_FIRMA_META, true);
    if ($id <= 0) return 0;
    $p = get_post($id);
    return ($p instanceof WP_Post && $p->post_type === 'ma_business' && $p->post_status !== 'trash') ? $id : 0;
}

/** Firmenprofil eines Beitrags; 0 = keins. */
function ma_business_von_beitrag(int $post_id): int {
    $id = (int) get_post_meta($post_id, MA_FIRMA_META, true);
    if ($id <= 0) return 0;
    $p = get_post($id);
    return ($p instanceof WP_Post && $p->post_type === 'ma_business') ? $id : 0;
}

/**
 * Veröffentlichte Beiträge (oder Anzeigen, Tipps) eines Firmenprofils, neueste zuerst.
 * Beiträge nur, wenn sie als gesponsert gekennzeichnet sind: Der Kanal zeigt Anzeigen
 * der Firma, ein redaktioneller Bericht über sie gehört nicht hinein.
 */
function ma_business_beitraege(int $bid, int $n = 6, string $typ = 'post'): array {
    if ($bid <= 0) return [];
    $mq = [['key' => MA_FIRMA_META, 'value' => (string) $bid]];
    if ($typ === 'post') $mq[] = ['key' => 'ma_gesponsert', 'value' => '1'];
    return get_posts(['post_type' => $typ, 'post_status' => 'publish', 'posts_per_page' => $n, 'meta_query' => $mq,
        'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true]);
}

/** Alle Status außer Papierkorb, automatischem Entwurf und Revision (auch In Prüfung, Änderung, Archiv, Abgelehnt). */
function ma_business_alle_status(): array {
    return array_values(array_diff(get_post_stati(), ['trash', 'auto-draft', 'inherit']));
}

/** Adresse des Unternehmenskanals (Theme, inc/unternehmen.php). */
function ma_business_kanal_url($b): string {
    $p = $b instanceof WP_Post ? $b : get_post((int) $b);
    return ($p instanceof WP_Post && $p->post_name !== '') ? home_url('/unternehmen/' . $p->post_name . '/') : '';
}

/** Profile zur Auswahl in der Verwaltung (alle außer Papierkorb und Änderungskopien), nicht veröffentlichte mit Status. */
function ma_business_auswahl(): array {
    $l = get_posts(['post_type' => 'ma_business', 'post_status' => ma_business_alle_status(), 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC',
        'meta_query' => [['key' => '_ma_aenderung_von', 'compare' => 'NOT EXISTS']]]);
    $r = [];
    foreach ($l as $p) {
        $o = $p->post_status !== 'publish' ? get_post_status_object($p->post_status) : null;
        $r[(int) $p->ID] = ($p->post_title !== '' ? $p->post_title : '(ohne Namen)') . ($o ? ' (' . $o->label . ')' : '');
    }
    return $r;
}

/**
 * Optionen der Profilauswahl. Der aktuelle Wert steht immer darin, auch wenn er
 * nicht in der Liste ist; dazu ein verstecktes Feld mit dem Ausgangswert, damit
 * Speichern ohne Änderung die Verknüpfung nie löscht.
 */
function ma_business_auswahl_html(string $name, int $jetzt, string $leer, bool $neu = false): string {
    $l = ma_business_auswahl();
    if ($jetzt > 0 && !isset($l[$jetzt]) && get_post_type($jetzt) === 'ma_business') $l = [$jetzt => get_the_title($jetzt) . ' (' . (get_post_status_object((string) get_post_status($jetzt))->label ?? '') . ')'] + $l;
    $h = '<input type="hidden" name="' . esc_attr($name) . '_alt" value="' . esc_attr($jetzt > 0 ? (string) $jetzt : '') . '">'
        . '<select id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" style="max-width:100%"><option value="">' . esc_html($leer) . '</option>'
        . ($neu ? '<option value="neu">Neues Profil als Entwurf anlegen</option>' : '');
    foreach ($l as $id => $titel) $h .= sprintf('<option value="%d"%s>%s</option>', $id, selected($jetzt, $id, false), esc_html($titel));
    return $h . '</select>';
}

/** Wurde die Auswahl gegenüber dem Ausgangswert geändert? (Formular ohne Ausgangswert: ja.) */
function ma_business_auswahl_geaendert(string $name): bool {
    if (!array_key_exists($name, $_POST)) return false;
    if (!array_key_exists($name . '_alt', $_POST)) return true;
    return sanitize_text_field(wp_unslash((string) $_POST[$name])) !== sanitize_text_field(wp_unslash((string) $_POST[$name . '_alt']));
}

/**
 * Zugang mit einem Profil verknüpfen. $wahl: 'neu' (Entwurf mit dem Zugang als
 * Autor anlegen, damit die Firma ihr Profil selbst ausfüllt und einreicht),
 * eine Profil-ID oder '' (Verknüpfung lösen). Bestehende Beiträge des Zugangs
 * ohne Firma werden nachgetragen. Gibt die Profil-ID zurück (0 = keins).
 */
function ma_partner_business_verknuepfen(int $uid, string $wahl, string $name = ''): int {
    if ($wahl === '') { delete_user_meta($uid, MA_FIRMA_META); return 0; }
    if ($wahl === 'neu') {
        $bid = wp_insert_post(['post_type' => 'ma_business', 'post_status' => 'draft', 'post_author' => $uid, 'post_title' => $name !== '' ? $name : 'Unternehmensprofil'], true);
        if (is_wp_error($bid)) return 0;
    } else {
        $bid = (int) $wahl;
        $p = get_post($bid);
        if (!$p instanceof WP_Post || $p->post_type !== 'ma_business') return 0;
    }
    update_user_meta($uid, MA_FIRMA_META, (string) $bid);
    // Ausdrücklich alle Status: 'any' ließe In Prüfung, Änderung, Archiv und Abgelehnt aus.
    $alt = get_posts(['post_type' => ['post', 'ma_ad', 'ma_tip'], 'post_status' => ma_business_alle_status(), 'author' => $uid, 'posts_per_page' => -1, 'fields' => 'ids',
        'meta_query' => [['key' => MA_FIRMA_META, 'compare' => 'NOT EXISTS']]]);
    $GLOBALS['ma_system_schreibt'] = true;
    foreach ($alt as $id) update_post_meta((int) $id, MA_FIRMA_META, (string) $bid);
    unset($GLOBALS['ma_system_schreibt']);
    return (int) $bid;
}

/* ---------------------------------------------------------- Beiträge der Firma zuordnen */

/** Beitrag, Anzeige oder Tipp eines verknüpften Zugangs bekommt die Firma automatisch (wie ma_verein in vereine.php). */
function ma_business_beitrag_zuordnen(int $id, WP_Post $p): void {
    if (wp_is_post_revision($id) || !in_array($p->post_type, ['post', 'ma_ad', 'ma_tip'], true)) return;
    if (!function_exists('ma_current_partner_policy') || !ma_current_partner_policy()) return;
    $uid = get_current_user_id();
    if ((int) $p->post_author !== $uid || get_post_meta($id, '_ma_aenderung_von', true)) return;
    $bid = ma_business_von_benutzer($uid);
    if (!$bid || (int) get_post_meta($id, MA_FIRMA_META, true) === $bid) return;
    $GLOBALS['ma_system_schreibt'] = true;
    update_post_meta($id, MA_FIRMA_META, (string) $bid);
    unset($GLOBALS['ma_system_schreibt']);
}
add_action('save_post', 'ma_business_beitrag_zuordnen', 110, 2);

/* ---------------------------------------------------------- Verwaltung: Benutzerprofil */

/** Auswahl am Benutzerprofil eines Unternehmens-Partners (nur Administration). */
function ma_business_profilfeld(WP_User $u): void {
    if (!current_user_can('edit_users') || (function_exists('ma_current_partner_policy') && ma_current_partner_policy())) return;
    if (!in_array('ma_wirtschaft_partner', (array) $u->roles, true)) return;
    $jetzt = ma_business_von_benutzer($u->ID);
    wp_nonce_field('ma_business_zugang_' . $u->ID, 'ma_business_zugang_nonce');
    echo '<h2>Unternehmenskanal</h2><table class="form-table" role="presentation"><tr><th><label for="ma_business_id">Unternehmensprofil</label></th><td>';
    echo ma_business_auswahl_html('ma_business_id', (int) get_user_meta($u->ID, MA_FIRMA_META, true), '– kein Profil –', true);
    echo '<p class="description">Beiträge dieses Zugangs erscheinen im Kanal des gewählten Unternehmens unter /unternehmen/…/ (nach Freigabe durch die Redaktion).';
    if ($jetzt && ($url = ma_business_kanal_url($jetzt)) && get_post_status($jetzt) === 'publish') echo ' <a href="' . esc_url($url) . '" target="_blank">Kanal ansehen</a>';
    echo '</p></td></tr></table>';
}
add_action('edit_user_profile', 'ma_business_profilfeld');
add_action('show_user_profile', 'ma_business_profilfeld');

function ma_business_profilfeld_speichern(int $uid): void {
    if (!isset($_POST['ma_business_zugang_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_business_zugang_nonce'])), 'ma_business_zugang_' . $uid)) return;
    if (!current_user_can('edit_users') || (function_exists('ma_current_partner_policy') && ma_current_partner_policy())) return;
    $u = get_userdata($uid);
    if (!$u || !in_array('ma_wirtschaft_partner', (array) $u->roles, true)) return;
    if (!ma_business_auswahl_geaendert('ma_business_id')) return;
    $wahl = sanitize_text_field(wp_unslash($_POST['ma_business_id'] ?? ''));
    if ($wahl !== 'neu' && $wahl !== '' && !ctype_digit($wahl)) return;
    if ($wahl === (string) ma_business_von_benutzer($uid)) return;
    ma_partner_business_verknuepfen($uid, $wahl, (string) $u->display_name);
}
add_action('edit_user_profile_update', 'ma_business_profilfeld_speichern');
add_action('personal_options_update', 'ma_business_profilfeld_speichern');

/* ---------------------------------------------------------- Dashboard des Zugangs */

/** Zeile im Dashboard eines Unternehmens-Partners (vereine.php, ma_partner_dashboard). */
function ma_business_dashboard_zeile(int $uid): void {
    $u = get_userdata($uid);
    if (!$u || !in_array('ma_wirtschaft_partner', (array) $u->roles, true)) return;
    $bid = ma_business_von_benutzer($uid);
    if (!$bid) {
        echo '<div class="notice notice-warning inline"><p><strong>Noch kein Unternehmensprofil verknüpft.</strong> Ihre Beiträge erscheinen erst in Ihrem Unternehmenskanal, wenn die Redaktion Ihr Profil zugeordnet hat.</p></div>';
        return;
    }
    $p = get_post($bid);
    echo '<p><strong>' . esc_html($p->post_title ?: 'Ihr Unternehmen') . '</strong> · Unternehmenskanal' . ($p->post_status === 'publish' ? '' : ' (Profil noch nicht veröffentlicht)') . '</p><div class="ma-pd__wege">';
    if (current_user_can('edit_post', $bid)) echo '<a class="button" href="' . esc_url(get_edit_post_link($bid, 'raw')) . '">Unternehmensprofil ausfüllen</a>';
    elseif ($p->post_status === 'publish' && function_exists('ma_aenderung_erlaubt') && ma_aenderung_erlaubt($p, $u)) echo '<a class="button" href="' . esc_url(ma_aenderung_link($bid)) . '">Unternehmensprofil bearbeiten</a>';
    if ($p->post_status === 'publish') echo ' <a class="button" href="' . esc_url(ma_business_kanal_url($p)) . '" target="_blank">Kanal ansehen</a>';
    echo '</div>';
}
