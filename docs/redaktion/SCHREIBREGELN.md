# Schreib- und Prüfregeln der KI-Redaktion

Stand: 02.11.2026. Ergänzt [docs/REDAKTIONELLER-STANDARD-V2.md](../REDAKTIONELLER-STANDARD-V2.md) (Artikeltypen, Pflichtfragen, Paketformat) und gilt für jede Meldung, jeden Termin und jeden Umkreis-Eintrag aus einem Claude-Lauf. Ablauf: [CLAUDE-LAUF.md](CLAUDE-LAUF.md). Quellen: [QUELLEN.md](QUELLEN.md). Maschinell geprüft wird, was mit ⚙ markiert ist (`node qa/redaktion-lauf.mjs`, für Claude-Pakete; Updates in älteren Fremddateien prüft das Skript nicht, dort gilt die Selbstprüfung).

Grundsatz: **Was nicht an einer Primärquelle gelesen wurde, steht nicht im Text. Lieber weniger, dafür richtig.**

Seit 28.10.2026 gehen Claude-Beiträge ohne Freigabe online (CLAUDE-LAUF.md Abschnitt 3). Daraus folgen zwei Ansprüche zugleich: **länger und redaktioneller** (Einordnung, Vorgeschichte, Bedeutung für den Ort, nicht generisch) und **mehrere Quellen, im Text genannt**. Die Länge kommt aus mehr belegten Fakten, nie aus Füllsätzen.

## 0. Mindesttakt und Kurzmeldung

Entscheidung Luis Kummer, 02.11.2026. Anlass: Vom 28.10. bis 02.11.2026 erschien keine redaktionelle Meldung, `news-sitemap.xml` war leer, Google News und Discover lagen bei null. Ursache waren Mindestlänge und Drei-Quellen-Regel ohne Mengenvorgabe: Trug ein Thema keine 800 Wörter, erschien nichts. Am 02.11. wurde ein Einbruch mit Polizeimitteilung auf T2 gesperrt, nur weil eine unabhängige Zweitquelle fehlte.

- **Mindesttakt:** an jedem Tag mindestens **zwei Meldungen**, davon mindestens eine aus Blaulicht, Rathaus oder Termine-Vorschau. Jeder Lauf veröffentlicht **mindestens eine Meldung, wenn eine belegbare Nachricht existiert**. Ein Nullfund ist nur mit vollständigem Quellenprotokoll erlaubt (CLAUDE-LAUF.md Abschnitt 7). ⚙ Hinweis bei 24 Stunden ohne Meldung und bei weniger als zwei Meldungen am Lauftag
- **Kurzmeldung** (`kurzmeldung`), seit 04.11.2026 **250 bis 450 Wörter in mindestens 4 Absätzen** (Blaulicht-Eilmeldung ab 200 Wörtern, 3 Absätze), für: Einsatz (Polizei, Feuerwehr), Sperrung, Ratsbeschluss, Terminhinweis mit Einordnung (auch Wochenvorschau), Ergebnis vom Wochenende. Entscheidung Luis Kummer 03.11.2026: „Beiträge länger … immer eigene Beiträge, redaktionell“. Jede Kurzmeldung hat neben dem Kern einen Absatz **Einordnung** (Vorgeschichte aus dem eigenen Archiv, frühere Vorlagen, Beschlüsse oder Zahlen) und einen Absatz **Wie es weitergeht** (nächster Termin, Frist, wer entscheidet, wo man mitreden kann). Außerhalb von Blaulicht ist sie die Ausnahme: Trägt die Quellenlage mit Vorgeschichte und Folgen einen Standardtext, wird es ein Standardtext. ⚙
- **Eine offizielle Quelle reicht** (Abschnitt 3a Nr. 1: Polizei, Feuerwehr, Gemeinde, Kreis, Amtsblatt, Verband) oder eine Selbstauskunft des Vereins über eigene Termine und Ergebnisse. Die Zwei- und Drei-Quellen-Regel (Abschnitt 4) gilt für Kurzmeldungen nicht. ⚙
- **Geprüft wird genauso wie bei Standard-Meldungen**: Prüfprotokoll mit mindestens drei Aussagen, jede Zahl darin, keine Privatpersonen, keine Hausnummern, keine Deutung als Tatsache. Was die Quelle nicht sagt, steht als offen im Text.
- Der **Standard mit 800 Wörtern und mehr** bleibt für Hintergrund, Porträt, Analyse und alle Themen, deren Quellenlage es trägt. Eine Kurzmeldung kann im nächsten Lauf zum Standardtext ausgebaut werden (Update, kein zweiter Artikel).
- **Eilmeldung:** Blaulicht und Sperrungen möglichst bald nach der Quelle als Kurzmeldung, später ergänzen (Abschnitt 5, Nachtrag).
- **Bestand:** Auch von Hand in WordPress angelegte Beiträge zählen (Live-Archiv `https://merzenich-aktuell.de/archiv/` prüfen, nicht nur `inhalte/`); dazu keine Dublette schreiben.

