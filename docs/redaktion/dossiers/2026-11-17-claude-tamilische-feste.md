# Merzenich Aktuell – Sonderauftrag Tamilische Feste (Claude), 17. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9) für einen direkten Auftrag von Luis Kummer in der festen Redaktionssitzung, Bearbeitung 15:00 bis 15:20 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 17.11. nachmittags](2026-11-17-claude-nachmittags.md). main vor dem Auftrag: `2d3577e1`.

Auftrag im Wortlaut: „Tamilische Feiertage, tamilische Feste, so wie auch was im Dachraum an Terminen ansteht von Tamilen. Bitte mach mir das auch alles fertig.“ „Dachraum“ ist als **Dürener Raum** gelesen (Annahme, im Bericht an Luis Kummer genannt).

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Service-Kurzmeldung zu tamilischen Festtagen bis April 2027 (304 Wörter im Fließtext) |
| Neue / geänderte Termine | 0 / 0 | keine datierte Veranstaltung einer tamilischen Gruppe in der Region gefunden (Abschnitt 3) |
| Bilder gefunden / geprüft / integriert | 3 / 3 / 1 | Commons-Foto „Kathigai Thirunal.JPG“ (CC BY-SA 3.0), neue Bildklasse `leben.kultur.tamilisch` |
| Quellenkatalog | neuer Abschnitt 3.9a | `QUELLEN.md`; ChatGPT-Auftrag Abschnitt 3c |
| Kette / QA / redaktion-lauf | Kette zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs --datum 2026-11-17 --lauf tamilische-feste` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 1 Hinweis (Zentralbank Sri Lanka antwortet dem Werkzeug mit HTTP 403) | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-TAMILISCHE-FESTE-2026 | Kurzmeldung „Tamilische Feste für Merzenich und Region: Karthigai Deepam am 24. November, Pongal im Januar“ (Ressort leben, Relevanz 3): Karthigai Deepam Di. 24.11.2026; Thai Pongal Do. 14.01.2027 nach dem Kalender für Köln (in Sri Lanka und Indien Fr. 15.01.); Thaipusam Fr. 22.01.2027; Maha Shivaratri Sa. 06.03.2027; Singhalesisch-tamilisches Neujahr Mi. 14.04.2027. Je Fest ein Satz zum Brauch in eigenen Worten. Hinweis, dass bis 17.11. keine öffentlich angekündigte Feier tamilischer Gemeinden im Raum Düren gefunden wurde und Termine von Vereinen oder Tempeln in den Kalender kommen | `inhalte/meldungen/2026-11-17-recherche-claude-tamilische-feste.json` → `/leben/tamilische-feste-2026-2027-karthigai-deepam-thai-pongal-thaipusam-neujahr-termine-merzenich/` | Drik Panchang mit Ort Köln (`geoname-id=2886242`) für vier Feste, Neu-Delhi zum Vergleich; Feiertagsliste 2027 der Zentralbank von Sri Lanka (amtlich, Knotenadresse `node/20573`); Haindavam und Office Holidays als Zweitbelege; gelesen 15:07 bis 15:09 Uhr; vier Prüfeinträge; Commit `80414d35` |
| Bild | neues Commons-Foto „Kathigai Thirunal.JPG“ in Pool `themen` (themen-26), Motiv `oellampen-karthigai`, Bildklasse `leben.kultur.tamilisch`, deutscher Motivtext in `deploy/symbolbilder.mjs` | `deploy/import-editorial-photos.mjs`, `deploy/editorial-photo-review.json`, `deploy/bildmotive.json`, `deploy/bildklassen.json`, `deploy/symbolbilder.mjs` | Abschnitt 8 |
| Quellen | neuer Abschnitt 3.9a „Tamilische Feste und Veranstaltungen“ (Quellen, URL-Muster, Sperren, Suchwege, Lücke) | `docs/redaktion/QUELLEN.md` | – |
| ChatGPT | neuer Abschnitt 3c „Tamilische Feste und Gruppen“: Vereine, Tempel, Tamil-Schulen und Kulturgruppen im Kreis Düren und angrenzend, auch Instagram und Facebook | `docs/redaktion/vorlagen/chatgpt-auftrag.md` | – |

Warum keine Termine: Ein Termin auf merzenich-aktuell.de braucht Ort und Veranstalter (Pflichtfelder, Event-Schema für Suchmaschinen). Ein Festtag allein hat beides nicht; als Termin eingetragen, stünde ein Fest ohne Veranstaltung im Kalender. Die Festdaten stehen deshalb in der Service-Meldung. Sobald eine Gruppe in der Region ein Fest mit Ort und Uhrzeit ankündigt, wird daraus ein Termin.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Meldung ohne Freigabe (kein rechtliches Risiko). Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Termine tamilischer Gruppen im Dürener Raum | sieben Websuchen (Merzenich, Düren, Jülich, Kerpen, Eschweiler, Aachen, Köln; Verein, Tempel, Schule, Pongal-Fest) ohne datierte Veranstaltung; einziger regionaler Treffer, die Seite einer tamilischen Vereinigung der Stadt Jülich (`juelich.de/tamiassociation`), leitet auf die Startseite um (HTTP 200, 242 Bytes) |
| Feste als Termine im Kalender | siehe Abschnitt 1, „Warum keine Termine“ |
| Deepavali | nicht Teil der Meldung (Zeitraum ab 24.11.); Datum 2026 in diesem Auftrag nicht nachgeprüft, Deepavali 2027 in einer späteren Meldung |
| Gedenktag Maaveerar Naal (27.11.) | politischer Gedenktag, keine örtliche Quelle und keine angekündigte Veranstaltung in der Region; nur bei öffentlicher Ankündigung mit Veranstalter neutral prüfen (`QUELLEN.md` 3.9a) |
| Tamil-Nadu-Feiertagsliste 2027 | amtliche Liste (G.O.) in der Suche nicht als lesbare Originalquelle gefunden; Indien-Daten nur über Drik Panchang mit Ort Neu-Delhi |

## 4. Quellen dieses Auftrags

| Quelle | Weg | Ergebnis |
|---|---|---|
| Drik Panchang | Festseiten 2026/2027 mit `geoname-id=2886242` (Köln) und ohne Ort (Neu-Delhi), 15:07 bis 15:09 Uhr | Daten in Abschnitt 1; für Pongal weicht Köln (14.01.) von Neu-Delhi (15.01.) ab, Text nennt beide |
| Zentralbank Sri Lanka | `https://www.cbsl.gov.lk/en/node/20573`, 15:07 Uhr | Pongal 15.01., Maha Shivaratri 06.03., Neujahr 14.04. (Vortag 13.04.); Hauptadresse `…/bank-holidays-2027` HTTP 504 |
| Haindavam, Office Holidays | Festseite Karthigai Deepam 2026; Thaipusam Malaysia | Zweitbelege 24.11.2026 und 22.01.2027 |
| Websuche regional | sieben Suchen, Abschnitt 3 | kein Termin |
| Wikimedia Commons | Suche „Pongal pot“, „Karthigai Deepam lamps“, drei Kandidaten mit Lizenzangaben angesehen (zwischendurch Drosselung HTTP 429, Wiederholung erfolgreich) | Abschnitt 8 |
| Heimat-Info | Veranstaltungskalender 15:15 Uhr | Abschnitt 6 |

