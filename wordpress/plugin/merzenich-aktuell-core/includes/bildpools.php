<?php
/**
 * Bildpools in WordPress (03.10.2026).
 *
 * Die statische Seite hat 23 gesichtete Foto-Pools (Wikimedia Commons, je
 * Thema mindestens 20 Motive, dazu Detailpools ohne Mindestgröße). In
 * WordPress gab es sie nicht: /assets/editorial-pools/ wurde nur auf die
 * statische Seite umgeleitet. Jetzt:
 *
 *  - Taxonomie ma_bildpool an Medien („Bildpool“), pflegbar nur von der
 *    Redaktion; Spalte und Filter in der Mediathek.
 *  - Medien → Bildpools: Übersicht je Pool (Fotos in WordPress, davon
 *    geprüft, Soll laut Liste, Warnung unter 20), „Pools anlegen“ und
 *    „Fotos übernehmen“ in Paketen. Quelle der Liste: data/bildpools.json
 *    (deploy/wordpress-import.mjs aus editorial-photo-pools.json), Dateien
 *    von der statischen Seite (Option ma_bildpool_quelle).
 *  - Jedes übernommene Foto trägt Fotograf, Quelle, Lizenz und Prüfstand
 *    (Medienfelder aus bildrechte.php); „geprüft“ nur, was in der Liste
 *    gesichtet ist, alles andere bleibt „offen“ und wird nie automatisch
 *    verwendet.
 *  - Symbolbild aus dem Pool: Wird eine Meldung (oder ein Termin) ohne
 *    eigenes Bild veröffentlicht, setzt das Plugin ein geprüftes Foto des
 *    passenden Pools als Beitragsbild, gekennzeichnet als Symbolbild, mit
 *    Nachweis, und vermerkt es im Verlauf. Ein echtes Bild ersetzt es
 *    jederzeit. Kein Ereignisfoto wird erfunden: Poolfotos sind Symbolbilder.
 */
if (!defined('ABSPATH')) { exit; }

const MA_BILDPOOL_TAX = 'ma_bildpool';

function ma_bildpool_labels(): array {
    return [
        'aktuell' => 'Aktuell / Gemeinde', 'blaulicht' => 'Blaulicht', 'polizei' => 'Polizei', 'feuerwehr' => 'Feuerwehr', 'brand' => 'Brand',
        'verkehr' => 'Verkehr', 'sport' => 'Sport', 'termine' => 'Termine / Veranstaltungen', 'vereine' => 'Vereine / Ehrenamt', 'leben' => 'Leben',
        'menschen' => 'Menschen', 'wirtschaft' => 'Wirtschaft', 'tipp' => 'Tipp / Freizeit',
        'technik' => 'Detail: Technische Hilfe', 'rettung' => 'Detail: Rettung', 'unfall' => 'Detail: Unfallstelle', 'flaeche' => 'Detail: Flächenbrand',
        'tennisdetail' => 'Detail: Tennis', 'digitaldetail' => 'Detail: Digital', 'tanzdetail' => 'Detail: Tanz', 'naturdetail' => 'Detail: Natur',
        'vereinsdetail' => 'Detail: Vereinsheim', 'kirchedetail' => 'Detail: Kirche',
    ];
}

/** Detailpools haben keine Mindestgröße (wie EREIGNIS_KATEGORIEN in deploy/lib-symbolbilder.mjs). */
function ma_bildpool_ist_detail(string $pool): bool {
    return in_array($pool, ['technik', 'rettung', 'unfall', 'flaeche', 'tennisdetail', 'digitaldetail', 'tanzdetail', 'naturdetail', 'vereinsdetail', 'kirchedetail'], true);
}

function ma_bildpool_daten(): array {
    static $d = null;
    if ($d !== null) return $d;
    $j = json_decode((string) @file_get_contents(MA_CORE_PATH . 'data/bildpools.json'), true);
    return $d = is_array($j) ? $j : ['pools' => [], 'bilder' => [], 'mindest' => 20];
}

