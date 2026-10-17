<?php
/**
 * Bildangaben rund um automatisch gesetzte Poolfotos (17.10.2026, Nachprüfung der
 * Bildwiederholung) mit dem echten Hook auf _thumbnail_id (bildpools.php):
 * Kasten „Bild und Prüfung“ nach dem Veröffentlichen im Block-Editor
 * (editorial.php), Änderungsvorschlag mit und ohne neues Bild (redaktion.php),
 * Nachholen des Abgleich-Bilds (abgleich.php).
 * Aufruf: php qa/wordpress/bildangaben-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('MA_CORE_VERSION', 'test'); define('MA_CORE_PATH', __DIR__ . '/'); define('MINUTE_IN_SECONDS', 60);
const MA_IMAGE_TYPES = ['original' => 'Originalbild', 'official' => 'Offizielles Bild', 'licensed' => 'Lizenziertes Bild', 'symbol' => 'Symbolbild', 'place' => 'Ortsansicht'];
class WP_Post { public $ID = 0; public $post_type = 'post'; public $post_status = 'publish'; public $post_title = ''; public $post_content = ''; public $post_excerpt = ''; public $post_author = 0; public function __construct(array $a = []) { foreach ($a as $k => $v) $this->$k = $v; } }
class WP_User { public $ID = 0; public $display_name = ''; public $user_login = ''; public $user_email = ''; }

$GLOBALS['posts'] = []; $GLOBALS['meta'] = []; $GLOBALS['pools'] = ['aktuell' => [101, 102]]; $GLOBALS['opt'] = [];
function add_action(...$a) {} function add_filter(...$a) {} function register_deactivation_hook(...$a) {}
function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function update_option($k, $v, $a = null) { $GLOBALS['opt'][$k] = $v; return true; }
function get_post($id) { return $GLOBALS['posts'][(int) $id] ?? null; }
function get_post_type($id) { return ($p = get_post($id)) ? $p->post_type : false; }
function get_post_meta($id, $k, $s = false) { return $GLOBALS['meta'][(int) $id][$k] ?? ''; }
// Wie WordPress: Schreiben löst added_/updated_post_meta aus; hier direkt der Hook aus bildpools.php.
function update_post_meta($id, $k, $v) { $neu = !isset($GLOBALS['meta'][(int) $id][$k]); $GLOBALS['meta'][(int) $id][$k] = $v; ma_bildpool_merkmal_pruefen(1, (int) $id, $k, $v); return true; }
function add_post_meta($id, $k, $v) { return update_post_meta($id, $k, $v); }
function delete_post_meta($id, $k) { unset($GLOBALS['meta'][(int) $id][$k]); return true; }
function get_post_thumbnail_id($id) { return (int) ($GLOBALS['meta'][(int) $id]['_thumbnail_id'] ?? 0); }
function has_post_thumbnail($id) { return get_post_thumbnail_id($id) > 0; }
function set_post_thumbnail($id, $b) { return update_post_meta($id, '_thumbnail_id', (int) $b); }
function delete_post_thumbnail($id) { return delete_post_meta($id, '_thumbnail_id'); }
function has_term($t, $tax, $id) { foreach ($GLOBALS['pools'] as $ids) if (in_array((int) $id, $ids, true)) return true; return false; }
function wp_update_post(array $a) { foreach ($a as $k => $v) if ($k !== 'ID') $GLOBALS['posts'][(int) $a['ID']]->$k = $v; return (int) $a['ID']; }
function wp_delete_post($id, $f = false) { unset($GLOBALS['posts'][(int) $id], $GLOBALS['meta'][(int) $id]); return true; }
function get_userdata($id) { return false; } function get_current_user_id() { return 1; } function is_email($e) { return false; }
function is_object_in_taxonomy($t, $tax) { return false; } function get_permalink($id) { return '/?p=' . (int) $id; }
function current_user_can(...$a) { return true; } function wp_verify_nonce(...$a) { return true; } function wp_is_post_revision($id) { return false; }
function sanitize_text_field($s) { return trim(strip_tags((string) $s)); } function sanitize_textarea_field($s) { return trim((string) $s); } function wp_unslash($s) { return $s; }
function sanitize_key($s) { return strtolower(preg_replace('/[^a-z0-9_\-]/i', '', (string) $s)); } function esc_url_raw($s) { return (string) $s; }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES); } function wp_json_encode($v) { return json_encode($v); }
function get_the_ID() { return $GLOBALS['aktuell'] ?? 0; }
function get_posts($q) { return []; }

require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/fotoschluessel.php';
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/bildpools.php';
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/redaktion.php';
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/editorial.php';
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/abgleich.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
function beitrag(int $id, array $meta = [], string $typ = 'post'): WP_Post { $GLOBALS['posts'][$id] = new WP_Post(['ID' => $id, 'post_type' => $typ, 'post_title' => "Beitrag $id"]); $GLOBALS['meta'][$id] = $meta; return $GLOBALS['posts'][$id]; }
// Poolfotos mit Nachweis wie aus ma_bildpool_uebernehmen()
$GLOBALS['meta'][101] = ['ma_image_credit' => 'Foto X / Wikimedia Commons', 'ma_image_license' => 'CC BY 4.0', 'ma_image_original_url' => 'https://commons.wikimedia.org/wiki/File:X.jpg'];
// Kasten so, wie ihn der Browser beim Laden zeigte, und was er abschickt.
function kasten(int $id, array $geladen, array $gesendet): void {
    $_POST = ['ma_editorial_nonce' => 'x', 'ma_bildfelder_geladen' => json_encode($geladen)] + $gesendet;
    ma_save_editorial_meta($id);
    $_POST = [];
}
$leer = ['ma_image_credit' => '', 'ma_image_license' => '', 'ma_image_original_url' => '', 'ma_image_type' => '', 'ma_image_rights_verified' => '0'];

echo "Kasten „Bild und Prüfung“ nach dem Veröffentlichen (Block-Editor)\n";
beitrag(10);
ma_bildpool_bild_setzen(get_post(10), 101, 'aktuell');
pruefe('Poolfoto gesetzt: Nachweis, Typ, Rechte', [get_post_meta(10, 'ma_image_credit'), get_post_meta(10, 'ma_image_type'), get_post_meta(10, 'ma_image_rights_verified')], ['Foto X / Wikimedia Commons', 'symbol', '1']);
kasten(10, $leer, ['ma_image_credit' => '', 'ma_image_license' => '', 'ma_image_original_url' => '', 'ma_image_type' => '']);
pruefe('Alte, leere Felder überschreiben den Nachweis des Poolfotos nicht', [get_post_meta(10, 'ma_image_credit'), get_post_meta(10, 'ma_image_license'), get_post_meta(10, 'ma_image_type'), get_post_meta(10, 'ma_image_rights_verified')], ['Foto X / Wikimedia Commons', 'CC BY 4.0', 'symbol', '1']);
pruefe('Merkmal bleibt (das Plugin darf weiter verteilen)', get_post_meta(10, '_ma_bildpool_auto'), 'aktuell');
$GLOBALS['aktuell'] = 10;
ob_start(); ma_bildfelder_geladen_feld(); $feld = ob_get_clean();
$geladen = json_decode(html_entity_decode((string) preg_replace('/^.*value="([^"]*)".*$/s', '$1', $feld), ENT_QUOTES), true);
kasten(10, $geladen, ['ma_image_credit' => 'Foto X (Korrektur) / Wikimedia Commons', 'ma_image_license' => 'CC BY 4.0', 'ma_image_original_url' => 'https://commons.wikimedia.org/wiki/File:X.jpg', 'ma_image_type' => 'symbol', 'ma_image_rights_verified' => '1']);
pruefe('Redaktion ändert den Nachweis: gespeichert, Bild gilt als ihres (kein Merkmal)', [get_post_meta(10, 'ma_image_credit'), get_post_meta(10, '_ma_bildpool_auto')], ['Foto X (Korrektur) / Wikimedia Commons', '']);
beitrag(11, ['_thumbnail_id' => 880, 'ma_image_credit' => 'Gemeinde']);
kasten(11, ['ma_image_credit' => 'Gemeinde'] + $leer, ['ma_image_credit' => '', 'ma_image_type' => '']);
pruefe('Ohne Poolfoto wie bisher: geleertes Feld wird gelöscht', get_post_meta(11, 'ma_image_credit'), '');

echo "Änderungsvorschlag übernehmen\n";
// Vor dem Veröffentlichen „Bildrechte geprüft“ angehakt, dann ohne Bild veröffentlicht: Poolfoto, Sicherung mit Rechte '1'.
beitrag(20, ['ma_image_rights_verified' => '1']);
ma_bildpool_bild_setzen(get_post(20), 101, 'aktuell');
beitrag(21, [MA_AENDERUNG_VON => '20', '_ma_aenderung_bild_vorher' => '101', '_thumbnail_id' => 700, 'ma_image_credit' => 'Verein', 'ma_image_type' => 'original']);
ma_aenderung_uebernehmen(21);
pruefe('Neues, ungeprüftes Bild: „Bildrechte geprüft“ kommt nicht aus der Sicherung zurück', [get_post_thumbnail_id(20), get_post_meta(20, 'ma_image_rights_verified'), get_post_meta(20, 'ma_image_credit'), get_post_meta(20, '_ma_bildpool_auto')], [700, '', 'Verein', '']);
beitrag(30, ['_thumbnail_id' => 880, 'ma_image_credit' => 'unbekannt'], 'ma_business');
beitrag(31, [MA_AENDERUNG_VON => '30', '_ma_aenderung_bild_vorher' => '880', '_thumbnail_id' => 880, 'ma_image_credit' => 'Foto: Verein', 'ma_image_license' => 'Eigenes Foto'], 'ma_business');
ma_aenderung_uebernehmen(31);
pruefe('Eigenes Foto behalten, Nachweis korrigiert: Korrektur kommt an', [get_post_thumbnail_id(30), get_post_meta(30, 'ma_image_credit'), get_post_meta(30, 'ma_image_license')], [880, 'Foto: Verein', 'Eigenes Foto']);

echo "Abgleich: fehlendes Bild nachholen\n";
beitrag(40); ma_bildpool_bild_setzen(get_post(40), 102, 'aktuell');
beitrag(41, ['_thumbnail_id' => 880]); beitrag(42);
pruefe('Nur Poolfoto statt Abgleich-Bild: gilt als fehlend; eigenes Bild nicht; ohne Bild schon', [ma_abgleich_bild_fehlt(40), ma_abgleich_bild_fehlt(41), ma_abgleich_bild_fehlt(42)], [true, false, true]);
beitrag(43, ['ma_image_credit' => 'Gemeinde Merzenich', 'ma_image_type' => 'original', 'ma_image_rights_verified' => '0']);
ma_bildpool_bild_setzen(get_post(43), 102, 'aktuell');
set_post_thumbnail(43, 950); // ma_abgleich_bild_setzen beim nächsten Lauf
pruefe('Abgleich-Bild ersetzt das Poolfoto: Angaben aus der Import-Datei zurück', [get_post_meta(43, 'ma_image_credit'), get_post_meta(43, 'ma_image_type'), get_post_meta(43, 'ma_image_rights_verified'), get_post_meta(43, '_ma_bildpool_auto')], ['Gemeinde Merzenich', 'original', '0', '']);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
