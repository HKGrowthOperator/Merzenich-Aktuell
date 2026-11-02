# SEO-, GEO- und News-Plan für merzenich-aktuell.de

Stand 02.11.2026 · live Plugin 1.37.1, Theme 21.24.1 · Arbeitsplan für eine eigene Sitzung. Vorher lesen: `docs/GOOGLE-NEWS.md` (Stand 07.10.2026, was schon gebaut ist und was nur der Betreiber kann).

**Ziel:** Bei Suchen rund um Merzenich und seine Ortsteile vorn stehen, in Google News und Discover laufen, und von KI-Suchen (ChatGPT, Perplexity, Google AI Overviews, Copilot) als Quelle für Merzenich genannt werden. Daraus sollen viele wiederkehrende Leserinnen und Leser werden.

**Wer macht was:** Jede Aufgabe trägt eine Markierung.
- **[Code]** setzt die Sitzung im Repository um.
- **[Redaktion]** betrifft die Redaktionsläufe (`docs/redaktion/CLAUDE-LAUF.md`, `SCHREIBREGELN.md`).
- **[Betreiber]** geht nur im Google-, Bing- oder Social-Konto.

---

## 0. Ausgangslage (gemessen am 02.11.2026)

**Technisch ist fast alles da** (Details: `includes/seo.php`, `includes/feeds.php`):
- **Kopf der Seiten:**
  - Titel, Beschreibung und Canonical auf jeder Seite.
  - `robots` mit `max-image-preview:large`; dünne Themen- und Archivseiten auf `noindex`.
  - Open Graph mit 1200-px-Bildern.
  - Geo-Meta (DE-NW, Koordinaten).
- **Strukturdaten:**
  - `NewsMediaOrganization` mit Grundsätzen, Korrekturen, Ethik und Finanzierung.
  - `NewsArticle` mit Person als Autor, drei Bildzuschnitten, `speakable`, Quelle und Ort.
  - `Event`, `SportsClub`, `Place` für die 5 Orte, `BreadcrumbList`, `WebSite` mit Suche.
- **Sitemaps:** `wp-sitemap.xml` mit eigener Bereiche- und Autoren-Sitemap; `news-sitemap.xml` mit den Meldungen der letzten 48 Stunden; beide in `robots.txt`.
- **Feeds:** RSS und Atom, auch je Ressort, mit Bild; WebSub-Ping an `pubsubhubbub.appspot.com` bei jeder Veröffentlichung.
- **IndexNow:** an Bing, Yandex und Seznam bei jeder Veröffentlichung.
- **KI und Autoren:** `/llms.txt`, Autorenseiten `/autor/<name>/`, `/heute/`.
- **Tempo:** Startseite TTFB etwa 0,9 s, Ressorts und Orte 0,5 bis 0,6 s. **`/termine/` kalt 3,3 s**, warm 1,3 s.

**Der größte Hebel ist nicht die Technik, sondern der Takt:**
- Die letzte redaktionelle Meldung auf der Seite ist vom **28.10.2026**.
- Seitdem kamen nur Termine, Dossiers und Firmenbeiträge.
- `news-sitemap.xml` ist deshalb **leer**. Für Google News wirkt die Seite eingeschlafen, genau wie in `GOOGLE-NEWS.md` vom 07.10. schon einmal beschrieben.
- Ursache: Die Läufe verlangen je Meldung mindestens 800 Wörter, drei Quellen und ein Prüfprotokoll und haben keine Mengenvorgabe. Gibt es kein Thema, das das trägt, erscheint nichts.

**Sichtbarkeit:** In der Websuche dieser Sitzung (nicht Google) findet `site:merzenich-aktuell.de` keine Seite. Generische Suchen nach „Merzenich“ zeigen dort vor allem den Hirnforscher Michael Merzenich. Den echten Stand bei Google zeigt nur die Search Console (Schritt 1.1).

---

## 1. Sofort (Woche 1)

### 1.1 Messen, bevor geändert wird [Code, Betreiber]
- **Zugang prüfen:** `NODE_USE_ENV_PROXY=1 node deploy/google-seo.mjs zugang`.
  - Fehlt `GOOGLE_SERVICE_ACCOUNT_JSON` in der Umgebung, die Einrichtung aus `GOOGLE-NEWS.md` („Zugriff per API“) beim Betreiber anstoßen. Den Schlüssel nie in den Chat und nie ins Repository.
