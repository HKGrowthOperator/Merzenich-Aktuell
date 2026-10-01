# Merzenich Aktuell – Lieferung und Prüfstand

2. Oktober 2026 · Theme 21.5.0 · Core-Plugin 1.13.0 (Historie unten)

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
