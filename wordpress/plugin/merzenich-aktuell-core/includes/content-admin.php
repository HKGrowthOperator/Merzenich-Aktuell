<?php
if (!defined('ABSPATH')) { exit; }

function ma_register_content_admin_hooks(): void {
    add_action('add_meta_boxes', 'ma_add_content_meta_boxes');
    add_action('save_post', 'ma_save_content_meta_boxes', 10, 2);
    add_filter('wp_insert_post_data', 'ma_market_publication_gate', 20, 2);
}

function ma_add_content_meta_boxes(): void {
    add_meta_box('ma-event-details', 'Veranstaltungsdaten', 'ma_render_event_meta_box', 'ma_event', 'normal', 'high');
    add_meta_box('ma-property-details', 'Immobiliendaten', 'ma_render_property_meta_box', 'ma_property', 'normal', 'high');
    add_meta_box('ma-job-details', 'Stellendaten', 'ma_render_job_meta_box', 'ma_job', 'normal', 'high');
    add_meta_box('ma-obituary-details', 'Traueranzeige', 'ma_render_obituary_meta_box', 'ma_obituary', 'normal', 'high');
    add_meta_box('ma-family-details', 'Familienanzeige', 'ma_render_family_meta_box', 'ma_family_notice', 'normal', 'high');
    add_meta_box('ma-ad-details', 'Werbeschaltung', 'ma_render_ad_meta_box', 'ma_ad', 'normal', 'high');
}

function ma_admin_field_value(int $post_id, string $key): string {
    return (string)get_post_meta($post_id, $key, true);
}

function ma_admin_datetime_input_value(string $value): string {
    if ($value === '') return '';
    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $value)) return substr($value, 0, 16);
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $value)) return str_replace(' ', 'T', substr($value, 0, 16));
    return '';
}

function ma_admin_input(string $name, string $label, string $value='', string $type='text', string $placeholder='', string $description=''): void {
    if ($type === 'datetime-local') $value = ma_admin_datetime_input_value($value);
    echo '<tr><th scope="row"><label for="'.esc_attr($name).'">'.esc_html($label).'</label></th><td>';
    echo '<input class="regular-text" type="'.esc_attr($type).'" id="'.esc_attr($name).'" name="'.esc_attr($name).'" value="'.esc_attr($value).'"'.($placeholder!==''?' placeholder="'.esc_attr($placeholder).'"':'').'>';
    if ($description!=='') echo '<p class="description">'.esc_html($description).'</p>';
    echo '</td></tr>';
}

function ma_admin_textarea(string $name, string $label, string $value='', string $description=''): void {
    echo '<tr><th scope="row"><label for="'.esc_attr($name).'">'.esc_html($label).'</label></th><td>';
    echo '<textarea class="large-text" rows="4" id="'.esc_attr($name).'" name="'.esc_attr($name).'">'.esc_textarea($value).'</textarea>';
    if ($description!=='') echo '<p class="description">'.esc_html($description).'</p>';
    echo '</td></tr>';
}

function ma_admin_select(string $name, string $label, string $value, array $options): void {
    echo '<tr><th scope="row"><label for="'.esc_attr($name).'">'.esc_html($label).'</label></th><td><select id="'.esc_attr($name).'" name="'.esc_attr($name).'">';
    foreach ($options as $key=>$text) echo '<option value="'.esc_attr($key).'" '.selected($value,(string)$key,false).'>'.esc_html($text).'</option>';
    echo '</select></td></tr>';
}

function ma_admin_checkbox(string $name, string $label, bool $checked, string $description='', string $check_text='Aktiv'): void {
    echo '<tr><th scope="row">'.esc_html($label).'</th><td><label><input type="checkbox" name="'.esc_attr($name).'" value="1" '.checked($checked,true,false).'> '.esc_html($check_text).'</label>';
    if ($description!=='') echo '<p class="description">'.esc_html($description).'</p>';
    echo '</td></tr>';
}

function ma_admin_meta_box_start(): void {
    wp_nonce_field('ma_save_content_admin', 'ma_content_admin_nonce');
    echo '<table class="form-table" role="presentation">';
}

function ma_admin_meta_box_end(): void { echo '</table>'; }

