<?php
if (!defined('ABSPATH')) { exit; }

function ma_register_content_types(): void {
    register_taxonomy('ma_location', ['post','ma_event','ma_property','ma_job','ma_obituary','ma_family_notice','ma_club','ma_business','ma_tip'], [
        'labels' => ['name'=>'Orte','singular_name'=>'Ort'],
        'public'=>true,
        'hierarchical'=>true,
        'show_in_rest'=>true,
        'rewrite'=>['slug'=>'ort'],
    ]);

    $types = [
        'ma_event' => ['Veranstaltungen','Veranstaltung','veranstaltungen','dashicons-calendar-alt'],
        'ma_property' => ['Immobilien','Immobilie','immobilien','dashicons-building'],
        'ma_job' => ['Stellen','Stelle','stellen','dashicons-businessperson'],
        'ma_obituary' => ['Traueranzeigen','Traueranzeige','traueranzeigen','dashicons-heart'],
        'ma_family_notice' => ['Familienanzeigen','Familienanzeige','familienanzeigen','dashicons-groups'],
        'ma_club' => ['Vereine','Verein','vereine','dashicons-groups'],
        // Beschriftung "Unternehmen" wie in der Navigation; die Adresse
        // /betriebe/ bleibt, damit bestehende Links weiter funktionieren.
        'ma_business' => ['Unternehmen','Unternehmen','betriebe','dashicons-store'],
        'ma_tip' => ['Tipps','Tipp','tipp','dashicons-star-filled'],
        'ma_ad' => ['Werbung','Werbemittel','werbung','dashicons-megaphone'],
    ];

    foreach ($types as $slug => $d) {
        register_post_type($slug, [
            'labels'=>[
                'name'=>$d[0],
                'singular_name'=>$d[1],
                'add_new_item'=>$d[1].' hinzufügen',
                'edit_item'=>$d[1].' bearbeiten',
            ],
            'public'=>$slug !== 'ma_ad',
            'show_ui'=>true,
            'show_in_rest'=>true,
            'has_archive'=>$slug !== 'ma_ad' ? $d[2] : false,
            'rewrite'=>$slug !== 'ma_ad' ? ['slug'=>$d[2]] : false,
            'menu_icon'=>$d[3],
            'supports'=>['title','editor','excerpt','thumbnail','author'],
        ]);
    }

    register_taxonomy('ma_family_type','ma_family_notice',[
        'labels'=>['name'=>'Anlass','singular_name'=>'Anlass'],
        'public'=>true,
        'hierarchical'=>true,
        'show_in_rest'=>true,
    ]);
}
add_action('init','ma_register_content_types');

function ma_meta(string $key, int $post_id = 0, $default = '') {
    $post_id = $post_id ?: get_the_ID();
    $value = get_post_meta($post_id, $key, true);
    return $value === '' ? $default : $value;
}

function ma_event_timestamp(int $post_id, string $kind='start'): int {
    $key = $kind === 'end' ? 'ma_event_end' : 'ma_event_start';
    $v = (string)get_post_meta($post_id, $key, true);
    if (!$v && $kind === 'end') $v = (string)get_post_meta($post_id, 'ma_event_start', true);
    if (!$v) return 0;

    try {
        return (new DateTimeImmutable($v, wp_timezone()))->getTimestamp();
    } catch (Exception $e) {
        return 0;
    }
}

function ma_upcoming_events(int $limit=6): array {
    $now = current_time('timestamp');
    $q = new WP_Query([
        'post_type'=>'ma_event',
        'post_status'=>'publish',
        'posts_per_page'=>50,
        'meta_key'=>'ma_event_start',
        'orderby'=>'meta_value',
        'order'=>'ASC',
    ]);

    $out=[];
    foreach ($q->posts as $p) {
        $end=ma_event_timestamp($p->ID,'end');
        if ($end && $end < $now) continue;
        $out[]=$p;
        if (count($out) >= $limit) break;
    }
    return $out;
}

function ma_active_market_items(string $type, int $limit=3): array {
    $allowed=['ma_property','ma_job','ma_obituary','ma_family_notice','ma_tip'];
    if (!in_array($type,$allowed,true)) return [];

    $now=current_time('Y-m-d H:i:s');
    $q=new WP_Query([
        'post_type'=>$type,
        'post_status'=>'publish',
        'posts_per_page'=>$limit,
        'orderby'=>'date',
        'order'=>'DESC',
        'meta_query'=>[
            'relation'=>'AND',
            ['key'=>'ma_release_confirmed','value'=>'1','compare'=>'='],
            [
                'relation'=>'OR',
                ['key'=>'ma_end_at','compare'=>'NOT EXISTS'],
                ['key'=>'ma_end_at','value'=>'','compare'=>'='],
                ['key'=>'ma_end_at','value'=>$now,'compare'=>'>=','type'=>'DATETIME'],
            ],
        ],
    ]);
    return $q->posts;
}

function ma_filter_public_service_archives(WP_Query $query): void {
    if (is_admin() || !$query->is_main_query()) return;

    $post_type=$query->get('post_type');
    if (is_array($post_type)) return;

    if (in_array($post_type,['ma_property','ma_job','ma_obituary','ma_family_notice','ma_tip'],true) || $query->is_post_type_archive(['ma_property','ma_job','ma_obituary','ma_family_notice','ma_tip'])) {
        $now=current_time('Y-m-d H:i:s');
        $query->set('meta_query',[
            'relation'=>'AND',
            ['key'=>'ma_release_confirmed','value'=>'1','compare'=>'='],
            [
                'relation'=>'OR',
                ['key'=>'ma_end_at','compare'=>'NOT EXISTS'],
                ['key'=>'ma_end_at','value'=>'','compare'=>'='],
                ['key'=>'ma_end_at','value'=>$now,'compare'=>'>=','type'=>'DATETIME'],
            ],
        ]);
        $query->set('orderby','date');
        $query->set('order','DESC');
        return;
    }

    if ($post_type==='ma_event' || $query->is_post_type_archive('ma_event')) {
        $now=current_time('Y-m-d H:i:s');
        $query->set('posts_per_page',20);
        $query->set('meta_key','ma_event_start');
        $query->set('orderby','meta_value');
        $query->set('order','ASC');
        $query->set('meta_query',[
            'relation'=>'OR',
            ['key'=>'ma_event_end','value'=>$now,'compare'=>'>=','type'=>'DATETIME'],
            [
                'relation'=>'AND',
                ['key'=>'ma_event_end','compare'=>'NOT EXISTS'],
                ['key'=>'ma_event_start','value'=>$now,'compare'=>'>=','type'=>'DATETIME'],
            ],
            [
                'relation'=>'AND',
                ['key'=>'ma_event_end','value'=>'','compare'=>'='],
                ['key'=>'ma_event_start','value'=>$now,'compare'=>'>=','type'=>'DATETIME'],
            ],
        ]);
    }
}
add_action('pre_get_posts','ma_filter_public_service_archives');

add_action('wp_head', function () {
    if (is_singular(['ma_obituary','ma_family_notice'])) {
        echo "<meta name=\"robots\" content=\"noindex,follow\">\n";
    }
}, 1);
