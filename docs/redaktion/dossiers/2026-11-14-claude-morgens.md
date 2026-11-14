# Merzenich Aktuell – Morgenlauf (Claude), 14. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung 09:14 bis 09:22 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 13.11. nachmittags](2026-11-13-claude-nachmittags.md), seither neu [ChatGPT 14.11. morgens](2026-11-14-morgens.md). main vor dem Lauf: `ba309a61`. Eil-Prüfungen 13.11. 17:10, 18:45, 20:41 Uhr und 14.11. 06:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Weihnachtsmalwettbewerb der Polizei (261 Wörter im Fließtext, Leben, `relevanz` 3) |
| Korrigierte Bestandsmeldungen | 0 | ChatGPT 14.11. morgens ohne Korrekturauftrag; Wochenvorschau dort ohne Abweichung |
| Neue / geänderte Termine | 0 / 0 | |
| Termine an der Quelle bestätigt | 13 | Heimat-Info (10 Detailseiten unverändert), Ratsinfo `termine.ics` unverändert |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | Kurzmeldung mit offizieller Quelle (Polizeimitteilung im Presseportal), 3 Aussagen |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Polizeimotorrad (Pool `polizei`), neue Bildklasse `leben.polizei.aktion` |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-POLIZEI-MALWETTBEWERB-2026 | „Malwettbewerb der Polizei Düren: Auch Kinder aus Merzenich können bis 14. Dezember mitmachen“ (Kurzmeldung): Motto „Weihnachten auf Streife“, Kinder bis elf Jahre, Vorjahr 105 Kinder; Einsendung bis 14.12. per Post oder E-Mail an die Pressestelle; Angaben für Einzel- und Gruppenteilnahme; vier Gewinnerbilder, Besuch mit dem Streifenwagen am 22.12. | `inhalte/meldungen/2026-11-14-recherche-claude-morgens.json` → `/leben/weihnachtsmalwettbewerb-polizei-dueren-2026-kinder-merzenich-mitmachen-14-dezember/` | Polizeimitteilung im Presseportal (13.11. 08:56 Uhr, gelesen 14.11. 09:15 Uhr); Commit `45ee6ad4` |

Quellenausnahmen: ChatGPT führte die Karte als T2 (Widersprüche in den Teilnahmebedingungen, unabhängige Bestätigung fehlt). Umgesetzt als Service-Kurzmeldung, weil die Polizeimitteilung offizielle Quelle ist (SCHREIBREGELN Abschnitt 0) und der Text nur deren Angaben wiedergibt. Die Teilnahmebedingungen (PDF) und die Behördenseite lieferten um 09:15 Uhr eine Bot-Prüfung (HTTP 503); nicht umgangen, im Text offen genannt. Die von ChatGPT benannten Widersprüche der Bedingungen (Rückmeldefrist, Datenwege) sind nicht Teil der Meldung. Keine Kinderdaten.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| MA-BAHN-DUEREN-HORREM-WEICHE-1411 (T2) | RE 1/RE 9, keine S19-Betroffenheit belegt; Endfeld 09 Uhr, bei Lauf nicht erneut als fortdauernd geprüft |
| MA-SC-TABELLE-1411 (T2) | Tabellenstand nach Freitagsspielen; Spielstand wird mit der Sportprüfung am Dienstag aktualisiert |
| Bauausschuss 12.11. | weiter keine Ergebnisse im Ratsinformationssystem (laut ChatGPT 07:05 Uhr) |
| Förder-Infoabend der LAG in Eschweiler | Veranstaltung außerhalb der Gemeinde |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 14.11. morgens, zuerst KORREKTUREN | keine Korrekturen; drei T2-Karten (Bahn, Malwettbewerb, Tabelle) |
| Polizei Düren | Dienststellenliste nr/8 und Mitteilung 6389777, 09:15 Uhr; neue Meldung 14.11. 08:09 Uhr (Düren) | Malwettbewerb siehe Abschnitt 1; neue Meldung ohne Merzenich-Bezug |
| Polizei Düren, Website | dueren.polizei.nrw, Artikel und Teilnahmebedingungen, 09:15 Uhr | HTTP 503 mit Bot-Prüfung, nicht umgangen |
| Feuerwehr | Website und Heimat-Info-Profil, 06:41 und 09:19 Uhr | weiter bis 140/26 bzw. 147/26 |
| Heimat-Info | Gemeindeseite 06:41 Uhr; Veranstaltungskalender und zehn Detailseiten 09:19 Uhr | nur Beiträge der LAG (Eschweiler) und des Heimatvereins (Modellbahn); Kalender unverändert |
| Ratsinformationssystem | `termine.ics` 09:19 Uhr | unverändert |

## 5. Sport

Samstag: Tabellenstand laut ChatGPT verändert (SC Merzenich Vierter); Spielstand nicht geändert, Aktualisierung mit der Pflichtprüfung am Dienstag.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde um 09:19 Uhr gelesen (10 Einträge, dieselben IDs, alle Detailseiten unverändert gegenüber 13.11.) und Ratsinfo-`termine.ics` (unverändert). Übrige Termine bis 27.11. am 12. und 13.11. an ihrer Quelle bestätigt.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Pluriversum 17.11., Seniorentreffs 18./25.11. und 02.12., Senioren-Nachmittag 18.11., Gottesdienst 22.11., Adventsliedersingen 29.11. | je Slug | bestätigt | Heimat-Info | 2026-11-14 |
| HFA 19.11., Bürgewald-Ausschuss 26.11., Rat und Jugendgremium 03.12. | je Slug | bestätigt | Ratsinfo `termine.ics` | 2026-11-14 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `45ee6ad4` (Meldung, Bildklasse), `83461b73` (Kalenderpflege; Rebase-Konflikt nur in `docs/SHA256SUMS.txt`, Stand von main, Kette neu), Dokumentation folgt. Abgleich 09:18 Uhr `angestossen: true`, 09:20 Uhr `angestossen: false`; wp-cron HTTP 200.

Live-Nachweis 09:20 Uhr: Meldung HTTP 200, in der Nachrichten-Sitemap, in `wordpress-delivery/merzenich-aktuell-import.xml` auf GitHub (2 Treffer), oben in „Neu in Merzenich“.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| weihnachtsmalwettbewerb-polizei-dueren-2026-kinder-merzenich-mitmachen-14-dezember | leben.polizei.aktion (neu) | polizeimotorrad | Münster, Lambertikirchplatz, Motorrad -- 2019 -- 3594.jpg | CC BY-SA 4.0 | selbst angesehen: Polizeimotorrad auf einem Platz, Kennzeichenfeld leer, Passanten im Hintergrund nicht erkennbar; Symbolbild für eine Aktion der Polizei, kein Foto der Aktion |

Neue Bildklasse `leben.polizei.aktion` (Motive Streifenwagen, Polizeimotorrad; für Streifenwagen liegt kein Poolfoto vor). Commons-Vorschlag „Colored Pencils.jpg“ nicht importiert (Commons-API bis zuletzt HTTP 429).

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Malwettbewerb | 261 | 2 | 3 Einträge, alle mit offizieller Quelle; einer zusätzlich Selbstauskunft (Abrufversuch) | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Mindesttakt 14.11.: zweite Meldung im Nachmittagslauf.
- Bauausschuss 12.11.: Ergebnisse abwarten.
- Sport: Ergebnisse vom 15.11. und Tabelle am Dienstag.
- HFA 19.11.: Empfehlungen nach der Sitzung.
- Werkzeugpunkte (Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
