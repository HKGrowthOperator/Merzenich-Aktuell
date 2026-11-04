# Merzenich Aktuell – Nachmittagslauf (Claude), 4. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „14:43“ in der festen Redaktionssitzung, Start 14:44 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 04.11. morgens](2026-11-04-claude-morgens.md), seither neu [ChatGPT 04.11. mittags](2026-11-04-mittags.md). main vor dem Lauf: `a76575a`. Eil-Prüfungen 10:40 und 12:40 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 2 | Tiefbau (Standard, 830 Wörter, `relevanz` 2); zweiter Solarpark bei Golzheim (Kurzmeldung, 365 Wörter, `relevanz` 2) |
| Korrigierte Bestandsmeldungen | 2 | Energiemanagement und Solarpark an der A4: „acht Gebäude“ → acht Anlagen auf kommunalen Liegenschaften (Hinweis ChatGPT) |
| Neue Termine (live / Entwurf) | 2 / 0 | DRK-Infoabend Krisenvorsorge 10.11., Pluriversum-Workshop 18.11. |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | Tiefbau 10 Quellen, 9 Aussagen; Solarpark Golzheim 4 Quellen (amtlich), 4 Aussagen | |
| Bilder geprüft / neu integriert | 2 / 0 | aus bestehenden Pools (Baustelle, Photovoltaik) |
| Werkzeug und Plugin | Plugin 1.48.2, `qa/redaktion-lauf.mjs` | Meldungsdatum in der Zukunft: Plugin setzt beim Anlegen auf jetzt, schaltet hängende KI-Meldungen frei; QA meldet es als Fehler |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 1 Hinweis | |
| Mindesttakt 04.11. | 4 Meldungen | Energiemanagement 09:24, Golzheim-Schule 09:45, Tiefbau 14:50, Solarpark Golzheim 14:55 |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| Korrektur MA-EMS-SACHSTAND-2026 | alt: „hat die Gemeinde 2024 auf acht kommunalen Gebäuden, vor allem Kitas, Solaranlagen mit Batteriespeichern gebaut“; neu: „hat die Gemeinde 2024 acht neue Solaranlagen mit Batteriespeichern auf kommunalen Liegenschaften gebaut, vor allem auf Kitas“ | `inhalte/meldungen/2026-11-04-recherche-claude-morgens.json` | Energiesteckbrief S. 2; Commit `8ccc212` |
| Korrektur MA-SOLARPARK-A4-E3 | alt: „auf acht eigenen Gebäuden, vor allem Kitas, Solaranlagen mit zusammen rund 70 Kilowatt Spitzenleistung und Batteriespeichern montiert“; neu: „acht neue Solaranlagen mit zusammen rund 70 Kilowatt Spitzenleistung und Batteriespeichern auf kommunalen Liegenschaften montiert, vor allem auf Kitas“ | `inhalte/meldungen/2026-11-03-recherche-claude-nachmittags.json` | wie oben; `8ccc212` |
| MA-TIEFBAU-M140-2026 (neu) | „Baustelle vor dem Rathaus in Merzenich dauert länger: Kabel im Hauptkanal, Versicherung schickt Gutachter“ (Standard) | `inhalte/meldungen/2026-11-04-recherche-claude-nachmittags.json` → `/rathaus/baustelle-valdersweg-rathaus-merzenich-telekomschaden-bahnstrasse-foerderung-tiefbau-2026/` | M140/2026 vollständig (3 Seiten), Tagesordnung 12.11., WZV Neffeltal (Versorgungsgebiet, Verband, Baumaßnahmen), WVER, Förderrichtlinie Straßenausbaubeiträge (MB.NRW 2025 Nr. 109), NRW-Infrastrukturgesetz mit Anlage, MHKBD-Erläuterungen; Commit `118106a` |
| MA-PV-GOLZHEIM-E1-FNP20 | „Zweiter Solarpark in Merzenich: 15 Megawatt auf früherer Kiesgrube bei Golzheim, Bauausschuss berät Offenlage“ (Kurzmeldung) | gleiches Paket → `/rathaus/solarpark-golzheim-eschweiler-ueber-feld-merzenich-15-mwp-kiesgrube-bauausschuss-2026/` | Vorlage 92/2026 (2 Seiten), Entwurfsbegründung (Kapitel 1, 2, Ver- und Entsorgung), Tagesordnung, Kurzbegründung A4; Commit `1c65652` |
| Kalender | DRK-Infoabend 10.11. (`d6ae335`), Pluriversum-Workshop 18.11. (`51fab98`) | `inhalte/termine/2026-11-04-nachmittags-claude.json` | Abschnitt 6 |

