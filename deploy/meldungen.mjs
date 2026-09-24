#!/usr/bin/env node
/**
 * Merzenich Aktuell - Meldungen aus recherchierten Quellen anlegen.
 *
 * Quelle: inhalte/meldungen/*.json (je Datei { meldungen: [...] }). Jede
 * Meldung hat Titel, Vorspann, Absaetze, Fakten, Themen, Ortsteil, Datum und
 * die Quelle mit Link. Geschrieben wird nur, was in der Quelle steht; die
 * Rohdaten liegen unter imports/quellen/ (deploy/quellen-abruf.mjs).
 *
 * Schreibt chatgpt-site/<ressort>/<slug>/index.html nach dem Muster der
 * bestehenden Einsatzseiten (Kopf, Artikel, Quelle & Transparenz, Weiterlesen).
 * Bild, Ortsmarke, Themen-, Orts- und Ressortlisten, Suche und Feeds ergaenzen
 * die nachfolgenden Generatoren wie bei jeder anderen Meldung.
 *
 * Jede erzeugte Seite traegt data-meldung="<hash der Quelle>". Aendert sich die
 * Quelle, wird die Seite neu geschrieben; sonst bleibt sie unberuehrt, damit
 * die Ergaenzungen der anderen Generatoren erhalten bleiben. Eine Seite ohne
 * diese Marke (von Hand geschrieben) wird nie ueberschrieben.
 *
 * Aufruf: node deploy/meldungen.mjs [--check]   (als erster Schritt der Kette)
 */
import { readFileSync, writeFileSync, existsSync, readdirSync, mkdirSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { esc, ORTSTEILE, SITE_URL } from './lib-artikel.mjs';
import { bildklassenLesen } from './lib-symbolbilder.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');
const VORLAGE = join(site, 'blaulicht', 'einsatz-118-rosspfad', 'index.html');
const RESSORT = { blaulicht: 'Blaulicht', sport: 'Sport', rathaus: 'Rathaus & Politik', leben: 'Leben', wirtschaft: 'Wirtschaft', menschen: 'Menschen', vereine: 'Vereine' };
const ORTSTEIL_SEITE = { merzenich: '/merzenich/', golzheim: '/golzheim/', girbelsrath: '/girbelsrath/', morschenich: '/morschenich/', buergewald: '/buergewald/' };
const PFLICHT = ['slug', 'ressort', 'ortsteil', 'kicker', 'titel', 'dek', 'datum', 'absaetze', 'themen', 'quelle', 'bildklasse'];

