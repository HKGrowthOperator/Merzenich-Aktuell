#!/usr/bin/env node
/**
 * Merzenich Aktuell: Read-only Fotopool-Audit.
 * Zählt Fotos, nicht WebP-Varianten oder SVGs. Ungeprüfte Kandidaten
 * sind kein fertig nutzbarer Bildpool.
 *
 * node deploy/editorial-photo-audit.mjs
 * node deploy/editorial-photo-audit.mjs --json
 * node deploy/editorial-photo-audit.mjs --check    # Integritätsprüfung
 * node deploy/editorial-photo-audit.mjs --strict   # 20 freigegebene je Pool
 */
import { readFileSync, existsSync } from 'node:fs';
import { resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const repo = resolve(fileURLToPath(new URL('..', import.meta.url)));
const read = (path) => JSON.parse(readFileSync(resolve(repo, path), 'utf8'));
const { pools = {}, images = [] } = read('chatgpt-site/data/editorial-images/editorial-photo-pools.json');
const review = read('deploy/editorial-photo-review.json');
const motive = read('deploy/bildmotive.json').motive || {};
const freigegeben = new Set(review.freigegeben || []);
const ausgeschlossen = new Set((review.ausgeschlossen || []).map((x) => x.titel));
const MIN = 20;
const error = [];
const warn = [];
const seenIds = new Set();
const seenSources = new Set();
const seenChecksums = new Set();

const canonical = (image) => {
  if (!/^\/assets\/editorial-pools\/[a-z0-9-]+\/.+\.(?:jpe?g|png|webp)$/i.test(image.src || '')) return false;
  return existsSync(resolve(repo, 'chatgpt-site', image.src.slice(1)));
};
function licenceAllowed(license = '') {
  const l = String(license);
  return !/(?:\bNC\b|\bND\b|BY-NC|BY-ND)/i.test(l) && /^(?:CC0|CC BY(?:-SA)?(?:\s|$)|Public domain|Public Domain|PDM)/i.test(l);
}
const grouped = new Map(Object.keys(pools).map((k) => [k, []]));
for (const im of images) {
  if (!grouped.has(im.pool)) { error.push(`Unbekannter Pool: ${im.pool} / ${im.id}`); continue; }
  grouped.get(im.pool).push(im);
  for (const required of ['id','pool','src','alt','credit','source','sourceTitle','sourceUrl','license','rightsCheckedAt','checksum']) {
    if (!im[required]) error.push(`${im.id}: ${required} fehlt`);
  }
  if (!Array.isArray(im.tags) || !im.tags.length) error.push(`${im.id}: Tags fehlen`);
  if (!canonical(im)) error.push(`${im.id}: Lokale Originaldatei fehlt`);
  if (!licenceAllowed(im.license)) error.push(`${im.id}: Lizenz ist nicht ausreichend nachgewiesen (${im.license})`);
  if (!/^https:\/\/commons\.wikimedia\.org\/wiki\/File/i.test(im.sourceUrl || '')) warn.push(`${im.id}: nicht bestätigter Commons-Link`);
  if (seenIds.has(im.id)) error.push(`Doppelte ID: ${im.id}`);
  if (im.sourceTitle && seenSources.has(im.sourceTitle)) error.push(`Doppelte Quelle: ${im.sourceTitle}`);
  if (im.checksum && seenChecksums.has(im.checksum)) error.push(`Bilddatei-Dublette: ${im.checksum}`);
  seenIds.add(im.id);
  seenSources.add(im.sourceTitle);
  seenChecksums.add(im.checksum);
  if (ausgeschlossen.has(im.sourceTitle)) error.push(`Ausgeschlossenes Foto weiterhin im Pool: ${im.sourceTitle}`);
  if (im.geprueft && !freigegeben.has(im.sourceTitle)) error.push(`Freigabe nicht nachvollziehbar: ${im.sourceTitle}`);
}
const perPool = [...grouped].map(([pool, poolImages]) => {
  const approved = poolImages.filter((x) => x.geprueft === true && freigegeben.has(x.sourceTitle));
  const mapped = approved.filter((x) => Boolean(motive[x.sourceTitle]));
  return {
    pool,
    fotos: poolImages.length,
    geprueft: approved.length,
    motivZugeordnet: mapped.length,
    offenFoto: Math.max(0, MIN - poolImages.length),
    offenFreigabe: Math.max(0, MIN - approved.length),
    ungeprueft: poolImages.length - approved.length
  };
});
for (const x of perPool) {
  if (x.fotos !== pools[x.pool]) error.push(`${x.pool}: Manifestzahl ${pools[x.pool]}, vorhanden ${x.fotos}`);
  if (x.fotos < MIN) warn.push(`${x.pool}: ${MIN - x.fotos} echte Fotos fehlen`);
}
const summary = {
  datenstand: new Date().toISOString(),
  pools: perPool.length,
  minimum: MIN,
  sollFotos: MIN * perPool.length,
  istFotos: images.length,
  redaktionellGeprueft: perPool.reduce((n,x)=>n+x.geprueft,0),
  poolsMit20Fotos: perPool.filter(x=>x.fotos>=MIN).length,
  poolsMit20Freigaben: perPool.filter(x=>x.geprueft>=MIN).length,
  fehlendeFotos: perPool.reduce((n,x)=>n+x.offenFoto,0),
  fehlendeFreigaben: perPool.reduce((n,x)=>n+x.offenFreigabe,0),
  perPool, error, warn
};
if (process.argv.includes('--json')) {
  console.log(JSON.stringify(summary, null, 2));
} else {
  console.log('MERZENICH AKTUELL — FOTOPOOL-CHECK');
  console.log('Pool'.padEnd(21) + 'Dateien'.padStart(9) + 'Freigaben'.padStart(12) + 'Motiv'.padStart(8) + 'Fehlen'.padStart(9));
  for (const p of perPool) console.log(p.pool.padEnd(21) + String(p.fotos).padStart(9) + String(p.geprueft).padStart(12) + String(p.motivZugeordnet).padStart(8) + String(p.offenFoto).padStart(9));
  console.log(`SUMME: ${summary.istFotos} echte Dateien, ${summary.redaktionellGeprueft} gesichtet; ${summary.poolsMit20Fotos}/${summary.pools} Pools mengenmäßig vollständig, ${summary.poolsMit20Freigaben}/${summary.pools} freigegeben.`);
  console.log(`Offen: ${summary.fehlendeFotos} Foto-Lücken + ${summary.fehlendeFreigaben} Freigabe-Lücken. Metadatenfehler: ${error.length}. Hinweise: ${warn.length}.`);
  for (const item of error.slice(0,40)) console.error('FEHLER', item);
}
if (process.argv.includes('--check') && error.length) process.exitCode = 1;
if (process.argv.includes('--strict') && (error.length || perPool.some(x=>x.geprueft<MIN))) process.exitCode = 1;
