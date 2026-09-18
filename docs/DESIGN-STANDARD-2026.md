# Merzenich Aktuell – Design Standard 2026

Stand: 18.09.2026, Fassung 2 · Status: verbindlich, beschlossen im Gespräch (Entwurf 01 und V2-Regeln).

Fassung 2 ändert gegenüber Fassung 1 drei Dinge, jeweils auf Ansage des Auftraggebers:
Abstandsleiter von sechs auf zehn Stufen, weil die Referenzen neun bis elf führen.
Bildsystem: kein Beitrag bleibt bildlos, die frühere Regel "im Zweifel lieber kein Bild" ist aufgehoben.
Farbwelt: keine großen beigefarbenen Flächen mehr, Gold verliert seine Rolle.
Dieses Dokument ist die einzige Quelle für Gestaltung. Was hier nicht steht, gibt es nicht.

## 1. Zielbild

Merzenich Aktuell ist im Kern eine News-App mit der Glaubwürdigkeit einer Lokalzeitung
und den Diensten einer Stadtplattform. Verhältnis: 70 % News-App (schnell, visuell,
kompakt, Live-Informationen, Mobile first), 30 % Onlinezeitung (recherchierte Artikel,
Ressorts, Hintergrund, Vereine, Menschen).

Zwei Welten, klar getrennt:

| Welt | Inhalte | Gestaltung |
|---|---|---|
| Redaktion | Hero, Aktuell, Ressorts, Artikel, Dossier | offen auf der Fläche, Bild + Typografie erzeugen die Hierarchie, keine Boxen |
| Live / Dienste | Jetzt, Heute, Wetter, Verkehr, Termine, Ergebnisse, Tabellen, Jobs, Immobilien, Meine Orte | eine einzige Kartenform |

Unverhandelbar: Es werden nur echte Daten gezeigt. Jede Fläche hat einen definierten
Leerzustand. Keine Platzhalter, keine erfundenen Zeiten, keine Attrappen.

## 2. Ortslogik

- Merzenich (gesamte Gemeinde) ist immer Ebene 1. Die Startseite ist immer die Gemeinde.
- Gruppe „Merzenich“: Merzenich, Golzheim, Girbelsrath, Morschenich, Bürgewald.
- Gruppe „Region“: Nachbarkommunen, über die berichtet wird (Kandidaten: Düren,
  Niederzier, Nörvenich, Kerpen). Ein Ort erscheint erst, wenn mindestens eine
  veröffentlichte Meldung existiert. Die Gruppe erscheint erst mit dem ersten Ort.
- Weiter entfernte Meldungen nur, wenn sie für Merzenich relevant sind.
- Ein Klick auf einen Ort führt auf dessen Ortsseite. Die Startseite wird nicht gefiltert.
  Personalisierung („Meine Orte“) ist eine spätere, eigene Stufe.

## 3. Farben

Eine Identität, keine Ressortfarben. Ziel ist Newsroom und Wirtschaftsmagazin, nicht Lifestyle.

Ab Fassung 2: Weiß und Off-White als Fläche, Anthrazit für Text, Bordeaux als Marke, ein sehr dezentes
neutrales Grau für Linien und Ruhezonen. Keine großen beigefarbenen Hintergründe. Gold höchstens als
winziges Detail, wenn überhaupt, und nie für kleine Schrift, weil es den Kontrastwert verfehlt.
Verteilung ungefähr 85 Prozent Weiß und Off-White, 13 Prozent Bordeaux und Anthrazit, 2 Prozent Akzent.

