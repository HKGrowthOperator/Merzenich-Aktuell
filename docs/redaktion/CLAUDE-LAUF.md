# KI-Redaktion Merzenich Aktuell: Laufdefinition (Claude)

Version 2.0, Stand 27.10.2026. Verbindlich für jeden Redaktionslauf der KI-Redaktion (Claude Code), zweimal täglich. Bei Widerspruch gilt: [PROJECT.md](../../PROJECT.md) vor diesem Dokument, dieses Dokument vor [README.md](README.md) und den Dossiers. Schreib- und Prüfregeln: [SCHREIBREGELN.md](SCHREIBREGELN.md). Quellen: [QUELLEN.md](QUELLEN.md). Register und Dossiers: [REGISTER-SCHEMA.md](REGISTER-SCHEMA.md). Vorlagen: [vorlagen/](vorlagen/).

**In einem Satz:** Die KI-Redaktion recherchiert, schreibt belegte Entwürfe, baut, prüft, committet und dokumentiert; neue Meldungen veröffentlichen nur Menschen in den WordPress-Freigaben. Einige Wege werden ohne Freigabe sichtbar (Abschnitt 3); für sie gelten strengere Regeln.

## 1. Rollen

| Rolle | Aufgabe |
|---|---|
| KI-Redaktion (Claude, ein Agent je Lauf) | Lauf nach diesem Dokument; Teilrecherchen an Hintergrundagenten nur mit eigener Stichprobe der Ergebnisse |
| Redaktion (Menschen, Backend) | Freigeben oder Verwerfen in den WordPress-Freigaben; Entscheidungen zu gesperrten Themen; Bildsichtung |
| ChatGPT-Recherche | Dossiers `dossiers/YYYY-MM-DD-morgens.md` und `-abends.md`; Zulieferung und Vergleichsbasis, keine Freigabe |
| Automatiken (GitHub Actions) | Quellen-Abruf (`imports/quellen/`), Feuerwehr-Automatik, Umkreis-Automatik der Polizei, QA, WordPress-Abgleich |
| Luis Kummer | erhält den Abschlussbericht, entscheidet über Regeln und Zugänge |

## 2. Takt

| Zeit | Was |
|---|---|
| 05:10 und 15:10 UTC | Quellen-Abruf (`.github/workflows/quellen-abruf.yml`), danach Feuerwehr- und Umkreis-Automatik |
| 08:31 und 16:31 Europe/Berlin | Routine startet den Lauf; Bezeichnung `morgens` vor 12 Uhr, sonst `nachmittags` |
| stündlich Minute 7 | WordPress-Abgleich holt die Import-XML |

Zielzeit je Lauf: 60 Minuten. Die Containeruhr läuft in UTC und kann abweichen; Zeiten im Dossier mit `TZ=Europe/Berlin date` bestimmen und, wo es darauf ankommt, mit Commit-Zeiten abgleichen. Sommerzeit endet/beginnt am letzten Sonntag im Oktober/März.

## 3. Was sofort sichtbar wird

Geprüft in `deploy/wordpress-import.mjs`, im Plugin (`wordpress/plugin/merzenich-aktuell-core/includes/abgleich.php`, `ma_abgleich_status`) und im Theme (`wordpress/theme/merzenich-aktuell/inc/ma21.php`):

| Was | Weg | Sichtbar |
|---|---|---|
| Meldungen (`inhalte/meldungen/`) | Import-XML, Typ `post` | **Entwurf** in den Freigaben, solange „Sofort veröffentlichen“ aus ist |
| Termine (`inhalte/termine/`) | Import-XML, Typ `ma_event`, Status aus der XML | **veröffentlicht** ohne `"entwurf": true`, sonst Entwurf; der Abgleich ändert den Status bestehender Termine nie |
| **Änderungen an bestehenden Meldungen und Terminen** (Update, Nachtrag, Korrektur) | Abgleich überschreibt Titel, Text, Auszug und Importfelder im bestehenden Status (`ma_abgleich_entscheidung` → `aktualisieren`) | bei veröffentlichten Beiträgen **ohne Freigabe** beim nächsten Abgleich; wurde der Beitrag in WordPress von Hand geändert, kommt die Änderung **gar nicht** an (Protokoll „von Hand geändert“) |
| Spielstand `chatgpt-site/api/sport-current.json` | Theme liest von GitHub, Zwischenspeicher 15 Minuten; ein mindestens gleich neuer Backend-Spielstand geht vor | **ohne Freigabe**, in der Regel binnen 15 Minuten |
| Umkreis-Liste (`inhalte/umkreis/`, auch Automatik) | Theme liest den Block `umkreis` aus `chatgpt-site/index.html` auf GitHub, Zwischenspeicher 1 Stunde | **ohne Freigabe**, in der Regel binnen einer Stunde (bis zu drei Stunden, wenn die Auffrischung per WP-Cron hängt) |
| Markt (Stellen, Immobilien) | Theme liest Repo-Seiten, Zwischenspeicher 1 Stunde | **ohne Freigabe** |
| `chatgpt-site/api/editorial-current.json` | nur statischer Build; auf WordPress entscheidet das Board „Startseite & Ressorts“ | ohne Wirkung auf WordPress |

