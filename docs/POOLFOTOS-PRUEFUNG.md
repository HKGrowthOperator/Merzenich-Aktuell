# Sichtprüfung der Commons-Poolfotos

Stand 2026-09-24. Runde 1: alle 240 Fotos, die am 24.09. aus Wikimedia Commons in die zwölf Pools geladen wurden; 159 ausgeschlossen. Runde 2: die 159 Ersatzfotos; 88 ausgeschlossen. Freigegeben sind 152 Fotos. Maßgeblich ist `deploy/editorial-photo-review.json`; der Importer (`deploy/import-editorial-photos.mjs`) löscht ausgeschlossene Dateien, lädt sie nie wieder und füllt die Lücken aus Commons auf. Neu geladene Fotos gelten als ungeprüft (`geprueft: false`) und werden bei der Bildvergabe nachrangig behandelt, bis sie in einer weiteren Sichtprüfung freigegeben sind.

## Gründe

| Grund | Anzahl |
|---|---|
| Motiv passt nicht zum Pool | 49 |
| Militär- oder Auslandsmotiv | 28 |
| Fast gleiches Motiv wie ein anderes Bild im Pool | 21 |
| Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich | 20 |
| Abzeichen, Wappen oder Logo als Motiv | 19 |
| Grafik, Karte oder Satellitenbild statt Foto | 19 |
| Lesbares Kfz-Kennzeichen | 18 |
| Ortsfremder Name, Schild oder Tafel lesbar | 16 |
| Erkennbare Personen des öffentlichen Lebens | 11 |
| Demonstration mit Parolen und erkennbaren Personen | 9 |
| Ortsfremde Kennzeichnung (anderes Bundesland oder Ausland) | 8 |
| Unscharf oder ohne Bildaussage | 8 |
| Erkennbare Personen als Hauptmotiv | 7 |
| Falscher Ort: Köln, Firmenschriftzug Bäckerei „Merzenich“ | 4 |
| Markenetikett oder Werbeschriftzug im Bild | 4 |
| Ortsfremdes Wahrzeichen | 2 |
| Extremes Panoramaformat, taugt nicht für Bildflächen | 2 |
| Unfallstelle mit Personen | 1 |
| Transparent mit politischer Parole | 1 |

Wichtigster Befund: Es gibt einen zweiten Ort Merzenich, einen Stadtteil von Zülpich (Kreis Euskirchen), und in Köln die Bäckereikette „Merzenich“. Die Suche nach „Merzenich Germany“ hat beides als Gemeinde Merzenich markiert. Der ganze Pool „Leben“ (St. Severinus in Zülpich-Merzenich) und zwei Bilder in „Aktuell“ zeigten deshalb den falschen Ort. Der Importer erkennt Zülpich, Euskirchen und Köln jetzt und verortet solche Treffer nicht mehr in Merzenich.

In Runde 2 lieferte die Volltextsuche für Blaulicht vor allem Motive aus Japan, für Brand aus Österreich und für Sport Wegekreuze „hinter dem Sportplatz“. Diese Pools suchen seit Runde 3 über Commons-Kategorien (`deepcat:`), dazu kommen Filter gegen Auslandsmotive und nicht-lateinische Titel.

## Je Pool

### Aktuell: 4 entfernt

| Runde | Nr. | Commons-Datei | Grund |
|---|---|---|---|
| 1 | 02 | Merzenich zülpich.JPG | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 15 | Merzenich (Zülpich) 001x.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 19 | Wohn- und Geschäftshaus Eigelstein 89-91, Köln-4719.jpg | Falscher Ort: Köln, Firmenschriftzug Bäckerei „Merzenich“ |
| 1 | 20 | Köln (Germany) (23573767200).jpg | Falscher Ort: Köln, Firmenschriftzug Bäckerei „Merzenich“ |

### Blaulicht: 36 entfernt