## 0a. Top-Meldung (Aufmacher) und Folgemeldung

Entscheidung Luis Kummer, 02.11.2026: Oben auf der Startseite steht automatisch immer die stärkste aktuelle Nachricht.

- **Feld `relevanz`** an jeder Claude-Meldung: `1` Top, `2` Standard (Vorgabe ohne Angabe), `3` Service oder Hinweis. Der Import schreibt daraus `ma_relevanz`; das Plugin (ab 1.41.0) stellt eine frische Stufe-1-Meldung als Aufmacher, löst dafür auch einen von Hand fest gesetzten Aufmacher ab und lässt sie nach `frisch_stunden` (72 Stunden) automatisch abrücken.
- **Stufe 1 bekommt die stärkste Nachricht des Laufs**, wenn sie großen Nachrichtenwert für Merzenich hat: Tote oder Schwerverletzte, Großbrand, Vollsperrung einer wichtigen Straße oder der Bahn, Festnahme nach einer Tat im Ort, Ratsbeschluss mit spürbaren Folgen für viele, Großereignis oder Ausfall eines Großereignisses. Höchstens eine Meldung je Lauf; im Zweifel `2`. Im Dossier steht die Begründung.
- Voraussetzungen für den Aufmacherplatz (Theme): kein Sport, kein Tipp, echtes Themenfoto mit mindestens 480 px Breite (keine Ortsansicht). Sport bleibt auch mit Stufe 1 in der Sportrubrik.
- **Folgemeldung statt nur Update:** Eine neue Entwicklung mit eigenem Nachrichtenwert zu einem älteren Artikel (Tod nach einem Unfall, Festnahme, Urteil, Beschluss, Absage, Ergebnis) wird eine **neue Kurzmeldung** mit neuem Datum, damit sie als Nachricht erscheint (Startseite, News-Sitemap). Der ältere Artikel bekommt einen Nachtrag mit Hinweis auf die Folgemeldung; sein Titel beschreibt dann das ursprüngliche Ereignis, damit keine zwei gleichlautenden Überschriften stehen. Kleine Ergänzungen (Uhrzeit, Ort, Korrektur) bleiben ein Update (Abschnitt 5). Abgestimmt mit `docs/SEO-KEYWORDS.md` Regel 5 (03.11.2026).

## 1. Welcher Artikeltyp?

Entscheidung in dieser Reihenfolge:

1. Gibt es nur ein Datum, eine Frist oder einen Ort und sonst nichts Belegbares? → **Termin** in `inhalte/termine/` statt Meldung. Kommt ein Nachrichtenwert dazu (neue Regel, geänderter Ort, Ausfall), zusätzlich eine Meldung.
2. Einsatz, Sperrung, Ratsbeschluss, Terminhinweis, Ergebnis oder reiner Service mit Frist oder Kontakt, und die Quellenlage trägt keine 800 Wörter? → `kurzmeldung` nach Abschnitt 0 (150 bis 350 Wörter, 3 Absätze, eine offizielle Quelle reicht), bei Service mit `"service": true`. Eine Blaulicht-Kurzmeldung wird im nächsten Lauf nachgefasst oder zum Standardtext ausgebaut. ⚙ Hinweis bei Kurzmeldungen in Wirtschaft oder Menschen und über 350 Wörtern
3. Normale Lokalnachricht mit tragender Quellenlage → `standard`. **Das ist der Regelfall.** Eine Nachricht wird nie zurückgehalten, nur weil sie keinen Standardtext trägt; dann ist sie eine Kurzmeldung.
4. Vorgang mit Vorgeschichte (frühere Beschlüsse, Vorjahreszahlen), Folgen für Ortsteile und mindestens drei Primärquellen? → `vertiefung`.
5. Einordnung über mehrere Vorgänge oder Jahre → `hintergrund`.

