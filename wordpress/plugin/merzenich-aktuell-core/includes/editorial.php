<?php
if (!defined('ABSPATH')) { exit; }

function ma_register_editorial_hooks(): void {
    add_action('add_meta_boxes', 'ma_editorial_boxes');
    add_action('save_post', 'ma_save_editorial_meta');
    add_filter('wp_insert_post_data','ma_publication_gate',20,2);
}

function ma_editorial_boxes(): void {
    add_meta_box('ma_editorial','Redaktion & Quelle','ma_editorial_box','post','normal','high');
    add_meta_box('ma_ai','KI & redaktionelle Transparenz','ma_ai_box','post','side','default');
}

function ma_field(string $label,string $name,string $type='text',string $description=''): void {
    $value=(string)get_post_meta(get_the_ID(),$name,true);
    if ($type==='datetime-local' && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/',$value)) $value=str_replace(' ','T',substr($value,0,16));
    $v=esc_attr($value);
    echo '<p><label><strong>'.esc_html($label).'</strong><br><input style="width:100%" type="'.esc_attr($type).'" name="'.esc_attr($name).'" value="'.$v.'"></label>';
    if($description!=='') echo '<br><span class="description">'.esc_html($description).'</span>';
    echo '</p>';
}

function ma_check(string $label,string $name): void {
    $v=(string)get_post_meta(get_the_ID(),$name,true);
    echo '<p><label><input type="checkbox" name="'.esc_attr($name).'" value="1" '.checked($v,'1',false).'> '.esc_html($label).'</label></p>';
}

function ma_editorial_box(): void {
    wp_nonce_field('ma_editorial_save','ma_editorial_nonce');
    ma_field('Original Source URL','ma_source_url','url');
    ma_field('Source Publisher','ma_source_publisher');
    ma_field('Source Published At','ma_source_published_at','datetime-local');
    ma_field('Last Checked At','ma_source_checked_at','datetime-local');
    ma_field('Redaktionelle Priorität (0–100)','ma_editorial_priority','number','Beeinflusst zusammen mit Aktualität und lokaler Relevanz die automatische Homepage-Priorisierung.');
    ma_field('Aufmacher fixiert bis','ma_top_until','datetime-local','Nur relevant, wenn „Top Story fixiert“ aktiviert ist. Danach soll die Fixierung auslaufen.');
    ma_field('Bildcredit','ma_image_credit');
    ma_field('Bildlizenz / Freigabe','ma_image_license');
    ma_field('Original-Bild-URL','ma_image_original_url','url');
    ma_check('Quelle geprüft','ma_source_verified');
    ma_check('Datum geprüft','ma_date_verified');
    ma_check('Ort geprüft','ma_place_verified');
    ma_check('Bildrechte geprüft','ma_image_rights_verified');
    ma_check('Human Review abgeschlossen','ma_human_reviewed');
    ma_check('Top Story bewusst fixieren','ma_top_pinned');
}

function ma_ai_box(): void {
    ma_check('KI wurde eingesetzt','ma_ai_used');
    ma_field('Art der KI-Nutzung','ma_ai_use_type');
    ma_check('KI-generiertes Bild','ma_ai_generated_image');
    ma_check('KI-manipuliertes Bild','ma_ai_manipulated_image');
    ma_field('Geprüft von','ma_reviewed_by');
    ma_field('Geprüft am','ma_reviewed_at','datetime-local');
    ma_field('Redaktionelle Verantwortung','ma_editorial_responsibility');
}

function ma_editorial_datetime_value($value): string {
    $value=is_string($value)?wp_unslash($value):'';
    if ($value==='') return '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/',$value)) return '';
    return str_replace('T',' ',$value).':00';
}

function ma_save_editorial_meta(int $post_id): void {
    if (get_post_type($post_id)!=='post') return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if (!isset($_POST['ma_editorial_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_editorial_nonce'])),'ma_editorial_save')) return;
    if (!current_user_can('edit_post',$post_id)) return;

    $text=['ma_source_publisher','ma_image_credit','ma_image_license','ma_ai_use_type','ma_reviewed_by','ma_editorial_responsibility'];
    $url=['ma_source_url','ma_image_original_url'];
    $datetime=['ma_source_published_at','ma_source_checked_at','ma_top_until','ma_reviewed_at'];
    $number=['ma_editorial_priority'];
    $checks=['ma_source_verified','ma_date_verified','ma_place_verified','ma_image_rights_verified','ma_human_reviewed','ma_top_pinned','ma_ai_used','ma_ai_generated_image','ma_ai_manipulated_image'];

    foreach($text as $k){$v=isset($_POST[$k])?sanitize_text_field(wp_unslash($_POST[$k])):'';$v===''?delete_post_meta($post_id,$k):update_post_meta($post_id,$k,$v);}
    foreach($url as $k){$v=isset($_POST[$k])?esc_url_raw(wp_unslash($_POST[$k])):'';$v===''?delete_post_meta($post_id,$k):update_post_meta($post_id,$k,$v);}
    foreach($datetime as $k){$v=isset($_POST[$k])?ma_editorial_datetime_value($_POST[$k]):'';$v===''?delete_post_meta($post_id,$k):update_post_meta($post_id,$k,$v);}
    foreach($number as $k){$v=isset($_POST[$k])?max(0,min(100,(int)$_POST[$k])):0;update_post_meta($post_id,$k,(string)$v);}
    foreach($checks as $k) update_post_meta($post_id,$k,isset($_POST[$k])?'1':'0');
}

function ma_publication_gate(array $data,array $postarr): array {
    if (($data['post_type']??'')!=='post' || ($data['post_status']??'')!=='publish') return $data;
    $post_id=(int)($postarr['ID']??0);
    if(!$post_id) return $data;

    $required=['ma_source_verified','ma_date_verified','ma_place_verified','ma_human_reviewed'];
    foreach($required as $key){
        $submitted=isset($_POST[$key]);
        $stored=get_post_meta($post_id,$key,true)==='1';
        if(!$submitted && !$stored){$data['post_status']='draft';return $data;}
    }

    if(has_post_thumbnail($post_id)){
        $rights_submitted=isset($_POST['ma_image_rights_verified']);
        $rights_stored=get_post_meta($post_id,'ma_image_rights_verified',true)==='1';
        if(!$rights_submitted && !$rights_stored) $data['post_status']='draft';
    }
    return $data;
}
