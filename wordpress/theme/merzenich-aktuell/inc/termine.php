<?php
/**
 * Termine im neuen Markup (21.12.0): /termine/ und jede Terminseite wie auf der
 * statischen Seite (deploy/termine.mjs: Liste mit Filtern, Seite mit Faktenblock).
 * Bis 21.11 liefen beide über die alte Vorlage (archive-alt.php, single-service.php)
 * mit eigener Schrift und ohne Brotkrumen; auf dem Handy wirkte das wie eine
 * andere Website (Meldung Betreiber 06.10.2026). Daten aus WordPress (ma_event).
 */
if (!defined('ABSPATH')) { exit; }

const MA21_TAGE = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
const MA21_MON = ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
const MA21_ICON_KAL = '<span class="ef-ic"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span>';
const MA21_ICON_ORT = '<span class="ef-ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s7-7.1 7-12a7 7 0 10-14 0c0 4.9 7 12 7 12z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="2.5" fill="none" stroke="currentColor" stroke-width="2"/></svg></span>';
const MA21_ICON_VER = '<span class="ef-ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a6 6 0 00-6 6v4l-2 4h16l-2-4V8a6 6 0 00-6-6zm-2 17a2 2 0 004 0h-4z"/></svg></span>';

/**
 * Felder eines Termins: Beginn und Ende als Zeitstempel (Ende ohne Angabe = Tagesende
 * des Beginns, „ohneEnde“ merkt sich das), Ort, Ortsteil, Kategorie, Veranstalter, Quelle.
 */
function ma21_termin(WP_Post $p): array {
    $start = function_exists('ma_event_timestamp') ? (int) ma_event_timestamp($p->ID, 'start') : 0;
    $endeRoh = (string) get_post_meta($p->ID, 'ma_event_end', true);
    $ende = $endeRoh !== '' && function_exists('ma_event_timestamp') ? (int) ma_event_timestamp($p->ID, 'end') : 0;
    $ohneEnde = !$ende || $ende <= $start;
    if ($ohneEnde) $ende = $start ? (new DateTimeImmutable('@' . $start))->setTimezone(wp_timezone())->setTime(23, 59, 59)->getTimestamp() : 0;
    $ort = ma21_ort($p);
    $quelle = (string) get_post_meta($p->ID, 'ma_event_source_url', true) ?: (string) get_post_meta($p->ID, 'ma_source_url', true);
    $text = $p->post_excerpt !== '' ? $p->post_excerpt : wp_trim_words(wp_strip_all_tags(strip_shortcodes($p->post_content)), 40, ' …');
    return [
        'post' => $p, 'id' => $p->ID, 'titel' => html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8'),
        'url' => wp_make_link_relative(get_permalink($p)), 'start' => $start, 'ende' => $ende, 'ohneEnde' => $ohneEnde,
        'ort' => (string) get_post_meta($p->ID, 'ma_event_place', true), 'ortSlug' => $ort, 'ortsteil' => MA21_ORTE[$ort] ?? 'Merzenich',
        'kategorie' => (string) get_post_meta($p->ID, 'ma_event_category', true) ?: 'Termin',
        'veranstalter' => (string) get_post_meta($p->ID, 'ma_event_organizer', true),
        'eintritt' => (string) get_post_meta($p->ID, 'ma_event_price', true),
        'anmeldung' => (string) get_post_meta($p->ID, 'ma_event_registration', true),
        'quelle' => $quelle, 'beschreibung' => trim(html_entity_decode((string) $text, ENT_QUOTES, 'UTF-8')),
    ];
}

/** Uhrzeit wie deploy/termine.mjs: „ganztägig“, „18:00 Uhr“, „16:00 Uhr bis 20:00 Uhr“; über mehrere Tage „bis 12.10.“. */
function ma21_termin_zeit(array $t): string {
    if (!$t['start']) return '';
    $hm = wp_date('H:i', $t['start']);
    $mehrtaegig = !$t['ohneEnde'] && wp_date('Ymd', $t['ende']) !== wp_date('Ymd', $t['start']);
    if ($mehrtaegig) return ($hm === '00:00' ? '' : $hm . ' Uhr ') . 'bis ' . wp_date('j.n.', $t['ende']);
    if ($t['ohneEnde']) return $hm === '00:00' ? 'ganztägig' : $hm . ' Uhr';
    return $hm . ' Uhr bis ' . wp_date('H:i', $t['ende']) . ' Uhr';
}

