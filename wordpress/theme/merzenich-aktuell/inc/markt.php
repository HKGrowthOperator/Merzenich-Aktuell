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

/**
 * Musterprofile für /unternehmen/ (Vorgabe Betreiber 05.10.2026: rechte Spalte
 * mit Unternehmen, Bild und Werbetext wie bei Oberberg Aktuell). Echte Betriebe
 * erscheinen nur mit Einwilligung (deploy/unternehmen.mjs); bis genug eigene
 * Profile da sind, zeigen ausgedachte, sichtbar als „Musterprofil“
 * gekennzeichnete Betriebe, wie ein Eintrag aussieht. Fotos: frei lizenziert,
 * Nachweis in chatgpt-site/assets/werben/credits.json.
 */
const MA21_MUSTERPROFILE = [
    ['name' => 'Muster-Backstube', 'branche' => 'Bäckerei', 'ort' => 'Merzenich', 'bild' => '/assets/werben/format-startseitenband.jpg', 'alt' => 'Verkaufstheke einer Bäckerei mit Brot und Gebäck', 'foto' => 'Kgbo', 'lizenz' => 'CC BY-SA 4.0', 'lizenz_url' => 'https://creativecommons.org/licenses/by-sa/4.0',
        'text' => 'Frische Brötchen ab 6 Uhr, Kuchen nach Hausrezept und sonntags Frühstück zum Mitnehmen.', 'info' => 'Mo bis Sa 6 bis 18 Uhr, So 7 bis 11 Uhr',
        'beitrag' => ['titel' => 'Sonntags frisch: die Frühstückstüte zum Mitnehmen', 'text' => 'Brötchen, Croissants und Aufschnitt für zwei, bis Samstagabend vorbestellt und sonntags ab 7 Uhr abholbereit.']],
    ['name' => 'Muster-Café am Markt', 'branche' => 'Café', 'ort' => 'Golzheim', 'bild' => '/assets/werben/werbebanner.jpg', 'alt' => 'Helles Café mit Tischen und Sitzplätzen', 'foto' => 'Phi', 'lizenz' => 'CC0', 'lizenz_url' => 'https://creativecommons.org/publicdomain/zero/1.0/deed.de',
        'text' => 'Kaffee aus der Region, Torten aus eigener Herstellung und ein Mittagstisch, der jeden Tag wechselt.', 'info' => 'Di bis So 9 bis 18 Uhr',
        'beitrag' => ['titel' => 'Neuer Mittagstisch, jeden Tag ab 12 Uhr', 'text' => 'Suppe, Hauptgericht und Kaffee zum festen Preis. Die Karte der Woche hängt freitags im Schaufenster.']],
    ['name' => 'Muster-Schreinerei', 'branche' => 'Handwerk', 'ort' => 'Girbelsrath', 'bild' => '/assets/werben/format-artikelanzeige.jpg', 'alt' => 'Werkstatt mit Werkbank und Werkzeugen', 'foto' => 'Dimitrios Savva', 'lizenz' => 'CC0', 'lizenz_url' => 'https://creativecommons.org/publicdomain/zero/1.0/deed.de',
        'text' => 'Küchen, Treppen und Möbel nach Maß, gefertigt in der eigenen Werkstatt. Aufmaß und Beratung vor Ort.', 'info' => 'Termine nach Vereinbarung',
        'beitrag' => ['titel' => 'Ausbildung zum Tischler: zwei Plätze frei', 'text' => 'Wer gern mit Holz arbeitet, lernt bei uns Möbelbau, Treppenbau und Montage. Schnuppertage sind jederzeit möglich.']],
    ['name' => 'Muster-Steuerbüro', 'branche' => 'Beratung', 'ort' => 'Merzenich', 'bild' => '/assets/werben/unternehmensprofil.jpg', 'alt' => 'Modernes Büro mit Arbeitsplätzen', 'foto' => 'MichaelHolemans', 'lizenz' => 'CC BY-SA 4.0', 'lizenz_url' => 'https://creativecommons.org/licenses/by-sa/4.0',
        'text' => 'Steuererklärung, Lohnabrechnung und Gründungsberatung, persönlich im Büro oder digital von zu Hause.', 'info' => 'Mo bis Fr 8 bis 17 Uhr',
        'beitrag' => ['titel' => 'Steuererklärung ohne Papierstapel', 'text' => 'Belege fotografieren, hochladen, fertig. In einer offenen Sprechstunde zeigen wir, wie das digitale Steuerbüro funktioniert.']],
    ['name' => 'Muster-Immobilien', 'branche' => 'Immobilien', 'ort' => 'Bürgewald', 'bild' => '/assets/werben/format-sidebar.jpg', 'alt' => 'Modernes Wohnhaus mit Garten', 'foto' => 'Rüdiger Müller', 'lizenz' => 'CC BY-SA 4.0', 'lizenz_url' => 'https://creativecommons.org/licenses/by-sa/4.0',
        'text' => 'Wir bewerten Ihr Haus, finden passende Käufer und begleiten Sie bis zum Notartermin.', 'info' => 'Beratung auch am Wochenende',
        'beitrag' => ['titel' => 'Was ist mein Haus wert?', 'text' => 'Wir sehen uns Haus und Grundstück an und nennen einen realistischen Preis, unverbindlich und ohne Maklervertrag.']],
];

