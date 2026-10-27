# Merzenich Aktuell – Morgenlauf (Claude), 27. Oktober 2026

Umsetzungsdossier nach `docs/redaktion/CLAUDE-LAUF.md`. Routinestart 08:31 Uhr, Bearbeitung ab etwa 08:45 Uhr (Europe/Berlin, MEZ). Vergleichsbasis: [Claude-Nachmittagslauf 26.10.](2026-10-26-claude-nachmittags.md), [Claude-Nachlauf 26.10.](2026-10-26-claude-nachlauf.md) (Parallelsitzung), [ChatGPT-Abenddossier 26.10.](2026-10-26-abends.md), [ChatGPT-Morgendossier 27.10.](2026-10-27-morgens.md). main vor dem Lauf: `044befe`.

## 0. Kurzfassung in Zahlen

| Größe | Zahl | Anmerkung |
|---|---:|---|
| Neue Meldungen | 0 | keine neue belegte Merzenicher Nachricht seit dem Vortag |
| Korrigierte Bestandsmeldungen | 1 | ELW-Beschluss 116/2026 |
| Neue Termine | 0 | |
| Termine auf Entwurf umgestellt | 11 | alle Claude-Termine vom 25./26.10. (siehe 2.) |
| Umkreis-Einträge | 1 | VHS Rur-Eifel offline |
| Sportstand | unverändert | SC III Endstand weiter nicht bestätigt |
| Bilder | 0 | |
| Kette / QA | zweimal, `--check` unverändert, 0 Fehler, 32 Hinweise | |

## 1. Umgesetzt

| ID | Ergebnis | Pfad | Prüfumfang |
|---|---|---|---|
| MA-ELW1-116-2026 | Korrektur nach den QA-Karten der ChatGPT-Dossiers vom 26.10. abends und 27.10. morgens: Satz „Vergeben ist der Auftrag noch nicht“ ersetzt durch „Ob ein Auftrag inzwischen erteilt ist, geht aus den veröffentlichten Unterlagen nicht hervor“; der Absatz zum EU-Schwellenwert nennt jetzt nur Belegtes (216.000 Euro netto für Lieferaufträge subzentraler Auftraggeber 2026/2027, Delegierte Verordnung (EU) 2025/2152) und sagt ausdrücklich, dass die Vorlage keinen Grund für die Höhe des Limits nennt; „in der vergangenen Woche“ ersetzt durch das Datum des Bestandsartikels (16.10.). Neue Quelle: EU-Kommission, Schwellenwerte. | `inhalte/meldungen/2026-10-26-recherche-claude-nachmittags.json` → `/rathaus/einsatzleitwagen-feuerwehr-merzenich-ratsbeschluss-257000-euro-2026/` | Beschlussvorlage und Beschlusstext 116/2026 am 27.10. erneut als PDF gelesen; EU-Schwellenwertseite gelesen. Die Vorlage erwähnt den Schwellenwert nicht. |
| MA-VHS-OFFLINE-26102026 | Umkreis-Eintrag Düren/Bildung | `inhalte/umkreis/2026-10-27.json` | Pressemitteilung der Stadt Düren vom 26.10., 11:24 Uhr, vollständig gelesen. Ob Merzenich zum Einzugsgebiet der VHS Rur-Eifel gehört, ist nicht an einer Quelle belegt; deshalb kein Merzenicher Artikel. |

## 2. Termine ohne Freigabe online (Korrektur am System)

`deploy/wordpress-import.mjs` liefert Termine ohne `"entwurf": true` mit Status `publish`; das Plugin übernimmt diesen Status beim Anlegen (`ma_abgleich_status`). Die 11 Claude-Termine vom 25./26.10. waren dadurch ohne redaktionelle Freigabe auf `/termine/` sichtbar (am 27.10. geprüft). Die Termindaten waren an den Primärquellen geprüft. Seit Commit `20d0b3f` tragen alle Claude-Termine `"entwurf": true`; das wirkt für künftige Importe, bestehende Termine behalten ihren Status. `CLAUDE-LAUF.md` nennt jetzt alle Pfade, die ohne Freigabe sichtbar werden (Termine, Spielstand, Startseiten-Setzungen, Umkreis, Markt). Ob die 11 Termine stehen bleiben, entscheidet die Redaktion im Backend.