| Token | Hell | Dunkel | Verwendung |
|---|---|---|---|
| `--flaeche` | #ffffff | #121110 | Hauptfläche |
| `--creme` | #f7f5f0 | #1a1917 | Dienstkarten, Ortsmenü, seltene Flächen |
| `--text` | #151412 | #f2efe9 | Headlines, Fließtext |
| `--text-2` | #3c3a36 | #cfcac2 | Teaser |
| `--meta` | #615d55 | #a8a39a | Zeit, Ort, Bildnachweis |
| `--linie` | #e4e0d5 | #2b2926 | Trennlinien |
| `--marke` | #971725 | #b8283a | Kategorie, Links aktiv, Section-Marker, ein Button je Sektion |
| `--marke-dunkel` | #70111b | #971725 | Hover, Fokus |
| `--gold` | #c8a24e | #d1ac5a | Ortsmenü-Akzent, Ergebnisbox, Fortschritt im Hero |
| `--gold-text` | #7a5e1c | #e2c27a | Gold als Text auf hellem Grund |

Verboten: eigene Farben je Ressort, farbige Vollflächen als Sektionshintergrund,
Verläufe, Schatten als Gestaltungsmittel.

## 4. Typografie

- Fließtext, Metadaten, Navigation, Dienstkarten: Inter (lokal, variabel).
- Headlines Größe XL und L, Sektionsköpfe: Newsreader (lokal, variabel), Gewicht 650.
- Headlines Größe M und S: Inter 650.

| Stufe | Größe / Zeilenhöhe | Einsatz |
|---|---|---|
| 12 | 12 / 16 | Kategorie, Zeit, Bildnachweis (Kategorie in Versalien, Laufweite 0,08 em) |
| 14 | 14 / 20 | Teaser in Größe S, Listen |
| 16 | 16 / 24 | Fließtext, Teaser in Größe M |
| 18 | 18 / 24 | Headline Größe S |
| 22 | 22 / 28 | Headline Größe M |
| 28 | 28 / 32 | Sektionskopf, Headline Größe L |
| 40 | 40 / 44 | Headline XL (Hero), mobil 30 / 34 |

Headlines sind auf drei Zeilen begrenzt (Hero: vier). Längere Headlines werden nicht
umgebrochen in eine vierte Zeile, sondern redaktionell gekürzt; das Raster wächst nie.

## 5. Raster, Abstände, Radien

- Inhaltsbreite 1200 px, 12 Spalten, Rinne 24 px, Seitenrand 20 px (mobil 16 px).
- Umbrüche: 390 (mobil), 768 (Tablet), 1024 (kleiner Desktop), 1280 (Desktop).
- Abstände aus einer benannten Leiter mit zehn Stufen: 4, 8, 12, 16, 20, 24, 32, 48, 64, 96.
  Dazu zwei fließende Werte für den Sektionsrhythmus: clamp(32px, 5vw, 64px) und clamp(48px, 7vw, 96px).
  Gemessen führen Guardian neun Stufen von 4 bis 96 und BBC neun bis elf von 4 bis 56, dazu 80. Sechs
  Werte wären enger als jede Referenz. Rohe Zwischenwerte bleiben innerhalb einer Komponente erlaubt,
  der Rhythmus zwischen Blöcken kommt ausschließlich aus der Leiter.
- Radien nur 0 (Redaktion), 6 (Bilder, Buttons), 12 (Dienstkarten).
- Innerhalb einer Reihe haben alle Bilder dieselbe Höhe. Kein Bild bestimmt die Kartenhöhe.

## 6. Drei Story-Größen

| Größe | Raster | Bild | Inhalt |
|---|---|---|---|
| XL (Lead) | 8/12 oder 12/12 | 16:9 | Kategorie · Ort · Zeit, Headline 40, ein Satz Teaser |
| M (Standard) | 4/12 oder 3/12 | 16:9 | Kategorie, Headline 22 (max. 3 Zeilen), Zeit |
| S (Kompakt) | Zeile | 3:2, 96 px breit | Headline 18, Ort · Zeit |

Es gibt keine vierte Größe. Sport, Blaulicht, Wirtschaft, Immobilien, Stellen werden aus
diesen drei Größen gebaut. Ressorts unterscheiden sich durch Wort und Inhalt, nicht durch
Form oder Farbe.

## 7. Bildsystem

Kein Beitrag bleibt bildlos. Das gilt ohne Ausnahme, auch für Blaulicht, Verkehr, Immobilien, Stellen,
Familie und Trauer. Die Zuweisung geschieht im Generator zur Bauzeit, nicht im Browser.

