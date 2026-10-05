<?php
/**
 * Theme 21: WordPress sieht aus wie die statische Seite (30.09.2026).
 *
 * Kopf, Fuß und die Service-Blöcke der Startseite kommen als Vorlagen aus der
 * gebauten Seite (deploy/wp-theme.mjs -> vorlagen/*.html). Die Nachrichten
 * (Bühne, Rubrikflächen, Listen, Artikel) setzt WordPress aus seinen Beiträgen
 * in genau dem Markup, das deploy/inhaltsindex.mjs und deploy/meldungen.mjs
 * erzeugen, damit dieselben Stylesheets greifen.
 *
 * Stylesheets, Skripte, Schriften und Bilder der statischen Seite liegen im
 * Theme unter static/ (beim Paketbau aus chatgpt-site/assets kopiert) und
 * werden unter /assets/ ausgeliefert (Rewrite in .htaccess). Fehlt eine Datei,
 * antwortet PHP aus der Mediathek oder holt sie einmal aus dem Repository und legt
 * sie hier ab (ma21_asset); das Ressort-Menü wird aus den eigenen Beiträgen gefüllt.
 */
if (!defined('ABSPATH')) { exit; }

const MA21_ORTE = ['merzenich' => 'Merzenich', 'golzheim' => 'Golzheim', 'girbelsrath' => 'Girbelsrath', 'morschenich' => 'Morschenich', 'buergewald' => 'Bürgewald'];
const MA21_RESSORT = ['blaulicht' => 'Blaulicht', 'sport' => 'Sport', 'rathaus' => 'Rathaus & Politik', 'leben' => 'Leben', 'wirtschaft' => 'Wirtschaft', 'menschen' => 'Menschen', 'vereine' => 'Vereine', 'tipp' => 'Tipp'];
// Bildhinweis je Bildtyp: auf der Startseite (knapp) und in Listen/Artikeln.
const MA21_HINWEIS_START = ['symbol' => 'Symbolbild', 'place' => 'Ortsansicht', 'original' => '', 'official' => 'Bild: Quelle', 'licensed' => 'Archivbild'];
const MA21_HINWEIS = ['symbol' => 'Symbolbild', 'place' => 'Ortsansicht', 'original' => 'Originalbild', 'official' => 'Quellenmotiv', 'licensed' => 'Archivbild'];

/**
 * sizes-Angaben der Bildkarten (02.10.2026), gemessen an den gerenderten
 * Breiten bei 390/560/760/1100/1280/1440 px. Dieselbe Tabelle steht in
 * deploy/lib-artikel.mjs (SIZES) fuer die statische Seite. Vorher stammten die
 * Werte aus Schaetzungen; das Handy lud etwa fuer ein 120-px-Vorschaubild die
 * Breite 100vw (Lighthouse „uses-responsive-images“, rund 2 MB je Seite).
 */
const MA21_SIZES = [
    'xl' => '(max-width: 1099px) 100vw, (max-width: 1439px) 62vw, 845px',
    'r' => '(max-width: 759px) 132px, (max-width: 1099px) 46vw, (max-width: 1439px) 31vw, 411px',
    'u' => '(max-width: 759px) 132px, (max-width: 1439px) 31vw, 411px',
    'l' => '(max-width: 759px) 100vw, (max-width: 1099px) 46vw, (max-width: 1439px) 34vw, 468px',
    'l-vereine' => '(max-width: 759px) 100vw, (max-width: 1439px) 46vw, 624px',
    'l-blaulicht' => '(max-width: 759px) 100vw, (max-width: 1099px) 46vw, (max-width: 1439px) 55vw, 749px',
    'l-blaulicht-klein' => '(max-width: 759px) 100vw, (max-width: 1099px) 46vw, 132px',
    'l-rathaus' => '(max-width: 759px) 100vw, (max-width: 1099px) 46vw, (max-width: 1439px) 18vw, 241px',
    'l-wirtschaft' => '(max-width: 759px) 100vw, (max-width: 1099px) 46vw, (max-width: 1439px) 18vw, 240px',
    'm' => '(max-width: 559px) 100vw, (max-width: 759px) 45vw, (max-width: 1099px) 30vw, (max-width: 1439px) 22.5vw, 307px',
    'm-blaulicht' => '(max-width: 559px) 100vw, (max-width: 759px) 45vw, (max-width: 1099px) 30vw, 132px',
    'm-rathaus' => '(max-width: 559px) 100vw, (max-width: 759px) 45vw, (max-width: 1099px) 30vw, (max-width: 1439px) 31vw, 411px',
    'm-vereine' => '(max-width: 559px) 100vw, (max-width: 759px) 45vw, (max-width: 1099px) 30vw, (max-width: 1439px) 31vw, 411px',
    'm-wirtschaft' => '(max-width: 559px) 100vw, (max-width: 759px) 45vw, (max-width: 1099px) 30vw, (max-width: 1439px) 18vw, 240px',
    's' => '(max-width: 759px) 112px, 220px',
    's-vereine' => '(max-width: 759px) 112px, (max-width: 1099px) 220px, 200px',
    'feed-lead' => '(max-width: 1099px) 120px, (max-width: 1439px) 30.5vw, 419px',
    'feed-row' => '(max-width: 1099px) 120px, (max-width: 1439px) 210px, 240px',
    'news-card' => '(max-width: 1099px) 120px, (max-width: 1439px) 30vw, 405px',
    'raster' => '(max-width: 559px) 132px, (max-width: 1099px) 46vw, (max-width: 1279px) 31vw, 300px',
    'unternehmen' => '(max-width: 1099px) 100vw, (max-width: 1439px) 32vw, 450px',
    'figur' => '(max-width: 1099px) 100vw, 720px',
];
/** sizes einer Sektionskarte: Blaulicht, Rathaus und Wirtschaft ordnen ab 1100 px anders an (startseite.css). */
function ma21_sizes(string $g, string $sektion = '', int $i = 0): string {
    if ($g === 'l' && $sektion === 'blaulicht') return $i === 0 ? MA21_SIZES['l-blaulicht'] : MA21_SIZES['l-blaulicht-klein'];
    return MA21_SIZES["{$g}-{$sektion}"] ?? MA21_SIZES[$g] ?? '';
}

/* ------------------------------------------------------------ Vorlagen */

/** Vorlage aus vorlagen/ lesen (von deploy/wp-theme.mjs erzeugt). */
function ma21_vorlage(string $name): string {
    static $cache = [];
    if (!isset($cache[$name])) {
        $pfad = get_template_directory() . '/vorlagen/' . $name;
        $cache[$name] = is_readable($pfad) ? (string) file_get_contents($pfad) : '';
    }
    return $cache[$name];
}

/** Kopf-Assets: Icons, Schrift-Preloads und das CSS-Buendel (deploy/css-bundle.mjs). bundle-start.css nur auf der Startseite, bundle.css sonst; die Vorlage traegt beide (data-ma-css, deploy/wp-theme.mjs). */
function ma21_kopf_assets(): string {
    $weg = is_front_page() ? 'seite' : 'start';
    return (string) preg_replace('/<link rel="stylesheet"[^>]*data-ma-css="' . $weg . '"[^>]*>\n?/', '', ma21_vorlage('kopf-assets.html'));
}

/* LCP-Bild vorladen (wp_head, frueh): Aufmacher der Startseite, Bild einer Meldung oder eines Vereinsprofils, mit denselben srcset/sizes wie das <img>. */
add_action('wp_head', function (): void {
    $b = null; $sizes = '';
    if (is_front_page()) { $a = ma21_startseite_belegung()['aufmacher'] ?? null; if ($a instanceof WP_Post) { $b = ma21_bild($a); $sizes = MA21_SIZES['xl']; } }
    elseif (is_singular(['post', 'ma_club'])) { $p = get_queried_object(); if ($p instanceof WP_Post) { $b = ma21_bild($p); $sizes = MA21_SIZES['figur']; } }
    if (!$b || !ma21_echtes_bild($b)) return;
    echo '<link rel="preload" as="image" href="' . esc_url($b['src']) . '"' . ($b['srcset'] ? ' imagesrcset="' . esc_attr($b['srcset']) . '" imagesizes="' . esc_attr($sizes) . '"' : '') . ' fetchpriority="high">' . "\n";
}, 2);

/**
 * Kopf (Masthead). Der Platzhalter {{ma:datum}} (deploy/wp-theme.mjs) wird in
 * Ortszeit gefüllt. Seit 21.9.9 kommt auch der Rest aus WordPress statt aus dem
 * Stand der Vorlage: die Ausgabe-Wahl (Adressen /ort/…/, keine Umleitung mehr,
 * seit 21.10.2 ohne Zahlen), die Zeile „Merzenich · Jetzt“ mit der jüngsten
 * veröffentlichten Meldung und dem nächsten Termin, und die Markierung der gerade besuchten Seite im Menü.
 * (Vorher zeigte die Zeile einen Entwurf, der für Leser ein 404 war.)
 */
function ma21_kopf(string $name): string {
    $datum = '<time data-today datetime="' . esc_attr((string) wp_date('c')) . '">' . esc_html((string) wp_date('d.m.')) . '</time>';
    $html = str_replace('{{ma:datum}}', $datum, ma21_vorlage($name));
    $d = ma21_kopf_daten();
    $html = ma21_kopf_aktuell($html);
    $html = ma21_kopf_ortswahl($html);
    return ma21_kopf_jetzt($html, $d);
}

