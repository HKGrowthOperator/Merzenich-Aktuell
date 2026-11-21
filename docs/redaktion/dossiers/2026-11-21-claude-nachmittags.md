# Merzenich Aktuell – Nachmittagslauf (Claude), 21. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung ab 14:44 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 21.11. morgens](2026-11-21-claude-morgens.md), seither neu [ChatGPT 21.11. mittags](2026-11-21-mittags.md) (Commit `a6d814da`) und der Quellenabruf der Actions (Commit `bf5411b3`, 13:33 Uhr). main vor dem Lauf: `bf5411b3`. Eil-Prüfungen 10:41 und 12:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 0 | **Nullfund:** keine belegbare neue Nachricht mit Ortsbezug (Abschnitt 3 und 4) |
| Korrigierte Bestandsmeldungen | 0 | ChatGPT mittags ohne neuen Korrekturauftrag; Wochenvorschau und Unwetter-Abschluss dort gegen die Originale bestätigt |
| Neue / geänderte Termine | 0 / 1 | Weihnachtsfenster der Gemeinde 15.12. ergänzt |
| Termine an der Quelle bestätigt | 19 | alle Termine 21.11. bis 05.12., Stand 2026-11-21 bleibt |
| Bilder gefunden / geprüft / integriert | 0 / 0 / 0 | keine neue Meldung |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs --datum 2026-11-21 --lauf nachmittags` 0 Fehler | |

Mindesttakt 21.11.: 1 Meldung (Wochenvorschau im Morgenlauf). Eine zweite belegbare Nachricht gab es an diesem Samstag nicht; nichts wurde gestreckt oder aus alten Quellen neu aufgelegt.

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| Termin Weihnachtsfenster 15.12. (Kalenderpflege 60 Tage) | Beschreibung und Absätze ergänzt: Glühwein, alkoholfreier Punsch für Kinder, Waffeln, Gebäck, weihnachtliche Gedichte, Bläsergruppe der Gemeinde mit dem Bürgermeister; Ort „vor dem Rathaus“; offen, ob Christkind und Nikolaus kommen. Quelle jetzt der Gemeindebeitrag in Heimat-Info vom 16.11. (Kulturkalender im Quellennamen genannt) | `inhalte/termine/2026-11-12-kulturkalender-claude.json` → `/termine/weihnachtsfenster-lebendiger-adventskalender-rathaus-merzenich-2026-12-15/` | Gemeindebeitrag `8819ff5f` in den Rohdaten des Quellenabrufs (13:29 Uhr) und um 14:46 Uhr direkt gelesen. Namen der übrigen Musikerinnen und Musiker nicht übernommen. Die Pressemitteilungsseite der Gemeinde-Website zum selben Thema lieferte mir um 14:45 Uhr weiter HTTP 428 (ChatGPT um 12:06 Uhr HTTP 200); Inhalt laut ChatGPT gleich. Commit `bd7a865f` |

## 2. Sofort sichtbar

Terminergänzung ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| DWD-Warnung vor leichtem Frost (0 bis minus 3 Grad) für den Kreis Düren, 21.11. 22 Uhr bis 22.11. 9 Uhr, Stufe 2 | einfache Wetterwarnung ohne Unwetterlage, im November üblich; keine eigene Meldung. Wird im Morgenlauf 22.11. nur geprüft, falls sich die Lage verschärft |
| Gemeinde-Adventsfenster als eigene Meldung | Termin am 15.12., Gemeindebeitrag vom 16.11., bereits im Advent-Überblick vom 15.11.; keine neue Entwicklung, daher nur Terminergänzung (Abschnitt 1) |
| Kreis Düren, Familienkalender (20.11.) | kreisweit, kein Ortsbezug (SCHREIBREGELN 0b) |
| Ergebnisse HFA 19.11. | Sitzungsseite um 14:45 Uhr weiter ohne Niederschrift oder Beschlusstexte; Vorlagenliste unverändert (höchste Nummer 151/2026); Ratssitzung 3.12. weiter ohne Tagesordnung |
| Malwettbewerb-Bildbeschreibung (MA-KORR-MALWETTBEWERB-BILDBESCHREIBUNG-1411) | Werkzeugpunkt (Alt-Text bestehender Poolanhänge im Abgleich), Entscheidung Luis Kummer offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 21.11. mittags, zuerst KORREKTUREN | kein neuer Korrekturauftrag; keine neue Themenkarte |
| Polizei Düren | Dienststellenliste nr/8 14:45 Uhr | jüngste Meldung 20.11. 16:01 Uhr (Vorlesetag), kein Ortsbezug |
| Feuerwehr | Website (bis 147/26) und Heimat-Info-Profil (bis 148/26) 14:45 Uhr | kein neuer Einsatz |
| Deutscher Wetterdienst | `warnings.json` 14:45 Uhr | Frostwarnung Stufe 2 für die Nacht (Abschnitt 3) |
| Kreis Düren | Presse-Index 14:45 Uhr HTTP 200 | nur Familienkalender 20.11. |
| Ratsinformationssystem | HFA-Seite, Ratssitzung 3.12., Vorlagenliste 14:45 Uhr; `termine.ics` 14:46 Uhr | nichts Neues |
| Heimat-Info | Gemeindeseite 14:45 Uhr; Rohdaten des Quellenabrufs (13:32 Uhr, alle Beiträge der Profile bis 20.11. 10:00 Uhr); Veranstaltungskalender und zehn Detailseiten 14:46 Uhr | kein neuer Beitrag seit dem 20.11.; Kalender unverändert |
| Gemeinde Merzenich | Rohdaten Pressemitteilungsliste (13:27 Uhr); Adventsfenster-Seite 14:45 Uhr HTTP 428; Amtsblätter 11 und 12, Kulturkalender 16 unter `/bindata/` 14:46 Uhr | Liste unverändert; PDFs bytegleich |
| Schulen | Rohdaten KGS Merzenich und KGS Golzheim (13:32 Uhr) | keine neue Nachricht |

## 5. Sport

Samstag, kein Pflichttag. Ergebnis Girbelsrath vom Freitag und die Spiele des Wochenendes im Montagslauf; die Heimspiele von FC Golzheim und SC Merzenich am 29.11. stehen in der Wochenvorschau.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 14:46 Uhr gelesen: dieselben zehn IDs wie am Morgen, alle zehn Detailseiten im Veranstaltungsteil textgleich. `termine.ics` bis auf den technischen Zeitstempel gleich. Amtsblätter 11 und 12 und Kulturkalender 16 bytegleich; Schützen Morschenich, Ausstellungs- und Wunschbaum-Beitrag textgleich.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Weihnachtsfenster der Gemeinde 15.12. | weihnachtsfenster-lebendiger-adventskalender-rathaus-merzenich-2026-12-15 | ergänzt (Abschnitt 1) | Gemeindebeitrag Heimat-Info 16.11. | 2026-11-21 |
| alle 19 Termine vom 21.11. bis 03.12. | je Slug | erneut bestätigt, Stand vom Morgen bleibt | jeweilige Quelle | 2026-11-21 |

**Neu:** keiner. **Geändert:** einer (Ergänzung, keine Zeit- oder Ortsänderung). **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `bd7a865f` (Termin Weihnachtsfenster), Dokumentation in diesem Commit. Abgleich 14:48 Uhr `angestossen: true`.

Live-Nachweis 14:49 Uhr: `/termine/weihnachtsfenster-lebendiger-adventskalender-rathaus-merzenich-2026-12-15/` HTTP 200 mit der ergänzten Beschreibung („Bläsergruppe“).

## 8. Bildprüfung

Keine neue oder bildrelevant geänderte Meldung in diesem Lauf. Bilder der Wochenvorschau und der Unwetter-Meldung siehe [Dossier 21.11. morgens](2026-11-21-claude-morgens.md), Abschnitt 8; ChatGPT hat das Bild der Wochenvorschau um 12:08 Uhr ebenfalls gesichtet.

## 9. Offene Punkte für den nächsten Lauf

- Mindesttakt 22.11. (Sonntag): zwei Meldungen, wenn belegbare Nachrichten vorliegen.
- HFA 19.11.: Ergebnisse als Folgemeldungen, sobald Niederschrift oder Beschlusstexte vorliegen; Gemeinderat 3.12.: Tagesordnung abwarten.
- Montag: Ergebnis Girbelsrath und Wochenende.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
