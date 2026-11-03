# Merzenich Aktuell – Nachmittagslauf (Claude), 3. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.8). Routine „14:43“ in der festen Redaktionssitzung, Start 14:44 Uhr, Bearbeitung 14:44 bis 15:00 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 03.11. morgens](2026-11-03-claude-morgens.md), seither neu [ChatGPT 03.11. mittags](2026-11-03-mittags.md). main vor dem Lauf: `d4f12d4`. Eil-Prüfung 12:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Rathaus-Vorschau Solarpark an der A4 (337 Wörter, `relevanz` 2) |
| Aktualisierte oder korrigierte Bestandsmeldungen | 1 | Martinszüge 2026: Nachtrag mit den Zeiten des Veranstalters für Golzheim |
| Neue Termine (live / Entwurf) | 0 / 0 | |
| Termine 03.–17.11. an der Quelle nachgeprüft | 22 von 24 | Bücher- und Spielemarkt (gemeinde-merzenich.de HTTP 428) und VHS (HTTP 404) nicht erneut lesbar |
| Umkreis-Einträge | 0 | |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | Kurzmeldung mit offizieller Quelle (4 Quellen, alle RIS) / ja, 8 Aussagen | |
| Sportstand | unverändert | ChatGPT-Fußballprüfung 03.11. mittags ohne neuen Stand |
| Bilder gefunden / geprüft / integriert | 1 / 1 / 0 | Rathaus Merzenich; Commons-Suche nach Solarpark-Fotos ohne Antwort |
| Kette / QA / redaktion-lauf | zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `redaktion-lauf` 0 Fehler | |
| Mindesttakt 03.11. | 3 Meldungen | Jugendgremium 09:16, Feuerwehr 144/26 11:13 (Automatik), Solarpark 14:49 |
| Dauer des Laufs | etwa 16 Minuten | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-SOLARPARK-A4-E3 | „Solarpark an der A4 in Merzenich: Bauausschuss berät über 36 Hektar Photovoltaik mit Batteriespeicher“ (Kurzmeldung, `relevanz` 2). Vorschlag der Verwaltung, kein Beschluss; Verfahrensschritte und Beteiligung der Öffentlichkeit erst nach dem Aufstellungsbeschluss benannt | `inhalte/meldungen/2026-11-03-recherche-claude-nachmittags.json` → `/rathaus/solarpark-a4-merzenich-bauausschuss-12-november-2026-photovoltaik-batteriespeicher/` | Beschlussvorlage 144/2026 vollständig gelesen (3 Seiten), Tagesordnung 12.11., Mitteilungsvorlage M142/2026, Beschlussvorlage 142/2026 |
| MA-MARTINSZUEGE-2026 | Nachtrag in „Martinszüge 2026“: Golzheim laut Förderverein 17.30 Uhr Wortgottesdienst, 18 Uhr Zugstart; Amtsblatt-Angabe 17.45 Uhr bleibt genannt | `inhalte/meldungen/2026-10-16-amtsblatt-11.json` → `/leben/martinszuege-2026-alle-ortsteile-gemeinde-merzenich/` | Heimat-Info-Veranstaltung 791f374e (14:52 Uhr) |
| Kalender | Pluriversum 17.11. an Heimat-Info (Gemeinde) bestätigt | `inhalte/termine/2026-10-16-amtsblatt-11.json` | Abschnitt 6 |

Fund: Die Vorlagen 144/2026 (29.10.) und 142/2026 (28.10.) sowie M142/2026 (02.11.) stehen seit dem Export vom 03.11. im Ratsinformationssystem; weder ChatGPT noch das Register führten sie. Quellenausnahmen: keine (Kurzmeldung mit offizieller Quelle nach SCHREIBREGELN.md Abschnitt 0).

Top-Meldung: keine neue Stufe 1. Ein Aufstellungsvorschlag ist noch kein Ratsbeschluss mit spürbaren Folgen (SCHREIBREGELN 0a, im Zweifel 2); Stufe 1 wäre der Beschluss des Gemeinderats am 03.12. Die Folgemeldung zum Verkehrstod (02.11.) bleibt Aufmacher, solange sie frisch ist.

## 2. Sofort sichtbar

- Neue Meldung Solarpark (direkt veröffentlicht), oben in „Neu in Merzenich“ und in der Nachrichten-Sitemap.
- Nachtrag Martinszüge mit dem nächsten Abgleich (Text in WordPress nicht von Hand geändert).
- Termine: nur Quelle und Prüfstand Pluriversum.

## 3. Bewusst nicht umgesetzt

