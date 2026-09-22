# Editorial Image System V2

Die thematischen Ersatzmotive werden beim Build erzeugt und zugewiesen. Ein Browser-Reload darf niemals ein anderes Motiv auswählen.

## Source of Truth

- Motivliste (Slugs, Namen, Tags) und Zuweisung: `deploy/lib-symbolbilder.mjs`
- Gezeichnete Formen: `deploy/lib-motiv-formen.mjs`
- Szenenrezepte je Motiv und Eindeutigkeitsprüfung: `deploy/lib-motiv-szenen.mjs`
- Build-Time-Resolver: `deploy/symbolbilder.mjs`
- Zentrale Bibliothek: `chatgpt-site/data/editorial-images/editorial-images.json`
- Persistente Zuweisungen: `chatgpt-site/data/editorial-images/editorial-image-assignments.json`
- Technischer Browser-Fallback: `chatgpt-site/assets/bild-fallbacks.js`
- Anforderung und Beleg: `docs/SYMBOLBILDER-ANFORDERUNG.md`

## Pools

Pflichtpools: Sport, Polizei, Feuerwehr, Verkehr, Vereine/Ehrenamt, Rathaus/Gemeinde, Veranstaltungen, Leben/Menschen, Wirtschaft, Jobs/Arbeit, Immobilien, Familie, Trauer, Kultur/Freizeit, Schule/Bildung und Kirche/religiöses Leben.

Jeder Pflichtpool enthält genau 20 eigenständige Motive, insgesamt 320 lokale SVG-Grafiken. SVG wird verwendet, weil die Grafiken damit ohne Hochskalierung responsiv bleiben und keine externe Bild-URL ausfallen kann.

## Stil

Linienzeichnung: Anthrazit `#2b2926` auf Weiß, Nebenflächen `#e9e6df`, Bodenband `#f3f1ec`, ein Akzent in Bordeaux `#971725` je Motiv. Format 1600 × 900. Jedes Motiv zeigt einen erkennbaren Gegenstand (Fahrzeug, Gebäude, Gerät, Silhouette ohne Gesicht) und trägt unten rechts die Marke „SYMBOLBILD". Zwei Motive eines Pools sind auch bei 360 px Kartenbreite als verschiedene Bilder erkennbar; `pruefeEindeutigkeit()` stellt beim Build sicher, dass alle 320 Elementfolgen verschieden sind.

## Rechte

Die Poolmotive sind eigene, im Repo erzeugte redaktionelle Symbolgrafiken für Merzenich Aktuell. Sie enthalten keine Fotos fremder Ereignisse, keine erkennbaren Personen, Kennzeichen, Hausnummern oder Vereinslogos. Für jedes Motiv dokumentiert die zentrale Bibliothek:

- ID
- Pool
- src
- alt
- credit
- source
- license
- rightsCheckedAt
- tags
- width / height
- checksum

## Priorität

1. echtes Bild der konkreten Meldung
2. offizielles Quellenbild
3. passendes lokales Archivbild
4. thematisches Symbolbild aus dem Pool

Ein bereits vorhandenes echtes Bild wird nicht durch die Rotation ersetzt. Alte allgemeine Symbolbilder und unzulässige Vereinslogos als Newsfoto werden dagegen in das V2-System migriert.

## Rotation

Neue Meldungen ohne eigenes Bild erhalten ein Motiv anhand von Kategorie, Inhaltstags und bisheriger Nutzung. Die Zuordnung wird persistent gespeichert. Deshalb gilt:

- Rotation bei neuer Meldung: ja
- Rotation bei Reload: nein
- Rotation bei erneutem Build: nein
- bestehende Zuweisung verschieben, nur weil eine neue Meldung hinzukommt: nein

Die Motiv-IDs hängen an Slug und Position in `RAW`. Neue Motive werden deshalb nur am Ende eines Pools ergänzt, nie mittendrin.

## Browser-Fallback

`bild-fallbacks.js` entscheidet keine redaktionellen Bilder. Scheitert ein bereits ausgewähltes Bild technisch, versucht das Skript ausschließlich das nächste Motiv desselben Pools. Polizei, Feuerwehr oder Verkehr fallen niemals auf ein beliebiges Ortsbild zurück.

## QA

`node deploy/symbolbilder.mjs --check` ist Teil der bestehenden GitHub-QA-Kette. Der Check schlägt fehl, wenn unter anderem:

- ein Pflichtpool weniger als 20 gültige Motive hat
- Metadaten oder Dateien fehlen
- eine exakte Bilddublette als eigener Pool-Eintrag gezählt würde
- zwei Motive dieselbe Elementfolge haben
- die Rotation nicht stabil ist
- ein normaler Artikel bildlos bleibt
- ein allgemeines Vereinslogo als Newsfoto verbleibt
- ein Symbolbild aus dem falschen Pool stammt
- der Generator nicht idempotent ist

`qa/pruefung.mjs` prüft zusätzlich je Pool die Motivvielfalt (mindestens 60 % eigene Strukturen) und meldet einen Rückfall als Fehler.
