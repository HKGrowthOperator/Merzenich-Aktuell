<?php
/**
 * Statistik im Backend (30.09.2026): Merzenich Aktuell → Statistik.
 *
 * Aufrufe zählt die Seite selbst, ohne Cookies, ohne IP-Adresse, ohne
 * Speicherung im Browser: Jede Seite meldet beim Laden per sendBeacon
 * „Beitrag X einmal aufgerufen“ an /wp-json/ma/v1/aufruf. Gespeichert wird nur
 * die Summe je Tag und Beitrag (Tabelle {prefix}ma_aufrufe). Bots (nach
 * User-Agent) und angemeldete Redakteure zählen nicht. Kein Dritter ist beteiligt.
 *
 * Die Seite zeigt: Aufrufe heute/7/30 Tage, Verlauf 30 Tage, meistgelesene
 * Meldungen, Aufrufe je Rubrik, veröffentlichte Meldungen je Rubrik und
 * Ortsteil, Freigaben, Eingang, Kommentare und Partner-Einreichungen.
 * CSV-Export der Tageswerte. Nur Redaktion und Administration.
 */
if (!defined('ABSPATH')) { exit; }

const MA_STATISTIK_DB_VERSION = '1';

function ma_statistik_tabelle(): string { global $wpdb; return $wpdb->prefix . 'ma_aufrufe'; }

add_action('plugins_loaded', function (): void {
    if (get_option('ma_statistik_db') === MA_STATISTIK_DB_VERSION) return;
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta('CREATE TABLE ' . ma_statistik_tabelle() . " (
  tag date NOT NULL,
  objekt bigint(20) unsigned NOT NULL DEFAULT 0,
  art varchar(20) NOT NULL DEFAULT 'post',
  aufrufe int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (tag,objekt,art),
  KEY objekt (objekt)
) " . $wpdb->get_charset_collate() . ';');
    update_option('ma_statistik_db', MA_STATISTIK_DB_VERSION, false);
});

/** Ist der Aufruf zu zählen? Bots und angemeldete Redakteure nicht. */
function ma_statistik_zaehlt(string $ua): bool {
    if ($ua === '' || preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|embedly|curl|wget|python|headless|lighthouse|pingdom|uptime|monitor|feed|scan/i', $ua)) return false;
    return !(function_exists('is_user_logged_in') && is_user_logged_in() && current_user_can('edit_posts'));
}

/**
 * Bremse je Besucher (03.10.2026): höchstens $max Zählungen je festem Fenster von
 * $sek Sekunden. Kennung wie in live.php (Tages-Salz, Hash aus Adresse und
 * Browser), gespeichert wird nur der Zähler, keine IP-Adresse. Wer die Endpunkte
 * in Schleifen anspricht, bläht Statistik und Impressionen nicht mehr auf.
 */
function ma_zaehl_bremse(string $art, int $max, int $sek, string $ua): bool {
    $kennung = function_exists('ma_live_kennung') ? ma_live_kennung($ua) : substr(hash('sha256', $ua), 0, 16);
    $k = 'ma_zb_' . sanitize_key($art) . '_' . $kennung;
    $d = get_transient($k);
    if (!is_array($d) || (int) ($d['bis'] ?? 0) <= time()) $d = ['n' => 0, 'bis' => time() + $sek];
    if ((int) $d['n'] >= $max) return false;
    $d['n'] = (int) $d['n'] + 1;
    set_transient($k, $d, max(1, (int) $d['bis'] - time()));
    return true;
}

function ma_statistik_zaehlen(int $objekt, string $art): void {
    global $wpdb;
    $wpdb->query($wpdb->prepare('INSERT INTO ' . ma_statistik_tabelle() . ' (tag, objekt, art, aufrufe) VALUES (%s, %d, %s, 1) ON DUPLICATE KEY UPDATE aufrufe = aufrufe + 1', current_time('Y-m-d'), $objekt, $art));
}

add_action('rest_api_init', function (): void {
    register_rest_route('ma/v1', '/aufruf', [
        'methods' => 'POST', 'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $r) {
            if (!ma_statistik_zaehlt((string) $r->get_header('user_agent'))) return new WP_REST_Response(null, 204);
            if (!ma_zaehl_bremse('aufruf', 40, 5 * MINUTE_IN_SECONDS, (string) $r->get_header('user_agent'))) return new WP_REST_Response(null, 204);
            $d = json_decode((string) $r->get_body(), true) ?: [];
            $art = in_array($d['art'] ?? '', ['post', 'start', 'seite', 'liste'], true) ? $d['art'] : 'seite';
            $id = (int) ($d['id'] ?? 0);
            // Nur veröffentlichte Inhalte; Startseite und Listen zählen als Objekt 0.
            if ($art === 'post' && (!$id || get_post_status($id) !== 'publish')) return new WP_REST_Response(null, 204);
            if ($art !== 'post' && $art !== 'seite') $id = 0;
            ma_statistik_zaehlen($id, $art);
            if (function_exists('ma_live_erfassen')) ma_live_erfassen($r, $d, 1);
            return new WP_REST_Response(null, 204);
        },
    ]);
});

