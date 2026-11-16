# Merzenich Aktuell – Morgenlauf (Claude), 16. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „09:13“ in der festen Redaktionssitzung, Bearbeitung 09:18 bis 09:35 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 15.11. nachmittags](2026-11-15-claude-nachmittags.md), seither neu [ChatGPT 16.11. morgens](2026-11-16-morgens.md). main vor dem Lauf: `ef0d220a`. Eil-Prüfungen 15.11. 16:40, 18:43, 20:41 Uhr und 16.11. 06:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Fußball-Wochenende 14./15.11. (318 Wörter im Fließtext, Sport, `relevanz` 3) |
| Korrigierte Bestandsmeldungen | 0 neu | ChatGPT morgens: nur der bekannte Bildfehler Malwettbewerb, live weiter offen (Werkzeugpunkt, Abschnitt 7) |
| Spielstand | aktualisiert | letztes Spiel Frenz – SC 1:0, nächstes 29.11. gegen Huchem-Stammeln, Tabelle nach dem 12. Spieltag |
| Neue / geänderte Termine | 0 / 0 | |
| Termine an der Quelle bestätigt | 21 | alle Termine 17. bis 29.11., Stand 2026-11-16 |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | 10 Quellen, 4 Aussagen, alle mit offizieller Quelle (FUSSBALL.DE) |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Sportplatz aus der Luft (Pool `sport`) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 4 Hinweise (FuPa für das Prüfwerkzeug HTTP 403, nur Tabellen) | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-SONNTAGSSPORT-ERG-1511 und MA-FUSSBALL-GIRB-LANGERWEHE-1411 | „SC Merzenich verliert 0:1 in Frenz, Morschenich schlägt Tabellenführer Inden/Altdorf 4:3“ (Kurzmeldung): SC Vierter mit 27 Punkten, Golzheim nach 2:2 bei SW Düren punktgleich Fünfter; Morschenich 4:3 nach 2:3 zur Pause, Tabellenplatz je nach Quelle 1 (FuPa mit Derby-Urteil) oder 2 (FUSSBALL.DE); Girbelsrath 0:3 in Langerwehe, Vorletzter; nächste Spiele 20.11., 29.11. und 06.12. | `inhalte/meldungen/2026-11-16-recherche-claude-morgens.json` → `/sport/fussball-merzenich-0-1-frenz-morschenich-4-3-inden-altdorf-golzheim-2-2-sw-dueren-2026-11-15/` | vier FUSSBALL.DE-Spielberichte (15.11., automatisch aus DFB-Spieldaten), SC-Mannschaftsseite mit offizieller Tabelle, FuPa-Tabellen und -Spieltage A und B2, gelesen 09:20 bis 09:23 Uhr; Commits `507e8f12`, `1673baf3` (Abrufzeit im Hinweis), `60af284d` (Torminute) |
| Spielstand | `chatgpt-site/api/sport-current.json`: letztes Spiel JSV Frenz – SC Merzenich 1:0 (15.11.), nächstes SC Merzenich – SV SW Huchem-Stammeln (29.11., 15 Uhr, Heimspiel laut Mannschaftsseite), Tabelle Kreisliga A nach 12 Spieltagen | dieselbe Datei | FUSSBALL.DE-Mannschaftsseite 09:21 Uhr; Tabelle FUSSBALL.DE und FuPa gleich; Commit `507e8f12` |
| Kalender | 21 Termine bis 29.11. an der Quelle bestätigt, Stand 2026-11-16 | 10 Termindateien | Abschnitt 6; Commit `4afd926b` |

Quellenausnahmen: ChatGPT führt beide Sportkarten als T2 (Spielberichte und FuPa nicht als unabhängig bestätigt). Umgesetzt als Kurzmeldung, weil FUSSBALL.DE die offiziellen Spieldaten des DFB wiedergibt (SCHREIBREGELN Abschnitt 0, wie bei der Sportmeldung vom 10.11.). Aus den automatisch erzeugten Berichten keine Spielernamen, keine Trainer- oder Schiedsrichternamen, keine Serien- oder Wertungsaussagen. Torminuten nur, wo FuPa-Liveticker und Bericht übereinstimmen (7., 15.) oder ausdrücklich dem Spielbericht zugeschrieben (83., 63., 69.); die Minute des 2:1 in Düren fehlt, weil der Bericht die 16. und FuPa die 30. Minute nennt. Der Widerspruch beim Tabellenplatz Morschenich ist im Text benannt und erklärt (Derby-Urteil 2:0 vom 8. November nur bei FuPa eingerechnet; FUSSBALL.DE 53 Tore, FuPa 55). Golzheim II (2:2 bei SW Düren II laut FuPa) nicht aufgenommen: nur FuPa, kein Verbandsbeleg gelesen.