/** Zahlen und Meldungen für den Kopf, 10 Minuten zwischengespeichert (Reset bei jeder Statusänderung von Meldungen und Terminen). */
function ma21_kopf_daten(): array {
    $d = get_transient('ma21_kopf_daten');
    if (is_array($d) && isset($d['daten'], $d['ohne_zahlen'])) return $d;
    // Keine Zahlen auf der Seite (Vorgabe Betreiber 05.10.2026: „wie viele Meldungen es gibt, ist unprofessionell“).
    $d = ['ohne_zahlen' => true, 'neu' => null, 'daten' => [], 'termin' => null];
    $neueste = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'DESC']);
    foreach ($neueste as $i => $p) {
        $iso = (string) get_post_time('c', false, $p);
        $d['daten'][] = $iso;
        if ($i === 0) $d['neu'] = ['iso' => $iso, 'zeit' => (string) get_post_time('d.m. · H:i', false, $p) . ' Uhr', 'url' => wp_make_link_relative(get_permalink($p)), 'titel' => html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8')];
    }
    $jetzt = (string) current_time('Y-m-d\TH:i');
    $t = get_posts(['post_type' => 'ma_event', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => 'ma_event_start', 'orderby' => 'meta_value', 'order' => 'ASC',
        'meta_query' => [['key' => 'ma_event_start', 'value' => $jetzt, 'compare' => '>=']]]);
    if ($t) {
        // Beginn steht als Ortszeit ohne Zone in ma_event_start; strtotime() hätte sie als UTC gelesen (zwei Stunden zu spät).
        $p = $t[0];
        try { $start = (new DateTimeImmutable((string) get_post_meta($p->ID, 'ma_event_start', true), wp_timezone()))->getTimestamp(); } catch (Exception $e) { $start = 0; }
        $wann = $start ? wp_date('d.m.', $start) . (wp_date('H:i', $start) !== '00:00' ? ', ' . wp_date('H:i', $start) . ' Uhr' : '') : '';
        $d['termin'] = ['url' => wp_make_link_relative(get_permalink($p)), 'titel' => html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8'), 'wann' => $wann];
    }
    set_transient('ma21_kopf_daten', $d, 10 * MINUTE_IN_SECONDS);
    return $d;
}
add_action('transition_post_status', function (string $neu, string $alt, WP_Post $p): void {
    if (in_array($p->post_type, ['post', 'ma_event'], true) && ($neu === 'publish' || $alt === 'publish')) delete_transient('ma21_kopf_daten');
}, 10, 3);

/** Welche Seite gerade besucht wird: nur dieser Menüpunkt trägt aria-current (die Vorlage markiert immer „Aktuell“). */
function ma21_kopf_aktuell(string $html): string {
    $html = str_replace(' aria-current="page"', '', $html);
    $pfad = '/' . trim((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH), '/') . '/';
    if ($pfad === '//') $pfad = '/';
    if (is_singular('post')) { $o = get_queried_object(); if ($o instanceof WP_Post) $pfad = '/' . ma21_ressort($o)[0] . '/'; }
    elseif (is_singular('ma_event') || is_post_type_archive('ma_event')) $pfad = '/termine/';
    elseif (is_singular('ma_club') || is_post_type_archive('ma_club')) $pfad = '/vereine/';
    if (!preg_match('#^/[a-z0-9-]+/$#', $pfad)) return $html;
    return str_replace('<a href="' . $pfad . '">', '<a href="' . $pfad . '" aria-current="page">', $html);
}

/**
 * Ausgabe-Wahl oben im Kopf entfällt (Vorgabe Betreiber 05.10.2026: alle fünf
 * Orte sind Ortsteile der Gemeinde Merzenich; sie stehen weiter im Mehr-Menü,
 * in der Schublade und im Fuß). Ortslinks zeigen auf /ort/<slug>/, auf einer
 * Ortsseite ist ihr Ort markiert. Unterseiten ohne Jetzt-Zeile verlieren die
 * dann leere Leiste ganz.
 */
function ma21_kopf_ortswahl(string $html): string {
    $html = (string) preg_replace('#<details class="ortswahl-schalter">.*?</details>#su', '', $html);
    $html = (string) preg_replace('#<nav class="ortswahl"[^>]*><div class="shell">\s*</div></nav>#u', '', $html);
    $html = str_replace('<nav class="ortswahl" aria-label="Ausgabe waehlen">', '<nav class="ortswahl" aria-label="Merzenich jetzt">', $html);
    $html = (string) preg_replace('#href="/(' . implode('|', array_keys(MA21_ORTE)) . ')/"#', 'href="/ort/$1/"', $html);
    $o = is_tax('ma_location') ? get_queried_object() : null;
    if ($o instanceof WP_Term && isset(MA21_ORTE[$o->slug])) $html = str_replace('<a href="/ort/' . $o->slug . '/">', '<a href="/ort/' . $o->slug . '/" aria-current="page">', $html);
    return $html;
}

/** „Merzenich · Jetzt“: jüngste veröffentlichte Meldung und nächster Termin, keine Zahlen (assets/kopf.js macht aus der Uhrzeit eine relative Zeit). */
function ma21_kopf_jetzt(string $html, array $d): string {
    if (!str_contains($html, 'class="jetzt"')) return $html;
    if (!empty($d['neu'])) {
        $neu = '<span class="jetzt-feld jetzt-neu">Neu <time datetime="' . esc_attr($d['neu']['iso']) . '">' . ma21_e($d['neu']['zeit']) . '</time> <a href="' . esc_url($d['neu']['url']) . '">' . ma21_e($d['neu']['titel']) . '</a></span>';
        $html = (string) preg_replace('#<span class="jetzt-feld jetzt-neu">.*?</span>#su', $neu, $html, 1);
    } else {
        $html = (string) preg_replace('#<span class="jetzt-feld jetzt-neu">.*?</span>#su', '<span class="jetzt-feld jetzt-neu" hidden></span>', $html, 1);
    }
    // „Heute n neue Meldungen“ entfällt (keine Zahlen auf der Seite); ohne das Feld rechnet assets/kopf.js auch nichts nach.
    $html = (string) preg_replace('#<span class="jetzt-feld jetzt-heute"[^>]*>(?:[^<]*)</span>#u', '', $html, 1);
    $t = $d['termin'] ?? null;
    $terminHtml = $t ? '<span class="jetzt-feld jetzt-termin">Nächster Termin <a href="' . esc_url($t['url']) . '">' . ma21_e($t['titel']) . '</a>' . ($t['wann'] !== '' ? ' · ' . ma21_e($t['wann']) : '') . '</span>' : '<span class="jetzt-feld jetzt-termin" hidden></span>';
    return (string) preg_replace('#<span class="jetzt-feld jetzt-termin"[^>]*>(?:[^<]*)</span>#u', $terminHtml, $html, 1);
}

/*
 * Zwischenspeicher (21.10.3): Der Server bei IONOS gibt HTML-Seiten eine Stunde
 * Speicherzeit mit (mod_expires), JSON sogar 28 Tage. Browser und der
 * Zwischenspeicher des Hosters zeigten dadurch bis zu eine Stunde alte Seiten:
 * neue Meldungen fehlten, entfernte Angaben standen noch da (05.10.2026).
 * Setzt PHP selbst Cache-Control und Expires, fügt mod_expires nichts hinzu.
 * Seiten: jedes Mal frisch prüfen. Daten: kurze, ausdrückliche Speicherzeit.
 */
function ma21_cache(int $sekunden): void {
    if (headers_sent()) return;
    header('Cache-Control: ' . ($sekunden > 0 ? 'public, max-age=' . $sekunden : 'no-cache, max-age=0, must-revalidate'));
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + max(0, $sekunden)) . ' GMT');
}
add_action('send_headers', function (): void {
    if (is_admin() || wp_doing_ajax() || wp_doing_cron() || (defined('REST_REQUEST') && REST_REQUEST)) return;
    ma21_cache(0);
});

/* /assets/ -> Theme-Verzeichnis static/, danach die einmal aus dem Repository
   nachgeladenen Dateien (uploads/ma-assets/assets/); das Ressort-Menü und alles
   Übrige (Poolfotos, Quellen) beantwortet PHP: Mediathek, sonst nachladen. */
add_filter('mod_rewrite_rules', function (string $regeln): string {
    $dir = trailingslashit(get_template_directory()) . 'static/';
    $rel = ltrim(str_replace(ABSPATH, '', $dir), '/');
    $nach = ma21_nachgeladen_verzeichnis() . '/assets/';
    $nachRel = ltrim(str_replace(ABSPATH, '', $nach), '/');
    $block = "# BEGIN Merzenich Aktuell Assets\n<IfModule mod_rewrite.c>\nRewriteEngine On\n"
        . "RewriteRule ^assets/ressort-menue\\.json$ index.php?ma_asset=ressort-menue.json [L,QSA]\n"
        . "RewriteCond {$dir}$1 -f\nRewriteRule ^assets/(.*)$ {$rel}$1 [L]\n"
        . "RewriteCond {$nach}$1 -f\nRewriteRule ^assets/(.*)$ {$nachRel}$1 [L]\n"
        . "RewriteRule ^assets/(.*)$ index.php?ma_asset=$1 [L,QSA]\n"
        . "</IfModule>\n# END Merzenich Aktuell Assets\n";
    return $block . $regeln;
});

/** Das Repository (Stand von main) als letzte Quelle für Dateien aus chatgpt-site/. */
const MA21_REPO = 'https://raw.githubusercontent.com/HKGrowthOperator/Merzenich-Aktuell/main/chatgpt-site';

// Nach dem Registrieren von Beitragstypen und Taxonomien (init 10), sonst
// liefert get_permalink() für Termine nur ?p= und ma_location fehlt.
add_action('init', function (): void {
    if (!isset($_GET['ma_asset'])) return;
    ma21_asset((string) wp_unslash($_GET['ma_asset']));
}, 50);

/**
 * Fehlende /assets/-Datei: zuerst die Mediathek (ma_image_static_src, alle
 * Poolfotos liegen dort), sonst einmal aus dem Repository holen und hier ablegen.
 * Die Vorschauseite auf Coolify gibt es seit 05.10.2026 nicht mehr; der Browser
 * lädt nichts von GitHub, nur von dieser Domain.
 */
function ma21_asset(string $pfad): void {
    $pfad = '/' . ltrim((string) preg_replace('#/+#', '/', $pfad), '/');
    if ($pfad === '/' || str_contains($pfad, '..') || !preg_match('#^/[\w./@%-]+$#u', $pfad)) { status_header(404); nocache_headers(); exit; }
    if ($pfad === '/ressort-menue.json') ma21_ressort_menue_ausgeben();
    $voll = '/assets' . $pfad;
    $ziel = ma21_asset_lokal($voll);
    if ($ziel === '') $ziel = ma21_asset_nachladen($voll);
    if ($ziel === '') { status_header(404); nocache_headers(); exit; }
    header('Cache-Control: public, max-age=3600');
    wp_redirect($ziel, 302, 'Merzenich Aktuell');
    exit;
}

