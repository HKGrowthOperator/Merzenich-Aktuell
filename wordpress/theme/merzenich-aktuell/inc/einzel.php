<?php
/**
 * Einzelseiten von Stellen, Immobilien, Trauer- und Familienanzeigen, Tipps und
 * Unternehmensprofilen im neuen Markup (21.13.0), wie Meldung und Terminseite:
 * Brotkrumen, Kicker, Faktenblock, Knöpfe, Bild, Text, rechts „Weitere“.
 * Bis 21.12 liefen sie über template-parts/single-service.php, single-ma_tip.php
 * und single-ma_business.php mit der alten style.css (Arial/Georgia, keine
 * Brotkrumen), obwohl die Listen schon neu waren (Abnahme-Check 06.10.2026).
 */
if (!defined('ABSPATH')) { exit; }

const MA21_EINZEL = [
    'ma_job' => ['liste' => '/jobs/', 'crumb' => 'Jobs', 'art' => 'Stelle', 'weitere' => 'Weitere Stellen', 'aufgeben' => ['Stelle inserieren', '/anzeigen/aufgeben/?art=Stellenanzeige']],
    'ma_property' => ['liste' => '/immobilien/', 'crumb' => 'Immobilienmarkt', 'art' => 'Immobilie', 'weitere' => 'Weitere Angebote', 'aufgeben' => ['Immobilie anbieten', '/anzeigen/aufgeben/?art=Immobilie']],
    'ma_obituary' => ['liste' => '/traueranzeigen/', 'crumb' => 'Traueranzeigen', 'art' => 'Traueranzeige', 'weitere' => 'Weitere Traueranzeigen', 'aufgeben' => ['Traueranzeige aufgeben', '/anzeigen/aufgeben/?art=Traueranzeige']],
    'ma_family_notice' => ['liste' => '/familienanzeigen/', 'crumb' => 'Familienanzeigen', 'art' => 'Familienanzeige', 'weitere' => 'Weitere Familienanzeigen', 'aufgeben' => ['Familienanzeige aufgeben', '/anzeigen/aufgeben/?art=Familienanzeige']],
    'ma_tip' => ['liste' => '/tipp/', 'crumb' => 'Tipps', 'art' => 'Tipp', 'weitere' => 'Weitere Tipps', 'aufgeben' => ['Tipp anfragen', '/anzeigen/aufgeben/?art=Werbung&format=Tipp%20(bezahlter%20Beitrag)']],
    'ma_business' => ['liste' => '/betriebe/', 'crumb' => 'Lokale Betriebe', 'art' => 'Betrieb', 'weitere' => 'Weitere Betriebe', 'aufgeben' => ['Betrieb eintragen', '/betriebe/#partner-antrag']],
];

/** Datum aus dem Backend (JJJJ-MM-TT oder Freitext) deutsch: 12.04.1938; Freitext bleibt. */
function ma21_datum_de(string $wert): string {
    $wert = trim($wert);
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $wert, $m)) return $m[3] . '.' . $m[2] . '.' . $m[1];
    return $wert;
}

