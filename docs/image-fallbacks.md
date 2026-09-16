# Merzenich Aktuell — Bild-Fallbacks und Rechte

Stand / Rechte-Check: **16.09.2026**

## Verbindliche Reihenfolge

1. Konkretes Bild zur Meldung mit verifiziertem Nutzungsrecht.
2. Passendes lizenziertes Archiv-/Kategorie-Bild.
3. Zentrales Symbolbild aus dieser Fallback-Bibliothek.

Ein Symbolbild wird nur verwendet, wenn kein rechtlich nutzbares konkretes Bild zur Meldung vorliegt. Es wird im sichtbaren Credit immer mit **„Symbolbild“** gekennzeichnet. Fremde Bilder aus Presse-, Immobilien- oder Jobanzeigen werden ohne geklärtes Nutzungsrecht nicht übernommen.

Die technische Zuordnung liegt in `/image-fallbacks.js`.

## Fallback-Bibliothek

| Typ | Einsatz | Quelle / Datei | Urheber | Lizenz | Öffentlicher Credit |
|---|---|---|---|---|---|
| Polizei | Einbruch, Verkehr, Fahndung, sonstige Polizeimeldungen | Wikimedia Commons: `Neue Streifenwagen für die Polizei vorgestellt.jpg` | IM NRW | CC0 1.0 | `Symbolbild · IM NRW / Wikimedia Commons · CC0 1.0` |
| Feuerwehr | Brand, technische Hilfe, Feuerwehr-/Rettungseinsatz | Wikimedia Commons: `Feuerwehrmänner im Einsatz.jpg` | AK-Bino | CC BY-SA 4.0 | `Symbolbild · AK-Bino / Wikimedia Commons · CC BY-SA 4.0` |
| Gemeinde | Rathaus, Verwaltung, allgemeine Gemeinde-Themen | Wikimedia Commons: `Merzenich Rathaus HDR.jpg` | Karl-Heinz Meurer | CC BY-SA 3.0 | `Symbolbild · Karl-Heinz Meurer / Wikimedia Commons · CC BY-SA 3.0` |
| Leben | Gemeindeleben, Kirche, allgemeine lokale Geschichten | Wikimedia Commons: `Merzenich Alte Pfarrkirche.jpg` | Karl-Heinz Meurer | CC BY-SA 3.0 | `Symbolbild · Karl-Heinz Meurer / Wikimedia Commons · CC BY-SA 3.0` |
| Termine / Vereine | Veranstaltungen und Vereinsleben | Wikimedia Commons: `Merzenich Denkmal-Nr. 18, Lindenplatz (1235).jpg` | Käthe und Bernd Limburg | CC BY-SA 3.0 DE | `Symbolbild · Käthe und Bernd Limburg / Wikimedia Commons · CC BY-SA 3.0 DE` |
| Sport | Fußball / Lokalsport | Wikimedia Commons: `2026-08-30 Fußballplatz Trogen HOF8480 RAW-Export.png` | PantheraLeo1359531 | CC BY-SA 4.0 | `Symbolbild · PantheraLeo1359531 / Wikimedia Commons · CC BY-SA 4.0` |
| Stellen | Stellenangebote ohne freigegebenes Arbeitgeberfoto | Wikimedia Commons: `LOOM office workspaces.jpg` | Loominade | CC0 1.0 | `Symbolbild · Loominade / Wikimedia Commons · CC0 1.0` |
| Immobilien | Immobilienangebote ohne freigegebenes Objektfoto | Wikimedia Commons: `Merzenich Denkmal-Nr. 20, Lindenplatz 5 (1238).jpg` | Käthe und Bernd Limburg | CC BY-SA 3.0 DE | `Symbolbild · Käthe und Bernd Limburg / Wikimedia Commons · CC BY-SA 3.0 DE` |

## Quellen

- Polizei: https://commons.wikimedia.org/wiki/File:Neue_Streifenwagen_f%C3%BCr_die_Polizei_vorgestellt.jpg
- Feuerwehr: https://commons.wikimedia.org/wiki/File:Feuerwehrm%C3%A4nner_im_Einsatz.jpg
- Gemeinde: https://commons.wikimedia.org/wiki/File:Merzenich_Rathaus_HDR.jpg
- Leben: https://commons.wikimedia.org/wiki/File:Merzenich_Alte_Pfarrkirche.jpg
- Termine: https://commons.wikimedia.org/wiki/File:Merzenich_Denkmal-Nr._18,_Lindenplatz_(1235).jpg
- Sport: https://commons.wikimedia.org/wiki/File:2026-08-30_Fu%C3%9Fballplatz_Trogen_HOF8480_RAW-Export.png
- Stellen: https://commons.wikimedia.org/wiki/File:LOOM_office_workspaces.jpg
- Immobilien: https://commons.wikimedia.org/wiki/File:Merzenich_Denkmal-Nr._20,_Lindenplatz_5_(1238).jpg

## Technische Regeln

- `image-fallbacks.js` greift nur ein, wenn `article.image` fehlt oder eine Blaulichtmeldung noch das bekannte generische Rathaus-Platzhalterbild trägt.
- Vorhandene konkrete Bilder werden nicht überschrieben.
- Blaulicht wird anhand des redaktionellen Textes in Polizei oder Feuerwehr unterschieden.
- Stellen- und Immobilienkarten erhalten nur dann ein fremdes konkretes Bild, wenn `imageRightsVerified === true` gesetzt ist. Sonst bleibt das geprüfte Symbolbild aktiv.
- Bildquelle und Lizenz werden als Metadaten am Fallback hinterlegt; in der Artikeldetailansicht wird die Bildquelle verlinkt.
- Bei einem rein technischen Ladefehler wird einmalig auf das Gemeinde-Symbolbild zurückgefallen, damit die Seite nicht bildlos bleibt.
