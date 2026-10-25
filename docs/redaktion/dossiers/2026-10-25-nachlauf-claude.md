# Merzenich Aktuell – Nachlauf-Dossier (Claude), 25. Oktober 2026, abends

Arbeitsmaterial für Claude Code, abgelegt nach `docs/redaktion/README.md`. Dieses Dossier entstand parallel zum [Abenddossier](2026-10-25-abends.md) und wurde **mit diesem zusammengeführt umgesetzt** (siehe Abschnitt „Umsetzung“ am Ende und `register.json`). Zeitangaben in diesem Text stammen aus der Containeruhr der Claude-Sitzung und können gegenüber der tatsächlichen Uhrzeit nachgehen; das Quellenfenster lag am Nachmittag und Abend des 25.10.2026 (MEZ).


**Stand:** Sonntag, 25. Oktober 2026, Recherche 15:15 bis etwa 17:30 Uhr (Europe/Berlin, MEZ; die Sommerzeit endete heute um 3 Uhr)
**Für:** Claude-Code-Sitzung am Repository `HKGrowthOperator/Merzenich-Aktuell`, Branch `main`
**Erstellt von:** Claude (KI-Redaktion), ohne Repository-Änderung, ohne Commit, ohne Push, ohne Veröffentlichung, ohne Backend-Login
**Letzter Vergleichsstand:** Meldungen auf merzenich-aktuell.de bis 19.10.2026 (Ölspur 141/26), letzte Redaktionsmeldungen 16./17.10.2026, letztes ChatGPT-Dossier 14.10.2026 (34 Karten, eingearbeitet in `inhalte/meldungen/2026-10-14-recherche-chatgpt.json`), Terminbestand `inhalte/termine/` bis 23.01.2027

---

## 0. Kurzfassung in Zahlen (tatsächlich erreicht)

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Ressorts systematisch geprüft | 12 von 12 | Abdeckung je Ressort in Abschnitt 5 |
| Themenkarten gesamt | 33 | 12 Neu/Update, 9 Termin, 8 Nachfassen/Rechercheauftrag, 4 Hintergrund/Befund/gesperrt/vorbei (Details Abschnitt 1) |
| Vollständige, veröffentlichungsnahe Textentwürfe | 16 | 10 geeignet, 6 bedingt (mit benannten Prüfpunkten); Ziel 15 bis 25 knapp erreicht |
| Karten ohne Text (Nachfassen, Termin-Einträge, Hintergrund, Befunde) | 17 | je mit Grund und konkreter Frage |
| Bildkandidaten | 4 gefunden, 0 lizenzgeprüft, 0 sichtgeprüft, 0 integriert | Commons-API gedrosselt (HTTP 429); Ziel 10 bis 20 nicht erreicht, Abschnitt 7 |

Keine Zahl in diesem Dossier ist hochgerechnet. Fußball-Sonntagsergebnisse sind Zwischenstände (Spiele liefen bei Redaktionsschluss noch).

## Arbeitsweise

- **RESEARCH:** Rohdaten des Quellenabrufs von heute 09:47 bis 09:56 Uhr UTC (`imports/quellen/*.json`: Feuerwehr, Presseportal Polizei Düren, Gemeinde, Heimat-Info, Schulen, Stellen, Trauer) plus eigene Live-Abrufe: Heimat-Info (Liste und Einzelbeiträge), Ratsinformationssystem (`termine.ics`), Kreis Düren Presse, Neuland Hambach, Pfarrei St. Laurentius, Autobahn-API, fupa.net, Wikimedia-Commons-API. Zeitfenster: zuerst 23. bis 25.10., dann 11. bis 25.10. für laufende Entwicklungen, dann Termine bis 23.12.2026.
- **VERIFY:** Jede Aussage im Text ist an der genannten Quelle gelesen worden. Was nur in einer Liste oder einem Teaser stand, ist als „Teaser“ markiert. Namen von Privatpersonen sind weggelassen; genannt werden nur Amts- und Funktionsträger sowie Künstler, die öffentlich auftreten.
- **IMAGE RIGHTS:** Nur Wikimedia Commons mit CC0, Public Domain, CC BY oder CC BY-SA. Fotos der Gemeinde, der Feuerwehr, von Heimat-Info oder Vereinen sind **gesperrt**, bis eine schriftliche Freigabe vorliegt; Copyright-Vermerke sind keine Freigabe.
- **CONTENT:** Textentwürfe folgen `docs/REDAKTIONELLER-STANDARD-V2.md` (Kurzmeldung ab 80 Wörter, Standard ab 300 Wörter) und `docs/BILD-MOTIVREGELN.md` (Bildklasse je Meldung). Keine fremden Volltexte, keine Werbesprache, zuletzt „Wie geht es weiter?“.
- **FRONTEND / QA / PUSH-STATUS:** nicht ausgeführt. Dieses Dossier ändert nichts im Repository. Die Übernahme in `inhalte/meldungen/` und `inhalte/termine/`, `node deploy/kette.mjs`, `node qa/pruefung.mjs` und der Push sind der nächste, getrennte Schritt.

## Quellensperren an diesem Tag

| Quelle | Status | Ausweichweg |
|---|---|---|
| gemeinde-merzenich.de, alle Unterseiten außer Amtsblatt-PDFs und Stellenseite | HTTP 200, aber JavaScript-Prüfung „Schutz vor automatisierten Zugriffen“ (kdvz Rhein-Erft-Rur); kein Inhalt lesbar | Heimat-Info-App der Gemeinde (dieselben Mitteilungen, offizieller Kanal), Ratsinformationssystem, Amtsblatt-PDF direkt |
| kgs-golzheim.gemeinde-merzenich.de | dieselbe JavaScript-Prüfung | KGS Merzenich erreichbar; Golzheim offen |
| schuetzen-golzheim.de | HTTP 403 (Cloudflare) | Heimat-Info-Profil der Bruderschaft |
| ratsinfo.gemeinde-merzenich.de/termine (HTML) | Kalender nur per JavaScript | `termine.ics` liefert alle terminierten Sitzungen (gelesen) |
| aachener-zeitung.de | HTTP 403 | nicht genutzt; Paywall ohnehin |
| fussball.de | Ergebnisse als Icon-Schrift | fupa.net, Vereinsseiten (Abschnitt 4) |
| Websuche (englischsprachiger Index) | keine brauchbaren Treffer für „Merzenich“ | direkte Quellen |

---


---

## 1. Themenübersicht (alle Karten)

**Zählung:** 33 Themenkarten (MA-1025-01 bis -33). Davon 16 mit vollständigem Textentwurf (10 „geeignet“, 6 „bedingt“ mit benannten Kürzungen oder Prüfpunkten), 17 ohne Text (Nachfassen, Termin-Einträge, Hintergrund, Befunde, gesperrt, vorbei). Keine Karte beruht auf einer bloßen Listenseite; jede hat mindestens eine gelesene Detailquelle oder ist als Lücke gekennzeichnet. Das Ziel von 25 bis 40 Karten ist erreicht, das Ziel von 15 bis 25 Entwürfen knapp (16); mehr trägt die Nachrichtenlage eines Herbstferien-Sonntags nicht, ohne Themen zu zerteilen.

| ID | Prio | Ressort · Ort | Typ | Titel (Kurzform) | Text | Freigabe Text | Bild |
|---|---|---|---|---|---|---|---|
| 01 | P1 | Blaulicht · Golzheim, L264 | Neu | Sturmtief „Luise“: Äste in Golzheim, Baum auf L264 (142/26, 143/26) | ja (152 W.) | geeignet | offen, neue Bildklasse |
| 02 | P2 | Leben · Merzenich | Neu | Rund 120 Gäste beim Seniorennachmittag | ja (301 W.) | geeignet | Gemeindefoto gesperrt |
| 03 | P2 | Menschen · Merzenich, Bürgewald | Neu | Siegener Schüler 17 Tage zu Fuß, Station in Merzenich | ja (318 W.) | bedingt | Fotos Jonen, Freigabe nötig |
| 04 | P2 | Rathaus · Golzheim | Termin/Neu | Bürgersprechstunde 30.10., Pfarrheim Golzheim | ja (112 W.) | geeignet | Ortsmotiv |
| 05 | P2 | Strukturwandel · Sophienhöhe | Termin | Wildpferdeexkursion 29.10., kostenlos | ja (171 W.) | geeignet (1 Satz kürzen) | Commons-Kandidat |
| 06 | P2 | Leben · Kreishaus | Termin (Frist 26.10.) | Aktionstag Persönliches Budget 5.11. | ja (158 W.) | geeignet (1 Satz streichen) | Bildklasse vorhanden |
| 07 | P3 | Rathaus · Gemeinde | Termin | Gemeinderat 3.12. und Sitzungen bis Jahresende | ja (139 W.) | bedingt | Bildklasse vorhanden |
| 08 | P3 | Wirtschaft · Golzheim | Neu | Übungsleiter für Sportkita Bärenstark gesucht | ja (104 W.) | bedingt (PDF) | Bildklasse vorhanden |
| 09 | P3 | Leben · alle Ortsteile | Update/Service | Weckmann-Bons: Vorverkaufstermine | ja (121 W.) | nur als Erinnerung (Dublette) | Bildklasse vorhanden |
| 10 | P3 | Vereine · Merzenich | Update (Dublette) | Bierabend 31.10.: Verzehrkarte | nein | – | gesperrt |
| 11 | P2 | Strukturwandel · Bürgewald | Nachfassen/Termin | Tagebausee: Auslegung ab 9.11. (BRA-Bekanntmachung mit Merzenich-Nennung) | nein (Update) | – | vorhanden |
| 12 | P2 | Rathaus · Merzenich | Nachfassen | Nachbericht Strukturausschuss 15.10. | nein | – | – |
| 13 | P3 | Rathaus · Merzenich | Nachfassen/Hintergrund | RPA 5.11. und GPA-Prüfung | nein | – | – |
| 14 | P3 | Jugend · Merzenich | Nachfassen | Jugendgremium 4.11. (Vorbericht) | nein | – | – |
| 15 | P4 | Rathaus · Gemeinde | Nachfassen | Amtsblatt 12/2026 | nein | – | – |
| 16 | P4 | Kirchen · alle Ortsteile | Rechercheauftrag | Pfarrbrief statt veralteter Website | nein | – | – |
| 17 | P4 | Schulen · Golzheim, Merzenich | Rechercheauftrag | KGS Golzheim gesperrt, Anmeldetermine | nein | – | – |
| 18 | P4 | Vereine/Umkreis · Aldenhoven | Hintergrund | LEADER RömerRunde | nein | – | – |
| 19 | P4 | Polizei · Gemeinde | Befund | Keine Merzenich-Meldung seit 15.10. | nein | – | – |
| 20 | P4 | Menschen · Girbelsrath, Merzenich | gesperrt | Diamantene Hochzeit, Trauung (Privatpersonen) | nein | gesperrt | – |
| 21 | P4 | Kirchen · Morschenich | vorbei | Wortgottesdienst 24.10. | nein | – | – |
| 22 | P4 | Strukturwandel · Elsdorf | vorbei | RWE-Infomesse 19.10. | nein | – | – |
| 23 | P2 | Sport · alle Ortsteile | Neu + Nachfassen | Spieltag: Merzenich 6:3, Golzheim 5:0, Morschenich 7:2, Girbelsrath 1:5; Sonntag offen | ja (289 W.) | geeignet (Fassung A) | Poolfotos |
| 24 | P3 | Sport · Merzenich | Termin/Neu | TTC: AndroCup 31.10., Auswärtswoche | ja (131 W.) | bedingt | Bildklasse vorhanden |
| 25 | P2 | Verkehr · Pendler | Neu/Service | Bahn Düren–Köln nachts, Bismarckstraße, A4 | ja (312 W.) | geeignet | Bildklasse vorhanden |
| 26 | P3 | Vereine/Jugend · Hürtgenwald | Neu (Frist 20.11.) | Zeltplatz Finkenheide sucht Träger | ja (139 W.) | bedingt | neue Bildklasse |
| 27 | P3 | Umwelt · Hürtgenwald | Update/Termin | Eichel-Sammelaktion: Absage, 6.11. | ja (147 W.) | geeignet | Commons-Kandidat |
| 28 | P3 | Wirtschaft · Kreis | Termin (Frist 31.10.) | Meisterstipendium 6.000 Euro | ja (118 W.) | geeignet | Bildklasse vorhanden |
| 29 | P4 | Leben · online | Termin | Mental-Load-Vortrag 18.11. | ja (93 W.) | bedingt | Bildklasse vorhanden |
| 30 | P4 | Wirtschaft · Kreis | Hintergrund | Erntedank: Kosten und Trockenheit | nein | – | – |
| 31 | P4 | Leben · Düren | Termin | Seniorenweihnacht 24.11., Restkarten | nein (Termin) | – | – |
| 32 | P3 | Schulen · Düren | Nachfassen | Infoabende weiterführende Schulen ab 5.11. | nein | – | – |
| 33 | P4 | Blaulicht · Umkreis | Befund/Liste | Polizei-Umkreis 72 h (6 Einträge) | nein (Umkreis-Liste) | – | – |

**Was weggefallen ist und warum:** Krätzchensitzung-Vorverkauf (seit 16.10. mit Preis und Vorverkaufsstellen auf der Seite), Martinszüge (16.10. auf der Seite), LEADER-Projekte (17.10.), VISIONplus-Preis und Trinkwasser Linnich (keine Wirkung auf Merzenich), Anzeigen-Beiträge vom 20.10. (Partnerinhalte, keine Redaktion), Elsdorfer Meldungen (keine Wirkung), RWE-Infomesse (vorbei), zwei Familienanzeigen (Privatpersonen).
## 2. Themenkarten Gemeinde Merzenich (Kern)

Reihenfolge nach Priorität. P1 = Aufmacher-Kandidat, P2 = Startseite, P3 = Ressort, P4 = Hintergrund/optional.

### MA-1025-01 · P1 · Blaulicht · Gemeinde (Golzheim, Merzenich) · NEU

**Titel:** Sturmtief „Luise“: Feuerwehr räumt Äste in Golzheim und einen Baum von der L264
**Teaser:** Zwei Einsätze innerhalb weniger Minuten am Freitagabend: In der St.-Sebastianus-Straße in Golzheim lagen Äste auf der Fahrbahn, auf der Landstraße 264 bei Merzenich ein umgestürzter Baum. Beide Einsätze waren nach rund einer halben Stunde beendet.
**Artikeltyp:** kurzmeldung (ausbaufähig zu standard, wenn die Einsatzseiten der Feuerwehr erscheinen)

**Textentwurf (152 Wörter):**

Das Sturmtief „Luise“ hat die Freiwillige Feuerwehr Merzenich am Freitagabend, 23. Oktober, zweimal auf die Straße gerufen. Gegen 18:30 Uhr meldete die Wehr über die Heimat-Info-App zwei sturmbedingte Einsätze im Gemeindegebiet, beide unter dem Stichwort „Technische Hilfe“.

Zuerst wurden die Löschgruppen Golzheim und Girbelsrath in die St.-Sebastianus-Straße nach Golzheim alarmiert (Einsatz 142/26). Dort lagen Äste auf der Fahrbahn, die die Einsatzkräfte beiseite räumten. Nach etwa 30 Minuten war die Straße wieder frei.

Kurz darauf forderte die Polizei die Feuerwehr auf die Landstraße 264 an (Einsatz 143/26). Ein umgestürzter Baum blockierte die Fahrbahn bei Merzenich; die Löschgruppen Merzenich und Morschenich beseitigten ihn ebenfalls innerhalb von rund einer halben Stunde.

Verletzt wurde nach Angaben der Feuerwehr niemand. Ob es im Gemeindegebiet weitere Sturmschäden gab, hat die Wehr bislang nicht mitgeteilt. Die ausführlichen Einsatzberichte auf feuerwehr-merzenich.de standen am Sonntag noch aus; dort endet die Liste bei Einsatz 140/26.

