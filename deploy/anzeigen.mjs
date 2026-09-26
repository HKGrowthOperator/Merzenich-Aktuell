#!/usr/bin/env node
/**
 * Werbesystem (KBS/Ordin 26.09.2026): fuellt jede Werbeflaeche der Website aus
 * deploy/anzeigen.json.
 *
 * Werbeflaechen sind Markierungen im HTML:
 *   <!-- werbung:SLOT:start --> ... <!-- werbung:SLOT:end -->
 * SLOT ist band-1 ... band-6 (Startseite, zwischen den Rubriken), spalte
 * (Servicespalte der Startseite, klebt beim Scrollen), sport (rechte Spalte der
 * Sportseite) oder artikel (Artikel- und uebrige Seiten).
 *
 * - Ein Band zeigt zwei Motive nebeneinander (mobil eines), jede andere Flaeche
 *   eines. Benachbarte Flaechen beginnen versetzt, damit nicht zweimal
 *   hintereinander dasselbe Motiv steht. Wiederholungen ueber die Seite sind
 *   erlaubt (Wunsch KBS).
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
  if (!['kunde', 'eigen'].includes(m.art)) fehler.push(`${m.id}: art muss kunde oder eigen sein`);
  if (!m.titel || !m.kunde) fehler.push(`${m.id}: titel und kunde sind Pflicht`);
  if (/€|\bEUR\b|\d+\s*Euro/i.test(`${m.titel} ${m.text} ${m.cta}`)) fehler.push(`${m.id}: keine Preise in Motiven (Preis auf Anfrage)`);
  if (/probebank|musteranzeige|demo/i.test(`${m.id} ${m.kunde} ${m.titel} ${m.text}`)) fehler.push(`${m.id}: Demo-Motive gehoeren nicht auf die Website`);
  if (m.ziel && !/^(https:\/\/|\/)/.test(m.ziel)) fehler.push(`${m.id}: ziel muss https:// oder / sein`);
  if (m.ziel && m.ziel.startsWith('/')) {
    const pfad = m.ziel.split(/[?#]/)[0];
    if (!existsSync(join(site, pfad, pfad.endsWith('/') ? 'index.html' : ''))) fehler.push(`${m.id}: Ziel ${m.ziel} gibt es nicht`);
  }
}
const MOTIVE = daten.motive;
if (MOTIVE.length < 2) fehler.push('mindestens zwei Motive noetig, sonst rotiert nichts');

// ------------------------------------------------------------------ Rendering
function motivHtml(m) {
  const extern = /^https:\/\//.test(m.ziel || '');
  const zeichen = m.art === 'kunde'
    ? `<span class="werbemotiv-zeichen" aria-hidden="true">${esc(m.zeichen || m.kunde.slice(0, 2))}</span>`
    : '<span class="werbemotiv-zeichen werbemotiv-zeichen--eigen" aria-hidden="true">MA</span>';
  const innen = zeichen
    + `<span class="werbemotiv-text"><strong>${esc(m.titel)}</strong>${m.text ? `<span>${esc(m.text)}</span>` : ''}</span>`
    + (m.cta && m.ziel ? `<span class="werbemotiv-cta">${esc(m.cta)}</span>` : '');
  const klasse = `werbemotiv werbemotiv--${m.art}`;
  if (!m.ziel) return `<div class="${klasse}" data-motiv="${esc(m.id)}">${innen}</div>`;
  const rel = extern ? ' target="_blank" rel="sponsored noopener"' : (m.art === 'kunde' ? ' rel="sponsored"' : '');
  return `<a class="${klasse}" href="${esc(m.ziel)}"${rel} data-motiv="${esc(m.id)}">${innen}</a>`;
}

// Startversatz je Flaeche. Baender zeigen zwei Motive; der Versatz um zwei
// sorgt dafuer, dass zwei aufeinanderfolgende Baender verschiedene Motive tragen.
const ANZAHL = { band: 2 };
function versatz(slot) {
  const band = /^band-(\d+)$/.exec(slot);
  if (band) return (Number(band[1]) - 1) * 2;
  if (slot === 'spalte') return 1;
  if (slot === 'sport') return 3;
  // artikel: fester Start. Listen-Folgeseiten sind Kopien ihrer Ressortseite
  // (inhaltsindex.mjs); ein Versatz je Seite waere nicht wiederholbar.
  return 0;
}
function slotHtml(slot) {
  const art = slot.startsWith('band-') ? 'band' : slot;
  const n = ANZAHL[art] || 1;
  const o = versatz(slot);
  const motive = Array.from({ length: n }, (_, i) => MOTIVE[(o + i) % MOTIVE.length]);
  return `<aside class="werbung werbung--${art}${art === 'band' ? ' shell' : ''}" data-werbung="${art}" data-versatz="${o % MOTIVE.length}" data-anzahl="${n}" aria-label="Anzeige">`
    + `<span class="werbung-label">Anzeige</span><div class="werbung-flaeche">${motive.map(motivHtml).join('')}</div></aside>`;
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
  const json = JSON.stringify({ rotationSekunden: daten.rotationSekunden || 12, motive: MOTIVE.map((m) => ({ id: m.id, html: motivHtml(m) })) }) + '\n';
  const ziel = join(site, 'assets', 'werbung.json');
  if (!existsSync(ziel) || readFileSync(ziel, 'utf8') !== json) { veraltet.push('/assets/werbung.json'); if (!nurPruefen) writeFileSync(ziel, json); }
}

// Die Startseite braucht nach jeder Rubrik genau ein Band (Wunsch KBS).
{
  const start = readFileSync(join(site, 'index.html'), 'utf8');
  for (let i = 1; i <= 6; i++) if (!start.includes(`<!-- werbung:band-${i}:start -->`)) fehler.push(`Startseite: Werbeband band-${i} fehlt`);
  if (!start.includes('<!-- werbung:spalte:start -->')) fehler.push('Startseite: Werbeflaeche in der Servicespalte fehlt');
}

if (fehler.length) { console.error('Anzeigen: ' + fehler.join('\n  ')); process.exit(2); }
console.log(`Anzeigen: ${MOTIVE.length} Motive, ${flaechen} Flaechen auf ${seiten.length} Seiten; ${veraltet.length} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}.`);
if (nurPruefen && veraltet.length) process.exit(2);