/** Medium der Mediathek zu einem statischen Pfad (Größenendung -480/-800/… wird ignoriert), sonst leer. */
function ma21_asset_lokal(string $pfad): string {
    if (!preg_match('#^(/assets/[\w./-]+?)(?:-(\d{3,4}))?\.(webp|jpe?g|png)$#i', $pfad, $m)) return '';
    global $wpdb;
    $basis = $m[1]; $breite = (int) ($m[2] ?? 0);
    $ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'ma_image_static_src' AND meta_value LIKE %s ORDER BY post_id ASC LIMIT 8", $wpdb->esc_like($basis) . '%'));
    foreach ((array) $ids as $id) {
        $src = (string) get_post_meta((int) $id, 'ma_image_static_src', true);
        if (!preg_match('#^' . preg_quote($basis, '#') . '(?:-\d{3,4})?\.(webp|jpe?g|png)$#i', $src)) continue;
        $datei = get_attached_file((int) $id);
        if (!$datei || !file_exists($datei)) continue;
        $groesse = !$breite || $breite > 1024 ? 'full' : ($breite <= 480 ? 'ma-480' : ($breite <= 768 ? 'medium_large' : 'large'));
        $url = wp_get_attachment_image_url((int) $id, $groesse);
        if ($url) return $url;
    }
    return '';
}

/** Ablage der aus dem Repository nachgeladenen Dateien (uploads/ma-assets). */
function ma21_nachgeladen_verzeichnis(): string {
    return trailingslashit((string) wp_upload_dir(null, false)['basedir']) . 'ma-assets';
}

/** Ist der Pfad als nachladbare Datei erlaubt? Nur Medien, Stile, Skripte, Schriften und Daten; nie PHP. */
function ma21_nachladbar(string $voll): bool {
    return (bool) preg_match('#^/assets/[\w./@%-]+\.(webp|jpe?g|png|gif|svg|avif|ico|css|js|json|woff2?|ttf|txt|pdf)$#i', $voll) && !str_contains($voll, '..');
}

/**
 * Holt eine fehlende Datei einmal aus dem Repository und legt sie unter
 * uploads/ma-assets/ ab (danach liefert Apache sie direkt, siehe .htaccess).
 * Rückgabe: ihre Adresse auf dieser Domain oder leer (dann 404). Fehlt die Datei
 * auch im Repository, wird eine Stunde lang nicht erneut gefragt.
 */
function ma21_asset_nachladen(string $voll): string {
    if (!ma21_nachladbar($voll)) return '';
    $ziel = ma21_nachgeladen_verzeichnis() . $voll;
    $url = trailingslashit((string) wp_upload_dir(null, false)['baseurl']) . 'ma-assets' . $voll;
    if (is_file($ziel)) return $url;
    $sperre = 'ma21_fehlt_' . md5($voll);
    if (get_transient($sperre)) return '';
    $r = wp_remote_get(MA21_REPO . $voll, ['timeout' => 10]);
    $body = !is_wp_error($r) && (int) wp_remote_retrieve_response_code($r) === 200 ? (string) wp_remote_retrieve_body($r) : '';
    if ($body === '' || strlen($body) > 15 * MB_IN_BYTES) { set_transient($sperre, 1, HOUR_IN_SECONDS); return ''; }
    if (!wp_mkdir_p(dirname($ziel)) || file_put_contents($ziel, $body) === false) return '';
    return $url;
}

/**
 * Datei aus chatgpt-site/ im Repository (Stand von main), zwischengespeichert;
 * antwortet GitHub nicht, gilt die letzte gute Kopie. Für Daten, die die Kette
 * laufend erneuert (Spielstand, Stellen- und Immobilienmarkt).
 */
function ma21_repo_datei(string $pfad, int $ttl = HOUR_IN_SECONDS): string {
    $schluessel = 'ma21_repo_' . md5($pfad);
    $t = get_transient($schluessel);
    if (is_string($t) && $t !== '') return $t;
    $r = wp_remote_get(MA21_REPO . '/' . ltrim($pfad, '/'), ['timeout' => 8]);
    $body = !is_wp_error($r) && (int) wp_remote_retrieve_response_code($r) === 200 ? (string) wp_remote_retrieve_body($r) : '';
    if ($body !== '') {
        set_transient($schluessel, $body, $ttl);
        update_option($schluessel . '_kopie', $body, false);
        return $body;
    }
    $kopie = (string) get_option($schluessel . '_kopie', '');
    if ($kopie !== '') set_transient($schluessel, $kopie, 10 * MINUTE_IN_SECONDS);
    return $kopie;
}

/**
 * Ressort-Menü (assets/ressort-menue.json, liest ressort-dropdowns.js): Gruppen
 * und Links aus der statischen Datei, „Neu im Ressort“ aus den veröffentlichten
 * Beiträgen dieser WordPress-Installation, nie aus dem redaktionellen Stand
 * im Repository (dort stehen auch noch nicht freigegebene Meldungen).
 */
