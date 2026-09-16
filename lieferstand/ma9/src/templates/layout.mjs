import { esc, json, fmt, isoLocal } from '../lib/util.mjs';
import { cdn } from './media.mjs';

const PULSE = `<div class="pulse"><svg viewBox="0 0 600 14" preserveAspectRatio="none" aria-hidden="true"><path d="M0 7h300l6-3 5 4 7-6 6 8 8-11 6 13 7-7 9 -6 7 12 6-4h220" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg></div>`;
export { PULSE };

export const ICONS = {
  whatsapp: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 00-8.6 15.1L2 22l5.1-1.3A10 10 0 1012 2zm0 2a8 8 0 016.9 12.1l-.3.5.6 2.2-2.3-.6-.5.3A8 8 0 1112 4zm-3.2 4c-.2 0-.5.1-.7.4-.3.3-.8.9-.8 1.9 0 1 .7 2 .8 2.1.1.2 1.4 2.3 3.5 3.1 1.7.7 2.1.6 2.5.5.5 0 1.3-.6 1.5-1.1.2-.6.2-1 .1-1.1l-1.5-.7c-.2-.1-.4-.1-.5.1l-.6.8c-.1.1-.3.2-.5.1-.6-.3-1.2-.6-1.7-1.3-.5-.6-.7-1-.9-1.2-.1-.2 0-.3.1-.4l.4-.5c.1-.2.1-.3 0-.5L9.4 8.3c-.1-.2-.3-.3-.6-.3z"/></svg>',
  facebook: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 21v-7h2.4l.4-2.8h-2.8V9.4c0-.8.2-1.4 1.4-1.4h1.5V5.4c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.1H8.1V14h2.4v7h3z"/></svg>',
  mail: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v14H3V5zm2 2v.5l7 4.2 7-4.2V7H5zm0 3v7h14v-7l-7 4.2L5 10z"/></svg>',
  link: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.6 13.4a1 1 0 001.4 0l3.5-3.5a2.5 2.5 0 10-3.5-3.5l-1 1 1.4 1.4 1-1a.7.7 0 011 1l-3.5 3.5a1 1 0 000 1.1zm2.8-2.8a1 1 0 00-1.4 0l-3.5 3.5a2.5 2.5 0 103.5 3.5l1-1-1.4-1.4-1 1a.7.7 0 01-1-1l3.5-3.5a1 1 0 000-1.1z"/></svg>',
  print: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h12v5H6V3zm-2 6h16a2 2 0 012 2v6h-4v4H6v-4H2v-6a2 2 0 012-2zm4 6v4h8v-4H8z"/></svg>',
  search: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M15.5 15.5L21 21" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>',
  sun: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4.5"/><path d="M12 1.5v2.6M12 19.9v2.6M4.2 4.2l1.9 1.9M17.9 17.9l1.9 1.9M1.5 12h2.6M19.9 12h2.6M4.2 19.8l1.9-1.9M17.9 6.1l1.9-1.9" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg>',
  system: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2.5" y="4" width="19" height="13" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 20.5h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  moon: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 14.2A8.4 8.4 0 019.8 4 8.4 8.4 0 1020 14.2z"/></svg>',
  bell: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a6 6 0 00-6 6v4l-2 4h16l-2-4V8a6 6 0 00-6-6zm-2 17a2 2 0 004 0h-4z"/></svg>',
  arrow: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  calendar: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  pin: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s7-7.1 7-12a7 7 0 10-14 0c0 4.9 7 12 7 12z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="2.5" fill="none" stroke="currentColor" stroke-width="2"/></svg>'
};

function absolute(site, path) { return path.startsWith('http') ? path : site.url + path; }

/**
 * meta: { title, description, url, image, imageAlt, type ('website'|'article'), jsonld: [], noindex, robots, feed, canonical, published, modified, section, tags, breadcrumbs:[{name,url}], bodyClass, nav:'nachrichten', ort:'merzenich' }
 */
