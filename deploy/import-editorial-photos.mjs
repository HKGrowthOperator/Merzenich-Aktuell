#!/usr/bin/env node
/**
 * Merzenich Aktuell — echte Foto-Pools für Editorial Image System V2.
 *
 * Quelle: ausschließlich Wikimedia Commons (freie Lizenzen, kommerzielle
 * Nutzung und Bearbeitung zulässig). Zuerst werden Merzenich/Kreis Düren/NRW
 * gesucht, danach erst allgemeine deutsche Motive.
 *
 * Je Pool werden exakt 20 verschiedene Fotos lokal materialisiert.
 * Die Original-Dateiseite, Lizenz, Urheber und Download-URL bleiben im
 * Manifest nachvollziehbar. Für die Website wird eine max. 1600px breite
 * Commons-Ableitung gespeichert, damit das Repository nicht mit mehreren
 * hundert Megabyte Originaldateien explodiert.
 */
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, extname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const SITE = join(ROOT, 'chatgpt-site');
const ASSET_ROOT = join(SITE, 'assets', 'editorial-pools');
const MANIFEST = join(SITE, 'data', 'editorial-images', 'editorial-photo-pools.json');
const PRO_POOL = 20;
const REFRESH = process.argv.includes('--refresh');
const HEUTE = new Date().toISOString().slice(0, 10);
const UA = 'MerzenichAktuell-EditorialImageImporter/2.1 (https://merzenichaktuell.hk-growthoperator.de)';

const POOLS = {
  aktuell: {
    tags: ['aktuell','merzenich','dueren','ort','strasse','gemeinde'],
    queries: ['Merzenich Germany', 'Merzenich Kreis Düren', 'Düren Nordrhein-Westfalen streets', 'Kreis Düren village']
  },
  blaulicht: {
    tags: ['blaulicht','einsatz','rettung','notfall'],
    queries: ['Blaulicht Düren', 'Rettungsdienst Nordrhein-Westfalen', 'Einsatzfahrzeug Deutschland', 'emergency vehicle Germany']
  },
  polizei: {
    tags: ['polizei','streifenwagen','einsatz','kontrolle'],
    queries: ['Polizei Düren', 'Polizei Nordrhein-Westfalen', 'Polizeiauto Deutschland', 'German police car']
  },
  feuerwehr: {
    tags: ['feuerwehr','loeschfahrzeug','einsatz','rettung'],
    queries: ['Feuerwehr Düren', 'Feuerwehr Nordrhein-Westfalen', 'Feuerwehr Deutschland', 'German fire brigade']
  },
  brand: {
    tags: ['brand','feuer','rauch','loeschen'],
    queries: ['Feuerwehr Brand Deutschland', 'Gebäudebrand Deutschland', 'Hausbrand Feuerwehr', 'fire smoke Germany']
  },
  sport: {
    tags: ['sport','fussball','amateur','spiel','platz'],
    queries: ['Fußball Düren', 'Amateurfußball Nordrhein-Westfalen', 'Fußball Kreisliga Deutschland', 'football pitch Germany']
  },
  termine: {
    tags: ['termine','veranstaltung','event','fest','markt'],
    queries: ['Dorffest Nordrhein-Westfalen', 'Kirmes Nordrhein-Westfalen', 'Veranstaltung Deutschland', 'Weihnachtsmarkt Nordrhein-Westfalen', 'Konzert Deutschland']
  },
  vereine: {
    tags: ['vereine','verein','ehrenamt','gemeinschaft'],
    queries: ['Verein Düren', 'Ehrenamt Nordrhein-Westfalen', 'Schützenverein Nordrhein-Westfalen', 'Karnevalsverein Nordrhein-Westfalen', 'volunteers Germany']
  },
  leben: {
    tags: ['leben','dorfleben','alltag','nachbarschaft','familie'],
    queries: ['Merzenich Germany', 'Dorfleben Nordrhein-Westfalen', 'Nachbarschaft Deutschland', 'family park Germany', 'village life Germany']
  },
  wirtschaft: {
    tags: ['wirtschaft','handel','strukturwandel','gewerbe','handwerk','industrie'],
    queries: ['Tagebau Hambach', 'Wirtschaft Düren', 'Handwerk Nordrhein-Westfalen', 'Einzelhandel Deutschland', 'Industrie Nordrhein-Westfalen', 'Gewerbe Deutschland']
  },
  tipp: {
    tags: ['tipp','freizeit','ausflug','wandern','radfahren','natur'],
    queries: ['Freizeit Düren', 'Wandern Kreis Düren', 'Radweg Nordrhein-Westfalen', 'Natur Kreis Düren', 'hiking Nordrhein-Westfalen']
  },
  menschen: {
    tags: ['menschen','portraet','community','senioren','handwerk'],
    queries: ['Menschen Nordrhein-Westfalen', 'people Germany community', 'Senioren Deutschland', 'Handwerker Deutschland', 'portrait Germany']
  }
};