| Runde | Nr. | Commons-Datei | Grund |
|---|---|---|---|
| 1 | 01 | Bundespolizei (Deutschland) – Einsatzfahrzeug Volkswagen T5, Kennz. BG34-71 (Aufnahmejahr 2006).jpg | Lesbares Kfz-Kennzeichen |
| 1 | 02 | Polizeistern Baden-Württemberg-Fahrzeug.JPG | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 03 | Einsatzfahrzeug Polizei Baden-Württemberg.JPG | Ortsfremde Kennzeichnung (anderes Bundesland oder Ausland) |
| 1 | 04 | Einsatzfahrzeug Polizei auf dem Neckar.JPG | Ortsfremde Kennzeichnung (anderes Bundesland oder Ausland) |
| 1 | 05 | Einsatzfahrzeug Berufsfeuerwehr Heidelberg Übungseinsatz.JPG | Lesbares Kfz-Kennzeichen |
| 1 | 06 | Ziegler Feuerwehrfahrzeug HD.JPG | Lesbares Kfz-Kennzeichen |
| 1 | 07 | Übungseinsatz Heidelberger Berufsfeuerwehr.JPG | Lesbares Kfz-Kennzeichen |
| 1 | 08 | Feuerwehr Plankstadt Einsatzfahrzeug MAN Ziegler.JPG | Lesbares Kfz-Kennzeichen |
| 1 | 09 | MAN Ziegler 49 Feuerwehr Plankstadt.JPG | Lesbares Kfz-Kennzeichen |
| 1 | 10 | Plankstadt Feuerwehr Ziegler 49.JPG | Ortsfremder Name, Schild oder Tafel lesbar |
| 1 | 11 | Juni 2012 Einsatzfahrzeug THW Fulda.JPG | Lesbares Kfz-Kennzeichen |
| 1 | 12 | Karlsruhe Einsatzfahrzeug Polizei Juli 2012.JPG | Lesbares Kfz-Kennzeichen |
| 1 | 13 | Hamburg, Ausnahmegenehmigung NDR NIK 8024.jpg | Motiv passt nicht zum Pool |
| 1 | 14 | Bergwacht Bayern 6987.jpg | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 16 | Feuerwehr Illingen (2023-08-15) 01.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 1 | 17 | Deutsches Rotes Kreuz Landesverband Saarland (2023-08-15) 01.jpg | Lesbares Kfz-Kennzeichen |
| 1 | 18 | Unimog Sanitaeter.jpg | Militär- oder Auslandsmotiv |
| 1 | 19 | Zepper-BK 117-C2-(EC145)-SchweizerischeRettungsflugwacht.jpg | Militär- oder Auslandsmotiv |
| 1 | 20 | KTW 4 breit.jpg | Lesbares Kfz-Kennzeichen |
| 2 | 02 | Ashikaga Bank Automated teller machine.jpg | Militär- oder Auslandsmotiv |
| 2 | 03 | ORBIS3（Rear）.JPG | Militär- oder Auslandsmotiv |
| 2 | 04 | A Dosimeter in Japan.jpg | Militär- oder Auslandsmotiv |
| 2 | 05 | Sound level meter in Japan.jpg | Militär- oder Auslandsmotiv |
| 2 | 06 | Drehspiegelleuchte Innenansicht.jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 2 | 07 | Sound level meter in Japan (4464526592).jpg | Militär- oder Auslandsmotiv |
| 2 | 08 | 緊急時 避難壕 44 第一機械リース (5693044604).jpg | Militär- oder Auslandsmotiv |
| 2 | 09 | 危険ゼロ 戸田建設 (14208257443).jpg | Militär- oder Auslandsmotiv |
| 2 | 10 | Model car of Minato 303.JPG | Militär- oder Auslandsmotiv |
| 2 | 11 | Model car of Minato 302.JPG | Militär- oder Auslandsmotiv |
| 2 | 12 | Model car of Nissan LEOPARD Ultima MY1987 ver.Saraba Abunai Deka.JPG | Militär- oder Auslandsmotiv |
| 2 | 13 | Nissan LEOPARD ULTIMA MY1987 Ver.Saraba Abunai Deka (1).JPG | Militär- oder Auslandsmotiv |
| 2 | 14 | Nissan LEOPARD ULTIMA MY1987 Ver.Saraba Abunai Deka.JPG | Militär- oder Auslandsmotiv |
| 2 | 16 | Nissan LEOPARD ULTIMA MY1987 Ver.Saraba Abunai Deka (2).JPG | Militär- oder Auslandsmotiv |
| 2 | 17 | 1500円床屋 (14430674608).jpg | Militär- oder Auslandsmotiv |
| 2 | 18 | Before you go on the tour, leave you cell phone and camera in one of these lockers. (30252631372).jpg | Militär- oder Auslandsmotiv |
| 2 | 19 | Osaka Den Den Town lighting fixture shop. (26155362333).jpg | Militär- oder Auslandsmotiv |

### Polizei: 26 entfernt

