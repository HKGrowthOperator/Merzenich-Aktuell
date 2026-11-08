# Merzenich Aktuell – Nachmittagslauf (Claude), 8. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung 14:44 bis 14:55 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 08.11. morgens (Nachhollauf, bis 13:10 Uhr)](2026-11-08-claude-morgens.md); seither kein neues ChatGPT-Dossier, das jüngste bleibt [08.11. mittags](2026-11-08-mittags.md) (im Nachhollauf vollständig abgearbeitet). main vor dem Lauf: `9d6105d`.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung S19-Störung (295 Wörter, Leben/Service, `relevanz` 3) |
| Korrigierte Bestandsmeldungen | 0 | keine neuen Korrekturaufträge |
| Neue / geänderte Termine | 0 / 0 | Heimat-Info und Ratsinfo unverändert seit dem Nachhollauf |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | Kurzmeldung mit offizieller Quelle (mobil.nrw) und VRS als Hintergrund, 3 Aussagen |
| Sportstand | unverändert | Sonntag, keine Pflichtprüfung |
| Bilder gefunden / geprüft / integriert | 1 / 1 / 1 | Commons „423194 Porz-Wahn Nah.jpg“, neue Bildklasse `verkehr.bahn` |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | eine 11-Wort-Folge aus mobil.nrw umformuliert |
| Mindesttakt 08.11. | 3 Meldungen | Feuerwehr 12:55, Pluriversum 12:56, S19 14:47 Uhr |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-S19-STELLWERK-07112026 | „S19 aus Merzenich hält seit Samstag nicht am Flughafen: Stellwerk in Köln defekt“ (Kurzmeldung) | `inhalte/meldungen/2026-11-08-recherche-claude-nachmittags.json` → `/leben/s19-merzenich-flughafen-koeln-bonn-stellwerk-stoerung-umleitung-november-2026/` | mobil.nrw SPNV-Meldungen 12:52 und 14:46 Uhr (Meldungsstand 13:26 Uhr), VRS Haltestelle Merzenich Bf und Linie S19 14:45 Uhr; Commit `319ba58` |
| Bildpflege | Commons-Foto im Pool `themen`, Motiv `s-bahn`, Bildklasse `verkehr.bahn` | `deploy/import-editorial-photos.mjs`, `bildmotive.json`, `bildklassen.json`, `editorial-photo-review.json` | Lizenz und Maße über die Commons-API, Foto selbst angesehen; Commit `319ba58` |

Quellenausnahmen: ChatGPT führte die Störung seit 07.11. mittags als T2 (keine unabhängige Ereignisbestätigung). Im Nachhollauf am Mittag zurückgestellt, weil sich der Stand stündlich änderte. Jetzt umgesetzt: Die Störung läuft am zweiten Tag, mobil.nrw ist offizielle Quelle (SCHREIBREGELN Abschnitt 0: eine offizielle Quelle trägt eine Kurzmeldung), der Text nennt den Stand mit Uhrzeit und den Widerspruch zwischen Ende-Feld (19.30 Uhr) und „Ende nicht abschätzbar“. VRS bestätigt den Linienweg über Merzenich, nennt die Störung selbst nicht.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Die S19-Meldung ist ohne Freigabe online. Ist die Strecke wieder frei, kommt ein Nachtrag in dieselbe Meldung (Eil-Prüfung oder nächster Lauf).

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Weitere Meldung zu Terminen der nächsten sieben Tage | alle Termine 09.–15.11. stehen in der korrigierten Wochenvorschau und im Kalender; eine zweite Vorschau wäre eine Dublette |
| Bauausschuss 12.11. | Folgemeldungen nach der Sitzung |
| Jugendgremium 04.11. | weiter kein Ergebnis veröffentlicht |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| Rohdaten `imports/quellen/` | Abruf 08.11. 13:02 Uhr (im Nachhollauf ausgewertet) | nichts Neues |
| Polizei Düren | Dienststellenliste nr/8, 14:44 Uhr | neueste Meldungen 08.11. 08:49 und 08:07 Uhr (Düren); nichts mit Merzenich |
| Kreis Düren Presse | Presseübersicht, 14:44 Uhr | keine Meldung vom 07. oder 08.11. |
| Heimat-Info | Gemeindeseite, Feuerwehrprofil, Veranstaltungskalender, 14:44 Uhr; zehn Detailseiten 14:50 Uhr | keine neuen Beiträge seit 147/26 (10:33 Uhr); Kalender 10 Einträge, alle unverändert gegenüber 12:45 Uhr |
| Ratsinformationssystem | `termine.ics`, 14:44 Uhr | unverändert (nur Zeitstempel) |
| Feuerwehr | feuerwehr-merzenich.de/einsaetze, 14:44 Uhr | weiter bis 140/26 |
| Amtsblatt | Ausgabe 12 (`/bindata/…/Merzenich_12_2026-web.pdf`) | HTTP 404, noch nicht erschienen |
| Bahn | mobil.nrw, VRS | S19-Störung, siehe Abschnitt 1 |
| ChatGPT | kein neues Dossier seit 08.11. mittags | Korrekturen bereits im Nachhollauf umgesetzt |

