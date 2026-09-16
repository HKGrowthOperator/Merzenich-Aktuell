#!/bin/sh
# Laeuft beim Containerstart (das nginx-Image fuehrt /docker-entrypoint.d/*.sh aus).
#
# nginx kennt /etc/resolv.conf nicht und braucht ein eigenes "resolver". Fest
# eingetragene oeffentliche Adressen waeren eine Wette: auf Hosts, die
# ausgehendes DNS nur ueber den eigenen Aufloeser zulassen, liefe der
# Wetter-Proxy danach gar nicht mehr. Deshalb uebernehmen wir genau die
# Nameserver, die der Container ohnehin benutzt - im Docker-Netz ist das
# 127.0.0.11, auf der Standard-Bridge sind es die des Hosts. Nur wenn dort
# nichts Brauchbares steht, greifen oeffentliche als Rueckfall.
set -eu

ns=$(awk '/^[[:space:]]*nameserver/ && $2 !~ /:/ { printf "%s ", $2 }' /etc/resolv.conf 2>/dev/null || true)
[ -n "${ns}" ] || ns="1.1.1.1 8.8.8.8"

printf 'resolver %svalid=300s ipv6=off;\nresolver_timeout 3s;\n' "${ns}" \
  > /etc/nginx/conf.d/00-resolver.conf

echo "resolver: ${ns}"
