#!/usr/bin/env node
/**
 * WordPress-Pakete bauen (30.09.2026): Core-Plugin und Theme als ZIP nach
 * wordpress-delivery/. Das Theme bekommt dabei static/ = chatgpt-site/assets
 * ohne die großen Bildpools (editorial-pools, sources); die liefert der Server
 * bei Bedarf per Umleitung von der statischen Seite (theme/inc/ma21.php).
 * Seit 28.10.2026 bleiben beide Pakete unter 8 MB (Upload-Grenze des Servers):
 * ohne Fotos in static/ und ohne die Anzeigenvideos des Plugins; beides holt
 * der Server bei Bedarf aus dem Repository.
 * Aufruf: node deploy/wp-pakete.mjs
 */
import { execFileSync } from 'node:child_process';
import { rmSync, mkdirSync, statSync, readFileSync, writeFileSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const wp = join(wurzel, 'wordpress');
const ziel = join(wurzel, 'wordpress-delivery');
const theme = join(wp, 'theme', 'merzenich-aktuell');

execFileSync('node', [join(wurzel, 'deploy', 'wp-theme.mjs')], { stdio: 'inherit' });
rmSync(join(theme, 'static'), { recursive: true, force: true });
mkdirSync(join(theme, 'static'), { recursive: true });
execFileSync('bash', ['-c', `cd "${join(wurzel, 'chatgpt-site', 'assets')}" && tar cf - --exclude=./editorial-pools --exclude=./sources . | (cd "${join(theme, 'static')}" && tar xf -)`]);

for (const [ordner, datei] of [[join(wp, 'plugin'), 'merzenich-aktuell-core'], [join(wp, 'theme'), 'merzenich-aktuell']]) {
  const zip = join(ziel, `${datei === 'merzenich-aktuell' ? 'merzenich-aktuell-theme' : datei}.zip`);
  rmSync(zip, { force: true });
  // Plugin ohne Anzeigenvideos: die holt ma_firmen_datei() bei Bedarf aus dem Repository (Plugin 1.36.1).
  // Theme ohne Fotos in static/: die liefert /assets/ aus uploads/ma-assets oder einmal aus dem Repository (ma21_asset).
  const ohne = datei === 'merzenich-aktuell-core' ? ['*/data/banner/*.mp4', '*/data/banner/*.webm']
    : ['*/static/*.webp', '*/static/*.jpg', '*/static/*.jpeg', '*/static/*.png'];
  execFileSync('zip', ['-qr', zip, datei, '-x', '*/.DS_Store', '*/archive-alt.php.orig', ...ohne], { cwd: ordner });
  const groesse = statSync(zip).size;
  console.log(`${zip.replace(wurzel + '/', '')}: ${(groesse / 1048576).toFixed(1)} MB`);
  // Der Webserver (IONOS) lehnt Uploads ab etwa 8 MB mit 413 ab (gemessen 28.10.2026).
  if (groesse >= 8 * 1024 * 1024 - 256 * 1024) throw new Error(`${zip} ist zu groß zum Hochladen (Grenze etwa 8 MB).`);
}

// docs/SHA256SUMS.txt: Zeilen der beiden Pakete nachziehen (qa/pruefung.mjs prüft sie).
const summen = join(wurzel, 'docs', 'SHA256SUMS.txt');
let text = readFileSync(summen, 'utf8');
for (const name of ['merzenich-aktuell-theme.zip', 'merzenich-aktuell-core.zip']) {
  const hash = createHash('sha256').update(readFileSync(join(ziel, name))).digest('hex');
  text = text.replace(new RegExp(`^[0-9a-f]{64}  wordpress-delivery/${name.replace('.', '\\.')}$`, 'm'), `${hash}  wordpress-delivery/${name}`);
}
writeFileSync(summen, text);

