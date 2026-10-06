# Kommentare: Vorab-Freigabe und Freigabe-Mail

## Live-Seite (WordPress, merzenich-aktuell.de)

Seit dem 5. Oktober 2026 läuft die Seite nur noch als WordPress; Kommentare moderiert das Core-Plugin (`wordpress/plugin/merzenich-aktuell-core/includes/comments.php`).

1. Jeder Kommentar von Besuchern wartet auf Freigabe, auch wenn die WordPress-Einstellung etwas anderes sagt. Die IP-Adresse wird nicht gespeichert.
2. Das Formular unter jeder Meldung verlangt Name, E-Mail und Text und verweist auf die Kommentarrichtlinien (`/kommentarregeln/`) und die Datenschutzerklärung. Die E-Mail-Adresse wird nicht veröffentlicht.
3. **Kommentare → Sammelfreigabe:** alle wartenden Kommentare, jeder vorausgewählt. Problematische abwählen, den Rest in einem Schritt freigeben. Einzelne Kommentare lassen sich auch in der normalen Kommentarliste freigeben oder ablehnen.
4. Nach der Freigabe bekommt der Verfasser eine E-Mail mit Link zur Meldung, sofern er eine gültige Adresse angegeben hat. Voraussetzung ist funktionierender Mailversand (SMTP, siehe `README-INSTALLATION.md`).
5. `/diskussion/` zeigt die jüngsten freigegebenen Kommentare aller Meldungen.

Kommentare lassen sich je Beitrag im Diskussionsfeld des Editors abschalten.

## Archiv: Kommentardienst der früheren Vorschauseite

Der folgende Abschnitt beschreibt den Kommentardienst der statischen Vorschauseite auf Coolify. Die Vorschauseite ist seit dem 5. Oktober 2026 abgeschaltet; Code (`deploy/coolify/`) und Test (`qa/dienst/kommentare.test.mjs`) liegen noch im Repository und laufen in der CI mit. Für die Live-Seite gilt der Abschnitt oben.

Gilt für die statische Auslieferung (Coolify, `chatgpt-site/`). Der
Kommentar-Dienst ist `deploy/coolify/kommentare/server.mjs` und läuft im selben
Container wie nginx (Grundeinrichtung: `deploy/coolify/README.md`, Abschnitt
„Kommentare und Diskussion“). Die WordPress-Ausgabe hat ihre eigene Moderation
im Core-Plugin und ist davon nicht betroffen.

### Ablauf

1. Leserinnen und Leser schreiben einen Kommentar unter einem Artikel oder in
   der Diskussion. Er wird mit Status `wartend` gespeichert und ist **nicht**
   öffentlich. Das Formular zeigt: „Danke. Ihr Kommentar erscheint nach Prüfung
   durch die Redaktion.“ Wurde eine E-Mail-Adresse angegeben, zusätzlich: „Wir
   benachrichtigen Sie per E-Mail, sobald er freigegeben ist.“
2. Ein neues Diskussionsthema erscheint erst, wenn sein Eröffnungsbeitrag
   freigegeben ist. Auf ein noch nicht freigegebenes Thema kann niemand antworten.
3. Die Redaktion öffnet die Freigabeseite, gibt einmal das Admin-Token ein und
   sieht alle wartenden Kommentare, **jeder ist vorausgewählt**. Was abgelehnt
   werden soll, wird abgewählt. Der Knopf „Ausgewählte freigeben, abgewählte
   ablehnen“ fragt mit den Anzahlen nach und speichert dann alles auf einmal.
   Es werden nur die Kommentare bearbeitet, die gerade auf der Seite stehen;
   was danach eingeht, bleibt wartend.
4. Freigegebene Kommentare sind sofort öffentlich. Wer eine E-Mail-Adresse
   angegeben hat, bekommt eine Mail mit dem Betreff „Ihr Kommentar auf
   Merzenich Aktuell ist freigegeben“ und dem Link zum Artikel bzw. Thema,
   höchstens einmal je Kommentar. Abgelehnte Kommentare bleiben gespeichert,
   werden nie angezeigt und lösen keine Mail aus.

Kommentare, die vor dieser Umstellung gespeichert wurden (ohne Status oder mit
Status `sichtbar`), gelten als freigegeben und bleiben sichtbar.

### Freigabeseite

<https://merzenichaktuell.hk-growthoperator.de/api/kommentare/moderation>