## 3. Bewusst nicht umgesetzt

- Martinsbons Morschenich 27.10., 18 bis 19 Uhr: steht bereits in der Meldung vom 16.10. (`inhalte/meldungen/2026-10-16-amtsblatt-11.json`).
- S-19-Ausfall in der Nacht 27./28.10.: steht im Verkehrsartikel vom 25./26.10.; Ersatzbus-Abfahrten weiter nicht belegt.
- Heimat-Info-Beiträge der Gemeinde zu Hochzeit und Ehejubiläum: Privatpersonen, kein Artikel.
- Wortgottesdienst Morschenich 24.10.: vorbei.
- Hintergründe Sophienhöhe (Besucherzentrum, Naturerlebnis, Ökosystemverbund, Hambach Loop) und Radstation Düren: im ChatGPT-Dossier als T2 oder ohne unabhängige Zweitquelle; nicht in diesem Lauf.
- Bärenstark-Kita-Stelle (Krankheitsvertretung): Frist im PDF abgelaufen, keine Verlängerung belegt; nicht zu verwechseln mit dem Übungsleiter-Artikel MA-1025-08.

## 4. Quellen dieses Laufs

| Quelle | Weg | Ergebnis |
|---|---|---|
| Rohdaten `imports/quellen/` (Abruf 27.10. früh) | Vergleich mit Stand `f102893` | Heimat-Info, Feuerwehr, Gemeinde, Schulen ohne neue Einträge; Polizei 22 zusätzliche, aber ältere Einträge aus der neuen Gesamtliste |
| Polizei Düren RSS | presseportal Dienststelle 8 | jüngste Meldung 26.10., 11:00 Uhr; keine mit Merzenich |
| Kreis Düren Presse | Index | jüngste Mitteilung 26.10. (KI-Roadshow, bereits umgesetzt) |
| Heimat-Info Merzenich | Startseite live | 8 Beiträge, alle in den Rohdaten; vier ohne Bestand (siehe 3.) |
| Ratsinformationssystem | `termine.ics` | Jugendgremium 04.11., RPA 05.11., Bauausschuss 12.11., HFA 19.11., Ausschuss Revitalisierung Bürgewald 26.11., Gemeinderat 03.12.; keine neuen Tagesordnungen |
| Feuerwehr Merzenich | Einsatzliste | Website bis 140/26; 142/143 nur über Heimat-Info (bereits umgesetzt) |
| Stadt Düren | Pressemitteilung VHS | gelesen |
| Ratsinfo PDFs 116/2026, EU-Schwellenwerte | Direktabruf | gelesen (ELW-Korrektur) |

## 5. Sport

Keine Änderung an `chatgpt-site/api/sport-current.json`. SC Merzenich III in Gereonsweiler: Endstand weiter nicht bestätigt.

## 6. QA, Kette, Push, Abgleich

Commits `20d0b3f` (Termine-Entwurf, Laufdefinition) und `636d04c` (ELW, Umkreis). Ein CI-Commit (`464d46d`) kreuzte den Push; Konflikt in `docs/SHA256SUMS.txt` durch erneuten Kettenlauf gelöst, kein Force. Abgleich nach dem Dokumentations-Commit angestoßen.

## 7. Offene Punkte

1. Redaktion: 11 live gestellte Claude-Termine prüfen (stehen lassen oder zurückziehen).
2. SC III Endstand; SEV-Abfahrten Merzenich (nur noch bis 28.10., 5 Uhr relevant).
3. Tagesordnungen Jugendgremium 04.11. und RPA 05.11. sobald im RIS.
4. Amtliche Ernennung der Leitung des Pastoralen Raums.
5. Teil B des Ausbauplans: Zweitquellen für die sieben Ein-Quellen-Meldungen vom 25.10.
