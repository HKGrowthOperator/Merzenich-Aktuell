# Merzenich Aktuell — WordPress-Theme v18

Lokalmedien-Theme für die Gemeinde Merzenich. WordPress ist das dauerhafte Zielsystem für den
Livebetrieb; die parallele Netlify-Version dient nur der Vorabsicht. Beide teilen dieselbe
Bildsprache und dieselben redaktionellen Bausteine.

## Erst lokal ansehen

Im Wurzelverzeichnis dieses Pakets liegt `lokal/setup-localhost.sh`. Das Skript baut eine
vollständige WordPress-Installation mit diesem Theme und allen Startinhalten auf dem eigenen
Rechner auf, auf SQLite, ohne Docker und ohne MySQL:

```bash
bash lokal/setup-localhost.sh
MA_PORT=8080 php -S 127.0.0.1:8080 -t lokal/instanz/wp
```

Website unter `http://127.0.0.1:8080/`, Verwaltung unter `/wp-admin/` mit
`redaktion` / `merzenich`.

## Installation

1. Ordner `merzenich-aktuell` als ZIP packen und unter **Design → Themes → Theme hinzufügen → Theme hochladen** installieren.
2. Theme aktivieren. Beim Aktivieren legt das Theme die fünf Ortsteile und die Ressort-Kategorien selbst an.
3. **Werkzeuge → Daten importieren → WordPress** und `starter-content-v17.xml` einspielen. Das bringt 42 Meldungen, 12 Termine, 11 Vereine und 21 Seiten mit allen Redaktionsfeldern (automatisch aus dem aktuellen Datenbestand erzeugt, siehe unten „Starter-Content neu erzeugen").
4. **Einstellungen → Lesen**: Startseite auf „Eine statische Seite" stellen ist *nicht* nötig — `front-page.php` greift automatisch.
5. **Einstellungen → Permalinks** einmal speichern, damit `/news-sitemap.xml` erreichbar wird.
6. **Einstellungen → Allgemein**: Sprache der Website auf **Deutsch** stellen, sonst zeigt
   WordPress englische Wochentags- und Monatsnamen.
7. **Design → Menüs**: Menü „Hauptnavigation" anlegen und die Ressorts plus Veranstaltungen, Vereine, Stellenmarkt und Betriebe einhängen. Ohne Menü zeigt die Navigation automatisch alle Kategorien.
8. **Design → Merzenich Aktuell**: die Redaktionsadresse für die Formulare, die beiden Werbekunden (Name, Ziel-URL, Bildmotiv) und den WhatsApp-Kanal-Link eintragen.

## Eine Meldung veröffentlichen

Beitrag anlegen, dann im Block **Merzenich Aktuell — Redaktion, Quelle und SEO** ausfüllen:

| Feld | Wofür |
|---|---|
| Dachzeile | Das kleine rote Wort über der Überschrift |
| Unterzeile | Der Vorspann unter der Überschrift, auch als Teaser in Listen |
| Das Wichtigste in Kürze | Eine Zeile je Punkt, erscheint als Kasten mit goldenem Balken |
| Quellenname und Quellen-URL | Pflicht. Steht sichtbar unter dem Artikel |
| Datenstand der Quelle | Wann die Quelle veröffentlicht wurde |
| Externes Quellenbild | Nur nutzen, wenn kein eigenes Bild in der Mediathek liegt |
| Bildcredit und Bildbeschreibung | Pflicht. Credit steht in der Bildunterschrift, die Beschreibung ist der Alt-Text |
| Bildtyp | Originalbild, Veranstaltungsbild, Quellenmotiv, Archivbild oder Vereinslogo |
| Format | Leer für eine normale Meldung, sonst Familienanzeige, Nachruf, Kolumne, Tipps, Interview oder Firmenporträt — erscheint als Kennzeichnung am Artikel |
| Ohne Datum / Abrufdatum | Für Pressemitteilungen der Gemeinde ohne eigenes Veröffentlichungsdatum. Zeigt „Ohne Datum · abgerufen …" statt eines Zeitstempels, läuft als `Article` ohne `datePublished` und nicht in der News-Sitemap |

Rechts zeigt der **Publish-Check** in Rot, was noch fehlt. Er blockiert nicht, er erinnert.

Drei Schalter steuern die Startseite:

- **Aufmacher der Startseite** — der große Artikel oben. Es kann nur einen geben, das Theme schaltet den vorherigen automatisch ab.
- **Eilmeldung im Ticker** — das rote Band unter der Navigation.
- **Top-Thema** — fließt in „Meistgelesen", solange noch keine Zugriffszahlen vorliegen.

## Termine, Vereine, Stellenmarkt, Betriebe

Vier eigene Inhaltstypen im Menü links:

- **Veranstaltungen** — Beginn, Ort und Veranstalter sind Pflicht, daraus baut das Theme das Event-Schema für Google. Jede Terminseite bietet einen „Zum Kalender hinzufügen"-Link (.ics). Vergangene Termine verschwinden automatisch aus Kalender und Sidebar. Der Monatskalender auf der Startseite markiert alle Tage mit Termin.
- **Vereine** — das Verzeichnis zeigt ein A–Z-Sprungregister.
- **Stellenanzeigen** (`/jobs/`) — Betrieb, Art (Vollzeit/Teilzeit/Minijob/Ausbildung/Ehrenamt), Veröffentlicht, Bewerbungsschluss, Beginn. Jede Anzeige trägt JobPosting-Schema für Google. Erscheinen auch im Anzeigenmarkt auf der Startseite.
- **Betriebe** (`/betriebe/`) — Kategorie, Website, Telefon, LocalBusiness-Schema.

Beide neuen Verzeichnisse sind zu Beginn leer, bis die ersten Einträge kommen; die Startseite und
die Service-Leiste im Kopf zeigen dann einen Leerzustand mit Handlungsaufforderung statt einer
leeren Fläche.

## Darstellung, Wetter, Archiv

**Nur hell.** Es gibt keinen Umschalter und keinen Dunkelmodus — bewusste Entscheidung des
Auftraggebers. Einzige Ausnahme ist die bewusst dunkel gestaltete Blaulicht-Bühne auf der
Startseite, das ist ein Gestaltungselement, keine Umschaltung.

**Kopfbereich.** Suchfeld, Wetter und Datum stehen direkt im Zeitungskopf; eine Service-Leiste
darüber verlinkt Stellenmarkt, Familienanzeigen, Termine und die SC-1919-Merzenich-Seite.

**Wetter.** Die Sidebar-Box und die Kurzanzeige im Kopf holen die Werte für Merzenich live von
Open-Meteo. Kein Schlüssel, keine Cookies, keine Registrierung; nur die Koordinaten der Gemeinde
gehen an den Dienst. Ist er nicht erreichbar, steht ein sachlicher Hinweis in der Box statt einer
Fehlermeldung.

**Archiv.** Eine Seite anlegen und rechts unter Seitenattribute das Template
**Archiv nach Monaten** wählen. Pressemitteilungen der Gemeinde ohne Datum erscheinen dort in
einer eigenen, nachgestellten Gruppe statt zwischen den datierten Meldungen.

**Ortsteilseiten.** Jede Ortsteilseite zeigt zusätzlich eine „Kurz gemeldet aus …"-Liste der
letzten Meldungen aus diesem Ortsteil.

**Ressortblöcke.** Die Startseite zeigt Rathaus & Politik, Wirtschaft und Leben als eigene Blöcke
mit Aufmacher und vier weiteren Überschriften, sobald die jeweilige Kategorie existiert und Inhalte
hat.

**Bildserien.** Hängen an einem Beitrag mehrere Bilder in der Mediathek, erscheinen sie unter dem
Text als Serie. Ein Klick öffnet sie groß, Pfeiltasten blättern, Escape schließt. Bildtyp und
Credit stehen in der Großansicht mit dabei.

**Lesezeit.** Wird aus der Textlänge berechnet, 200 Wörter je Minute, und steht in der Autorenzeile.
Ein goldener Balken am oberen Rand zeigt den Lesefortschritt.

## Werbung

**Design → Merzenich Aktuell.** Zwei unabhängige Werbekunden, jeder mit eigenem Namen, eigener
Ziel-URL und eigenem Bildmotiv (Default-Namen: KBS Management GmbH und AJ Sports Entertainment —
bitte gegen die tatsächlichen Firmennamen prüfen). Jede Anzeige ist eigenständig mit „Anzeige"
gekennzeichnet, es gibt keinen gemeinsamen Balken mit beiden Namen. Breite Plätze zeigen beide
Anzeigen nebeneinander (728 × 90 je Anzeige), die Seitenspalte zeigt eine Anzeige (300 × 104). Die
native Anzeige im Nachrichtenstrom zeigt eine Anzeige im Look einer Meldungskarte und wechselt
zwischen den beiden Kunden. Ohne Bildmotiv steht eine gesetzte Fläche mit Firmennamen und
„Merzenich · Kreis Düren". Auf derselben Seite steht auch der WhatsApp-Kanal-Link.

## Formulare

Sieben Formulare sind ohne Plugin eingebaut: Meldung senden, Termin melden, Verein eintragen,
Betrieb eintragen, Korrektur melden, Werbeanfrage und Kontakt. Sie stehen als Shortcode im Text
der jeweiligen Seite:

```
[ma_formular typ="meldung"]
```

Erlaubte Werte für `typ`: `meldung`, `termin`, `verein`, `betrieb`, `korrektur`, `werbung`,
`kontakt`. Der Starter-Content setzt sie bereits auf den passenden Seiten ein.

**Vor dem Livegang eintragen:** unter **Design → Merzenich Aktuell → Formulare** die
Redaktionsadresse, an die Einsendungen gehen sollen. Ohne Eintrag geht die Post an die
Administrator-Adresse der Installation.

Die Einsendung läuft über `admin-post.php` und `wp_mail()`, mit Nonce, Honigtopf-Feld und
Zeitfalle gegen automatische Einsendungen. Die Antwortadresse des Absenders steht als `Reply-To`
in der Mail, damit sich die Redaktion direkt zurückmelden kann. Nach erfolgreichem Versand
leitet das Formular auf die passende Danke-Seite; bei Fehlern kommt es mit einer Liste der
fehlenden Angaben zurück. Ein optionales Bild landet in der Mediathek, seine Adresse in der Mail.

## Sichtbarkeit bei Google

Das Theme liefert ohne SEO-Plugin:

- NewsArticle- bzw. Article-Schema (undatierte Meldungen ohne `datePublished`) mit Autor, Bild, Ressort, Ortsangabe und Quellenzitat
- JobPosting-Schema für Stellenanzeigen, LocalBusiness-Schema für Betriebe
- Event-Schema für Termine, Organization- und WebSite-Schema, Breadcrumbs
- OpenGraph und Twitter Cards mit großem Vorschaubild
- `max-image-preview:large`, `news_keywords`
- News-Sitemap unter `/news-sitemap.xml`, automatisch in `robots.txt` eingetragen, undatierte Meldungen ausgeschlossen
- RSS über die WordPress-Bordmittel

Ein zusätzliches SEO-Plugin ist nicht nötig. Wird trotzdem eines eingesetzt, dessen Schema-Ausgabe abschalten,
damit keine doppelten Angaben entstehen.

## Reichweite

`ma8_popular_ids()` zählt Aufrufe ohne Cookies und ohne personenbezogene Daten, nur als Zahl je Beitrag und Tag.
Eingeloggte Nutzer werden nicht gezählt. Solange keine Daten vorliegen, zeigt „Meistgelesen" die Top-Themen der Redaktion.

## Bilder

Eigene Bilder gehören in die Mediathek und als Beitragsbild an den Artikel. Das Feld für externe Bild-URLs ist
für Fälle gedacht, in denen nur die Quelle ein Bild hat. Externe Links können ablaufen; das Theme zeigt dann
einen gestalteten Platzhalter statt eines kaputten Bildes.

Für reale Ereignisse werden keine generierten Ersatzbilder als Ereignisfoto verwendet.

Die acht Startbilder liegen im Theme unter `assets/uploads/`. Der Startinhalt verweist mit
relativen Pfaden darauf; `ma8_img_url()` löst sie auf die Theme-Adresse auf. Eigene Bilder gehören
trotzdem in die Mediathek.

## Starter-Content neu erzeugen

`starter-content-v17.xml` wird nicht von Hand gepflegt, sondern aus dem aktuellen Datenbestand der
Netlify-Version erzeugt (`ma9/content/`), solange beide Linien parallel existieren. Neu erzeugen:

```bash
node gen-wxr.mjs   # im Projekt-Wurzelverzeichnis, liest ma9/content/ und schreibt
                    # v13/wp/merzenich-aktuell/starter-content-v17.xml neu
```

`inc/fussball-data.php` wird ebenso automatisch aus `ma9/content/daten/fussball.json` erzeugt
(`node gen-fussball-php.mjs`) — auch diese Datei nicht von Hand pflegen.
