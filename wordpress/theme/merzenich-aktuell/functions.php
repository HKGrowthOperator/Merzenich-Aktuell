<?php
if (!defined('ABSPATH')) { exit; }

add_action('after_setup_theme', function(){
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5',['search-form','comment-form','comment-list','gallery','caption','style','script']);
    register_nav_menus(['primary'=>'Hauptnavigation (Ressorts)']);
});

add_action('wp_enqueue_scripts', function(){
    $version=wp_get_theme()->get('Version');
    wp_enqueue_style('ma-style',get_stylesheet_uri(),[],$version);
    wp_enqueue_style('ma-service',get_template_directory_uri().'/assets/css/service.css',['ma-style'],$version);
    wp_enqueue_script('ma-site',get_template_directory_uri().'/assets/js/site.js',[],$version,true);
});

function ma_theme_location_label(int $id=0): string {
    $id=$id?:get_the_ID();
    $terms=get_the_terms($id,'ma_location');
    if (!$terms || is_wp_error($terms)) $terms=get_the_terms($id,'ma_district');
    if (!$terms || is_wp_error($terms)) return '<span class="place-primary">MERZENICH</span>';

    $t=array_shift($terms);
    $slug=strtolower((string)$t->slug);
    $name=strtoupper(esc_html($t->name));
    $municipality_districts=['golzheim','girbelsrath','morschenich','buergewald','bürgewald'];

    if ($slug==='merzenich') return '<span class="place-primary">MERZENICH</span>';
    if (in_array($slug,$municipality_districts,true)) return '<span class="place-primary">MERZENICH</span> · <span class="place-secondary">'.$name.'</span>';
    return '<span class="place-secondary">'.$name.'</span>';
}

function ma_theme_primary_nav(): void {
    $fallback=[
        'Aktuell'=>'/category/nachrichten/',
        'Blaulicht'=>'/category/blaulicht/',
        'Sport'=>'/sport/',
        'Termine'=>'/veranstaltungen/',
        'Vereine'=>'/vereine/',
        'Rathaus & Politik'=>'/category/rathaus/',
        'Leben'=>'/category/leben/',
        'Wirtschaft'=>'/category/wirtschaft/',
        'Menschen'=>'/category/menschen/',
    ];
    if (has_nav_menu('primary')) {
        wp_nav_menu(['theme_location'=>'primary','container'=>false,'menu_class'=>'','fallback_cb'=>false]);
        return;
    }
    echo '<ul>';
    foreach($fallback as $label=>$url) echo '<li><a href="'.esc_url(home_url($url)).'">'.esc_html($label).'</a></li>';
    echo '</ul>';
}

function ma_theme_is_local_post(int $post_id): bool {
    $local_slugs=['merzenich','golzheim','girbelsrath','morschenich','buergewald','bürgewald'];
    foreach($local_slugs as $slug) if(has_term($slug,'ma_location',$post_id)) return true;
    return false;
}

function ma_theme_hero_post(): ?WP_Post {
    $now=current_time('Y-m-d H:i:s');
    $pinned=new WP_Query([
        'post_type'=>'post','post_status'=>'publish','posts_per_page'=>1,'orderby'=>'date','order'=>'DESC',
        'meta_query'=>[
            'relation'=>'AND',
            ['key'=>'ma_top_pinned','value'=>'1'],
            [
                'relation'=>'OR',
                ['key'=>'ma_top_until','compare'=>'NOT EXISTS'],
                ['key'=>'ma_top_until','value'=>'','compare'=>'='],
                ['key'=>'ma_top_until','value'=>$now,'compare'=>'>=','type'=>'DATETIME'],
            ],
        ],
    ]);
    if($pinned->posts) return $pinned->posts[0];

    $since=gmdate('Y-m-d H:i:s',time()-7*DAY_IN_SECONDS);
    $q=new WP_Query([
        'post_type'=>'post','post_status'=>'publish','posts_per_page'=>20,
        'date_query'=>[['after'=>$since,'inclusive'=>true]],'orderby'=>'date','order'=>'DESC',
    ]);
    if(!$q->posts) return null;

    $best=null;$best_score=-INF;$now_ts=time();
    foreach($q->posts as $post){
        $age=max(0,($now_ts-get_post_time('U',true,$post))/DAY_IN_SECONDS);
        $fresh=max(0,100-($age/7*100));
        $local=ma_theme_is_local_post((int)$post->ID)?100:75;
        $priority=max(0,min(100,(int)get_post_meta($post->ID,'ma_editorial_priority',true)));
        $score=.4*$fresh+.3*$local+.3*$priority;
        if($score>$best_score){$best=$post;$best_score=$score;}
    }
    return $best;
}