- wird vom Dienst selbst ausgeliefert, `noindex`, kein Inline-Skript (Skript:
  `/api/kommentare/moderation.js`)
- das Token bleibt nur im `sessionStorage` des Browserfensters und ist nach dem
  Schließen des Fensters weg; „Abmelden“ löscht es sofort
- ist der Mailversand nicht eingerichtet, steht das als Hinweis auf der Seite
- nach zehn falschen Token-Eingaben von derselben Adresse ist der Zugang für
  15 Minuten gesperrt

### Umgebungsvariablen (in Coolify setzen, danach Redeploy)

| Variable | Pflicht | Bedeutung |
|---|---|---|
| `KOMMENTARE_ADMIN_TOKEN` | ja | Langes Zufallswort für die Freigabe. Ohne Token antworten alle Admin-Endpunkte mit 503, und nichts kann freigegeben werden. |
| `SMTP_HOST` | für Mails | Mailserver des Postfachs, aus dem verschickt wird. |
| `SMTP_PORT` | nein | Standard `587` (STARTTLS). `465` für TLS von Anfang an. |
| `SMTP_SECURE` | nein | `true` = TLS von Anfang an, `false` = STARTTLS. Ohne Angabe: `true` bei Port 465, sonst `false`. |
| `SMTP_USER`, `SMTP_PASS` | meist | Zugangsdaten des Postfachs. Werden nie unverschlüsselt gesendet. |
| `SMTP_FROM` | für Mails | Absender, z. B. `Merzenich Aktuell <adresse@domain>`. Muss zum Postfach passen. |
| `REDAKTION_MAIL` | nein | Adresse der Redaktion. Wenn gesetzt (und SMTP eingerichtet), kommt ein Hinweis, sobald etwas auf Freigabe wartet, gebündelt höchstens eine Mail je 10 Minuten, mit Link zur Freigabeseite. |
| `KOMMENTARE_SEITE_URL` | nein | Basisadresse für Links in Mails. Standard `https://merzenichaktuell.hk-growthoperator.de`. |

Ohne `SMTP_HOST` oder `SMTP_FROM` funktioniert die Freigabe trotzdem; es wird
keine Mail verschickt, und das Log des Containers sagt „SMTP nicht
eingerichtet, keine Mail verschickt“. Fehler beim Versand (falsches Passwort,
Server nicht erreichbar) stehen ebenfalls im Log und stören den Dienst nicht.

### Schnittstelle (für curl)

Admin-Zugriff mit `Authorization: Bearer <TOKEN>` (die bisherige Kopfzeile
`X-Admin-Token: <TOKEN>` funktioniert weiter).

```bash
H='Authorization: Bearer <TOKEN>'; B=https://merzenichaktuell.hk-growthoperator.de/api/kommentare
curl -s "$B/admin/liste?status=wartend" -H "$H"        # wartende Kommentare mit Artikel-/Themenbezug
curl -s -X POST $B/admin/freigabe -H "$H" -H 'Content-Type: application/json' \
  -d '{"freigeben":["<ID>"],"ablehnen":["<ID>"]}'       # Sammelfreigabe
```

Die Admin-Liste enthält Name, Text, Datum, Artikel bzw. Thema und ob eine
E-Mail-Adresse hinterlegt ist (`hatEmail`), aber nie die Adresse selbst.

### Datenschutz (Stand 28.09.2026)

Die Datenschutzerklärung (`/datenschutz/#kommentare`) nennt jetzt beide Zwecke der E-Mail-Adresse: Rückfragen der Redaktion und die einmalige Nachricht nach der Freigabe. Außerdem steht dort, dass diese Nachricht über den SMTP-Server des Redaktionspostfachs geht und der E-Mail-Anbieter dafür Adresse und Nachrichtentext erhält.

**Offen für den Betreiber (juristische Endprüfung):** Den tatsächlich genutzten E-Mail-Anbieter mit Namen und Sitz ergänzen, sobald `SMTP_HOST` in Coolify gesetzt ist, und die gesamte Datenschutzerklärung rechtlich prüfen lassen.

### Test

`node qa/dienst/kommentare.test.mjs` startet den Dienst mit einem temporären
Datenordner und einem Schein-Mailserver und prüft den ganzen Ablauf. Läuft in
der CI (`qa.yml`, Schritt „Kommentardienst: Vorab-Freigabe und Freigabe-Mail“).
