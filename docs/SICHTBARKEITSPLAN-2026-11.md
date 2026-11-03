# Sichtbarkeitsplan: Merzenich Aktuell bei Google sichtbar machen

Stand 03.11.2026. Anlass: Google-Suche „merzenich“ am 03.11.2026 um 10:26 Uhr (Bildschirmfotos von Luis Kummer). Merzenich Aktuell steht weder unter „Alle“ noch unter „News“. Ergänzt `docs/SEO-GEO-BERICHT-2026-11-03.md` (Technik und Anleitung) um die Reihenfolge, in der wir vorgehen.

---

## 1. Was die Suche zeigt

**Unter „Alle“:** Wissenskasten der Gemeinde (Wikipedia, Karte, Wetter), danach gemeinde-merzenich.de. Den Wissenskasten bekommt keine Zeitung; erreichbar sind die Plätze darunter.

**Unter „News“ zum Verkehrstod auf der L 264:**

| Quelle | Titel | online (aus „vor x Stunden“ um 10:26 Uhr, ± 30 Min.) |
|---|---|---|
| Polizei Düren (Presseportal) | „22-Jähriger nach Verkehrsunfall auf der L264 verstorben“, Ortszeile Merzenich | 02.11., **15:08 Uhr** |
| AOL (Kopie von news.de) | „Polizei-News Merzenich, 02.11.26: 22-Jähriger nach Verkehrsunfall auf der L264 verstorben“ | etwa 16:30 Uhr |
| Radio Rur | „22-jähriger Euskirchener stirbt nach Unfall in Merzenich“ | etwa 17:30 Uhr |
| dueren-magazin.de | „Merzenich: 22-Jähriger nach Verkehrsunfall auf der L264 verstorben“ | etwa 17:30 Uhr |
| Aachener Zeitung | „Nach schwerem Verkehrsunfall auf der L264: 22-Jähriger erliegt seinen Verletzungen“ | etwa 18:30 Uhr |
| **Merzenich Aktuell** | „Tödlicher Unfall auf der L 264 bei Girbelsrath: Autofahrer im Krankenhaus gestorben“ | **18:56 Uhr**, nicht gelistet |

**Drei Gründe, nach Gewicht:**
1. **Google kennt uns noch kaum.** Die Seite ist seit Ende September online, ohne Links aus dem Ort und ohne bestätigte Search Console. Ob und wie viele Seiten im Index sind, weiß niemand. Google kann alles lesen (am 03.11. mit dem Googlebot-Kennzeichen geprüft: Startseite, Meldung, Nachrichten-Sitemap und robots.txt antworten mit 200), aber Lesen heißt nicht Vertrauen.
2. **Wir waren die Letzten.** 3 Stunden 48 Minuten nach der Polizei, alle anderen 1 bis 3 Stunden früher.
3. **„Merzenich“ fehlte im Titel.** Alle gelisteten Titel nennen Merzenich oder die L264 mit Merzenich; unserer nur Girbelsrath. Von 25 Claude-Titeln seit 25.10. hatten 19 kein „Merzenich“.

---

## 2. Der Plan als Baum

### 🌱 Wurzel: Google muss uns kennen (diese Woche, Betreiber)

Ohne diese Schritte bleibt alles andere unsichtbar. Anleitung im Detail: `docs/SEO-GEO-BERICHT-2026-11-03.md`, Abschnitt 4.

| # | Schritt | Dauer | Woran man sieht, dass es klappt |
|---|---|---|---|
| W1 | Auf dem Handy `site:merzenich-aktuell.de` und `merzenich aktuell` bei Google suchen, Bildschirmfoto an die Redaktionssitzung | 1 Min. | Zeigt, ob und wie viele Seiten im Index sind |
| W2 | Search Console: Domain bestätigen, beide Sitemaps einreichen | 20 Min. | Sitemaps „Erfolgreich“, Seiten unter „Indexiert“ |
| W3 | Search Console: URL-Prüfung → „Indexierung beantragen“ für Startseite, `/nachrichten/`, `/blaulicht/` und die drei neuesten Meldungen | 10 Min. | „URL ist auf Google“ |
| W4 | Lesezugang für die Redaktionssitzung (Dienstkonto als Umgebungsgeheimnis `GOOGLE_SERVICE_ACCOUNT_JSON`) | 15 Min. | Wöchentliche Zahlen in `docs/SEO-STAND.md` |
| W5 | Publisher Center anlegen, Rubriken als Feeds | 30 Min. | Publikation sichtbar, Logo da |
| W6 | Bing Webmaster Tools aus der Search Console importieren | 10 Min. | Sitemaps in Bing, IndexNow-Einträge |
| W7 | Alte Vorschauadresse bei IONOS löschen oder umleiten | 10 Min. | keine Antwort 503 mehr |

### 🪵 Stamm: schneller und treffender als die anderen (ab heute, Redaktion und Code)

