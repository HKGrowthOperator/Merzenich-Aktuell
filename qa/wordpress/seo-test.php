<?php
/**
 * SEO und Geo (includes/seo.php) ohne WordPress: Kürzen, Robots, robots.txt,
 * News-Sitemap, llms.txt, Umleitungen und der JSON-LD-Graph je Seitentyp.
 * Aufruf: php qa/wordpress/seo-test.php
 */
define('ABSPATH', __DIR__); define('MA_CORE_VERSION', 'test');
$GLOBALS['opt'] = ['ma_weather_settings' => ['latitude' => '50.8317', 'longitude' => '6.5361']];
function add_action(...$a) {} function add_filter(...$a) {}
function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function update_option($k, $v, $a = null) { $GLOBALS['opt'][$k] = $v; return true; }

require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/seo.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-66s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
$HOME = 'https://merzenich-aktuell.de/';
$ORG = ['home' => $HOME, 'logo' => $HOME . 'assets/img/logo-on-light.png', 'bild' => $HOME . 'assets/img/og-default.jpg', 'sameas' => [], 'mitte' => [50.8317, 6.5361]];
$typen = fn(array $g) => array_map(fn($e) => $e['@type'], $g['@graph']);
$finde = fn(array $g, string $typ) => array_values(array_filter($g['@graph'], fn($e) => $e['@type'] === $typ))[0] ?? null;

