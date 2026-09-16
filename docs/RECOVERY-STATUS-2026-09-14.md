# Recovery-Status – 14.09.2026

Dokumentierter Stand des zuletzt erzeugten Recovery-Builds.

## Geändert

- weißer, bündiger Sticky-Header ohne Floating-Gap
- 1px Bottom-Border
- kleinere Wortmarke
- mittlere Suche
- Wetter/Uhrzeit/Datum rechts
- Ressortzeile plus „Mehr“
- Desktop-Hierarchie: 260px Service / flexible News-Mitte / 280px rechts
- Mobile-Reihenfolge: News → Service → Werbung
- dynamische Hero-Auswahl: automatisch nur Meldungen der letzten 7 Tage; alte Story nur über bewusste redaktionelle Fixierung
- rot umrandeter „Mehr lesen“-Button und Kommentarzahl
- Event-CPT mit Start/Ende und automatischem Entfernen abgelaufener Termine aus „kommend“
- Wetter zentral für Header und Service: Serverabruf, 10–30-Minuten-Cache, letzter gültiger Datenstand bei Fehler, kein 0 °C, `undefined` oder erzwungenes „nicht verfügbar“
- `ma_property`, `ma_job`, `ma_obituary`, `ma_family_notice`, Vereine und Betriebe
- globale und slotbezogene Werbeschalter, Laufzeiten und Prioritäten
- native WordPress-Kommentare samt Formular und Moderationshinweis
- News-Radar mit `Neu → Prüfen → Übernommen / Ignoriert / Duplikat`, ohne Auto-Publish
- serverseitige Formulare mit Nonce, Honeypot, Validierung und begrenzten Uploads
- KI-/Human-Review-/Quellenfelder und Veröffentlichungs-Gate
- Sportdarstellung mit eigenem Status für noch nicht bestätigte Ergebnisse
- Related Content im Artikel
- keine aktuellen Wetterwerte, News oder Sportergebnisse fest im Theme/Core einbetoniert

## Content Refresh – dokumentierter Stand 14.09.2026

- aktuellste geprüfte offizielle Merzenich-Blaulichtmeldung: Einbruch im Arnoldsweilerweg vom 11.09.; Tat am 10.09.; Polizei Düren bittet um Hinweise
- dafür eigener redaktioneller Entwurf statt kopierter Polizeimeldung
- erneut geprüft: Standkartenvergabe am 16.09., „Merzenich räumt auf“ am 20.09., Naturdetektive am 21.09., Oldieabend am 02.10.
- Fußball: für 13.09. kein Ergebnis erfunden; SC 1919 Merzenich – BC Oberzier als `pending_match`; nächstes gelistetes Spiel 18.09. gegen SC Jülich
- lokale Mediathek: hochauflösende Commons-Motive geprüft, u. a. Rathaus-HDR (3070×2004, CC BY-SA 3.0, Attribution Charlie1965nrw/Karl-Heinz Meurer) sowie Alte Pfarrkirche und mehrere Lindenplatz-/Wasserturm-Motive; keine blinde Artikelzuweisung

## QA / Release Gates – dokumentierter Stand

| Prüfung | Status |
|---|---|
| Weißer Header, top:0, keine Floating Island | PASS lokal |
| Suche + dynamische Wetterlogik + Zeit/Datum | PASS lokal |
| 3-Spalten-Desktop-Hierarchie | PASS lokal |
| Mobile News zuerst | PASS lokal |
| 1440 px ohne horizontales Scrollen | PASS lokal |
| 390 px ohne horizontales Scrollen | PASS lokal |
| Mobile-Menü funktional getestet | PASS lokal |
| PHP-Syntax aller Theme-/Core-Dateien | PASS lokal |
| ZIP-Integrität und jeweils eine installierbare Root | PASS lokal |
| WXR-XML syntaktisch gültig | PASS lokal |
| kein festes aktuelles Wetter im Theme/Core | PASS lokal |
| kein festes SC-Ergebnis im Theme/Core | PASS lokal |
| echter Live-Before-Screenshot der veröffentlichten ChatGPT-Site | FAIL / nicht verifiziert |
| Git-Tag auf bestehendem Projekt-Repository | FAIL zum damaligen Zeitpunkt |
| Recovery-Build auf echter WordPress-Staging-Installation | FAIL |
| Before/After-Vergleich gegen live veröffentlichte ChatGPT-Site | FAIL |
| Push auf ChatGPT-Site | FAIL / bewusst blockiert |

## Wichtig

Die damaligen PASS-Werte waren **lokale Build-Prüfungen**, keine Bestätigung des öffentlichen Live-Stands. Ab jetzt gilt GitHub als technischer Source of Truth; Live-PASS und Local-PASS müssen getrennt ausgewiesen werden.
