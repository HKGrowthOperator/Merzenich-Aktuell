#!/usr/bin/env node
/**
 * Unternehmen aus Merzenich (/unternehmen/) und die Angebote unter /tipp/
 * (KBS/Ordin 26.09.2026, Vorbild Oberberg Aktuell). Daten: deploy/unternehmen.json.
 * - /unternehmen/ (seit 30.09.2026 eigener Menuepunkt, aufgebaut wie der
 *   Wirtschaftsbereich von Oberberg Aktuell): Unternehmensmeldungen als Karten,
 *   Unternehmenskanaele /unternehmen/<slug>/ (nur mit Einwilligung), Werbung.
 * - Hauptnavigation: „Unternehmen“ nach „Wirtschaft“ auf allen Seiten.
 * - /tipp/: Abschnitt "Angebote fuer Vereine & Unternehmen" mit Preis auf Anfrage
 *   und Direktweg in den Anzeige-Assistenten.
 * Die Seite /unternehmen/ entsteht beim ersten Lauf aus der Vorlage /werben/.
 * Aufruf: node deploy/unternehmen.mjs [--check]
 */
import { readFileSync, writeFileSync, existsSync, mkdirSync, readdirSync, statSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { bild } from './lib-piktogramme.mjs';
import { SITE_URL, liveUrl, SIZES } from './lib-artikel.mjs';

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
// ---------------------------------------------------------------- /unternehmen/ wie Oberberg Aktuell
// Betreiber 30.09.2026: „Unternehmen“ als eigener Menuepunkt, aufgebaut wie der
// Wirtschaftsbereich von Oberberg Aktuell: Liste der Unternehmensmeldungen als
// Karten, je zwei nebeneinander, Nachladen beim Scrollen; Unternehmenskanaele
// (je Unternehmen eine Seite mit seinen Beitraegen, jeder als Anzeige
// gekennzeichnet); rechts Werbung. Kanaele entstehen nur aus echten Eintraegen
// in deploy/unternehmen.json mit dokumentierter Einwilligung.
const inhalte = JSON.parse(readFileSync(join(site, 'api', 'inhalte.json'), 'utf8'));
const ORTNAME = { merzenich: 'Merzenich', golzheim: 'Golzheim', girbelsrath: 'Girbelsrath', morschenich: 'Morschenich', buergewald: 'Bürgewald' };
const kanaele = daten.unternehmen.map((u) => ({ ...u, slug: u.slug || slug(u.name), beitraege: u.beitraege || [] }));
const kanalFuer = new Map(kanaele.flatMap((k) => k.beitraege.map((url) => [url, k])));
const meldungen = (inhalte.artikel || [])
  .filter((a) => a.ressort === 'wirtschaft' || kanalFuer.has(a.url))
  .sort((a, b) => String(b.datum).localeCompare(String(a.datum)));
const tagZeit = (iso) => new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(iso))
  + ', ' + new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', hour: '2-digit', minute: '2-digit' }).format(new Date(iso)) + ' Uhr';
const ERSTE = 8;
function karte(a, i) {
  const k = kanalFuer.get(a.url);
  const b = a.bild;
  const bildHtml = b && b.src ? `<a class="u-karte__bild" href="${esc(a.url)}" tabindex="-1" aria-hidden="true"><img src="${esc(b.src)}"${b.srcset ? ` srcset="${esc(b.srcset)}" sizes="${SIZES.unternehmen}"` : ''} alt="${esc(b.alt || '')}" loading="${i < 2 ? 'eager' : 'lazy'}" decoding="async">${b.badge ? `<span class="badge">${esc(b.badge)}</span>` : ''}</a>` : '';
  return `<article class="u-karte${bildHtml ? '' : ' u-karte--ohne-bild'}"${i >= ERSTE ? ' data-nachladen hidden' : ''}>${bildHtml}`
    + `<p class="u-karte__kicker">${esc(k ? k.name : (ORTNAME[a.ortsteil] || a.ressortLabel || 'Wirtschaft'))}</p>`
    + `<h2><a href="${esc(a.url)}">${esc(a.titel)}</a></h2>`
    + `<p class="u-karte__meta">${k ? '<span class="fbadge anzeige">Anzeige</span>' : 'Redaktion'}${a.datum && !Number.isNaN(Date.parse(a.datum)) ? ` · <time datetime="${esc(a.datum)}">${tagZeit(a.datum)}</time>` : ''}</p>`
    + (a.teaser ? `<p class="u-karte__teaser">${esc(a.teaser)}</p>` : '')
    + `<a class="u-karte__weiter" href="${esc(a.url)}">Weiterlesen<span class="sr-only">: ${esc(a.titel)}</span></a></article>`;
}
const kanalListe = kanaele.length
  ? `<ul class="u-kanaele">${kanaele.map((k) => `<li><a href="/unternehmen/${esc(k.slug)}/">${esc(k.name)}</a><small>${esc(k.branche)}${k.ortsteil ? ` · ${esc(k.ortsteil)}` : ''}</small></li>`).join('')}</ul>`
  : '<p class="u-leer">Noch kein Unternehmen hat einen eigenen Kanal. Hier erscheinen Unternehmen aus der Gemeinde mit ihren Beiträgen, jeder als Anzeige gekennzeichnet.</p>';