function ma21_ressort_menue_ausgeben(): void {
    $datei = trailingslashit(get_template_directory()) . 'static/ressort-menue.json';
    $d = is_readable($datei) ? json_decode((string) file_get_contents($datei), true) : null;
    if (!is_array($d) || empty($d['ressorts']) || !is_array($d['ressorts'])) { status_header(404); nocache_headers(); exit; }
    foreach ($d['ressorts'] as $pfad => &$r) {
        if (!is_array($r)) continue;
        $r['neu'] = ma21_menue_neu((string) $pfad, (array) ($r['neu'] ?? []));
        // Unternehmen: Firmenliste links, Beiträge rechts (wie Oberberg Aktuell).
        if ($pfad === '/unternehmen/' && function_exists('ma21_menue_firmen')) $r['firmen'] = ma21_menue_firmen();
    }
    unset($r);
    $d['standWordPress'] = (string) wp_date('c');
    header('Content-Type: application/json; charset=utf-8');
    ma21_cache(300);
    echo wp_json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function ma21_menue_bild(WP_Post $p): ?array {
    $id = (int) get_post_thumbnail_id($p->ID);
    if (!$id) return null;
    $url = wp_get_attachment_image_url($id, 'ma-480');
    if (!$url) return null;
    $typ = (string) (get_post_meta($p->ID, 'ma_image_type', true) ?: get_post_meta($id, 'ma_image_type', true));
    return ['src' => $url, 'alt' => (string) (get_post_meta($id, '_wp_attachment_image_alt', true) ?: get_the_title($id)), 'symbol' => $typ === 'symbol'];
}

/** „Neu im Ressort“: Meldungen der Rubrik, alle Meldungen (Aktuell) oder kommende Termine; unbekannte Ressorts behalten die statische Liste. */
function ma21_menue_neu(string $pfad, array $statisch): array {
    $slug = trim($pfad, '/');
    $titel = fn(WP_Post $p): string => html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8');
    if ($slug === 'termine') {
        $jetzt = (string) current_time('Y-m-d\TH:i');
        $l = get_posts(['post_type' => 'ma_event', 'post_status' => 'publish', 'posts_per_page' => 4, 'meta_key' => 'ma_event_start', 'orderby' => 'meta_value', 'order' => 'ASC', 'meta_query' => [['key' => 'ma_event_start', 'value' => $jetzt, 'compare' => '>=']]]);
        return array_map(function (WP_Post $p) use ($titel): array {
            $start = function_exists('ma_event_timestamp') ? (int) ma_event_timestamp($p->ID, 'start') : 0;
            return ['titel' => $titel($p), 'url' => wp_make_link_relative(get_permalink($p)), 'ort' => (string) (get_post_meta($p->ID, 'ma_event_place', true) ?: (MA21_ORTE[ma21_ort($p)] ?? ucfirst(ma21_ort($p)))), 'datum' => $start ? (string) wp_date('c', $start) : (string) get_post_meta($p->ID, 'ma_event_start', true), 'bild' => ma21_menue_bild($p), 'termin' => true];
        }, $l);
    }
    $args = ['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 4, 'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true];
    if ($slug === 'unternehmen') { $ids = ma21_unternehmen_ids(); if (!$ids) return []; $args['post__in'] = $ids; }
    elseif ($slug !== 'nachrichten') {
        if (!get_category_by_slug($slug)) {
            // Kein Ressort mit eigener Rubrik (z. B. Unternehmen): statische Liste, aber nur Beiträge, die hier veröffentlicht sind.
            return array_values(array_filter($statisch, function ($e): bool {
                if (!is_array($e) || empty($e['url'])) return false;
                $id = url_to_postid(home_url((string) $e['url']));
                return $id > 0 && get_post_status($id) === 'publish';
            }));
        }
        $args['category_name'] = $slug;
    }
    // Anriss nur, wo das Menü Karten mit Text zeigt (Wirtschaft im Unternehmen-Menü).
    $anriss = in_array($slug, ['wirtschaft', 'unternehmen'], true);
    return array_map(fn(WP_Post $p): array => ['titel' => $titel($p), 'url' => wp_make_link_relative(get_permalink($p)), 'ort' => (string) (MA21_ORTE[ma21_ort($p)] ?? ucfirst(ma21_ort($p))), 'datum' => (string) get_post_time('c', false, $p), 'bild' => ma21_menue_bild($p)]
        + ($anriss ? ['anriss' => html_entity_decode(wp_html_excerpt(ma21_teaser($p), 170, ' …'), ENT_QUOTES, 'UTF-8')] : []), get_posts($args));
}
add_action('after_switch_theme', function (): void { flush_rewrite_rules(true); });

/* Kopf aufräumen: WordPress-Blockstile und Emoji-Skripte gibt es auf der
   statischen Seite nicht, sie verschieben Abstände. */
add_action('wp_enqueue_scripts', function (): void {
    if (ma21_legacy()) return;
    foreach (['wp-block-library', 'wp-block-library-theme', 'global-styles', 'classic-theme-styles'] as $h) wp_dequeue_style($h);
}, 100);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

/** Vorlagen mit eigenem, älterem Markup (Märkte, Anzeigen, Betriebe) brauchen noch die alten Stile. */
function ma21_legacy(): bool {
    // Vereinsprofile haben seit 01.10.2026 eine eigene Vorlage im neuen Markup (single-ma_club.php).
    $alt = ['ma_property', 'ma_job', 'ma_obituary', 'ma_family_notice', 'ma_business', 'ma_tip', 'ma_event', 'ma_club'];
    if (is_singular('ma_club')) return false;
    // Stellen, Immobilien und Tipps haben seit 21.10.0 eigene Übersichten im neuen Markup (inc/markt.php).
    if (is_post_type_archive(['ma_job', 'ma_property', 'ma_tip'])) return false;
    return is_singular($alt) || is_post_type_archive($alt);
}

add_filter('body_class', function (array $k): array { $k[] = 'v20'; return $k; });

/* ------------------------------------------------------------ Bausteine */

function ma21_e($s): string { return esc_html((string) $s); }

function ma21_ressort(WP_Post $p): array {
    foreach (get_the_category($p->ID) as $c) {
        if (isset(MA21_RESSORT[$c->slug])) return [$c->slug, MA21_RESSORT[$c->slug]];
    }
    $c = get_the_category($p->ID)[0] ?? null;
    return $c ? [$c->slug, $c->name] : ['nachrichten', 'Nachrichten'];
}

function ma21_ort(WP_Post $p): string {
    $t = get_the_terms($p->ID, 'ma_location');
    if (is_array($t)) foreach ($t as $term) if (isset(MA21_ORTE[$term->slug])) return $term->slug;
    return 'merzenich';
}

/** Ortsmarke: MERZENICH, Ortsteil, Rubrik (Ressort oder Kicker). */
function ma21_marke(WP_Post $p, string $rubrik = 'ressort'): string {
    $ort = ma21_ort($p);
    $label = $rubrik === 'kicker' ? (get_post_meta($p->ID, 'ma_kicker', true) ?: ma21_ressort($p)[1]) : ma21_ressort($p)[1];
    return '<p class="marke"><span class="marke-ort">Merzenich</span>'
        . ($ort !== 'merzenich' ? '<span class="marke-teil"> · ' . ma21_e(MA21_ORTE[$ort]) . '</span>' : '')
        . '<span class="marke-rubrik">' . ma21_e($label) . '</span>'
        // Bezahlte Unternehmenspräsentation (Plugin, werbung-stat.php): immer gekennzeichnet.
        . (function_exists('ma_ist_gesponsert') && ma_ist_gesponsert($p) ? '<span class="gesponsert">Anzeige · Gesponsert</span>' : '') . '</p>';
}

function ma21_zeit(WP_Post $p, bool $lang = false): string {
    $fmt = $lang ? 'd.m.Y · H:i' : 'd.m. · H:i';
    return '<time datetime="' . esc_attr(get_post_time('c', false, $p)) . '">' . ma21_e(get_post_time($fmt, false, $p, true)) . ' Uhr</time>';
}

function ma21_lesezeit(WP_Post $p): int {
    $w = str_word_count(wp_strip_all_tags($p->post_excerpt . ' ' . $p->post_content));
    return max(1, (int) round($w / 200));
}

function ma21_teaser(WP_Post $p): string {
    $t = trim($p->post_excerpt) ?: wp_trim_words(wp_strip_all_tags($p->post_content), 30, ' …');
    return $t;
}

/** Bilddaten des Beitragsbilds oder null. */
function ma21_bild(WP_Post $p): ?array {
    $id = (int) get_post_thumbnail_id($p->ID);
    if (!$id) return null;
    $voll = wp_get_attachment_image_src($id, 'full');
    if (!$voll) return null;
    $alt = get_post_meta($id, '_wp_attachment_image_alt', true) ?: get_the_title($id);
    $typ = get_post_meta($p->ID, 'ma_image_type', true) ?: get_post_meta($id, 'ma_image_type', true);
    return ['id' => $id, 'src' => $voll[0], 'w' => (int) $voll[1], 'h' => (int) $voll[2], 'srcset' => (string) wp_get_attachment_image_srcset($id, 'full'),
        'alt' => $alt, 'typ' => (string) $typ, 'credit' => ma_credit_kurz((string) get_post_meta($p->ID, 'ma_image_credit', true), (string) get_post_meta($p->ID, 'ma_image_license', true))];
}

/** Echtes, redaktionell nutzbares Bild: kein Logo, kein Wappen. */
function ma21_echtes_bild(?array $b): bool {
    return $b && !preg_match('/logo|wappen/i', $b['alt'] . ' ' . $b['src']);
}

function ma21_img(array $b, string $sizes, bool $eager): string {
    return '<img src="' . esc_url($b['src']) . '"' . ($b['srcset'] ? ' srcset="' . esc_attr($b['srcset']) . '"' : '')
        . ' sizes="' . esc_attr($sizes) . '" alt="' . esc_attr($b['alt']) . '"' . ($b['w'] && $b['h'] ? ' width="' . $b['w'] . '" height="' . $b['h'] . '"' : '')
        . ' loading="' . ($eager ? 'eager' : 'lazy') . '"' . ($eager ? ' fetchpriority="high"' : '') . ' decoding="async" data-editorial-image class="">';
}

function ma21_badge(array $b, bool $start): string {
    $t = ($start ? MA21_HINWEIS_START : MA21_HINWEIS)[$b['typ']] ?? '';
    return $t !== '' ? '<span class="badge">' . ma21_e($t) . '</span>' : '';
}

function ma21_story_id(WP_Post $p): string { return get_post_time('Y-m-d', false, $p) . '-' . $p->post_name; }

function ma21_bildflaeche(WP_Post $p, ?array $b, string $sizes, bool $eager): string {
    if (!$b) return '';
    return '<a class="karte-bild" href="' . esc_url(get_permalink($p)) . '" tabindex="-1" aria-hidden="true"><div class="media">'
        . ma21_img($b, $sizes, $eager) . ma21_badge($b, true) . '</div></a>';
}

/** Datenattribute einer Karte für den Bearbeitungsmodus (includes/layout-front.php): Beitrag, Platz, fest. */
function ma21_karte_attr(WP_Post $p, string $slot = '', bool $fest = false): string {
    return ' data-story="' . esc_attr(ma21_story_id($p)) . '" data-post="' . (int) $p->ID . '"' . ($slot !== '' ? ' data-slot="' . esc_attr($slot) . '"' : '') . ($fest ? ' data-fest="1"' : '');
}

/** Startseitenkarte in den Größen xl (Aufmacher), r/u (Bühne), l, m, s (Rubrikflächen). */
function ma21_karte(WP_Post $p, string $g, string $tag = 'h3', string $slot = '', bool $fest = false, string $sektion = '', int $i = 0): string {
    $url = esc_url(get_permalink($p)); $titel = ma21_e(get_the_title($p)); $b = ma21_bild($p); $attr = ma21_karte_attr($p, $slot, $fest);
    $kopf = "<{$tag}><a href=\"{$url}\">{$titel}</a></{$tag}>";
    if ($g === 'xl') {
        return "<article class=\"front-lead\"{$attr}>" . ma21_bildflaeche($p, $b, MA21_SIZES['xl'], true)
            . '<div class="front-lead-copy">' . ma21_marke($p) . "<h1><a href=\"{$url}\">{$titel}</a></h1><p>" . ma21_e(ma21_teaser($p)) . '</p>'
            . '<div class="meta">' . ma21_zeit($p) . '<span>' . ma21_lesezeit($p) . ' Min. Lesezeit</span></div></div></article>';
    }
    if ($g === 'r' || $g === 'u') {
        return "<article class=\"front-neben-story buehne-karte buehne-karte--{$g}\"{$attr}>" . ma21_bildflaeche($p, $b, MA21_SIZES[$g], false)
            . '<div class="karte-text">' . ma21_marke($p) . "<h2><a href=\"{$url}\">{$titel}</a></h2><div class=\"meta\">" . ma21_zeit($p) . '</div></div></article>';
    }
    // Feste Plätze dürfen auch ohne Bild belegt sein (Layout-Karte): dann ohne Bildfläche.
    if ($g === 'l') {
        return "<article class=\"desk-karte desk-karte--gross" . ($b ? '' : ' desk-karte--ohne-bild') . "\"{$attr}>" . ma21_bildflaeche($p, $b, ma21_sizes('l', $sektion, $i), false)
            . '<div class="karte-text">' . ma21_marke($p) . $kopf . '<p class="dek">' . ma21_e(ma21_teaser($p)) . '</p><div class="meta">' . ma21_zeit($p) . '</div></div></article>';
    }
    if ($g === 'm') {
        return "<article class=\"desk-karte desk-karte--mittel" . ($b ? '' : ' desk-karte--ohne-bild') . "\"{$attr}>" . ma21_bildflaeche($p, $b, ma21_sizes('m', $sektion, $i), false)
            . '<div class="karte-text">' . ma21_marke($p) . $kopf . '<div class="meta">' . ma21_zeit($p) . '</div></div></article>';
    }
    $mitBild = ma21_echtes_bild($b);
    return '<article class="front-zeile' . ($mitBild ? ' front-zeile--bild' : '') . "\"{$attr}>"
        . ($mitBild ? ma21_bildflaeche($p, $b, ma21_sizes('s', $sektion, $i), false) : '')
        . '<div class="karte-text">' . ma21_marke($p) . $kopf . '<div class="meta">' . ma21_zeit($p) . '</div></div></article>';
}

/* ------------------------------------------------------------ Startseite */

/** Platzwahl der Redaktion (Metabox im Plugin): auto, aufmacher, buehne-1 … buehne-4, aus. */
function ma21_startplatz(WP_Post $p): string {
    $v = (string) get_post_meta($p->ID, 'ma_startplatz', true);
    if ($v === '' && get_post_meta($p->ID, 'ma_top_pinned', true) === '1') {
        $bis = (string) get_post_meta($p->ID, 'ma_top_until', true);
        if ($bis === '' || strtotime($bis) > time()) return 'aufmacher';
    }
    return $v ?: 'auto';
}

function ma21_ist_sport(WP_Post $p): bool { return has_category('sport', $p); }

/**
 * Belegung der Startseite wie deploy/inhaltsindex.mjs: Aufmacher, vier
 * Nebenmeldungen (zwei rechts, zwei darunter, daneben eine Anzeige), dann die
 * Rubrikflächen. Die Startseiten-Freigabe entscheidet, ob eine Meldung
 * erscheint; die Relevanz 1–10 (mit Aktualität) entscheidet, wo.
 * Feste Plätze der Redaktion (Layout-Karte, Plugin includes/layout.php) gehen
 * vor, auch gegen die Bildregeln; freie Plätze füllt die jüngste passende
 * Meldung. „aus“ = nur in der eigenen Rubrik, nie auf der Startseite.
 * $neu = true leert den Zwischenspeicher (nach einer Änderung im Bearbeitungsmodus).
 */
function ma21_startseite_belegung(bool $neu = false): array {
    static $cache = null;
    if ($cache !== null && !$neu) return $cache;
    $alle = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 120, 'orderby' => 'date', 'order' => 'DESC', 'suppress_filters' => false]);
    // Startseiten-Freigabe und Relevanz 1–10 (Plugin, includes/relevanz.php):
    // „Nur in der Rubrik“ (aus) bleibt draußen, alles andere sortiert die
    // zentrale Relevanzlogik. Aufmacher und Bühne bekommen zuerst, was frisch
    // die Schwellen erreicht (Zone hero, dann buehne), danach den höchsten Wert.
    // Seit 03.10.2026 mit eigener Startseiten-Freigabe (ma_relevanz_startseite), ohne Plugin wie bisher.
    $alle = array_values(array_filter($alle, fn($p) => function_exists('ma_relevanz_startseite') ? ma_relevanz_startseite($p) : ma21_startplatz($p) !== 'aus'));
    if (function_exists('ma_relevanz_sortieren')) $alle = ma_relevanz_sortieren($alle);
    $zone = fn($p) => function_exists('ma_relevanz_zone') ? ma_relevanz_zone($p) : 'feed';
    $rang = ['hero' => 0, 'buehne' => 1];
    $pos = []; $z = [];
    foreach ($alle as $i => $p) { $pos[$p->ID] = $i; $z[$p->ID] = $rang[$zone($p)] ?? 2; }
    $vorrang = $alle;
    usort($vorrang, fn($a, $b) => [$z[$a->ID], $pos[$a->ID]] <=> [$z[$b->ID], $pos[$b->ID]]);
    $termine = function_exists('ma21_kommende_termine') ? count(ma21_kommende_termine(4)) : 3;
    $SEK = [
        'blaulicht' => ['titel' => 'Blaulicht', 'mehr' => '/blaulicht/', 'mehrText' => 'Alle Einsatzmeldungen', 'nimm' => fn($p) => has_category('blaulicht', $p)],
        'rathaus' => ['titel' => 'Politik & Gemeinde', 'mehr' => '/rathaus/', 'mehrText' => 'Zum Rathaus', 'nimm' => fn($p) => has_category('rathaus', $p)],
        'wirtschaft' => ['titel' => 'Wirtschaft', 'mehr' => '/wirtschaft/', 'mehrText' => 'Zur Wirtschaft', 'nimm' => fn($p) => has_category('wirtschaft', $p)],
        'vereine' => ['titel' => 'Vereine & Menschen', 'mehr' => '/vereine/', 'mehrText' => 'Zu den Vereinen', 'nimm' => fn($p) => (has_category('vereine', $p) || has_category('menschen', $p)) && !ma21_ist_sport($p)],
        'gemeinde' => ['titel' => 'Nachrichten aus Merzenich', 'mehr' => '/nachrichten/', 'mehrText' => 'Alle Meldungen', 'nimm' => fn($p) => !ma21_ist_sport($p) && !has_category('tipp', $p), 'jeRessort' => 3, 'fenster' => 44, 'zeilen' => max(2, min(4, $termine))],
    ];
    $regeln = [
        'vorrang' => $vorrang,
        'buehnenTauglich' => fn($p) => !ma21_ist_sport($p) && !has_category('tipp', $p) && ma21_echtes_bild(ma21_bild($p)),
        'breite' => fn($p) => (ma21_bild($p)['w'] ?? 0),
        'motiv' => fn($p) => (int) get_post_thumbnail_id($p->ID),
        'bildtyp' => fn($p) => (string) (ma21_bild($p)['typ'] ?? ''),
        'bild' => fn($p) => ma21_bild($p),
        'echtesBild' => fn($p) => ma21_echtes_bild(ma21_bild($p)),
        'ressort' => fn($p) => ma21_ressort($p)[0],
        'kategorie' => fn($p, $slug) => has_category($slug, $p),
        'sektionen' => $SEK,
        'holen' => fn($id) => get_post($id) ?: null,
    ];
    if (function_exists('ma_layout_aufloesen')) return $cache = ma_layout_aufloesen('startseite', ma_layout_feste_plaetze('startseite'), $alle, $regeln);
    return $cache = ma21_belegung_ohne_plugin($alle, $regeln);
}

