#!/usr/bin/env node
/**
 * Merzenich Aktuell - Quellen abrufen (Recherche-Rohdaten).
 *
 * Holt die oeffentlichen Seiten der Quellen, aus denen die Redaktion schreibt,
 * und legt sie als Beleg unter imports/quellen/ ab. Veroeffentlicht nichts:
 * Meldungen, Stellen und Zuordnungen zu Ortsteilen entstehen erst daraus,
 * mit Quellenlink.
 *
 *   feuerwehr.json  Einsatzliste der Freiwilligen Feuerwehr Merzenich + Einsatzseiten
 *   polizei.json    Presseportal Blaulicht, Suche nach Ort (Merzenich + Ortsteile)
 *                   + Pressemitteilungen (Text, Bild-URLs mit Bildhinweis)
 *   gemeinde.json   "Aktuelles" der Gemeinde Merzenich + Beitraege
 *   schulen.json    Aktuelles der Grundschulen (KGS Merzenich, KGS Golzheim)
 *   heimatinfo.json Heimat-Info-App der Gemeinde (Gemeinde, Vereine, Schulen)
 *   jobs.json       Jobboerse der Bundesagentur, Umkreis 25 km um Merzenich
 *   bilder/         Fotos der Polizei zu Meldungen aus inhalte/meldungen
 *                   (Presseportal, "highlight"-Fassung) zur Sichtung;
 *                   bilder/index.json nennt Quelle, alt-Text und Groesse.
 *                   Auf die Seite kommt ein Foto erst mit quellbild.freigegeben.
 *
 * Je Seite: URL, Abrufzeit, HTTP-Status, Titel, Text (ohne Skripte, Stil,
 * Navigation), Links, Bilder mit alt-Text. Keine Auswertung, keine Annahmen
 * ueber den Seitenaufbau: Die Auswertung folgt, wenn die echten Seiten
 * vorliegen.
 *
 * Laeuft nur in GitHub Actions (der Container der Redaktion erreicht die
 * Quellen nicht). Aufruf: node deploy/quellen-abruf.mjs [--nur=feuerwehr,...]
 */
