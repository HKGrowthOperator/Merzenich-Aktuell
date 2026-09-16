# Merzenich Aktuell · v9

Lokalzeitung online für die Gemeinde Merzenich (Kreis Düren). Statische Seite, gebaut mit einem eigenen Generator ohne npm-Abhängigkeiten, Redaktionsoberfläche über Decap CMS, Hosting und Formulare über Netlify.

## In drei Schritten live

1. **Repository anlegen.** Diesen Ordner in ein Git-Repository (GitHub, GitLab) pushen, Branch `main`.
2. **Netlify verbinden.** „Add new site → Import from Git". Build-Befehl `node build.mjs`, Publish-Verzeichnis `dist` (steht in `netlify.toml`). Domain `merzenich-aktuell.de` unter Domain management verbinden.
3. **Redaktion freischalten.** Netlify → Site configuration → Identity → *Enable Identity*; unter Registration *Invite only*; unter Services → Git Gateway *Enable*. Dann Identity → *Invite users* mit den E-Mail-Adressen der Redaktion. Ab jetzt ist das Redaktionssystem unter `https://merzenich-aktuell.de/admin/` erreichbar.

Formulare (Meldung senden, Termin melden, Verein, Betrieb, Korrektur, Werbung, Kontakt) sind Netlify Forms. Nach dem ersten Deploy unter Forms → Notifications eine E-Mail-Benachrichtigung an die Redaktionsadresse einrichten. Bild-Uploads in Formularen sind bis 8 MB möglich.

## Wie eine Meldung online geht

`/admin/` → Artikel → Neu → Felder ausfüllen → Speichern (Entwurf) → Status „Bereit" → Veröffentlichen. Jede Veröffentlichung ist ein Git-Commit, Netlify baut die Seite in etwa einer Minute neu. Startseite, Ressort, Ortsteil, Themen, Autorenseite, RSS/Atom/JSON-Feed, Sitemaps, News-Sitemap, Suchindex und die JSON-Schnittstelle unter `/api/latest.json` aktualisieren sich automatisch. Das ausführliche Redaktionshandbuch liegt unter `/redaktionshandbuch/` (nicht indexiert).

## Struktur

```
content/
  artikel/     Meldungen (Markdown + Frontmatter), URL: /<ressort>/<slug>/
  termine/     Termine, URL: /termine/<slug>/, je Termin eine .ics-Datei
  vereine/     Vereinsverzeichnis, URL: /vereine/<slug>/
  betriebe/    Lokale Betriebe (Branchenbuch), URL: /betriebe/<slug>/
  stellen/     Stellenmarkt, URL: /jobs/<slug>/ (JobPosting-Markup)
  daten/fussball.json  Tabelle und Spielplan SC 1919 Merzenich → /sc-1919-merzenich/
  orte/        Die fünf Ortsteile mit Porträt und Steckbrief, URL: /<ortsteil>/
  autoren/     Redaktion (E-E-A-T: Autorenseiten mit Profil)
  seiten/      Statische Seiten inkl. Formularen und Danke-Seiten
  daten/       site.json (Einstellungen, Navigation, Notdienste), umfrage.json
src/
  templates/   HTML-Templates (layout, components, pages, media)
  lib/         Frontmatter-Parser, Utilities, Feeds/Sitemaps, marked (gebündelt)
  assets/      style.css, app.js, Fonts, Bilder, uploads/ (CMS-Uploads)
  admin/       Decap CMS (index.html, config.yml)
build.mjs      Generator: content/ + src/ → dist/
netlify.toml   Build, Node-Version, Image-CDN-Allowlist
```

## Lokal bauen

```
node build.mjs                 # → dist/ (mit Netlify Image CDN-Pfaden)
IMAGE_CDN=off node build.mjs   # → dist/ mit direkten Bildpfaden für die lokale Vorschau
python3 -m http.server 8080 --directory dist
node scripts/check.mjs         # Links, JSON-LD, Descriptions prüfen
```

Es gibt keine npm-Installation. Node 20 oder neuer genügt.

## Was die Seite technisch mitbringt

