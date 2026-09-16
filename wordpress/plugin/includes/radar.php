<?php
if (!defined('ABSPATH')) { exit; }
function ma_register_radar_hooks(): void {
    add_action('init', function(){
        register_post_type('ma_source_item',[
            'labels'=>['name'=>'Quellen / News Radar','singular_name'=>'Fundstück'],
            'public'=>false,'show_ui'=>true,'show_in_menu'=>'merzenich-aktuell','supports'=>['title','editor','custom-fields'],'menu_icon'=>'dashicons-rss',
        ]);
        register_taxonomy('ma_source_status','ma_source_item',['labels'=>['name'=>'Radar-Status','singular_name'=>'Status'],'public'=>false,'show_ui'=>true,'hierarchical'=>false]);
    },20);
    add_action('add_meta_boxes',function(){add_meta_box('ma_source_meta','Fundstück','ma_radar_box','ma_source_item','normal','high');});
    add_action('save_post_ma_source_item','ma_radar_save');
}
function ma_radar_box(): void {
    wp_nonce_field('ma_radar_save','ma_radar_nonce');
    foreach(['ma_radar_source'=>'Quelle','ma_radar_url'=>'Original-URL','ma_radar_published'=>'Veröffentlicht am','ma_radar_last_checked'=>'Zuletzt geprüft','ma_radar_location'=>'Ort'] as $k=>$label){$v=esc_attr((string)get_post_meta(get_the_ID(),$k,true));echo '<p><label><strong>'.esc_html($label).'</strong><br><input style="width:100%" name="'.esc_attr($k).'" value="'.$v.'"></label></p>';}
    echo '<p>Workflow: <strong>Neu → Prüfen → Übernommen / Ignoriert / Duplikat</strong>. Ein Fundstück wird nie automatisch zum veröffentlichten Artikel.</p>';
}
function ma_radar_save(int $id): void {
    if(!isset($_POST['ma_radar_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_radar_nonce'])),'ma_radar_save')||!current_user_can('edit_post',$id)) return;
    foreach(['ma_radar_source','ma_radar_published','ma_radar_last_checked','ma_radar_location'] as $k) if(isset($_POST[$k])) update_post_meta($id,$k,sanitize_text_field(wp_unslash($_POST[$k])));
    if(isset($_POST['ma_radar_url'])) update_post_meta($id,'ma_radar_url',esc_url_raw(wp_unslash($_POST['ma_radar_url'])));
}
