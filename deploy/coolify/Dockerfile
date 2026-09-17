# Merzenich Aktuell - statische Auslieferung fuer Coolify.
#
# Baut kein Projekt: chatgpt-site/ IST der ausgelieferte Stand. Dieses Image
# legt ihn nur hinter einen nginx, der dieselben Weiterleitungen und Kopfzeilen
# spricht wie bisher Cloudflare Pages (siehe nginx.conf, erzeugt aus
# _redirects und _headers).
#
# In Coolify: Build Pack = Dockerfile, Dockerfile Location = deploy/coolify/Dockerfile,
# Base Directory = / (Repository-Wurzel), Port = 80.
# Fuer Kommentare zusaetzlich: Persistent Storage /data und die Umgebungsvariable
# KOMMENTARE_ADMIN_TOKEN (siehe README.md).

FROM nginx:1.27-alpine

# Node fuer den Kommentar-Dienst (deploy/coolify/kommentare/server.mjs), ohne npm-Pakete.
RUN apk add --no-cache nodejs && rm -f /etc/nginx/conf.d/default.conf
COPY deploy/coolify/nginx.conf /etc/nginx/conf.d/merzenich.conf
COPY deploy/coolify/weather-location.conf /tmp/weather-location.conf
COPY deploy/coolify/kommentare-location.conf /tmp/kommentare-location.conf
COPY deploy/coolify/40-resolver.sh /docker-entrypoint.d/40-resolver.sh
COPY deploy/coolify/50-kommentare.sh /docker-entrypoint.d/50-kommentare.sh
COPY deploy/coolify/kommentare/server.mjs /opt/kommentare/server.mjs
COPY chatgpt-site/ /usr/share/nginx/html/

# Wetter-Proxy und Kommentar-Weiterleitung unmittelbar vor die schliessende
# server-Klammer einsetzen. nginx.conf bleibt weiterhin die aus _redirects/_headers erzeugte
# Datei und muss fuer diese isolierte API-Ergaenzung nicht von Hand gepflegt werden.
RUN cat /tmp/weather-location.conf /tmp/kommentare-location.conf > /tmp/extra-locations.conf \
 && awk 'BEGIN { while ((getline line < "/tmp/extra-locations.conf") > 0) extra = extra line ORS } /^}$/ { printf "%s", extra } { print }' \
      /etc/nginx/conf.d/merzenich.conf > /tmp/merzenich.conf \
 && mv /tmp/merzenich.conf /etc/nginx/conf.d/merzenich.conf \
 && rm -f /tmp/weather-location.conf /tmp/kommentare-location.conf /tmp/extra-locations.conf \
 && chmod +x /docker-entrypoint.d/40-resolver.sh /docker-entrypoint.d/50-kommentare.sh \
 && node --version

# _redirects und _headers sind Cloudflare-Anweisungen und gehoeren nicht ins Web.
RUN rm -f /usr/share/nginx/html/_redirects /usr/share/nginx/html/_headers \
 && nginx -t

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD wget -q -O /dev/null http://127.0.0.1/ && wget -q -O /dev/null http://127.0.0.1/api/kommentare/status || exit 1

EXPOSE 80
