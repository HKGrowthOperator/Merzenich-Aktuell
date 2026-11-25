# Merzenich Aktuell – Morgenlauf (Claude), 25. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung ab 09:14 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 24.11. nachmittags](2026-11-24-claude-nachmittags.md), seither neu [ChatGPT 25.11. morgens](2026-11-25-morgens.md) (Commit `94935ff4`). main vor dem Lauf: `94935ff4`. Eil-Prüfungen 16:41, 18:41 und 20:41 Uhr (24.11.) sowie 06:41 Uhr (25.11.) ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Bau-Turbo Fassung 2.0, 252 Wörter |
| Aktualisierte Bestandsmeldungen | 2 | Bau-Turbo (5.11.) und Bürgewald-Ausschuss (18.11.), Korrekturauftrag ChatGPT |
| Neue / geänderte Termine | 0 / 0 | |
| Termine an der Quelle bestätigt | 21 | alle Termine 25.11. bis 09.12., Stand 2026-11-25 |
| Bilder gefunden / geprüft / integriert | 1 / 1 / 1 | Rathaus Merzenich aus dem Pool (Abschnitt 8) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/eigenformulierung.mjs` für 2026-11-25 morgens, 2026-11-05 morgens und 2026-11-18 morgens je 0 Fehler (Hinweise: Ortsteilaufzählung, Quelle gesetze-im-internet.de HTTP 503, je 8 bis 9 gleiche Wörter mit Vorlagen); `qa/redaktion-lauf.mjs --datum 2026-11-25 --lauf morgens` 0 Fehler | |

Mindesttakt 25.11.: bisher 1 Meldung (dieser Lauf).

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-KORR-BAUTURBO-ANLAGEN-2511 (ChatGPT 25.11. morgens) | Bau-Turbo-Artikel vom 5.11.: Im Zuständigkeitssatz ergänzt „in der überarbeiteten Fassung vom 20. November sind es mehr als sechs Wohnungen (siehe Nachtrag unten)“. Dazu der Vermerk „Aktualisiert am 25. November: …“ (Version 2.0, mehr als sechs statt zehn Wohnungen, nur vier Ortsteile, eigene Leitlinien für Bürgewald 2027, Beschluss offen) sowie Faktenliste, Prüfeintrag und drei Quellen. Bürgewald-Ausschuss-Artikel vom 18.11.: „tragen weiter die Versionsangabe 1.0“ → „trugen weiter … inzwischen gibt es eine überarbeitete Fassung“; Schwelle ergänzt; „lässt die Ergänzung offen“ → „ließ … zunächst offen; die überarbeitete Fassung nimmt Bürgewald ausdrücklich aus“; Vermerk „Aktualisiert am 25. November“. Der frühere Text gab den damaligen Entwurf richtig wieder und bleibt erkennbar. | `inhalte/meldungen/2026-11-05-recherche-claude-morgens.json`, `inhalte/meldungen/2026-11-18-recherche-claude-morgens.json` | Neue Anlage 2 (Ver. 2.0 vom 20.11.2026) um 09:15 Uhr gelesen, Seiten 1 und 2 als Bild angesehen: In Nr. 2.1 ist „10“ durchgestrichen und „6“ rot eingesetzt, dazu der rote Einschub zu den vier Ortsteilen und Bürgewald. Textvergleich mit der alten Anlage 2: sonst nur Seitenumbruch geändert (0,1 ha, 0,15 ha, 10 WE Außenbereich, 5 WE, 0,10 ha und 8 WE unverändert). 142/2026, 1. Ergänzung, S. 2 und 3 (Bürgewald 2027, Abstimmung mit Kreis und NRW.URBAN als Angabe der Verwaltung). Artikel 1008 und 1003 Wörter. Commit `15b2abba` |
| MA-BAUTURBO-FASSUNG2-2511 (neu, nach Themenkarte MA-BAUTURBO-142-2026) | Kurzmeldung „Bau-Turbo in Merzenich: Politik soll schon bei mehr als sechs Wohnungen entscheiden, Bürgewald bekommt eigene Regeln“, rathaus, relevanz 3, 252 Wörter. Aus der amtlichen Quelle (Gemeinde, Ratsinformationssystem), jede Angabe dort gelesen. Die Abstimmung mit Kreis und NRW.URBAN ist als Angabe der Verwaltung gekennzeichnet. Kein Beschluss behauptet. ChatGPT hatte eine eigene Meldung als T2 gesperrt, weil eine unabhängige Bestätigung fehlt. Nach SCHREIBREGELN trägt eine offizielle Quelle eine Kurzmeldung: Belegt werden hier nur die Inhalte des Gemeindeentwurfs, nicht ihre Wirkung. | `inhalte/meldungen/2026-11-25-recherche-claude-morgens.json` → `/rathaus/bau-turbo-merzenich-leitlinien-fassung-2-mehr-als-sechs-wohnungen-buergewald-ausgenommen/` | wie oben, dazu Bekanntmachung Gemeinderat 3.12. (TOP 9.2) und Tagesordnung des Bürgewald-Ausschusses 26.11. (09:24 Uhr unverändert). Drei Prüfeinträge. Commit `edcb79ff` |
| Kalenderpflege 14 Tage | 21 Termine 25.11. bis 09.12. bestätigt, Stand 2026-11-25 | 14 Termindateien | Abschnitt 6. Commit `525afc62` |

## 2. Sofort sichtbar

Alles ohne Freigabe. Zur Freigabe liegt nichts. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Rechtliche Folgen und Einordnung des Bau-Turbos über die Leitlinien hinaus | nicht neu recherchiert; Verweis auf den Artikel vom 5.11. |
| Wunschbaum (MA-WUNSCHBAUM-2111) | weiter nur Fanclub-Angaben, T2 |
| Girbelsrath-Ergebnis 20.11. | FUSSBALL.DE um 09:19 Uhr: „Letztes Spiel“ weiter ohne lesbares Ergebnis |
| DWD: Frost Stufe 2 | keine Unwetterlage |
| HFA 19.11., Stimmverhältnisse | Sitzungsseite 09:19 Uhr unverändert, ohne Niederschrift |
| Malwettbewerb-Bildbeschreibung (MA-KORR-MALWETTBEWERB-BILDBESCHREIBUNG-1411) | Werkzeugpunkt, Entscheidung Luis Kummer offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 25.11. morgens, zuerst KORREKTUREN | ein Aktualisierungsauftrag (Bau-Turbo, zwei Artikel), umgesetzt; Ratsmeldung, Haushalts- und Abgabennachträge, Wochenvorschau und Termin dort live bestätigt |
| Ratsinformationssystem | Anlagen 2 und 3 und 1. Ergänzung zu 142/2026, alte Anlage 2 (09:15 Uhr); HFA-Seite, Ratssitzung 3.12., Vorlagenliste (09:19 Uhr); Bürgewald-Ausschuss und `termine.ics` (09:24 Uhr) | Aktualisierung und Meldung; sonst unverändert |
| Polizei Düren | Dienststellenliste nr/8 09:19 Uhr | keine neue Meldung seit dem 24.11., 10:00 Uhr |
| Feuerwehr | Website (bis 147/26) und Heimat-Info-Profil (bis 149/26) 09:19 Uhr | nichts Neues |
| Heimat-Info | Gemeindeseite 09:19 Uhr; Veranstaltungskalender, elf Detailseiten, drei Beiträge 09:24 Uhr | nichts Neues |
| Kreis Düren | Presse-Index 09:19 Uhr | keine neue Mitteilung |
| Deutscher Wetterdienst | `warnings.json` 09:19 Uhr | Frost Stufe 2 |
| Gemeinde Merzenich | Amtsblätter 11 und 12, Kulturkalender 16 09:24 Uhr; Amtsblatt 13 09:19 Uhr | bytegleich; Amtsblatt 13 weiter HTTP 404 |
| FUSSBALL.DE | Mannschaftsseite Girbelsrath 09:19 Uhr | Ergebnis weiter nicht lesbar; nächstes Spiel Do 26.11. in Schlich |

## 5. Sport

Mittwoch, kein Pflichttag. Girbelsrath spielt am Do 26.11. um 19.30 Uhr in Schlich; das Ergebnis vom 20.11. ist weiter offen.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 09:24 Uhr gelesen: dieselben zehn IDs wie am 24.11. nachmittags, alle zehn Detailseiten und die Weihnachtsfeier Morschenich (`1e0ecb73`) im Veranstaltungsteil textgleich. `termine.ics` ist bis auf den technischen Zeitstempel gleich. Amtsblätter 11 und 12 und Kulturkalender 16 sind bytegleich. Schützen Morschenich, beide Ausstellungsbeiträge, Adventsfenster-Beitrag und die Tagesordnung des Bürgewald-Ausschusses sind textgleich.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| alle 21 Termine vom 25.11. bis 09.12. | je Slug | bestätigt | jeweilige Quelle | 2026-11-25 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `15b2abba` (Aktualisierungen Bau-Turbo), `edcb79ff` (Meldung), `525afc62` (Kalenderpflege), `2a643e2a` (Dokumentation), Live-Nachweis in diesem Commit. Abgleich 09:18 Uhr `angestossen: true`, 09:22 Uhr `angestossen: false`. Die Meldung war trotzdem schon um 09:26 Uhr abrufbar. Import-XML auf GitHub raw um 09:27 Uhr mit dem neuen Slug.

Live-Nachweis 09:27 Uhr, jeweils HTTP 200:

- `/rathaus/bau-turbo-merzenich-leitlinien-fassung-2-mehr-als-sechs-wohnungen-buergewald-ausgenommen/`: Titel vorhanden, Teilbild `aktuell-05-85b961cd5e` (Rathaus); in `news-sitemap.xml` enthalten; oben in „Neu in Merzenich“ auf der Startseite (09:20 Uhr, Rathaus & Politik).
- `/rathaus/bau-turbo-merzenich-leitlinien-wohnungsbau-ohne-bebauungsplan-bauausschuss-2026/` und `/rathaus/buergewald-ausschuss-merzenich-26-november-2026-solarpark-a4-bau-turbo-vermarktung-erster-abschnitt/`: je mit „Aktualisiert am 25. November“.

Die Fassung 2.0 lag seit dem 24.11. im Ratsinformationssystem (Exportdatum); gelesen habe ich sie um 09:15 Uhr, live war die Meldung um 09:26 Uhr.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| bau-turbo-merzenich-leitlinien-fassung-2-mehr-als-sechs-wohnungen-buergewald-ausgenommen | rathaus.rat | rathaus-merzenich | Merzenich Rathaus HDR.jpg | CC BY-SA 3.0 | selbst angesehen (Pooldatei `aktuell-05-85b961cd5e`, 09:21 Uhr): Rathaus Merzenich mit Schriftzug „RATHAUS“; Leitlinien der Gemeinde, über die der Rat entscheidet. Keine Personen, keine lesbaren Kennzeichen |

## 9. Offene Punkte für den nächsten Lauf

- Bürgewald-Ausschuss 26.11.: Ergebnisse (Solarpark A4, Bau-Turbo, nichtöffentliche Vermarktungspunkte nur soweit veröffentlicht); Ausstellungseröffnung 27.11.
- Haushalt 2027: Erweiterung der Ratstagesordnung oder neuer Zeitplan; HFA-Niederschrift.
- Girbelsrath-Ergebnis 20.11. und Spiel 26.11.; Wunschbaum (T2); Amtsblatt 13.
- Entscheidungspunkte für Luis Kummer unverändert: dwd.de in `OFFIZIELL`; Werkzeugpunkte Alt-Text, Feuerwehr „Hund“, gleichzeitige Termine, Kalender Seite 2, Ortsfelder, Dublettenvergleich der Ratsinfo-URLs in `qa/redaktion-lauf.mjs`.
