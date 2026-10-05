# Google News: Wie Merzenich Aktuell in die Nachrichten kommt

Stand 04.10.2026 · Plugin 1.20.0. Für den Betreiber geschrieben; die technischen Teile sind umgesetzt, die Schritte in Googles Oberflächen kann nur der Betreiber ausführen.

## Woran es bis jetzt lag

Google stuft eine Seite nur dann als Nachrichtenquelle ein, wenn sie **laufend neue, eigene Meldungen** mit klarem Datum, Autor und Impressum liefert. Genau das hat merzenich-aktuell.de bisher nicht getan: Die tagesaktuellen Meldungen entstanden nur auf der Vorschauseite (merzenichaktuell.hk-growthoperator.de). Nach WordPress kamen sie nur, wenn jemand die Import-Datei von Hand einspielte. Am 04.10.2026 war die neueste Meldung auf merzenich-aktuell.de vom 29.09., die News-Sitemap (`/news-sitemap.xml`, Meldungen der letzten 48 Stunden) war leer. Für Google war die Seite damit eine Seite ohne Nachrichten.

Alles andere war schon da: NewsArticle-Daten je Meldung, News-Sitemap, RSS-Feed, Impressum, Über uns, Redaktion, Grundsätze mit Korrekturen, Ethik und Vielfalt, Finanzierung, Kontakt, Datum und Quelle sichtbar, Bildnachweise, IndexNow, Search Console bestätigt.

## Was seit 1.20.0 automatisch läuft: der Abgleich

Das Plugin holt den redaktionellen Stand jetzt **stündlich** selbst (Backend: Merzenich Aktuell → Abgleich). Weil WordPress geplante Aufgaben nur bei Seitenaufrufen ausführt und das hinter dem Cache des Hosters ausbleiben kann (04.10.2026: zehn Stunden kein Lauf), stößt der GitHub-Workflow „WordPress-Abgleich anstoßen“ (`.github/workflows/wordpress-abgleich.yml`) den Lauf stündlich und nach jedem Build der Import-Datei an; unverändert gebliebene Import-Dateien kosten nur eine Kopfanfrage (ETag). Er liest die Import-Datei aus dem Repository (immer der Stand von `main`), legt neue Meldungen und Termine in WordPress an, übernimmt die Bilder mit Nachweis und Rechteprüfung und aktualisiert Texte, die auf der Vorschauseite nachgebessert wurden. Was in WordPress von Hand geändert wurde, bleibt unangetastet; was im Papierkorb liegt, kommt nicht wieder; gelöscht wird nichts.

**Entscheidend für Google News ist eine Einstellung:**

| Einstellung „Neue Meldungen sofort veröffentlichen“ | Wirkung |
| --- | --- |
| aus (Standard) | Neue Meldungen landen als Entwurf in den Freigaben. Erst nach Freigabe stehen sie auf der Seite und in der News-Sitemap. Gibt niemand täglich frei, bleibt die Seite für Google alt. |
| an | Neue Meldungen stehen innerhalb einer Stunde auf merzenich-aktuell.de, mit Startseiten-Freigabe. Termine werden ohnehin sofort veröffentlicht. |

Empfehlung für das Ziel „bei Merzenich in den News vorn“: **an**, solange die Redaktion die Entwürfe nicht täglich freigibt. Die Meldungen sind auf der Vorschauseite ohnehin schon öffentlich und mit Quelle geprüft.

## Was der Betreiber einmalig tun muss

