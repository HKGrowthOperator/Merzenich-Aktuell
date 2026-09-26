#!/usr/bin/env node
/**
 * Unternehmen aus Merzenich (/unternehmen/) und die Angebote unter /tipp/
 * (KBS/Ordin 26.09.2026, Vorbild Oberberg Aktuell). Daten: deploy/unternehmen.json.
 * - /unternehmen/: Kacheln der Betriebe (nur mit Einwilligung), Branchenfilter,
 *   freie Plaetze "Ihr Unternehmen hier" bis zur Zahl in plaetze.
 * - /tipp/: Abschnitt "Angebote fuer Vereine & Unternehmen" mit Preis auf Anfrage
 *   und Direktweg in den Anzeige-Assistenten.
 * Die Seite /unternehmen/ entsteht beim ersten Lauf aus der Vorlage /werben/.
 * Aufruf: node deploy/unternehmen.mjs [--check]
 */
import { readFileSync, writeFileSync, existsSync, mkdirSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { bild } from './lib-piktogramme.mjs';
import { SITE_URL } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const daten = JSON.parse(readFileSync(join(wurzel, 'deploy', 'unternehmen.json'), 'utf8'));
const ORTE = ['Merzenich', 'Golzheim', 'Girbelsrath', 'Morschenich', 'Bürgewald'];
const fehler = [];
const geaendert = [];
const assistent = (format) => `/anzeigen/aufgeben/?art=Werbung&format=${encodeURIComponent(format)}`;

// ---------------------------------------------------------------- Pruefung
for (const u of daten.unternehmen) {
  if (!u.name || !u.branche) fehler.push(`Unternehmen ohne name/branche: ${JSON.stringify(u).slice(0, 60)}`);
  if (!/^\d{4}-\d{2}-\d{2}$/.test(u.einwilligung || '')) fehler.push(`${u.name}: ohne dokumentierte Einwilligung (einwilligung: Datum) nicht veroeffentlichen`);
  if (u.ortsteil && !ORTE.includes(u.ortsteil)) fehler.push(`${u.name}: unbekannter Ortsteil ${u.ortsteil}`);
  if (u.website && !/^https:\/\//.test(u.website)) fehler.push(`${u.name}: website muss mit https:// beginnen`);
  if (u.bild && !existsSync(join(site, u.bild.replace(/^\//, '')))) fehler.push(`${u.name}: Bild ${u.bild} fehlt`);
}
for (const a of daten.angebote) if (/€|\d+\s*Euro/i.test(`${a.titel} ${a.text}`)) fehler.push(`Angebot ${a.titel}: keine Preise (Preis auf Anfrage)`);

// ---------------------------------------------------------------- /unternehmen/
const slug = (t) => String(t).toLowerCase().replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
const firmaHtml = (u) => `<article class="firma" data-branche="${esc(slug(u.branche))}">`
  + (u.bild ? `<div class="firma-bild"><img src="${esc(u.bild)}" alt="${esc(u.name)}" loading="lazy" decoding="async"></div>` : `<div class="firma-bild firma-bild--zeichen" aria-hidden="true">${esc(u.name.slice(0, 2).toUpperCase())}</div>`)
  + `<div class="firma-text"><p class="firma-branche">${esc(u.branche)}${u.ortsteil ? ` · ${esc(u.ortsteil)}` : ''}</p><h2>${esc(u.name)}</h2>`
  + (u.text ? `<p>${esc(u.text)}</p>` : '')
  + '<dl class="firma-daten">'
  + (u.adresse ? `<dt>Adresse</dt><dd>${esc(u.adresse)}</dd>` : '')
  + (u.oeffnungszeiten ? `<dt>Öffnungszeiten</dt><dd>${esc(u.oeffnungszeiten)}</dd>` : '')
  + (u.telefon ? `<dt>Telefon</dt><dd><a href="tel:${esc(u.telefon.replace(/[^\d+]/g, ''))}">${esc(u.telefon)}</a></dd>` : '')
  + (u.website ? `<dt>Website</dt><dd><a href="${esc(u.website)}" target="_blank" rel="noopener">${esc(u.website.replace(/^https:\/\/(www\.)?/, '').replace(/\/$/, ''))}</a></dd>` : '')
  + '</dl>' + (u.bildnachweis ? `<p class="firma-nachweis">Bild: ${esc(u.bildnachweis)}</p>` : '') + '<p class="firma-kennung">Unternehmensporträt · Anzeige</p></div></article>';
const frei = '<article class="firma firma--frei"><div class="firma-bild firma-bild--frei" aria-hidden="true">' + bild('laden') + '</div><div class="firma-text"><p class="firma-branche">Freier Platz</p><h2>Ihr Unternehmen hier</h2><p>Porträt mit Bild, Öffnungszeiten und Kontakt, geprüft von der Redaktion. Preis auf Anfrage.</p>'
  + `<p><a class="read-more" href="${esc(assistent('Unternehmenspräsenz'))}">Platz anfragen</a></p></div></article>`;
const branchen = [...new Set(daten.unternehmen.map((u) => u.branche))].sort((a, b) => a.localeCompare(b, 'de'));
const unternehmenMain = '<main id="main"><div class="page-head"><div class="shell"><nav class="crumbs" aria-label="Brotkrumen"><a href="/">Start</a><span class="sep">›</span><a href="/wirtschaft/" rel="up">Wirtschaft</a><span class="sep">›</span><span aria-current="page">Unternehmen</span></nav>'
  + '<span class="eyebrow">Wirtschaft</span><h1>Unternehmen aus Merzenich</h1><p class="desc">Betriebe aus der Gemeinde stellen sich vor: mit Öffnungszeiten, Kontakt und einem kurzen Porträt. Jeder Eintrag ist als Anzeige gekennzeichnet und von der Redaktion geprüft.</p>'
  + `<p class="count-line">${daten.unternehmen.length} ${daten.unternehmen.length === 1 ? 'Betrieb' : 'Betriebe'} · ${Math.max(0, daten.plaetze - daten.unternehmen.length)} freie Plätze</p></div></div>`
  + '<section class="section"><div class="shell">'
  + (branchen.length > 1 ? `<nav class="firmen-filter" aria-label="Nach Branche filtern"><a href="#alle" data-branche="">Alle</a>${branchen.map((b) => `<a href="#${esc(slug(b))}" data-branche="${esc(slug(b))}">${esc(b)}</a>`).join('')}</nav>` : '')
  + `<div class="firmen">${daten.unternehmen.map(firmaHtml).join('')}${Array.from({ length: Math.max(0, daten.plaetze - daten.unternehmen.length) }, () => frei).join('')}</div>`
  + '<div class="firmen-weg"><h2>So kommt Ihr Betrieb auf diese Seite</h2><ol><li><b>Platz anfragen.</b> Über den Anzeige-Assistenten oder per E-Mail an die Redaktion.</li><li><b>Porträt abstimmen.</b> Text, Bild, Öffnungszeiten und Kontakt, mit Ihrer Einwilligung zur Veröffentlichung.</li><li><b>Freigabe.</b> Die Redaktion prüft den Eintrag und schaltet ihn frei. Änderungen jederzeit.</li></ol>'
  + '<p>Ein einfacher Eintrag im Branchenbuch bleibt kostenlos: <a href="/betriebe/">Lokale Betriebe in Merzenich</a>.</p>'
  + `<p><a class="btn" href="${esc(assistent('Unternehmenspräsenz'))}">Platz anfragen</a> <a class="btn ghost" href="/werben/">Werben & Mediadaten</a></p></div>`
  + '</div></section><!-- werbung:artikel:start --><!-- werbung:artikel:end --></main>';
{
  const ziel = join(site, 'unternehmen', 'index.html');
  const vorlagePfad = existsSync(ziel) ? ziel : join(site, 'werben', 'index.html');
  let html = readFileSync(vorlagePfad, 'utf8');
  const alt = existsSync(ziel) ? html : '';
  const beschreibung = 'Unternehmen aus der Gemeinde Merzenich stellen sich vor: Porträt, Öffnungszeiten und Kontakt, als Anzeige gekennzeichnet und von der Redaktion geprüft.';
  html = html.replace(/<title>[^<]*<\/title>/, '<title>Unternehmen aus Merzenich | Merzenich Aktuell</title>')
    .replace(/<meta name="description" content="[^"]*">/, `<meta name="description" content="${esc(beschreibung)}">`)
    .replace(/<meta property="og:description" content="[^"]*">/, `<meta property="og:description" content="${esc(beschreibung)}">`)
    .replace(/<meta name="twitter:description" content="[^"]*">/, `<meta name="twitter:description" content="${esc(beschreibung)}">`)
    .replace(/<link rel="canonical" href="[^"]*">/, `<link rel="canonical" href="${SITE_URL}/unternehmen/">`)
    .replace(/<meta property="og:url" content="[^"]*">/, `<meta property="og:url" content="${SITE_URL}/unternehmen/">`)
    .replace(/<meta property="og:title" content="[^"]*">/, '<meta property="og:title" content="Unternehmen aus Merzenich">')
    .replace(/<meta name="twitter:title" content="[^"]*">/, '<meta name="twitter:title" content="Unternehmen aus Merzenich">')
    .replace(/<script type="application\/ld\+json">(?:(?!<\/script>)[\s\S])*?"@type":"(?:WebPage|BreadcrumbList|CollectionPage)"[\s\S]*?<\/script>/g, '');
  // Hauptbereich ersetzen; eine schon gefuellte Werbeflaeche bleibt (anzeigen.mjs fuellt sie).
  const i = html.indexOf('<main'); const j = html.indexOf('</main>') + '</main>'.length;
  const werbungAlt = (/<!-- werbung:artikel:start -->[\s\S]*?<!-- werbung:artikel:end -->/.exec(html.slice(i, j)) || [])[0];
  let main = unternehmenMain;
  if (alt && werbungAlt) main = main.replace('<!-- werbung:artikel:start --><!-- werbung:artikel:end -->', werbungAlt);
  html = html.slice(0, i) + main + html.slice(j);
  if (html !== alt) { geaendert.push('unternehmen/index.html'); if (!nurPruefen) { mkdirSync(dirname(ziel), { recursive: true }); writeFileSync(ziel, html); } }
}

// ---------------------------------------------------------------- /tipp/ Angebote
{
  const pfad = join(site, 'tipp', 'index.html');
  const alt = readFileSync(pfad, 'utf8');
  const block = '<!-- tipp:angebote:start --><section class="angebote" aria-labelledby="angebote-titel"><h2 id="angebote-titel">Angebote für Vereine & Unternehmen</h2>'
    + '<p class="angebote-intro">Sichtbar werden in Merzenich: jede bezahlte Platzierung klar gekennzeichnet, von der Redaktion getrennt. Preis auf Anfrage.</p>'
    + `<div class="angebote-liste">${daten.angebote.map((a) => `<a class="angebot" href="${esc(assistent(a.format))}"><span class="angebot-bild" aria-hidden="true">${bild(a.bild).replace('class="anz-karte__bild"', 'class="angebot-zeichen"')}</span><span class="angebot-text"><strong>${esc(a.titel)}</strong><span>${esc(a.text)}</span><em>Preis auf Anfrage · Anfragen</em></span></a>`).join('')}</div>`
    + '<p class="angebote-mehr"><a href="/unternehmen/">Unternehmen aus Merzenich</a> · <a href="/werben/">Werben & Mediadaten</a></p></section><!-- tipp:angebote:end -->';
  const MARKE = /<!-- tipp:angebote:start -->[\s\S]*?<!-- tipp:angebote:end -->/;
  let neu = alt;
  if (MARKE.test(alt)) neu = alt.replace(MARKE, () => block);
  else {
    const anker = '<div data-leerzustand="/tipp/"';
    if (!alt.includes(anker)) fehler.push('tipp/index.html: Anker fuer die Angebote fehlt');
    else neu = alt.replace(anker, block + anker);
  }
  if (neu !== alt) { geaendert.push('tipp/index.html'); if (!nurPruefen) writeFileSync(pfad, neu); }
}

if (fehler.length) { console.error('Unternehmen: ' + fehler.join('\n  ')); process.exit(2); }
console.log(`Unternehmen: ${daten.unternehmen.length} Betriebe, ${daten.plaetze} Plaetze, ${daten.angebote.length} Angebote; ${geaendert.length} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}.`);
if (nurPruefen && geaendert.length) process.exit(2);
