# Merzenich Aktuell – Morgenlauf (Claude), 11. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung 09:15 bis 09:30 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 10.11. nachmittags](2026-11-10-claude-nachmittags.md), seither neu [ChatGPT 11.11. morgens](2026-11-11-morgens.md). main vor dem Lauf: `546f75a4`. Eil-Prüfungen 10.11. 16:40, 18:40, 20:40 Uhr und 11.11. 06:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Standardartikel Abgaben 2027 (820 Wörter im Fließtext, Rathaus, `relevanz` 2) |
| Korrigierte Bestandsmeldungen | 0 | ChatGPT 11.11. morgens ohne Korrekturauftrag |
| Neue / geänderte Termine | 0 / 0 | |
| Termine an der Quelle bestätigt | 15 | Heimat-Info (10 Einträge, alle Detailseiten) und Ratsinfo `termine.ics` (5 Sitzungen) |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | 9 Quellen (drei Vorlagen, zwei Anlagen, Satzung, Bund der Steuerzahler, Tagesordnung, eigene Meldung), 6 Aussagen |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Rathaus Merzenich (Pool `aktuell`) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-ABGABEN-2027 | „Merzenich 2027: Abwasser teurer, Hundesteuer steigt, Grundsteuer B soll auf 1.077 Punkte“ (Standard): Abwasser 3,33 €/m³ und 0,61 €/m², Musterfamilie plus 71,90 € (673,40 auf 745,30 €), Ursachen Verbandsumlagen (+10 %) und Vorjahresergebnisse, Klärschlamm 50,68 €/m³; Hundesteuer in drei Schritten (ein Hund 54 → 59/64/69 €), Dauermarke, zwei statt vier Fälligkeiten, 14 statt 4 Listenrassen; Hebesätze 2027 A 683, B 1.077, Gewerbe 559; HFA 19.11., Rat 03.12. | `inhalte/meldungen/2026-11-11-recherche-claude-morgens.json` → `/rathaus/merzenich-abgaben-2027-abwassergebuehren-hundesteuer-grundsteuer-b-1077-vorschlag-verwaltung/` | Vorlagen 141/2026 mit Anlage 2 (Kalkulation), 131/2026 mit Satzungsentwurf, 101/2026, geltende Hundesteuersatzung, Grundsteuer-B-Erhebung 2026 des Bundes der Steuerzahler NRW, HFA-Tagesordnung (alle 09:16 bis 09:20 Uhr gelesen, Zahlen um 09:19 Uhr an den PDF-Texten nachgeprüft); Commit `f9804cb4` |

Quellenausnahmen: ChatGPT führte die Karte als T2 (unabhängige Bestätigung der Kalkulationen fehlt). Umgesetzt, weil die Vorlagen offizielle Quellen sind (SCHREIBREGELN Abschnitt 0) und der Text sie durchgehend als Vorschläge der Verwaltung kennzeichnet, nicht als Beschlüsse. Unabhängig ist nur der Vergleichswert Grundsteuer B 2026 (Bund der Steuerzahler). Nicht im Text, weil nicht belegt: alte Hebesätze für Grundsteuer A und Gewerbesteuer, der bisherige Satz für Kleinkläranlagen, Beträge für einzelne Häuser. `relevanz` 2 statt 1, weil noch kein Ratsbeschluss vorliegt (SCHREIBREGELN 0a: „im Zweifel 2“).

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| MA-ALTE-KIRCHE-ENTGELTE-2027, MA-UNTERKUNFTSGEBUEHREN-2027, MA-BRANDSCHUTZPLAN-2026-FORTSCHREIBUNG, MA-DIGITAL-IKZ-2026 (alle T2) | Zeit des Laufs für den Abgabenartikel genutzt; Unterkünfte, Brandschutzplan und Übernachtungssteuer sind im Artikel als weitere HFA-Punkte genannt. Eigene Meldungen in einem späteren Lauf vor dem 19.11. |
| Wertung Girbelsrath – Morschenich | weiter offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 11.11. morgens, zuerst KORREKTUREN | keine Korrekturen; fünf T2-Karten aus dem Ratsinformationssystem |
| Ratsinformationssystem | Sitzungsseite HFA 19.11., Vorlagen 101, 131, 141/2026 mit Anlagen, 09:16 bis 09:20 Uhr; `termine.ics` 09:22 Uhr | siehe Abschnitt 1; Sitzungsliste unverändert |
| Bund der Steuerzahler NRW | Grundsteuer-B-Erhebung 2026 (PDF), gefunden über eine Websuche | Merzenich 2026: 988, 2025: 788 |
| Polizei Düren | Dienststellenliste nr/8, 06:41 und 09:25 Uhr | neueste Meldungen 10.11. (Düren, Aldenhoven); nichts mit Merzenich |
| Feuerwehr | feuerwehr-merzenich.de/einsaetze 09:25 Uhr | weiter bis 140/26 |
| Heimat-Info | Gemeindeseite 09:25 Uhr; Veranstaltungskalender und zehn Detailseiten 09:22 Uhr | keine neuen Beiträge seit 10.11. 13:11 Uhr; Kalender unverändert |
| Amtsblatt | Ausgabe 12 (`/bindata/amtsblatt-2026/Merzenich_12_2026-web.pdf`) | HTTP 404, noch nicht erschienen |
| KG Jonge vom Berg | Startseite 09:23 Uhr | keine Angaben zu Orten der Sessionstermine |

