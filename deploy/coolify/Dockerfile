# Merzenich Aktuell - statische Auslieferung fuer Coolify.
#
# Der Repo-Stand unter chatgpt-site/ bleibt die statische Grundlage. Vor der
# Auslieferung wird er jedoch in einem reproduzierbaren Node-Build einmal gegen
# die tatsaechlich vorhandenen Artikelseiten abgeglichen. So koennen Suche,
# Themen, Ressorts, Ortsseiten und Archiv nicht mehr unterschiedliche Staende
# ausliefern.

FROM node:22-alpine AS editorial
WORKDIR /app
COPY chatgpt-site/ ./chatgpt-site/
COPY deploy/ ./deploy/

# P0 Editorial Consistency Gate:
# - fehlende Artikel in Suche nachtragen
# - Themenseiten aus denselben Artikeln synchronisieren
# - Ressorts, Orte und Archiv synchronisieren
# - Prototyp-/Pre-Launch-Texte bereinigen, /admin aus dem oeffentlichen Build entfernen
# - gemeinsame Assets/Service-Worker abschliessend versionieren
RUN node deploy/suche-index.mjs \
 && node deploy/thema-prerender.mjs \
 && node deploy/content-sync.mjs \
 && node deploy/kopf-theme-einbinden.mjs

FROM nginx:1.27-alpine

# Node fuer den Kommentar-/Abo-/Wetter-Dienst, ohne npm-Pakete.
RUN apk add --no-cache nodejs && rm -f /etc/nginx/conf.d/default.conf
COPY deploy/coolify/nginx.conf /etc/nginx/conf.d/merzenich.conf
COPY deploy/coolify/kommentare-location.conf /tmp/kommentare-location.conf
COPY deploy/coolify/40-resolver.sh /docker-entrypoint.d/40-resolver.sh
COPY deploy/coolify/50-kommentare.sh /docker-entrypoint.d/50-kommentare.sh
COPY deploy/coolify/kommentare/server.mjs /opt/kommentare/server.mjs
COPY --from=editorial /app/chatgpt-site/ /usr/share/nginx/html/

# Kommentar-, Abo- und Wetter-Weiterleitung (alle zum Node-Dienst) unmittelbar
# vor die schliessende server-Klammer einsetzen. weather-location.conf bleibt
# als Referenz im Repo, wird aber nicht mehr eingebaut. nginx.conf bleibt die
# aus _redirects/_headers erzeugte Datei.
RUN cat /tmp/kommentare-location.conf > /tmp/extra-locations.conf \
 && awk 'BEGIN { while ((getline line < "/tmp/extra-locations.conf") > 0) extra = extra line ORS } /^}$/ { printf "%s", extra } { print }' \
      /etc/nginx/conf.d/merzenich.conf > /tmp/merzenich.conf \
 && mv /tmp/merzenich.conf /etc/nginx/conf.d/merzenich.conf \
 && rm -f /tmp/kommentare-location.conf /tmp/extra-locations.conf \
 && chmod +x /docker-entrypoint.d/40-resolver.sh /docker-entrypoint.d/50-kommentare.sh \
 && node --version

# _redirects und _headers sind Cloudflare-Anweisungen und gehoeren nicht ins Web.
# /admin ist bereits im Editorial-Gate entfernt; der zweite rm ist absichtlich
# idempotent und verhindert eine spaetere versehentliche Oeffnung.
RUN rm -f /usr/share/nginx/html/_redirects /usr/share/nginx/html/_headers \
 && rm -rf /usr/share/nginx/html/admin \
 && nginx -t

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD wget -q -O /dev/null http://127.0.0.1/ && wget -q -O /dev/null http://127.0.0.1/api/kommentare/status || exit 1

EXPOSE 80
