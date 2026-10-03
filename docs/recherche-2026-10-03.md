# Redaktioneller Recherche- und Dublettenabgleich · 03.10.2026

Zeitfenster: 19.09. bis 03.10.2026 (Europe/Berlin). Neue Beiträge in `inhalte/meldungen/2026-09-26-bis-10-03-redaktionsupdate.json` (9 Meldungen). Die Generator-Kette baut daraus Artikel, Orts- und Ressortlisten, die Suche, Startseite, Feeds und Sitemaps. Der Zeitpunkt der Meldungen orientiert sich an der Originalquelle, nicht an einer erfundenen Vor-Ort-Recherche. Die im Artikel verlinkten Originalquellen sind Pflichtfelder.

## Neu

- SC Merzenich gewinnt in Barmen mit 5:0 – zweiter deutlicher Sieg binnen sechs Tagen (https://www.fussball.de/mgc.newsdetail/-/article-uuid/03296PU34O000000VS5489BVVTRHJ3AS)
- 7:0 zur Pause: SC Merzenich schlägt Nörvenich-Hochkirchen mit 8:2 (https://www.fussball.de/mgc.newsdetail/-/article-uuid/0327HM16M8000000VS5489C0VV1LSU7O)
- FC Golzheim gewinnt beim FC Düren 77 mit 3:1 (https://golzheim1928.de/weiterhin-ein-sehr-guter-saisonstart-unsere-1-mannschaft-gewinnt-am-6-spieltag-der-kreisliga-a-dueren-auswaerts-bei)
- SV Morschenich unterliegt Merken im Spitzenspiel mit 0:1 (https://www.fussball.de/mgc.newsdetail/-/article-uuid/0327HMFQT8000000VS5489C0VV1LSU7O)
- Golzheims C-Jugend: Pokalsieg nach Verlängerung und zwei Qualifikationssiege (https://golzheim1928.de/starker-saisonstart-unserer-c-jugend-fc-golzheim-u15-die-neue-saison-ist-gestartet-und-unsere-u15-jahrgang-2012-konnte-bereit)
- Erweiterung des Golzheimer Sportlerheims: Streifenfundamente sind betoniert (https://golzheim1928.de/bildergeschichte-nr-3-unser-erweiterungsbau-waechst-weiter-nachdem-in-unserer-zweiten-bildergeschichte-die-streifenfu)
- Herbstlaub ab 12. Oktober am Bauhof Girbelsrath abgeben – Standort geändert (https://www.gemeinde-merzenich.de/aktuelles/pressemitteilungen/herbstlaub-2026.php)
- Kita Bärenstark in Golzheim sucht Erzieherin oder Erzieher als Krankheitsvertretung (https://www.gemeinde-merzenich.de/politik/stellenanngebote.php)
- Merzenicher Pfadfinder kündigen Teilnahme am Erntedankfest in Girbelsrath an (https://www.heimat-info.de/beitraege/ef9a1e2e-048c-4736-9669-782f3a5b46a5)

## Bereits vorhanden, daher nicht erneut eingespielt

- Motorradunfall Steinweg (Polizei 26.09.)
- Einsätze 130/26, 131/26, 132/26, 133/26 und 134/26
- Flutlicht FC Rhenania Girbelsrath
- Kleidertreff: Spendenannahme Erwachsenenkleidung pausiert
- Verkehrssperrung zum Ortsfest und Ratssitzung vom 30.09.

## Fotoentscheidung

Pressebilder, Vereinsbilder und Bilder aus Heimat-Info wurden nicht ohne Nutzungsrecht kopiert. Neue Spielberichte verwenden ausschließlich das redaktionell geprüfte Fußball-Motiv aus dem projektinternen Commons-Pool, kommunale Serviceartikel das vorhandene freigegebene Rathausmotiv. Beim Erntedankartikel bleibt das Beitragsbild leer, bis ein örtlich korrektes Originalfoto mit Nutzungsrecht vorliegt. Motive sind ausdrücklich Symbolbilder, keine Aufnahmen der geschilderten Ereignisse.

## Nicht ungeprüft veröffentlicht

- Abgelaufene Ankündigungen zum 02./03.10. nicht als Ereignisberichte ausgegeben.
- Eine genaue Uhrzeit des Erntedankfests in Girbelsrath wurde aus der Pfadfinder-Ankündigung nicht abgeleitet.
- Kreismajestätenschießen 10.10.: Primärdetailseite und widerspruchsfreie Venue-/Uhrzeitangabe noch nachprüfen, daher kein Terminimport.
- Nachbarstädte ohne klaren Bezug zur Gemeinde Merzenich nicht pauschal in den Ortsindex einsortiert.

## Technischer Abgleich

Keine Änderung der Root-Preview (`index.html`/`content.json`). Automatische QA (`.github/workflows/qa.yml`) führt `node deploy/kette.mjs`, `--check`, Routen-/Bild-/Browserprüfungen aus und committed generierte `chatgpt-site`-Ausgaben bei Erfolg zurück nach main. Die tatsächliche öffentliche Bereitstellung erfolgt nach Coolify-Deploy; ohne dessen Liveprüfung ist ein GitHub-Push nicht mit einem nachgewiesenen Live-Deploy gleichzusetzen.