const tz = { timeZone: 'Europe/Berlin' };
const datumLang = (iso) => new Intl.DateTimeFormat('de-DE', { ...tz, day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(iso))
  + ' · ' + new Intl.DateTimeFormat('de-DE', { ...tz, hour: '2-digit', minute: '2-digit' }).format(new Date(iso)) + ' Uhr';
const datumKurz = (iso) => new Intl.DateTimeFormat('de-DE', { ...tz, day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(iso));
const lesezeit = (m) => `${Math.max(1, Math.round([m.dek, ...m.absaetze].join(' ').split(/\s+/).length / 200))} Min. Lesezeit`;
const jsonLd = (o) => JSON.stringify(o).replace(/</g, '\\u003c');

function meldungenLesen() {
  const ordner = join(wurzel, 'inhalte', 'meldungen');
  if (!existsSync(ordner)) return [];
  const alle = [];
  for (const datei of readdirSync(ordner).filter((f) => f.endsWith('.json')).sort()) {
    for (const m of JSON.parse(readFileSync(join(ordner, datei), 'utf8')).meldungen || []) {
      const fehlt = PFLICHT.filter((k) => m[k] === undefined || m[k] === '' || (Array.isArray(m[k]) && !m[k].length));
      if (fehlt.length) throw new Error(`${datei} / ${m.slug || '?'}: Pflichtfelder fehlen: ${fehlt.join(', ')}`);
      if (!RESSORT[m.ressort]) throw new Error(`${datei} / ${m.slug}: unbekanntes Ressort ${m.ressort}`);
      if (!ORTSTEILE[m.ortsteil]) throw new Error(`${datei} / ${m.slug}: unbekannter Ortsteil ${m.ortsteil}`);
      if (Number.isNaN(new Date(m.datum).getTime())) throw new Error(`${datei} / ${m.slug}: Datum ungueltig`);
      if (!bildklassenLesen().klassen[m.bildklasse]) throw new Error(`${datei} / ${m.slug}: Bildklasse ${m.bildklasse} fehlt in deploy/bildklassen.json`);
      if (!/^https:\/\//.test(m.quelle.url || '')) throw new Error(`${datei} / ${m.slug}: Quelle ohne https-Link`);
      alle.push({ ...m, datei });
    }
  }
  const doppelt = alle.map((m) => m.slug).filter((s, i, a) => a.indexOf(s) !== i);
  if (doppelt.length) throw new Error(`Slug doppelt: ${[...new Set(doppelt)].join(', ')}`);
  return alle;
}

// Weiterlesen: drei neueste andere Meldungen, zuerst aus demselben Ortsteil, dann aus dem Ressort.
function weiterlesen(m, index) {
  const andere = (index.artikel || []).filter((a) => a.url !== `/${m.ressort}/${m.slug}/`);
  const nach = (a, b) => String(b.datum).localeCompare(String(a.datum));
  const wahl = [...andere.filter((a) => a.ortsteil === m.ortsteil && a.ressort === m.ressort).sort(nach),
    ...andere.filter((a) => a.ressort === m.ressort).sort(nach)].filter((a, i, l) => l.findIndex((b) => b.url === a.url) === i).slice(0, 3);
  return wahl.map((a) => {
    const bild = a.bild?.src ? `<a href="${esc(a.url)}" tabindex="-1" aria-hidden="true"><div class="media"><img src="${esc(a.bild.src)}"${a.bild.srcset ? ` srcset="${esc(a.bild.srcset)}" sizes="(max-width: 640px) 100vw, 400px"` : ''} alt="${esc(a.bild.alt || '')}" width="${a.bild.width || 1600}" height="${a.bild.height || 1067}" loading="lazy" decoding="async" data-editorial-image class="">${a.bild.badge ? `<span class="badge">${esc(a.bild.badge)}</span>` : ''}</div></a>\n  ` : '';
    return `<article class="news-card">\n  ${bild}<div class="news-card-body">\n    <p class="marke"><span class="marke-ort">Merzenich</span>${a.ortsteil && a.ortsteil !== 'merzenich' && ORTSTEILE[a.ortsteil] ? `<span class="marke-teil"> · ${esc(ORTSTEILE[a.ortsteil])}</span>` : ''}<span class="marke-rubrik">${esc(a.kicker || a.ressortLabel)}</span></p>\n    <h3><a href="${esc(a.url)}">${esc(a.titel)}</a></h3>\n    <p class="dek">${esc(a.teaser)}</p>\n    <div class="meta"><time datetime="${esc(a.datum)}">${esc(datumLang(a.datum))}</time></div><div class="story-actions"><a class="read-more" href="${esc(a.url)}">Mehr lesen<span class="sr-only">: ${esc(a.titel)}</span></a></div>\n  </div>\n</article>`;
  }).join('');
}

// Teilen-Leiste wie in der Vorlage (mit Symbolen), nur die Links sind die der Meldung.
function teilenBlock(m) {
  const vorlageMain = readFileSync(VORLAGE, 'utf8');
  const block = /<div class="share" role="group" aria-label="Teilen">[\s\S]*?<button type="button" data-print[\s\S]*?<\/button>\s*<\/div>/.exec(vorlageMain);
  if (!block) throw new Error('Vorlage: Teilen-Leiste nicht gefunden');
  const ziel = encodeURIComponent(`${SITE_URL}/${m.ressort}/${m.slug}/`);
  const titel = encodeURIComponent(m.titel);
  return block[0]
    .replace(/href="https:\/\/api\.whatsapp\.com\/send\?text=[^"]*"/, `href="https://api.whatsapp.com/send?text=${titel}%20${ziel}"`)
    .replace(/href="https:\/\/www\.facebook\.com\/sharer\/sharer\.php\?u=[^"]*"/, `href="https://www.facebook.com/sharer/sharer.php?u=${ziel}"`)
    .replace(/href="mailto:\?subject=[^"]*"/, `href="mailto:?subject=${titel}&body=${ziel}"`);
}

function hauptteil(m, index) {
  const url = `/${m.ressort}/${m.slug}/`;
  const teil = m.ortsteil !== 'merzenich' ? ORTSTEILE[m.ortsteil] : '';
  const crumbs = `<a href="/">Start</a><span class="sep">›</span><a href="/${m.ressort}/">${esc(RESSORT[m.ressort])}</a><span class="sep">›</span>`
    + (teil ? `<a href="${ORTSTEIL_SEITE[m.ortsteil]}">${esc(teil)}</a><span class="sep">›</span>` : '') + `<span aria-current="page">${esc(m.titel)}</span>`;
  const fakten = m.fakten?.length ? `<div class="facts"><h2>Das Wichtigste in Kürze</h2><ul>${m.fakten.map((f) => `<li>${esc(f)}</li>`).join('')}</ul></div>` : '';
  const tags = m.themen.map(([slug, label]) => `<a href="/thema/${esc(slug)}/" rel="tag">${esc(label)}</a>`).join('')
    + (teil ? `<a href="${ORTSTEIL_SEITE[m.ortsteil]}" rel="tag">${esc(teil)}</a>` : '');
  const hinweis = m.hinweisQuelle ? ` ${esc(m.hinweisQuelle)}` : '';
  const karten = weiterlesen(m, index);
  return `<main id="main">

<article class="article" data-meldung="${m.hash}">

<div class="article-head"><div class="shell">
  <nav class="crumbs" aria-label="Brotkrumen">${crumbs}</nav>
  <div class="kick-row"><div class="location-line"><span class="location-brand">MERZENICH</span>${teil ? ` · ${esc(teil.toUpperCase())}` : ''}</div><span class="kicker">${esc(m.kicker)}</span></div>
  <h1>${esc(m.titel)}</h1>
  <p class="dek">${esc(m.dek)}</p>
  <div class="byline">
    <span class="avatar" aria-hidden="true">MA</span>
    <span class="who"><b><a href="/autor/redaktion/" rel="author">Redaktion Merzenich Aktuell</a></b><span>Lokalredaktion</span></span>
    <span class="dates">Veröffentlicht <time datetime="${esc(m.datum)}">${esc(datumLang(m.datum))}</time><br><span class="readtime">${lesezeit(m)}</span></span>
  </div>
</div></div>
<div class="shell article-grid">
  <div class="article-body" data-readable>
    ${teilenBlock(m)}
    ${fakten}
    <div class="prose">${m.absaetze.map((p) => `<p>${esc(p)}</p>`).join('\n')}
</div>
    <div class="ad-row-body" aria-label="Anzeigen"><div class="managed-ad"><span class="ad-label">Anzeige</span><a href="https://kbs-management.tv/" target="_blank" rel="noopener sponsored"><span class="ad-name">KBS Management GmbH<small>Merzenich · Kreis Düren</small></span></a></div><div class="managed-ad"><span class="ad-label">Anzeige</span><div class="ad-in"><span class="ad-name">AJ Sports Entertainment<small>Merzenich · Kreis Düren</small></span></div></div></div><div class="source-box"><b>Quelle & Transparenz</b> Grundlage dieser Meldung: <a href="${esc(m.quelle.url)}" target="_blank" rel="noopener nofollow">${esc(m.quelle.name)} ↗</a>. <span class="stand">Abgerufen am ${esc(datumKurz(m.quelle.stand))}.</span>${hinweis} Die Redaktion gibt nur wieder, was in der Quelle steht. <a href="/korrekturen/">Fehler melden</a></div>
    <div class="tags">${tags}</div>
    <div class="author-box"><span class="avatar" aria-hidden="true">MA</span><div class="b"><b><a href="/autor/redaktion/">Redaktion Merzenich Aktuell</a></b><p>Die Redaktion prüft jede Meldung gegen die Originalquelle, dokumentiert Bildtyp und Bildcredit und ergänzt eigene Einordnung. Kontakt: <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a></p></div></div>
    <div class="cta-row"><a class="btn ghost" href="/meldung-senden/">Hinweis zu dieser Meldung senden</a><a class="btn ghost" href="/korrekturen/">Fehler melden</a></div>
  </div>
  <aside class="sidebar"><h2 class="sr-only">Weitere Inhalte</h2></aside>
</div>
</article>
${karten ? `<section class="section"><div class="shell"><div class="section-head"><div class="left"><span class="eyebrow">Weiterlesen</span><h2>Das passt zum Thema</h2></div><a class="more" href="/${m.ressort}/">Alle Meldungen</a></div><div class="cards-3">${karten}</div></div></section>\n` : ''}</main>`;
}

function kopf(vorlage, m) {
  const url = `${SITE_URL}/${m.ressort}/${m.slug}/`;
  const teil = m.ortsteil !== 'merzenich' ? ORTSTEILE[m.ortsteil] : '';
  const labels = m.themen.map(([, l]) => l).concat(teil ? [teil] : []);
  const og = `${SITE_URL}/assets/img/og-default.jpg`;
  const ersetze = (s, re, neu) => { if (!re.test(s)) throw new Error(`Vorlage: ${re} nicht gefunden`); return s.replace(re, () => neu); };
  let h = vorlage;
  h = ersetze(h, /<title>[^<]*<\/title>/, `<title>${esc(m.titel)} | Merzenich Aktuell</title>`);
  h = ersetze(h, /<meta name="description" content="[^"]*">/, `<meta name="description" content="${esc(m.dek)}">`);
  h = ersetze(h, /<link rel="canonical" href="[^"]*">/, `<link rel="canonical" href="${url}">`);
  h = ersetze(h, /<meta name="news_keywords" content="[^"]*">/, `<meta name="news_keywords" content="${esc(labels.join(', '))}">\n<meta name="ma:bildklasse" content="${esc(m.bildklasse)}">`);
  h = ersetze(h, /<meta property="og:title" content="[^"]*">/, `<meta property="og:title" content="${esc(m.titel)}">`);
  h = ersetze(h, /<meta property="og:description" content="[^"]*">/, `<meta property="og:description" content="${esc(m.dek)}">`);
  h = ersetze(h, /<meta property="og:url" content="[^"]*">/, `<meta property="og:url" content="${url}">`);
  h = ersetze(h, /<meta property="og:image" content="[^"]*">/, `<meta property="og:image" content="${og}">`);
  h = h.replace(/\n?<meta property="og:image:alt" content="[^"]*">/, '');
  h = ersetze(h, /<meta property="article:published_time" content="[^"]*">/, `<meta property="article:published_time" content="${esc(m.datum)}">`);
  h = ersetze(h, /<meta property="article:modified_time" content="[^"]*">/, `<meta property="article:modified_time" content="${esc(m.datum)}">`);
  h = ersetze(h, /<meta property="article:section" content="[^"]*">/, `<meta property="article:section" content="${esc(RESSORT[m.ressort])}">`);
  h = ersetze(h, /(<meta property="article:tag" content="[^"]*">\n?)+/, labels.map((l) => `<meta property="article:tag" content="${esc(l)}">\n`).join(''));
  h = ersetze(h, /<meta name="twitter:title" content="[^"]*">/, `<meta name="twitter:title" content="${esc(m.titel)}">`);
  h = ersetze(h, /<meta name="twitter:description" content="[^"]*">/, `<meta name="twitter:description" content="${esc(m.dek)}">`);
  h = ersetze(h, /<meta name="twitter:image" content="[^"]*">/, `<meta name="twitter:image" content="${og}">`);
  const artikel = {
    '@context': 'https://schema.org', '@type': 'NewsArticle', '@id': `${url}#article`, mainEntityOfPage: { '@type': 'WebPage', '@id': url },
    headline: m.titel, description: m.dek, image: [{ '@type': 'ImageObject', url: og }],
    datePublished: m.datum, dateModified: m.datum,
    author: [{ '@type': 'Organization', name: 'Redaktion Merzenich Aktuell', url: `${SITE_URL}/autor/redaktion/` }],
    publisher: { '@id': `${SITE_URL}/#organization` }, isAccessibleForFree: true, inLanguage: 'de-DE', articleSection: RESSORT[m.ressort],
    keywords: labels.join(', '), wordCount: [m.dek, ...m.absaetze].join(' ').split(/\s+/).length,
    contentLocation: { '@type': 'Place', name: teil || 'Merzenich', address: { '@type': 'PostalAddress', addressLocality: 'Merzenich', postalCode: '52399', addressCountry: 'DE' } },
    about: { '@type': 'Place', name: teil || 'Merzenich', url: `${SITE_URL}${ORTSTEIL_SEITE[m.ortsteil]}` },
    citation: [m.quelle.url], isBasedOn: [m.quelle.url],
    speakable: { '@type': 'SpeakableSpecification', cssSelector: ['.article-head h1', '.article-head .dek'] },
  };
  const krumen = [['Start', `${SITE_URL}/`], [RESSORT[m.ressort], `${SITE_URL}/${m.ressort}/`], ...(teil ? [[teil, `${SITE_URL}${ORTSTEIL_SEITE[m.ortsteil]}`]] : []), [m.titel]];
  const brot = { '@context': 'https://schema.org', '@type': 'BreadcrumbList', itemListElement: krumen.map(([name, item], i) => ({ '@type': 'ListItem', position: i + 1, name, ...(item ? { item } : {}) })) };
  h = ersetze(h, /<script type="application\/ld\+json">\{"@context":"https:\/\/schema\.org","@type":"NewsArticle"[\s\S]*?<\/script>/, `<script type="application/ld+json">${jsonLd(artikel)}</script>`);
  h = ersetze(h, /<script type="application\/ld\+json">\{"@context":"https:\/\/schema\.org","@type":"BreadcrumbList"[\s\S]*?<\/script>/, `<script type="application/ld+json">${jsonLd(brot)}</script>`);
  return h;
}

const meldungen = meldungenLesen();
const vorlage = readFileSync(VORLAGE, 'utf8');
const index = JSON.parse(readFileSync(join(site, 'api', 'inhalte.json'), 'utf8'));
let neu = 0, aktuell = 0;
const veraltet = [];
for (const m of meldungen) {
  const { datei, ...kern } = m;
  m.hash = createHash('sha256').update(JSON.stringify(kern)).digest('hex').slice(0, 12);
  const pfad = join(site, m.ressort, m.slug, 'index.html');
  if (existsSync(pfad)) {
    const alt = readFileSync(pfad, 'utf8');
    const marke = /<article class="article" data-meldung="([0-9a-f]+)"/.exec(alt);
    if (!marke) throw new Error(`${m.ressort}/${m.slug}: Seite existiert und stammt nicht aus inhalte/meldungen, wird nicht ueberschrieben`);
    // Weiterlesen-Karten auf Artikel, die es nicht mehr gibt: Seite neu schreiben.
    const weiter = alt.slice(alt.indexOf('<div class="cards-3">') >>> 0);
    const tot = alt.includes('<div class="cards-3">') && [...weiter.matchAll(/<h3><a href="(\/[^"#?]+\/)"/g)].some((x) => !existsSync(join(site, x[1], 'index.html')));
    if (marke[1] === m.hash && !tot) { aktuell++; continue; }
  }
  veraltet.push(`${m.ressort}/${m.slug}`);
  if (nurPruefen) continue;
  const i = vorlage.indexOf('<main id="main">'), j = vorlage.indexOf('</main>') + '</main>'.length;
  const html = kopf(vorlage.slice(0, i), m) + hauptteil(m, index) + vorlage.slice(j);
  mkdirSync(dirname(pfad), { recursive: true });
  writeFileSync(pfad, html);
  neu++;
}
console.log(`Meldungen: ${meldungen.length} aus inhalte/meldungen, ${aktuell} aktuell, ${nurPruefen ? `${veraltet.length} nicht aktuell` : `${neu} geschrieben`}${veraltet.length ? ': ' + veraltet.join(', ') : ''}.`);
if (nurPruefen && veraltet.length) process.exit(2);
