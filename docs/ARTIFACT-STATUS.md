# Artefakt-Status

Stand: 16. September 2026

Dieses Repository ist ab jetzt die zentrale technische Quelle für Merzenich Aktuell.

## Im Repository vorhanden

### Frontend

- `PROJECT.md`
- `index.html`
- `styles.css`
- `app.js`
- `content.json`

### WordPress-Lieferung

- `wordpress-delivery/merzenich-aktuell-theme.zip`
- `wordpress-delivery/merzenich-aktuell-core.zip`
- `wordpress-delivery/merzenich-aktuell-import.xml`

### Dokumentation / QA

- `README.md`
- `docs/README-INSTALLATION.md`
- `docs/WORDPRESS-LIEFERUNG.md`
- `docs/PROJECT-REQUIREMENTS.md`
- `docs/RECOVERY-BUILD-STATUS.md`
- `docs/REPO-SYNC-STATUS.md`
- `.github/workflows/qa.yml`
- vorbereitete `qa/`-Struktur

## Noch offen

Die installierbaren ZIP-Pakete sind nun physisch vorhanden. Noch nicht als normal versionierbarer Quellcode im Repository vorhanden sind die **entpackten Original-Quellordner** aus diesen Paketen:

```text
wordpress/theme/merzenich-aktuell/
wordpress/plugin/merzenich-aktuell-core/
```

Solange diese Quellordner fehlen, bleiben die ZIPs zwar installierbare Lieferartefakte, aber Änderungen am WordPress-Code sind nicht sauber diff-/reviewbar und die Pakete nicht vollständig reproduzierbar aus Git.

Weitere noch offene bzw. zu verifizierenende Artefakte:

- `docs/QA-WORDPRESS.json`, sofern aus dem ursprünglichen Recovery-Build vorhanden
- Recovery-Renderings/Screenshots (`qa/before`, `qa/after`)
- vollständige Bildrechte-/Content-Refresh-/Sport-Prüfdokumente, sofern Originale vorhanden
- Prüfsummen des ursprünglichen Lieferstands, sofern Originale vorhanden

## Automatische QA

`.github/workflows/qa.yml` prüft bei Push/PR mindestens:

- JavaScript-Syntax von `app.js`
- JSON-Syntax von `content.json`
- XML-Syntax des WordPress-Imports
- Vorhandensein der Theme-/Core-ZIPs
- Entpackbarkeit beider ZIPs
- PHP-Syntax aller PHP-Dateien in beiden Paketen
- auffällige Placeholder-Marker als Warnsignal

## Source of truth

Neue Änderungen gehören ausschließlich in dieses Repository. Keine getrennten `v21`, `v22`, `recovery-final` oder nicht synchronisierten Parallelprojekte mehr.

Die öffentliche ChatGPT-Site bleibt Vorschau/Referenz und ist kein Ersatz für den Git-/WordPress-Quellstand.
