# MA / Editorial System 1.0

Stand: 24.09.2026 · Status: Teil 1 umgesetzt (Marke, Scroll-Moment, Navlinie)

Leitsatz: weniger neue Sektionen erfinden, mehr Markenobjekte definieren. Ein
Ausschnitt der Seite soll auch ohne Logo nach Merzenich Aktuell aussehen.

## Markenobjekte

| Objekt | Was es ist | Quelle |
|---|---|---|
| **Monogramm** | Das M aus der Wortmarke „MERZENICH“, nachgezeichnet, kein Schrift-M. Darunter links beginnend die Merzenich-Linie (60 % der M-Breite), rechts davon der Bordeaux-Punkt. Keine Box, kein Kreis, kein Wappen, kein Gold. | `deploy/marke-monogramm.json` → `deploy/marke.mjs` |
| **Bordeaux-Punkt** | Kleiner Punkt am Ende der Linie. Später auch Herzschlag der Live-Zeile. | wie oben |
| **Merzenich-Linie** | Kurze Bordeaux-Linie unter dem M; als 1-px-Linie unter der Navigation, auf der der aktuelle Ressortabschnitt Bordeaux liegt; als Trenner mit Pulslinie vor dem Fuß und vor „Weiterlesen“. | `korrekturen.css` |
| **Farben** | Tinte `#171513`, Papier `#fdfdfc`, Bordeaux `#971725`, hell `#f5f2ec` | `deploy/marke.mjs` (`FARBEN`), `system.css` |
| **Schriften** | Newsreader (Titel, Serif), Hanken Grotesk (Navigation, Dachzeilen, Text in Listen) | `system.css` |
| **Ortsmarke** | MERZENICH in Bordeaux · Ortsteil in Anthrazit · Ressort leise | `deploy/ortsmarke.mjs`, `docs/STARTSEITE-RASTER.md` A3 |

## Ableitungen des Monogramms

Alle aus derselben Quelle, `node deploy/marke.mjs` schreibt sie, `--check`
läuft in QA und Materialize:

- `assets/marke/monogramm.svg`: Tinte auf hell, im eingeklappten Kopf
- `assets/marke/monogramm-hell.svg`: hell auf dunklem Grund (Bildwasserzeichen, Fuß)
- `assets/img/favicon.svg`: Browser-Tab, Papiergrund, bei dunklem Browser umgekehrt

Einmalig mit Chromium gerendert (`deploy/marke-png.mjs`, nicht in CI):

- `assets/img/avatar-1024.png`: App-Icon (Manifest „any“ und „maskable“,
  apple-touch-icon) und Social-Avatar. Das Zeichen nimmt 46 % der Kante ein und
  bleibt so in jeder runden Maske ganz sichtbar.

Lesbarkeit geprüft bei 16, 24, 32, 48 und 96 px.

## Scroll-Moment

- Der große Kopf schiebt sich weg (`kopf.js` setzt `html.kopf-kompakt`), die
  Ressortleiste bleibt oben.
- Links erscheint das Monogramm, 35 px, in 200 ms über Deckkraft und Maßstab
  0,96 → 1 (`cubic-bezier(.22,1,.36,1)`). Bei „weniger Bewegung“ ohne Animation.
- `kopf-theme-einbinden.mjs` schreibt den Link in alle Seiten, mit Hash im
  Bildpfad, damit ein neues Zeichen sofort ankommt.
- Unter 768 px bleibt die volle Wortmarke im kompakten Kopf.

## Noch nicht umgesetzt (Vorschlag für die Reihenfolge)

Nur mit echten Daten; was keine Quelle hat, entfällt.

1. **Ausgabezeile** „DONNERSTAG · 24. SEPTEMBER 2026 · MERZENICH“ und
   „AKTUALISIERT hh:mm“ aus dem Stand des Inhaltsindex (jüngste Meldung), nicht
   aus der Uhr des Besuchers.
2. **Live-Zeile** „● MERZENICH / JETZT“ mit dem Bordeaux-Punkt als Herzschlag,
   nur wenn eine Meldung jünger als die festgelegte Frist ist.
3. **Bildstufen A/B/C**: Aufmacher nur Stufe A (eigenes Foto oder Quelle,
   Querformat, scharf). Poolfotos höchstens B.
4. **Meldungsstufen** 1/2/3/Kurz und Titellängen je Stufe.
5. **„Die 5 wichtigsten“** mit Nummern, „Merzenich in einem Bild“ (nur mit
   eigenem Foto des Tages), „Neu seit deinem letzten Besuch“ (localStorage,
   nur im Browser des Lesers).
6. Mikrotexte, Abstandsrhythmus.