function ma_bildpool_quelle(): string {
    return untrailingslashit((string) get_option('ma_bildpool_quelle', 'https://merzenichaktuell.hk-growthoperator.de'));
}

add_action('init', function (): void {
    register_taxonomy(MA_BILDPOOL_TAX, 'attachment', [
        'labels' => ['name' => 'Bildpools', 'singular_name' => 'Bildpool', 'menu_name' => 'Bildpools', 'all_items' => 'Alle Bildpools', 'edit_item' => 'Bildpool bearbeiten', 'search_items' => 'Bildpools durchsuchen'],
        'public' => false, 'show_ui' => true, 'show_in_menu' => false, 'show_admin_column' => true, 'show_in_rest' => false, 'hierarchical' => false, 'query_var' => false, 'rewrite' => false,
        'update_count_callback' => '_update_generic_term_count',
        'capabilities' => ['manage_terms' => 'edit_others_posts', 'edit_terms' => 'edit_others_posts', 'delete_terms' => 'manage_options', 'assign_terms' => 'edit_others_posts'],
    ]);
}, 12);

/** Legt alle Pools der Liste als Begriffe an (idempotent). */
function ma_bildpools_anlegen(): int {
    $n = 0;
    foreach (array_keys((array) (ma_bildpool_daten()['pools'] ?? [])) as $pool) {
        if (term_exists($pool, MA_BILDPOOL_TAX)) continue;
        $r = wp_insert_term(ma_bildpool_labels()[$pool] ?? $pool, MA_BILDPOOL_TAX, ['slug' => $pool]);
        if (!is_wp_error($r)) $n++;
    }
    return $n;
}

/** Medium zu einer Pool-ID der Liste (0 = noch nicht übernommen). */
function ma_bildpool_medium(string $pool_id): int {
    $ids = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => 'ma_pool_id', 'meta_value' => $pool_id]);
    return (int) ($ids[0] ?? 0);
}

/** Übernimmt ein Foto der Liste in die Mediathek. Gibt die Medien-ID zurück, 0 bei Fehler (Grund in $fehler). */
function ma_bildpool_uebernehmen(array $b, string &$fehler = ''): int {
    $vorhanden = ma_bildpool_medium((string) $b['id']);
    if ($vorhanden) return $vorhanden;
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $url = ma_bildpool_quelle() . $b['datei'];
    $tmp = download_url($url, 30);
    if (is_wp_error($tmp)) { $fehler = $tmp->get_error_message(); return 0; }
    $credit = trim((string) preg_replace('/^Symbolbild\s*·\s*/u', '', (string) $b['credit']));
    $id = media_handle_sideload(['name' => sanitize_file_name(basename(parse_url($url, PHP_URL_PATH))), 'tmp_name' => $tmp], 0, (string) ($b['alt'] ?: $b['titel']), ['post_excerpt' => $credit]);
    if (is_wp_error($id)) { @unlink($tmp); $fehler = $id->get_error_message(); return 0; }
    update_post_meta($id, 'ma_pool_id', (string) $b['id']);
    update_post_meta($id, '_wp_attachment_image_alt', (string) $b['alt']);
    $nutzung = trim((string) $b['lizenz'] . ((string) $b['lizenzUrl'] !== '' ? ' (' . $b['lizenzUrl'] . ')' : ''));
    foreach (['ma_credit' => $credit, 'ma_quelle' => (string) $b['quelle'], 'ma_nutzung' => $nutzung, 'ma_image_credit' => $credit, 'ma_image_license' => (string) $b['lizenz'], 'ma_image_original_url' => (string) $b['quelle'], 'ma_image_type' => 'symbol'] as $k => $v) {
        if ($v !== '') update_post_meta($id, $k, $v);
    }
    $geprueft = !empty($b['geprueft']);
    update_post_meta($id, 'ma_rechtepruefung', $geprueft ? 'geprueft' : 'offen');
    if ($geprueft) {
        update_post_meta($id, 'ma_image_rights_verified', '1');
        update_post_meta($id, 'ma_rechtepruefung_von', ['u' => 0, 't' => (int) strtotime((string) $b['geprueftAm'] ?: 'now'), 'grund' => 'Sichtung der Bildpools (docs/POOLFOTOS-PRUEFUNG.md)']);
    }
    wp_set_object_terms($id, (string) $b['pool'], MA_BILDPOOL_TAX);
    return (int) $id;
}

