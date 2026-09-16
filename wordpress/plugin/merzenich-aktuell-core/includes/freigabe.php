<?php
/**
 * Freigabe sichtbar machen - und Eingaben nicht mehr verlieren.
 *
 * Die beiden Veroeffentlichungs-Sperren (ma_publication_gate,
 * ma_market_publication_gate) setzen einen Beitrag auf Entwurf zurueck, wenn
 * eine Pflichtfreigabe fehlt. Das ist richtig. Falsch war, dass sie es
 * stillschweigend taten: die Redaktion fuellte alles aus, drueckte
 * "Veroeffentlichen", bekam "gespeichert" zu sehen - und die Seite blieb leer.
 * Ohne Meldung gibt es keinen Weg, das zu verstehen; man gibt es einfach wieder
 * und wieder ein.
 *
 * Drei Dinge passieren hier:
 *  1. Die Sperre hinterlaesst ihren Grund, und der Editor zeigt ihn an.
 *  2. Eine Freigabeliste im Editor zeigt jederzeit, was noch fehlt.
 *  3. Die Felder speichern beim Tippen, nicht erst beim Absenden.
 */
if (!defined('ABSPATH')) { exit; }

const MA_GATE_NOTICE_META = '_ma_gate_reason';

/** Pflichtfreigaben je Inhaltsart, mit der Beschriftung aus der Metabox. */
function ma_gate_requirements(string $post_type): array {
    if ($post_type === 'post') {
        return [
            'ma_source_verified' => 'Quelle geprüft',
            'ma_date_verified'   => 'Datum geprüft',
            'ma_place_verified'  => 'Ort geprüft',
            'ma_human_reviewed'  => 'Human Review abgeschlossen',
        ];
    }
    if (in_array($post_type, ['ma_property','ma_job','ma_obituary','ma_family_notice'], true)) {
        return ['ma_release_confirmed' => 'Veröffentlichung freigegeben'];
    }
    return [];
}

/** Was fehlt gerade? Beruecksichtigt das laufende Formular UND den Bestand. */
function ma_gate_missing(int $post_id, string $post_type): array {
    $fehlt = [];
    foreach (ma_gate_requirements($post_type) as $key => $label) {
        $submitted = isset($_POST[$key]);
        $stored    = get_post_meta($post_id, $key, true) === '1';
        if (!$submitted && !$stored) $fehlt[$key] = $label;
    }
    if ($post_type === 'post' && has_post_thumbnail($post_id)) {
        $submitted = isset($_POST['ma_image_rights_verified']);
        $stored    = get_post_meta($post_id, 'ma_image_rights_verified', true) === '1';
        if (!$submitted && !$stored) $fehlt['ma_image_rights_verified'] = 'Bildrechte geprüft';
    }
    return $fehlt;
}

/**
 * Grund festhalten, sobald eine Sperre greift. Laeuft nach beiden Gates, weil
 * es an derselben Stelle haengt und dieselbe Bedingung prueft.
 */
function ma_gate_record_reason(array $data, array $postarr): array {
    $post_id = (int)($postarr['ID'] ?? 0);
    if (!$post_id) return $data;

    $angefragt = ($postarr['post_status'] ?? '') === 'publish';
    if (!$angefragt) return $data;

    if (($data['post_status'] ?? '') === 'draft') {
        $fehlt = ma_gate_missing($post_id, (string)($data['post_type'] ?? ''));
        if ($fehlt) update_post_meta($post_id, MA_GATE_NOTICE_META, implode('|', $fehlt));
    } else {
        delete_post_meta($post_id, MA_GATE_NOTICE_META);
    }
    return $data;
}
// Prioritaet 30: nach ma_publication_gate (20) und ma_market_publication_gate.
add_filter('wp_insert_post_data', 'ma_gate_record_reason', 30, 2);

/** Die Meldung, die bisher fehlte. */
function ma_gate_admin_notice(): void {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->base !== 'post') return;

    $post_id = (int)(($GLOBALS['post']->ID ?? 0));
    if (!$post_id) return;

    $grund = (string)get_post_meta($post_id, MA_GATE_NOTICE_META, true);
    if ($grund === '') return;

    $fehlt = array_filter(explode('|', $grund));
    echo '<div class="notice notice-warning"><p><strong>Nicht veröffentlicht.</strong> ';
    echo 'Der Beitrag steht auf Entwurf, weil diese Freigabe fehlt: <strong>';
    echo esc_html(implode(', ', $fehlt));
    echo '</strong>.</p><p>Ihre Eingaben sind gespeichert — es fehlt nur der Haken. ';
    echo 'Setzen Sie ihn und drücken Sie noch einmal auf „Veröffentlichen“.</p></div>';
}
add_action('admin_notices', 'ma_gate_admin_notice');

