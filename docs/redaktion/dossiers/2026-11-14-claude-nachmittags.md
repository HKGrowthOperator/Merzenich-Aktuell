# Merzenich Aktuell – Nachmittagslauf (Claude), 14. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung 14:44 bis 15:10 Uhr (Europe/Berlin; Live-Nachweis wartete auf den Abgleich um 15:05 Uhr). Vergleichsbasis: [Claude 14.11. morgens](2026-11-14-claude-morgens.md), seither neu [ChatGPT 14.11. mittags](2026-11-14-mittags.md). main vor dem Lauf: `38453ed1`. Eil-Prüfungen 10:46 und 12:42 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung Shared Service Center und Digitalisierungscheck (242 Wörter im Fließtext, Rathaus, `relevanz` 3) |
| Korrigierte Bestandsmeldungen | 1 (plus alle Seiten mit demselben Foto) | Bildunterschrift/Alt-Text Malwettbewerb: „Polizeimotorrad“ statt „Polizeiwache in Nordrhein-Westfalen“ |
| Neue / geänderte Termine | 1 / 0 | Wortgottesdienst Morschenich 14.11., 17.30 Uhr |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | Kurzmeldung, zwei Vorlagen und Tagesordnung (offiziell), 3 Aussagen |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Rathaus Merzenich |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | |
| Mindesttakt 14.11. | 2 Meldungen | Malwettbewerb 09:16, Shared Service Center 14:47 Uhr |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| Korrektur Malwettbewerb (ChatGPT 14.11. mittags) | Bildunterschrift und Alt-Text nannten „Polizeiwache in Nordrhein-Westfalen“, das Foto zeigt ein Polizeimotorrad. Ursache: `deploy/symbolbilder.mjs` setzte für alle Fotos des Polizeipools pauschal die Poolbeschreibung. Behoben über `MOTIV_ALT` (Polizeimotorrad, Polizeihubschrauber, Streifenwagen); damit sind alle Seiten mit diesem Foto korrigiert (84 Stellen) | `deploy/symbolbilder.mjs` | Foto am 14.11. 09:18 Uhr selbst angesehen; Commons-Titel „Münster, Lambertikirchplatz, Motorrad -- 2019 -- 3594.jpg“; Commit `d4e035d8` |
| MA-DIGITAL-IKZ-2026 | „Merzenich soll Aufgaben mit anderen Kommunen im Kreis Düren bündeln: zuerst die Denkmalbehörde“ (Kurzmeldung): Shared Service Center, grundsätzlich 13 Kommunen, Förderantrag über Jülich, mindestens fünf Jahre, erst Untere Denkmalbehörde, dann Gebührenkalkulation; Digitalisierungscheck mit der kdvz (bereits beauftragte Schritte, keine Mehrkosten laut Verwaltung) | `inhalte/meldungen/2026-11-14-recherche-claude-nachmittags.json` → `/rathaus/merzenich-shared-service-center-kreis-dueren-denkmalbehoerde-digitalisierungscheck-2026/` | Vorlagen 146/2026 und 134/2026, gelesen 14:47 Uhr; Konzept, Förderantrag und Anlagen nicht ausgewertet; Commit `afc7a455` |
| Kalender | Wortgottesdienst mit Kommunionausteilung, St.-Lambertus-Kapelle Morschenich, 14.11., 17.30 Uhr (ohne Namen von Organistin) | `inhalte/termine/2026-11-14-recherche-claude-nachmittags.json` | Heimat-Info-Beitrag der Kapelle vom 14.11. 09:13 Uhr; Commit `82bb2474` |

