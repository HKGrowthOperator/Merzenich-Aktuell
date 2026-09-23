# MERZENICH AKTUELL — PROJECT DIRECTIVE

Stand: 16.09.2026

Dieses Dokument ist die verbindliche Arbeitsanweisung für alle zukünftigen Chats/Agenten, die an Merzenich Aktuell arbeiten.

## Source of Truth

- Repository: `HKGrowthOperator/Merzenich-Aktuell`
- Branch: `main`
- Source of Truth für Änderungen: dieses GitHub-Repository auf `main`
- Öffentlich ausgelieferter statischer Stand: `chatgpt-site/` (der Ordnername ist nur ein historischer technischer Name; es gibt keine separate ChatGPT-Seite mehr)
- Coolify-Auslieferung: `deploy/coolify/Dockerfile` kopiert `chatgpt-site/` nach nginx
- Repository-Root enthält weiterhin Preview-/Kompatibilitätsdateien, ist aber nicht der aktuell von Coolify ausgelieferte HTML-Bestand
- Öffentliche Deploy-/Kontroll-URL: `https://merzenichaktuell.hk-growthoperator.de`
- Frühere externe ChatGPT-Sites sind nur historische Altstände und dürfen nicht als Source of Truth verwendet werden.
- WordPress Theme/Core sind ein **separater** Lieferstand unter `/wordpress-delivery` und dürfen nicht mit dem Frontend verwechselt werden.

## Grundregel

**Recovery + Completion + Polish. Kein radikales Redesign.**

Bestehende gute Komponenten schützen. Änderungen klein, nachvollziehbar und gezielt durchführen. Kein Parallelprojekt und keine v21/v22/v23-Kopie.

**Jede wirksame Änderung an Content, Frontend, Bildern, Komponenten, Logik oder Dokumentation wird im Repository `HKGrowthOperator/Merzenich-Aktuell` auf dem vorgesehenen Branch umgesetzt und dorthin gepusht.** Die öffentliche Seite wird nach dem Deploy über `https://merzenichaktuell.hk-growthoperator.de` kontrolliert.

## Darstellung — verbindlich

- Es gibt ab 23.09.2026 **nur noch den Hellmodus**.
- Dunkelmodus, Theme-Toggle, Theme-Einstellungen und gespeicherte Dunkelmodus-Zustände werden vollständig entfernt bzw. ignoriert.
- Kein Werbefrei-Abo und keine „Werbefrei lesen“-Navigation auf der Website.

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

Verbindlicher Stand aus dem KBS/Ordin-Feedback vom 23.09.2026:

- Sport erscheint **nicht mehr als Inhaltsblock auf der Startseite**. Sport bleibt als eigener Menüpunkt und eigene Unterseite bestehen.
- Der erste Bildschirm muss deutlich dichter, bildstärker und moderner sein. Ziel ist ein eigener Blue-Ocean-Charakter statt einer normalen Lokalzeitungs-Startseite.
- Oben steht eine große Highlight-News. Darum/rechts/unten stehen **fünf kleinere Highlight-News mit sichtbaren, größeren Bildflächen**.
- Jede redaktionelle Karte, bei der ein verifiziertes Bild oder ein freigegebenes Symbolbild verfügbar ist, zeigt ein Bild. Bildlose Textflächen dürfen nicht das Gesamtbild dominieren.
- Weißraum reduzieren: kompaktere vertikale Abstände, mehr redaktioneller Inhalt oberhalb der Falz, weiterhin klare Typografie.
- Zwischen den redaktionellen Kategorien sind horizontale Werbebanner-/Sponsorflächen vorgesehen. Kampagnen dürfen sich wiederholen; verschiedene Motive/Kunden sind möglich. Redaktion und Werbung bleiben sichtbar getrennt und als Anzeige/Sponsoring gekennzeichnet.
- Neue Rubrik **Foto des Tages**; täglich wechselnd, mit Bildcredit und redaktioneller Freigabe.
- Neue bezahlbare Rubrik **Tipp** für Vereine, Unternehmen und Sponsoren. Bezahlte Platzierungen werden eindeutig als Anzeige/Sponsored gekennzeichnet.
- WhatsApp-Kanal und Instagram werden als Social-Kanäle vorgesehen.
- Die Startseite darf keine Sport-Ergebnisse, Sporttabellen oder Sport-Aufmacher als eigene Sektion enthalten.

Desktop-Ziel:
- links Service
- Mitte redaktionell dominant
- rechte Zusatz-/Werbeflächen nur dann, wenn sie belegt sind
- keine tote leere Spalte

Mobile-Reihenfolge:
1. Header
2. Highlight-Bühne
3. weitere wichtige News
4. Veranstaltungen/Service
5. Werbe-/Tippflächen
6. weitere Ressorts

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

## Bilder — verbindlich

**Jede redaktionelle Meldung / Artikelseite hat ein Startbild.** Ein fehlendes Recherchebild darf nicht mehr zu einer bildlosen Seite oder einem thematisch falschen Platzhalter führen.

Priorität:

1. echtes Bild zur konkreten Meldung, dessen Nutzung verifiziert ist
2. passendes, sauber lizenziertes Archiv-/Kategorie-Bild
3. verifiziertes, dauerhaftes Symbolbild aus der zentralen Fallback-Bibliothek

Weitere Regeln:

