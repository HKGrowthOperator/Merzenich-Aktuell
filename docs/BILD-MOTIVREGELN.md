# Bilder müssen zur Aussage passen: Motivregeln

Stand: 24.09.2026 · Status: verbindlich · Anlass: Rückmeldung KBS vom 24.09.

> „Die Bilder müssen immer, immer auch zu der Aussage und zum Design passen.
> Es kann nicht sein, dass da Geschwindigkeitskontrollen gemacht werden und da
> ein Haus zu sehen ist. Oder Polizei durchsucht Wohnung und da ist ein
> Helikopter.“

## Was schiefging

Die Vergabe wählte für eine Meldung nur den Pool (zum Beispiel „polizei“) und
nahm daraus irgendein Foto. So standen am 24.09. auf der Seite:

| Meldung | Bild | Warum falsch |
|---|---|---|
| 37 Zitronen bei Geschwindigkeitskontrolle | Polizeiwache Saerbeck | sieht aus wie ein Wohnhaus |
| Einbruch im Arnoldsweilerweg | Polizeihubschrauber | kein Bezug zum Einbruch |
| FC-Fanclub startet in die Saison | Dampflok der Rurtalbahn 1995 | kein Bezug |
| Fahrrad-Reparaturstationen | Straße in Ibbenbüren | zeigt keine Station |
| Fußballergebnisse | Plätze auf den Shetlands und den Färöern | Ausland, Felsküste |
| Aufmacher Ortsfest (Startseite) | „Ortsansicht“: Fachwerkhaus | Haus ohne Aussage |

## Die Regel

1. **Jedes Poolfoto hat ein gesichtetes Motiv** in `deploy/bildmotive.json`
   (Schlüssel: Commons-Dateititel). Beispiele: `fussball`, `feuerwehrhaus`,
   `rettungswagen`, `geschwindigkeitsmessung`, `stadion`, `kirmes`,
   `rathaus-merzenich`. Fotos ohne Eintrag werden nie vergeben.
2. **Jede Meldung bekommt höchstens eine Motivregel** (`MOTIVREGELN` in
   `deploy/lib-symbolbilder.mjs`), die erste passende von oben. Geprüft werden
   Pfad, Dachzeile, Titel, Teaser und Themen, nicht der Fließtext.
3. **Die Regel nennt die Motive, die die Aussage zeigen.** Nur Fotos mit genau
   diesem Motiv kommen in Frage, egal aus welchem Pool.
4. **Passt keine Regel oder fehlt das Motiv: kein Bild.** Die Meldung steht als
   Text. Lieber kein Bild als ein falsches.
5. Eigene Fotos der Redaktion oder der Quelle gehen immer vor.

Reihenfolge der Regeln: speziell vor allgemein. Geschwindigkeitskontrolle steht
vor Polizei, Fanclub vor Fußball, Reparaturstation vor Fahrrad.

## Wer prüft das

- `node deploy/symbolbilder.mjs --check` schlägt fehl, wenn ein Symbolbild nicht
  das Motiv seiner Regel zeigt. Läuft in QA und Materialize.
- Der Selbsttest prüft, dass eine Polizeimeldung kein Fußballfoto bekommt, nur
  weil keines mit passendem Motiv da ist.
- `node deploy/teaser-bilder.mjs --check`: Jede Karte (Thema-Seiten,
  Weiterlesen, Vereine) zeigt das Bild ihres Zielartikels, ohne Bild keine
  Bildfläche.
- Der Bericht von `symbolbilder.mjs` listet jede Meldung ohne Bild mit Grund
  („Regel geschwindigkeit, kein gesichtetes Foto mit Motiv …“).

## Neues Thema, neues Motiv

1. Regel in `MOTIVREGELN` ergänzen: `wenn` (Stichwörter ohne Umlaute, ß als ss)
   und `motive`.
2. Fehlt ein passendes Foto: Suchbegriffe im passenden Pool in
   `deploy/import-editorial-photos.mjs` ergänzen. Der Import läuft in GitHub
   Actions (Materialize).
3. Neue Fotos sichten: kein erkennbares Gesicht, kein lesbares Kennzeichen,
   keine Hausnummer, kein Firmenschild, kein Vereinswappen, kein Ausland mit
   erkennbarer Landschaft. Ergebnis in `deploy/editorial-photo-review.json`
   (freigegeben oder ausgeschlossen mit Grund) und das Motiv in
   `deploy/bildmotive.json`.

## Stand 24.09.2026

