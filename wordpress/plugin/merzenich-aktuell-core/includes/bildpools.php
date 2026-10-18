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

/**
 * Pools in fester, gut lesbarer Reihenfolge (Wunsch Betreiber 07.10.2026):
 * zuerst die Themenpools A–Z, dann „Detail: …“ A–Z.
 */
function ma_bildpool_labels_sortiert(): array {
    $l = ma_bildpool_labels();
    uksort($l, function (string $a, string $b) use ($l): int {
        $da = ma_bildpool_ist_detail($a); $db = ma_bildpool_ist_detail($b);
        return $da !== $db ? ($da ? 1 : -1) : strcoll(remove_accents($l[$a]), remove_accents($l[$b]));
    });
    return $l;
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

/**
 * Sichtprüfung aus der Liste in WordPress nachziehen (1.25.0): Fotos, die die
 * Liste inzwischen als geprüft führt, werden geprüft; Poolfotos, die nicht mehr
 * in der Liste stehen (bei der Sichtprüfung ausgeschlossen), werden „Abgelehnt –
 * nicht verwenden“ und verlassen ihren Pool. Gelöscht wird nichts; was die
 * Redaktion von Hand entschieden hat (Stufe nicht „offen“), bleibt.
 * @return array{geprueft:int,abgelehnt:int}
 */
function ma_bildpools_pruefung_abgleichen(): array {
    $liste = [];
    foreach ((array) (ma_bildpool_daten()['bilder'] ?? []) as $b) $liste[(string) $b['id']] = $b;
    $n = ['geprueft' => 0, 'abgelehnt' => 0];
    if (!$liste) return $n;
    $ids = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => 'ma_pool_id', 'no_found_rows' => true]);
    foreach ($ids as $id) {
        $pid = (string) get_post_meta($id, 'ma_pool_id', true);
        $stufe = function_exists('ma_rechtepruefung') ? ma_rechtepruefung((int) $id) : ((string) get_post_meta($id, 'ma_image_rights_verified', true) === '1' ? 'geprueft' : 'offen');
        if ($stufe !== 'offen') continue;
        $b = $liste[$pid] ?? null;
        if ($b && !empty($b['geprueft'])) {
            update_post_meta($id, 'ma_rechtepruefung', 'geprueft');
            update_post_meta($id, 'ma_image_rights_verified', '1');
            update_post_meta($id, 'ma_rechtepruefung_von', ['u' => 0, 't' => time(), 'grund' => 'Sichtung der Bildpools (docs/POOLFOTOS-PRUEFUNG.md)']);
            $n['geprueft']++;
        } elseif (!$b) {
            update_post_meta($id, 'ma_rechtepruefung', 'abgelehnt');
            delete_post_meta($id, 'ma_image_rights_verified');
            update_post_meta($id, 'ma_rechtepruefung_von', ['u' => 0, 't' => time(), 'grund' => 'Bei der Sichtprüfung der Bildpools ausgeschlossen (deploy/editorial-photo-review.json)']);
            wp_set_object_terms((int) $id, [], MA_BILDPOOL_TAX);
            $n['abgelehnt']++;
        }
    }
    return $n;
}

/* Einmal je Listenstand beim Aufruf im Backend nachziehen. */
add_action('admin_init', function (): void {
    if (!current_user_can('edit_others_posts')) return;
    $d = ma_bildpool_daten();
    $kennung = (string) ($d['stand'] ?? '') . ':' . count((array) ($d['bilder'] ?? [])) . ':' . count(array_filter((array) ($d['bilder'] ?? []), fn($b) => !empty($b['geprueft'])));
    if (get_option('ma_bildpool_pruefstand') === $kennung) return;
    update_option('ma_bildpool_pruefstand', $kennung, false);
    ma_bildpools_pruefung_abgleichen();
});

