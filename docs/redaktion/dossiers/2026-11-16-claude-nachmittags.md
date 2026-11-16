# Merzenich Aktuell – Nachmittagslauf (Claude), 16. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung 14:45 bis 15:09 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 16.11. morgens](2026-11-16-claude-morgens.md), seither neu [ChatGPT 16.11. mittags](2026-11-16-mittags.md). main vor dem Lauf: `0cf5100a`. Eil-Prüfungen 10:41 und 12:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Übernachtungssteuer (282 Wörter im Fließtext, Rathaus, `relevanz` 3) |
| Korrigierte Bestandsmeldungen | 1 | Sportmeldung 16.11.: SC war schon vor dem Spiel Vierter (ChatGPT mittags) |
| Ergänzte Bestandsmeldungen | 3 | Advents-Übersicht (Nachtrag), Pluriversum-Meldung (Nachtrag), Kita-Meldung (Zahl der Kitas) |
| Neue / geänderte Termine | 4 / 3 | neu: AWO-Weihnachtsfeier 11.12., Taizé 06.12. und 03.01., Kindergottesdienst 13.12.; ergänzt: Pluriversum 17.11., Adventmarkt 28.11., Bürgewaldzentrum 28.11. |
| Termine an der Quelle bestätigt | 21 + 13 | 21 im Morgenlauf; nachmittags Heimat-Info (10 Detailseiten) und Ratsinfo `termine.ics` erneut unverändert |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | Kurzmeldung mit offizieller Quelle (Vorlage, Tagesordnung), 4 Aussagen |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Rathaus Merzenich |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise (auch für die geänderten Pakete 15.11.) | |
| Mindesttakt 16.11. | 2 Meldungen | Fußball-Wochenende 09:23, Übernachtungssteuer 14:50 Uhr |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-KORR-SC-RANGWECHSEL-1611 (ChatGPT mittags) | Korrektur in `fussball-merzenich-0-1-frenz-…-2026-11-15`. Alt: „Mit 27 Punkten rutscht der SC vom dritten auf den vierten Platz.“ Neu: „Mit 27 Punkten bleibt der SC Vierter: Welldorf-Güsten hatte ihn schon am Freitag, 13. November, mit einem 1:0 gegen Oberzier vom dritten Platz verdrängt.“ | `inhalte/meldungen/2026-11-16-recherche-claude-morgens.json` | FUSSBALL.DE-Spielbericht Welldorf – Oberzier (14.11. 07:04 Uhr) und Welldorf-Mannschaftsseite („Letztes Spiel: Fr, 13.11.2026 19:15“), gelesen 14:45 Uhr; Commit `6d1447e1` |
| MA-UEBERNACHTUNGSSTEUER-2026 (neu) | „Übernachtungssteuer für Merzenich? Ausschuss entscheidet, ob die Verwaltung weiter prüft“ (Kurzmeldung): Antrag der SPD-Fraktion aus den Haushaltsberatungen; Satzung und Genehmigung durch Kommunal- und Finanzministerium NRW nötig; Vergleichswerte 5 % oder 2 bis 3 € je Übernachtung; vier Betriebe nach erster Recherche der Verwaltung; Alternative A (konkretisieren) oder B (beenden), kein Votum | `inhalte/meldungen/2026-11-16-recherche-claude-nachmittags.json` → `/rathaus/merzenich-uebernachtungssteuer-pruefung-haupt-finanzausschuss-19-november-2026/` | Beschlussvorlage 104/2026 (4 Seiten), gelesen 14:49 Uhr; HFA-Tagesordnung und `termine.ics`; Anlage A nicht ausgewertet; Commit `27aada39` |
| Amtsblatt 12, Termine | neu: AWO-Weihnachtsfeier 11.12. (15.30 Uhr, Merzenicher Brauhaus, 60 Plätze, Anmeldung), Taizé-Gottesdienste Morschenich 06.12. und 03.01. (17 Uhr), Kindergottesdienst mit Frühstück 13.12. (11 Uhr, Ev. Gemeindehaus; Anmeldefrist „bis 31.10.“ im Amtsblatt als überholt benannt); ergänzt: Pluriversum 17.11. (Plakat: 18 Uhr Einlass, 18.45 Uhr Bühne; Kalender weiter 18.15 Uhr), Adventmarkt 28.11. (Sa bis 19 Uhr, Programm, Nikolaus „etwa gegen 15.15 Uhr“ statt „Einbruch der Dunkelheit“ als abweichende Angabe), Bürgewaldzentrum 28.11. („Glühweinfenster“ mit SV Morschenich, Fotoausstellung, Weihnachtsmarkt) | `inhalte/termine/2026-11-16-amtsblatt-12-claude.json`, `2026-10-16-amtsblatt-11.json`, `2026-11-04-kalenderpflege-claude.json` | Amtsblatt Nr. 12 vom 13.11.2026 (32 Seiten, PDF erstellt 13.11. 13:33 Uhr UTC), gelesen 14:47 Uhr; Commit `fe28fa62` |
| Updates Bestand | Advents-Übersicht: Nachtrag vom 16. November (Adventmarkt, Glühweinfenster, AWO, Taizé, Kindergottesdienst), Satz zum Golzheimer Adventskalender umformuliert (9 Wörter gleich mit Amtsblatt); Kita-Meldung: „mehrere Kitas im Gemeindegebiet“ ersetzt durch „sechs Kitas in Merzenich, Girbelsrath, Golzheim und Morschenich, … über 300 Kinder zwischen einem und sechs Jahren“ (Amtsblatt 12, S. 9); Pluriversum-Meldung vom 16.10.: Nachtrag mit Ablauf | `2026-11-15-recherche-claude-nachmittags.json`, `2026-11-15-recherche-claude-morgens.json`, `2026-10-16-amtsblatt-11.json` | Commits `8e8930c7`, `97c77a43` |

