#!/usr/bin/env node
/**
 * Ergaenzt chatgpt-site/suche-index.json um Artikelseiten, die dort fehlen
 * (die Suche fand sonst sichtbare aktuelle Meldungen nicht). Bei bestehenden
 * Artikel-Eintraegen zieht es nur Ortsteil (o) und Ortswort (g) nach.
 * Idempotent. Aufruf: [--check]
 */
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { artikelSammeln, dmyKurz, ORTSTEILE, entschaerfen } from './lib-artikel.mjs';
import { termineAusSeiten } from './lib-termine.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const site = join(wurzel, 'chatgpt-site');
const pfad = join(site, 'suche-index.json');
const roh = JSON.parse(readFileSync(pfad, 'utf8'));
// Eintraege auf Seiten, die es nicht mehr gibt (geloescht, zusammengefuehrt), fallen heraus.
const seiteDa = (u) => typeof u !== 'string' || !/^\/[^#?]*\/$/.test(u) || existsSync(join(site, u, 'index.html'));
const index = roh.filter((e) => seiteDa(e.u));
const verwaist = roh.filter((e) => !seiteDa(e.u)).map((e) => e.u);
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
// Datum mit doppeltem Punkt aus der alten dmyKurz-Fassung ("28.09..").
for (const e of index) if (typeof e.dt === 'string' && /\.\.$/.test(e.dt)) { e.dt = e.dt.replace(/\.+$/, '.'); nachgezogen++; }

// Termine: jede Terminseite ist auffindbar (Audit 28.09.2026: neue Termine fehlten).
const termine = termineAusSeiten(site);
for (const t of termine) {
  const u = `/termine/${t.slug}/`;
  if (bekannt.has(u)) continue;
  const b = new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit', year: 'numeric' }).format(t.start);
  neu.push({ t: t.titel, d: `${b} · ${t.ort}`, u, k: 'Termin', g: ['Merzenich', t.ortsteil !== 'Merzenich' ? t.ortsteil : '', t.veranstalter, t.kategorie].filter(Boolean).join(' '), b: t.beschreibung, dt: dmyKurz(t.start.toISOString()), typ: 'Termin' });
  bekannt.add(u);
}

// Wichtige Service- und Redaktionsseiten: Title und Description der Seite.
const SEITEN = ['/service/', '/termine/', '/nachrichten/', '/blaulicht/', '/sport/', '/rathaus/', '/leben/', '/wirtschaft/', '/vereine/', '/tipp/', '/menschen/',
  '/anzeigen/', '/anzeigen/aufgeben/', '/immobilien/', '/jobs/', '/unternehmen/', '/betriebe/', '/traueranzeigen/', '/familienanzeigen/',
  '/kontakt/', '/meldung-senden/', '/termine/melden/', '/werben/', '/unterstuetzen/', '/whatsapp/', '/diskussion/', '/archiv/',
  '/ueber-uns/', '/grundsaetze/', '/ki-redaktion/', '/kommentarregeln/', '/korrekturen/', '/impressum/', '/datenschutz/', '/sc-1919-merzenich/'];
for (const u of SEITEN) {
  if (bekannt.has(u)) continue;
  const f = join(site, u, 'index.html');
  if (!existsSync(f)) continue;
  const html = readFileSync(f, 'utf8');
  const h1 = entschaerfen(((/<h1[^>]*>([\s\S]*?)<\/h1>/.exec(html) || [])[1] || '').replace(/<[^>]+>/g, '').trim());
  const d = entschaerfen((/<meta name="description" content="([^"]*)"/.exec(html) || [])[1] || '');
  if (!h1) continue;
  neu.push({ t: h1, d, u, k: 'Seite', g: 'Merzenich', b: '', dt: '', typ: 'Seite' });
  bekannt.add(u);
}

if ((neu.length || nachgezogen || verwaist.length) && !nurPruefen) writeFileSync(pfad, JSON.stringify([...neu, ...index], null, 0).replace(/\},\{/g, '},\n{') + '\n');
if (nachgezogen) console.log(`Suchindex: ${nachgezogen} Eintraege ${nurPruefen ? 'veraltet' : 'nachgezogen'} (Ortsteil, Ortswort).`);
console.log(`Suchindex: ${index.length} Eintraege, ${neu.length} ${nurPruefen ? 'fehlen' : 'ergaenzt'}${neu.length ? ': ' + neu.map((e) => e.u).join(', ') : ''}.`);
if (verwaist.length) console.log(`Suchindex: ${verwaist.length} Eintraege ohne Seite ${nurPruefen ? 'vorhanden' : 'entfernt'}: ${verwaist.join(', ')}`);
if (nurPruefen && (neu.length || nachgezogen || verwaist.length)) process.exit(2);
