#!/usr/bin/env node
/**
 * WordPress-Import (WXR 1.2) aus dem ausgelieferten statischen Stand
 * (Audit 28.09.2026, Punkt WordPress-Paritaet: die Importdatei stand seit dem
 * 16.09. auf 2 Beitraegen und 5 Terminen).
 *
 * Quelle ist, was die Seite zeigt:
 * - Meldungen: chatgpt-site/api/inhalte.json (Titel, Teaser, Datum, Ressort,
 *   Ortsteil, Themen, Bild) plus der Artikeltext (<div class="prose">), der
 *   Kasten "Das Wichtigste in Kuerze" und der Quellenkasten der Seite.
 * - Termine: dieselben Terminseiten wie termine.mjs (lib-termine.mjs).
 * - Bilder: Beitragsbild als Anhang; Herkunft, Lizenz und Pruefvermerk aus
 *   data/editorial-images/editorial-images.json bzw. deploy/ortsbilder.json.
 * - Gemeindedaten: deploy/gemeinde.json wird ins Core-Plugin kopiert
 *   (data/gemeinde.json, Shortcode [ma_gemeinde]).
 *
 * Status: Meldungen kommen als Entwurf. Das Core-Plugin veroeffentlicht einen
 * Beitrag erst, wenn Quelle, Datum, Ort und Human Review in WordPress
 * bestaetigt sind (includes/freigabe.php); der Import behauptet davon nur, was
 * der statische Stand belegt (Quelle mit Link). Termine und die Service-Seite
 * haben keine solche Sperre und kommen veroeffentlicht.
 *
 * IDs sind stabil (aus Slug bzw. Bildpfad abgeleitet), ohne Bauzeit: ein
 * zweiter Lauf aendert nichts. Jede Meldung und jeder Termin traegt
 * ma_legacy_url (alte statische Adresse) fuer die 301-Weiterleitung im Plugin
 * (includes/permalinks.php).
 *
 * Aufruf: node deploy/wordpress-import.mjs [--check]
 */
import { readFileSync, writeFileSync, existsSync, mkdirSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { SITE_URL, ORTSTEILE, ortSlug, entschaerfen } from './lib-artikel.mjs';
import { termineAusSeiten, berliner } from './lib-termine.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');
const ZIEL = 'wordpress-delivery/merzenich-aktuell-import.xml';
const GEMEINDE_KOPIE = 'wordpress/plugin/merzenich-aktuell-core/data/gemeinde.json';

const lies = (rel) => readFileSync(join(wurzel, rel), 'utf8');
const idx = JSON.parse(lies('chatgpt-site/api/inhalte.json'));
const manifest = new Map(JSON.parse(lies('chatgpt-site/data/editorial-images/editorial-images.json')).images.map((b) => [b.src, b]));
const ortsbilder = new Map(JSON.parse(lies('deploy/ortsbilder.json')).ansichten.map((b) => [b.src, b]));

// ------------------------------------------------------------ Hilfen
const cdata = (s) => `<![CDATA[${String(s ?? '').replace(/]]>/g, ']]]]><![CDATA[>')}]]>`;
const xmlEsc = (s) => String(s ?? '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
const vergeben = new Map();
/** Stabile ID je Bereich: Basis + 6 Stellen aus dem Schluessel. Kollision bricht ab. */
function stabileId(basis, schluessel) {
  const id = basis + (parseInt(createHash('sha1').update(schluessel).digest('hex').slice(0, 8), 16) % 900000);
  if (vergeben.has(id) && vergeben.get(id) !== schluessel) throw new Error(`wordpress-import: ID ${id} doppelt (${vergeben.get(id)} / ${schluessel})`);
  vergeben.set(id, schluessel);
  return id;
}
/** ISO-Zeitpunkt -> [lokal Berlin, GMT] als "YYYY-MM-DD HH:MM:SS". */
function wpDatum(iso) {
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) throw new Error(`wordpress-import: ungueltiges Datum ${iso}`);
  const b = berliner(d);
  const sek = String(d.getUTCSeconds()).padStart(2, '0');
  return [`${b.jahr}-${b.monat}-${b.tag} ${b.stunde}:${b.minute}:${sek}`, d.toISOString().slice(0, 19).replace('T', ' ')];
}
const lokalT = (iso) => { const b = berliner(new Date(iso)); return `${b.jahr}-${b.monat}-${b.tag}T${b.stunde}:${b.minute}`; };
const dmyIso = (s) => { const m = /(\d{2})\.(\d{2})\.(\d{4})/.exec(s || ''); return m ? `${m[3]}-${m[2]}-${m[1]}` : ''; };

