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
 *   <a href="/ressort/slug/" ...><div class="media..."><img src="/assets/(symbolbilder|editorial-pools)/...">
 * Seit den Motivregeln (24.09.) gilt es auch fuer Commons-Poolfotos: Die Karte
 * zeigt immer das Bild, das der Zielartikel traegt. Hat der Artikel kein
 * passendes Motiv und damit kein Bild, faellt die Bildflaeche der Karte weg.
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
// Karte mit Poolbild (gezeichnete Grafik oder Commons-Foto): Link auf einen
// Artikel, darin .media mit dem Bild, optional Badge, dann </div></a>.
const KARTE = /(<a\b[^>]*\bhref="(\/[a-z0-9-]+\/[a-z0-9-]+\/)"[^>]*>\s*<div class="media[^"]*">\s*)(<img\b[^>]*\bsrc="(\/assets\/(?:symbolbilder|editorial-pools)\/[^"]*)"[^>]*>)([\s\S]*?<\/div>\s*<\/a>)/g;
const attr = (tag, n) => { const m = new RegExp(`\\b${n}="([^"]*)"`).exec(tag); return m ? m[1] : ''; };
const istLogo = (b) => !b || !b.src || b.fit === 'contain' || /logo|wappen/i.test(`${b.badge || ''} ${b.alt || ''} ${b.src}`);

function neuesBild(alt, b) {
  const sizes = attr(alt, 'sizes'); const loading = attr(alt, 'loading') || 'lazy';
  return `<img src="${esc(b.src)}"${b.srcset ? ` srcset="${esc(b.srcset)}"` : ''}${sizes ? ` sizes="${sizes}"` : ''} alt="${esc(b.alt)}"${b.width && b.height ? ` width="${b.width}" height="${b.height}"` : ''} loading="${loading}" decoding="async">`;
}

// Hat der Zielartikel kein Bild mehr (Motivregeln: kein passendes Motiv),
// faellt die Bildflaeche der Karte weg; eine Listenzeile wird zur Textzeile.
function ohneBild(html, start, ende) {
  let neu = html.slice(0, start) + html.slice(ende);
  const artikelStart = neu.lastIndexOf('<article', start);
  if (artikelStart >= 0 && neu.lastIndexOf('</article>', start) < artikelStart) {
    const kopfEnde = neu.indexOf('>', artikelStart);
    const kopf = neu.slice(artikelStart, kopfEnde);
    const neuKopf = kopf.replace(/class="feed-row"/, 'class="feed-row no-media no-image"');
    neu = neu.slice(0, artikelStart) + neuKopf + neu.slice(kopfEnde);
  }
  return neu;
}

let seiten = 0; let karten = 0; let entfernt = 0; const geaendert = [];
(function lauf(d) {
  for (const e of readdirSync(d)) {
    const p = join(d, e);
    if (statSync(p).isDirectory()) { if (e !== 'admin') lauf(p); continue; }
    if (!e.endsWith('.html')) continue;
    const html = readFileSync(p, 'utf8');
    let neu = html; let n = 0;
    // Von hinten nach vorn, damit Positionen beim Entfernen gueltig bleiben.
    const treffer = [...html.matchAll(KARTE)].reverse();
    for (const m of treffer) {
      const [ganz, vor, url, img, src, rest] = m;
      const a = nachUrl.get(url); if (!a) continue;
      const b = a.bild;
      if (istLogo(b)) { neu = ohneBild(neu, m.index, m.index + ganz.length); n++; entfernt++; continue; }
      if (b.src === src) continue;
      neu = neu.slice(0, m.index) + vor + neuesBild(img, b) + rest + neu.slice(m.index + ganz.length); n++;
    }
    if (neu !== html) {
      seiten++; karten += n; geaendert.push(p.slice(site.length + 1));
      if (!nurPruefen) writeFileSync(p, neu);
    }
  }
})(site);

if (nurPruefen) {
  if (geaendert.length) { console.error(`Teaserbilder: ${karten} Karten mit veraltetem Bild auf ${seiten} Seiten.`); geaendert.slice(0, 20).forEach((x) => console.error('  ' + x)); process.exit(1); }
  console.log('Teaserbilder: aktuell.');
} else {
  console.log(`Teaserbilder: ${karten} Karten auf ${seiten} Seiten nachgezogen, davon ${entfernt} ohne Bild.`);
}
