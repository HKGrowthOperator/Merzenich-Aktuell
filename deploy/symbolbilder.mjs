#!/usr/bin/env node
/**
 * Editorial Image System V2
 *
 * Build-Time-Resolver fuer Meldungen ohne eigenes/geeignetes Bild.
 * - Originalbilder bleiben unangetastet.
 * - Alte Browser-/Commons-Symbolbilder und allgemeine Vereinslogos werden migriert.
 * - 16 Pools mit je 20 lokalen neutralen Symbolgrafiken werden erzeugt.
 * - Die Zuordnung wird persistent gespeichert und aendert sich bei spaeteren Builds nicht.
 * - Neue Meldungen erhalten nach Tags + geringster Nutzung ein anderes Motiv.
 *
 * Aufruf:
 *   node deploy/symbolbilder.mjs
 *   node deploy/symbolbilder.mjs --check
 */
import { readFileSync, writeFileSync, existsSync, readdirSync, statSync, mkdirSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { artikelSammeln, esc } from './lib-artikel.mjs';
import {
  RECHTE_GEPRUEFT_AM, KATEGORIEN, MINDEST_POOL, BIBLIOTHEK_DATEI, ZUORDNUNGEN_DATEI,
  bibliothekErzeugen, bibliothekAudit, zuordnungenLesen, zuordnungenSchreiben,
  vergibSymbolbilder, brauchtSymbolbild, istUnzulaessigesLogo, selbsttest, kategorieFuer,
} from './lib-symbolbilder.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');
const geaendert = [];
const fehler = [];

function writeIfChanged(pfad, inhalt) {
  const alt = existsSync(pfad) ? readFileSync(pfad, 'utf8') : '';
  if (alt === inhalt) return false;
  geaendert.push(pfad.replace(wurzel + '/', ''));
  if (!nurPruefen) { mkdirSync(dirname(pfad), { recursive: true }); writeFileSync(pfad, inhalt); }
  return true;
}

function figureHtml(m) {
  return `<figure class="art-figure art-figure--symbol" data-symbolbild="${esc(m.pool)}" data-editorial-image-id="${esc(m.id)}" data-editorial-pool="${esc(m.pool)}"><div class="media"><img src="${esc(m.src)}" alt="${esc(m.alt)}" width="${m.width || 1600}" height="${m.height || 900}" loading="eager" decoding="async" data-editorial-image data-editorial-image-id="${esc(m.id)}" data-editorial-pool="${esc(m.pool)}"></div><figcaption><span><span class="figure-badge">Symbolbild</span> · ${esc(m.alt)}. Kein Foto vom Ereignis.</span><span>Bild: ${esc(m.credit)} · ${esc(m.license)}</span></figcaption></figure>`;
}

