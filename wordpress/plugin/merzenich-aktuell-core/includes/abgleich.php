<?php
/**
 * Abgleich mit dem redaktionellen Stand (includes/abgleich.php, Plugin 1.20.0).
 *
 * Die Meldungen entstehen auf der Vorschauseite (Generatoren unter deploy/);
 * die CI schreibt daraus wordpress-delivery/merzenich-aktuell-import.xml nach
 * main. Bis 1.19 musste diese Datei von Hand importiert werden, und auf
 * merzenich-aktuell.de blieb der Stand tagelang stehen: Google sah keine
 * aktuelle Nachrichtenseite, die News-Sitemap war leer.
 *
 * Dieses Modul liest die Import-Datei stündlich (WP-Cron) oder auf Knopfdruck
 * (Merzenich Aktuell → Abgleich) und
 *   - legt neue Meldungen als Entwurf in die Freigabe (Option „sofort
 *     veröffentlichen“: direkt veröffentlicht) und neue Termine veröffentlicht an,
 *   - übernimmt das Beitragsbild mit Nachweis, Lizenz und Rechteprüfung,
 *   - aktualisiert bestehende Beiträge nur, wenn seit dem letzten Abgleich
 *     niemand Titel, Text oder Auszug von Hand geändert hat; Status, Freigaben,
 *     Hervorhebungen und ein von Hand gesetztes Bild fasst der Abgleich nie an,
 *   - löscht nichts.
 * Zuordnung über ma_legacy_url (setzt der Import), ersatzweise über den Slug.
 * Bilder kommen von der Vorschauseite; ist sie nicht erreichbar, vom Repository.
 */
if (!defined('ABSPATH')) { exit; }

const MA_ABGLEICH_WXR = 'https://raw.githubusercontent.com/HKGrowthOperator/Merzenich-Aktuell/main/wordpress-delivery/merzenich-aktuell-import.xml';
const MA_ABGLEICH_REPO_SEITE = 'https://raw.githubusercontent.com/HKGrowthOperator/Merzenich-Aktuell/main/chatgpt-site';
const MA_ABGLEICH_TYPEN = ['post', 'ma_event'];
const MA_ABGLEICH_CRON = 'ma_abgleich_stuendlich';

function ma_abgleich_quelle(): string { $q = trim((string) get_option('ma_abgleich_quelle', '')); return $q !== '' ? $q : MA_ABGLEICH_WXR; }
function ma_abgleich_aktiv(): bool { return get_option('ma_abgleich_aktiv', 'ja') !== 'nein'; }
function ma_abgleich_sofort(): bool { return get_option('ma_abgleich_sofort', 'nein') === 'ja'; }

/** Basis-Adressen für Bilder (ma_image_static_src beginnt mit /assets/…), in Reihenfolge der Versuche. */
function ma_abgleich_bildbasen(): array {
    $b = [];
    $eigene = trim((string) get_option('ma_abgleich_bilder', ''));
    if ($eigene !== '') $b[] = rtrim($eigene, '/');
    if (function_exists('ma_bildpool_quelle')) $b[] = ma_bildpool_quelle();
    $b[] = MA_ABGLEICH_REPO_SEITE;
    return array_values(array_unique($b));
}

/* ---------------------------------------------------------- Lesen (ohne WordPress prüfbar) */

function ma_abgleich_hash(array $e, string $bildSrc): string {
    return sha1(json_encode([$e['titel'], $e['inhalt'], $e['auszug'], $e['datum_gmt'], $e['terme'], $e['meta'], $bildSrc], JSON_UNESCAPED_UNICODE));
}

function ma_abgleich_texthash(string $titel, string $inhalt, string $auszug): string {
    return sha1(trim($titel) . "\n" . trim($inhalt) . "\n" . trim($auszug));
}

/**
 * Liest die Import-Datei (WXR 1.2): Meldungen und Termine als Einträge, Anhänge
 * je Import-ID. Leer, wenn die Datei nicht lesbar ist.
 * @return array{stand:string, eintraege:array<int,array>, bilder:array<int,array>}
 */
