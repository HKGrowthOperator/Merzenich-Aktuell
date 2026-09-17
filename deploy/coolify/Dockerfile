# Merzenich Aktuell - statische Auslieferung fuer Coolify.
#
# Baut kein Projekt: chatgpt-site/ IST der ausgelieferte Stand. Dieses Image
# legt ihn nur hinter einen nginx, der dieselben Weiterleitungen und Kopfzeilen
# spricht wie bisher Cloudflare Pages (siehe nginx.conf, erzeugt aus
# _redirects und _headers).
#
# Bewusst KEIN Build-Schritt im Image: Listen, Archiv, Feeds, Suchindex und
# Themen werden von den Generatoren unter deploy/ im Repo erzeugt und von der
# CI (qa.yml) geprueft bzw. nachgezogen. So ist das, was Coolify ausliefert,
# byteidentisch mit main - nichts entsteht erst beim Deploy.
#
# In Coolify: Build Pack = Dockerfile, Dockerfile Location = deploy/coolify/Dockerfile,
# Base Directory = / (Repository-Wurzel), Port = 80.
# Fuer Kommentare zusaetzlich: Persistent Storage /data und die Umgebungsvariable
# KOMMENTARE_ADMIN_TOKEN (siehe README.md).

FROM nginx:1.27-alpine

# Node fuer den Kommentar-Dienst (deploy/coolify/kommentare/server.mjs), ohne npm-Pakete.
RUN apk add --no-cache nodejs tzdata && rm -f /etc/nginx/conf.d/default.conf
# Tagesdatum im Seitenkopf wird per nginx-SSI in Ortszeit gerendert.
ENV TZ=Europe/Berlin
COPY deploy/coolify/nginx.conf /etc/nginx/conf.d/merzenich.conf
COPY deploy/coolify/kommentare-location.conf /tmp/kommentare-location.conf
COPY deploy/coolify/40-resolver.sh /docker-entrypoint.d/40-resolver.sh
COPY deploy/coolify/50-kommentare.sh /docker-entrypoint.d/50-kommentare.sh
COPY deploy/coolify/kommentare/server.mjs /opt/kommentare/server.mjs
COPY chatgpt-site/ /usr/share/nginx/html/

# Kommentar-, Abo- und Wetter-Weiterleitung (alle zum Node-Dienst) unmittelbar
# vor die schliessende server-Klammer einsetzen. weather-location.conf bleibt
# als Referenz im Repo, wird aber nicht mehr eingebaut. nginx.conf bleibt weiterhin die aus _redirects/_headers erzeugte
# Datei und muss fuer diese isolierte API-Ergaenzung nicht von Hand gepflegt werden.
RUN cat /tmp/kommentare-location.conf > /tmp/extra-locations.conf \
 && awk 'BEGIN { while ((getline line < "/tmp/extra-locations.conf") > 0) extra = extra line ORS } /^}$/ { printf "%s", extra } { print }' \
      /etc/nginx/conf.d/merzenich.conf > /tmp/merzenich.conf \
 && mv /tmp/merzenich.conf /etc/nginx/conf.d/merzenich.conf \
 && rm -f /tmp/kommentare-location.conf /tmp/extra-locations.conf \
 && chmod +x /docker-entrypoint.d/40-resolver.sh /docker-entrypoint.d/50-kommentare.sh \
 && node --version

# _redirects und _headers sind Cloudflare-Anweisungen und gehoeren nicht ins Web.
# /admin/ und /redaktion/ liefert nginx ohnehin 404; der Ordner wird zusaetzlich entfernt.
RUN rm -f /usr/share/nginx/html/_redirects /usr/share/nginx/html/_headers \
 && rm -rf /usr/share/nginx/html/admin \
 && nginx -t

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD wget -q -O /dev/null http://127.0.0.1/ && wget -q -O /dev/null http://127.0.0.1/api/kommentare/status || exit 1

EXPOSE 80
