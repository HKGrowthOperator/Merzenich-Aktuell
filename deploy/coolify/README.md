# Coolify: Vorschau von Merzenich Aktuell

Dient dazu, den Stand unter einer erreichbaren Adresse anzusehen, ohne auf
GitHub Pages oder Cloudflare zu warten.

## Was hier liegt

| Datei | Zweck |
|---|---|
| `Dockerfile` | nginx-Image, legt `chatgpt-site/` dahinter |
| `nginx.conf` | **erzeugt** - nicht von Hand bearbeiten |
| `erzeuge-nginx-conf.mjs` | erzeugt `nginx.conf` aus `_redirects` und `_headers` |
| `Dockerfile.dockerignore` | haelt den Build-Kontext klein (BuildKit liest ihn neben dem Dockerfile) |

`chatgpt-site/` ist der ausgelieferte Stand. Hier wird nichts gebaut, nur
ausgeliefert. Das Image enthaelt keinerlei Build-Schritt und keine
Abhaengigkeiten.

## Einstellungen in Coolify

Neue Resource -> Application -> Public Repository (oder GitHub App):

| Feld | Wert |
|---|---|
| Repository | `https://github.com/HKGrowthOperator/merzenich-aktuell` |
| Branch | `main` |
| Build Pack | `Dockerfile` |
| Base Directory | `/` |
| Dockerfile Location | `/deploy/coolify/Dockerfile` |
| Ports Exposes | `80` |
| Domain | die Vorschauadresse, die Coolify vorschlaegt, oder eine eigene |

Kein Build-Command, kein Start-Command, keine Umgebungsvariablen.

**Beide Pfadfelder muessen stimmen.** Steht `Dockerfile Location` noch auf dem
Standardwert `/Dockerfile`, bricht der Build mit

```
#1 transferring dockerfile: 2B done
ERROR: failed to solve: failed to read dockerfile: open Dockerfile: no such file or directory
```

ab - Coolify sucht dann im Wurzelverzeichnis, und dort liegt bewusst kein
Dockerfile. `Base Directory` muss auf `/` stehen bleiben: der Dockerfile kopiert
`chatgpt-site/`, und der Build-Kontext muss diesen Ordner enthalten.

"Automatic Deployment" einschalten, dann baut jeder Push auf `main` die
Vorschau neu.

## Wenn statt der Seite "Bad Gateway" kommt

Das kommt von Traefik, nicht von nginx: die Route existiert, dahinter antwortet
nichts. Der Container ist dann meist in Ordnung - der Healthcheck in diesem
Dockerfile ruft `http://127.0.0.1:80/` von innen ab, und ein gruenes "healthy"
im Deploy-Log heisst, dass nginx laeuft und die Startseite ausliefert.

Bleibt also der Weg von Traefik zum Container:

1. **Ports Exposes** muss `80` sein. Ist das Feld leer, raet Coolify `3000` -
   dort lauscht nichts, und genau das ergibt 502. **Port Mappings** bleibt leer.
   Kontrolle unten bei "Labels": dort muss
   `loadbalancer.server.port=80` stehen.
2. Danach **Redeploy**, nicht nur Restart. Redeploy schreibt die Labels neu;
   ein Restart startet den alten Container mit den alten Labels.
3. Hilft das nicht: Servers -> localhost -> Proxy -> Restart. Das ist Traefik
   selbst, nicht die Anwendung.
4. Browser hart neu laden - 502-Antworten werden gern zwischengespeichert.

Gegentest im **Terminal**-Tab der Anwendung:

```bash
wget -qO- http://localhost/ | head -5
```

Kommt `<!doctype html>`, ist der Container in Ordnung und der Fehler liegt
zwischen Traefik und Container.

## Nach dem Anlegen pruefen

```bash
curl -I https://<adresse>/                      # 200
curl -I https://<adresse>/einsatz-94-pkw-brand/ # 301 auf /blaulicht/...
curl -I https://<adresse>/feed.xml              # application/rss+xml
curl -I https://<adresse>/gibtesnicht/          # 404 mit gestalteter Seite
```

