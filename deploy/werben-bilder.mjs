#!/usr/bin/env node
/**
 * Materialisiert die kuratierten Bilder fuer /werben/ aus Wikimedia Commons.
 *
 * Schreibmodus:
 *   node deploy/werben-bilder.mjs
 *
 * Check (offline):
 *   node deploy/werben-bilder.mjs --check
 *
 * Die Bildauswahl ist bewusst separat vom redaktionellen Fallback-Pool:
 * Werbemotive muessen das beworbene Format sofort erklaeren und duerfen nicht
 * zufaellig aus einem Nachrichtenressort stammen.
 */
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, extname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const cfgPath = join(root, 'deploy', 'werben-bilder.json');
const outDir = join(root, 'chatgpt-site', 'assets', 'werben');
const creditsPath = join(outDir, 'credits.json');
const check = process.argv.includes('--check');
const cfg = JSON.parse(readFileSync(cfgPath, 'utf8'));
const erlaubteLizenz = /^(CC0|Public domain|CC BY(?:-| )|CC BY-SA)/i;

function cleanHtml(s='') {
  return String(s).replace(/<[^>]+>/g, ' ').replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/\s+/g, ' ').trim();
}
function extFromUrl(url, fallback='.jpg') {
  try {
    const p = new URL(url).pathname.toLowerCase();
    const e = extname(p);
    return ['.jpg','.jpeg','.png','.webp'].includes(e) ? e : fallback;
  } catch { return fallback; }
}
async function commonsInfo(sourceTitle) {
  const qs = new URLSearchParams({
    action: 'query',
    format: 'json',
    origin: '*',
    prop: 'imageinfo',
    titles: sourceTitle,
    iiprop: 'url|extmetadata|size',
    iiurlwidth: '1600'
  });
  const r = await fetch('https://commons.wikimedia.org/w/api.php?' + qs, {
    headers: { 'User-Agent': 'MerzenichAktuell-WerbenBildmaterialisierung/1.0 (https://merzenichaktuell.hk-growthoperator.de)' }
  });
  if (!r.ok) throw new Error(sourceTitle + ': Commons API ' + r.status);
  const j = await r.json();
  const page = Object.values(j.query?.pages || {})[0];
  const ii = page?.imageinfo?.[0];
  if (!page || page.missing !== undefined || !ii) throw new Error(sourceTitle + ': Datei nicht gefunden');
  const m = ii.extmetadata || {};
  const license = cleanHtml(m.LicenseShortName?.value || '');
  const author = cleanHtml(m.Artist?.value || m.Credit?.value || 'Urheber siehe Wikimedia Commons');
  const desc = cleanHtml(m.ImageDescription?.value || '');
  const licenseUrl = m.LicenseUrl?.value || '';
  const sourceUrl = 'https://commons.wikimedia.org/wiki/' + encodeURIComponent(sourceTitle.replace(/^File:/, 'File:')).replace(/%2F/g, '/');
  const download = ii.thumburl || ii.url;
  return { license, author, desc, licenseUrl, sourceUrl, download, width: ii.thumbwidth || ii.width, height: ii.thumbheight || ii.height };
}

function assertOffline() {
  const credits = existsSync(creditsPath) ? JSON.parse(readFileSync(creditsPath, 'utf8')) : null;
  const errs = [];
  if (!credits || !Array.isArray(credits.images)) errs.push('credits.json fehlt oder ist ungueltig');
  for (const item of cfg.images) {
    const p = join(outDir, item.output);
    if (!existsSync(p)) errs.push(item.output + ' fehlt');
    const c = credits?.images?.find(x => x.id === item.id);
    if (!c) errs.push(item.id + ': Credit fehlt');
    else {
      if (c.sourceTitle !== item.sourceTitle) errs.push(item.id + ': Quelle stimmt nicht');
      if (!erlaubteLizenz.test(c.license || '')) errs.push(item.id + ': Lizenz nicht freigegeben');
    }
  }
  if (errs.length) {
    console.error('Werben-Bilder: ' + errs.join('\n  '));
    process.exit(2);
  }
  console.log('Werben-Bilder: ' + cfg.images.length + ' kuratierte Assets vorhanden; Credits und Lizenzen geprueft.');
}

if (check) {
  assertOffline();
  process.exit(0);
}

mkdirSync(outDir, { recursive: true });
const credits = {
  version: cfg.version,
  generated: new Date().toISOString(),
  rightsCheckedAt: cfg.rightsCheckedAt,
  note: cfg.note,
  images: []
};

for (const item of cfg.images) {
  const info = await commonsInfo(item.sourceTitle);
  if (!erlaubteLizenz.test(info.license)) throw new Error(item.sourceTitle + ': nicht freigegebene Lizenz ' + info.license);
  if (item.expectedLicense && !info.license.toLowerCase().includes(item.expectedLicense.toLowerCase().replace('public domain','public domain'))) {
    // Nur warnen: Commons benennt z.B. CC0 als "CC0 1.0".
    console.warn(item.id + ': Lizenz laut Commons "' + info.license + '", erwartet "' + item.expectedLicense + '"');
  }
  const r = await fetch(info.download, {
    headers: { 'User-Agent': 'MerzenichAktuell-WerbenBildmaterialisierung/1.0 (https://merzenichaktuell.hk-growthoperator.de)' }
  });
  if (!r.ok) throw new Error(item.sourceTitle + ': Bilddownload ' + r.status);
  const buf = Buffer.from(await r.arrayBuffer());
  if (buf.length < 20000) throw new Error(item.sourceTitle + ': Bilddatei unerwartet klein (' + buf.length + ' Bytes)');
  writeFileSync(join(outDir, item.output), buf);
  credits.images.push({
    id: item.id,
    src: '/assets/werben/' + item.output,
    alt: item.alt,
    purpose: item.purpose,
    source: 'Wikimedia Commons',
    sourceTitle: item.sourceTitle,
    sourceUrl: info.sourceUrl,
    author: info.author,
    license: info.license,
    licenseUrl: info.licenseUrl,
    rightsCheckedAt: cfg.rightsCheckedAt,
    width: info.width,
    height: info.height,
    description: info.desc
  });
  console.log('Werben-Bild: ' + item.id + ' <- ' + item.sourceTitle + ' (' + info.license + ', ' + Math.round(buf.length/1024) + ' KB)');
}
writeFileSync(creditsPath, JSON.stringify(credits, null, 2) + '\n');
assertOffline();
