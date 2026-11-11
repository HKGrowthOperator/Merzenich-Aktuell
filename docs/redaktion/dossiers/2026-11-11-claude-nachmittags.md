# Merzenich Aktuell – Nachmittagslauf (Claude), 11. November 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` (Version 3.9). Routine „14:43“ in der festen Redaktionssitzung, Bearbeitung 14:44 bis 14:55 Uhr (Europe/Berlin). Vergleichsbasis: [Claude 11.11. morgens](2026-11-11-claude-morgens.md), seither neu [ChatGPT 11.11. mittags](2026-11-11-mittags.md). main vor dem Lauf: `59151a61`. Eil-Prüfungen 10:41 und 12:41 Uhr ohne Fund.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Standardartikel Brandschutzbedarfsplan (956 Wörter im Fließtext, Rathaus, `relevanz` 2) |
| Korrigierte Bestandsmeldungen | 0 | ChatGPT 11.11. mittags ohne Korrekturauftrag; Abgabenartikel dort gegen die Originale geprüft, keine Abweichung |
| Neue / geänderte Termine | 0 / 0 | Krätzchensitzung 23.01.2027 an neuer Gemeindemitteilung bestätigt (Stand aktualisiert) |
| Drei-Quellen-Quote, Prüfprotokoll vollständig | 1/1 | 7 Quellen (zwei Vorlagen, Planentwurf, Kostenliste, Tagesordnung, zwei eigene Meldungen), 6 Aussagen |
| Bilder gefunden / geprüft / integriert | 0 / 1 / 1 | Feuerwehrhaus (Pool `feuerwehr`, Symbolbild) |
| Kette / QA / redaktion-lauf | je Schritt zweimal, `--check` unverändert; `qa/pruefung.mjs` 0 Fehler; `qa/redaktion-lauf.mjs` 0 Fehler; `qa/eigenformulierung.mjs` 0 Fehler, 0 Hinweise | |
| Mindesttakt 11.11. | 2 Meldungen | Abgaben 09:21, Brandschutzbedarfsplan 14:48 Uhr |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-BRANDSCHUTZPLAN-2026-FORTSCHREIBUNG | „Brandschutzplan für Merzenich: Feuerwehr verfehlt Zeitziel, Golzheim soll neues Feuerwehrhaus bekommen“ (Standard): gestaffeltes Schutzziel (Merzenich 9 Kräfte in 10 Minuten, übrige Ortsteile 6), Auswertung der Leitstellendaten 07/2019 bis 06/2024 (71,4 % und 82,9 % beim ersten Ziel statt 90 %), Bürgewald 12 Minuten in der Sonderkategorie „Brand 0“ (laut Plan rein planerisch, Buir und Ellen weiter mitalarmiert; Widerspruch zur Brand-1-Aufzählung offen benannt), Mitglieder 119 und Sollstärken je Löschgruppe, Kostenliste der Verwaltung (rund 4,4 Mio. € inkl. ELW, Feuerwehrhaus Golzheim 2,5 Mio. €) | `inhalte/meldungen/2026-11-11-recherche-claude-nachmittags.json` → `/rathaus/brandschutzbedarfsplan-merzenich-2026-schutzziel-feuerwehrhaus-golzheim-buergewald-hilfsfrist-kosten/` | Vorlage 40/2026 1. Ergänzung (09.11.) und Ursprungsvorlage (17.09.), Planentwurf Stand 10.11.2026 ohne Markierungen (104 Seiten; Kapitel 3.9, 3.10, 4.1.1 bis 4.3, 5.2.2 gelesen), Anlage „Maßnahmen, Haushaltsjahre und voraussichtliche Kosten“, alle über die Vorgangsseite im Ratsinformationssystem 14:46 bis 14:48 Uhr; Commit `25c082de` |

Quellenausnahmen: ChatGPT führte die Karte als T2 (fachliche Einordnung und unabhängige Bestätigung fehlen). Umgesetzt, weil Vorlage, Gutachten und Kostenliste offizielle Quellen sind (SCHREIBREGELN Abschnitt 0) und der Text sie als Entwurf und Schätzung kennzeichnet. Den Hinweis aus dem ChatGPT-Dossier (keine pauschale längere Hilfsfrist für Bürgewald) beachtet: Der Text nennt die zwölf Minuten nur für die Sonderkategorie „Brand 0“ und den Widerspruch zur Brand-1-Aufzählung. Keine Namen von Sachbearbeitern. `relevanz` 2 (kein Beschluss).

Top-Meldung: keine neue Stufe 1.

## 2. Sofort sichtbar

Neue Meldung ohne Freigabe. Live-Nachweis in Abschnitt 7.

## 3. Bewusst nicht umgesetzt