/** Faktenzeilen, Knöpfe, Hinweise und Kicker je Art. Leere Werte fallen weg. */
function ma21_einzel_daten(WP_Post $p): array {
    $id = $p->ID; $m = fn(string $k): string => trim((string) get_post_meta($id, $k, true));
    $fakten = []; $knoepfe = []; $hinweise = []; $kicker = MA21_EINZEL[$p->post_type]['art'] ?? 'Anzeige'; $quelle = ''; $gesponsert = '';
    switch ($p->post_type) {
        case 'ma_job':
            $art = function_exists('ma_theme_job_provider_label') ? ma_theme_job_provider_label($m('ma_job_provider_type')) : '';
            $fakten = ['Unternehmen' => $m('ma_job_company'), 'Inseriert von' => $art === 'Personaldienstleister' && $m('ma_job_provider') !== '' ? $art . ': ' . $m('ma_job_provider') : $art,
                'Arbeitsort' => $m('ma_job_location'), 'Adresse' => $m('ma_job_address'), 'Beschäftigungsart' => function_exists('ma_theme_job_type_label') ? ma_theme_job_type_label($m('ma_job_type')) : '',
                'Arbeitszeit' => $m('ma_job_hours'), 'Beginn' => ma21_datum_de($m('ma_job_start_date')), 'Ansprechpartner' => $m('ma_job_contact')];
            $quelle = $m('ma_job_apply_url');
            if ($quelle !== '') $knoepfe[] = ['Zur Bewerbung ↗', $quelle, true];
            break;
        case 'ma_property':
            $fakten = ['Angebot' => $m('ma_property_offer'), 'Preis' => $m('ma_property_price'), 'Warmmiete' => $m('ma_property_warm_price'), 'Zimmer' => $m('ma_property_rooms'),
                'Wohnfläche' => $m('ma_property_area'), 'Grundstück' => $m('ma_property_lot'), 'Verfügbar ab' => ma21_datum_de($m('ma_property_available_from')),
                'Lage' => $m('ma_property_address'), 'Anbieter' => $m('ma_property_provider'), 'Kontakt' => $m('ma_property_contact')];
            $quelle = $m('ma_property_url');
            if ($quelle !== '') $knoepfe[] = ['Zum Angebot ↗', $quelle, true];
            break;
        case 'ma_obituary':
            $leben = trim(ma21_datum_de($m('ma_obituary_birth')) . ' – ' . ma21_datum_de($m('ma_obituary_death')), ' –');
            $fakten = ['Name' => $m('ma_obituary_name'), 'Lebensdaten' => $leben, 'Ort' => $m('ma_obituary_place')];
            if ($m('ma_obituary_funeral') !== '') $hinweise['Trauerfeier und Bestattung'] = $m('ma_obituary_funeral');
            if ($m('ma_obituary_family') !== '') $hinweise['Im Namen der Familie'] = $m('ma_obituary_family');
            break;
        case 'ma_family_notice':
            $kicker = function_exists('ma_theme_family_kind_label') ? ma_theme_family_kind_label($m('ma_family_kind')) : 'Familienanzeige';
            $fakten = ['Anlass' => $kicker, 'Datum' => ma21_datum_de($m('ma_family_date')), 'Ort' => $m('ma_family_place')];
            break;
        case 'ma_tip':
            $kicker = ['tipp' => 'Tipp', 'sponsoring' => 'Sponsoring', 'anzeige' => 'Anzeige'][$m('ma_tip_kind')] ?? 'Tipp';
            $gesponsert = $m('ma_tip_sponsor');
            $fakten = ['Art' => $kicker . ' · bezahlte Platzierung', 'Auftraggeber' => $gesponsert];
            if ($m('ma_tip_url') !== '') $knoepfe[] = ['Zum Angebot ↗', $m('ma_tip_url'), true];
            break;
        case 'ma_business':
            $b = function_exists('ma_business_profile') ? (array) ma_business_profile($id) : [];
            $kicker = (string) ($b['branche'] ?? '') ?: 'Betrieb';
            $fakten = ['Branche' => (string) ($b['branche'] ?? ''), 'Adresse' => (string) ($b['adresse'] ?? ''), 'Telefon' => (string) ($b['telefon'] ?? '')];
            if (!empty($b['oeffnungszeiten'])) $hinweise['Öffnungszeiten'] = (string) $b['oeffnungszeiten'];
            if (!empty($b['telefon_href'])) $knoepfe[] = ['Anrufen', (string) $b['telefon_href'], false];
            if (!empty($b['website'])) $knoepfe[] = ['Website ↗', (string) $b['website'], true];
            break;
    }
    return ['fakten' => array_filter($fakten, fn($v) => trim((string) $v) !== ''), 'knoepfe' => $knoepfe, 'hinweise' => $hinweise, 'kicker' => $kicker, 'quelle' => $quelle, 'gesponsert' => $gesponsert];
}

