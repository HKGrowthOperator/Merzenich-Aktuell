#!/usr/bin/env node
/**
 * Bindet Darstellung (theme.css/theme.js), einklappenden Kopf (kopf.js) und
 * Kommentare (kommentare.js) in alle Seiten von chatgpt-site/ ein, die
 * korrekturen.css laden, und setzt den Link zur Diskussion ins Mehr-Menue.
 * Idempotent: was schon drin ist, wird nicht doppelt eingefuegt.
 * Aufruf: node deploy/kopf-theme-einbinden.mjs [--check]
 */
import { readFileSync, writeFileSync, readdirSync, statSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const V = 'v=20260916b';
const CSS_ANKER = /<link rel="stylesheet" href="\/assets\/korrekturen\.css[^"]*">/;
const JS_ANKER = /<script src="\/assets\/v20\.js[^"]*" defer><\/script>/;
const INLINE = '<script>try{document.documentElement.dataset.theme=localStorage.getItem("merzenich-theme")==="dark"?"dark":"light"}catch(e){document.documentElement.dataset.theme="light"}</script>';
const CSS = `<link rel="stylesheet" href="/assets/theme.css?${V}">`;
const JS = `<script src="/assets/kopf.js?${V}" defer></script><script src="/assets/theme.js?${V}" defer></script>`;
const KOMMENTARE = `<script src="/assets/kommentare.js?${V}" defer></script>`;
const LINK_MEHR = '<a href="/kontakt/">Kontakt</a>';
const LINK_DISKUSSION = '<a href="/diskussion/">Diskussion</a>';

const seiten = [];
(function lauf(d) { for (const e of readdirSync(d)) { const p = join(d, e); statSync(p).isDirectory() ? lauf(p) : p.endsWith('.html') && seiten.push(p); } })(join(wurzel, 'chatgpt-site'));

let geaendert = 0, uebersprungen = 0, fehler = 0;
for (const pfad of seiten) {
  let html = readFileSync(pfad, 'utf8');
  if (!CSS_ANKER.test(html)) { uebersprungen++; continue; }
  const alt = html;
  if (!html.includes('/assets/theme.css')) html = html.replace(CSS_ANKER, (m) => m + CSS);
  if (!html.includes('merzenich-theme')) html = html.replace('<head>', '<head>' + INLINE);
  if (!html.includes('/assets/kopf.js')) {
    if (!JS_ANKER.test(html)) { fehler++; console.error('kein v20.js-Anker: ' + pfad); continue; }
    html = html.replace(JS_ANKER, (m) => m + JS);
  }
  if (!html.includes('/assets/kommentare.js')) html = html.replace(/<script src="\/assets\/theme\.js[^"]*" defer><\/script>/, (m) => m + KOMMENTARE);
  // "Diskussion" im Mehr-Menue und in der Schublade, direkt hinter Kontakt.
  if (!html.includes('href="/diskussion/"')) html = html.split(LINK_MEHR).join(LINK_MEHR + LINK_DISKUSSION);
  if (html !== alt) { geaendert++; if (!nurPruefen) writeFileSync(pfad, html); }
}
console.log(`${seiten.length} Seiten, ${geaendert} ${nurPruefen ? 'nicht aktuell' : 'geaendert'}, ${uebersprungen} ohne korrekturen.css uebersprungen, ${fehler} Fehler.`);
if (fehler || (nurPruefen && geaendert)) process.exit(2);
