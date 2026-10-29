<?php
/**
 * Werbeformate mit Preisen (29.10.2026, Entscheidung Betreiber).
 *
 * - Die Redaktion legt unter Werbung → „Formate & Preise“ fest, was buchbar ist:
 *   Name, öffentliche Beschreibung, Plätze, Anzahl Motive, Video, Laufzeit, Preis.
 *   Vorgabe ist das Rundum-Paket wie bei den fünf Firmen (alle Plätze, zwei Motive
 *   mit Foto und Video, Firmenprofil unter „Unternehmen“).
 * - Preise stehen nur im Backend: auf dieser Seite, bei der Anfrage im Eingang,
 *   im Werbemittel und in den Freigaben. Öffentlich bleibt es „Preis auf Anfrage“;
 *   Firmen-Zugänge sehen den Preis ebenfalls nicht.
 * - Ein Format im Werbemittel (Meta ma_ad_format) setzt die Plätze; beim
 *   Freischalten läuft die Anzeige ab dem Start für die Tage des Formats.
 */
if (!defined('ABSPATH')) { exit; }

/** Alle Plätze, die die Seite zeigt (wie bei den Firmenbannern). */
function ma_werbeformate_plaetze(): array {
    return function_exists('ma_firmen_banner_plaetze') ? ma_firmen_banner_plaetze() : ['homepage_band_1', 'homepage_band_2', 'homepage_band_3', 'homepage_band_4', 'homepage_band_5', 'homepage_band_6', 'homepage_sidebar_top', 'homepage_buehne', 'article_inline_1', 'sport_sidebar', 'business_sidebar'];
}

/** Vorgabe, solange nichts gespeichert ist: das Rundum-Paket ohne Preis. */
function ma_werbeformate_vorgabe(): array {
    return ['rundum-paket' => [
        'name' => 'Rundum-Paket',
        'beschreibung' => 'Ihre Anzeige läuft auf allen Werbeplätzen: zwischen den Rubriken der Startseite, in der Bühne ganz oben, unter jeder Meldung und in den Seitenspalten. Dazu kommt ein Firmenprofil unter „Unternehmen“. Zwei Motive mit Foto und kurzer Video-Schleife; ein Klick führt direkt auf Ihre Website.',
        'plaetze' => ma_werbeformate_plaetze(), 'motive' => 2, 'video' => true, 'profil' => true, 'tage' => 30,
        'preis_cent' => 0, 'notiz' => '', 'aktiv' => true,
    ]];
}

/** Ein Format säubern (aus der Option oder dem Formular). */
function ma_werbeformat_saeubern(array $f): array {
    $plaetze = array_values(array_intersect(ma_werbeformate_plaetze(), array_map('strval', (array) ($f['plaetze'] ?? []))));
    return [
        'name' => mb_substr(sanitize_text_field((string) ($f['name'] ?? '')), 0, 80),
        'beschreibung' => mb_substr(sanitize_textarea_field((string) ($f['beschreibung'] ?? '')), 0, 600),
        'plaetze' => $plaetze,
        'motive' => max(1, min(4, (int) ($f['motive'] ?? 1))),
        'video' => !empty($f['video']),
        'profil' => !empty($f['profil']),
        'tage' => max(1, min(365, (int) ($f['tage'] ?? 30))),
        'preis_cent' => max(0, (int) ($f['preis_cent'] ?? 0)),
        'notiz' => mb_substr(sanitize_textarea_field((string) ($f['notiz'] ?? '')), 0, 600),
        'aktiv' => !empty($f['aktiv']),
    ];
}

/** Alle Formate (Schlüssel => Format); ohne gespeicherte Formate die Vorgabe. */
function ma_werbeformate(): array {
    $o = get_option('ma_werbeformate');
    if (!is_array($o) || !$o) return ma_werbeformate_vorgabe();
    $r = [];
    foreach ($o as $k => $f) if (is_array($f) && ($k = sanitize_key((string) $k)) !== '') $r[$k] = ma_werbeformat_saeubern($f);
    return $r ?: ma_werbeformate_vorgabe();
}

/** Nur die buchbaren Formate (aktiv, mit Namen und mindestens einem Platz). */
function ma_werbeformate_aktiv(): array {
    return array_filter(ma_werbeformate(), fn($f) => $f['aktiv'] && $f['name'] !== '' && $f['plaetze']);
}

function ma_werbeformat(string $schluessel): ?array {
    return ma_werbeformate()[sanitize_key($schluessel)] ?? null;
}

/** „490,00 €“ aus Cent; 0 = noch kein Preis. */
function ma_werbeformat_preis(int $cent): string {
    return $cent > 0 ? number_format($cent / 100, 2, ',', '.') . ' €' : '';
}

