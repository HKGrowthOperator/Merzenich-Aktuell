# SEO-, GEO- und News-Bericht für merzenich-aktuell.de

Stand 03.11.2026, gemessen an der Live-Seite zwischen 10:15 und 10:45 Uhr (Abruf mit curl, ohne Cache). Grundlage für die Umsetzung bleibt `docs/SEO-GEO-NEWS-PLAN.md` (02.11.2026); dieser Bericht sagt, was davon steht, was fehlt und wer was tun muss.

**Grenze der Messung:** Ohne Zugang zur Search Console (`GOOGLE_SERVICE_ACCOUNT_JSON` ist in dieser Umgebung nicht gesetzt) gibt es keine Zahlen zu Klicks, Impressionen, Platzierungen oder indexierten Seiten. Alles unten ist technisch an der Seite gemessen. Rankings sind nicht gemessen, sondern begründet eingeschätzt.

Kennzeichnung wie im Plan: **[Code]** setzt die Redaktionssitzung im Repository um, **[Redaktion]** betrifft die Redaktionsläufe, **[Betreiber]** geht nur in Google-, Bing-, IONOS- oder Social-Konten.

---

## 1. Auf einen Blick

| Bereich | Stand | Bewertung |
|---|---|---|
| Technik für Google News | Nachrichten-Sitemap mit Bild, `NewsArticle`, `NewsMediaOrganization` mit allen Richtlinien-Seiten, große Bildvorschau erlaubt, Feed mit WebSub | gut |
| Datum der Meldungen | Feuerwehr 140/26, 141/26 und 144/26 standen mit Einsatztag und erfundener 12 Uhr online, damit nie in der Nachrichten-Sitemap | behoben (Plugin 1.43.0 live seit 10:36 Uhr); 144/26 wartet in den Freigaben |
| Freigegebene Entwürfe | behielten das Paketdatum, kamen also oft zu alt online | heute behoben: Freigabezeit gilt als Veröffentlichung |
| Takt | 4 Meldungen in den letzten 48 Stunden; Ziel laut Plan: jeden Tag mindestens 2 | ausbaufähig |
| Auffindbarkeit | Bei Google nicht gemessen. In einer anderen Websuche (nicht Google) erscheint merzenich-aktuell.de für „Merzenich Nachrichten aktuell“ gar nicht; dort führen news.de („Polizei-News Merzenich, TT.MM.JJ: …“), Presseportal und die Gemeinde | Lücke, messen |
| Tempo | Antwortzeit 0,5 bis 0,8 s, `/termine/` 1,4 s; jede Seite wird ohne Seiten-Cache neu gerechnet (`Cache-Control: no-cache`) | mittel |
| KI-Suchen (GEO) | `robots.txt` erlaubt GPTBot, ClaudeBot, PerplexityBot, Google-Extended und andere ausdrücklich; `llms.txt` da; `llms-full.txt` fehlt (404); Organisation ohne `sameAs` | mittel |
| Ortsseiten | Titel und `Place`-Daten mit Wikidata gut; Einleitung nur etwa 25 Wörter | dünn |
| Überschriften | H1 „Alle Meldungen“, „Heute & die nächsten Tage“, „Feuerwehr, Polizei und Rettungsdienst“ ohne „Merzenich“ | kleine Lücke |
| `/umgebung/` | HTTP 404, weil Plugin 1.42.0 nie gebaut wurde; 1.43.0 ist live, die Seite entsteht beim nächsten stündlichen Lauf oder beim nächsten Besuch im Backend | wird von selbst behoben |
| Alte Vorschauadresse | `merzenichaktuell.hk-growthoperator.de` antwortet mit 503 statt Weiterleitung | Betreiber |

---

## 2. Wie eine Meldung zu Google News kommt

Google hat **keinen Knopf, mit dem man eine Nachricht hineinschiebt**. IndexNow nimmt Google nicht an, die Indexing-API gilt nur für Stellenanzeigen und Livestreams. Google holt sich Nachrichten selbst. Deshalb kommt es darauf an, dass Google oft vorbeischaut und jedes Mal etwas Frisches findet.

