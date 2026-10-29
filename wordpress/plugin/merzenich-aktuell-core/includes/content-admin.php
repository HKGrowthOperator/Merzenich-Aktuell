<?php
if (!defined('ABSPATH')) { exit; }

function ma_register_content_admin_hooks(): void {
    add_action('add_meta_boxes', 'ma_add_content_meta_boxes');
    add_action('save_post', 'ma_save_content_meta_boxes', 10, 2);
    add_filter('wp_insert_post_data', 'ma_market_publication_gate', 20, 2);
    add_action('enqueue_block_editor_assets', 'ma_datenkasten_aufklappen');
}

/**
 * Block-Editor: Datum, Ort und die anderen Pflichtangaben stehen in Kästen unter dem Text,
 * die WordPress zugeklappt zeigt („Metaboxen“). Bei diesen Inhaltsarten gleich aufklappen.
 */
function ma_datenkasten_aufklappen(): void {
    if (!in_array((string) get_post_type(), ['ma_event', 'ma_property', 'ma_job', 'ma_obituary', 'ma_family_notice', 'ma_tip'], true)) return;
    wp_register_script('ma-datenkasten', false, ['wp-data', 'wp-preferences'], MA_CORE_VERSION, true);
    wp_enqueue_script('ma-datenkasten');
    wp_add_inline_script('ma-datenkasten', "try{var pr=wp.data.dispatch('core/preferences');pr.set('core/edit-post','metaBoxesMainIsOpen',true);"
        . "if(!wp.data.select('core/preferences').get('core/edit-post','metaBoxesMainOpenHeight'))pr.set('core/edit-post','metaBoxesMainOpenHeight',Math.round(innerHeight*0.45));}catch(e){}");
}

function ma_add_content_meta_boxes(): void {
    add_meta_box('ma-event-details', 'Veranstaltungsdaten', 'ma_render_event_meta_box', 'ma_event', 'normal', 'high');
    add_meta_box('ma-property-details', 'Immobiliendaten', 'ma_render_property_meta_box', 'ma_property', 'normal', 'high');
    add_meta_box('ma-job-details', 'Stellendaten', 'ma_render_job_meta_box', 'ma_job', 'normal', 'high');
    add_meta_box('ma-obituary-details', 'Traueranzeige', 'ma_render_obituary_meta_box', 'ma_obituary', 'normal', 'high');
    add_meta_box('ma-family-details', 'Familienanzeige', 'ma_render_family_meta_box', 'ma_family_notice', 'normal', 'high');
    add_meta_box('ma-tip-details', 'Tipp / Sponsoring', 'ma_render_tip_meta_box', 'ma_tip', 'normal', 'high');
    add_meta_box('ma-ad-details', 'Werbeschaltung', 'ma_render_ad_meta_box', 'ma_ad', 'normal', 'high');
    // Anzeige anlegen auf einem Bildschirm: Werbeschaltung, dann das Werbemittel
    // (Bild) direkt darunter; Permalink, Kommentare und Trackbacks gibt es nicht.
    remove_meta_box('postimagediv', 'ma_ad', 'side');
    add_meta_box('postimagediv', 'Werbemittel (Bild)', 'post_thumbnail_meta_box', 'ma_ad', 'normal', 'high');
    foreach (['slugdiv', 'commentstatusdiv', 'commentsdiv', 'trackbacksdiv', 'postcustom'] as $box) remove_meta_box($box, 'ma_ad', 'normal');
}
add_filter('admin_post_thumbnail_html', function (string $html, $post_id): string {
    if (get_post_type((int) $post_id) !== 'ma_ad') return $html;
    $slot = (string) get_post_meta((int) $post_id, 'ma_ad_slot', true);
    $hinweis = str_starts_with($slot, 'homepage_band_') || $slot === 'article_inline_1' ? 'Querformat, mindestens 1200 × 400 px (Werbeband).' : 'Hochformat oder 4:3, mindestens 600 × 600 px (Spaltenplatz).';
    return '<p class="description">' . esc_html($hinweis) . ' Ohne Bild erscheint der Titel als Textanzeige.</p>' . $html;
}, 10, 2);
add_filter('gettext', function (string $t, string $orig, string $domain): string {
    if ($domain !== 'default' || !function_exists('get_current_screen')) return $t;
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'ma_ad') return $t;
    if ($orig === 'Excerpt') return 'Kurztext (optional, erscheint im Menü „Vereine“ und auf Spaltenplätzen)';
    if (str_starts_with($orig, 'Excerpts are optional hand-crafted summaries')) return 'Ein Satz zur Anzeige, zum Beispiel das Angebot oder der Anlass.';
    return $t;
}, 10, 3);

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

