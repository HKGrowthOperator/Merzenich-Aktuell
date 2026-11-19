# Merzenich Aktuell – Morgenlauf (Claude), 19. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung 09:14 bis 09:30 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 18.11. nachmittags](2026-11-18-claude-nachmittags.md), seither neu [ChatGPT 19.11. morgens](2026-11-19-morgens.md). main vor dem Lauf: `9b59ec96`. Eil-Prüfungen 18.11. 16:41, 18:41, 20:41 Uhr und 19.11. 06:42 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 2 | Haushaltsprognose 2026 (323 Wörter), Umsatzsteuer der Gemeinde ab 2027 (273 Wörter) |
| Korrigierte Bestandsmeldungen | 0 | ChatGPT: kein neuer Fehler; Malwettbewerb-Bildtext weiter Werkzeugpunkt |
| Neue / geänderte Termine | 2 / 1 | neu: Frühstückstreff und Skatrunde 9.12.; geändert: HFA 19.11. (zwei Berichte ergänzt) |
| Termine an der Quelle bestätigt | 20 | alle Termine 19.11. (ab 10 Uhr) bis 03.12. |
| Bilder gefunden / geprüft / integriert | 2 / 2 / 2 | vorhandene Rathausfotos der Bildklasse `rathaus.rat` |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler (Ortsbezug geprüft); `qa/eigenformulierung.mjs --datum 2026-11-19 --lauf morgens` 0 Fehler, 0 Hinweise | |

Mindesttakt 19.11.: 2 Meldungen im Morgenlauf.

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-HAUSHALT-M148-2026 (ChatGPT-Karte) | Kurzmeldung „Haushalt Merzenich 2026: Verwaltung erwartet 3,4 statt 5,7 Millionen Euro Defizit“ (Rathaus, Relevanz 2, 323 Wörter): Prognose 3.420.933 Euro Fehlbetrag statt geplanter 5.686.372 Euro, vorläufig (Abschlussbuchungen fehlen), Stand 12.11. Minus 2.973.955 Euro, größte Posten Rekultivierung Bürgewald, Verkauf alter Sportplatz; **Widerspruch benannt**: Finanzbericht nennt als Ansatz 6.835.327 Euro Minus, Unterschied unerklärt; Einordnung Haushaltssicherung und Entwurf 2027 am 3.12. | `inhalte/meldungen/2026-11-19-recherche-claude-morgens.json` → `/rathaus/merzenich-haushalt-2026-prognose-defizit-3-4-millionen-statt-5-7-millionen-finanzausschuss/` | Mitteilung M148/2026 (1 Seite), Anmerkungen zum Finanzbericht (1 Seite), Finanzbericht S. 1 von 355 (Gesamtergebnisrechnung), gelesen 09:15 Uhr; Zeitplan Haushalt 2027 und Haushaltssicherung aus eigenem Artikel vom 13.10. (M128/2026); vier Prüfeinträge mit offizieller Quelle; Commit `ab6f7723` |
| MA-UMSATZSTEUER-M149-2026 (ChatGPT-Karte) | Kurzmeldung „Umsatzsteuer für die Gemeinde Merzenich ab 2027: Grababräumung, Gemeindemobil und Spülmobil betroffen“ (Rathaus, Relevanz 3, 273 Wörter): § 2b UStG ab 1.1.2027 verpflichtend, Liste steuerpflichtiger Einnahmen (Stammbücher, Gemeindemobil, Spülmobil, Fahrradboxen, Stellplätze, Solar-Einspeisung, Veranstaltungen, Werbung Bürgerbus, Sporthallen, Lehrschwimmbecken 7 Prozent), Wohnungsmieten steuerfrei, Friedhofsgebühren Anfang 2027, keine Aussage zu Preisen | dieselbe Datei → `/rathaus/umsatzsteuer-gemeinde-merzenich-ab-2027-grababraeumung-gemeindemobil-spuelmobil-friedhofsgebuehren/` | Mitteilung M149/2026 (2 Seiten) und Anlage (1 Seite), 09:15 Uhr; drei Prüfeinträge mit offizieller Quelle; Gesetzestext (gesetze-im-internet.de) nicht selbst gelesen und deshalb nicht als Beleg geführt; Commit `ab6f7723` |
| Kalender | zwei neue Termine 9.12., HFA ergänzt, 20 bestätigt | `inhalte/termine/2026-11-19-recherche-claude-morgens.json`, `2026-oktober-november.json` u. a. | Abschnitt 6; Commit `ae253e38` |