import { writeFileSync, mkdirSync, readFileSync, readdirSync, existsSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const ziel = join(wurzel, 'imports', 'quellen');
const UA = 'MerzenichAktuell-Recherche/1.0 (+https://merzenichaktuell.hk-growthoperator.de; Lokalredaktion)';
const nur = (process.argv.find((a) => a.startsWith('--nur=')) || '').slice(6).split(',').filter(Boolean);
const ORTE = ['Merzenich', 'Golzheim', 'Girbelsrath', 'Morschenich', 'Bürgewald'];
const pause = (ms) => new Promise((ok) => setTimeout(ok, ms));

// Weiterleitungen von Hand, mit Cookies: Die Seiten der Gemeinde setzen beim
// ersten Aufruf ein Cookie und leiten auf sich selbst weiter; fetch mit
// redirect:'follow' gab dort nur 302 zurueck.
async function holen(url, accept = 'text/html') {
  const cookies = new Map();
  let ziel = url, letzte = null;
  for (let schritt = 0; schritt < 8; schritt++) {
    const ba = ziel.startsWith('https://rest.arbeitsagentur.de/') ? { 'X-API-Key': 'jobboerse-jobsuche' } : {};
    const kekse = [...cookies].map(([k, v]) => `${k}=${v}`).join('; ');
    let r;
    try {
      r = await fetch(ziel, { headers: { 'User-Agent': UA, Accept: accept, 'Accept-Language': 'de-DE,de;q=0.9', ...(kekse ? { Cookie: kekse } : {}), ...ba }, redirect: 'manual', signal: AbortSignal.timeout(25000) });
    } catch (e) {
      if (schritt === 0 && !letzte) { await pause(2000); letzte = 'fehler'; schritt--; continue; }
      return { status: 'fehler: ' + (e.cause?.code || e.name), url: ziel, text: '' };
    }
    for (const c of r.headers.getSetCookie?.() || []) { const [kv] = c.split(';'); const i = kv.indexOf('='); if (i > 0) cookies.set(kv.slice(0, i).trim(), kv.slice(i + 1).trim()); }
    const ort = r.headers.get('location');
    if (r.status >= 300 && r.status < 400 && ort) { ziel = new URL(ort, ziel).href; letzte = r.status; continue; }
    const text = await r.text();
    // Fehlerseiten kurz mitschreiben, damit der naechste Lauf den Grund zeigt.
    return { status: r.status, url: ziel, text, weiterleitungen: schritt, ...(r.status !== 200 ? { fehlertext: text.slice(0, 400) } : {}) };
  }
  return { status: 'zu viele Weiterleitungen', url: ziel, text: '' };
}

const ENT = { amp: '&', lt: '<', gt: '>', quot: '"', apos: "'", nbsp: ' ', shy: '', auml: 'ä', ouml: 'ö', uuml: 'ü', Auml: 'Ä', Ouml: 'Ö', Uuml: 'Ü', szlig: 'ß', ndash: '–', mdash: '—', bdquo: '„', ldquo: '“', rdquo: '”', laquo: '«', raquo: '»', euro: '€', eacute: 'é' };
const entschluesseln = (s) => String(s).replace(/&(#x[0-9a-f]+|#\d+|[a-z]+);/gi, (m, e) => e[0] === '#' ? String.fromCodePoint(e[1] === 'x' || e[1] === 'X' ? parseInt(e.slice(2), 16) : parseInt(e.slice(1), 10)) : (ENT[e] ?? m));
const attr = (tag, name) => { const m = new RegExp(`\\b${name}\\s*=\\s*("([^"]*)"|'([^']*)'|([^\\s>]+))`, 'i').exec(tag); return m ? entschluesseln(m[2] ?? m[3] ?? m[4] ?? '') : null; };
const absolut = (href, basis) => { try { return new URL(href, basis).href; } catch { return null; } };

// Seite zerlegen: Titel, Metadaten, Haupttext, Links, Bilder.
function zerlegen(html, basis) {
  const titel = entschluesseln((/<title[^>]*>([\s\S]*?)<\/title>/i.exec(html) || [])[1] || '').replace(/\s+/g, ' ').trim();
  const meta = {};
  for (const m of html.matchAll(/<meta\b[^>]*>/gi)) {
    const k = attr(m[0], 'property') || attr(m[0], 'name');
    const v = attr(m[0], 'content');
    if (k && v && /^(og:|article:|description$|date$|dc\.|author$|keywords$)/i.test(k)) meta[k] = v;
  }
  // Inhalt: <main> oder <article>, sonst body; Navigation, Kopf und Fuss raus.
  let teil = (/<main\b[\s\S]*?<\/main>/i.exec(html) || /<article\b[\s\S]*<\/article>/i.exec(html) || /<body\b[\s\S]*<\/body>/i.exec(html) || [html])[0];
  teil = teil.replace(/<(script|style|noscript|svg|nav|header|footer|form|iframe)\b[\s\S]*?<\/\1>/gi, ' ');
  const links = [];
  for (const m of teil.matchAll(/<a\b([^>]*)>([\s\S]*?)<\/a>/gi)) {
    const href = absolut(attr('<a ' + m[1] + '>', 'href') || '', basis);
    const text = entschluesseln(m[2].replace(/<[^>]+>/g, ' ')).replace(/\s+/g, ' ').trim();
    if (href && /^https?:/.test(href)) links.push({ text: text.slice(0, 200), href });
  }
  const bilder = [];
  for (const m of teil.matchAll(/<img\b[^>]*>/gi)) {
    const src = absolut(attr(m[0], 'src') || attr(m[0], 'data-src') || '', basis);
    if (src && !/^data:/.test(src)) bilder.push({ src, alt: attr(m[0], 'alt') || '', title: attr(m[0], 'title') || '' });
  }
  const text = entschluesseln(teil
    .replace(/<br\s*\/?>/gi, '\n').replace(/<\/(p|div|li|h[1-6]|tr|section|article|dd|dt)>/gi, '\n')
    .replace(/<[^>]+>/g, ' '))
    .split('\n').map((z) => z.replace(/\s+/g, ' ').trim()).filter(Boolean).join('\n');
  return { titel, meta, text: text.slice(0, 40000), links: links.slice(0, 400), bilder: bilder.slice(0, 60) };
}

async function seite(url) {
  const r = await holen(url);
  const eintrag = { url, endUrl: r.url, abgerufen: new Date().toISOString(), status: r.status, ...(r.fehlertext ? { fehlertext: r.fehlertext } : {}) };
  if (r.status === 200) Object.assign(eintrag, zerlegen(r.text, r.url));
  await pause(1200);
  return eintrag;
}

// Liste abrufen, passende Unterseiten folgen (hoechstens max, ohne Dubletten).
async function listeMitDetails({ listen, detail, max }) {
  const seiten = [];
  for (const u of listen) seiten.push(await seite(u));
  const ziele = [...new Set(seiten.flatMap((s) => (s.links || []).map((l) => l.href.split('#')[0])).filter((h) => detail.test(h)))].slice(0, max);
  const details = [];
  for (const u of ziele) details.push(await seite(u));
  return { listen: seiten, details };
}

const QUELLEN = {
  feuerwehr: () => listeMitDetails({
    listen: ['https://feuerwehr-merzenich.de/einsaetze', 'https://feuerwehr-merzenich.de/einsaetze?page=1', 'https://feuerwehr-merzenich.de/einsaetze?page=2'],
    detail: /^https:\/\/(www\.)?feuerwehr-merzenich\.de\/einsaetze\/[^?#/]+/, max: 40,
  }),
  polizei: () => listeMitDetails({
    listen: ORTE.map((o) => `https://www.presseportal.de/blaulicht/r/${encodeURIComponent(o)}`),
    detail: /^https:\/\/www\.presseportal\.de\/blaulicht\/pm\/\d+\/\d+/, max: 60,
  }),
  gemeinde: () => listeMitDetails({
    listen: ['https://www.gemeinde-merzenich.de/aktuelles/index.php', 'https://www.gemeinde-merzenich.de/aktuelles/'],
    detail: /^https:\/\/www\.gemeinde-merzenich\.de\/aktuelles\/[^?#]+\.php$/, max: 40,
  }),
  // Die Website der Gemeinde sperrt Abrufe aus GitHub Actions ("403: Zugriff
  // verweigert"). Dieselben Mitteilungen stehen in der Heimat-Info-App der
  // Gemeinde, dort auch Vereine, Schulen, Feuerwehr.
  heimatinfo: () => listeMitDetails({
    listen: ['https://www.heimat-info.de/gemeinden/merzenich', 'https://www.heimat-info.de/gemeinden/merzenich/organisationen/gemeinde-merzenich', 'https://www.heimat-info.de/gemeinden/merzenich/organisationen'],
    detail: /^https:\/\/www\.heimat-info\.de\/beitraege\/[0-9a-f-]{36}$/, max: 60,
  }),
  schulen: () => listeMitDetails({
    listen: ['https://kgs.gemeinde-merzenich.de/aktuelles/index.php', 'https://kgs-golzheim.gemeinde-merzenich.de/rubrik-unsere-schule/index.php'],
    detail: /^https:\/\/kgs(-golzheim)?\.gemeinde-merzenich\.de\/aktuelles\/[^?#]+\.php$/, max: 20,
  }),
  // Jobboerse der Bundesagentur: Arbeit (angebotsart 1) und Ausbildung (4).
  jobs: async () => {
    const seiten = [];
    for (const art of [1, 4]) {
      let gesammelt = 0;
      for (let page = 1; page <= 4; page++) {
        const frage = `wo=Merzenich&umkreis=25&size=100&page=${page}&angebotsart=${art}`;
        let url = `https://rest.arbeitsagentur.de/jobboerse/jobsuche-service/pc/v4/jobs?${frage}`;
        let r = await holen(url, 'application/json');
        // Zweiter bekannter Pfad der Schnittstelle (App-Variante).
        if (r.status !== 200) { const erst = r; url = `https://rest.arbeitsagentur.de/jobboerse/jobsuche-service/pc/v4/app/jobs?${frage}`; r = await holen(url, 'application/json'); if (r.status !== 200) r.fehlertext = `pc/v4/jobs ${erst.status}: ${erst.fehlertext || ''} | app/jobs ${r.status}: ${r.fehlertext || ''}`; }
        let daten = null;
        try { daten = JSON.parse(r.text); } catch { /* bleibt null, der Status sagt warum */ }
        const stellen = daten?.stellenangebote ?? [];
        seiten.push({ url, abgerufen: new Date().toISOString(), status: r.status, gesamt: daten?.maxErgebnisse ?? null, stellen, ...(r.fehlertext ? { fehlertext: r.fehlertext } : {}) });
        gesammelt += stellen.length;
        await pause(800);
        if (!stellen.length || gesammelt >= (Number(daten?.maxErgebnisse) || 0)) break;
      }
    }
    return { listen: seiten };
  },
};

mkdirSync(ziel, { recursive: true });
let fehlerQuellen = 0;
for (const [name, lauf] of Object.entries(QUELLEN)) {
  if (nur.length && !nur.includes(name)) continue;
  const start = Date.now();
  const ergebnis = await lauf();
  const alle = [...(ergebnis.listen || []), ...(ergebnis.details || [])];
  const ok = alle.filter((s) => s.status === 200).length;
  if (!ok) fehlerQuellen++;
  writeFileSync(join(ziel, `${name}.json`), JSON.stringify({ quelle: name, abgerufen: new Date().toISOString(), ...ergebnis }, null, 1) + '\n');
  console.log(`${name.padEnd(10)} ${ok}/${alle.length} Seiten ok, ${ergebnis.details?.length ?? 0} Unterseiten, ${Math.round((Date.now() - start) / 1000)} s; Status: ${[...new Set(alle.map((s) => s.status))].join(', ')}`);
}
if (fehlerQuellen) console.log(`${fehlerQuellen} Quelle(n) ohne eine einzige erreichbare Seite.`);

// Fotos zu Polizeimeldungen, die als Meldung erscheinen: nur die Bilder der
// Mitteilung selbst ("highlight", keine Logos, kein "story_big"-Kachelbild),
// je Datei einmal. Sichtung auf Kennzeichen und Gesichter von Hand.
if (!nur.length || nur.includes('quellbilder')) {
  const bildOrdner = join(ziel, 'bilder');
  mkdirSync(bildOrdner, { recursive: true });
  const indexPfad = join(bildOrdner, 'index.json');
  const index = existsSync(indexPfad) ? JSON.parse(readFileSync(indexPfad, 'utf8')) : {};
  const polizei = JSON.parse(readFileSync(join(ziel, 'polizei.json'), 'utf8'));
  const gesucht = new Set();
  for (const datei of readdirSync(join(wurzel, 'inhalte', 'meldungen')).filter((d) => d.endsWith('.json'))) {
    for (const m of JSON.parse(readFileSync(join(wurzel, 'inhalte', 'meldungen', datei), 'utf8')).meldungen || []) {
      const pm = /presseportal\.de\/blaulicht\/pm\/\d+\/(\d+)/.exec(m.quelle?.url || '');
      if (pm) gesucht.add(pm[1]);
    }
  }
  let neu = 0, fehler = 0;
  for (const d of polizei.details || []) {
    const pm = /\/pm\/\d+\/(\d+)/.exec(d.url)?.[1];
    if (!pm || !gesucht.has(pm)) continue;
    const fotos = [...new Map((d.bilder || []).filter((b) => /\/thumbnail\/highlight\//.test(b.src)).map((b) => [b.src, b])).values()];
    // Bildhinweis (Rechteinhaber, Nutzungsbedingung) steht nur im HTML der
    // Mitteilung, nicht im Lesetext: Fundstellen woertlich mitschreiben.
    // Bildhinweis (Rechteinhaber, Nutzung) liefert das Presseportal je Foto
    // ueber /api/image_info.htx; die id steht im Markup der Mitteilung
    // (data-id + data-name). Text woertlich mitschreiben.
    const bildinfo = {};
    if (fotos.length) {
      const html = String((await holen(d.url)).text || '');
      for (const b of fotos) {
        const name = b.src.split('/').pop();
        const id = new RegExp(`data-id="([0-9a-f]{16,})" data-name="${name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}"`).exec(html)?.[1];
        if (!id) { bildinfo[b.src] = { fehler: 'id im Markup nicht gefunden' }; continue; }
        const url = `https://www.presseportal.de/api/image_info.htx?id=${id}&story_id=${pm}&render=html`;
        const r = await holen(url);
        const text = entschluesseln(String(r.text || '').replace(/<script[\s\S]*?<\/script>/gi, ' ').replace(/<(br|\/p|\/div|\/li|\/dt|\/dd|\/tr)\b[^>]*>/gi, '\n').replace(/<[^>]+>/g, ' ')).replace(/[ \t]+/g, ' ').replace(/\n\s*/g, '\n').trim();
        bildinfo[b.src] = { url, status: r.status, text: text.slice(0, 2000) };
        await pause(400);
      }
    }
    for (const [i, b] of fotos.entries()) {
      const name = `${pm}-${i + 1}.jpg`;
      if (index[name] && existsSync(join(bildOrdner, name))) { index[name].bildinfo = bildinfo[b.src]; continue; }
      try {
        const r = await fetch(b.src, { headers: { 'User-Agent': UA }, signal: AbortSignal.timeout(25000) });
        if (r.status !== 200 || !/^image\//.test(r.headers.get('content-type') || '')) { fehler++; index[name] = { quelle: d.url, src: b.src, status: r.status }; continue; }
        const daten = Buffer.from(await r.arrayBuffer());
        writeFileSync(join(bildOrdner, name), daten);
        index[name] = { quelle: d.url, src: b.src, alt: b.alt || '', bytes: daten.length, abgerufen: new Date().toISOString(), bildinfo: bildinfo[b.src] };
        neu++;
      } catch (e) { fehler++; index[name] = { quelle: d.url, src: b.src, status: 'fehler: ' + (e.cause?.code || e.name) }; }
      await pause(500);
    }
  }
  writeFileSync(indexPfad, JSON.stringify(index, null, 1) + '\n');
  console.log(`quellbilder ${neu} neu, ${fehler} Fehler, ${Object.keys(index).length} im Index (${gesucht.size} Mitteilungen gesucht)`);
}
