<?php
/**
 * Nachrichtendienste (includes/feeds.php) ohne WordPress: latest.json-Eintrag,
 * WebSub-Nutzlast, Feed-Item-XML.
 * Aufruf: php qa/wordpress/feeds-test.php
 */
define('ABSPATH', __DIR__);
function add_action(...$a) {} function add_filter(...$a) {}
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/feeds.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-66s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "latest.json\n";
$e = ma_latest_eintrag('Titel', 'https://merzenich-aktuell.de/leben/x/', '2026-09-29T10:56:00+02:00', 'leben', 'golzheim', 'Anriss', 'https://merzenich-aktuell.de/wp-content/uploads/a.webp');
pruefe('Eintrag mit den Feldern der statischen Datei', array_keys($e), ['title', 'url', 'date', 'ressort', 'ort', 'teaser', 'image']);

echo "\nWebSub\n";
pruefe('Nutzlast: publish + je Feed hub.url, ohne Doppelte und Leere', ma_websub_nutzlast(['https://merzenich-aktuell.de/feed/', 'https://merzenich-aktuell.de/feed/', '', 'https://merzenich-aktuell.de/blaulicht/feed/']),
    'hub.mode=publish&hub.url=https%3A%2F%2Fmerzenich-aktuell.de%2Ffeed%2F&hub.url=https%3A%2F%2Fmerzenich-aktuell.de%2Fblaulicht%2Ffeed%2F');
pruefe('Hub ist der öffentliche Google-Hub', MA_WEBSUB_HUB, 'https://pubsubhubbub.appspot.com/');

echo "\nFeed-Item\n";
$x = ma_feed_item_xml('https://merzenich-aktuell.de/wp-content/uploads/r.webp', 139806, 'image/webp', 'Rauch & Feuer', 'Freiwillige Feuerwehr', 1536, 1024);
pruefe('enclosure mit Länge und Typ', str_contains($x, '<enclosure url="https://merzenich-aktuell.de/wp-content/uploads/r.webp" length="139806" type="image/webp" />'), true);
pruefe('media:content mit Maßen, Titel maskiert, Bildnachweis', str_contains($x, 'medium="image" type="image/webp" width="1536" height="1024"') && str_contains($x, '<media:title type="plain">Rauch &amp; Feuer</media:title>') && str_contains($x, '<media:credit role="photographer" scheme="urn:ebu">Freiwillige Feuerwehr</media:credit>'), true);
pruefe('Ohne Bild kein XML', ma_feed_item_xml('', 0, '', '', ''), '');
pruefe('Ohne Nachweis kein media:credit', str_contains(ma_feed_item_xml('https://x/a.jpg', 1, 'image/jpeg', '', ''), 'media:credit'), false);

echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