function ma_render_event_meta_box(WP_Post $post): void {
    $source = ma_admin_field_value($post->ID,'ma_event_source_url');
    if ($source === '') $source = ma_admin_field_value($post->ID,'ma_source_url');
    ma_admin_meta_box_start();
    ma_admin_input('ma_event_start','Beginn',ma_admin_field_value($post->ID,'ma_event_start'),'datetime-local','','Datum und Uhrzeit des Starts.');
    ma_admin_input('ma_event_end','Ende',ma_admin_field_value($post->ID,'ma_event_end'),'datetime-local','','Bei eintägigen Terminen optional; ohne Ende gilt der Start als Ablaufzeit.');
    ma_admin_input('ma_event_place','Ort / Veranstaltungsstätte',ma_admin_field_value($post->ID,'ma_event_place'),'text','Bürgerhaus Merzenich');
    ma_admin_input('ma_event_organizer','Veranstalter',ma_admin_field_value($post->ID,'ma_event_organizer'),'text','Gemeinde / Verein / Initiative');
    ma_admin_input('ma_event_source_url','Quelle / Veranstaltungslink',$source,'url','https://...');
    ma_admin_input('ma_event_price','Eintritt / Kosten',ma_admin_field_value($post->ID,'ma_event_price'),'text','kostenfrei / 10 €');
    ma_admin_textarea('ma_event_registration','Anmeldung / Hinweise',ma_admin_field_value($post->ID,'ma_event_registration'),'Optional: Anmeldung, Treffpunkt oder besondere Hinweise.');
    ma_admin_meta_box_end();
}

function ma_render_property_meta_box(WP_Post $post): void {
    ma_admin_meta_box_start();
    ma_admin_select('ma_property_mode','Art',ma_admin_field_value($post->ID,'ma_property_mode'),['rent'=>'Mieten','buy'=>'Kaufen']);
    ma_admin_input('ma_property_price','Preis',ma_admin_field_value($post->ID,'ma_property_price'),'text','1.250 € / Monat oder 385.000 €');
    ma_admin_input('ma_property_warm_price','Warmmiete',ma_admin_field_value($post->ID,'ma_property_warm_price'),'text','1.480 € / Monat','Nur bei Mietangeboten. Leer lassen, wenn das Portal keine nennt.');
    ma_admin_input('ma_property_rooms','Zimmer',ma_admin_field_value($post->ID,'ma_property_rooms'),'number','4');
    ma_admin_input('ma_property_area','Wohnfläche',ma_admin_field_value($post->ID,'ma_property_area'),'text','125 m²');
    ma_admin_input('ma_property_lot','Grundstück',ma_admin_field_value($post->ID,'ma_property_lot'),'text','480 m²');
    ma_admin_input('ma_property_address','Adresse / Lage',ma_admin_field_value($post->ID,'ma_property_address'),'text','Merzenich');
    ma_admin_input('ma_property_available_from','Verfügbar ab',ma_admin_field_value($post->ID,'ma_property_available_from'),'text','sofort / 01.11.2026');
    ma_admin_input('ma_property_provider','Anbieter',ma_admin_field_value($post->ID,'ma_property_provider'),'text','Makler / Eigentümer');
    ma_admin_input('ma_property_contact','Kontakt',ma_admin_field_value($post->ID,'ma_property_contact'),'text','E-Mail / Telefon');
    ma_admin_input('ma_property_url','Externer Link (Originalportal)',ma_admin_field_value($post->ID,'ma_property_url'),'url','https://...','Das Inserat bleibt auf das Portal verlinkt. Fremde Bilder nicht lokal kopieren, solange die Bildrechte nicht bestätigt sind.');
    ma_admin_input('ma_source_published_at','Im Portal veröffentlicht am',ma_admin_field_value($post->ID,'ma_source_published_at'),'datetime-local');
    ma_admin_input('ma_verified_at','Gegen Quelle geprüft am',ma_admin_field_value($post->ID,'ma_verified_at'),'datetime-local','','Wann wurden Preis, Fläche und Verfügbarkeit zuletzt mit dem Portal abgeglichen?');
    ma_admin_input('ma_end_at','Anzeige endet',ma_admin_field_value($post->ID,'ma_end_at'),'datetime-local','','Nach Ablauf wird die Anzeige nicht mehr als aktiv ausgespielt - weder auf der Startseite noch im Archiv.');
    ma_admin_checkbox('ma_release_confirmed','Freigabe',ma_admin_field_value($post->ID,'ma_release_confirmed')==='1','Nur veröffentlichen, wenn die Freigabe des Auftraggebers dokumentiert ist.','Freigabe dokumentiert');
    ma_admin_meta_box_end();
}

