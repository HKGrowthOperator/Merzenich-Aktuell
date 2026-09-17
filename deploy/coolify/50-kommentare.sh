#!/bin/sh
# Laeuft beim Containerstart (das nginx-Image fuehrt /docker-entrypoint.d/*.sh aus)
# und startet den Kommentar-Dienst im Hintergrund. nginx wird danach vom
# Entrypoint wie gewohnt im Vordergrund gestartet und leitet /api/kommentare/
# an 127.0.0.1:8787 weiter (kommentare-location.conf).
#
# Ablage: /data (in Coolify als "Persistent Storage" einhaengen). Fehlt das,
# laeuft der Dienst trotzdem, warnt aber im Log und unter /api/kommentare/status.
set -eu

if [ -d /data ]; then
  chown nginx:nginx /data 2>/dev/null || true
fi

node /opt/kommentare/server.mjs >/proc/1/fd/1 2>/proc/1/fd/2 &
echo "kommentare: Dienst gestartet (PID $!)"
