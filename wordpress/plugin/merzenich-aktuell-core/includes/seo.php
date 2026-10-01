<?php
/**
 * SEO und Geo (02.10.2026): Was Suchmaschinen, Nachrichtendienste und
 * KI-Suchen über jede Seite von Merzenich Aktuell wissen sollen.
 *
 * Bisher gab WordPress nur den Titel und auf Einzelseiten ein Canonical aus.
 * Dieses Modul ergänzt je Seite: Description, Canonical (auch Startseite und
 * Listen), Robots-Regel, Open Graph und Twitter Card, Geo-Angaben
 * (Gemeinde Merzenich, Ortsteil), strukturierte Daten als JSON-LD
 * (NewsMediaOrganization, WebSite, NewsArticle, CollectionPage mit ItemList,
 * Place, Event, Organization für Vereine, BreadcrumbList) sowie eine
 * News-Sitemap (/news-sitemap.xml), eine bereinigte Standard-Sitemap (keine
 * Benutzer, keine Trauer- und Familienanzeigen), robots.txt-Zeilen und
 * /llms.txt für KI-Suchen.
 *
 * Adressen: Die Ressorts laufen unter /blaulicht/, /sport/ … (wie die
 * statische Seite und die Navigation); /category/… leitet dorthin um,
 * Blättern und Feeds funktionieren unter der kurzen Adresse. Autorenarchive
 * (/author/…, zeigen Anmeldenamen) und Anhangsseiten leiten weiter.
 *
 * Nur belegte Angaben: Anbieterin KBS Management GmbH (Impressum), Ortsmitte
 * aus den Wetter-Einstellungen, Ortsteil-Koordinaten nur, wo GeoNames sie
 * führt (Golzheim, Girbelsrath; Abruf 02.10.2026 über die Open-Meteo
 * Geocoding-API). Social-Profile (sameAs) und Verifizierungs-Codes trägt der
 * Betreiber unter Merzenich Aktuell → SEO ein; ohne Eintrag werden sie nicht
 * ausgegeben.
 */
if (!defined('ABSPATH')) { exit; }

const MA_SEO_MARKE = 'Merzenich Aktuell';
const MA_SEO_UNTERTITEL = 'Lokalzeitung online für die Gemeinde Merzenich';
const MA_SEO_BESCHREIBUNG = 'Merzenich Aktuell ist die Lokalzeitung online für die Gemeinde Merzenich im Kreis Düren: Nachrichten, Blaulicht, Termine, Vereine und Rathaus aus Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald. Jede Meldung mit Quelle und Bildcredit.';
const MA_SEO_PLZ = '52399';
const MA_SEO_GEBIET = 'Gemeinde Merzenich, Kreis Düren, Nordrhein-Westfalen';

/** Ortsteile: Name, Beschreibung (wie die Ortsseiten der statischen Seite), Koordinaten nur mit Quelle. */
function ma_seo_orte(): array {
    return [
        'merzenich' => ['name' => 'Merzenich', 'beschreibung' => 'Hauptort der Gemeinde mit Rathaus, Bürgerhaus, Pfarrkirche St. Laurentius, Heimatmuseum und S-Bahn-Halt an der Strecke Köln–Aachen.'],
        'golzheim' => ['name' => 'Golzheim', 'beschreibung' => 'Ländlicher Ortsteil im Norden der Gemeinde mit Pfarrkirche St. Gregorius, eigener Grundschule, Schützenhalle und einer der ältesten Schützenbruderschaften der Region.', 'lat' => 50.83966, 'lon' => 6.58118],
        'girbelsrath' => ['name' => 'Girbelsrath', 'beschreibung' => 'Ortsteil im Süden der Gemeinde mit der Kirche St. Amandus, Sportplatz, eigener Löschgruppe und aktivem Karnevalsverein inmitten der Feldflur Richtung Binsfeld.', 'lat' => 50.81185, 'lon' => 6.55163],
        'morschenich' => ['name' => 'Morschenich', 'beschreibung' => 'Der ab 2015 bezogene Umsiedlungsort „Zwischen den Höfen“ westlich von Merzenich, bis Juli 2024 Morschenich-Neu genannt, mit Bürgewaldzentrum, Kirche St. Lambertus und aktivem Vereinsleben.'],
        'buergewald' => ['name' => 'Bürgewald', 'beschreibung' => 'Das alte Morschenich am Rand des Hambacher Forsts, seit 6. Juli 2024 Bürgewald genannt und als „Ort der Zukunft“ von Gemeinde, Land NRW und RWE gemeinsam neu entwickelt.'],
    ];
}

/** Ressorts und Listen: Titel und Beschreibung (Texte der statischen Seite). Schlüssel = Adresse /<slug>/. */
function ma_seo_ressorte(): array {
    return [
        'blaulicht' => ['Blaulicht', 'Blaulicht Merzenich: Feuerwehr, Polizei und Rettungsdienst', 'Einsätze der Freiwilligen Feuerwehr Merzenich, Polizeimeldungen für das Gemeindegebiet und Verkehrsmeldungen, sachlich zusammengefasst und mit Originalquelle.'],
        'sport' => ['Sport', 'Sport in der Gemeinde Merzenich', 'Fußball in der Kreisliga, Tischtennis, Schießsport, Turniere und Ergebnisse der Vereine aus allen Ortsteilen.'],
        'rathaus' => ['Rathaus & Politik', 'Rathaus, Rat und Kommunalpolitik in Merzenich', 'Was im Rathaus Merzenich, im Gemeinderat und in den Ausschüssen entschieden wird. Sprechstunden, Satzungen, Bauvorhaben, Bürgerbeteiligung.'],
        'leben' => ['Leben', 'Leben in der Gemeinde Merzenich', 'Feste, Kultur, Kirche, Schule, Freizeit und Alltag in Merzenich und seinen Ortsteilen.'],
        'wirtschaft' => ['Wirtschaft', 'Wirtschaft, Handel und Strukturwandel in Merzenich', 'Betriebe, Handwerk, Gewerbegebiete, Förderprogramme wie LEADER und der Strukturwandel im Rheinischen Revier rund um Merzenich.'],
        'menschen' => ['Menschen', 'Menschen aus Merzenich', 'Jubiläen, Hochzeiten, Ehrungen, Nachrufe und Porträts: die Menschen hinter den Meldungen.'],
        'vereine' => ['Vereine', 'Vereine in der Gemeinde Merzenich', 'Wer in Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald aktiv ist: Schützen, Sport, Karneval, Feuerwehr, Kirche und Ehrenamt mit Ansprechpartnern und offiziellen Seiten.'],
        'tipp' => ['Tipp', 'Tipps aus Merzenich', 'Tipp: klar gekennzeichnete bezahlte und gesponserte Platzierungen von Vereinen, Unternehmen und Partnern aus Merzenich und der Region.'],
        'nachrichten' => ['Nachrichten', 'Nachrichten aus der Gemeinde Merzenich', 'Alle Meldungen aus Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald in chronologischer Reihenfolge.'],
        'termine' => ['Termine', 'Termine und Veranstaltungen in Merzenich', 'Veranstaltungen in Merzenich und seinen Ortsteilen: Feste, Sitzungen, Sport, Kirche und Vereine, nach Datum sortiert.'],
        'unternehmen' => ['Unternehmen', 'Unternehmen in Merzenich', 'Unternehmen in Merzenich: Wirtschaftsmeldungen der Redaktion und die Kanäle der Unternehmen aus der Gemeinde, Unternehmensbeiträge als Anzeige gekennzeichnet.'],
    ];
}

