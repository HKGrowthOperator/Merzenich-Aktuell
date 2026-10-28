# Quellenkatalog der KI-Redaktion

Stand: 27.10.2026 (Grundlage: Quellenkarte vom 26.10.2026). Gilt für jeden Redaktionslauf nach [CLAUDE-LAUF.md](CLAUDE-LAUF.md); Schreib- und Belegregeln stehen in [SCHREIBREGELN.md](SCHREIBREGELN.md).

**Pflege:** Jede Änderung an einem Zugang (neue Sperre, neuer Umweg, neue Quelle, Quelle entfällt) wird im selben Lauf hier nachgetragen, mit Datum in der Spalte „Stand“ oder im Text, und im Dossier unter „Quellen dieses Laufs“ erwähnt. Eine Quelle wird nur aufgenommen, wenn sie mindestens einmal tatsächlich gelesen wurde (Belegstufe D oder R). Vermutungen stehen ausschließlich in Abschnitt 7.

**Grundsätze:**

- Primärquelle ist die Seite oder das Dokument des Herausgebers, auf dem die Angabe wörtlich steht: Beschlusstext und Vorlage im Ratsinformationssystem, Pressemitteilung der Behörde, Einsatzseite der Feuerwehr, Veranstalterseite, Spielbericht des Verbands.
- Heimat-Info ist der offizielle Kanal der Gemeinde Merzenich und gilt für Gemeindemitteilungen als Primärquelle, solange gemeinde-merzenich.de nur hinter der JavaScript-Prüfung erreichbar ist.
- Keine Quelle sind: Suchmaschinentreffer, KI-Zusammenfassungen, frühere Dossiers (sie sind Arbeitsaufträge), Social-Media-Beiträge ohne Original, Listen und Teaser ohne Detailseite, Artikel von merzenich-aktuell.de (nur Kontext, nie Beleg).
- `stand` ist das Datum, an dem die Quelle in diesem Lauf gelesen wurde, nicht das Veröffentlichungsdatum.
- PDFs werden mit `pdftotext -layout` gelesen; ICS-Kalender und JSON-APIs gelten als Primärquelle des Herausgebers.

## 1. Grundlagen und Belegstufen

### 1.1 Gelesene Grundlagen

| Datei | Verwendung |
|---|---|
| `deploy/quellen-abruf.mjs` (275 Zeilen, Stand 27.10.) | Skriptlogik der automatisierten Quellen, Listen-URLs, Detailmuster, Obergrenzen, Kommentare zu Sperren |
| `.github/workflows/quellen-abruf.yml`, `meldungen-automatik.yml`, `markt-abgleich.yml`, `editorial-images-v2-materialize.yml` | Zeitpläne und Auslöser |
| `imports/quellen/*.json`, `imports/quellen/bilder/index.json` | Tatsächlicher Rohdatenstand 26.10.2026, 11:03 bis 11:09 UTC (HTTP-Status, Titel, Textlängen) |
| `docs/RECHERCHE-QUELLEN.md` (Stand 24.09.2026, Nachtrag 13.10.) | Herkunft der Automatik, frühere Sperren |
| `docs/INHALTE-QUELLEN.md`, `docs/redaktion/README.md`, `docs/redaktion/CLAUDE-LAUF.md`, `docs/REDAKTIONELLER-STANDARD-V2.md`, `PROJECT.md` | Redaktionsregeln, Ressortliste, Laufzeiten |
| `docs/redaktion/dossiers/2026-10-25-nachlauf-claude.md` (Arbeitsweise, Quellensperren, Ressortabdeckung, Sport, Umkreis, Bilder) | Live-Abrufe aus der Sitzung am 25.10. |
| `docs/redaktion/dossiers/2026-10-25-abends.md` (RESEARCH, Rohdaten, VERIFY-Quellenregister) | ChatGPT-Abendlauf 25.10. |
| `docs/redaktion/dossiers/2026-10-26-morgens.md` (VERIFY, Quellenabdeckung der zwölf Ressorts, Quellenblatt, Sport) | ChatGPT-Morgenlauf 26.10. |
| `docs/redaktion/dossiers/2026-10-26-claude-nachmittags.md` (Abschnitt 2 „Quellen dieses Laufs“) | Claude-Umsetzungslauf 26.10. |
| `docs/redaktion/register.json` (Kopf, `research_runs`) | Laufhistorie |
| `inhalte/meldungen/2026-10-*.json`, `inhalte/termine/*.json`, `inhalte/umkreis/*.json` | Tatsächlich verwendete Quell-URLs (`grep -oh 'https://[^"]*' | sort -u`) |
| `chatgpt-site/api/sport-current.json` | Sportdatenstand (source FUSSBALL.DE, generated 2026-10-25T22:30:00+01:00) |

### 1.2 Belegstufen

| Stufe | Bedeutung |
|---|---|
| **R** | Rohdatenabruf über GitHub Actions in `imports/quellen/` vorhanden; HTTP-Status am 26.10.2026 (11:03 bis 11:09 UTC) wie angegeben. |
| **D** | In mindestens einem der vier Dossiers (25.10. abends, 25.10. Nachlauf Claude, 26.10. morgens, 26.10. nachmittags Claude) als tatsächlich gelesen protokolliert oder als `quelle`/`weitereQuellen` in `inhalte/` verwendet. |
| **S** | Nur im Skript, in der Dokumentation oder in der Laufbeschreibung der Routine genannt; im Zeitraum 25./26.10. nicht als gelesen protokolliert. |
| **V** | Vermutung oder offene Prüffrage. Steht ausschließlich in Abschnitt 7. |

Abrufrhythmus: „Ist“ ist belegt (Cron-Ausdruck, Git-Log, Routine, Dossierprotokoll). Ein Rhythmus-„Vorschlag“ ist eine Empfehlung dieser Karte und ist als solcher gekennzeichnet.

### 1.3 Zwei Ressortsysteme

Die Dossiers prüfen zwölf Recherche-Ressorts. Die Meldungen tragen eines von sieben Meldungsressorts (`deploy/meldungen.mjs` Zeile 33). Zuordnung, wie sie in den Paketen vom Oktober tatsächlich verwendet wurde:

| Recherche-Ressort (Dossiers) | Meldungsressort (`ressort`) |
|---|---|
| 1 Rathaus / Rat / Amtsblatt | rathaus |
| 2 Ortsteile | je Thema (Feld `ortsteil`) |
| 3 Polizei / Feuerwehr | blaulicht |
| 4 Verkehr / Baustellen / ÖPNV | leben |
| 5 Fußball und weitere Sportarten | sport (nicht auf der Startseite) |
| 6 Vereine / Ehrenamt | vereine, menschen |
| 7 Schulen / Kitas / Jugend | leben, rathaus |
| 8 Kirchen / Soziales / Senioren | leben |
| 9 Kultur / Veranstaltungen | leben, vereine; Termine in `inhalte/termine/` |
| 10 Wirtschaft / Arbeitsplätze / Immobilien | wirtschaft |
| 11 Umwelt / Energie | leben, rathaus |
| 12 Strukturwandel / Hambach / Bürgewald | rathaus |
| Nachbarkommunen | `inhalte/umkreis/` (kein Artikel) |

---

## 2. Automatisierte Zugänge (Ist-Zustand)

### 2.1 Quellenabruf: `quellen-abruf.yml` → `deploy/quellen-abruf.mjs` → `imports/quellen/`

- Zeitplan laut Workflow: `cron: '10 5,15 * * *'` (05:10 und 15:10 UTC; Dateikommentar: 06:10/16:10 MEZ bzw. 07:10/17:10 MESZ). Zusätzlich bei Push auf `deploy/quellen-abruf.mjs` oder den Workflow selbst, und von Hand (`workflow_dispatch`, Eingabe `nur` mit Werten `feuerwehr,polizei,gemeinde,heimatinfo,schulen,jobs,trauer,quellbilder`).
- Commit `recherche: Quellen abgerufen [skip ci]`, bis zu vier Push-Versuche mit `rebase -X theirs`.
- Laufzeitgrenze 20 Minuten; User-Agent `MerzenichAktuell-Recherche/1.0`; je Seite 1,2 Sekunden Pause; Timeout 25 Sekunden; Cookies und Weiterleitungen werden von Hand verfolgt (Gemeindeseiten setzen beim ersten Aufruf ein Cookie).
- Das Skript wertet nichts aus: je Seite URL, End-URL, Abrufzeit, HTTP-Status, Titel, Metadaten, Text (bis 40.000 Zeichen), Links (bis 400), Bilder mit alt-Text (bis 60).

**Tatsächliche Commit-Zeiten der Rohdaten im gelesenen Stand (Git-Log, UTC):** 21.10. 10:15 · 22.10. 10:17 · 23.10. 10:10 · 24.10. 09:39 · 25.10. 09:56, 14:43, 18:30 · 26.10. 11:09. Diese Zeiten liegen nicht bei 05:10/15:10 UTC. Das Nachmittagsdossier vom 26.10. nennt zudem einen Abruf „15:10 UTC“, der in diesem Repositorystand nicht enthalten ist. Beides steht als Prüfpunkt in Abschnitt 7.