Sperren und Umwege: gemeinde-merzenich.de (Website) HTTP 428, nicht erneut versucht.

## 5. Sport

Keine Pflichtprüfung am Sonntag; nächste Prüfung Dienstag, 10.11.

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender` (39 Termine bis 07.01.2027). Alle Termine 10.–22.11. sind im Nachhollauf zwischen 12:45 und 13:05 Uhr an ihrer Quelle nachgeprüft worden ([Dossier morgens](2026-11-08-claude-morgens.md), Abschnitt 6). In diesem Lauf erneut gelesen: Heimat-Info-Veranstaltungskalender der Gemeinde um 14:44 Uhr (10 Einträge, Detailseiten 14:50 Uhr, alle unverändert) und Ratsinfo-`termine.ics` (unverändert).

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| alle zehn Heimat-Info-Termine 08.–22.11. | siehe Dossier morgens | bestätigt, unverändert | heimat-info.de/veranstaltungen (Detailseiten) | 2026-11-08 |
| Bauausschuss 12.11., HFA 19.11. | bauausschuss-sitzung-2026-11-12, haupt-finanzausschuss-sitzung-2026-11-19 | bestätigt, unverändert | ratsinfo `termine.ics` | 2026-11-08 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner. Offene Hinweise „nachpruefen“: 0.

## 7. QA, Commit, Abgleich

Commit `319ba58` (Meldung und Bildpflege), Dokumentation folgt. Abgleich angestoßen 14:49 Uhr (`angestossen: true`), wp-cron HTTP 200. Live-Nachweis 14:50 Uhr: Artikel HTTP 200 mit dem S-Bahn-Foto, in der Nachrichten-Sitemap, in der Import-XML auf GitHub (2 Treffer) und oben in „Neu in Merzenich“ vor Pluriversum und Feuerwehr. Bei bestehenden Artikeln hat sich durch das neue Poolfoto kein Bild geändert (nur Übersichtsseiten verschoben).

Freigaben im Backend: nicht geprüft (kein Zugang bereitgestellt).

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| s19-merzenich-flughafen-koeln-bonn-stellwerk-stoerung-umleitung-november-2026 | verkehr.bahn | s-bahn | 423194 Porz-Wahn Nah.jpg | CC BY 2.5 | selbst angesehen: rote S-Bahn der Baureihe 423 mit Zielanzeige „Düren“ am Bahnhof Porz-Wahn, dem Punkt, an dem die S19 jetzt umgeleitet wird; keine Personen, keine Kennzeichen; Symbolbild, kein Foto der Störung |

Neu importierte Fotos: „File:423194 Porz-Wahn Nah.jpg“, Urheber Qualle (Wikimedia Commons), CC BY 2.5, 1600 × 1200 px; Vorschlag aus dem ChatGPT-Dossier 07.11. mittags.

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll (offiziell / doppelt / Selbstauskunft) | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| S19-Störung | 295 | 3 | 3 / 0 / 0 | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- S19: Nachtrag, sobald mobil.nrw die Störung aufhebt (Eil-Prüfung 16:40 Uhr oder später).
- Werkzeugpunkte aus dem Nachhollauf (Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
- Bauausschuss 12.11. Folgemeldungen; Jugendgremium; Orte der KG-Jonge-vom-Berg-Termine; Pluriversum 18 oder 18.15 Uhr.
