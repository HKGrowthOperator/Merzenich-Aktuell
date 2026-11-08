# Merzenich Aktuell – Morgenlauf (Claude, Nachhollauf), 8. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Nachhollauf für die Routine „09:13“ vom 08.11.: Die feste Redaktionssitzung hing nach dem Lauf vom 05.11. nachmittags (letzte Eil-Prüfung 18:40 Uhr) wegen wiederholter Neustarts. 21 Routinen-Aufrufe blieben liegen und wurden erst am 08.11. um 12:42 Uhr gelesen. Bezeichnung „morgens“, obwohl der Start nach 12 Uhr lag: Der Lauf ersetzt den verpassten Morgenlauf, der Nachmittagslauf 14:43 bleibt eigenständig. Bearbeitung 12:42 bis 13:20 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 05.11. nachmittags](2026-11-05-claude-nachmittags.md), seither neu sechs ChatGPT-Dossiers ([06.11. morgens](2026-11-06-morgens.md), [06.11. mittags](2026-11-06-mittags.md), [07.11. morgens](2026-11-07-morgens.md), [07.11. mittags](2026-11-07-mittags.md), [08.11. morgens](2026-11-08-morgens.md), [08.11. mittags](2026-11-08-mittags.md)). main vor dem Lauf: `391e801`.

