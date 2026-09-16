<?php
/**
 * Prueft die Freigabe-Logik ohne WordPress.
 *
 * Aufruf: php qa/wordpress/freigabe-test.php
 *
 * Es gibt hier keine WordPress-Installation und keinen Netzzugang, um eine zu
 * holen. Statt die Funktion ungeprueft zu lassen, laeuft sie gegen nachgebaute
 * WordPress-Funktionen. Das prueft die Entscheidungslogik - nicht die
 * Darstellung im Editor.
 */
define('ABSPATH', __DIR__);

$GLOBALS['meta']  = [];
$GLOBALS['thumb'] = [];
$GLOBALS['hooks'] = [];

function get_post_meta($id, $key, $single = false) { return $GLOBALS['meta'][$id][$key] ?? ''; }
function update_post_meta($id, $key, $v) { $GLOBALS['meta'][$id][$key] = $v; return true; }
function delete_post_meta($id, $key) { unset($GLOBALS['meta'][$id][$key]); return true; }
function has_post_thumbnail($id = 0) { return !empty($GLOBALS['thumb'][$id]); }
function add_filter($h, $f, $p = 10, $a = 1) { $GLOBALS['hooks'][$h][] = $f; }
function add_action($h, $f, $p = 10, $a = 1) { $GLOBALS['hooks'][$h][] = $f; }
function add_meta_box(...$a) {}
function esc_html($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
function get_the_ID() { return 0; }
function get_post_type($id = 0) { return 'post'; }
function current_time($f) { return date($f === 'Y-m-d H:i:s' ? 'Y-m-d H:i:s' : 'H:i:s'); }
function get_current_screen() { return null; }
function plugins_url(...$a) { return ''; }
function wp_enqueue_script(...$a) {}
function wp_localize_script(...$a) {}
function wp_add_inline_style(...$a) {}
function admin_url($p = '') { return '/wp-admin/' . $p; }
function wp_create_nonce($a) { return 'x'; }
function check_ajax_referer(...$a) { return true; }
function current_user_can(...$a) { return true; }
function wp_send_json_error(...$a) {}
function wp_send_json_success(...$a) {}
function sanitize_key($s) { return strtolower(preg_replace('/[^a-z0-9_\-]/i', '', (string)$s)); }
function sanitize_text_field($s) { return trim(strip_tags((string)$s)); }
function esc_url_raw($s) { return (string)$s; }
function wp_unslash($s) { return $s; }
const MA_IMAGE_TYPES = ['original'=>'','official'=>'','licensed'=>'','symbol'=>''];
define('MA_CORE_URL', '');
define('MA_CORE_VERSION', 'test');

require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/freigabe.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) {
    global $fehler;
    $ok = $ist === $soll;
    if (!$ok) $fehler++;
    printf("  %-58s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')');
}

echo "Pflichtfreigaben je Inhaltsart\n";
pruefe('Beitrag hat vier Pflichthaken', count(ma_gate_requirements('post')), 4);
pruefe('Immobilie hat einen',           count(ma_gate_requirements('ma_property')), 1);
pruefe('Verein hat keinen',             count(ma_gate_requirements('ma_club')), 0);

echo "\nWas fehlt gerade?\n";
$GLOBALS['meta'][7] = [];
pruefe('leerer Beitrag: vier offen', count(ma_gate_missing(7, 'post')), 4);

$GLOBALS['meta'][7] = ['ma_source_verified'=>'1','ma_date_verified'=>'1'];
pruefe('zwei gesetzt: zwei offen',   count(ma_gate_missing(7, 'post')), 2);

$GLOBALS['meta'][7] = ['ma_source_verified'=>'1','ma_date_verified'=>'1','ma_place_verified'=>'1','ma_human_reviewed'=>'1'];
pruefe('alle gesetzt: nichts offen', count(ma_gate_missing(7, 'post')), 0);

$GLOBALS['thumb'][7] = true;
pruefe('mit Bild ohne Bildrechte: eins offen', count(ma_gate_missing(7, 'post')), 1);
pruefe('und zwar die Bildrechte', array_key_first(ma_gate_missing(7, 'post')), 'ma_image_rights_verified');

echo "\nGrund wird festgehalten\n";
$GLOBALS['meta'][8] = [];
$GLOBALS['thumb'][8] = false;
ma_gate_record_reason(['post_type'=>'post','post_status'=>'draft'], ['ID'=>8,'post_status'=>'publish']);
$grund = get_post_meta(8, MA_GATE_NOTICE_META, true);
pruefe('Sperre hinterlaesst einen Grund', $grund !== '', true);
pruefe('Grund nennt alle vier', count(explode('|', $grund)), 4);

echo "\nGrund verschwindet wieder\n";
ma_gate_record_reason(['post_type'=>'post','post_status'=>'publish'], ['ID'=>8,'post_status'=>'publish']);
pruefe('nach erfolgreicher Veroeffentlichung geloescht', get_post_meta(8, MA_GATE_NOTICE_META, true), '');

echo "\nKein Grund ohne Veroeffentlichungsabsicht\n";
$GLOBALS['meta'][9] = [];
ma_gate_record_reason(['post_type'=>'post','post_status'=>'draft'], ['ID'=>9,'post_status'=>'draft']);
pruefe('bewusst gespeicherter Entwurf meldet nichts', get_post_meta(9, MA_GATE_NOTICE_META, true), '');

echo "\nZwischenspeicherung: erlaubte Felder\n";
pruefe('Freigabe-Haken NICHT zwischenspeicherbar', in_array('ma_release_confirmed', ma_autosave_allowed_keys(), true), false);
pruefe('Quellen-URL zwischenspeicherbar',          in_array('ma_source_url', ma_autosave_allowed_keys(), true), true);
pruefe('Bildtyp zwischenspeicherbar',              in_array('ma_image_type', ma_autosave_allowed_keys(), true), true);

echo "\n" . ($fehler === 0 ? "Alle Pruefungen bestanden.\n" : "$fehler Pruefung(en) fehlgeschlagen.\n");
exit($fehler === 0 ? 0 : 1);