/** Stand je Pool: in WordPress, davon geprüft, laut Liste. */
function ma_bildpools_stand(): array {
    $liste = []; $geprueftListe = [];
    foreach ((array) (ma_bildpool_daten()['bilder'] ?? []) as $b) { $liste[$b['pool']] = ($liste[$b['pool']] ?? 0) + 1; if (!empty($b['geprueft'])) $geprueftListe[$b['pool']] = ($geprueftListe[$b['pool']] ?? 0) + 1; }
    $raus = [];
    foreach (array_keys(ma_bildpool_labels_sortiert()) as $pool) {
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
    if ($aktion === 'verteilen') $q['ma_verteilt'] = ma_bildpool_verteilen();
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
    if (isset($_GET['ma_verteilt'])) echo '<div class="notice notice-success"><p>' . (int) $_GET['ma_verteilt'] . ' Symbolbilder neu verteilt (höchstens ' . MA_BILDPOOL_GRENZE . '× dasselbe Foto unter den sichtbaren Inhalten).</p></div>';
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
    $form('verteilen', 'Symbolbilder verteilen');
    if ($offen) $form('importieren', 'Fotos übernehmen (' . $offen . ' offen, in Paketen zu 10)', '', 'ma-bildpools-import');
    else echo '<p><strong>Alle ' . count((array) (ma_bildpool_daten()['bilder'] ?? [])) . ' Fotos der Liste sind in der Mediathek.</strong></p>';
    echo '<table class="widefat striped" style="max-width:980px;margin-top:8px"><thead><tr><th>Pool</th><th>In WordPress</th><th>davon geprüft</th><th>Liste (geprüft)</th><th>Hinweis</th></tr></thead><tbody>';
    $gruppe = '';
    foreach ($stand as $pool => $s) {
        $neueGruppe = $s['detail'] ? 'Detailpools (ohne Mindestgröße)' : 'Themenpools (je ' . $mindest . ' Fotos)';
        if ($neueGruppe !== $gruppe) { $gruppe = $neueGruppe; echo '<tr><th colspan="5" style="background:#f0f0f1">' . esc_html($gruppe) . '</th></tr>'; }
        $hinweis = !$s['angelegt'] ? 'noch nicht angelegt' : (!$s['detail'] && $s['geprueft'] < $mindest ? 'Fehlen noch ' . ($mindest - $s['geprueft']) . ' geprüfte Fotos' . ($s['liste'] > $s['wp'] ? ' (' . ($s['liste'] - $s['wp']) . ' davon über „Fotos übernehmen“)' : '') : ($s['detail'] ? ($s['wp'] ? 'Detailpool' : 'Detailpool, noch leer') : 'vollständig'));
        printf('<tr><td><a href="%s"><strong>%s</strong></a><br><code>%s</code></td><td>%d</td><td>%d</td><td>%d (%d)</td><td%s>%s</td></tr>', esc_url(admin_url('upload.php?mode=list&' . MA_BILDPOOL_TAX . '=' . $pool)), esc_html($s['label']), esc_html($pool), $s['wp'], $s['geprueft'], $s['liste'], $s['liste_geprueft'], str_starts_with($hinweis, 'unter') || $hinweis === 'noch nicht angelegt' ? ' style="color:#b32d2e"' : '', esc_html($hinweis));
    }
    echo '</tbody></table>';
    $auto = get_option('ma_bildpool_auto', '1') === '1';
    echo '<h2>Symbolbild aus dem Pool</h2><p>Wird eine Meldung oder ein Termin ohne eigenes Bild veröffentlicht, setzt das System ein geprüftes Foto des passenden Pools als Beitragsbild (gekennzeichnet als Symbolbild, mit Nachweis, Eintrag im Verlauf). Ein eigenes Bild ersetzt es jederzeit.</p>';
    if (current_user_can('manage_options')) $form('auto', 'Speichern', '<label style="margin-right:8px"><input type="checkbox" name="auto" value="1"' . checked($auto, true, false) . '> Symbolbild automatisch setzen</label>');
    else echo '<p>Zurzeit ' . ($auto ? 'eingeschaltet' : 'ausgeschaltet') . '.</p>';
    echo '</div>';
}

/* Filter in der Mediathek: Listenansicht (Auswahl über der Liste; auch der Link in der
   Spalte „Bildpools“ wählt den Pool) und Rasteransicht samt Beitragsbild-Fenster (1.25.0). */
function ma_bildpool_aus_anfrage(): string {
    $pool = sanitize_key(wp_unslash($_GET[MA_BILDPOOL_TAX] ?? ''));
    if ($pool === '' && sanitize_key(wp_unslash($_GET['taxonomy'] ?? '')) === MA_BILDPOOL_TAX) $pool = sanitize_key(wp_unslash($_GET['term'] ?? ''));
    return isset(ma_bildpool_labels()[$pool]) ? $pool : '';
}
add_action('restrict_manage_posts', function (string $typ): void {
    if ($typ !== 'attachment' || !current_user_can('edit_others_posts')) return;
    $jetzt = ma_bildpool_aus_anfrage();
    echo '<select name="' . esc_attr(MA_BILDPOOL_TAX) . '" aria-label="Nach Bildpool filtern"><option value="">Alle Bildpools</option>';
    foreach (ma_bildpool_labels_sortiert() as $k => $l) printf('<option value="%s"%s>%s</option>', esc_attr($k), selected($jetzt, $k, false), esc_html($l));
    echo '</select>';
});
add_action('pre_get_posts', function (WP_Query $q): void {
    global $pagenow;
    if (!is_admin() || !$q->is_main_query() || $pagenow !== 'upload.php') return;
    $pool = ma_bildpool_aus_anfrage();
    if ($pool !== '') $q->set('tax_query', [['taxonomy' => MA_BILDPOOL_TAX, 'field' => 'slug', 'terms' => $pool]]);
});
add_filter('ajax_query_attachments_args', function (array $q): array {
    $pool = sanitize_key(wp_unslash($_REQUEST['query'][MA_BILDPOOL_TAX] ?? ''));
    if ($pool !== '' && isset(ma_bildpool_labels()[$pool]) && current_user_can('edit_others_posts')) $q['tax_query'] = [['taxonomy' => MA_BILDPOOL_TAX, 'field' => 'slug', 'terms' => $pool]];
    return $q;
});
add_action('admin_enqueue_scripts', function (): void {
    if (!current_user_can('edit_others_posts')) return;
    $pools = [];
    foreach (ma_bildpool_labels_sortiert() as $k => $l) $pools[] = ['slug' => $k, 'label' => $l];
    // Hängt an media-views: läuft nur dort, wo die Mediathek oder das Bild-Auswahlfenster geladen ist.
    wp_add_inline_script('media-views', 'window.maBildpools=' . wp_json_encode($pools) . ';(function(){var v=wp&&wp.media&&wp.media.view;if(!v||!v.AttachmentFilters||!v.AttachmentsBrowser)return;'
        . 'var Pools=v.AttachmentFilters.extend({id:"ma-bildpool-filter",createFilters:function(){var f={alle:{text:"Alle Bildpools",props:{ma_bildpool:""},priority:1}};(window.maBildpools||[]).forEach(function(p,i){f[p.slug]={text:p.label,props:{ma_bildpool:p.slug},priority:i+2};});this.filters=f;}});'
        . 'var B=v.AttachmentsBrowser;v.AttachmentsBrowser=B.extend({createToolbar:function(){B.prototype.createToolbar.apply(this,arguments);if(this.toolbar&&this.collection&&this.collection.props)this.toolbar.set("maBildpool",new Pools({controller:this.controller,model:this.collection.props,priority:-75}).render());}});})();');
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

/* Höchstens so oft dasselbe Poolfoto unter den sichtbaren Inhalten (17.10.2026: auf
   /termine/ stand dasselbe Kirchenfoto siebenmal, weil beim Vergeben erst eines der
   vier Kirchenfotos geprüft war und nie neu verteilt wurde). Sichtbar sind seit 1.30.0
   alle veröffentlichten Meldungen: Die Listen laden beim Scrollen bis in den Juli nach,
   und dort standen dieselben fünf Fotos bis zu elfmal (18.10.2026). */
const MA_BILDPOOL_GRENZE = 2;

/** Geprüfte Fotos eines Pools (Medien-IDs, aufsteigend). */
function ma_bildpool_kandidaten(string $pool): array {
    static $cache = [];
    if (!isset($cache[$pool])) $cache[$pool] = array_map('intval', get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC',
        'tax_query' => [['taxonomy' => MA_BILDPOOL_TAX, 'field' => 'slug', 'terms' => $pool]], 'meta_query' => [['key' => 'ma_rechtepruefung', 'value' => 'geprueft']]]));
    return $cache[$pool];
}

/** Sichtbare Inhalte: alle veröffentlichten Meldungen, kommende und laufende Termine (IDs, wie /termine/). */
function ma_bildpool_sichtbar(): array {
    $meldungen = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 2000, 'fields' => 'ids', 'no_found_rows' => true]);
    $termine = get_posts(['post_type' => 'ma_event', 'post_status' => 'publish', 'posts_per_page' => 400, 'fields' => 'ids', 'no_found_rows' => true,
        'meta_query' => ['relation' => 'OR',
            ['key' => 'ma_event_end', 'value' => (string) current_time('Y-m-d H:i:s'), 'compare' => '>=', 'type' => 'DATETIME'],
            ['key' => 'ma_event_start', 'value' => (string) current_time('Y-m-d'), 'compare' => '>=']]]);
    $ids = array_values(array_unique(array_map('intval', array_merge($meldungen, $termine))));
    // Bildangaben der Inhalte und ihrer Beitragsbilder in einem Zug laden statt je Inhalt.
    if ($ids && function_exists('update_meta_cache')) {
        update_meta_cache('post', $ids);
        $bilder = array_filter(array_map('get_post_thumbnail_id', $ids));
        if ($bilder) update_meta_cache('post', array_values(array_unique($bilder)));
    }
    return $ids;
}