Quellenausnahmen: ChatGPT führt die Ergänzungen aus Amtsblatt 12 als T2 (keine unabhängige Bestätigung der Programmzeiten). Übernommen, weil das Amtsblatt die offizielle Veröffentlichung der Gemeinde ist; wo es älteren Angaben widerspricht (Nikolaus, Pluriversum-Beginn), stehen beide Angaben mit Quelle im Text, nichts ist zusammengeführt. Nicht übernommen: „Eintritt frei“ beim Pluriversum (Zuordnung im Layout nicht eindeutig), Sonntagscafé 06.12. (kein Ort genannt), Senioren-Nachmittag 18.11. und Adventsfeier 09.12. bleiben mit offener Zuordnung des Chanukka-Vortrags (Amtsblatt 12, S. 13, hat dasselbe mehrdeutige Layout wie Heimat-Info).

Geprüft und verworfen als neue Meldungen (Dubletten): Aufruf „Lebendiger Adventskalender 2026, Gastgeber gesucht“ (gemeldet am 16.10. aus Amtsblatt 11), Aufruf zur Altpapiertonne (gemeldet am 15.10. aus Heimat-Info).

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung, Korrektur, Updates und Termine ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Bürgerpokalschießen Golzheim (Amtsblatt 12, S. 22) | Siegerehrung am 10.10., Vereinsbericht mit vielen Namen von Privatpersonen; kein aktueller Nachrichtenwert |
| Diamantene Hochzeit, Eheschließungen (S. 5 und 6) | Privatpersonen |
| Wifö on Tour (Kreis, 16.11.) | Station in Niederzier, kein Merzenich-Bezug |
| Neuland Hambach (151/2026), Bauausschuss 12.11. | weiter ohne neue Unterlagen bzw. Ergebnisse |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 16.11. mittags, zuerst KORREKTUREN | eine Korrektur (Sport), umgesetzt; Bildfehler Malwettbewerb live weiter offen; Karte Adventmarkt-Update (T2) |
| Amtsblatt | Nr. 12 vom 13.11.2026, PDF direkt, 14:47 Uhr | 32 Seiten vollständig durchgesehen; siehe Abschnitt 1 und 3; nächste Ausgabe 11.12., Redaktionsschluss 01.12. |
| Ratsinformationssystem | Vorlage 104/2026 14:49 Uhr; `termine.ics` 14:58 Uhr | siehe Abschnitt 1; ICS unverändert |
| FUSSBALL.DE | Spielbericht Welldorf – Oberzier und Welldorf-Mannschaftsseite, 14:45 Uhr | Korrektur belegt |
| Polizei Düren | Dienststellenliste nr/8, 12:41 und 14:59 Uhr | sechs Mitteilungen vom 16.11. 12:00 Uhr (Düren, Kreuzau-Bereich, Hürtgenwald), keine mit Merzenich |
| Feuerwehr | Website und Heimat-Info-Profil, 14:59 Uhr | weiter bis 147/26 |
| Heimat-Info | Gemeindeseite 14:59 Uhr; Veranstaltungskalender und zehn Detailseiten 14:58 Uhr | Gemeindebeitrag „Lebendiger Adventskalender“ (10:41 Uhr) zum bekannten Termin 15.12.; Kalender unverändert |
| Kreis Düren | Presse-Index 14:59 Uhr | „Wifö on Tour“ (Niederzier) |

## 5. Sport