| Datei | Herausgeber | Listen-URLs (Skript) | Detailmuster (Skript) | Max. | Format | Befund 26.10.2026, 11:03 bis 11:09 UTC |
|---|---|---|---|---|---|---|
| `feuerwehr.json` | Freiwillige Feuerwehr Merzenich | https://feuerwehr-merzenich.de/einsaetze und `?page=1` bis `?page=8` | `/einsaetze/<nummer><jahr>-<ort>-<strasse>` (z. B. `/einsaetze/13826-merzenich-steinweg`) | 90 | HTML | 9 Listen HTTP 200; 72 Einsatzseiten, davon 66 HTTP 200 und 6 `ECONNREFUSED` (u. a. 13826-merzenich-steinweg, 11226-girbelsrath-feld-richtung-binsfeld, 11126-burgewald-unterstrasse). Felder je Einsatz: Nummer, Ort/Straße, Stichwort/Kategorie, Alarmzeit, Bericht, Kräfte (`docs/RECHERCHE-QUELLEN.md`). |
| `polizei.json` | Polizei Düren über Presseportal (news aktuell) | Ortssuche https://www.presseportal.de/blaulicht/r/Merzenich, `/r/Golzheim`, `/r/Girbelsrath`, `/r/Morschenich`, `/r/B%C3%BCrgewald` | `/blaulicht/pm/<dienststelle>/<id>` | 60 | HTML | 5 Listen HTTP 200 (Merzenich 30 Meldungslinks, Golzheim 3, Girbelsrath 2, Morschenich 3, Bürgewald 0); 38 Meldungen HTTP 200. Jüngste Meldung mit Ortsbezug: 6359466 vom 26.09.2026. Die Ortssuche liefert nur Meldungen mit Ortsnennung; die Dienststellenliste nr/8 (alle POL-DN) ist nicht Teil des Abrufs. |
| `gemeinde.json` | Gemeinde Merzenich | https://www.gemeinde-merzenich.de/aktuelles/index.php und `/aktuelles/` | `/aktuelles/<…>.php` | 40 | HTML | 5 Seiten HTTP 200. Lesbar: `/aktuelles/amtsblaetter.php` (Index mit Erscheinungsdaten aller Ausgaben 2026) und `/aktuelles/pressemitteilungen.php` (Teaserliste, rund 9.000 Zeichen: Martinsumzüge, Krätzchensitzung, Altpapier u. a.). Keine Detailseite einer einzelnen Pressemitteilung im Abruf. Skriptkommentar: früher „403: Zugriff verweigert“ aus Actions; 302-Cookie-Schleife seit 24.09. gelöst. |
| `heimatinfo.json` | Heimat-Info (Kanal der Gemeinde; Vereine, Schulen, Feuerwehr, Kirche; ohne Gewerbe) | https://www.heimat-info.de/gemeinden/merzenich, dazu je Organisation `…/gemeinden/merzenich/organisationen/<slug>/beitraege` (41 Organisationen, Liste in Anhang B) | `/beitraege/<uuid>` | 200 | HTML | 42 Listen und 109 Beiträge, alle HTTP 200. Beitragstexte datiert bis 26.10.2026. Die Startseite zeigt nur die neuesten zehn Beiträge, deshalb die Organisationslisten (Skriptkommentar). |
| `schulen.json` | KGS Am Weinberg Merzenich; KGS Johann-Kaspar-Kratz Golzheim | https://kgs.gemeinde-merzenich.de/aktuelles/index.php; https://kgs-golzheim.gemeinde-merzenich.de/rubrik-unsere-schule/index.php | `/aktuelles/<…>.php` beider Hosts | 20 | HTML | 2 Listen und 19 Details HTTP 200. Merzenich: Meldungen `/aktuelles/news-2026/<slug>.php`, jüngste datierte vom 21.09.2026. Golzheim: nur Rubrikseite „Unsere Schule“ (rund 660 Zeichen), keine Aktuelles-Details. |
| `jobs.json` | Gemeinde Merzenich; Kreis Düren; jobsDN | https://www.gemeinde-merzenich.de/politik/stellenanngebote.php (Schreibweise wie im Repo); https://www.kreis-dueren.de/karriere; https://www.jobsdn.de/jobportal | keine | – | HTML | 3 Listen HTTP 200; jobsdn.de ohne Text (0 Zeichen, JavaScript). Hinweis im Rohdatensatz: „Recherche ohne BA-Listen-API; Einzelstellen werden separat an ihrer Originalquelle geprüft.“ Skriptkommentar: BA-Listenschnittstelle aus Actions seit September 2026 HTTP 403. |
| `trauer.json` | wirtrauern.de; Bestattungen Kick | https://www.wirtrauern.de/traueranzeigen-suche/merzenich; https://www.bestattungen-kick.de/ | keine | – | HTML | 2 Listen HTTP 200. Nur Recherchehinweis. Skript: keine automatische Extraktion von Personen oder Daten; die Suche nach „Merzenich“ trifft auch den Familiennamen außerhalb der Gemeinde. |
| `bilder/` | Polizei Düren über Presseportal | Fotos der Fassung „highlight“ zu Pressemitteilungen, die als `quelle.url` in `inhalte/meldungen` stehen; Bildhinweis über `https://www.presseportal.de/api/image_info.htx?id=<id>&story_id=<pm>&render=html`; Nutzungsbedingungen https://www.presseportal.de/nutzungsbedingungen | – | JPG, `index.json` | 4 Dateien zur Mitteilung 6238991 (`6238991-1.jpg` bis `-4.jpg`). Verwendung nur nach Sichtung (Kennzeichen, Gesichter) und mit `quellbild.freigegeben: true`; Credit „Polizei Düren / Presseportal“ (`docs/RECHERCHE-QUELLEN.md`). |

### 2.2 Weitere Automatik mit Quellenbezug

| Workflow / Skript | Auslöser (Ist) | Quellen | Wirkung |
|---|---|---|---|
| `meldungen-automatik.yml` → `deploy/automatik-feuerwehr.mjs` | nach erfolgreichem Lauf „Quellen abrufen (Recherche)“; von Hand mit `trocken` | `imports/quellen/feuerwehr.json` (Einsatzseiten) und `heimatinfo.json` (Feuerwehr-Beiträge), je Einsatznummer zusammengeführt | Legt neue Einsätze als Meldung in `inhalte/meldungen/<datum>-feuerwehr-automatik.json` an (vorhanden: 13.10., 18.10., 21.10.). Einsatznummern, die bereits in `inhalte/meldungen` stehen, gelten als gedeckt. Übersprungen: Einsatzort ohne Ortsteil, kein Bericht, keine Bildklasse, unter 80 Wörtern. Veröffentlicht nichts. |
| `markt-abgleich.yml` → `deploy/markt-abgleich.mjs` | `cron: '20 4 * * *'` (04:20 UTC), Push auf die genannten Dateien, von Hand | Bundesagentur, Jobdetail-Endpunkt `https://rest.arbeitsagentur.de/jobboerse/jobsuche-service/pc/v4/jobdetails/` (API-Key `jobboerse-jobsuche`); Karriereseiten der Anzeigen; Immobilien bei immowelt und ImmobilienScout24 (Namen laut Skriptkommentar) | Prüft nur bestehende Einträge in `market.json` nach (online/entfernt). Nimmt nichts Neues auf. |
| `editorial-images-v2-materialize.yml` → `deploy/import-editorial-photos.mjs` | nur `workflow_dispatch` (kein Cron im Workflow) | Wikimedia Commons API `https://commons.wikimedia.org/w/api.php`, Dateiseiten `https://commons.wikimedia.org/wiki/<Datei>` | Füllt Bildpools (`chatgpt-site/data/editorial-images/editorial-photo-pools.json`), Sichtung in `deploy/editorial-photo-review.json`. Einzige freie Bildquelle; nur CC0, Public Domain, CC BY, CC BY-SA (Dossier 25.10.). |
| `wordpress-abgleich.yml` | stündlich Minute 7 und nach QA/Build/Automatik (Projektangabe) | kein Rechercheweg | Publikationsweg (Import-XML → Entwürfe in den WordPress-Freigaben). Hier nur zur Abgrenzung genannt. |

### 2.3 Redaktionsläufe mit Live-Abrufen

| Lauf | Zeitplan (Ist) | Protokoll |
|---|---|---|
| Claude-Routine (selbstgebunden) | `CRON_TZ=Europe/Berlin 31 8,16 * * *` (Projektangabe), Läufe „morgens“ und „nachmittags“ | `docs/redaktion/dossiers/YYYY-MM-DD-claude-morgens.md` bzw. `-claude-nachmittags.md`; Ausnahme 25.10.: `2026-10-25-nachlauf-claude.md` |
| ChatGPT-Recherche | täglich etwa 08:00 und 18:00 Europe/Berlin (`docs/redaktion/README.md`, `PROJECT.md`) | `YYYY-MM-DD-morgens.md` bzw. `-abends.md`; letzter: `2026-10-26-morgens.md` (ab 08:05) |

Live-Abrufe aus der Claude-Sitzung treffen bei mehreren Hosts auf Sperren, die aus GitHub Actions nicht bestehen (Abschnitt 5). Websuche (US-Index) liefert für „Merzenich“ keine brauchbaren Treffer; Suchmaschinentreffer sind keine Quelle (Dossier 25.10., Projektdokumentation).

---

## 3. Quellenkarte je Ressort

Spalten: Herausgeber · Basis-URL und Adressmuster · Format · Was die Quelle belegt · Bekannte Sperre oder Umweg · Abrufrhythmus (Ist) · Belegstufe.

### 3.1 Rathaus / Rat / Amtsblatt

| Quelle | Herausgeber | Basis-URL, Muster | Format | Belegt | Sperre / Umweg | Rhythmus (Ist) | Stufe |
|---|---|---|---|---|---|---|---|
| Ratsinformationssystem | Gemeinde Merzenich (SD.NET) | https://ratsinfo.gemeinde-merzenich.de · Sitzungskalender `/termine` (HTML) und `/termine.ics` · Vorlagenliste `/vorlagen` · Tagesordnungen `/tops/?__=<token>` · Vorgänge `/vorgang/?__=<token>` · Dokumente `/sdnetrim/<token>/<Datei>.pdf` (Beschlusstexte, Beschlussvorlagen, Mitteilungsvorlagen, Bekanntmachungen, Anlagen) | ICS, HTML, PDF | Sitzungstermine (z. B. Bauausschuss 12.11., Gemeinderat 03.12.2026), Tagesordnungen, öffentliche Beschlüsse (116/2026 ELW, 130/2026 Schulumbau Golzheim, 95/2026 Neuland), Mitteilungsvorlagen (M128, M132 Ruhbens, M133 RWE, M134 Neuland), Bekanntmachung Ausschuss 15.10.2026 | HTML-Kalender nur per JavaScript; Vorlagen „nur per Formular“ (Dossier 25.10.). Umweg: `termine.ics` (vollständig gelesen), `/vorlagen` (gelesen 25./26.10.), sdnetrim-PDF-Direktlinks. System nannte 25.10. „Aktualisierung 21.10.2026 13:09“. Niederschrift Strukturausschuss 15.10. und Tagesordnung Jugendgremium 04.11. am 26.10. noch offen. | je Redaktionslauf (25.10., 26.10. morgens und nachmittags); 26.10. nachmittags: Vorlagenliste ohne neue Vorlagen seit dem Morgen | D |
| Amtsblatt | Gemeinde Merzenich | Index https://www.gemeinde-merzenich.de/aktuelles/amtsblaetter.php · PDF `https://www.gemeinde-merzenich.de/bindata/amtsblatt-2026/Merzenich_<NN>_2026-web.pdf` (verwendet: 07, 08, 10, 11; Seitenanker `#page=N`) | HTML-Index, PDF | Mitteilungen der Verwaltung und der Vereine, Veranstaltungskalender, Abgabe- und Erscheinungstermine; Ausgabe 11 vom 16.10.2026 (40 Seiten) ist die letzte sichtbare; Ausgabe 12: Abgabeschluss 03.11., Erscheinen 13.11. (Dossier 26.10. nachmittags) | Index aus Actions und Sitzung lesbar; PDF-Direktabruf am 25.10. abends zunächst Timeout, Klick vom Index erfolgreich. Bilder im Amtsblatt ohne Einzelerlaubnis gesperrt. | Erscheinen monatlich (Index listet 01/2026 bis 11/2026 mit Datum); Index im Actions-Abruf; PDF je Lauf gezielt | R, D |
| Pressemitteilungen und Bekanntmachungen der Gemeinde | Gemeinde Merzenich (Hosting kdvz Rhein-Erft-Rur) | Liste https://www.gemeinde-merzenich.de/aktuelles/pressemitteilungen.php · Detail `/aktuelles/pressemitteilungen/<slug>.php` (gelesen: `sankt-martin-2026.php`, `umfrage-nvp.php`, `dokumentation-masterplan.php`, `haushaltsinfo-2026.php`) · Bekanntmachungen https://www.gemeinde-merzenich.de/politik/oeffentliche-bekanntmachungen.php (Anhänge über WEB-IES-RPC-Download-Link) | HTML, PDF-Download | Gemeindemitteilungen (Martinszüge, Krätzchensitzung-Vorverkauf, Laub, Masterplan Bürgewald, Nahverkehrsplan), amtliche Bekanntmachungen (Haushaltssatzung 13.08.2026, Planfeststellung 09.10.2026) | Aus der Sitzung: JavaScript-Prüfung „Schutz vor automatisierten Zugriffen“ auf allen Unterseiten außer PDFs und Stellenseite (HTTP 200 ohne Inhalt). Laub-Detailseite 25.10. abends 404. Hambach-Bekanntmachungs-Download 25.10. gescheitert. Umweg: Heimat-Info (dieselben Mitteilungen, offizieller Kanal), Amtsblatt-PDF, Actions-Rohdaten (Teaserliste). Detailseiten sind nur aus Actions-Rohdaten oder im Browser lesbar. | Actions zweimal täglich (Teaserliste); Detailseiten je Lauf, soweit erreichbar | R (Liste), D (einzelne Details) |
| Heimat-Info, Kanal Gemeinde Merzenich („Die Rathaus-Infos“) | Gemeinde Merzenich über Heimat-Info | https://www.heimat-info.de/gemeinden/merzenich · https://www.heimat-info.de/gemeinden/merzenich/organisationen/gemeinde-merzenich/beitraege · Beitrag `/beitraege/<uuid>` · Veranstaltung `/veranstaltungen/<uuid>` | HTML | Gemeindemitteilungen mit Veröffentlichungszeit (Laub 07.10. 10:50, Schülerwanderung 23.10. 11:15, Bürgersprechstunde Golzheim 23.10.); gilt laut Projektdokumentation als Primärquelle für Gemeinde-Mitteilungen | Aus Actions vollständig lesbar. Aus der Sitzung: Startseite und Einzelbeiträge lesbar; Organisationsprofile laden Beiträge nur per JavaScript (Dossier 25.10.). 26.10. nachmittags: keine neue Gemeinde-Mitteilung seit 25.10. | Actions zweimal täglich; Live je Lauf | R, D |
| Haushalt | Gemeinde Merzenich | https://www.gemeinde-merzenich.de/aktuelles/pressemitteilungen/haushaltsinfo-2026.php | HTML | Chronik des Gemeindehaushalts 2026 (ohne Datumsangabe) | JavaScript-Prüfung wie oben | einmalig verwendet | D |

