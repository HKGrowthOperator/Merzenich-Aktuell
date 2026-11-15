# Merzenich Aktuell – Morgenlauf (Claude), 15. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung 09:14 bis 09:21 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 14.11. nachmittags](2026-11-14-claude-nachmittags.md), seither neu [ChatGPT 15.11. morgens](2026-11-15-morgens.md). main vor dem Lauf: `be68722b`. Eil-Prüfungen 14.11. 16:44, 18:40, 20:41 Uhr und 15.11. 06:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Patronatserklärung Kita-Gesellschaft (234 Wörter im Fließtext, Rathaus, `relevanz` 3) |
| Korrigierte Bestandsmeldungen | 0 neu | Bildbeschreibung Malwettbewerb: im Repository seit 14.11. korrigiert, live weiter alt (Werkzeugpunkt, siehe Abschnitt 7) |
| Neue / geänderte Termine | 0 / 0 | |
| Termine an der Quelle bestätigt | 13 | Heimat-Info (10 Detailseiten unverändert), Ratsinfo `termine.ics` unverändert |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | Kurzmeldung, Vorlage, Erklärungsentwurf, Tagesordnung, eigene Meldung; 3 Aussagen |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Rathaus Merzenich (gemeinfrei) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-KITA-PATRONAT-2026 (neu) | „Kita-Gesellschaft der Gemeinde Merzenich überschuldet: Gemeinde soll unbegrenzt für Verluste einstehen“ (Kurzmeldung): Jahresfehlbetrag 2024 laut Abschlussentwurf 428.442,04 Euro, negatives Eigenkapital, Wirtschaftsprüfer verlangt Patronatserklärung; uneingeschränkte Erklärung, solange die Gemeinde Alleingesellschafterin ist; Verlustausgleich seit 2023 im Haushalt; Anzeige bei der Kommunalaufsicht, Monatsfrist | `inhalte/meldungen/2026-11-15-recherche-claude-morgens.json` → `/rathaus/merzenich-kita-ggmbh-fehlbetrag-2024-patronatserklaerung-gemeinde-vorschlag/` | Vorlage 136/2026 vom 21.10.2026 und Anlage „Patronatserklärung“, gelesen 09:15 Uhr; Commit `848da92d` |

Quellenausnahmen: keine Themenkarte von ChatGPT; eigener Fund aus der HFA-Tagesordnung (in der Wochenvorschau als Punkt genannt). Eine offizielle Quelle trägt die Kurzmeldung (SCHREIBREGELN Abschnitt 0). Welche Kitas die Gesellschaft betreibt, nennt die Vorlage nicht; eine Websuche ergab keine belastbare Liste, daher im Text nur „mehrere Kitas im Gemeindegebiet“. Geschäftsführung nicht genannt.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| MA-FUSSBALL-GIRB-LANGERWEHE-1411 (T2) | Ergebnis vom 14.11.; kommt in die Sportmeldung am Dienstag mit allen Spielen des Wochenendes |
| Neuland Hambach GmbH, Wirtschaftsplan (151/2026) | nur Genehmigung eines Gesellschafterbeschlusses; ohne Einsicht in den Wirtschaftsplan keine belastbare Meldung |
| Bauausschuss 12.11. | weiter keine Ergebnisse (laut ChatGPT 07:03 Uhr) |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 15.11. morgens, zuerst KORREKTUREN | bekannter Bildfehler live offen; neue Karte Fußball Girbelsrath (T2) |
| Ratsinformationssystem | Vorlagen 136/2026 (mit Anlage) und 151/2026, 09:15 Uhr; `termine.ics` 09:18 Uhr | siehe Abschnitt 1 und 3 |
| Polizei, Feuerwehr, Heimat-Info | Eil-Prüfung 06:41 Uhr; Veranstaltungskalender und zehn Detailseiten 09:18 Uhr | nichts Neues; Kalender unverändert |
| Websuche | „Kindertageseinrichtungen Merzenich gGmbH“ | nur Firmenverzeichnis, keine Liste der Einrichtungen |

## 5. Sport

Sonntag, Spieltag; Ergebnisse und Tabelle am Dienstag.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender um 09:18 Uhr gelesen (10 Einträge, dieselben IDs wie am 14.11., alle Detailseiten unverändert) und Ratsinfo-`termine.ics` (unverändert). Übrige Termine bis 29.11. am 12. bis 14.11. an ihrer Quelle bestätigt.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Pluriversum 17.11., Seniorentreffs 18./25.11. und 02.12., Senioren-Nachmittag 18.11., Gottesdienst 22.11., Adventsliedersingen 29.11. | je Slug | bestätigt | Heimat-Info | 2026-11-15 |
| HFA 19.11., Bürgewald-Ausschuss 26.11., Rat und Jugendgremium 03.12. | je Slug | bestätigt | Ratsinfo `termine.ics` | 2026-11-15 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `848da92d` (Meldung), `0839b6b8` (Kalenderpflege; Rebase-Konflikt nur in `docs/SHA256SUMS.txt`, Stand von main, Kette neu), Dokumentation folgt. Abgleich 09:17 Uhr `angestossen: true`; wp-cron HTTP 200.

Live-Nachweis 09:19 Uhr: Meldung HTTP 200, in der Nachrichten-Sitemap, in `wordpress-delivery/merzenich-aktuell-import.xml` auf GitHub (2 Treffer), oben in „Neu in Merzenich“. Bildbeschreibung im Malwettbewerb-Artikel live weiter „Polizeiwache in Nordrhein-Westfalen“: Der Abgleich zieht Alt-Texte bestehender Poolanhänge nicht nach (Ursache und Abhilfe im [Dossier 14.11. nachmittags](2026-11-14-claude-nachmittags.md), Abschnitt 7); Entscheidung Luis Kummer offen.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| merzenich-kita-ggmbh-fehlbetrag-2024-patronatserklaerung-gemeinde-vorschlag | rathaus.rat | rathaus-merzenich | Merzenich Rathaus.JPG | Public domain | am 11.11. selbst angesehen: Rathaus hinter Bäumen, keine Personen, keine Kennzeichen; die Gemeinde als Gesellschafterin und der Rat entscheiden; bewusst kein Kita-Foto mit Kindern |

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Kita-Patronatserklärung | 234 | 4 | 3 Einträge, alle mit offizieller Quelle; einer zusätzlich mit eigenem Archiv | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Mindesttakt 15.11.: zweite Meldung im Nachmittagslauf.
- Sport Dienstag 17.11.: Ergebnisse des Wochenendes (auch Girbelsrath 14.11.), Tabelle, Spielstand.
- HFA 19.11.: Empfehlungen nach der Sitzung als Folgemeldungen.
- Bauausschuss 12.11.: Ergebnisse abwarten.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
