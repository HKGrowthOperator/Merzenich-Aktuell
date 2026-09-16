# DESIGN.md – Merzenich Aktuell

## Color (light only)
- paper #f7f5f0 (warmes Zeitungspapier), card #ffffff-nah (#fffdf9 erlaubt), ink #151412, ink-soft #3c3a36, muted #615d55
- line #e4e0d5, line-soft #eeeae0
- red #971725 (Rubriken, Links, Eilmeldung), red-dark #70111b (Hover)
- gold #c8a24e (Markenlinie, Akzent), gold-deep #a8842f, gold-wash #f6efdd
- stage #0c0b0a, stage-2 #141210 (Kopf, Fuß, Blaulicht-Bühne, Datumskacheln)
- ok #18794e, wa #25d366 (nur WhatsApp-Button)
Strategy: Committed. Schwarzer Kopf und Fuß tragen die Marke, Weinrot führt, Gold akzentuiert.

## Typography
- Display/Lesetext: Newsreader (variable, opsz), Überschriften 700, Lesetext 420
- UI/Meta: Inter (variable), Kicker 800 uppercase +0.1em, Meta 12.5px
- Scale: 46/30/24/21/18.5/16.5/14.5/13.5/12.5/11.5
- Body-Maß 680px, Artikeltext 19px/1.68

## Layout
- Shell 1240px, Content-Grid 1fr + 340px Sidebar, Artikel 760px + 320px
- Rhythmus: Sektionen 40px, Feed-Zeilen 20px, Sidebox 18px
- Bildverhältnis 16:9, Termine 2:1, Galerie 3:2, feste aspect-ratio (kein Layout-Sprung)

## Signature elements
- Tuschelinie mit Goldpuls: 2px Tusche, rechts ausklingend in den Puls (SVG), unter dem Kopf und unter jeder Rubriküberschrift
- Kicker: Rubrik in Weinrot, Ortsteil in Grau, getrennt durch goldenen Punkt
- Datumskacheln schwarz mit goldenem Monat
- Bildbadge (Bildtyp) unten links auf jedem Bild
- Kein-Foto-Grafik: schwarze Fläche, Pulslinie, Ressortwort in Newsreader

## Components
- Feed-Zeile (Bild 250px + Text), Karte (nur in 3er-Reihe für Sport/Leben), Photo-Story (Blaulicht), Termin-Zeile, Sidebox mit Gold-Unterstrich
- Buttons: Weinrot (primär), Gold (sekundär), Ghost (tertiär), WhatsApp-Grün nur für den Kanal
- Werbebanner: 728×90 im Artikel, 300×104 Sidebar, je Firma eigene Kennzeichnung „Anzeige"

## Motion
- Nur Hover-Farbwechsel und Schattenanhebung, ease-out, 180ms. Lesefortschrittsbalken 3px Gold.

## Don't
- Kein Dunkelmodus, kein Umschalter
- Keine Gradient-Texte, kein Glas, keine Side-Stripe-Borders über 1px
- Kein Newsletter-Formular
