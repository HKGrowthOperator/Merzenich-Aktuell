<?php
/**
 * Echte Firma verdrängt Beispielfirma (inc/unternehmen.php ma21_muster_liste,
 * Review 15.10.2026): Ein echtes Profil mit dem Namen einer Beispielfirma bekommt
 * die Adresse <slug>-firma (wp_unique_post_slug). Auch dann verschwindet die
 * Beispielfirma, damit nie eine erfundene neben der echten Firma steht.
 * Eigener Lauf, weil ma21_muster_liste je Aufruf zwischenspeichert.
 * Aufruf: php qa/wordpress/unternehmen-belegt-test.php
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
function wp_date($f) { return '2026-10-13T12:00:00+02:00'; }
function get_transient($k) { return false; } function set_transient($k, $v, $t) { return true; }
function get_term_by(...$a) { return false; } function get_category_by_slug($s) { return false; }
// Veröffentlichte echte Profile (ma_business) nach Adresse.
$GLOBALS['profile'] = [];
function get_posts($q) { if (($q['post_type'] ?? '') !== 'ma_business') return []; return array_values(array_map(fn($n) => (object) ['post_name' => $n], array_intersect($GLOBALS['profile'], (array) ($q['post_name__in'] ?? [])))); } function get_users($q) { return []; }
function get_query_var($k) { return $GLOBALS['qv'][$k] ?? ''; }
function get_header() {} function get_footer() {}
function ma_seo_kuerzen($t, $max = 160) { return mb_substr((string) $t, 0, $max); }
class WP_Term { public $count = 0; } class WP_Query {} class WP_Post {}
require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/ma21.php';
require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/markt.php';
require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/unternehmen.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Echtes Profil „Café Feldlerche“ unter /unternehmen/cafe-feldlerche-firma/\n";
$GLOBALS['profile'] = ['cafe-feldlerche-firma', 'baeckerei-mustermann'];
pruefe('Beispielfirma Café Feldlerche verschwindet, die anderen bleiben', array_column(ma21_muster_liste(), 'slug'), ['backhaus-morgenrot', 'holzwerkstatt-jahresring']);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
