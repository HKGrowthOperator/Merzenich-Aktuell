# Ausgelieferter Stand der ChatGPT-Site

Das hier ist der **Build-Ausgabestand** der öffentlichen Seite
`merzenich-aktuell-v19.luiskummer.chatgpt.site`, als Datei aus dem Chat
übergeben am 16.09.2026. 484 Dateien, 213 HTML-Seiten, 34 MB.

Es ist kein Quellcode. Es ist das, was der Browser bekommt.

## Was der Build-Bericht sagt

`build-report.json`, vom Generator selbst geschrieben:

```
generated  2026-09-16T12:12:14+02:00
stand      2026-09-09T10:15:14+02:00
artikel 42 · termine 6 · vergangeneTermine 7 · vereine 11 · orte 5 · seiten 28
```

`humans.txt` nennt die Technik: *statischer Generator ohne Abhängigkeiten,
Netlify, Decap CMS*, Schriften Newsreader und Inter.

## Wie die Seite aufgebaut ist

Vier Stylesheets liegen übereinander, jedes überschreibt das vorige:

| Datei | Größe | Rolle |
|---|---|---|
| `assets/style.css` | 54,9 KB | Grundstand des Generators |
| `assets/v19.css` | 27,8 KB | Überlagerung |
| `assets/v20.css` | 34,7 KB | Überlagerung, „shared editorial tokens" |
| `assets/recovery.css` | 23,7 KB | Überlagerung, „retain the V19 newspaper proportions and V20 reading/filter features" |

Zusammen 141 KB CSS. Dazu `assets/app.js` plus `assets/v20.js`.

Der weiße Kopf entsteht nicht im Grundstand, sondern wird in
`recovery.css` Zeile 3 erzwungen:

```css
.topbar,.masthead,.mainnav{background:#fff;color:#17191b;box-shadow:none;border-radius:0}
```

Das erklärt, warum Änderungen am Kopf schwer zu halten sind: Der Grundstand
setzt eine dunkle Fläche, drei Ebenen später wird sie wieder hell gesetzt.
Wer im Grundstand etwas ändert, kämpft gegen die Überlagerung.

## Was hier fehlt

Der **Generator** selbst. Aus diesem Ordner lässt sich die Seite nicht neu
bauen, nur ausliefern. Wer Inhalte oder Vorlagen ändern will, braucht die
Quelle, aus der `build-report.json` entstanden ist.

## Wozu der Ordner gut ist

- Als Referenzstand: was öffentlich tatsächlich ausgeliefert wird.
- Zum Vergleich vorher/nachher bei jeder Änderung.
- Als Beleg für die Struktur, solange die Adresse selbst nicht erreichbar ist.

Nicht als Arbeitsgrundlage zum Editieren. Änderungen an einem Build-Ausgabestand
sind beim nächsten Bauen weg.
