#!/usr/bin/env node
/**
 * CSS-Buendel fuer das WordPress-Theme (02.10.2026).
 *
 * Die statische Seite laedt im Kopf neun Stylesheets nacheinander, alle
 * blockierend. WordPress bekommt stattdessen eine Datei (zwei auf der
 * Startseite): bundle.css = system, style, v19, v20, recovery, korrekturen,
 * werbung, theme in genau dieser Reihenfolge, unveraendert aneinandergehaengt;
 * bundle-start.css = dasselbe plus startseite.css. url()-Verweise bleiben
 * gueltig, weil die Buendel im selben Ordner liegen. Keine Datei nutzt
 * @import oder @charset (sonst waere die Reihenfolge nicht erlaubt).
 *
 * Die statische Seite selbst bleibt bei den Einzeldateien; deploy/wp-theme.mjs
 * setzt die Buendel in vorlagen/kopf-assets.html ein.
 *
 * Aufruf: node deploy/css-bundle.mjs [--check]   (vor kopf-theme-einbinden)
 */
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const ordner = join(wurzel, 'chatgpt-site', 'assets');
export const BUNDLE_TEILE = ['system.css', 'style.css', 'v19.css', 'v20.css', 'recovery.css', 'korrekturen.css', 'werbung.css', 'theme.css'];
export const BUNDLE_START_TEILE = [...BUNDLE_TEILE, 'startseite.css'];

function baue(teile) {
  let aus = `/* Erzeugt von deploy/css-bundle.mjs aus: ${teile.join(', ')}. Nicht von Hand aendern. */\n`;
  for (const t of teile) {
    const css = readFileSync(join(ordner, t), 'utf8');
    if (/@import\b|@charset\b/.test(css)) throw new Error(`css-bundle: ${t} enthaelt @import/@charset, Buendel nicht erlaubt`);
    aus += `\n/* ---- ${t} ---- */\n${css.trim()}\n`;
  }
  return aus;
}

// Nur als Skript schreiben; deploy/wp-theme.mjs importiert nur die Listen.
const alsSkript = process.argv[1] && import.meta.url === pathToFileURL(resolve(process.argv[1])).href;
let geaendert = 0;
if (alsSkript) for (const [name, teile] of [['bundle.css', BUNDLE_TEILE], ['bundle-start.css', BUNDLE_START_TEILE]]) {
  const pfad = join(ordner, name);
  const neu = baue(teile);
  if (existsSync(pfad) && readFileSync(pfad, 'utf8') === neu) continue;
  geaendert++;
  if (nurPruefen) console.error(`css-bundle: ${name} nicht aktuell`);
  else writeFileSync(pfad, neu);
}
if (alsSkript) console.log(`CSS-Buendel: bundle.css (${BUNDLE_TEILE.length} Dateien), bundle-start.css (${BUNDLE_START_TEILE.length} Dateien); ${geaendert} geaendert.`);
if (alsSkript && nurPruefen && geaendert) process.exit(2);
