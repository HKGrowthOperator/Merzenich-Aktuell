# Recherche: Quellen, Rohdaten, Meldungen

Stand: 24.09.2026

## Warum über GitHub Actions

Der Container, in dem die Redaktion (Claude) arbeitet, erreicht keine der
Quellen (Netzrichtlinie, HTTP 403). GitHub Actions erreicht sie. Deshalb:

1. `.github/workflows/quellen-abruf.yml` ruft täglich 05:10 UTC (und von Hand)
   `deploy/quellen-abruf.mjs` auf.
2. Das Skript legt je Quelle Liste und Einzelseiten als Rohdaten unter
   `imports/quellen/` ab: Text, Links, Bilder mit alt-Text, Abrufzeit, Status.
   Es veröffentlicht nichts.
3. Meldungen entstehen erst daraus, mit Quellenlink, in
   `inhalte/meldungen/*.json`. `deploy/meldungen.mjs` schreibt die Seiten.

| Quelle | Datei | Stand 24.09. |
|---|---|---|
| Freiwillige Feuerwehr Merzenich, Einsätze | `feuerwehr.json` | 18 Einsätze (109–127/26) |
| Presseportal, Polizei Düren, Suche nach Ort (Merzenich und Ortsteile) und Gesamtliste der Polizei Düren (`blaulicht/nr/8`, seit 26.10.) | `polizei.json` | 38 Mitteilungen 2026 |
| Gemeinde Merzenich, Aktuelles | `gemeinde.json` | erster Lauf: 302, Abruf mit Cookies seit 24.09. |
| Grundschulen (KGS Merzenich, KGS Golzheim) | `schulen.json` | erster Lauf: 302, wie oben |
| Jobbörse der Bundesagentur, 25 km um Merzenich | `jobs.json` | erster Lauf: 403, Fehlertext wird jetzt mitgeschrieben |

## Automatik Feuerwehr (seit 13.10.2026)

`deploy/automatik-feuerwehr.mjs` läuft nach jedem erfolgreichen Quellenabruf
(`.github/workflows/meldungen-automatik.yml`, auch von Hand mit „trocken“).

- **Quellen:** Einsatzseiten von feuerwehr-merzenich.de (`feuerwehr.json`:
  Nummer, Ort - Straße, Stichwort / Kategorie, Alarmzeit, Bericht, Kräfte) und
  die Feuerwehr-Beiträge in der Heimat-Info-App (`heimatinfo.json`: Einsatz-Nr,
  Datum, Stichwort, Meldung, Einsatzort, Kräfte; erscheinen meist früher).
  Beide werden je Einsatznummer zusammengeführt.
- **Nur Neues:** Nummer in keiner Meldung, keinem Artikel und keiner Seite
  (`inhalte/meldungen`, `site-source/content/artikel`, `chatgpt-site/blaulicht`),
  nicht in `deploy/automatik-stand.json`, Alarm höchstens 14 Tage alt.
- **Text:** nur aus den Feldern der Quelle, dazu höchstens drei Sätze des
  Feuerwehrberichts als Zitat („Die Feuerwehr schreibt dazu: …“), ohne Sätze mit
  Ziffern (Hausnummern, Alter). Widersprechen sich die Quellen bei Datum oder
  Ort, nennt der Text beide (Beispiel 136/26: 02.10. laut Einsatzseite,
  03.10. laut Heimat-Info).
- **Übersprungen** (steht in der Zusammenfassung des Laufs): Einsatzort ohne
  Ortsteil (Autobahn, Landstraße), kein Bericht, keine passende Bildklasse,
  unter 80 Wörtern.
- **Veröffentlicht nichts.** Die Meldungen kommen über den Abgleich als Entwurf
  in die Freigaben. Wer eine Meldung nicht will, legt sie in WordPress in den
  Papierkorb; sie kommt nicht wieder.
- Test mit echten Rohdaten: `node qa/automatik-feuerwehr-test.mjs`.

**Quellenstand 13.10.2026:** Polizei/Presseportal liefert seit 10.10. nur
„Security Check“ (HTTP 503), letzte lesbare Fassung im Commit 0b88a926.
`gemeinde.json` enthält keine Daten (Datum) und keine Einzelmeldungen.
Datierte Gemeinde-Beiträge kommen nur über Heimat-Info; sie werden nicht
automatisch übernommen, sondern stehen in der Zusammenfassung des Laufs.

## Automatik Umkreis (seit 26.10.2026)

`deploy/automatik-umkreis.mjs` läuft im selben Workflow wie die Feuerwehr-Automatik
(`.github/workflows/meldungen-automatik.yml`, auch von Hand mit „trocken“).

