# Merzenich Aktuell – Nachmittagslauf (Claude), 19. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung ab 14:44 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 19.11. morgens](2026-11-19-claude-morgens.md), seither neu [ChatGPT 19.11. mittags](2026-11-19-mittags.md). main vor dem Lauf: `5487f8d5`. Eil-Prüfungen 10:41 und 12:42 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 0 | Kompost-Meldung fertig geschrieben, aber auf den Morgenlauf 20.11. verschoben: kein zulässiges Foto (Abschnitt 3 und 8); Nullfund für neue Meldungen in diesem Lauf |
| Aktualisierte Bestandsmeldungen | 1 | Haushalt 2027, Zeitplan (Ratssitzung 3.12. jetzt 18 Uhr; ChatGPT MA-KORR-HAUSHALTSZEITPLAN-RAT-1911) |
| Neue / geänderte Termine | 0 / 0 | |
| Termine an der Quelle bestätigt | 20 | alle Termine 19.11. (ab 18 Uhr) bis 03.12., Stand vom Morgen bestätigt |
| Bilder gefunden / geprüft / integriert | 3 / 3 / 0 | Abschnitt 8 |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler (Ortsbezug geprüft); `qa/eigenformulierung.mjs --datum 2026-11-19 --lauf nachmittags` 0 Fehler, 0 Hinweise | |

Mindesttakt 19.11.: 2 Meldungen (beide im Morgenlauf).

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-KORR-HAUSHALTSZEITPLAN-RAT-1911 (ChatGPT mittags) | Artikel „Haushalt 2027: Entwurf soll am 3. Dezember in den Merzenicher Gemeinderat kommen“: Alt „… für diesen Tag um 17 Uhr im Ratssaal des Rathauses eingetragen; eine Tagesordnung liegt dort noch nicht vor.“ Neu „… um 18 Uhr …; eine Tagesordnung lag dort am 19. November noch nicht vor.“ Dazu „Aktualisiert am 19. November: …“ mit Hinweis auf die Haushaltsprognose 2026 und angepasste Faktenliste | `inhalte/meldungen/2026-10-13-recherche-chatgpt.json` → `/rathaus/haushalt-2027-zeitplan-merzenich/` | Ratsbeginn am 18.11. an `termine.ics` und Sitzungsseite selbst belegt (Dossier 18.11. morgens), heute 14:48 Uhr `termine.ics` unverändert; Commit `49e5d7d8` |

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Aktualisierung ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| MA-KOMPOST-KGS-2026: Garten-AG der KGS Merzenich baut Keyhole-Komposter | Kurzmeldung (296 Wörter, Ortsbezug KGS Merzenich, QA 0 Fehler bis auf das Bild) nach dem Gemeindebeitrag in Heimat-Info vom 19.11. 09:56 Uhr und dem Eintrag in der Pressemitteilungsliste der Gemeinde fertig. **Verschoben**, weil kein zulässiges Foto zur Verfügung stand: Die drei passenden Commons-Fotos eines Keyhole-Beets (Paris, Nottingham, Jerusalem) schließt der Bildimport als Auslandsmotive bewusst aus, die Suche nach deutschen Motiven scheiterte von 14:50 bis 15:01 Uhr an der Drosselung durch Commons (HTTP 429). Ein Klassenzimmerfoto hätte nicht gezeigt, worum es geht. Entwurf gesichert unter `docs/redaktion/entwuerfe/2026-11-19-kompost-kgs-merzenich.json`; Veröffentlichung im Morgenlauf 20.11. mit deutschem Garten- oder Kompostfoto |
| Ergebnisse HFA 19.11. | Sitzung heute 18 Uhr; Folgemeldungen im Morgenlauf 20.11. nach Beschlusstexten |
| Kreis Düren, „Orange Day“-Bus (19.11.) | Bus der Rurtalbus GmbH gegen Gewalt an Frauen; im Text kein Bezug zu Merzenich (SCHREIBREGELN 0b) |
| Polizei 19.11. (vier Meldungen bis 12:42 Uhr) | Jülich (drei), Düren; Volltexte ohne Merzenicher Ort |
| Malwettbewerb-Bildbeschreibung | Werkzeugpunkt, Entscheidung Luis Kummer offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 19.11. mittags, zuerst KORREKTUREN | eine Aktualisierung umgesetzt; Morgenmeldungen gegen Originale bestätigt; keine neue Themenkarte |
| Heimat-Info | Gemeindebeitrag Kompost 14:46 Uhr; Veranstaltungskalender und zehn Detailseiten 14:48 bis 14:50 Uhr | Meldung; Kalender unverändert |
| Polizei Düren | Dienststellenliste nr/8, 12:42 und 14:48 Uhr; Meldung 12:42 Uhr im Volltext | kein Merzenich-Bezug |
| Feuerwehr | Website und Heimat-Info-Profil, 14:48 Uhr | weiter bis 147/26 |
| Kreis Düren | Presse-Index und „Orange Day“-Meldung, 14:48 Uhr | kein Ortsbezug |
| Ratsinformationssystem | Vorlagenliste und `termine.ics`, 14:48 Uhr | keine neue Vorlage seit dem Morgen |
| Wikimedia Commons | Suchen „keyhole garden“, „Schulgarten Kompost“, „school garden compost“, „Schlüssellochbeet“ 14:50 bis 15:01 Uhr | mehrfach HTTP 429; Abschnitt 8 |

