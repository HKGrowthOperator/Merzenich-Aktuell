#!/usr/bin/env node
/**
 * Themenseiten (/thema/<slug>/) aus den Artikel-Tags: fehlende Seiten werden
 * aus der Vorlage /thema/feuerwehr/ erzeugt, bestehende Seiten bekommen neue
 * Artikel als Zeilen nachgetragen (Anzahl in der Kopfzeile wird angepasst).
 * Idempotent. Aufruf: node deploy/thema-prerender.mjs [--check]
 */
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { artikelSammeln, esc, dmyLang, markeHtml } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const site = join(wurzel, 'chatgpt-site');
const vorlage = readFileSync(join(site, 'thema', 'feuerwehr', 'index.html'), 'utf8');

const themen = new Map();
for (const a of artikelSammeln(site)) for (const t of a.themen) {
  if (!themen.has(t.slug)) themen.set(t.slug, { label: t.label, artikel: [] });
  themen.get(t.slug).artikel.push(a);
}
// Bildflaeche wie in den bestehenden Zeilen der Themenseiten: a.feed-img mit
// .media und Badge. Nachgetragene Zeilen standen bis zum 23.09. pauschal auf
// "no-media no-image" - gemessen 34 von 73 Karten auf den Themenseiten ohne
// Bild, obwohl jeder Artikel ein Motiv hat. Ein Vereinslogo oder Wappen fuellt
// keine Bildflaeche (Designstandard 7b), dort bleibt die Zeile textlich.
const nurLogo = (b) => !b || !b.src || b.fit === 'contain' || /logo|wappen/i.test(`${b.badge || ''} ${b.alt || ''} ${b.src}`);
const bildFlaeche = (a) => {
  const b = a.bild;
  if (nurLogo(b)) return '';
  const srcset = b.srcset ? ` srcset="${esc(b.srcset)}"` : '';
  const masse = b.width && b.height ? ` width="${b.width}" height="${b.height}"` : '';
  const badge = b.badge ? `<span class="badge">${esc(b.badge)}</span>` : '';
  return `<a class="feed-img" href="${esc(a.url)}" tabindex="-1" aria-hidden="true"><div class="media">`
    + `<img src="${esc(b.src)}"${srcset} sizes="(max-width: 640px) 120px, 240px" alt="${esc(b.alt || '')}"${masse}`
    + ` loading="lazy" decoding="async" data-editorial-image>${badge}</div></a>`;
};

const zeile = (a) => `<article data-story="${esc(a.id)}" class="feed-row${nurLogo(a.bild) ? ' no-media no-image' : ''}">${bildFlaeche(a)}<div class="feed-copy">${markeHtml(a)}<h3><a href="${esc(a.url)}">${esc(a.titel)}</a></h3><p class="dek">${esc(a.teaser)}</p><div class="meta"><time datetime="${esc(a.datum)}">${esc(a.zeitLabel || dmyLang(a.datum))}</time></div><div class="story-actions"><a class="read-more" href="${esc(a.url)}">Mehr lesen<span class="sr-only">: ${esc(a.titel)}</span></a></div></div></article>`;
const zaehler = (html, n) => html.replace(/<p class="count-line">\d+ Meldung(?:en)?<\/p>/, `<p class="count-line">${n} Meldung${n === 1 ? '' : 'en'}</p>`);