function ma_render_job_meta_box(WP_Post $post): void {
    ma_admin_meta_box_start();
    ma_admin_input('ma_job_company','Unternehmen',ma_admin_field_value($post->ID,'ma_job_company'),'text','Unternehmen GmbH');
    // Direkter Arbeitgeber und Personaldienstleister muessen unterscheidbar
    // sein. Eine Anzeige eines Vermittlers, die wie die des Betriebs aussieht,
    // fuehrt Bewerber in die Irre - deshalb ist das ein eigenes Feld und nicht
    // ein Zusatz im Firmennamen.
    ma_admin_select('ma_job_provider_type','Wer inseriert?',ma_admin_field_value($post->ID,'ma_job_provider_type'),[''=>'Bitte wählen','direct'=>'Direkter Arbeitgeber','agency'=>'Personaldienstleister / Vermittler']);
    ma_admin_input('ma_job_provider','Inserierender Dienstleister',ma_admin_field_value($post->ID,'ma_job_provider'),'text','Name des Personaldienstleisters','Nur ausfüllen, wenn nicht der Arbeitgeber selbst inseriert.');
    ma_admin_input('ma_job_location','Arbeitsort',ma_admin_field_value($post->ID,'ma_job_location'),'text','Merzenich');
    ma_admin_input('ma_job_address','Adresse',ma_admin_field_value($post->ID,'ma_job_address'),'text','Straße Nr., 52399 Merzenich');
    ma_admin_select('ma_job_type','Beschäftigungsart',ma_admin_field_value($post->ID,'ma_job_type'),[''=>'Bitte wählen','fulltime'=>'Vollzeit','parttime'=>'Teilzeit','minijob'=>'Minijob','training'=>'Ausbildung','internship'=>'Praktikum','freelance'=>'Freie Mitarbeit']);
    ma_admin_input('ma_job_hours','Arbeitszeit / Umfang',ma_admin_field_value($post->ID,'ma_job_hours'),'text','40 Std. / Woche');
    ma_admin_input('ma_job_start_date','Beginn',ma_admin_field_value($post->ID,'ma_job_start_date'),'text','ab sofort / 01.10.2026');
    ma_admin_input('ma_job_contact','Ansprechpartner',ma_admin_field_value($post->ID,'ma_job_contact'),'text','Name / E-Mail / Telefon');
    ma_admin_input('ma_job_apply_url','Bewerbungslink (Originalquelle)',ma_admin_field_value($post->ID,'ma_job_apply_url'),'url','https://...');
    ma_admin_input('ma_source_published_at','Bei der Quelle veröffentlicht am',ma_admin_field_value($post->ID,'ma_source_published_at'),'datetime-local');
    ma_admin_input('ma_verified_at','Gegen Quelle geprüft am',ma_admin_field_value($post->ID,'ma_verified_at'),'datetime-local');
    ma_admin_input('ma_end_at','Anzeige endet',ma_admin_field_value($post->ID,'ma_end_at'),'datetime-local','','Nach Ablauf wird die Stelle nicht mehr als aktiv ausgespielt - weder auf der Startseite noch im Archiv.');
    ma_admin_checkbox('ma_release_confirmed','Freigabe',ma_admin_field_value($post->ID,'ma_release_confirmed')==='1','Nur veröffentlichen, wenn die Freigabe des Unternehmens dokumentiert ist.','Freigabe dokumentiert');
    ma_admin_meta_box_end();
}