1. **Search Console** (bereits bestätigte Property `merzenich-aktuell.de`): unter Sitemaps zusätzlich `https://merzenich-aktuell.de/news-sitemap.xml` eintragen. Nach dem ersten Abgleich mit Veröffentlichung enthält sie die Meldungen der letzten zwei Tage.
2. **Google Publisher Center** (publishercenter.google.com, mit dem Google-Konto der Search Console):
   - „Publikation hinzufügen“: Name *Merzenich Aktuell*, Sprache Deutsch, Standort Deutschland, Website `https://merzenich-aktuell.de` (die Bestätigung kommt aus der Search Console).
   - Kontakt: `info@kbs-management.tv`, Logo quadratisch und als Querformat (Vorlagen: `chatgpt-site/assets/img/logo.png` und `logo-on-light.png`, Publisher Center verlangt mindestens 512 px Breite).
   - Unter „Google News“ → Inhalte: Abschnitte per Feed anlegen, zum Beispiel *Aktuell* `https://merzenich-aktuell.de/feed/`, *Blaulicht* `https://merzenich-aktuell.de/blaulicht/feed/`, *Sport* `https://merzenich-aktuell.de/sport/feed/`, *Rathaus* `https://merzenich-aktuell.de/rathaus/feed/`.
   - Veröffentlichen. Die Publikation erscheint danach in der Google-News-App unter ihrem Namen und kann von Leserinnen und Lesern abonniert werden; die Prüfung dauert erfahrungsgemäß einige Tage bis wenige Wochen.
3. **Bing** (optional, erreicht auch Nachrichten in Windows und Edge): Bing Webmaster Tools mit derselben Sitemap, dann „Bing PubHub“ für die Publikation.

Eine gesonderte Bewerbung für Google News gibt es seit 2019 nicht mehr. Ob eine Seite in den Reiter „Nachrichten“ und in die Schlagzeilen kommt, entscheidet Google allein aus dem, was die Seite über Wochen liefert.

## Was die Reihenfolge bei „Merzenich“ bestimmt

Die Mitbewerber (Aachener Zeitung, Dürener Nachrichten, Rundschau, Radio Rur) schreiben selten über Merzenich. Wer bei „Merzenich“ in den Nachrichten vorn stehen will, braucht vor allem:

- **Takt:** mehrere eigene Meldungen pro Tag, jeden Tag, auf merzenich-aktuell.de. Google lernt den Rhythmus einer Quelle; eine Woche Pause wirft zurück.
- **Ortsname im Titel und im ersten Absatz:** „Merzenich“, „Golzheim“, „Girbelsrath“, „Morschenich“, „Bürgewald“ dort, wo sie hingehören, nicht in jeden Satz.
- **Eigene Berichte statt Zusammenfassungen:** Google erkennt, ob eine Meldung nur eine Pressemitteilung nacherzählt. Ratssitzung besucht, Verein befragt, Foto vor Ort geschossen: das zählt.
- **Namen in der Byline:** Zurzeit heißt der Autor jeder Meldung „Redaktion Merzenich Aktuell“. Google bevorzugt Meldungen mit einer Person als Autor und einer Autorenseite (Name, kurze Vorstellung, Kontakt). Sobald KBS Autorinnen und Autoren benennt, trägt das Plugin sie in die Strukturdaten ein; die Redaktionsseite (`/redaktion/`) ist dafür vorbereitet.
- **Bilder in drei Formaten:** Für die Schlagzeilen empfiehlt Google je Meldung Bilder in 16:9, 4:3 und 1:1 mit mindestens 1200 px Breite. Die hochgeladenen Fotos sind breit genug; die drei Zuschnitte liefert WordPress noch nicht (offener Punkt, Theme).
- **Geduld:** Nach dem Start einer neuen Quelle dauert es in der Regel zwei bis sechs Wochen, bis Google sie in den Nachrichten zeigt.

## Prüfen, ob es wirkt

- `https://merzenich-aktuell.de/news-sitemap.xml` zeigt die Meldungen der letzten zwei Tage.
- Search Console → Leistung → Suchtyp „Nachrichten“ zeigt Impressionen, sobald Google die Seite als Nachrichtenquelle führt.
- Google-Suche „Merzenich“ → Reiter „Nachrichten“ und `site:merzenich-aktuell.de` → Reiter „Nachrichten“.
- Backend → Abgleich zeigt den letzten Lauf (neu, aktualisiert, Fehler) und das Protokoll.