| # | Schritt | Stand |
|---|---|---|
| S1 | **„Merzenich“ in jedem Titel**, Ereignis in den ersten 60 Zeichen. `qa/redaktion-lauf.mjs` lässt ab 03.11. keinen Claude-Titel ohne „Merzenich“ durch. | **erledigt 03.11.**; die beiden L-264-Titel sind umgestellt |
| S2 | **Eil-Prüfung:** Polizei, Feuerwehr und Heimat-Info alle zwei Stunden prüfen (06:40, 10:40, 12:40, 16:40, 18:40, 20:40 Uhr; 09:13 und 14:43 übernehmen die Läufe), neue Merzenich-Meldung sofort schreiben und veröffentlichen. Gestern hätte das etwa 16:50 statt 18:56 Uhr bedeutet. | **beschlossen 03.11.** (Luis Kummer), Routine eingerichtet, erste Prüfung 12:40 Uhr; Ablauf: CLAUDE-LAUF Abschnitt 2a |
| S3 | **Mehr als die Polizei:** jede Blaulicht-Meldung mit dem, was die Kopierer nicht haben: Feuerwehr und Polizei zusammen, Sperrung und Umleitung, Einordnung aus unserem Archiv (etwa: wie viele Unfälle auf der L 264 in diesem Jahr), Link auf die erste Meldung | Regel in den Läufen ab heute |
| S4 | **Alter und Wohnort in Blaulicht-Meldungen** so, wie die Polizei sie nennt, auch im Titel; nie Namen, Hausnummern, Kennzeichen oder Angaben darüber hinaus (SCHREIBREGELN Abschnitt 7) | **beschlossen 03.11.** (Luis Kummer); Folgemeldung L 264 umgestellt |
| S5 | **Takt:** jeden Tag mindestens zwei Meldungen, eine davon aus Blaulicht, Rathaus oder Termine | läuft |
| S6 | **Freigaben am selben Tag:** Feuerwehr-Automatik und andere Entwürfe tragen seit Plugin 1.43.0 die Freigabezeit; je schneller freigegeben, desto eher in Google News | Luis, täglich |

### 🌳 Krone: Vertrauen und Inhalte, die bleiben (Woche 2 bis 8)

| # | Schritt | Wer |
|---|---|---|
| K1 | **Links aus dem Ort:** Gemeinde (Link und Presseverteiler), Feuerwehr Merzenich, Pfarrei, Schulen, Vereine. Ein Link von gemeinde-merzenich.de oder der Feuerwehr wiegt mehr als jeder technische Kniff. Ziel: 10 Links in 8 Wochen. Vorlagen in `docs/vorlagen/`. | Betreiber, Redaktion schickt nach jedem Bericht den Link |
| K2 | **Eigene Kanäle:** WhatsApp-Kanal, Facebook, Instagram; Adressen als `sameAs` eintragen | Betreiber, Code |
| K3 | **Sammelseiten zum Anlass:** zuerst „Martinszüge in Merzenich 2026“ (jetzt gesucht), dann Sessionseröffnung 11.11., Advents- und Weihnachtsmärkte | Redaktion, ab Nachmittagslauf 03.11. |
| K4 | **Ortsseiten** mit 100 bis 200 Wörtern Fakten und Quellen; **Faktenseiten** („Merzenich in Zahlen“, Notdienste, Abfuhr, Ortsteile, Strukturwandel) | Redaktion, Code |
| K5 | **Tempo der Seite:** Seiten-Cache, `/termine/` schneller | Code, Betreiber entscheidet |
| K6 | **KI-Suchen:** `llms-full.txt`, monatliche Probe mit 10 Fragen | Code, Redaktion |

---

## 3. Was realistisch ist

- **Google News und Top Stories** zeigen neue Quellen erst, wenn Google ihnen über Wochen vertraut: regelmäßiger Takt, eigene Inhalte, Links aus der Region. Für neue Lokalseiten sind 4 bis 12 Wochen üblich; garantieren kann das niemand.
- Gegen Aachener Zeitung, WDR und Radio Rur gewinnen wir nicht durch Größe, sondern durch **Tempo, Nähe und Vollständigkeit** bei Merzenich-Themen, die sie nur am Rand bringen: Feuerwehr-Einsätze, Rat und Ausschüsse, Vereine, Termine, Ortsteile.
- Zuerst erreichbar: Platz 1 für „merzenich aktuell“, Einträge im Reiter „News“ bei Merzenich-Meldungen, Seite 1 für Anlässe („martinszug merzenich 2026“). Danach: „blaulicht merzenich“, „nachrichten merzenich“.

| Nach | Ziel |
|---|---|
| 1 Woche | W1 bis W6 erledigt; jede neue Meldung mit „Merzenich“ im Titel; Blaulicht spätestens etwa zwei Stunden nach der Quelle |
| 4 Wochen | alle Meldungen im Index (Search Console); erste Einträge im Reiter „News“; 3 Links aus dem Ort |
| 8 Wochen | Platz 1 „merzenich aktuell“; regelmäßig im Reiter „News“ bei Merzenich-Meldungen; 10 Links aus dem Ort |
| 3 Monate | Seite 1 für „nachrichten merzenich“ und „blaulicht merzenich“; mindestens eine Meldung in Top Stories |

Gemessen wird wöchentlich mit den Zahlen aus der Search Console (W4) in `docs/SEO-STAND.md`. Bis dahin: einmal je Woche dieselbe Handy-Suche wie am 03.11. („merzenich“, Reiter „Alle“ und „News“) als Bildschirmfoto.
