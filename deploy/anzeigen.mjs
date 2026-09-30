#!/usr/bin/env node
/**
 * Werbesystem (KBS/Ordin 26.09.2026): fuellt jede Werbeflaeche der Website aus
 * deploy/anzeigen.json.
 *
 * Werbeflaechen sind Markierungen im HTML:
 *   <!-- werbung:SLOT:start --> ... <!-- werbung:SLOT:end -->
 * SLOT ist band-1 ... band-6 (Startseite, zwischen den Rubriken), buehne
 * (dritte Karte unten in der Buehne, Kartengroesse), spalte
 * (Servicespalte der Startseite, klebt beim Scrollen), sport (rechte Spalte der
 * Sportseite) oder artikel (Artikel- und uebrige Seiten).
 *
 * - Jede Flaeche zeigt ein Demo-Motiv mit echter, bereits freigegebener Fotografie.
 *   Benachbarte
 *   Flaechen beginnen versetzt, damit nicht zweimal hintereinander dasselbe
 *   Motiv steht. Wiederholungen ueber die Seite sind erlaubt.
 * - Das HTML traegt den Startzustand, damit ohne JavaScript Werbung steht.
 *   assets/werbung.js rotiert danach alle Flaechen im selben Takt.
 * - Alte Werbebloecke (.ad-row-body mit den zwei Textkaesten, .home-ad-band)
 *   werden einmalig in Markierungen umgewandelt.
 * - Schreibt assets/werbung.json (Motive als fertiges HTML fuer die Rotation).
 *
 * Aufruf: node deploy/anzeigen.mjs [--check]
 */
