import { loadContent } from './ma9/src/lib/content.mjs';
import fs from 'node:fs';

const NOW = new Date('2026-09-06T06:30:00+02:00');
const ctx = await loadContent('ma9/content', NOW);
const { site } = ctx;

const cdata = (s) => '<![CDATA[' + String(s ?? '').replace(/]]>/g, ']]]]><![CDATA[>') + ']]>';
const esc = (s) => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const tzBerlin = (d, opts) => new Intl.DateTimeFormat('sv-SE', { timeZone: 'Europe/Berlin', ...opts }).format(d);
const localDateTime = (d) => tzBerlin(d, { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }).replace(' ', ' ');
const gmtDateTime = (d) => new Intl.DateTimeFormat('sv-SE', { timeZone: 'UTC', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }).format(d);
const rfc822 = (d) => d.toUTCString().replace('GMT', 'GMT');
const eventLocal = (d) => tzBerlin(d, { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false }).replace(' ', 'T');

let nextId = 1000;
const id = () => nextId++;

// --- Kategorien (Ressorts) ---
const categoryIds = {};
let catId = 200;
const categoryBlocks = Object.entries(site.ressorts).map(([slug, r]) => {
  categoryIds[slug] = catId;
  const block = `\t<wp:category>
\t\t<wp:term_id>${catId}</wp:term_id>
\t\t<wp:category_nicename>${cdata(slug)}</wp:category_nicename>
\t\t<wp:category_parent><![CDATA[]]></wp:category_parent>
\t\t<wp:cat_name>${cdata(r.name)}</wp:cat_name>
\t\t<wp:category_description>${cdata(r.desc)}</wp:category_description>
\t</wp:category>`;
  catId++;
  return block;
}).join('\n');

// --- Ortsteil-Taxonomie ---
const districtIds = {};
let distId = 300;
const districtBlocks = ctx.places.map((p) => {
  districtIds[p.slug] = distId;
  const block = `\t<wp:term>
\t\t<wp:term_id>${distId}</wp:term_id>
\t\t<wp:term_taxonomy><![CDATA[ma_district]]></wp:term_taxonomy>
\t\t<wp:term_slug>${cdata(p.slug)}</wp:term_slug>
\t\t<wp:term_parent><![CDATA[]]></wp:term_parent>
\t\t<wp:term_name>${cdata(p.name)}</wp:term_name>
\t\t<wp:term_description>${cdata(p.description)}</wp:term_description>
\t</wp:term>`;
  distId++;
  return block;
}).join('\n');

const districtCategory = (ortSlug) => {
  if (!ortSlug || !(ortSlug in districtIds)) return '';
  const place = ctx.places.find((p) => p.slug === ortSlug);
  return `\t\t<category domain="ma_district" nicename="${esc(ortSlug)}">${cdata(place.name)}</category>\n`;
};

const tagCategories = (tags) => (tags || []).map((t) => `\t\t<category domain="post_tag" nicename="${esc(t.toLowerCase().replace(/[^a-z0-9äöüß]+/g, '-'))}">${cdata(t)}</category>`).join('\n');

const meta = (key, val) => val === undefined || val === null || val === '' ? '' : `\t\t<wp:postmeta><wp:meta_key>${cdata(key)}</wp:meta_key><wp:meta_value>${cdata(val)}</wp:meta_value></wp:postmeta>\n`;

function itemHeader(postId, title, link, pubDateJs, postName, postType, status = 'publish') {
  return `<item>
\t\t<title>${cdata(title)}</title>
\t\t<link>${esc(link)}</link>
\t\t<pubDate>${rfc822(pubDateJs)}</pubDate>
\t\t<dc:creator><![CDATA[redaktion]]></dc:creator>
\t\t<guid isPermaLink="false">https://merzenich-aktuell.de/?p=${postId}</guid>
\t\t<description></description>
`;
}
function itemFooter(postId, pubDateJs, postName, postType, extraTop = '') {
  return `\t\t<wp:post_id>${postId}</wp:post_id>
\t\t<wp:post_date><![CDATA[${localDateTime(pubDateJs)}]]></wp:post_date>
\t\t<wp:post_date_gmt><![CDATA[${gmtDateTime(pubDateJs)}]]></wp:post_date_gmt>
\t\t<wp:post_modified><![CDATA[${localDateTime(pubDateJs)}]]></wp:post_modified>
\t\t<wp:post_modified_gmt><![CDATA[${gmtDateTime(pubDateJs)}]]></wp:post_modified_gmt>
\t\t<wp:comment_status><![CDATA[closed]]></wp:comment_status>
\t\t<wp:ping_status><![CDATA[closed]]></wp:ping_status>
\t\t<wp:post_name>${cdata(postName)}</wp:post_name>
\t\t<wp:status><![CDATA[publish]]></wp:status>
\t\t<wp:post_parent>0</wp:post_parent>
\t\t<wp:menu_order>0</wp:menu_order>
\t\t<wp:post_type>${cdata(postType)}</wp:post_type>
\t\t<wp:post_password></wp:post_password>
\t\t<wp:is_sticky>0</wp:is_sticky>
${extraTop}`;
}

