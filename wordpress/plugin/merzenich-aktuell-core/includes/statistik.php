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

function ma_statistik_zaehlen(int $objekt, string $art): void {
    global $wpdb;
    $wpdb->query($wpdb->prepare('INSERT INTO ' . ma_statistik_tabelle() . ' (tag, objekt, art, aufrufe) VALUES (%s, %d, %s, 1) ON DUPLICATE KEY UPDATE aufrufe = aufrufe + 1', current_time('Y-m-d'), $objekt, $art));
}

add_action('rest_api_init', function (): void {
    register_rest_route('ma/v1', '/aufruf', [
        'methods' => 'POST', 'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $r) {
            if (!ma_statistik_zaehlt((string) $r->get_header('user_agent'))) return new WP_REST_Response(null, 204);
            $d = json_decode((string) $r->get_body(), true) ?: [];
            $art = in_array($d['art'] ?? '', ['post', 'start', 'seite', 'liste'], true) ? $d['art'] : 'seite';
            $id = (int) ($d['id'] ?? 0);
            // Nur veröffentlichte Inhalte; Startseite und Listen zählen als Objekt 0.
            if ($art === 'post' && (!$id || get_post_status($id) !== 'publish')) return new WP_REST_Response(null, 204);
            if ($art !== 'post' && $art !== 'seite') $id = 0;
            ma_statistik_zaehlen($id, $art);
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
    // Ein Aufruf je Seitenaufbau, ohne Cookie und ohne Speicherung im Browser.
    printf('<script>(function(){try{var b=JSON.stringify({id:%d,art:%s});if(navigator.sendBeacon)navigator.sendBeacon(%s,new Blob([b],{type:"application/json"}));}catch(e){}})();</script>',
        $id, wp_json_encode($art), wp_json_encode($url));
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
    echo "Tag;Aufrufe\n";
    foreach (ma_statistik_verlauf(90) as $t => $v) echo $t . ';' . $v . "\n";
    exit;
});

function ma_statistik_seite(): void {
    if (!current_user_can('edit_others_posts') || (function_exists('ma_current_partner_policy') && ma_current_partner_policy())) wp_die('Keine Berechtigung.');
    $kachel = fn($zahl, $text, $link = '') => '<div class="ma-stat__kachel"><strong>' . esc_html(number_format_i18n((int) $zahl)) . '</strong><span>' . ($link ? '<a href="' . esc_url($link) . '">' . esc_html($text) . '</a>' : esc_html($text)) . '</span></div>';
    $seit30 = gmdate('Y-m-d', strtotime('-30 days'));
    $veroeff30 = count(get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'date_query' => [['after' => $seit30]]]));
    $wartend = (int) (wp_count_posts('post')->pending ?? 0);
    $eingang_neu = post_type_exists('ma_eingang') ? (int) (wp_count_posts('ma_eingang')->ma_neu ?? 0) : 0;
    $komm_wartend = (int) (wp_count_comments()->moderated ?? 0);
    $komm30 = (int) get_comments(['count' => true, 'status' => 'approve', 'date_query' => [['after' => $seit30]]]);
    $partner30 = count(get_posts(['post_type' => 'any', 'post_status' => ['pending', 'publish', 'draft', (defined('MA_REJECTED_STATUS') ? MA_REJECTED_STATUS : 'ma_rejected')], 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_ma_partner_submission', 'meta_value' => '1', 'date_query' => [['after' => $seit30]]]));

    echo '<div class="wrap ma-stat"><h1>Statistik</h1>';
    echo '<style>.ma-stat__kacheln{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:12px;margin:16px 0 24px}.ma-stat__kachel{background:#fff;border:1px solid #dcdcde;padding:14px 16px}.ma-stat__kachel strong{display:block;font-size:28px;line-height:1.1;font-variant-numeric:tabular-nums}.ma-stat__kachel span{color:#50575e}.ma-stat__raster{display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:20px}.ma-stat__box{background:#fff;border:1px solid #dcdcde;padding:14px 16px}.ma-stat__box h2{margin-top:0;font-size:15px}.ma-stat__box table{width:100%;border-collapse:collapse}.ma-stat__box td{padding:5px 0;border-top:1px solid #f0f0f1}.ma-stat__box td:last-child{text-align:right;font-variant-numeric:tabular-nums}</style>';
    echo '<div class="ma-stat__kacheln">'
        . $kachel(ma_statistik_summe(1), 'Aufrufe heute')
        . $kachel(ma_statistik_summe(7), 'Aufrufe 7 Tage')
        . $kachel(ma_statistik_summe(30), 'Aufrufe 30 Tage')
        . $kachel($veroeff30, 'Meldungen veröffentlicht (30 Tage)', admin_url('edit.php?post_status=publish&post_type=post'))
        . $kachel($wartend, 'warten auf Freigabe', admin_url('admin.php?page=ma-freigaben'))
        . $kachel($eingang_neu, 'neue Einsendungen im Eingang', admin_url('edit.php?post_type=ma_eingang'))
        . $kachel($komm_wartend, 'Kommentare warten', admin_url('edit-comments.php?comment_status=moderated'))
        . $kachel($komm30, 'Kommentare freigegeben (30 Tage)')
        . $kachel($partner30, 'Partner-Einreichungen (30 Tage)')
        . '</div>';
    echo '<div class="ma-stat__box" style="margin-bottom:20px"><h2>Aufrufe je Tag, letzte 30 Tage</h2>' . ma_statistik_svg(ma_statistik_verlauf(30))
        . '<p><a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=ma_statistik_csv'), 'ma_statistik_csv')) . '">Tageswerte (90 Tage) als CSV</a></p></div>';
    echo '<div class="ma-stat__raster">';
    foreach ([7 => 'Meistgelesen, 7 Tage', 30 => 'Meistgelesen, 30 Tage'] as $tage => $titel) {
        echo '<div class="ma-stat__box"><h2>' . esc_html($titel) . '</h2><table>';
        $top = ma_statistik_top($tage, 10);
        if (!$top) echo '<tr><td>Noch keine Aufrufe gezählt.</td><td></td></tr>';
        foreach ($top as $z) printf('<tr><td><a href="%s">%s</a></td><td>%s</td></tr>', esc_url(get_permalink((int) $z->objekt)), esc_html(get_the_title((int) $z->objekt)), esc_html(number_format_i18n((int) $z->n)));
        echo '</table></div>';
    }
    echo '<div class="ma-stat__box"><h2>Aufrufe je Rubrik, 30 Tage</h2><table>';
    $je = ma_statistik_je_rubrik(30);
    if (!$je) echo '<tr><td>Noch keine Aufrufe gezählt.</td><td></td></tr>';
    foreach ($je as $name => $n) printf('<tr><td>%s</td><td>%s</td></tr>', esc_html($name), esc_html(number_format_i18n($n)));
    echo '</table></div>';
    echo '<div class="ma-stat__box"><h2>Veröffentlichte Meldungen je Rubrik</h2><table>';
    foreach (get_categories(['hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC']) as $k) printf('<tr><td><a href="%s">%s</a></td><td>%s</td></tr>', esc_url(admin_url('edit.php?category_name=' . $k->slug)), esc_html($k->name), esc_html(number_format_i18n($k->count)));
    echo '</table></div>';
    if (taxonomy_exists('ma_location')) {
        echo '<div class="ma-stat__box"><h2>Meldungen je Ortsteil</h2><table>';
        foreach (get_terms(['taxonomy' => 'ma_location', 'hide_empty' => true]) as $t) printf('<tr><td>%s</td><td>%s</td></tr>', esc_html($t->name), esc_html(number_format_i18n($t->count)));
        echo '</table></div>';
    }
    echo '</div><p class="description" style="margin-top:18px">Gezählt wird ohne Cookies, ohne IP-Adresse und ohne Speicherung im Browser, nur als Summe je Tag und Meldung. Suchmaschinen-Bots und angemeldete Redakteure zählen nicht mit. Die Zählung läuft seit der Installation dieser Version (Plugin ' . esc_html(MA_CORE_VERSION) . ').</p></div>';
}
