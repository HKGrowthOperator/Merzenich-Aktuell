# Startseite – Bauplan 2026 (erste drei Bildschirmhöhen und Folgeabschnitte)

Stand: 17.09.2026 · Grundlage: Entwurf 01 und Design Standard 2026. Kein Code.
Für jede Fläche stehen Datenquelle, Regel und Leerzustand. Was keine Quelle hat, wird
nicht gebaut, bis die Quelle einen echten Abruf bestanden hat.

## Bildschirm 1: Kopf und Editorial Stage

**Header · Navigation · Ortszeile** wie im Standard, Abschnitt 8. Wetter im Header aus
`/api/weather.json` (Open-Meteo, läuft). Leerzustand: ohne Daten keine Wetterangabe im
Header, kein Fehlertext.

**Hero, Desktop** (12 Spalten, Höhe ca. 560 px):

- Links 8/12: vier Positionen aus `api/editorial-current.json` (neue Struktur, siehe
  Datenmodelle). Jede Position: Bild 16:9, Kategorie · Ort · Zeit, Headline XL, ein Satz.
  Fortschritt „01 ━━━ 02 03 04“ unten links.
- Rechts 4/12 „Jetzt in Merzenich“: drei Einträge, Bild 3:2, Zeit, Headline, Kategorie.
  Quelle: Ereignisindex; solange er nicht existiert, die drei jüngsten Meldungen des
  Inhaltsindex, die nicht im Hero stehen.
- Regel: Position ohne Originalbild (Stufe 1 bis 3) oder gültiges Ersatzbild ist
  ausgeschlossen. Logos sind nie Hero-Bild.
- Leerzustand: weniger als vier bebilderte Meldungen → so viele Positionen wie vorhanden,
  ohne Fortschrittsanzeige bei nur einer.

**Hero, Mobil**: Bild volle Breite, Kategorie, Headline (max. vier Zeilen), Teaser,
Fortschritt; darunter „Jetzt“ als drei Zeilen in Größe S.

## Ortsmenü (nur geöffnet)

Erscheint zwischen Bildschirm 1 und 2. Gruppe „Merzenich“ mit fünf Orten, je Ort: Bild
und Headline der jüngsten Meldung, „Noch n Meldungen“ (Zahl aus dem Inhaltsindex),
„Alle Meldungen →“ auf die Ortsseite. Gruppe „Region“ erst mit dem ersten Ort, der eine
Meldung hat. Leerzustand eines Orts: Name und „Noch keine Meldung“ ohne Bild. Beim
Öffnen scrollt die Seite an das Menü.

## Bildschirm 2: Jetzt und Heute

Zweigeteilt, Desktop 7/12 links, 5/12 rechts. Mobil: Heute als horizontale Kachelreihe
vor dem Live-Stream.

**Merzenich Jetzt** (links, offen, kein Kasten): Live-Stream mit fünf Einträgen, jeweils
Zeit, Punkt und Linie, Headline, Kategorie · Ort, Link. Quelle: Ereignisindex. Überschrift
„Jetzt“ nur, wenn der jüngste Eintrag unter drei Stunden alt ist, sonst „Zuletzt
gemeldet“. Leerzustand ohne Ereignisindex: die fünf jüngsten Meldungen mit ihrer echten
Zeit unter „Zuletzt gemeldet“.

**Heute** (rechts, Dienstkarten):

| Kachel | Quelle | Anzeige | Leerzustand |
|---|---|---|---|
| Wetter | Open-Meteo (läuft) | Temperatur, Zustand, Tageshöchst/-tiefst | Kachel entfällt |
| Termine | Terminseiten (läuft) | „n heute“, erste zwei Titel | „Heute keine Termine“, nächster Termin mit Datum |
| Sport | fussball.de-Stand (läuft) | nächstes Spiel, Platz, Punkte | Kachel entfällt |
| Verkehr | keine Quelle | erst nach geprüftem Abruf | Kachel wird nicht gebaut |

Kandidaten für Verkehr, jeweils vor Einbau mit echtem Abruf zu prüfen: Mitteilungen der
Gemeinde Merzenich, Straßen.NRW-Baustellen, Warnmeldungen NINA.

## Bildschirm 3: Aktuell

