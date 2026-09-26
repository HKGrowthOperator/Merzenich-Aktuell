<?php
/**
 * Werbekontingent je Unternehmens-Zugang.
 *
 * Entscheidung KBS 26.09.2026: 5 bis 10 Unternehmen buchen ihre Anzeigen
 * selbst, immer mit Freigabe der Redaktion. Jeder Zugang hat ein Kontingent:
 * wie viele Anzeigen gleichzeitig laufen oder in Pruefung sein duerfen und auf
 * welchen Plaetzen. Voreinstellung: 1 Anzeige, alle Werbebaender der
 * Startseite. Die Redaktion stellt es am Benutzerprofil ein.
 *
 * Reicht ein Unternehmen eine Anzeige ueber das Kontingent hinaus oder auf
 * einem nicht freigeschalteten Platz ein, bleibt sie Entwurf und der Editor
 * nennt den Grund - dasselbe Muster wie bei der fehlenden Fotoerlaubnis.
 */
if (!defined('ABSPATH')) { exit; }

const MA_AD_QUOTA_MAX_META = 'ma_ad_quota_max';
const MA_AD_QUOTA_SLOTS_META = 'ma_ad_quota_slots';
const MA_AD_QUOTA_REASON_META = '_ma_ad_quota_reason';
const MA_AD_QUOTA_LIMIT = 20;

function ma_register_ad_quota_hooks(): void {
    // Nach ma_partner_force_pending (5): dort wird aus "publish" erst "pending".
    add_filter('wp_insert_post_data', 'ma_ad_quota_gate', 6, 2);
    add_action('admin_notices', 'ma_ad_quota_admin_notice');
    add_action('show_user_profile', 'ma_ad_quota_profile_fields');
    add_action('edit_user_profile', 'ma_ad_quota_profile_fields');
    add_action('personal_options_update', 'ma_ad_quota_profile_save');
    add_action('edit_user_profile_update', 'ma_ad_quota_profile_save');
}

function ma_ad_quota_defaults(): array {
    return ['max' => 1, 'slots' => ma_ad_band_slots()];
}

/** Kontingent eines Zugangs: gespeicherter Wert oder Voreinstellung. */
function ma_ad_quota_for_user(int $user_id): array {
    $d = ma_ad_quota_defaults();
    $max_raw = (string)get_user_meta($user_id, MA_AD_QUOTA_MAX_META, true);
    $max = $max_raw === '' ? $d['max'] : max(0, min(MA_AD_QUOTA_LIMIT, (int)$max_raw));

    // '' = nie eingestellt (Voreinstellung), '-' = bewusst kein Platz.
    $slots_raw = (string)get_user_meta($user_id, MA_AD_QUOTA_SLOTS_META, true);
    if ($slots_raw === '') $slots = $d['slots'];
    elseif ($slots_raw === '-') $slots = [];
    else $slots = array_values(array_intersect(ma_ad_slots(), array_map('trim', explode(',', $slots_raw))));

    return ['max' => $max, 'slots' => $slots];
}

/** Gilt fuer diesen Zugang ein Kontingent? (Unternehmens-Partner) */
function ma_ad_quota_applies(?WP_User $user = null): bool {
    $policy = function_exists('ma_current_partner_policy') ? ma_current_partner_policy($user) : null;
    return $policy && in_array('ma_ad', (array)($policy['post_types'] ?? []), true);
}

/**
 * Wie viele Anzeigen des Zugangs belegen gerade Kontingent? Laufende
 * (veroeffentlicht, aktiv, innerhalb der Laufzeit) und eingereichte
 * (ausstehend). Abgelaufene, pausierte und Entwuerfe zaehlen nicht.
 */
function ma_ad_quota_used(int $user_id, int $exclude_id = 0): int {
    $q = new WP_Query([
        'post_type' => 'ma_ad',
        'post_status' => ['publish','pending'],
        'author' => $user_id,
        'posts_per_page' => 100,
        'no_found_rows' => true,
    ]);
    $now = ma_ads_now();
    $used = 0;
    foreach ((array)$q->posts as $ad) {
        if (!$ad instanceof WP_Post || (int)$ad->ID === $exclude_id) continue;
        if ((int)$ad->post_author !== $user_id) continue;
        if ($ad->post_status === 'pending' || ma_ad_is_running($ad, $now)) $used++;
    }
    return $used;
}

/** Plaetze, die dieser Zugang waehlen darf (Metabox und Speichern). */
function ma_ad_allowed_slots_for_current_user(): array {
    if (!ma_ad_quota_applies()) return ma_ad_slots();
    return ma_ad_quota_for_user((int)get_current_user_id())['slots'];
}

/** Gewaehlter Platz: Formularwert vor gespeichertem Stand. */
function ma_ad_quota_requested_slot(int $post_id): string {
    if (isset($_POST['ma_content_admin_nonce']) && array_key_exists('ma_ad_slot', $_POST)) {
        return sanitize_text_field(wp_unslash((string)$_POST['ma_ad_slot']));
    }
    return $post_id > 0 ? (string)get_post_meta($post_id, 'ma_ad_slot', true) : '';
}

/**
 * Sperre: Anzeige eines Unternehmens geht nur zur Freigabe, wenn Platz und
 * Kontingent passen. Sonst Entwurf mit Grund.
 */
