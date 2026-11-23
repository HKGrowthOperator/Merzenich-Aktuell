# Merzenich Aktuell – Nachmittagslauf (Claude), 23. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung ab 14:44 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 23.11. morgens](2026-11-23-claude-morgens.md), seither neu [ChatGPT 23.11. mittags](2026-11-23-mittags.md) (Commit `075d3434`). main vor dem Lauf: `075d3434`. Eil-Prüfungen 10:41 und 12:41 Uhr ohne Eilfund (Polizeimeldung von 09:44 Uhr nur mit Wohnort Merzenich, Abschnitt 3).

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 0 | **Nullfund:** keine belegbare neue Nachricht mit Ortsbezug (Abschnitt 3 und 4) |
| Korrigierte Bestandsmeldungen | 0 | ChatGPT mittags ohne neuen Korrekturauftrag; Wetter-Korrektur dort live bestätigt |
| Neue / geänderte Termine | 1 / 0 | Friedenslicht-Aussendung der Pfadfinder, 13.12. |
| Termine an der Quelle bestätigt | 18 | alle Termine 23.11. bis 07.12., Stand 2026-11-23 bleibt |
| Bilder gefunden / geprüft / integriert | 0 / 0 / 0 | keine Meldung |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs --datum 2026-11-23 --lauf nachmittags` 0 Fehler; `deploy/automatik-termine.mjs --trocken` ohne Fehler | |

Mindesttakt: 22.11. und 23.11. je 0 Meldungen. An beiden Tagen gab es in keiner Pflichtquelle eine belegbare neue Nachricht mit Bezug zu Merzenich; nichts wurde gestreckt oder aus alten Quellen neu aufgelegt.

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| Termin Friedenslicht (Kalenderpflege 60 Tage) | neuer Termin „Aussendungsfeier des Friedenslichts aus Bethlehem mit den Pfadfindern“, So 13.12., 18 Uhr, Alte Kirche, danach Pfarrkirche St. Laurentius; Abholung im Aachener Dom am Nachmittag, Kerzen gegen Spende, Abholung auch in den Tagen danach | `inhalte/termine/2026-11-23-recherche-claude-nachmittags.json` → `/termine/friedenslicht-aussendung-pfadfinder-merzenich-2026-12-13/` | Eintrag der Pfadfinder Merzenich (DPSG St. Laurentius) im Heimat-Info-Kalender (`042c28a0`, veröffentlicht 22.11. 15:43 Uhr), um 09:20 und 14:46 Uhr gelesen, textgleich. Die Termin-Automatik hatte ihn nicht übernommen: Beim Quellenabruf am 22.11. um 21:09 Uhr stand er auf der zweiten Kalenderseite, die nicht gelesen wird (Werkzeugpunkt „Seite 2“). Trockenlauf 14:47 Uhr bestätigt: Die Rohdaten enthalten ihn nicht. Die Automatik erkennt ihn beim nächsten Abruf über die Quellen-URL als vorhanden, eine Dublette entsteht nicht. Commit `8a1110fc` |

## 2. Sofort sichtbar

Termin ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Polizei Düren 23.11. 09:44 Uhr, Trunkenheitsfahrt mit Bedrohung (Kontrolle in Düren, Fahrer 76 Jahre, aus Merzenich) | Ereignis in Düren, Merzenich nur als Wohnort; kein Ortsbezug nach SCHREIBREGELN 0b (wie beim Unfall am Heerweg, 20.11.); ChatGPT mittags gleich bewertet |
| Polizei Düren 23.11. 09:38 Uhr (Verdacht auf K.-o.-Tropfen bei einer Feier im Schloss Burgau) | kein Merzenicher Bezug |
| Kreis Düren 23.11.: Generationentag (2.12. in der Kreisverwaltung), Polizeiklasse am Berufskolleg | kreisweite Angebote ohne Merzenicher Beteiligung (0b) |
| Ergebnis Viktoria Arnoldsweiler II – FC Rhenania Girbelsrath 3:0 (FuPa) | FUSSBALL.DE um 14:44 Uhr weiter mit 14:34 Toren und ohne Spielbericht; FuPa allein reicht nicht (SCHREIBREGELN 3a). ChatGPT mittags ebenfalls T2 |
| DWD-Warnungen vor Frost und Glätte (Stand 14:38 Uhr, Stufe 2) | keine Unwetterlage; der Bestandsartikel ist datiert und braucht keinen weiteren Nachtrag |
| Ergebnisse HFA 19.11. | Sitzungsseite um 14:44 Uhr weiter ohne Niederschrift oder Beschlusstexte; Vorlagenliste unverändert (höchste Nummer 151/2026); Ratssitzung 3.12. ohne Tagesordnung |
| Friedenslicht als Meldung | nach SCHREIBREGELN Abschnitt 1 ein Termin (Datum, Ort, Ablauf), kein eigener Nachrichtenwert über die Ankündigung hinaus |
| Malwettbewerb-Bildbeschreibung (MA-KORR-MALWETTBEWERB-BILDBESCHREIBUNG-1411) | Werkzeugpunkt (Alt-Text bestehender Poolanhänge im Abgleich), Entscheidung Luis Kummer offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 23.11. mittags, zuerst KORREKTUREN | kein neuer Korrekturauftrag, keine neue Themenkarte |
| Polizei Düren | Dienststellenliste nr/8 14:44 Uhr; Volltexte der beiden Meldungen von 09:38 und 09:44 Uhr (Eil-Prüfung 10:41 Uhr) | kein Ortsbezug |
| Feuerwehr | Website (bis 147/26) und Heimat-Info-Profil (bis 149/26) 14:44 Uhr | nichts Neues |
| Kreis Düren | Presse-Index und zwei neue Mitteilungen 14:44 bis 14:45 Uhr | kein Ortsbezug |
| Deutscher Wetterdienst | `warnings.json` 14:44 Uhr (Datenstand 14:38 Uhr) | Frost und Glätte, Stufe 2 |
| Ratsinformationssystem | HFA-Seite, Ratssitzung 3.12., Vorlagenliste 14:44 Uhr; `termine.ics` 14:46 Uhr | nichts Neues |
| Heimat-Info | Gemeindeseite 14:44 Uhr; Veranstaltungskalender und elf Detailseiten 14:46 Uhr; Rohdaten des Quellenabrufs (22.11. 21:09 Uhr) | Termin Friedenslicht |
| Gemeinde Merzenich | Amtsblätter 11 und 12, Kulturkalender 16 unter `/bindata/` 14:46 Uhr | bytegleich |
| FUSSBALL.DE | Mannschaftsseite Girbelsrath 14:44 Uhr | Ergebnis weiter nicht lesbar |

## 5. Sport

Montag, Pflichtprüfung im Morgenlauf erledigt (Tabellen siehe [Dossier morgens](2026-11-23-claude-morgens.md), Abschnitt 5). Offen bleibt nur die Bestätigung des Girbelsrath-Ergebnisses durch FUSSBALL.DE. Nächstes Spiel: Girbelsrath am Do 26.11. um 19.30 Uhr in Schlich.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 14:46 Uhr gelesen: dieselben zehn IDs wie am Morgen, alle zehn Detailseiten und die Weihnachtsfeier Morschenich (`1e0ecb73`) im Veranstaltungsteil textgleich. `termine.ics` bis auf den technischen Zeitstempel gleich. Amtsblätter 11 und 12 und Kulturkalender 16 bytegleich; Schützen Morschenich, Ausstellungs- und Adventsfenster-Beitrag textgleich.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Friedenslicht-Aussendung 13.12. | friedenslicht-aussendung-pfadfinder-merzenich-2026-12-13 | neu (Abschnitt 1) | Heimat-Info `042c28a0` | 2026-11-23 |
| alle 18 Termine vom 23.11. bis 06.12. | je Slug | erneut bestätigt, Stand vom Morgen bleibt | jeweilige Quelle | 2026-11-23 |

**Neu:** einer. **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `8a1110fc` (Termin Friedenslicht), Dokumentation in diesem Commit. Abgleich 14:48 Uhr `angestossen: true`.

Live-Nachweis 14:48 Uhr: `/termine/friedenslicht-aussendung-pfadfinder-merzenich-2026-12-13/` HTTP 200.

## 8. Bildprüfung

Keine neue oder bildrelevant geänderte Meldung.

## 9. Offene Punkte für den nächsten Lauf

- Girbelsrath 0:3 in Arnoldsweiler: Kurzmeldung, sobald FUSSBALL.DE das Ergebnis lesbar führt.
- Nach dem nächsten Quellenabruf prüfen, ob die Termin-Automatik den Friedenslicht-Eintrag als vorhanden erkennt.
- HFA 19.11. und Ratssitzung 3.12.: weiter beobachten; Bürgewald-Ausschuss am 26.11.
- Entscheidungspunkte für Luis Kummer: dwd.de in die Liste `OFFIZIELL`; Werkzeugpunkte Alt-Text bestehender Poolanhänge, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2 (heute mit Folgen: der Friedenslicht-Termin fehlte deshalb).