function ma_abgleich_lesen(string $xml): array {
    $aus = ['stand' => '', 'eintraege' => [], 'bilder' => []];
    if (preg_match('/aus dem statischen Stand (\S+?)\.(?:\s|$)/m', $xml, $m)) $aus['stand'] = $m[1];
    $alt = libxml_use_internal_errors(true);
    $x = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
    libxml_use_internal_errors($alt);
    if (!$x || !isset($x->channel)) return $aus;
    $NS_WP = 'http://wordpress.org/export/1.2/'; $NS_C = 'http://purl.org/rss/1.0/modules/content/'; $NS_E = 'http://wordpress.org/export/1.2/excerpt/';
    foreach ($x->channel->item as $item) {
        $wp = $item->children($NS_WP);
        $typ = (string) $wp->post_type;
        $meta = [];
        foreach ($wp->postmeta as $pm) $meta[(string) $pm->meta_key] = (string) $pm->meta_value;
        if ($typ === 'attachment') {
            $aus['bilder'][(int) $wp->post_id] = ['url' => (string) $wp->attachment_url, 'titel' => (string) $item->title, 'auszug' => (string) $item->children($NS_E)->encoded, 'src' => (string) ($meta['ma_image_static_src'] ?? ''), 'alt' => (string) ($meta['_wp_attachment_image_alt'] ?? ''), 'meta' => $meta];
            continue;
        }
        if (!in_array($typ, MA_ABGLEICH_TYPEN, true)) continue;
        $terme = [];
        foreach ($item->category as $c) $terme[] = ['tax' => (string) $c['domain'], 'slug' => (string) $c['nicename'], 'name' => (string) $c];
        $bild = (int) ($meta['_thumbnail_id'] ?? 0);
        unset($meta['_thumbnail_id']);
        ksort($meta);
        $aus['eintraege'][] = ['typ' => $typ, 'legacy' => (string) ($meta['ma_legacy_url'] ?? ''), 'slug' => (string) $wp->post_name, 'titel' => (string) $item->title, 'inhalt' => (string) $item->children($NS_C)->encoded, 'auszug' => (string) $item->children($NS_E)->encoded, 'datum' => (string) $wp->post_date, 'datum_gmt' => (string) $wp->post_date_gmt, 'status' => (string) $wp->status ?: 'draft', 'kommentare' => (string) $wp->comment_status ?: 'closed', 'terme' => $terme, 'meta' => $meta, 'bild' => $bild];
    }
    foreach ($aus['eintraege'] as &$e) $e['hash'] = ma_abgleich_hash($e, (string) ($aus['bilder'][$e['bild']]['src'] ?? ''));
    unset($e);
    return $aus;
}

/**
 * Entscheidung für einen bestehenden Beitrag: 'uebernehmen' (noch nie abgeglichen,
 * nur Stand merken), 'unveraendert', 'aktualisieren' oder 'von_hand' (Titel, Text
 * oder Auszug wurden seit dem letzten Abgleich in WordPress geändert).
 */
function ma_abgleich_entscheidung(string $hashNeu, string $hashAlt, string $textAlt, string $textJetzt): string {
    if ($hashAlt === '') return 'uebernehmen';
    if ($hashAlt === $hashNeu) return 'unveraendert';
    return $textAlt === $textJetzt ? 'aktualisieren' : 'von_hand';
}

/** Status eines neuen Beitrags: Meldungen als Entwurf in die Freigabe oder (Option) sofort veröffentlicht, Termine wie geliefert. */
function ma_abgleich_status(array $e, bool $sofort): string {
    if ($e['typ'] === 'post') return $sofort ? 'publish' : 'draft';
    return in_array($e['status'], ['publish', 'draft', 'pending'], true) ? $e['status'] : 'draft';
}

/* ---------------------------------------------------------- Schreiben */

/** Beiträge, die der Abgleich kennt (auch im Papierkorb: Verworfenes kommt nicht wieder): nach ma_legacy_url und nach Typ|Slug. */
function ma_abgleich_vorhandene(): array {
    global $wpdb;
    $rows = $wpdb->get_results("SELECT p.ID, p.post_type, p.post_name, m.meta_value AS legacy FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = 'ma_legacy_url' WHERE p.post_type IN ('post', 'ma_event') AND p.post_status <> 'auto-draft'", ARRAY_A);
    $legacy = []; $slug = [];
    foreach ((array) $rows as $r) {
        if (!empty($r['legacy']) && !isset($legacy[$r['legacy']])) $legacy[$r['legacy']] = (int) $r['ID'];
        if ($r['post_name'] !== '') $slug[$r['post_type'] . '|' . $r['post_name']] = (int) $r['ID'];
    }
    return ['legacy' => $legacy, 'slug' => $slug];
}

