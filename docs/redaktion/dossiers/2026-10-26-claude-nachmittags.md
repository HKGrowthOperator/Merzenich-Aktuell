# Merzenich Aktuell – Nachmittagslauf (Claude), 26. Oktober 2026

Arbeitsdossier nach `docs/redaktion/README.md` und `docs/redaktion/CLAUDE-LAUF.md`. Dieser Lauf hat **umgesetzt**: Inhalte liegen als JSON im Repository, die Kette ist gelaufen, der Stand ist auf `main` gepusht (Commit `8041a59`). Die neuen Meldungen erscheinen über den stündlichen Abgleich als **Entwurf in den WordPress-Freigaben**; veröffentlicht wird ausschließlich dort von der Redaktion. Zeitangaben: Europe/Berlin (MEZ). Die Containeruhr der Sitzung kann gegenüber der tatsächlichen Uhrzeit nachgehen; das Recherchefenster lag am Nachmittag des 26.10.2026 nach dem Routinestart um 16:31 Uhr.

**Vergleichsbasis:** [Morgendossier 26.10.](2026-10-26-morgens.md) (ChatGPT, 25 Karten, keine Umsetzung) und [Claude-Nachlauf 25.10.](2026-10-25-nachlauf-claude.md) (Commit `7e268d6`). Gelesener main-Stand vor dem Lauf: `94c07a4`.

---

## 0. Kurzfassung in Zahlen (tatsächlich erreicht)

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 4 | 3 Standard (333, 342, 313 Wörter), 1 Kurzmeldung (134 Wörter); je 2 bis 4 Quellen |
| Aktualisierte Bestandsmeldungen | 2 | Verkehr (Nachtrag S 19), Laub (Zweitquelle) |
| Neue Termine | 2 | KI-Roadshow 06.11., Rosenkranz im Pfarrheim ab 04.11. |
| Umkreis-Einträge | 6 | 5 Polizei (Niederzier, Titz, 2× Jülich, Vettweiß/Düren), 1 Wirtschaft |
| Sportstand | unverändert | kein neues Herren-Ergebnis seit 25.10.; SC III Endstand weiter offen |
| Bilder | 0 gefunden, 0 geprüft, 0 integriert | in diesem Lauf keine Bildrecherche (siehe 6.) |
| Kette / QA | 2 Läufe, `--check` unverändert, 0 Fehler, 32 Hinweise | Hinweise betreffen bekannte Symbolbild-Pools |

Nicht erreicht: 15 bis 25 Entwürfe. Die belegte Nachrichtenlage eines Sonntagnachmittags in den Herbstferien trägt das nicht; Bestandsartikel werden nicht als neue Entwürfe gezählt.

## 1. Was umgesetzt wurde (mit Morgendossier abgeglichen)