## 5. Sport

Nicht Teil dieses Auftrags; Stand wie im [Nachmittagslauf](2026-11-17-claude-nachmittags.md).

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 15:15 Uhr erneut gelesen: dieselben 10 Veranstaltungs-IDs wie im Nachmittagslauf, kein Eintrag mit Bezug zu tamilischen Festen oder Gruppen (Suche nach „tamil“, „Pongal“, „Deepam“, „Hindu“, „Tempel“ ohne Treffer). Die übrigen Termine bis 01.12. hat der Nachmittagslauf am selben Tag an der Quelle bestätigt.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| tamilische Veranstaltungen im Dürener Raum | – | keiner angelegt | Websuche, Heimat-Info | 2026-11-17 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `80414d35` (Meldung, Bild), `3e598486` (Dokumentation). Abgleich durch Anstoß vorgezogen (planmäßig 16:05 Uhr).

Live-Nachweis 15:18 Uhr: Artikel `/leben/tamilische-feste-2026-2027-karthigai-deepam-thai-pongal-thaipusam-neujahr-termine-merzenich/` HTTP 200, Titel und Daten im Text (unter anderem „Freitag, 22. Januar“), Foto mit deutschem Alt-Text „Öllampen zum tamilischen Lichterfest Karthigai Deepam“ und Bildnachweis „Gitakart / Wikimedia Commons · CC BY-SA 3.0“; Startseite zeigt die Meldung, Nachrichten-Sitemap führt sie an erster Stelle (erster Abruf aus dem Zwischenspeicher noch ohne Eintrag).

## 8. Bildprüfung

| Meldung | Slug | Bild | Prüfung |
|---|---|---|---|
| Tamilische Feste für Merzenich und Region | tamilische-feste-2026-2027-karthigai-deepam-thai-pongal-thaipusam-neujahr-termine-merzenich | „Kathigai Thirunal.JPG“ (Gitakart, Wikimedia Commons, CC BY-SA 3.0), Pool `themen`, `themen-26-233a92d40a`; Bildklasse `leben.kultur.tamilisch`; Alt-Text „Öllampen zum tamilischen Lichterfest Karthigai Deepam“ | selbst angesehen: brennende Messinglampen auf einem Hauseingang mit Kolam-Bodenmuster, im Hintergrund ein Lichterkranz am Abend; passt zum ersten Fest der Meldung (Karthigai Deepam, Lichterfest, Lampen vor dem Haus). Echtes Foto, keine KI-Grafik, keine Personen, keine Kennzeichen, keine Schrift im Bild. Nicht gewählt: „Agalvilaku.jpg“ (Begil90ml, CC BY-SA 4.0; zwei Tonlampen in Nahaufnahme, zeigt weniger vom Fest), „Thai Pongal.jpg“ (Emperor91, CC BY-SA 4.0; Kamera-Einblendung „Shot on Mi A3“ und Datumsstempel im Bild, Hand einer Person am Rand) |

## 9. Offene Punkte für den nächsten Lauf

- Daueraufgabe (Luis Kummer 17.11.): in jeder Breitenrecherche nach Veranstaltungen tamilischer Gruppen im Dürener Raum suchen (`QUELLEN.md` 3.9a, ChatGPT-Auftrag 3c); Fund mit Ort und Uhrzeit wird Termin.
- Luis Kummer fragen, ob er tamilische Vereine, einen Tempel oder eine Tamil-Schule im Kreis Düren kennt.
- Karthigai Deepam 24.11.: keine Folgemeldung nötig, außer es kündigt sich eine Feier in der Region an.
