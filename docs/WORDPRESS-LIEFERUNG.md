# Merzenich Aktuell – Lieferung und Prüfstand

28. Oktober 2026 · Theme 21.21.0 · Core-Plugin 1.34.0 · live: 21.20.8 / 1.31.0 (Historie unten)

## Neu am 28.10.2026: Plugin 1.34.0, Themenbild aus dem Repository gilt auch für bestehende Beiträge

**Stand live:** noch nicht eingespielt (enthält 1.32.0 und 1.33.0).

**Anlass:** Live zeigten Beiträge Bilder, die nicht zum Thema passen (Prüfung 28.10.: Martinszüge mit Ortsansicht, L264-Unfall mit Polizeifoto). Zwei Ursachen im Plugin: (1) Ein neues Bild aus dem Repository kam nur bei Beiträgen ohne Bild an. (2) Die tägliche Bildverteilung ersetzte Symbolbilder aus dem Abgleich, sobald ein Foto mehr als zweimal vorkam, durch ein Foto des Ressort-Pools (jede Leben-Meldung ein Dorfplatz).

- **Themenbild nachziehen** (`includes/abgleich.php`, `ma_abgleich_bild_veraltet`, `ma_abgleich_bild_nachziehen`): Weicht das Bild der Import-Datei vom aktuellen ab und wurde das Bild nicht von Hand gewählt, übernimmt der Abgleich das neue Bild samt Nachweis; Verlaufseintrag, Zähler „Themenbild aktualisiert“ im Protokoll.
- **Keine Umverteilung von Themenbildern** (`includes/bildpools.php`, `ma_bildpool_allgemein`): Symbolbilder aus der Import-Datei bleiben stehen; das Repository wählt sie nach Motiv und begrenzt Wiederholungen. Umverteilt werden nur noch Ortsansichten und von WordPress selbst gesetzte Poolfotos.
- **Prüfung:** `qa/wordpress/abgleich-test.php` (fünf Prüfungen Themenbild), `qa/wordpress/bildpools-verteilen-test.php` an die Regel angepasst; alle `qa/wordpress/*-test.php` grün.

## Neu am 28.10.2026: Plugin 1.33.0, Beiträge der KI-Redaktion direkt online, Themenfotos

**Stand live:** noch nicht eingespielt (kommt mit 1.34.0).

**Anlass:** Entscheidung von Luis Kummer am 28.10.: Beiträge, die die KI-Redaktion (Claude-Redaktionslauf) schreibt, gehen ohne Freigabe online; Beiträge anderer (Feuerwehr-Automatik, Umkreis, Vereine, Partner, ChatGPT-Pakete) laufen weiter über die Freigaben. Außerdem muss das Bild zum Thema passen.

- **KI-Redaktion direkt** (`includes/abgleich.php`): neue Einstellung unter Merzenich Aktuell → Abgleich, „Beiträge der KI-Redaktion direkt veröffentlichen“ (Standard aus). Erkennt Meldungen am Importfeld `ma_herkunft = ki-redaktion`, das `deploy/wordpress-import.mjs` nur für Pakete `inhalte/meldungen/*recherche-claude*` setzt (nicht bei `"zurFreigabe": true`). Neue solche Meldungen werden veröffentlicht, bereits angelegte Entwürfe beim nächsten Lauf ebenfalls; beide mit den Prüfhäkchen und Prüfer „KI-Redaktion (Freigabe Luis Kummer, 28.10.2026)“, Verlaufseintrag. Nur Status Entwurf; in Prüfung, abgelehnt, archiviert, Papierkorb und von Hand geänderte Beiträge bleiben unberührt. Die globale Option „Sofort veröffentlichen“ bleibt aus.
- **Themenfotos** (`includes/bildpools.php`): neuer Detailpool `themen` (22 gezielt recherchierte, gesichtete Commons-Fotos: Sturmschaden, Martinsfeuer, Baustelle, Eicheln, Zeltplatz, Wanderweg, Karneval, Strommast, Seminarraum, Papiertonne, Erntekrone).
- **Prüfung:** `php qa/wordpress/abgleich-test.php` (fünf neue Prüfungen: KI mit/ohne Option, andere Meldungen und Termine unberührt, Standard aus, Häkchen).

**Einspielen:** ZIP `wordpress-delivery/merzenich-aktuell-core.zip` (baut die CI nach dem Push) unter Plugins → Installieren → Plugin hochladen, „Vorhandene Version ersetzen“. Danach unter Merzenich Aktuell → Abgleich den Haken „Beiträge der KI-Redaktion direkt veröffentlichen“ setzen und „Jetzt abgleichen“. Nachweis: Die Claude-Meldungen der letzten Tage antworten unter ihrer Adresse mit HTTP 200.

## Neu am 24.10.2026: Plugin 1.32.0 und Theme 21.21.0, Relevanz in drei Stufen, vier Unternehmen, Werbebanner, Hilfe für alle Zugänge

**Stand live:** noch nicht eingespielt.

**Anlass:** Auftrag des Betreibers vom 24.10.: im Backend nur noch drei Relevanzstufen statt zehn; statt Bäckerei Wirtz, Hasberg und Porschen Media stehen Almera Clean, KBS Management, Merzlicht und AJ Sports auf der Seite, mit Zugang; Merzlicht und AJ Sports laufen als Werbebanner; Mails an die Unternehmen.

- **Relevanz** (`includes/relevanz.php`): ein Wert je Meldung, 1 Hoch, 2 Mitte, 3 Niedrig; ohne Angabe gilt Mitte. Gerechnet wird mit Punkten (9, 6, 2) minus einem Punkt je Tag Alter; Aufmacher und Bühne nur mit Stufe 1 und frisch (72 Stunden). Drei Knöpfe in Freigaben und Beitragsbox, Auswahl „1 Hoch / 2 Mitte / 3 Niedrig“ in der Beitragsliste (Filter je Stufe, Sortierung mit Hoch oben, ohne Angabe wie Mitte) und im Layout-Board. Die Priorität 1–10 und die Einstellungen „Aufmacher ab“ und „Bühne ab“ entfallen. Einmalige Umstellung beim ersten Aufruf: 8–10 → 1, 4–7 → 2, 1–3 → 3; der alte Wert bleibt in `_ma_relevanz_alt`, kein Beitrag wird doppelt umgerechnet. Die frühere Umstellung von 1.11 (1–3 = „Nur in der Rubrik“) ist entfernt; sie hätte nach der neuen Skala die wichtigsten Meldungen von der Startseite genommen.
- **Unternehmen** (`data/firmen-entwuerfe.json`): Almera Clean (Gebäudereinigung, Kettenweg 1, Merzenich) und KBS Management GmbH (App- und Softwareentwicklung, Leverkusen) mit Angaben von ihren Websites, Logo (weiß, für helle Flächen dunkel eingefärbt) und je einem Beitrag; Merzlicht und AJ Sports nur mit Namen (`nur_name`), ohne Adresse, Logo und Beitrag, bis der Betreiber Angaben schickt. Kein Testzeitraum mehr: Kennzeichnung „Anzeige“ (`ma_gesponsert_art = anzeige`), bei KBS „Anzeige · in eigener Sache“ mit dem Hinweis „Herausgeberin von Merzenich Aktuell“ (Profil, Beitrag, Kanal, Menü). KBS steht nicht im Kasten „Unternehmen aus der Gemeinde“.
- **Firma zurückziehen** (Firmen-Entwürfe, Abschnitt „Früher eingetragen“): Profil, Beiträge und Banner eines früheren Betriebs werden Entwurf, Banner mit „Schaltung aus“. Nichts wird gelöscht, Logos bleiben in der Mediathek.
- **Werbebanner** Merzlicht und AJ Sports (`data/banner/`, 1200 × 400, nur der Name als Schriftzug): entstehen mit der eingetragenen Einwilligung einmal als laufende Anzeige auf allen sichtbaren Werbeplätzen (sechs Startseiten-Bänder, Servicespalte und Bühne der Startseite, unter Artikeln, Sport- und Unternehmensspalte), ohne Link und ohne Ende, und ersetzen dort die Musteranzeigen; mehrere Anzeigen auf einem Platz wechseln wie bisher alle 8 Sekunden.
- **Theme:** Jedes Profil aus den Firmen-Entwürfen ist ein Kanal, auch ohne Beitrag (`ma_business_kanal`); das Menü zeigt dann „Noch keine Beiträge“. Die Beispielfirmen verschwinden, sobald es eine echte Firma gibt (`MA21_MUSTER_BIS = 1`). Sitz außerhalb der Gemeinde über `ma_business_ort`.
- **Kasten „Kennzeichnung“:** Speichern im Editor behält „anzeige“ und „eigen“; der Haken steuert nur den Testeintrag.
- **Code-Prüfung** (2 Prüfer, Befunde eingearbeitet): Die Umstellung läuft nie im Namen eines Partners (dessen Schreibsperre hätte sie still leer laufen lassen), schreibt erst den neuen Wert und prüft ihn, sichert dann den alten, erfasst auch Papierkorb und eigene Status und gilt erst als erledigt, wenn jeder Beitrag umgerechnet ist. Profile nur mit Namen bekommen auch auf Profilseite und im Branchenbuch keinen erfundenen Ort und stehen nicht im Kasten „aus der Gemeinde“; die KBS-Profilseite nennt die Herausgeberin; „Anzeige · in eigener Sache“ ohne doppeltes „von“; „Firma zurückziehen“ holt nichts aus dem Papierkorb; ein Banner ohne Bild bekommt es beim nächsten Eintragen.
- **Zugänge** für Almera Clean und KBS über Benutzer → Partner-Zugänge (ohne E-Mail von WordPress); Mails zum Kopieren in `docs/vorlagen/mails-firmen-2026-10.md`.
- **Hilfe für alle Zugänge** (`includes/hilfe.php`): Kasten „So geht's: in vier Schritten zum Beitrag“ ganz oben im Dashboard der Partner (einspaltig, ohne leere Fläche), Menüpunkt **Hilfe** mit Erklärvideo, PDF, häufigen Fragen und Kontakt, und eine geführte Tour im Editor (Titel, Text, Beitragsbild, Bildrechte, Ortsteil, Einreichen; „Nicht mehr zeigen“ je Konto, über die Hilfe-Seite neu startbar). Die Anleitung liegt als PDF im Plugin (`assets/hilfe/anleitung-zugang.pdf`, 24 Seiten, Quelle `docs/anleitung/ZUGANG.md`, gebaut mit `node deploy/anleitung-pdf.mjs zugang`). Das Video (3:51 Minuten, ohne Ton, Quelle `docs/anleitung/video/`) kommt in die Mediathek; die Option `ma_hilfe_video` nennt die Medien-ID. Ohne Video zeigt die Hilfe nur das PDF.
- **Beim Aufnehmen gefunden und behoben:** Die Tour startete erst nach 10 Sekunden (Titel liegt im Editor-Rahmen); der Haken für die Bildrechte galt in der Seitenleiste, aber nicht im Fenster vor dem Einreichen; Hinweise der Redaktion („bittet um Änderungen“, „Änderungsvorschlag zu …“) erschienen im Block-Editor nicht; Datum und Ort eines Termins lagen zugeklappt unter dem Text (jetzt bei Terminen, Immobilien, Stellen, Familien- und Traueranzeigen und Tipps aufgeklappt); Partner sahen den Redaktionskasten „Freigabe“ („4 Freigaben offen“); das Upload-Fenster nannte die Servergrenze statt 10 MB; ein Änderungsvorschlag stand in der Liste wie ein zweiter Entwurf.

## Neu am 20.10.2026: Plugin 1.31.0 und Theme 21.20.6 bis 21.20.8, drei echte Firmen im Unternehmensmenü

**Stand live (20.10., 16:50 Uhr):** Plugin 1.31.0 und Theme 21.20.8 sind eingespielt. Die Zusagen aller drei Betriebe sind eingetragen (Nachweis „Zusage laut Betreiber, 20.10.2026“), Profile und Werbebeiträge sind veröffentlicht, Testzeitraum bis 19.11.2026; die sechs Zusatzbeiträge bleiben Entwurf. Menü, Übersicht, Kanäle und Beiträge geprüft bei 1440, 820 und 390 px, 0 Konsolenfehler, Tastatur (Pfeil runter, Tab, Escape) funktioniert.

**Anlass:** Im Hover-Menü „Unternehmen“ standen noch die drei Beispielfirmen. Der Betreiber hat am 20.10. bestätigt, dass Bäckerei Wirtz, Hasberg Fenster & Rollladentechnik und Porschen Media dem kostenfreien Testeintrag (Anzeige, 0 €, 30 Tage) und der Logonutzung zugestimmt haben.

- **Zusage eintragen** (Merzenich Aktuell → Firmen-Entwürfe): je Betrieb Einwilligung und Logo-Freigabe getrennt, jeweils mit Pflicht-Nachweis, Person und Zeit (`ma_business_consent_quelle`, `ma_business_logo_freigabe`). Das Logo kommt aus `data/logos/` einmal in die Mediathek (Alt-Text, Nachweis, Herkunft) und wird Beitragsbild des Profils; `ma_business_logo_rechte = geklaert`. Mit der Einwilligung wird das Profil veröffentlicht (Sperre in `business.php` bleibt) und der Werbebeitrag liegt zur Prüfung in den Freigaben. Zweiter Aufruf ändert nichts.
- **Logos:** Wirtz (PNG 500 × 500) und Hasberg (JPEG 543 × 205) von den Unternehmenswebsites, Porschen Media aus dem SVG der Website als PNG auf Weiß gerendert (WordPress nimmt kein SVG an).
- **Darstellung:** Logos überall ganz auf heller Fläche (`object-fit: contain`, kein Kreis), im Menü 72 px breit. Anzeigen eines Unternehmens ohne eigenes Foto zeigen das freigegebene Logo (`ma21_bild_firmenlogo`, Klasse `bild-logo`), auch in „Neu von Unternehmen“. Sie bekommen kein Commons-Symbolfoto und werden nicht verteilt. Ein Logo zählt nicht als Motiv der Startseite.
- **Beispielfirmen** verschwinden über `ma21_muster_aktiv()`, sobald drei echte Kanäle je einen veröffentlichten Beitrag haben. Die statische Kopie (`deploy/ressort-menue.mjs`) zeigt weiter die Beispielfirmen; auf WordPress liefert `ma21_menue_firmen()` die Firmen.
- **Zusatzbeiträge:** je Betrieb zwei sachliche Beiträge aus den Leistungsseiten (abgerufen 20.10.2026), nur als Entwurf.
- **Firmenzugänge** (`unternehmenskanal.php`) sind vorbereitet, brauchen aber je Betrieb eine E-Mail-Adresse und die Zustimmung zum Konto.

## Neu am 18.10.2026: Plugin 1.30.0, weniger Wiederholungen über andere echte Fotos

**Stand live (18.10.):** Theme 21.20.5 und Plugin 1.30.0 sind eingespielt. Gemessen danach auf 17 Seiten bei 1440 px bis zum Ende nachgeladen: 0 Karten ohne Bild, kein Foto öfter als 2× je Seite, 0 Konsolenfehler. Verschiedene Fotos: Startseite 68 → 109 (von 130 Bildern), `/nachrichten/` 45 → 118, `/ort/merzenich/` 35 → 59, `/blaulicht/` 38 → 54. Karte und Meldungsseite zeigen dasselbe Foto mit Nachweis (Stichprobe `/ort/girbelsrath/`).

**Anlass:** Nach 21.20.5 zeigt jede Karte ihr Bild, aber 189 Meldungen teilen sich nur 87 Fotos. Gemessen bis zum Ende nachgeladen: Startseite ein Foto 9×, `/ort/merzenich/` 8×, `/ort/girbelsrath/` die Ortsansicht 7×, `/ort/buergewald/` 6×. Die Pools haben je rund 20 geprüfte Fotos.

- **Verteilung über alle Meldungen** (`ma_bildpool_verteilen`): statt der letzten 45 Tage alle veröffentlichten Meldungen und kommende Termine. Von neu nach alt behält jede Meldung ihr Foto, solange es höchstens zweimal vorkommt; sonst kommt das seltenste geprüfte Foto ihres Pools (bei Gleichstand das am längsten nicht vergebene), der übergeordnete Pool nur, wenn der eigene kein Foto unter der Grenze mehr hat. Ortsansichten behalten so die zwei neuesten Meldungen.
- **Was wechseln darf:** das automatisch vergebene Poolfoto und Symbolbilder oder Ortsansichten, die der Abgleich gesetzt hat (Typ am Inhalt `symbol`/`place`, Anhang mit `ma_image_static_src`). Nie: Originalfotos, Quellenmotive, Archivbilder und alles, was jemand von Hand gesetzt hat (`_ma_bild_von_hand`, gesetzt vom Hook auf `_thumbnail_id`).
- **Kein Hin und Her:** Jeder Inhalt wechselt höchstens einmal (`_ma_bildpool_verteilt`). Der Abgleich liefert ein so ersetztes allgemeines Import-Bild nicht stündlich zurück (`_ma_bildpool_ersetzt`), ein echtes Foto setzt sich aber durch. Bildangaben aus der Import-Datei landen nicht an einem von Hand gewählten Bild.
- **Nachweis:** Das Poolfoto bringt seinen Nachweis mit; die alten Angaben liegen in `_ma_bildpool_vorher`. Jeder Wechsel steht im Verlauf mit altem und neuem Medium. Rückweg: Plugin 1.29.1, dann setzt der Abgleich die Import-Bilder wieder.
- **Code-Prüfung:** 3 Prüfer mit Gegenprüfung; bestätigt und eingearbeitet: tägliches Weiterschieben in vollen Pools, von Hand gewählte Import-Bilder, übergeordneter Pool zu früh, gescheiterter Download, feste Reihenfolge, Abstand auch zu festen Fotos, veralteter Kommentar in `v20.js`.

## Neu am 18.10.2026: Theme 21.20.5, jede Karte zeigt wieder ihr Bild

**Anlass:** Der Betreiber sah auf der Live-Seite Linien statt Bilder: „ÜBERALL BILDER WIEDER HIN UND GENERISCHE STRICHE DA RAUS“. Die Obergrenze „höchstens zweimal dasselbe Foto je Seite“ (21.20.3/21.20.4) nahm ab dem dritten Vorkommen das Bild aus der Karte; stattdessen stand eine 3 px dicke Linie da. Gemessen am 18.10. bis zum Ende nachgeladen: Startseite 62 von 142 Karten ohne Bild, `/nachrichten/` 38 von 126, `/ort/merzenich/` 25 von 70. Alle 189 Meldungen haben ein Beitragsbild.

- **Theme:** `ma21_bild_seite`, `ma21_bild_frei`, `ma21_bild_zaehlen`, `ma21_bild_register*` und `MA21_BILD_MAX` sind weg; alle Karten, Zeilen, Terminzeilen und Startseitenkarten nutzen wieder `ma21_bild()`. `ma21_termin_zeile()` hat keinen Parameter `$alleBilder` mehr.
- **Browser:** `endlos.js` (`bilderBegrenzen`) und `v20.js` (`fotoGrenze`) nehmen keine Bilder mehr heraus.
- **CSS:** Die Linie über Karten ohne Bild (`plattform.css`) ist weg; hat ein fester Platz doch kein Bild, steht nur der Text.
- **Bleibt:** Foto-Schlüssel (`data-foto`, `ma21_foto_schluessel`) für die Motivgrenze der Startseiten-Belegung (wählt nur die Meldung je Platz) und das Foto des Tages.
- **Test:** `qa/wordpress/bildwiederholung-test.php` prüft jetzt, dass jede Karte ihr Bild behält und keine Linie im CSS steht.

## Neu am 17.10.2026: Theme 21.20.3/21.20.4 und Plugin 1.29.0, höchstens zweimal dasselbe Foto je Seite

**Stand live (17.10., 21:20 Uhr):** Theme 21.20.4 und Plugin 1.29.1 sind eingespielt. Gemessen danach auf denselben 17 Seiten bei 1440 px bis zum Seitenende: kein Foto mehr als zweimal, auch nicht auf der Startseite; 0 Konsolenfehler. Die strenge Prüfung nach Fotoschlüssel (13 Seiten mit `/heute/`) ist überall in Ordnung. Auf `/termine/` stehen 28 Bilder aus 21 Fotos (vorher 15, Kirchenfoto 7×).

**Zwischenstand (17.10., 19:20 Uhr):** Plugin 1.29.0 und Theme 21.20.3 sind eingespielt. Gemessen nach dem Einspielen auf denselben 17 Seiten: Bis auf die Startseite steht kein Foto mehr als zweimal da. Auf der Startseite standen zwei Fotos noch je 3×, alle auf **festen Plätzen**, die die Motivgrenze des Layouts bewusst umgehen; ihre Fotos stammen aus dem Abgleich und werden deshalb nicht umverteilt.

**Plugin 1.29.1** (letzte Prüfrunde, Kasten „Bild und Prüfung“): Der Kasten schickt mit, welches Bild und welche Bildangaben er beim Laden zeigte (`ma_bildfelder_bild`, `ma_bildfelder_werte`). Hat das Bild seitdem gewechselt (Poolfoto beim Veröffentlichen, Neuverteilung, eigenes Bild in der Seitenleiste), schreibt er nur Felder, die die Redaktion seither geändert hat. Steht dabei das automatisch vergebene Poolfoto, kommen die Änderungen in dessen Sicherung. Die Sicherung kommt zurück, bevor das neue Medium leere Angaben füllt (Hook-Priorität 5). Ein Poolfoto ist immer ein Symbolbild, auch von Hand gewählt.

**Theme 21.20.4:** Auch die Startseite zeigt ein Foto höchstens zweimal. Aufmacher und Bühne behalten ihr Bild und zählen mit, ebenso die fünf Ortsbilder „Ihre fünf Orte“. Karten in den Rubrikblöcken erscheinen ab dem dritten Vorkommen ohne Bild (`ma21_karte`, `ma21_bild_register_start`).

**Anlass:** Der Betreiber meldete am 16.10.: „gerade ist oft das gleiche Bild 5 mal in einer Seite“. Gemessen am 17.10. auf 17 Seiten bei 1440 px bis zum Seitenende:

| Seite | Wiederholung |
|---|---|
| `/termine/` | Kirchenfoto 7×, Tanzfoto 4× |
| `/ort/buergewald/` | Ortsansicht 6× |
| `/rathaus/` | ein Pool-Foto 4× |
| Startseite | zwei Pool-Fotos je 3× |

- **Plugin: Pool-Bilder gleichmäßig verteilen** (`includes/bildpools.php`)
  - Neue Symbolbilder nehmen das am wenigsten genutzte geprüfte Foto des Pools. Gezählt werden nur sichtbare Inhalte: kommende Termine und Meldungen der letzten 45 Tage.
  - Höchstens zweimal je Foto. Reicht ein kleiner Pool nicht (Tanz: 1 Foto, Kirche: 4), hilft der übergeordnete Pool aus: Termine für Termine, Aktuell für Meldungen.
  - `ma_bildpool_verteilen()` verteilt automatisch vergebene Bilder (`_ma_bildpool_auto`) neu. Es läuft einmal nach dem Update, täglich per Cron und über den Knopf „Symbolbilder verteilen“ unter Medien → Bildpools. Redaktionsfotos und Bilder aus dem Abgleich bleiben unberührt; jeder Wechsel steht im Verlauf.
- **Theme: höchstens zweimal dasselbe Foto je Seite**
  - `ma21_bild_seite()` zählt je Seitenaufruf das Foto, nicht den Anhang: Dateiname ohne Größen-, Kopie- und Prüfsummenzusatz. Damit zählen Abgleich- und Pool-Fassung sowie 1440 und 720 px als ein Foto.
  - Ab dem dritten Mal erscheint die Karte ohne Bild (Entscheidung Betreiber: „Ohne Bild“). Das gilt für Listen, Kartenraster, Termine und Unternehmenskarten. Die Meldungsseite behält ihr Foto.
  - Startseite: Die Motivgrenze zählt nach Foto; die Ortsbilder der Bühne zählen vorab mit.
  - Nachladen (`assets/endlos.js`): dieselbe Grenze und Normalisierung über die ganze Seite.
- **„Weitere Meldungen“ auf der Startseite:** Die Karten sehen aus wie die Rubrikblöcke darüber (Raster `.desk-mittel`, 3/2/1 Spalten). Die Anzeige folgt wie in den Ressortlisten nach Karte 6.
- **Markt (Vorführ-Check, Gruppe Markt, am 17.10. live nachgestellt)**
  - Die Testanzeige einer Beispielfirma führt auf ihrem eigenen Kanal zum ersten Musterbeitrag statt auf dieselbe Seite.
  - Das Etikett „Musterbeitrag“ heißt auf kleinen Vorschaubildern am Handy kurz „Muster“ und sitzt in der Ecke (vorher 27 % der Bildfläche).
  - `/termine/` am Handy: Kopf ohne Einleitungssatz, Zeiträume in einer wischbaren Zeile, Auswahlfelder ohne sichtbare Beschriftung. Der erste Termin beginnt bei 474 statt 798 px (390 × 844).
  - Bildnachweise an Musterbildern waren schon vollständig; Fotowiederholungen auf `/unternehmen/` erledigt die Grenze oben.
- **Neue Meldung:** LEADER an Inde und Rur, sieben neue Projekte, Ideen bis 27.01.2027 (`inhalte/meldungen/2026-10-17-leader.json`). Sie kommt mit dem stündlichen Abgleich als Entwurf.
- **Tests:** neu `qa/wordpress/bildwiederholung-test.php` und `bildpools-verteilen-test.php`; erweitert `layout-test.php`, `endlos-test.php` und `unternehmen-test.php`.