Was bei jeder Veröffentlichung schon automatisch passiert:

1. **Nachrichten-Sitemap** `/news-sitemap.xml`: alle Meldungen der letzten 48 Stunden mit Titel, Zeitpunkt und Bild. Das ist der wichtigste Weg zu Google News, sobald die Sitemap in der Search Console eingereicht ist.
2. **Feed** `/feed/` (auch je Ressort) mit WebSub-Meldung an `pubsubhubbub.appspot.com`: Dienste, die den Feed abonniert haben, bekommen die Meldung sofort.
3. **IndexNow** an Bing, DuckDuckGo, Ecosia, Yandex und Seznam. Bing ist die Quelle für ChatGPT-Suche und Copilot.
4. **Web-Push** an Leser, die Benachrichtigungen erlaubt haben (gebündelt alle 10 Minuten).
5. **News-Sitemap-Wächter** (GitHub, 11:30 und 17:30 Uhr): meldet sich, wenn die Nachrichten-Sitemap leer ist.

Heute dazugekommen (Commits `e2da1cf`, `515af69`):

- **Ehrliches Datum.** Die Feuerwehr-Automatik setzt als Datum den Zeitpunkt, an dem sie die Meldung anlegt, nicht mehr den Einsatztag mit 12 Uhr. 140/26 und 141/26 sind seit 10:40 Uhr korrigiert. **144/26 ist seit der Korrektur nicht öffentlich:** Er war ohne Prüfhäkchen online, beim Speichern hat die Veröffentlichungssperre ihn auf Entwurf gesetzt. Er wartet in den Freigaben; nach der Freigabe trägt er die Freigabezeit und steht 48 Stunden in der Nachrichten-Sitemap. Plugin 1.43.1 korrigiert ein Datum künftig, ohne den Status anzufassen.
- **Freigabe = Veröffentlichung.** Gibt jemand einen Entwurf aus dem Abgleich frei, gilt die Freigabezeit als Datum. Vorher konnte eine Meldung, die einen Tag in den Freigaben lag, schon bei Veröffentlichung zu alt für Google News sein.
- **Folgemeldung statt nur Update** bei neuer Entwicklung (SEO-KEYWORDS Regel 5 jetzt gleich mit SCHREIBREGELN 0a). Nur eine neue Adresse mit neuem Datum kommt in die Nachrichten-Sitemap; ein Nachtrag an einer alten Adresse nicht.

Was Google für die Reihenfolge in News und Top Stories wertet: Aktualität, regelmäßiger Takt über Wochen, eigene Leistung statt Kopie, klarer Ortsbezug in Titel und erstem Satz, großes Bild (mindestens 1200 Pixel breit), Transparenz (Impressum, Redaktion, Korrekturen) und wie oft Leser draufklicken.

---

## 3. Befunde aus dem Live-Abruf

### 3.1 Was schon gut ist

- **Kopf jeder Seite:** eigener Titel und eigene Beschreibung, `canonical`, `robots` mit `max-image-preview:large`, Open Graph mit 1200-px-Bild, `summary_large_image`.
- **Meldungen** (Beispiel Folgemeldung L 264): `NewsArticle` mit Überschrift, Datum, Bild 1200 × 675, Ort (`contentLocation`), Quelle (`isBasedOn`, `citation`), `speakable`, Wortzahl; dazu `BreadcrumbList`.
- **Herausgeber:** `NewsMediaOrganization` mit Impressum, Korrektur-, Ethik-, Vielfalts- und Finanzierungsangaben. Das ist mehr als die meisten Lokalportale haben.
- **Sitemaps:** `wp-sitemap.xml` und `news-sitemap.xml` beide in `robots.txt`; die Nachrichten-Sitemap hat zu jeder Meldung ein Bild.
- **Bilder:** 182 von 187 Bildern der Startseite als WebP, 183 mit `loading="lazy"`, alle mit Alt-Text.
- **Adressen:** `http://` und `www.` leiten mit 301 auf `https://merzenich-aktuell.de/`; HSTS gesetzt; Brotli-Kompression.
- **Ortsseiten** `/ort/<ort>/` mit eigenem Titel („Golzheim: Nachrichten aus dem Ortsteil der Gemeinde Merzenich“) und `Place` mit Wikidata-Verweis.