Korrektur im Lauf: Der Hinweis im Paket nannte zunächst „09:25 bis 09:40 Uhr“ als Abrufzeit, `generated` im Spielstand 09:40 Uhr; berichtigt auf die Dateizeiten 09:20 bis 09:23 bzw. 09:24 Uhr (Commit `1673baf3`).

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung und Spielstand ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Bauausschuss 12.11. | Sitzungsseite im Ratsinformationssystem 09:29 Uhr: weiter nur Tagesordnung und Vorlagen, keine Beschlüsse oder Niederschrift |
| Golzheim II, 2:2 bei SW Düren II | nur FuPa gelesen |
| Kreis-Presse | jüngste Mitteilungen vom 11. und 12.11. („Eichel-Sammelaktion in Langerwehe“, „Jugendzeltplatz sucht neuen Eigentümer“, „Infoabend am Berufskolleg für Technik“, „Von Herzen für Herzen helfen“) ohne erkennbaren Merzenich-Bezug in den Titeln |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 16.11. morgens, zuerst KORREKTUREN | keine neue Korrektur; Advents-Übersicht ohne Abweichung im geprüften Terminkern; neue Karte Sonntagsergebnisse (T2) |
| Polizei Düren | Dienststellenliste nr/8, 09:19 Uhr | letzte Meldung 14.11. 08:09 Uhr (Düren), nichts mit Merzenich |
| Feuerwehr | Website und Heimat-Info-Profil, 09:19 Uhr | Website jetzt bis 147/26 (Einsätze 141 bis 147 vom 19.10. bis 07.11. nachgetragen, alle im Bestand); Heimat-Info bis 147/26 |
| Heimat-Info | Gemeindeseite 09:19 Uhr; Veranstaltungskalender und zehn Detailseiten 09:26 Uhr | keine neuen Beiträge seit 15.11. 12:24 Uhr; Kalender siehe Abschnitt 6 |
| Kreis Düren | Presse-Index 09:19 Uhr | jüngste Mitteilungen 11./12.11., Titel ohne Merzenich-Bezug (Abschnitt 3) |
| Amtsblatt | Index (Actions-Rohdaten vom 15.11. 20:30 Uhr), PDF Ausgabe 12 direkt 09:20 Uhr | Ausgabe 12 (angekündigt für 13.11.) noch nicht im Index, PDF HTTP 404; Indexseite direkt HTTP 428 (Bot-Prüfung, nicht umgangen) |
| Ratsinformationssystem | `termine.ics` 09:19 Uhr; Vorlagenliste und Sitzung Bauausschuss 09:29 Uhr | ICS unverändert; jüngste Vorlage 151/2026; keine Ergebnisse Bauausschuss |
| Sport | FUSSBALL.DE (vier Spielberichte, SC-Mannschaftsseite) 09:20 bis 09:21 Uhr; FuPa A und B2 (Tabelle, Spieltag) 09:20 Uhr | siehe Abschnitt 1 und 5; FUSSBALL.DE meldete „technische Probleme“, die Berichtstexte waren trotzdem vollständig im Seitenquelltext |

## 5. Sport