| ID (Register) | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-ELW1-116-2026 | Standard-Artikel „Neuer Einsatzleitwagen für die Feuerwehr: Rat gibt bis zu 257.000 Euro frei“ (rathaus, Bildklasse rathaus.feuerwehr) | `inhalte/meldungen/2026-10-26-recherche-claude-nachmittags.json` → `/rathaus/einsatzleitwagen-feuerwehr-merzenich-ratsbeschluss-257000-euro-2026/` | Beschlusstext 116/2026 (öffentliche Niederschrift 30.09., TOP 12, einstimmig) **und** Beschlussvorlage 116/2026 vom 16.09. als PDF gelesen; Kostenlimit 257.039 Euro brutto, Bezug Brandschutzbedarfsplan 190/2019 und Fortschreibung 40/2026, AG 05.02.2026, Infoabend 09.09.2026. EU-Schwellenwert 216.000 Euro netto ab 01.01.2026 nachgerechnet (×1,19 = 257.040). Vergabe ausdrücklich als offen beschrieben. |
| MA-PFARRLEITUNG-31012027 | Standard-Artikel „Pfarrer Andreas Galbierz gibt die Leitung der Pfarrei St. Laurentius zum 31. Januar 2027 ab“ (leben, kirche.gottesdienst) | dito → `/leben/pfarrer-galbierz-legt-leitung-st-laurentius-merzenich-zum-31-januar-2027-nieder/` | Brief S. 2 des Pfarrbriefs 08/26 gelesen; Rahmenkonzept des Bistums Aachen vom 15.05.2025 (44 Pastorale Räume, ein Pfarrer je Raum) als zweite Quelle. Die Leitungsübernahme durch Pfarrer Gerd Kraus steht im Artikel als **geplant** (Wortlaut des Briefs); eine amtliche Ernennung des Bistums ist nicht belegt und wird nicht behauptet. Amts- und Funktionsträger sind genannt, keine Privatpersonen. |
| MA-ROSENKRANZ-PFARRHEIM-0411 | Standard-Artikel „Allerheiligen, Rosenkranz im Pfarrheim, Sonntagscafé: Was der Pfarrbrief für den November ankündigt“ (leben) plus Termin `rosenkranzgebet-pfarrheim-merzenich-ab-2026-11-04` | dito → `/leben/allerheiligen-rosenkranz-pfarrheim-sonntagscafe-selbsthilfe-pfarrbrief-november-2026/`; `inhalte/termine/2026-10-26-recherche-claude-nachmittags.json` | Pfarrbrief S. 11, 31 und 33 gelesen: Gräbersegnungen 31.10./01.11., Ortswechsel Rosenkranz ab 04.11. ins Pfarrheim Schulstraße 4a, Sonntagscafé 01.11., Caritas-Gruppen „Café Auszeit“ und „Men Power“. Der bestehende Allerheiligen-Termin (01.11.) ist verlinkt, nicht dupliziert. Novemberausgabe des Pfarrbriefs lag nicht vor; Gültigkeit 08/26 bis 02.11. |
| MA-1025-25 | Nachtrag im Verkehrsartikel vom 25.10.: Dek neu, Absatz „Nachtrag vom 26. Oktober“, zusätzlicher Fakt, Quelle ergänzt | `inhalte/meldungen/2026-10-25-recherche-claude.json` → `/leben/verkehr-ferienwoche-bahn-dueren-koeln-bismarckstrasse-a4-2026-10-26/` | Plakat zuginfo.NRW/VRS FPLO 30754 (PDF `Zuginfo_Ausfaelle_Koeln_Aachen_26bis28102026.pdf`) gelesen: S 19 Düren–Köln Hansaring 26.10. 21 Uhr bis 28.10. 5 Uhr jeweils 21 bis 5 Uhr mit SEV; zusätzlich RE 9, RB 20, RB 38, RE 1. Buszeiten und Einstiegsstelle in Merzenich nennt das Plakat nicht; das steht so im Artikel. |
| MA-LAUB-2610 | Quellenkorrektur: Heimat-Info-Beitrag der Gemeinde vom 07.10.2026 als Zweitquelle ergänzt, Text unverändert | `inhalte/meldungen/2026-09-26-bis-10-03-redaktionsupdate.json` → `herbstlaub-2026-abgabe-bauhof-girbelsrath` | Gemeindeseite weiter hinter der kdvz-JavaScript-Prüfung; der offizielle Heimat-Info-Kanal trägt dieselbe Mitteilung. |
| KD-KI-ROADSHOW-0611 (neu) | Kurzmeldung „KI-Roadshow der IHK macht am 6. November in Düren Station“ (wirtschaft, wirtschaft.weiterbildung) plus Termin `ki-roadshow-ihk-dueren-2026-11-06` plus Umkreis-Eintrag | dito → `/wirtschaft/ki-roadshow-ihk-aachen-dueren-6-november-2026/` | Kreis-Mitteilung vom 26.10. und IHK-Veranstaltungsseite (Termine Alsdorf 09.10., Euskirchen 27.10., Düren 06.11., Heinsberg 16.11.; Inhalte, Anmeldung) gelesen. |

**Bewusst nicht umgesetzt**

- **KD-CROWD-2610** (Düren-Crowd, Frist 26.10.): Die Frist läuft am Tag des Laufs ab. Ein Entwurf, der erst nach Abgleich und redaktioneller Freigabe erscheinen kann, käme nach Fristende; kein Artikel, Status gesperrt (vorbei).
- **MA-HGAS-OKT** (H-Gas-Umstellung): Betreiberangaben widersprüchlich (Fließtext gegen Ortstabelle). Keine Glättung, bleibt T2 bis zur Klärung beim Netzbetreiber.
- **MA-MARTINSZUEGE-2026**: Dublette zum Amtsblatt-Artikel vom 16.10., unverändert gesperrt.
- Weitere Karten des Morgendossiers (Bürgewald, Nahverkehrsplan, Krätzchen, Adventfenster, Reibekuchen-Nachbericht, FBL3, KGS Golzheim, SC III) bleiben in ihrem bisherigen Status; kein neuer Beleg an diesem Nachmittag.

## 2. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| Ratsinformationssystem Merzenich | `ratsinfo.gemeinde-merzenich.de/vorlagen`, sdnetrim-PDFs (Beschlusstext und Vorlage 116/2026) | gelesen; Vorlagenliste ohne neue Vorlagen seit dem Morgen |
| Pfarrgemeinden Merzenich/Niederzier | Pfarrbrief 08/26 (36 Seiten, PDF), Website, st-laurentius-merzenich.de | S. 2, 11, 31, 33 gelesen; keine Novemberausgabe online |
| Bistum Aachen | Rahmenkonzept „Leitung und Mitverantwortung in den Pastoralen Räumen“ (PDF 15.05.2025) | gelesen; keine Personalmeldung zu Merzenich/Niederzier gefunden |
| Kreis Düren Presse | `kreis-dueren.de/presse/` Index und Mitteilung KI-Roadshow 26.10. | eine neue Mitteilung mit Bezug zum Kreis |
| IHK Aachen | Veranstaltungsseite KI-Roadshow | gelesen |
| Polizei Düren | presseportal.de nr/8, Einzelmeldungen 6377641 bis 6377797 | fünf Meldungen vom 26.10., keine mit Tatort Merzenich |
| VRS / zuginfo.NRW | PDF-Plakat FPLO 30754 | gelesen (Nachtrag Verkehr) |
| Heimat-Info | Beitrag der Gemeinde vom 07.10. (Laub); Liste | keine neue Gemeinde-Mitteilung seit 25.10. |
| Feuerwehr Merzenich | Rohdaten `imports/quellen/feuerwehr.json` (Abruf 15:10 UTC) | kein neuer Einsatz nach 143/26 |
| FUSSBALL.DE / fupa | nicht erneut abgerufen | Sonntagsspiele am 25.10. bereits bestätigt; am 26.10. keine Herrenspiele |