**Ausgefallen (Sitzungsstörung, nicht nachholbar):** Morgen- und Nachmittagsläufe am 06.11. und 07.11., 16 Eil-Prüfungen vom 05.11. 20:40 Uhr bis 08.11. 12:40 Uhr. Dieser Lauf deckt die Eil-Prüfungen inhaltlich ab: Polizei Düren ohne Merzenich-Meldung seit 05.11. 12:06 Uhr; die Feuerwehreinsätze 145/26 (Heimat-Info 06.11. 13:30 Uhr) und 146/26 (06.11. 20:22 Uhr) wären in den Eil-Prüfungen am 06.11. fällig gewesen und erscheinen jetzt rund zwei Tage später. Mindesttakt: am 06.11. und 07.11. keine Meldung (auch keine von Hand in WordPress, REST-Abfrage seit 05.11. 15 Uhr).

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 2 | zwei Kurzmeldungen: Feuerwehr 145–147/26 (356 Wörter, Blaulicht, `relevanz` 3), Pluriversum in Niederzier (320 Wörter, Leben, `relevanz` 3, Folgemeldung) |
| Korrigierte Bestandsmeldungen | 3 | Wochenvorschau 9.–15.11. (vier Korrekturen), Pluriversum-Artikel vom 16.10. (Ort und Zeiten), Martinszüge (Polizeihinweis präzisiert); alle sechs ChatGPT-Korrekturaufträge seit 06.11. umgesetzt |
| Neue Termine (live / Entwurf) | 1 / 0 | Männergruppe und Skatrunde 18.11. (von der Automatik verwechselt) |
| Termine geändert | 3 | Pluriversum 17.11. (Niederzier, 18 Uhr), Workshop 18.11. (11–13 Uhr, Niederzier), Frühstückstreff 11.11. (Ort offen) |
| Termine an der Quelle bestätigt | 20 | alle Termine 10.–22.11.; die drei Termine vom 08.11. selbst liegen in der Vergangenheit |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 2/2 | Kurzmeldungen mit offizieller Quelle; Feuerwehr 4 und Pluriversum 4 Quellen |
| Sportstand | unverändert | Sonntag, keine Pflichtprüfung; die Freitagsprüfung 06.11. ist ausgefallen |
| Bilder gefunden / geprüft / integriert | 0 / 2 / 2 | Poolfotos, beide selbst angesehen; Bildklasse Tierrettung verworfen (Katze im Baum) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | eine 9-Wort-Folge aus dem Gemeinde-Beitrag umformuliert |
| Dauer des Laufs | etwa 40 Minuten | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| Korrektur MA-WOCHENVORSCHAU-2026-46 | (1) alt: „An drei Abenden hintereinander ziehen Golzheim, Merzenich und Girbelsrath durch die Straßen“ (und Vorspann „Drei Martinszüge“); neu: Martinszüge in Golzheim und Merzenich, Girbelsrath Martinsfeier in der Kirche mit Lichterfest am Kindergarten. (2) alt: „beginnt im Evangelischen Gemeindehaus mit zwei Angeboten“; neu: Skatrunde im Gemeindehaus, Frühstückstreff am 11.11. ohne Ortsangabe. (3) alt: „kurze Sperrungen einzuplanen“; neu: Verzögerungen oder kurzfristige Verkehrsbehinderungen. (4) Pluriversum: Niederzier, 18 Uhr (Kalender 18.15 Uhr), Workshop 11–13 Uhr; HFA weiter ohne Tagesordnung (08.11.). Hinweis „Korrigiert am 8. November“ im Text | `inhalte/meldungen/2026-11-05-recherche-claude-nachmittags.json` | Amtsblatt 11/2026 S. 11 (Martinszüge, gelesen 08.11.), Polizei Düren pm/8/6384849, Heimat-Info a5c2217f, 14702026, 9e5fb188, 93f8c611, 671697c5, HFA-Sitzungsseite; Commit `12e1944` |
| Korrektur MA-PLURIVERSUM-1711 (Artikel 16.10.) | alt: „ab 18.15 Uhr in der Aula der Gesamtschule an der Schulstraße“, Workshop „10 bis 12 Uhr“; neu: ab 18 Uhr Aula Am Weiherhof in Niederzier, Workshop 11–13 Uhr, Abschnitt „Aktualisiert am 8. November“ mit altem Stand und abweichender Kalenderzeit; Titel mit „Merzenich“ und „Niederzier“ | `inhalte/meldungen/2026-10-16-amtsblatt-11.json` → `/leben/pluriversum-17-november-2026-gesamtschule-merzenich/` | Heimat-Info-Beitrag der Gemeinde 06.11.2026 09:30 Uhr, Kalendereintrag 671697c5, Amtsblatt S. 4; Commit `12e1944` |
| Korrektur MA-MARTINSZUEGE-2026 | alt: „kurze Sperrungen oder Wartezeiten einplanen“; neu: „mögliche Verzögerungen und kurzfristige Verkehrsbehinderungen im Umfeld der Züge“, Hinweis „Präzisiert am 8. November“ | `inhalte/meldungen/2026-10-16-amtsblatt-11.json` | Polizei-Original, Absatz zu Autofahrerinnen und Autofahrern; Commit `12e1944` |
| MA-FEUERWEHR-145-146-26 und MA-FEUERWEHR-147-26 (neu) | „Drei Feuerwehreinsätze in Merzenich: Hund aus Gartentor befreit, Wohnung nach Hausnotruf geöffnet“ (Kurzmeldung), Datumswiderspruch 146/26 benannt, Einordnung: sieben Einsätze „Person hinter Tür“ seit 05.09. im eigenen Archiv, darunter 123/26 am selben Steinpfahl | `inhalte/meldungen/2026-11-08-recherche-claude-morgens.json` → `/blaulicht/feuerwehr-merzenich-hund-gartentor-tierheim-tueroeffnung-steinpfahl-november-2026/` | Heimat-Info 7b525257, 978800db, 4c34e523 (Feuerwehr), Einsatzliste feuerwehr-merzenich.de (bis 140/26), sieben eigene Archivmeldungen (HTTP 200); Commit `7712685` |
| MA-PLURIVERSUM-1711 (Folgemeldung) | „Pluriversum der Gemeinde Merzenich zieht um: Abend am 17. November in der Gesamtschule Niederzier“ (Kurzmeldung) | gleiche Datei → `/leben/pluriversum-merzenich-17-november-2026-gesamtschule-niederzier-neuer-ort/` | wie oben, Amtsblatt als offizielle Quelle des alten Stands; Commit `7712685` |
| Kalender | 1 neu, 3 geändert, 20 bestätigt, siehe Abschnitt 6 | `inhalte/termine/` | Commits `12e1944`, `f4bb6a8` |