- Mit passendem Poolfoto: Fußball (8 Meldungen, Fußballplätze), Löschgruppe
  Girbelsrath (Feuerwehrhaus Drove, Kreis Düren), Ortsfest (Dorfkirmes),
  Geschwindigkeitskontrolle (Blitzer-Kasten), Einbruch (Polizeimotorrad NRW),
  FC-Fanclub (RheinEnergieStadion).
- Fahrrad-Reparaturstationen: gesichtete Fotos von Stationen in Tübingen, Ulm, Bad Tölz, Bernkastel-Kues und Mainz (Runde 13).
- Alle übrigen Meldungen tragen eigene Fotos.

## V3: Bildklassen (ab 24.09.2026)

Die Stichwortregel ordnete nach dem Akteur zu („Feuerwehr“ → Feuerwehrhaus,
„Polizei“ → Polizeifahrzeug). Das passte nicht: Brandmeldeanlage mit Foto
eines Dachstuhlbrands, Taube im Zaun mit Rettungswagen, Glutnester auf dem
Feld mit einem Brand in Köln-Ehrenfeld. V3 ordnet nach dem **Ereignis**.

1. **Jede recherchierte Meldung trägt eine Bildklasse** (`bildklasse` in
   `inhalte/meldungen/*.json`, im HTML `<meta name="ma:bildklasse">`), z. B.
   `blaulicht.technik.oelspur`, `blaulicht.alarm.rauchmelder`,
   `verkehr.unfall.rettung`, `polizei.einbruch`. Alle Klassen mit ihren Motiven:
   `deploy/bildklassen.json`.
2. **Die Klasse nennt die Motive, die das Ereignis zeigen.** Ohne gesichtetes
   Foto mit diesem Motiv: kein Bild. Die Elternklasse zählt nur mit
   `eltern: true`, also nur, wenn ihr Motiv die Aussage noch zeigt.
3. **Ein Foto trägt höchstens drei Meldungen** (`maxJeFoto`), damit keine Liste
   achtmal dasselbe Bild zeigt.
4. **Motive sind feiner:** `brand` ist aufgeteilt in `gebaeudebrand`,
   `waldbrand`, `feuerwehr-anfahrt` (Bildtext nennt einen Waldbrand, deshalb
   nirgends sonst), dazu `flaechenbrand`, `kleinbrand`, `oelspur`,
   `brandmeldeanlage`, `rauchmelder`, `tueroeffnung`, `tierrettung`,
   `unfallstelle`, `fussgaengerbruecke`.
5. **Meldungen ohne Klasse** laufen über die Stichwortregeln; auch dort gilt
   jetzt Ereignis vor Akteur (Ölspur, Tierrettung, Brandmelder, E-Call stehen
   vor Feuerwehr und Polizei).
6. **Kein Ersatzbild an anderer Stelle:** Das Ressort-Menü zeigt nur das Bild
   der Meldung selbst; ohne Bild steht der Teaser als Text.

Neue Fotos für die Lücken holt der Importer aus den Ereignis-Pools `technik`,
`rettung`, `unfall` und `flaeche` (Pflichtwort im Dateititel). Sie kommen
ungesichtet herein und werden erst nach Sichtung mit Motiv in
`deploy/bildmotive.json` vergeben.

Offen: Bildstufen A/B/C (Aufmacher nur A). Heute gilt bereits: Der Aufmacher
der Startseite nimmt nur eigene oder Quellfotos, nie ein Poolfoto.

## Bildstufen A/B/C (V3, 25.09.2026)

| Stufe | Was | Wo erlaubt |
|---|---|---|
| A | Eigenes Foto vom Ereignis (Originalbild, Quellenmotiv, Foto der Polizei/Feuerwehr) | überall, **einzige Stufe für den Aufmacher** |
| B | Poolfoto mit einem Motiv der eigenen Bildklasse (oder Motivregel); Archiv- und Beispielbilder | Artikel, Listen, Nebenmeldungen der Startbühne |
| C | Poolfoto nur über die Elternklasse, oder schmaler als 800 px | Artikel und Listen, nie in der Startbühne |

- Berechnet in `deploy/lib-symbolbilder.mjs` (`stufeFuer`), am Symbolbild als `data-bildstufe`, gelesen in `lib-artikel.mjs` (`bild.stufe` im Inhaltsindex).
- Aufmacher (`deploy/inhaltsindex.mjs`): jüngste Meldung der letzten 7 Tage mit Stufe A. Gibt es keine, steht die jüngste Meldung als **Text-Aufmacher** ohne Bild (`.front-lead--text`, Entscheidung KBS 25.09.2026). Nie ein Symbolbild an dieser Stelle.
- QA (`qa.yml`) bricht ab, wenn der Aufmacher ein Poolfoto trägt oder ein Text-Aufmacher doch ein Bild hat.
