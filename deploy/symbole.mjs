#!/usr/bin/env node
/**
 * Webseiten-Symbole aus dem M-Logo (chatgpt-site/assets/img/avatar-1024.png):
 * favicon.ico (16/32/48), favicon-32.png, icon-180.png (Apple, Home-Bildschirm),
 * icon-192.png und icon-512.png (Android, Manifest). Einmalig bzw. nach einem
 * neuen Logo ausführen (nicht in CI, Playwright global wie deploy/marke-png.mjs):
 *   NODE_PATH=$(npm root -g) node deploy/symbole.mjs
 * Kleine Größen und das Apple-Symbol kommen aus favicon.svg (Zeichen groß im
 * Feld, Apple ohne eigene Rundung, die setzt iOS), 192/512 aus dem 1024er PNG
 * (Zeichen in der sicheren Zone für runde Masken unter Android).
 * Hintergrund: Handys und Browser fragen zuerst /favicon.ico und
 * /apple-touch-icon.png ab; bis Theme 21.11 waren das nur Umleitungen auf das
 * 1024er PNG, und gespeicherte Symbole früherer Besuche blieben stehen.
 */
import { createRequire } from 'node:module';
import { readFileSync, writeFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const { chromium } = createRequire(process.env.NODE_PATH + '/')('playwright');
const ordner = join(dirname(fileURLToPath(import.meta.url)), '..', 'chatgpt-site', 'assets', 'img');
const svg = readFileSync(join(ordner, 'favicon.svg'), 'utf8').replace(/@media \(prefers-color-scheme:dark\)\{[^}]*\}[^}]*\}/, '');
const svgEckig = svg.replace(/<rect class="g" width="64" height="64" rx="12"\/>/, '<rect class="g" width="64" height="64"/>');
const daten = (s) => 'data:image/svg+xml;base64,' + Buffer.from(s.replace('viewBox="0 0 64 64"', 'viewBox="0 0 64 64" width="1024" height="1024"')).toString('base64');
const avatar = 'data:image/png;base64,' + readFileSync(join(ordner, 'avatar-1024.png')).toString('base64');
const browser = await chromium.launch({ executablePath: process.env.PW_CHROMIUM || '/opt/pw-browsers/chromium' });
const seite = await browser.newPage();
await seite.setContent('<canvas id="c"></canvas>');

/** PNG in Kantenlänge n, schrittweise verkleinert (scharf auch bei 16 px). */
async function png(n, quelle) {
  const b64 = await seite.evaluate(async ({ quelle, n }) => {
    const img = new Image(); img.src = quelle; await img.decode();
    let w = img.width, bild = img;
    while (w / 2 >= n) {
      const z = document.createElement('canvas'); z.width = z.height = w / 2;
      const c = z.getContext('2d'); c.imageSmoothingQuality = 'high'; c.drawImage(bild, 0, 0, w / 2, w / 2);
      bild = z; w = w / 2;
    }
    const e = document.createElement('canvas'); e.width = e.height = n;
    const c = e.getContext('2d'); c.imageSmoothingQuality = 'high'; c.drawImage(bild, 0, 0, n, n);
    return e.toDataURL('image/png').split(',')[1];
  }, { quelle, n });
  return Buffer.from(b64, 'base64');
}

/** ICO mit PNG-Einträgen (von allen aktuellen Browsern gelesen). */
function ico(bilder) {
  const kopf = Buffer.alloc(6 + 16 * bilder.length);
  kopf.writeUInt16LE(0, 0); kopf.writeUInt16LE(1, 2); kopf.writeUInt16LE(bilder.length, 4);
  let versatz = kopf.length;
  bilder.forEach(([n, daten], i) => {
    const o = 6 + 16 * i;
    kopf.writeUInt8(n >= 256 ? 0 : n, o); kopf.writeUInt8(n >= 256 ? 0 : n, o + 1);
    kopf.writeUInt8(0, o + 2); kopf.writeUInt8(0, o + 3);
    kopf.writeUInt16LE(1, o + 4); kopf.writeUInt16LE(32, o + 6);
    kopf.writeUInt32LE(daten.length, o + 8); kopf.writeUInt32LE(versatz, o + 12);
    versatz += daten.length;
  });
  return Buffer.concat([kopf, ...bilder.map(([, d]) => d)]);
}

const groessen = {};
for (const n of [16, 32, 48]) groessen[n] = await png(n, daten(svg));
groessen[180] = await png(180, daten(svgEckig));
for (const n of [192, 512]) groessen[n] = await png(n, avatar);
await browser.close();
writeFileSync(join(ordner, 'favicon.ico'), ico([[16, groessen[16]], [32, groessen[32]], [48, groessen[48]]]));
writeFileSync(join(ordner, 'favicon-32.png'), groessen[32]);
for (const n of [180, 192, 512]) writeFileSync(join(ordner, `icon-${n}.png`), groessen[n]);
console.log('Symbole geschrieben:', ['favicon.ico', 'favicon-32.png', 'icon-180.png', 'icon-192.png', 'icon-512.png'].join(', '));