/** Notnagel ohne Plugin: Aufmacher und Bühne nach Reihenfolge, keine Rubrikflächen. Die Seite bleibt lesbar. */
function ma21_belegung_ohne_plugin(array $alle, array $r): array {
    $aufmacher = null; $neben = array_fill(1, 4, null); $pl = [];
    foreach ($r['vorrang'] as $p) {
        if (!$r['buehnenTauglich']($p)) continue;
        if (!$aufmacher && $r['breite']($p) >= 480) { $aufmacher = $p; $pl['aufmacher'] = ['post' => $p->ID, 'fest' => false, 'warnungen' => []]; continue; }
        $i = array_search(null, $neben, true);
        if ($i === false) break;
        $neben[$i] = $p; $pl["buehne-$i"] = ['post' => $p->ID, 'fest' => false, 'warnungen' => []];
    }
    return ['aufmacher' => $aufmacher, 'neben' => $neben, 'sektionen' => [], 'plaetze' => $pl];
}

/** Werbefläche der Vorlage; mit laufender Anzeige aus der Werbeverwaltung ersetzt sie das Muster (Plugin, includes/ads.php). */
function ma21_werbung(string $marker): string {
    if (function_exists('ma_render_werbeplatz') && function_exists('ma_ad_marker_slot')) {
        $slot = ma_ad_marker_slot($marker);
        if ($slot !== '' && ma_active_ads($slot)) { $echt = ma_render_werbeplatz($slot, $marker); if ($echt !== '') return $echt; }
    }
    return trim(ma21_vorlage("werbung-{$marker}.html"));
}

function ma21_block(string $name): string {
    $b = ma21_startseite_belegung();
    $pl = $b['plaetze'] ?? [];
    $fest = fn(string $slot) => !empty($pl[$slot]['fest']);
    if ($name === 'oben') {
        $n = $b['neben'];
        // Jeder Platz bleibt, wo er ist: Bühne 1–2 rechts, 3–4 unten. Leere Plätze rutschen nicht.
        $rechts = ''; foreach ([1, 2] as $i) if (!empty($n[$i])) $rechts .= ma21_karte($n[$i], 'r', 'h3', "buehne-$i", $fest("buehne-$i"));
        $unten = ''; foreach ([3, 4] as $i) if (!empty($n[$i])) $unten .= ma21_karte($n[$i], 'u', 'h3', "buehne-$i", $fest("buehne-$i"));
        $inhalt = ($b['aufmacher'] ? ma21_karte($b['aufmacher'], 'xl', 'h3', 'aufmacher', $fest('aufmacher')) : '')
            . ($rechts !== '' ? '<div class="buehne-rechts">' . $rechts . '</div>' : '')
            . '<div class="buehne-unten">' . $unten . ma21_werbung('buehne') . '</div>';
        return '<!-- start:oben:start --><section class="shell buehne" data-editorial-verified="1" aria-label="Die wichtigsten Nachrichten">' . $inhalt . '</section><!-- start:oben:end -->';
    }
    $sek = $b['sektionen'][$name] ?? null;
    $rahmen = fn($x) => "<!-- start:{$name}:start -->{$x}<!-- start:{$name}:end -->";
    if (!$sek || !($sek['gross'] || $sek['mittel'] || $sek['zeilen'])) return $rahmen('');
    $s = $sek['s'];
    $kopf = '<div class="desk-heading"><div><h2>' . ma21_e($s['titel']) . '</h2></div><a class="desk-more" href="' . esc_url(home_url($s['mehr'])) . '">' . ma21_e($s['mehrText']) . '</a></div>';
    // Platz je Karte aus der Belegung (bei festen Plätzen kann die Reihe Lücken haben).
    $slotVon = function (string $art, WP_Post $p, int $i) use ($pl, $name): string {
        foreach ($pl as $slot => $e) if ($e['post'] === $p->ID && str_starts_with($slot, "{$name}.{$art}.")) return $slot;
        return "{$name}.{$art}." . ($i + 1);
    };
    $reihe = function (string $klasse, array $l, string $g, string $art) use ($slotVon, $fest, $name): string {
        if (!$l) return '';
        $h = '';
        foreach (array_values($l) as $i => $p) { $slot = $slotVon($art, $p, $i); $h .= ma21_karte($p, $g, 'h3', $slot, $fest($slot), $name, $i); }
        return "<div class=\"{$klasse}\">{$h}</div>";
    };
    return $rahmen("<section class=\"desk shell\" data-sektion=\"{$name}\">{$kopf}" . $reihe('desk-gross', $sek['gross'], 'l', 'gross') . $reihe('desk-mittel', $sek['mittel'], 'm', 'mittel') . $reihe('desk-zeilen', $sek['zeilen'], 's', 'zeilen') . '</section>');
}

/** Startseite: Vorlage mit gefüllten Nachrichtenblöcken und Werbeflächen. */
function ma21_startseite(): string {
    $html = ma21_vorlage('startseite.html');
    $html = (string) preg_replace_callback('/\{\{ma:([a-z0-9:-]+)\}\}/', fn($m) => str_starts_with($m[1], 'werbung:') ? ma21_werbung(substr($m[1], 8)) : ma21_block($m[1]), $html);
    // Foto des Tages wählt WordPress selbst (die Vorlage kann leer oder vom Bautag sein).
    return (string) preg_replace_callback('#<!-- fotodestages:start -->.*?<!-- fotodestages:end -->#s', fn() => '<!-- fotodestages:start -->' . ma21_foto_des_tages() . '<!-- fotodestages:end -->', $html, 1);
}

/** Motiv eines Bildpfads ohne Breite und Endung (wie deploy/foto-des-tages.mjs): /assets/places/golzheim-1440.webp → places/golzheim. */
function ma21_motiv_schluessel(string $src): string {
    $s = (string) preg_replace('#[?\#].*$#', '', $src);
    $s = (string) preg_replace('#^.*?/assets/#', '', $s);
    $s = (string) preg_replace('#-\d{3,4}(?=\.[a-z0-9]+$)#i', '', $s);
    return (string) preg_replace('#\.[a-z0-9]+$#i', '', $s);
}

/**
 * Wahl des Fotos des Tages: datierte Leser-Einsendung für heute, sonst reihum
 * nach Tag eine gesichtete Ortsansicht. Motive, die schon als Bild einer
 * Meldung auf der Startseite stehen ($belegt), kommen erst später dran; sind
 * alle belegt, gilt die volle Reihe. So fällt die Fläche nie weg.
 */
