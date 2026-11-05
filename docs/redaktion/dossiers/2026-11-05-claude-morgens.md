# Merzenich Aktuell – Morgenlauf (Claude), 5. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „09:13“ in der festen Redaktionssitzung, Start 09:14 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 04.11. nachmittags](2026-11-04-claude-nachmittags.md), seither neu [ChatGPT 05.11. morgens](2026-11-05-morgens.md). main vor dem Lauf: `46dfc03`. Eil-Prüfungen 16:40, 18:40, 20:40 (04.11.) und 06:40 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Bau-Turbo-Leitlinien (Standard, 845 Wörter, `relevanz` 2), eigener Fund |
| Korrigierte Bestandsmeldungen | 1 | Solarpark Golzheim: Erdwall „2,5 bis 3 Meter“ statt „zwei bis drei“ (Hinweis ChatGPT) |
| Termine mit Nachtrag oder Bereinigung | 4 | Kindergottesdienst 08.11. (zwei Anmeldefristen), Senioren-Nachmittag 18.11. (Chanukka-Vortrag 09.12.), Frühstücks-/Spieletreff 18.11. und Adventsfeier 09.12. (Name und Nummer entfernt) |
| Neue Termine | 0 | Automatik 04.11. 21:25 Uhr: drei Heimat-Info-Termine, an der Quelle nachgeprüft |
| Drei-Quellen-Quote, Prüfprotokoll | Bau-Turbo 6 Quellen (Ratsinfo, Bundesregierung, Bundestag), 8 Aussagen | |
| Bilder geprüft | 1 | Rathaus Merzenich (gemeinfrei) |
| Werkzeug | `deploy/automatik-termine.mjs` | Zitat trennt vor „Kontakt:“ und lässt Kurznummern weg; Test ergänzt |
| Kette / QA | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | Die neue Prüfung meldete ein Meldungsdatum in der Zukunft (09:58 Uhr um 09:22 Uhr), korrigiert |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| Korrektur MA-PV-GOLZHEIM-E1-FNP20 | alt: „von einem zwei bis drei Meter hohen Erdwall“; neu: „von einem 2,5 bis 3 Meter hohen Erdwall“ | `inhalte/meldungen/2026-11-04-recherche-claude-nachmittags.json` | Entwurfsbegründung S. 5, 2.2; Commit `4e0646a` |
| MA-BAUTURBO-142-2026 (neu) | „Bau-Turbo in Merzenich: Wann die Gemeinde Wohnungen ohne Bebauungsplan erlauben will“ (Standard) | `inhalte/meldungen/2026-11-05-recherche-claude-morgens.json` → `/rathaus/bau-turbo-merzenich-leitlinien-wohnungsbau-ohne-bebauungsplan-bauausschuss-2026/` | Vorlage 142/2026 (3 Seiten), Leitlinien Ver. 1.0 (alle Ziffern), Synopse Kreis/Gemeinde, Tagesordnung 12.11., Bundesregierung (29.01.2026), Bundestag (Textarchiv KW 41/2025); Commit `dee5f0f` |
| Kalender | Nachträge und Bereinigung, siehe Abschnitt 6 | `inhalte/termine/` | Commit `4e0646a` |

Nicht genannt: Sachbearbeiterinnen, Küsterin (Name und Telefonnummer aus zwei Automatik-Terminen entfernt), Gastreferentin des Senioren-Nachmittags, Karnevalsprinzessin.

Top-Meldung: keine neue Stufe 1 (Vorlage, kein Beschluss).

## 2. Sofort sichtbar

Neue Meldung und Termin-Nachträge beim nächsten Abgleich ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Wochenende 6.–8.11. als Vorschau | Handbeitrag „Was vom 2. bis 8. November in Merzenich ansteht“ (02.11.) ist live; keine Dublette |
| MA-FANCLUB-60-2027 (Jubiläums-Bierabend 30.10.2027) | T2, Termin außerhalb des Kalenderfensters, reine Vereinsankündigung |
| DRK-Infoabend „kostenlos“ | Gemeindepresse antwortet Claude mit HTTP 428 (Browserprüfung), nicht umgangen; Heimat-Info nennt keine Kosten |
| Poolplatz (M142/2026) | Mitteilung ohne Inhalt außer der Ankündigung; Folgemeldung nach der Sitzung |
| Jugendgremium 04.11. | Kein Ergebnis veröffentlicht (Vorlage mit Platzhalter, ChatGPT 06:58 Uhr) |

