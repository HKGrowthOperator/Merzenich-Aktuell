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
/**
 * Kurzer Bildnachweis (02.10.2026), dieselbe Regel wie ma_credit_kurz() im
 * Plugin (includes/images.php): Commons-Floskeln, Wohnort, Benutzerkonto,
 * Diskussionslink, Web-Adressen und die Langform von „(bearbeitet: …)“
 * fallen weg; die Lizenz steht genau einmal am Ende, „Public domain“ heißt
 * „gemeinfrei“.
 */
const LIZENZ_FORM = /^(CC[ -][A-Z0-9 .-]+|CC0(?: 1\.0)?|Public domain|gemeinfrei)$/i;
/**
 * sizes-Angaben der Bildkarten (02.10.2026), gemessen an den gerenderten
 * Breiten bei 390/560/760/1100/1280/1440 px (scratchpad mess.mjs). Dieselbe
 * Tabelle steht im Theme als MA21_SIZES (inc/ma21.php). Vorher stammten die
 * Werte aus Schaetzungen und luden auf dem Handy bis zu 2 MB zu grosse Bilder
 * (Lighthouse „uses-responsive-images“), etwa 100vw fuer ein 120-px-Vorschaubild.
 */
export const SIZES = {
  xl: '(max-width: 1099px) 100vw, (max-width: 1439px) 62vw, 845px',
  r: '(max-width: 759px) 132px, (max-width: 1099px) 46vw, (max-width: 1439px) 31vw, 411px',
  u: '(max-width: 759px) 132px, (max-width: 1439px) 31vw, 411px',
  l: '(max-width: 759px) 100vw, (max-width: 1099px) 46vw, (max-width: 1439px) 34vw, 468px',
  'l-vereine': '(max-width: 759px) 100vw, (max-width: 1439px) 46vw, 624px',
  'l-blaulicht': '(max-width: 759px) 100vw, (max-width: 1099px) 46vw, (max-width: 1439px) 55vw, 749px',
  'l-blaulicht-klein': '(max-width: 759px) 100vw, (max-width: 1099px) 46vw, 132px',
  'l-rathaus': '(max-width: 759px) 100vw, (max-width: 1099px) 46vw, (max-width: 1439px) 18vw, 241px',
  'l-wirtschaft': '(max-width: 759px) 100vw, (max-width: 1099px) 46vw, (max-width: 1439px) 18vw, 240px',
  m: '(max-width: 559px) 100vw, (max-width: 759px) 45vw, (max-width: 1099px) 30vw, (max-width: 1439px) 22.5vw, 307px',
  'm-blaulicht': '(max-width: 559px) 100vw, (max-width: 759px) 45vw, (max-width: 1099px) 30vw, 132px',
  'm-rathaus': '(max-width: 559px) 100vw, (max-width: 759px) 45vw, (max-width: 1099px) 30vw, (max-width: 1439px) 31vw, 411px',
  'm-vereine': '(max-width: 559px) 100vw, (max-width: 759px) 45vw, (max-width: 1099px) 30vw, (max-width: 1439px) 31vw, 411px',
  'm-wirtschaft': '(max-width: 559px) 100vw, (max-width: 759px) 45vw, (max-width: 1099px) 30vw, (max-width: 1439px) 18vw, 240px',
  s: '(max-width: 759px) 112px, 220px',
  's-vereine': '(max-width: 759px) 112px, (max-width: 1099px) 220px, 200px',
  'feed-lead': '(max-width: 1099px) 120px, (max-width: 1439px) 30.5vw, 419px',
  'feed-row': '(max-width: 1099px) 120px, (max-width: 1439px) 210px, 240px',
  'news-card': '(max-width: 1099px) 120px, (max-width: 1439px) 30vw, 405px',
  raster: '(max-width: 559px) 132px, (max-width: 1099px) 46vw, (max-width: 1279px) 31vw, 300px',
  unternehmen: '(max-width: 1099px) 100vw, (max-width: 1439px) 32vw, 450px',
  figur: '(max-width: 1099px) 100vw, 720px',
};
/** sizes einer Sektionskarte: Blaulicht, Rathaus und Wirtschaft ordnen ab 1100 px anders an (startseite.css). */
export function sizesFuer(groesse, sektion = '', i = 0) {
  if (groesse === 'l' && sektion === 'blaulicht') return i === 0 ? SIZES['l-blaulicht'] : SIZES['l-blaulicht-klein'];
  return SIZES[`${groesse}-${sektion}`] || SIZES[groesse] || '';
}