Quellenausnahmen: ChatGPT führte 145–147/26 als T2 (keine unabhängige Ereignisbestätigung). Umgesetzt, weil die Feuerwehr über ihre eigenen Einsätze die maßgebliche Quelle ist (Selbstauskunft im Prüfprotokoll, `feuerwehr-merzenich.de` als offizielle Quelle der Kurzmeldung) und ihre Berichte seit 03.11. ohnehin automatisch online gehen (CLAUDE-LAUF Abschnitt 3). Keine Gesundheitsangaben, keine Hausnummern.

Nicht genannt: Referentin und Moderatoren des Pluriversums (Namen im Gemeinde-Beitrag), Kontaktpersonen des Gemeindezentrums, Mitarbeiterin der Betreuungsbehörde.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Alle drei Korrekturen, beide neuen Meldungen und die Terminänderungen gehen ohne Freigabe online. Die Korrekturen stehen mit Hinweis „Korrigiert/Aktualisiert/Präzisiert am 8. November“ im Text. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| MA-S19-STELLWERK-07112026 | Störung Köln Messe/Deutz–Troisdorf seit 07.11. 08:00 Uhr, S19 ohne Halt am Flughafen; mobil.nrw 12:52 Uhr: Ende-Feld 13:30 Uhr, Text „Ende nicht abschätzbar“. Kein Ereignis in Merzenich, Stand ändert sich stündlich; eine Meldung wäre beim Erscheinen womöglich überholt. T2 bleibt |
| Bauausschuss 12.11. als Vorschau | alle öffentlichen Punkte (5–11.1) sind mit eigenen Artikeln vorberichtet und in der Wochenvorschau gebündelt; nichtöffentlich TOP 13 Deckensanierung Pastoratstraße/Wallstraße (137/2026) ohne Unterlagen. Folgemeldungen nach der Sitzung |
| Jugendgremium 04.11. | weiter kein Ergebnis im Ratsinformationssystem (ChatGPT 08.11. mittags) |
| Kindergottesdienst 08.11. | Heimat-Info nennt 10:30 Uhr, unser Termin 11 Uhr; der Termin liegt in der Vergangenheit und bleibt unverändert (Phase 1a, Punkt 5) |
| Sessionsauftakt 11.11. und Prinzenproklamation 13.11. der KG Jonge vom Berg | Vereinsseite wieder erreichbar (08.11. 12:50 Uhr), nennt aber weiter keinen Ort; Volkstrauertag 15.11. bestätigt |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| Rohdaten `imports/quellen/` (Abruf 07.11. 19:32 Uhr und 08.11. 13:02 Uhr) | `automatik-feuerwehr.mjs --trocken`, `automatik-termine.mjs --trocken` | Feuerwehr: 145/26 und 146/26 übersprungen („keine Bildklasse für das Stichwort Hund …“), 147/26 erst im Abruf 13:02 Uhr; Termine: Skatrunde 18.11. als Frühstückstreff erkannt (Fehlzuordnung) |
| Polizei Düren | Dienststellenliste nr/8, 12:43 Uhr | keine Meldung mit Merzenich oder Ortsteil seit 05.11. 12:06 Uhr (Martinszüge); 06.11. und 08.11. nur Düren und Jülich |
| Kreis Düren Presse | Presseübersicht, 12:47 Uhr | 06.11.: Gründertreffen, Jugendzeltplatz Hürtgenwald, Volkstrauertag in Hürtgen; nichts mit Merzenich-Bezug |
| Heimat-Info | Gemeindeseite, Beiträge und Veranstaltungskalender (Seite 1, 10 Einträge bis 22.11., 12:44 Uhr) | Gemeinde: Pluriversum-Beitrag 06.11.; Feuerwehr 145–147/26; Kalender: Gottesdienst 15.11. neu eingetragen, Pluriversum-Kopf jetzt Niederzier/18:15 Uhr. Seite 2 lädt nur per JavaScript nach (Lücke ab 22.11., Werkzeugpunkt) |
| Ratsinformationssystem | `termine.ics`, Sitzungsseiten Bauausschuss und HFA | Bauausschuss 12.11. unverändert (Tagesordnung seit 03.11.), HFA 19.11. weiter ohne Tagesordnung, RPA ohne Neues |
| Feuerwehr | feuerwehr-merzenich.de/einsaetze, 12:45 Uhr; Heimat-Info-Profil | Website bis 140/26; Heimat-Info 145, 146, 147/26 |
| Amtsblatt | Ausgabe 11 als PDF (`/bindata/`, HTTP 200), S. 4, 11, 15, 22, 29; Ausgabe 08 (Betreuungsbehörde) | Martinszüge Girbelsrath ohne Umzug bestätigt; Taizé 15.11. 17 Uhr; Wasserturm 21.11. 13–17 Uhr |
| Gemeinde-Website | Pressemitteilungen | HTTP 428 (Browserprüfung), nicht umgangen; ChatGPT liest sie |
| Bahn | mobil.nrw SPNV-Meldungen, 12:52 Uhr | S19-Störung weiter gelistet, siehe Abschnitt 3 |
| Vereinsseiten | `node qa/vereinsseiten.mjs` | 30 Seiten, 114 Datumsangaben, 18 Treffer „NICHT im Kalender“: KG Jonge vom Berg Januar 2027 (Orte offen), Lesementor (Düren, Nideggen), Schützen Morschenich ab 05.12. (Vereinsinterna), BSC-Schnupperkurs-Reihe bis 30.11. (offen); keiner in den nächsten 14 Tagen |
| ChatGPT-Dossiers 06.–08.11. | sechs Dossiers, zuerst KORREKTUREN | sechs Korrekturaufträge (unverändert seit 06.11.) umgesetzt; Themenkarten Pluriversum, Feuerwehr 145–147, S19 |

