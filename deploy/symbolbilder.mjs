#!/usr/bin/env node
/**
 * Symbolbilder fuer Meldungen ohne eigenes Foto - ausgelieferte Seite.
 *
 * Bibliothek und Reihenfolge wie in docs/image-fallbacks.md (ChatGPT, 16.09.):
 * 1. eigenes Bild mit geklaerten Rechten, 2. lizenziertes Archivbild,
 * 3. Symbolbild aus dieser Liste. Ein Symbolbild ist immer als solches
 * gekennzeichnet, traegt Urheber, Lizenz und Link zur Bildquelle.
 *
 * Die Bilder werden von Wikimedia Commons geladen (Hotlink), nicht kopiert:
 * "externes Bild NICHT als lokales Asset uebernehmen" gilt, solange die
 * Rechte nicht am eigenen Bestand dokumentiert sind. CSP erlaubt img-src https:.
 *
 * Was das Skript tut (idempotent):
 * - Artikelseiten unter den Ressorts ohne <figure> im Hauptteil bekommen
 *   eine Symbolbild-Figur vor dem Text. Blaulicht: Feuerwehr, wenn der Text
 *   danach klingt, sonst Polizei.
 * - api/editorial-current.json: Aufmacher und Zweitkarte ohne Bild bekommen
 *   die Symbolbild-Felder, damit die Startseite und die Archive sie zeigen.
 *
 * Aufruf: node deploy/symbolbilder.mjs [--check]
 */

