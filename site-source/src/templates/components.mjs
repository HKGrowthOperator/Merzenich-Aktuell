import { esc, fmt, isoLocal, truncate } from '../lib/util.mjs';
import { img, imageTypeLabel } from './media.mjs';
import { PULSE, ICONS } from './layout.mjs';
export const BRANDLINE = `<div class="brandline thin" aria-hidden="true"><i></i><svg viewBox="0 0 240 48"><path d="M0 24 H118 L124 20 L130 29 L136 21 L144 4 L152 44 L158 25 L166 24 L174 24 L180 18 L186 30 L192 24 H240" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linejoin="round" stroke-linecap="round"/></svg></div>`;

export function kicker(a, ctx) {
  const r = ctx.site.ressorts[a.ressort];
  const label = a.kicker || (r ? r.name : a.ressort);
  // Ortszeile wie im ausgelieferten Stand: die Gemeinde in Versalien voran, der
  // Ortsteil dahinter. Frueher stand der Ort klein im Ressortlabel; das las sich
  // wie ein Zusatz, nicht wie eine Ortsmarke.
  return `${locationLine(a, ctx)}<span class="kicker">${esc(label)}</span>`;
}

export function locationLine(a, ctx) {
  const gemeinde = (ctx.site.shortName || 'Merzenich').toUpperCase();
  const ort = ctx.placeName(a.ort);
  const teil = ort && ort.toUpperCase() !== gemeinde ? ` · ${esc(ort.toUpperCase())}` : '';
  return `<div class="location-line"><span class="location-brand">${esc(gemeinde)}</span>${teil}</div>`;
}

export function formatBadge(a) {
  const map = { familienanzeige: 'Familienanzeige', nachruf: 'Nachruf', eilmeldung: 'Eilmeldung', interview: 'Interview', kommentar: 'Kommentar', kolumne: 'Kolumne', tipps: 'Tipps', hintergrund: 'Hintergrund', anzeige: 'Anzeige', liveticker: 'Liveticker', kurz: 'Kurz notiert', bericht: '' , meldung: '' };
  const l = map[a.format] || '';
  return l ? `<span class="fbadge ${esc(a.format)}">${esc(l)}</span>` : '';
}

export function timeEl(d, cls = '', a) { if (a && a.undated) return `<span class="undated" title="Die Quelle nennt kein Veröffentlichungsdatum">Ohne Datum · abgerufen ${esc(fmt.date(a.retrieved || a.date))}</span>`; return `<time datetime="${esc(isoLocal(d))}"${cls ? ` class="${cls}"` : ''}>${esc(fmt.dateTime(d))}</time>`; }
export function timeShort(d, a) { if (a && a.undated) return `<span class="undated">o. D.</span>`; return `<time datetime="${esc(isoLocal(d))}">${esc(fmt.shortTime(d))}</time>`; }

/* Gesetzte Ersatzflaeche fuer Meldungen ohne eigenes Foto. Es wird kein
   Ereignisfoto erfunden (Regel 6), die Leerstelle wird nur gestaltet. */
/* Seitenspalte auf Einsendeseiten. Dort waere der Kasten "Sie wissen etwas?"
   eine Aufforderung zu genau der Handlung, auf deren Seite der Leser schon
   steht. Stattdessen beantwortet die Spalte die Frage, die beim Absenden
   tatsaechlich offen ist: was passiert jetzt damit. */
export function ablaufBox() {
  const schritte = [
    ['Eingang', 'Ihre Einsendung landet direkt in der Redaktion. Eine Eingangsbestätigung kommt per E-Mail.'],
    ['Prüfung', 'Wir gleichen jede Angabe mit der Originalquelle ab und klären Rückfragen mit Ihnen.'],
    ['Veröffentlichung', 'Nach der Freigabe erscheint die Meldung mit Quellenangabe, Bildtyp und Bildcredit.'],
  ];
  return `<div class="sidebox ablauf"><h3>Was danach passiert</h3><ol class="ablauf-list">` +
    schritte.map(([t, txt], i) => `<li><span class="ablauf-n">${i + 1}</span><div><b>${esc(t)}</b><p>${esc(txt)}</p></div></li>`).join('') +
    `</ol></div>`;
}