/**
 * Wie oft jedes Foto unter den sichtbaren Inhalten Beitragsbild ist, nach Foto-Schlüssel
 * (fotoschluessel.php): Abgleich- und Pool-Anhang desselben Fotos zählen zusammen.
 * $neu verwirft den Zwischenstand.
 */
function &ma_bildpool_nutzung(bool $neu = false): array {
    static $n = null;
    if ($n === null || $neu) {
        $n = [];
        foreach (ma_bildpool_sichtbar() as $id) { $b = (int) get_post_thumbnail_id($id); if ($b) { $k = ma_foto_schluessel($b); $n[$k] = ($n[$k] ?? 0) + 1; } }
    }
    return $n;
}

/**
 * Geprüftes Foto des Pools, das unter den sichtbaren Inhalten am seltensten
 * vorkommt (bei Gleichstand wie bisher ab Stelle post_id % n). Mit $grenze nur
 * Fotos, die seltener als $grenze vorkommen. 0 = keines.
 */
function ma_bildpool_waehlen(string $pool, int $post_id, int $grenze = PHP_INT_MAX): int {
    $ids = ma_bildpool_kandidaten($pool);
    $n = count($ids);
    if (!$n) return 0;
    $nutzung = ma_bildpool_nutzung();
    $best = 0; $bestZahl = PHP_INT_MAX;
    for ($i = 0; $i < $n; $i++) {
        $k = $ids[($post_id + $i) % $n]; $z = $nutzung[ma_foto_schluessel($k)] ?? 0;
        if ($z < $grenze && $z < $bestZahl) { $best = $k; $bestZahl = $z; }
    }
    return $best;
}