/** Alle veröffentlichten Termine, getrennt in kommende (Ende ab jetzt, nach Beginn) und vergangene (neueste zuerst). */
function ma21_termine_alle(?int $jetzt = null): array {
    $jetzt = $jetzt ?? time();
    $alle = array_map('ma21_termin', get_posts(['post_type' => 'ma_event', 'post_status' => 'publish', 'posts_per_page' => 400, 'meta_key' => 'ma_event_start', 'orderby' => 'meta_value', 'order' => 'ASC', 'no_found_rows' => true]));
    $alle = array_values(array_filter($alle, fn($t) => $t['start'] > 0));
    usort($alle, fn($a, $b) => $a['start'] <=> $b['start'] ?: strcmp($a['titel'], $b['titel']));
    $kommend = array_values(array_filter($alle, fn($t) => $t['ende'] >= $jetzt));
    $vergangen = array_reverse(array_values(array_filter($alle, fn($t) => $t['ende'] < $jetzt)));
    return [$kommend, $vergangen];
}

/**
 * Zeile der Terminliste (Markup wie deploy/termine.mjs, Filter in assets/v20.js
 * über data-*). Seit 21.14.0 mit Foto des Termins (Beitragsbild, sonst keins):
 * /termine/ zeigt die Termine als Bildkarten, wie die Terminübersicht von
 * Oberberg Aktuell (Vorgabe Betreiber 07.10.2026); „Liste“ blendet die Bilder aus.
 */
function ma21_termin_zeile(array $t): string {
    $url = esc_url($t['url']);
    $iso = fn(int $ts): string => gmdate('Y-m-d\TH:i:s.000\Z', $ts);
    $b = ma21_bild($t['post']);
    $bild = $b ? '<a class="event-bild" href="' . $url . '" tabindex="-1" aria-hidden="true"><div class="media">'
        . ma21_img($b, '(max-width: 640px) 92vw, (max-width: 1099px) 46vw, 380px', false) . ma21_badge($b, false) . '</div></a>' : '';
    return '<div data-event-row data-start="' . esc_attr($iso($t['start'])) . '" data-end="' . esc_attr($iso($t['ende'])) . '" data-place="' . esc_attr($t['ortSlug']) . '" data-category="' . esc_attr($t['kategorie']) . '">'
        . '<article class="event-row' . ($b ? ' event-row--bild' : '') . '" id="' . esc_attr($t['post']->post_name) . '">' . $bild
        . '<span class="d"><b>' . esc_html(wp_date('j', $t['start'])) . '</b><span>' . esc_html(MA21_MON[(int) wp_date('n', $t['start']) - 1]) . '</span></span>'
        . '<div class="info"><span class="eyebrow">' . esc_html(MA21_TAGE[(int) wp_date('w', $t['start'])] . ' · ' . $t['kategorie'] . ' · ' . $t['ortsteil']) . '</span>'
        . '<h2><a href="' . $url . '">' . esc_html($t['titel']) . '</a></h2>'
        . '<div class="meta"><time datetime="' . esc_attr($iso($t['start'])) . '">' . esc_html(ma21_termin_zeit($t)) . '</time>'
        . ($t['ort'] !== '' ? '<span>' . esc_html($t['ort']) . '</span>' : '') . ($t['veranstalter'] !== '' ? '<span>' . esc_html($t['veranstalter']) . '</span>' : '') . '</div>'
        . ($t['beschreibung'] !== '' ? '<p class="ev-desc">' . esc_html($t['beschreibung']) . '</p>' : '') . '</div>'
        . '<div class="act"><a href="' . $url . '">Details</a><a href="' . esc_url(trailingslashit($t['url']) . 'termin.ics') . '" download>Kalender</a>'
        . ($t['quelle'] !== '' ? '<a href="' . esc_url($t['quelle']) . '" target="_blank" rel="noopener nofollow">Quelle ↗</a>' : '') . '</div>'
        . '</article></div>';
}