- **Mit Zugang:**
  - `sitemaps`: Ist `news-sitemap.xml` eingereicht und gelesen?
  - `leistung --tage=28`: Klicks, Impressionen und Suchanfragen, getrennt nach Websuche, News und Discover.
  - `pruefen` für Startseite, `/blaulicht/`, `/ort/golzheim/` und die neueste Meldung.
  - `tempo` für mobile und Desktop-Werte.
- **Ohne Zugang:** Der Betreiber schaut in der Search Console unter Indexierung → Seiten, Sitemaps und Leistung → Suchtyp „Nachrichten“ nach und schickt Zahlen oder Screenshots.
- **Ergebnis:** eine Tabelle „Stand vorher“ in `docs/SEO-STAND.md`. Daran wird jeder spätere Schritt gemessen.

### 1.2 Wieder täglich Nachrichten [Redaktion, Betreiber entscheidet]
Ohne neue Meldungen wirkt nichts anderes in diesem Plan.
- **Mindesttakt:** an jedem Tag mindestens **2 Meldungen**, davon mindestens eine aus Blaulicht, Rathaus oder Termine-Vorschau.
- **Neuer Artikeltyp „Kurzmeldung“, 150 bis 350 Wörter:**
  - Einsatz, Sperrung, Ratsbeschluss, Terminhinweis mit Einordnung, Ergebnis vom Wochenende.
  - Eine offizielle Quelle reicht (Polizei, Feuerwehr, Gemeinde, Verein). Aussagen werden genauso geprüft wie bei Standard-Meldungen, ohne Mindestlänge.
  - Der Standard mit 800+ Wörtern bleibt für Hintergrund, Porträt und Analyse.
- **Umsetzen:**
  - Abschnitt in `SCHREIBREGELN.md` und `CLAUDE-LAUF.md`: Mengenvorgabe „mindestens 1 Meldung je Lauf, wenn eine belegbare Nachricht existiert“.
  - Ein Nullfund ist nur mit Quellenprotokoll erlaubt.
  - `qa/redaktion-lauf.mjs` meldet einen Hinweis, wenn 24 Stunden keine Meldung erschienen ist.
- **Wächter [Code]:** ein GitHub-Workflow, der täglich `/news-sitemap.xml` liest. Hat sie 0 Einträge, öffnet er ein Issue oder schickt eine Mail.
- **Entscheidung Betreiber:** Kurzmeldungen ja oder nein, und welcher Mindesttakt.

### 1.3 Betreiber-Schritte aus GOOGLE-NEWS.md abschließen [Betreiber]
Stand je Punkt abfragen und abhaken:
1. In der Search Console `news-sitemap.xml` einreichen; beide Sitemaps müssen „Erfolgreich“ zeigen.
2. **Google Publisher Center:**
   - Publikation anlegen, beide Logos hochladen.
   - Abschnitte per Feed: `/feed/`, `/blaulicht/feed/`, `/rathaus/feed/`, `/sport/feed/`, `/leben/feed/`.
   - Das bringt den Knopf „Folgen“ in Google News.
3. Bing Webmaster Tools: Sitemaps einreichen, IndexNow-Meldungen prüfen. ChatGPT-Suche und Copilot nutzen den Bing-Index (siehe 3.1).
4. Die alte Vorschauadresse `merzenichaktuell.hk-growthoperator.de` auf 301 umstellen oder den DNS-Eintrag löschen.
5. Indexierung beantragen: Startseite, `/heute/`, `/blaulicht/`, `/termine/`, `/nachrichten/`, 5 Ortsseiten, danach die neuesten Meldungen.

### 1.4 Kleine technische Lücken [Code, Plugin-Patch]
- **`/termine/` beschleunigen:** kalt 3,3 s. Mit `zeitmessung.log` lokal die Ursache finden und Abfragen per Transient zwischenspeichern. Ziel: TTFB unter 1 s.
- **Aktualisieren statt neu anlegen:** Bei Updates wird `dateModified` sichtbar („Aktualisiert 14:20“), und WebSub und IndexNow pingen erneut. `ma_websub_merken` reagiert schon auf `post_updated`; prüfen, ob IndexNow das auch tut.
- **News-Sitemap um das Hauptbild ergänzen** (`image:image`). Google wertet es für Nachrichten aus. `ma_seo_news_sitemap_xml()` in `includes/seo.php:202`.
- **`llms.txt`** (`ma_seo_llms_txt()`):
  - `/heute/`, `/ki-redaktion/`, `/termine/kalender.ics` und die 10 neuesten Meldungen mit Datum ergänzen.
  - Zusätzlich `/llms-full.txt` mit den Faktenseiten aus 3.2.