### 3.2 Ortsteile (Golzheim, Girbelsrath, Morschenich, Bürgewald)

Es gibt keine eigenen Nachrichtenportale der Ortsteile. Belegte Zugänge:

| Quelle | Herausgeber | Basis-URL, Muster | Format | Belegt | Sperre / Umweg | Rhythmus (Ist) | Stufe |
|---|---|---|---|---|---|---|---|
| Heimat-Info, Ortsbeiträge und Vereinsprofile | Gemeinde, Vereine (u. a. IG Golzheim aktiv, Dorfgemeinschaft Morschenich, Girbelsrath 2.0 Die Dorfwerkstatt, St. Lambertus Schützenbruderschaft Morschenich) | wie 3.1; Profile `…/organisationen/<slug>` (Liste Anhang B) | HTML | Ortsbezogene Mitteilungen, Vereinsleben | Profile aus der Sitzung nur Stammdaten (JavaScript); Beitragslisten aus Actions lesbar | Actions zweimal täglich | R, D |
| Amtsblatt, Vereinsmitteilungen je Ortsteil | Gemeinde Merzenich | wie 3.1 | PDF | Schützenfeste, Karnevalsgesellschaften, Martinszüge je Ortsteil (Amtsblatt 11 S. 9) | wie 3.1 | monatlich | D |
| Presseportal, Ortssuche | Polizei Düren | https://www.presseportal.de/blaulicht/r/<Ort> (Merzenich, Golzheim, Girbelsrath, Morschenich, Bürgewald) | HTML | Polizeimeldungen mit Ortsnennung | Bürgewald ohne Treffer (0 Links) | Actions zweimal täglich | R |
| Feuerwehr, Einsatzorte | Freiwillige Feuerwehr Merzenich | wie 3.3 | HTML | Einsätze mit Ortsteil und Straße (Hausnummern werden nicht übernommen) | Einsatzort ohne Ortsteil (Autobahn, Landstraße) wird von der Automatik übersprungen | Actions zweimal täglich | R, D |
| Vereinsseiten mit Ortsbezug | St. Lambertus Schützenbruderschaft Morschenich; KG Mir hahle Poohl Golzheim; KG Jonge vom Berg | https://www.schuetzen-morschenich.de (Majestätenlisten) · https://kg-mir-hahle-poohl.de (Startseite, `/veranstaltung/<slug>/`, `/tollitaeten-der-kg/`) · https://jonge-vom-berg.de/event/<slug>/ | HTML | Vereinschronik, Veranstaltungen (Kostümsitzung, Kranzniederlegung) | Marianische Schützenbruderschaft Golzheim https://www.schuetzen-golzheim.de HTTP 403 (Cloudflare); Umweg Heimat-Info-Profil, Amtsblatt | anlassbezogen | D |
| Bürgewald | Neuland Hambach GmbH; NRW.URBAN; Gemeinde; Ratsinformationssystem | siehe 3.12 | HTML, PDF | Masterplan, Vergabe, Ausschuss Bürgewald (RIS-Termin 26.11., Dossier 25.10.) | Masterplan-Gemeindeseite nennt Ziel „Ende 2025“ (Altstand); aktuelles Beschluss-/Vergabedatum fehlt (T2) | je Lauf | D |

Lücke: Girbelsrath und Bürgewald ohne eigene aktuelle Webquelle; Golzheim nur über Vereinsseiten, Schule (JavaScript) und Heimat-Info.

### 3.3 Polizei / Feuerwehr (blaulicht)

| Quelle | Herausgeber | Basis-URL, Muster | Format | Belegt | Sperre / Umweg | Rhythmus (Ist) | Stufe |
|---|---|---|---|---|---|---|---|
| Einsatzliste und Einsatzseiten | Freiwillige Feuerwehr Merzenich | https://feuerwehr-merzenich.de/einsaetze (`?page=N`); Detail `/einsaetze/<nnn><jj>-<ort>-<strasse>` (verwendet: 11226, 11326, 13526, 13626, 13826) | HTML | Einsatznummer, Alarmzeit, Ort/Straße, Stichwort, Bericht, Kräfte; Datum = Alarmierung laut Feuerwehr | Einsatzindex hinkt teils hinterher (25.10. abends: Index bis 16.10., Heimat-Info bis 23.10.; 25.10. nachmittags: Index bis 140/26, Heimat-Info 142/26 und 143/26). 6 Einsatzseiten am 26.10. `ECONNREFUSED`. Fotos: keine Lizenz auf der Seite, nur mit Freigabe der Feuerwehr. Datums-/Ortsabweichungen zu Heimat-Info kommen vor (136/26: 02.10. gegen 03.10.). | Actions zweimal täglich; Automatik danach; 26.10. nachmittags: kein Einsatz nach 143/26 | R, D |
| Feuerwehr-Beiträge Heimat-Info | Freiwillige Feuerwehr Merzenich über Heimat-Info | https://www.heimat-info.de/gemeinden/merzenich/organisationen/freiwillige-feuerwehr-merzenich/beitraege; Beitrag `/beitraege/<uuid>` | HTML | Einsatz-Nr, Datum, Stichwort, Meldung, Einsatzort, Kräfte; erscheinen meist früher als die Einsatzseite (`docs/RECHERCHE-QUELLEN.md`) | Beitragsliste im Morgenabruf 26.10. zeigte „veralteten Ausschnitt bis Anfang Oktober“, Rohdaten enthielten jüngere Einsätze (Dossier 26.10. morgens) | Actions zweimal täglich; Live je Lauf | R, D |
| Polizei Düren, Pressemitteilungen | Polizei Düren über Presseportal | Dienststelle https://www.presseportal.de/blaulicht/nr/8 · Einzelmeldung `/blaulicht/pm/8/<id>` (im Oktober genutzt: 6356706 bis 6377797) · Ortssuche `/blaulicht/r/<Ort>` (Actions) · RSS https://www.presseportal.de/rss/dienststelle_8.rss2 (Projektdokumentation; im Repo nicht verwendet) | HTML, RSS | Meldungen POL-DN mit Datum und Uhrzeit, Fotos mit Bildhinweis; 26.10.: fünf Meldungen 6377641 bis 6377797, keine mit Tatort Merzenich | 10. bis 13.10.2026 aus Actions nur „Security Check“ HTTP 503 (`docs/RECHERCHE-QUELLEN.md`); am 26.10. alle 43 Seiten HTTP 200. `/presse/aktuell.php`-ähnliche Übersichtsseiten gelten in `deploy/umkreis.mjs` nicht als Quelle für eine Einzelmeldung (`/blaulicht/nr/<n>` ausgeschlossen). | Actions zweimal täglich (Ortssuche); Dienststellenliste live je Lauf (25.10. 15:39 MEZ, 26.10. morgens und nachmittags) | R, D; RSS: S |
| Polizei Rhein-Erft-Kreis | Polizei Rhein-Erft-Kreis über Presseportal | Dienststelle nr/10374 (Projektangabe); Einzelmeldung `/blaulicht/pm/10374/<id>` (verwendet: 6362210, 6371830, 6376792, 6376862) | HTML | Meldungen aus Kerpen, Elsdorf (Umkreis) | wie Presseportal | live je Lauf | D |
| Weitere Blaulichtquellen im Umkreis | Feuerwehr Düren (Presseportal pm/134173); Unternehmensmeldung pm/62097 (Umkreis, Rubrik Wirtschaft) | `/blaulicht/pm/134173/<id>`, `/pm/62097/<id>` | HTML | je eine Umkreis-Meldung | – | anlassbezogen | D |

Regeln mit Quellenbezug: eine Einsatznummer, eine Meldung (`qa/pruefung.mjs` bricht bei Dubletten ab; vorher `grep -rl "Einsatz\(nummer\)\? NNN/26" chatgpt-site/blaulicht/`); doppelte Meldungen zur selben Pressemitteilung werden ebenfalls gesperrt; keine Hausnummern bei Polizei- und Feuerwehrthemen.

### 3.4 Verkehr / Baustellen / ÖPNV (leben)

| Quelle | Herausgeber | Basis-URL, Muster | Format | Belegt | Sperre / Umweg | Rhythmus (Ist) | Stufe |
|---|---|---|---|---|---|---|---|
| Verkehrsinfo A4 | Die Autobahn GmbH des Bundes | https://verkehr.autobahn.de/o/autobahn/A4/services/roadworks und https://verkehr.autobahn.de/o/autobahn/A4/services/closure | JSON-API | Baustellen und Sperrungen A4 (Wiegeanlage, Nachtsperrung Eschweiler-Ost–Weisweiler, Dauerbaustelle Königsdorf; „kein Eintrag für Düren und Merzenich“) | keine; Dossier 25.10. nennt zusätzlich A61/A44 als geprüft (Adressen dafür nicht im Repo) | live je Lauf (25.10. 15:41 bis 15:50 MEZ) | D |
| Baustellenübersicht Schiene | go.Rheinland | https://www.gorheinland.com/auskunft/baustellenuebersicht | HTML | Bauarbeiten Düren – Köln-Ehrenfeld 23.10. bis 30.10.2026 (Stand 09.10.2026) | keine | live je Lauf | D |
| Fahrplanänderungen Schiene | zuginfo.NRW über VRS | Plakat-PDF https://ekap-download.vrs.de/downloads/ems/Zuginfo_Ausfaelle_Koeln_Aachen_26bis28102026.pdf (FPLO 30754) · Haltestellenseite https://www.vrs.de/en/his/haltestelle/de:05358:4738 (Dossier 26.10. morgens) | PDF, HTML | S 19 Düren–Köln Hansaring 26.10. 21 Uhr bis 28.10. 5 Uhr mit SEV; RE 9, RB 20, RB 38, RE 1 betroffen; Buszeiten und Einstiegsstelle Merzenich nicht genannt | zuginfo.nrw und DB-Seiten nur per JavaScript; AVV HTTP 401; „DKB“ Zertifikatfehler (Dossier 25.10.) | anlassbezogen | D |
| Rurtalbus | Rurtalbus GmbH | https://www.rurtalbus.de (Meldungen `/JJJJ/MM/TT/<slug>/`, verwendet: Kaiserplatz 19.10.2026) | HTML | Haltestellenverlegungen, Busverkehr Düren | 25.10. nachmittags „Feed (200)“, 25.10. abends „Aktuellabruf nicht erreichbar“; Feed-Adresse nicht im Repo | anlassbezogen | D |
| Stadt Düren, Presse | Stadt Düren | https://www.dueren.de/de/verwaltung-politik/stadtverwaltung/presse/pressemitteilung/<slug> | HTML | Straßensperrungen (Bismarckstraße ab 26.10., Lendersdorfer Straße), Kaiserplatz, Schulen, Kultur | keine protokollierte Sperre (HTTP 200 am 25.10.) | live je Lauf | D |
| Nahverkehrsplan | Gemeinde Merzenich | https://www.gemeinde-merzenich.de/aktuelles/pressemitteilungen/umfrage-nvp.php | HTML | Umfrage mit Frist Ende Februar 2026 (abgelaufen; gesperrt) | JavaScript-Prüfung | einmalig | D |