export function layout(site, ctx, meta, content) {
  const title = meta.title ? `${meta.title} | ${site.name}` : `${site.name} · ${site.tagline}`;
  const desc = meta.description || site.description;
  const url = absolute(site, meta.url || '/');
  const image = absolute(site, meta.image || '/assets/img/og-default.jpg');
  const ld = [orgLd(site), websiteLd(site), ...(meta.jsonld || [])];
  if (meta.breadcrumbs && meta.breadcrumbs.length) ld.push(breadcrumbLd(site, meta.breadcrumbs));

  return `<!doctype html>
<html lang="de" data-page="${esc(meta.bodyClass || '')}">
<head>
<meta http-equiv="Content-Security-Policy" content="default-src 'self'; img-src 'self' data: https:; script-src 'self'; style-src 'self' 'unsafe-inline'; font-src 'self'; connect-src 'self' https://api.open-meteo.com; base-uri 'self'; form-action 'self'">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>${esc(title)}</title>
<meta name="description" content="${esc(desc)}">
<link rel="canonical" href="${esc(meta.canonical || url)}">
<meta name="robots" content="${meta.noindex ? 'noindex,follow' : 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1'}">
<meta name="theme-color" content="#0c0b0a">
<meta name="color-scheme" content="light">
<meta name="author" content="${esc(meta.author || site.name)}">
<meta name="geo.region" content="DE-NW"><meta name="geo.placename" content="Merzenich"><meta name="geo.position" content="${site.geo.lat};${site.geo.lng}"><meta name="ICBM" content="${site.geo.lat}, ${site.geo.lng}">
${meta.keywords ? `<meta name="news_keywords" content="${esc(meta.keywords)}">` : ''}
<meta property="og:type" content="${meta.type || 'website'}">
<meta property="og:site_name" content="${esc(site.name)}">
<meta property="og:locale" content="de_DE">
<meta property="og:title" content="${esc(meta.title || site.name)}">
<meta property="og:description" content="${esc(desc)}">
<meta property="og:url" content="${esc(url)}">
<meta property="og:image" content="${esc(image)}">
${meta.imageAlt ? `<meta property="og:image:alt" content="${esc(meta.imageAlt)}">` : ''}
${meta.published ? `<meta property="article:published_time" content="${esc(meta.published)}">` : ''}
${meta.modified ? `<meta property="article:modified_time" content="${esc(meta.modified)}">` : ''}
${meta.section ? `<meta property="article:section" content="${esc(meta.section)}">` : ''}
${(meta.tags || []).map(t => `<meta property="article:tag" content="${esc(t)}">`).join('')}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="${esc(meta.title || site.name)}">
<meta name="twitter:description" content="${esc(desc)}">
<meta name="twitter:image" content="${esc(image)}">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/assets/img/avatar-1024.png">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="alternate" type="application/rss+xml" title="${esc(site.name)} – Alle Meldungen" href="/feed.xml">
${meta.feed ? `<link rel="alternate" type="application/rss+xml" title="${esc(site.name)} – ${esc(meta.feedTitle || meta.title)}" href="${esc(meta.feed)}">` : ''}
<link rel="alternate" type="application/feed+json" title="${esc(site.name)} JSON Feed" href="/feed.json">
<link rel="preload" href="/assets/fonts/newsreader-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/libre-franklin-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
${meta.preloadImage ? `<link rel="preload" as="image" href="${esc(meta.preloadImage)}" fetchpriority="high">` : ''}
<link rel="stylesheet" href="/assets/style.css?v=${ctx.assetHash}">
${ld.map(o => `<script type="application/ld+json">${json(o)}</script>`).join('\n')}
</head>
<body>
<a class="skip" href="#main">Zum Inhalt springen</a>
${header(site, ctx, meta)}
<main id="main">
${content}
</main>
${footer(site, ctx)}
<script src="/assets/app.js?v=${ctx.assetHash}" defer></script>
</body>
</html>`;
}