Formate: Hero 16:9 · Größe M 16:9 · Größe S 3:2 · Personen 4:3 · Immobilien 4:3 ·
Fotostrecke 16:9. Mindestbreite eines Originalbilds für XL und M: 960 px, für S: 480 px.
Kleinere Bilder werden nicht hochgezogen, sondern durch die Ersatzlogik ersetzt.

Bildpriorität:

1. echtes Bild der konkreten Meldung
2. offizielles passendes Quellenbild: Feuerwehr, Verein, Kommune, Veranstalter
3. passendes lokales Archivbild
4. thematisches Symbolbild aus dem Pool der Kategorie
5. niemals bildlos

Originalbilder (Stufe 1 bis 3) werden unverändert und ohne Symbolbild-Kennzeichnung
verwendet, mit Bildnachweis.

Regeln für Ersatzbilder (Stufe 4):

1. Jedes Ersatzbild trägt sichtbar „Symbolbild“, im Teaser und im Artikel.
2. Kein Ersatzbild zeigt erkennbare Personen, Kennzeichen, Hausnummern, Firmenschilder oder
   ein fremdes Feuerwehrhaus.
3. Ortsmotive nicht für Blaulicht. Blaulicht bekommt Fahrzeug- und Ausrüstungsmotive ohne
   Ortsbezug. Ortsmotive für Rathaus, Politik, Gemeinde, Veranstaltungen.
4. Kuratierter Pool im Repo, je Kategorie mehrere Motive, Lizenz je Datei dokumentiert. Die Auswahl ist
   deterministisch je Meldung und innerhalb einer Kategorie gleichmäßig verteilt, damit nicht jede
   dritte Meldung dasselbe Motiv zeigt. Keine generierten Bilder, keine Montagen.
5. Vereinslogos sind kein allgemeiner Fallback. Ein Logo als großes Aufmacherbild sieht nicht nach
   Redaktion aus. Für Fußball gehören Platz, Ball, Tor, Mannschaft, Tribüne oder lokales Vereinsumfeld
   in den Pool. Ein Logo erscheint nur dort, wo das Logo selbst das Thema ist.
6. Kein Foto, das zu einer anderen Meldung gehört, wird als Symbolbild einer fremden Meldung
   wiederverwendet.

Kategorien der Pools: Feuerwehr, Polizei, Verkehr, Fußball, Vereine, Rathaus/Politik,
Wirtschaft, Immobilien, Stellen, Veranstaltungen, Hochzeit, Geburt, Trauer, Menschen.

## 7a. Aufbau der oberen Fläche

Die linke Service-Spalte bleibt als eigenständiges Element erhalten. Sie ist der Punkt, an dem sich
Merzenich Aktuell von einer normalen Nachrichtenseite unterscheidet: links sofort das lokale Leben und
die Dienste, rechts die Redaktion.

Rechts daneben läuft eine dichte redaktionelle Matrix, damit kein Weißraum entsteht, während links
sieben Servicepunkte stehen: ein Aufmacher mit großem Bild, zwei mittlere Meldungen nebeneinander,
vier kompakte Meldungen, bei Bedarf eine Kurzmeldungsleiste. Die redaktionelle Fläche endet dadurch
ungefähr auf derselben Höhe wie die Service-Spalte. Header und Footer bleiben unverändert.

## 8. Komponenten

- **Header** (64 px, weiß): Logo links, Suche mittig (ab 1024), rechts Uhrzeit, Datum,
  Wetter als Text, Hell/Dunkel, Menü. Beim Scrollen klappt der Kopf ein; sichtbar bleibt
  nur die Navigationszeile mit kleinem „M“.
- **Navigation** (48 px): Aktuell · Blaulicht · Sport · Events · Rathaus · Leben ·
  Wirtschaft · Mehr. Eine Zeile, keine zweite Ebene.
