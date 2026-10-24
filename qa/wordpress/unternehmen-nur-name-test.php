<?php
/**
 * Unternehmen ohne Beitrag (Theme 21.21.0, Auftrag Betreiber 24.10.2026,
 * inc/unternehmen.php): Ein als Kanal angelegtes Profil (ma_business_kanal)
 * erscheint im Menü und in der Übersicht auch ohne Beitrag; die
 * Beispielfirmen verschwinden schon bei einer echten Firma. Profile nur mit
 * Namen bekommen keinen erfundenen Ort; KBS trägt den Hinweis
 * „Herausgeberin von Merzenich Aktuell“.
 * Eigener Lauf, weil ma21_kanal_ids und ma21_echte_firmen je Aufruf zwischenspeichern.
 * Aufruf: php qa/wordpress/unternehmen-nur-name-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('DAY_IN_SECONDS', 86400);
$wurzel = dirname(__DIR__, 2);
function add_action(...$a) {} function add_filter(...$a) {} function remove_action(...$a) {} function add_image_size(...$a) {}
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s, $p = null) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function home_url($p = '') { return 'https://merzenich-aktuell.de' . $p; }
function trailingslashit($s) { return rtrim($s, '/') . '/'; }
function get_template_directory() { return dirname(__DIR__, 2) . '/wordpress/theme/merzenich-aktuell'; }
function sanitize_title($s) { return strtolower(preg_replace('/[^a-z0-9\-]/i', '', (string) $s)); }
function wp_make_link_relative($u) { return (string) preg_replace('#^https?://[^/]+#', '', $u); }
function wp_date($f) { return '2026-10-24T12:00:00+02:00'; }
function get_transient($k) { return false; } function set_transient($k, $v, $t) { return true; }
function get_term_by(...$a) { return false; } function get_category_by_slug($s) { return false; }
function get_query_var($k) { return $GLOBALS['qv'][$k] ?? ''; }
function get_header() {} function get_footer() {}
function ma_seo_kuerzen($t, $max = 160) { return mb_substr((string) $t, 0, $max); }
class WP_Term { public $count = 0; } class WP_Query {}
class WP_Post { public $ID; public $post_type = 'ma_business'; public $post_name; public $post_title; public $post_excerpt = ''; public $post_content = '';
    function __construct($id, $name, $titel, $excerpt) { $this->ID = $id; $this->post_name = $name; $this->post_title = $titel; $this->post_excerpt = $excerpt; } }

// Zwei veröffentlichte Profile aus den Firmen-Entwürfen, beide ohne Beitrag.
$GLOBALS['p'] = [
    20 => new WP_Post(20, 'kbs-management', 'KBS Management GmbH', 'Beratungsunternehmen aus Leverkusen.'),
    21 => new WP_Post(21, 'merzlicht', 'Merzlicht', 'Merzlicht ist auf Merzenich Aktuell mit Anzeigen vertreten.'),
];
$GLOBALS['meta'] = [
    20 => ['ma_business_kanal' => '1', 'ma_business_branche' => 'App- und Softwareentwicklung', 'ma_business_ort' => 'Leverkusen', 'ma_business_herausgeberin' => '1'],
    21 => ['ma_business_kanal' => '1', 'ma_business_nur_name' => '1'],
];
function get_posts($q) {
    if (($q['post_type'] ?? '') === 'ma_business' && ($q['meta_query'][0]['key'] ?? '') === 'ma_business_kanal') return array_keys($GLOBALS['p']);
    return [];
}
function get_users($q) { return []; }
function get_post($id) { return $GLOBALS['p'][$id] ?? null; }
function get_post_type($id) { return isset($GLOBALS['p'][$id]) ? 'ma_business' : 'post'; }
function get_post_status($id) { return isset($GLOBALS['p'][$id]) ? 'publish' : false; }
function get_post_meta($id, $k, $s = false) { return $GLOBALS['meta'][$id][$k] ?? ''; }
function get_the_title($p) { return is_object($p) ? $p->post_title : ($GLOBALS['p'][$p]->post_title ?? ''); }
function get_the_post_thumbnail_url($p, $g = '') { return ''; }
function get_permalink($p) { return 'https://merzenich-aktuell.de/branchenbuch/' . $p->post_name . '/'; }
function wp_strip_all_tags($s) { return strip_tags((string) $s); }
function wp_trim_words($s, $n, $m = '') { return $s; }
// Plugin-Bausteine wie in business.php / unternehmenskanal.php.
function ma_business_profile($id) { return ['branche' => get_post_meta($id, 'ma_business_branche'), 'ortsteil' => '', 'ort' => get_post_meta($id, 'ma_business_ort'),
    'nur_name' => get_post_meta($id, 'ma_business_nur_name') === '1', 'herausgeberin' => get_post_meta($id, 'ma_business_herausgeberin') === '1', 'telefon_href' => '', 'website' => '']; }
function ma_business_beitraege($id, $n = 6, $typ = 'post') { return []; }

require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/ma21.php';
require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/markt.php';
require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/unternehmen.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Kanäle ohne Beitrag\n";
pruefe('beide Profile sind Kanäle', ma21_kanal_ids(), [20, 21]);
$echt = ma21_echte_firmen();
pruefe('beide stehen im Menü, ohne Beitrag, nach Namen', array_column($echt, 'name'), ['KBS Management GmbH', 'Merzlicht']);
pruefe('Beispielfirmen sind weg (schon ab einer echten Firma)', [ma21_muster_aktiv(), MA21_MUSTER_BIS], [false, 1]);
pruefe('Menü/Übersicht: nur die echten Firmen', array_column(ma21_alle_firmen(), 'name'), ['KBS Management GmbH', 'Merzlicht']);
pruefe('Beispielfirma-Adresse antwortet nicht mehr', ma21_firmen_route('backhaus-morgenrot')['art'], '404');

echo "\nAngaben\n";
[$kbs, $merz] = $echt;
pruefe('KBS: Ort Leverkusen statt „Gemeinde Merzenich“, Zeile mit Branche', [$kbs['ort'], $kbs['zeile']], ['Leverkusen', 'App- und Softwareentwicklung · Leverkusen']);
pruefe('KBS: Hinweis Herausgeberin', $kbs['hinweis'], 'Herausgeberin von Merzenich Aktuell');
pruefe('Merzlicht: kein erfundener Ort, leere Zeile, kein Hinweis', [$merz['ort'], $merz['zeile'], $merz['hinweis']], ['', '', '']);
pruefe('Merzlicht: Kürzel statt Logo', [$merz['logo'], $merz['kuerzel']], ['', 'M']);
pruefe('Merzlicht: Fakten ohne Ort und Branche', array_keys($merz['fakten']), []);

echo "\nMenü (ressort-dropdowns.js) und Kanalseite\n";
$js = (string) file_get_contents($wurzel . '/chatgpt-site/assets/ressort-dropdowns.js');
pruefe('Firma ohne Beitrag: Hinweis statt leerer Fläche; Herausgeberin im Kanal-Kasten', [str_contains($js, 'Noch keine Beiträge von '), str_contains($js, "f.hinweis ? '<em>' + esc(f.hinweis)")], [true, true]);
$kanal = (string) file_get_contents($wurzel . '/wordpress/theme/merzenich-aktuell/unternehmen-kanal.php');
pruefe('Kanalseite nennt den Hinweis', str_contains($kanal, "\$f['hinweis']"), true);

$ez = (string) file_get_contents($wurzel . '/wordpress/theme/merzenich-aktuell/inc/einzel.php');
$mk = (string) file_get_contents($wurzel . '/wordpress/theme/merzenich-aktuell/inc/markt.php');
$sg = (string) file_get_contents($wurzel . '/wordpress/theme/merzenich-aktuell/single.php');
pruefe('Profilseite: Ort wie im Kanal, nur Name ohne Ort, Hinweis Herausgeberin', [str_contains($ez, "!empty(\$b['nur_name']) ? '' : \$ort"), str_contains($ez, 'ist die Herausgeberin von Merzenich Aktuell.')], [true, true]);
pruefe('Branchenbuch und Gemeinde-Kasten: kein erfundener Ort, nur-Name-Profile nicht „aus der Gemeinde“', [str_contains($mk, "!empty(\$f['nur_name']) ? '' :"), str_contains($mk, "'ma_business_nur_name', true) !== '1'")], [true, true]);
pruefe('Anzeigenhinweis „in eigener Sache“ ohne doppeltes „von“', str_contains($sg, "!== 'eigen' ? ' von '"), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
