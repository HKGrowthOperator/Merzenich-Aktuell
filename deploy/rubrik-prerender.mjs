#!/usr/bin/env node
/**
 * Sammelseiten der roten Menuetitel (/rubrik/<slug>/, Wunsch Betreiber
 * 15.10.2026): Ein Titel im Aufklappmenue, fuer den es keine eigene Seite gibt
 * (z. B. Blaulicht › Verkehr, Rathaus › Gemeinde), zeigt alle Meldungen mit
 * einem der Themen aus deploy/ressort-menue.json. Die Seiten stehen auf
 * noindex,follow: Die Themenseiten bleiben das, was Suchmaschinen finden sollen.
 *
 * Schreibt chatgpt-site/rubrik/<slug>/index.html (Vorlage /thema/feuerwehr/)
 * und wordpress/theme/merzenich-aktuell/inc/rubriken.json (Route und Abfrage
 * in WordPress, inc/ma21.php). Nicht mehr definierte Rubriken fallen weg.
 * Aufruf: node deploy/rubrik-prerender.mjs [--check]   (vor ressort-menue)
 */
import { readFileSync, writeFileSync, mkdirSync, existsSync, readdirSync, rmSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { artikelSammeln, esc, feedZeile, SITE_URL, rubrikenAusMenue, rubrikTrifft } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const site = join(wurzel, 'chatgpt-site');
const quelle = JSON.parse(readFileSync(join(wurzel, 'deploy', 'ressort-menue.json'), 'utf8'));
const vorlage = readFileSync(join(site, 'thema', 'feuerwehr', 'index.html'), 'utf8');
const KATEGORIEN = ['blaulicht', 'sport', 'rathaus', 'leben', 'wirtschaft', 'menschen', 'vereine', 'kultur', 'nachrichten'];

const rubriken = rubrikenAusMenue(quelle);
const fehler = [];
const gesehen = new Set();
for (const d of rubriken) {
  if (!/^[a-z0-9-]+$/.test(d.slug || '')) fehler.push(`Rubrik "${d.titel}": Slug "${d.slug}" ungueltig`);
  if (gesehen.has(d.slug)) fehler.push(`Rubrik ${d.slug} doppelt definiert`);
  gesehen.add(d.slug);
  if (!d.themen.length) fehler.push(`Rubrik ${d.slug}: keine Themen`);
  for (const k of [d.kategorie, ...d.ohneKategorie].filter(Boolean)) if (!KATEGORIEN.includes(k)) fehler.push(`Rubrik ${d.slug}: Kategorie "${k}" unbekannt`);
}
if (fehler.length) { console.error('Rubriken:\n  ' + fehler.join('\n  ')); process.exit(1); }

const artikel = artikelSammeln(site);
const zaehler = (n) => `<p class="count-line">${n} Meldung${n === 1 ? '' : 'en'}</p>`;

function seite(d, liste) {
  const pfad = `/rubrik/${d.slug}/`;
  const titel = `${d.titel} · ${d.ressortTitel}`;
  // Wie alle noindex-Seiten der Vorschau: eigene Adresse (kopf-theme-einbinden
  // setzt die Live-Adresse nur bei indexierbaren Seiten).
  const url = SITE_URL + pfad;
  let kopf = vorlage.slice(0, vorlage.indexOf('<main'));
  kopf = kopf
    .replace(/<title>[^<]*<\/title>/, `<title>${esc(titel)} | Merzenich Aktuell</title>`)
    .replace(/<meta name="description" content="[^"]*">/, `<meta name="description" content="${esc(d.beschreibung)}">`)
    .replace(/<link rel="canonical" href="[^"]*">/, `<link rel="canonical" href="${esc(url)}">`)
    .replace(/<meta name="robots" content="[^"]*">/, '<meta name="robots" content="noindex,follow">')
    .replace(/<meta property="og:title" content="[^"]*">/, `<meta property="og:title" content="${esc(titel)}">`)
    .replace(/<meta property="og:description" content="[^"]*">/, `<meta property="og:description" content="${esc(d.beschreibung)}">`)
    .replace(/<meta property="og:url" content="[^"]*">/, `<meta property="og:url" content="${esc(url)}">`)
    .replace(/<meta name="twitter:title" content="[^"]*">/, `<meta name="twitter:title" content="${esc(titel)}">`)
    .replace(/<meta name="twitter:description" content="[^"]*">/, `<meta name="twitter:description" content="${esc(d.beschreibung)}">`)
    // Sammlung und Brotkrumen der Themenseite gelten hier nicht (Seite ist noindex).
    .replace(/<script type="application\/ld\+json">\{"@context":"https:\/\/schema\.org","@type":"(?:CollectionPage|BreadcrumbList)"[\s\S]*?<\/script>\s*/g, '')
    // Markiert ist das Ressort der Sammelseite wie in WordPress (ma21_kopf_aktuell),
    // nicht „Aktuell“ und der Ort der Vorlage. Wie auf allen statischen Seiten in
    // Leiste und Schublade; das Mehr-Menü baut kopf-theme-einbinden.mjs ohne Markierung.
    // (Die Ortsauswahl behält ihre Markierung, die pflegt inhaltsindex.mjs auf allen Seiten.)
    .replace(/<div class="navscroll">[\s\S]*?<\/div>|<div class="drawer-group"><div class="grp">Ressorts<\/div>[\s\S]*?<\/div>/g,
      (b) => b.replace(/ aria-current="page"/g, '').split(`<a href="${d.ressort}">`).join(`<a href="${d.ressort}" aria-current="page">`));
  let rumpf = vorlage.slice(vorlage.indexOf('<main'));
  const kopfzeile = `<div class="page-head"><div class="shell"><nav class="crumbs" aria-label="Brotkrumen"><a href="/">Start</a><span class="sep">›</span><a href="${esc(d.ressort)}">${esc(d.ressortTitel)}</a><span class="sep">›</span><span aria-current="page">${esc(d.titel)}</span></nav><span class="eyebrow">${esc(d.ressortTitel)}</span><h1>${esc(d.titel)}</h1><p class="desc">${esc(d.beschreibung)}</p>${zaehler(liste.length)}</div></div>`;
  rumpf = rumpf.replace(/<div class="page-head">[\s\S]*?<\/div><\/div>/, () => kopfzeile);
  const zeilen = liste.length ? liste.map(feedZeile).join('\n      ') : '<p class="leer">Zu diesem Bereich gibt es noch keine Meldungen.</p>';
  rumpf = rumpf.replace(/(<div class="feed">)[\s\S]*?(<\/div>\s*<aside class="sidebar">)/, (m, a, z) => `${a}\n      ${zeilen}\n    ${z}`);
  return kopf + rumpf;
}

const geaendert = [];
const zahlen = {};
for (const d of rubriken) {
  const liste = artikel.filter((a) => rubrikTrifft(d, a));
  zahlen[d.slug] = liste.length;
  const ordner = join(site, 'rubrik', d.slug), pfad = join(ordner, 'index.html');
  const html = seite(d, liste);
  if (existsSync(pfad) && readFileSync(pfad, 'utf8') === html) continue;
  geaendert.push(`/rubrik/${d.slug}/`);
  if (!nurPruefen) { mkdirSync(ordner, { recursive: true }); writeFileSync(pfad, html); }
}
// Rubriken, die es im Menue nicht mehr gibt, fallen weg.
const basis = join(site, 'rubrik');
if (existsSync(basis)) for (const slug of readdirSync(basis)) {
  if (gesehen.has(slug) || slug === 'index.html') continue;
  geaendert.push(`/rubrik/${slug}/ (entfernt)`);
  if (!nurPruefen) rmSync(join(basis, slug), { recursive: true, force: true });
}

// Themenseiten, die ein roter Titel öffnet (Blaulicht › Feuerwehr): Ressort für Markierung,
// Brotkrumen und Dachzeile wie bei den Sammelseiten (Vorführ-Check 15.10.2026). Erstes Ressort gewinnt.
const themenRessort = {};
for (const r of quelle.ressorts || []) for (const p of r.punkte || []) {
  const m = /^\/thema\/([a-z0-9-]+)\/$/.exec(p.href || '');
  if (m && !themenRessort[m[1]]) themenRessort[m[1]] = { ressort: r.href, ressortTitel: r.titel };
}

// WordPress liest dieselben Definitionen (Route /rubrik/<slug>/, inc/ma21.php).
const wp = join(wurzel, 'wordpress', 'theme', 'merzenich-aktuell', 'inc', 'rubriken.json');
const wpJson = JSON.stringify({
  hinweis: 'Erzeugt von deploy/rubrik-prerender.mjs aus deploy/ressort-menue.json. Nicht von Hand bearbeiten.',
  rubriken: Object.fromEntries(rubriken.map((d) => [d.slug, { titel: d.titel, ressort: d.ressort, ressortTitel: d.ressortTitel, themen: d.themen, kategorie: d.kategorie, ohneKategorie: d.ohneKategorie, beschreibung: d.beschreibung }])),
  themenRessort,
}, null, 2) + '\n';
if (!existsSync(wp) || readFileSync(wp, 'utf8') !== wpJson) { geaendert.push('inc/rubriken.json'); if (!nurPruefen) writeFileSync(wp, wpJson); }

const leer = Object.entries(zahlen).filter(([, n]) => !n).map(([s]) => s);
console.log(`Rubriken: ${rubriken.length} Sammelseiten (${Object.entries(zahlen).map(([s, n]) => `${s} ${n}`).join(', ')})${leer.length ? `; ohne Meldung (im Menue ausgeblendet): ${leer.join(', ')}` : ''}; ${geaendert.length} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}.`);
if (nurPruefen && geaendert.length) process.exit(2);
