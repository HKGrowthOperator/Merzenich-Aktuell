<?php
/**
 * Unternehmenskanäle im Plugin (1.28.0, includes/unternehmenskanal.php) ohne
 * WordPress: Rechte eines Firmenzugangs (nur eigene, nicht veröffentlichte
 * Inhalte), Sperre der Kanal-Zuordnung, automatische Zuordnung neuer Beiträge,
 * Verknüpfen beim Anlegen des Zugangs, Änderungsvorschlag am eigenen Profil.
 * Aufruf: php qa/wordpress/unternehmenskanal-test.php
 */
define('ABSPATH', __DIR__);
define('MA_CORE_PATH', __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/');
class WP_Post {
    public $ID = 0; public $post_type = 'post'; public $post_status = 'draft'; public $post_author = 0; public $post_title = ''; public $post_name = ''; public $post_excerpt = ''; public $post_content = '';
    function __construct(array $a = []) { foreach ($a as $k => $v) $this->$k = $v; }
}
class WP_User {
    public $ID; public $roles; public $display_name = ''; public $user_login = ''; public $user_email = '';
    function __construct($id, $r) { $this->ID = $id; $this->roles = $r; }
    function exists() { return $this->ID > 0; }
}
class WP_Error { public $m; function __construct($c = '', $m = '') { $this->m = $m; } function get_error_message() { return $this->m; } }
function add_action(...$a) {} function add_filter(...$a) {} function register_post_status(...$a) {} function _n_noop($a, $b) { return [$a, $b]; }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES); } function esc_attr($s) { return esc_html($s); } function esc_url($s) { return (string) $s; }
function home_url($p = '') { return 'https://merzenich-aktuell.de' . $p; }
function is_wp_error($x) { return $x instanceof WP_Error; }
function wp_is_post_revision($id) { return false; }
function sanitize_key($s) { return strtolower(preg_replace('/[^a-z0-9_\-]/i', '', (string) $s)); }

