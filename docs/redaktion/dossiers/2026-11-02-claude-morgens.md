# Merzenich Aktuell – Morgenlauf (Claude), 2. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md`. Nachgeholter Morgenlauf: Die Routine von 09:13 Uhr startete um 09:25 Uhr in einer frischen Sitzung ohne Schreibrecht; deren Push scheiterte mit HTTP 403, die Arbeit blieb im Container (siehe Abschnitt 9). Dieser Lauf wurde auf Anweisung von Luis Kummer in der neuen festen Redaktionssitzung gestartet und auf seine Anweisung um die **verlorenen Läufe seit 30.10. nachmittags** erweitert (Abschnitt 10). Bearbeitung 11:29 bis 11:55 Uhr (Europe/Berlin), Nachholung ab 11:41 Uhr. Vergleichsbasis: letzter Claude-Lauf [30.10. Kalenderpflege](2026-10-30-claude-kalenderpflege.md); seither neue ChatGPT-Dossiers [30.10. mittags](2026-10-30-mittags.md), [31.10. morgens](2026-10-31-morgens.md), [31.10. mittags](2026-10-31-mittags.md), [01.11. morgens](2026-11-01-morgens.md), [01.11. mittags](2026-11-01-mittags.md), [02.11. morgens](2026-11-02-morgens.md). main vor dem Lauf: `6aabf97`.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 0 | Nullfund für neue Artikel: keine neue örtliche Meldung in den Pflichtquellen |
| Aktualisierte oder korrigierte Bestandsmeldungen | 1 | TTC-Artikel: Nachtrag mit zwei Ergebnissen und Tabelle (316 Wörter gesamt, Typ bleibt Kurzmeldung) |
| Neue Termine (live / Entwurf zur Freigabe) | 6 / 0 | alle aus Amtsblatt 11/2026 (Veranstaltungskalender, Mitteilungen, Plakat), Paket `inhalte/termine/2026-11-02-recherche-claude-morgens.json` |
| Geänderte Termine | 2 | Kindergottesdienst 8.11. (Frühstück und Anmeldefrist 5.11., 11.30 Uhr), Volkstrauertag (Wortlaut „in der Alten Kirche“ wie Quelle) |
| Termine 02.–15.11. an der Quelle nachgeprüft | 19 von 21 | 18 mit neuem Prüfstand, 1 über Vereinsseite (Stand unverändert); 2 nicht prüfbar |
| Termine 16.11.–01.01. an der Quelle nachgeprüft | 9 von 9 | dazu 6 neue Termine; Kalender jetzt 36 Termine bis 01.01.2027 |
| Umkreis-Einträge | 0 | ohne Automatik |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | – | kein neues Meldungspaket |
| Sportstand (`sport-current.json`) | geändert | Tabelle Kreisliga A nach Nachholspiel (FUSSBALL.DE, 11:43 Uhr); TTC-Ergebnisse im Artikel |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 0 | Commons-API HTTP 429, Tischtennis-Foto im nächsten Lauf |
| Kette / QA / redaktion-lauf | zweimal, `--check` unverändert, `pruefung` 0 Fehler (27 Hinweise), `redaktion-lauf` 0 Fehler (57 Hinweise) | |
| Dauer des Laufs | etwa 21 Minuten Morgenlauf, etwa 15 Minuten Nachholung | ohne Routinenumstellung |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-TTC-OKT25 | Update: „Tischtennis: TTC Merzenich gewinnt beide Auswärtsspiele und führt die 2. Bezirksklasse an“; neuer Titel und Dek, zwei Nachtragsabsätze, drei Fakten, zwei weitere Quellen. Alter Titel: „Tischtennis: AndroCup am 31. Oktober in Merzenich, zwei Auswärtsspiele für die Erste“ | `inhalte/meldungen/2026-10-25-recherche-claude.json` → `/sport/ttc-merzenich-androcup-31-oktober-auswaertsspiele-2026/` | click-TT Gruppenseite (Tabelle, Spielplan) und Mannschaftsporträt 11:35 Uhr; Spielbericht 30.10. mit Vermerk „Spielbericht durch Spielleiter genehmigt“, Letzte Änderung Admin 31.10.2026, 09:56 Uhr |
| Kalender | Kindergottesdienst 8.11.: Titel „… mit Frühstück …“, Beschreibung und Nachtrag: Frühstück ab 10.30 Uhr, Gottesdienst 11 Uhr, telefonische Anmeldung bei der Küsterin (Nummer nur in der Quelle, kein Name übernommen) | `inhalte/termine/2026-oktober-november.json` → `/termine/kindergottesdienst-ev-gemeindehaus-2026-11-08/` | Heimat-Info-Ankündigung des Ev. Gemeindezentrums vom 01.11., 14:00 Uhr; Gemeindekalender ekir 11:31 Uhr (8.11., 11 Uhr bestätigt) |
| Kalender | Volkstrauertag: „an der Alten Kirche“ → „in der Alten Kirche“ (Titel, Beschreibung, Absatz), wie im Vereinskalender | `inhalte/termine/2026-oktober-november.json` → `/termine/volkstrauertag-merzenich-2026/` | Kalender KG Jonge vom Berg 11:31 Uhr |
| Kalender, Korrektur des eigenen Nachtrags | Kindergottesdienst: alter Wortlaut „Eine Anmeldefrist ist nicht genannt.“ ersetzt durch Anmeldung „bis Donnerstag, 5. November, 11.30 Uhr“; Frühstück stand schon im Amtsblatt vom 16.10. | `inhalte/termine/2026-oktober-november.json` → `/termine/kindergottesdienst-ev-gemeindehaus-2026-11-08/` | Amtsblatt 11/2026, Mitteilungen der Ev. Gemeinde S. 22 („zum Frühstück bitte bis 5.11. um 11.30 Uhr anmelden“), gelesen 11:46 Uhr |
| Kalender, 6 neue Termine | Kripo-Info Girbelsrath 5.11.; Lesung Jennifer Teege 5.11.; Inthronisation KBF Girbelsrath 8.11.; Senioren-Nachmittag 18.11.; Funky-Monkeys-Workshops 19.12.; Weihnachtsshow „XMAS FUN“ 20.12. | `inhalte/termine/2026-11-02-recherche-claude-morgens.json` | Amtsblatt 11/2026 S. 15 (Plakat, selbst angesehen), 22, 23, 31, 32, 33; je Termin in Abschnitt 6 |
| Sport | `sport-current.json`: Tabelle Kreisliga A, Plätze 8 bis 11 (SV SW Huchem-Stammeln jetzt 9 Spiele, 13 Punkte; JSV Frenz 10 Spiele, 7 Punkte), Datenstand `generated` 2026-11-02T11:43 | `chatgpt-site/api/sport-current.json` | FUSSBALL.DE Mannschaftsseite SC 1919 Merzenich, Tabelle im Klartext, 11:43 Uhr |