import { readFileSync, writeFileSync, readdirSync, statSync, existsSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

const daten = JSON.parse(readFileSync(join(wurzel, 'deploy', 'anzeigen.json'), 'utf8'));
const fehler = [];

// ------------------------------------------------------------------ Pruefung
const ids = new Set();
for (const m of daten.motive) {
  if (!m.id || ids.has(m.id)) fehler.push(`Motiv ohne oder mit doppelter id: ${m.id}`);
  ids.add(m.id);
  if (!/^[a-z0-9-]+$/.test(m.typ || '')) fehler.push(`${m.id}: typ muss ein sicherer CSS-Schluessel sein`);
  for (const f of ['kunde', 'eyebrow', 'headline', 'text', 'cta', 'ziel', 'bild', 'bildAlt']) if (!m[f]) fehler.push(`${m.id}: ${f} fehlt`);
  if (m.bild && !/^\/assets\//.test(m.bild)) fehler.push(`${m.id}: bild muss unter /assets/ liegen`);
  if (m.bild && /^\/assets\//.test(m.bild) && !existsSync(join(site, m.bild.replace(/^\//, '')))) fehler.push(`${m.id}: Bilddatei fehlt: ${m.bild}`);
  if (/€|\bEUR\b|\d+\s*Euro/i.test(`${m.headline} ${m.text} ${m.cta}`)) fehler.push(`${m.id}: keine Preise in Motiven (Preis auf Anfrage)`);
  if (m.ziel && !/^(https:\/\/|\/)/.test(m.ziel)) fehler.push(`${m.id}: ziel muss https:// oder / sein`);
  if (m.ziel && m.ziel.startsWith('/')) {
    const pfad = m.ziel.split(/[?#]/)[0];
    if (!existsSync(join(site, pfad, pfad.endsWith('/') ? 'index.html' : ''))) fehler.push(`${m.id}: Ziel ${m.ziel} gibt es nicht`);
  }
}
const MOTIVE = daten.motive;
// Urhebernachweis je Motiv (CC BY/BY-SA verlangt ihn dort, wo das Foto
// erscheint). Er steht unter der Werbeflaeche, nicht in der Anzeige, und
// wechselt mit der Rotation. CC0 und gemeinfreie Fotos brauchen keinen.
const CREDITS = new Map(JSON.parse(readFileSync(join(site, 'assets', 'werben', 'credits.json'), 'utf8')).images.map((c) => [c.src, c]));
function creditHtml(m) {
  const c = CREDITS.get(m.bild);
  if (!c) { fehler.push(`${m.id}: kein Eintrag in assets/werben/credits.json fuer ${m.bild}`); return ''; }
  if (!/^CC BY/i.test(c.license || '')) return '';
  if (!c.author || !c.sourceUrl || !c.licenseUrl) { fehler.push(`${m.id}: Urheber, Quelle oder Lizenzlink fehlt in credits.json`); return ''; }
  return `<span class="werbung-credit-vor">Foto: </span><a href="${esc(c.sourceUrl)}" target="_blank" rel="noopener">${esc(c.author)}</a>, <a href="${esc(c.licenseUrl)}" target="_blank" rel="noopener license">${esc(c.license)}</a>`;
}
const LABEL = daten.label || 'Anzeige';
if (MOTIVE.length < 2) fehler.push('mindestens zwei Motive noetig, sonst rotiert nichts');

// ------------------------------------------------------------------ Rendering
// Markup der Anzeigenrotation: Text plus kuratierte, lokal materialisierte
// Werbe-Fotografie. Format band: Werbebaender und Artikelseiten.
// Format gap: hochkant in Spalten.
function kunst(m) {
  return '<span class="ma-ad-art ma-ad-art--' + esc(m.typ) + '">'
    + '<img src="' + esc(m.bild) + '" alt="' + esc(m.bildAlt) + '" loading="lazy" decoding="async">'
    + '</span>';
}
function motivHtml(m, format) {
  const extern = /^https:\/\//.test(m.ziel);
  const rel = extern ? ' target="_blank" rel="sponsored noopener"' : '';
  return `<a class="ma-ad-card ma-ad-card--${format} ma-ad-theme--${esc(m.typ)}" href="${esc(m.ziel)}"${rel} data-motiv="${esc(m.id)}" aria-label="Anzeige: ${esc(m.kunde)} – ${esc(m.headline)}">`
    + kunst(m)
    + '<span class="ma-ad-copy">'
    + `<span class="ma-ad-eyebrow">${esc(m.eyebrow)}</span>`
    + `<strong>${esc(m.headline)}</strong>`
    + `<span class="ma-ad-text">${esc(m.text)}</span>`
    + `<span class="ma-ad-cta">${esc(m.cta)}</span>`
    + (format === 'gap' ? '<span class="ma-ad-muster">Musteranzeige</span>' : '')
    + '</span></a>';
}

// Startversatz je Flaeche: benachbarte Baender beginnen mit verschiedenen Motiven.
function versatz(slot) {
  const band = /^band-(\d+)$/.exec(slot);
  if (band) return Number(band[1]) - 1;
  if (slot === 'spalte') return 1;
  if (slot === 'buehne') return 2;
  if (slot === 'sport') return 3;
  // artikel: fester Start. Listen-Folgeseiten sind Kopien ihrer Ressortseite
  // (inhaltsindex.mjs); ein Versatz je Seite waere nicht wiederholbar.
  return 0;
}
function slotHtml(slot) {
  const art = slot.startsWith('band-') ? 'band' : slot;
  const format = art === 'spalte' || art === 'sport' || art === 'buehne' ? 'gap' : 'band';
  const o = versatz(slot) % MOTIVE.length;
  return `<aside class="werbung werbung--${art}${art === 'band' ? ' shell' : ''}" data-werbung="${art}" data-format="${format}" data-versatz="${o}" data-anzahl="1" aria-label="${esc(LABEL)}">`
    + `<span class="werbung-label">${esc(LABEL)}</span><div class="werbung-flaeche ma-ad-rotator ma-ad-rotator--${format}">${motivHtml(MOTIVE[o], format)}</div>`
    + `<span class="werbung-credit">${creditHtml(MOTIVE[o])}</span></aside>`;
}

// ------------------------------------------------------------------ Migration
const ALT_ARTIKEL = '<div class="ad-row-body" aria-label="Anzeigen"><div class="managed-ad"><span class="ad-label">Anzeige</span><a href="https://kbs-management.tv/" target="_blank" rel="noopener sponsored"><span class="ad-name">KBS Management GmbH<small>Merzenich · Kreis Düren</small></span></a></div><div class="managed-ad"><span class="ad-label">Anzeige</span><div class="ad-in"><span class="ad-name">AJ Sports Entertainment<small>Merzenich · Kreis Düren</small></span></div></div></div>';
const LEER = (slot) => `<!-- werbung:${slot}:start --><!-- werbung:${slot}:end -->`;
function migrieren(html) {
  return html.split(ALT_ARTIKEL).join(LEER('artikel'));
}

// ------------------------------------------------------------------ Seiten
const MARKE = /<!-- werbung:([a-z0-9-]+):start -->[\s\S]*?<!-- werbung:\1:end -->/g;
const seiten = [];
(function lauf(d) { for (const e of readdirSync(d)) { const p = join(d, e); if (statSync(p).isDirectory()) { if (!['admin', 'redaktion', 'node_modules'].includes(e)) lauf(p); } else if (e.endsWith('.html')) seiten.push(p); } })(site);

const veraltet = [];
let flaechen = 0;
for (const pfad of seiten) {
  const alt = readFileSync(pfad, 'utf8');
  const rel = pfad.slice(site.length);
  let html = migrieren(alt);
  html = html.replace(MARKE, (m, slot) => { flaechen++; return `<!-- werbung:${slot}:start -->${slotHtml(slot)}<!-- werbung:${slot}:end -->`; });
  if (html !== alt) { veraltet.push(rel); if (!nurPruefen) writeFileSync(pfad, html); }
}

// Laufzeitdaten fuer die Rotation.
{
  const json = JSON.stringify({ rotationSekunden: daten.rotationSekunden || 12, motive: MOTIVE.map((m) => ({ id: m.id, band: motivHtml(m, 'band'), gap: motivHtml(m, 'gap'), credit: creditHtml(m) })) }) + '\n';
  const ziel = join(site, 'assets', 'werbung.json');
  if (!existsSync(ziel) || readFileSync(ziel, 'utf8') !== json) { veraltet.push('/assets/werbung.json'); if (!nurPruefen) writeFileSync(ziel, json); }
}

// Die Startseite braucht nach jeder Rubrik genau ein Band (Wunsch KBS).
{
  const start = readFileSync(join(site, 'index.html'), 'utf8');
  for (let i = 1; i <= 6; i++) if (!start.includes(`<!-- werbung:band-${i}:start -->`)) fehler.push(`Startseite: Werbeband band-${i} fehlt`);
  if (!start.includes('<!-- werbung:spalte:start -->')) fehler.push('Startseite: Werbeflaeche in der Servicespalte fehlt');
  if (!start.includes('<!-- werbung:buehne:start -->')) fehler.push('Startseite: Anzeige in der Buehne (unten rechts) fehlt');
}

if (fehler.length) { console.error('Anzeigen: ' + fehler.join('\n  ')); process.exit(2); }
console.log(`Anzeigen: ${MOTIVE.length} Motive, ${flaechen} Flaechen auf ${seiten.length} Seiten; ${veraltet.length} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}.`);
if (nurPruefen && veraltet.length) process.exit(2);