### 3.2 Lücken, nach Wirkung geordnet

| # | Befund | Warum es zählt | Wer | Aufwand |
|---|---|---|---|---|
| 1 | Search Console: Stand unbekannt, kein API-Zugang für Messungen | Ohne eingereichte Nachrichten-Sitemap liest Google sie nur zufällig; ohne Zahlen weiß niemand, was wirkt | Betreiber | 20 Min. |
| 2 | Publisher Center: Stand unbekannt | Voraussetzung für „Folgen“ in Google News und eigene Rubriken | Betreiber | 30 Min. |
| 3 | Takt: unter 2 Meldungen an manchen Tagen | Google News braucht 2 bis 6 Wochen gleichmäßigen Takt, bevor eine Quelle sicher läuft | Redaktion | laufend |
| 4 | Sammelseiten für Anlässe fehlen (`/thema/martinszuege-merzenich-2026/` 404) | „martinszug merzenich 2026“ wird jetzt gesucht; Martinszüge laufen bis Mitte November | Redaktion, Code | 1 Lauf |
| 5 | Bing Webmaster Tools: Stand unbekannt | ChatGPT-Suche und Copilot zitieren aus Bing | Betreiber | 10 Min. |
| 6 | `NewsMediaOrganization` ohne `sameAs` (keine Social-Profile) | Google und KI verknüpfen die Marke schlechter mit „Merzenich Aktuell“ | Betreiber liefert Adressen, Code trägt ein | 10 Min. |
| 7 | Ortsseiten: etwa 25 Wörter Einleitung | Suchen „golzheim“, „morschenich“ brauchen Text mit Fakten, nicht nur Liste | Redaktion, Code | 1 Lauf je Ort |
| 8 | Kein Seiten-Cache, `/termine/` 1,4 s | Ladezeit fließt in Core Web Vitals und in Discover ein | Code, Betreiber entscheidet | halber Tag |
| 9 | `llms-full.txt` fehlt | KI-Systeme lesen gern eine Volltextfassung der wichtigsten Seiten | Code | 1 Std. |
| 10 | H1 ohne „Merzenich“ auf `/nachrichten/`, `/termine/`, `/blaulicht/` | Kleines Relevanzsignal, kostet nichts | Code (Theme) | 30 Min. |
| 11 | Faktenseiten fehlen („Merzenich in Zahlen“, Notdienste, Abfuhr, Ortsteile, Strukturwandel) | Das sind die Seiten, die KI-Antworten zitieren | Redaktion, Code | je 1 Lauf |
| 12 | Alte Vorschauadresse 503 | Doppelter Inhalt, falls Google sie noch kennt | Betreiber | 10 Min. |

---

## 4. Anleitung für den Betreiber, Schritt für Schritt

Zugangsdaten und Schlüssel gehören nie in den Chat und nie in eine Datei im Repository.

### A. Google Search Console (zuerst, 20 Minuten)

