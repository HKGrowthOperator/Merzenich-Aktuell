<?php
if (!defined('ABSPATH')) { exit; }
function ma_register_admin_hooks(): void {
    add_action('admin_menu', function () {
        add_menu_page('Merzenich Aktuell','Merzenich Aktuell','manage_options','merzenich-aktuell','ma_dashboard','dashicons-admin-site-alt3',3);
        add_submenu_page('merzenich-aktuell','Wetter','Wetter','manage_options','ma-weather','ma_weather_settings_page');
        add_submenu_page('merzenich-aktuell','Werbung','Werbung','manage_options','ma-ads','ma_ads_settings_page');
        add_submenu_page('merzenich-aktuell','Sport','Sport','manage_options','ma-sport','ma_sport_settings_page');
    });
}
function ma_dashboard(): void {
    $hero=new WP_Query(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>1,'orderby'=>'date','order'=>'DESC']);
    $old=false; if ($hero->posts) $old=(time()-get_post_time('U',true,$hero->posts[0]))>7*DAY_IN_SECONDS;
    $events=ma_upcoming_events(6); $weather=ma_get_weather();
    echo '<div class="wrap"><h1>Merzenich Aktuell – Aktualitätscheck</h1><table class="widefat striped"><tbody>';
    echo '<tr><th>Letzter News-Check</th><td>'.esc_html((string)get_option('ma_last_news_check','nicht dokumentiert')).'</td></tr>';
    echo '<tr><th>Hero älter als 7 Tage</th><td>'.($old?'<strong style="color:#b71920">JA – prüfen</strong>':'Nein').'</td></tr>';
    echo '<tr><th>Kommende Events</th><td>'.count($events).'</td></tr>';
    echo '<tr><th>Wetter</th><td>'.($weather?'OK · '.esc_html($weather['updated_at']??''):'Kein valider Datenstand – Frontend blendet Modul aus').'</td></tr>';
    echo '<tr><th>Beiträge ohne Bild</th><td>'.esc_html((string)ma_count_posts_without_thumbnail()).'</td></tr>';
    echo '<tr><th>Unreviewed Drafts</th><td>'.esc_html((string)ma_count_unreviewed()).'</td></tr>';
    echo '</tbody></table></div>';
}
function ma_count_posts_without_thumbnail(): int { global $wpdb; return (int)$wpdb->get_var("SELECT COUNT(p.ID) FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m ON p.ID=m.post_id AND m.meta_key='_thumbnail_id' WHERE p.post_type='post' AND p.post_status='publish' AND m.meta_id IS NULL"); }
function ma_count_unreviewed(): int { $q=new WP_Query(['post_type'=>'post','post_status'=>'draft','posts_per_page'=>1,'fields'=>'ids','meta_query'=>[['key'=>'ma_human_reviewed','value'=>'1','compare'=>'!=']]]); return (int)$q->found_posts; }
function ma_weather_settings_page(): void {
    if (!current_user_can('manage_options')) return;
    if (isset($_POST['ma_weather_save']) && check_admin_referer('ma_weather_save')) {
        update_option('ma_weather_settings',['enabled'=>isset($_POST['enabled'])?1:0,'latitude'=>sanitize_text_field($_POST['latitude']??''),'longitude'=>sanitize_text_field($_POST['longitude']??''),'cache_minutes'=>max(10,min(30,(int)($_POST['cache_minutes']??20)))]);
        delete_transient('ma_weather_current_v2'); echo '<div class="notice notice-success"><p>Gespeichert.</p></div>';
    }
    $s=wp_parse_args((array)get_option('ma_weather_settings',[]),['enabled'=>1,'latitude'=>'50.826813','longitude'=>'6.524935','cache_minutes'=>20]);
    echo '<div class="wrap"><h1>Wetter</h1><form method="post">'; wp_nonce_field('ma_weather_save');
    echo '<p><label><input type="checkbox" name="enabled" '.checked($s['enabled'],1,false).'> Wetter aktiv</label></p><p>Breite <input name="latitude" value="'.esc_attr($s['latitude']).'"> Länge <input name="longitude" value="'.esc_attr($s['longitude']).'"></p><p>Cache (10–30 Min.) <input type="number" min="10" max="30" name="cache_minutes" value="'.esc_attr($s['cache_minutes']).'"></p><p><button class="button button-primary" name="ma_weather_save">Speichern</button></p></form></div>';
}
function ma_ads_settings_page(): void {
    if (!current_user_can('manage_options')) return;
    if (isset($_POST['ma_ads_save']) && check_admin_referer('ma_ads_save')) { update_option('ma_ads_enabled',isset($_POST['global'])?1:0); $slots=[]; foreach(ma_ad_slots() as $s) $slots[$s]=isset($_POST['slot'][$s])?1:0; update_option('ma_ad_slots',$slots); echo '<div class="notice notice-success"><p>Gespeichert.</p></div>'; }
    $enabled=(int)get_option('ma_ads_enabled',0); $slots=(array)get_option('ma_ad_slots',[]);
    echo '<div class="wrap"><h1>Werbung</h1><p>Werbemittel selbst unter <strong>Werbung</strong> anlegen. Ohne aktive Kampagne wird kein leerer Platz ausgegeben.</p><form method="post">'; wp_nonce_field('ma_ads_save'); echo '<p><label><input type="checkbox" name="global" '.checked($enabled,1,false).'> Werbung global AN</label></p>';
    foreach(ma_ad_slots() as $s) echo '<p><label><input type="checkbox" name="slot['.esc_attr($s).']" '.checked($slots[$s]??0,1,false).'> '.esc_html($s).'</label></p>';
    echo '<p><button class="button button-primary" name="ma_ads_save">Speichern</button></p></form></div>';
}
function ma_sport_settings_page(): void {
    if (!current_user_can('manage_options')) return;
    if (isset($_POST['ma_sport_save']) && check_admin_referer('ma_sport_save')) {
        $json=json_decode(wp_unslash($_POST['sport_json']??''),true); if (is_array($json)) { update_option('ma_sport_data',$json); echo '<div class="notice notice-success"><p>Sportdaten gespeichert.</p></div>'; } else echo '<div class="notice notice-error"><p>Ungültiges JSON.</p></div>';
    }
    $v=wp_json_encode(ma_sport_data(),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    echo '<div class="wrap"><h1>Sport</h1><p>Ergebnis, nächstes Spiel und Tabelle immer gemeinsam aktualisieren. Unbestätigte Ergebnisse in <code>pending_match</code> führen.</p><form method="post">'; wp_nonce_field('ma_sport_save'); echo '<textarea name="sport_json" rows="28" style="width:100%;font-family:monospace">'.esc_textarea($v).'</textarea><p><button class="button button-primary" name="ma_sport_save">Speichern</button></p></form></div>';
}
