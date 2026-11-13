# Merzenich Aktuell – Nachmittagslauf (Claude), 13. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „14:43“, in der festen Redaktionssitzung erst um 15:17 Uhr angekommen; Bearbeitung 15:18 bis 15:26 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 13.11. morgens](2026-11-13-claude-morgens.md), seither neu [ChatGPT 13.11. mittags](2026-11-13-mittags.md). main vor dem Lauf: `e2064820`. Eil-Prüfungen 10:41 und 12:41 Uhr ohne Fund; die Eil-Prüfung 14:40 Uhr ist nicht eingetroffen, die Pflichtquellen deckt dieser Lauf ab.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Standard Wochenvorschau 16. bis 22. November (807 Wörter im Fließtext, Leben, `relevanz` 3) |
| Korrigierte Bestandsmeldungen | 0 | ChatGPT 13.11. mittags ohne Korrekturauftrag; Trauentgelt-Artikel dort ohne Abweichung |
| Neue / geänderte Termine | 0 / 0 | Heimat-Info und Ratsinfo unverändert seit morgens |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | 15 Quellen, 6 Aussagen |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Veranstaltungssaal (Pool `kultur`) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise (zwei 8-Wort-Folgen umformuliert) | |
| Mindesttakt 13.11. | 2 Meldungen | Trauentgelte 09:17, Wochenvorschau 15:20 Uhr |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-WOCHENVORSCHAU-2026-47 (neu) | „Merzenich vom 16. bis 22. November: Pluriversum, Haupt- und Finanzausschuss und Tag der Modelleisenbahn“ (Standard): Pluriversum 17.11. ab 18 Uhr in Niederzier (Gemeinde bestätigt am 13.11.; Kalendereinträge weiter 18.15 Uhr), Workshop 18.11.; Seniorentreffs 18.11. (Widerspruch beim Senioren-Nachmittag benannt); Mental Load online; Sprechstunde Betreuungsbehörde 19.11. (neuer Raum); HFA 19.11. mit Zahlen aus den Einzelartikeln und neu der Übernachtungssteuer (Vorlage 104/2026: zwei Alternativen, ein Hotel, zwei Gästehäuser, eine Monteurunterkunft, keine Empfehlung der Verwaltung); Modelleisenbahn 21.11.; Gottesdienst Totensonntag 22.11. | `inhalte/meldungen/2026-11-13-recherche-claude-nachmittags.json` → `/leben/merzenich-16-bis-22-november-2026-pluriversum-haupt-finanzausschuss-modelleisenbahn/` | Gemeindebeitrag Pluriversum 13.11. (09:42 Uhr, gelesen 12:41 Uhr), HFA-Tagesordnung und Vorlage 104/2026 (15:22 Uhr), Heimat-Info-Detailseiten (09:19 und 15:23 Uhr unverändert), Kulturkalender, Amtsblätter 08 und 11, Kreis Düren; Commit `03ccda03` |

Quellenausnahmen: keine. Die Wochenvorschau bündelt Termine und verlinkt die Einzelartikel, keine Dublette.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Bauausschuss 12.11., Ergebnisse | um 15:23 Uhr weiter keine Beschlüsse oder Niederschrift im Ratsinformationssystem |
| MA-POLIZEI-MALWETTBEWERB-2026 (T2, neu) | kreisweite Polizeiaktion, Teilnahmebedingungen nicht lesbar (Behördenseite HTTP 503 laut ChatGPT); Einsendeschluss 14.12., in einem späteren Lauf prüfen |
| MA-DIGITAL-IKZ-2026 (Shared Service Center) | in der Wochenvorschau als HFA-Punkt genannt; eigene Meldung bei Bedarf nach der Sitzung |
| MA-FUSSBALL-GIRB-SWD-2911 | laut ChatGPT jetzt beide Spielpläne mit SW Düren II als Gastgeber; Chronikangabe weiter widersprüchlich, T2 |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 13.11. mittags, zuerst KORREKTUREN | keine Korrekturen; neue Karte Malwettbewerb (T2) |
| Ratsinformationssystem | HFA-Tagesordnung, Vorlage 104/2026, Sitzungsseite Bauausschuss, `termine.ics`, 15:22 bis 15:23 Uhr | siehe Abschnitt 1 und 3 |
| Heimat-Info | Gemeindeseite 12:41 Uhr (Pluriversum-Beitrag 09:42 Uhr); Veranstaltungskalender und zehn Detailseiten 15:23 Uhr | Kalender unverändert |
| Polizei Düren | Dienststellenliste nr/8, 10:41 und 12:41 Uhr | 13.11.: Düren (zwei Unfälle), Vettweiß, kreisweit Malwettbewerb; nichts mit Merzenich |
| Feuerwehr | Website und Heimat-Info-Profil, 12:41 Uhr | weiter bis 140/26 bzw. 147/26 |

## 5. Sport

Spielstand unverändert; nächstes Spiel JSV Frenz – SC Merzenich, Sonntag 15.11., 15 Uhr.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender um 15:23 Uhr erneut gelesen (dieselben 10 Einträge, Detailseiten unverändert gegenüber 09:19 Uhr), Ratsinfo-`termine.ics` unverändert. Termine bis 26.11. am 12. und 13.11. an ihrer Quelle bestätigt ([Dossier morgens](2026-11-13-claude-morgens.md), Abschnitt 6). Pluriversum 17.11.: neuer Gemeindebeitrag vom 13.11. bestätigt 18 Uhr und Niederzier wie im Bestand.

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commit `03ccda03` (Meldung), Dokumentation folgt. Abgleich 15:23 Uhr `angestossen: true`, wp-cron HTTP 200.

Live-Nachweis 15:23 Uhr: Meldung HTTP 200, in der Nachrichten-Sitemap, in `wordpress-delivery/merzenich-aktuell-import.xml` auf GitHub (2 Treffer), oben in „Neu in Merzenich“.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| merzenich-16-bis-22-november-2026-pluriversum-haupt-finanzausschuss-modelleisenbahn | leben.veranstaltung | veranstaltungssaal | Blick vom Techniker-Raum über die Sesselreihen … zur Bühne bei Aufräum-Beleuchtung | laut Pool | selbst angesehen: leerer Saal mit Stuhlreihen, Bühne und Leinwand; keine Personen (eine Büste als Dekoration), keine Kennzeichen; Symbolbild für eine Woche mit Abendveranstaltung, Vortrag und Sitzung |

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Wochenvorschau 16.–22.11. | 807 | 15 | 6 Einträge, davon 5 mit offizieller Quelle; einer mit Veranstalterangaben und eigenem Archiv | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Bauausschuss 12.11.: Ergebnisse abwarten, dann Folgemeldungen.
- Sport Dienstag 17.11.: Ergebnisse vom 15.11.
- HFA 19.11.: Empfehlungen nach der Sitzung als Folgemeldungen.
- Zeitkonflikte Weckmannschießen, Senioren-Nachmittag, Krätzchensitzung; Orte der KG-Jonge-vom-Berg-Termine.
- Werkzeugpunkte (Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
