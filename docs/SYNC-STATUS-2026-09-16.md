# Sync-Status – 16.09.2026

## Repository jetzt zentral

`HKGrowthOperator/Merzenich-Aktuell` ist ab jetzt der technische Source of Truth.

## Aktuelles Frontend im Repository

Parallel zur Dokumentationsmigration wurde bereits ein aktueller statischer Frontend-Stand in `main` eingebracht:

- `PROJECT.md`
- `index.html`
- `styles.css`
- `app.js`

Dieser Stand setzt zentrale Recovery-Vorgaben um, darunter:

- weißer Sticky-Header direkt an `top: 0`
- dünne dunkle Bottom-Border
- kleinere Wortmarke
- mittige Suche
- Wetter / Uhrzeit / Datum rechts
- eine Ressortzeile + „Mehr“
- Desktop Service / News / rechte Zusatzspalte
- News-first Mobile-Reihenfolge
- dynamische Hero-Bewertung mit 7-Tage-Grenze
- rot umrandete „Mehr lesen“-Buttons
- Kommentaranzeige und Artikel-Kommentarbereich in der Preview
- Eventfilterung nach Ablaufdatum
- Open-Meteo-basierte Wetterlogik mit lokalem Cache

Dieser Frontend-Stand darf nicht durch ältere V18/V19-Dateien überschrieben werden. Weitere Arbeit erfolgt gezielt auf diesem Source.

## Dokumentation ebenfalls übernommen

- `README.md`
- `docs/README-INSTALLATION.md`
- `docs/WORDPRESS-LIEFERUNG.md`
- `docs/PROJECT-REQUIREMENTS.md`
- `docs/RECOVERY-STATUS-2026-09-14.md`
- `docs/ARTIFACT-STATUS.md`
- `wordpress/theme/README.md`
- `wordpress/plugin/README.md`
- `imports/README.md`
- `qa/README.md`

## Originalartefakte noch nicht als Git-Dateien verfügbar

In den bisherigen Projektunterlagen sind folgende Dateien nachweislich dokumentiert, aber in der aktuellen Sitzung nicht als direkt übertragbare Originaldatei/Bytequelle verfügbar:

- `merzenich-aktuell-theme.zip` – Theme 20.2.0
- `merzenich-aktuell-core.zip` – Core 1.0.0
- vollständiger Theme-Quellordner
- vollständiger Core-Plugin-Quellordner
- vollständige aktuelle `merzenich-aktuell-import.xml`
- `docs/QA-WORDPRESS.json`
- originale Recovery-Screenshots
- originale Bilddateien

Diese Dateien werden nicht als Dummy oder aus Dokumentation rekonstruiert. Sobald die Originalartefakte verfügbar sind, werden sie in die vorgesehene Repo-Struktur übernommen.

## Regel ab jetzt

Keine unverbundenen Parallel-Builds mehr. Frontend, WordPress-Lieferstand, Imports und QA werden in diesem Repository nachvollziehbar versioniert.