## Wenn sich die Weiterleitungen aendern

`_redirects` oder `_headers` in `chatgpt-site/` sind die Quelle. Nach einer
Aenderung:

```bash
node deploy/coolify/erzeuge-nginx-conf.mjs
```

und `nginx.conf` mit committen.

## Hinweise

- **HSTS**: `Strict-Transport-Security: max-age=31536000` kommt aus `_headers`
  und gilt damit auch fuer die Vorschauadresse. Wer die Vorschau auf einer
  Subdomain betreibt, die er spaeter ohne TLS nutzen will, sollte das wissen.
- **`/admin/`** ist die Decap-CMS-Oberflaeche. Sie braucht Netlify Identity und
  funktioniert in der Coolify-Vorschau nicht. Die Seite laedt, die Anmeldung
  nicht. Das ist erwartet und kein Fehler dieser Auslieferung.
- **Formulare** (Meldung senden, Termin melden, Kontakt) sind Netlify Forms.
  Sie nehmen in der Vorschau nichts entgegen.

## Was hier geprueft wurde

Gegen nginx 1.24 lokal, mit demselben `nginx.conf`:

- `nginx -t` fehlerfrei
- alle 212 Seiten liefern 200
- Weiterleitungen: `/index.html`, `/einsatz-94-pkw-brand/`,
  `/artikel-faire-woche.html`, `/stadtleben/*`, `/newsletter/*`, `/wa`, `/rss`
- Inhaltstypen: RSS, Atom, JSON Feed, iCal, Service Worker, CSS
- 404 liefert die gestaltete Seite, nicht die nginx-Standardseite
- Sicherheitskopfzeilen auf jeder Antwort, Content-Type nicht doppelt
- Browser: Desktop, Tablet, Telefon - kein waagerechter Ueberlauf,
  null Konsolenfehler

Das Docker-Image selbst wurde **nicht** gebaut - in dieser Umgebung laeuft kein
Docker-Daemon. Geprueft ist die nginx-Konfiguration, nicht der Image-Bau.


## Kommentare und Diskussion

Im Container laeuft neben nginx ein kleiner Node-Dienst
(`deploy/coolify/kommentare/server.mjs`), erreichbar unter `/api/kommentare/`.
Er speichert Kommentare als JSON-Datei. Zwei Dinge sind in Coolify zu setzen:

1. **Persistent Storage**: Mount-Pfad `/data` (Volume). Ohne Volume laeuft der
   Dienst zwar, meldet aber unter `/api/kommentare/status` `"persistent": false`,
   und alle Kommentare sind nach dem naechsten Deploy weg.
2. **Umgebungsvariable** `KOMMENTARE_ADMIN_TOKEN`: ein langes Zufallswort. Damit
   kann die Redaktion Kommentare ausblenden oder loeschen und Themen schliessen.
   Optional `KOMMENTARE_SALZ` (fester Wert, damit das Stundenlimit einen
   Neustart ueberlebt).

Danach **Redeploy**.

### Moderation (per curl, mit dem Token)

```bash
H='X-Admin-Token: <TOKEN>'; B=https://merzenichaktuell.hk-growthoperator.de/api/kommentare
curl -s $B/admin/liste -H "$H" | python3 -m json.tool          # alles, auch Verborgenes
curl -s -X POST $B/admin/status -H "$H" -H 'Content-Type: application/json' \
  -d '{"id":"<KOMMENTAR-ID>","status":"verborgen"}'              # sichtbar | verborgen | geloescht
curl -s -X POST $B/admin/thema-status -H "$H" -H 'Content-Type: application/json' \
  -d '{"id":"<THEMA-ID>","status":"geschlossen"}'                # offen | geschlossen
```

Regeln ohne Konto: Honeypot, Laengen, hoechstens zwei Links, Richtlinien
bestaetigen, sechs Beitraege pro Stunde je Absender (Tages-Hash der IP, keine
Klartext-IP), nach drei Meldungen wird ein Kommentar automatisch ausgeblendet.
E-Mail ist freiwillig und wird nie ausgegeben, auch nicht im Admin-JSON.