Quellenausnahmen: ChatGPT führte MA-DIGITAL-IKZ-2026 als T2 (Umsetzung, Förderung und Verbindlichkeit unabhängig nicht bestätigt). Umgesetzt als Kurzmeldung, weil die Vorlagen offizielle Quellen sind; durchgehend als Vorschlag gekennzeichnet, ob die Förderung kommt, ist im Text offen.

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung, Bildkorrektur und Termin ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| Bahn Düren–Horrem (T2) | laut ChatGPT mittags nicht mehr im Betriebsfeed; kein Anlass |
| SC-Tabelle (T2) | Sportprüfung am Dienstag |
| Bauausschuss 12.11. | weiter keine Ergebnisse im Ratsinformationssystem |
| Patronatserklärung Kita gGmbH (136/2026), Neuland Hambach (151/2026) | in der Wochenvorschau genannt; eigene Meldungen nach der Sitzung, wenn Empfehlungen vorliegen |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 14.11. mittags, zuerst KORREKTUREN | eine Korrektur (Bildbeschreibung), umgesetzt |
| Ratsinformationssystem | Vorlagen 146/2026 und 134/2026, 14:47 Uhr; `termine.ics` 14:50 Uhr | siehe Abschnitt 1; Sitzungsliste unverändert |
| Heimat-Info | Gemeindeseite 14:48 Uhr; Veranstaltungskalender 14:50 Uhr | Gottesdienst Morschenich (Abschnitt 1); Kalender-IDs unverändert |
| Polizei Düren | Dienststellenliste nr/8, 12:42 Uhr | seit 08:09 Uhr nichts Neues; Fahndung Düren ohne Merzenich-Bezug |
| Feuerwehr | Website und Heimat-Info-Profil, 12:42 Uhr | weiter bis 140/26 bzw. 147/26 |

## 5. Sport

Samstag; Spielstand unverändert, Prüfung am Dienstag.

## 6. Kalenderpflege

Heimat-Info-Veranstaltungskalender um 14:50 Uhr gelesen (dieselben 10 Einträge wie morgens), Ratsinfo-`termine.ics` unverändert. Termine bis 27.11. im Morgenlauf und am 12./13.11. an ihrer Quelle bestätigt ([Dossier morgens](2026-11-14-claude-morgens.md), Abschnitt 6).

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Wortgottesdienst Morschenich, 14.11., 17.30 Uhr | wortgottesdienst-st-lambertus-kapelle-morschenich-2026-11-14 | **neu** | Heimat-Info 27e440ee | 2026-11-14 |

**Abgesagt:** keiner.

## 7. QA, Commit, Abgleich

Commits: `d4e035d8` (Bildkorrektur), `afc7a455` (Meldung), `82bb2474` (Termin), je Rebase-Konflikt nur in `docs/SHA256SUMS.txt` (Stand von main, Kette neu), Dokumentation folgt. Abgleich 14:46 Uhr `angestossen: true`, danach `angestossen: false` (Lauf schon angestoßen, nächster planmäßig 15:05 Uhr).

Live-Nachweis: Import-XML auf GitHub enthält Meldung und Termin (3 Treffer) seit 14:50 Uhr. Meldung und Bildkorrektur um 14:50 Uhr auf der Website noch nicht sichtbar (HTTP 404 bzw. alte Bildunterschrift); Nachweis nach dem Abgleich siehe Nachtrag.

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| merzenich-shared-service-center-kreis-dueren-denkmalbehoerde-digitalisierungscheck-2026 | rathaus.rat | rathaus-merzenich | Merzenich Rathaus HDR.jpg | CC BY-SA 3.0 | am 12.11. selbst angesehen: Rathaus Merzenich mit Schriftzug, keine Personen, keine lesbaren Kennzeichen; Sitz der Verwaltung, um deren Organisation es geht |

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Shared Service Center | 242 | 4 | 3 Einträge, alle mit offizieller Quelle; einer zusätzlich mit eigenem Archiv | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- Bauausschuss 12.11.: Ergebnisse abwarten.
- Sport Dienstag 17.11.: Ergebnisse vom 15.11., Tabelle.
- HFA 19.11.: Empfehlungen nach der Sitzung.
- Zeitkonflikte Weckmannschießen (15.11., morgen), Senioren-Nachmittag, Krätzchensitzung.
- Werkzeugpunkte (Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
