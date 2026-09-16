# Merzenich Aktuell – WordPress installieren

Lieferstand: 14. September 2026 · Theme 20.2.0 · Core 1.0.0

## Pakete

- `merzenich-aktuell-core.zip`: Inhaltstypen, Orte, Redaktion, Märkte, Werbung, Wetter, KI-Transparenz und Formularverarbeitung.
- `merzenich-aktuell-theme.zip`: Layout, Vorlagen, Navigation, Mobilansicht, Quellenboxen, Kommentare und Sportdarstellung.
- `merzenich-aktuell-import.xml`: optionale Übernahme der bestehenden Inhalte.
- `WORDPRESS-LIEFERUNG.md`: Funktionsumfang, Grenzen und offene Voraussetzungen.

Die Vorschauseite ist kein CMS und keine Voraussetzung für WordPress. Die Pakete laden keine Assets oder APIs von der ChatGPT-Domain.

## 1. Voraussetzungen und Sicherung

Eine aktuelle WordPress-Installation mit PHP ab 8.1, HTTPS und regulärer WordPress-Datenbank verwenden. Empfohlen: PHP 8.3 oder neuer, 256 MB PHP-Speicherlimit und mindestens 32 MB Upload-Limit. Vor einem Upgrade Datenbank und `wp-content` sichern. Änderungen zuerst auf einer Testinstallation vornehmen.

Bei einer vorhandenen älteren Merzenich-Installation zuerst vorübergehend ein WordPress-Standardtheme aktivieren. Das bisherige Theme enthielt bereits die Core-Funktionen; beide Fassungen dürfen nicht gleichzeitig geladen werden. Beiträge und Metadaten werden dabei nicht gelöscht.

## 2. Installation

1. **Plugins → Neues Plugin hinzufügen → Plugin hochladen:** `merzenich-aktuell-core.zip` auswählen, installieren und aktivieren.
2. **Design → Themes → Neues Theme hinzufügen → Theme hochladen:** `merzenich-aktuell-theme.zip` auswählen. Bei bestehendem Theme nach Sicherung ersetzen. Anschließend aktivieren.
3. **Einstellungen → Allgemein:** Sprache Deutsch, Zeitzone **Berlin**. Titel „Merzenich Aktuell“.
4. **Einstellungen → Permalinks:** `/%category%/%postname%/` setzen und speichern. Die CPT-Archive verwenden eigene stabile Pfade.
5. **Einstellungen → Lesen:** „Deine letzten Beiträge“ für die Nachrichtenstartseite auswählen. Bis zur inhaltlichen und rechtlichen Freigabe Suchmaschinenindexierung deaktivieren und die Testinstallation zusätzlich gegen unberechtigten Zugriff schützen.

Die Installation legt Orte und einige Service-/Transparenzseiten an. Vorhandene gleichnamige Seiten werden nicht überschrieben. Es werden keine Testnachrichten, Werbekampagnen oder Immobilienangebote angelegt.

## 3. Optionaler Import

Unter **Werkzeuge → Daten importieren → WordPress** den offiziellen WordPress-Importer installieren und `merzenich-aktuell-import.xml` auswählen. Den Autor einem echten Redaktionskonto zuordnen. Vor dem Import eine Sicherung erstellen; einen Import nicht unkontrolliert mehrfach ausführen.

Alle importierten Nachrichten bleiben **Entwürfe**. Originaldaten, Bildcredits und Quellen werden übernommen, sind aber keine automatische Nutzungserlaubnis. Vor Veröffentlichung Bildrechte, Text, Datum und Autor prüfen und die redaktionellen Freigaben setzen. Die bereits bekannten Termine und Vereinsinformationen werden mit übernommen; abgelaufene Termine erscheinen nicht im kommenden Kalender.

Die gelieferten Quellbilder liegen im Core-Plugin. Neue Bilder in der WordPress-Mediathek verwalten. Für die Übernahme der vorhandenen Ortsteilzuordnungen **Merzenich Aktuell → Dashboard → Vorhandene Ortsteile nach „Orte“ übernehmen** verwenden. Der Vorgang verarbeitet jeweils bis zu 200 Inhalte; bei größeren Beständen erneut ausführen. Die alte Taxonomie wird nicht gelöscht.