ChatGPT stufte beide Karten als „T2 – gesperrt“ ein (nur ein Herausgeber, Planwertkonflikt). Nach CLAUDE-LAUF 3.3 trägt eine offizielle Quelle eine Kurzmeldung; der Planwertkonflikt ist im Text als offene Frage benannt, nicht aufgelöst. Keine Gebührenerhöhung und keine Entwarnung behauptet.

Ortsbezug (SCHREIBREGELN 0b): beide Meldungen betreffen Haushalt und Leistungen der Gemeinde Merzenich; Feld `ortsbezug` gesetzt und von `qa/redaktion-lauf.mjs` geprüft.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Beide Meldungen ohne Freigabe (kein rechtliches Risiko). Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Ergebnisse HFA 19.11. | Sitzung heute 18 Uhr; Folgemeldungen ab dem nächsten Lauf nach Niederschrift oder Beschlusstexten |
| Vergleich mit dem Vorbericht M113/2026 | Original im Ratsinformationssystem über die Suche nicht erreichbar (ChatGPT); im Text nur die Aussage der Verwaltung „keine wesentlichen Veränderungen“ |
| Malwettbewerb-Bildbeschreibung | Werkzeugpunkt, Entscheidung Luis Kummer offen |
| Polizei, Kreis Düren | keine neue Meldung mit Ortsbezug seit 18.11. 10:45 Uhr (Kreis: Familientag Nideggen) |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 19.11. morgens, zuerst KORREKTUREN | keine neue Korrektur; Bau-Turbo-Korrektur live bestätigt; zwei Karten umgesetzt |
| Ratsinformationssystem | M148/2026 mit zwei Anlagen, M149/2026 mit Anlage, 09:15 Uhr; `termine.ics` 09:20 Uhr | Abschnitt 1; Kalender unverändert |
| Polizei Düren | Dienststellenliste nr/8, 06:42 und 09:20 Uhr | nichts Neues |
| Feuerwehr | Website und Heimat-Info-Profil, 09:20 Uhr | weiter bis 147/26 |
| Heimat-Info | Gemeindeseite, Veranstaltungskalender und zwölf Detailseiten, 09:20 bis 09:21 Uhr | zwei neue Termine 9.12. |
| Kreis Düren | Presse-Index 09:20 Uhr | nichts mit Ortsbezug |

## 5. Sport

