<?php
/**
 * Firmen-Entwürfe (16.10.2026, Auftrag Betreiber; seit 24.10.2026 Almera Clean,
 * KBS Management, Merzlicht und AJ Sports statt Bäckerei Wirtz, Hasberg und
 * Porschen Media): Unternehmen aus data/firmen-entwuerfe.json als Entwurf im
 * vorhandenen System.
 *
 * - Unternehmensprofil (ma_business) mit Stammdaten, Quelle und Stand. Ohne
 *   Einwilligung bleibt es Entwurf (Sperre in business.php); die Redaktion setzt
 *   den Haken erst, wenn das Unternehmen zugestimmt hat. Jedes Profil aus der
 *   Datei ist ein Unternehmenskanal (ma_business_kanal), auch ohne Beitrag.
 *   „nur_name“: Profil nur mit dem Namen (keine Adresse, kein Logo, kein
 *   Beitrag), bis der Betreiber Angaben schickt; nichts wird dazu erfunden.
 * - Ein Werbebeitrag je Unternehmen, gesponsert und verknüpft mit dem Profil
 *   (ma_business_id), nicht auf der Startseite, als Entwurf. Kennzeichnung nach
 *   „art“: test (kostenfreier Testeintrag, testeintrag.php), anzeige oder
 *   eigen (in eigener Sache, für die Herausgeberin KBS).
 * - Logos: Quelle und Rechtestand stehen am Profil (ma_business_logo_quelle,
 *   ma_business_logo_rechte = offen, bis die Logo-Freigabe eingetragen ist);
 *   bis dahin zeigt die Seite das Kürzel der Firma.
 * - Werbebanner („anzeige“ mit Bild aus data/banner/): entsteht einmal mit der
 *   Einwilligung als laufende Anzeige (ma_ad) auf allen sichtbaren Werbeplätzen
 *   (ma_firmen_banner_plaetze()), ohne Link, ohne Ende.
 *
 * Merzenich Aktuell → Firmen-Entwürfe legt fehlende Entwürfe an. Vorhandenes
 * (auch im Papierkorb) wird nie überschrieben oder doppelt angelegt.
 *
 * Zusage eintragen (20.10.2026): je Betrieb Einwilligung und Logo-Freigabe getrennt,
 * jeweils mit Nachweis (wer, wann, woher). Mit der Logo-Freigabe kommt das Logo
 * aus data/logos/ einmal in die Mediathek und wird Beitragsbild des Profils; mit
 * der Einwilligung wird das Profil veröffentlicht (die Sperre in business.php
 * bleibt die Prüfung). Den Werbebeitrag gibt die Redaktion danach wie jeden
 * Beitrag in den Freigaben frei: Er liegt dort nach der Einwilligung zur Prüfung
 * (Prüfhäkchen, Sperre in freigabe.php). Zusatzbeiträge bleiben Entwurf.
 *
 * Firma zurückziehen (24.10.2026): Profil, Beiträge und Werbebanner eines
 * früheren Betriebs („frueher“ in der Datei) werden Entwurf; nichts wird
 * gelöscht, Logos bleiben in der Mediathek.
 */
if (!defined('ABSPATH')) { exit; }

function ma_firmen_entwuerfe_daten(): array {
    $d = json_decode((string) @file_get_contents(MA_CORE_PATH . 'data/firmen-entwuerfe.json'), true);
    return is_array($d) ? $d : ['firmen' => []];
}

/** Kennzeichnung des Werbebeitrags: test (Standard), anzeige oder eigen. */
function ma_firmen_art(array $f): string {
    $a = (string) ($f['art'] ?? 'test');
    return in_array($a, ['test', 'anzeige', 'eigen'], true) ? $a : 'test';
}

/**
 * Werbeplätze der Banner: alle Plätze, die die Seite zeigt (sechs Startseiten-Bänder,
 * Servicespalte und Bühne der Startseite, unter Artikeln, Sport- und Unternehmensspalte).
 * Wo eine echte Anzeige läuft, verschwindet die Musteranzeige.
 */
function ma_firmen_banner_plaetze(): array {
    return ['homepage_band_1', 'homepage_band_2', 'homepage_band_3', 'homepage_band_4', 'homepage_band_5', 'homepage_band_6', 'homepage_sidebar_top', 'homepage_buehne', 'article_inline_1', 'sport_sidebar', 'business_sidebar'];
}

