# KI-Redaktion Merzenich Aktuell: Laufdefinition (Claude)

Version 3.8, Stand 03.11.2026. Verbindlich für jeden Redaktionslauf der KI-Redaktion (Claude Code), zweimal täglich, und für die Eil-Prüfung (Abschnitt 2a). Bei Widerspruch gilt: [PROJECT.md](../../PROJECT.md) vor diesem Dokument, dieses Dokument vor [README.md](README.md) und den Dossiers. Schreib- und Prüfregeln: [SCHREIBREGELN.md](SCHREIBREGELN.md). Quellen: [QUELLEN.md](QUELLEN.md). Register und Dossiers: [REGISTER-SCHEMA.md](REGISTER-SCHEMA.md). Vorlagen: [vorlagen/](vorlagen/). Auftrag an ChatGPT: [vorlagen/chatgpt-auftrag.md](vorlagen/chatgpt-auftrag.md).

**In einem Satz:** Die KI-Redaktion recherchiert, schreibt belegte, redaktionelle Artikel mit passendem Bild, baut, prüft, committet und dokumentiert; **ihre eigenen Beiträge gehen ohne Freigabe online** (Entscheidung Luis Kummer, 28.10.2026). Beiträge anderer (Feuerwehr- und Umkreis-Automatik, Vereine, Partner, ChatGPT-Pakete) laufen weiter über die WordPress-Freigaben.

Weil niemand mehr gegenliest, trägt der Lauf die volle Verantwortung: Was nicht belegt ist, erscheint nicht. Im Zweifel geht ein Beitrag gezielt in die Freigabe (Abschnitt 3).

## 1. Rollen

| Rolle | Aufgabe |
|---|---|
| KI-Redaktion (Claude, ein Agent je Lauf) | Lauf nach diesem Dokument, veröffentlicht eigene Beiträge; Teilrecherchen an Hintergrundagenten nur mit eigener Stichprobe der Ergebnisse |
| ChatGPT-Recherche (Wurzel) | Dossiers `dossiers/YYYY-MM-DD-morgens.md` (bis 08:45) und `-mittags.md` (bis 14:15, ältere Läufe `-abends.md`) nach [vorlagen/chatgpt-auftrag.md](vorlagen/chatgpt-auftrag.md): Korrekturen, Themenkarten mit mindestens drei Quellen, Bildkandidaten; schreibt keine fertigen Texte |
| Redaktion (Menschen, Backend) | Freigaben fremder Beiträge und der Claude-Beiträge, die der Lauf gezielt zur Freigabe gibt; Entscheidungen zu gesperrten Themen |
| Automatiken (GitHub Actions) | Quellen-Abruf (`imports/quellen/`), Feuerwehr-Automatik, Umkreis-Automatik der Polizei, QA, WordPress-Abgleich |
| Luis Kummer | erhält den Abschlussbericht, entscheidet über Regeln und Zugänge |

## 2. Takt

| Zeit (Europe/Berlin) | Was |
|---|---|
| 06:10 und 16:10 (05:10/15:10 UTC im Winter) | Quellen-Abruf (`.github/workflows/quellen-abruf.yml`), danach Feuerwehr- und Umkreis-Automatik |
| etwa 07:15 und 12:05 | ChatGPT-Dossier morgens und mittags (`*-morgens.md`, `*-mittags.md`) |
| 09:13 und 14:43 | Routine startet den Lauf in der festen Redaktionssitzung (mit Push-Rechten auf das Repository); Bezeichnung `morgens` vor 12 Uhr, sonst `nachmittags` |
| stündlich Minute 7 | WordPress-Abgleich holt die Import-XML |
| 06:40, 10:40, 12:40, 16:40, 18:40, 20:40 | Eil-Prüfung in der Redaktionssitzung (Abschnitt 2a); 09:13 und 14:43 übernehmen die beiden Läufe |

Zielzeit je Lauf: 60 bis 90 Minuten. Die Containeruhr läuft in UTC; Zeiten im Dossier mit `TZ=Europe/Berlin date` bestimmen. Sommerzeit endet/beginnt am letzten Sonntag im Oktober/März.

## 2a. Eil-Prüfung (Entscheidung Luis Kummer 03.11.2026)

Zweck: Blaulicht und Eilmeldungen aus Merzenich vor den anderen online, spätestens etwa zwei Stunden nach der Quelle (Anlass: Polizei 02.11. 15:08 Uhr, Radio Rur, Aachener Zeitung und andere 1 bis 3 Stunden später, wir 18:56 Uhr; `docs/SICHTBARKEITSPLAN-2026-11.md`). Kein vollständiger Lauf: kein ChatGPT-Dossier, keine Kalenderpflege, kein Dossier.

