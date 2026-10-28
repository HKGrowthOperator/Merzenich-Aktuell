<?php
/**
 * Werbung ohne Musteranzeigen, echte Anzeigen mit Video und Knopf (28.10.2026).
 * - ma_ad_karte (includes/ads.php): Bild, Video-Schleife nur wenn gewünscht,
 *   Zeile oben, Überschrift, Satz, Knopf, „KI-Bild“ bzw. CC-Nachweis; Links nach
 *   außen mit rel="sponsored" in neuem Fenster, interne Links (Firmenkanal) nicht.
 * - Theme: ma21_werbung liest keine Muster-Vorlagen mehr; leerer Platz ohne Anzeige.
 * - ad-rotation.js startet Videos nur sichtbar und ohne „Bewegung reduzieren“.
 * Aufruf: php qa/wordpress/werbung-ohne-muster-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('MA_CORE_URL', '/plugin/');
define('MA_CORE_VERSION', 'test');
$W = dirname(__DIR__, 2) . '/wordpress';

class WP_Post { public $ID = 0; public $post_type = 'ma_ad'; public $post_title = ''; public $post_excerpt = ''; function __construct(array $a) { foreach ($a as $k => $v) $this->$k = $v; } }
$GLOBALS['meta'] = [];
function get_post_meta($id, $k, $s = false) { return $GLOBALS['meta'][$id][$k] ?? ''; }
function get_post_thumbnail_id($id) { return (int) ($GLOBALS['meta'][$id]['_thumbnail_id'] ?? 0); }
function get_the_post_thumbnail_url($ad, $g) { $b = get_post_thumbnail_id($ad->ID); return $b ? "https://merzenich-aktuell.de/wp-content/uploads/bild-$b-1024x576.jpg" : false; }
function wp_get_attachment_url($id) { return $id ? "https://merzenich-aktuell.de/wp-content/uploads/video-$id.mp4" : false; }
function get_the_title($ad) { return $ad->post_title; }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function wp_parse_url($u, $c = -1) { return parse_url($u, $c); }
function home_url($p = '') { return 'https://merzenich-aktuell.de' . $p; }
require $W . '/plugin/merzenich-aktuell-core/includes/ads.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Anzeigenkarte\n";
$GLOBALS['meta'][5] = ['_thumbnail_id' => 50, 'ma_ad_sponsor' => 'Merzlicht · Videowall am Bürgewaldzentrum', 'ma_ad_url' => 'https://merzenich-aktuell.de/unternehmen/merzlicht/', 'ma_ad_knopf' => 'Zum Profil', 'ma_ad_video' => '51', 'ma_ad_ki' => '1'];
$GLOBALS['meta'][50] = ['_wp_attachment_image_alt' => 'LED-Videowall (KI-Bild)', 'ma_image_credit' => 'KI-Bild (Symbolbild), erstellt für Merzenich Aktuell'];
$ad = new WP_Post(['ID' => 5, 'post_title' => 'Ihre Werbung auf der Videowall.', 'post_excerpt' => 'Am Sportplatz Bürgewaldzentrum.']);
$band = ma_ad_karte($ad, 'band', true);
pruefe('Video-Schleife: stumm, Schleife, inline, erst bei Bedarf geladen, Standbild als Poster', (bool) preg_match('/<video class="ma-ad-video" muted loop playsinline preload="none" poster="[^"]+bild-50[^"]*" data-src="[^"]+video-51\.mp4" aria-hidden="true"><\/video>/', $band), true);
pruefe('Bild mit Alt-Text bleibt (ohne JavaScript und als Poster)', str_contains($band, '<img src="https://merzenich-aktuell.de/wp-content/uploads/bild-50-1024x576.jpg" alt="LED-Videowall (KI-Bild)"'), true);
pruefe('Zeile oben, Überschrift, Satz, Knopf', [str_contains($band, '<span class="ma-ad-eyebrow">Merzlicht · Videowall am Bürgewaldzentrum</span>'), str_contains($band, '<strong>Ihre Werbung auf der Videowall.</strong>'), str_contains($band, '<span class="ma-ad-text">Am Sportplatz Bürgewaldzentrum.</span>'), str_contains($band, '<span class="ma-ad-cta">Zum Profil</span>')], [true, true, true, true]);
pruefe('„KI-Bild“ auf dem Bild', str_contains($band, '<span class="ma-ad-nachweis">KI-Bild</span>'), true);
pruefe('interner Link (Firmenkanal): gleiches Fenster, kein sponsored', [str_contains($band, 'href="https://merzenich-aktuell.de/unternehmen/merzlicht/"'), str_contains($band, 'target="_blank"')], [true, false]);
pruefe('Zählung: data-ma-anzeige', str_contains($band, 'data-ma-anzeige="5"'), true);
$GLOBALS['meta'][5]['ma_ad_fokus'] = '50% 78%';
$f = ma_ad_karte($ad, 'band', true);
pruefe('Bildfokus für Bild und Video', substr_count($f, 'style="object-position:50% 78%"'), 2);
$GLOBALS['meta'][5]['ma_ad_fokus'] = '50%;background:url(x)';
pruefe('Bildfokus: nur Prozentwerte', str_contains(ma_ad_karte($ad, 'band', true), 'object-position'), false);
$GLOBALS['meta'][5]['ma_ad_fokus'] = '';
$gap = ma_ad_karte($ad, 'gap', false, true);
pruefe('kleine Plätze: kein Video, Zoom per CSS, versteckt in der Rotation', [str_contains($gap, '<video'), str_contains($gap, 'ma-ad-art--bewegt'), str_contains($gap, ' hidden')], [false, true, true]);
$GLOBALS['meta'][5]['ma_ad_url'] = 'https://www.almera-clean.de/'; $GLOBALS['meta'][5]['ma_ad_ki'] = ''; $GLOBALS['meta'][50]['ma_image_credit'] = 'Foto: Almera Clean (Website des Unternehmens)';
$aussen = ma_ad_karte($ad, 'band', true);
pruefe('externer Link: neues Fenster, rel sponsored', str_contains($aussen, 'href="https://www.almera-clean.de/" rel="sponsored noopener" target="_blank"'), true);
pruefe('Foto des Unternehmens: kein Nachweis auf dem Bild', str_contains($aussen, 'ma-ad-nachweis'), false);
$GLOBALS['meta'][50]['ma_image_credit'] = 'Symbolbild · MrChippo / Wikimedia Commons · CC BY-SA 4.0';
pruefe('CC-Foto: Lizenzangabe auf dem Bild', str_contains(ma_ad_karte($ad, 'band'), '<span class="ma-ad-nachweis">Symbolbild · MrChippo / Wikimedia Commons · CC BY-SA 4.0</span>'), true);
pruefe('Ziel überschreiben: leer = ohne Link, ohne Knopf', [str_starts_with(ma_ad_karte($ad, 'gap', false, false, ''), '<div'), str_contains(ma_ad_karte($ad, 'gap', false, false, ''), 'ma-ad-cta')], [true, false]);
$GLOBALS['meta'][5]['ma_ad_video'] = '';
pruefe('ohne Video: Zoom statt Video auch auf großen Plätzen', [str_contains(ma_ad_karte($ad, 'band', true), '<video'), str_contains(ma_ad_karte($ad, 'band', true), 'ma-ad-art--bewegt')], [false, true]);
$ad->post_title = '<script>x</script>';
pruefe('Texte escaped', str_contains(ma_ad_karte($ad, 'band'), '<script>'), false);

echo "\nKeine Musterwerbung im Theme\n";
$ma21 = (string) file_get_contents($W . '/theme/merzenich-aktuell/inc/ma21.php');
preg_match('/function ma21_werbung\(string \$marker\): string \{(.*?)\n\}/s', $ma21, $f);
pruefe('ma21_werbung liest keine Vorlage werbung-*.html mehr', isset($f[1]) && !str_contains($f[1], 'ma21_vorlage'), true);
pruefe('ma21_werbung: ohne Plugin oder ohne Anzeige leer', isset($f[1]) && substr_count($f[1], "''") >= 2, true);
$js = (string) file_get_contents(dirname(__DIR__, 2) . '/chatgpt-site/assets/ressort-dropdowns.js');
pruefe('Menü (WordPress): ohne laufende Anzeige keine Musteranzeige', str_contains($js, "if (wp && typeof wp === 'object' && !liste.length) return '';"), true);
$anz = json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/deploy/anzeigen.json'), true);
pruefe('statische Kopie: zehn echte Motive, Label „Anzeige“, keine Demo-Motive', [count($anz['motive']), $anz['label'], count(array_filter($anz['motive'], fn($m) => str_starts_with($m['id'], 'demo-')))], [10, 'Anzeige', 0]);
pruefe('statische Kopie: kein „Musteranzeige“ auf der Startseite', str_contains((string) file_get_contents(dirname(__DIR__, 2) . '/chatgpt-site/index.html'), 'Musteranzeige'), false);

echo "\nRotation und Video\n";
$rot = (string) file_get_contents($W . '/plugin/merzenich-aktuell-core/assets/ad-rotation.js');
pruefe('Video nur bei Bewegung, ohne Datensparmodus, breiter als 560 px', str_contains($rot, "prefers-reduced-motion: reduce") && str_contains($rot, 'saveData') && str_contains($rot, 'window.innerWidth > 560'), true);
pruefe('Video lädt erst beim Abspielen (data-src, WebM wo möglich) und pausiert außerhalb', str_contains($rot, "v.setAttribute('src', quelle)") && str_contains($rot, 'data-src-webm') && str_contains($rot, 'v.pause()') && str_contains($rot, 'IntersectionObserver'), true);
$css = (string) file_get_contents(dirname(__DIR__, 2) . '/chatgpt-site/assets/werbung.css');
pruefe('CSS: ohne Bewegung kein Zoom und kein Video', (bool) preg_match('/@media \(prefers-reduced-motion:reduce\)\{\s*\.ma-ad-art--bewegt img\{animation:none\}\s*\.ma-ad-video\{display:none\}/', $css), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
