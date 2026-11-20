# Merzenich Aktuell – Nachmittagslauf (Claude), 20. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung ab 14:44 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 20.11. morgens](2026-11-20-claude-morgens.md), seither neu [ChatGPT 20.11. mittags](2026-11-20-mittags.md) (Commit `5ac44995`). main vor dem Lauf: `b5e6f132`. Eil-Prüfungen 10:41 und 12:42 Uhr ohne Eilfund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Gutachterbüro HR² im Gewerbegebiet (Wirtschaft, Kurzmeldung, 295 Wörter) |
| Korrigierte / aktualisierte Bestandsmeldungen | 1 / 1 | Bauausschuss-Folgemeldung (ChatGPT MA-KORR-BAUAUSSCHUSS-RATSTERMIN-2011); Unwetterwarnung (DWD-Stand 14:38 Uhr, neu Glätte und Frost) |
| Neue / geänderte Termine | 0 / 0 | drei DSEE-Online-Termine bewusst nicht übernommen (Abschnitt 3) |
| Termine an der Quelle bestätigt | 19 | alle Termine 21.11. bis 03.12., Stand 2026-11-20 bleibt |
| Bilder gefunden / geprüft / integriert | 1 / 1 / 1 | Commons-Foto Feuchtemessung (Abschnitt 8) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs --datum 2026-11-20 --lauf nachmittags` 0 Fehler (Ortsbezug geprüft); `qa/eigenformulierung.mjs --datum 2026-11-20 --lauf nachmittags` 0 Fehler, 0 Hinweise; `--lauf morgens` (geänderte Pakete) 0 Fehler, 1 Hinweis wie am Morgen | |

Mindesttakt 20.11.: 4 Meldungen (3 morgens, 1 nachmittags).

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-KORR-BAUAUSSCHUSS-RATSTERMIN-2011 (ChatGPT mittags) | Bauausschuss-Folgemeldung präzisiert. Absatz 1 alt: „Endgültig entscheidet in allen Punkten der Gemeinderat; er tagt am Donnerstag, 3. Dezember, um 18 Uhr im Ratssaal.“ Neu: „Über Schule, Solarparks und Bau-Turbo entscheidet am Ende der Gemeinderat; die Vorlagen sehen dafür seine Sitzung am Donnerstag, 3. Dezember, um 18 Uhr vor. Zum Poolplatz und zum Energiemanagement gab es nur Berichte, keinen Beschluss.“ Schlussabsatz alt: „Über die Empfehlungen zu Schule, Solarparks und Bau-Turbo stimmt der Gemeinderat am 3. Dezember ab.“ Neu: „Nach der vorgesehenen Beratungsfolge der vier Vorlagen soll der Gemeinderat am 3. Dezember über die Empfehlungen … beraten; eine Tagesordnung für diese Sitzung lag am 20. November noch nicht vor.“ Dek „bekamen grünes Licht“ → „gab es einstimmige Empfehlungen“. Dazu „Korrigiert am 20. November: …“, Faktenzeile angepasst | `inhalte/meldungen/2026-11-20-recherche-claude-morgens.json` → `/rathaus/bauausschuss-merzenich-ergebnisse-grundschule-golzheim-poolplatz-solarpark-a4-bau-turbo-2026/` | Beschlussvorlagen 138, 92, 142 und 144/2026 um 14:45 Uhr selbst gelesen: jeweils „Vorgesehene Beratungsfolge … Gemeinderat 03.12.2026“ (bei 144 dazwischen Bürgewald-Ausschuss 26.11.). Neuer Prüfeintrag, vier Vorlagen als weitere Quellen. Das Datum ist damit belegt als geplanter Beratungsschritt, nicht als feststehende Abstimmung. 950 Wörter. Commit `017c0a2f` |
| MA-DAUERREGEN-20112026 (Nachfassung) | „Aktualisiert am 20. November: …“: DWD-Stand 14.38 Uhr, Unwetterwarnung unverändert bis 22 Uhr; neu Warnung vor Glätte und leichtem Frost (0 bis minus 3 Grad) für Samstag, 21. November, 0 bis 9 Uhr; Feuerwehr und Polizei bis 14.48 Uhr ohne Regenfolgen in Merzenich. Dek angepasst | `…/2026-11-20-recherche-claude-morgens.json` → `/blaulicht/unwetterwarnung-merzenich-dauerregen-kreis-dueren-20-november-2026-dwd/` | `warnings.json` (Datenstand 14:38:28 Uhr) um 14:48 Uhr gelesen, drei Einträge für Warnzelle Kreis Düren; Feuerwehr Website und Heimat-Info-Profil, Polizei nr/8 14:48 Uhr. 377 Wörter. Commit `2ce71288` |
| MA-HR2-SACHVERSTAENDIGE-20112026 (ChatGPT mittags, dort T2) | Kurzmeldung „Neues Gutachterbüro in Merzenich: Gemeinde meldet 15 Arbeitsplätze im Gewerbegebiet“, Wirtschaft, Relevanz 3, 295 Wörter | `inhalte/meldungen/2026-11-20-recherche-claude-nachmittags.json` → `/wirtschaft/gutachterbuero-hr2-merzenich-gewerbegebiet-schimmel-feuchteschaeden-15-arbeitsplaetze/` | Gemeindebeitrag in Heimat-Info (offizielles Gemeindeprofil, 20.11. 10:00 Uhr) um 14:51 Uhr vollständig gelesen. Eine offizielle Quelle trägt die Kurzmeldung (SCHREIBREGELN 3a); Angaben des Unternehmens sind im Text als solche gekennzeichnet („nach Angaben der Gemeinde“, „heißt es von Seiten des Unternehmens“). Offen benannt: Umfang der 15 Stellen und Betriebsbeginn. Keine Namen der Unternehmer, keine Hausnummer, keine Werbeaussagen (Qualitätsgarantie) übernommen. Einordnung aus dem Archiv: Mybach-Filiale (Oktober, gleiche Reihe). Commits `15445c22` (Meldung), `06bad5a0` (Bild) |
| Kalender | 19 Termine bestätigt, keine Änderung | – | Abschnitt 6 |

**Quellenausnahmen:** Gutachterbüro HR²: einzige Veröffentlichung ist der Gemeindebeitrag in Heimat-Info; die Pressemitteilungsseite der Gemeinde-Website lieferte um 14:51 Uhr HTTP 428 (Bot-Prüfung, nicht umgangen), ChatGPT fand dort um 12:04 Uhr keinen Eintrag. Feld `quellenausnahme` gesetzt.

Ortsbezug (SCHREIBREGELN 0b): Gutachterbüro im Merzenicher Gewerbegebiet, Arbeitsplätze in der Gemeinde (Ziffer 1 und 2); Feld `ortsbezug` gesetzt.

Top-Meldung: weiter die Unwetterwarnung (Relevanz 1, gültig bis 22 Uhr).

## 2. Sofort sichtbar

Neue Meldung, Korrektur und Aktualisierung ohne Freigabe, kein `zurFreigabe`. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Drei Online-Veranstaltungen der DSEE für Ehrenamtliche (23., 24., 30.11.), Hinweis der LAG Rheinisches Revier an Inde und Rur im Gemeindeprofil | online, nicht in Merzenich, keine Merzenicher Beteiligung: Online-Vorträge und Förderhinweise ohne Bezug zur Gemeinde sind nach SCHREIBREGELN 0b ausgeschlossen; auch kein Termin |
| Ergebnisse HFA 19.11. | Sitzungsseite um 14:55 Uhr weiter ohne Niederschrift oder Beschlusstexte; Vorlagenliste unverändert (höchste Nummer 151/2026) |
| Polizei 20.11. (drei Meldungen 09:30 Uhr) | wie im Morgenlauf: Unfall am Heerweg in Düren (Merzenich nur Wohnort), Bahnhof Düren, Soller; seither keine neue Meldung (14:48 Uhr) |
| Malwettbewerb-Bildbeschreibung (MA-KORR-MALWETTBEWERB-BILDBESCHREIBUNG-1411) | Werkzeugpunkt (Alt-Text bestehender Poolanhänge im Abgleich), Entscheidung Luis Kummer offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 20.11. mittags, zuerst KORREKTUREN | eine Präzisierung umgesetzt; Bildtext Malwettbewerb Werkzeugpunkt; Themenkarte HR² umgesetzt; DSEE-Termine nicht übernommen |
| Ratsinformationssystem | Beschlussvorlagen 138, 92, 142, 144/2026 um 14:45 Uhr; HFA-Sitzungsseite und Vorlagenliste 14:55 Uhr; `termine.ics` 14:55 Uhr | Korrektur; sonst nichts Neues |
| Deutscher Wetterdienst | `warnings.json` 14:48 Uhr (Datenstand 14:38 Uhr) | Aktualisierung |
| Feuerwehr | Website (bis 147/26) und Heimat-Info-Profil (bis 148/26) 14:48 Uhr | kein neuer Einsatz |
| Polizei Düren | Dienststellenliste nr/8 14:48 Uhr | keine neue Meldung seit 09:30 Uhr |
| Kreis Düren | Presse-Index 14:48 Uhr | nur Familienkalender, kein Ortsbezug |
| Heimat-Info | Gemeindeseite 14:48 Uhr; Beitrag HR² 14:51 Uhr; Veranstaltungskalender und zehn Detailseiten 14:55 Uhr | Meldung; Kalender unverändert |
| Gemeinde Merzenich | Pressemitteilungen 14:51 Uhr HTTP 428 (nicht umgangen); Amtsblätter 11 und 12, Kulturkalender 16 unter `/bindata/` 14:55 Uhr | PDFs bytegleich |
| Wikimedia Commons | Suche 14:53 Uhr HTTP 429; 14:56 Uhr Suche „Feuchtemessung“ HTTP 200, weitere Suchen wieder 429; Bildimport 14:56 Uhr | ein Foto (Abschnitt 8) |

## 5. Sport

Freitag: Girbelsrath bei Viktoria Arnoldsweiler II heute 19:30 Uhr, laut FUSSBALL.DE (09:28 Uhr) und ChatGPT (12:02 Uhr) ohne Absetzung. Ergebnis im Montagslauf. SC Merzenich und FC Golzheim spielen wieder am 29.11.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 14:55 Uhr gelesen: dieselben zehn IDs wie am Morgen, alle zehn Detailseiten im Veranstaltungsteil textgleich. `termine.ics` bis auf den technischen Zeitstempel gleich (Bürgewald-Ausschuss 26.11. 18 Uhr, Rat und Jugendgremium 3.12. 18 Uhr). Amtsblätter 11 und 12 und Kulturkalender 16 bytegleich; Schützen Morschenich, Ausstellungs- und Wunschbaum-Beitrag textgleich mit dem Morgen.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| alle 19 Termine vom 21.11. bis 03.12. | je Slug | erneut bestätigt, Stand vom Morgen bleibt | jeweilige Quelle | 2026-11-20 |

**Neu:** keiner (DSEE-Online-Termine ohne Ortsbezug, Abschnitt 3). **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `017c0a2f` (Korrektur Bauausschuss), `2ce71288` (Aktualisierung Unwetter), `15445c22` (Meldung HR²), `06bad5a0` (Bild HR²), Dokumentation in diesem Commit.

Abgleich: Anstoß 14:50 Uhr `angestossen: false` (nächster Lauf 15:05 Uhr); der stündliche Abgleich hat alles übernommen. Import-XML auf GitHub raw enthält den neuen Slug.

Live-Nachweis 15:10 Uhr, jeweils HTTP 200:
- `/wirtschaft/gutachterbuero-hr2-merzenich-gewerbegebiet-schimmel-feuchteschaeden-15-arbeitsplaetze/` mit „15 neue Arbeitsplätze“, og:image `themen-28-0db85162b4` (Feuchtemessung, nicht mehr die Ortsansicht); Startseite „Neu in Merzenich“ oben (14:55 Uhr), `news-sitemap.xml` an erster Stelle.
- `/rathaus/bauausschuss-merzenich-ergebnisse-grundschule-golzheim-poolplatz-solarpark-a4-bau-turbo-2026/` mit „die Vorlagen sehen dafür“ und „Korrigiert am 20. November“.
- `/blaulicht/unwetterwarnung-merzenich-dauerregen-kreis-dueren-20-november-2026-dwd/` mit „Aktualisiert am 20. November“ und dem Glättehinweis.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| gutachterbuero-hr2-merzenich-gewerbegebiet-schimmel-feuchteschaeden-15-arbeitsplaetze | wirtschaft.sachverstaendige.feuchte | feuchtemessung-bau | CM Messung Probenbereitung.jpg | CC BY-SA 3.0 de | selbst angesehen 14:57 Uhr: Hände bei der Feuchtemessung nach der Calciumcarbid-Methode an einem Estrichboden (Probe in Schale, Waage), also das, womit ein Feuchte-Sachverständiger arbeitet; keine Gesichter, keine Schrift, keine Kennzeichen. Bildunterschrift „Symbolbild Feuchtemessung am Estrich, kein Foto des Betriebs“. Bewusst kein Schimmelfoto, damit es nicht wie ein Schadensfall in Merzenich wirkt |

Ablauf: Erst fand die Suche kein passendes Motiv im Pool, und Commons drosselte (HTTP 429). Deshalb ging die Meldung um 14:54 Uhr zunächst mit der Ortsansicht (St. Laurentius) online. Um 14:56 Uhr kam eine Suche durch; das Foto wurde importiert (`themen-28-0db85162b4`, Auslandsfilter nicht berührt), angesehen, in `deploy/editorial-photo-review.json` freigegeben und als Motiv `feuchtemessung-bau` mit neuer Klasse `wirtschaft.sachverstaendige.feuchte` eingetragen. Die Bilder der korrigierten und aktualisierten Morgenmeldungen sind unverändert (Dossier morgens, Abschnitt 8).

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Gutachterbüro HR² | 295 | 2 | 5 Einträge: Gemeindebeitrag 20.11. (vier, Selbstauskunft der Gemeinde), Gemeindebeitrag 08.10. (Archiv) | 0 Wörter |
| Bauausschuss-Ergebnisse (Korrektur) | 950 | 14 | 8 Einträge, neu: Beratungsfolge der vier Vorlagen | 0 Wörter; Niederschrift wie am Morgen nur lokal geprüft (Zeitüberschreitung) |
| Unwetterwarnung (Aktualisierung) | 377 | 6 | 5 Einträge, neu: DWD-Stand 14:38 Uhr mit Glätte und Frost | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Unwetterwarnung: nach 22 Uhr abgelaufen; im Morgenlauf 21.11. Folgen (Feuerwehr, Polizei, Kreis) und Glättewarnung prüfen, Meldung ggf. mit „Aktualisiert am …“ abschließen.
- HFA 19.11.: Ergebnisse als Folgemeldungen, sobald Niederschrift oder Beschlusstexte vorliegen.
- Gemeinderat 3.12.: Tagesordnung abwarten; danach Bauausschuss-Folgemeldung um die tatsächlichen Tagesordnungspunkte ergänzen.
- Girbelsrath in Arnoldsweiler (heute 19:30 Uhr): Ergebnis im Montagslauf.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
