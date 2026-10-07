<?php
/**
 * Stellen und Immobilien als echte Einträge im Backend (Wunsch Betreiber
 * 07.10.2026: „da ist gar nichts drin, das darf nicht sein“). Quelle ist der
 * geprüfte Marktstand im Repository (market.json, /api/market.json): die
 * Stellen prüft deploy/markt-abgleich.mjs täglich bei der Bundesagentur bzw.
 * der Karriereseite, die Immobilien tragen ihr letztes Prüfdatum.
 *
 * - Neue Angebote werden als Stelle (ma_job) bzw. Immobilie (ma_property)
 *   veröffentlicht, mit Quelle, Prüfdatum und „Anzeige endet“:
 *   Stellen 30 Tage, Immobilien 35 Tage nach der letzten Prüfung. Wer nicht
 *   erneut geprüft wird, verschwindet von selbst.
 * - Vorhandene Einträge (Kennung ma_markt_id) werden aktualisiert; von Hand
 *   geänderte Texte bleiben, nur Prüfdatum und Ende ziehen nach.
 * - Fällt ein Angebot aus dem Marktstand (Quelle entfernt), endet es heute.
 * - Marktübersichten (Suchseiten der Portale) sind keine Angebote und bleiben draußen.
 * - Es wird nie gelöscht; Bilder der Anbieter werden nie übernommen.
 *
 * Läuft einmal täglich mit dem Abgleich und auf Knopfdruck unter
 * Merzenich Aktuell → Abgleich („Stellen und Immobilien übernehmen“).
 */
if (!defined('ABSPATH')) { exit; }

const MA_MARKT_QUELLE = 'https://raw.githubusercontent.com/HKGrowthOperator/Merzenich-Aktuell/main/market.json';
const MA_MARKT_LAUFZEIT = ['ma_job' => 30, 'ma_property' => 35];
const MA_MARKT_ORTSTEILE = ['merzenich' => 'Merzenich', 'golzheim' => 'Golzheim', 'girbelsrath' => 'Girbelsrath', 'morschenich' => 'Morschenich', 'buergewald' => 'Bürgewald'];

/** Beschäftigungsart aus „Ausbildung · Vollzeit · Start …“ (Werte wie im Backend-Feld ma_job_type). */
function ma_markt_job_typ(string $art): string {
    $a = mb_strtolower($art);
    foreach (['ausbildung' => 'training', 'minijob' => 'minijob', 'aushilfe' => 'minijob', 'praktikum' => 'internship', 'teilzeit' => 'parttime', 'freiberuf' => 'freelance', 'honorar' => 'freelance'] as $wort => $typ) if (str_contains($a, $wort)) return $typ;
    return 'fulltime';
}

/** Ortsteil-Slug aus „Golzheim“, „Bürgewald“ …; leer, wenn außerhalb der Gemeinde. */
function ma_markt_ortsteil(string $municipality, string $district): string {
    if (mb_strtolower(trim($municipality)) !== 'merzenich') return '';
    $s = array_search(trim($district), MA_MARKT_ORTSTEILE, true);
    return $s !== false ? (string) $s : 'merzenich';
}

/** Prüfzeit „2026-10-06T13:18:31+02:00“ als Ortszeit „Y-m-d H:i:s“ (leer bei Unsinn). */
function ma_markt_zeit(string $iso): string {
    try { return $iso !== '' ? (new DateTimeImmutable($iso))->setTimezone(new DateTimeZone('Europe/Berlin'))->format('Y-m-d H:i:s') : ''; }
    catch (Exception $e) { return ''; }
}

/**
 * Ein Angebot aus market.json als Felder eines Eintrags (pure Funktion, testbar).
 * $typ: ma_job | ma_property. Gibt null zurück, wenn es kein Einzelangebot ist.
 */
