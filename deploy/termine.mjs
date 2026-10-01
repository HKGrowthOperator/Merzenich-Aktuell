#!/usr/bin/env node
/**
 * Termine aus einer Quelle (Audit 28.09.2026: "6 Termine" stand fest im HTML,
 * sichtbar waren drei; Kalender, Feed und "Weitere Termine" wurden von Hand
 * gepflegt und liefen auseinander).
 *
 * 1. Neue Termine: inhalte/termine/*.json -> chatgpt-site/termine/<slug>/.
 *    Seiten mit data-termin="<hash>" werden bei geaenderter Quelle neu
 *    geschrieben; Seiten ohne diese Marke (von Hand) bleiben unberuehrt.
 * 2. Aus allen Terminseiten (lib-termine.mjs) werden erzeugt:
 *    - /termine/: kommende Liste, vergangene Termine, Zaehler, Kategorien
 *    - "Weitere Termine" auf jeder Terminseite (nur kommende)
 *    - termin.ics je Termin, /termine/kalender.ics, /termine/feed.xml
 *    - sitemap-termine.xml (lastmod = Stand der Termindaten)
 * Kommend/vergangen haengt an der Uhrzeit (Europe/Berlin); der taegliche
 * CI-Lauf schreibt den Stand zurueck (wie termine-prerender.mjs).
 * Aufruf: node deploy/termine.mjs [--check]
 */
import { readFileSync, writeFileSync, existsSync, readdirSync, mkdirSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { esc, SITE_URL, ortSlug, liveUrl } from './lib-artikel.mjs';
import { termineAusSeiten, berliner } from './lib-termine.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');
const VORLAGE = join(site, 'termine', 'seniorennachmittag-2026', 'index.html');
const jetzt = Date.now();
const fehler = [];
const geaendert = [];
const schreibe = (rel, inhalt) => {
  const pfad = join(site, rel);
  if (existsSync(pfad) && readFileSync(pfad, 'utf8') === inhalt) return;
  geaendert.push(rel);
  if (!nurPruefen) { mkdirSync(dirname(pfad), { recursive: true }); writeFileSync(pfad, inhalt); }
};

const TAGE = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
const MON = ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
const wochentag = (d) => TAGE[new Date(`${berliner(d).jahr}-${berliner(d).monat}-${berliner(d).tag}T12:00:00Z`).getUTCDay()];
const dmy = (d) => { const b = berliner(d); return `${b.tag}.${b.monat}.${b.jahr}`; };
const hm = (d) => { const b = berliner(d); return `${b.stunde}:${b.minute}`; };
const ganztags = (t) => hm(t.start) === '00:00' && t.ohneEnde;
const zeitText = (t) => (ganztags(t) ? 'ganztägig' : (t.ohneEnde ? `${hm(t.start)} Uhr` : `${hm(t.start)} Uhr bis ${hm(t.ende)} Uhr`));
const absolut = (u) => (/^https?:/.test(u) ? u : SITE_URL + u);

// ------------------------------------------------ 1. Terminseiten aus Daten
const vorlage = readFileSync(VORLAGE, 'utf8');
const ICON_KAL = '<span class="ef-ic"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span>';
const ICON_ORT = '<span class="ef-ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s7-7.1 7-12a7 7 0 10-14 0c0 4.9 7 12 7 12z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="2.5" fill="none" stroke="currentColor" stroke-width="2"/></svg></span>';
const ICON_VER = '<span class="ef-ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a6 6 0 00-6 6v4l-2 4h16l-2-4V8a6 6 0 00-6-6zm-2 17a2 2 0 004 0h-4z"/></svg></span>';
// Aendert sich die Seitenvorlage, hier hochzaehlen: dann werden alle erzeugten Seiten neu geschrieben.
const GENERATOR_STAND = 'v2';
const PFLICHT = ['slug', 'titel', 'start', 'ort', 'ortsteil', 'kategorie', 'veranstalter', 'beschreibung', 'absaetze', 'quelle'];