export function header(site, ctx, meta) {
  const nav = site.nav.map(n => `<a href="${esc(n.href)}"${meta.nav === n.href ? ' aria-current="page"' : ''}>${esc(n.label)}</a>`).join('');
  const orte = ctx.places.map(p => `<a href="${p.url}"${meta.ort === p.slug ? ' aria-current="page"' : ''}>${esc(p.name)}</a>`).join('');
  const eil = ctx.eilmeldung;
  const wa = site.social.whatsapp;
  return `
<div class="topbar"><div class="shell">
  <div class="date"><b>${esc(fmt.long(ctx.now))}</b><span class="stand">Redaktionsstand ${esc(fmt.date(ctx.stand))}, ${esc(fmt.time(ctx.stand))} Uhr</span></div>
  <div class="toplinks">
    <span class="svc"><a href="/jobs/">Stellenmarkt</a><a href="/menschen/">Familienanzeigen</a><a href="/termine/">Termine</a><a href="/sc-1919-merzenich/">SC 1919</a></span>
    ${wa ? `<a class="wa" href="${esc(wa)}" rel="noopener" target="_blank">WhatsApp-Kanal</a>` : `<a class="wa" href="/whatsapp/">WhatsApp</a>`}
    <a href="/meldung-senden/">Meldung senden</a>
    <a class="opt-hide" href="/werben/">Werben</a>
  </div>
</div></div>
<header class="masthead">
  <div class="shell mast-inner">
    <div class="mast-left"><b>Lokalzeitung online</b>${esc(site.region)}</div>
    <button class="menu-btn" type="button" data-menu aria-label="Menü öffnen" aria-expanded="false" aria-controls="drawer">☰</button>
    <a class="logo" href="/" aria-label="${esc(site.name)} – Startseite"><picture><source media="print" srcset="/assets/img/logo-on-light.png"><img src="${cdn('/assets/img/logo.png', 440, ctx.cfg)}" srcset="${cdn('/assets/img/logo.png', 440, ctx.cfg)} 440w, ${cdn('/assets/img/logo.png', 880, ctx.cfg)} 880w" sizes="220px" alt="${esc(site.name)}" width="600" height="169"></picture></a>
    <div class="mast-right">
      <form class="mast-search" action="/suche/" role="search"><input type="search" name="q" placeholder="Suchen und finden …" aria-label="Suchbegriff" autocomplete="off"><button type="submit" aria-label="Suchen">${ICONS.search}</button></form>
      <div class="mast-meta"><b><span class="mast-weather" data-weather-mini>Wetter Merzenich</span></b><span class="mast-date">${esc(fmt.long(ctx.now))}</span></div>
      <a class="search-btn" href="/suche/" aria-label="Suche">${ICONS.search}</a>
    </div>
  </div>
</header>
<div class="mast-line"><div class="shell"><div class="brandline" aria-hidden="true"><i></i><svg viewBox="0 0 240 48"><path d="M0 24 H118 L124 20 L130 29 L136 21 L144 4 L152 44 L158 25 L166 24 L174 24 L180 18 L186 30 L192 24 H240" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linejoin="round" stroke-linecap="round"/></svg></div></div></div>
<nav class="mainnav" aria-label="Ressorts"><div class="shell navrow">
  <div class="navscroll">${nav}</div>
  <a class="nav-search" href="/suche/">Suche</a>
</div></nav>
<nav class="districtbar" aria-label="Ortsteile"><div class="shell">
  <span class="lbl">Leben in</span>${orte}
</div></nav>
${eil ? `<div class="ticker"><div class="shell"><span class="tag">EILMELDUNG</span><a href="${eil.url}">${esc(eil.title)}</a><time datetime="${esc(isoLocal(eil.date))}">${esc(fmt.shortTime(eil.date))}</time></div></div>` : ''}
<div class="drawer" data-drawer id="drawer">
  <div class="scrim"></div>
  <div class="panel" role="dialog" aria-modal="true" aria-label="Menü">
    <div class="panel-top"><img src="${cdn('/assets/img/logo.png', 440, ctx.cfg)}" srcset="${cdn('/assets/img/logo.png', 440, ctx.cfg)} 440w, ${cdn('/assets/img/logo.png', 880, ctx.cfg)} 880w" sizes="220px" alt="${esc(site.name)}" width="600" height="169"><button type="button" data-close aria-label="Menü schließen">✕</button></div>
    <form class="drawer-search" action="/suche/" role="search"><input type="search" name="q" placeholder="Suchen …" aria-label="Suchbegriff"><button type="submit" aria-label="Suchen">${ICONS.search}</button></form>
    <div class="grp">Ressorts</div>${nav}
    <div class="grp">Leben in</div>${orte}
    <div class="grp">Service</div>
    <a href="/termine/">Termine &amp; Kalender</a><a href="/vereine/">Vereinsverzeichnis</a><a href="/betriebe/">Lokale Betriebe</a><a href="/jobs/">Jobs</a><a href="/service/">Notdienste &amp; Rathaus</a><a href="/whatsapp/">WhatsApp-Kanal</a><a href="/sc-1919-merzenich/">SC 1919 Merzenich</a><a href="/meldung-senden/">Meldung senden</a><a href="/werben/">Werben</a><a href="/archiv/">Archiv</a><a href="/ueber-uns/">Über uns</a>
  </div>
</div>`;
}

