# Merzenich Aktuell – Morgenlauf (Claude), 4. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung ab 09:13 Uhr bis etwa 10:00 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 03.11. nachmittags und Nachlauf](2026-11-03-claude-nachmittags.md), seither neu [ChatGPT 04.11. morgens](2026-11-04-morgens.md). main vor dem Lauf: `9acc73b`.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 2 | Energiemanagement (Kurzmeldung, 314 Wörter, `relevanz` 3); Grundschule Golzheim (Standard, 1.075 Wörter, `relevanz` 2) |
| Aktualisierte oder ausgebaute Bestandsmeldungen | 2 | Solarpark an der A4 von der Kurzmeldung zum Standardtext (827 Wörter) mit Themenfoto; Nachtrag mit Hinweis auf die Folgemeldung im Golzheim-Artikel vom 25.10. |
| Neue Termine (live / Entwurf) | 3 / 0 | Volkstrauertag Morschenich 15.11., Adventsfenster Morschenich 28.11., VHS-Bildvortrag Wien 12.01.2027 |
| Entwürfe freigegeben | 2 | Heimat-Info-Automatik vom 02.11.: Gottesdienst 22.11. (Niederzier), Weihnachtsschießen und Weihnachtsfeier Morschenich 06.12.; je zwei Quellen |
| Termine 04.–17.11. an der Quelle nachgeprüft | 16 von 24 | 8 Termine aus Amtsblatt 11, Pfarrbrief und Heimat-Info-Beitrag nicht erneut gelesen (keine neue Ausgabe laut ChatGPT 07:06); VHS 10.11. über die vorläufige Programmübersicht |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | Golzheim 8 Quellen (Ratsinfo, Bezirksregierung, Städtetag/StGB, Landtag NRW), 11 Aussagen; Energiemanagement 4 Quellen, 4 Aussagen | |
| Bilder gefunden / geprüft / integriert | 3 / 3 / 1 | neu integriert: Solarpark-Symbolfoto (Commons, Pool „themen“); Rathaus und Klassenzimmer aus bestehenden Pools angesehen |
| Kette / QA / redaktion-lauf | zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | |
| Mindesttakt 04.11. | 2 Meldungen | beide Rathaus (09:24 und 09:45) |
| Werkzeug | `qa/vereinsseiten.mjs` erkennt Terminlisten unter Jahresüberschrift | Fund: Terminseite der Schützenbruderschaft Morschenich (Daten ohne Jahr) |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-SOLARPARK-A4-E3 | Ausbau zum Standardtext: „Solarpark an der A4 in Merzenich: RWE plant bis zu 45 Megawatt, Bauausschuss berät am 12. November“ (827 Wörter, 12 Aussagen im Prüfprotokoll); Themenfoto statt Rathaus | `inhalte/meldungen/2026-11-03-recherche-claude-nachmittags.json` → `/rathaus/solarpark-a4-merzenich-bauausschuss-12-november-2026-photovoltaik-batteriespeicher/` | Kurzbegründung und Übersichtsplan (Anlagen zu 144/2026), Energiesteckbrief, Vorlagen 144, 142, M142, M139; Commits `5969b40`, `2c5a224` |
| MA-EMS-SACHSTAND-2026 | „Energiemanagement für Merzenichs Gemeindegebäude: Verwaltung erwartet Abschluss Ende 2026 statt bis 2029“ (Kurzmeldung, `relevanz` 3) | `inhalte/meldungen/2026-11-04-recherche-claude-morgens.json` → `/rathaus/energiemanagement-gemeinde-merzenich-gebaeude-fertig-ende-2026-statt-2029/` | M139/2026 vollständig, Tagesordnung 12.11., Energiesteckbrief (Selbstauskunft der Gemeinde, im Prüfprotokoll begründet); Commit `179c885` |
| GO-OGS-138-2026 (neu) | Folgemeldung „Grundschule Golzheim in Merzenich: Umbau kostet 12 Millionen Euro, Gemeinde soll rund 7,5 Millionen tragen“ (Standard, 1.075 Wörter, `relevanz` 2) | `inhalte/meldungen/2026-11-04-recherche-claude-morgens.json` → `/rathaus/grundschule-golzheim-merzenich-umbau-12-millionen-euro-gemeindeanteil-7-5-millionen/` | Vorlage 138/2026 mit Kostenschätzung (23.10.) und E-Mail der Bezirksregierung (20.10.), Vorlage 130/2026 mit Kostenschätzung (29.09.) und Schreiben Städtetag/StGB NRW (18.09.), Beschlussauszug Rat 30.09., Tagesordnung 12.11., Landtag NRW Drucksache 18/20566; Commit `b2009f6` |
| GO-OGS-130-2026 | Nachtrag mit Hinweis auf die Folgemeldung (SCHREIBREGELN 0a) | `inhalte/meldungen/2026-10-25-recherche-claude.json` | Titel beschreibt weiter den Ratsbeschluss vom 30.09. |

