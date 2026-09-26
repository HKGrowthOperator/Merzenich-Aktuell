<?php
/**
 * Unternehmen (ma_business): Stammdaten, Einwilligung, Veroeffentlichung.
 *
 * Nach dem Vorbild "Unternehmen" auf Oberberg Aktuell stellen sich 5 bis 10
 * Betriebe aus der Gemeinde mit einem schlichten Profil vor. Ein Profil wird
 * nur mit Einwilligung des Unternehmens veroeffentlicht; ohne Haken bleibt es
 * in Pruefung bzw. Entwurf, und der Editor nennt den Grund.
 *
 * Registriert wird der Inhaltstyp in content.php; hier kommen Felder,
 * Speichern, Sperre und Archiv-Reihenfolge dazu. Preise werden nie
 * ausgegeben (Vorgabe: "Preis auf Anfrage").
 */
if (!defined('ABSPATH')) { exit; }

const MA_BUSINESS_CONSENT_META = 'ma_business_consent';
const MA_BUSINESS_CONSENT_MISSING_META = '_ma_business_consent_missing';
const MA_BUSINESS_ARCHIVE_SLOTS = 8;

function ma_business_ortsteile(): array {
    return [
        'merzenich' => 'Merzenich',
        'golzheim' => 'Golzheim',
        'girbelsrath' => 'Girbelsrath',
        'morschenich' => 'Morschenich',
        'buergewald' => 'Bürgewald',
    ];
}

/** Feldschema: Schluessel => [Beschriftung, Typ]. */
function ma_business_fields(): array {
    return [
        'ma_business_branche' => ['Branche', 'text'],
        'ma_business_adresse' => ['Adresse', 'text'],
        'ma_business_telefon' => ['Telefon', 'tel'],
        'ma_business_website' => ['Website', 'url'],
        'ma_business_oeffnungszeiten' => ['Öffnungszeiten', 'textarea'],
        'ma_business_ortsteil' => ['Ortsteil', 'ortsteil'],
        'ma_business_quelle' => ['Quelle', 'text'],
        'ma_business_stand' => ['Stand', 'date'],
    ];
}

function ma_register_business_hooks(): void {
    add_action('add_meta_boxes', 'ma_business_meta_box');
    add_action('save_post_ma_business', 'ma_business_save', 10, 2);
    add_filter('wp_insert_post_data', 'ma_business_publication_gate', 22, 2);
    add_action('admin_notices', 'ma_business_admin_notice');
    add_action('pre_get_posts', 'ma_business_archive_query');
}

function ma_business_meta_box(): void {
    add_meta_box('ma-business-details', 'Unternehmensprofil', 'ma_business_render_meta_box', 'ma_business', 'normal', 'high');
}

function ma_business_sanitize(string $type, $value): string {
    $value = is_string($value) ? wp_unslash($value) : '';
    switch ($type) {
        case 'url': return esc_url_raw(trim((string)$value));
        case 'tel': return trim(preg_replace('/[^0-9+\/\-() ]/', '', (string)$value));
        case 'textarea': return sanitize_textarea_field((string)$value);
        case 'date': return preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$value) ? (string)$value : '';
        case 'ortsteil':
            $key = sanitize_key((string)$value);
            return isset(ma_business_ortsteile()[$key]) ? $key : '';
        default: return sanitize_text_field((string)$value);
    }
}

