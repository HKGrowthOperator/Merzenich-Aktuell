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
 *    aus dem Repository (chatgpt-site/ auf GitHub; Option ma_bildpool_quelle
 *    kann eine andere Quelle vorschalten).
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

/**
 * Erste Quelle für Poolfotos. Seit 05.10.2026 das Repository: die Vorschauseite
 * auf Coolify ist abgeschaltet. Eine noch gespeicherte Adresse dieser Seite wird
 * ignoriert, damit kein Lauf auf einen toten Server wartet.
 */
function ma_bildpool_quelle(): string {
    $q = untrailingslashit(trim((string) get_option('ma_bildpool_quelle', '')));
    if ($q === '' || str_contains($q, 'merzenichaktuell.hk-growthoperator.de')) return MA_REPO_SEITE;
    return $q;
}

/** Das Repository auf GitHub (immer der Stand von main) als zweite Quelle für alles aus chatgpt-site/. */
const MA_REPO_SEITE = 'https://raw.githubusercontent.com/HKGrowthOperator/Merzenich-Aktuell/main/chatgpt-site';

/** Quellen für Poolfotos in Reihenfolge der Versuche: eine vorgeschaltete Quelle, dann das Repository. */
function ma_bildpool_quellen(): array {
    return array_values(array_unique(apply_filters('ma_bildpool_quellen', [ma_bildpool_quelle(), MA_REPO_SEITE])));
}

/** Hosts, die in diesem Aufruf nicht erreichbar waren (5xx oder keine Verbindung); werden nicht erneut versucht. */
function ma_quelle_ausgefallen(?string $host = null): array {
    static $tot = [];
    if ($host !== null && $host !== '') $tot[$host] = true;
    return $tot;
}

/**
 * Lädt eine Datei von der ersten erreichbaren Adresse. Rückgabe: temporärer Pfad
 * oder null (Grund in $fehler). Ein Host, der 5xx liefert oder keine Verbindung
 * annimmt, wird für den Rest des Aufrufs übersprungen (04.10.2026: die
 * Vorschauseite war Stunden nicht erreichbar, und die Übernahme meldete 308-mal
 * „Service Unavailable“, statt auf das Repository auszuweichen).
 */