export function footer(site, ctx) {
  const nav = site.nav.map(n => `<li><a href="${esc(n.href)}">${esc(n.label)}</a></li>`).join('');
  const orte = ctx.places.map(p => `<li><a href="${p.url}">${esc(p.name)}</a></li>`).join('');
  return `
<footer>
  <!-- Die Wortmarke steht ueber dem Linkraster, nicht darin. In der ersten
       Rasterspalte fuellte sie nur 62 Prozent der Spaltenbreite und klebte ohne
       Schutzabstand an der Kante, wodurch sie schwerer wirkte als im Kopf,
       obwohl sie kleiner ist. Die goldene Pulslinie schliesst den Fuss nach
       oben so ab, wie sie den Kopf nach unten abschliesst. -->
  <div class="foot-top"><div class="shell">
    <img class="foot-logo" src="${cdn('/assets/img/logo.png', 400, ctx.cfg)}" srcset="${cdn('/assets/img/logo.png', 400, ctx.cfg)} 400w, ${cdn('/assets/img/logo.png', 800, ctx.cfg)} 800w" sizes="206px" alt="${esc(site.name)}" width="600" height="169" loading="lazy">
    <div class="brandline" aria-hidden="true"><i></i><svg viewBox="0 0 240 48"><path d="M0 24 H118 L124 20 L130 29 L136 21 L144 4 L152 44 L158 25 L166 24 L174 24 L180 18 L186 30 L192 24 H240" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linejoin="round" stroke-linecap="round"/></svg></div>
  </div></div>
  <div class="shell foot-grid">
    <div class="foot-brand">
      <p>${esc(site.claim)}. Jede Meldung nennt ihre Originalquelle, den Datenstand sowie Bildtyp und Bildcredit.</p>
      ${[
        site.social.whatsapp ? `<a href="${esc(site.social.whatsapp)}" rel="noopener" target="_blank">WhatsApp</a>` : '',
        site.social.facebook ? `<a href="${esc(site.social.facebook)}" rel="noopener" target="_blank">Facebook</a>` : '',
        site.social.instagram ? `<a href="${esc(site.social.instagram)}" rel="noopener" target="_blank">Instagram</a>` : '',
      ].filter(Boolean).join('') ? `<div class="foot-social">${[
        site.social.whatsapp ? `<a href="${esc(site.social.whatsapp)}" rel="noopener" target="_blank">WhatsApp</a>` : '',
        site.social.facebook ? `<a href="${esc(site.social.facebook)}" rel="noopener" target="_blank">Facebook</a>` : '',
        site.social.instagram ? `<a href="${esc(site.social.instagram)}" rel="noopener" target="_blank">Instagram</a>` : '',
      ].filter(Boolean).join('')}</div>` : ''}
    </div>
    <div><h3>Ressorts</h3><ul>${nav}</ul></div>
    <div><h3>Leben in</h3><ul>${orte}</ul></div>
    <div><h3>Service</h3><ul>
      <li><a href="/termine/">Termine</a></li>
      <li><a href="/vereine/">Vereine</a></li>
      <li><a href="/betriebe/">Lokale Betriebe</a></li>
      <li><a href="/jobs/">Jobs in Merzenich</a></li>
      <li><a href="/service/">Notdienste &amp; Rathaus</a></li>
      <li><a href="/sc-1919-merzenich/">SC 1919 Merzenich</a></li>
      <li><a href="/archiv/">Archiv</a></li>
      <li><a href="/suche/">Suche</a></li>
    </ul></div>
    <div><h3>Mitmachen</h3><ul>
      <li><a href="/meldung-senden/">Meldung senden</a></li>
      <li><a href="/termine/melden/">Termin melden</a></li>
      <li><a href="/whatsapp/">WhatsApp-Kanal</a></li>
      <li><a href="/werben/">Werben &amp; Mediadaten</a></li>
      <li><a href="/unterstuetzen/">Unterstützen</a></li>
    </ul></div>
    <div><h3>Redaktion</h3><ul>
      <li><a href="/ueber-uns/">Über uns &amp; Team</a></li>
      <li><a href="/grundsaetze/">Publizistische Grundsätze</a></li>
      <li><a href="/korrekturen/">Korrekturen</a></li>
      <li><a href="/impressum/">Impressum</a></li>
      <li><a href="/datenschutz/">Datenschutz</a></li>
    </ul></div>
  </div>
  <div class="shell foot-bottom">
    <div>© ${new Date(ctx.now).getFullYear()} ${esc(site.name)} · ${esc(site.region)}</div>
    <div><a href="/impressum/">Impressum</a> · <a href="/datenschutz/">Datenschutz</a> · <a href="/grundsaetze/">Grundsätze</a> · <a href="/feed.xml">RSS</a></div>
  </div>
  <div class="foot-seal" aria-hidden="true"></div>
</footer>`;
}