## Werbefrei-Abo und Wetter (derselbe Dienst)

Der Node-Dienst bedient auch `/api/abo/` (Werbefrei-Abo, 2,50 Euro im Monat) und
`/api/weather.json` (Open-Meteo-Proxy mit 10-Minuten-Cache; nginx leitet dorthin
weiter, ein eigener DNS-Resolver in nginx ist nicht mehr noetig).

Umgebungsvariablen in Coolify:

| Variable | Pflicht | Bedeutung |
|---|---|---|
| `ABO_GEHEIMNIS` | ja, sobald Abos ausgegeben werden | Langes Zufallswort; signiert die Werbefrei-Nachweise im Browser. Aenderung macht alle Nachweise ungueltig. |
| `ABO_ZAHLUNGSLINK` | fuer Zahlung | Stripe Payment Link. Erfolgs-URL im Link auf `https://merzenichaktuell.hk-growthoperator.de/werbefrei/?session_id={CHECKOUT_SESSION_ID}` setzen. Ohne Variable zeigt `/werbefrei/` "Zahlungslink wird gerade eingerichtet". |
| `STRIPE_SECRET_KEY` | fuer Zahlung | Geheimer Stripe-Schluessel (`sk_live_...`), mit dem der Dienst die Checkout-Session nach der Rueckkehr prueft. |
| `ABO_CODES` | optional | Kommagetrennte Einloesecodes der Redaktion (z. B. fuer Testgeraete oder Unterstuetzer ohne Karte). |
| `ABO_PREIS` | optional | Anzeigetext, Standard `2,50 € im Monat`. |

Werbung ist derzeit global aus (`WERBUNG_AN = false` in `deploy/kopf-theme-einbinden.mjs`,
ergibt `<html data-werbung="aus">`). Wird sie wieder eingeschaltet, blendet ein
gueltiger Werbefrei-Nachweis (`html[data-werbefrei="ja"]`) die Anzeigenflaechen aus.

## Bildproxy fuer Symbolbilder

Symbolbilder von Wikimedia Commons laufen ueber `/api/bild?u=<Adresse>` (derselbe
Node-Dienst). Der Dienst laedt das Bild einmal, legt es unter `/data/bilder/` ab und
liefert es mit langen Cache-Headern aus. Leser sprechen nie mit Wikimedia; deshalb
gibt es dafuer keinen Einwilligungs-Punkt mehr. Erlaubt sind nur
`commons.wikimedia.org` und `upload.wikimedia.org` (Umgebungsvariable `BILD_HOSTS`
zum Erweitern). Ohne Persistent Storage `/data` wird nach jedem Deploy neu geladen,
was funktioniert, aber langsamer ist.

## Redaktionsformulare

Die sieben Formulare (Korrektur, Meldung senden, Termin melden, Verein eintragen,
Betrieb eintragen, Kontakt, Werbung) schicken per `POST /api/formular` an den Dienst.
Vorher zeigten sie auf `data-netlify`, was auf nginx nur einen Fehler 405 ergab.

- Ablage: `/data/formulare.jsonl` (eine JSON-Zeile je Eingang) und Bilder unter
  `/data/formulare/`. Ohne Persistent Storage gehen Eingaenge beim naechsten Deploy verloren.
- Optional `FORMULAR_WEBHOOK`: URL, an die jeder Eingang als JSON gePOSTet wird
  (z. B. ein Make-/Zapier-Hook, der eine E-Mail an die Redaktion ausloest).
- Abrufen: `curl -s $B/formular/admin/liste -H 'X-Admin-Token: <TOKEN>'`
  und `curl -s "$B/formular/admin/datei?datei=<name>" -H 'X-Admin-Token: <TOKEN>' -o bild.jpg`.
- Schutz: Honeypot, Pflichtfelder serverseitig, 5 Einsendungen pro Stunde je Absender,
  Doppelversand innerhalb von 10 Minuten wird verworfen, nur Bilder als Anhang.
