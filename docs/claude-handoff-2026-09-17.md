# Claude Code Handoff — 17.09.2026

Basis: `docs/editorial-refresh-2026-09-17.md`

## P0/P1 technische Integration

Bitte die redaktionell verifizierten Inhalte technisch in die ausgelieferte `chatgpt-site/` integrieren, ohne die bestehende gute Frontend-Richtung zu resetten.

### Neu anzulegen

1. Blaulichtartikel: Geschwindigkeitskontrolle mit Viertklässlern vom 15./16.09.2026.
2. Blaulichtartikel: Feuerwehr-Einsatz 126/26, eCall in Morschenich vom 14.09.2026.
3. Sport/Vereine: Damen 30 des SC Merzenich Tennis steigen auf.

Texte, Fakten, Quellen und Bildrechtsstatus stehen in `docs/editorial-refresh-2026-09-17.md`.

### Danach zwingend regenerieren / synchronisieren

- `deploy/suche-index.mjs`
- `deploy/thema-prerender.mjs`
- ggf. `latest.json`, Feeds und Ressortarchive, sofern nicht automatisch Teil der vorhandenen Pipeline
- bestehende Seite `/rathaus/faire-woche/` und Terminmodule mit den kommenden Terminen 20.09., 21.09. und 28.09. abgleichen

### Bildlogik

Kein Bild darf allein deshalb als Originalbild gelten, weil es auf Presseportal oder Heimat-Info vorhanden ist. `Bild gefunden` und `Bildrecht verifiziert` bleiben getrennte Gates. Für die Polizeimeldung kann das Presseportal-Bild erst nach Prüfung der konkreten Bildinformationen genutzt werden. Bis dahin verifiziertes Symbolbild.

### Regression

- neue Headlines müssen in `/suche/` auffindbar sein
- neue interne Links dürfen QA nicht brechen
- Sport darf nur Sport-Aufmacher bekommen
- Blaulicht darf keine Sportmeldung als Ressortlead erhalten
- SC-Merzenich-Daten weiter ausschließlich aus `sport-current.json`
- 390 / 430 / 1440 ohne horizontalen Overflow
- Homepage muss `editorial-current.json` als aktuellen Aufmacher verwenden
- Formularstrecke nach der neuen `/api/formular`-Annahme real gegen die ausgelieferte Umgebung prüfen

### Nicht publizieren

Die für 17.09. bei Ratsblick aufgeführten Gemeinderatsthemen bleiben gesperrt, bis eine Primärquelle aus dem Ratsinformationssystem bzw. der Gemeinde geprüft wurde.