function ma_abgleich_autor(): int {
    $u = get_user_by('login', 'redaktion');
    if ($u && user_can($u, 'edit_posts')) return (int) $u->ID;
    $ids = get_users(['role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ID']);
    return (int) ($ids[0] ?? 1);
}

function ma_abgleich_term_id(string $tax, string $slug, string $name): int {
    if ($slug === '' || !taxonomy_exists($tax)) return 0;
    $t = get_term_by('slug', $slug, $tax);
    if ($t instanceof WP_Term) return (int) $t->term_id;
    $r = wp_insert_term($name !== '' ? $name : $slug, $tax, ['slug' => $slug]);
    if (is_wp_error($r)) return (int) ($r->get_error_data('term_exists') ?: 0);
    return (int) $r['term_id'];
}

function ma_abgleich_terme_setzen(int $id, array $terme): void {
    $je = [];
    foreach ($terme as $t) { $tid = ma_abgleich_term_id((string) $t['tax'], (string) $t['slug'], (string) $t['name']); if ($tid) $je[$t['tax']][] = $tid; }
    foreach ($je as $tax => $ids) wp_set_object_terms($id, $ids, $tax, false);
}

/** Medium zu einem Bild der Import-Datei: vorhanden (ma_image_static_src) oder laden. 0 bei Fehler. */
function ma_abgleich_bild(array $b, int $post_id, string &$fehler = ''): int {
    $src = (string) ($b['src'] ?? '');
    if ($src !== '') {
        $ids = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => 'ma_image_static_src', 'meta_value' => $src]);
        if (!empty($ids)) return (int) $ids[0];
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $kandidaten = [];
    if (!empty($b['url'])) $kandidaten[] = (string) $b['url'];
    if ($src !== '') foreach (ma_abgleich_bildbasen() as $basis) $kandidaten[] = $basis . $src;
    $tmp = null; $gruende = []; $genutzt = '';
    foreach (array_unique($kandidaten) as $url) {
        $t = download_url($url, 30);
        if (!is_wp_error($t)) { $tmp = $t; $genutzt = $url; break; }
        $gruende[] = $url . ': ' . $t->get_error_message();
    }
    if ($tmp === null) { $fehler = $gruende ? implode(' | ', $gruende) : 'keine Bildadresse'; return 0; }
    $name = sanitize_file_name(basename((string) parse_url($src !== '' ? $src : $genutzt, PHP_URL_PATH)));
    $id = media_handle_sideload(['name' => $name ?: 'bild.jpg', 'tmp_name' => $tmp], $post_id, (string) ($b['alt'] ?: $b['titel']), ['post_excerpt' => (string) $b['auszug']]);
    if (is_wp_error($id)) { @unlink($tmp); $fehler = $id->get_error_message(); return 0; }
    foreach ((array) $b['meta'] as $k => $v) if ($v !== '') update_post_meta($id, $k, $v);
    if ($src !== '') update_post_meta($id, 'ma_image_static_src', $src);
    if (!empty($b['meta']['ma_image_credit'])) update_post_meta($id, 'ma_credit', $b['meta']['ma_image_credit']);
    if (!empty($b['meta']['ma_image_license'])) update_post_meta($id, 'ma_nutzung', $b['meta']['ma_image_license']);
    if (!empty($b['meta']['ma_image_original_url'])) update_post_meta($id, 'ma_quelle', $b['meta']['ma_image_original_url']);
    $geprueft = ($b['meta']['ma_image_rights_verified'] ?? '') === '1';
    update_post_meta($id, 'ma_rechtepruefung', $geprueft ? 'geprueft' : 'offen');
    if ($geprueft) update_post_meta($id, 'ma_rechtepruefung_von', ['u' => 0, 't' => time(), 'grund' => 'Rechte laut Import-Datei geprüft' . (!empty($b['meta']['ma_image_provenance']) ? ' (' . $b['meta']['ma_image_provenance'] . ')' : '')]);
    return (int) $id;
}