Offener Punkt aus dem Morgendossier geklärt: Die Vorlage M140/2026 schreibt „Wasserverband Eifel-Rur (WZV)“. WZV ist die Abkürzung des Wasserleitungszweckverbands der Neffeltalgemeinden, der laut eigener Übersicht Girbelsrath und Golzheim versorgt; der Wasserverband Eifel-Rur heißt WVER und ist für die Gewässer zuständig. Im Text als Unklarheit der Vorlage benannt, kein Verband als Bauherr behauptet.

Eigener Fund: Anlage zum NRW-Infrastrukturgesetz 2025 bis 2036 weist Merzenich 4.799.525,90 Euro pauschal zu (10.508 Einwohner).

Freigabe: keine. MA-PV-GOLZHEIM-E1-FNP20 war bei ChatGPT T2 (Datumswiderspruch, keine unabhängige Bestätigung). Umgesetzt als Kurzmeldung mit amtlicher Quelle (SCHREIBREGELN Abschnitt 0); Widerspruch im Text benannt, Einspeisezusage nur als Angabe der Begründung, Vorhabenträger als nicht genannt.

Top-Meldung: keine neue Stufe 1 (Mitteilung und Vorlage, keine Beschlüsse).

## 2. Sofort sichtbar

Neue Meldungen und Termine gehen beim nächsten Abgleich ohne Freigabe online; Korrekturen der beiden Energieartikel als Update. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Jugendgremium 04.11., 17 Uhr | Sitzung nach diesem Lauf; Ergebnis im nächsten Lauf |
| Vorgeschichte Glasfaserausbau (SOCO, Kooperationsvertrag) | Amtsblatt-Archiv der ULB Bonn antwortet mit HTTP 403, nicht umgangen |
| FöRi-kom-Stra, aktuelle Fassung | nicht gelesen; im Text nur als Programmname aus der Vorlage |
| Wochenvorschau 9.–15.11. | besser am Freitag oder Sonntag vor der Woche |

## 4. Quellen dieses Laufs

Pflichtquellen nach ChatGPT 11:57 bis 12:00 Uhr und eigener Eil-Prüfung 12:40 Uhr (Polizei: Vettweiß, Jülich, Langerwehe, kein Merzenich-Fall; Feuerwehr bis 144/26; Heimat-Info: DRK-Ankündigung 10:34 Uhr). Zusätzlich gelesen: Heimat-Info-Beitrag 6b90fdb6 (DRK), Vorlage 92/2026 und Entwurfsbegründung, neffeltal.de (Versorgungsgebiet, Der Verband, Baumaßnahmen), wver.de, recht.nrw.de (MB.NRW 2025 Nr. 109, NRW-Infrastrukturgesetz und Anlage), MHKBD-Erläuterungen (Stand 30.03.2026), Kreis Düren (Mental Load 18.11.), Heimat-Info-Kalender 15:00 Uhr (6 Einträge, unverändert).

## 5. Sport

Keine Pflichtprüfung am Mittwoch.

## 6. Kalenderpflege

Fenster 04.–18.11.; Abgleich gegen die Liste aus `node qa/redaktion-lauf.mjs --kalender`.

