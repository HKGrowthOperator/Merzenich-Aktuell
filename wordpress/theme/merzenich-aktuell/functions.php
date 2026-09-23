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

/**
 * Notnagel ohne Plugin: Die Vorlagen rufen ma_content_image() ungeschuetzt
 * auf. Ist merzenich-aktuell-core deaktiviert, darf daraus kein Fatal Error
 * auf jeder Seite werden. Dieselbe Regel wie im Plugin: ein Bild zaehlt nur
 * mit geklaerten Rechten oder Lizenz, sonst die gekennzeichnete Ersatzgrafik.
 */
if(!function_exists('ma_content_image')){
    function ma_content_image($post=null,string $size='large'): array {
        $post=get_post($post);
        $id=$post?(int)$post->ID:0;
        $credit=$id?(string)get_post_meta($id,'ma_image_credit',true):'';
        $license=$id?(string)get_post_meta($id,'ma_image_license',true):'';
        $verified=$id && (string)get_post_meta($id,'ma_image_rights_verified',true)==='1';
        $thumb=$id?(int)get_post_thumbnail_id($id):0;
        $url=$thumb?(string)get_the_post_thumbnail_url($id,$size):'';
        if($url!=='' && ($verified||$license!=='')){
            $alt=(string)get_post_meta($thumb,'_wp_attachment_image_alt',true);
            return ['url'=>$url,'type'=>$license!=='' && !$verified?'licensed':'original','type_label'=>'','credit'=>$credit,'alt'=>$alt!==''?$alt:get_the_title($id),'license'=>$license,'source_url'=>'','is_fallback'=>false,'disclaimer'=>''];
        }
        return ['url'=>get_template_directory_uri().'/assets/img/ph-nachrichten.svg','type'=>'symbol','type_label'=>'Symbolbild','credit'=>'Symbolbild · Merzenich Aktuell','alt'=>'Symbolgrafik Merzenich Aktuell','license'=>'','source_url'=>'','is_fallback'=>true,'disclaimer'=>''];
    }
}
if(!function_exists('ma_image_caption')){
    function ma_image_caption(array $bild): string { return (string)($bild['credit']??''); }
}

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
        'Tipp'=>'/tipp/',
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

/* Auch ein manuell gepflegtes WordPress-Menue darf den verbindlichen Tipp-Kanal
   nicht verlieren. Falls er fehlt, wird er nach Wirtschaft ergaenzt. */
add_filter('wp_nav_menu_items', function(string $items, stdClass $args): string {
    if (($args->theme_location ?? '') !== 'primary' || str_contains($items,'/tipp/')) return $items;
    $link='<li class="menu-item ma-menu-tipp"><a href="'.esc_url(home_url('/tipp/')).'">Tipp</a></li>';
    $pattern='~(<li[^>]*>\s*<a[^>]+href="[^"]*/(?:category/)?wirtschaft/?"[^>]*>Wirtschaft</a>\s*</li>)~i';
    if (preg_match($pattern,$items)) return preg_replace($pattern,'$1'.$link,$items,1) ?: $items.$link;
    return $items.$link;
},20,2);

function ma_theme_is_local_post(int $post_id): bool {
    $local_slugs=['merzenich','golzheim','girbelsrath','morschenich','buergewald','bürgewald'];
    foreach($local_slugs as $slug) if(has_term($slug,'ma_location',$post_id)) return true;
    return false;
}

function ma_theme_home_excluded_category_ids(): array {
    $ids=[];
    foreach(['sport'] as $slug){
        $term=get_category_by_slug($slug);
        if($term) $ids[]=(int)$term->term_id;
    }
    return $ids;
}

function ma_theme_photo_of_day(): ?array {
    $q=new WP_Query([
        'post_type'=>'post','post_status'=>'publish','posts_per_page'=>20,
        'category__not_in'=>ma_theme_home_excluded_category_ids(),
        'meta_query'=>[
            'relation'=>'AND',
            ['key'=>'_thumbnail_id','compare'=>'EXISTS'],
            ['key'=>'ma_image_rights_verified','value'=>'1','compare'=>'='],
        ],
        'orderby'=>'date','order'=>'DESC',
    ]);
    if(!$q->posts) return null;
    $index=(int)wp_date('z') % count($q->posts);
    $post=$q->posts[$index];
    $bild=ma_content_image($post,'large');
    if(empty($bild['url'])) return null;
    return ['post'=>$post,'bild'=>$bild];
}

function ma_theme_guide_icon(string $kind): string {
    $icons=[
        'haus'=>'<svg viewBox="0 0 64 48" aria-hidden="true"><path d="M8 25 32 7l24 18v18H39V31H25v12H8Z" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/></svg>',
        'kerze'=>'<svg viewBox="0 0 64 48" aria-hidden="true"><path d="M28 18h8v25h-8zM32 5c6 6 6 10 0 14-6-4-6-8 0-14Z" fill="none" stroke="currentColor" stroke-width="2.4"/></svg>',
        'familie'=>'<svg viewBox="0 0 64 48" aria-hidden="true"><circle cx="24" cy="16" r="6" fill="none" stroke="currentColor" stroke-width="2.4"/><circle cx="42" cy="18" r="5" fill="none" stroke="currentColor" stroke-width="2.4"/><path d="M10 42c1-11 7-16 14-16s13 5 14 16M34 42c1-8 5-12 10-12 6 0 10 4 11 12" fill="none" stroke="currentColor" stroke-width="2.4"/></svg>',
        'werbung'=>'<svg viewBox="0 0 64 48" aria-hidden="true"><path d="M8 22h12l28-12v28L20 28H8zM20 28l6 14h8l-5-11" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/></svg>',
    ];
    return $icons[$kind]??$icons['werbung'];
}

