<?php
/**
 * Selbst-Update aus dem Repository (1.39.0). Vorgabe Betreiber 02.11.2026: Jeder Push auf main
 * soll ohne Handarbeit live gehen („das muss immer automatisch laufen“).
 *
 * Ablauf: Der Workflow „Build WordPress Delivery Packages“ prüft alle qa/wordpress/*-test.php,
 * baut die ZIPs und schreibt wordpress-delivery/release.json (Version und SHA-256 je Paket).
 * Danach stößt „WordPress-Abgleich anstoßen“ /?ma_api=update-anstoss an. WordPress liest
 * release.json, lädt nur neuere Pakete, prüft die Prüfsumme und installiert erst das Plugin,
 * dann das Theme (dieselbe Reihenfolge wie beim Einspielen von Hand). Zusätzlich stündlich per
 * Cron. Ohne Geheimnis: Der Anstoß plant nur einen Lauf (höchstens alle fünf Minuten), und
 * installiert wird nur, was im Repository steht und zur Prüfsumme passt. Nie zurück auf eine
 * ältere Version.
 *
 * Abschalten: Option ma_selbst_update_aus = '1' oder Konstante MA_SELBST_UPDATE_AUS in wp-config.php.
 * Stand: /?ma_api=update-status (Versionen, letzter Lauf).
 */
if (!defined('ABSPATH')) { exit; }

const MA_SU_ROH = 'https://raw.githubusercontent.com/HKGrowthOperator/merzenich-aktuell/main/';
const MA_SU_CRON = 'ma_selbst_update';
const MA_SU_THEME = 'merzenich-aktuell';

/** Ist release.json vollständig und plausibel? (reine Funktion) */
function ma_su_manifest_gueltig($m): bool {
    if (!is_array($m)) return false;
    foreach (['plugin', 'theme'] as $teil) {
        $t = $m[$teil] ?? null;
        if (!is_array($t)) return false;
        if (!preg_match('/^\d+\.\d+\.\d+$/', (string) ($t['version'] ?? ''))) return false;
        if (!preg_match('/^[a-f0-9]{64}$/', (string) ($t['sha256'] ?? ''))) return false;
        if (!preg_match('~^wordpress-delivery/[a-z0-9-]+\.zip$~', (string) ($t['zip'] ?? ''))) return false;
    }
    return true;
}

/** Welche Pakete sind neuer als das Installierte? Nur aufwärts. (reine Funktion) */
function ma_su_faellig(array $m, string $plugin, string $theme): array {
    $aus = [];
    if (version_compare($m['plugin']['version'], $plugin, '>')) $aus['plugin'] = $m['plugin'];
    if ($theme !== '' && version_compare($m['theme']['version'], $theme, '>')) $aus['theme'] = $m['theme'];
    return $aus;
}

function ma_su_aktiv(): bool {
    if (defined('MA_SELBST_UPDATE_AUS') && MA_SELBST_UPDATE_AUS) return false;
    return get_option('ma_selbst_update_aus') !== '1';
}

function ma_su_theme_version(): string {
    $t = wp_get_theme(MA_SU_THEME);
    return $t->exists() ? (string) $t->get('Version') : '';
}

function ma_su_merken(array $e): array {
    update_option('ma_selbst_update_letzter', $e, false);
    return $e;
}