## 5. Sport

Donnerstag, kein Pflichttag. Freitagsprüfung 20.11.: Girbelsrath bei Viktoria Arnoldsweiler II, 19.30 Uhr; Wochenendspiele.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 14:48 Uhr gelesen: dieselben zehn IDs wie am Morgen, alle zehn Detailseiten im Veranstaltungsteil textgleich. `termine.ics` unverändert (HFA heute 18 Uhr, Bürgewald-Ausschuss 26.11., Rat 3.12. 18 Uhr). Amtsblätter 11 und 12, Kulturkalender 16 und Schützen Morschenich bytegleich mit dem Morgen.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| alle 20 Termine vom 19.11. (ab 18 Uhr) bis 03.12. | je Slug | bestätigt | jeweilige Quelle | 2026-11-19 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `49e5d7d8` (Aktualisierung Haushalt-2027-Zeitplan), Dokumentation und Entwurf in diesem Commit. Abgleich 14:46 Uhr `angestossen: true`.

Live-Nachweis 14:49 Uhr: `/rathaus/haushalt-2027-zeitplan-merzenich/` mit „um 18 Uhr im Ratssaal des Rathauses eingetragen“ und „Aktualisiert am 19. November“.

## 8. Bildprüfung

Keine neue Meldung veröffentlicht. Für die verschobene Kompost-Meldung angesehen (14:51 Uhr): „St Ann's Community Orchard African keyhole bed 1304.JPG“ (CC BY-SA 3.0, Nottingham; Schlüssellochbeet mit geflochtenem Kompostkorb, passt inhaltlich am besten), „KeyHole Bercy.jpg“ (CC BY-SA 4.0, Paris; Holz-Keyhole mit französischem Schild) und „Composter in a school garden in the botanical garden, jerusalem.jpg“ (CC BY-SA 4.0; Kunststoff-Komposter). Alle drei weist `deploy/import-editorial-photos.mjs` über den Auslandsfilter ab (Import 14:51 Uhr: „themen: 25 behalten, 0 neu“); der Filter wurde nicht umgangen, der Pool-Eintrag wieder entfernt.

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Garten-AG KGS Merzenich (Entwurf, nicht veröffentlicht) | 296 | 2 | 3 Einträge: Gemeindeprofil Heimat-Info (Selbstauskunft), Pressemitteilungsliste der Gemeinde | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Kompost-Meldung aus `docs/redaktion/entwuerfe/2026-11-19-kompost-kgs-merzenich.json` mit deutschem Foto veröffentlichen (Datum und Paketname auf den Lauf anpassen).
- HFA 19.11.: Ergebnisse als Folgemeldungen (Abgaben, Übernachtungssteuer, Neuland, Kita, Brandschutzbedarfsplan, Trauungen, Shared Service Center, Mitteilungen Haushalt und Umsatzsteuer).
- Freitagsprüfung Sport 20.11.
- Bauausschuss 12.11.: Niederschrift; Gemeinderat 3.12.: Tagesordnung.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