add_action('wp_footer', function (): void {
    if (is_admin() || is_preview() || is_404()) return;
    // Angemeldete Redakteure zaehlen nicht (die REST-Anfrage kennt ohne Nonce keinen Benutzer).
    if (is_user_logged_in() && current_user_can('edit_posts')) return;
    $art = is_front_page() ? 'start' : (is_singular('post') ? 'post' : (is_singular() ? 'seite' : 'liste'));
    $id = is_singular() ? (int) get_queried_object_id() : 0;
    $url = esc_url_raw(rest_url('ma/v1/aufruf'));
    $puls = esc_url_raw(rest_url('ma/v1/puls'));
    // Ein Aufruf je Seitenaufbau, danach ein Puls alle 30 Sekunden, solange der
    // Tab sichtbar ist (Live-Anzeige im Backend, includes/live.php). Ohne Cookie,
    // ohne Speicherung im Browser; vom Verweis wird nur der Hostname gesendet.
    printf('<script>(function(){try{if(!navigator.sendBeacon)return;var p=location.pathname,s=function(u,o){navigator.sendBeacon(u,new Blob([JSON.stringify(o)],{type:"application/json"}));},r="";try{r=document.referrer?new URL(document.referrer).hostname:"";}catch(e){}'
        . 's(%s,{id:%d,art:%s,pfad:p,titel:document.title,ref:r});setInterval(function(){if(document.visibilityState==="visible")s(%s,{pfad:p});},30000);}catch(e){}})();</script>',
        wp_json_encode($url), $id, wp_json_encode($art), wp_json_encode($puls));
}, 99);

/* ---------------------------------------------------------- Auswertung */

function ma_statistik_summe(int $tage): int {
    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare('SELECT COALESCE(SUM(aufrufe),0) FROM ' . ma_statistik_tabelle() . ' WHERE tag > %s', gmdate('Y-m-d', strtotime(current_time('Y-m-d') . ' -' . $tage . ' days'))));
}

/** Tageswerte der letzten $tage Tage, lückenlos (0 an Tagen ohne Aufruf). */
function ma_statistik_verlauf(int $tage): array {
    global $wpdb;
    $ab = gmdate('Y-m-d', strtotime(current_time('Y-m-d') . ' -' . ($tage - 1) . ' days'));
    $zeilen = $wpdb->get_results($wpdb->prepare('SELECT tag, SUM(aufrufe) n FROM ' . ma_statistik_tabelle() . ' WHERE tag >= %s GROUP BY tag', $ab), OBJECT_K);
    $raus = [];
    for ($i = 0; $i < $tage; $i++) { $t = gmdate('Y-m-d', strtotime($ab . ' +' . $i . ' days')); $raus[$t] = (int) ($zeilen[$t]->n ?? 0); }
    return $raus;
}

function ma_statistik_top(int $tage, int $n): array {
    global $wpdb;
    return $wpdb->get_results($wpdb->prepare("SELECT objekt, SUM(aufrufe) n FROM " . ma_statistik_tabelle() . " WHERE art = 'post' AND tag > %s GROUP BY objekt ORDER BY n DESC LIMIT %d", gmdate('Y-m-d', strtotime(current_time('Y-m-d') . ' -' . $tage . ' days')), $n));
}

function ma_statistik_je_rubrik(int $tage): array {
    $je = [];
    foreach (ma_statistik_top($tage, 500) as $z) {
        foreach (get_the_category((int) $z->objekt) as $k) $je[$k->name] = ($je[$k->name] ?? 0) + (int) $z->n;
    }
    arsort($je);
    return $je;
}