**Code-Prüfung** (drei Prüfer, jeder Befund gegengeprüft, 10 bestätigt, alle eingearbeitet):
- **Redaktionsfotos sind geschützt.** Das Plugin merkt sich, welches Poolfoto es selbst gesetzt hat (`_ma_bildpool_auto_bild`). Ersetzt jemand das Bild (Redaktion, Editor, Änderungsvorschlag, Abgleich), entfällt das Merkmal, und weder der Verteil-Lauf noch das Nachziehen fassen das Bild an.
- **Ein Foto-Schlüssel für Plugin, Theme und Nachladen** (`includes/fotoschluessel.php`):
  - Pipeline-Dateien mit Prüfsumme zählen als ein Foto.
  - Übrige Dateien unter `/assets/` zählen ohne die erzeugte Breite.
  - Alles andere zählt je Anhang. Zwei verschiedene Uploads „plakat.jpg“ oder „einsatz-118“ und „einsatz-121“ bleiben also zwei Fotos.
  - Das Theme schreibt den Schlüssel als `data-foto` ans Bild; `endlos.js` liest ihn.
- **Täglicher Lauf:** nur bei eingeschalteter Automatik; der Knopf geht immer.
- **Laufende mehrtägige Termine** zählen als sichtbar.
- **Gezählt wird auch, was fest auf der Seite steht:**
  - Meldungsseite: Das Foto der Meldung zählt mit, „Weiterlesen“ zeigt es höchstens noch einmal.
  - Startseite: Ortsbilder „Ihre fünf Orte“ und Foto des Tages zählen beim Nachladen mit.
- **Kleinkram:** Fokusrahmen der Zeitraum-Knöpfe auf `/termine/` am Handy vollständig; Abstand über „Weitere Meldungen“.
- **Bleibt:** Die statische Kopie (`chatgpt-site`) begrenzt Fotos nur beim Nachladen. Ihre Ortsseiten listen alle Meldungen auf einmal. Live läuft WordPress mit der Begrenzung im Theme.

**Nachprüfung der Korrekturen** (zwei Prüfer, gegengeprüft, 6 weitere bestätigt, alle eingearbeitet):
- **Merkmale von vor 1.29.0:** Der Lauf trägt das gemerkte Bild einmal nach, solange noch ein Poolfoto am Inhalt steht; sonst fällt das Merkmal weg. Danach entfernt jeder Bildwechsel, den nicht das Plugin selbst vornimmt, das Merkmal, auch wenn die Redaktion ein anderes Poolfoto wählt.
- **Symbolbild vor dem eigentlichen Bild** (Abgleich mit „sofort veröffentlichen“, Block-Editor):
  - Vor dem ersten Poolfoto sichert das Plugin Nachweis, Lizenz, Quelle, Typ und Rechtestand (`_ma_bildpool_vorher`).
  - Kommt danach das eigentliche Bild, stellt es diese Angaben wieder her.
- **Änderungsvorschläge** (`redaktion.php`):
  - Bild und Bildangaben werden nur übernommen, wenn der Vorschlag das Bild geändert hat. Ein inzwischen neu verteiltes Symbolbild bleibt.
  - Kopien bekommen kein eigenes Symbolbild.
- **Foto des Tages:** Es meidet Fotos der Startseite nach Foto-Schlüssel, auch Pool-Anhänge und die Ortsbilder, und trägt `data-foto`.
- **`/termine/`:** Die Liste wird im Browser gefiltert. Deshalb rechnet `assets/v20.js` die Grenze nach jedem Filtern über die sichtbaren Termine; ein gefilterter Termin verliert sein Bild nicht wegen ausgeblendeter Zeilen. Ohne JavaScript stehen alle Bilder da.

**Dritte Prüfrunde** (zwei Prüfer, gegengeprüft, 6 bestätigt, alle eingearbeitet; Test `qa/wordpress/bildangaben-test.php` mit dem echten Hook):
- **Änderungsvorschlag mit neuem Bild:** Die Sicherung des Poolfotos wird verworfen. „Bildrechte geprüft“ kommt also nicht zurück an ein ungeprüftes Bild.
- **Änderungsvorschlag ohne neues Bild:** Korrekturen am Nachweis kommen nur an, wenn das Original noch dasselbe eigene Foto trägt. Steht dort das automatisch vergebene Poolfoto oder inzwischen ein anderes Bild, bleiben dessen Angaben.
- **Kasten „Bild und Prüfung“** (`editorial.php`): Er merkt sich, welches Beitragsbild er beim Laden zeigte (`ma_bildfelder_bild`).
  - Zeigte er ein anderes Bild als das inzwischen gesetzte Poolfoto (meist noch keins; der Block-Editor lädt ihn nach dem Veröffentlichen nicht neu), gehen seine Bildangaben in die Sicherung des Poolfotos. Sie gelten, sobald das eigentliche Bild kommt. Der Nachweis des Poolfotos bleibt, ebenso das Merkmal.
  - Zeigte er das Poolfoto und ändert die Redaktion eine Angabe bewusst, gilt das Bild als ihres.
  - Zeigte er ein Poolfoto, das inzwischen ersetzt ist (neu verteilt oder durch ein eigenes Bild), schreibt er dessen unveränderte Angaben weder an den Inhalt noch in die Sicherung; nur bewusst geänderte Felder zählen (seit 1.29.1 gemessen am Stand beim Laden, nicht an den Angaben des Mediums).
- **Abgleich:**
  - Ist der Bild-Download beim Anlegen gescheitert und steht nur das Poolfoto, holt der nächste Lauf das Bild aus der Import-Datei nach (`ma_abgleich_bild_fehlt`).
  - Neue Bildangaben aus der Import-Datei gehen bis dahin in die Sicherung, nicht an das Poolfoto.
- **`/heute/`:** Die Terminzeilen begrenzt wieder der Server; nur `/termine/` gibt alle Bilder aus und begrenzt im Browser.
- **`/termine/`:** `editorial-audit.js` blendet laufende mehrtägige Termine nicht mehr nach ihrem Beginn aus (die Zeile trägt ein Ende, das `v20.js` prüft).


## Neu am 16.10.2026 (abends): Theme 21.20.1 und Plugin 1.28.3, Feinschliff und drei Betriebe als Entwurf

**Feinschliff aus dem Vorführ-Check.** Die offenen Befunde wurden am 16.10. auf der Live-Seite 21.20.0 noch einmal nachgestellt. Behoben ist, was noch bestand:

- **Kopf und Menüs**
  - Wettersymbol und Ortsname haben bei 768 bis 1180 px Abstand; am Desktop gleichmäßige Abstände um den Trennpunkt.
  - Handy: Die Ressortleiste läuft rechts sichtbar aus und springt auf das aktuelle Ressort (ohne die Seite zu verschieben).
  - „Menschen“ passt bei 1181 bis 1339 px in die Leiste.
  - Menü „Termine“: rechte Spalte „Nächste Termine“; Menü „Tipp“ zeigt die nächsten Tipp-Termine.
- **Suche**
  - Kopf mit Begriff, Trefferzahl und Suchfeld.
  - Leere Suche bittet um einen Begriff, statt alle Meldungen zu listen.
  - Fenstertitel „Suche“ bzw. „Suche: „Begriff““.
- **Meldungsseite**
  - Nur noch eine robots-Angabe im Kopf.
  - Kommentarformular ohne das nicht vorhandene Feld „Website“; der Haken bleibt gesetzt, wenn schon gespeichert.
  - „Link kopiert“ ist sichtbar, auch in der senkrechten Leiste ab 1360 px.
  - „Das passt zum Thema“ dreispaltig ab 768 px, Titel nach fünf Zeilen gekürzt.
  - Anzeige unter dem Text am Handy zweizeilig statt mit „…“.
- **Listen**
  - Endhinweis „Alle Meldungen geladen. Archiv nach Monaten“, statt ältere Meldungen zu versprechen, die es nicht gibt. Er steht jetzt auch auf einseitigen Listen wie `/ort/buergewald/`.
  - Keine doppelte Linie darüber.
  - `/nachrichten/` zeigt rechts „Nach Ortsteil“ und „Nach Ressort“ statt derselben fünf Meldungen wie links.
  - Bildhinweis („Symbolbild“, „Ortsansicht“) auch in Zeilenlisten (Feuerwehr, Polizei, Verkehr).
  - Ortsmarke „Merzenich · Ortsteil“ bricht in schmalen Karten nicht mehr nach dem Punkt um.
- **Startseite:** Schlagzeilen auf der Bühne und im Termin-Kasten werden nicht mehr mit „…“ gekürzt; der Bühnen-Aufmacher darf vier Zeilen haben.
- **Termine:** Terminbilder in der Liste und Symbolbilder auf der Terminseite haben ein leeres `alt`. Die Pool-Texte waren oft englisch oder abgeschnitten; die Bildunterschrift nennt das Symbolbild.

**Drei echte Betriebe als Entwurf** (Auftrag Betreiber 16.10.2026, Entscheidung „Entwurf bis Zusage“): Bäckerei Wirtz, Hasberg Fenster & Rollladentechnik, Porschen Media. Einzelheiten, Logo-Quellen und Anfragetext in `docs/FIRMEN-ENTWUERFE.md`.

- **Plugin, Merzenich Aktuell → Firmen-Entwürfe** (`includes/firmen-entwuerfe.php`, Daten `data/firmen-entwuerfe.json`)
  - Der Knopf legt je Betrieb ein Unternehmensprofil und einen Werbebeitrag an, **nur als Entwurf**.
  - Vorhandenes (auch im Papierkorb) wird nie doppelt angelegt oder überschrieben.
  - Profile ohne Einwilligung bleiben durch die bestehende Sperre Entwurf.
- **Kostenfreier Testeintrag** (`includes/testeintrag.php`)
  - Haken im Kasten „Kennzeichnung“.
  - Die Seite schreibt „Anzeige · kostenfreier Testeintrag“ und „Kostenfreie Präsentation im Testzeitraum bis …“ statt „Bezahlte Präsentation“.
  - Der Zeitraum beginnt mit der ersten Veröffentlichung, dauert 30 Tage und endet von selbst: Ein täglicher Lauf archiviert den Beitrag. Keine Verlängerung, keine Rechnung.
- **Unternehmensprofil**
  - Neue Felder: E-Mail, Logo-Quelle, Logo-Rechte (offen/geklärt), Knopf und Knopfziel.
  - Das Logo (Beitragsbild) erscheint nur bei „geklärt“, sonst das Kürzel.
- **Kasten „Unternehmen aus der Gemeinde“** (`/unternehmen/`, `/betriebe/`)
  - Veröffentlichte echte Betriebe stehen zuerst (diese drei in fester Reihenfolge), mit Logo oder Kürzel, Branche · Ort, Kurztext und Knopf zur Website.
  - Darunter folgen die Beispielfirmen, solange sie aktiv sind, weiter als Beispielfirma gekennzeichnet.
- **Bleibt:** Startseite und Bannerplätze werden nicht belegt (`ma_startseite_freigabe = nein`); die Banner-Texte stehen nur in der Datei.

**Code-Prüfung** (drei Prüfer, jeder Befund gegengeprüft, 13 bestätigt, alle eingearbeitet):
- **Einwilligung:** Ein Werbebeitrag mit verknüpftem Unternehmensprofil bleibt Entwurf, solange am Profil die Einwilligung fehlt. Das gilt auch für die Sammelfreigabe; die Freigabeliste nennt den Grund (`ma_gate_einwilligung`, `freigabe.php`). Schon veröffentlichte Beiträge bleiben beim Bearbeiten unberührt.
- **Testzeitraum:** Er beginnt nach dem Speichern der Felder (`wp_after_insert_post`). Damit greift der Haken auch, wenn er im selben Schritt wie „Veröffentlichen“ oder nachträglich gesetzt wird.
- **Firmen-Entwürfe:**
  - Sie erkennen Inhalte in jedem Status (auch In Prüfung, Änderung, Abgelehnt).
  - Ein Fehler beim Anlegen hängt nichts an fremde Inhalte.
- **Logo:** Es erscheint nur bei „geklärt“. Ohne Angabe gilt wie auf der Profilseite „Bildrechte geprüft“.
- **Listen:**
  - Leere Listen und Suchen ohne Treffer zeigen keinen Endhinweis.
  - Die Ortsmarke bricht erst um, wenn „Merzenich · Ortsteil“ nicht in die Spalte passt, und nie nach links aus der Spalte.
- **Ressortleiste:**
  - Am Handy rückt sie nach dem Laden der Schrift erneut zum aktuellen Ressort.
  - Bei 1181 bis 1339 px hat sie auch mit Scrollleiste und im kompakten Kopf Platz für „Menschen“.
- **Kommentarformular:** Gespeicherte Angaben füllen Name und E-Mail vor. Solche Seiten gehen mit `Cache-Control: private, no-store` raus.

**Tests:** neu `testeintrag-test.php` und `firmen-entwuerfe-test.php`; erweitert `freigabe-test.php`, `endlos-test.php`, `seo-test.php`, `termine-test.php`. Lokal im WordPress bei 320 bis 1440 px nachgestellt, 0 Konsolenfehler.

**Plugin 1.28.3** (gleicher Abend): Die Seite „Firmen-Entwürfe“ meldet sich jetzt nach dem Hauptmenü an. Unter 1.28.2 fehlte sie und lieferte 403 (`firmen-entwuerfe-test.php` prüft die Reihenfolge).

**Theme 21.20.2** (gleicher Abend): Live fehlte die Wetteranzeige im Kopf. Ursache: `/api/weather.json` antwortete mit 503, weil Open-Meteo das Tageskontingent der geteilten Server-Adresse als erschöpft meldete („Daily API request limit exceeded“).
- **Ersatzquelle:** Antwortet Open-Meteo nicht, holt der Server das Wetter bei Bright Sky (`api.brightsky.dev`, Daten des Deutschen Wetterdienstes). Die Antwort wird ins Format von Open-Meteo umgerechnet, sodass `cockpit.js` und `v20.js` unverändert bleiben.
- **Letzte gute Antwort:** Fallen beide Quellen aus, gilt sie bis zu zwei Stunden weiter.
- **Pause:** Nach einem Fehlschlag wartet der Server zwei Minuten, statt jeden Aufruf sechs Sekunden warten zu lassen.
- **Grund:** Die 503-Antwort nennt ihn (`grund`).

**Nicht in diesem Stand:**
- Karten von „Weitere Meldungen“ auf der Startseite im Stil der Blöcke darüber (größerer Umbau).
- Prüfgruppe „Märkte“: nicht nachgestellt.
- Englische Alt-Texte in den Bildpools (`bildpools.json`, Sache der Pool-Pflege).

**Stand live:** eingespielt am 16.10.2026 abends, jeweils aus `node deploy/wp-pakete.mjs`:
- erst Plugin 1.28.2, dann Theme 21.20.1, danach Permalinks gespeichert;
- dann Plugin 1.28.3 (Menü „Firmen-Entwürfe“) und Theme 21.20.2 (Wetter).

Firmen-Entwürfe live angelegt: 3 Profile und 3 Werbebeiträge (IDs 4556256 bis 4556261), alle Entwurf, ohne Einwilligung.
- Alle sechs Adressen sowie `/unternehmen/baeckerei-wirtz-merzenich/` und `?p=` antworten mit 404; REST nach Adresse liefert leere Listen.
- `/unternehmen/` zeigt weiter nur die gekennzeichneten Beispielfirmen; auf der Startseite steht nichts davon.
- Die Vorschau zeigt „Anzeige · kostenfreier Testeintrag“ und den Website-Knopf mit `rel="noopener sponsored"`.

Geprüft bei 1440, 820 und 390 px auf Startseite, `/nachrichten/`, Ortsseiten, Feuerwehr, Termine, Suche (leer und mit Begriff), Unternehmen, Menschen, Sport und einer Meldung:
- 36 von 36 Aufrufen ohne Konsolenfehler, kein Überlauf, genau ein robots-Tag.
- Bildhinweise in Zeilenlisten sichtbar.
- Endhinweis auf `/ort/buergewald/` und `/menschen/`.
- Ortsmarke ohne Umbruch nach dem Punkt.
- Wetter im Kopf (über Bright Sky, solange das Open-Meteo-Kontingent erschöpft ist).

## Neu am 16.10.2026: Theme 21.20.0, Sportseite ohne Ergebnisse und Tabellen, darunter alle Sportvereine

Ergebnisse, Tabellenstände und Spielpläne hätten laufend nachgetragen werden müssen; live waren sie seit dem 05. bzw. 07.10. nicht mehr aktuell. `/sport/` zeigt deshalb nur noch die Sportmeldungen und darunter alle Sportvereine der Gemeinde.

- **Weg auf `/sport/`:** Spielstand-Ecke im Seitenkopf (Platz, Punkte, nächstes Spiel), der Kasten mit letztem und nächstem Spiel samt Tabellenauszug und „Vereinssport · Tabellenstand“. Auch die Vereinsliste in der Seitenspalte entfällt auf Seite 1, weil die Vereine jetzt unten ausführlich stehen.
- **Meldungen zuerst:** Aufmacher, Bildraster und Reihen wie bisher. Kein Endlos-Scrollen auf `/sport/`, sonst wäre die Vereinsliste nie erreichbar; stattdessen der Knopf „Ältere Sportmeldungen“ (`/sport/page/2/` usw., dort ohne Vereinsliste).
- **Neu darunter: „Sportvereine in Merzenich“** (`ma21_sport_vereine()` in `inc/sport.php`). Quelle ist das Vereinsverzeichnis im Plugin (`ma_vereine_struktur()`, Kategorie Sport): 16 Hauptvereine, gegliedert nach Fußball, Turnen und Breitensport, Tischtennis und Billard, Schießsport und weiteren Sportarten, mit Sprungleiste. Je Verein Ortsteil, Name, Sparten (Abteilungen stehen als Sparte beim Hauptverein, mit Link, wenn sie eine eigene Website haben), Vereinsprofil (wenn veröffentlicht, dann auch mit Bild, sonst Monogramm) und Website. Keine Pflegefelder wie Platz oder Punkte. Die internen Hinweistexte des Verzeichnisses erscheinen nicht.
- **Seitenkopf:** „Spielberichte und Neues aus den Sportvereinen der Gemeinde.“ (ohne „Ergebnisse“).
- **Wieder einschalten ohne neuen Code:** Design → Ausbaustufe → „Ergebnisse und Tabellen auf der Sportseite“ (Schlüssel `sport_daten`, Standard aus). Dann sieht `/sport/` aus wie bis zum 16.10., mit Endlos-Scrollen. `inc/sport.php`, die Vorlagen `sport-ecke.html`/`sport-modul.html`, das Plugin-Raster und `/api/sport-current.json` bleiben unverändert erhalten; die statische Kopie `chatgpt-site/sport/` behält die Module als Vorlage für diesen Schalter.
- **Tests:** neu `qa/wordpress/sport-vereine-test.php` (alle 16 Vereine genau einmal, Gruppen und Zahlen der Sprungleiste, Sparten, Profil und Monogramm, keine Tabellenwerte, keine Hinweistexte, Schalter aus); `ausbau-test.php` prüft fünf Schalter.
- **Enthält** die Korrekturen aus 21.19.1 (Abschnitt unten) und gehört zu Plugin 1.28.1.

- **Profiltext SC 1919:** „Spielberichte und Tabellen finden Sie im Ressort Sport“ heißt jetzt „Spielberichte finden Sie im Ressort Sport“ (Quelle `site-source/content/vereine/sc-1919-merzenich.md`; live direkt im Vereinsprofil geändert, weil der Profiltext beim Anlegen in den Beitrag übernommen wird).

**Stand live:** eingespielt am 16.10.2026 (erst Plugin 1.28.1, dann Theme 21.20.0, beide aus `node deploy/wp-pakete.mjs`, danach Permalinks gespeichert). Geprüft bei 1440, 820 und 390 px: keine Spielstand-Ecke, kein Spiel- und Tabellenmodul, kein Tabellenstand, 16 Vereine unter den Meldungen, kein Endlos-Scrollen, „Ältere Sportmeldungen“ führt auf `/sport/page/2/` (dort ohne Vereinsliste), alle 16 Vereinsprofile antworten mit 200, keine Konsolenfehler. Gegenprobe Startseite, Meldung, Termine, Unternehmen, Blaulicht bei 1440 und 390 px ohne Fehler. Schalter „Ergebnisse und Tabellen auf der Sportseite“ steht live auf aus.

## Neu am 15.10.2026 (abends): Theme 21.19.1 und Plugin 1.28.1, Korrekturen aus dem Vorführ-Check

Vor der Vorstellung beim Bürgermeister wurde die Live-Seite in sechs Breiten (1920, 1440, 1280, 1180 quer, 820 hoch, 390) Seite für Seite durchgesehen, jeder auffällige Befund unabhängig gegengeprüft. Behoben ist, was im Code liegt; die Ausbaustufe bleibt unverändert (einfache Fassung).

**Nachtrag 16.10.2026 (Code-Prüfung, im selben Versionsstand):** Karten-Link gemeindeweiter Termine sucht wieder „Merzenich“ statt „Gemeinde Merzenich“ (OSM fand nichts); „Familienanzeigen“ verschwindet aus dem Menü auch dann, wenn es nur abgelaufene oder nicht freigegebene Anzeigen gibt; „Weitere Meldungen“ auf der Startseite zeigt nur Meldungen mit Startseiten-Freigabe („Nur in der Rubrik“ bleibt draußen); Marktlisten zeigen Art bzw. Branche am Desktop in der Kopfzeile; Aufklappmenüs nennen Ortsansichten „Ortsansicht“ statt „Symbolbild“.

**Stand live:** eingespielt am 16.10.2026 zusammen mit Theme 21.20.0 (Abschnitt oben).

- **Startseite**
  - Die Rubrikflächen nehmen für große und mittlere Karten zuerst Meldungen der letzten 14 Tage, erst danach ältere mit eigenem Foto (`MA_LAYOUT_FRISCH_TAGE`, Plugin `includes/layout.php`). Vorher standen in „Politik & Gemeinde“ und „Wirtschaft“ groß fünf Meldungen vom 04.09., die vom 13.10. nur als Zeilen.
  - „Weitere Meldungen“ ist sportfrei wie die übrige Startseite (Vorgabe KBS 26.09.2026): `/nachrichten/?ma_ohne_sport=1` lässt Sport und Vereinsmeldungen mit Sportbezug weg; „Vereine & Menschen“ nutzt dieselbe Sportregel des Plugins.
  - Terminzeile „Vor Ort“: Die Punkte über Ä/Ö/Ü am Zeilenanfang werden nicht mehr abgeschnitten („Okumenischer“).
- **Feste Plätze:** Ältere Einträge ohne Einsetz-Zeitpunkt behalten beim Speichern der Karte ihre Frist (vorher verlängerte jede Änderung an einem anderen Platz sie wieder).
- **Termine:** Ein Termin, der schon begonnen hat, zeigt weiter seine Uhrzeit statt „vor 41 Min.“. Gemeindeweite Termine heißen „Gemeinde Merzenich“ statt „Merzenich“.
- **Menüs**
  - Feuerwehr und Polizei (aus dem Blaulicht-Menü) markieren Blaulicht und zeigen Start › Blaulicht › Feuerwehr, wie die Sammelseiten (`inc/rubriken.json`, Feld `themenRessort`).
  - Rote Titel zu leeren Verzeichnissen (Branchenbuch, Familienanzeigen) blendet WordPress aus wie leere Themen; sie kommen mit dem ersten Eintrag zurück.
  - Keine Meldung zweimal im Sport-Menü.
  - Symbolbilder im Menü tragen das Etikett „Symbolbild“.
  - Tablet hochkant: kein Leerraum mehr, das Menü passt auf den Bildschirm.
  - Handy-Menü liegt über dem Kopf: Logo und × sind wieder zu sehen.
- **Listen**
  - Der Aufmacher steht immer ganz da; Zeilen werden erst nach fünf statt drei Zeilen gekürzt.
  - Anrisse enden am Wortende.
  - Ein Aufmacher ohne Bild nutzt die ganze Breite.
- **Suche:** Nur Meldungen, keine Vereinsprofile und Formularseiten als „Nachrichten“.
- **Meldungsseite:** Kopf und Text stehen auf derselben Kante; rechts „Neueste Meldungen“ statt einer leeren Spalte.
- **Märkte und Seiten**
  - Stellenmarkt einspaltig: Die Art steht in der Kopfzeile, nicht mehr umbrochen („AUSBILDU/NG“).
  - Werbeband in der Inhaltsspalte statt über die ganze Breite; unter Traueranzeigen keines.
  - `/werben/`: Knopf „Werbung anfragen“ lesbar, der Auswahlkasten steht unter dem Seitentitel.
  - Sporttabelle am Handy zeigt die Punkte ohne Wischen.
- **Fuß:** „Datenschutz-Einstellungen“ steht neben Impressum und Datenschutz statt am linken Rand.
- **Ladezeit:**
  - Am 15.10. gegen 17:20 UTC brauchten einzelne erste Aufrufe 12,6 s (`/unternehmen/`) bzw. über 20 s (`/wirtschaft/`). Bei zwei späteren Messreihen, jeweils ohne und mit zufälliger Abfrage, lagen alle 15 Hauptseiten bei 0,5 bis 1 s. Die Ursache ließ sich ohne Serverzugang nicht sicher klären.
  - Behoben ist eine belegte Bremse: Dateien aus dem Repository (Startseiten-Blöcke, Spielstand, Märkte) holte der Seitenaufruf bei abgelaufenem Zwischenspeicher selbst von GitHub, mit bis zu 8 s Wartezeit je Datei. Jetzt liefert er die letzte gute Kopie sofort und frischt sie im Hintergrund auf (WP-Cron; ist die Kopie älter als dreimal die Laufzeit, wie bisher direkt).

Tests: `layout-test.php`, `endlos-test.php`, `ausbau-test.php`, `kopf-test.php`, `termine-test.php`, `ressort-test.php` erweitert; jede Korrektur zusätzlich im Browser mit den neuen Dateien auf den Live-Inhalten nachgestellt.

