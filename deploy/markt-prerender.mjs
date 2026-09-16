#!/usr/bin/env node
/**
 * Markt vorab rendern: market.json (Wurzelverzeichnis, gepflegt in der
 * Root-Vorschau) in die ausgelieferte Seite chatgpt-site/ schreiben.
 *
 * Warum serverseitig: chatgpt-site/ ist der Stand, den Coolify und GitHub
 * Pages ausliefern - 212 statische Seiten. Ein reiner Client-Fetch wuerde
 * die Listen erst nach dem Laden einblenden, bei blockiertem JS gar nicht,
 * und Suchmaschinen saehen leere Seiten. Deshalb steht der Markt als HTML
 * in den Seiten, und market.json liegt zusaetzlich unter /api/ zum Abruf.
 *
 * Regeln:
 * - Nur Einzelinserate mit eigener Quellen-URL. Eintraege, deren Quelle nur
 *   eine Portal-Suchseite ist, werden uebersprungen und gemeldet.
 * - Merzenich zuerst, danach der direkte Umkreis mit deutlicher Kennzeichnung.
 * - Kein Bild wird uebernommen. Jede Zeile traegt Quelle und Pruefdatum.
 * - Idempotent: die Bloecke stehen zwischen Marker-Kommentaren und werden
 *   bei jedem Lauf ersetzt.
 *
 * Aufruf: node deploy/markt-prerender.mjs [--check]
 * --check schreibt nichts und meldet nur, ob die Seiten aktuell waeren.
 */

