#!/usr/bin/env node
/* Merzenich Aktuell – statischer Generator ohne Abhängigkeiten.
   node build.mjs            → schreibt dist/
   IMAGE_CDN=off node build.mjs → ohne Netlify Image CDN (lokale Vorschau)
   BUILD_NOW=2026-09-04T09:00:00+02:00 → festes "Jetzt" (reproduzierbare Builds) */
import { mkdirSync, writeFileSync, rmSync, cpSync, existsSync, readFileSync, readdirSync, statSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { loadContent } from './src/lib/content.mjs';
import { slugify, hash, fmt, isoLocal, stripHtml, esc } from './src/lib/util.mjs';
import * as P from './src/templates/pages.mjs';
import * as F from './src/lib/feeds.mjs';

const ROOT = dirname(fileURLToPath(import.meta.url));
const SRC = join(ROOT, 'src'), CONTENT = join(ROOT, 'content'), DIST = join(ROOT, 'dist');
const now = process.env.BUILD_NOW ? new Date(process.env.BUILD_NOW) : new Date();
const PER_PAGE = 12;

const cfg = {
  imageCdn: process.env.IMAGE_CDN !== 'off',
  // Freigabeliste der Bild-CDN. Was hier steht, darf ueber die eigene Domain
  // ausgeliefert werden, deshalb nur Hosts, die der Inhalt wirklich nutzt.
  remoteImageHosts: ['feuerwehr-merzenich.de', 'gemeinde-merzenich.de', 'tourismus.kreis-dueren.de']
};

const t0 = Date.now();
const data = loadContent(CONTENT, now);
const { site } = data;
const fussball = existsSync(join(CONTENT, 'daten/fussball.json')) ? JSON.parse(readFileSync(join(CONTENT, 'daten/fussball.json'), 'utf8')) : null;

/* ---------- Kontext für Templates ---------- */
const articles = data.allArticles.filter(a => !a.noindex || true); // alle veröffentlichten
const ctx = {
  ...data, cfg, now, articles, fussball,
  stand: articles.reduce((m, a) => { const d = a.updated || a.date; return d > m ? d : m; }, new Date(0)),
  assetHash: hash(readFileSync(join(SRC, 'assets/style.css'), 'utf8') + readFileSync(join(SRC, 'assets/app.js'), 'utf8')),
  fileSize: (p) => { try { return statSync(join(SRC, String(p).replace(/^\/?/, ''))).size; } catch { return 0; } },
  eilmeldung: articles.find(a => !a.undated && (a.format === 'eilmeldung' || a.eilmeldung) && (now - a.date) < 48 * 3600 * 1000) || null,
  topTags: data.tags.slice(0, 12),
  placeName: (s) => s === 'gemeinde' ? 'Gemeinde' : (data.placeMap.get(s)?.name || ''),
  placeBy: (s) => data.placeMap.get(s) || null,
  authorBy: (s) => data.authors.find(a => a.slug === s) || null,
  eventBy: (id) => data.allEvents.find(e => e.id === id || e.slug === id) || null,
  articleBy: (id) => articles.find(a => a.id === id || a.slug === id) || null,
  countByOrt: (s) => articles.filter(a => a.ort === s).length,
  tagSlug: slugify,
  related(a, n) {
    const score = x => (x.ort && x.ort === a.ort ? 2 : 0) + (x.ressort === a.ressort ? 1 : 0) + x.tags.filter(t => a.tags.includes(t)).length * 2;
    return articles.filter(x => x !== a).map(x => [score(x), x]).sort((p, q) => q[0] - p[0] || q[1].date - p[1].date).slice(0, n).map(p => p[1]);
  }
};
if (ctx.stand.getTime() === 0) ctx.stand = now;

/* ---------- Helfer ---------- */
const written = [];
function out(path, content) {
  const full = join(DIST, path.replace(/^\//, ''));
  mkdirSync(dirname(full), { recursive: true });
  writeFileSync(full, content);
  written.push(path);
}
function page(url, html) { out(url.endsWith('/') ? url + 'index.html' : url, html); }
function paginate(items) { return Math.max(1, Math.ceil(items.length / PER_PAGE)); }

rmSync(DIST, { recursive: true, force: true });
mkdirSync(DIST, { recursive: true });

/* ---------- Assets ---------- */
cpSync(join(SRC, 'assets'), join(DIST, 'assets'), { recursive: true });
if (existsSync(join(SRC, 'admin'))) cpSync(join(SRC, 'admin'), join(DIST, 'admin'), { recursive: true });

/* Platzhalter-Grafiken je Ressort (ehrlich gekennzeichnet, keine KI-Fotos) */
const PH_LABELS = { nachrichten: 'Meldung', blaulicht: 'Blaulicht', sport: 'Sport', rathaus: 'Rathaus', vereine: 'Vereine', leben: 'Leben', wirtschaft: 'Wirtschaft', menschen: 'Menschen', termine: 'Termin' };
for (const [k, label] of Object.entries(PH_LABELS)) {
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 900" width="1600" height="900"><rect width="1600" height="900" fill="#141210"/><path d="M0 460h700l14-8 12 10 18-16 14 20 20-28 14 32 18-18 22-14 18 30 14-10h722" fill="none" stroke="#c8a24e" stroke-width="3" stroke-linejoin="round"/><text x="800" y="410" text-anchor="middle" font-family="Georgia,serif" font-size="64" fill="#e8e4da" letter-spacing="2">${label}</text><text x="800" y="560" text-anchor="middle" font-family="Helvetica,Arial,sans-serif" font-size="22" fill="#8f8a7e" letter-spacing="6">MERZENICH AKTUELL · KEIN FOTO ZUM EREIGNIS</text></svg>`;
  out(`/assets/img/ph-${k}.svg`, svg);
}

/* ---------- Seiten ---------- */
page('/', P.homePage(ctx));

// Ressorts (nachrichten = alle)
const ressortPaginationUrls = [];
for (const [key, r] of Object.entries(site.ressorts)) {
  const items = key === 'nachrichten' ? articles : articles.filter(a => a.ressort === key);
  const pages = paginate(items);
  for (let p = 1; p <= pages; p++) {
    const slice = items.slice((p - 1) * PER_PAGE, p * PER_PAGE);
    const html = P.listPage(ctx, { title: r.long, eyebrow: r.name, desc: r.desc, items: slice, base: `/${key}/`, page: p, pages, count: items.length, nav: `/${key}/`, crumbs: [{ name: 'Start', url: '/' }, { name: r.name, url: `/${key}/` }], feed: `/${key}/feed.xml` });
    page(p === 1 ? `/${key}/` : `/${key}/seite/${p}/`, html);
    if (p > 1) ressortPaginationUrls.push({ loc: `/${key}/seite/${p}/`, lastmod: ctx.stand, changefreq: 'daily', priority: 0.5 });
  }
  out(`/${key}/feed.xml`, F.rss(site, ctx, { title: `${site.name} – ${r.name}`, url: `/${key}/`, items, description: r.desc, self: `/${key}/feed.xml` }));
}
// Vereinsmeldungen (Unterseite des Vereinsverzeichnisses)
{
  const items = articles.filter(a => a.ressort === 'vereine' || a.ressort === 'sport');
  page('/vereine/meldungen/', P.listPage(ctx, { title: 'Meldungen aus den Vereinen', eyebrow: 'Vereine', desc: site.ressorts.vereine.desc, items: items.slice(0, PER_PAGE), base: '/vereine/meldungen/', page: 1, pages: 1, count: items.length, nav: '/vereine/', crumbs: [{ name: 'Start', url: '/' }, { name: 'Vereine', url: '/vereine/' }, { name: 'Meldungen' }] }));
}

// Artikel
for (const a of articles) page(a.url, P.articlePage(ctx, a));

// Ortsteile
const placePaginationUrls = [];
for (const pl of data.places) {
  const items = articles.filter(a => a.ort === pl.slug);
  const pages = paginate(items);
  for (let p = 1; p <= pages; p++) {
    page(p === 1 ? pl.url : `${pl.url}seite/${p}/`, P.placePage(ctx, pl, items.slice((p - 1) * PER_PAGE, p * PER_PAGE), p, pages));
    if (p > 1) placePaginationUrls.push({ loc: `${pl.url}seite/${p}/`, lastmod: ctx.stand, changefreq: 'daily', priority: 0.5 });
  }
  out(`${pl.url}feed.xml`, F.rss(site, ctx, { title: `${site.name} – ${pl.name}`, url: pl.url, items, description: `Meldungen aus ${pl.name}`, self: `${pl.url}feed.xml` }));
}

// Termine
page('/termine/', P.eventsPage(ctx));
out('/termine/kalender.ics', F.ics(site, data.allEvents.filter(e => e.start > new Date(now.getTime() - 90 * 86400000))));
out('/termine/feed.xml', F.eventsRss(site, ctx, data.events));
for (const e of data.allEvents) { page(e.url, P.eventPage(ctx, e)); out(`${e.url}termin.ics`, F.ics(site, [e], { name: e.title })); }

// Vereine, Betriebe
page('/vereine/', P.directoryPage(ctx, 'vereine'));
page('/betriebe/', P.directoryPage(ctx, 'betriebe'));
for (const c of data.clubs) page(c.url, P.entityPage(ctx, c, 'vereine'));
for (const b of data.businesses) page(b.url, P.entityPage(ctx, b, 'betriebe'));

// Autoren, Themen, Archiv, Suche
for (const au of data.authors) page(au.url, P.authorPage(ctx, au));
page('/thema/', P.tagsIndex(ctx, data.tags));
for (const t of data.tags) page(t.url, P.tagPage(ctx, t, t.items));
page('/archiv/', P.archivePage(ctx));
page('/suche/', P.searchPage(ctx));

// Statische Seiten aus content/seiten
const pageBySlug = new Map(data.pages.map(p => [p.slug, p]));
for (const p of data.pages) {
  if (p.type === 'danke') page(p.url, P.thanksPage(ctx, p));
  else page(p.url, P.staticPage(ctx, buildStatic(p)));
}
function buildStatic(p) {
  const x = { ...p };
  if (p.slug === 'ueber-uns') {
    x.after = `<div class="team-grid">${data.authors.map(au => `<article class="team-card"><span class="avatar">${esc(au.initials)}</span><div><b><a href="${au.url}">${esc(au.name)}</a></b><span>${esc(au.role || '')}</span><p>${esc(au.bio || '')}</p>${au.email ? `<a href="mailto:${esc(au.email)}">${esc(au.email)}</a>` : ''}</div></article>`).join('')}</div>` + (p.form ? '' : '');
    x.jsonld = [{ '@context': 'https://schema.org', '@type': 'AboutPage', name: p.title, url: site.url + p.url, mainEntity: { '@id': site.url + '/#organization' } }];
  }
  if (p.slug === 'service') {
    x.sideHtml = '';
  }
  return x;
}
if (fussball) page('/sc-1919-merzenich/', P.fussballPage(ctx));
page('/jobs/', P.jobsPage(ctx));
for (const j of data.jobs) page(j.url, P.jobPage(ctx, j));
page('/404.html', P.notFoundPage(ctx));
page('/offline/', P.offlinePage(ctx));

/* ---------- Feeds, Sitemaps ---------- */
out('/feed.xml', F.rss(site, ctx, { title: site.name, url: '/', items: articles, description: site.claim, self: '/feed.xml' }));
out('/atom.xml', F.atom(site, ctx, articles));
out('/feed.json', F.jsonFeed(site, ctx, articles));
out('/news-sitemap.xml', F.newsSitemap(site, articles, now));
out('/sitemap-artikel.xml', F.sitemap(site, articles.filter(a => !a.noindex).map(a => ({ loc: a.url, lastmod: a.updated || (a.undated ? a.retrieved || a.date : a.date), changefreq: 'weekly', priority: 0.8, image: a.image && a.image.src ? a.image : null }))));
out('/sitemap-seiten.xml', F.sitemap(site, [
  { loc: '/', lastmod: ctx.stand, changefreq: 'hourly', priority: 1.0 },
  ...Object.keys(site.ressorts).map(k => ({ loc: `/${k}/`, lastmod: ctx.stand, changefreq: 'daily', priority: 0.9 })),
  ...data.places.map(p => ({ loc: p.url, lastmod: ctx.stand, changefreq: 'daily', priority: 0.9 })),
  { loc: '/termine/', lastmod: now, changefreq: 'daily', priority: 0.9 },
  ...(fussball ? [{ loc: '/sc-1919-merzenich/', lastmod: fussball.stand, changefreq: 'weekly', priority: 0.8 }] : []),
  { loc: '/vereine/meldungen/', lastmod: ctx.stand, changefreq: 'weekly', priority: 0.5 },
  { loc: '/jobs/', lastmod: now, changefreq: 'daily', priority: 0.6 }, ...data.jobs.map(j => ({ loc: j.url, lastmod: j.posted, changefreq: 'weekly', priority: 0.5 })), { loc: '/betriebe/', lastmod: now, changefreq: 'weekly', priority: 0.7 },
  { loc: '/archiv/', lastmod: ctx.stand, changefreq: 'daily', priority: 0.4 }, { loc: '/thema/', lastmod: ctx.stand, changefreq: 'weekly', priority: 0.4 },
  ...data.tags.map(t => ({ loc: t.url, lastmod: t.items[0].date, changefreq: 'weekly', priority: 0.4 })),
  ...data.authors.map(a => ({ loc: a.url, changefreq: 'monthly', priority: 0.5 })),
  ...data.pages.filter(p => !p.noindex && p.type !== 'danke').map(p => ({ loc: p.url, changefreq: 'monthly', priority: 0.5 })),
  ...ressortPaginationUrls, ...placePaginationUrls
]));
out('/sitemap-termine.xml', F.sitemap(site, data.allEvents.map(e => ({ loc: e.url, lastmod: e.updated || e.created || e.start, changefreq: 'weekly', priority: 0.6 }))));
out('/sitemap-verzeichnis.xml', F.sitemap(site, [...data.clubs, ...data.businesses].map(x => ({ loc: x.url, lastmod: x.updated, changefreq: 'monthly', priority: 0.5 }))));
out('/sitemap.xml', F.sitemapIndex(site, ['sitemap-seiten.xml', 'sitemap-artikel.xml', 'sitemap-termine.xml', 'sitemap-verzeichnis.xml', 'news-sitemap.xml'], now));

/* ---------- Suchindex ---------- */
const index = [
  ...articles.map(a => ({ t: a.title, d: a.teaser, u: a.url, k: site.ressorts[a.ressort]?.name || '', g: [ctx.placeName(a.ort), ...a.tags].filter(Boolean).join(' '), b: stripHtml(a.html).slice(0, 600), dt: a.undated ? 'o. D.' : fmt.short(a.date), typ: 'Meldung' })),
  ...data.allEvents.map(e => ({ t: e.title, d: `${fmt.date(e.start)} · ${e.location || ''}`, u: e.url, k: 'Termin', g: [ctx.placeName(e.ort), e.organizer, e.category].filter(Boolean).join(' '), b: e.teaser || '', dt: fmt.short(e.start), typ: 'Termin' })),
  ...data.clubs.map(c => ({ t: c.name, d: c.description, u: c.url, k: 'Verein', g: [ctx.placeName(c.ort), c.category].filter(Boolean).join(' '), b: '', dt: '', typ: 'Verein' })),
  ...data.businesses.map(b => ({ t: b.name, d: b.description, u: b.url, k: 'Betrieb', g: [ctx.placeName(b.ort), b.category].filter(Boolean).join(' '), b: '', dt: '', typ: 'Betrieb' })),
  ...data.jobs.map(j => ({ t: j.title, d: `${j.betrieb} · ${j.art || 'Stelle'}`, u: j.url, k: 'Job', g: [ctx.placeName(j.ort), j.betrieb].filter(Boolean).join(' '), b: j.description, dt: fmt.short(j.posted), typ: 'Stelle' })),
  ...data.places.map(p => ({ t: `Leben in ${p.name}`, d: p.description, u: p.url, k: 'Ortsteil', g: p.name, b: '', dt: '', typ: 'Ortsteil' }))
];
out('/suche-index.json', JSON.stringify(index));
out('/api/latest.json', JSON.stringify({ generated: isoLocal(now), stand: isoLocal(ctx.stand), items: articles.slice(0, 20).map(a => ({ title: a.title, url: site.url + a.url, date: isoLocal(a.date), ressort: a.ressort, ort: a.ort, teaser: a.teaser, image: a.image && a.image.src ? (a.image.src.startsWith('http') ? a.image.src : site.url + a.image.src) : null })), events: data.events.slice(0, 20).map(e => ({ title: e.title, url: site.url + e.url, start: isoLocal(e.start), location: e.location, ort: e.ort })) }, null, 1));

/* ---------- Infrastruktur ---------- */
out('/robots.txt', `User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /*/danke/\n\nSitemap: ${site.url}/sitemap.xml\nSitemap: ${site.url}/news-sitemap.xml\n`);
out('/manifest.webmanifest', JSON.stringify({ name: site.name, short_name: site.shortName, description: site.claim, start_url: '/?source=pwa', scope: '/', display: 'standalone', background_color: '#0c0b0a', theme_color: '#0c0b0a', lang: 'de', categories: ['news'], icons: [{ src: '/assets/img/avatar-1024.png', sizes: '1024x1024', type: 'image/png', purpose: 'any' }, { src: '/assets/img/avatar-1024.png', sizes: '1024x1024', type: 'image/png', purpose: 'maskable' }], shortcuts: [{ name: 'Blaulicht', url: '/blaulicht/' }, { name: 'Termine', url: '/termine/' }, { name: 'Meldung senden', url: '/meldung-senden/' }] }, null, 1));
out('/sw.js', readFileSync(join(SRC, 'sw.js'), 'utf8').replace('__VERSION__', ctx.assetHash + '-' + hash(String(ctx.stand.getTime()))));
out('/humans.txt', `/* TEAM */\n${data.authors.map(a => `${a.role || 'Redaktion'}: ${a.name}`).join('\n')}\nKontakt: ${site.email}\n\n/* SITE */\nStand: ${isoLocal(ctx.stand)}\nTechnik: statischer Generator ohne Abhängigkeiten, Netlify, Decap CMS\nSchriften: Newsreader, Inter (SIL OFL)\n`);

// Legacy-Weiterleitungen (v8-Dateinamen) + Kurzlinks
const legacy = {};
for (const a of articles) if (a.legacy) for (const l of [].concat(a.legacy)) legacy[l] = a.url;
const redirects = [
  ...Object.entries(legacy).map(([from, to]) => `${from} ${to} 301`),
  '/index.html / 301', '/nachrichten.html /nachrichten/ 301', '/blaulicht.html /blaulicht/ 301', '/sport.html /sport/ 301', '/rathaus.html /rathaus/ 301', '/vereine.html /vereine/ 301', '/stadtleben.html /leben/ 301', '/wirtschaft.html /wirtschaft/ 301', '/veranstaltungen.html /termine/ 301', '/veranstaltungen /termine/ 301',
  ...data.places.map(p => `/${p.slug}.html ${p.url} 301`),
  '/suche.html /suche/ 301', '/archiv.html /archiv/ 301', '/newsletter.html /whatsapp/ 301', '/redaktion.html /meldung-senden/ 301', '/werben.html /werben/ 301', '/quellen.html /grundsaetze/ 301', '/impressum.html /impressum/ 301', '/datenschutz.html /datenschutz/ 301',
  '/rss /feed.xml 301', '/feed /feed.xml 301', '/aktuell /nachrichten/ 301', '/kalender /termine/ 301', '/stellen /jobs/ 301', '/stellenmarkt /jobs/ 301', '/events /termine/ 301', '/stadtleben/* /leben/:splat 301', '/wa /whatsapp/ 302', '/newsletter /whatsapp/ 301', '/newsletter/* /whatsapp/ 301', '/sc-merzenich.html /sc-1919-merzenich/ 301', '/sc-merzenich /sc-1919-merzenich/ 301',
  '/api/* /api/:splat 200', '/* /404.html 404'
];
out('/_redirects', redirects.join('\n') + '\n');

const headers = `/*
  X-Content-Type-Options: nosniff
  X-Frame-Options: SAMEORIGIN
  Referrer-Policy: strict-origin-when-cross-origin
  Permissions-Policy: geolocation=(), microphone=(), camera=(), interest-cohort=()
  Cross-Origin-Opener-Policy: same-origin-allow-popups
  Strict-Transport-Security: max-age=31536000
/assets/fonts/*
  Cache-Control: public, max-age=31536000, immutable
/assets/img/*
  Cache-Control: public, max-age=2592000
/assets/uploads/*
  Cache-Control: public, max-age=2592000
/assets/*.css
  Cache-Control: public, max-age=31536000, immutable
/assets/*.js
  Cache-Control: public, max-age=31536000, immutable
/sw.js
  Cache-Control: no-cache
/feed.xml
  Content-Type: application/rss+xml; charset=utf-8
  Cache-Control: public, max-age=600
/*/feed.xml
  Content-Type: application/rss+xml; charset=utf-8
  Cache-Control: public, max-age=600
/atom.xml
  Content-Type: application/atom+xml; charset=utf-8
/feed.json
  Content-Type: application/feed+json; charset=utf-8
/news-sitemap.xml
  Cache-Control: public, max-age=300
/termine/kalender.ics
  Content-Type: text/calendar; charset=utf-8
  Cache-Control: public, max-age=3600
/termine/*/termin.ics
  Content-Type: text/calendar; charset=utf-8
/admin/*
  X-Robots-Tag: noindex
  Content-Security-Policy: default-src 'self' https://unpkg.com https://identity.netlify.com https://api.netlify.com https://*.netlify.app https://*.netlify.com; img-src 'self' data: blob: https:; style-src 'self' 'unsafe-inline' https://unpkg.com; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://identity.netlify.com; connect-src 'self' https://identity.netlify.com https://api.netlify.com; frame-src 'self'
`;
out('/_headers', headers);

/* netlify.toml fuer die manuelle Veroeffentlichung in den Build legen.
   Netlify liest beim Drag and drop die Konfiguration aus dem hochgeladenen
   Ordner. Der [images]-Abschnitt MUSS mit, sonst weist die Bild-CDN alle
   externen Quellbilder ab. Der [build]-Abschnitt darf NICHT mit: Netlify
   fuehrt ihn sonst aus und der Build scheitert, weil im hochgeladenen Ordner
   nur das Ergebnis liegt und kein Quellcode. */
{
  const quelle = readFileSync(new URL('netlify.toml', import.meta.url), 'utf8');
  const abschnitte = quelle.split(/^(?=\[)/m);
  const behalten = abschnitte.filter((teil) => {
    const name = (teil.match(/^\[\[?([^\]]+)\]\]?/) || [])[1];
    return !name || !/^build(\.|$)/.test(name.trim());
  });
  out(
    '/netlify.toml',
    '# Erzeugt von build.mjs fuer die Veroeffentlichung per Drag and drop.\n' +
    '# Ohne die Freigabeliste unter [images] weist die Netlify Bild-CDN alle\n' +
    '# externen Quellbilder ab. Nicht von Hand bearbeiten.\n\n' +
    behalten.join('').trim() + '\n'
  );
}

/* ---------- Build-Report ---------- */
const report = {
  generated: isoLocal(now), stand: isoLocal(ctx.stand), files: written.length,
  artikel: articles.length, termine: data.events.length, vergangeneTermine: data.pastEvents.length, vereine: data.clubs.length, betriebe: data.businesses.length, orte: data.places.length, autoren: data.authors.length, seiten: data.pages.length, themen: data.tags.length,
  newsSitemap: articles.filter(a => a.date >= new Date(now.getTime() - 48 * 3600 * 1000)).length,
  warnings: []
};
for (const a of articles) {
  if (!a.image || !a.image.src) report.warnings.push(`Kein Bild: ${a.url}`);
  else if (/fbcdn\.net/.test(a.image.src)) report.warnings.push(`Facebook-CDN-Bild läuft ab, bitte lokal ablegen: ${a.url}`);
  if (a.image && a.image.src) {
    if (!a.image.type) report.warnings.push(`Bild ohne Bildtyp: ${a.url}`);
    if (!a.image.credit) report.warnings.push(`Bild ohne Credit: ${a.url}`);
  }
  if (!a.sources || !a.sources.length) report.warnings.push(`Keine Quelle: ${a.url}`);
  if (a.title.length > 110) report.warnings.push(`Titel länger als 110 Zeichen: ${a.url}`);
  if (a.teaser.length > 200) report.warnings.push(`Teaser länger als 200 Zeichen: ${a.url}`);
  if (a.teaser.length < 60) report.warnings.push(`Teaser kürzer als 60 Zeichen: ${a.url}`);
}
out('/build-report.json', JSON.stringify(report, null, 1));
console.log(`✔ ${written.length} Dateien in ${Date.now() - t0} ms · ${report.artikel} Artikel · ${report.termine} Termine · ${report.vereine} Vereine · ${report.betriebe} Betriebe · Stand ${fmt.dateTime(ctx.stand)}`);
if (report.warnings.length) console.log('Hinweise:\n  ' + report.warnings.join('\n  '));
