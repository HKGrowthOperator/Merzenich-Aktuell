#!/usr/bin/env node
/**
 * Merzenich-Cockpit (KBS/Ordin 26.09.2026): eine Leiste direkt unter der Buehne
 * mit dem, was man in Merzenich gerade wissen will - nur echte Daten:
 *   Rathaus geoeffnet/geschlossen (Zeiten laut Gemeinde, deploy/gemeinde.json),
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
import { GEMEINDE } from './lib-gemeinde.mjs';
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

// Gestaltung 30.09.2026 (Betreiber: „gut, aber zu generisch“): keine fuenf
// gleichen Kacheln mehr. Links ein rotes Ortsschild mit Datum und Wetter als
// grosser Zahl, in der Mitte drei kurze Saetze mit eigenem Zeichen (Rathaus,
// Termin, Feuerwehr), rechts die Notrufnummern als echte Tasten. Nichts wird
// abgeschnitten; Titel duerfen zwei Zeilen haben.
const ZEICHEN = {
  rathaus: '<path d="M3 20.5h18M5 20.5v-9M9.5 20.5v-9M14.5 20.5v-9M19 20.5v-9M3.5 11.5h17L12 4.5z"/>',
  termin: '<rect x="3.5" y="5" width="17" height="15.5" rx="1.5"/><path d="M3.5 9.5h17M8 3v4M16 3v4M8 13.5h3v3H8z"/>',
  einsatz: '<path d="M12 21c-3.9 0-6.5-2.6-6.5-6.1 0-3.6 3-5.6 3.6-9.4 2.4 1.5 3.6 3.8 3.6 5.9 1-.6 1.8-1.7 2-3.1 2 1.7 3.8 4 3.8 6.6 0 3.5-2.6 6.1-6.5 6.1z"/><path d="M12 21c-1.6 0-2.7-1.1-2.7-2.6 0-1.7 1.4-2.5 1.8-4.1 1.9 1.1 3.6 2.4 3.6 4.1 0 1.5-1.1 2.6-2.7 2.6z"/>',
};
const zeichen = (art) => `<svg class="mj-zeichen" viewBox="0 0 24 24" aria-hidden="true" focusable="false">${ZEICHEN[art]}</svg>`;
const heuteText = new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', weekday: 'long', day: 'numeric', month: 'long' }).format(jetzt);
const uhrKurz = (iso) => uhr(iso).replace(/:00$/, '');

const eintraege = [];
eintraege.push(`<a class="mj-eintrag" href="${esc(cfg.rathaus.link)}" data-cockpit="rathaus" data-zeiten="${esc(JSON.stringify(GEMEINDE.rathaus.zeiten))}">${zeichen('rathaus')}`
  + '<span class="mj-text"><span class="mj-lead"><span class="mj-status" aria-hidden="true"></span><span data-cockpit-wert>Rathaus</span></span>'
  + `<span class="mj-titel" data-cockpit-naechst>Öffnungszeiten der Gemeindeverwaltung</span><small class="mj-klein" data-cockpit-klein>${esc(cfg.rathaus.quelle)}</small></span></a>`);
if (termin) {
  const iso = termin.start.toISOString();
  eintraege.push(`<a class="mj-eintrag" href="/termine/${esc(termin.slug)}/" data-cockpit="termin" data-start="${iso}">${zeichen('termin')}`
    + `<span class="mj-text"><span class="mj-lead" data-cockpit-wert>${esc(tag(iso))}, ${esc(uhrKurz(iso))} Uhr</span>`
    + `<span class="mj-titel">${esc(termin.titel)}</span><small class="mj-klein">${termin.ort ? esc(termin.ort) : 'Nächster Termin'}</small></span></a>`);
}
if (einsatz) {
  const a = einsatz.a;
  const jahr = String(new Date(a.datum).getFullYear()).slice(2);
  const kurz = a.titel.split(/[:–]/)[0].trim();
  const ort = ORTSTEILE[a.ortsteil] || 'Gemeinde Merzenich';
  eintraege.push(`<a class="mj-eintrag" href="${esc(a.url)}" data-cockpit="einsatz">${zeichen('einsatz')}`
    + `<span class="mj-text"><span class="mj-lead">Feuerwehr · Einsatz ${einsatz.m[1]}/${jahr}</span>`
    + `<span class="mj-titel">${esc(kurz)}</span><small class="mj-klein">${esc(ort)} · ${esc(tag(a.datum))}</small></span></a>`);
}
const teile = eintraege;

const block = '<!-- cockpit:start --><section class="cockpit mj shell" aria-labelledby="mj-titel"><div class="mj-innen">'
  + `<div class="mj-schild"><div class="mj-kopf"><h2 class="mj-ort" id="mj-titel">Merzenich <span>jetzt</span></h2><p class="mj-datum" data-cockpit-datum>${esc(heuteText)}</p></div>`
  + '<p class="mj-wetter" data-cockpit="wetter" hidden><span class="mj-grad" data-cockpit-wert>–</span><span class="mj-himmel" data-cockpit-klein>Open-Meteo</span></p></div>'
  + `<div class="mj-liste">${eintraege.join('')}</div>`
  + '<div class="mj-notruf" aria-label="Notruf"><a class="mj-taste" href="tel:112"><b>112</b><span>Feuerwehr, Rettung</span></a><a class="mj-taste" href="tel:110"><b>110</b><span>Polizei</span></a></div>'
  + '</div></section><!-- cockpit:end -->';

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
console.log(`Cockpit: ${teile.length} Eintraege${termin ? `, Termin ${termin.slug}` : ''}${einsatz ? `, Einsatz ${einsatz.m[1]}` : ''}; ${geaendert ? (nurPruefen ? 'nicht aktuell' : 'geschrieben') : 'aktuell'}.`);
if (nurPruefen && geaendert) process.exit(2);