**Bleibt beim Betreiber (WordPress-Admin, nicht im Code):**
- Fester Platz „Bühne 1“ (Infoabend am 8. Oktober) freigeben.
- Aufmacher KGS-Schulanmeldung ab 16.10. tauschen.
- Sportergebnis eintragen.
- Entwürfe vom 14.10. freigeben (A4-Entwurf verwerfen, er galt nur für den 15.10.).
- Fußballergebnis vom festen Platz im Blaulicht-Block nehmen.
- Doppelte Flächenbrand-Meldung zusammenführen.

## Neu am 15.10.2026: Theme 21.19.0, Menü nur mit roten Titeln, Home-Knopf, Endlos-Scrollen, 3 Beispielfirmen

Wünsche des Betreibers vom 15.10.2026 nach Durchsicht des Live-Menüs. Alles steckt im Theme; das Plugin bleibt 1.28.0. Theme 21.19.0 enthält 21.18.0 (Abschnitt unten). Hochladen wie dort beschrieben: erst Plugin 1.28.0, dann Theme 21.19.0, beide aus `node deploy/wp-pakete.mjs`. Danach einmal Einstellungen → Permalinks speichern (die neuen Adressen `/rubrik/<slug>/` schreibt das Theme beim ersten Aufruf zwar selbst ein, sicher ist der Klick trotzdem).

**Stand live: eingespielt am 15.10.2026** über das Konto „HK Growth“ (Pakete aus `node deploy/wp-pakete.mjs`, Stand Commit 3da5898e, QA grün): Plugin 1.27.4 → 1.28.0, Theme 21.17.1 → 21.19.0, danach Permalinks gespeichert. Live geprüft:
- Seitenquelltext `ver=21.19.0` und `ver=1.28.0`; Startseite und Listen ohne PHP-Fehler.
- `/assets/ressort-menue.json` nur mit roten Titeln (Blaulicht: Feuerwehr, Polizei, Verkehr), ohne Vereine, Unterpunkte und Anzeige; Termine mit 5 Ortsteilen; 3 Beispielfirmen.
- `/rubrik/verkehr/` 200 mit `noindex,follow` (Meta und `X-Robots-Tag`); `/blaulicht/page/2/?ma_endlos=1` liefert 13 Meldungen als Teilstück (`no-cache`), hinter der letzten Seite 404.
- `/unternehmen/backhaus-morgenrot/` 200 mit `noindex`, alte `/unternehmen/muster-backstube/` 404; keine Beispielfirma in Sitemap und Feed.
- `/termine/?ort=golzheim` zeigt gemeindeweite Termine (Allerheiligen).
- Browser (Playwright): Endlos-Scrollen auf `/blaulicht/` 13 → 68 Meldungen ohne Doppelte, Zurück-Taste an dieselbe Stelle; Home-Knopf, rote Titel, Uhrzeit in Ortszeit auch bei Browser-Zeitzone UTC, Startseite „Weitere Meldungen“ ohne Doppelte, Handy-Home-Knopf; Unternehmen-Menü mit 3 Firmen, Firma 2 zeigt 3 Beiträge mit Bild.
- Design → Ausbaustufe vorhanden, Standard: einfache Fassung (nur Endlos-Scrollen an).

Sitzungsdatei danach gelöscht; das Passwort des Kontos sollte geändert werden.

**Nichts ist gelöscht (Ausbaustufe, Wunsch Betreiber 15.10.2026):** Die einfache Fassung ist für die Vorstellung beim Bürgermeister gedacht. Alles, was dafür ausgeblendet wurde, bleibt im Code und in den Daten gespeichert und lässt sich im Backend unter **Design → Ausbaustufe** einzeln wieder einschalten, ohne neues Theme (`inc/ausbau.php`, Option `ma21_ausbau`; die Menüs übernehmen es spätestens nach 5 Minuten):

| Schalter | Standard | Eingeschaltet kommt zurück (Stand 14.10.2026) | Gespeichert in |
|---|---|---|---|
| Unterpunkte in den Aufklappmenüs | aus | die 114 kleinen Links in ihren Gruppen, Themen ohne Meldung blendet WordPress wie damals aus (z. B. Blaulicht › Rettung & Hilfe, Diebstahl; Sport › SC 1919: Tabelle & Spielplan; Termine › Ortsfest), mit Vereinen auch die Anzeige im Vereine-Menü | `deploy/ressort-menue.json`, je Ressort `voll` |
| Vereine in der Ressortleiste | aus | Vereine in Leiste, „Mehr“ und Handy-Menü, mit eigenem Aufklappmenü | Ressort `/vereine/` mit `ausbau`; Links mit `data-ausbau="vereine"` (`deploy/kopf-theme-einbinden.mjs`) |
| Endlos-Scrollen | an | ausgeschaltet: Blättern mit Seite 1, 2, keine „Weitere Meldungen“ auf der Startseite | `inc/ma21.php`, `assets/endlos.js` |
| Fünf Musterfirmen statt drei Beispielfirmen | aus | Muster-Backstube, Muster-Café am Markt, Muster-Schreinerei, Muster-Steuerbüro, Muster-Immobilien mit allen Beiträgen, Anzeigen und Öffnungszeiten (gekennzeichnet wie alle Musterfirmen als „Beispielfirma“, früher „Musterprofil“) | `inc/unternehmen-muster.json`, `frueher` |

Der Werbeplatz „Menü „Vereine“ aufgeklappt – Fläche rechts“ (Werbung → Werbeplätze) bleibt buchbar, Anzeigen darauf erscheinen aber nur, wenn „Unterpunkte“ und „Vereine“ beide an sind. Der Generator prüft auch die ausgeblendeten Links bei jedem Lauf; `qa/wordpress/ausbau-test.php` vergleicht die gespeicherten Daten mit dem Stand vom 14.10.2026 (Commit 7c56c388) und prüft jeden Schalter. Die statische Kopie zeigt immer die einfache Fassung.

- **Menü: nur rote Titel.** Jedes Aufklappmenü zeigt nur noch die roten Titel, die kleinen Unterpunkte (Diebstahl, Einbruch, Brände …) sind ausgeblendet (Schalter oben). Ein Klick auf einen Titel zeigt alle Meldungen dazu. Gibt es eine passende Seite, führt der Titel dorthin (Polizei → `/thema/polizei/`, das alle Polizeimeldungen trägt), sonst auf eine neue Sammelseite `/rubrik/<slug>/` mit allen Meldungen der bisherigen Unterpunkte. Titel ohne Meldung blendet WordPress aus.
  - Blaulicht: Feuerwehr, Polizei, Verkehr. Sport: Vereine, Fußball, Weitere („SC 1919: Tabelle & Spielplan“ ist ausgeblendet). Termine: Alle Termine und die 5 Ortsteile (`/termine/?ort=golzheim` zeigt nur Golzheim und gemeindeweite Termine). Rathaus & Politik: Gemeinde, Geld & Planung, Beteiligung. Leben, Wirtschaft, Menschen und Tipp entsprechend.
  - Sammelseiten (12): Verkehr, Sportvereine, Weitere Sportarten, Gemeinde, Geld & Planung, Beteiligung, Familie & Schule, Natur & Umwelt, Freizeit & Mobilität, Strukturwandel, Infrastruktur, Aus der Gemeinde. Sie stehen auf `noindex,follow`, damit keine Doppelinhalte entstehen; Suchmaschinen finden weiter die Themenseiten. Brotkrumen Start › Ressort › Titel, im Kopf ist ihr Ressort markiert.
  - Daten: `deploy/ressort-menue.json` (`punkte`), daraus `deploy/rubrik-prerender.mjs` und `inc/rubriken.json` (Route und Abfrage in `inc/ma21.php`).
  - Termine im Menü zeigen die Uhrzeit fest in deutscher Ortszeit. In Browsern mit anderer Zeitzone stand vorher z. B. 16:00 statt 18:00.
- **Leiste:** „Vereine“ ist in Leiste, Handy-Menü und „Mehr“ ausgeblendet (Schalter oben; Seite `/vereine/` und Fußlink bleiben immer), Sport bleibt. Neu links neben „Aktuell“ ein Home-Knopf (Haus-Symbol), der wie das Logo zur Startseite führt; auf der Startseite ist er markiert. Impressum und Datenschutz stehen zusätzlich in „Mehr“, weil der Fuß durch das Endlos-Scrollen weiter nach hinten rückt.
- **Endlos-Scrollen statt Seite 1, 2:** Ressorts, Aktuell, Ortsteile, Themen, Sammelseiten, Suche und Autorenseiten laden kurz vor dem Listenende die nächsten Meldungen nach, ohne Obergrenze, bis alle da sind („Alle Meldungen geladen“ mit Link ins Archiv). Auf der Startseite folgen nach den Blöcken „Weitere Meldungen“; was oben schon steht, kommt nicht noch einmal. Termine, Märkte und Unternehmen bleiben wie sie sind.
  - Technik: `assets/endlos.js`; dieselbe Listenadresse mit `?ma_endlos=1` liefert nur die Meldungen der Seite (`noindex`). `/…/page/N/` bleibt für Suchmaschinen und Leser ohne JavaScript, mit `rel="next"`/`"prev"`.
  - Keine Meldung doppelt, Anzeigen im Kartenraster immer am Zeilenanfang, nachgeladene Anzeigen wechseln mit. Die Zurück-Taste führt an dieselbe Stelle (nachgeladene Meldungen werden 30 Minuten in der Sitzung gemerkt). Knopf „Weitere Meldungen laden“ als Rückfall, Ansage für Bildschirmleser. Im Bearbeitungsmodus der Redaktion ist das Nachladen aus.
- **Unternehmen: 3 Beispielfirmen** statt der fünf Musterfirmen (diese bleiben gespeichert, Schalter oben), mit Namen wie echte Betriebe: Backhaus Morgenrot (Bäckerei, Golzheim), Café Feldlerche (Café, Merzenich), Holzwerkstatt Jahresring (Handwerk, Girbelsrath), je drei Beiträge mit Bild. Maus auf eine Firma zeigt sofort ihre drei Beiträge. Damit niemand sie für echt hält: Kennzeichen „Beispielfirma“ neben jedem Namen, „Musterbeitrag“ an jedem Beitrag, Hinweis „Es sind keine echten Unternehmen“, keine Öffnungszeiten, Adressen oder Telefonnummern, `noindex`. Die Namen wurden vorher in Branchenverzeichnissen und im Web gesucht (Feld `pruefung` in `inc/unternehmen-muster.json`). Ab drei echten Firmenkanälen verschwinden sie wie bisher.
- **Kleinigkeiten:** Zeilen ohne Bild stehen in WordPress über die volle Breite (vorher Text in der schmalen Bildspalte), Karten ohne Bild auf dem Handy ebenso. Verwaiste statische Seiten `/merzenich/seite/2/` und `/3/` sind aufgeräumt.
- **Tests:** neu `qa/wordpress/endlos-test.php` und `qa/wordpress/ausbau-test.php`; erweitert `ressort-test.php`, `kopf-test.php`, `unternehmen-test.php`, `qa/pruefung.mjs` (rote Titel, Home-Knopf, keine Vereine in der Leiste, `endlos.js` auf jeder Seite, Sammelseiten `noindex`) und `qa/routen.mjs` (Seitentyp `rubrik`). In `qa.yml` Browserprüfung: Blaulicht-Menü genau Feuerwehr, Polizei, Verkehr in Rot und jedes Ziel mit Meldungen, Termine-Menü und Ortsfilter, Uhrzeit bei Browser-Zeitzone UTC, Home-Knopf auf 1440 und 390 px, Endlos-Scrollen auf `/nachrichten/` und der Startseite samt Zurück-Taste, genau 3 Firmen und 3 Beiträge mit geladenem Bild.


## Neu am 13.10.2026: Theme 21.18.0 / Plugin 1.28.0, Unternehmen mit Firmenkanälen (Vorbild Oberberg Aktuell)

Auftrag des Betreibers vom 13.10.2026, drei Punkte. Beide Pakete hochladen; die ZIPs kommen aus `node deploy/wp-pakete.mjs` (nur dieser Bau enthält `static/`).

**Stand live:** mit Theme 21.19.0 und Plugin 1.28.0 am 15.10.2026 eingespielt, Permalinks gespeichert (Abschnitt oben).

- **Anzeige im Menü „Unternehmen“ entfernt:** Der Block „Anzeige“ mit großem Bild unter „Für Unternehmen“ ist weg (`ma21_ressort_menue_daten`, `deploy/ressort-menue.json`). Die beiden Linkgruppen stehen jetzt untereinander links, daneben die Firmenliste, rechts wie bisher die Firmenbeiträge. Es bleibt keine leere Fläche.
- **Firmen im Menü, Beiträge beim Überfahren:** Links stehen die Unternehmen (Kürzel, Name, Branche · Ort, bei Musterfirmen „Musterprofil“). Bleibt die Maus kurz auf einer Firma (160 ms), zeigt der rechte Bereich ihre neuesten Beiträge und „Zum Unternehmenskanal“. Wer nur darüber streicht oder schräg zu den Beiträgen zieht, behält die gewählte Firma. Klick auf die Firma öffnet ihren Kanal.
  - Verlässt die Maus das Menü kurz, schließt es erst nach 400 ms. Kommt sie vorher zurück, bleibt es offen. Bisher klappte es trotz Rückkehr zu, weil zwei Zeitgeber liefen.
  - Tastatur: Fokus zeigt die Beiträge, Pfeiltasten wandern, Escape schließt.
  - Tablet quer: Tippen öffnet das Menü, Tippen auf eine Firma zeigt ihre Beiträge, ein zweiter Tipp öffnet den Kanal. Unter etwa 1180 px steht „Unternehmen“ wie bisher unter „Mehr“.
  - Handy: Tippen auf „Unternehmen“ öffnet die Menüspalte mit den Firmen; je Firma klappen die Beiträge auf.
- **Unternehmenskanäle** `/unternehmen/<firma>/` (`inc/unternehmen.php`, `unternehmen-kanal.php`): Profil, Beiträge, Anzeige der Firma, weitere Kanäle. `/unternehmen/` zeigt oben die Kanäle als Kacheln, darunter die Beiträge.
- **Musterfirmen mit vollständigen Seiten:** Muster-Backstube, Muster-Café am Markt, Muster-Schreinerei, Muster-Steuerbüro und Muster-Immobilien (`inc/unternehmen-muster.json`) mit je drei Musterbeiträgen und einer Testanzeige. Jeder Musterbeitrag öffnet eine ganze Seite (`/unternehmen/<firma>/<beitrag>/`, `unternehmen-beitrag.php`); bisher führten die Karten nur auf das Anfrageformular.
  - Überall gekennzeichnet als Musterprofil, Musterbeitrag oder Testanzeige, „kein echtes Unternehmen“. Die Musterseiten haben `noindex` (Meta und `X-Robots-Tag`) und stehen in keiner Sitemap, keinem Feed und keiner Suche, weil sie nicht in WordPress gespeichert sind.
  - Ab drei echten Firmen mit veröffentlichten Beiträgen verschwinden sie; ihre Adressen geben dann 404.
  - Fotos: zusätzliche frei lizenzierte Commons-Fotos (`deploy/werben-bilder.json`, Lizenz und Urheber vor der Aufnahme einzeln geprüft), Nachweis mit Urheber und Lizenzlink an jedem Bild.
- **Firmenzugang (vorhandene Rolle Unternehmens-Partner, kein neues System, `includes/unternehmenskanal.php`):** Benutzer → Partner-Zugänge verknüpft den Zugang mit einem Unternehmensprofil (neu als Entwurf oder vorhanden), für bestehende Zugänge am Benutzerprofil. Beiträge, Anzeigen und Tipps des Zugangs kommen beim Speichern automatisch in den Kanal (`ma_business_id`). Die Zuordnung kann der Zugang nicht selbst ändern; die Redaktion setzt sie im Kasten „Kennzeichnung“. Bearbeiten wie bisher nur eigene, nicht veröffentlichte Inhalte; das veröffentlichte Profil über „Änderung vorschlagen“. Diese übernimmt jetzt auch Profilfelder und Einwilligung; vorher gingen diese Änderungen verloren.
- **Kleinere Korrekturen:** Karten auf `/unternehmen/` zeigen bei Anzeigen „Anzeige · Firma“ statt „Redaktion“; Beiträge einer Firma verlinken oben auf ihren Kanal; „Unternehmen“ ist im Kopf auch auf Kanal- und Musterseiten markiert. `seo.php` hat den Filter `ma_seo_kontext` für Titel, Adresse und Index dieser Seiten.
- **Nach dem Review (gleiche Versionen, noch vor dem Einspielen):**
  - Rechte: Die Redaktionsfelder, darunter die Kanalzuordnung, kann ein Partner auch über „Individuelle Felder“, admin-ajax und XML-RPC nicht mehr ändern. Speichern eines Benutzerprofils oder Beitrags ohne Änderung löscht die Verknüpfung nicht mehr, auch wenn das Profil gerade „In Prüfung“ ist.
  - Kennzeichnung: Im Kanal stehen nur gesponserte Beiträge (ein redaktioneller Bericht über eine Firma erscheint dort nicht als Anzeige). Musterbeiträge heißen überall „Musterbeitrag“, nie „Anzeige“; echte Kanalbeiträge „Anzeige“, auch in der Handy-Menüspalte. Gesponserte Beiträge stehen nicht als „Neu“ im Kopf und tragen im Menü, im RSS-Feed und in `/api/latest.json` „Anzeige“.
  - Bildnachweise im Menü mit Urheber und Lizenzlink, auf allen Breiten sichtbar. Bilder echter Firmenbeiträge laden richtig (vorher doppelte Domain in der Adresse).
  - Bedienung: Der gerade Weg von „Unternehmen“ zu den Firmen schaltet nicht mehr auf „Wirtschaft“ um. Steht die Maus auf einer Firma, wechselt die Ansicht sofort. Tab führt zu den Beiträgen der gewählten Firma. Am Handy rutscht „Unternehmen“ nicht mehr unter den Seitenkopf.
  - Keine leere Fläche unter „Für Unternehmen“: Die linke Spalte reicht bis zur Unterkante der Karten, „Für Unternehmen“ schließt dort als Kasten ab.
  - Ein echtes Profil bekommt keine Adresse einer Musterfirma; ist sie schon belegt, verschwindet die Musterfirma.
  - Vier Musterbeiträge (Schreinerei: Ausbildung und Treppe, Steuerbüro: Beratung per Video, Immobilien: Schlüsselübergabe) haben eigene Fotos statt Werbemotiven. `deploy/werben-bilder.mjs` kann dafür Ausschnitte setzen und Urheber und Beschreibung überschreiben.
- **Tests:** `qa/wordpress/unternehmen-test.php`, `qa/wordpress/unternehmenskanal-test.php`, `kopf-test.php`, `ladegewicht-test.php`; in `qa.yml` Browserprüfung des Unternehmen-Menüs (Desktop, Tablet quer und hochkant, Handy), dazu Lücke an der Panelunterkante, Kennzeichnung, Bildnachweise und der Weg von „Unternehmen“ zu den Firmen.
- **Hinweis:** Eine Firmenliste im Menü gab es schon in Theme 21.10.5. Sie wurde am 07.10. auf Wunsch des Betreibers entfernt; der Auftrag vom 13.10. holt sie zurück.

## Neu am 13.10.2026: Plugin 1.27.3 und 1.27.4, Theme 21.17.1, Feuerwehr-Automatik (live eingespielt)

Plugin 1.27.4 und Theme 21.17.1 sind auf merzenich-aktuell.de eingespielt (Konto „HK Growth“, Plugins bzw. Design → hochladen → „Aktuelle Version ersetzen“). Geprüft: Seitenquelltext `ver=1.27.4`/`ver=21.17.1`, Startseite 200 ohne PHP-Fehler auf Handy und Desktop, keine 4xx/5xx beim Laden, `/wp-json/wp/v2/posts` mit `cache-control: public, max-age=300`, unbekannte REST-Route mit `no-store`. Enthalten sind 1.27.1 (REST-Speicherzeit) und 1.27.2 (feste Plätze laufen nach 7 Tagen aus).

- **Abgleich lässt Prüfhäkchen stehen (1.27.3):** Beim Aktualisieren eines bestehenden Beitrags schrieb der Abgleich die Import-Werte `ma_date_verified`, `ma_place_verified`, `ma_human_reviewed` = `'0'`. Eine veröffentlichte Meldung fiel dadurch beim nächsten Speichern auf Entwurf zurück (Veröffentlichungssperre). `ma_abgleich_meta_fuer_update()` lässt diese Häkchen und die Startseiten-Freigabe beim Aktualisieren weg.
- **Reparatur und Bildrechte (1.27.4):** Einmalig beim ersten Backend-Aufruf bekommen veröffentlichte Abgleich-Beiträge die fehlenden Häkchen zurück, mit Eintrag „Prüfhäkchen wiederhergestellt“ im Verlauf (live geprüft an der Herbstlaub-Meldung, die sie verloren hatte). `ma_image_rights_verified` wird beim Aktualisieren nur noch geschrieben, wenn die Import-Datei `'1'` trägt; eine bestätigte 1 wird nie herabgesetzt. Tests: `qa/wordpress/abgleich-test.php`.
- **Theme 21.17.1:** gleicher Code, aktuelle Vorlagen (u. a. Cache-Buster für `kopf.js`). Gebaut mit `node deploy/wp-pakete.mjs`, weil nur dieses Paket `static/` enthält; das CI-Zip hat es nicht und würde beim Ersetzen die Assets löschen.
- **Feuerwehr-Automatik:** `deploy/automatik-feuerwehr.mjs` und `.github/workflows/meldungen-automatik.yml` legen neue Einsätze nach dem täglichen Quellenabruf als Meldung an; sie kommen über den Abgleich als Entwurf in die Freigaben. Erster Lauf 13.10.: Einsatz 135/26 (Girbelsrath), 136/26 (Merzenich, mit beiden Datumsangaben der Quellen) und 138/26 (Merzenich) stehen in den Freigaben. Regeln und Grenzen: `RECHERCHE-QUELLEN.md`, Abschnitt „Automatik Feuerwehr“.
- **Paketbau:** `build-wordpress-packages.yml` baut bei abgelehntem Push auf frischem `main` neu (bis vier Versuche). Am 12.10. war das Zip deshalb auf 1.27.1 stehen geblieben.
- **ChatGPT-Recherche:** ChatGPT liefert die Morgenrecherche als Markdown-Bericht, nicht als Commit; der letzte Inhalts-Commit von ChatGPT ist vom 06.10. Wie die Berichte ins Repo kommen: `CHATGPT-MORGENUPDATE.md`.

## Neu in Plugin 1.27.2 (12.10.2026): feste Startseitenplätze laufen aus

Nur das Plugin-Paket hochladen (`wordpress-delivery/merzenich-aktuell-core.zip`); das Theme bleibt 21.17.0. Enthält auch 1.27.1 (REST-Speicherzeit).

Vorgabe Betreiber (12.10.2026): Neue Meldungen sollen oben erscheinen, nicht dauerhaft dieselben. Die Automatik tut das schon (Relevanz minus Alter, „frisch“ für Aufmacher und Bühne), aber feste Plätze aus dem Board (Startseite & Ressorts) galten bisher unbefristet: Am 12.10. standen auf Bühne 2 und 4 fest gesetzte Meldungen vom 25.09. und 04.10.

- **Feste Plätze enden nach 7 Tagen** (`includes/layout.php`): Jeder feste Platz merkt sich den Zeitpunkt des Einsetzens (`seit`). Ohne „Hervorhebung bis“ endet er nach der Frist, dann füllt die Automatik den Platz, mit Eintrag im Verlauf („Fester Platz abgelaufen (7 Tage): …“). Die Frist beginnt mit dem Einsetzen, bei vorgemerkten Entwürfen mit der Veröffentlichung. „Nochmal einsetzen“, Tauschen und Verschieben bestätigen den Platz neu. Ein ausdrückliches „Hervorhebung bis“ im Beitrag geht vor (auch länger als die Frist).
- **Einstellung:** Freigaben → Einstellungen der Platzierung → „Feste Plätze enden nach (Tage, 0 = unbefristet)“, Standard 7, höchstens 60. Mit 0 verhält sich alles wie vor 1.27.2.
- **Bestand nach dem Update:** Plätze, die vor 1.27.2 gesetzt wurden, haben keinen Zeitpunkt; für sie zählt der Stand der Karte (letzte Änderung im Board). Ist der älter als 7 Tage, gehen diese Plätze beim ersten Aufruf nach dem Update an die Automatik. Wer eine Meldung oben halten will, setzt sie im Board neu ein oder trägt „Hervorhebung bis“ ein.
- **Board/REST (`ma/v1/layout`):** je Platz das Feld `bis` (Zeitpunkt, 0 = unbefristet) für eine spätere Anzeige; die Oberfläche zeigt es noch nicht.
- Test: `qa/wordpress/layout-test.php`, Abschnitt „Feste Plätze laufen aus“.

**Was sonst für „immer Neues oben“ nötig ist (keine Technik):** Seit dem 06.10. ist keine neue Meldung veröffentlicht; der tägliche Lauf „Quellen abrufen“ legt nur Rohdaten unter `imports/quellen/` ab und schreibt keine Meldungen. Neue Meldungen entstehen erst redaktionell (`inhalte/meldungen/`), landen über den stündlichen Abgleich in den Freigaben und brauchen dort Veröffentlichung plus Startseiten-Freigabe. Aufmacher braucht ein echtes Foto (kein Ortsbild) mit mindestens 480 px Breite.

## Neu in Plugin 1.27.1 (12.10.2026): REST-Antworten kurz gespeichert, CI repariert, alte Vorschauadresse umleiten

Nur das Plugin-Paket hochladen (`wordpress-delivery/merzenich-aktuell-core.zip`); das Theme bleibt 21.17.0. Ohne das Einspielen bleibt die 28-Tage-Speicherzeit auf `/wp-json/` bestehen.