/** Kasten „Unternehmen aus der Gemeinde“ mit Musterprofilen (nur solange weniger als drei echte Profile veröffentlicht sind). */
function ma21_musterprofile_html(int $echte, string $anfrage): string {
    if ($echte >= 3) return '';
    $h = '<div class="sidebox u-box u-muster"><h3>Unternehmen aus der Gemeinde</h3>'
        . '<p class="u-muster__hinweis">So erscheinen Betriebe, die sich hier eintragen. Die folgenden Einträge sind Musterprofile, keine echten Unternehmen.</p>';
    foreach (array_slice(MA21_MUSTERPROFILE, 0, 5 - min($echte, 2)) as $m) {
        $h .= '<article class="u-muster__karte"><a class="u-muster__link" href="' . esc_url($anfrage) . '" aria-label="' . esc_attr($m['name'] . ': Musterprofil. Eigenes Profil anfragen') . '">'
            . '<span class="u-muster__bild"><img src="' . esc_url(home_url($m['bild'])) . '" alt="' . esc_attr($m['alt']) . '" width="600" height="338" loading="lazy" decoding="async"><span class="u-muster__marke">Musterprofil</span></span>'
            . '<span class="u-muster__eyebrow">' . esc_html($m['branche'] . ' · ' . $m['ort']) . '</span>'
            . '<strong class="u-muster__name">' . esc_html($m['name']) . '</strong>'
            . '<span class="u-muster__text">' . esc_html($m['text']) . '</span>'
            . '<span class="u-muster__info">' . esc_html($m['info']) . '</span></a>'
            . '<span class="u-muster__credit">Foto: ' . esc_html($m['foto']) . ', <a href="' . esc_url($m['lizenz_url']) . '" target="_blank" rel="noopener license">' . esc_html($m['lizenz']) . '</a></span></article>';
    }
    return $h . '<p><a class="btn block" href="' . esc_url($anfrage) . '">Eigenen Betrieb eintragen</a></p></div>';
}

/**
 * Firmenliste im Aufklappmenü „Unternehmen“ (Vorgabe Betreiber 05.10.2026, wie
 * Oberberg Aktuell unter „Wirtschaft“): links die Betriebe, rechts beim
 * Überfahren ihre Beiträge. Echte Profile (ma_business, nur mit Einwilligung
 * veröffentlicht) zuerst; solange weniger als drei da sind, füllen sichtbar
 * gekennzeichnete Musterprofile auf (gleiche Regel wie die rechte Spalte).
 */