function seiteAusDaten(t) {
  const start = new Date(t.start);
  const ende = t.ende ? new Date(t.ende) : null;
  const url = `${SITE_URL}/termine/${t.slug}/`;
  const datumLang = new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(start);
  const desc = `${t.beschreibung} ${t.titel} am ${datumLang} um ${hm(start)} Uhr.`.slice(0, 300);
  const titel = `${t.titel} – ${dmy(start)}`;
  const ld = { '@context': 'https://schema.org', '@type': 'Event', '@id': `${url}#event`, name: t.titel, description: t.beschreibung, url, startDate: t.start, ...(t.ende ? { endDate: t.ende } : {}), eventStatus: 'https://schema.org/EventScheduled', eventAttendanceMode: 'https://schema.org/OfflineEventAttendanceMode', location: { '@type': 'Place', name: t.ort, address: { '@type': 'PostalAddress', streetAddress: (t.adresse || '').split(',')[0], addressLocality: t.ortsteil, postalCode: '52399', addressRegion: 'NRW', addressCountry: 'DE' } }, organizer: { '@type': 'Organization', name: t.veranstalter } };
  const hash = createHash('sha256').update(GENERATOR_STAND + JSON.stringify(t)).digest('hex').slice(0, 16);
  const stand = t.quelle.stand ? t.quelle.stand.split('-').reverse().join('.') : '';
  const links = (t.links || []).map(([text, href]) => `<a class="btn ghost" href="${esc(href)}" target="_blank" rel="noopener">${esc(text)} ↗</a>`).join('');
  const artikel = `<article class="article event-page" data-termin="${hash}">
<div class="article-head"><div class="shell">
  <nav class="crumbs" aria-label="Brotkrumen"><a href="/">Start</a><span class="sep">›</span><a href="/termine/">Termine</a><span class="sep">›</span><span aria-current="page">${esc(t.titel)}</span></nav>
  <div class="kick-row"><span class="kicker">${esc(t.kategorie)}<span class="dist">${esc(t.ortsteil)}</span></span></div>
  <h1>${esc(t.titel)}</h1>
  <p class="dek">${esc(t.beschreibung)}</p>
</div></div>
<div class="shell article-grid">
  <div class="article-body">
    <div class="event-facts">
      <div class="ef">${ICON_KAL}<div><b>${wochentag(start)}, ${dmy(start)}</b><span>${ende ? `${hm(start)} Uhr bis ${hm(ende)} Uhr` : `${hm(start)} Uhr`}</span></div></div>
      <div class="ef">${ICON_ORT}<div><b>${esc(t.ort)}</b><span>${esc(t.adresse || t.ortsteil)}</span></div></div>
      <div class="ef">${ICON_VER}<div><b>${esc(t.veranstalter)}</b><span>Veranstalter</span></div></div>
    </div>
    <div class="cta-row"><a class="btn" href="/termine/${esc(t.slug)}/termin.ics" download>In den Kalender</a><a class="btn ghost" href="https://www.openstreetmap.org/search?query=${encodeURIComponent(t.adresse || t.ort)}" target="_blank" rel="noopener">Karte ↗</a><a class="btn ghost" href="${esc(t.quelle.url)}" target="_blank" rel="noopener nofollow">Quelle ↗</a>${links}</div>
    <div class="prose">${t.absaetze.map((p) => `<p>${esc(p)}</p>`).join('')}${t.artikel ? `<p><a href="${esc(t.artikel)}">Zur Meldung auf Merzenich Aktuell</a></p>` : ''}</div>
    <!-- werbung:artikel:start --><!-- werbung:artikel:end --><div class="source-box"><b>Termindaten.</b> Quelle: <a href="${esc(t.quelle.url)}" target="_blank" rel="noopener nofollow">${esc(t.quelle.name)} ↗</a>.${stand ? ` Stand ${stand}.` : ''} Änderungen bitte an <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a> oder über <a href="/termine/melden/">Termin melden</a>.</div>
  </div>
  <aside class="sidebar"><h2 class="sr-only">Weitere Inhalte</h2>
    <!-- termine:weitere:start --><!-- termine:weitere:end -->
  </aside>
</div>
</article>`;
  let html = vorlage
    .replace(/<title>[^<]*<\/title>/, `<title>${esc(titel)} | Merzenich Aktuell</title>`)
    .replace(/(<meta name="description" content=")[^"]*"/, `$1${esc(desc)}"`)
    .replace(/(<meta property="og:description" content=")[^"]*"/, `$1${esc(desc)}"`)
    .replace(/(<meta name="twitter:description" content=")[^"]*"/, `$1${esc(desc)}"`)
    .replace(/(<meta property="og:title" content=")[^"]*"/, `$1${esc(titel)}"`)
    .replace(/(<meta name="twitter:title" content=")[^"]*"/, `$1${esc(titel)}"`)
    .replace(/(<link rel="canonical" href=")[^"]*"/, `$1${liveUrl(`/termine/${t.slug}/`)}"`)
    .replace(/(<meta property="og:url" content=")[^"]*"/, `$1${liveUrl(`/termine/${t.slug}/`)}"`)
    .replace(/<script type="application\/ld\+json">\{"@context":"https:\/\/schema\.org","@type":"Event"[\s\S]*?<\/script>/, () => `<script type="application/ld+json">${JSON.stringify(ld)}</script>`)
    .replace(/<article class="article event-page"[\s\S]*?<\/article>/, () => artikel);
  // Breadcrumb-JSON-LD der Vorlage nennt den Vorlagentermin.
  html = html.replace(/("@type":"ListItem","position":3,"name":")[^"]*"/, (m, a) => `${a}${t.titel.replace(/"/g, '\\"')}"`);
  const rest = html.replace(/<!-- termine:weitere:start -->[\s\S]*?<!-- termine:weitere:end -->/, '').match(/.{0,80}Seniorennachmittag der Gemeinde.{0,40}/);
  if (rest) fehler.push(`${t.slug}: Vorlagentext nicht vollstaendig ersetzt: ${rest[0]}`);
  return { html, hash };
}

for (const datei of existsSync(join(wurzel, 'inhalte', 'termine')) ? readdirSync(join(wurzel, 'inhalte', 'termine')).filter((f) => f.endsWith('.json')).sort() : []) {
  const daten = JSON.parse(readFileSync(join(wurzel, 'inhalte', 'termine', datei), 'utf8'));
  for (const t of daten.termine || []) {
    const fehlt = PFLICHT.filter((f) => !t[f] || (Array.isArray(t[f]) && !t[f].length));
    if (fehlt.length) { fehler.push(`${datei}/${t.slug || '?'}: ${fehlt.join(', ')} fehlt`); continue; }
    if (Number.isNaN(Date.parse(t.start)) || (t.ende && Number.isNaN(Date.parse(t.ende)))) { fehler.push(`${t.slug}: Datum nicht lesbar`); continue; }
    const rel = `termine/${t.slug}/index.html`;
    const alt = existsSync(join(site, rel)) ? readFileSync(join(site, rel), 'utf8') : '';
    if (alt && !/data-termin="/.test(alt)) { console.log(`Termine: ${t.slug} ist von Hand geschrieben und bleibt.`); continue; }
    const { html, hash } = seiteAusDaten(t);
    if (alt.includes(`data-termin="${hash}"`)) continue;
    schreibe(rel, html);
  }
}

// ---------------------------------------- 2. alles aus den Terminseiten
// Nach Schritt 1 lesen: im Pruefmodus fehlen neue Seiten noch auf der Platte.
const termine = termineAusSeiten(site).sort((a, b) => a.start - b.start || a.titel.localeCompare(b.titel));
const kommend = termine.filter((t) => t.ende.getTime() >= jetzt);
const vergangen = termine.filter((t) => t.ende.getTime() < jetzt).reverse();

// /termine/
{
  const rel = 'termine/index.html';
  let html = readFileSync(join(site, rel), 'utf8');
  const alt = html;
  const zeile = (t) => {
    const b = berliner(t.start);
    return `<div data-event-row data-start="${t.start.toISOString()}" data-end="${t.ende.toISOString()}" data-place="${esc(ortSlug(t.ortsteil) || 'merzenich')}" data-category="${esc(t.kategorie)}"><article class="event-row" id="${esc(t.slug)}">
  <span class="d"><b>${Number(b.tag)}</b><span>${MON[Number(b.monat) - 1]}</span></span>
  <div class="info">
    <span class="eyebrow">${wochentag(t.start)} · ${esc(t.kategorie)} · ${esc(t.ortsteil || 'Gemeinde')}</span>
    <h2><a href="/termine/${esc(t.slug)}/">${esc(t.titel)}</a></h2>
    <div class="meta"><time datetime="${t.start.toISOString()}">${zeitText(t)}</time><span>${esc(t.ort)}</span>${t.veranstalter ? `<span>${esc(t.veranstalter)}</span>` : ''}</div>
    ${t.beschreibung ? `<p class="ev-desc">${esc(t.beschreibung)}</p>` : ''}
  </div>
  <div class="act">
    <a href="/termine/${esc(t.slug)}/">Details</a>
    <a href="/termine/${esc(t.slug)}/termin.ics" download>Kalender</a>
    ${t.quelle ? `<a href="${esc(t.quelle)}" target="_blank" rel="noopener nofollow">Quelle ↗</a>` : ''}
  </div>
</article></div>`;
  };
  const liste = `<div class="event-list">${kommend.map(zeile).join('')}<p class="no-result" data-event-empty${kommend.length ? ' hidden' : ''}>Keine Termine für diese Auswahl. Wählen Sie einen anderen Zeitraum oder Ort.</p></div>`;
  const past = `<details class="past"><summary>Vergangene Termine</summary><ul class="archive-list">${vergangen.map((t) => `<li><time datetime="${t.start.toISOString()}">${dmy(t.start)}</time><a href="/termine/${esc(t.slug)}/">${esc(t.titel)}</a></li>`).join('')}</ul></details>`;
  const re = /<div class="event-list">[\s\S]*?<\/details>/;
  if (!re.test(html)) fehler.push('termine/: Terminliste nicht gefunden');
  else html = html.replace(re, () => liste + past);
  // Zaehler: ohne JavaScript steht die Zahl der kommenden Termine; v20.js
  // zaehlt nach dem Filtern neu.
  html = html.replace(/(<p class="count-line" data-event-count aria-live="polite">)[^<]*(<\/p>)/, (m, a, b) => `${a}${kommend.length} ${kommend.length === 1 ? 'Termin' : 'Termine'}${b}`);
  const kategorien = [...new Set(kommend.map((t) => t.kategorie).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'de'));
  html = html.replace(/(<select data-event-category><option value="">Alle Kategorien<\/option>)[\s\S]*?(<\/select>)/, (m, a, b) => `${a}${kategorien.map((k) => `<option>${esc(k)}</option>`).join('')}${b}`);
  if (html !== alt) schreibe(rel, html);
}

// "Weitere Termine" auf jeder Terminseite: nur kommende, ohne sich selbst.
for (const t of termine) {
  const rel = `termine/${t.slug}/index.html`;
  const alt = readFileSync(join(site, rel), 'utf8');
  const weitere = kommend.filter((x) => x.slug !== t.slug).slice(0, 5);
  const box = weitere.length
    ? `<div class="sidebox"><h3>Weitere Termine<a href="/termine/">alle</a></h3><ul>${weitere.map((x) => { const b = berliner(x.start); return `<li class="termin"><span class="d"><b>${Number(b.tag)}</b><span>${MON[Number(b.monat) - 1]}</span></span><span class="t"><a href="/termine/${esc(x.slug)}/">${esc(x.titel)}</a><small>${zeitText(x)} · ${esc(x.ort)}</small></span></li>`; }).join('')}</ul></div>`
    : '<div class="sidebox"><h3>Weitere Termine<a href="/termine/">alle</a></h3><p>Derzeit keine weiteren Termine. <a href="/termine/melden/">Termin melden</a></p></div>';
  let html = alt;
  const MARKE = /<!-- termine:weitere:start -->[\s\S]*?<!-- termine:weitere:end -->/;
  if (MARKE.test(html)) html = html.replace(MARKE, () => `<!-- termine:weitere:start -->${box}<!-- termine:weitere:end -->`);
  else html = html.replace(/<div class="sidebox"><h3>Weitere Termine<a href="\/termine\/">alle<\/a><\/h3>[\s\S]*?<\/(?:ul|p)><\/div>/, () => `<!-- termine:weitere:start -->${box}<!-- termine:weitere:end -->`);
  if (html !== alt) schreibe(rel, html);
}

// Kalender
const icsText = (s) => String(s || '').replace(/\\/g, '\\\\').replace(/;/g, '\\;').replace(/,/g, '\\,').replace(/\r?\n/g, '\\n');
const icsZeit = (d) => d.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '');
const falten = (zeile) => { const teile = []; let rest = zeile; while (Buffer.byteLength(rest) > 75) { let n = 75; while (Buffer.byteLength(rest.slice(0, n)) > 75) n--; teile.push(rest.slice(0, n)); rest = ' ' + rest.slice(n); } teile.push(rest); return teile.join('\r\n'); };
const vevent = (t) => {
  const url = `${SITE_URL}/termine/${t.slug}/`;
  const stamp = t.stand ? `${t.stand.replace(/-/g, '')}T060000Z` : icsZeit(t.start);
  const tagDatum = (d) => { const b = berliner(d); return `${b.jahr}${b.monat}${b.tag}`; };
  const zeiten = ganztags(t)
    ? [`DTSTART;VALUE=DATE:${tagDatum(t.start)}`]
    : [`DTSTART:${icsZeit(t.start)}`, ...(t.ohneEnde ? [] : [`DTEND:${icsZeit(t.ende)}`])];
  return ['BEGIN:VEVENT', `UID:${t.slug}@merzenich-aktuell.de`, `DTSTAMP:${stamp}`, ...zeiten,
    `SUMMARY:${icsText(t.titel)}`, `DESCRIPTION:${icsText([t.beschreibung, t.veranstalter ? `Veranstalter: ${t.veranstalter}` : '', url].filter(Boolean).join('\n'))}`,
    `LOCATION:${icsText(t.ort)}`, `URL:${url}`, 'STATUS:CONFIRMED', 'END:VEVENT'].map(falten).join('\r\n');
};
const kalender = (name, liste) => [...['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Merzenich Aktuell//Termine//DE', 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH', `X-WR-CALNAME:${icsText(name)}`, 'X-WR-TIMEZONE:Europe/Berlin', 'REFRESH-INTERVAL;VALUE=DURATION:PT12H'].map(falten), ...liste.map(vevent), 'END:VCALENDAR'].join('\r\n') + '\r\n';
for (const t of termine) schreibe(`termine/${t.slug}/termin.ics`, kalender(t.titel, [t]));
// Abo-Kalender: kommende Termine und die der letzten 30 Tage.
schreibe('termine/kalender.ics', kalender('Merzenich Aktuell Termine', termine.filter((t) => t.ende.getTime() >= jetzt - 30 * 864e5)));

// Feed: kommende Termine, naechster zuerst.
{
  const x = (s) => esc(s);
  const pub = (t) => new Date(t.stand ? `${t.stand}T08:00:00+02:00` : t.start).toUTCString();
  const items = kommend.map((t) => `<item><title>${x(`${dmy(t.start)} ${ganztags(t) ? 'ganztägig' : `${hm(t.start)} Uhr`}: ${t.titel}`)}</title><link>${SITE_URL}/termine/${x(t.slug)}/</link><guid isPermaLink="true">${SITE_URL}/termine/${x(t.slug)}/</guid><pubDate>${pub(t)}</pubDate><description>${x([t.beschreibung, t.ort].filter(Boolean).join(' '))}</description></item>`).join('\n');
  schreibe('termine/feed.xml', `<?xml version="1.0" encoding="UTF-8"?>\n<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">\n<channel>\n<title>Merzenich Aktuell – Termine</title>\n<link>${SITE_URL}/termine/</link>\n<description>Kommende Termine in der Gemeinde Merzenich</description>\n<language>de-DE</language>\n<atom:link href="${SITE_URL}/termine/feed.xml" rel="self" type="application/rss+xml"/>\n${items}\n</channel>\n</rss>\n`);
}

// Sitemap: jede Terminseite, lastmod = Stand der Termindaten.
{
  const urls = termine.map((t) => `<url><loc>${SITE_URL}/termine/${esc(t.slug)}/</loc><lastmod>${t.stand ? `${t.stand}T08:00:00+02:00` : t.start.toISOString()}</lastmod><changefreq>weekly</changefreq><priority>0.6</priority></url>`).join('\n');
  schreibe('sitemap-termine.xml', `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">\n${urls}\n</urlset>\n`);
}

if (fehler.length) { console.error('Termine: ' + fehler.join('\n  ')); process.exit(2); }
console.log(`Termine: ${termine.length} Terminseiten, ${kommend.length} kommend, ${vergangen.length} vergangen; ${geaendert.length} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}.`);
if (nurPruefen && geaendert.length) process.exit(2);