function ma_abgleich_bild_setzen(int $id, array $e, array $bilder, array &$fehler): void {
    if (!$e['bild'] || empty($bilder[$e['bild']])) return;
    $f = '';
    $m = ma_abgleich_bild($bilder[$e['bild']], $id, $f);
    if ($m) set_post_thumbnail($id, $m); else $fehler[] = $e['titel'] . ' (Bild): ' . $f;
}

function ma_abgleich_stand_merken(int $id, array $e): void {
    $p = get_post($id);
    if (!$p) return;
    update_post_meta($id, '_ma_abgleich_hash', $e['hash']);
    update_post_meta($id, '_ma_abgleich_text', ma_abgleich_texthash($p->post_title, $p->post_content, $p->post_excerpt));
    update_post_meta($id, '_ma_abgleich_zeit', current_time('mysql', true));
}

function ma_abgleich_anlegen(array $e, array $bilder, array &$fehler): int {
    $daten = ['post_type' => $e['typ'], 'post_status' => ma_abgleich_status($e, ma_abgleich_sofort()), 'post_author' => ma_abgleich_autor(), 'post_title' => $e['titel'], 'post_content' => $e['inhalt'], 'post_excerpt' => $e['auszug'], 'post_name' => $e['slug'], 'post_date' => $e['datum'], 'post_date_gmt' => $e['datum_gmt'], 'comment_status' => $e['kommentare'], 'ping_status' => 'closed', 'meta_input' => $e['meta']];
    if ($e['datum'] === '') unset($daten['post_date'], $daten['post_date_gmt']);
    $id = wp_insert_post(wp_slash($daten), true);
    if (is_wp_error($id) || !$id) { $fehler[] = $e['titel'] . ': ' . (is_wp_error($id) ? $id->get_error_message() : 'nicht angelegt'); return 0; }
    ma_abgleich_terme_setzen($id, $e['terme']);
    ma_abgleich_bild_setzen($id, $e, $bilder, $fehler);
    ma_abgleich_stand_merken($id, $e);
    if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Abgleich', 'Aus dem redaktionellen Stand übernommen' . ($daten['post_status'] === 'draft' ? ' (wartet auf Freigabe)' : ''), 0);
    return (int) $id;
}

/** Aktualisiert Titel, Text, Auszug, Schlagworte und Importfelder; Status und Freigaben bleiben. */
function ma_abgleich_aktualisieren(int $id, array $e, array $bilder, array &$fehler): bool {
    $meta = $e['meta'];
    unset($meta['ma_startseite_freigabe']);
    $r = wp_update_post(wp_slash(['ID' => $id, 'post_title' => $e['titel'], 'post_content' => $e['inhalt'], 'post_excerpt' => $e['auszug'], 'meta_input' => $meta]), true);
    if (is_wp_error($r) || !$r) { $fehler[] = $e['titel'] . ': ' . (is_wp_error($r) ? $r->get_error_message() : 'nicht aktualisiert'); return false; }
    ma_abgleich_terme_setzen($id, $e['terme']);
    if ($e['bild'] && !has_post_thumbnail($id)) ma_abgleich_bild_setzen($id, $e, $bilder, $fehler);
    ma_abgleich_stand_merken($id, $e);
    if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Abgleich', 'Text aus dem redaktionellen Stand aktualisiert', 0);
    return true;
}

/**
 * Ein Lauf: Import-Datei holen, neue Beiträge anlegen (höchstens $max je Lauf,
 * neueste zuerst), bestehende prüfen. Ergebnis wird als letzter Stand gespeichert.
 */