function ma_render_obituary_meta_box(WP_Post $post): void {
    ma_admin_meta_box_start();
    ma_admin_input('ma_obituary_name','Name',ma_admin_field_value($post->ID,'ma_obituary_name'),'text','Vorname Nachname');
    ma_admin_input('ma_obituary_birth','Geburtsdatum',ma_admin_field_value($post->ID,'ma_obituary_birth'),'date');
    ma_admin_input('ma_obituary_death','Sterbedatum',ma_admin_field_value($post->ID,'ma_obituary_death'),'date');
    ma_admin_input('ma_obituary_place','Ort',ma_admin_field_value($post->ID,'ma_obituary_place'),'text','Merzenich');
    ma_admin_textarea('ma_obituary_funeral','Trauerfeier / Bestattung',ma_admin_field_value($post->ID,'ma_obituary_funeral'));
    ma_admin_textarea('ma_obituary_family','Familienangabe',ma_admin_field_value($post->ID,'ma_obituary_family'),'Nur veröffentlichen, wenn die Angabe freigegeben ist.');
    ma_admin_input('ma_obituary_contact','Interner Kontakt',ma_admin_field_value($post->ID,'ma_obituary_contact'),'text','','Nicht automatisch öffentlich ausgeben.');
    ma_admin_input('ma_end_at','Anzeige endet',ma_admin_field_value($post->ID,'ma_end_at'),'datetime-local');
    ma_admin_checkbox('ma_release_confirmed','Freigabe',ma_admin_field_value($post->ID,'ma_release_confirmed')==='1','Für sensible personenbezogene Angaben muss die Freigabe dokumentiert sein.','Freigabe dokumentiert');
    ma_admin_meta_box_end();
}

function ma_render_family_meta_box(WP_Post $post): void {
    ma_admin_meta_box_start();
    ma_admin_select('ma_family_kind','Anlass',ma_admin_field_value($post->ID,'ma_family_kind'),['birth'=>'Geburt','wedding'=>'Hochzeit','anniversary'=>'Jubiläum','thanks'=>'Danksagung','congratulations'=>'Glückwunsch','other'=>'Sonstiges']);
    ma_admin_input('ma_family_date','Datum',ma_admin_field_value($post->ID,'ma_family_date'),'date');
    ma_admin_input('ma_family_place','Ort',ma_admin_field_value($post->ID,'ma_family_place'),'text','Merzenich');
    ma_admin_input('ma_family_contact','Interner Kontakt',ma_admin_field_value($post->ID,'ma_family_contact'),'text','','Nicht automatisch öffentlich ausgeben.');
    ma_admin_input('ma_end_at','Anzeige endet',ma_admin_field_value($post->ID,'ma_end_at'),'datetime-local');
    ma_admin_checkbox('ma_release_confirmed','Freigabe',ma_admin_field_value($post->ID,'ma_release_confirmed')==='1','Für personenbezogene Angaben und Bilder muss die Freigabe dokumentiert sein.','Freigabe dokumentiert');
    ma_admin_meta_box_end();
}

function ma_render_ad_meta_box(WP_Post $post): void {
    ma_admin_meta_box_start();
    ma_admin_checkbox('ma_ad_active','Schaltung aktiv',ma_admin_field_value($post->ID,'ma_ad_active')==='1','Zusätzlich müssen Werbung global und der Slot in Merzenich Aktuell → Werbung aktiviert sein.');
    $slots = ma_ad_slots();
    ma_admin_select('ma_ad_slot','Platzierung',ma_admin_field_value($post->ID,'ma_ad_slot'),array_combine($slots,$slots));
    ma_admin_input('ma_ad_sponsor','Sponsor / Firma',ma_admin_field_value($post->ID,'ma_ad_sponsor'),'text','Unternehmen GmbH');
    ma_admin_input('ma_ad_url','Ziel-URL',ma_admin_field_value($post->ID,'ma_ad_url'),'url','https://...');
    ma_admin_input('ma_ad_start','Start',ma_admin_field_value($post->ID,'ma_ad_start'),'datetime-local');
    ma_admin_input('ma_ad_end','Ende',ma_admin_field_value($post->ID,'ma_ad_end'),'datetime-local');
    ma_admin_input('ma_ad_priority','Priorität',ma_admin_field_value($post->ID,'ma_ad_priority'),'number','10','Höhere Zahl gewinnt innerhalb desselben Slots.');
    ma_admin_meta_box_end();
}