Gesperrt wie an den Vortagen: gemeinde-merzenich.de (JavaScript-Prüfung, außer PDFs und Stellenseite), kgs-golzheim, schuetzen-golzheim.de (403), Wikimedia Commons (429 am 25.10.; heute nicht erneut versucht).

## 3. Sport

Keine Änderung an `chatgpt-site/api/sport-current.json`. Stand bleibt: SC Merzenich 8:0 in Hambach (10. Spieltag, Platz 4, 24 Punkte), nächstes Spiel 08.11., 15 Uhr, gegen FC Düren 77. Offen: Endstand SC Merzenich III in Gereonsweiler (Spielseite ohne Klartext).

## 4. Termine (14 Tage, Veränderungen)

Neu: 04.11. 18 Uhr Rosenkranzgebet im Pfarrheim Merzenich (dann wöchentlich mittwochs); 06.11. 9 bis 11 Uhr KI-Roadshow IHK, CO_SPACE.DN Düren. Unverändert im Bestand: 29.10. Wildpferdeexkursion, 30.10. Bürgersprechstunde Golzheim, 31.10. AndroCup TTC, 31.10./01.11. Allerheiligen-Gottesdienste, 03.11. Abgabeschluss Amtsblatt 12, 05.11. Persönliches Budget, 06.11. Eichel-Sammelaktion, 12.11. Bauausschuss, 18.11. Mental Load, 03.12. Gemeinderat.

## 5. QA, Kette und Push

- `node deploy/kette.mjs` zweimal: 578 Seiten, WP-Theme 18 Vorlagen, WordPress-Import 215 Meldungen, 66 Termine, 89 Bilder; zweiter Lauf 0 Dateien geschrieben.
- `node deploy/kette.mjs --check`: alle Generatoren unverändert.
- `node qa/pruefung.mjs`: 0 Fehler, 32 Hinweise (Symbolbild-Pools, bekannt, siehe `docs/SYMBOLBILDER-ANFORDERUNG.md`).
- Commit `8041a59` auf `main` (fetch vorher, kein Force). Keine Workflow-, Scraper- oder Theme-Logik verändert.
- Abgleich: öffentlicher Anstoß `?ma_api=abgleich-anstoss` und `wp-cron.php` nach dem Push aufgerufen (Ergebnis im Abschlussbericht). Eine Sichtprüfung der Freigaben im Backend war nicht möglich, weil der WordPress-Benutzername zur Redaktionsadresse weiter fehlt; E-Mail-Login ist deaktiviert, Raten ist wegen Sperrrisiko ausgeschlossen.

## 6. Bilder

Keine Bildrecherche in diesem Lauf. Alle vier Meldungen tragen bestehende Bildklassen (`rathaus.feuerwehr`, `kirche.gottesdienst` ×2, `wirtschaft.weiterbildung`), die Zuordnung übernimmt die Pipeline. Fotos aus Pfarrbrief, Ratsunterlagen oder von Kreis/IHK sind ohne schriftliche Freigabe gesperrt. Die vier ungeprüften Commons-Kandidaten vom 25.10. und die sieben Kandidaten des Morgendossiers bleiben offen; empfohlen bleibt ein Importlauf über GitHub Actions, weil die Commons-API von der Sitzungs-IP gedrosselt wird.

## 7. Offene Punkte für den nächsten Lauf (27.10. morgens)

1. Freigaben-Status: Sind die Entwürfe vom 25./26.10. in WordPress angekommen (Benutzername nötig)?
2. SC Merzenich III: Endstand Gereonsweiler.
3. Niederschrift Strukturausschuss 15.10., Tagesordnung Jugendgremium 04.11., GPA/RPA 05.11.
4. SEV-Buszeiten und Einstiegsstelle Merzenich (Fahrplanauskunft/VRS) für den Verkehrsartikel.
5. Bistum Aachen: amtliche Ernennung der Leitung des Pastoralen Raums (Nachtrag zum Pfarrleitungs-Artikel).
6. H-Gas, Bürgewald-Masterplan, Krätzchen-Ortsfrage (T2).
7. Montag: Feuerwehr- und Polizei-Rohdaten 05:10 UTC, Heimat-Info, Kreis-Presse, Amtsblatt-Index (Ausgabe 12 am 13.11.).