| Runde | Nr. | Commons-Datei | Grund |
|---|---|---|---|
| 1 | 02 | North Rhine-Westphalia Police Patch.jpg | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 03 | VW Passat Variant B6 Type 3C since 2005 Polizei NRW Germany backright 2008-03-27 A.jpg | Lesbares Kfz-Kennzeichen |
| 1 | 04 | VW Passat Variant B6 Type 3C since 2005 Polizei NRW Germany frontleft 2008-03-27 A.jpg | Lesbares Kfz-Kennzeichen |
| 1 | 05 | 20090720 VW Passat Variant FuStW Polizei NRW PB LED.JPG | Lesbares Kfz-Kennzeichen |
| 1 | 06 | Landesleitstelle Polizei NRW 18 08 11 (1).JPG | Motiv passt nicht zum Pool |
| 1 | 07 | Landesleitstelle Polizei NRW 18 08 11 (2).JPG | Motiv passt nicht zum Pool |
| 1 | 08 | Germany - Polizei Nordrhein-Westfalen (4518423229).jpg | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 09 | Germany - Polizei Nordrhein Westfalen Marine WSP (woven) (4518428099).jpg | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 10 | Germany - Polizei Nordrhein Westfalen (green felt) (5406219030).jpg | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 11 | Germany - Polizei Nordrhein Westfalen (woven)(yellow text) (5406221022).jpg | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 12 | Germany - Polizei Nordrhein Westfalen (old style) (5406220796).jpg | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 13 | Germany - Polizei Nordrhein Westfalen (black felt)(WSP Wasserschutz Polizei Marine) (5406224108).jpg | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 15 | Verkehrsunfall Willich-Neersen 2014-10-06 005.jpg | Unfallstelle mit Personen |
| 1 | 16 | Fachhochschule für öffentliche Verwaltung Nordrhein-Westfalen, Köln-8556.jpg | Motiv passt nicht zum Pool |
| 1 | 17 | Germany license plate of the polizei in Nordrhein-Westfalen.jpg | Lesbares Kfz-Kennzeichen |
| 1 | 18 | Fachhochschule für öffentliche Verwaltung Nordrhein-Westfalen, Erna-Scheffler-Straße 4, 51103 Köln-4403.jpg | Motiv passt nicht zum Pool |
| 1 | 19 | Fachhochschule für öffentliche Verwaltung Nordrhein-Westfalen, Erna-Scheffler-Straße 4, 51103 Köln-4404.jpg | Motiv passt nicht zum Pool |
| 1 | 20 | Fachhochschule für öffentliche Verwaltung Nordrhein-Westfalen, Erna-Scheffler-Straße 4, 51103 Köln-4589.jpg | Motiv passt nicht zum Pool |
| 2 | 03 | Rheinberg, Kirchplatz, 2014-08 CN-01.jpg | Motiv passt nicht zum Pool |
| 2 | 04 | Rheinberg, Polizeiwache, 2014-08 CN-01.jpg | Motiv passt nicht zum Pool |
| 2 | 06 | Münster, Julius-Voos-Gasse, Konventsgebäude -- 2017 -- 9802.jpg | Motiv passt nicht zum Pool |
| 2 | 10 | Aussetzen einer Belohnung Verbrechen in der Silvesternacht 2015-2016 Köln -4597.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 2 | 12 | 2019 Polizei NRW Koeln im Gespraech.jpg | Abzeichen, Wappen oder Logo als Motiv |
| 2 | 15 | Frank-Arno Richter.jpg | Erkennbare Personen des öffentlichen Lebens |
| 2 | 18 | Polizeihubschrauber (10566995274).jpg | Erkennbare Personen als Hauptmotiv |
| 2 | 20 | Bell 47J Polizeifliegerstaffel Nordrhein-Westfalen.jpg | Abzeichen, Wappen oder Logo als Motiv |

### Feuerwehr: 12 entfernt

| Runde | Nr. | Commons-Datei | Grund |
|---|---|---|---|
| 1 | 06 | Feuerwehr Bünde (5).jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 1 | 08 | Feuerwehr Bünde (4).jpg | Unscharf oder ohne Bildaussage |
| 1 | 10 | Feuerwehr Bünde (6).jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 1 | 12 | Feuerwehr Bünde (8).jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 1 | 13 | Feuerwehr Bünde (7).jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 1 | 15 | 12-12-15 Feuerwehr Köttingen.jpg | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 16 | Freiwillige Feuerwehr Werl Westoennen 01.jpg | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 20 | Dülmen, Hausdülmen, Feuerwehr, Eingang -- 2013 -- 00082.jpg | Abzeichen, Wappen oder Logo als Motiv |
| 2 | 08 | Recke Feuerwehrhaus 01.jpg | Abzeichen, Wappen oder Logo als Motiv |
| 2 | 12 | Borken, Burlo -- 2014 -- 2282.jpg | Motiv passt nicht zum Pool |
| 2 | 13 | Altes Feuerwehrgerätehaus.jpg | Markenetikett oder Werbeschriftzug im Bild |
| 2 | 20 | Unterrichtsraum Münster.jpg | Motiv passt nicht zum Pool |

### Brand: 36 entfernt