1. search.google.com/search-console öffnen, mit dem Google-Konto anmelden, das auch das Publisher Center bekommt.
2. Prüfen, ob die Property `merzenich-aktuell.de` schon besteht. Wenn nicht: „Property hinzufügen“ → **Domain** → `merzenich-aktuell.de`. Den angezeigten TXT-Eintrag bei IONOS unter Domain → DNS anlegen, dann „Bestätigen“.
3. **Sitemaps** → beide einreichen: `https://merzenich-aktuell.de/wp-sitemap.xml` und `https://merzenich-aktuell.de/news-sitemap.xml`.
4. **URL-Prüfung** → `https://merzenich-aktuell.de/` → „Indexierung beantragen“. Dasselbe für `/nachrichten/`, `/blaulicht/`, `/termine/`.
5. Nach 2 bis 3 Tagen nachsehen: **Indexierung → Seiten** (wie viele indexiert, welche Gründe für „nicht indexiert“) und **Leistung → Suchtyp „Nachrichten“**.
6. **Messzugang für die Redaktionssitzung:** In der Google Cloud ein Dienstkonto anlegen, es in der Search Console als Nutzer mit Leserecht hinzufügen und die JSON-Datei als Umgebungsgeheimnis `GOOGLE_SERVICE_ACCOUNT_JSON` in der Claude-Umgebung hinterlegen (Anleitung: `docs/GOOGLE-NEWS.md`, Abschnitt „Zugriff per API“). Dann misst die Sitzung jede Woche Klicks, Suchanfragen und News-Impressionen und schreibt sie in `docs/SEO-STAND.md`.

### B. Google Publisher Center (30 Minuten)

1. publishercenter.google.com → „Publikation hinzufügen“: Name *Merzenich Aktuell*, Sprache Deutsch, Land Deutschland, Website `https://merzenich-aktuell.de` (wird über die Search Console bestätigt).
2. Logo hochladen (`chatgpt-site/assets/img/logo.png` und `logo-on-light.png`).
3. Rubriken als Feeds anlegen: Nachrichten `/feed/`, Blaulicht `/blaulicht/feed/`, Termine `/termine/feed/`, dazu Rathaus und Sport mit ihrem Feed.
4. Danach der Redaktionssitzung Bescheid geben: Sie setzt im Fuß der Seite den Link „Merzenich Aktuell in Google News folgen“.

### C. Bing Webmaster Tools (10 Minuten)

1. bing.com/webmasters → „Aus Google Search Console importieren“. Das übernimmt Bestätigung und Sitemaps.
2. IndexNow läuft schon (Schlüsseldatei liefert das Plugin aus). Unter „IndexNow“ sollten nach der nächsten Meldung Einträge erscheinen.

### D. Eigene Kanäle und Profile (je 10 Minuten)

1. Facebook-Seite, Instagram und vor allem einen **WhatsApp-Kanal** „Merzenich Aktuell“ anlegen, falls noch nicht vorhanden. Im Ort teilen Menschen Nachrichten vor allem dort.
2. Die Adressen der Profile der Redaktionssitzung schicken. Sie trägt sie als `sameAs` in die Organisation ein; damit erkennen Google und KI-Systeme die Marke.
3. Optional: Google-Unternehmensprofil als Verlag, nur mit der Adresse aus dem Impressum.

### E. Links aus dem Ort (laufend)

1. **Gemeinde:** um einen Link auf der Gemeindeseite und um Aufnahme in den Presseverteiler bitten (`docs/vorlagen/verlinkung-gemeinde.md`).
2. **Vereine, Feuerwehr, Pfarrei, Schulen:** nach jedem Bericht über sie den Link schicken (`docs/vorlagen/verlinkung-verein.md`). Ziel: 10 Seiten aus dem Ort mit Link in 8 Wochen. Ein Link von der Feuerwehr Merzenich oder der Gemeinde wiegt mehr als hundert Verzeichniseinträge.
3. In lokalen Gruppen einmal vorstellen, danach nur wichtige Einzelmeldungen teilen (`docs/vorlagen/teilen-lokale-gruppen.md`).

### F. Aufräumen bei IONOS (10 Minuten)

1. Die alte Vorschauadresse `merzenichaktuell.hk-growthoperator.de` entweder im DNS löschen oder per 301 auf `https://merzenich-aktuell.de/` umleiten.
2. **Entscheidung Seiten-Cache:** IONOS bietet für WordPress ein Performance-Paket, alternativ ein Cache-Plugin. Vorher mit der Redaktionssitzung testen, damit Werbung, Push-Hinweis und Freigaben weiter funktionieren.

