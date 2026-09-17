/**
 * Gemeinsame Artikel-Erfassung fuer Suchindex, Themenseiten, Inhaltsindex
 * (Ressorts, Orte, Archiv, Feeds, Startseite): liest die ausgelieferten
 * Artikelseiten unter chatgpt-site/<ressort>/<slug>/ und liefert einen
 * gemeinsamen Datensatz.
 */
import { readFileSync, readdirSync, statSync, existsSync } from 'node:fs';
import { join } from 'node:path';

const RESSORTE = { nachrichten: 'Nachrichten', blaulicht: 'Blaulicht', sport: 'Sport', rathaus: 'Rathaus & Politik', leben: 'Leben', wirtschaft: 'Wirtschaft', menschen: 'Menschen', vereine: 'Vereine', kultur: 'Kultur' };
const ENT = { amp: '&', lt: '<', gt: '>', quot: '"', apos: "'", nbsp: ' ', ndash: '–', mdash: '—', hellip: '…', laquo: '«', raquo: '»', bdquo: '„', ldquo: '“', rdquo: '”', sbquo: '‚', lsquo: '‘', rsquo: '’', shy: '' };
export const entschaerfen = (t) => String(t ?? '').replace(/&#x([0-9a-f]+);/gi, (m, h) => String.fromCodePoint(parseInt(h, 16))).replace(/&#(\d+);/g, (m, d) => String.fromCodePoint(Number(d))).replace(/&([a-z]+);/gi, (m, n) => (n in ENT ? ENT[n] : m));
export const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const text = (html) => entschaerfen(String(html ?? '').replace(/<[^>]+>/g, ' ')).replace(/\s+/g, ' ').trim();
const erstes = (re, s) => { const m = re.exec(s); return m ? m[1] : ''; };

export const ORTSTEILE = { merzenich: 'Merzenich', golzheim: 'Golzheim', girbelsrath: 'Girbelsrath', morschenich: 'Morschenich', buergewald: 'Bürgewald' };
export const ortSlug = (t) => String(t || '').toLowerCase().replace(/ü/g, 'ue').replace(/ö/g, 'oe').replace(/ä/g, 'ae').replace(/ß/g, 'ss').replace(/[^a-z]/g, '');
export const SITE_URL = JSON.parse(readFileSync(new URL('./site.json', import.meta.url), 'utf8')).url.replace(/\/$/, '');
function bildAus(main) {
  const fig = /<figure class="(art-figure[^"]*)"[^>]*>([\s\S]*?)<\/figure>/.exec(main);
  if (!fig) return null;
  const img = /<img\b([^>]*)>/.exec(fig[2]); if (!img) return null;
  const attr = (n) => { const m = new RegExp(`\\b${n}="([^"]*)"`).exec(img[1]); return m ? entschaerfen(m[1]) : ''; };
  const src = attr('src'); if (!src) return null;
  return {
    src, srcset: attr('srcset'), alt: attr('alt'), width: Number(attr('width')) || 0, height: Number(attr('height')) || 0,
    fit: /class="media contain"/.test(fig[2]) ? 'contain' : '',
    badge: text(erstes(/<span class="figure-badge">([^<]*)<\/span>/, fig[2])),
    credit: text(erstes(/<span>Bild: ([\s\S]*?)<\/span>/, fig[2])).replace(/\s*·\s*Bildquelle\s*$/, ''),
    symbol: fig[1].includes('art-figure--symbol'),
  };
}
export function artikelSammeln(site) {
  const out = [];
  for (const ressort of Object.keys(RESSORTE)) {
    const ordner = join(site, ressort);
    if (!existsSync(ordner)) continue;
    for (const slug of readdirSync(ordner)) {
      const basis = join(ordner, slug);
      const pfad = join(basis, 'index.html');
      if (!existsSync(basis) || !statSync(basis).isDirectory() || !existsSync(pfad)) continue;
      const html = readFileSync(pfad, 'utf8');
      if (!/<article class="article"/.test(html) || !html.includes('data-readable')) continue;
      const main = html.slice(html.indexOf('<main'), html.indexOf('</main>'));
      const datum = erstes(/"datePublished":\s*"([^"]+)"/, html) || erstes(/<time datetime="([^"]+)"/, main);
      const zeitLabel = text(erstes(/<time datetime="[^"]+"[^>]*>([^<]*)<\/time>/, main));
      const themen = [...main.matchAll(/<a href="\/thema\/([^"/]+)\/" rel="tag">([^<]*)<\/a>/g)].map((m) => ({ slug: m[1], label: text(m[2]) }));
      const body = erstes(/<div class="article-body" data-readable>([\s\S]*?)<\/div>\s*(?:<footer|<div class="article-foot|<div class="tags|<section|$)/, main) || '';
      const loc = erstes(/<div class="location-line">([\s\S]*?)<\/div>/, main);
      const ortsteilText = text(loc.replace(/<span class="location-brand">[^<]*<\/span>/, '')).replace(/^[·\s]+/, '');
      const ortsteil = ortsteilText && ORTSTEILE[ortSlug(ortsteilText)] ? ortSlug(ortsteilText) : (ortsteilText ? 'region' : 'merzenich');
      out.push({
        url: `/${ressort}/${slug}/`, ressort, ressortLabel: RESSORTE[ressort],
        // ort = Marke (erster Span), ortsteilLabel = Rest der Ortszeile, ortZeile = komplette Zeile als Text.
        ortZeile: text(loc), ortsteil, ortsteilLabel: ortsteilText || '', lesezeit: erstes(/(\d+ Min\. Lesezeit)/, main), aktualisiert: erstes(/"dateModified":\s*"([^"]+)"/, html) || '',
        bild: bildAus(main),
        titel: text(erstes(/<h1[^>]*>([\s\S]*?)<\/h1>/, main)),
        teaser: entschaerfen(erstes(/<meta name="description" content="([^"]*)"/, html)),
        kicker: text(erstes(/<span class="kicker">([\s\S]*?)<\/span>/, main)) || RESSORTE[ressort],
        ort: text(erstes(/<span class="location-brand">([^<]*)<\/span>/, main)) || 'MERZENICH',
        datum, zeitLabel, themen,
        text: text(body).slice(0, 700),
        id: `${(datum || '').slice(0, 10)}-${slug}`,
      });
    }
  }
  return out.filter((a) => a.titel).sort((a, b) => (b.datum || '').localeCompare(a.datum || ''));
}
export const dmyKurz = (iso) => { const d = new Date(iso); return Number.isNaN(d.getTime()) ? '' : new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit' }).format(d) + '.'; };
export const dmyLang = (iso) => { const d = new Date(iso); return Number.isNaN(d.getTime()) ? '' : new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit', year: 'numeric' }).format(d) + ' · ' + new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', hour: '2-digit', minute: '2-digit' }).format(d) + ' Uhr'; };
