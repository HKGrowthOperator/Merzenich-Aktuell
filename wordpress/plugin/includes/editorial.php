<?php
if (!defined('ABSPATH')) { exit; }

function ma_register_editorial_hooks(): void {
    add_action('add_meta_boxes', 'ma_editorial_boxes');
    add_action('save_post', 'ma_save_editorial_meta');
    add_filter('wp_insert_post_data','ma_publication_gate',20,2);
}

function ma_editorial_boxes(): void {
    add_meta_box('ma_editorial','Redaktionsdaten','ma_editorial_box','post','normal','high');
    add_meta_box('ma_ai','KI & redaktionelle Transparenz','ma_ai_box','post','side','default');
    foreach (['ma_event','ma_property','ma_job','ma_obituary','ma_family_notice'] as $pt) {
        add_meta_box('ma_dates','Zeitraum & Freigabe','ma_dates_box',$pt,'side','default');
        if ($pt !== 'ma_event') add_meta_box('ma_market_details','Anzeigendaten','ma_market_details_box',$pt,'normal','default');
    }
    add_meta_box('ma_ad_details','Werbemittel','ma_ad_details_box','ma_ad','normal','high');
}
function ma_field(string $label,string $name,string $type='text'): void {
    $v = esc_attr((string)get_post_meta(get_the_ID(),$name,true));
    echo '<p><label><strong>'.esc_html($label).'</strong><br><input style="width:100%" type="'.esc_attr($type).'" name="'.esc_attr($name).'" value="'.$v.'"></label></p>';
}
function ma_check(string $label,string $name): void {
    $v = (string)get_post_meta(get_the_ID(),$name,true);
    echo '<p><label><input type="checkbox" name="'.esc_attr($name).'" value="1" '.checked($v,'1',false).'> '.esc_html($label).'</label></p>';
}
function ma_editorial_box(): void {
    wp_nonce_field('ma_editorial_save','ma_editorial_nonce');
    ma_field('Original Source URL','ma_source_url','url');
    ma_field('Source Publisher','ma_source_publisher');
    ma_field('Source Published At','ma_source_published_at','datetime-local');
    ma_field('Last Checked At','ma_source_checked_at','datetime-local');
    ma_field('Bildcredit','ma_image_credit');
    ma_field('Bildlizenz / Freigabe','ma_image_license');
    ma_field('Original-Bild-URL','ma_image_original_url','url');
    ma_field('Aufmacher bis','ma_top_until','datetime-local');
    ma_check('Quelle geprüft','ma_source_verified');
    ma_check('Datum geprüft','ma_date_verified');
    ma_check('Ort geprüft','ma_place_verified');
    ma_check('Bildrechte geprüft','ma_image_rights_verified');
    ma_check('Human Review','ma_human_reviewed');
    ma_check('Top Story fixiert','ma_top_pinned');
}
function ma_ai_box(): void {
    ma_check('AI used','ma_ai_used'); ma_field('AI use type','ma_ai_use_type');
    ma_check('AI generated image','ma_ai_generated_image'); ma_check('AI manipulated image','ma_ai_manipulated_image');
    ma_field('Reviewed by','ma_reviewed_by'); ma_field('Reviewed at','ma_reviewed_at','datetime-local');
    ma_field('Editorial responsibility','ma_editorial_responsibility');
}
function ma_market_details_box(): void {
    wp_nonce_field('ma_editorial_save','ma_editorial_nonce');
    $pt=get_post_type();
    if ($pt==='ma_property') { ma_field('Preis','ma_property_price'); ma_field('Zimmer','ma_property_rooms'); ma_field('Wohnfläche','ma_property_area'); ma_field('Anbieter','ma_property_provider'); }
    if ($pt==='ma_job') { ma_field('Unternehmen','ma_job_company'); ma_field('Arbeitszeit','ma_job_hours'); ma_field('Beschäftigungsart','ma_job_type'); ma_field('Bewerbungs-URL','ma_job_apply_url','url'); }
    if ($pt==='ma_obituary') { ma_field('Geburtsdatum','ma_obituary_birth','date'); ma_field('Sterbedatum','ma_obituary_death','date'); ma_field('Bestattung','ma_obituary_funeral'); }
    if ($pt==='ma_family_notice') { ma_field('Anlass/Datum','ma_family_date','date'); ma_field('Öffentlicher Kontakt','ma_family_contact'); }
}
function ma_ad_details_box(): void {
    wp_nonce_field('ma_editorial_save','ma_editorial_nonce');
    ma_field('Sponsor','ma_ad_sponsor'); ma_field('Ziel-URL','ma_ad_url','url'); ma_field('Start','ma_ad_start','datetime-local'); ma_field('Ende','ma_ad_end','datetime-local'); ma_field('Priorität','ma_ad_priority','number');
    $current=(string)get_post_meta(get_the_ID(),'ma_ad_slot',true); echo '<p><label><strong>Slot</strong><br><select name="ma_ad_slot" style="width:100%">'; foreach(ma_ad_slots() as $slot) echo '<option value="'.esc_attr($slot).'" '.selected($current,$slot,false).'>'.esc_html($slot).'</option>'; echo '</select></label></p>';
    ma_check('Aktiv','ma_ad_active');
}
function ma_dates_box(): void {
    wp_nonce_field('ma_editorial_save','ma_editorial_nonce');
    $pt = get_post_type();
    if ($pt === 'ma_event') { ma_field('Beginn','ma_event_start','datetime-local'); ma_field('Ende','ma_event_end','datetime-local'); ma_field('Ort','ma_event_place'); ma_field('Veranstalter','ma_event_organizer'); ma_field('Quelle','ma_source_url','url'); }
    else { ma_field('Aktiv bis','ma_end_at','datetime-local'); ma_field('Kontakt','ma_contact'); ma_check('Freigabe dokumentiert','ma_release_confirmed'); }
}
function ma_save_editorial_meta(int $post_id): void {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!isset($_POST['ma_editorial_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_editorial_nonce'])),'ma_editorial_save')) { return; }
    if (!current_user_can('edit_post',$post_id)) { return; }
    $text = ['ma_source_publisher','ma_image_credit','ma_image_license','ma_ai_use_type','ma_reviewed_by','ma_editorial_responsibility','ma_event_place','ma_event_organizer','ma_contact','ma_property_price','ma_property_rooms','ma_property_area','ma_property_provider','ma_job_company','ma_job_hours','ma_job_type','ma_obituary_funeral','ma_family_contact','ma_ad_sponsor','ma_ad_slot','ma_ad_priority'];
    $url = ['ma_source_url','ma_image_original_url','ma_job_apply_url','ma_ad_url'];
    $date = ['ma_source_published_at','ma_source_checked_at','ma_top_until','ma_reviewed_at','ma_event_start','ma_event_end','ma_end_at','ma_obituary_birth','ma_obituary_death','ma_family_date','ma_ad_start','ma_ad_end'];
    $checks = ['ma_source_verified','ma_date_verified','ma_place_verified','ma_image_rights_verified','ma_human_reviewed','ma_top_pinned','ma_ai_used','ma_ai_generated_image','ma_ai_manipulated_image','ma_release_confirmed','ma_ad_active'];
    foreach ($text as $k) if (isset($_POST[$k])) update_post_meta($post_id,$k,sanitize_text_field(wp_unslash($_POST[$k])));
    foreach ($url as $k) if (isset($_POST[$k])) update_post_meta($post_id,$k,esc_url_raw(wp_unslash($_POST[$k])));
    foreach ($date as $k) if (isset($_POST[$k])) update_post_meta($post_id,$k,sanitize_text_field(wp_unslash($_POST[$k])));
    foreach ($checks as $k) update_post_meta($post_id,$k,isset($_POST[$k])?'1':'0');
}
function ma_publication_gate(array $data,array $postarr): array {
    if (($data['post_type'] ?? '') !== 'post' || ($data['post_status'] ?? '') !== 'publish') { return $data; }
    if (!empty($postarr['ID'])) {
        $required = ['ma_source_verified','ma_date_verified','ma_place_verified','ma_human_reviewed'];
        foreach ($required as $key) {
            if (get_post_meta((int)$postarr['ID'],$key,true) !== '1') { $data['post_status']='draft'; break; }
        }
        if (has_post_thumbnail((int)$postarr['ID']) && get_post_meta((int)$postarr['ID'],'ma_image_rights_verified',true) !== '1') { $data['post_status']='draft'; }
    }
    return $data;
}