export function textcard(ressort, quelle) {
  return `<div class="textcard">` +
    `<span class="tc-ressort">${esc(ressort)}</span>` +
    `<span class="tc-rule" aria-hidden="true"></span>` +
    (quelle ? `<span class="tc-quelle">${esc(quelle)}</span>` : '') +
    `<span class="tc-hint">kein Foto zum Ereignis</span>` +
    `</div>`;
}

export function media(a, opts, ctx) {
  const im = a.image;
  const src = im && im.src;
  const badge = im && im.type ? `<span class="badge">${esc(imageTypeLabel(im.type))}</span>` : '';
  const contain = im && im.fit === 'contain' ? ' contain' : '';
  // Ohne eigenes Bild wird keine Flaeche schwarz gelassen und auch kein Ersatzfoto
  // erfunden (Regel 6), sondern eine gesetzte Textkachel ausgegeben: Ressort in der
  // Lesetype, Haarlinie, Quelle in Kapitaelchen, unten der Hinweis auf das fehlende Foto.
  const ressort = (ctx.site.ressorts[a.ressort] && ctx.site.ressorts[a.ressort].name) || 'Meldung';
  const quelle = (a.sources && a.sources[0] && a.sources[0].name) || '';
  const daten = ` data-ressort="${esc(ressort)}" data-quelle="${esc(quelle)}"`;
  if (!src) return `<div class="media none"${daten}>${textcard(ressort, quelle)}</div>`;
  // Die Datenattribute bleiben auch bei vorhandenem Bild stehen: bricht die
  // externe Quelle weg, baut app.js daraus dieselbe Kachel, statt die schwarze
  // Platzhaltergrafik einzublenden.
  return `<div class="media${contain}"${daten}>${img(src, { alt: (im && im.alt) || a.title, kind: a.ressort, ...opts }, ctx.cfg)}${badge}</div>`;
}

export function card(a, ctx, opts = {}) {
  return `<article class="news-card${a.sponsored ? ' sponsored' : ''}">
  <a href="${esc(a.url)}" tabindex="-1" aria-hidden="true">${media(a, { sizes: '(max-width: 640px) 100vw, 400px', priority: !!opts.priority }, ctx)}</a>
  <div class="news-card-body">
    ${kicker(a, ctx)}
    <h3><a href="${esc(a.url)}">${esc(a.title)}</a></h3>
    <p class="dek">${esc(truncate(a.teaser, 150))}</p>
    <div class="meta">${timeEl(a.date, '', a)}${formatBadge(a)}</div><div class="story-actions"><a class="read-more" href="${esc(a.url)}">Mehr lesen<span class="sr-only">: ${esc(a.title)}</span></a></div>
  </div>
</article>`;
}

export function feedRow(a, ctx) {
  const src = a.sources && a.sources[0];
  return `<article class="feed-row${a.sponsored ? ' sponsored' : ''}">
  <a class="feed-img" href="${esc(a.url)}" tabindex="-1" aria-hidden="true">${media(a, { sizes: '(max-width: 640px) 100vw, 260px' }, ctx)}</a>
  <div class="feed-copy">
    ${kicker(a, ctx)}
    <h3><a href="${esc(a.url)}">${esc(a.title)}</a></h3>
    <p class="dek">${esc(truncate(a.teaser, 190))}</p>
    <div class="meta">${timeEl(a.date, '', a)}<span class="readtime">${a.readingMinutes} Min.</span>${formatBadge(a)}</div>
    <div class="creditline"><span>${a.image && a.image.type ? esc(imageTypeLabel(a.image.type)) + ' · ' : ''}${esc((a.image && a.image.credit) || '')}</span>${src ? `<span class="src">Quelle: ${esc(src.title)}</span>` : ''}</div>
  </div>
</article>`;
}

