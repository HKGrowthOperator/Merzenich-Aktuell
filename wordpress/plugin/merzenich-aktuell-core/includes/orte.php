<?php
/**
 * Orte: Adresse, Kurzinfo, Originalquelle und Bild am bestehenden Begriff.
 *
 * Die Ortsteile sind Begriffe der Taxonomie ma_location - Beitraege, Termine,
 * Immobilien und Stellen haengen bereits daran. Was fehlte, waren Felder am
 * Begriff selbst. Die kommen hier als Term-Meta hinzu; eine zweite
 * Ortsdatenbank daneben gibt es nicht.
 */
if (!defined('ABSPATH')) { exit; }

const MA_ORT_FELDER = [
    'ma_ort_address'      => 'Adresse / Anlaufstelle',
    'ma_ort_summary'      => 'Kurzinfo',
    'ma_ort_source_url'   => 'Originalquelle (URL)',
    'ma_ort_image_id'     => 'Bild (Mediathek-ID)',
    'ma_ort_image_credit' => 'Bildcredit',
];

function ma_ort_add_fields(): void {
    wp_nonce_field('ma_ort_save', 'ma_ort_nonce');
    foreach (MA_ORT_FELDER as $key => $label) {
        $typ = $key === 'ma_ort_source_url' ? 'url' : ($key === 'ma_ort_image_id' ? 'number' : 'text');
        echo '<div class="form-field"><label for="'.esc_attr($key).'">'.esc_html($label).'</label>';
        echo $key === 'ma_ort_summary'
            ? '<textarea id="'.esc_attr($key).'" name="'.esc_attr($key).'" rows="3"></textarea>'
            : '<input type="'.esc_attr($typ).'" id="'.esc_attr($key).'" name="'.esc_attr($key).'" value="">';
        echo '</div>';
    }
}

function ma_ort_edit_fields(WP_Term $term): void {
    wp_nonce_field('ma_ort_save', 'ma_ort_nonce');
    foreach (MA_ORT_FELDER as $key => $label) {
        $wert = (string)get_term_meta($term->term_id, $key, true);
        $typ = $key === 'ma_ort_source_url' ? 'url' : ($key === 'ma_ort_image_id' ? 'number' : 'text');
        echo '<tr class="form-field"><th scope="row"><label for="'.esc_attr($key).'">'.esc_html($label).'</label></th><td>';
        echo $key === 'ma_ort_summary'
            ? '<textarea id="'.esc_attr($key).'" name="'.esc_attr($key).'" rows="3">'.esc_textarea($wert).'</textarea>'
            : '<input type="'.esc_attr($typ).'" id="'.esc_attr($key).'" name="'.esc_attr($key).'" value="'.esc_attr($wert).'">';
        if ($key === 'ma_ort_image_id') echo '<p class="description">Ohne geprüfte Bildrechte bleibt es bei der Ersatzgrafik. Credit daneben eintragen.</p>';
        echo '</td></tr>';
    }
}

function ma_ort_save(int $term_id): void {
    if (!isset($_POST['ma_ort_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_ort_nonce'])), 'ma_ort_save')) return;
    if (!current_user_can('manage_categories')) return;
    foreach (MA_ORT_FELDER as $key => $label) {
        if (!isset($_POST[$key])) continue;
        $roh = wp_unslash($_POST[$key]);
        if ($key === 'ma_ort_source_url')      $wert = esc_url_raw((string)$roh);
        elseif ($key === 'ma_ort_image_id')    $wert = (string)max(0, (int)$roh);
        elseif ($key === 'ma_ort_summary')     $wert = sanitize_textarea_field((string)$roh);
        else                                   $wert = sanitize_text_field((string)$roh);
        $wert === '' || $wert === '0' ? delete_term_meta($term_id, $key) : update_term_meta($term_id, $key, $wert);
    }
}

add_action('ma_location_add_form_fields', 'ma_ort_add_fields');
add_action('ma_location_edit_form_fields', 'ma_ort_edit_fields');
add_action('created_ma_location', 'ma_ort_save');
add_action('edited_ma_location', 'ma_ort_save');

/**
 * Alles, was die Vorlage ueber einen Ort braucht. Bild nur mit Credit -
 * sonst die Ersatzgrafik des Ortes oder der Gemeinde.
 */
function ma_ort_info(WP_Term $term): array {
    $bild_id = (int)get_term_meta($term->term_id, 'ma_ort_image_id', true);
    $credit  = (string)get_term_meta($term->term_id, 'ma_ort_image_credit', true);
    $url     = $bild_id && $credit !== '' ? (string)wp_get_attachment_image_url($bild_id, 'large') : '';
    $alt     = $bild_id ? (string)get_post_meta($bild_id, '_wp_attachment_image_alt', true) : '';

    if ($url === '') {
        $basis = trailingslashit(get_template_directory());
        $eigen = 'assets/img/ph-ort-' . $term->slug . '.svg';
        $url   = trailingslashit(get_template_directory_uri()) . (file_exists($basis.$eigen) ? $eigen : 'assets/img/ph-rathaus.svg');
        $credit = 'Symbolbild · Merzenich Aktuell';
        $alt    = 'Symbolgrafik ' . $term->name;
    }
    return [
        'name'       => $term->name,
        'address'    => (string)get_term_meta($term->term_id, 'ma_ort_address', true),
        'summary'    => (string)get_term_meta($term->term_id, 'ma_ort_summary', true),
        'source_url' => (string)get_term_meta($term->term_id, 'ma_ort_source_url', true),
        'image'      => $url,
        'image_alt'  => $alt !== '' ? $alt : $term->name,
        'credit'     => $credit,
        'is_symbol'  => $bild_id === 0 || $credit === 'Symbolbild · Merzenich Aktuell',
    ];
}