Eigener Fund: Die neuen Zahlen zur Grundschule Golzheim (TOP 7 des Bauausschusses) führten weder das ChatGPT-Dossier noch das Register. Rechnung der Redaktion, im Text gekennzeichnet: Der Anstieg von 9.177.455,70 Euro (Schätzung 29.09.) auf 12.022.466,97 Euro (23.10.) entspricht genau den neu eingerechneten Baunebenkosten (2.845.011,27 Euro); Kostengruppen 200 bis 600 sind in beiden Fassungen gleich. Aus dem förderfähigen OGS-Teil herausgerechnet: Dachanteil 675.424,18, Aufzug 85.000, Kostengruppe 200 174.297,01 Euro (zusammen 934.721,19 Euro).

Nicht genannt: Namen der Sachbearbeiterinnen der Gemeinde und der Bezirksregierung (Verwaltungspersonal); Ratsmitglieder nur mit Fraktion.

Top-Meldung: keine neue Stufe 1. Golzheim hat großen Nachrichtenwert, ist aber eine Vorlage, kein Ratsbeschluss (SCHREIBREGELN 0a, im Zweifel 2). Stufe 1 bei Beschluss des Gemeinderats am 03.12. oder bei Förderzusage.

## 2. Sofort sichtbar

- Zwei neue Meldungen, direkt veröffentlicht, oben in „Neu in Merzenich“ und in der Nachrichten-Sitemap.
- Solarpark-Ausbau und Foto über den Abgleich (Update der bestehenden Meldung).
- Termine: drei neue live, zwei freigegebene Heimat-Info-Termine jetzt veröffentlicht, Nachträge bei VHS Paris und Gottesdienst 22.11.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| M140/2026 Tiefbauangelegenheiten (Baustelle Valdersweg vor dem Rathaus, Glasfaser-Schäden an Bäumen Eichenstraße und Hauptstraße, Wirtschaftswege Girbelsrath, Förderprogramme Bahnstraße) | gelesen (3 Seiten); Stoff für eine eigene Meldung im Nachmittagslauf. Offen: Verbandsbezeichnung „Wasserverband Eifel-Rur (WZV)“ in der Vorlage nicht eindeutig, vor Übernahme klären; Sachstand vom 23.10., nicht als heutiger Zustand belegt |
| Vorlage 92/2026 (20. FNP-Änderung Golzheim/Eschweiler über Feld, PV-Anlage) | TOP 9 am 12.11.; nicht gelesen, nächster Lauf |
| Bierabend-Rückblick (Heimat-Info) | reine Veranstalterdarstellung ohne neuen Nachrichtenwert (wie ChatGPT) |
| BSC Merzenich, VHS-Billardkurse | Kurs läuft seit 14.09. (bis 30.11.), kein Einzeltermin |
| Schützenbruderschaft Morschenich: Bezirksjungschützenschießen 21.11. (Rölsdorf), Nikolausrunde 05.12., Generalversammlung 08.01.2027 | vereinsintern oder außerhalb der Gemeinde |
| KG Jonge vom Berg, Januar 2027 (15., 16., 17., 20., 24., 28., 31.01.) | im Vereinskalender (ICS) gefunden, Orte und Details fehlen; nach Tag 60, nächste Läufe |

## 4. Quellen dieses Laufs

Pflichtquellen nach ChatGPT-Abdeckung 07:06 bis 07:15 Uhr (Polizei, Kreis, Heimat-Info, Ratsinfo, Feuerwehr bis 144/26, Amtsblatt 11, Bahn): kein neuer Merzenicher Fall. Zusätzlich in diesem Lauf gelesen:

- Ratsinformationssystem: Tagesordnung Bauausschuss 12.11., Vorlagen 138/2026 (mit Anlagen), 130/2026 (mit Anlagen und Beschlussauszug), M139/2026, M140/2026, Gesamtes Sitzungspaket (120 Seiten).
- Heimat-Info-Veranstaltungskalender (09:28 Uhr): sechs Einträge, unverändert gegenüber dem Quellenabruf vom 03.11.
- Breitenrecherche `qa/vereinsseiten.mjs` über 30 Vereinsseiten: neu Terminseite und Seite „Schützenheim“ der St. Lambertus Schützenbruderschaft Morschenich.
- VHS Rur-Eifel: Website im Neuaufbau (Hinweis auf dueren.de, 02.11.–02.12.), vorläufige Programmübersicht als PDF.
- Evangelische Gemeinde zu Düren, Bezirk Merzenich/Niederzier: Gottesdienste 08., 15. und 22.11.
- Landtag NRW, Drucksache 18/20566 (Kleine Anfrage 8430 vom 16.07.2026); eine Antwort der Landesregierung wurde nicht gefunden.

Instagram und Facebook: nicht aus der Sitzung lesbar (Anmeldung), weiter Auftrag an ChatGPT.

## 5. Sport

Keine Pflichtprüfung am Mittwoch; Stand aus dem Dossier 03.11. mittags.

## 6. Kalenderpflege

Liste aus `node qa/redaktion-lauf.mjs --kalender` (38 Termine bis 03.01.2027), Fenster 04.–17.11. an der Quelle nachgeprüft.

**Neu (3):** Volkstrauertag-Gedenken auf dem Friedhof Morschenich 15.11., 11:30 Uhr; Adventsfenster am Bürgewaldzentrum Morschenich 28.11., ab 17 Uhr; VHS-Bildvortrag „Wien“ im Seniorenhaus Marienhof 12.01.2027, 15:30 Uhr (`inhalte/termine/2026-11-04-kalenderpflege-claude.json`).

**Geändert oder freigegeben (3):** Heimat-Info-Automatik vom 02.11. ohne Entwurf, nach der Entscheidung vom 03.11.: Gottesdienst mit Abendmahl 22.11. im Bürgerhaus Niederzier (Heimat-Info und Kalender der Ev. Gemeinde: Totensonntag), Weihnachtsschießen ab 15 Uhr und Weihnachtsfeier 15:30 Uhr der Schützenbruderschaft Morschenich am 06.12. (Heimat-Info und Terminseite; „BWZ“ ist das Bürgewaldzentrum, Ellener Allee). VHS Paris 10.11.: Quelle jetzt die vorläufige Programmübersicht, Nachtrag zum Website-Umbau.

**Abgesagt:** keiner.

**Nachgeprüft ohne Änderung:** Jugendgremium 04.11. 17 Uhr (Bürgerhaus), Rechnungsprüfungsausschuss 05.11. 18 Uhr, Bauausschuss 12.11. 18 Uhr (Ratsinfo); Persönliches Budget 05.11. 10–17 Uhr, KI-Roadshow 06.11. 9–11 Uhr, Eichel-Sammelaktion 06.11. (Kreis Düren); Kostümsitzung KG Golzheim 07.11. 20:11 Uhr; Bücher- und Spielemarkt 08.11. 10–16 Uhr im Bürgerhaus (golzheimaktiv.de); Kindergottesdienst 08.11. 11 Uhr mit Frühstück ab 10:30 Uhr (Heimat-Info und Ev. Gemeinde); Kranzniederlegung und Weckmannschießen 15.11. 9:30 Uhr (Jonge vom Berg); TTC-Jugendvereinsmeisterschaften 13.11. 17:30 Uhr; Martinszug Golzheim 11.11. (Heimat-Info); Pluriversum 17.11. (Heimat-Info-Kalender).

**Nicht erneut gelesen (8):** Rosenkranzgebet 04.11. und Taizé-Gottesdienst 15.11. (Pfarrbrief), Kripo-Info Girbelsrath 05.11., Lesung Jennifer Teege 05.11., Inthronisation Girbelsrath 08.11., Martinszug Merzenich 12.11., Martinsfeier Girbelsrath 13.11. (Amtsblatt 11, keine neue Ausgabe), Erste-Hilfe-Kurs Golzheim 14.11. (Heimat-Info-Beitrag).

