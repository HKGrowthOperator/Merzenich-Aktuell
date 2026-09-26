#!/usr/bin/env node
/**
 * Editorial Image System V2
 *
 * Build-Time-Resolver fuer Meldungen ohne eigenes/geeignetes Bild.
 * - Originalbilder und offizielle Quellenmotive bleiben unangetastet.
 * - Alte Browser-/Commons-Symbolbilder und allgemeine Vereinslogos werden migriert.
 * - 16 Pools mit mindestens 20 lokalen neutralen Symbolgrafiken werden erzeugt.
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
import { artikelSammeln, esc, SITE_URL } from './lib-artikel.mjs';
import {
  RECHTE_GEPRUEFT_AM, KATEGORIEN, ALLE_KATEGORIEN, MINDEST_POOL, BIBLIOTHEK_DATEI, ZUORDNUNGEN_DATEI,
  bibliothekErzeugen, bibliothekAudit, zuordnungenLesen, zuordnungenSchreiben,
  vergibSymbolbilder, selbsttest, kategorieFuer, motivregelFuer, motivFuer,
} from './lib-symbolbilder.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');
const geaendert = [];
const fehler = [];

const norm = (s) => String(s || '').toLocaleLowerCase('de-DE').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/ß/g, 'ss');

/**
 * Ein Vereinslogo ist nur dann ein redaktionell erlaubtes Hauptbild, wenn die
 * Meldung selbst explizit Logo/Wappen/Identitaet zum Gegenstand hat.
 *
 * Wichtig: Weder der Quellenkasten noch ein alter Badge werden ausgewertet.
 * Dort kann historisch "Offizielles Vereinslogo" stehen, obwohl es sich z.B.
 * um ein offizielles Gemeindezeichen handelt. Ausserdem muss ein echter
 * Vereins-/Club-Kontext vorliegen; Gemeinde-/Behoerdenzeichen bleiben damit
 * als offizielle Quellenmotive erhalten.
 */
function istAllgemeinesVereinslogo(a) {
  const b = a?.bild || {};
  const bildText = norm(`${b.src || ''} ${b.alt || ''} ${b.credit || ''}`);
  const redaktionellerKern = norm(`${a?.titel || ''} ${a?.teaser || ''} ${a?.kicker || ''}`);
  const vereinsKontext = a?.ressort === 'sport' || a?.ressort === 'vereine'
    || /\b(sc|fc|sv|verein|club|sportverein)\b/.test(redaktionellerKern);
  if (!vereinsKontext) return false;
  if (!/logo|wappen|sc[-_ ]?1919|vereinszeichen/.test(bildText)) return false;
  return !/\b(logo|wappen|vereinszeichen|vereinsidentitat)\b/.test(redaktionellerKern);
}

function brauchtV2Symbol(a) {
  return !a?.bild?.src || Boolean(a?.bild?.symbol) || istAllgemeinesVereinslogo(a);
}

function writeIfChanged(pfad, inhalt) {
  const alt = existsSync(pfad) ? readFileSync(pfad, 'utf8') : '';
  if (alt === inhalt) return false;
  geaendert.push(pfad.replace(wurzel + '/', ''));
  if (!nurPruefen) { mkdirSync(dirname(pfad), { recursive: true }); writeFileSync(pfad, inhalt); }
  return true;
}

// Poolfotos tragen Urheber und Lizenz schon im credit ("Symbolbild · Name /
// Wikimedia Commons · CC BY-SA 4.0"). Ohne diese Pruefung stand die Lizenz
// doppelt und "Symbolbild" zweimal in der Bildzeile.
const nachweis = (m) => {
  const c = String(m.credit || '').replace(/^Symbolbild\s*·\s*/, '');
  return m.license && !c.includes(m.license) ? `${c} · ${m.license}` : c;
};

