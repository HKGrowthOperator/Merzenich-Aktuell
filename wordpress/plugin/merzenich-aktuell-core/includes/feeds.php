<?php
/**
 * Nachrichtendienste und Browser-Hinweise (02.10.2026): alles, was eine neue
 * Meldung automatisch weiterträgt, sobald sie veröffentlicht ist.
 *
 * - RSS-Feeds (/feed/, Ressort-Feeds): Beitragsbild als <enclosure> und
 *   <media:content> mit Bildnachweis, Bild oben im Volltext, Autor
 *   „Redaktion Merzenich Aktuell“ statt des Anmeldenamens, Feedtitel ohne
 *   Seitentitel-Zusatz. Google News (Publisher Center), Microsoft Start,
 *   Feedly und Co. lesen genau diesen Feed.
 * - WebSub (PubSubHubbub): der Feed nennt einen Hub, und bei jeder
 *   Veröffentlichung oder Änderung meldet das Plugin den Feed dem Hub.
 *   Abonnenten (Feedly, Inoreader, NewsBlur und andere Dienste) bekommen die
 *   Meldung in Sekunden statt beim nächsten Abruf. Google News selbst zieht
 *   den Feed und die News-Sitemap (includes/seo.php) von sich aus.
 * - /api/latest.json: dieselbe Datei wie auf der statischen Seite. Das
 *   Skript einwilligung.js (Theme, Fuß) fragt sie alle fünf Minuten ab und
 *   zeigt mit Einwilligung eine Browser-Benachrichtigung zur neuesten
 *   Meldung. Auf WordPress lief die Adresse bisher auf 404.
 *
 * Nicht automatisierbar: die Aufnahme bei Google News (Publisher Center),
 * Microsoft Start (Partner Hub) und Apple News (News Publisher) beantragt der
 * Betreiber; danach greift alles hier von selbst.
 */
if (!defined('ABSPATH')) { exit; }

const MA_WEBSUB_HUB = 'https://pubsubhubbub.appspot.com/';
const MA_FEED_AUTOR = 'Redaktion Merzenich Aktuell';

/* ------------------------------------------------------------ Reine Funktionen (ohne WordPress testbar) */

/** Ein Eintrag für /api/latest.json (Form wie deploy/inhaltsindex.mjs). */
function ma_latest_eintrag(string $titel, string $url, string $datum, string $ressort, string $ort, string $teaser, string $bild): array {
    return ['title' => $titel, 'url' => $url, 'date' => $datum, 'ressort' => $ressort, 'ort' => $ort, 'teaser' => $teaser, 'image' => $bild];
}

/** Nutzlast für den WebSub-Hub (hub.mode=publish, eine oder mehrere Feed-Adressen). */
function ma_websub_nutzlast(array $feeds): string {
    $teile = ['hub.mode=publish'];
    foreach (array_values(array_unique(array_filter($feeds))) as $f) $teile[] = 'hub.url=' . rawurlencode($f);
    return implode('&', $teile);
}

/** Zusatz-XML für ein Feed-Item: enclosure und media:content mit Nachweis. */
function ma_feed_item_xml(string $bildUrl, int $bytes, string $mime, string $alt, string $credit, int $w = 0, int $h = 0): string {
    if ($bildUrl === '') return '';
    $e = fn($s) => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    $x = '<enclosure url="' . $e($bildUrl) . '" length="' . max(0, $bytes) . '" type="' . $e($mime) . '" />' . "\n";
    $x .= '<media:content url="' . $e($bildUrl) . '" medium="image" type="' . $e($mime) . '"' . ($w ? ' width="' . $w . '" height="' . $h . '"' : '') . '>';
    if ($alt !== '') $x .= '<media:title type="plain">' . $e($alt) . '</media:title>';
    if ($credit !== '') $x .= '<media:credit role="photographer" scheme="urn:ebu">' . $e($credit) . '</media:credit>';
    return $x . '</media:content>' . "\n";
}

/* ------------------------------------------------------------ Feed-Anreicherung */

