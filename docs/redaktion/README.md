# Redaktionelle Recherche – Arbeitsbereich für Claude Code

Stand: 25.10.2026. Dieser Ordner verbindet die ausführliche ChatGPT-Recherche mit dem bestehenden Merzenich-Aktuell-Repository. Er enthält Arbeitsmaterial und gehört nicht zur öffentlichen Website. Das Repository ist öffentlich; der Ordner ist kein vertraulicher Speicher.

## Historischer Rückstand und Archiv

Alle verfügbaren vollständigen Dossiers vom 13.–25.10. stehen im [Archiv](archiv.md). Letzter ausdrücklich verarbeiteter ChatGPT-Recherchestand: 14.10.; der folgende Recherchevergleich beginnt am 15.10. Das Register verbindet die historischen Fassungen über dossier_history. Historische Themen mit archiv_refresh_noetig vor Übernahme neu prüfen. Der Archivimport aktualisiert keine Website-Artikel und setzt keine alten Themen auf freigegeben.

## Einstieg bei jeder Redaktionsaufgabe

1. PROJECT.md und docs/REDAKTIONELLER-STANDARD-V2.md lesen; git status prüfen und aktuelle main-Änderungen ohne Überschreiben fremder Arbeit übernehmen.
2. latest.md und [offen.md](offen.md) lesen (Lagebild aus dem Register, erzeugt von `node deploy/register-pflege.mjs`; nennt je offenem Thema den möglichen Bestand), register.json nur für die bearbeiteten Themen, danach die relevanten vollständigen Dossiers. Quellenrohstände unter imports/quellen/ berücksichtigen; Abrufdatum und HTTP-Status prüfen. Ein Rohabruf ersetzt weder Detailprüfung noch redaktionelle Recherche.
3. Bestehende Artikel, Termine und Quellenpakete unter inhalte/ sowie die aktuellen Generatoren unter deploy/ suchen. Nicht allein auf das Register verlassen: Es protokolliert ab diesem Übergabestand und ist noch kein vollständiges Artikelinventar.
4. Themen anhand stabiler IDs, kanonischer Quellen-URLs, Ereignis und Ort zusammenführen. Neue Fakten als Update einer bestehenden Meldung behandeln, wenn sie denselben Vorgang betreffen. Kein zweiter Artikel allein wegen eines neuen Recherchelaufs.

## Zwei Recherchefenster

ChatGPT-Recherche: täglich ca. 08:00 und 18:00 Europe/Berlin. Sommer-/Winterzeit beachten. Jeder Lauf prüft alle relevanten Ressorts systematisch, priorisiert Neuigkeiten seit dem letzten erfolgreichen Lauf und ergänzt den belegbaren Kontext. Morgens ausführliche Lage und Tages-/Fristenüberblick, abends neue Meldungen, Ergebnisse, Korrekturen und Vertiefungen. Umfang nach tatsächlicher Nachrichtenlage, keine Wiederholungsquote.

Die bestehende GitHub-Quellenbeschaffung bleibt .github/workflows/quellen-abruf.yml → deploy/quellen-abruf.mjs → imports/quellen/. Ihr Cron wird auf 05:10 und 15:10 UTC erweitert (MEZ 06:10/16:10, MESZ 07:10/17:10), damit vor beiden Recherchefenstern Rohmaterial vorliegt. GitHub-Zeitpläne können sich verzögern. Kein paralleler Scraper und keine Behauptung, die Rohdaten wären fertige Artikel.

## Ablage und Vergleich

- Pro Lauf ein vollständiges, datiertes Dossier unter dossiers/YYYY-MM-DD-morgens.md oder dossiers/YYYY-MM-DD-abends.md; Nachläufe mit Zeitstempel separat, alte Berichte nicht überschreiben.
- Quellenstand mit Europe/Berlin-Zeit, Vergleichsbasis und tatsächlicher Abdeckung angeben. Neue Meldungen, materielle Updates, bestätigte Termine, fortgeführten Kontext und offene Aufträge getrennt zählen. Vollständige Inhalte nur erneut aufnehmen, wenn Kontext oder Update dies rechtfertigt; sonst auf vorige Karte verweisen.
- latest.md auf den jüngsten Bericht umstellen und kurze Änderungsübersicht ergänzen. Unverändert übernommene Alttexte niemals als neu recherchiert oder frisch verifiziert zählen.
- register.json erhalten und bestehende Themen fortschreiben. Pro Thema nach Bedarf Quelle, Ereignisdatum, last_checked_at, Refresh-/Ablaufdatum, Artikelpfad/-ID, Verarbeitung und Commit ergänzen. Unbekannte Werte null lassen. Keine erfundenen Zeitstempel oder URLs.
- Ohne Zugriff auf Vorbericht/Artikelbestand den Dublettenstatus ausdrücklich ungeprüft lassen. Bei Quellenfehlern keine Aktualität vortäuschen. Ohne Neuigkeit einen kurzen geprüften Nullfundbericht mit Quellenlücken ablegen.

## Anforderungen an jede Themenkarte