/** „490“, „490,50“ oder „1.290,00 €“ → Cent. */
function ma_werbeformat_preis_lesen(string $text): int {
    $t = preg_replace('/[^\d,.]/', '', $text);
    if ($t === '' || $t === null) return 0;
    if (preg_match('/,\d{1,2}$/', $t)) $t = str_replace(',', '.', str_replace('.', '', $t));
    elseif (preg_match('/\.\d{3}$/', $t)) $t = str_replace('.', '', $t);
    return (int) round((float) $t * 100);
}

/** Interne Zeile für das Backend: „Rundum-Paket · 30 Tage · 490,00 € netto“. Leer ohne Format. */
function ma_werbeformat_zeile(string $schluessel, bool $mitPreis = false): string {
    $f = ma_werbeformat($schluessel);
    if (!$f) return '';
    $preis = ma_werbeformat_preis($f['preis_cent']);
    return $f['name'] . ' · ' . $f['tage'] . ' Tage' . ($mitPreis ? ' · ' . ($preis !== '' ? $preis . ' netto' : 'noch kein Preis hinterlegt') : '');
}

/** Preise sieht nur die Redaktion, keine Firmen- oder Vereinszugänge. */
function ma_werbeformat_preis_sichtbar(): bool {
    return current_user_can('edit_others_posts') && !(function_exists('ma_current_partner_policy') && ma_current_partner_policy());
}

/**
 * Format auf eine Anzeige anwenden: Plätze (Hauptplatz plus weitere). Das Ende
 * setzt erst das Freischalten (ma_werbeformat_laufzeit). $erlaubt begrenzt die Plätze
 * (Kontingent eines Firmen-Zugangs; [] = kein Platz); null = alle Plätze des Formats.
 */
function ma_werbeformat_anwenden(int $id, string $schluessel, ?array $erlaubt = null): void {
    $f = ma_werbeformat($schluessel);
    if (!$f) return;
    $plaetze = $erlaubt !== null ? array_values(array_intersect($f['plaetze'], $erlaubt)) : $f['plaetze'];
    if (!$plaetze) return;
    update_post_meta($id, 'ma_ad_slot', $plaetze[0]);
    delete_post_meta($id, 'ma_ad_slot_weitere');
    foreach (array_slice($plaetze, 1) as $p) add_post_meta($id, 'ma_ad_slot_weitere', $p);
}

/**
 * Laufzeit beim Freischalten (29.10.2026): Hat eine Anzeige ein Format und noch
 * kein Ende, läuft sie ab dem Start (liegt er in der Vergangenheit oder fehlt er:
 * ab jetzt) für die Tage des Formats. So verkürzt eine spätere Freigabe die
 * gebuchte Laufzeit nicht.
 */
function ma_werbeformat_laufzeit(int $id): void {
    $f = ma_werbeformat((string) get_post_meta($id, 'ma_ad_format', true));
    if (!$f || (string) get_post_meta($id, 'ma_ad_end', true) !== '') return;
    // Start und Ende stehen in Ortszeit (Y-m-d H:i:s); gerechnet wird in „Ortszeit als Zeitstempel“ wie current_time().
    $jetzt = (int) current_time('timestamp');
    $start = strtotime((string) get_post_meta($id, 'ma_ad_start', true)) ?: 0;
    if ($start < $jetzt) { $start = $jetzt; update_post_meta($id, 'ma_ad_start', gmdate('Y-m-d H:i:s', $start)); }
    update_post_meta($id, 'ma_ad_end', gmdate('Y-m-d H:i:s', $start + $f['tage'] * DAY_IN_SECONDS));
}
add_action('transition_post_status', function (string $neu, string $alt, $p): void {
    if (($p->post_type ?? '') === 'ma_ad' && in_array($neu, ['publish', 'future'], true) && $neu !== $alt) ma_werbeformat_laufzeit((int) $p->ID);
}, 10, 3);

/**
 * Formular der Seite „Formate & Preise“ lesen. $zeilen: ['schluessel' => [...]]
 * plus eine neue Zeile unter 'neu'. Gelöschte (loeschen=1) fallen weg; die neue
 * Zeile zählt nur mit Namen. Rückgabe: gesäuberte Formate.
 */
function ma_werbeformate_aus_formular(array $zeilen): array {
    $r = [];
    foreach ($zeilen as $k => $z) {
        if (!is_array($z) || !empty($z['loeschen'])) continue;
        $z['preis_cent'] = ma_werbeformat_preis_lesen((string) ($z['preis'] ?? ''));
        $f = ma_werbeformat_saeubern($z);
        if ($f['name'] === '') continue;
        $k = $k === 'neu' ? sanitize_title($f['name']) : sanitize_key((string) $k);
        if ($k === '') continue;
        while (isset($r[$k])) $k .= '-2';
        $r[$k] = $f;
    }
    return $r;
}

