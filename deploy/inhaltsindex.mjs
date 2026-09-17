#!/usr/bin/env node
/**
 * Zentraler Inhaltsindex: ein Artikel -> ein Index -> alle Ansichten.
 *
 * Liest alle Artikelseiten (lib-artikel.mjs) und schreibt daraus:
 *   - api/inhalte.json           kanonischer Index
 *   - api/latest.json            items (events bleiben unangetastet)
 *   - /<ressort>/ + /seite/N/    Ressortlisten mit Blaetterung
 *   - /nachrichten/ + /seite/N/  Gesamtarchiv
 *   - /<ortsteil>/               Ortsseiten-Feed
 *   - index.html                 Aufmacher + Nebenmeldungen (Aufmacher und
 *                                zweite Meldung aus api/editorial-current.json)
 *   - feed.xml, atom.xml, feed.json, Ressort-/Ortsfeeds, news-sitemap.xml,
 *     sitemap-artikel.xml
 *   - Sidebox "Neueste Meldungen" auf allen Seiten
 * Vorher wurden diese Ansichten getrennt gepflegt; ein neuer Artikel fehlte
 * dann in Ressort, Archiv, Ort, Feed und auf der Startseite (Audit 17.09.).
 * Idempotent. Aufruf: node deploy/inhaltsindex.mjs [--check]
 */