**Länge der Claude-Artikel** (Entscheidung Luis Kummer 28.10.2026: bisher ein bis zwei Minuten Lesezeit, das ist zu kurz). ⚙ ab Paketen vom 29.10.2026:

| Typ | Mindestens | Ziel | Lesezeit |
|---|---|---|---|
| `kurzmeldung` | 250 Wörter, 4 Absätze (Blaulicht-Eilmeldung 200 Wörter, 3 Absätze); höchstens 450, ab Paketen vom 04.11.2026 | 300 bis 400 | 2 Minuten |
| `standard` | **800 Wörter, 7 Absätze** | 900 bis 1.300 | 4 bis 6 Minuten |
| `vertiefung` | 1.100 Wörter, 8 Absätze | 1.200 bis 1.800 | 6 bis 9 Minuten |
| `hintergrund` | 1.400 Wörter, 10 Absätze | 1.500 bis 2.500 | 7 bis 12 Minuten |

Die Länge kommt aus **mehr Recherche**, nie aus Füllstoff: Vorgeschichte (frühere Beschlüsse, Vorjahre, ähnliche Fälle im Ort), Zahlen im Vergleich (Haushalt, Statistik, Vorjahr), Folgen für die Ortsteile, Stimmen aus öffentlichen Stellungnahmen (Rat, Verwaltung, Verein, mit Quelle), was offen ist, wie es weitergeht, Service. Trägt ein Thema allein keine 800 belegten Wörter, wird es **nicht künstlich gestreckt**, sondern als Kurzmeldung veröffentlicht (Abschnitt 0), mit verwandten Themen zu einem Sammelartikel gebündelt (z. B. „Was in Merzenich bis Monatsende ansteht“, „Neues aus dem Rathaus“), als Termin angelegt oder als Update in einen bestehenden Artikel geschrieben.

Länge entsteht nur aus zusätzlichen belegten Fakten. Verboten sind Füllsätze, Wiederholungen von Titel oder Dek im Text, allgemeine Aussagen ohne Merzenich-Bezug und Mutmaßungen über Gründe. ⚙ Mindestwerte je Typ.

## 2. Aufbau einer Meldung

| Feld | Regel |
|---|---|
| `titel` | Nachricht in einem Satz, 50 bis 110 Zeichen, konkret (Ort, Zahl, Datum); kein Fragezeichen-Köder, keine Doppelpunkt-Ketten. **„Merzenich“ in jedem Titel (auch „Merzenicher“ oder „Ortsteil (Merzenich)“), Ort und Ereignis in den ersten 60 Zeichen, mit den Wörtern der Suchenden** (Unfall, Polizeieinsatz, Brand, Sperrung, Veranstaltung), Ortsteil bei Mehrdeutigkeit mit „(Merzenich)“, kein „heute“ im Titel. Titelregeln und Suchbegriffe: [docs/SEO-KEYWORDS.md](../SEO-KEYWORDS.md) |
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

## 3a. Prüfdefinition: Was in den Text darf

Weil Claude-Beiträge ohne Freigabe online gehen, darf **nichts Falsches** erscheinen. Eine Aussage darf nur in den Text, wenn sie eine dieser Bedingungen erfüllt:

1. **Offizielle Quelle:** Sie steht bei der Stelle, die es wissen muss: Gemeinde, Kreis, Polizei, Land, Bund, Ratsinformationssystem, Amtsblatt, Verband (FUSSBALL.DE, click-TT), Verkehrsunternehmen, Kammer, Bistum oder Feuerwehr (Liste `OFFIZIELL` in `qa/redaktion-lauf.mjs`), **oder**
2. **Zwei unabhängige Belege:** Sie steht übereinstimmend bei zwei verschiedenen Herausgebern (nicht dieselbe Pressemitteilung zweimal), **oder**
3. **Selbstauskunft:** Sie ist eine Angabe eines Veranstalters oder Vereins über sich selbst (eigener Termin, eigene Öffnungszeit, eigenes Ergebnis auf der eigenen Seite); dann `selbstauskunft` mit Begründung und im Text so kennzeichnen („nach Angaben des Vereins“).

Alles andere fliegt raus oder wird als offen benannt („geht aus den Unterlagen nicht hervor“). Vermutungen, Gerüchte, Social-Media-Beiträge ohne Bestätigung und eigene Schlüsse sind keine Belege.

