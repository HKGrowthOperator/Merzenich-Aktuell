# Lieferstand: statische Linie und WordPress-Theme

Dieser Ordner enthält einen **zweiten, eigenständigen Arbeitsstand** von Merzenich
Aktuell. Er liegt bewusst neben dem Frontend im Wurzelverzeichnis und ersetzt es
nicht.

## Verhältnis zu PROJECT.md

`PROJECT.md` im Wurzelverzeichnis legt fest: *Recovery + Completion + Polish, kein
radikales Redesign, kein Parallelprojekt.* Der Stand in diesem Ordner ist nach
dieser Definition ein Parallelprojekt: eigener Generator, eigenes Inhaltsmodell,
schwarzer Zeitungskopf statt des dort vorgeschriebenen weißen Sticky-Headers.

Er wird deshalb als Branch und Pull Request abgelegt, nicht nach `main` gebracht.
Die Entscheidung, ob davon etwas übernommen wird, ob beides parallel läuft oder ob
der Ordner wieder verschwindet, liegt beim Auftraggeber.

## Was hier liegt

| Ordner | Inhalt |
|---|---|
| `ma9/` | Statischer Generator ohne Fremdpakete (Node ≥ 20, ESM), Inhalte als Markdown unter `content/`, Ausgabe nach `dist/` |
| `wp-theme/` | WordPress-Theme `merzenich-aktuell`, 22 PHP-Dateien plus `inc/`, Stylesheet zeichengleich mit der statischen Linie |
| `lokal/` | WordPress lokal ohne Docker und ohne MySQL (SQLite), zum Ansehen beider Linien nebeneinander |
| `gen-wxr.mjs` | Erzeugt `starter-content-v17.xml` (WXR 1.2) aus `ma9/content/` für den WordPress-Import |

Beide Linien liefern denselben Funktionsumfang und werden gemeinsam auf Stand
gehalten. Das Stylesheet ist in beiden Linien dieselbe Datei.

## Bauen und ansehen

```bash
cd lieferstand/ma9
node build.mjs                 # Produktionsbuild nach dist/ (mit Netlify Bild-CDN)
IMAGE_CDN=off node build.mjs   # lokale Vorschau ohne CDN-Pfade
node scripts/check.mjs         # prüft Seiten, Strukturdaten, Links
python3 -m http.server 8080 -d dist
```

WordPress lokal:

```bash
bash lieferstand/lokal/setup-localhost.sh
php -S 127.0.0.1:8080 -t lieferstand/lokal/instanz/wp
```

## Stand der Prüfung

204 Seiten, 792 Strukturdatenblöcke ohne Fehler, keine kaputten Links, 32
Schlagzeilen auf der Startseite ohne Dublette. WordPress alle Seiten HTTP 200,
keine PHP-Fehler, keine JavaScript-Fehler.

## Offene Punkte

- **§ 18 Abs. 2 MStV**: Das Impressum nennt noch keinen Verantwortlichen. Ein
  journalistisch-redaktionelles Angebot braucht dafür eine natürliche Person mit
  ladungsfähiger Anschrift. Im Impressum der KBS Management GmbH steht keine.
  Bewusst als sichtbarer Platzhalter stehengelassen, nichts erfunden.
- **Umsatzsteuer-ID**: liegt als `332847684` vor, also ohne Länderpräfix.
  Vermutlich `DE332847684`, nicht eigenmächtig ergänzt.
- **Fremdbilder**: 34 Bilder in 31 Meldungen werden von den Servern der Feuerwehr
  Merzenich (19), der Gemeinde Merzenich (14) und des Kreises Düren (1) geladen.
  In der statischen Linie laufen sie über die Netlify Bild-CDN, in WordPress
  direkt. Beides ist in der Datenschutzerklärung unter „Bilder externer Quellen“
  offengelegt. Eine Freigabe der jeweiligen Stellen ist nicht dokumentiert.
- **AJ Sports Entertainment**: Werbebanner ohne Motiv und ohne Zieladresse.