/**
 * Ersatzbild für einen Inhalt: zuerst ein seltenes Foto des eigenen Pools, dann
 * des übergeordneten Pools (Termine: „termine“, Meldungen: „aktuell“), erst dann
 * das seltenste Foto ohne Grenze. Detailpools haben oft nur ein oder vier
 * geprüfte Fotos.
 */
function ma_bildpool_ersatz(string $pool, WP_Post $p): int {
    $ober = $p->post_type === 'ma_event' ? 'termine' : 'aktuell';
    foreach (array_unique([$pool, $ober]) as $q) if ($b = ma_bildpool_waehlen($q, $p->ID, MA_BILDPOOL_GRENZE)) return $b;
    return ma_bildpool_waehlen($pool, $p->ID) ?: ma_bildpool_waehlen($ober, $p->ID) ?: ma_bildpool_waehlen('aktuell', $p->ID);
}

/** Poolfoto und Nachweis am Inhalt setzen (Typ Symbolbild, Merkmal _ma_bildpool_auto = vorgesehener Pool). */
/** Bildangaben am Inhalt, die ein Poolfoto überschreibt (und die zurückkommen, wenn danach das eigentliche Bild gesetzt wird). */
const MA_BILDPOOL_BILDFELDER = ['ma_image_credit', 'ma_image_license', 'ma_image_original_url', 'ma_image_type', 'ma_image_rights_verified'];

/** Setzt das Plugin gerade selbst ein Beitragsbild? (Dann lässt ma_bildpool_merkmal_pruefen das Merkmal stehen.) */
function ma_bildpool_setzt_selbst(?bool $an = null): bool {
    static $selbst = false;
    if ($an !== null) $selbst = $an;
    return $selbst;
}