Lücken: Straßen.NRW (keine Kreis-Düren-Inhalte maschinenlesbar), AVV und DB-Störungslage, Schienenersatzverkehr-Details; kein belegter Zugang zu Kreisstraßen-Sperrungen außer Kreis-Presse (K6-Sanierung 12.10.).

### 3.5 Fußball und weitere Sportarten (sport)

| Quelle | Herausgeber | Basis-URL, Muster | Format | Belegt | Sperre / Umweg | Rhythmus (Ist) | Stufe |
|---|---|---|---|---|---|---|---|
| FUSSBALL.DE | DFB | https://www.fussball.de · Mannschaft `/mannschaft/<slug>/-/saison/2627/team-id/<id>` (SC Merzenich I und III, SV Morschenich, Gegner) · Verein `/verein/<slug>/-/id/<id>` (SC Merzenich, FC Rhenania Girbelsrath, FC Golzheim) · Spieltag `/spieltag/<staffel>/-/spieldatum/<JJJJ-MM-TT>/staffel/<id>` · Spieltagsübersicht `/spieltagsuebersicht/<staffel>/-/staffel/<id>` (Kreisliga A Düren, Kreisliga B Staffel 2) · Spielseite `/spiel/<slug>/-/spiel/<id>` · Datenbericht `/mgc.newsdetail/-/article-uuid/<id>` | HTML | Tabellen als Text, Ansetzungen, „Nächstes Spiel“; Ergebnisse nur aus den automatischen Datenberichten im Klartext (z. B. Hambach – SC Merzenich 0:8, 25.10. 17:35). Datenstand `chatgpt-site/api/sport-current.json`: source FUSSBALL.DE, generated 2026-10-25T22:30:00+01:00, 16 Tabellenzeilen | Ergebnisse auf Spielseiten als Icon-Schrift (nicht lesbar); Störungsbanner „Aktuell technische Probleme“ am 25.10.; Tabellenköpfe teils alter Cache (Golzheim 26.10. morgens). SC Merzenich III, Spiel in Gereonsweiler 25.10.: Endstand unlesbar (offen) | live je Lauf (Spieltage Fr/Sa/So; 26.10. nachmittags nicht erneut abgerufen, da keine Herrenspiele) | D (32 eindeutige URLs im Oktober) |
| fupa.net | FuPa GmbH | Spielseite `https://www.fupa.net/match/<slug>-<jjmmtt>` · Tabelle `https://www.fupa.net/league/kreisliga-a-dueren/standing`, `…/kreisliga-b-2-dueren/standing` · `/matches` (Projektangabe) | HTML | Ergebnisse nur mit Klartext „Abpfiff“ (Koslar – Golzheim 0:5, 24.10.), Tabellen, Ansetzungen | Spielseiten sind JavaScript-Hüllen; Live-Zwischenstände werden nicht übernommen | live je Lauf | D |
| Vereinsseite SC 1919 Merzenich | SC 1919 Merzenich e.V. | https://www.scmerzenich.de/home/aktuelles-1/<slug>/ (Spieltagberichte 06 und 07 – 26/27) | HTML | Vereinsberichte, auch zweite und dritte Mannschaft, Tennis/Badminton (Stand 2023) | keine; Torschützennamen nur mit Quelle übernehmen | nach Spieltag | D |
| Tischtennis click-TT | Westdeutscher Tischtennis-Verband | https://wttv.click-tt.de/cgi-bin/WebObjects/nuLigaTTDE.woa/wa/clubInfoDisplay?club=8073 (TTC Merzenich) und `club=8036` (TV Golzheim) · `teamPortrait?championship=Aachen/Euregio+26/27&group=<g>&pageState=vorrunde&teamtable=<t>` · `clubMeetingReport?…&meeting=<id>` · `groupPage?…&group=<g>` | HTML | Spielpläne, Spielberichte, Tabellen (Datenstand ohne Veröffentlichungszeit) | 26.10. morgens „WTTV-Original Abruffehler“ | je Lauf bei Spieltagen | D |
| TTC Merzenich, Vereinsseite | TTC Merzenich | https://ttc-merzenich.de · Termin-Schnittstellen https://ttc-merzenich.de/wp-json/ttc/v1/events und https://ttc-merzenich.de/wp-json/ttc/v1/manual-events | HTML, JSON (WordPress-REST) | Termine, Turniere (AndroCup 31.10.), Spielersuche | Ergebnis- und Tabellenseiten der Vereinsseite nur per JavaScript | anlassbezogen | D |
| myTischtennis Turnierwelt | myTischtennis GmbH | https://www.mytischtennis.de/turnierwelt/turniere/<id> (844140) | HTML | Turnierdaten AndroCup | keine | anlassbezogen | D |
| Weitere Vereinsseiten (Hostnamen laut Dossier 25.10.) | TV Merzenich 1882 (tv-merzenich.de, nur HTTP); BSC Merzenich (bsc-merzenich.de, HTTP 200, News 21.07.2026); TV 1910 Girbelsrath (tv-girbelsrath.com, 200); Düren Demons (demonsfootball.de, 200); FC Golzheim (fc-golzheim.de) und SV Morschenich (sv-morschenich1925.de) nicht erreichbar; scm-badminton.de nicht erreichbar; Rhenania Girbelsrath ohne Website | nur Hostnamen protokolliert | HTML | Vereinsnachrichten ohne Datum, Übungsleitersuche | wie angegeben; Ligaseiten BVNR (Billard) nicht ausgewertet | einmalig 25.10. | D (Befund), Adressen nicht im Repo |

Sportregel: Ergebnis, offenes Spiel, nächstes Spiel, Tabelle und Datenzeitpunkt werden gemeinsam gepflegt; Sport erscheint nicht auf der Startseite.

### 3.6 Vereine / Ehrenamt (vereine, menschen)

| Quelle | Herausgeber | Basis-URL, Muster | Format | Belegt | Sperre / Umweg | Rhythmus (Ist) | Stufe |
|---|---|---|---|---|---|---|---|
| Heimat-Info, Vereinsprofile und Beiträge | 41 Organisationen (Anhang B), u. a. 1. FC Köln Fanclub Merzenich 1967, Geschichts- und Heimatverein, Freunde der Musik, Treckerfreunde, ISM e.V., LAG Rheinisches Revier an Inde und Rur | wie 3.1; `…/organisationen/<slug>/beitraege` | HTML | Vereinsmitteilungen mit Datum, Veranstaltungen, LEADER-Projekte (16.10.2026) | Aus Actions lesbar (41 Listen); aus der Sitzung nur Stammdaten. Fotos der Vereine gesperrt ohne schriftliche Freigabe. | Actions zweimal täglich | R, D |
| Amtsblatt, Vereinsmitteilungen | Gemeinde Merzenich | wie 3.1 | PDF | Schützen, Karneval, Adventskalender, Hallenbosseln, Ortsfest-Aufruf | wie 3.1 | monatlich | D |
| Karnevalsgesellschaften | KG Mir hahle Poohl Golzheim 1905 e.V.; KG Jonge vom Berg 1975 e.V. | https://kg-mir-hahle-poohl.de (Startseite, `/veranstaltung/grosse-kostuemsitzung/`, `/tollitaeten-der-kg/`) · https://jonge-vom-berg.de/event/kranzniederlegung-und-weckmannschiessen/ | HTML | Sitzungen, Tollitäten, Veranstaltungen | keine protokolliert | anlassbezogen | D |
| Schützenbruderschaften | St. Lambertus Morschenich; Marianische Golzheim; St. Sebastianus Merzenich | https://www.schuetzen-morschenich.de/schuetzenkoenige-innen.html, `/jungschuetzenprinz-sessin.html` · https://www.schuetzen-golzheim.de/aktuelles/termine/ · St. Sebastianus nur über Amtsblatt | HTML | Majestäten, Termine | schuetzen-golzheim.de HTTP 403 (Cloudflare); Umweg Heimat-Info, Amtsblatt | anlassbezogen | D |
| LandFrauen | Rheinischer LandFrauenverband, Kreisverband Düren-Jülich | https://www.rheinische-landfrauen.de/kreisverbaende/dueren-juelich/termine und `…/veranstaltungsshykalender/ansicht-veranstaltung?…` | HTML | Termine (Ortsverein Merzenich) | keine protokolliert | anlassbezogen | D |
| Blutspende | DRK-Blutspendedienst West | https://www.blutspendedienst-west.de/blutspendetermine/stadt/merzenich-05358040 · `/blutspendetermine/termine/<id>` · Umkreissuche `/blutspendetermine/termine?radius=10&term=52355+D%C3%BCren` | HTML | Spendetermine Merzenich und Umkreis | keine protokolliert | anlassbezogen | D |
| Kreis Düren, Ehrenamt und Jugend | Kreis Düren | https://www.kreis-dueren.de/presse/<slug>_<JJJJ-MM-TT>.php (Zeltplatz Finkenheide 21.10., Unser Dorf hat Zukunft 01.10.2026 und 05.09.2024) | HTML | Kreisweite Vereins- und Ehrenamtsthemen mit Bezug zur Gemeinde | `/presse/aktuell.php` scheiterte 25.10. abends; Index `/presse/` lesbar | live je Lauf | D |
| Düren-Crowd | Stadtwerke Düren | https://www.stadtwerke-dueren.de/ueber-uns/news-presse/aktuelle-meldungen/detailseite/kinder-foerdern-zukunft-gestalten | HTML | Crowdfunding-Aktion mit Frist 26.10. (gesperrt, vorbei) | keine | einmalig | D |

### 3.7 Schulen / Kitas / Jugend