function ma_ad_quota_gate(array $data, array $postarr): array {
    if (($data['post_type'] ?? '') !== 'ma_ad') return $data;
    if (!in_array((string)($data['post_status'] ?? ''), ['pending','publish','future'], true)) return $data;
    $user = function_exists('wp_get_current_user') ? wp_get_current_user() : null;
    if (!$user || !ma_ad_quota_applies($user)) return $data;

    $user_id = (int)$user->ID;
    $post_id = (int)($postarr['ID'] ?? 0);
    $quota = ma_ad_quota_for_user($user_id);
    $slot = ma_ad_quota_requested_slot($post_id);

    $reason = '';
    if ($slot === '' || !in_array($slot, $quota['slots'], true)) {
        $reason = 'slot:'.$slot;
    } else {
        $used = ma_ad_quota_used($user_id, $post_id);
        if ($used >= $quota['max']) $reason = 'quota:'.$used.'/'.$quota['max'];
    }

    if ($reason !== '') {
        $data['post_status'] = 'draft';
        if ($post_id) update_post_meta($post_id, MA_AD_QUOTA_REASON_META, $reason);
    } elseif ($post_id) {
        delete_post_meta($post_id, MA_AD_QUOTA_REASON_META);
    }
    return $data;
}

/** Klartext zum gespeicherten Grund. */
function ma_ad_quota_reason_text(string $reason, int $user_id): string {
    $quota = ma_ad_quota_for_user($user_id);
    if (str_starts_with($reason, 'slot:')) {
        $slot = substr($reason, 5);
        $erlaubt = $quota['slots'] ? implode(', ', array_map('ma_ad_slot_label', $quota['slots'])) : 'keine';
        return ($slot === '' ? 'Es ist keine Platzierung gewählt.' : 'Die Platzierung „'.ma_ad_slot_label($slot).'“ ist für Ihren Zugang nicht freigeschaltet.')
            .' Freigeschaltet: '.$erlaubt.'.';
    }
    if (str_starts_with($reason, 'quota:')) {
        return 'Ihr Kontingent erlaubt '.$quota['max'].' gleichzeitig laufende oder eingereichte Anzeige'.($quota['max'] === 1 ? '' : 'n')
            .'. Dieses Kontingent ist ausgeschöpft. Sobald eine Anzeige endet, können Sie diese einreichen. Für mehr Plätze wenden Sie sich an die Redaktion (Preis auf Anfrage).';
    }
    return '';
}

function ma_ad_quota_admin_notice(): void {
    $post = $GLOBALS['post'] ?? null;
    if (!$post instanceof WP_Post || $post->post_type !== 'ma_ad') return;
    $reason = (string)get_post_meta($post->ID, MA_AD_QUOTA_REASON_META, true);
    if ($reason === '') return;
    $text = ma_ad_quota_reason_text($reason, (int)$post->post_author);
    if ($text === '') return;
    echo '<div class="notice notice-warning"><p><strong>Noch nicht zur Freigabe eingereicht.</strong> '.esc_html($text).'</p>';
    echo '<p>Die Anzeige ist als Entwurf gespeichert; Ihre Eingaben bleiben erhalten.</p></div>';
}

/** Felder am Benutzerprofil - sichtbar und speicherbar nur fuer Administratoren. */
function ma_ad_quota_profile_fields($user): void {
    if (!$user instanceof WP_User || !current_user_can('manage_options')) return;
    if (!ma_ad_quota_applies($user)) return;
    $quota = ma_ad_quota_for_user((int)$user->ID);

    echo '<h2>Werbekontingent (Merzenich Aktuell)</h2>';
    wp_nonce_field('ma_ad_quota_save_'.(int)$user->ID, 'ma_ad_quota_nonce');
    echo '<table class="form-table" role="presentation"><tr><th><label for="ma_ad_quota_max">Gleichzeitig aktive Anzeigen</label></th><td>';
    echo '<input type="number" min="0" max="'.esc_attr((string)MA_AD_QUOTA_LIMIT).'" id="ma_ad_quota_max" name="ma_ad_quota_max" value="'.esc_attr((string)$quota['max']).'" class="small-text">';
    echo '<p class="description">Laufende und eingereichte Anzeigen zählen zusammen. Voreinstellung: 1. Jede Anzeige braucht weiterhin die Freigabe der Redaktion.</p></td></tr>';
    echo '<tr><th>Erlaubte Plätze</th><td><fieldset>';
    foreach (ma_ad_slots() as $slot) {
        echo '<label style="display:block;margin:2px 0"><input type="checkbox" name="ma_ad_quota_slots[]" value="'.esc_attr($slot).'" '.checked(in_array($slot, $quota['slots'], true), true, false).'> '.esc_html(ma_ad_slot_label($slot)).'</label>';
    }
    echo '<p class="description">Voreinstellung: alle Werbebänder der Startseite.</p></fieldset></td></tr></table>';
}

function ma_ad_quota_profile_save(int $user_id): void {
    if (!current_user_can('manage_options') || !current_user_can('edit_user', $user_id)) return;
    if (!isset($_POST['ma_ad_quota_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_ad_quota_nonce'])), 'ma_ad_quota_save_'.$user_id)) return;

    $max = max(0, min(MA_AD_QUOTA_LIMIT, (int)($_POST['ma_ad_quota_max'] ?? 1)));
    update_user_meta($user_id, MA_AD_QUOTA_MAX_META, (string)$max);

    $raw = isset($_POST['ma_ad_quota_slots']) && is_array($_POST['ma_ad_quota_slots']) ? wp_unslash($_POST['ma_ad_quota_slots']) : [];
    $slots = array_values(array_intersect(ma_ad_slots(), array_map('sanitize_key', $raw)));
    update_user_meta($user_id, MA_AD_QUOTA_SLOTS_META, $slots ? implode(',', $slots) : '-');
}
