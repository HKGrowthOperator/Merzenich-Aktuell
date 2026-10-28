# Schreib- und Prüfregeln der KI-Redaktion

Stand: 28.10.2026. Ergänzt [docs/REDAKTIONELLER-STANDARD-V2.md](../REDAKTIONELLER-STANDARD-V2.md) (Artikeltypen, Pflichtfragen, Paketformat) und gilt für jede Meldung, jeden Termin und jeden Umkreis-Eintrag aus einem Claude-Lauf. Ablauf: [CLAUDE-LAUF.md](CLAUDE-LAUF.md). Quellen: [QUELLEN.md](QUELLEN.md). Maschinell geprüft wird, was mit ⚙ markiert ist (`node qa/redaktion-lauf.mjs`, für Claude-Pakete; Updates in älteren Fremddateien prüft das Skript nicht, dort gilt die Selbstprüfung).

Grundsatz: **Was nicht an einer Primärquelle gelesen wurde, steht nicht im Text. Lieber weniger, dafür richtig.**

Seit 28.10.2026 gehen Claude-Beiträge ohne Freigabe online (CLAUDE-LAUF.md Abschnitt 3). Daraus folgen zwei Ansprüche zugleich: **länger und redaktioneller** (Einordnung, Vorgeschichte, Bedeutung für den Ort, nicht generisch) und **mehrere Quellen, im Text genannt**. Die Länge kommt aus mehr belegten Fakten, nie aus Füllsätzen.

## 1. Welcher Artikeltyp?

Entscheidung in dieser Reihenfolge:

1. Gibt es nur ein Datum, eine Frist oder einen Ort und sonst nichts Belegbares? → **Termin** in `inhalte/termine/` statt Meldung. Kommt ein Nachrichtenwert dazu (neue Regel, geänderter Ort, Ausfall), zusätzlich eine Meldung.
2. Blaulicht-Erstmeldung oder reiner Service mit Frist oder Kontakt, Quellenlage gibt nicht mehr her? → `kurzmeldung` (mindestens 80 Wörter, 2 Absätze, 2 Fakten), bei Service mit `"service": true`. Eine Blaulicht-Kurzmeldung wird im nächsten Lauf nachgefasst oder zum Standardtext ausgebaut. ⚙ Hinweis bei Kurzmeldungen außerhalb Blaulicht/Service
3. Normale Lokalnachricht → `standard`, **für Claude mindestens 450 Wörter**, 4 Absätze, 4 Fakten und drei Quellen. **Das ist der Regelfall.** ⚙ (ab Paketen vom 29.10.2026)
4. Vorgang mit Vorgeschichte (frühere Beschlüsse, Vorjahreszahlen), Folgen für Ortsteile und mindestens drei Primärquellen? → `vertiefung` (450/5/5).
5. Einordnung über mehrere Vorgänge oder Jahre → `hintergrund` (700/7/5), nur auf ausdrücklichen Auftrag.

Länge entsteht nur aus zusätzlichen belegten Fakten. Verboten sind Füllsätze, Wiederholungen von Titel oder Dek im Text, allgemeine Aussagen ohne Merzenich-Bezug und Mutmaßungen über Gründe. ⚙ Mindestwerte je Typ.

## 2. Aufbau einer Meldung

