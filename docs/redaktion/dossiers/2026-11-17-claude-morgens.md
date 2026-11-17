# Merzenich Aktuell – Morgenlauf (Claude), 17. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung 09:14 bis 09:25 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 16.11. nachmittags](2026-11-16-claude-nachmittags.md), seither neu [ChatGPT 17.11. morgens](2026-11-17-morgens.md). main vor dem Lauf: `85e2451b`. Eil-Prüfungen 16.11. 16:41, 18:41, 20:41 Uhr und 17.11. 06:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 0 | keine neue belegbare Nachricht (Abschnitt 3) |
| Korrigierte Bestandsmeldungen | 1 | Übernachtungssteuer (ChatGPT morgens) |
| Neue / geänderte Termine | 0 / 0 | |
| Termine an der Quelle bestätigt | 22 | alle Termine 17.11. bis 01.12., Stand 2026-11-17 |
| Bilder gefunden / geprüft / integriert | 0 / 0 / 0 | keine neue Meldung |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` (Paket 16.11. nachmittags) 0 Fehler, 0 Hinweise | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-KORR-UEBERNACHTUNGSSTEUER-GENEHMIGUNG-1711 (ChatGPT morgens) | Korrektur in `merzenich-uebernachtungssteuer-pruefung-haupt-finanzausschuss-19-november-2026`. Alt (2. Absatz): „Rechtlich wäre der Weg lang. Der Rat müsste eine eigene Satzung beschließen, und weil es in Nordrhein-Westfalen keine landesweite Übernachtungssteuer gibt, müssten das Kommunal- und das Finanzministerium des Landes die Einführung genehmigen. Wie hoch die Steuer ausfallen könnte, lässt die Vorlage offen. …“ Neu: Satzung des Rates; Genehmigung „nach Darstellung der Verwaltung“; Gegenposition der Stadt Hilden (landesweit zugelassen seit der Genehmigung der Kölner Satzung 2010); Vergleichswerte der Vorlage; Beispielsätze der Anlage A (5 Prozent, 2 Euro je Wohnmobilstellplatz und Nacht; zahlen die Gäste, einziehen die Betriebe). 3. Absatz: „erste Recherche“ jetzt „nach eigener Angabe mit Hilfe künstlicher Intelligenz“. Schlusssatz alt „… und der Genehmigung durch das Land“, neu „… und einer Satzung des Rates; ob zusätzlich das Land zustimmen muss, wäre dabei zu klären“. Dek und Faktenliste angepasst | `inhalte/meldungen/2026-11-16-recherche-claude-nachmittags.json` | Anlage A zu 104/2026 (2 Seiten), Hildener Vorlage SV 20/268/1 („Prüfungsergebnis der Verwaltung“), Stadt Köln (Genehmigung 09.09.2010), gelesen 09:15 Uhr; der KAG-Text auf recht.nrw.de war im Seitenquelltext nicht enthalten (Inhalt per Skript geladen), § 2 Abs. 2 daher nur nach Vorlage und ChatGPT-Zitat; zwei Prüfeinträge ergänzt (333 Wörter); Commit `3fa1783d` |
| Kalender | 22 Termine bis 01.12. an der Quelle bestätigt | 10 Termindateien | Abschnitt 6; Commit `8fdc2bc4` |

Die Korrektur trifft keine rechtliche Entscheidung: Ob Merzenich eine Genehmigung braucht, bleibt im Text ausdrücklich offen.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Korrektur ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Neuland Hambach, Wirtschaftsplan 2027/2028 (151/2026) | Vorlage vom 06.11. (2 Seiten) gelesen: Kosten „lt. Anlage“, Anlagen nicht öffentlich, vollständige Unterlagen erst nicht-öffentlich im Rat am 03.12.; keine belastbare Meldung |
| Vorschau Haupt- und Finanzausschuss 19.11. | alle öffentlichen Punkte außer 151/2026 bereits als Einzelmeldungen und in der Wochenvorschau 16.–22.11.; keine neue Sammelmeldung |
| Ausschuss Revitalisierung Bürgewald 26.11. | im Ratsinformationssystem noch keine Tagesordnung (09:18 Uhr) |
| Pluriversum heute | Gemeindebeitrag 17.11. 08:32 Uhr bestätigt „ab 18 Uhr“ in Niederzier; Termin und zwei Meldungen bereits mit Ablauf |
| Polizei 17.11. 09:00 Uhr | Radfahrer auf der B56 bei Düren an der Anschlussstelle zur A4 leicht verletzt; Ortsangabe Düren, kein Merzenich-Bezug |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 17.11. morgens, zuerst KORREKTUREN | eine neue Korrektur (Übernachtungssteuer), umgesetzt; Sport-Rangkorrektur live bestätigt; Bildfehler Malwettbewerb live weiter offen; keine neuen Karten |
| Ratsinformationssystem | Anlage A zu 104/2026 09:15 Uhr; Tagesordnung HFA, Sitzung Bürgewald-Ausschuss, Vorlage 151/2026, `termine.ics` 09:16 bis 09:18 Uhr | siehe Abschnitt 1 und 3 |
| Hilden, Köln | Ratsinformationssystem Hilden, Seite der Stadt Köln, 09:15 Uhr | Belege der Korrektur |
| Gemeinde Merzenich | Pressemitteilungen (Actions-Rohdaten vom 16.11. 23:47 Uhr) | nur bekannte Themen (Adventsfenster, Pluriversum, Krätzchensitzung, Altpapier) |
| Polizei Düren | Dienststellenliste nr/8, 06:41 und 09:21 Uhr | neu 17.11. 09:00 Uhr (Düren, B56), siehe Abschnitt 3 |
| Feuerwehr | Website und Heimat-Info-Profil, 09:21 Uhr | weiter bis 147/26 |
| Heimat-Info | Gemeindeseite 09:21 Uhr; Veranstaltungskalender und zehn Detailseiten 09:19 Uhr | Pluriversum-Beitrag 08:32 Uhr; Kalender unverändert |
| Kreis Düren | Presse-Index 09:21 Uhr | nichts Neues seit „Wifö on Tour“ (16.11., Niederzier) |

## 5. Sport

Dienstagsprüfung: Ergebnisse des Wochenendes und Spielstand bereits am 16.11. veröffentlicht und korrigiert; laut ChatGPT morgens Mannschaftsseiten ohne neue Spiele. Nächstes Spiel mit Merzenicher Beteiligung: Girbelsrath bei Viktoria Arnoldsweiler II, Fr. 20.11., 19.30 Uhr.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender um 09:19 Uhr gelesen (dieselben 10 IDs wie am 16.11. nachmittags, alle Detailseiten unverändert), Ratsinfo-`termine.ics` unverändert. Kulturkalender 16 und Amtsblätter 08, 11 und 12 neu geladen und bytegleich mit den Abrufen vom 16.11.; Kreis Düren (Mental Load, Seniorenweihnacht), Terminplan Schützen Morschenich und Beiträge des Heimatvereins (Adventmarkt) und der Gemeinde (Pluriversum) im Text unverändert.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| alle 22 Termine vom 17.11. bis 01.12. (Pluriversum, Seniorentreffs, Workshop, Mental Load, Sprechstunde, HFA, Modelleisenbahn, Gottesdienst, Seniorenweihnacht, Bürgewald-Ausschuss, Tannenbaumschmücken, Adventfeier, Adventmarkt, Weinbergschnecken, Adventsfenster Bürgewaldzentrum, Golzheimer Adventskalender, Adventsliedersingen, Amtsblatt-Abgabeschluss) | je Slug | bestätigt | jeweilige Quelle wie am 16.11. | 2026-11-17 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `3fa1783d` (Korrektur), `8fdc2bc4` (Kalenderpflege), Dokumentation folgt. Abgleich 09:17 Uhr `angestossen: true`, wp-cron HTTP 200.

Live-Nachweis 09:21 Uhr: korrigierte Fassung live (Treffer „Stadt Hilden“ bzw. „Nach Darstellung der Verwaltung“), ebenso in `wordpress-delivery/merzenich-aktuell-import.xml` auf GitHub. Bildbeschreibung im Malwettbewerb-Artikel live weiter „Polizeiwache in Nordrhein-Westfalen“: Werkzeugpunkt, Entscheidung Luis Kummer offen.

## 8. Bildprüfung

Keine neue oder bildrelevant geänderte Meldung in diesem Lauf (die Korrektur betrifft nur Text; Bild der Übernachtungssteuer-Meldung am 16.11. geprüft, siehe [Dossier 16.11. nachmittags](2026-11-16-claude-nachmittags.md), Abschnitt 8).

## 9. Offene Punkte für den nächsten Lauf

- Mindesttakt 17.11.: noch keine Meldung; Nachmittagslauf.
- HFA 19.11.: Empfehlungen nach der Sitzung als Folgemeldungen.
- Pluriversum 17.11.: nach dem Abend keine Termin- oder Artikeländerung mehr nötig.
- Bürgewald-Ausschuss 26.11. und Rat 03.12.: Tagesordnungen abwarten.
- Bauausschuss 12.11.: Ergebnisse abwarten.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