export function sideCard(a, ctx, opts = {}) {
  return `<article class="side-card">
  <a href="${a.url}" tabindex="-1" aria-hidden="true">${media(a, { sizes: '(max-width: 640px) 100vw, 372px', ...opts }, ctx)}</a>
  ${kicker(a, ctx)}
  <h3><a href="${a.url}">${esc(a.title)}</a></h3>
  <p>${esc(truncate(a.teaser, 120))}</p>
  <div class="meta">${timeShort(a.date, a)}</div>
</article>`;
}

export function listItem(a, ctx) {
  return `<li class="li-story"><span class="kicker">${esc(ctx.site.ressorts[a.ressort]?.name || a.ressort)}</span><a href="${a.url}">${esc(a.title)}</a><span class="meta">${timeShort(a.date, a)}</span></li>`;
}

export function kurzItem(a, ctx) {
  return `<li class="kurz"><span class="k-time">${a.undated ? 'o. D.' : esc(fmt.wd(a.date)) + ' ' + esc(fmt.time(a.date))}</span><div><span class="kicker">${esc(ctx.placeName(a.ort) || ctx.site.ressorts[a.ressort]?.name || '')}</span><a href="${a.url}">${esc(a.title)}</a></div></li>`;
}

export function photoStory(a, ctx, small = false) {
  return `<a class="photo-story${small ? ' small' : ''}" href="${a.url}">${media(a, { sizes: small ? '(max-width: 640px) 100vw, 320px' : '(max-width: 640px) 100vw, 620px' }, ctx)}<span class="over"><span class="eyebrow">${a.undated ? 'o. D.' : esc(fmt.short(a.date))} · ${esc(ctx.placeName(a.ort) || 'Gemeinde')}</span><h3>${esc(a.title)}</h3>${small ? '' : `<p>${esc(truncate(a.teaser, 130))}</p>`}<span class="photo-credit">${esc((a.image && a.image.credit) || '')}</span></span></a>`;
}

export function sectionHead(eyebrow, title, more, moreLabel = 'Alle', opts = {}) {
  // Dachzeile und Rubriktitel stehen in EINER Zeile. Gestapelt waren es zwei
  // Etiketten uebereinander, und der Titel lief mit 24px/800 groesser und
  // schwerer als die Schlagzeilen, die er ankuendigt. Als gesperrte Rubrikzeile
  // ist er Moebel, und die Schlagzeilen sind wieder das Lauteste im Block.
  const tag = opts.h || 'h2';
  return `<div class="section-head"><${tag} class="sec-rule">${eyebrow ? `<span class="sec-eyebrow">${esc(eyebrow)}</span>` : ''}<span class="sec-title">${esc(title)}</span></${tag}>${more ? `<a class="more" href="${more}">${esc(moreLabel)}</a>` : ''}</div>${opts.pulse === false ? '' : BRANDLINE}`;
}

/* ---------- Termine ---------- */
export function dateBox(d) { return `<span class="d"><b>${esc(fmt.day(d))}</b><span>${esc(fmt.mon3(d))}</span></span>`; }

export function terminLi(e) {
  return `<li class="termin">${dateBox(e.start)}<span class="t"><a href="${e.url}">${esc(e.title)}</a><small>${fmt.time(e.start) === '00:00' ? 'ganztägig' : esc(fmt.time(e.start)) + ' Uhr'} · ${esc(e.location || '')}</small></span></li>`;
}