function ma_admin_select(string $name, string $label, string $value, array $options, string $description=''): void {
    echo '<tr><th scope="row"><label for="'.esc_attr($name).'">'.esc_html($label).'</label></th><td><select id="'.esc_attr($name).'" name="'.esc_attr($name).'">';
    foreach ($options as $key=>$text) echo '<option value="'.esc_attr($key).'" '.selected($value,(string)$key,false).'>'.esc_html($text).'</option>';
    echo '</select>';
    if ($description!=='') echo '<p class="description">'.esc_html($description).'</p>';
    echo '</td></tr>';
}

function ma_admin_checkbox(string $name, string $label, bool $checked, string $description='', string $check_text='Aktiv'): void {
    $partner = function_exists('ma_current_partner_policy') ? ma_current_partner_policy() : null;
    if ($partner && in_array($name, ['ma_release_confirmed','ma_ad_active'], true)) {
        echo '<tr><th scope="row">'.esc_html($label).'</th><td><strong>Freigabe durch Redaktion</strong>';
        echo '<p class="description">Dieser Partner-Zugang kann die Veröffentlichung nicht selbst freigeben.</p></td></tr>';
        return;
    }
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

function ma_render_tip_meta_box(WP_Post $post): void {
    ma_admin_meta_box_start();
    ma_admin_select('ma_tip_kind','Kennzeichnung',ma_admin_field_value($post->ID,'ma_tip_kind'),[
        'tipp'=>'Tipp','sponsoring'=>'Sponsoring','anzeige'=>'Anzeige'
    ]);
    ma_admin_input('ma_tip_sponsor','Sponsor / Auftraggeber',ma_admin_field_value($post->ID,'ma_tip_sponsor'),'text','Unternehmen / Verein');
    ma_admin_input('ma_tip_url','Partner-Link',ma_admin_field_value($post->ID,'ma_tip_url'),'url','https://...');
    ma_admin_input('ma_tip_start','Start der Platzierung',ma_admin_field_value($post->ID,'ma_tip_start'),'datetime-local');
    ma_admin_input('ma_end_at','Platzierung endet',ma_admin_field_value($post->ID,'ma_end_at'),'datetime-local');
    ma_admin_checkbox('ma_release_confirmed','Freigabe',ma_admin_field_value($post->ID,'ma_release_confirmed')==='1','Erst nach dokumentierter redaktioneller und kaufmännischer Freigabe veröffentlichen.','Freigabe dokumentiert');
    ma_admin_meta_box_end();
}

function ma_render_ad_meta_box(WP_Post $post): void {
    ma_admin_meta_box_start();
    $partner = function_exists('ma_current_partner_policy') ? ma_current_partner_policy() : null;
    echo '<tr><td colspan="2" style="padding:0 0 8px"><p class="description" style="margin:0">Alles auf einer Seite: Titel oben, hier Platz und Laufzeit, darunter das Bild. ' . ($partner ? 'Nach dem Einreichen prüft die Redaktion und schaltet die Anzeige frei.' : 'Veröffentlichen + „Schaltung aktiv“ = die Anzeige läuft. Ohne laufende Anzeige bleibt ein Platz leer.') . '</p></td></tr>';
    if (function_exists('ma_render_ad_item') && has_post_thumbnail($post)) echo '<tr><th scope="row">Vorschau</th><td><div class="ma-ad ma-ad--vorschau" style="max-width:420px;border:1px solid #dcdcde;padding:8px;background:#fff">' . ma_render_ad_item($post) . '</div></td></tr>';
    if (!$partner) ma_admin_checkbox('ma_ad_active','Schaltung aktiv',ma_admin_field_value($post->ID,'ma_ad_active')==='1','Zusätzlich müssen Werbung global und der Platz unter Werbung → Werbeplätze eingeschaltet sein.');
    // Format (29.10.2026, werbeformate.php): setzt Plätze und Laufzeit; den Preis sieht nur die Redaktion.
    if (function_exists('ma_werbeformate_aktiv')) {
        $format = (string) get_post_meta($post->ID, 'ma_ad_format', true);
        $formate = ['' => '– kein Format, Plätze selbst wählen –'];
        foreach (ma_werbeformate_aktiv() as $k => $f) $formate[$k] = ma_werbeformat_zeile($k, ma_werbeformat_preis_sichtbar());
        if ($format !== '' && !isset($formate[$format]) && ($z = ma_werbeformat_zeile($format, ma_werbeformat_preis_sichtbar())) !== '') $formate[$format] = $z . ' (nicht mehr buchbar)';
        ma_admin_select('ma_ad_format', 'Format', $format, $formate, 'Beim Wechsel des Formats setzt das Speichern die Plätze des Formats und, wenn noch kein Ende steht, das Ende nach der Laufzeit.');
    }
    // Unternehmens-Zugaenge sehen nur die Plaetze ihres Kontingents
    // (Benutzerprofil, Voreinstellung: alle Werbebaender der Startseite).
    $slots = function_exists('ma_ad_allowed_slots_for_current_user') ? ma_ad_allowed_slots_for_current_user() : ma_ad_slots();
    $options = ['' => 'Bitte wählen'];
    foreach ($slots as $slot) $options[$slot] = ma_ad_slot_label($slot);
    ma_admin_select('ma_ad_slot','Platzierung',ma_admin_field_value($post->ID,'ma_ad_slot'),$options);
    // Kampagne über mehrere Plätze (03.10.2026): dasselbe Motiv zusätzlich auf weiteren Plätzen.
    $weitere = array_map('strval', (array) get_post_meta($post->ID, 'ma_ad_slot_weitere'));
    echo '<tr><th scope="row">Weitere Plätze (Kampagne)</th><td><input type="hidden" name="ma_ad_slot_weitere_feld" value="1"><div style="columns:2;max-width:640px">';
    foreach ($slots as $slot) printf('<label style="display:block"><input type="checkbox" name="ma_ad_slot_weitere[]" value="%s"%s> %s</label>', esc_attr($slot), checked(in_array($slot, $weitere, true), true, false), esc_html(ma_ad_slot_label($slot)));
    echo '</div><p class="description">Optional. Die Anzeige läuft zusätzlich zum Hauptplatz auf jedem gewählten Platz und wechselt sich dort mit anderen laufenden Anzeigen ab. Freigabe und Laufzeit gelten für alle Plätze.</p></td></tr>';
    if (function_exists('ma_ad_quota_applies') && ma_ad_quota_applies()) {
        $quota = ma_ad_quota_for_user((int)get_current_user_id());
        echo '<tr><th scope="row">Ihr Kontingent</th><td>'.esc_html((string)$quota['max']).' gleichzeitig laufende oder eingereichte Anzeige'.($quota['max'] === 1 ? '' : 'n').'.';
        echo '<p class="description">Jede Anzeige wird vor der Veröffentlichung von der Redaktion geprüft. Mehr Plätze: Preis auf Anfrage bei der Redaktion.</p></td></tr>';
    }
    ma_admin_input('ma_ad_sponsor','Sponsor / Firma',ma_admin_field_value($post->ID,'ma_ad_sponsor'),'text','Unternehmen GmbH');
    // Ziel: die Website der Firma (29.10.2026). Neue Anzeigen eines Firmen-Zugangs bekommen die Website ihres Profils vorbelegt.
    $ziel = ma_admin_field_value($post->ID,'ma_ad_url');
    if ($ziel === '' && defined('MA_FIRMA_META') && ($bid = (int) get_user_meta(get_current_user_id(), MA_FIRMA_META, true))) $ziel = (string) get_post_meta($bid, 'ma_business_website', true);
    ma_admin_input('ma_ad_url','Ziel-URL (Website der Firma)',$ziel,'url','https://www.firma.de/','Ein Klick auf die Anzeige öffnet diese Seite direkt in einem neuen Fenster.');
    ma_admin_input('ma_ad_knopf','Knopftext',ma_admin_field_value($post->ID,'ma_ad_knopf'),'text','Zur Website','Optional, erscheint nur mit Ziel-URL.');
    $vid = (int) ma_admin_field_value($post->ID,'ma_ad_video');
    $vurl = $vid ? (string) wp_get_attachment_url($vid) : '';
    echo '<tr><th scope="row"><label for="ma_ad_video">Video</label></th><td data-ma-ad-video>';
    echo '<input type="hidden" id="ma_ad_video" name="ma_ad_video" value="' . esc_attr($vid ? (string) $vid : '') . '">';
    echo '<span data-ma-ad-video-name>' . ($vurl !== '' ? esc_html(basename($vurl)) : 'kein Video') . '</span> ';
    echo '<button type="button" class="button" data-ma-ad-video-waehlen>Video auswählen oder hochladen</button> <button type="button" class="button-link" data-ma-ad-video-weg' . ($vid ? '' : ' hidden') . '>entfernen</button>';
    echo '<p class="description">Optional: MP4 oder WebM, stumm, 6 bis 15 Sekunden, ohne Schrift im Bild, höchstens 6 MB (am besten unter 1 MB). Läuft auf den großen Plätzen statt des Bildes; das Bild bleibt Standbild.</p></td></tr>';
    ma_admin_input('ma_ad_start','Start',ma_admin_field_value($post->ID,'ma_ad_start'),'datetime-local');
    ma_admin_input('ma_ad_end','Ende',ma_admin_field_value($post->ID,'ma_ad_end'),'datetime-local');
    if (!$partner) ma_admin_select('ma_ad_priority','Priorität',(string) (function_exists('ma_ad_stufe') ? ma_ad_stufe($post->ID) : 2),['1'=>'1 · Hoch','2'=>'2 · Mitte','3'=>'3 · Niedrig'],'Drei Stufen wie bei der Relevanz der Meldungen. Laufen mehrere Anzeigen auf einem Platz, steht Hoch vorn; bei gleicher Stufe die neuere.');
    $bild = (int) get_post_thumbnail_id($post->ID);
    ma_admin_input('ma_ad_alt','Alternativtext des Bildes',$bild ? (string) get_post_meta($bild, '_wp_attachment_image_alt', true) : '','text','Was zeigt das Werbemittel? Für Screenreader.','Leer = Titel der Anzeige.');
    if ($partner && function_exists('ma_partner_rights_field')) {
        // Partner reichen Anzeigen mit Bild nur mit bestätigten Bildrechten ein (partners.php, bildrechte.php).
        wp_nonce_field('ma_editorial_save', 'ma_editorial_nonce');
        echo '<tr><th scope="row">Bildrechte</th><td>'; ma_partner_rights_field($post->ID); echo '</td></tr>';
    }
    ma_admin_meta_box_end();
}

/* Video-Auswahl im Werbemittel (29.10.2026): Mediathek-Fenster nur mit Videos statt der Medien-ID. */
add_action('admin_enqueue_scripts', function (): void {
    $s = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$s || $s->base !== 'post' || $s->post_type !== 'ma_ad') return;
    wp_enqueue_media();
    wp_add_inline_script('media-editor', <<<'JS'
document.addEventListener('click', function (e) {
  var z = e.target.closest('[data-ma-ad-video]'); if (!z) return;
  var feld = z.querySelector('input[name="ma_ad_video"]'), name = z.querySelector('[data-ma-ad-video-name]'), weg = z.querySelector('[data-ma-ad-video-weg]');
  if (e.target.closest('[data-ma-ad-video-weg]')) { feld.value = ''; name.textContent = 'kein Video'; weg.hidden = true; return; }
  if (!e.target.closest('[data-ma-ad-video-waehlen]') || !window.wp || !wp.media) return;
  var f = wp.media({ title: 'Video für die Anzeige', button: { text: 'Dieses Video nehmen' }, library: { type: 'video' }, multiple: false });
  f.on('select', function () { var a = f.state().get('selection').first().toJSON(); feld.value = a.id; name.textContent = a.filename || a.title; weg.hidden = false; });
  f.open();
});
JS);
});

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
        'ma_tip'=>array_merge($common_end,[
            'ma_tip_kind'=>'text','ma_tip_sponsor'=>'text','ma_tip_url'=>'url','ma_tip_start'=>'datetime',
        ]),
        'ma_ad'=>[
            'ma_ad_active'=>'bool','ma_ad_slot'=>'text','ma_ad_sponsor'=>'text','ma_ad_url'=>'url','ma_ad_knopf'=>'text','ma_ad_video'=>'number','ma_ad_start'=>'datetime','ma_ad_end'=>'datetime','ma_ad_priority'=>'stufe','ma_ad_format'=>'format',
        ],
    ];
    return $schema[$post_type] ?? [];
}

