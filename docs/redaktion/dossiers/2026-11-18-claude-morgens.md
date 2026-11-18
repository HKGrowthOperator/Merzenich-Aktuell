# Merzenich Aktuell – Morgenlauf (Claude), 18. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung 09:14 bis 09:35 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 17.11. Sonderauftrag Tamilische Feste](2026-11-17-claude-tamilische-feste.md) und [Claude 17.11. nachmittags](2026-11-17-claude-nachmittags.md), seither neu [ChatGPT 18.11. morgens](2026-11-18-morgens.md). main vor dem Lauf: `0b142307`. Eil-Prüfungen 17.11. 16:42, 18:42, 20:41 Uhr und 18.11. 06:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Standard „Bürgewald-Ausschuss in Merzenich“ (891 Wörter) |
| Korrigierte Bestandsmeldungen | 1 | Ausstellung Bürgewald (ChatGPT-Korrektur MA-KORR-NEULAND-HFA-EMPFEHLUNG-1811) |
| Aktualisierte Bestandsmeldungen | 1 | Sitzungstermine bis Jahresende (Rat 3.12. jetzt 18 Uhr) |
| Neue / geänderte Termine | 1 / 3 | neu: Wunschbaum 21.11.; geändert: Gemeinderat 3.12. (17 → 18 Uhr), HFA 19.11. und Bürgewald-Ausschuss 26.11. (Tagesordnung ergänzt) |
| Termine an der Quelle bestätigt | 22 | alle Termine 18.11. bis 03.12. außer dem bereits begonnenen Frühstückstreff 18.11. (Detailseite HTTP 404) |
| Bilder gefunden / geprüft / integriert | 1 / 1 / 1 | vorhandenes Commons-Foto Bürgewald (Pool `themen`, `themen-25-7e1a7afdf3`) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs --datum 2026-11-18 --lauf morgens` 0 Fehler, 1 Hinweis (9 Wörter Aufzählung der Planungswerkstätten mit NRW.URBAN gleich) | |

Mindesttakt 18.11.: 1 Meldung im Morgenlauf.

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-KORR-NEULAND-HFA-EMPFEHLUNG-1811 (ChatGPT morgens) | Korrektur im Ausstellungsartikel, letzter Sachabsatz. Alt: „Er soll den Wirtschafts- und Stellenplan 2027/2028 der Neuland Hambach GmbH genehmigen. Die Verwaltung empfiehlt das, …“ Neu: „Er soll dem Gemeinderat empfehlen, den Wirtschafts- und Stellenplan 2027/2028 der Neuland Hambach GmbH zu genehmigen; entscheiden soll der Rat am Donnerstag, 3. Dezember. Die Verwaltung befürwortet die Genehmigung, …“ Dazu Korrekturhinweis „Korrigiert am 18. November: …“, Faktenliste und Prüfeintrag | `inhalte/meldungen/2026-11-17-recherche-claude-nachmittags.json` → `/leben/ausstellung-buergewald-morschenich-alt-kunst-fotografie-film-ratssaal-merzenich-27-november-2026/` | Vorlage 151/2026 S. 1 um 09:15 Uhr selbst gelesen: Beratungsfolge HFA 19.11., Gemeinderat 03.12., Beschlussvorschlag „Der Haupt- und Finanzausschuss empfiehlt:“; 301 Wörter; Commit `996cacce` |
| MA-BUERGEWALD-AUSSCHUSS-2611 (Folge zu MA-BAUTURBO-142-2026 und MA-SOLARPARK-A4-E3) | Standard „Bürgewald-Ausschuss in Merzenich: Solarpark an der A4 und Bau-Turbo kommen am 26. November auf den Tisch“ (Rathaus, Relevanz 2, 891 Wörter): neue Ergänzung 142/2026 (Bau-Turbo zusätzlich im Bürgewald-Ausschuss wegen der „Sondersituation“, Abstimmung mit den „Sonderambitionen“), Solarpark E3 mit Beteiligungsmodell für die Energieversorgung von Bürgewald, Bericht NRW.URBAN (M144/2026), Titel der nichtöffentlichen Punkte (Baufeldpässe erster Vermarktungsabschnitt, Bestandsgebäude, Grundstückspakete, Vorkaufsoptionisten, Erbbaurechtsverträge, Dialogprozess „Vielzutun“), Einordnung nach der NRW.URBAN-Projektseite | `inhalte/meldungen/2026-11-18-recherche-claude-morgens.json` → `/rathaus/buergewald-ausschuss-merzenich-26-november-2026-solarpark-a4-bau-turbo-vermarktung-erster-abschnitt/` | Bekanntmachung (2 Seiten), Sitzungspaket (30 Seiten), Ergänzung 142/2026 (3 Seiten), M144/2026, alle 09:15 Uhr; Sitzungsseite Bauausschuss 09:22 Uhr (keine Niederschrift); NRW.URBAN-Projektseite gegen 09:20 Uhr; sechs Prüfeinträge (vier mit offizieller Quelle, zwei Selbstauskunft); Commit `4d85e4e7` |
| Kalender | Gemeinderat 3.12. jetzt 18 Uhr; Tagesordnungen HFA und Bürgewald-Ausschuss; Wunschbaum 21.11.; 22 Termine bestätigt | 17 Termindateien; Meldung `2026-10-25-recherche-claude.json` (Sitzungstermine) mit „Aktualisiert am 18. November“ | Abschnitt 6; Commit `230e24ae` |
| Quellen | Tamilischer Kunst- und Kulturverein im Vereinsverzeichnis der Stadt Jülich in `QUELLEN.md` 3.9a ergänzt (ohne private Kontaktdaten) | `docs/redaktion/QUELLEN.md` | Seite 09:28 Uhr selbst gelesen |

Bewusst nicht als T2 behandelt: ChatGPT stuft die Bau-Turbo-Karte als „T2 – gesperrt“ ein, weil nur Dokumente der Gemeinde vorliegen. Nach SCHREIBREGELN trägt eine offizielle Quelle (Ratsinformationssystem) eine Meldung über eine angekündigte Beratung; der Artikel behauptet keine Beschlüsse, sondern berichtet über Tagesordnung und Vorlagen und kennzeichnet die NRW.URBAN-Angaben als deren Darstellung.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Meldung, Korrektur und Termine ohne Freigabe (kein rechtliches Risiko). Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Ergebnisse Bauausschuss 12.11. (Grundschule Golzheim, Solarpark Golzheim, Poolplatz) | Niederschrift im Ratsinformationssystem am 18.11. um 09:22 Uhr nicht abrufbar; einziges belegtes Ergebnis (Bau-Turbo zusätzlich im Bürgewald-Ausschuss) steht in der neuen Meldung |
| Vorschau HFA 19.11. | alle öffentlichen Punkte bereits als Einzelmeldungen und in der Wochenvorschau; Ergebnisse nach der Sitzung |
| Wunschbaum als Meldung | nur Hinweis der Gemeinde ohne Veranstalter, Abgabefrist und beteiligte Einrichtungen; als Termin mit offen benannten Lücken eingetragen (Abschnitt 6), Vorjahreszahl „1.004 Herzenswünsche“ als Angabe der Veranstalter nicht übernommen |
| Malwettbewerb-Bildbeschreibung (MA-KORR-MALWETTBEWERB-BILDBESCHREIBUNG-1411) | im Repository korrigiert, live weiter „Polizeiwache“: Alt-Text bestehender Poolanhänge wird vom Abgleich nicht aktualisiert, Werkzeugpunkt, Entscheidung Luis Kummer offen |
| Tamilische Feste, ChatGPT-Hinweis „Originalabgleich unvollständig“ | Drik Panchang, Zentralbank Sri Lanka und Haindavam am 17.11. zwischen 15:07 und 15:09 Uhr selbst gelesen (Dossier Sonderauftrag); die 403-Antworten an ChatGPT sind kein Fehlerhinweis |
| Familientag Burg Nideggen (Kreis, 17.11.) | Ort Nideggen, kein Merzenich-Bezug |
| Polizei | Dienststellenliste 09:28 Uhr unverändert (zuletzt 17.11. 09:00 Uhr, Düren, B56) |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 18.11. morgens, zuerst KORREKTUREN | eine neue Korrektur umgesetzt; Bau-Turbo-Karte als Meldung umgesetzt; Wunschbaum als Termin; Jülicher Vereinseintrag übernommen |
| Ratsinformationssystem | Vorlage 151/2026, Ergänzung 142/2026, Bekanntmachung und Sitzungspaket Bürgewald-Ausschuss, M144/2026 (09:15 Uhr); Sitzungsseiten Bauausschuss (09:22), Gemeinderat und HFA (09:24/09:25 Uhr); `termine.ics` (09:24 Uhr) | Rat 3.12. 18 Uhr statt 17 Uhr; Rat noch ohne Tagesordnung |
| NRW.URBAN | Projektseite Masterplan Bürgewald, gegen 09:20 Uhr | Einordnung der neuen Meldung |
| Heimat-Info | Veranstaltungskalender und zehn Detailseiten 09:23 Uhr; Beiträge Pluriversum, Ausstellung, Wunschbaum 09:15 bis 09:26 Uhr | Abschnitt 6 |
| Polizei Düren | Dienststellenliste nr/8, 06:41 und 09:28 Uhr | nichts Neues |
| Feuerwehr | Website und Heimat-Info-Profil, 06:41 Uhr | weiter bis 147/26 |
| Kreis Düren | Presse-Index 09:28 Uhr; Mental-Load-Anmeldeseite 09:26 Uhr | Familientag Nideggen; Vortrag ausgebucht (bekannt) |
| Stadt Jülich | Vereinsverzeichnis, Tamilischer Kunst- und Kulturverein, 09:28 Uhr | QUELLEN.md 3.9a |

## 5. Sport

Mittwoch, kein Pflichttag. Nächstes Spiel mit Merzenicher Beteiligung: Girbelsrath bei Viktoria Arnoldsweiler II, Fr. 20.11., 19.30 Uhr (Freitagsprüfung).

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender der Gemeinde (https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen) um 09:23 Uhr gelesen: zehn IDs; gegenüber dem 17.11. fehlt die abgelaufene Veranstaltung `671697c5` (17.11.), wieder im Listenfenster ist `1e0ecb73` (Weihnachtsschießen Morschenich 06.12., schon im Kalender). Alle neun übrigen Detailseiten textgleich mit dem 17.11. Detailseite `0d9701e3` (Frühstückstreff heute 9 Uhr) HTTP 404 nach Beginn: kein Absagebeleg, Termin unverändert. Amtsblätter 08, 11, 12 und Kulturkalender 16 bytegleich, Kreis-Seiten Mental Load und Seniorenweihnacht, Schützen Morschenich und Pluriversum-Beitrag textgleich mit dem 17.11.

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Gemeinderat, 8. Sitzung | gemeinderat-sitzung-2026-12-03 | **geändert**: Beginn 17 Uhr → 18 Uhr (`termine.ics` 17.11. 13:49 Uhr UTC noch 17:00, 18.11. 08:24 Uhr UTC 18:00; Sitzungsseite „Do, 03.12.2026 18:00 Uhr“), noch ohne Tagesordnung | Ratsinformationssystem | 2026-11-18 |
| Haupt- und Finanzausschuss 19.11. | haupt-finanzausschuss-sitzung-2026-11-19 | ergänzt: Tagesordnung (TOP 5 bis 15), Link | Sitzungsseite | 2026-11-18 |
| Ausschuss Revitalisierung Bürgewald 26.11. | ausschuss-revitalisierung-buergewald-2026-11-26 | ergänzt: öffentliche und nichtöffentliche Themen, Link | Bekanntmachung | 2026-11-18 |
| Wunschbaum-Weihnachtsaktion | wunschbaum-weihnachtsaktion-seniorenheime-fussballhalle-merzenich-2026-11-21 | **neu**: Sa. 21.11. ab 11 Uhr, Fußballhalle, Auf der Heide; Veranstalter in der Ankündigung nicht genannt (im Termin so vermerkt), Baum eine Woche | Gemeindebeitrag Heimat-Info 17.11. 12:50 Uhr | 2026-11-18 |
| übrige 19 Termine vom 18.11. bis 03.12. (Senioren- und Skatrunden, Pluriversum-Workshop, Seniorennachmittag, Mental Load, Sprechstunde, Modelleisenbahn, Gottesdienst, Seniorenweihnacht, Ausstellung, Tannenbaumschmücken, Adventfeier, Adventmarkt, Weinbergschnecken, Adventsfenster, Golzheimer Adventskalender, Adventsliedersingen, Abgabeschluss Amtsblatt, Jugendgremium) | je Slug | bestätigt | jeweilige Quelle | 2026-11-18 |

**Neu:** 1. **Geändert:** 3. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `996cacce` (Korrektur), `4d85e4e7` (Meldung), `230e24ae` (Kalender), Dokumentation in diesem Commit. Abgleich nach jedem Commit angestoßen (`angestossen: true`), zuletzt 09:29 Uhr; wp-cron HTTP 200.

Live-Nachweis 09:29 Uhr: neue Meldung HTTP 200 mit Text (unter anderem „Sonderambitionen“, „Vorkaufsoptionisten“) und Foto `themen-25-7e1a7afdf3`, in der Nachrichten-Sitemap, unter „Neu in Merzenich“ auf der Startseite und in `wordpress-delivery/merzenich-aktuell-import.xml` auf GitHub. Korrektur im Ausstellungsartikel live („Er soll dem Gemeinderat empfehlen …“, „Korrigiert am 18. November“). Termine 09:31 Uhr live: Wunschbaum auf /termine/ mit offen vermerktem Veranstalter; Terminseite `/termine/gemeinderat-sitzung-2026-12-03/` HTTP 200 mit 18 Uhr und Änderungshinweis.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| buergewald-ausschuss-merzenich-26-november-2026-solarpark-a4-bau-turbo-vermarktung-erster-abschnitt | leben.buergewald | morschenich-alt-leerstand | Unterstraße Morschenich 01 Juli 2021.jpg | CC BY-SA 4.0 | selbst angesehen (09:23 Uhr): leere Dorfstraße mit Backsteinhäusern im früheren Morschenich-Alt, Juli 2021; zeigt den Ort, um dessen Wiederbelebung es im Ausschuss geht; Symbolbild, kein Foto der Sitzung; keine Personen, kein lesbares Kennzeichen |

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Bürgewald-Ausschuss | 891 | 7 | 6 Einträge: vier mit Ratsinformationssystem, NRW.URBAN und Ausstellung als Selbstauskunft | 9 Wörter (Aufzählung der Werkstattmonate), nach Umformulierung eines 12-Wort-Treffers |
| Ausstellung Bürgewald (Korrektur) | 301 | 6 | Prüfeintrag Vorlage 151/2026 um Beschlussvorschlag und 3. Dezember ergänzt | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Mindesttakt 18.11.: 1 Meldung; Nachmittagslauf.
- HFA 19.11.: Empfehlungen nach der Sitzung als Folgemeldungen (Abgaben, Übernachtungssteuer, Neuland, Kita, Brandschutzbedarfsplan).
- Bauausschuss 12.11.: Niederschrift abwarten (Grundschule Golzheim, Solarparks).
- Gemeinderat 3.12.: Tagesordnung abwarten (Termin live mit 18 Uhr).
- Wunschbaum: Veranstalter und Abgabefrist nachtragen, sobald belegt.
- Tamilischer Kunst- und Kulturverein: Luis Kummer fragen, ob Kontakt gewünscht; keine Termine ohne datierte Ankündigung.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