| Quelle | Herausgeber | Basis-URL, Muster | Format | Belegt | Sperre / Umweg | Rhythmus (Ist) | Stufe |
|---|---|---|---|---|---|---|---|
| KGS Am Weinberg Merzenich | Schule (Hosting Gemeinde) | https://kgs.gemeinde-merzenich.de/aktuelles/index.php · `/aktuelles/news-2026/<slug>.php` · Termine https://www.gemeinde-merzenich.de/microsites/kgs_merzenich/termine/index.php | HTML | Schulnachrichten (Tag der offenen Tür 25.09., Zitronenaktion 21.09.2026, Martinszug), Terminübersicht | Rohdaten enthalten widersprüchliche ältere Tage-der-offenen-Tür-Angaben (Dossier 25.10. abends); nichts Neues seit 21.09. | Actions zweimal täglich | R, D |
| KGS Johann-Kaspar-Kratz Golzheim | Schule (Hosting Gemeinde) | https://kgs-golzheim.gemeinde-merzenich.de/rubrik-unsere-schule/index.php · Terminseite https://www.gemeinde-merzenich.de/microsites/kgs_golzheim/rubrik-unsere-schule/termine.php | HTML | Schulprofil; Termine am 26.10. „erreichbar, aber nicht auslesbar“ | JavaScript-Prüfung aus der Sitzung; Actions liefert nur die Rubrikseite | Actions zweimal täglich | R (Rubrikseite), D |
| Schulumbau Golzheim, OGS | Gemeinderat (RIS); Kreis Düren Schulaufsicht | RIS Beschluss 130/2026 (3.1) · https://www.kreis-dueren.de/kreishaus/amt/sa---schulaufsicht/ogs.php · https://www.kreis-dueren.de/presse/ogs-angebot_2026-10-09.php, `ogs_2025-05-30.php` | PDF, HTML | Nachforderungen der Bezirksregierung, OGS-Rechtsanspruch, Ganztagsvereinbarung | – | anlassbezogen | D |
| Kitas | Kreis Düren (Kita-Navigator); Gemeinde (Stellen Sportkita Bärenstark); Heimat-Info (Kindertagesstätte Windmühle, Förderverein der Grundschule) | https://kreisdueren.kita-navigator.org/ und `/suche` · https://www.gemeinde-merzenich.de/bindata/stellenausschreibungen/Stellenausschreibung_Uebungsleiter.pdf | HTML, PDF | Anmeldung Kita-Jahr 2027/2028, Übungsleiterstelle | keine vollständige Kita-Abdeckung (Dossier 26.10.) | anlassbezogen | D |
| Jugend | Kreis Düren (Jugendamt, Frühe Hilfen); RIS (Jugendgremium) | https://www.kreis-dueren.de/kreishaus/amt/amt51/zustaendigkeiten-jugendaemter.php · https://kreis-dueren.de/kreishaus/amt/amt51/fruehe_hilfen.php · Anmeldungen https://beteiligung.nrw.de/k/<id> (1031298, 1031471, 1028609) | HTML | Zuständigkeiten, Veranstaltungen, Anmeldewege | Tagesordnung Jugendgremium 04.11. am 26.10. offen | anlassbezogen | D |
| Berufsorientierung | Kreis Düren; Berufskolleg Kaufmännische Schulen; Polizei NRW | https://www.kreis-dueren.de/presse/fos-polizei-bewerbung_2026-10-13.php · https://bksd.de/vollzeit/fos-polizei · https://www.next-level-polizei.de/fachoberschule-polizei/ (und `/praxis/`, `/so-bewirbst-du-dich/voraussetzungen/`) | HTML | FOS Polizei, Bewerbung 2027/2028 | keine | einmalig | D |

Lücken: Kitas vor Ort (nur über Stellen und Heimat-Info), weiterführende Schulen (nur Stadt Düren), Jugendgremium (RIS-Tagesordnung offen), Offener Ganztag (Träger nicht erfasst).

### 3.8 Kirchen / Soziales / Senioren (leben)

| Quelle | Herausgeber | Basis-URL, Muster | Format | Belegt | Sperre / Umweg | Rhythmus (Ist) | Stufe |
|---|---|---|---|---|---|---|---|
| Pfarrbrief | Katholische Kirchengemeinden Merzenich und Niederzier | https://www.pfarrgemeinden-niederzier.de · Ausgabe 08/26: https://www.pfarrgemeinden-niederzier.de/wp-content/uploads/2026/09/Pfarrbrief-Oktober-2026-Pfarrgemeinden-Merzenich-Niederzier.pdf (36 Seiten, gilt 26.09. bis 02.11.2026) · Jahresarchiv `/pfarrbrief-aller-niederzierer-pfarrgemeinden/pfarrbriefe-als-pdf-dateien/2026-2/` | PDF, HTML | Gottesdienste, Allerheiligen, Rosenkranz-Ortswechsel ab 04.11., Sonntagscafé, Caritas-Gruppen, Brief zur Pfarrleitung (S. 2) | Archivseite zeigt im Abruf nur bis September, Oktober-PDF über Suche gefunden; keine Novemberausgabe online am 26.10.; Bilder im Pfarrbrief gesperrt | je Ausgabe (etwa fünf- bis sechswöchiger Gültigkeitszeitraum); nächste Ausgabe ab 03.11. erwartet (Ablauf 08/26) | D |
| Pfarrei St. Laurentius | Pfarrei St. Laurentius Merzenich | https://www.st-laurentius-merzenich.de/ | HTML | Pfarrbüro, Kirchen, Kontakt | Inhalt Stand 2019 (Dossier 25.10.) | selten | D |
| Bistum Aachen | Bistum Aachen | https://www.bistum-aachen.de · Rahmenkonzept-PDF `…/2025-05-15_Die-Leitung-im-Pastoralen-Raum_finaleFassung.pdf` | HTML, PDF | Rahmenkonzept Pastorale Räume (15.05.2025); keine Personalmeldung zu Merzenich/Niederzier gefunden (26.10.) | amtliche Ernennung der Leitung offen | anlassbezogen | D |
| Evangelische Gemeinde Düren, Bezirk 7 | Evangelische Gemeinde zu Düren | https://evangelischegemeinde-dueren.ekir.de/thema/bezirk-7-merzenich-nierzier/ | HTML | Gottesdienste und Termine Merzenich/Niederzier (5 Termine im Bestand) | keine protokolliert | anlassbezogen | D |
| Heimat-Info, kirchliche und soziale Träger | Lambertus-Kapelle Morschenich; Evangelische Gemeinde; Pfarre St. Marien Vettweiß (Pastoraler Raum); Freie Alten- und Krankenpflege Tagespflege; Gruppe Regenbogen; Lesementor Düren | Profile `…/organisationen/<slug>` (Anhang B) | HTML | Mitteilungen dieser Träger | Profile aus der Sitzung nur per JavaScript | Actions zweimal täglich | R |
| Kreis Düren, Soziales und Senioren | Kreis Düren | https://www.kreis-dueren.de/presse/das-persoenliche-budget_2026-10-19.php, `seniorenweihnacht_2026-09-30.php`, `vortrag-mental-load_2026-10-20.php` | HTML | Aktionstag Persönliches Budget 05.11., Seniorenweihnacht 24.11., Mental Load 18.11. | – | live je Lauf | D |

Lücken: Caritas und Sozialverbände nur über Pfarrbrief; Seniorenarbeit der Gemeinde nur über Termine (Seniorennachmittag) und Amtsblatt.

### 3.9 Kultur / Veranstaltungen

| Quelle | Herausgeber | Basis-URL, Muster | Format | Belegt | Sperre / Umweg | Rhythmus (Ist) | Stufe |
|---|---|---|---|---|---|---|---|
| Heimat-Info, Veranstaltungen | Gemeinde, Vereine | Einzelveranstaltung https://www.heimat-info.de/veranstaltungen/<uuid> (im Bestand 7 Adressen); Profil „Veranstaltungen Merzenich“ in Anhang B | HTML | Bürgersprechstunde, Vereinsveranstaltungen mit Ort und Zeit | Listenadresse der Veranstaltungen nicht im Repo (Abschnitt 7) | live je Lauf | D |
| Amtsblatt und Kulturkalender | Gemeinde Merzenich | wie 3.1 („Amtsblätter und Kulturkalender“) | PDF | Veranstaltungen der Vereine (Adventmarkt Heimatmuseum, Tag der Modelleisenbahn, Pluriversum) | wie 3.1 | monatlich | D |
| Gemeinde, Pressemitteilungen | Gemeinde Merzenich | wie 3.1 | HTML | Krätzchensitzung 23.01.2027 (Ortsfrage T2), Martinszüge | JavaScript-Prüfung | je Lauf | D |
| VHS Rur-Eifel | VHS Rur-Eifel (Zweigstelle Merzenich auch auf Heimat-Info) | https://www.vhs-rur-eifel.de/kurssuche/kurs/<Titel>/<Nr> (Y1712A, Y1315A) | HTML | Kurse mit Ort und Termin | keine protokolliert | anlassbezogen | D |
| Vereinsseiten (Karneval, Schützen) | siehe 3.6 | siehe 3.6 | HTML | Sitzungen, Feste | siehe 3.6 | anlassbezogen | D |
| Nachbarstädte, Kultur | Stadt Düren; Kolpingstadt Kerpen; Stadt Elsdorf; Stadt Jülich | siehe Abschnitt 4 | HTML | Comiciade, Herbstmarkt, Jüdische Kulturwochen u. a. (Umkreis) | Kerpen/Elsdorf teils kdvz-JavaScript oder Cookie-Seiten | live je Lauf | D |

Lücke: kein eigener, maschinenlesbarer Veranstaltungskalender der Gemeinde (hinter JavaScript); Gastronomie, Kirmes, private Veranstalter nicht erfasst.

### 3.10 Wirtschaft / Arbeitsplätze / Immobilien (wirtschaft)

| Quelle | Herausgeber | Basis-URL, Muster | Format | Belegt | Sperre / Umweg | Rhythmus (Ist) | Stufe |
|---|---|---|---|---|---|---|---|
| Stellen der Gemeinde | Gemeinde Merzenich | https://www.gemeinde-merzenich.de/politik/stellenanngebote.php · PDFs `/bindata/stellenausschreibungen/<Datei>.pdf` | HTML, PDF | Ausschreibungen (Fachbereichsleitung Planen und Bauen, Übungsleitung Sportkita); FBL3-Frist 15.11. offen | eine der wenigen Gemeindeseiten ohne JavaScript-Prüfung | Actions zweimal täglich | R, D |
| Kreis Düren, Karriere und Wirtschaftspresse | Kreis Düren | https://www.kreis-dueren.de/karriere · `/presse/<slug>_<datum>.php` (Meisterstipendium 01.10., VISIONplus 21.10., KI-Workshop 09.10., KI-Roadshow 26.10., Erntedankempfang 23.10., Kreisjahrbuch 22.09.) | HTML | Kreisstellen, Wirtschaftsförderung, Landwirtschaft | – | Actions (Karriere) zweimal täglich; Presse live je Lauf | R, D |
| jobsDN | jobsDN (Kreis Düren) | https://www.jobsdn.de/jobportal | HTML | Stellenportal | Text 0 Zeichen (JavaScript) | Actions zweimal täglich | R (leer) |
| Bundesagentur für Arbeit | BA Jobbörse | Jobdetail-API (2.2) | API (JSON) | Nachprüfung bestehender Stellen | Listen-API aus Actions seit September 2026 HTTP 403; nur Einzelprüfung | täglich 04:20 UTC (Markt-Abgleich) | S (Skript) |
| Firmenportale | Stollenwerk Gruppe | https://jobs.stollenwerk-konserven.de/stellenangebote/ · https://jobs2.stollenwerk-konserven.de/jobposting/<id> | HTML | Stellen am Standort Merzenich | – | anlassbezogen | D |
| IHK Aachen | IHK Aachen | https://www.ihk.de/aachen (Veranstaltungsseite KI-Roadshow `…/innovation/digitalisierung/ki-ropadshow-7153252`) | HTML | Veranstaltungen, Termine Düren 06.11. | – | anlassbezogen | D |
| Ideenwettbewerb | LANUK NRW | https://mit-tatkraft.de (8 Unterseiten: Projekt, Teilnahmebedingungen, Ablauf, Idee einreichen, Beratung, Aktuelles 15.09.2026) | HTML | Wettbewerb Rheinisches Revier | – | einmalig | D |
| Immobilien | immowelt, ImmobilienScout24 (Abgleich); Mybach Immobilien (Heimat-Info, Website) | https://www.myco-immobilien.de/ (Anschrift); Abgleich-Quellen ohne URL im Repo | HTML | Anschrift; Nachprüfung bestehender Anzeigen | keine systematische Prüfung von Immobilien-/Vertragsnachrichten (Dossier 26.10.) | täglich 04:20 UTC (nur Abgleich) | D, S |
| Heimat-Info, Gewerbe | Gewerbetreibende | Profile mit Kennzeichnung „Gewerbe“ | HTML | – | vom Quellenabruf bewusst ausgeschlossen (`!/\bGewerbe\b/`) | – | S |