const unternehmenMain = '<main id="main"><div class="page-head"><div class="shell"><nav class="crumbs" aria-label="Brotkrumen"><a href="/">Start</a><span class="sep">›</span><span aria-current="page">Unternehmen</span></nav>'
  + '<h1>Unternehmen</h1><p class="desc">Wirtschaft in Merzenich: Meldungen der Redaktion über Betriebe, Arbeit und Strukturwandel und die Kanäle der Unternehmen aus der Gemeinde. Beiträge von Unternehmen sind als Anzeige gekennzeichnet.</p>'
  + `<p class="count-line">${meldungen.length} ${meldungen.length === 1 ? 'Meldung' : 'Meldungen'} · ${kanaele.length} ${kanaele.length === 1 ? 'Unternehmenskanal' : 'Unternehmenskanäle'}</p></div></div>`
  + '<section class="section"><div class="shell content-grid"><div class="u-liste">'
  + `<div class="u-karten" data-u-karten>${meldungen.map(karte).join('')}</div>`
  + (meldungen.length > ERSTE ? '<p class="u-mehr"><button class="btn ghost" type="button" data-u-mehr>Weitere Meldungen laden</button></p>' : '')
  + (meldungen.length ? '' : '<p class="no-result">Noch keine Unternehmensmeldungen.</p>')
  + '</div><aside class="sidebar">'
  + `<div class="sidebox u-box"><h3>Unternehmenskanäle</h3>${kanalListe}<p><a class="btn" href="${esc(assistent('Unternehmenskanal'))}">Eigenen Kanal anfragen</a></p></div>`
  + `<div class="sidebox u-box"><h3>Für Unternehmen</h3><ul class="linklist"><li><a href="/werben/">Werben &amp; Mediadaten</a></li><li><a href="${esc(assistent('Unternehmenspräsenz'))}">Unternehmensporträt</a><small>Porträt mit Bild, Öffnungszeiten und Kontakt</small></li><li><a href="/betriebe/">Branchenbuch: lokale Betriebe</a><small>einfacher Eintrag kostenlos</small></li><li><a href="/jobs/">Stellenmarkt</a></li><li><a href="/immobilien/">Immobilienmarkt</a></li></ul></div>`
  + '<!-- werbung:unternehmen:start --><!-- werbung:unternehmen:end --></aside></div></section></main>';