**Nutzen für Leser:** Erklärt, warum am Freitagabend Straßen in Golzheim und an der L264 kurz gesperrt waren; ordnet den Sturm lokal ein.
**Primärquelle / Herausgeber:** Freiwillige Feuerwehr Merzenich über Heimat-Info: https://www.heimat-info.de/beitraege/ea9cebd6-cb5d-4b20-88ed-1ee6babe60aa (142/26) und https://www.heimat-info.de/beitraege/3865a392-2ea9-4ac0-864e-7a32eb3ba895 (143/26)
**Quellen-Veröffentlichung:** 23.10.2026, 18:24 Uhr und 18:31 Uhr · **Ereignisdatum:** 23.10.2026, Abend · **Abruf:** 25.10.2026, 09:52 Uhr UTC (Rohdaten des Quellenabrufs) und 15:40 Uhr MEZ (Live-Liste)
**Faktenbelege:** Einsatz-Nr. 142/26, Stichwort TH, „Ast auf Straße“, Golzheim St.-Sebastianus-Straße, LG Golzheim + LG Girbelsrath, „nach etwa 30 Minuten beendet“ · Einsatz-Nr. 143/26, Stichwort TH, „Baum auf Straße“, Merzenich L264, LG Merzenich + LG Morschenich, „auf Anforderung durch die Polizei“, „nach etwa 30 Minuten“ · Sturmtief namentlich „Luise“ laut Feuerwehrtext
**Offene Fragen:** Gab es weitere Einsätze am 24./25.10.? Uhrzeiten der Alarmierung (Heimat-Info nennt nur die Veröffentlichungszeit). Wurde die L264 voll gesperrt?
**Textfreigabe:** geeignet (zwei Quellen derselben Herausgeberin, konsistent) · **Bildstatus:** offen; Heimat-Info zeigt „Symbolfoto“ der Feuerwehr (gesperrt). Passende Bildklasse fehlt in `deploy/bildklassen.json` (keine Klasse für Sturm/Baum auf Straße); Vorschlag neue Klasse `blaulicht.technik.sturm` mit Motiven `baum-auf-strasse`, `sturmschaden`; Kandidaten in Abschnitt 7.
**Ablauf / Refresh:** Tagesaktualität bis 27.10.; erneut prüfen, sobald feuerwehr-merzenich.de die Seiten 141 bis 143/26 veröffentlicht (dann ergänzen: Alarmzeiten, Fahrzeuge).
**Dublettenhinweis:** keine Meldung zu 142/26 oder 143/26 im Bestand (`grep "142/26" inhalte chatgpt-site/blaulicht` leer). **Achtung Automatik:** `deploy/automatik-feuerwehr.mjs` legt nach dem nächsten Quellenabruf vermutlich je Einsatz einen eigenen Entwurf an, sobald die Einsatzseiten existieren. Wird diese Sammelmeldung veröffentlicht, müssen die beiden Automatik-Entwürfe im Backend in den Papierkorb (sie kommen dann nicht wieder); `qa/pruefung.mjs` sperrt doppelte Einsatznummern.

---

### MA-1025-02 · P2 · Leben · Merzenich · NEU (Nachbericht)

**Titel:** Rund 120 Gäste beim Seniorennachmittag in der Weinberghalle
**Teaser:** Kuchen, Akkordeon und ein Tanzauftritt der Kita-Kinder: Die Gemeinde hat ihre Seniorinnen und Senioren wieder in die Weinberghalle eingeladen. Getragen wurde der Nachmittag von Vereinen, Ehrenamtlichen und Sponsoren.
**Artikeltyp:** standard

**Textentwurf (301 Wörter):**

Rund 120 Seniorinnen und Senioren haben in der Weinberghalle den diesjährigen Seniorennachmittag der Gemeinde Merzenich verbracht. Das teilte die Gemeinde am 20. Oktober über ihren Kanal in der Heimat-Info-App mit. Der Nachmittag war im Veranstaltungskalender für Samstag, 17. Oktober, angekündigt.

Bürgermeister Georg Gelhausen und die Organisatorin der Gemeinde hatten nach Angaben der Verwaltung ein Programm mit mehreren Auftritten zusammengestellt. Die Kinder der Merzenicher Kitas zeigten einen Tanz, der Musiker Christoph Peetz begleitete den Nachmittag am Akkordeon. Für Kuchen, Brötchen, Kaffee und kalte Getränke war gesorgt.

Die Gemeinde beschreibt den Seniorennachmittag ausdrücklich als Gemeinschaftsprojekt, das ohne Ehrenamt und Sponsoren nicht stattfinden könnte. Den Thekendienst übernahmen die KG Mir hahle Poohl aus Golzheim und die Marianischen Schützen aus Golzheim, die Tischdekoration stellte das Familienzentrum Merzenich. Für Licht und Ton sorgte ein ehrenamtlicher Helfer. Hauptsponsor war die Sparkasse Düren; dazu kamen Beiträge von Geschäften aus Merzenich, die die Gemeinde als „lokale Unterstützer der Merzenicher Geschäftswelt“ zusammenfasst, ohne sie einzeln zu nennen.

Der Seniorennachmittag gehört zu den festen Angeboten der Gemeinde für ältere Einwohnerinnen und Einwohner. In diesem Jahr war eine Anmeldung im Vorfeld nötig; die Gemeinde hatte dazu im September über das Amtsblatt und die App aufgerufen. Wie viele Plätze insgesamt zur Verfügung standen und ob Anmeldungen abgewiesen werden mussten, teilt die Verwaltung nicht mit.

Der nächste größere Termin für ältere Bürgerinnen und Bürger steht bereits fest: Am 10. November zeigt die VHS um 15:30 Uhr im Seniorenhaus Marienhof einen Bildvortrag über Paris. Ob der Seniorennachmittag 2027 wieder in der Weinberghalle stattfindet, ist noch nicht angekündigt; die Gemeinde dankt in ihrer Mitteilung allen Beteiligten und stellt eine Wiederholung in Aussicht.

**Nutzen für Leser:** Nachbericht zu einer Veranstaltung, die viele Familien betrifft; nennt die Vereine und Einrichtungen, die geholfen haben.
**Primärquelle / Herausgeber:** Gemeinde Merzenich über Heimat-Info: https://www.heimat-info.de/beitraege/cf8dd84e-5371-4615-a0f0-9b991c0de9cd · Terminbeleg: https://merzenich-aktuell.de/termine/seniorennachmittag-2026/ (eigener Bestand, 17.10.2026)
**Quellen-Veröffentlichung:** 20.10.2026, 10:12 Uhr · **Ereignisdatum:** 17.10.2026 (laut Terminbestand; die Mitteilung selbst nennt kein Datum, nur „jetzt wieder“) · **Abruf:** 25.10.2026, 09:52 Uhr UTC (Rohdaten des Quellenabrufs), 15:45 Uhr MEZ (Einzelbeitrag)
**Faktenbelege:** „Rund 120 Seniorinnen und Senioren“ · Weinberghalle · Bürgermeister Georg Gelhausen und Organisatorin genannt · Kuchen, Brötchen, Kaffee, Softgetränke · KG Mir hahle Poohl und Marianische Schützen Golzheim „Thekendienst“ · Familienzentrum Merzenich Tischdeko · Kita-Kinder Tanzauftritt · Musiker Christoph Peetz, Akkordeon · Hauptsponsor Sparkasse Düren · VHS-Vortrag 10.11. aus `inhalte/termine/`
**Offene Fragen:** Veranstaltungsdatum bestätigen (17.10.?). Name der Organisatorin ist in der Gemeindemitteilung genannt; redaktionell entscheiden, ob die Nennung als Funktionsträgerin gewünscht ist (im Entwurf weggelassen). Teilnehmerzahl der Vorjahre?
**Textfreigabe:** geeignet · **Bildstatus:** Heimat-Info zeigt Gemeindefotos (gesperrt, Freigabe anfragen); Bildklasse `leben.senioren` vorhanden; Kandidaten Abschnitt 7 (Pool Senioren/Akkordeon).
**Ablauf / Refresh:** Nachbericht, 14 Tage relevant; danach Archiv.
**Dublettenhinweis:** Bestand enthält nur die Ankündigung („Jetzt anmelden zum Seniorennachmittag“, ohne Datum) und den Termin 17.10.; kein Nachbericht. Keine Dublette.

---

### MA-1025-03 · P2 · Menschen · Merzenich, Bürgewald · NEU

**Titel:** 17 Tage zu Fuß von Köln an die Grenze: Siegener Schüler machen Station in Merzenich
**Teaser:** Vier Neuntklässler und zwei Lehramtsstudentinnen wandern mit Bollerwagen und 150 Euro pro Person durch das Rheinland. In Merzenich lud der Bürgermeister sie zum Essen ein und schickte sie mit dem Bürgerbus nach Bürgewald.
**Artikeltyp:** standard

**Textentwurf (318 Wörter):**

Eine ungewöhnliche Reisegruppe hat in der vergangenen Woche in Merzenich Halt gemacht: vier Schüler des Evangelischen Gymnasiums Siegen-Weidenau und zwei Lehramtsstudentinnen, die 17 Tage lang zu Fuß von Köln bis an die niederländische Grenze und wieder zurück unterwegs sind. Die Gemeinde berichtete am 23. Oktober über den Besuch.

Hinter der Tour steckt ein festes Projekt der Siegener Schule. Zu Beginn der neunten Klasse sollen sich die Jugendlichen selbst eine Herausforderung stellen, um eigene Grenzen kennenzulernen. Das Quartett entschied sich für den langen Fußmarsch, packte Rucksäcke und einen Bollerwagen mit Vorräten und setzte sich zusätzlich ein knappes Budget: 150 Euro pro Person für die gesamte Zeit.

Weil der Weg durch Merzenich führte, griff Bürgermeister Georg Gelhausen ein. Er lud die Gruppe zum Essen ein, besorgte eine Übernachtungsmöglichkeit und organisierte einen Ausflug mit dem Bürgerbus nach Bürgewald, den neuen Ortsteil, in den die Morschenicher wegen des Tagebaus Hambach umgesiedelt sind. „Ein tolles Projekt und eine sehr mutige Herausforderung der vier Schüler“, wird Gelhausen in der Mitteilung zitiert. Er wünsche den Beteiligten, dass sie gesund blieben und den gemeinsamen Weg erfolgreich bestreiten würden.

Wo die Gruppe in Merzenich übernachtet hat und an welchem Tag sie weitergezogen ist, nennt die Gemeinde nicht. Auch die genaue Route an die Grenze bleibt offen; von Merzenich aus liegen Aachen und die Grenzorte bei Herzogenrath rund 40 Kilometer Fußweg entfernt.

Der Bürgerbus, mit dem die Wanderer nach Bürgewald fuhren, ist ein ehrenamtlich betriebenes Angebot der Gemeinde, das die Ortsteile untereinander und mit Düren verbindet. Für die Schüler aus dem Siegerland war der Abstecher nach Angaben der Gemeinde zugleich eine Lehrstunde zum Strukturwandel im Rheinischen Revier.

Wie es weitergeht: Die Gruppe soll nach Abschluss der 17 Tage wieder in Köln ankommen. Ob die Schule oder die Gemeinde einen Bericht über das Ende der Tour veröffentlicht, ist offen; die Redaktion fragt nach.

**Nutzen für Leser:** Menschen-Geschichte mit Ortsbezug und Bürgerbus; zeigt, wie die Gemeinde auf Durchreisende reagiert.
**Primärquelle / Herausgeber:** Gemeinde Merzenich über Heimat-Info: https://www.heimat-info.de/beitraege/bb5362f1-7c2c-45ee-aa48-b9d5374bd5e5
**Quellen-Veröffentlichung:** 23.10.2026, 11:15 Uhr · **Ereignisdatum:** Woche vom 19. bis 23.10.2026 (genauer Tag nicht genannt) · **Abruf:** 25.10.2026, 09:52 Uhr UTC (Rohdaten des Quellenabrufs), 15:45 Uhr MEZ
**Faktenbelege:** Evangelisches Gymnasium Siegen-Weidenau · 9. Jahrgangsstufe · „Quartett“ + „zwei Lehramtsstudentinnen“ · 17 Tage, Köln bis holländische Grenze und zurück · Bollerwagen · Budget 150 Euro pro Person · Bürgermeister: Essen, Übernachtung, Ausflug nach Bürgewald „mit dem Bürgerbus“ · Zitat Gelhausen wörtlich aus der Mitteilung · Fotos: Stephan Jonen (Credit in der Quelle)
**Offene Fragen:** Übernachtungsort, Datum des Besuchs, Route, Rückkehrtermin. Vorsicht: Der Satz über 40 Kilometer bis zur Grenze ist eine redaktionelle Einordnung ohne Quelle; vor Freigabe streichen oder mit Routenangabe belegen. Der Bürgerbus-Absatz stützt sich auf Allgemeinwissen zur Gemeinde; Beleg nachholen (Gemeinde-Seite Bürgerbus) oder kürzen.
**Textfreigabe:** bedingt (zwei Einordnungssätze ohne Quelle, siehe oben; Kern geeignet) · **Bildstatus:** Fotos von Stephan Jonen für die Gemeinde; Nutzung nur mit Freigabe von Gemeinde und Fotograf; keine Schülergesichter ohne Einwilligung. Bildklasse fehlt (Vorschlag `menschen.wandern`); Kandidaten Abschnitt 7 (Pool Wandern/Rucksack/Landstraße).
**Ablauf / Refresh:** 10 Tage; Nachfassen bei Rückkehr der Gruppe.
**Dublettenhinweis:** kein Treffer zu „Siegen“ im Artikelbestand (nur in Sport-Rubrik „Siegen“ als Wortteil). Keine Dublette.

---

### MA-1025-04 · P2 · Rathaus · Golzheim · TERMIN / NEU

**Titel:** Bürgersprechstunde: Bürgermeister Gelhausen am Freitag im Pfarrheim Golzheim
**Teaser:** Am 30. Oktober um 18 Uhr können Golzheimerinnen und Golzheimer ihre Anliegen direkt mit dem Bürgermeister besprechen. Eine Anmeldung ist nach der Ankündigung nicht nötig.
**Artikeltyp:** kurzmeldung

**Textentwurf (112 Wörter):**

Bürgermeister Georg Gelhausen bietet am Freitag, 30. Oktober, eine Bürgersprechstunde in Golzheim an. Beginn ist um 18 Uhr im Pfarrheim Golzheim an der Pastoratstraße 20. Die Gemeinde hat den Termin am 23. Oktober in der Heimat-Info-App veröffentlicht.

Die Sprechstunde richtet sich an alle, die Fragen oder Anliegen zu ihrem Ortsteil oder zur Gemeinde haben. Eine Anmeldung oder Themenvorgabe nennt die Ankündigung nicht; wer kommt, kann sein Anliegen vor Ort vorbringen. Weitere Informationen gibt es im Rathaus unter 02421 399-0 oder per E-Mail an buergermeister@gemeinde-merzenich.de.

Ob Sprechstunden in den anderen Ortsteilen folgen, ist bislang nicht angekündigt.

**Nutzen für Leser:** Konkreter Termin mit Ort und Uhrzeit; niedrigschwelliger Kontakt zur Verwaltung.
**Primärquelle / Herausgeber:** Gemeinde Merzenich über Heimat-Info (Veranstaltung): https://www.heimat-info.de/veranstaltungen/a225a9c4-ce51-4811-b6c9-0cb43af200c1
**Quellen-Veröffentlichung:** 23.10.2026, 18:04 Uhr · **Ereignisdatum:** 30.10.2026, 18:00 Uhr · **Abruf:** 25.10.2026, 15:45 Uhr MEZ
**Faktenbelege:** Titel „Bürgersprechstunde mit Bürgermeister Georg Gelhausen“ · 30.10.2026 18:00 · „Pfarrheim Golzheim, Pastoratstraße 20“ · Kontakt aus dem Gemeindeprofil (Telefon 02421/399 0, E-Mail buergermeister@…)
**Offene Fragen:** Ende der Sprechstunde? Einzelgespräche oder offene Runde? Reihe für alle Ortsteile?
**Textfreigabe:** geeignet · **Bildstatus:** Bildklasse `rathaus.rat` oder Ortsmotiv Golzheim (St. Gregorius, Pfarrheim); Kandidaten Abschnitt 7.
**Ablauf / Refresh:** läuft am 30.10.2026, 18:00 Uhr ab; danach als Termin archivieren. Zusätzlich als Eintrag in `inhalte/termine/` anlegen (Kategorie Rathaus, Ortsteil Golzheim).
**Dublettenhinweis:** kein Eintrag „Bürgersprechstunde“ im Terminbestand. Keine Dublette.

---

### MA-1025-05 · P2 · Strukturwandel / Umkreis · Sophienhöhe (Niederzier/Jülich), Bezug Merzenich · TERMIN

**Titel:** Wildpferde und Hambachsee: kostenlose Exkursion auf die Sophienhöhe am 29. Oktober
**Teaser:** Neuland Hambach und die RWE-Forschungsstelle Rekultivierung bieten in den Herbstferien einen Zusatztermin an. Anmeldung beim RWE-Besucherdienst, die Plätze sind begrenzt.
**Artikeltyp:** kurzmeldung

**Textentwurf (171 Wörter):**