function ma_business_render_meta_box(WP_Post $post): void {
    wp_nonce_field('ma_business_save', 'ma_business_nonce');
    echo '<table class="form-table" role="presentation">';
    foreach (ma_business_fields() as $key => [$label, $type]) {
        $value = (string)get_post_meta($post->ID, $key, true);
        echo '<tr><th scope="row"><label for="'.esc_attr($key).'">'.esc_html($label).'</label></th><td>';
        if ($type === 'textarea') {
            echo '<textarea class="large-text" rows="4" id="'.esc_attr($key).'" name="'.esc_attr($key).'">'.esc_textarea($value).'</textarea>';
            echo '<p class="description">Zum Beispiel je Zeile ein Tag oder Zeitraum.</p>';
        } elseif ($type === 'ortsteil') {
            echo '<select id="'.esc_attr($key).'" name="'.esc_attr($key).'"><option value="">Bitte wählen</option>';
            foreach (ma_business_ortsteile() as $slug => $name) echo '<option value="'.esc_attr($slug).'" '.selected($value, $slug, false).'>'.esc_html($name).'</option>';
            echo '</select>';
        } else {
            $input = ['tel' => 'tel', 'url' => 'url', 'date' => 'date'][$type] ?? 'text';
            echo '<input class="regular-text" type="'.esc_attr($input).'" id="'.esc_attr($key).'" name="'.esc_attr($key).'" value="'.esc_attr($value).'">';
            if ($key === 'ma_business_quelle') echo '<p class="description">Woher stammen die Angaben? Zum Beispiel „Angaben des Unternehmens“ oder die Website.</p>';
            if ($key === 'ma_business_stand') echo '<p class="description">Wann wurden die Angaben zuletzt bestätigt?</p>';
        }
        echo '</td></tr>';
    }
    $consent = (string)get_post_meta($post->ID, MA_BUSINESS_CONSENT_META, true) === '1';
    echo '<tr><th scope="row">Einwilligung</th><td><label><input type="checkbox" name="'.esc_attr(MA_BUSINESS_CONSENT_META).'" value="1" '.checked($consent, true, false).'> ';
    echo 'Das Unternehmen ist mit der Veröffentlichung dieser Angaben auf Merzenich Aktuell einverstanden.</label>';
    if ($consent) {
        $wer = (string)get_post_meta($post->ID, MA_BUSINESS_CONSENT_META.'_by', true);
        $wann = (string)get_post_meta($post->ID, MA_BUSINESS_CONSENT_META.'_at', true);
        if ($wer !== '' || $wann !== '') echo '<p class="description">Erteilt'.($wer !== '' ? ' von '.esc_html($wer) : '').($wann !== '' ? ' am '.esc_html($wann) : '').'.</p>';
    }
    echo '<p class="description"><strong>Pflicht:</strong> Ohne Einwilligung wird das Profil nicht veröffentlicht.</p></td></tr>';
    echo '</table>';
}

/** Liegt die Einwilligung vor? Formular vor gespeichertem Stand. */
function ma_business_consent_given(int $post_id): bool {
    if (isset($_POST['ma_business_nonce'])) return isset($_POST[MA_BUSINESS_CONSENT_META]);
    return $post_id > 0 && (string)get_post_meta($post_id, MA_BUSINESS_CONSENT_META, true) === '1';
}

function ma_business_save(int $post_id, WP_Post $post): void {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if (!isset($_POST['ma_business_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_business_nonce'])), 'ma_business_save')) return;
    if (!current_user_can('edit_post', $post_id)) return;

    foreach (ma_business_fields() as $key => [$label, $type]) {
        $value = ma_business_sanitize($type, $_POST[$key] ?? '');
        if ($value === '') delete_post_meta($post_id, $key);
        else update_post_meta($post_id, $key, $value);
    }

    if (isset($_POST[MA_BUSINESS_CONSENT_META])) {
        if ((string)get_post_meta($post_id, MA_BUSINESS_CONSENT_META, true) !== '1') {
            $user = wp_get_current_user();
            update_post_meta($post_id, MA_BUSINESS_CONSENT_META, '1');
            update_post_meta($post_id, MA_BUSINESS_CONSENT_META.'_by', (string)($user->display_name ?? '') ?: (string)($user->user_login ?? ''));
            update_post_meta($post_id, MA_BUSINESS_CONSENT_META.'_at', current_time('d.m.Y H:i'));
        }
    } else {
        delete_post_meta($post_id, MA_BUSINESS_CONSENT_META);
        delete_post_meta($post_id, MA_BUSINESS_CONSENT_META.'_by');
        delete_post_meta($post_id, MA_BUSINESS_CONSENT_META.'_at');
    }
}

