# Merzenich Aktuell – Nachlauf (Claude), 26. Oktober 2026, abends

Arbeitsdossier nach `docs/redaktion/README.md` und `docs/redaktion/CLAUDE-LAUF.md`. Auftrag dieses Laufs war nicht die Tagesrecherche (die hat der [Nachmittagslauf](2026-10-26-claude-nachmittags.md), Commit `8041a59`, erledigt), sondern: den Stand prüfen, offene belegte Themen nachziehen und Arbeit aus den täglichen Läufen herausnehmen. Zeitangaben Europe/Berlin (MEZ).

**Vergleichsbasis:** [Nachmittagslauf 26.10.](2026-10-26-claude-nachmittags.md) und [Morgendossier 26.10.](2026-10-26-morgens.md). Gelesener main-Stand vor dem Lauf: `47a5fb1` (build: materialize editorial image system v2, 16:48 UTC).

**Push-Status:** Die Sitzung begann mit Leserechten; nach Freigabe der Schreibrechte wurden die Änderungen auf den inzwischen fortgeschrittenen Stand von `main` (`3524005`, QA-Lauf 15:58 UTC) neu aufgesetzt, die Kette erneut gebaut und auf `main` gepusht: `b73122c9` (Automatisierung), `976e9be1` (Meldung und Kette), danach dieses Dossier. Die Meldung erscheint über den Abgleich als Entwurf in den WordPress-Freigaben.

---

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Standard, 436 Wörter, 6 Fakten, 2 Quellen (Amprion-Netzausbau) |
| Registerthemen geklärt | 3 | Flutlicht (Dublette → uebernommen), Papiertonne (unbelegt → gesperrt), SC III (weiter T0) |
| Neue Termine / Umkreis | 0 / 0 | keine Tagesrecherche in diesem Lauf |
| Automatisierung | 3 | Umkreis-Automatik Polizei, Register-Lagebild, Korrektur Bildsystem-Workflow |
| Kette / QA | 2 Läufe, `--check` unverändert, 0 Fehler, 32 Hinweise | Hinweise: bekannte Symbolbild-Pools |
| Bilder | 0 | keine Bildrecherche |

## 1. Befund zum Stand (Wurzel)

- **Freigabestau in WordPress.** Auf merzenich-aktuell.de ist die jüngste redaktionelle Meldung vom 21.10. (Einsatz 140/26); die 17 Meldungen vom 25.10. (`7e268d6`) und die 4 vom 26.10. (`8041a59`) sind nicht sichtbar. Sie liegen, wenn der Abgleich lief, als Entwürfe in den Freigaben. Der Umkreis-Block ist dagegen aktuell (25.10.), weil das Theme ihn stündlich aus dem Repository liest. Eine Sichtprüfung im Backend war ohne Benutzername weiter nicht möglich.
- **`kette --check` auf main war rot.** Der Workflow „Materialize Editorial Image System V2“ läuft die ganze Kette, committet aber nur `chatgpt-site/`; die Plugin-Datenkopie `wordpress/plugin/merzenich-aktuell-core/data/bildpools.json` (Feld `stand`) blieb auf dem Vortag. Lokal reproduziert (Exit 2), Ursache im Workflow behoben (siehe 3.).
- **Quellenabruf Polizei** holt nur die fünf Ortslisten des Presseportals; die Gesamtliste der Polizei Düren (`blaulicht/nr/8`), aus der jeder Lauf die Umkreis-Einträge von Hand zusammensuchte, fehlte in den Rohdaten.
- **Register:** 152 Themen, davon 101 `archiv_refresh_noetig` ohne Priorität; die 12 offenen Themen waren nur durch Lesen der ganzen 295-KB-Datei zu finden. Zwei der drei „T1“-Themen mit P1 waren bereits Bestand bzw. unbelegt (siehe 2.).
- **Rohdaten** vom 26.10. 11:03–11:09 UTC; der 15:10-UTC-Abruf hat bis 16:48 UTC keinen Commit erzeugt (nichts Neues oder verzögert).
- Presseportal antwortet aus dieser Sitzung mit HTTP 503; Gemeinde, Heimat-Info, Ratsinformationssystem, Kreis, FUSSBALL.DE, Amprion und Bistum waren erreichbar.

## 2. Redaktion (Stamm)