Wer die Konik-Wildpferde auf der Sophienhöhe sehen will, bekommt in den Herbstferien noch eine Gelegenheit: Am Donnerstag, 29. Oktober, laden die Neuland Hambach GmbH und die RWE-Forschungsstelle Rekultivierung zu einer geführten Exkursion ein. Treffpunkt ist um 9 Uhr der Wanderparkplatz Sophienhöhe. Die Teilnahme ist kostenlos, eine Anmeldung beim RWE-Besucherdienst unter 0800 8833-830 ist Pflicht, weil die Plätze begrenzt sind.

Mit dem Bus geht es auf die Sophienhöhe und zur Goldenen Aue, mit Zwischenstopps unter anderem am Inselsee. Eine rund halbstündige Wanderung führt durch das Weidegebiet der Koniks, die seit Juni 2024 dort leben. Unterwegs erklären die Veranstalter, welche Rolle die Tiere für die Entwicklung der rekultivierten Landschaft spielen.

Neuland Hambach stellt außerdem geplante Projekte vor, darunter den Aussichtsturm „Waldtänzerin“, die „Höllentreppe“ und das neue Besucher- und Informationszentrum an der Goldenen Aue. Auch der künftige Hambachsee, an dessen Südwestufer Merzenich und Bürgewald liegen werden, ist Thema. Festes Schuhwerk und wetterfeste Kleidung werden empfohlen.

**Nutzen für Leser:** Ferienangebot in der Nachbarschaft mit direktem Bezug zum Tagebausee; konkrete Anmeldung.
**Primärquelle / Herausgeber:** Neuland Hambach GmbH: https://www.neuland-hambach.de/aktuelles/artikel/wildpferdeexkursion-auf-die-sophienhoehe-zusatztermin-in-den-herbstferien
**Quellen-Veröffentlichung:** 20.10.2026 · **Ereignisdatum:** 29.10.2026, 09:00 Uhr · **Abruf:** 25.10.2026, 15:38 Uhr MEZ
**Faktenbelege:** 29.10.2026, 9 Uhr · Wanderparkplatz Sophienhöhe (50.91124, 6.44156) · kostenlos, Anmeldung erforderlich, RWE Besucherdienst 0800 8833-830, Plätze begrenzt · Koniks seit Juni 2024 · Projekte „Waldtänzerin“, „Höllentreppe“, Besucher- und Informationszentrum Goldene Aue · Bus, Zwischenstopp Inselsee, 30-minütige Wanderung
**Offene Fragen:** Dauer der Exkursion? Für Kinder geeignet? Der Satz zur Lage von Merzenich und Bürgewald am Hambachsee ist Einordnung aus früheren Meldungen (Tagebausee-Artikel vom 13.10.); Formulierung „Südwestufer“ vor Freigabe prüfen oder auf „am künftigen See“ kürzen.
**Textfreigabe:** geeignet (nach Kürzung des Einordnungssatzes) · **Bildstatus:** Neuland-Fotos (© RWE Power AG/Kerkling) gesperrt; Bildklasse `rathaus.tagebau` vorhanden; Commons-Kandidaten Sophienhöhe/Konik in Abschnitt 7.
**Ablauf / Refresh:** läuft 29.10.2026, 09:00 Uhr ab. Als Termin in `inhalte/termine/` anlegen (Kategorie Freizeit, Ort Sophienhöhe).
**Dublettenhinweis:** kein Treffer „Wildpferd“ oder „Sophienhöhe“ im Artikel- oder Terminbestand (nur Bildpool). Keine Dublette.

---

### MA-1025-06 · P2 · Leben / Soziales · Kreishaus Düren, Bezug Gemeinde · TERMIN (Frist morgen)

**Titel:** Persönliches Budget: Aktionstag im Kreishaus am 5. November, Anmeldung bis Montag
**Teaser:** Der Kreis Düren erklärt, wie Menschen mit Behinderung ihre Unterstützung selbst einkaufen können. Die Gemeinde Merzenich weist auf die Anmeldefrist 26. Oktober hin.
**Artikeltyp:** kurzmeldung

**Textentwurf (158 Wörter):**

Der Kreis Düren lädt am Donnerstag, 5. November, von 10 bis 17 Uhr zum Aktionstag „Das Persönliche Budget“ ins Kreishaus Düren ein (Raum A 158). Die Gemeinde Merzenich hat die Einladung am 21. Oktober in der Heimat-Info-App weitergegeben. Wer teilnehmen möchte, muss sich bis Montag, 26. Oktober, online unter beteiligung.nrw.de/k/1028 anmelden.

Das Persönliche Budget ist ein Geldbetrag, auf den Menschen mit Behinderung einen Rechtsanspruch haben. Damit können sie Leistungen selbst auswählen, einkaufen und bezahlen, statt sie als Sachleistung zu erhalten. Der Aktionstag bietet Fachvorträge, einen „Markt der Möglichkeiten“ mit Beratungsstellen und Diensten sowie Raum für Erfahrungsaustausch. Die Teilnahme ist kostenlos.

Das Kreishaus stellt technische Hörunterstützung, Schrift- und Gebärdensprachdolmetschende sowie einen Ruheraum bereit. Begleitend informiert vom 2. bis 6. November eine barrierefreie Wanderausstellung im Kreishaus über das Persönliche Budget. Beratung vor Ort gibt es auch nach dem Aktionstag beim Kreis Düren als Träger der Eingliederungshilfe.

**Nutzen für Leser:** Konkrete Frist (morgen), kostenloses Beratungsangebot für Betroffene und Angehörige.
**Primärquelle / Herausgeber:** Gemeinde Merzenich über Heimat-Info: https://www.heimat-info.de/beitraege/6ff892e9-2eba-4374-a65d-44609b7a6fdb · Ursprung: Kreis Düren, Pressemitteilung 19.10.2026: https://www.kreis-dueren.de/presse/das-persoenliche-budget_2026-10-19.php (Details in Abschnitt 3, Umkreis)
**Quellen-Veröffentlichung:** 21.10.2026, 09:29 Uhr (Gemeinde), 19.10.2026 (Kreis) · **Ereignisdatum:** 05.11.2026, 10 bis 17 Uhr; Ausstellung 02. bis 06.11.2026; Anmeldeschluss 26.10.2026 · **Abruf:** 25.10.2026, 09:52 Uhr UTC (Rohdaten des Quellenabrufs), 15:45 Uhr MEZ
**Faktenbelege:** 5.11.2026, 10–17 Uhr, Kreishaus Düren, Raum A 158 · Fachvorträge, Markt der Möglichkeiten, Austausch, barrierefrei und kostenlos · Wanderausstellung 2.–6.11. · Hörunterstützung, Schriftdolmetschende, Gebärdensprachdolmetschende, Ruheraum · Anmeldung bis 26.10.2026, https://beteiligung.nrw.de/k/1028
**Offene Fragen:** Letzter Satz („Beratung auch nach dem Aktionstag beim Kreis als Träger der Eingliederungshilfe“) ist nicht aus der Quelle; streichen oder mit Kreis-Mitteilung belegen.
**Textfreigabe:** geeignet nach Streichung des letzten Satzes · **Bildstatus:** Bildklasse `leben.vorsorge` oder `leben.veranstaltung` vorhanden; Kreishaus-Foto auf Commons prüfen (Abschnitt 7).
**Ablauf / Refresh:** Anmeldefrist 26.10.2026; Veranstaltung 05.11.2026. Nach dem 26.10. Titel ändern („Anmeldung geschlossen“) oder Meldung zurückziehen.
**Dublettenhinweis:** Kreis-Mitteilung vom 19.10. steht nicht in `inhalte/umkreis/2026-10-16.json` (dort nur bis 15./16.10.) und nicht im Artikelbestand. Keine Dublette.

---

### MA-1025-07 · P3 · Rathaus · Gemeinde · TERMIN

**Titel:** Gemeinderat tagt am 3. Dezember: die Sitzungstermine bis Jahresende
**Teaser:** Das Ratsinformationssystem nennt sechs Sitzungen bis Dezember, vom Jugendgremium am 4. November bis zum Gemeinderat am 3. Dezember. Alle Sitzungen sind öffentlich.
**Artikeltyp:** kurzmeldung

**Textentwurf (139 Wörter):**

Bis zum Jahresende stehen in der Gemeinde Merzenich sechs öffentliche Gremiensitzungen an. Das geht aus dem Sitzungskalender des Ratsinformationssystems hervor (Stand 25. Oktober).

Den Anfang macht das Jugendgremium am Mittwoch, 4. November, um 17 Uhr im Bürgerhaus an der Burgstraße 9. Am Donnerstag, 5. November, tagt um 18 Uhr der Rechnungsprüfungsausschuss im Ratssaal. Es folgen der Bau-, Planungs- und Umweltausschuss am 12. November, der Haupt- und Finanzausschuss am 19. November und der Ausschuss für Revitalisierung Bürgewald am 26. November, jeweils um 18 Uhr im Ratssaal am Valdersweg 1.

Der Gemeinderat kommt am Donnerstag, 3. Dezember, um 17 Uhr im Ratssaal zusammen. Es ist die letzte Ratssitzung des Jahres; üblicherweise steht dann der Haushalt für das Folgejahr auf der Tagesordnung. Die Tagesordnungen veröffentlicht die Verwaltung in der Regel rund eine Woche vor dem Termin im Ratsinformationssystem.

**Nutzen für Leser:** Termine auf einen Blick; Gemeinderat 3.12. ist neu im Bestand.
**Primärquelle / Herausgeber:** Gemeinde Merzenich, Ratsinformationssystem (Kalenderexport): https://ratsinfo.gemeinde-merzenich.de/termine.ics
**Quellen-Veröffentlichung:** laufend (Kalender) · **Ereignisdaten:** 04.11. 17:00 (Jugendgremium, Bürgerhaus) · 05.11. 18:00 (RPA) · 12.11. 18:00 (BPU) · 19.11. 18:00 (HFA) · 26.11. 18:00 (Bürgewald) · 03.12. 17:00 (Gemeinderat) · **Abruf:** 25.10.2026, 15:45 Uhr MEZ
**Faktenbelege:** sechs VEVENT-Einträge mit DTSTART, SUMMARY und LOCATION (Rathaus, Ratssaal bzw. Bürgerhaus Merzenich, Burgstraße 9)
**Offene Fragen:** Der Satz „üblicherweise Haushalt“ und „rund eine Woche vorher“ sind Erfahrungswerte ohne Beleg; vor Freigabe streichen oder mit Tagesordnung belegen, sobald sie vorliegt.
**Textfreigabe:** bedingt (zwei Sätze ohne Beleg) · **Bildstatus:** Bildklasse `rathaus.rat` vorhanden.
**Ablauf / Refresh:** Termin 3.12. als Eintrag in `inhalte/termine/` anlegen; die fünf übrigen stehen schon dort (Dublette). Refresh bei Veröffentlichung der Tagesordnungen (ca. 28.10. für das Jugendgremium).
**Dublettenhinweis:** 04.11., 05.11., 12.11., 19.11., 26.11. sind im Terminbestand; **03.12. Gemeinderat fehlt**.

---

### MA-1025-08 · P3 · Wirtschaft / Stellen · Golzheim · NEU

**Titel:** Sportkita Bärenstark: Kreis Düren sucht Übungsleiter mit C-Lizenz
**Teaser:** Für das Bewegungsangebot in den U3- und Ü3-Gruppen der Kita in Golzheim wird eine Übungsleiterin oder ein Übungsleiter gesucht. Die Ausschreibung läuft über die Stellenseite der Gemeinde.
**Artikeltyp:** kurzmeldung

**Textentwurf (104 Wörter):**

Der Kreis Düren sucht für die Sportkita Bärenstark in Merzenich-Golzheim eine Übungsleiterin oder einen Übungsleiter mit C-Lizenz. Die Kraft soll das Bewegungsangebot in den Gruppen für Kinder unter und über drei Jahren übernehmen. Die Gemeinde Merzenich hat die Anzeige auf ihrer Stellenseite veröffentlicht; die vollständige Ausschreibung liegt dort als PDF bereit.

Die Kita Bärenstark trägt das Profil einer Sportkita; das Bewegungsangebot wird über den Kreissportbund beziehungsweise den Kreis organisiert. Zur Vergütung und zum Stundenumfang macht die Anzeige auf der Übersichtsseite keine Angaben.

Unabhängig davon sucht die Gemeinde für dieselbe Kita weiterhin eine Erzieherin oder einen Erzieher als Krankheitsvertretung bis Ende Juli 2027.

**Nutzen für Leser:** Lokale Stelle für Trainerinnen und Trainer aus den Vereinen.
**Primärquelle / Herausgeber:** Gemeinde Merzenich, Stellenangebote: https://www.gemeinde-merzenich.de/politik/stellenanngebote.php (Rohdaten `imports/quellen/jobs.json`, Abruf 25.10.2026, 09:55 Uhr UTC); PDF: https://www.gemeinde-merzenich.de/bindata/stellenausschreibungen/Stellenausschreibung_Uebungsleiter.pdf (15 MB, nicht gelesen)
**Quellen-Veröffentlichung:** unbekannt (Seite ohne Datum) · **Ereignisdatum:** laufend · **Abruf:** 25.10.2026, 09:55 Uhr UTC
**Faktenbelege:** „Der Kreis Düren sucht für die Sportkita Bärenstark in Merzenich-Golzheim Übungsleiter mit C-Lizenz für das Bewegungsangebot in Ü3- und U3-Gruppen“ · Erzieher-Stelle „befristet als Krankheitsvertretung bis zum 31.07.2027“, 30 Stunden oder Vollzeit
**Offene Fragen:** Seit wann steht die Anzeige online (ggf. älter als 14 Tage)? PDF lesen: Umfang, Vergütung, Bewerbungsfrist, Ansprechperson. Satz zum Kreissportbund ist Vermutung; streichen, wenn das PDF ihn nicht stützt.
**Textfreigabe:** bedingt (PDF ungelesen) · **Bildstatus:** Bildklasse `gemeinde.stellenangebot` vorhanden.
**Ablauf / Refresh:** bis zur Streichung von der Stellenseite; wöchentlich prüfen.
**Dublettenhinweis:** Erzieher-Stelle steht seit 01.10. auf der Seite (Dublette, nur als Nebensatz). „Übungsleiter“ ohne Treffer im Bestand.

---

### MA-1025-09 · P3 · Leben · alle Ortsteile · UPDATE (Service, Dublette im Kern)

**Titel:** Weckmann-Bons: Vorverkauf in Morschenich am Dienstag, in Girbelsrath ab Mittwoch
**Teaser:** Die Martinszüge stehen fest, jetzt beginnen die Vorverkäufe für zusätzliche Weckmänner. Die Termine je Ortsteil im Überblick.
**Artikeltyp:** kurzmeldung (nur als Service-Erinnerung; der Volltext steht seit 16.10. auf der Seite)

**Textentwurf (121 Wörter):**

In dieser Woche beginnen in den Ortsteilen die Vorverkäufe für zusätzliche Weckmann-Bons zu den Martinszügen. Die Gemeinde hat die Termine am 22. Oktober noch einmal gebündelt veröffentlicht.

Morschenich macht den Anfang: Am Dienstag, 27. Oktober, gibt es von 18 bis 19 Uhr Bons im Bürgewaldzentrum; danach bei der Ortsvorsteherin. Der Zug startet am 2. November um 18 Uhr am Ludwig-Rixen-Platz.

In Girbelsrath werden Bons am 28., 29. und 30. Oktober sowie am 2. November jeweils von 14:30 bis 16 Uhr in der Kita Villa Wichtel verkauft; Girbelsrather Kinder bis zehn Jahre bekommen ihren Weckmann kostenlos. In Merzenich läuft der Verkauf am 2. und 3. November von 7:30 bis 10:30 Uhr in der Grundschule an der Schulstraße sowie ab 18 Uhr in den Kitas Regenbogen (2.11.), Windmühle (3.11.) und Krümelkiste (4.11.).

**Primärquelle / Herausgeber:** Gemeinde Merzenich über Heimat-Info: https://www.heimat-info.de/beitraege/c1cfc473-86db-436c-b372-c0fd5661eb51 (22.10.2026, 10:00 Uhr) · Amtsblatt Nr. 11 vom 16.10.2026, S. 9 (Terminbestand)
**Ereignisdaten:** 27.10. (Morschenich), 28.–30.10. und 02.11. (Girbelsrath), 02./03.11. (Merzenich) · **Abruf:** 25.10.2026, 09:52 Uhr UTC
**Faktenbelege:** alle Zeiten und Orte wörtlich aus der Gemeindemitteilung vom 22.10. (siehe Quelle), identisch mit Amtsblatt-Fassung im Bestand
**Textfreigabe:** geeignet als Erinnerung; **nicht als neue Nachricht** · **Bildstatus:** Bildklasse `leben.brauchtum` vorhanden.
**Ablauf / Refresh:** 27.10. bis 04.11.2026.
**Dublettenhinweis:** **Dublette im Kern.** Der Artikel „Martinszüge 2026: Termine für Morschenich, Golzheim, Merzenich und Girbelsrath stehen fest“ (16.10.) enthält bereits alle Bons-Termine (7 Treffer „Weckmann-Bons“). Empfehlung: nur als Startseiten-Hinweis am 27.10. oder als Aktualisierung des bestehenden Artikels, kein zweiter Artikel.

