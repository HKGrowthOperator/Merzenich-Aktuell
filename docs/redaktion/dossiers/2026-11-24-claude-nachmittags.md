# Merzenich Aktuell – Nachmittagslauf (Claude), 24. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung ab 14:44 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 24.11. morgens](2026-11-24-claude-morgens.md), seither neu [ChatGPT 24.11. mittags](2026-11-24-mittags.md) (Commit `a7cfc20a`). main vor dem Lauf: `0f7556eb`. Eil-Prüfungen 10:41 und 12:41 Uhr ohne Eilfund (fünf neue Polizeimeldungen ohne Merzenicher Bezug).

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Standardartikel Gemeinderat 3.12., 1.115 Wörter |
| Korrigierte oder aktualisierte Bestandsmeldungen | 3 | Wochenvorschau (Korrekturauftrag ChatGPT), Haushalt-2027-Zeitplan, Abgaben 2027 |
| Neue / geänderte Termine | 0 / 1 | Gemeinderat 3.12.: Tagesordnung eingetragen |
| Termine an der Quelle bestätigt | 18 | alle Termine 24.11. bis 08.12., Stand 2026-11-24 bleibt |
| Bilder gefunden / geprüft / integriert | 1 / 1 / 1 | Rathaus Merzenich aus dem Pool (Abschnitt 8) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/eigenformulierung.mjs --datum 2026-11-24 --lauf nachmittags` 0 Fehler, 0 Hinweise; `--datum 2026-11-21 --lauf morgens` (Wochenvorschau) 0 Fehler, 0 Hinweise; `qa/redaktion-lauf.mjs --datum 2026-11-24 --lauf nachmittags` 0 Fehler | |

Mindesttakt 24.11.: 1 Meldung (dieser Lauf). Am Morgen gab es keine belegbare Nachricht. Die Ratstagesordnung erschien erst am 24.11. (Bekanntmachung mit diesem Datum, Vorlagen „exportiert: 24.11.2026“, um 09:18 Uhr noch nicht vorhanden).

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-GEMEINDERAT-03122026-TO (neu, eigener Fund) | Standardartikel „Gemeinderat Merzenich am 3. Dezember: Hundesteuer soll stärker steigen, Haushaltsentwurf fehlt auf der Tagesordnung“, ressort rathaus, relevanz 2, 1.115 Wörter. Kern 1: Laut erster Ergänzung zu 101/2026 (Datum 22.11., rot markiert) legte der HFA am 19.11. für den Rat höhere Hundesteuersätze fest: ein Hund 60/72/84 Euro (2027/2028/ab 2029) statt 59/64/69 Euro; zwei Hunde je Tier bis 102 statt 87 Euro, drei und mehr bis 114 statt 99 Euro; gefährliche Hunde unverändert. Benannter Widerspruch: Die neuen Einnahmesummen (82.512 Euro ab 2029) enthalten die Steuer für den einen gefährlichen Hund nicht mehr. Kern 2: Laut M128/2026 war die Einbringung des Haushaltsentwurfs 2027 für den 3.12. vorgesehen; die Bekanntmachung vom 24.11. führt sie nicht auf, und `termine.ics` nennt keine weitere Ratssitzung im Dezember. Dazu neue Vorlagen: Entwässerungssatzung 160, Gebührensatzung 153, Kleinkläranlagen 139, Unterkünfte 162, Jahresabschluss 2025 (Fehlbetrag 2.495.271,29 Euro, 132), Dorfauto (149 mit LEADER-Bescheid). Benannter Widerspruch beim Dorfauto: Förderende laut Vorlage 30.06.2029, Durchführung laut Bescheid bis 01.06.2029. Den Namen des Schiedsmanns habe ich nicht übernommen. | `inhalte/meldungen/2026-11-24-recherche-claude-nachmittags.json` → `/rathaus/gemeinderat-merzenich-3-dezember-2026-tagesordnung-hundesteuer-haushaltsentwurf-dorfauto/` | Tagesordnungsseite des Rats um 14:48 Uhr (bis 09:18 Uhr ohne Tagesordnung), Bekanntmachungs-PDF, 101/2026 1. Ergänzung (Seiten 2 und 3 als Bild angesehen, Rot und Durchgestrichen getrennt), M128/2026, 132, 139, 149 mit Zuwendungsbescheid, 153, 160, 162 und M154, gelesen 14:50 bis 14:55 Uhr. Neun Prüfeinträge, alle Belege im Ratsinformationssystem. Commit `2fbd0ffb` |
| MA-KORR-WOCHENVORSCHAU-MORSCHENICH-2411 (ChatGPT 24.11. mittags) | Sonntagsabsatz alt: „Der SV Morschenich tritt ebenfalls um 14.30 Uhr zu einem Freundschaftsspiel beim SSV Weilerswist an.“ Neu: „Das für 14.30 Uhr angesetzte Freundschaftsspiel des SV Morschenich beim SSV Weilerswist führt FUSSBALL.DE inzwischen als abgesetzt; einen Grund oder einen neuen Termin nennt der Eintrag nicht.“ Dazu der Vermerk „Aktualisiert am 24. November: …“ und ein neuer Faktenpunkt | `inhalte/meldungen/2026-11-21-recherche-claude-morgens.json` | FUSSBALL.DE um 14:44 Uhr: Mannschaftsseite SV Morschenich, Spielseite und Mannschaftsseite SSV Weilerswist zeigen alle drei „Absetzung“. Gegenprobe 14:45 Uhr: Golzheim, SC 1919 und Girbelsrath ohne Vermerk. FUSSBALL.DE ist der amtliche Spielbetrieb des Verbands (in `OFFIZIELL`). Die ChatGPT-Einstufung T2 betraf eine unabhängige Bestätigung des Ausfalls. Ich schreibe nur, was FUSSBALL.DE führt, ohne Grund und ohne Ersatztermin. 972 Wörter. Commit `d80f62bb` |
| MA-KORR-HAUSHALTSZEITPLAN-RAT-1911 (Folge) | Haushalt-2027-Zeitplan: Vermerk „Aktualisiert am 24. November“: Die Einbringung steht nicht in der Tagesordnung, keine weitere Ratssitzung im Dezember, Verwaltung hat nichts mitgeteilt. Prüfeintrag und zwei Quellen ergänzt | `inhalte/meldungen/2026-10-13-recherche-chatgpt.json` | Bekanntmachung, `termine.ics` 14:51 Uhr. Commit `2fbd0ffb` |
| MA-ABGABEN-2027 (Folge) | Abgaben 2027: Vermerk „Aktualisiert am 24. November“ mit den höheren HFA-Sätzen zur Hundesteuer und dem fehlenden Haushaltsentwurf; Prüfeintrag, zwei Quellen | `inhalte/meldungen/2026-11-11-recherche-claude-morgens.json` | wie oben. Commit `2fbd0ffb` |
| Termin Gemeinderat 3.12. | Beschreibung mit Themen, Absatz „Aktualisiert am 24. November“, Bekanntmachung als Quelle und erster Link | `inhalte/termine/2026-10-25-recherche-claude.json` | Bekanntmachung vom 24.11. Commit `02e57865` |

## 2. Sofort sichtbar

Alle Änderungen ohne Freigabe. Zur Freigabe liegt nichts. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Abstimmungsergebnisse des HFA vom 19.11. | Die Ergänzung zu 101/2026 nennt die neuen Sätze, aber nicht das Stimmverhältnis. Eine Niederschrift war auf der Sitzungsseite um 14:48 Uhr nicht vorhanden. Die übrigen HFA-Vorlagen haben keine Fassung nach der Sitzung |
| Rüge der FDP/SPD-Fraktion zur Rede des Personalratsvorsitzenden (M152/2026) | Den Anhang habe ich nicht gelesen, und es geht um eine erkennbare Person. Ohne Volltextprüfung kommt das nicht in den Artikel |
| Bau-Turbo 142/2026, 1. Ergänzung „aktualisiert: 24.11.2026“ | Die Vorlage selbst trägt weiter das Datum 17.11.; neu sind die Anlagen 2 und 3 (Stand 20.11.). Welche Inhalte sich geändert haben, prüfe ich zum Bürgewald-Ausschuss am 26.11. |
| Wunschbaum (MA-WUNSCHBAUM-2111) | weiter nur Fanclub-Angaben, T2 |
| Girbelsrath-Ergebnis 20.11. | FUSSBALL.DE um 14:45 Uhr: „Letztes Spiel“ weiter ohne lesbares Ergebnis |
| DWD: Frostwarnung Stufe 2 (Datenstand 14:42 Uhr) | keine Unwetterlage |
| Malwettbewerb-Bildbeschreibung (MA-KORR-MALWETTBEWERB-BILDBESCHREIBUNG-1411) | Werkzeugpunkt, Entscheidung Luis Kummer offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 24.11. mittags, zuerst KORREKTUREN | ein neuer Korrekturauftrag (Morschenich), umgesetzt; Frühstück und Ausstellung dort live bestätigt |
| Polizei Düren | Dienststellenliste nr/8 14:48 Uhr | keine neue Meldung seit 10:00 Uhr (fünf Meldungen bereits in der Eil-Prüfung 10:41 Uhr gelesen) |
| Feuerwehr | Website (bis 147/26) und Heimat-Info-Profil (bis 149/26) 14:48 Uhr | nichts Neues |
| Heimat-Info | Gemeindeseite 14:48 Uhr; Veranstaltungskalender, elf Detailseiten, Ausstellungsbeiträge 14:59 Uhr | nichts Neues |
| Kreis Düren | Presse-Index 14:48 Uhr | keine neue Mitteilung |
| Deutscher Wetterdienst | `warnings.json` 14:48 Uhr | Frost Stufe 2 |
| Ratsinformationssystem | Tagesordnung Gemeinderat 3.12. 14:48 Uhr (neu), Vorlagenliste (neu: 139, 149, 153, 160, 162), HFA-Seite (unverändert, ohne Niederschrift), 17 Dokumente 14:50 bis 14:55 Uhr, `termine.ics` 14:51 Uhr, Tagesordnung Bürgewald-Ausschuss 14:59 Uhr | Meldung (Abschnitt 1) |
| Gemeinde Merzenich | Amtsblätter 11 und 12, Kulturkalender 16 14:59 Uhr; Amtsblatt 13 14:48 Uhr | bytegleich; Amtsblatt 13 weiter HTTP 404 |
| FUSSBALL.DE | Mannschaftsseiten SV Morschenich, SSV Weilerswist, Spielseite 14:44 Uhr; SC 1919, FC Golzheim, Girbelsrath 14:45 Uhr | Korrektur Wochenvorschau |

## 5. Sport

Abgesetzt: Freundschaftsspiel SSV Weilerswist – SV Morschenich (So 29.11., 14.30 Uhr), laut FUSSBALL.DE. Unverändert angesetzt: FC Viktoria Schlich – FC Rhenania Girbelsrath (Do 26.11., 19.30 Uhr), FC Golzheim – Borussia Freialdenhoven (So 29.11., 14.30 Uhr), SC 1919 Merzenich – SV SW Huchem-Stammeln (So 29.11., 15 Uhr). Das Freundschaftsspiel des FC Golzheim in Berrendorf (Fr 20.11.) steht ebenfalls als „Absetzung“; es lag vor der Wochenvorschau und stand in keinem Artikel.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 14:59 Uhr gelesen: dieselben zehn IDs wie am Morgen, alle zehn Detailseiten und die Weihnachtsfeier Morschenich (`1e0ecb73`) im Veranstaltungsteil textgleich. `termine.ics` (14:51 Uhr) ist bis auf den technischen Zeitstempel gleich. Amtsblätter 11 und 12 und Kulturkalender 16 sind bytegleich. Schützen Morschenich, beide Ausstellungsbeiträge und der Adventsfenster-Beitrag sind textgleich. Bei der Tagesordnung des Bürgewald-Ausschusses wurden nur die Anlagen zu 142/2026 aktualisiert; Termin, Uhrzeit und Ort sind gleich.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Gemeinderat 3.12. | gemeinderat-sitzung-2026-12-03 | ergänzt (Tagesordnung, Abschnitt 1) | Bekanntmachung vom 24.11. | 2026-11-24 |
| übrige 17 Termine vom 25.11. bis 06.12. | je Slug | erneut bestätigt, Stand vom Morgen bleibt | jeweilige Quelle | 2026-11-24 |

**Neu:** keiner. **Geändert:** einer (Ergänzung, keine Zeit- oder Ortsänderung). **Abgesagt:** keiner in der Terminliste. Das abgesetzte Freundschaftsspiel stand nur in der Wochenvorschau, nicht als Termin.

## 7. QA, Commit, Abgleich

Commits: `d80f62bb` (Korrektur Wochenvorschau), `2fbd0ffb` (Meldung und zwei Aktualisierungen), `02e57865` (Termin Gemeinderat), `d33e9b60` (Dokumentation), Live-Nachweis in diesem Commit. Abgleich 14:48 Uhr `angestossen: true`, um 14:57 und 15:00 Uhr `angestossen: false` (planmäßiger Abgleich 15:05 Uhr), 15:02 Uhr `angestossen: true`. Import-XML auf GitHub raw um 15:03 Uhr mit dem neuen Slug.

Live-Nachweis 15:10 Uhr, jeweils HTTP 200:

- `/rathaus/gemeinderat-merzenich-3-dezember-2026-tagesordnung-hundesteuer-haushaltsentwurf-dorfauto/`: Titel vorhanden, Teilbild `aktuell-01-0b6bcf033a` (Rathaus); in `news-sitemap.xml` enthalten; auf der Startseite oben in „Neu in Merzenich“ (14:55 Uhr, Rathaus & Politik).
- `/leben/merzenich-23-bis-29-november-2026-buergewald-ausschuss-tannenbaumschmuecken-adventmarkt/`: mit „Aktualisiert am 24. November“ und „als abgesetzt“.
- `/rathaus/haushalt-2027-zeitplan-merzenich/` und `/rathaus/merzenich-abgaben-2027-abwassergebuehren-hundesteuer-grundsteuer-b-1077-vorschlag-verwaltung/`: je mit „Aktualisiert am 24. November“.
- `/termine/gemeinderat-sitzung-2026-12-03/`: mit der neuen Beschreibung („Hebesätze 2027“).

Die Bekanntmachung ist auf den 24.11. datiert; ich habe sie um 14:48 Uhr gefunden (um 09:18 Uhr gab es noch keine Tagesordnung). Live war die Meldung 22 Minuten nach dem Fund.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| gemeinderat-merzenich-3-dezember-2026-tagesordnung-hundesteuer-haushaltsentwurf-dorfauto | rathaus.rat | rathaus-merzenich | Merzenich Rathaus.JPG | Public domain | selbst angesehen (Pooldatei `aktuell-01-0b6bcf033a`, 14:57 Uhr): Rathaus Merzenich von der Straße aus, Gebäude, in dem der Rat am 3.12. tagt. Keine Personen, keine lesbaren Kennzeichen |

## 9. Offene Punkte für den nächsten Lauf

- Bürgewald-Ausschuss 26.11.: Ergebnisse und die aktualisierten Anlagen zu 142/2026.
- Haushalt 2027: Erweiterung der Tagesordnung oder neuer Zeitplan beobachten; HFA-Niederschrift abwarten.
- Girbelsrath-Ergebnis, Wunschbaum (T2), Amtsblatt 13.
- Entscheidungspunkte für Luis Kummer: dwd.de in die Liste `OFFIZIELL`; Werkzeugpunkte Alt-Text bestehender Poolanhänge, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2, Ortsfelder aus einer allgemeinen Durchsicht. Neu: `qa/redaktion-lauf.mjs` schneidet beim Dublettenvergleich die Abfrage `?__=` ab und hält deshalb zwei verschiedene Sitzungsseiten des Ratsinformationssystems für dieselbe Quelle.
