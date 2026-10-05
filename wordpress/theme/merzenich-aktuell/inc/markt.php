<?php
/**
 * Stellenmarkt (/jobs/), Immobilienmarkt (/immobilien/) und Tipps (/tipp/) im
 * neuen Markup. Bis 21.9 liefen sie über die alte Vorlage (archive-alt.php) und
 * zeigten nur „keine veröffentlichten Einträge“, obwohl der redaktionelle Stand
 * im Repository täglich geprüfte Angebote führt (deploy/markt-abgleich.mjs,
 * deploy/markt-prerender.mjs). Vorgabe Betreiber 05.10.2026: keine leeren Seiten.
 *
 * Stellen und Immobilien: zuerst hier aufgegebene Anzeigen (ma_job, ma_property),
 * dann der Marktüberblick aus dem Repository (Block zwischen den Markierungen
 * <!-- markt:jobs:start --> und :end -->, wie auf der statischen Seite).
 * Tipps: bezahlte Tipps (ma_tip, gekennzeichnet), dann die Tipps der Redaktion,
 * die nächsten veröffentlichten Termine.
 */
if (!defined('ABSPATH')) { exit; }

const MA21_MARKT = [
    'ma_job' => [
        'art' => 'jobs', 'crumb' => 'Jobs', 'eyebrow' => 'Stellenmarkt', 'h1' => 'Jobs in Merzenich und Umgebung',
        'desc' => 'Stellen, Ausbildungsplätze und Minijobs von Betrieben, Gemeinde, Kitas und Vereinen aus der Gemeinde Merzenich und dem direkten Umkreis. Jede Anzeige führt zur Originalausschreibung.',
        'einheit' => ['Stelle', 'Stellen'], 'eigene' => 'Hier aufgegebene Stellen',
        'box' => ['Stelle inserieren', 'Stellenanzeigen für Betriebe aus der Gemeinde: 30 Tage im Stellenmarkt und im Ressort Wirtschaft. Ehrenamt und gemeinnützige Träger kostenlos.', '/werben/', 'Anfrage senden'],
    ],
    'ma_property' => [
        'art' => 'immobilien', 'crumb' => 'Immobilienmarkt', 'eyebrow' => 'Anzeigen', 'h1' => 'Immobilienmarkt',
        'desc' => 'Kaufen und mieten in der Gemeinde Merzenich und im direkten Umkreis. Jede Anzeige führt zum Originalangebot.',
        'einheit' => ['Angebot', 'Angebote'], 'eigene' => 'Hier aufgegebene Angebote',
        'box' => ['Immobilie anbieten', 'Haus, Wohnung, Grundstück oder Gewerbefläche verkaufen oder vermieten, auch Gesuche. Die Redaktion prüft vor der Veröffentlichung.', '/anzeigen/aufgeben/?art=Immobilie', 'Anzeige aufgeben'],
    ],
];

/** Marktüberblick aus dem Repository (jobs/index.html bzw. immobilien/index.html), stündlich erneuert, nur erlaubtes HTML. */
function ma21_markt_block(string $art): string {
    $html = ma21_repo_datei($art . '/index.html', HOUR_IN_SECONDS);
    $q = preg_quote($art, '#');
    if (!preg_match('#<!-- markt:' . $q . ':start -->(.*?)<!-- markt:' . $q . ':end -->#s', $html, $m)) return '';
    // Keine Zahlen auf der Seite: „13 Anzeigen“ je Ort entfällt.
    return trim((string) preg_replace('#<span class="markt-ort-count">[^<]*</span>#', '', wp_kses_post($m[1])));
}

/** Zeile für eine hier aufgegebene Anzeige, im Markup des Marktüberblicks. */
function ma21_markt_eigene_zeile(WP_Post $p, string $typ): string {
    $url = esc_url(get_permalink($p));
    $art = $typ === 'ma_job' ? 'Stelle' : ((string) get_post_meta($p->ID, 'ma_property_offer', true) ?: 'Immobilie');
    $text = wp_trim_words(wp_strip_all_tags((string) ($p->post_excerpt ?: $p->post_content)), 32, ' …');
    return '<article class="event-row job-row markt-row"><span class="d job-d"><b>' . esc_html($art) . '</b></span><div class="info">'
        . '<span class="eyebrow"><span class="markt-art">Anzeige · </span>' . esc_html(MA21_ORTE[ma21_ort($p)] ?? 'Merzenich') . '</span>'
        . '<h3><a href="' . $url . '">' . esc_html(get_the_title($p)) . '</a></h3>'
        . ($text !== '' ? '<p class="ev-desc">' . esc_html($text) . '</p>' : '')
        . '<div class="meta"><span>Veröffentlicht ' . esc_html(get_the_date('d.m.Y', $p)) . '</span></div></div>'
        . '<div class="act"><a href="' . $url . '">Ansehen</a></div></article>';
}

