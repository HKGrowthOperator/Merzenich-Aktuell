#!/usr/bin/env node
/**
 * Startseite, Block „Direkter Umkreis“ (29.09.2026).
 *
 * Vorher stand der Block von Hand im generierten chatgpt-site/index.html:
 * 18 Einträge, davon viele nur mit Link auf eine Übersichtsseite und mit
 * „Aktuell“ statt Datum. Nichts zog ihn nach, er veraltete.
 *
 * Jetzt: Einträge in inhalte/umkreis/*.json, je Eintrag Ort, Rubrik, Titel,
 * Link auf die Einzelmeldung der Originalquelle, Veröffentlichungsdatum und
 * Quelle. Gezeigt werden Einträge der letzten TAGE Tage (Ortszeit Berlin),
 * je Ort höchstens JE_ORT, insgesamt höchstens MAX, nach Ort gruppiert.
 * Sind es weniger als MIN, bleibt der Block leer (kein halbleerer Kasten).
 *
 * Schreibt zwischen <!-- umkreis:start --> und <!-- umkreis:end -->.
 * Aufruf: node deploy/umkreis.mjs [--check]
 */
import { readFileSync, writeFileSync, readdirSync, existsSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { esc } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const pfad = join(wurzel, 'chatgpt-site', 'index.html');
const ordner = join(wurzel, 'inhalte', 'umkreis');
const ORTE = ['Düren', 'Niederzier', 'Nörvenich', 'Elsdorf', 'Kerpen', 'Kreis Düren'];
const TAGE = 10, JE_ORT = 4, MAX = 18, MIN = 3;
// Übersichts- und Suchseiten sind keine Quelle für eine einzelne Meldung.
const UEBERSICHT = /(\/index\.php|\/presse|\/news|\/meldungen\.php|\/amtsblatt\.php|\/blaulicht\/nr\/\d+)\/?$|suche-none|sword_list/i;

const eintraege = [];
for (const datei of existsSync(ordner) ? readdirSync(ordner).filter((d) => d.endsWith('.json')).sort() : []) {
  const j = JSON.parse(readFileSync(join(ordner, datei), 'utf8'));
  for (const e of j.eintraege || []) {
    const wo = `${datei} / ${e.titel}`;
    if (!ORTE.includes(e.ort)) throw new Error(`Umkreis: ${wo}: Ort "${e.ort}" nicht in ${ORTE.join(', ')}`);
    if (!/^\d{4}-\d{2}-\d{2}$/.test(e.datum || '')) throw new Error(`Umkreis: ${wo}: Datum fehlt (YYYY-MM-DD)`);
    if (!/^https:\/\//.test(e.url || '')) throw new Error(`Umkreis: ${wo}: https-Link fehlt`);
    if (UEBERSICHT.test(e.url)) throw new Error(`Umkreis: ${wo}: Link zeigt auf eine Übersichtsseite, nicht auf die Meldung`);
    for (const f of ['rubrik', 'titel', 'quelle']) if (!e[f]) throw new Error(`Umkreis: ${wo}: ${f} fehlt`);
    eintraege.push(e);
  }
}

// Heute in Berlin, als YYYY-MM-DD; Grenze = heute minus TAGE.
const heute = new Intl.DateTimeFormat('sv-SE', { timeZone: 'Europe/Berlin' }).format(new Date());
const grenze = new Date(Date.parse(heute + 'T00:00:00Z') - TAGE * 864e5).toISOString().slice(0, 10);
const frisch = eintraege.filter((e) => e.datum >= grenze && e.datum <= heute)
  .sort((a, b) => b.datum.localeCompare(a.datum) || a.titel.localeCompare(b.titel));
const gewaehlt = [];
for (const ort of ORTE) gewaehlt.push(...frisch.filter((e) => e.ort === ort).slice(0, JE_ORT));
const liste = gewaehlt.slice(0, MAX);
const orteMit = ORTE.filter((o) => liste.some((e) => e.ort === o));
const kurz = (d) => `${d.slice(8, 10)}.${d.slice(5, 7)}.`;

const block = liste.length < MIN ? '' : '<section class="desk shell" data-sektion="umkreis" aria-labelledby="umkreis-titel"><div class="desk-heading"><div><span class="eyebrow">Direkter Umkreis</span><h2 id="umkreis-titel">Was rund um Merzenich passiert</h2>'
  + `<p class="dek">Meldungen der letzten ${TAGE} Tage aus ${orteMit.length > 1 ? orteMit.slice(0, -1).join(', ') + ' und ' + orteMit.at(-1) : orteMit[0]}. Jeder Beitrag führt direkt zur Originalquelle.</p></div></div><div class="desk-zeilen">`
  + liste.map((e, i) => `<article class="front-zeile" data-story="umkreis-${i + 1}"><div class="karte-text"><p class="marke"><span class="marke-ort">${esc(e.ort)}</span><span class="marke-rubrik">${esc(e.rubrik)}</span></p>`
    + `<h3><a href="${esc(e.url)}" target="_blank" rel="noopener noreferrer">${esc(e.titel)}</a></h3>`
    + `<div class="meta"><time datetime="${esc(e.datum)}">${kurz(e.datum)}</time><span>${esc(e.quelle)} · externe Quelle</span></div></div></article>`).join('')
  + '</div></section>';

const alt = readFileSync(pfad, 'utf8');
const marke = /<!-- umkreis:start -->[\s\S]*?<!-- umkreis:end -->/;
if (!marke.test(alt)) { console.error('Umkreis: Marker umkreis fehlt in index.html.'); process.exit(2); }
const neu = alt.replace(marke, () => `<!-- umkreis:start -->${block}<!-- umkreis:end -->`);
if (neu !== alt && !nurPruefen) writeFileSync(pfad, neu);
console.log(`Umkreis: ${eintraege.length} Einträge, ${liste.length} gezeigt (seit ${grenze}); ${neu !== alt ? (nurPruefen ? 'nicht aktuell' : 'geschrieben') : 'aktuell'}.`);
if (nurPruefen && neu !== alt) process.exit(2);
