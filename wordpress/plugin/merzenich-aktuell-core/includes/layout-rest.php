<?php
/**
 * REST-Schnittstelle der Layout-Karte (02.10.2026): Board im Backend und
 * Bearbeitungsmodus auf der Seite sprechen beide hiermit.
 *
 *   GET   /ma/v1/layout/{seite}                Belegung, Struktur, Auswahlwerte
 *   GET   /ma/v1/layout/{seite}/kandidaten     Meldungen zum Einsetzen (Suche, Rubrik, Ort)
 *   POST  /ma/v1/layout/{seite}                setzen | doppelt | tauschen | entfernen | nur-rubrik | wiederherstellen
 *   POST  /ma/v1/layout/{seite}/zuruecksetzen  alle festen Plätze der Seite freigeben
 *   POST  /ma/v1/meldung                       Schnellformular: Meldung anlegen und einsetzen
 *   PATCH /ma/v1/meldung/{id}                  Titel, Anriss, Kicker direkt auf der Seite ändern
 *
 * Nur Redaktion und Administration (edit_others_posts), nie Partner oder
 * Vereinsredakteure; Cookie-Anmeldung mit X-WP-Nonce (wp_rest). Antworten
 * tragen das fertige Markup der betroffenen Blöcke (Theme-Filter
 * ma_layout_block_html), damit die Seite ohne Neuladen aktuell bleibt.
 */
if (!defined('ABSPATH')) { exit; }

function ma_layout_rest_darf(): bool {
    return function_exists('ma_startplatz_darf') && ma_startplatz_darf();
}

add_action('rest_api_init', function (): void {
    $seite = '(?P<seite>[a-z-]+)';
    $darf = 'ma_layout_rest_darf';
    register_rest_route('ma/v1', "/layout/{$seite}", [
        ['methods' => 'GET', 'permission_callback' => $darf, 'callback' => fn(WP_REST_Request $r) => ma_layout_rest_lesen((string) $r['seite'], (string) $r->get_param('html') === '1')],
        ['methods' => 'POST', 'permission_callback' => $darf, 'callback' => fn(WP_REST_Request $r) => ma_layout_rest_aendern((string) $r['seite'], (array) $r->get_json_params())],
    ]);
    register_rest_route('ma/v1', "/layout/{$seite}/kandidaten", ['methods' => 'GET', 'permission_callback' => $darf,
        'callback' => fn(WP_REST_Request $r) => ma_layout_rest_kandidaten((string) $r['seite'], (string) $r->get_param('q'), (string) $r->get_param('ressort'), (string) $r->get_param('ort'), (int) $r->get_param('seite_nr'))]);
    register_rest_route('ma/v1', "/layout/{$seite}/zuruecksetzen", ['methods' => 'POST', 'permission_callback' => $darf,
        'callback' => function (WP_REST_Request $r) { $d = (array) $r->get_json_params(); return ma_layout_rest_aendern((string) $r['seite'], ['aktion' => 'wiederherstellen', 'karte' => [], 'rev' => (int) ($d['rev'] ?? 0)]); }]);
    register_rest_route('ma/v1', '/meldung', ['methods' => 'POST', 'permission_callback' => $darf, 'callback' => fn(WP_REST_Request $r) => ma_layout_rest_meldung((array) $r->get_json_params())]);
    register_rest_route('ma/v1', '/meldung/(?P<id>\d+)', ['methods' => 'PATCH', 'permission_callback' => $darf, 'callback' => fn(WP_REST_Request $r) => ma_layout_rest_meldung_text((int) $r['id'], (array) $r->get_json_params())]);
});

function ma_layout_rest_antwort($daten, int $status = 200): WP_REST_Response {
    $a = new WP_REST_Response($daten, $status);
    $a->header('Cache-Control', 'no-store');
    return $a;
}

function ma_layout_rest_fehler($e): WP_REST_Response {
    if ($e instanceof WP_Error) return ma_layout_rest_antwort(['meldung' => $e->get_error_message(), 'code' => $e->get_error_code()], (int) ($e->get_error_data()['status'] ?? 400));
    return ma_layout_rest_antwort(['meldung' => (string) $e], 400);
}