Lücken: Einzelhandel, Gastronomie, Handwerk und Gewerbegebiet ohne Quelle; keine Arbeitsmarktzahlen; Stellenrecherche ohne Listen-API nur punktuell.

### 3.11 Umwelt / Energie

| Quelle | Herausgeber | Basis-URL, Muster | Format | Belegt | Sperre / Umweg | Rhythmus (Ist) | Stufe |
|---|---|---|---|---|---|---|---|
| Erdgasumstellung (H-Gas) | Leitungspartner GmbH | https://www.erdgasumstellung-dueren.de (`/allgemeine-infos/` mit Zeitplan je Ortsteil, `/faq/`, `/fuer-kunden/`, `/news/<slug>/`) | HTML | Umstellungsphasen Düren und Merzenich | Fließtext und Ortstabelle widersprechen sich; Thema T2 bis zur Klärung beim Netzbetreiber | anlassbezogen | D |
| Kreis Düren, Umwelt | Kreis Düren | `/presse/eichel-sammelaktion_2026-10-21.php`, `…_2026-10-23.php`, `baumausgabe_2026-09-28.php`, `windpocken_2026-10-14.php` | HTML | Eichel-Sammelaktion (Absage 23.10., Termin 06.11.), Baumausgabe, Gesundheit | – | live je Lauf | D |
| Gemeinde, Klima und Abfall | Gemeinde Merzenich | Amtsblatt 11 S. 11 „Klimaanpassung“; Laub über Heimat-Info 07.10.2026 und Amtsblatt 11 S. 7; Altpapier über Heimat-Info 13.10.2026 | PDF, HTML | Laubannahme Bauhof Girbelsrath bis 07.12., Altpapier | Gemeinde-Detailseite Laub 404; Heimat-Info als Zweitquelle ergänzt (26.10.) | je Lauf | D |
| Abfall, Entsorgung | Zweckverband Entsorgungsregion West (ZEW), Heimat-Info-Profil; Abfallkalender manuell in `deploy/gemeinde.json` | Profil in Anhang B | HTML | – (Inhalt im Zeitraum nicht geprüft) | – | – | S |
| Stromtrasse | Amprion GmbH (Heimat-Info-Profil „amprion-gmbh-merzenich“); Bestand | Profil in Anhang B | HTML | Bestand; neue Fortschritte am 26.10. nicht belegt | – | – | S |
| Landschaft, Natur | Neuland Hambach; LANUK | siehe 3.12 und 3.10 | HTML | Wildpferdeexkursion Sophienhöhe 29.10. | – | anlassbezogen | D |

Lücken: Warnlage (Profil „Warnmeldungen Merzenich“ auf Heimat-Info nicht geprüft), Wasserversorgung, Netzbetreiber-Originalaussage zur H-Gas-Umstellung, Amprion-Fortschritt.

### 3.12 Strukturwandel / Hambach / Bürgewald (rathaus)

| Quelle | Herausgeber | Basis-URL, Muster | Format | Belegt | Sperre / Umweg | Rhythmus (Ist) | Stufe |
|---|---|---|---|---|---|---|---|
| Neuland Hambach | Neuland Hambach GmbH | https://neuland-hambach.de (Projektangabe) · Artikel https://www.neuland-hambach.de/aktuelles/artikel/<slug> | HTML | Exkursionen, Projekte; keine Neuigkeiten zu Tagebausee, Rheinwassertransportleitung, Morschenicher Bucht, Bürgewald seit 01.10. (Dossier 25.10.) | – | live je Lauf | D |
| Planfeststellung Tagebausee Hambach | Bezirksregierung Arnsberg | https://www.bra.nrw.de/bekanntmachungen/antrag-auf-planfeststellung-nach-ss-68-abs-1-des-wasserhaushaltsgesetzes-whg-zur-herstellung-des | HTML (+ PDF 196 kB) | Antrag RWE Power 28.08.2026; Internet-Auslegung 09.11. bis 08.12.2026; Einwendungen bis 22.12.2026; Merzenich als voraussichtlich betroffene Gemeinde genannt | PDF-Download über die Gemeindeseite scheiterte (25.10.) | anlassbezogen; Auslegung ab 09.11. | D |
| Gemeinde, Bekanntmachung und Masterplan | Gemeinde Merzenich | https://www.gemeinde-merzenich.de/politik/oeffentliche-bekanntmachungen.php (09.10.2026) · `/aktuelles/pressemitteilungen/dokumentation-masterplan.php` | HTML | Veröffentlichung der Bekanntmachung; Masterplan-Dokumentation (Ziel Ende 2025, Altstand) | JavaScript-Prüfung | je Lauf | D |
| NRW.URBAN, bauhaus.nrw | NRW.URBAN | https://nrw-urban.de/transformation-des-ehemaligen-tagebau-dorfs-dynamischer-masterplan-buergewald/ (04.08.2025, Änderungsdatum 18.06.2026) · https://www.bauhaus.nrw/projekte/merzenich-neubau-buergerhaus-alte-musikschule | HTML | Masterplan Bürgewald (Fertigstellungsupdate), Bürgerhaus | Endfassung nicht komplett gelesen | anlassbezogen | D |
| Landesförderung | MHKBD NRW | https://www.mhkbd.nrw/foerderprogramme/stadtentwicklungsprogramm-rheinisches-revier-der-zukunft | HTML | Förderprogramm | – | einmalig | D |
| Ratsinformationssystem | Gemeinde Merzenich | Ausschuss Wirtschaftsförderung und Strukturentwicklung 15.10.2026 (Bekanntmachung, Tagesordnung); Mitteilungsvorlagen M132 (Ruhbens, CO2-Leitung; Anlage OGE-Präsentation 20.05.2026), M133 (RWE), M134 (Neuland); Beschluss 95/2026; Ausschuss Bürgewald 26.11. (Dossier 25.10.) | PDF, HTML | Projektstände aus Sicht der Verwaltung | Niederschrift 15.10. offen | je Lauf | D |
| Kreis Düren, Strukturwandel | Kreis Düren | https://kreis-dueren.de/kreishaus/amt/60/strukturwandel.php | HTML | Kreisebene | – | einmalig | D |
| Nachbarkommunen mit Revierbezug | Gemeinde Niederzier; Stadt Jülich | https://www.niederzier.de/aktuelles/news/<slug>.php · https://www.juelich.de/aktuelles?news=<id> (Brainergy Forum, LEADER, Neuland-Stellen) | HTML | LANUK-Preisgeld, Brainergy, Fördertopf | Niederzier aus der Sitzung hinter kdvz-JavaScript (25.10.) | live je Lauf | D |
| RWE Power | RWE | Presseliste nur per JavaScript (Dossier 25.10.); keine Adresse im Repo | – | – | Lücke | – | – |

Lücken: RWE-Originalquelle, Niederschrift Strukturausschuss, aktuelles Vergabedatum Bürgewald.

---

## 4. Umkreis (Nachbarkommunen, `inhalte/umkreis/`)

Zulässige `ort`-Werte laut `deploy/umkreis.mjs` Zeile 27: Düren, Niederzier, Nörvenich, Elsdorf, Kerpen, Kreis Düren (andere Kreisorte unter „Kreis Düren“ mit Ortsnennung im Titel). Tatsächlich verwendete Werte: Düren 41, Kreis Düren 33, Niederzier 13, Kerpen 7, Elsdorf 5, Nörvenich 3. Übersichts- und Suchseiten gelten nicht als Quelle einer Einzelmeldung (`UEBERSICHT`-Regex: `/index.php`, `/presse`, `/news`, `/meldungen.php`, `/amtsblatt.php`, `/blaulicht/nr/<n>`).

| Ort | Herausgeber, Basis-URL, Muster | Format | Sperre / Umweg | Verwendung im Bestand | Stufe |
|---|---|---|---|---|---|
| Düren | Stadt Düren https://www.dueren.de/de/verwaltung-politik/stadtverwaltung/presse/pressemitteilung/<slug>; Polizei Düren (3.3) | HTML | keine protokolliert | 16 Stadt-URLs, 47 Polizei-Einträge | D |
| Kreis Düren | Kreis Düren https://www.kreis-dueren.de/presse/ (Index) · Detail `/presse/<slug>_<JJJJ-MM-TT>.php` | HTML | `/presse/aktuell.php` scheiterte 25.10.; Index und Detailseiten lesbar; Listendatum und URL-Datum können abweichen (z. B. Zeltplatz: Liste 23.10., URL 21.10.) | 14 URLs | D |
| Niederzier | Gemeinde Niederzier https://www.niederzier.de/aktuelles/news/<slug>.php; Heimat-Info-Veranstaltungen; KG Frohsinn Oberzier | HTML | kdvz-JavaScript-Prüfung aus der Sitzung (25.10.); am 26.10. morgens nur Indexsichtung | 13 Einträge (meist Polizei/Heimat-Info) | D |
| Nörvenich | Gemeinde Nörvenich | HTML | kdvz-JavaScript-Prüfung (25.10.); Startseite „geöffnet“ (25.10. abends); keine Adresse im Repo | 3 Einträge (Polizei) | – |
| Kerpen | Kolpingstadt Kerpen https://www.stadt-kerpen.de/aktuelles/pressemitteilungen/<slug>.php; Polizei Rhein-Erft | HTML | kdvz-JavaScript-Prüfung am 25.10. gemeldet; dennoch drei Stadt-URLs im Bestand (Lesbarkeit schwankt) | 7 Einträge | D |
| Elsdorf | Stadt Elsdorf https://www.elsdorf.de/rathaus-service/verwaltung/aktuell/<id>/<slug> · Straßensperrungen https://www.elsdorf.de/rathaus-service/rathaus-service/aktuelle-strassensperrungen; Polizei Rhein-Erft | HTML | Cookie-/dynamische Seiten erschweren Elsdorf (26.10. morgens); 25.10. zehn Meldungen lesbar | 5 Einträge | D |
| Weitere Kreisorte (unter „Kreis Düren“) | Stadt Jülich https://www.juelich.de/aktuelles?news=<id>; Gemeinde Langerwehe (als `quelle` genannt); Polizei-Meldungen aus Aldenhoven, Vettweiß, Linnich, Titz, Jülich | HTML | – | 5 Jülich-URLs; Polizei je Anlass | D |

