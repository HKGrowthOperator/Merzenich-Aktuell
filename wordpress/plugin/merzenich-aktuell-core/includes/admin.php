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
    $hero = function_exists('ma_theme_hero_post') ? ma_theme_hero_post() : null;
    if (!$hero) {
        $latest = new WP_Query(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>1,'orderby'=>'date','order'=>'DESC']);
        $hero = $latest->posts[0] ?? null;
    }
    $old = $hero ? (time() - get_post_time('U', true, $hero)) > 7 * DAY_IN_SECONDS : false;
    $events = ma_upcoming_events(6);
    $weather = ma_get_weather();
    $pending_comments = (int)get_comments(['status'=>'hold','count'=>true]);
    $partner_q = new WP_Query([
        'post_type'=>['post','ma_property','ma_business','ma_ad'],
        'post_status'=>'pending','posts_per_page'=>1,'fields'=>'ids',
        'meta_query'=>[['key'=>'_ma_partner_submission','value'=>'1']],
    ]);
    $pending_partner = (int)$partner_q->found_posts;

    echo '<div class="wrap"><h1>Merzenich Aktuell – Aktualitätscheck</h1><table class="widefat striped"><tbody>';
    echo '<tr><th>Letzter News-Check</th><td>'.esc_html((string)get_option('ma_last_news_check','nicht dokumentiert')).'</td></tr>';
    echo '<tr><th>Aktueller Hero</th><td>'.($hero ? esc_html(get_the_title($hero)) : 'Kein veröffentlichter Beitrag').'</td></tr>';
    echo '<tr><th>Hero älter als 7 Tage</th><td>'.($old?'<strong style="color:#b71920">JA – prüfen</strong>':'Nein').'</td></tr>';
    echo '<tr><th>Kommende Events</th><td>'.count($events).'</td></tr>';
    echo '<tr><th>Wetter</th><td>'.($weather?'OK · '.esc_html($weather['updated_at']??''):'Kein valider Datenstand – Frontend blendet Modul aus').'</td></tr>';
    echo '<tr><th>Beiträge ohne Bild</th><td>'.esc_html((string)ma_count_posts_without_thumbnail()).'</td></tr>';
    echo '<tr><th>Unreviewed Drafts</th><td>'.esc_html((string)ma_count_unreviewed()).'</td></tr>';
    echo '<tr><th>Partner-Einreichungen</th><td><strong>'.esc_html((string)$pending_partner).'</strong> · <a href="'.esc_url(admin_url('users.php?page=ma-partner-zugaenge')).'">prüfen</a></td></tr>';
    echo '<tr><th>Kommentare zur Freigabe</th><td><strong>'.esc_html((string)$pending_comments).'</strong> · <a href="'.esc_url(admin_url('edit-comments.php?page=ma-kommentar-freigabe')).'">Sammelfreigabe</a></td></tr>';
    echo '</tbody></table></div>';
}

function ma_count_posts_without_thumbnail(): int {
    global $wpdb;
    return (int)$wpdb->get_var("SELECT COUNT(p.ID) FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m ON p.ID=m.post_id AND m.meta_key='_thumbnail_id' WHERE p.post_type='post' AND p.post_status='publish' AND m.meta_id IS NULL");
}

function ma_count_unreviewed(): int {
    $q = new WP_Query([
        'post_type'=>'post',
        'post_status'=>'draft',
        'posts_per_page'=>1,
        'fields'=>'ids',
        'meta_query'=>[
            'relation'=>'OR',
            ['key'=>'ma_human_reviewed','compare'=>'NOT EXISTS'],
            ['key'=>'ma_human_reviewed','value'=>'1','compare'=>'!='],
        ],
    ]);
    return (int)$q->found_posts;
}

function ma_weather_settings_page(): void {
    if (!current_user_can('manage_options')) return;
    if (isset($_POST['ma_weather_save']) && check_admin_referer('ma_weather_save')) {
        update_option('ma_weather_settings',[
            'enabled'=>isset($_POST['enabled'])?1:0,
            'latitude'=>sanitize_text_field(wp_unslash($_POST['latitude']??'')),
            'longitude'=>sanitize_text_field(wp_unslash($_POST['longitude']??'')),
            'cache_minutes'=>max(10,min(30,(int)($_POST['cache_minutes']??20))),
        ]);
        delete_transient('ma_weather_current_v2');
        echo '<div class="notice notice-success"><p>Gespeichert.</p></div>';
    }
    $s=wp_parse_args((array)get_option('ma_weather_settings',[]),['enabled'=>1,'latitude'=>'50.826813','longitude'=>'6.524935','cache_minutes'=>20]);
    echo '<div class="wrap"><h1>Wetter</h1><form method="post">'; wp_nonce_field('ma_weather_save');
    echo '<p><label><input type="checkbox" name="enabled" '.checked($s['enabled'],1,false).'> Wetter aktiv</label></p><p>Breite <input name="latitude" value="'.esc_attr($s['latitude']).'"> Länge <input name="longitude" value="'.esc_attr($s['longitude']).'"></p><p>Cache (10–30 Min.) <input type="number" min="10" max="30" name="cache_minutes" value="'.esc_attr($s['cache_minutes']).'"></p><p><button class="button button-primary" name="ma_weather_save">Speichern</button></p></form></div>';
}