**Eine offizielle Quelle ist ein vollständiger Beleg.** Stammen die tragenden Aussagen aus einer offiziellen Quelle (Nr. 1), wird das Thema nicht wegen einer fehlenden unabhängigen Zweitquelle auf T2 gesetzt, nicht zurückgehalten und nicht mit `zurFreigabe` versehen. Beispiel: Zeugenaufruf der Polizei Düren zum Einbruch an der Bahnstraße, 02.11.2026; die Polizeimitteilung trägt die Kurzmeldung allein. T2 bleibt für widersprüchliche Quellen, Vorwürfe gegen Personen und Angaben ohne offizielle Quelle.

**Prüfprotokoll im Paket** (Feld `pruefung`, ⚙ ab Paketen vom 29.10.2026): jede tragende Aussage einzeln, mindestens sechs (Kurzmeldung drei), jede mit den Belegen aus `quelle`/`weitereQuellen` und der Fundstelle. **Jede Zahl im Text** (Beträge, Uhrzeiten, Daten, Mengen; Jahreszahlen ausgenommen) muss in einer Aussage des Protokolls stehen, sonst meldet das Prüfskript einen Fehler.

```json
"pruefung": [
  {"aussage": "Der Gemeinderat bewilligte am 16.10.2026 bis zu 257.000 Euro für einen Einsatzleitwagen.",
   "belege": ["https://ratsinfo.gemeinde-merzenich.de/…", "https://www.gemeinde-merzenich.de/…"],
   "stelle": "Beschlussvorlage 123/2026, S. 2; Niederschrift TOP 7"},
  {"aussage": "Der Verein trainiert ab 4. November dienstags um 18 Uhr in der Weinberghalle.",
   "belege": ["https://www.verein-beispiel.de/training"], "stelle": "Trainingszeiten, Stand 28.10.2026",
   "selbstauskunft": "Verein nennt eigene Trainingszeiten"}
]
```

## 4. Quellen

- `quelle` ist die Primärquelle, `weitereQuellen` die weiteren; jede mit `name` („Herausgeber, Dokument oder Seite, Datum“), `url` (https) und `stand` (Lesedatum YYYY-MM-DD). ⚙
- **Mindestens drei verschiedene Quellen** je Meldung, **davon mindestens zwei außerhalb von merzenich-aktuell.de**; ausgenommen Kurzmeldungen mit offizieller Quelle (Abschnitt 0). ⚙ (drei Quellen ab Paketen vom 29.10.2026) Gute dritte Quellen: frühere Beschlüsse oder Vorlagen, Haushaltsplan, amtliche Statistik (IT.NRW), Satzung, Richtlinie, Pressemitteilung eines zweiten Beteiligten.
- **Jede Quelle wird im Text genannt** („laut Beschlussvorlage 123/2026“, „teilt die Kreisverwaltung mit“, „heißt es im Amtsblatt vom …“). Leser sollen sehen, woher jede Angabe stammt.
- Zwei Quellen außerhalb von merzenich-aktuell.de bleiben die harte Untergrenze. ⚙ Zweite Quelle kann sein: zweites Dokument desselben Vorgangs (Vorlage plus Beschlusstext), ein anderer Herausgeber, ein amtlicher Rahmen (Verordnung, Richtlinie). Ein eigener Artikel auf merzenich-aktuell.de ist nur Kontext.
- Ausnahme von der Drei- und Zwei-Quellen-Regel nur mit Feld `quellenausnahme` (Begründung, z. B. „einzige amtliche Bekanntmachung, keine weitere Veröffentlichung“) in der Meldung; sie steht im Dossier im Abschnitt „Umgesetzt“ unter „Quellenausnahmen“.
- Stammen alle Quellen von derselben Domain, meldet das Prüfskript einen Hinweis; im Dossier begründen oder eine unabhängige Quelle ergänzen.

## 5. Update, Nachtrag, Korrektur

