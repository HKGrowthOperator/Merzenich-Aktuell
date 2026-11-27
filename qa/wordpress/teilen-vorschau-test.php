<?php
/**
 * Vorschaubild beim Teilen (ma_seo_bild, includes/seo.php): genau das Bild, das der Beitrag zeigt,
 * auch ohne Rechte-Haken; ohne Beitragsbild das freigegebene Firmenlogo, sonst das Standardbild.
 * Vorgabe Betreiber 27.11.2026. Aufruf: php qa/wordpress/teilen-vorschau-test.php
 */
define('ABSPATH', __DIR__); define('MA_CORE_VERSION', 'test');
function add_action(...$a) {} function add_filter(...$a) {}
function home_url($p = '') { return 'https://merzenich-aktuell.de' . $p; }

$GLOBALS['thumb'] = [10 => 500];
$GLOBALS['meta'] = [500 => ['_wp_attachment_image_alt' => 'Feuerwehr am Einsatzort']];
function get_post($p) { return is_object($p) ? $p : ($GLOBALS['posts'][$p] ?? null); }
function get_post_thumbnail_id($p) { $id = is_object($p) ? $p->ID : $p; return $GLOBALS['thumb'][$id] ?? 0; }
function wp_get_attachment_image_src($id, $g) { return [500 => ['https://merzenich-aktuell.de/wp-content/uploads/a.jpg', 1600, 900], 700 => ['https://merzenich-aktuell.de/wp-content/uploads/logo.png', 800, 400]][$id] ?? false; }
function get_post_meta($id, $k = '', $s = false) { return $GLOBALS['meta'][$id][$k] ?? ''; }
function get_the_title($p) { $p = get_post($p); return $p ? $p->post_title : ''; }
function ma21_bild_firmenlogo($p) { return $p->ID === 11 ? ['id' => 700] : null; }
// Wäre die alte Regel noch aktiv, fiele der Beitrag ohne Rechte-Haken auf das Standardbild zurück.
function ma_content_image($p, $s = 'large') { return ['is_fallback' => true]; }
class WP_Post { public $ID; public $post_type = 'post'; public $post_name = ''; public $post_title = ''; function __construct($id, $typ = 'post', $name = '') { $this->ID = $id; $this->post_type = $typ; $this->post_name = $name; } }
class WP_Term { public $term_id = 0; public $taxonomy = ''; }
$GLOBALS['posts'] = [10 => new WP_Post(10), 11 => new WP_Post(11), 12 => new WP_Post(12)];
$GLOBALS['posts'][10]->post_title = 'Ohne Haken'; $GLOBALS['posts'][11]->post_title = 'Firma'; $GLOBALS['posts'][12]->post_title = 'Ohne Bild';
$GLOBALS['wp_query'] = null;
function is_singular($t = '') { return true; }
function get_query_var($k, $d = '') { return $d; }
function attachment_url_to_postid($u) { return 0; }
function get_the_terms($id, $tax) { return false; }
function get_posts($a) { return [new WP_Post(10)]; }
function get_post_status($id) { return 'publish'; }
function get_category_by_slug($s) { return null; }
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/seo.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-66s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
pruefe('Beitragsbild ohne Rechte-Haken: Vorschau zeigt das Bild der Seite', ma_seo_bild(10)['url'], 'https://merzenich-aktuell.de/wp-content/uploads/a.jpg');
pruefe('Alt-Text des Bildes geht mit', ma_seo_bild(10)['alt'], 'Feuerwehr am Einsatzort');
pruefe('Firmenbeitrag ohne Foto: freigegebenes Firmenlogo', ma_seo_bild(11)['url'], 'https://merzenich-aktuell.de/wp-content/uploads/logo.png');
pruefe('Ohne jedes Bild: Standardbild', ma_seo_bild(12)['url'], 'https://merzenich-aktuell.de/assets/img/og-default.jpg');
$verein = new WP_Post(20, 'ma_club', 'sc'); $verein->post_title = 'SC 1919 Merzenich';
$GLOBALS['meta'][20] = ['ma_club_logo' => 'https://merzenich-aktuell.de/wp-content/uploads/sc-logo.webp'];
$GLOBALS['posts'][20] = $verein;
pruefe('Verein ohne Bild: Vereinslogo', ma_seo_ersatzbild(['typ' => 'verein'], $verein)['url'], 'https://merzenich-aktuell.de/wp-content/uploads/sc-logo.webp');
pruefe('Seite ohne Bild (z. B. Impressum): Bild einer aktuellen Meldung statt Standardbild', ma_seo_ersatzbild(['typ' => 'seite'], new WP_Post(30, 'page', 'impressum'))['url'], 'https://merzenich-aktuell.de/wp-content/uploads/a.jpg');
echo $fehler ? "$fehler Fehler\n" : "Alle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
