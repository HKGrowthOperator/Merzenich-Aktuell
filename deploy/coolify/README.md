# Coolify: Vorschau von Merzenich Aktuell

Dient dazu, den Stand unter einer erreichbaren Adresse anzusehen, ohne auf
GitHub Pages oder Cloudflare zu warten.

## Was hier liegt

| Datei | Zweck |
|---|---|
| `Dockerfile` | nginx-Image, legt `chatgpt-site/` dahinter |
| `nginx.conf` | **erzeugt** - nicht von Hand bearbeiten |
| `erzeuge-nginx-conf.mjs` | erzeugt `nginx.conf` aus `_redirects` und `_headers` |

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
| Dockerfile Location | `deploy/coolify/Dockerfile` |
| Ports Exposes | `80` |
| Domain | die Vorschauadresse, die Coolify vorschlaegt, oder eine eigene |

Kein Build-Command, kein Start-Command, keine Umgebungsvariablen.

"Automatic Deployment" einschalten, dann baut jeder Push auf `main` die
Vorschau neu.

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
