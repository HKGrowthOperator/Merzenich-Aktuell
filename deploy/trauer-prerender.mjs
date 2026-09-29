#!/usr/bin/env node
/**
 * Traueranzeigen-Seite (Entscheidung 29.09.2026): nur Hinweis und Links zu
 * den Traueranzeigen-Portalen der Region, keine Einzelanzeigen.
 *
 * Vorher (28.09.) wurden Einzelanzeigen mit Porträtfoto direkt von
 * aachen-gedenkt.de und wirtrauern.de eingebunden, ohne Einwilligung der
 * Familien; eine davon betraf Zülpich-Merzenich (Kreis Euskirchen), nicht die
 * Gemeinde Merzenich. Traueranzeigen gehören den Familien, die sie geschaltet
 * haben: Merzenich Aktuell verweist auf die Portale und nimmt eigene Anzeigen
 * nur über /anzeigen/aufgeben/ an.
 *
 * Kein Netzabruf. Liest traueranzeigen.json (Portale) und schreibt zwischen
 * <!-- trauer:liste:start/end --> auf /traueranzeigen/.
 * Aufruf: node deploy/trauer-prerender.mjs [--check]
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { resolve, dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { esc } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const pfad = join(wurzel, 'chatgpt-site', 'traueranzeigen', 'index.html');
const daten = JSON.parse(readFileSync(join(wurzel, 'traueranzeigen.json'), 'utf8'));
if ((daten.notices || []).length) throw new Error('traueranzeigen.json: Einzelanzeigen werden nicht mehr veröffentlicht (Entscheidung 29.09.2026). notices muss leer sein.');
const portale = (daten.portale || []).filter((p) => /^https:\/\//.test(p.url || ''));
if (!portale.length) throw new Error('traueranzeigen.json: keine Portale mit https-Link.');

const inhalt = '<div class="info-prose">'
  + '<p>Merzenich Aktuell veröffentlicht keine Traueranzeigen aus anderen Portalen. Traueranzeigen gehören den Familien, die sie aufgegeben haben. Aktuelle Anzeigen aus Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald finden Sie bei den Portalen der Zeitungen:</p>'
  + `<ul>${portale.map((p) => `<li><a href="${esc(p.url)}" target="_blank" rel="noopener noreferrer nofollow">${esc(p.name)}</a>${p.hinweis ? ` <span class="muted">· ${esc(p.hinweis)}</span>` : ''}</li>`).join('')}</ul>`
  + '<p>Sie möchten eine Traueranzeige oder einen Nachruf auf Merzenich Aktuell veröffentlichen? <a href="/anzeigen/aufgeben/">Traueranzeige aufgeben</a>. Wir veröffentlichen sie nur mit Ihrer Zustimmung und so, wie Sie sie freigeben.</p>'
  + '</div>';

const alt = readFileSync(pfad, 'utf8');
const marke = /<!-- trauer:liste:start -->[\s\S]*?<!-- trauer:liste:end -->/;
if (!marke.test(alt)) throw new Error('Trauer-Marker fehlen auf /traueranzeigen/.');
const neu = alt.replace(marke, () => `<!-- trauer:liste:start -->${inhalt}<!-- trauer:liste:end -->`);
if (neu !== alt && !nurPruefen) writeFileSync(pfad, neu);
console.log(`Traueranzeigen: ${portale.length} Portale, ${neu !== alt ? (nurPruefen ? 'nicht aktuell' : 'geschrieben') : 'aktuell'}.`);
if (nurPruefen && neu !== alt) process.exit(2);
