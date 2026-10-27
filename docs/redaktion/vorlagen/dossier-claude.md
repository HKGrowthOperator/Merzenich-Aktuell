<!-- Vorlage für docs/redaktion/dossiers/<YYYY-MM-DD>-claude-<lauf>.md. Spitze Klammern ersetzen, Kommentare löschen. Alle Zahlen zählen, nicht schätzen. Keine Privatpersonen, keine Zugangsdaten. -->
# Merzenich Aktuell – <Morgenlauf|Nachmittagslauf> (Claude), <TT. Monat JJJJ>

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md`. Routinestart <HH:MM>, Bearbeitung <HH:MM> bis <HH:MM> Uhr (Europe/Berlin). Vergleichsbasis: <Links auf letzten Claude-Lauf und seither neue ChatGPT-Dossiers>. main vor dem Lauf: `<hash>`.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | <n> | <davon standard/vertiefung/kurzmeldung> |
| Aktualisierte oder korrigierte Bestandsmeldungen | <n> | |
| Neue Termine (alle mit `entwurf: true`) | <n> | |
| Umkreis-Einträge | <n> | ohne Automatik |
| Zwei-Quellen-Quote | <n>/<n> | |
| Sportstand | <geändert/unverändert> | <Quelle, Zeitpunkt> |
| Bilder gefunden / geprüft / integriert | <n> / <n> / <n> | |
| Kette / QA / redaktion-lauf | <zweimal, --check unverändert, 0 Fehler, n Hinweise> | |
| Dauer des Laufs | <Minuten> | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| <ID> | <Titel oder Korrektur mit altem und neuem Wortlaut> | `<inhalte/…json>` → `</ressort/slug/>` | <welche Originale gelesen, Datum> |

Quellenausnahmen: <Meldung, Begründung; oder „keine“>

## 2. Sofort sichtbar
<!-- Spielstand, Umkreis und jede Korrektur oder jedes Update an bereits veröffentlichten Meldungen und Terminen: was, Quelle, Zeitpunkt. Sonst „nichts“. -->

## 3. Bewusst nicht umgesetzt

- <ID oder Thema>: <Grund> (<Status im Register>)

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| Rohdaten `imports/quellen/` (<Abrufzeit>) | Vergleich mit `<hash>` | <neu je Quelle> |
| Polizei Düren RSS | | |
| Kreis Düren Presse | | |
| Heimat-Info | | |
| Ratsinformationssystem | `termine.ics`, Vorlagen | |
| Feuerwehr | | |
| <weitere nach QUELLEN.md> | | |

Sperren und Umwege: <…>

## 5. Sport

<Ergebnis, offenes Spiel, nächstes Spiel, Tabelle mit Datenstand; oder „unverändert“ und offene Endstände>

## 6. Termine der nächsten 14 Tage (Veränderungen)

<neu, geändert, abgesagt; Bestand nur als Liste>

## 7. QA, Commit, Abgleich

Commits `<hash>` (Inhalte) und `<hash>` (Dokumentation). Konflikte und Lösung: <…>. Abgleich: Anstoß <Antwort>, wp-cron <HTTP>, Nachweis in der Import-XML <ja/nein>. Freigaben im Backend: <geprüft/nicht geprüft, Grund>.

## 8. Bilder

<gefunden/geprüft/integriert, Lizenz, Sperren; oder „keine Bildarbeit in diesem Lauf“>

## 9. Offene Punkte für den nächsten Lauf

1. <…>

<!-- Bei Nullfund: Abschnitt 1 „keine“, Abschnitt 0 „Neue Meldungen | 0 | Nullfund“, Abschnitt 4 vollständig. -->