---

### MA-1025-10 · P3 · Vereine · Merzenich · UPDATE (Dublette)

**Titel:** 59. Bierabend des FC-Fanclubs: Verzehrkarte schließt Eintritt und Getränke ein
**Typ:** Update zum bestehenden Termin
**Was neu ist:** Der Fanclub hat heute (25.10., 10:05 Uhr) die Veranstaltung in der Heimat-Info-App als Termin angelegt: Weinberghalle Merzenich, 31.10.2026, 19:48 Uhr bis 1.11., 00:30 Uhr, Programm mit lokalen Künstlern, Tanzgruppen und Marita Köllner; Getränke und Eintritt sind in der Verzehrkarte enthalten, „weitere Informationen folgen“.
**Kein eigener Textentwurf.** Der Terminbestand (`fc-fanclub-59-bierabend-2026`, 31.10. 19:48 Uhr) nennt Marita Köllner bereits. Neu ist nur der Hinweis auf die Verzehrkarte; als Ergänzung in den bestehenden Termin einarbeiten.
**Quelle:** https://www.heimat-info.de/veranstaltungen/efb93919-2d19-4892-a781-1f693bd73f45 (Abruf 25.10.2026, 15:45 Uhr MEZ) · **Offene Fragen:** Preis der Verzehrkarte, Vorverkaufsstellen, ob noch Karten erhältlich sind (Nachfassen beim Fanclub, fc-fanclub-merzenich@gmx.de). · **Bild:** Vereinsfoto gesperrt; Bildklasse `leben.veranstaltung`.

---

### MA-1025-11 · P2 · Strukturwandel · Bürgewald, Morschenich · NACHFASSEN / TERMIN

**Titel (Arbeitstitel):** Tagebausee Hambach: Planunterlagen ab 9. November online, Einwendungen bis 22. Dezember
**Typ:** Termin mit Refresh; **kein neuer Text**, Artikel vom 13.10. steht.
**Aufgabe:** Am 8./9.11. prüfen, ob die Bezirksregierung Arnsberg die Unterlagen wie angekündigt online stellt (Fundstelle laut Umkreis-Recherche, Abschnitt 3); dann Update mit direktem Link zu den Unterlagen und zur Einwendungsmöglichkeit, Hinweis auf die Ausschusssitzung Bürgewald am 26.11.
**Quelle Bestand:** merzenich-aktuell.de/rathaus/… (Tagebausee-Artikel, 13.10.2026) · **Ereignisdaten:** 09.11. bis 08.12. Auslegung, Einwendungen bis 22.12.2026 · **Dublette:** Artikel vorhanden; nur Update.

---

### MA-1025-12 · P2 · Rathaus · Merzenich · NACHFASSEN

**Titel (Arbeitstitel):** Was der Strukturausschuss am 15. Oktober zu „Ruhbens“, RWE und Neuland Hambach erfahren hat
**Typ:** Nachbericht fehlt. Der Vorbericht (14.10.) kündigte Vorträge der OGE (CO2-Leitung, Trassensegment A05 bei Merzenich), der RWE Power AG und der Neuland Hambach GmbH an.
**Quellenlage heute:** Das Ratsinformationssystem bietet Niederschriften nur über die Vorlagen-/Sitzungssuche (Formular, nicht per Direktlink abrufbar); Heimat-Info und Gemeinde haben keinen Bericht veröffentlicht; aachener-zeitung.de ist für den Abruf gesperrt.
**Konkrete Fragen:** 1. Hat die OGE eine aktualisierte Präsentation vorgelegt (Antrag Raumverträglichkeitsprüfung gestellt? Korridor bei Merzenich konkretisiert?) 2. Was hat RWE zu Aussichtspunkt Morschenicher Bucht und Werksbahn gesagt? 3. Gibt es einen Beschluss oder nur Kenntnisnahme? → Niederschrift im RIS unter Gremium „Ausschuss für Wirtschaftsförderung und Strukturentwicklung“, Sitzung 15.10.2026, abrufen, sobald freigegeben (meist 2 bis 4 Wochen).
**Dublette:** Vorbericht vorhanden. **Textfreigabe:** kein Text.

---

### MA-1025-13 · P3 · Rathaus · Merzenich · NACHFASSEN / HINTERGRUND

**Titel (Arbeitstitel):** Rechnungsprüfungsausschuss am 5. November: Was die Gemeindeprüfungsanstalt beanstandet hat
**Typ:** Hintergrund (älter) mit Terminbezug. Die Gemeinde hat eine Pressemitteilung „Gemeindeprüfungsanstalt stellt Ergebnisse vor“ (Listenposition zwischen „Nahverkehrsplan“ und „Ehrenamtsfest 2025“, Datum nicht lesbar) veröffentlicht; im Artikelbestand gibt es dazu keinen Treffer.
**Aufgabe:** Mitteilung über Heimat-Info oder im Browser (JavaScript) lesen; GPA-Bericht im RIS suchen; mit der Tagesordnung des RPA vom 05.11. (Veröffentlichung ca. 29.10.) verbinden. Erst dann Text.
**Quelle:** Teaser auf https://www.gemeinde-merzenich.de/aktuelles/pressemitteilungen.php (Rohdaten, Abruf 25.10. 09:51 UTC); Direktlink https://www.gemeinde-merzenich.de/aktuelles/pressemitteilungen/gpa-pruefung.php (heute JavaScript-Sperre).
**Dublette:** keine. **Textfreigabe:** kein Text.

---

### MA-1025-14 · P3 · Jugend / Rathaus · Merzenich · NACHFASSEN (Vorbericht)

**Titel (Arbeitstitel):** Jugendgremium tagt am 4. November im Bürgerhaus
**Typ:** Termin im Bestand (04.11., 17 Uhr); Vorbericht möglich, sobald die Tagesordnung im RIS steht (erwartet ab ca. 28.10.).
**Konkrete Fragen:** Welche Anträge liegen vor? Erste Projekte des im Frühjahr gewählten Gremiums (11 Mitglieder laut Gemeindemitteilung „Erstes Jugendgremium in Merzenich gewählt“, Bestand)? Ist die Sitzung öffentlich für Jugendliche ohne Mandat?
**Dublette:** Termin vorhanden. **Textfreigabe:** kein Text.

---

### MA-1025-15 · P4 · Rathaus · Gemeinde · NACHFASSEN

**Titel (Arbeitstitel):** Amtsblatt 12/2026 erscheint voraussichtlich Mitte November
**Stand:** Auf der Amtsblatt-Seite der Gemeinde ist Nr. 11 (16.10.2026) die jüngste Ausgabe; `Merzenich_12_2026-web.pdf` existiert noch nicht (Abruf 25.10., 15:38 Uhr MEZ: HTML statt PDF). Rhythmus 2026: 09.01., 06.02., 06.03., 02.04., 30.04., 29.05., …, 16.10. → Nr. 12 voraussichtlich zwischen 13. und 20.11.
**Aufgabe:** ab 12.11. täglich die PDF-Adresse prüfen; dann Termine (Dezember, Advent, Sitzungen) und Bekanntmachungen auswerten wie bei Nr. 11.
**Dublette:** Nr. 11 vollständig eingearbeitet (`2026-10-16-amtsblatt-11.json`). **Textfreigabe:** kein Text.

---

### MA-1025-16 · P4 · Kirchen / Soziales · alle Ortsteile · RECHERCHEAUFTRAG

**Befund:** Die Website der Pfarrei St. Laurentius (st-laurentius-merzenich.de, Bistum Aachen) ist seit 2019 nicht gepflegt (jüngste Nachricht „Erstkommunion 2020“); Gottesdienstzeiten liegen nur im PDF-Pfarrbrief („aktueller Pfarrbrief“, Link auf der Startseite, heute nicht gelesen). Heimat-Info liefert nur Morschenich (St. Lambertus) und die evangelische Gemeinde. Allerheiligen (01.11., Gräbersegnungen) und Taizé-Gottesdienst (15.11.) stehen im Terminbestand.
**Aufgabe:** Pfarrbrief-PDF laden und auf November-Termine prüfen (Allerseelen, Martinsmessen, Adventsgottesdienste, Sternsinger-Aufruf); Zentralpfarrbüro Schulstraße 4, 02421 33770, kgv-merzenich@t-online.de als Kontakt für Rückfragen notieren (keine Mail automatisch senden).
**Dublette:** keine. **Textfreigabe:** kein Text.

---

### MA-1025-17 · P4 · Schulen / Kitas · Golzheim, Merzenich · RECHERCHEAUFTRAG

**Befund:** KGS Merzenich: keine neue Meldung seit 21.09. (Zitronenaktion, Tag der offenen Tür 25./26.09.; beides im Bestand). KGS Golzheim: Website heute hinter JavaScript-Prüfung, Rohdaten enthalten nur die Rubrikseite „Unsere Schule“ ohne Aktuelles. Kitas: keine eigenen Kanäle gefunden außer Stellen (Bärenstark).
**Aufgabe:** KGS Golzheim im Browser prüfen (Aktuelles, Termine); Anmeldetermine für Erstklässler 2027/28 bei beiden Grundschulen erfragen (Gemeinde-Bekanntmachung meist im November-Amtsblatt).
**Dublette:** keine. **Textfreigabe:** kein Text.

---

### MA-1025-18 · P4 · Vereine / Umkreis · LEADER-Region (Freialdenhoven, Aldenhoven) · HINTERGRUND

**Befund:** Die LAG Rheinisches Revier an Inde und Rur hat am 22.10. über Heimat-Info die LEADER-geförderte „RömerRunde“ in Freialdenhoven vorgestellt (Rundweg über die Via Belgica, Infotafeln, Spiel- und Bewegungsangebote). Merzenich gehört zur LAG; ein Merzenicher Projekt ist nicht genannt.
**Einordnung:** Nachbarkultur ohne direkte Wirkung auf Merzenich; nur als Beispiel in einem späteren LEADER-Hintergrund (Ideen bis 27.01.2027, Artikel vom 17.10. im Bestand) verwendbar.
**Quelle:** https://www.heimat-info.de/beitraege/fc5481c0-bd53-4f59-86b4-5ce6d7d75115 (22.10.2026, 14:51 Uhr) · **Textfreigabe:** kein Text.

---

### MA-1025-19 · P4 · Blaulicht / Polizei · Gemeinde · BEFUND