Pflichtprüfung (sonst dienstags) wegen der vorliegenden Wochenendergebnisse am Montag. Kreisliga A nach dem 12. Spieltag: 1. SC Jülich/Hoengen 34, 2. Welldorf-Güsten 29, 3. Oberzier 28, 4. SC Merzenich 27 (48:18), 5. FC Golzheim 27 (30:13), 6. SW Düren 25. Kreisliga B2 (FuPa): 1. Morschenich 27, 2. Inden/Altdorf 25, …, 13. Girbelsrath 3, 14. 1. FC Düren II 3. Spielstand-Datei aktualisiert (Abschnitt 1). Nächste Spiele: Girbelsrath Fr. 20.11. 19.30 Uhr in Arnoldsweiler; SC und Golzheim So. 29.11.; Morschenich So. 06.12.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender um 09:26 Uhr gelesen (10 Einträge): Gegenüber 15.11. nachmittags ist der Gottesdienst vom 15.11. entfallen, der Frühstückstreff 02.12. steht wieder auf der ersten Seite; alle zehn Detailseiten unverändert. Ratsinfo-`termine.ics` unverändert. Alle 21 Termine vom 17. bis 29.11. an ihrer Quelle nachgelesen:

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Pluriversum 17.11., Seniorentreffs (Frühstück, Skat) 18. und 25.11., Senioren-Nachmittag 18.11., Gottesdienst 22.11., Adventsliedersingen 29.11. | je Slug | bestätigt | Heimat-Info Detailseiten | 2026-11-16 |
| Pluriversum-Workshop 18.11., 11 bis 13 Uhr | pluriversum-workshop-engagement-gesamtschule-merzenich-2026-11-18 | bestätigt | Gemeindebeitrag in Heimat-Info | 2026-11-16 |
| Mental Load 18.11., 18 bis 19.30 Uhr; Seniorenweihnacht 24.11., 15 Uhr | je Slug | bestätigt | Kreis Düren, Pressemitteilungen | 2026-11-16 |
| Sprechstunde Betreuungsbehörde 19.11., 10 bis 12 Uhr, Fraktionsraum B | sprechstunde-betreuungsbehoerde-rathaus-2026-11-19 | bestätigt | Amtsblatt 08, S. 26 | 2026-11-16 |
| HFA 19.11., Bürgewald-Ausschuss 26.11. | je Slug | bestätigt | Ratsinfo `termine.ics` | 2026-11-16 |
| Modelleisenbahn 21.11., Adventmarkt 28.11. | je Slug | bestätigt | Amtsblatt 11; Adventmarkt zusätzlich Heimat-Info-Beitrag des Heimatvereins | 2026-11-16 |
| Tannenbaumschmücken und Adventfeier 27.11., Weinbergschnecken 28.11. 15 Uhr, Golzheimer Adventskalender 28.11. | je Slug | bestätigt | 16. Kulturkalender | 2026-11-16 |
| Adventsfenster am Bürgewaldzentrum 28.11. ab 17 Uhr | adventsfenster-bwz-morschenich-2026-11-28 | bestätigt | Terminplan Schützen Morschenich | 2026-11-16 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `507e8f12` (Meldung, Spielstand), `1673baf3` (Abrufzeit), `4afd926b` (Kalenderpflege), `60af284d` (Torminute), je Rebase-Konflikt nur in `docs/SHA256SUMS.txt` (Stand von main, Kette neu), einmal `cannot lock ref` beim Push, im zweiten Versuch gepusht; Dokumentation folgt. Abgleich 09:25 Uhr `angestossen: true` (wp-cron HTTP 200) und nach der Torminuten-Korrektur 09:30 Uhr erneut `angestossen: true`.

Live-Nachweis 09:28 Uhr: Meldung HTTP 200, in der Nachrichten-Sitemap, in `wordpress-delivery/merzenich-aktuell-import.xml` auf GitHub (2 Treffer), auf der Startseite in „Neu in Merzenich“ (2 Verweise). Bildbeschreibung im Malwettbewerb-Artikel live weiter „Polizeiwache in Nordrhein-Westfalen“ (ChatGPT 16.11. morgens): Der Abgleich zieht Alt-Texte bestehender Poolanhänge nicht nach ([Dossier 14.11. nachmittags](2026-11-14-claude-nachmittags.md), Abschnitt 7); Entscheidung Luis Kummer offen.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| fussball-merzenich-0-1-frenz-morschenich-4-3-inden-altdorf-golzheim-2-2-sw-dueren-2026-11-15 | sport.spielbericht | fussball | Borken, Burlo, Sportplatz -- 2014 -- 2288.jpg | CC BY-SA 4.0 | am 16.11. selbst angesehen: Luftaufnahme eines Sportplatzes mit Aschenplatz und Tennisplätzen am Ortsrand, keine erkennbaren Personen, keine Kennzeichen; zeigt einen Fußballplatz, wie er in der Kreisliga üblich ist; als Symbolbild gekennzeichnet (Bildunterschrift „Symbolbild · Fußballplatz. Kein Foto vom Ereignis.“) |

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Fußball-Wochenende 14./15.11. | 318 | 10 | 4 Einträge, alle mit offizieller Quelle (FUSSBALL.DE), zwei zusätzlich mit eigenem Archiv | 0 Wörter (FuPa-Seiten für das Prüfwerkzeug nicht abrufbar, nur Tabellenwerte) |

## 9. Offene Punkte für den nächsten Lauf

- Mindesttakt 16.11.: zweite Meldung im Nachmittagslauf.
- HFA 19.11.: Empfehlungen nach der Sitzung als Folgemeldungen.
- Bauausschuss 12.11.: Ergebnisse abwarten.
- Amtsblatt 12: Erscheinen prüfen (laut Abgabeplan 13.11.).
- Sport: Girbelsrath Fr. 20.11.; Spieltag Kreisliga A erst 29.11.
- Werkzeugpunkte (Alt-Text bestehender Poolanhänge im Abgleich, Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
