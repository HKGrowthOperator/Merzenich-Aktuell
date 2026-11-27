<?php
/**
 * Vorschaubild beim Teilen (ma_seo_bild, includes/seo.php): genau das Bild, das der Beitrag zeigt,
 * auch ohne Rechte-Haken; ohne Beitragsbild das freigegebene Firmenlogo, sonst das Standardbild.
 * Vorgabe Betreiber 27.11.2026. Aufruf: php qa/wordpress/teilen-vorschau-test.php
 */
define('ABSPATH', __DIR__); define('MA_CORE_VERSION', 'test');
function add_action(...$a) {} function add_filter(...$a) {}
function home_url($p = '') { return 'https://merzenich-aktuell.de' . $p; }
$GLOBALS['posts'] = [10 => (object) ['ID' => 10, 'post_title' => 'Ohne Haken'], 11 => (object) ['ID' => 11, 'post_title' => 'Firma'], 12 => (object) ['ID' => 12, 'post_title' => 'Ohne Bild']];
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
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/seo.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-66s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
pruefe('Beitragsbild ohne Rechte-Haken: Vorschau zeigt das Bild der Seite', ma_seo_bild(10)['url'], 'https://merzenich-aktuell.de/wp-content/uploads/a.jpg');
pruefe('Alt-Text des Bildes geht mit', ma_seo_bild(10)['alt'], 'Feuerwehr am Einsatzort');
pruefe('Firmenbeitrag ohne Foto: freigegebenes Firmenlogo', ma_seo_bild(11)['url'], 'https://merzenich-aktuell.de/wp-content/uploads/logo.png');
pruefe('Ohne jedes Bild: Standardbild', ma_seo_bild(12)['url'], 'https://merzenich-aktuell.de/assets/img/og-default.jpg');
echo $fehler ? "$fehler Fehler\n" : "Alle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