Sperren und Umwege: gemeinde-merzenich.de (Website) weiter HTTP 428 für Seiten; PDFs unter `/bindata/` erreichbar.

## 5. Sport

Mittwoch, keine Pflichtprüfung. Spielstand unverändert seit 10.11. (nächstes Spiel JSV Frenz – SC Merzenich, 15.11., 15 Uhr).

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender`. Heimat-Info-Veranstaltungskalender der Gemeinde um 09:22 Uhr gelesen (10 Einträge, alle Detailseiten mit Datum, Zeit und Ort abgeglichen, unverändert gegenüber 10.11.) und Ratsinfo-`termine.ics` (5 Sitzungen, unverändert). Übrige Termine bis 25.11. am 08.11. und 10.11. an ihrer Quelle bestätigt ([Dossier 08.11.](2026-11-08-claude-morgens.md), [Dossier 10.11.](2026-11-10-claude-morgens.md), je Abschnitt 6).

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Frühstückstreff und Skatrunde 11.11., 18.11.; Skatrunde 25.11.; Senioren-Nachmittag 18.11. | je Slug | bestätigt | Heimat-Info | 2026-11-11 |
| Martinszug Golzheim, 11.11. | martinszug-golzheim-2026-11-11 | bestätigt (17:30 Uhr Wortgottesdienst, 18 Uhr Umzug ab Pfarrkirche) | Heimat-Info 791f374e | 2026-11-11 |
| Gottesdienste 15.11. und 22.11. | je Slug | bestätigt | Heimat-Info | 2026-11-11 |
| Pluriversum, 17.11. | pluriversum-gesamtschule-merzenich-2026-11-17 | bestätigt (Ort Aula der Gesamtschule Niederzier); Zeitfeld 18:15 Uhr gegenüber 18 Uhr im Amtsblatt weiter offen | Heimat-Info 671697c5 | 2026-11-11 |
| Bauausschuss 12.11., HFA 19.11., Bürgewald-Ausschuss 26.11., Gemeinderat und Jugendgremium 03.12. | je Slug | bestätigt | Ratsinfo `termine.ics` | 2026-11-11 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner. Offene Hinweise „nachpruefen“: 0.

## 7. QA, Commit, Abgleich

Commits: `f9804cb4` (Meldung), `a00f852d` (Kalenderpflege; Rebase-Konflikt nur in `docs/SHA256SUMS.txt`, Stand von main, Kette neu), Dokumentation folgt. Abgleich 09:21 Uhr `angestossen: true`, 09:24 Uhr `angestossen: false` (Lauf schon angestoßen); wp-cron HTTP 200.

Live-Nachweis 09:24 Uhr: Meldung HTTP 200, in der Nachrichten-Sitemap, in `wordpress-delivery/merzenich-aktuell-import.xml` auf GitHub (2 Treffer) und oben in „Neu in Merzenich“ (vor der Bauausschuss-Vorschau).

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| merzenich-abgaben-2027-abwassergebuehren-hundesteuer-grundsteuer-b-1077-vorschlag-verwaltung | rathaus.rat | rathaus-merzenich | Merzenich Rathaus.JPG | Public domain | selbst angesehen: Rathaus Merzenich von der Straße mit Bäumen und Laterne, Sitz von Ausschuss und Rat; keine Personen, keine Kennzeichen, keine Hausnummer |

Neu importierte Fotos: keine.

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll (offiziell / doppelt / Selbstauskunft) | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Abgaben 2027 | 820 | 9 | 6 / 1 / 2 (BdSt-Erhebung, eigenes Archiv) | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- HFA 19.11.: eigene Meldungen zu Unterkunftsgebühren, Brandschutzbedarfsplan und Übernachtungssteuer; nach der Sitzung Empfehlungen zu den Abgaben nachtragen, nach dem Rat am 03.12. die Beschlüsse.
- Bauausschuss 12.11. (morgen): Folgemeldungen am 13.11.
- Martinszug Merzenich 12.11. und Girbelsrath 13.11.: Amtsblatt 12 prüfen, sobald erschienen.
- Wertung Girbelsrath – Morschenich; Papiersammlung ab 2027; Jugendgremium; Orte der KG-Jonge-vom-Berg-Termine; Pluriversum 18 oder 18.15 Uhr.
- Werkzeugpunkte (Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
