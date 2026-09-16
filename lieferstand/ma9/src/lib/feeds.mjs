import { esc, fmt, isoLocal, icsDate, icsText, stripHtml } from './util.mjs';

const abs = (site, p) => (p && p.startsWith('http')) ? p : site.url + (p || '');

const MIME_BY_EXT = { webp: 'image/webp', png: 'image/png', jpg: 'image/jpeg', jpeg: 'image/jpeg', gif: 'image/gif' };
function mime(url) {
  const m = /\.([a-z0-9]+)(?:[?#].*)?$/i.exec(String(url || ''));
  return m ? MIME_BY_EXT[m[1].toLowerCase()] || null : null;
}
function mediaMarkup(site, ctx, image) {
  if (!image || !image.src) return '';
  const src = image.src;
  const type = mime(src);
  const url = esc(abs(site, src));
  const external = /^https?:\/\//.test(src);
  const length = (type && !external && ctx.fileSize) ? ctx.fileSize(src) : 0;
  const enclosure = type ? `<enclosure url="${url}" type="${type}" length="${length}"/>` : '';
  return `${enclosure}<media:content url="${url}" medium="image"${type ? ` type="${type}"` : ''}><media:credit>${esc(image.credit || '')}</media:credit><media:description>${esc(image.alt || '')}</media:description></media:content>`;
}

export function rss(site, ctx, { title, url, items, description, self }) {
  const it = items.slice(0, 30).map(a => `<item>
<title>${esc(a.title)}</title>
<link>${site.url}${a.url}</link>
<guid isPermaLink="true">${site.url}${a.url}</guid>
${a.undated ? '' : `<pubDate>${fmt.rfc822(a.date)}</pubDate>`}
${a.ressort ? `<category>${esc(site.ressorts[a.ressort]?.name || a.ressort)}</category>` : ''}
${a.ort && ctx.placeName(a.ort) ? `<category>${esc(ctx.placeName(a.ort))}</category>` : ''}
${a.author && ctx.authorBy(a.author) ? `<dc:creator>${esc(ctx.authorBy(a.author).name)}</dc:creator>` : '<dc:creator>Redaktion Merzenich Aktuell</dc:creator>'}
<description>${esc(a.teaser)}</description>
${mediaMarkup(site, ctx, a.image)}
<content:encoded><![CDATA[${a.image && a.image.src ? `<p><img src="${esc(abs(site, a.image.src))}" alt="${esc(a.image.alt || '')}"></p>` : ''}${a.html}${(a.sources || []).length ? `<p><em>Quelle: ${a.sources.map(s => s.url ? `<a href="${esc(s.url)}">${esc(s.title)}</a>` : esc(s.title)).join(', ')}</em></p>` : ''}<p><a href="${site.url}${a.url}">Zur Meldung auf Merzenich Aktuell</a></p>]]></content:encoded>
</item>`).join('\n');
  return `<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:media="http://search.yahoo.com/mrss/">
<channel>
<title>${esc(title)}</title>
<link>${site.url}${url}</link>
<description>${esc(description)}</description>
<language>de-DE</language>
<copyright>© ${new Date(ctx.now).getFullYear()} ${esc(site.name)}</copyright>
<managingEditor>${esc(site.email)} (Redaktion ${esc(site.name)})</managingEditor>
<lastBuildDate>${fmt.rfc822(items[0] ? items[0].date : ctx.now)}</lastBuildDate>
<image><url>${site.url}/assets/img/avatar-1024.png</url><title>${esc(title)}</title><link>${site.url}${url}</link></image>
<atom:link href="${site.url}${self}" rel="self" type="application/rss+xml"/>
<atom:link href="https://pubsubhubbub.appspot.com/" rel="hub"/>
${it}
</channel>
</rss>`;
}

export function atom(site, ctx, items) {
  return `<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="de">
<title>${esc(site.name)}</title>
<subtitle>${esc(site.claim)}</subtitle>
<link href="${site.url}/"/>
<link rel="self" href="${site.url}/atom.xml"/>
<link rel="hub" href="https://pubsubhubbub.appspot.com/"/>
<id>${site.url}/</id>
<updated>${isoLocal(items[0] ? items[0].date : ctx.now)}</updated>
<author><name>Redaktion ${esc(site.name)}</name><email>${esc(site.email)}</email></author>
${items.slice(0, 30).map(a => `<entry>
<title>${esc(a.title)}</title>
<link href="${site.url}${a.url}"/>
<id>${site.url}${a.url}</id>
${a.undated ? '' : `<published>${isoLocal(a.date)}</published>`}
<updated>${isoLocal(a.updated || (a.undated ? a.retrieved : a.date))}</updated>
<summary>${esc(a.teaser)}</summary>
<content type="html">${esc(a.html)}</content>
${a.ressort ? `<category term="${esc(site.ressorts[a.ressort]?.name || a.ressort)}"/>` : ''}
</entry>`).join('\n')}
</feed>`;
}

export function jsonFeed(site, ctx, items) {
  return JSON.stringify({
    version: 'https://jsonfeed.org/version/1.1', title: site.name, home_page_url: site.url + '/', feed_url: site.url + '/feed.json',
    description: site.claim, language: 'de-DE', icon: site.url + '/assets/img/avatar-1024.png', favicon: site.url + '/assets/img/favicon.svg',
    authors: [{ name: 'Redaktion ' + site.name, url: site.url + '/ueber-uns/' }],
    items: items.slice(0, 50).map(a => ({
      id: site.url + a.url, url: site.url + a.url, title: a.title, summary: a.teaser, content_html: a.html,
      ...(a.undated ? {} : { date_published: isoLocal(a.date) }),
      date_modified: isoLocal(a.updated || (a.undated ? a.retrieved : a.date)),
      ...(a.image && a.image.src ? { image: abs(site, a.image.src) } : {}),
      tags: [site.ressorts[a.ressort]?.name, ctx.placeName(a.ort), ...(a.tags || [])].filter(Boolean),
      authors: [{ name: a.author && ctx.authorBy(a.author) ? ctx.authorBy(a.author).name : 'Redaktion ' + site.name }],
      _merzenich: { ressort: a.ressort, ort: a.ort, format: a.format, sources: a.sources }
    }))
  }, null, 1);
}

export function eventsRss(site, ctx, events) {
  return `<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title>${esc(site.name)} – Termine</title>
<link>${site.url}/termine/</link>
<description>Kommende Termine in der Gemeinde Merzenich</description>
<language>de-DE</language>
<atom:link href="${site.url}/termine/feed.xml" rel="self" type="application/rss+xml"/>
${events.slice(0, 50).map(e => `<item><title>${esc(fmt.date(e.start))} ${esc(fmt.time(e.start))} Uhr: ${esc(e.title)}</title><link>${site.url}${e.url}</link><guid isPermaLink="true">${site.url}${e.url}</guid><pubDate>${fmt.rfc822(e.created || e.start)}</pubDate><description>${esc(e.teaser || '')} ${esc(e.location || '')}</description></item>`).join('\n')}
</channel></rss>`;
}

export function sitemapIndex(site, files, now) {
  return `<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${files.map(f => `<sitemap><loc>${site.url}/${f}</loc><lastmod>${isoLocal(now)}</lastmod></sitemap>`).join('\n')}
</sitemapindex>`;
}

export function sitemap(site, urls) {
  return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
${urls.map(u => `<url><loc>${site.url}${u.loc}</loc>${u.lastmod ? `<lastmod>${isoLocal(u.lastmod)}</lastmod>` : ''}${u.changefreq ? `<changefreq>${u.changefreq}</changefreq>` : ''}${u.priority ? `<priority>${u.priority}</priority>` : ''}${u.image ? `<image:image><image:loc>${esc(abs(site, u.image.src))}</image:loc>${u.image.alt ? `<image:title>${esc(u.image.alt)}</image:title>` : ''}</image:image>` : ''}</url>`).join('\n')}
</urlset>`;
}

export function newsSitemap(site, articles, now) {
  const since = new Date(now.getTime() - 48 * 3600 * 1000);
  const recent = articles.filter(a => a.date >= since && !a.noindex && !a.sponsored && !a.undated).slice(0, 1000);
  return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
${recent.map(a => `<url><loc>${site.url}${a.url}</loc><news:news><news:publication><news:name>${esc(site.name)}</news:name><news:language>de</news:language></news:publication><news:publication_date>${isoLocal(a.date)}</news:publication_date><news:title>${esc(a.title)}</news:title></news:news></url>`).join('\n')}
</urlset>`;
}

export function ics(site, events, opts = {}) {
  const lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', `PRODID:-//${site.name}//Termine//DE`, 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH',
    `X-WR-CALNAME:${icsText(opts.name || site.name + ' Termine')}`, 'X-WR-TIMEZONE:Europe/Berlin', 'REFRESH-INTERVAL;VALUE=DURATION:PT12H'];
  for (const e of events) {
    lines.push('BEGIN:VEVENT', `UID:${e.slug}@merzenich-aktuell.de`, `DTSTAMP:${icsDate(e.updated || e.created || e.start)}`,
      `DTSTART:${icsDate(e.start)}`, e.end ? `DTEND:${icsDate(e.end)}` : `DTEND:${icsDate(new Date(e.start.getTime() + 2 * 3600 * 1000))}`,
      `SUMMARY:${icsText(e.title)}`, `DESCRIPTION:${icsText((e.teaser || '') + (e.organizer ? '\nVeranstalter: ' + e.organizer : '') + '\n' + site.url + e.url)}`,
      `LOCATION:${icsText([e.location, e.address].filter(Boolean).join(', '))}`, `URL:${site.url}${e.url}`,
      e.status === 'abgesagt' ? 'STATUS:CANCELLED' : 'STATUS:CONFIRMED', 'END:VEVENT');
  }
  lines.push('END:VCALENDAR');
  return lines.map(l => l.length > 73 ? l.match(/.{1,73}/g).join('\r\n ') : l).join('\r\n') + '\r\n';
}