function ma_bildpool_bild_setzen(WP_Post $p, int $bild, string $pool): void {
    // Erstes Poolfoto an diesem Inhalt: bisherige Bildangaben sichern. Abgleich und Block-Editor setzen das
    // eigentliche Bild erst nach dem Veröffentlichen; dann kommen sie zurück (Review 17.10.2026).
    if ((string) get_post_meta($p->ID, '_ma_bildpool_auto', true) === '') {
        $vorher = [];
        foreach (MA_BILDPOOL_BILDFELDER as $k) $vorher[$k] = (string) get_post_meta($p->ID, $k, true);
        update_post_meta($p->ID, '_ma_bildpool_vorher', $vorher);
    }
    ma_bildpool_setzt_selbst(true);
    set_post_thumbnail($p->ID, $bild);
    ma_bildpool_setzt_selbst(false);
    foreach (['ma_image_credit', 'ma_image_license', 'ma_image_original_url'] as $k) {
        $v = (string) get_post_meta($bild, $k, true);
        $v !== '' ? update_post_meta($p->ID, $k, $v) : delete_post_meta($p->ID, $k);
    }
    update_post_meta($p->ID, 'ma_image_type', 'symbol');
    update_post_meta($p->ID, 'ma_image_rights_verified', '1');
    update_post_meta($p->ID, '_ma_bildpool_auto', $pool);
    update_post_meta($p->ID, '_ma_bildpool_auto_bild', (string) $bild);
}

/**
 * Trägt der Inhalt noch das automatisch vergebene Poolfoto? Ersetzt jemand das Bild
 * (Redaktion, Editor nach dem Veröffentlichen, Änderungsvorschlag, Abgleich), ist das
 * Merkmal veraltet und das Bild bleibt unangetastet (Review 17.10.2026).
 * Vor 1.29.0 gesetzte Merkmale ohne gemerktes Bild: nur wenn das Bild im Bildpool liegt
 * (ma_bildpool_merkmale_nachtragen() trägt das gemerkte Bild dann einmal nach).
 */
function ma_bildpool_auto_aktuell(int $id): bool {
    if ((string) get_post_meta($id, '_ma_bildpool_auto', true) === '') return false;
    $bild = (int) get_post_thumbnail_id($id);
    if (!$bild) return false;
    $gemerkt = (int) get_post_meta($id, '_ma_bildpool_auto_bild', true);
    return $gemerkt ? $bild === $gemerkt : has_term('', MA_BILDPOOL_TAX, $bild);
}

function ma_bildpool_merkmal_loeschen(int $id): void {
    delete_post_meta($id, '_ma_bildpool_auto');
    delete_post_meta($id, '_ma_bildpool_auto_bild');
    delete_post_meta($id, '_ma_bildpool_vorher');
    delete_post_meta($id, '_ma_bildpool_verteilt');
    delete_post_meta($id, '_ma_bildpool_ersetzt');
}

/**
 * Jemand anderes als das Plugin setzt ein Beitragsbild (Redaktion, Editor, Abgleich,
 * Änderungsvorschlag): Das Merkmal fällt weg, auch bei einem anderen Poolfoto, und die
 * vor dem Poolfoto gesicherten Bildangaben kommen zurück.
 */
function ma_bildpool_merkmal_pruefen($meta_id, $post_id, $key, $wert): void {
    if ($key !== '_thumbnail_id' || ma_bildpool_setzt_selbst()) return;
    $id = (int) $post_id; $neu = (int) $wert;
    // Wer das Bild setzt: der Abgleich (ma_abgleich_setzt_bild) oder jemand von Hand. Von Hand gewählte Bilder
    // verteilt ma_bildpool_verteilen nie neu, auch wenn sie aus dem Abgleich stammen (Review 18.10.2026).
    if (function_exists('ma_abgleich_setzt_bild') && ma_abgleich_setzt_bild()) delete_post_meta($id, '_ma_bild_von_hand');
    else update_post_meta($id, '_ma_bild_von_hand', '1');
    if ((string) get_post_meta($id, '_ma_bildpool_auto', true) !== '' && $neu !== (int) get_post_meta($id, '_ma_bildpool_auto_bild', true)) {
        $vorher = get_post_meta($id, '_ma_bildpool_vorher', true);
        if (is_array($vorher)) foreach (MA_BILDPOOL_BILDFELDER as $k) { $v = (string) ($vorher[$k] ?? ''); $v !== '' ? update_post_meta($id, $k, $v) : delete_post_meta($id, $k); }
        ma_bildpool_merkmal_loeschen($id);
    }
    // Ein Poolfoto ist immer ein Symbolbild, auch wenn die Redaktion es selbst wählt.
    if ($neu && has_term('', MA_BILDPOOL_TAX, $neu)) update_post_meta($id, 'ma_image_type', 'symbol');
}