| Runde | Nr. | Commons-Datei | Grund |
|---|---|---|---|
| 1 | 01 | Fire in a tire depot - 2012 April 27th - Mörfelden-Walldorf -7.jpg | Lesbares Kfz-Kennzeichen |
| 1 | 02 | Schöne Eiche bei Endlichhofen.jpg | Motiv passt nicht zum Pool |
| 1 | 04 | Great Fire London.jpg | Motiv passt nicht zum Pool |
| 1 | 05 | Fighting fire and smoke.jpg | Erkennbare Personen als Hauptmotiv |
| 1 | 06 | German military fire engine at Camp Marmal.jpg | Militär- oder Auslandsmotiv |
| 1 | 07 | Smoke On The Water, Fire In The Sky - panoramio.jpg | Motiv passt nicht zum Pool |
| 1 | 08 | Levada do Norte mirroring forest fire smoke (38095309461).jpg | Motiv passt nicht zum Pool |
| 1 | 09 | Pagami smoke plumes.png | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 10 | Thomas Fire smoke plume 16 Dec Aqua MODIS nat (25236397938).jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 11 | Thomas Fire smoke plume 16 Dec Aqua MODIS nat NIR SWIR (39102612851).jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 12 | Thomas Fire smoke plume 15 Dec S3 OLCI 764 (38367402104).jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 13 | Thomas Fire smoke plume 14 Dec S3 OLCI 764 (24202237697).jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 14 | Thomas Fire smoke plume 12 Dec Suomi NPP VIIRS (39019370261).jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 15 | Thomas Fire smoke plume 11 Dec S3 OLCI 764 (38110587015).jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 16 | Thomas Fire smoke plume 10 Dec S3 OLCI 764 (38946055042).jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 17 | Smoke of a possible oil fire (46340055041).jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 18 | Camp Fire smoke plume Aqua Nov 12 nat (45809607862).jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 19 | Camp Fire smoke plume Aqua Nov 12 nat IR (45809607382).jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 20 | McCash Fire, River Complex 2021, Monument Fire, smoke-filled valleys, California, USA - September 4th, 2021 (51427680005).jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 2 | 01 | Brand Waldschlag(40938214984).jpg | Ortsfremde Kennzeichnung (anderes Bundesland oder Ausland) |
| 2 | 02 | Brand Waldschlag(40757854275).jpg | Ortsfremde Kennzeichnung (anderes Bundesland oder Ausland) |
| 2 | 04 | 2024-03-22 FF Redlham Frühjahrsübung (84) (53603386567).jpg | Ortsfremde Kennzeichnung (anderes Bundesland oder Ausland) |
| 2 | 05 | PV Seibersdorf 1 (54374923374).jpg | Ortsfremde Kennzeichnung (anderes Bundesland oder Ausland) |
| 2 | 06 | PV Seibersdorf 2 (54375116470).jpg | Ortsfremde Kennzeichnung (anderes Bundesland oder Ausland) |
| 2 | 07 | PV Seibersdorf 3 (54374959628).jpg | Ortsfremde Kennzeichnung (anderes Bundesland oder Ausland) |
| 2 | 08 | ABI Konrad PV Seibersdorf 4 (54373846232).jpg | Motiv passt nicht zum Pool |
| 2 | 09 | Brand Hotel Knoche1.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 2 | 12 | Brand in der Maschinenfabrik Buckau R. Wolf AG in der Werftstraße 214-216 (Kiel 32.283).jpg | Erkennbare Personen als Hauptmotiv |
| 2 | 13 | Brand in der Maschinenfabrik Buckau R. Wolf AG in der Werftstraße 214-216 (Kiel 32.284).jpg | Erkennbare Personen als Hauptmotiv |
| 2 | 14 | Landesweite Zivilschutzübung "Orkan 67", 07. - 08.10.1967 (Kiel 42.072).jpg | Erkennbare Personen als Hauptmotiv |
| 2 | 15 | Halon fire suppression warning sign.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 2 | 16 | Argonite automatic fire suppression system server room.jpg | Motiv passt nicht zum Pool |
| 2 | 17 | Fire suppression agent 3.jpg | Motiv passt nicht zum Pool |
| 2 | 18 | Controls for FM-200 fire suppression system at NERSC.jpg | Motiv passt nicht zum Pool |
| 2 | 19 | FM-200 fire suppression system at NERSC.jpg | Motiv passt nicht zum Pool |
| 2 | 20 | I-DPCN at work 03 (4203528315).jpg | Militär- oder Auslandsmotiv |

### Sport: 29 entfernt