/** Ganze Seite /termine/ (archive-ma_event.php). */
function ma21_termine_seite(): void {
    [$kommend, $vergangen] = ma21_termine_alle();
    $kategorien = array_values(array_unique(array_map(fn($t) => $t['kategorie'], $kommend)));
    sort($kategorien, SORT_LOCALE_STRING);
    $n = count($kommend);
    echo '<div class="page-head"><div class="shell"><nav class="crumbs" aria-label="Brotkrumen"><a href="' . esc_url(home_url('/')) . '">Start</a><span class="sep">›</span><span aria-current="page">Termine</span></nav>'
        . '<span class="eyebrow">Kalender</span><h1>Heute &amp; die nächsten Tage</h1><p class="desc">Feste, Kultur, Sport und Vereinsleben in Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald.</p>'
        . '<p class="count-line">' . (ma21_heute_da() ? '<a href="' . esc_url(home_url('/heute/')) . '">Was ist heute los?</a> · ' : '') . '<a href="' . esc_url(home_url('/termine/kalender.ics')) . '">Kalender abonnieren</a> · <a href="' . esc_url(home_url('/termin-melden/')) . '">Termin melden</a></p></div></div>';
    echo '<section class="section"><div class="shell"><div class="filter-controls" data-event-filters>'
        . '<div class="period-tabs" aria-label="Zeitraum"><button type="button" data-period="all" aria-pressed="true">Alle</button><button type="button" data-period="today" aria-pressed="false">Heute</button><button type="button" data-period="weekend" aria-pressed="false">Wochenende</button><button type="button" data-period="14" aria-pressed="false">Nächste 14 Tage</button></div>'
        . '<label>Ort<select data-event-place><option value="">Alle Ortsteile</option>';
    foreach (MA21_ORTE as $slug => $name) echo '<option value="' . esc_attr($slug) . '">' . esc_html($name) . '</option>';
    echo '</select></label><label>Kategorie<select data-event-category><option value="">Alle Kategorien</option>';
    foreach ($kategorien as $k) echo '<option>' . esc_html($k) . '</option>';
    echo '</select></label>'
        . '<div class="ansicht-tabs" role="group" aria-label="Ansicht"><button type="button" data-ansicht="bilder" aria-pressed="true">Bilder</button><button type="button" data-ansicht="liste" aria-pressed="false">Liste</button></div></div>';
    echo '<p class="count-line" data-event-count aria-live="polite">' . $n . ($n === 1 ? ' Termin' : ' Termine') . '</p>';
    echo '<div class="event-list event-list--bilder" data-ansicht-ziel>';
    foreach ($kommend as $t) echo ma21_termin_zeile($t);
    echo '<p class="no-result" data-event-empty' . ($n ? ' hidden' : '') . '>' . ($n ? 'Keine Termine für diese Auswahl. Wählen Sie einen anderen Zeitraum oder Ort.' : 'Gerade sind keine Termine eingetragen. Vereine und Gruppen melden ihre Termine kostenlos: <a href="' . esc_url(home_url('/termin-melden/')) . '">Termin melden</a>.') . '</p></div>';
    if ($vergangen) {
        echo '<details class="past"><summary>Vergangene Termine</summary><ul class="archive-list">';
        foreach (array_slice($vergangen, 0, 40) as $t) echo '<li><time datetime="' . esc_attr(gmdate('Y-m-d\TH:i:s.000\Z', $t['start'])) . '">' . esc_html(wp_date('d.m.Y', $t['start'])) . '</time><a href="' . esc_url($t['url']) . '">' . esc_html($t['titel']) . '</a></li>';
        echo '</ul></details>';
    }
    echo '</div></section>';
    echo ma21_werbung('artikel');
}