/* ---------------------------------------------------------- Daten einer Karte */

/** Rubrik eines Beitrags: erste Ressort-Rubrik, sonst erste Rubrik. */
function ma_layout_ressort_von(int $post_id): array {
    $kats = get_the_category($post_id);
    foreach ($kats as $c) if (ma_layout_seite_gueltig('ressort-' . $c->slug)) return ['slug' => $c->slug, 'name' => $c->name];
    return $kats ? ['slug' => $kats[0]->slug, 'name' => $kats[0]->name] : ['slug' => 'nachrichten', 'name' => 'Nachrichten'];
}

function ma_layout_karte_daten(?WP_Post $p): ?array {
    if (!$p) return null;
    $bild = (int) get_post_thumbnail_id($p->ID);
    $src = $bild ? wp_get_attachment_image_src($bild, 'medium_large') : false;
    $orte = get_the_terms($p->ID, 'ma_location');
    $ort = is_array($orte) && $orte ? $orte[0]->name : 'Merzenich';
    return [
        'id' => (int) $p->ID, 'titel' => html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8'), 'kicker' => (string) get_post_meta($p->ID, 'ma_kicker', true),
        'anriss' => (string) $p->post_excerpt, 'status' => $p->post_status,
        'bild' => $src ? ['src' => $src[0], 'w' => (int) $src[1], 'h' => (int) $src[2], 'alt' => (string) get_post_meta($bild, '_wp_attachment_image_alt', true), 'typ' => (string) get_post_meta($p->ID, 'ma_image_type', true)] : null,
        'ressort' => ma_layout_ressort_von($p->ID), 'ort' => $ort, 'sport' => has_category('sport', $p->ID),
        'datum' => get_the_date('d.m.Y · H:i', $p) . ' Uhr', 'relevanz' => (int) get_post_meta($p->ID, 'ma_relevanz', true) ?: 5,
        'startplatz' => (string) get_post_meta($p->ID, 'ma_startplatz', true) ?: 'auto',
        'bearbeiten' => get_edit_post_link($p->ID, 'raw'), 'ansehen' => get_permalink($p),
    ];
}

/** Auswahlwerte für Picker und Schnellformular. */
function ma_layout_rest_auswahl(): array {
    $rubriken = []; $orte = [];
    foreach (get_categories(['hide_empty' => false]) as $c) if ($c->slug !== 'uncategorized') $rubriken[] = ['slug' => $c->slug, 'name' => $c->name, 'id' => (int) $c->term_id];
    foreach (get_terms(['taxonomy' => 'ma_location', 'hide_empty' => false]) as $t) if ($t instanceof WP_Term) $orte[] = ['slug' => $t->slug, 'name' => $t->name, 'id' => (int) $t->term_id];
    $typen = [];
    if (defined('MA_IMAGE_TYPES')) foreach (MA_IMAGE_TYPES as $k => $l) $typen[] = ['wert' => $k, 'name' => $l];
    return ['rubriken' => $rubriken, 'orte' => $orte, 'bildtypen' => $typen, 'bildrechte' => function_exists('ma_bildrechte_text') ? ma_bildrechte_text() : '',
        'bildrechteHaken' => function_exists('ma_bildrechte_haken_text') ? ma_bildrechte_haken_text() : 'Ich bestätige die Bild- und Nutzungsrechte.',
        'relevanz' => function_exists('ma_relevanz_js_daten') ? ma_relevanz_js_daten() : [], 'darfVeroeffentlichen' => current_user_can('publish_posts'),
        'medien' => rest_url('wp/v2/media'), 'warnungen' => array_combine($w = ['nicht-veroeffentlicht', 'nur-rubrik', 'hervorhebung-abgelaufen', 'kein-bild', 'logo-motiv', 'bild-klein', 'sport', 'nicht-im-ressort', 'reihe-unvollstaendig'], array_map('ma_layout_warnung_text', $w))];
}

