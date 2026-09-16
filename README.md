# Merzenich Aktuell

Zentrales Repository für **Merzenich Aktuell** – lokale Nachrichtenplattform für Merzenich, Golzheim, Girbelsrath, Morschenich/Bürgewald und relevante Umgebung.

## Aktueller dokumentierter Lieferstand

- Stand: 14. September 2026
- WordPress Theme: 20.2.0
- Core Plugin: 1.0.0
- Zielsystem: WordPress
- Öffentliche Vorschau/Referenz: https://merzenich-aktuell-v19.luiskummer.chatgpt.site

## Architektur

Das Theme enthält Präsentation und Templates. Dauerhafte Funktionen und Datenmodelle liegen im Core-Plugin. Dazu gehören u. a. Nachrichten, Orte, Veranstaltungen, Vereine/Betriebe, Immobilien, Stellen, Trauer- und Familienanzeigen, Werbung, Wetter, Kommentare, Sport, Quellenradar, Formulare sowie KI-/Human-Review-Metadaten.

## Design-/Produktgrundsätze

- keine erneuten Total-Redesigns ohne Vorher/Nachher-QA
- heller, weißer Header; Sticky direkt an `top: 0`; dünne dunkle Bottom-Border
- Desktop: Service links, News dominant in der Mitte, sparsame Werbung rechts
- Mobile: News zuerst, danach Service und Werbung
- aktuelle Hero-/Featured-Logik; alte Stories laufen automatisch aus
- echte lokale Inhalte, keine Fake-News und keine erfundenen Marktangebote
- echte bzw. rechtlich nutzbare Bilder mit Credit/Quelle
- WordPress ist das produktive CMS; die ChatGPT-Site ist nur Vorschau/Referenz

## Repository-Struktur

- `docs/` – Projektstand, Anforderungen, Installation und Lieferdokumentation
- `imports/` – WordPress-Importdateien, sobald vollständig als Datei verfügbar
- `wordpress/theme/` – Theme-Quellcode, sobald aus dem bestehenden Lieferpaket eingebracht
- `wordpress/plugin/` – Core-Plugin-Quellcode, sobald aus dem bestehenden Lieferpaket eingebracht

## Wichtiger Hinweis zum aktuellen Push

Das Repository war beim Start leer. Die vorhandenen Projektunterlagen werden hier zentralisiert. Die bereits dokumentierten Binärpakete `merzenich-aktuell-theme.zip` und `merzenich-aktuell-core.zip` sind in den bisherigen Unterlagen referenziert, standen in dieser Sitzung aber nicht als direkt übertragbare Binärdateien zur Verfügung. Es werden **keine leeren oder erfundenen ZIP-Dateien** committed.

Siehe `docs/ARTIFACT-STATUS.md` für den genauen Stand.