/** Terminseite (single-ma_event.php), Markup wie deploy/termine.mjs seiteAusDaten(). */
function ma21_termin_einzel(WP_Post $p): void {
    $t = ma21_termin($p);
    $vorbei = $t['ende'] && $t['ende'] < time();
    [$kommend] = ma21_termine_alle();
    $weitere = array_slice(array_values(array_filter($kommend, fn($x) => $x['id'] !== $t['id'])), 0, 5);
    $kartenOrt = trim($t['ort'] . ', ' . $t['ortsteil'] . ', 52399 Merzenich', ', ');
    $bild = function_exists('ma_content_image') ? ma_content_image($p, 'large') : [];
    echo '<article class="article event-page"><div class="article-head"><div class="shell">'
        . '<nav class="crumbs" aria-label="Brotkrumen"><a href="' . esc_url(home_url('/')) . '">Start</a><span class="sep">›</span><a href="' . esc_url(home_url('/termine/')) . '">Termine</a><span class="sep">›</span><span aria-current="page">' . esc_html($t['titel']) . '</span></nav>'
        . '<div class="kick-row"><span class="kicker">' . esc_html($t['kategorie']) . '<span class="dist">' . esc_html($t['ortsteil']) . '</span></span></div>'
        . '<h1>' . esc_html($t['titel']) . '</h1>'
        . ($p->post_excerpt !== '' ? '<p class="dek">' . esc_html(html_entity_decode($p->post_excerpt, ENT_QUOTES, 'UTF-8')) . '</p>' : '')
        . '</div></div><div class="shell article-grid"><div class="article-body">';
    if ($vorbei) echo '<p class="termin-vorbei"><strong>Dieser Termin ist vorbei.</strong> Kommende Veranstaltungen stehen unter <a href="' . esc_url(home_url('/termine/')) . '">Termine</a>.</p>';
    echo '<div class="event-facts">';
    if ($t['start']) echo '<div class="ef">' . MA21_ICON_KAL . '<div><b>' . esc_html(MA21_TAGE[(int) wp_date('w', $t['start'])] . ', ' . wp_date('d.m.Y', $t['start'])) . '</b><span>' . esc_html(ma21_termin_zeit($t)) . '</span></div></div>';
    echo '<div class="ef">' . MA21_ICON_ORT . '<div><b>' . esc_html($t['ort'] !== '' ? $t['ort'] : $t['ortsteil']) . '</b><span>' . esc_html($t['ortsteil']) . '</span></div></div>';
    if ($t['veranstalter'] !== '') echo '<div class="ef">' . MA21_ICON_VER . '<div><b>' . esc_html($t['veranstalter']) . '</b><span>Veranstalter</span></div></div>';
    echo '</div>';
    echo '<div class="cta-row">'
        . (!$vorbei && $t['start'] ? '<a class="btn" href="' . esc_url(trailingslashit($t['url']) . 'termin.ics') . '" download>In den Kalender</a>' : '')
        . '<a class="btn ghost" href="' . esc_url('https://www.openstreetmap.org/search?query=' . rawurlencode($kartenOrt)) . '" target="_blank" rel="noopener">Karte ↗</a>'
        . ($t['quelle'] !== '' ? '<a class="btn ghost" href="' . esc_url($t['quelle']) . '" target="_blank" rel="noopener nofollow">Quelle ↗</a>' : '')
        . '</div>';
    if (!empty($bild['url'])) {
        echo '<figure class="art-figure' . (!empty($bild['is_fallback']) ? ' art-figure--symbol' : '') . '"><div class="media"><img src="' . esc_url($bild['url']) . '" alt="' . esc_attr((string) ($bild['alt'] ?? '')) . '" loading="lazy" decoding="async"></div>'
            . '<figcaption><span>' . (!empty($bild['is_fallback']) ? '<span class="figure-badge">Symbolbild</span> · Kein Foto der Veranstaltung. ' : '') . esc_html(function_exists('ma_image_caption') ? ma_image_caption($bild) : (string) ($bild['credit'] ?? '')) . '</span></figcaption></figure>';
    }
    $inhalt = apply_filters('the_content', $p->post_content);
    if (trim(wp_strip_all_tags($inhalt)) !== '') echo '<div class="prose">' . $inhalt . '</div>';
    if ($t['eintritt'] !== '' || $t['anmeldung'] !== '') {
        echo '<div class="prose">' . ($t['eintritt'] !== '' ? '<p><b>Eintritt:</b> ' . esc_html($t['eintritt']) . '</p>' : '') . ($t['anmeldung'] !== '' ? '<p><b>Anmeldung und Hinweise:</b> ' . nl2br(esc_html($t['anmeldung'])) . '</p>' : '') . '</div>';
    }
    echo ma21_werbung('artikel');
    echo '<div class="source-box"><b>Termindaten.</b> '
        . ($t['quelle'] !== '' ? 'Quelle: <a href="' . esc_url($t['quelle']) . '" target="_blank" rel="noopener nofollow">' . esc_html((string) parse_url($t['quelle'], PHP_URL_HOST)) . ' ↗</a>. ' : '')
        . 'Änderungen bitte an <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a> oder über <a href="' . esc_url(home_url('/termin-melden/')) . '">Termin melden</a>.</div>';
    echo '</div><aside class="sidebar"><h2 class="sr-only">Weitere Inhalte</h2><div class="sidebox"><h3>Weitere Termine<a href="' . esc_url(home_url('/termine/')) . '">alle</a></h3>';
    if ($weitere) {
        echo '<ul>';
        foreach ($weitere as $x) echo '<li class="termin"><span class="d"><b>' . esc_html(wp_date('j', $x['start'])) . '</b><span>' . esc_html(MA21_MON[(int) wp_date('n', $x['start']) - 1]) . '</span></span><span class="t"><a href="' . esc_url($x['url']) . '">' . esc_html($x['titel']) . '</a><small>' . esc_html(implode(' · ', array_filter([ma21_termin_zeit($x), $x['ort']]))) . '</small></span></li>';
        echo '</ul>';
    } else {
        echo '<p>Derzeit keine weiteren Termine. <a href="' . esc_url(home_url('/termin-melden/')) . '">Termin melden</a></p>';
    }
    echo '</div></aside></div></article>';
}