## 4. Quellen dieses Laufs

Pflichtquellen nach ChatGPT 06:57 bis 07:00 Uhr und Eil-Prüfung 06:40 Uhr (Polizei ohne Merzenich-Fall seit 04.11. 10:45 Uhr, Feuerwehr bis 144/26, Heimat-Info). Zusätzlich gelesen: Heimat-Info-Veranstaltungen f60eafb6, 43009e41, 0d9701e3, 0bfa22f8, 65540fac; Ratsinfo Vorlage 142/2026 mit Anlagen 2 und 3, M142/2026, Sitzungen RPA 05.11. und HFA 19.11.; Live-Beiträge seit 01.11. (REST); bundesregierung.de, bundestag.de; Vereinsseiten KG Golzheim und Golzheim aktiv.

## 5. Sport

Keine Pflichtprüfung am Donnerstag.

## 6. Kalenderpflege

Fenster 05.–19.11.

**Neu:** keiner von Claude. Automatik vom 04.11., 21:25 Uhr (an der Quelle nachgeprüft): Frühstücks- und Spieletreff für Seniorinnen und Senioren 18.11., 9–11 Uhr; Adventsliedersingen 29.11., 18 Uhr, Alte Kirche; Adventsfeier für Seniorinnen und Senioren 09.12., 15–17:30 Uhr. Bei zwei davon Name und Telefonnummer der Küsterin aus dem Zitat entfernt, Titel lesbar gemacht.

**Geändert:** Kindergottesdienst 08.11. (Nachtrag: Amtsblatt nennt Anmeldung bis 05.11., 11:30 Uhr, Heimat-Info seit 04.11. 19:39 Uhr bis 31.10., 11:30 Uhr; welche gilt, offen); Senioren-Nachmittag 18.11. (Nachtrag: App nennt im Text einen Chanukka-Vortrag für 09.12.).

**Abgesagt:** keiner.

**Nachgeprüft:** Kostümsitzung KG Golzheim 07.11. (Vereinsseite, unverändert), Bücher- und Spielemarkt 08.11. (golzheimaktiv.de, unverändert), Rechnungsprüfungsausschuss 05.11. und Haupt- und Finanzausschuss 19.11. (Ratsinfo, ohne Tagesordnung), Heimat-Info-Kalender (ChatGPT 06:57 Uhr). Übrige Termine 05.–17.11. im Lauf vom 04.11. nachgeprüft.

## 7. QA, Commit, Abgleich

Commits: `4e0646a` (Korrektur, Termine, Automatik-Filter), `dee5f0f` (Bau-Turbo), `96a6246` (Dossier). Live-Nachweis 09:27 Uhr: Bau-Turbo HTTP 200, in der Nachrichten-Sitemap und oben in „Neu in Merzenich“; Erdwall-Korrektur und Nachtrag Kindergottesdienst live; Frühstücks- und Spieletreff ohne Telefonnummer.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| bau-turbo-merzenich-leitlinien-wohnungsbau-ohne-bebauungsplan-bauausschuss-2026 | rathaus.rat | rathaus-merzenich | Merzenich Rathaus.JPG | Public domain | selbst angesehen: Rathaus Merzenich, Sitz von Verwaltung und Ausschuss, die über die Leitlinien entscheiden |

## 8a. Prüfung und Eigenformulierung

`node qa/eigenformulierung.mjs --datum 2026-11-05 --lauf morgens`: 0 Fehler, 0 Hinweise (zwei Wortfolgen von 9 Wörtern aus den Bundesquellen und eine von 8 umformuliert). Prüfprotokoll: jede Zahl mit Eintrag.

## 9. Offene Punkte für den nächsten Lauf

- Jugendgremium-Ergebnis, Niederschrift.
- Bauausschuss 12.11.: Folgemeldungen (Schule Golzheim, Solarparks, Energiemanagement, Tiefbau, Poolplatz, Bau-Turbo).
- Wochenvorschau 9.–15.11. (Sonntag).
- Kindergottesdienst-Frist und Senioren-Nachmittag am Veranstalter klären, sobald eine Quelle es auflöst.
