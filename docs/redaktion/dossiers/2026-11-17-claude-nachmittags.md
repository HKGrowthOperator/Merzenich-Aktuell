# Merzenich Aktuell – Nachmittagslauf (Claude), 17. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung 14:44 bis 15:10 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 17.11. morgens](2026-11-17-claude-morgens.md), seither neu [ChatGPT 17.11. mittags](2026-11-17-mittags.md). main vor dem Lauf: `b9140ae2`. Eil-Prüfungen 10:41 und 12:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 2 | Folgemeldung Jugendgremium (260 Wörter im Fließtext, Rathaus, `relevanz` 3); Ausstellung Bürgewald (242 Wörter, Leben, `relevanz` 3) |
| Korrigierte / ergänzte Bestandsmeldungen | 0 neu / 3 | Nachtrag Jugendgremium-Vorschau 03.11. (Verweis auf Folgemeldung); Hinweise „Korrigiert am …“ in Sportmeldung 16.11. und Übernachtungssteuer-Meldung (Pflicht nach CLAUDE-LAUF Abschnitt 3, bei den Korrekturen am 16. und 17.11. versäumt) |
| Neue / geänderte Termine | 1 / 1 | neu: Ausstellung Bürgewald 27.11.; ergänzt: Jugendgremium 03.12. (Ende 19 bzw. 19.30 Uhr) |
| Termine an der Quelle bestätigt | 22 + 13 | 22 im Morgenlauf; nachmittags Heimat-Info (10 Detailseiten) und Ratsinfo `termine.ics` erneut unverändert |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 2/2 | Jugendgremium: 10 Quellen, 4 Aussagen, alle offiziell (Ratsinformationssystem); Ausstellung: 6 Quellen, 4 Aussagen (Gemeindeprofil als Selbstauskunft, Ratsinfo, Kreis) |
| Bilder gefunden / geprüft / integriert | 1 / 2 / 2 | neu: Commons „Unterstraße Morschenich 01 Juli 2021.jpg“; Rathaus Merzenich (Pool) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise (Ausstellung nach Umformulierung; vorher 23 Wörter gleich mit dem Gemeindebeitrag) | |
| Mindesttakt 17.11. | 2 Meldungen | Jugendgremium 14:46, Ausstellung 14:51 Uhr (morgens keine) |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-JUGENDGREMIUM-STATUS (ChatGPT mittags, T2) | Folgemeldung „Jugendgremium Merzenich wünscht sich Calisthenics-Park und Hundewiese und will die Bäume an der Bahnstraße retten“ (Kurzmeldung): Anliegen vom Ortsfest, einstimmige Empfehlung (Prüfung durch Rat/Verwaltung, Bericht, Umsetzung priorisierter Maßnahmen), Gemeinderat 03.12.; ÖPNV-Hinweise der Kreisverwaltung, Klimaschutz-App-Aktion vor den Osterferien; Landtagsbesuch mit Niederzier (Datum in der Niederschrift vor der Sitzung, als unklar benannt); nächste Sitzung 03.12. online (Ende 19 bzw. 19.30 Uhr benannt) | `inhalte/meldungen/2026-11-17-recherche-claude-nachmittags.json` → `/rathaus/jugendgremium-merzenich-calisthenics-park-hundewiese-baeume-bahnstrasse-empfehlung-rat-2026/` | Niederschrift-Auszüge TOP 3, 4, 5, 6, 7, 8, 9 und Beschluss 135/2026 (exportiert 17.11.), Vorlage 135/2026, Sitzungsseite und ICS, gelesen 14:44 Uhr; Commit `e9817914` |
| Bestand Jugendgremium | Vorschau vom 03.11.: Nachtrag vom 17. November mit Verweis; Termin 03.12.: Ende laut Niederschrift 19 Uhr, laut Kalender 19.30 Uhr, Geschäftsordnung als Thema | `2026-11-03-recherche-claude-morgens.json`, `inhalte/termine/2026-11-09-recherche-claude-nachmittags.json` | Commit `144022bb` |
| MA-AUSSTELLUNG-BUERGEWALD-2711 (neu, eigener Fund) | „Kunst über Bürgewald: Ausstellung im Merzenicher Ratssaal zeigt den verlassenen Ort mit anderen Augen“ (Kurzmeldung): drei Künstlerinnen und Künstler, Fotografie, Film, bildende Kunst über Leerstand, Natur, Verfall und Protestkultur; Fr. 27.11. ab 16 Uhr, Ratssaal; Dauer, Ende und Eintritt nicht angegeben; Einordnung Kreisjahrbuch 2026, Bürgewald-Ausschuss 26.11. (ohne Tagesordnung), HFA 19.11. mit Wirtschaftsplan der Neuland Hambach GmbH (Vorlage 151/2026) | `inhalte/meldungen/2026-11-17-recherche-claude-nachmittags.json` → `/leben/ausstellung-buergewald-morschenich-alt-kunst-fotografie-film-ratssaal-merzenich-27-november-2026/`; Termin `inhalte/termine/2026-11-17-recherche-claude-nachmittags.json` | Beitrag der Gemeinde in Heimat-Info vom 17.11. 13:05 Uhr, gelesen 14:50 Uhr; Commit `25c92bd4` |
| Korrekturhinweise | Sportmeldung 16.11.: „Korrigiert am 16. November: Der SC stand schon vor dem Spiel auf Platz vier, nicht auf Platz drei.“ Übernachtungssteuer: „Korrigiert am 17. November: Die Genehmigung durch das Land ist die Auffassung der Verwaltung, keine feststehende Rechtslage.“ (beide Kurzmeldungen danach 348 bzw. 350 Wörter) | `2026-11-16-recherche-claude-morgens.json`, `2026-11-16-recherche-claude-nachmittags.json` | Commit `208867ae` |