| Thema | Grund |
|---|---|
| MA-UNTERKUNFTSGEBUEHREN-2027 | Vorlage 145/2026 gelesen (195,72 €/290,66 € Benutzungsgebühr je Person und Monat, rund 230 Geflüchtete); bisherige Sätze nicht in der Vorlage, Kalkulationsanlage noch nicht ausgewertet. Nächster Lauf vor dem 19.11.; die Vorlage nennt eine Hausnummer, die nicht übernommen wird |
| MA-ALTE-KIRCHE-ENTGELTE-2027, MA-DIGITAL-IKZ-2026 | weiter offen, vor dem 19.11. einplanen |
| Stellenanzeige der Gemeinde (Heimat-Info 13:49 Uhr) und Ticketaufruf Krätzchensitzung (11:50 Uhr) | Stellenanzeige ohne Nachrichtenwert; Krätzchensitzung als Termin vorhanden und bestätigt |

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| ChatGPT | 11.11. mittags, zuerst KORREKTUREN | keine Korrekturen; Abgabenartikel ohne Abweichung; BdSt-PDF dort HTTP 403 (im Morgenlauf hier gelesen) |
| Ratsinformationssystem | HFA-Tagesordnung, Vorgangsseite 40/2026, Vorlagen 40 und 145/2026 mit Anlagen, 14:45 bis 14:48 Uhr; `termine.ics` 14:50 Uhr | siehe Abschnitt 1; Sitzungsliste unverändert |
| Polizei Düren | Dienststellenliste nr/8, 10:41, 12:41 und 14:45 Uhr | neu 11.11.: Niederzier (Pkw-Brand Ellen, Spendenbetrug), Düren (zwei Pedelec-Unfälle); im Volltext kein Merzenich-Bezug |
| Feuerwehr | feuerwehr-merzenich.de/einsaetze und Heimat-Info-Profil, 14:45 Uhr | weiter bis 140/26 bzw. 147/26 |
| Heimat-Info | Gemeindeseite 14:45 Uhr; Veranstaltungskalender und zehn Detailseiten 14:50 Uhr | Gemeinde: Ticketaufruf Krätzchensitzung, Stellenanzeige; Kalender: nur Aktualisierungsstempel bei Pluriversum und zwei Seniorentreffs, Inhalte unverändert |

## 5. Sport

Mittwoch, keine Pflichtprüfung.

## 6. Kalenderpflege

Arbeitsliste `node qa/redaktion-lauf.mjs --kalender`. Heimat-Info-Veranstaltungskalender der Gemeinde um 14:50 Uhr gelesen (10 Einträge, dieselben IDs wie morgens; Detailseiten verglichen: sieben unverändert, drei nur mit neuem Aktualisierungsstempel 10:03 bzw. 10:15 Uhr, Datum, Zeit und Ort gleich) und Ratsinfo-`termine.ics` (inhaltlich gleich). Alle Termine bis 25.11. im Morgenlauf an ihrer Quelle bestätigt ([Dossier morgens](2026-11-11-claude-morgens.md), Abschnitt 6).

| Termin | Slug | Aktion | Quelle | Stand |
|---|---|---|---|---|
| Krätzchensitzung, 23.01.2027 | kraetzchensitzung-weinberghalle-2027-01-23 | bestätigt (14 bis 18.30 Uhr, Weinberghalle, Karten 15 Euro) | Heimat-Info c256ce15 (Gemeinde, 11.11. 11:50 Uhr) | 2026-11-11 |
| alle Heimat-Info- und Ratsinfo-Termine 11.11.–03.12. | siehe Dossier morgens | bestätigt, unverändert | Heimat-Info, Ratsinfo `termine.ics` | 2026-11-11 |

**Neu:** keiner. **Geändert:** keiner. **Abgesagt:** keiner. Offene Hinweise „nachpruefen“: 0.

## 7. QA, Commit, Abgleich

Commit `25c082de` (Meldung), Dokumentation folgt. Abgleich angestoßen 14:50 Uhr (`angestossen: true`), wp-cron HTTP 200.

Live-Nachweis 14:51 Uhr: Meldung HTTP 200, in der Nachrichten-Sitemap, in `wordpress-delivery/merzenich-aktuell-import.xml` auf GitHub (2 Treffer) und oben in „Neu in Merzenich“ (vor dem Abgabenartikel).

## 8. Bildprüfung

| Meldung | Bildklasse | Motiv | Foto (Commons) | Lizenz | passt, weil |
|---|---|---|---|---|---|
| brandschutzbedarfsplan-merzenich-2026-schutzziel-feuerwehrhaus-golzheim-buergewald-hilfsfrist-kosten | rathaus.feuerwehr | feuerwehrhaus | Dülmen, Feuerwehr -- 2013 -- 0155.jpg | CC BY-SA 4.0 | selbst angesehen: Feuerwache mit Schriftzug „Feuerwehr“ und Fahrzeughallen; keine Personen, keine lesbaren Kennzeichen; Symbolbild für Feuerwehrhäuser und Ausstattung, kein Merzenicher Gebäude |

Neu importierte Fotos: keine.

## 8a. Prüfung und Eigenformulierung

| Meldung | Wörter | Quellen | Aussagen im Prüfprotokoll (offiziell / doppelt / Selbstauskunft) | Eigenformulierung (max. gleiche Wortfolge) |
|---|---:|---:|---|---|
| Brandschutzbedarfsplan | 956 | 7 | 6 Einträge, alle mit offizieller Quelle (Ratsinformationssystem); einer zusätzlich mit eigenem Archiv | 0 Wörter |

## 9. Offene Punkte für den nächsten Lauf

- HFA 19.11.: Unterkunftsgebühren (Kalkulationsanlage, bisherige Sätze), Trauentgelte Alte Kirche, Digitalisierung, Übernachtungssteuer; nach der Sitzung Empfehlungen zu Abgaben und Brandschutzplan nachtragen.
- Bauausschuss 12.11. (morgen): Folgemeldungen am 13.11.
- Amtsblatt 12, Martinszug Merzenich 12.11. und Girbelsrath 13.11.
- Wertung Girbelsrath – Morschenich; Papiersammlung ab 2027; Jugendgremium; Orte der KG-Jonge-vom-Berg-Termine (Vereinsseite „Site Unavailable“); Pluriversum 18 oder 18.15 Uhr.
- Werkzeugpunkte (Feuerwehr-Automatik „Hund“, Termin-Automatik gleichzeitige Termine, Heimat-Info-Kalender Seite 2) warten auf Entscheidung.