Daraus folgt verbindlich:

- **Jeder Claude-Termin trägt `"entwurf": true`** (geprüft von `qa/redaktion-lauf.mjs`). Die 11 Claude-Termine vom 25./26.10. gingen ohne dieses Feld online; seit Commit `20d0b3f` ist es nachgetragen.
- Spielstand und Umkreis nur mit Klartext-Belegen ändern und im Dossier mit Quelle und Zeitpunkt ausweisen.
- Korrekturen und Nachträge an bereits veröffentlichten Beiträgen sind sofort öffentlich: besonders sorgfältig prüfen, im Dossier und im Abschlussbericht ausdrücklich nennen und im Abgleich-Protokoll auf „von Hand geändert“ achten (dann der Redaktion den neuen Wortlaut zum Einpflegen geben).
- `editorial-current.json` und Marktdaten ändert die KI-Redaktion nicht.

## 4. Phasen eines Laufs

Jede Phase hat ein Abnahmekriterium. Ist es nicht erfüllt, gilt die festgelegte Reaktion; es wird nicht improvisiert.

### Phase 0: Vorbereitung (5 Minuten)

1. `git -C /home/user/Merzenich-Aktuell fetch origin main` und `git merge --ff-only origin/main`. Fremde Änderungen nie überschreiben; `git status` muss sauber sein.
2. Lesen: dieses Dokument, [latest.md](latest.md), die seit dem letzten Claude-Lauf neuen Dossiers (`ls -t docs/redaktion/dossiers | head`), zuerst deren Themenübersicht und alle Karten mit „Korrektur“ zu Claude-Artikeln.
3. `node deploy/register-pflege.mjs` und [offen.md](offen.md) lesen (offene Themen, möglicher Bestand je Thema); `register.json` nur für bearbeitete Themen öffnen.
4. Vergleichsbasis festhalten: main-Commit vor dem Lauf (`git log -1 --format=%h`), letzter Claude-Lauf.

Abnahme: main aktuell, Vergleichsbasis notiert, Korrekturaufträge bekannt. Reaktion bei Merge-Konflikt: Lauf nicht beginnen, Konflikt im Bericht nennen.

### Phase 1: Recherche (20 bis 30 Minuten)

1. **Korrekturen zuerst:** Korrekturaufträge der Dossiers zu eigenen Artikeln am Original prüfen und umsetzen (SCHREIBREGELN.md Abschnitt 5).
2. **Neues seit dem letzten Lauf:** Rohdaten mit dem Stand des letzten Laufs vergleichen (`git show <commit>:imports/quellen/<quelle>.json`), dann live die **Pflichtquellen jedes Laufs**: Polizei-RSS Dienststelle 8, Kreis-Presse (Index), Heimat-Info Merzenich (Startseite und neue Beiträge), Ratsinformationssystem (`termine.ics`, Vorlagenliste), Feuerwehr-Einsatzliste, Amtsblatt-Index; dienstags und freitags zusätzlich FUSSBALL.DE/fupa für die vier Herrenteams, bei laufenden Bahn- oder Straßensperrungen VRS/zuginfo.NRW und Autobahn-API. Danach die zwölf Recherche-Ressorts (QUELLEN.md Abschnitte 1.3 und 3) mit den dort genannten Zugängen. Kernorte vor Nachbarorten.
3. Zeitfenster: 24 Stunden zuerst, dann laufende Vorgänge der letzten 14 Tage, Termine bis 60 Tage.
4. Polizeimeldungen aus Nachbarorten legt `deploy/automatik-umkreis.mjs` an; im Lauf nur Meldungen mit Merzenich oder Ortsteil als eigene Meldung prüfen.
5. Teilrecherchen dürfen an Hintergrundagenten gehen, wenn sie unabhängig sind; jede übernommene Angabe stichprobenartig selbst am Original lesen.

Abnahme: Für jede Quelle in Abschnitt 4 des Dossiers steht Weg und Ergebnis, auch „nichts Neues“. Reaktion bei Sperre: Umweg aus QUELLEN.md, sonst Lücke im Dossier; keine Sperren umgehen.

### Phase 2: Verifikation

Nach dem Verifikationsprotokoll in SCHREIBREGELN.md Abschnitt 3: jede Zahl, jedes Datum, jeder Name an der Primärquelle; zwei Quellen außerhalb merzenich-aktuell.de; Bestand prüfen (offen.md, `grep` in `inhalte/`, `chatgpt-site/api/inhalte.json`); Widersprüche benennen.