function ma_layout_rest_url(string $seite): string {
    $slug = ma_layout_ressort_slug($seite);
    return $slug === '' ? home_url('/') : home_url('/' . $slug . '/');
}

/** Aufgelöste Belegung + Struktur einer Seite. */
function ma_layout_rest_belegung(string $seite): array {
    $karte = ma_layout_get($seite);
    $belegung = (array) apply_filters('ma_layout_belegung', [], $seite);
    $plaetze = [];
    $posts = [];
    foreach (ma_layout_slots($seite) as $slot => $label) {
        $e = $belegung[$slot] ?? null;
        $id = (int) ($e['post'] ?? 0);
        if ($id && !isset($posts[$id])) $posts[$id] = ma_layout_karte_daten(get_post($id));
        $plaetze[$slot] = ['label' => $label, 'post' => $id ? $posts[$id] : null, 'fest' => !empty($e['fest']), 'doppelt' => !empty($karte['slots'][$slot]['doppelt']),
            'warnungen' => array_values(array_map(fn($w) => ['code' => $w, 'text' => ma_layout_warnung_text($w)], (array) ($e['warnungen'] ?? [])))];
    }
    return ['seite' => $seite, 'name' => ma_layout_seiten()[$seite] ?? $seite, 'rev' => $karte['rev'], 'stand' => $karte['stand'], 'struktur' => ma_layout_struktur($seite),
        'plaetze' => $plaetze, 'fest' => $karte['slots'], 'vorschau' => ma_layout_rest_url($seite), 'bloecke' => ma_layout_rest_bloecke($seite)];
}

function ma_layout_rest_bloecke(string $seite): array {
    return $seite === 'startseite' ? array_merge(['oben'], array_keys(ma_layout_sektionen())) : ['feed'];
}

/** Fertiges Markup der Blöcke einer Seite (Theme-Filter), nach dem Auffrischen der Belegung. */
function ma_layout_rest_html(string $seite): array {
    $h = [];
    foreach (ma_layout_rest_bloecke($seite) as $b) $h[$b] = (string) apply_filters('ma_layout_block_html', '', $seite, $b);
    return $h;
}

function ma_layout_rest_lesen(string $seite, bool $mitHtml = false) {
    if (!ma_layout_seite_gueltig($seite)) return ma_layout_rest_antwort(['meldung' => 'Unbekannte Seite.'], 404);
    $a = ma_layout_rest_belegung($seite);
    $a['auswahl'] = ma_layout_rest_auswahl();
    $a['seiten'] = ma_layout_seiten();
    if ($mitHtml) $a['html'] = ma_layout_rest_html($seite);
    return ma_layout_rest_antwort($a);
}