## 4. Navigation und Seiten

Unter **Design → Menüs** die Hauptnavigation anlegen und „Hauptnavigation (Ressorts)“ zuordnen: Aktuell, Blaulicht, Sport, Termine, Vereine, Rathaus & Politik, Leben, Wirtschaft, Menschen. Termine auf `/veranstaltungen/` und Vereine auf `/vereine/` verlinken. Die eingebaute Mehr-Navigation bündelt Service und Redaktion; bei Platzmangel wandern zusätzliche Menüpunkte hinein.

Der Import enthält Archiv und SC-Vereinsseite mit den richtigen Seitenvorlagen. Kontakt-, Impressums-, Datenschutz-, Redaktion-, Korrektur- und Formularseiten aufrufen und prüfen. Bei abweichenden bestehenden Slugs Weiterleitungen oder passende Menüpunkte setzen; keine relevanten Alt-URLs ersatzlos entfernen.

## 5. Redaktion

Beiträge werden über den normalen WordPress-Editor erstellt. Titel, Text, Auszug, Bild, Ressort und Ort pflegen. Die Panels „Redaktionsdaten“, „Veröffentlichung & Aufmacher“ und „KI & redaktionelle Transparenz“ enthalten Quelle, Bildtyp, Credit, Freigaben und Priorität.

WordPress verwaltet Entwurf, ausstehende Prüfung, geplant und veröffentlicht. Nur veröffentlichte Beiträge werden öffentlich abgefragt. Neuveröffentlichungen ohne erforderliche Quellen-/Bild-/Textprüfung bleiben Entwurf. Vor einer geplanten Veröffentlichung wird erneut geprüft. Vorschauen nur im authentifizierten WordPress-Zugang verwenden; es gibt keine ungeschützten Entwurfslinks.

Aufmacher laufen regulär nach 48 Stunden aus. Eine ausdrückliche Verlängerung oder Fixierung ist möglich; aktuelle Nachrichten haben bei der automatischen Auswahl Vorrang. Die Aktualisierung eines Textes allein macht ihn nicht zu einer neuen Nachricht. Das aktuelle Datum im Kopf ist vom redaktionellen Veröffentlichungsdatum getrennt.

## 6. Märkte und Kommentare

Immobilien, Stellen, Traueranzeigen und Familienanzeigen besitzen eigene Verwaltungsbereiche. Die eigentliche Beschreibung bleibt im Editor; Bilder und Galerien in der Mediathek. Ort über „Orte“ zuordnen. Kontaktfelder sind öffentlich: nur zur Veröffentlichung freigegebene Angaben eintragen. Ablaufdaten verwenden. Familienanzeigen brauchen eine dokumentierte Freigabe. Trauer- und Familienanzeigen sind standardmäßig `noindex` und aus der WordPress-Sitemap ausgenommen; dies ist kein Zugangsschutz.

**Einstellungen → Diskussion** steuert Moderation, E-Mail-Pflicht, Registrierung und Benachrichtigungen. Beim ersten Aktivieren wird Vorabmoderation eingeschaltet. Unter **Merzenich Aktuell → Einstellungen** lassen sich Kommentare zusätzlich global abschalten, pro Beitrag im Diskussionspanel. E-Mail-Adressen werden nicht im Kommentar veröffentlicht. Externe Avatare sind in der Kommentarliste ausgeschaltet. Datenschutz, Aufbewahrung und Moderationsabläufe vor dem Start festlegen.

## 7. Werbung

Unter **Merzenich Aktuell → Werbung** Werbemittel anlegen: Titel, Bild/URL oder begrenztes sicheres HTML, Sponsor, Zieladresse, Alt-Text, Gerät, Start, Ende und Platzierung. Veröffentlichen und „Aktiv“ setzen. Unter **Einstellungen** anschließend den globalen Schalter und den gewünschten Platz aktivieren. Standardmäßig ist alles aus.

