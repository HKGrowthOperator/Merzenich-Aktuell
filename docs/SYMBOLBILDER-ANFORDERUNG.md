# Symbolbilder: Anforderung an die Pools

Stand: 19.09.2026 · Status: verbindlich · Beleg: `qa/befunde/symbolbilder-2026-09-19.png`

Gilt für alles unter `chatgpt-site/assets/symbolbilder/<pool>/`. Ergänzt
`docs/DESIGN-STANDARD-2026.md` Abschnitt 6 (Bildsystem) und Abschnitt 3 (Farben).

## Warum es dieses Dokument gibt

Am 19.09. lagen 320 Dateien in 16 Pools, je 20. Gerendert im Kartenformat sind es
Farbverläufe mit aufgedrucktem Wort. `sport-01-spiel`, `sport-02-zweikampf`,
`sport-03-fussballplatz` und `sport-04-tor` sind vier nicht unterscheidbare olivgoldene
Rechtecke. Polizei sind vier blaue, Feuerwehr vier rote.

Nachgemessen über alle Pools, nach Normalisierung von Text, Farbwerten und Zahlen:

| | Wert |
|---|---|
| Dateien je Pool | 20 |
| verschiedene Strukturen je Pool | 3 |
| Anteil eigener Strukturen | 15 % |
| Dateigrößen | 1457 bis 1517 Byte |

Alle 16 Pools zeigen dasselbe Bild.

Damit verfehlen die Pools zwei bereits gesetzte Regeln: „unterschiedliche Bilder bei
unterschiedlichen Texten" und den Farbreset (Gold nur noch als winziges Detail, keine
beigefarbenen Großflächen). Vier Blaulichtmeldungen untereinander bekämen vier gleiche blaue
Flächen. Das ist das Wappen-Problem in neuer Farbe.

Der technische Unterbau bleibt und wird weiterverwendet: feste Zuordnung je Artikel, stabile
Rotation ohne Wechsel beim Neuladen, `lizenzen.json` je Pool, Prüfsummen, Wappen-Migration.
Ausgetauscht wird nur das Material in den Pools.

## Anforderung

1. **Unterscheidbarkeit.** Zwei beliebige Motive eines Pools müssen auf Kartengröße
   (rund 360 px breit, 16:9) als verschiedene Bilder erkennbar sein. Ein anderes Wort im
   Bild zählt nicht als Unterschied, ein anderer Farbton allein auch nicht.
2. **Gegenstand statt Fläche.** Erkennbar dargestellt wird die Sache, um die es geht. Für
   Sport zum Beispiel: Fußballplatz, Spielszene, Ball, Tor, Mannschaft, Kabine, Tribüne,
   Flutlicht, Stadion.
3. **Farbe nach Standard Abschnitt 3.** Weiß und sehr helles neutrales Grau als Grund,
   Anthrazit für Zeichnung, Bordeaux `#971725` als Marke. Kein Gold als Fläche, kein Beige
   als Fläche.
4. **Keine Scheingenauigkeit.** Keine erkennbaren Personen, keine Kennzeichen, keine
   Hausnummern, keine Firmenschilder, kein Vereinslogo. Ein Symbolbild behauptet nie, ein
   Foto des Ereignisses zu sein.
5. **Vollständiger Nachweis.** Jedes Motiv braucht einen Eintrag in `lizenzen.json` mit
   Alt-Text und Bildnachweis. Ein Motiv ohne vollständigen Eintrag wird nicht ausgeliefert.

## Prüfung

`qa/pruefung.mjs` enthält `pruefeMotivvielfalt`. Die Prüfung normalisiert jede SVG (Text,
Farbwerte und Zahlen raus), bildet eine Prüfsumme über die verbliebene Struktur und zählt
die verschiedenen Strukturen je Pool. Gemeldet wird ein Pool mit weniger als 60 % eigenen
Strukturen. Am Bestand vom 19.09. schlägt sie in allen 16 Pools an.

Sie meldet vorerst als Hinweis, nicht als Fehler: der Befund ist gemeldet und im Generator
bereits entschärft, ein roter Lauf würde nur unbeteiligte Arbeit blockieren. **Sobald die
Pools ausgetauscht sind, wird aus `hinweis()` ein `fehler()`**, damit ein Rückfall den Build
anhält.

## Solange die Pools so aussehen

Ein Beitrag ohne echtes Bild bekommt **keine Bildfläche**, sondern die kompakte Zeile
`front-zeile`. `deploy/inhaltsindex.mjs` schließt Motive aus `assets/symbolbilder/` über
`VERLAUFSPOOL` von Aufmacher und Nebenmeldung aus. Diese eine Zeile entfällt, sobald die
Pools die Anforderung erfüllen.