- **Bau-Turbo-Leitlinien (142/2026) und Projekt „Poolplatz“ (M142/2026)** nur als Absatz in der Solarpark-Meldung: Die Leitlinien bestehen aus Kreis-Vorlage, Gemeinde-Fassung und Synopse (Anlagen nicht als Text abrufbar), „Poolplatz“ hat nur zwei Sätze. Eigene Meldung nach der Sitzung am 12.11. mit den vorgestellten Plänen.
- **Anlagen zu 144/2026** (Kurzbegründung, Übersicht „E3_PV_BESS-Hambach“) stehen in der Tagesordnung, ließen sich aber nicht direkt abrufen; Leistung in Megawatt und Lage der Teilflächen deshalb nicht genannt.
- **20. Änderung des Flächennutzungsplans Golzheim/Eschweiler über Feld (92/2026)** mit Umweltbericht zu einer PV-Anlage Düren: in derselben Sitzung, Prüfung im nächsten Lauf (Bestand und Vorgeschichte klären).

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT 03.11. mittags | Dossier, Abschnitt KORREKTUREN zuerst | keine Korrektur, keine Themenkarte, keine Terminänderung |
| Polizei Düren | Dienststellenliste nr/8, 14:45 Uhr, HTTP 200 (Eil-Prüfung 12:41 Uhr) | jüngste Merzenich-Meldung 02.11., 15:08 Uhr (L 264), bereits Folgemeldung; neu nur Nideggen, Düren, Linnich |
| Kreis Düren Presse | Index, 14:45 Uhr, HTTP 200 | jüngste Mitteilung 28.10., nichts Neues |
| Heimat-Info | Gemeindeliste, 14:45 Uhr, HTTP 200 | jüngster Beitrag Feuerwehr 02.11., 19:18 Uhr (144/26, live) |
| Heimat-Info-Kalender | 14:45 Uhr, HTTP 200, 6 Einträge | alle bei uns (Abschnitt 6) |
| Ratsinformationssystem | `/vorlagen`, `termine.ics` 14:45 Uhr; Tagesordnung Bauausschuss 14:47 Uhr; Vorlagen 144/2026, 142/2026, M142/2026 14:46 Uhr; alle HTTP 200 | **neu:** 144/2026, 142/2026, M142/2026 (Grundlage der Meldung); Sitzungen 04.11., 05.11., 12.11. unverändert |
| Feuerwehr | Einsatzliste 14:45 Uhr (bis 140/26), Heimat-Info-Kanal (bis 144/26), beide HTTP 200 | nichts Neues |
| Amtsblatt, Gemeinde-Presse | Index HTTP 428 aus dieser Sitzung (14:45 Uhr) | Lücke; ChatGPT las den Index um 12:06 Uhr: weiterhin 11/2026 |
| Live-Archiv, Startseite | 14:54 Uhr | Solarpark live, „Neu in Merzenich“ mit 5 Einträgen |

## 5. Sport

