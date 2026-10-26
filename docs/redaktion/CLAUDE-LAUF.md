# Claude-Redaktionslauf: Qualitätsanforderungen

Stand: 26.10.2026. Gilt für jeden Lauf der KI-Redaktion (Claude), zweimal täglich (morgens und nachmittags, Europe/Berlin), zusätzlich zu `README.md`, `PROJECT.md` und `REDAKTIONELLER-STANDARD-V2.md`.

## Texte

- **Standard ist der Regelfall.** Eine Meldung erscheint als `standard` (mindestens 300 Wörter, 4 Absätze, 4 belegte Fakten), sobald die Quellenlage das trägt. `kurzmeldung` nur für Termine, Fristen und Servicehinweise, deren Quellen nicht mehr hergeben. Keine Textstreckung: Länge entsteht aus zusätzlichen belegten Fakten, Vorgeschichte, Einordnung für den Ortsteil und dem Ausblick („Wie geht es weiter?“), nie aus Füllsätzen.
- **Redaktionell schreiben:** eigener Einstieg mit Nachrichtenwert, konkrete Zahlen, Orte und Zeiten, Zitate nur wörtlich aus der Quelle und als solche gekennzeichnet, letzter Absatz mit Ausblick oder Service. Keine Pressemitteilung nacherzählen, keine Werbesprache.
- Namen von Privatpersonen nur mit Einwilligung; Amts- und Funktionsträger sowie öffentlich auftretende Personen dürfen genannt werden.

## Quellen und Fakten

- **Mindestens zwei Quellen je Meldung** (`quelle` plus `weitereQuellen`), jede mit Name, https-Link und `stand`. Ausnahme nur bei reinen Terminhinweisen mit einer amtlichen Quelle; dann steht das ausdrücklich im `hinweis` des Pakets.
- **Jede Zahl, jedes Datum, jeder Name wird an der Primärquelle gelesen**, nicht aus Listen, Teasern, Suchergebnissen oder einem früheren Dossier übernommen. Was sich nicht bestätigen lässt, kommt nicht in den Text. Widersprüche zwischen Quellen werden im Text benannt.
- Sportergebnisse nur aus Klartext (FUSSBALL.DE-Datenberichte, fupa mit „Abpfiff“, click-TT); Tabellen mit Datenstand, nie aus einem alten Kopf. Ergebnis, offenes Spiel, nächstes Spiel und Tabelle immer gemeinsam aktualisieren (`chatgpt-site/api/sport-current.json`).
- Vor dem Schreiben Bestand prüfen (`chatgpt-site/api/inhalte.json`, `inhalte/`, `docs/redaktion/register.json`): Neues zu einem bestehenden Vorgang ist ein Update des bestehenden Artikels, kein zweiter Artikel.

## Ablauf je Lauf

1. `git fetch`/`merge origin/main`; `PROJECT.md`, `docs/redaktion/README.md`, `latest.md` und die jüngsten Dossiers lesen. Statt das ganze Register zu lesen: `node deploy/register-pflege.mjs` ausführen und `docs/redaktion/offen.md` lesen (offene Themen nach Priorität, je Thema der mögliche Bestand als Dublettenwarnung); `register.json` nur für die Themen öffnen, die bearbeitet werden.
2. Umkreis-Einträge der Polizei Düren legt die Automatik an (`deploy/automatik-umkreis.mjs` nach jedem Quellenabruf); im Lauf nur noch Polizeimeldungen mit Merzenich oder Ortsteil im Vorspann als eigene Meldung prüfen (Zusammenfassung des Workflows „Meldungen-Automatik“ oder `node deploy/automatik-umkreis.mjs --trocken`). Rohdaten `imports/quellen/*.json` plus eigene Live-Abrufe (Heimat-Info, Ratsinformationssystem, Amtsblatt-PDF, Kreis Düren, Polizei Düren, Feuerwehr, Neuland Hambach, Bezirksregierung, Vereine, fupa/FUSSBALL.DE/click-TT, Autobahn GmbH, go.Rheinland). Alle zwölf Ressorts, Kernorte zuerst.
3. Meldungen, Termine und Umkreis als neue JSON-Pakete; bestehende Artikel bei Updates ergänzen; neue Bildklassen nur in `deploy/bildklassen.json`.
4. `node deploy/kette.mjs` zweimal, `node deploy/kette.mjs --check`, `node qa/pruefung.mjs` (0 Fehler), dann Commit und Push auf `main`.
5. Dossier unter `docs/redaktion/dossiers/`, `latest.md` und `register.json` nachziehen (Artikelpfad, Commit, Status `zur_abnahme`); danach `node deploy/register-pflege.mjs` (schreibt `offen.md` neu, prüft Pfade und Pflichtfelder); zweiter Commit.
6. Abgleich anstoßen (`https://merzenich-aktuell.de/?ma_api=abgleich-anstoss`, danach `wp-cron.php`); Freigaben im Backend prüfen, sobald ein Zugang hinterlegt ist. Veröffentlicht wird nur von der Redaktion im Backend.
7. Kurzbericht an die Redaktion: Zahl der neuen Meldungen, Updates, Termine, offene Fragen, Quellenlücken, Commit.

## Was nicht passiert

Keine Veröffentlichung, kein Einschalten von „Sofort veröffentlichen“, keine Bilder ohne geprüfte Lizenz, keine Zugangsdaten im Repository, keine erfundenen Zeitstempel oder Adressen.
