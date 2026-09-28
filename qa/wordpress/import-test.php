<?php
/**
 * WordPress-Paritaet ohne WordPress (Audit 28.09.2026):
 *  1. WXR-Import: jede Meldung und jeder Termin des statischen Stands ist drin,
 *     mit alter Adresse, Quelle und aufloesbarem Beitragsbild.
 *  2. Weiterleitungen alter Adressen (includes/permalinks.php).
 *  3. Rathauszeiten aus data/gemeinde.json wie im statischen Stand.
 *  4. Symbolbild und Ortsansicht bleiben gekennzeichnet (includes/images.php).
 * Aufruf: php qa/wordpress/import-test.php
 */
define('ABSPATH', __DIR__);
define('OBJECT', 'OBJECT');
$wurzel = dirname(__DIR__, 2);

$fehler = 0;
function pruefe(string $name, $ist, $soll) {
    global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++;
    printf("  %-66s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ', soll: ' . var_export($soll, true) . ')');
}

// ------------------------------------------------------------------ 1. WXR
echo "WXR-Import gegen den statischen Stand\n";
$idx = json_decode(file_get_contents("$wurzel/chatgpt-site/api/inhalte.json"), true);
$termine = array_values(array_filter(glob("$wurzel/chatgpt-site/termine/*/index.html"), fn($p) => basename(dirname($p)) !== 'melden'));
$xml = simplexml_load_file("$wurzel/wordpress-delivery/merzenich-aktuell-import.xml");
pruefe('XML laesst sich lesen', $xml !== false, true);
$wp = 'http://wordpress.org/export/1.2/';
$items = [];
foreach ($xml->channel->item as $item) {
    $w = $item->children($wp);
    $meta = [];
    foreach ($w->postmeta as $m) $meta[(string)$m->meta_key] = (string)$m->meta_value;
    $items[] = ['typ' => (string)$w->post_type, 'id' => (int)$w->post_id, 'status' => (string)$w->status, 'meta' => $meta, 'name' => (string)$w->post_name];
}
$nach = fn(string $typ) => array_values(array_filter($items, fn($i) => $i['typ'] === $typ));
$posts = $nach('post'); $events = $nach('ma_event'); $bilder = $nach('attachment');
pruefe('Meldungen = api/inhalte.json', count($posts), count($idx['artikel']));
pruefe('Termine = Terminseiten', count($events), count($termine));
pruefe('mindestens 109 Meldungen (Master-Prompt)', count($posts) >= 109, true);
$ids = array_column($items, 'id');
pruefe('IDs eindeutig', count($ids), count(array_unique($ids)));
$urls = array_column($idx['artikel'], 'url');
$legacy = array_map(fn($p) => $p['meta']['ma_legacy_url'] ?? '', $posts);
sort($urls); sort($legacy);
pruefe('jede statische Adresse genau einmal als ma_legacy_url', $legacy, $urls);
pruefe('jede Meldung mit https-Quelle', count(array_filter($posts, fn($p) => str_starts_with($p['meta']['ma_source_url'] ?? '', 'https://'))), count($posts));
pruefe('jede Meldung mit Pruefdatum der Quelle', count(array_filter($posts, fn($p) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $p['meta']['ma_source_checked_at'] ?? ''))), count($posts));
pruefe('Meldungen als Entwurf (Freigabe in WordPress)', array_unique(array_column($posts, 'status')), ['draft']);
pruefe('Human Review wird nicht behauptet', count(array_filter($posts, fn($p) => ($p['meta']['ma_human_reviewed'] ?? '') === '1')), 0);
$bildIds = array_column($bilder, 'id');
pruefe('jedes Beitragsbild ist ein Anhang im Import', count(array_filter($posts, fn($p) => in_array((int)($p['meta']['_thumbnail_id'] ?? 0), $bildIds, true))), count($posts));
pruefe('jeder Anhang mit Credit und Bildtyp', count(array_filter($bilder, fn($b) => ($b['meta']['ma_image_credit'] ?? '') !== '' && ($b['meta']['ma_image_type'] ?? '') !== '')), count($bilder));
pruefe('Rechte geprueft nur mit Lizenz oder Sichtung', count(array_filter($bilder, fn($b) => $b['meta']['ma_image_rights_verified'] === '1' && $b['meta']['ma_image_license'] === '')), 0);
pruefe('jeder Termin mit Beginn im Format datetime-local', count(array_filter($events, fn($e) => preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $e['meta']['ma_event_start'] ?? ''))), count($events));
pruefe('jeder Termin mit alter Adresse /termine/<slug>/', count(array_filter($events, fn($e) => ($e['meta']['ma_legacy_url'] ?? '') === '/termine/' . $e['name'] . '/')), count($events));
$roh = file_get_contents("$wurzel/wordpress-delivery/merzenich-aktuell-import.xml");
pruefe('keine Werbeflaechen im Import', str_contains($roh, 'werbung') || str_contains($roh, 'ma-ad-'), false);
pruefe('Gemeindedaten im Plugin = deploy/gemeinde.json', file_get_contents("$wurzel/wordpress/plugin/merzenich-aktuell-core/data/gemeinde.json"), file_get_contents("$wurzel/deploy/gemeinde.json"));

