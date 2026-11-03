# Merzenich Aktuell – Morgenlauf (Claude), 3. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.5). Routine „09:13“ in der festen Redaktionssitzung, Start 09:14 Uhr, Bearbeitung 09:14 bis 09:30 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 02.11. nachmittags](2026-11-02-claude-nachmittags.md), seither neu [ChatGPT 03.11. morgens](2026-11-03-morgens.md). main vor dem Lauf: `3b34677`.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Rathaus-Vorschau Jugendgremium (203 Wörter, `relevanz` 2) |
| Aktualisierte oder korrigierte Bestandsmeldungen | 0 | Korrekturauftrag betrifft einen Automatik-Beitrag (Abschnitt 3) |
| Neue Termine (live / Entwurf zur Freigabe) | 0 / 0 | |
| Termine 03.–16.11. an der Quelle nachgeprüft | 21 von 23 | VHS-Seite HTTP 404; Bücher- und Spielemarkt an der Vereinsseite bestätigt, Amtsblatt-Quelle nicht erneut gelesen |
| Umkreis-Einträge | 0 | |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | Kurzmeldung mit offizieller Quelle (3 Quellen, alle RIS) / ja, 6 Aussagen | |
| Sportstand | unverändert | ChatGPT-Fußballprüfung 03.11. ohne neuen Stand |
| Bilder gefunden / geprüft / integriert | 1 / 1 / 0 | Rathaus Merzenich |
| Kette / QA / redaktion-lauf | zweimal, `--check` unverändert; `qa/pruefung.mjs` 1 Fehler (vorbestehend, Abschnitt 7); `redaktion-lauf` 0 Fehler | |
| Mindesttakt 03.11. | 1 Meldung bis 09:30 Uhr | Ziel 2, Nachmittagslauf |
| Dauer des Laufs | etwa 16 Minuten | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-JUGENDGREMIUM-STATUS | „Jugendgremium Merzenich tagt am 4. November: Wünsche vom Ortsfest und Nahverkehr auf der Tagesordnung“ (Kurzmeldung, `relevanz` 2). Die leere Dokumentation in der Vorlage ist im Text benannt; die Vorlage ist nicht als Beschluss dargestellt | `inhalte/meldungen/2026-11-03-recherche-claude-morgens.json` → `/rathaus/jugendgremium-merzenich-sitzung-4-november-2026-ortsfest-oepnv/` | RIS-Tagesordnung „Jugendgremium, 4. Sitzung“ (9 TOP) und Beschlussvorlage 135/2026 (PDF), `termine.ics`, alle 09:15 Uhr gelesen |
| Kalender | 21 Termine 03.–16.11. mit Prüfstand 2026-11-03 | `inhalte/termine/*.json` | Abschnitt 6 |

Quellenausnahmen: keine (Kurzmeldung mit offizieller Quelle nach SCHREIBREGELN.md Abschnitt 0). Die frühere T2-Sperre des Themas (fehlende Zweitquelle, Platzhalter in der Vorlage) trägt nach CLAUDE-LAUF 3.3 keine Sperre mehr: Die Meldung kündigt nur die öffentliche Sitzung an und benennt die Lücke.

Top-Meldung: keine neue Stufe 1. Die Folgemeldung zum Verkehrstod (Stufe 1, 02.11.) bleibt bis zum Ende ihrer Frische Aufmacher; ein Ausschuss-Vorbericht hat keinen Nachrichtenwert für Stufe 1.

## 2. Sofort sichtbar

- Neue Meldung Jugendgremium (direkt veröffentlicht).
- Termine: nur Prüfstand geändert, keine sichtbaren Inhalte.
- Spielstand, Umkreis: nichts.

## 3. Bewusst nicht umgesetzt