Donnerstag, kein Pflichttag. Freitagsprüfung: Girbelsrath bei Viktoria Arnoldsweiler II, Fr. 20.11., 19.30 Uhr; Wochenendspiele.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 09:20 Uhr gelesen: zehn IDs; abgelaufen und aus der Liste: Senioren-Nachmittag und Frühstückstreff 18.11. (`43009e41`, `9e5fb188`), Skatrunde 18.11. (`a8c9e12b`); neu im Listenfenster: Senioren-Adventsfeier 9.12. (`65540fac`, schon im Kalender) sowie **Frühstückstreff** (`c66dc70a`) und **Skatrunde** (`de9ceabc`) am 9.12., beide mit Ort Ev. Gemeindehaus, Severin-Böhr-Straße. Die übrigen Detailseiten inhaltlich unverändert; Heimat-Info hat die Seitenvorlage geändert (Cookie-Hinweis, Ortsname im Seitentitel), deshalb Textvergleich nur im Veranstaltungsteil. `termine.ics` unverändert. Amtsblätter 08, 11, 12, Kulturkalender 16 und Schützen Morschenich bytegleich mit dem 18.11.; Ausstellungs- und Wunschbaum-Beitrag inhaltlich unverändert.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Frühstücks- und Spieletreff 9.12. | fruehstuecks-und-spieletreff-fuer-senior-innen-merzenich-2026-12-09 | **neu** (gleichzeitiger Eintrag, von der Termin-Automatik nicht übernommen) | Heimat-Info `c66dc70a` | 2026-11-19 |
| Männergruppe und Skatrunde 9.12. | maennergruppe-skatrunde-fuer-senioren-merzenich-2026-12-09 | **neu** | Heimat-Info `de9ceabc` | 2026-11-19 |
| Haupt- und Finanzausschuss 19.11. | haupt-finanzausschuss-sitzung-2026-11-19 | ergänzt: Berichte zum Haushalt 2026 und zur Umsatzsteuer | Ratsinformationssystem | 2026-11-19 |
| übrige 19 Termine vom 19.11. (ab 10 Uhr) bis 03.12. | je Slug | bestätigt | jeweilige Quelle | 2026-11-19 |

**Neu:** 2. **Geändert:** 1. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `ab6f7723` (Meldungen), `ae253e38` (Kalender), Dokumentation in diesem Commit. Abgleich 09:19 Uhr `angestossen: true`; nächster planmäßiger Abgleich 10:05 Uhr.

Live-Nachweis 09:23 Uhr: beide Meldungen HTTP 200 mit Text (unter anderem „6.835.327“, „Steinmetzbetriebe“) und Rathausfotos, beide in der Nachrichten-Sitemap, unter „Neu in Merzenich“ auf der Startseite und in `wordpress-delivery/merzenich-aktuell-import.xml` auf GitHub. Neue Termine 09:27 Uhr live: `/termine/fruehstuecks-und-spieletreff-fuer-senior-innen-merzenich-2026-12-09/` und `/termine/maennergruppe-skatrunde-fuer-senioren-merzenich-2026-12-09/` HTTP 200.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| merzenich-haushalt-2026-prognose-defizit-3-4-millionen-statt-5-7-millionen-finanzausschuss | rathaus.rat | rathaus-merzenich | Merzenich Rathaus HDR.jpg | CC BY-SA 3.0 | selbst angesehen (09:19 Uhr): Rathaus Merzenich von vorn mit Schriftzug „RATHAUS“, Winter; zeigt den Sitz von Verwaltung und Ausschuss, der über den Haushalt berät; keine Personen, keine Kennzeichen |
| umsatzsteuer-gemeinde-merzenich-ab-2027-grababraeumung-gemeindemobil-spuelmobil-friedhofsgebuehren | rathaus.rat | rathaus-merzenich | Merzenich Rathaus.JPG | Public domain | selbst angesehen (09:19 Uhr): Rathaus Merzenich von der Seite mit Bäumen, Sommer; Symbolbild der Gemeindeverwaltung, deren Leistungen steuerpflichtig werden; keine Personen, keine Kennzeichen |

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Haushalt 2026 | 323 | 5 | 4 Einträge, alle Ratsinformationssystem | 0 Wörter |
| Umsatzsteuer ab 2027 | 273 | 3 | 3 Einträge, alle Ratsinformationssystem | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- HFA 19.11.: Ergebnisse als Folgemeldungen, sobald Beschlusstexte oder Niederschrift vorliegen (Abgaben, Übernachtungssteuer, Neuland, Kita, Brandschutzbedarfsplan, Trauungen, Shared Service Center).
- Haushalt: Unterschied der Planwerte (5.686.372 gegenüber 6.835.327 Euro) bei Gelegenheit klären.
- Girbelsrath Fr. 20.11. (Freitagsprüfung Sport).
- Bauausschuss 12.11.: Niederschrift; Gemeinderat 3.12.: Tagesordnung (Haushaltsentwurf 2027).
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