/** Ein Lauf: Manifest lesen, fällige Pakete laden, prüfen, installieren. */
function ma_su_lauf(): array {
    $e = ['zeit' => time(), 'plugin_vorher' => MA_CORE_VERSION, 'theme_vorher' => ma_su_theme_version(), 'schritte' => []];
    if (!ma_su_aktiv()) { $e['schritte'][] = 'abgeschaltet'; return ma_su_merken($e); }
    if (get_transient('ma_su_sperre')) { $e['schritte'][] = 'läuft bereits'; return $e; }
    set_transient('ma_su_sperre', '1', 10 * MINUTE_IN_SECONDS);

    $antwort = wp_remote_get(MA_SU_ROH . 'wordpress-delivery/release.json?t=' . time(), ['timeout' => 20]);
    $m = is_wp_error($antwort) ? null : json_decode((string) wp_remote_retrieve_body($antwort), true);
    if (!ma_su_manifest_gueltig($m)) {
        $e['schritte'][] = 'release.json nicht lesbar' . (is_wp_error($antwort) ? ': ' . $antwort->get_error_message() : ' (HTTP ' . wp_remote_retrieve_response_code($antwort) . ')');
        delete_transient('ma_su_sperre');
        return ma_su_merken($e);
    }
    $e['release'] = ['plugin' => $m['plugin']['version'], 'theme' => $m['theme']['version']];
    $faellig = ma_su_faellig($m, MA_CORE_VERSION, $e['theme_vorher']);
    if (!$faellig) { $e['schritte'][] = 'aktuell'; delete_transient('ma_su_sperre'); return ma_su_merken($e); }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/misc.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    require_once ABSPATH . 'wp-admin/includes/theme.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    if (!WP_Filesystem()) { $e['schritte'][] = 'kein direkter Dateizugriff (WP_Filesystem)'; delete_transient('ma_su_sperre'); return ma_su_merken($e); }

    foreach (['plugin', 'theme'] as $teil) {
        if (!isset($faellig[$teil])) continue;
        $p = $faellig[$teil];
        $datei = download_url(MA_SU_ROH . $p['zip'] . '?v=' . $p['sha256'], 120);
        if (is_wp_error($datei)) { $e['schritte'][] = "$teil {$p['version']}: Download fehlgeschlagen ({$datei->get_error_message()})"; continue; }
        if (!hash_equals($p['sha256'], (string) hash_file('sha256', $datei))) {
            @unlink($datei);
            $e['schritte'][] = "$teil {$p['version']}: Prüfsumme passt nicht (GitHub liefert noch den alten Stand), nächster Lauf versucht es erneut";
            continue;
        }
        $skin = new Automatic_Upgrader_Skin();
        $upgrader = $teil === 'plugin' ? new Plugin_Upgrader($skin) : new Theme_Upgrader($skin);
        $ok = $upgrader->install($datei, ['overwrite_package' => true]);
        @unlink($datei);
        if ($ok === true) $e['schritte'][] = "$teil {$p['version']} installiert";
        else $e['schritte'][] = "$teil {$p['version']}: Installation fehlgeschlagen (" . (is_wp_error($ok) ? $ok->get_error_message() : implode(' / ', array_map('wp_strip_all_tags', (array) $skin->get_upgrade_messages()))) . ')';
        // Ohne neues Plugin kein Theme: Das Theme kann Funktionen der neuen Plugin-Version brauchen.
        if ($teil === 'plugin' && $ok !== true) break;
    }
    delete_transient('ma_su_sperre');
    return ma_su_merken($e);
}
add_action(MA_SU_CRON, 'ma_su_lauf');

/* Stündlich als Rückfall, falls ein Anstoß ausbleibt. */
add_action('init', function (): void {
    if (!wp_next_scheduled(MA_SU_CRON . '_stuendlich')) wp_schedule_event(time() + 600, 'hourly', MA_SU_CRON . '_stuendlich');
});
add_action(MA_SU_CRON . '_stuendlich', 'ma_su_lauf');
register_deactivation_hook(MA_CORE_PATH . 'merzenich-aktuell-core.php', function (): void {
    wp_clear_scheduled_hook(MA_SU_CRON);
    wp_clear_scheduled_hook(MA_SU_CRON . '_stuendlich');
});

/* /?ma_api=update-anstoss plant einen Lauf auf jetzt; /?ma_api=update-status zeigt den Stand. */
add_filter('query_vars', function (array $v): array { if (!in_array('ma_api', $v, true)) $v[] = 'ma_api'; return $v; });
add_action('template_redirect', function (): void {
    $api = get_query_var('ma_api');
    if ($api !== 'update-anstoss' && $api !== 'update-status') return;
    nocache_headers();
    $aus = ['aktiv' => ma_su_aktiv(), 'plugin' => MA_CORE_VERSION, 'theme' => ma_su_theme_version(), 'letzter' => get_option('ma_selbst_update_letzter') ?: null, 'angestossen' => false];
    if ($api === 'update-anstoss' && ma_su_aktiv() && get_transient('ma_su_anstoss') === false) {
        set_transient('ma_su_anstoss', '1', 5 * MINUTE_IN_SECONDS);
        if (!wp_next_scheduled(MA_SU_CRON)) wp_schedule_single_event(time() - 1, MA_SU_CRON);
        if (function_exists('spawn_cron')) spawn_cron();
        $aus['angestossen'] = true;
    }
    wp_send_json($aus);
}, 0);