function ma_ads_settings_page(): void {
    if (!current_user_can('manage_options')) return;
    if (isset($_POST['ma_ads_save']) && check_admin_referer('ma_ads_save')) {
        update_option('ma_ads_enabled',isset($_POST['global'])?1:0);
        $slots=[];
        foreach(ma_ad_slots() as $s) $slots[$s]=isset($_POST['slot'][$s])?1:0;
        update_option('ma_ad_slots',$slots);
        echo '<div class="notice notice-success"><p>Gespeichert.</p></div>';
    }
    $enabled=(int)get_option('ma_ads_enabled',0);
    $slots=(array)get_option('ma_ad_slots',[]);
    echo '<div class="wrap"><h1>Werbung</h1><p>Werbemittel selbst unter <strong>Werbung</strong> anlegen. Ohne aktive Kampagne wird kein leerer Platz ausgegeben.</p><form method="post">';
    wp_nonce_field('ma_ads_save');
    echo '<p><label><input type="checkbox" name="global" '.checked($enabled,1,false).'> Werbung global AN</label></p>';
    foreach(ma_ad_slots() as $s) echo '<p><label><input type="checkbox" name="slot['.esc_attr($s).']" '.checked($slots[$s]??0,1,false).'> '.esc_html($s).'</label></p>';
    echo '<p><button class="button button-primary" name="ma_ads_save">Speichern</button></p></form></div>';
}

function ma_sport_match_from_request(string $prefix, bool $with_score=false): array {
    $home = sanitize_text_field(wp_unslash($_POST[$prefix.'_home'] ?? ''));
    $away = sanitize_text_field(wp_unslash($_POST[$prefix.'_away'] ?? ''));
    $date = sanitize_text_field(wp_unslash($_POST[$prefix.'_date'] ?? ''));
    $score = $with_score ? sanitize_text_field(wp_unslash($_POST[$prefix.'_score'] ?? '')) : '';

    if ($home === '' && $away === '' && $date === '' && $score === '') return [];

    $match = ['home'=>$home,'away'=>$away,'date'=>$date];
    if ($with_score) $match['score']=$score;
    return $match;
}

function ma_sport_table_from_request(): array {
    $teams = isset($_POST['table_team']) && is_array($_POST['table_team']) ? $_POST['table_team'] : [];
    $ranks = isset($_POST['table_rank']) && is_array($_POST['table_rank']) ? $_POST['table_rank'] : [];
    $played = isset($_POST['table_played']) && is_array($_POST['table_played']) ? $_POST['table_played'] : [];
    $points = isset($_POST['table_points']) && is_array($_POST['table_points']) ? $_POST['table_points'] : [];
    $goals = isset($_POST['table_goals']) && is_array($_POST['table_goals']) ? $_POST['table_goals'] : [];
    $table = [];

    for ($i=0; $i<16; $i++) {
        $team = sanitize_text_field(wp_unslash($teams[$i] ?? ''));
        if ($team === '') continue;
        $table[] = [
            'rank'=>sanitize_text_field(wp_unslash($ranks[$i] ?? '')),
            'team'=>$team,
            'played'=>sanitize_text_field(wp_unslash($played[$i] ?? '')),
            'points'=>sanitize_text_field(wp_unslash($points[$i] ?? '')),
            'goals'=>sanitize_text_field(wp_unslash($goals[$i] ?? '')),
        ];
    }
    return $table;
}

