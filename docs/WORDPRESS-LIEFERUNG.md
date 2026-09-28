# Merzenich Aktuell – Lieferung und Prüfstand

28. September 2026 · Theme 20.5.1 · Core-Plugin 1.7.0

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