function ma21_menue_firmen(): array {
    $anfrage = wp_make_link_relative(home_url('/anzeigen/aufgeben/?art=Werbung&format=Unternehmenskanal'));
    $echte = get_posts(['post_type' => 'ma_business', 'post_status' => 'publish', 'posts_per_page' => 8, 'orderby' => 'title', 'order' => 'ASC']);
    $firmen = [];
    foreach ($echte as $u) {
        $name = html_entity_decode(get_the_title($u), ENT_QUOTES, 'UTF-8');
        $ort = (string) get_post_meta($u->ID, 'ma_business_ortsteil', true);
        $ort = MA21_ORTE[$ort] ?? ($ort !== '' ? ucfirst($ort) : 'Gemeinde Merzenich');
        $branche = (string) get_post_meta($u->ID, 'ma_business_branche', true);
        $bild = ma21_menue_bild($u);
        // Beiträge des Betriebs: gesponserte Beiträge mit diesem Auftraggeber.
        $posts = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3, 'orderby' => 'date', 'order' => 'DESC',
            'meta_query' => [['key' => 'ma_gesponsert', 'value' => '1'], ['key' => 'ma_gesponsert_von', 'value' => $name]]]);
        $karten = array_map(fn(WP_Post $p): array => [
            'titel' => html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8'), 'url' => wp_make_link_relative(get_permalink($p)),
            'kicker' => (string) (MA21_ORTE[ma21_ort($p)] ?? $ort), 'meta' => 'Anzeige · ' . get_the_date('d.m.Y, H:i', $p) . ' Uhr',
            'anriss' => html_entity_decode(wp_html_excerpt(ma21_teaser($p), 170, ' …'), ENT_QUOTES, 'UTF-8'), 'bild' => ma21_menue_bild($p), 'knopf' => 'Weiterlesen',
        ], $posts);
        if (!$karten) $karten[] = ['titel' => $name, 'url' => wp_make_link_relative(get_permalink($u)), 'kicker' => trim($branche . ' · ' . $ort, ' ·'),
            'meta' => 'Unternehmensprofil', 'anriss' => html_entity_decode(wp_html_excerpt(ma21_teaser($u), 170, ' …'), ENT_QUOTES, 'UTF-8'), 'bild' => $bild, 'knopf' => 'Zum Profil'];
        $firmen[] = ['name' => $name, 'url' => wp_make_link_relative(get_permalink($u)), 'zeile' => trim($branche . ' · ' . $ort, ' ·'), 'logo' => $bild ? $bild['src'] : null, 'karten' => $karten];
    }
    if (count($echte) < 3) {
        $platz = ['titel' => 'Ihr Betrieb an dieser Stelle', 'url' => $anfrage, 'kicker' => 'Für Unternehmen', 'meta' => 'Unternehmenskanal auf Merzenich Aktuell',
            'anriss' => 'Eigene Beiträge, ein Profil mit Bild, Öffnungszeiten und Kontakt. Die Redaktion prüft jeden Beitrag vor der Veröffentlichung.', 'bild' => null, 'knopf' => 'Betrieb eintragen', 'platz' => true];
        foreach (array_slice(MA21_MUSTERPROFILE, 0, 5 - min(count($echte), 2)) as $m) {
            $bild = ['src' => $m['bild'], 'alt' => $m['alt']];
            $firmen[] = ['name' => $m['name'], 'url' => $anfrage, 'zeile' => $m['branche'] . ' · ' . $m['ort'], 'logo' => null, 'muster' => true, 'karten' => [
                ['titel' => $m['beitrag']['titel'], 'url' => $anfrage, 'kicker' => $m['ort'], 'meta' => 'Musterbeitrag · Anzeige', 'anriss' => $m['beitrag']['text'], 'bild' => $bild, 'knopf' => 'Beitrag buchen', 'muster' => true,
                    'credit' => 'Foto: ' . $m['foto'] . ', ' . $m['lizenz']],
                ['titel' => $m['name'], 'url' => $anfrage, 'kicker' => $m['branche'] . ' · ' . $m['ort'], 'meta' => 'Musterprofil · ' . $m['info'], 'anriss' => $m['text'], 'bild' => null, 'knopf' => 'Profil anfragen', 'muster' => true],
                $platz,
            ]];
        }
    }
    return $firmen;
}