Sektionskopf „Aktuell“. Editorial Grid: eine XL (8/12) plus zwei M (4/12) untereinander,
darunter eine Reihe mit vier M. Quelle: Inhaltsindex nach Datum, ohne die Hero-Positionen
und ohne die drei „Jetzt“-Einträge. Regel: alle Bilder einer Reihe gleich hoch, Headlines
max. drei Zeilen. Leerzustand: Reihe wird mit so vielen M gefüllt, wie bebilderte
Meldungen vorhanden sind, mindestens zwei, sonst entfällt die Reihe.

## Folgeabschnitte (Reihenfolge verbindlich, Form aus dem Standard)

1. **Blaulicht**: eine L (8/12) + zwei S; Ressortbilder nur ohne Ortsbezug.
2. **Merzenich & Ortsteile**: fünf Ortskacheln (Ortsfoto, Name, Zahl) als Dienstkarten.
3. **Sport**: Ergebnis- und Tabellenkarte links (fussball.de), rechts drei M.
4. **Region**: nur mit Inhalt, sonst entfällt der Abschnitt vollständig.
5. **Events**: Terminkarte „Heute / Wochenende“ plus drei M mit Veranstaltungsbild.
6. **Wirtschaft & Stellen**: zwei M plus Stellenkarte (market.json, Gesamtzahl, drei Einträge).
7. **Immobilien**: Karten 4:3 aus market.json, Gesamtzahl.
8. **Menschen & Familie**: drei M; ohne Inhalt eine ehrliche Leerfläche mit
   „Anzeige aufgeben“.
9. **Hintergrund / Dossier**: eine L zu Bürgewald aus dem Thema „buergewald“.
10. **Newsfeed**: chronologisch alle weiteren Meldungen in Größe S, zwölf je Seite.
11. **Abo-Band**: nur reale Kanäle (RSS, WhatsApp-Seite, Werbefrei). Newsletter erst mit
    Versanddienst.
12. **Footer**.

## Datenmodelle (Planung)

**editorial-current.json (neu)**: `stage[]` mit bis zu vier Positionen, je `id`, `url`,
`title`, `teaser`, `kicker`, `ort`, `published`, `image {src, alt, credit, kind}` wobei
`kind` einen Wert aus original, quelle, verein, ort, stock, ressort hat; `jetzt[]` bleibt
leer, solange der Ereignisindex fehlt.

**ereignisse.json (neu)**: Einträge mit `zeit` (ISO), `typ` (einsatz, sperrung,
mitteilung, spieltermin, veranstaltung, meldung), `ort`, `titel`, `url`, `quelle`
(Name und Link), `bild` optional. Nur aus geprüften Quellen befüllt; jede Quelle wird
einzeln freigeschaltet.

**Artikel-Faktenblock**: Felder `was`, `wo`, `wann`, `dauer`, `quelle` je Meldung, von
der Redaktion gepflegt; ohne Felder kein Block. Darunter „Weitere Meldungen zu diesem
Thema“ und „aus diesem Ort“ aus dem Inhaltsindex.

**Bildpools**: `assets/symbolbilder/<kategorie>/` mit `lizenzen.json` je Ordner
(Datei, Urheber, Lizenz, Quelle). Auswahl: Hash der Meldungs-ID modulo Poolgröße.

## Ausbaustufen

1. **Stufe 1**: eine CSS-Datei, Generator für die drei Größen, Header/Ortszeile/Ortsmenü
   (nur Gruppe Merzenich), Hero mit vier Positionen, Heute mit drei Kacheln, Aktuell,
   Folgeabschnitte 1 bis 3 und 5 bis 12. Bildpools werden vor Stufe 1 gefüllt.
2. **Stufe 2**: Ereignisindex mit geprüften Quellen, „Merzenich Jetzt“, Verkehr-Kachel,
   Region.
3. **Stufe 3**: „Meine Orte / Meine Themen“ im Browser, Artikel-Faktenblock, Karte und
   Zeitstrahl im Artikel.

## Offene Entscheidungen

- Verkehrsquelle (welche der drei Kandidaten, Prüfung durch echten Abruf).
- Regionsliste nach tatsächlicher Abdeckung.
- Wer füllt die Bildpools und bis wann (Voraussetzung für Stufe 1).
