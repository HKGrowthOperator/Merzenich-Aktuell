# Merzenich Aktuell – Nachmittagslauf (Claude), 15. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung 14:44 bis 15:00 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 15.11. morgens](2026-11-15-claude-morgens.md), seither neu [ChatGPT 15.11. mittags](2026-11-15-mittags.md). main vor dem Lauf: `0839b6b8` (plus Dokumentation des Morgenlaufs). Eil-Prüfungen 10:41 und 12:41 Uhr ohne Fund; eine Eil-Prüfung um 14:40 Uhr ist nicht eingetroffen, Polizei, Feuerwehr und Gemeindebeiträge hat dieser Lauf um 14:53 Uhr selbst gelesen.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Standard „Advent in Merzenich 2026“ (808 Wörter im Fließtext, Leben, `relevanz` 3) |
| Korrigierte Bestandsmeldungen | 0 neu | ChatGPT mittags: nur der bekannte Bildfehler Malwettbewerb, live weiter offen (Werkzeugpunkt, Abschnitt 7) |
| Neue / geänderte Termine | 1 / 0 | Skatrunde für Senioren 02.12. |
| Termine an der Quelle bestätigt | 13 | Heimat-Info (10 Detailseiten, davon 1 neu in der Liste), Ratsinfo `termine.ics` unverändert |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | Standard mit 12 Quellen, 8 Aussagen |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Rathaus Dülmen als Adventskalender (Pool `veranstaltungen`) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | |
| Mindesttakt 15.11. | 2 Meldungen | Kita-Patronatserklärung 09:16, Advent 14:49 Uhr |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-ADVENT-2026 (neu) | „Advent in Merzenich 2026: Adventsmarkt, Adventsfenster und Weihnachtsshow, alle Termine bis 20. Dezember“ (Standard): Seniorenweihnacht des Kreises 24.11.; Tannenbaumschmücken Lindenplatz und Adventfeier der Schützen 27.11.; Adventmarkt des Heimatvereins (28.11. ab 14 Uhr, 29.11. 11 bis 18 Uhr), „Weinbergschnecken“, Adventsfenster der Schützen am Bürgewaldzentrum und Golzheimer Adventskalender 28.11.; Adventsliedersingen 29.11.; ISM-Weihnachtsfeier und Weihnachtsschießen Morschenich 06.12.; Senior:innen-Adventsfeier 09.12.; Weihnachtsfenster der Gemeinde am Rathaus 15.12.; Funky Monkeys 19./20.12.; Einsendeschluss Malwettbewerb; Kartenverkauf Krätzchensitzung | `inhalte/meldungen/2026-11-15-recherche-claude-nachmittags.json` → `/leben/advent-merzenich-2026-adventsmarkt-adventsfenster-weihnachtsshow-termine/` | 16. Kulturkalender und Amtsblatt 11/2026 (12.11.), Terminplan Schützen Morschenich (15.11. 14:49 Uhr), Heimat-Info-Beiträge und -Kalender (12. und 15.11.), Kreis Düren (Mitteilung 19.10., am 15.11. erneut gelesen); Commit `4d822e16` |
| Kalender | Männergruppe und Skatrunde für Senioren, Evangelisches Gemeindehaus, 02.12., 9 bis 11 Uhr | `inhalte/termine/2026-11-15-recherche-claude-nachmittags.json` | Heimat-Info-Eintrag 33400a80 (eingestellt 05.11., bisher nicht auf der ersten Kalenderseite), gelesen 14:53 Uhr; Commit `3256e1f9` |

Quellenausnahmen: keine. Die Übersicht bündelt Termine; Einzelmeldungen (Malwettbewerb, Wochenvorschau) sind verlinkt, keine Dublette. Benannte Widersprüche im Text: Senior:innen-Adventsfeier 09.12. (Überschrift im Heimat-Info-Eintrag „Senioren-Nachmittag“ mit Termin 18.11., Text nennt den 09.12.), Krätzchensitzung (Beginn 14 oder 15 Uhr je nach Quelle). Register: `MA-ADVENTMARKT-2811` übernommen; `MA-ADVENTFENSTER-2026` bleibt für eine eigene Meldung zur Reihe gesperrt (keine Gastgeberliste 2026), die Übersicht nennt nur belegte Einzeltermine.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung und neuer Termin ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Taizé-Gottesdienst Morschenich 15.11., 17 Uhr | Beitrag der Kapelle 12:24 Uhr; laut Eil-Prüfung 12:41 Uhr bereits im Kalender |
| Gottesdienst 22.11. (Heimat-Info 65643863) | nur Beitragszeitstempel neu (15.11. 10:15 Uhr), Termin und Ort unverändert (wie ChatGPT mittags) |
| MA-FUSSBALL-GIRB-LANGERWEHE-1411 (T2) | Sportmeldung Dienstag mit allen Spielen des Wochenendes |
| Bauausschuss 12.11. | weiter keine Ergebnisse (laut ChatGPT mittags) |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 15.11. mittags, zuerst KORREKTUREN | keine neue Korrektur; Bildfehler Malwettbewerb live offen; keine neuen Karten |
| Polizei Düren | Dienststellenliste nr/8, 14:53 Uhr | letzte Meldung 14.11. 08:09 Uhr (Düren), nichts mit Merzenich |
| Feuerwehr | Website und Heimat-Info-Profil, 14:54 Uhr | weiter bis 140/26 bzw. 147/26 |
| Heimat-Info | Gemeindeseite 14:53 Uhr; Veranstaltungskalender und zehn Detailseiten 14:51 bis 14:53 Uhr | Taizé-Gottesdienst (bekannt); Kalender siehe Abschnitt 6 |
| Ratsinformationssystem | `termine.ics`, 14:51 Uhr | unverändert |
| Kreis Düren | Mitteilung Seniorenweihnacht, 14:55 Uhr | Datum 19.10.2026, Beginn 15 Uhr, Einlass 14 Uhr, Restkarten: wie im Artikel |