- **Ortszeile** (36 px, Creme): „MERZENICH ↓“ links, sonst nichts. Öffnet das Ortsmenü.
- **Ortsmenü**: Fläche unterhalb des Hero. Gruppe Merzenich, dann Region. Je Ort: Name,
  Bild der wichtigsten Meldung, Headline, „Noch n Meldungen“, „Alle Meldungen →“. Beim
  Öffnen scrollt die Seite sanft an das Menü, beim Schließen zurück zum Kopf. Zustand je
  Gerät gespeichert, Standard geschlossen. Mobil als vertikale Liste je Ort.
- **Hero (Editorial Stage)**: 8/12 Hauptgeschichte mit vier redaktionell gesetzten
  Positionen, 4/12 Spalte „Jetzt in Merzenich“ mit drei Einträgen (Bild 3:2, Zeit,
  Headline). Fortschritt „01 ━━━ 02 03 04“. Alle vier Positionen sind fertig im HTML,
  Position 1 ist ohne JavaScript sichtbar.
- **Sektionskopf**: Name in Marke, dünne Linie in Text, rechts „Alle Meldungen →“.
- **Live-Stream-Eintrag**: Zeit, Punkt, Linie, Headline, Kategorie · Ort. Keine Karte.
- **Heute-Kachel**: Creme, Radius 12, Bezeichnung, Wert groß, Zusatz klein.
- **Dienstkarte**: Creme, Radius 12, Innenabstand 24. Einzige Boxform der Seite.
- **Tabelle**: Zebra ohne Rahmen, eigene Zeile markiert mit Gold links.
- **Anzeigen**: nur mit echtem Creative, gekennzeichnet, nie im Hero. Aktuell global aus.
- **Footer**: Marke, drei Linkspalten, Impressum, Datenschutz, RSS.

## 9. Bewegung

- Hero-Wechsel alle 7 Sekunden: Crossfade 600 ms, Bild bewegt sich um höchstens 2 %,
  Text blendet ein. Pause bei Hover, Fokus, Berührung und nach jedem manuellen Wechsel.
- Bei „Bewegung reduzieren“ im System: kein automatischer Wechsel, kein Zoom.
- Sonst keine Animationen außer Hover-Farbwechsel und dem Aufklappen des Ortsmenüs.

## 10. Zeit- und Live-Regeln

- Relative Zeit („vor 24 Min.“) nur unter 24 Stunden, danach Datum und Uhrzeit.
- „Jetzt“ heißt die Fläche nur, wenn der jüngste Eintrag unter drei Stunden alt ist,
  sonst „Zuletzt gemeldet“ mit Datum.
- Eine Zahl (z. B. „6 Termine heute“) wird nur gezeigt, wenn sie aus Daten berechnet ist.
  Ohne Quelle entfällt die Kachel, es steht keine Null und kein „–“.

## 11. Mobil

Mobil ist eine eigene Reihenfolge, nicht Desktop gestapelt: Header, Ortszeile, Hero (Bild
volle Breite, Kategorie, Headline, Teaser, Fortschritt), „Jetzt“ als Liste in Größe S,
Heute-Kacheln als horizontale Reihe, Aktuell (eine XL, danach Größe S), Ressorts jeweils
eine M und zwei S, Dienste als Karten. Keine Hero-Höhe über 1,2 Bildschirmhöhen.

## 12. Technische Umsetzung (Konsequenz, noch nicht beauftragt)

- Eine CSS-Datei mit den Tokens aus Abschnitt 3 bis 5. Die heutigen neun Dateien und die
  zur Laufzeit nachgeladenen Layer entfallen.
- Kein JavaScript, das Markup umschreibt. Laufzeit-Funktionen nur: Hero-Wechsel,
  Ortsmenü, Uhr/Wetter, Meine Orte.
- Ein Generator baut aus dem Inhaltsindex und dem Ereignisindex die drei Story-Größen
  und alle Sektionen. Reihenfolge und Leerzustände stehen im Bauplan.
- CI prüft: Bildpflicht je Teaser, feste Zeilenhöhen, keine horizontale Überbreite bei
  390/768/1024/1280/1440, ein H1, Hero-Position 1 ohne JavaScript sichtbar.
