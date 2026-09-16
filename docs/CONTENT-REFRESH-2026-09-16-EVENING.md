# Merzenich Aktuell – Content-Refresh 16.09.2026, Abend

Prüfstand: **16.09.2026 · 19:11 Uhr (Europe/Berlin)**

Verbindliche Reihenfolge: **RESEARCH → VERIFY → IMAGE RIGHTS → CONTENT → FRONTEND → QA → PUSH-STATUS**.

## Statusdefinition

- **Fakt verifiziert** = Inhalt anhand einer Original- oder Primärquelle geprüft.
- **Bild gefunden** = ein Motiv existiert; daraus folgt noch kein Nutzungsrecht.
- **Bildrecht verifiziert** = Lizenz/Freigabe ist belastbar dokumentiert.

Diese drei Zustände werden nicht gleichgesetzt.

## Nachrichten / Gemeinde

### Vier Fahrrad-Reparaturstationen
- Fakt verifiziert: **JA**
- Originalquelle: Gemeinde Merzenich über Heimat-Info
- Quelle: https://www.heimat-info.de/beitraege/0ddfdf29-5e8b-4248-afa6-49ea616b1847
- Quellenstand: 11.09.2026, 10:00 Uhr
- Letzte Prüfung: 16.09.2026, 19:11 Uhr
- Verifiziert: Standorte Lindenplatz Merzenich, Buirer Straße Golzheim, Dechant-Fabry-Straße Girbelsrath, Bürgewaldzentrum Morschenich; Werkzeug und Luftpumpe kostenlos nutzbar.
- Bild gefunden: **JA**, auf der Originalmeldung
- Bildrecht verifiziert: **NEIN**
- Konsequenz: kein fremdes Quellenfoto übernommen; Beitrag ohne ungeklärtes Originalbild.

## Polizei

### Geschwindigkeitskontrolle mit Grundschule
- Fakt verifiziert: **JA**
- Originalquelle: Polizei Düren / Presseportal
- Quelle: https://www.presseportal.de/blaulicht/pm/8/6353148
- Veröffentlichung: 16.09.2026, 11:14 Uhr
- Letzte Prüfung: 16.09.2026, 19:11 Uhr
- Verifiziert: Aktion am 15.09.; Viertklässler, Bezirksdienst, Verkehrsdienst und Ordnungsamt; 37 Zitronen an zu schnelle Verkehrsteilnehmende.
- Bild gefunden: **JA**, Pressefoto an der Meldung
- Bildrecht verifiziert: **NEIN** für eine Veröffentlichung durch Merzenich Aktuell
- Konsequenz: Pressefoto nicht übernommen; gekennzeichnetes Blaulicht-Symbolbild.

Bereits vor diesem Pass verifiziert und weiter gültig: Polizeimeldungen vom 15.09. und 16.09.2026. Nicht jede Meldung mit Bezug zu einer Person aus Merzenich wird automatisch als Merzenicher Lokalereignis hochpriorisiert.

## Feuerwehr

- Fakt verifiziert: **JA**, Stand aus dem bereits abgeschlossenen Feuerwehr-Refresh übernommen und erneut auf dem offiziellen Heimat-Info-Kanal gegengeprüft.
- Letzte aktuelle sichtbare Einsatzmeldung beim Check: Einsatz 124/26 vom 11.09.2026, Ölspur B264/Mühlenstraße.
- Originalquelle: Freiwillige Feuerwehr Merzenich über Heimat-Info
- Übersicht: https://www.heimat-info.de/gemeinden/merzenich
- Letzte Prüfung: 16.09.2026, 19:11 Uhr
- Neue ungeklärte Einsatzbilder wurden in diesem Pass nicht übernommen.

## Veranstaltungen und Termine

- Faire Woche: 11.–25.09.2026 – **aktiv**.
- Müllsammelaktion: 20.09.2026, 11:00–13:00 Uhr – **kommend**.
- Originalquelle: Gemeinde Merzenich / Heimat-Info
- Quelle: https://www.heimat-info.de/beitraege/fdb48f37-4d9d-4e4d-8a5b-533bc9832951
- Letzte Prüfung: 16.09.2026, 19:11 Uhr.

