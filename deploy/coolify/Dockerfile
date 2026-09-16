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
COPY chatgpt-site/ /usr/share/nginx/html/

# _redirects und _headers sind Cloudflare-Anweisungen und gehoeren nicht ins Web.
RUN rm -f /usr/share/nginx/html/_redirects /usr/share/nginx/html/_headers \
 && nginx -t

HEALTHCHECK --interval=30s --timeout=5s --start-period=5s --retries=3 \
  CMD wget -q -O /dev/null http://127.0.0.1/ || exit 1

EXPOSE 80
