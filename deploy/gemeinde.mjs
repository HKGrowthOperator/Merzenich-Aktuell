#!/usr/bin/env node
/**
 * Gemeindedaten auf /service/ (Audit 28.09.2026): Rathaus und Abfall kommen aus
 * deploy/gemeinde.json, dieselbe Quelle wie die Rathaus-Kachel im Cockpit
 * (deploy/cockpit.mjs). Geschrieben wird zwischen
 *   <!-- gemeinde:rathaus:start/end --> und <!-- gemeinde:abfall:start/end -->.
 * Aufruf: node deploy/gemeinde.mjs [--check]
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { esc } from './lib-artikel.mjs';
import { GEMEINDE, zeitenText, standText } from './lib-gemeinde.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const pfad = join(wurzel, 'chatgpt-site', 'service', 'index.html');
const nurPruefen = process.argv.includes('--check');
const r = GEMEINDE.rathaus, a = GEMEINDE.abfall;
const quelle = (q) => `<small class="quelle">Quelle: <a href="${esc(q.url)}" target="_blank" rel="noopener">${esc(q.name)}</a>, Stand ${standText(q.stand)}.</small>`;

const rathaus = '<!-- gemeinde:rathaus:start -->'
  + `<p>${esc(r.adresse)} · Telefon <a href="tel:${esc(r.telefonLink)}">${esc(r.telefon)}</a> · <a href="${esc(r.web)}" target="_blank" rel="noopener">gemeinde-merzenich.de</a></p>`
  + `<p>Öffnungszeiten laut Gemeinde: ${esc(zeitenText())}. ${esc(r.hinweis)} <a href="${esc(r.termineOnline)}" target="_blank" rel="noopener">Termin online buchen</a>.</p>`
  + `<p>${quelle(r.quelle)}</p>`
  + '<!-- gemeinde:rathaus:end -->';
const abfall = '<!-- gemeinde:abfall:start -->'
  + `<p>${esc(a.text)} Telefon <a href="tel:${esc(a.telefonLink)}">${esc(a.telefon)}</a>. Abfuhrtermine stehen im <a href="${esc(a.kalender)}" target="_blank" rel="noopener">Abfuhrkalender der Gemeinde</a>. ${esc(a.beratung)}</p>`
  + `<p>${quelle(a.quelle)}</p>`
  + '<!-- gemeinde:abfall:end -->';

const alt = readFileSync(pfad, 'utf8');
let html = alt;
const block = (name, inhalt, altRe) => {
  const marke = new RegExp(`<!-- gemeinde:${name}:start -->[\\s\\S]*?<!-- gemeinde:${name}:end -->`);
  if (marke.test(html)) html = html.replace(marke, () => inhalt);
  else if (altRe.test(html)) html = html.replace(altRe, (m, kopf) => kopf + inhalt + '\n');
  else { console.error(`Gemeinde: Block ${name} auf /service/ nicht gefunden.`); process.exit(2); }
};
// Einmalige Umstellung: bisher standen die Absaetze fest im HTML. Der Satz zum
// Buergermeister bleibt als eigener Absatz nach dem Block stehen.
block('rathaus', rathaus, /(<h2 id="rathaus-merzenich">Rathaus Merzenich<\/h2>\n)<p>Valdersweg[\s\S]*?<\/p>\n<p>Öffnungszeiten laut Gemeinde:[\s\S]*?(?=Bürgermeister ist)/);
html = html.replace(/<!-- gemeinde:rathaus:end -->\n?(Bürgermeister ist [^<]*)<\/p>/, '<!-- gemeinde:rathaus:end -->\n<p>$1</p>');
block('abfall', abfall, /(<h2 id="abfall">Abfall<\/h2>\n)<p>[\s\S]*?<\/p>\n/);
if (html !== alt && !nurPruefen) writeFileSync(pfad, html);
console.log(`Gemeinde: /service/ ${html !== alt ? (nurPruefen ? 'nicht aktuell' : 'geschrieben') : 'aktuell'}.`);
if (nurPruefen && html !== alt) process.exit(2);