- **Korrekturauftrag ChatGPT 03.11.:** `einsatz-144-verkehrsunfall-eingeklemmte-person-girbelsrath` zeigt „Veröffentlicht 27.10.2026 · 12:00 Uhr“; der 27.10. ist das Einsatzdatum, die Feuerwehr veröffentlichte am 02.11.2026, 19:18 Uhr. Der Beitrag stammt aus der Feuerwehr-Automatik (`inhalte/meldungen/2026-11-03-feuerwehr-automatik.json`, `datum` 2026-10-27T12:00:00+01:00); `deploy/automatik-feuerwehr.mjs` setzt das Einsatzdatum mit 12:00 Uhr als Ersatzzeit. Ein Handeingriff in der Datei würde beim nächsten Lauf der Automatik überschrieben; Generatoren ändert der Redaktionslauf nicht (CLAUDE-LAUF Abschnitt 6). **Offen für Luis:** Generator so ändern, dass das Beitragsdatum der Feuerwehr Veröffentlichungsdatum wird und das Einsatzdatum im Text steht.
- **Regelwiderspruch:** `docs/SEO-KEYWORDS.md` (02.11., 22:29 Uhr) Titelregel 5 „Ein Vorgang, eine Adresse … nicht als zweiter Artikel“ widerspricht SCHREIBREGELN.md Abschnitt 0a (Folgemeldung bei neuer Entwicklung mit eigenem Nachrichtenwert, Entscheidung 02.11. nachmittags). In diesem Lauf kein Anwendungsfall; **Entscheidung nötig**, welche Regel gilt.
- Keine neue Polizei-, Kreis-, Feuerwehr- oder Gemeindemeldung mit Merzenich-Bezug seit dem 02.11., 15:08 Uhr.

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| Rohdaten `imports/quellen/` | nicht verglichen | Live-Abrufe maßgeblich |
| Polizei Düren RSS | Dienststelle 8, 09:15 Uhr, HTTP 200 | jüngste Meldung weiter 02.11., 15:08 (L 264), nichts Neues |
| Kreis Düren Presse | Index, 09:15 Uhr, HTTP 200 | jüngste Mitteilung 28.10., nichts Neues |
| Heimat-Info | Gemeindeliste und Veranstaltungskalender, 09:15 Uhr, HTTP 200 | Feuerwehr 144/26 (02.11., bereits live über die Automatik); Kalender unverändert 6 Einträge |
| Ratsinformationssystem | `termine.ics`, `/vorlagen`, Tagesordnung Jugendgremium, Vorlage 135/2026, 09:15 Uhr, HTTP 200 | Sitzungen unverändert; Grundlage der neuen Meldung |
| Feuerwehr | Einsatzliste HTTP 200 (bis 140/26), Heimat-Info-Kanal HTTP 200 (bis 144/26) | 144/26 vom 27.10., Beitrag vom 02.11. |
| Amtsblatt | Index HTTP 428 aus dieser Sitzung; PDF 11/2026 direkt HTTP 200 | keine neue Ausgabe |
| Live-Archiv | 09:15 Uhr | 03.11. bis dahin ohne Meldung |

Sperren und Umwege: gemeinde-merzenich.de HTTP 428 auf Unterseiten aus dieser Sitzung (ChatGPT erreichte den Index um 07:04 Uhr); vhs-rur-eifel.de HTTP 404.

## 5. Sport

