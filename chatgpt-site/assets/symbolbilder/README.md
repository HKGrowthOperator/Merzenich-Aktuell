# Symbolbilder

Hier liegen die thematischen Ersatzmotive. Sie erscheinen nur dann, wenn eine
Meldung kein eigenes Bild hat, und sie tragen immer sichtbar die Kennzeichnung
"Symbolbild" mit Bildnachweis.

## Ablage

    chatgpt-site/assets/symbolbilder/<kategorie>/<datei>.webp
    chatgpt-site/assets/symbolbilder/<kategorie>/lizenzen.json

Kategorien: polizei, feuerwehr, verkehr, sport, vereine, gemeinde, leben,
termine, wirtschaft, jobs, immobilien, familie, trauer.

## lizenzen.json

Je Bilddatei ein Eintrag. Ohne vollstaendigen Eintrag wird ein Bild nicht
ausgeliefert, auch wenn die Datei im Ordner liegt. Ein Bild ohne Nachweis ist ein
Rechtsrisiko, und ein Alt-Text, den niemand geschrieben hat, waere eine
Behauptung ueber ein Bild, das die Redaktion nicht gesehen hat.

    {
      "platz-01.webp": {
        "alt": "Leerer Fussballplatz bei Flutlicht",
        "credit": "Symbolbild · KI-generiert fuer Merzenich Aktuell",
        "lizenz": "",
        "quelle": ""
      }
    }

Pflicht sind `alt` und `credit`. `lizenz` und `quelle` sind Pflicht, sobald das
Motiv von einer fremden Quelle stammt.

## Regeln fuer die Motive

1. Keine erkennbaren Personen, Kennzeichen, Hausnummern oder Firmenschilder.
2. Kein Vereinslogo als Motiv. Der Verein erscheint als kleine Textmarke.
3. Keine Ortsansicht der Gemeinde fuer Blaulicht und Verkehr. Ein Dorffoto unter
   einer Einsatzmeldung behauptet still einen Tatort.
4. Kein Foto, das zu einer anderen Meldung gehoert.
5. Je Kategorie moeglichst 20 bis 30 Motive. Der Verteiler vergibt sie der Reihe
   nach, deshalb bestimmt die Poolgroesse direkt, wie oft sich ein Motiv
   wiederholt: bei n Meldungen und p Motiven hoechstens aufgerundet n/p mal.

## Vergabe

Die Zuordnung macht `deploy/lib-symbolbilder.mjs`. Sie ist fest je Meldung, also
auf Startseite, Ressortseite, Suchseite und Artikelseite dasselbe Motiv, und
gleichzeitig ueber die Meldungen einer Kategorie gleichmaessig verteilt. Keine
Zufallsauswahl, kein Wechsel beim Neuladen.