1. **Prüfen (höchstens 10 Minuten):** Polizei Düren, Dienststellenliste https://www.presseportal.de/blaulicht/nr/8 (Einträge seit dem letzten Lauf oder der letzten Eil-Prüfung, Ortszeile oder Vorspann mit Merzenich, Golzheim, Girbelsrath, Morschenich, Bürgewald); Feuerwehr Merzenich (Heimat-Info-Profil und https://feuerwehr-merzenich.de/einsaetze); Heimat-Info-Beiträge der Gemeinde (Sperrungen, Warnungen, Ausfälle). Bestand vorher prüfen: Quelle schon in `inhalte/meldungen` oder `docs/redaktion/register.json`, Einsatznummer schon gedeckt?
2. **Nichts Neues:** sofort beenden, eine Zeile Antwort („Eil-Prüfung HH:MM: nichts Neues“), kein Commit.
3. **Neues mit Nachrichtenwert:** Kurzmeldung nach SCHREIBREGELN (150 bis 350 Wörter, eigene Worte, offizielle Quelle, Prüfprotokoll, „Merzenich“ im Titel, Einordnung aus dem eigenen Archiv, wenn vorhanden), `relevanz` nach Abschnitt 0a, Paket `inhalte/meldungen/<datum>-recherche-claude-eil-<HHMM>.json`. Bild nach Phase 4a. Dann `node qa/redaktion-lauf.mjs`, `node qa/eigenformulierung.mjs --datum <d> --lauf eil-<HHMM>`, Kette zweimal und `--check`, Commit, **sofort pushen**, Abgleich anstoßen, Live-Nachweis (Artikel HTTP 200, Nachrichten-Sitemap). Register: `research_runs`-Eintrag mit `edition: "eil-HHMM"`; `latest.md` eine Zeile.
4. Eine Feuerwehr-Meldung, die die Automatik schon als Entwurf angelegt hat, wird nicht doppelt geschrieben; dann nur prüfen, ob sie in den Freigaben wartet, und im nächsten Lauf berichten.

## 3. Was sofort sichtbar wird

Geprüft in `deploy/wordpress-import.mjs`, im Plugin (`wordpress/plugin/merzenich-aktuell-core/includes/abgleich.php`, `ma_abgleich_status`) und im Theme (`wordpress/theme/merzenich-aktuell/inc/ma21.php`):

| Was | Weg | Sichtbar |
|---|---|---|
| Claude-Meldungen (`inhalte/meldungen/*-recherche-claude-*.json`) | Import-XML, Typ `post`, Meta `ma_herkunft = ki-redaktion` | **veröffentlicht** beim nächsten Abgleich, sobald im Plugin die Option „Beiträge der KI-Redaktion direkt veröffentlichen“ an ist; bis dahin Entwurf in den Freigaben. `"zurFreigabe": true` hält eine Meldung gezielt als Entwurf zurück |
| Feuerwehr-Automatik (`inhalte/meldungen/*-feuerwehr-automatik.json`) | Import-XML, Typ `post`, Meta `ma_herkunft = ki-redaktion`, `ma_relevanz` aus der Bildklasse (2 Brand und Unfall mit Rettung, sonst 3) | **veröffentlicht** beim nächsten Abgleich (Entscheidung Luis Kummer 03.11.2026); der nächste Lauf prüft nach |
| Andere Meldungen (ChatGPT, Amtsblatt-Pakete) und Einsendungen von Vereinen, Partnern, Firmen | Import-XML bzw. Backend | **Entwurf** in den Freigaben |
| Termine (`inhalte/termine/`, auch Heimat-Info-Automatik seit 03.11.2026) | Import-XML, Typ `ma_event`, Status aus der XML | **veröffentlicht**, außer mit `"entwurf": true`; der Abgleich ändert den Status bestehender Termine nie |
| **Änderungen an bestehenden Meldungen und Terminen** (Update, Nachtrag, Korrektur) | Abgleich überschreibt Titel, Text, Auszug und Importfelder im bestehenden Status | bei veröffentlichten Beiträgen **sofort** beim nächsten Abgleich; wurde der Beitrag in WordPress von Hand geändert, kommt die Änderung **gar nicht** an (Protokoll „von Hand geändert“) |
| Artikelbild (Poolfoto) | Import-XML; das Plugin tauscht Poolfotos nach, solange die Redaktion sie nicht von Hand geändert hat | mit dem Abgleich |
| Spielstand `chatgpt-site/api/sport-current.json` | Theme liest von GitHub, Zwischenspeicher 15 Minuten; ein mindestens gleich neuer Backend-Spielstand geht vor | **ohne Freigabe**, in der Regel binnen 15 Minuten |
| Umkreis-Liste (`inhalte/umkreis/`, auch Automatik) | Theme liest den Block `umkreis` aus `chatgpt-site/index.html` auf GitHub, Zwischenspeicher 1 Stunde | **ohne Freigabe**, in der Regel binnen einer Stunde |
| Markt (Stellen, Immobilien) | Theme liest Repo-Seiten, Zwischenspeicher 1 Stunde | **ohne Freigabe** |
| `chatgpt-site/api/editorial-current.json` | nur statischer Build; auf WordPress entscheidet das Board „Startseite & Ressorts“ | ohne Wirkung auf WordPress |

Daraus folgt verbindlich:

