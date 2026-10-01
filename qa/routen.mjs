#!/usr/bin/env node
/**
 * Routenmatrix (Audit 28.09.2026): jede HTML-Route unter chatgpt-site/ mit
 * Index-Status, Title, Description, Canonical, H1, OG/Twitter, JSON-LD, Bildern,
 * internen Links, Sitemap-Soll/Ist und verbotenen Inhalten.
 *
 * Ausgabe: qa/routen.json (maschinenlesbar) und mit --bericht zusaetzlich
 * docs/SITE-AUDIT.md (Tabelle je Route). Exitcode 1 bei Fehlern.
 * Aufruf: node qa/routen.mjs [--bericht] [--json]
 *
 * Fehler: tote interne Links, fehlende Bilddateien, indexierbare Seite ohne
 * Title/Description/Canonical/genau ein H1, indexierbare Seite nicht in einer
 * Sitemap, Sitemap-Eintrag ohne Seite oder mit noindex, Platzhalter und
 * Entwicklerreste im Seiteninhalt. Die Werbeflaechen (werbung:*-Marker) sind
 * vom Inhaltsscan ausgenommen: dort laufen gekennzeichnete Demo-Anzeigen
 * (Entscheidung Betreiber 27./28.09.2026).
 */
import { readFileSync, writeFileSync, readdirSync, statSync, existsSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const SITE_URL = JSON.parse(readFileSync(join(wurzel, 'deploy', 'site.json'), 'utf8')).url.replace(/\/$/, '');
const { liveUrl } = await import(join(wurzel, 'deploy', 'lib-artikel.mjs'));
const bericht = process.argv.includes('--bericht');
const nurJson = process.argv.includes('--json');

// Interne Bereiche: serverseitig gesperrt (deploy/coolify/erzeuge-nginx-conf.mjs).
const INTERN = ['admin', 'redaktion', 'redaktionshandbuch'];

// ------------------------------------------------------------- Routen
const routen = [];
(function lauf(d) {
  for (const e of readdirSync(d)) {
    const p = join(d, e);
    if (statSync(p).isDirectory()) { if (!(d === site && INTERN.includes(e))) lauf(p); continue; }
    if (!e.endsWith('.html')) continue;
    const rel = p.slice(site.length).replace(/\\/g, '/');
    const route = rel.endsWith('/index.html') ? rel.slice(0, -'index.html'.length) : rel;
    routen.push({ route, datei: p });
  }
})(site);
routen.sort((a, b) => a.route.localeCompare(b.route));
const bekannt = new Set(routen.map((r) => r.route));

// Weiterleitungen (_redirects): Quelle -> Ziel. Ein Link auf eine Quelle ist kein toter Link.
const umleitung = new Map();
for (const f of [join(site, '_redirects'), join(wurzel, '_redirects')]) {
  if (!existsSync(f)) continue;
  for (const z of readFileSync(f, 'utf8').split('\n')) {
    const t = z.trim().split(/\s+/);
    if (t.length >= 2 && t[0].startsWith('/')) umleitung.set(t[0], t[1]);
  }
}
const nginx = existsSync(join(wurzel, 'deploy', 'coolify', 'nginx.conf')) ? readFileSync(join(wurzel, 'deploy', 'coolify', 'nginx.conf'), 'utf8') : '';
const nginxOrte = new Set([...nginx.matchAll(/location\s+(?:=|\^~)?\s*(\/[^\s{]*)/g)].map((m) => m[1]));

// Sitemaps
const sitemapUrls = new Map();
for (const f of readdirSync(site).filter((f) => /^(sitemap-.*|news-sitemap)\.xml$/.test(f))) {
  for (const m of readFileSync(join(site, f), 'utf8').matchAll(/<loc>([^<]+)<\/loc>/g)) {
    const u = m[1].replace(SITE_URL, '');
    if (!sitemapUrls.has(u)) sitemapUrls.set(u, []);
    sitemapUrls.get(u).push(f);
  }
}

// ---------------------------------------------------------- Pruefung
const PLATZHALTER = [/lorem ipsum/i, /\bTODO\b/, /\bFIXME\b/, /\bPrüffassung\b/i, /\bPlatzhaltertext\b/i, /\bBeispielkunde\b/i, /\bMusterfirma\b/i, /\bMax Mustermann\b/i, /\bcoming soon\b/i];
// Nur Formulierungen, die mit dem Datum veralten: "heute"/"morgen" als Zeitangabe,
// nicht "bis heute", "bereits heute" oder "am Morgen".
const ZEITWORT = /(?<!\b(?:bis|bereits|schon|noch|am|frühen|des) )\b(heute|morgen|übermorgen)\b(?! früh)|\b(kommenden (?:Montag|Dienstag|Mittwoch|Donnerstag|Freitag|Samstag|Sonntag)|nächstes Wochenende|am kommenden Wochenende)\b/;
const entities = (s) => s.replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&#39;/g, "'").replace(/&lt;/g, '<').replace(/&gt;/g, '>');
const text = (html) => entities(html.replace(/<script[\s\S]*?<\/script>/g, ' ').replace(/<style[\s\S]*?<\/style>/g, ' ').replace(/<[^>]+>/g, ' ')).replace(/\s+/g, ' ');

const befunde = [];
const matrix = [];
for (const { route, datei } of routen) {
  const html = readFileSync(datei, 'utf8');
  const meta = (name) => { const m = new RegExp(`<meta (?:name|property)="${name}" content="([^"]*)"`).exec(html); return m ? entities(m[1]) : ''; };
  const robots = meta('robots');
  const typ = /\/404\.html$|^\/offline/.test(route) ? 'system'
    : /\/danke\/$/.test(route) ? 'danke'
    : /^\/termine\/[^/]+\/$/.test(route) && route !== '/termine/melden/' ? 'termin'
    : /^\/thema\/[^/]+\//.test(route) ? 'thema'
    : /\/seite\/\d+\/$/.test(route) ? 'pagination'
    : /<meta property="og:type" content="article"/.test(html) && /"@type":"(?:News)?Article"/.test(html) ? 'artikel'
    : /^\/vereine\/[^/]+\/$/.test(route) ? 'verein'
    : 'seite';
  const noindex = /noindex/i.test(robots);
  const title = (/<title>([^<]*)<\/title>/.exec(html) || [])[1] || '';
  const desc = meta('description');
  const canonical = (/<link rel="canonical" href="([^"]+)"/.exec(html) || [])[1] || '';
  const h1 = (html.match(/<h1[\s>]/g) || []).length;
  const ld = [...html.matchAll(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/g)].map((m) => { try { const d = JSON.parse(m[1]); return [].concat(d['@graph'] || d).map((x) => x['@type']).flat(); } catch { return ['UNGUELTIG']; } }).flat();
  const main = html.slice(Math.max(0, html.indexOf('<main')), html.indexOf('</main>') > 0 ? html.indexOf('</main>') : html.length);
  const ohneWerbung = main.replace(/<!-- werbung:([a-z0-9-]+):start -->[\s\S]*?<!-- werbung:\1:end -->/g, '');
  const bilder = [...ohneWerbung.matchAll(/<img\b[^>]*>/g)].map((m) => m[0]);
  const ohneAlt = bilder.filter((i) => !/\salt="/.test(i)).length;
  const fehlendeBilder = bilder.map((i) => (/\ssrc="([^"]+)"/.exec(i) || [])[1]).filter((s) => s && s.startsWith('/') && !s.startsWith('//') && !s.startsWith('/api/') && !existsSync(join(site, decodeURIComponent(s.split(/[?#]/)[0]))));
  const links = [...html.matchAll(/<a\b[^>]*\bhref="([^"#?]*)(?:[?#][^"]*)?"/g)].map((m) => m[1]).filter((h) => h.startsWith('/') && !h.startsWith('//'));
  const tot = [...new Set(links.filter((h) => {
    if (bekannt.has(h) || umleitung.has(h) || umleitung.has(h.replace(/\/$/, '')) || nginxOrte.has(h)) return false;
    if (/^\/(api|assets|admin|redaktion)\//.test(h) || /\.(xml|json|ics|webmanifest|txt|pdf|png|jpg|jpeg|webp|svg|zip)$/.test(h)) return !existsSync(join(site, h)) && !/^\/api\//.test(h);
    if (INTERN.some((i) => h.startsWith(`/${i}/`))) return true;
    return true;
  }))];
  const inhalt = text(ohneWerbung);
  const platzhalter = PLATZHALTER.filter((r) => r.test(inhalt)).map((r) => r.source);
  const zeitwort = typ === 'artikel' && ZEITWORT.test(text((/<div class="prose[^"]*">([\s\S]*?)<\/div>/.exec(html) || [])[1] || '')) ? (ZEITWORT.exec(inhalt) || [''])[0] : '';
  const inSitemap = sitemapUrls.get(route) || [];
  const zeile = { route, typ, index: !noindex, title: title.length, desc: desc.length, canonical: canonical === SITE_URL + route ? 'eigen' : canonical === liveUrl(route) ? 'live' : canonical ? 'fremd' : 'fehlt', h1, og: !!meta('og:title') && !!meta('og:image'), twitter: !!meta('twitter:card'), jsonld: [...new Set(ld)].join('+'), bilder: bilder.length, ohneAlt, fehlendeBilder, links: links.length, tot, platzhalter, zeitwort, sitemap: inSitemap };
  matrix.push(zeile);

  const f = (text2) => befunde.push({ route, schwere: 'fehler', text: text2 });
  const h = (text2) => befunde.push({ route, schwere: 'hinweis', text: text2 });
  for (const t of tot) f(`toter interner Link ${t}`);
  for (const b of fehlendeBilder) f(`Bilddatei fehlt: ${b}`);
  if (ohneAlt) f(`${ohneAlt} Bild(er) ohne alt`);
  if (platzhalter.length) f(`Platzhalter im Inhalt: ${platzhalter.join(', ')}`);
  if (ld.includes('UNGUELTIG')) f('JSON-LD nicht lesbar');
  if (typ === 'system' || typ === 'danke') { if (!noindex) f('System-/Danke-Seite ohne noindex'); }
  if (!noindex) {
    if (!title) f('Title fehlt');
    if (!desc) f('Description fehlt');
    if (zeile.canonical === 'fehlt') f('Canonical fehlt');
    if (zeile.canonical === 'fremd') f(`Canonical zeigt auf eine fremde Adresse: ${canonical}`);
    if (h1 !== 1) f(`${h1} H1 statt genau einer`);
    if (!zeile.og) h('OG-Titel oder -Bild fehlt');
    if (zeile.canonical === 'eigen' && !inSitemap.length) f('indexierbar, aber in keiner Sitemap');
  } else if (inSitemap.length) f(`noindex, steht aber in ${inSitemap.join(', ')}`);
  if (zeitwort) h(`zeitrelatives Wort im Artikeltext: "${zeitwort}"`);
}
for (const u of sitemapUrls.keys()) if (!bekannt.has(u)) befunde.push({ route: u, schwere: 'fehler', text: 'Sitemap-Eintrag ohne Seite' });

const fehlerZahl = befunde.filter((b) => b.schwere === 'fehler').length;
writeFileSync(join(wurzel, 'qa', 'routen.json'), JSON.stringify({ stand: new Date().toISOString().slice(0, 10), routen: matrix.length, fehler: fehlerZahl, hinweise: befunde.length - fehlerZahl, befunde, matrix }, null, 1) + '\n');

if (bericht) {
  const nachTyp = {};
  for (const z of matrix) nachTyp[z.typ] = (nachTyp[z.typ] || 0) + 1;
  const ok = (z) => (befunde.some((b) => b.route === z.route && b.schwere === 'fehler') ? 'Fehler' : befunde.some((b) => b.route === z.route) ? 'Hinweis' : 'ok');
  const md = [`# Site-Audit (Routenmatrix)`, '', `Stand ${new Date().toISOString().slice(0, 10)}, erzeugt von \`node qa/routen.mjs --bericht\`. ${matrix.length} HTML-Routen, ${fehlerZahl} Fehler, ${befunde.length - fehlerZahl} Hinweise.`, '',
    `Seitentypen: ${Object.entries(nachTyp).map(([k, v]) => `${k} ${v}`).join(', ')}.`, '',
    '| Route | Typ | Index | Title | H1 | Canonical | Bilder | Links | Sitemap | Ergebnis |', '|---|---|---|---|---|---|---|---|---|---|',
    ...matrix.map((z) => `| \`${z.route}\` | ${z.typ} | ${z.index ? 'ja' : 'noindex'} | ${z.title ? 'ok' : 'fehlt'} | ${z.h1} | ${z.canonical} | ${z.bilder}${z.ohneAlt ? ` (${z.ohneAlt} ohne alt)` : ''} | ${z.links}${z.tot.length ? ` (${z.tot.length} tot)` : ''} | ${z.sitemap.length ? z.sitemap.join(', ') : '–'} | ${ok(z)} |`),
    '', '## Befunde', '', ...(befunde.length ? befunde.map((b) => `- ${b.schwere === 'fehler' ? '**Fehler**' : 'Hinweis'} \`${b.route}\`: ${b.text}`) : ['Keine.'])];
  writeFileSync(join(wurzel, 'docs', 'SITE-AUDIT.md'), md.join('\n') + '\n');
}

if (!nurJson) {
  for (const b of befunde) console.log(`${b.schwere === 'fehler' ? 'FEHLER ' : 'Hinweis'} ${b.route}: ${b.text}`);
  console.log(`\nRouten: ${matrix.length}, ${fehlerZahl} Fehler, ${befunde.length - fehlerZahl} Hinweise.`);
}
process.exit(fehlerZahl ? 1 : 0);