Korrektur siehe Abschnitt 1. Spielstand unverändert seit dem Morgenlauf (nächstes Spiel 29.11.).

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender um 14:58 Uhr erneut gelesen (dieselben 10 IDs wie morgens, alle Detailseiten unverändert), Ratsinfo-`termine.ics` unverändert. Alle 21 Termine bis 29.11. im Morgenlauf an ihrer Quelle bestätigt ([Dossier morgens](2026-11-16-claude-morgens.md), Abschnitt 6). Neue Quelle Amtsblatt 12:

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Pluriversum 17.11. | pluriversum-gesamtschule-merzenich-2026-11-17 | **geändert** (Ablauf ergänzt, Beginn 18 Uhr bleibt) | Amtsblatt 12, S. 3 | 2026-11-16 |
| Adventmarkt 28.11. | adventmarkt-heimatmuseum-2026-11-28 | **geändert** (Ende 19 Uhr, Programm, Quelle jetzt Amtsblatt 12) | Amtsblatt 12, S. 14 und 15 | 2026-11-16 |
| Adventsfenster/Glühweinfenster Bürgewaldzentrum 28.11. | adventsfenster-bwz-morschenich-2026-11-28 | **geändert** (SV Morschenich, Fotoausstellung, Weihnachtsmarkt) | Amtsblatt 12, S. 14; Terminplan Schützen | 2026-11-16 |
| Taizé-Gottesdienst 06.12., 17 Uhr | taize-gottesdienst-morschenich-2026-12-06 | **neu** | Amtsblatt 12, S. 11 | 2026-11-16 |
| AWO-Weihnachtsfeier 11.12., 15.30 Uhr | awo-weihnachtsfeier-senioren-brauhaus-merzenich-2026-12-11 | **neu** | Amtsblatt 12, S. 14 und 16 | 2026-11-16 |
| Kindergottesdienst 13.12., 11 Uhr | kindergottesdienst-ev-gemeindehaus-2026-12-13 | **neu** | Amtsblatt 12, S. 13 | 2026-11-16 |
| Taizé-Gottesdienst 03.01.2027, 17 Uhr | taize-gottesdienst-morschenich-2027-01-03 | **neu** | Amtsblatt 12, S. 11 | 2026-11-16 |

**Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `6d1447e1` (Korrektur Sport), `27aada39` (Meldung), `fe28fa62` (Termine), `8e8930c7` (Updates Advent und Kita), `97c77a43` (Nachtrag Pluriversum), je Rebase-Konflikt nur in `docs/SHA256SUMS.txt` (Stand von main, Kette neu); Dokumentation folgt. Abgleich 14:47 Uhr `angestossen: true` (Korrektur), danach planmäßig 15:45 Uhr; wp-cron HTTP 200.

Live-Nachweis 15:08 Uhr (Abgleich früher als planmäßig, nach Anstoß): Übernachtungssteuer-Meldung HTTP 200, in der Nachrichten-Sitemap, auf der Startseite in „Neu in Merzenich“ (3 Verweise); Korrektur in der Sportmeldung live („bleibt der SC Vierter“); Nachträge live in der Advents-Übersicht („Nachtrag vom 16. November“), der Kita-Meldung („sechs Kitas“) und der Pluriversum-Meldung („18.45 Uhr“); neue Termine AWO 11.12., Taizé 06.12. und 03.01., Kindergottesdienst 13.12. je HTTP 200. Bildbeschreibung im Malwettbewerb-Artikel live weiter „Polizeiwache in Nordrhein-Westfalen“ (ChatGPT mittags): Werkzeugpunkt, Entscheidung Luis Kummer offen.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| merzenich-uebernachtungssteuer-pruefung-haupt-finanzausschuss-19-november-2026 | rathaus.rat | rathaus-merzenich | Merzenich Rathaus HDR.jpg | CC BY-SA 3.0 | am 16.11. erneut selbst angesehen: Rathaus Merzenich mit Schriftzug „RATHAUS“, Winteraufnahme, keine Personen, am Bildrand ein Auto ohne lesbares Kennzeichen; der Haupt- und Finanzausschuss tagt im Ratssaal, die Steuer wäre eine Satzung des Rates; als Symbolbild gekennzeichnet |

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Übernachtungssteuer | 282 | 4 | 4 Einträge, alle mit offizieller Quelle (Vorlage, Tagesordnung); einer zusätzlich eigenes Archiv | 0 Wörter |
| Advents-Übersicht (Update) | 933 | 13 | 9 Einträge (neu: Amtsblatt 12) | 0 Wörter nach Umformulierung |
| Kita-Patronat (Update) | – | 5 | 4 Einträge (neu: Amtsblatt 12, S. 9) | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- HFA 19.11.: Empfehlungen nach der Sitzung als Folgemeldungen (Abgaben, Unterkünfte, Trauentgelte, Kita-Patronat, Shared Service Center, Übernachtungssteuer).
- Pluriversum 17.11.: Beginn 18 Uhr (Einlass) und 18.45 Uhr (Bühne), Kalender 18.15 Uhr; nach dem Abend nichts mehr ändern.
- Adventmarkt: Nikolauszeit beim Verein klären, wenn eine neue Mitteilung erscheint.
- Bauausschuss 12.11.: Ergebnisse abwarten.
- Sport: Girbelsrath Fr. 20.11.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