function ma_content_admin_schema(string $post_type): array {
    $common_end = ['ma_end_at'=>'datetime','ma_release_confirmed'=>'bool'];
    $schema = [
        'ma_event'=>[
            'ma_event_start'=>'datetime','ma_event_end'=>'datetime','ma_event_place'=>'text','ma_event_organizer'=>'text','ma_event_source_url'=>'url','ma_event_price'=>'text','ma_event_registration'=>'textarea',
        ],
        'ma_property'=>array_merge($common_end,[
            'ma_property_mode'=>'text','ma_property_price'=>'text','ma_property_rooms'=>'number','ma_property_area'=>'text','ma_property_lot'=>'text','ma_property_address'=>'text','ma_property_provider'=>'text','ma_property_contact'=>'text','ma_property_url'=>'url',
            'ma_property_warm_price'=>'text','ma_property_available_from'=>'text','ma_source_published_at'=>'datetime','ma_verified_at'=>'datetime',
        ]),
        'ma_job'=>array_merge($common_end,[
            'ma_job_company'=>'text','ma_job_location'=>'text','ma_job_type'=>'text','ma_job_hours'=>'text','ma_job_contact'=>'text','ma_job_apply_url'=>'url',
            'ma_job_address'=>'text','ma_job_start_date'=>'text','ma_job_provider_type'=>'text','ma_job_provider'=>'text','ma_source_published_at'=>'datetime','ma_verified_at'=>'datetime',
        ]),
        'ma_obituary'=>array_merge($common_end,[
            'ma_obituary_name'=>'text','ma_obituary_birth'=>'date','ma_obituary_death'=>'date','ma_obituary_place'=>'text','ma_obituary_funeral'=>'textarea','ma_obituary_family'=>'textarea','ma_obituary_contact'=>'text',
        ]),
        'ma_family_notice'=>array_merge($common_end,[
            'ma_family_kind'=>'text','ma_family_date'=>'date','ma_family_place'=>'text','ma_family_contact'=>'text',
        ]),
        'ma_ad'=>[
            'ma_ad_active'=>'bool','ma_ad_slot'=>'text','ma_ad_sponsor'=>'text','ma_ad_url'=>'url','ma_ad_start'=>'datetime','ma_ad_end'=>'datetime','ma_ad_priority'=>'number',
        ],
    ];
    return $schema[$post_type] ?? [];
}

function ma_sanitize_content_admin_value(string $type, $value): string {
    $value = is_string($value) ? wp_unslash($value) : $value;
    if ($type==='url') return esc_url_raw((string)$value);
    if ($type==='number') return (string)max(0,(float)$value);
    if ($type==='bool') return $value ? '1' : '0';
    if ($type==='textarea') return sanitize_textarea_field((string)$value);
    if ($type==='date') return preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$value) ? (string)$value : '';
    if ($type==='datetime') {
        $raw=(string)$value;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/',$raw)) return '';
        return str_replace('T',' ',$raw).':00';
    }
    return sanitize_text_field((string)$value);
}

function ma_save_content_meta_boxes(int $post_id, WP_Post $post): void {
    $schema = ma_content_admin_schema($post->post_type);
    if (!$schema) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if (!current_user_can('edit_post',$post_id)) return;
    if (!isset($_POST['ma_content_admin_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_content_admin_nonce'])),'ma_save_content_admin')) return;

    foreach ($schema as $key=>$type) {
        $raw = $type==='bool' ? isset($_POST[$key]) : ($_POST[$key] ?? '');
        $value = ma_sanitize_content_admin_value($type,$raw);
        if ($value==='') delete_post_meta($post_id,$key);
        else update_post_meta($post_id,$key,$value);
    }

    if ($post->post_type === 'ma_event') {
        $source = (string)get_post_meta($post_id,'ma_event_source_url',true);
        if ($source !== '') update_post_meta($post_id,'ma_source_url',$source);
    }
}

function ma_market_publication_gate(array $data,array $postarr): array {
    $gated=['ma_property','ma_job','ma_obituary','ma_family_notice'];
    if (!in_array($data['post_type']??'',$gated,true) || ($data['post_status']??'')!=='publish') return $data;

    $post_id=(int)($postarr['ID']??0);
    $submitted=isset($_POST['ma_release_confirmed']);
    $stored=$post_id && get_post_meta($post_id,'ma_release_confirmed',true)==='1';
    if(!$submitted && !$stored) $data['post_status']='draft';
    return $data;
}