| Feld | Regel |
|---|---|
| `titel` | Nachricht in einem Satz, 50 bis 110 Zeichen, konkret (Ort, Zahl, Datum); kein Fragezeichen-Köder, keine Doppelpunkt-Ketten |
| `kicker` | `Ressort · Unterthema`, z. B. „Rathaus · Feuerwehr“ |
| `dek` | ein bis drei Sätze mit dem Neuen; keine Behauptung, die im Text nicht belegt ist |
| `absaetze` | 1 Nachricht und Beleg (wer sagt was, laut welcher Quelle), 2 Zahlen und Einzelheiten, 3 Vorgeschichte (frühere Beschlüsse, Vorjahr, ähnliche Fälle im Ort), 4 Einordnung und Bedeutung für Merzenich oder den Ortsteil (wen betrifft es, was ändert sich konkret), 5 was offen ist (ausdrücklich: „geht aus den Unterlagen nicht hervor“), letzter Absatz „Wie geht es weiter?“ oder Service (Anmeldung, Kontakt, Fristen) |
| `fakten` | je Eintrag eine belegte Angabe als kurzer Satz, wenn möglich mit Fundstelle in Klammern („Pfarrbrief 08/26, S. 2“) |
| `themen` | `[["slug", "Label"], …]`, Slugs klein, mit Bindestrich, bestehende Themen bevorzugen (`grep -oh '\["[a-z0-9-]*"' inhalte/meldungen/*.json`) |
| `datum` | ISO mit Zeitzone (`+01:00` MEZ, `+02:00` MESZ), Zeitpunkt der Erstellung, nicht in der Zukunft (⚙ toleriert höchstens einen Tag) |
| `bildklasse` | aus `deploy/bildklassen.json`; **das Bild muss zeigen, worum es geht** (Sport zeigt Sport, Sturm zeigt Sturmschaden, kein Fachwerkhaus). Fehlt ein passendes Foto, nach CLAUDE-LAUF.md Phase 4a beschaffen. ⚙ Ortsansicht trotz Motivklasse ist ein Fehler; Klasse ohne Motiv nur, wenn wirklich kein Motiv passt ([BILD-MOTIVREGELN.md](../BILD-MOTIVREGELN.md)) |
| `zurFreigabe` | optional `true`: Meldung geht als Entwurf in die Freigabe statt direkt online (eine tragende Angabe nur aus einer Quelle, Quellen widersprechen sich, Vorwürfe gegen Personen, T2); Grund im Dossier |
| `service` | optional `true`: reine Servicemeldung (Frist, Anmeldung, Kontakt), darf Kurzmeldung sein |

## 3. Verifikationsprotokoll je Faktentyp

| Faktentyp | Woran gelesen | Gilt als Beleg | Bei Widerspruch |
|---|---|---|---|
| Datum, Uhrzeit | Veranstalter- oder Behördenseite, Kalender (ICS), Amtsblatt | wörtliche Angabe mit Wochentag passend zum Datum | beide Angaben nennen, Quelle je Angabe; nicht glätten |
| Zahl, Betrag | Beschlusstext, Vorlage, Haushaltsplan, Statistik | Zahl in der Quelle; Rechnungen (brutto aus netto) als solche kennzeichnen | Originalzahl nennen, Rechnung erklären |
| Name, Funktion | Impressum, Behördenseite, Beschlusstext | nur Amts- und Funktionsträger in ihrer Funktion | Funktion statt Name |
| Zitat | Originaltext | wörtlich, in Anführungszeichen, mit Urheber | kein Zitat |
| Sportergebnis, Tabelle | FUSSBALL.DE-Datenbericht, fupa mit „Abpfiff“, click-TT | Klartext mit Endstand; Tabelle mit Datenstand | „Ergebnis noch nicht bestätigt“ |
| Termin, Ort, Adresse | Veranstalterdetail | Ort und Straße der Veranstaltungsstätte | beide nennen, Termin auf T2 |
| Frist | Ausschreibung, Pressemitteilung | wörtliche Frist; abgelaufene Fristen nie als offen darstellen | Frist weglassen, Kontakt nennen |
| Beschluss, Drucksache | Beschlusstext (Niederschrift), Vorlage | Nummer, Datum, Gremium, Abstimmung | Beschlusstext geht vor Vorlage |
| Gegenwartszustand („noch nicht vergeben“, „ist geöffnet“) | nur eine Quelle, die den Zustand **zum Lesedatum** belegt | Zustand mit Datum der Quelle | „geht aus den bis zum … veröffentlichten Unterlagen nicht hervor“ |
| Begründung, Absicht | nur ausdrückliche Aussage der Quelle | Zitat oder Paraphrase mit Quelle | keine eigene Deutung als Tatsache |

Relative Zeitangaben („vergangene Woche“, „gestern“) werden durch Daten ersetzt.

## 4. Quellen