- **Grundsatz (Entscheidung Luis Kummer 03.11.2026): automatisch online, nach Wichtigkeit sortiert.** Was die KI-Redaktion schreibt und was die Automatik anlegt, geht ohne Freigabe online; `relevanz` 1 bis 3 bestimmt den Platz, „Neu in Merzenich“ zeigt jede neue Meldung. Unbelegtes wird weggelassen statt zur Freigabe gelegt; widersprüchliche Angaben stehen beide mit Quelle im Text.
- **Zur Freigabe nur noch bei rechtlichem Risiko:** `"zurFreigabe": true`, wenn Vorwürfe oder Verdacht gegen erkennbare Personen ohne amtliche Quelle, Gerichtsverfahren oder Kündigungen betroffen sind, oder ein Thema auf T2 steht. Begründung im Dossier.
- Spielstand und Umkreis nur mit Klartext-Belegen ändern und im Dossier mit Quelle und Zeitpunkt ausweisen.
- Korrekturen an veröffentlichten Beiträgen sind sofort öffentlich: im Dossier und im Bericht ausdrücklich nennen, Hinweis „Korrigiert am …“ im Text, Protokoll „von Hand geändert“ beachten.
- `editorial-current.json` und Marktdaten ändert die KI-Redaktion nicht.

## 4. Phasen eines Laufs

Jede Phase hat ein Abnahmekriterium. Ist es nicht erfüllt, gilt die festgelegte Reaktion; es wird nicht improvisiert.

### Phase 0: Vorbereitung (5 Minuten)

1. Repository: fehlt `/home/user/Merzenich-Aktuell`, klonen (`git clone https://github.com/HKGrowthOperator/Merzenich-Aktuell`). Dann `git fetch origin main` und `git merge --ff-only origin/main`. Fremde Änderungen nie überschreiben; `git status` muss sauber sein.
2. Lesen: dieses Dokument, [latest.md](latest.md), das neueste ChatGPT-Dossier (`ls -t docs/redaktion/dossiers | head`), **zuerst dessen Abschnitt `## KORREKTUREN`**, dann die Themenkarten nach Priorität; außerdem die seit dem letzten Claude-Lauf neuen Dossiers.
3. `node deploy/register-pflege.mjs` und [offen.md](offen.md) lesen; `register.json` nur für bearbeitete Themen öffnen.
4. Vergleichsbasis festhalten: main-Commit vor dem Lauf (`git log -1 --format=%h`), letzter Claude-Lauf.

Abnahme: main aktuell, Vergleichsbasis notiert, Korrekturaufträge bekannt. Reaktion bei Merge-Konflikt: Lauf nicht beginnen, Konflikt im Bericht nennen.

### Phase 1: Recherche (20 bis 30 Minuten)

1. **Korrekturen zuerst:** Korrekturaufträge der Dossiers zu eigenen Artikeln am Original prüfen und umsetzen (SCHREIBREGELN.md Abschnitt 5).
2. **ChatGPT-Karten:** Jede Karte ist ein Hinweis, kein Beleg. Jede übernommene Angabe selbst am Original lesen.
3. **Neues seit dem letzten Lauf:** Rohdaten mit dem Stand des letzten Laufs vergleichen (`git show <commit>:imports/quellen/<quelle>.json`), dann live die **Pflichtquellen jedes Laufs**: Polizei-RSS Dienststelle 8, Kreis-Presse (Index), Heimat-Info Merzenich (Beiträge **und Veranstaltungskalender**, Phase 1a), Ratsinformationssystem (`termine.ics`, Vorlagenliste), Feuerwehr-Einsatzliste, Amtsblatt-Index; dienstags und freitags zusätzlich FUSSBALL.DE/fupa für die vier Herrenteams, bei laufenden Bahn- oder Straßensperrungen VRS/zuginfo.NRW und Autobahn-API. Danach die zwölf Recherche-Ressorts (QUELLEN.md Abschnitte 1.3 und 3). Kernorte vor Nachbarorten.
4. Zeitfenster: 24 Stunden zuerst, dann laufende Vorgänge der letzten 14 Tage, Termine bis 60 Tage.
5. Polizeimeldungen aus Nachbarorten legt `deploy/automatik-umkreis.mjs` an; im Lauf nur Meldungen mit Merzenich oder Ortsteil als eigene Meldung prüfen.
6. Für jedes Thema **mindestens drei Quellen** suchen (Primärquelle, unabhängige Zweitquelle, Hintergrund wie frühere Beschlüsse, Vorlagen, Statistiken). Findet sich keine Zweitquelle, trägt eine offizielle Quelle eine Kurzmeldung (SCHREIBREGELN.md Abschnitt 0); ein Thema wird deshalb nicht gesperrt.
7. **Mindesttakt prüfen:** Live-Archiv (`https://merzenich-aktuell.de/archiv/`) und `node qa/redaktion-lauf.mjs` zeigen, wie viele Meldungen heute schon erschienen sind, auch von Hand in WordPress angelegte. Ziel: jeden Tag mindestens zwei, davon eine aus Blaulicht, Rathaus oder Termine-Vorschau.