/**
 * Merkmale von vor 1.29.0 tragen kein gemerktes Bild. Trägt der Inhalt noch ein Poolfoto,
 * gilt es als das automatisch vergebene; sonst fällt das Merkmal weg. Danach erkennt
 * ma_bildpool_merkmal_pruefen jeden Bildwechsel. Gibt die Zahl der Nachträge zurück.
 */
function ma_bildpool_merkmale_nachtragen(): int {
    $n = 0;
    foreach (get_posts(['post_type' => ['post', 'ma_event'], 'post_status' => 'any', 'posts_per_page' => 2000, 'fields' => 'ids', 'no_found_rows' => true,
        'meta_query' => [['key' => '_ma_bildpool_auto', 'compare' => 'EXISTS'], ['key' => '_ma_bildpool_auto_bild', 'compare' => 'NOT EXISTS']]]) as $id) {
        $id = (int) $id; $bild = (int) get_post_thumbnail_id($id);
        if (!$bild) continue;
        if (has_term('', MA_BILDPOOL_TAX, $bild)) { update_post_meta($id, '_ma_bildpool_auto_bild', (string) $bild); $n++; }
        else ma_bildpool_merkmal_loeschen($id);
    }
    return $n;
}
// Priorität 5: vor ma_medium_beitragsbild_gesetzt (sport-bilder-admin.php, 10), das leere Bildangaben aus dem neuen
// Medium füllt. Erst die Sicherung zurück, dann füllt das neue Bild, was dort leer ist.
add_action('added_post_meta', 'ma_bildpool_merkmal_pruefen', 5, 4);
add_action('updated_post_meta', 'ma_bildpool_merkmal_pruefen', 5, 4);

/** Setzt ein Poolfoto als Beitragsbild (nur ohne eigenes Bild). Gibt die Medien-ID zurück. */
function ma_bildpool_symbolbild_setzen(WP_Post $p): int {
    if (has_post_thumbnail($p->ID) || !in_array($p->post_type, ['post', 'ma_event'], true)) return 0;
    $pool = ma_bildpool_fuer($p);
    $bild = ma_bildpool_ersatz($pool, $p);
    if (!$bild) return 0;
    ma_bildpool_bild_setzen($p, $bild, $pool);
    $nutzung = &ma_bildpool_nutzung(); $k = ma_foto_schluessel($bild); $nutzung[$k] = ($nutzung[$k] ?? 0) + 1;
    if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($p->ID, 'Symbolbild aus dem Bildpool „' . (ma_bildpool_labels()[$pool] ?? $pool) . '“ gesetzt (kein eigenes Bild)', '', 0);
    return $bild;
}

/**
 * Darf das Bild dieses Inhalts neu verteilt werden? Ja beim automatisch vergebenen
 * Poolfoto und (1.30.0) bei allgemeinen Bildern, die der Abgleich gesetzt hat:
 * Symbolbild oder Ortsansicht (Typ am Inhalt), Anhang aus der Import-Datei
 * (ma_image_static_src), nie von Hand gesetzt (_ma_bild_von_hand). Nie bei
 * Originalfotos, Quellenmotiven, Archivbildern oder von der Redaktion gewählten Bildern.
 */
function ma_bildpool_allgemein(int $id): bool {
    if (ma_bildpool_auto_aktuell($id)) return true;
    $bild = (int) get_post_thumbnail_id($id);
    if (!$bild || (string) get_post_meta($bild, 'ma_image_static_src', true) === '') return false;
    if ((string) get_post_meta($id, '_ma_bild_von_hand', true) === '1') return false;
    // Nur der Typ am Inhalt zählt: Ein von der Redaktion gewähltes Import-Bild hat dort meist keinen.
    return in_array((string) get_post_meta($id, 'ma_image_type', true), ['symbol', 'place'], true);
}