function ma_markt_felder(array $e, string $typ): ?array {
    $id = trim((string) ($e['id'] ?? '')); $titel = trim((string) ($e['title'] ?? '')); $url = trim((string) ($e['sourceUrl'] ?? ''));
    if ($id === '' || $titel === '' || !preg_match('#^https://#', $url)) return null;
    if ($typ === 'ma_property' && preg_match('#/(suche|liste)/#', $url)) return null; // Marktübersicht, kein Angebot
    $geprueft = ma_markt_zeit((string) ($e['checkedAt'] ?? ''));
    if ($geprueft === '') return null;
    $ende = (new DateTimeImmutable($geprueft, new DateTimeZone('Europe/Berlin')))->modify('+' . MA_MARKT_LAUFZEIT[$typ] . ' days')->format('Y-m-d 23:59:00');
    $gemeinde = trim((string) ($e['municipality'] ?? '')); $teil = trim((string) ($e['district'] ?? ''));
    $lage = $teil !== '' && $teil !== $gemeinde ? $gemeinde . '-' . $teil : $gemeinde;
    $details = trim((string) ($e['details'] ?? ''));
    $quelleName = trim((string) ($e['sourceName'] ?? ''));
    $meta = ['ma_markt_id' => $typ . ':' . $id, 'ma_source_url' => $url, 'ma_source_name' => $quelleName, 'ma_verified_at' => $geprueft, 'ma_end_at' => $ende,
        'ma_release_confirmed' => '1', 'ma_markt_gemeinde' => $gemeinde, 'ma_markt_ortsteil' => $teil];
    if ($typ === 'ma_job') {
        $art = trim((string) ($e['employment'] ?? ''));
        $meta += ['ma_job_company' => trim((string) ($e['employer'] ?? '')), 'ma_job_location' => $lage, 'ma_job_address' => $details, 'ma_job_type' => ma_markt_job_typ($art),
            'ma_job_hours' => $art, 'ma_job_apply_url' => $url, 'ma_job_provider_type' => stripos($quelleName, 'Karriere') !== false ? 'direct' : ''];
        $text = trim(implode("\n\n", array_filter([$art, $details, $quelleName !== '' ? 'Quelle: ' . $quelleName . '. Maßgeblich ist die Originalausschreibung.' : ''])));
        $anriss = trim(implode(' · ', array_filter([(string) ($e['employer'] ?? ''), $art])));
    } else {
        $mode = mb_strtolower((string) ($e['offerType'] ?? '')) === 'kauf' ? 'buy' : 'rent';
        $meta += ['ma_property_mode' => $mode, 'ma_property_offer' => $mode === 'buy' ? 'Kauf' : 'Miete', 'ma_property_price' => trim((string) ($e['price'] ?? '')),
            'ma_property_address' => $lage, 'ma_property_provider' => $quelleName, 'ma_property_url' => $url];
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*Zimmer/u', $details, $m)) $meta['ma_property_rooms'] = $m[1];
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*m²(?!\s*Grund)/u', $details, $m)) $meta['ma_property_area'] = $m[1] . ' m²';
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*m²\s*Grund/u', $details, $m)) $meta['ma_property_lot'] = $m[1] . ' m²';
        $text = trim(implode("\n\n", array_filter([$details, $quelleName !== '' ? 'Quelle: ' . $quelleName . '. Angaben laut Anbieter, maßgeblich ist die Originalanzeige.' : ''])));
        $anriss = trim(implode(' · ', array_filter([(string) ($e['price'] ?? ''), $details])));
    }
    return ['titel' => $titel, 'text' => $text, 'anriss' => mb_substr($anriss, 0, 300), 'meta' => $meta, 'ortsteil' => ma_markt_ortsteil($gemeinde, $teil), 'ende' => $ende];
}

/** Marktstand aus dem Repository; null, wenn nicht lesbar. */
function ma_markt_holen(): ?array {
    $r = wp_remote_get(MA_MARKT_QUELLE . '?t=' . time(), ['timeout' => 20, 'user-agent' => 'Merzenich Aktuell Markt/' . MA_CORE_VERSION]);
    if (is_wp_error($r) || (int) wp_remote_retrieve_response_code($r) !== 200) return null;
    $d = json_decode((string) wp_remote_retrieve_body($r), true);
    return is_array($d) && isset($d['jobs'], $d['properties']) ? $d : null;
}