// Kleine Datenbank: Beiträge, Beitrags- und Benutzer-Meta, Benutzer.
$GLOBALS['posts'] = []; $GLOBALS['pmeta'] = []; $GLOBALS['umeta'] = []; $GLOBALS['users'] = []; $GLOBALS['ich'] = 0;
function neuer_beitrag(array $a): WP_Post { $p = new WP_Post($a); $GLOBALS['posts'][$p->ID] = $p; return $p; }
function get_post($id) { return $GLOBALS['posts'][(int) $id] ?? null; }
function get_post_type($id) { return ($p = get_post($id)) ? $p->post_type : false; }
function get_post_status($id) { return ($p = get_post($id)) ? $p->post_status : false; }
function get_the_title($id) { $p = $id instanceof WP_Post ? $id : get_post($id); return $p ? $p->post_title : ''; }
function get_post_stati() { return ['publish', 'future', 'draft', 'pending', 'private', 'trash', 'auto-draft', 'inherit', 'ma_in_pruefung', 'ma_aenderung', 'ma_archiv', 'ma_abgelehnt']; }
function get_post_status_object($s) { return (object) ['label' => ['draft' => 'Entwurf', 'ma_in_pruefung' => 'In Prüfung'][$s] ?? $s]; }
function selected($a, $b, $echo = true) { return (string) $a === (string) $b ? ' selected="selected"' : ''; }
function sanitize_text_field($s) { return trim((string) $s); } function wp_unslash($s) { return $s; }
function get_metadata_by_mid($typ, $mid) { foreach ($GLOBALS['pmeta'] as $pid => $m) foreach ($m as $k => $v) if (crc32($pid . $k) === $mid) return (object) ['post_id' => $pid, 'meta_key' => $k]; return false; }
function get_post_meta($id, $k, $single = false) { return $GLOBALS['pmeta'][(int) $id][$k] ?? ''; }
// Wie WordPress: update/add laufen durch den Filter (hier die Feldsperre aus redaktion.php).
function update_post_meta($id, $k, $v) { if (ma_redaktion_meta_sperre(null, $id, $k) === false) return false; $GLOBALS['pmeta'][(int) $id][$k] = $v; return true; }
function delete_post_meta($id, $k) { unset($GLOBALS['pmeta'][(int) $id][$k]); return true; }
function get_user_meta($id, $k, $single = false) { return $GLOBALS['umeta'][(int) $id][$k] ?? ''; }
function update_user_meta($id, $k, $v) { $GLOBALS['umeta'][(int) $id][$k] = $v; return true; }
function delete_user_meta($id, $k) { unset($GLOBALS['umeta'][(int) $id][$k]); return true; }
function get_userdata($id) { return $GLOBALS['users'][(int) $id] ?? false; }
function wp_get_current_user() { return $GLOBALS['users'][$GLOBALS['ich']] ?? new WP_User(0, []); }
function get_current_user_id() { return $GLOBALS['ich']; }
function wp_insert_post(array $a, $fehler = false) { $id = max(array_keys($GLOBALS['posts']) ?: [0]) + 1; neuer_beitrag(['ID' => $id] + $a); return $id; }
function get_posts(array $q) {
    $l = array_values(array_filter($GLOBALS['posts'], function (WP_Post $p) use ($q) {
        if (isset($q['post_type']) && !in_array($p->post_type, (array) $q['post_type'], true)) return false;
        if (isset($q['author']) && (int) $p->post_author !== (int) $q['author']) return false;
        if (isset($q['post_status']) && $q['post_status'] !== 'any' && !in_array($p->post_status, (array) $q['post_status'], true)) return false;
        if (isset($q['meta_key'], $q['meta_value']) && (string) get_post_meta($p->ID, $q['meta_key']) !== (string) $q['meta_value']) return false;
        foreach ((array) ($q['meta_query'] ?? []) as $m) {
            $c = $m['compare'] ?? (isset($m['value']) ? '=' : 'EXISTS'); $w = get_post_meta($p->ID, $m['key']);
            if (($c === 'NOT EXISTS' && $w !== '') || ($c === 'EXISTS' && $w === '') || ($c === '=' && (string) $w !== (string) $m['value'])) return false;
        }
        return true;
    }));
    return ($q['fields'] ?? '') === 'ids' ? array_map(fn($p) => $p->ID, $l) : $l;
}

require MA_CORE_PATH . 'includes/partners.php';
require MA_CORE_PATH . 'includes/business.php';
require MA_CORE_PATH . 'includes/redaktion.php';
require MA_CORE_PATH . 'includes/unternehmenskanal.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-72s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

// Zugang 7 (Bäckerei), Zugang 8 (andere Firma), Redaktion 1.
$GLOBALS['users'][1] = new WP_User(1, ['administrator']);
$GLOBALS['users'][7] = new WP_User(7, ['ma_wirtschaft_partner']); $GLOBALS['users'][7]->display_name = 'Bäckerei Beispiel';
$GLOBALS['users'][8] = new WP_User(8, ['ma_wirtschaft_partner']);
neuer_beitrag(['ID' => 50, 'post_type' => 'ma_business', 'post_status' => 'publish', 'post_author' => 1, 'post_title' => 'Bäckerei Beispiel', 'post_name' => 'baeckerei-beispiel']);
neuer_beitrag(['ID' => 51, 'post_type' => 'ma_business', 'post_status' => 'publish', 'post_author' => 1, 'post_title' => 'Andere Firma', 'post_name' => 'andere-firma']);
neuer_beitrag(['ID' => 101, 'post_type' => 'post', 'post_status' => 'draft', 'post_author' => 7, 'post_title' => 'Eigener Entwurf']);
neuer_beitrag(['ID' => 102, 'post_type' => 'post', 'post_status' => 'publish', 'post_author' => 7, 'post_title' => 'Eigener, veröffentlicht']);
neuer_beitrag(['ID' => 103, 'post_type' => 'post', 'post_status' => 'draft', 'post_author' => 8, 'post_title' => 'Fremder Entwurf']);

