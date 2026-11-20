# Unternehmen auf Merzenich Aktuell: Almera Clean, ASS Elektrotechnik, Merzlicht, AJ Sports, HK Growth Webstudio

Stand 20.11.2026 · Plugin 1.48.8 · Daten: `wordpress/plugin/merzenich-aktuell-core/data/firmen-entwuerfe.json`

Aufträge des Betreibers vom 24.10. und 28.10.2026: Die fünf Unternehmen ersetzen Bäckerei Wirtz, Hasberg Fenster & Rollladentechnik und Porschen Media (Oktober 2026, Testeintrag). Je Unternehmen liegen ein Profil und, wo Angaben vorliegen, ein Werbebeitrag als **Entwurf** in WordPress. Öffentlich wird erst etwas, wenn die Zusage eingetragen ist.

## Was angelegt wird

**WordPress → Merzenich Aktuell → Firmen-Entwürfe → „Fehlende Entwürfe anlegen“**

| Unternehmen | Kanal | Werbebeitrag | Logo | Werbebanner (je 2, Foto + Video) | Anzeigen im Zugang |
|---|---|---|---|---|---|
| Almera Clean | `/unternehmen/almera-clean-merzenich/` | `almera-clean-gebaeudereinigung-merzenich` | von almera-clean.de | je 2 | 5 |
| ASS Elektrotechnik | `/unternehmen/ass-elektrotechnik/` | `ass-elektrotechnik-smart-home-beleuchtung-dueren` | von asselektrotechnik.de | je 2 | 5 |
| Merzlicht | `/unternehmen/merzlicht/` | – (nur Name) | – | je 2 (KI-Bild) | 5 |
| AJ Sports | `/unternehmen/aj-sports/` | `aj-sports-neue-trikotlinie-sc-merzenich` | vom Betreiber | je 2 (KI-Bild) | 5 |
| HK Growth Webstudio | `/unternehmen/hk-growth-webstudio/` | `hk-growth-webstudio-websites-marienheide` | von hk-growthoperator.de | 3 (DealUno, Anfragen, HK Growth OS; seit 20.11.2026) | 5 |

- **Almera Clean und ASS:** Stammdaten, wie sie am 24.10. bzw. 28.10.2026 (ASS) auf den Websites standen (Branche, Adresse, Telefon soweit genannt, E-Mail, Website), Quelle und Stand, Kurztext, Knopf zur Website. Die Logos sind auf den Websites weiß; für die hellen Logo-Flächen der Seite sind sie dunkel eingefärbt und zugeschnitten (`data/logos/`).
- **ASS Elektrotechnik:** Meisterbetrieb mit Sitz in Düren (Am Dürener Weg 53), daher ohne Ortsteil; Sitz „Düren“.
- **Merzlicht und AJ Sports:** Profil ohne belegten Ort und ohne Kontaktangaben (`nur_name`); die Seite erfindet keine. Merzlicht trägt die belegte Branche „Werbung auf der Videowall“ (Bericht des SC 1919 zur Videowall am Bürgewaldzentrum), seit 20.11.2026 mit Logo (von hk-growthoperator.de, vom Betreiber bestätigt), noch ohne Beitrag; im Menü steht „Noch keine Beiträge“.
- **AJ Sports** (Auftrag Betreiber 20.11.2026): Logo vom Betreiber (`data/logos/aj-sports.png`, weißer Rand abgeschnitten, Hintergrund durchsichtig, Form und Farbe wie geliefert; Original in `artifacts/firmen/`), Branche „Trikots und Sportbekleidung“ und ein Beitrag zur neuen Trikotlinie für den SC 1919 Merzenich (Einverständnis des Vereins laut Betreiber). Das Bild dazu ist eine KI-Visualisierung (Wappen und Logo als Vorlage) und im Bild, im Bildnachweis und im Text so gekennzeichnet; Originale in `artifacts/firmen/aj-sports-trikot-ki-*.jpg`. Anzeige 1 zeigt das Trikot (Schlüssel `aj-sports-trikot`, Dateien `aj-sports-1-v3*`, Knopf „Zum Beitrag“); die frühere Anzeige 1 ist seitdem abgeschaltet.
- **Werbung für alle fünf** (Auftrag 28.10. abends, ersetzt die Musterwerbung): je zwei Anzeigen (`anzeigen`) mit Foto, Video-Schleife (MP4 und WebM aus `docs/anzeigen/bauen.mjs`), Zeile oben, Überschrift, Satz und Knopf „Zum Profil“, auf allen Werbeplätzen im Wechsel. Seit 20.11.2026 (zweite Runde) echt statt gestellt: echte Firmenfotos, wo es sie gibt, sonst Logo, Firmenfarben und Schrift der Firmenwebsite (siehe „Anzeigen echt statt gestellt“); einziges KI-Bild ist der Trikot-Entwurf von AJ Sports.
- **HK Growth Webstudio** (Auftrag Betreiber 02.11.2026): Werbung nur für das Webstudio, also für neue Websites, gekennzeichnet nur als „Anzeige“. Kein Hinweis, dass das Webstudio Merzenich Aktuell gebaut hat (Vorgabe Betreiber). Seit 20.11.2026 abends (Vorgabe Betreiber): Software (DealUno), Anfragen und KI-Automation (HK Growth OS), siehe „Anzeigen echt statt gestellt“. Davor drei Anzeigen zu drei Themen (zweite Runde 02.11.2026): Website („Zeit für eine neue Website?“), Anfragen („Mehr Anfragen, die wirklich passen.“, Ziel `#leistungen`) und Automation („Jede Anfrage sofort bei Ihnen.“, Ziel `#automatisierung`), jeweils auf `https://hk-growthoperator.de/` (bis 12.11.2026 `website.hk-growthoperator.de`; dort ist die Seite nicht mehr erreichbar, Ziele, Profil und Beitrag live am 12.11. umgestellt). Die Videos zeigen nur die Webstudio-Seite (HyperFrames, Kompositionen im Scratchpad der Sitzung; Logoleiste mit Referenzen ausgeblendet). Profil mit Logo und ein Beitrag aus den Angaben der Website; Sitz Marienheide, daher ohne Ortsteil.
- **Kontingent:** jeder Zugang darf bis zu 5 eigene Anzeigen einreichen (`werbung.max`).
- **KBS Management** ist seit 20.11.2026 als Firma zurückgezogen (Auftrag Betreiber: „nur die Einträge von KBS gehen raus, als Firma“): Eintrag unter `frueher`, Logo, Banner und Anzeigenmaterial entfernt; live liegen Profil, Beitrag und beide Anzeigen als Entwurf. Impressum, Datenschutz, Kontaktadresse und Fuß bleiben unverändert.
- **Kennzeichnung:** Alle Beiträge sind Anzeigen (`ma_gesponsert_art = anzeige` bzw. `eigen`), verknüpft mit dem Profil, Rubrik Wirtschaft. Firmenbeiträge erscheinen nur unter „Unternehmen“: /unternehmen/, Firmenkanal, Unternehmen-Menü und die Beitragsseite selbst, nicht auf der Startseite, in Rubriken, Ortsteilen, Suche, Seitenspalten oder im RSS-Feed (`includes/firmenbeitraege.php`, seit 1.35.0). Kein Testzeitraum: Die Einträge laufen, bis der Betreiber sie austauscht.

