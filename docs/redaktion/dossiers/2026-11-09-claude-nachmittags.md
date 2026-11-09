# Merzenich Aktuell – Nachmittagslauf (Claude, nachgeholt), 9. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „14:43“, wegen eines erneuten Sitzungsausfalls erst um 18:41 Uhr gelesen; Bearbeitung 18:41 bis 18:55 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 08.11. nachmittags](2026-11-08-claude-nachmittags.md), seither neu [ChatGPT 09.11. morgens](2026-11-09-morgens.md) und [09.11. mittags](2026-11-09-mittags.md). main vor dem Lauf: `c58cb63d`.

**Ausgefallen (Sitzungsstörung vom 08.11. etwa 20:45 Uhr bis 09.11. 18:40 Uhr):** Morgenlauf 09.11., Eil-Prüfungen 06:40, 10:40, 12:40 und 16:40 Uhr; die Eil-Prüfung 18:40 Uhr deckt dieser Lauf ab. Der S19-Nachtrag war am 08.11. um 20:41 Uhr vorbereitet, aber nicht committet; er ist mit angepasstem Wortlaut nachgetragen (Abschnitt 1). Polizei Düren: seit 08.11. keine Meldung mit Merzenich-Bezug (09.11.: Düren, Linnich).

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Papiersammlung Golzheim (308 Wörter, Vereine, `relevanz` 3) |
| Aktualisierte Bestandsmeldungen | 1 | S19-Meldung vom 08.11.: Nachtrag Reparatur, Titel und Vorspann |
| Neue Termine | 1 | 5. Sitzung des Jugendgremiums, 03.12., online |
| Termine geändert / bestätigt | 1 / 11 | Automatik-Termin Skatrunde 25.11. lesbar gemacht; Heimat-Info- und Ratsinfo-Termine bis 22.11. bestätigt |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | 4 Quellen, darunter Amtsblatt als offizielle Quelle |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Poolfoto Altpapiertonne |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | |
| Mindesttakt 09.11. | 1 Meldung | Ziel zwei verfehlt: Sitzungsausfall bis 18:40 Uhr, keine weitere belegbare neue Nachricht in den Pflichtquellen |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-S19-STELLWERK-07112026 (Nachtrag) | Titel neu „S19 aus Merzenich: Stellwerk in Köln repariert, Züge fahren wieder über den Flughafen“; Nachtrag vom 9. November oben: Reparatur laut mobil.nrw am 08.11. um 20:22 Uhr, am 09.11. um 18:42 Uhr keine Störung mehr gelistet; übrige Absätze in die Vergangenheit gesetzt. Damit ist der ChatGPT-Befund vom 09.11. mittags („Artikel sagt weiterhin ‚kommt derzeit nicht direkt ans Ziel‘“) erledigt | `inhalte/meldungen/2026-11-08-recherche-claude-nachmittags.json` | mobil.nrw 08.11. 20:41 Uhr (Eintrag „repariert“) und 09.11. 18:42 Uhr (kein Eintrag mehr); Commit `1b16fab2` |
| MA-PAPIERTONNE-2026 (neu) | „Merzenich: Schützen in Golzheim beenden nach über 40 Jahren ihre Papiersammlung“ (Kurzmeldung) | `inhalte/meldungen/2026-11-09-recherche-claude-nachmittags.json` → `/vereine/papiersammlung-schuetzen-golzheim-endet-vereine-2027-merzenich/` | Gemeinde-Beitrag Heimat-Info fde69987 (09.11. 07:53 Uhr), Amtsblatt 11/2026 PDF-S. 21 (Papiersammlung 06./07.11.), Schönmackers-Abfallkalender 2026 (Bezirke A/B/C, 05.12.), eigene Meldung 15.10.; Commit `7a33c763` |
| Kalender | Jugendgremium 03.12. neu; Skatrunde 25.11. bereinigt | `inhalte/termine/` | Commits `7a33c763`, `57b68c65` |

Quellenausnahmen: ChatGPT führte die Papiersammlung als T2 (nur eine Quelle für die Ankündigung, Schützen-Website HTTP 403). Umgesetzt, weil die Mitteilung im offiziellen Profil der Gemeinde steht (SCHREIBREGELN Abschnitt 0) und der Text nur ihren Inhalt wiedergibt; offen benannt sind Grund, künftiger Abholer, Termin 05.12. und Reichweite über Golzheim hinaus. Der Beitrag ist aus Sicht der Sammler formuliert („wir danken“); keine Namen.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

