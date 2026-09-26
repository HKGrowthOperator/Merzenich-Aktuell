#!/usr/bin/env node
/**
 * Merzenich-Cockpit (KBS/Ordin 26.09.2026): eine Leiste direkt unter der Buehne
 * mit dem, was man in Merzenich gerade wissen will - nur echte Daten:
 *   Rathaus geoeffnet/geschlossen (Zeiten laut Gemeinde, deploy/cockpit.json),
 *   Wetter (dieselbe Quelle wie im Kopf, /api/weather.json),
 *   naechster Termin (Terminseiten, ohne Sport),
 *   letzter gemeldeter Feuerwehreinsatz (Einsatzmeldungen im Inhaltsindex),
 *   Notrufnummern.
 * assets/cockpit.js rechnet Rathaus-Status, "in N Tagen" und Wetter im Browser.
 * Aufruf: node deploy/cockpit.mjs [--check]
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { termineAusSeiten } from './lib-termine.mjs';
import { sportTermin, ORTSTEILE } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const cfg = JSON.parse(readFileSync(join(wurzel, 'deploy', 'cockpit.json'), 'utf8'));
const idx = JSON.parse(readFileSync(join(site, 'api', 'inhalte.json'), 'utf8'));
const tag = (iso) => new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', weekday: 'short', day: 'numeric', month: 'short' }).format(new Date(iso));
const uhr = (iso) => new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', hour: '2-digit', minute: '2-digit' }).format(new Date(iso));

// Naechster Termin (ohne Sport, die Startseite ist sportfrei).
const jetzt = new Date();
const termin = termineAusSeiten(site).filter((t) => t.ende >= jetzt && !sportTermin(t)).sort((a, b) => a.start - b.start)[0];

// Letzter gemeldeter Feuerwehreinsatz: Einsatzmeldungen tragen die Nummer im Pfad.
const einsatz = (idx.artikel || [])
  .map((a) => ({ a, m: /^\/blaulicht\/einsatz-(\d+)-/.exec(a.url) }))
  .filter((x) => x.m && x.a.datum)
  .sort((x, y) => String(y.a.datum).localeCompare(String(x.a.datum)))[0];

const kachel = (art, label, wert, klein, href, extra = '') => `${href ? `<a class="cockpit-kachel" href="${esc(href)}"` : '<div class="cockpit-kachel"'} data-cockpit="${art}"${extra}>`
  + `<span class="cockpit-label">${esc(label)}</span><strong class="cockpit-wert" data-cockpit-wert>${wert}</strong><small class="cockpit-klein" data-cockpit-klein>${klein}</small>${href ? '</a>' : '</div>'}`;

const teile = [];
teile.push(kachel('rathaus', 'Rathaus', 'Öffnungszeiten', esc(cfg.rathaus.quelle), cfg.rathaus.link, ` data-zeiten="${esc(JSON.stringify(cfg.rathaus.zeiten))}"`));
teile.push(kachel('wetter', 'Wetter in Merzenich', '–', 'Open-Meteo', '/service/', ' hidden'));
if (termin) teile.push(kachel('termin', 'Nächster Termin', esc(termin.titel), `${esc(tag(termin.start.toISOString()))}, ${esc(uhr(termin.start.toISOString()))} Uhr`, `/termine/${termin.slug}/`, ` data-start="${termin.start.toISOString()}"`));
if (einsatz) {
  const a = einsatz.a;
  const jahr = String(new Date(a.datum).getFullYear()).slice(2);
  const kurz = a.titel.split(/[:–]/)[0].trim();
  const ort = ORTSTEILE[a.ortsteil] || 'Gemeinde Merzenich';
  teile.push(kachel('einsatz', 'Feuerwehr · letzter Einsatz', `Nr. ${einsatz.m[1]}/${jahr}: ${esc(kurz)}`, `${esc(ort)} · ${esc(tag(a.datum))}`, a.url));
}
teile.push('<div class="cockpit-kachel cockpit-kachel--notruf" data-cockpit="notruf"><span class="cockpit-label">Notruf</span><strong class="cockpit-wert"><a href="tel:112">112</a> <a href="tel:110">110</a></strong><small class="cockpit-klein">Feuerwehr, Rettung · Polizei</small></div>');

const block = '<!-- cockpit:start --><section class="cockpit shell" aria-label="Merzenich jetzt"><div class="cockpit-innen"><h2 class="cockpit-titel">Merzenich <span>jetzt</span></h2>'
  + `<div class="cockpit-reihe">${teile.join('')}</div></div></section><!-- cockpit:end -->`;

const pfad = join(site, 'index.html');
const alt = readFileSync(pfad, 'utf8');
const MARKE = /<!-- cockpit:start -->[\s\S]*?<!-- cockpit:end -->/;
let neu = alt;
if (MARKE.test(alt)) neu = alt.replace(MARKE, () => block);
else if (alt.includes('<!-- start:oben:end -->')) neu = alt.replace('<!-- start:oben:end -->', '<!-- start:oben:end -->' + block);
else { console.error('Cockpit: Anker start:oben fehlt'); process.exit(2); }
if (!neu.includes('/assets/cockpit.js')) neu = neu.replace(/<script src="\/assets\/kopf\.js[^"]*" defer><\/script>/, (m) => m + '<script src="/assets/cockpit.js" defer></script>');
const geaendert = neu !== alt;
if (geaendert && !nurPruefen) writeFileSync(pfad, neu);
console.log(`Cockpit: ${teile.length} Kacheln${termin ? `, Termin ${termin.slug}` : ''}${einsatz ? `, Einsatz ${einsatz.m[1]}` : ''}; ${geaendert ? (nurPruefen ? 'nicht aktuell' : 'geschrieben') : 'aktuell'}.`);
if (nurPruefen && geaendert) process.exit(2);