| Runde | Nr. | Commons-Datei | Grund |
|---|---|---|---|
| 1 | 01 | Nationalspielerinnen-Debutalter.PNG | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 02 | Kanone4.jpg | Motiv passt nicht zum Pool |
| 1 | 03 | DFB-Mitglieder.jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 05 | National Football Museum Manchester 5697 (14180312576).jpg | Motiv passt nicht zum Pool |
| 1 | 06 | CINvPGH 2018-04-21 - Kevin Kerr (26993231997) (cropped).jpg | Erkennbare Personen des öffentlichen Lebens |
| 1 | 08 | Suburban football field – Preview (Grzegorz Wronkowski via Poly Haven).png | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 09 | Suburban football field – Panorama (Grzegorz Wronkowski via Poly Haven).jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 10 | Cancha de Balompié del COS Sports Plaza.jpg | Motiv passt nicht zum Pool |
| 1 | 11 | 20100819-fußballplatz-soccer-football-ground-nogometno-igralište-trnoc-čakovečka-školska-stadion-nk-podturen-wegweiser-fingerpost-putokaz-1.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 1 | 12 | 20100819-fußballplatz-soccer-football-ground-nogometno-igralište-trnoc-čakovečka-školska-stadion-nk-podturen- wegweiser-fingerpost-putokaz-2.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 1 | 14 | 20100819-fußballplatz-soccer-football-ground-nogometno-igralište-trnoc-čakovečka-školska-stadion-nk-podturen-gerade-trainerbänke-dugouts-10.jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 1 | 15 | 20100819-fußballplatz-soccer-football-ground-nogometno-igralište-trnoc-čakovečka-školska-stadion-nk-podturen-gerade-trainerbänke-dugouts-11.jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 1 | 16 | 20100819-fußballplatz-soccer-football-ground-nogometno-igralište-trnoc-čakovečka-školska-stadion-nk-podturen-gerade-trainerbänke-dugouts-14.jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 1 | 18 | 20100819-fußballplatz-soccer-football-ground-nogometno-igralište-trnoc-čakovečka-školska-stadion-nk-podturen- vereinsgebäude-klubhaus-kabinen-3.jpg | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 19 | 20100819-fußballplatz-soccer-football-ground-nogometno-igralište-trnoc-čakovečka-školska-stadion-nk-podturen- vereinsgebäude-klubhaus-kabinen-1.jpg | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 20 | 20100819-fußballplatz-soccer-football-ground-nogometno-igralište-trnoc-čakovečka-školska-stadion-nk-podturen- vereinsgebäude-klubhaus-kabinen-4.jpg | Abzeichen, Wappen oder Logo als Motiv |
| 2 | 01 | Vettweiß-Gladbach Denkmal-Nr. Gla-10, Mersheim -Ecke Sportplatz (1819).jpg | Motiv passt nicht zum Pool |
| 2 | 02 | Vettweiß-Kelz Denkmal-Nr. Kel-01, Mühlenweg (1839).jpg | Motiv passt nicht zum Pool |
| 2 | 03 | Hürtgen WK an Wegekreuz südl. vom Sportplatz.jpg | Motiv passt nicht zum Pool |
| 2 | 05 | Hürtgen WK an Wegekreuz südl. vom Sportplatz-Infotafel.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 2 | 06 | Bergstein Bildstock hinter dem Sportplatz.jpg | Motiv passt nicht zum Pool |
| 2 | 08 | Bergstein Bildstock hinter dem Sportplatz - Infotafel.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 2 | 09 | Luftaufnahme Langerwehe 2025-06-04.jpg | Motiv passt nicht zum Pool |
| 2 | 10 | Luftaufnahme Langerwehe 2025-06-03.jpg | Motiv passt nicht zum Pool |
| 2 | 11 | Luftaufnahme Langerwehe 2025-06-02.jpg | Motiv passt nicht zum Pool |
| 2 | 12 | Buscherheide Kapelle am Sportplatz Altar.jpg | Motiv passt nicht zum Pool |
| 2 | 14 | Buscherheide Kapelle am Sportplatz Ansicht.jpg | Motiv passt nicht zum Pool |
| 2 | 15 | Buscherheide Kapelle am Sportplatz Gitter.jpg | Motiv passt nicht zum Pool |
| 2 | 20 | Beckwitz Am Sportplatz 15-02.jpg | Motiv passt nicht zum Pool |

### Termine: 4 entfernt

| Runde | Nr. | Commons-Datei | Grund |
|---|---|---|---|
| 1 | 04 | Hopsten Kirmes 2014 Pharaos Rache 01.JPG | Markenetikett oder Werbeschriftzug im Bild |
| 1 | 11 | D-NW-LIP-Bad Salzuflen-Wüsten - Kirmes 1970-001.jpg | Erkennbare Personen als Hauptmotiv |
| 1 | 13 | Münster, Domplatz, Wochenmarkt -- 2019 -- 2648.jpg | Markenetikett oder Werbeschriftzug im Bild |
| 2 | 04 | Münster, St.-Paulus-Dom -- 2019 -- 2698.jpg | Ortsfremdes Wahrzeichen |

