# Merzenich Aktuell – Morgenlauf (Claude), 23. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung ab 09:14 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 22.11. nachmittags](2026-11-22-claude-nachmittags.md), seither neu [ChatGPT 23.11. morgens](2026-11-23-morgens.md) (Commit `57df4771`). main vor dem Lauf: `57df4771`. Eil-Prüfungen 16:41, 18:40, 20:41 (22.11.) und 06:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 0 | **Nullfund:** keine belegbare neue Nachricht mit Ortsbezug; Girbelsrath-Ergebnis wartet auf eine zweite Quelle (Abschnitt 3 und 5) |
| Korrigierte Bestandsmeldungen | 1 | Unwetter-Meldung, Vorspann (ChatGPT MA-KORR-WETTER-DEK-2311) |
| Neue / geänderte Termine | 0 / 0 | neuer Heimat-Info-Termin 13.12. der Termin-Automatik überlassen |
| Termine an der Quelle bestätigt | 18 | alle Termine 23.11. bis 07.12. (Stand 2026-11-23); die Commit-Nachricht `a47858aa` nennt versehentlich 19 |
| Bilder gefunden / geprüft / integriert | 0 / 0 / 0 | Korrektur betrifft nur Text |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs --datum 2026-11-23 --lauf morgens` 0 Fehler; `qa/eigenformulierung.mjs --datum 2026-11-20 --lauf morgens` 0 Fehler, 1 Hinweis wie bisher | |

Mindesttakt 23.11.: bis hierher keine Meldung; Girbelsrath-Ergebnis und HFA-Ergebnisse sind Kandidaten für den Nachmittag.

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-KORR-WETTER-DEK-2311 (ChatGPT morgens) | Vorspann alt: „Die amtliche Unwetterwarnung für den Kreis Düren galt bis Freitag, 22 Uhr, die anschließende Warnung vor Glätte und Frost bis Samstag, 9 Uhr. Seither führt der Wetterdienst keine Warnung mehr.“ Neu: „Die amtliche Unwetterwarnung vor Dauerregen für den Kreis Düren galt bis Freitag, 20. November, 22 Uhr, die anschließende Warnung vor Glätte und Frost bis Samstag, 21. November, 9 Uhr.“ Dazu „Aktualisiert am 23. November: …“ mit den neuen Warnungen (Glätte bis 10 Uhr, leichter Frost zwischen minus 1 und minus 4 Grad bis 11 Uhr, beide Stufe 2, Stand Montag 9.10 Uhr) und dem Hinweis, dass der alte Vorspann so nicht mehr galt. Frühere Nachträge erhalten | `inhalte/meldungen/2026-11-20-recherche-claude-morgens.json` → `/blaulicht/unwetterwarnung-merzenich-dauerregen-kreis-dueren-20-november-2026-dwd/` | `warnings.json` (Datenstand 09:10:03 Uhr) um 09:15 Uhr und DWD-Warntabelle der Gemeinden, Abschnitt „Gemeinde Merzenich“, um 09:17 Uhr gelesen; beide Wege gleich. Die von ChatGPT um 07:05 Uhr gesehene Nebelwarnung (bis 9 Uhr) war um 09:10 Uhr nicht mehr eingetragen und steht deshalb nicht im Text. Commit `b4b2baba` |
| Kalender | 18 Termine bestätigt | 13 Dateien unter `inhalte/termine/` | Abschnitt 6; Commit `a47858aa` |

Hinweis Prüfwerkzeug: `qa/redaktion-lauf.mjs` führt dwd.de nicht in der Liste `OFFIZIELL`, obwohl der Deutsche Wetterdienst eine Bundesbehörde ist (SCHREIBREGELN 3a nennt „Bund“). Der Prüfeintrag trägt deshalb zusätzlich die Gemeindetabelle des DWD; die Liste habe ich nicht eigenmächtig erweitert (Entscheidungspunkt in Abschnitt 9).

## 2. Sofort sichtbar

Korrektur ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Ergebnis Viktoria Arnoldsweiler II – FC Rhenania Girbelsrath (Nachholspiel 5. Spieltag, 20.11.) | Der FuPa-Spielbericht nennt 3:0 für Arnoldsweiler (gelesen 09:20 Uhr), die FuPa-Tabelle der Kreisliga B2 führt Girbelsrath danach mit 9 Spielen, 3 Punkten und 14:36 Toren auf Platz 13. FUSSBALL.DE zeigt um 09:21 Uhr noch 14:34 Tore und keinen Spielbericht. Ein Ergebnis nur aus FuPa reicht nach SCHREIBREGELN 3a nicht; Meldung, sobald FUSSBALL.DE das Ergebnis lesbar führt |
| Friedenslicht-Aussendung der Pfadfinder Merzenich (DPSG St. Laurentius), 13.12., 18 Uhr, Alte Kirche und Pfarrkirche St. Laurentius | neuer Eintrag im Heimat-Info-Kalender (`042c28a0`, veröffentlicht 22.11. 15:43 Uhr); außerhalb der 14 Tage. Die Termin-Automatik übernimmt Heimat-Info-Termine selbst, eine Handanlage wäre eine Dublette. Im nächsten Lauf nachprüfen, ob der Termin angelegt ist |
| DWD-Warnungen vor Glätte und Frost als eigene Meldung | Stufe 2, keine Unwetterlage; nur im Bestandsartikel nachgetragen |
| Polizei, Feuerwehr, Kreis | keine neue Meldung seit dem 22.11. 08:47 Uhr (Polizei) bzw. 149/26 (Feuerwehr); Kreis nur Familienkalender 20.11. |
| Ergebnisse HFA 19.11. | Sitzungsseite um 09:15 Uhr weiter ohne Niederschrift oder Beschlusstexte; Vorlagenliste unverändert (höchste Nummer 151/2026); Ratssitzung 3.12. ohne Tagesordnung |
| Malwettbewerb-Bildbeschreibung (MA-KORR-MALWETTBEWERB-BILDBESCHREIBUNG-1411) | Werkzeugpunkt (Alt-Text bestehender Poolanhänge im Abgleich), Entscheidung Luis Kummer offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 23.11. morgens, zuerst KORREKTUREN | eine Korrektur umgesetzt; Korrektur der Wochenvorschau von ChatGPT bestätigt |
| Deutscher Wetterdienst | `warnings.json` 09:15 Uhr; Gemeindetabelle NRW 09:17 Uhr | Korrektur |
| Polizei Düren | Dienststellenliste nr/8 09:15 Uhr | nichts Neues |
| Feuerwehr | Website (bis 147/26) und Heimat-Info-Profil (bis 149/26) 09:15 Uhr | nichts Neues |
| Kreis Düren | Presse-Index 09:15 Uhr HTTP 200 | nichts mit Ortsbezug |
| Ratsinformationssystem | HFA-Seite, Ratssitzung 3.12., Vorlagenliste 09:15 Uhr; `termine.ics` 09:20 Uhr | nichts Neues |
| Heimat-Info | Gemeindeseite 09:15 Uhr; Veranstaltungskalender und elf Detailseiten 09:20 Uhr; Rohdaten des Quellenabrufs (22.11. 21:09 Uhr) | neuer Termin Friedenslicht (Abschnitt 3), sonst nichts Neues |
| Gemeinde Merzenich | Rohdaten Pressemitteilungen (22.11. 21:04 Uhr); Amtsblätter 11 und 12, Kulturkalender 16 unter `/bindata/` 09:20 Uhr | unverändert, bytegleich |
| Sport | FuPa-Tabellen Kreisliga A und B2, Spieltag und Spielbericht B2 (09:19 bis 09:20 Uhr); FUSSBALL.DE Girbelsrath 09:21 Uhr | Abschnitt 5 |

## 5. Sport

Montag, Pflichtprüfung. Kreisliga A (FuPa, 09:19 Uhr): SC Merzenich Platz 4 mit 27 Punkten (12 Spiele, 48:18 Tore), FC Golzheim Platz 5 mit 27 Punkten (12 Spiele, 30:13 Tore), gegenüber dem 16.11. unverändert, am Wochenende kein Spiel. Kreisliga B2: SV Morschenich Platz 1 mit 27 Punkten (11 Spiele), Golzheim II Platz 8, Girbelsrath Platz 13 mit 3 Punkten nach 9 Spielen. Einziges Spiel mit Merzenicher Beteiligung am Wochenende war das Nachholspiel Girbelsrath in Arnoldsweiler am Freitag (Abschnitt 3). Nächste Spiele: Girbelsrath Do 26.11. in Schlich, Golzheim und SC So 29.11. zu Hause, Morschenich So 29.11. Freundschaftsspiel in Weilerswist.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 09:20 Uhr gelesen: zehn Einträge. Neu ist die Friedenslicht-Aussendung am 13.12. (`042c28a0`, Abschnitt 3), der vergangene Gottesdienst vom 22.11. ist entfallen. Die übrigen neun Detailseiten sind im Veranstaltungsteil textgleich mit dem 22.11.; beim Adventsliedersingen hat sich nur der Veröffentlichungszeitpunkt geändert. Die Weihnachtsfeier Morschenich (`1e0ecb73`) ist ebenfalls textgleich. `termine.ics` ist bis auf den technischen Zeitstempel gleich. Amtsblätter 11 und 12 und Kulturkalender 16 sind bytegleich; Schützen Morschenich, Ausstellungs- und Adventsfenster-Beitrag textgleich.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| alle 18 Termine vom 23.11. bis 06.12. | je Slug | bestätigt | jeweilige Quelle | 2026-11-23 |

**Neu:** keiner (Friedenslicht 13.12. über die Automatik). **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `b4b2baba` (Korrektur Unwetter-Vorspann), `a47858aa` (Kalender), Dokumentation in diesem Commit. Abgleich 09:18 Uhr `angestossen: true`.

Live-Nachweis 09:22 Uhr: `/blaulicht/unwetterwarnung-merzenich-dauerregen-kreis-dueren-20-november-2026-dwd/` HTTP 200 mit „Aktualisiert am 23. November“ und „minus 4 Grad“; der alte Satz „Seither führt der Wetterdienst keine Warnung mehr“ ist nicht mehr auf der Seite, die Seitenbeschreibung beginnt mit dem datierten Vorspann.

## 8. Bildprüfung

Keine neue oder bildrelevant geänderte Meldung (Bild der Unwetter-Meldung siehe Dossier 20.11. morgens, Abschnitt 8).

## 9. Offene Punkte für den nächsten Lauf

- Girbelsrath 0:3 in Arnoldsweiler: Kurzmeldung, sobald FUSSBALL.DE das Ergebnis lesbar führt (Spielbericht oder aktualisierte Tabelle); die Torverhältnisse von FuPa (14:36) und FUSSBALL.DE (14:34) passen derzeit nicht zusammen.
- Friedenslicht 13.12.: prüfen, ob die Termin-Automatik den Termin angelegt hat.
- HFA 19.11. und Ratssitzung 3.12.: weiter beobachten.
- Entscheidungspunkt für Luis Kummer: dwd.de in die Liste `OFFIZIELL` von `qa/redaktion-lauf.mjs` aufnehmen (Deutscher Wetterdienst als Bundesbehörde)?
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