import { readFileSync, writeFileSync, existsSync, mkdirSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { symbolbild } from './symbolbilder.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const site = join(wurzel, 'chatgpt-site');

const ORTE = ['Merzenich', 'Düren', 'Niederzier', 'Nörvenich', 'Elsdorf', 'Kerpen'];
const ORTSTEILE = ['Merzenich', 'Golzheim', 'Girbelsrath', 'Morschenich', 'Bürgewald'];

const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const sichereUrl = (u) => (/^https?:\/\//i.test(String(u || '')) ? String(u) : '');
const istSuchseite = (u) => /\/suche\b|jobsuche\/suche/.test(String(u || ''));

function datum(iso) {
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return { tag: '', zeit: '' };
  const f = (opt) => new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', ...opt }).format(d);
  return { tag: f({ day: '2-digit', month: '2-digit', year: 'numeric' }), zeit: f({ hour: '2-digit', minute: '2-digit' }) };
}

function ortZeile(item) {
  const gemeinde = String(item.municipality || '').trim();
  const teil = String(item.district || '').trim();
  if (gemeinde === 'Merzenich') {
    const zusatz = teil && teil !== 'Merzenich' && ORTSTEILE.includes(teil) ? ' · ' + teil.toUpperCase() : '';
    return `<span class="markt-ort-tag markt-ort-tag--merzenich">MERZENICH${esc(zusatz)}</span>`;
  }
  const zusatz = teil && teil.toLowerCase() !== gemeinde.toLowerCase() ? ' · ' + teil.toUpperCase() : '';
  return `<span class="markt-ort-tag">UMKREIS · ${esc(gemeinde.toUpperCase())}${esc(zusatz)}</span>`;
}

function kurzArt(item, art) {
  if (art === 'jobs') {
    const e = String(item.employment || '');
    if (/ausbildung/i.test(e) || /ausbildung/i.test(item.title || '')) return 'Ausbildung';
    if (/teilzeit/i.test(e)) return 'Teilzeit';
    if (/minijob/i.test(e)) return 'Minijob';
    if (/vollzeit/i.test(e)) return 'Vollzeit';
    return 'Stelle';
  }
  return String(item.offerType || 'Immobilie');
}

function zeile(item, art) {
  const url = sichereUrl(item.sourceUrl);
  const p = datum(item.checkedAt);
  const job = art === 'jobs';
  const haupt = job ? item.employer : item.price;
  const fakten = job ? item.employment : item.details;
  const details = job ? item.details : '';
  // Stellen bekommen das Buero-Symbolbild als Vorschau. Immobilien bewusst
  // nicht: ein echtes Haus neben einem Inserat liest sich als das Objekt (§4).
  const symbol = job ? symbolbild('stellen', 480) : null;
  const thumb = symbol ? `<a class="markt-thumb" href="${esc(url)}" target="_blank" rel="noopener noreferrer nofollow" tabindex="-1" aria-hidden="true"><img src="${esc(symbol.src)}" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer"><span class="markt-thumb__badge">Symbolbild</span></a>` : '';
  return `<article class="event-row job-row markt-row${symbol ? ' markt-row--thumb' : ''}">` +
    `<span class="d job-d"><b>${esc(kurzArt(item, art))}</b></span>` + thumb +
    `<div class="info">` +
      `<span class="eyebrow"><span class="markt-art">${esc(kurzArt(item, art))} · </span>${ortZeile(item)}</span>` +
      `<h3><a href="${esc(url)}" target="_blank" rel="noopener noreferrer nofollow">${esc(item.title)}</a></h3>` +
      (haupt ? `<p class="markt-haupt">${esc(haupt)}</p>` : '') +
      `<div class="meta">${fakten ? `<span>${esc(fakten)}</span>` : ''}${details ? `<span>${esc(details)}</span>` : ''}${p.tag ? `<span>Geprüft ${esc(p.tag)}${p.zeit ? ' · ' + esc(p.zeit) + ' Uhr' : ''}</span>` : ''}</div>` +
      `<p class="ev-desc">Quelle: ${esc(item.sourceName || 'Originalquelle')} · Angaben laut Anbieter, maßgeblich ist die Originalanzeige.${symbol ? ` Symbolbild: ${esc(symbol.credit)}, <a href="${esc(symbol.sourceUrl)}" target="_blank" rel="noopener noreferrer">Bildquelle</a>.` : ''}</p>` +
    `</div>` +
    `<div class="act"><a href="${esc(url)}" target="_blank" rel="noopener noreferrer nofollow">Originalanzeige öffnen</a></div>` +
  `</article>`;
}

function liste(daten, art) {
  const alle = daten[art === 'jobs' ? 'jobs' : 'properties'] || [];
  const quellen = daten[art === 'jobs' ? 'jobSources' : 'propertySources'] || [];
  const uebersprungen = alle.filter((i) => !sichereUrl(i.sourceUrl) || istSuchseite(i.sourceUrl));
  const items = alle.filter((i) => !uebersprungen.includes(i));
  const p = datum(daten.meta?.checkedAt);
  const teile = [];

  teile.push(`<div class="markt-stand"><strong>Markt geprüft: ${esc(p.tag)}${p.zeit ? ' · ' + esc(p.zeit) + ' Uhr' : ''}</strong>` +
    `<p>${esc(daten.meta?.methodNote || 'Maßgeblich ist die verlinkte Originalquelle.')} Bilder der Anbieter werden nicht übernommen.</p></div>`);

  for (const ort of ORTE) {
    const hier = items.filter((i) => i.municipality === ort);
    const hierQuellen = quellen.filter((q) => q.municipality === ort && sichereUrl(q.sourceUrl));
    if (!hier.length && !hierQuellen.length) continue;
    const istMerzenich = ort === 'Merzenich';
    teile.push(`<h2 class="markt-ort"><span class="markt-ort-eyebrow">${istMerzenich ? 'Gemeinde Merzenich' : 'Direkter Umkreis'}</span>${esc(ort)}` +
      `<span class="markt-ort-count">${hier.length} ${hier.length === 1 ? 'Anzeige' : 'Anzeigen'}</span></h2>`);
    teile.push(`<p class="markt-hinweis">${istMerzenich
      ? 'Einschließlich Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald.'
      : esc(ort) + ' gehört zum direkten Umkreis. Diese Angebote liegen ausdrücklich nicht in Merzenich.'}</p>`);
    teile.push(hier.length ? hier.map((i) => zeile(i, art)).join('\n') : '<p class="empty">Derzeit kein einzelnes Angebot geprüft.</p>');
    if (hierQuellen.length) {
      teile.push(`<p class="markt-quellen"><strong>Laufende Übersicht:</strong> ${hierQuellen.map((q) =>
        `<a href="${esc(sichereUrl(q.sourceUrl))}" target="_blank" rel="noopener noreferrer nofollow">${esc(q.label)}</a> <small>(${esc(q.sourceName)})</small>`).join(' · ')}</p>`);
    }
  }
  return { html: teile.join('\n'), anzahl: items.length, merzenich: items.filter((i) => i.municipality === 'Merzenich'), uebersprungen };
}

function mini(items, art) {
  if (!items.length) return '<p class="markt-mini-leer">Derzeit kein einzelnes Angebot in Merzenich geprüft.</p>';
  return items.slice(0, 3).map((i) => {
    const haupt = art === 'jobs' ? i.employer : i.price;
    return `<a class="markt-mini" href="${esc(sichereUrl(i.sourceUrl))}" target="_blank" rel="noopener noreferrer nofollow">` +
      `<strong>${esc(i.title)}</strong><span>${esc([haupt, i.district && i.district !== 'Merzenich' ? i.district : 'Merzenich'].filter(Boolean).join(' · '))}</span></a>`;
  }).join('');
}

/** Block zwischen Markern ersetzen; beim ersten Lauf hinter dem Anker einsetzen. */
function setze(html, name, block, ankerRegex, ersetzeAnker) {
  const start = `<!-- markt:${name}:start -->`, ende = `<!-- markt:${name}:end -->`;
  const neu = `${start}\n${block}\n${ende}`;
  const i = html.indexOf(start), j = html.indexOf(ende);
  if (i !== -1 && j !== -1) return html.slice(0, i) + neu + html.slice(j + ende.length);
  const m = html.match(ankerRegex);
  if (!m) throw new Error(`Anker fuer ${name} nicht gefunden`);
  const vor = html.slice(0, m.index), nach = html.slice(m.index + m[0].length);
  return ersetzeAnker ? vor + neu + nach : vor + m[0] + '\n' + neu + nach;
}

function schreibe(pfad, inhalt) {
  const alt = existsSync(pfad) ? readFileSync(pfad, 'utf8') : null;
  if (alt === inhalt) return false;
  if (!nurPruefen) { mkdirSync(dirname(pfad), { recursive: true }); writeFileSync(pfad, inhalt); }
  return true;
}

// ---------------------------------------------------------------- Lauf
const quelle = join(wurzel, 'market.json');
if (!existsSync(quelle)) { console.error('market.json fehlt im Wurzelverzeichnis.'); process.exit(1); }
const rohtext = readFileSync(quelle, 'utf8');
const daten = JSON.parse(rohtext);
const geaendert = [];

if (schreibe(join(site, 'api', 'market.json'), rohtext)) geaendert.push('api/market.json');

// /jobs/
{
  const pfad = join(site, 'jobs', 'index.html');
  let html = readFileSync(pfad, 'utf8');
  const l = liste(daten, 'jobs');
  html = setze(html, 'jobs', l.html, /<p class="empty">Noch keine Stellen eingetragen\.[^<]*<a href="\/werben\/">Stelle inserieren<\/a><\/p>/, true);
  html = html.replace(/<p class="count-line">\d+ Stellen? · /, `<p class="count-line">${l.anzahl} ${l.anzahl === 1 ? 'Stelle' : 'Stellen'} · `);
  if (schreibe(pfad, html)) geaendert.push('jobs/index.html');
  daten._jobs = l;
}

// /immobilien/
{
  const pfad = join(site, 'immobilien', 'index.html');
  let html = readFileSync(pfad, 'utf8');
  const l = liste(daten, 'properties');
  html = setze(html, 'immobilien', l.html, /<p>Zurzeit liegen keine veröffentlichten Immobilienangebote vor\.<\/p>/, true);
  // Der info-prose-Wrapper setzt Serifen fuer Fliesstext; die Liste soll wie
  // die Stellenliste aussehen, deshalb ein neutraler Wrapper mit gleicher Tiefe.
  html = html.replace('<div class="narrow"><div class="info-prose">', '<div class="event-list markt-liste"><h2 class="sr-only">Immobilienangebote</h2><div class="markt-liste-body">');
  if (schreibe(pfad, html)) geaendert.push('immobilien/index.html');
  daten._props = l;
}

// Startseite: Service-Kaesten
{
  const pfad = join(site, 'index.html');
  let html = readFileSync(pfad, 'utf8');
  html = setze(html, 'home-jobs', mini(daten._jobs.merzenich, 'jobs'), /<summary>Stellenmarkt<\/summary><div>/, false);
  html = setze(html, 'home-immobilien', mini(daten._props.merzenich, 'properties'), /<summary>Immobilienmarkt<\/summary><div>/, false);
  if (schreibe(pfad, html)) geaendert.push('index.html');
}

const uebersprungen = [...daten._jobs.uebersprungen, ...daten._props.uebersprungen];
console.log(`Markt: ${daten._jobs.anzahl} Stellen, ${daten._props.anzahl} Immobilien uebernommen (Merzenich: ${daten._jobs.merzenich.length} / ${daten._props.merzenich.length}).`);
for (const u of uebersprungen) console.log(`  uebersprungen: ${u.id} - Quelle ist eine Portal-Suchseite, kein Einzelinserat.`);
if (nurPruefen) {
  if (geaendert.length) { console.log('Nicht aktuell: ' + geaendert.join(', ')); process.exit(2); }
  console.log('Seiten sind aktuell.');
} else {
  console.log(geaendert.length ? 'Geschrieben: ' + geaendert.join(', ') : 'Nichts zu aendern.');
}
