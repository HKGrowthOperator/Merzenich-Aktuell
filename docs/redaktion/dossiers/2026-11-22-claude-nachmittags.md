# Merzenich Aktuell – Nachmittagslauf (Claude), 22. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung ab 14:44 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 22.11. morgens](2026-11-22-claude-morgens.md), seither neu [ChatGPT 22.11. mittags](2026-11-22-mittags.md) (Commit `154a564e`) und die Automatik von 13:27 Uhr (nur Umkreis der Polizei). main vor dem Lauf: `34038c75`. Eil-Prüfungen 10:41 und 12:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 0 | **Nullfund:** keine belegbare neue Nachricht mit Ortsbezug (Abschnitt 3 und 4) |
| Korrigierte Bestandsmeldungen | 1 | Wochenvorschau 23. bis 29. November (eigener Fund) |
| Neue / geänderte Termine | 0 / 0 | |
| Termine an der Quelle bestätigt | 20 | alle Termine 22.11. bis 06.12., Stand 2026-11-22 bleibt |
| Bilder gefunden / geprüft / integriert | 0 / 0 / 0 | Korrektur betrifft nur Text |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs --datum 2026-11-22 --lauf nachmittags` 0 Fehler; `qa/eigenformulierung.mjs --datum 2026-11-21 --lauf morgens` (korrigiertes Paket) 0 Fehler, 0 Hinweise | |

Mindesttakt 22.11. (Sonntag): 0 Meldungen. An diesem Sonntag gab es in keiner Pflichtquelle eine belegbare neue Nachricht mit Bezug zu Merzenich; nichts wurde gestreckt oder aus alten Quellen neu aufgelegt.

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-WOCHENVORSCHAU-2026-48 (eigene Korrektur) | Absatz zum Sonntag alt: „Auf dem Platz spielen am Nachmittag beide Merzenicher Fußballvereine zu Hause: …“ Das war ungenau, weil auch der FC Rhenania Girbelsrath und der SV Morschenich in der Gemeinde spielen. Neu: „Auf dem Platz haben am Nachmittag der FC Golzheim und der SC 1919 Merzenich Heimspiele: …“, ergänzt um das Freundschaftsspiel des SV Morschenich beim SSV Weilerswist (So 29.11., 14.30 Uhr). Im Donnerstagsabsatz ergänzt: FC Rhenania Girbelsrath beim FC Viktoria Schlich (Do 26.11., 19.30 Uhr, Kreisliga B). Faktenliste ergänzt, Vermerk „Korrigiert am 22. November: …“ | `inhalte/meldungen/2026-11-21-recherche-claude-morgens.json` → `/leben/merzenich-23-bis-29-november-2026-buergewald-ausschuss-tannenbaumschmuecken-adventmarkt/` | FUSSBALL.DE-Mannschaftsseiten Girbelsrath (14:44 Uhr) und Morschenich (14:45 Uhr), jeweils „Nächstes Spiel“; neuer Prüfeintrag und zwei weitere Quellen. Aufgefallen bei der Suche nach dem Girbelsrath-Ergebnis. 895 Wörter. Commit `2306ee1b` |

## 2. Sofort sichtbar

Korrektur ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Ergebnis Viktoria Arnoldsweiler II – FC Rhenania Girbelsrath (20.11.) | FUSSBALL.DE führt das Spiel als „Letztes Spiel“, das Ergebnis steht aber nur in nicht lesbarer Symbolschrift; ein Spielbericht fehlt (jüngste Mannschafts-News: Vorbericht vom 18.11.). Tabelle Kreisliga B Staffel 2: Platz 13, 3 Punkte, 14:34 Tore. Ergebnis im Montagslauf, wenn ein lesbarer Bericht vorliegt |
| DWD-Warnung vor Frost für den Kreis Düren, 22.11. 20 Uhr bis 23.11. 11 Uhr, Stufe 2 (Stand 14:41 Uhr) | einfache Wetterwarnung, keine Unwetterlage; keine eigene Meldung |
| Polizei (seit dem Morgen keine neue Meldung; jüngste drei vom 22.11. betreffen Düren) | kein Ortsbezug |
| Kreis Düren | Presse-Index 14:44 Uhr: nur Familienkalender vom 20.11. |
| Ergebnisse HFA 19.11. | Sitzungsseite um 14:44 Uhr weiter ohne Niederschrift oder Beschlusstexte; Vorlagenliste unverändert (höchste Nummer 151/2026); Ratssitzung 3.12. ohne Tagesordnung |
| Malwettbewerb-Bildbeschreibung (MA-KORR-MALWETTBEWERB-BILDBESCHREIBUNG-1411) | Werkzeugpunkt (Alt-Text bestehender Poolanhänge im Abgleich), Entscheidung Luis Kummer offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 22.11. mittags, zuerst KORREKTUREN | kein neuer Korrekturauftrag; Korrektur 149/26 und Grammatik 148/26 live bestätigt; keine neue Themenkarte |
| Polizei Düren | Dienststellenliste nr/8 14:44 Uhr | keine neue Meldung seit 08:47 Uhr |
| Feuerwehr | Website (bis 147/26) und Heimat-Info-Profil (bis 149/26) 14:44 Uhr | kein neuer Einsatz |
| Deutscher Wetterdienst | `warnings.json` 14:44 Uhr (Datenstand 14:41 Uhr) | Frostwarnung Stufe 2 für die Nacht (Abschnitt 3) |
| Kreis Düren | Presse-Index 14:44 Uhr HTTP 200 | nichts mit Ortsbezug |
| Ratsinformationssystem | HFA-Seite, Ratssitzung 3.12., Vorlagenliste 14:44 Uhr; `termine.ics` 14:48 Uhr | nichts Neues |
| Heimat-Info | Gemeindeseite 14:44 Uhr; Veranstaltungskalender und elf Detailseiten 14:48 Uhr | kein neuer Beitrag seit dem Feuerwehrbericht 149/26; Kalender unverändert |
| FUSSBALL.DE | Mannschaftsseiten Girbelsrath und Morschenich 14:44 bis 14:45 Uhr | Korrektur der Wochenvorschau |
| Gemeinde Merzenich | Amtsblätter 11 und 12, Kulturkalender 16 unter `/bindata/` 14:48 Uhr | bytegleich |

## 5. Sport

Sonntag, kein Pflichttag. Girbelsrath (20.11.): Ergebnis nicht lesbar (Abschnitt 3). Nächste Spiele jetzt vollständig in der Wochenvorschau: Girbelsrath Do 26.11. in Schlich, Golzheim und SC Merzenich So 29.11. zu Hause, Morschenich So 29.11. Freundschaftsspiel in Weilerswist.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 14:48 Uhr gelesen: dieselben zehn IDs wie am Morgen, alle zehn Detailseiten und die Weihnachtsfeier Morschenich (`1e0ecb73`) im Veranstaltungsteil textgleich. `termine.ics` bis auf den technischen Zeitstempel gleich. Amtsblätter 11 und 12 und Kulturkalender 16 bytegleich; Schützen Morschenich, Ausstellungs- und Adventsfenster-Beitrag textgleich.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| alle 20 Termine vom 22.11. bis 06.12. | je Slug | erneut bestätigt, Stand vom Morgen bleibt | jeweilige Quelle | 2026-11-22 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `2306ee1b` (Korrektur Wochenvorschau), Dokumentation in diesem Commit. Abgleich 14:47 Uhr `angestossen: true`.

Live-Nachweis 14:48 Uhr: `/leben/merzenich-23-bis-29-november-2026-buergewald-ausschuss-tannenbaumschmuecken-adventmarkt/` HTTP 200 mit „Viktoria Schlich“, „SSV Weilerswist“ und „Korrigiert am 22. November“.

## 8. Bildprüfung

Keine neue oder bildrelevant geänderte Meldung. Bild der Wochenvorschau siehe [Dossier 21.11. morgens](2026-11-21-claude-morgens.md), Abschnitt 8.

## 9. Offene Punkte für den nächsten Lauf

- Montag (Sport-Pflichttag): Ergebnis Girbelsrath vom 20.11., Tabellen.
- HFA 19.11.: Ergebnisse als Folgemeldungen, sobald Niederschrift oder Beschlusstexte vorliegen; Gemeinderat 3.12.: Tagesordnung abwarten.
- Mindesttakt 23.11.: zwei Meldungen, wenn belegbar.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