/** Bild eines Beitrags für Feeds und latest.json: nur mit geklärten Rechten (ma_content_image), möglichst ≥ 1200 px. */
function ma_feed_bild(WP_Post $p): array {
    if (!has_post_thumbnail($p)) return [];
    if (function_exists('ma_content_image') && !empty(ma_content_image($p, 'large')['is_fallback'])) return [];
    $id = (int) get_post_thumbnail_id($p);
    $src = null;
    foreach (['1536x1536', 'full', 'large'] as $g) { $s = wp_get_attachment_image_src($id, $g); if ($s && (int) $s[1] >= 1200) { $src = $s; break; } if (!$src && $s) $src = $s; }
    if (!$src) return [];
    $datei = get_attached_file($id);
    $meta = wp_get_attachment_metadata($id);
    $bytes = 0; $mime = (string) get_post_mime_type($id);
    foreach ((array) ($meta['sizes'] ?? []) as $sz) if (!empty($sz['file']) && str_ends_with($src[0], $sz['file'])) { $bytes = (int) ($sz['filesize'] ?? 0); $mime = (string) ($sz['mime-type'] ?? $mime); }
    if (!$bytes && $datei && file_exists($datei) && str_ends_with($src[0], basename($datei))) $bytes = (int) filesize($datei);
    if (preg_match('/\.webp$/i', $src[0])) $mime = 'image/webp';
    return ['url' => $src[0], 'w' => (int) $src[1], 'h' => (int) $src[2], 'bytes' => $bytes, 'mime' => $mime ?: 'image/jpeg',
        'alt' => (string) get_post_meta($id, '_wp_attachment_image_alt', true) ?: get_the_title($p), 'credit' => ma_credit_kurz((string) get_post_meta($p->ID, 'ma_image_credit', true), (string) get_post_meta($p->ID, 'ma_image_license', true))];
}

add_action('rss2_ns', function (): void { echo 'xmlns:media="http://search.yahoo.com/mrss/"' . "\n"; });
add_action('atom_ns', function (): void { echo 'xmlns:media="http://search.yahoo.com/mrss/"' . "\n"; });
add_action('rss2_head', function (): void {
    echo '<atom:link rel="hub" href="' . esc_url(MA_WEBSUB_HUB) . '" />' . "\n";
});
add_action('atom_head', function (): void { echo '<link rel="hub" href="' . esc_url(MA_WEBSUB_HUB) . '" />' . "\n"; });
add_action('rss2_item', function (): void {
    $p = get_post();
    if (!$p instanceof WP_Post || $p->post_type !== 'post') return;
    $b = ma_feed_bild($p);
    if ($b) echo ma_feed_item_xml($b['url'], $b['bytes'], $b['mime'], $b['alt'], $b['credit'], $b['w'], $b['h']);
});
/* Bild oben im Volltext: Dienste ohne Media-RSS (und Mail-Leser) sehen es trotzdem. */
add_filter('the_content_feed', function (string $inhalt): string {
    $p = get_post();
    if (!$p instanceof WP_Post || $p->post_type !== 'post') return $inhalt;
    $b = ma_feed_bild($p);
    if (!$b) return $inhalt;
    return '<p><img src="' . esc_url($b['url']) . '" alt="' . esc_attr($b['alt']) . '"' . ($b['w'] ? ' width="' . $b['w'] . '" height="' . $b['h'] . '"' : '') . '></p>' . ($b['credit'] !== '' ? '<p><small>Bild: ' . esc_html($b['credit']) . '</small></p>' : '') . $inhalt;
});
/* Autor in Feeds: die Redaktion, nicht der Anmeldename. */
add_filter('the_author', fn($name) => is_feed() ? MA_FEED_AUTOR : $name);
add_filter('get_the_author_display_name', fn($name) => is_feed() ? MA_FEED_AUTOR : $name);
/* Feedtitel: „Merzenich Aktuell“ bzw. „Blaulicht | Merzenich Aktuell“, nicht der Dokumenttitel der Liste. */
add_filter('wp_title_rss', function ($t): string {
    if (is_category() || is_tag() || is_tax()) { $o = get_queried_object(); if ($o instanceof WP_Term) return $o->name . ' | Merzenich Aktuell'; }
    return 'Merzenich Aktuell';
});
add_filter('bloginfo_rss', fn($wert, $zeig) => $zeig === 'name' ? 'Merzenich Aktuell' : $wert, 10, 2);

