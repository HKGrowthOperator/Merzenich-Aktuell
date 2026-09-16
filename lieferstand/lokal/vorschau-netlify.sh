#!/usr/bin/env bash
#
# Startet die Netlify-Vorschau lokal. Baut ohne Bild-CDN, damit alle Bilder
# auch ohne Netlify-Dienste erscheinen, und serviert dist/ auf Port 8081.
#
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PORT="${PORT:-8081}"
cd "$ROOT/ma9"
IMAGE_CDN=off node build.mjs
node scripts/check.mjs
echo
echo "Vorschau läuft auf http://127.0.0.1:$PORT/"
cd dist && python3 -m http.server "$PORT" --bind 127.0.0.1
