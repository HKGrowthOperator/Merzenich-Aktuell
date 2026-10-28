# Register, Dossiers und latest.md: Schema und Pflege

Stand: 27.10.2026. Gilt für `docs/redaktion/register.json`, `latest.md`, `offen.md` und die Dossiers unter `dossiers/`. Ablauf: [CLAUDE-LAUF.md](CLAUDE-LAUF.md), Phase 5. Geprüft von `node deploy/register-pflege.mjs --check` und `node qa/redaktion-lauf.mjs --datum <datum> --lauf <lauf>`.

## 1. Zweck

Das Register ist das Bearbeitungsgedächtnis über Läufe und Sitzungen hinweg: Welches Thema ist recherchiert, wer hat es umgesetzt, wo liegt der Artikel, was ist offen. Es ist **kein Artikelinventar** (dafür `chatgpt-site/api/inhalte.json`) und **keine Publikationsfreigabe**.

## 2. Felder auf oberster Ebene

| Feld | Bedeutung | Pflege |
|---|---|---|
| `schema_version` | 1 | nicht ändern |
| `project`, `timezone` | „Merzenich Aktuell“, „Europe/Berlin“ | nicht ändern |
| `latest_dossier` | jüngstes Dossier relativ zu `docs/redaktion/` | jeder Lauf |
| `source_check_window` | Recherchefenster des jüngsten Laufs in Worten | jeder Lauf |
| `note` | Hinweis zur Reichweite des Registers | nicht ändern |
| `topics` | Themen (Abschnitt 3) | fortschreiben, nie löschen |
| `archive` | historische Dossier-Zuordnung bis 25.10. | nicht ändern |
| `research_runs` | ein Eintrag je Lauf (Abschnitt 6) | anhängen |

## 3. Felder eines Themas

| Feld | Pflicht | Werte / Form | Wer schreibt |
|---|---|---|---|
| `id` | ja | stabil, siehe Abschnitt 5 | wer das Thema anlegt |
| `priority` | ja | `P1` (dringend, Frist, Kernort), `P2`, `P3` | Recherche |
| `location_section` | ja | „Ort / Ressort“ | Recherche |
| `change_type` | ja | für neue Einträge: Neu, Update, Termin, Hintergrund, Nachfassen, Korrektur; Freitextwerte im Bestand bleiben stehen | Recherche |
| `text_status` | ja | `T0` gesperrt/unvollständig, `T1` belegter Entwurf, `T1-B` Bestandsentwurf ohne erneute Primärprüfung, `T2` Freigabebedingung offen | Recherche und Umsetzung |
| `image_status` | ja | `B0` kein freigegebenes Bild; sonst Stufe nach BILD-MOTIVREGELN | Umsetzung |
| `workflow_status` | ja | Abschnitt 4 | Umsetzung |
| `dossier` | ja | Dossier, das das Thema angelegt hat | wer anlegt |
| `last_checked_at` | ja | `YYYY-MM-DD morgens|nachmittags|abends Europe/Berlin` oder ISO | jeder, der prüft |
| `article_path` | bei Umsetzung | `inhalte/<ordner>/<datei>.json → /<ressort>/<slug>/` oder nur die Datei bei Umkreis | Umsetzung |
| `implementation_commit` | bei Umsetzung | kurzer Hash des Inhaltscommits | Umsetzung |
| `article_id` | nein | WordPress-ID, sobald bekannt | Rückkanal |
| `notes` | ja | fortlaufend, mit „Claude TT.MM. <Lauf>: …“ bzw. „ChatGPT …“ | alle |
| `dossier_history` | ja | `[{date, dossier, heading, check_scope?}]`, anhängen | alle |
| `related_topic_ids` | nein | IDs zusammengeführter Themen | wer zusammenführt |
| `editorial_correction_required` | nein | `true`, solange ein Korrekturauftrag offen ist | Recherche setzt, Umsetzung löscht |
| `research_*`, `latest_research_*`, `publication_status`, `refresh_note`, `source_check_status` | nein | Felder der ChatGPT-Recherche | ChatGPT; Claude lässt sie stehen |

## 4. Bearbeitungsstatus

```
neu ─► in_bearbeitung ─► zur_abnahme ─► uebernommen
  └──────────────► gesperrt / veraltet
archiv_refresh_noetig (Altbestand bis 25.10.) ─► neu, sobald ein Lauf es aufgreift
```

