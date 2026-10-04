# Merzenich Aktuell – Lieferung und Prüfstand

4. Oktober 2026 · Theme 21.9.6 · Core-Plugin 1.20.2 (Historie unten)

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

**Rechtstexte.** `/impressum/` und `/datenschutz/` sind Seiten der Installation (`includes/rechtstexte.php`, Fassung 2026-10-02): KBS Management GmbH als Anbieterin und Verantwortliche, Datenschutzerklärung nach der tatsächlichen Technik (Aufruf- und Besucherzählung mit Tages-Hash und 40 Tagen, Anzeigenzählung, Wetter über den eigenen Server, Formulare mit Eingang, Kommentare ohne IP-Speicherung, Zugänge und Verlauf, lokale Speicherung, Umleitung fehlender Dateien). Eine neue Fassung wird nur eingespielt, wenn der Text nicht von Hand geändert wurde (Merzenich Aktuell → Rechtstexte). Offen und nur dort sichtbar: § 18 Abs. 2 MStV-Person, AV-Vertrag IONOS, SMTP-Anbieter. Kommentare speichern keine IP-Adresse mehr (`pre_comment_user_ip`).

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

Die Bilder lädt der Importer von der statischen Seite `merzenichaktuell.hk-growthoperator.de` (siehe `SITE_URL` in `deploy/wordpress-import.mjs`), nicht von merzenich-aktuell.de. Die Import-Anfrage läuft länger als 30 Sekunden; bricht der Browser ab, arbeitet der Server weiter (Anzahl unter Beiträge/Medien prüfen).

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