function ma21_foto_des_tages_wahl(array $d, string $heute, array $belegt): ?array {
    foreach ((array) ($d['eintraege'] ?? []) as $e) if (is_array($e) && ($e['datum'] ?? '') === $heute && !empty($e['src'])) return $e;
    $alle = array_values(array_filter((array) ($d['alle'] ?? $d['reihe'] ?? []), fn($r) => is_array($r) && !empty($r['src']) && !empty($r['alt'])));
    if (!$alle) return null;
    $frei = array_values(array_filter($alle, fn($r) => !in_array(ma21_motiv_schluessel((string) $r['src']), $belegt, true)));
    $reihe = $frei ?: $alle;
    return $reihe[intdiv((int) strtotime($heute . ' 00:00:00 UTC'), 86400) % count($reihe)];
}

/** Motive der Meldungsbilder auf der Startseite (Bühne und Rubrikflächen). */
function ma21_startseite_motive(): array {
    $b = ma21_startseite_belegung();
    $posts = array_merge([$b['aufmacher'] ?? null], array_values((array) ($b['neben'] ?? [])));
    foreach ((array) ($b['sektionen'] ?? []) as $sek) foreach (['gross', 'mittel', 'zeilen'] as $art) $posts = array_merge($posts, array_values((array) ($sek[$art] ?? [])));
    $motive = [];
    foreach ($posts as $p) {
        if (!$p instanceof WP_Post) continue;
        $src = (string) get_post_meta((int) get_post_thumbnail_id($p->ID), 'ma_image_static_src', true);
        if ($src !== '') $motive[] = ma21_motiv_schluessel($src);
    }
    return array_values(array_unique($motive));
}

/**
 * Foto des Tages auf der Startseite (Wunsch KBS 26.09.2026; Vorgabe Betreiber
 * 05.10.2026: „muss auf jeden Fall mit rein“). Daten: static/foto-des-tages.json
 * aus deploy/foto-des-tages.mjs. Poolfotos kommen aus der Mediathek, Ortsansichten
 * aus /assets/. assets/foto-des-tages.js tauscht nur, wenn die Seite von gestern ist.
 */
function ma21_foto_des_tages(): string {
    $datei = trailingslashit(get_template_directory()) . 'static/foto-des-tages.json';
    $d = is_readable($datei) ? json_decode((string) file_get_contents($datei), true) : null;
    if (!is_array($d)) return '';
    $heute = (string) wp_date('Y-m-d');
    $f = ma21_foto_des_tages_wahl($d, $heute, ma21_startseite_motive());
    if (!$f) return '';
    $src = (string) $f['src']; $srcset = (string) ($f['srcset'] ?? '');
    $mediathek = function_exists('ma21_asset_lokal') ? ma21_asset_lokal($src) : '';
    if ($mediathek !== '') {
        $id = attachment_url_to_postid($mediathek);
        $src = $mediathek;
        $srcset = $id ? (string) wp_get_attachment_image_srcset($id, 'full') : '';
    }
    $tage = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
    $monate = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
    $ts = (int) strtotime($heute . ' 12:00:00 UTC');
    $datum = $tage[(int) gmdate('w', $ts)] . ', ' . gmdate('j', $ts) . '. ' . $monate[(int) gmdate('n', $ts) - 1];
    $credit = ma21_e((string) ($f['credit'] ?? ''));
    if (!empty($f['quelle'])) $credit = '<a href="' . esc_url((string) $f['quelle']) . '" target="_blank" rel="noopener">' . $credit . '</a>';
    return '<section class="ansichten foto-des-tages" aria-labelledby="ansichten-titel" data-foto-des-tages>'
        . '<figure class="ansichten-bild shell"><img id="foto-des-tages-bild" src="' . esc_url(str_starts_with($src, '/') ? home_url($src) : $src) . '"'
        . ($srcset !== '' ? ' srcset="' . esc_attr(str_starts_with($srcset, '/') ? (string) preg_replace('#(^|,\s*)/#', '$1' . home_url('/'), $srcset) : $srcset) . '" sizes="(max-width: 1440px) 100vw, 1440px"' : '')
        . ' alt="' . esc_attr((string) $f['alt']) . '" width="1440" height="960" loading="lazy" decoding="async"></figure>'
        . '<div class="shell ansichten-text">'
        . '<p class="ansichten-marke">Foto des Tages · <time id="foto-des-tages-datum" datetime="' . esc_attr($heute) . '">' . esc_html($datum) . '</time></p>'
        . '<h2 id="ansichten-titel" data-ansicht-ort>' . ma21_e((string) (($f['ort'] ?? '') ?: 'Gemeinde Merzenich')) . '</h2>'
        . '<p class="ansichten-beschreibung" data-ansicht-text>' . ma21_e(rtrim((string) $f['alt'], '.')) . '.</p>'
        . '<p class="ansichten-credit" id="foto-des-tages-credit">Foto: ' . $credit . '</p>'
        . '<a class="ansichten-senden" href="' . esc_url(home_url('/meldung-senden/#formular')) . '">Ihr Foto des Tages einsenden</a>'
        . '</div></section>';
}

/** Kommende Termine (ma_event), nach Beginn sortiert. */
function ma21_kommende_termine(int $n): array {
    $jetzt = current_time('Y-m-d\TH:i');
    $q = get_posts(['post_type' => 'ma_event', 'post_status' => 'publish', 'posts_per_page' => 50, 'meta_key' => 'ma_event_start', 'orderby' => 'meta_value', 'order' => 'ASC']);
    $raus = [];
    foreach ($q as $p) {
        $ende = get_post_meta($p->ID, 'ma_event_end', true) ?: substr((string) get_post_meta($p->ID, 'ma_event_start', true), 0, 10) . 'T23:59';
        if ($ende >= $jetzt) $raus[] = $p;
        if (count($raus) >= $n) break;
    }
    return $raus;
}

/* ------------------------------------------------------------ Listen */

/** Karte im „Weiterlesen“-Block unter einer Meldung. */
function ma21_news_card(WP_Post $p): string {
    $url = esc_url(get_permalink($p)); $titel = ma21_e(get_the_title($p)); $b = ma21_bild($p);
    return '<article class="news-card">' . ($b ? "\n  <a href=\"{$url}\" tabindex=\"-1\" aria-hidden=\"true\"><div class=\"media\">" . ma21_img($b, MA21_SIZES['news-card'], false) . ma21_badge($b, false) . '</div></a>' : '')
        . "\n  <div class=\"news-card-body\">\n    " . ma21_marke($p, 'kicker') . "\n    <h3><a href=\"{$url}\">{$titel}</a></h3>\n    <p class=\"dek\">" . ma21_e(ma21_teaser($p)) . "</p>\n    <div class=\"meta\">" . ma21_zeit($p, true) . '</div>'
        . "<div class=\"story-actions\"><a class=\"read-more\" href=\"{$url}\">Mehr lesen<span class=\"sr-only\">: {$titel}</span></a></div>\n  </div>\n</article>";
}

/** Erste Meldung einer Liste (Rubrik, Ort, Thema). */
function ma21_feed_lead(WP_Post $p, string $slot = '', bool $fest = false): string {
    $url = esc_url(get_permalink($p)); $titel = ma21_e(get_the_title($p)); $b = ma21_bild($p);
    return '<article class="feed-lead"' . ma21_karte_attr($p, $slot, $fest) . '>'
        . ($b ? "<a href=\"{$url}\" tabindex=\"-1\" aria-hidden=\"true\"><div class=\"media\">" . ma21_img($b, MA21_SIZES['feed-lead'], true) . ma21_badge($b, false) . '</div></a>' : '')
        . '<div class="lead-copy">' . ma21_marke($p, 'kicker') . "<h2><a href=\"{$url}\">{$titel}</a></h2><p class=\"dek\">" . ma21_e(ma21_teaser($p)) . '</p>'
        . '<div class="meta">' . ma21_zeit($p, true) . '<span class="readtime">' . ma21_lesezeit($p) . ' Min.</span></div></div></article>';
}

function ma21_feed_row(WP_Post $p, string $slot = '', bool $fest = false): string {
    $url = esc_url(get_permalink($p)); $titel = ma21_e(get_the_title($p)); $b = ma21_bild($p);
    return '<article' . ma21_karte_attr($p, $slot, $fest) . ' class="feed-row">'
        . ($b ? "<a class=\"feed-img\" href=\"{$url}\" tabindex=\"-1\" aria-hidden=\"true\"><div class=\"media\">" . ma21_img($b, MA21_SIZES['feed-row'], false) . ma21_badge($b, false) . '</div></a>' : '')
        . '<div class="feed-copy">' . ma21_marke($p, 'kicker') . "<h3><a href=\"{$url}\">{$titel}</a></h3><p class=\"dek\">" . ma21_e(ma21_teaser($p)) . '</p>'
        . '<div class="meta">' . ma21_zeit($p, true) . '<span class="readtime">' . ma21_lesezeit($p) . ' Min.</span></div>'
        . "<div class=\"story-actions\"><a class=\"read-more\" href=\"{$url}\">Mehr lesen<span class=\"sr-only\">: {$titel}</span></a></div></div></article>";
}

/** Bildraster der Sportseite: gleich hohe Karten mit Bild, Marke, Titel, Zeit. $plaetze: je Karte [Platz, fest]. */
function ma21_bildraster(array $posts, array $plaetze = []): string {
    $h = '<div class="bildraster">';
    foreach (array_values($posts) as $i => $p) {
        $url = esc_url(get_permalink($p)); $titel = ma21_e(get_the_title($p)); $b = ma21_bild($p);
        [$slot, $fest] = $plaetze[$i] ?? ['', false];
        $h .= '<article class="bildraster-karte' . ($b ? '' : ' bildraster-karte--ohne-bild') . '"' . ma21_karte_attr($p, $slot, $fest) . '>'
            . ($b ? "<a class=\"bildraster-bild\" href=\"{$url}\" tabindex=\"-1\" aria-hidden=\"true\"><div class=\"media\">" . ma21_img($b, MA21_SIZES['raster'], false) . ma21_badge($b, false) . '</div></a>' : '')
            . '<div class="bildraster-text">' . ma21_marke($p, 'kicker') . "<h3><a href=\"{$url}\">{$titel}</a></h3><div class=\"meta\">" . ma21_zeit($p) . '</div></div></article>';
    }
    return $h . '</div>';
}