echo "Helfer\n";
pruefe('Kürzen: Tags, Shortcodes und Entities raus', ma_seo_kuerzen('<p>Die &amp; der [ma_formular typ="x"] Text</p>'), 'Die & der Text');
$lang = str_repeat('Feuerwehr Merzenich löscht Brand ', 10);
$k = ma_seo_kuerzen($lang);
pruefe('Kürzen auf 160 Zeichen am Wortende mit …', mb_strlen($k) <= 160 && str_ends_with($k, '…') && !str_contains($k, ' …'), true);
pruefe('Kurzer Text bleibt unverändert', ma_seo_kuerzen('Kurz.'), 'Kurz.');
pruefe('Robots indexierbar mit Vorschauregeln', ma_seo_robots_wert(true), 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1');
pruefe('Robots nicht indexierbar', ma_seo_robots_wert(false), 'noindex,follow');
pruefe('Ortsmitte aus den Wetter-Einstellungen', ma_seo_mitte(), [50.8317, 6.5361]);
$r = ma_seo_robots_txt("User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n\nSitemap: {$HOME}wp-sitemap.xml\n", $HOME);
pruefe('robots.txt: Suche, Formularrückmeldung, News-Sitemap', str_contains($r, 'Disallow: /?s=') && str_contains($r, 'Disallow: /*?gesendet=') && str_contains($r, "Sitemap: {$HOME}news-sitemap.xml") && str_contains($r, 'Disallow: /wp-admin/'), true);
pruefe('robots.txt: News-Sitemap nur einmal', substr_count(ma_seo_robots_txt($r, $HOME), 'news-sitemap.xml'), 1);
$x = ma_seo_news_sitemap_xml([['url' => $HOME . 'blaulicht/a-b/', 'titel' => 'Brand & Rauch', 'zeit' => '2026-10-01T10:00:00+02:00']]);
pruefe('News-Sitemap: Namensraum, Publikation, Titel maskiert', str_contains($x, 'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"') && str_contains($x, '<news:name>Merzenich Aktuell</news:name><news:language>de</news:language>') && str_contains($x, '<news:title>Brand &amp; Rauch</news:title>') && str_contains($x, '<news:publication_date>2026-10-01T10:00:00+02:00</news:publication_date>'), true);
$l = ma_seo_llms_txt($HOME, [['Über uns', $HOME . 'ueber-uns/', 'Wer dahintersteht.']]);
pruefe('llms.txt: Marke, Ressorts, Orte, Seiten, Anbieterin, keine Vorschau-Domain', str_starts_with($l, '# Merzenich Aktuell') && str_contains($l, "[Blaulicht]({$HOME}blaulicht/)") && str_contains($l, "[Golzheim]({$HOME}ort/golzheim/)") && str_contains($l, '[Über uns]') && str_contains($l, 'KBS Management GmbH') && !str_contains($l, 'hk-growthoperator') && !str_contains($l, 'TODO'), true);
pruefe('Profil-Adressen: nur mit Schema und Host', array_map('ma_seo_url_gueltig', ['https://www.facebook.com/x', 'http://kein-link', 'facebook.com/x', 'https://whatsapp.com/channel/abc']), [true, false, false, true]);
pruefe('Umleitungen statischer Adressen', ma_seo_umleitungen()['/autor/redaktion/'] . ' ' . ma_seo_umleitungen()['/termine/melden/'] . ' ' . ma_seo_umleitungen()['/feed.xml'], '/redaktion/ /termin-melden/ /feed/');
pruefe('Ressorts mit kurzer Adresse sind Kategorien', ma_seo_ressort_kategorien(), ['blaulicht', 'sport', 'rathaus', 'leben', 'wirtschaft', 'menschen', 'vereine']);
foreach (ma_seo_ressorte() as $slug => $d) if (count($d) !== 3 || $d[2] === '' || mb_strlen($d[2]) > 200) $fehler++;
pruefe('Jedes Ressort hat Label, Titel und Beschreibung ≤ 200 Zeichen', true, true);
pruefe('Koordinaten nur mit Quelle (Golzheim, Girbelsrath), nicht für Morschenich/Bürgewald', [isset(ma_seo_orte()['golzheim']['lat']), isset(ma_seo_orte()['girbelsrath']['lat']), isset(ma_seo_orte()['morschenich']['lat']), isset(ma_seo_orte()['buergewald']['lat'])], [true, true, false, false]);

pruefe('Startseite: Description ≤ 155 Zeichen, Titel mit „Nachrichten aus Merzenich“', [mb_strlen(MA_SEO_BESCHREIBUNG) <= 155, str_contains(MA_SEO_STARTTITEL, 'Nachrichten aus Merzenich'), mb_strlen(MA_SEO_STARTTITEL) < 100], [true, true, true]);
pruefe('llms.txt nutzt die Langfassung', str_contains($l, 'Jede Meldung mit Quelle und Bildcredit'), true);

echo "\nIndexNow\n";
$key = ma_indexnow_key();
pruefe('Schlüssel: 32 Hex, stabil', [preg_match('/^[a-f0-9]{32}$/', $key), ma_indexnow_key() === $key], [1, true]);
$n = ma_indexnow_nutzlast('merzenich-aktuell.de', $key, ['https://merzenich-aktuell.de/blaulicht/a/', 'https://merzenich-aktuell.de/blaulicht/a/', 'https://fremd.example/x/', 'https://merzenich-aktuell.de/']);
pruefe('Nutzlast: Host, Schlüsseldatei, nur eigene Adressen, ohne Doppelte', [$n['host'], $n['keyLocation'], $n['urlList']], ['merzenich-aktuell.de', 'https://merzenich-aktuell.de/' . $key . '.txt', ['https://merzenich-aktuell.de/blaulicht/a/', 'https://merzenich-aktuell.de/']]);

echo "\nJSON-LD: Startseite\n";
$g = ma_seo_graph(['typ' => 'start', 'titel' => 'Merzenich Aktuell', 'beschreibung' => MA_SEO_BESCHREIBUNG, 'url' => $HOME, 'bild' => ['url' => $ORG['bild'], 'w' => 1200, 'h' => 630, 'alt' => 'MA'], 'krumen' => []], $ORG);
pruefe('Organisation, WebSite, WebPage, Place; keine Brotkrumen', $typen($g), ['NewsMediaOrganization', 'WebSite', 'WebPage', 'Place']);
$org = $finde($g, 'NewsMediaOrganization');
pruefe('Organisation: Anbieterin KBS mit Anschrift, Transparenzseiten, kein sameAs ohne Eintrag', $org['parentOrganization']['address']['postalCode'] === '51371' && $org['publishingPrinciples'] === $HOME . 'grundsaetze/' && $org['masthead'] === $HOME . 'ueber-uns/' && !isset($org['sameAs']) && !isset($org['foundingDate']), true);
pruefe('WebSite mit Suche über ?s=', $finde($g, 'WebSite')['potentialAction']['target']['urlTemplate'], $HOME . '?s={search_term_string}');
pruefe('Place Merzenich mit Ortsmitte aus den Einstellungen', $finde($g, 'Place')['geo'], ['@type' => 'GeoCoordinates', 'latitude' => 50.8317, 'longitude' => 6.5361]);
$g2 = ma_seo_graph(['typ' => 'start', 'titel' => 'x', 'beschreibung' => 'y', 'url' => $HOME, 'bild' => null, 'krumen' => []], $ORG + ['sameas' => ['https://www.facebook.com/x']]);
pruefe('sameAs nur mit Eintrag des Betreibers', $finde(ma_seo_graph(['typ' => 'start', 'titel' => 'x', 'beschreibung' => 'y', 'url' => $HOME, 'bild' => null, 'krumen' => []], array_merge($ORG, ['sameas' => ['https://www.facebook.com/x']])), 'NewsMediaOrganization')['sameAs'], ['https://www.facebook.com/x']);

echo "\nJSON-LD: Meldung\n";
$k = ['typ' => 'artikel', 'titel' => 'Rauch über Bürgewald', 'beschreibung' => 'Zwei Nutzfeuer.', 'url' => $HOME . 'blaulicht/rauch/', 'bild' => ['url' => $HOME . 'wp-content/uploads/r.jpg', 'w' => 1600, 'h' => 900, 'alt' => 'Rauchwolke'], 'ort' => 'buergewald', 'ressort' => 'Blaulicht', 'tags' => ['Feuerwehr', 'Bürgewald'], 'veroeffentlicht' => '2026-09-28T20:52:00+02:00', 'geaendert' => '2026-09-29T08:00:00+02:00', 'woerter' => 120, 'quelle' => 'https://www.feuerwehr-merzenich.de/x', 'gesponsert' => '',
    'krumen' => [['Start', $HOME], ['Blaulicht', $HOME . 'blaulicht/'], ['Bürgewald', $HOME . 'ort/buergewald/'], ['Rauch über Bürgewald', null]]];
$g = ma_seo_graph($k, $ORG);
pruefe('NewsArticle, Place, BreadcrumbList', $typen($g), ['NewsMediaOrganization', 'WebSite', 'NewsArticle', 'Place', 'BreadcrumbList']);
$a = $finde($g, 'NewsArticle');
pruefe('Artikel: Kopf, Zeiten, Redaktion als Autor, Verlag, frei lesbar', $a['headline'] === 'Rauch über Bürgewald' && $a['datePublished'] === '2026-09-28T20:52:00+02:00' && $a['dateModified'] === '2026-09-29T08:00:00+02:00' && $a['author'][0]['url'] === $HOME . 'redaktion/' && $a['publisher'] === ['@id' => $HOME . '#organization'] && $a['isAccessibleForFree'] === true, true);
pruefe('Artikel: Bild mit Maßen, Ressort, Schlagworte, Quelle als citation/isBasedOn', $a['image'][0]['width'] === 1600 && $a['articleSection'] === 'Blaulicht' && $a['keywords'] === 'Feuerwehr, Bürgewald' && $a['citation'] === ['https://www.feuerwehr-merzenich.de/x'] && $a['isBasedOn'][0] === 'https://www.feuerwehr-merzenich.de/x' && $a['wordCount'] === 120, true);
pruefe('Artikel: Ortsbezug Bürgewald (PLZ 52399), speakable, kein sponsor', $a['contentLocation']['name'] === 'Bürgewald' && $a['contentLocation']['address']['postalCode'] === '52399' && $a['about'] === ['@id' => $HOME . 'ort/buergewald/#place'] && isset($a['speakable']) && !isset($a['sponsor']), true);
pruefe('Place Bürgewald ohne erfundene Koordinaten', isset($finde($g, 'Place')['geo']), false);
$b = $finde($g, 'BreadcrumbList')['itemListElement'];
pruefe('Brotkrumen: 4 Stufen, letzte ohne item', count($b) === 4 && $b[1]['item'] === $HOME . 'blaulicht/' && $b[3]['name'] === 'Rauch über Bürgewald' && !isset($b[3]['item']), true);
$k['gesponsert'] = 'Musterfirma'; $k['quelle'] = ''; $k['bild'] = null; $k['tags'] = [];
$a = $finde(ma_seo_graph($k, $ORG), 'NewsArticle');
pruefe('Gesponsert: sponsor gesetzt; ohne Quelle/Bild/Tags keine leeren Felder', $a['sponsor']['name'] === 'Musterfirma' && !isset($a['citation']) && !isset($a['image']) && !isset($a['keywords']), true);

echo "\nJSON-LD: Listen, Ort, Termin, Verein, Seite\n";
$g = ma_seo_graph(['typ' => 'ressort', 'titel' => 'Blaulicht Merzenich', 'beschreibung' => 'Einsätze.', 'url' => $HOME . 'blaulicht/', 'bild' => null, 'krumen' => [['Start', $HOME], ['Blaulicht', null]], 'liste' => [[$HOME . 'blaulicht/a/', 'A'], [$HOME . 'blaulicht/b/', 'B']]], $ORG);
$c = $finde($g, 'CollectionPage');
pruefe('Ressort: CollectionPage mit ItemList (2 Einträge, Positionen)', $c['mainEntity']['itemListElement'][1] === ['@type' => 'ListItem', 'position' => 2, 'url' => $HOME . 'blaulicht/b/', 'name' => 'B'], true);
$g = ma_seo_graph(['typ' => 'ort', 'ort' => 'golzheim', 'titel' => 'Golzheim', 'beschreibung' => 'x', 'url' => $HOME . 'ort/golzheim/', 'bild' => null, 'krumen' => [['Start', $HOME], ['Golzheim', null]], 'liste' => []], $ORG);
$p = $finde($g, 'Place');
pruefe('Ortsseite: Place Golzheim mit GeoNames-Koordinaten, Beschreibung, in der Gemeinde', $p['geo']['latitude'] === 50.83966 && str_contains($p['description'], 'St. Gregorius') && $p['containedInPlace']['name'] === 'Gemeinde Merzenich' && $finde($g, 'CollectionPage')['about'] === ['@id' => $p['@id']], true);
$g = ma_seo_graph(['typ' => 'termin', 'titel' => 'Ratssitzung', 'beschreibung' => 'Sitzung.', 'url' => $HOME . 'termine/rat/', 'bild' => null, 'krumen' => [['Start', $HOME], ['Termine', $HOME . 'termine/'], ['Ratssitzung', null]], 'termin' => ['start' => '2026-09-30T18:00:00+02:00', 'ende' => '', 'ort' => 'Rathaus Merzenich', 'veranstalter' => 'Gemeinde Merzenich', 'preis' => 'kostenfrei']], $ORG);
$e = $finde($g, 'Event');
pruefe('Termin: geplant trägt eventStatus Scheduled', $e['eventStatus'] ?? '', 'https://schema.org/EventScheduled');
$gv = ma_seo_graph(['typ' => 'termin', 'titel' => 'Alt', 'beschreibung' => 'x', 'url' => $HOME . 'termine/alt/', 'bild' => null, 'krumen' => [], 'termin' => ['start' => '2026-01-01T10:00:00+01:00', 'ende' => '', 'vorbei' => true, 'ort' => '', 'veranstalter' => '', 'preis' => '']], $ORG);
pruefe('Vergangener Termin: Event ohne eventStatus, mit startDate', [isset($finde($gv, 'Event')['eventStatus']), $finde($gv, 'Event')['startDate']], [false, '2026-01-01T10:00:00+01:00']);
pruefe('Termin: Event mit Beginn, Ort, Veranstalter, kostenfrei, ohne endDate', $e['startDate'] === '2026-09-30T18:00:00+02:00' && !isset($e['endDate']) && $e['location']['name'] === 'Rathaus Merzenich' && $e['organizer']['name'] === 'Gemeinde Merzenich' && $e['isAccessibleForFree'] === true, true);
$g = ma_seo_graph(['typ' => 'verein', 'titel' => 'SC 1919 Merzenich', 'beschreibung' => 'Fußball.', 'url' => $HOME . 'vereine/sc/', 'bild' => null, 'ort' => 'merzenich', 'krumen' => [['Start', $HOME], ['Vereine', $HOME . 'vereine/'], ['SC', null]], 'verein' => ['adresse' => 'Sportplatz 1', 'website' => 'https://sc-merzenich.de', 'gegruendet' => '1919', 'sportarten' => 'Fußball', 'logo' => '']], $ORG);
$v = $finde($g, 'SportsClub');
pruefe('Verein mit Sportart: SportsClub, Gründungsjahr, Website als sameAs, Adresse', $v['foundingDate'] === '1919' && $v['sameAs'] === ['https://sc-merzenich.de'] && $v['address']['streetAddress'] === 'Sportplatz 1' && !isset($v['logo']), true);
$g = ma_seo_graph(['typ' => 'verein', 'titel' => 'Chor', 'beschreibung' => 'x', 'url' => $HOME . 'vereine/chor/', 'bild' => null, 'krumen' => [], 'verein' => ['adresse' => '', 'website' => '', 'gegruendet' => 'um 1900', 'sportarten' => '', 'logo' => '']], $ORG);
pruefe('Verein ohne Sportart: Organization, unklare Gründung weggelassen', isset($finde($g, 'Organization')['name']) && !isset($finde($g, 'Organization')['foundingDate']), true);
$g = ma_seo_graph(['typ' => 'seite', 'titel' => 'Über uns', 'beschreibung' => 'Wer.', 'url' => $HOME . 'ueber-uns/', 'bild' => null, 'seitenart' => 'AboutPage', 'geaendert' => '2026-10-02T10:00:00+02:00', 'krumen' => [['Start', $HOME], ['Über uns', null]]], $ORG);
pruefe('Seite: AboutPage mit dateModified und Brotkrumen', $finde($g, 'AboutPage')['dateModified'] === '2026-10-02T10:00:00+02:00' && $finde($g, 'BreadcrumbList') !== null, true);
pruefe('Graph ist gültiges JSON ohne leere Werte', !str_contains(json_encode($g), '""') && !str_contains(json_encode($g), 'null'), true);

echo "\nGoogle-Bestätigungsdatei\n";
pruefe('Name aus Dateiname, mit .html, aus Adresse; Fremdes abgelehnt', [ma_seo_google_datei_name('googlef2b56ca4196206a7'), ma_seo_google_datei_name(' googlef2b56ca4196206a7.html '), ma_seo_google_datei_name('https://merzenich-aktuell.de/googlef2b56ca4196206a7.html'), ma_seo_google_datei_name('google123.html'), ma_seo_google_datei_name('')], ['googlef2b56ca4196206a7', 'googlef2b56ca4196206a7', 'googlef2b56ca4196206a7', '', '']);
pruefe('Inhalt exakt wie von Google verlangt', ma_seo_google_datei_inhalt('googlef2b56ca4196206a7'), 'google-site-verification: googlef2b56ca4196206a7.html');

echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