/** Balkendiagramm als SVG, ohne Bibliothek. */
function ma_statistik_svg(array $werte): string {
    $max = max(1, max($werte ?: [0])); $n = count($werte); $b = 720; $h = 160; $w = $b / max(1, $n);
    $svg = '<svg viewBox="0 0 ' . $b . ' ' . ($h + 22) . '" role="img" aria-label="Aufrufe je Tag, letzte ' . $n . ' Tage" style="width:100%;max-width:760px;height:auto">';
    $i = 0;
    foreach ($werte as $tag => $v) {
        $hb = round($v / $max * $h); $x = round($i * $w + 1, 1);
        $svg .= '<rect x="' . $x . '" y="' . ($h - $hb) . '" width="' . round($w - 2, 1) . '" height="' . $hb . '" fill="#8c1c22"><title>' . esc_html(wp_date('d.m.', strtotime($tag)) . ': ' . $v . ' Aufrufe') . '</title></rect>';
        if ($i % 7 === 0 && $i <= $n - 4) $svg .= '<text x="' . $x . '" y="' . ($h + 16) . '" font-size="11" fill="#50575e">' . esc_html(wp_date('d.m.', strtotime($tag))) . '</text>';
        $i++;
    }
    return $svg . '</svg>';
}

add_action('admin_menu', function (): void {
    add_submenu_page('merzenich-aktuell', 'Statistik', 'Statistik', 'edit_others_posts', 'ma-statistik', 'ma_statistik_seite', 2);
}, 20);

add_action('admin_post_ma_statistik_csv', function (): void {
    if (!current_user_can('edit_others_posts')) wp_die('Keine Berechtigung.', 403);
    check_admin_referer('ma_statistik_csv');
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="merzenich-aktuell-aufrufe-' . current_time('Y-m-d') . '.csv"');
    $von = sanitize_text_field(wp_unslash($_GET['von'] ?? '')); $bis = sanitize_text_field(wp_unslash($_GET['bis'] ?? ''));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $von) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $bis) || $von > $bis) { $bis = current_time('Y-m-d'); $von = gmdate('Y-m-d', strtotime($bis . ' -89 days')); }
    $besucher = ma_statistik_besucher_reihe($von, $bis); $artikel = ma_statistik_reihe($von, $bis, 'post');
    echo "Tag;Seitenaufrufe;Artikelaufrufe;Besucher\n";
    foreach (ma_statistik_reihe($von, $bis) as $t => $v) echo $t . ';' . $v . ';' . $artikel[$t] . ';' . $besucher[$t] . "\n";
    exit;
});

/* ---------------------------------------------------------- Zeitraum */

/** Zeitraum aus der Anfrage: heute, 7, 30 (Standard) oder eigen (von/bis). [von, bis, Bezeichnung] */
function ma_statistik_zeitraum(): array {
    $heute = current_time('Y-m-d');
    $z = sanitize_key($_GET['zeitraum'] ?? '30');
    if ($z === 'heute') return [$heute, $heute, 'heute', 'heute'];
    if ($z === '7') return [gmdate('Y-m-d', strtotime($heute . ' -6 days')), $heute, 'letzte 7 Tage', '7'];
    if ($z === 'eigen') {
        $von = sanitize_text_field(wp_unslash($_GET['von'] ?? '')); $bis = sanitize_text_field(wp_unslash($_GET['bis'] ?? ''));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $von) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $bis)) {
            if ($von > $bis) [$von, $bis] = [$bis, $von];
            if ($bis > $heute) $bis = $heute;
            if (strtotime($bis) - strtotime($von) > 400 * DAY_IN_SECONDS) $von = gmdate('Y-m-d', strtotime($bis . ' -400 days'));
            return [$von, $bis, wp_date('d.m.Y', strtotime($von)) . ' bis ' . wp_date('d.m.Y', strtotime($bis)), 'eigen'];
        }
    }
    return [gmdate('Y-m-d', strtotime($heute . ' -29 days')), $heute, 'letzte 30 Tage', '30'];
}

/** Tage im Zeitraum, lückenlos. */
function ma_statistik_tage(string $von, string $bis): array {
    $t = []; for ($d = $von; $d <= $bis; $d = gmdate('Y-m-d', strtotime($d . ' +1 day'))) $t[$d] = 0;
    return $t;
}

/** Aufrufe je Tag im Zeitraum, optional nur eine Art (post, start, liste, seite). */
function ma_statistik_reihe(string $von, string $bis, string $art = ''): array {
    global $wpdb;
    $sql = 'SELECT tag, SUM(aufrufe) n FROM ' . ma_statistik_tabelle() . ' WHERE tag BETWEEN %s AND %s' . ($art !== '' ? ' AND art = %s' : '') . ' GROUP BY tag';
    $z = $wpdb->get_results($art !== '' ? $wpdb->prepare($sql, $von, $bis, $art) : $wpdb->prepare($sql, $von, $bis), OBJECT_K);
    $r = ma_statistik_tage($von, $bis);
    foreach ($r as $t => $v) $r[$t] = (int) ($z[$t]->n ?? 0);
    return $r;
}