/** Ganze Seite Stellen- oder Immobilienmarkt. */
function ma21_markt_seite(string $typ): void {
    $k = MA21_MARKT[$typ];
    $eigene = get_posts(['post_type' => $typ, 'post_status' => 'publish', 'posts_per_page' => 50, 'orderby' => 'date', 'order' => 'DESC']);
    $block = ma21_markt_block($k['art']);
    echo '<div class="page-head"><div class="shell"><nav class="crumbs" aria-label="Brotkrumen"><a href="' . esc_url(home_url('/')) . '">Start</a><span class="sep">›</span><span aria-current="page">' . esc_html($k['crumb']) . '</span></nav>'
        . '<span class="eyebrow">' . esc_html($k['eyebrow']) . '</span><h1>' . esc_html($k['h1']) . '</h1><p class="desc">' . esc_html($k['desc']) . '</p>'
        . '<p class="count-line"><a href="' . esc_url(home_url($k['box'][2])) . '">' . esc_html($k['box'][0]) . '</a></p>'
        . '</div></div>';
    echo '<section class="section"><div class="shell"><div class="content-grid"><div class="event-list markt-liste"><h2 class="sr-only">' . esc_html($k['einheit'][1]) . '</h2>';
    if ($eigene) {
        echo '<h2 class="markt-ort"><span class="markt-ort-eyebrow">Merzenich Aktuell</span>' . esc_html($k['eigene']) . '</h2>';
        foreach ($eigene as $p) echo ma21_markt_eigene_zeile($p, $typ);
    }
    if ($block !== '') echo $block;
    elseif (!$eigene) echo '<p class="no-result">Der Marktüberblick wird gerade aktualisiert und ist in wenigen Minuten wieder da. Eigene Anzeigen nimmt die Redaktion jederzeit an.</p>';
    echo '</div><aside class="sidebar"><div class="sidebox"><h3>' . esc_html($k['box'][0]) . '</h3><p class="p">' . esc_html($k['box'][1]) . '</p><a class="btn block" href="' . esc_url(home_url($k['box'][2])) . '">' . esc_html($k['box'][3]) . '</a></div></aside></div></div></section>';
    echo ma21_werbung('artikel');
}

/** Termine der nächsten Tage für die Tipps (veröffentlicht, Beginn ab jetzt). */
function ma21_tipp_termine(int $tage = 21, int $max = 10): array {
    $jetzt = (string) current_time('Y-m-d\TH:i');
    $bis = (string) wp_date('Y-m-d\T23:59', time() + $tage * DAY_IN_SECONDS);
    $args = ['post_type' => 'ma_event', 'post_status' => 'publish', 'posts_per_page' => $max, 'meta_key' => 'ma_event_start', 'orderby' => 'meta_value', 'order' => 'ASC'];
    $l = get_posts($args + ['meta_query' => [['key' => 'ma_event_start', 'value' => [$jetzt, $bis], 'compare' => 'BETWEEN']]]);
    if (count($l) < 4) $l = get_posts(['posts_per_page' => 6] + $args + ['meta_query' => [['key' => 'ma_event_start', 'value' => $jetzt, 'compare' => '>=']]]);
    return $l;
}

/** Ein Termin als Tipp, im Markup der Terminseite (event-row). */
function ma21_tipp_zeile(WP_Post $p): string {
    $start = function_exists('ma_event_timestamp') ? (int) ma_event_timestamp($p->ID, 'start') : 0;
    $url = esc_url(get_permalink($p));
    $ort = (string) get_post_meta($p->ID, 'ma_event_place', true);
    $zeit = $start && wp_date('H:i', $start) !== '00:00' ? wp_date('H:i', $start) . ' Uhr' : '';
    $text = wp_trim_words(wp_strip_all_tags((string) ($p->post_excerpt ?: $p->post_content)), 34, ' …');
    return '<article class="event-row">'
        . ($start ? '<span class="d"><b>' . esc_html(wp_date('j', $start)) . '</b><span>' . esc_html(wp_date('M', $start)) . '</span></span>' : '<span class="d"><b>–</b></span>')
        . '<div class="info"><span class="eyebrow">' . esc_html(trim(($start ? wp_date('l', $start) . ' · ' : '') . (MA21_ORTE[ma21_ort($p)] ?? 'Merzenich'))) . '</span>'
        . '<h2><a href="' . $url . '">' . esc_html(get_the_title($p)) . '</a></h2>'
        . '<div class="meta">' . ($zeit !== '' ? '<time datetime="' . esc_attr((string) wp_date('c', $start)) . '">' . esc_html($zeit) . '</time>' : '') . ($ort !== '' ? '<span>' . esc_html($ort) . '</span>' : '') . '</div>'
        . ($text !== '' ? '<p class="ev-desc">' . esc_html($text) . '</p>' : '') . '</div>'
        . '<div class="act"><a href="' . $url . '">Details</a></div></article>';
}

