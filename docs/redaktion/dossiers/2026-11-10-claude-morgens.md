# Merzenich Aktuell – Morgenlauf (Claude), 10. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung 09:16 bis 09:30 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 09.11. nachmittags (nachgeholt)](2026-11-09-claude-nachmittags.md), seither neu [ChatGPT 10.11. morgens](2026-11-10-morgens.md). main vor dem Lauf: `ecb65c1e`. Eil-Prüfungen 09.11. 20:41 Uhr und 10.11. 06:42 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Fußball-Spieltag 8.11. (289 Wörter im Fließtext, Sport, `relevanz` 3) |
| Korrigierte Bestandsmeldungen | 0 | ChatGPT 10.11. ohne Korrekturauftrag |
| Neue / geänderte Termine | 0 / 0 | |
| Termine an der Quelle bestätigt | 13 | Heimat-Info, Ratsinfo, Kreis Düren (Seniorenweihnacht 24.11.) |
| Sportstand | geändert | `chatgpt-site/api/sport-current.json`: letztes Spiel SC – FC Düren 77 2:1 (08.11.), nächstes Spiel JSV Frenz – SC (15.11., 15 Uhr), Tabelle nach dem 11. Spieltag; Quellen FUSSBALL.DE-Spielbericht 09.11. 08:34 Uhr und FuPa-Tabelle 10.11. 09:22 Uhr |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | 7 Quellen, 5 Aussagen |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Poolfoto Sportplatz |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 2 Hinweise (FuPa-Seiten für das Prüfwerkzeug HTTP 403) | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-SONNTAGSSPORT-ERG-0811 | „SC Merzenich springt nach 2:1 gegen Düren 77 auf Platz drei, Golzheim spielt 2:2 im Spitzenspiel“ (Kurzmeldung): SC – FC Düren 77 2:1 (1:0), Platz drei mit 27 Punkten, sechs Spiele ungeschlagen; Golzheim – Welldorf-Güsten 2:2 (2:1), Golzheim Fünfter; Girbelsrath – Morschenich als „Nichtantritt HEIM“, Wertung bei FUSSBALL.DE offen, FuPa mit Urteil 2:0; nächste Spiele 15.11. | `inhalte/meldungen/2026-11-10-recherche-claude-morgens.json` → `/sport/fussball-merzenich-2-1-dueren-77-platz-drei-golzheim-2-2-welldorf-derby-girbelsrath-nichtantritt-2026-11-08/` | FUSSBALL.DE-Spielberichte (automatisch aus den offiziellen Spieldaten, 09.11. 08:24 und 08:34 Uhr), Spielseiten Golzheim–Welldorf (Saisonstatistik) und Girbelsrath–Morschenich (Spielkopf), FuPa-Tabellen Kreisliga A und B2, eigene Meldung 25.10.; Commit `de270a71` |
| Spielstand | `sport-current.json` aktualisiert (siehe Abschnitt 0) | `chatgpt-site/api/sport-current.json` | Commit `de270a71` |

Quellenausnahmen: ChatGPT führte die Karte als T2 (Unabhängigkeit der Ergebnisquellen nicht nachgewiesen). Umgesetzt, weil FUSSBALL.DE als offizielle Verbandsquelle die Ergebnisse von SC und Golzheim im Klartext trägt (SCHREIBREGELN Abschnitt 0) und FuPa die Tabellenplätze unabhängig darstellt; Platz, Punkte und Tore der beiden Teams stimmen in beiden Quellen überein. Nicht übernommen: die Ergebnisse der zweiten und dritten Mannschaft des SC (nur FuPa, DFB-Ziffern verschlüsselt) und jeder Grund für den Nichtantritt. Keine Spielernamen.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung ohne Freigabe; Spielstand im Sportkasten des Themes binnen etwa 15 Minuten (Theme liest `sport-current.json` von GitHub). Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Ergebnisse SC II (6:2) und SC III (1:10) | nur FuPa im Klartext, keine offizielle Bestätigung lesbar |
| Grund und Wertung des Nichtantritts in Girbelsrath | keine Quelle; Verbandswertung steht aus |
| Bauausschuss 12.11. | Folgemeldungen nach der Sitzung |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 10.11. morgens, zuerst KORREKTUREN | keine Korrekturen; Themenkarte Sportergebnisse (T2) |
| Polizei Düren | Dienststellenliste, Eil-Prüfung 06:42 Uhr | keine Merzenich-Meldung seit 05.11. |
| Heimat-Info | Gemeindeseite und Feuerwehrprofil 06:42 Uhr; Veranstaltungskalender und zehn Detailseiten 09:21 Uhr | nichts Neues; alle zehn Kalendereinträge unverändert gegenüber 09.11. 18:48 Uhr |
| Feuerwehr | feuerwehr-merzenich.de/einsaetze 06:42 Uhr | weiter bis 140/26 |
| Ratsinformationssystem | `termine.ics` 09:21 Uhr | unverändert |
| Kreis Düren | Presseübersicht laut ChatGPT 07:03 Uhr; Seniorenweihnacht-Seite 09:21 Uhr | nichts mit Merzenich-Bezug; 24.11. bestätigt |
| FUSSBALL.DE / FuPa | Spielberichte, Spielseiten, Spieltag Kreisliga A, Tabellen A und B2, 09:17 bis 09:22 Uhr | siehe Abschnitt 1 |