/** Ganze Einzelseite. */
function ma21_anzeige_einzel(WP_Post $p): void {
    $k = MA21_EINZEL[$p->post_type] ?? MA21_EINZEL['ma_job'];
    $d = ma21_einzel_daten($p);
    $ort = MA21_ORTE[ma21_ort($p)] ?? 'Merzenich';
    $titel = get_the_title($p);
    echo '<article class="article event-page anzeige-page anzeige-page--' . esc_attr(str_replace('ma_', '', $p->post_type)) . '"><div class="article-head"><div class="shell">'
        . '<nav class="crumbs" aria-label="Brotkrumen"><a href="' . esc_url(home_url('/')) . '">Start</a><span class="sep">›</span><a href="' . esc_url(home_url($k['liste'])) . '">' . esc_html($k['crumb']) . '</a><span class="sep">›</span><span aria-current="page">' . esc_html($titel) . '</span></nav>'
        . '<div class="kick-row"><span class="kicker">' . esc_html($d['kicker']) . '<span class="dist">' . esc_html($ort) . '</span></span></div>'
        . '<h1>' . esc_html($titel) . '</h1>'
        . ($p->post_excerpt !== '' ? '<p class="dek">' . esc_html(html_entity_decode($p->post_excerpt, ENT_QUOTES, 'UTF-8')) . '</p>' : '')
        . ($p->post_type === 'ma_tip' ? '<p class="gesponsert-hinweis"><span class="gesponsert">Anzeige · Gesponsert</span> Bezahlte Platzierung' . ($d['gesponsert'] !== '' ? ' von ' . esc_html($d['gesponsert']) : '') . ', kein redaktioneller Beitrag.</p>' : '')
        . '</div></div><div class="shell article-grid"><div class="article-body">';
    if ($d['fakten']) {
        echo '<dl class="anzeige-fakten">';
        foreach ($d['fakten'] as $label => $wert) echo '<div><dt>' . esc_html($label) . '</dt><dd>' . esc_html((string) $wert) . '</dd></div>';
        echo '</dl>';
    }
    if ($d['knoepfe']) {
        echo '<div class="cta-row">';
        foreach ($d['knoepfe'] as $i => [$text, $url, $extern]) echo '<a class="btn' . ($i ? ' ghost' : '') . '" href="' . esc_url($url, ['http', 'https', 'tel', 'mailto']) . '"' . ($extern ? ' target="_blank" rel="noopener' . ($p->post_type === 'ma_tip' ? ' sponsored' : '') . '"' : '') . '>' . esc_html($text) . '</a>';
        echo '</div>';
    }
    // Bild: Traueranzeigen ohne Symbolbild; Betriebe nur mit eigenem Bild.
    $bild = function_exists('ma_content_image') ? ma_content_image($p, 'large') : [];
    $zeigeBild = !empty($bild['url']) && $p->post_type !== 'ma_obituary' && !($p->post_type === 'ma_business' && !empty($bild['is_fallback']));
    if ($zeigeBild) {
        echo '<figure class="art-figure' . (!empty($bild['is_fallback']) ? ' art-figure--symbol' : '') . '"><div class="media"><img src="' . esc_url($bild['url']) . '" alt="' . esc_attr((string) ($bild['alt'] ?? '')) . '" loading="lazy" decoding="async"></div>'
            . '<figcaption><span>' . (!empty($bild['is_fallback']) ? '<span class="figure-badge">Symbolbild</span> · ' : '') . esc_html(function_exists('ma_image_caption') ? ma_image_caption($bild) : (string) ($bild['credit'] ?? '')) . '</span></figcaption></figure>';
    }
    $inhalt = apply_filters('the_content', $p->post_content);
    if (trim(wp_strip_all_tags($inhalt)) !== '') echo '<div class="prose">' . $inhalt . '</div>';
    foreach ($d['hinweise'] as $label => $text) echo '<div class="source-box anzeige-hinweis"><b>' . esc_html($label) . '</b><p>' . nl2br(esc_html($text)) . '</p></div>';
    echo ma21_werbung('artikel');
    // Kennzeichnung bzw. Herkunft: Prüfzeile nur, wenn es eine Quelle gibt.
    if ($p->post_type === 'ma_tip') echo '<div class="source-box"><b>Werbliche Kennzeichnung.</b> ' . esc_html($d['kicker']) . ' · bezahlte Platzierung' . ($d['gesponsert'] !== '' ? ' · Auftraggeber: ' . esc_html($d['gesponsert']) : '') . '. Inhalte von Auftraggebern sind getrennt von den Nachrichten der Redaktion.</div>';
    elseif ($p->post_type === 'ma_business') echo '<div class="source-box"><b>Angaben.</b> Veröffentlicht mit Einwilligung des Betriebs. Änderungen bitte an <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a>.</div>';
    elseif ($d['quelle'] !== '' && function_exists('ma_theme_verified_line')) echo '<div class="source-box"><b>Herkunft.</b> Originalanzeige: <a href="' . esc_url($d['quelle']) . '" target="_blank" rel="noopener nofollow">' . esc_html((string) parse_url($d['quelle'], PHP_URL_HOST)) . ' ↗</a>. ' . esc_html(ma_theme_verified_line($p->ID)) . '.</div>';
    else echo '<div class="source-box"><b>Anzeige.</b> Aufgegeben bei Merzenich Aktuell und vor der Veröffentlichung von der Redaktion geprüft. Veröffentlicht am ' . esc_html(get_the_date('d.m.Y', $p)) . '.</div>';
    // Rechts: weitere Einträge derselben Art und der Weg zur eigenen Anzeige.
    $weitere = get_posts(['post_type' => $p->post_type, 'post_status' => 'publish', 'posts_per_page' => 5, 'post__not_in' => [$p->ID], 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true]);
    echo '</div><aside class="sidebar"><h2 class="sr-only">Weitere Inhalte</h2><div class="sidebox"><h3>' . esc_html($k['weitere']) . '<a href="' . esc_url(home_url($k['liste'])) . '">alle</a></h3>';
    if ($weitere) { echo '<ul class="linklist">'; foreach ($weitere as $w) echo '<li><a href="' . esc_url(get_permalink($w)) . '">' . esc_html(get_the_title($w)) . '</a></li>'; echo '</ul>'; }
    echo '<p><a class="btn ghost block" href="' . esc_url(home_url($k['aufgeben'][1])) . '">' . esc_html($k['aufgeben'][0]) . '</a></p></div></aside></div></article>';
}
