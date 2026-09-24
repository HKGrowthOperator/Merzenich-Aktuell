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
| Presseportal, Polizei Düren, Suche nach Ort (Merzenich und Ortsteile) | `polizei.json` | 38 Mitteilungen 2026 |
| Gemeinde Merzenich, Aktuelles | `gemeinde.json` | erster Lauf: 302, Abruf mit Cookies seit 24.09. |
| Grundschulen (KGS Merzenich, KGS Golzheim) | `schulen.json` | erster Lauf: 302, wie oben |
| Jobbörse der Bundesagentur, 25 km um Merzenich | `jobs.json` | erster Lauf: 403, Fehlertext wird jetzt mitgeschrieben |

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
