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
