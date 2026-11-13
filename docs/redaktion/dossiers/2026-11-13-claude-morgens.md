# Merzenich Aktuell – Morgenlauf (Claude), 13. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung 09:15 bis 09:22 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 12.11. nachmittags](2026-11-12-claude-nachmittags.md), seither neu [ChatGPT 13.11. morgens](2026-11-13-morgens.md). main vor dem Lauf: `a9bc4130`. Eil-Prüfungen 12.11. 16:41, 18:45, 20:41 Uhr und 13.11. 06:42 Uhr ohne Fund; Kulturkalender-Termine seit 16:41 Uhr live.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Trauentgelte Alte Kirche (272 Wörter im Fließtext, Rathaus, `relevanz` 3) |
| Korrigierte Bestandsmeldungen | 0 | ChatGPT 13.11. morgens ohne Korrekturauftrag; Corhelper-Artikel dort ohne Abweichung |
| Neue / geänderte Termine | 0 / 0 | |
| Termine an der Quelle bestätigt | 13 | Heimat-Info (10 Detailseiten unverändert), Ratsinfo `termine.ics` (nur Bauausschuss als vergangen entfallen) |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | Kurzmeldung mit offizieller Quelle, 3 Quellen, 3 Aussagen |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Rathaus Merzenich (gemeinfrei) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-ALTE-KIRCHE-ENTGELTE-2027 | „Heiraten in der Alten Kirche Merzenich soll teurer werden: 150 Euro für Paare aus dem Ort, 250 Euro für Auswärtige“ (Kurzmeldung): ab 01.01.2027 von 100 auf 150 Euro (ortsansässig) und von 150 auf 250 Euro (ortsfremd), auch für freie Trauungen; 34/60/60 Trauungen 2024 bis 2026, 2026 mehr Auswärtige (37) als Paare aus dem Ort (23); Kosten je Trauung 30 bis 50 Euro, samstags 60 Euro; rund 5.000 Euro Mehreinnahmen | `inhalte/meldungen/2026-11-13-recherche-claude-morgens.json` → `/rathaus/merzenich-alte-kirche-trauung-entgelt-2027-150-250-euro-vorschlag/` | Vorlage 150/2026 vom 05.11.2026 (gelesen 09:18 Uhr), HFA-Tagesordnung; Commit `4c25764c` |

Quellenausnahmen: ChatGPT führte die Karte als T2 (unabhängige Bestätigung und Buchungsübergang fehlen). Umgesetzt, weil die Vorlage offizielle Quelle ist und eine Kurzmeldung trägt; als Vorschlag gekennzeichnet. Ob bereits gebuchte Trauungen 2027 nach altem oder neuem Satz abgerechnet werden, nennt die Vorlage nicht; im Text nicht behauptet.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Bauausschuss 12.11., Ergebnisse | im Ratsinformationssystem um 09:16 Uhr noch keine Beschlüsse oder Niederschrift; Folgemeldung, sobald veröffentlicht |
| MA-FUSSBALL-GIRB-SWD-2911 (T2, neu) | Spielverlegung 29.11. auf 13 Uhr; Heimrecht und Spielort zwischen FuPa und FUSSBALL.DE widersprüchlich; Termin außerhalb von 14 Tagen |
| MA-DIGITAL-IKZ-2026 (Shared Service Center, Vorlage 146/2026) | für den Nachmittagslauf oder das Wochenende vor dem 19.11. |
| Commons-Foto der Alten Kirche | Commons-API um 09:19 Uhr erneut HTTP 429; Rathausfoto aus dem Bestand |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 13.11. morgens, zuerst KORREKTUREN | keine Korrekturen; neue Karte Spielverlegung Girbelsrath (T2) |
| Ratsinformationssystem | Sitzungsseite Bauausschuss 09:16 Uhr, Vorlage 150/2026 09:18 Uhr, `termine.ics` 09:19 Uhr | keine Bauausschuss-Ergebnisse; Vorlage siehe Abschnitt 1 |
| Polizei Düren | Dienststellenliste nr/8, 06:42 Uhr | neueste Meldung 12.11. 11:32 Uhr (Niederzier); nichts mit Merzenich |
| Feuerwehr | Website und Heimat-Info-Profil, 06:42 Uhr | weiter bis 140/26 bzw. 147/26 |
| Heimat-Info | Gemeindeseite 06:42 Uhr; Veranstaltungskalender und zehn Detailseiten 09:19 Uhr | Adventmarkt-Beitrag des Heimatvereins (Termin vorhanden, Zeiten gleich); Kalender unverändert |

## 5. Sport

Freitag: Spielverlegung Girbelsrath (siehe Abschnitt 3). Spielstand unverändert (nächstes Spiel SC Merzenich in Frenz, 15.11., 15 Uhr).

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender`. Heimat-Info-Veranstaltungskalender der Gemeinde um 09:19 Uhr gelesen (10 Einträge, dieselben IDs wie am 12.11., alle Detailseiten unverändert) und Ratsinfo-`termine.ics` (nur der vergangene Bauausschuss entfallen). Übrige Termine bis 26.11. am 12.11. an ihrer Quelle bestätigt ([Dossier morgens](2026-11-12-claude-morgens.md) und [nachmittags](2026-11-12-claude-nachmittags.md), je Abschnitt 6).

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Pluriversum 17.11., Seniorentreffs 18./25.11. und 02.12., Senioren-Nachmittag 18.11., Gottesdienst 22.11., Adventsliedersingen 29.11. | je Slug | bestätigt | Heimat-Info | 2026-11-13 |
| HFA 19.11., Bürgewald-Ausschuss 26.11., Rat und Jugendgremium 03.12. | je Slug | bestätigt | Ratsinfo `termine.ics` | 2026-11-13 |

Offene Widersprüche unverändert: Weckmannschießen 15.11. (11 oder 11.30 Uhr), Pluriversum 17.11. (18 oder 18.15 Uhr), Senioren-Nachmittag (Kopf November, Text Dezember), Krätzchensitzung 23.01. (14 oder 15 Uhr). **Neu:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `4c25764c` (Meldung), `4755bc0e` (Kalenderpflege), Dokumentation folgt. Abgleich 09:18 Uhr `angestossen: true`, 09:19 Uhr `angestossen: false`; wp-cron HTTP 200.

Live-Nachweis 09:19 Uhr: Meldung HTTP 200, in der Nachrichten-Sitemap, in `wordpress-delivery/merzenich-aktuell-import.xml` auf GitHub (2 Treffer), oben in „Neu in Merzenich“.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| merzenich-alte-kirche-trauung-entgelt-2027-150-250-euro-vorschlag | rathaus.rat | rathaus-merzenich | Merzenich Rathaus.JPG | Public domain | am 11.11. selbst angesehen: Rathaus Merzenich hinter Bäumen, keine Personen, keine Kennzeichen; Sitz von Standesamt und Rat; kein Foto der Alten Kirche im Bestand |

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Trauentgelte Alte Kirche | 272 | 3 | 3 Einträge, alle mit offizieller Quelle; einer zusätzlich mit eigenem Archiv | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Mindesttakt 13.11.: zweite Meldung im Nachmittagslauf (Kandidaten: Shared Service Center 146/2026 oder Wochenvorschau 16. bis 22. November).
- Bauausschuss 12.11.: Ergebnisse im Ratsinformationssystem abwarten.
- Spielverlegung Girbelsrath 29.11.: Heimrecht klären.
- Zeitkonflikte Weckmannschießen, Pluriversum, Krätzchensitzung; Orte der KG-Jonge-vom-Berg-Termine.
- Werkzeugpunkte (Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