Abnahme: Für jede Pflichtquelle steht im Dossier Weg und Ergebnis, auch „nichts Neues“. Reaktion bei Sperre: Umweg aus QUELLEN.md, sonst Lücke im Dossier; keine Sperren umgehen.

### Phase 1a: Kalenderpflege (jeder Lauf, 10 bis 15 Minuten)

Entscheidung Luis Kummer, 30.10.2026: Kalender und Veranstaltungen sind in jedem Lauf aktuell zu halten, zusammen mit ChatGPT.

1. Arbeitsliste: `node qa/redaktion-lauf.mjs --kalender` (heute bis 60 Tage, mit Slug, Quelle und Prüfstand `quelle.stand`).
2. Quellen: **Veranstaltungskalender der Gemeinde in Heimat-Info** (Pflicht in jedem Lauf, siehe 2a), Abschnitt `## TERMINE` des neuesten ChatGPT-Dossiers (Hinweis, kein Beleg), Ratsinformationssystem (`termine.ics`), Heimat-Info-Beiträge, Amtsblatt, Pfarrbrief/GdG, Vereinsseiten, Kreis Düren. Heimat-Info ist eine Quelle unter mehreren, nicht die einzige: Die übrigen Quellen werden weiter geprüft.
2a. **Heimat-Info-Veranstaltungskalender (Entscheidung Luis Kummer, 02.11.2026):** Die Gemeinde Merzenich pflegt ihre Veranstaltungen derzeit dort; Gemeinde, Vereine und Kirchen tragen ein. Damit wird der Terminkalender aufgefüllt.
   - Liste https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen in jedem Lauf **vollständig** lesen, alle kommenden Einträge, nicht nur 60 Tage. Die Automatik übernimmt bis 120 Tage voraus; Stand 02.11.2026 reicht das bis Anfang März 2027.
   - Jeden Eintrag an seiner Detailseite `/veranstaltungen/<uuid>` mit `inhalte/termine/` abgleichen: Titel, Datum, Uhrzeit, Ort, Veranstalter. Rohdaten: `imports/quellen/heimatinfo.json` (`kalender`, `veranstaltungen`).
   - **Fehlt der Termin bei uns:** Hat `deploy/automatik-termine.mjs` ihn schon angelegt (`inhalte/termine/<datum>-heimatinfo-automatik.json`), keinen zweiten anlegen; seit 03.11.2026 sind neue Automatik-Termine direkt online (Entscheidung Luis Kummer), der Lauf prüft sie wie jeden anderen Termin (Aktion **bestätigt**, **geändert** oder **abgesagt**). Die zwei Entwürfe vom 02.11. bleiben Entwurf. Hat die Automatik ihn nicht (zum Beispiel weil der Lauf noch aussteht), Aktion **neu**.
   - **Weicht unser Termin ab** (Uhrzeit, Ort, Absage): Aktion **geändert** oder **abgesagt**.
   - Private Termine (Hochzeit, Geburtstag, Taufe, Trauerfeier) nie übernehmen.
   - Im Quellenprotokoll steht der Kalender mit Abrufzeit und Zahl der Einträge, auch bei „nichts Neues“; `qa/redaktion-lauf.mjs --datum --lauf` verlangt „Heimat-Info“ im Abschnitt „Kalenderpflege“.
3. Je Termin genau eine Aktion:
   - **neu:** Termin in `inhalte/termine/` anlegen (Dublette gegen Slug und Datum prüfen);
   - **geändert:** den bestehenden Slug bearbeiten (Datum, Uhrzeit, Ort); bei Verschiebung Hinweis „Verschoben vom …“ in die Beschreibung, kein zweiter Termin;
   - **abgesagt:** `"abgesagt": true` setzen, nicht löschen (die Seite zeigt „Abgesagt:“ und Schema.org `EventCancelled`);
   - **bestätigt:** an der Quelle gelesen, `quelle.stand` auf das Lesedatum setzen.
4. Pflicht: Jeder Termin der nächsten 14 Tage ist an seiner Quelle nachgeprüft. Ziel: kein Hinweis „nachpruefen“ mehr von `node qa/redaktion-lauf.mjs`.
5. Vergangene Termine bleiben unverändert. Eine Selbstauskunft des Vereins oder Veranstalters ist Beleg, wenn sie als Quelle genannt ist; ist Datum, Ort oder Durchführung unsicher, `"entwurf": true` mit Grund im Dossier.

Abnahme: Dossier-Abschnitt „Kalenderpflege“ mit Tabelle (Termin, Slug, Aktion, Quelle, Stand) und Zahl der offenen Hinweise; `qa/redaktion-lauf.mjs --datum --lauf` prüft den Abschnitt. Reaktion bei gesperrter Quelle: Termin nicht ändern, Lücke im Dossier.

### Phase 2: Verifikation