Quellenausnahmen: keine.

Abweichung von der ChatGPT-Einstufung: Die Karte MA-TTC-OKT25 stand seit 31.10. auf T2, weil eine unabhängige Ergebnisbestätigung fehlte. Nach SCHREIBREGELN.md Abschnitt 3a genügt für Sportergebnisse die offizielle Verbandsquelle (click-TT steht in der Liste `OFFIZIELL`); beide Ergebnisse stehen im Spielplan und in der Tabelle des Verbands, der Bericht vom 30.10. ist vom Spielleiter genehmigt. Spielernamen nicht übernommen.

## 2. Sofort sichtbar

- Update am veröffentlichten TTC-Artikel (neuer Titel, Dek, zwei Absätze): Quelle WTTV click-TT, gelesen 02.11.2026, 11:35 Uhr.
- Änderungen an zwei veröffentlichten Terminen (Kindergottesdienst, Volkstrauertag): Quellen und Zeitpunkte in Abschnitt 6.
- Sechs neue Termine ohne `entwurf`: gehen beim Abgleich direkt online.
- Spielstand: Tabelle in `sport-current.json` aktualisiert (FUSSBALL.DE, 02.11.2026, 11:43 Uhr); letztes Spiel (25.10., 8:0 in Hambach) und nächstes Spiel (8.11., 15 Uhr, gegen FC Düren 77) unverändert, an derselben Seite bestätigt. Umkreis: nichts.

## 3. Bewusst nicht umgesetzt

