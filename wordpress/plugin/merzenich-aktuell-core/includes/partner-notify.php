<?php
/**
 * Rueckmeldung an Partner: freigegeben oder abgelehnt.
 *
 * Partner (Polizei, Feuerwehr, Rathaus, Sport, Vereine, Unternehmen,
 * Immobilien) schreiben selbst, die Redaktion gibt nur frei. Bisher erfuhr der
 * Partner davon nichts. Jetzt:
 *  - Veroeffentlicht die Redaktion eine Einreichung, erhaelt der Partner eine
 *    E-Mail mit Link.
 *  - Lehnt sie ab (Kasten "Ablehnung" im Editor oder Papierkorb), erhaelt er
 *    eine E-Mail mit der eingetragenen Begruendung.
 */
if (!defined('ABSPATH')) { exit; }

const MA_REJECTED_STATUS = 'ma_abgelehnt';
const MA_REJECT_REASON_META = '_ma_reject_reason';

function ma_partner_notify_post_types(): array {
    return ['post','ma_property','ma_business','ma_tip','ma_ad'];
}

function ma_register_partner_notify_hooks(): void {
    add_action('init', 'ma_register_rejected_status');
    add_action('add_meta_boxes', 'ma_partner_reject_meta_box');
    add_filter('wp_insert_post_data', 'ma_partner_reject_apply', 40, 2);
    add_action('transition_post_status', 'ma_partner_decision_mail', 30, 3);
    add_filter('display_post_states', 'ma_partner_rejected_state', 10, 2);
}

function ma_register_rejected_status(): void {
    register_post_status(MA_REJECTED_STATUS, [
        'label' => 'Abgelehnt',
        'public' => false,
        'internal' => false,
        'protected' => true,
        'exclude_from_search' => true,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Abgelehnt <span class="count">(%s)</span>', 'Abgelehnt <span class="count">(%s)</span>'),
    ]);
}

function ma_partner_rejected_state(array $states, WP_Post $post): array {
    if ($post->post_status === MA_REJECTED_STATUS) $states[MA_REJECTED_STATUS] = 'Abgelehnt';
    return $states;
}

/** Stammt der Beitrag aus einem Partner-Zugang? */
function ma_is_partner_submission(WP_Post $post): bool {
    if ((string)get_post_meta($post->ID, '_ma_partner_submission', true) === '1') return true;
    $author = get_userdata((int)$post->post_author);
    return $author && function_exists('ma_current_partner_policy') && ma_current_partner_policy($author) !== null;
}

/** Kasten "Ablehnung" - nur fuer die Redaktion und nur an Partner-Einreichungen. */
function ma_partner_reject_meta_box(): void {
    $post = $GLOBALS['post'] ?? null;
    if (!$post instanceof WP_Post || !ma_is_partner_submission($post)) return;
    if (!current_user_can('edit_others_posts')) return;
    add_meta_box('ma-partner-reject', 'Partner-Einreichung ablehnen', 'ma_partner_reject_box', ma_partner_notify_post_types(), 'side', 'high');
}

function ma_partner_reject_box(WP_Post $post): void {
    wp_nonce_field('ma_partner_reject', 'ma_partner_reject_nonce');
    $reason = (string)get_post_meta($post->ID, MA_REJECT_REASON_META, true);
    if ($post->post_status === MA_REJECTED_STATUS) {
        echo '<p><strong>Abgelehnt.</strong> Der Partner wurde benachrichtigt. Reicht er den Beitrag überarbeitet erneut ein, steht er wieder unter „Ausstehend“.</p>';
    }
    echo '<p><label for="ma_reject_reason"><strong>Begründung</strong> (geht per E-Mail an den Partner)</label>';
    echo '<textarea id="ma_reject_reason" name="ma_reject_reason" rows="4" style="width:100%">'.esc_textarea($reason).'</textarea></p>';
    echo '<p><label><input type="checkbox" name="ma_reject" value="1"> Beim Speichern ablehnen und Partner benachrichtigen</label></p>';
    echo '<p class="description">Alternativ in den Papierkorb legen: Auch dann geht eine E-Mail mit der hier eingetragenen Begründung an den Partner.</p>';
}

