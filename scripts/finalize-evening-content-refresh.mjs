#!/usr/bin/env node
import { existsSync, readdirSync, readFileSync, statSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(root, 'chatgpt-site');
const source = join(root, 'site-source');

const editorial = {
  generated: '2026-09-16T19:11:00+02:00',
  hero: {
    id: '2026-09-14-sc-merzenich-oberzier-3-2',
    url: '/sport/sc-merzenich-oberzier-3-2/',
    title: 'SC Merzenich gewinnt 3:2 gegen BC Oberzier',
    teaser: 'Drei Punkte im Heimspiel: Der SC 1919 Merzenich schlägt den BC Oberzier 3:2 und steht nach vier Spielen mit neun Punkten auf Platz fünf.',
    kicker: 'Kreisliga A',
    eyebrow: 'Aktuell · Heimspiel vom 13. September',
    location: 'MERZENICH',
    published: '2026-09-14T09:15:00+02:00',
    timeLabel: '14.09. · 09:15 Uhr',
    readTime: '1 Min. Lesezeit',
    image: '/assets/img/ph-sport.svg',
    imageAlt: 'Symbolbild Sport – kein Vereinsfoto',
    imageBadge: 'Symbolbild',
    imageFit: 'cover'
  },
  secondary: {
    id: '2026-09-16-geschwindigkeitskontrolle-grundschule',
    url: '/blaulicht/geschwindigkeitskontrolle-grundschule/',
    title: 'Viertklässler unterstützen Geschwindigkeitskontrolle in Merzenich',
    teaser: 'Grundschulkinder begleiteten Polizei und Ordnungsamt bei Geschwindigkeitskontrollen. 37 Zitronen gingen an Verkehrsteilnehmende, die zu schnell unterwegs waren.',
    kicker: 'Blaulicht',
    location: 'MERZENICH',
    published: '2026-09-16T11:14:00+02:00',
    timeLabel: '16.09. · 11:14 Uhr'
  }
};
writeFileSync(join(site, 'api/editorial-current.json'), JSON.stringify(editorial, null, 2) + '\n');

// One exact unresolved asset must not leak into any public HTML/JSON.
const walk = (dir, exts, fn) => {
  if (!existsSync(dir)) return;
  for (const name of readdirSync(dir)) {
    const p = join(dir, name);
    if (statSync(p).isDirectory()) walk(p, exts, fn);
    else if (exts.some(ext => p.endsWith(ext))) fn(p);
  }
};

walk(site, ['.html', '.json', '.xml'], file => {
  let text = readFileSync(file, 'utf8');
  const before = text;
  text = text
    .replaceAll('https://merzenich-aktuell.de/assets/uploads/sc-1919-merzenich-logo.webp', 'https://merzenich-aktuell.de/assets/img/ph-sport.svg')
    .replaceAll('/assets/uploads/sc-1919-merzenich-logo.webp', '/assets/img/ph-sport.svg')
    .replaceAll('Vereinslogo des SC 1919 Merzenich', 'Symbolbild Sport – kein Vereinsfoto')
    .replaceAll('Vereinslogo SC 1919 Merzenich', 'Symbolbild Sport – kein Vereinsfoto')
    .replaceAll('Vereinswappen des SC 1919 Merzenich', 'Symbolbild Sport – kein Vereinsfoto')
    .replaceAll('Offizielles Vereinslogo', 'Symbolbild');
  if (text !== before) writeFileSync(file, text);
});

// Also sanitize the canonical content source so a future generator run cannot
// silently reintroduce the unresolved club logo.
walk(join(source, 'content'), ['.md', '.json'], file => {
  let text = readFileSync(file, 'utf8');
  const before = text;
  text = text
    .replaceAll('https://merzenich-aktuell.de/assets/uploads/sc-1919-merzenich-logo.webp', 'https://merzenich-aktuell.de/assets/img/ph-sport.svg')
    .replaceAll('/assets/uploads/sc-1919-merzenich-logo.webp', '/assets/img/ph-sport.svg')
    .replaceAll('Vereinswappen des SC 1919 Merzenich', 'Symbolbild Sport – kein Vereinsfoto')
    .replaceAll('Vereinslogo des SC 1919 Merzenich', 'Symbolbild Sport – kein Vereinsfoto')
    .replaceAll('credit: "SC 1919 Merzenich"\n  type: logo', 'credit: "Merzenich Aktuell · Symbolbild"\n  type: symbol');
  if (text !== before) writeFileSync(file, text);
});

// latest.json must represent today's refresh date rather than an old snapshot.
const latestPath = join(site, 'api/latest.json');
if (existsSync(latestPath)) {
  const latest = JSON.parse(readFileSync(latestPath, 'utf8'));
  latest.generated = '2026-09-16T19:11:00+02:00';
  latest.stand = '2026-09-16T19:11:00+02:00';
  if (Array.isArray(latest.events)) {
    const now = new Date('2026-09-16T19:11:00+02:00');
    latest.events = latest.events.filter(e => {
      const end = e.end || e.start;
      return !end || Number.isNaN(Date.parse(end)) || new Date(end) >= now;
    });
  }
  writeFileSync(latestPath, JSON.stringify(latest, null, 2) + '\n');
}

// Guardrails: rights-sensitive logo reference and stale stand must be absent.
const offenders = [];
walk(site, ['.html', '.json', '.xml'], file => {
  const text = readFileSync(file, 'utf8');
  if (text.includes('sc-1919-merzenich-logo.webp')) offenders.push(file.slice(site.length + 1));
});
if (offenders.length) throw new Error('Unresolved SC logo still referenced in public site: ' + offenders.join(', '));

console.log('Rights-safe finalization complete: sport hero retained, police item secondary, unresolved club logo removed.');
