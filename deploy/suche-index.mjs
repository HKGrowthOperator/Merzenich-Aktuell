#!/usr/bin/env node
/**
 * Ergaenzt chatgpt-site/suche-index.json um Artikelseiten, die dort fehlen
 * (die Suche fand sonst sichtbare aktuelle Meldungen nicht). Bestehende
 * Eintraege bleiben unveraendert. Idempotent. Aufruf: [--check]
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { artikelSammeln, dmyKurz } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const site = join(wurzel, 'chatgpt-site');
const pfad = join(site, 'suche-index.json');
const index = JSON.parse(readFileSync(pfad, 'utf8'));
const bekannt = new Set(index.map((e) => e.u));
const neu = artikelSammeln(site).filter((a) => !bekannt.has(a.url)).map((a) => ({
  t: a.titel, d: a.teaser, u: a.url, k: a.ressortLabel,
  g: [a.ort.replace(/^\w/, (c) => c + a.ort.slice(1).toLowerCase()).replace(/^(\w)\w*/, (m) => m[0] + m.slice(1).toLowerCase()), ...a.themen.map((t) => t.label)].join(' '),
  b: a.text, dt: dmyKurz(a.datum), typ: 'Meldung',
}));
if (neu.length && !nurPruefen) writeFileSync(pfad, JSON.stringify([...neu, ...index], null, 0).replace(/\},\{/g, '},\n{') + '\n');
console.log(`Suchindex: ${index.length} Eintraege, ${neu.length} ${nurPruefen ? 'fehlen' : 'ergaenzt'}${neu.length ? ': ' + neu.map((e) => e.u).join(', ') : ''}.`);
if (nurPruefen && neu.length) process.exit(2);