/** Übernimmt bis zu $max noch fehlende Fotos. @return array{neu:int,offen:int,fehler:array} */
function ma_bildpools_importieren(int $max = 10): array {
    ma_bildpools_anlegen();
    if (function_exists('set_time_limit')) @set_time_limit(300);
    $neu = 0; $fehler = []; $offen = 0;
    foreach ((array) (ma_bildpool_daten()['bilder'] ?? []) as $b) {
        if (ma_bildpool_medium((string) $b['id'])) continue;
        if ($neu >= $max) { $offen++; continue; }
        $f = '';
        if (ma_bildpool_uebernehmen($b, $f)) $neu++; else { $fehler[] = $b['id'] . ': ' . $f; $offen++; }
    }
    return ['neu' => $neu, 'offen' => $offen, 'fehler' => $fehler];
}

/** Stand je Pool: in WordPress, davon geprüft, laut Liste. */
function ma_bildpools_stand(): array {
    $liste = []; $geprueftListe = [];
    foreach ((array) (ma_bildpool_daten()['bilder'] ?? []) as $b) { $liste[$b['pool']] = ($liste[$b['pool']] ?? 0) + 1; if (!empty($b['geprueft'])) $geprueftListe[$b['pool']] = ($geprueftListe[$b['pool']] ?? 0) + 1; }
    $raus = [];
    foreach (array_keys(ma_bildpool_labels()) as $pool) {
        $ids = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'fields' => 'ids', 'tax_query' => [['taxonomy' => MA_BILDPOOL_TAX, 'field' => 'slug', 'terms' => $pool]]]);
        $geprueft = count(array_filter($ids, fn($i) => function_exists('ma_rechtepruefung') ? ma_rechtepruefung((int) $i) === 'geprueft' : get_post_meta($i, 'ma_image_rights_verified', true) === '1'));
        $raus[$pool] = ['label' => ma_bildpool_labels()[$pool], 'wp' => count($ids), 'geprueft' => $geprueft, 'liste' => $liste[$pool] ?? 0, 'liste_geprueft' => $geprueftListe[$pool] ?? 0, 'detail' => ma_bildpool_ist_detail($pool), 'angelegt' => (bool) term_exists($pool, MA_BILDPOOL_TAX)];
    }
    return $raus;
}

/* ---------------------------------------------------------- Seite Medien → Bildpools */
add_action('admin_menu', function (): void {
    add_media_page('Bildpools', 'Bildpools', 'edit_others_posts', 'ma-bildpools', 'ma_bildpools_seite');
});

add_action('admin_post_ma_bildpools', function (): void {
    if (!current_user_can('edit_others_posts') || (function_exists('ma_current_partner_policy') && ma_current_partner_policy())) wp_die('Keine Berechtigung.', 403);
    check_admin_referer('ma_bildpools');
    $aktion = sanitize_key(wp_unslash($_POST['aktion'] ?? ''));
    $q = [];
    if ($aktion === 'anlegen') $q['ma_angelegt'] = ma_bildpools_anlegen();
    if ($aktion === 'importieren') { $e = ma_bildpools_importieren(10); $q = ['ma_neu' => $e['neu'], 'ma_offen' => $e['offen'], 'ma_fehler' => count($e['fehler'])]; if ($e['fehler']) set_transient('ma_bildpool_fehler', array_slice($e['fehler'], 0, 10), 600); }
    if ($aktion === 'auto' && current_user_can('manage_options')) update_option('ma_bildpool_auto', !empty($_POST['auto']) ? '1' : '0', false);
    wp_safe_redirect(add_query_arg($q, admin_url('upload.php?page=ma-bildpools'))); exit;
});