Stabile ID, Priorität, Ort und Ressort; Typ Neu/Update/Termin/Hintergrund/Nachfassen; Titel, Teaser und eigenständiger Entwurf; konkreter Lesernutzen; Primärquelle und Herausgeber mit direkter URL; Veröffentlichungs-, Ereignis- und Abrufdatum; Faktenbelege; offene Fragen; Textstatus getrennt vom Bildstatus; Ablauf-/Refreshdatum; Vergleich zur letzten Fassung. Bei Standard/Vertiefung/Hintergrund die geltenden Wort-, Absatz- und Faktenanforderungen erfüllen, ohne Fülltext. Fremde Volltexte nicht übernehmen.

Textstatus: T0 gesperrt/unvollständig; T2 konkrete Freigabebedingung offen; T1-B Bestandsentwurf ohne vollständige erneute Primärprüfung; T1 belegter Entwurf, Endabnahme nötig. Kein Status ist automatische Publikationsfreigabe. B0 bedeutet kein freigegebenes integriertes Bild.

## Kombination und Umsetzung durch Claude

Priorität: dringende lokale Meldungen und Fristen, dann bestätigte Sport-/Ortsteilupdates, dann relevante Kreisstoffe. Mehrere Quellen für denselben Vorgang zu einer belegten Meldung zusammenführen; Unstimmigkeiten benennen und klären. Bereits verstrichene Spielvorschauen aktualisieren oder sperren. Sportdaten als Einheit aus Ergebnis, offenem Spiel, nächstem Spiel, Tabelle und einheitlichem Datenzeitpunkt behandeln. Sport bleibt von der Startseite ausgeschlossen.

Recherchedateien nicht nach inhalte/meldungen/ kopieren und nicht pauschal in den WordPress-Import aufnehmen. Einzelne Artikel erst nach Quellenrefresh, Bildfreigabe und Human Review in die vorhandene Pipeline übernehmen. Achtung: WordPress kann die Import-XML stündlich selbst abrufen; ihre Änderung ist publikationswirksam.

Verarbeitung im Register: neu → in_bearbeitung → zur_abnahme → uebernommen bzw. gesperrt/veraltet. Nur bei echter Umsetzung Artikelpfad und Commit erfassen; Quellenprüfung, Bildfreigabe und Publikation getrennt halten. Ein gelesener Bericht ist noch nicht umgesetzt. Claude Code muss laufen bzw. vom Nutzer gestartet werden; die Ablage und ChatGPT-Automation starten keine Claude-Sitzung.

## Bilder und Bildpools

Ziel je Lauf 10–20 zusätzliche motivisch unterschiedliche Kandidaten, soweit sinnvoll verfügbar. Gegen bisherige Dossiers und echte Bibliothek abgleichen. Je Bild Dateiseite, Originalzugang, Urheber, genaue Lizenz und Lizenzlink, sichtbarer Credit, Alt-Text, Maße, tatsächlicher Ort/Datum, Pool und Sperrthemen dokumentieren. Archiv/Symbol klar kennzeichnen. Gefunden, rechtlich geprüft, visuell geprüft, heruntergeladen, Dubletten geprüft und integriert getrennt zählen.

Bestehende Pfade verwenden: deploy/import-editorial-photos.mjs, deploy/editorial-photo-review.json, chatgpt-site/data/editorial-images/editorial-photo-pools.json; weitere Pfade mit rg auflösen. Keine neuen parallelen Poolmanifeste. Keine offizielle Fotoerlaubnis aus bloßem Copyright ableiten, keine ungeprüften Freigaben und kein auswärtiger Ort als Merzenich. Poolerweiterung erfordert tatsächlichen Download, Rechte-/Sichtprüfung, Dublettenabgleich und vorhandenen Importer; eine Kandidatenliste erfüllt das noch nicht.

## Schutz vor unbeabsichtigter Ausspielung

Dieser Ordner liegt außerhalb chatgpt-site/, inhalte/ und wordpress-delivery/. Keine Aufnahme in Frontendnavigation, Suche, Feed, Sitemap, WordPress-Import oder öffentliche Build-Assets. Keine Links von öffentlichen Artikeln auf Dossiers. Nur öffentliche Quellen und redaktionelle Arbeitsdaten speichern, keine Zugangsdaten oder personenbezogenen internen Notizen. Unsichtbar auf der Website bedeutet nicht privat auf GitHub.

## Abschluss eines Laufs

1. Dossier, latest.md und Register auf konsistente IDs, Pfade, Zahlen und Status prüfen.
2. Ausschließlich Recherche-/Dokumentationsdateien committen; keine fremden Änderungen einbeziehen. main frisch lesen und mit aktuellem Parent/Lease aktualisieren, niemals Force-Push oder konkurrierende Commits überschreiben.
3. Commit auf main bereitstellen und Pfade/Inhalt am resultierenden Commit nachlesen. Scheitert der Upload, Dossier verfügbar machen und ausdrücklich „nicht im Repo“ melden; latest erst nach vollständiger Dateibereitstellung als erfolgreich melden.
4. Kurz an Luis melden: echte Neu-/Updatezahlen, Bildstatus, Dossierpfad und Commit. Website-Veröffentlichung und Claude-Verarbeitung nur bestätigen, wenn separat belegt.
