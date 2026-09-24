# Merzenich Aktuell – Lieferung und Prüfstand

24. September 2026 · Theme 20.4.0 · Core-Plugin 1.4.0

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

`homepage_sidebar_top`, `homepage_sidebar_middle`, `homepage_feed`, `article_inline`, `article_sidebar`, `header_billboard`.

Alle zunächst aus. Nachrichtenstrom frühestens nach vier Beiträgen. Keine Dummy-Kampagnen und keine erfundenen Reichweiten. Tracking-Kennungen sind interne Bezeichnungen, keine aktivierten Impression-/Klickzähler.

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
