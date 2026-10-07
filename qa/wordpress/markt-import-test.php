<?php
/**
 * Stellen und Immobilien aus dem Marktstand (Plugin 1.25.0,
 * includes/markt-import.php) und die Filter der Beiträge-Liste
 * (includes/beitragsliste.php), ohne WordPress.
 * Aufruf: php qa/wordpress/markt-import-test.php
 */
define('ABSPATH', __DIR__ . '/');
function add_action(...$a) {} function add_filter(...$a) {}
$wurzel = dirname(__DIR__, 2);
require "$wurzel/wordpress/plugin/merzenich-aktuell-core/includes/markt-import.php";
require "$wurzel/wordpress/plugin/merzenich-aktuell-core/includes/beitragsliste.php";
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-72s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Stelle aus market.json\n";
$job = ['id' => 'schall-industriekaufmann-2027', 'municipality' => 'Merzenich', 'district' => 'Merzenich', 'title' => 'Ausbildung Industriekaufmann/-frau (m/w/d)',
    'employer' => 'M. Schall GmbH & Co. KG', 'employment' => 'Ausbildung · Vollzeit · Start 01.08.2027', 'details' => 'Am Roßpfad 1–3, Merzenich',
    'sourceName' => 'M. Schall Karriere', 'sourceUrl' => 'https://mschall.de/ausbildung/', 'checkedAt' => '2026-10-06T13:18:31+02:00'];
$f = ma_markt_felder($job, 'ma_job');
pruefe('Kennung mit Typ', $f['meta']['ma_markt_id'], 'ma_job:schall-industriekaufmann-2027');
pruefe('Prüfdatum in Ortszeit', $f['meta']['ma_verified_at'], '2026-10-06 13:18:31');
pruefe('Ende 30 Tage nach Prüfung, Tagesende', $f['meta']['ma_end_at'], '2026-11-05 23:59:00');
pruefe('Freigabe für die Marktsperre gesetzt', $f['meta']['ma_release_confirmed'], '1');
pruefe('Ausbildung als Art, Bewerbung = Quelle', [$f['meta']['ma_job_type'], $f['meta']['ma_job_apply_url']], ['training', 'https://mschall.de/ausbildung/']);
pruefe('Karriereseite gilt als Direktanzeige', $f['meta']['ma_job_provider_type'], 'direct');
pruefe('Ortsteil Merzenich', $f['ortsteil'], 'merzenich');
pruefe('Text nennt die Quelle', str_contains($f['text'], 'Quelle: M. Schall Karriere.'), true);

echo "\nImmobilie aus market.json\n";
$haus = ['id' => 'golzheim-dg', 'municipality' => 'Merzenich', 'district' => 'Golzheim', 'title' => '4-Zimmer-Dachgeschosswohnung in Golzheim', 'offerType' => 'Miete',
    'price' => '700 € Kaltmiete', 'details' => '100 m² · 4 Zimmer · Im Hoverfeld 8', 'sourceName' => 'ImmobilienScout24',
    'sourceUrl' => 'https://www.immobilienscout24.de/expose/170213703', 'checkedAt' => '2026-09-27T11:46:00+02:00'];
$f = ma_markt_felder($haus, 'ma_property');
pruefe('Miete als Angebot (beide Felder)', [$f['meta']['ma_property_mode'], $f['meta']['ma_property_offer']], ['rent', 'Miete']);
pruefe('Zimmer und Wohnfläche aus den Details', [$f['meta']['ma_property_rooms'], $f['meta']['ma_property_area']], ['4', '100 m²']);
pruefe('Ende 35 Tage nach Prüfung', $f['meta']['ma_end_at'], '2026-11-01 23:59:00');
pruefe('Lage mit Ortsteil, Slug golzheim', [$f['meta']['ma_property_address'], $f['ortsteil']], ['Merzenich-Golzheim', 'golzheim']);
$kauf = ma_markt_felder(['offerType' => 'Kauf', 'municipality' => 'Nörvenich', 'details' => '140 m² · 5 Zimmer · 600 m² Grundstück'] + $haus, 'ma_property');
pruefe('Kauf, Grundstück getrennt von Wohnfläche', [$kauf['meta']['ma_property_mode'], $kauf['meta']['ma_property_area'], $kauf['meta']['ma_property_lot']], ['buy', '140 m²', '600 m²']);
pruefe('Nachbargemeinde: kein Ortsteil, Gemeinde gespeichert', [$kauf['ortsteil'], $kauf['meta']['ma_markt_gemeinde']], ['', 'Nörvenich']);

echo "\nWas nicht übernommen wird\n";
pruefe('Suchseite eines Portals ist kein Angebot', ma_markt_felder(['sourceUrl' => 'https://www.immowelt.de/suche/merzenich/wohnungen'] + $haus, 'ma_property'), null);
pruefe('ohne https-Quelle', ma_markt_felder(['sourceUrl' => 'http://example.org/x'] + $job, 'ma_job'), null);
pruefe('ohne Prüfdatum', ma_markt_felder(['checkedAt' => ''] + $job, 'ma_job'), null);
pruefe('unlesbares Prüfdatum', ma_markt_felder(['checkedAt' => 'gestern'] + $job, 'ma_job'), null);
pruefe('ohne Titel', ma_markt_felder(['title' => ' '] + $job, 'ma_job'), null);

echo "\nBeschäftigungsart\n";
pruefe('Teilzeit, Minijob, Praktikum, sonst Vollzeit', array_map('ma_markt_job_typ', ['Teilzeit · unbefristet', 'Minijob', 'Praktikum 3 Monate', 'Vollzeit']), ['parttime', 'minijob', 'internship', 'fulltime']);

echo "\nFilter der Beiträge-Liste\n";
pruefe('ohne Wahl keine Bedingung', ma_beitragsliste_meta_query([]), []);
pruefe('Startseite freigegeben', ma_beitragsliste_meta_query(['ma_sf' => 'ja']), [['key' => 'ma_startseite_freigabe', 'value' => 'ja']]);
pruefe('Nur in der Rubrik', ma_beitragsliste_meta_query(['ma_sf' => 'nur-rubrik']), [['key' => 'ma_startplatz', 'value' => 'aus']]);
$ohne = ma_beitragsliste_meta_query(['ma_rel' => 'ohne']);
pruefe('ohne Relevanz: fehlend oder leer', [$ohne[0]['relation'], $ohne[0][0]['compare'], $ohne[0][1]['compare']], ['OR', 'NOT EXISTS', 'IN']);
pruefe('Relevanz hoch ab 8, numerisch', ma_beitragsliste_meta_query(['ma_rel' => 'hoch'])[0], ['key' => 'ma_relevanz', 'value' => 8, 'compare' => '>=', 'type' => 'NUMERIC']);
pruefe('Bildrechte offen: Bild da und Rechte nicht bestätigt', count(ma_beitragsliste_meta_query(['ma_bild' => 'rechte-offen'])), 2);
pruefe('Filter kombinierbar', count(ma_beitragsliste_meta_query(['ma_sf' => 'offen', 'ma_rel' => 'mittel', 'ma_bild' => 'ohne'])), 3);
pruefe('unbekannter Wert wird ignoriert', ma_beitragsliste_meta_query(['ma_sf' => 'quatsch', 'ma_rel' => 'x']), []);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