/** Beitragstext: Absätze (einfaches HTML aus der Datei) und der Knopf zur Website des Unternehmens. */
function ma_firmen_beitrag_html(array $b): string {
    $h = '';
    foreach ((array) ($b['absaetze'] ?? []) as $a) $h .= '<p>' . wp_kses((string) $a, ['strong' => [], 'em' => []]) . "</p>\n";
    $k = (array) ($b['knopf'] ?? []);
    if (!empty($k['ziel']) && !empty($k['text'])) $h .= '<p class="cta-row"><a class="btn" href="' . esc_url((string) $k['ziel']) . '" target="_blank" rel="noopener sponsored">' . esc_html((string) $k['text']) . ' ↗</a></p>' . "\n";
    return $h;
}

/** Vorhandener Inhalt mit diesem Slug in jedem Status (auch In Prüfung, Abgelehnt, Archiv, Papierkorb), sonst 0. */
function ma_firmen_vorhanden(string $typ, string $slug): int {
    // Ohne Slug nie suchen: WordPress ignoriert ein leeres „name“ und liefert sonst den neuesten Inhalt.
    if ($slug === '') return 0;
    $q = get_posts(['post_type' => $typ, 'name' => $slug, 'post_status' => array_values(array_diff(get_post_stati(), ['auto-draft', 'inherit'])), 'posts_per_page' => 1, 'fields' => 'ids']);
    if ($q) return (int) $q[0];
    // Im Papierkorb trägt der Slug den Zusatz __trashed.
    $q = get_posts(['post_type' => $typ, 'name' => $slug . '__trashed', 'post_status' => 'trash', 'posts_per_page' => 1, 'fields' => 'ids']);
    return $q ? (int) $q[0] : 0;
}

