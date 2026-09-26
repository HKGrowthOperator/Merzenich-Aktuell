#!/usr/bin/env node
/**
 * Foto des Tages (KBS 26.09.2026): schreibt assets/foto-des-tages.json und den
 * Stand des Bautags in die Startseite. assets/foto-des-tages.js waehlt im Browser
 * das Foto fuer das heutige Datum (Berliner Zeit): zuerst ein datierter Eintrag
 * aus deploy/foto-des-tages.json, sonst reihum eine gesichtete Ortsansicht aus
 * deploy/ortsbilder.json. So wechselt das Foto taeglich, auch ohne neuen Build.
 * Aufruf: node deploy/foto-des-tages.mjs [--check]
 */
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const ORTE = { merzenich: 'Merzenich', golzheim: 'Golzheim', girbelsrath: 'Girbelsrath', morschenich: 'Morschenich', buergewald: 'Bürgewald' };

const fdt = JSON.parse(readFileSync(join(wurzel, 'deploy', 'foto-des-tages.json'), 'utf8'));
const orte = JSON.parse(readFileSync(join(wurzel, 'deploy', 'ortsbilder.json'), 'utf8'));
const bibliothek = JSON.parse(readFileSync(join(site, 'data', 'editorial-images', 'editorial-images.json'), 'utf8')).images;
const nachId = new Map(bibliothek.map((m) => [m.id, m]));
const fehler = [];

const eintraege = (fdt.eintraege || []).map((e) => {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(e.datum || '')) fehler.push(`Eintrag ohne gueltiges datum: ${JSON.stringify(e).slice(0, 80)}`);
  if (!e.src || !existsSync(join(site, String(e.src).replace(/^\//, '')))) fehler.push(`${e.datum}: Bild ${e.src} fehlt`);
  if (!e.alt || !e.credit) fehler.push(`${e.datum}: alt und credit sind Pflicht`);
  return { datum: e.datum, src: e.src, alt: e.alt, ort: e.ort || '', credit: [e.credit, e.lizenz].filter(Boolean).join(' · '), leser: true };
});
const reihe = orte.ansichten.map((o) => {
  const m = o.pool ? nachId.get(o.pool) : null;
  if (o.pool && !m) { fehler.push(`${o.id}: Poolfoto ${o.pool} fehlt`); return null; }
  return { src: m ? m.src : o.src, alt: o.alt, ort: ORTE[o.ortsteil] || '', credit: `${o.credit} · ${o.lizenz}` };
}).filter(Boolean);
if (!reihe.length) fehler.push('keine Ortsansichten fuer die taegliche Reihe');

// Tag des Jahres nach Berliner Kalender. Gleiche Rechnung wie im Browser.
const berlinDatum = (d) => new Intl.DateTimeFormat('en-CA', { timeZone: 'Europe/Berlin' }).format(d);
const tagesnummer = (iso) => Math.floor(Date.parse(`${iso}T00:00:00Z`) / 864e5);
const heute = berlinDatum(new Date());
const fotoFuer = (iso) => eintraege.find((e) => e.datum === iso) || reihe[tagesnummer(iso) % reihe.length];
const f = fotoFuer(heute);
const datumText = new Intl.DateTimeFormat('de-DE', { timeZone: 'UTC', weekday: 'long', day: 'numeric', month: 'long' }).format(new Date(`${heute}T12:00:00Z`));

const geaendert = [];
{
  const json = JSON.stringify({ eintraege, reihe }) + '\n';
  const ziel = join(site, 'assets', 'foto-des-tages.json');
  if (!existsSync(ziel) || readFileSync(ziel, 'utf8') !== json) { geaendert.push('assets/foto-des-tages.json'); if (!nurPruefen) writeFileSync(ziel, json); }
}
{
  const pfad = join(site, 'index.html');
  const alt = readFileSync(pfad, 'utf8');
  const MARKE = /<!-- fotodestages:start -->[\s\S]*?<!-- fotodestages:end -->/;
  const block = '<!-- fotodestages:start -->'
    + '<section class="ansichten foto-des-tages" aria-labelledby="ansichten-titel" data-foto-des-tages>'
    + `<figure class="ansichten-bild shell"><img id="foto-des-tages-bild" src="${esc(f.src)}" alt="${esc(f.alt)}" width="1440" height="960" loading="lazy" decoding="async"></figure>`
    + '<div class="shell ansichten-text">'
    + `<p class="ansichten-marke">Foto des Tages · <time id="foto-des-tages-datum" datetime="${heute}">${esc(datumText)}</time></p>`
    + `<h2 id="ansichten-titel" data-ansicht-ort>${esc(f.ort || 'Gemeinde Merzenich')}</h2>`
    + `<p class="ansichten-beschreibung" data-ansicht-text>${esc(f.alt)}.</p>`
    + `<p class="ansichten-credit" id="foto-des-tages-credit">Foto: ${esc(f.credit)}</p>`
    + '<a class="ansichten-senden" href="/meldung-senden/#formular">Ihr Foto des Tages einsenden</a>'
    + '</div></section><!-- fotodestages:end -->';
  let neu = alt;
  if (MARKE.test(alt)) neu = alt.replace(MARKE, () => block);
  else {
    // Einmalige Umstellung: die bisherige Sektion "Ansichten aus der Gemeinde".
    const i = alt.indexOf('<section class="ansichten"');
    const j = i >= 0 ? alt.indexOf('</section>', i) : -1;
    if (i < 0 || j < 0) fehler.push('Startseite: Sektion fuer das Foto des Tages fehlt');
    else neu = alt.slice(0, i) + block + alt.slice(j + '</section>'.length);
  }
  if (neu !== alt) { geaendert.push('index.html'); if (!nurPruefen) writeFileSync(pfad, neu); }
}

if (fehler.length) { console.error('Foto des Tages: ' + fehler.join('\n  ')); process.exit(2); }
console.log(`Foto des Tages: ${eintraege.length} datierte Einsendung(en), ${reihe.length} Ortsansichten in der Reihe; heute ${f.ort || ''}; ${geaendert.length} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}.`);
if (nurPruefen && geaendert.length) process.exit(2);