Nach dem Verifikationsprotokoll in SCHREIBREGELN.md Abschnitt 3 und der **Prüfdefinition** in Abschnitt 3a: jede tragende Aussage offiziell belegt, von zwei Herausgebern bestätigt oder als Selbstauskunft gekennzeichnet; jede Zahl, jedes Datum, jeder Name an der Primärquelle; mindestens drei Quellen; Bestand prüfen (offen.md, `grep` in `inhalte/`, `chatgpt-site/api/inhalte.json`); Widersprüche benennen. Das Ergebnis steht als Prüfprotokoll (`pruefung`) in der Meldung.

Abnahme: Jede Aussage und jede Zahl im Text steht im Prüfprotokoll mit gelesenem Beleg. Reaktion bei Unsicherheit: Angabe streichen; trägt der Rest keine Standardmeldung, aber eine Kurzmeldung, dann Kurzmeldung; trägt er gar keine Meldung, Thema als T2 ins Dossier; reicht es für eine Meldung, aber nicht für die Direktveröffentlichung, `"zurFreigabe": true`. Eine fehlende Zweitquelle allein ist bei offizieller Quelle kein Grund für T2 oder `zurFreigabe`.

### Phase 3: Schreiben

**Top-Meldung (ab 3.5):** Jede Meldung bekommt `relevanz` (1 Top, 2 Standard, 3 Service) nach SCHREIBREGELN.md Abschnitt 0a; die stärkste Nachricht des Laufs mit großem Nachrichtenwert bekommt `1` und wird damit automatisch Aufmacher. Neue Entwicklung mit eigenem Nachrichtenwert zu einem älteren Artikel: Folgemeldung (neue Kurzmeldung) plus Nachtrag im alten Artikel. Nach dem Abgleich prüfen, ob die Top-Meldung oben steht; das Ergebnis steht im Dossier.

Nach SCHREIBREGELN.md: **Standard (mindestens 800 Wörter, Ziel 900 bis 1.300, also 4 bis 6 Minuten Lesezeit) als Regelfall, wenn die Quellenlage es trägt; sonst Kurzmeldung (150 bis 350 Wörter, eine offizielle Quelle reicht, Abschnitt 0), statt nichts zu veröffentlichen**, in eigenen Worten neu geschrieben (nicht nacherzählt), redaktionell mit Einordnung, Vorgeschichte und Folgen für den Ort, nicht generisch; dünne Themen bündeln statt strecken; jede Quelle im Text genannt; Updates in bestehende Einträge; Termine ohne `entwurf` (nur bei Unsicherheit `"entwurf": true`); Umkreis nur die sechs Orte; Sport als Einheit. **Bildklasse so wählen, dass das Bild zeigt, worum es geht** (Abschnitt 4a). Selbstprüfung nach SCHREIBREGELN.md Abschnitt 13.

### Phase 4: Paketierung, Build, Bild, Commit (15 Minuten)

1. Dateien: `inhalte/meldungen/<datum>-recherche-claude-<lauf>.json` (`{"hinweis": …, "redaktionsstandard": 2, "meldungen": […]}`), `inhalte/termine/<datum>-recherche-claude-<lauf>.json`, `inhalte/umkreis/<datum>.json`. Updates direkt in der Datei des Bestandsartikels, Einrückung erhalten.
2. `node deploy/kette.mjs` zweimal, `node deploy/kette.mjs --check` (muss „alle Generatoren unveraendert“ melden), `node qa/pruefung.mjs` (0 Fehler), `node deploy/register-pflege.mjs` (schreibt `offen.md`, mitcommitten), `node qa/redaktion-lauf.mjs` (0 Fehler; prüft Länge, drei Quellen, Prüfprotokoll und Bild), `node qa/eigenformulierung.mjs --datum <datum> --lauf <lauf>` (0 Fehler; nicht abrufbare Quellen ins Dossier).
3. Bild: Meldet die Prüfung „Ortsansicht statt Themenbild“, Abschnitt 4a anwenden, dann Schritt 2 wiederholen.
4. Commit mit Inhalten und allen erzeugten Dateien (`chatgpt-site/`, `wordpress-delivery/`, `wordpress/`, `docs/SHA256SUMS.txt`, `deploy/sitemap-stand.json`); Nachricht `redaktion: <Lauf> <TT.MM.> – <Themen>`; Attributionszeilen.
5. `git fetch origin main`, `git rebase origin/main`, `git push -u origin main`. Nie `--force`. **Sofort pushen** (Entscheidung Luis Kummer, 02.11.2026): Jeder fertige Arbeitsschritt (Inhalte, Korrektur, Termine, Dokumentation, Live-Nachweis) wird direkt committet und auf main gepusht, nicht gesammelt; danach `git status` sauber und `git log origin/main..main` leer.

Abnahme: Push erfolgreich, `kette --check` auf dem gepushten Stand unverändert. Reaktionen: Prüfung rot → nur eigene Pakete korrigieren oder herausnehmen, nie Prüfung, Generator oder Workflow ändern. Rebase-Konflikt in generierten Dateien (häufig `docs/SHA256SUMS.txt`) → `git checkout --ours <datei>` (beim Rebase ist „ours“ der Stand von main), Kette erneut, `git add`, `git rebase --continue`. Uncommittete Arbeit vorher mit `git stash push -u` sichern. Konflikt in Inhalten → Abbruch, Bericht.