// --- Artikel (post) ---
const articleItems = ctx.allArticles.map((a) => {
  const pid = id();
  const link = `https://merzenich-aktuell.de/${a.ressort}/${a.slug}/`;
  let out = itemHeader(pid, a.title, link, a.date, a.slug, 'post');
  out += `\t\t<content:encoded>${cdata(a.html)}</content:encoded>\n`;
  out += `\t\t<excerpt:encoded>${cdata(a.teaser)}</excerpt:encoded>\n`;
  out += itemFooter(pid, a.date, a.slug, 'post');
  const catName = site.ressorts[a.ressort] ? site.ressorts[a.ressort].name : a.ressort;
  out += `\t\t<category domain="category" nicename="${esc(a.ressort)}">${cdata(catName)}</category>\n`;
  out += districtCategory(a.ort);
  if (a.tags && a.tags.length) out += tagCategories(a.tags) + '\n';
  out += meta('ma8_kicker', a.kicker);
  out += meta('ma8_dek', a.teaser);
  if (a.facts && a.facts.length) out += meta('ma8_facts', a.facts.join('\n'));
  const src = a.sources && a.sources[0];
  if (src) {
    out += meta('ma8_source_name', src.title);
    out += meta('ma8_source_url', src.url);
    out += meta('ma8_source_date', src.stand);
  }
  if (a.image) {
    out += meta('ma8_external_image', a.image.src);
    out += meta('ma8_image_credit', a.image.credit);
    out += meta('ma8_image_alt', a.image.alt);
    out += meta('ma8_image_type', a.image.type);
    if (a.image.fit) out += meta('ma8_image_fit', a.image.fit);
  }
  out += meta('ma8_featured', a.featured ? 1 : 0);
  out += meta('ma8_breaking', 0);
  out += meta('ma8_lead', a.featured ? 1 : 0);
  out += meta('ma8_undated', a.undated ? 1 : 0);
  if (a.undated) out += meta('ma8_retrieved', a.retrieved);
  if (a.format && a.format !== 'meldung') out += meta('ma8_format', a.format);
  out += '\t</item>';
  return out;
});

// --- Termine (ma_event) ---
const eventItems = ctx.allEvents.map((e) => {
  const pid = id();
  const link = `https://merzenich-aktuell.de/termine/${e.slug}/`;
  let out = itemHeader(pid, e.title, link, e.created ? new Date(e.created) : e.start, e.slug, 'ma_event');
  out += `\t\t<content:encoded>${cdata(e.html)}</content:encoded>\n`;
  out += `\t\t<excerpt:encoded>${cdata(e.teaser)}</excerpt:encoded>\n`;
  out += itemFooter(pid, e.created ? new Date(e.created) : e.start, e.slug, 'ma_event');
  out += districtCategory(e.ort);
  out += meta('ma8_dek', e.teaser);
  out += meta('ma8_event_start', eventLocal(e.start));
  if (e.end) out += meta('ma8_event_end', eventLocal(e.end));
  out += meta('ma8_event_location', e.location);
  out += meta('ma8_event_organizer', e.organizer);
  if (e.organizerUrl) out += meta('ma8_event_organizer_url', e.organizerUrl);
  if (e.price) out += meta('ma8_event_price', e.price);
  out += meta('ma8_event_category', e.category);
  if (e.source) {
    out += meta('ma8_source_name', e.organizer);
    out += meta('ma8_source_url', e.source);
  }
  out += meta('ma8_image_type', 'event');
  out += '\t</item>';
  return out;
});

