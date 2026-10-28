# Unternehmen auf Merzenich Aktuell: Almera Clean, KBS Management, ASS Elektrotechnik, Merzlicht, AJ Sports

Stand 28.10.2026 abends · Plugin 1.36.0 · Daten: `wordpress/plugin/merzenich-aktuell-core/data/firmen-entwuerfe.json`

Aufträge des Betreibers vom 24.10. und 28.10.2026: Die fünf Unternehmen ersetzen Bäckerei Wirtz, Hasberg Fenster & Rollladentechnik und Porschen Media (Oktober 2026, Testeintrag). Je Unternehmen liegen ein Profil und, wo Angaben vorliegen, ein Werbebeitrag als **Entwurf** in WordPress. Öffentlich wird erst etwas, wenn die Zusage eingetragen ist.

## Was angelegt wird

**WordPress → Merzenich Aktuell → Firmen-Entwürfe → „Fehlende Entwürfe anlegen“**

| Unternehmen | Kanal | Werbebeitrag | Logo | Werbebanner (je 2, Foto + Video) | Anzeigen im Zugang |
|---|---|---|---|---|---|
| Almera Clean | `/unternehmen/almera-clean-merzenich/` | `almera-clean-gebaeudereinigung-merzenich` | von almera-clean.de | je 2 | 5 |
| KBS Management GmbH | `/unternehmen/kbs-management/` | `kbs-management-apps-software-it-beratung` | von kbs-management.tv | je 2 | 5 |
| ASS Elektrotechnik | `/unternehmen/ass-elektrotechnik/` | `ass-elektrotechnik-smart-home-beleuchtung-dueren` | von asselektrotechnik.de | je 2 | 5 |
| Merzlicht | `/unternehmen/merzlicht/` | – (nur Name) | – | je 2 (KI-Bild) | 5 |
| AJ Sports | `/unternehmen/aj-sports/` | – (nur Name) | – | je 2 (KI-Bild) | 5 |

- **Almera Clean, KBS und ASS:** Stammdaten, wie sie am 24.10. bzw. 28.10.2026 (ASS) auf den Websites standen (Branche, Adresse, Telefon soweit genannt, E-Mail, Website), Quelle und Stand, Kurztext, Knopf zur Website. Die Logos sind auf den Websites weiß; für die hellen Logo-Flächen der Seite sind sie dunkel eingefärbt und zugeschnitten (`data/logos/`).
- **ASS Elektrotechnik:** Meisterbetrieb mit Sitz in Düren (Am Dürener Weg 53), daher ohne Ortsteil; Sitz „Düren“ wie bei KBS.
- **Merzlicht und AJ Sports:** Profil nur mit dem Namen (`nur_name`). Keine Adresse, kein Logo, kein Beitrag: Es liegen keine weiteren Angaben vor, und die Seite erfindet keine. Merzlicht trägt die belegte Branche „Werbung auf der Videowall“ (Bericht des SC 1919 zur Videowall am Bürgewaldzentrum). Im Menü steht „Noch keine Beiträge“.
- **Werbung für alle fünf** (Auftrag 28.10. abends, ersetzt die Musterwerbung): je zwei Anzeigen (`anzeigen`) mit Foto, Video-Schleife (MP4 und WebM aus `docs/anzeigen/bauen.mjs`), Zeile oben, Überschrift, Satz und Knopf „Zum Profil“, auf allen Werbeplätzen im Wechsel. Fotos von den Websites der Firmen (Nutzung mit Zusage laut Betreiber, 28.10.2026), bei KBS auch die Intro-Animation der Website; für Merzlicht und AJ Sports KI-Bilder, auf dem Bild als „KI-Bild“ gekennzeichnet, bis echte Fotos kommen.
- **Kontingent:** jeder Zugang darf bis zu 5 eigene Anzeigen einreichen (`werbung.max`).
- **KBS** ist die Herausgeberin von Merzenich Aktuell. Profil, Beitrag, Kanal und Menü sagen das ausdrücklich; der Beitrag ist als „Anzeige · in eigener Sache“ gekennzeichnet. KBS erscheint nicht im Kasten „Unternehmen aus der Gemeinde“ (Sitz Leverkusen).
- **Kennzeichnung:** Alle Beiträge sind Anzeigen (`ma_gesponsert_art = anzeige` bzw. `eigen`), verknüpft mit dem Profil, Rubrik Wirtschaft. Firmenbeiträge erscheinen nur unter „Unternehmen“: /unternehmen/, Firmenkanal, Unternehmen-Menü und die Beitragsseite selbst, nicht auf der Startseite, in Rubriken, Ortsteilen, Suche, Seitenspalten oder im RSS-Feed (`includes/firmenbeitraege.php`, seit 1.35.0). Kein Testzeitraum: Die Einträge laufen, bis der Betreiber sie austauscht.

