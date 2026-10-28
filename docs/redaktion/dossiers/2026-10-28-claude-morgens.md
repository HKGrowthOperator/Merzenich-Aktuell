# Merzenich Aktuell – Morgenlauf (Claude), 28. Oktober 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md` 2.0. Routinestart 08:31 Uhr, Bearbeitung 08:33 bis etwa 09:15 Uhr (Europe/Berlin). Der Nachmittagslauf vom 27.10. ist ausgefallen (die Sitzung stand im Planungsmodus); sein Stoff ist hier enthalten. Vergleichsbasis: [Claude-Morgenlauf 27.10.](2026-10-27-claude-morgens.md), [ChatGPT-Abenddossier 27.10.](2026-10-27-abends.md), [ChatGPT-Morgendossier 28.10.](2026-10-28-morgens.md). main vor dem Lauf: `c3e2d76`.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 1 | Kurzmeldung (Blaulicht, Girbelsrath) |
| Aktualisierte oder korrigierte Bestandsmeldungen | 0 | keine Korrekturaufträge zu Claude-Artikeln in den neuen Dossiers |
| Neue Termine | 0 | |
| Umkreis-Einträge | 0 | |
| Zwei-Quellen-Quote | 0/1 | eine begründete Quellenausnahme |
| Sportstand | unverändert | keine neuen Endstände; SC III Gereonsweiler weiter offen |
| Bilder gefunden / geprüft / integriert | 0 / 0 / 0 | keine Bildarbeit in diesem Lauf |
| Kette / QA / redaktion-lauf | zweimal, `--check` unverändert, 0 Fehler, 32 Hinweise; redaktion-lauf 0 Fehler, 7 Hinweise | |
| Dauer des Laufs | etwa 45 Minuten | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| GR-L264-27102026 | Kurzmeldung „Alleinunfall auf der L 264 bei Girbelsrath: Autofahrer schwer verletzt, Straße fünf Stunden gesperrt“ | `inhalte/meldungen/2026-10-28-recherche-claude-morgens.json` → `/blaulicht/alleinunfall-l264-girbelsrath-autofahrer-schwer-verletzt-2026-10-27/` | Pressemitteilung der Polizei Düren 6379066 (27.10., 14:48 Uhr) vollständig gelesen; die Umkreis-Automatik hatte sie als „Kernort, für die Redaktion“ gemeldet |

Quellenausnahmen: GR-L264-27102026, einzige Veröffentlichung. Feuerwehr-Einsatzliste (bis 140/26) und Heimat-Info-Kanal der Feuerwehr (bis 143/26) ohne Bericht zu diesem Unfall. Alter und Herkunftsort des Fahrers bewusst weggelassen.

## 2. Sofort sichtbar

Nichts.

## 3. Bewusst nicht umgesetzt

- S-19-Ausfall 26. bis 28.10.: Zeitraum ist abgelaufen; der Verkehrsartikel nennt ihn mit Datum, kein Nachtrag.
- Persönliches Budget: Portal nennt beim Wiederholungsabruf weiter den 2. November (ChatGPT-Morgendossier); Artikel und Termin bleiben.
- Heimat-Info-Beitrag der Gemeinde zu einer Hochzeit: Privatpersonen.
- Dürener Polizeimeldungen vom 27.10. (Dooring, Pkw-Brand, B 399): von der Umkreis-Automatik übersprungen (Befund siehe 7.), im ChatGPT-Abenddossier T2; kein Umkreis-Eintrag von Hand.
- Weitere Karten der ChatGPT-Dossiers (Krätzchen, Jugendgremium, Nahverkehrsplan, Tourismustag, KG-Karten, Archivservice): T2 oder ohne neue Primärquelle.

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| Rohdaten `imports/quellen/` (Abruf 28.10. früh) | Vergleich mit `061bf49` | Heimat-Info: ein neuer Beitrag (Hochzeit); Feuerwehr unverändert bis 140/26 |
| Polizei Düren RSS | Dienststelle 8 | fünf neue Meldungen am 27.10., eine mit Merzenich (6379066) |
| Kreis Düren Presse | Index | keine Mitteilung nach dem 26.10. |
| Heimat-Info Feuerwehr | Beitragsliste live | bis 143/26 |
| Ratsinformationssystem | `termine.ics` | unverändert: 04.11., 05.11., 12.11., 19.11., 26.11., 03.12. |
| Umkreis-Automatik | `node deploy/automatik-umkreis.mjs --trocken` | 0 neu, 1 Kernort an die Redaktion, 5 übersprungen |

## 5. Sport

Keine Änderung an `chatgpt-site/api/sport-current.json`. Laut ChatGPT-Morgendossier gibt es einen neuen Vorbericht zum SC III, der Gereonsweiler-Endstand bleibt offen.

## 6. Termine der nächsten 14 Tage (Veränderungen)

Keine neuen. Bürgersprechstunde Golzheim (30.10. laut Heimat-Info, 13.11. laut Amtsblatt) weiter ungeklärt.

## 7. QA, Commit, Abgleich

Commit `24702c2` (Inhalte). Ein ChatGPT-Commit (`04a67bd`) kreuzte den Push; Konflikt in `docs/redaktion/offen.md` durch erneutes `register-pflege.mjs` gelöst, kein Force. Abgleich nach dem Dokumentationscommit angestoßen.

**Befund Umkreis-Automatik:** `deploy/automatik-umkreis.mjs` überspringt Meldungen, deren Ortsangabe „Düren“ oder „Jülich“ lautet, mit dem Grund „Dienststelle … (nur Polizei Düren)“. Dadurch fehlen Dürener Stadtmeldungen im Umkreis. Nicht im Redaktionslauf geändert (Verbot); Aufgabe für Phase C1.

## 8. Bilder

Keine Bildarbeit in diesem Lauf.

## 9. Offene Punkte für den nächsten Lauf

1. Zweitquellen für Seniorennachmittag und Wildpferdeexkursion (Exkursion ist am 29.10.).
2. Vertiefung Schulumbau Golzheim, Eichel-Sammelaktion, Persönliches Budget.
3. SC III Endstand; Bürgersprechstunde Golzheim klären.
4. Umkreis-Automatik: Dürener Meldungen (Phase C1).