- Vor dem Schreiben Bestand prüfen: `docs/redaktion/offen.md` (möglicher Bestand je Thema), `grep -l '<Stichwort>' inhalte/meldungen/*.json`, `chatgpt-site/api/inhalte.json`.
- Neues zum selben Vorgang ist ein **Update** des bestehenden Eintrags: neuer Absatz „Nachtrag vom <Datum>: …“, Dek anpassen, zusätzlicher Fakt, Quelle ergänzen. `slug`, `ressort` und `datum` bleiben.
- **Korrektur** (falsche oder unbelegte Angabe): Satz ersetzen, nicht ergänzen; im Dossier unter „Umgesetzt“ mit altem und neuem Wortlaut; Register-Notiz.
- Neue Claude-Beiträge sowie Updates und Korrekturen an **veröffentlichten** Beiträgen und Terminen gehen beim nächsten Abgleich **ohne Freigabe** online (CLAUDE-LAUF.md Abschnitt 3). Deshalb dort nur geprüfte Änderungen, im Abschlussbericht ausdrücklich nennen; meldet das Abgleich-Protokoll „von Hand geändert“, erreicht die Änderung WordPress nicht, dann den neuen Wortlaut an die Redaktion geben.
- Neuer Artikel nur, wenn es ein neuer Vorgang ist (neuer Beschluss, neues Ereignis) oder eine Folgemeldung nach Abschnitt 0a.

## 6. Vertiefung bestehender Artikel

Eigene Artikel unter den Mindestlängen (Standard 800 Wörter) oder mit weniger als drei Quellen werden in ruhigen Läufen (Nullfund, wenig Neues) nachrecherchiert und ausgebaut; `qa/redaktion-lauf.mjs` listet sie als Hinweise.

Ein `standard` wird `vertiefung`, wenn die zusätzliche Recherche mindestens drei Primärquellen, fünf belegte Fakten und Vorgeschichte oder Einordnung liefert. Ändern dürfen sich `artikeltyp`, `dek`, `absaetze`, `fakten`, `weitereQuellen`, `themen`. Jede neue Angabe hat eine geprüfte Fundstelle (Recherchedatei mit URL und Stelle). Trägt die Lage keine Vertiefung, bleibt der Typ; im Dossier steht der Grund.

## 7. Personen und Datenschutz

- Genannt werden dürfen Amts- und Funktionsträger in ihrer Funktion (Bürgermeister, Beigeordnete, Pfarrer, Wehrleiter, Vereinsvorsitzende, Trainer) und Menschen, die öffentlich auftreten (Referenten, Künstler).
- Nie: Schüler, Verletzte, Beschuldigte, Anwohner, Brautpaare und Jubilare, private Gastgeber, Teilnehmer. Auch nicht, wenn die Gemeinde sie in Heimat-Info nennt.
- Blaulicht: keine Namen, keine Hausnummern, keine Kennzeichen. Alter und Wohnort von Beteiligten nur so, wie die Polizei sie in ihrer Pressemitteilung nennt, auch im Titel („22-Jähriger aus Euskirchen“); nichts darüber hinaus (Arbeitgeber, Familie, Fotos, Angaben aus sozialen Netzwerken). Entscheidung Luis Kummer 03.11.2026. (⚙ Hinweis nur für das Muster „…straße/weg/platz + Zahl“; alles andere Selbstprüfung)
- Kontaktadressen nur als Funktionsadresse aus der Quelle.

## 8. Eigene Formulierung, Zitate und Fremdtexte

Jeder Text ist **neu geschrieben**: Aus den Quellen werden nur Fakten entnommen, Aufbau, Einstieg, Sätze und Gewichtung sind eigene redaktionelle Arbeit. Pressemitteilungen, Vereinstexte und Amtsblatt werden nicht nacherzählt oder umgestellt, sondern als Faktenbasis genutzt und mit weiteren Quellen zu einem eigenen Bericht verbunden.

**Nie kopieren (Entscheidung Luis Kummer 03.11.2026):** Kein Satz aus einer Pressemitteilung, Vereinsseite, Instagram- oder Facebook-Beitrag, keinem anderen Medium (Aachener Zeitung, Radio Rur, news.de und andere) und keiner KI-Zusammenfassung wird übernommen, auch nicht umgestellt. Andere Medien sind nie Quelle, sondern höchstens Hinweis auf eine Originalquelle. Fotos aus sozialen Netzwerken werden nie verwendet. Die Prüfung ist seit 03.11.2026 strenger (höchstens 9 gleiche Wörter in Folge, höchstens 6 Prozent gleiche Wortfolgen je Quelle). Auch die Feuerwehr-Automatik zitiert höchstens zwei Sätze und nennt ihre Quelle im Text.