---

## 5. Für die Redaktion: so schreiben wir für Google, News und KI

**Titel**
- Ort und konkretes Ding vorn, „Merzenich“ oder der Ortsteil in den ersten 60 Zeichen. Straße oder Straßennummer nennen, wenn es um einen Ort geht („L 264“, „Bahnstraße“).
- Beispiel gut: „Tödlicher Unfall auf der L 264 bei Girbelsrath: Autofahrer im Krankenhaus gestorben“.
- Gegen news.de, das mit „Polizei-News Merzenich, TT.MM.JJ: …“ die Polizeimeldungen automatisch abschreibt, gewinnen wir nur mit mehr Inhalt: Feuerwehr und Polizei zusammen, Straße und Ortsteil, Folgemeldung, Einordnung. Nie dieselbe Formel kopieren.
- Ortsteile mit Verwechslungsgefahr mit Gemeinde: „Golzheim (Merzenich)“.

**Erster Satz:** Wer, was, wann, wo. KI-Antworten und Google-Vorschauen zitieren meist genau diesen Satz.

**Takt:** jeden Tag mindestens 2 Meldungen, davon eine aus Blaulicht, Rathaus oder Termine. Blaulicht möglichst innerhalb von 60 Minuten nach der Quelle.

**Update oder Folgemeldung:** Kleine Ergänzung → Nachtrag in derselben Meldung. Neue Entwicklung mit eigenem Nachrichtenwert (Tod, Festnahme, Urteil, Beschluss, Absage, Ergebnis) → Folgemeldung mit neuem Datum, die ältere Meldung bekommt einen Nachtrag mit Link (SCHREIBREGELN 0a, SEO-KEYWORDS Regel 5).

**Bild:** mindestens 1200 Pixel breit und zum Thema passend. Ein eigenes Foto aus dem Ort schlägt jedes Symbolbild.

**Interne Links:** jede Meldung auf Ortsseite und Ressort, Folgemeldungen auf die erste Meldung, Termine auf die Terminseite.

**Sammelseiten mit Jahr, rechtzeitig vor dem Anlass**
1. **Martinszüge in Merzenich 2026**: alle Züge mit Ortsteil, Tag, Uhrzeit, Treffpunkt und Quelle. Jetzt, denn die Züge laufen bis Mitte November. Erste Aufgabe für den Nachmittagslauf.
2. Sessionseröffnung 11.11. in Merzenich und Golzheim.
3. Adventsmärkte und Weihnachtsmärkte in Merzenich 2026 (Ende November).
4. Silvester, Neujahrsempfang und Sternsinger 2027 (Mitte Dezember).

---

## 6. Suchbegriffe und Zielseiten

Ohne Suchvolumen-Daten geordnet nach Suchabsicht. Nach vier Wochen mit Search-Console-Zahlen überprüfen (Plan Abschnitt 2).

| Wer sucht was | Beispiele | Zielseite |
|---|---|---|
| Marke | merzenich aktuell, nachrichten merzenich, merzenich news | Startseite, `/nachrichten/` |
| Was ist los | was ist heute in merzenich los, veranstaltungen merzenich, merzenich wochenende | `/heute/`, `/termine/`, Wochenvorschau |
| Blaulicht | unfall merzenich, feuerwehr merzenich einsatz, l264 unfall, polizei düren merzenich | `/blaulicht/`, Einzelmeldungen |
| Anlässe | martinszug merzenich 2026, karneval golzheim, weihnachtsmarkt merzenich | Sammelseite je Anlass und Jahr |
| Rathaus | gemeinderat merzenich, amtsblatt merzenich, bürgermeister merzenich, abfallkalender merzenich | `/rathaus/`, Faktenseiten |
| Strukturwandel | morschenich ort der zukunft, bürgewald, tagebau hambach merzenich | Themenseiten mit Zeitachse |
| Ortsteile | golzheim, girbelsrath, morschenich, bürgewald mit „nachrichten“ oder „termine“ | `/ort/<ort>/` mit Einleitungstext |
| Umkreis | nachrichten düren, niederzier, buir | `/umgebung/` (kommt mit dem Update) |

