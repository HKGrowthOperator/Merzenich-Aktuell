# Merzenich Aktuell – Nachmittagslauf (Claude), 12. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung 14:44 bis 14:56 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 12.11. morgens](2026-11-12-claude-morgens.md), seither neu [ChatGPT 12.11. mittags](2026-11-12-mittags.md). main vor dem Lauf: `acbc7acc`. Eil-Prüfungen 10:46 und 12:41 Uhr ohne Fund; Termine 25.11. und 02.12. um 10:46 Uhr live (HTTP 200).

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Ersthelfer-Ehrung (276 Wörter im Fließtext, Leben, `relevanz` 3) |
| Korrigierte Bestandsmeldungen | 0 | ChatGPT 12.11. mittags ohne Korrekturauftrag; Unterkunftsgebühren dort ohne Abweichung |
| Neue / geänderte Termine | 7 / 0 | aus dem 16. Kulturkalender der Gemeinde |
| Termine an der Quelle bestätigt | 3 | Modelleisenbahn 21.11., Adventmarkt 28.11., Frühschoppen KG Golzheim 10.01. (Kulturkalender) |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | Kurzmeldung mit offizieller Quelle (Kreis Düren), Projektseite und Zweckverband, 3 Aussagen |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Innenraum eines Rettungswagens (Pool `blaulicht`), neue Bildklasse `leben.ersthilfe` |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise (zwei 10- und 11-Wort-Folgen aus der Kreismitteilung umformuliert) | |
| Mindesttakt 12.11. | 2 Meldungen | Unterkunftsgebühren 09:16, Ersthelfer 14:46 Uhr |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-CORHELPER-EHRUNG-2026 | „Ersthelfer aus Merzenich ausgezeichnet: Kreis Düren ehrt Einsatz für Wiederbelebung und Defibrillatoren“ (Kurzmeldung): Ehrung durch den Landrat für den Kreis Düren, sechs Geehrte aus der Region, Corhelper der ersten Stunde, Pflege der Defibrillatoren; „Region Aachen rettet“ seit fünf Jahren, mehr als 1.500 Helfer im Kreis, 10.500 in der Region, mehr als 7.000 Einsätze; Aufruf zu Kurs und Anmeldung | `inhalte/meldungen/2026-11-12-recherche-claude-nachmittags.json` → `/leben/ersthelfer-merzenich-corhelper-ausgezeichnet-kreis-dueren-region-aachen-rettet-2026/` | Pressemitteilung Kreis Düren vom 11.11. (gelesen 14:47 Uhr), Projektseite regionaachenrettet.de (14:49 Uhr); Commit `3684e92f` |

Quellenausnahmen: ChatGPT führte die Karte als T2 (Kreis, Zweckverband und Projekt nicht voneinander unabhängig). Umgesetzt, weil die Kreismitteilung eine offizielle Quelle ist und eine Kurzmeldung trägt (SCHREIBREGELN Abschnitt 0). Der Kreis nennt den Geehrten mit Namen; als Privatperson nicht übernommen (SCHREIBREGELN Abschnitt 7). Das Datum der Feierstunde steht nicht in der Kreismitteilung, die Seite des Zweckverbands war ohne Text (Skript-Seite); daher im Text nur „Mitteilung vom 11. November“.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung und sieben Termine ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Glückwunsch der Gemeinde zu einer Trauung am 11.11. (Heimat-Info 10:36 Uhr) | Namen von Privatpersonen; kein Nachrichtenwert über die Namen hinaus |
| „25 Jahre ZeLE“ (LAG Rheinisches Revier, 30.11.) | Veranstaltung außerhalb der Gemeinde, Ort nicht geprüft |
| Commons-Foto „AED open cutout.jpg“ (Vorschlag ChatGPT) | Commons-API um 14:48 Uhr HTTP 429 (Ratenbegrenzung); nicht umgangen, Bestandsfoto Rettungswagen genutzt |
| Trauentgelte Alte Kirche, Digitalisierung | weiter offen, vor dem 19.11. |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 12.11. mittags, zuerst KORREKTUREN | keine Korrekturen; Hinweis auf den 16. Kulturkalender; Zeitkonflikte Weckmannschießen und Pluriversum |
| Kreis Düren | Pressemitteilung Corhelper | siehe Abschnitt 1 |
| Gemeinde | 16. Kulturkalender (PDF, erstellt 03.11.), 14:52 Uhr | siehe Abschnitt 6 |
| Polizei Düren | Dienststellenliste nr/8, 12:41 Uhr | 12.11. 11:32 Uhr Niederzier (Unfall Ellener Straße), im Volltext kein Merzenich-Bezug |
| Feuerwehr | Website und Heimat-Info-Profil, 12:41 Uhr | weiter bis 140/26 bzw. 147/26 |
| Heimat-Info | Gemeindeseite 12:41 Uhr | Trauungsglückwunsch, LAG-Veranstaltungstipp (Abschnitt 3) |

## 5. Sport