export function creditKurz(credit, lizenz = '') {
  let t = String(credit ?? '').replace(/\s+/g, ' ').trim();
  if (!t) return '';
  t = t.replace(/^Symbolbild\s*·\s*/, '')
    .replace(/No machine-readable author provided\.\s*(.+?)\s+assumed \(based on copyright claims\)\.?/, '$1')
    .replace(/\(bearbeitet:[^)]*\)/, '(bearbeitet)')
    .replace(/\s*\(\s*Diskussion\s*\)/, '')
    .replace(/\s*\(User:[^)]*\)/, '')
    .replace(/,?\s*(?:https?:\/\/|www\.)\S+/, '')
    .replace(/ from [^/·]+?(?= \/ Wikimedia)/, '');
  const teile = t.split(/\s*·\s*/).map((s) => s.trim());
  let lz = '';
  if (teile.length > 1 && LIZENZ_FORM.test(teile[teile.length - 1])) lz = teile.pop();
  if (!lz && LIZENZ_FORM.test(String(lizenz ?? '').trim())) lz = String(lizenz).trim();
  if (/^public domain$/i.test(lz)) lz = 'gemeinfrei';
  t = teile.filter(Boolean).join(' · ');
  return lz && t ? `${t} · ${lz}` : (t || lz);
}
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
/**
 * Original-Adresse einer Vorschauseite (deploy/site.json canonicalUrl, seit
 * 02.10.2026 die WordPress-Installation). Pfade, die WordPress anders fuehrt,
 * werden abgebildet; Blaetterseiten und Seiten ohne Live-Pendant behalten ihre
 * eigene Adresse (Google soll nicht auf eine fremde Seite verwiesen werden).
 */
