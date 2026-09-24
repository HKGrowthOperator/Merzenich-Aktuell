#!/usr/bin/env node
/**
 * Merzenich Aktuell - PNG-Fassungen des Monogramms (einmalig, nicht in CI).
 *
 * Rendert das Zeichen aus deploy/marke-monogramm.json mit Chromium:
 *   chatgpt-site/assets/img/avatar-1024.png  App-Icon (Manifest, apple-touch-icon)
 *                                            und Social-Avatar, Papiergrund,
 *                                            Zeichen in der sicheren Zone (maskable)
 *
 * Aufruf: NODE_PATH=$(npm root -g) node deploy/marke-png.mjs
 * (Playwright muss global installiert sein; Chromium unter /opt/pw-browsers.)
 */
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const { chromium } = createRequire(process.env.NODE_PATH + '/')('playwright');
const q = JSON.parse(readFileSync(join(wurzel, 'deploy', 'marke-monogramm.json'), 'utf8'));

// Maskable: der sichere Kreis hat 80 % Durchmesser. Das Zeichen bleibt bei
// 46 % der Kantenlaenge, damit es in jeder Maske ganz sichtbar ist.
const kante = 1024, anteil = 0.46;
const s = (kante * anteil) / Math.max(q.W, q.vb_h);
const x = (kante - q.W * s) / 2, y = (kante - q.vb_h * s) / 2;
const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${kante}" height="${kante}" viewBox="0 0 ${kante} ${kante}">`
  + `<rect width="${kante}" height="${kante}" fill="#fdfdfc"/>`
  + `<g transform="translate(${x} ${y}) scale(${s})"><path fill="#171513" d="${q.d}"/>`
  + `<rect x="0" y="${q.lin_y}" width="${q.lin_w}" height="${q.lin_h}" fill="#971725"/>`
  + `<circle cx="${q.dot_x}" cy="${q.lin_y + q.lin_h / 2}" r="${q.dot_r}" fill="#971725"/></g></svg>`;

const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome' });
const p = await b.newPage({ viewport: { width: kante, height: kante } });
await p.setContent(`<html><body style="margin:0">${svg}</body></html>`);
await p.screenshot({ path: join(wurzel, 'chatgpt-site', 'assets', 'img', 'avatar-1024.png') });
await b.close();
console.log('avatar-1024.png geschrieben.');
