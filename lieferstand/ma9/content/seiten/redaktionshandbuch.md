---
title: Redaktionshandbuch
slug: redaktionshandbuch
eyebrow: Intern
description: Wie eine Meldung auf Merzenich Aktuell entsteht, welche Regeln für Titel, Teaser, Bilder und Quellen gelten und wie das Redaktionssystem bedient wird.
noindex: true
---
## In fünf Minuten online

1. **Anmelden** unter [/admin/](/admin/) mit der per E-Mail zugeschickten Einladung.
2. **Artikel → Neu.** Überschrift, Teaser, Ressort, Ortsteil, Datum, Text. Bild hochladen, Bildtyp und Credit setzen. Mindestens eine Quelle mit Link.
3. **Speichern** legt einen Entwurf an. Die Vorschau rechts zeigt, wie es aussieht.
4. **Status auf „Bereit"** setzen, dann **Veröffentlichen**. Die Seite baut sich in etwa einer Minute neu; Startseite, Ressort, Ortsteil, Feeds und Sitemaps aktualisieren sich automatisch.

## Regeln für Titel und Teaser

- Titel: 10 bis 110 Zeichen, eine Aussage, kein Anreißer. Ort oder Verein möglichst nennen („Golzheim", „SC Merzenich"). Doppelpunkt-Bauweise funktioniert gut: „Flächenbrand an der L257: mehr als 200 Kräfte im Einsatz".
- Teaser: 1 bis 2 Sätze, 60 bis 200 Zeichen, beantwortet wer/was/wann/wo. Erscheint bei Google und im WhatsApp-Kanal.
- Dachzeile: ein bis drei Wörter (Kreisliga A, Ortsfest 2026, Feuerwehr).
- „Das Wichtigste in Kürze": drei bis fünf Stichpunkte, bei jeder Meldung über 150 Wörter.

## Bilder

- Mindestens 1200 Pixel breit, Querformat. JPG für Fotos, PNG für Logos, Logos mit Darstellung „Komplett zeigen".
- Bildtyp und Credit sind Pflicht. Kein KI-Bild als Ereignisfoto. Ohne Bild erscheint eine gekennzeichnete Grafik.
- Facebook-Bildlinks laufen nach wenigen Tagen ab: Bild herunterladen und hochladen, Credit auf den Urheber setzen.
- Blaulicht: keine erkennbaren Personen, Kennzeichen, Verletzten.

## Formate

Meldung, Bericht, Kurz notiert (ohne Bild, erscheint im Kurzticker der Startseite), Eilmeldung (48 Stunden im roten Balken), Interview, Hintergrund, Kommentar, Kolumne, Tipps, Liveticker. Familienanzeige, Nachruf: Ressort „Menschen" wählen; sie erscheinen zusätzlich im Block „Anzeigenmarkt · Familienanzeigen" der Startseite. Anzeigen immer mit dem Schalter „Anzeige / gesponsert".

## Meldung ohne Datum

Nennt die Quelle (zum Beispiel eine Gemeinde-Pressemitteilung) kein Veröffentlichungsdatum, den Schalter „Quelle nennt kein Veröffentlichungsdatum" setzen und bei „Quelle abgerufen am" das Abrufdatum eintragen. „Veröffentlicht am" dient dann nur als Sortierdatum.

## Termine, Vereine, Betriebe

Termine über die Sammlung „Termine" anlegen; jeder Termin bekommt eine eigene Seite mit Kalenderdatei. Termine aus dem Formular „Termin melden" kommen als E-Mail und im Netlify-Dashboard (Forms) an und werden von Hand übernommen. Vereine und Betriebe ebenso. In der Seitenspalte markiert der Monatskalender automatisch alle Tage mit Termin; ein Klick auf einen markierten Tag führt zum ersten Termin dieses Tages.

## Korrekturen

Fehler im Artikel korrigieren, unter „Korrekturen" Datum und Text eintragen, „Aktualisiert am" setzen. Bei wesentlichen Fehlern zusätzlich die Seite Korrekturen ergänzen.

## WhatsApp-Kanal

Der Kanal-Link wird in Einstellungen → Website → Social eingetragen; danach erscheint der Kanal im Seitenkopf, in jeder Seitenspalte und auf der Seite /whatsapp/. Für Automatisierungen liefert /api/latest.json die letzten 20 Meldungen und Termine.

## Fußball

Tabelle und Spielplan des SC 1919 Merzenich stehen in Einstellungen → Fußball SC 1919 Merzenich (Datei content/daten/fussball.json). Nach jedem Spieltag Tabelle, nächste Spiele und Datenstand aktualisieren; Quelle bleibt FUSSBALL.DE.

## Werbung

Vier feste Plätze: Billboard unter dem Zeitungskopf, native Anzeige nach der vierten Meldung im Nachrichtenstrom, im Artikel nach dem zweiten Absatz, Seitenspalte. Motive, Links und der Text der nativen Anzeige stehen in Einstellungen → Website → Werbung. Ohne Motiv steht der Firmenname im Rahmen. Der Schalter „Banner anzeigen“ blendet alles auf einmal aus.

## Stellenmarkt

Stellen werden in der Sammlung „Stellenmarkt“ angelegt und laufen unter /jobs/ mit Google-JobPosting-Markup. „Gültig bis“ nimmt die Anzeige automatisch vom Netz. Anfragen von Betrieben kommen über das Werben-Formular (Auswahl „Stellenanzeige“).