- **robots.txt:** KI-Crawler ausdrücklich erlauben, als Signal, dass Zitieren gewollt ist. Gemeint sind `GPTBot`, `OAI-SearchBot`, `ChatGPT-User`, `PerplexityBot`, `ClaudeBot`, `Google-Extended`, `Bingbot` und `Applebot-Extended`. Heute ist nichts gesperrt, es fehlt nur die ausdrückliche Zeile.
- **Twitter-Karte:** `twitter:site` erst setzen, wenn es ein Konto gibt.
- **Tests:** jede Änderung in `qa/wordpress/seo-test.php` und `feeds-test.php`; dazu Kette, `qa/pruefung.mjs` und `qa/routen.mjs`.

---

## 2. Schlüsselwörter und Seiten, die sie tragen (Woche 1 bis 4)

**Wichtig:**
- Ich habe hier **keine Suchvolumen-Daten**. Die Liste ordnet nach Suchabsicht und lokaler Plausibilität.
- Nach 4 Wochen mit echten Daten prüfen: Search Console „Suchanfragen“, Google Trends (Region NRW) und Google Ads Keyword-Planer.
- Dann jede Gruppe nach Impressionen sortieren und schwache streichen.

**Grundregel für Titel:**
- Ortsname und das konkrete Ding stehen vorn; „Merzenich“ oder der Ortsteil steht im Titel und im ersten Satz.
- Bei Verwechslungsgefahr kommt „Kreis Düren“ dazu, wegen Michael Merzenich und der Bäckerei Merzenich in Köln.

| Gruppe | Typische Suchen (Beispiele) | Zielseite | Was fehlt |
|---|---|---|---|
| Marke / Navigation | merzenich aktuell, nachrichten merzenich, merzenich news heute | Startseite, `/nachrichten/` | Takt (1.2) |
| Heute / Wochenende | was ist heute in merzenich los, merzenich wochenende, veranstaltungen merzenich | `/heute/`, `/termine/` | Wochenend-Vorschau jeden Donnerstag als Meldung |
| Blaulicht | unfall merzenich, feuerwehr merzenich einsatz, polizei düren merzenich, l264 unfall, a4 merzenich sperrung, brand golzheim | `/blaulicht/`, Einzelmeldungen | schnelle Kurzmeldungen, Straßennamen im Titel |
| Saison und Feste | martinszug merzenich 2026, karneval golzheim, sessionseröffnung 11.11. merzenich, adventsmarkt heimatmuseum merzenich, weihnachtsmarkt merzenich, schützenfest golzheim, kirmes girbelsrath | je eine **Sammelseite pro Anlass und Jahr** | Sammelseiten (2.1) |
| Rathaus und Alltag | gemeinderat merzenich, bürgermeister merzenich, amtsblatt merzenich, abfallkalender merzenich, grundsteuer merzenich, baugebiet merzenich, kita merzenich | `/rathaus/`, Faktenseiten (3.2) | Service-Seiten |
| Strukturwandel | morschenich ort der zukunft, bürgewald, tagebau hambach merzenich, tagebausee, amprion stromtrasse merzenich, rheinisches revier leader | Themenseiten `/thema/...` | Dossier-Seiten mit Zeitachse |
| Sport | sc merzenich, ttc merzenich, kreisliga a düren tabelle, fußball golzheim | `/sport/`, Vereinsseiten | Spieltag-Kurzmeldung montags |
| Ortsteile | golzheim, girbelsrath, morschenich, bürgewald + nachrichten / termine | `/ort/<ort>/` | Einleitungstext je Ort (100 bis 200 Wörter, Fakten, Links) |
| Wirtschaft / Markt | jobs merzenich, wohnung merzenich mieten, haus kaufen merzenich, firmen merzenich | `/jobs/`, `/immobilien/`, `/unternehmen/` | eigene Texte; Kopien bleiben `noindex` |
| Umkreis | nachrichten düren, niederzier, kerpen buir, elsdorf | Umkreis-Block | nur verlinken, kein eigener Schwerpunkt |