Zeitabhängig zurückgestuft/aus „kommend“ entfernt:
- Standkartenvergabe Trödelmarkt am 16.09., Ende 17:00 Uhr – **abgelaufen**.
- Vortrag „Fairer Handel“ am 16.09., Beginn 18:00 Uhr – zum Prüfzeitpunkt **bereits begonnen/kein kommender Termin mehr**.

Das Frontend blendet Termine nach ihrem `data-event-end` aus; die QA prüft, dass kein abgelaufener Termin sichtbar als „kommend“ bleibt.

## Sport

- Fakt verifiziert: **JA**
- Primärquelle: FUSSBALL.DE
- Verifizierter Stand: SC 1919 Merzenich, Kreisliga A: Platz 5, 4 Spiele, 9 Punkte, 12:8 Tore; letzter bestätigter Endstand 13.09.2026: SC Merzenich 3:2 BC Oberzier; nächstes Spiel 18.09.2026, 19:30 Uhr auswärts gegen SC Jülich 1910/97/Hoengen.
- Letzte Prüfung: 16.09.2026, 19:11 Uhr.
- Bild gefunden: Vereinslogo im Bestand/auf Vereinsseiten.
- Bildrecht verifiziert: **NEIN**.
- Konsequenz: Logo wird aus der öffentlichen Ausgabe entfernt und durch das eigene gekennzeichnete Sport-Symbolbild ersetzt. Der bestätigte 3:2-Aufmacher bleibt inhaltlich der Sport-Hero; keine unnötige Änderung der redaktionellen Hierarchie.

## Wetter

- Quelle im Produktiv-Frontend: Open-Meteo über den eigenen Same-Origin-Proxy `/api/weather.json`.
- Letzte Prüfung: 16.09.2026, 19:11 Uhr.
- Status beim Refresh: Regen, rund 17 °C in Merzenich; wechselhafte Folgetage.
- Frontend-Regel: Header und Service-Modul verwenden denselben Abruf; bei Providerfehler nur letzten gültigen Cache verwenden, niemals erfundene 0-°C-Werte.

## Stellen

### Klein's Backstube – Filialleitung (m/w/d)
- Fakt verifiziert: **JA**
- Originalquelle: https://kleinsbackstube.softgarden.io/job/62261459/Filialleitung-m-w-d-in-Merzenich
- Originalanzeige datiert: 10.09.2026
- Letzte Prüfung: 16.09.2026, 19:11 Uhr
- Bild gefunden: Arbeitgeber-/Stellenbild vorhanden
- Bildrecht verifiziert: **NEIN**
- Konsequenz: kein Arbeitgeberfoto übernehmen.

### Thermopor Glas GmbH – Merzenich-Girbelsrath
Am 16.09.2026 auf der offiziellen Karriereseite aktiv verifiziert:
- CNC-Maschinenbediener (m/d/w)
- Betriebselektriker (m/d/w)
- Industriemechaniker (m/d/w)
- Kommissionierer Versand (m/d/w)

- Originalquelle: https://www.thermopor-glas.de/karriere/
- Letzte Prüfung: 16.09.2026, 19:11 Uhr
- Arbeitgeber nennt „zum nächstmöglichen Termin“; kein erfundenes ursprüngliches Veröffentlichungsdatum.
- Bild gefunden: **JA**
- Bildrecht verifiziert: **NEIN**
- Konsequenz: keine Arbeitgeberfotos übernehmen.

Explizit entfernt/nicht übernommen:
- Gemeinde-Stelle „Fachbereichsleitung Planen und Bauen“: Bewerbungsfrist 30.06.2026, daher nicht aktuell.

## Immobilien

Nur bei der letzten Prüfung noch erreichbare/nicht als reserviert markierte Originalangebote werden gezeigt. Keine fremden Objektfotos.

1. Erdgeschosswohnung mit Terrasse, Merzenich – 900 € Kaltmiete, 90 m², 3 Zimmer, bezugsfrei ab 12.09.2026.
   - Originalquelle: https://www.immobilienscout24.de/expose/170734472
   - Letzte Prüfung: 16.09.2026, 19:11 Uhr
