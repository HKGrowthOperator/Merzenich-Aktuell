# Verbindlicher Branding-Auftrag – Logo 04 (09.11.2026)

**Entscheidung:** Für Merzenich Aktuell ist von Arun und Ordin die Variante **04 „Gemeinsam“** final ausgewählt. Das auf der Website zu verwendende Logo ist ausschließlich:

`artifacts/branding/2026-11-logo/04-alle-drei.svg`

## Nicht verhandelbar
- Diese vorhandene **Originaldatei unverändert** verwenden. Sie zeigt Wasserturm links, Kirche in Gold mittig, Fachwerkhaus/Heimatmuseum rechts, „MERZENICH“ schwarz, goldene Linie mit Zackenmotiv und „AKTUELL“ bordeauxrot.
- **Nicht** nachzeichnen, per KI neu generieren oder Typografie, Proportionen, Bildmotive, Farben, Anordnung, Abstände verändern.
- Das Auswahllabel **„04 | Gemeinsam“** und die grüne handschriftliche Markierung im Screenshot sind **nicht** Bestandteil des Logos. Keinen zusätzlichen Slogan einbauen.
- Die SVG enthält eine eingebettete PNG-Bildebene und ergänzende Vektorelemente. Beim Kopieren nichts daraus entfernen. Die Masterdatei bleibt unverändert im Artefaktordner.
- Andere Variationen `01`, `02`, `03`, `05` sind nicht freigegeben.

## Implementierung mit Claude Code
1. Vorab `CLAUDE.md` und `PROJECT.md` lesen, tatsächliche Website-Build- und Deploymentpfade prüfen.
2. Alle produktiven Logostellen auf der Hauptseite, Unterseiten, Desktop/Mobil-Navigation, Footer, Sticky Header, 404 und sonstigen Layouts identifizieren und diese gezielt auf **Originalvariante 04** umstellen.
3. Die SVG in den öffentlich ausgelieferten Assets ablegen oder gemäß bestehender Asset-Pipeline unverändert referenzieren. Nicht direkt auf ein nicht-deploytes `artifacts/`-Verzeichnis zeigen.
4. Original-Seitenverhältnis erhalten (SVG `viewBox="0 0 720 350"` und 1440×700). Responsive und vollständig: `height:auto`, `object-fit:contain`. Nirgends beschneiden, verzerren, invertieren oder umkolorieren. Auf geeigneter hell/cremefarbener Fläche platzieren.
5. Vorhandene OG-/Social-/PWA-/Icon-Metadaten auf alte Branding-Bilder prüfen; wo sinnvoll das neue Gesamtlogo einsetzen. Keine neue unabhängige Favicon-Marke entwerfen. Nicht ungezielt Texte ersetzen.
6. Sonstige Seitengestaltung, Nachrichten, Werbung, Dienste, Redaktion und Automation unberührt lassen. Vorhandene Tests/Build-/QA-Prozesse ausführen.
7. Desktop-, Tablet- und Mobilansichten sowie mindestens drei Unterseiten visuell prüfen, Asset-Status/Caching testen, Vorher/Nachher-Screenshots und geänderte Pfade berichten.

## Fertig, wenn
Überall wo bisher das Markenlogo zu sehen ist, erscheint **exakt die Version 04** aus `04-alle-drei.svg`, ohne grüne Umkreisung, ohne „04 | Gemeinsam“, ohne andere Ergänzungen. Alle bisherigen Funktionen bleiben erhalten.

**Arbeitsauftrag:** Implementieren und testen; keine neue Entwurfsrunde starten.

## Umsetzung (09.11.2026, Theme 21.30.0, Plugin 1.48.3)

**Master:** Die Datei `04-alle-drei.svg` zeigt nicht das freigegebene Motiv. Sie enthält den alten Schriftzug mit groben Strich-Symbolen: Turm und Haus klein über der Schrift, die Kirche im „H“. Der Betreiber hat am 09.11. das Original als Bild geschickt und bestätigt: „Mein Anhang“. Verbindlich ist deshalb dieses Bild, Byte für Byte unverändert:

- `artifacts/branding/2026-11-logo/04-final-original.png`: Archiv, PNG 1348 × 692, weißer Grund, sha256 `13caa3a26cd89ad906d0…`
- `chatgpt-site/assets/marke/logo-04.png`: Web-Asset, identische Datei, live unter `/assets/marke/logo-04.png?v=<Inhalts-Hash>`

`04-alle-drei.svg` und die Varianten 01, 02, 03 und 05 bleiben als Entwurfsarchiv liegen und sind nirgends auf der Website eingebunden.

**Wo das Logo steht:**
- **Kopf (Masthead) aller Seiten:** auf der Startseite in der H1. Breite 230 px am Desktop, 200 px bis 900 px, 170 px am Handy, 150 px bis 374 px, 136 px bis 340 px; Höhe immer automatisch im Seitenverhältnis.
- **Handy-Menü (Schublade):** 170 px auf einer weißen Fläche, weil die Schublade dunkel ist. Das Logo selbst wird nicht umgefärbt.
- **Fuß:** 190 px am Desktop, 150 px am Handy.
- **Strukturierte Daten:** `logo` der Organisation in den statischen Seiten und im Plugin (`includes/seo.php`), 1348 × 692.

Gesetzt wird das zentral über `deploy/kopf-theme-einbinden.mjs` für alle Seiten. Das CSS steht gesammelt am Ende von `chatgpt-site/assets/korrekturen.css`.

**Ableitung, zur Freigabe vorgesehen:**
- `chatgpt-site/assets/img/og-default.jpg`, das Vorschaubild für soziale Netze: 1200 × 630, weißer Grund, darauf das unveränderte Logo, proportional auf 1000 px Breite skaliert und mittig.
- Sonst ist nichts hinzugefügt.

**Unverändert, mit Begründung:**
- Das Monogramm „M“ im eingeklappten Kopf und im Cookie-Hinweis, außerdem Favicon und App-Symbole. Das sind quadratische Zeichen; eine neue quadratische Fassung von Logo 04 wäre eine eigene Markenvariante, und die ist nicht freigegeben.
- Anleitungs-PDF und -Video nutzen noch den alten Schriftzug. Sie gehören nicht zur Website.

## Live geprüft (09.11.2026, 20:27 Uhr, Theme 21.30.1, Plugin 1.48.3)

Vorher und nachher: [Desktop](logo-04/logo04-desktop-vorher-nachher.png) und [Handy, Menü, Fuß](logo-04/logo04-handy-vorher-nachher.png).

| Breite | Logo im Kopf | Kopfhöhe | Fuß-Logo |
|---|---|---|---|
| 1440 / 1180 | 230 × 118 | 134 (vorher 86) | 190 × 98 |
| 820 | 200 × 103 | 119 | 190 × 98 |
| 390 | 170 × 87 | 95 (vorher 64) | 150 × 77 |
| 360 | 150 × 77 | 85 | 150 × 77 |
| 320 | 136 × 70 | 78 | 150 × 77 |

- **Seitenverhältnis:** überall 1,948 = 1348 : 692.
- **Überlauf und Fehler:** kein waagrechter Überlauf. 0 Konsolenfehler auf Startseite, `/impressum/`, `/termine/` und `/unternehmen/`. Die 404-Seite meldet nur ihren eigenen Status.
- **Layout-Verschiebung:** am Handy 0,004, vorher 0,006. Am Desktop 0,163, vorher 0,177; der Rest kommt aus der Ressortleiste, nicht vom Logo.
- **Auslieferung:** `/assets/marke/logo-04.png` antwortet mit 200 und `image/png`, die Bytes sind gleich dem Master. Die Datei liegt im Theme unter `static/`, keine CSP- oder CORS-Fehler.
- **Druckansicht:** Logo 200 × 103.