/** Seite /tipp/: bezahlte Tipps (gekennzeichnet) und die Tipps der Redaktion aus den Terminen. */
function ma21_tipp_seite(): void {
    $bezahlt = get_posts(['post_type' => 'ma_tip', 'post_status' => 'publish', 'posts_per_page' => 20, 'orderby' => 'date', 'order' => 'DESC']);
    $termine = ma21_tipp_termine();
    $tipp = home_url('/anzeigen/aufgeben/?art=Werbung&format=' . rawurlencode('Tipp (bezahlter Beitrag)'));
    echo '<div class="page-head"><div class="shell"><nav class="crumbs" aria-label="Brotkrumen"><a href="' . esc_url(home_url('/')) . '">Start</a><span class="sep">›</span><span aria-current="page">Tipps</span></nav>'
        . '<span class="eyebrow">Tipps</span><h1>Tipps für Merzenich</h1><p class="desc">Was sich in den nächsten Tagen lohnt: Termine aus der Gemeinde, ausgewählt von der Redaktion. Bezahlte Tipps von Veranstaltern und Betrieben sind als Anzeige gekennzeichnet.</p></div></div>';
    echo '<section class="section"><div class="shell"><div class="content-grid"><div class="event-list">';
    if ($bezahlt) {
        echo '<h2 class="markt-ort"><span class="markt-ort-eyebrow">Anzeige</span>Tipps von Veranstaltern und Betrieben</h2>';
        foreach ($bezahlt as $p) {
            $url = esc_url(get_permalink($p));
            echo '<article class="event-row"><span class="d"><b>Tipp</b></span><div class="info"><span class="eyebrow">Anzeige · ' . esc_html(MA21_ORTE[ma21_ort($p)] ?? 'Merzenich') . '</span><h2><a href="' . $url . '">' . esc_html(get_the_title($p)) . '</a></h2><p class="ev-desc">' . esc_html(wp_trim_words(wp_strip_all_tags((string) ($p->post_excerpt ?: $p->post_content)), 30, ' …')) . '</p></div><div class="act"><a href="' . $url . '">Ansehen</a></div></article>';
        }
    }
    echo '<h2 class="markt-ort"><span class="markt-ort-eyebrow">Redaktion</span>Unsere Tipps für die nächsten Tage</h2>';
    if ($termine) foreach ($termine as $p) echo ma21_tipp_zeile($p);
    else echo '<p class="no-result">In den nächsten Tagen stehen keine Termine im Kalender. Alle Veranstaltungen: <a href="' . esc_url(home_url('/termine/')) . '">Termine</a>.</p>';
    echo '<p class="markt-quellen"><a href="' . esc_url(home_url('/termine/')) . '">Alle Termine</a> · <a href="' . esc_url(home_url('/termine/kalender.ics')) . '">Kalender abonnieren</a> · <a href="' . esc_url(home_url('/termine/melden/')) . '">Termin melden</a></p>';
    echo '</div><aside class="sidebar"><div class="sidebox"><h3>Eigenen Tipp platzieren</h3><p class="p">Veranstaltung, Projekt oder Angebot als Tipp auf Merzenich Aktuell: klar als Anzeige gekennzeichnet und getrennt von den Nachrichten der Redaktion.</p><a class="btn block" href="' . esc_url($tipp) . '">Tipp anfragen</a></div>'
        . '<div class="sidebox"><h3>Kostenlos</h3><p class="p">Vereine, Kirchen und gemeinnützige Gruppen melden ihre Termine kostenlos. Die Redaktion prüft und veröffentlicht sie im Terminkalender.</p><a class="btn ghost block" href="' . esc_url(home_url('/termine/melden/')) . '">Termin melden</a></div></aside></div></div></section>';
    echo ma21_werbung('artikel');
}