- **REST-Antworten (`/wp-json/…`) nur noch fünf Minuten gespeichert** (`includes/rest-cache.php`): Der Server bei IONOS gab anonymen REST-Antworten 28 Tage Speicherzeit mit (`Cache-Control: max-age=2419200`, `Expires` vier Wochen voraus; mod_expires für JSON), weil WordPress dort keine eigenen Kopfzeilen setzt. `/api/latest.json`, die Feeds und `/assets/ressort-menue.json` hatten schon eigene Angaben. Jetzt: anonymes GET/HEAD `public, max-age=300` mit passendem `Expires`, alle anderen Methoden `no-store`. Angemeldete Benutzer (WordPress setzt no-cache) und Endpunkte mit eigener Angabe (`ma/v1/live`, `ma/v1/layout`) bleiben unverändert. Prüfung nach dem Einspielen: `curl -sD - -o /dev/null 'https://merzenich-aktuell.de/wp-json/wp/v2/posts?per_page=1'` muss `cache-control: public, max-age=300` zeigen. Test: `qa/wordpress/rest-cache-test.php`.
- **CI „Materialize Werben Images“** lief seit dem 06.10. rot: `deploy/werben-bilder.mjs` verkleinert mit ImageMagick (`convert`), das auf den ubuntu-24.04-Runnern nicht mehr vorinstalliert ist („spawnSync convert ENOENT“). Der Workflow installiert ImageMagick jetzt vor dem Lauf.
- **Theme-Vorlagen im CI nachgezogen:** `qa.yml` committet `wordpress/theme/merzenich-aktuell/vorlagen/` (erzeugt von `deploy/wp-theme.mjs` in der Kette) jetzt mit. Bisher blieben sie auf dem Runner liegen, und das Theme-Zip aus `build-wordpress-packages.yml` trug alte Vorlagen. Die Pakete werden nicht täglich neu gebaut (die Startseiten-Vorlage wechselt jeden Tag mit dem Foto des Tages, das wäre täglich ein neues Zip im Repo); `build-wordpress-packages.yml` läuft bei jedem Theme-/Plugin-Push und auf Wunsch per workflow_dispatch und nimmt dann die aktuellen Vorlagen mit.
- **Alte Vorschauadresse `merzenichaktuell.hk-growthoperator.de`:** antwortet seit der Abschaltung mit 503 (DNS zeigt weiter auf den Coolify-Server, die Anwendung ist gestoppt). Im Repo liegt ein Umleitungs-Image (`deploy/coolify/Dockerfile.umleitung`, `umleitung.conf`): jede Anfrage bekommt 301 auf `https://merzenich-aktuell.de` mit gleichem Pfad und gleicher Abfrage. Die QA baut und prüft es bei jedem Lauf. **Betreiber:** in Coolify bei der alten Anwendung „Dockerfile Location“ auf `/deploy/coolify/Dockerfile.umleitung` stellen und neu deployen (Anleitung `deploy/coolify/README.md`), oder den DNS-Eintrag löschen. Von hier aus nicht möglich (kein Coolify-Zugang). Adressen in den User-Agent-Kennungen der Abrufskripte (`markt-abgleich.mjs`, `werben-bilder.mjs`, `quellen-abruf.mjs`) zeigen auf `merzenich-aktuell.de`.
- **Doku nachgezogen:** Rechtstexte-Fassung 2026-10-06 mit § 18-Person (Hinweis beim Eintrag 1.15.0), Bildzuschnitte vorhanden (`GOOGLE-NEWS.md`), Importer lädt seit 21.10.1 über `liveUrl()` (Hinweis beim Eintrag zum Import).

## Neu in Theme 21.17.0 / Plugin 1.27.0 (07.10.2026): bei Google gefunden werden

Beide Pakete hochladen und danach einmal das Backend öffnen. Dabei legt das Plugin die Seite „Heute in Merzenich“ an. Hintergrund und Schritte im Google-Konto: `GOOGLE-NEWS.md`, Abschnitt „Gefunden werden“.

- **Heute in Merzenich** (`/heute/`): Termine von heute und den nächsten sieben Tagen, dazu die neuesten Meldungen.
  - Bei Google heißt die Seite „Was ist heute in Merzenich los?“, die Frage, die Google selbst unter „Weitere Fragen“ zeigt.
  - Die Seite legt das Plugin an (`includes/seiten.php`). Die Ausgabe kommt aus dem Theme (`page-heute.php`, `ma21_heute_seite()` in `inc/termine.php`).
  - Verlinkt im Mehr-Menü, in der Schublade (Service), im Fuß nach „Termine“, über „Merzenich jetzt“ auf der Startseite und auf `/termine/`. Die Links erscheinen erst, wenn die Seite angelegt ist.
- **Startseite:** Die einzige H1 ist das Logo, Alt-Text „Merzenich Aktuell – Nachrichten aus der Gemeinde Merzenich“. Der Aufmacher ist eine H2 und sieht unverändert aus (Bildschirmfoto-Vergleich Handy und Desktop).
- **Strukturdaten:** Logo mit den echten Maßen 720 × 203 statt 1200 × 338. `WebSite` mit `alternateName` „Merzenich-Aktuell“ und „merzenich-aktuell.de“.
- **Sitemap:** Neue Sitemap `wp-sitemap-bereiche-1.xml` mit `/nachrichten/`, `/termine/` und `/unternehmen/`. Diese drei Seiten standen bisher in keiner Sitemap.
- **Mustertexte** für Links aus dem Ort: `docs/vorlagen/verlinkung-gemeinde.md`, `verlinkung-verein.md`, `teilen-lokale-gruppen.md`. Die Anleitung für KBS enthält die ersten beiden.

## Bedienungsanleitung für KBS als PDF (07.10.2026)

- `docs/Merzenich-Aktuell-Bedienungsanleitung-KBS.pdf`, 27 Seiten mit Inhaltsverzeichnis und 15 Bildschirmfotos aus dem Live-Backend (nur gelesen, Namen und Adressen unkenntlich).
- Teil A für die Redaktion (Rolle „Redakteur“), Teil B für Administratoren, Anhang mit Regeln, Hilfe bei Problemen, Begriffen, Mustermails und Kontakten.
- Quelle `docs/UEBERGABE-KBS.md`, neu bauen mit `deploy/anleitung-pdf.mjs` (siehe `README-INSTALLATION.md`).
- Mustermails in `docs/vorlagen/`: Das Passwort wird persönlich oder telefonisch übergeben, WordPress schickt keine Mail.

## Neu in Plugin 1.26.3 und 1.26.4 (07.10.2026): Texte und Beiträge-Liste für die Anleitung

- Sport und Partner-Zugänge: Hinweistexte ohne Entwicklerbegriffe (kein `/api/…`, kein Shortcode).
- Editor und Ortsseiten: Der Hinweis auf eine „Ersatzgrafik“ ist weg. Ohne geprüfte Bildrechte erscheint kein Bild.
- Beiträge-Liste: Die Relevanz-Auswahl ragte in die Nachbarspalten. Spalten Titel, Startseite und Relevanz haben jetzt feste Breiten.
- Nur das Plugin hochladen.

## Neu in Plugin 1.26.2 (07.10.2026): Redakteure, Schlagwörter im Abgleich, Kleinigkeiten für die Anleitung

Nur das Plugin hochladen.

- **Redakteure** (Rolle „Redakteur“, z. B. Tivi und Ordin) öffnen jetzt auch **Sport** und **Abgleich** („Jetzt abgleichen“, Stellen und Immobilien übernehmen). Die Einstellungen des Abgleichs sehen und ändern weiter nur Administratoren. Der Sport-Hinweis im Dashboard erscheint auch für Redakteure.
- **Schlagwörter:** Der Abgleich legte bei neuen oder aktualisierten Meldungen die zusammengeführten Schlagwörter wieder an (z. B. „Golzheim“). Jetzt entfallen Ressorts und Ortsteile als Schlagwort, Unterbegriffe landen im Oberbegriff (`ma_schlagwort_abbilden`).
- **„Heute zu tun“:** Die Kachel „Anzeigen enden in 7 Tagen“ zählt jetzt auch Werbung (Ende in `ma_ad_end`); „Ansehen“ öffnet die passende Liste.
- **Ein Name je Haken:** Der Freigabe-Kasten sagt „Redaktionelle Prüfung abgeschlossen“ wie die Checkbox, bei Anzeigen „Freigabe dokumentiert“.
- **Tipps** haben den Freigabe-Kasten und den Hinweis, wenn „Freigabe dokumentiert“ fehlt. Die Sperre gilt auch für geplante Anzeigen.
- **Grundsätze:** Statt „klar gekennzeichnete Grafik“ steht dort jetzt, dass eine Meldung ohne passendes Foto ohne Bild erscheint (Fassung 2026-10-07-2).
- **Sport-Seite:** kein Hinweis mehr auf das Repository.

## Neu in Plugin 1.26.1 (07.10.2026): Autoren-Sitemap erst mit Einträgen

Solange noch keine Person und kein Partner eine veröffentlichte Meldung hat, ist die Autoren-Sitemap leer. WordPress beantwortet eine leere Sitemap mit 404, und die Search Console hätte das als Fehler gemeldet. Die Sitemap steht jetzt erst im Verzeichnis `wp-sitemap.xml`, wenn sie Einträge hat. Nur das Plugin hochladen.

Dazu kommt die Sichtprüfung Runde 19: 8 nachgeladene Fotos derselben Serien sind ausgeschlossen. Der Importer sperrt diese Serien jetzt selbst (`docs/POOLFOTOS-PRUEFUNG.md`).

## Neu in Theme 21.16.0 / Plugin 1.26.0 (07.10.2026): echte Autoren, Schlagwörter aufgeräumt, Google per API

Beide Pakete hochladen und danach einmal das Backend öffnen. Dabei werden die doppelten Schlagwörter einmalig zusammengeführt.

- **Echte Autoren** (`includes/autoren.php`):
  - **Wer mit Namen erscheint:**
    - **Personen der Redaktion:** Konten mit dem Häkchen „Als Autor zeigen“ unter Benutzer → Profil. Nur Admins setzen es. Gezeigt wird „Öffentlich anzeigen als“, dazu das Feld „Funktion“ (z. B. Redakteur) und die „Biografischen Angaben“.
    - **Partner und Vereine:** Polizei, Feuerwehr, Gemeinde, Vereine, Unternehmen und Anbieter erscheinen mit ihrem Namen, ohne „(Partner)“, mit Funktion (Polizei, Gemeinde, Verein …) und dem Zusatz „geprüft von der Redaktion“.
    - **Sonst:** „Redaktion Merzenich Aktuell“ wie bisher. Das gilt für die Sammelkonten (admin, HK Growth, KBS) und für alle alten Meldungen.
  - **Freigabe setzt den Autor:** Gibt eine Person der Redaktion einen Entwurf frei (Entwurf, ausstehend, In Prüfung, Änderung → veröffentlicht), wird sie Autorin der Meldung. Das gilt nicht für Meldungen von Partnern und anderen Personen, nicht für Aktualisierungen schon veröffentlichter Meldungen und nicht für geplante Meldungen. Die Meldung wird dabei nicht erneut gespeichert, der Verlauf vermerkt den Wechsel.
  - **Autorenseiten** `/autor/<name>/`: Name, Funktion, Vorstellung, Link zum Vereinsprofil und die Meldungen. Unbekannte Namen geben 404. Die Seiten von WordPress mit Anmeldenamen (`/author/…`) leiten weiter auf `/redaktion/`. Dort steht unter dem Text die Liste der Redaktion.
  - **Für Google:** NewsArticle nennt die Person (Person mit url, jobTitle, worksFor) bzw. die Organisation. Die Autorenseite ist eine ProfilePage. Die neue Sitemap `wp-sitemap-autoren-1.xml` führt die Autorenseiten. Im Feed steht der Name in `dc:creator`.
  - **Theme:** Autorzeile und Autorenkasten in `single.php` zeigen Initialen, ohne Gravatar (keine Verbindung zu Dritten). Neue Vorlage `autor.php`.
- **Schlagwörter aufgeräumt** (`includes/schlagwoerter.php`, läuft einmal beim ersten Backend-Aufruf):
  - Die Schlagwörter Blaulicht, Sport und Rathaus entfallen, ihre Ressortseiten ersetzen sie.
  - Die Ortsteile Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald entfallen als Schlagwort, `/ort/<ort>/` ersetzt sie. Meldungen ohne Ortsangabe bekommen den Ortsteil.
  - Grundschule → Schule, Jugendfußball → Fußball, Seniorennachmittag → Senioren, Haushaltssicherung → Haushalt, Verkehrssicherheit → Verkehr.
  - Die alten Adressen `/thema/<alt>/` leiten dauerhaft (301) weiter. Gelöscht werden nur Schlagwörter, nie Meldungen. Das Ergebnis steht in der Option `ma_schlagwoerter_bericht`.
- **Weniger dünne Seiten bei Google** (`includes/seo.php`):
  - Themen mit weniger als 3 Meldungen: `noindex,follow` und raus aus der Sitemap. Für Leser bleiben sie erreichbar.
  - Übernommene Stellen und Immobilien (aus dem Markt-Abgleich): `noindex,follow` und raus aus der Sitemap. Eigene Anzeigen sowie `/jobs/` und `/immobilien/` bleiben drin.
  - Startseiten-Titel kürzer: „Merzenich Aktuell: Nachrichten aus Merzenich und Ortsteilen“. Die Ortsteile stehen in der Beschreibung.
- **Google per API** (`deploy/google-seo.mjs`, nicht in den Paketen): Sitemaps einreichen und ihren Stand zeigen, Leistung je Suchanfrage und Seite (Websuche, News, Discover, Google News), URL-Prüfung und PageSpeed. Die Schlüssel kommen aus der Umgebung (`GOOGLE_SERVICE_ACCOUNT_JSON`, `PAGESPEED_API_KEY`), nie aus dem Repository. Einrichtung: `GOOGLE-NEWS.md`.
- **Tests:** `qa/wordpress/autoren-test.php` (Autoren-Regeln, Freigabe, Schema, Schwelle, Schlagwörter), `qa/google-seo-test.mjs` (Anmeldung, Property, Fehlerhinweise).
- **Bildpools:** Sichtprüfung Runde 18. Die 12 nachgeladenen Fotos sind alle ausgeschlossen (`docs/POOLFOTOS-PRUEFUNG.md`).

## Neu in Theme 21.15.1 / Plugin 1.25.1 (07.10.2026): feste Reihenfolge bei gleichem Datum, Sichtprüfung Runde 17

Viele importierte Meldungen tragen dieselbe Zeit (z. B. 04.09., 00:00). Die Datenbank gab sie je nach Abfrage verschieden aus. Deshalb zeigte das Layout-Board auf den Ressortseiten andere Karten als die Seite, und nach einem Tausch sprangen die automatisch belegten Plätze. Bei gleichem Datum entscheidet jetzt die Beitragsnummer (`posts_orderby`, `inc/ma21.php`), so wie die Seite es schon zeigte.

Plugin 1.25.1 bringt die Poolliste nach Sichtprüfung Runde 17 mit: 6 Fotos freigegeben, 21 ausgeschlossen (`docs/POOLFOTOS-PRUEFUNG.md`). Beide Pakete hochladen.

## Neu in Theme 21.15.0 / Plugin 1.25.0 (07.10.2026): Rückmeldung Luis, Teil 2 (Backend, Märkte, Sport)

Beide Pakete hochladen. Nach dem Hochladen einmal das Backend öffnen. Dabei werden Bildpools, Redaktionsseiten und Abgleich nachgezogen.

- **Layout-Board:**
  - **Ziehen:**
    - Karten lassen sich am Griff ⠿ ziehen, mit der Maus auch an der ganzen Karte, auf jede andere Karte. Das gilt im Backend-Board und im Bearbeiten-Modus auf der Seite.
    - Eine halbdurchsichtige Kopie folgt dem Zeiger. Am oberen und unteren Rand scrollt die Seite mit. Esc bricht ab, Loslassen neben einer Karte sagt „Hier ist kein Platz“.
    - Am Handy wird am Griff gezogen.
    - Statt HTML5-Drag-and-drop laufen Pointer-Ereignisse (`layout.js`, `ziehbar`).
  - **Alle Meldungen einsetzbar:**
    - „Nur in der Rubrik“ sperrt nur noch die Startseite. Zieht man so eine Meldung dorthin, fragt das Board kurz nach und setzt sie auf „Startseite frei“.
    - Entwürfe, wartende und geplante Meldungen lassen sich **vormerken**. Der Platz bleibt reserviert, die Freigabe bleibt beim Menschen.
    - Die Auswahl hat den Filter „Stand“ und „Mehr laden“.
  - Bei einem Konflikt (409) lädt das Board still neu und wiederholt die Aktion.
- **Dashboard:**
  - Ganz oben steht die Kachel „Heute zu tun“ mit Zahl und Knopf für Freigaben, Eingang, Kommentare, Sport und bald endende Anzeigen.
  - Site Health, „Ereignisse & News“ und „Schneller Entwurf“ sind ausgeblendet.
  - Der Aktualitätscheck spricht Deutsch und nimmt den Aufmacher aus dem Layout.
- **Beiträge-Liste:**
  - Filter für Startseite (freigegeben / offen / nur Rubrik), Relevanz (hoch / mittel / niedrig / ohne) und Bild (ohne Bild / Rechte offen).
  - Beim Sortieren nach Relevanz bleiben Beiträge ohne Relevanz sichtbar und stehen am Ende; bei gleicher Relevanz kommt der neueste zuerst (`beitragsliste.php`).
- **Bildpools:**
  - Sortiert: Themenpools A–Z, dann Detailpools A–Z. Die Übersicht sagt je Pool, wie viele Fotos fehlen.
  - Der Pool-Filter gibt es auch in der Mediathek-Rasteransicht und im Bild-Auswahlfenster.
  - Sichtprüfung Runde 16: 16 Fotos freigegeben, 49 ausgeschlossen, darunter Tierrettung im Sudan im Pool „Rettung“. WordPress zieht das selbst nach: ausgeschlossene Fotos werden „Abgelehnt“ und verlassen den Pool, nichts wird gelöscht.
  - Die Lücken stehen in `docs/FOTOWUNSCHLISTE.md` (für ChatGPT).
- **Benutzer:**
  - Unter Benutzer → Vereinszugänge zeigt ein gelber Kasten die Partner- und Vereinskonten mit Platzhalter-Adresse `@hk-growthoperator.de`. Ein Knopf leert diese Adressen; Admins sind ausgenommen.
  - Partnerkonten dürfen ohne E-Mail speichern und tragen ihre eigene Adresse ein. Ein Hinweis im Profil bittet darum.
  - Neue Vereinszugänge entstehen ohne Platzhalter-Adresse.
- **Stellen und Immobilien als echte Einträge** (`markt-import.php`):
  - Der geprüfte Marktstand (`market.json`) wird zu Einträgen unter „Stellen“ und „Immobilien“, mit Quelle, Prüfdatum und Ende: Stellen 30 Tage, Immobilien 35 Tage nach der letzten Prüfung.
  - Läuft täglich mit dem Abgleich und auf Knopfdruck (Merzenich Aktuell → Abgleich: „Probelauf“, „Übernehmen“).
  - Angebote, die aus der Quelle fallen, enden am selben Tag. Gelöscht wird nichts, Anbieterbilder werden nie übernommen.
  - `/jobs/` und `/immobilien/` zeigen die Einträge nach Gemeinde, Merzenich zuerst.
- **Keine selbstgemachten Grafiken mehr:**
  - Entfernt sind die Ersatzgrafiken `ph-*.svg`, die Sportart-Grafiken `sportarten/*.svg` und die Symbolbilder in Stellen- und Immobilienlisten.
  - Ohne echtes Foto bleibt die Karte ohne Bild. Auch Anzeigen-Einzelseiten zeigen nur noch eigene Fotos.
- **Sport:**
  - `/sport/` zeigt unter „Wer spielt wo“ nur noch Vereine und Abteilungen mit Mannschaften im Spielbetrieb: 11 Vereine, 47 Mannschaften.
  - Je Mannschaft stehen Liga, Platz, Punkte und Spiele, mit Link zur Tabelle beim Verband (FUSSBALL.DE, nuLiga, click-TT, Billard-Verband Niederrhein, Schachverband Mittelrhein).
  - Dazu kommen Kinderfußball ohne Tabelle, Wettkämpfe und der Vergleich der ersten Fußballmannschaften.
  - Ohne Nachweis fehlen SC 1919 Badminton, TV Girbelsrath, UL-Aero-Club und LDDF Discofox. Ungeklärt sind TV Merzenich, BSC Girbelsrath, KK Klub Morschenich, Gut Schuss und Düren Demons.
  - Die Daten liegen in `data/sport-aktiv.json`, Stand 07.10. Ein neuerer Stand im Repository kommt täglich ohne neues Plugin.
  - Das Vereinsverzeichnis bleibt unverändert.

**Geprüft:**
- Neu: `markt-import-test.php` (Felder, Laufzeiten, Ausschlüsse, Filter der Beiträge-Liste) und `sport-aktiv-test.php` (Gruppen, Vergleich, Datenstand, keine SVG). Neu ist auch der Abschnitt „Alle Meldungen einsetzbar“ in `layout-test.php`. Alle Tests grün, `node deploy/kette.mjs` ohne Fehler.
- Lokal:
  - Marktimport: Probelauf 67 neu, Übernehmen 67, zweiter Lauf 67 aktualisiert.
  - Board-Ziehen mit Maus und Touch: Aufmacher ↔ Bühne, Reihe ↔ Reihe, Ressort und Vormerkung eines Entwurfs.
  - Profil eines Partnerkontos ohne E-Mail speichern.
  - Sporttabellen bei 1440 und 390 px ohne seitliches Scrollen.

## Neu in Theme 21.14.0 / Plugin 1.24.0 (07.10.2026): Rückmeldung Luis, Teil 1 (Seite)

Rückmeldung des Betreibers am 07.10. nach Durchsicht der Live-Seite, Vorbild oberbergaktuell.de. Die Startseite und die Vereinsseite bleiben unverändert. Beide Pakete hochladen.

- **Kopfmenü wieder voll:** Seit 21.11.0 erschien ein Thema erst ab drei Meldungen; Rathaus schrumpfte auf 2, Leben auf 1 Link. Jetzt reicht eine Meldung (`ma21_menue_gruppen_pruefen`), alle Links aus `ressort-menue.json` sind zurück (Rathaus 12, Leben 12, Sport 9, Wirtschaft 11). Die Klappliste in der Menüspalte nutzt dieselben Gruppen.
- **Unternehmen wie die anderen Ressorts:**
  - Links die Linkgruppen, rechts „Neu im Ressort“.
  - Solange kein Betrieb gebucht hat, zeigt die rechte Spalte als „Musterbeitrag“ gekennzeichnete Beiträge (`ma21_menue_musterbeitraege`). Daneben steht die Musteranzeige wie bei Vereine.
  - Die Firmenliste links entfällt.
- **Kopfzeile:**
  - Die Lupe steht auf jeder Breite rechts, nicht erst beim Scrollen; das breite Suchfeld neben dem Logo entfällt. Ein Klick öffnet ein Suchfeld unter dem Kopf (`app.js`).
  - „Mehr ☰“ (am Handy ☰) öffnet die Spalte von rechts mit allen Ressorts und ihren Unterseiten als Klappliste.
- **Entfernt:**
  - Instagram überall: Kopf, Fuß, Menüspalte, Texte „Unterstützen“ und „WhatsApp“, `sameAs` (gespeicherte Liste einmalig bereinigt).
  - Die Notruf-Tasten 112/110 in „Merzenich jetzt“ und die beiden Nummern auf `/service/`. Termin, Wetter und Einsatz bleiben.
- **Ressortseiten** (Aktuell, Blaulicht, Rathaus, Leben, Wirtschaft, Menschen):
  - Nach dem Aufmacher zwei Spalten mit Bildkarten (Bild, Rubrik, Überschrift, Anriss, Zeit) statt Zeilen untereinander. Nach sechs Karten eine Musteranzeige.
  - 12 Karten je Seite, bei Aktuell 20. Die Plätze des Layout-Boards (`data-slot`) bleiben an den Karten.
  - Sport und Vereine behalten ihr Layout.
- **Termine:**
  - `/termine/` zeigt Bildkarten mit Datum auf dem Foto. „Liste“ schaltet auf die Zeilen um, die Wahl bleibt gespeichert.
  - Filter (Zeitraum, Ort, Kategorie) wie bisher.
- **/unternehmen/** zeigt vier Musterbeiträge mit Bild und eine Musteranzeige über dem Angebot. **/tipp/** zeigt Bildkarten.
- **/anzeigen/aufgeben/:**
  - Oben fünf Wahlkarten (Werbung, Immobilie, Stellenanzeige, Traueranzeige, Familienanzeige), darunter nur das gewählte Formular (`[ma_anzeige_aufgeben]`). Die Karten sind echte Links (`?art=…`); mit JavaScript ohne Neuladen.
  - Der Betreff ergibt sich bei Anzeigen aus der Auswahl, einheitlich in Mail und Eingang: „Werbung: Werbebanner (Name)“, Mail „[Merzenich Aktuell] Werbung: Werbebanner – Name“. Neu wählbar: „Sponsoring einer Rubrik“, „Noch offen, bitte beraten“.
  - Die Seite kommt mit der Fassung `2026-10-07` (Redaktionsseiten). Wurde sie von Hand geändert, unter Merzenich Aktuell → Redaktionsseiten übernehmen.