- KD-HALLOWEEN-31102026 (ChatGPT 30.10. mittags, einzige T1-Karte der nachgeholten Dossiers): Servicehinweis für den 31.10.; am 02.11. vorbei. Ein Halloween-Sicherheitstipp nach Halloween hätte keinen Nutzen und wäre ein veralteter Beitrag. Register `veraltet` (durch Zeitablauf erledigt).
- SC Merzenich III, Spielabsetzung 31.10. bei Welldorf-Güsten III (ChatGPT 31.10. mittags): Spieltag vorbei, kein Termin und kein Artikel im Bestand, kein Update nötig. Offener SC-III-Endstand gegen Gereonsweiler weiter nicht im Klartext bestätigt.
- Allerheiligen Morschenich 1.11. (ChatGPT 31.10. morgens): bestätigte nur den vorhandenen Termin; vergangen, bleibt unverändert.
- Nicht als Termin angelegt (Amtsblatt 11): Jahreshauptversammlung FC Golzheim 13.11. (vereinsintern), Sprechstunden des Psychologischen Beratungszentrums 11.11. und 2.12. (Ort nach Absprache), Bündelsammlung Grünabfall 16.11. (Amtsblatt nennt Sammlung am 16.11. und Online-Anmeldung „bis zum 16.11.“; widersprüchlich, Service ohne Kalendereintrag), Laubannahme montags bis 7.12. (Daueraktion), Dämmerschoppen der KG Jonge vom Berg 21.11. (Auftritt in Gürzenich), RABAUE-Weihnachtsshow KG Golzheim (Vereinsseite nennt nur 05.12.2025, kein Datum 2026).
- Bürgersprechstunde Golzheim: Amtsblatt-Kalender nennt 13.11., 18 bis 19 Uhr; Gemeindebeitrag auf Heimat-Info vom 23.10. nannte 30.10. Widerspruch weiter ungeklärt, kein neuer Termin angelegt; der Entwurf `buergersprechstunde-golzheim-2026-10-30` bleibt in der Freigabe.
- Martinszüge als eigener Artikel: Bestand `martinszuege-2026-alle-ortsteile-gemeinde-merzenich` aus dem Amtsblatt-Paket 11 deckt das Thema; keine neue Angabe seit 16.10. Termine bestätigt (Abschnitt 6).
- Kita-Martinszüge (Kita Regenbogen 2.11., Windmühle 3.11., Krümelkiste 4.11., jeweils ab 18 Uhr, Amtsblatt 11): kein Ort angegeben, als Einrichtungsfeiern nicht als Termine angelegt.
- MA-JUGENDGREMIUM-STATUS: Vorlage 135/2026 weiter Arbeitsstand mit Platzhalter (T2), Sitzung 4.11. abwarten.
- Vertiefung kurzer eigener Artikel (Hinweise von `qa/redaktion-lauf.mjs`): in diesem nachgeholten Lauf nicht begonnen, Aufgabe für den Nachmittagslauf.

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| Rohdaten `imports/quellen/` (letzter Abruf 01.11., 20:43 Uhr, Commit `b1b002b`) | nicht verglichen | älter als 12 Stunden: nur Live-Abrufe (CLAUDE-LAUF.md Abschnitt 8) |
| Polizei Düren RSS | `presseportal.de/rss/dienststelle_8.rss2`, 11:30 Uhr, HTTP 200 | nichts Neues für Merzenich; jüngste Meldungen 30.10. betreffen Linnich (L 228) und Düren (Wohnungseinbruch), beide im Original gelesen |
| Kreis Düren Presse | Index, 11:30 Uhr, HTTP 200 | jüngste Mitteilung 28.10. (Sozialraumbericht), nichts Neues |
| Heimat-Info | Gemeindeliste, 11:30 Uhr, HTTP 200 | neu nur „Kindergottesdienst mit Frühstück vorher“ (01.11.); sonst Gemeindebeiträge vom 26. bis 30.10. mit Privatpersonen (Hochzeiten, nicht verwertbar) |
| Ratsinformationssystem | `termine.ics` und `/vorlagen`, 11:30 Uhr, HTTP 200 | Sitzungen 4.11. bis 3.12. unverändert; jüngste Vorlage weiter 135/2026 (Export 26.10.) |
| Feuerwehr | `feuerwehr-merzenich.de/einsaetze`, 11:30 Uhr, HTTP 200 | jüngster Einsatz 140/26 vom 16.10., nichts Neues |
| Amtsblatt | Index `amtsblaetter.php` HTTP 428; PDF 11/2026 direkt HTTP 200; PDF 12/2026 HTTP 404 | Index gesperrt; Ausgabe 12 laut Bestand erst am 13.11. |
| Pressemitteilungen Gemeinde | `pressemitteilungen.php` HTTP 428 | gesperrt; Umweg Heimat-Info (Gemeindekanal) gelesen |
| Sport (click-TT) | Gruppenseite und Mannschaftsporträt TTC Merzenich, 11:35 Uhr | zwei Ergebnisse, Tabelle (Abschnitt 5) |
| FUSSBALL.DE / fupa | montags keine Regelprüfung | nicht abgerufen |