/** Ablehnung aus dem Editor uebernehmen: Status und Begruendung setzen. */
function ma_partner_reject_apply(array $data, array $postarr): array {
    if (!in_array((string)($data['post_type'] ?? ''), ma_partner_notify_post_types(), true)) return $data;
    if (!isset($_POST['ma_partner_reject_nonce'])) return $data;
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_partner_reject_nonce'])), 'ma_partner_reject')) return $data;
    $post_id = (int)($postarr['ID'] ?? 0);
    if (!$post_id || !current_user_can('edit_others_posts') || !current_user_can('edit_post', $post_id)) return $data;

    $reason = sanitize_textarea_field(wp_unslash((string)($_POST['ma_reject_reason'] ?? '')));
    if ($reason === '') delete_post_meta($post_id, MA_REJECT_REASON_META);
    else update_post_meta($post_id, MA_REJECT_REASON_META, $reason);

    if (!empty($_POST['ma_reject'])) $data['post_status'] = MA_REJECTED_STATUS;
    return $data;
}

function ma_partner_mail_greeting(WP_User $author): string {
    $name = trim((string)($author->display_name ?: $author->user_login));
    return $name !== '' ? 'Guten Tag '.$name.",\n\n" : "Guten Tag,\n\n";
}

function ma_partner_mail_headers(): array {
    $redaktion = sanitize_email((string)get_option('ma_editorial_email', get_option('admin_email')));
    return ($redaktion && is_email($redaktion)) ? ['Reply-To: Redaktion Merzenich Aktuell <'.$redaktion.'>'] : [];
}

/**
 * E-Mail an den Partner bei Freigabe (ausstehend -> veroeffentlicht bzw.
 * geplant) und bei Ablehnung (ausstehend -> abgelehnt oder Papierkorb).
 * Handelt der Partner selbst, geht keine Mail.
 */
function ma_partner_decision_mail(string $new_status, string $old_status, WP_Post $post): void {
    if ($new_status === $old_status || $old_status !== 'pending') return;
    if (!in_array($post->post_type, ma_partner_notify_post_types(), true)) return;
    $freigabe = in_array($new_status, ['publish','future'], true);
    $ablehnung = in_array($new_status, [MA_REJECTED_STATUS, 'trash'], true);
    if (!$freigabe && !$ablehnung) return;
    if ((int)$post->post_author === (int)get_current_user_id()) return;
    if (!ma_is_partner_submission($post)) return;

    $author = get_userdata((int)$post->post_author);
    if (!$author) return;
    $to = sanitize_email((string)$author->user_email);
    if (!$to || !is_email($to)) return;

    $titel = trim((string)$post->post_title) !== '' ? (string)$post->post_title : '(ohne Titel)';
    $body = ma_partner_mail_greeting($author);
    if ($freigabe) {
        $subject = '[Merzenich Aktuell] Ihr Beitrag ist freigegeben';
        $body .= 'die Redaktion hat Ihren Beitrag „'.$titel.'“ freigegeben.';
        if ($new_status === 'future') {
            $body .= ' Er erscheint zum geplanten Zeitpunkt ('.mysql2date('d.m.Y H:i', (string)$post->post_date).' Uhr).';
        } else {
            $body .= ' Er ist jetzt veröffentlicht.';
        }
        $link = $post->post_type === 'ma_ad' ? '' : (string)get_permalink($post);
        if ($link !== '') $body .= "\n\n".$link;
    } else {
        $subject = '[Merzenich Aktuell] Ihr Beitrag wurde nicht freigegeben';
        $reason = trim((string)get_post_meta($post->ID, MA_REJECT_REASON_META, true));
        $body .= 'die Redaktion hat Ihren Beitrag „'.$titel.'“ nicht freigegeben.'."\n\n";
        $body .= $reason !== ''
            ? "Begründung der Redaktion:\n".$reason
            : 'Die Redaktion hat keine Begründung eingetragen. Bei Fragen antworten Sie bitte auf diese E-Mail.';
        if ($new_status === MA_REJECTED_STATUS) {
            $body .= "\n\nSie können den Beitrag in Ihrem Zugang überarbeiten und erneut zur Freigabe einreichen:\n".admin_url('post.php?post='.$post->ID.'&action=edit');
        }
    }
    $body .= "\n\nRedaktion Merzenich Aktuell";

    if (!wp_mail($to, $subject, $body, ma_partner_mail_headers())) {
        $GLOBALS['ma_partner_mail_fehler'] = (int)($GLOBALS['ma_partner_mail_fehler'] ?? 0) + 1;
    }
}
