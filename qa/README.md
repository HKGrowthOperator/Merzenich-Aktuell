# QA

Hier gehören reproduzierbare Prüfstände und Vorher-/Nachher-Nachweise hinein.

Zielstruktur:

```text
qa/
  before/
  after/
  QA-WORDPRESS.json
  RELEASE-GATES.md
```

## Ausführen

```bash
node qa/pruefung.mjs          # Bericht, Rückgabewert 1 bei Fehlern
node qa/pruefung.mjs --json   # maschinenlesbar
```

Ohne Fremdpakete und ohne Browser. Läuft bei jedem Push und jedem Pull Request
über `.github/workflows/pruefung.yml`.

Geprüft wird heute:

| Bereich | Was |
|---|---|
| Markup | Jede Kennung und jede Vorlagenklasse, die `app.js` benutzt, muss im Markup stehen |
| Inhalte | `content.json` lesbar, Pflichtfelder je Artikel, lesbare Daten, eindeutige Kennungen, keine ausführbaren Adressschemata |
| Verweise | Jeder örtliche Verweis aus `index.html` zeigt auf eine vorhandene Datei |
| PHP | `php -l` über alles unter `wordpress/`, sofern PHP bereitsteht |
| Platzhalter | Lorem ipsum, TODO, FIXME, Mustermann und Ähnliches |

Die Markup-Prüfung ist die wichtigste: Wird im Markup eine Kennung umbenannt,
fällt in `app.js` still eine Funktion aus. Beim Arbeiten zu zweit am selben
Repository ist das der wahrscheinlichste Weg, etwas kaputt zu machen.

Noch nicht ausführbar und weiterhin von Hand zu prüfen:

Pflichtprüfungen vor Releases:

- 1440 px Desktop
- 390 px Mobile
- Header weiß, Sticky `top: 0`, kein Floating-Gap
- keine horizontale Scrollbar
- Hero aktuell
- Eventdaten korrekt
- Wetter real/sauberer Fallback
- Sportdaten konsistent
- Kommentare funktionsfähig
- Werbung schaltbar
- keine Fake-/Demo-Inhalte
- keine kaputten Bilder/Links
- PHP ohne Syntaxfehler
- Theme/Core installierbar

Lokale PASS-Werte immer von LIVE-PASS unterscheiden.