/**
 * Am seltensten genutztes geprüftes Foto aus $pool, bei Gleichstand das am längsten
 * nicht mehr vergebene (kleinste Stelle in $zuletzt, nie vergeben zuerst), dann ab
 * Stelle post_id % n. Gibt [Medien-ID, Zahl] zurück, [0, PHP_INT_MAX] ohne Foto.
 */
function ma_bildpool_seltenstes(string $pool, int $post_id, array $zahl, array $zuletzt): array {
    $ids = ma_bildpool_kandidaten($pool);
    $n = count($ids);
    $best = [0, PHP_INT_MAX, PHP_INT_MAX];
    for ($i = 0; $i < $n; $i++) {
        $b = $ids[($post_id + $i) % $n]; $k = ma_foto_schluessel($b);
        $z = $zahl[$k] ?? 0; $l = $zuletzt[$k] ?? -1;
        if ($z < $best[1] || ($z === $best[1] && $l < $best[2])) $best = [$b, $z, $l];
    }
    return [$best[0], $best[1]];
}

/**
 * Allgemeine Bilder neu verteilen (17.10.2026, seit 1.30.0 über alle Meldungen und
 * auch für Symbolbilder und Ortsansichten aus dem Abgleich): Von neu nach alt behält
 * jeder Inhalt sein Foto, solange es höchstens MA_BILDPOOL_GRENZE-mal vorkommt. Sonst
 * bekommt er das seltenste geprüfte Foto seines Pools (bei Gleichstand das am längsten
 * nicht vergebene), vom übergeordneten Pool (Termine: „termine“, Meldungen: „aktuell“)
 * nur, wenn der eigene kein Foto unter der Grenze mehr hat und es dort ein selteneres gibt.
 * Ortsansichten behalten so die zwei neuesten Meldungen. Jeder Inhalt wechselt höchstens
 * einmal (_ma_bildpool_verteilt). Was nicht verteilt werden darf (ma_bildpool_allgemein), steht fest und
 * zählt zuerst. Gibt die Zahl der Wechsel zurück; jeder steht im Verlauf.
 */