**Neu (2):** Infoabend „Private Krisenvorsorge“ des DRK mit der Gemeinde, 10.11., 18:30 Uhr, Bürgerhaus (Heimat-Info, Gemeinde, 04.11.); „Pluriversum“-Workshop 18.11., 10–12 Uhr, Gesamtschule (Amtsblatt 11, PDF-Seite 6; bisher nur im Text des Abendtermins).

**Geändert:** keiner. **Abgesagt:** keiner.

**Nachgeprüft:** Heimat-Info-Kalender 15:00 Uhr (sechs Einträge, alle im Kalender); Online-Vortrag „Mental Load“ 18.11., 18–19:30 Uhr (Kreis Düren); Senioren-Nachmittag 18.11., 15–17 Uhr (Amtsblatt 11, keine neue Ausgabe laut ChatGPT 11:57). Übrige Termine 04.–17.11. im Morgenlauf nachgeprüft (Dossier 04.11. morgens).

## 7. QA, Commit, Abgleich

Commits: `8ccc212` (Korrekturen), `d6ae335` (DRK), `118106a` (Tiefbau), `1c65652` (Solarpark Golzheim, Uhrzeiten, Plugin 1.48.2), `51fab98` (Workshop), dieser Dossier-Commit.

Befund: Die Tiefbau-Meldung war zunächst mit 15:35 Uhr datiert und um etwa 14:55 Uhr gepusht; mit einem Datum in der Zukunft hätte WordPress sie wie die Termine am Morgen nur eingeplant. Korrigiert auf 14:50 Uhr; Plugin 1.48.2 und Prüfung in `qa/redaktion-lauf.mjs` verhindern das künftig.

Abgleich-Anstoß 15:02 Uhr: `angestossen: true`; Plugin 1.48.2 per Selbst-Update vor 15:05 Uhr live. Live-Nachweis 15:09/15:10 Uhr: Tiefbau, Solarpark Golzheim, DRK-Infoabend und Pluriversum-Workshop HTTP 200; beide neuen Meldungen in der Nachrichten-Sitemap und oben in „Neu in Merzenich“ (Platz 1 und 2); Korrekturen „acht neue Solaranlagen“ in Energiemanagement und Solarpark A4 live.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| baustelle-valdersweg-rathaus-merzenich-telekomschaden-bahnstrasse-foerderung-tiefbau-2026 | verkehr.baustelle | baustelle | 2020091301 Baustellenabsperrung Dohnaischer Platz 2020.jpg | CC BY-SA 4.0 | selbst angesehen: abgesperrte Straßenbaustelle mit Bagger, keine Personen; Symbolbild, nicht der Valdersweg |
| solarpark-golzheim-eschweiler-ueber-feld-merzenich-15-mwp-kiesgrube-bauausschuss-2026 | leben.energie.solar | photovoltaik-freiflaeche | Dülmen, Solarpark -- 2014 -- 7060.jpg | CC BY-SA 4.0 | selbst angesehen (morgens): Freiflächenmodule; Symbolbild, nicht das Golzheimer Projekt |

## 8a. Prüfung und Eigenformulierung

`node qa/eigenformulierung.mjs --datum 2026-11-04 --lauf nachmittags`: 0 Fehler, 1 Hinweis (8 Wörter gleich mit den MHKBD-Erläuterungen: der Eigenname „Sondervermögen Infrastruktur und Klimaneutralität des Bundes“). Eine Folge von 10 Wörtern zur Mittelverteilung umformuliert. Prüfprotokoll: jede Zahl mit Eintrag; Verbandsangaben mit `selbstauskunft`.

## 9. Offene Punkte für den nächsten Lauf

- Jugendgremium 04.11.: Ergebnis.
- Bauausschuss 12.11.: Folgemeldungen (Golzheim-Schule, Solarparks, Energiemanagement, Tiefbau, Poolplatz).
- Wochenvorschau 9.–15.11.
- Jonge-vom-Berg-Termine Januar 2027 mit Orten; Bürgersprechstunde Golzheim.