function ma_abgleich_lauf(int $max = 20): array {
    $t0 = microtime(true);
    if (function_exists('set_time_limit')) @set_time_limit(300);
    $erg = ['zeit' => current_time('mysql'), 'quelle' => ma_abgleich_quelle(), 'stand' => '', 'neu' => 0, 'aktualisiert' => 0, 'unveraendert' => 0, 'uebernommen' => 0, 'verworfen' => 0, 'von_hand' => [], 'offen' => 0, 'fehler' => [], 'neue' => [], 'dauer' => 0.0];
    $antwort = wp_remote_get($erg['quelle'], ['timeout' => 30, 'user-agent' => 'Merzenich Aktuell Abgleich/' . MA_CORE_VERSION]);
    if (is_wp_error($antwort) || (int) wp_remote_retrieve_response_code($antwort) !== 200) {
        $erg['fehler'][] = 'Import-Datei nicht lesbar: ' . (is_wp_error($antwort) ? $antwort->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code($antwort));
        return ma_abgleich_abschliessen($erg, $t0);
    }
    $daten = ma_abgleich_lesen((string) wp_remote_retrieve_body($antwort));
    if (!$daten['eintraege']) { $erg['fehler'][] = 'Import-Datei ohne Meldungen (Format nicht erkannt)'; return ma_abgleich_abschliessen($erg, $t0); }
    $erg['stand'] = $daten['stand'];
    $vorhandene = ma_abgleich_vorhandene();
    $eintraege = $daten['eintraege'];
    usort($eintraege, fn($a, $b) => strcmp($b['datum_gmt'], $a['datum_gmt']));
    foreach ($eintraege as $e) {
        $id = (int) ($vorhandene['legacy'][$e['legacy']] ?? $vorhandene['slug'][$e['typ'] . '|' . $e['slug']] ?? 0);
        if (!$id) {
            if ($erg['neu'] >= $max) { $erg['offen']++; continue; }
            if (ma_abgleich_anlegen($e, $daten['bilder'], $erg['fehler'])) { $erg['neu']++; $erg['neue'][] = $e['titel']; }
            continue;
        }
        if ($e['legacy'] !== '' && empty($vorhandene['legacy'][$e['legacy']])) update_post_meta($id, 'ma_legacy_url', $e['legacy']);
        $p = get_post($id);
        if (!$p) continue;
        if ($p->post_status === 'trash') { $erg['verworfen']++; continue; }
        $ent = ma_abgleich_entscheidung($e['hash'], (string) get_post_meta($id, '_ma_abgleich_hash', true), (string) get_post_meta($id, '_ma_abgleich_text', true), ma_abgleich_texthash($p->post_title, $p->post_content, $p->post_excerpt));
        if ($ent === 'uebernehmen') { ma_abgleich_stand_merken($id, $e); $erg['uebernommen']++; }
        elseif ($ent === 'unveraendert') $erg['unveraendert']++;
        elseif ($ent === 'aktualisieren') { if (ma_abgleich_aktualisieren($id, $e, $daten['bilder'], $erg['fehler'])) $erg['aktualisiert']++; }
        else $erg['von_hand'][] = $e['titel'];
        // Fehlendes Beitragsbild nachholen, etwa wenn die Vorschauseite beim letzten Lauf nicht erreichbar war.
        if ($ent !== 'aktualisieren' && $ent !== 'von_hand' && $e['bild'] && !has_post_thumbnail($id)) ma_abgleich_bild_setzen($id, $e, $daten['bilder'], $erg['fehler']);
    }
    return ma_abgleich_abschliessen($erg, $t0);
}

function ma_abgleich_abschliessen(array $erg, float $t0): array {
    $erg['dauer'] = round(microtime(true) - $t0, 1);
    $erg['von_hand'] = array_slice($erg['von_hand'], 0, 30);
    $erg['neue'] = array_slice($erg['neue'], 0, 30);
    $erg['fehler'] = array_slice($erg['fehler'], 0, 30);
    update_option('ma_abgleich_stand', $erg, false);
    $protokoll = get_option('ma_abgleich_protokoll', []);
    if (!is_array($protokoll)) $protokoll = [];
    $protokoll[] = sprintf('%s: %d neu, %d aktualisiert, %d unverändert, %d von Hand geändert, %d offen, %d Fehler (%ss)', $erg['zeit'], $erg['neu'], $erg['aktualisiert'], $erg['unveraendert'] + $erg['uebernommen'], count($erg['von_hand']), $erg['offen'], count($erg['fehler']), $erg['dauer']);
    update_option('ma_abgleich_protokoll', array_slice($protokoll, -20), false);
    return $erg;
}

/* ---------------------------------------------------------- Zeitplan */