/** Eindeutige Besucher je Tag (live.php, Tabelle ma_besuch). */
function ma_statistik_besucher_reihe(string $von, string $bis): array {
    global $wpdb;
    $r = ma_statistik_tage($von, $bis);
    if (!function_exists('ma_live_tabelle')) return $r;
    foreach ($wpdb->get_results($wpdb->prepare('SELECT tag, COUNT(*) n FROM ' . ma_live_tabelle() . ' WHERE tag BETWEEN %s AND %s AND seiten > 0 GROUP BY tag', $von, $bis), OBJECT_K) as $t => $z) if (isset($r[$t])) $r[$t] = (int) $z->n;
    return $r;
}

function ma_statistik_top_zeitraum(string $von, string $bis, int $n): array {
    global $wpdb;
    return $wpdb->get_results($wpdb->prepare("SELECT objekt, SUM(aufrufe) n FROM " . ma_statistik_tabelle() . " WHERE art = 'post' AND tag BETWEEN %s AND %s GROUP BY objekt ORDER BY n DESC LIMIT %d", $von, $bis, $n));
}

/** Erster Tag mit Daten in einer Tabelle (für „erfasst seit“). */
function ma_statistik_seit(string $tabelle): string {
    global $wpdb;
    return (string) $wpdb->get_var('SELECT MIN(tag) FROM ' . $tabelle);
}

/** Waagrechte Balken (Verteilung) als HTML, ohne Bibliothek. */
function ma_statistik_balken(array $werte, string $einheit = ''): string {
    if (!$werte) return '<p class="description">Im Zeitraum keine Daten.</p>';
    $max = max(1, max($werte)); $h = '<div class="ma-stat__balken">';
    foreach ($werte as $name => $v) $h .= '<div class="ma-stat__balken-zeile"><span>' . esc_html((string) $name) . '</span><span class="ma-stat__balken-spur"><span style="width:' . round($v / $max * 100, 1) . '%"></span></span><b>' . esc_html(number_format_i18n($v)) . $einheit . '</b></div>';
    return $h . '</div>';
}

/** Säulen über die Tage des Zeitraums (beliebige Länge). */
function ma_statistik_saeulen(array $werte, string $was): string {
    $max = max(1, max($werte ?: [0])); $n = max(1, count($werte)); $b = 720; $h = 150; $w = $b / $n;
    $svg = '<svg viewBox="0 0 ' . $b . ' ' . ($h + 22) . '" role="img" aria-label="' . esc_attr($was) . ' je Tag" style="width:100%;height:auto">';
    $schritt = max(1, (int) ceil($n / 8)); $i = 0;
    foreach ($werte as $tag => $v) {
        $hb = round($v / $max * $h); $x = round($i * $w + 1, 1);
        $svg .= '<rect x="' . $x . '" y="' . ($h - $hb) . '" width="' . max(1, round($w - 2, 1)) . '" height="' . $hb . '" rx="1.5" fill="#8c1c22"><title>' . esc_html(wp_date('d.m.Y', strtotime($tag)) . ': ' . number_format_i18n($v) . ' ' . $was) . '</title></rect>';
        if ($i % $schritt === 0 && $i <= $n - max(1, (int) floor($schritt / 2))) $svg .= '<text x="' . $x . '" y="' . ($h + 16) . '" font-size="11" fill="#50575e">' . esc_html(wp_date('d.m.', strtotime($tag))) . '</text>';
        $i++;
    }
    return $svg . '</svg>';
}

