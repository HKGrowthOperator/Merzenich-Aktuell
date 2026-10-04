# Redaktioneller Standard V2

Stand: 04.10.2026

## Ziel

Merzenich Aktuell soll nicht aus gleichfoermigen 1-Minuten-Zusammenfassungen bestehen. Eine Quelle ist der Anlass fuer eine Meldung, nicht automatisch der fertige Artikel. Vor dem Schreiben wird geprueft, welcher Kontext fuer Leserinnen und Leser vor Ort wirklich relevant ist.

Grundsatz: **Research first, then write. Keine Textstreckung ohne neue, belegte Information.**

## Artikeltypen und Mindestumfang

| Typ | Mindestumfang | Einsatz |
| --- | ---: | --- |
| `kurzmeldung` | 80 Woerter, 2 Absaetze, 2 Fakten | Fristen, kurze Termine, knappe Einsatz- oder Servicemeldungen |
| `standard` | 300 Woerter, 4 Absaetze, 4 Fakten | normale Lokalnachricht, Spielvorschau, Vereins-, Wirtschafts- oder Rathausmeldung |
| `vertiefung` | 450 Woerter, 5 Absaetze, 5 Fakten | wichtiges lokales Vorhaben, groessere politische oder wirtschaftliche Entwicklung |
| `hintergrund` | 700 Woerter, 7 Absaetze, 5 Fakten | Dossier, Einordnung, laengerer Hintergrund |

Die Lesezeit wird aus der tatsaechlichen Wortzahl berechnet. Sie wird nicht manuell auf einen hoeheren Wert gesetzt.

## Redaktionelle Pflichtfragen

Bei `standard`, `vertiefung` und `hintergrund` muss die Recherche vor dem Schreiben mindestens diese Ebenen pruefen:

1. Was ist neu und was ist der konkrete Nachrichtenwert?
2. Welche belastbaren Zahlen, Termine, Beteiligten oder Resultate gibt es?
3. Was ging dem Ereignis voraus?
4. Warum ist das fuer Merzenich beziehungsweise den betroffenen Ortsteil relevant?
5. Gibt es eine zweite belastbare Quelle oder einen frueheren belegten Datenstand, der Kontext liefert?
6. Was passiert als Naechstes?
7. Welche Serviceinformation hilft Leserinnen und Lesern wirklich?

Nicht jede Frage muss einen eigenen Absatz bekommen. Nicht belegte Informationen werden nicht ergaenzt.

## Quellen

- `quelle` bleibt die primaere Originalquelle.
- Weitere belastbare Quellen kommen in `weitereQuellen`.
- Jede Quelle braucht `name`, `url` und `stand`.
- Pressemitteilungen, Vereinsseiten und Verbandsdaten duerfen Ausgangspunkt sein; der Artikel soll jedoch, wenn moeglich, durch weitere Originaldaten, fruehere Entscheidungen oder offizielle Statistiken eingeordnet werden.
- Keine fremden Texte kopieren. Fakten werden redaktionell neu formuliert.
- Widerspruechliche Angaben werden benannt statt still geglaettet.

## Paketformat

Neue oder redaktionell ueberarbeitete JSON-Pakete unter `inhalte/meldungen/` tragen:

```json
{
  "redaktionsstandard": 2,
  "meldungen": [
    {
      "artikeltyp": "standard"
    }
  ]
}
```

Der Generator `deploy/meldungen.mjs` bricht den Build ab, wenn ein V2-Artikel seinen Mindestumfang, seine Mindestzahl an Absaetzen oder belegten Fakten unterschreitet.

## Schreibstil

- Nachricht und lokaler Nutzen zuerst, kein langer Vorlauf.
- Kurze, konkrete Saetze; keine Werbesprache.
- Zahlen und Termine moeglichst konkret nennen.
- Keine Wiederholung von Titel und Vorspann im ersten Absatz.
- Kontext erklaeren, aber nicht kuenstlich aufblasen.
- Der letzte Teil beantwortet bei normalen Artikeln nach Moeglichkeit: **Wie geht es weiter?**