function ma_bildpools_seite(): void {
    if (!current_user_can('edit_others_posts')) wp_die('Keine Berechtigung.');
    $stand = ma_bildpools_stand();
    $mindest = (int) (ma_bildpool_daten()['mindest'] ?? 20);
    $offen = 0;
    foreach ((array) (ma_bildpool_daten()['bilder'] ?? []) as $b) if (!ma_bildpool_medium((string) $b['id'])) $offen++;
    echo '<div class="wrap"><h1>Bildpools</h1>';
    echo '<p>Gesichtete Symbolfotos je Thema (Quelle: Wikimedia Commons, Liste vom ' . esc_html((string) (ma_bildpool_daten()['stand'] ?? '')) . '). Ein Poolfoto ist immer ein <strong>Symbolbild</strong>, nie ein Foto vom Ereignis. Nur Fotos mit Rechteprüfung „geprüft“ werden verwendet.</p>';
    if (isset($_GET['ma_angelegt'])) echo '<div class="notice notice-success"><p>' . (int) $_GET['ma_angelegt'] . ' Pools angelegt.</p></div>';
    if (isset($_GET['ma_neu'])) {
        echo '<div class="notice notice-' . (!empty($_GET['ma_fehler']) ? 'warning' : 'success') . '"><p>' . (int) $_GET['ma_neu'] . ' Fotos übernommen, noch ' . (int) $_GET['ma_offen'] . ' offen.' . (!empty($_GET['ma_fehler']) ? ' ' . (int) $_GET['ma_fehler'] . ' Fehler: ' . esc_html(implode('; ', (array) get_transient('ma_bildpool_fehler'))) : '') . '</p></div>';
        // Weiter in Paketen, bis alle übernommen sind (ohne Fehler).
        if ((int) $_GET['ma_neu'] > 0 && (int) $_GET['ma_offen'] > 0 && empty($_GET['ma_fehler'])) echo '<script>window.addEventListener("load",function(){var f=document.getElementById("ma-bildpools-import");if(f)setTimeout(function(){f.submit();},600);});</script>';
    }
    $form = function (string $aktion, string $knopf, string $extra = '', string $id = '') {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline-block;margin:0 8px 8px 0"' . ($id ? ' id="' . esc_attr($id) . '"' : '') . '><input type="hidden" name="action" value="ma_bildpools"><input type="hidden" name="aktion" value="' . esc_attr($aktion) . '">';
        wp_nonce_field('ma_bildpools');
        echo $extra . '<button class="button' . ($aktion === 'importieren' ? ' button-primary' : '') . '">' . esc_html($knopf) . '</button></form>';
    };
    $form('anlegen', 'Pools anlegen');
    if ($offen) $form('importieren', 'Fotos übernehmen (' . $offen . ' offen, in Paketen zu 10)', '', 'ma-bildpools-import');
    else echo '<p><strong>Alle ' . count((array) (ma_bildpool_daten()['bilder'] ?? [])) . ' Fotos der Liste sind in der Mediathek.</strong></p>';
    echo '<table class="widefat striped" style="max-width:980px;margin-top:8px"><thead><tr><th>Pool</th><th>In WordPress</th><th>davon geprüft</th><th>Liste (geprüft)</th><th>Hinweis</th></tr></thead><tbody>';
    foreach ($stand as $pool => $s) {
        $hinweis = !$s['angelegt'] ? 'noch nicht angelegt' : (!$s['detail'] && $s['geprueft'] < $mindest ? 'unter ' . $mindest . ' geprüften Fotos: ergänzen' : ($s['detail'] ? 'Detailpool, ohne Mindestgröße' : 'vollständig'));
        printf('<tr><td><a href="%s"><strong>%s</strong></a><br><code>%s</code></td><td>%d</td><td>%d</td><td>%d (%d)</td><td%s>%s</td></tr>', esc_url(admin_url('upload.php?mode=list&' . MA_BILDPOOL_TAX . '=' . $pool)), esc_html($s['label']), esc_html($pool), $s['wp'], $s['geprueft'], $s['liste'], $s['liste_geprueft'], str_starts_with($hinweis, 'unter') || $hinweis === 'noch nicht angelegt' ? ' style="color:#b32d2e"' : '', esc_html($hinweis));
    }
    echo '</tbody></table>';
    $auto = get_option('ma_bildpool_auto', '1') === '1';
    echo '<h2>Symbolbild aus dem Pool</h2><p>Wird eine Meldung oder ein Termin ohne eigenes Bild veröffentlicht, setzt das System ein geprüftes Foto des passenden Pools als Beitragsbild (gekennzeichnet als Symbolbild, mit Nachweis, Eintrag im Verlauf). Ein eigenes Bild ersetzt es jederzeit.</p>';
    if (current_user_can('manage_options')) $form('auto', 'Speichern', '<label style="margin-right:8px"><input type="checkbox" name="auto" value="1"' . checked($auto, true, false) . '> Symbolbild automatisch setzen</label>');
    else echo '<p>Zurzeit ' . ($auto ? 'eingeschaltet' : 'ausgeschaltet') . '.</p>';
    echo '</div>';
}

