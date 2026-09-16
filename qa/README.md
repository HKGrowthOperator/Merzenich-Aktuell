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