**Geprüft:**
- `qa/wordpress/ressort-test.php` (neu), `formular-test.php` erweitert, alle übrigen Tests grün.
- Lokal mit Bildschirmfotos bei 1440, 860 und 390 px:
  - Ausklappmenüs mit voller Linkzahl.
  - Lupe auf jeder Breite rechts, auch nach dem Scrollen; Suchfeld öffnet mit Fokus.
  - Spalte öffnet von rechts, Esc schließt.
  - Ressort-Raster, Termine-Bildkarten, Unternehmen, Tipp.
  - Aufgeben: Auswahl, ein Formular, Absenden legt „Werbung: Werbebanner (…)“ im Eingang an.

## Neu in Plugin 1.23.0 (06.10.2026): Seite „Mailversand“

Frage des Betreibers: Ist alles vorbereitet, damit Mails rausgehen, sobald die Daten da sind? Bisher gab es keine Einstellung für den Mailserver; WordPress verschickte über die PHP-Standardfunktion.

- **Merzenich Aktuell → Mailversand** (`includes/mailversand.php`):
  - Felder: Schalter, Server (Vorgabe `smtp.ionos.de`), Port 587, Verschlüsselung (STARTTLS, SSL, für Tests keine), Benutzer, Passwort, Absender-Adresse und -Name.
  - SMTP greift nur, wenn eingeschaltet und vollständig (`phpmailer_init`, `wp_mail_from`, `wp_mail_from_name`). Sonst bleibt alles wie vorher.
- **Passwort:**
  - Verschlüsselt gespeichert (`sodium_crypto_secretbox`, Schlüssel aus `wp_salt('auth')`), nie im HTML.
  - Ein leeres Feld behält das gespeicherte, „Passwort löschen“ entfernt es.
- **Test-Mail** an die Redaktions-E-Mail. Fehler wie „Die Authentifizierung war nicht möglich“ erscheinen auf der Seite, ohne Passwort.
- **Dashboard:** neue Zeile „Mailversand“ mit Stand und Link.
- **Rechtstexte:** Der offene Punkt beschreibt den Weg (Postfach bei IONOS, hier eintragen, dann Datenschutzerklärung ergänzen). Den veralteten Satz zur § 18-Person auf der Rechtstexte-Seite habe ich entfernt.

**Geprüft:**
- `qa/wordpress/mailversand-test.php` (neu).
- Lokal mit einem Test-Mailserver:
  - Test-Mail kommt mit „Merzenich Aktuell <redaktion@…>“ an der Redaktions-E-Mail an.
  - Ein falsches Passwort zeigt die Fehlermeldung.
  - Erneutes Speichern mit leerem Passwortfeld behält das Passwort.
  - Ausschalten stellt den Standard wieder her.
- Live ist nichts eingetragen, solange die IONOS-Daten fehlen.

## Neu in Theme 21.13.0 / Plugin 1.22.0 (06./07.10.2026): Abnahme für die Übergabe

Auftrag des Betreibers (06.10.): die ganze Seite bis Mittwoch fertig machen. Grundlage war eine Vollprüfung: 465 Seiten, alle internen und 315 externe Links, 40 Seitentypen auf Handy (375 px) und Computer (1366 px), Backend, Code und Doku. Kaputte Bilder, JavaScript-Fehler oder seitliches Verrutschen gab es nicht. Behoben bzw. ergänzt:

- **Inhalte:** 5 Meldungen und 26 Termine gegen ihre Quellen geprüft und freigegeben. Die doppelte „MSG Hitnight“ ist entfernt. Spieltags-Überschriften vom 04.10. nennen das Datum statt „heute“.
- **Impressum (Plugin):** Verantwortlicher nach § 18 Abs. 2 MStV ist Anto-Sutharsan Jesuthasan, Rheinstr. 78a, 51371 Leverkusen (Vorgabe Betreiber). Rechtstexte-Fassung 2026-10-06.
- **Nur noch verschlüsselt:** `http://` leitet mit 301 auf `https://` um, auch hinter einem Proxy ohne Schleife. Dazu kommen Sicherheits-Kopfzeilen (nosniff, Referrer-Policy, X-Frame-Options, Permissions-Policy, HSTS ohne Subdomains). `readme.html`, `license.txt` und `wp-config-sample.php` sind gesperrt.
- **Einzelseiten im neuen Design:** Stelle, Immobilie, Trauer- und Familienanzeige, Tipp und Betriebsprofil (`inc/einzel.php`). Jede hat Brotkrumen, einen Faktenblock aus den vorhandenen Feldern und Knöpfe (Bewerbung, Angebot, Anrufen, Website). Dazu Herkunft bzw. werbliche Kennzeichnung und „Weitere …“. Damit nutzt keine Seite mehr die alten Stile. `archive-alt.php`, `template-parts/`, `service.css`, `site.js` und die alten Regeln in `style.css` sind entfernt.
- **Schneller laden:**
  - Die sechs Werbe-Musterbilder hatten bis zu 1,1 MB. Jetzt sind sie 1080 px breit und unter 300 KB, mit einer 720er-Fassung für Musterprofile und das Unternehmen-Menü.
  - Die Logos sind auf 720 px verkleinert.
  - `/unternehmen/` und `/betriebe/` laden rund 3 MB weniger.
  - `deploy/werben-bilder.mjs` verkleinert künftig selbst.
- **Teilen-Vorschau (Plugin, `includes/teilen-bilder.php`):**
  - Je Beitragsbild entstehen einmal drei JPEG-Zuschnitte: 16:9, 4:3 und 1:1, höchstens 1200 px, nie hochgerechnet.
  - `og:image` zeigt 16:9. NewsArticle und Event führen alle drei Zuschnitte.
  - Vorhandene Bilder holt WP-Cron nach, 20 je Lauf.
- **Links ohne Umleitung:** Die Links aus Kopf, Startseite und Sport-Kasten gehen direkt auf die WordPress-Adressen: Ortsteile unter `/ort/…/`, außerdem Suche, Termin melden und SC 1919. „Redaktion“ im Fuß führt auf `/redaktion/`, das Jahr im Copyright stellt sich selbst um.
- **Aktuell statt Bautag:**
  - Spielstand-Ecke und Sportmodul auf `/sport/` kommen aus demselben Stand wie `/api/sport-current.json` (`inc/sport.php`). Vorher zeigten sie den 7. Platz vom 23.09., obwohl im Backend der 5. Platz eingetragen war.
  - „Merzenich jetzt“ zeigt den jüngsten veröffentlichten Feuerwehreinsatz.
- **Google:**
  - Eigene Titel und Beschreibungen für die Anzeigenlisten, das Branchenbuch (`/betriebe/` hat nicht mehr denselben Titel wie `/unternehmen/`) sowie für Service, Diskussion, Archiv und Impressum.
  - Kurze Termin- und Vereinstexte werden mit Datum, Ort und einem Satz aufgefüllt.
  - Bezahlte Beiträge nennen den Auftraggeber als `author`.
- **Feinschliff:**
  - Vereinsseite: Die Terminzeiten waren bis zu zwei Stunden versetzt; „00:00 Uhr“ fällt weg.
  - Wochentag und Monat stehen immer auf Deutsch.
  - Abgelaufene Anzeigen fallen aus allen Listen.
  - Bezahlte Beiträge zeigen den Auftraggeber statt „Redaktion“ als Absender.
  - Links im Text auf noch nicht veröffentlichte eigene Beiträge werden zu Text.
  - Auf Vereins-, Termin- und Anzeigenseiten steht die Seitenleiste mit Kontakt auf Handy und Tablet unter dem Text.
  - Es gibt eine Sprungmarke „Zum Inhalt springen“.
  - Kommentar- und Partnerformular sehen aus wie die übrigen Formulare und verweisen auf Richtlinien und Datenschutz.
  - Dachzeile mit Abstand vor dem Punkt.
- **Texte (Plugin, Redaktionsseiten-Fassung 2026-10-06):**
  - `/werben/` listet die buchbaren Flächen und hat einen Knopf „Werbung anfragen“.
  - `/unterstuetzen/` steht ohne „gibt es noch nicht“.
  - `/whatsapp/`: „Kanal wird gerade eingerichtet“.
- **Doku:**
  - `docs/UEBERGABE-KBS.md` (neu): Bedienung für die Redaktion und die Aufgaben, die nur der Betreiber erledigen kann.
  - `README-INSTALLATION.md` beschreibt den heutigen Stand.
  - `KOMMENTARE-MODERATION.md` beschreibt die WordPress-Moderation.

**Tests:**
- `qa/wordpress/sicherheit-test.php` (neu)
- `ladegewicht-test.php` (neu)
- `sport-modul-test.php` (neu)
- `seo-test.php`, `termine-test.php`, `seiten-test.php` und `rechtstexte-test.php` (erweitert)
- alle übrigen PHP-Tests, dazu `node deploy/kette.mjs --check`
- lokal alle sechs Einzelseiten und eine Vereinsseite auf Handy und Computer

**Live geprüft (06.10.2026, nach dem Upload):**
- Vollprüfung: 491 Seiten, alle 200, keine interne Umleitung, kein toter Link, jede Seite mit Beschreibung ab 70 Zeichen, keine doppelten Titel, genau eine H1.
- 40 Seitentypen auf Handy und Computer: kein altes Stylesheet, keine JavaScript-Fehler, kein seitliches Verrutschen.
- `http://` → 301 `https://`, Sicherheits-Kopfzeilen gesetzt, `/readme.html` 403.
- Impressum mit § 18, alle Redaktionsseiten auf Fassung 2026-10-06.
- `/sport/`: 5. Platz, 15 Punkte, 0:5 in Barmen, nächstes Spiel 09.10.
- Ladegewicht nach vollem Scrollen: `/unternehmen/` 1,2 MB (vorher 4,3), `/betriebe/` 1,3 MB (vorher 5,1), Startseite Handy 5,6 MB (vorher 9,0).
- Teilen-Zuschnitte entstehen auf dem Server, der Nachlauf über WP-Cron läuft.
- Beim Test der Zuschnitte wurde „Rauch über Bürgewald“ (Einsatz 134) über REST ohne Änderung gespeichert. Der Beitrag war ohne die Prüfhäkchen Datum, Ort und Human Review veröffentlicht, deshalb setzte ihn die Veröffentlichungssperre auf Entwurf. Nach dem Abgleich mit der Quelle (Heimat-Info der Feuerwehr) ist er über die Freigaben wieder veröffentlicht: Datum unverändert 28.09., nur Rubrik. Hinweis dazu in `UEBERGABE-KBS.md`.

## Neu in Theme 21.12.1 (06.10.2026): Bild auf der Terminseite

Das Bild einer Terminseite hatte keine Höhe (Bildrahmen ohne Seitenverhältnis) und war unsichtbar. Es steht jetzt im Format 3:2 (`plattform.css`, `.event-page .art-figure`).

## Neu in Theme 21.12.0 (06.10.2026): Webseiten-Symbol „M“ und Handy-Ansicht ohne alte Vorlagen

Meldungen des Betreibers: Als Webseiten-Symbol erschien noch ein SPD-Logo. Außerdem sollten die mobilen Anpassungen gemacht werden.
- **Symbol:**
  - `/favicon.ico` (16/32/48) und `/apple-touch-icon.png` (180×180) liefert die Seite jetzt selbst aus. Bisher waren beides nur Umleitungen auf das 1024er PNG.
  - Neu ist `/manifest.webmanifest` mit 192/512.
  - Die Kopf-Links tragen neue Dateinamen, damit Zwischenspeicher sie neu laden.
  - Login, RSS und REST melden dasselbe „M“ (`get_site_icon_url`).
  - Die Dateien entstehen mit `NODE_PATH=$(npm root -g) node deploy/symbole.mjs` aus `favicon.svg` bzw. `avatar-1024.png`.
  - Bereits gespeicherte Symbole erneuert ein iPhone nicht von selbst. Dafür das Lesezeichen bzw. das Symbol auf dem Home-Bildschirm neu anlegen oder die Website-Daten löschen.
- **Neue Vorlagen:** Diese Seiten liefen über die alte Vorlage (eigene Schrift, dicke Linien, keine Brotkrumen) und sehen jetzt aus wie die statische Seite:
  - **Termine:** Liste mit Filter nach Zeitraum, Ort und Kategorie (`assets/v20.js`) und Knöpfen Details/Kalender/Quelle. Terminseite mit Faktenblock, „In den Kalender“, Karte, Quelle und „Weitere Termine“. Code: `inc/termine.php`, `archive-ma_event.php`, `single-ma_event.php`.
  - **Traueranzeigen und Familienanzeigen:** neuer Seitenkopf, veröffentlichte Anzeigen als Zeilen, Verweis auf das Anzeigenformular mit Vorauswahl.
  - **/betriebe/ (Branchenbuch):** echte Einträge als Zeilen, rechts gekennzeichnete Musterprofile statt acht gleicher Kästen „Platz frei“, Formular „Betrieb eintragen“.
- Test: `qa/wordpress/termine-test.php`.

## Neu in Theme 21.11.3 (05.10.2026): Handy wie heute Morgen

Vorgabe Betreiber: Die Handy-Ansicht soll wieder so aussehen wie vor den Änderungen des Tages (Theme 21.10.4). Die Ausgabe-Wahl bleibt weg, am Computer bleibt alles wie es ist.
- Vergleich auf 390 px: Theme 21.10.4 und 21.11.2 nebeneinander in der lokalen Testumgebung. Ergebnis: Unterschiede gibt es nur bei der Leiste unter der Ressortleiste und beim Foto des Tages.
- **Foto des Tages:** Unter 768 px ausgeblendet (`plattform.css`). Am Computer und auf dem Tablet bleibt es, ebenso Einsendung und Einplanung im Backend.
- **Leiste „Merzenich · Jetzt“:** Sie steht schon wie früher die zweite Zeile unter „Ausgabe“. Der Abstand oben (16 px) gleicht die Ressortleiste aus, die 11 px hineinragt; ohne ihn wäre „MERZENICH · JETZT“ angeschnitten. Unverändert.
- Test: `qa/wordpress/foto-des-tages-test.php` prüft die Handy-Regel.

## Neu in Theme 21.11.2 / Plugin 1.21.1 (05.10.2026): Sportmodul „Nächstes Spiel“

Plugin 1.21.1: Die Abgleich-Seite sagt jetzt, dass recherchierte Termine mit Freigabe-Vermerk als Entwurf in die Freigaben kommen (26 Termine seit 05.10.2026).

Sportdaten aus dem Backend eingetragen (FUSSBALL.DE, Stand 05.10.2026): letztes Spiel Barmen – Merzenich 0:5 (02.10.), nächstes Spiel Freialdenhoven – Merzenich (09.10., 19:30), Tabelle Kreisliga A. `assets/v20.js` beschriftet das zweite Feld des Sportmoduls als „Nächstes Spiel“, solange das Spiel noch nicht angepfiffen ist (die Vorlage vom Bautag sagte „Ergebnis noch offen“).

## Neu in Theme 21.11.1 (05.10.2026): „Merzenich jetzt“ zeigt nur veröffentlichte Termine; neue Termine und Umkreis

- Der Eintrag „Nächster Termin“ in „Merzenich jetzt“ auf der Startseite kommt aus WordPress (`ma21_cockpit_termin`), nicht mehr aus der Vorlage vom Bautag. Recherchierte Termine im Entwurf erscheinen dort nicht.
- Inhalte (Recherche 05.10.2026, Quellen je Eintrag): 26 Termine vom 06.10. bis 26.11. in `inhalte/termine/2026-oktober-november.json`, alle als Entwurf in den Freigaben. 20 Umkreis-Meldungen in `inhalte/umkreis/2026-10-nachtrag.json`. Nicht aufgenommen wegen widersprüchlicher Quellen: Kreismajestätenschießen 10.10., Fatimatag Golzheim.
- Korrektur: Die Sitzung des Kuratoriums Rixen-Stiftung am 13.10. ist laut Tagesordnung vollständig nichtöffentlich; Bekanntmachung (Fraktionsraum A) und Ratsinformationssystem (Dienstzimmer des Bürgermeisters, Zimmer 12) nennen verschiedene Räume. Termin und Meldung sagen das jetzt.

## Neu in Theme 21.11.0 / Plugin 1.21.0 (05.10.2026): Startseite aktuell, Sport aus dem Backend, Foto-Einsendungen, Feinschliff

Ergebnis der Prüfung „Was fehlt noch“ (Live-Seite, Code, Doku). Zuerst, was kaputt war oder bald ausgelaufen wäre:
- **Startseite veraltet nicht mehr.** Terminspalte, „Was rund um Merzenich passiert“ sowie Stellen- und Immobilien-Teaser standen als feste Kopie vom Bautag in `vorlagen/startseite.html`. `ma21_startseite_bloecke()` ersetzt sie zur Laufzeit: Termine aus WordPress (`ma21_agenda_zeilen`, ohne Sporttermine, „Ganztägig“, „bis …“ bei mehrtägigen), Umkreis und Märkte aus dem aktuellen `index.html` im Repository (`ma21_repo_datei`, nur erlaubtes HTML über `ma21_block_saeubern`). Neue Termine stehen sofort auf der Startseite.
- **„Im Kalender speichern“** (vorher 404): `/termine/<slug>/termin.ics` (Plugin `feeds.php`, `ma_ics_eintrag`), Knopf auch auf der Terminseite. Termine ohne eigenes Ende zeigen nur den Beginn (vorher „19:30 Uhr–19:30 Uhr“).
- **Sport aus dem Backend** (Merzenich Aktuell → Sport): Ergebnis mit Link zum Spielbericht, nächstes Spiel, Tabelle mit S/U/N. `ma_sport_als_json()` liefert dasselbe Format wie `/api/sport-current.json`; das Theme nimmt den Backend-Stand, sobald er mindestens so neu ist wie der im Repository. Hinweis im Dashboard, wenn ein Spiel seit drei Stunden vorbei ist und kein Ergebnis eingetragen wurde (`ma_sport_ueberfaellig`).
- **Foto des Tages aus Leser-Einsendungen.** Auf „Meldung senden“ ein eigenes Formular (`[ma_formular typ="foto"]`, Ortsteil, Foto Pflicht, Bildrechte und Zustimmung zur Veröffentlichung mit Namen). Im Eingang plant die Redaktion das Foto für ein Datum ein (Kasten „Foto des Tages“, `includes/foto-des-tages.php`, Option `ma_foto_des_tages`); an diesem Tag zeigt die Startseite dieses Foto vor der Ortsreihe. Der Knopf „Ihr Foto des Tages einsenden“ führt jetzt dorthin.
- **Recherchierte Termine als Entwurf.** `"entwurf": true` in `inhalte/termine/*.json` setzt den Termin im Import auf Entwurf; die Freigaben-Seite zeigt Abgleich-Entwürfe von Meldungen und Terminen (`ma_freigaben_abgleich`).

Feinschliff:
- Formulare: Nach dem Absenden steht eine Bestätigung über dem Formular (vorher nichts). Links wie `/anzeigen/aufgeben/?art=Werbung&format=Unternehmenskanal`, `?art=Immobilie&angebot=Verkauf`, `?art=Traueranzeige&trauerform=…` wählen das passende Formular und die Art vor und springen dorthin (`ma_form_ziel_aus_link`). „Stelle inserieren“ führt zum Stellen-Formular. Der Partner-Antrag auf `/betriebe/` bietet nur Unternehmen, Immobilien, Werbepartner an und landet zusätzlich im Eingang.
- Suche ohne Treffer: „Keine Treffer für …“ mit Vorschlägen statt „Hier gibt es noch keine Meldung“.
- Meldungen ohne bekannte Uhrzeit (00:00) zeigen nur das Datum (`ma21_zeit_text`).
- `/favicon.ico` und `/apple-touch-icon.png` zeigen auf das eigene Logo; RSS-Feed im Seitenkopf angemeldet.
- Sicherheit: `/wp-json/wp/v2/users` nur noch für Angemeldete (verriet den Benutzernamen).
- Werbung: Beiträge von Unternehmens-Zugängen sind automatisch „Anzeige · Gesponsert“; gesponserte Beiträge lösen keine Push-Nachricht aus; höchstens eine Push je 10 Minuten (Sammelfreigabe).
- `/service/`: Notrufe und Notdienste oben (112, 110, 116 117, Giftnotruf NRW, Telefonseelsorge, Apotheken-Notdienst).
- Ressort-Menü: Themenlinks erst ab drei Meldungen (vorher 27 Themen mit nur einer), Ortsteil-Links direkt auf `/ort/…/`.
- Plugin-Übersicht: Feld „Redaktions-E-Mail“ (`ma_editorial_email`), „Letzter Abgleich“ statt eines nie gesetzten Werts. Altes `kommentare.js` aus dem Fuß entfernt.
- Bewusst nicht geändert: `deploy/site.json` `url` (Adresse der früheren Vorschau). Der Wert steuert die Pfadumsetzung des WordPress-Imports (`liveUrl`); ein Umstellen würde Links im Import verändern, ohne dass WordPress ihn nutzt.
- Tests: `qa/wordpress/formular-test.php` (neu), `sport-test.php` (Backend-Format, Hinweis), `foto-des-tages-test.php`.

## Neu in Theme 21.10.6 (05.10.2026): Foto des Tages immer auf der Startseite

Vorgabe Betreiber: „Foto des Tages muss auf jeden Fall mit rein.“ Bisher füllte nur der statische Bau die Fläche, und der ließ sie weg, sobald jede Ortsansicht schon irgendwo auf der Startseite stand (zuletzt täglich). Jetzt wählt WordPress das Foto selbst (`ma21_foto_des_tages()`, Platz `<!-- fotodestages -->` in `ma21_startseite()`):
- Reihenfolge: datierte Leser-Einsendung für heute (`deploy/foto-des-tages.json`, `eintraege`, nur mit Zustimmung), sonst täglich reihum eine gesichtete Ortsansicht aus der vollen Reihe `alle` in `assets/foto-des-tages.json` (acht Ortsansichten aus `deploy/ortsbilder.json` und neu `zusatz`: Heiligenhäuschen St. Florian in Golzheim, nur Querformat, ohne Gesichter, Kennzeichen und Hausnummern).
- Motive, die schon als Bild einer Meldung auf der Startseite stehen (`ma_image_static_src` der Beitragsbilder), kommen später dran; sind alle belegt, gilt die volle Reihe. Die Fläche fällt nie mehr weg.
- Poolfotos kommen aus der Mediathek (mit `srcset`), Ortsansichten aus `/assets/places/`. Fotonachweis mit Link zur Quelle, Datum auf Deutsch, Knopf „Ihr Foto des Tages einsenden“.
- Test: `qa/wordpress/foto-des-tages-test.php`.

## Neu in Theme 21.10.5 (05.10.2026): Menü öffnet beim Überfahren, Firmenliste unter „Unternehmen“, keine Ausgabe-Wahl

- **Ressort-Menü (`assets/ressort-dropdowns.js`):** Mit Maus oder Trackpad öffnet das Menü beim Überfahren und schließt beim Verlassen; ein Klick auf das Ressort führt sofort auf die Ressortseite, ohne zweiten Klick. Bisher galt das Überfahren nur, wenn der Browser „hover: hover“ meldet. Das iPad mit Trackpad meldet das nicht, dort öffnete der erste Klick nur das Menü. Jetzt zählt die Zeigerart (`pointerType` „mouse“). Auf Touchscreens öffnet der erste Tipp das Menü, der zweite die Seite. Tastatur: Enter öffnet die Seite, Pfeil nach unten das Menü.
- **„Unternehmen“ wie Oberberg Aktuell unter „Wirtschaft“:** links der Ressortkopf mit Service-Links, daneben auf grauem Grund die Betriebe (Kürzel oder Logo, Branche, Ortsteil), rechts drei Karten mit Bild, Kicker, Titel, Zeile, Anriss und Knopf. Ohne gewählten Betrieb stehen dort die neuesten Beiträge der Unternehmen, solange es keine gibt, die neuesten Wirtschaftsmeldungen der Redaktion. Überfahren (oder Fokus) eines Betriebs zeigt seine Beiträge: echte Profile (`ma_business`) mit ihren gesponserten Beiträgen (Auftraggeber = Profilname), sonst die Profilkarte. Solange weniger als drei echte Profile da sind, füllen die fünf Musterprofile auf, jedes mit „Musterbeitrag“, Profilkarte und Karte „Ihr Betrieb an dieser Stelle“, sichtbar als Muster gekennzeichnet, mit Hinweis „keine echten Unternehmen“ und Fotonachweis. Daten: `ma21_menue_firmen()` (inc/markt.php) im Ressort-Menü unter `firmen`; Wirtschaftsmeldungen bekommen dort einen `anriss`.
- **Keine Ausgabe-Wahl mehr oben (`ma21_kopf_ortswahl`):** Der Schalter „Ausgabe Merzenich“ entfällt, alle fünf Orte sind Ortsteile der Gemeinde. Auf der Startseite bleibt die Zeile „Merzenich · Jetzt“ (links beginnend, eigener Abstand in `plattform.css`), auf Unterseiten verschwindet die dann leere Leiste. Die Ortsteile bleiben im Mehr-Menü, in der Schublade und im Fuß, auf einer Ortsseite markiert.

## Neu in Theme 21.10.4 / Plugin 1.20.8 (05.10.2026): Bilder bei Terminen, Musterprofile bei Unternehmen