add_action('init', function (): void {
    if (!wp_next_scheduled(MA_ABGLEICH_CRON)) wp_schedule_event(time() + 5 * MINUTE_IN_SECONDS, 'hourly', MA_ABGLEICH_CRON);
});
add_action(MA_ABGLEICH_CRON, function (): void { if (ma_abgleich_aktiv()) ma_abgleich_lauf(); });
register_deactivation_hook(MA_CORE_PATH . 'merzenich-aktuell-core.php', function (): void { wp_clear_scheduled_hook(MA_ABGLEICH_CRON); });

/* ---------------------------------------------------------- Backend */

add_action('admin_menu', function (): void {
    add_submenu_page('merzenich-aktuell', 'Abgleich', 'Abgleich', 'manage_options', 'ma-abgleich', 'ma_abgleich_seite_admin', 2);
}, 12);

function ma_abgleich_seite_admin(): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
    $hinweis = ''; $ergebnis = null;
    if (isset($_POST['ma_abgleich_aktion']) && check_admin_referer('ma_abgleich')) {
        $aktion = sanitize_key((string) $_POST['ma_abgleich_aktion']);
        if ($aktion === 'einstellungen') {
            update_option('ma_abgleich_aktiv', isset($_POST['aktiv']) ? 'ja' : 'nein');
            update_option('ma_abgleich_sofort', isset($_POST['sofort']) ? 'ja' : 'nein');
            $q = esc_url_raw(trim((string) ($_POST['quelle'] ?? '')));
            update_option('ma_abgleich_quelle', $q === MA_ABGLEICH_WXR ? '' : $q);
            update_option('ma_abgleich_bilder', esc_url_raw(trim((string) ($_POST['bilder'] ?? ''))));
            $hinweis = 'Einstellungen gespeichert.';
        } elseif ($aktion === 'lauf') {
            $ergebnis = ma_abgleich_lauf(20);
            $hinweis = $ergebnis['fehler'] && !$ergebnis['neu'] ? 'Abgleich mit Fehlern, siehe unten.' : 'Abgleich gelaufen.';
        }
    }
    $stand = $ergebnis ?? get_option('ma_abgleich_stand', []);
    $naechster = wp_next_scheduled(MA_ABGLEICH_CRON);
    echo '<div class="wrap"><h1>Abgleich mit dem redaktionellen Stand</h1>';
    echo '<p>Die Meldungen entstehen auf der Vorschauseite. Dieser Abgleich holt sie stündlich nach WordPress: neue Meldungen landen als Entwurf in den <a href="' . esc_url(admin_url('admin.php?page=ma-freigaben')) . '">Freigaben</a>, neue Termine werden veröffentlicht, Bilder kommen mit Nachweis mit. Was hier von Hand geändert wurde, bleibt unangetastet.</p>';
    if ($hinweis) echo '<div class="notice notice-info"><p>' . esc_html($hinweis) . '</p></div>';
    echo '<form method="post" style="margin:1em 0">'; wp_nonce_field('ma_abgleich');
    echo '<input type="hidden" name="ma_abgleich_aktion" value="lauf"><button class="button button-primary">Jetzt abgleichen</button> ';
    echo '<span class="description">Nächster automatischer Lauf: ' . ($naechster ? esc_html(wp_date('d.m.Y H:i', $naechster)) . ' Uhr' : 'nicht geplant') . (ma_abgleich_aktiv() ? '' : ' (automatischer Abgleich ist aus)') . '</span></form>';
    if (is_array($stand) && $stand) {
        echo '<h2>Letzter Lauf</h2><table class="widefat striped" style="max-width:720px"><tbody>';
        foreach ([['Zeit', $stand['zeit'] ?? ''], ['Stand der Import-Datei', $stand['stand'] ?? ''], ['Neu angelegt', (string) ($stand['neu'] ?? 0)], ['Aktualisiert', (string) ($stand['aktualisiert'] ?? 0)], ['Unverändert', (string) (($stand['unveraendert'] ?? 0) + ($stand['uebernommen'] ?? 0))], ['Von Hand geändert, nicht überschrieben', (string) count($stand['von_hand'] ?? [])], ['Verworfen (Papierkorb, kommt nicht wieder)', (string) ($stand['verworfen'] ?? 0)], ['Noch offen (nächster Lauf)', (string) ($stand['offen'] ?? 0)], ['Dauer', ($stand['dauer'] ?? 0) . ' s']] as [$k, $v]) {
            echo '<tr><th style="width:280px">' . esc_html($k) . '</th><td>' . esc_html($v) . '</td></tr>';
        }
        echo '</tbody></table>';
        if (!empty($stand['neue'])) echo '<p><strong>Neu:</strong> ' . esc_html(implode(' · ', $stand['neue'])) . '</p>';
        if (!empty($stand['von_hand'])) echo '<p><strong>Von Hand geändert (bleiben so):</strong> ' . esc_html(implode(' · ', $stand['von_hand'])) . '</p>';
        if (!empty($stand['fehler'])) echo '<div class="notice notice-warning"><p><strong>Fehler:</strong><br>' . implode('<br>', array_map('esc_html', $stand['fehler'])) . '</p></div>';
        if (!empty($stand['offen'])) echo '<p>Es warten noch ' . (int) $stand['offen'] . ' Beiträge. Erneut „Jetzt abgleichen“ anklicken oder den nächsten automatischen Lauf abwarten.</p>';
    }
    echo '<h2>Einstellungen</h2><form method="post">'; wp_nonce_field('ma_abgleich');
    echo '<input type="hidden" name="ma_abgleich_aktion" value="einstellungen"><table class="form-table"><tbody>';
    echo '<tr><th>Automatischer Abgleich</th><td><label><input type="checkbox" name="aktiv" value="1"' . checked(ma_abgleich_aktiv(), true, false) . '> Stündlich laufen lassen</label></td></tr>';
    echo '<tr><th>Neue Meldungen</th><td><label><input type="checkbox" name="sofort" value="1"' . checked(ma_abgleich_sofort(), true, false) . '> Sofort veröffentlichen (ohne Freigabe; ausgeschaltet landen sie als Entwurf in den Freigaben)</label></td></tr>';
    echo '<tr><th><label for="ma-abgleich-quelle">Import-Datei</label></th><td><input type="url" class="large-text" id="ma-abgleich-quelle" name="quelle" value="' . esc_attr(ma_abgleich_quelle()) . '"><p class="description">Standard: die Datei im Repository auf GitHub (immer der Stand von main).</p></td></tr>';
    echo '<tr><th><label for="ma-abgleich-bilder">Bilder zuerst von</label></th><td><input type="url" class="large-text" id="ma-abgleich-bilder" name="bilder" value="' . esc_attr((string) get_option('ma_abgleich_bilder', '')) . '" placeholder="leer = Vorschauseite, dann Repository"><p class="description">Reihenfolge der Versuche: ' . esc_html(implode(' → ', ma_abgleich_bildbasen())) . '</p></td></tr>';
    echo '</tbody></table><p><button class="button">Einstellungen speichern</button></p></form>';
    $protokoll = get_option('ma_abgleich_protokoll', []);
    if (is_array($protokoll) && $protokoll) echo '<h2>Protokoll</h2><ul style="font-family:monospace">' . implode('', array_map(fn($z) => '<li>' . esc_html((string) $z) . '</li>', array_reverse($protokoll))) . '</ul>';
    echo '</div>';
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('ma-abgleich', new class {
        /** Ein Lauf. --max=<n> neue Beiträge je Lauf (Standard 20). */
        public function lauf(array $args, array $assoc): void {
            $e = ma_abgleich_lauf((int) ($assoc['max'] ?? 20));
            WP_CLI::log(sprintf('%d neu, %d aktualisiert, %d unverändert, %d übernommen, %d von Hand geändert, %d verworfen, %d offen, %ss', $e['neu'], $e['aktualisiert'], $e['unveraendert'], $e['uebernommen'], count($e['von_hand']), $e['verworfen'], $e['offen'], $e['dauer']));
            foreach ($e['neue'] as $t) WP_CLI::log('  neu: ' . $t);
            foreach ($e['fehler'] as $f) WP_CLI::warning($f);
            if ($e['fehler'] && !$e['neu'] && !$e['aktualisiert']) WP_CLI::error('Abgleich mit Fehlern.');
        }
        public function stand(): void { WP_CLI::log(json_encode(get_option('ma_abgleich_stand', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); }
    });
}