### Phase 4a: Passendes Bild

Das Bild muss zeigen, worum es im Artikel geht (Sport zeigt Sport, ein Sturm zeigt Sturmschaden, kein Fachwerkhaus). **Jeder neue und jeder geänderte Artikel wird einzeln geprüft**: `node qa/redaktion-lauf.mjs --bilder --datum <datum> --lauf <lauf>` zeigt je Meldung Motiv, Foto und Lizenz; das zugewiesene Foto (`chatgpt-site/assets/editorial-pools/…`) selbst öffnen und ansehen. Passt es nicht, Schritte 1 bis 3. Die Tabelle mit der Spalte „passt, weil“ kommt ins Dossier (Abschnitt „Bildprüfung“, ⚙).

1. Passende vorhandene Bildklasse in `deploy/bildklassen.json` wählen; Motive je Foto stehen in `deploy/bildmotive.json`.
2. Gibt es im Pool kein passendes Foto, holt der Lauf selbst eines: auf Wikimedia Commons suchen (freie Lizenz: CC0, CC BY, CC BY-SA, gemeinfrei; kein NC/ND; mindestens 1000 px breit; keine erkennbaren Personen im Mittelpunkt, keine lesbaren Kennzeichen, keine fremden Ortsnamen als Hauptmotiv). Den Dateititel in den Pool `themen` in `deploy/import-editorial-photos.mjs` eintragen, `node deploy/import-editorial-photos.mjs --nur=themen`, `python3 deploy/pool-varianten.py`.
3. Jedes neue Foto selbst ansehen (Datei öffnen). Erst danach Titel in `deploy/editorial-photo-review.json` (`freigegeben`) und Motiv in `deploy/bildmotive.json`; Motiv der Klasse in `deploy/bildklassen.json` ergänzen. Ungeeignete Fotos in `ausgeschlossen` mit Grund.
4. Keine KI-generierten Fotos. Ortsansicht nur, wenn wirklich kein Motiv das Thema trifft (Prüfung meldet dann nur einen Hinweis).

### Phase 5: Dokumentation (5 Minuten)

1. Dossier `docs/redaktion/dossiers/<datum>-claude-<lauf>.md` nach [vorlagen/dossier-claude.md](vorlagen/dossier-claude.md).
2. `register.json` nach [REGISTER-SCHEMA.md](REGISTER-SCHEMA.md): bearbeitete Themen fortschreiben (`uebernommen` nach Live-Nachweis in Phase 6, sonst `zur_abnahme` mit Artikelpfad), neue anlegen, `research_runs`-Eintrag, `latest_dossier`.
3. `latest.md` neu schreiben; `node deploy/register-pflege.mjs` (schreibt `offen.md`).
4. `node qa/redaktion-lauf.mjs --datum <datum> --lauf <lauf>` (0 Fehler).
5. Commit `docs(redaktion): Dossier <Lauf> <TT.MM.>, Register, latest.md und offen.md`, Push wie Phase 4.

### Phase 6: Abgleich und Live-Nachweis

1. `curl -sS https://merzenich-aktuell.de/?ma_api=abgleich-anstoss` (Antwort `angestossen: true`, oder `false` mit `naechster`, wenn der Workflow schon angestoßen hat), danach `curl -sS "https://merzenich-aktuell.de/wp-cron.php?doing_wp_cron=$(date +%s)"` (HTTP 200).
2. Nachweis: neue Slugs bzw. geänderte Sätze in `https://raw.githubusercontent.com/HKGrowthOperator/Merzenich-Aktuell/main/wordpress-delivery/merzenich-aktuell-import.xml` (`grep -c`).
3. Live: Für jede neue Claude-Meldung ohne `zurFreigabe` prüfen, ob `https://merzenich-aktuell.de/<ressort>/<slug>/` mit HTTP 200 antwortet (veröffentlicht; Entwürfe antworten 404). Bei 200 im Register `uebernommen` mit Datum in `notes`; bei 404 im Bericht nennen (Option im Plugin aus oder Abgleich noch nicht gelaufen), im Register `zur_abnahme`.
4. Backend nur, wenn der Zugang in der laufenden Sitzung ausdrücklich bereitgestellt ist (Umgebungsgeheimnis oder vom Nutzer genannt; Quelle im Dossier nennen, nie den Wert). Login per Benutzername, nach einem Fehlversuch abbrechen.

### Phase 7: Abschlussbericht

Nach [vorlagen/abschlussbericht.md](vorlagen/abschlussbericht.md) an Luis: Zahlen aus dem Dossier (nicht neu schätzen), was live gegangen ist (mit Links), was zur Freigabe liegt und warum, Korrekturen, Bilder (welches Motiv je Artikel), offene Entscheidungen, Commits, Abgleich. Aufbau Wurzel (Quellen), Stamm (Repository), Krone (Sichtbares).