// Kanalseiten /unternehmen/<slug>/: Kopf des Unternehmens, „Über …“, Beitraege.
const kanalSeiten = kanaele.map((k) => {
  const eigene = meldungen.filter((a) => kanalFuer.get(a.url) === k);
  const main = `<main id="main"><div class="page-head"><div class="shell"><nav class="crumbs" aria-label="Brotkrumen"><a href="/">Start</a><span class="sep">›</span><a href="/unternehmen/" rel="up">Unternehmen</a><span class="sep">›</span><span aria-current="page">${esc(k.name)}</span></nav>`
    + `<h1>${esc(k.name)}</h1><p class="desc">${esc(k.branche)}${k.ortsteil ? ` · ${esc(k.ortsteil)}` : ''}. Unternehmenskanal, alle Beiträge sind Anzeigen.</p></div></div>`
    + `<section class="section"><div class="shell content-grid"><div class="u-liste"><div class="u-karten">${eigene.map(karte).join('')}</div>${eigene.length ? '' : '<p class="no-result">Noch keine Beiträge.</p>'}</div>`
    + `<aside class="sidebar"><div class="sidebox u-box"><h3>Über ${esc(k.name)}</h3>${k.text ? `<p>${esc(k.text)}</p>` : ''}<dl class="firma-daten">${k.adresse ? `<dt>Adresse</dt><dd>${esc(k.adresse)}</dd>` : ''}${k.oeffnungszeiten ? `<dt>Öffnungszeiten</dt><dd>${esc(k.oeffnungszeiten)}</dd>` : ''}${k.telefon ? `<dt>Telefon</dt><dd><a href="tel:${esc(k.telefon.replace(/[^\d+]/g, ''))}">${esc(k.telefon)}</a></dd>` : ''}${k.website ? `<dt>Website</dt><dd><a href="${esc(k.website)}" target="_blank" rel="noopener sponsored">${esc(k.website.replace(/^https:\/\/(www\.)?/, '').replace(/\/$/, ''))}</a></dd>` : ''}</dl></div>`
    + '<!-- werbung:unternehmen:start --><!-- werbung:unternehmen:end --></aside></div></section></main>';
  return { k, main };
});
{
  const ziel = join(site, 'unternehmen', 'index.html');
  const vorlagePfad = existsSync(ziel) ? ziel : join(site, 'werben', 'index.html');
  let html = readFileSync(vorlagePfad, 'utf8');
  const alt = existsSync(ziel) ? html : '';
  const beschreibung = 'Unternehmen in Merzenich: Wirtschaftsmeldungen der Redaktion und die Kanäle der Unternehmen aus der Gemeinde, Unternehmensbeiträge als Anzeige gekennzeichnet.';
  html = html.replace(/<title>[^<]*<\/title>/, '<title>Unternehmen | Merzenich Aktuell</title>')
    .replace(/<meta name="description" content="[^"]*">/, `<meta name="description" content="${esc(beschreibung)}">`)
    .replace(/<meta property="og:description" content="[^"]*">/, `<meta property="og:description" content="${esc(beschreibung)}">`)
    .replace(/<meta name="twitter:description" content="[^"]*">/, `<meta name="twitter:description" content="${esc(beschreibung)}">`)
    .replace(/<link rel="canonical" href="[^"]*">/, `<link rel="canonical" href="${liveUrl('/unternehmen/')}">`)
    .replace(/<meta property="og:url" content="[^"]*">/, `<meta property="og:url" content="${liveUrl('/unternehmen/')}">`)
    .replace(/<meta property="og:title" content="[^"]*">/, '<meta property="og:title" content="Unternehmen in Merzenich">')
    .replace(/<meta name="twitter:title" content="[^"]*">/, '<meta name="twitter:title" content="Unternehmen in Merzenich">')
    .replace(/<script type="application\/ld\+json">(?:(?!<\/script>)[\s\S])*?"@type":"(?:WebPage|BreadcrumbList|CollectionPage)"[\s\S]*?<\/script>/g, '');
  // Hauptbereich ersetzen; eine schon gefuellte Werbeflaeche bleibt (anzeigen.mjs fuellt sie).
  const i = html.indexOf('<main'); const j = html.indexOf('</main>') + '</main>'.length;
  // Die gefuellte Werbeflaeche bleibt stehen, bis deploy/anzeigen.mjs sie neu setzt.
  const werbungU = (/<!-- werbung:unternehmen:start -->[\s\S]*?<!-- werbung:unternehmen:end -->/.exec(html.slice(i, j)) || [])[0];
  let main = unternehmenMain;
  if (alt && werbungU) main = main.replace('<!-- werbung:unternehmen:start --><!-- werbung:unternehmen:end -->', werbungU);
  html = html.slice(0, i) + main + html.slice(j);
  if (!html.includes('/assets/unternehmen.js')) html = html.replace(/<script src="\/assets\/app\.js[^"]*" defer><\/script>/, (m) => m + '<script src="/assets/unternehmen.js" defer></script>');
  if (html !== alt) { geaendert.push('unternehmen/index.html'); if (!nurPruefen) { mkdirSync(dirname(ziel), { recursive: true }); writeFileSync(ziel, html); } }
}