Nachbarchecks sind laut beiden Dossiers vom 26.10. überwiegend Indexsichtungen, keine vollständigen Detailrecherchen.

---

## 5. Sperren und Umwege (konsolidiert, Stand 25./26.10.2026)

| Host / Quelle | Sperre | Betroffener Zugang | Umweg (belegt) |
|---|---|---|---|
| gemeinde-merzenich.de (alle Unterseiten außer `/bindata/*.pdf` und `/politik/stellenanngebote.php`) | kdvz-JavaScript-Prüfung „Schutz vor automatisierten Zugriffen“, HTTP 200 ohne Inhalt | Sitzung | Heimat-Info (Gemeindekanal), Amtsblatt-PDF, Ratsinformationssystem, Actions-Rohdaten (Teaserliste `pressemitteilungen.php`, Index `amtsblaetter.php`) |
| kgs-golzheim.gemeinde-merzenich.de | dieselbe JavaScript-Prüfung | Sitzung; Actions liefert nur Rubrikseite | KGS Merzenich erreichbar; Golzheim offen |
| niederzier.de, Nörvenich, stadt-kerpen.de | kdvz-JavaScript-Prüfung (25.10.) | Sitzung | Kreis- und Polizeikanal; Heimat-Info Niederzier-Veranstaltungen |
| ratsinfo.gemeinde-merzenich.de `/termine` (HTML), Vorlagen | Kalender nur per JavaScript; Vorlagen nur per Formular | Sitzung | `termine.ics`; `/vorlagen`; sdnetrim-PDF-Direktlinks |
| schuetzen-golzheim.de | HTTP 403 (Cloudflare) | Sitzung | Heimat-Info-Profil, Amtsblatt |
| presseportal.de | HTTP 503 „Security Check“ 10. bis 13.10.2026 | Actions | am 26.10. aufgehoben (43/43 Seiten HTTP 200); letzte lesbare Fassung damals Commit 0b88a926 |
| fussball.de | Ergebnisse als Icon-Schrift; Störungsbanner | alle | Datenberichte `/mgc.newsdetail/…` (Klartext), fupa „Abpfiff“, Vereinsseiten |
| fupa.net Spielseiten | JavaScript-Hüllen | alle | Liga-Seiten `/standing`, `/matches`; Klartext „Abpfiff“ |
| wttv.click-tt.de | Abruffehler 26.10. morgens | Sitzung | ttc-merzenich.de-Schnittstellen (Termine), erneuter Abruf |
| ttc-merzenich.de Ergebnisseiten | JavaScript | alle | `/wp-json/ttc/v1/events`, `/manual-events`; click-TT |
| heimat-info.de Organisationsprofile | Beiträge nur per JavaScript | Sitzung | Actions-Rohdaten (`…/beitraege`-Listen lesbar), Einzelbeiträge `/beitraege/<uuid>` direkt |
| feuerwehr-merzenich.de Einsatzseiten | 6 von 72 `ECONNREFUSED` (26.10.) | Actions | Heimat-Info-Feuerwehrbeiträge |
| Bundesagentur Listen-API | HTTP 403 seit September 2026 | Actions | Jobdetail-Endpunkt (nur Einzelprüfung), Gemeinde-/Kreis-/Firmenseiten |
| jobsdn.de | Text 0 Zeichen (JavaScript) | Actions | – |
| commons.wikimedia.org (API und HTML) | HTTP 429 nach vier Suchanfragen (25.10.) | Sitzung | Importlauf über GitHub Actions (`editorial-images-v2-materialize.yml`) |
| zuginfo.nrw, DB | JavaScript | Sitzung | VRS-Plakat-PDF (ekap-download.vrs.de), go.Rheinland |
| AVV | HTTP 401 | Sitzung | – |
| Straßen.NRW | keine Kreis-Düren-Inhalte lesbar | Sitzung | Kreis-Presse (Kreisstraßen), Stadt Düren |
| RWE Power Presseliste | JavaScript | Sitzung | RIS-Mitteilungsvorlagen (M133), Neuland Hambach, Bezirksregierung |
| aachener-zeitung.de | HTTP 403; Paywall | Sitzung | nicht genutzt |
| Websuche (US-Index) | keine brauchbaren Treffer | Sitzung | direkte Quellen |
| merzenich-aktuell.de | Timeout beim Abruf (25.10. abends) | Sitzung | keine Aussage über Störung ableiten |
| Bildrechte | Fotos von Gemeinde, Feuerwehr, Heimat-Info, Vereinen, Kreis, Neuland/RWE, Pfarrbrief, Amtsblatt ohne schriftliche Freigabe gesperrt; Copyright-Vermerk ist keine Freigabe | alle | Wikimedia Commons (CC0, PD, CC BY, CC BY-SA); Presseportal-Fotos nach Sichtung mit `quellbild.freigegeben` |

---

## 6. Lücken: Ressorts und Themen mit dünner Quellenlage

Reihenfolge nach Gewicht für die Lokalberichterstattung.

1. **Gemeinde-Detailseiten und Niederschriften (Rathaus).** Pressemitteilungen der Gemeinde sind aus der Sitzung nur als Teaser (Actions-Rohdaten) erreichbar; Niederschriften (Strukturausschuss 15.10.) und Tagesordnungen (Jugendgremium 04.11.) offen. Umweg Heimat-Info deckt nur, was die Gemeinde dort spiegelt.
2. **Verkehr / ÖPNV.** Keine maschinenlesbare Quelle für Straßen.NRW, AVV (401), DB-Störungen (JavaScript), SEV-Details. Belegt sind nur Autobahn-API, go.Rheinland, VRS-Plakate, Stadt Düren, Rurtalbus-Einzelmeldungen.
3. **Wirtschaft / Arbeitsplätze / Immobilien.** Keine Listen-API für Stellen; Immobilien nur als Nachprüfung bestehender Anzeigen; Betriebe, Handwerk, Einzelhandel, Gastronomie und Gewerbegebiet ohne Quelle; Gewerbe auf Heimat-Info bewusst ausgeschlossen.
4. **Schulen / Kitas / Jugend.** KGS Golzheim hinter JavaScript (Termine nicht auslesbar); Kitas nur punktuell (Stellen, Kita-Navigator); Jugendgremium offen; OGS-Träger nicht erfasst.
5. **Ortsteile Girbelsrath und Bürgewald.** Keine eigene aktuelle Webquelle; Girbelsrath über Heimat-Info (Dorfwerkstatt, TV 1910) und Feuerwehr; Bürgewald über RIS, Neuland, NRW.URBAN mit Altständen.
6. **Weitere Sportarten.** Tischtennis belegt; Billard (BVNR-Ligaseiten nicht ausgewertet), Badminton, Tennis (Stand 2023), Football (Demons) nur Befunde; Jugendfußball nicht erfasst; Vereinsseiten FC Golzheim und SV Morschenich nicht erreichbar.
7. **Umwelt / Energie.** H-Gas-Widerspruch ungeklärt (Netzbetreiber-Originalaussage fehlt); Amprion-Fortschritt und Warnlage ungeprüft; Wasserversorgung ohne Quelle.
8. **Kirchen / Soziales.** Nur katholische Pfarrbrief-PDF (Novemberausgabe fehlt), Bezirksseite der evangelischen Gemeinde, Kreis-Presse; Caritas, Seniorenbeirat, Tagespflege nur indirekt.
9. **Strukturwandel.** RWE-Originalquelle nur per JavaScript; aktuelles Vergabe-/Beschlussdatum Bürgewald fehlt.
10. **Nachbarkommunen Niederzier, Nörvenich, Kerpen.** kdvz-JavaScript-Prüfung aus der Sitzung; Lesbarkeit schwankt (Kerpen-URLs im Bestand).
11. **Bilder.** Einzige freie Quelle Wikimedia Commons, aus der Sitzung gedrosselt (429); Pools für Sturm/Feuerwehr, St. Martin, Senioren, Wandern, Eicheln, Karneval, Nahverkehr, Stromtrasse, Modelleisenbahn, Advent, Blutspende, Bauhof, Ratssaal, Sophienhöhe, Kreishaus ohne Aussage (Dossier 25.10.).
12. **Polizei-Ortsbezug.** Die Ortssuche des Presseportals liefert nur Meldungen mit Ortsnennung (zuletzt 26.09.2026); die Dienststellenliste nr/8 wird nicht automatisiert abgerufen, sondern nur live gelesen.

---

## 7. Vermutungen und Prüfpunkte (keine Belege)

| Nr. | Punkt | Status |
|---|---|---|
| V1 | Der Workflow nennt `cron: '10 5,15 * * *'`; die Rohdaten-Commits liegen 21. bis 24.10. bei 09:39 bis 10:17 UTC und am 26.10. bei 11:09 UTC. Ursache (verzögerter GitHub-Schedule, manuelle Auslösung, späte Erweiterung des Cron) nicht aus dem Repo ableitbar. | zu prüfen (Actions-Laufliste) |
| V2 | Das Nachmittagsdossier 26.10. nennt einen Feuerwehr-Rohdatenabruf „15:10 UTC“; im gelesenen Repositorystand ist `imports/quellen/feuerwehr.json` auf 11:03 UTC datiert (Commit 41b27d6). Entweder ist der Checkout älter als der Lauf oder die Angabe im Dossier bezeichnet den Plan statt den Ist-Abruf. | zu prüfen (`git fetch`) |
| V3 | Presseportal-RSS https://www.presseportal.de/rss/dienststelle_8.rss2 steht nur in der Projektdokumentation; ob er die Dienststellenliste vollständig und zeitnah liefert, ist nicht protokolliert. Ein entsprechender RSS für nr/10374 ist nicht belegt. | zu prüfen |
| V4 | Heimat-Info: Eine Listenadresse für Veranstaltungen der Gemeinde ist im Repo nicht enthalten; bekannt sind nur Einzeladressen `/veranstaltungen/<uuid>` und das Profil „veranstaltungen-merzenich“ (Anhang B). | zu prüfen |
| V5 | Rurtalbus „Feed (200)“ (Dossier 25.10.): Adresse nicht im Repo. | zu prüfen |
| V6 | Autobahn-API für A61 und A44 (Dossier 25.10. „A4/A61/A44 (200)“): nur A4-Adressen im Repo; ob das Muster `…/o/autobahn/<Autobahn>/services/<dienst>` für A61/A44 gilt, ist nicht belegt. | zu prüfen |
| V7 | Heimat-Info-Profile „warnmeldungen-merzenich“, „zweckverband-entsorgungsregion-west-zew-f-merzenich“, „amprion-gmbh-merzenich“, „kindertagesstaette-windmuehle“, „volkshochschule-rur-eifel-zweigstelle-merzenich“ existieren in den Rohdaten; Inhalt und Aktualität im Zeitraum nicht geprüft. | zu prüfen |
| V8 | Ob die Gemeinde einen RSS-/Presseverteiler anbietet, der die kdvz-Sperre umgeht, ist offen (Nachfassfrage im Dossier 25.10.). | Frage an die Gemeinde |
| V9 | Die Kreis-Presse-Detailseiten tragen das Datum in der URL; Listendatum und URL-Datum weichen ab. Welches Datum als Veröffentlichung gilt, ist redaktionell festzulegen. | Festlegung |
| V10 | Vorschlag (keine Ist-Angabe): Für Live-Quellen je Lauf eine feste Mindestliste (RIS `termine.ics` und `/vorlagen`, Heimat-Info Gemeinde- und Feuerwehrkanal, Presseportal nr/8 und nr/10374, Kreis-Presse-Index, Stadt Düren Presse, Autobahn-API A4, go.Rheinland; an Spieltagen FUSSBALL.DE-Datenberichte und click-TT; monatlich Amtsblatt-Index, Pfarrbrief-Archiv). | Vorschlag |