- **Termine mit Bild (Plugin):** Im Ressort-Menü stand bei „Termine“ rechts unter „Neu im Ressort“ kein einziges Bild: die Termine kamen vor den Bildpools nach WordPress und hatten kein Beitragsbild. `ma_bildpool_nachziehen()` setzt einmal je Plugin-Version (60 je Admin-Aufruf) ein geprüftes Poolfoto für Termine und Meldungen ohne Bild. `ma_bildpool_fuer_termin()` wählt nach Art des Termins: Sitzungen und Rathaus `aktuell` (Merzenicher Ortsmotive), Kirche `kirchedetail`, Sport `sport`, Tanz, Party und Karneval `tanzdetail`, sonst `termine`; ganze Wörter über `\p{L}` („Fußball“ ist kein Ball). Automatisch gesetzte Termin-Bilder werden neu gewählt, wenn die Art ein anderes Motiv ergibt; von Hand gesetzte Bilder bleiben.
- **Musterprofile auf `/unternehmen/` (Theme):** rechte Spalte „Unternehmen aus der Gemeinde“ wie bei Oberberg Aktuell: fünf ausgedachte Betriebe (Backstube, Café, Schreinerei, Steuerbüro, Immobilien) mit Foto, Branche, Ortsteil, Werbetext und Öffnungszeiten, jeder sichtbar als „Musterprofil“ gekennzeichnet, mit Hinweis „keine echten Unternehmen“ und Fotonachweis. Klick und Knopf „Eigenen Betrieb eintragen“ führen in den Anzeigen-Assistenten. Echte Betriebe nur mit Einwilligung; ab drei echten Profilen verschwinden die Muster (`ma21_musterprofile_html`, Daten in `MA21_MUSTERPROFILE`).

## Neu in Theme 21.10.3 (05.10.2026): Seiten immer frisch

Der Server bei IONOS gibt HTML-Seiten `Cache-Control: max-age=3600` mit (mod_expires), JSON-Daten 28 Tage. Browser und der Zwischenspeicher des Hosters zeigten so bis zu eine Stunde alte Seiten (Betreiber sah nach dem Update noch „149 Meldungen“). `ma21_cache()` setzt Cache-Control und Expires selbst: Seiten `no-cache, max-age=0, must-revalidate`, Ressort-Menü 5 Minuten, Spielstand und Übersteuerung 10 Minuten. Setzt PHP Expires, fügt mod_expires nichts mehr hinzu.

## Neu in Theme 21.10.2 / Plugin 1.20.7 (05.10.2026): keine Zahlen mehr auf der Seite

Vorgabe Betreiber: Wie viele Meldungen es gibt, steht nirgends mehr („unprofessionell“). Entfernt:
- Zählzeile „N Meldungen“ auf Rubriken, Ortsseiten, Suche und „Alle Meldungen“ (`archive.php`); Zahl in der Ausgabe-Wahl im Kopf und „Heute n neue Meldungen“ in der Zeile „Merzenich · Jetzt“ (`ma21_kopf_*`, das Feld fehlt, also rechnet `assets/kopf.js` auch nichts nach).
- Stellen- und Immobilienmarkt: „32 Stellen“ und „13 Anzeigen“ je Ort (auch im Block aus dem Repository); Tipps: „6 Termine“; Unternehmen: Zählzeile.
- Vereinsliste „42 Vereine“, Sportvereine „3 Einträge“ je Sportart (Plugin), Archiv „20 Meldungen“ je Monat, Themenliste mit Zahlen (Plugin), „0 Kommentare“ unter Artikeln (Überschrift heißt jetzt „Kommentare“).
- `qa/wordpress/kopf-test.php` prüft, dass im Kopf keine Zahl steht.

## Neu in Theme 21.10.1 / Plugin 1.20.6 (05.10.2026): ohne Coolify, keine leeren Seiten mehr

- **Coolify abgeschaltet:** Die Vorschauseite `merzenichaktuell.hk-growthoperator.de` gibt es nicht mehr (Betreiber, 05.10.2026). Theme und Plugin hängen nicht mehr an ihr: keine Erreichbarkeitsprüfung, keine Umleitung dorthin. Fehlt eine Datei unter `/assets/`, holt der Server sie einmal aus dem Repository, legt sie unter `uploads/ma-assets/` ab und liefert sie danach selbst aus (eigene Regel in `.htaccess`); der Browser verbindet sich nie mit GitHub. Spielstand, Stellen- und Immobilienmarkt liest `ma21_repo_datei()` aus dem Repository, mit Zwischenspeicher und Notkopie. Poolfotos und Abgleich-Bilder kommen aus dem Repository; eine noch gespeicherte Adresse der Vorschauseite wird ignoriert. Datenschutzerklärung Fassung 2026-10-05 beschreibt das.
- **Stellenmarkt `/jobs/` und Immobilienmarkt `/immobilien/`:** neue Vorlagen im aktuellen Design (`inc/markt.php`, `archive-ma_job.php`, `archive-ma_property.php`). Statt „keine veröffentlichten Einträge“ zeigen sie hier aufgegebene Anzeigen und den täglich geprüften Marktüberblick aus dem Repository (32 Stellen, 35 Immobilienangebote, jeweils mit Prüfdatum und Link zur Originalanzeige). Die Art der Anzeige steht in der Kopfzeile; die schmale Box daneben schnitt „Ausbildung“ ab.
- **Tipps `/tipp/`:** „Unsere Tipps für die nächsten Tage“ aus den veröffentlichten Terminen der nächsten drei Wochen, darüber bezahlte Tipps als Anzeige gekennzeichnet; Seitenleiste mit Tipp-Anfrage und kostenloser Terminmeldung.
- **Unternehmen `/unternehmen/`:** solange kein Unternehmensbeitrag freigegeben ist, steht dort das Angebot (Unternehmenskanal, drei Schritte, Knöpfe) statt einer leeren Liste. Untertitel passt zur Vorgabe „nur Beiträge der Unternehmen“.
- **Rubrik Menschen:** sechs belegte Meldungen (`inhalte/meldungen/2026-10-05-menschen.json`): Schützenmajestäten in Merzenich, Golzheim und Morschenich, die Golzheimer Karnevalsprinzessin, Ehrungen zum 121-jährigen Bestehen der KG Golzheim, die Landwirte beim Flächenbrand vor Girbelsrath. Quellen: Amtsblätter der Gemeinde, Vereinsseiten, Feuerwehrberichte; Datum je Meldung ist das der jüngsten verwendeten Quelle.
- **Import-Datei ohne Coolify-Adressen (`deploy/wordpress-import.mjs`):** interne Links in Artikeltexten, Bildadressen und Basisadresse zeigen auf `merzenich-aktuell.de` (über `liveUrl()`), nicht mehr auf die abgeschaltete Vorschauseite. Sieben Live-Artikel enthielten je einen toten Link; der Abgleich ersetzt sie.
- **Trauer- und Familienanzeigen, Unternehmensprofile (21.10.1):** statt „keine veröffentlichten Einträge“ bzw. „Noch kein Unternehmen hat ein Profil“ erklären die Seiten, was dort erscheint und wie man eine Anzeige aufgibt. Anzeigen erfinden wir nicht; die geben Angehörige und Unternehmen selbst auf.
- **Diskussion `/diskussion/` (Plugin):** ohne freigegebene Kommentare die jüngsten Meldungen mit Sprung zum Kommentarfeld statt eines leeren Satzes.

**Geprüft:** alle PHP-Tests (Rechtstexte-Test auf die neue Fassung angepasst), lokal `/jobs/` 32 und `/immobilien/` 35 Anzeigen, `/tipp/` 6 Termine, Nachladen einer fehlenden Datei aus dem Repository, Bildschirmfotos Desktop und Mobil; live nach dem Upload.

## Neu in Theme 21.9.10 / Plugin 1.20.5 (04.10.2026): Kopfleiste aus WordPress, Freigaben mit Abgleich-Entwürfen, Dashboard-Kachel, Abgleich zuverlässig angestoßen

- **Kopf („Merzenich · Jetzt“, Ausgabe-Wahl):** Die Vorlage `vorlagen/kopf.html` trug den Stand der statischen Seite: feste Zahlen je Ortsteil („94 Meldungen“), Links `/merzenich/` (eine Umleitung je Klick) und als „Neu“ eine Meldung, die auf WordPress noch Entwurf war, für Leser also ein 404. `ma21_kopf()` füllt die Leiste jetzt aus WordPress: Zahl veröffentlichter Meldungen je Ort (`ma_location`), Adressen `/ort/…/`, jüngste veröffentlichte Meldung mit Uhrzeit, „Heute n neue Meldungen“, nächster veröffentlichter Termin (Beginn in Ortszeit über `ma_event_timestamp`, 21.9.9 zeigte ihn zwei Stunden zu spät); auf `/ort/golzheim/` zeigt die Wahl Golzheim. Alles 10 Minuten zwischengespeichert (`ma21_kopf_daten`), Reset bei jeder Veröffentlichung. Das Menü markiert die besuchte Seite (`aria-current`) statt immer „Aktuell“; bei Meldungen das Ressort, bei Terminen „Termine“, bei Vereinen „Vereine“.
- **`/api/sport-current.json`, `/api/editorial-current.json`:** `assets/v20.js` ruft beide auf jeder Seite ab, auf WordPress liefen sie ins Leere (404 in der Konsole). Der Spielstand kommt jetzt von der Vorschauseite oder aus dem Repository (15 Minuten Cache, Notkopie in `ma21_sport_current_kopie`), das Sportmodul in `/sport/` zeigt damit frische Ergebnisse ohne Theme-Lieferung. Die redaktionelle Übersteuerung antwortet leer (`hero: null`), denn auf WordPress entscheidet das Board „Startseite & Ressorts“.
- **Freigaben (Plugin):** Die Entwürfe aus dem Abgleich standen bisher nur unter „Beiträge → Entwürfe“; die Freigaben-Seite und der Zähler im Menü kannten sie nicht. Jetzt stehen sie dort mit Relevanz, Startseite ja/nein und „Freigeben“ (ein Klick, dann online und in der News-Sitemap), gekennzeichnet als „Meldung aus dem Abgleich“, mit Hinweis oben und im Menü-Zähler (`ma_freigaben_abgleich()`).
- **Abgleich zuverlässig angestoßen (Plugin 1.20.5):** Live war der nächste geplante Lauf um 14:05 Uhr noch um Mitternacht „nächster Lauf“: WordPress-Cron hängt an Seitenaufrufen, die hinter dem Cache des Hosters ausbleiben; ein direkter Aufruf von `wp-cron.php` holte den Lauf in 41 s nach. Jetzt: GitHub-Workflow `wordpress-abgleich.yml` (stündlich, nach jedem Build der Import-Datei mit fünf Minuten Wartezeit für den raw-Cache, von Hand) ruft `/?ma_api=abgleich-anstoss` (plant den nächsten Lauf auf jetzt, höchstens alle fünf Minuten, kein Geheimnis nötig) und dann `wp-cron.php`. Vor dem vollen Lauf vergleicht das Plugin den ETag der Import-Datei (`ma_abgleich_etag`); unverändert heißt eine Kopfanfrage statt 40 s. „Jetzt abgleichen“ läuft immer voll. Backend (Abgleich-Seite, Dashboard-Kachel) warnt, wenn der geplante Lauf mehr als 20 Minuten überfällig ist.
- **Dashboard-Kachel „Redaktion: Freigaben und Abgleich“:** wartende Meldungen aus dem Abgleich, Einreichungen, zuletzt veröffentlichte Meldung, letzter und nächster Abgleich-Lauf, jeweils mit Link. Die Freigabelogik bleibt: nichts geht ohne Klick der Redaktion online.

**Geprüft:** alle PHP-Tests, lokal (Kopfleiste, Ortswahl, `aria-current`, beide JSON-Adressen, Freigaben-Seite, Dashboard) und live nach dem Upload.

## Neu in Theme 21.9.8 / Plugin 1.20.3 (04.10.2026): Fehlerseite, Unternehmen nur mit Unternehmensbeiträgen, Kalenderdatei

- **Fehlerseite:** Eine unbekannte Adresse zeigte bisher „Alle Meldungen“ mit leerer Liste und „1 Meldung“ (das Theme hatte keine `404.php`, `index.php` lud `archive.php`). Jetzt eine eigene Seite wie `/404.html` der statischen Seite: „Diese Seite gibt es nicht“, Suchfeld, neueste Meldungen, Ressorts, Status 404. WordPress rät außerdem keine ähnliche Adresse mehr (`/ausgabe/merzenich/` landete bei einem Vereinsprofil); alte Adressen werden weiter über `ma_legacy_url` und den genauen Slug aufgelöst.
- **Unternehmen (`/unternehmen/`):** nur noch Beiträge der Unternehmen: gesponserte Beiträge (`ma_gesponsert`) und Beiträge der Unternehmens-Zugänge, höchstens vier je Unternehmen (Vorgabe Betreiber). Wirtschaftsnachrichten der Redaktion stehen nur noch unter `/wirtschaft/`. Das Ressort-Menü „Neu im Ressort“ für Unternehmen folgt derselben Regel. Solange kein Unternehmensbeitrag freigegeben ist, steht ein Hinweis mit den Unternehmensprofilen daneben.
- **Fußlinks der statischen Seite:** `/vereine/eintragen/` → `/meldung-senden/`, `/betriebe/eintragen/` → Anzeigen-Assistent (Unternehmenskanal). `/termine/kalender.ics` liefert jetzt die kommenden Termine als Kalenderdatei (iCalendar, Zeiten in UTC, Ort, Link), abonnierbar in Kalender-Apps.

**Geprüft:** `qa/wordpress/feeds-test.php` (Kalender), `seo-test.php` (Umleitungen), alle Tests; lokal und live: unbekannte Adresse → Fehlerseite mit Status 404, `/unternehmen/` ohne Wirtschaftsnachrichten, Kalenderdatei als `text/calendar`.

## Neu in Theme 21.9.7 (04.10.2026): Datenschutz-Dialog neu gestaltet

Der Hinweis zu Datenschutz und Einstellungen (`assets/einwilligung.js`, Styles in `korrekturen.css`, gilt für Vorschauseite und WordPress) sah nach Formular aus. Jetzt: Karte mit Monogramm, Zeile „Datenschutz“, Serif-Titel „Ihre Daten bleiben bei uns“, kurzer Text, zwei gleich große Knöpfe „Alle akzeptieren“ und „Nur notwendige“ (Annehmen und Ablehnen gleich leicht), darunter „Einstellungen anpassen“. In den Einstellungen stehen die beiden Optionen als Schalter: „Notwendig“ (immer an) und „Live-Meldungen“ (aus, bis man zustimmt; vorher war das Häkchen vorangekreuzt). Auf dem Handy als Bogen vom unteren Rand mit gestapelten Knöpfen. Verhalten, Speicherformat, Fußlink „Datenschutz-Einstellungen“ und Ereignis für die Push-Benachrichtigungen unverändert. Geprüft lokal (Desktop, Handy, beide Ansichten, Speichern, Wiederöffnen) und live.

## Neu in Theme 21.9.6 (04.10.2026): Ortsseiten wie die statische Seite

Auf `/ort/<ort>/` standen Termine und Vereinsprofile chronologisch in der Meldungsliste, ohne Bild, und verdrängten die Meldungen (Zähler „120 Meldungen“ zählte alles mit). Jetzt listet die Ortsseite nur Meldungen, mit Aufmacher und Bildern wie jede Ressortseite; die nächsten fünf Termine und die Vereine des Orts stehen wie auf der statischen Ortsseite in der Seitenleiste („Nächste Termine in …“, „Vereine in …“). Geprüft lokal und live auf `/ort/merzenich/` und `/ort/golzheim/`.

## Neu in 1.20.2 (04.10.2026): Abgleich ohne falsches Änderungsdatum

Ändern sich im redaktionellen Stand nur Importfelder oder Schlagworte (z. B. nach dem Wegfall des Kastens „Das Wichtigste in Kürze“ in der Import-Datei), speichert der Abgleich den Beitrag nicht neu: Änderungsdatum, „Aktualisiert am“, `dateModified` und Sitemap bleiben unberührt. Nur ein geänderter Titel, Text oder Auszug speichert den Beitrag neu. Lokal geprüft: 143 Beiträge mit geänderten Importfeldern, Änderungsdatum unverändert.

## Neu in Theme 21.9.3 bis 21.9.5 (04.10.2026): Kopf ohne Bruch beim Scrollen, Kasten „Das Wichtigste in Kürze“ entfernt

Beides Wunsch des Auftraggebers (04.10.2026), gilt für WordPress und die Vorschauseite (gemeinsames Stylesheet `assets/korrekturen.css`, Generator `deploy/meldungen.mjs`).

- **Kopf:** Auf dem Desktop klebte der große Kopf beim Scrollen erst am Browserrand, die Ressortleiste hing 84 px darunter, und nach wenigen Pixeln klappte der Kopf mit Animation weg. Jetzt scrollt der große Kopf wie Inhalt weg, und die Ressortleiste bleibt ohne Sprung ganz oben kleben (sticky, kein Umschalten auf fixed, keine Animation); das kleine Logo erscheint wie bisher. Eingeloggt bleibt die Leiste unter der WordPress-Admin-Leiste. Auf dem Handy bleibt der schmale Kopf oben, wie er war.
- **Eingeloggt (21.9.5):** `plattform.css` schob die Leiste für Eingeloggte auf 116 px (alte Regel für den klebenden großen Kopf); jetzt klebt sie bei 32 px direkt unter der Admin-Leiste, auf dem Handy der schmale Kopf bei 46 px.
- **Kleines Logo auch eingeloggt (21.9.4):** Die Ressortleiste klebt unter der WordPress-Admin-Leiste bei 32 px; `v20.js` schaltet das kleine Logo jetzt an, sobald die Leiste an ihrer Klebekante steht (vorher nur bei 0 px, also nie für Eingeloggte).
- **„Das Wichtigste in Kürze“** erscheint in keiner Meldung mehr (Theme `single.php`, statische Seiten). Das Feld `ma_facts` bleibt im Backend als Arbeitsgrundlage der Redaktion; auf der Vorschauseite bleiben belegte Fakten Pflicht in den Daten.

**Geprüft:** Scrollfolge auf merzenich-aktuell.de (Desktop 1400 px: Leiste bei jeder Scrollposition ab 86 px bei y = 0, Inhalt ohne Sprung; Handy unverändert), Artikelseite ohne Kasten, `kette.mjs --check`, alle `qa/wordpress/*-test.php`.

## Neu in Theme 21.9.2 (04.10.2026): Bilder im Ressort-Menü, Menü aus eigenen Beiträgen

Im aufgeklappten Ressort-Menü („Neu im Ressort“) fehlten die Bilder: Poolfotos liegen nicht im Theme, und der Server leitete jede fehlende `/assets/`-Datei auf die Vorschauseite um, die stundenlang nicht erreichbar war.

- **Fehlende `/assets/`-Dateien** beantwortet jetzt PHP (`ma21_asset`): zuerst die Mediathek (Medium mit passendem `ma_image_static_src`, Größenendung `-480/-800/-1200` wird auf die passende WordPress-Größe abgebildet; alle 308 Poolfotos liegen seit 1.20.1 dort), dann die Vorschauseite, wenn sie antwortet (alle fünf Minuten geprüft), sonst das Repository auf GitHub. Antwort ist eine Umleitung mit einer Stunde Cache. Dateien, die im Theme liegen, liefert weiterhin der Server direkt.
- **Ressort-Menü aus WordPress:** `assets/ressort-menue.json` kommt jetzt aus PHP. Gruppen und Links bleiben die der statischen Datei, „Neu im Ressort“ sind die vier neuesten veröffentlichten Meldungen der Rubrik (Aktuell: alle Meldungen; Termine: die nächsten vier Termine; Ressorts ohne eigene Rubrik: statische Liste, aber nur hier veröffentlichte Beiträge). Vorher zeigte das Menü die neuesten Meldungen der Vorschauseite, auch wenn sie hier noch nicht freigegeben waren (Link ins Leere).
- Nach dem Theme-Update schreibt das Theme die `.htaccess`-Regeln einmal neu (wie bisher bei jedem Versionswechsel).

**Geprüft:** lokales WordPress: Poolfoto-Pfad → Umleitung auf das Medium der Mediathek, Original-Pfad (`.jpg`) → dasselbe Medium, unbekannte Datei → Repository (Vorschauseite nicht erreichbar), `..`-Pfade → 404; Menü-JSON mit Meldungen und Terminen aus WordPress.

## Neu in 1.20.1 (04.10.2026): Bildpools laden aus dem Repository, wenn die Vorschauseite ausfällt

Medien → Bildpools → „Fotos übernehmen“ meldete am 04.10. 308-mal „Service Unavailable“, weil die Fotos nur von der Vorschauseite (Coolify) geladen wurden und die stundenlang nicht erreichbar war.

- **Zweite Quelle:** Jedes Poolfoto wird zuerst von der Vorschauseite, dann aus dem Repository auf GitHub (Stand von `main`, `chatgpt-site/`) geladen. Ein Host, der 5xx liefert oder keine Verbindung annimmt, wird im selben Lauf nicht noch einmal versucht. Der Abgleich (1.20.0) nutzt dieselbe Routine für Beitragsbilder.
- **Meldung statt Fehlerliste:** Die Seite sagt jetzt „Die Vorschauseite war nicht erreichbar; die Fotos kamen aus dem Repository“ beziehungsweise „Weder die Vorschauseite noch das Repository waren erreichbar, später erneut“ und zeigt höchstens drei Beispiel-Fehler. Sind alle Quellen ausgefallen, bricht der Lauf nach dem ersten Fehler ab. Die Pakete laufen weiter, solange ein Lauf etwas übernommen hat.

**Geprüft:** lokales WordPress mit ausgefallener Vorschauseite (Fotos kommen aus dem Repository, Nachweis und Rechteprüfung gesetzt), mit zwei toten Quellen (eine Meldung, Abbruch), alle `qa/wordpress/*-test.php`.

## Neu in 1.20.0 (04.10.2026): Abgleich mit dem redaktionellen Stand, Google News

Hintergrund in `docs/GOOGLE-NEWS.md`: Auf merzenich-aktuell.de war am 04.10. die neueste Meldung vom 29.09., weil die Import-Datei nur von Hand eingespielt wurde. Die News-Sitemap war leer; Google sah keine Nachrichtenseite.

- **Abgleich (Merzenich Aktuell → Abgleich, `includes/abgleich.php`):** liest stündlich die Import-Datei `wordpress-delivery/merzenich-aktuell-import.xml` aus dem Repository (Stand von `main`), legt neue Meldungen als Entwurf in die Freigaben und neue Termine veröffentlicht an, übernimmt Beitragsbilder mit Nachweis, Lizenz und Rechteprüfung (von der Vorschauseite, ersatzweise aus dem Repository) und aktualisiert Titel, Text, Auszug, Schlagworte und Importfelder, wenn sich der Stand geändert hat. Was in WordPress von Hand geändert wurde, bleibt (Vergleich über Text-Prüfsumme); Status, Freigaben, Hervorhebungen und ein von Hand gesetztes Bild werden nie angefasst; Papierkorb bleibt Papierkorb; gelöscht wird nichts. Zuordnung über `ma_legacy_url`, ersatzweise Slug. Höchstens 20 neue Beiträge je Lauf, Rest beim nächsten.
- **Einstellungen:** automatischer Abgleich an/aus, „Neue Meldungen sofort veröffentlichen“ (Standard aus: Entwurf in den Freigaben), eigene Adressen für Import-Datei und Bilder. Knopf „Jetzt abgleichen“, letzter Lauf mit Zahlen und Fehlern, Protokoll der letzten 20 Läufe. WP-CLI: `wp ma-abgleich lauf [--max=N]`, `wp ma-abgleich stand`. Jeder übernommene Beitrag bekommt einen Eintrag im Verlauf.
- **Google News:** Schrittfolge für Search Console (News-Sitemap eintragen) und Publisher Center in `docs/GOOGLE-NEWS.md`; Transparenz-Markup (Grundsätze, Korrekturen, Ethik, Vielfalt, Finanzierung, Impressum, Kontakt) ist vollständig und zeigt auf vorhandene Seiten.

**Nach dem Update im Backend:** Merzenich Aktuell → Abgleich → „Jetzt abgleichen“ (beim ersten Lauf werden alle vorhandenen Beiträge als bekannt übernommen, neue angelegt). Entscheiden, ob neue Meldungen sofort veröffentlicht werden sollen (siehe `docs/GOOGLE-NEWS.md`).

**Geprüft:** `php qa/wordpress/abgleich-test.php` (Lesen der echten Import-Datei, Entscheidung je Beitrag, Status, Quellen) und alle `qa/wordpress/*-test.php`; lokales WordPress 7.1.2 mit dem Stand von merzenich-aktuell.de: erster Lauf 18 neu (14 Meldungen, 4 Termine), 147 übernommen, 10 s; zweiter Lauf unverändert; geänderter Stand → aktualisiert; von Hand geänderter Text → nicht überschrieben; Papierkorb → nicht neu angelegt; Bilder mit Nachweis, Lizenz, Quelle und Rechteprüfung in der Mediathek.

## Neu in 1.19.1 (04.10.2026): Search-Console-Meldungen zu Navigationspfaden und Terminen

Google Search Console (04.10.2026) meldete auf vier Themenseiten (`/thema/polizei/`, `/thema/ausflug/`, `/thema/buergermeister/`, `/thema/staedtebaufoerderung/`) den kritischen Fehler „Feld ‚item‘ fehlt (in itemListElement)“ und auf Terminseiten die Hinweise `endDate`, `organizer.url`, `offers` und `performer` fehlen.

- **Navigationspfade:** Die Stufe „Thema“ verweist jetzt auf `/thema/` (Themenübersicht, erreichbar); nur die letzte Stufe bleibt ohne Link, wie bei allen anderen Seiten.
- **Termine (Event):** Ohne eingetragenes Ende endet der Termin am Starttag (`endDate`). Der Veranstalter bekommt seine Website (`organizer.url`), wenn sie bekannt ist: Gemeinde Merzenich, Kreis Düren, Feuerwehr Merzenich oder ein Verein aus dem Vereinsverzeichnis (Name gleich oder als ganze Wörter enthalten, ab 8 Zeichen); nichts wird geraten. Aus dem Preisfeld entsteht ein Angebot (`offers`): „frei/kostenlos/0 €“ = Preis 0 und `isAccessibleForFree`, eine Zahl mit Euro = dieser Preis; andere Angaben („Spende erbeten“) ergeben kein Angebot. `performer` bleibt bewusst weg (für Ratssitzungen und Feste gibt es keinen Künstler).
- **Danach in der Search Console:** bei beiden Meldungen „Fehlerbehebung überprüfen“ anklicken; Google prüft die Seiten dann innerhalb einiger Tage neu.