Sperren und Umwege: gemeinde-merzenich.de (Website) weiter HTTP 428; FuPa liefert dem Prüfwerkzeug HTTP 403 (der Abruf im Lauf ging).

## 5. Sport

Pflichtprüfung Dienstag erledigt, siehe Abschnitt 1 und Spielstand. Nächste Spiele Sonntag, 15. November (Volkstrauertag): JSV Frenz – SC Merzenich (15 Uhr), Spvg. SW 1896 Düren – FC Golzheim.

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender`. Heimat-Info-Veranstaltungskalender der Gemeinde um 09:21 Uhr gelesen (10 Einträge, alle Detailseiten unverändert gegenüber 09.11.), Ratsinfo-`termine.ics` unverändert, Kreis Düren für 24.11. gelesen. Übrige Termine bis 22.11. am 08.11. an ihrer Quelle nachgeprüft ([Dossier](2026-11-08-claude-morgens.md), Abschnitt 6).

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Seniorenweihnacht des Kreises, 24.11. | seniorenweihnacht-kreis-dueren-2026-11-24 | bestätigt (15 Uhr, Einlass 14 Uhr, Arena Kreis Düren) | kreis-dueren.de | 2026-11-10 |
| DRK-Infoabend, 10.11. | infoabend-krisenvorsorge-drk-buergerhaus-merzenich-2026-11-10 | bestätigt (18:30 Uhr) | Heimat-Info 6b90fdb6 | 2026-11-10 |
| Skatrunde und Frühstückstreff 11./18.11., Senioren-Nachmittag 18.11., Gottesdienste 15./22.11., Martinszug Golzheim 11.11., Pluriversum 17.11. | je Slug | bestätigt | Heimat-Info | 2026-11-10 |
| Bauausschuss 12.11., HFA 19.11. | je Slug | bestätigt | Ratsinfo `termine.ics` | 2026-11-10 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner. Offene Hinweise „nachpruefen“: 0.

## 7. QA, Commit, Abgleich

Commits: `de270a71` (Meldung und Spielstand), `b86a2bfd` (Kalenderpflege), Dokumentation folgt. Abgleich 09:21 Uhr `angestossen: false` (Workflow hatte schon angestoßen), 09:24 Uhr `angestossen: true`; wp-cron HTTP 200.

Live-Nachweis 09:24 Uhr: Meldung HTTP 200, in der Nachrichten-Sitemap und oben in „Neu in Merzenich“ (vor der Papiersammlung); die Sportseite nennt das nächste Spiel in Frenz (neuer Spielstand übernommen).

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| fussball-merzenich-2-1-dueren-77-platz-drei-golzheim-2-2-welldorf-derby-girbelsrath-nichtantritt-2026-11-08 | sport.spielbericht | fussball | Borken, Burlo, Sportplatz -- 2014 -- 2288.jpg | CC BY-SA 4.0 | selbst angesehen: Luftbild eines Dorf-Sportplatzes mit Ascheplatz und Toren, keine erkennbaren Personen, keine lesbaren Kennzeichen; Symbolbild für Amateurfußball, kein Foto der Merzenicher Plätze |

Neu importierte Fotos: keine.

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll (offiziell / doppelt / Selbstauskunft) | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Fußball-Spieltag 8.11. | 289 | 7 | 3 / 1 / 1 (eigenes Archiv) | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Wertung Girbelsrath – Morschenich bei FUSSBALL.DE; dann Tabelle B2 im Spielstand und Nachtrag.
- Bauausschuss 12.11. (Donnerstag): Folgemeldungen am 13.11.
- Papiersammlung ab 2027, Jugendgremium-Ergebnis, Orte der KG-Jonge-vom-Berg-Termine.
- Werkzeugpunkte (Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