Abnahme: Jede Angabe im Entwurf hat eine gelesene Fundstelle. Reaktion bei Unsicherheit: Angabe streichen; trägt der Rest keine Meldung, Thema als T2 ins Dossier.

### Phase 3: Schreiben

Artikeltyp nach SCHREIBREGELN.md Abschnitt 1 (Standard als Regelfall); Updates in bestehende Einträge; Termine mit `"entwurf": true`; Umkreis nur die sechs Orte; Sport als Einheit. Selbstprüfung nach SCHREIBREGELN.md Abschnitt 13.

### Phase 4: Paketierung, Build, Commit (10 Minuten)

1. Dateien: `inhalte/meldungen/<datum>-recherche-claude-<lauf>.json` (`{"hinweis": …, "redaktionsstandard": 2, "meldungen": […]}`), `inhalte/termine/<datum>-recherche-claude-<lauf>.json`, `inhalte/umkreis/<datum>.json`. Updates direkt in der Datei des Bestandsartikels, Einrückung erhalten.
2. `node deploy/kette.mjs` zweimal, `node deploy/kette.mjs --check` (muss „alle Generatoren unveraendert“ melden), `node qa/pruefung.mjs` (0 Fehler), `node deploy/register-pflege.mjs` (schreibt `offen.md`, mitcommitten), `node qa/redaktion-lauf.mjs` (0 Fehler).
3. Commit mit Inhalten und allen erzeugten Dateien (`chatgpt-site/`, `wordpress-delivery/`, `wordpress/`, `docs/SHA256SUMS.txt`, `deploy/sitemap-stand.json`); Nachricht `redaktion: <Lauf> <TT.MM.> – <Themen>`; Attributionszeilen.
4. `git fetch origin main`, `git rebase origin/main`, `git push -u origin main`. Nie `--force`.

Abnahme: Push erfolgreich, `kette --check` auf dem gepushten Stand unverändert. Reaktionen: Prüfung rot → nur eigene Pakete korrigieren oder herausnehmen, nie Prüfung, Generator oder Workflow ändern. Rebase-Konflikt in generierten Dateien (häufig `docs/SHA256SUMS.txt` durch CI-Commits) → beliebige Seite nehmen (`git checkout --ours <datei>`; beim Rebase ist „ours“ der Stand von main), Kette erneut laufen lassen, `git add`, `git rebase --continue`. Uncommittete Arbeit vorher mit `git stash push -u` sichern und danach zurückholen. Konflikt in Inhalten → Abbruch, Bericht.

### Phase 5: Dokumentation (5 Minuten)

1. Dossier `docs/redaktion/dossiers/<datum>-claude-<lauf>.md` nach [vorlagen/dossier-claude.md](vorlagen/dossier-claude.md).
2. `register.json` nach [REGISTER-SCHEMA.md](REGISTER-SCHEMA.md): bearbeitete Themen fortschreiben, neue anlegen, `research_runs`-Eintrag, `latest_dossier`.
3. `latest.md` neu schreiben; `node deploy/register-pflege.mjs` (schreibt `offen.md`).
4. `node qa/redaktion-lauf.mjs --datum <datum> --lauf <lauf>` (0 Fehler).
5. Commit `docs(redaktion): Dossier <Lauf> <TT.MM.>, Register, latest.md und offen.md`, Push wie Phase 4.

### Phase 6: Abgleich

1. `curl -sS https://merzenich-aktuell.de/?ma_api=abgleich-anstoss` (Antwort `angestossen: true`, oder `false` mit `naechster` in naher Zukunft, wenn der Workflow `wordpress-abgleich.yml` schon angestoßen hat; der Anstoß ist fünf Minuten gesperrt), danach `curl -sS "https://merzenich-aktuell.de/wp-cron.php?doing_wp_cron=$(date +%s)"` (HTTP 200).
2. Nachweis: neue Slugs bzw. geänderte Sätze in `https://raw.githubusercontent.com/HKGrowthOperator/Merzenich-Aktuell/main/wordpress-delivery/merzenich-aktuell-import.xml` (`grep -c`).
3. Backend nur, wenn der Zugang in der laufenden Sitzung ausdrücklich bereitgestellt ist (vom Nutzer genannt oder als Umgebungsgeheimnis vorhanden; Quelle im Dossier nennen, nie den Wert). Login per Benutzername, Eingabetaste im Passwortfeld, nach einem Fehlversuch abbrechen. Ohne Zugang gilt der Nachweis über die Import-XML.
4. Rückkanal: Für Themen mit `zur_abnahme` und Artikelpfad prüfen, ob `https://merzenich-aktuell.de/<ressort>/<slug>/` mit HTTP 200 antwortet (veröffentlichter Beitrag; Entwürfe antworten 404). Dann im Register `uebernommen` mit Datum in `notes`.