Sperren und Umwege: gemeinde-merzenich.de (Website) HTTP 428, Umweg über Amtsblatt-PDF und Heimat-Info; kgs-golzheim.gemeinde-merzenich.de HTTP 428.

## 5. Sport

Keine Pflichtprüfung am Sonntag. Die Freitagsprüfung am 06.11. ist mit dem Sitzungsausfall entfallen; nächste Pflichtprüfung Dienstag, 10.11.

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender` (08.11.2026 bis 07.01.2027, 39 Termine). Heimat-Info-Veranstaltungskalender der Gemeinde um 12:44 Uhr gelesen (Seite 1: 10 Einträge bis 22.11., alle Detailseiten geöffnet); alle Termine vom 10. bis 22.11. an der Quelle nachgeprüft.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Pluriversum, 17.11. | pluriversum-gesamtschule-merzenich-2026-11-17 | geändert: Aula Standort Niederzier, 18 Uhr (Kalender 18:15), Ortsteil Umgebung | Heimat-Info-Beitrag 93f8c611, Kalender 671697c5 | 2026-11-08 |
| Pluriversum-Workshop, 18.11. | pluriversum-workshop-engagement-gesamtschule-merzenich-2026-11-18 | geändert: 11–13 Uhr, Niederzier | Heimat-Info-Beitrag 93f8c611 | 2026-11-08 |
| Frühstücks- und Spieletreff, 11.11. | neu-fruehstuecks-und-spieletreff-fuer-senior-innen-merzenich-2026-11-11 | geändert: Ort „laut Veranstalter“, Titel ohne Gemeindehaus | Heimat-Info a5c2217f (ohne Ortszeile) | 2026-11-08 |
| Männergruppe/Skatrunde, 18.11. | maennergruppe-skatrunde-fuer-senioren-merzenich-2026-11-18 | **neu** | Heimat-Info a8c9e12b | 2026-11-08 |
| Ev. Gottesdienst mit Gedenken, 15.11. | ev-gottesdienst-gedenken-verstorbene-2026-11-15 | bestätigt, Heimat-Info-Eintrag 3f95f059 (08.11. 10:21 Uhr) ergänzt | Heimat-Info, Kirchenkalender | 2026-11-08 |
| Skatrunde 11.11., Frühstückstreff 18.11., Senioren-Nachmittag 18.11., Gottesdienst 22.11. (Niederzier) | je Slug | bestätigt | Heimat-Info 14702026, 9e5fb188, 43009e41, 65643863 | 2026-11-08 |
| Martinszug Golzheim 11.11. | martinszug-golzheim-2026-11-11 | bestätigt | Heimat-Info 791f374e | 2026-11-08 |
| Martinszug Merzenich 12.11., Martinsfeier Girbelsrath 13.11. | je Slug | bestätigt | Amtsblatt 11/2026 S. 11 | 2026-11-08 |
| Bauausschuss 12.11., HFA 19.11. | je Slug | bestätigt | Ratsinfo-Sitzungsseiten, `termine.ics` | 2026-11-08 |
| DRK-Infoabend 10.11. | infoabend-krisenvorsorge-drk-buergerhaus-merzenich-2026-11-10 | bestätigt | Heimat-Info 6b90fdb6 | 2026-11-08 |
| VHS-Bildvortrag Paris 10.11. | vhs-bildvortrag-paris-marienhof-2026-11-10 | bestätigt | VHS-Programmübersicht (Stadt Düren), Y1712A | 2026-11-08 |
| TTC-Jugendmeisterschaften 13.11. | ttc-merzenich-jugend-vereinsmeisterschaften-2026-11-13 | bestätigt | TTC-Terminschnittstelle | 2026-11-08 |
| Erste-Hilfe-Kurs 14.11. | erste-hilfe-kurs-golzheim-2026-11-14 | bestätigt | Heimat-Info 75056bea | 2026-11-08 |
| Volkstrauertag Merzenich und Morschenich 15.11. | volkstrauertag-merzenich-2026, volkstrauertag-gedenken-friedhof-morschenich-2026-11-15 | bestätigt | jonge-vom-berg.de (wieder erreichbar), schuetzen-morschenich.de | 2026-11-08 |
| Taizé-Gottesdienst 15.11. | taize-gottesdienst-morschenich-2026-11-15 | bestätigt (17 Uhr, Morschenich) | Amtsblatt 11/2026 S. 22 | 2026-11-08 |
| Mental Load online 18.11. | mental-load-online-vortrag-2026-11-18 | bestätigt | Kreis Düren | 2026-11-08 |
| Betreuungsbehörde 19.11. | sprechstunde-betreuungsbehoerde-rathaus-2026-11-19 | bestätigt (Fraktionsraum B, 10–12 Uhr) | Amtsblatt 08/2026 | 2026-11-08 |
| Tag der Modelleisenbahn 21.11. | tag-der-modelleisenbahn-wasserturm-2026-11-21 | bestätigt | Amtsblatt 11/2026 S. 29 | 2026-11-08 |

**Abgesagt:** keiner. Offene Hinweise „nachpruefen“: 0.

## 7. QA, Commit, Abgleich

Commits: `12e1944` (Korrekturen und Termine), `7712685` (zwei Meldungen), `f4bb6a8` (Kalenderpflege; Rebase-Konflikt nur in `docs/SHA256SUMS.txt`, Stand von main übernommen, Kette neu), Dokumentation folgt. Abgleich angestoßen 12:59 Uhr (`angestossen: true`), wp-cron HTTP 200.

Live-Nachweis 13:00 Uhr: beide neuen Meldungen HTTP 200, beide in der Nachrichten-Sitemap und oben in „Neu in Merzenich“ (Pluriversum vor Feuerwehr, dann Wochenvorschau und Bau-Turbo); Import-XML auf GitHub enthält beide Slugs, „Präzisiert am 8. November“ und die neue Pluriversum-Passage der Wochenvorschau. Korrekturen live: Wochenvorschau („Korrigiert am 8. November“), Pluriversum-Artikel („Aktualisiert am 8. November“), Martinszüge („Präzisiert am 8. November“); Termin 17.11. zeigt Niederzier und 18:00 Uhr, Workshop 11–13 Uhr. Skatrunde 18.11.: zum Zeitpunkt der Dossiererstellung noch 404 (Abgleich nach `f4bb6a8` steht aus), Nachweis unten.

Freigaben im Backend: nicht geprüft (kein Zugang in dieser Sitzung bereitgestellt).

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| feuerwehr-merzenich-hund-gartentor-tierheim-tueroeffnung-steinpfahl-november-2026 | blaulicht.rettung.tuer | feuerwehrhaus | Freiwillige Feuerwehr Werl Westoennen 02.jpg | CC BY-SA 3.0 | selbst angesehen: Feuerwehrhaus mit Einsatzwagen, keine Personen, Kennzeichen in Artikelgröße nicht lesbar; Symbolbild für drei Feuerwehreinsätze. Zuerst `blaulicht.rettung.tier` geprüft: zeigt eine Katze im Baum, passt nicht zu zwei Hunden und einer Türöffnung, verworfen |
| pluriversum-merzenich-17-november-2026-gesamtschule-niederzier-neuer-ort | leben.veranstaltung | veranstaltungssaal | Blick vomTechniker-Raum über die Sesselreihen … zur Bühne bei Aufräum-Beleuchtung ….jpg | CC BY-SA 3.0 | selbst angesehen: leerer Saal mit Bühne und Leinwand, keine Personen; passt zu einem Abend mit Impulsen, Musik und Projektionen in einer Aula |

Neu importierte Fotos: keine.

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll (offiziell / doppelt / Selbstauskunft) | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Feuerwehr 145–147/26 | 356 | 11 (3 Feuerwehrberichte, Einsatzliste, 7 Archivmeldungen) | 1 / 0 / 5 (3 Feuerwehr über eigene Einsätze, 2 eigenes Archiv) | 0 Wörter |
| Pluriversum in Niederzier | 320 | 4 | 1 / 0 / 3 | 0 Wörter (eine 9-Wort-Folge umformuliert) |
| Wochenvorschau nach Korrektur | 1.038 | 20 | 9 Einträge | 0 Wörter |

`node qa/eigenformulierung.mjs --datum 2026-11-08 --lauf morgens`: 0 Fehler, 0 Hinweise.

## 9. Offene Punkte für den nächsten Lauf

- **Werkzeug Feuerwehr-Automatik:** `deploy/automatik-feuerwehr.mjs` ordnet „Hund“, „TIN“ und „TIF“ keiner Bildklasse zu (Regel nur `/tier/i`); Tiereinsätze bleiben so liegen. Vorschlag: Muster um `hund|katze|pferd|^ti[nf]$` erweitern, mit Test. Nicht im Redaktionslauf geändert (CLAUDE-LAUF Abschnitt 6).
- **Werkzeug Termin-Automatik:** `deploy/automatik-termine.mjs` hält zwei verschiedene Veranstaltungen zur selben Zeit am selben Ort für einen Termin (Skatrunde/Frühstückstreff 18.11.). Abgleich sollte den Titel einbeziehen.
- **Heimat-Info-Kalender Seite 2** lädt nur per JavaScript; Quellenabruf und Lauf sehen nur die ersten zehn Einträge (derzeit bis 22.11.).
- Jugendgremium-Ergebnis; Bauausschuss 12.11. Folgemeldungen (Schule Golzheim, Solarparks, Bau-Turbo, EMS, Tiefbau, Poolplatz).
- Orte der KG-Jonge-vom-Berg-Termine (11.11., 13.11., Januar 2027).
- S19-Störung beobachten; Pluriversum-Beginn 18 oder 18.15 Uhr bei der Gemeinde klären, sobald eine Quelle es auflöst.
- `qa/redaktion-lauf.mjs`: Ratsinfo-Adressen mit Parameter nicht als Dublette werten (seit 05.11. offen).