echo "Rechte eines Firmenzugangs (partners.php)\n";
$cap = fn(string $c, int $id) => ma_partner_map_meta_cap(['edit_posts'], $c, 7, [$id]);
pruefe('Eigener Entwurf: bearbeiten erlaubt', $cap('edit_post', 101), ['edit_posts']);
pruefe('Fremder Entwurf: gesperrt', $cap('edit_post', 103), ['do_not_allow']);
pruefe('Eigener veröffentlichter Beitrag: nicht bearbeiten', $cap('edit_post', 102), ['do_not_allow']);
pruefe('Eigener veröffentlichter Beitrag: lesen erlaubt', $cap('read_post', 102), ['edit_posts']);
pruefe('Fremdes Unternehmensprofil (Autor Redaktion): gesperrt', $cap('edit_post', 50), ['do_not_allow']);

echo "\nZuordnung zum Kanal nur durch System oder Redaktion\n";
pruefe('ma_business_id gehört zu den Redaktionsfeldern', in_array('ma_business_id', ma_redaktion_nur_felder(), true), true);
$GLOBALS['ich'] = 7;
pruefe('Zugang kann die Zuordnung nicht selbst setzen', update_post_meta(101, 'ma_business_id', '51'), false);
$GLOBALS['ma_system_schreibt'] = true;
pruefe('Systemseitige Zuordnung ist erlaubt', ma_redaktion_meta_sperre(null, 101, 'ma_business_id'), null);
unset($GLOBALS['ma_system_schreibt']);
// Schreibwege über die Meta-ID (Individuelle Felder, admin-ajax add-meta, XML-RPC).
pruefe('Kein Recht edit_post_meta auf ma_business_id für den Zugang', ma_redaktion_meta_recht(true, 'ma_business_id', 101, 7), false);
pruefe('Redaktion behält das Recht', ma_redaktion_meta_recht(true, 'ma_business_id', 101, 1), true);
$GLOBALS['pmeta'][101]['ma_organisation'] = 'Bäckerei';
pruefe('Andere Zeile per Meta-ID in ma_business_id umbenennen: gesperrt', ma_redaktion_meta_sperre_mid(null, crc32('101ma_organisation'), '51', 'ma_business_id'), false);
$GLOBALS['pmeta'][101]['notiz'] = 'x';
pruefe('Freies Feld per Meta-ID ändern bleibt möglich', ma_redaktion_meta_sperre_mid(null, crc32('101notiz'), 'y', 'notiz'), null);
unset($GLOBALS['pmeta'][101]['ma_organisation'], $GLOBALS['pmeta'][101]['notiz']);

echo "\nNeue Beiträge eines verknüpften Zugangs\n";
ma_business_beitrag_zuordnen(101, get_post(101));
pruefe('Ohne Verknüpfung keine Zuordnung', get_post_meta(101, 'ma_business_id'), '');
update_user_meta(7, 'ma_business_id', '50');
pruefe('Profil des Zugangs', ma_business_von_benutzer(7), 50);
ma_business_beitrag_zuordnen(101, get_post(101));
pruefe('Eigener Beitrag bekommt das Profil', get_post_meta(101, 'ma_business_id'), '50');
ma_business_beitrag_zuordnen(103, get_post(103));
pruefe('Fremder Beitrag bleibt ohne Zuordnung', get_post_meta(103, 'ma_business_id'), '');
neuer_beitrag(['ID' => 104, 'post_type' => 'ma_event', 'post_status' => 'draft', 'post_author' => 7]);
ma_business_beitrag_zuordnen(104, get_post(104));
pruefe('Termine gehören nicht in den Kanal', get_post_meta(104, 'ma_business_id'), '');
pruefe('Kanaladresse aus dem Profil', ma_business_kanal_url(50), 'https://merzenich-aktuell.de/unternehmen/baeckerei-beispiel/');
update_user_meta(8, 'ma_business_id', '103');
pruefe('Verknüpfung auf einen Beitrag statt Profil zählt nicht', ma_business_von_benutzer(8), 0);

