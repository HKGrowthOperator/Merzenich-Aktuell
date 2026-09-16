<?php
if (!defined('ABSPATH')) { exit; }
function ma_ad_slots(): array { return ['homepage_sidebar_top','homepage_sidebar_middle','homepage_feed_1','article_inline_1','article_sidebar','header_billboard']; }
function ma_register_ads_hooks(): void { add_shortcode('ma_ad','ma_ad_shortcode'); }
function ma_active_ad(string $slot): ?WP_Post {
    if (!get_option('ma_ads_enabled',0)) return null;
    $enabled=(array)get_option('ma_ad_slots',[]); if (empty($enabled[$slot])) return null;
    $now=current_time('Y-m-d H:i:s');
    $basis=['post_type'=>'ma_ad','post_status'=>'publish','posts_per_page'=>1,
        'meta_query'=>['relation'=>'AND',['key'=>'ma_ad_slot','value'=>$slot],['key'=>'ma_ad_active','value'=>'1'],['relation'=>'OR',['key'=>'ma_ad_start','compare'=>'NOT EXISTS'],['key'=>'ma_ad_start','value'=>'','compare'=>'='],['key'=>'ma_ad_start','value'=>$now,'compare'=>'<=','type'=>'DATETIME']],['relation'=>'OR',['key'=>'ma_ad_end','compare'=>'NOT EXISTS'],['key'=>'ma_ad_end','value'=>'','compare'=>'='],['key'=>'ma_ad_end','value'=>$now,'compare'=>'>=','type'=>'DATETIME']]]];
    // Erster Durchgang wie bisher, nach Prioritaet sortiert. meta_key setzt in
    // WP_Query zugleich voraus, dass der Schluessel existiert. Eine Anzeige, die
    // per Import oder programmatisch angelegt wurde und ma_ad_priority nicht
    // traegt, war dadurch unsichtbar, obwohl sie veroeffentlicht und aktiv war
    // und dem freigeschalteten Platz zugeordnet. Ueber die Verwaltungsmaske
    // fiel das nicht auf, weil das leere Feld mitgeschickt wird und den
    // Schluessel anlegt.
    $q=new WP_Query($basis+['meta_key'=>'ma_ad_priority','orderby'=>['meta_value_num'=>'DESC','date'=>'DESC']]);
    if (!empty($q->posts)) return $q->posts[0];
    // Zweiter Durchgang ohne Prioritaet, damit solche Anzeigen ausgeliefert
    // werden. Reihenfolge dann nach Datum.
    $q=new WP_Query($basis+['orderby'=>['date'=>'DESC']]);
    return $q->posts[0]??null;
}
function ma_render_ad(string $slot): string {
    $ad=ma_active_ad($slot); if (!$ad) return '';
    $url=esc_url((string)get_post_meta($ad->ID,'ma_ad_url',true));
    $sponsor=esc_html((string)get_post_meta($ad->ID,'ma_ad_sponsor',true));
    $img=get_the_post_thumbnail_url($ad,'large');

    // Eine bezahlte Displayflaeche ist kein Text-Platzhalter. Ohne hinterlegtes
    // Creative bleibt der Slot leer und erzeugt gemaess Projektregel auch keinen
    // Leerraum. Sobald im Backend ein Bannerbild gesetzt ist, wird es ausgeliefert.
    if (!$img) return '';

    $body='<img src="'.esc_url($img).'" alt="'.esc_attr(get_the_title($ad)).'" loading="lazy" decoding="async">';
    if ($url) $body='<a href="'.$url.'" rel="sponsored noopener" target="_blank">'.$body.'</a>';
    return '<aside class="ma-ad ma-ad--'.esc_attr($slot).'" aria-label="Anzeige"><small>ANZEIGE</small>'.$body.($sponsor?'<p>'.$sponsor.'</p>':'').'</aside>';
}
function ma_ad_shortcode($atts): string { $a=shortcode_atts(['slot'=>''],$atts); return in_array($a['slot'],ma_ad_slots(),true)?ma_render_ad($a['slot']):''; }