## 5. Definition of Done

- [ ] Korrekturaufträge der neuen Dossiers zu Claude-Artikeln erledigt oder begründet offen
- [ ] Mindesttakt: mindestens eine Meldung in diesem Lauf, wenn eine belegbare Nachricht existiert; Tagesziel zwei (Live-Archiv gezählt)
- [ ] Jede neue oder geänderte Meldung: Mindestwerte, drei Quellen (Kurzmeldung: eine offizielle), `stand` = Lesedatum, jede Quelle im Text genannt
- [ ] Jede neue Meldung mindestens 800 Wörter (Standard) oder 150 bis 350 (Kurzmeldung), in eigenen Worten (`qa/eigenformulierung.mjs` 0 Fehler), Prüfprotokoll vollständig
- [ ] Jede neue Meldung mit passendem Themenbild, Bild selbst angesehen, Lizenz frei, Tabelle „Bildprüfung“ im Dossier
- [ ] Unsichere Beiträge mit `zurFreigabe`/`entwurf` und Begründung im Dossier
- [ ] Kette zweimal, `--check` unverändert, `qa/pruefung.mjs` 0 Fehler, `qa/redaktion-lauf.mjs --datum --lauf` 0 Fehler
- [ ] Ein Inhalts- und ein Dokumentationscommit auf main gepusht (bei Nullfund nur der Dokumentationscommit), ohne Force
- [ ] Lauf endet mit sauberem Arbeitsverzeichnis: `git status` ohne Änderungen, `git log origin/main..main` leer; nachträgliche Ergänzungen (z. B. Live-Nachweis) sofort als eigener Commit gepusht
- [ ] Dossier, Register, `latest.md`, `offen.md` konsistent
- [ ] Abgleich angestoßen, Nachweis in der Import-XML, Live-URLs geprüft
- [ ] Bericht an Luis mit gezählten Zahlen

## 6. Harte Verbote

Fremde Beiträge (Automatik, Vereine, Partner, ChatGPT-Pakete) nie veröffentlichen; „Sofort veröffentlichen“ (gilt für alle Beiträge) nie einschalten; keine Bilder ohne geprüfte Lizenz und eigene Sichtung; keine KI-generierten Fotos; keine Zugangsdaten in Dateien; keine erfundenen Zeitstempel, Adressen, URLs; keine Privatpersonen; kein Force-Push; keine Änderungen an Workflows, Scrapern, Generatoren, Theme oder Plugin im Redaktionslauf, ausgenommen die Bildpflege nach Phase 4a (Pool `themen`, `bildmotive.json`, `bildklassen.json`, `editorial-photo-review.json`); von der Kette erzeugte Dateien unter `wordpress/` und `wordpress-delivery/` werden mitcommittet; keine fremden Volltexte.

## 7. Nullfund-Lauf

Ein Nullfund ist nur erlaubt, wenn keine Pflichtquelle eine belegbare Nachricht liefert; eine Polizei-, Feuerwehr- oder Gemeindemitteilung mit Merzenich-Bezug, ein Ratsbeschluss, eine Sperrung oder ein Termin der nächsten sieben Tage mit Nachrichtenwert ist kein Nullfund, sondern mindestens eine Kurzmeldung. Dann: kein Meldungspaket, aber Dossier mit Zahlentabelle („Neue Meldungen | 0“), Quellenabschnitt mit Weg und Ergebnis **je Pflichtquelle** (Quellenprotokoll) und offenen Punkten; Register `research_runs` mit `implementation_commit: null` und Begründung in `summary`; Bericht „Nullfund“ mit Quellenlücken. Die Zeit für Vertiefungen eigener Artikel nutzen (SCHREIBREGELN.md Abschnitt 6). **Mengenvorgabe (Entscheidung Luis Kummer 02.11.2026):** mindestens eine Meldung je Lauf, wenn eine belegbare Nachricht existiert, und mindestens zwei Meldungen je Tag.

## 8. Störungen

| Störung | Reaktion |
|---|---|
| Quelle gesperrt (403, 429, JavaScript-Prüfung) | Umweg aus QUELLEN.md; sonst Lücke im Dossier, nach drei Läufen in offene Punkte für Menschen |
| Kein ChatGPT-Dossier seit dem letzten Lauf | ohne Zulieferung arbeiten, im Dossier und Bericht vermerken |
| Rohdaten älter als 12 Stunden | nur Live-Abrufe, im Dossier vermerken |
| Commons nicht erreichbar | Bildklasse mit vorhandenem Motiv wählen; sonst Ortsansicht mit Hinweis im Dossier, Foto im nächsten Lauf nachholen |
| Push scheitert (403, Netz) | bis zu vier Wiederholungen mit Pause; bei 403 `add_repo` mit Push-Zugriff; sonst Arbeit lokal sichern und im Bericht an erster Stelle nennen |
| Sitzungs- oder Zeitlimit droht | bereits Geprüftes paketieren und pushen, Dossier knapp mit „abgebrochen nach Phase …“; nächster Lauf holt Phase 5 nach |
| Lauf überschneidet sich mit einer zweiten Claude-Sitzung | vor jedem Commit `git fetch`; dieselbe Meldung nie in zwei Sitzungen; Register zeigt, wer was umgesetzt hat |
| Widersprüchliche Quellen | beide Angaben benennen und `zurFreigabe`, oder Thema auf T2 |
| Live-URL antwortet 404 | Option im Plugin prüfen lassen (Bericht), Register `zur_abnahme` |