S19-Nachtrag, neue Meldung und Termine ohne Freigabe online. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| „Adventausstellung“ von Blumen Hüffelmann (Heimat-Info, 08.11. 21:37 Uhr) | Beitrag eines Gewerbebetriebs; Einsendungen von Firmen werden nicht veröffentlicht |
| Zweite Meldung für den Mindesttakt | keine weitere neue belegbare Nachricht; Termine der Woche stehen in der korrigierten Wochenvorschau |
| Jugendgremium 04.11. | weiter kein Ergebnis veröffentlicht; neu nur die nächste Sitzung am 03.12. |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 09.11. morgens und mittags, zuerst KORREKTUREN | keine neuen Korrekturen; S19-Aktualität offen (jetzt erledigt); Themenkarte Papiersammlung (T2) |
| Polizei Düren | Dienststellenliste nr/8, 18:44 Uhr | 09.11.: Düren (2), Linnich (1); nichts mit Merzenich |
| Heimat-Info | Gemeindeseite und Beiträge, 18:44 Uhr; Veranstaltungskalender 18:44 Uhr, Detailseiten 18:48 Uhr | Gemeinde: Papiersammlung (07:53 Uhr); Gewerbe: Adventausstellung; Feuerwehr: nichts nach 147/26; Kalender 10 Einträge, neu Skatrunde 25.11., übrige unverändert |
| Feuerwehr | feuerwehr-merzenich.de/einsaetze | weiter bis 140/26 |
| Ratsinformationssystem | `termine.ics`, 18:48 Uhr, Sitzungsseite Jugendgremium | neu: Jugendgremium 03.12., 18 Uhr, online, ohne Tagesordnung |
| Amtsblatt | Ausgabe 11, PDF-S. 21 | Papiersammlungstermine; Ausgabe 12 am 08.11. noch nicht erschienen |
| Bahn | mobil.nrw 18:42 Uhr | S19-Störung nicht mehr gelistet |
| Schützen Golzheim | schuetzen-golzheim.de/aktuelles | HTTP 403, nicht umgangen |

## 5. Sport

Montag, keine Pflichtprüfung; nächste Dienstag, 10.11.

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender`. Heimat-Info-Veranstaltungskalender der Gemeinde um 18:44 Uhr gelesen (10 Einträge bis 25.11., Detailseiten 18:48 Uhr mit dem Stand vom 08.11. verglichen: 9 unverändert, 1 neu) und Ratsinfo-`termine.ics` (18:48 Uhr). Die übrigen Termine bis 22.11. wurden am 08.11. zwischen 12:45 und 13:05 Uhr an ihrer Quelle nachgeprüft ([Dossier](2026-11-08-claude-morgens.md), Abschnitt 6).

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Jugendgremium, 03.12. | jugendgremium-sitzung-2026-12-03 | **neu** | Ratsinfo `termine.ics`, Sitzungsseite | 2026-11-09 |
| Skatrunde, 25.11. (Automatik) | maennergruppe-skatrunde-fuer-senioren-merzenich-2026-11-25 | geändert: Titel, Ort und Adresse lesbar | Heimat-Info 7d1b5151 | 2026-11-09 |
| Skatrunde 11.11. und 18.11., Frühstückstreff 11.11. und 18.11., Senioren-Nachmittag 18.11., Gottesdienste 15.11. und 22.11., Martinszug Golzheim 11.11., Pluriversum 17.11. | je Slug | bestätigt (Detailseite unverändert) | Heimat-Info | 2026-11-09 |
| Bauausschuss 12.11., HFA 19.11. | je Slug | bestätigt | Ratsinfo `termine.ics` | 2026-11-09 |

**Abgesagt:** keiner. Offene Hinweise „nachpruefen“: 0.

## 7. QA, Commit, Abgleich

Commits: `1b16fab2` (S19-Nachtrag), `7a33c763` (Papiersammlung, Termin 25.11.; Rebase-Konflikt nur in `docs/SHA256SUMS.txt`, Stand von main, Kette neu), `57b68c65` (Kalenderpflege), Dokumentation folgt. Abgleich angestoßen 18:43 Uhr (`angestossen: true`), 18:47 Uhr (`angestossen: false`, Lauf schon angestoßen), wp-cron HTTP 200.

Live-Nachweis 18:50 Uhr: Papiersammlung HTTP 200, in der Nachrichten-Sitemap und oben in „Neu in Merzenich“ (vor S19 und Pluriversum); S19-Seite zeigt den Nachtrag vom 9. November; Termin Jugendgremium 03.12. HTTP 200; Import-XML auf GitHub enthält Meldung und Termin.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| papiersammlung-schuetzen-golzheim-endet-vereine-2027-merzenich | gemeinde.abfall | papiertonne | Blaue Altpapiertonne Richen Juni 2014.JPG | CC BY-SA 3.0 | selbst angesehen: blaue Altpapiertonnen am Gehweg, keine Personen, keine Kennzeichen; Symbolbild für Altpapier, kein Foto der Sammlung |

Neu importierte Fotos: keine.

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll (offiziell / doppelt / Selbstauskunft) | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Papiersammlung Golzheim | 308 | 4 | 1 / 0 / 3 | 0 Wörter |
| S19 nach Nachtrag | – | 3 | 4 Einträge | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Papiersammlung: Regelung ab 2027 (Abholer, andere Ortsteile, Termin 05.12.) bei Gemeinde oder Schönmackers nachverfolgen.
- Bauausschuss 12.11. Folgemeldungen; Jugendgremium-Ergebnis 04.11.; Orte der KG-Jonge-vom-Berg-Termine.
- Werkzeugpunkte (Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
- Sitzungsstabilität: zweiter Ausfall binnen vier Tagen (05./06.–08.11. und 08./09.11.); vorbereitete Arbeit wurde bisher nicht verloren, aber Läufe und Eil-Prüfungen fallen aus.