/* ------------------------------------------------------------ WebSub: Hub bei jeder Veröffentlichung anstoßen */

function ma_websub_feeds(?WP_Post $p = null): array {
    $feeds = [get_feed_link('rss2'), get_feed_link('atom')];
    if ($p instanceof WP_Post && $p->post_type === 'post') foreach (get_the_category($p->ID) as $c) $feeds[] = get_category_feed_link($c->term_id);
    return array_values(array_unique($feeds));
}

function ma_websub_merken(?WP_Post $p): void {
    $GLOBALS['ma_websub_feeds'] = array_values(array_unique(array_merge((array) ($GLOBALS['ma_websub_feeds'] ?? []), ma_websub_feeds($p))));
    if (!has_action('shutdown', 'ma_websub_senden')) add_action('shutdown', 'ma_websub_senden');
}

function ma_websub_senden(): void {
    $feeds = (array) ($GLOBALS['ma_websub_feeds'] ?? []);
    if (!$feeds) return;
    $host = (string) parse_url(home_url('/'), PHP_URL_HOST);
    if (in_array($host, ['localhost', '127.0.0.1'], true) || str_ends_with($host, '.local')) return;
    $antwort = wp_remote_post(MA_WEBSUB_HUB, ['timeout' => 5, 'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'], 'body' => ma_websub_nutzlast($feeds)]);
    $status = is_wp_error($antwort) ? $antwort->get_error_message() : (string) wp_remote_retrieve_response_code($antwort);
    update_option('ma_websub_letzter', ['zeit' => time(), 'anzahl' => count($feeds), 'status' => $status], false);
}

add_action('transition_post_status', function (string $neu, string $alt, WP_Post $p): void {
    if ($p->post_type !== 'post') return;
    if ($neu === 'publish' || $alt === 'publish') ma_websub_merken($p);
}, 10, 3);
add_action('post_updated', function (int $id, WP_Post $nach): void {
    if ($nach->post_status === 'publish' && $nach->post_type === 'post') ma_websub_merken($nach);
}, 10, 2);

/* ------------------------------------------------------------ /api/latest.json */

add_action('init', function (): void {
    add_rewrite_rule('^api/latest\.json/?$', 'index.php?ma_api=latest', 'top');
}, 6);

function ma_latest_daten(): array {
    $posts = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 20]);
    $items = [];
    foreach ($posts as $p) {
        $b = ma_feed_bild($p);
        $items[] = ma_latest_eintrag(html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8'), (string) get_permalink($p), (string) get_post_time('c', false, $p),
            function_exists('ma_seo_ressort_von') ? ma_seo_ressort_von($p)[0] : '', function_exists('ma_seo_ort_von') ? ma_seo_ort_von($p) : 'merzenich',
            function_exists('ma_seo_kuerzen') ? ma_seo_kuerzen(trim($p->post_excerpt) !== '' ? $p->post_excerpt : $p->post_content, 220) : '', $b['url'] ?? '');
    }
    $stand = $items ? $items[0]['date'] : current_time('c');
    return ['generated' => current_time('c'), 'stand' => $stand, 'items' => $items];
}

/* ---------------------------------------------------------- Kalender /termine/kalender.ics (04.10.2026) */

/**
 * iCalendar-Text (RFC 5545) für Termine. Je Eintrag: start, ende (Unix, 0 = offen),
 * titel, ort, url, beschreibung, stand. Zeiten in UTC, Zeilen auf 75 Oktette gefaltet.
 * Ohne WordPress prüfbar (qa/wordpress/feeds-test.php).
 */
