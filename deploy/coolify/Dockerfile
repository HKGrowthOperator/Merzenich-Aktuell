# Merzenich Aktuell - statische Auslieferung fuer Coolify.
#
# Baut kein Projekt: chatgpt-site/ IST der ausgelieferte Stand. Dieses Image
# legt ihn nur hinter einen nginx, der dieselben Weiterleitungen und Kopfzeilen
# spricht wie bisher Cloudflare Pages (siehe nginx.conf, erzeugt aus
# _redirects und _headers).
#
# In Coolify: Build Pack = Dockerfile, Dockerfile Location = deploy/coolify/Dockerfile,
# Base Directory = / (Repository-Wurzel), Port = 80.

FROM nginx:1.27-alpine

RUN rm -f /etc/nginx/conf.d/default.conf
COPY deploy/coolify/nginx.conf /etc/nginx/conf.d/merzenich.conf
COPY deploy/coolify/weather-location.conf /tmp/weather-location.conf
COPY chatgpt-site/ /usr/share/nginx/html/

# Den kleinen Wetter-Proxy unmittelbar vor die schliessende server-Klammer
# einsetzen. nginx.conf bleibt weiterhin die aus _redirects/_headers erzeugte
# Datei und muss fuer diese isolierte API-Ergaenzung nicht von Hand gepflegt werden.
RUN awk 'BEGIN { while ((getline line < "/tmp/weather-location.conf") > 0) extra = extra line ORS } /^}$/ { printf "%s", extra } { print }' \
      /etc/nginx/conf.d/merzenich.conf > /tmp/merzenich.conf \
 && mv /tmp/merzenich.conf /etc/nginx/conf.d/merzenich.conf \
 && rm -f /tmp/weather-location.conf

# _redirects und _headers sind Cloudflare-Anweisungen und gehoeren nicht ins Web.
RUN rm -f /usr/share/nginx/html/_redirects /usr/share/nginx/html/_headers \
 && nginx -t

HEALTHCHECK --interval=30s --timeout=5s --start-period=5s --retries=3 \
  CMD wget -q -O /dev/null http://127.0.0.1/ || exit 1

EXPOSE 80