Wörtliche Zitate höchstens zwei Sätze, in „…“, mit Urheber. ⚙ `node qa/eigenformulierung.mjs --datum <d> --lauf <l>` lädt alle Quellen und meldet einen Fehler bei 12 oder mehr gleichen Wörtern in Folge außerhalb eines Zitats oder wenn mehr als 10 Prozent der Wortfolgen gleich sind.

## 9. Sport

Ergebnis, offenes Spiel, nächstes Spiel und Tabelle sind eine Einheit mit einem Datenzeitpunkt in `chatgpt-site/api/sport-current.json` (sofort sichtbar, siehe CLAUDE-LAUF.md). Ergebnisse nur aus Klartextquellen; Spielvorschauen nach Anpfiff aktualisieren oder sperren; Jugendspieler ohne Namen.

## 10. Termine

Pflichtfelder wie in `deploy/termine.mjs`; `start`/`ende` ISO mit Zeitzone; `ortsteil` aus Merzenich, Golzheim, Girbelsrath, Morschenich, Bürgewald, Gemeinde, Umgebung; `kategorie` aus dem Bestand (Politik, Kirche, Brauchtum, Info, Vereine, Umwelt, Kultur, Senioren, Sport, Jugend); `quelle` als `{name, url, stand}`. Claude-Termine gehen **direkt online**; `"entwurf": true` nur, wenn Datum, Ort oder Durchführung nicht sicher belegt sind (dann Grund im Dossier). ⚙ Keine vergangenen Termine (⚙ nur im Paket des Laufs mit `--datum`); Dublette gegen `inhalte/termine/` prüfen (gleicher Slug überschreibt still). ⚙

Kalenderpflege (30.10.2026, CLAUDE-LAUF.md Phase 1a): `quelle.stand` ist das Datum, an dem der Termin zuletzt an der Quelle gelesen wurde, und wird bei jeder Bestätigung neu gesetzt; Termine der nächsten 14 Tage mit älterem Stand meldet die QA als „nachpruefen“. Abgesagte Termine bekommen `"abgesagt": true` (true oder false ⚙) und bleiben stehen. Verschiebungen ändern den bestehenden Slug, mit Hinweis „Verschoben vom …“ in der Beschreibung.

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
6. Mindestens drei Quellen, davon zwei außerhalb merzenich-aktuell.de (Kurzmeldung: eine offizielle Quelle), jede im Text genannt?
7. `stand` = heutiges Lesedatum?
8. Keine Privatperson, keine Hausnummer im Blaulicht?
9. Bestand geprüft, kein Doppelartikel?
10. Artikeltyp passt zur Faktenlage, Mindestwerte erfüllt (Standard ab 800 Wörtern, Kurzmeldung 150 bis 350)? Beim Standard Vorgeschichte und Einordnung enthalten?
10a. Mindesttakt: Wie viele Meldungen sind heute schon erschienen (Live-Archiv)? Unter zwei: belegbare Kurzmeldung schreiben statt Nullfund.
11. Titel konkret, Dek ohne unbelegte Aussage?
12. Letzter Absatz mit Ausblick oder Service?
13. Zitate wörtlich und gekennzeichnet, alles andere in eigenen Worten (`qa/eigenformulierung.mjs` 0 Fehler)?
13a. Prüfprotokoll vollständig: jede Aussage offiziell oder doppelt belegt, jede Zahl im Protokoll?
14. Bild zeigt das Thema (keine Ortsansicht, wo ein Motiv passt)? Bilddatei selbst angesehen, Lizenz frei (CC0, CC BY, CC BY-SA, gemeinfrei)?
15. Unsicheres mit `zurFreigabe` bzw. Termin mit `"entwurf": true` und Grund im Dossier?
16. Widersprüche zwischen Quellen benannt?

## Beispiel aus dem Bestand (27.10.2026)

Der ELW-Artikel vom 26.10. enthielt „Vergeben ist der Auftrag noch nicht“ (Gegenwartsbehauptung ohne Beleg zum Lesedatum) und „Die Obergrenze ist kein Zufall … sodass die Gemeinde national ausschreiben kann“ (eigene Deutung, die Vorlage nennt keinen Grund). Korrigiert zu: „Ob ein Auftrag inzwischen erteilt ist, geht aus den veröffentlichten Unterlagen nicht hervor“ und „Ob die Gemeinde das Limit deshalb so gesetzt hat, steht in der Beschlussvorlage nicht“, mit der EU-Kommission als Quelle für den Schwellenwert.