## Zusage eintragen

Je Unternehmen in der Tabelle: Nachweis eintragen (z. B. „Zusage laut Betreiber, 24.10. und 28.10.2026“), Haken für die Einwilligung und, wo es ein Logo gibt, für die Logo-Freigabe.

- **Mit der Logo-Freigabe** kommt das Logo einmal in die Mediathek und wird Profilbild.
- **Mit der Einwilligung** werden leere Profilfelder aus der Datei ergänzt (vorhandene bleiben), das Profil wird veröffentlicht, der Werbebeitrag liegt zur Prüfung in den Freigaben (dort Relevanz wählen und freigeben), und die zwei Werbebanner laufen. Geänderte Motive in der Datei zieht „Fehlende Entwürfe anlegen“ nach, außer eine Anzeige wurde im Backend bearbeitet.
- **Anzeigenvideos, zweite Fassung (02.11.2026):** Jede Anzeige hat ein Video in 16:9 und eine breite Fassung (3,5 : 1) für die Werbebänder, gebaut mit `docs/anzeigen/bauen.mjs` aus `docs/anzeigen/motive.json` und dem Material unter `docs/anzeigen/material/`. Stile je Firma verschieden: Almera Sequenz (Fassade, Tisch, Boden) und Bahnen; ASS Sequenz (Verteiler, Verkabelung, Monteur von hinten) und Detail (Akkuschrauber); Merzlicht Licht (Videowall) und Sequenz; AJ Sports Detail (Trikot-Entwurf, seit 20.11.2026) und Bahnen (KI-Bilder, gekennzeichnet); HK Growth Gerät (Browser und Handy). Keine Gesichter im Mittelpunkt, keine fremden Logos. Dateien `<motiv>-v2.*` und `<motiv>-v2-breit.*`, feste `id` je Anzeige; neu bauen: `node docs/anzeigen/bauen.mjs <arbeitsordner> [--nur <id>]`.
- **Anzeigen echt statt gestellt (20.11.2026, zweite Runde, Vorgabe Betreiber):** Die Higgsfield-Motive wirkten gestellt und sind ersetzt. Stil `marke` (`docs/anzeigen/marken.mjs`): ASS mit echten Fotos von asselektrotechnik.de (Firmenwagen, Mitarbeiter im Firmenshirt) und Farbfläche in Firmenblau; Almera mit Logo, Petrol/Mint und Raleway (Leistungen mit Anschrift, Telefon); Merzlicht mit Logo (von hk-growthoperator.de, vom Betreiber bestätigt) und Videowall als Zeichnung; AJ Sports mit Logo und Streifen, Trikot-Entwurf bleibt als einziges KI-Bild; HK Growth seit dem Abend mit eigener Software (DealUno) und KI-Automation (HK Growth OS) im Browser und auf dem Handy, mit HK-Logo, ohne Schild „Referenz“, weiter ohne Merzenich Aktuell als Referenz (CCRR und PlantCeylon auf Wunsch des Betreibers wieder raus). Schlüssel: `almera-clean-leistungen`, `almera-clean-kontakt`, `ass-elektrotechnik-wagen`, `ass-elektrotechnik-team`, `merzlicht-wand`, `merzlicht-marke`, `aj-sports-trikot`, `aj-sports-marke`, `hk-growth-webstudio-software`, `hk-growth-webstudio-2`, `hk-growth-webstudio-ki`. Ein echtes Foto der Videowall folgt vom Betreiber und ersetzt dann die Zeichnung.
- **Anzeigen mit Higgsfield (20.11.2026, erste Runde, ersetzt):** Almera, ASS, Merzlicht und AJ Sports haben neue Motive: Bild mit GPT Image 2.6, Video mit Kling 3.0 (5 s, Start- gleich Endbild, also nahtlose Schleife), eingebaut mit dem Stil `film` und `standbild_bild` (scharfes Standbild aus dem Ausgangsbild). Alle sind als „KI-Bild“ gekennzeichnet (`ki: true`, Alt-Text „… (KI-Bild)“, Nachweis „erstellt mit Higgsfield“). Keine Gesichter zur Kamera, keine Schrift und keine fremden Logos im Bild; das Trikot zeigt Wappen und AJ-Sports-Logo mit Einverständnis laut Betreiber. Neue Schlüssel (`almera-clean-fenster`, `almera-clean-waesche`, `ass-elektrotechnik-verteiler`, `ass-elektrotechnik-licht`, `merzlicht-videowall`, `merzlicht-spieltag`, `aj-sports-flutlicht`, `aj-sports-trikot`); HK Growth behält seine Website-Ansichten. Prompts und Ausgangsbilder: `artifacts/werbung-2026-11/`.
- **Banner-Plätze:** alle Werbeplätze, die die Seite zeigt: sechs Startseiten-Bänder, Servicespalte und Bühne der Startseite, unter Artikeln, Sport- und Unternehmensspalte; Knopf zum Firmenkanal, ohne Ende. Musteranzeigen gibt es nicht mehr; ohne laufende Anzeige bleibt ein Platz leer. Mehrere Anzeigen auf einem Platz wechseln alle 8 Sekunden.