function ma_theme_reading_time(int $id=0): int {
    $id=$id?:get_the_ID();
    $words=str_word_count(wp_strip_all_tags((string)get_post_field('post_content',$id)));
    return max(1,(int)ceil($words/220));
}

function ma_theme_ad(string $slot): void {
    if(function_exists('ma_render_ad')) echo ma_render_ad($slot);
}

function ma_theme_family_kind_label(string $kind): string {
    $labels=[
        'birth'=>'Geburt','wedding'=>'Hochzeit','anniversary'=>'Jubiläum',
        'thanks'=>'Danksagung','congratulations'=>'Glückwunsch','other'=>'Familienanzeige',
    ];
    return $labels[$kind]??'Familienanzeige';
}

function ma_theme_job_type_label(string $type): string {
    $labels=[
        'fulltime'=>'Vollzeit','parttime'=>'Teilzeit','minijob'=>'Minijob','training'=>'Ausbildung',
        'internship'=>'Praktikum','freelance'=>'Freie Mitarbeit',
    ];
    return $labels[$type]??'';
}

function ma_theme_market_meta(int $post_id=0): array {
    $post_id=$post_id?:get_the_ID();
    $type=get_post_type($post_id);
    $items=[];

    if($type==='ma_property'){
        foreach(['ma_property_price','ma_property_rooms','ma_property_area'] as $key){
            $value=(string)get_post_meta($post_id,$key,true);
            if($value==='') continue;
            if($key==='ma_property_rooms') $value.=' Zimmer';
            $items[]=$value;
        }
    } elseif($type==='ma_job'){
        $company=(string)get_post_meta($post_id,'ma_job_company',true);
        $job_type=ma_theme_job_type_label((string)get_post_meta($post_id,'ma_job_type',true));
        $location=(string)get_post_meta($post_id,'ma_job_location',true);
        foreach([$company,$job_type,$location] as $value) if($value!=='') $items[]=$value;
    } elseif($type==='ma_obituary'){
        $name=(string)get_post_meta($post_id,'ma_obituary_name',true);
        $birth=(string)get_post_meta($post_id,'ma_obituary_birth',true);
        $death=(string)get_post_meta($post_id,'ma_obituary_death',true);
        if($name!=='') $items[]=$name;
        if($birth!=='' || $death!=='') $items[]=trim($birth.' – '.$death,' –');
    } elseif($type==='ma_family_notice'){
        $items[]=ma_theme_family_kind_label((string)get_post_meta($post_id,'ma_family_kind',true));
        $date=(string)get_post_meta($post_id,'ma_family_date',true);
        $place=(string)get_post_meta($post_id,'ma_family_place',true);
        if($date!=='') $items[]=$date;
        if($place!=='') $items[]=$place;
    }

    return array_values(array_filter($items,static fn($v)=>$v!==''));
}

function ma_theme_market_type_label(string $post_type=''): string {
    $post_type=$post_type?:get_post_type();
    $labels=[
        'ma_property'=>'Immobilien','ma_job'=>'Stellen','ma_obituary'=>'Traueranzeigen','ma_family_notice'=>'Familienanzeigen',
    ];
    return $labels[$post_type]??'Anzeigen';
}