function ma_statistik_seite(): void {
    if (!current_user_can('edit_others_posts') || (function_exists('ma_current_partner_policy') && ma_current_partner_policy())) wp_die('Keine Berechtigung.');
    global $wpdb;
    [$von, $bis, $bezeichnung, $wahl] = ma_statistik_zeitraum();
    $kachel = fn($zahl, $text, $link = '', $hinweis = '') => '<div class="ma-stat__kachel"><strong>' . ($zahl === null ? '–' : esc_html(number_format_i18n((int) $zahl))) . '</strong><span>' . ($link ? '<a href="' . esc_url($link) . '">' . esc_html($text) . '</a>' : esc_html($text)) . '</span>' . ($hinweis !== '' ? '<small>' . esc_html($hinweis) . '</small>' : '') . '</div>';
    $zeit = ['after' => $von . ' 00:00:00', 'before' => $bis . ' 23:59:59', 'inclusive' => true];
    $zaehle = fn(array $q) => count(get_posts($q + ['posts_per_page' => -1, 'fields' => 'ids']));

    echo '<div class="wrap ma-stat"><h1>Statistik</h1>';
    echo '<style>.ma-stat__filter{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:14px 0 6px}.ma-stat__filter .button.ist-an{background:#8c1c22;border-color:#8c1c22;color:#fff}.ma-stat__filter form{display:flex;gap:6px;align-items:center;flex-wrap:wrap}.ma-stat h2.ma-stat__bereich{margin:30px 0 4px;font-size:18px}.ma-stat__kacheln{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:12px;margin:12px 0 18px}.ma-stat__kachel{background:#fff;border:1px solid #dcdcde;padding:14px 16px}.ma-stat__kachel strong{display:block;font-size:28px;line-height:1.1;font-variant-numeric:tabular-nums}.ma-stat__kachel span{color:#50575e}.ma-stat__kachel small{display:block;margin-top:4px;color:#646970}.ma-stat__raster{display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:20px}.ma-stat__box{background:#fff;border:1px solid #dcdcde;padding:14px 16px}.ma-stat__box h2,.ma-stat__box h3{margin-top:0;font-size:15px}.ma-stat__box table{width:100%;border-collapse:collapse}.ma-stat__box td,.ma-stat__box th{padding:5px 8px 5px 0;border-top:1px solid #f0f0f1;text-align:left}.ma-stat__box td.z,.ma-stat__box th.z{text-align:right;font-variant-numeric:tabular-nums}.ma-stat__balken-zeile{display:grid;grid-template-columns:minmax(90px,38%) 1fr auto;gap:10px;align-items:center;padding:4px 0}.ma-stat__balken-spur{height:10px;background:#f0f0f1}.ma-stat__balken-spur span{display:block;height:100%;background:#8c1c22}.ma-stat__balken-zeile b{font-variant-numeric:tabular-nums}</style>';
    if (function_exists('ma_live_html')) { ma_live_assets(); echo '<div class="ma-stat__box ma-stat__live">' . ma_live_html(true) . '</div>'; }

    // Filter
    $basis = admin_url('admin.php?page=ma-statistik');
    echo '<div class="ma-stat__filter"><strong>Zeitraum:</strong>';
    foreach (['heute' => 'Heute', '7' => 'Letzte 7 Tage', '30' => 'Letzte 30 Tage'] as $k => $l) printf('<a class="button%s" href="%s">%s</a>', $wahl === $k ? ' ist-an' : '', esc_url(add_query_arg('zeitraum', $k, $basis)), esc_html($l));
    printf('<form method="get" action="%s"><input type="hidden" name="page" value="ma-statistik"><input type="hidden" name="zeitraum" value="eigen"><label>von <input type="date" name="von" value="%s" max="%s"></label><label>bis <input type="date" name="bis" value="%s" max="%s"></label><button class="button%s">Eigener Zeitraum</button></form></div>',
        esc_url(admin_url('admin.php')), esc_attr($von), esc_attr(current_time('Y-m-d')), esc_attr($bis), esc_attr(current_time('Y-m-d')), $wahl === 'eigen' ? ' ist-an' : '');
    echo '<p class="description">Alle Zahlen unten gelten für <strong>' . esc_html($bezeichnung) . '</strong> (' . esc_html(wp_date('d.m.Y', strtotime($von))) . ' bis ' . esc_html(wp_date('d.m.Y', strtotime($bis))) . '). Seitenaufruf = jeder Aufruf einer Seite; Artikelaufruf = Aufruf einer Meldung; Besucher = eindeutige Besucher je Tag, über die Tage summiert; Impression = Anzeige war mindestens zur Hälfte sichtbar.</p>';

    // ---------- Website
    $aufrufe = ma_statistik_reihe($von, $bis); $artikel = ma_statistik_reihe($von, $bis, 'post'); $besucher = ma_statistik_besucher_reihe($von, $bis);
    $seitA = ma_statistik_seit(ma_statistik_tabelle()); $seitB = function_exists('ma_live_tabelle') ? ma_statistik_seit(ma_live_tabelle()) : '';
    $hinweisA = $seitA === '' ? 'noch nicht erfasst' : ($seitA > $von ? 'erfasst seit ' . wp_date('d.m.Y', strtotime($seitA)) : '');
    $hinweisB = $seitB === '' ? 'noch nicht erfasst' : ($seitB > $von ? 'erfasst seit ' . wp_date('d.m.Y', strtotime($seitB)) : '');
    echo '<h2 class="ma-stat__bereich">Website</h2><div class="ma-stat__kacheln">'
        . $kachel($seitA === '' ? null : array_sum($aufrufe), 'Seitenaufrufe', '', $hinweisA)
        . $kachel($seitA === '' ? null : array_sum($artikel), 'Artikelaufrufe', '', $hinweisA)
        . $kachel($seitB === '' ? null : array_sum($besucher), 'Besucher (Summe der Tage)', '', $hinweisB)
        . $kachel(count(array_filter($aufrufe)) ? (int) round(array_sum($aufrufe) / max(1, count(array_filter($aufrufe)))) : null, 'Aufrufe je Tag mit Daten')
        . '</div>';
    echo '<div class="ma-stat__raster"><div class="ma-stat__box"><h3>Seitenaufrufe je Tag</h3>' . ma_statistik_saeulen($aufrufe, 'Seitenaufrufe')
        . '<p><a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=ma_statistik_csv&von=' . $von . '&bis=' . $bis), 'ma_statistik_csv')) . '">Tageswerte als CSV</a></p></div>';
    echo '<div class="ma-stat__box"><h3>Besucher je Tag</h3>' . ma_statistik_saeulen($besucher, 'Besucher') . '</div></div>';
    $top = ma_statistik_top_zeitraum($von, $bis, 12);
    $jeRubrik = [];
    foreach (ma_statistik_top_zeitraum($von, $bis, 1000) as $z) foreach (get_the_category((int) $z->objekt) as $k) $jeRubrik[$k->name] = ($jeRubrik[$k->name] ?? 0) + (int) $z->n;
    arsort($jeRubrik);
    echo '<div class="ma-stat__raster" style="margin-top:20px"><div class="ma-stat__box"><h3>Meistgelesene Beiträge</h3><table>';
    if (!$top) echo '<tr><td>Im Zeitraum keine Artikelaufrufe erfasst.</td></tr>';
    foreach ($top as $z) printf('<tr><td><a href="%s">%s</a></td><td class="z">%s</td></tr>', esc_url(get_permalink((int) $z->objekt)), esc_html(get_the_title((int) $z->objekt)), esc_html(number_format_i18n((int) $z->n)));
    echo '</table></div><div class="ma-stat__box"><h3>Meistgelesene Ressorts</h3>' . ma_statistik_balken($jeRubrik) . '</div></div>';

    // ---------- Redaktion
    $eingereicht = 0; $abgelehnt = 0; $veroeff = 0;
    foreach (get_posts(['post_type' => ['post', 'ma_event', 'ma_club'], 'post_status' => ['draft', 'pending', 'ma_in_pruefung', 'ma_aenderung', 'future', 'publish', 'ma_archiv', 'ma_abgelehnt', 'trash'], 'posts_per_page' => 500, 'date_query' => [['column' => 'post_modified', 'after' => $von . ' 00:00:00']], 'meta_key' => '_ma_verlauf']) as $p) {
        foreach ((array) get_post_meta($p->ID, '_ma_verlauf', true) as $e) {
            $tag = wp_date('Y-m-d', (int) ($e['t'] ?? 0));
            if ($tag < $von || $tag > $bis) continue;
            $a = (string) ($e['a'] ?? '');
            if (str_contains($a, 'eingereicht')) $eingereicht++;
            if ($a === 'Abgelehnt') $abgelehnt++;
        }
    }
    $veroeffReihe = ma_statistik_tage($von, $bis); $jeKat = [];
    foreach (get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'date_query' => [$zeit]]) as $p) {
        $veroeff++; $t = get_post_time('Y-m-d', false, $p); if (isset($veroeffReihe[$t])) $veroeffReihe[$t]++;
        foreach (get_the_category($p->ID) as $k) $jeKat[$k->name] = ($jeKat[$k->name] ?? 0) + 1;
    }
    arsort($jeKat);
    $wartend = $zaehle(['post_type' => ['post', 'ma_event', 'ma_club', 'ma_business', 'ma_tip'], 'post_status' => ['pending', 'ma_in_pruefung']]);
    $aenderung = $zaehle(['post_type' => ['post', 'ma_event', 'ma_club'], 'post_status' => 'ma_aenderung']);
    $abgelehntGesamt = $zaehle(['post_type' => ['post', 'ma_event', 'ma_club', 'ma_business', 'ma_tip', 'ma_property', 'ma_ad'], 'post_status' => 'ma_abgelehnt']);
    $eingang_neu = post_type_exists('ma_eingang') ? (int) (wp_count_posts('ma_eingang')->ma_neu ?? 0) : 0;
    echo '<h2 class="ma-stat__bereich">Redaktion</h2><div class="ma-stat__kacheln">'
        . $kachel($eingereicht, 'Einreichungen im Zeitraum', admin_url('admin.php?page=ma-freigaben'), 'laut Verlauf, erfasst seit Plugin 1.12')
        . $kachel($wartend, 'warten auf Freigabe (jetzt)', admin_url('admin.php?page=ma-freigaben'))
        . $kachel($aenderung, 'Änderungen angefordert (jetzt)')
        . $kachel($veroeff, 'Meldungen veröffentlicht', admin_url('edit.php?post_status=publish&post_type=post'))
        . $kachel($abgelehnt, 'abgelehnt im Zeitraum', '', $abgelehntGesamt . ' abgelehnt insgesamt')
        . $kachel($eingang_neu, 'neue Formular-Einsendungen', admin_url('edit.php?post_type=ma_eingang'))
        . $kachel((int) (wp_count_comments()->moderated ?? 0), 'Kommentare warten', admin_url('edit-comments.php?comment_status=moderated'))
        . '</div><div class="ma-stat__raster"><div class="ma-stat__box"><h3>Veröffentlichte Meldungen je Tag</h3>' . ma_statistik_saeulen($veroeffReihe, 'Meldungen') . '</div>'
        . '<div class="ma-stat__box"><h3>Veröffentlichte Meldungen nach Kategorie</h3>' . ma_statistik_balken($jeKat) . '</div></div>';

    // ---------- Vereine
    if (function_exists('ma_vereine_struktur')) {
        $struktur = ma_vereine_struktur();
        $redakteure = get_users(['meta_key' => 'ma_verein_kurz', 'meta_compare' => 'EXISTS', 'fields' => ['ID']]);
        $aktiv = 0; $eingeladen = 0;
        foreach ($redakteure as $u) { if (!ma_zugang_gesperrt((int) $u->ID)) $aktiv++; if (get_user_meta((int) $u->ID, 'ma_verein_eingeladen', true)) $eingeladen++; }
        $profile = $zaehle(['post_type' => 'ma_club', 'post_status' => 'publish', 'meta_query' => [['key' => '_ma_aenderung_von', 'compare' => 'NOT EXISTS']]]);
        $offen = $zaehle(['post_type' => ['post', 'ma_event', 'ma_club'], 'post_status' => ['pending', 'ma_in_pruefung'], 'author__in' => array_map(fn($u) => (int) $u->ID, $redakteure) ?: [0]]);
        $jeVerein = []; $aufrufeVerein = 0;
        $vereinsPosts = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_key' => 'ma_verein', 'meta_compare' => 'EXISTS']);
        foreach ($vereinsPosts as $p) { $tag = get_post_time('Y-m-d', false, $p); if ($tag >= $von && $tag <= $bis) { $n = ma_verein_name((string) get_post_meta($p->ID, 'ma_verein', true)); $jeVerein[$n] = ($jeVerein[$n] ?? 0) + 1; } }
        if ($vereinsPosts) $aufrufeVerein = (int) $wpdb->get_var($wpdb->prepare('SELECT COALESCE(SUM(aufrufe),0) FROM ' . ma_statistik_tabelle() . " WHERE art = 'post' AND tag BETWEEN %s AND %s AND objekt IN (" . implode(',', array_map(fn($p) => (int) $p->ID, $vereinsPosts)) . ')', $von, $bis));
        arsort($jeVerein);
        echo '<h2 class="ma-stat__bereich">Vereine</h2><div class="ma-stat__kacheln">'
            . $kachel(count($struktur), 'Vereine im Verzeichnis', admin_url('users.php?page=ma-vereinszugaenge'), count(ma_vereine_fuer_zugang()) . ' Einträge mit Abteilungen')
            . $kachel($profile, 'Vereinsprofile veröffentlicht')
            . $kachel($aktiv, 'aktive Vereinsredakteure', admin_url('users.php?page=ma-vereinszugaenge'), $eingeladen . ' eingeladen, ' . (count($redakteure) - $aktiv) . ' deaktiviert')
            . $kachel($offen, 'offene Vereinseinreichungen', admin_url('admin.php?page=ma-freigaben'))
            . $kachel(array_sum($jeVerein), 'Vereinsmeldungen veröffentlicht')
            . $kachel($aufrufeVerein, 'Aufrufe von Vereinsmeldungen')
            . '</div><div class="ma-stat__box"><h3>Veröffentlichte Meldungen je Verein</h3>' . ma_statistik_balken($jeVerein) . '</div>';
    }

    // ---------- Anzeigen
    if (function_exists('ma_werbung_summen')) {
        $laufend = array_values(array_filter(get_posts(['post_type' => 'ma_ad', 'post_status' => 'publish', 'posts_per_page' => 200]), fn($a) => ma_ad_is_running($a)));
        $summen = ma_werbung_summen($von, $bis);
        $i = array_sum(array_map(fn($z) => (int) $z->i, $summen)); $k = array_sum(array_map(fn($z) => (int) $z->k, $summen));
        $seitW = ma_statistik_seit(ma_werbung_tabelle());
        $global = (int) get_option('ma_ads_enabled', 0);
        echo '<h2 class="ma-stat__bereich">Anzeigen</h2><div class="ma-stat__kacheln">'
            . $kachel(count($laufend), 'laufende Kampagnen', admin_url('edit.php?post_type=ma_ad'), $global ? '' : 'Werbung ist in den Einstellungen ausgeschaltet')
            . $kachel($seitW === '' ? null : $i, 'Impressionen', '', $seitW === '' ? 'noch nicht erfasst' : '')
            . $kachel($seitW === '' ? null : $k, 'Klicks', '', $seitW === '' ? 'noch nicht erfasst' : '')
            . ($i ? '<div class="ma-stat__kachel"><strong>' . esc_html(number_format_i18n($k / $i * 100, 1)) . ' %</strong><span>Klickrate</span></div>' : $kachel(null, 'Klickrate', '', 'ohne Impressionen nicht berechenbar'))
            . '</div><div class="ma-stat__box"><h3>Kampagnen</h3><table><tr><th>Anzeige</th><th>Platz</th><th>Laufzeit</th><th class="z">Impressionen</th><th class="z">Klicks</th></tr>';
        $nachId = []; foreach ($summen as $z) $nachId[(int) $z->anzeige] = $z;
        $zeilen = array_unique(array_merge(array_map(fn($a) => $a->ID, $laufend), array_keys($nachId)));
        if (!$zeilen) echo '<tr><td colspan="5">Keine laufende Kampagne und im Zeitraum keine erfasste Anzeige. Die Muster-Anzeigen der Seite sind keine Kampagnen und werden nicht gezählt.</td></tr>';
        foreach ($zeilen as $id) {
            $start = (string) get_post_meta($id, 'ma_ad_start', true); $ende = (string) get_post_meta($id, 'ma_ad_end', true);
            printf('<tr><td><a href="%s">%s</a></td><td>%s</td><td>%s</td><td class="z">%s</td><td class="z">%s</td></tr>', esc_url(get_edit_post_link($id)), esc_html(get_the_title($id)),
                esc_html(ma_ad_slot_label((string) get_post_meta($id, 'ma_ad_slot', true))), esc_html(($start !== '' ? wp_date('d.m.Y', strtotime($start)) : 'offen') . ' – ' . ($ende !== '' ? wp_date('d.m.Y', strtotime($ende)) : 'offen')),
                esc_html(number_format_i18n((int) ($nachId[$id]->i ?? 0))), esc_html(number_format_i18n((int) ($nachId[$id]->k ?? 0))));
        }
        echo '</table></div>';
    }

    echo '<p class="description" style="margin-top:18px">Gezählt wird ohne Cookies und ohne Speicherung im Browser. Aufrufe werden nur als Summe je Tag und Meldung gespeichert. Für die Besucherzahl und die Live-Anzeige wird ein Besucher nur innerhalb eines Tages wiedererkannt: über einen Prüfwert aus IP-Adresse, Browserkennung und einem täglich neuen Zufallswert. Die IP-Adresse selbst wird nicht gespeichert, die Besuchsdaten werden nach 40 Tagen gelöscht. Suchmaschinen-Bots und angemeldete Redakteure zählen nicht mit. Ein Neuladen derselben Seite zählt als weiterer Seitenaufruf, aber nicht als weiterer Besucher.</p></div>';
}
