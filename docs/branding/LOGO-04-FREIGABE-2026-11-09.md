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