/* ------------------------------------------------------------ Heute in Merzenich (/heute/, 21.17.0) */

/**
 * Seite „Heute in Merzenich“: antwortet auf die Google-Frage „Was ist heute in
 * Merzenich los?“ (Rückmeldung Betreiber 07.10.2026). Die Seite legt das Plugin
 * an (includes/seiten.php, Slug heute); page-heute.php zeigt statt ihres Textes
 * die Termine und die neuesten Meldungen aus WordPress.
 *
 * Auswahl aus den kommenden Terminen (ma21_termine_alle): heute = beginnt vor
 * Mitternacht und ist noch nicht vorbei (auch mehrtägige); woche = beginnt
 * morgen bis einschließlich in sieben Tagen; naechste = die nächsten fünf
 * danach, nur wenn heute und woche leer sind.
 */
function ma21_heute_auswahl(array $termine, int $jetzt, ?DateTimeZone $tz = null): array {
    $tag = (new DateTimeImmutable('@' . $jetzt))->setTimezone($tz ?? wp_timezone())->setTime(0, 0);
    $morgen = $tag->modify('+1 day')->getTimestamp();
    $bis = $tag->modify('+8 days')->getTimestamp();
    $termine = array_values(array_filter($termine, fn($t) => (int) $t['start'] > 0 && (int) $t['ende'] >= $jetzt));
    usort($termine, fn($a, $b) => $a['start'] <=> $b['start'] ?: strcmp((string) ($a['titel'] ?? ''), (string) ($b['titel'] ?? '')));
    $heute = array_values(array_filter($termine, fn($t) => $t['start'] < $morgen));
    $woche = array_values(array_filter($termine, fn($t) => $t['start'] >= $morgen && $t['start'] < $bis));
    $naechste = $heute || $woche ? [] : array_slice(array_values(array_filter($termine, fn($t) => $t['start'] >= $bis)), 0, 5);
    return ['heute' => $heute, 'woche' => $woche, 'naechste' => $naechste];
}

/** „Mittwoch, 7. Oktober 2026“. */
function ma21_heute_datum(int $ts): string {
    $monate = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
    return MA21_TAGE[(int) wp_date('w', $ts)] . ', ' . wp_date('j', $ts) . '. ' . $monate[(int) wp_date('n', $ts) - 1] . ' ' . wp_date('Y', $ts);
}

/** Kopf eines Abschnitts wie auf der statischen Seite (section-head mit Dachzeile und „alle“-Link). */
function ma21_heute_abschnitt(string $dach, string $titel, string $mehrUrl = '', string $mehrText = ''): string {
    return '<div class="section-head"><div class="left"><span class="eyebrow">' . esc_html($dach) . '</span><h2>' . esc_html($titel) . '</h2></div>'
        . ($mehrUrl !== '' ? '<a class="more" href="' . esc_url(home_url($mehrUrl)) . '">' . esc_html($mehrText) . '</a>' : '') . '</div>';
}

/** Meldungen von heute, aufgefüllt mit den neuesten bis fünf; [Beiträge, ob es heutige gibt]. */
function ma21_heute_meldungen(int $jetzt): array {
    $heute = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 8, 'no_found_rows' => true,
        'date_query' => [['after' => wp_date('Y-m-d 00:00:00', $jetzt), 'inclusive' => true]]]);
    $mehr = count($heute) < 5 ? get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 5 - count($heute), 'no_found_rows' => true,
        'post__not_in' => array_map(fn($p) => $p->ID, $heute)]) : [];
    return [array_merge($heute, $mehr), (bool) $heute];
}