### Vereine: 28 entfernt

| Runde | Nr. | Commons-Datei | Grund |
|---|---|---|---|
| 1 | 01 | Düren Denkmal-Nr. 1-120, Meckerstraße 7 b (298).jpg | Motiv passt nicht zum Pool |
| 1 | 02 | Bad Godesberg Bürgerstraße 2 1890.jpg | Motiv passt nicht zum Pool |
| 1 | 03 | 2014-03-30 10-43-34 RX100 3287 Zerkall A1x2.jpg | Motiv passt nicht zum Pool |
| 1 | 04 | 2018-10-07 17-01-06 kW-S7 Obermaubach A1x2 Hm5HTm10.jpg | Motiv passt nicht zum Pool |
| 1 | 07 | 2019-11-01-heimbach-abtei-mariawald-01.jpg | Motiv passt nicht zum Pool |
| 1 | 08 | 2019-11-01-heimbach-abtei-mariawald-02.jpg | Motiv passt nicht zum Pool |
| 1 | 09 | 2019-11-01-heimbach-abtei-mariawald-03.jpg | Motiv passt nicht zum Pool |
| 1 | 10 | 2019-11-01-heimbach-abtei-mariawald-04.jpg | Abzeichen, Wappen oder Logo als Motiv |
| 1 | 11 | 2019-11-01-heimbach-abtei-mariawald-05.jpg | Motiv passt nicht zum Pool |
| 1 | 12 | Ibbenbueren Unterer Markt Mittwochsdemonstration KJS Steinfurt Tecklenburg 01.JPG | Demonstration mit Parolen und erkennbaren Personen |
| 1 | 13 | Ibbenbueren Unterer Markt Mittwochsdemonstration KJS Steinfurt Tecklenburg 03.JPG | Demonstration mit Parolen und erkennbaren Personen |
| 1 | 14 | Ibbenbueren Unterer Markt Mittwochsdemonstration KJS Steinfurt Tecklenburg 02.JPG | Demonstration mit Parolen und erkennbaren Personen |
| 1 | 15 | Ibbenbueren Unterer Markt Mittwochsdemonstration KJS Steinfurt Tecklenburg 04.JPG | Demonstration mit Parolen und erkennbaren Personen |
| 1 | 16 | Ibbenbueren Unterer Markt Mittwochsdemonstration KJS Steinfurt Tecklenburg 05.JPG | Demonstration mit Parolen und erkennbaren Personen |
| 1 | 17 | Ibbenbueren Unterer Markt Mittwochsdemonstration KJS Steinfurt Tecklenburg 06.JPG | Demonstration mit Parolen und erkennbaren Personen |
| 1 | 18 | Ibbenbueren Unterer Markt Mittwochsdemonstration KJS Steinfurt Tecklenburg 07.JPG | Demonstration mit Parolen und erkennbaren Personen |
| 1 | 19 | Ibbenbueren Unterer Markt Mittwochsdemonstration KJS Steinfurt Tecklenburg 08.JPG | Demonstration mit Parolen und erkennbaren Personen |
| 1 | 20 | Ibbenbueren Unterer Markt Mittwochsdemonstration KJS Steinfurt Tecklenburg 09.JPG | Demonstration mit Parolen und erkennbaren Personen |
| 2 | 02 | Krippken Mettingen 2.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 2 | 03 | Krippken Mettingen 3.jpg | Unscharf oder ohne Bildaussage |
| 2 | 07 | Mettingen Krippken 06.jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 2 | 08 | Dülmen, Annette-von-Droste-Hülshoff-Gymnasium (Spinnerei Bendix) -- 2013 -- 00071.jpg | Motiv passt nicht zum Pool |
| 2 | 09 | Dülmen, Annette-von-Droste-Hülshoff-Gymnasium -- 2015 -- 4927.jpg | Motiv passt nicht zum Pool |
| 2 | 11 | Verl-Alter-Postweg-Schuützenhalle-Kaunitz-2026-08-03.jpg | Unscharf oder ohne Bildaussage |
| 2 | 14 | Laggenbeck 01.jpg | Motiv passt nicht zum Pool |
| 2 | 16 | Laggenbeck Dorfgemeinschaftshaus 02.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 2 | 19 | Hausdülmen, Dorfplatz -- 2012 -- 3779 (bw).jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 2 | 20 | Hausdülmen, Maibaum -- 2014 -- 0134.jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |

### Leben: 29 entfernt

