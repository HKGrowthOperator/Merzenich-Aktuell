<?php
/**
 * Werbung im Frontend und ihre Zählung (01.10.2026).
 *
 * 1. Anzeigenplatz in den aufgeklappten Menüs „Vereine“ und „Unternehmen“
 *    (hero_clubs_expanded_right): Die Seite bekommt window.maWerbungPlaetze mit
 *    den laufenden Anzeigen aus der Werbeverwaltung (Werbemittel, Platz,
 *    Start/Ende, „Schaltung aktiv“, Platz-Schalter). ressort-dropdowns.js zeigt
 *    sie rechts neben den Linkgruppen. Ohne laufende Anzeige erscheint dort wie
 *    auf allen Werbeplätzen die gekennzeichnete Musteranzeige (07.10.2026).
 *
 * 2. Zählung: Impression = eine Anzeige war in einem Seitenaufruf mindestens zur
 *    Hälfte sichtbar (einmal je Anzeige und Seitenaufruf). Klick = Klick auf die
 *    Anzeige. Gespeichert nur als Summe je Tag, Anzeige und Platz
 *    ({prefix}ma_werbung), ohne Cookies und ohne IP. Bots und angemeldete
 *    Redakteure zählen nicht (ma_statistik_zaehlt()).
 *
 * 3. Gesponserte Beiträge: Haken „Gesponserter Beitrag“ (nur Redaktion). Die
 *    Seite kennzeichnet sie als „Anzeige · Gesponsert“ in Karten und Artikel.
 */
if (!defined('ABSPATH')) { exit; }

const MA_WERBUNG_DB_VERSION = '1';

function ma_werbung_tabelle(): string { global $wpdb; return $wpdb->prefix . 'ma_werbung'; }

add_action('plugins_loaded', function (): void {
    if (get_option('ma_werbung_db') === MA_WERBUNG_DB_VERSION) return;
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta('CREATE TABLE ' . ma_werbung_tabelle() . " (
  tag date NOT NULL,
  anzeige bigint(20) unsigned NOT NULL,
  platz varchar(60) NOT NULL DEFAULT '',
  impressionen int(10) unsigned NOT NULL DEFAULT 0,
  klicks int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (tag,anzeige,platz)
) " . $wpdb->get_charset_collate() . ';');
    update_option('ma_werbung_db', MA_WERBUNG_DB_VERSION, false);
});

function ma_werbung_zaehlen(int $anzeige, string $platz, string $art): void {
    global $wpdb;
    $spalte = $art === 'k' ? 'klicks' : 'impressionen';
    $wpdb->query($wpdb->prepare('INSERT INTO ' . ma_werbung_tabelle() . " (tag, anzeige, platz, impressionen, klicks) VALUES (%s, %d, %s, %d, %d) ON DUPLICATE KEY UPDATE {$spalte} = {$spalte} + 1",
        current_time('Y-m-d'), $anzeige, $platz, $art === 'k' ? 0 : 1, $art === 'k' ? 1 : 0));
}

add_action('rest_api_init', function (): void {
    register_rest_route('ma/v1', '/werbung', [
        'methods' => 'POST', 'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $r) {
            if (function_exists('ma_statistik_zaehlt') && !ma_statistik_zaehlt((string) $r->get_header('user_agent'))) return new WP_REST_Response(null, 204);
            $d = json_decode((string) $r->get_body(), true) ?: [];
            $id = (int) ($d['id'] ?? 0); $platz = sanitize_key((string) ($d['platz'] ?? ''));
            $art = ($d['art'] ?? '') === 'k' ? 'k' : 'i';
            // Bremse je Besucher (statistik.php): Impressionen und Klicks getrennt.
            if (function_exists('ma_zaehl_bremse') && !ma_zaehl_bremse('werbung_' . $art, $art === 'k' ? 10 : 200, 10 * MINUTE_IN_SECONDS, (string) $r->get_header('user_agent'))) return new WP_REST_Response(null, 204);
            // Nur laufende Anzeigen der Werbeverwaltung auf ihrem Platz.
            $ad = $id ? get_post($id) : null;
            if (!$ad || !function_exists('ma_ad_is_running') || !ma_ad_is_running($ad) || !in_array($platz, function_exists('ma_ad_plaetze') ? ma_ad_plaetze($id) : [(string) get_post_meta($id, 'ma_ad_slot', true)], true)) return new WP_REST_Response(null, 204);
            ma_werbung_zaehlen($id, $platz, $art);
            return new WP_REST_Response(null, 204);
        },
    ]);
});