Quellenausnahmen: ChatGPT führt das Jugendgremium als T2 (dieselbe kommunale Quelle für alle Dokumente). Umgesetzt, weil die Niederschrift-Auszüge und der Beschlusstext offizielle Quellen sind (SCHREIBREGELN Abschnitt 0); der Text gibt nur deren Inhalt wieder und benennt die Datumsfehler, statt sie zu korrigieren. Die Ausstellung stützt sich auf das offizielle Gemeindeprofil in Heimat-Info (vom Prüfwerkzeug nicht als offizieller Host erkannt, daher Selbstauskunft im Prüfprotokoll); Namen der Künstlerinnen und Künstler bewusst nicht übernommen.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldungen, Nachträge, Korrekturhinweise und Termine ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| MA-KOMPOST-KGS-2026 (ChatGPT, T2) | Detailseite der Gemeinde liefert HTTP 428 mit Bot-Prüfung (14:49 Uhr), nicht umgangen; ohne gelesenen Text keine Meldung |
| Polizei 17.11. 09:00 Uhr (B56) | Düren, kein Merzenich-Bezug |
| Kreis „Tipp fürs Wochenende: Gemeinsam kreativ werden“ (17.11.) | kein Merzenich-Bezug im Text |
| Neuland Hambach (151/2026) als eigene Meldung | nur Einordnung in der Ausstellungs-Meldung; Zahlen in nicht-öffentlichen Anlagen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 17.11. mittags, zuerst KORREKTUREN | keine neue Korrektur; Korrekturen Sport und Übernachtungssteuer live bestätigt; Karten Jugendgremium (umgesetzt) und Kompostprojekt (nicht lesbar) |
| Ratsinformationssystem | Sitzung Jugendgremium 04.11. mit allen Auszügen, Vorlage 135/2026, 14:44 Uhr; `termine.ics` 14:49 Uhr | siehe Abschnitt 1; ICS unverändert |
| Gemeinde Merzenich | Pressemitteilung Kompostprojekt, 14:49 Uhr | HTTP 428, Bot-Prüfung |
| Heimat-Info | Gemeindeseite 14:49 Uhr; Ausstellungsbeitrag 14:50 Uhr; Veranstaltungskalender und zehn Detailseiten 14:49 Uhr | Ausstellung neu; Kalender unverändert |
| Polizei Düren | Dienststellenliste nr/8, 14:49 Uhr | nur 17.11. 09:00 Uhr (Düren) |
| Feuerwehr | Website und Heimat-Info-Profil, 14:49 Uhr | weiter bis 147/26 |
| Kreis Düren | Presse-Index 14:49 Uhr | Wochenend-Tipp ohne Merzenich-Bezug |
| Wikimedia Commons | Suche „Morschenich“ (Dateien), Metadaten und Vorschauen, 14:53 Uhr | Foto für die Ausstellungs-Meldung, Abschnitt 8 |

## 5. Sport