| Runde | Nr. | Commons-Datei | Grund |
|---|---|---|---|
| 1 | 01 | Köln (Germany) (22860737980).jpg | Falscher Ort: Köln, Firmenschriftzug Bäckerei „Merzenich“ |
| 1 | 02 | Köln (Germany) (19732049521).jpg | Falscher Ort: Köln, Firmenschriftzug Bäckerei „Merzenich“ |
| 1 | 03 | St. Severinus (Merzenich, Zülpich) 02.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 04 | St. Severinus (Merzenich, Zülpich) 01.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 05 | St. Severinus (Merzenich, Zülpich) 03.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 06 | St. Severinus (Merzenich, Zülpich) 04.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 07 | St. Severinus (Merzenich, Zülpich) 05.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 08 | St. Severinus (Merzenich, Zülpich) 07.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 09 | St. Severinus (Merzenich, Zülpich) 08.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 10 | St. Severinus (Merzenich, Zülpich) 09.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 11 | St. Severinus (Merzenich, Zülpich) 06.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 12 | St. Severinus (Merzenich, Zülpich) 10.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 13 | St. Severinus (Merzenich, Zülpich) 11.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 14 | St. Severinus (Merzenich, Zülpich) 12.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 15 | St. Severinus (Merzenich, Zülpich) 13.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 16 | St. Severinus (Merzenich, Zülpich) 14.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 17 | St. Severinus (Merzenich, Zülpich) 15.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 18 | St. Severinus (Merzenich, Zülpich) 17.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 19 | St. Severinus (Merzenich, Zülpich) 18.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 1 | 20 | St. Severinus (Merzenich, Zülpich) 16.jpg | Falscher Ort: Merzenich bei Zülpich (Kreis Euskirchen), nicht Gemeinde Merzenich |
| 2 | 03 | Steinsfeld, Am Dorfplatz 1, 002.jpg | Markenetikett oder Werbeschriftzug im Bild |
| 2 | 06 | Hausdülmen, E-Bike-Ladestation am Dorfplatz -- 2014 -- 0132.jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 2 | 07 | Hausdülmen, E-Bike-Ladestation am Dorfplatz -- 2014 -- 0133.jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 2 | 11 | Am Dorfplatz 5 Rockau 2.JPG | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 2 | 12 | Am Dorfplatz 5 Rockau 1.JPG | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 2 | 14 | Scheune mit angrenzendem Wohnhaus am Dorfplatz 23 in Meimersdorf (Kiel 56.737).jpg | Unscharf oder ohne Bildaussage |
| 2 | 17 | Helfenberg Am Dorfplatz 5 2019-06-20 (6).jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 2 | 18 | Helfenberg Am Dorfplatz 5 2019-06-20 (7).jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 2 | 20 | Gasthaus eines ehemaligen Vierseithofes Am Dorfplatz 3 Oberuttlau Haarbach Ansicht von Nordosten.jpg | Lesbares Kfz-Kennzeichen |

### Wirtschaft: 6 entfernt

| Runde | Nr. | Commons-Datei | Grund |
|---|---|---|---|
| 1 | 04 | Hambach surface mine and Hambach Forest.png | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 05 | Tagebau Hambach und Hambacher Forst 2018.jpg | Grafik, Karte oder Satellitenbild statt Foto |
| 1 | 12 | Preparation area between the open pit mine Hambach and the Hambach forest 02.jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 1 | 16 | Preparation area between the open pit mine Hambach and the Hambach forest 07.jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 1 | 19 | Preparation area between the open pit mine Hambach and the Hambach forest 11.jpg | Lesbares Kfz-Kennzeichen |
| 2 | 05 | RWE security point at the Hambach forest 09.jpg | Transparent mit politischer Parole |

### Tipp: 11 entfernt

| Runde | Nr. | Commons-Datei | Grund |
|---|---|---|---|
| 1 | 01 | Dueren Stefan-Schwer-Strasse 4-6 City Archive.jpg | Lesbares Kfz-Kennzeichen |
| 1 | 03 | Roedinghausen Radnetz.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 1 | 07 | Recke Poggenpaettken 04.jpg | Unscharf oder ohne Bildaussage |
| 1 | 08 | Recke Poggenpaettken 05.jpg | Unscharf oder ohne Bildaussage |
| 1 | 09 | Recke Poggenpaettken 07.jpg | Unscharf oder ohne Bildaussage |
| 1 | 16 | Toeddenland Radweg Mettingen Haus Telsemeyer 01.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 1 | 17 | Toeddenland Radweg Mettingen Brenninckhof 01.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 1 | 19 | Wildgänse auf dem Radweg an der Ruhr - panoramio (1).jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 1 | 20 | 00 7344 Köln - Hohenzollernbrücke.jpg | Motiv passt nicht zum Pool |
| 2 | 01 | Sophienhöhe l.JPG | Extremes Panoramaformat, taugt nicht für Bildflächen |
| 2 | 08 | Sophienpanorama.jpg | Extremes Panoramaformat, taugt nicht für Bildflächen |