/** Laufende Anzeigen eines Platzes als Daten für die Seite (Markup fertig escaped). */
function ma_werbung_platz_daten(string $platz): array {
    if (!function_exists('ma_active_ads')) return [];
    $raus = [];
    foreach (ma_ads_rotate(ma_active_ads($platz), $platz) as $ad) {
        $url = (string) get_post_meta($ad->ID, 'ma_ad_url', true);
        $img = get_the_post_thumbnail_url($ad, 'large');
        $sponsor = (string) get_post_meta($ad->ID, 'ma_ad_sponsor', true);
        $inhalt = $img ? '<span class="ma-ad-art"><img src="' . esc_url($img) . '" alt="' . esc_attr(ma_ad_alt($ad)) . '" loading="lazy" decoding="async"></span>' : '';
        $inhalt .= '<span class="ma-ad-copy">' . ($sponsor !== '' ? '<span class="ma-ad-eyebrow">' . esc_html($sponsor) . '</span>' : '') . '<strong>' . esc_html(get_the_title($ad)) . '</strong>'
            . ($ad->post_excerpt !== '' ? '<span class="ma-ad-text">' . esc_html($ad->post_excerpt) . '</span>' : '') . '</span>';
        $html = $url !== '' ? '<a class="ma-ad-card ma-ad-card--gap" href="' . esc_url($url) . '" rel="sponsored noopener" target="_blank">' . $inhalt . '</a>' : '<div class="ma-ad-card ma-ad-card--gap">' . $inhalt . '</div>';
        $raus[] = ['id' => (int) $ad->ID, 'html' => $html];
    }
    return $raus;
}

/** Plätze, die erst im Browser entstehen (Menü), und der Zähler für alle Anzeigen. */
add_action('wp_head', function (): void {
    if (is_admin()) return;
    printf('<script>window.maWerbungPlaetze=%s;</script>' . "\n", wp_json_encode(['hero_clubs_expanded_right' => ma_werbung_platz_daten('hero_clubs_expanded_right')]));
}, 5);

add_action('wp_footer', function (): void {
    if (is_admin() || (is_user_logged_in() && current_user_can('edit_posts'))) return;
    $url = esc_url_raw(rest_url('ma/v1/werbung'));
    // Impression: einmal je Anzeige und Seitenaufruf, sobald halb sichtbar. Auch
    // für Anzeigen, die später entstehen (Menü). Klick: beim Klick auf die Anzeige.
    printf('<script>(function(){if(!navigator.sendBeacon||!window.IntersectionObserver)return;var u=%s,gesehen={};'
        . 'function s(el,art){var id=el.getAttribute("data-ma-anzeige"),p=el.getAttribute("data-ma-platz")||(el.closest("[data-ma-ad-slot]")||{getAttribute:function(){return""}}).getAttribute("data-ma-ad-slot");if(!id||!p)return;navigator.sendBeacon(u,new Blob([JSON.stringify({id:+id,platz:p,art:art})],{type:"application/json"}));}'
        . 'var io=new IntersectionObserver(function(es){es.forEach(function(e){var id=e.target.getAttribute("data-ma-anzeige");if(e.isIntersecting&&!gesehen[id]&&!e.target.hidden){gesehen[id]=1;s(e.target,"i");}});},{threshold:.5});'
        . 'function beobachte(w){(w.matches&&w.matches("[data-ma-anzeige]")?[w]:[]).concat([].slice.call(w.querySelectorAll?w.querySelectorAll("[data-ma-anzeige]"):[])).forEach(function(el){if(!el.__maBeob){el.__maBeob=1;io.observe(el);}});}'
        . 'beobachte(document);new MutationObserver(function(m){m.forEach(function(x){x.addedNodes.forEach(function(n){if(n.nodeType===1)beobachte(n);});});}).observe(document.body,{childList:true,subtree:true});'
        . 'document.addEventListener("click",function(e){var a=e.target.closest&&e.target.closest("[data-ma-anzeige]");if(a)s(a,"k");},true);})();</script>',
        wp_json_encode($url));
}, 98);

