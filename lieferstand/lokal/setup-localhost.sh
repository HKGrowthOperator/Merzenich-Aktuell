#!/usr/bin/env bash
#
# Baut eine lokale WordPress-Installation von Merzenich Aktuell auf.
# Datenbank ist SQLite, es wird also kein MySQL und kein Docker gebraucht.
#
# Voraussetzungen: git, PHP 8.1 oder neuer mit den Erweiterungen pdo_sqlite,
# sqlite3, gd, zip, mbstring, xml und curl, sowie python3 (nur fuer den
# deutschen Sprachkatalog, optional).
#
# Aufruf aus dem Wurzelverzeichnis dieses Pakets:
#   bash lokal/setup-localhost.sh
#
set -euo pipefail

WP_VERSION="${WP_VERSION:-7.1}"
PORT="${PORT:-8080}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WORK="$ROOT/lokal/instanz"
THEME_SRC="$ROOT/v13/wp/merzenich-aktuell"

if [ ! -d "$THEME_SRC" ]; then
	echo "Theme nicht gefunden unter $THEME_SRC" >&2
	exit 1
fi

mkdir -p "$WORK"
cd "$WORK"

# 1. WordPress-Kern holen.
if [ ! -d wp ]; then
	echo "→ WordPress $WP_VERSION wird geholt ..."
	git clone --depth 1 --branch "$WP_VERSION" https://github.com/WordPress/WordPress.git wp
	rm -rf wp/.git
fi

# 2. SQLite-Treiber holen und einbauen.
if [ ! -f wp/wp-content/db.php ]; then
	echo "→ SQLite-Treiber wird eingebaut ..."
	rm -rf sqlite-plugin
	git clone --depth 1 --branch v3.0.1 https://github.com/WordPress/sqlite-database-integration.git sqlite-plugin
	bash sqlite-plugin/bin/build-sqlite-plugin-zip.sh >/dev/null
	rm -rf wp/wp-content/plugins/sqlite-database-integration
	cp -R sqlite-plugin/build/plugin-sqlite-database-integration wp/wp-content/plugins/sqlite-database-integration
	sed -e 's#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#__NICHT_GESETZT__#g' \
	    -e 's#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#g' \
	    wp/wp-content/plugins/sqlite-database-integration/db.copy > wp/wp-content/db.php
fi

# 3. Importer holen.
if [ ! -d wp/wp-content/plugins/wordpress-importer ]; then
	echo "→ Importer wird geholt ..."
	rm -rf wp-importer
	git clone --depth 1 https://github.com/WordPress/wordpress-importer.git wp-importer
	mkdir -p wp/wp-content/plugins/wordpress-importer
	cp wp-importer/wordpress-importer.php wp/wp-content/plugins/wordpress-importer/
	cp -R wp-importer/src/* wp/wp-content/plugins/wordpress-importer/
	rm -rf wp-importer
fi

# 4. Konfiguration schreiben.
if [ ! -f wp/wp-config.php ]; then
	echo "→ Konfiguration wird geschrieben ..."
	cat > wp/wp-config.php <<'PHP'
<?php
/** Lokale Testinstallation von Merzenich Aktuell. Nicht fuer den Livebetrieb. */
define( 'DB_NAME', 'merzenich_lokal' );
define( 'DB_USER', '' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         'merzenich-lokal-auth-key-nur-fuer-tests' );
define( 'SECURE_AUTH_KEY',  'merzenich-lokal-secure-auth-key-nur-fuer-tests' );
define( 'LOGGED_IN_KEY',    'merzenich-lokal-logged-in-key-nur-fuer-tests' );
define( 'NONCE_KEY',        'merzenich-lokal-nonce-key-nur-fuer-tests' );
define( 'AUTH_SALT',        'merzenich-lokal-auth-salt-nur-fuer-tests' );
define( 'SECURE_AUTH_SALT', 'merzenich-lokal-secure-auth-salt-nur-fuer-tests' );
define( 'LOGGED_IN_SALT',   'merzenich-lokal-logged-in-salt-nur-fuer-tests' );
define( 'NONCE_SALT',       'merzenich-lokal-nonce-salt-nur-fuer-tests' );

$table_prefix = 'wp_';

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_ENVIRONMENT_TYPE', 'local' );
// Adresse der lokalen Vorschau. Wird aus MA_PORT gelesen, damit ein anderer
// Port nicht dazu fuehrt, dass WordPress auf den alten zurueckleitet.
$ma_port = getenv( 'MA_PORT' ) ? getenv( 'MA_PORT' ) : '8080';
define( 'WP_HOME', 'http://127.0.0.1:' . $ma_port );
define( 'WP_SITEURL', 'http://127.0.0.1:' . $ma_port );
// Sprache der Website. Auf einer normalen Installation waehlt man das unter
// Einstellungen -> Allgemein; hier fest gesetzt, damit die Vorschau ohne
// Sprachpaket-Download deutsch ist.
define( 'WPLANG', 'de_DE' );
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'WP_AUTO_UPDATE_CORE', false );
define( 'DISALLOW_FILE_EDIT', true );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';
PHP
fi

# 5. Theme hineinkopieren (bei jedem Lauf, damit Aenderungen ankommen).
echo "→ Theme wird kopiert ..."
mkdir -p wp/wp-content/themes/merzenich-aktuell
cp -R "$THEME_SRC/." wp/wp-content/themes/merzenich-aktuell/

# 6. Hilfsskripte hineinkopieren.
cp "$ROOT/lokal/bootstrap.php" "$ROOT/lokal/install.php" "$ROOT/lokal/activate.php" "$ROOT/lokal/import.php" .
mkdir -p wp/wp-content/mu-plugins
cp "$ROOT/lokal/ma-lokaler-postfach.php" wp/wp-content/mu-plugins/

# 7. Deutscher Sprachkatalog (Wochentage und Monate).
if command -v python3 >/dev/null 2>&1; then
	python3 "$ROOT/lokal/make-de-mo.py" wp/wp-content/languages/de_DE.mo
fi

# 8. Installieren, Theme aktivieren, Inhalte einspielen.
MA_PORT="$PORT" php install.php
MA_PORT="$PORT" php activate.php
MA_PORT="$PORT" php import.php

echo
echo "Fertig. Starten mit:"
echo "  MA_PORT=$PORT php -S 127.0.0.1:$PORT -t \"$WORK/wp\""
echo
echo "  Website:     http://127.0.0.1:$PORT/"
echo "  Verwaltung:  http://127.0.0.1:$PORT/wp-admin/  (redaktion / merzenich)"