export const CANONICAL_URL = (JSON.parse(readFileSync(new URL('./site.json', import.meta.url), 'utf8')).canonicalUrl || '').replace(/\/$/, '') || SITE_URL;
const OHNE_LIVE = [/^\/.+\/seite\/\d+\/$/, /^\/(?:betriebe|vereine)\/eintragen\/$/, /^\/vereine\/meldungen\/$/, /^\/unternehmen\/[^/]+\/$/, /^\/(?:suche|offline|redaktionshandbuch|admin|redaktion)\/$/, /\/danke\/$/];
export function liveUrl(pfad) {
  const r = '/' + String(pfad || '/').replace(/^\/+/, '');
  if (CANONICAL_URL === SITE_URL || OHNE_LIVE.some((re) => re.test(r))) return SITE_URL + r;
  let m;
  if ((m = /^\/(merzenich|golzheim|girbelsrath|morschenich|buergewald)\/$/.exec(r))) return `${CANONICAL_URL}/ort/${m[1]}/`;
  if (r === '/autor/redaktion/') return `${CANONICAL_URL}/redaktion/`;
  if (r === '/termine/melden/') return `${CANONICAL_URL}/termin-melden/`;
  if (r === '/sc-1919-merzenich/') return `${CANONICAL_URL}/vereine/sc-1919-merzenich/`;
  return CANONICAL_URL + r;
}
function bildAus(main) {
  const fig = /<figure class="(art-figure[^"]*)"([^>]*)>([\s\S]*?)<\/figure>/.exec(main);
  if (!fig) return null;
  // Bildstufe (V3): Symbolbilder tragen sie am figure-Tag (symbolbilder.mjs),
  // ein eigenes Foto ist Stufe A.
  const symbol = fig[1].includes('art-figure--symbol');
  // Archiv- und Beispielbilder zeigen nicht das Ereignis: Stufe B.
  const badgeRoh = (/<span class="figure-badge">([^<]*)<\/span>/.exec(fig[3]) || [])[1] || '';
  const stufe = (/data-bildstufe="([ABCO])"/.exec(fig[2]) || [])[1]
    || (symbol || /archivbild|beispielbild|ortsansicht/i.test(badgeRoh) ? 'B' : 'A');
  fig.splice(2, 1);
  const img = /<img\b([^>]*)>/.exec(fig[2]); if (!img) return null;
  const attr = (n) => { const m = new RegExp(`\\b${n}="([^"]*)"`).exec(img[1]); return m ? entschaerfen(m[1]) : ''; };
  const src = attr('src'); if (!src) return null;
  return {
    src, srcset: attr('srcset'), alt: attr('alt'), width: Number(attr('width')) || 0, height: Number(attr('height')) || 0,
    fit: /class="media contain"/.test(fig[2]) ? 'contain' : '',
    badge: text(erstes(/<span class="figure-badge">([^<]*)<\/span>/, fig[2])),
    credit: creditKurz(text(erstes(/<span>Bild: ([\s\S]*?)<\/span>/, fig[2])).replace(/\s*·\s*Bildquelle\s*$/, '')),
    symbol, stufe,
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
// Motiv eines Bildes unabhaengig von Groessenvariante und Format:
// /assets/places/golzheim-1440.webp und -720.webp sind dasselbe Motiv, ebenso
// ein Poolfoto als .jpg und als -800.webp. Die Startseite zaehlt Motive, nicht
// URLs (CI: kein Motiv oefter als zweimal sichtbar).
export const motivSchluessel = (src) => String(src || '').split(/[?#]/)[0].replace(/^.*?\/assets\//, '').replace(/-\d{3,4}(?=\.[a-z0-9]+$)/i, '').replace(/\.[a-z0-9]+$/i, '');
// Intl liefert "28.09." schon mit Schlusspunkt; ein angehaengter Punkt ergab "28.09.." im Suchindex.
export const dmyKurz = (iso) => { const d = new Date(iso); return Number.isNaN(d.getTime()) ? '' : new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit' }).format(d).replace(/\.?$/, '.'); };
export const dmyLang = (iso) => { const d = new Date(iso); return Number.isNaN(d.getTime()) ? '' : new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit', year: 'numeric' }).format(d) + ' · ' + new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', hour: '2-digit', minute: '2-digit' }).format(d) + ' Uhr'; };

// Sportbezug (Entscheidung KBS 26.09.2026: Startseite komplett sportfrei).
// Rubrik Sport ist immer Sport; Blaulicht, Rathaus und Wirtschaft nie. In allen
// anderen Rubriken entscheidet ein Muster ueber Titel, Teaser, Kicker und Themen
// (Sportlerheim-Ausbau, Fanclub-Fahrt usw.). Ausnahmen per URL in deploy/startseite.json.
const STARTSEITE = JSON.parse(readFileSync(new URL('./startseite.json', import.meta.url), 'utf8'));
export const SPORT_MUSTER = /fu(?:ß|ss)ball|kreisliga|bezirksliga|landesliga|bundesliga|sc 1919|sc merzenich|fc golzheim|sv morschenich|rhenania|fan-?club|fc-fanclub|1\. fc k(?:ö|oe)ln|sportlerheim|sportplatz|kunstrasen|tennis|tischtennis|billard|badminton|tv merzenich|turnverein|handball|volleyball|leichtathlet|sportverein|e-jugend|d-jugend|c-jugend|spieltag|\bsport\b/i;
const NIE_SPORT_RESSORTS = new Set(['blaulicht', 'rathaus', 'wirtschaft']);
export function sportBezug(a) {
  if (!a) return false;
  const url = a.url || '';
  if (STARTSEITE.sport.nieSport.includes(url)) return false;
  if (STARTSEITE.sport.immerSport.includes(url)) return true;
  if (a.ressort === 'sport' || /^\/sport\//.test(url) || /^\/sc-1919-merzenich\//.test(url)) return true;
  if (NIE_SPORT_RESSORTS.has(a.ressort)) return false;
  const text = [a.titel, a.teaser, a.kicker, (a.themen || []).map((t) => `${t.label} ${t.slug}`).join(' ')].join(' | ');
  return SPORT_MUSTER.test(text);
}
// Termine: gleiches Muster ueber Titel, Kategorie und Veranstalter.
export function sportTermin(t) {
  if (!t) return false;
  const text = [t.titel, t.title, t.kategorie, t.category, t.veranstalter, t.organizer, t.name].filter(Boolean).join(' | ');
  return SPORT_MUSTER.test(text);
}
