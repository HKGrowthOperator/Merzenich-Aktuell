# Drei Betriebe als Entwurf: Bäckerei Wirtz, Hasberg, Porschen Media

Stand 16.10.2026 · Plugin 1.28.2 · Daten: `wordpress/plugin/merzenich-aktuell-core/data/firmen-entwuerfe.json`

Auftrag des Betreibers vom 16.10.2026, Entscheidung „Entwurf bis Zusage“. Je Betrieb liegen ein Unternehmensprofil und ein Werbebeitrag als **Entwurf** in WordPress. Öffentlich wird nichts, bevor der Betrieb zugestimmt hat.

## Was angelegt wird

**WordPress → Merzenich Aktuell → Firmen-Entwürfe → „Fehlende Entwürfe anlegen“**

| Betrieb | Profil (Entwurf) | Werbebeitrag (Entwurf) |
|---|---|---|
| Bäckerei Wirtz | `/betriebe/baeckerei-wirtz-merzenich/` | `baeckerei-wirtz-brot-torten-merzenich` |
| Hasberg Fenster & Rollladentechnik | `/betriebe/hasberg-fenster-rollladentechnik-merzenich/` | `hasberg-fenster-rolllaeden-sonnenschutz-merzenich` |
| Porschen Media | `/betriebe/porschen-media-girbelsrath/` | `porschen-media-druck-werbung-girbelsrath` |

**Profil**
- Stammdaten, wie sie am 16.10.2026 auf der Website des Betriebs standen: Branche, Adresse, Telefon, E-Mail (falls genannt), Website, Öffnungs- bzw. Bürozeiten, Ortsteil.
- Quelle und Stand.
- Kurztext.
- Knopf zur Website.

**Werbebeitrag**
- Text wie vom Betreiber vorgegeben, Rubrik Wirtschaft.
- Gekennzeichnet als gesponsert und als kostenfreier Testeintrag, verknüpft mit dem Profil.
- Nicht für die Startseite freigegeben.

**Was nicht angelegt wird**
- Keine Einwilligung: Der Haken bleibt leer, bis der Betrieb zugestimmt hat.
- Keine Logos, keine Fotos.
- Keine Bannerplätze und kein Platz auf der Startseite. Die Bannertexte (`anzeige` in der Datei) sind nur vorbereitet.

## Freigeben, wenn ein Betrieb zusagt

1. **Profil:** Haken „Einwilligung“ setzen, dann Veröffentlichen. Ohne Haken bleibt es Entwurf, das sichert die Profil-Sperre. Auch der Werbebeitrag lässt sich erst veröffentlichen, wenn am Profil die Einwilligung steht, sonst bleibt er Entwurf (auch bei der Sammelfreigabe).
2. **Werbebeitrag:** Text mit dem Betrieb abstimmen. Dann im Kasten „Kennzeichnung“ prüfen, dass „Gesponsert“ und „Kostenfreier Testeintrag“ gesetzt sind. Danach die Prüfhäkchen setzen und veröffentlichen, wie bei jeder Meldung.
3. **Testzeitraum:**
   - Er beginnt mit dieser ersten Veröffentlichung, nicht mit dem Anlegen.
   - Er dauert 30 Tage, kostet 0 € und verlängert sich nicht.
   - Am Ende archiviert ein täglicher Lauf den Beitrag; er ist dann nicht mehr öffentlich. Das Profil bleibt.
   - Das Enddatum steht im Beitrag („Kostenfreie Präsentation im Testzeitraum bis …“) und im Verlauf.
4. **Kasten „Unternehmen aus der Gemeinde“ auf `/unternehmen/`:**
   - Sobald ein Profil veröffentlicht ist, steht der Betrieb dort vor den Beispielfirmen.
   - Ab drei echten Betrieben mit veröffentlichtem Beitrag verschwinden die Beispielfirmen.

**Wording auf der Seite**
- Die Betriebe werden nie als Partner, Kunden oder Sponsoren bezeichnet.
- Die Kennzeichnung lautet „Anzeige · kostenfreier Testeintrag“.

## Logos: Quellen und Rechte

**Alle Rechte sind offen.** Ohne schriftliche Freigabe des Betriebs zeigt die Seite das Kürzel (BW, HF, PM). So wird ein Logo eingesetzt, sobald die Freigabe vorliegt:
1. Logo im Profil als Beitragsbild hochladen.
2. „Logo-Rechte“ auf „geklärt“ stellen.

| Betrieb | Fundstelle (16.10.2026) | Format | Rechte |
|---|---|---|---|
| Bäckerei Wirtz | https://www.baeckerei-wirtz.de/bilder/logo-frei0_500x500.png | PNG, 500 × 500 | offen |
| Hasberg | https://www.hasberg.de/uploads/6xZoQu63/01-hasberg-weber-logo__msi___jpg.jpg | JPEG, 543 × 205, Logo laut Strukturdaten der Website | offen |
| Porschen Media | https://porschen.media/_astro/logo.Bq7BYIkx_1Gh7MW.svg | SVG aus der Kopfzeile der Website | offen |

**Am besten** bittet man jeden Betrieb um eine eigene Logodatei, idealerweise SVG oder PNG mit transparentem Hintergrund, zusammen mit der Zusage.

## Anfragetext an die Betriebe

> Betreff: Ihr Betrieb auf Merzenich Aktuell, kostenfreier Testeintrag
>
> Guten Tag,
>
> Merzenich Aktuell ist die Lokalzeitung im Netz für die Gemeinde Merzenich mit Golzheim, Girbelsrath, Morschenich und Bürgewald. Wir möchten Ihren Betrieb in der Rubrik „Unternehmen“ vorstellen: mit einem kurzen Unternehmensprofil (Adresse, Telefon, Öffnungszeiten, Website) und einem Beitrag über Ihr Angebot.
>
> Wir haben dafür einen Entwurf vorbereitet. Die Angaben stammen von Ihrer Website (Stand 16.10.2026). Den Text senden wir Ihnen gern zur Durchsicht.
>
> - Der Eintrag ist ein kostenfreier Testeintrag: 0 Euro, 30 Tage ab Veröffentlichung, er endet von selbst. Es gibt keine Verlängerung und keine Rechnung.
> - Der Beitrag ist als Anzeige gekennzeichnet („Anzeige · kostenfreier Testeintrag“) und klar von den Nachrichten der Redaktion getrennt.
> - Veröffentlicht wird nur mit Ihrer Zustimmung. Ihr Logo zeigen wir nur, wenn Sie es uns dafür freigeben.
>
> Wenn Sie einverstanden sind, antworten Sie bitte kurz auf diese Nachricht. Senden Sie gern Ihr Logo mit, wenn es erscheinen soll. Änderungswünsche am Text nehmen wir gern auf.
>
> Mit freundlichen Grüßen
> Redaktion Merzenich Aktuell

## Technik

- `includes/firmen-entwuerfe.php`: Menüseite, Anlegen (idempotent nach Adresse, auch Papierkorb), Übersicht mit Status, Einwilligung und Logo-Rechten.
- `includes/testeintrag.php`: Kennzeichnung (`ma_gesponsert_label`, `ma_gesponsert_hinweis`), Start und Ende (`ma_test_start`, `ma_test_ende`), täglicher Lauf `ma_testeintraege_pruefen`.
- Theme: `ma21_gemeinde_profile()` und `ma21_musterprofile_html()` in `inc/markt.php`; das Logo nur bei `ma_business_logo_rechte` ≠ „offen“ (`inc/unternehmen.php`).
- Tests: `qa/wordpress/firmen-entwuerfe-test.php`, `qa/wordpress/testeintrag-test.php`.
