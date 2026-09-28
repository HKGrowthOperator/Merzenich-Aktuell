#!/usr/bin/env node
/**
 * Sitemaps fuer Seiten und Verzeichnis, dazu der Sitemap-Index (Audit
 * 28.09.2026: sitemap-seiten.xml und sitemap.xml waren von Hand gepflegt,
 * 46 indexierbare Seiten fehlten, lastmod stand ueberall auf dem 16.09.).
 *
 * - Jede HTML-Seite unter chatgpt-site/ mit eigenem Canonical und ohne
 *   noindex kommt in eine Sitemap. Artikel (sitemap-artikel.xml, inhaltsindex)
 *   und Termine (sitemap-termine.xml, termine.mjs) haben eigene Generatoren.
 *   Vereinsprofile stehen in sitemap-verzeichnis.xml, alles andere in
 *   sitemap-seiten.xml. Interne Bereiche (admin, redaktion,
 *   redaktionshandbuch) werden nicht betrachtet.
 * - lastmod ist der Zeitpunkt der letzten inhaltlichen Aenderung: der Hauptteil
 *   der Seite ohne Werbeflaechen, Versionsparameter und Datumszeile wird
 *   gehasht (deploy/sitemap-stand.json). Aendert sich der Hash, gilt die
 *   Bauzeit als neuer Stand; sonst bleibt der alte. Ein zweiter Lauf aendert
 *   nichts.
 * - sitemap.xml (Index) nennt je Teil-Sitemap das juengste lastmod darin.
 * Aufruf: node deploy/sitemaps.mjs [--check]
 */
import { readFileSync, writeFileSync, readdirSync, statSync, existsSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { SITE_URL } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');
const INTERN = ['admin', 'redaktion', 'redaktionshandbuch'];
const STAND_DATEI = join(wurzel, 'deploy', 'sitemap-stand.json');
const stand = existsSync(STAND_DATEI) ? JSON.parse(readFileSync(STAND_DATEI, 'utf8')) : { hinweis: '', seiten: {} };
// Bauzeit auf die Minute, Europe/Berlin (Sommerzeit bis Ende Oktober 2026).
const jetzt = (() => { const d = new Date(); const f = new Intl.DateTimeFormat('sv-SE', { timeZone: 'Europe/Berlin', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false }).format(d).replace(' ', 'T'); const off = /GMT([+-]\d+)/.exec(new Intl.DateTimeFormat('en-US', { timeZone: 'Europe/Berlin', timeZoneName: 'shortOffset' }).format(d)); const h = off ? Number(off[1]) : 2; return `${f}:00${h >= 0 ? '+' : '-'}${String(Math.abs(h)).padStart(2, '0')}:00`; })();
const geaendert = [];
const schreibe = (rel, inhalt) => {
  const pfad = join(wurzel, rel);
  if (existsSync(pfad) && readFileSync(pfad, 'utf8') === inhalt) return;
  geaendert.push(rel);
  if (!nurPruefen) writeFileSync(pfad, inhalt);
};

const artikel = new Set((JSON.parse(readFileSync(join(site, 'api', 'inhalte.json'), 'utf8')).artikel || []).map((a) => a.url));
const seiten = [];
(function lauf(d) {
  for (const e of readdirSync(d)) {
    const p = join(d, e);
    if (statSync(p).isDirectory()) { if (!(d === site && INTERN.includes(e))) lauf(p); continue; }
    if (e !== 'index.html') continue;
    const route = p.slice(site.length).replace(/\\/g, '/').slice(0, -'index.html'.length);
    const html = readFileSync(p, 'utf8');
    const robots = (/<meta name="robots" content="([^"]*)"/.exec(html) || [])[1] || '';
    const canonical = (/<link rel="canonical" href="([^"]+)"/.exec(html) || [])[1] || '';
    if (/noindex/i.test(robots) || canonical !== SITE_URL + route) continue;
    if (artikel.has(route)) continue;
    if (/^\/termine\/[^/]+\/$/.test(route) && route !== '/termine/melden/') continue;
    const main = html.slice(Math.max(0, html.indexOf('<main')), html.indexOf('</main>') > 0 ? html.indexOf('</main>') : html.length)
      .replace(/<!-- werbung:([a-z0-9-]+):start -->[\s\S]*?<!-- werbung:\1:end -->/g, '')
      .replace(/\?v=[0-9a-f]+/g, '')
      .replace(/<!--#[^>]*-->/g, '');
    const hash = createHash('sha256').update(main).digest('hex').slice(0, 16);
    const alt = stand.seiten[route];
    // Erster Lauf ohne Stand: Datum des letzten Commits der Seite statt Bauzeit,
    // sonst truege jede Seite beim Einfuehren dasselbe Datum.
    const gitDatum = () => { try { return execFileSync('git', ['log', '-1', '--format=%cI', '--', p], { cwd: wurzel, encoding: 'utf8' }).trim(); } catch { return ''; } };
    const lastmod = alt ? (alt.hash === hash ? alt.lastmod : jetzt) : (gitDatum() || jetzt);
    stand.seiten[route] = { hash, lastmod };
    seiten.push({ route, lastmod, verzeichnis: /^\/vereine\/[^/]+\/$/.test(route) && !['/vereine/meldungen/', '/vereine/eintragen/'].includes(route) });
  }
})(site);
seiten.sort((a, b) => a.route.localeCompare(b.route));
// Verschwundene Seiten aus dem Stand nehmen.
const vorhanden = new Set(seiten.map((s) => s.route));
for (const r of Object.keys(stand.seiten)) if (!vorhanden.has(r)) delete stand.seiten[r];