echo "\nÄnderungsvorschlag am eigenen Profil (redaktion.php)\n";
pruefe('Verknüpftes, veröffentlichtes Profil: Änderung vorschlagen', ma_aenderung_erlaubt(get_post(50), get_userdata(7)), true);
pruefe('Profil einer anderen Firma: nein', ma_aenderung_erlaubt(get_post(51), get_userdata(7)), false);
pruefe('Ohne Verknüpfung: nein', ma_aenderung_erlaubt(get_post(50), get_userdata(8)), false);
$felder = ma_aenderung_felder('ma_business');
pruefe('Profilfelder und Einwilligung wandern mit', [in_array('ma_business_branche', $felder, true), in_array('ma_business_oeffnungszeiten', $felder, true), in_array('ma_business_consent', $felder, true)], [true, true, true]);

echo "\nZugang mit Profil verknüpfen (Partner-Zugänge)\n";
$GLOBALS['ich'] = 1;
$GLOBALS['users'][9] = new WP_User(9, ['ma_wirtschaft_partner']);
neuer_beitrag(['ID' => 120, 'post_type' => 'post', 'post_status' => 'pending', 'post_author' => 9, 'post_title' => 'Schon eingereicht']);
neuer_beitrag(['ID' => 121, 'post_type' => 'post', 'post_status' => 'ma_in_pruefung', 'post_author' => 9, 'post_title' => 'In Prüfung']);
$neu = ma_partner_business_verknuepfen(9, 'neu', 'Café Beispiel');
pruefe('Neues Profil als Entwurf, Autor ist der Zugang', [get_post($neu)->post_type, get_post($neu)->post_status, (int) get_post($neu)->post_author, get_post($neu)->post_title], ['ma_business', 'draft', 9, 'Café Beispiel']);
pruefe('Zugang zeigt auf das neue Profil', ma_business_von_benutzer(9), $neu);
pruefe('Bisherige Beiträge werden nachgetragen', get_post_meta(120, 'ma_business_id'), (string) $neu);
pruefe('Auch Beiträge „In Prüfung“', get_post_meta(121, 'ma_business_id'), (string) $neu);
pruefe('Auswahl zeigt nicht veröffentlichte Profile mit Status', ma_business_auswahl()[$neu] ?? null, 'Café Beispiel (Entwurf)');
$_POST = ['ma_business_id' => (string) $neu, 'ma_business_id_alt' => (string) $neu];
pruefe('Speichern ohne Änderung lässt die Verknüpfung stehen', ma_business_auswahl_geaendert('ma_business_id'), false);
$_POST = ['ma_business_id' => '', 'ma_business_id_alt' => (string) $neu];
pruefe('Bewusst „kein Profil“ gewählt: Änderung', ma_business_auswahl_geaendert('ma_business_id'), true);
$_POST = [];
get_post($neu)->post_status = 'ma_in_pruefung';
pruefe('Aktuelles Profil steht in der Auswahl, auch wenn nicht gelistet', str_contains(ma_business_auswahl_html('ma_business_id', $neu, '– kein Profil –'), 'value="' . $neu . '" selected="selected"'), true);
pruefe('Vorhandenes Profil wählen', ma_partner_business_verknuepfen(9, '51'), 51);
pruefe('Kein Unternehmensprofil (Beitrag) wird abgelehnt', ma_partner_business_verknuepfen(9, '101'), 0);
pruefe('Leer löst die Verknüpfung', [ma_partner_business_verknuepfen(9, ''), ma_business_von_benutzer(9)], [0, 0]);
$GLOBALS['ich'] = 7;
pruefe('Ein Zugang darf selbst nichts verknüpfen (Feldsperre greift bei Beiträgen)', update_post_meta(102, 'ma_business_id', '51'), false);

echo "\nKanal zeigt nur gesponserte Beiträge\n";
$GLOBALS['pmeta'][102]['ma_business_id'] = '50';
pruefe('Ohne Kennzeichnung „gesponsert“ nicht im Kanal', array_map(fn($p) => $p->ID, ma_business_beitraege(50)), []);
$GLOBALS['pmeta'][102]['ma_gesponsert'] = '1';
pruefe('Gesponsert: im Kanal', array_map(fn($p) => $p->ID, ma_business_beitraege(50)), [102]);