**Befund:** Das Presseportal listet für Merzenich (https://www.presseportal.de/blaulicht/r/Merzenich, heute HTTP 200, Sperre vom 10.10. aufgehoben) 28 Meldungen 2026; die jüngste mit Ortsbezug stammt vom 25.09. (Abbiegeunfall, im Bestand). Zwischen 15.10. und 25.10. gibt es **keine neue Polizeimeldung mit Merzenich-Bezug** in den Rohdaten. Regionale Meldungen der Polizei Düren der letzten 72 Stunden: siehe Abschnitt 3.
**Textfreigabe:** kein Text (keine Nachricht).

---

### MA-1025-20 · P4 · Menschen · Girbelsrath, Merzenich · GESPERRT

**Befund:** Heimat-Info meldet eine Diamantene Hochzeit (22.10., Gemeinde) und eine Trauung (21.10.). Beide Beiträge nennen die Namen der Privatpersonen. Nach den Regeln der Redaktion (keine Namen von Privatpersonen ohne Einwilligung) und mangels anderer Nachrichtenwerte: **gesperrt**, es sei denn, die Familien stimmen ausdrücklich zu (Partner-Einreichung über das Backend wäre der richtige Weg).
**Quellen:** https://www.heimat-info.de/beitraege/a654ad32-0234-4938-8976-ca53e7d45faf, https://www.heimat-info.de/beitraege/ba8b3e8a-8eb5-4624-b081-e654f3b1afa2

---

### MA-1025-21 · P4 · Kirchen · Morschenich · VORBEI

**Befund:** Wortgottesdienst St. Lambertus Morschenich am 24.10., 17:30 Uhr (Heimat-Info 23.10.). Vorbei, kein Nachrichtenwert. Nur als Beleg dafür, dass die Kapellengemeinde Heimat-Info regelmäßig nutzt (Quelle für künftige Gottesdiensttermine).

---

### MA-1025-22 · P4 · Strukturwandel · Elsdorf-Berrendorf · VORBEI

**Befund:** RWE-Infomesse „Vom Tagebau zum See“ am 19.10. im Forum Terra Nova (Heimat-Info 19.10., 08:20 Uhr, Gemeinde). Vorbei; kein Nachbericht veröffentlicht. Inhaltlich deckungsgleich mit dem Tagebausee-Artikel vom 13.10. Kein Text.

---

## 3. Umkreis, Verkehr, Blaulicht (Kreis Düren, Nachbarn)

Abrufe 25.10.2026, 15:38 bis 15:50 Uhr MEZ. Nur Themen mit benennbarer Wirkung auf die Gemeinde erhalten eine Karte; alles andere steht in der Umkreis-Liste am Ende des Abschnitts.

### MA-1025-25 · P2 · Verkehr · Pendler aus allen Ortsteilen · NEU (Service für die Ferienwoche)

**Titel:** Bahn, Bismarckstraße, A4: Was Pendler aus Merzenich in dieser Woche wissen müssen
**Teaser:** Nachts fährt zwischen Düren und Köln bis Freitag nur ein Gleis, in Düren ist die Bismarckstraße stadteinwärts gesperrt, und auf der A4 wird an zwei Stellen gearbeitet. Die Termine im Überblick.
**Artikeltyp:** standard · Bildklasse `verkehr.baustelle`

**Textentwurf (312 Wörter):**

In der zweiten Herbstferienwoche kommen auf Pendlerinnen und Pendler aus der Gemeinde Merzenich mehrere Einschränkungen zu, auf der Schiene wie auf der Straße.

Zwischen Düren und Köln-Ehrenfeld lässt die Bahn vom 23. bis 30. Oktober Gleisarbeiten ausführen. Nach der Baustellenübersicht des Verkehrsverbunds go.Rheinland ist die Strecke in diesem Zeitraum täglich von 21 bis 5 Uhr nur eingleisig befahrbar. Der RE 9 fällt zwischen Düren und Köln Hauptbahnhof voraussichtlich aus; als Alternativen nennt der Verbund den RE 1 und die S 19, die in Merzenich hält. In den Nächten vom 26. auf den 27. und vom 27. auf den 28. Oktober sind wegen zusätzlicher Instandhaltungsarbeiten auch RE 1, RE 9, RB 20, RB 38 und S 19 betroffen. Welche S-19-Fahrten genau ausfallen, steht nur in der Fahrplanauskunft (zuginfo.nrw).

In Düren ist die Bismarckstraße ab Montag, 26. Oktober, bis voraussichtlich Freitag, 30. Oktober, stadteinwärts zwischen Schoellerstraße und Kreuzstraße gesperrt. Die Stadt lässt dort einen Abwasserschacht reparieren; die Umleitung führt über Kreuzstraße und Arnoldsweilerstraße. Wer aus Merzenich in die Innenstadt fährt, sollte diese Einfahrt meiden. Entwarnung gibt es am Kaiserplatz: Nach der verzögerten Sanierung fahren die Busse seit dem 22. Oktober wieder regulär über Kaiserplatz und Zehnthofstraße; die Ersatzhaltestellen in der Schenkelstraße sind aufgehoben.

Auf der A4 richtet die Autobahn GmbH von Montag bis Donnerstag, 26. bis 29. Oktober, jeweils von 9 bis 15 Uhr in Fahrtrichtung Köln zwischen dem Autobahnkreuz Aachen und der Anschlussstelle Düren eine Wiegeanlage ein; gesperrt wird nichts, die Fahrbahn bleibt gut elf Meter breit. In Richtung Aachen ist die A4 in der Nacht von Dienstag auf Mittwoch, 27. auf 28. Oktober, von 21 bis 5 Uhr zwischen den Anschlussstellen Eschweiler-Ost und Weisweiler wegen Freileitungsarbeiten gesperrt, einschließlich der Auffahrt Weisweiler aus Richtung Langerwehe. Die Dauerbaustelle an der Anschlussstelle Königsdorf (Tempo 80, verengte Spuren) bleibt bis Ende November.

Für den Abschnitt zwischen Kerpen-Buir, Merzenich und Düren meldet die Autobahn GmbH keine Arbeiten. Baustellen von Straßen.NRW im Kreis Düren konnte die Redaktion am Sonntag nicht abrufen; wir reichen sie nach.

**Nutzen für Leser:** Konkrete Zeiten für die Fahrt zur Arbeit und nach Köln in einer Woche mit mehreren Baustellen.
**Primärquellen / Herausgeber:** go.Rheinland Baustellenübersicht (Stand 09.10.2026): https://www.gorheinland.com/auskunft/baustellenuebersicht · Stadt Düren, Pressemitteilung 21.10.2026 (Bismarckstraße): https://www.dueren.de/de/verwaltung-politik/stadtverwaltung/presse/pressemitteilung/stadteinwaerts-fuehrende-fahrspur-der-bismarckstrae-zwischen-schoellerstrae-und-kreuzstrae-ab-2610-voruebergehend-gesperrt · Stadt Düren 17.10.2026 (Kaiserplatz): https://www.dueren.de/de/verwaltung-politik/stadtverwaltung/presse/pressemitteilung/sanierung-der-nebenanlagen-im-bereich-kaiserplatzzehnthofstrae-verzoegert-sich · Rurtalbus 19.10.2026: https://www.rurtalbus.de/2026/10/19/kaiserplatz-wegen-bauarbeiten-gesperrt-haltestellen-bis-einschliesslich-21-oktober-in-die-schenkelstrasse-verlegt/ · Autobahn GmbH, Schnittstelle: https://verkehr.autobahn.de/o/autobahn/A4/services/roadworks und …/closure
**Ereignisdaten:** Bahn 23.–30.10., nachts 21–5 Uhr; Sondernächte 26./27. und 27./28.10. · Bismarckstraße 26.–30.10. · A4 Wiegeanlage 26.–29.10., 9–15 Uhr · A4 Eschweiler-Ost–Weisweiler 27.10. 21 Uhr bis 28.10. 5 Uhr · Königsdorf bis 30.11. · **Abruf:** 15:41 bis 15:50 Uhr MEZ
**Faktenbelege:** go.Rheinland: „Düren – Köln-Ehrenfeld 23.10.–30.10.2026, eingleisige Sperrung, täglich 21–5 Uhr, RE 9 fällt voraussichtlich zwischen Düren und Köln Hbf aus, Alternativen RE 1 und S 19; Nächte 26./27. und 27./28.10. zusätzlich RE 1, RE 9, RB 20, RB 38, S 19“ · Stadt Düren: Sperrung Bismarckstraße stadteinwärts Schoellerstraße–Kreuzstraße 26.10. bis voraussichtlich 30.10., Abwasserschacht, Umleitung Kreuzstraße/Arnoldsweilerstraße · Stadt Düren/Rurtalbus: Busverkehr ab Do 22.10. regulär über Kaiserplatz/Zehnthofstraße · Autobahn-API: Wiegeanlage „Rur Scholle Süd“ Mo–Do 26.–29.10. 9–15 Uhr, Durchfahrtsbreite 11,25 m, keine Sperrung; Closure AS Eschweiler-Ost – AS Weisweiler 27.10. 21:00 bis 28.10. 05:00, Auffahrt Weisweiler aus Richtung Langerwehe betroffen; Königsdorf 13.07.–30.11.2026, Tempo 80
**Offene Fragen:** Konkrete S-19-Ausfälle in den beiden Nächten (zuginfo.nrw nur per Browser lesbar); Straßen.NRW-Baustellen im Kreis Düren (Seite heute nicht maschinenlesbar); Schienenersatzverkehr?
**Textfreigabe:** geeignet (Bahnteil mit Vorbehalt „voraussichtlich“ wie in der Quelle) · **Bildstatus:** Bildklasse `verkehr.baustelle` vorhanden; Commons-Kandidat S-Bahnhof Merzenich im Pool.
**Ablauf / Refresh:** 30.10.2026; am 26.10. früh zuginfo.nrw im Browser prüfen und S-19-Details nachtragen.
**Dublettenhinweis:** Bestand: „A4: Engstellen nahe der Anschlussstelle Düren auch am Donnerstag“ (14.10., abgelaufen). Keine Dublette.

---

### MA-1025-26 · P3 · Vereine / Jugend · Kreis Düren, Bezug Jugendgruppen aus Merzenich · NEU (Frist 20.11.)

**Titel:** Jugendzeltplatz Finkenheide: Kreis sucht bis 20. November einen neuen Träger
**Teaser:** Der Kreis Düren verkauft den 11.500 Quadratmeter großen Zeltplatz in Hürtgenwald-Kleinhau. Gesucht ist ein Jugendhilfeträger, der ihn als Bildungs- und Begegnungsort weiterführt.
**Artikeltyp:** kurzmeldung · Bildklasse: keine passende vorhanden (Vorschlag `vereine.jugend`)

**Textentwurf (139 Wörter):**

Der Kreis Düren will den Jugendzeltplatz Finkenheide in Hürtgenwald-Kleinhau abgeben. Nach einer Mitteilung der Kreisverwaltung vom 21. Oktober wird ein Träger der Jugendhilfe gesucht, der das rund 11.500 Quadratmeter große Gelände kauft und als Ort für Bildung und Begegnung weiterführt. Zum Platz gehören Zeltflächen, ein Gruppen- und Aktionshaus sowie Küche und Sanitäranlagen.

Interessenten müssen bis zum 20. November 2026 ein Nutzungskonzept und ein Kaufpreisangebot einreichen. Ein Informationsblatt mit den Einzelheiten stellt der Kreis als PDF auf seiner Website bereit.

Für Jugendgruppen aus der Gemeinde Merzenich, etwa die Pfadfinder des Stammes St. Laurentius, die Messdiener oder die Jugendfeuerwehr, ist der Platz in der Nordeifel ein klassisches Ziel für Zeltlager. Ob er nach dem Verkauf weiter für Gruppen von außerhalb buchbar bleibt, hängt vom künftigen Träger ab.

**Primärquelle / Herausgeber:** Kreis Düren: https://www.kreis-dueren.de/presse/zeltplatz-finkenheide_2026-10-21.php (Listendatum 23.10., URL 21.10.2026) · **Abruf:** 15:39 Uhr MEZ
**Faktenbelege:** rund 11.500 m², Hürtgenwald-Kleinhau · Jugendhilfeträger gesucht, Bildungs-/Begegnungsort · Zeltflächen, Gruppen- und Aktionshaus, Küche/Sanitär · Nutzungskonzept + Kaufpreisangebot bis 20.11.2026 · Infoblatt-PDF
**Offene Fragen:** Haben Merzenicher Gruppen den Platz in den letzten Jahren genutzt (Beleg fehlt; der dritte Absatz ist Einordnung ohne Quelle, vor Freigabe kürzen oder belegen)? Mindestgebot? Warum verkauft der Kreis?
**Textfreigabe:** bedingt (dritter Absatz) · **Ablauf / Refresh:** 20.11.2026 · **Dublette:** keine (nicht in `inhalte/umkreis/`).

---

### MA-1025-27 · P3 · Umwelt · Kreis Düren, Mitmachaktion · UPDATE (Absage wegen Sturm) + TERMIN

**Titel:** Eicheln für den Hürtgenwald: erster Sammeltermin fiel dem Sturm zum Opfer, zweiter am 6. November
**Teaser:** Nach dem Waldbrand im August sollen Eicheln aus der Region die Wiederbewaldung anstoßen. Der Termin am Freitag wurde wegen Sturmböen abgesagt; am 6. November geht es an der Burg Laufenburg weiter.
**Artikeltyp:** kurzmeldung · Bildklasse `leben.klima` oder neu `umwelt.wald`

**Textentwurf (147 Wörter):**

Der Kreis Düren und Wald und Holz NRW rufen dazu auf, Eicheln für die Wiederbewaldung des Hürtgenwalds zu sammeln. Dort hatte im August ein Waldbrand rund 300 Hektar erfasst. Die gesammelten Eicheln sollen zum Jahreswechsel ausgesät werden.

Der erste Sammeltermin am Freitag, 23. Oktober, wurde kurzfristig abgesagt: Der Deutsche Wetterdienst hatte Sturmböen gemeldet, und im Wald drohten abbrechende Äste. Es war derselbe Sturm, der am Abend auch die Feuerwehr in Golzheim und auf der L264 beschäftigte. Angemeldete Helferinnen und Helfer wurden nach Angaben des Kreises direkt informiert.

Der zweite Termin bleibt bestehen: Freitag, 6. November, von 14 bis 17 Uhr, Treffpunkt ist der Parkplatz an der Burg Laufenburg in Hürtgenwald. Mitzubringen sind ein Jutebeutel, feste Kleidung und Handschuhe. Die Anmeldung läuft per E-Mail beim Amt für Umwelt des Kreises (Amt 61).

**Primärquellen:** Kreis Düren 21.10.2026: https://www.kreis-dueren.de/presse/eichel-sammelaktion_2026-10-21.php · Absage 23.10.2026: https://www.kreis-dueren.de/presse/eichel-sammelaktion_2026-10-23.php · **Abruf:** 15:39 Uhr MEZ
**Faktenbelege:** Waldbrand August, rund 300 ha · Partner Wald und Holz NRW · Aussaat zum Jahreswechsel · 23.10. ab 14 Uhr abgesagt wegen DWD-Sturmböen, Astabbruchgefahr · 6.11., 14–17 Uhr, Parkplatz Burg Laufenburg · Jutebeutel, feste Kleidung, Handschuhe · Anmeldung E-Mail Amt 61
**Offene Fragen:** Genaue E-Mail-Adresse (in der Mitteilung; vor Freigabe eintragen). Der Verweis auf die Merzenicher Feuerwehreinsätze ist redaktionelle Verknüpfung (Sturmtief „Luise“ laut Feuerwehr), kein Beleg des Kreises.
**Textfreigabe:** geeignet · **Ablauf / Refresh:** 06.11.2026 · **Dublette:** keine.

---

### MA-1025-28 · P3 · Wirtschaft · Kreis Düren, Handwerk · TERMIN (Frist 31.10.)

**Titel:** Meisterstipendium des Kreises: 6.000 Euro, Bewerbung noch bis Samstag
**Teaser:** Der Kreis Düren vergibt zum dritten Mal vier Stipendien für angehende Meisterinnen und Meister. Bedingung: fünf Jahre im Kreis arbeiten.
**Artikeltyp:** kurzmeldung · Bildklasse `wirtschaft.weiterbildung`

**Textentwurf (118 Wörter):**

Handwerkerinnen und Handwerker aus der Gemeinde, die eine Meisterausbildung beginnen oder absolvieren, können sich noch bis Samstag, 31. Oktober, um ein Meisterstipendium des Kreises Düren bewerben. Der Kreis vergibt in der dritten Runde vier Stipendien von je 6.000 Euro; das Geld wird einmalig ausgezahlt und muss nicht zurückgezahlt werden. Im Gegenzug verpflichten sich die Stipendiaten, nach dem Abschluss fünf Jahre im Kreis Düren in ihrem Beruf zu arbeiten.

Die Bewerbung läuft online über www.kreis-dueren.de/meisterstipendium. Der Kreis stellt in seiner Mitteilung vom 20. Oktober einen Landschaftsbau-Meister aus Düren vor, der zum ersten Jahrgang gehört. Ob bisher jemand aus Merzenich gefördert wurde, geht aus der Mitteilung nicht hervor.

**Primärquelle:** Kreis Düren: https://www.kreis-dueren.de/presse/meisterstipendium_2026-10-01.php (Listendatum 20.10.2026) · **Abruf:** 15:39 Uhr MEZ
**Faktenbelege:** 6.000 Euro einmalig, nicht rückzahlbar · fünf Jahre Berufspflicht im Kreis · dritte Runde, vier Plätze · Bewerbung online bis Sa 31.10.2026
**Offene Fragen:** Voraussetzungen (Gewerk, Alter, Wohnsitz)? Stipendiaten aus Merzenich in Runde 1 und 2?
**Textfreigabe:** geeignet · **Ablauf / Refresh:** 31.10.2026 · **Dublette:** keine (Bestand: KI-Workshop für Betriebe 14.10.).

---

### MA-1025-29 · P4 · Leben / Familie · online, Kreis Düren · TERMIN

**Titel:** „Mental Load“: kostenloser Online-Vortrag des Kreises am 18. November
**Artikeltyp:** kurzmeldung · Bildklasse `leben.vorsorge`

**Textentwurf (93 Wörter):**

Wer in der Familie an alles denken muss, trägt die „Mental Load“. Wie sich diese unsichtbare Organisationsarbeit fairer verteilen lässt, erklärt ein kostenloser Online-Vortrag des Kreises Düren am Mittwoch, 18. November, von 18 bis 19:30 Uhr. Referent ist der Leiter der SkF-Beratungsstelle Düren. Der Vortrag gehört zur Reihe „Wir. Gemeinsam. Familie im digitalen Gespräch“.

Eine Anmeldung ist ab sofort über beteiligung.nrw.de/k/1031298 möglich; der Zugangslink wird nach der Anmeldung verschickt. Für Eltern aus Merzenich entfällt die Anfahrt nach Düren.

**Primärquelle:** Kreis Düren 20.10.2026: https://www.kreis-dueren.de/presse/vortrag-mental-load_2026-10-20.php · **Abruf:** 15:39 Uhr MEZ · **Faktenbelege:** 18.11.2026, 18–19:30 Uhr, online, 90 Minuten, kostenfrei, Referent SkF Düren, Anmeldung beteiligung.nrw.de/k/1031298 · **Offene Fragen:** „Zugangslink nach Anmeldung“ ist Annahme; prüfen. · **Textfreigabe:** bedingt · **Ablauf:** 18.11.2026 · **Dublette:** keine. Als Termin anlegen.

---

### MA-1025-30 · P4 · Wirtschaft / Landwirtschaft · Kreis Düren · HINTERGRUND

**Befund:** Erntedankempfang im Kreishaus (23.10., rund 80 Gäste): Die Kreisbauernschaft nennt Kostendruck bei Saatgut, Dünger und Diesel sowie Trockenheitsverluste vor allem bei Kartoffeln, Mais, Rüben und Gemüse; der Landrat dankte der Landwirtschaft auch für Löschwassertransporte beim Waldbrand Hürtgenwald. Quelle: https://www.kreis-dueren.de/presse/erntedankempfang_2026-10-23.php
**Einordnung:** Für Merzenich (Rüben-, Kartoffel- und Gemüseanbau rund um alle Ortsteile) ein guter Anlass für einen eigenen Hintergrund „Wie lief die Ernte 2026 in der Gemeinde?“ mit zwei bis drei örtlichen Betrieben als Gesprächspartner. **Kein Text ohne lokale Stimmen.**

---

### MA-1025-31 · P4 · Leben / Senioren · Arena Kreis Düren · TERMIN

**Befund:** 13. Seniorenweihnacht des Kreises am Dienstag, 24.11.2026, 15 Uhr (Einlass 14 Uhr), Arena Kreis Düren, Konzert mit Stefanie Hertel, Blechbläser Vossenack, Kinderchöre, 10 Euro inkl. Kaffee und Kuchen, Restkarten über ticketree.de/event/seniorenweihnacht. Quelle: https://www.kreis-dueren.de/presse/seniorenweihnacht_2026-09-30.php (Listendatum 19.10.)
**Empfehlung:** als Termin in `inhalte/termine/` (Kategorie Senioren), kein Artikel. **Dublette:** keine.

---

### MA-1025-32 · P3 · Schulen · Düren, Bezug Viertklässler aus der Gemeinde · NACHFASSEN

**Befund:** Die Stadt Düren kündigt Infoabende und Tage der offenen Tür der weiterführenden Schulen ab 5. November an (Pressemitteilung vom 06.10.2026: https://www.dueren.de/de/verwaltung-politik/stadtverwaltung/presse/pressemitteilung/welche-schule-ist-die-richtige-weiterfuehrende-schulen-in-dueren-laden-zum-kennenlernen-ein). Die Viertklässler der KGS Merzenich und der KGS Golzheim wechseln überwiegend nach Düren.
**Aufgabe:** Detailseite lesen, Termine je Schule tabellarisch erfassen, als Service-Meldung „Schulwahl: Termine der Dürener Schulen“ schreiben (Textfreigabe erst nach Lektüre). **Dublette:** keine.

---

### MA-1025-33 · P4 · Blaulicht · Umkreis (72 Stunden) · BEFUND, Liste für `inhalte/umkreis/`

Polizei Düren (https://www.presseportal.de/blaulicht/nr/8, HTTP 200, 15:39 Uhr) und Polizei Rhein-Erft (nr/10374): **keine Meldung nennt Merzenich oder einen Ortsteil.** Für die Umkreis-Liste (Titel in eigenen Worten, ohne Namen und Hausnummern):

| Ort | Rubrik | Titel | Datum | URL |
|---|---|---|---|---|
| Aldenhoven | Blaulicht | B56 zwischen Freialdenhoven und Dürboslar: alkoholisierter Fahrer verursacht Unfall mit drei Autos und fünf Schwerverletzten, einer lebensgefährlich | 24.10. (PM 25.10.) | https://www.presseportal.de/blaulicht/pm/8/6377348 |
| Vettweiß | Blaulicht | L33 bei Froitzheim: Fahrerin weicht Wildtier aus, Auto überschlägt sich, Vollsperrung bis 10:15 Uhr | 23.10. | https://www.presseportal.de/blaulicht/pm/8/6376638 |
| Aldenhoven | Blaulicht | Kreuzungsunfall mit Taxi an der L11: vier Leichtverletzte | 22.10. (PM 23.10.) | https://www.presseportal.de/blaulicht/pm/8/6376491 |
| Düren | Blaulicht | Karton auf dem Herd gerät in Brand, Bewohner löschen selbst | 22.10. (PM 23.10.) | https://www.presseportal.de/blaulicht/pm/8/6376492 |
| Elsdorf | Blaulicht | Räuberischer Diebstahl in Discounter an der Ohndorfer Straße: Polizei sucht Zeugen | PM 23.10. | https://www.presseportal.de/blaulicht/pm/10374/6376792 |
| Kerpen-Blatzheim | Blaulicht | Werkzeug aus Transporter gestohlen, zweiter Fall in Bergheim | 21./22.10. | https://www.presseportal.de/blaulicht/pm/10374/6376862 |

### Weitere Umkreis-Einträge (für `inhalte/umkreis/2026-10-25.json`, kein Artikel)

| Ort | Rubrik | Titel | Datum | URL |
|---|---|---|---|---|
| Kreis Düren | Wirtschaft | VISIONplus Unternehmerinnenpreis: erster Platz für eine Goldschmiede aus Inden | 21.10. | https://www.kreis-dueren.de/presse/visionplus-unternehmerinnenpreis_2026-10-21.php |
| Linnich | Gesundheit | Trinkwasser: Chlorung beendet, Wasser wieder uneingeschränkt nutzbar | 19.10. | https://www.kreis-dueren.de/presse/trinkwasser-linnich_2026-10-19.php |
| Kreis Düren | Landwirtschaft | Erntedankempfang: Bauernschaft klagt über Kosten und Trockenheitsverluste | 23.10. | https://www.kreis-dueren.de/presse/erntedankempfang_2026-10-23.php |
| Düren | Verkehr | Kaiserplatz: Busse fahren seit 22.10. wieder regulär | 17.10. | https://www.dueren.de/de/verwaltung-politik/stadtverwaltung/presse/pressemitteilung/sanierung-der-nebenanlagen-im-bereich-kaiserplatzzehnthofstrae-verzoegert-sich |
| Elsdorf | Verkehr | Jülicher Straße in Oberembt bis 6.11. gesperrt (Wasserleitung); Wirtschaftsweg Elisenhof bis 31.03.2027 | 12.10./19.10. | https://www.elsdorf.de/rathaus-service/rathaus-service/aktuelle-strassensperrungen |
| Aldenhoven | Freizeit | LEADER-geförderte „RömerRunde“ in Freialdenhoven eröffnet (Via Belgica) | 22.10. | https://www.heimat-info.de/beitraege/fc5481c0-bd53-4f59-86b4-5ce6d7d75115 |

Dublettenprüfung gegen `inhalte/umkreis/2026-10-16.json`: dort enden die Einträge am 15./16.10. (Leuchte-deinen-Schulweg, NoBiZ-Jubiläum, Windpocken, K6-Sanierung). Keiner der obigen Einträge ist enthalten.

### Nachbarkommunen und Strukturwandel: Ergänzung zu MA-1025-11

- **Bezirksregierung Arnsberg, Bekanntmachung vom 01.10.2026** (https://www.bra.nrw.de/bekanntmachungen/antrag-auf-planfeststellung-nach-ss-68-abs-1-des-wasserhaushaltsgesetzes-whg-zur-herstellung-des, Abruf 15:47 Uhr): Antrag der RWE Power AG vom 28.08.2026 auf wasserrechtliche Planfeststellung mit Umweltverträglichkeitsprüfung für den Gewässerausbau „Tagebausee Hambach“ (Teilvorhaben Herstellung des Sees, nachlaufende Sümpfung). Internet-Auslegung 09.11. bis 08.12.2026 auf bra.nrw.de/bekanntmachungen; zusätzlich Einsicht bei der Gemeinde Niederzier und der Stadt Elsdorf; Einwendungen bis einschließlich 22.12.2026; **Merzenich ist in der Bekanntmachung ausdrücklich als voraussichtlich betroffene Gemeinde genannt**; Ansprechpartner in der Außenstelle Düren der Bezirksregierung; Bekanntmachung als PDF (196 kB). → In das Update zu MA-1025-11 übernehmen: Direktlink, Antragsdatum, die beiden Auslegungsorte (keiner davon in Merzenich; Frage an die Gemeinde: Einsicht auch im Rathaus Merzenich?).
- **Neuland Hambach, RWE:** keine Neuigkeiten zu Tagebausee, Rheinwassertransportleitung, Morschenicher Bucht oder Bürgewald seit 01.10. außer der Exkursion (MA-1025-05). RWE-Power-Presseliste nur per JavaScript (Lücke).
- **Niederzier, Nörvenich, Kerpen:** Websites heute hinter derselben kdvz-JavaScript-Prüfung wie die Gemeinde Merzenich; nicht lesbar (Lücke). **Elsdorf:** zehn Meldungen seit 18.10., keine mit Wirkung auf Merzenich. **Düren:** fünf Mitteilungen seit 18.10., zwei mit Wirkung (Bismarckstraße, Kaiserplatz, siehe MA-1025-25).

---

## 4. Sport

**Datenzeitpunkt:** Abrufe am 25.10.2026 zwischen 15:38 und 16:08 Uhr (MEZ; die Sommerzeit endete heute Nacht). Die Sonntagsspiele des 10. Spieltags (Anstoß 15:00 Uhr) liefen zu diesem Zeitpunkt noch. **Bestätigt** sind daher nur die Ergebnisse bis Samstag, 24.10. Die fussball.de-Tabellen enthalten die Sonntagsspiele nicht; fupa.net zeigte Live-Tabellen mit Zwischenständen, die hier nicht übernommen werden. fussball.de blendete den Hinweis „Aktuell technische Probleme bei FUSSBALL.DE“ ein; Ergebnisse dort sind als Icon-Schrift codiert und wurden nicht geraten. Vereinsseiten: scmerzenich.de lesbar; fc-golzheim.de und sv-morschenich1925.de nicht erreichbar; Rhenania Girbelsrath hat keine Website.

**Folge für `chatgpt-site/api/sport-current.json`:** Der dort hinterlegte Stand (generated 23.09.2026, letztes Spiel 18.09., Tabelle nach 5 Spielen) ist fünf Spieltage alt. Der Sport-Abgleich des Themes sollte mit den Zahlen unten aktualisiert werden, aber erst, wenn die Sonntagsergebnisse bestätigt sind (Montag, 26.10., fussball.de oder fupa mit „Abpfiff“).

### Vier Herrenmannschaften, ein Datenstand

| Mannschaft | Staffel | Letztes bestätigtes Ergebnis | Heute (25.10., 15 Uhr), Ergebnis offen | Nächstes Spiel | Tabelle (fussball.de, vor den Sonntagsspielen) |
|---|---|---|---|---|---|
| SC 1919 Merzenich | Kreisliga A Düren | 18.10. SC Merzenich – Jugendsport Wenau 6:3 (H) | Hambacher SV – SC Merzenich (A); fupa-Zwischenstand 0:2 um 15:41 Uhr und um 16:08 Uhr (Spiel lief noch), **nicht bestätigt** | So. 08.11., 15:00, SC Merzenich – FC Düren 77 (H, Ellbachstadion, Ellener Allee) | Platz 6, 9 Spiele, 21 Punkte, 38:16 |
| FC Golzheim | Kreisliga A Düren | Sa. 24.10. SV Viktoria Koslar – FC Golzheim 0:5 (A), fupa „Abpfiff“ | – (1. Mannschaft spielte Samstag) | So. 08.11., 14:30, FC Golzheim – SV GW Welldorf-Güsten (H, Rasenplatz Herderstraße) | Platz 3, 10 Spiele, 25 Punkte, 26:9 |
| FC Rhenania Girbelsrath | Kreisliga B Staffel 2 Düren | 18.10. Sportfreunde Düren II – Rhenania Girbelsrath 5:1 (A) | SV Merken – Rhenania Girbelsrath (A); 0:0 um 15:41 Uhr, 1:0 um 16:08 Uhr (Spiel lief noch), **nicht bestätigt** | So. 08.11., 14:30, Rhenania Girbelsrath – SV Morschenich (H, Rasenplatz Neuwerk) · Ortsderby | Platz 13 von 14, 5 Spiele, 3 Punkte, 13:25 |
| SV Morschenich | Kreisliga B Staffel 2 Düren | 18.10. Schwarz-Weiß Düren II – SV Morschenich 2:7 (A) | SV Morschenich – FC Golzheim II (H, Ortsderby); 4:0 um 15:41 Uhr, mindestens 5 Tore für Morschenich um 16:08 Uhr (Spiel lief noch), **nicht bestätigt** | So. 08.11., 14:30, bei Rhenania Girbelsrath (A) · Ortsderby | Platz 3, 8 Spiele, 18 Punkte, 41:16 |

Vollständige Tabellen beider Staffeln (fussball.de, 15:40/15:45 Uhr) stehen im Anhang A. Weitere Spiele der Kreisliga A am 25.10.: Siersdorf – Oberzier abgesagt.

Quellen: fupa.net (Vereins-, Team-, Spiel- und Tabellenseiten, 15:38 bis 15:47 Uhr), fussball.de (Teamseiten SC Merzenich, FC Golzheim, Rhenania Girbelsrath, SV Morschenich, 15:40 bis 15:45 Uhr), scmerzenich.de „Spieltagbericht 07 – 26/27“ (15:45 Uhr). Alle Adressen in Anhang A.

### MA-1025-23 · P2 · Sport · Merzenich · NEU (bestätigter Teil) + NACHFASSEN (Sonntag)

**Titel (Fassung A, nur Bestätigtes):** SC Merzenich nach 6:3 gegen Wenau mit vier Siegen in Folge, Golzheim gewinnt 5:0 in Koslar
**Titel (Fassung B, nach Bestätigung der Sonntagsspiele):** Spieltag im Kreis: so haben Merzenich, Golzheim, Girbelsrath und Morschenich gespielt
**Artikeltyp:** standard (Fassung A) · Bildklasse `sport.spielbericht`

**Textentwurf Fassung A (289 Wörter, veröffentlichbar ab sofort):**

Der SC Merzenich hat in der Kreisliga A Düren seinen vierten Sieg in Folge eingefahren. Am 18. Oktober schlug die erste Mannschaft im Ellbachstadion Jugendsport Wenau mit 6:3. Ein Spieler traf dabei dreimal; der Verein spricht in seinem Spieltagbericht von einem „Dreierpack“. Mit 21 Punkten aus neun Spielen steht der Aufsteiger vor dem zehnten Spieltag auf Platz sechs, bei sieben Siegen und zwei Niederlagen.

Noch besser läuft es für den FC Golzheim. Die Mannschaft gewann am Samstag, 24. Oktober, mit 5:0 bei Viktoria Koslar und hat nach zehn Spielen 25 Punkte. Damit liegt Golzheim punktgleich mit Welldorf-Güsten und Jülich/Hoengen auf Platz drei; die Tordifferenz entscheidet über die Reihenfolge an der Spitze.

In der Kreisliga B, Staffel 2, hat der SV Morschenich am 18. Oktober mit 7:2 bei Schwarz-Weiß Düren II gewonnen und ist mit 18 Punkten aus acht Spielen Dritter. Der FC Rhenania Girbelsrath verlor am selben Tag 1:5 bei den Sportfreunden Düren II und steht mit drei Punkten aus erst fünf Spielen auf dem vorletzten Platz; die Girbelsrather haben zwei bis vier Spiele weniger absolviert als die Konkurrenz.

Für die zweite Mannschaft des SC Merzenich lief der 18. Oktober ebenfalls gut: 6:0 gegen Jugendsport Wenau II, laut Verein das siebte Spiel ohne Niederlage. Die dritte Mannschaft verlor in der Kreisliga C 1:3 beim SV Niederzier II.

Am Sonntag, 25. Oktober, waren alle vier ersten Mannschaften im Einsatz: Merzenich beim Hambacher SV, Girbelsrath beim SV Merken, Morschenich zu Hause im Ortsderby gegen Golzheim II. Die Ergebnisse lagen bei Redaktionsschluss noch nicht bestätigt vor.

Wie es weitergeht: Nach einem spielfreien Wochenende geht es am 8. November weiter. Merzenich empfängt um 15 Uhr den FC Düren 77, Golzheim um 14:30 Uhr Welldorf-Güsten. In Girbelsrath steigt um 14:30 Uhr das Ortsderby gegen Morschenich.

**Faktenbelege:** SCM – Wenau 6:3 (fupa-Spielbericht 261018; scmerzenich.de Spieltagbericht 07: „6:3 Heimerfolg“, „vierter Sieg in Folge“, Dreierpack) · Tabelle Kreisliga A fussball.de 15:40 Uhr · Koslar – Golzheim 0:5 (fupa 261024, Abpfiff; fussball.de-Tabelle 10 Spiele 26:9) · SW Düren II – Morschenich 2:7 (fupa 261018, Abpfiff) · SF Düren II – Girbelsrath 5:1 (fupa 261018, Abpfiff) · SCM II 6:0, „seit sieben Spielen ungeschlagen“; SCM III 1:3 in Niederzier (scmerzenich.de) · Ansetzungen 08.11. (fupa Team-Spielpläne, fussball.de „Nächstes Spiel“)
**Offene Fragen:** Endstände vom 25.10. (Hambach – Merzenich, Merken – Girbelsrath, Morschenich – Golzheim II, SCM III gegen Gereonsweiler, SCM II am 23.10. in Berzbuir). „Spielfreies Wochenende“ am 1.11. ist aus den Spielplänen abgeleitet (nächstes Spiel 08.11.), nicht als Allerheiligen-Pause belegt; Formulierung vor Freigabe prüfen. Torschützenname steht im offiziellen Vereinsbericht; im Entwurf weggelassen, kann mit Quelle ergänzt werden.
**Textfreigabe:** Fassung A geeignet · Fassung B erst nach Bestätigung · **Bildstatus:** keine Spielfotos mit Rechten; Bildklasse `sport.spielbericht` / `sport.platz` (Poolfotos vorhanden).
**Ablauf / Refresh:** Montag, 26.10., Endstände nachtragen (fupa „Abpfiff“, fussball.de). Dann `sport-current.json` aktualisieren (letztes Spiel, nächstes Spiel, Tabelle, Datenstand in einem Zug).
**Dublettenhinweis:** Letzte Sportmeldung im Bestand: 14.10. („Golzheim verliert erstmals, Morschenich gewinnt 5:0“). Der 18.10. und 24.10. sind nicht abgedeckt. Keine Dublette.

### MA-1025-24 · P3 · Sport · Merzenich · TERMIN / NEU

**Titel:** Tischtennis: AndroCup am 31. Oktober in der Turnhalle Merzenich, Auswärtswoche für den TTC
**Artikeltyp:** kurzmeldung · Bildklasse `sport.tischtennis`

**Textentwurf (131 Wörter):**

Der TTC Merzenich richtet am Samstag, 31. Oktober, von 13:30 bis 16:30 Uhr einen AndroCup in der Turnhalle Merzenich aus. Das Turnier ist auf 16 Teilnehmerinnen und Teilnehmer begrenzt; die Anmeldung läuft über das Portal mytischtennis.de.

Im Ligabetrieb stehen in dieser Woche zwei Auswärtsspiele an: Am Donnerstag, 29. Oktober, spielt die erste Mannschaft um 19:30 Uhr bei den TTF Weisweiler/Wenau IV, am Freitag, 30. Oktober, um 20 Uhr bei TTC Mersch-Pattern IV. Das nächste Heimspiel bestreitet die zweite Mannschaft am Montag, 2. November, um 20 Uhr gegen den TTV Gey II in der Turnhalle an der Schulstraße 4.

Der Verein sucht nach eigenen Angaben weiterhin Spielerinnen und Spieler mit Ligaerfahrung für die erste und zweite Herrenmannschaft.

**Quellen:** Termin-Schnittstelle der Vereinsseite https://ttc-merzenich.de/wp-json/ttc/v1/events und …/manual-events (Abruf 15:45/15:46 Uhr); Startseite https://ttc-merzenich.de (15:39 Uhr). Die Ergebnis- und Tabellenseiten laden nur per JavaScript; Ergebnis des Heimspiels TTC III – TTC indeland Jülich IX vom 16.10. **nicht belegbar**.
**Faktenbelege:** AndroCup Sa. 31.10.2026, 13:30–16:30, Turnhalle Merzenich, max. 16 Teilnehmer, Anmeldung mytischtennis.de · 29.10. 19:30 Weisweiler/Wenau IV – TTC Merzenich · 30.10. 20:00 Mersch-Pattern IV – TTC Merzenich · 02.11. 20:00 TTC Merzenich II – TTV Gey II (H) · Spielersuche „2. Bezirksklasse“ ohne Datum
**Offene Fragen:** Welche Mannschaft spielt am 29./30.10. (die Schnittstelle nennt „TTC Merzenich“ ohne Nummer)? Startgeld und Altersklassen des AndroCups? Ergebnisse seit 11.10.?
**Textfreigabe:** bedingt (Mannschaftszuordnung prüfen) · **Dublette:** keine (Bestand: TV Golzheim Tischtennis vom 04.10.). AndroCup als Termin in `inhalte/termine/` anlegen.

### Weitere Sportarten (Befunde, kein Text)

- **TV Merzenich 1882:** Website nur per HTTP lesbar; Meldungen „Übungsleitung gesucht“ (u. a. Eltern-Kind-Turnen), neuer Vorstand (Wahl Mai/Juni), Lagerraum-Umzug, alle ohne Datum. Kein Nachrichtenwert der letzten 14 Tage; „Übungsleitung gesucht“ passt als Nebensatz zu Karte MA-1025-08.
- **SC Merzenich Tennis/Badminton:** keine datierten Meldungen; Mannschaftsseiten auf Stand 2023.
- **Düren Demons (Vereinssitz Merzenich):** „Senior Rebuild“ seit 31.08.; keine Termine. Kein Text.
- **BSC Merzenich (Billard):** jüngste News 21.07.2026. Ligaseiten des BVNR nicht ausgewertet (Nachfassen: Spielplan Oberliga/Verbandsliga Dreiband für November).
- **TV 1910 Girbelsrath:** jüngste Meldung 01.10. (Hallenbosseln), im Bestand (16.10.).
- **Marianische Schützenbruderschaft Golzheim:** Website heute gesperrt (403).
- **Heimat-Info:** keine Sportvereinsmeldung der letzten 14 Tage im sichtbaren Feed; die Vereinsprofile laden Beiträge nur per JavaScript (Nachfassen im Browser).

---

## 5. Quellenabdeckung je Ressort (tatsächlicher Prüfstatus)

| # | Ressort | Geprüfte Quellen (Status) | Ergebnis |
|---|---|---|---|
| 1 | Rathaus / Rat / Amtsblatt | Ratsinfosystem `termine.ics` (200, gelesen), RIS News/Vorlagen (200, keine News; Vorlagen nur per Formular), Amtsblatt-Liste und PDF 11/12 (PDF 11 vorhanden, 12 nicht), Gemeinde-Pressemitteilungen (Liste als Teaser aus Rohdaten; Detailseiten JS-Sperre), Heimat-Info Gemeindekanal (200, gelesen) | Karten 04, 07, 12, 13, 15; Lücke: Gemeinde-Detailseiten, Niederschriften |
| 2 | Ortsteile | Heimat-Info Ortsbeiträge, Amtsblatt-Termine im Bestand, Martinszüge je Ortsteil | Karten 01 (Golzheim/L264), 04 (Golzheim), 09 (alle), 20 (gesperrt) |
| 3 | Polizei / Feuerwehr | Presseportal Merzenich-Seite (200, bis 25.09.), Polizei Düren 72 h (200, 4 Meldungen, keine mit Ortsbezug), Polizei Rhein-Erft (200), feuerwehr-merzenich.de (200, bis 140/26), Heimat-Info Feuerwehr (142/26, 143/26) | Karten 01, 19, 33 |
| 4 | Verkehr / Baustellen / ÖPNV | Autobahn-API A4/A61/A44 (200), go.Rheinland Baustellenübersicht (200), VRS (200), Stadt Düren Presse (200), Rurtalbus Feed (200); Straßen.NRW (keine Kreis-Düren-Inhalte lesbar), zuginfo.nrw/DB (JS), AVV (401), DKB (Zertifikat) | Karte 25; Lücken: Straßen.NRW, S-19-Details |
| 5 | Fußball und weitere Sportarten | fupa.net (200, Spiele/Tabellen), fussball.de (200, Tabellen als Text, Ergebnisse Icon-Schrift, Störungsbanner), scmerzenich.de (200), ttc-merzenich.de API (200), tv-merzenich.de (nur HTTP), bsc-merzenich.de (200), tv-girbelsrath.com (200), demonsfootball.de (200); fc-golzheim.de, sv-morschenich1925.de, scm-badminton.de nicht erreichbar | Karten 23, 24 + Befunde; Sonntagsergebnisse offen |
| 6 | Vereine / Ehrenamt | Heimat-Info Vereinsfeed (200), 41 Vereinsprofile (nur Stammdaten, Beiträge per JS), schuetzen-golzheim.de (403), LAG Inde-Rur (Heimat-Info) | Karten 10, 18, 26 |
| 7 | Schulen / Kitas / Jugend | KGS Merzenich (200, nichts Neues seit 21.09.), KGS Golzheim (JS-Sperre), Gemeinde-Stellen (200), RIS Jugendgremium, Stadt Düren Schulen-PM | Karten 08, 14, 17, 32 |
| 8 | Kirchen / Soziales / Senioren | st-laurentius-merzenich.de (200, Stand 2019), Heimat-Info St. Lambertus und ev. Gemeinde, Kreis Düren (Persönliches Budget, Seniorenweihnacht, Mental Load) | Karten 02, 06, 16, 21, 29, 31 |
| 9 | Kultur / Veranstaltungen | Heimat-Info Veranstaltungen (200), Terminbestand, Fanclub, Krätzchensitzung (Gemeinde über Heimat-Info) | Karten 10, 09; Krätzchen-Vorverkauf = Dublette (seit 16.10. auf der Seite) |
| 10 | Wirtschaft / Arbeitsplätze / Immobilien | Gemeinde-Stellenseite (200), Kreis Düren (Meisterstipendium, VISIONplus, Erntedank), jobsdn.de (200, leer); Immobilien: täglicher Markt-Abgleich der CI (Commit 09:12 UTC), nicht erneut geprüft | Karten 08, 28, 30 |
| 11 | Umwelt / Energie | Kreis Düren Eichel-Sammelaktion (200), Gemeinde Klimaanpassung (Bestand), Amprion/Stromtrasse (Bestand) | Karte 27 |
| 12 | Strukturwandel / Hambach / Bürgewald | Neuland Hambach (200), RWE (teilweise JS), Bezirksregierung Arnsberg Bekanntmachungen (200, Hambach-Filter), RIS Ausschuss Bürgewald 26.11., Heimat-Info (RWE-Infomesse, Fußmarsch nach Bürgewald) | Karten 05, 11, 12, 22 |

Nachbarkommunen: Düren (200, gelesen), Elsdorf (200, gelesen, nichts mit Wirkung), Niederzier, Nörvenich, Kerpen (kdvz-JavaScript-Sperre, nicht lesbar).

## 6. Terminüberblick 26. Oktober bis 8. November 2026 (14 Tage) mit Ausblick

Quelle je Zeile: B = Terminbestand `inhalte/termine/` (bereits auf der Seite), N = neu aus dieser Recherche (Karte in Klammern).

| Datum | Uhrzeit | Termin | Ort | Quelle |
|---|---|---|---|---|
| Mo 26.10. | – | Anmeldeschluss Aktionstag „Persönliches Budget“ | online | N (06) |
| Mo 26.10. bis Do 29.10. | 9–15 Uhr | A4 Richtung Köln: Wiegeanlage zwischen AK Aachen und AS Düren | A4 | N (25) |
| Mo 26.10. bis Fr 30.10. | ganztägig | Düren: Bismarckstraße stadteinwärts gesperrt | Düren | N (25) |
| bis Fr 30.10. | 21–5 Uhr | Bahn Düren–Köln eingleisig, RE 9 fällt aus; Sondernächte 26./27. und 27./28.10. auch S 19 | Strecke Düren–Köln | N (25) |
| Di 27.10. | 18–19 Uhr | Weckmann-Bons Morschenich | Bürgewaldzentrum | B (09) |
| Di 27.10. bis Mi 28.10. | 21–5 Uhr | A4 Richtung Aachen zwischen Eschweiler-Ost und Weisweiler gesperrt | A4 | N (25) |
| Mi 28.10. bis Fr 30.10. | 14:30–16 Uhr | Weckmann-Bons Girbelsrath (auch Mo 2.11.) | Kita Villa Wichtel | B (09) |
| Do 29.10. | 9 Uhr | Wildpferdeexkursion Sophienhöhe (Anmeldung) | Wanderparkplatz Sophienhöhe | N (05) |
| Do 29.10. | 19:30 Uhr | Tischtennis: TTF Weisweiler/Wenau IV – TTC Merzenich | Weisweiler | N (24) |
| Fr 30.10. | 18 Uhr | Bürgersprechstunde mit dem Bürgermeister | Pfarrheim Golzheim, Pastoratstraße 20 | N (04) |
| Fr 30.10. | 20 Uhr | Tischtennis: TTC Mersch-Pattern IV – TTC Merzenich | Mersch | N (24) |
| Sa 31.10. | – | Bewerbungsschluss Meisterstipendium Kreis Düren | online | N (28) |
| Sa 31.10. | 13:30–16:30 Uhr | AndroCup des TTC Merzenich | Turnhalle Merzenich | N (24) |
| Sa 31.10. | 19:48 Uhr | 59. Bierabend des 1. FC Köln Fanclubs (Marita Köllner) | Weinberghalle | B (10) |
| So 1.11. | ab 9:30 Uhr | Allerheiligen: Gottesdienste und Gräbersegnungen in den Ortsteilen | Gemeinde | B |
| Mo 2.11. | 18 Uhr | Martinszug Morschenich | Ludwig-Rixen-Platz | B |
| Mo 2.11. / Di 3.11. | 7:30–10:30 Uhr | Weckmann-Bons Merzenich; abends ab 18 Uhr in den Kitas (2./3./4.11.) | Grundschule Schulstraße | B (09) |
| Mo 2.11. | 20 Uhr | Tischtennis: TTC Merzenich II – TTV Gey II | Turnhalle Schulstraße 4 | N (24) |
| Mo 2.11. bis Fr 6.11. | – | Wanderausstellung „Persönliches Budget“ | Kreishaus Düren | N (06) |
| Mi 4.11. | 17 Uhr | 4. Sitzung des Jugendgremiums | Bürgerhaus, Burgstraße 9 | B (14) |
| Do 5.11. | 10–17 Uhr | Aktionstag „Das Persönliche Budget“ | Kreishaus Düren, Raum A 158 | N (06) |
| Do 5.11. | 18 Uhr | 2. Sitzung des Rechnungsprüfungsausschusses | Ratssaal | B (13) |
| ab Do 5.11. | – | Infoabende der weiterführenden Schulen in Düren | Düren | N (32, Termine offen) |
| Fr 6.11. | 14–17 Uhr | Eichel-Sammelaktion für den Hürtgenwald | Parkplatz Burg Laufenburg | N (27) |
| Fr 6.11. | 20 Uhr | Tischtennis: TTC Dürboslar III – TTC Merzenich; TTC Merzenich II – BW Wollersheim III (H) | Dürboslar / Merzenich | N (24) |
| Sa 7.11. | 18 Uhr | Tischtennis: Alemannia Aachen III – TTC Merzenich | Aachen | N (24) |
| Sa 7.11. | 20:11 Uhr | Große Kostümsitzung der KG Mir hahle Poohl | Golzheim | B |
| So 8.11. | 10 Uhr | Bücher- und Spielemarkt der IG Golzheim aktiv | Golzheim | B |
| So 8.11. | 11 Uhr | Kindergottesdienst im Evangelischen Gemeindehaus; Tischtennis TTC IV – TV Düren V (H) | Merzenich | B / N (24) |
| So 8.11. | 14:30 Uhr | Fußball: FC Golzheim – SV GW Welldorf-Güsten; Rhenania Girbelsrath – SV Morschenich (Ortsderby) | Golzheim / Girbelsrath | N (23) |
| So 8.11. | 15 Uhr | Fußball: SC Merzenich – FC Düren 77 | Ellbachstadion | N (23) |

**Ausblick ab 9.11.:** 9.11. bis 8.12. Internet-Auslegung Tagebausee Hambach, Einwendungen bis 22.12. (B, Karte 11) · 10.11. VHS-Bildvortrag „Paris“, Seniorenhaus Marienhof (B) · 11.11. Martinszug Golzheim (B) · 12.11. Bau-, Planungs- und Umweltausschuss und Martinszug Merzenich (B) · 13.11. Martinsfeier Girbelsrath (B) · 15.11. Volkstrauertag, Taizé-Gottesdienst (B) · 17.11. „Pluriversum“ (B) · 18.11. Online-Vortrag Mental Load (N, 29) · 19.11. Haupt- und Finanzausschuss (B) · 20.11. Frist Zeltplatz Finkenheide (N, 26) · 21.11. Tag der Modelleisenbahn (B) · 24.11. Seniorenweihnacht Arena Kreis Düren (N, 31) · 26.11. Ausschuss Bürgewald (B) · 28./29.11. Adventmarkt Heimatmuseum (B) · 3.12. Gemeinderat (N, 07) · 23.01.2027 Krätzchensitzung (B).


---

## 7. Bildrecherche (Wikimedia Commons) · Ergebnis mit Sperre

**Zähler (getrennt, tatsächlich):**

| Schritt | Zahl |
|---|---:|
| gefunden (neue Titel mit plausiblem Bezug zur Gemeinde, nicht im Pool) | 4 |
| lizenzgeprüft | 0 |
| sichtgeprüft (Thumbnail angesehen) | 0 |
| heruntergeladen (Thumbnail 800 px) | 0 |
| dublettengeprüft (Titelabgleich gegen 2.227 vorhandene Pool-Titel) | 4 |
| aus früheren Berichten übernommen | 0 (keine neuen Kandidaten aus dem 14.10.-Dossier offen; Pool-Stand siehe `docs/EDITORIAL-BILDPOOL-RESEARCH-2026-10-16.md`) |
| integriert | 0 (keine Repositoryänderung) |

**Warum das Ziel (10 bis 20 Kandidaten) nicht erreicht wurde:** Die Commons-API hat die gemeinsame Ausgangs-IP dieser Sitzung nach vier Suchanfragen mit HTTP 429 („too many requests“) gedrosselt; Backoff bis 125 Sekunden, zehn Minuten Pause, ein zweiter Durchlauf mit 10-Sekunden-Abständen und eigener Kennung sowie der Ausweichweg über die HTML-Dateiseiten und `Special:Search` scheiterten ebenfalls (429, zuletzt 16:08 Uhr MEZ). Von 40 geplanten Suchbegriffen wurden 4 ausgeführt („Merzenich“, „Merzenich Wasserturm“, „Merzenich Laurentius“, „Merzenich Rathaus“). Für die Pools Sturm/Feuerwehr, St. Martin, Senioren, Wandern, Eicheln/Hürtgenwald, Karneval, Nahverkehr, Stromtrasse, Modelleisenbahn, Advent, Blutspende, Bauhof, Ratssaal, Sophienhöhe/Konik, Kreishaus Düren liegt **keine Aussage** vor.

**Kandidaten (alle ungeprüft; Lizenz, Urheber, Datum, Maße, Sichtprüfung offen):**

| # | Datei (Commons) | Pool | Status | Sperrthemen / Hinweise |
|---|---|---|---|---|
| K1 | https://commons.wikimedia.org/wiki/File:St_Laurentius_Merzenich_Westseite.JPG | Ortsmotive Merzenich (Kirche) | ungeprüft; HTML-Seite einmal 200, aber ohne auswertbare Lizenzfelder (vermutlich gedrosselte Minimalseite) | eng verwandt mit Pool-Titeln „St. Laurentius Merzenich.jpg“, „…Hauptportal.JPG“: Variantenprüfung |
| K2 | https://commons.wikimedia.org/wiki/File:St._Laurentius_Merzenich_Kirchturm.jpg | Ortsmotive Merzenich (Kirche) | ungeprüft (429) | mögliche Variante zu K1 |
| K3 | https://commons.wikimedia.org/wiki/File:Merzenich_Denkmal-Nr._21,_Mühlenstraße,_Ecke_Mühlendriesch_(1239).jpg | Ortsmotive Merzenich (Baudenkmal) | ungeprüft | Sichtprüfung auf Hausnummern und Privatgrund zwingend; Treffer kam bei Suche „Wasserturm“, Umfeld unbestätigt |
| K4 | https://commons.wikimedia.org/wiki/File:Bucherschrank_Merzenich_2023-12-25.jpg | Ortsmotive Merzenich (Bücherschrank) | ungeprüft; Vorschau 960 × 641 px (unter Mindestbreite 1000 px) | nur Füllbild; Datum laut Titel vermutlich 25.12.2023 |

**Ausgeschlossen:** `Merzenich (Zülpich) 001x.jpg` (Luftaufnahme 2016): Aufnahmeort ist Merzenich bei Zülpich, Kreis Euskirchen, **nicht** die Gemeinde Merzenich; darf nie als Ortsbild verwendet werden. `Duren from Bogheim Mosaic.jpg`: kein Merzenich-Bezug. Fremde Laurentius-Kirchen (Ebern, Duisburg, Künzing u. a.): irrelevant.

**Bereits im Pool bestätigt (nicht neu gezählt):** Merzenich Windmühle Wasserturm HDR.jpg · St. Laurentius Merzenich.jpg · St. Laurentius Merzenich Hauptportal.JPG · Kath. Pfarrkirche St. Laurentius, Merzenich.jpg · Merzenich Rathaus HDR.jpg · Merzenich Rathaus.JPG · Merzenich Walze Löhr HDR.jpg.

**Bildbedarf aus den Themenkarten (für den nächsten Importlauf):**

| Karte | Bedarf | Vorhandene Bildklasse | Vorschlag |
|---|---|---|---|
| 01 Sturm | Baum/Äste auf Straße, Feuerwehr mit Kettensäge, ohne Gesichter/Kennzeichen | keine | neue Klasse `blaulicht.technik.sturm`, Motive `baum-auf-strasse`, `sturmschaden`; Suchbegriffe „Sturmschaden Baum Straße“, „Windbruch Fahrbahn“, „Feuerwehr Kettensäge Sturm“ |
| 02 Senioren | Kaffeetafel, Akkordeon, Halle | `leben.senioren` | prüfen, ob der Pool ein Motiv ohne erkennbare Gesichter hat |
| 03 Schüler-Fußmarsch | Wanderer mit Rucksack/Bollerwagen auf Landstraße | keine | neue Klasse `menschen.wandern`; oder Freigabe der Gemeindefotos (Stephan Jonen) |
| 05 Sophienhöhe | Konik-Pferde, Sophienhöhe, Goldene Aue | `rathaus.tagebau` | Suchbegriffe „Sophienhöhe“, „Konik Sophienhöhe“, „Goldene Aue Hambach“ |
| 27 Eicheln | Eicheln sammeln, Burg Laufenburg, Hürtgenwald | `leben.klima` (Notlösung) | neue Klasse `umwelt.wald`; Suchbegriffe „Eicheln“, „Quercus robur acorns“, „Burg Laufenburg“ |
| 26 Zeltplatz | Jugendzeltlager | keine | `vereine.jugend`-Pool prüfen |
| 25 Verkehr | S-Bahnhof Merzenich, Baustelle | `verkehr.baustelle` | Pool vorhanden (Bahnsteig Merzenich, CC BY-SA 3.0, bereits im Foto-des-Tages-Bestand) |

**Empfehlung:** Den Bildimport nicht aus dieser Sitzung wiederholen, sondern wie bisher über GitHub Actions („Materialize“, `deploy/import-editorial-photos.mjs`) mit den oben genannten Suchbegriffen und Pools laufen lassen; dort gilt eine andere IP. Danach Sichtung und Motivvergabe wie in `docs/BILD-MOTIVREGELN.md` (`deploy/editorial-photo-review.json`, `deploy/bildmotive.json`). Keine Pressefotos (Gemeinde, Feuerwehr, Neuland/RWE, Kreis Düren) ohne schriftliche Freigabe; ein Copyright-Vermerk ist keine Freigabe.

Arbeitsdateien der Bildrecherche: `/tmp/claude-0/-home-user/afce8bd5-e64a-5028-af08-404a6d47c480/scratchpad/dossier/bilder/` (Suchliste `queries.txt`, Skript `commons.py`, Bericht `bericht.md`), nicht im Repository.
## 8. Priorisierte Nachfassliste (konkrete Fragen, keine Mails automatisch)

1. **Sonntagsergebnisse Fußball (Mo 26.10., früh):** Endstände Hambacher SV – SC Merzenich, SV Merken – Rhenania Girbelsrath, SV Morschenich – FC Golzheim II, SC Merzenich III – Gereonsweiler, SC Merzenich II in Berzbuir (23.10.). Quelle: fupa.net Spielberichte mit „Abpfiff“, fussball.de. Dann `sport-current.json` als Einheit aktualisieren.
2. **Feuerwehr 141 bis 143/26:** Erscheinen die Einsatzseiten auf feuerwehr-merzenich.de? Alarmzeiten, Fahrzeuge, weitere Sturmeinsätze am 24./25.10.? Vor Veröffentlichung von MA-1025-01 entscheiden: Sammelmeldung jetzt (und Automatik-Entwürfe später verwerfen) oder auf die Automatik warten.
3. **S 19 in den Nächten 26./27. und 27./28.10.:** zuginfo.nrw im Browser; konkrete Ausfälle für Merzenich nachtragen (MA-1025-25).
4. **Gemeinde-Pressemitteilungen im Browser lesen** (JavaScript-Prüfung): Krätzchensitzung-Vorverkauf (Vergleich mit Bestand), GPA-Prüfung (MA-1025-13), Martinszüge (Vergleich). Frage an die Gemeinde: Gibt es einen RSS-/Presseverteiler, der die Sperre umgeht?
5. **Strukturausschuss 15.10.:** Niederschrift im RIS (Gremium „Ausschuss für Wirtschaftsförderung und Strukturentwicklung“), Fragen: OGE-Antrag auf Raumverträglichkeitsprüfung gestellt? Korridor A05 bei Merzenich konkretisiert? RWE: Aussichtspunkt Morschenicher Bucht, Werksbahn? (MA-1025-12)
6. **Tagebausee-Auslegung:** Am 8./9.11. prüfen, ob die Unterlagen auf bra.nrw.de stehen; Frage an das Rathaus: Einsicht auch in Merzenich, obwohl die Bekanntmachung nur Niederzier und Elsdorf nennt? Informiert die Gemeinde ihre Bürger über die Einwendungsfrist 22.12.? (MA-1025-11)
7. **Jugendgremium 4.11.:** Tagesordnung im RIS (ab ca. 28.10.) lesen; Vorbericht schreiben (MA-1025-14).
8. **Rechnungsprüfungsausschuss 5.11.:** Tagesordnung und GPA-Bericht; was wurde beanstandet? (MA-1025-13)
9. **Seniorennachmittag:** Datum 17.10. bestätigen; Nennung der Organisatorin klären; Fotos der Gemeinde anfragen (MA-1025-02).
10. **Siegener Schüler:** Gemeinde fragen: Übernachtungsort, Datum, Fotos von Stephan Jonen freigegeben? Schule (Evangelisches Gymnasium Siegen-Weidenau) nach Rückkehrtermin fragen (MA-1025-03).
11. **Bürgersprechstunde 30.10.:** Reihe für alle Ortsteile? Ende der Sprechstunde? (MA-1025-04)
12. **Übungsleiter Sportkita Bärenstark:** PDF (15 MB) lesen: Umfang, Vergütung, Frist (MA-1025-08).
13. **Weiterführende Schulen Düren:** Detailseite der Stadt lesen, Termine je Schule (MA-1025-32).
14. **Straßen.NRW Kreis Düren:** Baustellenliste im Browser; gibt es Sperrungen an L264, L257, K-Straßen im Gemeindegebiet?
15. **Amtsblatt 12/2026:** ab 12.11. täglich PDF-Adresse prüfen (MA-1025-15).
16. **Pfarrbrief St. Laurentius:** PDF laden, November-Gottesdienste (MA-1025-16).
17. **KGS Golzheim, Niederzier, Nörvenich, Kerpen:** im Browser prüfen (JavaScript-Sperre).
18. **TTC Merzenich:** Ergebnisse seit 11.10. (Seite lädt per JS); Mannschaftszuordnung der Auswärtsspiele 29./30.10. (MA-1025-24).
19. **Bierabend 31.10.:** Fanclub nach Verzehrkartenpreis und Restkarten fragen (MA-1025-10).
20. **Ernte 2026 in der Gemeinde:** zwei bis drei Betriebe für einen Hintergrund anfragen (MA-1025-30).

---

## Anhang A · Fußballtabellen (fussball.de, Abruf 25.10.2026, 15:40 und 15:45 Uhr MEZ, ohne die Sonntagsspiele vom 25.10.)

**Kreisliga A Düren** (Quelle: https://www.fussball.de/mannschaft/sc-1919-merzenich-sc-merzenich-mittelrhein/-/saison/2627/team-id/011MIEKMN4000000VTVG0001VTR8C1K7)

| Pl. | Mannschaft | Sp. | S | U | N | Tore | Diff. | Pkt. |
|---|---|---|---|---|---|---|---|---|
| 1 | SV Grün-Weiß Welldorf-Güsten | 10 | 8 | 1 | 1 | 52:17 | 35 | 25 |
| 2 | SC Jülich 1910/97/Hoengen | 9 | 8 | 1 | 0 | 26:7 | 19 | 25 |
| 3 | FC Golzheim | 10 | 8 | 1 | 1 | 26:9 | 17 | 25 |
| 4 | BC Oberzier | 9 | 7 | 1 | 1 | 25:8 | 17 | 22 |
| 5 | Spvg. SW 1896 Düren | 10 | 6 | 3 | 1 | 43:16 | 27 | 21 |
| 6 | SC 1919 Merzenich | 9 | 7 | 0 | 2 | 38:16 | 22 | 21 |
| 7 | SG Nörvenich-Hochkirchen | 9 | 4 | 0 | 5 | 13:23 | -10 | 12 |
| 8 | SV Siersdorf | 9 | 3 | 2 | 4 | 10:25 | -15 | 11 |
| 9 | SV SW Huchem-Stammeln | 7 | 3 | 1 | 3 | 14:11 | 3 | 10 |
| 10 | Jugendsport Wenau | 10 | 3 | 0 | 7 | 21:27 | -6 | 9 |
| 11 | JSV Frenz | 8 | 2 | 1 | 5 | 20:24 | -4 | 7 |
| 12 | Borussia Freialdenhoven | 10 | 2 | 1 | 7 | 19:44 | -25 | 7 |
| 13 | SV Viktoria Koslar | 10 | 1 | 2 | 7 | 17:37 | -20 | 5 |
| 14 | Hambacher Spielverein | 9 | 1 | 1 | 7 | 7:24 | -17 | 4 |
| 15 | SC Salingia 08 Barmen | 9 | 1 | 1 | 7 | 8:29 | -21 | 4 |
| 16 | FC Düren 77 | 8 | 1 | 0 | 7 | 12:34 | -22 | 3 |

**Kreisliga B Staffel 2 Düren** (Quelle: https://www.fussball.de/mannschaft/sv-morschenich-sv-morschenich-mittelrhein/-/saison/2627/team-id/011MIEFL7S000000VTVG0001VTR8C1K7)

| Pl. | Mannschaft | Sp. | S | U | N | Tore | Diff. | Pkt. |
|---|---|---|---|---|---|---|---|---|
| 1 | FC Inden/Altdorf 09/21 | 7 | 6 | 1 | 0 | 30:6 | 24 | 19 |
| 2 | SV Merken | 8 | 6 | 1 | 1 | 19:14 | 5 | 19 |
| 3 | SV Morschenich | 8 | 6 | 0 | 2 | 41:16 | 25 | 18 |
| 4 | CSV Düren | 8 | 5 | 1 | 2 | 35:10 | 25 | 16 |
| 5 | TuS 08 Langerwehe II | 8 | 4 | 2 | 2 | 31:14 | 17 | 14 |
| 6 | Sportfreunde 1919 Düren II | 9 | 5 | 2 | 2 | 28:18 | 10 | 14 |
| 7 | FC Golzheim II | 7 | 3 | 1 | 3 | 27:15 | 12 | 10 |
| 8 | Viktoria Arnoldsweiler II | 7 | 3 | 0 | 4 | 11:21 | -10 | 9 |
| 9 | Rhenania Mariaweiler | 8 | 2 | 2 | 4 | 18:30 | -12 | 8 |
| 10 | Spvg. SW 1896 Düren II | 8 | 2 | 1 | 5 | 13:22 | -9 | 7 |
| 11 | FC Viktoria Schlich | 8 | 2 | 1 | 5 | 12:28 | -16 | 7 |
| 12 | SG Türkischer SV Düren II | 9 | 2 | 0 | 7 | 21:46 | -25 | 6 |
| 13 | FC Rhenania Girbelsrath | 5 | 1 | 0 | 4 | 13:25 | -12 | 3 |
| 14 | 1. FC Düren II | 8 | 1 | 0 | 7 | 8:42 | -34 | 3 |

**Spiel- und Vereinsquellen (fupa.net, 15:38 bis 15:47 Uhr MEZ):** https://www.fupa.net/club/sc-merzenich · https://www.fupa.net/club/fc-golzheim · https://www.fupa.net/club/rhenania-girbelsrath · https://www.fupa.net/club/sv-morschenich · Spielberichte: https://www.fupa.net/match/sc-merzenich-m1-jugendsport-wenau-m1-261018 · https://www.fupa.net/match/viktoria-koslar-m1-fc-golzheim-m1-261024 · https://www.fupa.net/match/fc-golzheim-m1-sv-siersdorf-m1-261018 · https://www.fupa.net/match/sportfreunde-dueren-m2-rhenania-girbelsrath-m1-261018 · https://www.fupa.net/match/schwarz-weiss-dueren-m2-sv-morschenich-m1-261018 · offen (25.10.): https://www.fupa.net/match/hambacher-spielverein-m1-sc-merzenich-m1-261025 · https://www.fupa.net/match/sv-merken-m1-rhenania-girbelsrath-m1-261025 · https://www.fupa.net/match/sv-morschenich-m1-fc-golzheim-m2-261025 · Vereinsbericht: https://www.scmerzenich.de/home/aktuelles-1/spieltagbericht-07-26-27/

---

## Anhang B · Status der Arbeitsfolge

| Schritt | Status |
|---|---|
| RESEARCH | ausgeführt (12 Ressorts, Rohdaten + Live-Abrufe, drei parallele Teilrecherchen Sport, Bilder, Umkreis) |
| VERIFY | ausgeführt für jede Karte mit Text; Einordnungssätze ohne Quelle sind in den Karten benannt |
| IMAGE RIGHTS | nur Regeln angewendet; Commons-Prüfung durch Drosselung blockiert (Abschnitt 7) |
| CONTENT | 16 Entwürfe nach Redaktionsstandard V2 |
| FRONTEND | nicht ausgeführt |
| QA | nicht ausgeführt (`node deploy/kette.mjs`, `node qa/pruefung.mjs` stehen aus) |
| PUSH-STATUS | kein Commit, kein Push, keine Repositoryänderung, keine Veröffentlichung, kein Backend-Login |

**Nächster Schritt für die Claude-Code-Sitzung:** `PROJECT.md`, `docs/REDAKTIONELLER-STANDARD-V2.md`, `docs/BILD-MOTIVREGELN.md` und `git status` lesen; dann die freigegebenen Karten als `inhalte/meldungen/2026-10-25-recherche-claude.json` (redaktionsstandard 2, artikeltyp, bildklasse) und die neuen Termine als `inhalte/termine/2026-10-25-recherche.json` anlegen, Umkreis-Einträge als `inhalte/umkreis/2026-10-25.json`; Kette zweimal bauen, `--check`, `qa/pruefung.mjs`; Commit, Push. Die Meldungen erscheinen danach als Entwürfe in den WordPress-Freigaben; veröffentlicht wird dort von der Redaktion.
