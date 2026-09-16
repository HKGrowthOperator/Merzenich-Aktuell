# Merzenich Aktuell — Release Checklist

Stand: 16.09.2026

Ein Release gilt erst dann als fertig, wenn alle relevanten Gates bestanden sind. `LOCAL READY` oder `STAGING READY` ist **nicht** gleich `LIVE DEPLOYED`.

## 1. Repository

- [ ] Änderungen liegen ausschließlich in `HKGrowthOperator/Merzenich-Aktuell`
- [ ] keine nicht synchronisierten Parallelprojekte
- [ ] `main` enthält den vorgesehenen Release-Stand
- [ ] GitHub Actions QA ist grün

## 2. Frontend

- [ ] weißer Header
- [ ] Sticky Header direkt `top: 0`
- [ ] kein Floating-Gap
- [ ] 1px dunkle Bottom-Border
- [ ] Logo kompakt
- [ ] Suche mittig
- [ ] Wetter + Temperatur + Uhrzeit + Datum rechts
- [ ] nur eine Ressortzeile + `Mehr`
- [ ] Desktop ohne horizontales Scrollen
- [ ] Mobile ohne horizontales Scrollen

## 3. Homepage

- [ ] Desktop-Hierarchie: Service links / News dominant in der Mitte / Werbung rechts
- [ ] Mobile: News vor Service und Werbung
- [ ] Hero ist aktuell oder bewusst redaktionell fixiert
- [ ] keine automatische Hero-Story älter als 7 Tage
- [ ] Ortskennung vorhanden
- [ ] Bilder funktionieren und sind nicht verzerrt
- [ ] `Mehr lesen` sichtbar und konsistent
- [ ] Kommentaranzahl sichtbar, sofern Kommentarlogik aktiv

## 4. Content Refresh

Unmittelbar vor Veröffentlichung erneut prüfen:

- [ ] Gemeinde Merzenich / offizielle Kanäle
- [ ] Kreis Düren
- [ ] Polizei Düren / Presseportal
- [ ] Feuerwehr
- [ ] Vereine
- [ ] FUSSBALL.DE
- [ ] Veranstaltungen
- [ ] Ortsteile
- [ ] relevante lokale Einrichtungen / Unternehmen
- [ ] Bildrechte und Credits

Workflow für neue Inhalte:

`Fund → Quelle prüfen → Datum → Ort → Bildrechte → redaktionell schreiben → Human Review → Publish`

## 5. Service

- [ ] Veranstaltungen standardmäßig offen
- [ ] Wetter geschlossen, aber funktional
- [ ] Immobilien geschlossen
- [ ] Stellen geschlossen
- [ ] Traueranzeigen geschlossen
- [ ] Familienanzeigen geschlossen
- [ ] abgelaufene Events nicht unter kommenden Terminen
- [ ] keine Fake-Inhalte in leeren Märkten

## 6. Wetter

- [ ] Header und Service nutzen dieselbe Datenquelle
- [ ] Temperatur plausibel
- [ ] Wetterzustand/Icon vorhanden
- [ ] Heute / Morgen / Übermorgen vorhanden
- [ ] Regenwahrscheinlichkeit vorhanden
- [ ] Fehlerfall zeigt letzten gültigen Cache oder blendet Modul aus
- [ ] niemals `0 °C`, `undefined`, `NaN` als technischer Fallback

## 7. Sport

- [ ] letzter bestätigter Stand aktuell
- [ ] Pending-Spiel korrekt gekennzeichnet, falls Ergebnis offen
- [ ] nächstes Spiel aktuell
- [ ] Tabelle aktuell
- [ ] ein gemeinsamer sichtbarer Datenstand
- [ ] keine erfundenen Ergebnisse

## 8. Werbung

- [ ] aktive Slots klar als `ANZEIGE` markiert
- [ ] inaktive Slots erzeugen keinen Leerraum
- [ ] keine Bannerwand
- [ ] WordPress: globaler Schalter + Slot-Schalter + Laufzeit + Ziel vorhanden

## 9. Artikel

- [ ] Generalvorlage konsistent
- [ ] Ort sichtbar
- [ ] Autor / Redaktion
- [ ] Veröffentlichung / Aktualisierung
- [ ] Hero-Bild + Credit
- [ ] Quelle & Transparenz
- [ ] Kommentare, wenn aktiviert
- [ ] Related Content

## 10. EU AI Act / Transparenz

- [ ] KI-Nutzung im Backend dokumentierbar
- [ ] Human Review dokumentierbar
- [ ] KI-generierte / manipulierte Medien kennzeichnungsfähig
- [ ] keine KI-Ereignisbilder, die reale Ereignisse vortäuschen
- [ ] redaktionelle Verantwortung klar

## 11. WordPress Delivery

- [ ] `wordpress-delivery/merzenich-aktuell-theme.zip` vorhanden
- [ ] `wordpress-delivery/merzenich-aktuell-core.zip` vorhanden
- [ ] `wordpress-delivery/merzenich-aktuell-import.xml` vorhanden
- [ ] ZIPs entpackbar
- [ ] PHP-Syntax grün
- [ ] Theme installierbar
- [ ] Plugin installierbar
- [ ] Importdatei syntaktisch gültig
- [ ] entpackter Source im Repo, sobald Originalsource verfügbar ist

## 12. Status

Nur diese Begriffe verwenden:

- **LOCAL READY** — Source vorhanden, nicht öffentlich
- **STAGING READY** — auf echter Stagingumgebung getestet
- **LIVE READY** — Deployment möglich und Gates bestanden
- **LIVE DEPLOYED** — öffentlich ausgeliefert und danach erneut geprüft

Nur `LIVE DEPLOYED` bedeutet fertig.
