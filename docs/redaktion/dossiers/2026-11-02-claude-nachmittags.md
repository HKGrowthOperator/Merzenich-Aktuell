# Merzenich Aktuell – Nachmittagslauf (Claude), 2. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.3 vom 02.11.2026, Mindesttakt und Kurzmeldung). Erster Routinelauf in der festen Redaktionssitzung: Routine „14:43“ hier eingetroffen um 15:46 Uhr, Bearbeitung 15:46 bis 16:10 Uhr (Europe/Berlin). Vergleichsbasis: [Claude-Morgenlauf 02.11. (mit Nachholung)](2026-11-02-claude-morgens.md), seither neu [ChatGPT 02.11. mittags](2026-11-02-mittags.md). main vor dem Lauf: `43c2fa3`.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 0 | Die beiden Meldungen des Tages (Einbruch Bahnstraße, Wochenvorschau 2. bis 8.11.) stehen bereits im Live-Archiv; nicht im Repository, keine Dublette geschrieben |
| Aktualisierte oder korrigierte Bestandsmeldungen | 1 | Unfall L 264 Girbelsrath: Fahrer gestorben (Kurzmeldung, jetzt 161 Wörter, 3 Absätze) |
| Korrigierte Termine | 1 | Amtsblatt-Abgabeschluss 3.11.: ganztägig statt 12 Uhr (Korrekturauftrag ChatGPT) |
| Neue Termine (live / Entwurf zur Freigabe) | 2 / 0 | Abgabeschlüsse Amtsblatt 1.12. und 29.12. |
| Umkreis-Einträge | 0 | |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | – | kein neues Meldungspaket |
| Sportstand | unverändert | Tabelle am Morgen aktualisiert |
| Bilder gefunden / geprüft / integriert | 1 / 1 / 0 | Unfallfoto des Bestands gesichtet; Tischtennis-Foto: Commons-Datei HTTP 429 |
| Kette / QA / redaktion-lauf | zweimal, `--check` unverändert, `pruefung` 0 Fehler (27 Hinweise), `redaktion-lauf` 0 Fehler (42 Hinweise) | |
| Mindesttakt 02.11. | 2 Meldungen im Live-Archiv | `qa/redaktion-lauf.mjs` meldet „seit 126 Stunden keine Meldung im Repository“, weil die beiden Tagesmeldungen nur in WordPress angelegt sind |
| Dauer des Laufs | etwa 24 Minuten | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| GR-L264-27102026 | Update: neuer Titel „Nach Alleinunfall auf der L 264 bei Girbelsrath: Autofahrer im Krankenhaus gestorben“ (alt: „Alleinunfall auf der L 264 bei Girbelsrath: Autofahrer schwer verletzt, Straße fünf Stunden gesperrt“), neue Dek, Nachtrag vom 2. November, Fakt, zweite Polizeiquelle. Alter und Herkunft weiter weggelassen | `inhalte/meldungen/2026-10-28-recherche-claude-morgens.json` → `/blaulicht/alleinunfall-l264-girbelsrath-autofahrer-schwer-verletzt-2026-10-27/` | [Polizei Düren 02.11., 15:08 Uhr](https://www.presseportal.de/blaulicht/pm/8/6382725) und [Erstmeldung 27.10.](https://www.presseportal.de/blaulicht/pm/8/6379066), beide 15:48 Uhr im Original gelesen |
| Korrektur Kalender | `amtsblatt-12-2026-abgabeschluss-2026-11-03`: Start 12 Uhr → ganztägig; Ort „Rathaus Merzenich“ → „Amtsblatt-Redaktion (per E-Mail)“; Text „Korrigiert am 2. November 2026 …“ | `inhalte/termine/2026-10-25-recherche-claude.json` → `/termine/amtsblatt-12-2026-abgabeschluss-2026-11-03/` | Amtsblatt 11/2026 S. 1, Kasten „Nächstes Erscheinungsdatum“: Abgabe „bis Dienstag, 03. November 2026 per Email an amtsblatt@gemeinde-merzenich.de“, keine Uhrzeit |
| Kalender | 2 neue Termine: Abgabeschluss 1.12. (Ausgabe 11.12.) und 29.12. (Ausgabe 8.1.2027), ganztägig | `inhalte/termine/2026-11-02-recherche-claude-nachmittags.json` | Amtsblatt 11/2026 S. 1, „Weitere Abgabetermine“; Wochentage nachgerechnet |

Quellenausnahmen: Unfallartikel weiter nur mit Polizeimitteilungen (offizielle Quelle, SCHREIBREGELN.md Abschnitt 0 und 3a); Begründung in `quellenausnahme` aktualisiert.

## 2. Sofort sichtbar

- Update am veröffentlichten Unfallartikel (Titel, Dek, dritter Absatz): Quelle Polizei Düren, 02.11.2026, 15:08 Uhr.
- Korrektur am veröffentlichten Termin Amtsblatt-Abgabeschluss (Uhrzeit, Ort, Text).
- Zwei neue Termine (Abgabeschlüsse).
- Spielstand, Umkreis: nichts.

## 3. Bewusst nicht umgesetzt

- MA-EINBRUCH-BAHNSTR-31102026 (ChatGPT 02.11. mittags, T2): nach CLAUDE-LAUF 3.3 wäre die Polizeimitteilung allein eine Kurzmeldung; das Live-Archiv führt aber bereits „Einbruch in Einfamilienhaus an der Bahnstraße in Merzenich: Polizei sucht Zeugen“ (Blaulicht, 02.11.), nicht im Repository angelegt. Keine Dublette.
- Wochenvorschau: Live-Archiv führt „Martinszug, Kripo-Vortrag, Büchermarkt: Was vom 2. bis 8. November in Merzenich ansteht“ (Leben, 02.11.). Keine Dublette.
- Neue Polizeimeldungen ohne Merzenich-Bezug: Einbruch in Geschäft in Jülich (12:47), Brand in Einfamilienhaus in Nideggen (12:01), beschädigte Autos in Jülich (12:02); Umkreis nur über die Automatik.
- Vertiefung kurzer eigener Artikel: in diesem Lauf nicht begonnen (Todesfall-Update und Korrektur hatten Vorrang).

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| Rohdaten `imports/quellen/` | Commit `0b223e7` (Abruf vom Nachmittag) | nicht im Detail verglichen; Live-Abrufe maßgeblich |
| Polizei Düren RSS | Dienststelle 8, 15:47 Uhr, HTTP 200 | neu seit dem Morgen: 11:54 Zeugensuche nach drei Einbrüchen (darunter Bahnstraße Merzenich), 12:01 Nideggen, 12:02 Jülich, 12:47 Jülich, **15:08 L 264 Girbelsrath: Fahrer gestorben** |
| Kreis Düren Presse | Index, 15:47 Uhr, HTTP 200 | jüngste Mitteilung weiter 28.10., nichts Neues |
| Heimat-Info | Gemeindeliste, 15:47 Uhr, HTTP 200 | nichts Neues (erste Einträge Kindergottesdienst, Allerheiligen Morschenich) |
| Ratsinformationssystem | `termine.ics`, `/vorlagen`, 15:47 Uhr, HTTP 200 | Sitzungen 4.11. bis 3.12. unverändert, jüngste Vorlage 135/2026 |
| Feuerwehr | Einsatzliste, 15:47 Uhr, HTTP 200 | jüngster Einsatz weiter 140/26 vom 16.10. |
| Amtsblatt | Index HTTP 428 (ChatGPT erreichte ihn mittags mit HTTP 200); PDF 11/2026 aus dem Morgenabruf | keine neue Ausgabe bekannt |
| Live-Archiv | `merzenich-aktuell.de/archiv/`, 15:47 Uhr | zwei Meldungen vom 02.11. (Einbruch, Wochenvorschau) |
| Commons | API 15:56 Uhr HTTP 200, Bilddatei `upload.wikimedia.org` HTTP 429 | Tischtennis-Foto nicht geladen |

Sperren und Umwege: gemeinde-merzenich.de HTTP 428 auf Unterseiten aus dieser Sitzung; Amtsblatt-PDF direkt erreichbar.

## 5. Sport

Unverändert gegenüber dem Morgenlauf (TTC-Nachtrag, Fußballtabelle 11:43 Uhr). Nächste Spiele: TTC 6.11. in Dürboslar, SC Merzenich 8.11. gegen FC Düren 77. Fußball-Regelprüfung wieder am Dienstag.

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender`: alle Termine der nächsten 14 Tage und bis 01.01.2027 wurden heute im Morgenlauf an der Quelle geprüft (Prüfstand 2026-11-02, siehe [Morgendossier](2026-11-02-claude-morgens.md) Abschnitt 6). Nachmittags erneut gelesen: RIS `termine.ics` und Heimat-Info ohne Änderung, keine Absage.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Abgabeschluss Amtsblatt 12, 3.11. | amtsblatt-12-2026-abgabeschluss-2026-11-03 | geändert (ganztägig, per E-Mail; Korrekturauftrag ChatGPT 02.11. mittags) | [Amtsblatt 11/2026 S. 1](https://www.gemeinde-merzenich.de/bindata/amtsblatt-2026/Merzenich_11_2026-web.pdf#page=3) | 2026-11-02 |
| Abgabeschluss Amtsblatt vom 11.12., 1.12. | amtsblatt-abgabeschluss-2026-12-01 | neu | Amtsblatt 11/2026 S. 1, „Weitere Abgabetermine“ | 2026-11-02 |
| Abgabeschluss Amtsblatt vom 8.1.2027, 29.12. | amtsblatt-abgabeschluss-2026-12-29 | neu | Amtsblatt 11/2026 S. 1, „Weitere Abgabetermine“ | 2026-11-02 |

Offene Hinweise „nachpruefen“: 1 (VHS-Vortrag 10.11., Website HTTP 404).

## 7. QA, Commit, Abgleich

Commits `8fcc8c9` (Inhalte) und der Dokumentationscommit dieses Dossiers. Rebase-Konflikt nur in `docs/SHA256SUMS.txt` (CI-Commit `035236d`): Stand von main übernommen, Kette zweimal, `--check` unverändert, `qa/pruefung.mjs` 0 Fehler. Abgleich: Anstoß 15:53 Uhr `angestossen: false, naechster` gegen 16:05 Uhr (Workflow plant den Lauf selbst), wp-cron HTTP 200. Nachweis in der Import-XML auf GitHub raw: neuer Unfalltitel 1, Korrekturtext Abgabeschluss 1, neue Slugs 2 Treffer. Live-Nachweis: Abschnitt 7a. Backend nicht geprüft (kein Zugang).

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| alleinunfall-l264-girbelsrath-autofahrer-schwer-verletzt-2026-10-27 (Update) | verkehr.unfall.rettung | unfallstelle | Verkehrsunfall L261 04.JPG | CC BY 3.0 | selbst angesehen: Pkw nach Aufprall an einem Straßenbaum am Rand einer Landstraße, wie im Hergang (Baum, Fahrbahnrand); keine Personen erkennbar, Kennzeichen vom Baum verdeckt (nur zwei Zeichen sichtbar). Symbolbild aus dem Kreis Pinneberg |

Neu importierte Fotos: keine. Tischtennis: Commons-API lieferte die Metadaten (Datei aus Málaga, Kategorie „Table tennis tables in Andalusia“, CC BY-SA 4.0), die Bilddatei selbst HTTP 429. Hinweis für den nächsten Lauf: Die Ortskategorie Málaga dürfte den Auslandsfilter des Importskripts auslösen; besser ein Foto ohne fremden Ortsbezug suchen.

## 8a. Prüfung und Eigenformulierung

Kein neues Meldungspaket (`qa/eigenformulierung.mjs --datum 2026-11-02 --lauf nachmittags`: „Kein Paket … nichts zu pruefen“). Der Nachtrag im Unfallartikel ist eigener Text; übernommen sind nur Datum, Todestag und Uhrzeit der Mitteilung.

## 9. Offene Punkte für den nächsten Lauf

1. Mindesttakt 03.11.: zwei Meldungen; Kandidaten: Jugendgremium 4.11. (Termine-Vorschau), Kripo-Info Girbelsrath 5.11., Lesung 5.11.; Live-Archiv vorher auf Handbeiträge prüfen.
2. Tischtennis-Themenfoto (siehe Abschnitt 8).
3. Vertiefungen eigener kurzer Artikel (ELW, Amprion, Pfarrer Galbierz, Jugendzeltplatz).
4. Bürgersprechstunde Golzheim (30.10. oder 13.11.) und VHS-Vortrag 10.11.: Rückfrage durch Menschen.
5. Die Handbeiträge vom 02.11. (Einbruch, Wochenvorschau) sind nicht im Repository; Folgeupdates dazu nur in WordPress oder nach Übernahme ins Repository.

## 7a. Live-Nachweis

Wird nach dem Abgleich gegen 16:05 Uhr ergänzt.
