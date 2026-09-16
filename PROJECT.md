# MERZENICH AKTUELL — PROJECT DIRECTIVE

Stand: 16.09.2026

Dieses Dokument ist die verbindliche Arbeitsanweisung für alle zukünftigen Chats/Agenten, die an Merzenich Aktuell arbeiten.

## Source of Truth

- Repository: `HKGrowthOperator/Merzenich-Aktuell`
- Branch: `main`
- Frontend: Repository-Root
- Bestehende öffentliche historische Referenz: `https://merzenich-aktuell-v19.luiskummer.chatgpt.site`
- WordPress Theme/Core sind ein **separater** Lieferstand unter `/wordpress-delivery` und dürfen nicht mit dem Frontend verwechselt werden.

## Grundregel

**Recovery + Completion + Polish. Kein radikales Redesign.**

Bestehende gute Komponenten schützen. Änderungen klein, nachvollziehbar und gezielt durchführen. Kein Parallelprojekt und keine v21/v22/v23-Kopie.

## Darstellung — verbindlich

- Standardansicht ist **Hell**.
- Es gibt genau zwei manuell wählbare Modi: **Hell** und **Dunkel**.
- Die Umschaltung ist oben über **Darstellung / Einstellungen** erreichbar; im Einstellungs-Panel steht sie an erster Stelle.
- Auswahl wird lokal auf dem Gerät gespeichert.
- Hellmodus: weißer Header und helle redaktionelle Seite.
- Dunkelmodus: schwarzer Header, dunkle Seite und schwarzer Footer mit entsprechend angepassten Kontrasten.
- Kein automatischer Systemmodus als Standard. Nutzerentscheidung hat Vorrang.

## Header — verbindlich

- Hintergrund im Hellmodus `#FFFFFF`; im Dunkelmodus schwarz/dunkel entsprechend dem Theme
- sticky, `top:0`
- kein Abstand oberhalb
- keine Floating Island, keine Capsule, keine große Rundung
- unten exakt eine feine dunkle Linie
- Wortmarke kleiner und zeitungsartig
- Desktop Mitte: Suche, ca. 420–500px
- rechts: Wettericon + Temperatur + Uhrzeit + Datum
- nur eine Ressortzeile
- Ressorts: Aktuell, Blaulicht, Sport, Termine, Vereine, Rathaus & Politik, Leben, Wirtschaft, Menschen
- `Mehr`: Kontakt, Anzeigen, Werben & Mediadaten, Unterstützen, Archiv, Über uns, Redaktion, Grundsätze, KI & Redaktion

## Homepage

Desktop-Ziel:

- links 250–270px Service
- Mitte flexibel und dominant
- rechts 260–300px Werbung / Zusatzmodule
- Gap 24–32px

Tablet:

- rechte Spalte unter Content verschieben

Mobile-Reihenfolge:

1. Header
2. Top News
3. weitere wichtige News
4. Veranstaltungen
5. Service
6. Werbung
7. weitere Ressorts

## Service

Reihenfolge:

1. Veranstaltungen — standardmäßig offen
2. Wetter — geschlossen
3. Immobilien — geschlossen
4. Stellen — geschlossen
5. Traueranzeigen — geschlossen
6. Familienanzeigen — geschlossen

Keine Fake-Inhalte. Leere Datenbereiche zeigen hochwertige Empty States.

## Events

- eine Datenquelle (`content.json` in dieser Preview; später CMS)
- abgelaufene Einzeltermine nicht unter kommenden Terminen anzeigen
- Mehrtagestermine nur aktiv, solange `end` nicht überschritten ist
- nächste 4–6 relevante Termine

## Wetter

Frontend-Preview nutzt einen zentralen Wetterabruf.

- Header und Accordion benutzen dieselben Daten
- Temperatur, Zustand, Icon, Tageshoch/-tief, Regenwahrscheinlichkeit
- Heute, Morgen, Übermorgen
- 20-Minuten-Cache
- bei Providerfehler letzten gültigen Cache nutzen
- wenn noch nie valide Daten vorhanden: Modul ausblenden, niemals Fake-Wert oder `0 °C`