let neuAngelegt = 0, nachgetragen = 0, bebildert = 0;
for (const [slug, { label, artikel }] of themen) {
  const ordner = join(site, 'thema', slug), pfad = join(ordner, 'index.html');
  if (!existsSync(pfad)) {
    const kopf = vorlage.slice(0, vorlage.indexOf('<main'));
    let html = kopf.replace(/Thema: Feuerwehr/g, `Thema: ${esc(label)}`).replace(/zum Thema Feuerwehr/g, `zum Thema ${esc(label)}`).replace(/\/thema\/feuerwehr\//g, `/thema/${slug}/`)
      .replace(/<meta (?:property|name)="(?:og|twitter):image[^"]*" content="[^"]*">\s*/g, '')
      + vorlage.slice(vorlage.indexOf('<main'));
    html = html.replace(/<span aria-current="page">Feuerwehr<\/span>/, `<span aria-current="page">${esc(label)}</span>`).replace(/<h1>Thema: Feuerwehr<\/h1>/, `<h1>Thema: ${esc(label)}</h1>`).replace(/zum Thema Feuerwehr aus der Gemeinde Merzenich/g, `zum Thema ${esc(label)} aus der Gemeinde Merzenich`);
    html = html.replace(/(<div class="feed">)[\s\S]*?(<\/div>\s*<aside class="sidebar">)/, (m, a, z) => `${a}\n      ${artikel.map(zeile).join('\n      ')}\n    ${z}`);
    html = zaehler(html, artikel.length);
    if (!nurPruefen) { mkdirSync(ordner, { recursive: true }); writeFileSync(pfad, html); }
    neuAngelegt++; console.log(`neu: /thema/${slug}/ (${artikel.length})`);
    continue;
  }
  const alt = readFileSync(pfad, 'utf8');
  const feedStart = alt.indexOf('<div class="feed">'), feedEnde = alt.indexOf('<aside class="sidebar">');
  if (feedStart < 0 || feedEnde < 0) continue;
  const feed = alt.slice(feedStart, feedEnde);
  // Bildlose Zeilen nachziehen: bis zum 23.09. bekam jede nachgetragene Zeile
  // "no-media no-image", auch wenn der Artikel ein Motiv hat. Der Schritt
  // ersetzt genau diese Zeilen und laesst handgepflegte Zeilen mit Bild in Ruhe.
  let feedNeu = feed;
  let nachgezogen = 0;
  for (const a of artikel) {
    if (nurLogo(a.bild)) continue;
    const muster = new RegExp(`<article[^>]*data-story="${a.id.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}"[^>]*class="feed-row no-media no-image"[\\s\\S]*?<\\/article>`);
    if (!muster.test(feedNeu)) continue;
    feedNeu = feedNeu.replace(muster, () => zeile(a));
    nachgezogen++;
  }
  // Zeilen, deren Artikel es nicht mehr gibt (geloescht, zusammengefuehrt),
  // fallen heraus; sonst fuehren sie ins Leere.
  let entfernt = 0;
  feedNeu = feedNeu.replace(/\s*<article\b[^>]*>[\s\S]*?<\/article>/g, (block) => {
    const ziel = /<h[23]><a href="(\/[^"#?]+\/)"/.exec(block)?.[1];
    if (!ziel || existsSync(join(site, ziel, 'index.html'))) return block;
    entfernt++; return '';
  });
  if (entfernt) { nachgetragen++; console.log(`bereinigt: /thema/${slug}/ - ${entfernt} Zeile(n) ohne Artikel`); }
  const fehlend = artikel.filter((a) => !feedNeu.includes(`href="${a.url}"`));
  if (entfernt && !fehlend.length && !nachgezogen) {
    const anzahl = new Set([...feedNeu.matchAll(/<h[23]><a href="([^"]+)"/g)].map((m) => m[1])).size;
    if (!nurPruefen) writeFileSync(pfad, zaehler(alt.slice(0, feedStart) + feedNeu + alt.slice(feedEnde), anzahl));
    continue;
  }
  if (nachgezogen && !fehlend.length) {
    const neu = alt.slice(0, feedStart) + feedNeu + alt.slice(feedEnde);
    if (!nurPruefen) writeFileSync(pfad, neu);
    bebildert += nachgezogen; console.log(`bebildert: /thema/${slug}/ ${nachgezogen} Zeile(n)`);
    continue;
  }
  if (nachgezogen) bebildert += nachgezogen;
  if (!fehlend.length) continue;
  const lead = /<\/article>/.exec(feedNeu);
  const einfuegen = '\n      ' + fehlend.map(zeile).join('\n      ');
  const neuerFeed = lead && feedNeu.includes('class="feed-lead"') ? feedNeu.slice(0, lead.index + lead[0].length) + einfuegen + feedNeu.slice(lead.index + lead[0].length) : feedNeu.replace('<div class="feed">', '<div class="feed">' + einfuegen);
  const anzahl = new Set([...neuerFeed.matchAll(/<h[23]><a href="([^"]+)"/g)].map((m) => m[1])).size;
  const neu = zaehler(alt.slice(0, feedStart) + neuerFeed + alt.slice(feedEnde), anzahl);
  if (!nurPruefen) writeFileSync(pfad, neu);
  nachgetragen++; console.log(`nachgetragen: /thema/${slug}/ + ${fehlend.map((a) => a.url).join(', ')}`);
}
// Themenuebersicht /thema/: Wolke nur fuer Themen mit mindestens zwei Beitraegen
// (sonst liest sie sich wie eine CMS-Taxonomie), darunter alle Schlagworte A-Z.
let uebersicht = 0;
{
  const pfad = join(site, 'thema', 'index.html');
  if (existsSync(pfad)) {
    const alt = readFileSync(pfad, 'utf8'); let neu = alt;
    const sortiert = [...themen.entries()].sort((a, b) => b[1].artikel.length - a[1].artikel.length || a[1].label.localeCompare(b[1].label, 'de'));
    const wolke = sortiert.filter(([, t]) => t.artikel.length >= 2).map(([slug, t]) => `<a href="/thema/${slug}/" style="--n:${Math.min(t.artikel.length, 12)}">${esc(t.label)} <small>${t.artikel.length}</small></a>`).join('');
    const az = [...themen.entries()].sort((a, b) => a[1].label.localeCompare(b[1].label, 'de')).map(([slug, t]) => `<li><a href="/thema/${slug}/">${esc(t.label)}</a> <small>${t.artikel.length}</small></li>`).join('');
    const azBlock = `<div class="tag-az"><h2>Alle Schlagworte von A bis Z</h2><ul>${az}</ul></div>`;
    neu = neu.replace(/<div class="tagcloud">[\s\S]*?<\/div>/, () => `<div class="tagcloud">${wolke}</div>`);
    if (/<div class="tag-az">[\s\S]*?<\/ul><\/div>/.test(neu)) neu = neu.replace(/<div class="tag-az">[\s\S]*?<\/ul><\/div>/, () => azBlock);
    else neu = neu.replace(/(<div class="tagcloud">[\s\S]*?<\/div>)/, (m) => m + azBlock);
    neu = neu.replace(/<p class="desc">[^<]*<\/p>/, '<p class="desc">Die wichtigsten Themen aus allen Meldungen. Je größer, desto mehr Beiträge. Darunter alle Schlagworte von A bis Z.</p>');
    if (neu !== alt) { uebersicht = 1; if (!nurPruefen) writeFileSync(pfad, neu); }
  }
}
console.log(`Themen: ${themen.size} Tags, Uebersicht ${uebersicht ? (nurPruefen ? 'nicht aktuell' : 'aktualisiert') : 'aktuell'}, ${neuAngelegt} Seiten ${nurPruefen ? 'fehlen' : 'neu'}, ${nachgetragen} Seiten ${nurPruefen ? 'nicht aktuell' : 'nachgetragen'}, ${bebildert} Zeile(n) ${nurPruefen ? 'ohne Bild' : 'bebildert'}.`);
if (nurPruefen && (neuAngelegt || nachgetragen || uebersicht || bebildert)) process.exit(2);
