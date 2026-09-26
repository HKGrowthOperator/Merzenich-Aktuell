# Verhältnis zum ausgelieferten Stand

> **Produktionsregel:** `site-source/dist/` ist ausschließlich lokaler, wegwerfbarer Build-Output. Der Ordner ist per `.gitignore` ausgeschlossen, wird von Coolify nicht kopiert und darf niemals editiert, committed oder als Produktionsstand behandelt werden.

Dieser Ordner enthält den historischen statischen Generator, aus dem `chatgpt-site/` ursprünglich entstanden ist.
Der versionierte und von Coolify ausgelieferte Produktionsstand liegt in `chatgpt-site/`; Änderungen an `site-source/dist/` haben keinerlei Wirkung auf die Live-Seite.

## Gemessene Deckung

Beide gebaut und die Adressen verglichen:

```
gemeinsam    203
nur live       9
nur hier       0
```

Keine einzige Adresse existiert nur hier. Der ausgelieferte Stand ist diese
Quelle plus neun Seiten.

## Die neun Seiten, die hier fehlen

```
/anzeigen/            /immobilien/          /kommentarregeln/
/familienanzeigen/    /ki-redaktion/        /kontakt/
/redaktion/           /termine/denkmaltag-2026/
```

Sie stehen im ausgelieferten Stand unter `chatgpt-site/`, aber nicht in
`content/`. Solange das so ist, verlieren sie beim nächsten Bauen.

## Die vier Dateien ohne Quelle

Der ausgelieferte Stand lädt vier Dateien, die in keinem Generator und in
keiner Quelle stehen, nur im fertigen Build:

```
assets/v19.css       27,8 KB
assets/v20.css       34,7 KB
assets/recovery.css  23,7 KB
assets/v20.js
```

Zusammen rund 86 KB. Sie liegen über `assets/style.css` aus diesem Ordner
und überschreiben es. Der weiße Kopf kommt aus `recovery.css` Zeile 3.

Das ist der Grund, warum Änderungen am Kopf nicht halten: Wer hier etwas
ändert, ändert die unterste von vier Ebenen. Und wer diesen Generator neu
baut, ohne die vier Dateien mitzunehmen, verliert das gesamte Erscheinungsbild
der Live-Seite.

## Stand dieser Quelle

Sie ist weiter entwickelt als das, was live steht. Unterschiede, die auffallen:

| | live | hier |
|---|---|---|
| Schrift | Inter | Libre Franklin |
| Vereine | 11 | 12 |
| Inhaltsstand | 09.09.2026 | neuer |

Vor einem Neubau der Live-Seite gehören diese Unterschiede geklärt. Ein Bauen
aus diesem Ordner heraus würde die Seite sichtbar verändern, nicht nur
wiederherstellen.

## Bauen

```bash
cd site-source
node build.mjs                 # Produktionsbuild nach dist/
IMAGE_CDN=off node build.mjs   # lokale Vorschau ohne CDN-Pfade
node scripts/check.mjs         # Seiten, Strukturdaten, Links
```