Unverändert. ChatGPT-Regelprüfung 03.11. mittags: Tabellen und örtliche Ansetzungen ohne Änderung gegenüber morgens.

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender` (39 Termine bis 02.01.2027). Die Termine 03.–16.11. wurden im Morgenlauf heute an ihren Quellen nachgeprüft (Prüfstand 2026-11-03); neu im 14-Tage-Fenster ist der 17.11.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Pluriversum 17.11., 18:15 Uhr, Aula der Gesamtschule | pluriversum-gesamtschule-merzenich-2026-11-17 | bestätigt | [Heimat-Info, Gemeinde Merzenich, 21.10.](https://www.heimat-info.de/veranstaltungen/671697c5-6e42-41cd-8f80-11b2ee1894ea), 14:52 Uhr | 2026-11-03 |
| Martinszug Golzheim 11.11. | martinszug-golzheim-2026-11-11 | bestätigt | [Heimat-Info, Förderverein KGS](https://www.heimat-info.de/veranstaltungen/791f374e-0170-4d3b-ad22-74b3151101b0), 14:52 Uhr: 17:30 Wortgottesdienst, 18:00 Umzug | 2026-11-03 |
| Gottesdienst mit Abendmahl 15.11., 10 Uhr | ev-gottesdienst-gedenken-verstorbene-2026-11-15 | bestätigt | [Heimat-Info, Ev. Gemeindezentrum](https://www.heimat-info.de/veranstaltungen/3f95f059-3d0b-4e2d-b96c-bb6d11d55ac2), 14:52 Uhr | 2026-11-03 |
| Bauausschuss 12.11., 18 Uhr | bauausschuss-sitzung-2026-11-12 | bestätigt | Tagesordnung, 14:47 Uhr | 2026-11-03 |
| Jugendgremium 4.11., RPA 5.11. | jugendgremium-sitzung-2026-11-04, rechnungspruefungsausschuss-2026-11-05 | bestätigt | `termine.ics`, 14:45 Uhr | 2026-11-03 |
| Bücher- und Spielemarkt 8.11. | buecher-spielemarkt-ig-golzheim-2026 | nicht erneut geprüft | Amtsblatt 10/2026, gemeinde-merzenich.de HTTP 428 | 2026-10-30 |
| VHS-Vortrag Paris 10.11. | vhs-bildvortrag-paris-marienhof-2026-11-10 | nicht geprüft | HTTP 404 (ChatGPT 12:06 Uhr ebenso) | 2026-10-05 |

**Heimat-Info-Veranstaltungskalender** (Schritt 2a): 6 Einträge (Abruf 14:45 Uhr), alle bei uns: Kindergottesdienst 8.11., Martinszug Golzheim 11.11., Gottesdienst 15.11., Pluriversum 17.11., Gottesdienst 22.11. (Automatik-Entwurf vom 02.11.), Weihnachtsfeier Morschenich 6.12. (Automatik-Entwurf vom 02.11.). Neue Automatik-Termine gehen seit 03.11. direkt online; die beiden Entwürfe vom 02.11. bleiben Entwurf.

Offene Hinweise „nachpruefen“: 2 (Bücher- und Spielemarkt, VHS).

## 7. QA, Commit, Abgleich

Commits `1df9f20` (Meldung), `bd7317d` (Kalender, Nachtrag, CLAUDE-LAUF 1a) und der Dokumentationscommit. `qa/pruefung.mjs` 0 Fehler, `qa/redaktion-lauf.mjs` 0 Fehler, Kette zweimal und `--check` unverändert. Rebase-Konflikt nur in `docs/SHA256SUMS.txt` (generiert), Kette neu.

Abgleich: Anstoß 14:50 Uhr `angestossen: true`. Live 14:54 Uhr HTTP 200: [Solarpark-Meldung](https://merzenich-aktuell.de/rathaus/solarpark-a4-merzenich-bauausschuss-12-november-2026-photovoltaik-batteriespeicher/), `datePublished` 14:49 Uhr, an erster Stelle der [Nachrichten-Sitemap](https://merzenich-aktuell.de/news-sitemap.xml) und in „Neu in Merzenich“. `/umgebung/` antwortet jetzt mit HTTP 200.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| solarpark-a4-merzenich-bauausschuss-12-november-2026-photovoltaik-batteriespeicher | rathaus.rat | rathaus-merzenich | Merzenich Rathaus HDR.jpg | CC BY-SA 3.0 | selbst angesehen: Rathaus Merzenich mit Schriftzug „Rathaus“, keine Personen, keine Kennzeichen; der Ausschuss tagt im Ratssaal dieses Hauses. Ein Solarpark-Foto wäre treffender; die Commons-Suche (vier Anfragen, 14:50 Uhr) lieferte keine Antwort |

Neu importierte Fotos: keine. Auftrag: Bildklasse für Solarenergie mit freiem Foto einer Freiflächen-Photovoltaikanlage (Pool `themen`).

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll (offiziell / doppelt / Selbstauskunft) | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| solarpark-a4-merzenich-bauausschuss-12-november-2026-photovoltaik-batteriespeicher | 337 | 4 (Vorlage 144/2026, Tagesordnung, M142/2026, 142/2026) | 8 / 0 / 0 | 0 % (`qa/eigenformulierung.mjs` 0 Fehler) |

## 9. Offene Punkte für den nächsten Lauf

1. Nach der Sitzung am 04.11.: Update der Jugendgremium-Meldung mit der Priorisierung, sobald im RIS veröffentlicht.
2. Bauausschuss 12.11.: Ergebnis zu Solarpark, „Poolplatz“ und Bau-Turbo als Folgemeldung; vorher Vorlage 92/2026 (20. FNP-Änderung Golzheim/Eschweiler über Feld, PV-Anlage Düren) gegen Bestand prüfen.
3. Bücher- und Spielemarkt 8.11. erneut an einer Quelle prüfen (ChatGPT erreicht gemeinde-merzenich.de).
4. Bildpool Solarenergie (Commons), dazu Unfall, Rettung, Tischtennis.
5. Wochenvorschau 9. bis 15.11. am Montag mit den Martinszügen in Golzheim, Merzenich und Girbelsrath.
