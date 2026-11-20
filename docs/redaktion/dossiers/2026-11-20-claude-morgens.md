# Merzenich Aktuell – Morgenlauf (Claude), 20. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung ab 09:14 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 19.11. nachmittags](2026-11-19-claude-nachmittags.md), seither neu [ChatGPT 20.11. morgens](2026-11-20-morgens.md) (Commit `9462dd38`, 07:13 Uhr). main vor dem Lauf: `9462dd38`. Eil-Prüfungen 16:41, 18:41, 20:41 (19.11.) und 06:42 Uhr ohne Fund; Feuerwehreinsatz 148/26 hatte die Automatik um 23:47 Uhr angelegt.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 3 | Unwetterwarnung Dauerregen (Stufe 1), Garten-AG KGS Merzenich (aus dem Entwurf vom 19.11.), Bauausschuss-Ergebnisse (Standard, 874 Wörter) |
| Korrigierte Bestandsmeldungen | 0 | ChatGPT meldet keinen neuen Textfehler; Malwettbewerb-Bildtext bleibt Werkzeugpunkt |
| Neue / geänderte Termine | 0 / 0 | |
| Termine an der Quelle bestätigt | 19 | alle Termine 21.11. bis 03.12. (Stand 2026-11-20) |
| Bilder gefunden / geprüft / integriert | 2 / 3 / 2 | zwei neue Commons-Fotos, ein Bestandsfoto geprüft (Abschnitt 8) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs --datum 2026-11-20 --lauf morgens` 0 Fehler (Ortsbezug geprüft); `qa/eigenformulierung.mjs --datum 2026-11-20 --lauf morgens` 0 Fehler, 1 Hinweis (Abschnitt 8a) | |

Mindesttakt 20.11.: 3 Meldungen im Morgenlauf.

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-DAUERREGEN-20112026 (ChatGPT morgens, P1) | Kurzmeldung „Unwetterwarnung für Merzenich: DWD warnt bis Freitag 22 Uhr vor ergiebigem Dauerregen“, Blaulicht, Relevanz 1, 274 Wörter | `inhalte/meldungen/2026-11-20-recherche-claude-morgens.json` → `/blaulicht/unwetterwarnung-merzenich-dauerregen-kreis-dueren-20-november-2026-dwd/` | DWD-Warndaten (`warnings.json`, Datenstand 09:12:06 Uhr) um 09:15 Uhr selbst gelesen: Warnzelle Kreis Düren, Stufe 4, gültig bis 22 Uhr, Mengen wie im Text; Kreis-Pressemitteilung vom 19.11. (Warnung ab 11:44 Uhr, älterer Mengenstand) 09:15 Uhr; Feuerwehr Website und Heimat-Info-Profil 09:16 und 09:17 Uhr (kein Regeneinsatz). Keine Schäden behauptet. Commit `d1493fb2` |
| MA-KOMPOST-KGS-2026 | Kurzmeldung „Grundschule in Merzenich: Garten-AG baut Komposter nach afrikanischem Vorbild“, Leben, Relevanz 3, 297 Wörter, aus dem Entwurf vom 19.11. | `…/2026-11-20-recherche-claude-morgens.json` → `/leben/grundschule-kgs-merzenich-garten-ag-baut-keyhole-komposter-schulgarten-2026/` | Gemeindebeitrag in Heimat-Info (offizielles Gemeindeprofil, 19.11. 09:56 Uhr) am 20.11. um 09:17 Uhr erneut gelesen, unverändert; Pressemitteilungsliste der Gemeinde (Actions-Rohdaten). Kurzmeldung mit einer amtlichen Quelle nach SCHREIBREGELN Abschnitt 0; die Einschätzung T2 im ChatGPT-Dossier (beide Wege stammen von der Gemeinde) ist im Text berücksichtigt: Die Meldung gibt nur wieder, was die Gemeinde berichtet, und nennt sie als Absender. Commit `d1493fb2`; Entwurf in `docs/redaktion/entwuerfe/` als veröffentlicht vermerkt |
| MA-BAUAUSSCHUSS-12112026-VORSCHAU (Updatekarte ChatGPT morgens) mit GO-OGS-138-2026, MA-SOLARPARK-A4-E3, MA-PV-GOLZHEIM-E1-FNP20, MA-BAUTURBO-142-2026, MA-EMS-SACHSTAND-2026 | Standardmeldung „Bauausschuss Merzenich: Ja zum Umbau der Grundschule Golzheim, 36 Wohnungen am Poolplatz vorgestellt“, Rathaus, Ortsteil Golzheim, Relevanz 2, 874 Wörter | `…/2026-11-20-recherche-claude-morgens.json` → `/rathaus/bauausschuss-merzenich-ergebnisse-grundschule-golzheim-poolplatz-solarpark-a4-bau-turbo-2026/` | Öffentliche Niederschrift (31 MB) und sechs Beschlusstexte (138, M142, 144, 92, 142, M139) um 09:20 Uhr geladen und gelesen. Offene Widersprüche im Text benannt statt aufgelöst: Solarpark A4 rund 40 Hektar laut Vortrag gegen 44 bzw. 36 Hektar in den Unterlagen; Poolplatz Baubeginn „März/April“ ohne Jahr; Bau-Turbo „Änderung auf 6 Wohneinheiten“, ohne dass feststeht, welche Schwelle der Leitlinien gemeint ist (Paket des Bürgewald-Ausschusses nennt weiter 5). Ratsmitglieder nur mit Partei. Ratssitzung 3.12. 18 Uhr an der Sitzungsseite um 09:27 Uhr bestätigt. Commit `96e72812` |
| Kalender | 19 Termine bestätigt | 14 Dateien unter `inhalte/termine/` | Abschnitt 6; Commit `5d7a61bb` |

Ortsbezug (SCHREIBREGELN 0b): Unwetterwarnung gilt für den Kreis Düren und damit für alle fünf Ortsteile, Feuerwehr Merzenich als örtlicher Stand (Ziffer 3, Warnung, die hier gilt); Kompost an der KGS Merzenich (Ziffer 1 und 2); Bauausschuss der Gemeinde (Ziffer 1 und 2). Feld `ortsbezug` in allen drei Meldungen gesetzt.

Top-Meldung: Unwetterwarnung (Relevanz 1), gültig bis heute 22 Uhr.

## 2. Sofort sichtbar

Alle drei Meldungen ohne Freigabe, kein `zurFreigabe`. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Polizei Düren 20.11. 09:30 Uhr „Nach Unfall geflüchtet – und von der Polizei gefasst“ | Unfall auf einem Supermarktparkplatz am Heerweg in Düren; Merzenich kommt nur als Wohnort der unverletzten Autofahrerin vor. Ereignis nicht in der Gemeinde, keine Merzenicher Einrichtung beteiligt, kein Service für Merzenicher: kein Ortsbezug nach 0b, im Zweifel nicht veröffentlicht |
| Polizei 20.11. 09:30 Uhr (Bahnhof Düren, Einbruch Soller) und 19.11. (drei Meldungen) | Volltexte ohne Merzenicher Ort |
| Feuerwehr 148/26 | bereits als Automatik-Artikel online (19.11. 23:47 Uhr), von ChatGPT gegen das Original geprüft |
| Ergebnisse HFA 19.11. | Sitzungsseite um 06:58 (ChatGPT) und in diesem Lauf ohne öffentliche Niederschrift oder Beschlusstexte; keine Ergebnisse aus Vorlagen abgeleitet |
| Freundschaftsspiel RW Berrendorf – FC Golzheim (20.11. 19:30 Uhr, „Absetzung“) | Testspiel auswärts, nicht im Terminbestand; ChatGPT T0 |
| Malwettbewerb-Bildbeschreibung (MA-KORR-MALWETTBEWERB-BILDBESCHREIBUNG-1411) | Werkzeugpunkt (Alt-Text bestehender Poolanhänge im Abgleich), Entscheidung Luis Kummer offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 20.11. morgens, zuerst KORREKTUREN | kein neuer Textfehler; zwei Karten (Dauerregen, Bauausschuss-Update) umgesetzt; Kompost-Bildkandidat übernommen und selbst angesehen |
| Deutscher Wetterdienst | `warnings.json` 09:15 Uhr (Datenstand 09:12:06 Uhr) | Meldung |
| Kreis Düren | Pressemitteilung Unwetterwarnung 19.11. und Presse-Index 09:15 Uhr | Hintergrund zur Meldung; sonst nichts mit Ortsbezug |
| Feuerwehr | Website 09:16 Uhr (bis 147/26), Heimat-Info-Profil 09:17 Uhr (bis 148/26) | kein Regeneinsatz, 148 bereits online |
| Heimat-Info | Gemeindeseite 09:17 Uhr; Veranstaltungskalender und zehn Detailseiten 09:24 Uhr | Kompost unverändert; Kalender unverändert |
| Polizei Düren | Dienststellenliste nr/8 09:17 und 09:31 Uhr, drei Meldungen von 09:30 Uhr im Volltext | kein Ortsbezug (Abschnitt 3) |
| Ratsinformationssystem | Niederschrift und Beschlusstexte Bauausschuss 09:20 Uhr; `termine.ics` 09:24 Uhr; Sitzungsseite Rat 3.12. 09:27 Uhr | Meldung; Rat 18 Uhr, noch ohne Tagesordnung |
| Gemeinde Merzenich | Amtsblatt-PDFs 11 und 12, Kulturkalender 16 unter `/bindata/` 09:25 Uhr | bytegleich; Indexseiten mit Bot-Prüfung nicht umgangen |
| FUSSBALL.DE | Vereinsseite FC Rhenania Girbelsrath 09:28 Uhr | Spiel heute 19:30 Uhr ohne Absetzung (Abschnitt 5) |
| Wikimedia Commons | Bildimport `--nur=themen` 09:16 Uhr | zwei neue Poolfotos (Abschnitt 8) |

## 5. Sport

Freitagsprüfung: FUSSBALL.DE führt um 09:28 Uhr „Freitag, 20.11.2026 – 19:30 Uhr, Herren, Kreisliga B, Viktoria Arnoldsweiler II : FC Rhenania Girbelsrath“ ohne Absetzungsvermerk. Ergebnis im Montagslauf. Wochenende: SC Merzenich und FC Golzheim erst wieder am So. 29.11., Morschenich am So. 06.12. (Dossier 16.11.).

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 09:24 Uhr gelesen: dieselben zehn IDs wie am Vortag, alle zehn Detailseiten im Veranstaltungsteil textgleich. `termine.ics` 09:24 Uhr: nur die vergangene HFA-Sitzung entfallen, Bürgewald-Ausschuss 26.11. 18 Uhr, Rat und Jugendgremium 3.12. 18 Uhr unverändert; Sitzungsseite Rat um 09:27 Uhr mit 18 Uhr. Amtsblätter 11 und 12, Kulturkalender 16 und Schützen Morschenich bytegleich mit dem Vortag; Ausstellungs- und Wunschbaum-Beitrag inhaltlich unverändert.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| alle 19 Termine vom 21.11. bis 03.12. (Wunschbaum 21.11. bis Gemeinderat und Jugendgremium 3.12.) | je Slug | bestätigt | jeweilige Quelle | 2026-11-20 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner. Termine ab 04.12. bis 60 Tage: Bestand aus den Läufen 12.11. bis 19.11., nächste Prüfung, wenn sie ins 14-Tage-Fenster rücken.

## 7. QA, Commit, Abgleich

Commits: `d1493fb2` (Unwetter, Kompost, zwei Poolfotos), `96e72812` (Bauausschuss), `5d7a61bb` (Kalender), Dokumentation in diesem Commit. Der stündliche Abgleich hat die Meldungen übernommen; Anstoß um 09:32 Uhr `angestossen: false` (nächster Lauf 10:05 Uhr), alle drei waren da schon live.

Live-Nachweis:
- 09:23 Uhr HTTP 200: `/blaulicht/unwetterwarnung-merzenich-dauerregen-kreis-dueren-20-november-2026-dwd/` (og:image `themen-26-299418b5e9.webp`) und `/leben/grundschule-kgs-merzenich-garten-ag-baut-keyhole-komposter-schulgarten-2026/` (og:image `themen-27-a5e20eb2ff`).
- 09:26 Uhr HTTP 200: `/rathaus/bauausschuss-merzenich-ergebnisse-grundschule-golzheim-poolplatz-solarpark-a4-bau-turbo-2026/` (og:image `aktuell-05-85b961cd5e`, Rathaus).
- 09:28 Uhr: alle drei in `news-sitemap.xml` an den ersten drei Stellen und auf der Startseite unter „Neu in Merzenich“ (09:22 Uhr Bauausschuss, 09:18 Uhr Unwetterwarnung und Garten-AG).

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| unwetterwarnung-merzenich-dauerregen-kreis-dueren-20-november-2026-dwd | wetter.unwetter.regen | starkregen-unwetter | Unwetter-mit-Starkregen.jpg | CC BY 3.0 | selbst angesehen nach dem Import (09:16 Uhr) vor dem Commit um 09:19 Uhr und erneut um 09:31 Uhr: dunkle Gewitterwolke, darunter Regenschleier über einer Hügellandschaft, keine Personen, keine Schrift; Bildunterschrift „Symbolbild Regenfront, kein Foto aus Merzenich“ |
| grundschule-kgs-merzenich-garten-ag-baut-keyhole-komposter-schulgarten-2026 | leben.schule.garten | kompost-garten | Komposthaufen in einem Garten an der Straße Donnerbecke.jpg | CC BY-SA 4.0 | selbst angesehen vor dem Commit um 09:19 Uhr und erneut um 09:31 Uhr: zwei Drahtgitter-Komposter mit Laub in einem Garten, keine Personen, keine Schilder, keine Kennzeichen; deutsches Motiv, Auslandsfilter nicht berührt; Bildunterschrift „Symbolbild Komposter im Garten, kein Foto der Schule“ |
| bauausschuss-merzenich-ergebnisse-grundschule-golzheim-poolplatz-solarpark-a4-bau-turbo-2026 | rathaus.rat | rathaus-merzenich | Merzenich Rathaus HDR.jpg | CC BY-SA 3.0 | Live-Teilbild um 09:29 Uhr angesehen: Rathaus Merzenich mit Schriftzug „RATHAUS“, Sitz des Ausschusses |

Die von ChatGPT angegebenen Commons-Originale (HTTP 403 dort) hat der Bildimport über die Commons-Schnittstelle geladen; der Auslandsfilter wurde nicht umgangen. Neue Bildklassen `wetter.unwetter.regen` und `leben.schule.garten` in `deploy/bildklassen.json`, Freigabe in `deploy/editorial-photo-review.json`, Motive in `deploy/bildmotive.json`.

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Unwetterwarnung Dauerregen | 274 | 5 | 4 Einträge: DWD (zwei), Kreis Düren, Feuerwehr Website und Heimat-Info | 0 Wörter |
| Garten-AG KGS Merzenich | 297 | 2 | 3 Einträge: Gemeindebeitrag Heimat-Info (zwei), Pressemitteilungsliste der Gemeinde | 0 Wörter |
| Bauausschuss-Ergebnisse | 874 | 10 | 7 Einträge: Beschlusstexte 138, M142, 144, 92, 142, M139 und M128 | 0 Wörter bei den Beschlusstexten; die 31-MB-Niederschrift lief in `qa/eigenformulierung.mjs` in eine Zeitüberschreitung (Hinweis), daher lokal gegen den Volltext geprüft: keine gemeinsame Wortfolge ab acht Wörtern |

## 9. Offene Punkte für den nächsten Lauf

- Unwetterwarnung: im Nachmittagslauf DWD-Stand und Feuerwehr erneut prüfen; Aktualisierung oder Entwarnung mit „Aktualisiert am …“, Einsätze nur mit Quelle.
- HFA 19.11.: Ergebnisse als Folgemeldungen, sobald Niederschrift oder Beschlusstexte vorliegen.
- Gemeinderat 3.12.: Tagesordnung abwarten (Grundschule Golzheim, Bau-Turbo, Solarparks, Haushaltsentwurf 2027).
- Bau-Turbo: welche Schwelle die „Änderung auf 6 Wohneinheiten“ betrifft, in der Ratsvorlage nachlesen.
- Girbelsrath in Arnoldsweiler (heute 19:30 Uhr): Ergebnis im Montagslauf.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
