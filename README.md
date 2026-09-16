# Merzenich Aktuell

Zentrales Repository für **Merzenich Aktuell** – lokale Nachrichtenplattform für Merzenich, Golzheim, Girbelsrath, Morschenich/Bürgewald und relevante Umgebung.

## Source of Truth

- Repository: `HKGrowthOperator/Merzenich-Aktuell`
- Branch: `main`
- Öffentliche historische Referenz: https://merzenich-aktuell-v19.luiskummer.chatgpt.site
- Dieses Repository ist ab 16.09.2026 der zentrale technische Arbeitsstand für Frontend, Dokumentation und WordPress-Lieferung.

## Arbeitsregel für zukünftige Chats

Immer zuerst diesen Stand laden und gezielt ändern. **Kein neues Parallelprojekt, kein Redesign-Fork, keine v21/v22/v23-Kopie.**

In einem neuen Chat genügt:

> Arbeite weiter an `HKGrowthOperator/Merzenich-Aktuell`, Branch `main`. Lies zuerst `PROJECT.md` und ändere ausschließlich diesen Stand.

## Repository-Struktur

- Root: dauerhaft editierbares Frontend / visuelle Vorschau
- `docs/`: Projektstand, Anforderungen, Installation, QA, Content- und Bilddokumentation
- `wordpress-delivery/`: eingefrorene WordPress-ZIPs und Importdatei
- `qa/`: Screenshots und visuelle Prüfungen
- `archive/`: historische Gesamtpakete, sofern sinnvoll

## Design-/Produktgrundsätze

- keine erneuten Total-Redesigns ohne Vorher/Nachher-QA
- heller, weißer Header; Sticky direkt an `top: 0`; dünne dunkle Bottom-Border
- Desktop: Service links, News dominant in der Mitte, sparsame Werbung rechts
- Mobile: News zuerst, danach Service und Werbung
- aktuelle Hero-/Featured-Logik; alte Stories laufen automatisch aus
- echte lokale Inhalte, keine Fake-News und keine erfundenen Marktangebote
- echte bzw. rechtlich nutzbare Bilder mit Credit/Quelle
- WordPress-Lieferung bleibt technisch getrennt vom Frontend

## Frontend-Datenlogik

`content.json` ist die zentrale Datenquelle der aktuellen Frontend-Preview für Artikel, Veranstaltungen, Sport und Werbeslots.

`app.js` enthält Hero-Priorisierung, Event-Filter, Wetterabruf mit Cache/Fallback, Uhrzeit/Datum, Suche und Ressort-Routing.

## Statusbegriffe

- `LOCAL READY`: Source vorhanden, noch nicht öffentlich deployed
- `STAGING READY`: auf echter Hosting-Stagingumgebung geprüft
- `LIVE READY`: alle Gates bestanden und Deployment möglich
- `LIVE DEPLOYED`: öffentliche URL geprüft

Nur `LIVE DEPLOYED` bedeutet öffentlich fertig.
