# Sportvereine, Motive und Bildfreigaben · 01.10.2026

## Änderung an WordPress
Die bestehende Sportseite (/sport/) bleibt erhalten: Fußball-Spiel-/Tabellenmodul,
Meldungsfeed und Vereinsseiten werden nicht ersetzt. Zusätzlich listet das neue
\`includes/sport-vereine.php\` jeden belegten Nicht-Fußball-Sport aus
\`data/vereinsverzeichnis.json\`, getrennt nach Sportart. Abteilungen haben
jeweils eigene Karten, bleiben jedoch dem Profil des Hauptvereins zugeordnet.
Auch die Düren Demons werden wegen des Merzenicher Vereinssitzes gezeigt, mit
deutlichem Hinweis auf den Spielbetrieb in Düren. Grundlage ist das Verzeichnis,
kein neu erfundener Sportkalender.

## Redaktionsquellen (Vereinsdaten)
- Offizielles Verzeichnis: https://www.gemeinde-merzenich.de/leben/vereinsverzeichnis.php
- Gemeindliche Förderung / Liste aller Sportvereine (2024):
  https://www.gemeinde-merzenich.de/bindata/vereine-ehrenamt/Neufassung-Richtlinien-fuer-die-Vereinsfoerderung-Stand-08.10.2024.pdf
- TTC: https://ttc-merzenich.de/aktuelles/vereinsmeisterschaften2026/
- SC Merzenich Tennis + Badminton: https://www.scmerzenich.de/
- Tennis Girbelsrath: https://tvm.liga.nu/cgi-bin/WebObjects/nuLigaTENDE.woa/wa/clubInfoDisplay?club=36528
- Turnen: https://www.tv-merzenich.de/TV_merzenich/aktuelles/
- Billard: https://bsc-merzenich.de/
- Luftsport: https://www.ul-a-mo.de/
- Ehrungen Sportschießen, Billard mit Originalberichterstattung:
  https://www.gemeinde-merzenich.de/aktuelles/pressemitteilungen/ehrenamtsfest-2025.php

## Originalbildkandidaten – keine implizite Nutzungserlaubnis
Folgende Seiten zeigen bzw. verlinken Material, das die Redaktion direkt beim
Rechteinhaber anfragen kann. „Öffentlich sichtbar“ ist keine Fotolizenz.
- TTC Merzenich: Bildmaterial in Vereinsmeisterschaften 2026 und Vereinshistorie; Nutzungs-/Weiterveröffentlichungsrecht offen.
- TV Merzenich: Vorstandsbild 2026 auf /TV_merzenich/aktuelles/; Freigabe und Personenrechte offen.
- UL-Aero-Club: eigene Vereins-/Flugplatzbilder; Freigabe offen.
- Gemeinde Merzenich: Bilder vom Ehrenamtsfest 2025 zu Billard, Sportschützen Gut Schuss und KK Klub Waldesgrün; Gemeinde/Urheber um Erlaubnis fragen.
- SC Merzenich, FC Golzheim, FC Rhenania Girbelsrath: Abteilungsfotos nur nach Bestätigung der konkreten Tennis-/Badminton-Bildrechte.
- BSC Merzenich: Vereins- und Turnierbilder nur nach Freigabe.

Keine dieser externen Bilddateien wird ohne eindeutige Rechteklärung kopiert,
eingebettet oder als eigene Lokalaufnahme ausgegeben.

## WordPress-Bildsystem (vorbereitet und aktiviert)
1. Hauptvereinsprofil \`ma_club\`: für abteilungsspezifische Bilder ein
   Attachment-ID-Metafeld \`ma_sportfoto_<sportart>\` anlegen (z. B.
   \`ma_sportfoto_tennis\`). Bei Abteilungen bleibt das Profil des Hauptvereins maßgeblich.
2. Am jeweiligen Attachment Pflichtdaten:
   \`ma_image_rights_verified=1\`, \`ma_image_license\` (konkrete Freigabe/
   Lizenz), \`ma_image_credit\`, \`ma_image_original_url\`, WP-Alt-Text.
   Fehlt ein Pflichtfeld, erscheint dieses Bild nicht.
3. Thematische Pools: 20–30 **inhaltlich gesichtete** WordPress-Medien
   pro Sportart, Attachment-Metafeld \`ma_sportpool=<sportart>\` plus dieselben
   Rechtefelder. Die Bildwahl für einen Verein ist stabil, nicht per Page-Reload
   zufällig. Uploads müssen vor Verwendung rechtlich und redaktionell geprüft sein.
4. Noch fehlender Pool: eine der zehn neu im Theme mitgelieferten und als
   „Symbolgrafik · Merzenich Aktuell“ gekennzeichneten Sportmotivgrafiken.
   Diese Grafiken sind keine angeblichen Fotos der lokalen Vereine.
5. Prüfen: \`php -l\` für Plugin und Theme; WP Sport-Archiv (Desktop/Mobile),
   alle zehn Gruppierungen, Profil-Links, Bildnachweise und Bildrechte.

**Status:** Code und 10 eigene Sportmotivgrafiken sind GitHub-Quellstand,
nicht automatisch Live-CMS-Inhalt. Die bestehende installierte WordPress-Instanz
benötigt die aktuelle Theme-/Plugin-Version (ZIPs entstehen über CI beim Push).
Originalfotos mit ungeklärten Rechten sind weiterhin ausdrücklich offen.

## Vorab lizenzierte Bilder, mit WordPress-Theme ausgeliefert
Die vier im bestehenden Repo-Manifest bereits als gesichtet markierten Wikimedia-Fotos
wurden ohne erneutes Scraping direkt per identischer Git-Blob-SHA in
`wordpress/theme/merzenich-aktuell/assets/img/sportfotos/` übernommen.
Lizenz, Urheber, fremder Aufnahmeort und Originaldatei sind in
`wordpress/plugin/merzenich-aktuell-core/data/sport-fotopool.json` hinterlegt.
Direkt enthalten: Tennisplatz Dülmen, Tanzhaus NRW, Sporthalle Ennigloh in
Bünde, Berentelghalle Mettingen. Es sind ausdrücklich Symbolbilder.

## Vertiefung: belegte Sportangebote in bestehenden Turnvereinen (01.10.2026)
Diese Angebote sind **keine neuen Vereine**; sie werden durch
`data/sport-angebote.json` mit dem bestehenden Hauptvereinsprofil verbunden.
- TV 1910 Girbelsrath: aktueller Hallenplan vom 03.03.2026, einschließlich
  Tischtennis, Pickleball, Boule, Ju-Jutsu, Aquafitness, Zumba, Qi Gong und Wandern:
  https://www.tv-girbelsrath.com/hallenplan ; Badminton und Schach auf der
  Vereins-Startseite https://www.tv-girbelsrath.com/
- TV Merzenich: Schwimmen, Aquafitness, Zumba, Fitness und Volleyball:
  https://www.tv-merzenich.de/TV_merzenich/aktuelles/
- TV Golzheim: Tischtennis, Badminton, Volleyball, Fitness und Wandern laut
  https://www.gemeinde-merzenich.de/leben/vereinsverzeichnis.php
Aktuelle konkrete Trainingszeiten werden **nicht** aus alten Screenshots übernommen.
Sieben zusätzliche eigene Symbolgrafiken sind im Theme enthalten.