export function eventCard(e, ctx) {
  const im = e.image && e.image.src;
  const box = `<span class="event-date"><b>${esc(fmt.day(e.start))}</b><span>${esc(fmt.mon3(e.start))}</span></span>`;
  return `<article class="event-card${im ? '' : ' noimg'}">${im ? `<a href="${e.url}" tabindex="-1" aria-hidden="true"><div class="ev-media"><div class="media">${img(im, { alt: (e.image && e.image.alt) || e.title, kind: 'termine', sizes: '(max-width: 640px) 100vw, 300px' }, ctx.cfg)}</div>${box}</div></a>` : ''}
  <div class="event-body">${im ? '' : box}
    <span class="eyebrow"><time datetime="${esc(isoLocal(e.start))}">${esc(fmt.wd(e.start))}<span class="sr-only">, ${esc(fmt.date(e.start))}</span></time> · ${fmt.time(e.start) === '00:00' ? 'ganztägig' : esc(fmt.time(e.start)) + ' Uhr'} · ${esc(e.category || 'Termin')}</span>
    <h3><a href="${e.url}">${esc(e.title)}</a></h3>
    <p>${esc(e.location || '')}${e.ort ? ` · ${esc(ctx.placeName(e.ort))}` : ''}</p>
  </div>
</article>`;
}

export function eventRow(e, ctx, h = 'h3') {
  return `<article class="event-row" id="${esc(e.slug)}">
  ${dateBox(e.start)}
  <div class="info">
    <span class="eyebrow">${esc(fmt.wdLong(e.start))} · ${esc(e.category || 'Termin')}${e.ort ? ` · ${esc(ctx.placeName(e.ort))}` : ''}</span>
    <${h}><a href="${e.url}">${esc(e.title)}</a></${h}>
    <div class="meta"><time datetime="${esc(isoLocal(e.start))}">${fmt.time(e.start) === '00:00' ? (e.end ? `bis ${esc(fmt.date(e.end))}` : 'ganztägig') : `${esc(fmt.time(e.start))} Uhr${e.end ? ` bis ${esc(fmt.time(e.end))} Uhr` : ''}`}</time>${e.location ? `<span>${esc(e.location)}</span>` : ''}${e.organizer ? `<span>${esc(e.organizer)}</span>` : ''}</div>
    ${e.teaser ? `<p class="ev-desc">${esc(e.teaser)}</p>` : ''}
  </div>
  <div class="act">
    <a href="${e.url}">Details</a>
    <a href="${e.url}termin.ics" download>Kalender</a>
    ${e.source ? `<a href="${esc(e.source)}" target="_blank" rel="noopener nofollow">Quelle ↗</a>` : ''}
  </div>
</article>`;
}

