import { readdirSync, readFileSync, existsSync } from 'node:fs';
import { join, basename } from 'node:path';
import { marked } from './marked.esm.js';
import { parseFrontmatter } from './yaml.mjs';
import { slugify, toDate, isoLocal, readingMinutes, words, stripHtml, esc } from './util.mjs';

marked.setOptions({ gfm: true, breaks: false });

/* Markdown → HTML mit redaktionellen Erweiterungen:
   - Bilder werden zu <figure> mit Credit ("Alt | Credit" im Titel)
   - externe Links öffnen in neuem Tab mit rel
   - Tabellen bekommen einen Scroll-Container */
const renderer = {
  heading({ tokens, depth, text }) {
    const rendered = this.parser.parseInline(tokens);
    const m = /\{#([a-z0-9-]+)\}\s*$/i.exec(text || '');
    const id = m ? m[1] : slugify(stripHtml(rendered));
    const label = m ? rendered.replace(/\s*\{#[a-z0-9-]+\}\s*$/i, '') : rendered;
    return `<h${depth} id="${esc(id)}">${label}</h${depth}>\n`;
  },
  image({ href, title, text }) {
    const [alt, credit] = (title || '').includes('|') ? title.split('|').map(s => s.trim()) : [text, title];
    return `<figure class="inline-figure"><img src="${esc(href)}" alt="${esc(alt || text || '')}" loading="lazy" decoding="async" width="1600" height="900"><figcaption>${esc(alt || text || '')}${credit ? ` · Bild: ${esc(credit)}` : ''}</figcaption></figure>`;
  },
  link({ href, title, tokens }) {
    const text = this.parser.parseInline(tokens);
    const ext = /^https?:\/\//.test(href) && !href.includes('merzenich-aktuell.de');
    return `<a href="${esc(href)}"${title ? ` title="${esc(title)}"` : ''}${ext ? ' target="_blank" rel="noopener"' : ''}>${text}</a>`;
  },
  table({ header, rows }) {
    const cell = (c, tag) => `<${tag}${c.align ? ` style="text-align:${c.align}"` : ''}>${this.parser.parseInline(c.tokens)}</${tag}>`;
    return `<div class="table-wrap"><table><thead><tr>${header.map(c => cell(c, 'th')).join('')}</tr></thead><tbody>${rows.map(r => `<tr>${r.map(c => cell(c, 'td')).join('')}</tr>`).join('')}</tbody></table></div>`;
  }
};
marked.use({ renderer });

export function md(src) { return src ? marked.parse(src) : ''; }

function readDir(dir) {
  if (!existsSync(dir)) return [];
  return readdirSync(dir).filter(f => f.endsWith('.md')).sort().map(f => {
    const raw = readFileSync(join(dir, f), 'utf8');
    const { data, body } = parseFrontmatter(raw);
    return { ...data, _file: f, _id: basename(f, '.md'), body: body.trim() };
  });
}

const normImage = (im) => {
  if (!im) return null;
  if (typeof im === 'string') return { src: im };
  return im.src ? im : null;
};

export function loadContent(root, now) {
  const site = JSON.parse(readFileSync(join(root, 'daten/site.json'), 'utf8'));
  const poll = existsSync(join(root, 'daten/umfrage.json')) ? JSON.parse(readFileSync(join(root, 'daten/umfrage.json'), 'utf8')) : null;

  /* Orte */
  const places = readDir(join(root, 'orte')).filter(p => !p.draft).map(p => ({
    ...p, slug: p.slug || p._id, url: `/${p.slug || p._id}/`, html: md(p.body), facts: p.facts || []
  })).sort((a, b) => (a.order || 99) - (b.order || 99));
  const placeMap = new Map(places.map(p => [p.slug, p]));

  /* Autoren */
  const authors = readDir(join(root, 'autoren')).map(a => ({
    ...a, slug: a.slug || a._id, url: `/autor/${a.slug || a._id}/`, html: md(a.body),
    initials: a.initials || a.name.split(/\s+/).map(w => w[0]).join('').slice(0, 2).toUpperCase()
  }));

  /* Artikel */
  const allArticles = readDir(join(root, 'artikel')).filter(a => !a.draft && a.title).map(a => {
    const date = toDate(a.date);
    const slug = slugify(a.slug || a.title);
    const ressort = site.ressorts[a.ressort] ? a.ressort : 'nachrichten';
    const html = md(a.body);
    return {
      ...a, id: a._id, slug, ressort, date, updated: a.updated ? toDate(a.updated) : null,
      url: `/${ressort}/${slug}/`, html, teaser: a.teaser || stripHtml(html).slice(0, 160),
      image: normImage(a.image), gallery: (a.gallery || []).filter(g => g && g.src),
      tags: (a.tags || []).map(String), facts: a.facts || [], sources: a.sources || [],
      format: (a.format === 'eilmeldung' && (now - date) > 48 * 3600 * 1000) ? 'meldung' : (a.format || 'meldung'), readingMinutes: readingMinutes(html), wordCount: words(html),
      ort: placeMap.has(a.ort) ? a.ort : (a.ort === 'gemeinde' ? 'gemeinde' : null),
      author: a.author || null
    };
  }).filter(a => a.date <= new Date(now.getTime() + 60000)).sort((a, b) => (a.undated ? 1 : 0) - (b.undated ? 1 : 0) || b.date - a.date);

  /* Termine */
  const eventsRaw = readDir(join(root, 'termine')).filter(e => !e.draft && e.title && e.start).map(e => {
    const slug = slugify(e.slug || e._id);
    return {
      ...e, id: e._id, slug, start: toDate(e.start), end: e.end ? toDate(e.end) : null,
      url: `/termine/${slug}/`, html: md(e.body), image: normImage(e.image),
      ort: placeMap.has(e.ort) ? e.ort : null, teaser: e.teaser || stripHtml(md(e.body)).slice(0, 160)
    };
  }).sort((a, b) => a.start - b.start);
  const cutoff = new Date(now.getTime() - 6 * 3600 * 1000);
  const events = eventsRaw.filter(e => (e.end || e.start) >= cutoff);
  const pastEvents = eventsRaw.filter(e => (e.end || e.start) < cutoff).reverse();

  /* Vereine + Betriebe */
  const mkEntity = (base) => (x) => {
    const slug = slugify(x.slug || x._id);
    return {
      ...x, id: x._id, slug, url: `${base}${slug}/`, html: md(x.body),
      image: normImage(x.image), ort: placeMap.has(x.ort) ? x.ort : null, description: x.description || stripHtml(md(x.body)).slice(0, 160)
    };
  };
  const clubs = readDir(join(root, 'vereine')).filter(c => !c.draft && c.name).map(mkEntity('/vereine/')).sort((a, b) => a.name.localeCompare(b.name, 'de'));
  const businesses = readDir(join(root, 'betriebe')).filter(b => !b.draft && b.name).map(mkEntity('/betriebe/')).sort((a, b) => (b.sponsored ? 1 : 0) - (a.sponsored ? 1 : 0) || a.name.localeCompare(b.name, 'de'));

  /* Stellen */
  const jobs = readDir(join(root, 'stellen')).filter(j => !j.draft && j.title).map(j => {
    const slug = slugify(j.slug || j._id);
    return {
      ...j, id: j._id, slug, url: `/jobs/${slug}/`, html: md(j.body), ort: placeMap.has(j.ort) ? j.ort : null,
      posted: toDate(j.posted || j.date || now), validUntil: j.validUntil ? toDate(j.validUntil) : null, description: j.description || stripHtml(md(j.body)).slice(0, 160)
    };
  }).filter(j => !j.validUntil || j.validUntil >= now).sort((a, b) => b.posted - a.posted);

  /* Seiten */
  const pages = readDir(join(root, 'seiten')).filter(p => !p.draft && p.title).map(p => ({
    ...p, slug: p.slug || p._id, url: p.url || `/${p.slug || p._id}/`, html: md(p.body)
  }));

  /* Tags */
  const tagMap = new Map();
  for (const a of allArticles) for (const t of a.tags) {
    const s = slugify(t);
    if (!tagMap.has(s)) tagMap.set(s, { slug: s, name: t, url: `/thema/${s}/`, items: [] });
    tagMap.get(s).items.push(a);
  }
  const tags = [...tagMap.values()].map(t => ({ ...t, count: t.items.length })).sort((a, b) => b.count - a.count || a.name.localeCompare(b.name, 'de'));

  return { site, poll, places, placeMap, authors, allArticles, events, pastEvents, allEvents: eventsRaw, clubs, businesses, pages, tags, jobs };
}