// --- Vereine (ma_club) ---
const clubItems = ctx.clubs.map((c) => {
  const pid = id();
  const link = `https://merzenich-aktuell.de/vereine/${c.slug}/`;
  let out = itemHeader(pid, c.name, link, NOW, c.slug, 'ma_club');
  out += `\t\t<content:encoded>${cdata(c.html)}</content:encoded>\n`;
  out += `\t\t<excerpt:encoded>${cdata(c.description)}</excerpt:encoded>\n`;
  out += itemFooter(pid, NOW, c.slug, 'ma_club');
  out += districtCategory(c.ort);
  out += meta('ma8_dek', c.description);
  out += meta('ma8_club_category', c.category);
  out += meta('ma8_club_url', c.website);
  if (c.founded) out += meta('ma8_club_founded', c.founded);
  if (c.image) {
    out += meta('ma8_external_image', c.image.src);
    out += meta('ma8_image_type', c.image.type);
    if (c.image.fit) out += meta('ma8_image_fit', c.image.fit);
    out += meta('ma8_image_credit', c.image.credit);
    out += meta('ma8_image_alt', c.image.alt);
  }
  out += '\t</item>';
  return out;
});

// --- Seiten (page) ---
//
// Die Netlify-Fassung baut beliebig tiefe Pfade (/betriebe/eintragen/danke/), auch
// unterhalb von Verzeichnissen, die es in WordPress gar nicht als Seite gibt
// (/betriebe/ ist dort das Archiv des Inhaltstyps ma_business). In WordPress kann
// eine Seite nur unter einer anderen Seite haengen. Deshalb gilt:
//
//   * Existiert eine Seite mit dem Pfad des Elternverzeichnisses, wird die Seite
//     dort eingehaengt und traegt nur das letzte Pfadsegment als Titelform.
//   * Existiert sie nicht, bleibt die Seite auf oberster Ebene und behaelt ihren
//     eindeutigen Inhalts-Slug. Sonst kollidieren mehrere "danke" oder "eintragen"
//     miteinander und WordPress haengt stillschweigend -2, -3 an.
const normPath = (u) => '/' + String(u || '').split('/').filter(Boolean).join('/') + '/';
const pageByPath = {};
ctx.pages.forEach((p) => { pageByPath[normPath(p.url || '/' + p.slug + '/')] = p; });

const pageIds = {};
ctx.pages.forEach((p) => { pageIds[p.slug] = id(); });

function pageParent(p) {
  const segs = normPath(p.url || '/' + p.slug + '/').split('/').filter(Boolean);
  if (segs.length < 2) return { parentId: 0, ownSlug: p.slug };
  const parent = pageByPath['/' + segs.slice(0, -1).join('/') + '/'];
  if (!parent) return { parentId: 0, ownSlug: p.slug };
  return { parentId: pageIds[parent.slug] || 0, ownSlug: segs[segs.length - 1] };
}

// Seiten mit Formular bekommen den Shortcode des Themes. Die Feldsaetze in
// functions.php sind zeichengleich mit denen der Netlify-Fassung.
const FORM_SHORTCODE = ['meldung', 'termin', 'verein', 'betrieb', 'korrektur', 'werbung', 'kontakt'];

function whatsappBlock() {
  const link = site.social && site.social.whatsapp;
  const btn = link
    ? `<p><a class="btn wa-btn" href="${link}" target="_blank" rel="noopener">Kanal auf WhatsApp öffnen</a></p>`
    : '<p class="notice">Der Kanal startet mit dem Livegang. Der Link wird unter Design → Merzenich Aktuell eingetragen und erscheint dann hier und im Seitenkopf.</p>';
  return `\n<h2>So funktioniert der Kanal</h2>\n<p>Ein WhatsApp-Kanal ist ein Broadcast: Sie sehen unsere Meldungen, niemand sieht Ihre Nummer, niemand kann Ihnen über den Kanal schreiben. Abbestellen jederzeit über „Kanal verlassen“.</p>\n${btn}\n<h2>Lieber RSS?</h2>\n<p>Alle Meldungen als Feed: <a href="/feed/">/feed/</a>.</p>`;
}

function pageItem(p) {
  const pid = pageIds[p.slug];
  const { parentId, ownSlug } = pageParent(p);
  const link = `https://merzenich-aktuell.de/${normPath(p.url || '/' + p.slug + '/').replace(/^\//, '')}`;
  let out = itemHeader(pid, p.title, link, NOW, ownSlug, 'page');
  let content = p.html || '';
  if (p.form && FORM_SHORTCODE.includes(p.form)) content += `\n\n[ma_formular typ="${p.form}"]`;
  else if (p.form === 'whatsapp') content += whatsappBlock();
  out += `\t\t<content:encoded>${cdata(content)}</content:encoded>\n`;
  out += `\t\t<excerpt:encoded>${cdata(p.description)}</excerpt:encoded>\n`;
  out += itemFooter(pid, NOW, ownSlug, 'page');
  out = out.replace('<wp:post_parent>0</wp:post_parent>', `<wp:post_parent>${parentId}</wp:post_parent>`);
  out += meta('ma8_kicker', p.eyebrow);
  out += meta('ma8_dek', p.description);
  if (p.noindex) out += meta('ma8_noindex', 1);
  out += '\t</item>';
  return out;
}

