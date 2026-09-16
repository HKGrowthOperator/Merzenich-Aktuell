# Artefakt-Status

Stand: 16. September 2026

Dieses Repository wurde aus einem zuvor leeren GitHub-Repository initialisiert.

## Im Repository vorhanden

- `README.md`
- `docs/README-INSTALLATION.md`
- `docs/WORDPRESS-LIEFERUNG.md`
- `docs/PROJECT-REQUIREMENTS.md`
- `docs/ARTIFACT-STATUS.md`

## In bisherigen Projektunterlagen nachweislich referenziert

Folgende Artefakte wurden im bisherigen Merzenich-Aktuell-Projekt erzeugt bzw. dokumentiert:

- `merzenich-aktuell-theme.zip` – Theme 20.2.0
- `merzenich-aktuell-core.zip` – Core 1.0.0
- `merzenich-aktuell-import.xml`
- `README-INSTALLATION.md`
- `WORDPRESS-LIEFERUNG.md`
- `docs/QA-WORDPRESS.json`
- Sport-Datenstand 14.09.2026
- Content-Refresh-Dokumentation
- Bildkandidaten & Rechte
- PASS/FAIL-QA-Report
- SHA-256-Prüfsummen

## Noch nicht in Git übernommen

Die folgenden Artefakte stehen in dieser Sitzung nicht als direkt übertragbare Datei/Byte-Quelle zur Verfügung und wurden deshalb **nicht fingiert oder leer angelegt**:

- `merzenich-aktuell-theme.zip`
- `merzenich-aktuell-core.zip`
- vollständiger Theme-Quellordner
- vollständiger Core-Plugin-Quellordner
- vollständige `merzenich-aktuell-import.xml`
- `docs/QA-WORDPRESS.json`
- Renderings/Screenshots des Recovery-Builds
- Bilddateien aus dem Recovery-Build

Sobald diese Originaldateien als echte Dateien bereitstehen, gehören sie in dieses Repository bzw. bei Binärpaketen gegebenenfalls in einen Release/Artifact-Workflow.

## Source of truth

Ab jetzt soll dieses Repository die zentrale technische Quelle für Merzenich Aktuell werden. Neue Änderungen sollen nicht mehr in getrennten, nicht synchronisierten Parallelständen leben.

Empfohlene Zielstruktur:

```text
wordpress/
  theme/
    merzenich-aktuell/
  plugin/
    merzenich-aktuell-core/
imports/
  merzenich-aktuell-import.xml
docs/
qa/
```

Die öffentliche ChatGPT-Site bleibt Vorschau/Referenz und darf nicht als Ersatz für den Git-/WordPress-Quellstand behandelt werden.