| Status | Bedeutung | Wer setzt |
|---|---|---|
| `neu` | recherchiert, nicht umgesetzt | Recherche |
| `in_bearbeitung` | Umsetzung begonnen, nicht abgeschlossen | Umsetzung |
| `zur_abnahme` | **mit** `article_path` und Commit: umgesetzt, liegt als Entwurf in den WordPress-Freigaben (Claude setzt nach dem Push; seit 28.10.2026 nur noch, wenn die Meldung `zurFreigabe` trägt oder die Live-URL noch 404 antwortet). **Ohne** `article_path`: von der ChatGPT-Recherche als abnahmereifer Entwurf markiert, also ein **offener Umsetzungsauftrag** für Claude | Claude bzw. ChatGPT |
| `uebernommen` | sichtbar: von der Redaktion veröffentlicht, direkt veröffentlichte Claude-Meldung (seit 28.10.2026, Plugin-Option „KI-Redaktion direkt“), oder direkt sichtbar (Umkreis, Spielstand) | Claude bei Umkreis/Sport sofort; bei Meldungen nach dem Live-Nachweis (CLAUDE-LAUF.md Phase 6.3: Live-URL antwortet mit HTTP 200), Datum in `notes` |
| `gesperrt` | nicht umsetzen (Dublette, vorbei, Privatperson, Widerspruch); Grund in `notes` | jeder |
| `veraltet` | durch Zeitablauf erledigt | jeder |
| `archiv_refresh_noetig` | historisches Thema, vor Übernahme neu prüfen | nur Archivimport |

`uebernommen` braucht `article_path` und `implementation_commit` (`register-pflege.mjs --check` prüft das); bei `zur_abnahme` unterscheidet das Vorhandensein von `article_path` die beiden Bedeutungen. Kein Status ist eine Publikationsfreigabe.

## 5. IDs

Präfix nach Ort: `MA-` Merzenich und Gemeinde, `GO-` Golzheim, `GR-` Girbelsrath, `MO-` Morschenich, `BW-` Bürgewald, `KD-` Kreis Düren, `DN-` Stadt Düren, `NZ-` Niederzier, `ELS-` Elsdorf, `NOE-` Nörvenich, `BUIR-` Buir, `NH-` Neuland Hambach. Andere Präfixe im Bestand bleiben stehen. Danach ein sprechendes Kürzel und, wo nötig, Datum oder Drucksache (`MA-ELW1-116-2026`, `KD-KI-ROADSHOW-0611`). Bestehende IDs immer weiterführen; vor dem Anlegen in `offen.md` und mit `grep -o '"id": "[^"]*' register.json` suchen. Dubletten nicht löschen, sondern über `related_topic_ids` und `notes` verbinden und eine auf `gesperrt` setzen.

## 6. Laufeinträge (`research_runs`)

Claude-Lauf, Pflichtfelder:

```json
{"date": "2026-10-27", "edition": "morgens", "by": "Claude", "dossier": "dossiers/2026-10-27-claude-morgens.md",
 "compared_with": "dossiers/2026-10-27-morgens.md", "implementation_commit": "636d04c",
 "summary": "0 neue Meldungen, 1 Korrektur, 1 Umkreis …", "publication": "keine"}
```

`edition` ist `morgens`, `nachmittags`, `vertiefung` oder `nachlauf`. Bei Nullfund ist `implementation_commit` der Dokumentationscommit oder `null` mit Begründung in `summary`.

## 7. Pflege je Lauf

1. Bearbeitete Themen: `workflow_status`, `text_status`, `article_path`, `implementation_commit`, `last_checked_at`, `notes` anhängen, `dossier_history` anhängen.
2. Neue Themen mit allen Pflichtfeldern anlegen.
3. `latest_dossier`, `source_check_window`, `research_runs` aktualisieren.
4. Schreiben mit `json.dump(..., ensure_ascii=False, indent=2)` bzw. `JSON.stringify(r, null, 2)`, UTF-8; danach gültiges JSON prüfen (`node -e "JSON.parse(require('fs').readFileSync('docs/redaktion/register.json'))"`).
5. `node deploy/register-pflege.mjs` (schreibt `offen.md`), dann `--check`.
6. Keine erfundenen Zeitstempel: Datum und Laufbezeichnung genügen.

## 8. latest.md

Pflichtabsätze: (1) jüngstes Dossier mit Link und Vergleichsbasis, Link auf `offen.md`; (2) „Umgesetzt“ mit Zahlen und Commit; (3) Systembefunde, falls vorhanden; (4) „Offen“; Fußzeile mit Register, Regeln, Archiv. Jeder Lauf schreibt die Datei neu; Altes steht im Dossier.

## 9. Dossiers

Dateinamen: Claude `YYYY-MM-DD-claude-morgens.md`, `-claude-nachmittags.md`, `-claude-vertiefung.md`, `-claude-nachlauf.md`; ChatGPT `YYYY-MM-DD-morgens.md`, `-abends.md`. Nie ein bestehendes Dossier überschreiben. Aufbau: [vorlagen/dossier-claude.md](vorlagen/dossier-claude.md). Dossiers sind öffentlich lesbar (öffentliches Repository): keine Zugangsdaten, keine Privatpersonen, keine privaten Kontaktdaten.