### 2.1 Sammelseiten für Anlässe [Redaktion, Code]
- **Was:** Je wiederkehrendem Anlass eine Seite mit Jahr, die jedes Jahr aktualisiert wird (gleiche URL plus Jahr), zum Beispiel `/thema/martinszuege-merzenich-2026/`.
- **Inhalt:**
  - alle Termine aller Ortsteile als Tabelle (Datum, Uhrzeit, Treffpunkt, Weg);
  - Stand-Datum und Quelle;
  - Links auf die Einzelmeldungen.
- **Strukturdaten:** `ItemList` mit `Event`.
- **Zeitplan:** jeweils 3 bis 4 Wochen vor dem Anlass veröffentlichen, damit Google die Seite rechtzeitig kennt.
- **Kandidaten bis Jahresende:**
  - Martinszüge (läuft jetzt);
  - Sessionseröffnung 11.11.;
  - Adventsmärkte und Weihnachtsmärkte;
  - Gottesdienste an Weihnachten;
  - Silvester und Neujahrsempfang;
  - Karneval 2027 (Sitzungen, Züge).

---

## 3. GEO: von KI-Suchen zitiert werden (Woche 2 bis 6)

### 3.1 Grundlagen [Code, Betreiber]
- **Bing:** ChatGPT-Suche und Copilot greifen auf Bing zu. Bing Webmaster Tools und IndexNow sind dafür der direkte Weg (1.3).
- **Entitäten verknüpfen [Code]:**
  - In `ma_seo_orte()` (`Place` je Ort) `sameAs` auf den Wikidata- und Wikipedia-Eintrag des Orts setzen. Die Kennungen holt die Sitzung selbst von wikidata.org.
  - `NewsMediaOrganization.sameAs` mit den Social-Profilen ergänzen (Option `ma_seo_sameas`, Betreiber liefert die Adressen).
- **Antwort zuerst:** Jede Meldung beginnt mit einem Satz, der Wer, Was, Wann und Wo beantwortet. KI-Systeme zitieren gern den ersten Satz.

### 3.2 Faktenseiten, die KI und Google gern zitieren [Redaktion, Code]
- **Seiten:** Evergreen-Seiten mit klaren Fakten, Stand-Datum und Quellen:
  - „Gemeinde Merzenich in Zahlen“: Einwohner je Ortsteil, Fläche, Bürgermeister, Rat, Partnerstädte.
  - „Wichtige Nummern und Notdienste in Merzenich“.
  - „Abfuhr und Wertstoffhof Merzenich“, mit Link zum amtlichen Kalender.
  - „Ortsteile von Merzenich“, kurz mit Geschichte und Kirchen.
  - „Strukturwandel: Morschenich, Bürgewald und Tagebau Hambach“ als Zeitachse.
- **Form:**
  - Fragen als Zwischenüberschriften („Wie viele Einwohner hat Merzenich?“) mit kurzer Antwort darunter.
  - Dazu `FAQPage`-Strukturdaten. Google zeigt dafür keine FAQ-Ergebnisse mehr an, KI-Systeme lesen das Format aber gut.
- **Pflege:** Stand-Datum sichtbar, Quellen verlinkt, Prüfung zweimal im Jahr. Aufnahme in `llms.txt` und `llms-full.txt`.

### 3.3 Prüfen, ob es wirkt
Monatlich in ChatGPT (mit Suche), Perplexity und Google (AI Overview) dieselben 10 Fragen stellen und notieren, ob Merzenich Aktuell als Quelle erscheint:
- Was ist am Wochenende in Merzenich los?
- Wann ist der Martinszug in Golzheim?
- Was ist in Morschenich geplant?
- Wer ist Bürgermeister von Merzenich?
- Gab es heute einen Unfall in Merzenich?
- und fünf weitere aus der Schlüsselwort-Tabelle.

Ergebnis in `docs/SEO-STAND.md`.

---

## 4. Google News und Discover gezielt (ab Woche 2)

- **Takt** (1.2) ist die Grundlage. Google braucht 2 bis 6 Wochen regelmäßiger Meldungen, bis die Seite als Nachrichtenquelle läuft.
- **Eilmeldungen:** Blaulicht und Sperrungen innerhalb von 60 Minuten nach der Quelle. Kurz und sachlich, später ergänzen und „Aktualisiert“ setzen.
- **Discover:**
  - großes Hauptbild (1200 px, kein Logo), keine Klickköder-Titel;
  - Ort und Nutzen im Titel („Golzheim: Kirmesstraße ab Freitag gesperrt“).