function ma_layout_rest_kandidaten(string $seite, string $q, string $ressort, string $ort, int $seiteNr) {
    if (!ma_layout_seite_gueltig($seite)) return ma_layout_rest_antwort(['meldung' => 'Unbekannte Seite.'], 404);
    $args = ['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 30, 'paged' => max(1, $seiteNr), 'orderby' => 'date', 'order' => 'DESC', 'suppress_filters' => false];
    if ($q !== '') $args['s'] = sanitize_text_field($q);
    if ($ressort !== '') $args['category_name'] = sanitize_key($ressort);
    if ($ort !== '') $args['tax_query'] = [['taxonomy' => 'ma_location', 'field' => 'slug', 'terms' => sanitize_key($ort)]];
    $fest = ma_layout_get($seite)['slots'];
    $raus = [];
    foreach (get_posts($args) as $p) {
        $k = ma_layout_karte_daten($p);
        $k['plaetze'] = array_keys(array_filter($fest, fn($e) => $e['post'] === $p->ID));
        $raus[] = $k;
    }
    return ma_layout_rest_antwort(['kandidaten' => $raus]);
}

/* ---------------------------------------------------------- Änderungen */

function ma_layout_rest_aendern(string $seite, array $d) {
    if (!ma_layout_seite_gueltig($seite)) return ma_layout_rest_antwort(['meldung' => 'Unbekannte Seite.'], 404);
    $aktion = sanitize_key((string) ($d['aktion'] ?? ''));
    $slot = sanitize_text_field((string) ($d['slot'] ?? ''));
    $ziel = sanitize_text_field((string) ($d['ziel'] ?? ''));
    $post = (int) ($d['post'] ?? 0);
    $rev = (int) ($d['rev'] ?? 0);
    $vorher = ma_layout_get($seite)['slots'];
    if ($post && !current_user_can('edit_post', $post)) return ma_layout_rest_antwort(['meldung' => 'Keine Berechtigung für diese Meldung.'], 403);
    switch ($aktion) {
        case 'setzen': $e = ma_layout_set($seite, $slot, $post, ['doppelt' => !empty($d['doppelt']), 'rev' => $rev]); break;
        case 'doppelt': $e = ma_layout_set($seite, $slot, $post, ['doppelt' => true, 'rev' => $rev]); break;
        case 'tauschen': $e = ma_layout_tauschen($seite, $slot, $ziel, $rev, $post, (int) ($d['zielPost'] ?? 0)); break;
        case 'entfernen': $e = ma_layout_entfernen($seite, $slot, $rev); break;
        case 'nur-rubrik':
            if ($seite !== 'startseite' || !$post) return ma_layout_rest_antwort(['meldung' => '„Nur Rubrik“ gibt es nur auf der Startseite.'], 400);
            ma_startplatz_setzen($post, 'aus');
            if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($post, 'Startseite: Nur in der Rubrik (Bearbeitungsmodus)');
            $e = ma_layout_get($seite);
            break;
        case 'wiederherstellen': $e = ma_layout_ersetzen($seite, (array) ($d['karte'] ?? []), $rev); break;
        default: return ma_layout_rest_antwort(['meldung' => 'Unbekannte Aktion.'], 400);
    }
    if ($e instanceof WP_Error) return ma_layout_rest_fehler($e);
    $a = ma_layout_rest_belegung($seite);
    $a['html'] = ma_layout_rest_html($seite);
    $a['rueckgaengig'] = $vorher;
    $a['meldung'] = ['setzen' => 'Eingesetzt.', 'doppelt' => 'Nochmal eingesetzt.', 'tauschen' => 'Verschoben.', 'entfernen' => 'Platz wieder automatisch.', 'nur-rubrik' => 'Nur noch in der Rubrik.', 'wiederherstellen' => 'Wiederhergestellt.'][$aktion];
    return ma_layout_rest_antwort($a);
}

/* ---------------------------------------------------------- Schnellformular */

/**
 * Meldung anlegen: Entwurf → Zuordnungen und Metas → veröffentlichen
 * (Freigabe-Sperren bleiben wirksam) → auf den gewünschten Platz.
 * $f: titel, kicker, anriss, text, rubrik (Slug), ort (Slug), bild (Anhang-ID),
 * bildtyp, bildcredit, bildrechte (bool), quelle (URL), relevanz (1–10),
 * status (publish|draft), seite, slot.
 */
function ma_meldung_anlegen(array $f) {
    $titel = sanitize_text_field((string) ($f['titel'] ?? ''));
    if ($titel === '') return ma_layout_fehler('ma_meldung_titel', 'Bitte einen Titel eingeben.');
    $anriss = sanitize_textarea_field((string) ($f['anriss'] ?? ''));
    $text = wp_kses_post((string) ($f['text'] ?? ''));
    $bild = (int) ($f['bild'] ?? 0);
    if ($bild && get_post_type($bild) !== 'attachment') return ma_layout_fehler('ma_meldung_bild', 'Das Bild wurde nicht gefunden.');
    $veroeffentlichen = (string) ($f['status'] ?? 'publish') === 'publish';
    if ($veroeffentlichen && !current_user_can('publish_posts')) return ma_layout_fehler('ma_meldung_recht', 'Veröffentlichen ist mit diesem Zugang nicht möglich; als Entwurf speichern.', 403);
    if ($veroeffentlichen && $bild && empty($f['bildrechte'])) return ma_layout_fehler('ma_meldung_bildrechte', 'Zum Veröffentlichen mit Bild bitte die Bild- und Nutzungsrechte bestätigen.');
    $id = wp_insert_post(['post_type' => 'post', 'post_status' => 'draft', 'post_title' => $titel, 'post_excerpt' => $anriss, 'post_content' => $text, 'post_author' => get_current_user_id()], true);
    if ($id instanceof WP_Error) return $id;
    $rubrik = sanitize_key((string) ($f['rubrik'] ?? ''));
    $kat = $rubrik !== '' ? get_category_by_slug($rubrik) : null;
    if ($kat) wp_set_post_categories($id, [(int) $kat->term_id]);
    $ort = sanitize_key((string) ($f['ort'] ?? 'merzenich'));
    if ($ort !== '' && term_exists($ort, 'ma_location')) wp_set_object_terms($id, $ort, 'ma_location');
    $kicker = sanitize_text_field((string) ($f['kicker'] ?? ''));
    if ($kicker !== '') update_post_meta($id, 'ma_kicker', $kicker);
    $quelle = esc_url_raw((string) ($f['quelle'] ?? ''));
    if ($quelle !== '') update_post_meta($id, 'ma_source_url', $quelle);
    if ($bild) {
        set_post_thumbnail($id, $bild);
        $typ = sanitize_key((string) ($f['bildtyp'] ?? 'original'));
        update_post_meta($id, 'ma_image_type', defined('MA_IMAGE_TYPES') && isset(MA_IMAGE_TYPES[$typ]) ? $typ : 'original');
        $credit = sanitize_text_field((string) ($f['bildcredit'] ?? ''));
        if ($credit !== '') update_post_meta($id, 'ma_image_credit', $credit);
        if (!empty($f['bildrechte'])) {
            if (function_exists('ma_bildrechte_eintragen')) ma_bildrechte_eintragen($id, function_exists('ma_bildrechte_medien') ? ma_bildrechte_medien($id, $text, $bild) : [$bild]);
            update_post_meta($id, 'ma_image_rights_verified', '1');
        }
    }
    $relevanz = function_exists('ma_relevanz_saeubern') ? ma_relevanz_saeubern($f['relevanz'] ?? 5) : max(1, min(10, (int) ($f['relevanz'] ?? 5)));
    update_post_meta($id, 'ma_relevanz', $relevanz ?: 5);
    update_post_meta($id, 'ma_startplatz', 'auto');
    if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Angelegt im Layout-Editor');
    $antwort = ['id' => $id, 'bearbeiten' => get_edit_post_link($id, 'raw'), 'veroeffentlicht' => false];
    if ($veroeffentlichen) {
        $wer = wp_get_current_user()->display_name;
        foreach (['ma_source_verified', 'ma_date_verified', 'ma_place_verified', 'ma_human_reviewed'] as $k) update_post_meta($id, $k, '1');
        update_post_meta($id, 'ma_reviewed_by', $wer);
        update_post_meta($id, 'ma_reviewed_at', current_time('mysql'));
        wp_update_post(['ID' => $id, 'post_status' => 'publish']);
        if (get_post_status($id) !== 'publish') {
            $grund = (string) get_post_meta($id, '_ma_gate_reason', true);
            $antwort['meldung'] = 'Als Entwurf gespeichert, nicht veröffentlicht: ' . ($grund !== '' ? str_replace('|', ', ', $grund) : 'eine Pflichtangabe fehlt') . '. Bitte im Editor prüfen.';
            return $antwort;
        }
        $antwort['veroeffentlicht'] = true;
        $seite = sanitize_key((string) ($f['seite'] ?? '')); $slot = sanitize_text_field((string) ($f['slot'] ?? ''));
        if ($seite !== '' && $slot !== '' && ma_layout_seite_gueltig($seite) && isset(ma_layout_slots($seite)[$slot])) {
            $e = ma_layout_set($seite, $slot, $id, ['doppelt' => false]);
            $antwort['platziert'] = !($e instanceof WP_Error);
            $antwort['meldung'] = $antwort['platziert'] ? 'Veröffentlicht und eingesetzt: ' . ma_layout_platz_label($seite, $slot) . '.' : 'Veröffentlicht, aber nicht eingesetzt: ' . $e->get_error_message();
        } else $antwort['meldung'] = 'Veröffentlicht.';
    } else $antwort['meldung'] = 'Als Entwurf gespeichert.';
    return $antwort;
}

function ma_layout_rest_meldung(array $d) {
    $e = ma_meldung_anlegen($d);
    if ($e instanceof WP_Error) return ma_layout_rest_fehler($e);
    $a = ['post' => ma_layout_karte_daten(get_post((int) $e['id']))] + $e;
    $seite = sanitize_key((string) ($d['seite'] ?? ''));
    if ($seite !== '' && ma_layout_seite_gueltig($seite)) { $a['layout'] = ma_layout_rest_belegung($seite); $a['html'] = ma_layout_rest_html($seite); }
    return ma_layout_rest_antwort($a, 201);
}

/** Titel, Anriss, Kicker ändern (Inline-Bearbeitung auf der Seite). */
function ma_layout_rest_meldung_text(int $id, array $d) {
    $p = get_post($id);
    if (!$p || $p->post_type !== 'post') return ma_layout_rest_antwort(['meldung' => 'Meldung nicht gefunden.'], 404);
    if (!current_user_can('edit_post', $id)) return ma_layout_rest_antwort(['meldung' => 'Keine Berechtigung für diese Meldung.'], 403);
    $u = ['ID' => $id]; $was = [];
    if (array_key_exists('titel', $d)) { $t = sanitize_text_field((string) $d['titel']); if ($t === '') return ma_layout_rest_antwort(['meldung' => 'Der Titel darf nicht leer sein.'], 400); if ($t !== $p->post_title) { $u['post_title'] = $t; $was[] = 'Titel'; } }
    if (array_key_exists('anriss', $d)) { $t = sanitize_textarea_field((string) $d['anriss']); if ($t !== $p->post_excerpt) { $u['post_excerpt'] = $t; $was[] = 'Anriss'; } }
    if (count($u) > 1) {
        // Reine Textänderung: Status bleibt, wie er ist. Die Veröffentlichungssperre
        // (Pflichthaken) prüft nur beim Veröffentlichen, nicht beim Nachbessern des Titels.
        $GLOBALS['ma_verlauf_still'] = true;
        $gate = function_exists('ma_publication_gate') && has_filter('wp_insert_post_data', 'ma_publication_gate');
        if ($gate) remove_filter('wp_insert_post_data', 'ma_publication_gate', 20);
        $u['post_status'] = $p->post_status;
        $r = wp_update_post($u, true);
        if ($gate) add_filter('wp_insert_post_data', 'ma_publication_gate', 20, 2);
        unset($GLOBALS['ma_verlauf_still']);
        if ($r instanceof WP_Error) return ma_layout_rest_fehler($r);
    }
    if (array_key_exists('kicker', $d)) { $k = sanitize_text_field((string) $d['kicker']); if ($k !== (string) get_post_meta($id, 'ma_kicker', true)) { $k === '' ? delete_post_meta($id, 'ma_kicker') : update_post_meta($id, 'ma_kicker', $k); $was[] = 'Kicker'; } }
    if ($was && function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Bearbeitet auf der Seite: ' . implode(', ', $was));
    $a = ['post' => ma_layout_karte_daten(get_post($id)), 'geaendert' => $was, 'meldung' => $was ? 'Gespeichert.' : 'Nichts geändert.'];
    $seite = sanitize_key((string) ($d['seite'] ?? ''));
    if ($seite !== '' && ma_layout_seite_gueltig($seite)) { $a['layout'] = ma_layout_rest_belegung($seite); $a['html'] = ma_layout_rest_html($seite); }
    return ma_layout_rest_antwort($a);
}