echo "\nÄnderungsvorschlag übernehmen: Bild nur bei echter Änderung (Review 17.10.2026)\n";
function get_post_thumbnail_id($id) { return (int) ($GLOBALS['pmeta'][(int) $id]['_thumbnail_id'] ?? 0); }
function set_post_thumbnail($id, $b) { $GLOBALS['pmeta'][(int) $id]['_thumbnail_id'] = (int) $b; return true; }
function delete_post_thumbnail($id) { unset($GLOBALS['pmeta'][(int) $id]['_thumbnail_id']); return true; }
function wp_update_post(array $a) { foreach ($a as $k => $v) if ($k !== 'ID') $GLOBALS['posts'][(int) $a['ID']]->$k = $v; return (int) $a['ID']; }
function wp_delete_post($id, $f = false) { unset($GLOBALS['posts'][(int) $id], $GLOBALS['pmeta'][(int) $id]); return true; }
function is_object_in_taxonomy($t, $tax) { return false; }
function add_post_meta($id, $k, $v) { return true; }
function get_permalink($id) { return 'https://merzenich-aktuell.de/?p=' . (int) $id; }
function is_email($e) { return false; }
$GLOBALS['ich'] = 1;
// Termin mit Symbolbild 500, das der tägliche Lauf inzwischen auf 600 umgestellt hat; der Vorschlag ändert nur den Text.
neuer_beitrag(['ID' => 300, 'post_type' => 'ma_event', 'post_status' => 'publish', 'post_title' => 'Seniorentreff', 'post_author' => 7]);
$GLOBALS['pmeta'][300] = ['_thumbnail_id' => 600, 'ma_image_credit' => 'Foto B', 'ma_image_license' => 'CC BY 4.0', 'ma_image_type' => 'symbol', 'ma_image_original_url' => 'https://commons.wikimedia.org/wiki/File:B.jpg', 'ma_image_rights_verified' => '1'];
neuer_beitrag(['ID' => 301, 'post_type' => 'ma_event', 'post_status' => 'pending', 'post_title' => 'Seniorentreff im Pfarrheim', 'post_author' => 7]);
$GLOBALS['pmeta'][301] = [MA_AENDERUNG_VON => '300', '_ma_aenderung_bild_vorher' => '500', '_thumbnail_id' => 500, 'ma_image_credit' => 'Foto A', 'ma_image_license' => 'CC0', 'ma_image_type' => 'symbol'];
ma_aenderung_uebernehmen(301);
pruefe('Text übernommen', get_post(300)->post_title, 'Seniorentreff im Pfarrheim');
pruefe('Bild unverändert vorgeschlagen: neu verteiltes Bild und Nachweis bleiben', [get_post_thumbnail_id(300), $GLOBALS['pmeta'][300]['ma_image_credit'], $GLOBALS['pmeta'][300]['ma_image_original_url'], $GLOBALS['pmeta'][300]['ma_image_rights_verified'] ?? ''], [600, 'Foto B', 'https://commons.wikimedia.org/wiki/File:B.jpg', '1']);
neuer_beitrag(['ID' => 302, 'post_type' => 'ma_event', 'post_status' => 'pending', 'post_title' => 'Seniorentreff', 'post_author' => 7]);
$GLOBALS['pmeta'][302] = [MA_AENDERUNG_VON => '300', '_ma_aenderung_bild_vorher' => '600', '_thumbnail_id' => 700, 'ma_image_credit' => 'Verein', 'ma_image_type' => 'original'];
ma_aenderung_uebernehmen(302);
pruefe('Neues Bild vorgeschlagen: Bild, Nachweis, Typ; alte Quelle und Rechteprüfung fallen weg', [get_post_thumbnail_id(300), $GLOBALS['pmeta'][300]['ma_image_credit'] ?? '', $GLOBALS['pmeta'][300]['ma_image_type'] ?? '', $GLOBALS['pmeta'][300]['ma_image_original_url'] ?? '', $GLOBALS['pmeta'][300]['ma_image_rights_verified'] ?? ''], [700, 'Verein', 'original', '', '']);

echo $fehler ? "\n$fehler Fehler.\n" : "\nAlle Pruefungen bestanden.\n";
exit($fehler ? 1 : 0);