/** Kategorien, die unter /<slug>/ laufen (Blättern, Feed, Umleitung von /category/<slug>/). */
function ma_seo_ressort_kategorien(): array {
    return ['blaulicht', 'sport', 'rathaus', 'leben', 'wirtschaft', 'menschen', 'vereine'];
}

/** Statische Adressen, die WordPress anders führt (301). */
function ma_seo_umleitungen(): array {
    return [
        '/autor/redaktion/' => '/redaktion/',
        '/termine/melden/' => '/termin-melden/',
        '/feed.xml' => '/feed/',
        '/atom.xml' => '/feed/atom/',
        '/suche/' => '/?s=',
    ];
}

/* ------------------------------------------------------------ Helfer (ohne WordPress testbar) */

/** Text auf eine Description kürzen: ohne Tags und Shortcodes, eine Zeile, am Wortende abgeschnitten. */
function ma_seo_kuerzen(string $text, int $max = 160): string {
    $text = preg_replace('/\[[^\]]*\]/', ' ', $text) ?? $text;
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim((string) preg_replace('/\s+/u', ' ', $text));
    if (mb_strlen($text) <= $max) return $text;
    $kurz = mb_substr($text, 0, $max - 1);
    $pos = mb_strrpos($kurz, ' ');
    if ($pos !== false && $pos > $max / 2) $kurz = mb_substr($kurz, 0, $pos);
    return rtrim($kurz, " ,;:–-") . '…';
}

/** Koordinaten der Ortsmitte Merzenich aus den Wetter-Einstellungen (Plugin-Option, vom Betreiber änderbar). */
function ma_seo_mitte(): array {
    $s = function_exists('get_option') ? (array) get_option('ma_weather_settings', []) : [];
    $lat = (float) ($s['latitude'] ?? 50.8317);
    $lon = (float) ($s['longitude'] ?? 6.5361);
    return [round($lat, 4), round($lon, 4)];
}

/** Nur vollständige Web-Adressen (Schema, Host mit Punkt) gelten als Profil. */
function ma_seo_url_gueltig(string $u): bool {
    return (bool) preg_match('~^https?://[^/\s]+\.[a-z0-9-]{2,}(/|$)~i', $u);
}

/** Robots-Zeile: indexierbar oder nicht. */
function ma_seo_robots_wert(bool $index): string {
    return $index ? 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1' : 'noindex,follow';
}

/** ISO-8601-Zeit mit Zeitzone der Installation. */
function ma_seo_zeit(int $ts): string {
    return function_exists('wp_date') ? (string) wp_date('c', $ts) : date('c', $ts);
}

/** Zeilen für robots.txt: Suchseiten und Formular-Rückmeldungen nicht crawlen, News-Sitemap nennen. */
function ma_seo_robots_txt(string $vorhanden, string $home): string {
    $zeilen = rtrim($vorhanden) . "\nDisallow: /?s=\nDisallow: /*?s=\nDisallow: /*?gesendet=\n";
    if (!str_contains($zeilen, 'news-sitemap.xml')) $zeilen .= "\nSitemap: {$home}news-sitemap.xml\n";
    return $zeilen;
}

/** News-Sitemap (Google News): Meldungen der letzten zwei Tage. $eintraege: [['url','titel','zeit'(ISO)], …]. */
function ma_seo_news_sitemap_xml(array $eintraege): string {
    $x = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:news=\"http://www.google.com/schemas/sitemap-news/0.9\">\n";
    foreach ($eintraege as $e) {
        $x .= '<url><loc>' . htmlspecialchars($e['url'], ENT_XML1) . '</loc><news:news><news:publication><news:name>' . MA_SEO_MARKE . '</news:name><news:language>de</news:language></news:publication>'
            . '<news:publication_date>' . htmlspecialchars($e['zeit'], ENT_XML1) . '</news:publication_date><news:title>' . htmlspecialchars($e['titel'], ENT_XML1) . '</news:title></news:news></url>' . "\n";
    }
    return $x . "</urlset>\n";
}

/* ------------------------------------------------------------ Strukturierte Daten (JSON-LD, reine Funktion) */

/**
 * Graph aus dem Seitenkontext. $k: typ, titel, beschreibung, url, bild
 * (url,w,h,alt), krumen [[name,url|null]], liste [[url,name]], ort (slug),
 * veroeffentlicht/geaendert (ISO), ressort (Label), tags [], woerter,
 * quelle (URL), termin [...], verein [...], gesponsert, seitenart.
 * $o: home, logo, bild (Standardbild), sameas [], mitte [lat,lon].
 */