function ma_quelle_laden(array $urls, string &$fehler = '', int $timeout = 30): ?string {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $gruende = [];
    foreach ($urls as $url) {
        $host = (string) parse_url($url, PHP_URL_HOST);
        if (isset(ma_quelle_ausgefallen()[$host])) { $gruende[] = $host . ': nicht erreichbar'; continue; }
        $t = download_url($url, $timeout);
        if (!is_wp_error($t)) return $t;
        $daten = $t->get_error_data();
        $code = is_array($daten) ? (int) ($daten['code'] ?? 0) : 0;
        if ($t->get_error_code() !== 'http_404' || $code >= 500) ma_quelle_ausgefallen($host);
        $gruende[] = $host . ': ' . ($code ? 'HTTP ' . $code . ' ' : '') . $t->get_error_message();
    }
    $fehler = $gruende ? implode(' | ', $gruende) : 'keine Adresse';
    return null;
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
    $tmp = ma_quelle_laden(array_map(fn(string $q): string => $q . $b['datei'], ma_bildpool_quellen()), $fehler);
    if ($tmp === null) return 0;
    $credit = trim((string) preg_replace('/^Symbolbild\s*·\s*/u', '', (string) $b['credit']));
    $id = media_handle_sideload(['name' => sanitize_file_name(basename((string) parse_url((string) $b['datei'], PHP_URL_PATH))), 'tmp_name' => $tmp], 0, (string) ($b['alt'] ?: $b['titel']), ['post_excerpt' => $credit]);
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

/**
 * Übernimmt bis zu $max noch fehlende Fotos. Sind alle Quellen ausgefallen, bricht
 * der Lauf nach dem ersten Fehler ab (eine Meldung statt 308).
 * @return array{neu:int,offen:int,fehler:array,ausgefallen:array,hinweis:string}
 */
function ma_bildpools_importieren(int $max = 10): array {
    ma_bildpools_anlegen();
    if (function_exists('set_time_limit')) @set_time_limit(300);
    $neu = 0; $fehler = []; $offen = 0; $abbruch = false;
    $hosts = array_map(fn(string $q): string => (string) parse_url($q, PHP_URL_HOST), ma_bildpool_quellen());
    foreach ((array) (ma_bildpool_daten()['bilder'] ?? []) as $b) {
        if (ma_bildpool_medium((string) $b['id'])) continue;
        if ($neu >= $max || $abbruch) { $offen++; continue; }
        $f = '';
        if (ma_bildpool_uebernehmen($b, $f)) { $neu++; continue; }
        $offen++;
        $tot = ma_quelle_ausgefallen();
        if (count(array_filter($hosts, fn(string $h): bool => isset($tot[$h]))) === count($hosts)) { $abbruch = true; $fehler[] = 'Keine Quelle erreichbar: ' . $f; }
        else $fehler[] = $b['id'] . ': ' . $f;
    }
    $ausgefallen = array_keys(ma_quelle_ausgefallen());
    $hinweis = '';
    if ($abbruch) $hinweis = 'Das Repository auf GitHub war nicht erreichbar. Bitte später erneut „Fotos übernehmen“ anklicken.';
    elseif ($ausgefallen && $neu) $hinweis = 'Eine Quelle (' . implode(', ', $ausgefallen) . ') war nicht erreichbar; die Fotos kamen aus dem Repository.';
    return ['neu' => $neu, 'offen' => $offen, 'fehler' => $fehler, 'ausgefallen' => $ausgefallen, 'hinweis' => $hinweis];
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
    if ($aktion === 'importieren') { $e = ma_bildpools_importieren(10); $q = ['ma_neu' => $e['neu'], 'ma_offen' => $e['offen'], 'ma_fehler' => count($e['fehler'])]; set_transient('ma_bildpool_lauf', ['fehler' => array_slice($e['fehler'], 0, 3), 'hinweis' => $e['hinweis']], 600); }
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
        $lauf = get_transient('ma_bildpool_lauf'); $lauf = is_array($lauf) ? $lauf : ['fehler' => [], 'hinweis' => ''];
        $nFehler = (int) ($_GET['ma_fehler'] ?? 0);
        $text = (int) $_GET['ma_neu'] . ' Fotos übernommen, noch ' . (int) $_GET['ma_offen'] . ' offen.';
        if ($lauf['hinweis'] !== '') $text .= ' ' . $lauf['hinweis'];
        if ($nFehler) $text .= ' ' . $nFehler . ' Fehler' . ($lauf['fehler'] ? ', zum Beispiel: ' . implode('; ', $lauf['fehler']) : '') . ($nFehler > count($lauf['fehler']) && $lauf['fehler'] ? ' …' : '');
        echo '<div class="notice notice-' . ($nFehler ? 'warning' : 'success') . '"><p>' . esc_html($text) . '</p></div>';
        // Weiter in Paketen, solange der letzte Lauf etwas übernommen hat und noch Fotos offen sind.
        if ((int) $_GET['ma_neu'] > 0 && (int) $_GET['ma_offen'] > 0) echo '<script>window.addEventListener("load",function(){var f=document.getElementById("ma-bildpools-import");if(f)setTimeout(function(){f.submit();},600);});</script>';
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
    if ($p->post_type === 'ma_event') return ma_bildpool_fuer_termin($p->post_title . ' ' . (string) get_post_meta($p->ID, 'ma_event_place', true) . ' ' . wp_strip_all_tags($p->post_excerpt . ' ' . mb_substr($p->post_content, 0, 400)));
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

/**
 * Pool für einen Termin nach seiner Art (1.20.8). Vorher bekam jeder Termin ein
 * Kirmesfoto, auch eine Ausschusssitzung im Rathaus. Karneval vor „Sitzung“,
 * weil auch die Kostümsitzung eine Sitzung ist.
 */
function ma_bildpool_fuer_termin(string $text): string {
    $t = mb_strtolower($text);
    // Ganze Wörter über \p{L}: \b allein trennt in UTF-8 an „ß“, „Fußball“ wäre sonst ein Ball.
    if (preg_match('/karneval|kostümsitzung|kostuemsitzung|prunksitzung|kindersitzung|tanz|disco|party|hitnight|(?<!\p{L})ball(?!\p{L})|konzert/u', $t)) return 'tanzdetail';
    if (preg_match('/sitzung|ausschuss|gemeinderat|\brat\b|kuratorium|bürgerversammlung|buergerversammlung|einwohnerversammlung|haushalt|rathaus/u', $t)) return 'aktuell';
    if (preg_match('/kirche|gottesdienst|pfarr|messe\b|andacht|kommunion|firmung|pastoral|kapelle/u', $t)) return 'kirchedetail';
    if (preg_match('/fußball|fussball|tischtennis|turnier|(?<!\p{L})cup(?!\p{L})|sportfest|(?<!\p{L})lauf(?!\p{L})|volkslauf|spendenlauf|staffellauf|tennis|handball|spieltag/u', $t)) return 'sport';
    return 'termine';
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
    $bild = ma_bildpool_waehlen($pool, $p->ID);
    // Detailpools haben oft nur ein geprüftes Foto: Termine fallen auf den Terminpool zurück, alles auf „Aktuell“.
    if (!$bild && $p->post_type === 'ma_event' && $pool !== 'termine') $bild = ma_bildpool_waehlen('termine', $p->ID);
    if (!$bild && $pool !== 'aktuell') $bild = ma_bildpool_waehlen('aktuell', $p->ID);
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

/**
 * Nachlauf (1.20.8): Termine und Meldungen, die vor dem Anlegen der Bildpools
 * veröffentlicht wurden, haben kein Beitragsbild; im Ressort-Menü stand bei
 * „Termine“ deshalb rechts kein einziges Bild. Einmal je Plugin-Version, 60 je
 * Admin-Aufruf, bis keiner mehr fehlt.
 */
function ma_bildpool_nachziehen(int $max = 60): int {
    $ids = get_posts(['post_type' => ['ma_event', 'post'], 'post_status' => 'publish', 'posts_per_page' => $max, 'fields' => 'ids', 'orderby' => 'date', 'order' => 'DESC',
        'meta_query' => [['key' => '_thumbnail_id', 'compare' => 'NOT EXISTS']]]);
    $n = 0;
    // Automatisch gesetzte Termin-Bilder neu wählen, wenn die Art des Termins inzwischen
    // einen anderen Pool ergibt (vorher bekam jeder Termin ein Kirmesfoto). Von Hand
    // gesetzte Bilder tragen kein _ma_bildpool_auto und bleiben unberührt.
    foreach (get_posts(['post_type' => 'ma_event', 'post_status' => 'publish', 'posts_per_page' => 200, 'meta_query' => [['key' => '_ma_bildpool_auto', 'compare' => 'EXISTS']]]) as $p) {
        $pool = ma_bildpool_fuer($p);
        if ($pool === (string) get_post_meta($p->ID, '_ma_bildpool_auto', true)) continue;
        delete_post_thumbnail($p->ID);
        if (ma_bildpool_symbolbild_setzen($p)) $n++;
        else update_post_meta($p->ID, '_ma_bildpool_auto', $pool);
    }
    foreach ($ids as $id) { $p = get_post((int) $id); if ($p && ma_bildpool_symbolbild_setzen($p)) $n++; }
    return $n;
}
add_action('admin_init', function (): void {
    if (get_option('ma_bildpool_auto', '1') !== '1' || get_option('ma_bildpool_nachgezogen') === MA_CORE_VERSION) return;
    $n = ma_bildpool_nachziehen(60);
    if ($n < 60) update_option('ma_bildpool_nachgezogen', MA_CORE_VERSION, false);
});

/* WP-CLI: wp ma-bildpools importieren [--max=50] */
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('ma-bildpools', new class {
        /** Pools anlegen und fehlende Fotos übernehmen. ## OPTIONS [--max=<n>] */
        public function importieren($args, $assoc): void {
            $max = (int) ($assoc['max'] ?? 50);
            $e = ma_bildpools_importieren($max);
            WP_CLI::log($e['neu'] . ' übernommen, ' . $e['offen'] . ' offen.' . ($e['hinweis'] !== '' ? ' ' . $e['hinweis'] : '') . ($e['fehler'] ? ' Fehler: ' . implode('; ', array_slice($e['fehler'], 0, 10)) : ''));
        }
        /** Stand je Pool. */
        public function stand(): void {
            foreach (ma_bildpools_stand() as $pool => $s) WP_CLI::log(sprintf('%-14s WP %3d  geprüft %3d  Liste %3d (%d)', $pool, $s['wp'], $s['geprueft'], $s['liste'], $s['liste_geprueft']));
        }
    });
}