- **Saubere URLs** nach dem Muster Ressort/Slug, Ortsteilseiten als zweite Achse, Themenseiten, Autorenseiten, Archiv, Suche.
- **SEO für News:** NewsArticle/OpinionNewsArticle/AdvertiserContentArticle-Markup mit Autor, Publisher, Ort, Quellen (`citation`), Korrekturen; NewsMediaOrganization mit `publishingPrinciples`, `correctionsPolicy`, `ethicsPolicy`, `masthead`, `ownershipFundingInfo`; Event-Markup je Termin; Organization/LocalBusiness je Verein und Betrieb; Place je Ortsteil; BreadcrumbList überall; `max-image-preview:large`; News-Sitemap (nur 48 Stunden), Sitemap-Index mit Bild-Sitemap, RSS mit Media-RSS, Atom, JSON Feed, WebSub-Hub-Link.
- **Performance:** selbst gehostete variable Fonts mit Preload, `fetchpriority="high"` auf dem Aufmacherbild, feste Bildverhältnisse (kein Layout-Sprung), Netlify Image CDN mit `srcset` (AVIF/WebP automatisch), Service Worker mit Offline-Seite, keine Cookies, kein Consent-Banner nötig.
- **Redaktioneller Standard:** Bildtyp (Originalbild, Veranstaltungsbild, Quellenmotiv, Archivbild, Symbolbild, Logo, Leserfoto) und Credit an jedem Bild, Quellenkasten mit Datenstand, Korrekturhinweise im Artikel, Formatkennzeichnung (Eilmeldung, Interview, Kommentar, Kolumne, Anzeige …).
- **Community:** Termin melden, Meldung senden, Verein/Betrieb eintragen, Korrektur melden, Umfrage, WhatsApp-Kanal-Einbindung (Link in Einstellungen eintragen).
- **Legacy-Weiterleitungen** von allen v8-Dateinamen (`artikel-*.html`, `*.html`) auf die neuen URLs.
- **Werbung:** fünf feste Plätze mit eigener Kennung „Anzeige": Billboard 728×90 unter dem Kopf, native Anzeige nach der vierten Meldung im Nachrichtenstrom, breite Reihe nach dem Kalender (home_mid), im Artikel nach dem zweiten Absatz, Seitenspalte 300×104. Motive, Links und Text in `content/daten/site.json` → `ads.banners` (im CMS: Einstellungen → Website → Werbung); `ads.enabled` blendet alle Banner aus.
- **Anzeigenmarkt** auf der Startseite: Stellen aus `content/stellen/` und Familienanzeigen/Nachrufe (Format `familienanzeige`/`nachruf`) aus dem Ressort Menschen.
- **Monatskalender** in der Terminbox (Tage mit Termin sind verlinkt).
- **Meldungen ohne Datum:** Frontmatter-Felder `undated: true` und `retrieved: <Datum>` (nur in der Datei, kein CMS-Feld); sie erscheinen als „Ohne Datum · abgerufen …", werden hinter datierte Meldungen sortiert und als `Article` statt `NewsArticle` ausgezeichnet.

## Vor dem Livegang

- `content/seiten/impressum.md` und `datenschutz.md` mit den echten Betreiberangaben füllen.
- WhatsApp-Kanal-Link, Facebook/Instagram in Einstellungen → Website eintragen.
- Bilder, die von Facebook verlinkt sind, laufen nach wenigen Tagen ab. Beim ersten Öffnen im CMS herunterladen, unter `src/assets/uploads/` hochladen und Credit setzen. Der Build listet betroffene Meldungen in `dist/build-report.json`.
- Google Search Console und Bing Webmaster Tools anlegen, `sitemap.xml` einreichen. Ein manueller Antrag bei Google News ist nicht mehr nötig; ein Publisher-Center-Profil (Logo, Standort) lohnt trotzdem.
- Statistik: `analytics.provider` in `site.json` auf `plausible` oder `umami` setzen und das Script in `src/templates/layout.mjs` ergänzen. Beide Tools sind cookielos; ein Banner ist dann nicht nötig.
- Werbemotive der beiden Banner-Einträge in `site.json` → `ads.banners` (`kbs`: KBS Management GmbH, `aj`: AJ Sports Entertainment) mit `image`, `alt` und `url` eintragen; bis dahin steht der Firmenname im Rahmen.

## Lizenzen

Schriften Newsreader und Inter unter SIL Open Font License (siehe `src/assets/fonts/`). Markdown-Parser `marked` (MIT) liegt gebündelt in `src/lib/`. Wetterdaten von Open-Meteo (CC BY 4.0).
