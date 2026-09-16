# Merzenich Aktuell – Recovery-Installation

Lieferstand: 14. September 2026 · Theme 20.3.0-recovery · Core 1.1.0-recovery

## Zweck

Dieser Recovery-Pass verändert nicht die redaktionelle Grundrichtung. Er vervollständigt die dokumentierte bestehende WordPress-Basis: weißer bündiger Header, eine Ressortzeile, gemeinsame Wetterlogik, Eventfilter, native Kommentare, Werbeschalter, Märkte, Sport-Fallback und redaktionelle Freigabefelder.

## Installation

1. Vollständiges Backup von Datenbank und `wp-content` erstellen.
2. Bestehenden Stand auf Staging sichern. **Nicht direkt auf Produktion testen.**
3. `merzenich-aktuell-core.zip` über **Plugins → Installieren → Plugin hochladen** installieren/ersetzen und aktivieren.
4. `merzenich-aktuell-theme.zip` über **Design → Themes → Theme hochladen** installieren/ersetzen und aktivieren.
5. Unter **Einstellungen → Permalinks** einmal speichern.
6. Unter **Merzenich Aktuell → Wetter** prüfen: aktiviert, Merzenich-Koordinaten, Cache 10–30 Minuten. Der eingebaute Adapter nutzt Open-Meteo serverseitig; Nutzungsbedingungen/Tarif für den konkreten produktiven Einsatz prüfen.
7. Unter **Merzenich Aktuell → Werbung** Werbung global und je Slot nur bei echten Kampagnen aktivieren. Ohne Kampagne bleibt die Fläche unsichtbar.
8. Unter **Merzenich Aktuell → Sport** Ergebnis, nächstes Spiel, Tabelle und einen gemeinsamen Datenstand zusammen pflegen. Unbestätigte Partien gehören in `pending_match`.
9. `merzenich-aktuell-import.xml` optional als **inkrementellen Recovery-Import** verwenden. News werden als Entwürfe importiert; Termine als Event-Datensätze. Vor Veröffentlichung jede Quelle und jedes Bild prüfen.
10. Unter **Einstellungen → Diskussion** Moderation prüfen. Native Kommentare sind im Theme integriert.

## Release-Regeln

- Keine automatisch ausgewählte Hero-Story älter als sieben Tage; Ausnahme nur über manuelles Fixieren.
- Neue Quellenfunde bleiben Entwurf, solange Quelle, Datum, Ort, Bildrechte (falls Bild vorhanden) und Human Review nicht bestätigt sind.
- Abgelaufene Events werden aus der kommenden Liste gefiltert.
- Wetter verwendet Cache + letzten gültigen Datenstand; existiert gar kein valider Stand, blendet die UI die dynamischen Werte aus statt 0 °C/NaN zu zeigen.
- Keine Fake-Immobilien, Fake-Jobs, Fake-Trauer- oder Familienanzeigen.
- Feed-Anzeige frühestens nach vier redaktionellen Beiträgen.

## Vor Produktivgang zwingend

Browser-/Responsive-Test auf dem Zielhosting, SMTP, Cron, HTTPS, Backups, Rechte-/Datenschutzprüfung, Betreiberangaben und echte Kommentar-/Formulartests. Der ChatGPT-Vorschau-Deploy ist von diesen WordPress-Dateien getrennt.
