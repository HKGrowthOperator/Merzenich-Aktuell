<?php
if (!defined('ABSPATH')) { exit; }
add_action('after_setup_theme', function(){ add_theme_support('title-tag'); add_theme_support('post-thumbnails'); add_theme_support('html5',['search-form','comment-form','comment-list','gallery','caption','style','script']); register_nav_menus(['primary'=>'Hauptnavigation (Ressorts)']); });
add_action('wp_enqueue_scripts', function(){ wp_enqueue_style('ma-style',get_stylesheet_uri(),[],wp_get_theme()->get('Version')); wp_enqueue_script('ma-site',get_template_directory_uri().'/assets/js/site.js',[],wp_get_theme()->get('Version'),true); });

function ma_theme_location_label(int $id=0): string {
    $id=$id?:get_the_ID();
    $terms=get_the_terms($id,'ma_location');
    if (!$terms || is_wp_error($terms)) { $terms=get_the_terms($id,'ma_district'); }
    if (!$terms || is_wp_error($terms)) return '<span class="place-primary">MERZENICH</span>';

    $t=array_shift($terms);
    $slug=strtolower((string)$t->slug);
    $name=strtoupper(esc_html($t->name));
    $municipality_districts=['golzheim','girbelsrath','morschenich','buergewald','bürgewald'];

    if ($slug==='merzenich') return '<span class="place-primary">MERZENICH</span>';
    if (in_array($slug,$municipality_districts,true)) return '<span class="place-primary">MERZENICH</span> · <span class="place-secondary">'.$name.'</span>';

    return '<span class="place-secondary">'.$name.'</span>';
}

function ma_theme_primary_nav(): void { $fallback=['Aktuell'=>'/category/nachrichten/','Blaulicht'=>'/category/blaulicht/','Sport'=>'/sport/','Termine'=>'/veranstaltungen/','Vereine'=>'/vereine/','Rathaus & Politik'=>'/category/rathaus/','Leben'=>'/category/leben/','Wirtschaft'=>'/category/wirtschaft/','Menschen'=>'/category/menschen/']; if (has_nav_menu('primary')) wp_nav_menu(['theme_location'=>'primary','container'=>false,'menu_class'=>'','fallback_cb'=>false]); else { echo '<ul>'; foreach($fallback as $l=>$u) echo '<li><a href="'.esc_url(home_url($u)).'">'.esc_html($l).'</a></li>'; echo '</ul>'; } }
function ma_theme_hero_post(): ?WP_Post {
    $pinned=new WP_Query(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>1,'meta_query'=>[['key'=>'ma_top_pinned','value'=>'1']],'orderby'=>'date','order'=>'DESC']); if($pinned->posts) return $pinned->posts[0];
    $since=gmdate('Y-m-d H:i:s',time()-7*DAY_IN_SECONDS);
    $q=new WP_Query(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>20,'date_query'=>[['after'=>$since,'inclusive'=>true]],'orderby'=>'date','order'=>'DESC']);
    if(!$q->posts) return null; $best=null;$bestScore=-INF;$now=time(); foreach($q->posts as $p){$age=max(0,($now-get_post_time('U',true,$p))/DAY_IN_SECONDS);$fresh=max(0,100-($age/7*100));$loc=has_term('merzenich','ma_location',$p)?100:75;$prio=(int)get_post_meta($p->ID,'ma_editorial_priority',true);$score=.4*$fresh+.3*$loc+.3*$prio;if($score>$bestScore){$best=$p;$bestScore=$score;}} return $best;
}
function ma_theme_reading_time(int $id=0): int {$id=$id?:get_the_ID();$words=str_word_count(wp_strip_all_tags((string)get_post_field('post_content',$id)));return max(1,(int)ceil($words/220));}
function ma_theme_ad(string $slot): void { if(function_exists('ma_render_ad')) echo ma_render_ad($slot); }