/** Legt fehlende Entwürfe an. Ergebnis: Zeilen für die Rückmeldung. */
function ma_firmen_entwuerfe_anlegen(): array {
    $daten = ma_firmen_entwuerfe_daten();
    $stand = (string) ($daten['stand'] ?? '');
    $log = [];
    foreach ((array) ($daten['firmen'] ?? []) as $f) {
        $p = (array) ($f['profil'] ?? []); $b = (array) ($f['beitrag'] ?? []);
        if (empty($p['slug']) || empty($p['titel'])) continue;
        $pid = ma_firmen_vorhanden('ma_business', (string) $p['slug']);
        if (!$pid) {
            $logo = (array) ($p['logo'] ?? []);
            $r = wp_insert_post(wp_slash([
                'post_type' => 'ma_business', 'post_status' => 'draft', 'post_title' => (string) $p['titel'], 'post_name' => (string) $p['slug'],
                'post_excerpt' => (string) ($p['kurz'] ?? ''), 'post_content' => '<p>' . esc_html((string) ($p['text'] ?? '')) . '</p>',
                'meta_input' => array_filter([
                    'ma_business_branche' => (string) ($p['branche'] ?? ''), 'ma_business_adresse' => (string) ($p['adresse'] ?? ''),
                    'ma_business_telefon' => (string) ($p['telefon'] ?? ''), 'ma_business_email' => (string) ($p['email'] ?? ''),
                    'ma_business_website' => (string) ($p['website'] ?? ''), 'ma_business_oeffnungszeiten' => (string) ($p['oeffnungszeiten'] ?? ''),
                    'ma_business_ortsteil' => (string) ($p['ortsteil'] ?? ''), 'ma_business_quelle' => (string) ($p['quelle'] ?? ''), 'ma_business_stand' => $stand,
                    'ma_business_firmenname' => (string) ($p['firmenname'] ?? ''),
                    'ma_business_logo_quelle' => (string) ($logo['quelle'] ?? ''), 'ma_business_logo_rechte' => $logo ? (string) ($logo['rechte'] ?? 'offen') : '', 'ma_business_logo_alt' => (string) ($logo['alt'] ?? ''),
                    'ma_business_karte_knopf' => (string) ($p['karte']['knopf'] ?? ''), 'ma_business_karte_ziel' => (string) ($p['karte']['ziel'] ?? ''),
                    'ma_business_ort' => (string) ($p['ort'] ?? ''), 'ma_business_kanal' => '1',
                    'ma_business_nur_name' => !empty($f['nur_name']) ? '1' : '', 'ma_business_herausgeberin' => !empty($f['herausgeberin']) ? '1' : '',
                ], fn($v) => $v !== ''),
            ]), true);
            if (is_wp_error($r) || !$r) { $log[] = $p['titel'] . ': Profil nicht angelegt' . (is_wp_error($r) ? ' (' . $r->get_error_message() . ')' : ''); continue; }
            $pid = (int) $r;
            $log[] = $p['titel'] . ': Profil als Entwurf angelegt';
            if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($pid, 'Entwurf angelegt', 'Aus data/firmen-entwuerfe.json (Stand ' . $stand . '). Veröffentlichung erst mit Einwilligung des Unternehmens' . (!empty($p['logo']) ? '; Logo-Rechte offen.' : '.'));
        } else $log[] = $p['titel'] . ': Profil schon vorhanden (' . get_post_status($pid) . ')';

        // Werbebeitrag und (20.10.2026) die Zusatzbeiträge, alle als Entwurf.
        $art = ma_firmen_art($f);
        foreach (array_merge([$b], (array) ($f['zusatz'] ?? [])) as $nr => $b) {
            if (empty($b['slug']) || empty($b['titel'])) continue;
            if ($bid = ma_firmen_vorhanden('post', (string) $b['slug'])) { $log[] = $p['titel'] . ': Beitrag schon vorhanden (' . get_post_status($bid) . ')'; continue; }
            $kat = get_category_by_slug('wirtschaft');
            $r = wp_insert_post(wp_slash([
                'post_type' => 'post', 'post_status' => 'draft', 'post_title' => (string) $b['titel'], 'post_name' => (string) $b['slug'],
                'post_excerpt' => (string) ($b['anriss'] ?? ''), 'post_content' => ma_firmen_beitrag_html($b),
                'post_category' => $kat ? [(int) $kat->term_id] : [],
                'meta_input' => [
                    'ma_gesponsert' => '1', 'ma_gesponsert_art' => $art, 'ma_gesponsert_von' => (string) ($p['firmenname'] ?? '') ?: (string) $p['titel'],
                    'ma_business_id' => (string) $pid, 'ma_seo_titel' => (string) ($b['seo_titel'] ?? ''), 'ma_seo_beschreibung' => (string) ($b['seo_beschreibung'] ?? ''),
                    'ma_source_url' => (string) ($b['quelle'] ?? '') ?: (string) ($p['website'] ?? ''), 'ma_source_publisher' => (string) ($p['firmenname'] ?? '') ?: (string) $p['titel'],
                    'ma_startseite_freigabe' => 'nein',
                ],
            ]), true);
            if (is_wp_error($r) || !$r) { $log[] = $p['titel'] . ': Beitrag nicht angelegt' . (is_wp_error($r) ? ' (' . $r->get_error_message() . ')' : ''); continue; }
            $bid = (int) $r;
            if (!empty($p['ortsteil']) && taxonomy_exists('ma_location')) wp_set_object_terms($bid, (string) $p['ortsteil'], 'ma_location', false);
            $wie = ['test' => 'kostenfreier Testeintrag', 'anzeige' => 'Anzeige', 'eigen' => 'Anzeige in eigener Sache'][$art];
            $log[] = $p['titel'] . ': Beitrag als Entwurf angelegt (' . $wie . ')';
            if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($bid, 'Entwurf angelegt', 'Werbebeitrag (' . $wie . ') aus data/firmen-entwuerfe.json. Erst veröffentlichen, wenn das Unternehmen Text und Schaltung bestätigt hat.');
        }
    }
    return $log;
}

/** Daten eines Betriebs aus der Datei nach Profil-Slug, sonst null. */
function ma_firmen_eintrag(string $slug): ?array {
    foreach ((array) (ma_firmen_entwuerfe_daten()['firmen'] ?? []) as $f) if ((string) ($f['profil']['slug'] ?? '') === $slug) return $f;
    return null;
}

/**
 * Datei aus dem Plugin (data/<ordner>/<datei>) einmal in die Mediathek übernehmen;
 * ein zweiter Aufruf findet das Medium über $merkmal wieder. Gibt die Medien-ID
 * zurück, 0 bei Fehler ($fehler nennt den Grund).
 */
