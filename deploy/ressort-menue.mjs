#!/usr/bin/env node
/**
 * Merzenich Aktuell - Daten der Ressort-Dropdowns.
 *
 * Liest deploy/ressort-menue.json (rote Titel je Ressort, Wunsch Betreiber
 * 15.10.2026: keine kleinen Unterpunkte mehr), prueft jedes Ziel gegen die
 * ausgelieferten Seiten (Sammelseiten /rubrik/<slug>/ schreibt vorher
 * rubrik-prerender.mjs) und haengt je Ressort die neuesten echten Beitraege
 * aus api/inhalte.json an (Termine: die naechsten Termine aus
 * api/latest.json). Schreibt
 * chatgpt-site/assets/ressort-menue.json und traegt dessen Hash in
 * assets/ressort-dropdowns.js ein, damit Browser und Service Worker nie einen
 * alten Stand zeigen.
 *
 * Fehlt ein Ziel, bricht der Lauf ab: Das Menue verlinkt nur, was es gibt.
 * Aufruf: node deploy/ressort-menue.mjs [--check]   (nach rubrik-prerender, vor kopf-theme-einbinden)
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
const RESSORT = { '/blaulicht/': 'blaulicht', '/sport/': 'sport', '/vereine/': 'vereine', '/rathaus/': 'rathaus', '/leben/': 'leben', '/wirtschaft/': 'wirtschaft', '/unternehmen/': 'wirtschaft', '/menschen/': 'menschen', '/tipp/': 'tipp' };

const fehler = [];
// Unternehmen (Auftrag Betreiber 13.10.2026): Firmen aus der Theme-Datei, wie
// WordPress sie ausgibt (inc/unternehmen.php, ma21_menue_firmen), solange es
// keine echten Unternehmenskanaele gibt. Bilder mit Nachweis aus credits.json.
const musterDatei = join(wurzel, 'wordpress', 'theme', 'merzenich-aktuell', 'inc', 'unternehmen-muster.json');
const werbenBilder = Object.fromEntries((JSON.parse(readFileSync(join(site, 'assets', 'werben', 'credits.json'), 'utf8')).images || []).map((i) => [i.id, i]));
const kurzname = (s) => String(s || '').replace(/\s*(\(|https?:|,).*$/u, '').trim() || 'Wikimedia Commons';
function musterBild(id) {
  const i = werbenBilder[id];
  if (!i) { fehler.push(`Musterfirmen: Bild ${id} fehlt in assets/werben/credits.json`); return null; }
  return { src: i.src.replace(/\.jpg$/, '-720.jpg'), alt: i.alt || '', foto: kurzname(i.author), lizenz: i.license || '', lizenz_url: i.licenseUrl || '' };
}
const musterFirmen = (JSON.parse(readFileSync(musterDatei, 'utf8')).firmen || []).map((f) => {
  const url = `/unternehmen/${f.slug}/`;
  return { name: f.name, url, zeile: `${f.branche} · ${ORTSTEIL[f.ort] || 'Merzenich'}`, kuerzel: f.kuerzel, farbe: f.farbe, logo: '', muster: true,
    beitraege: (f.beitraege || []).map((b) => ({ titel: b.titel, url: url + b.slug + '/', anriss: b.anriss || '', zeit: '', muster: true, gesponsert: false, bild: musterBild(b.bild) })) };
});
// „Neu von Unternehmen“: der Reihe nach je Firma ein Musterbeitrag.
const musterNeu = [];
for (let runde = 0; musterNeu.length < 4 && runde < 5; runde++) for (const f of musterFirmen) if (f.beitraege[runde] && musterNeu.length < 4) {
  const b = f.beitraege[runde];
  musterNeu.push({ titel: b.titel, url: b.url, ort: `${f.name} · ${f.zeile.split(' · ')[1]}`, datum: '', bild: b.bild, muster: true });
}

// Ziel ohne Abfrage- und Sprungteil (/termine/?ort=golzheim zeigt /termine/ vorgefiltert).
const zielDa = (href) => {
  const pfad = href.split(/[?#]/)[0];
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
  if (!Array.isArray(r.punkte) || !r.punkte.length) fehler.push(`${r.titel}: keine Punkte (rote Titel)`);
  if (r.gruppen) fehler.push(`${r.titel}: "gruppen" gibt es nicht mehr, nur "punkte" (rote Titel ohne Unterpunkte)`);
  const punkte = (r.punkte || []).map((p) => ({ titel: p.titel, href: p.rubrik ? `/rubrik/${p.rubrik.slug}/` : p.href }));
  for (const p of punkte) if (!p.titel || !p.href || !zielDa(p.href)) fehler.push(`${r.titel}: "${p.titel}" -> ${p.href} fehlt`);
  const passend = artikel.filter((a) => (r.href === '/nachrichten/' && !sportBezug(a)) || a.ressort === RESSORT[r.href]);
  const neu = r.href === '/termine/' ? termine
    : bebildertZuerst(passend.slice(0, 12)).slice(0, 4).map(beitrag);
  // Weitere Bildmeldungen links unter dem Ressortnamen: nur Beitraege mit
  // eigenem Bild, die nicht schon rechts stehen.
  const rechts = new Set(neu.map((x) => x.url));
  const mehr = r.mehrBilder ? passend.filter((a) => a.bild?.src && !rechts.has(a.url)).slice(0, r.mehrBilder).map(beitrag) : [];
  if (r.mehrBilder && mehr.length < r.mehrBilder) fehler.push(`${r.titel}: nur ${mehr.length} von ${r.mehrBilder} weiteren Bildmeldungen`);
  const firmen = r.href === '/unternehmen/' ? musterFirmen : [];
  ressorts[r.href] = { titel: r.titel, alle: r.alle, punkte, neu: firmen.length ? musterNeu : neu, ...(mehr.length ? { mehr } : {}), ...(firmen.length ? { firmen } : {}) };
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
console.log(`Ressort-Menue: ${quelle.ressorts.length} Ressorts, ${quelle.ressorts.reduce((n, r) => n + (r.punkte || []).length, 0)} rote Titel, ${Object.values(ressorts).reduce((n, r) => n + r.neu.length, 0)} aktuelle Beiträge; ${offen.length} Wunschpunkte ohne Seite; ${veraltet.length} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}.`);
if (nurPruefen && veraltet.length) process.exit(2);