// Kanalseiten aus der Uebersicht als Vorlage (Kopf, Fuss, Skripte gleich).
{
  const vorlage = readFileSync(join(site, 'unternehmen', 'index.html'), 'utf8');
  for (const { k, main } of kanalSeiten) {
    const ziel = join(site, 'unternehmen', k.slug, 'index.html');
    const alt = existsSync(ziel) ? readFileSync(ziel, 'utf8') : '';
    const i = vorlage.indexOf('<main'); const j = vorlage.indexOf('</main>') + '</main>'.length;
    const titel = `${k.name} | Unternehmen | Merzenich Aktuell`;
    let html = (vorlage.slice(0, i) + main + vorlage.slice(j))
      .replace(/<title>[^<]*<\/title>/, `<title>${esc(titel)}</title>`)
      .replace(/<link rel="canonical" href="[^"]*">/, `<link rel="canonical" href="${liveUrl(`/unternehmen/${esc(k.slug)}/`)}">`)
      .replace(/<meta property="og:url" content="[^"]*">/, `<meta property="og:url" content="${liveUrl(`/unternehmen/${esc(k.slug)}/`)}">`);
    const w = alt && (/<!-- werbung:unternehmen:start -->[\s\S]*?<!-- werbung:unternehmen:end -->/.exec(alt) || [])[0];
    if (w) html = html.replace(/<!-- werbung:unternehmen:start -->[\s\S]*?<!-- werbung:unternehmen:end -->/, w);
    if (html !== alt) { geaendert.push(`unternehmen/${k.slug}/index.html`); if (!nurPruefen) { mkdirSync(dirname(ziel), { recursive: true }); writeFileSync(ziel, html); } }
  }
}

// ---------------------------------------------------------------- Hauptnavigation
// „Unternehmen“ steht als eigener Punkt nach „Wirtschaft“ in der Ressortleiste
// und im Handymenue, auf jeder Seite (die Leiste ist in jede Seite gebacken).
{
  // Nur in Ressortleiste und Ressort-Schublade, niemals in Breadcrumbs
  // oder redaktionellen Links. Die fruehere globale Regex traf auch
  // "Wirtschaft > Tipp" in Artikel-Brotkrumen und machte den Build dadurch
  // nicht idempotent.
  const NAV_RE = /(<a href="\/wirtschaft\/"(?: aria-current="page")?>Wirtschaft<\/a>)(?=<a href="\/tipp\/">)/g;
  const navBereiche = (html, fn) => html
    .replace(/<div class="navscroll">[\s\S]*?<\/div>/g, fn)
    .replace(/<div class="drawer-group"><div class="grp">Ressorts<\/div>[\s\S]*?<\/div>/g, fn);
  const seiten = [];
  (function lauf(d) { for (const e of readdirSync(d)) { const p = join(d, e); if (statSync(p).isDirectory()) { if (!['admin', 'redaktion', 'node_modules'].includes(e)) lauf(p); } else if (e.endsWith('.html')) seiten.push(p); } })(site);
  for (const pfad of seiten) {
    const alt = readFileSync(pfad, 'utf8');
    if (!alt.includes('class="navscroll"')) continue;
    const hier = pfad.slice(site.length).replace(/\\/g, '/').startsWith('/unternehmen/');
    let neu = navBereiche(alt, (block) => block.replace(NAV_RE, (m, w) => `${w}<a href="/unternehmen/"${hier ? ' aria-current="page"' : ''}>Unternehmen</a>`));
    if (hier) neu = neu.replace(/(<div class="navscroll">(?:(?!<\/div>)[\s\S])*?)<a href="\/unternehmen\/">Unternehmen<\/a>/, '$1<a href="/unternehmen/" aria-current="page">Unternehmen</a>');
    if (!neu.includes('<a href="/unternehmen/"')) fehler.push(`${pfad.slice(site.length)}: Unternehmen fehlt in der Navigation`);
    if (neu !== alt) { geaendert.push(pfad.slice(site.length)); if (!nurPruefen) writeFileSync(pfad, neu); }
  }
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
console.log(`Unternehmen: ${meldungen.length} Meldungen, ${kanaele.length} Kanaele, ${daten.angebote.length} Angebote; ${geaendert.length} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}.`);
if (nurPruefen && geaendert.length) {
  console.error('Unternehmen nicht aktuell: ' + geaendert.join(', '));
  process.exit(2);
}
