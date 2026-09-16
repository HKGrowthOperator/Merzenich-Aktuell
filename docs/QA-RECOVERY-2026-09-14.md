# Merzenich Aktuell – Hard Recovery QA

Stand: 14.09.2026 · Recovery-Paket

## Release Gates

| Gate | Status | Nachweis / Bemerkung |
|---|---|---|
| Aktuell live ausgelieferte ChatGPT-Site visuell geöffnet | **FAIL** | Domain in der verfügbaren Fetch-/Browser-Umgebung nicht erreichbar. Kein alter Snapshot als Ersatz benutzt. |
| Before-Screenshots 1440/390 vom Live-Stand | **FAIL** | Bewusst nicht fingiert; `qa/before/NOT-AVAILABLE.txt`. |
| Git-Tag `pre-recovery-2026-09-14` auf bestehendem Quellrepo | **FAIL** | Kein Merzenich-Repository im verbundenen GitHub und kein bestehender Checkout gemountet. |
| Weißer Header, bündig an `top:0`, 1px Bottom Border | **PASS** | Theme CSS und Browser-Render. |
| Logo kleiner, Suche mittig, Wetter/Temperatur/Uhrzeit/Datum rechts | **PASS** | Header-Template; Wetter dynamisch aus Core. |
| Eine Ressortzeile + Mehr-Menü | **PASS** | Header-Template. |
| Homepage 3-Spalten-Hierarchie Desktop | **PASS** | 260px Service / flexible Mitte / 280px rechts. |
| Mobile: News vor Service/Werbung | **PASS** | CSS Flex-Order; Browser-Render 390px. |
| Keine horizontale Scrollbar 1440px | **PASS** | `scrollWidth == clientWidth` im Browser-Check. |
| Keine horizontale Scrollbar 390px | **PASS** | `scrollWidth == clientWidth` im Browser-Check. |
| Events gemeinsame CPT-Quelle, abgelaufene ausgefiltert | **PASS** | `ma_event` + `ma_upcoming_events()` prüft Endzeit. |
| Veranstaltungen links standardmäßig offen | **PASS** | `<details open>`. |
| Wetter zentral für Header + Service | **PASS** | Beide verwenden `ma_get_weather()`. |
| Wetter Cache + Last-Good-Fallback | **PASS** | 10–30 Min. Cache; bei Fehler letzter gültiger Stand, Fehlercache 5 Min.; ohne validen Stand Modul ausblenden. |
| Kein hartcodiertes Wetter | **PASS** | Grep Theme/Core: 0 Treffer für feste aktuelle Temperaturen. |
| Hero standardmäßig max. 7 Tage | **PASS** | Automatische Auswahl fragt nur letzte sieben Tage ab; manuelles Pinning separat. |
| Mehr-lesen-Button rot umrandet | **PASS** | News Card + Hero. |
| Ortskennung Merzenich/Ortsteil | **PASS** | `ma_theme_location_label()`. |
| Kommentare Artikel + Count Cards | **PASS** | Native WordPress-Kommentare, Formular, Moderationshinweis, Count. |
| Immobilien/Stellen/Trauer/Familie CPTs | **PASS** | Registriert; Metafelder, Status, Ablauf, Kontakt, Bilder via Featured Image/Medien. |
| Keine Fake-Marktplatzdaten | **PASS** | Empty State bei 0 aktiven Einträgen. |
| Werbung global + pro Slot schaltbar | **PASS** | Adminseite; aktive Kampagne + Laufzeit + Priorität + Slot nötig. |
| Feed-Ad nicht vor vier Beiträgen | **PASS** | Ausgabe erst nach Beitrag 4. |
| Sport: unbestätigtes 13.09-Ergebnis nicht erfunden | **PASS** | Recovery-Datensatz führt Partie als `pending_match`. |
| Sportdaten im Theme hardcodiert | **PASS** | 0 Treffer; Sportdaten kommen aus WordPress-Option/Redaktion. |
| Quellen-/Human-Review-Gate | **PASS** | Veröffentlichung wird bei fehlenden Pflichtprüfungen auf Entwurf zurückgesetzt. |
| News Radar ohne Auto-Publish | **PASS** | `ma_source_item` + Workflowstatus; keine Publish-Automation. |
| Formulare serverseitig | **PASS** | Nonce, Honeypot, Mindestzeit, Validierung, begrenzte JPG/PNG/PDF-Uploads, `wp_mail`. |
| PHP-Syntax Theme/Core | **PASS** | Alle PHP-Dateien mit PHP 8.4 `php -l` ohne Fehler. |
| WXR-Import syntaktisch gültig | **PASS** | XML-Parser erfolgreich. |
| Theme ZIP installierbare Einzelwurzel | **PASS** | `merzenich-aktuell/` als einzige Root. |
| Core ZIP installierbare Einzelwurzel | **PASS** | `merzenich-aktuell-core/` als einzige Root. |
| After-Screenshots Recovery-Frontend | **PASS** | Home Desktop/Mobile, Artikel, Sport, Events unter `qa/after/`. |
| Before/After-Pixelvergleich gegen LIVE | **FAIL** | Ohne Live-Baseline nicht seriös möglich. |
| WordPress-Staging-Installation dieses neuen Recovery-Builds | **FAIL** | In dieser Runtime ist keine WordPress-Installation vorhanden. PHP/ZIP/XML sind geprüft, aber kein neuer WP-Runtime-Test. |
| ChatGPT-Site Push/Deploy | **FAIL** | Release Gate absichtlich nicht übergangen; keine Sites-Schreibfunktion in dieser Unterhaltung verfügbar. |

## Entscheidung

**NICHT DEPLOYEN.** Die technischen Recovery-Artefakte sind gebaut, aber der vom Auftrag verlangte Live-Baseline-Vergleich sowie ein Staging-Runtime-Test fehlen. Ein „alles erledigt“ wäre deshalb falsch.