2. Renovierte 3-Zimmer-Wohnung, Golzheim – 550 € Kaltmiete, 70 m², bezugsfrei ab 01.11.2026.
   - Originalquelle: https://www.immobilienscout24.de/expose/170617136
   - Letzte Prüfung: 16.09.2026, 19:11 Uhr
3. Moderne 3-Zimmer-Wohnung mit Balkon, Golzheim – 880 €, 80,28 m², Anzeige vom 11.09.2026.
   - Originalquelle: https://www.kleinanzeigen.de/s-anzeige/moderne-3-zimmer-wohnung-mit-balkon-neubau-in-golzheim/3509426368-203-1766
   - Letzte Prüfung: 16.09.2026, 19:11 Uhr

Ausgeschlossen:
- ein 600-€-/85-m²-Angebot in Golzheim, das bei der Prüfung als **deaktiviert/reserviert** gekennzeichnet war.

Bildstatus für alle Immobilien:
- Bild gefunden: **JA**
- Bildrecht verifiziert: **NEIN**
- Konsequenz: eigenes Bild `ph-immobilien.svg` mit dem sichtbaren Hinweis „SYMBOLBILD · KEIN FOTO DES ANGEBOTENEN OBJEKTS“.

## Lokale Orte und Einrichtungen

Aktuelle Quellen für konkrete Einrichtungen wurden erneut gesucht. Verifizierbar war unter anderem die offizielle Gemeindeseite zur Kita Bürgewald (Obere Straße 1b, 52399 Merzenich). Für Rathaus-/Kirchenmotive existieren zusätzlich bereits geprüfte Wikimedia-Commons-Kandidaten mit CC-BY-SA-Lizenzen.

Wichtig: diese Rechteprüfung bedeutet nicht, dass ein Bild automatisch eingebaut wird. In diesem Refresh wurden keine Ortsseiten nur zur Dekoration mit neuen Fremdbildern bestückt.

Letzte Prüfung: 16.09.2026, 19:11 Uhr.

## Serviceinformationen

- Rathauskontakt der offiziellen Gemeinde-/Heimat-Info-Präsenz: Valdersweg 1, 52399 Merzenich, Zentrale 02421/399-0.
- Bei Öffnungszeiten wurden in der Websuche auch ältere PDF-Stände gefunden. Da daraus kein belastbarer aktueller September-2026-Stand abgeleitet werden darf, werden Öffnungszeiten in diesem Pass **nicht auf Basis alter PDFs überschrieben**.
- Kein Servicewert wird erfunden oder aus einem alten Snapshot übernommen.

## Frontend-Integration

Vorgesehen/umgesetzt auf dem Refresh-Branch:
- zwei neue verifizierte Nachrichtenquellen in der kanonischen Content-Struktur;
- fünf aktuell geprüfte Stellen im Stellenmarkt;
- drei aktuelle externe Immobilienangebote mit eigenem Symbolbild statt Fremdfotos;
- Sport-Hero bleibt als bestätigte 3:2-Meldung erhalten, Bild wird auf eigenes Symbolbild umgestellt;
- Polizeimeldung vom 16.09. wird aktuelle Zweitmeldung;
- `latest.json` wird auf den Refresh-Stand 16.09.2026 aktualisiert und abgelaufene Termine werden entfernt;
- ungeklärte Referenzen auf das SC-Merzenich-Logo werden aus der öffentlichen Ausgabe und der kanonischen Content-Quelle entfernt;
- bestehende Designrichtung bleibt erhalten.

## QA-Gates vor Push auf main

Pflicht:
- JavaScript-/JSON-Syntax
- bestehende `qa/pruefung.mjs`
- vollständiger lokaler href/src-Linkcheck über alle statischen HTML-Seiten
- Coolify-Build
- Desktop 1440 px
- Tablet 834 px
- Mobile 390 px
- Hero / Zweitmeldung
- Jobs Desktop + Mobile
- Immobilien Desktop + Mobile
- Sportdaten + kein ungeklärtes Vereinslogo
- Termine: kein abgelaufener Termin als kommend
- neue Artikel erreichbar
- kein horizontaler Overflow
- Navigation / Menü / lokale Links

Erst wenn diese Gates grün sind, ist der Stand für Merge/PUSH auf `main` und den anschließenden Redeploy freigegeben.