add_action('admin_menu', function (): void {
    add_submenu_page('edit.php?post_type=ma_ad', 'Formate & Preise', 'Formate & Preise', 'manage_options', 'ma-werbeformate', 'ma_werbeformate_seite');
}, 20);

function ma_werbeformate_seite(): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.', 403);
    if (isset($_POST['ma_werbeformate_speichern']) && check_admin_referer('ma_werbeformate')) {
        $neu = ma_werbeformate_aus_formular((array) wp_unslash($_POST['format'] ?? []));
        update_option('ma_werbeformate', $neu ?: ma_werbeformate_vorgabe(), false);
        echo '<div class="notice notice-success"><p>Gespeichert.' . ($neu ? '' : ' Ohne Format gilt wieder die Vorgabe „Rundum-Paket“.') . '</p></div>';
    }
    $formate = ma_werbeformate();
    $labels = fn(string $s): string => function_exists('ma_ad_slot_label') ? ma_ad_slot_label($s) : $s;
    echo '<div class="wrap"><h1>Formate &amp; Preise</h1>';
    echo '<p>Was Firmen buchen können. Name, Beschreibung, Plätze, Motive und Laufzeit erscheinen auf der Seite „Werben“ und im Anfrageformular. <strong>Preis und Notiz sieht nur die Redaktion</strong> (hier, bei der Anfrage im Eingang, im Werbemittel und in den Freigaben); öffentlich steht „Preis auf Anfrage“.</p>';
    echo '<form method="post">';
    wp_nonce_field('ma_werbeformate');
    $zeilen = $formate + ['neu' => ['name' => '', 'beschreibung' => '', 'plaetze' => ma_werbeformate_plaetze(), 'motive' => 2, 'video' => true, 'profil' => true, 'tage' => 30, 'preis_cent' => 0, 'notiz' => '', 'aktiv' => true]];
    foreach ($zeilen as $k => $f) {
        $n = 'format[' . esc_attr($k) . ']';
        echo '<div class="postbox" style="padding:12px 16px;max-width:980px"><h2 style="margin:0 0 8px">' . ($k === 'neu' ? 'Neues Format anlegen' : esc_html($f['name'])) . '</h2><table class="form-table" role="presentation">';
        printf('<tr><th><label>Name</label></th><td><input class="regular-text" name="%s[name]" value="%s"%s></td></tr>', $n, esc_attr($f['name']), $k === 'neu' ? ' placeholder="z. B. Werbeband Startseite"' : ' required');
        printf('<tr><th><label>Beschreibung (öffentlich)</label></th><td><textarea class="large-text" rows="3" name="%s[beschreibung]">%s</textarea></td></tr>', $n, esc_textarea($f['beschreibung']));
        echo '<tr><th>Plätze</th><td><div style="columns:2">';
        foreach (ma_werbeformate_plaetze() as $s) printf('<label style="display:block"><input type="checkbox" name="%s[plaetze][]" value="%s"%s> %s</label>', $n, esc_attr($s), checked(in_array($s, $f['plaetze'], true), true, false), esc_html($labels($s)));
        echo '</div></td></tr>';
        printf('<tr><th>Motive, Laufzeit</th><td><label><input type="number" min="1" max="4" name="%s[motive]" value="%d" style="width:5em"> Motive</label> &nbsp; <label><input type="number" min="1" max="365" name="%s[tage]" value="%d" style="width:6em"> Tage</label> &nbsp; <label><input type="checkbox" name="%s[video]" value="1"%s> mit Video</label> &nbsp; <label><input type="checkbox" name="%s[profil]" value="1"%s> mit Firmenprofil</label></td></tr>',
            $n, $f['motive'], $n, $f['tage'], $n, checked($f['video'], true, false), $n, checked($f['profil'], true, false));
        printf('<tr><th><label>Preis (nur intern)</label></th><td><input name="%s[preis]" value="%s" style="width:10em" placeholder="z. B. 490,00"> € netto für die Laufzeit<p class="description">Leer = noch kein Preis; die Redaktion sieht dann „noch kein Preis hinterlegt“.</p></td></tr>', $n, esc_attr($f['preis_cent'] > 0 ? number_format($f['preis_cent'] / 100, 2, ',', '.') : ''));
        printf('<tr><th><label>Notiz (nur intern)</label></th><td><textarea class="large-text" rows="2" name="%s[notiz]">%s</textarea></td></tr>', $n, esc_textarea($f['notiz']));
        printf('<tr><th>Status</th><td><label><input type="checkbox" name="%s[aktiv]" value="1"%s> buchbar (auf „Werben“ und im Formular)</label>%s</td></tr>', $n, checked($f['aktiv'], true, false),
            $k === 'neu' ? '' : sprintf(' &nbsp; <label><input type="checkbox" name="%s[loeschen]" value="1"> Format löschen</label>', $n));
        echo '</table></div>';
    }
    echo '<p><button class="button button-primary" name="ma_werbeformate_speichern" value="1">Speichern</button></p></form></div>';
}

