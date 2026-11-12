# Merzenich Aktuell – Morgenlauf (Claude), 12. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung 09:14 bis 09:25 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 11.11. nachmittags](2026-11-11-claude-nachmittags.md), seither neu [ChatGPT 12.11. morgens](2026-11-12-morgens.md). main vor dem Lauf: `f9fd9d07`. Eil-Prüfungen 11.11. 16:41, 18:45, 20:41 Uhr und 12.11. 06:42 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Unterkunftsgebühren (324 Wörter im Fließtext, Rathaus, `relevanz` 3) |
| Korrigierte Bestandsmeldungen | 0 | ChatGPT 12.11. morgens ohne Korrekturauftrag; Brandschutz- und Abgabenartikel dort ohne Abweichung |
| Neue / geänderte Termine | 2 / 0 | Frühstücks- und Spieletreff 25.11. und 02.12. |
| Termine an der Quelle bestätigt | 20 | Heimat-Info, Ratsinfo, TTC, Schützen Morschenich, KG Jonge vom Berg (Seite wieder erreichbar), Kreis Düren |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | Kurzmeldung, 5 Quellen (Vorlage, Kalkulation, Tagesordnung, zwei eigene Meldungen), 3 Aussagen |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Rathaus Merzenich |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-UNTERKUNFTSGEBUEHREN-2027 | „Merzenich will Gebühren für Unterkünfte von Geflüchteten und Obdachlosen neu festsetzen“ (Kurzmeldung): rund 230 Geflüchtete, Häuser in Bürgewald von RWE ins Gemeindeeigentum, Umzug der Bewohner mittel- bis langfristig; zwei Sätze ab 2027 (195,72 € Häuser/Wohnungen, 290,66 € Container je Person und Monat, Strom 39,50/36,66 € extra), 80 % Auslastung, bei Vollbelegung 156,29/236,16 €; Kosten Häuser/Wohnungen rund 741.000 €, Landeszuweisung rund 229.000 €; bisherige Sätze in der Vorlage nicht genannt | `inhalte/meldungen/2026-11-12-recherche-claude-morgens.json` → `/rathaus/merzenich-gebuehren-unterkuenfte-gefluechtete-obdachlose-2027-neukalkulation-vorschlag/` | Vorlage 145/2026 (11.11. 14:47 Uhr gelesen, 12.11. unverändert) und Kalkulationsübersicht ANL_1 (12.11. 09:14 Uhr); Commit `de7b8308` |

Quellenausnahmen: ChatGPT führte die Karte als T2 (unabhängige Bestätigung der Kalkulation fehlt). Umgesetzt, weil Vorlage und Kalkulation offizielle Quellen sind und eine offizielle Quelle eine Kurzmeldung trägt (SCHREIBREGELN Abschnitt 0); als Vorschlag gekennzeichnet. Die Vorlage nennt eine Adresse mit Hausnummer (Objekt in Reparatur); nicht übernommen. Bisherige Gebührensätze: Websuche nach der geltenden Satzung ohne Merzenicher Treffer, Gemeindewebsite HTTP 428; im Text als offen benannt.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung und zwei Termine ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| MA-CORHELPER-EHRUNG-2026 (T2, neu) | Kreismitteilung vom 11.11. ist offizielle Quelle; für den Nachmittagslauf vorgesehen (Mindesttakt), keine Namen |
| MA-ALTE-KIRCHE-ENTGELTE-2027, MA-DIGITAL-IKZ-2026 | weiter offen, vor dem 19.11. |
| Bauausschuss 12.11. | tagt heute Abend; Folgemeldungen am 13.11. |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 12.11. morgens, zuerst KORREKTUREN | keine Korrekturen; neue Karte Corhelper-Ehrung (T2); Frühstückstreff 25.11. fehlte im Bestand |
| Ratsinformationssystem | Kalkulationsübersicht zu 145/2026, 09:14 Uhr; `termine.ics` 09:18 Uhr | siehe Abschnitt 1; Sitzungsliste unverändert |
| Polizei Düren | Dienststellenliste nr/8, 09:15 Uhr | neueste Meldungen 11.11. (Hürtgenwald, Niederzier); nichts mit Merzenich |
| Feuerwehr | feuerwehr-merzenich.de/einsaetze und Heimat-Info-Profil, 09:15 Uhr | weiter bis 140/26 bzw. 147/26 |
| Heimat-Info | Gemeindeseite 09:15 Uhr; Veranstaltungskalender und zehn Detailseiten 09:18 Uhr | keine neuen Beiträge seit 11.11. 16:02 Uhr; Kalender siehe Abschnitt 6 |
| Amtsblatt | Ausgabe 12 | HTTP 404, noch nicht erschienen |
| Websuche | Satzung Unterkünfte Merzenich | kein Merzenicher Treffer |

## 5. Sport