/**
 * Sperre: ohne Einwilligung keine Veroeffentlichung und keine Einreichung.
 * Wollte die Redaktion ein eingereichtes Profil veroeffentlichen, bleibt es
 * in Pruefung (ausstehend); sonst wird es Entwurf. Der Grund steht im Editor.
 */
function ma_business_publication_gate(array $data, array $postarr): array {
    if (($data['post_type'] ?? '') !== 'ma_business') return $data;
    $status = (string)($data['post_status'] ?? '');
    if (!in_array($status, ['publish','future','pending'], true)) return $data;

    $post_id = (int)($postarr['ID'] ?? 0);
    if (ma_business_consent_given($post_id)) {
        if ($post_id) delete_post_meta($post_id, MA_BUSINESS_CONSENT_MISSING_META);
        return $data;
    }

    $bisher = $post_id && function_exists('get_post_status') ? (string)get_post_status($post_id) : '';
    $data['post_status'] = ($status !== 'pending' && $bisher === 'pending') ? 'pending' : 'draft';
    if ($post_id) update_post_meta($post_id, MA_BUSINESS_CONSENT_MISSING_META, '1');
    return $data;
}

function ma_business_admin_notice(): void {
    $post = $GLOBALS['post'] ?? null;
    if (!$post instanceof WP_Post || $post->post_type !== 'ma_business') return;
    if ((string)get_post_meta($post->ID, MA_BUSINESS_CONSENT_MISSING_META, true) !== '1') return;
    echo '<div class="notice notice-warning"><p><strong>Nicht veröffentlicht.</strong> ';
    echo 'Für dieses Unternehmensprofil fehlt die Einwilligung zur Veröffentlichung. Setzen Sie im Kasten „Unternehmensprofil“ den Haken „Einwilligung“, sobald sie vorliegt, und speichern Sie erneut. Ihre Eingaben sind gespeichert.</p></div>';
}

/** Archiv: alphabetisch, alle auf einer Seite (es sind wenige Betriebe). */
function ma_business_archive_query(WP_Query $query): void {
    if (is_admin() || !$query->is_main_query() || !$query->is_post_type_archive('ma_business')) return;
    $query->set('orderby', 'title');
    $query->set('order', 'ASC');
    $query->set('posts_per_page', 48);
}

/** Angaben fuer die Vorlagen, fertig zum Escapen. */
function ma_business_profile(int $post_id): array {
    $ortsteil = (string)get_post_meta($post_id, 'ma_business_ortsteil', true);
    $stand = (string)get_post_meta($post_id, 'ma_business_stand', true);
    $stand_text = '';
    if ($stand !== '' && ($ts = strtotime($stand))) $stand_text = function_exists('wp_date') ? wp_date('d.m.Y', $ts) : date('d.m.Y', $ts);
    $telefon = (string)get_post_meta($post_id, 'ma_business_telefon', true);
    return [
        'branche' => (string)get_post_meta($post_id, 'ma_business_branche', true),
        'adresse' => (string)get_post_meta($post_id, 'ma_business_adresse', true),
        'telefon' => $telefon,
        'telefon_href' => $telefon !== '' ? 'tel:'.preg_replace('/[^0-9+]/', '', $telefon) : '',
        'website' => (string)get_post_meta($post_id, 'ma_business_website', true),
        'oeffnungszeiten' => (string)get_post_meta($post_id, 'ma_business_oeffnungszeiten', true),
        'ortsteil' => ma_business_ortsteile()[$ortsteil] ?? '',
        'quelle' => (string)get_post_meta($post_id, 'ma_business_quelle', true),
        'stand' => $stand_text,
    ];
}