Keine Änderung seit dem 16.11.; nächstes Spiel Girbelsrath Fr. 20.11., 19.30 Uhr.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender um 14:49 Uhr erneut gelesen (dieselben 10 IDs, alle Detailseiten unverändert gegenüber dem Morgen), Ratsinfo-`termine.ics` unverändert. Alle Termine bis 01.12. im Morgenlauf an ihrer Quelle bestätigt ([Dossier morgens](2026-11-17-claude-morgens.md), Abschnitt 6).

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Ausstellung Bürgewald, 27.11., 16 Uhr, Ratssaal | ausstellung-buergewald-ratssaal-merzenich-2026-11-27 | **neu** | Gemeinde, Heimat-Info 17.11. | 2026-11-17 |
| Jugendgremium 03.12., 18 Uhr, online | jugendgremium-sitzung-2026-12-03 | **geändert** (Hinweis Endzeit 19 bzw. 19.30 Uhr, Geschäftsordnung) | Niederschrift TOP 6; `termine.ics` | 2026-11-17 |

**Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `e9817914` (Jugendgremium), `144022bb` (Nachtrag, Termin), `25c92bd4` (Ausstellung, Termin, Foto), `208867ae` (Korrekturhinweise), je Rebase-Konflikt nur in `docs/SHA256SUMS.txt` (Stand von main, Kette neu); Dokumentation folgt. Abgleich 14:47 Uhr `angestossen: true`, danach planmäßig 15:05 Uhr; wp-cron HTTP 200.

Live-Nachweis: Jugendgremium-Meldung 14:58 Uhr HTTP 200. Ausstellungs-Meldung um 14:58 Uhr noch 404, nach dem Abgleich seit 15:05 Uhr HTTP 200, in der Nachrichten-Sitemap, in „Neu in Merzenich“ (3 Verweise), mit dem neuen Foto (themen-25, Credit Stefan Oberreuter); Termin Ausstellung 27.11. HTTP 200. Bildbeschreibung im Malwettbewerb-Artikel live weiter „Polizeiwache in Nordrhein-Westfalen“: Werkzeugpunkt, Entscheidung Luis Kummer offen.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| jugendgremium-merzenich-calisthenics-park-hundewiese-baeume-bahnstrasse-empfehlung-rat-2026 | rathaus.rat | rathaus-merzenich | Merzenich Rathaus.JPG | Public domain | am 11.11. selbst angesehen: Rathaus Merzenich hinter Bäumen, keine Personen, keine Kennzeichen; das Gremium richtet seine Empfehlung an Rat und Verwaltung; als Symbolbild gekennzeichnet |
| ausstellung-buergewald-morschenich-alt-kunst-fotografie-film-ratssaal-merzenich-27-november-2026 | leben.buergewald (neu) | morschenich-alt-leerstand (neu) | Unterstraße Morschenich 01 Juli 2021.jpg (Stefan Oberreuter, 4000 × 3000 px) | CC BY-SA 4.0 | am 17.11. selbst angesehen (Vorschau und importierte Datei): leere Dorfstraße mit leerstehenden Backsteinhäusern im damaligen Morschenich-Alt, Juli 2021, keine Personen, Transporter in der Ferne ohne lesbares Kennzeichen; zeigt den Leerstand, den die Ausstellung zum Thema hat. Die zunächst zugewiesene Ortsansicht zeigte das neue Morschenich im Bau (2015) und traf das Thema nicht. Aufnahme in Pool `themen`, `editorial-photo-review.json` (freigegeben), `bildmotive.json`, neue Klasse in `bildklassen.json`; Bildunterschrift übernimmt die Commons-Beschreibung („Leerstand bedingt durch den Tagebau Hambach der RWE“) |

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Jugendgremium | 260 | 10 | 4 Einträge, alle mit offizieller Quelle; einer zusätzlich eigenes Archiv | 0 Wörter |
| Ausstellung Bürgewald | 242 | 6 | 4 Einträge: Gemeindeprofil (Selbstauskunft), Ratsinfo, Kreis mit eigenem Archiv, Vorlage 151/2026 | 0 Wörter nach Umformulierung |

## 9. Offene Punkte für den nächsten Lauf

- HFA 19.11.: Empfehlungen nach der Sitzung als Folgemeldungen.
- Kompostprojekt: lesbar machen (Actions-Rohdaten, Heimat-Info) und dann prüfen.
- Bürgewald-Ausschuss 26.11. und Rat 03.12.: Tagesordnungen abwarten.
- Sport: Girbelsrath Fr. 20.11.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
