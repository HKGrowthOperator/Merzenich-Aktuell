<?php
/**
 * Firmenlogos (20.10.2026, Theme 21.20.6): Anzeigen eines Unternehmens ohne eigenes
 * Foto zeigen das freigegebene Logo (ma21_bild_firmenlogo), ganz auf hellem Grund
 * (Klasse bild-logo, firmenlogo-box, ressort-firmen__logo--bild), nie bei offenen
 * Logo-Rechten und nie bei normalen Meldungen.
 * Aufruf: php qa/wordpress/firmenlogo-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('DAY_IN_SECONDS', 86400);
$wurzel = dirname(__DIR__, 2);
function add_action(...$a) {} function add_filter(...$a) {} function remove_action(...$a) {} function add_image_size(...$a) {}
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s, $p = null) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function home_url($p = '') { return 'https://merzenich-aktuell.de' . $p; }
function get_template_directory() { return dirname(__DIR__, 2) . '/wordpress/theme/merzenich-aktuell'; }
class WP_Post { public $ID = 0; public $post_type = 'post'; public $post_title = ''; function __construct($id, $typ = 'post') { $this->ID = $id; $this->post_type = $typ; } }
$GLOBALS['meta'] = []; $GLOBALS['bild'] = []; $GLOBALS['typ'] = [];
function get_post_meta($id, $k, $s = false) { return $GLOBALS['meta'][$id][$k] ?? ''; }
function get_post_thumbnail_id($id) { return (int) ($GLOBALS['bild'][$id] ?? 0); }
function get_post_type($id) { return $GLOBALS['typ'][$id] ?? 'post'; }
function get_the_title($id) { return is_object($id) ? 'Meldung' : ($id === 100 ? 'Bäckerei Wirtz' : 'Firma'); }
function wp_get_attachment_image_src($id, $g = 'full') { return $id === 901 ? ['https://merzenich-aktuell.de/wp-content/uploads/2026/10/baeckerei-wirtz.png', 500, 500] : false; }
function wp_get_attachment_image_srcset(...$a) { return ''; }
function ma_credit_kurz($c, $l) { return (string) $c; }
require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/ma21.php';
require $wurzel . '/wordpress/theme/merzenich-aktuell/inc/unternehmen.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

// Profil 100 mit Logo 901; Anzeige 10 ohne eigenes Foto.
$GLOBALS['typ'][100] = 'ma_business'; $GLOBALS['bild'][100] = 901;
$GLOBALS['meta'][100] = ['ma_business_logo_rechte' => 'geklaert'];
$GLOBALS['meta'][901] = ['ma_image_credit' => 'Logo: Bäckerei Wirtz, Nutzung mit Freigabe des Unternehmens'];
$GLOBALS['meta'][10] = ['ma_gesponsert' => '1', 'ma_business_id' => '100'];

echo "Logo als Bild einer Anzeige\n";
$b = ma21_bild(new WP_Post(10));
pruefe('Anzeige ohne Foto: Logo des Unternehmens', [$b['src'] ?? '', $b['logo'] ?? false, $b['alt'] ?? '', $b['typ'] ?? 'x'], ['https://merzenich-aktuell.de/wp-content/uploads/2026/10/baeckerei-wirtz.png', true, 'Logo Bäckerei Wirtz', '']);
pruefe('Bild trägt die Klasse bild-logo (ganz zeigen)', str_contains(ma21_img($b, '100vw', false), 'class="bild-logo"'), true);
pruefe('Normales Foto ohne die Klasse', str_contains(ma21_img(['src' => '/x.jpg', 'srcset' => '', 'alt' => '', 'w' => 1, 'h' => 1], '100vw', false), 'class=""'), true);
$GLOBALS['meta'][100]['ma_business_logo_rechte'] = 'offen';
pruefe('Logo-Rechte offen: kein Bild', ma21_bild(new WP_Post(10)), null);
$GLOBALS['meta'][100]['ma_business_logo_rechte'] = 'geklaert';
$GLOBALS['meta'][11] = ['ma_business_id' => '100'];
pruefe('Nicht gesponsert: kein Logo', ma21_bild(new WP_Post(11)), null);
$GLOBALS['meta'][12] = ['ma_gesponsert' => '1'];
pruefe('Gesponsert ohne Profil: kein Logo', ma21_bild(new WP_Post(12)), null);
$GLOBALS['bild'][10] = 950; // eigenes Beitragsbild: kein Logo-Ersatz (hier ohne Datei, also kein Bild)
pruefe('Eigenes Beitragsbild geht vor: kein Logo-Ersatz', ma21_bild(new WP_Post(10)), null);
unset($GLOBALS['bild'][10]);

echo "Logo-Kasten\n";
$f = ['logo' => 'https://merzenich-aktuell.de/logo.png', 'farbe' => '#000', 'kuerzel' => 'BW'];
pruefe('Logo: heller Kasten, kein Kürzel', str_contains(ma21_firma_logo_html($f, 'u-kanal__logo'), 'class="u-kanal__logo firmenlogo-box"'), true);
pruefe('Ohne Logo: Kürzel auf Firmenfarbe', str_contains(ma21_firma_logo_html(['logo' => '', 'farbe' => '#123456', 'kuerzel' => 'BW'], 'u-kanal__logo'), 'background:#123456'), true);
pruefe('Menübild trägt das Logo-Merkmal', ma21_menue_bildnachweis(['klein' => '/l.png', 'alt' => 'Logo', 'foto' => '', 'lizenz' => '', 'lizenz_url' => '', 'logo' => true])['logo'] ?? false, true);

echo "Menü und CSS\n";
$js = (string) file_get_contents($wurzel . '/chatgpt-site/assets/ressort-dropdowns.js');
$css = (string) file_get_contents($wurzel . '/chatgpt-site/assets/ressort-dropdowns.css');
$pl = (string) file_get_contents($wurzel . '/wordpress/theme/merzenich-aktuell/assets/css/plattform.css');
pruefe('Menü: Logo im Kasten, Karte mit Logo ohne „Foto:“-Nachweis', [str_contains($js, 'ressort-firmen__logo--bild'), str_contains($js, 'ressort-firmen__bild--logo'), str_contains($js, '!b.bild.logo ? nachweis(')], [true, true, true]);
pruefe('CSS: Logos mit contain, Regeln schlagen die Grundregeln (doppelte Klasse)', [str_contains($css, '.ressort-firmen__logo.ressort-firmen__logo--bild img{width:auto;height:auto;max-width:100%;max-height:100%;object-fit:contain}'), str_contains($css, '.ressort-firmen__bild.ressort-firmen__bild--logo img,'), str_contains($pl, 'img.bild-logo,.media--logo img{object-fit:contain!important')], [true, true, true]);
pruefe('CSS: Logo-Kasten fest 88 × 44, Bild darin ganz (kein Abschneiden des runden Wirtz-Logos)', [str_contains($pl, 'width:88px!important;height:44px!important'), str_contains($pl, '.firmenlogo-box img{width:auto!important;height:auto!important;max-width:100%;max-height:100%')], [true, true]);
pruefe('CSS: Firmenzeile mit Logo bekommt eine 72-px-Spalte (kein Überlappen)', str_contains($css, '.ressort-firmen__firma:has(.ressort-firmen__logo--bild){grid-template-columns:72px minmax(0,1fr)}'), true);
pruefe('„Neu von Unternehmen“: Logo-Karte mit eigener Klasse', str_contains($js, "item.bild.logo ? ' ressort-dropdown__story-image--logo'"), true);

echo "Logo-Dateien im Plugin\n";
$d = json_decode((string) file_get_contents($wurzel . '/wordpress/plugin/merzenich-aktuell-core/data/firmen-entwuerfe.json'), true);
foreach ($d['firmen'] as $x) {
    $datei = $wurzel . '/wordpress/plugin/merzenich-aktuell-core/data/logos/' . ($x['profil']['logo']['datei'] ?? '-');
    $info = is_file($datei) ? getimagesize($datei) : false;
    pruefe($x['profil']['titel'] . ': Logo-Datei als PNG oder JPEG, mindestens 200 px breit', $info !== false && in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true) && $info[0] >= 200, true);
}

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
