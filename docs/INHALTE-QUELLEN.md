# Woher die Inhalte kommen (Stand 28.09.2026)

Die ausgelieferte Seite liegt in `chatgpt-site/`. Die Artikel stammen aus zwei Generationen. Beide sind gültig, und keine wird gelöscht.

| Herkunft | Artikel | Erkennbar an | Pflege |
|---|---|---|---|
| `inhalte/meldungen/*.json` → `deploy/meldungen.mjs` | 44 | `data-meldung="<hash>"` im `<article>` | JSON-Eintrag anlegen oder ändern, dann `node deploy/kette.mjs` |
| Ältere Artikelseiten (bis 16.09.2026, aus `site-source/content/artikel/*.md` erzeugt und seitdem als HTML weitergeführt) | 67 | kein `data-meldung` | Seite direkt in `chatgpt-site/<ressort>/<slug>/index.html`; die Generatoren ergänzen Bild, Ortsmarke, Listen, Feeds und Suche |

Deshalb meldet `deploy/meldungen.mjs` „44 aus inhalte/meldungen“, während `api/inhalte.json` 111 Artikel zählt. `site-source/` ist der frühere Generator. Seine Ausgabe `site-source/dist/` ist Wegwerf-Ausgabe und wird nicht ausgeliefert.

## Neue Inhalte

- **Meldung:** Neuer Eintrag in `inhalte/meldungen/<jahr>-<quelle>.json`. Pflicht sind slug, ressort, ortsteil, kicker, titel, dek, datum, absaetze, themen, quelle (Name, URL, Stand) und bildklasse. Nur was in der Quelle steht; die Rohdaten legt `deploy/quellen-abruf.mjs` (Workflow „Quellen-Abruf“) unter `imports/quellen/` ab. Vor dem Anlegen prüfen, ob die Meldung schon als ältere Seite existiert: `qa/pruefung.mjs` findet doppelte Einsatznummern.
- **Termin:** Neuer Eintrag in `inhalte/termine/*.json`. `deploy/termine.mjs` schreibt die Terminseite und daraus Liste, Zähler, Kalender (`kalender.ics`, `termin.ics`), Feed, Sitemap und „Weitere Termine“.
- **Gemeindedaten (Rathauszeiten, Abfall):** nur in `deploy/gemeinde.json`. Daraus erzeugen `deploy/gemeinde.mjs` die Seite `/service/` und `deploy/cockpit.mjs` die Rathaus-Kachel auf der Startseite.
- **Startseite:** Aufmacher und Nebenmeldungen wählt `deploy/inhaltsindex.mjs`. Redaktionelle Setzungen stehen in `chatgpt-site/api/editorial-current.json`. Jede Setzung läuft ab (Feld `bis`, sonst 72 Stunden).
- **Sport:** `chatgpt-site/api/sport-current.json` enthält einen gemeinsamen Datenstand für Tabelle, letztes und nächstes Spiel. Ist das nächste Spiel vorbei und sein Ergebnis noch nicht eingetragen, zeigt `/sport/` es als „Ergebnis noch nicht bestätigt“.

## Prüfen

```
node deploy/kette.mjs           # zweimal
node deploy/kette.mjs --check   # darf nichts mehr ändern
node qa/pruefung.mjs            # 0 Fehler
node qa/routen.mjs --bericht    # jede Route, schreibt docs/SITE-AUDIT.md
```