const BAD_TITLE = /(logo|wappen|coat of arms|flag|karte|map of|locator|diagram|poster|plakat|flyer|icon|scan|seite \d|page \d|screenshot|symbol|svg)/i;
const ALLOWED_MIME = new Set(['image/jpeg', 'image/png', 'image/webp']);

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const hash = (x) => createHash('sha256').update(x).digest('hex');
const stripHtml = (s = '') => String(s)
  .replace(/<br\s*\/?>/gi, ' ')
  .replace(/<[^>]+>/g, ' ')
  .replace(/&nbsp;/g, ' ')
  .replace(/&amp;/g, '&')
  .replace(/&quot;/g, '"')
  .replace(/&#039;|&apos;/g, "'")
  .replace(/\s+/g, ' ')
  .trim();

function licenceAllowed(name = '') {
  const s = String(name);
  if (/NC|ND/i.test(s)) return false;
  return /CC0|CC BY(?:-SA)?|Public domain|PDM|Public Domain/i.test(s);
}

function extensionFor(info) {
  if (info.mime === 'image/jpeg') return 'jpg';
  if (info.mime === 'image/png') return 'png';
  if (info.mime === 'image/webp') return 'webp';
  const e = extname(new URL(info.thumburl || info.url).pathname).toLowerCase().replace('.', '');
  return e === 'jpeg' ? 'jpg' : e;
}

function localityFor(text) {
  const s = String(text).toLocaleLowerCase('de-DE');
  if (s.includes('merzenich') || s.includes('golzheim') || s.includes('girbelsrath') || s.includes('morschenich') || s.includes('bürgewald')) return 'Merzenich';
  if (s.includes('düren') || s.includes('dueren')) return 'Kreis Düren';
  if (s.includes('nordrhein-westfalen') || s.includes('north rhine-westphalia') || s.includes(' nrw')) return 'NRW';
  return 'Deutschland / überregional';
}

async function commonsSearch(query) {
  const params = new URLSearchParams({
    action: 'query',
    format: 'json',
    generator: 'search',
    gsrsearch: query,
    gsrnamespace: '6',
    gsrlimit: '50',
    prop: 'imageinfo',
    iiprop: 'url|mime|size|extmetadata',
    iiurlwidth: '1600'
  });
  const url = 'https://commons.wikimedia.org/w/api.php?' + params.toString();
  const res = await fetch(url, { headers: { 'User-Agent': UA, 'Accept': 'application/json' } });
  if (!res.ok) throw new Error('Commons search ' + res.status + ' for ' + query);
  const json = await res.json();
  return Object.values(json?.query?.pages || {}).map((page) => {
    const info = page.imageinfo?.[0];
    const meta = info?.extmetadata || {};
    return {
      pageid: page.pageid,
      title: page.title,
      info,
      license: stripHtml(meta.LicenseShortName?.value || ''),
      licenseUrl: meta.LicenseUrl?.value || '',
      artist: stripHtml(meta.Artist?.value || meta.Credit?.value || ''),
      description: stripHtml(meta.ImageDescription?.value || meta.ObjectName?.value || ''),
      categories: stripHtml(meta.Categories?.value || '')
    };
  });
}

function usable(c) {
  if (!c?.info || !ALLOWED_MIME.has(c.info.mime)) return false;
  if (BAD_TITLE.test(c.title)) return false;
  if (!licenceAllowed(c.license)) return false;
  if ((c.info.width || 0) < 800 || (c.info.height || 0) < 600) return false;
  if (!c.info.thumburl && !c.info.url) return false;
  return true;
}

async function download(candidate, pool, index) {
  const url = candidate.info.thumburl || candidate.info.url;
  const res = await fetch(url, { headers: { 'User-Agent': UA } });
  if (!res.ok) throw new Error('image ' + res.status);
  const bytes = Buffer.from(await res.arrayBuffer());
  if (bytes.length < 10_000 || bytes.length > 18_000_000) throw new Error('unexpected image size ' + bytes.length);
  const ext = extensionFor(candidate.info);
  if (!['jpg','png','webp'].includes(ext)) throw new Error('unsupported extension ' + ext);
  const file = pool + '-' + String(index).padStart(2, '0') + '-' + hash(candidate.title).slice(0, 10) + '.' + ext;
  const dir = join(ASSET_ROOT, pool);
  mkdirSync(dir, { recursive: true });
  const abs = join(dir, file);
  writeFileSync(abs, bytes);

  const titleClean = candidate.title.replace(/^File:/, '').replace(/\.[^.]+$/, '').replace(/_/g, ' ');
  const alt = (candidate.description || titleClean).slice(0, 180);
  const artist = candidate.artist || 'Urheber siehe Quelldatei';
  const sourceUrl = 'https://commons.wikimedia.org/wiki/' + encodeURIComponent(candidate.title.replace(/ /g, '_'));
  const textForLocality = [candidate.title, candidate.description, candidate.categories].join(' ');
  const cfg = POOLS[pool];

  return {
    id: 'web-' + pool + '-' + String(index).padStart(2, '0') + '-' + hash(candidate.title).slice(0, 8),
    pool,
    src: '/assets/editorial-pools/' + pool + '/' + file,
    alt,
    credit: 'Symbolbild · ' + artist + ' / Wikimedia Commons · ' + candidate.license,
    source: 'Wikimedia Commons',
    sourceUrl,
    sourceTitle: candidate.title,
    downloadUrl: url,
    license: candidate.license,
    licenseUrl: candidate.licenseUrl,
    rightsCheckedAt: HEUTE,
    tags: [...new Set([...cfg.tags, ...titleClean.toLocaleLowerCase('de-DE').split(/[^a-zäöüß0-9]+/i).filter((x) => x.length >= 5).slice(0, 8)])],
    width: candidate.info.thumbwidth || candidate.info.width,
    height: candidate.info.thumbheight || candidate.info.height,
    format: ext,
    symbol: true,
    photo: true,
    locality: localityFor(textForLocality),
    checksum: hash(bytes)
  };
}

function loadExisting() {
  if (!existsSync(MANIFEST)) return { images: [] };
  try {
    const x = JSON.parse(readFileSync(MANIFEST, 'utf8'));
    return { images: Array.isArray(x.images) ? x.images : [] };
  } catch {
    return { images: [] };
  }
}

function validExisting(list, pool) {
  const own = list.filter((x) => x.pool === pool);
  return own.length >= PRO_POOL && own.slice(0, PRO_POOL).every((x) => existsSync(join(SITE, String(x.src || '').replace(/^\//, ''))));
}

const existing = loadExisting();
const finalImages = [];
const usedTitles = new Set();

for (const pool of Object.keys(POOLS)) {
  if (!REFRESH && validExisting(existing.images, pool)) {
    const keep = existing.images.filter((x) => x.pool === pool).slice(0, PRO_POOL);
    keep.forEach((x) => x.sourceTitle && usedTitles.add(x.sourceTitle));
    finalImages.push(...keep);
    console.log(pool + ': vorhandene 20 Fotos bleiben bestehen');
    continue;
  }

  const chosen = [];
  for (const query of POOLS[pool].queries) {
    let candidates = [];
    try {
      candidates = await commonsSearch(query);
    } catch (e) {
      console.warn(pool + ': Suche fehlgeschlagen (' + query + '): ' + e.message);
      continue;
    }
    for (const candidate of candidates) {
      if (chosen.length >= PRO_POOL) break;
      if (!usable(candidate) || usedTitles.has(candidate.title) || chosen.some((x) => x.title === candidate.title)) continue;
      chosen.push(candidate);
    }
    if (chosen.length >= PRO_POOL) break;
    await sleep(180);
  }

  if (chosen.length < PRO_POOL) throw new Error(pool + ': nur ' + chosen.length + ' geeignete Commons-Fotos gefunden, benötigt ' + PRO_POOL);

  let index = 1;
  for (const candidate of chosen) {
    let record;
    try {
      record = await download(candidate, pool, index);
    } catch (e) {
      console.warn(pool + ': Download verworfen ' + candidate.title + ': ' + e.message);
      continue;
    }
    finalImages.push(record);
    usedTitles.add(candidate.title);
    index++;
    await sleep(120);
  }

  const n = finalImages.filter((x) => x.pool === pool).length;
  if (n < PRO_POOL) throw new Error(pool + ': nach Downloads nur ' + n + ' gültige Fotos');
  console.log(pool + ': ' + n + ' Fotos importiert');
}

mkdirSync(dirname(MANIFEST), { recursive: true });
const counts = Object.fromEntries(Object.keys(POOLS).map((p) => [p, finalImages.filter((x) => x.pool === p).length]));
writeFileSync(MANIFEST, JSON.stringify({
  version: 1,
  generated: HEUTE,
  source: 'Wikimedia Commons',
  minimumPerPool: PRO_POOL,
  pools: counts,
  images: finalImages
}, null, 2) + '\n');

console.log('Editorial photo pools:', counts);
console.log('Total photos:', finalImages.length);