// Archivseite. In der Netlify-Fassung erzeugt der Generator /archiv/ selbst; in
// WordPress braucht es dafuer eine Seite mit dem Seitentemplate template-archiv.php.
function archivItem() {
  const pid = id();
  let out = itemHeader(pid, 'Archiv', 'https://merzenich-aktuell.de/archiv/', NOW, 'archiv', 'page');
  out += `\t\t<content:encoded>${cdata('')}</content:encoded>\n`;
  out += `\t\t<excerpt:encoded>${cdata('Alle Meldungen von Merzenich Aktuell nach Monaten sortiert.')}</excerpt:encoded>\n`;
  out += itemFooter(pid, NOW, 'archiv', 'page');
  out += meta('_wp_page_template', 'template-archiv.php');
  out += meta('ma8_kicker', 'Alle Meldungen');
  out += meta('ma8_dek', 'Alle Meldungen von Merzenich Aktuell nach Monaten sortiert.');
  out += '\t</item>';
  return out;
}

// Eltern muessen im Export VOR ihren Kindern stehen: der WordPress-Importer
// setzt post_parent erst nach dem Einfuegen nach. Steht ein Kind zuerst, wird es
// kurzzeitig auf oberster Ebene angelegt, kollidiert dort mit gleichnamigen
// Geschwistern und WordPress haengt -2, -3 an den Slug.
const pagesByDepth = [...ctx.pages].sort(
  (a, b) => normPath(a.url || '/' + a.slug + '/').split('/').filter(Boolean).length
          - normPath(b.url || '/' + b.slug + '/').split('/').filter(Boolean).length
);
const pageItems = [...pagesByDepth.map(pageItem), archivItem()];

const allItems = [...articleItems, ...eventItems, ...clubItems, ...pageItems].join('\n');

const stand = tzBerlin(NOW, { year: 'numeric', month: '2-digit', day: '2-digit' });
const header = `<?xml version="1.0" encoding="UTF-8" ?>
<!-- Merzenich Aktuell v17 – Starter-Content.
     Erzeugt automatisch aus ma9/content/ (Stand ${stand}). Import über Werkzeuge → Daten importieren → WordPress.
     Enthält ${ctx.allArticles.length} Meldungen, ${ctx.allEvents.length} Termine, ${ctx.clubs.length} Vereine, ${ctx.pages.length} Seiten,
     ${Object.keys(site.ressorts).length} Ressorts und ${ctx.places.length} Ortsteile samt aller Redaktionsfelder.
     Stellenanzeigen (ma_job) und Betriebe (ma_business) sind aktuell leer, weil im Datenbestand keine Eintraege vorliegen. -->
<rss version="2.0"
	xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:wp="http://wordpress.org/export/1.2/">
<channel>
	<title>${esc(site.name)}</title>
	<link>${esc(site.url)}</link>
	<description>${esc(site.description || site.claim || '')}</description>
	<pubDate>${rfc822(NOW)}</pubDate>
	<language>de-DE</language>
	<wp:wxr_version>1.2</wp:wxr_version>
	<wp:base_site_url>${esc(site.url)}</wp:base_site_url>
	<wp:base_blog_url>${esc(site.url)}</wp:base_blog_url>
	<wp:author>
		<wp:author_id>1</wp:author_id>
		<wp:author_login><![CDATA[redaktion]]></wp:author_login>
		<wp:author_email>${cdata(ctx.authors[0].email)}</wp:author_email>
		<wp:author_display_name>${cdata(ctx.authors[0].name)}</wp:author_display_name>
		<wp:author_first_name><![CDATA[]]></wp:author_first_name>
		<wp:author_last_name><![CDATA[]]></wp:author_last_name>
	</wp:author>
${categoryBlocks}
${districtBlocks}
`;

const footer = `</channel>
</rss>
`;

const out = header + allItems + '\n' + footer;
fs.writeFileSync('v13/wp/merzenich-aktuell/starter-content-v17.xml', out);
console.log('geschrieben:', 'starter-content-v17.xml');
console.log('Artikel', articleItems.length, 'Termine', eventItems.length, 'Vereine', clubItems.length, 'Seiten', pageItems.length, 'Kategorien', Object.keys(categoryIds).length, 'Ortsteile', Object.keys(districtIds).length);
