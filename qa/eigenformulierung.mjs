#!/usr/bin/env node
/**
 * Merzenich Aktuell - Prueft, ob Claude-Meldungen eigenstaendig formuliert sind (28.10.2026).
 *
 * Entscheidung Luis Kummer: Texte werden aus den Fakten der Quellen neu geschrieben, nicht
 * abgeschrieben. Das Skript laedt jede Quelle einer Meldung (quelle, weitereQuellen; HTML und
 * PDF), entfernt woertliche, gekennzeichnete Zitate aus dem Artikel und vergleicht dann
 * Wortfolgen von acht Woertern.
 *
 * Aufruf (im Redaktionslauf nach der Kette, Netz noetig):
 *   node qa/eigenformulierung.mjs --datum 2026-10-29 --lauf morgens   Paket des Laufs
 *   node qa/eigenformulierung.mjs --paket inhalte/meldungen/<datei>.json [--slug <slug>]
 *   Optionen: --json
 *
 * Fehler (Exit 1): gleiche Wortfolge von 12 oder mehr Woertern ausserhalb eines Zitats, oder
 * mehr als 10 Prozent der Achtwortfolgen des Artikels stehen woertlich in einer Quelle.
 * Hinweis: Wortfolge ab 9 Woertern; Quelle nicht abrufbar (dann ist die Meldung fuer diese
 * Quelle ungeprueft und das gehoert ins Dossier).
 */