/**
 * Übernimmt den Marktstand. $trocken: nur zählen. Gibt [neu, aktualisiert, beendet, uebersprungen, fehler[]] zurück.
 */
function ma_markt_import(array $daten, bool $trocken = false): array {
    $bericht = ['neu' => 0, 'aktualisiert' => 0, 'beendet' => 0, 'uebersprungen' => 0, 'fehler' => []];
    $jetzt = current_time('Y-m-d H:i:s');
    foreach (['ma_job' => (array) $daten['jobs'], 'ma_property' => (array) $daten['properties']] as $typ => $liste) {
        $vorhanden = [];
        foreach (get_posts(['post_type' => $typ, 'post_status' => 'any', 'posts_per_page' => -1, 'meta_key' => 'ma_markt_id', 'fields' => 'ids', 'no_found_rows' => true]) as $pid) $vorhanden[(string) get_post_meta($pid, 'ma_markt_id', true)] = (int) $pid;
        $gesehen = [];
        foreach ($liste as $e) {
            $f = is_array($e) ? ma_markt_felder($e, $typ) : null;
            if (!$f) { $bericht['uebersprungen']++; continue; }
            $mid = $f['meta']['ma_markt_id'];
            if (isset($gesehen[$mid])) { $bericht['uebersprungen']++; continue; } // doppelt im Marktstand
            $gesehen[$mid] = true;
            if ($f['ende'] < $jetzt && !isset($vorhanden[$mid])) { $bericht['uebersprungen']++; continue; } // zu lange nicht geprüft
            if ($trocken) { isset($vorhanden[$mid]) ? $bericht['aktualisiert']++ : $bericht['neu']++; continue; }
            if (isset($vorhanden[$mid])) {
                $pid = $vorhanden[$mid];
                foreach (['ma_verified_at', 'ma_end_at', 'ma_source_url'] as $k) update_post_meta($pid, $k, $f['meta'][$k]);
                $bericht['aktualisiert']++;
                continue;
            }
            $pid = wp_insert_post(['post_type' => $typ, 'post_status' => 'draft', 'post_title' => $f['titel'], 'post_content' => $f['text'], 'post_excerpt' => $f['anriss'],
                'post_author' => (int) (get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID'])[0] ?? 0)], true);
            if (is_wp_error($pid)) { $bericht['fehler'][] = $f['titel'] . ': ' . $pid->get_error_message(); continue; }
            foreach ($f['meta'] as $k => $v) if ($v !== '') update_post_meta($pid, $k, $v);
            if ($f['ortsteil'] !== '' && taxonomy_exists('ma_location') && term_exists($f['ortsteil'], 'ma_location')) wp_set_object_terms($pid, $f['ortsteil'], 'ma_location');
            // Erst mit gesetzter Freigabe veröffentlichen (Marktsperre ma_market_publication_gate).
            wp_update_post(['ID' => $pid, 'post_status' => 'publish']);
            if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($pid, 'Aus dem geprüften Marktstand übernommen (Prüfung ' . mysql2date('d.m.Y', $f['meta']['ma_verified_at']) . ')', '', 0);
            $bericht['neu']++;
        }
        // Nicht mehr im Marktstand: Quelle hat das Angebot entfernt, die Anzeige endet heute.
        foreach ($vorhanden as $mid => $pid) {
            if (isset($gesehen[$mid])) continue;
            $ende = (string) get_post_meta($pid, 'ma_end_at', true);
            if ($ende !== '' && $ende <= $jetzt) continue;
            if (!$trocken) { update_post_meta($pid, 'ma_end_at', $jetzt); if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($pid, 'Nicht mehr im Marktstand: Anzeige beendet', '', 0); }
            $bericht['beendet']++;
        }
    }
    return $bericht;
}

/** Einmal täglich mit dem stündlichen Abgleich (Cron, nach dem Meldungs-Abgleich). */
add_action(defined('MA_ABGLEICH_CRON') ? MA_ABGLEICH_CRON : 'ma_abgleich_stuendlich', function (): void {
    if (get_transient('ma_markt_import_lauf')) return;
    set_transient('ma_markt_import_lauf', 1, 20 * HOUR_IN_SECONDS);
    $d = ma_markt_holen();
    if ($d) update_option('ma_markt_import_stand', ['zeit' => current_time('mysql'), 'bericht' => ma_markt_import($d)], false);
}, 20);

/* Knopf unter Merzenich Aktuell → Abgleich. */
add_action('admin_post_ma_markt_import', function (): void {
    if (!current_user_can('edit_others_posts')) wp_die('Keine Berechtigung.');
    check_admin_referer('ma_markt_import');
    $d = ma_markt_holen();
    if (!$d) { wp_safe_redirect(add_query_arg('ma_markt', 'fehler', admin_url('admin.php?page=ma-abgleich'))); exit; }
    $b = ma_markt_import($d, !empty($_POST['trocken']));
    if (empty($_POST['trocken'])) update_option('ma_markt_import_stand', ['zeit' => current_time('mysql'), 'bericht' => $b], false);
    set_transient('ma_markt_import_bericht', $b + ['trocken' => !empty($_POST['trocken'])], 10 * MINUTE_IN_SECONDS);
    wp_safe_redirect(add_query_arg('ma_markt', 'ok', admin_url('admin.php?page=ma-abgleich'))); exit;
});

/** Kasten auf der Abgleich-Seite: letzter Stand und Knöpfe. */
function ma_markt_import_kasten(): string {
    $h = '<h2>Stellen und Immobilien</h2><p>Übernimmt den geprüften Marktstand (Stellen täglich geprüft, Immobilien mit letztem Prüfdatum) als Einträge unter „Stellen“ und „Immobilien“. Läuft täglich mit dem Abgleich. Angebote, die nicht mehr in der Quelle stehen, enden von selbst; nichts wird gelöscht.</p>';
    $b = get_transient('ma_markt_import_bericht');
    if (is_array($b) && isset($_GET['ma_markt'])) $h .= '<div class="notice notice-success inline"><p>' . ($b['trocken'] ? 'Probelauf: ' : '') . (int) $b['neu'] . ' neu, ' . (int) $b['aktualisiert'] . ' aktualisiert, ' . (int) $b['beendet'] . ' beendet, ' . (int) $b['uebersprungen'] . ' übersprungen' . ($b['fehler'] ? '; Fehler: ' . esc_html(implode('; ', array_slice($b['fehler'], 0, 3))) : '') . '.</p></div>';
    if (($_GET['ma_markt'] ?? '') === 'fehler') $h .= '<div class="notice notice-error inline"><p>Der Marktstand war nicht abrufbar. Später erneut versuchen.</p></div>';
    $s = get_option('ma_markt_import_stand');
    if (is_array($s)) $h .= '<p>Zuletzt: ' . esc_html(mysql2date('d.m.Y, H:i', (string) $s['zeit'])) . ' Uhr · ' . (int) ($s['bericht']['neu'] ?? 0) . ' neu, ' . (int) ($s['bericht']['aktualisiert'] ?? 0) . ' aktualisiert, ' . (int) ($s['bericht']['beendet'] ?? 0) . ' beendet.</p>';
    foreach ([['Probelauf (nur zählen)', 1, ''], ['Stellen und Immobilien übernehmen', 0, ' button-primary']] as [$text, $trocken, $klasse]) {
        $h .= '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline-block;margin-right:8px"><input type="hidden" name="action" value="ma_markt_import">' . wp_nonce_field('ma_markt_import', '_wpnonce', true, false)
            . ($trocken ? '<input type="hidden" name="trocken" value="1">' : '') . '<button class="button' . $klasse . '">' . esc_html($text) . '</button></form>';
    }
    return $h;
}