- **Byline:**
  - Jede Meldung trägt eine Person mit Autorenseite (`GOOGLE-NEWS.md`, Abschnitt „Namen in der Byline“).
  - Meldungen der KI-Redaktion verweisen sichtbar auf `/ki-redaktion/` und die Grundsätze. Diese Offenheit stützt Vertrauen und E-E-A-T.
- **„Folgen“ in Google News** über das Publisher Center (1.3). Im Fuß der Seite einen Link „Merzenich Aktuell in Google News folgen“ setzen, sobald die Publikation live ist.

---

## 5. Links und Reichweite aus dem Ort (laufend) [Betreiber, Redaktion]
- **Gemeinde:** Link von gemeinde-merzenich.de und Aufnahme in den Presseverteiler (`docs/vorlagen/verlinkung-gemeinde.md`).
- **Vereine:** Nach jedem Bericht den Link schicken (`vorlagen/verlinkung-verein.md`). Ziel: 10 Vereinsseiten mit Link in 8 Wochen.
- **Firmen:**
  - Die sechs Firmenkunden um einen Link „Bekannt aus Merzenich Aktuell“ bitten.
  - Kein Linkkauf, kein Linktausch (siehe `GOOGLE-NEWS.md`, „Nicht tun“).
- **Eigene Kanäle:**
  - WhatsApp-Kanal, Facebook-Seite und Instagram; täglich die wichtigste Meldung mit Link.
  - Die Adressen kommen in `ma_seo_sameas`.
  - Web-Push gibt es schon (`includes/push.php`); auf Startseite und unter Meldungen sichtbar anbieten.
- **Lokale Gruppen:** einmal vorstellen, dann nur wichtige Einzelmeldungen teilen (`vorlagen/teilen-lokale-gruppen.md`).

---

## 6. Messen und Ziele

**Jede Woche:**
- Mit `node deploy/google-seo.mjs leistung --tage=7` Tabelle in `docs/SEO-STAND.md`: Klicks, Impressionen, Top-10-Suchanfragen, News-Impressionen, Discover.
- Indexierte Seiten.
- Anzahl Meldungen je Tag.

**Ziele (Richtwerte, nach der ersten Messung anpassen):**

| Nach | Ziel |
|---|---|
| 2 Wochen | jeden Tag ≥ 2 Meldungen; `news-sitemap.xml` nie leer; Publisher Center angelegt |
| 6 Wochen | erste Impressionen im Suchtyp „Nachrichten“; „merzenich aktuell“ auf Platz 1; ≥ 3 Links aus dem Ort |
| 3 Monate | Top 3 für „nachrichten merzenich“, „blaulicht merzenich“, „was ist heute in merzenich los“; Sammelseiten auf Seite 1 für ihren Anlass; in 3 von 10 KI-Testfragen als Quelle genannt |

---

## 7. Reihenfolge für die Umsetzungs-Sitzung

1. **Messen** (1.1) und `docs/SEO-STAND.md` anlegen.
2. **Redaktionstakt:** Kurzmeldung und Mindesttakt (1.2) als Vorschlag in `SCHREIBREGELN.md` und `CLAUDE-LAUF.md`. Erst nach Freigabe des Betreibers übernehmen. Dazu den Sitemap-Wächter bauen.
3. **Plugin-Patch** mit 1.4 und 3.1 (Entitäten, `llms.txt`, robots, News-Sitemap mit Bild, `/termine/` schneller), mit Tests. Danach Version erhöhen, Pakete bauen und mit Freigabe live einspielen wie in `docs/WORDPRESS-LIEFERUNG.md` beschrieben.
4. **Erste Sammelseite** (Martinszüge 2026) und die **Faktenseite** „Gemeinde Merzenich in Zahlen“.
5. **Betreiber-Liste** (1.3, 5) als kurze Checkliste an den Betreiber schicken. Zugänge und Schlüssel nie im Chat.
6. Nach 4 Wochen: Schlüsselwörter mit echten Daten neu ordnen (Abschnitt 2), Ziele prüfen.

**Regeln:**
- Nichts erfinden: keine Zahlen ohne Quelle, keine Volumen ohne Messung.
- Die Schreibregeln der Redaktion gelten.
- Keine Gedankenstriche in Texten für die Seite.
- Jede Änderung mit Test.
- Live nur mit Freigabe des Betreibers.