**Heimat-Info-Kalender:** sechs Einträge, alle im Kalender; keine neuen.

Offen: Bürgersprechstunde Golzheim (30.10. gegen 13.11.), Orte der Jonge-vom-Berg-Termine (Website zeigt teils „Site Unavailable“).

## 7. QA, Commit, Abgleich

`node deploy/kette.mjs` je Schritt zweimal, `--check` unverändert; `node qa/pruefung.mjs` 0 Fehler; `node qa/redaktion-lauf.mjs --datum 2026-11-04 --lauf morgens` 0 Fehler nach diesem Dossier. Commits: `5969b40`, `2c5a224`, `179c885`, `001c7d7`, `b2009f6`, `8374b33`.

Live-Nachweis (09:48 Uhr): Grundschule Golzheim, Energiemanagement und Solarpark (neuer Titel, Themenfoto) HTTP 200, alle drei in der Nachrichten-Sitemap, Golzheim und Energiemanagement oben in „Neu in Merzenich“. Freigegebene Termine 22.11. und 06.12. sowie VHS Paris HTTP 200. Abgleich-Anstoß 09:48 und 09:49 Uhr: `angestossen: false` (Workflow hatte schon angestoßen), nächster Lauf 10:05 Uhr.

Befund: Die drei neuen Termine standen in der Import-Datei, blieben bis 10:15 Uhr aber HTTP 404. Ursache: Der Import datierte Termine auf den Prüftag 10:00 UTC (11:00 Uhr Berlin); im Morgenlauf vor 11 Uhr angelegt, setzt WordPress sie auf „Geplant“. Sie erscheinen um 11:00 Uhr. Korrektur in `deploy/wordpress-import.mjs`: Tagesbeginn 00:00 UTC; bestehende Termine ändert der Abgleich nicht.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| solarpark-a4-merzenich-bauausschuss-12-november-2026-photovoltaik-batteriespeicher | leben.energie.solar | photovoltaik-freiflaeche | Dülmen, Solarpark -- 2014 -- 7060.jpg | CC BY-SA 4.0 | selbst angesehen: Freiflächenmodule auf Ackerland, keine Personen, kein Ortsname; Symbolbild, nicht das Merzenicher Projekt |
| energiemanagement-gemeinde-merzenich-gebaeude-fertig-ende-2026-statt-2029 | rathaus.rat | rathaus-merzenich | Merzenich Rathaus HDR.jpg | CC BY-SA 3.0 | selbst angesehen: Rathaus Merzenich, Thema sind die Gebäude der Gemeinde |
| grundschule-golzheim-merzenich-umbau-12-millionen-euro-gemeindeanteil-7-5-millionen | leben.schule.anmeldung | klassenzimmer | Klassenraum Grundschule.jpg | CC BY-SA 3.0 | selbst angesehen: leeres Klassenzimmer, keine Personen; Symbolbild für Schulbau, nicht die Golzheimer Schule |

## 8a. Prüfung und Eigenformulierung

`node qa/eigenformulierung.mjs --datum 2026-11-04 --lauf morgens`: 0 Fehler, 0 Hinweise. Energiemanagement 314 Wörter, Golzheim 1.075 Wörter, je 0 % Übereinstimmung mit den Quellen (längste gleiche Wortfolge 0 Wörter); eine zuvor gemeldete Folge von 8 Wörtern aus Vorlage 138/2026 umformuliert. Prüfprotokoll: jede Zahl im Text mit Eintrag; Rechnungen der Redaktion als solche gekennzeichnet; Angaben der Verwaltung und des Architekturbüros mit `selbstauskunft` begründet.

## 9. Offene Punkte für den nächsten Lauf

- Jugendgremium heute 17 Uhr: Ergebnis nachfassen (Niederschrift oder Bericht).
- Meldung aus M140/2026 (Tiefbau: Baustelle Valdersweg, Bahnstraße, Girbelsrath), Verbandsbezeichnung klären.
- Vorlage 92/2026 (FNP Golzheim/Eschweiler über Feld) lesen.
- Wochenvorschau 9.–15.11. (Martinszüge, Volkstrauertag, Bauausschuss).
- Bauausschuss 12.11.: Folgemeldungen Golzheim, Solarpark, Energiemanagement.
- Jonge-vom-Berg-Termine im Januar 2027 mit Orten.