/** Inhalt von /heute/ (page-heute.php). */
function ma21_heute_seite(WP_Post $seite): void {
    $jetzt = time();
    [$kommend] = ma21_termine_alle($jetzt);
    $a = ma21_heute_auswahl($kommend, $jetzt);
    [$meldungen, $neuHeute] = ma21_heute_meldungen($jetzt);
    $liste = fn(array $termine): string => '<div class="event-list event-list--bilder heute-termine">' . implode('', array_map('ma21_termin_zeile', $termine)) . '</div>';
    echo '<div class="page-head"><div class="shell"><nav class="crumbs" aria-label="Brotkrumen"><a href="' . esc_url(home_url('/')) . '">Start</a><span class="sep">›</span><span aria-current="page">' . esc_html(get_the_title($seite)) . '</span></nav>'
        . '<span class="eyebrow">Heute</span><h1>' . esc_html(get_the_title($seite)) . '</h1>'
        . ($seite->post_excerpt !== '' ? '<p class="desc">' . esc_html(html_entity_decode($seite->post_excerpt, ENT_QUOTES, 'UTF-8')) . '</p>' : '')
        . '<p class="count-line"><time datetime="' . esc_attr(wp_date('Y-m-d', $jetzt)) . '">' . esc_html(ma21_heute_datum($jetzt)) . '</time> · <a href="' . esc_url(home_url('/termine/')) . '">Alle Termine</a> · <a href="' . esc_url(home_url('/termin-melden/')) . '">Termin melden</a></p></div></div>';
    echo '<section class="section heute"><div class="shell">';
    echo ma21_heute_abschnitt('Kalender', 'Termine heute', '/termine/', 'Alle Termine');
    echo $a['heute'] ? $liste($a['heute']) : '<p class="no-result">Für heute steht kein Termin im Kalender. Vereine und Gruppen melden ihre Termine kostenlos: <a href="' . esc_url(home_url('/termin-melden/')) . '">Termin melden</a>.</p>';
    if ($a['woche']) echo ma21_heute_abschnitt('Vorschau', 'Die nächsten sieben Tage') . $liste($a['woche']);
    if ($a['naechste']) echo ma21_heute_abschnitt('Vorschau', 'Die nächsten Termine') . $liste($a['naechste']);
    if ($meldungen) {
        echo ma21_heute_abschnitt('Nachrichten', $neuHeute ? 'Neu heute auf Merzenich Aktuell' : 'Die neuesten Meldungen', '/nachrichten/', 'Alle Nachrichten');
        echo '<div class="heute-meldungen">' . implode("\n", array_map(fn($p) => ma21_feed_row($p), $meldungen)) . '</div>';
    }
    echo '<p class="heute-weiter">Mehr aus der Gemeinde: <a href="' . esc_url(home_url('/blaulicht/')) . '">Blaulicht</a> · <a href="' . esc_url(home_url('/rathaus/')) . '">Rathaus &amp; Politik</a> · <a href="' . esc_url(home_url('/vereine/')) . '">Vereine</a> · <a href="' . esc_url(home_url('/service/')) . '">Notdienste &amp; Rathaus</a></p>';
    echo '</div></section>';
    echo ma21_werbung('artikel');
}

/** Gibt es die Seite /heute/ schon (das Plugin legt sie beim ersten Öffnen des Backends an)? */
function ma21_heute_da(): bool {
    static $da = null;
    if ($da === null) $da = function_exists('get_page_by_path') && get_page_by_path('heute', OBJECT, 'page') instanceof WP_Post;
    return $da;
}

/** Links auf /heute/: im Kopf ins Mehr-Menü und in die Schublade (Gruppe Service), im Fuß nach „Termine“. */
function ma21_heute_links(string $html, string $wo, ?bool $da = null): string {
    if (!($da ?? ma21_heute_da()) || str_contains($html, 'href="/heute/"')) return $html;
    if ($wo === 'fuss') return str_replace('<h3>Nachrichten</h3><a href="/nachrichten/">Aktuell</a><a href="/blaulicht/">Blaulicht</a><a href="/sport/">Sport</a><a href="/termine/">Termine</a>', '<h3>Nachrichten</h3><a href="/nachrichten/">Aktuell</a><a href="/blaulicht/">Blaulicht</a><a href="/sport/">Sport</a><a href="/termine/">Termine</a><a href="/heute/">Heute in Merzenich</a>', $html);
    $html = str_replace('<li><a href="/service/">', '<li><a href="/heute/">Heute in Merzenich</a></li><li><a href="/service/">', $html);
    return str_replace('<div class="grp">Service</div>', '<div class="grp">Service</div><a href="/heute/">Heute in Merzenich</a>', $html);
}
