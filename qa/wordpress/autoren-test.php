<?php
/**
 * Echte Autoren (Plugin 1.26.0, includes/autoren.php und seo.php) und das
 * Zusammenführen der Schlagwörter (includes/schlagwoerter.php), ohne WordPress.
 * Aufruf: php qa/wordpress/autoren-test.php
 */
define('ABSPATH', __DIR__ . '/');
function add_action(...$a) {} function add_filter(...$a) {}
$wurzel = dirname(__DIR__, 2);
require "$wurzel/wordpress/plugin/merzenich-aktuell-core/includes/autoren.php";
require "$wurzel/wordpress/plugin/merzenich-aktuell-core/includes/schlagwoerter.php";
// seo.php braucht für die reinen Funktionen nur Konstanten und Filter-Stubs.
require "$wurzel/wordpress/plugin/merzenich-aktuell-core/includes/seo.php";
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-72s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Wer mit Namen erscheint\n";
pruefe('Person der Redaktion mit Funktion', ma_autor_art(['person' => true, 'name' => 'Petra Probe', 'funktion' => 'Redakteurin', 'slug' => 'petra-probe']), ['art' => 'person', 'name' => 'Petra Probe', 'funktion' => 'Redakteurin', 'slug' => 'petra-probe']);
pruefe('Person ohne Funktion heißt „Redaktion“', ma_autor_art(['person' => true, 'name' => 'Tivi'])['funktion'], 'Redaktion');
pruefe('Polizei-Partner: Name ohne „(Partner)“, Funktion Polizei', array_slice(ma_autor_art(['partner' => true, 'rolle' => 'ma_polizei_partner', 'name' => 'Polizei Düren (Partner)']), 0, 3), ['art' => 'organisation', 'name' => 'Polizei Düren', 'funktion' => 'Polizei']);
pruefe('Gemeinde-Partner heißt Gemeinde', ma_autor_art(['partner' => true, 'rolle' => 'ma_rathaus_partner', 'name' => 'Gemeinde Merzenich (Partner)'])['funktion'], 'Gemeinde');
pruefe('Sammelkonto (HK Growth) bleibt Redaktion', ma_autor_art(['name' => 'HK Growth'])['name'], 'Redaktion Merzenich Aktuell');
pruefe('Person ohne Namen fällt auf Redaktion zurück', ma_autor_art(['person' => true, 'name' => '  '])['art'], 'redaktion');
pruefe('Initialen', [ma_autor_initialen('Polizei Düren'), ma_autor_initialen('Tivi'), ma_autor_initialen('Anna-Lena Ört'), ma_autor_initialen('')], ['PD', 'T', 'AL', 'MA']);

echo "\nFreigabe setzt den Autor\n";
pruefe('Person gibt Abgleich-Entwurf frei: wird Autorin', ma_autor_freigabe_uebernimmt('draft', 'publish', true, false, false), true);
pruefe('auch aus „In Prüfung“ und „ausstehend“', [ma_autor_freigabe_uebernimmt('ma_in_pruefung', 'publish', true, false, false), ma_autor_freigabe_uebernimmt('pending', 'publish', true, false, false)], [true, true]);
pruefe('Partner-Meldung behält den Partner', ma_autor_freigabe_uebernimmt('pending', 'publish', true, false, true), false);
pruefe('Meldung einer anderen Person bleibt deren', ma_autor_freigabe_uebernimmt('draft', 'publish', true, true, false), false);
pruefe('Freigabe durch Sammelkonto ändert nichts', ma_autor_freigabe_uebernimmt('draft', 'publish', false, false, false), false);
pruefe('veröffentlichte Meldung (Aktualisierung) nie umschreiben', ma_autor_freigabe_uebernimmt('publish', 'publish', true, false, false), false);
pruefe('geplante Meldung (Cron) nicht umschreiben', ma_autor_freigabe_uebernimmt('future', 'publish', true, false, false), false);

echo "\nAutor für Google (NewsArticle)\n";
$home = 'https://merzenich-aktuell.de/';
pruefe('Person mit Seite, Funktion, Arbeitgeber', ma_seo_autor_schema(['art' => 'person', 'name' => 'Petra Probe', 'funktion' => 'Redakteurin', 'url' => $home . 'autor/petra-probe/'], $home),
    ['@type' => 'Person', 'name' => 'Petra Probe', 'url' => $home . 'autor/petra-probe/', 'jobTitle' => 'Redakteurin', 'worksFor' => ['@id' => $home . '#organization']]);
pruefe('Partner als Organisation', ma_seo_autor_schema(['art' => 'organisation', 'name' => 'Polizei Düren', 'url' => $home . 'autor/polizei-dueren/'], $home)['@type'], 'Organization');
pruefe('ohne Angabe: Redaktion wie bisher', ma_seo_autor_schema([], $home), ['@type' => 'Organization', 'name' => 'Redaktion Merzenich Aktuell', 'url' => $home . 'redaktion/']);

echo "\nThemenseiten und Startseite\n";
pruefe('Thema ab 3 Meldungen in Google', [ma_seo_thema_indexierbar(1), ma_seo_thema_indexierbar(2), ma_seo_thema_indexierbar(3), ma_seo_thema_indexierbar(37)], [false, false, true, true]);
pruefe('Startseiten-Titel unter 65 Zeichen, „Merzenich“ vorn', [mb_strlen(MA_SEO_STARTTITEL) < 65, str_starts_with(MA_SEO_STARTTITEL, 'Merzenich')], [true, true]);

echo "\nSchlagwörter zusammenführen\n";
pruefe('Ressort-Schlagwort führt zum Ressort', ma_schlagwort_ziel('Blaulicht'), ['ressort', 'blaulicht', '/blaulicht/']);
pruefe('Ortsteil-Schlagwort führt zur Ortsseite', ma_schlagwort_ziel('Bürgewald'), ['ort', 'buergewald', '/ort/buergewald/']);
pruefe('Unterbegriff geht im Oberbegriff auf (Slug des Ziels)', ma_schlagwort_ziel('Jugendfußball', 'fussball'), ['thema', 'Fußball', '/thema/fussball/']);
pruefe('andere Schlagwörter bleiben', [ma_schlagwort_ziel('Feuerwehr'), ma_schlagwort_ziel('Kreisliga A')], [null, null]);
$karte = ['grundschule' => '/thema/schule/', 'golzheim' => '/ort/golzheim/'];
pruefe('alte Adresse leitet weiter, auch ohne Schrägstrich', [ma_schlagwort_weiterleitung('/thema/grundschule/', $karte), ma_schlagwort_weiterleitung('/thema/Golzheim', $karte)], ['/thema/schule/', '/ort/golzheim/']);
pruefe('unbekannte und fremde Adressen nicht', [ma_schlagwort_weiterleitung('/thema/feuerwehr/', $karte), ma_schlagwort_weiterleitung('/leben/grundschule/', $karte)], ['', '']);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