// ------------------------------------------------------------------ Stubs
$GLOBALS['posts_meta'] = ['/blaulicht/einsatz-131-unfall-b264-l264/' => 11, '/vereine/meldung-x/' => 12];
$GLOBALS['permalinks'] = [11 => 'https://merzenich-aktuell.de/blaulicht/einsatz-131-unfall-b264-l264/', 12 => 'https://merzenich-aktuell.de/vereine/meldung-x/'];
$GLOBALS['terme'] = ['category:blaulicht' => 'https://merzenich-aktuell.de/category/blaulicht/', 'ma_location:golzheim' => 'https://merzenich-aktuell.de/ort/golzheim/', 'post_tag:feuerwehr' => 'https://merzenich-aktuell.de/thema/feuerwehr/'];
$GLOBALS['clubs'] = ['sc-1919'];
$GLOBALS['post_names'] = ['meldung-x'];
function get_posts($a) {
    if (isset($a['meta_key'])) return isset($GLOBALS['posts_meta'][$a['meta_value']]) ? [$GLOBALS['posts_meta'][$a['meta_value']]] : [];
    return in_array($a['name'] ?? '', $GLOBALS['post_names'], true) ? [99] : [];
}
function get_permalink($id) { return $GLOBALS['permalinks'][$id] ?? ''; }
function get_term_by($f, $slug, $tax) { return isset($GLOBALS['terme']["$tax:$slug"]) ? (object)['k' => "$tax:$slug"] : false; }
function get_term_link($t) { return $GLOBALS['terme'][$t->k]; }
function is_wp_error($x) { return false; }
function get_page_by_path($slug, $o, $typ) { return in_array($slug, $GLOBALS['clubs'], true) ? (object)[] : null; }
function add_action(...$a) {} function add_filter(...$a) {} function add_shortcode(...$a) {}
$GLOBALS['opt'] = [];
function get_option($k, $d = '') { return $GLOBALS['opt'][$k] ?? $d; }
function update_option($k, $v) { $GLOBALS['opt'][$k] = $v; }
function esc_html($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
function esc_attr($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
function esc_url($s) { return (string)$s; }

require "$wurzel/wordpress/plugin/merzenich-aktuell-core/includes/permalinks.php";
require "$wurzel/wordpress/plugin/merzenich-aktuell-core/includes/gemeinde.php";

// ------------------------------------------------------------------ 2. Adressen
echo "\nAlte Adressen\n";
pruefe('Meldung: gleiche Adresse, keine Schleife', ma_legacy_weiterleitung_fuer('/blaulicht/einsatz-131-unfall-b264-l264/'), '');
$GLOBALS['permalinks'][11] = 'https://merzenich-aktuell.de/blaulicht/einsatz-131/';
pruefe('Meldung mit neuer Adresse: 301 dorthin', ma_legacy_weiterleitung_fuer('/blaulicht/einsatz-131-unfall-b264-l264'), 'https://merzenich-aktuell.de/blaulicht/einsatz-131/');
pruefe('Ressort /blaulicht/ -> Kategorie', ma_legacy_weiterleitung_fuer('/blaulicht/'), 'https://merzenich-aktuell.de/category/blaulicht/');
pruefe('Ortsteil /golzheim/ -> Ort', ma_legacy_weiterleitung_fuer('/golzheim/'), 'https://merzenich-aktuell.de/ort/golzheim/');
pruefe('Thema bereits unter /thema/: keine Weiterleitung', ma_legacy_weiterleitung_fuer('/thema/feuerwehr/'), '');
pruefe('Unbekanntes: nichts', ma_legacy_weiterleitung_fuer('/gibt-es-nicht/'), '');
pruefe('Fremde Zeichen: nichts', ma_legacy_weiterleitung_fuer('/<script>/'), '');
pruefe('/vereine/<profil>/ bleibt Vereinsprofil', ma_vereine_anfrage(['ma_club' => 'sc-1919', 'post_type' => 'ma_club']), ['ma_club' => 'sc-1919', 'post_type' => 'ma_club']);
pruefe('/vereine/<meldung>/ zeigt die Meldung', ma_vereine_anfrage(['ma_club' => 'meldung-x', 'post_type' => 'ma_club']), ['name' => 'meldung-x']);
ma_permalinks_standard();
pruefe('Aktivierung: Permalinks /%category%/%postname%/', get_option('permalink_structure'), '/%category%/%postname%/');
pruefe('Aktivierung: Schlagwort-Basis thema', get_option('tag_base'), 'thema');
$GLOBALS['opt'] = ['permalink_structure' => '/%postname%/'];
ma_permalinks_standard();
pruefe('bewusst gesetzte Struktur bleibt', get_option('permalink_structure'), '/%postname%/');

// ------------------------------------------------------------------ 3. Gemeinde
echo "\nRathaus und Abfall\n";
$g = json_decode(file_get_contents("$wurzel/deploy/gemeinde.json"), true);
$soll = trim((string)shell_exec('node --input-type=module -e ' . escapeshellarg("import { zeitenText } from '$wurzel/deploy/lib-gemeinde.mjs'; console.log(zeitenText());")));
pruefe('Zeiten wie deploy/lib-gemeinde.mjs', ma_gemeinde_zeiten_text($g['rathaus']['zeiten']), $soll);
pruefe('Dienstag geschlossen', str_contains($soll, 'Dienstag geschlossen'), true);
$rathaus = ma_gemeinde_shortcode(['teil' => 'rathaus']);
pruefe('Shortcode Rathaus nennt Donnerstag bis 18 Uhr', str_contains($rathaus, 'Donnerstag 8 bis 12:30 und 14 bis 18 Uhr'), true);
pruefe('Shortcode Rathaus nennt Quelle und Stand', str_contains($rathaus, 'Stand 28.09.2026'), true);
pruefe('Shortcode Abfall nennt Schoenmackers-Telefon', str_contains(ma_gemeinde_shortcode(['teil' => 'abfall']), '02237 9742-4502'), true);
pruefe('unbekannter Teil: leer', ma_gemeinde_shortcode(['teil' => 'x']), '');

// ------------------------------------------------------------------ 4. Bilder
echo "\nBildkennzeichnung\n";
$GLOBALS['m'] = [];
function get_post($p = null) { return (object)['ID' => 5, 'post_type' => 'post']; }
function get_post_meta($id, $k, $s = false) { return $GLOBALS['m'][$k] ?? ''; }
function get_post_thumbnail_id($id) { return 7; }
function get_the_post_thumbnail_url($id, $s) { return 'https://merzenich-aktuell.de/wp-content/uploads/x.jpg'; }
function get_the_title($id) { return 'Titel'; }
function get_the_category($id) { return [(object)['slug' => 'blaulicht']]; }
function get_the_terms($p, $t) { return []; }
function trailingslashit($s) { return rtrim($s, '/') . '/'; }
function get_template_directory_uri() { return ''; } function get_template_directory() { return ''; }
require "$wurzel/wordpress/plugin/merzenich-aktuell-core/includes/images.php";
$GLOBALS['m'] = ['ma_image_type' => 'symbol', 'ma_image_license' => 'CC BY-SA 3.0', 'ma_image_credit' => 'A / Wikimedia Commons', 'ma_image_rights_verified' => '1'];
$b = ma_content_image();
pruefe('lizenziertes Symbolbild bleibt Symbolbild', $b['type'], 'symbol');
pruefe('Bildzeile sagt: nicht am Einsatzort', str_starts_with(ma_image_caption($b), 'Symbolbild. Nicht am Einsatzort aufgenommen.'), true);
$GLOBALS['m']['ma_image_type'] = 'place';
pruefe('Ortsansicht bleibt gekennzeichnet', str_starts_with(ma_image_caption(ma_content_image()), 'Ortsansicht. Kein Foto vom Ereignis.'), true);
$GLOBALS['m']['ma_image_type'] = 'original';
pruefe('Originalbild ohne Hinweis', str_starts_with(ma_image_caption(ma_content_image()), 'Originalbild.'), true);
$GLOBALS['m'] = ['ma_image_type' => 'original'];
pruefe('ohne Lizenz und Pruefung: Ersatzgrafik', ma_content_image()['is_fallback'], true);

echo "\n" . ($fehler ? "$fehler Fehler\n" : "alles ok\n");
exit($fehler ? 1 : 0);