function ma_seo_graph(array $k, array $o): array {
    $home = $o['home'];
    $org = [
        '@type' => 'NewsMediaOrganization', '@id' => $home . '#organization', 'name' => MA_SEO_MARKE, 'alternateName' => MA_SEO_UNTERTITEL, 'url' => $home,
        'logo' => ['@type' => 'ImageObject', 'url' => $o['logo'], 'width' => 1200, 'height' => 338], 'image' => $o['bild'],
        'description' => 'Nachrichten aus Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald',
        'parentOrganization' => ['@type' => 'Organization', 'name' => 'KBS Management GmbH', 'email' => 'info@kbs-management.tv',
            'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Rheinstr. 78a', 'postalCode' => '51371', 'addressLocality' => 'Leverkusen', 'addressCountry' => 'DE']],
        'email' => 'info@kbs-management.tv',
        'areaServed' => ['@type' => 'AdministrativeArea', 'name' => MA_SEO_GEBIET],
        'publishingPrinciples' => $home . 'grundsaetze/', 'correctionsPolicy' => $home . 'korrekturen/', 'ethicsPolicy' => $home . 'grundsaetze/#ethik',
        'actionableFeedbackPolicy' => $home . 'meldung-senden/', 'masthead' => $home . 'ueber-uns/', 'ownershipFundingInfo' => $home . 'ueber-uns/#finanzierung', 'diversityPolicy' => $home . 'grundsaetze/#vielfalt',
    ];
    if (!empty($o['sameas'])) $org['sameAs'] = array_values($o['sameas']);
    $web = ['@type' => 'WebSite', '@id' => $home . '#website', 'name' => MA_SEO_MARKE, 'url' => $home, 'inLanguage' => 'de-DE', 'publisher' => ['@id' => $home . '#organization'],
        'potentialAction' => ['@type' => 'SearchAction', 'target' => ['@type' => 'EntryPoint', 'urlTemplate' => $home . '?s={search_term_string}'], 'query-input' => 'required name=search_term_string']];
    $graph = [$org, $web];
    $orte = ma_seo_orte();
    $ort = $orte[$k['ort'] ?? 'merzenich'] ?? $orte['merzenich'];
    $adresse = ['@type' => 'PostalAddress', 'addressLocality' => 'Merzenich', 'postalCode' => MA_SEO_PLZ, 'addressRegion' => 'NRW', 'addressCountry' => 'DE'];
    $platz = function (string $slug) use ($orte, $home, $adresse, $o): array {
        $d = $orte[$slug];
        $p = ['@type' => 'Place', '@id' => $home . 'ort/' . $slug . '/#place', 'name' => $d['name'], 'url' => $home . 'ort/' . $slug . '/', 'address' => $adresse, 'containedInPlace' => ['@type' => 'AdministrativeArea', 'name' => 'Gemeinde Merzenich']];
        if ($slug === 'merzenich' && !empty($o['mitte'])) $p['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $o['mitte'][0], 'longitude' => $o['mitte'][1]];
        elseif (isset($d['lat'])) $p['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $d['lat'], 'longitude' => $d['lon']];
        return $p;
    };
    $bild = !empty($k['bild']['url']) ? array_filter(['@type' => 'ImageObject', 'url' => $k['bild']['url'], 'width' => $k['bild']['w'] ?? null, 'height' => $k['bild']['h'] ?? null, 'caption' => $k['bild']['alt'] ?? null]) : null;
    $url = $k['url'] ?? $home;
    switch ($k['typ']) {
        case 'start':
            $graph[] = ['@type' => 'WebPage', '@id' => $url . '#webpage', 'url' => $url, 'name' => MA_SEO_MARKE . ' · ' . MA_SEO_UNTERTITEL, 'description' => $k['beschreibung'], 'isPartOf' => ['@id' => $home . '#website'], 'about' => ['@id' => $home . '#organization'], 'inLanguage' => 'de-DE', 'primaryImageOfPage' => $bild];
            $graph[] = $platz('merzenich');
            break;
        case 'artikel':
            $a = ['@type' => 'NewsArticle', '@id' => $url . '#article', 'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url], 'headline' => $k['titel'], 'description' => $k['beschreibung'],
                'datePublished' => $k['veroeffentlicht'], 'dateModified' => $k['geaendert'] ?: $k['veroeffentlicht'],
                'author' => [['@type' => 'Organization', 'name' => 'Redaktion Merzenich Aktuell', 'url' => $home . 'redaktion/']], 'publisher' => ['@id' => $home . '#organization'],
                'isAccessibleForFree' => true, 'inLanguage' => 'de-DE', 'articleSection' => $k['ressort'] ?? '',
                'contentLocation' => ['@type' => 'Place', 'name' => $ort['name'], 'address' => $adresse], 'about' => ['@id' => $home . 'ort/' . ($k['ort'] ?? 'merzenich') . '/#place'],
                'speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['.article-head h1', '.article-head .dek']]];
            if ($bild) $a['image'] = [$bild];
            if (!empty($k['tags'])) $a['keywords'] = implode(', ', $k['tags']);
            if (!empty($k['woerter'])) $a['wordCount'] = (int) $k['woerter'];
            if (!empty($k['quelle'])) { $a['citation'] = [$k['quelle']]; $a['isBasedOn'] = [$k['quelle']]; }
            if (!empty($k['gesponsert'])) $a['sponsor'] = ['@type' => 'Organization', 'name' => $k['gesponsert']];
            $graph[] = $a;
            $graph[] = $platz($k['ort'] ?? 'merzenich');
            break;
        case 'termin':
            $t = $k['termin'] ?? [];
            $e = ['@type' => 'Event', '@id' => $url . '#event', 'name' => $k['titel'], 'url' => $url, 'description' => $k['beschreibung'], 'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode', 'eventStatus' => 'https://schema.org/EventScheduled', 'inLanguage' => 'de-DE'];
            if (!empty($t['start'])) $e['startDate'] = $t['start'];
            if (!empty($t['ende'])) $e['endDate'] = $t['ende'];
            if (!empty($t['ort'])) $e['location'] = ['@type' => 'Place', 'name' => $t['ort'], 'address' => $adresse];
            if (!empty($t['veranstalter'])) $e['organizer'] = ['@type' => 'Organization', 'name' => $t['veranstalter']];
            if (isset($t['preis']) && preg_match('/^(kostenlos|kostenfrei|frei|eintritt frei|0 ?€)/iu', (string) $t['preis'])) $e['isAccessibleForFree'] = true;
            if ($bild) $e['image'] = [$bild];
            $graph[] = $e;
            break;
        case 'verein':
            $v = $k['verein'] ?? [];
            $c = ['@type' => !empty($v['sportarten']) ? 'SportsClub' : 'Organization', '@id' => $url . '#organization', 'name' => $k['titel'], 'url' => $url, 'description' => $k['beschreibung'], 'location' => ['@type' => 'Place', 'name' => $ort['name'], 'address' => $adresse]];
            if (!empty($v['adresse'])) $c['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $v['adresse'], 'addressLocality' => 'Merzenich', 'postalCode' => MA_SEO_PLZ, 'addressCountry' => 'DE'];
            if (!empty($v['website'])) $c['sameAs'] = [$v['website']];
            if (!empty($v['gegruendet']) && preg_match('/^\d{4}$/', (string) $v['gegruendet'])) $c['foundingDate'] = (string) $v['gegruendet'];
            if (!empty($v['sportarten'])) $c['sport'] = $v['sportarten'];
            if (!empty($v['logo'])) $c['logo'] = $v['logo'];
            $graph[] = $c;
            break;
        case 'ort':
            $p = $platz($k['ort'] ?? 'merzenich'); $p['description'] = $ort['beschreibung'];
            $graph[] = ['@type' => 'CollectionPage', '@id' => $url . '#webpage', 'name' => $k['titel'], 'url' => $url, 'description' => $k['beschreibung'], 'isPartOf' => ['@id' => $home . '#website'], 'about' => ['@id' => $p['@id']], 'inLanguage' => 'de-DE', 'mainEntity' => ma_seo_itemlist($k['liste'] ?? [])];
            $graph[] = $p;
            break;
        case 'ressort': case 'thema': case 'liste':
            $graph[] = ['@type' => 'CollectionPage', '@id' => $url . '#webpage', 'name' => $k['titel'], 'url' => $url, 'description' => $k['beschreibung'], 'isPartOf' => ['@id' => $home . '#website'], 'inLanguage' => 'de-DE', 'mainEntity' => ma_seo_itemlist($k['liste'] ?? [])];
            break;
        case 'seite': case 'eintrag':
            $graph[] = ['@type' => $k['seitenart'] ?? 'WebPage', '@id' => $url . '#webpage', 'url' => $url, 'name' => $k['titel'], 'description' => $k['beschreibung'], 'isPartOf' => ['@id' => $home . '#website'], 'inLanguage' => 'de-DE', 'dateModified' => $k['geaendert'] ?? null];
            break;
    }
    if (!empty($k['krumen']) && count($k['krumen']) > 1) {
        $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => array_values(array_map(fn($kr, $i) => array_filter(['@type' => 'ListItem', 'position' => $i + 1, 'name' => $kr[0], 'item' => $kr[1] ?? null]), $k['krumen'], array_keys($k['krumen'])))];
    }
    $graph = array_map(fn($e) => array_filter($e, fn($v) => $v !== null && $v !== '' && $v !== []), $graph);
    return ['@context' => 'https://schema.org', '@graph' => $graph];
}

function ma_seo_itemlist(array $liste): array {
    return ['@type' => 'ItemList', 'itemListElement' => array_values(array_map(fn($e, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'url' => $e[0], 'name' => $e[1]], $liste, array_keys($liste)))];
}

/** llms.txt: Überblick für KI-Suchen aus Ressorts, Orten, Seiten und Anbieterin. $seiten: [[name, url, beschreibung]]. */
function ma_seo_llms_txt(string $home, array $seiten): string {
    $t = "# " . MA_SEO_MARKE . "\n\n> " . MA_SEO_BESCHREIBUNG . "\n\n";
    $t .= "Anbieterin: KBS Management GmbH, Rheinstr. 78a, 51371 Leverkusen, info@kbs-management.tv. Jede Meldung nennt ihre Originalquelle, ihren Datenstand und den Bildtyp; Fehler werden im Artikel mit Datum korrigiert.\n\n";
    $t .= "## Ressorts\n\n";
    foreach (ma_seo_ressorte() as $slug => [$name, $titel, $desc]) $t .= "- [{$name}]({$home}{$slug}/): {$desc}\n";
    $t .= "\n## Orte der Gemeinde Merzenich\n\n";
    foreach (ma_seo_orte() as $slug => $d) $t .= "- [{$d['name']}]({$home}ort/{$slug}/): {$d['beschreibung']}\n";
    if ($seiten) { $t .= "\n## Über die Redaktion\n\n"; foreach ($seiten as [$name, $url, $desc]) $t .= "- [{$name}]({$url})" . ($desc !== '' ? ": {$desc}" : '') . "\n"; }
    $t .= "\n## Feeds und Sitemaps\n\n- [RSS]({$home}feed/)\n- [Sitemap]({$home}wp-sitemap.xml)\n- [News-Sitemap]({$home}news-sitemap.xml)\n";
    return $t;
}

/* ------------------------------------------------------------ Kontext der aktuellen Seite */

/** Standardbild (1200×630) und Logo liegen im Theme unter static/assets/img (Adresse /assets/…). */
function ma_seo_standardbild(): array {
    return ['url' => home_url('/assets/img/og-default.jpg'), 'w' => 1200, 'h' => 630, 'alt' => MA_SEO_MARKE];
}

/** Beitragsbild für Vorschau und JSON-LD: nur mit geklärten Rechten (ma_content_image), sonst Standardbild. */
function ma_seo_bild($post): array {
    $post = get_post($post);
    if (!$post || !has_post_thumbnail($post)) return ma_seo_standardbild();
    if (function_exists('ma_content_image') && !empty(ma_content_image($post, 'large')['is_fallback'])) return ma_seo_standardbild();
    $id = (int) get_post_thumbnail_id($post);
    $src = wp_get_attachment_image_src($id, 'large');
    if (!$src) return ma_seo_standardbild();
    $alt = (string) get_post_meta($id, '_wp_attachment_image_alt', true);
    return ['url' => $src[0], 'w' => (int) $src[1], 'h' => (int) $src[2], 'alt' => $alt !== '' ? $alt : get_the_title($post)];
}

function ma_seo_ort_von($post): string {
    $t = get_the_terms($post, 'ma_location');
    if (is_array($t)) foreach ($t as $term) if (isset(ma_seo_orte()[$term->slug])) return $term->slug;
    return 'merzenich';
}

function ma_seo_ressort_von(WP_Post $p): array {
    $r = ma_seo_ressorte();
    foreach (get_the_category($p->ID) as $c) if (isset($r[$c->slug])) return [$c->slug, $r[$c->slug][0]];
    $c = get_the_category($p->ID)[0] ?? null;
    return $c ? [$c->slug, $c->name] : ['nachrichten', 'Nachrichten'];
}

/** Adresse der aktuellen Liste mit Seitenzahl. */
function ma_seo_liste_url(string $basis): string {
    $seite = max(1, (int) get_query_var('paged'));
    return $seite > 1 ? trailingslashit($basis) . 'page/' . $seite . '/' : trailingslashit($basis);
}

function ma_seo_liste(): array {
    global $wp_query;
    if (!$wp_query || is_paged()) return [];
    $liste = [];
    foreach (array_slice((array) $wp_query->posts, 0, 20) as $p) if ($p instanceof WP_Post) $liste[] = [get_permalink($p), html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8')];
    return $liste;
}

function ma_seo_kontext(): array {
    static $k = null;
    if ($k !== null) return $k;
    $home = home_url('/');
    $r = ma_seo_ressorte();
    $k = ['typ' => 'liste', 'titel' => '', 'beschreibung' => '', 'url' => '', 'index' => true, 'og' => 'website', 'krumen' => [['Start', $home]], 'ort' => 'merzenich', 'bild' => ma_seo_standardbild(), 'liste' => [], 'tags' => [], 'veroeffentlicht' => '', 'geaendert' => '', 'ressort' => ''];
    $o = get_queried_object();
    if (is_front_page()) {
        $k = array_merge($k, ['typ' => 'start', 'titel' => MA_SEO_MARKE . ' · ' . MA_SEO_UNTERTITEL, 'beschreibung' => MA_SEO_BESCHREIBUNG, 'url' => $home, 'krumen' => []]);
    } elseif (is_singular('post') && $o instanceof WP_Post) {
        [$rs, $rl] = ma_seo_ressort_von($o);
        $ort = ma_seo_ort_von($o);
        $text = trim($o->post_excerpt) !== '' ? $o->post_excerpt : $o->post_content;
        $krumen = [['Start', $home], [$rl, isset($r[$rs]) ? $home . $rs . '/' : $home . 'nachrichten/']];
        if ($ort !== 'merzenich') $krumen[] = [ma_seo_orte()[$ort]['name'], $home . 'ort/' . $ort . '/'];
        $krumen[] = [html_entity_decode(get_the_title($o), ENT_QUOTES, 'UTF-8'), null];
        $tags = array_map(fn($t) => $t->name, (array) (get_the_tags($o->ID) ?: []));
        if ($ort !== 'merzenich') $tags[] = ma_seo_orte()[$ort]['name'];
        $k = array_merge($k, ['typ' => 'artikel', 'titel' => html_entity_decode(get_the_title($o), ENT_QUOTES, 'UTF-8'), 'beschreibung' => ma_seo_kuerzen($text), 'url' => get_permalink($o), 'og' => 'article', 'krumen' => $krumen, 'ort' => $ort,
            'bild' => ma_seo_bild($o), 'tags' => $tags, 'ressort' => $rl, 'veroeffentlicht' => get_post_time('c', false, $o), 'geaendert' => get_post_modified_time('c', false, $o),
            'woerter' => str_word_count(strip_tags($o->post_excerpt . ' ' . $o->post_content)), 'quelle' => (string) get_post_meta($o->ID, 'ma_source_url', true),
            'gesponsert' => function_exists('ma_ist_gesponsert') && ma_ist_gesponsert($o) ? ((string) get_post_meta($o->ID, 'ma_gesponsert_von', true) ?: 'Gesponsert') : '']);
    } elseif (is_singular('ma_event') && $o instanceof WP_Post) {
        $start = function_exists('ma_event_timestamp') ? ma_event_timestamp($o->ID, 'start') : 0;
        $ende = function_exists('ma_event_timestamp') ? ma_event_timestamp($o->ID, 'end') : 0;
        $platz = (string) get_post_meta($o->ID, 'ma_event_place', true);
        $text = trim($o->post_excerpt) !== '' ? $o->post_excerpt : ($o->post_content ?: ('Termin' . ($start ? ' am ' . wp_date('d.m.Y', $start) : '') . ($platz ? ' in ' . $platz : '') . ', Gemeinde Merzenich.'));
        $k = array_merge($k, ['typ' => 'termin', 'titel' => html_entity_decode(get_the_title($o), ENT_QUOTES, 'UTF-8'), 'beschreibung' => ma_seo_kuerzen($text), 'url' => get_permalink($o), 'og' => 'article', 'ort' => ma_seo_ort_von($o), 'bild' => ma_seo_bild($o),
            'krumen' => [['Start', $home], ['Termine', $home . 'termine/'], [html_entity_decode(get_the_title($o), ENT_QUOTES, 'UTF-8'), null]], 'geaendert' => get_post_modified_time('c', false, $o),
            'termin' => ['start' => $start ? ma_seo_zeit($start) : '', 'ende' => $ende && $ende !== $start ? ma_seo_zeit($ende) : '', 'ort' => $platz, 'veranstalter' => (string) get_post_meta($o->ID, 'ma_event_organizer', true), 'preis' => (string) get_post_meta($o->ID, 'ma_event_price', true)]]);
    } elseif (is_singular('ma_club') && $o instanceof WP_Post) {
        $logo = (string) get_post_meta($o->ID, 'ma_club_logo', true);
        $k = array_merge($k, ['typ' => 'verein', 'titel' => html_entity_decode(get_the_title($o), ENT_QUOTES, 'UTF-8'), 'beschreibung' => ma_seo_kuerzen(trim($o->post_excerpt) !== '' ? $o->post_excerpt : ($o->post_content ?: get_the_title($o) . ': Verein in der Gemeinde Merzenich mit Meldungen und Terminen auf Merzenich Aktuell.')), 'url' => get_permalink($o), 'ort' => ma_seo_ort_von($o), 'bild' => ma_seo_bild($o),
            'krumen' => [['Start', $home], ['Vereine', $home . 'vereine/'], [html_entity_decode(get_the_title($o), ENT_QUOTES, 'UTF-8'), null]], 'geaendert' => get_post_modified_time('c', false, $o),
            'verein' => ['adresse' => (string) get_post_meta($o->ID, 'ma_club_adresse', true), 'website' => (string) get_post_meta($o->ID, 'ma_club_website', true), 'gegruendet' => (string) get_post_meta($o->ID, 'ma_club_gegruendet', true), 'sportarten' => (string) get_post_meta($o->ID, 'ma_club_sportarten', true), 'logo' => $logo !== '' && str_starts_with($logo, 'http') ? $logo : '']]);
    } elseif (is_singular() && $o instanceof WP_Post) {
        $arten = ['ueber-uns' => 'AboutPage', 'kontakt' => 'ContactPage', 'impressum' => 'AboutPage', 'datenschutz' => 'WebPage', 'suche' => 'SearchResultsPage'];
        $krumen = [['Start', $home]];
        if ($o->post_parent) $krumen[] = [html_entity_decode(get_the_title($o->post_parent), ENT_QUOTES, 'UTF-8'), get_permalink($o->post_parent)];
        $krumen[] = [html_entity_decode(get_the_title($o), ENT_QUOTES, 'UTF-8'), null];
        $k = array_merge($k, ['typ' => $o->post_type === 'page' ? 'seite' : 'eintrag', 'titel' => html_entity_decode(get_the_title($o), ENT_QUOTES, 'UTF-8'), 'beschreibung' => ma_seo_kuerzen(trim($o->post_excerpt) !== '' ? $o->post_excerpt : $o->post_content), 'url' => get_permalink($o),
            'krumen' => $krumen, 'bild' => ma_seo_bild($o), 'geaendert' => get_post_modified_time('c', false, $o), 'seitenart' => $arten[$o->post_name] ?? 'WebPage', 'index' => !in_array($o->post_type, ['ma_obituary', 'ma_family_notice'], true)]);
    } elseif (is_category() && $o instanceof WP_Term) {
        $d = $r[$o->slug] ?? [$o->name, $o->name . ' in Merzenich', $o->description ?: 'Meldungen aus dem Ressort ' . $o->name . ' auf Merzenich Aktuell.'];
        $k = array_merge($k, ['typ' => 'ressort', 'titel' => $d[1], 'beschreibung' => $d[2], 'url' => ma_seo_liste_url(get_term_link($o)), 'krumen' => [['Start', $home], [$d[0], null]], 'liste' => ma_seo_liste()]);
    } elseif (get_query_var('ma_alle')) {
        $k = array_merge($k, ['typ' => 'liste', 'titel' => $r['nachrichten'][1], 'beschreibung' => $r['nachrichten'][2], 'url' => ma_seo_liste_url($home . 'nachrichten/'), 'krumen' => [['Start', $home], ['Nachrichten', null]], 'liste' => ma_seo_liste()]);
    } elseif (get_query_var('ma_unternehmen')) {
        $k = array_merge($k, ['typ' => 'liste', 'titel' => $r['unternehmen'][1], 'beschreibung' => $r['unternehmen'][2], 'url' => $home . 'unternehmen/', 'krumen' => [['Start', $home], ['Unternehmen', null]], 'liste' => ma_seo_liste()]);
    } elseif (is_tax('ma_location') && $o instanceof WP_Term) {
        $d = ma_seo_orte()[$o->slug] ?? ['name' => $o->name, 'beschreibung' => $o->description ?: 'Meldungen aus ' . $o->name . '.'];
        $k = array_merge($k, ['typ' => 'ort', 'ort' => isset(ma_seo_orte()[$o->slug]) ? $o->slug : 'merzenich', 'titel' => $d['name'] . ': Nachrichten aus dem Ortsteil der Gemeinde Merzenich', 'beschreibung' => ma_seo_kuerzen($o->description ?: $d['beschreibung']), 'url' => ma_seo_liste_url(get_term_link($o)), 'krumen' => [['Start', $home], [$d['name'], null]], 'liste' => ma_seo_liste()]);
    } elseif (is_tag() && $o instanceof WP_Term) {
        $k = array_merge($k, ['typ' => 'thema', 'titel' => $o->name . ': Meldungen aus Merzenich', 'beschreibung' => ma_seo_kuerzen($o->description ?: 'Alle Meldungen von Merzenich Aktuell zum Thema ' . $o->name . ' aus Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald.'), 'url' => ma_seo_liste_url(get_term_link($o)), 'krumen' => [['Start', $home], ['Thema', null], [$o->name, null]], 'liste' => ma_seo_liste()]);
    } elseif (is_post_type_archive()) {
        $typ = (string) get_query_var('post_type'); if (is_array(get_query_var('post_type'))) $typ = (string) (get_query_var('post_type')[0] ?? '');
        $slug = ['ma_event' => 'termine', 'ma_tip' => 'tipp', 'ma_club' => 'vereine'][$typ] ?? '';
        $obj = get_post_type_object($typ);
        $d = $r[$slug] ?? [$obj->labels->name ?? 'Liste', ($obj->labels->name ?? 'Liste') . ' in Merzenich', ($obj->labels->name ?? 'Einträge') . ' aus der Gemeinde Merzenich auf Merzenich Aktuell.'];
        $k = array_merge($k, ['typ' => 'liste', 'titel' => $d[1], 'beschreibung' => $d[2], 'url' => ma_seo_liste_url(get_post_type_archive_link($typ) ?: $home), 'krumen' => [['Start', $home], [$d[0], null]], 'liste' => ma_seo_liste()]);
    } elseif (is_search()) {
        $k = array_merge($k, ['typ' => 'suche', 'titel' => 'Suche: ' . get_search_query(), 'beschreibung' => 'Suche auf Merzenich Aktuell.', 'url' => '', 'index' => false, 'krumen' => []]);
    } elseif (is_404()) {
        $k = array_merge($k, ['typ' => '404', 'titel' => 'Seite nicht gefunden', 'beschreibung' => 'Diese Seite gibt es auf Merzenich Aktuell nicht.', 'url' => '', 'index' => false, 'krumen' => []]);
    } else {
        // Datums-, Autoren- und sonstige Archive: lesbar, aber nicht indexierbar.
        $k = array_merge($k, ['typ' => 'liste', 'titel' => html_entity_decode(wp_strip_all_tags(get_the_archive_title()), ENT_QUOTES, 'UTF-8'), 'beschreibung' => 'Meldungen auf Merzenich Aktuell.', 'url' => '', 'index' => false, 'krumen' => []]);
    }
    return $k;
}

/* ------------------------------------------------------------ Kopfausgabe */

add_filter('document_title_separator', fn() => '|');
add_filter('document_title_parts', function (array $teile): array {
    $k = ma_seo_kontext();
    if ($k['typ'] === 'start') return ['title' => $k['titel']];
    $teile['title'] = $k['titel'];
    $teile['site'] = MA_SEO_MARKE;
    unset($teile['tagline']);
    return $teile;
});

/* Eigene Ausgabe statt der Kernfunktionen: Canonical (auch für Listen), kein Shortlink, keine Generator- und Editor-Hinweise. */
add_action('init', function (): void {
    remove_action('wp_head', 'rel_canonical');
    remove_action('wp_head', 'wp_shortlink_wp_head', 10);
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
});

function ma_seo_head(): void {
    $k = ma_seo_kontext();
    $home = home_url('/');
    $e = fn($s) => esc_attr(html_entity_decode((string) $s, ENT_QUOTES, 'UTF-8'));
    $z = '';
    if ($k['beschreibung'] !== '') $z .= '<meta name="description" content="' . $e($k['beschreibung']) . '">' . "\n";
    $z .= '<meta name="robots" content="' . ma_seo_robots_wert($k['index'] && get_option('blog_public')) . '">' . "\n";
    if ($k['url'] !== '') $z .= '<link rel="canonical" href="' . esc_url($k['url']) . '">' . "\n";
    foreach (['google' => 'google-site-verification', 'bing' => 'msvalidate.01'] as $opt => $name) {
        $code = trim((string) get_option('ma_seo_' . $opt, ''));
        if ($code !== '') $z .= '<meta name="' . $name . '" content="' . $e($code) . '">' . "\n";
    }
    if ($k['index']) {
        [$lat, $lon] = ma_seo_mitte();
        $ort = ma_seo_orte()[$k['ort']] ?? ma_seo_orte()['merzenich'];
        $z .= '<meta name="geo.region" content="DE-NW"><meta name="geo.placename" content="' . $e($ort['name'] . ($k['ort'] !== 'merzenich' ? ', Merzenich' : '')) . '">';
        if ($k['ort'] === 'merzenich') $z .= '<meta name="geo.position" content="' . $lat . ';' . $lon . '"><meta name="ICBM" content="' . $lat . ', ' . $lon . '">';
        elseif (isset($ort['lat'])) $z .= '<meta name="geo.position" content="' . $ort['lat'] . ';' . $ort['lon'] . '"><meta name="ICBM" content="' . $ort['lat'] . ', ' . $ort['lon'] . '">';
        $z .= "\n";
    }
    $titel = $k['typ'] === 'start' ? MA_SEO_MARKE : $k['titel'];
    $z .= '<meta property="og:type" content="' . $e($k['og']) . '"><meta property="og:site_name" content="' . MA_SEO_MARKE . '"><meta property="og:locale" content="de_DE">' . "\n";
    $z .= '<meta property="og:title" content="' . $e($titel) . '">' . "\n";
    if ($k['beschreibung'] !== '') $z .= '<meta property="og:description" content="' . $e($k['beschreibung']) . '">' . "\n";
    if ($k['url'] !== '') $z .= '<meta property="og:url" content="' . esc_url($k['url']) . '">' . "\n";
    $b = $k['bild'];
    $z .= '<meta property="og:image" content="' . esc_url($b['url']) . '">' . ($b['w'] ? '<meta property="og:image:width" content="' . (int) $b['w'] . '"><meta property="og:image:height" content="' . (int) $b['h'] . '">' : '') . '<meta property="og:image:alt" content="' . $e($b['alt']) . '">' . "\n";
    if ($k['og'] === 'article') {
        if ($k['veroeffentlicht']) $z .= '<meta property="article:published_time" content="' . $e($k['veroeffentlicht']) . '">';
        if ($k['geaendert']) $z .= '<meta property="article:modified_time" content="' . $e($k['geaendert']) . '">';
        if ($k['ressort']) $z .= '<meta property="article:section" content="' . $e($k['ressort']) . '">';
        foreach ($k['tags'] as $t) $z .= '<meta property="article:tag" content="' . $e($t) . '">';
        if ($k['tags']) $z .= '<meta name="news_keywords" content="' . $e(implode(', ', $k['tags'])) . '">';
        $z .= "\n";
    }
    $z .= '<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="' . $e($titel) . '">' . ($k['beschreibung'] !== '' ? '<meta name="twitter:description" content="' . $e($k['beschreibung']) . '">' : '') . '<meta name="twitter:image" content="' . esc_url($b['url']) . '">' . "\n";
    if ($k['typ'] !== '404') {
        $sameas = array_values(array_filter(array_map('trim', (array) get_option('ma_seo_sameas', [])), 'ma_seo_url_gueltig'));
        $graph = ma_seo_graph($k, ['home' => $home, 'logo' => home_url('/assets/img/logo-on-light.png'), 'bild' => ma_seo_standardbild()['url'], 'sameas' => $sameas, 'mitte' => ma_seo_mitte()]);
        $z .= '<script type="application/ld+json">' . wp_json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
    }
    echo $z;
}
add_action('wp_head', 'ma_seo_head', 1);

/* ------------------------------------------------------------ Adressen: Ressorts kurz, Blättern, Feeds, Umleitungen */

add_filter('term_link', function (string $url, $term, string $tax): string {
    if ($tax === 'category' && $term instanceof WP_Term && in_array($term->slug, ma_seo_ressort_kategorien(), true)) return home_url('/' . $term->slug . '/');
    return $url;
}, 10, 3);

add_action('init', function (): void {
    $slugs = implode('|', ma_seo_ressort_kategorien());
    add_rewrite_rule("^({$slugs})/?$", 'index.php?category_name=$matches[1]', 'top');
    add_rewrite_rule("^({$slugs})/page/([0-9]+)/?$", 'index.php?category_name=$matches[1]&paged=$matches[2]', 'top');
    add_rewrite_rule("^({$slugs})/feed/(feed|rdf|rss|rss2|atom)/?$", 'index.php?category_name=$matches[1]&feed=$matches[2]', 'top');
    add_rewrite_rule("^({$slugs})/(feed|rdf|rss|rss2|atom)/?$", 'index.php?category_name=$matches[1]&feed=$matches[2]', 'top');
    add_rewrite_rule('^news-sitemap\.xml$', 'index.php?ma_seo=news', 'top');
    add_rewrite_rule('^llms\.txt$', 'index.php?ma_seo=llms', 'top');
    add_rewrite_rule('^suche/?$', 'index.php?ma_seo=suche', 'top');
}, 5);
add_filter('query_vars', function (array $v): array { $v[] = 'ma_seo'; return $v; });
add_filter('redirect_canonical', fn($ziel) => get_query_var('ma_seo') ? false : $ziel);

/* Nach einem Plugin-Update die Regeln einmal neu schreiben (Theme macht dasselbe für seine Adressen). */
add_action('init', function (): void {
    if (get_option('ma_seo_regeln') === MA_CORE_VERSION . '-2') return;
    flush_rewrite_rules(false);
    update_option('ma_seo_regeln', MA_CORE_VERSION . '-2', false);
}, 99);

add_action('template_redirect', function (): void {
    $pfad = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
    $pfadN = '/' . trim(strtolower($pfad), '/') . '/';
    // Statische Adressen (Fußzeile, Artikel) auf die WordPress-Adresse.
    $um = ma_seo_umleitungen();
    if (isset($um[$pfadN]) || isset($um[rtrim($pfadN, '/')])) {
        $ziel = $um[$pfadN] ?? $um[rtrim($pfadN, '/')];
        if ($ziel === '/?s=') $ziel .= rawurlencode(sanitize_text_field(wp_unslash($_GET['q'] ?? $_GET['s'] ?? '')));
        wp_safe_redirect(home_url($ziel), 301); exit;
    }
    if (get_query_var('ma_seo') === 'suche') { wp_safe_redirect(home_url('/?s=' . rawurlencode(sanitize_text_field(wp_unslash($_GET['q'] ?? $_GET['s'] ?? '')))), 301); exit; }
    // /category/<ressort>/… → /<ressort>/…
    if (preg_match('~^/category/(' . implode('|', ma_seo_ressort_kategorien()) . ')(/.*)?$~', $pfad, $m)) {
        wp_safe_redirect(home_url('/' . $m[1] . ($m[2] ?? '/')), 301); exit;
    }
    // Autorenarchive zeigen Anmeldenamen; es gibt nur die Redaktion.
    if (is_author()) { wp_safe_redirect(home_url('/redaktion/'), 301); exit; }
    // Anhangsseiten: zum Beitrag, sonst zur Datei.
    if (is_attachment()) {
        $a = get_queried_object();
        $ziel = $a instanceof WP_Post && $a->post_parent && get_post_status($a->post_parent) === 'publish' ? get_permalink($a->post_parent) : ($a instanceof WP_Post ? wp_get_attachment_url($a->ID) : home_url('/'));
        if ($ziel) { wp_safe_redirect($ziel, 301); exit; }
    }
    $was = get_query_var('ma_seo');
    if ($was === 'news') {
        nocache_headers(); header('Content-Type: application/xml; charset=utf-8'); header('Cache-Control: public, max-age=600');
        echo ma_seo_news_sitemap_xml(ma_seo_news_eintraege()); exit;
    }
    if ($was === 'llms') {
        header('Content-Type: text/plain; charset=utf-8'); header('Cache-Control: public, max-age=3600');
        $seiten = [];
        foreach (['ueber-uns' => 'Über uns', 'redaktion' => 'Redaktion', 'grundsaetze' => 'Publizistische Grundsätze', 'korrekturen' => 'Korrekturen', 'kontakt' => 'Kontakt', 'service' => 'Service', 'impressum' => 'Impressum', 'datenschutz' => 'Datenschutz'] as $slug => $name) {
            $p = get_page_by_path($slug, OBJECT, 'page');
            if ($p instanceof WP_Post && $p->post_status === 'publish') $seiten[] = [$name, get_permalink($p), ma_seo_kuerzen($p->post_excerpt, 120)];
        }
        echo ma_seo_llms_txt(home_url('/'), $seiten); exit;
    }
}, 1);

/** Einträge der News-Sitemap: veröffentlichte Meldungen der letzten 48 Stunden, keine gesponserten. */
function ma_seo_news_eintraege(): array {
    $posts = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1000, 'date_query' => [['after' => '48 hours ago']], 'orderby' => 'date', 'order' => 'DESC']);
    $aus = [];
    foreach ($posts as $p) {
        if (function_exists('ma_ist_gesponsert') && ma_ist_gesponsert($p)) continue;
        $aus[] = ['url' => get_permalink($p), 'titel' => html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8'), 'zeit' => get_post_time('c', false, $p)];
    }
    return $aus;
}

/* ------------------------------------------------------------ Sitemap und robots.txt */

add_filter('wp_sitemaps_add_provider', fn($provider, string $name) => $name === 'users' ? false : $provider, 10, 2);
add_filter('wp_sitemaps_post_types', fn(array $t) => array_diff_key($t, array_flip(['ma_obituary', 'ma_family_notice', 'attachment'])));
add_filter('wp_sitemaps_taxonomies', fn(array $t) => array_diff_key($t, array_flip(['ma_family_type', 'ma_source_status', 'post_format'])));
add_filter('robots_txt', fn(string $out, bool $public) => $public ? ma_seo_robots_txt($out, home_url('/')) : $out, 10, 2);

/* Ortsteile: Beschreibung am Begriff, wenn noch keine eingetragen ist (Text der statischen Ortsseiten). Nie überschreiben. */
function ma_seo_orte_beschreiben(): void {
    if (get_option('ma_seo_orte_114') === '1') return;
    foreach (ma_seo_orte() as $slug => $d) {
        $t = get_term_by('slug', $slug, 'ma_location');
        if ($t instanceof WP_Term && trim((string) $t->description) === '') wp_update_term($t->term_id, 'ma_location', ['description' => $d['beschreibung']]);
    }
    update_option('ma_seo_orte_114', '1', false);
}
add_action('admin_init', 'ma_seo_orte_beschreiben');

/* ------------------------------------------------------------ Backend: Merzenich Aktuell → SEO */

add_action('admin_menu', function (): void {
    add_submenu_page('merzenich-aktuell', 'SEO & Geo', 'SEO & Geo', 'manage_options', 'ma-seo', 'ma_seo_seite_admin');
}, 31);

add_action('admin_post_ma_seo_speichern', function (): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.', 403);
    check_admin_referer('ma_seo_speichern');
    $urls = array_values(array_filter(array_map(fn($u) => esc_url_raw(trim($u)), explode("\n", (string) wp_unslash($_POST['sameas'] ?? ''))), 'ma_seo_url_gueltig'));
    update_option('ma_seo_sameas', $urls, false);
    update_option('ma_seo_google', sanitize_text_field(wp_unslash($_POST['google'] ?? '')), false);
    update_option('ma_seo_bing', sanitize_text_field(wp_unslash($_POST['bing'] ?? '')), false);
    wp_safe_redirect(admin_url('admin.php?page=ma-seo&gespeichert=1')); exit;
});

function ma_seo_seite_admin(): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
    $home = home_url('/');
    echo '<div class="wrap"><h1>SEO &amp; Geo</h1>';
    if (isset($_GET['gespeichert'])) echo '<div class="notice notice-success is-dismissible"><p>Gespeichert.</p></div>';
    echo '<p>Das Plugin gibt auf jeder Seite Titel, Description, Canonical, Open Graph, Geo-Angaben und strukturierte Daten aus; Sitemaps und robots.txt sind eingerichtet. Diese Angaben kann nur der Betreiber liefern:</p>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ma_seo_speichern">'; wp_nonce_field('ma_seo_speichern');
    echo '<table class="form-table"><tr><th><label for="sameas">Offizielle Profile (sameAs)</label></th><td><textarea id="sameas" name="sameas" rows="4" class="large-text" placeholder="https://www.facebook.com/…&#10;https://www.instagram.com/…&#10;https://whatsapp.com/channel/…">' . esc_textarea(implode("\n", (array) get_option('ma_seo_sameas', []))) . '</textarea><p class="description">Eine Adresse je Zeile: Facebook, Instagram, WhatsApp-Kanal, YouTube. Erscheint in den strukturierten Daten der Organisation.</p></td></tr>';
    echo '<tr><th><label for="google">Google Search Console</label></th><td><input id="google" name="google" class="regular-text" value="' . esc_attr((string) get_option('ma_seo_google', '')) . '"><p class="description">Inhalt des HTML-Tags <code>google-site-verification</code> (nur der Code).</p></td></tr>';
    echo '<tr><th><label for="bing">Bing Webmaster Tools</label></th><td><input id="bing" name="bing" class="regular-text" value="' . esc_attr((string) get_option('ma_seo_bing', '')) . '"><p class="description">Inhalt des Tags <code>msvalidate.01</code>.</p></td></tr></table>';
    submit_button('Speichern'); echo '</form>';
    [$lat, $lon] = ma_seo_mitte();
    echo '<h2>Prüfen</h2><ul>';
    foreach (['wp-sitemap.xml' => 'Sitemap (Meldungen, Seiten, Termine, Vereine, Ressorts, Orte, Themen)', 'news-sitemap.xml' => 'News-Sitemap (Meldungen der letzten 48 Stunden)', 'robots.txt' => 'robots.txt', 'llms.txt' => 'llms.txt für KI-Suchen', 'feed/' => 'RSS-Feed'] as $pfad => $name) {
        echo '<li><a href="' . esc_url($home . $pfad) . '" target="_blank">' . esc_html($name) . '</a> <code>/' . esc_html($pfad) . '</code></li>';
    }
    echo '</ul><p>Strukturierte Daten einer Meldung prüfen: <a href="https://search.google.com/test/rich-results" target="_blank">Google Rich-Results-Test</a> oder <a href="https://validator.schema.org/" target="_blank">validator.schema.org</a> mit der Adresse der Meldung.</p>';
    echo '<p>Ortsmitte Merzenich für Geo-Angaben: ' . esc_html($lat . ', ' . $lon) . ' (aus den Wetter-Einstellungen). Ortsteil-Koordinaten für Golzheim und Girbelsrath stammen aus GeoNames; für Morschenich und Bürgewald werden keine ausgegeben, weil dort keine verlässliche Quelle vorliegt.</p>';
    echo '<h2>Nach dem Einspielen</h2><ol><li>Search Console: Property für ' . esc_html($home) . ' anlegen, Code oben eintragen, dann <code>wp-sitemap.xml</code> und <code>news-sitemap.xml</code> einreichen.</li><li>Google Publisher Center: Publikation „Merzenich Aktuell“ mit der Startseite anlegen (nur der Betreiber kann das).</li><li>Offizielle Profile oben eintragen, sobald sie bestehen.</li></ol></div>';
}