/* ---------- Sidebar-Boxen ---------- */
export function sidebox(title, inner, opts = {}) {
  return `<div class="sidebox${opts.cls ? ' ' + opts.cls : ''}"${opts.attrs || ''}><h3>${esc(title)}${opts.more ? `<a href="${opts.more}">${esc(opts.moreLabel || 'alle')}</a>` : ''}</h3>${inner}</div>`;
}
export function weatherBox() { return sidebox('Wetter in Merzenich', `<div class="weather" data-weather><p class="weather-skel">Wetterdaten werden geladen.</p></div>`); }
export function monthGrid(events, now, today = now) {
  const y = now.getFullYear(), m = now.getMonth();
  const monthName = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'][m];
  const first = new Date(y, m, 1), days = new Date(y, m + 1, 0).getDate();
  const lead = (first.getDay() + 6) % 7;
  const key = d => `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
  const byDay = new Map();
  for (const e of events) { const k = isoLocal(e.start).slice(0, 10); if (!byDay.has(k)) byDay.set(k, []); byDay.get(k).push(e); }
  const todayKey = isoLocal(today).slice(0, 10);
  const cells = [];
  for (let i = 0; i < lead; i++) cells.push('<span></span>');
  for (let d = 1; d <= days; d++) {
    const k = key(d), es = byDay.get(k);
    if (es && es.length) {
      const label = es.map(x => x.title).join(', ');
      cells.push(`<a class="has${k === todayKey ? ' today' : ''}" href="/termine/#${esc(es[0].slug)}" title="${esc(label)}" aria-label="${d}. ${esc(monthName)}: ${esc(label)}">${d}</a>`);
    } else {
      cells.push(`<span${k === todayKey ? ' class="today"' : ''}>${d}</span>`);
    }
  }
  return `<div class="mcal" role="group" aria-label="Kalender ${esc(fmt.month(now))}"><div class="mcal-head"><b>${esc(monthName)} ${y}</b><span>Tage mit Termin sind markiert</span></div><div class="mcal-grid">${['Mo','Di','Mi','Do','Fr','Sa','So'].map(d => `<i>${d}</i>`).join('')}${cells.join('')}</div></div>`;
}
export function terminBox(events, now) {
  let cal = '';
  if (now) {
    cal = monthGrid(events, now);
    const nx = new Date(now.getFullYear(), now.getMonth() + 1, 1);
    if (events.some(e => fmt.ym(e.start) === fmt.ym(nx))) cal += `<details class="mcal-next"><summary>${esc(fmt.month(nx))}</summary>${monthGrid(events, nx, now)}</details>`;
  }
  return sidebox('Nächste Termine', `${cal}<ul>${events.slice(0, 5).map(terminLi).join('')}</ul>${events.length ? '' : '<p class="empty">Aktuell sind keine Termine eingetragen. <a href="/termine/melden/">Termin melden</a></p>'}`, { more: '/termine/' });
}
export function rankedBox(title, articles, more) { return sidebox(title, `<ol class="ranked">${articles.map(a => `<li><a href="${a.url}">${esc(a.title)}</a></li>`).join('')}</ol>`, { more }); }
export function serviceBox(site) {
  return sidebox('Merzenich heute', `<ul class="service">${site.notdienste.map(n => `<li><span class="k">${esc(n.k)}</span><span class="v">${n.tel ? `<a href="tel:${esc(n.tel)}">${esc(n.v)}</a>` : n.url ? `<a href="${esc(n.url)}" target="_blank" rel="noopener">${esc(n.v)}</a>` : esc(n.v)}</span></li>`).join('')}<li><span class="k">${esc(site.rathaus.name)}</span><span class="v">${esc(site.rathaus.street)}, ${esc(site.rathaus.zip)} ${esc(site.rathaus.city)}</span></li><li><span class="k">Telefon Rathaus</span><span class="v"><a href="tel:${esc(site.rathaus.phone.replace(/[^\d+]/g, ''))}">${esc(site.rathaus.phone)}</a></span></li></ul>`, { more: '/service/', moreLabel: 'alle Service-Infos' });
}
export function tipBox(site) {
  return `<div class="sidebox dark"><h3>Sie wissen etwas?</h3><p>Vereinsmeldung, Termin, Foto vom Fest, Hinweis auf einen Einsatz: Die Redaktion prüft jede Einsendung und veröffentlicht mit Quelle.</p><a class="btn gold block" href="/meldung-senden/">Meldung senden</a><a class="btn ghost block on-dark" href="/termine/melden/">Termin melden</a></div>`;
}
export function whatsappBox(site) {
  const wa = site.social.whatsapp;
  return `<div class="sidebox wa-box"><h3>WhatsApp-Kanal</h3><p>Eilmeldungen, Blaulicht und die wichtigsten Termine direkt aufs Handy. Kein Gruppenchat, keine Nummer sichtbar.</p><a class="btn wa-btn block" href="${wa ? esc(wa) : '/whatsapp/'}"${wa ? ' target="_blank" rel="noopener"' : ''}>${ICONS.whatsapp} Kanal abonnieren</a></div>`;
}
export function pollBox(poll) {
  if (!poll) return '';
  return `<div class="sidebox poll" data-poll="${esc(poll.id)}"><h3>Ihre Meinung</h3><p class="q">${esc(poll.question)}</p>${poll.options.map(o => `<button class="opt" type="button"><span class="bar"></span><span>${esc(o)}<span class="pct"></span></span></button>`).join('')}<small>Eine Stimme pro Browser. Die Auswertung erscheint sofort.</small></div>`;
}
export function nativeAd(site) {
  const banners = (site.ads && site.ads.enabled && site.ads.banners) || [];
  if (!banners.length) return '';
  const b = banners[0];
  return `<article class="feed-row native-ad"><a class="feed-img" href="${b.url ? esc(b.url) : '/werben/'}"${b.url ? ' target="_blank" rel="noopener sponsored"' : ''} tabindex="-1" aria-hidden="true"><div class="media none ad-media">${b.image ? `<img src="${esc(b.image)}" alt="${esc(b.alt || b.name)}" loading="lazy" width="728" height="90">` : `<span class="ad-name">${esc(b.name)}<small>Merzenich · Kreis Düren</small></span>`}</div></a><div class="feed-copy"><span class="kicker">Anzeige<span class="dist">${esc(b.name)}</span></span><h3><a href="${b.url ? esc(b.url) : '/werben/'}"${b.url ? ' target="_blank" rel="noopener sponsored"' : ''}>${esc(b.headline || b.name)}</a></h3><p class="dek">${esc(b.text || 'Partner von Merzenich Aktuell. Gekennzeichnete Anzeige, ohne Einfluss auf die Redaktion.')}</p><div class="meta"><a href="/werben/">Hier werben</a></div></div></article>`;
}
export function adSlot(name, site) {
  const banners = (site.ads && site.ads.enabled && site.ads.banners) || [];
  if (!banners.length) return '';
  const wide = /home|in_article|content|billboard/.test(name);
  return `<div class="ad-row ${wide ? 'wide' : 'narrow'}" data-slot="${esc(name)}">${banners.map(b => `<div class="ad"><span class="ad-label">Anzeige</span>${b.url ? `<a href="${esc(b.url)}" target="_blank" rel="noopener sponsored">` : '<div class="ad-in">'}${b.image ? `<img src="${esc(b.image)}" alt="${esc(b.alt || b.name)}" loading="lazy" width="${wide ? 728 : 300}" height="${wide ? 90 : 104}">` : `<span class="ad-name">${esc(b.name)}<small>Merzenich · Kreis Düren</small></span>`}${b.url ? '</a>' : '</div>'}</div>`).join('')}</div>`;
}

export function placesGrid(ctx) {
  return `<div class="district-grid">${ctx.places.map(p => {
    const n = ctx.countByOrt(p.slug);
    return `<a class="district-tile" href="${p.url}"><h3>${esc(p.name)}</h3><p>${esc(truncate(p.description, 110))}</p><p class="n">${n === 1 ? '1 Meldung' : n + ' Meldungen'}${p.einwohner ? ` · ${esc(p.einwohner)} Einwohner` : ''}</p></a>`;
  }).join('')}</div>`;
}

export function crumbs(items) {
  return `<nav class="crumbs" aria-label="Brotkrumen">${items.map((c, i) => i < items.length - 1 ? `<a href="${c.url}">${esc(c.name)}</a><span class="sep">›</span>` : `<span aria-current="page">${esc(c.name)}</span>`).join('')}</nav>`;
}

export function pagination(base, page, pages) {
  if (pages <= 1) return '';
  const link = n => n === 1 ? base : `${base}seite/${n}/`;
  let out = '<nav class="pagination" aria-label="Seiten">';
  if (page > 1) out += `<a href="${link(page - 1)}" rel="prev">← Neuer</a>`;
  out += `<span>Seite ${page} von ${pages}</span>`;
  if (page < pages) out += `<a href="${link(page + 1)}" rel="next">Älter →</a>`;
  return out + '</nav>';
}

export function pageHead(eyebrow, title, desc, extra = '', crumbItems = null) {
  return `<div class="page-head"><div class="shell">${crumbItems ? crumbs(crumbItems) : ''}<span class="eyebrow">${esc(eyebrow)}</span><h1>${esc(title)}</h1>${desc ? `<p class="desc">${esc(desc)}</p>` : ''}${extra}</div></div>`;
}

export function clubCard(c, ctx) {
  return `<article class="club-card"><span class="cat">${esc(c.category || 'Verein')} · ${esc(ctx.placeName(c.ort) || 'Gemeinde')}</span><h3><a href="${c.url}">${esc(c.name)}</a></h3><p>${esc(truncate(c.description, 140))}</p><div class="club-links"><a href="${c.url}">Profil</a>${c.website ? `<a class="ext" href="${esc(c.website)}" target="_blank" rel="noopener">Website ↗</a>` : ''}</div></article>`;
}
export function businessCard(b, ctx) {
  return `<article class="club-card biz${b.sponsored ? ' sponsored' : ''}"><span class="cat">${esc(b.category || 'Betrieb')} · ${esc(ctx.placeName(b.ort) || 'Gemeinde')}</span><h3><a href="${b.url}">${esc(b.name)}</a></h3><p>${esc(truncate(b.description, 140))}</p><div class="club-links"><a href="${b.url}">Profil</a>${b.website ? `<a class="ext" href="${esc(b.website)}" target="_blank" rel="noopener${b.sponsored ? ' sponsored' : ''}">Website ↗</a>` : ''}${b.phone ? `<a href="tel:${esc(b.phone.replace(/[^\d+]/g, ''))}">${esc(b.phone)}</a>` : ''}</div>${b.sponsored ? '<span class="sp-tag">Anzeige</span>' : ''}</article>`;
}

export function shareBar(a, site) {
  const u = encodeURIComponent(site.url + a.url), t = encodeURIComponent(a.title);
  return `<div class="share" role="group" aria-label="Teilen">
    <a href="https://api.whatsapp.com/send?text=${t}%20${u}" target="_blank" rel="noopener" aria-label="Per WhatsApp teilen" class="wa">${ICONS.whatsapp}</a>
    <a href="https://www.facebook.com/sharer/sharer.php?u=${u}" target="_blank" rel="noopener" aria-label="Auf Facebook teilen">${ICONS.facebook}</a>
    <a href="mailto:?subject=${t}&body=${u}" aria-label="Per E-Mail teilen">${ICONS.mail}</a>
    <button type="button" data-share aria-label="Link kopieren">${ICONS.link}</button>
    <span class="sr-only" role="status" data-share-status></span>
    <button type="button" data-print aria-label="Drucken">${ICONS.print}</button>
  </div>`;
}

export function ressortBlock(key, ctx, vergeben) {
  const r = ctx.site.ressorts[key]; if (!r) return '';
  const alle = ctx.articles.filter(a => a.ressort === key);
  if (!alle.length) return '';
  // Die Zahl neben der Ressortzeile nennt weiter den vollen Bestand, angezeigt
  // werden aber nur Meldungen, die weiter oben auf der Seite noch nicht standen.
  const items = vergeben ? alle.filter(a => !vergeben.has(a)) : alle;
  if (!items.length) return '';
  const [lead, ...rest] = items;
  if (vergeben) { vergeben.add(lead); rest.slice(0, 4).forEach(a => vergeben.add(a)); }
  return `<section class="rblock"><h3 class="rblock-h"><a href="/${key}/">${esc(r.name)}</a><span>${alle.length} ${alle.length === 1 ? 'Meldung' : 'Meldungen'}</span></h3>
  <article class="rblock-lead"><a href="${lead.url}" tabindex="-1" aria-hidden="true">${media(lead, { sizes: '(max-width: 640px) 100vw, 400px' }, ctx)}</a><h4><a href="${lead.url}">${esc(lead.title)}</a></h4><p>${esc(truncate(lead.teaser, 110))}</p><div class="meta">${timeShort(lead.date, lead)}</div></article>
  <ul class="rblock-list">${rest.slice(0, 4).map(a => `<li><a href="${a.url}">${esc(a.title)}</a><span class="meta">${timeShort(a.date, a)}</span></li>`).join('')}</ul>
  <a class="more" href="/${key}/">Alle ${esc(r.name)}-Meldungen</a></section>`;
}