## Hero

Automatische Gewichtung:

- Aktualität 40 %
- lokale Relevanz 30 %
- redaktionelle Priorität 30 %

Standardmäßig darf kein Beitrag älter als 7 Tage Hero werden. Ausnahme nur per bewusstem `heroOverride`.

## News Cards

Kern:

- Bild
- Ortsangabe
- Kategorie
- Headline
- kurzer Teaser
- Datum
- Kommentarzahl
- `Mehr lesen`

`Mehr lesen`:

- weiß
- rote Schrift
- 1px rote Border
- kleiner Radius
- Hover rot/weiß

Keine riesigen weißen Kartencontainer.

## Ortslogik

- Hauptort: `MERZENICH` rot
- Ortsteil: `MERZENICH · GOLZHEIM` etc.; `MERZENICH` rot, Ortsteil dunkel
- konsistent Homepage, Archive, Suche, Artikel, Related Content

## Bilder

- nur eigene, offizielle/freigegebene oder sauber lizenzierte Motive
- kein Google-Image-Scraping
- Hero möglichst >=1600px; normale Meldung möglichst >=1000px
- Credits und Alt-Texte speichern
- Archivbild deutlich, aber dezent kennzeichnen

## Sport

Sportdaten immer als eine Einheit aktualisieren:

- letzter bestätigter Stand
- pending Spiel, falls Ergebnis offiziell noch offen
- nächstes Spiel
- Tabelle
- Punkte
- Tore
- Platz
- ein gemeinsamer sichtbarer Datenstand

## Kommentare

Die Frontend-Preview rendert den Kommentarbereich. Persistente Kommentare gehören in die produktive Hosting-/WordPress-Schicht. Solange kein echter Backend-Endpunkt verbunden und getestet ist, **nicht als funktional PASS kennzeichnen**.

## Werbung

Frontend darf nur aktive Slots rendern. Inaktive Slots erzeugen keinen Leerraum. Produktiv müssen globaler Schalter, Slot-Schalter, Laufzeit, Bild/Ziel und Kennzeichnung administrierbar sein.

## Typografie / Gestaltung

- bestehende redaktionelle Richtung erhalten
- keine neue Fontfamilie aus reinem Änderungsdrang
- keine 70px Landingpage-Headlines
- Hero Desktop ca. 42–50px, Mobile 30–34px
- Section H2 30–36px
- News H3 20–24px
- Body Homepage 16–18px, Artikel 18–19px
- keine Glassmorphism-/SaaS-Karten
- keine Gradient Blobs
- keine starken Schatten
- keine großen Radien
- Animation nur dezent: 180–200ms; Bildzoom maximal 1.02

## Footer

Claim schützen:

> Internet-Zeitung für die Gemeinde Merzenich und Umkreis.

Footer kompakt halten. Nur Spacing, Typografie, Ausrichtung und Responsive polieren. Im Dunkelmodus ist der Footer schwarz/dunkel; im Hellmodus bleibt er hell.

## Content-Refresh vor Release

Vor jedem echten Release neu prüfen:

- Gemeinde Merzenich / offizielle Kanäle
- Heimat-Info Merzenich
- Kreis Düren
- Polizei Düren / Presseportal
- Feuerwehr
- lokale Vereine
- FUSSBALL.DE
- Veranstaltungen
- relevante Ortsteile / lokale Einrichtungen
- Bildrechte

Gefundener Inhalt ist nicht automatisch ein Artikel. Workflow:

`Fund → Quelle prüfen → Datum → Ort → Bildrechte → redaktionell schreiben → Human Review → Publish`

## Statusbegriffe

- LOCAL READY — Source vorhanden, nicht öffentlich
- STAGING READY — auf echter Hosting-Stagingumgebung getestet
- LIVE READY — Deployment möglich und Gates bestanden
- LIVE DEPLOYED — öffentlich ausgeliefert und nochmals geprüft

Nur `LIVE DEPLOYED` heißt fertig.