## Zusage eintragen

Je Unternehmen in der Tabelle: Nachweis eintragen (z. B. „Zusage laut Betreiber, 24.10. und 28.10.2026“), Haken für die Einwilligung und, wo es ein Logo gibt, für die Logo-Freigabe.

- **Mit der Logo-Freigabe** kommt das Logo einmal in die Mediathek und wird Profilbild.
- **Mit der Einwilligung** werden leere Profilfelder aus der Datei ergänzt (vorhandene bleiben), das Profil wird veröffentlicht, der Werbebeitrag liegt zur Prüfung in den Freigaben (dort Relevanz wählen und freigeben), und die zwei Werbebanner laufen. Geänderte Motive in der Datei zieht „Fehlende Entwürfe anlegen“ nach, außer eine Anzeige wurde im Backend bearbeitet.
- **Banner-Plätze:** alle Werbeplätze, die die Seite zeigt: sechs Startseiten-Bänder, Servicespalte und Bühne der Startseite, unter Artikeln, Sport- und Unternehmensspalte; Knopf zum Firmenkanal, ohne Ende. Musteranzeigen gibt es nicht mehr; ohne laufende Anzeige bleibt ein Platz leer. Mehrere Anzeigen auf einem Platz wechseln alle 8 Sekunden.

## Früher eingetragene Betriebe

Abschnitt „Früher eingetragen“ auf derselben Seite: **„Firma zurückziehen“** setzt Profil, alle veröffentlichten oder wartenden Beiträge und Banner des Betriebs auf Entwurf (Banner zusätzlich „Schaltung aus“). Gelöscht wird nichts; die Logos bleiben in der Mediathek. Betrifft Bäckerei Wirtz, Hasberg und Porschen Media.

## Zugänge

Unter **Benutzer → Partner-Zugänge**: Name, E-Mail-Adresse, Rolle „Unternehmens-Partner“ und das Profil wählen, „Zugang anlegen (ohne E-Mail)“.

- Almera Clean mit `info@almera-clean.de`, KBS mit `info@kbs-management.tv`, ASS mit `info@asselektrotechnik.de`. Das Kontingent setzt sich beim Verknüpfen selbst: 5 Anzeigen auf allen Werbeplätzen, für alle fünf.
- Merzlicht und AJ Sports erst, wenn eine E-Mail-Adresse vorliegt.
- WordPress verschickt nichts. Passwort im Profil setzen und persönlich weitergeben; die Mails zum Kopieren stehen in `docs/vorlagen/mails-firmen-2026-10.md`.

## Wenn Angaben von Merzlicht oder AJ Sports kommen

1. In `data/firmen-entwuerfe.json` die Profilangaben ergänzen, `nur_name` entfernen, Logo nach `data/logos/` und auf Wunsch einen Beitrag eintragen.
2. Das vorhandene Profil im Backend von Hand ergänzen; „Fehlende Entwürfe anlegen“ überschreibt nichts. Ein neuer Beitrag wird angelegt.
3. Banner: neues Foto nach `data/banner/<firma>-<n>.jpg`, Video mit `node docs/anzeigen/bauen.mjs` neu bauen, `stand` im Motiv erhöhen; „Fehlende Entwürfe anlegen“ zieht es nach. Oder direkt in der Werbeverwaltung (Werbung → Werbemittel) tauschen; dann bleibt die Anzeige so, wie sie im Backend steht.
