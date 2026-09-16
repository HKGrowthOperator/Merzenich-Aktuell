# Merzenich Aktuell – verbindlicher Projektstand

Stand: 16. September 2026

Dieses Dokument fasst den aktuell gültigen Produkt-, Design-, Content- und Delivery-Stand zusammen. Frühere Mega-Prompts bleiben historische Referenz, aber bei Widersprüchen gilt dieses Dokument.

## 1. Grundprinzip

Merzenich Aktuell wird als echte digitale Regionalzeitung gebaut – nicht als Landingpage, SaaS-Template oder generische KI-Seite.

Ziel: funktionale Tiefe ähnlich Oberberg Aktuell, aber visuell moderner, ruhiger, hochwertiger und auf Mobile besser.

Keine weiteren radikalen Redesigns. Gute existierende Komponenten erhalten und nur gezielt verbessern.

## 2. Header

- Hintergrund vollständig weiß
- kein schwarzer Header
- Sticky Header direkt an `top: 0`
- kein Floating-Gap, keine schwebende Capsule, kein Weißraum darüber
- unten eine feine dunkle 1px-Linie
- Merzenich-Aktuell-Wortmarke kleiner und dezenter
- Suche mittig; Inhalt linksbündig
- rechts dynamisch: Wetterzustand/Emoji, Temperatur, Uhrzeit, Datum
- Datum/Uhrzeit in Zeitzone `Europe/Berlin`
- Desktop-Navigation in einer Ressortzeile
- ganz rechts `Mehr` als aufklappbarer Bereich

### Hauptressorts

- Aktuell
- Blaulicht
- Sport
- Termine
- Vereine
- Rathaus & Politik
- Leben
- Wirtschaft
- Menschen

### Mehr-Menü

Mindestens:

- Kontakt
- Anzeigen
- Werben & Mediadaten
- Unterstützen/Spenden
- Archiv
- Über uns
- Redaktion
- Grundsätze
- KI & Redaktion

Keine zweizeilige Hauptnavigation.

## 3. Orte

Eigene Taxonomie/Logik für:

- Merzenich
- Golzheim
- Girbelsrath
- Morschenich
- Bürgewald
- relevante Orte im Umkreis

Bei Beiträgen aus der Gemeinde:

- `MERZENICH` in Rot
- Ortsteil danach dunkel, z. B. `MERZENICH · GOLZHEIM`

Umliegende Orte erhalten kein falsches Merzenich-Präfix.

## 4. Homepage – Desktop

Grundhierarchie:

- links ca. 260 px Service
- Mitte flexibel und klar dominant: News
- rechts ca. 280 px sparsame Werbung/Zusatzmodule

Keine drei gleichwertigen Spalten.

### Mobile Reihenfolge

1. News
2. Service
3. Werbung

Keine Sidebar vor den Nachrichten.

## 5. Linke Serviceleiste

Accordion-System.

### Standardmäßig offen

- Veranstaltungskalender

### Standardmäßig geschlossen

- Wetter
- Immobilienmarkt
- Stellenmarkt
- Traueranzeigen
- Familienanzeigen

Diese Module dürfen nicht nur optisch existieren. Sie müssen reale Daten bzw. echte CMS-Strukturen verwenden.

## 6. Veranstaltungen

- zentrale Event-Datenquelle/CPT
- nächstes/kommendes Datum korrekt aus Start/Ende ableiten
- abgelaufene Termine automatisch aus „kommend“ entfernen
- auf Homepage 4–6 nächste Termine
- alle Orte/Ortsteile berücksichtigen
- Eventdetail mit Datum, Uhrzeit, Ort, Veranstalter, Quelle, ggf. Bild
- keine Demo-Events

## 7. Wetter

Ein zentraler Weather Service für Header und Service-Accordion.

Mindestens:

- aktuelle Temperatur
- Wetterzustand
- passendes Emoji/Icon
- Tageshoch/-tief
- Regenwahrscheinlichkeit
- Heute
- Morgen
- Übermorgen

Serverseitiger Abruf, Cache, Timeout, letzter gültiger Datenstand als Fallback.

Keine hartcodierten Wetterwerte.

Webcam nur bei geklärten Nutzungs-/Embeddingrechten.

## 8. Nachrichtenkarten

Reguläre Homepage-News besitzen:

- Bild
- Ort
- Kategorie/Ressort
- Headline
- optional kurzen Teaser
- Datum/Uhrzeit
- Kommentarzahl
- sichtbaren `Mehr lesen`-Button

### Mehr-lesen-Button

- weißer Hintergrund
- rote Schrift
- rote 1px-Umrandung
- kleiner Radius, kein Pill
- Hover: rot gefüllt, weiße Schrift

Keine generische SaaS-Cardwall.

## 9. Kommentare

Native WordPress-Kommentare bzw. vollwertige gleichwertige Integration.

- Kommentarzahl auf Card/Artikel
- Kommentarbereich unter Artikel
- Name
- E-Mail nicht öffentlich
- Kommentar
- Moderationshinweis
- Vorabmoderation
- pro Artikel/global abschaltbar
- Spam/Löschen/Freigeben/Antworten

Kommentarrichtlinien allein zählen nicht als Implementierung.

## 10. Hero / Aktualität

Merzenich **Aktuell** muss sichtbar aktuell sein.

- automatische Hero-Auswahl bevorzugt neue relevante Meldungen
- alte Storys laufen automatisch aus
- Story > 7 Tage standardmäßig nicht Hero
- ältere Story nur bei bewusster redaktioneller Fixierung/Feature-Kennzeichnung
- Content-Refresh vor jedem Release

Relevanzgewichtung: Aktualität + lokale Relevanz + redaktionelle Priorität.

## 11. Content-Refresh vor jedem Upload

Vor jedem Release zwingend:

- aktuelle News recherchieren
- Gemeinde Merzenich prüfen
- Kreis Düren prüfen
- Polizei Düren/Presseportal prüfen
- Feuerwehr prüfen
- Vereine prüfen
- Veranstaltungen prüfen
- Sport prüfen
- Wetter prüfen
- Bilder prüfen
- alte/abgelaufene Inhalte zurückstufen

Keine bloße Umsortierung eines alten Snapshots.

## 12. Bilder

- echte, thematisch passende Bilder bevorzugen
- keine KI-Ereignisbilder, die reale Ereignisse vortäuschen
- keine ungeklärten Bilder aus Google/anderen Medien kopieren
- Credits, Quelle, Lizenz/Freigabe dokumentieren
- Bild-Duplikate auf Homepage reduzieren
- hohe Auflösung für Hero
- Vereinslogos nicht verzerren
- Focal Point/sauberer Crop
- neue gute lokale Evergreen-Motive in Mediathek aufbauen

## 13. Sport

Sportmodul nicht wieder vereinfachen.

- Mannschaften getrennt
- Score separat
- echte Logos wenn vorhanden
- letztes Spiel
- nächstes Spiel
- Tabelle
- einheitlicher Datenstand
- pending/unbestätigte Ergebnisse transparent kennzeichnen
- keine erfundenen Ergebnisse

Tabelle je nach Datenlage mindestens:

- Pl.
- Mannschaft
- Sp.
- Punkte

und wenn verfügbar:

- S
- U
- N
- Tore
- Diff.

## 14. Märkte / Anzeigenbereiche

Eigene Content Types:

- `ma_property`
- `ma_job`
- `ma_obituary`
- `ma_family_notice`

Keine Fake-Einträge. Bei leerem Bestand sauberer Empty State.

Trauer/Familie standardmäßig datenschutzbewusst, z. B. noindex soweit vorgesehen.

## 15. Werbung

Eigener WordPress-Werbemanager.

- global AN/AUS
- pro Slot AN/AUS
- Start/Ende
- Gerät
- Priorität
- Sponsor
- Bild/URL bzw. sicher begrenztes HTML
- klare Kennzeichnung `ANZEIGE`
- deaktivierte/leere Slots rendern keinen Wrapper

Vorgesehene Slots:

- `homepage_sidebar_top`
- `homepage_sidebar_middle`
- `homepage_feed`
- `article_inline`
- `article_sidebar`
- `header_billboard`

Nicht alle gleichzeitig aktivieren.

## 16. Artikeltemplate

Ein zentrales WordPress-Single-Template für alle Nachrichten.

Reihenfolge ungefähr:

- Breadcrumb
- Ort
- Kategorie
- Headline
- Teaser
- Autor
- Datum/Uhrzeit
- Aktualisiert falls vorhanden
- Lesezeit
- Hero
- Bildcredit
- Artikeltext
- Inline-Werbung falls aktiv
- weitere Medien
- Quellen & Transparenz
- Tags
- Kommentare
- Related Content

Keine individuell manuell gebauten Artikelseiten.

## 17. Quellen & Transparenz

Bestehende Transparenzlogik bleibt erhalten:

- Originalquelle
- Quellen-URL
- Quellendatum
- Datenstand
- Bildcredit
- Bildtyp
- Korrekturen
- Human Review

Auf Homepage dezent; auf Artikelseite vollständig.

## 18. KI / EU AI Act

Technisch auf Artikel-50-Transparenz vorbereitet.

Beitragsfelder mindestens:

- KI genutzt ja/nein
- Nutzungsart
- Human Review ja/nein
- geprüft von
- geprüft am
- redaktionelle Verantwortung
- Disclosure erforderlich
- Disclosure-Text
- Bild KI-generiert
- Bild KI-manipuliert

Keine Behauptung vollständiger Rechtskonformität ohne juristische Prüfung.

Synthetische/manipulierte Medien, die real wirken können, müssen entsprechend gekennzeichnet werden.

## 19. Footer

Kompakt, deutlich kleiner als frühere Versionen.

Claim:

> Internet-Zeitung für die Gemeinde Merzenich und Umkreis.

Maximal wenige Hauptgruppen, z. B. Nachrichten, Service, Redaktion + Legal Bottom Bar.

Keine unnötig riesige Linkwand.

## 20. WordPress-Aufteilung

### Theme

Nur Präsentation/Frontend:

- Header
- Footer
- Templates
- Cards
- Archive
- Styles
- JS
- Responsive

### Core Plugin

Dauerhafte Funktionalität:

- CPTs
- Taxonomien
- Orte
- Events
- Märkte
- Werbung
- Wetter
- KI-Transparenz
- Artikel-Metadaten
- Kommentareinstellungen
- Sporteinstellungen/-daten
- Quellenradar
- Formulare

Keine produktiven Inhalte im Theme hardcoden.

## 21. WordPress Delivery

Das Projekt ist nicht fertig, wenn nur eine ChatGPT-Site existiert.

Zielartefakte:

- `merzenich-aktuell-theme.zip`
- `merzenich-aktuell-core.zip`
- `merzenich-aktuell-import.xml`
- `README-INSTALLATION.md`
- QA-/Release-Dokumentation

ChatGPT-Site = Vorschau/Referenz, nicht produktives CMS.

## 22. Designregeln

- hell, redaktionell, präzise, bildstark, seriös
- Weiß dominiert
- dunkle Typografie/Linien
- Rot gezielt für Marke/Ort/Buttons
- keine Glassmorphism-Optik
- keine Bubble-Cards
- keine riesigen Pill-Buttons
- keine starken Schatten
- keine SaaS-Dashboard-Ästhetik
- keine unnötigen 24px-Radien
- keine riesigen Landingpage-Headlines
- keine künstlich großen Weißräume

Premium entsteht aus Typografie, Raster, Bildern, Hierarchie und sauberen Abständen.

## 23. QA / Release Gates

Vor Release mindestens prüfen:

- Header weiß
- Sticky `top: 0`
- kein Floating-Gap
- Mobile 390 px ohne Overflow
- Desktop 1440 px ohne Overflow
- Hero aktuell
- Eventlogik korrekt
- Wetter real verbunden bzw. sauberer Fallback
- Sportdaten konsistent
- Mehr-lesen-Buttons vorhanden
- Ortskennung vorhanden
- Kommentare funktionieren
- Werbung im Backend schaltbar
- keine Fake-Inhalte
- keine kaputten Bilder
- keine PHP-Fehler
- Theme/Core installierbar

Jede Änderung gegen Vorher-Screenshot vergleichen. Eine sichtbar schlechtere Änderung wird revertiert.

## 24. Source of truth

Ab 16.09.2026 soll GitHub `HKGrowthOperator/Merzenich-Aktuell` der zentrale technische Source-of-Truth werden.

Keine weiteren nicht synchronisierten Parallel-Builds. Neue Arbeit gehört in dieses Repository bzw. in nachvollziehbare Branches/PRs.