**Geprüft:** `php qa/wordpress/seo-test.php` (neue Fälle: Thema-Brotkrumen, Termin ohne Ende, Veranstalter-Link, Preis 0 und 12,50 €, keine Angabe, Vereinsverzeichnis), alle `qa/wordpress/*-test.php`, `kette.mjs --check`.

## Neu in 1.19.0 / Theme 21.9.0 (03.10.2026): Backend nach den KBS-Anforderungen

Vollständiger Abgleich je Anforderung: `docs/BACKEND-KBS-ABGLEICH.md`.

- **Zwei getrennte Freigaben:** Startseiten-Freigabe als eigenes Feld (`ma_startseite_freigabe`, wer und wann). Ohne Entscheidung steht eine neue Meldung nur in ihrer Rubrik; was vor der Umstellung veröffentlicht war (`ma_startseite_freigabe_seit`) oder mit älterem Datum importiert wird, behält die bisherige Regel. Theme fragt `ma_relevanz_startseite()`. Priorität 1–10 (`ma_prioritaet`) ordnet innerhalb derselben Relevanz.
- **Ablauf:** Archivieren / Wieder veröffentlichen als Zeilen- und Mehrfachaktion; Freigabeseite und Verlauf auch für Immobilien, Stellen, Trauer- und Familienanzeigen.
- **Artikeldaten:** Organisation, Dachzeile, SEO-Titel und -Beschreibung, öffentliches „Aktualisiert am“; „Hervorhebung bis“ beendet feste Plätze (stündliches Räumen, Hinweis im Board). Speichern fasst nur mitgeschickte Felder an.
- **Medien und Bildrechte:** Partner sehen nur eigene Medien; Rechteprüfung je Bild für die Redaktion; importierte Nachweise gehen beim Speichern leerer Felder nicht mehr verloren; Nachweis geht vom Bild an den Beitrag.
- **Bildpools:** Taxonomie `ma_bildpool`, Medien → Bildpools, 308 Fotos aus `data/bildpools.json` (Übernahme in Paketen, `wp ma-bildpools importieren`), Symbolbild aus dem Pool bei Veröffentlichung ohne eigenes Bild.
- **Einsendungen:** Stellen-Formular, Eingang mit Filter und „Als Entwurf übernehmen“.
- **Werbung und Rollen:** Kampagne über mehrere Plätze (`ma_ad_slot_weitere`), Rolle Werbepartner (Rollen-Version 3), Werbe-Priorität und Schaltung nur Redaktion.
- **Einladungsfunktion entfernt** (Entscheidung KBS): Zugänge ohne E-Mail.
- **Sicherheit:** Redaktionsfelder für Partner auch nicht löschbar; Zähl-Schnittstellen je Besucher begrenzt.

**Nach dem Update im Backend:** Medien → Bildpools → „Pools anlegen“, dann „Fotos übernehmen“ (läuft in Paketen von selbst weiter).

**Geprüft:** lokales WordPress 7.1.2 (SQLite) mit importierten Inhalten: 112 Integrationsprüfungen (Medien und Rechte, Freigaben, Artikeldaten, Einsendungen und Werbung, Bildpools mit Übernahme aller 308 Fotos); Startseite vor und nach dem Update gleich (131 Meldungen, Aufmacher); `php qa/wordpress/*-test.php`, `kette.mjs --check`, Routenmatrix.

## Neu in 1.18.2 / Theme 21.8.1 (03.10.2026): Handyansicht wie am 30.09.

Gemessen am Stand vom 30.09. (Theme 21.3.3) auf 320–1180 px Breite, statisch und live:

- **Kopf:** Seit 01./02.10. steht beim Wetter „Merzenich“. Ausgeblendet war das Wort nur unter 390 px, bei 390–479 px (iPhone 12–16, gängige Androids) lief „Merzenich · 15 °C“ über das Logo. Jetzt unter 480 px wie am 30.09. nur Symbol, Temperatur, Datum (unter 390 px ohne Symbol), Logo bei 390–409 px 180 px breit. Abstand Logo–Wetter auf allen Breiten mindestens 14 px (`chatgpt-site/assets/korrekturen.css`).
- **Werbebänder der Startseite:** `padding:12px 0` nahm `.shell` den Seitenrand; am Handy liefen Label, Linie und Anzeige randlos bis an den Bildschirmrand, am Desktop stand das Band 40 px links und rechts über den Inhaltsspalten. Jetzt so breit wie die Inhaltsspalten (`chatgpt-site/assets/werbung.css`).
- **Sportseite:** Drei Sportgrafiken (`wasser.svg`, `fitness.svg`, `breitensport.svg`) enthielten ein unmaskiertes `&` und waren damit kein gültiges SVG; der Browser zeigte leere Flächen. `&amp;` gesetzt, alle SVGs im Repository parsen.
- **Menü „SC 1919 Merzenich“** (`/sc-1919-merzenich/`) gab in WordPress 404, das Profil liegt unter `/vereine/sc-1919-merzenich/`. `ma_legacy_ziel()` leitet eine kurze Adresse jetzt per 301 auf das Vereinsprofil gleichen Namens (`qa/wordpress/import-test.php`).
- **Alte Vorlage** (Termine, Märkte, Trauer-, Familienanzeigen, Tipps): Überschrift „Stellen“ statt „Archiv: Stellen“ (`get_the_archive_title_prefix`).

## Neu in 1.18.1 (02.10.2026): Google-Bestätigungsdatei

Search Console, Methode „HTML-Datei“: Im Backend SEO & Geo den Dateinamen (`google…`) eintragen, das Plugin liefert `/google….html` mit dem verlangten Inhalt aus (`ma_seo_google_datei_name()`, `ma_seo_google_datei_inhalt()`, Rewrite `ma_seo=googledatei`). Nichts muss per FTP hochgeladen werden. Geprüft: `qa/wordpress/seo-test.php`, Live-Abruf der Datei.

## Neu in 1.18.0 (02.10.2026): Benachrichtigungen aufs Handy (Web Push, ohne Fremddienst)

**Was Leser bekommen** (`includes/push.php`, `assets/push.js`, `/sw.js`): Wer in den Datenschutz-Einstellungen „Live-Meldungen“ erlaubt und die Browser-Nachfrage nach Benachrichtigungen bestätigt, bekommt jede neu veröffentlichte Meldung als Benachrichtigung auf Handy oder Rechner, auch bei geschlossenem Browser (Android: Chrome, Firefox, Edge; iPhone: Safari ab iOS 16.4, wenn die Seite zum Home-Bildschirm hinzugefügt ist). Tippen öffnet die Meldung. Der bisherige Hinweis bei offener Seite (`/api/latest.json`) bleibt.

**Wie es läuft:** Das Plugin erzeugt einmalig ein VAPID-Schlüsselpaar (Option `ma_push_vapid`, privater Schlüssel bleibt auf dem Server). `push.js` registriert `/sw.js` (nur Push und Klick, kein Seiten-Cache), holt beim Browser das Push-Abo und meldet es an `ma/v1/push` (POST; DELETE beim Widerruf; höchstens 10 Anfragen je Abo und Stunde). Gespeichert werden in `{prefix}ma_push` nur Abo-Adresse (als Kennung ihr SHA-256), die zwei Schlüssel des Browsers, Zeitpunkte und ein Fehlerzähler; keine IP, kein User-Agent. Die erste Veröffentlichung einer Meldung (`transition_post_status`, nicht bei Änderungen) stößt per Cron-Einzelereignis den Versand an: Nutzlast Titel, Anriss (≤ 120 Zeichen), Link, Beitragsbild (nur mit Rechten, sonst Site-Icon), verschlüsselt nach RFC 8291 (aes128gcm) und signiert nach RFC 8292 (ES256), in Paketen zu 50 an den Push-Dienst des jeweiligen Browsers (Google, Mozilla, Apple). Antworten 404/410 löschen das Abo, nach 5 Fehlern ebenso. Ergebnis in `ma_push_letzter`, sichtbar im Backend SEO & Geo (Abonnenten, letzter Versand). Alles mit PHP-Bordmitteln (openssl, hash_hkdf); fehlt etwas davon, sagt das Backend es und das Skript wird nicht eingebunden.

**Datenschutz** (`includes/rechtstexte.php`, Fassung 2026-10-03): Abschnitt „Live-Meldungen und Benachrichtigungen“ beschreibt das Push-Abo, die beteiligten Push-Dienste, die gespeicherten Daten, die Rechtsgrundlage (Einwilligung) und den Widerruf; „Technische Schnittstelle“ nennt die Abo-Verwaltung. Der Hinweistext der Einwilligung (`einwilligung.js`) sagt jetzt, dass Benachrichtigungen auch bei geschlossener Seite kommen.

**Kein Testversand an Leser.** Der echte Empfang lässt sich nur auf einem Gerät prüfen: Betreiber erlaubt auf dem Handy „Live-Meldungen“ und die Benachrichtigungen; die nächste veröffentlichte Meldung ist der Test.

**Geprüft:** `php qa/wordpress/push-test.php` (Base64url, VAPID-Schlüssel, JWT mit `openssl_verify`, Verschlüsselung mit Rückweg, Abo-Prüfung, Kopfzeilen, Nutzlast) und alle übrigen Tests; Playground (Abo per REST, Versand bei Erstveröffentlichung mit HTTP-Stub, keine zweite Sendung bei Änderung, 410 löscht, `/sw.js`, Backend, Datenschutz); Live-Prüfung (`/sw.js`, Skript mit Schlüssel, Backend-Abschnitt).

## Neu in 1.17.0 / Theme 21.8.0 (02.10.2026): Ladezeit und Bilder, kurze Bildnachweise

**Bilder in der richtigen Größe** (`theme/inc/ma21.php` `MA21_SIZES`, `deploy/lib-artikel.mjs` `SIZES`): Die `sizes`-Angaben aller Bildkarten sind an den gerenderten Breiten gemessen (390/560/760/1100/1280/1440 px) statt geschätzt. Vorher lud das Handy für das 120 px breite Vorschaubild einer Rubrikseite die Breite `100vw` (eager, `fetchpriority="high"`), für Blaulicht-, Rathaus- und Wirtschaftskarten ab 1100 px die 600er statt 130–240 px; Lighthouse zählte rund 2 MB zu große Bilder je Seite. Blaulicht, Rathaus und Wirtschaft haben eigene Werte, weil `startseite.css` sie ab 1100 px anders anordnet. Dieselbe Tabelle gilt auf der statischen Seite.

**Zwischengrößen 480 und 240 px** (`includes/images.php` `MA_GROESSEN`): WordPress hatte nur 300, 768, 1024 und 1536 px; Vorschaubilder von 112 bis 240 px bekamen die 768er. Neue Uploads erhalten die Größen automatisch, vorhandene Anhänge zieht der bestehende Nachlauf nach (20 je Admin-Aufruf, Guard `ma_groessen_117`, zusammen mit der WebP-Erzeugung in `ma_bilder_nachlauf()`).

**Ein Stylesheet statt neun** (`deploy/css-bundle.mjs`, `vorlagen/kopf-assets.html`, `ma21_kopf_assets()`): WordPress lädt `bundle.css` (Unterseiten) bzw. `bundle-start.css` (Startseite, zusätzlich `startseite.css`), unverändert aneinandergehängt in der bisherigen Reihenfolge, versioniert über den Inhalts-Hash. `startseite.css` (52 KB) lud vorher auf jeder Seite, obwohl sie nur `body.home` gestaltet. Die statische Seite bleibt bei den Einzeldateien; `deploy/wp-theme.mjs` prüft, dass ihre Reihenfolge zur Bündelreihenfolge passt.

**LCP-Bild vorladen** (`wp_head`, Priorität 2): Startseite (Aufmacher), Meldung und Vereinsprofil geben `<link rel="preload" as="image" … imagesrcset imagesizes fetchpriority="high">` mit denselben Werten wie das `<img>` aus.

**Kurze Bildnachweise** (`ma_credit_kurz()` in `includes/images.php`, `creditKurz()` in `deploy/lib-artikel.mjs`): Commons-Floskeln („No machine-readable author provided. Papa1234 assumed (based on copyright claims).“), Wohnort („from Malmö, Sweden“), Benutzerkonto („(User:H-stt)“), Diskussionslink und Web-Adressen fallen in der Ausgabe weg, „(bearbeitet: …)“ wird „(bearbeitet)“, die Lizenz steht genau einmal am Ende, „Public domain“ heißt „gemeinfrei“. Gilt für Bildzeile, Karten, Feed (`media:credit`) und `inhalte.json`; die Rohdaten in `ma_image_credit` bleiben unverändert. Die Bildzeile der Karten nannte die Lizenz vorher doppelt.

**Geprüft:** `php qa/wordpress/images-test.php` (Kürzung mit echten Nachweisen, Nachlauf der Zwischengrößen) und alle übrigen Tests; `qa/pruefung.mjs` (keine lange Commons-Floskel mehr in Listen, Feeds, `inhalte.json`); Playground (sizes, srcset mit 480/240, Bündel, Preload, Pixelvergleich Bündel gegen Einzeldateien); Lighthouse mobil vor/nach dem Deploy; Live-Prüfung.

## Neu in 1.16.0 (02.10.2026): Nachrichtendienste und Browser-Hinweise automatisch beliefern

**Was eine neue Meldung jetzt von selbst auslöst** (`includes/feeds.php`): Der RSS-Feed (`/feed/`, Ressort-Feeds, Atom) trägt das Beitragsbild als `enclosure` und `media:content` mit Bildnachweis (nur Bilder mit geklärten Rechten, möglichst ≥ 1200 px), das Bild steht zusätzlich oben im Volltext, der Autor heißt „Redaktion Merzenich Aktuell“ statt des Anmeldenamens, der Feedtitel ist „Merzenich Aktuell“ bzw. „Blaulicht | Merzenich Aktuell“ (vorher „Archiv | …“). Der Feed nennt den WebSub-Hub `pubsubhubbub.appspot.com`; bei jeder Veröffentlichung und Änderung meldet das Plugin Haupt-, Atom- und Ressort-Feed dem Hub, Feed-Dienste (Feedly, Inoreader, NewsBlur und andere) laden sofort nach. Dazu IndexNow (1.15.0) und die News-Sitemap (1.14.0). `/api/latest.json` liefert die neuesten 20 Meldungen in der Form der statischen Datei; `einwilligung.js` (schon im Theme-Fuß) fragt sie alle fünf Minuten ab und zeigt mit Einwilligung eine Browser-Benachrichtigung, vorher 404. Vorschaubilder (`og:image`, JSON-LD) nehmen die größte Größe ab 1200 px (Google-Discover-Vorgabe).

**Was nur der Betreiber beantragen kann** (Backend SEO & Geo nennt die Schritte und Adressen): Google News über das Publisher Center (Website über die Search Console bestätigen, Feed `/feed/` eintragen), Microsoft Start Partner Hub (Edge-Startseite, Bing News), Apple News Publisher. Google Discover braucht keine Anmeldung, nur Indexierung und große Bilder.

**Geprüft:** `php qa/wordpress/feeds-test.php` und alle übrigen Tests; Playground (Feed mit Media-RSS, Hub, Autor, Titel; `/api/latest.json`; `og:image` ≥ 1200 px; Backend); Live-Prüfung.

## Neu in 1.15.0 / Theme 21.7.0 (02.10.2026): Indexierung vorbereitet, Vorschau zeigt auf die Live-Seite, IndexNow, WebP

**Befund.** Google kannte am 02.10. weder `merzenich-aktuell.de` noch die Vorschau `merzenichaktuell.hk-growthoperator.de` (`site:`-Abfragen leer); für „Merzenich Aktuell“ erscheinen Aachener Zeitung, presseportal.de, gemeinde-merzenich.de und dn-news.de. Die Live-Seite ist seit 30.09. online und bei keiner Suchmaschine angemeldet. Die Vorschau trug Canonical auf sich selbst, `index,follow` und eigene Sitemaps und hätte mit der Live-Seite konkurriert.

**Vorschau verweist auf die Live-Seite.** `deploy/site.json` → `canonicalUrl`; `deploy/lib-artikel.mjs` `liveUrl()` bildet Pfade ab, die WordPress anders führt (`/golzheim/` → `/ort/golzheim/`, `/autor/redaktion/` → `/redaktion/`, `/termine/melden/` → `/termin-melden/`, `/sc-1919-merzenich/` → `/vereine/sc-1919-merzenich/`). Blätterseiten, `/suche/`, `/vereine/eintragen/`, `/betriebe/eintragen/`, `/vereine/meldungen/` und `/unternehmen/<slug>/` behalten ihr eigenes Canonical (kein Live-Pendant). Jede indexierbare Vorschauseite nennt in `canonical` und `og:url` die Live-Adresse (`kopf-theme-einbinden.mjs`, abgeleitet aus dem Dateipfad; `meldungen.mjs`, `termine.mjs`, `unternehmen.mjs` schreiben dasselbe), JSON-LD bleibt die Vorschau-Entität. `robots.txt` der Vorschau nennt keine Sitemaps mehr; `sitemap-seiten.xml` enthält nur noch Seiten mit eigenem Canonical. `qa/routen.mjs` kennt den Wert `live`. Der Deploy-Workflow hat die Vorschau nach dem Push automatisch neu ausgeliefert (geprüft 02.10.: Canonical der Vorschau zeigt auf die Live-Seite).

**WordPress.** Kategorie „Menschen“ wird einmalig angelegt (`/menschen/` war 404, die Navigation verlinkt sie). Themenübersicht `/thema/` als Redaktionsseite mit `[ma_themen]` (17 Seiten). Vergangene Termine: Einzelansicht bleibt erreichbar (vorher 404 bei jedem alten Link und in der Sitemap), mit Hinweis „Dieser Termin ist vorbei“, ohne `eventStatus` „geplant“; die Terminliste zeigt weiter nur Kommendes (`ma_filter_public_service_archives` filtert nicht mehr `is_singular`). Startseite: Titel „Merzenich Aktuell: Nachrichten aus Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald“, Description 149 Zeichen (vorher ~250; Google zeigt ~155), die Langfassung bleibt in `llms.txt`.

**IndexNow.** Schlüssel `ma_indexnow_key` (32 Hex) einmalig erzeugt, Schlüsseldatei `/{key}.txt` per Rewrite; beim Veröffentlichen und Ändern von Meldungen, Seiten, Terminen und Vereinsprofilen meldet das Plugin die Adressen am Ende der Anfrage an `api.indexnow.org` (Bing, DuckDuckGo, Ecosia, Yandex; Google nimmt IndexNow nicht an). Letzte Antwort unter SEO & Geo. Kein Versand bei lokalem Host.

**WebP.** `image_editor_output_format` JPEG → WebP für neue Bildgrößen (Original bleibt JPEG), nur wenn die PHP-Installation WebP kann (`wp_image_editor_supports`). Vorhandene Anhänge werden beim Admin-Aufruf gestückelt nachgezogen (20 je Aufruf, Guard `ma_webp_fertig`, Sperre 120 s). Das Theme liest `srcset` aus den Metadaten und bekommt die WebP-Größen automatisch.

**Geprüft:** `php qa/wordpress/seo-test.php` (IndexNow-Nutzlast, Titel/Description, vergangener Termin), `seiten-test.php` (17 Seiten) und alle übrigen Tests; Playground-Integration; `kette.mjs` zweimal + `--check`, `qa/routen.mjs` 335 Routen ohne Fehler; Live-Prüfung.

**Nur der Betreiber kann:** Google Search Console (Property, Code unter SEO & Geo, Sitemaps einreichen, Startseite „Indexierung beantragen“), Bing Webmaster Tools, Google News Publisher Center, Links von gemeinde-merzenich.de und den Vereinen, namentliche Redaktion, regelmäßiges Veröffentlichen. Kein Google-Unternehmensprofil (keine Anschrift in Merzenich; Betreiber 02.10.2026).

## Neu in 1.14.0 / Theme 21.6.0 (02.10.2026): SEO und Geo, Redaktionsseiten, Kopfdatum

**Was fehlte.** WordPress gab nur den Titel und auf Einzelseiten ein Canonical aus: keine Description, kein Open Graph, keine strukturierten Daten, keine Geo-Angaben. Die Sitemap nannte Benutzer (Anmeldenamen) und die langen Kategorie-Adressen, Autorenarchive waren erreichbar, `/blaulicht/page/2/` und `/blaulicht/feed/` liefen auf 404, und jede Meldung sowie die Fußzeile verlinkten rund 15 Seiten, die es auf WordPress nicht gab (`/ueber-uns/`, `/grundsaetze/`, `/korrekturen/`, `/kontakt/`, `/meldung-senden/` …). Im Kopf standen SSI-Reste im `datetime`-Attribut.

**SEO-Modul (`includes/seo.php`).** Je Seitentyp Titel („… | Merzenich Aktuell“, Startseite wie die statische Seite), Description (Anriss, sonst Text; Ressorts, Orte und Listen mit den Texten der statischen Seite), Canonical auch für Startseite, Listen und Blätterseiten, Robots-Regel (`max-image-preview:large`; `noindex` für Suche, Datums- und Autorenarchive, Trauer- und Familienanzeigen), Open Graph und Twitter Card (Beitragsbild nur mit geklärten Rechten, sonst `og-default.jpg`), `article:*`-Angaben, Geo-Metas (DE-NW, Ortsname, Ortsmitte aus den Wetter-Einstellungen; Golzheim und Girbelsrath mit GeoNames-Koordinaten, Morschenich und Bürgewald ohne, weil keine belegte Quelle vorliegt). JSON-LD als Graph: NewsMediaOrganization (Anbieterin KBS Management GmbH mit Anschrift, Transparenzseiten, `sameAs` nur mit Eintrag), WebSite mit Suche, NewsArticle (Redaktion als Autor, Quelle als `citation`, Ortsbezug, `speakable`, `sponsor` bei gesponserten Beiträgen), CollectionPage mit ItemList für Ressorts, Orte und Themen, Place für Ortsseiten, Event für Termine, Organization/SportsClub für Vereinsprofile, AboutPage/ContactPage, BreadcrumbList. Dazu `/news-sitemap.xml` (Meldungen der letzten 48 Stunden, ohne gesponserte), die Standard-Sitemap ohne Benutzer und ohne Trauer-/Familienanzeigen, robots.txt mit Suchsperre und News-Sitemap, `/llms.txt` für KI-Suchen. Backend: Merzenich Aktuell → **SEO & Geo** (Social-Profile, Search-Console- und Bing-Code, Prüflinks).

**Adressen.** Ressorts laufen unter `/blaulicht/`, `/sport/` … mit Blättern und Feed; `/category/<ressort>/…` leitet per 301 dorthin, Kategorielinks (Sitemap, Feeds, REST) sind kurz. Autorenarchive → `/redaktion/`, Anhangsseiten → Beitrag, statische Adressen `/autor/redaktion/`, `/termine/melden/`, `/feed.xml`, `/atom.xml`, `/suche/` → WordPress-Adresse.

**Redaktionsseiten (`includes/seiten.php`).** 16 Seiten nach der Mechanik der Rechtstexte (anlegen, Fassung heben, manuell Geändertes schützen, Backend Merzenich Aktuell → Redaktionsseiten): Über uns, Redaktion (mit den jüngsten Meldungen), Publizistische Grundsätze, Korrekturen (mit Formular Typ `korrektur`), Kommentarrichtlinien (nach dem tatsächlichen Verhalten: Vorabmoderation, Mail bei Freigabe, keine IP), KI & Redaktion, Kontakt, Meldung senden, Termin melden, Werben, Unterstützen, Anzeigen mit Unterseite Anzeige aufgeben (vier Formulare), Archiv nach Monat, Diskussion (jüngste freigegebene Kommentare), WhatsApp-Kanal („noch nicht gestartet“). Formulare über `[ma_formular typ=…]` mit Eingang im Backend; das Formular trägt jetzt die Klasse `form` und den `btn`-Knopf der statischen Seite.

**Theme.** Kopfdatum serverseitig (`ma21_kopf()`, Platzhalter `{{ma:datum}}` aus `deploy/wp-theme.mjs` statt SSI), Autorenlinks auf `/redaktion/`, Termin-melden-Link auf `/termin-melden/`, Stile für Archiv- und Diskussionslisten.

**Geprüft:** `php qa/wordpress/seo-test.php` (JSON-LD je Seitentyp, Kürzen, robots.txt, News-Sitemap, llms.txt), `seiten-test.php` (Inhalte, Elternseite, Pflege) und die übrigen Tests; Playground-Integration (79 Prüfungen: Kopf je Seitentyp, Blättern und Feeds, Umleitungen, noindex, Sitemaps, robots, llms, alle 16 Seiten, Backend-Seiten, mobil ohne Überlauf).

**Nicht geändert, weil nur der Betreiber es kann:** Social-Profile (sameAs), Search-Console-/Bing-Verifizierung, Google Publisher Center, WhatsApp-Kanal-Link, Ortsteil-Koordinaten für Morschenich und Bürgewald. Die statische Vorschau `merzenichaktuell.hk-growthoperator.de` bleibt indexierbar (gleicher Inhalt wie die Live-Seite); ob sie `noindex` oder ein Canonical auf merzenich-aktuell.de bekommt, ist eine Entscheidung des Betreibers.

## Neu in 1.13.0 / Theme 21.5.0 (02.10.2026): Layout-Editor, Anzeigenformular, Werbung an, Rechtstexte, Kopf ohne Uhr

