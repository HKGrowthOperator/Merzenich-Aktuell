# Merzenich Aktuell – Nachmittagslauf (Claude), 18. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10, neu: Ortsbezug Pflicht). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung 14:44 bis 14:55 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 18.11. morgens](2026-11-18-claude-morgens.md), seither neu [ChatGPT 18.11. mittags](2026-11-18-mittags.md) und die Entscheidung des Betreibers zum Ortsbezug (Commit `85a3bd07`, 12:53 Uhr). main vor dem Lauf: `9905ade3`. Eil-Prüfungen 10:41 und 12:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 0 | keine neue belegbare Nachricht mit Ortsbezug (Abschnitt 3); Nullfund für neue Meldungen |
| Korrigierte Bestandsmeldungen | 1 | Bürgewald-Ausschuss (ChatGPT-Korrektur MA-KORR-BAUTURBO-AUSSENBEREICH-1811) |
| Neue / geänderte Termine | 0 / 0 | ein Prüfvermerk aktualisiert (Frühstückstreff 25.11.) |
| Termine an der Quelle bestätigt | 20 | alle Termine 18.11. (ab 15 Uhr) bis 03.12. |
| Bilder gefunden / geprüft / integriert | 0 / 0 / 0 | keine neue Meldung |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs --datum 2026-11-18 --lauf morgens` (korrigiertes Paket) 0 Fehler, 1 Hinweis wie am Morgen | |

Mindesttakt 18.11.: 1 Meldung (Morgenlauf); im Nachmittag keine belegbare neue Nachricht mit Ortsbezug.

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-KORR-BAUTURBO-AUSSENBEREICH-1811 (ChatGPT mittags) | Korrektur im Bürgewald-Artikel, 3. Absatz. Alt: „Über Vorhaben auf mehr als 0,1 Hektar oder mit mehr als zehn Wohnungen und über jedes Vorhaben im Außenbereich soll die Politik entscheiden.“ Neu: „Über Vorhaben auf mehr als 0,1 Hektar oder mit mehr als zehn Wohnungen soll die Politik entscheiden, nach einem Punkt der Leitlinien auch über Vorhaben im Außenbereich. Ein anderer Punkt überlässt kleine Außenbereichsvorhaben mit 0,1 Hektar und bis zu fünf Wohnungen aber der Verwaltung; wie beide Regeln zusammenpassen, lässt der Entwurf offen.“ Dazu „Korrigiert am 18. November: …“ und Prüfeintrag mit Nr. 2.1 und 2.2 der Leitlinien | `inhalte/meldungen/2026-11-18-recherche-claude-morgens.json` → `/rathaus/buergewald-ausschuss-merzenich-26-november-2026-solarpark-a4-bau-turbo-vermarktung-erster-abschnitt/` | Sitzungspaket S. 20 (Leitlinien der Gemeinde, Nr. 2.1 und 2.2) um 14:45 Uhr selbst gelesen; der Widerspruch bleibt im Text benannt, nicht aufgelöst; 915 Wörter; Commit `5b2f625a` |
| Kalender | 20 Termine bestätigt, Prüfvermerk Frühstückstreff 25.11. | `inhalte/termine/2026-11-12-recherche-claude-morgens.json` | Abschnitt 6; Commit `52700568` |

Ortsbezug (SCHREIBREGELN 0b) der heutigen Inhalte geprüft: Bürgewald-Ausschuss (Sitzung im Rathaus Merzenich, Projekte in Bürgewald), Ausstellung (Ratssaal), Wunschbaum (Fußballhalle Merzenich) und Ratssitzung erfüllen Ziffer 1. Der Hinweis auf den tamilischen Verein in Jülich aus dem Morgendossier ist mit der Streichung der Daueraufgabe hinfällig; der Betreiber hat den Eintrag aus `QUELLEN.md` 3.9a entfernt.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Korrektur ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Vorschau Haupt- und Finanzausschuss 19.11. | alle öffentlichen Punkte (Brandschutzbedarfsplan, Hundesteuer, Übernachtungssteuer, Hebesätze, Digitalisierungscheck, Kita-Patronat, Abwasser, Unterkünfte, Neuland, Shared Service Center, Trauungen) sind bereits als Einzelmeldungen und in der Wochenvorschau online; eine Sammelmeldung wäre eine Dublette. Ergebnisse nach der Sitzung |
| Polizei 18.11. 10:45 Uhr (fünf Meldungen) | Düren-Birkesdorf, Langerwehe (zwei), Kreuzau, Jülich; Volltexte ohne Merzenicher Ort, kein Ortsbezug |
| Kreis Düren (Familientag Nideggen, Wifö on Tour Niederzier) | kein Ortsbezug |
| Malwettbewerb-Bildbeschreibung (MA-KORR-MALWETTBEWERB-BILDBESCHREIBUNG-1411) | Werkzeugpunkt (Alt-Text bestehender Poolanhänge), Entscheidung Luis Kummer offen |
| Tamilische Feste (ChatGPT mittags: Originalprüfung unvollständig) | Meldung am 18.11. vom Betreiber zurückgezogen (kein Ortsbezug); nichts weiter zu tun |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 18.11. mittags, zuerst KORREKTUREN | eine neue Korrektur umgesetzt; Ausstellungskorrektur live bestätigt; keine neue Themenkarte |
| Betreiber | Commit `85a3bd07` (Ortsbezug Pflicht, zehn Beiträge und fünf Termine zurückgezogen), SCHREIBREGELN 0b, CLAUDE-LAUF 3.10 | gelesen und angewendet |
| Polizei Düren | Dienststellenliste nr/8, 12:41 und 14:47 Uhr; fünf Meldungen von 10:45 Uhr im Volltext | kein Merzenich-Bezug |
| Feuerwehr | Website und Heimat-Info-Profil, 14:47 Uhr | weiter bis 147/26 |
| Heimat-Info | Gemeindeseite 14:47 Uhr; Veranstaltungskalender und zehn Detailseiten 14:48 Uhr | nur neue Zeitstempel der Seniorentermine 25.11. |
| Kreis Düren | Presse-Index 14:47 Uhr | nichts mit Ortsbezug |
| Ratsinformationssystem | Vorlagenliste 14:48 Uhr (erster Abruf Verbindungsabbruch, Wiederholung HTTP 200); `termine.ics` 14:48 Uhr | keine neue Vorlage, Rat 3.12. noch ohne Tagesordnung |
| Gemeinde Merzenich | Amtsblätter, Pressemitteilungen, Bekanntmachungen 14:47 Uhr | HTTP 428 (Bot-Prüfung), nicht umgangen; Amtsblatt-PDFs unter `/bindata/` lesbar und bytegleich |

## 5. Sport

Mittwoch, kein Pflichttag. Nächstes Spiel mit Merzenicher Beteiligung: Girbelsrath bei Viktoria Arnoldsweiler II, Fr. 20.11., 19.30 Uhr.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 14:48 Uhr gelesen: dieselben zehn IDs wie am Morgen, acht Detailseiten textgleich, zwei (Frühstückstreff und Skatrunde 25.11., `0b6866fc`, `7d1b5151`) nur mit neuem Veröffentlichungszeitstempel 18.11. 10:07 Uhr, Inhalt gleich; der Frühstückstreff nennt weiter keinen Ort. `termine.ics` unverändert (Rat 3.12. 18 Uhr). Amtsblätter 08, 11, 12, Kulturkalender 16, Schützen Morschenich und Pluriversum-Beitrag bytegleich mit dem Morgen; Ausstellungs- und Wunschbaum-Beitrag inhaltlich unverändert.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Frühstücks- und Spieletreff 25.11. | neu-fruehstuecks-und-spieletreff-fuer-senior-innen-merzenich-2026-11-25 | Prüfvermerk „zuletzt geprüft am 18. November“, Ort weiter offen | Heimat-Info | 2026-11-18 |
| übrige 19 Termine vom 18.11. (ab 15 Uhr) bis 03.12. | je Slug | bestätigt | jeweilige Quelle | 2026-11-18 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner. Die vom Betreiber zurückgezogenen Termine (Mental Load, Seniorenweihnacht des Kreises u. a.) sind nicht mehr im Bestand.

## 7. QA, Commit, Abgleich

Commits: `5b2f625a` (Korrektur), `52700568` (Kalender), Dokumentation in diesem Commit. Abgleich 14:46 Uhr `angestossen: true`.

Live-Nachweis 14:50 Uhr: korrigierte Fassung des Bürgewald-Artikels live („Ein anderer Punkt überlässt kleine Außenbereichsvorhaben …“, „Korrigiert am 18. November: Die Leitlinien sehen …“).

## 8. Bildprüfung

Keine neue oder bildrelevant geänderte Meldung in diesem Lauf (die Korrektur betrifft nur Text; Bild des Bürgewald-Artikels am Morgen geprüft, siehe [Dossier 18.11. morgens](2026-11-18-claude-morgens.md), Abschnitt 8).

## 9. Offene Punkte für den nächsten Lauf

- HFA 19.11.: Empfehlungen als Folgemeldungen (Abgaben, Übernachtungssteuer, Neuland, Kita, Brandschutzbedarfsplan, Trauungen, Shared Service Center).
- Bauausschuss 12.11.: Niederschrift abwarten.
- Gemeinderat 3.12.: Tagesordnung abwarten.
- Girbelsrath Fr. 20.11. (Freitagsprüfung Sport).
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