---

## 7. GEO: von ChatGPT, Perplexity und Google-KI zitiert werden

Steht schon: KI-Crawler ausdrücklich erlaubt, `llms.txt`, saubere Strukturdaten, Quelle an jeder Meldung, IndexNow für Bing.

Fehlt, in dieser Reihenfolge:
1. **Bing Webmaster Tools** (Anleitung C). Ohne Bing kein ChatGPT-Zitat.
2. **`sameAs` der Organisation** mit den Social-Profilen (Anleitung D).
3. **Faktenseiten** mit Fragen als Zwischenüberschrift und kurzer Antwort darunter, Stand-Datum und Quelle: „Gemeinde Merzenich in Zahlen“, „Notdienste und wichtige Nummern“, „Abfuhr und Wertstoffhof“, „Die Ortsteile von Merzenich“, „Morschenich, Bürgewald und der Tagebau Hambach“.
4. **`llms-full.txt`** mit dem Volltext dieser Faktenseiten und der Ortsseiten.
5. **Monatliche Probe:** dieselben 10 Fragen in ChatGPT (mit Suche), Perplexity und Google stellen und notieren, ob Merzenich Aktuell als Quelle erscheint. Fragen: Was ist am Wochenende in Merzenich los? Wann ist der Martinszug in Golzheim? Gab es heute einen Unfall in Merzenich? Was ist in Morschenich geplant? Wer ist Bürgermeister von Merzenich? Wann tagt der Gemeinderat Merzenich? Wo ist der Wertstoffhof in Merzenich? Was kostet die Grundsteuer in Merzenich? Welche Vereine gibt es in Golzheim? Was passiert mit Bürgewald?

---

## 8. Messen und Ziele

**Jede Woche** (sobald der Zugang aus A.6 steht): Klicks, Impressionen, die 10 häufigsten Suchanfragen, News-Impressionen, Discover, Zahl der indexierten Seiten, Meldungen je Tag. Ablage in `docs/SEO-STAND.md`.

| Nach | Ziel |
|---|---|
| 2 Wochen | jeden Tag mindestens 2 Meldungen; Nachrichten-Sitemap nie leer; Search Console, Publisher Center und Bing eingerichtet |
| 6 Wochen | erste Impressionen im Suchtyp „Nachrichten“; „merzenich aktuell“ auf Platz 1; mindestens 3 Links aus dem Ort |
| 3 Monate | Top 3 für „nachrichten merzenich“, „blaulicht merzenich“, „was ist heute in merzenich los“; Sammelseiten auf Seite 1 für ihren Anlass; in 3 von 10 KI-Testfragen als Quelle |

---

## 9. Nächste Schritte der Redaktionssitzung [Code, Redaktion]

1. Prüfen: `/umgebung/` HTTP 200 nach dem stündlichen Lauf (Plugin 1.43.2 live seit 10:50 Uhr); 144/26 nach der Freigabe in der Nachrichten-Sitemap.
2. Nachmittagslauf 03.11.: Sammelseite „Martinszüge in Merzenich 2026“ als Meldung mit allen Terminen aus dem Kalender; zweite Meldung des Tages.
3. Nach Freigabe des Betreibers als ein Plugin- und Theme-Paket: `llms-full.txt`, H1 mit „Merzenich“ auf den Ressortseiten, `sameAs`-Feld für die Organisation, schnellere `/termine/` (Zwischenspeicher).
4. Ortseinleitungen (100 bis 200 Wörter mit Fakten und Quellen) und die erste Faktenseite „Gemeinde Merzenich in Zahlen“.
