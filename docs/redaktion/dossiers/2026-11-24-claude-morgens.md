# Merzenich Aktuell – Morgenlauf (Claude), 24. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung ab 09:14 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 23.11. nachmittags](2026-11-23-claude-nachmittags.md), seither neu [ChatGPT 24.11. morgens](2026-11-24-morgens.md) (Commit `6dfdd86d`). main vor dem Lauf: `6dfdd86d`.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 0 | **Nullfund:** keine belegbare neue Nachricht mit Ortsbezug (Abschnitt 3 und 4) |
| Korrigierte Bestandsmeldungen | 1 | Ausstellung „Bürgewald“: Nachtrag aus der zweiten Einladung der Gemeinde |
| Neue / geänderte Termine | 0 / 2 | Frühstückstreff 25.11. (Ortsfeld, Korrekturauftrag ChatGPT); Ausstellung 27.11. (Beschreibung, Quelle) |
| Termine an der Quelle bestätigt | 18 | alle Termine 24.11. bis 08.12., Stand jetzt 2026-11-24 |
| Bilder gefunden / geprüft / integriert | 0 / 0 / 0 | Korrekturen betreffen nur Text |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/eigenformulierung.mjs --datum 2026-11-17 --lauf nachmittags` (ergänztes Paket) 0 Fehler, 0 Hinweise; `qa/redaktion-lauf.mjs --datum 2026-11-24 --lauf morgens` 0 Fehler | |

Mindesttakt: 22.11., 23.11. und bisher 24.11. je 0 Meldungen. Auch heute Morgen gab es in keiner Pflichtquelle eine belegbare neue Nachricht mit Bezug zu Merzenich. Nichts wurde gestreckt oder aus alten Quellen neu aufgelegt.

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-KORR-FRUEHSTUECK-ORT-2411 (ChatGPT 24.11. morgens) | Ortsfeld alt: „Ev. Gemeindehaus Merzenich, Severin-Böhr-Str. 15“. Neu: „Ort im Kalendereintrag nicht angegeben“. Zweiter Absatz: „zuletzt geprüft am 24. November … Wer sicher gehen will, fragt beim Gemeindezentrum nach.“ Der Fließtext hatte den fehlenden Ort schon richtig beschrieben. Das Ortsfeld hatte die Durchsicht einer anderen Sitzung (Commit `6ea1ebff`, 21.11., „Termine außerhalb ohne Merzenicher Anschrift“) gefüllt. Dieser eine Wert ist zurückgenommen, der Rest des Commits bleibt. | `inhalte/termine/2026-11-12-recherche-claude-morgens.json` → `/termine/neu-fruehstuecks-und-spieletreff-fuer-senior-innen-merzenich-2026-11-25/` | Heimat-Info-Eintrag `0b6866fc` vor der Korrektur und um 09:19 Uhr erneut gelesen: Der Text nennt keinen Ort (ChatGPT fand auch im eingebetteten Veranstaltungsobjekt keinen). Gegenprobe: Die Einträge für den 2.12. (`6b9b9f9d`) und 9.12. (`c66dc70a`) nennen „Ev. Gemeindehaus Sverin-Böhr-Str. 15“; dort bleibt der Ort. Commit `0cb25d54` |
| MA-AUSSTELLUNG-BUERGEWALD-2711 (ChatGPT 24.11. morgens, Terminhinweis) | Artikel: Vermerk „Aktualisiert am 24. November: …“. In der zweiten Einladung vom 23.11. schreibt die Gemeinde, dass im Ratssaal ausgewählte Arbeiten im Original zu sehen sind und das ganze Projekt digital vorgestellt wird. Motive laut Einladung: Graffiti, leer stehende Räume, Natur. Laufzeit und Eintritt nennt die Einladung weiter nicht. Termin: letzter Absatz entsprechend, Quelle jetzt der Beitrag vom 23.11. | `inhalte/meldungen/2026-11-17-recherche-claude-nachmittags.json` → `/leben/ausstellung-buergewald-morschenich-alt-kunst-fotografie-film-ratssaal-merzenich-27-november-2026/`; `inhalte/termine/2026-11-17-recherche-claude-nachmittags.json` | Gemeindebeitrag Heimat-Info `0626b794` (23.11.) vor der Korrektur gelesen und mit der ersten Einladung `72cdf5c6` verglichen: Datum, Uhrzeit und Ort gleich. Neuer Prüfeintrag und weitere Quelle. 383 Wörter. Commit `0cb25d54` |
| Kalenderpflege 14 Tage | 18 Termine vom 25.11. bis 06.12. an der Quelle bestätigt, Stand 2026-11-24; inhaltlich keine Änderung | 12 Termindateien | Abschnitt 6. Commit `27b7b73b` |

## 2. Sofort sichtbar

Korrekturen und Kalenderpflege ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Wunschbaum für Seniorenheime, Zwischenstand des Fanclubs (MA-WUNSCHBAUM-2111) | Zahlen und Frist stehen nur im Beitrag des Fanclubs. Weder die Gemeinde noch eine zweite Quelle bestätigen sie, ChatGPT stuft sie als T2 ein. Keine Meldung und keine Frist im Kalender, bis eine zweite Quelle vorliegt |
| Ergebnis Viktoria Arnoldsweiler II – FC Rhenania Girbelsrath 3:0 (FuPa, 20.11.) | FUSSBALL.DE führt um 09:18 Uhr weiter 14:34 Tore und keinen lesbaren Spielbericht; FuPa allein reicht nicht (SCHREIBREGELN 3a) |
| DWD-Warnungen vor Glätte und Nebel bis 10 Uhr, Frost bis 12 Uhr (Stand 09:10 Uhr, Stufe 2) | keine Unwetterlage; der Bestandsartikel ist datiert und braucht keinen weiteren Nachtrag |
| Ergebnisse HFA 19.11. | Sitzungsseite um 09:18 Uhr weiter ohne Niederschrift oder Beschlusstexte; Vorlagenliste unverändert (höchste Nummer 151/2026); Ratssitzung 3.12. ohne Tagesordnung |
| Bürgewald-Ausschuss 26.11. als neue Meldung | Tagesordnung um 09:21 Uhr gelesen, gegenüber dem Vorbericht vom 18.11. ohne neue Vorlage; Vorschau steht bereits (MA-BUERGEWALD-AUSSCHUSS-2611) |
| Kreis Düren 23.11. (Generationentag, Polizeiklasse am Berufskolleg) | kreisweite Angebote ohne Merzenicher Beteiligung (0b) |
| Nikolaus beim Adventmarkt am 28.11. (Amtsblatt 12: „etwa gegen 15.15 Uhr“, Kulturkalender: „bei Einbruch der Dunkelheit“) | bekannter Widerspruch, in der Wochenvorschau offen benannt; keine Quelle hat ihn aufgelöst |
| Malwettbewerb-Bildbeschreibung (MA-KORR-MALWETTBEWERB-BILDBESCHREIBUNG-1411) | Werkzeugpunkt (Alt-Text bestehender Poolanhänge im Abgleich), Entscheidung Luis Kummer offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 24.11. morgens, zuerst KORREKTUREN | ein neuer Korrekturauftrag (Frühstücksort), ein Terminhinweis (Ausstellung), beides umgesetzt |
| Polizei Düren | Dienststellenliste nr/8 09:18 Uhr | keine neue Meldung seit dem 23.11. |
| Feuerwehr | Website (bis 147/26) und Heimat-Info-Profil (bis 149/26) 09:18 Uhr | kein neuer Einsatz |
| Kreis Düren | Presse-Index 09:18 Uhr | nur die beiden Mitteilungen vom 23.11., ohne Ortsbezug |
| Deutscher Wetterdienst | `warnings.json` 09:18 Uhr (Datenstand 09:10 Uhr) | Glätte und Nebel, Frost, je Stufe 2 |
| Ratsinformationssystem | HFA-Seite, Ratssitzung 3.12., Vorlagenliste 09:18 Uhr; Tagesordnung Bürgewald-Ausschuss 09:21 Uhr; `termine.ics` 09:19 Uhr | nichts Neues |
| Heimat-Info | Gemeindeseite 09:18 Uhr; Ausstellungsbeitrag `0626b794` und Frühstückseintrag `0b6866fc` vor den Korrekturen; Veranstaltungskalender und elf Detailseiten 09:19 Uhr | jüngste Beiträge weiter Fanclub (Wunschbaum) und Ausstellung vom 23.11.; Kalender unverändert |
| Gemeinde Merzenich | Amtsblätter 11 und 12, Kulturkalender 16 unter `/bindata/` 09:19 Uhr; Amtsblatt 13 09:24 Uhr | PDFs bytegleich; Amtsblatt 13 noch nicht veröffentlicht (HTTP 404) |
| FUSSBALL.DE | Mannschaftsseite Girbelsrath 09:18 Uhr | Ergebnis weiter nicht lesbar |

## 5. Sport

Girbelsrath (20.11. in Arnoldsweiler): FUSSBALL.DE um 09:18 Uhr weiter mit 14:34 Toren und ohne lesbaren Spielbericht, FuPa mit 3:0 und 14:36. Der Unterschied bleibt offen, keine Meldung. Nächste Spiele: Girbelsrath Do 26.11. um 19.30 Uhr in Schlich; FC Golzheim und SC 1919 Merzenich So 29.11. zu Hause; SV Morschenich So 29.11. Freundschaftsspiel in Weilerswist (alle in der Wochenvorschau).

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 09:19 Uhr gelesen: dieselben zehn IDs wie am 23.11. nachmittags. Alle zehn Detailseiten und die Weihnachtsfeier Morschenich (`1e0ecb73`) sind im Veranstaltungsteil textgleich. `termine.ics` ist bis auf den technischen Zeitstempel gleich. Amtsblätter 11 und 12 und Kulturkalender 16 sind bytegleich. Schützen Morschenich, Ausstellungs- und Adventsfenster-Beitrag sind textgleich. Die Tagesordnung des Bürgewald-Ausschusses (26.11., 18 Uhr, Ratssaal) ist unverändert. Den Friedenslicht-Termin hat die Termin-Automatik nicht doppelt angelegt; im Kalender steht er einmal.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Frühstückstreff 25.11. | neu-fruehstuecks-und-spieletreff-fuer-senior-innen-merzenich-2026-11-25 | Ortsfeld korrigiert (Abschnitt 1) | Heimat-Info `0b6866fc` | 2026-11-24 |
| Ausstellung „Bürgewald“ 27.11. | ausstellung-buergewald-ratssaal-merzenich-2026-11-27 | Beschreibung ergänzt (Abschnitt 1) | Gemeindebeitrag `0626b794` | 2026-11-24 |
| übrige 16 Termine vom 25.11. bis 06.12. | je Slug | bestätigt | jeweilige Quelle | 2026-11-24 |

**Neu:** keiner. **Geändert:** zwei (ohne Zeit- oder Ortsverlegung). **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `0cb25d54` (Korrekturen), `27b7b73b` (Kalenderpflege), Dokumentation in diesem Commit. Abgleich 09:18 Uhr `angestossen: true`.

Live-Nachweis: wird nach dem Push dieses Commits ergänzt.

## 8. Bildprüfung

Keine neue oder bildrelevant geänderte Meldung.

## 9. Offene Punkte für den nächsten Lauf

- Girbelsrath 0:3 in Arnoldsweiler: Kurzmeldung, sobald FUSSBALL.DE das Ergebnis lesbar führt.
- Wunschbaum: Meldung nur mit zweiter Quelle für Zahlen und Frist.
- HFA 19.11. und Ratssitzung 3.12.: weiter beobachten; Bürgewald-Ausschuss am 26.11. (Ergebnisse ab 27.11.); Ausstellungseröffnung 27.11.
- Amtsblatt 13 prüfen, sobald es erscheint.
- Entscheidungspunkte für Luis Kummer: dwd.de in die Liste `OFFIZIELL`; Werkzeugpunkte Alt-Text bestehender Poolanhänge, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2. Neu: Ortsfelder aus einer allgemeinen Durchsicht sollten nur gesetzt werden, wenn die Terminquelle den Ort für genau diesen Tag nennt.