### Phase 7: Abschlussbericht

Nach [vorlagen/abschlussbericht.md](vorlagen/abschlussbericht.md) an Luis: Zahlen aus dem Dossier (nicht neu schätzen), Korrekturen (bei veröffentlichten Beiträgen ausdrücklich: „ohne Freigabe sichtbar“), Sofort-Sichtbares, offene Entscheidungen, Commits, Abgleich. Aufbau Wurzel (Quellen), Stamm (Repository), Krone (Sichtbares).

## 5. Definition of Done

- [ ] Korrekturaufträge der neuen Dossiers zu Claude-Artikeln erledigt oder begründet offen
- [ ] Jede neue oder geänderte Meldung: Mindestwerte, zwei Quellen, `stand` = Lesedatum
- [ ] Jeder neue Termin mit `"entwurf": true`
- [ ] Kette zweimal, `--check` unverändert, `qa/pruefung.mjs` 0 Fehler, `qa/redaktion-lauf.mjs --datum --lauf` 0 Fehler
- [ ] Ein Inhalts- und ein Dokumentationscommit auf main gepusht (bei Nullfund nur der Dokumentationscommit), ohne Force
- [ ] Dossier, Register, `latest.md`, `offen.md` konsistent
- [ ] Abgleich angestoßen, Nachweis in der Import-XML
- [ ] Bericht an Luis mit gezählten Zahlen

## 6. Harte Verbote

Keine Veröffentlichung (auch nicht über Termine ohne `entwurf`); „Sofort veröffentlichen“ nie einschalten; keine Bilder ohne geprüfte Lizenz und Sichtung; keine KI-generierten Fotos; keine Zugangsdaten in Dateien; keine erfundenen Zeitstempel, Adressen, URLs; keine Privatpersonen; kein Force-Push; keine Änderungen an Workflows, Scrapern, Generatoren, Theme oder Plugin im Redaktionslauf (von der Kette erzeugte Dateien unter `wordpress/` und `wordpress-delivery/` werden mitcommittet); keine fremden Volltexte.

## 7. Nullfund-Lauf

Gibt es nichts Neues: kein Meldungspaket, aber Dossier mit Zahlentabelle („Neue Meldungen | 0“), Quellenabschnitt mit Weg und Ergebnis je Quelle und offenen Punkten; Register `research_runs` mit `implementation_commit: null` und Begründung in `summary` (siehe REGISTER-SCHEMA.md Abschnitt 6); Bericht „Nullfund“ mit Quellenlücken. Keine Mengenvorgabe je Lauf.

## 8. Störungen

| Störung | Reaktion |
|---|---|
| Quelle gesperrt (403, 429, JavaScript-Prüfung) | Umweg aus QUELLEN.md; sonst Lücke im Dossier, nach drei Läufen in offene Punkte für Menschen |
| Rohdaten älter als 12 Stunden | nur Live-Abrufe, im Dossier vermerken |
| Sitzungs- oder Zeitlimit droht | bereits Geprüftes paketieren und pushen, Dossier knapp mit „abgebrochen nach Phase …“; nächster Lauf holt Phase 5 nach |
| Lauf überschneidet sich mit einer zweiten Claude-Sitzung | vor jedem Commit `git fetch`; dieselbe Meldung nie in zwei Sitzungen; Register zeigt, wer was umgesetzt hat |
| Widersprüchliche Quellen | beide Angaben benennen oder Thema auf T2 |
| Kein Backend-Zugang | Nachweis über Import-XML; im Bericht sagen, dass Freigaben nicht geprüft sind |

## 9. Kennzahlen je Lauf (im Dossier)

Neue Meldungen nach Typ, Anteil Standard und länger, Zwei-Quellen-Quote (Ziel 100 Prozent), Korrekturen an eigenen Artikeln, Termine (alle als Entwurf), Umkreis, Bilder (gefunden, geprüft, integriert), Dauer des Laufs. Nichts hochrechnen.

## 10. Änderungshistorie

| Version | Datum | Änderung |
|---|---|---|
| 1.0 | 26.10.2026 | Kurzfassung: Texte, Quellen, Ablauf in sieben Schritten |
| 1.1 | 26.10.2026 | Register-Lagebild (`register-pflege.mjs`, `offen.md`), Umkreis-Automatik (Parallelsitzung) |
| 1.2 | 27.10.2026 | Sofort sichtbare Pfade, Termine nur als Entwurf |
| 2.0 | 27.10.2026 | Vollständige Laufdefinition mit Phasen, Abnahmen, Störungen; `qa/redaktion-lauf.mjs`; QUELLEN.md, SCHREIBREGELN.md, REGISTER-SCHEMA.md, Vorlagen |