- Ein recherchiertes, rechtlich nutzbares Originalbild wird **nie** durch ein Symbolbild ersetzt.
- Wenn kein konkretes Bild nutzbar ist, wird ein semantisch passendes Symbolbild gewählt: z. B. Feuerwehrmotiv für Feuerwehreinsatz, Polizeimotiv für Polizeimeldung, Fußballmotiv für Sport, Arbeitsplatz für Stellen und Hausmotiv für Immobilien.
- Blaulichtmeldungen dürfen nicht mit einem beliebigen Rathaus- oder Ortsbild aufgefüllt werden.
- Symbolbilder werden im sichtbaren Bildnachweis ausdrücklich mit `Symbolbild` gekennzeichnet.
- Für jedes Fallback werden Quelle, Urheber, Lizenz und letzter Rechte-Check dokumentiert.
- kein Google-Image-Scraping
- nur eigene, offizielle/freigegebene oder sauber lizenzierte Motive
- Hero möglichst >=1600px; normale Meldung möglichst >=1000px
- Credits und Alt-Texte speichern
- Archivbild deutlich, aber dezent kennzeichnen
- Live-Fallbacks liegen in `chatgpt-site/assets/bild-fallbacks.js` und werden zentral über `chatgpt-site/assets/kopf.js` auf den ausgelieferten Seiten geladen. Die Root-Preview nutzt zusätzlich `image-fallbacks.js`. Rechte-/Quellendokumentation liegt in `docs/image-fallbacks.md`.

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

Produktiv in WordPress:
- **Jeder neue Kommentar ist zunächst „wartet auf Freigabe“** und wird niemals automatisch veröffentlicht.
- Redaktion/Admin sieht eine Moderationsliste mit Mehrfachauswahl.
- Workflow: unerwünschte Kommentare abwählen/ablehnen, anschließend **„Alle ausgewählten genehmigen“** bzw. „Alle genehmigen“ für den verbleibenden Satz.
- Einzelgenehmigung und Einzelablehnung bleiben möglich.
- Nach Genehmigung erhält der Verfasser eine E-Mail an die beim Kommentar angegebene Adresse, sofern eine E-Mail angegeben wurde.
- Die E-Mail-Adresse wird niemals öffentlich angezeigt.
- Spam-/Melde-/Rate-Limit-Schutz bleibt erhalten.
- Die statische Preview darf diesen produktiven Workflow nicht als funktionsfähig behaupten, solange kein WordPress-Backend angeschlossen ist.

## Werbung

- Startseite: Werbebanner zwischen redaktionellen Kategorien als klar getrennte, gekennzeichnete Slots.
- Kampagnen können auf mehreren Slots wiederholt werden.
- Werbekunden sollen im WordPress-Zielsystem begrenzte eigene Zugänge erhalten können; Veröffentlichung/Änderung bleibt redaktionell freigabepflichtig.
- Wirtschaft kann zusätzlich Unternehmens-/Partnerprofile aufnehmen.
- Inaktive Slots erzeugen keinen unnötigen Leerraum.
- Kein Werbefrei-Abo.

## WordPress Rollen & Freigabe

Für den späteren WordPress-Go-live ist ein Contributor-/Approval-Modell verbindlich:

- **Polizei/Feuerwehr → Blaulicht**
- **Sportverein(e) → Sport**
- **Gemeinde/Rathaus → Rathaus & Politik**
- **Vereine → Vereinsnews**
- **Unternehmen/Partner → Wirtschaft und freigegebene Werbeflächen**
- **Immobilien-Verantwortliche → Immobilienanzeigen**

Externe Nutzer dürfen nur ihre zugewiesenen Inhaltsarten/Kategorien sehen und bearbeiten. Sie dürfen Entwürfe einreichen, aber **nicht selbst veröffentlichen**. Jede Einreichung geht als Freigabeanfrage an Redaktion/Admin. Redaktion genehmigt, bearbeitet oder lehnt ab. Rechte werden nach dem Least-Privilege-Prinzip vergeben.

## Navigation

- Desktop-Ressorts bleiben sichtbar.
- Sport erhält eine größere ausfahrende Untermenü-/Megamenü-Fläche nach dem Vorbild großer Lokalportale, ohne das Layout von Oberberg Aktuell zu kopieren.
- Untermenüs können Vereinskanäle, Tabellen/Spielplan, Sportmeldungen und relevante Unterrubriken zeigen.

## Anzeigen-/Marktführung

- Immobilien: „Anzeige aufgeben“ muss prominent und früh sichtbar sein.
- Beim Erstellen einer Anzeige wird zuerst visuell gewählt, **was** aufgegeben werden soll; Auswahlkarten enthalten ein passendes Bild/Icon und kurze Erklärung.
- Immobilien, Trauer, Familienanzeigen und Werbung verwenden eine geführte Schrittfolge statt unübersichtlicher Unterpunkte.
- Ziel: Nutzer verstehen ohne Erklärung, welchen Weg sie für ihren Inhalt wählen.

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

Für Bilder gilt separat:

`konkretes Bild gefunden → Nutzungsrecht prüfen → erst dann verwenden`

Wenn kein nutzbares konkretes Bild vorhanden ist:

`Fallback-Kategorie bestimmen → verifiziertes Symbolbild verwenden → als Symbolbild kennzeichnen`

## Statusbegriffe

- LOCAL READY — Source vorhanden, nicht öffentlich
- STAGING READY — auf echter Hosting-Stagingumgebung getestet
- LIVE READY — Deployment möglich und Gates bestanden
- LIVE DEPLOYED — öffentlich ausgeliefert und nochmals geprüft

Nur `LIVE DEPLOYED` heißt fertig.
