# Symbolbilder: Anforderung an die Pools

Stand: 22.09.2026 · Status: verbindlich, erfüllt · Beleg: `qa/befunde/symbolbilder-2026-09-22.png`
(Befund vor dem Austausch: `qa/befunde/symbolbilder-2026-09-19.png`)

Gilt für alles unter `chatgpt-site/assets/symbolbilder/<pool>/`. Ergänzt
`docs/DESIGN-STANDARD-2026.md` Abschnitt 6 (Bildsystem) und Abschnitt 3 (Farben).

## Warum es dieses Dokument gibt

Am 19.09. lagen 320 Dateien in 16 Pools, je 20. Gerendert im Kartenformat waren es
Farbverläufe mit aufgedrucktem Wort. `sport-01-spiel`, `sport-02-zweikampf`,
`sport-03-fussballplatz` und `sport-04-tor` waren vier nicht unterscheidbare olivgoldene
Rechtecke. Polizei vier blaue, Feuerwehr vier rote.

Nachgemessen über alle Pools, nach Normalisierung von Text, Farbwerten und Zahlen:

| | 19.09. | 22.09. |
|---|---|---|
| Dateien je Pool | 20 | 20 |
| verschiedene Strukturen je Pool | 3 | 20 |
| Anteil eigener Strukturen | 15 % | 100 % |
| Dateigrößen | 1457 bis 1517 Byte | 1050 bis 2785 Byte |
| Farbwerte insgesamt | 48 Pooltöne, Gold und Beige als Fläche | 6 Töne aus Standard Abschnitt 3 |

Am 22.09. wurde das Material in allen 16 Pools ausgetauscht. Der technische Unterbau ist
geblieben: feste Zuordnung je Artikel, stabile Rotation ohne Wechsel beim Neuladen,
`lizenzen.json` je Pool, Prüfsummen, Wappen-Migration. Die sechs bestehenden Zuordnungen
(`editorial-image-assignments.json`) haben den Austausch unverändert überstanden, weil die
Motiv-IDs an Slug und Position in `RAW` hängen und beides gleich geblieben ist.

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

## So sind die Pools gebaut

Die Motive sind Linienzeichnungen, erzeugt zur Bauzeit aus zwei Dateien im Repo:

- `deploy/lib-motiv-formen.mjs`: rund 260 gezeichnete Formen (Löschfahrzeug, Tor, Rathaus,
  Kerze, Bagger, Figuren als Silhouetten ohne Gesicht …), jede in lokalen Koordinaten mit
  festem Ursprung. Strich 14 Einheiten Anthrazit `#2b2926` auf 1600 × 900, Flächen Weiß und
  `#e9e6df`, Bodenband `#f3f1ec`, ein Akzent in `#971725` je Motiv.
- `deploy/lib-motiv-szenen.mjs`: je Motiv ein Rezept aus Grundfläche (Bodenband, Horizont,
  Innenraum oder nichts) und Platzierungen `[Form, x, y, Maßstab]`. `motivSvg()` setzt daraus
  das SVG mit `<title>`, `<desc>` und der kleinen Marke „SYMBOLBILD".

`deploy/lib-symbolbilder.mjs` ruft `motivSvg()` je Eintrag aus `RAW` auf; Zuweisung, Rotation,
`lizenzen.json` und Prüfsummen sind unverändert.

Die Unterscheidbarkeit ist eingebaut: `pruefeEindeutigkeit()` in `lib-motiv-szenen.mjs`
normalisiert jedes Motiv genau wie die QA (Text, Farbwerte, Zahlen raus) und verlangt, dass
alle 320 Strukturen und alle 320 Dateien verschieden sind. Der Generator wirft, bevor er
eine Datei schreibt. Zwei Motive mit gleichen Formen in nur anderer Position gelten dabei
als gleich; jedes Rezept braucht eine eigene Formfolge.

Ein neues Motiv braucht also drei Dinge: einen Eintrag am Ende der Poolliste in `RAW`
(nie mittendrin, sonst verschieben sich IDs), ein Rezept in `SZENEN`, und Formen, die es
noch nicht in dieser Folge gibt. Fehlt eine Form, wird sie in `FORMEN` gezeichnet.

## Prüfung

`qa/pruefung.mjs` enthält `pruefeMotivvielfalt`. Die Prüfung normalisiert jede SVG (Text,
Farbwerte und Zahlen raus), bildet eine Prüfsumme über die verbliebene Struktur und zählt
die verschiedenen Strukturen je Pool. Gemeldet wird ein Pool mit weniger als 60 % eigenen
Strukturen. Am Bestand vom 19.09. schlug sie in allen 16 Pools an, am Bestand vom 22.09. in
keinem.

Seit dem 22.09. meldet sie als **Fehler**: ein Rückfall auf Verlaufsflächen hält den Build an.

## Folge für die Startseite

Mit dem Austausch entfiel die Ausnahme in `deploy/inhaltsindex.mjs`, die Motive aus
`assets/symbolbilder/` von Aufmacher und Nebenmeldung ausschloss (bis dahin bekam eine
Meldung ohne Foto oben eine kompakte Textzeile). Jetzt gilt:

- Nebenmeldungen tragen ein Symbolbild wie jedes andere Bild.
- Der Aufmacher bevorzugt weiterhin die jüngste Meldung mit echtem Foto. Nur wenn keine
  bebilderte Meldung mit Foto frei ist, wird die jüngste mit Symbolbild Aufmacher.
- Ein redaktionell gesetzter Aufmacher in `api/editorial-current.json` muss weiterhin ein
  echtes Foto haben.