/* ---------------------------------------------------------- Auswertung */

/** Summen je Anzeige im Zeitraum [von, bis] (Y-m-d, beide eingeschlossen). */
function ma_werbung_summen(string $von, string $bis): array {
    global $wpdb;
    return $wpdb->get_results($wpdb->prepare('SELECT anzeige, platz, SUM(impressionen) i, SUM(klicks) k FROM ' . ma_werbung_tabelle() . ' WHERE tag BETWEEN %s AND %s GROUP BY anzeige, platz ORDER BY i DESC', $von, $bis));
}

/* ---------------------------------------------------------- Gesponserte Beiträge */

add_action('add_meta_boxes_post', function (): void {
    if (!current_user_can('edit_others_posts') || (function_exists('ma_current_partner_policy') && ma_current_partner_policy())) return;
    add_meta_box('ma-gesponsert', 'Kennzeichnung', function (WP_Post $p): void {
        wp_nonce_field('ma_gesponsert', 'ma_gesponsert_nonce');
        printf('<label><input type="checkbox" name="ma_gesponsert" value="1"%s> Gesponserter Beitrag (bezahlte Unternehmenspräsentation)</label><p class="description">Die Seite kennzeichnet ihn als „Anzeige · Gesponsert“. Redaktionelle Unternehmensnachrichten bleiben ohne Haken.</p>',
            checked((string) get_post_meta($p->ID, 'ma_gesponsert', true), '1', false));
        printf('<p><label>Auftraggeber (sichtbar)<br><input type="text" name="ma_gesponsert_von" value="%s" style="width:100%%"></label></p>', esc_attr((string) get_post_meta($p->ID, 'ma_gesponsert_von', true)));
    }, 'post', 'side', 'default');
});
add_action('save_post_post', function (int $id): void {
    if (!isset($_POST['ma_gesponsert_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_gesponsert_nonce'])), 'ma_gesponsert')) return;
    if (!current_user_can('edit_others_posts') || !current_user_can('edit_post', $id)) return;
    $an = !empty($_POST['ma_gesponsert']);
    $an ? update_post_meta($id, 'ma_gesponsert', '1') : delete_post_meta($id, 'ma_gesponsert');
    $von = sanitize_text_field(wp_unslash($_POST['ma_gesponsert_von'] ?? ''));
    $von !== '' ? update_post_meta($id, 'ma_gesponsert_von', $von) : delete_post_meta($id, 'ma_gesponsert_von');
}, 20);

/* Beiträge von Unternehmens-Zugängen sind Werbung (1.21.0): automatisch „Anzeige · Gesponsert“,
   Auftraggeber ist das Unternehmen (änderbar). Läuft nach dem Kasten der Redaktion (20),
   damit der Haken nicht vergessen werden kann. */
add_action('save_post_post', function (int $id, WP_Post $p): void {
    if (wp_is_post_revision($id) || wp_is_post_autosave($id)) return;
    $u = get_userdata((int) $p->post_author);
    if (!$u || !in_array('ma_wirtschaft_partner', (array) $u->roles, true)) return;
    if ((string) get_post_meta($id, 'ma_gesponsert', true) !== '1') update_post_meta($id, 'ma_gesponsert', '1');
    if ((string) get_post_meta($id, 'ma_gesponsert_von', true) === '') update_post_meta($id, 'ma_gesponsert_von', trim((string) $u->display_name));
}, 30, 2);

function ma_ist_gesponsert($post): bool {
    $id = $post instanceof WP_Post ? $post->ID : (int) $post;
    return (string) get_post_meta($id, 'ma_gesponsert', true) === '1';
}