/** Schlüssel der Layout-Karte für die aktuelle Liste ('' = nicht anordenbar). */
function ma21_ressort_schluessel(): string {
    if (!function_exists('ma_layout_seite_gueltig')) return '';
    if (get_query_var('ma_alle')) return 'ressort-nachrichten';
    if (is_category()) { $o = get_queried_object(); if ($o instanceof WP_Term && ma_layout_seite_gueltig('ressort-' . $o->slug)) return 'ressort-' . $o->slug; }
    return '';
}

/** Beiträge der ersten Seite einer Ressortliste (für das Neu-Rendern im Bearbeitungsmodus). */
function ma21_ressort_beitraege(string $seite): array {
    $slug = substr($seite, 8);
    $q = ['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => $slug === 'nachrichten' ? 20 : max(1, (int) get_option('posts_per_page', 10)), 'orderby' => 'date', 'order' => 'DESC', 'suppress_filters' => false];
    if ($slug !== 'nachrichten') $q['category_name'] = $slug;
    return get_posts($q);
}

/** Belegung einer Ressortseite: Aufmacher, Bildraster (Sport), Reihen; feste Plätze aus der Layout-Karte. */
function ma21_ressort_belegung(string $seite, array $posts): array {
    $sport = $seite === 'ressort-sport';
    if (function_exists('ma_layout_aufloesen')) {
        return ma_layout_aufloesen($seite, ma_layout_feste_plaetze($seite), $posts, ['raster' => $sport, 'echtesBild' => fn($p) => ma21_echtes_bild(ma21_bild($p)), 'holen' => fn($id) => get_post($id) ?: null]);
    }
    $lead = array_shift($posts); $raster = []; $reihen = [];
    foreach ($posts as $p) { if ($sport && count($raster) < 6 && ma21_echtes_bild(ma21_bild($p))) $raster[] = $p; else $reihen[] = $p; }
    return ['lead' => $lead, 'raster' => $raster, 'reihen' => $reihen, 'plaetze' => []];
}

/** Liste einer Ressortseite als HTML (erste Seite: nach Layout-Karte; weitere Seiten: nur Reihen). */
function ma21_feed_html(string $seite, array $posts, bool $ersteSeite): string {
    if (!$posts) return '<p class="no-result">Hier gibt es noch keine Meldung. Sobald die erste Meldung vorliegt, steht sie an dieser Stelle.</p>';
    if (!$ersteSeite) return implode("\n", array_map(fn($p) => ma21_feed_row($p), $posts));
    $b = ma21_ressort_belegung($seite, $posts);
    $pl = $b['plaetze'];
    $slotVon = function (WP_Post $p, string $art) use ($pl): array {
        foreach ($pl as $slot => $e) if ($e['post'] === $p->ID && str_starts_with($slot, $art)) return [$slot, !empty($e['fest'])];
        return ['', false];
    };
    $h = '';
    if ($b['lead']) { [$slot, $fest] = $slotVon($b['lead'], 'lead'); $h .= ma21_feed_lead($b['lead'], $slot, $fest) . "\n"; }
    if ($b['raster']) $h .= ma21_bildraster($b['raster'], array_map(fn($p) => $slotVon($p, 'raster.'), $b['raster'])) . "\n";
    foreach ($b['reihen'] as $p) { [$slot, $fest] = $slotVon($p, 'reihe.'); $h .= ma21_feed_row($p, $slot, $fest) . "\n"; }
    return $h;
}

/* Bearbeitungsmodus und Board (Plugin): Blöcke neu rendern und Belegung lesen. */
add_filter('ma_layout_block_html', function ($html, string $seite, string $block): string {
    if ($seite === 'startseite') return ma21_block($block); // Belegung vorher über ma_layout_belegung auffrischen.
    return $block === 'feed' ? ma21_feed_html($seite, ma21_ressort_beitraege($seite), true) : (string) $html;
}, 10, 3);
add_filter('ma_layout_belegung', function ($b, string $seite): array {
    if ($seite === 'startseite') return ma21_startseite_belegung(true)['plaetze'] ?? [];
    return ma21_ressort_belegung($seite, ma21_ressort_beitraege($seite))['plaetze'] ?? [];
}, 10, 2);

/** Rechte Spalte der Listen: neueste Meldungen, bei Sport und Vereinen die Vereine der Gemeinde. */
function ma21_liste_seitenspalte(): string {
    $h = '';
    $neu = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 5]);
    if ($neu) {
        $h .= '<div class="sidebox"><h3>Neueste Meldungen aus allen Ressorts<a href="' . esc_url(home_url('/nachrichten/')) . '">alle</a></h3><ol class="ranked">';
        foreach ($neu as $p) $h .= '<li><a href="' . esc_url(get_permalink($p)) . '">' . ma21_e(get_the_title($p)) . '</a></li>';
        $h .= '</ol></div>';
    }
    if (is_tax('ma_location')) { $o = get_queried_object(); if ($o instanceof WP_Term) $h .= ma21_ort_seitenspalte($o); }
    $sport = is_category('sport'); $vereine = is_category('vereine');
    if (($sport || $vereine) && function_exists('ma_vereine_struktur')) {
        if ($sport) $h .= '<div class="sidebox sport-side-action"><h3>Sport direkt aus den Vereinen</h3><p>Vereine reichen Spielberichte, Ergebnisse, Termine und Mannschaftsfotos über ihren Redaktionszugang ein. Veröffentlicht wird nach Freigabe durch die Redaktion.</p><p><a class="read-more" href="' . esc_url(home_url('/meldung-senden/')) . '">Sportmeldung senden</a></p></div>';
        $liste = ''; $n = 0;
        foreach (ma_vereine_struktur() as $kurz => $e) {
            $v = $e['verein'];
            if ($sport !== (($v['kategorie'] ?? '') === 'Sport')) continue;
            $profil = ma_verein_profil($kurz);
            $sportarten = array_filter(array_merge([$v['sportart'] ?? ''], array_map(fn($a) => $a['sportart'] ?? '', $e['abteilungen'])));
            $klein = implode(' · ', array_filter([$sportarten ? implode(', ', array_unique($sportarten)) : ($v['kategorie'] ?? ''), MA21_ORTE[$v['ort'] ?? ''] ?? 'Gemeinde']));
            $name = ma21_e($v['name']);
            $link = $profil && $profil->post_status === 'publish' ? '<a href="' . esc_url(get_permalink($profil)) . '">' . $name . '</a>' : (!empty($v['website']) ? '<a href="' . esc_url($v['website']) . '" target="_blank" rel="noopener">' . $name . '</a>' : '<span>' . $name . '</span>');
            $liste .= '<li>' . $link . '<small>' . ma21_e($klein) . '</small></li>'; $n++;
        }
        if ($n) $h .= '<div class="sidebox sport-leiste"><h3>' . ($sport ? 'Sportvereine in der Gemeinde' : 'Vereine in Merzenich') . '</h3><ul class="sport-vereine">' . $liste . '</ul><p class="sport-leiste-quelle">Vereinsverzeichnis der Gemeinde · Abteilungen im Profil des Hauptvereins</p></div>';
    }
    return $h;
}

/* Ortsseiten (/ort/<slug>/, 04.10.2026): In der Liste stehen nur Meldungen. Vorher
   mischten sich Termine und Vereinsprofile chronologisch hinein, ohne Bild, und
   verdrängten die Meldungen; die statische Ortsseite zeigt Termine und Vereine
   des Orts in der Seitenleiste, so jetzt auch hier. */
add_action('pre_get_posts', function (WP_Query $q): void {
    if (is_admin() || !$q->is_main_query() || !$q->is_tax('ma_location')) return;
    $q->set('post_type', 'post');
});

/** Seitenleiste der Ortsseite: nächste Termine und Vereine des Orts (Aufbau wie die statische Ortsseite). */
function ma21_ort_seitenspalte(WP_Term $ort): string {
    $h = '';
    $termine = get_posts(['post_type' => 'ma_event', 'post_status' => 'publish', 'posts_per_page' => 5, 'meta_key' => 'ma_event_start', 'orderby' => 'meta_value', 'order' => 'ASC',
        'meta_query' => [['key' => 'ma_event_start', 'value' => (string) current_time('Y-m-d\TH:i'), 'compare' => '>=']],
        'tax_query' => [['taxonomy' => 'ma_location', 'field' => 'term_id', 'terms' => (int) $ort->term_id]]]);
    if ($termine) {
        $h .= '<div class="sidebox ort-termine"><h3>Nächste Termine in ' . ma21_e($ort->name) . '<a href="' . esc_url(home_url('/termine/')) . '">alle</a></h3><ul class="linklist">';
        foreach ($termine as $t) {
            $start = function_exists('ma_event_timestamp') ? (int) ma_event_timestamp($t->ID, 'start') : 0;
            $wann = $start ? wp_date('D, d.m., H:i', $start) . ' Uhr' : '';
            $platz = (string) get_post_meta($t->ID, 'ma_event_place', true);
            $h .= '<li><a href="' . esc_url(get_permalink($t)) . '">' . ma21_e(get_the_title($t)) . '</a><small>' . ma21_e(implode(' · ', array_filter([$wann, $platz]))) . '</small></li>';
        }
        $h .= '</ul></div>';
    }
    if (function_exists('ma_vereine_struktur')) {
        $liste = '';
        foreach (ma_vereine_struktur() as $kurz => $e) {
            $v = $e['verein'];
            if (($v['ort'] ?? '') !== $ort->slug) continue;
            $profil = function_exists('ma_verein_profil') ? ma_verein_profil($kurz) : null;
            $name = ma21_e((string) $v['name']);
            $link = $profil instanceof WP_Post && $profil->post_status === 'publish' ? '<a href="' . esc_url(get_permalink($profil)) . '">' . $name . '</a>' : (!empty($v['website']) ? '<a href="' . esc_url($v['website']) . '" target="_blank" rel="noopener">' . $name . '</a>' : $name);
            $klein = (string) (($v['sportart'] ?? '') ?: ($v['kategorie'] ?? ''));
            $liste .= '<li>' . $link . ($klein !== '' ? '<small>' . ma21_e($klein) . '</small>' : '') . '</li>';
        }
        if ($liste !== '') $h .= '<div class="sidebox ort-vereine"><h3>Vereine in ' . ma21_e($ort->name) . '<a href="' . esc_url(home_url('/vereine/')) . '">alle</a></h3><ul class="linklist">' . $liste . '</ul></div>';
    }
    return $h;
}

