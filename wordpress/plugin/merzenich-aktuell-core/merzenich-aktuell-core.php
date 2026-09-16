<?php
/**
 * Plugin Name: Merzenich Aktuell Core
 * Description: Redaktion, Orte, Termine, Wetter, Märkte, Werbung, Sport und Transparenz für Merzenich Aktuell.
 * Version: 1.2.0-recovery
 * Author: Merzenich Aktuell
 * Requires PHP: 8.1
 */
if (!defined('ABSPATH')) { exit; }
define('MA_CORE_VERSION', '1.2.0-recovery');
define('MA_CORE_PATH', plugin_dir_path(__FILE__));
define('MA_CORE_URL', plugin_dir_url(__FILE__));

require_once MA_CORE_PATH . 'includes/content.php';
require_once MA_CORE_PATH . 'includes/images.php';
require_once MA_CORE_PATH . 'includes/content-admin.php';
require_once MA_CORE_PATH . 'includes/editorial.php';
require_once MA_CORE_PATH . 'includes/freigabe.php';
require_once MA_CORE_PATH . 'includes/weather.php';
require_once MA_CORE_PATH . 'includes/ads.php';
require_once MA_CORE_PATH . 'includes/sport.php';
require_once MA_CORE_PATH . 'includes/forms.php';
require_once MA_CORE_PATH . 'includes/radar.php';
require_once MA_CORE_PATH . 'includes/admin.php';

add_action('plugins_loaded', function () {
    ma_register_content_admin_hooks();
    ma_register_editorial_hooks();
    ma_register_weather_hooks();
    ma_register_ads_hooks();
    ma_register_sport_hooks();
    ma_register_form_hooks();
    ma_register_radar_hooks();
    ma_register_admin_hooks();
});

register_activation_hook(__FILE__, function () {
    ma_register_content_types();
    foreach (['Merzenich'=>'merzenich','Golzheim'=>'golzheim','Girbelsrath'=>'girbelsrath','Morschenich'=>'morschenich','Bürgewald'=>'buergewald'] as $name=>$slug) {
        if (!term_exists($slug,'ma_location')) wp_insert_term($name,'ma_location',['slug'=>$slug]);
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
            'latitude' => '50.826813',
            'longitude' => '6.524935',
            'cache_minutes' => 20,
        ]);
    }
    if (get_option('ma_ads_enabled', null) === null) {
        update_option('ma_ads_enabled', 0);
        update_option('ma_ad_slots', []);
    }
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, 'flush_rewrite_rules');