function ma_firmen_medium(string $ordner, string $datei, string $merkmal, int $pid, string $alt, string $credit, string $quelle, string &$fehler = ''): int {
    $datei = basename($datei);
    $pfad = MA_CORE_PATH . 'data/' . $ordner . '/' . $datei;
    if ($datei === '' || !is_file($pfad)) { $fehler = 'Datei fehlt im Plugin'; return 0; }
    $da = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => $merkmal, 'meta_value' => $datei]);
    if ($da) return (int) $da[0];
    if (!function_exists('media_handle_sideload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }
    $tmp = wp_tempnam($datei);
    if (!$tmp || !copy($pfad, $tmp)) { $fehler = 'Datei nicht lesbar'; return 0; }
    $id = media_handle_sideload(['name' => $datei, 'tmp_name' => $tmp], $pid, $alt);
    if (is_wp_error($id)) { @unlink($tmp); $fehler = $id->get_error_message(); return 0; }
    $id = (int) $id;
    update_post_meta($id, $merkmal, $datei);
    update_post_meta($id, '_wp_attachment_image_alt', $alt);
    update_post_meta($id, 'ma_image_credit', $credit);
    if ($quelle !== '') update_post_meta($id, 'ma_image_original_url', $quelle);
    update_post_meta($id, 'ma_image_rights_verified', '1');
    return $id;
}

/** Logo aus data/logos/ (Medien-ID, 0 bei Fehler). */
function ma_firmen_logo_medium(array $p, int $pid, string &$fehler = ''): int {
    $logo = (array) ($p['logo'] ?? []);
    $id = ma_firmen_medium('logos', (string) ($logo['datei'] ?? ''), 'ma_firmen_logo_datei', $pid, (string) ($logo['alt'] ?? ''),
        'Logo: ' . (string) ($p['titel'] ?? '') . ', Nutzung mit Freigabe des Unternehmens', (string) ($logo['quelle'] ?? ''), $fehler);
    if (!$id && $fehler === 'Datei fehlt im Plugin') $fehler = 'Logo-Datei fehlt im Plugin';
    return $id;
}

/** Vorhandenes Werbebanner eines Unternehmens (jeder Status, auch Papierkorb), sonst 0. */
function ma_firmen_banner_vorhanden(string $slug): int {
    $q = get_posts(['post_type' => 'ma_ad', 'post_status' => array_values(array_diff(get_post_stati(), ['auto-draft', 'inherit'])), 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => 'ma_firmen_banner', 'meta_value' => $slug]);
    return $q ? (int) $q[0] : 0;
}

/**
 * Werbebanner aus „anzeige“ einmal anlegen: laufende Anzeige (ma_ad) auf allen
 * Plätzen aus ma_firmen_banner_plaetze(), ohne Link und ohne Ende, Bild aus
 * data/banner/, verknüpft mit dem Profil. Ergebnis: Zeile für die Rückmeldung
 * ('' ohne Banner in der Datei).
 */
function ma_firmen_banner_anlegen(array $f, int $pid): string {
    $a = (array) ($f['anzeige'] ?? []); $p = (array) ($f['profil'] ?? []);
    $slug = (string) ($p['slug'] ?? ''); $name = (string) ($p['titel'] ?? $slug);
    if (!$a || empty($a['titel']) || $slug === '') return '';
    if ($da = ma_firmen_banner_vorhanden($slug)) {
        // Bild fehlt (Übernahme beim ersten Mal gescheitert): noch einmal versuchen.
        if (!get_post_thumbnail_id($da) && !empty($a['bild']) && get_post_status($da) !== 'trash') {
            $fehler = '';
            $m = ma_firmen_medium('banner', (string) $a['bild'], 'ma_firmen_banner_datei', $da, (string) ($a['alt'] ?? 'Anzeige ' . $name), 'Werbebanner ' . $name . ', gestaltet von Merzenich Aktuell', '', $fehler);
            if ($m) { set_post_thumbnail($da, $m); return $name . ': Werbebanner schon vorhanden, Bild nachgetragen.'; }
        }
        return $name . ': Werbebanner schon vorhanden (' . get_post_status($da) . ').';
    }
    $plaetze = ma_firmen_banner_plaetze();
    $r = wp_insert_post(wp_slash([
        'post_type' => 'ma_ad', 'post_status' => 'publish', 'post_title' => (string) $a['titel'], 'post_excerpt' => (string) ($a['text'] ?? ''),
        'meta_input' => ['ma_ad_slot' => $plaetze[0], 'ma_ad_active' => '1', 'ma_ad_url' => (string) ($a['ziel'] ?? ''), 'ma_ad_sponsor' => '',
            'ma_business_id' => (string) $pid, 'ma_firmen_banner' => $slug],
    ]), true);
    if (is_wp_error($r) || !$r) return $name . ': Werbebanner nicht angelegt' . (is_wp_error($r) ? ' (' . $r->get_error_message() . ')' : '') . '.';
    $id = (int) $r;
    foreach (array_slice($plaetze, 1) as $pl) add_post_meta($id, 'ma_ad_slot_weitere', $pl);
    $fehler = '';
    $m = !empty($a['bild']) ? ma_firmen_medium('banner', (string) $a['bild'], 'ma_firmen_banner_datei', $id, (string) ($a['alt'] ?? 'Anzeige ' . $name),
        'Werbebanner ' . $name . ', gestaltet von Merzenich Aktuell', '', $fehler) : 0;
    if ($m) set_post_thumbnail($id, $m);
    if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Werbebanner angelegt', 'Aus data/firmen-entwuerfe.json, mit der Einwilligung von ' . $name . '. Läuft ohne Ende, bis der Betreiber es austauscht.');
    return $name . ': Werbebanner läuft' . ($m ? '' : ' (ohne Bild: ' . ($fehler ?: 'keine Bilddatei') . ')') . '.';
}

/**
 * Zusage eines Betriebs eintragen. $nachweis: woher die Zusage stammt (Pflicht).
 * Einwilligung und Logo getrennt; was schon eingetragen ist, bleibt unverändert.
 * Ergebnis: Zeilen für die Rückmeldung.
 */
function ma_firmen_zusage_eintragen(string $slug, string $nachweis, bool $einwilligung, bool $logo): array {
    $f = ma_firmen_eintrag($slug);
    if (!$f) return ['Unbekannter Betrieb.'];
    $p = (array) $f['profil']; $name = (string) ($p['titel'] ?? $slug);
    $nachweis = trim($nachweis);
    if ($nachweis === '') return [$name . ': Ohne Nachweis der Zusage wird nichts eingetragen.'];
    if (empty($p['logo'])) $logo = false;
    if (!$einwilligung && !$logo) return [$name . ': Kein Haken gesetzt, nichts eingetragen.'];
    $pid = ma_firmen_vorhanden('ma_business', (string) $p['slug']);
    if (!$pid || get_post_status($pid) === 'trash') return [$name . ': Profil fehlt. Zuerst „Fehlende Entwürfe anlegen“.'];
    $wer = wp_get_current_user();
    $wer = (string) ($wer->display_name ?? '') ?: (string) ($wer->user_login ?? '');
    $jetzt = (string) current_time('d.m.Y H:i');
    $log = [];
    if ($logo && (string) get_post_meta($pid, 'ma_business_logo_rechte', true) === 'geklaert') {
        $log[] = $name . ': Logo war schon freigegeben, nichts geändert.';
    } elseif ($logo) {
        $fehler = '';
        $m = ma_firmen_logo_medium($p, $pid, $fehler);
        if (!$m) $log[] = $name . ': Logo nicht übernommen (' . $fehler . ').';
        else {
            set_post_thumbnail($pid, $m);
            update_post_meta($pid, 'ma_business_logo_rechte', 'geklaert');
            update_post_meta($pid, 'ma_business_logo_freigabe', $nachweis . ' · eingetragen von ' . $wer . ' am ' . $jetzt);
            $log[] = $name . ': Logo freigegeben und als Beitragsbild gesetzt.';
            if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($pid, 'Logo freigegeben', $nachweis);
        }
    }
    if ($einwilligung) {
        if ((string) get_post_meta($pid, 'ma_business_consent', true) !== '1') {
            update_post_meta($pid, 'ma_business_consent', '1');
            update_post_meta($pid, 'ma_business_consent_by', $wer);
            update_post_meta($pid, 'ma_business_consent_at', $jetzt);
            update_post_meta($pid, 'ma_business_consent_quelle', $nachweis);
            if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($pid, 'Einwilligung eingetragen', $nachweis);
        }
        if (get_post_status($pid) !== 'publish') {
            $r = wp_update_post(['ID' => $pid, 'post_status' => 'publish'], true);
            $log[] = $name . (!is_wp_error($r) && get_post_status($pid) === 'publish' ? ': Einwilligung eingetragen, Profil veröffentlicht.' : ': Einwilligung eingetragen, Profil aber nicht veröffentlicht.');
        } else $log[] = $name . ': Einwilligung eingetragen, Profil war schon veröffentlicht.';
        // Der Werbebeitrag (erster Beitrag der Datei) geht zur Prüfung in die Freigaben; dort setzt die Redaktion die
        // Prüfhäkchen und veröffentlicht (Sperre in freigabe.php). Zusatzbeiträge bleiben Entwurf (Review 20.10.2026).
        $bid = ma_firmen_vorhanden('post', (string) ($f['beitrag']['slug'] ?? ''));
        if ($bid && get_post_status($pid) === 'publish' && get_post_status($bid) === 'draft') {
            wp_update_post(['ID' => $bid, 'post_status' => 'pending']);
            $log[] = $name . ': Werbebeitrag liegt in den Freigaben zur Prüfung.';
            if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($bid, 'Zur Freigabe vorgelegt', 'Einwilligung des Unternehmens eingetragen: ' . $nachweis);
        }
        // Werbebanner erst, wenn das Profil veröffentlicht ist (Einwilligung wirksam).
        if (get_post_status($pid) === 'publish' && ($z = ma_firmen_banner_anlegen($f, $pid)) !== '') $log[] = $z;
    }
    return $log;
}

/**
 * Firma zurückziehen: veröffentlichte, geplante und wartende Beiträge des Profils,
 * seine laufenden Werbebanner und das Profil selbst werden Entwurf (Banner auch
 * „Schaltung aus“). Nichts wird gelöscht; Logos bleiben in der Mediathek. Nur für
 * Unternehmen aus der Datei (aktuell oder „frueher“). Ergebnis: Zeilen.
 */
function ma_firmen_zurueckziehen(string $slug): array {
    $bekannt = [];
    foreach ((array) (ma_firmen_entwuerfe_daten()['firmen'] ?? []) as $f) $bekannt[(string) ($f['profil']['slug'] ?? '')] = (string) ($f['profil']['titel'] ?? '');
    foreach ((array) (ma_firmen_entwuerfe_daten()['frueher'] ?? []) as $f) $bekannt[(string) ($f['slug'] ?? '')] = (string) ($f['titel'] ?? '');
    if ($slug === '' || !isset($bekannt[$slug])) return ['Unbekannter Betrieb.'];
    $name = $bekannt[$slug] ?: $slug;
    $pid = ma_firmen_vorhanden('ma_business', $slug);
    if (!$pid) return [$name . ': Profil nicht gefunden, nichts geändert.'];
    if (get_post_status($pid) === 'trash') return [$name . ': Profil liegt im Papierkorb, nichts geändert.'];
    $log = []; $n = 0;
    foreach (['post', 'ma_ad'] as $typ) {
        $ids = get_posts(['post_type' => $typ, 'post_status' => ['publish', 'future', 'pending', 'ma_in_pruefung'], 'posts_per_page' => -1, 'fields' => 'ids',
            'meta_query' => [['key' => 'ma_business_id', 'value' => (string) $pid]]]);
        foreach ($ids as $id) {
            if ($typ === 'ma_ad') update_post_meta((int) $id, 'ma_ad_active', '0');
            wp_update_post(['ID' => (int) $id, 'post_status' => 'draft']);
            if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen((int) $id, 'Zurückgezogen', 'Unternehmen ' . $name . ' von der Seite genommen (Firmen-Entwürfe).');
            $n++;
        }
    }
    $log[] = $name . ': ' . $n . ($n === 1 ? ' Beitrag oder Banner' : ' Beiträge und Banner') . ' auf Entwurf gesetzt.';
    if (get_post_status($pid) !== 'draft') {
        wp_update_post(['ID' => $pid, 'post_status' => 'draft']);
        if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($pid, 'Zurückgezogen', 'Profil von der Seite genommen (Firmen-Entwürfe).');
        $log[] = $name . ': Profil auf Entwurf gesetzt.';
    } else $log[] = $name . ': Profil war schon Entwurf.';
    return $log;
}

// Priorität 30: Das Hauptmenü „Merzenich Aktuell“ meldet admin.php erst bei plugins_loaded an (Priorität 10);
// vorher angemeldet, fehlte die Seite (403 „Keine Berechtigung“).
add_action('admin_menu', function (): void {
    add_submenu_page('merzenich-aktuell', 'Firmen-Entwürfe', 'Firmen-Entwürfe', 'edit_others_posts', 'ma-firmen-entwuerfe', 'ma_firmen_entwuerfe_seite');
}, 30);

add_action('admin_post_ma_firmen_zusage', function (): void {
    if (!current_user_can('edit_others_posts')) wp_die('Keine Berechtigung.');
    check_admin_referer('ma_firmen_zusage');
    $log = ma_firmen_zusage_eintragen(sanitize_title(wp_unslash($_POST['firma'] ?? '')), sanitize_text_field(wp_unslash($_POST['nachweis'] ?? '')), !empty($_POST['einwilligung']), !empty($_POST['logo']));
    set_transient('ma_firmen_entwuerfe_log_' . get_current_user_id(), $log, 300);
    wp_safe_redirect(admin_url('admin.php?page=ma-firmen-entwuerfe&angelegt=1'));
    exit;
});

add_action('admin_post_ma_firmen_zurueckziehen', function (): void {
    if (!current_user_can('edit_others_posts')) wp_die('Keine Berechtigung.');
    check_admin_referer('ma_firmen_zurueckziehen');
    set_transient('ma_firmen_entwuerfe_log_' . get_current_user_id(), ma_firmen_zurueckziehen(sanitize_title(wp_unslash($_POST['firma'] ?? ''))), 300);
    wp_safe_redirect(admin_url('admin.php?page=ma-firmen-entwuerfe&angelegt=1'));
    exit;
});

add_action('admin_post_ma_firmen_entwuerfe', function (): void {
    if (!current_user_can('edit_others_posts')) wp_die('Keine Berechtigung.');
    check_admin_referer('ma_firmen_entwuerfe');
    set_transient('ma_firmen_entwuerfe_log_' . get_current_user_id(), ma_firmen_entwuerfe_anlegen(), 300);
    wp_safe_redirect(admin_url('admin.php?page=ma-firmen-entwuerfe&angelegt=1'));
    exit;
});

function ma_firmen_entwuerfe_seite(): void {
    $daten = ma_firmen_entwuerfe_daten();
    echo '<div class="wrap"><h1>Firmen-Entwürfe</h1><p>' . esc_html((string) ($daten['hinweis'] ?? '')) . '</p>';
    $log = get_transient('ma_firmen_entwuerfe_log_' . get_current_user_id());
    if (!empty($_GET['angelegt']) && is_array($log)) { echo '<div class="notice notice-success"><ul>'; foreach ($log as $z) echo '<li>' . esc_html((string) $z) . '</li>'; echo '</ul></div>'; }
    $zurueck = fn(string $slug, string $name): string => '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(' . esc_attr(wp_json_encode($name . ' von der Seite nehmen? Profil, Beiträge und Banner werden Entwurf.')) . ')"><input type="hidden" name="action" value="ma_firmen_zurueckziehen"><input type="hidden" name="firma" value="' . esc_attr($slug) . '">'
        . wp_nonce_field('ma_firmen_zurueckziehen', '_wpnonce', true, false) . '<button class="button-link" style="color:#b32d2e">Firma zurückziehen</button></form>';
    echo '<table class="widefat striped"><thead><tr><th>Betrieb</th><th>Profil</th><th>Einwilligung</th><th>Werbebeitrag</th><th>Logo</th><th>Werbebanner</th><th>Zusage eintragen</th></tr></thead><tbody>';
    foreach ((array) ($daten['firmen'] ?? []) as $f) {
        $p = (array) ($f['profil'] ?? []); $b = (array) ($f['beitrag'] ?? []);
        $pid = ma_firmen_vorhanden('ma_business', (string) ($p['slug'] ?? ''));
        $bid = ma_firmen_vorhanden('post', (string) ($b['slug'] ?? ''));
        $zelle = fn(int $id): string => $id ? '<a href="' . esc_url((string) get_edit_post_link($id)) . '">' . esc_html(function_exists('ma_status_name') ? ma_status_name((string) get_post_status($id)) : (string) get_post_status($id)) . '</a>' . (get_post_status($id) !== 'publish' ? ' · <a href="' . esc_url((string) get_preview_post_link($id)) . '" target="_blank">Vorschau</a>' : '') : 'fehlt';
        $ja = $pid && (string) get_post_meta($pid, 'ma_business_consent', true) === '1';
        $logoJa = $pid && (string) get_post_meta($pid, 'ma_business_logo_rechte', true) === 'geklaert';
        $logo = (array) ($p['logo'] ?? []);
        $einw = $ja ? 'liegt vor<br><small>' . esc_html(trim((string) get_post_meta($pid, 'ma_business_consent_quelle', true) . ' · ' . get_post_meta($pid, 'ma_business_consent_by', true) . ', ' . get_post_meta($pid, 'ma_business_consent_at', true), ' ·,')) . '</small>' : '<strong>fehlt</strong>';
        $logoZelle = !$logo ? '– <small>(noch kein Logo)</small>' : (!empty($logo['quelle']) ? '<a href="' . esc_url((string) $logo['quelle']) . '" target="_blank" rel="noopener">Quelle</a>, ' : '')
            . ($logoJa ? 'freigegeben<br><small>' . esc_html((string) get_post_meta($pid, 'ma_business_logo_freigabe', true)) . '</small>' : 'Rechte <strong>offen</strong>');
        $ban = !empty($f['anzeige']) ? ma_firmen_banner_vorhanden((string) $p['slug']) : 0;
        $banZelle = empty($f['anzeige']) ? '–' : ($ban ? $zelle($ban) . ((string) get_post_meta($ban, 'ma_ad_active', true) === '1' ? ' · läuft' : ' · aus') : 'kommt mit der Einwilligung');
        if (!$logo) $logoJa = true;
        $form = '';
        if ($pid && (!$ja || !$logoJa)) {
            $form = '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ma_firmen_zusage"><input type="hidden" name="firma" value="' . esc_attr((string) $p['slug']) . '">'
                . wp_nonce_field('ma_firmen_zusage', '_wpnonce', true, false)
                . '<label style="display:block">Nachweis <input type="text" name="nachweis" required class="regular-text" placeholder="z. B. E-Mail vom … / Zusage laut Betreiber am …"></label>'
                . (!$ja ? '<label style="display:block"><input type="checkbox" name="einwilligung" value="1"> Das Unternehmen hat der Veröffentlichung von Profil' . ($b ? ' und Werbebeitrag' : '') . (!empty($f['anzeige']) ? ' und Werbebanner' : '') . ' zugestimmt.</label>' : '')
                . (!$logoJa ? '<label style="display:block"><input type="checkbox" name="logo" value="1"> Das Unternehmen hat die Nutzung seines Logos freigegeben.</label>' : '')
                . '<button class="button">Zusage eintragen</button></form>';
        }
        echo '<tr><td><strong>' . esc_html((string) ($p['titel'] ?? '')) . '</strong><br><small>' . esc_html((string) ($p['adresse'] ?? '') ?: (!empty($f['nur_name']) ? 'bisher nur der Name' : '')) . '</small></td><td>' . $zelle($pid) . '</td><td>' . $einw . '</td><td>' . ($b ? $zelle($bid) : '–') . '</td>'
            . '<td>' . $logoZelle . '</td><td>' . $banZelle . '</td><td>' . ($form ?: ($pid ? 'eingetragen' : '–')) . ($pid && get_post_status($pid) === 'publish' ? $zurueck((string) $p['slug'], (string) ($p['titel'] ?? '')) : '') . '</td></tr>';
    }
    echo '</tbody></table>';
    $frueher = (array) ($daten['frueher'] ?? []);
    if ($frueher) {
        echo '<h2 style="margin-top:24px">Früher eingetragen</h2><p>Diese Betriebe sind nicht mehr in der Datei. „Firma zurückziehen“ setzt Profil, Beiträge und Banner auf Entwurf; gelöscht wird nichts.</p><table class="widefat striped"><thead><tr><th>Betrieb</th><th>Profil</th><th>Veröffentlichte Beiträge</th><th></th></tr></thead><tbody>';
        foreach ($frueher as $alt) {
            $slug = (string) ($alt['slug'] ?? ''); $pid = $slug !== '' ? ma_firmen_vorhanden('ma_business', $slug) : 0;
            $n = $pid ? count(get_posts(['post_type' => ['post', 'ma_ad'], 'post_status' => ['publish', 'future', 'pending', 'ma_in_pruefung'], 'posts_per_page' => -1, 'fields' => 'ids', 'meta_query' => [['key' => 'ma_business_id', 'value' => (string) $pid]]])) : 0;
            $aktiv = $pid && get_post_status($pid) !== 'trash' && (get_post_status($pid) !== 'draft' || $n);
            echo '<tr><td><strong>' . esc_html((string) ($alt['titel'] ?? $slug)) . '</strong></td><td>' . ($pid ? esc_html(function_exists('ma_status_name') ? ma_status_name((string) get_post_status($pid)) : (string) get_post_status($pid)) : 'nicht vorhanden') . '</td><td>' . $n . '</td><td>' . ($aktiv ? $zurueck($slug, (string) ($alt['titel'] ?? $slug)) : 'von der Seite genommen') . '</td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top:16px"><input type="hidden" name="action" value="ma_firmen_entwuerfe">';
    wp_nonce_field('ma_firmen_entwuerfe');
    echo '<button class="button button-primary">Fehlende Entwürfe anlegen</button> <span class="description">Legt nur an, was fehlt. Nichts wird veröffentlicht.</span></form></div>';
}
