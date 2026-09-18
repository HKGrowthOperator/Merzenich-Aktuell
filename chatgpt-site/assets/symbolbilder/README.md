# Editorial Image System V2

Die thematischen Ersatzmotive werden beim Build erzeugt und zugewiesen. Ein Browser-Reload darf niemals ein anderes Motiv auswählen.

## Source of Truth

- Generator und Motivdefinitionen: `deploy/lib-symbolbilder.mjs`
- Build-Time-Resolver: `deploy/symbolbilder.mjs`
- Zentrale Bibliothek: `chatgpt-site/data/editorial-images/editorial-images.json`
- Persistente Zuweisungen: `chatgpt-site/data/editorial-images/editorial-image-assignments.json`
- Technischer Browser-Fallback: `chatgpt-site/assets/bild-fallbacks.js`

## Pools

Pflichtpools: Sport, Polizei, Feuerwehr, Verkehr, Vereine/Ehrenamt, Rathaus/Gemeinde, Veranstaltungen, Leben/Menschen, Wirtschaft, Jobs/Arbeit, Immobilien, Familie, Trauer, Kultur/Freizeit, Schule/Bildung und Kirche/religiöses Leben.

Jeder Pflichtpool enthält mindestens 20 eigenständige Motive. Die aktuelle V2 erzeugt exakt 20 pro Pool, insgesamt 320 lokale SVG-Symbolgrafiken. SVG wird verwendet, weil die Grafiken damit ohne Hochskalierung responsiv bleiben und keine externe Bild-URL ausfallen kann.

## Rechte

Die V2-Poolmotive sind eigene neutrale redaktionelle Symbolgrafiken für Merzenich Aktuell. Sie enthalten keine Fotos fremder Ereignisse, keine erkennbaren Personen, Kennzeichen, Hausnummern oder Vereinslogos. Für jedes Motiv dokumentiert die zentrale Bibliothek:

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

## Browser-Fallback

`bild-fallbacks.js` entscheidet keine redaktionellen Bilder. Scheitert ein bereits ausgewähltes Bild technisch, versucht das Skript ausschließlich das nächste Motiv desselben Pools. Polizei, Feuerwehr oder Verkehr fallen niemals auf ein beliebiges Ortsbild zurück.

## QA

`node deploy/symbolbilder.mjs --check` ist Teil der bestehenden GitHub-QA-Kette. Der Check schlägt fehl, wenn unter anderem:

- ein Pflichtpool weniger als 20 gültige Motive hat
- Metadaten oder Dateien fehlen
- eine exakte Bilddublette als eigener Pool-Eintrag gezählt würde
- die Rotation nicht stabil ist
- ein normaler Artikel bildlos bleibt
- ein allgemeines Vereinslogo als Newsfoto verbleibt
- ein Symbolbild aus dem falschen Pool stammt
- der Generator nicht idempotent ist

Die allgemeine Prüfung `qa/pruefung.mjs` läuft zusätzlich weiter.
