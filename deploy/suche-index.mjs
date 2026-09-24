#!/usr/bin/env node
/**
 * Ergaenzt chatgpt-site/suche-index.json um Artikelseiten, die dort fehlen
 * (die Suche fand sonst sichtbare aktuelle Meldungen nicht). Bei bestehenden
 * Artikel-Eintraegen zieht es nur Ortsteil (o) und Ortswort (g) nach.
 * Idempotent. Aufruf: [--check]
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { artikelSammeln, dmyKurz, ORTSTEILE } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const site = join(wurzel, 'chatgpt-site');
const pfad = join(site, 'suche-index.json');
const index = JSON.parse(readFileSync(pfad, 'utf8'));
const bekannt = new Set(index.map((e) => e.u));
const artikel = artikelSammeln(site);
const teilVon = (a) => (a.ortsteil && a.ortsteil !== 'merzenich' && ORTSTEILE[a.ortsteil]) || '';
const ortWoerter = (a) => ['Merzenich', teilVon(a)].filter(Boolean);
const neu = artikel.filter((a) => !bekannt.has(a.url)).map((a) => ({
  t: a.titel, d: a.teaser, u: a.url, k: a.ressortLabel,
  g: [...ortWoerter(a), ...a.themen.map((t) => t.label)].join(' '),
  b: a.text, dt: dmyKurz(a.datum), typ: 'Meldung', ...(teilVon(a) ? { o: teilVon(a) } : {}),
}));
// Bestehende Eintraege: Ortsteil fuer die Ortsmarke der Treffer (Feld o) und
// das verstuemmelte Ortswort "Merzenicherzenich" aus einer frueheren Fassung.
const nachUrl = new Map(artikel.map((a) => [a.url, a]));
let nachgezogen = 0;
for (const e of index) {
  const a = nachUrl.get(e.u); if (!a) continue;
  const vorher = JSON.stringify(e);
  if (typeof e.g === 'string' && e.g.startsWith('Merzenicherzenich')) e.g = [...ortWoerter(a), e.g.slice('Merzenicherzenich'.length).trim()].filter(Boolean).join(' ');
  if (teilVon(a)) e.o = teilVon(a); else delete e.o;
  if (JSON.stringify(e) !== vorher) nachgezogen++;
}
if ((neu.length || nachgezogen) && !nurPruefen) writeFileSync(pfad, JSON.stringify([...neu, ...index], null, 0).replace(/\},\{/g, '},\n{') + '\n');
if (nachgezogen) console.log(`Suchindex: ${nachgezogen} Eintraege ${nurPruefen ? 'veraltet' : 'nachgezogen'} (Ortsteil, Ortswort).`);
console.log(`Suchindex: ${index.length} Eintraege, ${neu.length} ${nurPruefen ? 'fehlen' : 'ergaenzt'}${neu.length ? ': ' + neu.map((e) => e.u).join(', ') : ''}.`);
if (nurPruefen && (neu.length || nachgezogen)) process.exit(2);