Verfügbare Plätze: `homepage_sidebar_top`, `homepage_sidebar_middle`, `homepage_feed`, `article_inline`, `article_sidebar`, `header_billboard`. Nicht alle gleichzeitig belegen. Eine Anzeige im Nachrichtenstrom erscheint frühestens nach vier Beiträgen. Nicht belegte/deaktivierte Plätze erzeugen keinen Wrapper. Anzeigen sind ausdrücklich gekennzeichnet. Es werden keine Werbeprofile, Klicktracker oder Drittanbieter-Skripte eingerichtet.

## 8. Wetter und Webcam

Unter **Merzenich Aktuell → Wetter** Provider, Ort, Koordinaten und Cache konfigurieren. Wetter ist zunächst deaktiviert. Der eingebaute Open-Meteo-Adapter ruft ausschließlich vom WordPress-Server ab, mit Timeout und Fehlercache. Für kommerzielle Nutzung einen passenden Tarif/Nutzungsumfang prüfen. Andere Anbieter können über den Filter `ma_weather_provider` eingebunden werden; ein eingetragener Schlüssel allein installiert keinen weiteren Adapter.

API-Schlüssel werden nur serverseitig als WordPress-Option gespeichert, nicht ins Frontend oder Konfigurationsexport geschrieben. Datenbank/Backups entsprechend schützen. Bei Ausfall bleibt eine klare Nichtverfügbarkeitsanzeige; es wird keine Temperatur erfunden.

Webcams sind standardmäßig deaktiviert. Eine freigegebene URL mit Anbieter und Credit kann als bewusster externer Link eingebunden werden. Kein automatisches Scraping oder Drittanbieter-iframe. Ein direktes Live-Embed ist bewusst noch nicht freigegeben; dafür werden konkrete Quelle, Einbettungsrecht und Datenschutzentscheidung benötigt.

## 9. Sport, Quellenradar und Cron

Tabelle unter **Sport**, letzte Ergebnisse und kommende Ansetzungen unter **Ergebnisse & Spielplan** pflegen. Nur belegte Werte eintragen; den Quellenstand aktualisieren. Es findet kein automatischer Tabellenabruf von FUSSBALL.DE statt.

Der Quellenradar erstellt Kandidaten, keine Veröffentlichungen. Erst aktivieren, wenn die Quellennutzung geprüft ist. WordPress-Cron benötigt Seitenaufrufe; auf produktiven Installationen einen echten Hosting-Cron im Abstand von etwa fünf Minuten für `wp-cron.php` einrichten. Dieser bedient geplante Artikel, den optionalen Radar und ablaufende Anzeigen. Anweisungen des Hosters beachten.

## 10. Formulare und Mailversand

Unter **Kontakt & Versand** das Redaktionspostfach eintragen. Ohne eigenen Empfänger wird die WordPress-Administratoradresse verwendet. Die mitgelieferten Formulare nutzen `wp_mail`, Nonces, Honeypot, Zeitprüfung, Feldvalidierung und eingeschränkte Uploads. Für verlässliche Zustellung SMTP/Maildienst einrichten, SPF/DKIM/DMARC prüfen und echte Testeinsendungen durchführen. Die Vorschauseite bereitet lediglich E-Mail-Entwürfe vor; WordPress verarbeitet Formulare eigenständig.

## 11. Freigabe vor dem öffentlichen Start

- KBS-Betreiberangaben, redaktionell Verantwortliche, Datenschutz und Rechtefreigaben bestätigen.
- KI-Prozess nach Artikel 50 rechtlich prüfen; die Software ist keine Konformitätsbescheinigung.
- Wettertarif, Webcamrechte und gegebenenfalls Consent bestimmen.
- Formulare, Kommentarbenachrichtigungen, Uploads, Cron, HTTPS, Backups und Wiederherstellung auf dem Zielhosting testen.
- Mobil-/Tablet-/Desktop-Abnahme, Barrierefreiheit und Leistung auf dem Zielhosting durchführen.
- Suchmaschinenfreigabe erst danach aktivieren; Sitemap und News-Sitemap prüfen.

Keine Zahlungsintegration für Unterstützung ist aktiv. Betreiber-, Steuer- und Datenschutzfragen müssen davor geklärt werden.