- **Quelle:** die Gesamtliste der Polizei Düren im Presseportal (`polizei.json`,
  `blaulicht/nr/8`) und die Ortslisten. Jede Listenzeile liefert Datum, Dienststelle,
  Schlagzeile und den Vorspann, der mit dem Ort beginnt („Niederzier (ots) - …“).
- **Ergebnis:** Einträge für den Block „Direkter Umkreis“ der Startseite
  (`inhalte/umkreis/<datum>-polizei-automatik.json`, Schema wie `deploy/umkreis.mjs`):
  Ort (Düren, Niederzier, Nörvenich, Elsdorf, Kerpen oder „Kreis Düren“ mit dem Ort
  vor der Schlagzeile), amtliche Schlagzeile ohne „POL-DN:“, Datum, Link zur
  Einzelmeldung. Kein eigener Text, kein Vorspann, keine Namen.
- **Nur Umkreis:** Meldungen mit Merzenich, Golzheim, Girbelsrath, Morschenich oder
  Bürgewald im Vorspann werden nie angelegt; sie stehen in der Zusammenfassung des
  Laufs für den Redaktionslauf (eigene Meldung mit Primärquelle).
- **Nur Neues:** URL in keiner Datei unter `inhalte/umkreis/`, nicht als Quelle einer
  Meldung, nicht in `deploy/automatik-stand.json`; höchstens drei Tage alt.
- **Übersprungen:** andere Dienststelle (etwa Polizei Aachen in einer Ortsliste),
  Ort außerhalb des Kreises Düren, kein Vorspann, kein Link.
- **Sichtbarkeit:** Der Umkreis-Block erscheint auf merzenich-aktuell.de ohne
  Freigabe (das Theme liest die Startseite stündlich aus dem Repository). Darum
  nur amtliche Schlagzeile und Link. Ein unerwünschter Eintrag wird in der JSON-Datei
  gelöscht; die URL bleibt im Stand und kommt nicht wieder.
- Test mit echten Rohdaten: `node qa/automatik-umkreis-test.mjs`.

## Regeln für Meldungen

- Nur was in der Quelle steht. Keine Verallgemeinerungen, keine Vermutungen.
  Widersprechen sich Quellen, stehen beide Angaben mit Quelle im Text.
- Datum: bei Einsätzen die Alarmierung laut Feuerwehr, bei Pressemitteilungen
  deren Veröffentlichung.
- Ortsteil nur, wenn die Quelle ihn nennt oder die Straße eindeutig dort liegt.
- Jede Meldung hat eine Bildklasse (`docs/BILD-MOTIVREGELN.md`, V3).
- Bilder der Feuerwehr-Website nur mit Freigabe der Feuerwehr: Die Seite nennt
  keine Lizenz. Presseportal-Bilder dürfen redaktionell verwendet werden, wenn
  der Rechteinhaber nichts anderes angibt.

## Ein Einsatz, eine Meldung

`qa/pruefung.mjs` bricht ab, wenn dieselbe Einsatznummer (erster Punkt im
Kasten „Das Wichtigste in Kürze“: „Einsatz(nummer) NNN/JJ“) auf zwei Seiten
steht. Vor einer neuen Einsatzmeldung deshalb prüfen:

    grep -rl "Einsatz\(nummer\)\? 128/26" chatgpt-site/blaulicht/

Zwei Redaktionswege schreiben Meldungen: `inhalte/meldungen/*.json`
(→ `deploy/meldungen.mjs`, in der CI-Kette) und `site-source/content/artikel`
(→ `site-source/build.mjs`, lokal). Doppelte werden zusammengeführt, die alte
Adresse per `chatgpt-site/_redirects` umgeleitet und
`node deploy/coolify/erzeuge-nginx-conf.mjs` neu erzeugt.

## Fotos der Polizei (Presseportal)

`quellen-abruf.mjs` lädt zu jeder Meldung aus `inhalte/meldungen/`, deren Quelle eine Presseportal-Mitteilung ist, die Fotos der Mitteilung (Fassung „highlight“) nach `imports/quellen/bilder/<pm>-<n>.jpg`; `bilder/index.json` nennt Quelle und alt-Text. Der Container der Redaktion erreicht `cache.pressmailing.net` nicht, deshalb nur über CI. Auf die Seite kommt ein Foto erst nach Sichtung (Kennzeichen, Gesichter), Credit „Polizei Düren / Presseportal“.

QA sperrt doppelte Meldungen zur selben Pressemitteilung (erster Link der Quellenbox) wie doppelte Einsatznummern.
