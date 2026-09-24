# Startseite: Raster und Tokens (Anhang A, Phase 2 und 3)

Stand 23.09.2026. Verbindlich ist Anhang A (Art Direction). Entscheidungen von
KBS am 23.09.: Serif-Überschriften + Hanken Grotesk, „Vor Ort“ nach der Bühne,
Orte-Bühne weiter unten, „Ansichten aus der Gemeinde“ groß wie A7, bis es ein
tägliches eigenes Foto gibt.

## Reihenfolge

| # | Desktop (≥1100 px) | Telefon (≤759 px) |
|---|---|---|
| 1 | Kopf: Marke links, Suche Mitte, Wetter/Uhrzeit/Datum rechts | Kopf kompakt |
| 2 | Ressortleiste | Ressortleiste (wischbar) |
| 3 | Zeile: Ortswahl links, MERZENICH · JETZT rechts | MERZENICH · JETZT |
| 4 | Bühne: Aufmacher + 5 Highlights | Aufmacher, darunter 5 Highlights als Wischreihe |
| 5 | Vor Ort (links) · Hauptstrom (Mitte) · rechte Spalte nur mit Inhalt | Hauptstrom, dann Vor Ort |
| 6 | Anzeige, Ressorts im eigenen Rhythmus | dieselbe Folge |
| 7 | Die fünf Orte (Bildbühne) | dieselbe |
| 8 | Tipp, Ansichten aus der Gemeinde (Ruhepunkt), Leserhinweis | dieselbe |

Tablet (760–1099 px): Bühne zweispaltig bleibt, 2×2 wird eine Reihe unter dem
Aufmacher; Vor Ort und Hauptstrom untereinander, Hauptstrom zuerst.

## Bühne

- Raster `1.6fr 1fr`, Abstand 24 px. Der Aufmacher hat rund 61 % der Breite.
- Aufmacher: Bild füllt die Höhe der rechten Spalte (mindestens 420 px),
  darunter Ortsmarke, Serif-Überschrift 44–48 px, Teaser 18 px, Zeit. Kein Knopf.
- Rechts oben die zweite Meldung: Bild 2:1, Überschrift 24–26 px.
- Darunter 2×2: Bild 16:10, Überschrift 18–19 px, kein Teaser.
- Telefon: Aufmacher 4:3, Überschrift 30–32 px. Die fünf Highlights als
  waagerechte Reihe (Karte 78 % breit, die nächste schaut an), ohne Pfeile.

## Ortsmarke (A3.1)

`MERZENICH · GOLZHEIM   Vereine`. Versalien, 12,5 px, stark gesperrt:
MERZENICH in Bordeaux, der Ortsteil zurückhaltend, die Rubrik in normaler
Schreibung dahinter. Sans. Seit 24.09. dieselbe Marke auf allen Seiten:
Startseite, Ressortlisten, Archiv, Thema-Seiten, Weiterlesen-Blöcke, Vereins-
und Autorenseiten, Suchtreffer (Meldungen) und Artikelköpfe.

- Baustein: `markeHtml()` in `deploy/lib-artikel.mjs`; Generatoren schreiben
  ihn selbst, `deploy/ortsmarke.mjs` zieht ältere Karten nach (`--check` in CI).
- Artikelköpfe behalten das Markup `location-line` + `kicker`, weil
  `lib-artikel.mjs` daraus Ort und Dachzeile liest; `korrekturen.css` setzt sie
  im Stil der Marke.
- Suchtreffer: Feld `o` (Ortsteil) im Suchindex, geschrieben von
  `deploy/suche-index.mjs`.

## Merzenich-Linie

Die Pulslinie aus dem Logo, Bordeaux, Haarlinie `#e3e1dd`, keine Animation.
Höchstens zweimal je Seite: über dem Fuß (`.merzenich-linie`, eingesetzt von
`deploy/kopf-theme-einbinden.mjs`) und unter der Überschrift von
„Weiterlesen“ in Artikeln (`.brandline.thin`).

## Tokens

| Rolle | Wert |
|---|---|
| Grund | `#fdfdfc` (Seite), `#f5f4f1` (Vor Ort, Leisten) |
| Text | `#171513`, leise `#5f5b55` (≥ 6:1 auf Grund) |
| Linie | `#e3e1dd`, 1 px |
| Signal | Bordeaux `#971725`, nur Marke, aktive Navigation, LIVE, Links |
| Serif | Newsreader: Überschriften und Aufmacher |
| Sans | Hanken Grotesk: Navigation, Ort, Zeit, Service, Metadaten |
| Größen | Aufmacher 44–48 · Ressortkopf 34 · mittel 24 · klein 18–19 · Text 17 · Meta 13 |
| Abstände | 4/8-Raster: 4 8 12 16 20 24 32 40 48 64 |
| Radius | 0–3 px, keine Schatten |
| Bewegung | 180 ms, Bild beim Überfahren höchstens `scale(1.02)` |

## MERZENICH · JETZT

Eine Zeile, keine Kacheln: roter Punkt, „MERZENICH · JETZT“, neueste Meldung
mit relativer Zeit, Zahl der Meldungen von heute, nächster Termin. Werte
kommen aus dem Inhaltsindex und der Terminliste; fehlt ein Wert, entfällt das
Feld. Uhrzeit, Datum und Wetter stehen einmal, im Kopf rechts.