- `quelle` ist die Primärquelle, `weitereQuellen` die weiteren; jede mit `name` („Herausgeber, Dokument oder Seite, Datum“), `url` (https) und `stand` (Lesedatum YYYY-MM-DD). ⚙
- **Mindestens drei verschiedene Quellen** je Meldung, **davon mindestens zwei außerhalb von merzenich-aktuell.de**. ⚙ (drei Quellen ab Paketen vom 29.10.2026) Gute dritte Quellen: frühere Beschlüsse oder Vorlagen, Haushaltsplan, amtliche Statistik (IT.NRW), Satzung, Richtlinie, Pressemitteilung eines zweiten Beteiligten.
- **Jede Quelle wird im Text genannt** („laut Beschlussvorlage 123/2026“, „teilt die Kreisverwaltung mit“, „heißt es im Amtsblatt vom …“). Leser sollen sehen, woher jede Angabe stammt.
- Zwei Quellen außerhalb von merzenich-aktuell.de bleiben die harte Untergrenze. ⚙ Zweite Quelle kann sein: zweites Dokument desselben Vorgangs (Vorlage plus Beschlusstext), ein anderer Herausgeber, ein amtlicher Rahmen (Verordnung, Richtlinie). Ein eigener Artikel auf merzenich-aktuell.de ist nur Kontext.
- Ausnahme von der Drei- und Zwei-Quellen-Regel nur mit Feld `quellenausnahme` (Begründung, z. B. „einzige amtliche Bekanntmachung, keine weitere Veröffentlichung“) in der Meldung; sie steht im Dossier im Abschnitt „Umgesetzt“ unter „Quellenausnahmen“.
- Stammen alle Quellen von derselben Domain, meldet das Prüfskript einen Hinweis; im Dossier begründen oder eine unabhängige Quelle ergänzen.

## 5. Update, Nachtrag, Korrektur

- Vor dem Schreiben Bestand prüfen: `docs/redaktion/offen.md` (möglicher Bestand je Thema), `grep -l '<Stichwort>' inhalte/meldungen/*.json`, `chatgpt-site/api/inhalte.json`.
- Neues zum selben Vorgang ist ein **Update** des bestehenden Eintrags: neuer Absatz „Nachtrag vom <Datum>: …“, Dek anpassen, zusätzlicher Fakt, Quelle ergänzen. `slug`, `ressort` und `datum` bleiben.
- **Korrektur** (falsche oder unbelegte Angabe): Satz ersetzen, nicht ergänzen; im Dossier unter „Umgesetzt“ mit altem und neuem Wortlaut; Register-Notiz.
- Neue Claude-Beiträge sowie Updates und Korrekturen an **veröffentlichten** Beiträgen und Terminen gehen beim nächsten Abgleich **ohne Freigabe** online (CLAUDE-LAUF.md Abschnitt 3). Deshalb dort nur geprüfte Änderungen, im Abschlussbericht ausdrücklich nennen; meldet das Abgleich-Protokoll „von Hand geändert“, erreicht die Änderung WordPress nicht, dann den neuen Wortlaut an die Redaktion geben.
- Neuer Artikel nur, wenn es ein neuer Vorgang ist (neuer Beschluss, neues Ereignis).

## 6. Vertiefung bestehender Artikel

Eigene Artikel unter 450 Wörtern oder mit weniger als drei Quellen werden in ruhigen Läufen (Nullfund, wenig Neues) nachrecherchiert und ausgebaut; `qa/redaktion-lauf.mjs` listet sie als Hinweise.

Ein `standard` wird `vertiefung`, wenn die zusätzliche Recherche mindestens drei Primärquellen, fünf belegte Fakten und Vorgeschichte oder Einordnung liefert. Ändern dürfen sich `artikeltyp`, `dek`, `absaetze`, `fakten`, `weitereQuellen`, `themen`. Jede neue Angabe hat eine geprüfte Fundstelle (Recherchedatei mit URL und Stelle). Trägt die Lage keine Vertiefung, bleibt der Typ; im Dossier steht der Grund.

## 7. Personen und Datenschutz

- Genannt werden dürfen Amts- und Funktionsträger in ihrer Funktion (Bürgermeister, Beigeordnete, Pfarrer, Wehrleiter, Vereinsvorsitzende, Trainer) und Menschen, die öffentlich auftreten (Referenten, Künstler).
- Nie: Schüler, Verletzte, Beschuldigte, Anwohner, Brautpaare und Jubilare, private Gastgeber, Teilnehmer. Auch nicht, wenn die Gemeinde sie in Heimat-Info nennt.
- Blaulicht: keine Hausnummern, keine Kennzeichen, keine Altersangaben mit Ort, die eine Person erkennbar machen. (⚙ Hinweis nur für das Muster „…straße/weg/platz + Zahl“; alles andere Selbstprüfung)
- Kontaktadressen nur als Funktionsadresse aus der Quelle.

## 8. Zitate und Fremdtexte

Wörtliche Zitate höchstens zwei Sätze, gekennzeichnet, mit Urheber. Pressemitteilungen werden nicht nacherzählt, sondern als Quelle für Fakten genutzt; eigener Einstieg, eigene Gliederung.

## 9. Sport