Donnerstag, keine Pflichtprüfung.

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender`. Heimat-Info-Veranstaltungskalender der Gemeinde um 09:18 Uhr gelesen (10 Einträge; drei neu im sichtbaren Fenster, drei vergangene entfallen; übrige Detailseiten unverändert, Senioren-Nachmittag nur neuer Stempel) und Ratsinfo-`termine.ics` (inhaltlich gleich). Übrige Termine bis 26.11. an ihrer Quelle nachgeprüft (09:19 bis 09:21 Uhr).

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Frühstücks- und Spieletreff, 25.11. | neu-fruehstuecks-und-spieletreff-fuer-senior-innen-merzenich-2026-11-25 | **neu** (Ort im Eintrag nicht genannt; Skatrunde zur selben Zeit im Gemeindehaus) | Heimat-Info 0b6866fc | 2026-11-12 |
| Frühstücks- und Spieletreff, 02.12. | neu-fruehstuecks-und-spieletreff-fuer-senior-innen-merzenich-2026-12-02 | **neu** (Ev. Gemeindehaus) | Heimat-Info 6b9b9f9d | 2026-11-12 |
| Adventsliedersingen, 29.11. | adventsliedersingen-merzenich-2026-11-29 | bestätigt (18 Uhr, Alte Kirche) | Heimat-Info 0bfa22f8 | 2026-11-12 |
| Volkstrauertag Merzenich, 15.11. | volkstrauertag-merzenich-2026 | bestätigt (9.30 bis 14 Uhr, Alte Kirche; Vereinsseite wieder erreichbar) | jonge-vom-berg.de | 2026-11-12 |
| TTC-Jugendvereinsmeisterschaften 13.11., Erste-Hilfe-Kurs 14.11., Gedenken Morschenich 15.11., Pluriversum-Workshop 18.11., Mental Load 18.11., Seniorenweihnacht 24.11. | je Slug | bestätigt | TTC-Kalender, Heimat-Info-Beitrag, Schützen Morschenich, Heimat-Info-Mitteilung der Gemeinde, Kreis Düren (2) | 2026-11-12 |
| Seniorentreffs 18./25.11., Gottesdienst 22.11., Pluriversum 17.11.; Bauausschuss 12.11., HFA 19.11., Bürgewald-Ausschuss 26.11., Rat und Jugendgremium 03.12. | je Slug | bestätigt | Heimat-Info, Ratsinfo `termine.ics` | 2026-11-12 |

Nicht erneut geprüft (statische Quelle Amtsblatt 11, am 08.11. gelesen): Martinszüge Merzenich 12.11. und Girbelsrath 13.11., Modelleisenbahn 21.11., Sprechstunde 19.11.; Taizé 15.11. (Pfarrbrief). **Abgesagt:** keiner. Offene Hinweise „nachpruefen“: 0.

## 7. QA, Commit, Abgleich

Commits: `de7b8308` (Meldung), `7920f42e` (Kalenderpflege; Rebase-Konflikt nur in `docs/SHA256SUMS.txt`, Stand von main, Kette neu), Dokumentation folgt. Abgleich 09:17 Uhr `angestossen: true`, 09:20 Uhr `angestossen: false` (Lauf schon angestoßen); wp-cron HTTP 200.

Live-Nachweis 09:20 Uhr: Meldung HTTP 200, in der Nachrichten-Sitemap, oben in „Neu in Merzenich“ (vor dem Brandschutzartikel); Import-XML auf GitHub enthält Meldung und Termine (3 Treffer). Termin 25.11. um 09:21 Uhr noch HTTP 404 (Import lief noch), Nachweis im nächsten Lauf.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| merzenich-gebuehren-unterkuenfte-gefluechtete-obdachlose-2027-neukalkulation-vorschlag | rathaus.rat | rathaus-merzenich | Merzenich Rathaus HDR.jpg | CC BY-SA 3.0 | selbst angesehen: Rathaus Merzenich mit Schriftzug „Rathaus“, Ort der Entscheidung; keine Personen, keine lesbaren Kennzeichen; kein Foto einer Unterkunft (bewusst) |

Neu importierte Fotos: keine.

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Unterkunftsgebühren | 324 | 5 | 3 Einträge, alle mit offizieller Quelle; einer zusätzlich mit eigenem Archiv | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Mindesttakt 12.11.: zweite Meldung im Nachmittagslauf (Kandidat Corhelper-Ehrung, Kreis Düren 11.11.).
- Termin 25.11. live nachweisen.
- Bauausschuss heute Abend: Folgemeldungen am 13.11.
- KG Jonge vom Berg: Vereinsseite wieder erreichbar, Orte der Sessionstermine nachtragen.
- HFA 19.11.: Trauentgelte, Digitalisierung, Übernachtungssteuer; danach Empfehlungen nachtragen.
- Wertung Girbelsrath – Morschenich; Papiersammlung ab 2027; Jugendgremium; Pluriversum 18 oder 18.15 Uhr.
- Werkzeugpunkte (Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine – heute erneut zwei fehlende Einträge –, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