/** Inhalt eines <div class="...">-Blocks mit verschachtelten divs; '' wenn nicht vorhanden. */
function block(html, klasse) {
  const start = html.indexOf(`<div class="${klasse}">`);
  if (start < 0) return '';
  const re = /<\/?div\b/g; re.lastIndex = start;
  let tiefe = 0, m;
  while ((m = re.exec(html))) {
    tiefe += m[0] === '<div' ? 1 : -1;
    if (tiefe === 0) return html.slice(html.indexOf('>', start) + 1, m.index);
  }
  return '';
}
const text = (h) => entschaerfen(String(h).replace(/<[^>]+>/g, '')).replace(/\s+/g, ' ').trim();

/** Artikeltext fuer WordPress: ohne Kommentare und Werbeflaechen, interne Links absolut. */
const inhaltFuerWp = (h) => h
  .replace(/<!-- werbung:([a-z0-9-]+):start -->[\s\S]*?<!-- werbung:\1:end -->/g, '')
  .replace(/<!--[\s\S]*?-->/g, '')
  .replace(/(href|src)="\/(?!\/)/g, `$1="${SITE_URL}/`)
  .trim();

/** Quellenkasten der Seite: erster Link ist die Grundlage der Meldung. */
function quelleAusSeite(html) {
  const box = /<div class="source-box">([\s\S]*?)<\/div>/.exec(html)?.[1] || '';
  const links = [...box.matchAll(/<a href="(https?:\/\/[^"]+)"[^>]*>([\s\S]*?)<\/a>/g)].map((m) => ({ url: m[1], name: text(m[2]).replace(/\s*↗$/, '') }));
  // Stand der Pruefung, in der Reihenfolge der Formulierungen, die die Seiten nutzen.
  const stand = dmyIso(/(?:Abgerufen am|Quellenstand bis|Quelle geprüft am|Abgeglichen mit [^.<]*? am|Datenstand|Stand)\s+([\d.]+\d)/.exec(box)?.[1]);
  const erste = links[0] || { url: '', name: '' };
  return { url: erste.url, name: erste.name, veroeffentlicht: dmyIso(/vom (\d{2}\.\d{2}\.\d{4})/.exec(erste.name)?.[1]), stand, weitere: links.slice(1).map((l) => l.url) };
}

// ------------------------------------------------------------ Bilder
const TYP = { Symbolbild: 'symbol', Ortsansicht: 'place', Originalbild: 'original', Quellenmotiv: 'official', 'Offizielles Veranstaltungsbild': 'official', 'Offizielles Quellenmotiv': 'official', Archivbild: 'licensed' };
const LIZENZ_RE = /\b(CC[ -][A-Z0-9 .-]+|Public domain|gemeinfrei|CC0)\b/i;
const anhaenge = new Map();
function anhang(bild) {
  if (!bild?.src) return null;
  if (anhaenge.has(bild.src)) return anhaenge.get(bild.src);
  const m = manifest.get(bild.src), o = ortsbilder.get(bild.src);
  const typ = TYP[bild.badge];
  if (!typ) throw new Error(`wordpress-import: Bildkennzeichnung "${bild.badge}" ohne Zuordnung (${bild.src})`);
  const lizenz = m?.license || o?.lizenz || (LIZENZ_RE.exec(String(bild.credit || '').split(' · ').pop() || '')?.[0] ?? '');
  const a = {
    id: stabileId(2000000, `bild:${bild.src}`),
    src: bild.src, url: SITE_URL + bild.src, alt: bild.alt || '',
    titel: (m?.sourceTitle || bild.alt || bild.src.split('/').pop()).replace(/^File:/, ''),
    credit: String(bild.credit || '').replace(/^Symbolbild · /, ''),
    lizenz, typ,
    original: m?.sourceUrl || o?.quelle || '',
    // Rechte gelten nur als geprueft, wenn die Bildbibliothek es vermerkt
    // (geprueft am rightsCheckedAt) oder die Ortsansicht gesichtet ist.
    geprueft: m?.geprueft === true || Boolean(o) ? '1' : '0',
    herkunft: m ? `Bildbibliothek (${m.source || 'Pool'}), Rechte geprueft am ${m.rightsCheckedAt || 'unbekannt'}` : o ? 'Ortsansicht (deploy/ortsbilder.json), gesichtet am 26.09.2026' : 'Quellenbild des statischen Stands',
  };
  anhaenge.set(bild.src, a);
  return a;
}

// ------------------------------------------------------------ Meldungen
const kategorien = new Map(); // slug -> Name
const schlagworte = new Map();
const orte = new Map(Object.entries(ORTSTEILE));
const meldungen = [];
for (const a of idx.artikel) {
  const html = readFileSync(join(site, a.url.slice(1), 'index.html'), 'utf8');
  const prosa = block(html, 'prose');
  if (!prosa.trim()) throw new Error(`wordpress-import: ${a.url} ohne Artikeltext`);
  const fakten = [...block(html, 'facts').matchAll(/<li>([\s\S]*?)<\/li>/g)].map((m) => text(m[1]));
  const q = quelleAusSeite(html);
  if (!q.url) throw new Error(`wordpress-import: ${a.url} ohne Quellenlink`);
  const slug = a.url.split('/').filter(Boolean).pop();
  kategorien.set(a.ressort, a.ressortLabel);
  for (const t of a.themen || []) schlagworte.set(t.slug, t.label);
  // Undatierte Meldungen (15, Stand 04.09.) tragen im statischen Stand nur
  // "aktualisiert"; das wird Beitragsdatum, ma_date_unknown sagt es der Redaktion.
  const ohneDatum = !a.datum || a.undatiert === true;
  const [lokal, gmt] = wpDatum(ohneDatum ? a.aktualisiert : a.datum);
  const bild = anhang(a.bild);
  meldungen.push({ a, slug, ohneDatum, prosa: inhaltFuerWp(prosa), fakten, q, lokal, gmt, bild, id: stabileId(1000000, `meldung:${a.url}`) });
}

// ------------------------------------------------------------ Termine
const termine = termineAusSeiten(site).sort((x, y) => new Date(x.start) - new Date(y.start) || x.slug.localeCompare(y.slug)).map((t) => {
  const html = readFileSync(join(site, 'termine', t.slug, 'index.html'), 'utf8');
  const prosa = block(html, 'prose');
  const ort = ortSlug(t.ortsteil);
  return { t, id: stabileId(3000000, `termin:${t.slug}`), inhalt: prosa.trim() ? inhaltFuerWp(prosa) : `<p>${xmlEsc(t.beschreibung || '')}</p>`, ort: orte.has(ort) ? ort : '' };
});

// ------------------------------------------------------------ XML
const meta = (k, v) => `<wp:postmeta><wp:meta_key>${cdata(k)}</wp:meta_key><wp:meta_value>${cdata(v)}</wp:meta_value></wp:postmeta>`;
const zeile = (tag, v) => `<${tag}>${v}</${tag}>`;
const items = [];

// Der WordPress-Importer hält Anhänge mit gleichem Titel für schon vorhanden und
// überspringt sie (30.09.: zwei verschiedene Feuerwehrfotos, eines fehlte). Doppelte
// Titel bekommen deshalb eine Nummer; der Alt-Text bleibt unverändert.
const titelZahl = new Map();
for (const b of [...anhaenge.values()].sort((x, y) => x.id - y.id)) {
  const n = (titelZahl.get(b.titel) || 0) + 1;
  titelZahl.set(b.titel, n);
  const titel = n > 1 ? `${b.titel} (Bild ${n})` : b.titel;
  items.push(`<item>${zeile('title', cdata(titel))}${zeile('link', xmlEsc(b.url))}${zeile('dc:creator', cdata('redaktion'))}`
    + `<content:encoded>${cdata('')}</content:encoded><excerpt:encoded>${cdata(b.credit)}</excerpt:encoded>`
    + `${zeile('wp:post_id', b.id)}${zeile('wp:post_name', cdata(`bild-${b.id}`))}${zeile('wp:status', 'inherit')}${zeile('wp:post_parent', 0)}${zeile('wp:post_type', 'attachment')}`
    + `${zeile('wp:attachment_url', cdata(b.url))}`
    + meta('_wp_attachment_image_alt', b.alt) + meta('ma_image_credit', b.credit) + meta('ma_image_license', b.lizenz)
    + meta('ma_image_original_url', b.original) + meta('ma_image_provenance', b.herkunft) + meta('ma_image_type', b.typ)
    + meta('ma_image_rights_verified', b.geprueft) + meta('ma_image_static_src', b.src)
    + '</item>');
}

for (const m of meldungen) {
  const { a, q, bild } = m;
  const tax = `<category domain="category" nicename="${xmlEsc(a.ressort)}">${cdata(a.ressortLabel)}</category>`
    + `<category domain="ma_location" nicename="${xmlEsc(a.ortsteil)}">${cdata(ORTSTEILE[a.ortsteil] || a.ortsteil)}</category>`
    + (a.themen || []).map((t) => `<category domain="post_tag" nicename="${xmlEsc(t.slug)}">${cdata(t.label)}</category>`).join('');
  items.push(`<item>${zeile('title', cdata(a.titel))}${zeile('link', xmlEsc(SITE_URL + a.url))}${zeile('dc:creator', cdata('redaktion'))}`
    + `<content:encoded>${cdata(m.prosa)}</content:encoded><excerpt:encoded>${cdata(a.teaser)}</excerpt:encoded>`
    + `${zeile('wp:post_id', m.id)}${zeile('wp:post_date', cdata(m.lokal))}${zeile('wp:post_date_gmt', cdata(m.gmt))}`
    + `${zeile('wp:comment_status', 'open')}${zeile('wp:ping_status', 'closed')}${zeile('wp:post_name', cdata(m.slug))}`
    + `${zeile('wp:status', 'draft')}${zeile('wp:post_parent', 0)}${zeile('wp:post_type', 'post')}${zeile('wp:is_sticky', 0)}`
    + tax
    + meta('ma_legacy_url', a.url)
    + meta('ma_source_url', q.url) + meta('ma_source_publisher', q.name) + meta('ma_source_published_at', q.veroeffentlicht)
    + meta('ma_source_checked_at', q.stand || a.abgerufen || '') + meta('ma_source_verified', '1')
    + (q.weitere.length ? meta('ma_source_more_urls', q.weitere.join('\n')) : '')
    + (m.ohneDatum ? meta('ma_date_unknown', '1') : '')
    + meta('ma_date_verified', '0') + meta('ma_place_verified', '0') + meta('ma_human_reviewed', '0')
    + (m.fakten.length ? meta('ma_facts', m.fakten.join(' · ')) : '')
    + meta('ma_kicker', a.kicker || '')
    + (bild ? meta('_thumbnail_id', String(bild.id)) + meta('ma_image_credit', bild.credit) + meta('ma_image_license', bild.lizenz)
      + meta('ma_image_original_url', bild.original) + meta('ma_image_type', bild.typ) + meta('ma_image_rights_verified', bild.geprueft) : '')
    + '</item>');
}

for (const { t, id, inhalt, ort } of termine) {
  // Beitragsdatum = Stand der Terminseite, nicht der Beginn: WordPress stellt ein
  // veröffentlichtes Datum in der Zukunft auf „Geplant“ und zeigt den Termin nicht.
  const veroeffentlicht = t.stand ? new Date(`${t.stand}T10:00:00Z`) : t.start;
  const [lokal, gmt] = wpDatum(veroeffentlicht < t.start ? veroeffentlicht : t.start);
  items.push(`<item>${zeile('title', cdata(t.titel))}${zeile('link', xmlEsc(`${SITE_URL}/termine/${t.slug}/`))}${zeile('dc:creator', cdata('redaktion'))}`
    + `<content:encoded>${cdata(inhalt)}</content:encoded><excerpt:encoded>${cdata(t.beschreibung || '')}</excerpt:encoded>`
    + `${zeile('wp:post_id', id)}${zeile('wp:post_date', cdata(lokal))}${zeile('wp:post_date_gmt', cdata(gmt))}`
    + `${zeile('wp:comment_status', 'closed')}${zeile('wp:ping_status', 'closed')}${zeile('wp:post_name', cdata(t.slug))}`
    + `${zeile('wp:status', 'publish')}${zeile('wp:post_parent', 0)}${zeile('wp:post_type', 'ma_event')}${zeile('wp:is_sticky', 0)}`
    + (ort ? `<category domain="ma_location" nicename="${ort}">${cdata(ORTSTEILE[ort])}</category>` : '')
    + meta('ma_legacy_url', `/termine/${t.slug}/`)
    + meta('ma_event_start', lokalT(t.start)) + meta('ma_event_end', t.ohneEnde ? '' : lokalT(t.ende))
    + meta('ma_event_place', t.ort || '') + meta('ma_event_organizer', t.veranstalter || '') + meta('ma_event_source_url', t.quelle || '')
    + meta('ma_event_category', t.kategorie || '') + meta('ma_source_checked_at', t.stand || '')
    + '</item>');
}

// Service-Seite: Rathaus und Abfall ueber den Shortcode, Daten im Plugin.
items.push(`<item>${zeile('title', cdata('Service'))}${zeile('link', xmlEsc(`${SITE_URL}/service/`))}${zeile('dc:creator', cdata('redaktion'))}`
  + `<content:encoded>${cdata('<h2>Rathaus Merzenich</h2>\n[ma_gemeinde teil="rathaus"]\n<h2>Abfall</h2>\n[ma_gemeinde teil="abfall"]')}</content:encoded><excerpt:encoded>${cdata('')}</excerpt:encoded>`
  + `${zeile('wp:post_id', stabileId(4000000, 'seite:service'))}${zeile('wp:comment_status', 'closed')}${zeile('wp:ping_status', 'closed')}`
  + `${zeile('wp:post_name', cdata('service'))}${zeile('wp:status', 'publish')}${zeile('wp:post_parent', 0)}${zeile('wp:post_type', 'page')}`
  + meta('ma_legacy_url', '/service/') + '</item>');

const termId = (tax, slug) => stabileId(5000000, `term:${tax}:${slug}`);
const sortiert = (m) => [...m.entries()].sort((x, y) => x[0].localeCompare(y[0]));
const terme = [
  ...sortiert(kategorien).map(([s, n]) => `<wp:category>${zeile('wp:term_id', termId('category', s))}${zeile('wp:category_nicename', cdata(s))}${zeile('wp:category_parent', cdata(''))}${zeile('wp:cat_name', cdata(n))}</wp:category>`),
  ...sortiert(schlagworte).map(([s, n]) => `<wp:tag>${zeile('wp:term_id', termId('post_tag', s))}${zeile('wp:tag_slug', cdata(s))}${zeile('wp:tag_name', cdata(n))}</wp:tag>`),
  ...sortiert(orte).map(([s, n]) => `<wp:term>${zeile('wp:term_id', termId('ma_location', s))}${zeile('wp:term_taxonomy', cdata('ma_location'))}${zeile('wp:term_slug', cdata(s))}${zeile('wp:term_parent', cdata(''))}${zeile('wp:term_name', cdata(n))}</wp:term>`),
];

const xml = `<?xml version="1.0" encoding="UTF-8"?>
<!-- Erzeugt von deploy/wordpress-import.mjs aus dem statischen Stand ${idx.generated}. Nicht von Hand pflegen. -->
<rss version="2.0" xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:wp="http://wordpress.org/export/1.2/">
<channel>
<title>Merzenich Aktuell</title>
<link>${SITE_URL}</link>
<description>Internet-Zeitung für die Gemeinde Merzenich und Umkreis</description>
<language>de-DE</language>
<wp:wxr_version>1.2</wp:wxr_version>
<wp:base_site_url>${SITE_URL}</wp:base_site_url>
<wp:base_blog_url>${SITE_URL}</wp:base_blog_url>
<wp:author><wp:author_id>1</wp:author_id><wp:author_login>${cdata('redaktion')}</wp:author_login><wp:author_email>${cdata('')}</wp:author_email><wp:author_display_name>${cdata('Redaktion Merzenich Aktuell')}</wp:author_display_name></wp:author>
${terme.join('\n')}
${items.join('\n')}
</channel>
</rss>
`;

const geaendert = [];
const schreibe = (rel, inhalt) => {
  const pfad = join(wurzel, rel);
  if (existsSync(pfad) && readFileSync(pfad, 'utf8') === inhalt) return;
  geaendert.push(rel);
  if (!nurPruefen) { mkdirSync(dirname(pfad), { recursive: true }); writeFileSync(pfad, inhalt); }
};
schreibe(ZIEL, xml);
schreibe(GEMEINDE_KOPIE, lies('deploy/gemeinde.json'));
// docs/SHA256SUMS.txt: die Zeile der Importdatei gleich mitziehen, sonst meldet
// qa/pruefung.mjs nach jeder neuen Meldung eine Abweichung. Die ZIP-Zeilen
// schreibt weiter .github/workflows/build-wordpress-packages.yml.
{
  const summen = 'docs/SHA256SUMS.txt';
  const zeileNeu = `${createHash('sha256').update(xml).digest('hex')}  ${ZIEL}`;
  const alt = existsSync(join(wurzel, summen)) ? lies(summen) : '';
  const zeilen = alt.split('\n').filter((z) => z.trim() && !z.trim().endsWith(ZIEL));
  schreibe(summen, [...zeilen, zeileNeu].join('\n') + '\n');
}
console.log(`WordPress-Import: ${meldungen.length} Meldungen, ${termine.length} Termine, ${anhaenge.size} Bilder, 1 Seite; ${geaendert.length} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}${geaendert.length ? ': ' + geaendert.join(', ') : ''}.`);
if (nurPruefen && geaendert.length) process.exit(2);