## 9. Kennzahlen je Lauf (im Dossier)

Neue Meldungen nach Typ, durchschnittliche Wortzahl, Anteil Standard und länger, Quellen je Meldung (Ziel mindestens 3), davon live und zur Freigabe, Korrekturen an eigenen Artikeln, Termine (live/Entwurf), Umkreis, Bilder (Themenbild/Ortsansicht, neu importiert, gesichtet), Dauer des Laufs. Nichts hochrechnen.

## 10. Änderungshistorie

| Version | Datum | Änderung |
|---|---|---|
| 1.0 | 26.10.2026 | Kurzfassung: Texte, Quellen, Ablauf in sieben Schritten |
| 1.1 | 26.10.2026 | Register-Lagebild (`register-pflege.mjs`, `offen.md`), Umkreis-Automatik (Parallelsitzung) |
| 1.2 | 27.10.2026 | Sofort sichtbare Pfade, Termine nur als Entwurf |
| 2.0 | 27.10.2026 | Vollständige Laufdefinition mit Phasen, Abnahmen, Störungen; `qa/redaktion-lauf.mjs`; QUELLEN.md, SCHREIBREGELN.md, REGISTER-SCHEMA.md, Vorlagen |
| 3.1 | 28.10.2026 | Länge (Standard ab 800 Wörtern), Prüfdefinition mit Prüfprotokoll `pruefung`, Eigenformulierung (`qa/eigenformulierung.mjs`), Bildprüfung je Artikel im Dossier |
| 3.2 | 30.10.2026 | Phase 1a Kalenderpflege (60 Tage, neu/geändert/abgesagt/bestätigt, `"abgesagt": true`, Prüfstand `quelle.stand`), ChatGPT-Termine 60 Tage, Läufe in fester Redaktionssitzung |
| 3.8 | 03.11.2026 | Entscheidung Luis Kummer: Feuerwehr-Automatik und Heimat-Info-Termine direkt online, Relevanz aus der Bildklasse; Freigabe nur noch bei rechtlichem Risiko; „Neu in Merzenich“ in der Spalte „Nachrichten aus Merzenich“ (Theme 21.26.0) |
| 3.7 | 03.11.2026 | Entscheidung Luis Kummer: Eil-Prüfung alle zwei Stunden zwischen den Läufen (Abschnitt 2a); „Merzenich“ in jedem Titel (`qa/redaktion-lauf.mjs`); Alter und Wohnort bei Blaulicht so, wie die Polizei sie nennt (SCHREIBREGELN Abschnitt 7) |
| 3.6 | 02.11.2026 | Entscheidung Luis Kummer: Heimat-Info-Veranstaltungskalender der Gemeinde in jedem Lauf vollständig prüfen und zum Auffüllen des Terminkalenders nutzen (Phase 1a, Schritt 2a); Automatik-Termine bleiben Entwurf; Prüfung in `qa/redaktion-lauf.mjs` |
| 3.5 | 02.11.2026 | Entscheidung Luis Kummer: Top-Meldung mit `relevanz` 1 wird automatisch Aufmacher (Plugin 1.41.0 löst festen Aufmacher ab), Folgemeldung bei neuer Entwicklung mit eigenem Nachrichtenwert |
| 3.4 | 02.11.2026 | Entscheidung Luis Kummer: Sofort pushen, jeder fertige Schritt direkt auf main, Lauf endet mit sauberem Arbeitsverzeichnis; Störung „Push scheitert“ |
| 3.3 | 02.11.2026 | Entscheidung Luis Kummer: Mindesttakt (zwei Meldungen je Tag, eine je Lauf bei belegbarer Nachricht), Kurzmeldung 150 bis 350 Wörter mit einer offiziellen Quelle, offizielle Quelle genügt für Direktveröffentlichung (kein T2 wegen fehlender Zweitquelle), Nullfund nur mit Quellenprotokoll; `qa/redaktion-lauf.mjs` meldet 24 Stunden ohne Meldung |
| 3.0 | 28.10.2026 | Entscheidungen Luis Kummer: Claude-Beiträge direkt online (fremde weiter über Freigabe), `zurFreigabe` bei Unsicherheit; Standard ab 450 Wörtern, drei Quellen; Bild muss zum Thema passen (Phase 4a, Pool `themen`); ChatGPT-Auftrag mit Korrekturen zuerst; Läufe 09:13 und 14:43 in frischer Sitzung |