### Menschen: 26 entfernt

| Runde | Nr. | Commons-Datei | Grund |
|---|---|---|---|
| 1 | 01 | Upholstering the seats of your own car, Margarita Island, Venezuela.jpg | Militär- oder Auslandsmotiv |
| 1 | 02 | Informal settlement where people now have community ablution blocks (8152040443).jpg | Militär- oder Auslandsmotiv |
| 1 | 03 | Cologne Germany Cologne-Gay-Pride-2015 Parade-03.jpg | Erkennbare Personen als Hauptmotiv |
| 1 | 04 | Beach, New Brighton, New Zealand 06.jpg | Motiv passt nicht zum Pool |
| 1 | 05 | 2022-07-03 Basketball, Männer, European Qualifiers, Deutschland - Polen 1DX 1281 by Stepro.jpg | Erkennbare Personen des öffentlichen Lebens |
| 1 | 06 | 2022-07-03 Basketball, Männer, European Qualifiers, Deutschland - Polen 1DX 1386 by Stepro.jpg | Erkennbare Personen des öffentlichen Lebens |
| 1 | 07 | Dülmen, Jüdischer Friedhof -- 2023 -- 6451 (bw).jpg | Motiv passt nicht zum Pool |
| 1 | 08 | 2022-08-19 European Championships 2022 – Table Tennis by Sandro Halank–007.jpg | Erkennbare Personen des öffentlichen Lebens |
| 1 | 09 | The community of Israeli Jews originally born in Hamburg, Germany holding a meeting in Tel Aviv attended by the German Ambassador to Israel Niels Hansen (FL45851174).jpg | Erkennbare Personen des öffentlichen Lebens |
| 1 | 10 | After a 36 year break from Germany, new LRC director is back to support Stuttgart community (7131116).jpg | Erkennbare Personen des öffentlichen Lebens |
| 1 | 11 | LRC Stuttgart employee with important family commitments in U S says she’ll miss the Stuttgart community, people (7088057).jpg | Erkennbare Personen des öffentlichen Lebens |
| 1 | 12 | Home Secretary Yvette Cooper signs landmark plan with German Federal Minister of the Interior and Community Nancy Faeser to break the business model of people smuggling gangs on 9 December 2024 - 1.jpg | Erkennbare Personen des öffentlichen Lebens |
| 1 | 13 | Home Secretary Yvette Cooper signs landmark plan with German Federal Minister of the Interior and Community Nancy Faeser to break the business model of people smuggling gangs on 9 December 2024 - 2.jpg | Erkennbare Personen des öffentlichen Lebens |
| 1 | 14 | Home Secretary Yvette Cooper signs landmark plan with German Federal Minister of the Interior and Community Nancy Faeser to break the business model of people smuggling gangs on 9 December 2024 - 3.jpg | Erkennbare Personen des öffentlichen Lebens |
| 1 | 15 | Wiesbaden community takes back the night (5809869).jpg | Militär- oder Auslandsmotiv |
| 1 | 16 | Wiesbaden community takes back the night (5809872).jpg | Militär- oder Auslandsmotiv |
| 1 | 17 | Wiesbaden community takes back the night (5809875).jpg | Militär- oder Auslandsmotiv |
| 1 | 18 | Wiesbaden community takes back the night (5809878).jpg | Militär- oder Auslandsmotiv |
| 1 | 19 | Wiesbaden community takes back the night (5809880).jpg | Militär- oder Auslandsmotiv |
| 1 | 20 | Wiesbaden community takes back the night (5809881).jpg | Militär- oder Auslandsmotiv |
| 2 | 02 | Münster, Park Sentmaring -- 2015 -- 01058.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 2 | 06 | Dülmen, Skulpturen im Bendixpark -- 2015 -- 8519.jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 2 | 09 | Dülmen, Parkbank am Wildpark -- 2016 -- 0349-55.jpg | Unscharf oder ohne Bildaussage |
| 2 | 10 | Dülmen, Parkbank am Wildpark -- 2016 -- 0356-62.jpg | Fast gleiches Motiv wie ein anderes Bild im Pool |
| 2 | 11 | Münster, Park Sentmaring -- 2016 -- 0786.jpg | Ortsfremder Name, Schild oder Tafel lesbar |
| 2 | 12 | Münster, Fürstbischöfliches Schloss -- 2026 -- 0742-8.jpg | Ortsfremdes Wahrzeichen |