/* ---------------------------------------------------------- Seite „Werben“ (ohne Preis) */

add_action('init', function (): void {
    add_shortcode('ma_werbeformate', 'ma_werbeformate_shortcode');
    add_shortcode('ma_werbung_beispiel', 'ma_werbung_beispiel_shortcode');
});

/** Karten der buchbaren Formate: Name, Beschreibung, Umfang. Nie ein Preis. */
function ma_werbeformate_shortcode(): string {
    $formate = ma_werbeformate_aktiv();
    if (!$formate) return '<p>Zurzeit stellen wir die Formate neu zusammen. Schreiben Sie uns gern, wir beraten Sie.</p>';
    $h = '<div class="ma-werbeformate">';
    foreach ($formate as $k => $f) {
        $fakten = [
            count($f['plaetze']) >= count(ma_werbeformate_plaetze()) ? 'Alle Werbeplätze: Startseiten-Bänder, Bühne, unter jeder Meldung, Seitenspalten' : count($f['plaetze']) . ' Werbeplätze: ' . implode(', ', array_map(fn($s) => function_exists('ma_ad_slot_label') ? ma_ad_slot_label($s) : $s, $f['plaetze'])),
            $f['motive'] . ' Motiv' . ($f['motive'] > 1 ? 'e, die sich abwechseln' : '') . ', je mit Überschrift, Satz und Foto' . ($f['video'] ? '; auf den großen Plätzen auch als Video-Schleife' : ''),
            'Ein Klick führt direkt auf Ihre Website',
        ];
        if ($f['profil']) $fakten[] = 'Firmenprofil unter „Unternehmen“ mit Logo, Kontakt und eigenen Beiträgen';
        $fakten[] = $f['tage'] . ' Tage Laufzeit, verlängerbar';
        $h .= '<section class="ma-werbeformat" id="format-' . esc_attr($k) . '"><h3>' . esc_html($f['name']) . '</h3>'
            . ($f['beschreibung'] !== '' ? '<p>' . esc_html($f['beschreibung']) . '</p>' : '')
            . '<ul>' . implode('', array_map(fn($t) => '<li>' . esc_html($t) . '</li>', $fakten)) . '</ul>'
            . '<p class="ma-werbeformat__preis">Preis auf Anfrage</p>'
            . '<p><a class="btn" href="' . esc_url(add_query_arg(['art' => 'Werbung', 'format' => 'Paket'], '') . '#anfrage') . '">Dieses Paket anfragen</a></p></section>';
    }
    return $h . '</div>';
}

/**
 * Beispiel aus den laufenden Anzeigen: ein Werbeband, eine Anzeige je Firma,
 * im Wechsel. Ohne Platzkennung, damit Aufrufe hier nicht in die Statistik der
 * Firmen zählen; ein Klick führt wie überall zur Website der Firma.
 */
function ma_werbung_beispiel_shortcode(): string {
    if (!function_exists('ma_active_ads') || !function_exists('ma_ad_karte')) return '';
    $je = [];
    foreach (function_exists('ma_firmen_banner_plaetze') ? ma_firmen_banner_plaetze() : ['homepage_band_1'] as $slot) {
        foreach (ma_active_ads($slot) as $ad) {
            $firma = (string) get_post_meta($ad->ID, 'ma_business_id', true) ?: 'ad-' . $ad->ID;
            if (!isset($je[$firma]) && has_post_thumbnail($ad)) $je[$firma] = $ad;
        }
        if (count($je) >= 6) break;
    }
    if (!$je) return '';
    $items = ''; $i = 0;
    foreach ($je as $ad) $items .= ma_ad_karte($ad, 'band', true, $i++ > 0);
    if (function_exists('wp_enqueue_script')) wp_enqueue_script('ma-ad-rotation', MA_CORE_URL . 'assets/ad-rotation.js', [], MA_CORE_VERSION, true);
    // Format der Artikelanzeige: für die Textspalte der Seite gebaut (wie unter jeder Meldung).
    return '<div class="werbung werbung--artikel werbung--echt werbung--beispiel" aria-label="Beispiel: laufende Anzeigen"><span class="werbung-label">Anzeige</span>'
        . '<div class="werbung-flaeche ma-ad ma-ad-rotator ma-ad-rotator--band"' . (count($je) > 1 ? ' data-ma-ad-rotate="6000" data-ma-ad-count="' . count($je) . '"' : '') . '>' . $items . '</div></div>';
}
