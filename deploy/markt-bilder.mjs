#!/usr/bin/env node
/**
 * Merzenich Aktuell – Bildabgleich fuer Stellen- und Immobilienmarkt.
 * Speichert nur externe Bild-URLs samt Herkunft in market.json. Fremde
 * Bilddateien werden nicht in dieses Repository kopiert.
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const pfad = join(wurzel, 'market.json');
const trocken = process.argv.includes('--dry');
const daten = JSON.parse(readFileSync(pfad, 'utf8'));
const UA = 'MerzenichAktuell-Bildabgleich/1.0 (+https://merzenichaktuell.hk-growthoperator.de)';
const MAX_PARALLEL = 6;

function berlinZeit() {
  const t = Object.fromEntries(new Intl.DateTimeFormat('en-CA', {
    timeZone: 'Europe/Berlin', year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23',
    timeZoneName: 'longOffset'
  }).formatToParts(new Date()).map((p) => [p.type, p.value]));
  const off = (t.timeZoneName || 'GMT+01:00').replace('GMT', '') || '+00:00';
  return `${t.year}-${t.month}-${t.day}T${t.hour}:${t.minute}:${t.second}${off}`;
}

function decodeHtml(s) {
  return String(s || '').replace(/&amp;/gi, '&').replace(/&quot;/gi, '"')
    .replace(/&#39;|&#x27;/gi, "'").replace(/&lt;/gi, '<').replace(/&gt;/gi, '>')
    .replace(/\\u002F/g, '/').replace(/\\\//g, '/');
}
function absUrl(raw, basis) {
  try {
    const u = new URL(decodeHtml(raw), basis);
    return /^https?:$/.test(u.protocol) ? u.href : '';
  } catch { return ''; }
}
function istUnbrauchbar(u) {
  const s = String(u || '').toLowerCase();
  return !u || /favicon|sprite|spacer|pixel|tracking|transparent|placeholder|blank\.|\/icon[-_/]|logo(?:[-_.\/]|$)/i.test(s) || /\.svg(?:\?|$)/i.test(s);
}
function metaWert(html, schluessel) {
  for (const tag of String(html).match(/<meta\b[^>]*>/gi) || []) {
    const prop = /\b(?:property|name)\s*=\s*["']([^"']+)["']/i.exec(tag)?.[1]?.toLowerCase();
    if (prop !== schluessel.toLowerCase()) continue;
    const content = /\bcontent\s*=\s*["']([^"']+)["']/i.exec(tag)?.[1];
    if (content) return decodeHtml(content);
  }
  return '';
}
function immoScoutBild(html, basis) {
  for (const re of [
    /["']obj_picture["']\s*:\s*["']([^"']+)["']/i,
    /obj_picture(?:&quot;|")?\s*:\s*(?:&quot;|")([^"&]+)(?:&quot;|")/i
  ]) {
    const m = re.exec(html);
    if (!m) continue;
    let u = absUrl(m[1], basis);
    if (!u) continue;
    u = u.replace(/\/ORIG\/resize\/[^/]+(?:\/extent\/[^/]+)?\/format\/webp\/quality\/\d+/i, '/ORIG/resize/900x675%3E/format/webp/quality/82')
      .replace(/\/ORIG\/resize\/[^/]+/i, '/ORIG/resize/900x675%3E');
    return u;
  }
  return '';
}
function jsonLdBilder(html, basis) {
  const out = [];
  for (const m of String(html).matchAll(/<script\b[^>]*type=["']application\/ld\+json["'][^>]*>([\s\S]*?)<\/script>/gi)) {
    try {
      const json = JSON.parse(m[1]);
      const queue = Array.isArray(json) ? [...json] : [json];
      while (queue.length) {
        const x = queue.shift();
        if (!x || typeof x !== 'object') continue;
        const imgs = Array.isArray(x.image) ? x.image : x.image ? [x.image] : [];
        for (const img of imgs) {
          const roh = typeof img === 'string' ? img : img?.url || img?.contentUrl;
          const u = absUrl(roh, basis);
          if (u && !istUnbrauchbar(u)) out.push(u);
        }
        if (Array.isArray(x['@graph'])) queue.push(...x['@graph']);
      }
    } catch {}
  }
  return out;
}
function htmlBilder(html, basis) {
  const out = [];
  for (const tag of String(html).match(/<img\b[^>]*>/gi) || []) {
    const src = /\b(?:src|data-src|data-lazy-src)\s*=\s*["']([^"']+)["']/i.exec(tag)?.[1];
    const srcset = /\b(?:srcset|data-srcset)\s*=\s*["']([^"']+)["']/i.exec(tag)?.[1];
    const raw = [];
    if (srcset) for (const part of srcset.split(',')) raw.push(part.trim().split(/\s+/)[0]);
    if (src) raw.push(src);
    for (const x of raw) {
      const u = absUrl(x, basis);
      if (u && !istUnbrauchbar(u)) out.push(u);
    }
  }
  return out;
}
function bestesBild(html, basis, sourceName) {
  if (/immobilienscout24/i.test(sourceName || '') || /immobilienscout24\.de/i.test(basis)) {
    const u = immoScoutBild(html, basis);
    if (u) return { url: u, art: 'original' };
  }
  const meta = [
    metaWert(html, 'og:image:secure_url'), metaWert(html, 'og:image'),
    metaWert(html, 'twitter:image'), metaWert(html, 'twitter:image:src')
  ].map((x) => absUrl(x, basis)).filter((u) => u && !istUnbrauchbar(u));
  const kandidaten = [...meta, ...jsonLdBilder(html, basis), ...htmlBilder(html, basis)];
  if (!kandidaten.length) return null;
  const objekt = kandidaten.find((u) => /pictures\.immobilienscout24\.de|mms\.immowelt\./i.test(u));
  return objekt ? { url: objekt, art: 'original' } : { url: kandidaten[0], art: 'source' };
}
async function abruf(url) {
  const varianten = [url];
  const m = /^https:\/\/www\.immowelt\.de\/expose\/([^/?#]+)/i.exec(url || '');
  if (m) varianten.push(`https://www.immowelt.at/expose/${m[1]}`);
  for (const u of varianten) {
    try {
      const r = await fetch(u, {
        headers: { 'User-Agent': UA, Accept: 'text/html,application/xhtml+xml' },
        redirect: 'follow', signal: AbortSignal.timeout(12000)
      });
      if (!r.ok || !/html|xhtml/i.test(r.headers.get('content-type') || '')) continue;
      return { html: await r.text(), endUrl: r.url || u };
    } catch {}
  }
  return null;
}
async function bildFuer(item) {
  const sourceUrl = String(item.sourceUrl || '');
  if (!/^https?:\/\//i.test(sourceUrl) || /\/suche\b|jobsuche\/suche/i.test(sourceUrl)) return null;
  const seite = await abruf(sourceUrl);
  if (!seite) return null;
  const bild = bestesBild(seite.html, seite.endUrl, item.sourceName);
  if (!bild?.url) return null;
  return {
    imageUrl: bild.url,
    imageKind: bild.art,
    imageSourceUrl: sourceUrl,
    imageCredit: bild.art === 'original'
      ? `Bild: ${item.sourceName || 'Originalquelle'} · Originalanzeige`
      : `Bild/Quelle: ${item.sourceName || 'Originalanzeige'}`
  };
}
async function mapLimit(items, limit, fn) {
  const erg = new Array(items.length);
  let index = 0;
  async function worker() {
    for (;;) {
      const i = index++;
      if (i >= items.length) return;
      erg[i] = await fn(items[i], i);
    }
  }
  await Promise.all(Array.from({ length: Math.min(limit, items.length || 1) }, worker));
  return erg;
}

const jetzt = berlinZeit();
const sammlung = [
  ...(daten.jobs || []).map((item) => ({ gruppe: 'jobs', item })),
  ...(daten.properties || []).map((item) => ({ gruppe: 'properties', item }))
];
let gefunden = 0, behalten = 0, ohne = 0;
await mapLimit(sammlung, MAX_PARALLEL, async ({ gruppe, item }) => {
  const neu = await bildFuer(item);
  if (neu) {
    Object.assign(item, neu, { imageCheckedAt: jetzt });
    gefunden++;
    console.log(`bild       ${gruppe.padEnd(10)} ${item.id} -> ${neu.imageUrl}`);
  } else if (item.imageUrl && /^https?:\/\//i.test(item.imageUrl)) {
    behalten++;
    console.log(`behalten   ${gruppe.padEnd(10)} ${item.id}`);
  } else {
    ohne++;
    delete item.imageUrl; delete item.imageKind; delete item.imageSourceUrl;
    delete item.imageCredit; delete item.imageCheckedAt;
    console.log(`symbolbild ${gruppe.padEnd(10)} ${item.id}`);
  }
});
daten.meta = daten.meta || {};
daten.meta.marketImagesCheckedAt = jetzt;
daten.meta.marketImagesNote = 'Originalbild der konkreten Anzeige, soweit technisch erreichbar; sonst gekennzeichnetes Symbolbild von Merzenich Aktuell.';
console.log(`Marktbilder: ${gefunden} aktualisiert, ${behalten} bestehende behalten, ${ohne} mit Symbolbild-Fallback.`);
if (trocken) console.log('Trockenlauf: market.json nicht geschrieben.');
else { writeFileSync(pfad, JSON.stringify(daten, null, 2) + '\n'); console.log('market.json geschrieben.'); }
