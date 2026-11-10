# Merzenich Aktuell – Nachmittagslauf (Claude), 10. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung 14:45 bis 14:55 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 10.11. morgens](2026-11-10-claude-morgens.md), seither neu [ChatGPT 10.11. mittags](2026-11-10-mittags.md). main vor dem Lauf: `0574fe90`. Eil-Prüfungen 10:44 und 12:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Vorschau Bauausschuss 12.11. (265 Wörter im Fließtext, Rathaus, `relevanz` 2) |
| Korrigierte Bestandsmeldungen | 0 | ChatGPT 10.11. mittags ohne Korrekturauftrag |
| Neue / geänderte Termine | 0 / 0 | Heimat-Info (10 Einträge) und Ratsinfo unverändert |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | 13 Quellen (Tagesordnung, sechs Vorlagen, sechs eigene Meldungen), 4 Aussagen |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Rathaus Merzenich |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | |
| Mindesttakt 10.11. | 2 Meldungen | Sport 09:20, Bauausschuss-Vorschau 14:48 Uhr |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-BAUAUSSCHUSS-12112026-VORSCHAU (neu) | „Bauausschuss in Merzenich am Donnerstag: Grundschule Golzheim, zwei Solarparks und der Bau-Turbo“ (Kurzmeldung, Termin-Vorschau nach CLAUDE-LAUF Abschnitt 7) | `inhalte/meldungen/2026-11-10-recherche-claude-nachmittags.json` → `/rathaus/bauausschuss-merzenich-12-november-2026-vorschau-grundschule-golzheim-solarparks-bau-turbo/` | Tagesordnung im Ratsinformationssystem 14:46 Uhr (unverändert seit 03.11.); Zahlen aus den Vorlagen 138, 144, 92, 142/2026 und M139, M140, M142/2026, die Merzenich Aktuell vom 25.10. bis 05.11. einzeln ausgewertet hat; Commit `d16371d5` |

Quellenausnahmen: keine. Die Vorschau bündelt die Bestandsartikel und verlinkt sie, keine Dublette eines einzelnen Artikels.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| „Tag der Tagespflege“ (Heimat-Info, 10.11. 13:11 Uhr) | Beitrag eines Pflegeanbieters in eigener Sache |
| „Adventausstellung“ (Heimat-Info, 08.11. 21:37 Uhr) | Beitrag eines Gewerbebetriebs |
| Wertung Girbelsrath – Morschenich | laut ChatGPT mittags weiter offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 10.11. mittags, zuerst KORREKTUREN | keine Korrekturen, keine neuen Themenkarten |
| Polizei Düren | Dienststellenliste, Eil-Prüfung 12:41 Uhr | neu Düren, Aldenhoven, Langerwehe; keine Erwähnung von Merzenich oder Ortsteilen (drei Meldungen im Volltext geprüft) |
| Heimat-Info | Gemeindeseite 14:50 Uhr, Veranstaltungskalender und zehn Detailseiten 14:50 Uhr | zwei neue Beiträge von Anbietern (Abschnitt 3); Kalender unverändert |
| Feuerwehr | Website und Heimat-Info-Profil, Eil-Prüfung 12:41 Uhr | nichts nach 147/26 |
| Ratsinformationssystem | `termine.ics` 14:50 Uhr, Sitzungsseite Bauausschuss 14:46 Uhr | unverändert |
| Amtsblatt, Kreis | laut ChatGPT mittags | nichts Neues mit Merzenich-Bezug |

## 5. Sport

Pflichtprüfung im Morgenlauf erledigt; Derbywertung offen.

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender`. Heimat-Info-Veranstaltungskalender der Gemeinde um 14:50 Uhr gelesen (10 Einträge, alle Detailseiten unverändert gegenüber 09:21 Uhr) und Ratsinfo-`termine.ics` (unverändert). Alle Termine bis 24.11. sind im Morgenlauf an ihrer Quelle bestätigt ([Dossier](2026-11-10-claude-morgens.md), Abschnitt 6).

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| alle Heimat-Info-Termine 11.–25.11. | siehe Dossier morgens | bestätigt, unverändert | heimat-info.de/veranstaltungen | 2026-11-10 |
| Bauausschuss 12.11., HFA 19.11., Jugendgremium 03.12. | je Slug | bestätigt, unverändert | Ratsinfo `termine.ics` | 2026-11-10 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner. Offene Hinweise „nachpruefen“: 0.

## 7. QA, Commit, Abgleich

Commit `d16371d5` (Meldung), Dokumentation folgt. Abgleich angestoßen 14:50 Uhr (`angestossen: true`), wp-cron HTTP 200. Live-Nachweis 14:50 Uhr: Vorschau HTTP 200, in der Nachrichten-Sitemap und oben in „Neu in Merzenich“ (vor der Sportmeldung).

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| bauausschuss-merzenich-12-november-2026-vorschau-grundschule-golzheim-solarparks-bau-turbo | rathaus.rat | rathaus-merzenich | Merzenich Rathaus HDR.jpg | CC BY-SA 3.0 | selbst angesehen: Rathaus Merzenich mit Schriftzug „Rathaus“, Sitz des Ausschusses; keine Personen, kein lesbares Kennzeichen |

Neu importierte Fotos: keine.

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll (offiziell / doppelt / Selbstauskunft) | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Vorschau Bauausschuss | 265 | 13 | 4 / 3 / 3 (Vorlagen offiziell, eigene Auswertungen als Archiv) | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Bauausschuss 12.11.: Ergebnisse am 13.11. als Folgemeldungen (Schule Golzheim, Solarparks, Bau-Turbo, Poolplatz).
- Wertung Girbelsrath – Morschenich; Papiersammlung ab 2027; Jugendgremium; Orte der KG-Jonge-vom-Berg-Termine.
- Werkzeugpunkte (Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
