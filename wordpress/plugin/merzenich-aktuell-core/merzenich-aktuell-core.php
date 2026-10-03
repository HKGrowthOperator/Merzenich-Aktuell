<?php
/**
 * Plugin Name: Merzenich Aktuell Core
 * Description: Redaktion, Orte, Termine, Wetter, Märkte, Werbung, Sport und Transparenz für Merzenich Aktuell.
 * Version: 1.19.0
 * Author: Merzenich Aktuell
 * Requires PHP: 8.1
 */
if (!defined('ABSPATH')) { exit; }
define('MA_CORE_VERSION', '1.19.0');
define('MA_CORE_PATH', plugin_dir_path(__FILE__));
define('MA_CORE_URL', plugin_dir_url(__FILE__));

// IONOS-Designbibliothek und KI-Werkzeuge (Plugin Extendify) bleiben aus:
// kein Vorlagen-Fenster und kein leerer Block-Editor beim Anlegen von
// Inhalten (Vorgabe Betreiber 02.10.2026). Extendify prüft diesen Filter in
// plugins_loaded und lädt dann nichts. Die IONOS-Plugins bleiben installiert.
add_filter('extendify_load_library', '__return_false');

require_once MA_CORE_PATH . 'includes/content.php';
require_once MA_CORE_PATH . 'includes/permalinks.php';
require_once MA_CORE_PATH . 'includes/images.php';
require_once MA_CORE_PATH . 'includes/orte.php';
require_once MA_CORE_PATH . 'includes/content-admin.php';
require_once MA_CORE_PATH . 'includes/editorial.php';
require_once MA_CORE_PATH . 'includes/freigabe.php';
require_once MA_CORE_PATH . 'includes/sammel-freigabe.php';
require_once MA_CORE_PATH . 'includes/layout.php';
require_once MA_CORE_PATH . 'includes/layout-rest.php';
require_once MA_CORE_PATH . 'includes/layout-admin.php';
require_once MA_CORE_PATH . 'includes/layout-front.php';
require_once MA_CORE_PATH . 'includes/startplatz.php';
require_once MA_CORE_PATH . 'includes/relevanz.php';
require_once MA_CORE_PATH . 'includes/partners.php';
require_once MA_CORE_PATH . 'includes/comments.php';
require_once MA_CORE_PATH . 'includes/weather.php';
require_once MA_CORE_PATH . 'includes/ads.php';
require_once MA_CORE_PATH . 'includes/ad-quota.php';
require_once MA_CORE_PATH . 'includes/business.php';
require_once MA_CORE_PATH . 'includes/partner-notify.php';
require_once MA_CORE_PATH . 'includes/partner-antrag.php';
require_once MA_CORE_PATH . 'includes/startseite.php';
require_once MA_CORE_PATH . 'includes/sport.php';
require_once MA_CORE_PATH . 'includes/sport-vereine.php';
require_once MA_CORE_PATH . 'includes/gemeinde.php';
require_once MA_CORE_PATH . 'includes/forms.php';
require_once MA_CORE_PATH . 'includes/eingang.php';
require_once MA_CORE_PATH . 'includes/statistik.php';
require_once MA_CORE_PATH . 'includes/live.php';
require_once MA_CORE_PATH . 'includes/redaktion.php';
require_once MA_CORE_PATH . 'includes/bildrechte.php';
require_once MA_CORE_PATH . 'includes/vereine.php';
require_once MA_CORE_PATH . 'includes/sport-bilder-admin.php';
require_once MA_CORE_PATH . 'includes/bildpools.php';
require_once MA_CORE_PATH . 'includes/werbung-stat.php';
require_once MA_CORE_PATH . 'includes/vereinszugaenge.php';
require_once MA_CORE_PATH . 'includes/radar.php';
require_once MA_CORE_PATH . 'includes/rechtstexte.php';
require_once MA_CORE_PATH . 'includes/seiten.php';
require_once MA_CORE_PATH . 'includes/seo.php';
require_once MA_CORE_PATH . 'includes/feeds.php';
require_once MA_CORE_PATH . 'includes/push.php';
require_once MA_CORE_PATH . 'includes/admin.php';

add_action('plugins_loaded', function () {
    ma_register_content_admin_hooks();
    ma_register_editorial_hooks();
    ma_register_partner_hooks();
    ma_register_partner_notify_hooks();
    ma_register_partner_antrag_hooks();
    ma_register_comment_moderation_hooks();
    ma_register_weather_hooks();
    ma_register_ads_hooks();
    ma_register_ad_quota_hooks();
    ma_register_business_hooks();
    ma_register_sport_hooks();
    ma_register_gemeinde_hooks();
    ma_register_form_hooks();
    ma_register_radar_hooks();
    ma_register_admin_hooks();
});

register_activation_hook(__FILE__, function () {
    ma_register_content_types();
    ma_register_partner_roles();
    foreach (['Merzenich'=>'merzenich','Golzheim'=>'golzheim','Girbelsrath'=>'girbelsrath','Morschenich'=>'morschenich','Bürgewald'=>'buergewald'] as $name=>$slug) {
        if (!term_exists($slug,'ma_location')) wp_insert_term($name,'ma_location',['slug'=>$slug]);
    }
    foreach (['Blaulicht'=>'blaulicht','Sport'=>'sport','Rathaus & Politik'=>'rathaus','Vereine'=>'vereine','Wirtschaft'=>'wirtschaft'] as $name=>$slug) {
        if (!term_exists($slug,'category')) wp_insert_term($name,'category',['slug'=>$slug]);
    }
    foreach (['Neu','Prüfen','Übernommen','Ignoriert','Duplikat'] as $status) {
        if (!term_exists($status,'ma_source_status')) wp_insert_term($status,'ma_source_status');
    }
    if (get_option('ma_comments_defaults_set') !== '1') {
        update_option('comment_moderation', 1);
        update_option('require_name_email', 1);
        update_option('ma_comments_defaults_set', '1');
    }
    if (get_option('ma_weather_settings', null) === null) {
        update_option('ma_weather_settings', [
            'enabled' => 1,
            // Wie der statische Stand (deploy/coolify/kommentare/server.mjs):
            // Ortsmitte Merzenich, zehn Minuten Cache.
            'latitude' => '50.8317',
            'longitude' => '6.5361',
            'cache_minutes' => 10,
        ]);
    }
    if (get_option('ma_ads_enabled', null) === null) {
        update_option('ma_ads_enabled', 0);
        update_option('ma_ad_slots', []);
    }
    ma_permalinks_standard();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, 'flush_rewrite_rules');