function ma_sport_settings_page(): void {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['ma_sport_import']) && check_admin_referer('ma_sport_save')) {
        $ergebnis = ma_sport_import_from_json((string)wp_unslash($_POST['sport_json'] ?? ''));
        if (is_wp_error($ergebnis)) {
            echo '<div class="notice notice-error"><p>Import fehlgeschlagen: '.esc_html($ergebnis->get_error_message()).'</p></div>';
        } else {
            update_option('ma_sport_data',$ergebnis);
            echo '<div class="notice notice-success"><p>Datenstand aus JSON übernommen ('.esc_html($ergebnis['checked_at']).'). Unten prüfen und bei Bedarf korrigieren.</p></div>';
        }
    }

    if (isset($_POST['ma_sport_save']) && check_admin_referer('ma_sport_save')) {
        $data = [
            'checked_at'=>sanitize_text_field(wp_unslash($_POST['checked_at'] ?? '')),
            'source_url'=>esc_url_raw(wp_unslash($_POST['source_url'] ?? '')),
            'last_match'=>ma_sport_match_from_request('last_match', true),
            'pending_match'=>ma_sport_match_from_request('pending_match', false),
            'next_match'=>ma_sport_match_from_request('next_match', false),
            'table'=>ma_sport_table_from_request(),
        ];
        update_option('ma_sport_data',$data);
        echo '<div class="notice notice-success"><p>Sportdaten gespeichert.</p></div>';
    }

    $d = ma_sport_data();
    $last = (array)($d['last_match'] ?? []);
    $pending = (array)($d['pending_match'] ?? []);
    $next = (array)($d['next_match'] ?? []);
    $table = array_values((array)($d['table'] ?? []));

    echo '<div class="wrap"><h1>Sport</h1><p>Ergebnis, offenes Spiel, nächstes Spiel und Tabelle werden hier gemeinsam gepflegt. Keine JSON-Eingabe erforderlich.</p><form method="post">';
    wp_nonce_field('ma_sport_save');

    echo '<h2>Aus JSON übernehmen</h2><p>Denselben Datenstand, den die statische Seite unter <code>/api/sport-current.json</code> ausliefert, hier einfügen. Ergebnis, offenes Spiel, nächstes Spiel und Tabelle werden daraus gefüllt; ein unbestätigtes Ergebnis landet als offenes Spiel.</p>';
    echo '<textarea name="sport_json" rows="6" style="width:100%;max-width:900px;font-family:monospace" placeholder=\'{"generated":"…","sourceUrl":"…","lastMatch":{…},"nextMatch":{…},"table":[…]}\'></textarea>';
    echo '<p><button class="button" name="ma_sport_import">Aus JSON übernehmen</button></p>';

    echo '<h2>Gemeinsamer Datenstand</h2><table class="form-table"><tr><th><label for="checked_at">Datenstand</label></th><td><input class="regular-text" id="checked_at" name="checked_at" value="'.esc_attr((string)($d['checked_at'] ?? '')).'" placeholder="16.09.2026 · 11:30 Uhr"></td></tr><tr><th><label for="source_url">Quelle</label></th><td><input class="regular-text" type="url" id="source_url" name="source_url" value="'.esc_attr((string)($d['source_url'] ?? '')).'" placeholder="https://..."></td></tr></table>';

    $sections = [
        'last_match'=>['Letztes bestätigtes Spiel',$last,true],
        'pending_match'=>['Noch nicht bestätigtes Spiel',$pending,false],
        'next_match'=>['Nächstes Spiel',$next,false],
    ];

    foreach ($sections as $prefix=>$config) {
        [$label,$match,$with_score] = $config;
        echo '<h2>'.esc_html($label).'</h2><table class="form-table">';
        echo '<tr><th>Heim</th><td><input class="regular-text" name="'.esc_attr($prefix).'_home" value="'.esc_attr((string)($match['home'] ?? '')).'"></td></tr>';
        if ($with_score) echo '<tr><th>Ergebnis</th><td><input class="regular-text" name="'.esc_attr($prefix).'_score" value="'.esc_attr((string)($match['score'] ?? '')).'" placeholder="2 : 1"></td></tr>';
        echo '<tr><th>Gast</th><td><input class="regular-text" name="'.esc_attr($prefix).'_away" value="'.esc_attr((string)($match['away'] ?? '')).'"></td></tr>';
        echo '<tr><th>Datum / Uhrzeit</th><td><input class="regular-text" name="'.esc_attr($prefix).'_date" value="'.esc_attr((string)($match['date'] ?? '')).'" placeholder="18.09.2026 · 19:30 Uhr"></td></tr>';
        echo '</table>';
    }

    echo '<h2>Tabelle</h2><p>Nur Zeilen mit Mannschaft werden gespeichert.</p><div style="overflow:auto"><table class="widefat striped" style="max-width:900px"><thead><tr><th style="width:70px">Pl.</th><th>Mannschaft</th><th style="width:80px">Sp.</th><th style="width:80px">Pkt.</th><th style="width:120px">Tore</th></tr></thead><tbody>';
    for ($i=0; $i<16; $i++) {
        $row = (array)($table[$i] ?? []);
        echo '<tr>';
        echo '<td><input style="width:55px" name="table_rank['.$i.']" value="'.esc_attr((string)($row['rank'] ?? '')).'"></td>';
        echo '<td><input style="width:100%" name="table_team['.$i.']" value="'.esc_attr((string)($row['team'] ?? '')).'"></td>';
        echo '<td><input style="width:65px" name="table_played['.$i.']" value="'.esc_attr((string)($row['played'] ?? '')).'"></td>';
        echo '<td><input style="width:65px" name="table_points['.$i.']" value="'.esc_attr((string)($row['points'] ?? '')).'"></td>';
        echo '<td><input style="width:100px" name="table_goals['.$i.']" value="'.esc_attr((string)($row['goals'] ?? '')).'" placeholder="12:8"></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';

    echo '<p style="margin-top:20px"><button class="button button-primary" name="ma_sport_save">Sportdaten speichern</button></p></form></div>';
}