Ergebnis, offenes Spiel, nächstes Spiel und Tabelle sind eine Einheit mit einem Datenzeitpunkt in `chatgpt-site/api/sport-current.json` (sofort sichtbar, siehe CLAUDE-LAUF.md). Ergebnisse nur aus Klartextquellen; Spielvorschauen nach Anpfiff aktualisieren oder sperren; Jugendspieler ohne Namen.

## 10. Termine

Pflichtfelder wie in `deploy/termine.mjs`; `start`/`ende` ISO mit Zeitzone; `ortsteil` aus Merzenich, Golzheim, Girbelsrath, Morschenich, Bürgewald, Gemeinde, Umgebung; `kategorie` aus dem Bestand (Politik, Kirche, Brauchtum, Info, Vereine, Umwelt, Kultur, Senioren, Sport, Jugend); `quelle` als `{name, url, stand}`. Claude-Termine gehen **direkt online**; `"entwurf": true` nur, wenn Datum, Ort oder Durchführung nicht sicher belegt sind (dann Grund im Dossier). ⚙ Keine vergangenen Termine (⚙ nur im Paket des Laufs mit `--datum`); Dublette gegen `inhalte/termine/` prüfen (gleicher Slug überschreibt still). ⚙

## 11. Umkreis

Nur die Orte Düren, Niederzier, Nörvenich, Elsdorf, Kerpen und Kreis Düren (andere Kreisorte unter „Kreis Düren“ mit Ortsnennung im Titel). ⚙ Ein Satz mit dem Kern, URL der Detailseite, `datum` der Quelle. Polizeimeldungen legt `deploy/automatik-umkreis.mjs` selbst an; im Lauf nicht doppelt anlegen. Umkreis ist sofort sichtbar.

## 12. Sprache

Deutsch, kurze Sätze, aktive Verben. Zahlen deutsch (257.039 Euro, 18 Uhr, 3. Dezember). Keine Werbesprache, keine Superlative ohne Beleg, keine Floskeln („in der heutigen Zeit“, „spannend“, „ein voller Erfolg“, „sorgt für“, „lädt herzlich ein“, „auf Hochtouren“, „in den Startlöchern“, „rundum“, „ein Zeichen setzen“). Nicht generisch: Jeder Absatz enthält etwas, das nur für diesen Vorgang in Merzenich gilt (Ort, Zahl, Datum, Name einer Funktion, Beschluss). Ein Satz, der in jedem Lokalblatt stehen könnte, wird gestrichen. Ortsnamen korrekt: Merzenich, Golzheim, Girbelsrath, Morschenich, Bürgewald; Bürgewald und Morschenich bei Ort, Bild und Termin strikt unterscheiden.

## 13. Selbstprüfung je Meldung vor dem Build

1. Jede Zahl an der Primärquelle gelesen?
2. Jedes Datum mit Wochentag geprüft?
3. Jede Gegenwartsaussage zum Lesedatum belegt?
4. Keine eigene Deutung als Tatsache?
5. Keine relative Zeitangabe?
6. Mindestens drei Quellen, davon zwei außerhalb merzenich-aktuell.de, jede im Text genannt?
7. `stand` = heutiges Lesedatum?
8. Keine Privatperson, keine Hausnummer im Blaulicht?
9. Bestand geprüft, kein Doppelartikel?
10. Artikeltyp passt zur Faktenlage, Mindestwerte erfüllt (Standard ab 450 Wörtern)? Vorgeschichte und Einordnung enthalten?
11. Titel konkret, Dek ohne unbelegte Aussage?
12. Letzter Absatz mit Ausblick oder Service?
13. Zitate wörtlich und gekennzeichnet?
14. Bild zeigt das Thema (keine Ortsansicht, wo ein Motiv passt)?
15. Unsicheres mit `zurFreigabe` bzw. Termin mit `"entwurf": true` und Grund im Dossier?
16. Widersprüche zwischen Quellen benannt?

## Beispiel aus dem Bestand (27.10.2026)

Der ELW-Artikel vom 26.10. enthielt „Vergeben ist der Auftrag noch nicht“ (Gegenwartsbehauptung ohne Beleg zum Lesedatum) und „Die Obergrenze ist kein Zufall … sodass die Gemeinde national ausschreiben kann“ (eigene Deutung, die Vorlage nennt keinen Grund). Korrigiert zu: „Ob ein Auftrag inzwischen erteilt ist, geht aus den veröffentlichten Unterlagen nicht hervor“ und „Ob die Gemeinde das Limit deshalb so gesetzt hat, steht in der Beschlussvorlage nicht“, mit der EU-Kommission als Quelle für den Schwellenwert.