| ID (Register) | Ergebnis | Pfad / Status | Prüfumfang |
|---|---|---|---|
| MA-AMPRION-BAUSTART | **Neu:** Standard-Artikel „Neue 380-kV-Leitung: Amprion baut bei Merzenich, Radweg am S-Bahnhof gesperrt“ (leben, Bildklasse leben.energie) | `inhalte/meldungen/2026-10-26-recherche-claude-nachlauf.json` → `/leben/amprion-stromtrasse-oberzier-blatzheim-bauarbeiten-merzenich-radweg-s-bahnhof-2026/`; Register `zur_abnahme` | Gemeindemitteilung „Baustart für die neue Stromtrasse“ (veröffentlicht 28.07.2026, Metadatum der Seite) und Amprion-Projektseite „Oberzier – Pkt. Blatzheim“ vollständig gelesen. Belegt: vier 380-kV-Stromkreise, 15,8 km, Vorhaben Nr. 74 BBPlG, Planfeststellung Bezirksregierung Köln, 50–60 m Parallelführung, Baustraßen östlich „Auf der Heide“, Fundamente am S-Bahnhof mit Radwegsperrung, Fundamente/Montage bis Mitte 2027, Baustellenverkehr L 264/Steinweg/Auf der Heide, Hintergrund Tagebau Hambach. **Nicht übernommen:** „zwei neue Masten im Gemeindegebiet“ aus dem Dossier vom 25.10. steht auf keiner der beiden Seiten. Bestand zuvor: keiner (Paket 07.10. hatte das Thema ausdrücklich nicht übernommen). |
| GR-FLUTLICHT-2026 | **Dublette.** Thema steht seit 29.09. (`rhenania-girbelsrath-neues-flutlicht`, Heimat-Info) und 16.10. (`flutlicht-sportplatz-rhenania-girbelsrath-kek-westenergie`, Amtsblatt 11) im Bestand | Register `uebernommen` mit beiden Pfaden | Gemeindeseite `flutlicht-rhenania-girbelsrath.php` liefert keine Angaben über den Bestand hinaus (Kosten, Leuchtenzahl, Abnahme offen). Keine neue Meldung. |
| MA-PAPIERTONNE-2026 | **Unbelegt.** Die Karte vom 25.10. („240-Liter-Behälter ab 2027 ohne Zusatzkosten“) hat keine Quelle | Register `gesperrt` | Gemeindeseite `papiertonne-gemeinde-merzenich.php` am 26.10. gelesen: Bitte, Altpapier in die vorhandene Tonne statt in Bündel zu geben; kein Wort zu Behältergröße, 2027 oder Gebühren. Diesen Inhalt deckt der Bestandsartikel vom 15.10. (`altpapier-in-die-papiertonne-gemeinde-schoenmackers-2026`). Gesperrt bis zu einer Quelle für die Behälterumstellung. |
| MA-SC3-GEREONSWEILER-2510 | **Weiter offen.** Spielseite zeigt „Abpfiff“, Endstand nur als verschleierte Schrift | Register `neu`/T0, Notiz ergänzt | Regel „nur Klartext“ (CLAUDE-LAUF.md) eingehalten; nächste Chance: FUSSBALL.DE-Datenbericht oder fupa. |
| MA-RIS-OKT25 | nicht weiter | unverändert | Ratsinformationssystem: `/sitzungen` und `/sitzungskalender` antworten 404, `/termine` listet nur die Gremien; die Niederschrift des Strukturausschusses vom 15.10. war in der Zeit nicht zu finden. Für den Morgenlauf: Gremienseite „Ausschuss für Wirtschaftsförderung und Strukturentwicklung“ direkt öffnen. |

Nicht bearbeitet (kein neuer Beleg, keine Tagesrecherche): SEV-Buszeiten, Bistum-Ernennung, H-Gas, Bürgewald, Krätzchen, FBL3, Team Ortsfest, Modellbahn, Adventmarkt, Vision+, Ernte.

## 3. Automatisierung (Krone)

