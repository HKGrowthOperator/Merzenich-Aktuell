# Merzenich Aktuell – Morgenlauf (Claude), 22. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.10). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung ab 09:14 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 21.11. nachmittags](2026-11-21-claude-nachmittags.md), seither neu [ChatGPT 22.11. morgens](2026-11-22-morgens.md) (Commit `3f107317`) und die Feuerwehr-Automatik vom 21.11. 21:03 Uhr (Commit `d808f595`). main vor dem Lauf: `3f107317`. Eil-Prüfungen 16:42, 18:41, 20:41 (21.11.) und 06:41 Uhr; um 06:41 Uhr war Einsatz 149/26 bereits von der Automatik angelegt.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 0 | **Nullfund:** keine belegbare neue Nachricht mit Ortsbezug (Abschnitt 3) |
| Korrigierte Bestandsmeldungen | 1 | Feuerwehr-Automatik 149/26 (ChatGPT MA-KORR-FW149-BILDMOTIV-2211) |
| Werkzeugkorrekturen | 1 | `deploy/automatik-feuerwehr.mjs`: Bildklasse für Brandmeldealarm, Quellensatz im Nominativ; Test ergänzt |
| Neue / geänderte Termine | 0 / 0 | |
| Termine an der Quelle bestätigt | 20 | alle Termine 22.11. bis 06.12. (Stand 2026-11-22) |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Bestandsfoto Wasserturm für 149/26 (Abschnitt 8) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs --datum 2026-11-22 --lauf morgens` 0 Fehler; `qa/automatik-feuerwehr-test.mjs`, `automatik-termine-test.mjs`, `automatik-umkreis-test.mjs`: alle Prüfungen bestanden | |

Mindesttakt 22.11. (Sonntag): bis hierher keine Meldung; der Feuerwehr-Einsatz 149/26 ist ein Automatik-Artikel vom Vorabend.

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-KORR-FW149-BILDMOTIV-2211 (ChatGPT morgens) / MA-FEUERWEHR-149-26 | Automatik-Artikel korrigiert. Titel alt „Feuerwehr Merzenich: Brandmeldealarm in Merzenich (Einsatz 149/26)“ → neu „Feuerwehr Merzenich: Waffeln lösen Brandmeldealarm am Wasserturm aus (Einsatz 149/26)“. Dek neu mit Wasserturm und „es brannte nicht“. Neuer Absatz „Aktualisiert am 22. November: …“ in eigenen Worten: kein Feuer, Waffelbacken bei einer Veranstaltung löste die Anlage aus, Anlage zurückgestellt, Gebäude an den Veranstalter zurückgegeben. Bild: Klasse `vereine.wasserturm` (Wasserturm Merzenich) statt `blaulicht.feuer` (Löschfoto). Relevanz 2 → 3. Prüfprotokoll ergänzt. Grammatik „Grundlage … ist dem Beitrag“ → „ist der Beitrag“ | `inhalte/meldungen/2026-11-21-feuerwehr-automatik.json` → `/blaulicht/einsatz-149-brandmeldealarm-merzenich/` | Feuerwehrbericht in Heimat-Info (21.11. 21:03 Uhr) um 06:41 und 09:15 Uhr vollständig gelesen. Keine Zuordnung zu einer bestimmten Veranstaltung (der Bericht nennt sie nicht), keine Uhrzeit, keine Dauer ergänzt. Commit `fc6ab28a` |
| Werkzeug Feuerwehr-Automatik | Ursache behoben: Die Zuordnung Stichwort → Bildklasse prüfte „brandmeldeanlage“, der Bericht sagte aber „Brandmeldealarm“ (Stichwort „MDL“); das fiel auf die Regel „brand“ und bekam ein Löschfoto. Regel jetzt `brandmelde(anlage|alarm)|automatischer brandalarm|^mdl$` → `blaulicht.alarm.brandmeldeanlage` (Relevanz 3). Außerdem steht der Quellensatz im Nominativ („Grundlage … ist der Beitrag“); im Bestand bei 148/26 nachgezogen | `deploy/automatik-feuerwehr.mjs`, `qa/automatik-feuerwehr-test.mjs`, `inhalte/meldungen/2026-11-19-feuerwehr-automatik.json` | neuer Testfall (Brandmeldealarm/MDL, Brandmeldeanlage, MDL allein → Anlage; Gebäudebrand → Brand); alle drei Automatik-Tests bestanden. Commit `c440abf2` |
| Kalender | 20 Termine bestätigt | 3 Dateien unter `inhalte/termine/` | Abschnitt 6; Commit `ad701833` |

## 2. Sofort sichtbar

Korrektur ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| DWD-Warnungen vor Glätte und Frost für den Kreis Düren (ChatGPT MA-DAUERREGEN-20112026, T2) | Stufe 2, nach dem Stand von 9.09 Uhr gültig bis 22.11. 10 Uhr; keine Unwetterlage und kurz vor dem Ablauf. Keine gemeldeten Glätteunfälle in Merzenich (Polizei, Feuerwehr 09:18 Uhr). Der abgeschlossene Unwetterartikel bleibt unverändert |
| Polizei 22.11. (drei Meldungen 07:21 bis 08:47 Uhr) | Unfälle und Verfolgungsfahrt in Düren; Volltexte ohne Merzenicher Ort |
| Kreis Düren | Presse-Index 09:18 Uhr: nur Familienkalender vom 20.11., kein Ortsbezug |
| Ergebnisse HFA 19.11. | Sitzungsseite um 09:18 Uhr weiter ohne Niederschrift oder Beschlusstexte; Vorlagenliste unverändert (höchste Nummer 151/2026); Ratssitzung 3.12. ohne Tagesordnung |
| Malwettbewerb-Bildbeschreibung (MA-KORR-MALWETTBEWERB-BILDBESCHREIBUNG-1411) | Werkzeugpunkt (Alt-Text bestehender Poolanhänge im Abgleich), Entscheidung Luis Kummer offen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 22.11. morgens, zuerst KORREKTUREN | eine Korrektur umgesetzt; Weihnachtsfenster-Ergänzung und Wochenvorschau von ChatGPT bestätigt |
| Feuerwehr | Heimat-Info-Profil (bis 149/26) und Website (bis 147/26) 06:41 und 09:18 Uhr; Bericht 149/26 im Volltext | Korrektur |
| Polizei Düren | Dienststellenliste nr/8 09:18 Uhr, drei neue Meldungen im Volltext | kein Ortsbezug |
| Deutscher Wetterdienst | `warnings.json` 09:18 Uhr (Datenstand 09:09 Uhr) | Glätte und Frost bis 10 Uhr, Stufe 2 |
| Kreis Düren | Presse-Index 09:18 Uhr HTTP 200 | nichts mit Ortsbezug |
| Ratsinformationssystem | HFA-Seite, Ratssitzung 3.12., Vorlagenliste 09:18 Uhr; `termine.ics` 09:19 Uhr | nichts Neues |
| Heimat-Info | Gemeindeseite 09:18 Uhr; Veranstaltungskalender und zwölf Detailseiten 09:19 Uhr | Feuerwehrbeitrag 149, sonst nichts Neues |
| Gemeinde Merzenich | Amtsblätter 11 und 12, Kulturkalender 16 unter `/bindata/` 09:19 Uhr | bytegleich |

## 5. Sport

Sonntag. Heute keine Spiele der ersten Mannschaften aus Merzenich laut Wochenvorschau-Recherche (SC und FC Golzheim erst wieder am 29.11.). Ergebnis Girbelsrath vom 20.11. im Montagslauf.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 09:19 Uhr gelesen: zehn Einträge. Die vergangene Modelleisenbahn-Einladung ist entfallen, die Senioren-Adventsfeier am 9.12. (`65540fac`) ist wieder auf der ersten Seite und textgleich mit dem 20.11. Die übrigen neun Detailseiten sind im Veranstaltungsteil textgleich mit dem 21.11. Zusätzlich gelesen: Weihnachtsfeier der Schützen Morschenich am 6.12. (`1e0ecb73`, 15.30 Uhr am BWZ), Terminseite der Bruderschaft (Weihnachtsschießen ab 15 Uhr), Kulturkalender 16 (ISM-Weihnachtsfeier 6.12. ab 14 Uhr im Bürgerhaus, Anmeldung erforderlich) und Amtsblatt Nr. 12, S. 11 (Taizé-Gottesdienst 6.12. um 17 Uhr in Morschenich). `termine.ics` ist bis auf den technischen Zeitstempel gleich. Amtsblätter 11 und 12 und Kulturkalender 16 sind bytegleich; Schützen Morschenich, Ausstellungs- und Adventsfenster-Beitrag textgleich.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| neu im 14-Tage-Fenster: ISM-Weihnachtsfeier, Weihnachtsfeier Morschenich, Taizé-Gottesdienst (alle 6.12.) | ism-weihnachtsfeier-buergerhaus-merzenich-2026-12-06, weihnachtsfeier-morschenich-2026-12-06, taize-gottesdienst-morschenich-2026-12-06 | an der Quelle bestätigt | Kulturkalender 16, Heimat-Info und Schützen-Terminseite, Amtsblatt Nr. 12 | 2026-11-22 |
| übrige 17 Termine vom 22.11. bis 03.12. | je Slug | bestätigt | jeweilige Quelle | 2026-11-22 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `fc6ab28a` (Korrektur 149/26), `c440abf2` (Werkzeug, Test, Grammatik 148/26), `ad701833` (Kalender), Dokumentation in diesem Commit. Abgleich 09:16 Uhr `angestossen: true`.

Live-Nachweis 09:21 Uhr: `/blaulicht/einsatz-149-brandmeldealarm-merzenich/` HTTP 200 mit Titel „Feuerwehr Merzenich: Waffeln lösen Brandmeldealarm am Wasserturm aus (Einsatz 149/26)“, Nachtrag „Aktualisiert am 22. November“ und „ist der Beitrag“; og:image `aktuell-07-d923824479` (Wasserturm), Bildunterschrift „Symbolbild · Ehemalige Merzenicher Windmühle. Kein Foto vom Ereignis.“

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| einsatz-149-brandmeldealarm-merzenich | vereine.wasserturm | windmuehle-merzenich | Merzenich Windmühle Wasserturm HDR.jpg | CC BY-SA 3.0 | selbst angesehen 09:17 Uhr: der Wasserturm an der Mühlenstraße, also der Einsatzort, im Winter, ohne Personen; kein Feuer, kein Löscheinsatz. Ersetzt das Löschfoto, das einen Brand zeigte, den es nicht gab. Der Alt-Text „Ehemalige Merzenicher Windmühle“ beschreibt dasselbe Gebäude |

## 9. Offene Punkte für den nächsten Lauf

- Mindesttakt 22.11.: Meldung im Nachmittagslauf, wenn eine belegbare Nachricht vorliegt.
- Montag: Ergebnis Girbelsrath und Wochenende; HFA-Ergebnisse und Ratstagesordnung weiter beobachten.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung. Erledigt in diesem Lauf: Zuordnung „Brandmeldealarm“ in der Feuerwehr-Automatik.
