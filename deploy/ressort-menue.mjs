#!/usr/bin/env node
/**
 * Merzenich Aktuell - Daten der Ressort-Dropdowns.
 *
 * Liest deploy/ressort-menue.json (Gruppen und Links je Ressort), prueft jedes
 * Ziel gegen die ausgelieferten Seiten und haengt je Ressort die zwei
 * neuesten echten Beitraege aus api/inhalte.json an (Termine: die zwei
 * naechsten Termine aus api/latest.json). Schreibt
 * chatgpt-site/assets/ressort-menue.json und traegt dessen Hash in
 * assets/ressort-dropdowns.js ein, damit Browser und Service Worker nie einen
 * alten Stand zeigen.
 *
 * Fehlt ein Ziel, bricht der Lauf ab: Das Menue verlinkt nur, was es gibt.
 * Aufruf: node deploy/ressort-menue.mjs [--check]   (vor kopf-theme-einbinden)
 */
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');
const quelle = JSON.parse(readFileSync(join(wurzel, 'deploy', 'ressort-menue.json'), 'utf8'));
import { sportBezug } from './lib-artikel.mjs';
const inhalte = JSON.parse(readFileSync(join(site, 'api', 'inhalte.json'), 'utf8'));
const latest = JSON.parse(readFileSync(join(site, 'api', 'latest.json'), 'utf8'));

const ORTSTEIL = { merzenich: 'Merzenich', golzheim: 'Golzheim', girbelsrath: 'Girbelsrath', morschenich: 'Morschenich', buergewald: 'Bürgewald' };
// Ressortseite -> Schluessel im Inhaltsindex; /nachrichten/ zeigt alle.
const RESSORT = { '/blaulicht/': 'blaulicht', '/sport/': 'sport', '/vereine/': 'vereine', '/rathaus/': 'rathaus', '/leben/': 'leben', '/wirtschaft/': 'wirtschaft', '/menschen/': 'menschen', '/tipp/': 'tipp' };

const fehler = [];
const zielDa = (href) => {
  const pfad = href.split('#')[0];
  return pfad.endsWith('/') ? existsSync(join(site, pfad, 'index.html')) : existsSync(join(site, pfad));
};

const kleinstes = (srcset) => (String(srcset || '').split(',').map((s) => s.trim().split(/\s+/)).find(([, w]) => /^\d+w$/.test(w || '') && parseInt(w, 10) <= 480) || [])[0] || null;
function beitrag(a) {
  const b = a.bild && a.bild.src ? { src: kleinstes(a.bild.srcset) || a.bild.src, alt: a.bild.alt || '', symbol: !!a.bild.symbol } : null;
  return { titel: a.titel, url: a.url, ort: ORTSTEIL[a.ortsteil] || null, datum: a.datum, bild: b };
}
const artikel = [...(inhalte.artikel || [])].sort((x, y) => String(y.datum).localeCompare(String(x.datum)));

// Heute nach Berliner Kalender; vergangene Termine fallen heraus.
const heute = new Intl.DateTimeFormat('en-CA', { timeZone: 'Europe/Berlin' }).format(new Date());
const termine = (latest.events || [])
  .filter((e) => String(e.start || '').slice(0, 10) >= heute)
  .sort((x, y) => String(x.start).localeCompare(String(y.start)))
  .slice(0, 4)
  .map((e) => ({ titel: e.title, url: new URL(e.url, 'https://x/').pathname, ort: e.location || null, datum: e.start, bild: null, termin: true }));

// Aus den zehn neuesten Beitraegen zuerst die mit eigenem Bild, sonst die
// neuesten ohne Bild (als Text). Nie ein Ersatzbild.
const bebildertZuerst = (liste) => [...liste.filter((a) => a.bild?.src), ...liste.filter((a) => !a.bild?.src)];
const ressorts = {};
for (const r of quelle.ressorts) {
  if (!zielDa(r.href)) fehler.push(`${r.titel}: Ressortseite ${r.href} fehlt`);
  for (const g of r.gruppen) for (const [label, href] of g.links) if (!zielDa(href)) fehler.push(`${r.titel} / ${g.titel}: "${label}" -> ${href} fehlt`);
  const neu = r.href === '/termine/' ? termine
    : bebildertZuerst(artikel.filter((a) => (r.href === '/nachrichten/' && !sportBezug(a)) || a.ressort === RESSORT[r.href]).slice(0, 12)).slice(0, 4).map(beitrag);
  ressorts[r.href] = { titel: r.titel, alle: r.alle, gruppen: r.gruppen.map((g) => ({ titel: g.titel, links: g.links })), neu };
}
if (fehler.length) { console.error('Ressort-Menue: Ziele fehlen\n  ' + fehler.join('\n  ')); process.exit(1); }

const json = JSON.stringify({ stand: inhalte.generated || null, ressorts }) + '\n';
const hash = createHash('sha256').update(json).digest('hex').slice(0, 10);
const ziele = [['assets/ressort-menue.json', json]];
const jsPfad = join(site, 'assets', 'ressort-dropdowns.js');
const js = readFileSync(jsPfad, 'utf8');
const MARKE = /const MENUE_URL = '[^']*';/;
if (!MARKE.test(js)) { console.error('ressort-dropdowns.js: Zeile "const MENUE_URL = ...;" fehlt'); process.exit(1); }
ziele.push(['assets/ressort-dropdowns.js', js.replace(MARKE, `const MENUE_URL = '/assets/ressort-menue.json?v=${hash}';`)]);

const veraltet = [];
for (const [rel, inhalt] of ziele) {
  const pfad = join(site, rel);
  if (existsSync(pfad) && readFileSync(pfad, 'utf8') === inhalt) continue;
  veraltet.push(rel);
  if (!nurPruefen) writeFileSync(pfad, inhalt);
}
const offen = quelle.ressorts.flatMap((r) => (r.offen || []).map((o) => `${r.titel}: ${o}`));
console.log(`Ressort-Menue: ${quelle.ressorts.length} Ressorts, ${quelle.ressorts.reduce((n, r) => n + r.gruppen.reduce((m, g) => m + g.links.length, 0), 0)} Links, ${Object.values(ressorts).reduce((n, r) => n + r.neu.length, 0)} aktuelle Beiträge; ${offen.length} Wunschpunkte ohne Seite; ${veraltet.length} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}.`);
if (nurPruefen && veraltet.length) process.exit(2);