---

## Anhang A: URL-Inventar aus `inhalte/` (eindeutige URLs je Host, Stand 26.10.2026)

Ermittelt mit `grep -oh 'https://[^"]*' <Dateien> | sort -u`, Host ohne `www.`.

**`inhalte/meldungen/2026-10-*.json` (Oktober-Pakete):** fussball.de 32 · heimat-info.de 24 · ratsinfo.gemeinde-merzenich.de 20 · kreis-dueren.de 20 · gemeinde-merzenich.de 18 · mit-tatkraft.de 8 · merzenich-aktuell.de 7 (Eigenverweise) · feuerwehr-merzenich.de 6 · wttv.click-tt.de 5 · fupa.net 5 · erdgasumstellung-dueren.de 5 · ttc-merzenich.de 3 · next-level-polizei.de 3 · verkehr.autobahn.de 2 · schuetzen-morschenich.de 2 · pfarrgemeinden-niederzier.de 2 · kreisdueren.kita-navigator.org 2 · kg-mir-hahle-poohl.de 2 · jobs2.stollenwerk-konserven.de 2 · dueren.de 2 · beteiligung.nrw.de 2 · asg-goe.de 2 · je 1: st-laurentius-merzenich.de, scmerzenich.de, schuetzen-golzheim.de, rurtalbus.de, rheinische-landfrauen.de, presseportal.de, niederzier.de, neuland-hambach.de, mytischtennis.de, myco-immobilien.de, mhkbd.nrw, jobs.stollenwerk-konserven.de, ihk.de, gorheinland.com, ekap-download.vrs.de, bra.nrw.de, blutspendedienst-west.de, bksd.de, bistum-aachen.de, bauhaus.nrw.

**`inhalte/termine/*.json`:** ratsinfo.gemeinde-merzenich.de 9 · gemeinde-merzenich.de 8 · heimat-info.de 6 · kreis-dueren.de 5 · vhs-rur-eifel.de 2 · ttc-merzenich.de 2 · blutspendedienst-west.de 2 · beteiligung.nrw.de 2 · je 1: schuetzen-golzheim.de, rheinische-landfrauen.de, pfarrgemeinden-niederzier.de, neuland-hambach.de, mytischtennis.de, kg-mir-hahle-poohl.de, jonge-vom-berg.de, ihk.de, evangelischegemeinde-dueren.ekir.de. Verwendete `quelle`-Namen in Terminen sind Freitext (keine Zählung).

**`inhalte/umkreis/*.json`:** presseportal.de 53 · dueren.de 16 · kreis-dueren.de 14 · heimat-info.de 6 · juelich.de 5 · elsdorf.de 4 · stadt-kerpen.de 3 · neuland-hambach.de 1. `quelle`-Werte: Polizei Düren 47, Stadt Düren 16, Kreis Düren 14, Stadt Jülich 5, Stadt Elsdorf 4, Polizei Rhein-Erft-Kreis 4, Kolpingstadt Kerpen 3, Gemeinde Niederzier 2, je 1 Veranstaltungen Niederzier, Neuland Hambach GmbH, NORMA, LAG Rheinisches Revier an Inde und Rur, KG Frohsinn 1971 Oberzier e.V., Gemeinde Langerwehe, Feuerwehr Düren. `rubrik`-Werte: Blaulicht 33, Verkehr 27, Kultur 7, Wirtschaft 6, Gesundheit 5, Stadtleben 3, Politik 3, Leben 3, Klima 3, Freizeit 3, Vereine 2, Veranstaltungen 2, Senioren 2, je 1 Polizei, Landwirtschaft, Dorfentwicklung.

## Anhang B: Heimat-Info-Organisationen im Quellenabruf (41, `imports/quellen/heimatinfo.json`, 26.10.2026)

Slugs unter `https://www.heimat-info.de/gemeinden/merzenich/organisationen/<slug>` (Beitragsliste jeweils `…/beitraege`), ohne Gewerbe:

gemeinde-merzenich · freiwillige-feuerwehr-merzenich · lag-rheinisches-revier-an-inde-und-rur-ev-gemeinde-merzenich · 1-fc-koeln-fanclub-merzenich-1967 · lambertus-kapelle-morschenich · pfadfinder-merzenich-dpsg-st-laurentius · evangelische-gemeinde-1 · amprion-gmbh-merzenich · heimat-info-fur-merzenich · veranstaltungen-merzenich · volkshochschule-rur-eifel-zweigstelle-merzenich · warnmeldungen-merzenich · zweckverband-entsorgungsregion-west-zew-f-merzenich · kgs-am-weinberg-merzenich · kindertagesstaette-windmuehle · freie-alten--und-krankenpflege-e-v-tagespflege-merzenich · billardsportclub-merzenich-1970-e-v · dorfgemeinschaft-morschenich · fc-golzheim-1928-e-v · fc-rhenania-girbelsrath-1931-e-v · freunde-der-musik-merzenich · geschichts--und-heimatverein-merzenich-e-v · girbelsrath-2-0-die-dorfwerkstatt · gruppe-regenbogen · gut-schuss-merzenich · ig-golzheim-aktiv · inklusives-soziales-miteinander-merzenich-e-v-ism · kg-jonge-vom-berg-1975-e-v · kg-mir-hahle-poohl-1905-e-v · kk-klub-waldesgrun · landfrauenverband-ev-kreisverband-duren-julich-merzenich · lesementor-duren-e-v-merzenich · sc-merzenich-tennisabteilung · sc1919merzenich-e-v · scm-badminton-merzenich · st-lambertus-schuetzenbruderschaft-morschenich-e-v · treckerfreunde-merzenich · tv-merzenich-1882-e-v · tv1910e-v-girbelsrath · ul-aero-club-morschenich-e-v · vereinigung-der-freunde-und-foerderer-der-johann-kasper-kratz-grundschule-merzenich--golzheim-e-v

## Anhang C: Rohdatenstand `imports/quellen/` am 26.10.2026 (UTC)

| Datei | abgerufen | Listen | Details | HTTP-Status |
|---|---|---|---|---|
| feuerwehr.json | 11:03:11 | 9 | 72 | 75 × 200, 6 × `fehler: ECONNREFUSED` |
| polizei.json | 11:04:16 | 5 | 38 | 43 × 200 |
| gemeinde.json | 11:04:24 | 2 | 3 | 5 × 200 |
| heimatinfo.json | 11:08:44 | 42 (41 Organisationen) | 109 | 151 × 200 |
| schulen.json | 11:09:15 | 2 | 19 | 21 × 200 |
| jobs.json | 11:09:21 | 3 | 0 | 3 × 200 (jobsdn.de ohne Text) |
| trauer.json | 11:09:26 | 2 | 0 | 2 × 200 |
| bilder/index.json | – | – | 4 Dateien | Quelle PM 6238991 |

Vergleich mit den Dossiers: 25.10. Nachlauf nutzte den Stand 09:47 bis 09:56 UTC; 25.10. abends den Stand 15:36 bis 15:42 MEZ (Commit 14:43 UTC); 26.10. morgens den Stand 18:24 bis 18:30 UTC des 25.10. (Commit 18:30 UTC).

## Nachtrag 28.10.2026 – im Morgendossier tatsächlich gelesene Zugänge

Diese Ergänzung beschreibt konkrete Lesebelege, keine pauschale Änderung sämtlicher älterer Sperr-/Abrufbefunde. Belegstufe D am 28.10.2026:

- **KG Golzheim:** https://kg-mir-hahle-poohl.de/veranstaltungen/ nennt die Kostümsitzung07.11.2026; der von dort verlinkte Originaldetailweg https://kg-mir-hahle-poohl.de/veranstaltung/grosse-kostuemsitzung/ zeigt ausdrücklich09.11.2024. Startseite, Übersicht und Detail gelesen. Vor Terminübernahme Jahres-/Detailzuordnung bestätigen; relative Restkarten-Beitragszeiten sind kein heutiger Kapazitätsbeleg.
- **Stadt- und Kreisarchiv:** https://www.kreis-dueren.de/presse/oeffnungszeiten_2026-10-01.php und https://www.dueren.de/de/verwaltung-politik/stadtverwaltung/presse/pressemitteilung/ffnungszeiten-des-stadt-und-kreisarchivs-dueren-im-oktober-und-november (beide01.10.) sowie https://www.kreis-dueren.de/kreishaus/amt/amt10/stadt-kreisarchiv.php gelesen. Besuchstage, Voranmeldung und Aktenvorbestellung auseinanderhalten; ältere Mitteilung mit fortgeltendem Termin, keine Tagesneuigkeit.
- **SC III:** eigenes Teamprofil gab einen Septemberstand aus. Gegenweg über die Gegner-Mannschaft und https://www.fussball.de/spieltagsuebersicht/kreisliga-c-staffel-1-kreis-dueren-kreisliga-c-herren-saison2627-mittelrhein/-/staffel/031GUV1TCG000003VS5489BUVSJ4CRAP-G sowie Datenvorbericht https://www.fussball.de/mgc.newsdetail/-/article-uuid/032HCPSAV4000000VS5489BVVT0GJGMV (28.10.,06:34) erfolgreich gelesen. Tabellenzeile/Ansetzung belegt, Gereonsweiler-Endstand weiterhin nicht eindeutig gelesen. Zahlen aus Suchausschnitt oder Septembercache nicht übernehmen.
- **TTC-Alternative:** über https://ttc-merzenich.de/mannschaften/herren-1/ gelangt man zum lesbaren Verbandsplan https://www.mytischtennis.de/click-tt/WTTV/26--27/ligen/2._Bezirksklasse_6/gruppe/522583/spielplan/gesamt und zur Tabelle https://www.mytischtennis.de/click-tt/WTTV/26--27/ligen/2._Bezirksklasse_6/gruppe/522583/tabelle/vr. Am28.10. gelesen; Direktabruf zunächst fehlerhaft, Klickweg erfolgreich.
- **Pfarrbriefarchiv:** https://www.pfarrgemeinden-niederzier.de/pfarrbrief-aller-niederzierer-pfarrgemeinden/pfarrbriefe-als-pdf-dateien/2026-2/ gelesen. Oktober-PDF als36-seitiges Original erkannt, gezielte Inhaltsabrufe anschließend Timeout. Kein frischer Vollbeleg der einzelnen Novembertermine.
- **Budgetportal:** zwei Antworten derselben kanonischen URL unterschieden sich bei der Frist; Wiederholungsabruf wieder02.11.2026. Vor Nutzung erneut kontrollieren, keine bestätigte Fristverkürzung aus Zwischenantwort ableiten. Detailprotokoll im [Morgendossier28.10.](dossiers/2026-10-28-morgens.md).