**Anordnen statt Dropdowns.** Merzenich Aktuell → **Startseite & Ressorts** ist ein Board, das die Seite spiegelt: Bühne (Aufmacher, Bühne 1–4, Anzeige), die Rubrikflächen mit ihren Reihen, bei Ressortseiten Aufmacher, Bildraster (Sport) und Reihen. Karten ziehen und auf einem anderen Platz ablegen (tauschen), **Ersetzen** (Auswahl mit Suche, Rubrik, Ortsteil), **Nochmal einsetzen** (dieselbe Meldung zusätzlich an anderer Stelle), **Verschieben nach …**, **Platz freigeben** (wieder automatisch), **Nur Rubrik**, **Neue Meldung** (Schnellformular an Ort und Stelle: Titel, Kicker, Anriss, Rubrik, Ortsteil, Text, Bild mit Bildrechte-Erklärung, Relevanz; veröffentlicht und setzt ein). Jede Aktion speichert sofort, „Rückgängig“ nimmt die letzte zurück. Dieselben Funktionen gibt es **direkt auf der Seite**: angemeldete Redaktion klickt in der WordPress-Leiste auf „Seite bearbeiten“ (Startseite und Ressortseiten), jede Karte bekommt eine Leiste mit Griff ⠿, Ersetzen, Nochmal, Freigeben, **Text** (Titel und Anriss direkt in der Karte ändern, Enter speichert) und Menü. Nach jeder Änderung tauscht das Skript nur die betroffenen Blöcke aus; nichts öffnet eine neue Seite.

Datenmodell: eine Layout-Karte je Seite (`ma_layout_startseite`, `ma_layout_ressort_<slug>`, `includes/layout.php`) mit den festen Plätzen; alles andere füllt die Automatik wie bisher. **Ein fester Platz gilt immer**, auch für eine Sportmeldung auf dem Aufmacher oder eine Meldung ohne Bild; das Board zeigt dafür Hinweise (gelb), keine Sperre. Harte Bedingung nur: veröffentlicht und nicht „Nur in der Rubrik“. Bestehende feste Plätze (`ma_startplatz`) wurden einmalig übernommen; die Box „Startseite“ im Beitrag kennt jetzt auch die Rubrikflächen und die eigene Ressortseite. REST: `ma/v1/layout/{seite}`, `ma/v1/meldung` (nur `edit_others_posts`, nie Partner).

**Anzeige anlegen auf einem Bildschirm.** Werbemittel (`ma_ad`) öffnen im klassischen Formular: Titel, Box „Werbeschaltung“ (Platz, Laufzeit, Sponsor, Ziel-URL, Priorität, Alternativtext, Vorschau), darunter „Werbemittel (Bild)“. Kein Block-Editor, kein Inhaltsfeld. Die IONOS-Designbibliothek und die KI-Werkzeuge (Plugin Extendify) sind über `extendify_load_library` abgeschaltet; die IONOS-Plugins bleiben installiert. Anzeigen stehen jetzt auch in den **Freigaben** („Freigeben“ = veröffentlichen + „Schaltung aktiv“). Die Einstellungsseite heißt **Werbung → Werbeplätze**.

**Werbung ist an.** `ma_ads_enabled` = 1 und alle Plätze eingeschaltet (einmalig beim ersten Admin-Aufruf; danach unter Werbeplätze änderbar). Die sechs Musterflächen der Startseite (Bänder 1–6, Servicespalte, Bühne), die Sport-Seitenspalte, die Unternehmensspalte und die Fläche unter dem Artikeltext sind an die Werbeverwaltung gebunden (`ma_ad_marker_slot()`, `ma_render_werbeplatz()`): läuft dort eine freigegebene Anzeige, ersetzt sie die Musteranzeige; sonst bleibt die Musteranzeige, jetzt mit dem Label „Musteranzeige“ (`deploy/anzeigen.json`). Neue Plätze: `homepage_buehne`, `sport_sidebar`, `business_sidebar`.

**Rechtstexte.** `/impressum/` und `/datenschutz/` sind Seiten der Installation (`includes/rechtstexte.php`, Fassung 2026-10-02): KBS Management GmbH als Anbieterin und Verantwortliche, Datenschutzerklärung nach der tatsächlichen Technik (Aufruf- und Besucherzählung mit Tages-Hash und 40 Tagen, Anzeigenzählung, Wetter über den eigenen Server, Formulare mit Eingang, Kommentare ohne IP-Speicherung, Zugänge und Verlauf, lokale Speicherung, Umleitung fehlender Dateien). Eine neue Fassung wird nur eingespielt, wenn der Text nicht von Hand geändert wurde (Merzenich Aktuell → Rechtstexte). Offen und nur dort sichtbar: § 18 Abs. 2 MStV-Person, AV-Vertrag IONOS, SMTP-Anbieter. Kommentare speichern keine IP-Adresse mehr (`pre_comment_user_ip`). *Stand 12.10.2026: Fassung 2026-10-06, die § 18-Person ist eingetragen (siehe 21.13.0); offen bleiben AV-Vertrag und SMTP-Anbieter (Checkliste B6).*

**Kopf.** Wettersymbol · Merzenich · Temperatur · Datum, ohne Uhrzeit, auch mobil (`v20.js`, `korrekturen.css`; `kopf-theme-einbinden.mjs` entfernt `data-clock` aus allen Seiten).

**Geprüft:** `php qa/wordpress/layout-test.php`, `rechtstexte-test.php` und die übrigen Tests; Playground-Integration (Board mit Ziehen, Auswahl, Schnellformular; Bearbeitungsmodus auf der Seite mit Ziehen und Inline-Text; Anzeige im klassischen Formular, Freigabe, Band 1 mit echter Anzeige, übrige Bänder Musteranzeige; `/impressum/`, `/datenschutz/`; Kopf ohne Uhr). Nicht in der Testinstanz prüfbar: Extendify (dort nicht installiert) und der Wetterabruf (kein Netz).

## Installiert auf merzenich-aktuell.de (IONOS, 30.09.2026)

WordPress 7.1.2, frisch installiert am 30.09. (nur Musterinhalte). Eingerichtet über das Konto „HK Growth“:

| Schritt | Stand |
|---|---|
| Sicherung vorher | WordPress-Export aller Inhalte, abgelegt außerhalb des Repos |
| Core-Plugin 1.7.0, Theme 20.5.1 | hochgeladen und aktiv (IONOS-Plugins und Theme „Extendable“ bleiben installiert) |
| Import | WordPress-Importer; 118 Meldungen als **Entwurf**, 15 Termine veröffentlicht, 70 Bilder, Seite „Service“; Autor „HK Growth“ |
| Permalinks | `/%category%/%postname%/`, Schlagwort-Basis `thema` |
| Allgemein | Untertitel „Internet-Zeitung für die Gemeinde Merzenich und Umkreis.“, Zeitzone Europe/Berlin |
| Geprüft | `/`, `/termine/`, `/termine/ortsfest-2026/`, `/service/`, `/blaulicht/`, `/merzenich/` → 301 `/ort/merzenich/`, je 1440 und 390 px ohne Überlauf |

Beim Import aufgefallen und im Generator behoben: Termine trugen den Beginn als Beitragsdatum (WordPress setzte kommende Termine auf „Geplant“), und zwei Anhänge mit gleichem Titel wurden als Dublette übersprungen. Die fünf betroffenen Termine und das fehlende Beitragsbild wurden auf der Seite direkt korrigiert.

Die Bilder lädt der Importer von der statischen Seite `merzenichaktuell.hk-growthoperator.de` (siehe `SITE_URL` in `deploy/wordpress-import.mjs`), nicht von merzenich-aktuell.de. *Überholt seit 21.10.1: Bildadressen und interne Links zeigen über `liveUrl()` auf merzenich-aktuell.de, die Bildpools laden aus dem Repository.* Die Import-Anfrage läuft länger als 30 Sekunden; bricht der Browser ab, arbeitet der Server weiter (Anzahl unter Beiträge/Medien prüfen).

Offen: Meldungen freigeben (Veröffentlichungssperre: Datum, Ort, Quelle und menschliche Prüfung je Beitrag), WordPress-Musterinhalte („Hello world!“, „Sample Page“), SMTP, Impressum und Datenschutz.

## Neu in 1.7.0: Parität mit dem statischen Stand (Audit 28.09.)

**Import (`wordpress-delivery/merzenich-aktuell-import.xml`).** Erzeugt von `deploy/wordpress-import.mjs` als letzter Schritt von `node deploy/kette.mjs`, aus dem, was die ausgelieferte Seite zeigt. Nicht von Hand pflegen. Stand heute: 111 Meldungen, 15 Termine, 70 Bilder als Anhänge, 1 Seite (Service).

| Inhalt | Im Import |
|---|---|
| Meldung | Beitrag, **Entwurf**; Kategorie = Ressort, `ma_location` = Ortsteil, Schlagworte = Themen; Text ohne Werbeflächen; `ma_facts` aus „Das Wichtigste in Kürze“ |
| Quelle | `ma_source_url`, `ma_source_publisher`, `ma_source_published_at` (wenn die Quelle ein Datum nennt), `ma_source_checked_at`, `ma_source_verified` = 1, weitere Quellen in `ma_source_more_urls` |
| Freigabe | `ma_date_verified`, `ma_place_verified`, `ma_human_reviewed` = 0. Die Veröffentlichungssperre des Plugins verlangt alle vier Häkchen; der Import behauptet nur, was der statische Stand belegt. 15 Meldungen ohne Ereignisdatum tragen den Stand 04.09. als Beitragsdatum und `ma_date_unknown` = 1. |
| Beitragsbild | Anhang (`wp:attachment_url` = Bild auf merzenich-aktuell.de), `_wp_attachment_image_alt`, `ma_image_credit`, `ma_image_license`, `ma_image_original_url`, `ma_image_provenance`, `ma_image_type`, `ma_image_rights_verified` |
| Termin | `ma_event`, veröffentlicht; `ma_event_start`/`ma_event_end` (Ortszeit), `ma_event_place`, `ma_event_organizer`, `ma_event_source_url` |
| Alte Adresse | `ma_legacy_url` an jeder Meldung und jedem Termin |

Bildrechte: 32 der 70 Bilder sind in der Bildbibliothek als geprüft vermerkt oder als Ortsansicht gesichtet. Die übrigen 38 sind Quellenbilder (Feuerwehr, Gemeinde, Polizei, Heimat-Info) ohne eingetragene Lizenz. WordPress zeigt dafür die gekennzeichnete Ersatzgrafik, bis die Redaktion „Bildrechte geprüft“ setzt. Das ist Absicht: Der statische Stand nennt die Urheber, eine Nutzungsfreigabe liegt uns für diese Bilder aber nicht schriftlich vor.

Wiederholter Import: Der WordPress-Importer überspringt Beiträge und Anhänge, die es mit gleichem Titel und Datum schon gibt. Ein zweiter Import legt also keine Dubletten an, überschreibt aber auch keine Änderungen aus WordPress. Die IDs in der Datei sind stabil (aus Adresse bzw. Bildpfad abgeleitet). Voraussetzung für den Bildimport: Die statische Seite ist unter merzenich-aktuell.de erreichbar, solange importiert wird.

**Adressen (`includes/permalinks.php`).** Die Aktivierung setzt Permalinks auf `/%category%/%postname%/` und die Schlagwort-Basis auf `thema`, aber nur, wenn noch nichts eingestellt ist. Damit liegen Meldungen unter `/<ressort>/<slug>/` wie im statischen Stand. Termine liegen unter `/termine/<slug>/`, Stellen unter `/jobs/` (vorher `/veranstaltungen/` und `/stellen/`). `/vereine/<slug>/` zeigt das Vereinsprofil, sonst die Meldung aus dem Ressort Vereine. Was es nur im statischen Stand gibt, leitet auf einer 404 per 301 weiter:

| Statisch | WordPress |
|---|---|
| jede Meldung, jeder Termin | über `ma_legacy_url` auf den aktuellen Permalink |
| `/blaulicht/`, `/rathaus/`, `/leben/` … | Kategorie-Archiv (`/category/<ressort>/`) |
| `/merzenich/`, `/golzheim/`, `/girbelsrath/`, `/morschenich/`, `/buergewald/` | Orts-Archiv (`/ort/<ort>/`) |
| `/thema/<slug>/` | Schlagwort-Archiv, gleiche Adresse |

Nach dem Aktivieren einmal Einstellungen → Permalinks speichern, falls das Hosting die Regeln nicht sofort schreibt.

**Rathaus und Abfall.** `data/gemeinde.json` im Plugin ist eine Kopie von `deploy/gemeinde.json` (die Kette hält sie gleich). Shortcode `[ma_gemeinde teil="rathaus"]` bzw. `teil="abfall"`; die importierte Seite „Service“ nutzt beide. Zeiten wie auf der statischen Seite: Dienstag geschlossen, Donnerstag bis 18 Uhr; Müllabfuhr Schönmackers, 02237 9742-4502.

**Bildkennzeichnung.** Ein lizenziertes Symbolbild bleibt „Symbolbild“ mit dem Hinweis „Kein Foto …“; vorher wurde es zu „Lizenziertes Bild“ und verlor den Hinweis. Neuer Bildtyp „Ortsansicht“.

**Wetter.** Standard wie der statische Dienst: 50.8317 / 6.5361, 10 Minuten Cache (gilt nur für neue Installationen; bestehende Einstellungen bleiben).

**Werbung.** Unverändert standardmäßig aus (`ma_ads_enabled` = 0). Ausgespielt werden nur veröffentlichte Werbemittel im Zeitfenster ihrer Laufzeit. Die Demo-Banner des statischen Stands sind nicht Teil des Imports.

**E-Mail (SMTP).** WordPress verschickt über `wp_mail()`. Ohne SMTP-Plugin (z. B. WP Mail SMTP) und Zugangsdaten des Betreibers gehen Freigabe-Mails an Kommentierende und Partner-Einladungen nicht raus; die Kommentar-Sammelfreigabe nennt dann die Zahl der gescheiterten Mails. Anbieter und Zugangsdaten gehören in die Hosting-Umgebung, nicht ins Repository. Der Anbieter muss in der Datenschutzerklärung genannt werden.

**Geprüft (lokal, ohne WordPress):** `php qa/wordpress/import-test.php` (Import gegen den statischen Stand, Weiterleitungen, Gemeindezeiten, Bildkennzeichnung) und die übrigen `qa/wordpress/*-test.php`. **Nicht geprüft:** ein Import in eine echte WordPress-Installation mit MariaDB; dafür fehlt hier das Zielhosting.

## Neu in 1.6.0

(Rückmeldung KBS 26.09.): Partner-Zugänge mit Freigabe-Mail und Ablehnungsgrund, Werbe-Kontingente für Unternehmen, Unternehmensprofile mit Einwilligung, Zugangsantrag per Shortcode `[ma_partner_antrag]`, rotierende Anzeigen je Werbeplatz und eine sportfreie Startseite. Einrichtung und Abläufe: `docs/PARTNER-ZUGAENGE.md`.

## Was erhalten und verbessert wurde

Die bestehende Generator-/WordPress-Basis wurde weiterverwendet. Kein Neustart, kein vollständiges Zurücksetzen. Der vorherige Recovery-Stand mit weißem Kopf, an der oberen Kante sitzender Navigation, getrennten Mannschaften, echten Vereinsbildern und redaktioneller Aufmacherbewertung bleibt die Grundlage. Die frühere Inline-Sportdarstellung wurde nicht wieder übernommen.

Der Kopf ist weiß, das Logo kleiner, die Suche mittig und Datum/Uhrzeit rechts. Datum und Uhrzeit werden für Berlin aktualisiert. Der Mehr-Bereich bündelt zusätzliche Navigation. Ort und Ressort sind getrennt; „MERZENICH“ erscheint rot, Ortsteile dunkel daneben. Umliegende Orte erhalten kein falsches Merzenich-Präfix. Normale Nachrichtenteaser besitzen sichtbare rot umrandete „Mehr lesen“-Links.

Die Startseite kombiniert die Nachrichtenbühne mit einer linken Serviceleiste. Veranstaltungen sind geöffnet, weitere Dienste geschlossen. Bei belegten Werbeplätzen ergänzt WordPress rechts eine sparsame Anzeigenspalte; ohne Werbung wird kein leerer Platz reserviert. Mobil stehen die Nachrichten vor den Diensten. Der Footer ist auf drei kompakte Gruppen verkleinert; Impressum, Datenschutz, Grundsätze und RSS bleiben erreichbar. Neuer Claim: „Internet-Zeitung für die Gemeinde Merzenich und Umkreis.“

## WordPress-Architektur

Das Theme enthält Präsentation und Vorlagen. Dauerhafte Inhalte und Funktionen liegen modular im Core-Plugin. Die älteren Metaschlüssel werden weiter unterstützt; kein Datenbank-Reset und kein automatisches Löschen bestehender Inhalte. Alte Ortsteilzuordnungen lassen sich in die neue hierarchische `ma_location`-Taxonomie übernehmen.

| Bereich | Umsetzung |
|---|---|
| Nachrichten | Native Beiträge, Quelle, Bildcredit/-typ, Datenstand, Prüfung, Aufmacherablauf, Priorität, Inhaltsart |
| Orte | `ma_location`, bestehendes `ma_district` bleibt erhalten |
| Termine | `ma_event`, Datums-/Orts-/Kategoriefilter, Einzelvorlage und Kalenderdatei |
| Vereine/Betriebe | Bestehende `ma_club` und `ma_business` |
| Immobilien | `ma_property`, Kaufen/Mieten, Preis, Zimmer, Flächen, Anbieter, Kontakt, Bilder, Ablauf |
| Stellen | `ma_job`, Unternehmen, Arbeitszeit, Beschäftigungsart, Bewerbung, Kontakt, Ablauf |
| Trauer | `ma_obituary`, Lebensdaten, Angehörige, Bestattung, Dokument, Ablauf; standardmäßig noindex |
| Familie | `ma_family_notice`, Anlass-Taxonomie, Datum, Bild/Text, Kontakt, dokumentierte Freigabe; standardmäßig noindex |
| Werbung | `ma_ad`, sechs Plätze, globaler/per-Slot Schalter, Laufzeit, Gerät, Priorität, Vorschau, sichere HTML-Auswahl |
| Wetter | Serveradapter, Cache, Timeout, Ausfallzustand, Provider-/Koordinaten-/Schlüsseleinstellungen |
| Kommentare | Native WordPress-Kommentare, Moderation, pro Beitrag/global abschaltbar, Richtlinien und Meldelink |
| Kommentarfreigabe | Jeder Kommentar wartet auf Freigabe. Kommentare → Sammelfreigabe: alle wartenden vorausgewählt, einzelne abwählen, „Alle ausgewählten genehmigen“ oder „Alle wartenden genehmigen“ (auch über mehrere Seiten à 100). Nach Freigabe E-Mail an den Verfasser; scheitert der Versand (kein SMTP), nennt die Seite die Zahl. |
| Partner-Rollen | Polizei, Feuerwehr, Rathaus, Verein, Sport, Unternehmen, Immobilien. Partner reichen nur ein (Status „Ausstehend“), veröffentlichen kann nur die Redaktion. Anlegen unter Benutzer → Partner-Zugänge. Die frühere gemeinsame Rolle „Blaulicht-Partner“ bleibt für bestehende Zugänge, wird für neue nicht mehr angeboten. |
| Sport | Getrennte Mannschaften, Score, vorhandenes SC-Logo, Tabelle, editierbarer Spielplan und Quellenstand |
| Quellenradar | Bestehende Inbox, Quellenzustände, Übernahme als Entwurf, kein automatisches Publizieren |
| Formulare | Native Verarbeitung mit Validierung, Nonce, Honeypot, Uploadbegrenzung und wp_mail |
| Portabilität | WordPress-Export für Inhalte; Konfigurationsexport ohne API-Schlüssel |

### Werbeplätze

Werbebänder zwischen den Rubriken der Startseite: `homepage_band_1` bis `homepage_band_6`. Weitere Plätze: `homepage_sidebar_top`, `homepage_sidebar_middle`, `homepage_tip`, `homepage_feed_1`, `article_inline_1`, `article_sidebar`, `header_billboard`.

Je Platz rotieren alle aktiven, freigegebenen Anzeigen (`ma_active_ads()`); `ma_active_ad()` liefert weiterhin die erste. Alle Plätze zunächst aus. Nachrichtenstrom frühestens nach vier Beiträgen. Keine Dummy-Kampagnen und keine erfundenen Reichweiten. Tracking-Kennungen sind interne Bezeichnungen, keine aktivierten Impression-/Klickzähler.

### KI-Transparenz

Das Beitrags-Panel dokumentiert KI-Einsatz, Einsatzart, menschliche Prüfung, Prüfer, Zeitpunkt, redaktionelle Verantwortung und Kennzeichnung. Generierte/manipulierte Texte erhalten in der konservativen automatischen Logik einen Hinweis, sofern die vollständige menschliche Prüfung und Verantwortung nicht dokumentiert sind. Ein manuelles „Nein“ überschreibt eine automatisch erforderliche Kennzeichnung nicht. Synthetische Medien behalten eine eigene Kennzeichnung unabhängig von der Textprüfung. Zusätzliche Medienfelder erfassen Fotograf, Quelle, Lizenz, Credit, Original-URL und Provenance-Nachweis.

Die Originaldateien werden mitgeliefert; WordPress-Bildderivate können Herkunftsmetadaten verändern. Eine vollständige C2PA-Validierung oder Signierung ist nicht implementiert. Der tatsächliche Medieneinsatz und die rechtliche Einstufung müssen durch die Redaktion geprüft werden.

Artikel 50 enthält insbesondere die Transparenzpflichten für entsprechende synthetische Medien und KI-generierte/-manipulierte Texte zu Angelegenheiten öffentlichen Interesses sowie die Ausnahme für menschlich kontrollierte Texte mit redaktioneller Verantwortung. Maßgeblich ist der offizielle Artikel-50-Text der EU-Kommission. Die technische Umsetzung ersetzt keine juristische Freigabe und behauptet keine vollständige EU-AI-Act-Konformität.

## Prüfungen

- Statischer Build erfolgreich; 42 öffentliche Artikel. Die sechs ungeprüften neueren Quellenentwürfe werden nicht öffentlich ausgespielt.
- Linkprüfung: 213 HTML-Seiten, 814 syntaktisch gültige JSON-LD-Blöcke, keine kaputten internen Links. Die technischen Admin-/Offline-Seiten sind keine indexierbaren Nachrichten.
- Bestehende Regressionstests für Entwurfsgrenzen, zeitgesteuerte Veröffentlichung, Aufmacherablauf, eindeutige Startseiten-IDs, Sportstruktur und bündigen Header bestanden.
- PHP-Parserprüfung aller Theme-/Plugin-Dateien bestanden; zusätzlich PHP-Laufzeitprüfung eingesetzt.
- Lokale WordPress-Playground-Installation: WordPress 7.1, PHP 8.3.33, SQLite-Testdatenbank. Core und Theme aktiviert; Inhaltstypen, Orte, Standard-Werbe-Aus, Wetter-Ausfallzustand, Entwurfsschutz, KI-Hinweise, Sportausgabe, Verwaltungsseiten und Startseite getestet. Bei diesem Durchlauf kein verbleibender PHP-Fehler.
- ZIP-Struktur und Archivintegrität werden beim Paketbau geprüft. Die Pakete besitzen jeweils genau einen installierbaren Hauptordner.
- Abschließender Test am 14. September: Installation beider tatsächlicher ZIPs über den WordPress-Installer, WXR-Import, Freigabe eines geprüften Testartikels, Einzelartikel, Archiv, Suche, Veranstaltungskalender und die vier Marktarchive erfolgreich. Zusätzlich aktive/abgelaufene Anzeigen, HTML-Bereinigung und abgelaufene Marktangebote geprüft. Kein verbleibender PHP-Fehler; der vollständige Ergebnisdatensatz liegt im Projekt unter `docs/QA-WORDPRESS.json`.

Dies ist kein Nachweis für jede Hostingkonfiguration. MySQL/MariaDB, SMTP, reale Wetteranbindung, echte Kommentareinsendungen, Browser-/Responsive-/Lighthouse-Abnahme und Rechte-/Rechtsprüfung auf dem Zielhosting stehen noch aus. Es wurde kein Browser oder Gerät von Luis übernommen. Vollständige Pixelgleichheit zwischen Vorschau und WordPress ist noch nicht visuell abgenommen; sie verwenden dieselben Stil- und Interaktionsgrundlagen, aber getrennte datengebundene Vorlagen.

## Externe Voraussetzungen und Entscheidungen

1. Zielhosting und WordPress-Zugang; E-Mail-/SMTP-Einrichtung und Cron. Ohne SMTP gehen die Freigabe-Mails an Kommentierende und die Einladungen an Partner nicht raus.
2. Bestätigte KBS-Betreiberdaten und redaktionell Verantwortliche; finale Rechtsprüfung von Impressum, Datenschutz, Kommentaren, Anzeigen und KI-Prozess.
3. Bild-/Quellennutzungsrechte und redaktionelle Freigabe der importierten Nachrichten. Es wurden keine aktuellen Nachrichten erfunden oder ungeprüfte Entwürfe veröffentlicht.
4. Wetterprovider und für den Einsatz geeigneter Tarif. Eingebaut ist Open-Meteo; weitere Provider benötigen einen Adapter über den bereitgestellten Filter.
5. Webcamquelle, Copyright und Einbettungsrecht. Derzeit nur ein konfigurierbarer externer Link, kein Live-iframe/Stream.
6. Tatsächliche Werbekunden und Werbemittel, Marktangebote und Moderationsprozess. Die Bereiche sind absichtlich ohne Fake-Angebote.
7. Unterstützung/Zahlung: noch keine Zahlungsintegration; Betreiber-, Steuer- und Datenschutzprüfung davor erforderlich.

## Dateien

Dokumentierter Lieferstand:

- `merzenich-aktuell-theme.zip`
- `merzenich-aktuell-core.zip`
- `merzenich-aktuell-import.xml`
- `README-INSTALLATION.md`
- diese Übersicht

Die produktive WordPress-Installation ist unabhängig von der ChatGPT-Vorschau.