/* Filter in der Mediathek (Listenansicht). */
add_action('restrict_manage_posts', function (string $typ): void {
    if ($typ !== 'attachment' || !current_user_can('edit_others_posts')) return;
    $jetzt = sanitize_key(wp_unslash($_GET[MA_BILDPOOL_TAX] ?? ''));
    echo '<select name="' . esc_attr(MA_BILDPOOL_TAX) . '" aria-label="Nach Bildpool filtern"><option value="">Alle Bildpools</option>';
    foreach (ma_bildpool_labels() as $k => $l) printf('<option value="%s"%s>%s</option>', esc_attr($k), selected($jetzt, $k, false), esc_html($l));
    echo '</select>';
});
add_action('pre_get_posts', function (WP_Query $q): void {
    global $pagenow;
    if (!is_admin() || !$q->is_main_query() || $pagenow !== 'upload.php') return;
    $pool = sanitize_key(wp_unslash($_GET[MA_BILDPOOL_TAX] ?? ''));
    if ($pool !== '' && isset(ma_bildpool_labels()[$pool])) $q->set('tax_query', [['taxonomy' => MA_BILDPOOL_TAX, 'field' => 'slug', 'terms' => $pool]]);
});

/* ---------------------------------------------------------- Symbolbild für Meldungen ohne Bild */

/** Passender Pool: Rubrik, bei Blaulicht nach Stichworten (Polizei, Brand, Verkehr). */
function ma_bildpool_fuer(WP_Post $p): string {
    if ($p->post_type === 'ma_event') return 'termine';
    $kats = array_map(fn($t) => $t->slug, (array) get_the_category($p->ID));
    $text = mb_strtolower($p->post_title . ' ' . wp_strip_all_tags($p->post_excerpt . ' ' . mb_substr($p->post_content, 0, 600)));
    if (in_array('blaulicht', $kats, true)) {
        if (preg_match('/polizei|einbruch|diebstahl|betrug|festnahm|zeugen|kripo/u', $text)) return 'polizei';
        if (preg_match('/unfall|verkehr|ölspur|oelspur|kollision|zusammenstoß|l\s?264|b\s?264|a\s?4\b/u', $text)) return 'verkehr';
        if (preg_match('/brand|feuer|rauch|flammen/u', $text)) return 'brand';
        if (preg_match('/feuerwehr|löschgruppe|loeschgruppe|einsatz/u', $text)) return 'feuerwehr';
        return 'blaulicht';
    }
    foreach (['sport' => 'sport', 'vereine' => 'vereine', 'leben' => 'leben', 'menschen' => 'menschen', 'wirtschaft' => 'wirtschaft', 'tipp' => 'tipp', 'rathaus' => 'aktuell'] as $kat => $pool) if (in_array($kat, $kats, true)) return $pool;
    return 'aktuell';
}