import { readFileSync, writeFileSync, existsSync, readdirSync, rmSync, mkdirSync, statSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { artikelSammeln, esc, dmyLang, ORTSTEILE, SITE_URL } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const site = join(wurzel, 'chatgpt-site');
const SEITENGROESSE = 12;
const geaendert = []; const geloescht = [];
function schreibe(rel, inhalt) {
  const pfad = join(site, rel);
  const alt = existsSync(pfad) ? readFileSync(pfad, 'utf8') : null;
  if (alt === inhalt) return false;
  geaendert.push(rel);
  if (!nurPruefen) { mkdirSync(dirname(pfad), { recursive: true }); writeFileSync(pfad, inhalt); }
  return true;
}
const x = (s) => String(s ?? '').replace(/[<>&"]/g, (c) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;' }[c]));
const abs = (u) => (u && /^https?:/.test(u) ? u : SITE_URL + u);
const kurzZeit = (iso) => { const d = new Date(iso); return new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit' }).format(d) + ' · ' + new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', hour: '2-digit', minute: '2-digit' }).format(d) + ' Uhr'; };

// ------------------------------------------------------------------ Index
const artikel = artikelSammeln(site);
const neuester = artikel.reduce((m, a) => (a.aktualisiert || a.datum) > m ? (a.aktualisiert || a.datum) : m, '');
const index = { generated: neuester, anzahl: artikel.length, hinweis: 'Kanonischer Inhaltsindex, erzeugt von deploy/inhaltsindex.mjs aus den Artikelseiten. Alle Listen, Feeds und die Startseite werden daraus gebaut.', artikel: artikel.map((a) => ({ id: a.id, url: a.url, titel: a.titel, teaser: a.teaser, kicker: a.kicker, ressort: a.ressort, ressortLabel: a.ressortLabel, ort: a.ort, ortsteil: a.ortsteil, datum: a.datum, aktualisiert: a.aktualisiert || a.datum, lesezeit: a.lesezeit, themen: a.themen, bild: a.bild })) };
schreibe('api/inhalte.json', JSON.stringify(index, null, 1) + '\n');

// ------------------------------------------------------------ Bausteine
const locHtml = (a) => `<div class="location-line"><span class="location-brand">${esc(a.ort)}</span>${a.ortsteilLabel ? ' · ' + esc(a.ortsteilLabel) : ''}</div>`;
const imgHtml = (b, sizes, eager) => `<img src="${esc(b.src)}"${b.srcset ? ` srcset="${esc(b.srcset)}"` : ''} sizes="${sizes}" alt="${esc(b.alt)}"${b.width && b.height ? ` width="${b.width}" height="${b.height}"` : ''} loading="${eager ? 'eager' : 'lazy'}"${eager ? ' fetchpriority="high"' : ''} decoding="async" data-editorial-image class="">`;
const badgeHtml = (b) => (b.badge ? `<span class="badge">${esc(b.badge)}</span>` : '');
const mehr = (a) => `<div class="story-actions"><a class="read-more" href="${esc(a.url)}">Mehr lesen<span class="sr-only">: ${esc(a.titel)}</span></a></div>`;
function leadHtml(a) {
  const b = a.bild;
  return `<article class="feed-lead" data-story="${esc(a.id)}">${b ? `<a href="${esc(a.url)}" tabindex="-1" aria-hidden="true"><div class="media${b.fit ? ' contain' : ''}">${imgHtml(b, '(max-width: 640px) 100vw, 800px', true)}${badgeHtml(b)}</div></a>` : ''}<div class="lead-copy">${locHtml(a)}<span class="kicker">${esc(a.kicker)}</span><h2><a href="${esc(a.url)}">${esc(a.titel)}</a></h2><p class="dek">${esc(a.teaser)}</p><div class="meta"><time datetime="${esc(a.datum)}">${dmyLang(a.datum)}</time>${a.lesezeit ? `<span class="readtime">${esc(a.lesezeit.replace(' Lesezeit', ''))}</span>` : ''}</div></div></article>`;
}
function rowHtml(a) {
  const b = a.bild;
  return `<article data-story="${esc(a.id)}" class="feed-row${b ? '' : ' no-media no-image'}">${b ? `<a class="feed-img" href="${esc(a.url)}" tabindex="-1" aria-hidden="true"><div class="media${b.fit ? ' contain' : ''}">${imgHtml(b, '(max-width: 640px) 120px, 240px', false)}${badgeHtml(b)}</div></a>` : ''}<div class="feed-copy">${locHtml(a)}<span class="kicker">${esc(a.kicker)}</span><h3><a href="${esc(a.url)}">${esc(a.titel)}</a></h3><p class="dek">${esc(a.teaser)}</p><div class="meta"><time datetime="${esc(a.datum)}">${dmyLang(a.datum)}</time>${a.lesezeit ? `<span class="readtime">${esc(a.lesezeit.replace(' Lesezeit', ''))}</span>` : ''}</div>${mehr(a)}${b && b.credit ? `<div class="creditline"><span>${esc(b.badge || 'Bild')} · ${esc(b.credit)}</span></div>` : ''}</div></article>`;
}
const leerHtml = '<p class="empty">In diesem Bereich ist noch keine Meldung erschienen. Sie haben einen Hinweis für die Redaktion? <a href="/meldung-senden/">Meldung senden</a>.</p>';
function paginationHtml(basis, n, k) {
  if (k < 2) return '';
  const prev = n === 2 ? basis : `${basis}seite/${n - 1}/`;
  return `<nav class="pagination" aria-label="Seiten">${n > 1 ? `<a href="${prev}" rel="prev">← Neuer</a>` : ''}<span>Seite ${n} von ${k}</span>${n < k ? `<a href="${basis}seite/${n + 1}/" rel="next">Älter →</a>` : ''}</nav>`;
}
// Feed-Container einer Listenseite ersetzen; Einleitungsboxen vor dem ersten Artikel bleiben.
function ersetzeFeed(html, neuInnen) {
  const start = html.indexOf('<div class="feed">'); if (start < 0) return null;
  let ende = html.indexOf('<aside class="sidebar">', start); if (ende < 0) ende = html.indexOf('</section>', start); if (ende < 0) return null;
  const schluss = html.lastIndexOf('</div>', ende); if (schluss < start) return null;
  const innen = html.slice(start + '<div class="feed">'.length, schluss);
  const marken = ['<article class="feed-lead"', '<article data-story=', '<p class="empty"', '<nav class="pagination"'].map((m) => innen.indexOf(m)).filter((i) => i >= 0);
  const praefix = (marken.length ? innen.slice(0, Math.min(...marken)) : innen).replace(/\s+$/, '');
  return html.slice(0, start) + '<div class="feed">' + praefix + neuInnen + '\n    ' + html.slice(schluss);
}
const zaehler = (html, n) => html.replace(/<p class="count-line">\d+ Meldung(?:en)?/, `<p class="count-line">${n} Meldung${n === 1 ? '' : 'en'}`);

function listeSchreiben(basis, items, { seiten = true } = {}) {
  const rel1 = basis.replace(/^\//, '') + 'index.html';
  const pfad1 = join(site, rel1); if (!existsSync(pfad1)) return;
  const html1 = readFileSync(pfad1, 'utf8'); if (!html1.includes('<div class="feed">')) return;
  const k = seiten ? Math.max(1, Math.ceil(items.length / SEITENGROESSE)) : 1;
  const seite1 = items.length ? (seiten ? items.slice(0, SEITENGROESSE) : items) : [];
  const innen1 = seite1.length ? '\n      ' + leadHtml(seite1[0]) + seite1.slice(1).map((a) => '\n      ' + rowHtml(a)).join('') + (k > 1 ? '\n      ' + paginationHtml(basis, 1, k) : '') : '\n      ' + leerHtml;
  const neu1 = ersetzeFeed(zaehler(html1, items.length), innen1); if (neu1) schreibe(rel1, neu1);
  if (!seiten) return;
  const vorlagePfad = join(site, basis.replace(/^\//, ''), 'seite', '2', 'index.html');
  const vorlage = existsSync(vorlagePfad) ? readFileSync(vorlagePfad, 'utf8') : html1.replace(/<title>([^<|]*?)\s*\|/, '<title>$1 – Seite 2 |').replace(/(<link rel="canonical" href="[^"]*?)("\s*>)/, '$1seite/2/$2');
  for (let n = 2; n <= k; n++) {
    const teil = items.slice((n - 1) * SEITENGROESSE, n * SEITENGROESSE);
    const innen = teil.map((a) => '\n      ' + rowHtml(a)).join('') + '\n      ' + paginationHtml(basis, n, k);
    let html = vorlage.replace(/seite\/2\//g, `seite/${n}/`).replace(/Seite 2\b/g, `Seite ${n}`);
    html = ersetzeFeed(zaehler(html, items.length), innen); if (html) schreibe(`${basis.replace(/^\//, '')}seite/${n}/index.html`, html);
  }
  const seitenDir = join(site, basis.replace(/^\//, ''), 'seite');
  if (existsSync(seitenDir)) for (const d of readdirSync(seitenDir)) {
    if (!/^\d+$/.test(d)) continue;
    if (Number(d) > k || k < 2) { geloescht.push(`${basis.replace(/^\//, '')}seite/${d}/`); if (!nurPruefen) rmSync(join(seitenDir, d), { recursive: true, force: true }); }
  }
  if (k < 2 && existsSync(seitenDir) && !nurPruefen && readdirSync(seitenDir).length === 0) rmSync(seitenDir, { recursive: true, force: true });
}

// ----------------------------------------------------- Listen und Archiv
const ressorts = [...new Set(artikel.map((a) => a.ressort))];
for (const r of ['nachrichten', 'blaulicht', 'sport', 'rathaus', 'leben', 'wirtschaft', 'menschen', 'vereine', 'kultur']) {
  if (r === 'nachrichten') listeSchreiben('/nachrichten/', artikel);
  else if (existsSync(join(site, r, 'index.html'))) listeSchreiben(`/${r}/`, artikel.filter((a) => a.ressort === r));
}
for (const ort of Object.keys(ORTSTEILE)) listeSchreiben(`/${ort}/`, artikel.filter((a) => a.ortsteil === ort), { seiten: false });

// ----------------------------------------------------------- Startseite
{
  const rel = 'index.html'; const alt = readFileSync(join(site, rel), 'utf8'); let html = alt;
  const ed = JSON.parse(readFileSync(join(site, 'api', 'editorial-current.json'), 'utf8'));
  const h = ed.hero;
  if (h && h.url && h.title) {
    const hero = `<article class="front-lead" data-story="${esc(h.id || '')}">${h.image ? `<a href="${esc(h.url)}" tabindex="-1" aria-hidden="true"><div class="media${h.imageFit === 'contain' ? ' contain' : ''}"><img src="${esc(h.image)}"${h.imageSrcset ? ` srcset="${esc(h.imageSrcset)}"` : ''}${h.imageSizes ? ` sizes="${esc(h.imageSizes)}"` : ''} alt="${esc(h.imageAlt || h.title)}"${h.imageWidth && h.imageHeight ? ` width="${h.imageWidth}" height="${h.imageHeight}"` : ''} loading="eager" fetchpriority="high" decoding="async" data-editorial-image class="">${h.imageBadge ? `<span class="badge">${esc(h.imageBadge)}</span>` : ''}</div></a>` : ''}<div class="front-lead-copy"><div class="location-line"><span class="location-brand">${esc(h.location || 'MERZENICH')}</span></div><span class="kicker">${esc(h.kicker || 'Aktuell')}</span>${h.eyebrow ? `<span class="eyebrow">${esc(h.eyebrow)}</span>` : ''}<h1><a href="${esc(h.url)}">${esc(h.title)}</a></h1><p>${esc(h.teaser || '')}</p><div class="meta"><time datetime="${esc(h.published || '')}">${esc(h.timeLabel || kurzZeit(h.published))}</time>${h.readTime ? `<span>${esc(h.readTime)}</span>` : ''}</div><div class="story-actions"><a class="read-more" href="${esc(h.url)}">Mehr lesen<span class="sr-only">: ${esc(h.title)}</span></a></div></div></article>`;
    html = html.replace(/<article class="front-lead"[\s\S]*?<\/article>/, () => hero);
  }
  const s = ed.secondary;
  const brief = (a) => `<article class="front-brief ${a.bild ? 'secondary-lead' : ''}" data-story="${esc(a.id)}">${a.bild ? `<a class="brief-image" href="${esc(a.url)}" tabindex="-1" aria-hidden="true"><div class="media${a.bild.fit ? ' contain' : ''}">${imgHtml(a.bild, '120px', false)}${badgeHtml(a.bild)}</div></a>` : ''}<div>${locHtml(a)}<span class="kicker">${esc(a.kicker)}</span><h2><a href="${esc(a.url)}">${esc(a.titel)}</a></h2><div class="meta"><time datetime="${esc(a.datum)}">${kurzZeit(a.datum)}</time></div>${mehr(a)}</div></article>`;
  const sekundaer = s && s.url && s.title ? `<article class="front-brief editorial-secondary${s.image ? ' secondary-lead' : ''}" data-editorial-secondary="" data-story="${esc(s.id || '')}">${s.image ? `<a class="brief-image" href="${esc(s.url)}" tabindex="-1" aria-hidden="true"><div class="media${s.imageFit === 'contain' ? ' contain' : ''}"><img src="${esc(s.image)}" alt="${esc(s.imageAlt || s.title)}" loading="lazy" decoding="async" referrerpolicy="no-referrer">${s.imageBadge ? `<span class="badge">${esc(s.imageBadge)}</span>` : ''}</div></a>` : ''}<div><div class="location-line"><span class="location-brand">${esc(s.location || 'MERZENICH')}</span></div><span class="kicker">${esc(s.kicker || 'Aktuell')}</span><h3><a href="${esc(s.url)}">${esc(s.title)}</a></h3><p>${esc(s.teaser || '')}</p><div class="meta"><time datetime="${esc(s.published || '')}">${esc(s.timeLabel || '')}</time></div><div class="story-actions"><a class="read-more" href="${esc(s.url)}">Mehr lesen<span class="sr-only">: ${esc(s.title)}</span></a></div></div></article>` : '';
  const ausgeschlossen = new Set([h && h.url, s && s.url].filter(Boolean));
  const weitere = artikel.filter((a) => !ausgeschlossen.has(a.url)).slice(0, 3);
  const start = html.indexOf('<div class="front-side">');
  if (start >= 0) {
    let pos = start + '<div class="front-side">'.length;
    for (;;) {
      const rest = html.slice(pos); const ws = rest.match(/^\s*/)[0].length; const r = rest.slice(ws);
      if (r.startsWith('<span class="eyebrow">')) { pos += ws + r.indexOf('</span>') + 7; continue; }
      if (r.startsWith('<article')) { pos += ws + r.indexOf('</article>') + 10; continue; }
      break;
    }
    const schluss = html.indexOf('</div>', pos);
    html = html.slice(0, start) + '<div class="front-side"><span class="eyebrow">Weitere Nachrichten</span>' + sekundaer + weitere.map(brief).join('') + html.slice(schluss);
  }
  if (html !== alt) schreibe(rel, html);
}

// ------------------------------------------------------------- Sidebox
{
  const top = artikel.slice(0, 5).map((a) => `<li><a href="${esc(a.url)}">${esc(a.titel)}</a></li>`).join('');
  const re = /<div class="sidebox"><h3>(?:Aus den Ortsteilen|Neueste Meldungen)<a href="\/nachrichten\/">alle<\/a><\/h3><ol class="ranked">[\s\S]*?<\/ol><\/div>/g;
  const neu = `<div class="sidebox"><h3>Neueste Meldungen<a href="/nachrichten/">alle</a></h3><ol class="ranked">${top}</ol></div>`;
  (function lauf(d) { for (const e of readdirSync(d)) { const p = join(d, e); if (statSync(p).isDirectory()) lauf(p); else if (e.endsWith('.html')) { const alt = readFileSync(p, 'utf8'); if (re.test(alt)) { re.lastIndex = 0; const n = alt.replace(re, () => neu); if (n !== alt) schreibe(p.slice(site.length + 1), n); } re.lastIndex = 0; } } })(site);
}

// ----------------------------------------------------------- latest.json
{
  const rel = 'api/latest.json'; const alt = JSON.parse(readFileSync(join(site, rel), 'utf8'));
  alt.generated = neuester; alt.stand = neuester;
  alt.items = artikel.slice(0, 20).map((a) => ({ title: a.titel, url: abs(a.url), date: a.datum, ressort: a.ressort, ort: a.ortsteil, teaser: a.teaser, image: a.bild ? abs(a.bild.src) : null }));
  schreibe(rel, JSON.stringify(alt, null, 2) + '\n');
}

// ---------------------------------------------------------------- Feeds
const rssItem = (a) => `<item>\n<title>${x(a.titel)}</title>\n<link>${abs(a.url)}</link>\n<guid isPermaLink="true">${abs(a.url)}</guid>\n<pubDate>${new Date(a.datum).toUTCString()}</pubDate>\n<category>${x(a.ressortLabel)}</category>\n<category>${x(ORTSTEILE[a.ortsteil] || 'Region')}</category>\n<dc:creator>Redaktion Merzenich Aktuell</dc:creator>\n<description>${x(a.teaser)}</description>${a.bild ? `\n<media:content url="${x(abs(a.bild.src))}" medium="image">${a.bild.credit ? `<media:credit>${x(a.bild.credit)}</media:credit>` : ''}<media:description>${x(a.bild.alt)}</media:description></media:content>` : ''}\n</item>`;
const atomEntry = (a) => `<entry>\n<title>${x(a.titel)}</title>\n<link href="${abs(a.url)}"/>\n<id>${abs(a.url)}</id>\n<published>${x(a.datum)}</published>\n<updated>${x(a.aktualisiert || a.datum)}</updated>\n<summary>${x(a.teaser)}</summary>\n<content type="html">${x(`<p>${esc(a.teaser)}</p><p><a href="${abs(a.url)}">Zum Beitrag</a></p>`)}</content>\n<category term="${x(a.ressortLabel)}"/>\n</entry>`;
function ersetzeBlock(rel, tagStart, tagEnde, items, datumTag) {
  const pfad = join(site, rel); if (!existsSync(pfad)) return;
  const alt = readFileSync(pfad, 'utf8');
  const i = alt.indexOf(tagStart); const j = alt.lastIndexOf(tagEnde);
  let kopf = i >= 0 ? alt.slice(0, i) : alt.slice(0, alt.lastIndexOf('</channel>') >= 0 ? alt.lastIndexOf('</channel>') : alt.lastIndexOf('</feed>'));
  const fuss = j >= 0 ? alt.slice(j + tagEnde.length) : alt.slice(kopf.length);
  if (datumTag === 'rss') kopf = kopf.replace(/<lastBuildDate>[^<]*<\/lastBuildDate>/, `<lastBuildDate>${new Date(neuester).toUTCString()}</lastBuildDate>`).replace(/<pubDate>[^<]*<\/pubDate>/, `<pubDate>${new Date(neuester).toUTCString()}</pubDate>`);
  if (datumTag === 'atom') kopf = kopf.replace(/<updated>[^<]*<\/updated>/, `<updated>${x(neuester)}</updated>`);
  const neu = kopf.replace(/\s*$/, '\n') + items.join('\n') + '\n' + fuss.replace(/^\s*/, '');
  schreibe(rel, neu);
}
ersetzeBlock('feed.xml', '<item>', '</item>', artikel.slice(0, 30).map(rssItem), 'rss');
ersetzeBlock('atom.xml', '<entry>', '</entry>', artikel.slice(0, 30).map(atomEntry), 'atom');
ersetzeBlock('nachrichten/feed.xml', '<item>', '</item>', artikel.slice(0, 30).map(rssItem), 'rss');
for (const r of ressorts) ersetzeBlock(`${r}/feed.xml`, '<item>', '</item>', artikel.filter((a) => a.ressort === r).slice(0, 30).map(rssItem), 'rss');
for (const ort of Object.keys(ORTSTEILE)) ersetzeBlock(`${ort}/feed.xml`, '<item>', '</item>', artikel.filter((a) => a.ortsteil === ort).slice(0, 30).map(rssItem), 'rss');
{
  const rel = 'feed.json'; if (existsSync(join(site, rel))) {
    const alt = JSON.parse(readFileSync(join(site, rel), 'utf8'));
    alt.items = artikel.slice(0, 30).map((a) => ({ id: abs(a.url), url: abs(a.url), title: a.titel, summary: a.teaser, content_html: `<p>${esc(a.teaser)}</p>`, date_published: a.datum, date_modified: a.aktualisiert || a.datum, ...(a.bild ? { image: abs(a.bild.src) } : {}), tags: [a.ressortLabel, ORTSTEILE[a.ortsteil] || 'Region', ...a.themen.map((t) => t.label)] }));
    schreibe(rel, JSON.stringify(alt, null, 2) + '\n');
  }
}
{
  const frisch = artikel.filter((a) => Date.parse(neuester) - Date.parse(a.datum) <= 2 * 86400e3);
  const urls = frisch.map((a) => `<url><loc>${abs(a.url)}</loc><news:news><news:publication><news:name>Merzenich Aktuell</news:name><news:language>de</news:language></news:publication><news:publication_date>${x(a.datum)}</news:publication_date><news:title>${x(a.titel)}</news:title></news:news></url>`);
  schreibe('news-sitemap.xml', `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">\n${urls.join('\n')}\n</urlset>\n`);
  const alle = artikel.map((a) => `<url><loc>${abs(a.url)}</loc><lastmod>${x(a.aktualisiert || a.datum)}</lastmod><changefreq>weekly</changefreq><priority>0.8</priority>${a.bild ? `<image:image><image:loc>${x(abs(a.bild.src))}</image:loc><image:title>${x(a.bild.alt)}</image:title></image:image>` : ''}</url>`);
  schreibe('sitemap-artikel.xml', `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">\n${alle.join('\n')}\n</urlset>\n`);
}

console.log(`Inhaltsindex: ${artikel.length} Artikel, Stand ${neuester}; ${geaendert.length} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}${geloescht.length ? `, ${geloescht.length} Seitenordner ${nurPruefen ? 'ueberzaehlig' : 'entfernt'}` : ''}.`);
if (geaendert.length && geaendert.length <= 40) console.log('  ' + geaendert.join('\n  '));
if (nurPruefen && (geaendert.length || geloescht.length)) process.exit(2);
