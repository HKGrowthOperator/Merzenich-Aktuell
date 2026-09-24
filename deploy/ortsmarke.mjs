#!/usr/bin/env node
/**
 * Merzenich Aktuell - Ortsmarke (Anhang A3.1) auf allen Karten.
 *
 * Karten, die auf eine Meldung verlinken, trugen bisher zwei Zeilen:
 *   <div class="location-line"><span class="location-brand">MERZENICH</span> · GOLZHEIM</div>
 *   <span class="kicker">Kreisliga A</span>
 * Die Startseite zeigt seit 23.09. stattdessen eine Ortsmarke
 * (markeHtml in lib-artikel.mjs). Generatoren schreiben sie inzwischen selbst;
 * dieses Skript zieht Karten nach, die von Hand oder von aelteren
 * Generatoren stammen: Thema-Aufmacher, Weiterlesen-Bloecke unter Artikeln,
 * Vereins- und Autorenseiten.
 *
 * Erkannt wird eine Karte als <article ...>...</article> mit einer
 * Ueberschrift <h2|h3><a href="/ressort/slug/">. Der Artikelkopf selbst
 * (<article class="article"> mit <h1>) bleibt unberuehrt: dort liest
 * lib-artikel.mjs Ort und Dachzeile aus, und korrekturen.css setzt ihn im
 * Stil der Ortsmarke.
 *
 * Aufruf: node deploy/ortsmarke.mjs [--check]
 */
import { readFileSync, writeFileSync, readdirSync, statSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { artikelSammeln, markeHtml } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');

const nachUrl = new Map(artikelSammeln(site).map((a) => [a.url, a]));
const KARTE = /<article\b(?![^>]*class="article")[^>]*>[\s\S]*?<\/article>/g;
const ZIEL = /<h[23][^>]*>\s*<a href="(\/[a-z0-9-]+\/[a-z0-9-]+\/)"/;
const ALT = /<div class="location-line">[\s\S]*?<\/div>(\s*<span class="kicker">[\s\S]*?<\/span>)?/;

let seiten = 0; let karten = 0; const geaendert = [];
(function lauf(d) {
  for (const e of readdirSync(d)) {
    const p = join(d, e);
    if (statSync(p).isDirectory()) { if (e !== 'admin') lauf(p); continue; }
    if (!e.endsWith('.html')) continue;
    const html = readFileSync(p, 'utf8');
    let n = 0;
    const neu = html.replace(KARTE, (karte) => {
      const z = ZIEL.exec(karte); const a = z && nachUrl.get(z[1]);
      if (!a || !ALT.test(karte)) return karte;
      n++; return karte.replace(ALT, () => markeHtml(a));
    });
    if (neu !== html) {
      seiten++; karten += n; geaendert.push(p.slice(site.length + 1));
      if (!nurPruefen) writeFileSync(p, neu);
    }
  }
})(site);

if (nurPruefen) {
  if (geaendert.length) { console.error(`Ortsmarke: ${karten} Karten auf ${seiten} Seiten mit alter Ortszeile.`); geaendert.slice(0, 20).forEach((x) => console.error('  ' + x)); process.exit(1); }
  console.log('Ortsmarke: aktuell.');
} else {
  console.log(`Ortsmarke: ${karten} Karten auf ${seiten} Seiten nachgezogen.`);
}