const prio = (r) => (r === '/' ? '1.0' : /^\/(nachrichten|blaulicht|sport|termine|rathaus|leben|wirtschaft|vereine|merzenich|golzheim|girbelsrath|morschenich|buergewald)\/$/.test(r) ? '0.8' : /^\/thema\//.test(r) || /\/seite\//.test(r) ? '0.4' : '0.5');
const freq = (r) => (r === '/' || /^\/(nachrichten|blaulicht)\//.test(r) ? 'hourly' : /^\/thema\/|\/seite\//.test(r) ? 'weekly' : 'daily');
const urlset = (liste) => `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${liste.map((s) => `<url><loc>${SITE_URL}${s.route}</loc><lastmod>${s.lastmod}</lastmod><changefreq>${freq(s.route)}</changefreq><priority>${prio(s.route)}</priority></url>`).join('\n')}\n</urlset>\n`;
schreibe('chatgpt-site/sitemap-seiten.xml', urlset(seiten.filter((s) => !s.verzeichnis)));
schreibe('chatgpt-site/sitemap-verzeichnis.xml', urlset(seiten.filter((s) => s.verzeichnis)));
stand.hinweis = 'Inhaltsstand je Seite fuer lastmod in sitemap-seiten.xml und sitemap-verzeichnis.xml (deploy/sitemaps.mjs). Nicht von Hand pflegen.';
schreibe('deploy/sitemap-stand.json', JSON.stringify(stand, null, 1) + '\n');

// Index: juengstes lastmod je Teil-Sitemap.
const teile = ['sitemap-seiten.xml', 'sitemap-artikel.xml', 'sitemap-termine.xml', 'sitemap-verzeichnis.xml', 'news-sitemap.xml'];
const juengstes = (f) => {
  const p = join(site, f);
  const inhalt = existsSync(p) ? (f === 'sitemap-seiten.xml' ? urlset(seiten.filter((s) => !s.verzeichnis)) : f === 'sitemap-verzeichnis.xml' ? urlset(seiten.filter((s) => s.verzeichnis)) : readFileSync(p, 'utf8')) : '';
  const daten = [...inhalt.matchAll(/<(?:lastmod|news:publication_date)>([^<]+)</g)].map((m) => m[1]).filter((d) => !Number.isNaN(Date.parse(d)));
  return daten.sort((a, b) => Date.parse(b) - Date.parse(a))[0] || '';
};
schreibe('chatgpt-site/sitemap.xml', `<?xml version="1.0" encoding="UTF-8"?>\n<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${teile.filter((f) => existsSync(join(site, f))).map((f) => { const l = juengstes(f); return `<sitemap><loc>${SITE_URL}/${f}</loc>${l ? `<lastmod>${l}</lastmod>` : ''}</sitemap>`; }).join('\n')}\n</sitemapindex>\n`);

// build-report.json: Bestand nach dem Lauf (Audit 28.09.2026: der Bericht stand
// seit dem 16.09. auf 42 Artikeln). Ohne Bauzeit, damit ein zweiter Lauf
// nichts aendert; "stand" ist die juengste Meldung.
{
  const idx = JSON.parse(readFileSync(join(site, 'api', 'inhalte.json'), 'utf8'));
  const zaehle = (datei) => (existsSync(join(site, datei)) ? (readFileSync(join(site, datei), 'utf8').match(/<url>/g) || []).length : 0);
  const html = [];
  (function lauf(d) { for (const e of readdirSync(d)) { const p = join(d, e); if (statSync(p).isDirectory()) { if (!(d === site && INTERN.includes(e))) lauf(p); } else if (e.endsWith('.html')) html.push(p); } })(site);
  const termineSeiten = readdirSync(join(site, 'termine')).filter((e) => existsSync(join(site, 'termine', e, 'index.html')) && e !== 'melden');
  const report = {
    hinweis: 'Bestand der ausgelieferten Seite, erzeugt von deploy/sitemaps.mjs am Ende von deploy/kette.mjs.',
    stand: idx.generated,
    htmlSeiten: html.length,
    artikel: idx.anzahl,
    termine: termineSeiten.length,
    vereine: seiten.filter((x) => x.verzeichnis).length,
    orte: 5,
    themen: readdirSync(join(site, 'thema')).filter((e) => existsSync(join(site, 'thema', e, 'index.html'))).length,
    sitemaps: { seiten: seiten.filter((x) => !x.verzeichnis).length, artikel: zaehle('sitemap-artikel.xml'), termine: zaehle('sitemap-termine.xml'), verzeichnis: seiten.filter((x) => x.verzeichnis).length, news: zaehle('news-sitemap.xml') },
  };
  schreibe('chatgpt-site/build-report.json', JSON.stringify(report, null, 1) + '\n');
}

console.log(`Sitemaps: ${seiten.length} Seiten (${seiten.filter((s) => s.verzeichnis).length} im Verzeichnis); ${geaendert.length} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}.`);
if (nurPruefen && geaendert.length) process.exit(2);
