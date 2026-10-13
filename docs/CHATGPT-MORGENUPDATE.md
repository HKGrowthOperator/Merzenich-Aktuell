# ChatGPT-Morgenrecherche: so kommt sie auf die Seite

Stand 13.10.2026

## Wie es heute läuft

ChatGPT erstellt jeden Morgen einen Recherchebericht als Markdown-Datei (zum Beispiel `Merzenich-Redaktions-Bildrecherche-2026-10-13.md`). Der Bericht enthält Themenkarten mit Textentwurf, Quelle, Datum und einer Freigabestufe („geeignet“, „bedingt“, „gesperrt“). ChatGPT hat **keinen Zugriff auf das Repository** und schreibt das in jedem Bericht selbst („kein Commit, kein Push“). Der letzte Inhalts-Commit aus ChatGPT ist vom 06.10.2026.

Auf die Seite kommt ein Thema erst, wenn es als Meldung in `inhalte/meldungen/` liegt. Von dort holt der stündliche Abgleich es als **Entwurf in die Freigaben**. Veröffentlicht wird es dort von der Redaktion (Anleitung `UEBERGABE-KBS.md`, A3).

## Bericht an Claude geben (so am 13.10.2026)

1. Die Markdown-Datei(en) in eine Claude-Code-Sitzung für dieses Repository hochladen.
2. Claude führt die Karten aller Berichte zu Themen zusammen und lässt weg, was gesperrt, vorbei, schon auf der Seite oder ohne Bezug zur Gemeinde Merzenich ist.
3. Jede Aussage wird an der Originalquelle geprüft. Was sich nicht bestätigen lässt, fällt weg.
4. Claude schreibt die Meldungen nach dem Redaktionsstandard, prüft sie ein zweites Mal unabhängig, committet und pusht.
5. Nach dem nächsten Abgleich (spätestens zur vollen Stunde, Minute 7) stehen sie in den Freigaben.

Die Berichte selbst kommen **nicht** ins Repository: Es ist öffentlich lesbar, und die Berichte nennen gelegentlich Namen von Privatpersonen.

## Was ChatGPT besser machen kann

Damit weniger wegfällt, diesen Text in die geplante Aufgabe in ChatGPT übernehmen:

> Recherchiere für Merzenich Aktuell (Gemeinde Merzenich mit Merzenich, Golzheim, Girbelsrath, Morschenich, Bürgewald) die Neuigkeiten seit dem letzten Bericht.
> - Nur Themen aus der Gemeinde oder mit einer konkret benannten Wirkung auf Merzenich. Themen aus Düren, Elsdorf, Niederzier, Kerpen, Nörvenich nur, wenn du diese Wirkung nennen kannst.
> - Je Thema genau eine Karte. Gib die direkte Adresse der Seite an, auf der die Fakten stehen (keine Startseiten, keine Suchtreffer), mit Veröffentlichungsdatum der Quelle.
> - Keine Feuerwehr-Einsätze: die legt die Automatik selbst an.
> - Keine Namen von Privatpersonen, keine Hausnummern bei Polizei- und Feuerwehrthemen.
> - Termine nur, wenn sie noch bevorstehen; vergangene nur als Ergebnisbericht.
> - Prüfe vorher, ob das Thema schon auf https://merzenich-aktuell.de steht. Wenn ja, nur eine Karte mit dem, was neu ist.
> - Markiere jede Karte mit „geeignet“, „bedingt“ (was fehlt) oder „gesperrt“ (warum).

## Was automatisch passiert

| Was | Wann | Ergebnis |
|---|---|---|
| Quellen abrufen (`quellen-abruf.yml`) | täglich 05:10 UTC, verspätet sich bei GitHub oft bis mittags | Rohdaten in `imports/quellen/` |
| Feuerwehr-Automatik (`meldungen-automatik.yml`) | direkt danach | neue Einsätze als Entwurf |
| WordPress-Abgleich (`wordpress-abgleich.yml`) | stündlich und nach jedem Lauf oben | Entwürfe in den Freigaben |
| Startseite | ständig | neue freigegebene Meldungen rücken nach oben; feste Plätze enden nach 7 Tagen |

Polizei-Meldungen kommen derzeit nicht automatisch: Das Presseportal sperrt den Abruf seit dem 10.10.2026 („Security Check“).