## Früher eingetragene Betriebe

Abschnitt „Früher eingetragen“ auf derselben Seite: **„Firma zurückziehen“** setzt Profil, alle veröffentlichten oder wartenden Beiträge und Banner des Betriebs auf Entwurf (Banner zusätzlich „Schaltung aus“). Gelöscht wird nichts; die Logos bleiben in der Mediathek. Betrifft Bäckerei Wirtz, Hasberg und Porschen Media.

## Zugänge

Unter **Benutzer → Partner-Zugänge**: Name, E-Mail-Adresse, Rolle „Unternehmens-Partner“ und das Profil wählen, „Zugang anlegen (ohne E-Mail)“.

- Almera Clean mit `info@almera-clean.de`, ASS mit `info@asselektrotechnik.de`. Das Kontingent setzt sich beim Verknüpfen selbst: 5 Anzeigen auf allen Werbeplätzen, für alle fünf.
- Merzlicht und AJ Sports erst, wenn eine E-Mail-Adresse vorliegt.
- WordPress verschickt nichts. Passwort im Profil setzen und persönlich weitergeben; die Mails zum Kopieren stehen in `docs/vorlagen/mails-firmen-2026-10.md`.

## Wenn Angaben von Merzlicht oder AJ Sports kommen

1. In `data/firmen-entwuerfe.json` die Profilangaben ergänzen (`nur_name` erst entfernen, wenn Ort und Kontakt belegt sind), Logo nach `data/logos/` und auf Wunsch einen Beitrag eintragen.
2. „Fehlende Entwürfe anlegen“ legt den neuen Beitrag als Entwurf an; vorhandene Profile überschreibt es nicht. Danach „Zusage eintragen“ mit dem Logo-Haken: Seit 1.48.8 ergänzt das bei schon eingetragener Einwilligung auch leere Profilfelder (Branche, Ort, Kontakt) aus der Datei und legt den Werbebeitrag in die Freigaben.
3. Banner: neues Foto nach `data/banner/<firma>-<n>.jpg`, Video mit `node docs/anzeigen/bauen.mjs` neu bauen, `stand` im Motiv erhöhen; „Fehlende Entwürfe anlegen“ zieht es nach. Oder direkt in der Werbeverwaltung (Werbung → Werbemittel) tauschen; dann bleibt die Anzeige so, wie sie im Backend steht.