Sperren und Umwege: gemeinde-merzenich.de antwortet auf Unterseiten mit HTTP 428 (Index Amtsblatt, Pressemitteilungen); PDFs unter `/bindata/` erreichbar. Pfarrbrief-Verzeichnis pfarrgemeinden-niederzier.de liefert eine Prüfseite („Please wait while your request is being verified“), die WordPress-Schnittstelle antwortet 401; nicht umgangen, Pfarrbrief 08/26 als PDF direkt gelesen. VHS Rur-Eifel: alle Seiten einschließlich Startseite HTTP 404. Commons-API HTTP 429.

## 5. Sport

TTC Merzenich, 1. Mannschaft, 2. Bezirksklasse 6 (Bezirk Aachen/Euregio), Datenstand click-TT 02.11.2026, 11:35 Uhr:

- Do 29.10., TTF Weisweiler/Wenau IV – TTC Merzenich 1:9; Fr 30.10., TTC Mersch-Pattern IV – TTC Merzenich 2:8 (Spielbericht genehmigt).
- Tabelle: 1. TTC Merzenich 6 Begegnungen, 5 Siege, 1 Unentschieden, 0 Niederlagen, 46:14 Spiele, 21:3 Punkte; 2. TTC Mersch-Pattern IV 5 Begegnungen, 34:16 Spiele, 15:5 Punkte.
- Nächste Spiele: Fr 6.11., 20 Uhr, TTC Dürboslar III – TTC Merzenich; Sa 14.11., 19 Uhr, TTC Merzenich – TTC Bourheim.