/** Kopf und Beschreibung je Liste, wie auf der statischen Seite. */
function ma21_liste_kopf(): array {
    $o = get_queried_object();
    $TEXTE = [
        'blaulicht' => ['Blaulicht', 'Feuerwehr, Polizei und Rettungsdienst', 'Einsätze der Freiwilligen Feuerwehr Merzenich, Polizeimeldungen für das Gemeindegebiet und Verkehrsmeldungen, sachlich zusammengefasst und mit Originalquelle.'],
        'rathaus' => ['Rathaus & Politik', 'Rathaus, Rat und Gemeinde', 'Beschlüsse, Bekanntmachungen und Projekte der Gemeinde Merzenich.'],
        'sport' => ['Sport', 'Sport in Merzenich', 'Spielberichte, Ergebnisse und Neues aus den Sportvereinen der Gemeinde.'],
        'leben' => ['Leben', 'Leben in Merzenich', 'Schule, Familie, Kirche, Senioren und Alltag in den fünf Orten.'],
        'wirtschaft' => ['Wirtschaft', 'Wirtschaft und Arbeit', 'Betriebe, Arbeit, Infrastruktur und Strukturwandel im Rheinischen Revier.'],
        'vereine' => ['Vereine', 'Aus den Vereinen', 'Meldungen, Feste und Termine der Vereine in Merzenich.'],
    ];
    if (is_category() && $o instanceof WP_Term && isset($TEXTE[$o->slug])) return $TEXTE[$o->slug];
    if (is_tax('ma_location') && $o instanceof WP_Term) return ['Ort', $o->name, $o->description ?: "Meldungen aus {$o->name}."];
    if (is_tag() && $o instanceof WP_Term) return ['Thema', $o->name, $o->description ?: "Alle Meldungen zum Thema {$o->name}."];
    if (is_search()) return ['Suche', 'Suche: ' . get_search_query(), ''];
    if ($o instanceof WP_Term) return [$o->name, $o->name, $o->description];
    return ['Nachrichten', 'Alle Meldungen', 'Alle Meldungen aus der Gemeinde Merzenich, die jüngste zuerst.'];
}

/* ------------------------------------------------------------ Adressen der statischen Seite */

/* /nachrichten/ = alle Meldungen (statisch: Liste aller Ressorts). /api/weather.json
   liefert Open-Meteo wie der Dienst der statischen Seite (cockpit.js, app.js). */
add_action('init', function (): void {
    add_rewrite_rule('^nachrichten/?$', 'index.php?ma_alle=1', 'top');
    add_rewrite_rule('^nachrichten/page/([0-9]+)/?$', 'index.php?ma_alle=1&paged=$matches[1]', 'top');
    add_rewrite_rule('^api/weather\.json/?$', 'index.php?ma_api=weather', 'top');
    // Die beiden Abrufe aus assets/v20.js (Sportmodul, redaktionelle Übersteuerung) liefen auf WordPress ins Leere (404).
    add_rewrite_rule('^api/(sport-current|editorial-current)\.json/?$', 'index.php?ma_api=$matches[1]', 'top');
    // /unternehmen/ wie auf der statischen Seite (Aufbau wie Oberberg Aktuell, 30.09.2026).
    add_rewrite_rule('^unternehmen/?$', 'index.php?ma_unternehmen=1', 'top');
});
add_filter('query_vars', function (array $v): array { $v[] = 'ma_alle'; $v[] = 'ma_api'; $v[] = 'ma_unternehmen'; return $v; });
/**
 * Beiträge der Unternehmen für /unternehmen/ (Vorgabe Betreiber 04.10.2026):
 * gesponserte Beiträge (ma_gesponsert) und Beiträge der Unternehmens-Zugänge.
 * Wirtschaftsnachrichten der Redaktion gehören nicht dazu, die stehen unter /wirtschaft/.
 */
function ma21_unternehmen_ids(): array {
    static $ids = null;
    if ($ids !== null) return $ids;
    $a = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 200, 'fields' => 'ids', 'meta_key' => 'ma_gesponsert', 'meta_value' => '1']);
    $nutzer = get_users(['role' => 'ma_wirtschaft_partner', 'fields' => 'ID']);
    $b = $nutzer ? get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 200, 'fields' => 'ids', 'author__in' => array_map('intval', (array) $nutzer)]) : [];
    return $ids = array_values(array_unique(array_map('intval', array_merge((array) $a, (array) $b))));
}
add_action('pre_get_posts', function (WP_Query $q): void {
    if (is_admin() || !$q->is_main_query() || !$q->get('ma_unternehmen')) return;
    $ids = ma21_unternehmen_ids();
    $q->set('post_type', 'post'); $q->set('post__in', $ids ?: [0]); $q->set('posts_per_page', 60); $q->set('orderby', 'date'); $q->set('order', 'DESC');
    $q->is_home = false; $q->is_archive = true; $q->is_404 = false;
});
add_filter('template_include', fn($t) => get_query_var('ma_unternehmen') ? (locate_template('unternehmen.php') ?: $t) : $t);
// Auch ohne Meldung ist /unternehmen/ eine Seite (Kanäle, Angebote), kein 404.
add_filter('pre_handle_404', fn($stop, $q) => ($q->is_main_query() && $q->get('ma_unternehmen')) ? true : $stop, 10, 2);

/** Karte der Unternehmensseite: Bild oben, Kicker, Titel, Zeile, Anriss, Weiterlesen. */
function ma21_u_karte(WP_Post $p, int $i): string {
    $url = esc_url(get_permalink($p)); $titel = ma21_e(get_the_title($p)); $b = ma21_bild($p);
    $ort = ma21_ort($p);
    return '<article class="u-karte' . ($b ? '' : ' u-karte--ohne-bild') . '"' . ($i >= 8 ? ' data-nachladen hidden' : '') . '>'
        . ($b ? "<a class=\"u-karte__bild\" href=\"{$url}\" tabindex=\"-1\" aria-hidden=\"true\">" . ma21_img($b, MA21_SIZES['unternehmen'], $i < 2) . ma21_badge($b, false) . '</a>' : '')
        . '<p class="u-karte__kicker">' . ma21_e(MA21_ORTE[$ort] ?? 'Wirtschaft') . (function_exists('ma_ist_gesponsert') && ma_ist_gesponsert($p) ? '<span class="gesponsert">Anzeige · Gesponsert</span>' : '') . '</p>'
        . "<h2><a href=\"{$url}\">{$titel}</a></h2>"
        . '<p class="u-karte__meta">Redaktion · <time datetime="' . esc_attr(get_the_date('c', $p)) . '">' . esc_html(get_the_date('d.m.Y, H:i', $p)) . ' Uhr</time></p>'
        . '<p class="u-karte__teaser">' . ma21_e(ma21_teaser($p)) . '</p>'
        . "<a class=\"u-karte__weiter\" href=\"{$url}\">Weiterlesen<span class=\"sr-only\">: {$titel}</span></a></article>";
}
// Keine Schrägstrich-Umleitung für /api/weather.json (die Skripte rufen genau diese Adresse).
add_filter('redirect_canonical', fn($ziel) => get_query_var('ma_api') ? false : $ziel);
add_action('pre_get_posts', function (WP_Query $q): void {
    if (!is_admin() && $q->is_main_query() && $q->get('ma_alle')) {
        $q->set('post_type', 'post'); $q->set('posts_per_page', 20);
        $q->is_home = false; $q->is_archive = true; $q->is_404 = false;
    }
});
add_action('template_redirect', function (): void {
    if (get_query_var('ma_api') !== 'weather') return;
    $daten = get_transient('ma21_wetter');
    if (!$daten) {
        $url = 'https://api.open-meteo.com/v1/forecast?latitude=50.8317&longitude=6.5361&current=temperature_2m,weather_code&daily=temperature_2m_max,temperature_2m_min,weather_code&timezone=Europe%2FBerlin&forecast_days=3';
        $r = wp_remote_get($url, ['timeout' => 6]);
        if (!is_wp_error($r) && wp_remote_retrieve_response_code($r) === 200) {
            $daten = wp_remote_retrieve_body($r);
            set_transient('ma21_wetter', $daten, 10 * MINUTE_IN_SECONDS);
        }
    }
    nocache_headers();
    if (!$daten) { status_header(503); wp_send_json(['fehler' => 'Wetter nicht verfügbar']); }
    header('Content-Type: application/json; charset=utf-8');
    echo $daten; exit;
});

/**
 * /api/sport-current.json: der Spielstand des SC Merzenich, den die Kette
 * (deploy/sport.mjs) nach chatgpt-site/api/ schreibt; aus dem Repository geholt,
 * 15 Minuten zwischengespeichert, mit Notkopie, falls GitHub gerade nicht antwortet
 * (ma21_repo_datei). /api/editorial-current.json: die
 * redaktionelle Übersteuerung der statischen Startseite; auf WordPress entscheidet
 * das Board „Startseite & Ressorts“, darum eine leere Antwort statt eines 404.
 */
add_action('template_redirect', function (): void {
    $api = (string) get_query_var('ma_api');
    if ($api === 'editorial-current') {
        header('Content-Type: application/json; charset=utf-8');
        ma21_cache(600);
        echo wp_json_encode(['generated' => (string) wp_date('c'), 'hinweis' => 'Auf WordPress entscheidet die Redaktion im Board „Startseite & Ressorts“; diese Datei setzt hier nichts.', 'hero' => null, 'secondary' => []], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($api !== 'sport-current') return;
    $daten = ma21_repo_datei('api/sport-current.json', 15 * MINUTE_IN_SECONDS);
    if (!is_array(json_decode($daten, true))) $daten = (string) get_option('ma21_sport_current_kopie', '');
    if ($daten === '') { status_header(503); nocache_headers(); wp_send_json(['fehler' => 'Spielstand nicht verfügbar']); }
    header('Content-Type: application/json; charset=utf-8');
    ma21_cache(600);
    echo $daten; exit;
});

/* Nach einem Theme-Update Regeln und .htaccess einmal neu schreiben (Assets, Adressen). */
add_action('init', function (): void {
    $v = wp_get_theme(get_template())->get('Version');
    if (get_option('ma21_regeln') === $v) return;
    flush_rewrite_rules(true);
    update_option('ma21_regeln', $v, false);
}, 99);