Donnerstag, keine Pflichtprüfung.

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender`. Neu ausgewertet: 16. Kulturkalender der Gemeinde (November 2026 bis März 2027), alle Termine bis 60 Tage mit dem Bestand verglichen. Heimat-Info und Ratsinfo im Morgenlauf vollständig geprüft, mittags laut ChatGPT unverändert.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Inklusiver Spielenachmittag, 14.11., 15 Uhr, Bürgerhaus | ism-inklusiver-spielenachmittag-buergerhaus-merzenich-2026-11-14 | **neu** | Kulturkalender S. 2 | 2026-11-12 |
| Tannenbaumschmücken Lindenplatz, 27.11., 17 Uhr (SC Merzenich) | tannenbaumschmuecken-lindenplatz-sc-merzenich-2026-11-27 | **neu** | Kulturkalender | 2026-11-12 |
| Adventfeier Schützenbruderschaft, 27.11., 19 Uhr, Schützenheim | adventfeier-schuetzenbruderschaft-merzenich-2026-11-27 | **neu** | Kulturkalender | 2026-11-12 |
| Weinbergschnecken „Schneewittchens Geburtstag“, 28.11., 15 Uhr, Weinberghalle | weinbergschnecken-schneewittchens-geburtstag-weinberghalle-2026-11-28 | **neu** | Kulturkalender S. 1 | 2026-11-12 |
| Golzheimer Adventskalender, 28.11., 18 Uhr, Weihnachtswiese Buirer Straße | golzheimer-adventskalender-eroeffnung-weihnachtswiese-2026-11-28 | **neu** | Kulturkalender | 2026-11-12 |
| ISM-Weihnachtsfeier, 06.12., 14 Uhr, Bürgerhaus (Anmeldung) | ism-weihnachtsfeier-buergerhaus-merzenich-2026-12-06 | **neu** | Kulturkalender | 2026-11-12 |
| Weihnachtsfenster der Gemeinde, 15.12., 18 Uhr, Rathaus | weihnachtsfenster-lebendiger-adventskalender-rathaus-merzenich-2026-12-15 | **neu** | Kulturkalender | 2026-11-12 |
| Modelleisenbahn 21.11., Adventmarkt 28./29.11., Frühschoppen KG Golzheim 10.01. | je Slug | bestätigt | Kulturkalender | 2026-11-12 |

Widersprüche (nicht geändert, offen): Weckmannschießen 15.11. – Kulturkalender „ab 11 Uhr“ im Schützenheim, Vereinsseite der KG Jonge vom Berg 11:30 Uhr; Pluriversum 17.11. – Kulturkalender und Heimat-Info 18:15 Uhr, Gemeindeseite 18 Uhr; Krätzchensitzung 23.01.2027 – Kulturkalender (erstellt 03.11.) 15 Uhr, jüngere Gemeindemitteilung (11.11.) 14 bis 18.30 Uhr, Bestand 14 Uhr. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `3684e92f` (Meldung, Bildklasse), `6549b383` (Kalender; Rebase-Konflikt nur in `docs/SHA256SUMS.txt`, ein Push-Versuch mit HTTP 403 beim Abruf, beim nächsten Versuch durchgegangen), Dokumentation folgt. Abgleich 14:50 Uhr `angestossen: true`, 14:52 Uhr `angestossen: false`; wp-cron HTTP 200.

Live-Nachweis 14:52 Uhr: Meldung HTTP 200, in der Nachrichten-Sitemap, oben in „Neu in Merzenich“ (vor den Unterkunftsgebühren); Import-XML auf GitHub enthält Meldung und neue Termine (3 Treffer). Termine nach dem nächsten Abgleich im nächsten Lauf nachweisen.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| ersthelfer-merzenich-corhelper-ausgezeichnet-kreis-dueren-region-aachen-rettet-2026 | leben.ersthilfe (neu) | rettungswagen | Ambulance in a town of North Rhine-Westphalia inside (2024).jpg | CC0 | selbst angesehen: geöffneter Rettungswagen mit Trage und Geräten, keine Personen, keine Kennzeichen; Symbolbild für die Rettungskette, in die Corhelper parallel zum Rettungsdienst eingebunden sind |

Neu importierte Fotos: keine. Neue Bildklasse `leben.ersthilfe` in `deploy/bildklassen.json` (Motiv `rettungswagen`).

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Ersthelfer-Ehrung | 276 | 3 | 3 Einträge: zwei aus der Kreismitteilung (offiziell), einer Projektseite (Selbstauskunft) | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Bauausschuss heute Abend: Folgemeldungen am 13.11. (Schule Golzheim, Solarparks, Bau-Turbo).
- Neue Termine aus dem Kulturkalender live nachweisen.
- Zeitkonflikte Weckmannschießen, Pluriversum, Krätzchensitzung beim Veranstalter klären.
- KG Jonge vom Berg: Orte der Sessionstermine von der wieder erreichbaren Vereinsseite.
- HFA 19.11.: Trauentgelte, Digitalisierung, Übernachtungssteuer.
- Werkzeugpunkte (Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
