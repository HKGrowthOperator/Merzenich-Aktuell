# Merzenich Aktuell – Nachmittagslauf (Claude), 5. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „14:43“ in der festen Redaktionssitzung, Start 14:45 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 05.11. morgens](2026-11-05-claude-morgens.md), seither neu [ChatGPT 05.11. mittags](2026-11-05-mittags.md). main vor dem Lauf: `81c23ac`. Eil-Prüfungen 10:40 und 12:40 Uhr ohne Eilfund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Wochenvorschau 9.–15.11. (Standard, 845 Wörter, `relevanz` 3, Service) |
| Korrigierte oder ergänzte Bestandsmeldungen | 2 | Bau-Turbo (vier Korrekturen, Hinweise ChatGPT); Martinszüge 2026 (Nachtrag Sicherheitstipps der Polizei) |
| Termine bereinigt | 2 | Automatik 05.11. 13:16 Uhr: Frühstücks- und Spieletreff 11.11. (falscher Ort), Männergruppe/Skatrunde 11.11. |
| Neue Termine | 0 | |
| Werkzeug | `deploy/automatik-termine.mjs` | erkennt fehlende Ortszeile (Zeitangabe ist kein Ort); Test ergänzt |
| Kette / QA | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | Prüfung meldete erneut ein Datum in der Zukunft (15:15 Uhr um 14:54 Uhr), korrigiert |
| Mindesttakt 05.11. | 2 Meldungen | Bau-Turbo 09:23, Wochenvorschau 14:55 |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| Korrektur MA-BAUTURBO-142-2026 | (1) alt: „Laut Bundestag genügt stattdessen eine Prüfung der Gemeinde, die zwei Monate dauert“; neu: Zustimmung gilt als erteilt, wenn die Gemeinde sie nicht binnen drei Monaten nach dem Ersuchen verweigert, plus höchstens ein Monat bei Beteiligung der Öffentlichkeit (§ 36a Abs. 1 Satz 4, Abs. 2, § 246e Abs. 2 BauGB). (2) Höhe: „mehr als einen Meter / mehr als einen halben Meter“ als Beispiel. (3) Vorab-Abstimmung: Verwaltungsentscheidung nur bei Gremienberatung und ohne gravierende Abweichung. (4) Vertrag: Ziffer 1.4 und 8.1 beide genannt; Vorspann „in der Regel“ | `inhalte/meldungen/2026-11-05-recherche-claude-morgens.json` | gesetze-im-internet.de § 36a und § 246e (14:4x Uhr gelesen), Leitlinien Ziff. 1.4, 2.3, 3.1, 8.1, Synopse 2.3; Commit `6ca324e` |
| MA-MARTINSZUEGE-2026 | Nachtrag 05.11.: Polizei begleitet Martinszüge, Hinweise für Eltern und Autofahrer | `inhalte/meldungen/2026-10-16-amtsblatt-11.json` | Polizei Düren, Pressemitteilung 05.11.2026 12:06 Uhr; Commit `9a5f063` |
| MA-WOCHENVORSCHAU-2026-46 (neu) | „Merzenich vom 9. bis 15. November: Martinszüge, Sessionsstart, Bauausschuss und Volkstrauertag“ | `inhalte/meldungen/2026-11-05-recherche-claude-nachmittags.json` → `/leben/merzenich-9-bis-15-november-2026-martinszuege-sessionsstart-bauausschuss-volkstrauertag/` | Termine aus dem geprüften Kalender (Quellen 03.–05.11. nachgeprüft), 15 Quellen, 8 Aussagen; Fortsetzung des Handbeitrags „2. bis 8. November“; Commit `1dca005` |

Nicht genannt: Küsterin, Gastreferentin, Karnevalstollitäten, Diakon im neuen Text (nur im bestehenden Termin).

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Korrektur, Nachtrag, Termin-Bereinigung und Wochenvorschau gehen beim nächsten Abgleich ohne Freigabe online. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Jugendgremium 04.11. | weiter ohne veröffentlichtes Ergebnis (ChatGPT 12:06 Uhr) |
| ChatGPT-Themenkarten | keine neue örtliche Sachentwicklung |
| QA: Ratsinfo-Adressen `/tops/?__=` | `qa/redaktion-lauf.mjs` wertet sie ohne Parameter als dieselbe Quelle; im Artikel durch den Sitzungskalender ersetzt. Werkzeugpunkt für einen späteren Lauf |

## 4. Quellen dieses Laufs

ChatGPT-Mittagsdossier (12:05–12:07 Uhr); Eil-Prüfung 12:40 Uhr (Polizei: Langerwehe, Jülich, Kreis-Hinweis Martinszüge; Feuerwehr bis 144/26; Heimat-Info: Skatrunde); gesetze-im-internet.de (§ 36a, § 246e BauGB); presseportal.de Meldung 6384849; Heimat-Info a5c2217f und 14702026; Ratsinfo ICS (Haupt- und Finanzausschuss 19.11.); Kalenderbestand.

## 5. Sport

Keine Pflichtprüfung am Donnerstag.

## 6. Kalenderpflege

Fenster 05.–19.11.

**Neu:** keiner von Claude. Automatik 05.11., 13:16 Uhr: Frühstücks- und Spieletreff 11.11. und Männergruppe/Skatrunde 11.11. (Heimat-Info, beide 14:01 Uhr veröffentlicht).

**Geändert:** Frühstücks- und Spieletreff 11.11.: Feld „Ort“ enthielt „Mittwochs von 9 - 11 Uhr“, weil der Eintrag keine Ortszeile hat; Ort aus dem Eintrag vom 18.11. übernommen und im Text so gekennzeichnet; Titel lesbar. Skatrunde: Titel lesbar, kein Kontaktname.

**Abgesagt:** keiner.

**Nachgeprüft:** Haupt- und Finanzausschuss 19.11., 18 Uhr (ICS); übrige Termine 05.–18.11. im Morgenlauf und am 04.11. nachgeprüft; Heimat-Info-Kalender (Quellenabruf 13:14 Uhr, 10 Einträge, alle im Bestand).

## 7. QA, Commit, Abgleich

Commits: `6ca324e` (Korrektur Bau-Turbo), `9a5f063` (Martinszüge, Termine, Automatik), `1dca005` (Wochenvorschau), `0333708` (Dossier). Live-Nachweis 15:11 Uhr: Wochenvorschau HTTP 200, in der Nachrichten-Sitemap und oben in „Neu in Merzenich“; Bau-Turbo-Korrektur („drei Monate“), Martinszug-Nachtrag und beide Termine vom 11.11. (ohne falschen Ort, ohne Nummer) live.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| merzenich-9-bis-15-november-2026-martinszuege-sessionsstart-bauausschuss-volkstrauertag | leben.brauchtum | martinsfeuer | Martinsfeuer.jpg | CC BY-SA 3.0 | selbst angesehen: Martinsfeuer bei Nacht, keine Personen; Symbolbild für die Martinswoche |

## 8a. Prüfung und Eigenformulierung

`node qa/eigenformulierung.mjs --datum 2026-11-05 --lauf nachmittags`: 0 Fehler, 0 Hinweise. Bau-Turbo nach Korrektur (Lauf morgens): 0 Fehler, 0 Hinweise.

## 9. Offene Punkte für den nächsten Lauf

- Jugendgremium-Ergebnis.
- Bauausschuss 12.11.: Folgemeldungen.
- Orte der KG-Jonge-vom-Berg-Termine (11.11., 13.11.).
- `qa/redaktion-lauf.mjs`: Ratsinfo-Adressen mit Parameter nicht als Dublette werten.