import { readFileSync, writeFileSync, existsSync, mkdtempSync, rmSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { tmpdir } from 'node:os';
import { fileURLToPath } from 'node:url';
import { spawnSync } from 'node:child_process';

const args = process.argv.slice(2);
const opt = (n) => { const i = args.indexOf(n); return i >= 0 ? args[i + 1] : undefined; };
const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const DATUM = opt('--datum'); const LAUF = opt('--lauf');
const paket = opt('--paket') || (DATUM && LAUF ? `inhalte/meldungen/${DATUM}-recherche-claude-${LAUF}.json` : null);
const NUR = opt('--slug');
const ALS_JSON = args.includes('--json');
if (!paket) { console.error('Aufruf: --datum <JJJJ-MM-TT> --lauf <morgens|nachmittags> oder --paket <datei>'); process.exit(2); }
if (!existsSync(join(wurzel, paket))) { console.log(`Kein Paket ${paket} (Nullfund?): nichts zu pruefen.`); process.exit(0); }

const N = 8; const RUN_FEHLER = 12; const RUN_HINWEIS = 9; const ANTEIL_FEHLER = 0.10;
const UA = 'MerzenichAktuell-Redaktion/1.0 (https://merzenich-aktuell.de)';
const fehler = []; const hinweise = []; const ergebnis = [];

const entities = (s) => s.replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&#(\d+);/g, (_, n) => String.fromCodePoint(+n)).replace(/&[a-z]+;/g, ' ');
const htmlText = (h) => entities(h.replace(/<(script|style|noscript)[\s\S]*?<\/\1>/gi, ' ').replace(/<[^>]+>/g, ' '));
// Gekennzeichnete Zitate gelten nicht als Abschrift (SCHREIBREGELN.md Abschnitt 8).
const ohneZitate = (s) => s.replace(/„[^“”]{0,400}[“”]/g, ' ').replace(/"[^"]{0,400}"/g, ' ').replace(/»[^«]{0,400}«/g, ' ');
const woerter = (s) => s.toLowerCase().replace(/[^a-z0-9äöüß]+/gi, ' ').trim().split(/\s+/).filter(Boolean);

async function quelleLaden(url) {
  const r = await fetch(url, { headers: { 'User-Agent': UA }, redirect: 'follow', signal: AbortSignal.timeout(30000) });
  if (!r.ok) throw new Error(`HTTP ${r.status}`);
  const typ = r.headers.get('content-type') || '';
  const daten = Buffer.from(await r.arrayBuffer());
  if (/pdf/i.test(typ) || /\.pdf($|\?)/i.test(url)) {
    const dir = mkdtempSync(join(tmpdir(), 'eigen-'));
    try {
      writeFileSync(join(dir, 'q.pdf'), daten);
      const p = spawnSync('pdftotext', ['-layout', join(dir, 'q.pdf'), '-'], { encoding: 'utf8', maxBuffer: 64 * 1024 * 1024 });
      if (p.status !== 0) throw new Error('PDF nicht lesbar');
      return p.stdout;
    } finally { rmSync(dir, { recursive: true, force: true }); }
  }
  return htmlText(daten.toString('utf8'));
}

const cache = new Map();
const daten = JSON.parse(readFileSync(join(wurzel, paket), 'utf8'));
for (const m of daten.meldungen || []) {
  if (NUR && m.slug !== NUR) continue;
  const b = `${paket.split('/').pop()}/${m.slug}`;
  const artikel = woerter(ohneZitate([m.titel, m.dek, ...(m.absaetze || [])].join(' ')));
  const grams = []; for (let i = 0; i + N <= artikel.length; i++) grams.push(artikel.slice(i, i + N).join(' '));
  // Eigene Seiten (merzenich-aktuell.de) sind unser Text, kein Fremdtext.
  const quellen = [m.quelle, ...(m.weitereQuellen || [])].filter((q) => q && q.url && !/^https?:\/\/([^/]*\.)?merzenich-aktuell\.de\//.test(q.url));
  const zeile = { meldung: b, woerter: artikel.length, quellen: [] };
  for (const q of quellen) {
    let text;
    try {
      if (!cache.has(q.url)) cache.set(q.url, await quelleLaden(q.url));
      text = cache.get(q.url);
    } catch (e) { hinweise.push(`${b}: Quelle ${q.url} nicht abrufbar (${e.message}), Eigenformulierung dafuer ungeprueft`); zeile.quellen.push({ url: q.url, fehler: e.message }); continue; }
    const qw = woerter(text); const set = new Set();
    for (let i = 0; i + N <= qw.length; i++) set.add(qw.slice(i, i + N).join(' '));
    let treffer = 0, lauf = 0, laengster = 0, beispiel = '';
    grams.forEach((g, i) => {
      if (set.has(g)) { treffer++; lauf++; if (lauf + N - 1 > laengster) { laengster = lauf + N - 1; beispiel = artikel.slice(i - lauf + 1, i + N).join(' '); } }
      else lauf = 0;
    });
    const anteil = grams.length ? treffer / grams.length : 0;
    zeile.quellen.push({ url: q.url, anteil: Math.round(anteil * 1000) / 10, laengsteFolge: laengster });
    if (laengster >= RUN_FEHLER) fehler.push(`${b}: ${laengster} Woerter woertlich aus ${q.url}: „${beispiel}“ – neu formulieren oder als Zitat kennzeichnen`);
    else if (laengster >= RUN_HINWEIS) hinweise.push(`${b}: ${laengster} Woerter gleich mit ${q.url}: „${beispiel}“`);
    if (anteil > ANTEIL_FEHLER) fehler.push(`${b}: ${(anteil * 100).toFixed(1)} Prozent der Wortfolgen stehen woertlich in ${q.url} (hoechstens ${ANTEIL_FEHLER * 100})`);
  }
  ergebnis.push(zeile);
}

if (ALS_JSON) console.log(JSON.stringify({ fehler, hinweise, ergebnis }, null, 2));
else {
  for (const z of ergebnis) console.log(`${z.meldung}: ${z.woerter} Woerter; ${z.quellen.map((q) => q.fehler ? `${q.url} nicht abrufbar` : `${q.anteil} % / max. ${q.laengsteFolge} Woerter`).join('; ')}`);
  for (const f of fehler) console.log(`Fehler   ${f}`);
  for (const h of hinweise) console.log(`Hinweis  ${h}`);
  console.log(`\n${fehler.length} Fehler, ${hinweise.length} Hinweise.`);
}
process.exit(fehler.length ? 1 : 0);