function ma_sanitize_content_admin_value(string $type, $value): string {
    $value = is_string($value) ? wp_unslash($value) : $value;
    if ($type==='url') return esc_url_raw((string)$value);
    if ($type==='number') return (string)max(0,(float)$value);
    if ($type==='stufe') return in_array((string)$value,['1','2','3'],true) ? (string)$value : '';
    if ($type==='format') return function_exists('ma_werbeformat') && ma_werbeformat(sanitize_key((string)$value)) ? sanitize_key((string)$value) : '';
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

    $partner = function_exists('ma_current_partner_policy') ? ma_current_partner_policy() : null;
    $video_vorher = $post->post_type === 'ma_ad' ? (string) get_post_meta($post_id, 'ma_ad_video', true) : '';
    $format_vorher = $post->post_type === 'ma_ad' ? (string) get_post_meta($post_id, 'ma_ad_format', true) : '';
    foreach ($schema as $key=>$type) {
        if ($partner && in_array($key, ['ma_release_confirmed','ma_ad_active'], true)) {
            delete_post_meta($post_id,$key);
            continue;
        }
        $raw = $type==='bool' ? isset($_POST[$key]) : ($_POST[$key] ?? '');
        $value = ma_sanitize_content_admin_value($type,$raw);
        if ($partner && $post->post_type === 'ma_ad' && $key === 'ma_ad_slot') {
            $allowed_partner_slots = function_exists('ma_ad_allowed_slots_for_current_user') ? ma_ad_allowed_slots_for_current_user() : [];
            if (!in_array($value,$allowed_partner_slots,true)) $value = '';
        }
        if ($value==='') delete_post_meta($post_id,$key);
        else update_post_meta($post_id,$key,$value);
    }

    if ($post->post_type === 'ma_event') {
        $source = (string)get_post_meta($post_id,'ma_event_source_url',true);
        if ($source !== '') update_post_meta($post_id,'ma_source_url',$source);
    }
    if ($post->post_type === 'ma_ad' && isset($_POST['ma_ad_slot_weitere_feld'])) {
        $erlaubt = $partner && function_exists('ma_ad_allowed_slots_for_current_user') ? ma_ad_allowed_slots_for_current_user() : ma_ad_slots();
        $haupt = (string) get_post_meta($post_id, 'ma_ad_slot', true);
        $neu = array_values(array_unique(array_filter(array_map('sanitize_key', (array) wp_unslash($_POST['ma_ad_slot_weitere'] ?? [])), fn($s) => $s !== $haupt && in_array($s, $erlaubt, true))));
        $alt = array_map('strval', (array) get_post_meta($post_id, 'ma_ad_slot_weitere'));
        sort($neu); sort($alt);
        if ($neu !== $alt) { delete_post_meta($post_id, 'ma_ad_slot_weitere'); foreach ($neu as $s) add_post_meta($post_id, 'ma_ad_slot_weitere', $s); }
    }
    if ($post->post_type === 'ma_ad' && function_exists('ma_werbeformat_anwenden') && ($format = (string) get_post_meta($post_id, 'ma_ad_format', true)) !== '' && $format !== $format_vorher) {
        // Neues Format: Plätze (für Firmen-Zugänge nur die des Kontingents) und Ende nach der Laufzeit.
        ma_werbeformat_anwenden($post_id, $format, $partner && function_exists('ma_ad_allowed_slots_for_current_user') ? ma_ad_allowed_slots_for_current_user() : []);
    }
    if ($post->post_type === 'ma_ad') {
        // Video: nur Videos aus der Mediathek; Partner nur eigene Uploads (Rechte wie beim Bild).
        $vid = (int) get_post_meta($post_id, 'ma_ad_video', true);
        if ($vid && (strpos((string) get_post_mime_type($vid), 'video/') !== 0 || ($partner && (int) get_post_field('post_author', $vid) !== (int) get_current_user_id()))) delete_post_meta($post_id, 'ma_ad_video');
        // Neues oder kein Video: die WebM-Fassung aus den Firmen-Entwürfen gehört zum alten und fällt weg.
        if ((string) get_post_meta($post_id, 'ma_ad_video', true) !== $video_vorher) delete_post_meta($post_id, 'ma_ad_video_webm');
    }
    if ($post->post_type === 'ma_ad' && isset($_POST['ma_ad_alt'])) {
        $bild = (int) get_post_thumbnail_id($post_id);
        $alt = sanitize_text_field(wp_unslash((string)$_POST['ma_ad_alt']));
        if ($bild && current_user_can('edit_post', $bild)) { $alt === '' ? delete_post_meta($bild, '_wp_attachment_image_alt') : update_post_meta($bild, '_wp_attachment_image_alt', $alt); }
    }
}

function ma_market_publication_gate(array $data,array $postarr): array {
    $gated=['ma_property','ma_job','ma_obituary','ma_family_notice','ma_tip'];
    if (!in_array($data['post_type']??'',$gated,true) || !in_array($data['post_status']??'',['publish','future'],true)) return $data;

    $post_id=(int)($postarr['ID']??0);
    $submitted=isset($_POST['ma_release_confirmed']);
    $stored=$post_id && get_post_meta($post_id,'ma_release_confirmed',true)==='1';
    if(!$submitted && !$stored) $data['post_status']='draft';
    return $data;
}