// WebP-Fassungen aus deploy/pool-varianten.py (480/800/1200 px) neben dem
// Poolfoto. Nur was auf der Platte liegt, kommt ins srcset.
const VARIANTEN = [480, 800, 1200];
function srcsetFuer(m) {
  if (!/^\/assets\/editorial-pools\/.+\.(jpe?g|png)$/i.test(String(m.src || ''))) return '';
  const basis = m.src.replace(/\.[^.]+$/, '');
  const teile = VARIANTEN.filter((w) => existsSync(join(site, `${basis}-${w}.webp`.replace(/^\//, '')))).map((w) => `${basis}-${w}.webp ${w}w`);
  if (!teile.length) return '';
  return [...teile, `${m.src} ${m.width || 1600}w`].join(', ');
}

// Bildbeschreibung fuer Poolfotos. Die Commons-Beschreibungen sind oft
// englisch oder nennen fremde Orte ("Dachstuhlbrand ... Koeln"); neben einer
// Merzenicher Meldung verwirrt das. Lokale Fotos (Merzenich, Kreis Dueren)
// behalten ihre deutsche Beschreibung, alle anderen bekommen eine neutrale
// deutsche Motivangabe ihres Pools.
const POOL_MOTIV = {
  aktuell: 'Ortsansicht aus der Gemeinde Merzenich', blaulicht: 'Rettungswagen im Einsatz',
  polizei: 'Polizeiwache in Nordrhein-Westfalen', feuerwehr: 'Feuerwehrhaus mit Einsatzfahrzeugen',
  brand: 'Feuerwehr beim Löschen eines Brandes', sport: 'Fußballplatz', termine: 'Kirmes und Markt',
  vereine: 'Treffpunkt des Vereinslebens im Dorf', leben: 'Dorfplatz', wirtschaft: 'Tagebau Hambach',
  tipp: 'Rad- und Wanderweg', menschen: 'Treffpunkt im Ort',
  tennisdetail: 'Tennisplatz', digitaldetail: 'Smartphone für ältere Menschen', tanzdetail: 'Tanzsaal',
  naturdetail: 'Nistkasten', vereinsdetail: 'Vereinsheim', kirchedetail: 'Kirchenfenster',
};
const ENGLISCH = /\b(the|of|and|with|street|near|view|house|church|road|square|germany|north rhine|open pit|mine|from)\b/i;
const FREMDSCHRIFT = /[^\u0000-\u024f\u2000-\u206f\u20ac]/;
function altFuer(m) {
  if (m.photo !== true) return m.alt;
  const lokal = ['Merzenich', 'Kreis Düren'].includes(m.locality);
  const alt = String(m.alt || '').replace(/\s+([,.;:])/g, '$1').replace(/\s*\.\s*$/, '').trim();
  if (lokal && alt && !ENGLISCH.test(alt) && !FREMDSCHRIFT.test(alt) && alt.length <= 140) return alt;
  return POOL_MOTIV[m.pool] || alt;
}

function figureHtml(m, stufe = 'B') {
  const srcset = srcsetFuer(m);
  m = { ...m, alt: altFuer(m) };
  return `<figure class="art-figure art-figure--symbol" data-bildstufe="${esc(stufe)}" data-symbolbild="${esc(m.pool)}" data-editorial-image-id="${esc(m.id)}" data-editorial-pool="${esc(m.pool)}"><div class="media"><img src="${esc(m.src)}"${srcset ? ` srcset="${esc(srcset)}" sizes="(max-width: 760px) 100vw, 760px"` : ''} alt="${esc(m.alt)}" width="${m.width || 1600}" height="${m.height || 900}" loading="eager" decoding="async" data-editorial-image data-editorial-image-id="${esc(m.id)}" data-editorial-pool="${esc(m.pool)}"></div><figcaption><span><span class="figure-badge">Symbolbild</span> · ${esc(m.alt)}. Kein Foto vom Ereignis.</span><span>Bild: ${esc(nachweis(m))}</span></figcaption></figure>`;
}

// Ortsansichten (Bildstufe O, Entscheidung KBS 26.09.2026): Jede Meldung ohne
// passendes Motiv traegt eine gesichtete Ansicht ihres Ortsteils, klar als
// "Ortsansicht" gekennzeichnet. Quelle: deploy/ortsbilder.json.
const ORTSBILDER = JSON.parse(readFileSync(join(wurzel, 'deploy', 'ortsbilder.json'), 'utf8'));
const ORT_SLUGS = new Set(['merzenich', 'golzheim', 'girbelsrath', 'morschenich', 'buergewald']);
function ortsansichtenAufloesen(pools) {
  const nachId = new Map(Object.values(pools).flat().map((m) => [m.id, m]));
  return ORTSBILDER.ansichten.map((o) => {
    if (!o.pool) return { ...o, nachweis: `${o.credit} · ${o.lizenz}` };
    const m = nachId.get(o.pool);
    if (!m) { fehler.push(`ortsbilder.json: ${o.id} verweist auf fehlendes Poolfoto ${o.pool}`); return null; }
    return { ...o, src: m.src, srcset: srcsetFuer(m), width: m.width, height: m.height, nachweis: `${o.credit} · ${o.lizenz}` };
  }).filter(Boolean);
}
// Innerhalb eines Ortsteils reihum nach Datum (neueste zuerst): benachbarte
// Meldungen in Listen zeigen so verschiedene Ansichten. Die Reihenfolge haengt
// nur am Bestand, der Lauf bleibt wiederholbar.
const ortVon = (a) => (ORT_SLUGS.has(a.ortsteil) ? a.ortsteil : 'merzenich');
function ortsansichtenVerteilen(artikel, ansichten) {
  const zuordnung = new Map();
  const zaehler = new Map();
  const sortiert = [...artikel].sort((x, y) => String(y.datum || '').localeCompare(String(x.datum || '')) || x.url.localeCompare(y.url));
  for (const a of sortiert) {
    const ort = ortVon(a);
    const liste = ansichten.filter((o) => o.ortsteil === ort);
    const auswahl = liste.length ? liste : ansichten.filter((o) => o.ortsteil === 'merzenich');
    if (!auswahl.length) continue;
    const i = zaehler.get(ort) || 0; zaehler.set(ort, i + 1);
    zuordnung.set(a.url, auswahl[i % auswahl.length]);
  }
  return zuordnung;
}
function ortsansichtFigure(o) {
  return `<figure class="art-figure art-figure--symbol art-figure--ortsansicht" data-bildstufe="O" data-ortsansicht="${esc(o.id)}"><div class="media"><img src="${esc(o.src)}"${o.srcset ? ` srcset="${esc(o.srcset)}" sizes="(max-width: 760px) 100vw, 760px"` : ''} alt="${esc(o.alt)}" width="${o.width || 1440}" height="${o.height || 960}" loading="eager" decoding="async" data-editorial-image></div><figcaption><span><span class="figure-badge">Ortsansicht</span> · ${esc(o.alt)}. Kein Foto vom Ereignis.</span><span>Bild: ${esc(o.nachweis)}</span></figcaption></figure>`;
}

function artikelPfad(a) { return join(site, a.url.replace(/^\//, ''), 'index.html'); }

/**
 * Repariert genau eine Fehlmigration aus dem ersten V2-Lauf: Das offizielle
 * Gemeindezeichen der Haushaltschronik war wegen eines historisch falsch
 * benannten Badges als Vereinslogo interpretiert worden. Die Quelle ist lokal,
 * dokumentiert und fuer diese Gemeindemeldung das passendere Originalmotiv.
 * Die Reparatur ist idempotent und greift nur, solange dort unser versehentlich
 * zugewiesenes Vereine-Symbol liegt.
 */
function repariereOffiziellesGemeindebild() {
  const pfad = join(site, 'rathaus', 'haushalt-2026-chronik', 'index.html');
  if (!existsSync(pfad)) return;
  const alt = readFileSync(pfad, 'utf8');
  if (!/data-editorial-image-id="vereine-01-vereinsleben"/.test(alt)) return;
  const src = '/assets/editorial/gemeinde-merzenich.png';
  const abs = `${SITE_URL}${src}`;
  const altText = 'Offizielles Zeichen der Gemeinde Merzenich';
  const credit = 'Gemeinde Merzenich · Heimat-Info';
  const figure = `<figure class="art-figure"><div class="media contain"><img src="${src}" alt="${altText}" width="1600" height="900" loading="eager" fetchpriority="high" decoding="async" data-editorial-image></div><figcaption><span><span class="figure-badge">Offizielles Quellenmotiv</span> · ${altText}</span><span>Bild: ${credit}</span></figcaption></figure>`;
  let neu = alt.replace(/<figure class="art-figure[^"]*"[^>]*>[\s\S]*?<\/figure>/, figure);
  neu = neu.replace(/<meta property="og:image" content="[^"]*">/, `<meta property="og:image" content="${abs}">`);
  neu = neu.replace(/<meta property="og:image:alt" content="[^"]*">/, `<meta property="og:image:alt" content="${altText}">`);
  neu = neu.replace(/<meta name="twitter:image" content="[^"]*">/, `<meta name="twitter:image" content="${abs}">`);
  neu = neu.replace(/\s*Bildtyp:\s*Symbolbild\.\s*Bild:\s*Merzenich Aktuell[^<]*\./i, ` Bildtyp: Offizielles Quellenmotiv. Bild: ${credit}.`);
  writeIfChanged(pfad, neu);
}

function metadataAufSymbolbild(html, m, typ = 'Symbolbild') {
  const absolute = `${SITE_URL}${m.src}`;
  let neu = html;
  neu = neu.replace(/<meta property="og:image" content="[^"]*">/, `<meta property="og:image" content="${esc(absolute)}">`);
  neu = neu.replace(/<meta property="og:image:alt" content="[^"]*">/, `<meta property="og:image:alt" content="${esc(m.alt)}">`);
  neu = neu.replace(/<meta name="twitter:image" content="[^"]*">/, `<meta name="twitter:image" content="${esc(absolute)}">`);
  // NewsArticle und Article werden beide verwendet. Nur der redaktionelle
  // Artikelknoten wird angefasst, nicht Organization/WebSite-JSON-LD.
  neu = neu.replace(/("@type":"(?:NewsArticle|Article)"[\s\S]*?"image":)"[^"]*"/, `$1"${absolute}"`);
  // Den kompletten bisherigen Bildnachweis ersetzen. Der alte Ausdruck endete
  // am ersten Punkt und war damit bei Lizenzen wie "CC BY-SA 4.0" nicht
  // idempotent (z. B. blieb ".DE." stehen und wuchs bei jedem Build weiter).
  const bildMeta = ` Bildtyp: ${typ}. Bild: ${nachweis(m)}. `;
  neu = neu.replace(
    /\s*Bildtyp:\s*(?:Offizielles Vereinslogo|Symbolbild|Ortsansicht)\.\s*(?:Foto|Bild):\s*[^<]*(?=(?:<a\b[^>]*href="\/korrekturen\/"|<\/div>))/i,
    bildMeta
  );
  return neu;
}

function ersetzeArtikelbild(a, m, stufe, ortsansicht = false) {
  const pfad = artikelPfad(a); if (!existsSync(pfad)) { fehler.push(`${a.url}: Artikelseite fehlt`); return; }
  const html = readFileSync(pfad, 'utf8');
  const neuFig = ortsansicht ? ortsansichtFigure(m) : figureHtml(m, stufe);
  const bodyMarker = '<div class="article-body" data-readable>';
  const figRe = /<figure class="art-figure[^"]*"[^>]*>[\s\S]*?<\/figure>/;
  let neu = html;
  if (a.bild?.src && (a.bild.symbol || istAllgemeinesVereinslogo(a))) {
    if (figRe.test(neu)) neu = neu.replace(figRe, neuFig);
    else if (neu.includes(bodyMarker)) neu = neu.replace(bodyMarker, bodyMarker + neuFig);
  } else if (!a.bild?.src) {
    if (neu.includes(bodyMarker)) neu = neu.replace(bodyMarker, bodyMarker + neuFig);
    else fehler.push(`${a.url}: article-body fehlt, Symbolbild kann nicht eingesetzt werden`);
  }
  neu = metadataAufSymbolbild(neu, m, ortsansicht ? 'Ortsansicht' : 'Symbolbild');
  writeIfChanged(pfad, neu);
}

// Meldung ohne passendes Motiv (Motivregeln): das Symbolbild faellt ersatzlos
// weg, Teilen zeigt das Standardbild. Echte Fotos fasst das nicht an.
function entferneSymbolbild(a) {
  const pfad = artikelPfad(a); if (!existsSync(pfad)) { fehler.push(`${a.url}: Artikelseite fehlt`); return; }
  const html = readFileSync(pfad, 'utf8');
  const standard = `${SITE_URL}/assets/img/og-default.jpg`;
  const POOLBILD = /\/assets\/(?:editorial-pools|symbolbilder)\//;
  let neu = html.replace(/<figure class="art-figure art-figure--symbol"[^>]*>[\s\S]*?<\/figure>/, '');
  neu = neu.replace(/<meta property="og:image" content="([^"]*)">/, (m, u) => (POOLBILD.test(u) ? `<meta property="og:image" content="${standard}">` : m));
  neu = neu.replace(/<meta property="og:image:alt" content="[^"]*">/, (m) => (neu.includes(`content="${standard}"`) ? '<meta property="og:image:alt" content="Merzenich Aktuell">' : m));
  neu = neu.replace(/<meta name="twitter:image" content="([^"]*)">/, (m, u) => (POOLBILD.test(u) ? `<meta name="twitter:image" content="${standard}">` : m));
  neu = neu.replace(/("@type":"(?:NewsArticle|Article)"[\s\S]*?"image":)"([^"]*)"/, (m, v, u) => (POOLBILD.test(u) ? `${v}"${standard}"` : m));
  neu = neu.replace(/\s*Bildtyp:\s*(?:Symbolbild|Ortsansicht)\.\s*Bild:\s*[^<]*(?=(?:<a\b[^>]*href="\/korrekturen\/"|<\/div>))/i, ' ');
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
      imageLicense: m.license, imageRightsCheckedAt: m.rightsCheckedAt, imageFit: '',
    };
    for (const [feld, wert] of Object.entries(neu)) if (s[feld] !== wert) { s[feld] = wert; dirty = true; }
    for (const feld of ['imageSrcset', 'imageSizes', 'imageWidth', 'imageHeight', 'imageSourceUrl']) {
      if (feld in s) { delete s[feld]; dirty = true; }
    }
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
    for (const m of html.matchAll(/<img\b[^>]*src="(\/assets\/(?:symbolbilder|editorial-pools)\/[^\"]+)"/g)) z.set(m[1], (z.get(m[1]) || 0) + 1);
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
    if (!a.bild?.src && !BILDLOS.has(a.url)) fehler.push(`${a.url}: normaler Beitrag ist bildlos`);
    if (a.bild?.src && !a.bild?.credit) { count.missingCredits++; fehler.push(`${a.url}: Bildnachweis/Credit fehlt`); }
    if (istAllgemeinesVereinslogo(a)) { count.logos++; fehler.push(`${a.url}: Vereinslogo/Wappen wird als allgemeines Newsfoto verwendet`); }
    if (a.bild?.stufe === 'O') {
      // Ortsansicht: nur gesichtete Ansichten aus deploy/ortsbilder.json.
      if (!ORTSANSICHTEN.some((o) => o.src === a.bild.src)) fehler.push(`${a.url}: Ortsansicht ${a.bild.src} steht nicht in deploy/ortsbilder.json`);
    } else if (a.bild?.symbol) {
      // Motivregel: das Symbolbild muss genau das Motiv zeigen, das die Aussage verlangt.
      const regel = motivregelFuer(a); const eintrag = BIBLIOTHEK_NACH_SRC.get(a.bild.src); const motiv = eintrag && motivFuer(eintrag);
      if (!regel) fehler.push(`${a.url}: Symbolbild ohne Motivregel (${a.bild.src})`);
      else if (!regel.motive.includes(motiv)) fehler.push(`${a.url}: Symbolbild zeigt ${motiv || 'kein gesichtetes Motiv'}, Regel ${regel.id} verlangt ${regel.motive.join('/')}`);
    }
  }
  return count;
}

// 0) Eine bekannte Fehlmigration des ersten V2-Laufs zurueckdrehen, bevor
// der aktuelle Bestand erfasst und die persistente Vergabe bereinigt wird.
repariereOffiziellesGemeindebild();

// 1) Bibliothek materialisieren bzw. im Check-Modus gegen Sollzustand pruefen.
const libBuild = bibliothekErzeugen(wurzel, { schreiben: !nurPruefen });
for (const p of libBuild.geaendert) geaendert.push(p.replace(wurzel + '/', ''));

// 2) Harte Library-QA.
let audit = bibliothekAudit(wurzel);
for (const f of audit.fehler) fehler.push(f);
try { selbsttest(); } catch (e) { fehler.push(`Rotation/Selftest: ${e.message}`); }

if (!nurPruefen && !audit.fehler.length) audit = bibliothekAudit(wurzel);

// 3) Artikel erfassen, persistente Zuordnung anwenden und Artikelseiten migrieren.
const artikelVorher = artikelSammeln(site);
// Der Library-Resolver kennt Symbolbilder als Ersatzgrund. Fuer allgemeine
// Vereinslogos markieren wir ausschliesslich die Arbeitskopie als Symbol,
// damit die bestehende persistente Vergabelogik dieselbe Route nutzt.
const artikelFuerVergabe = artikelVorher.map((a) => istAllgemeinesVereinslogo(a)
  ? { ...a, bild: { ...(a.bild || {}), symbol: true } }
  : a);
const bestand = zuordnungenLesen(wurzel);
const vergabe = vergibSymbolbilder(artikelFuerVergabe, audit.pools, bestand);
for (const w of vergabe.warnungen) fehler.push(w);
if (vergabe.dirty) {
  if (nurPruefen) geaendert.push(ZUORDNUNGEN_DATEI);
  else if (zuordnungenSchreiben(wurzel, vergabe.state)) geaendert.push(ZUORDNUNGEN_DATEI);
}
const ORTSANSICHTEN = ortsansichtenAufloesen(audit.pools);
let ortsansichtenGesetzt = 0;
const ORTSANSICHT_FUER = ortsansichtenVerteilen(artikelVorher.filter((a) => brauchtV2Symbol(a) && !vergabe.zuordnung.get(a.url)), ORTSANSICHTEN);
for (const a of artikelVorher) {
  if (!brauchtV2Symbol(a)) continue;
  const m = vergabe.zuordnung.get(a.url);
  if (m) { ersetzeArtikelbild(a, m, vergabe.stufen.get(a.url)); continue; }
  const o = ORTSANSICHT_FUER.get(a.url);
  if (o) { ersetzeArtikelbild(a, { ...o, credit: o.nachweis, license: '' }, 'O', true); ortsansichtenGesetzt++; }
  else entferneSymbolbild(a);
}
aktualisiereEditorialCurrent(artikelFuerVergabe, vergabe.zuordnung);
const BILDLOS = new Set(vergabe.bildlos.map((b) => b.url));
const BIBLIOTHEK_NACH_SRC = new Map(Object.values(audit.pools).flat().map((m) => [m.src, m]));

// 4) Nach dem Schreiben tatsächlichen ausgelieferten Stand prüfen.
const content = contentAudit();
const duplicates = sichtbareDublettenzahl();
if (duplicates > 0) console.log(`Hinweis: ${duplicates} sichtbare Wiederholung(en) desselben Symbolbilds über komplette HTML-Seiten; Section-QA bleibt zusätzlich im Browser.`);

// 5) Bericht: nur echte, gültige und eindeutige Motive zählen.
console.log('EDITORIAL IMAGE LIBRARY');
for (const k of ALLE_KATEGORIEN) console.log(`${k.padEnd(18)} ${String(audit.status[k] || 0).padStart(2)} ${(audit.status[k] || 0) >= MINDEST_POOL ? 'PASS' : 'FAIL'}`);
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
console.log(`Ortsansichten:         ${ortsansichtenGesetzt} (Meldungen ohne passendes Motiv, Stufe O)`);
for (const b of vergabe.bildlos) console.log(`  ohne Motiv, Ortsansicht: ${b.url} (${b.regel ? `Regel ${b.regel}, kein gesichtetes Foto mit Motiv ${b.motive.join('/')}` : 'keine Motivregel'})`);
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