/** Geprüftes Foto des Pools; nicht dasselbe wie bei den letzten acht Meldungen. 0 = keines. */
function ma_bildpool_waehlen(string $pool, int $post_id): int {
    $ids = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC',
        'tax_query' => [['taxonomy' => MA_BILDPOOL_TAX, 'field' => 'slug', 'terms' => $pool]], 'meta_query' => [['key' => 'ma_rechtepruefung', 'value' => 'geprueft']]]);
    if (!$ids) return 0;
    $zuletzt = array_map('intval', array_filter(array_map(fn($i) => get_post_thumbnail_id($i), get_posts(['post_type' => ['post', 'ma_event'], 'post_status' => 'publish', 'numberposts' => 8, 'fields' => 'ids', 'post__not_in' => [$post_id]]))));
    $n = count($ids);
    for ($i = 0; $i < $n; $i++) { $kandidat = (int) $ids[($post_id + $i) % $n]; if (!in_array($kandidat, $zuletzt, true)) return $kandidat; }
    return (int) $ids[$post_id % $n];
}

/** Setzt ein Poolfoto als Beitragsbild (nur ohne eigenes Bild). Gibt die Medien-ID zurück. */
function ma_bildpool_symbolbild_setzen(WP_Post $p): int {
    if (has_post_thumbnail($p->ID) || !in_array($p->post_type, ['post', 'ma_event'], true)) return 0;
    $pool = ma_bildpool_fuer($p);
    $bild = ma_bildpool_waehlen($pool, $p->ID) ?: ($pool !== 'aktuell' ? ma_bildpool_waehlen('aktuell', $p->ID) : 0);
    if (!$bild) return 0;
    set_post_thumbnail($p->ID, $bild);
    foreach (['ma_image_credit' => 'ma_image_credit', 'ma_image_license' => 'ma_image_license', 'ma_image_original_url' => 'ma_image_original_url'] as $von => $nach) {
        $v = (string) get_post_meta($bild, $von, true);
        if ($v !== '') update_post_meta($p->ID, $nach, $v);
    }
    update_post_meta($p->ID, 'ma_image_type', 'symbol');
    update_post_meta($p->ID, 'ma_image_rights_verified', '1');
    update_post_meta($p->ID, '_ma_bildpool_auto', $pool);
    if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($p->ID, 'Symbolbild aus dem Bildpool „' . (ma_bildpool_labels()[$pool] ?? $pool) . '“ gesetzt (kein eigenes Bild)', '', 0);
    return $bild;
}

add_action('transition_post_status', function (string $neu, string $alt, $p): void {
    if (!$p instanceof WP_Post || $neu !== 'publish' || $alt === 'publish' || get_option('ma_bildpool_auto', '1') !== '1') return;
    // WXR-Import: Das Beitragsbild kommt erst nach dem Anlegen als Meta, also hier nichts setzen.
    if (defined('WP_IMPORTING') && WP_IMPORTING) return;
    ma_bildpool_symbolbild_setzen($p);
}, 30, 3);

/* WP-CLI: wp ma-bildpools importieren [--max=50] */
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('ma-bildpools', new class {
        /** Pools anlegen und fehlende Fotos übernehmen. ## OPTIONS [--max=<n>] */
        public function importieren($args, $assoc): void {
            $max = (int) ($assoc['max'] ?? 50);
            $e = ma_bildpools_importieren($max);
            WP_CLI::log($e['neu'] . ' übernommen, ' . $e['offen'] . ' offen.' . ($e['fehler'] ? ' Fehler: ' . implode('; ', $e['fehler']) : ''));
        }
        /** Stand je Pool. */
        public function stand(): void {
            foreach (ma_bildpools_stand() as $pool => $s) WP_CLI::log(sprintf('%-14s WP %3d  geprüft %3d  Liste %3d (%d)', $pool, $s['wp'], $s['geprueft'], $s['liste'], $s['liste_geprueft']));
        }
    });
}