Fußball, SC 1919 Merzenich (Kreisliga A), FUSSBALL.DE Mannschaftsseite, 11:43 Uhr: kein Meisterschaftsspiel seit dem 8:0 in Hambach am 25.10.; am 29.10. ein Freundschaftsspiel bei FC Viktoria Manheim (Ergebnis auf der Seite nicht im Klartext, nicht übernommen). Tabelle: SC Merzenich Vierter mit 24 Punkten (10 Spiele, 46:16 Tore), FC Golzheim Dritter mit 25 Punkten; durch ein Nachholspiel geändert: SV SW Huchem-Stammeln 9 Spiele, 18:14 Tore, 13 Punkte (Platz 8), JSV Frenz 10 Spiele, 22:31 Tore, 7 Punkte (Platz 11). Nächstes Spiel So 8.11., 15 Uhr, gegen FC Düren 77; danach 15.11. in Frenz. `sport-current.json` entsprechend aktualisiert. Offener SC-III-Endstand gegen Gereonsweiler weiter nicht bestätigt.

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender` (02.11.2026 bis 01.01.2027, 30 Termine). Termine der nächsten 14 Tage (02. bis 15.11., 21 Termine), alle Abrufe am 02.11.2026 zwischen 11:30 und 11:35 Uhr:

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Martinszug Morschenich, 2.11. 18 Uhr | martinszug-morschenich-2026-11-02 | bestätigt | [Amtsblatt 11/2026](https://www.gemeinde-merzenich.de/bindata/amtsblatt-2026/Merzenich_11_2026-web.pdf#page=11), „Martinszüge in der Gemeinde Merzenich“; keine Absage im Gemeindekanal Heimat-Info | 2026-11-02 |
| Abgabeschluss Amtsblatt 12, 3.11. 12 Uhr | amtsblatt-12-2026-abgabeschluss-2026-11-03 | nicht geprüft | Index `amtsblaetter.php` HTTP 428 | 2026-10-25 |
| Jugendgremium, 4.11. 17 Uhr | jugendgremium-sitzung-2026-11-04 | bestätigt | [RIS termine.ics](https://ratsinfo.gemeinde-merzenich.de/termine.ics) | 2026-11-02 |
| Rosenkranzgebet im Pfarrheim ab 4.11. | rosenkranzgebet-pfarrheim-merzenich-ab-2026-11-04 | bestätigt | [Pfarrbrief 08/26](https://www.pfarrgemeinden-niederzier.de/wp-content/uploads/2026/09/Pfarrbrief-Oktober-2026-Pfarrgemeinden-Merzenich-Niederzier.pdf), „Rosenkranzgebet in Merzenich ab November im Pfarrheim“ | 2026-11-02 |
| Aktionstag Persönliches Budget, 5.11. 10 Uhr | aktionstag-persoenliches-budget-kreishaus-2026-11-05 | bestätigt | [Kreis Düren](https://www.kreis-dueren.de/presse/das-persoenliche-budget_2026-10-19.php) | 2026-11-02 |
| Rechnungsprüfungsausschuss, 5.11. 18 Uhr | rechnungspruefungsausschuss-2026-11-05 | bestätigt | RIS termine.ics | 2026-11-02 |
| KI-Roadshow IHK, 6.11. | ki-roadshow-ihk-dueren-2026-11-06 | bestätigt | [Kreis Düren](https://www.kreis-dueren.de/presse/ki-roadshow_2026-10-26.php) | 2026-11-02 |
| Eichel-Sammelaktion, 6.11. | eichel-sammelaktion-huertgenwald-2026-11-06 | bestätigt | [Kreis Düren, Update 23.10.](https://www.kreis-dueren.de/presse/eichel-sammelaktion_2026-10-23.php) („Nächster Termin am 6. November“) | 2026-11-02 |
| Kostümsitzung KG Golzheim, 7.11. 20:11 Uhr | kg-golzheim-kostuemsitzung-2026-11-07 | bestätigt | [KG-Seite](https://kg-mir-hahle-poohl.de/veranstaltung/grosse-kostuemsitzung/) | 2026-11-02 |
| Bücher- und Spielemarkt, 8.11. 10–16 Uhr | buecher-spielemarkt-ig-golzheim-2026 | bestätigt (Vereinsseite) | [IG Golzheim aktiv](https://www.golzheimaktiv.de/aktuelles): 8.11., 10–16 Uhr, Bürgerhaus Merzenich; Prüfstand der Amtsblatt-Quelle nicht geändert | 2026-10-30 |
| Kindergottesdienst, 8.11. 11 Uhr | kindergottesdienst-ev-gemeindehaus-2026-11-08 | geändert (Frühstück ab 10.30 Uhr, Anmeldung bis 5.11., 11.30 Uhr laut Amtsblatt 11/2026 S. 22) | [Gemeindekalender ekir](https://evangelischegemeinde-dueren.ekir.de/thema/bezirk-7-merzenich-nierzier/); [Heimat-Info 01.11.](https://www.heimat-info.de/veranstaltungen/f60eafb6-d44f-4448-8edc-884932f1299b) (Selbstauskunft) | 2026-11-02 |
| VHS-Vortrag Paris, 10.11. 15:30 Uhr | vhs-bildvortrag-paris-marienhof-2026-11-10 | nicht geprüft | vhs-rur-eifel.de vollständig HTTP 404; keine Absage belegt, unverändert | 2026-10-05 |
| Martinszug Golzheim, 11.11. 17:45 Uhr | martinszug-golzheim-2026-11-11 | bestätigt | Amtsblatt 11/2026 | 2026-11-02 |
| Martinszug Merzenich, 12.11. 18 Uhr | martinszug-merzenich-2026-11-12 | bestätigt | Amtsblatt 11/2026 | 2026-11-02 |
| Bauausschuss, 12.11. 18 Uhr | bauausschuss-sitzung-2026-11-12 | bestätigt | RIS termine.ics | 2026-11-02 |
| Martinsfeier Girbelsrath, 13.11. 17:30 Uhr | martinsfeier-girbelsrath-2026-11-13 | bestätigt | Amtsblatt 11/2026 | 2026-11-02 |
| TTC-Jugend-Vereinsmeisterschaften, 13.11. 17:30 Uhr | ttc-merzenich-jugend-vereinsmeisterschaften-2026-11-13 | bestätigt | [TTC-Vereinskalender](https://ttc-merzenich.de/wp-json/ttc/v1/manual-events) | 2026-11-02 |
| Erste-Hilfe-Kurs, 14.11. 9–17 Uhr | erste-hilfe-kurs-golzheim-2026-11-14 | bestätigt | [Heimat-Info-Beitrag 28.10.](https://www.heimat-info.de/beitraege/75056bea-225b-4585-9134-11670f657eba) | 2026-11-02 |
| Volkstrauertag, 15.11. 9:30 Uhr | volkstrauertag-merzenich-2026 | geändert (Wortlaut „in der Alten Kirche“) | [KG Jonge vom Berg](https://jonge-vom-berg.de/event/kranzniederlegung-und-weckmannschiessen/) | 2026-11-02 |
| Ev. Gottesdienst Gedenken, 15.11. 10 Uhr | ev-gottesdienst-gedenken-verstorbene-2026-11-15 | bestätigt | Gemeindekalender ekir | 2026-11-02 |
| Taizé-Gottesdienst Morschenich, 15.11. 17 Uhr | taize-gottesdienst-morschenich-2026-11-15 | bestätigt | Pfarrbrief 08/26, „Taizé – Gebete in Morschenich“ (neuer Pfarrbrief 09/26 hinter Prüfseite nicht lesbar) | 2026-11-02 |

Nachgeholt: Termine 16.11. bis 01.01.2027 (Abrufe 11:41 bis 11:47 Uhr) und neue Termine aus den Quellen.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Kripo-Info „Gefahren an der Haustür“, Girbelsrath, 5.11. 15 Uhr | kripo-info-gefahren-haustuer-girbelsrath-2026-11-05 | neu | [Amtsblatt 11/2026](https://www.gemeinde-merzenich.de/bindata/amtsblatt-2026/Merzenich_11_2026-web.pdf#page=25), Veranstaltungskalender S. 23 und Vereinsmitteilung S. 32 (übereinstimmend) | 2026-11-02 |
| Lesung Jennifer Teege, Aula Gesamtschule Niederzier, 5.11. 18 Uhr | lesung-jennifer-teege-gesamtschule-niederzier-merzenich-2026-11-05 | neu | Amtsblatt 11/2026, Plakat S. 15 (selbst angesehen: Datum, Uhrzeit, Ort, Abendkasse 3 Euro) und Veranstaltungskalender S. 23 | 2026-11-02 |
| Inthronisation KBF Girbelsrath, Maarhalle, 8.11. 11:11 Uhr | inthronisation-kbf-girbelsrath-2026-11-08 | neu (ohne Namen der Tollitäten, darunter ein Kind) | Amtsblatt 11/2026, Vereinsmitteilung S. 31 und Veranstaltungskalender S. 23 | 2026-11-02 |
| Pluriversum, Aula Gesamtschule Merzenich, 17.11. 18:15 Uhr | pluriversum-gesamtschule-merzenich-2026-11-17 | bestätigt | Amtsblatt 11/2026 S. 4 | 2026-11-02 |
| Online-Vortrag „Mental Load“, 18.11. 18 Uhr | mental-load-online-vortrag-2026-11-18 | bestätigt | [Kreis Düren](https://www.kreis-dueren.de/presse/vortrag-mental-load_2026-10-20.php) | 2026-11-02 |
| Senioren-Nachmittag ev. Gemeindehaus, 18.11. 15–17 Uhr | senioren-nachmittag-ev-gemeindehaus-2026-11-18 | neu | Amtsblatt 11/2026, Mitteilungen der Ev. Gemeinde S. 22 | 2026-11-02 |
| Sprechstunde Betreuungsbehörde, 19.11. 10–12 Uhr | sprechstunde-betreuungsbehoerde-rathaus-2026-11-19 | bestätigt | [Amtsblatt 08/2026](https://www.gemeinde-merzenich.de/bindata/amtsblatt-2026/Merzenich_08_2026-web.pdf) („6. August 2026 und 19. November 2026“, Fraktionsraum B) | 2026-11-02 |
| Haupt- und Finanzausschuss, 19.11. 18 Uhr | haupt-finanzausschuss-sitzung-2026-11-19 | bestätigt | RIS termine.ics | 2026-11-02 |
| Tag der Modelleisenbahn, Wasserturm, 21.11. 13–17 Uhr | tag-der-modelleisenbahn-wasserturm-2026-11-21 | bestätigt | Amtsblatt 11/2026 S. 27 | 2026-11-02 |
| Seniorenweihnacht des Kreises, 24.11. 15 Uhr | seniorenweihnacht-kreis-dueren-2026-11-24 | bestätigt | [Kreis Düren](https://www.kreis-dueren.de/presse/seniorenweihnacht_2026-09-30.php) (Stand 19.10., Restkarten online) | 2026-11-02 |
| Ausschuss Revitalisierung Bürgewald, 26.11. 18 Uhr | ausschuss-revitalisierung-buergewald-2026-11-26 | bestätigt | RIS termine.ics | 2026-11-02 |
| Adventmarkt Heimatmuseum, 28.11. ab 14 Uhr | adventmarkt-heimatmuseum-2026-11-28 | bestätigt | Amtsblatt 11/2026 S. 27 | 2026-11-02 |
| Gemeinderat, 3.12. 17 Uhr | gemeinderat-sitzung-2026-12-03 | bestätigt | RIS termine.ics | 2026-11-02 |
| Breakdance-Workshops The Funky Monkeys, Weinberghalle, 19.12. 16–18 Uhr | breakdance-workshop-funky-monkeys-weinberghalle-2026-12-19 | neu | Amtsblatt 11/2026 S. 33 | 2026-11-02 |
| Weihnachtsshow „XMAS FUN“, Weinberghalle, 20.12. 16 Uhr | funky-monkeys-xmas-fun-weinberghalle-2026-12-20 | neu | Amtsblatt 11/2026 S. 33 | 2026-11-02 |

Gelesene Wege ohne neuen Termin bis 01.01.: Gemeindekalender ekir (22.11. Gottesdienst in Niederzier, nicht Merzenich), KG Jonge vom Berg (Dämmerschoppen in Gürzenich, vereinsinterne Weihnachtsfeier), KG Golzheim (Termine-Seite HTTP 404, Veranstaltungen nur mit Daten 2025/26), RIS (keine Sitzung nach 3.12. im Kalender), Heimat-Info erste Seite.

Offene Hinweise „nachpruefen“: 1 (VHS-Vortrag 10.11.; laut [Stadt Düren vom 26.10.](https://www.dueren.de/de/verwaltung-politik/stadtverwaltung/presse/pressemitteilung/die-homepage-der-vhs-rur-eifel-ist-voruebergehend-offline) ist die VHS-Website vorsorglich abgeschaltet, ChatGPT-Dossier 30.10. mittags; das belegt weder Durchführung noch Absage). Neue Termine: 6. Abgesagt: 0. Kalender: 36 Termine vom 02.11.2026 bis 01.01.2027.

## 7. QA, Commit, Abgleich

Commits `0e926b7` (Inhalte Morgenlauf), `18b05df` (Dokumentation Morgenlauf), `e53e231` (Inhalte Nachholung: 6 Termine, Kalender 60 Tage, Fußballtabelle) und der Dokumentationscommit dieser Fassung. Beim Rebase von `e53e231` Konflikt nur in `docs/SHA256SUMS.txt` (CI-Commits `1d0739c`, `ebfe2b2`, `0ee3970` auf main): Stand von main übernommen, Kette zweimal neu, `--check` unverändert, `qa/pruefung.mjs` 0 Fehler. Abgleich der Nachholung: Anstoß 11:50 Uhr `angestossen: true`, wp-cron HTTP 200; alle sechs neuen Slugs und die Anmeldefrist je 1 Treffer in der Import-XML auf GitHub raw. Live um 11:52 Uhr: Kripo-Info und „XMAS FUN“ als Stichprobe HTTP 200; Kindergottesdienst mit „5. November, 11.30“ (2 Treffer). Rebase ohne Konflikt, Push ohne Force; `kette --check` auf dem gepushten Stand unverändert. Abgleich: Anstoß 11:38 Uhr `{"aktiv":true,"angestossen":true,…,"ueberfaellig":true}`, wp-cron HTTP 200. Nachweis in der Import-XML auf GitHub raw: neuer TTC-Titel 1 Treffer, „Frühstück ab 10.30 Uhr“ 1 Treffer. Live um 11:40 Uhr, alle HTTP 200: TTC-Artikel mit neuem Titel und „9:1“, Kindergottesdienst mit „Frühstück ab 10.30“ und Nachtrag, Volkstrauertag mit „Kranzniederlegung in der Alten Kirche“ (alter Wortlaut 0 Treffer). Freigaben im Backend: nicht geprüft, kein Zugang in der Sitzung bereitgestellt.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| ttc-merzenich-androcup-31-oktober-auswaertsspiele-2026 (Update) | sport.tischtennis | sporthalle | Sporthalle Ennigloh.jpg | CC BY-SA 3.0 | selbst angesehen: Sporthalle von außen, ohne Personen und Kennzeichen; zeigt den Ort des Hallensports, aber kein Tischtennis. Nur Notlösung |

Neu importierte Fotos: keine. Der ChatGPT-Vorschlag „File:Table Tennis Table in Ciudad Jardín Sports Centre.jpg“ (CC BY-SA 4.0) konnte nicht geprüft und importiert werden, weil die Commons-API mit HTTP 429 antwortete (Störung nach CLAUDE-LAUF.md Abschnitt 8). Nachholen im nächsten Lauf mit Motiv `tischtennis` für die Klasse `sport.tischtennis`.

## 8a. Prüfung und Eigenformulierung

Kein neues Meldungspaket (`qa/eigenformulierung.mjs --datum 2026-11-02 --lauf morgens`: „Kein Paket … nichts zu pruefen“). Der TTC-Nachtrag ist eigener Text aus Tabellen- und Spielplanwerten; Prüfprotokoll-Pflicht gilt für Pakete ab 29.10., dieser Artikel stammt vom 25.10.

## 9. Offene Punkte für den nächsten Lauf

1. Tischtennis-Themenfoto von Commons holen, sichten und der Klasse `sport.tischtennis` zuordnen (Commons heute HTTP 429).
2. Vertiefungen eigener kurzer Artikel beginnen (ELW 405 Wörter, Amprion 436, Pfarrer Galbierz 381, Jugendzeltplatz 339).
3. VHS-Vortrag 10.11.: Website seit mehreren Läufen vollständig HTTP 404; offener Punkt für Menschen (Rückfrage bei der VHS-Zweigstelle Merzenich oder beim Seniorenhaus Marienhof).
4. Abgabeschluss Amtsblatt 12 (3.11.) und Bürgersprechstunde Golzheim weiter offen; Gemeindeseite antwortet HTTP 428.
5. Routinebetrieb: Ab heute laufen die Routinen 09:13 und 14:43 in der festen Redaktionssitzung mit Push-Rechten; die alten Routinen in frischen Sitzungen sind abgeschaltet (nicht gelöscht). Die Arbeit des Laufs von 09:25 Uhr (Nullfund-Dossier und dieselbe Frühstücksergänzung, zwei lokale Commits) ist nicht auf GitHub angekommen; inhaltlich ist sie durch diesen Lauf ersetzt.
6. Bürgersprechstunde Golzheim (30.10. laut Heimat-Info oder 13.11. laut Amtsblatt-Kalender): bei der Gemeinde klären lassen.
7. Älter als der Nachholauftrag und weiter offen: T1-Karten aus Dossiers vor dem 30.10. ohne Umsetzung (KD-ERNTE-2310, KD-VISIONPLUS-2110, DN-ARCHIV-SERVICE-102026, NH-BIZ-SOPHIENHOEHE-2026; siehe `offen.md`); vor einer Umsetzung auf Aktualität prüfen.

## 10. Nachgeholte Läufe 30.10. bis 02.11.

Auftrag Luis Kummer, 02.11.2026: alles aus den verlorenen Läufen seit 30.10. nachholen. Nach dem Git-Verlauf gibt es nach der Kalenderpflege vom 30.10. (`9563a7f`) bis zu diesem Lauf keinen Claude-Commit; die Routinen liefen in Sitzungen ohne Push-Recht.

| Verlorener Claude-Lauf | Zugehörige ChatGPT-Zulieferung | Inhalt der Zulieferung | Hier erledigt |
|---|---|---|---|
| 30.10. nachmittags | [30.10. mittags](2026-10-30-mittags.md) | keine Korrektur; T1-Karte KD-HALLOWEEN-31102026; keine Terminänderung; VHS-Website laut Stadt Düren abgeschaltet | Halloween-Karte veraltet (Abschnitt 3), VHS-Hinweis in Abschnitt 6 |
| 31.10. morgens | [31.10. morgens](2026-10-31-morgens.md) | keine Korrektur; MA-TTC-OKT25 (T2, erster Bericht genehmigt); Allerheiligen Morschenich bestätigt | TTC-Update (Abschnitt 1), Allerheiligen vergangen |
| 31.10. nachmittags | [31.10. mittags](2026-10-31-mittags.md) | keine Korrektur; MA-TTC-OKT25 (zweiter Bericht genehmigt); Spielabsetzung SC III 31.10. | TTC-Update; Spielabsetzung ohne Bestand, vergangen |
| 01.11. morgens | [01.11. morgens](2026-11-01-morgens.md) | keine Korrektur, keine Karte, keine Terminänderung | Kalenderpflege (Abschnitt 6) |
| 01.11. nachmittags | [01.11. mittags](2026-11-01-mittags.md) | keine Korrektur, keine Karte, keine Terminänderung | Kalenderpflege (Abschnitt 6) |
| 02.11. morgens | [02.11. morgens](2026-11-02-morgens.md) | keine Korrektur; Frühstück vor dem Kindergottesdienst | Termin ergänzt, Anmeldefrist aus dem Amtsblatt nachgetragen |

Ergebnis: 0 offene Korrekturaufträge (alle sechs Dossiers ohne Korrektur, letzter Claude-Artikel jeweils geprüft), 1 T1-Karte (veraltet, nicht umgesetzt), 1 T2-Karte umgesetzt (offizielle Quelle), alle Terminhinweise bearbeitet. Zusätzlich die Aufgaben, die die Läufe selbst gehabt hätten: Kalenderpflege 60 Tage mit 6 neuen Terminen, Fußballprüfung (in den verlorenen Freitags- und Samstagsläufen fällig) mit Tabellenupdate, TTC-Ergebnisse. Nicht nachholbar: Pflichtquellen-Abrufe zu den damaligen Zeitpunkten; die Live-Abrufe dieses Laufs decken den Zeitraum ab, weil Polizei, Kreis, Heimat-Info, Rat und Feuerwehr ihre Einträge seit 30.10. weiter listen (Abschnitt 4).