function ma_bildpool_verteilen(): int {
    ma_bildpool_merkmale_nachtragen();
    $nutzung = &ma_bildpool_nutzung(true);
    $alle = get_posts(['post_type' => ['post', 'ma_event'], 'post_status' => 'publish', 'posts_per_page' => 2400, 'no_found_rows' => true, 'orderby' => ['date' => 'DESC', 'ID' => 'DESC'],
        'post__in' => ma_bildpool_sichtbar() ?: [0]]);
    // Fest: was nicht verteilt werden darf, und was die Verteilung schon einmal gesetzt hat. Jeder Inhalt
    // wechselt so höchstens einmal; sonst schöben neue Meldungen in vollen Pools täglich Dutzende ältere
    // weiter (Review 18.10.2026).
    $zahl = []; $frei = [];
    foreach ($alle as $i => $p) {
        // Veraltetes Merkmal (Bild inzwischen ersetzt): entfernen, das Bild bleibt.
        if ((string) get_post_meta($p->ID, '_ma_bildpool_auto', true) !== '' && !ma_bildpool_auto_aktuell($p->ID) && get_post_thumbnail_id($p->ID)) ma_bildpool_merkmal_loeschen($p->ID);
        $bild = (int) get_post_thumbnail_id($p->ID);
        if (!$bild) continue;
        if (ma_bildpool_allgemein($p->ID) && (string) get_post_meta($p->ID, '_ma_bildpool_verteilt', true) === '') { $frei[$i] = true; continue; }
        $k = ma_foto_schluessel($bild); $zahl[$k] = ($zahl[$k] ?? 0) + 1;
    }
    $gesehen = []; $zuletzt = []; $wechsel = 0;
    foreach ($alle as $i => $p) {
        $bild = (int) get_post_thumbnail_id($p->ID);
        if (!$bild) continue;
        $k = ma_foto_schluessel($bild);
        if (empty($frei[$i])) { $zuletzt[$k] = $i; continue; }
        // Bisher vergeben: feste Fotos ganz, freie nur bei neueren Inhalten.
        $jetzt = $zahl[$k] ?? 0;
        if ($jetzt < MA_BILDPOOL_GRENZE) { $zahl[$k] = $jetzt + 1; $zuletzt[$k] = $i; continue; }
        $pool = (ma_bildpool_auto_aktuell($p->ID) ? (string) get_post_meta($p->ID, '_ma_bildpool_auto', true) : '') ?: ma_bildpool_fuer($p);
        $ober = $p->post_type === 'ma_event' ? 'termine' : 'aktuell';
        [$neu, $z] = ma_bildpool_seltenstes($pool, $p->ID, $zahl, $zuletzt);
        // Der übergeordnete Pool nur, wenn der eigene kein Foto unter der Grenze mehr hat (sonst bekäme eine Ratssitzung ein Kirmesfoto).
        if ($ober !== $pool && $z >= MA_BILDPOOL_GRENZE) { [$neuO, $zO] = ma_bildpool_seltenstes($ober, $p->ID, $zahl, $zuletzt); if ($neuO && $zO < $z) { $neu = $neuO; $z = $zO; } }
        // Nur wechseln, wenn das neue Foto seltener ist als das bisherige.
        if (!$neu || $z >= $jetzt || ma_foto_schluessel($neu) === $k) { $zahl[$k] = $jetzt + 1; $zuletzt[$k] = $i; continue; }
        $typVorher = (string) get_post_meta($p->ID, 'ma_image_type', true);
        // Ein Bild des Abgleichs weicht: Dann liefert der Abgleich es nicht stündlich zurück (ma_abgleich_bild_fehlt).
        if (!ma_bildpool_auto_aktuell($p->ID)) update_post_meta($p->ID, '_ma_bildpool_ersetzt', $k);
        ma_bildpool_bild_setzen($p, $neu, $pool);
        update_post_meta($p->ID, '_ma_bildpool_verteilt', '1');
        $kn = ma_foto_schluessel($neu);
        $zahl[$kn] = ($zahl[$kn] ?? 0) + 1; $zuletzt[$kn] = $i;
        $nutzung[$k] = max(0, ($nutzung[$k] ?? 1) - 1); $nutzung[$kn] = ($nutzung[$kn] ?? 0) + 1;
        $wechsel++;
        if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($p->ID, ($typVorher === 'place' ? 'Ortsansicht' : 'Symbolbild') . ' gewechselt: dasselbe Foto stand schon bei ' . MA_BILDPOOL_GRENZE . ' neueren Inhalten; neu ist ein Symbolbild aus dem Bildpool (vorher Medium ' . $bild . ', jetzt ' . $neu . ')', '', 0);
    }
    return $wechsel;
}
// Täglich, aber nur bei eingeschalteter Automatik (wie das Setzen beim Veröffentlichen); der Knopf geht immer.
add_action('ma_bildpool_verteilen_taeglich', function (): void { if (get_option('ma_bildpool_auto', '1') === '1') ma_bildpool_verteilen(); });
add_action('init', function (): void {
    if (function_exists('wp_next_scheduled') && !wp_next_scheduled('ma_bildpool_verteilen_taeglich')) wp_schedule_event(time() + 900, 'daily', 'ma_bildpool_verteilen_taeglich');
});

add_action('transition_post_status', function (string $neu, string $alt, $p): void {
    if (!$p instanceof WP_Post || $neu !== 'publish' || $alt === 'publish' || get_option('ma_bildpool_auto', '1') !== '1') return;
    // WXR-Import: Das Beitragsbild kommt erst nach dem Anlegen als Meta, also hier nichts setzen.
    if (defined('WP_IMPORTING') && WP_IMPORTING) return;
    // Kopie eines Änderungsvorschlags: Das Bild kommt vom Original (redaktion.php).
    if (defined('MA_AENDERUNG_VON') && get_post_meta($p->ID, MA_AENDERUNG_VON, true)) return;
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
        // Bild inzwischen ersetzt (Redaktion, Editor): nie löschen, nur das veraltete Merkmal entfernen.
        if (get_post_thumbnail_id($p->ID) && !ma_bildpool_auto_aktuell($p->ID)) { ma_bildpool_merkmal_loeschen($p->ID); continue; }
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
/* Einmal je Plugin-Version neu verteilen (der tägliche Lauf hält es danach). */
add_action('admin_init', function (): void {
    if (get_option('ma_bildpool_auto', '1') !== '1' || get_option('ma_bildpool_verteilt') === MA_CORE_VERSION) return;
    update_option('ma_bildpool_verteilt', MA_CORE_VERSION, false);
    ma_bildpool_verteilen();
}, 20);

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