/* ---------- JSON-LD ---------- */
export function orgLd(site) {
  const sameAs = Object.values(site.social).filter(Boolean);
  return {
    '@context': 'https://schema.org', '@type': 'NewsMediaOrganization', '@id': site.url + '/#organization',
    name: site.name, alternateName: site.tagline, url: site.url + '/',
    logo: { '@type': 'ImageObject', url: site.url + '/assets/img/logo-on-light.png', width: 1200, height: 338 },
    image: site.url + '/assets/img/og-default.jpg',
    description: site.claim, foundingDate: site.foundingDate, email: site.email,
    areaServed: { '@type': 'AdministrativeArea', name: 'Gemeinde Merzenich, Kreis Düren, Nordrhein-Westfalen' },
    ...(sameAs.length ? { sameAs } : {}),
    publishingPrinciples: site.url + '/grundsaetze/',
    correctionsPolicy: site.url + '/korrekturen/',
    ethicsPolicy: site.url + '/grundsaetze/#ethik',
    actionableFeedbackPolicy: site.url + '/meldung-senden/',
    masthead: site.url + '/ueber-uns/',
    ownershipFundingInfo: site.url + '/ueber-uns/#finanzierung',
    diversityPolicy: site.url + '/grundsaetze/#vielfalt'
  };
}
export function websiteLd(site) {
  return {
    '@context': 'https://schema.org', '@type': 'WebSite', '@id': site.url + '/#website',
    name: site.name, url: site.url + '/', inLanguage: 'de-DE',
    publisher: { '@id': site.url + '/#organization' },
    potentialAction: { '@type': 'SearchAction', target: { '@type': 'EntryPoint', urlTemplate: site.url + '/suche/?q={search_term_string}' }, 'query-input': 'required name=search_term_string' }
  };
}
export function breadcrumbLd(site, crumbs) {
  return {
    '@context': 'https://schema.org', '@type': 'BreadcrumbList',
    itemListElement: crumbs.map((c, i) => ({ '@type': 'ListItem', position: i + 1, name: c.name, ...(c.url ? { item: absolute(site, c.url) } : {}) }))
  };
}