function artikelPfad(a) { return join(site, a.url.replace(/^\//, ''), 'index.html'); }

function ersetzeArtikelbild(a, m) {
  const pfad = artikelPfad(a); if (!existsSync(pfad)) { fehler.push(`${a.url}: Artikelseite fehlt`); return; }
  const html = readFileSync(pfad, 'utf8');
  const neuFig = figureHtml(m);
  const bodyMarker = '<div class="article-body" data-readable>';
  const figRe = /<figure class="art-figure[^"]*"[^>]*>[\s\S]*?<\/figure>/;
  let neu = html;
  if (a.bild?.src && (a.bild.symbol || istUnzulaessigesLogo(a))) {
    if (figRe.test(html)) neu = html.replace(figRe, neuFig);
    else if (html.includes(bodyMarker)) neu = html.replace(bodyMarker, bodyMarker + neuFig);
  } else if (!a.bild?.src) {
    if (html.includes(bodyMarker)) neu = html.replace(bodyMarker, bodyMarker + neuFig);
    else fehler.push(`${a.url}: article-body fehlt, Symbolbild kann nicht eingesetzt werden`);
  }
  writeIfChanged(pfad, neu);
}

function aktualisiereEditorialCurrent(artikel, zuordnung) {
  const pfad = join(site, 'api', 'editorial-current.json'); if (!existsSync(pfad)) return;
  const roh = readFileSync(pfad, 'utf8'); let d;
  try { d = JSON.parse(roh); } catch (e) { fehler.push(`api/editorial-current.json: ${e.message}`); return; }
  let dirty = false;
  for (const k of ['hero', 'secondary']) {
    const s = d[k]; if (!s?.url) continue;
    const a = artikel.find((x) => x.url === s.url); if (!a) continue;
    const m = zuordnung.get(a.url); if (!m) continue; // echtes Bild bleibt original
    const neu = {
      image: m.src, imageAlt: m.alt, imageBadge: 'Symbolbild', imageCredit: m.credit,
      imageType: 'symbol', imageId: m.id, imagePool: m.pool, imageSource: m.source,
      imageLicense: m.license, imageRightsCheckedAt: m.rightsCheckedAt,
    };
    for (const [feld, wert] of Object.entries(neu)) if (s[feld] !== wert) { s[feld] = wert; dirty = true; }
  }
  if (dirty) writeIfChanged(pfad, JSON.stringify(d, null, 2) + '\n');
}

function htmlDateien(d) {
  const out = []; if (!existsSync(d)) return out;
  for (const e of readdirSync(d)) { const p = join(d, e); if (statSync(p).isDirectory()) out.push(...htmlDateien(p)); else if (e.endsWith('.html')) out.push(p); }
  return out;
}

function sichtbareDublettenzahl() {
  let n = 0;
  for (const pfad of htmlDateien(site)) {
    const html = readFileSync(pfad, 'utf8'); const z = new Map();
    for (const m of html.matchAll(/<img\b[^>]*src="(\/assets\/symbolbilder\/[^\"]+)"/g)) z.set(m[1], (z.get(m[1]) || 0) + 1);
    for (const c of z.values()) if (c > 1) n += c - 1;
  }
  return n;
}

function klassifiziere(b) {
  if (!b?.src) return 'none'; if (b.symbol) return 'symbol';
  const c = `${b.credit || ''} ${b.src || ''}`.toLowerCase();
  if (/polizei|feuerwehr|gemeinde merzenich|verwaltung|ministerium/.test(c)) return 'official';
  if (/wikimedia|archiv/.test(c)) return 'archive';
  return 'original';
}

function contentAudit() {
  const artikel = artikelSammeln(site); const count = { checked: artikel.length, original:0, official:0, archive:0, symbol:0, none:0, missingCredits:0, logos:0 };
  for (const a of artikel) {
    const t = klassifiziere(a.bild); count[t]++;
    if (!a.bild?.src) fehler.push(`${a.url}: normaler Beitrag ist bildlos`);
    if (a.bild?.src && !a.bild?.credit) { count.missingCredits++; fehler.push(`${a.url}: Bildnachweis/Credit fehlt`); }
    if (istUnzulaessigesLogo(a)) { count.logos++; fehler.push(`${a.url}: Vereinslogo/Wappen wird als allgemeines Newsfoto verwendet`); }
    if (a.bild?.symbol) {
      const pool = kategorieFuer(a);
      if (!String(a.bild.src).includes(`/assets/symbolbilder/${pool}/`)) fehler.push(`${a.url}: Symbolbild stammt nicht aus erwartetem Pool ${pool}`);
    }
  }
  return count;
}

// 1) Bibliothek materialisieren bzw. im Check-Modus gegen Sollzustand pruefen.
const libBuild = bibliothekErzeugen(wurzel, { schreiben: !nurPruefen });
for (const p of libBuild.geaendert) geaendert.push(p.replace(wurzel + '/', ''));

// 2) Harte Library-QA.
let audit = bibliothekAudit(wurzel);
for (const f of audit.fehler) fehler.push(f);
try { selbsttest(); } catch (e) { fehler.push(`Rotation/Selftest: ${e.message}`); }

// Im Schreibmodus nach dem Erzeugen noch einmal aus der realen Library lesen.
if (!nurPruefen && !audit.fehler.length) audit = bibliothekAudit(wurzel);

// 3) Artikel erfassen, persistente Zuordnung anwenden und Artikelseiten migrieren.
const artikelVorher = artikelSammeln(site);
const bestand = zuordnungenLesen(wurzel);
const vergabe = vergibSymbolbilder(artikelVorher, audit.pools, bestand);
for (const w of vergabe.warnungen) fehler.push(w);
if (vergabe.dirty) {
  if (nurPruefen) geaendert.push(ZUORDNUNGEN_DATEI);
  else if (zuordnungenSchreiben(wurzel, vergabe.state)) geaendert.push(ZUORDNUNGEN_DATEI);
}
for (const a of artikelVorher) {
  if (!brauchtSymbolbild(a)) continue;
  const m = vergabe.zuordnung.get(a.url); if (!m) { fehler.push(`${a.url}: keine gültige Symbolbild-Zuordnung`); continue; }
  ersetzeArtikelbild(a, m);
}
aktualisiereEditorialCurrent(artikelVorher, vergabe.zuordnung);

// 4) Nach dem Schreiben tatsächlichen ausgelieferten Stand prüfen.
const content = contentAudit();
const duplicates = sichtbareDublettenzahl();
if (duplicates > 0) console.log(`Hinweis: ${duplicates} sichtbare Wiederholung(en) desselben Symbolbilds über komplette HTML-Seiten; Section-QA bleibt zusätzlich im Browser.`);

// 5) Bericht: nur echte, gültige und eindeutige Motive zählen.
console.log('EDITORIAL IMAGE LIBRARY');
for (const k of KATEGORIEN) console.log(`${k.padEnd(18)} ${String(audit.status[k] || 0).padStart(2)} ${(audit.status[k] || 0) >= MINDEST_POOL ? 'PASS' : 'FAIL'}`);
console.log(`Gesamt              ${Object.values(audit.status).reduce((a,b) => a+b,0)}`);
console.log('ROTATION');
console.log('neue Meldungen:       ' + (fehler.some((x) => x.includes('Rotation/Selftest')) ? 'FAIL' : 'PASS'));
console.log('Reload-Rotation:      AUS');
console.log('stabile Zuordnung:    ' + (fehler.some((x) => x.includes('Rotation/Selftest')) ? 'FAIL' : 'PASS'));
console.log(`sichtbare Dubletten:  ${duplicates}`);
console.log('CONTENT');
console.log(`Beiträge geprüft:     ${content.checked}`);
console.log(`Original:              ${content.original}`);
console.log(`Official:              ${content.official}`);
console.log(`Archive:               ${content.archive}`);
console.log(`Symbol:                ${content.symbol}`);
console.log(`Bildlos:               ${content.none}`);
console.log(`Fehlende Credits:      ${content.missingCredits}`);
console.log(`Falsche Vereinslogos:  ${content.logos}`);
console.log(`Rechte geprüft am:     ${RECHTE_GEPRUEFT_AM}`);
console.log(`Library:               /${BIBLIOTHEK_DATEI.replace(/^chatgpt-site\//,'')}`);
console.log(`Assignments:           /${ZUORDNUNGEN_DATEI.replace(/^chatgpt-site\//,'')}`);

if (nurPruefen && geaendert.length) fehler.push(`Generator nicht idempotent / Stand nicht aktuell: ${[...new Set(geaendert)].slice(0, 12).join(', ')}`);
if (fehler.length) {
  console.error('\nEDITORIAL IMAGE SYSTEM V2 – FAIL');
  for (const f of [...new Set(fehler)]) console.error('FEHLER: ' + f);
  process.exit(2);
}

if (nurPruefen) console.log('\nEDITORIAL IMAGE SYSTEM V2 – PASS');
else console.log(`\nEDITORIAL IMAGE SYSTEM V2 geschrieben: ${new Set(geaendert).size} Datei(en) geändert/erzeugt.`);