Unverändert. ChatGPT-Regelprüfung 03.11. (Dienstag): SC Merzenich Vierter, 24 Punkte; nächstes Spiel 8.11. gegen FC Düren 77; SC-III-Endstand gegen Gereonsweiler weiter offen. TTC nächstes Spiel 6.11. in Dürboslar.

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender`. Alle Abrufe 03.11.2026, 09:15 bis 09:19 Uhr, jede Quelle mit den Kerndaten gegengelesen:

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Abgabeschluss Amtsblatt 12, 3.11. | amtsblatt-12-2026-abgabeschluss-2026-11-03 | bestätigt | Amtsblatt 11/2026 S. 1 (PDF) | 2026-11-03 |
| Jugendgremium 4.11., RPA 5.11., Bauausschuss 12.11. | jugendgremium-sitzung-2026-11-04, rechnungspruefungsausschuss-2026-11-05, bauausschuss-sitzung-2026-11-12 | bestätigt | [RIS termine.ics](https://ratsinfo.gemeinde-merzenich.de/termine.ics) | 2026-11-03 |
| Rosenkranz ab 4.11., Taizé 15.11. | rosenkranzgebet-pfarrheim-merzenich-ab-2026-11-04, taize-gottesdienst-morschenich-2026-11-15 | bestätigt | Pfarrbrief 08/26 (PDF) | 2026-11-03 |
| Aktionstag 5.11., KI-Roadshow 6.11., Eichel-Sammelaktion 6.11. | aktionstag-persoenliches-budget-kreishaus-2026-11-05, ki-roadshow-ihk-dueren-2026-11-06, eichel-sammelaktion-huertgenwald-2026-11-06 | bestätigt | Kreis Düren, Presseseiten | 2026-11-03 |
| Kripo-Info 5.11., Lesung 5.11., Inthronisation 8.11., Martinszug Merzenich 12.11., Martinsfeier Girbelsrath 13.11. | kripo-info-…-2026-11-05, lesung-jennifer-teege-…-2026-11-05, inthronisation-kbf-girbelsrath-2026-11-08, martinszug-merzenich-2026-11-12, martinsfeier-girbelsrath-2026-11-13 | bestätigt | Amtsblatt 11/2026 (PDF) | 2026-11-03 |
| Kostümsitzung KG Golzheim 7.11. | kg-golzheim-kostuemsitzung-2026-11-07 | bestätigt | KG-Seite | 2026-11-03 |
| Kindergottesdienst 8.11., Gottesdienst 15.11. | kindergottesdienst-ev-gemeindehaus-2026-11-08, ev-gottesdienst-gedenken-verstorbene-2026-11-15 | bestätigt | Gemeindekalender ekir; Heimat-Info | 2026-11-03 |
| Martinszug Golzheim 11.11. | martinszug-golzheim-2026-11-11 | bestätigt | Heimat-Info, Förderverein (17:30 / 18:00 Uhr) | 2026-11-03 |
| TTC-Jugend-Vereinsmeisterschaften 13.11. | ttc-merzenich-jugend-vereinsmeisterschaften-2026-11-13 | bestätigt | TTC-Vereinskalender | 2026-11-03 |
| Erste-Hilfe-Kurs 14.11. | erste-hilfe-kurs-golzheim-2026-11-14 | bestätigt | Heimat-Info-Beitrag 28.10. | 2026-11-03 |
| Volkstrauertag 15.11. | volkstrauertag-merzenich-2026 | bestätigt | KG Jonge vom Berg | 2026-11-03 |
| Bücher- und Spielemarkt 8.11. | buecher-spielemarkt-ig-golzheim-2026 | bestätigt (Vereinsseite) | IG Golzheim aktiv; Prüfstand der Amtsblatt-Quelle unverändert | 2026-10-30 |
| VHS-Vortrag Paris 10.11. | vhs-bildvortrag-paris-marienhof-2026-11-10 | nicht geprüft | HTTP 404 | 2026-10-05 |

**Heimat-Info-Veranstaltungskalender** (Schritt 2a): 6 Einträge, identisch mit dem Stand vom 02.11. nachmittags; Gottesdienst 22.11. (Niederzier) und Weihnachtsfeier Morschenich 6.12. weiter Entwurf (Automatik), warten auf Freigabe.

Offene Hinweise „nachpruefen“: 1 (VHS). Commit `7fb625f`.

## 7. QA, Commit, Abgleich

Commits `7bd157f` (Meldung), `7fb625f` (Kalender) und der Dokumentationscommit. `qa/pruefung.mjs` meldet 1 Fehler: „/umgebung/ führt ins Leere (chatgpt-site/index.html)“. Er besteht auf main auch ohne diesen Lauf (Prüfung mit gestashten Änderungen, 09:18 Uhr) und kommt aus dem SEO-Commit `4d96722` vom 02.11., 22:29 Uhr: `/umgebung/` gibt es nur in WordPress, die statische Kopie leitet per `_redirects` weiter, die Linkprüfung kennt die Weiterleitung nicht. Prüfung und Generator ändert der Lauf nicht; **offen für Luis**. `qa/redaktion-lauf.mjs` 0 Fehler.

Abgleich: Anstoß 09:20 Uhr `angestossen: true`, wp-cron HTTP 200; Slug 2 Treffer in der Import-XML. Live 09:20 Uhr HTTP 200: [Jugendgremium-Meldung](https://merzenich-aktuell.de/rathaus/jugendgremium-merzenich-sitzung-4-november-2026-ortsfest-oepnv/) auf der Startseite direkt hinter dem Aufmacher (Folgemeldung Verkehrstod) und in der News-Sitemap. Backend nicht geprüft.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| jugendgremium-merzenich-sitzung-4-november-2026-ortsfest-oepnv | rathaus.rat | rathaus-merzenich | Merzenich Rathaus.JPG | Public domain | selbst angesehen: Rathaus Merzenich von der Straße aus, keine Personen, keine Kennzeichen; steht für das Gremium der Gemeinde (Vorlage aus dem Büro des Bürgermeisters). Die Sitzung selbst ist im Bürgerhaus |

Neu importierte Fotos: keine.

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll (offiziell / doppelt / Selbstauskunft) | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| jugendgremium-merzenich-sitzung-4-november-2026-ortsfest-oepnv | 203 | 3 (RIS-Tagesordnung, Vorlage 135/2026, termine.ics) | 6 / 0 / 0 | 0 % (`qa/eigenformulierung.mjs` 0 Fehler) |

## 9. Offene Punkte für den nächsten Lauf

1. Mindesttakt 03.11.: zweite Meldung im Nachmittagslauf (Kandidaten: Ergebnis der Jugendgremium-Sitzung erst am 04.11.; Kripo-Info Girbelsrath 5.11. als Blaulicht-Prävention, Bestand Wochenvorschau beachten).
2. Nach der Sitzung am 04.11.: Update der Jugendgremium-Meldung mit der Priorisierung, sobald im RIS veröffentlicht.
3. Feuerwehr-Automatik: Veröffentlichungsdatum (Abschnitt 3), Entscheidung und Codeänderung durch Luis.
4. Regelwiderspruch Folgemeldung (SCHREIBREGELN 0a) gegen SEO-KEYWORDS Regel 5: Entscheidung Luis.
5. QA-Fehler `/umgebung/` (Abschnitt 7).
6. Bildpools Unfall, Rettung, Tischtennis erweitern, sobald Commons liefert.