import { readFileSync, writeFileSync, existsSync, readdirSync, statSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');

export const RECHTE_GEPRUEFT_AM = '2026-09-16';

const commons = (datei, breite) => `https://commons.wikimedia.org/wiki/Special:FilePath/${encodeURIComponent(datei)}?width=${breite}`;
const quelle = (datei) => `https://commons.wikimedia.org/wiki/File:${encodeURIComponent(datei.replace(/ /g, '_'))}`;

export const SYMBOLBILDER = {
  polizei:    { datei: 'Neue Streifenwagen für die Polizei vorgestellt.jpg', alt: 'Streifenwagen der Polizei Nordrhein-Westfalen', urheber: 'IM NRW', lizenz: 'CC0 1.0' },
  feuerwehr:  { datei: 'Feuerwehrmänner im Einsatz.jpg', alt: 'Feuerwehrkräfte bei einem Löscheinsatz', urheber: 'AK-Bino', lizenz: 'CC BY-SA 4.0' },
  gemeinde:   { datei: 'Merzenich Rathaus HDR.jpg', alt: 'Rathaus der Gemeinde Merzenich', urheber: 'Karl-Heinz Meurer', lizenz: 'CC BY-SA 3.0' },
  leben:      { datei: 'Merzenich Alte Pfarrkirche.jpg', alt: 'Alte Pfarrkirche in Merzenich', urheber: 'Karl-Heinz Meurer', lizenz: 'CC BY-SA 3.0' },
  termine:    { datei: 'Merzenich Denkmal-Nr. 18, Lindenplatz (1235).jpg', alt: 'Lindenplatz in Merzenich', urheber: 'Käthe und Bernd Limburg', lizenz: 'CC BY-SA 3.0 DE' },
  sport:      { datei: '2026-08-30 Fußballplatz Trogen HOF8480 RAW-Export.png', alt: 'Fußballplatz als Symbolbild für Lokalsport', urheber: 'PantheraLeo1359531', lizenz: 'CC BY-SA 4.0' },
  stellen:    { datei: 'LOOM office workspaces.jpg', alt: 'Arbeitsplätze in einem Büro als Symbolbild für Stellenangebote', urheber: 'Loominade', lizenz: 'CC0 1.0' },
  // Immobilien: bewusst KEIN Foto eines konkreten Hauses neben einem
  // Inserat - es wuerde als das angebotene Objekt gelesen (Vorgabe §4).
};

export function symbolbild(art, breite = 1600) {
  const b = SYMBOLBILDER[art] || SYMBOLBILDER.gemeinde;
  return {
    art,
    src: commons(b.datei, breite),
    alt: b.alt,
    credit: `${b.urheber} / Wikimedia Commons · ${b.lizenz}`,
    creditVoll: `Symbolbild · ${b.urheber} / Wikimedia Commons · ${b.lizenz}`,
    sourceUrl: quelle(b.datei),
    lizenz: b.lizenz,
  };
}

const FEUER = /feuerwehr|brand\b|brennt|rauch|lösch|drehleiter|rettungsdienst|technische hilfe|feuer\b/i;

export function artFuerRessort(ressort, text = '') {
  switch (ressort) {
    case 'blaulicht': return FEUER.test(text) ? 'feuerwehr' : 'polizei';
    case 'sport': return 'sport';
    case 'termine': case 'vereine': return 'termine';
    case 'leben': case 'menschen': return 'leben';
    case 'wirtschaft': return 'stellen';
    default: return 'gemeinde';
  }
}

const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
// Externe Symbolbilder laufen ueber den eigenen Bildproxy (/api/bild im
// Node-Dienst): der Browser spricht nie mit Wikimedia, kein Platzhalter noetig.
const PROXY_HOSTS = ['commons.wikimedia.org', 'upload.wikimedia.org'];
export function bildUrl(u) {
  try { const x = new URL(u); if (/^https?:$/.test(x.protocol) && PROXY_HOSTS.includes(x.host)) return '/api/bild?u=' + encodeURIComponent(x.href); } catch { /* lokal */ }
  return u;
}

export function figurHtml(bild) {
  return `<figure class="art-figure art-figure--symbol" data-symbolbild="${esc(bild.art)}"><div class="media"><img src="${esc(bildUrl(bild.src))}" alt="${esc(bild.alt)}" loading="eager" decoding="async" referrerpolicy="no-referrer"></div>` +
    `<figcaption><span><span class="figure-badge">Symbolbild</span> · ${esc(bild.alt)}. Kein Foto vom Ereignis.</span>` +
    `<span>Bild: ${esc(bild.credit)} · <a href="${esc(bild.sourceUrl)}" target="_blank" rel="noopener noreferrer">Bildquelle</a></span></figcaption></figure>`;
}

// ------------------------------------------------------------------ Lauf
function seitenUnter(d) { const out = []; (function lauf(x) { for (const e of readdirSync(x)) { const p = join(x, e); statSync(p).isDirectory() ? lauf(p) : e === 'index.html' && out.push(p); } })(d); return out; }

function textAus(html) {
  const main = html.slice(html.indexOf('<main'), html.indexOf('</main>'));
  return main.replace(/<script[\s\S]*?<\/script>/g, '').replace(/<[^>]+>/g, ' ');
}

function hauptlauf() {
  const nurPruefen = process.argv.includes('--check');
  const RESSORTS = ['nachrichten', 'blaulicht', 'sport', 'rathaus', 'leben', 'wirtschaft', 'menschen'];
  const geaendert = [];
  const versorgt = [];

  for (const ressort of RESSORTS) {
    const ordner = join(site, ressort);
    if (!existsSync(ordner)) continue;
    for (const pfad of seitenUnter(ordner)) {
      if (pfad === join(ordner, 'index.html')) continue;
      const html = readFileSync(pfad, 'utf8');
      const main = html.slice(html.indexOf('<main'), html.indexOf('</main>'));
      if (main.includes('<figure')) {
        // Frueher gesetzte Symbolbilder: externe Adresse auf den Bildproxy umstellen.
        const neu = html.replace(/(<figure class="art-figure art-figure--symbol"[^>]*>\s*<div class="media"><img )src="([^"]*)"(?: data-extern-src="([^"]*)")?(?: class="extern-gesperrt")?/g, (m, a, src, ext) => { const quelle = (ext || src).replace(/&amp;/g, '&'); return quelle.startsWith('/api/bild') ? m : `${a}src="${esc(bildUrl(quelle))}"`; });
        if (neu !== html) { geaendert.push(pfad.replace(site + '/', '')); if (!nurPruefen) writeFileSync(pfad, neu); }
        continue;
      }
      const anker = '<div class="article-body" data-readable>';
      if (!main.includes(anker)) continue;
      const bild = symbolbild(artFuerRessort(ressort, textAus(html)));
      const neu = html.replace(anker, anker + figurHtml(bild));
      versorgt.push(`${pfad.replace(site + '/', '')} -> ${bild.art}`);
      if (neu !== html) { geaendert.push(pfad.replace(site + '/', '')); if (!nurPruefen) writeFileSync(pfad, neu); }
    }
  }

  // Redaktions-JSON: Aufmacher/Zweitkarte ohne Bild bekommen das Symbolbild
  // ihres Artikels, wenn auch der Artikel keins hat.
  const jsonPfad = join(site, 'api', 'editorial-current.json');
  if (existsSync(jsonPfad)) {
    const roh = readFileSync(jsonPfad, 'utf8');
    const d = JSON.parse(roh);
    let dirty = false;
    for (const k of ['hero', 'secondary']) {
      const s = d[k];
      if (!s || !s.url || s.image) continue;
      const seite = join(site, s.url.replace(/^\//, ''), 'index.html');
      if (!existsSync(seite)) continue;
      const html = readFileSync(seite, 'utf8');
      const fig = html.match(/<figure class="art-figure[^"]*"[^>]*data-symbolbild="([a-z]+)"/);
      if (!fig) continue; // Artikel hat ein echtes Bild oder gar keine Figur: nichts erfinden.
      const bild = symbolbild(fig[1]);
      Object.assign(s, { image: bild.src, imageAlt: bild.alt, imageBadge: 'Symbolbild', imageCredit: bild.creditVoll, imageType: 'symbol', imageSourceUrl: bild.sourceUrl, imageLicense: bild.lizenz, imageRightsCheckedAt: RECHTE_GEPRUEFT_AM });
      dirty = true; versorgt.push(`editorial-current.json ${k} -> ${bild.art}`);
    }
    if (dirty) { const neu = JSON.stringify(d, null, 2) + '\n'; if (neu !== roh) { geaendert.push('api/editorial-current.json'); if (!nurPruefen) writeFileSync(jsonPfad, neu); } }
  }

  for (const v of versorgt) console.log('  ' + v);
  if (nurPruefen) { if (geaendert.length) { console.log('Nicht aktuell: ' + geaendert.join(', ')); process.exit(2); } console.log('Symbolbilder aktuell.'); }
  else console.log(geaendert.length ? 'Geschrieben: ' + geaendert.join(', ') : 'Nichts zu aendern.');
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) hauptlauf();
