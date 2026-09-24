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
// Ortsmarke nach Anhang A3.1: "MERZENICH · GOLZHEIM  Rubrik". MERZENICH in
// Bordeaux, der Ortsteil zurueckhaltend, die Rubrik in normaler Schreibung.
// Eine Komponente fuer Startseite, Listen, Thema-Seiten und Suche. rubrik:
// 'kicker' zeigt die Dachzeile der Meldung (Listen, in denen das Ressort
// ohnehin feststeht), 'ressort' das Ressort (Startseite, gemischte Listen).
export function markeHtml(a, { rubrik = 'kicker' } = {}) {
  const teil = a.ortsteil && a.ortsteil !== 'merzenich' && ORTSTEILE[a.ortsteil] ? ORTSTEILE[a.ortsteil] : '';
  const r = rubrik === 'ressort' ? (a.ressortLabel || a.kicker) : (a.kicker || a.ressortLabel);
  return `<p class="marke"><span class="marke-ort">Merzenich</span>`
    + (teil ? `<span class="marke-teil"> · ${esc(teil)}</span>` : '')
    + (r ? `<span class="marke-rubrik">${esc(r)}</span>` : '') + '</p>';
}
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
/**
 * Datum eines Artikels, nach Verlaesslichkeit der Quelle geordnet.
 *
 * Gelesen wird ausschliesslich im Artikelkopf, also zwischen
 * <article class="article"> und dem Beginn des Fliesstextes. Der Rest von
 * <main> traegt die Teaser-Karten unter dem Text; deren <time>-Elemente
 * gehoeren fremden Meldungen. Wer dort mitliest, haengt einem Beitrag das
 * Datum seiner Nachbarin an - genau das ist bisher passiert: sieben
 * Hintergrundstuecke trugen im Index ein fremdes Datum, etwa
 * /vereine/container-girbelsrath/ das einer Feuerwehrmeldung.
 *
 * Nie gelesen werden dateModified, article:modified_time oder das Abrufdatum.
 * Das sind Bearbeitungs- und Erfassungszeitpunkte, keine Veroeffentlichung.
 * Ein Datum wird niemals errechnet, abgeleitet oder geraten.
 */
const artikelKopf = (main) => {
  const a = main.indexOf('<article class="article"');
  const b = main.indexOf('<div class="article-body"');
  return a >= 0 && b > a ? main.slice(a, b) : main;
};
// JSON-LD gibt es je Seite mehrfach (Organisation, Website, Artikel). Nur der
// Artikelknoten darf das Datum liefern.
const ldArtikel = (html) => {
  for (const m of html.matchAll(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/g)) {
    if (/"@type":\s*"[A-Za-z]*Article"/.test(m[1])) return m[1];
  }
  return '';
};
// Hintergrundstuecke: die Quelle nennt kein Veroeffentlichungsdatum, die Seite
// sagt das im Kopf ausdruecklich und nennt stattdessen den Abruftag. Das ist
// eine redaktionelle Aussage, kein Parserfehler - deshalb ohne Fehlermeldung.
const abrufAus = (kopf) => {
  const m = /<span class="undated"([^>]*)>/.exec(kopf);
  if (!m) return null;
  const d = /abgerufen\s+(\d{2})\.(\d{2})\.(\d{4})/.exec(m[1]);
  return d ? `${d[3]}-${d[2]}-${d[1]}` : '';
};
function datumAus(html, main, url) {
  const kopf = artikelKopf(main);
  // Im Kopf steht "Veroeffentlicht <time>" und bei Nachtraegen zusaetzlich
  // "Aktualisiert <time>". Ohne diese Bindung an die Beschriftung wuerde bei
  // nachtraeglich geaenderten Meldungen das Bearbeitungsdatum gewinnen.
  let kopfDatum = '';
  let kopfLabel = '';
  const beschriftet = /Ver(?:ö|&ouml;)ffentlicht\s*<time datetime="([^"]+)"[^>]*>([^<]*)<\/time>/.exec(kopf);
  if (beschriftet) { kopfDatum = beschriftet[1]; kopfLabel = beschriftet[2]; }
  else for (const m of kopf.matchAll(/(Aktualisiert\s*)?<time datetime="([^"]+)"[^>]*>([^<]*)<\/time>/g)) {
    if (m[1]) continue;
    kopfDatum = m[2]; kopfLabel = m[3]; break;
  }
  const datum = erstes(/"datePublished":\s*"([^"]+)"/, ldArtikel(html) || html)
    || erstes(/<meta property="article:published_time" content="([^"]+)"/, html)
    || kopfDatum;
  const abgerufen = abrufAus(kopf);
  // Kein Datum und keine Kennzeichnung als undatiert heisst: die Seite ist
  // kaputt oder anders ausgezeichnet. Das muss auffallen, nicht stillschweigend
  // zu einem leeren Feld werden.
  if (!datum && abgerufen === null) console.error(`lib-artikel: kein Veroeffentlichungsdatum gefunden in ${url}`);
  return {
    datum,
    zeitLabel: datum ? text(kopfLabel) : '',
    undatiert: !datum && abgerufen !== null,
    abgerufen: !datum && abgerufen !== null ? abgerufen : '',
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
      const { datum, zeitLabel, undatiert, abgerufen } = datumAus(html, main, `/${ressort}/${slug}/`);
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
        // undatiert/abgerufen: Beitraege, deren Quelle kein Veroeffentlichungsdatum
        // nennt. Sie bleiben ohne datum und gelten damit nirgends als aktuell.
        datum, zeitLabel, undatiert, abgerufen, themen,
        // Editorial Image System V3: ausdrueckliche Bildklasse (deploy/bildklassen.json), sonst leer.
        bildklasse: erstes(/<meta name="ma:bildklasse" content="([^"]+)">/, html),
        // Ohne Bildunterschrift: Sie beschreibt das Motiv, nicht die Meldung.
        // Mit ihr bestimmte der Alt-Text eines zugewiesenen Symbolbilds die
        // Themenerkennung (kategorieFuer) und damit den naechsten Bildpool.
        text: text(body.replace(/<figure[\s\S]*?<\/figure>/g, ' ')).slice(0, 700),
        id: `${(datum || '').slice(0, 10)}-${slug}`,
      });
    }
  }
  return out.filter((a) => a.titel).sort((a, b) => (b.datum || '').localeCompare(a.datum || ''));
}
export const dmyKurz = (iso) => { const d = new Date(iso); return Number.isNaN(d.getTime()) ? '' : new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit' }).format(d) + '.'; };
export const dmyLang = (iso) => { const d = new Date(iso); return Number.isNaN(d.getTime()) ? '' : new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit', year: 'numeric' }).format(d) + ' · ' + new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', hour: '2-digit', minute: '2-digit' }).format(d) + ' Uhr'; };