## 5. Sport

Sonntag, Spieltag; Ergebnisse und Tabelle am Dienstag.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender um 14:51 Uhr gelesen (10 Einträge). Gegenüber dem Morgen ist der Frühstückstreff 02.12. (6b9b9f9d) aus der ersten Kalenderseite gefallen, die Detailseite ist weiter erreichbar (HTTP 200, unverändert); neu auf der ersten Seite steht die Skatrunde 02.12. (33400a80), die im Bestand fehlte. Übrige acht Detailseiten unverändert; Gottesdienst 22.11. nur mit neuem Zeitstempel. Ratsinfo-`termine.ics` unverändert. Termine bis 29.11. am 12. bis 15.11. an ihrer Quelle bestätigt ([Dossier morgens](2026-11-15-claude-morgens.md), Abschnitt 6).

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Männergruppe / Skatrunde für Senioren, 02.12., 9 bis 11 Uhr | maennergruppe-skatrunde-fuer-senioren-merzenich-2026-12-02 | **neu** | Heimat-Info 33400a80 | 2026-11-15 |
| Frühstückstreff 02.12. | neu-fruehstuecks-und-spieletreff-fuer-senior-innen-merzenich-2026-12-02 | bestätigt (Detailseite direkt) | Heimat-Info 6b9b9f9d | 2026-11-15 |
| Seniorentreffs 18./25.11., Senioren-Nachmittag 18.11., Pluriversum 17.11., Gottesdienst 22.11., Adventsliedersingen 29.11. | je Slug | bestätigt | Heimat-Info | 2026-11-15 |
| HFA 19.11., Bürgewald-Ausschuss 26.11., Rat und Jugendgremium 03.12. | je Slug | bestätigt | Ratsinfo `termine.ics` | 2026-11-15 |

**Neu:** Skatrunde 02.12. **Geändert:** keiner. **Abgesagt:** keiner.

Werkzeugpunkt „Heimat-Info-Kalender Seite 2“ bestätigt sich: Die erste Kalenderseite zeigt nur zehn Einträge, spätere Termine (hier 02.12.) erscheinen erst, wenn frühere entfallen.

## 7. QA, Commit, Abgleich

Commits: `4d822e16` (Meldung), `3256e1f9` (Termin; Rebase-Konflikt nur in `docs/SHA256SUMS.txt`, Stand von main, Kette neu), Dokumentation folgt. Abgleich 14:49 Uhr `angestossen: true`; wp-cron HTTP 200.

Live-Nachweis 14:50 Uhr: Meldung HTTP 200 (Titel wie im Repository), in der Nachrichten-Sitemap, in `wordpress-delivery/merzenich-aktuell-import.xml` auf GitHub (2 Treffer), oben in „Neu in Merzenich“ (3 Verweise auf der Startseite). Bildbeschreibung im Malwettbewerb-Artikel live weiter „Polizeiwache in Nordrhein-Westfalen“: Der Abgleich zieht Alt-Texte bestehender Poolanhänge nicht nach ([Dossier 14.11. nachmittags](2026-11-14-claude-nachmittags.md), Abschnitt 7); Entscheidung Luis Kummer offen.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| advent-merzenich-2026-adventsmarkt-adventsfenster-weihnachtsshow-termine | leben.brauchtum.advent | adventskalender | Dülmen, Rathaus, -Weihnachtsmarkt- -- 2012 -- 9396.jpg | CC BY-SA 4.0 | am 15.11. selbst angesehen: Rathausfassade mit 24 beleuchteten, nummerierten Fenstern als Adventskalender, Abendaufnahme, keine Personen, keine Kennzeichen; passt zum Weihnachtsfenster am Rathaus Merzenich und den Adventsfenstern, als Symbolbild gekennzeichnet (nicht Merzenich) |

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Advent in Merzenich | 808 | 12 | 8 Einträge, davon 6 mit offizieller Quelle (Gemeinde, Kreis); 2 mit Veranstalterangaben aus mehreren Herausgebern bzw. Selbstauskunft | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Sport Dienstag 17.11.: Ergebnisse des Wochenendes (auch Girbelsrath 14.11.), Tabelle, Spielstand.
- HFA 19.11.: Empfehlungen nach der Sitzung als Folgemeldungen (Abgaben, Unterkünfte, Trauentgelte, Kita-Patronat, Shared Service Center, Übernachtungssteuer).
- Bauausschuss 12.11.: Ergebnisse abwarten.
- Advent: weitere Adventsfenster und Gottesdienste nachtragen, sobald veröffentlicht; Zeitkonflikte Senior:innen-Adventsfeier und Krätzchensitzung klären.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
