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
  + (a.sperrmuell ? `<p>${esc(a.sperrmuell.text)} <a href="${esc(a.sperrmuell.url)}" target="_blank" rel="noopener">Sperrmüll anmelden</a>. ${quelle(a.sperrmuell.quelle)}</p>` : '')
  + (a.annahmestelle ? `<p>${esc(a.annahmestelle.text)} ${esc(a.annahmestelle.name)}, ${esc(a.annahmestelle.adresse)}: <a href="${esc(a.annahmestelle.url)}" target="_blank" rel="noopener">Öffnungszeiten beim Betreiber</a>. ${quelle(a.annahmestelle.quelle)}</p>` : '')
  + '<!-- gemeinde:abfall:end -->';
// Weitere Notdienste als Tabellenzeilen unter den Notrufen (29.09.2026).
const notdienste = '<!-- gemeinde:notdienste:start -->'
  + (GEMEINDE.notdienste || []).map((n) => `<tr><td>${esc(n.name)}<br><small>${esc(n.hinweis)} · <a href="${esc(n.url)}" target="_blank" rel="noopener">${esc(n.quelle.name)}</a></small></td><td><a href="tel:${esc(n.telefonLink)}">${esc(n.telefon)}</a></td></tr>`).join('')
  + '<!-- gemeinde:notdienste:end -->';
const k = GEMEINDE.kreis;
const kreis = '<!-- gemeinde:kreis:start -->'
  + (k ? '<h2 id="kreis-dueren">Kreisverwaltung und Straßenverkehrsamt</h2>'
    + `<p>Kreishaus Düren, ${esc(k.adresse)} · Telefon <a href="tel:${esc(k.telefonLink)}">${esc(k.telefon)}</a> · <a href="${esc(k.web)}" target="_blank" rel="noopener">kreis-dueren.de</a></p>`
    + `<p>Kfz-Zulassung und Führerscheinstelle gehören zum Straßenverkehrsamt, ${esc(k.strassenverkehrsamt.adresse)}: <a href="${esc(k.strassenverkehrsamt.zulassung)}" target="_blank" rel="noopener">Zulassung</a>, <a href="${esc(k.strassenverkehrsamt.termin)}" target="_blank" rel="noopener">Termin buchen</a>, <a href="${esc(k.strassenverkehrsamt.fuehrerschein)}" target="_blank" rel="noopener">Führerscheinstelle</a>.</p>`
    + `<p>${quelle(k.quelle)}</p>` : '')
  + '<!-- gemeinde:kreis:end -->';

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
// Einmalige Umstellung: Notdienst-Zeilen hinter dem Giftnotruf, Kreis-Block vor Bahn und Bus.
block('notdienste', notdienste, /(Giftnotruf NRW \(Bonn\)<\/td><td>[\s\S]*?<\/td><\/tr>)()/);
block('kreis', kreis, /(<!-- gemeinde:abfall:end -->\n)()(?=<h2 id="bahn-und-bus">)/);
if (html !== alt && !nurPruefen) writeFileSync(pfad, html);
console.log(`Gemeinde: /service/ ${html !== alt ? (nurPruefen ? 'nicht aktuell' : 'geschrieben') : 'aktuell'}.`);
if (nurPruefen && html !== alt) process.exit(2);
