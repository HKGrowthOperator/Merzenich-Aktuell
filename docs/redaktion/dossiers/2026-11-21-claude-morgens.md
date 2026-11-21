# Merzenich Aktuell – Morgenlauf (Claude), 21. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung ab 09:14 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 20.11. nachmittags](2026-11-20-claude-nachmittags.md), seither neu [ChatGPT 21.11. morgens](2026-11-21-morgens.md) (Commit `cf6206cc`). main vor dem Lauf: `cf6206cc`. Eil-Prüfungen 16:41, 18:41, 20:41 (20.11.) und 06:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Wochenvorschau 23. bis 29. November (Standard, 818 Wörter) |
| Aktualisierte Bestandsmeldungen | 1 | Unwetterwarnung abgeschlossen (ChatGPT-Auftrag MA-DAUERREGEN-20112026) |
| Neue / geänderte Termine | 0 / 0 | neue Heimat-Info-Einladung zur Modelleisenbahn bestätigt den vorhandenen Termin |
| Termine an der Quelle bestätigt | 19 | alle Termine 21.11. bis 05.12. (Stand 2026-11-21) |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 0 | Bestandsbild der Klasse `leben.brauchtum.advent` geprüft (Abschnitt 8) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs --datum 2026-11-21 --lauf morgens` 0 Fehler (Ortsbezug geprüft); `qa/eigenformulierung.mjs --datum 2026-11-21 --lauf morgens` 0 Fehler, 0 Hinweise (nach Umformulierung zweier Stellen, Abschnitt 8a); `--datum 2026-11-20 --lauf morgens` 0 Fehler | |

Mindesttakt 21.11.: 1 Meldung im Morgenlauf; die zweite folgt bei belegbarer Nachricht im Nachmittagslauf.

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-DAUERREGEN-20112026 (ChatGPT morgens, Aktualisierungsbedarf nach Ablauf) | Titel „… DWD warnt bis Freitag 22 Uhr …“ → „… DWD warnte bis Freitag 22 Uhr …“; Dek neu: Unwetterwarnung galt bis Freitag 22 Uhr, Glätte- und Frostwarnung bis Samstag 9 Uhr, seither keine Warnung. Nachtrag „Aktualisiert am 21. November: …“ mit Ablauf beider Warnungen, DWD-Stand 9.09 Uhr ohne Warnung, Feuerwehr und Polizei bis 9.15 Uhr ohne Meldung zu Wetterfolgen. Relevanz 1 → 2 (Warnung abgelaufen, kein Aufmacher mehr). Frühere Nachträge erhalten | `inhalte/meldungen/2026-11-20-recherche-claude-morgens.json` → `/blaulicht/unwetterwarnung-merzenich-dauerregen-kreis-dueren-20-november-2026-dwd/` | `warnings.json` (Datenstand 09:09:30 Uhr) um 09:15 Uhr gelesen: kein Eintrag für Warnzelle Kreis Düren. Feuerwehr Website (bis 147/26) und Heimat-Info-Profil (bis 148/26), Polizei nr/8 (jüngste Meldung 20.11. 16:01 Uhr, Vorlesetag) um 09:15 Uhr. Keine Schadensfreiheit behauptet, nur der Stand der Veröffentlichungen. Die von ChatGPT um 06:57 Uhr gelesene „markante Glätte“ habe ich nicht selbst gesehen und deshalb nicht übernommen. 461 Wörter. Commit `ffcfee5e` |
| MA-WOCHENVORSCHAU-2026-48 (eigener Fund) | Standardmeldung „Merzenich vom 23. bis 29. November: Bürgewald-Ausschuss, Tannenbaumschmücken und Adventmarkt“, Leben, Relevanz 3, 818 Wörter | `inhalte/meldungen/2026-11-21-recherche-claude-morgens.json` → `/leben/merzenich-23-bis-29-november-2026-buergewald-ausschuss-tannenbaumschmuecken-adventmarkt/` | Alle Angaben am 21.11. an den Quellen gelesen: Kulturkalender 16 und Amtsblatt Nr. 12 (Text extrahiert, S. 13 bis 15), Heimat-Info-Einträge (Skatrunde, Frühstückstreff 25.11. und 2.12., Adventsliedersingen, Ausstellung), Sitzungsseite Bürgewald-Ausschuss mit Tagesordnung (09:21 Uhr), Terminseite Schützen Morschenich (09:20 Uhr), FUSSBALL.DE-Mannschaftsseiten SC 1919 Merzenich und FC Golzheim (09:23 Uhr). Widerspruch benannt: Nikolaus am Adventmarkt „etwa gegen 15.15 Uhr“ (Amtsblatt 12) gegen „bei Einbruch der Dunkelheit“ (Kulturkalender). Fehlender Ort beim Frühstückstreff 25.11. benannt. Keine Privatnamen (Küsterin ohne Namen), keine Hausnummern; Künstlerin und Künstler der Ausstellung als öffentlich Auftretende genannt. Commit `ad708019` |
| Kalender | 19 Termine bestätigt | 14 Dateien unter `inhalte/termine/` | Abschnitt 6; Commit `aac33b30` |

Ortsbezug (SCHREIBREGELN 0b): Alle Termine der Wochenvorschau finden in der Gemeinde statt (Ziffer 1); Feld `ortsbezug` gesetzt. Die Online-Seminare der DSEE (siehe Dossier 20.11. nachmittags) sind nicht enthalten.

Top-Meldung: keine neue Stufe 1. Die Unwetterwarnung ist auf Stufe 2 zurückgesetzt, weil die Warnung abgelaufen ist.

## 2. Sofort sichtbar

Neue Meldung und Aktualisierung ohne Freigabe, kein `zurFreigabe`. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Gemeinde-Pressemitteilung „Wir laden Sie ein zu unserem Adventsfenster!“ (neu in der Liste, Actions-Abruf 20.11. 22:18 Uhr) | Detailseite um 09:22 Uhr HTTP 428 (Bot-Prüfung, nicht umgangen), in den Rohdaten nur der Anrisstext. Der Termin (15.12., 18 Uhr, Rathaus) steht seit dem Kulturkalender im Bestand, ebenso im Advent-Überblick vom 15.11.; ohne lesbaren Text keine neuen Angaben |
| Wasserturm Modelleisenbahn, neue Einladung des Heimatvereins in Heimat-Info (20.11. 16:00 Uhr) | bestätigt Termin und Artikel vom Oktober (21.11., 13 bis 17 Uhr), keine Änderung; die Termin-Automatik erkennt den Eintrag über den Titelvergleich als vorhanden (`deploy/automatik-termine.mjs`, gleicher Tag, gleiche Wörter) |
| Ergebnis Girbelsrath bei Viktoria Arnoldsweiler II (20.11.) | FuPa-Spielplan zeigt nur kommende Spiele, Ergebnis nicht lesbar; Montagslauf |
| Ergebnisse HFA 19.11. | Sitzungsseite um 09:15 Uhr weiter ohne Niederschrift oder Beschlusstexte; Vorlagenliste unverändert (höchste Nummer 151/2026) |
| Malwettbewerb-Bildbeschreibung (MA-KORR-MALWETTBEWERB-BILDBESCHREIBUNG-1411) | Werkzeugpunkt (Alt-Text bestehender Poolanhänge im Abgleich), Entscheidung Luis Kummer offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 21.11. morgens, zuerst KORREKTUREN | Aktualisierung Unwetter umgesetzt; Ratstermin-Korrektur von ChatGPT live bestätigt; keine neue Themenkarte außer dem Wetter-Update |
| Deutscher Wetterdienst | `warnings.json` 09:15 Uhr (Datenstand 09:09 Uhr) | keine Warnung für den Kreis Düren |
| Feuerwehr | Website (bis 147/26) und Heimat-Info-Profil (bis 148/26) 09:15 Uhr | kein neuer Einsatz |
| Polizei Düren | Dienststellenliste nr/8 09:15 Uhr | jüngste Meldung 20.11. 16:01 Uhr (Vorlesetag in Mariaweiler, Lendersdorf, Gey), kein Ortsbezug |
| Kreis Düren | Presse-Index 09:15 Uhr | keine Antwort vom Server (wie bei ChatGPT um 06:58 Uhr HTTP 503) |
| Ratsinformationssystem | HFA-Seite, Vorlagenliste, `termine.ics` 09:15 Uhr; Bürgewald-Ausschuss 09:21 Uhr | Tagesordnung für die Wochenvorschau; sonst nichts Neues |
| Heimat-Info | Gemeindeseite 09:15 Uhr; Veranstaltungskalender und zehn Detailseiten 09:20 Uhr | eine neue Einladung (Modelleisenbahn), sonst unverändert |
| Gemeinde Merzenich | Rohdaten des Quellenabrufs (20.11. 22:18 Uhr): Pressemitteilungsliste; Adventsfenster-Seite 09:22 Uhr HTTP 428; Amtsblätter 11 und 12, Kulturkalender 16 unter `/bindata/` 09:20 Uhr | Abschnitt 3; PDFs bytegleich |
| Vereine und Sport | Schützen Morschenich 09:20 Uhr; FUSSBALL.DE SC und FC Golzheim 09:23 Uhr; FuPa Girbelsrath 09:17 Uhr | Wochenvorschau |

## 5. Sport

Samstag, kein Pflichttag. Girbelsrath spielte am Freitag um 19:30 Uhr in Arnoldsweiler; das Ergebnis war um 09:17 Uhr bei FuPa nicht lesbar und kommt in den Montagslauf. Am Sonntag, 29.11., spielen der FC Golzheim (14.30 Uhr gegen Borussia Freialdenhoven) und der SC 1919 Merzenich (15 Uhr gegen SV SW Huchem-Stammeln) zu Hause; beide Spiele stehen in der Wochenvorschau.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 09:20 Uhr gelesen: zehn Einträge auf der ersten Seite. Neu ist die Einladung „Wasserturm Modelleisenbahn“ (`96d65b21`, 21.11. 13 bis 17 Uhr), die den vorhandenen Termin bestätigt. Die Senioren-Adventsfeier am 9.12. (`65540fac`) ist dafür auf die zweite Kalenderseite gerückt; ihr Termin bleibt im Bestand (Werkzeugpunkt Seite 2). Die übrigen neun Detailseiten sind im Veranstaltungsteil textgleich mit dem 20.11. `termine.ics` ist bis auf den technischen Zeitstempel gleich. Amtsblätter 11 und 12 und Kulturkalender 16 sind bytegleich. Schützen Morschenich, Ausstellungs- und Wunschbaum-Beitrag sind textgleich.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Tag der Modelleisenbahn 21.11. | tag-der-modelleisenbahn-wasserturm-2026-11-21 | bestätigt, zusätzlich durch neue Heimat-Info-Einladung des Heimatvereins | Amtsblatt Nr. 11 | 2026-11-21 |
| übrige 18 Termine vom 21.11. bis 03.12. | je Slug | bestätigt | jeweilige Quelle | 2026-11-21 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `ffcfee5e` (Aktualisierung Unwetter), `aac33b30` (Kalender), `ad708019` (Wochenvorschau), Dokumentation in diesem Commit.

Abgleich: Anstoß um 09:28 Uhr `angestossen: false`; der Abgleich hat beide Änderungen bis 09:32 Uhr übernommen. Import-XML auf GitHub raw enthält den neuen Slug.

Live-Nachweis 09:32 Uhr, jeweils HTTP 200:
- `/leben/merzenich-23-bis-29-november-2026-buergewald-ausschuss-tannenbaumschmuecken-adventmarkt/` mit „etwa gegen 15.15 Uhr“ und „Adventsliedersingen“, og:image `veranstaltungen-10-5df0764e43` (Schloss Merode); Startseite „Neu in Merzenich“ oben (09:25 Uhr), `news-sitemap.xml` an erster Stelle.
- `/blaulicht/unwetterwarnung-merzenich-dauerregen-kreis-dueren-20-november-2026-dwd/` mit Titel „… DWD warnte bis Freitag 22 Uhr …“ und „Aktualisiert am 21. November“.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| merzenich-23-bis-29-november-2026-buergewald-ausschuss-tannenbaumschmuecken-adventmarkt | leben.brauchtum.advent | adventsmarkt | Schloss Merode Weihnachtsmarkt.jpg | CC BY-SA 4.0 | selbst angesehen 09:26 Uhr: Schloss Merode bei Nacht mit beleuchtetem Weihnachtsbaum und Marktständen, keine erkennbaren Personen, keine Kennzeichen; steht für die Adventszeit mit Tannenbaumschmücken und Adventmarkt. Bildunterschrift „Symbolbild … Kein Foto vom Ereignis“ |

Die Unwetter-Aktualisierung betrifft nur Text, Bild unverändert (Dossier 20.11. morgens, Abschnitt 8).

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Wochenvorschau 23. bis 29. November | 818 | 11 | 9 Einträge: Heimat-Info (zwei, Selbstauskunft der Veranstalter), RIS, Gemeindebeitrag Ausstellung, Kulturkalender und Amtsblatt (drei), FUSSBALL.DE, Kalenderpflege | 0 Wörter nach Umformulierung: erste Fassung hatte 13 Wörter wörtlich aus dem Amtsblatt (Golzheimer Krippenbauer) und 9 Wörter aus dem Ausstellungsbeitrag |
| Unwetterwarnung (Aktualisierung) | 461 | 6 | 6 Einträge, neu: DWD 9.09 Uhr ohne Warnung, Feuerwehr und Polizei 9.15 Uhr | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Mindesttakt 21.11.: zweite Meldung im Nachmittagslauf, wenn eine belegbare Nachricht vorliegt.
- Gemeinde-Pressemitteilung Adventsfenster: lesen, sobald erreichbar (Rohdaten des Quellenabrufs), mit Termin 15.12. abgleichen.
- HFA 19.11.: Ergebnisse als Folgemeldungen, sobald Niederschrift oder Beschlusstexte vorliegen; Gemeinderat 3.12.: Tagesordnung abwarten.
- Montag: Ergebnis Girbelsrath und Wochenende.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