/** Freigabeliste: zeigt jederzeit, was erledigt ist und was fehlt. */
function ma_gate_status_box(): void {
    $post_id = (int)get_the_ID();
    $typ     = (string)get_post_type($post_id);
    $pflicht = ma_gate_requirements($typ);
    if (!$pflicht) return;

    if ($typ === 'post' && has_post_thumbnail($post_id)) {
        $pflicht['ma_image_rights_verified'] = 'Bildrechte geprüft';
    }

    $offen = 0;
    echo '<ul class="ma-gate-list">';
    foreach ($pflicht as $key => $label) {
        $ok = get_post_meta($post_id, $key, true) === '1';
        if (!$ok) $offen++;
        printf(
            '<li class="%s"><span aria-hidden="true">%s</span> %s</li>',
            $ok ? 'ma-gate-ok' : 'ma-gate-offen',
            $ok ? '✓' : '○',
            esc_html($label)
        );
    }
    echo '</ul>';

    echo $offen === 0
        ? '<p class="ma-gate-frei"><strong>Freigegeben.</strong> Dieser Beitrag darf veröffentlicht werden.</p>'
        : '<p class="ma-gate-blockiert"><strong>' . (int)$offen . ' Freigabe' . ($offen === 1 ? '' : 'n') . ' offen.</strong> Solange sie fehlen, bleibt der Beitrag Entwurf.</p>';

    echo '<p class="description">Eingaben in den Redaktionsfeldern werden beim Tippen gespeichert. Die Freigabe-Haken gelten erst nach „Speichern“ oder „Veröffentlichen“.</p>';
}

function ma_gate_boxes(): void {
    foreach (array_merge(['post'], ['ma_property','ma_job','ma_obituary','ma_family_notice']) as $typ) {
        add_meta_box('ma_gate_status', 'Freigabe', 'ma_gate_status_box', $typ, 'side', 'high');
    }
}
add_action('add_meta_boxes', 'ma_gate_boxes');

function ma_gate_admin_assets(string $hook): void {
    if (!in_array($hook, ['post.php','post-new.php'], true)) return;

    wp_enqueue_script(
        'ma-autosave-meta',
        MA_CORE_URL . 'assets/autosave-meta.js',
        [],
        MA_CORE_VERSION,
        true
    );
    wp_localize_script('ma-autosave-meta', 'maAutosave', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('ma_autosave_meta'),
        'postId'  => (int)get_the_ID(),
    ]);

    $css = '.ma-gate-list{margin:0 0 10px;padding:0;list-style:none}'
         . '.ma-gate-list li{padding:3px 0;font-size:13px}'
         . '.ma-gate-ok{color:#1a7f37}.ma-gate-offen{color:#8a6d00}'
         . '.ma-gate-frei{margin:0;color:#1a7f37}.ma-gate-blockiert{margin:0;color:#8a6d00}'
         . '.ma-feld-gespeichert{outline:2px solid #1a7f37;outline-offset:1px;transition:outline-color .8s}';
    wp_add_inline_style('wp-admin', $css);
}
add_action('admin_enqueue_scripts', 'ma_gate_admin_assets');

/**
 * Feld einzeln speichern. Bewusst eng: nur bekannte Meta-Schluessel, nur mit
 * Nonce, nur mit Schreibrecht am konkreten Beitrag.
 */
function ma_autosave_meta_handler(): void {
    check_ajax_referer('ma_autosave_meta', 'nonce');

    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $key     = isset($_POST['key']) ? sanitize_key(wp_unslash($_POST['key'])) : '';
    $wert    = isset($_POST['value']) ? wp_unslash($_POST['value']) : '';

    if (!$post_id || !current_user_can('edit_post', $post_id)) wp_send_json_error('keine Berechtigung', 403);
    if (!in_array($key, ma_autosave_allowed_keys(), true))     wp_send_json_error('unbekanntes Feld', 400);

    if (strpos($key, '_url') !== false || $key === 'ma_source_url') {
        $wert = esc_url_raw((string)$wert);
    } elseif ($key === 'ma_editorial_priority') {
        $wert = (string)max(0, min(100, (int)$wert));
    } elseif ($key === 'ma_image_type') {
        $wert = isset(MA_IMAGE_TYPES[$wert]) ? (string)$wert : '';
    } else {
        $wert = sanitize_text_field((string)$wert);
    }

    $wert === '' ? delete_post_meta($post_id, $key) : update_post_meta($post_id, $key, $wert);
    wp_send_json_success(['key' => $key, 'saved_at' => current_time('H:i:s')]);
}
add_action('wp_ajax_ma_autosave_meta', 'ma_autosave_meta_handler');

/** Nur diese Felder duerfen per Zwischenspeicherung geschrieben werden. */
function ma_autosave_allowed_keys(): array {
    return [
        'ma_source_url','ma_source_publisher','ma_source_published_at','ma_source_checked_at',
        'ma_editorial_priority','ma_top_until',
        'ma_image_type','ma_image_credit','ma_image_license','ma_image_original_url',
        'ma_ai_use_type','ma_reviewed_by','ma_reviewed_at','ma_editorial_responsibility',
        'ma_property_price','ma_property_area','ma_property_rooms','ma_property_lot',
        'ma_property_address','ma_property_url','ma_property_provider','ma_property_contact','ma_property_mode',
        'ma_job_company','ma_job_location','ma_job_type','ma_job_hours','ma_job_apply_url','ma_job_contact',
        'ma_event_start','ma_event_end','ma_event_place','ma_event_organizer','ma_event_price',
        'ma_event_registration','ma_event_source_url',
        'ma_end_at','ma_source_url',
    ];
}