function ma_theme_publish_guide(string $type): string {
    $config=[
        'ma_property'=>[
            'title'=>'Immobilienanzeige aufgeben','intro'=>'Wählen Sie zuerst, was Sie veröffentlichen möchten. Danach führt das Formular durch die Einreichung.',
            'form'=>'immobilie','cards'=>[
                ['haus','Immobilie verkaufen','Haus, Wohnung, Grundstück oder Gewerbeobjekt.','verkauf'],
                ['haus','Immobilie vermieten','Wohnung, Haus oder Gewerbefläche zur Miete.','vermietung'],
                ['haus','Immobilie suchen','Gesuch für Kauf oder Miete.','gesuch'],
            ],
        ],
        'ma_obituary'=>[
            'title'=>'Traueranzeige aufgeben','intro'=>'Wählen Sie die passende Form. Die Redaktion prüft sensible Angaben vor der Veröffentlichung.',
            'form'=>'trauer','cards'=>[
                ['kerze','Traueranzeige','Einen Trauerfall würdevoll veröffentlichen.','traueranzeige'],
                ['kerze','Danksagung','Für Anteilnahme und Unterstützung danken.','danksagung'],
                ['kerze','Jahrgedächtnis','Erinnerung an einen verstorbenen Menschen.','jahrgedaechtnis'],
            ],
        ],
        'ma_family_notice'=>[
            'title'=>'Familienanzeige aufgeben','intro'=>'Geburt, Hochzeit, Jubiläum oder Glückwunsch – zuerst Anlass wählen, dann Daten übermitteln.',
            'form'=>'familie','cards'=>[
                ['familie','Geburt','Willkommen heißen und Freude teilen.','geburt'],
                ['familie','Hochzeit','Hochzeit oder Verlobung veröffentlichen.','hochzeit'],
                ['familie','Jubiläum','Geburtstag, Hochzeitstag oder Vereinsjubiläum.','jubilaeum'],
            ],
        ],
        'ma_tip'=>[
            'title'=>'Tipp / Sponsoring platzieren','intro'=>'Bezahlte Platzierungen bleiben von redaktionellen Nachrichten getrennt und werden sichtbar gekennzeichnet.',
            'form'=>'werbung','cards'=>[
                ['werbung','Tipp','Projekt, Veranstaltung oder Angebot als Tipp platzieren.','tipp'],
                ['werbung','Sponsoring','Eine klar gekennzeichnete Sponsorplatzierung buchen.','sponsoring'],
                ['werbung','Unternehmensprofil','Dauerhafte lokale Präsenz im Wirtschafts- und Partnerumfeld.','unternehmen'],
            ],
        ],
    ];
    if(!isset($config[$type])) return '';
    $c=$config[$type];
    $base=get_post_type_archive_link($type)?:home_url('/');
    $html='<section class="publish-guide"><div class="publish-guide__head"><span class="eyebrow">Anzeige aufgeben</span><h2>'.esc_html($c['title']).'</h2><p>'.esc_html($c['intro']).'</p></div><div class="publish-guide__grid">';
    foreach($c['cards'] as $card){
        [$icon,$title,$text,$art]=$card;
        $url=add_query_arg('art',$art,$base).'#anzeige-aufgeben';
        $html.='<a class="publish-card" href="'.esc_url($url).'"><span class="publish-card__visual">'.ma_theme_guide_icon($icon).'</span><span class="publish-card__copy"><strong>'.esc_html($title).'</strong><small>'.esc_html($text).'</small></span></a>';
    }
    $html.='</div></section>';
    return $html;
}

function ma_theme_hero_post(): ?WP_Post {
    $now=current_time('Y-m-d H:i:s');
    $pinned=new WP_Query([
        'post_type'=>'post','post_status'=>'publish','posts_per_page'=>1,'orderby'=>'date','order'=>'DESC',
        'category__not_in'=>ma_theme_home_excluded_category_ids(),
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
        'category__not_in'=>ma_theme_home_excluded_category_ids(),
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

/** Wer inseriert eine Stelle? Vermittler werden immer benannt. */
function ma_theme_job_provider_label(string $type): string {
    return ['direct'=>'Direkter Arbeitgeber','agency'=>'Personaldienstleister'][$type] ?? '';
}

/**
 * Pruefzeile fuer Inserate: wann wurde gegen die Quelle abgeglichen, wann hat
 * die Quelle veroeffentlicht. Ohne Pruefdatum steht das auch so da.
 */
function ma_theme_verified_line(int $post_id): string {
    $gep=(string)get_post_meta($post_id,'ma_verified_at',true);
    $veroeff=(string)get_post_meta($post_id,'ma_source_published_at',true);
    $teile=[];
    if($veroeff!=='' && ($ts=strtotime($veroeff))) $teile[]='Quelle veröffentlicht am '.wp_date('d.m.Y',$ts);
    $teile[]=$gep!=='' && ($ts=strtotime($gep)) ? 'Gegen Quelle geprüft am '.wp_date('d.m.Y',$ts) : 'Noch nicht gegen die Quelle geprüft';
    return implode(' · ',$teile);
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
        // Personaldienstleister erscheinen schon in der Kurzzeile, nicht erst
        // auf der Detailseite. Bewerber sollen es sehen, bevor sie klicken.
        $vermittler=get_post_meta($post_id,'ma_job_provider_type',true)==='agency' ? 'Personaldienstleister' : '';
        foreach([$company,$vermittler,$job_type,$location] as $value) if($value!=='') $items[]=$value;
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
        'ma_property'=>'Immobilien','ma_job'=>'Stellen','ma_obituary'=>'Traueranzeigen','ma_family_notice'=>'Familienanzeigen','ma_tip'=>'Tipp · Anzeige',
    ];
    return $labels[$post_type]??'Anzeigen';
}
