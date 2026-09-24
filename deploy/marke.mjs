#!/usr/bin/env node
/**
 * Merzenich Aktuell - Markenzeichen (MA Editorial System 1.0).
 *
 * Eine Quelle, alle Ableitungen: deploy/marke-monogramm.json enthaelt das M,
 * nachgezeichnet aus dem M der Wortmarke "MERZENICH" (logo-on-light.png,
 * 24.09.2026), dazu die Masse von Merzenich-Linie und Bordeaux-Punkt.
 * Kein Schrift-M, keine Box, kein Kreis: dasselbe Zeichen wie oben im Logo.
 *
 * Schreibt:
 *   chatgpt-site/assets/marke/monogramm.svg       Anthrazit auf hell (Scroll-Kopf)
 *   chatgpt-site/assets/marke/monogramm-hell.svg  hell auf dunkel
 *   chatgpt-site/assets/img/favicon.svg           Browser-Tab, hell und dunkel
 *
 * Die PNG-Fassungen (App-Icon, Social-Avatar) rendert deploy/marke-png.mjs
 * einmalig mit Chromium; sie haengen an denselben Massen.
 *
 * Aufruf: node deploy/marke.mjs [--check]
 */
import { readFileSync, writeFileSync, existsSync, mkdirSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const q = JSON.parse(readFileSync(join(wurzel, 'deploy', 'marke-monogramm.json'), 'utf8'));

export const FARBEN = { tinte: '#171513', papier: '#fdfdfc', bordeaux: '#971725', hell: '#f5f2ec', nacht: '#171513' };

// Das Zeichen: M, darunter die Linie (60 % der M-Breite, linksbuendig), rechts der Punkt.
function zeichen(tinte, akzent) {
  return `<path fill="${tinte}" d="${q.d}"/>`
    + `<rect x="0" y="${q.lin_y}" width="${q.lin_w}" height="${q.lin_h}" fill="${akzent}"/>`
    + `<circle cx="${q.dot_x}" cy="${q.lin_y + q.lin_h / 2}" r="${q.dot_r}" fill="${akzent}"/>`;
}
const monogramm = (tinte) => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${q.W} ${q.vb_h}" role="img" aria-label="Merzenich Aktuell">${zeichen(tinte, FARBEN.bordeaux)}</svg>\n`;

// Favicon: Zeichen mittig auf einer Papierflaeche, im dunklen Browser umgekehrt.
function favicon() {
  const kante = 64, rand = 13;
  const s = (kante - 2 * rand) / Math.max(q.W, q.vb_h);
  const x = (kante - q.W * s) / 2, y = (kante - q.vb_h * s) / 2;
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${kante} ${kante}">`
    + `<style>.g{fill:${FARBEN.papier}}.t{fill:${FARBEN.tinte}}@media (prefers-color-scheme:dark){.g{fill:${FARBEN.nacht}}.t{fill:${FARBEN.hell}}}</style>`
    + `<rect class="g" width="${kante}" height="${kante}" rx="12"/>`
    + `<g transform="translate(${x.toFixed(2)} ${y.toFixed(2)}) scale(${s.toFixed(4)})">`
    + `<path class="t" d="${q.d}"/>`
    + `<rect x="0" y="${q.lin_y}" width="${q.lin_w}" height="${q.lin_h}" fill="${FARBEN.bordeaux}"/>`
    + `<circle cx="${q.dot_x}" cy="${q.lin_y + q.lin_h / 2}" r="${q.dot_r}" fill="${FARBEN.bordeaux}"/></g></svg>\n`;
}

const ziele = [
  ['chatgpt-site/assets/marke/monogramm.svg', monogramm(FARBEN.tinte)],
  ['chatgpt-site/assets/marke/monogramm-hell.svg', monogramm(FARBEN.hell)],
  ['chatgpt-site/assets/img/favicon.svg', favicon()],
];
const veraltet = [];
for (const [rel, inhalt] of ziele) {
  const pfad = join(wurzel, rel);
  if (existsSync(pfad) && readFileSync(pfad, 'utf8') === inhalt) continue;
  veraltet.push(rel);
  if (!nurPruefen) { mkdirSync(dirname(pfad), { recursive: true }); writeFileSync(pfad, inhalt); }
}
console.log(`Marke: ${ziele.length} Dateien, ${veraltet.length} ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}${veraltet.length ? ': ' + veraltet.join(', ') : ''}.`);
if (nurPruefen && veraltet.length) process.exit(2);