1. **Umkreis-Automatik Polizei** – `deploy/automatik-umkreis.mjs`, Test `qa/automatik-umkreis-test.mjs` mit echten Rohdaten (`qa/fixtures/automatik-umkreis.json`, Listen Merzenich und Golzheim vom 26.10.), Beschreibung in `docs/RECHERCHE-QUELLEN.md`.
   - `deploy/quellen-abruf.mjs` holt zusätzlich die Gesamtliste der Polizei Düren (`blaulicht/nr/8`), Ortslisten bleiben zuerst (Vorrang bei den höchstens 60 Unterseiten).
   - Der Generator liest alle Listen, nimmt je Einzelmeldung Datum, Dienststelle, amtliche Schlagzeile und den Ort aus dem Vorspann („Niederzier (ots) - …“), und schreibt Einträge für den Block „Direkter Umkreis“ (`inhalte/umkreis/<datum>-polizei-automatik.json`, Schema von `deploy/umkreis.mjs`): Düren, Niederzier, Nörvenich, Elsdorf, Kerpen direkt; übrige Kreiskommunen unter „Kreis Düren“ mit Ort vor der Schlagzeile.
   - **Nie automatisch:** Meldungen mit Merzenich, Golzheim, Girbelsrath, Morschenich oder Bürgewald im Vorspann. Sie stehen in der Job-Zusammenfassung „Für die Redaktion“ und bleiben eigene Meldungen des Claude-Laufs. Andere Dienststellen (Polizei Aachen in den Ortslisten) und Orte außerhalb des Kreises werden übersprungen. Dubletten gegen `inhalte/umkreis/*`, Quellen aller Meldungen und `deploy/automatik-stand.json` (neuer Schlüssel `umkreis`).
   - Eingebunden in `.github/workflows/meldungen-automatik.yml` (nach dem Quellenabruf, gleiche Schleife, `trocken` wirkt auch hier) und als Test in `qa.yml`.
   - Bewusst: Der Umkreis-Block erscheint ohne Freigabe (Theme liest `index.html` aus dem Repository). Deshalb nur amtliche Schlagzeile, Ort, Datum und Link, kein eigener Text. Wer einen Eintrag nicht will, löscht ihn in der JSON-Datei; die URL bleibt im Stand.
   - Erster scharfer Lauf: nach dem nächsten Quellenabruf (05:10 UTC). Aus dieser Sitzung heraus war kein Livetest gegen das Presseportal möglich (503); der Test deckt Zerlegen, Zuordnung und Dubletten mit echten Listen ab.
2. **Register-Lagebild** – `deploy/register-pflege.mjs` schreibt `docs/redaktion/offen.md`: Zahlen je Status, die offenen Themen nach Priorität und Textstatus, je Thema Dossiers, Quellen, Artikelpfad und **möglicher Bestand** (gleiche Quellen-URL oder Stichwort der Themen-ID in Slug/Titel). Beim ersten Lauf zeigte es sofort die beiden Dubletten (Flutlicht, Papiertonne). `--check` prüft Pfade und Pflichtfelder (uebernommen ohne Artikelpfad/Commit, fehlende Dossiers oder Artikeldateien). In `CLAUDE-LAUF.md` (Schritt 1 und 5) und `README.md` verankert; `kette.mjs` bleibt unberührt.
3. **Bildsystem-Workflow** – `.github/workflows/editorial-images-v2-materialize.yml` committet jetzt denselben Satz wie die Meldungen-Automatik (Plugin-Daten, Import-XML, SHA256SUMS, Theme-Vorlagen, Sitemap-Stand). Damit bleibt `kette --check` nach einem Bildlauf grün.

## 4. QA, Kette und Commits

- `node deploy/kette.mjs` zweimal: 579 Seiten, WordPress-Import 216 Meldungen, 66 Termine, 89 Bilder; zweiter Lauf 0 Dateien geschrieben. `--check` unverändert. `node qa/pruefung.mjs`: 0 Fehler, 32 Hinweise. `qa/automatik-feuerwehr-test.mjs` und `qa/automatik-umkreis-test.mjs`: alle Prüfungen bestanden. Workflow-YAML geparst.
- Commits auf `main`: `b73122c9` (Automatisierung), `976e9be1` (Meldung und Kette), danach dieses Dossier. Vor dem Push: fetch, Neuaufbau auf `3524005`, Kette zweimal, `--check` unverändert, QA 0 Fehler.

## 5. Offene Punkte für den nächsten Lauf (27.10. morgens)

1. Abgleich-Ergebnis prüfen: Die Amprion-Meldung muss als Entwurf in den Freigaben liegen (Anstoß nach dem Push ausgelöst).
2. **Freigaben in WordPress:** 22 Entwürfe seit 25.10. warten; Benutzername für die Redaktionsadresse hinterlegen, damit der Lauf den Stand prüfen kann. Solange nichts freigegeben wird, bleibt die Startseite auf dem Stand vom 21.10.
3. Ersten scharfen Lauf der Umkreis-Automatik in der Job-Zusammenfassung prüfen (Orte, Schlagzeilen, Dubletten).
4. SC III Endstand (Datenbericht), SEV-Buszeiten, Bistum-Ernennung, Strukturausschuss-Niederschrift (Gremienseite), Jugendgremium 04.11.
5. Register: die 101 Archivthemen ohne Priorität sind für das Lagebild unsichtbar; wer sie braucht, setzt den Status im Register.
