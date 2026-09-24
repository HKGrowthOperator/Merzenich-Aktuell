#!/usr/bin/env node
/**
 * Merzenich Aktuell - Teaserbilder nachziehen.
 *
 * Karten, die auf einen Artikel verlinken (Weiterlesen-Bloecke, Vereins- und
 * Autorenseiten, Thema-Listen), wurden einmal von Hand oder von aelteren
 * Generatoren geschrieben. Hat der Zielartikel inzwischen ein Foto, zeigt die
 * Karte trotzdem noch die alte gezeichnete Symbolgrafik (gemessen 24.09.:
 * 39 Seiten). Dieses Skript setzt in solchen Karten das aktuelle Bild des
 * Zielartikels ein, samt srcset, Alt-Text und Abmessungen. Die Attribute
 * sizes und loading der Karte bleiben erhalten.
 *
 * Erkannt wird nur das feste Kartenmuster
 *   <a href="/ressort/slug/" ...><div class="media..."><img src="/assets/symbolbilder/...">
 * Die Artikelseite selbst (ihre eigene <figure>) fasst das Skript nicht an.
 *
 * Aufruf: node deploy/teaser-bilder.mjs [--check]
 */
import { readFileSync, writeFileSync, readdirSync, statSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { artikelSammeln, esc } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');

const nachUrl = new Map(artikelSammeln(site).map((a) => [a.url, a]));
const KARTE = /(<a\b[^>]*\bhref="(\/[a-z0-9-]+\/[a-z0-9-]+\/)"[^>]*>\s*<div class="media[^"]*">\s*)(<img\b[^>]*\bsrc="\/assets\/symbolbilder\/[^"]*"[^>]*>)/g;
const attr = (tag, n) => { const m = new RegExp(`\\b${n}="([^"]*)"`).exec(tag); return m ? m[1] : ''; };

function neuesBild(alt, b) {
  const sizes = attr(alt, 'sizes'); const loading = attr(alt, 'loading') || 'lazy';
  return `<img src="${esc(b.src)}"${b.srcset ? ` srcset="${esc(b.srcset)}"` : ''}${sizes ? ` sizes="${sizes}"` : ''} alt="${esc(b.alt)}"${b.width && b.height ? ` width="${b.width}" height="${b.height}"` : ''} loading="${loading}" decoding="async">`;
}

let seiten = 0; let karten = 0; const geaendert = [];
(function lauf(d) {
  for (const e of readdirSync(d)) {
    const p = join(d, e);
    if (statSync(p).isDirectory()) { if (e !== 'admin') lauf(p); continue; }
    if (!e.endsWith('.html')) continue;
    const html = readFileSync(p, 'utf8');
    let n = 0;
    const neu = html.replace(KARTE, (ganz, vor, url, img) => {
      const a = nachUrl.get(url); const b = a && a.bild;
      if (!b || !b.src || /\/assets\/symbolbilder\//.test(b.src)) return ganz;
      n++; return vor + neuesBild(img, b);
    });
    if (neu !== html) {
      seiten++; karten += n; geaendert.push(p.slice(site.length + 1));
      if (!nurPruefen) writeFileSync(p, neu);
    }
  }
})(site);

if (nurPruefen) {
  if (geaendert.length) { console.error(`Teaserbilder: ${karten} veraltete Symbolgrafiken auf ${seiten} Seiten.`); geaendert.slice(0, 20).forEach((x) => console.error('  ' + x)); process.exit(1); }
  console.log('Teaserbilder: aktuell.');
} else {
  console.log(`Teaserbilder: ${karten} Karten auf ${seiten} Seiten nachgezogen.`);
}