function ma_ics_text(array $termine): string {
    $e = fn(string $x): string => str_replace(["\\", ';', ',', "\n"], ["\\\\", '\\;', '\\,', '\\n'], trim($x));
    $z = fn(int $ts): string => gmdate('Ymd\THis\Z', $ts);
    $zeilen = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Merzenich Aktuell//Termine//DE', 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH', 'X-WR-CALNAME:Termine Merzenich Aktuell', 'X-WR-TIMEZONE:Europe/Berlin'];
    foreach ($termine as $t) {
        $start = (int) ($t['start'] ?? 0);
        if ($start <= 0 || empty($t['titel'])) continue;
        $ende = (int) ($t['ende'] ?? 0);
        $zeilen[] = 'BEGIN:VEVENT';
        $zeilen[] = 'UID:' . md5((string) ($t['url'] ?? $t['titel'])) . '@merzenich-aktuell.de';
        $zeilen[] = 'DTSTAMP:' . $z((int) ($t['stand'] ?? $start));
        $zeilen[] = 'DTSTART:' . $z($start);
        $zeilen[] = 'DTEND:' . $z($ende > $start ? $ende : $start + 7200);
        $zeilen[] = 'SUMMARY:' . $e((string) $t['titel']);
        if (!empty($t['ort'])) $zeilen[] = 'LOCATION:' . $e((string) $t['ort']);
        if (!empty($t['beschreibung'])) $zeilen[] = 'DESCRIPTION:' . $e((string) $t['beschreibung']);
        if (!empty($t['url'])) $zeilen[] = 'URL:' . (string) $t['url'];
        $zeilen[] = 'END:VEVENT';
    }
    $zeilen[] = 'END:VCALENDAR';
    $aus = [];
    foreach ($zeilen as $l) {
        if (strlen($l) <= 75) { $aus[] = $l; continue; }
        $teil = ''; $teile = [];
        foreach (mb_str_split($l) as $c) { if (strlen($teil . $c) > ($teile ? 74 : 75)) { $teile[] = $teil; $teil = ''; } $teil .= $c; }
        if ($teil !== '') $teile[] = $teil;
        $aus[] = implode("\r\n ", $teile);
    }
    return implode("\r\n", $aus) . "\r\n";
}

/** Kommende und laufende Termine (ab gestern) als Einträge für ma_ics_text(). */
function ma_ics_termine(): array {
    $von = (string) wp_date('Y-m-d\TH:i', time() - DAY_IN_SECONDS);
    $l = get_posts(['post_type' => 'ma_event', 'post_status' => 'publish', 'posts_per_page' => 200, 'meta_key' => 'ma_event_start', 'orderby' => 'meta_value', 'order' => 'ASC', 'meta_query' => [['key' => 'ma_event_start', 'value' => $von, 'compare' => '>=']]]);
    $aus = [];
    foreach ($l as $p) {
        $start = function_exists('ma_event_timestamp') ? (int) ma_event_timestamp($p->ID, 'start') : 0;
        $ende = function_exists('ma_event_timestamp') ? (int) ma_event_timestamp($p->ID, 'end') : 0;
        $aus[] = ['start' => $start, 'ende' => $ende, 'titel' => html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8'), 'ort' => (string) get_post_meta($p->ID, 'ma_event_place', true), 'url' => get_permalink($p), 'beschreibung' => wp_strip_all_tags((string) ($p->post_excerpt ?: '')), 'stand' => (int) get_post_modified_time('U', true, $p)];
    }
    return $aus;
}

add_action('init', function (): void {
    add_rewrite_rule('^termine/kalender\.ics$', 'index.php?ma_api=kalender', 'top');
});
add_action('template_redirect', function (): void {
    if (get_query_var('ma_api') !== 'kalender') return;
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename="merzenich-aktuell-termine.ics"');
    header('Cache-Control: public, max-age=600');
    echo ma_ics_text(ma_ics_termine()); exit;
}, 0);

add_action('template_redirect', function (): void {
    if (get_query_var('ma_api') !== 'latest') return;
    nocache_headers();
    header('Content-Type: application/json; charset=utf-8');
    echo wp_json_encode(ma_latest_daten(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); exit;
}, 0);
