import { esc, fmt, isoLocal, truncate, stripHtml, json, toDate } from '../lib/util.mjs';
import { img, imageTypeLabel } from './media.mjs';
import { layout, PULSE, ICONS, orgLd } from './layout.mjs';
import * as C from './components.mjs';

const abs = (site, p) => (p && p.startsWith('http')) ? p : site.url + (p || '');

/* ============================================================ Startseite */
export function homePage(ctx) {
  const { site, articles, events } = ctx;

  // Jeder Block zieht aus demselben Bestand. Ohne Register zeigte die Startseite
  // 14 Schlagzeilen doppelt, waehrend 17 Meldungen gar nicht vorkamen: der
  // Aufmacher stand noch einmal in der Kurzleiste und ein drittes Mal als Karte
  // im Sportblock. Das Register vergibt jede Meldung genau einmal, in der
  // Reihenfolge, in der die Bloecke auf der Seite stehen. Enge Filter (Kurz,
  // Blaulicht, Ressorts) kommen vor dem breiten Nachrichtenstrom an die Reihe,
  // damit ihnen der Strom nicht die Auswahl wegnimmt.
  const vergeben = new Set();
  const nimm = (anzahl, passt = () => true) => {
    const treffer = [];
    for (const a of articles) {
      if (treffer.length >= anzahl) break;
      if (vergeben.has(a) || !passt(a)) continue;
      treffer.push(a);
      vergeben.add(a);
    }
    return treffer;
  };
  // Bildlose Meldungen bekommen eine gesetzte Textkachel statt einer leeren
  // Flaeche. In den grossen Bildplaetzen oben rechts standen dadurch aber zwei
  // dieser Kacheln uebereinander, und die Startseite eroeffnete mit zwei
  // Ersatzflaechen. Wo ein Bild traegt, kommen bebilderte Meldungen zuerst;
  // reichen sie nicht, wird wie bisher aufgefuellt. Die Reihenfolge der
  // Meldungen untereinander bleibt sonst unveraendert.
  const hatBild = (a) => !!(a.image && a.image.src);
  const nimmBebildert = (anzahl, passt = () => true) => {
    const treffer = nimm(anzahl, (a) => passt(a) && hatBild(a));
    if (treffer.length < anzahl) treffer.push(...nimm(anzahl - treffer.length, passt));
    return treffer;
  };

  const lead = articles.find(a => a.featured) || articles[0];
  vergeben.add(lead);

  // Zwei Nebenmeldungen, nicht drei: mit drei lief die Nebenspalte fast doppelt
  // so hoch wie die Aufmacherspalte und hinterliess rund 640 px Leerraum unter
  // dem Aufmacher. Die WordPress-Fassung nutzt an dieser Stelle ohnehin zwei.
  const side = nimmBebildert(2);
  const quick = nimm(4);
  const kurz = nimm(6, a => a.format === 'kurz');
  const feed = nimm(12);
  const blaulicht = nimm(3, a => a.ressort === 'blaulicht');
  const sport = nimm(3, a => ['sport', 'vereine'].includes(a.ressort));
  const leben = nimm(3, a => ['leben', 'menschen', 'wirtschaft', 'rathaus'].includes(a.ressort));
  const tipps = nimm(5, a => a.featured);
  const businesses = ctx.businesses.slice(0, 4);

  const content = `
<section class="hero"><div class="shell hero-grid">
  <a class="lead-card" href="${lead.url}">
    ${C.media(lead, { priority: true, sizes: '(max-width: 640px) 100vw, (max-width: 1080px) 65vw, 800px' }, ctx)}
    <div class="lead-copy">${C.kicker(lead, ctx)}<h1>${esc(lead.title)}</h1><p class="dek">${esc(lead.teaser)}</p><div class="meta">${C.timeEl(lead.date, '', lead)}<span class="readtime">${lead.readingMinutes} Min. Lesezeit</span>${C.formatBadge(lead)}</div></div>
  </a>
  <div class="side-stack"><h2 class="sr-only">Weitere Aufmacher</h2>${side.map((a, i) => C.sideCard(a, ctx, { eager: i === 0 })).join('')}</div>
</div></section>

<section class="quickline"><div class="shell"><span class="quick-label">Kurz gemeldet</span>${quick.map(a => `<a class="quick-item" href="${a.url}"><b>${esc(a.kicker || site.ressorts[a.ressort]?.name || '')}</b><span>${esc(a.title)}</span></a>`).join('')}</div></section>

<div class="shell">${C.adSlot('billboard', site)}</div>

${kurz.length ? `<section class="section kurz-section"><div class="shell">
  ${C.sectionHead('Kurz notiert', 'Leben in der Gemeinde', '/nachrichten/', 'Alle Meldungen')}
  <ul class="kurz-list">${kurz.map(a => C.kurzItem(a, ctx)).join('')}</ul>
</div></section>` : ''}

<section class="section"><div class="shell">
  ${C.sectionHead('Chronologisch', 'Neu aus der Gemeinde', '/nachrichten/', 'Alle Meldungen')}
  <div class="content-grid">
    <div class="feed">${feed.slice(0, 4).map(a => C.feedRow(a, ctx)).join('')}${feed.length > 4 ? C.nativeAd(site) : ''}${feed.slice(4).map(a => C.feedRow(a, ctx)).join('')}${feed.length ? `<a class="btn ghost block feed-more" href="/nachrichten/">Ältere Meldungen im Archiv</a>` : ''}</div>
    <aside class="sidebar">
      ${C.weatherBox()}
      ${fussballBox(ctx)}
      ${C.terminBox(events, ctx.now)}
      ${C.whatsappBox(site)}
      ${tipps.length ? C.rankedBox('Lesetipps der Redaktion', tipps) : ''}
      ${C.pollBox(ctx.poll)}
      ${C.serviceBox(site)}
      ${C.tipBox(site)}
      ${C.adSlot('sidebar', site)}
    </aside>
  </div>
</div></section>

${blaulicht.length ? `<section class="section dark"><div class="shell">
  ${C.sectionHead('Blaulicht', 'Einsätze mit Originalbildern', '/blaulicht/', 'Alle Einsätze')}
  <div class="fire-grid">${blaulicht.map((a, i) => C.photoStory(a, ctx, i > 0)).join('')}</div>
</div></section>` : ''}

<section class="section"><div class="shell">
  ${C.sectionHead('Kalender', 'Was als Nächstes ansteht', '/termine/', 'Alle Termine')}
  ${events.length ? `<div class="events-grid">${events.slice(0, 4).map(e => C.eventCard(e, ctx)).join('')}</div>` : '<p class="empty">Noch keine Termine eingetragen.</p>'}
  <p class="section-note">Vereine und Veranstalter tragen ihre Termine selbst ein: <a href="/termine/melden/">Termin melden</a>. Jeder Termin ist als Kalenderdatei abrufbar.</p>
</div></section>

<div class="shell">${C.adSlot('home_mid', site)}</div>

${sport.length ? `<section class="section"><div class="shell">
  ${C.sectionHead('Sport & Vereine', 'Aus dem Vereinsleben', '/vereine/', 'Vereinsverzeichnis')}
  <div class="cards-3">${sport.map(a => C.card(a, ctx)).join('')}</div>
</div></section>` : ''}

<section class="section"><div class="shell">
  ${C.sectionHead('Ressorts', 'Rathaus, Wirtschaft, Leben', '/nachrichten/', 'Alle Meldungen')}
  <div class="rblock-grid">${['rathaus', 'wirtschaft', 'leben'].map(k => C.ressortBlock(k, ctx, vergeben)).join('')}</div>
</div></section>

  <section class="section markt"><div class="shell">
    <div class="markt-grid">
      <div class="markt-col">${C.sectionHead('Anzeigenmarkt', 'Stellenanzeigen', '/jobs/', 'Alle Stellen')}
        ${ctx.jobs.length ? ctx.jobs.slice(0, 3).map(j => `<article class="markt-card"><span class="eyebrow">${esc(j.betrieb)}${j.ort ? ' · ' + esc(ctx.placeName(j.ort)) : ''}</span><h3><a href="${j.url}">${esc(j.title)}</a></h3><p>${esc(truncate(j.description, 120))}</p><div class="meta"><span>${esc(fmt.date(j.posted))}</span><a class="btn-sm" href="${j.url}">Zur Anzeige</a></div></article>`).join('') : '<div class="markt-empty"><p>Betriebe, Gemeinde, Kitas und Vereine aus Merzenich inserieren hier. Ehrenamt und gemeinnützige Stellen sind kostenlos.</p><a class="btn-sm" href="/werben/">Stelle inserieren</a></div>'}
      </div>
      <div class="markt-col">${C.sectionHead('Anzeigenmarkt', 'Familienanzeigen', '/menschen/', 'Ressort Menschen')}
        ${(() => { const fam = nimm(3, a => ['familienanzeige', 'nachruf'].includes(a.format)); return fam.length ? fam.map(a => `<article class="markt-card"><span class="eyebrow">${esc(a.format === 'nachruf' ? 'Nachruf' : 'Familienanzeige')}${a.ort ? ' · ' + esc(ctx.placeName(a.ort)) : ''}</span><h3><a href="${a.url}">${esc(a.title)}</a></h3><p>${esc(truncate(a.teaser, 120))}</p><div class="meta">${C.timeShort(a.date, a)}<a class="btn-sm" href="${a.url}">Lesen</a></div></article>`).join('') : '<div class="markt-empty"><p>Geburten, Jubiläen, Danksagungen und Nachrufe aus der Gemeinde. Familienanzeigen erscheinen gekennzeichnet im Ressort Menschen.</p><a class="btn-sm" href="/menschen/">Zum Ressort Menschen</a></div>'; })()}
      </div>
    </div>
  </div></section>
<section class="section"><div class="shell">
  ${C.sectionHead('Leben in', 'Die fünf Ortsteile', null)}
  ${C.placesGrid(ctx)}
</div></section>

${businesses.length ? `<section class="section"><div class="shell">
  ${C.sectionHead('Lokale Betriebe', 'Einkaufen, Handwerk, Gastronomie', '/betriebe/', 'Alle Betriebe')}
  <div class="club-grid four">${businesses.map(b => C.businessCard(b, ctx)).join('')}</div>
</div></section>` : ''}

<section class="section"><div class="shell mitmachen">
  <div class="mm-text"><span class="eyebrow">Mitmachen</span><h2>Die Gemeinde schreibt mit</h2><p>Vereinsmeldungen, Termine, Fotos vom Fest, Hinweise auf Einsätze: Alles, was Sie einsenden, prüft die Redaktion gegen die Quelle und veröffentlicht es, auf Wunsch mit Ihrem Namen als Quelle.</p></div>
  <div class="mm-actions"><a class="btn" href="/meldung-senden/">Meldung senden</a><a class="btn ghost" href="/termine/melden/">Termin melden</a><a class="btn ghost" href="/vereine/eintragen/">Verein eintragen</a><a class="btn ghost" href="/betriebe/eintragen/">Betrieb eintragen</a></div>
</div></section>`;

  const ld = [{
    '@context': 'https://schema.org', '@type': 'CollectionPage', name: site.name, url: site.url + '/', description: site.claim,
    isPartOf: { '@id': site.url + '/#website' },
    mainEntity: { '@type': 'ItemList', itemListElement: articles.slice(0, 10).map((a, i) => ({ '@type': 'ListItem', position: i + 1, url: site.url + a.url, name: a.title })) }
  }];
  return layout(site, ctx, { title: '', description: site.description, url: '/', image: lead.image && lead.image.src ? lead.image.src : undefined, imageAlt: lead.image && lead.image.alt, jsonld: ld, bodyClass: 'home', nav: '/nachrichten/' , preloadImage: null }, content);
}

/* ============================================================ Ressort / Listen */
export function listPage(ctx, opts) {
  const { site } = ctx;
  const { title, eyebrow, desc, items, base, page, pages, nav, ort, crumbs, feed, extraTop = '', extraSide = '', jsonld = [], intro = '' } = opts;
  const lead = page === 1 ? items[0] : null;
  const rest = page === 1 ? items.slice(1) : items;
  const content = `
${C.pageHead(eyebrow, title, desc, `<p class="count-line">${opts.count} ${opts.count === 1 ? 'Meldung' : 'Meldungen'}${feed ? ` · <a href="${feed}">RSS-Feed</a>` : ''}</p>`, crumbs)}
${intro}
<section class="section"><div class="shell">
  ${extraTop}
  <div class="content-grid">
    <div class="feed">
      ${opts.base === '/menschen/' && page === 1 ? `<div class="facts fam"><h2>Familienanzeigen</h2><p>Hochzeit, Geburt, Jubiläum, Ehrung, Nachruf: Merzenich Aktuell veröffentlicht Familienanzeigen aus der Gemeinde mit Foto und Text. Private Anzeigen sind kostenlos, gewerbliche Danksagungen und Traueranzeigen von Bestattern werden als Anzeige gekennzeichnet. <a href="/meldung-senden/">Anzeige einsenden</a></p></div>` : ''}
      ${lead ? '' : '<h2 class="sr-only">Meldungen</h2>'}${lead ? `<article class="feed-lead"><a href="${lead.url}" tabindex="-1" aria-hidden="true">${C.media(lead, { priority: true, sizes: '(max-width: 640px) 100vw, 800px' }, ctx)}</a><div class="lead-copy">${C.kicker(lead, ctx)}<h2><a href="${lead.url}">${esc(lead.title)}</a></h2><p class="dek">${esc(lead.teaser)}</p><div class="meta">${C.timeEl(lead.date, '', lead)}${C.formatBadge(lead)}</div></div></article>` : ''}
      ${rest.slice(0, 4).map(a => C.feedRow(a, ctx)).join('')}${rest.length > 4 ? C.nativeAd(site) : ''}${rest.slice(4).map(a => C.feedRow(a, ctx)).join('')}
      ${items.length === 0 ? '<p class="empty">In diesem Bereich gibt es noch keine Meldungen. <a href="/meldung-senden/">Sie wissen etwas? Melden Sie es uns.</a></p>' : ''}
      ${C.pagination(base, page, pages)}
    </div>
    <aside class="sidebar">
      ${extraSide}
      ${nav === '/sport/' || nav === '/vereine/' ? fussballBox(ctx) : ''}
      ${C.terminBox(ctx.events)}
      ${C.whatsappBox(site)}
      ${C.tipBox(site)}
      ${C.serviceBox(site)}
      ${C.adSlot('sidebar', site)}
    </aside>
  </div>
</div></section>`;
  const ld = [{
    '@context': 'https://schema.org', '@type': 'CollectionPage', name: title, url: abs(site, page === 1 ? base : `${base}seite/${page}/`), description: desc,
    isPartOf: { '@id': site.url + '/#website' },
    mainEntity: { '@type': 'ItemList', itemListElement: items.slice(0, 20).map((a, i) => ({ '@type': 'ListItem', position: i + 1, url: site.url + a.url, name: a.title })) }
  }, ...jsonld];
  return layout(site, ctx, { title: page > 1 ? `${title} – Seite ${page}` : title, description: desc, url: page === 1 ? base : `${base}seite/${page}/`, jsonld: ld, nav, ort, feed, feedTitle: title, breadcrumbs: crumbs, bodyClass: 'list' }, content);
}

/* ============================================================ Ortsteil */
export function placePage(ctx, place, items, page, pages) {
  const { site } = ctx;
  const clubs = ctx.clubs.filter(c => c.ort === place.slug);
  const biz = ctx.businesses.filter(b => b.ort === place.slug);
  const ev = ctx.events.filter(e => e.ort === place.slug);
  const facts = place.facts && place.facts.length ? `<div class="sidebox"><h3>Steckbrief ${esc(place.name)}</h3><ul class="service">${place.facts.map(f => `<li><span class="k">${esc(f.k)}</span><span class="v">${esc(f.v)}</span></li>`).join('')}</ul></div>` : '';
  const ortBox = ev.length ? C.sidebox(`Termine in ${place.name}`, `<ul>${ev.slice(0, 5).map(C.terminLi).join('')}</ul>`, { more: '/termine/' }) : '';
  const clubBox = clubs.length ? `<div class="sidebox"><h3>Vereine in ${esc(place.name)}<a href="/vereine/">alle</a></h3><ul class="linklist">${clubs.map(c => `<li><a href="${c.url}">${esc(c.name)}</a><small>${esc(c.category || '')}</small></li>`).join('')}</ul></div>` : '';
  const bizBox = biz.length ? `<div class="sidebox"><h3>Betriebe in ${esc(place.name)}<a href="/betriebe/">alle</a></h3><ul class="linklist">${biz.map(b => `<li><a href="${b.url}">${esc(b.name)}</a><small>${esc(b.category || '')}</small></li>`).join('')}</ul></div>` : '';
  const all = ctx.articles.filter(a => a.ort === place.slug);
  const kurzList = all.length ? `<div class="sidebox"><h3>Kurz gemeldet aus ${esc(place.name)}</h3><ul class="kurz-list one">${all.slice(0, 8).map(a => C.kurzItem(a, ctx)).join('')}</ul></div>` : '';
  const intro = page === 1 && place.html ? `<section class="place-intro"><div class="shell"><div class="place-grid"><div class="info-prose">${place.html}</div><div class="place-aside">${facts}${kurzList}</div></div></div></section>` : '';
  const ld = [{
    '@context': 'https://schema.org', '@type': 'Place', '@id': site.url + place.url + '#place', name: place.name,
    description: place.description, url: site.url + place.url,
    address: { '@type': 'PostalAddress', addressLocality: 'Merzenich', postalCode: '52399', addressRegion: 'NRW', addressCountry: 'DE' },
    containedInPlace: { '@type': 'AdministrativeArea', name: 'Gemeinde Merzenich' },
    ...(place.lat ? { geo: { '@type': 'GeoCoordinates', latitude: place.lat, longitude: place.lng } } : {})
  }];
  return listPage(ctx, {
    title: `Leben in ${place.name}`, eyebrow: 'Ortsteil', desc: place.description, items, base: place.url, page, pages, count: ctx.countByOrt(place.slug),
    ort: place.slug, crumbs: [{ name: 'Start', url: '/' }, { name: place.name, url: place.url }], feed: `${place.url}feed.xml`, jsonld: ld,
    intro, extraSide: ortBox + clubBox + bizBox
  });
}

/* ============================================================ Artikel */
export function articlePage(ctx, a) {
  const { site } = ctx;
  const r = site.ressorts[a.ressort];
  const place = ctx.placeBy(a.ort);
  const crumbs = [{ name: 'Start', url: '/' }, { name: r ? r.name : a.ressort, url: `/${a.ressort}/` }, ...(place ? [{ name: place.name, url: place.url }] : []), { name: a.title }];
  const author = ctx.authorBy(a.author);
  const related = ctx.related(a, 3);
  const more = ctx.articles.filter(x => x.ort === a.ort && x !== a).slice(0, 4);
  const evs = (a.events || []).map(id => ctx.eventBy(id)).filter(Boolean).concat(ctx.events.filter(e => e.article === a.id)).filter((e, i, arr) => arr.indexOf(e) === i);
  const im = a.image;
  const gallery = a.gallery || [];
  const isPress = a.format === 'kurz' && !a.body.trim();

  const figure = im && im.src ? `<figure class="art-figure">
      <div class="media${im.fit === 'contain' ? ' contain' : ''}">${img(im.src, { alt: im.alt || a.title, priority: true, kind: a.ressort, sizes: '(max-width: 760px) 100vw, 760px' }, ctx.cfg)}</div>
      <figcaption><span>${im.type ? `<span class="figure-badge">${esc(imageTypeLabel(im.type))}</span> · ` : ''}${esc(im.alt || '')}</span><span>${im.credit ? `Bild: ${esc(im.credit)}` : ''}</span></figcaption>
    </figure>` : '';

  const galleryHtml = gallery.length ? `<div class="gallery">${gallery.map((g, i) => `<figure><button class="shot" type="button" data-shot data-credit="${esc(g.credit || '')}"><div class="media">${img(g.src, { alt: g.alt || '', kind: a.ressort, sizes: '(max-width: 640px) 100vw, 250px' }, ctx.cfg)}</div></button><figcaption>${esc(g.alt || '')}${g.credit ? ` · ${esc(g.credit)}` : ''}</figcaption></figure>`).join('')}</div>` : '';

  const corrections = (a.corrections || []).length ? `<div class="corrections"><b>Korrekturhinweis.</b><ul>${a.corrections.map(c => `<li><time datetime="${esc(isoLocal(c.date))}">${esc(fmt.dateTime(c.date))}</time> ${esc(c.text)}</li>`).join('')}</ul></div>` : '';

  const sources = (a.sources || []).length ? `<div class="source-box"><b>Quelle und Datenstand.</b> Grundlage dieser Meldung: ${a.sources.map(s => s.url ? `<a href="${esc(s.url)}" target="_blank" rel="noopener nofollow">${esc(s.title)} ↗</a>` : esc(s.title)).join(', ')}.${a.sources[0].stand ? ` <span class="stand">Datenstand ${esc(fmt.date(a.sources[0].stand))}.</span>` : ''} Angaben wurden redaktionell geprüft und zusammengefasst; Bildtyp und Bildcredit sind ausgewiesen. <a href="/korrekturen/">Fehler melden</a></div>` : '';

  const eventBox = evs.length ? `<div class="sidebox"><h3>Termine zu dieser Meldung</h3><ul>${evs.map(C.terminLi).join('')}</ul></div>` : '';

  const content = `
<article class="article">
<div class="article-head"><div class="shell">
  ${C.crumbs(crumbs)}
  <div class="kick-row">${C.kicker(a, ctx)}${C.formatBadge(a)}${a.sponsored ? '<span class="fbadge anzeige">Anzeige</span>' : ''}</div>
  <h1>${esc(a.title)}</h1>
  <p class="dek">${esc(a.teaser)}</p>
  <div class="byline">
    <span class="avatar" aria-hidden="true">${esc(author ? author.initials : 'MA')}</span>
    <span class="who"><b>${author ? `<a href="${author.url}" rel="author">${esc(author.name)}</a>` : 'Redaktion Merzenich Aktuell'}</b><span>${esc(author ? author.role : 'Lokalredaktion')}${a.kuerzel ? ` · ${esc(a.kuerzel)}` : ''}</span></span>
    <span class="dates">${a.undated ? C.timeEl(a.date, '', a) : `Veröffentlicht ${C.timeEl(a.date)}`}${a.updated && a.updated !== a.date ? `<br>Aktualisiert ${C.timeEl(a.updated)}` : ''}<br><span class="readtime">${a.readingMinutes} Min. Lesezeit</span></span>
  </div>
</div></div>
<div class="shell article-grid">
  <div class="article-body" data-readable>
    ${C.shareBar(a, site)}
    ${figure}
    ${a.facts && a.facts.length ? `<div class="facts"><h2>Das Wichtigste in Kürze</h2><ul>${a.facts.map(f => `<li>${esc(f)}</li>`).join('')}</ul></div>` : ''}
    <div class="prose">${(() => { const parts = a.html.split('</p>'); if (parts.length > 3) { return parts.slice(0, 2).join('</p>') + '</p>' + C.adSlot('in_article', site) + parts.slice(2).join('</p>'); } return a.html; })()}</div>
    ${galleryHtml}
    ${corrections}
    ${a.html.split('</p>').length <= 3 ? C.adSlot('in_article', site) : ''}
    ${sources}
    ${(a.tags || []).length ? `<div class="tags">${a.tags.map(t => `<a href="/thema/${esc(ctx.tagSlug(t))}/" rel="tag">${esc(t)}</a>`).join('')}</div>` : ''}
    <div class="author-box"><span class="avatar" aria-hidden="true">${esc(author ? author.initials : 'MA')}</span><div class="b"><b>${author ? `<a href="${author.url}">${esc(author.name)}</a>` : 'Redaktion Merzenich Aktuell'}</b><p>${esc(author ? author.bio : 'Die Redaktion prüft jede Meldung gegen die Originalquelle, dokumentiert Bildtyp und Credit und ergänzt eigene Einordnung.')} Kontakt: <a href="mailto:${esc(site.email)}">${esc(site.email)}</a></p></div></div>
    <div class="cta-row"><a class="btn ghost" href="/meldung-senden/">Hinweis zu dieser Meldung senden</a><a class="btn ghost" href="/korrekturen/">Fehler melden</a></div>
  </div>
  <aside class="sidebar"><h2 class="sr-only">Weitere Inhalte</h2>
    ${eventBox}
    ${(() => { const same = ctx.articles.filter(x => x.ressort === a.ressort && x !== a).slice(0, 5); return same.length ? `<div class="sidebox"><h3>Mehr aus ${esc(r ? r.name : a.ressort)}<a href="/${a.ressort}/">alle</a></h3><ul class="linklist">${same.map(x => `<li><a href="${x.url}">${esc(x.title)}</a><small>${esc(ctx.placeName(x.ort) || 'Gemeinde')} · ${x.undated ? 'o. D.' : esc(fmt.short(x.date))}</small></li>`).join('')}</ul></div>` : ''; })()}
    ${more.length ? `<div class="sidebox"><h3>Mehr aus ${esc(place ? place.name : 'der Gemeinde')}${place ? `<a href="${place.url}">alle</a>` : ''}</h3><ul class="linklist">${more.map(x => `<li><a href="${x.url}">${esc(x.title)}</a><small>${esc(site.ressorts[x.ressort]?.name || '')} · ${x.undated ? 'o. D.' : esc(fmt.short(x.date))}</small></li>`).join('')}</ul></div>` : ''}
    ${C.terminBox(ctx.events)}
    ${C.whatsappBox(site)}
    ${C.tipBox(site)}
    ${C.serviceBox(site)}
    ${C.adSlot('sidebar', site)}
  </aside>
</div>
</article>
${related.length ? `<section class="section"><div class="shell">${C.sectionHead('Weiterlesen', 'Ebenfalls aktuell', '/nachrichten/', 'Alle Meldungen')}<div class="cards-3">${related.map(x => C.card(x, ctx)).join('')}</div></div></section>` : ''}`;

  const images = im && im.src ? [abs(site, im.src)] : [site.url + '/assets/img/og-default.jpg'];
  const ld = [{
    '@context': 'https://schema.org', '@type': a.undated ? 'Article' : a.format === 'kommentar' || a.format === 'kolumne' ? 'OpinionNewsArticle' : a.sponsored ? 'AdvertiserContentArticle' : a.format === 'liveticker' ? 'LiveBlogPosting' : 'NewsArticle',
    '@id': site.url + a.url + '#article',
    mainEntityOfPage: { '@type': 'WebPage', '@id': site.url + a.url },
    headline: truncate(a.title, 110), description: a.teaser, image: images,
    ...(a.undated ? { dateModified: isoLocal(a.retrieved || a.date) } : { datePublished: isoLocal(a.date), dateModified: isoLocal(a.updated || a.date) }),
    author: author ? [author.org ? { '@type': 'Organization', name: author.name, url: site.url + author.url } : { '@type': 'Person', name: author.name, url: site.url + author.url, jobTitle: author.role }] : [{ '@type': 'Organization', name: 'Redaktion Merzenich Aktuell', url: site.url + '/ueber-uns/' }],
    publisher: { '@id': site.url + '/#organization' },
    isAccessibleForFree: true, inLanguage: 'de-DE', articleSection: r ? r.name : a.ressort,
    keywords: (a.tags || []).join(', ') || undefined, wordCount: a.wordCount,
    ...(place ? { contentLocation: { '@type': 'Place', name: place.name, address: { '@type': 'PostalAddress', addressLocality: 'Merzenich', postalCode: '52399', addressCountry: 'DE' } }, about: { '@type': 'Place', name: place.name, url: site.url + place.url } } : { contentLocation: { '@type': 'Place', name: 'Gemeinde Merzenich' } }),
    ...(a.sources && a.sources.length ? { citation: a.sources.filter(s => s.url).map(s => s.url), isBasedOn: a.sources.filter(s => s.url).map(s => s.url) } : {}),
    ...(a.corrections && a.corrections.length ? { correction: a.corrections.map(c => c.text) } : {}),
    speakable: { '@type': 'SpeakableSpecification', cssSelector: ['.article-head h1', '.article-head .dek'] }
  }];
  return layout(site, ctx, {
    title: a.title, description: a.teaser, url: a.url, image: im && im.src, imageAlt: im && im.alt, type: 'article',
    published: a.undated ? null : isoLocal(a.date), modified: isoLocal(a.updated || a.retrieved || a.date), section: r ? r.name : a.ressort, tags: a.tags, keywords: (a.tags || []).join(', '),
    author: author ? author.name : site.name, jsonld: ld, breadcrumbs: crumbs, nav: `/${a.ressort}/`, ort: a.ort, bodyClass: 'article',
    noindex: a.noindex || isPress, preloadImage: im && im.src && !ctx.cfg.imageCdn ? im.src : null
  }, content);
}

/* ============================================================ Termine */
export function eventsPage(ctx, opts = {}) {
  const { site, events } = ctx;
  const byMonth = new Map();
  for (const e of events) { const k = fmt.ym(e.start); if (!byMonth.has(k)) byMonth.set(k, []); byMonth.get(k).push(e); }
  const past = ctx.pastEvents.slice(0, 12);
  const cats = [...new Set(events.map(e => e.category).filter(Boolean))];
  const content = `
${C.pageHead('Kalender', 'Termine in der Gemeinde Merzenich', 'Feste, Sitzungen, Sport, Kirche und Vereine in Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald. Jeder Termin nennt Veranstalter und Quelle und lässt sich in den eigenen Kalender übernehmen.', `<p class="count-line">${events.length} kommende Termine · <a href="/termine/kalender.ics">Gesamtkalender abonnieren</a> · <a href="/termine/feed.xml">RSS</a></p>`, [{ name: 'Start', url: '/' }, { name: 'Termine' }])}
<section class="section"><div class="shell">
  ${(() => { const d0 = new Date(ctx.now); const day = x => fmt.ymd(x); const today = day(d0), tom = day(new Date(d0.getTime() + 86400000)); const dow = new Date(isoLocal(d0)).getDay(); const satOff = (6 - dow + 7) % 7; const sat = day(new Date(d0.getTime() + satOff * 86400000)), sun = day(new Date(d0.getTime() + (satOff + 1) * 86400000)); const grp = [['Heute', e => day(e.start) === today], ['Morgen', e => day(e.start) === tom], ['Wochenende', e => [sat, sun].includes(day(e.start))], ['Nächste 14 Tage', e => e.start - d0 < 14 * 86400000]]; return `<div class="weekbar">${grp.map(([l, f]) => { const n = events.filter(f).length; return `<div class="wb"><b>${n}</b><span>${l}</span></div>`; }).join('')}<div class="wb wb-cta"><a class="btn" href="/termine/melden/">Termin melden</a></div></div>`; })()}
  <div class="filterbar" data-filter-root>
    <div class="chips"><button type="button" class="chip on" data-filter="*" aria-pressed="true">Alle</button>${ctx.places.map(p => `<button type="button" class="chip" data-filter="ort:${p.slug}" aria-pressed="false">${esc(p.name)}</button>`).join('')}${cats.map(c => `<button type="button" class="chip" data-filter="cat:${esc(c)}" aria-pressed="false">${esc(c)}</button>`).join('')}</div>
    <p class="count-line" data-filter-count aria-live="polite"></p>
  </div>
  ${events.length ? `<h2 class="sr-only">Die nächsten Termine</h2><div class="events-grid">${events.slice(0, 4).map(e => C.eventCard(e, ctx)).join('')}</div>` : ''}
</div></section>
<section class="section"><div class="shell">
  ${C.sectionHead('Alle Termine', 'Chronologisch', '/termine/melden/', 'Termin melden')}
  <div class="content-grid">
    <div class="event-list" data-filter-list>
      ${[...byMonth.entries()].map(([k, list]) => `<h3 class="month-h">${esc(fmt.month(list[0].start))}</h3>${list.map(e => `<div data-ort="${esc(e.ort || '')}" data-cat="${esc(e.category || '')}">${C.eventRow(e, ctx, 'h4')}</div>`).join('')}`).join('')}
      ${events.length === 0 ? '<p class="empty">Aktuell sind keine Termine eingetragen.</p>' : ''}
      <p class="no-result" data-filter-empty hidden>Keine Termine für diese Auswahl.</p>
      ${past.length ? `<details class="past"><summary>Vergangene Termine</summary><ul class="archive-list">${past.map(e => `<li><time datetime="${esc(isoLocal(e.start))}">${esc(fmt.short(e.start))}</time><a href="${e.url}">${esc(e.title)}</a></li>`).join('')}</ul></details>` : ''}
    </div>
    <aside class="sidebar">
      <div class="sidebox"><h3>Termin eintragen</h3><p class="p">Vereine, Gemeinde, Kirchen und Veranstalter melden Termine kostenlos über das Formular. Die Redaktion prüft und veröffentlicht innerhalb eines Werktags.</p><a class="btn block" href="/termine/melden/">Zum Formular</a></div>
      <div class="sidebox"><h3>Kalender abonnieren</h3><p class="p">Alle Termine automatisch in Apple Kalender, Google Kalender oder Outlook: <a href="/termine/kalender.ics">kalender.ics</a> abonnieren.</p></div>
      ${C.whatsappBox(site)}
      ${C.tipBox(site)}
    </aside>
  </div>
</div></section>`;
  const ld = [{ '@context': 'https://schema.org', '@type': 'CollectionPage', name: 'Termine in Merzenich', url: site.url + '/termine/', mainEntity: { '@type': 'ItemList', itemListElement: events.slice(0, 20).map((e, i) => ({ '@type': 'ListItem', position: i + 1, url: site.url + e.url, name: e.title })) } }];
  return layout(site, ctx, { title: 'Termine & Veranstaltungen in Merzenich', description: 'Veranstaltungskalender für Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald: Feste, Sitzungen, Sport, Kirche und Vereine, mit Quelle und Kalenderdatei.', url: '/termine/', nav: '/termine/', jsonld: ld, feed: '/termine/feed.xml', feedTitle: 'Termine', breadcrumbs: [{ name: 'Start', url: '/' }, { name: 'Termine', url: '/termine/' }], bodyClass: 'events' }, content);
}

export function eventLd(site, e, ctx) {
  const place = ctx.placeBy(e.ort);
  const online = /online|zoom|digital/i.test(e.location || '');
  return {
    '@context': 'https://schema.org', '@type': 'Event', '@id': site.url + e.url + '#event',
    name: e.title, description: e.teaser || stripHtml(e.html || '').slice(0, 300) || e.title, url: site.url + e.url,
    startDate: isoLocal(e.start), ...(e.end ? { endDate: isoLocal(e.end) } : {}),
    eventStatus: e.status === 'abgesagt' ? 'https://schema.org/EventCancelled' : e.status === 'verschoben' ? 'https://schema.org/EventPostponed' : 'https://schema.org/EventScheduled',
    eventAttendanceMode: online ? 'https://schema.org/OnlineEventAttendanceMode' : 'https://schema.org/OfflineEventAttendanceMode',
    location: online ? { '@type': 'VirtualLocation', url: e.source || site.url + e.url } : { '@type': 'Place', name: e.location || (place ? place.name : 'Merzenich'), address: { '@type': 'PostalAddress', streetAddress: e.address || undefined, addressLocality: place ? place.name : 'Merzenich', postalCode: '52399', addressRegion: 'NRW', addressCountry: 'DE' } },
    ...(e.organizer ? { organizer: { '@type': 'Organization', name: e.organizer, ...(e.organizerUrl ? { url: e.organizerUrl } : {}) } } : {}),
    ...(e.image && e.image.src ? { image: [abs(site, e.image.src)] } : {}),
    offers: { '@type': 'Offer', url: site.url + e.url, price: e.price != null ? String(e.price).replace(/[^\d.,]/g, '').replace(',', '.') || '0' : '0', priceCurrency: 'EUR', availability: 'https://schema.org/InStock', validFrom: isoLocal((() => { const created = e.created ? toDate(e.created) : null; return created && created < e.start ? created : e.start; })()) },
    performer: e.organizer ? { '@type': 'Organization', name: e.organizer } : undefined,
    isAccessibleForFree: !e.price || /^(0|frei|kostenlos)/i.test(String(e.price))
  };
}

export function eventPage(ctx, e) {
  const { site } = ctx;
  const place = ctx.placeBy(e.ort);
  const art = e.article ? ctx.articleBy(e.article) : null;
  const crumbs = [{ name: 'Start', url: '/' }, { name: 'Termine', url: '/termine/' }, { name: e.title }];
  const others = ctx.events.filter(x => x !== e).slice(0, 5);
  const content = `
<article class="article event-page">
<div class="article-head"><div class="shell">
  ${C.crumbs(crumbs)}
  <div class="kick-row"><span class="kicker">${esc(e.category || 'Termin')}${place ? `<span class="dist">${esc(place.name)}</span>` : ''}</span>${e.status === 'abgesagt' ? '<span class="fbadge eilmeldung">Abgesagt</span>' : e.status === 'verschoben' ? '<span class="fbadge">Verschoben</span>' : ''}</div>
  <h1>${esc(e.title)}</h1>
  ${e.teaser ? `<p class="dek">${esc(e.teaser)}</p>` : ''}
</div></div>
<div class="shell article-grid">
  <div class="article-body">
    <div class="event-facts">
      <div class="ef"><span class="ef-ic">${ICONS.calendar}</span><div><b>${esc(fmt.wdLong(e.start))}, ${esc(fmt.date(e.start))}</b><span>${esc(fmt.time(e.start))} Uhr${e.end ? ` bis ${esc(fmt.time(e.end))} Uhr` : ''}</span></div></div>
      <div class="ef"><span class="ef-ic">${ICONS.pin}</span><div><b>${esc(e.location || (place ? place.name : 'Merzenich'))}</b><span>${esc(e.address || (place ? `${place.name}, 52399 Merzenich` : '52399 Merzenich'))}</span></div></div>
      ${e.organizer ? `<div class="ef"><span class="ef-ic">${ICONS.bell}</span><div><b>${esc(e.organizer)}</b><span>Veranstalter${e.organizerUrl ? ` · <a href="${esc(e.organizerUrl)}" target="_blank" rel="noopener">Website ↗</a>` : ''}</span></div></div>` : ''}
      ${e.price ? `<div class="ef"><span class="ef-ic">€</span><div><b>${esc(e.price)}</b><span>Eintritt</span></div></div>` : '<div class="ef"><span class="ef-ic">€</span><div><b>Eintritt frei</b><span>sofern nicht anders angegeben</span></div></div>'}
    </div>
    <div class="cta-row"><a class="btn" href="${e.url}termin.ics" download>In den Kalender</a>${e.address || e.location ? `<a class="btn ghost" href="https://www.openstreetmap.org/search?query=${encodeURIComponent((e.address || e.location) + ', Merzenich')}" target="_blank" rel="noopener">Karte ↗</a>` : ''}${e.source ? `<a class="btn ghost" href="${esc(e.source)}" target="_blank" rel="noopener nofollow">Quelle ↗</a>` : ''}</div>
    ${e.image && e.image.src ? `<figure class="art-figure"><div class="media">${img(e.image.src, { alt: e.image.alt || e.title, priority: true, kind: 'termine', sizes: '(max-width: 760px) 100vw, 760px' }, ctx.cfg)}</div><figcaption><span>${e.image.type ? `<span class="figure-badge">${esc(imageTypeLabel(e.image.type))}</span> · ` : ''}${esc(e.image.alt || '')}</span><span>${e.image.credit ? `Bild: ${esc(e.image.credit)}` : ''}</span></figcaption></figure>` : ''}
    ${e.html ? `<div class="prose">${e.html}</div>` : ''}
    ${art ? `<div class="facts"><h2>Meldung dazu</h2><p><a href="${art.url}">${esc(art.title)}</a></p></div>` : ''}
    <div class="source-box"><b>Termindaten.</b> ${e.source ? `Quelle: <a href="${esc(e.source)}" target="_blank" rel="noopener nofollow">${esc(e.organizer || 'Veranstalter')} ↗</a>. ` : ''}Stand ${esc(fmt.date(e.updated || e.created || ctx.now))}. Änderungen bitte an <a href="mailto:${esc(site.email)}">${esc(site.email)}</a> oder über <a href="/termine/melden/">Termin melden</a>.</div>
  </div>
  <aside class="sidebar"><h2 class="sr-only">Weitere Inhalte</h2>
    ${others.length ? `<div class="sidebox"><h3>Weitere Termine<a href="/termine/">alle</a></h3><ul>${others.map(C.terminLi).join('')}</ul></div>` : ''}
    ${C.whatsappBox(site)}
    ${C.tipBox(site)}
  </aside>
</div>
</article>`;
  const when = fmt.time(e.start) === '00:00' ? `${fmt.long(e.start)}${e.end ? ' bis ' + fmt.date(e.end) : ''}` : `${fmt.long(e.start)} um ${fmt.time(e.start)} Uhr`;
  const metaDesc = truncate([e.teaser, `${e.title} am ${when}${e.location ? ', ' + e.location : ''}${place && !(e.location || '').includes(place.name) ? ', ' + place.name : ''}${e.organizer ? '. Veranstalter: ' + e.organizer : ''}.`].filter(Boolean).join(' '), 160);
  return layout(site, ctx, { title: `${e.title} – ${fmt.date(e.start)}`, description: metaDesc, url: e.url, image: e.image && e.image.src, jsonld: [eventLd(site, e, ctx)], breadcrumbs: crumbs, nav: '/termine/', ort: e.ort, bodyClass: 'event', type: 'article' }, content);
}

/* ============================================================ Vereine / Betriebe */
export function directoryPage(ctx, kind) {
  const { site } = ctx;
  const isClub = kind === 'vereine';
  const items = isClub ? ctx.clubs : ctx.businesses;
  const cats = [...new Set(items.map(i => i.category).filter(Boolean))].sort();
  const title = isClub ? 'Vereine in der Gemeinde Merzenich' : 'Lokale Betriebe in Merzenich';
  const desc = isClub ? 'Wer in Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald aktiv ist: Schützen, Sport, Karneval, Feuerwehr, Kirche und Ehrenamt mit Ansprechpartnern und offiziellen Seiten.' : 'Einkaufen, Handwerk, Gastronomie, Gesundheit und Dienstleistungen aus der Gemeinde Merzenich. Einträge sind kostenlos, hervorgehobene Einträge sind als Anzeige gekennzeichnet.';
  const content = `
${C.pageHead(isClub ? 'Vereinsleben' : 'Branchenbuch', title, desc, `<p class="count-line">${items.length} Einträge · <a href="${isClub ? '/vereine/eintragen/' : '/betriebe/eintragen/'}">${isClub ? 'Verein eintragen' : 'Betrieb eintragen'}</a></p>`, [{ name: 'Start', url: '/' }, { name: isClub ? 'Vereine' : 'Lokale Betriebe' }])}
<section class="section"><div class="shell">
  <div class="filterbar" data-filter-root>
    <div class="chips"><button type="button" class="chip on" data-filter="*" aria-pressed="true">Alle</button>${ctx.places.map(p => `<button type="button" class="chip" data-filter="ort:${p.slug}" aria-pressed="false">${esc(p.name)}</button>`).join('')}${cats.map(c => `<button type="button" class="chip" data-filter="cat:${esc(c)}" aria-pressed="false">${esc(c)}</button>`).join('')}</div>
    <a class="btn" href="${isClub ? '/vereine/eintragen/' : '/betriebe/eintragen/'}">${isClub ? 'Verein eintragen' : 'Betrieb eintragen'}</a>
    <p class="count-line" data-filter-count aria-live="polite"></p>
  </div>
  ${items.length > 8 ? `<nav class="az" aria-label="Alphabet">${[...new Set(items.map(i => i.name.replace(/^(KG|SC|SV|TTC|St\.|1\.|FC|Der|Die|Das)\s+/i, '')[0].toUpperCase()))].sort().map(l => `<a href="#az-${l}">${l}</a>`).join('')}</nav>` : ''}
  <h2 class="sr-only">Verzeichnis</h2><div class="club-grid" data-filter-list>${(() => { const seen = new Set(); return items.map(i => { const L = i.name.replace(/^(KG|SC|SV|TTC|St\.|1\.|FC|Der|Die|Das)\s+/i, '')[0].toUpperCase(); const id = seen.has(L) ? '' : ` id="az-${L}"`; seen.add(L); return `<div data-ort="${esc(i.ort || '')}" data-cat="${esc(i.category || '')}"${id}>${isClub ? C.clubCard(i, ctx) : C.businessCard(i, ctx)}</div>`; }).join(''); })()}</div>
  <p class="no-result" data-filter-empty hidden>Keine Einträge für diese Auswahl.</p>
  ${items.length === 0 ? `<p class="empty">Noch keine Einträge. <a href="${isClub ? '/vereine/eintragen/' : '/betriebe/eintragen/'}">Jetzt eintragen.</a></p>` : ''}
</div></section>
${isClub ? `<section class="section"><div class="shell">${C.sectionHead('Meldungen', 'Aus den Vereinen', '/vereine/meldungen/', 'Alle Vereinsmeldungen')}<div class="cards-3">${ctx.articles.filter(a => a.ressort === 'vereine' || a.ressort === 'sport').slice(0, 3).map(a => C.card(a, ctx)).join('')}</div></div></section>` : `<section class="section"><div class="shell">${C.sectionHead('Wirtschaft', 'Meldungen aus Handel und Handwerk', '/wirtschaft/', 'Alle')}<div class="cards-3">${ctx.articles.filter(a => a.ressort === 'wirtschaft').slice(0, 3).map(a => C.card(a, ctx)).join('')}</div></div></section>`}`;
  const ld = [{ '@context': 'https://schema.org', '@type': 'CollectionPage', name: title, url: site.url + (isClub ? '/vereine/' : '/betriebe/'), mainEntity: { '@type': 'ItemList', itemListElement: items.map((i, n) => ({ '@type': 'ListItem', position: n + 1, url: site.url + i.url, name: i.name })) } }];
  return layout(site, ctx, { title, description: desc, url: isClub ? '/vereine/' : '/betriebe/', nav: isClub ? '/vereine/' : '/wirtschaft/', jsonld: ld, breadcrumbs: [{ name: 'Start', url: '/' }, { name: isClub ? 'Vereine' : 'Lokale Betriebe', url: isClub ? '/vereine/' : '/betriebe/' }], bodyClass: 'directory' }, content);
}

export function entityPage(ctx, item, kind) {
  const { site } = ctx;
  const isClub = kind === 'vereine';
  const place = ctx.placeBy(item.ort);
  const crumbs = [{ name: 'Start', url: '/' }, { name: isClub ? 'Vereine' : 'Lokale Betriebe', url: isClub ? '/vereine/' : '/betriebe/' }, { name: item.name }];
  const news = ctx.articles.filter(a => (a.tags || []).some(t => t.toLowerCase() === item.name.toLowerCase()) || (a.entities || []).includes(item.slug)).slice(0, 6);
  const evs = ctx.events.filter(e => e.organizer && e.organizer.toLowerCase() === item.name.toLowerCase() || (e.entity === item.slug)).slice(0, 5);
  const facts = [
    item.category && { k: 'Kategorie', v: item.category },
    place && { k: 'Ortsteil', v: place.name },
    item.founded && { k: 'Gegründet', v: String(item.founded) },
    item.members && { k: 'Mitglieder', v: String(item.members) },
    item.address && { k: 'Adresse', v: item.address },
    item.hours && { k: 'Öffnungszeiten', v: item.hours },
    item.contact && { k: 'Ansprechpartner', v: item.contact },
    item.phone && { k: 'Telefon', v: item.phone, tel: item.phone },
    item.email && { k: 'E-Mail', v: item.email, mail: item.email },
    item.website && { k: 'Website', v: item.website.replace(/^https?:\/\//, '').replace(/\/$/, ''), url: item.website }
  ].filter(Boolean);
  const content = `
<article class="article entity-page">
<div class="article-head"><div class="shell">
  ${C.crumbs(crumbs)}
  <div class="kick-row"><span class="kicker">${esc(item.category || (isClub ? 'Verein' : 'Betrieb'))}${place ? `<span class="dist">${esc(place.name)}</span>` : ''}</span>${item.sponsored ? '<span class="fbadge anzeige">Anzeige</span>' : ''}</div>
  <h1>${esc(item.name)}</h1>
  <p class="dek">${esc(item.description)}</p>
</div></div>
<div class="shell article-grid">
  <div class="article-body">
    ${item.image && item.image.src ? `<figure class="art-figure"><div class="media${item.image.fit === 'contain' ? ' contain' : ''}">${img(item.image.src, { alt: item.image.alt || item.name, priority: true, kind: isClub ? 'vereine' : 'wirtschaft', sizes: '(max-width: 760px) 100vw, 760px' }, ctx.cfg)}</div><figcaption><span>${esc(item.image.alt || '')}</span><span>${item.image.credit ? `Bild: ${esc(item.image.credit)}` : ''}</span></figcaption></figure>` : ''}
    ${item.html ? `<div class="prose">${item.html}</div>` : ''}
    ${news.length ? `<div class="facts"><h2>Meldungen zu ${esc(item.name)}</h2><ul>${news.map(a => `<li><a href="${a.url}">${esc(a.title)}</a> <small>${a.undated ? 'o. D.' : esc(fmt.short(a.date))}</small></li>`).join('')}</ul></div>` : ''}
    <div class="source-box"><b>Eintrag.</b> Angaben laut ${isClub ? 'Verein' : 'Betrieb'} bzw. öffentlicher Quelle, Stand ${esc(fmt.date(item.updated || ctx.now))}. Änderungen bitte über <a href="${isClub ? '/vereine/eintragen/' : '/betriebe/eintragen/'}">das Formular</a> oder an <a href="mailto:${esc(site.email)}">${esc(site.email)}</a>.</div>
  </div>
  <aside class="sidebar"><h2 class="sr-only">Weitere Inhalte</h2>
    <div class="sidebox"><h3>Auf einen Blick</h3><ul class="service">${facts.map(f => `<li><span class="k">${esc(f.k)}</span><span class="v">${f.url ? `<a href="${esc(f.url)}" target="_blank" rel="noopener${item.sponsored ? ' sponsored' : ''}">${esc(f.v)}</a>` : f.tel ? `<a href="tel:${esc(f.tel.replace(/[^\d+]/g, ''))}">${esc(f.v)}</a>` : f.mail ? `<a href="mailto:${esc(f.mail)}">${esc(f.v)}</a>` : esc(f.v)}</span></li>`).join('')}</ul></div>
    ${evs.length ? `<div class="sidebox"><h3>Termine</h3><ul>${evs.map(C.terminLi).join('')}</ul></div>` : ''}
    ${place ? `<div class="sidebox"><h3>Leben in ${esc(place.name)}</h3><p class="p">${esc(truncate(place.description, 160))}</p><a class="btn ghost block" href="${place.url}">Zur Ortsteilseite</a></div>` : ''}
    ${C.tipBox(site)}
  </aside>
</div>
</article>`;
  const ld = [{
    '@context': 'https://schema.org', '@type': isClub ? (item.category && /sport|fußball|tennis|tischtennis/i.test(item.category) ? 'SportsOrganization' : 'Organization') : 'LocalBusiness',
    '@id': site.url + item.url + '#entity', name: item.name, description: item.description, url: site.url + item.url,
    ...(item.website ? { sameAs: [item.website] } : {}), ...(item.phone ? { telephone: item.phone } : {}), ...(item.email ? { email: item.email } : {}),
    ...(item.founded ? { foundingDate: String(item.founded) } : {}),
    ...(item.image && item.image.src ? { image: abs(site, item.image.src), logo: item.image.fit === 'contain' ? abs(site, item.image.src) : undefined } : {}),
    address: { '@type': 'PostalAddress', streetAddress: item.address || undefined, addressLocality: place ? place.name : 'Merzenich', postalCode: '52399', addressRegion: 'NRW', addressCountry: 'DE' },
    ...(item.hours && !isClub ? { openingHours: item.hours } : {}), areaServed: 'Gemeinde Merzenich'
  }];
  return layout(site, ctx, { title: item.name, description: item.description, url: item.url, image: item.image && item.image.src, jsonld: ld, breadcrumbs: crumbs, nav: isClub ? '/vereine/' : '/wirtschaft/', ort: item.ort, bodyClass: 'entity' }, content);
}

/* ============================================================ Autor */
export function authorPage(ctx, au) {
  const { site } = ctx;
  const items = ctx.articles.filter(a => a.author === au.slug);
  const crumbsAuthor = [{ name: 'Start', url: '/' }, { name: 'Über uns', url: '/ueber-uns/' }, { name: au.name }];
  const content = `
${C.pageHead(au.role || 'Redaktion', au.name, au.bio, `<p class="count-line">${items.length} ${items.length === 1 ? 'Meldung' : 'Meldungen'}${au.email ? ` · <a href="mailto:${esc(au.email)}">${esc(au.email)}</a>` : ''}</p>`, crumbsAuthor)}
<section class="section"><div class="shell"><div class="content-grid"><div class="feed"><h2 class="sr-only">Meldungen</h2>${items.map(a => C.feedRow(a, ctx)).join('') || '<p class="empty">Noch keine Meldungen.</p>'}</div><aside class="sidebar">${au.html ? `<div class="sidebox"><h3>Über ${esc(au.name.split(' ')[0])}</h3><div class="p">${au.html}</div></div>` : ''}${C.tipBox(site)}</aside></div></div></section>`;
  const ld = [{ '@context': 'https://schema.org', '@type': 'ProfilePage', mainEntity: { '@type': au.org ? 'Organization' : 'Person', '@id': site.url + au.url + '#person', name: au.name, jobTitle: au.org ? undefined : au.role, description: au.bio, url: site.url + au.url, ...(au.email ? { email: au.email } : {}), ...(au.sameAs && au.sameAs.length ? { sameAs: au.sameAs } : {}), worksFor: { '@id': site.url + '/#organization' } } }];
  return layout(site, ctx, { title: `${au.name} – ${au.role || 'Redaktion'}`, description: au.bio, url: au.url, jsonld: ld, breadcrumbs: crumbsAuthor, bodyClass: 'author' }, content);
}

/* ============================================================ Thema / Archiv / Suche */
export function tagPage(ctx, tag, items) {
  return listPage(ctx, { title: `Thema: ${tag.name}`, eyebrow: 'Thema', desc: `Alle Meldungen zum Thema ${tag.name} aus der Gemeinde Merzenich.`, items, base: tag.url, page: 1, pages: 1, count: items.length, crumbs: [{ name: 'Start', url: '/' }, { name: 'Themen', url: '/thema/' }, { name: tag.name }], nav: '/nachrichten/' });
}
export function tagsIndex(ctx, tags) {
  const { site } = ctx;
  const crumbsThemen = [{ name: 'Start', url: '/' }, { name: 'Themen' }];
  const content = `${C.pageHead('Themen', 'Alle Themen', 'Schlagworte aus allen Meldungen. Je größer, desto mehr Meldungen.', '', crumbsThemen)}<section class="section"><div class="shell"><h2 class="sr-only">Schlagwortliste</h2><div class="tagcloud">${tags.map(t => `<a href="${t.url}" style="--n:${Math.min(t.count, 12)}">${esc(t.name)} <small>${t.count}</small></a>`).join('')}</div></div></section>`;
  return layout(site, ctx, { title: 'Themen', description: 'Alle Themen und Schlagworte auf Merzenich Aktuell.', url: '/thema/', breadcrumbs: crumbsThemen, bodyClass: 'tags' }, content);
}
export function archivePage(ctx) {
  const { site, allArticles } = ctx;
  const dated = allArticles.filter(a => !a.undated);
  const undated = allArticles.filter(a => a.undated);
  const byYM = new Map();
  for (const a of dated) { const k = fmt.ym(a.date); if (!byYM.has(k)) byYM.set(k, []); byYM.get(k).push(a); }
  const years = new Map();
  for (const [k, list] of byYM) { const y = k.slice(0, 4); if (!years.has(y)) years.set(y, []); years.get(y).push([k, list]); }
  const undatedBlock = undated.length ? `<div class="archive-year"><h2>Ohne Datum</h2><div class="archive-month" id="ohne-datum"><h3>Pressemitteilungen der Gemeinde<small>${undated.length} ${undated.length === 1 ? 'Meldung' : 'Meldungen'} · abgerufen ${esc(fmt.date(undated[0].retrieved || undated[0].date))}</small></h3><ul class="archive-list">${undated.map(a => `<li><span class="undated">o. D.</span><a href="${a.url}">${esc(a.title)}</a><span class="rs">${esc(site.ressorts[a.ressort]?.name || a.ressort)}</span></li>`).join('')}</ul></div></div>` : '';
  const crumbsArchiv = [{ name: 'Start', url: '/' }, { name: 'Archiv' }];
  const content = `${C.pageHead('Archiv', 'Alle Meldungen nach Monat', 'Jede Meldung seit dem Start, chronologisch. Für gezielte Suche nutzen Sie die Suche.', `<p class="count-line">${allArticles.length} Meldungen · <a href="/suche/">Suche</a> · <a href="/thema/">Themen</a></p>`, crumbsArchiv)}
<section class="section"><div class="shell">${[...years.entries()].map(([y, months]) => `<div class="archive-year"><h2>${y}</h2>${months.map(([k, list]) => `<div class="archive-month" id="${k}"><h3>${esc(fmt.month(list[0].date))}<small>${list.length} ${list.length === 1 ? 'Meldung' : 'Meldungen'}</small></h3><ul class="archive-list">${list.map(a => `<li><time datetime="${esc(isoLocal(a.date))}">${esc(fmt.short(a.date))}</time><a href="${a.url}">${esc(a.title)}</a><span class="rs">${esc(site.ressorts[a.ressort]?.name || a.ressort)}</span></li>`).join('')}</ul></div>`).join('')}</div>`).join('')}${undatedBlock}</div></section>`;
  return layout(site, ctx, { title: 'Archiv', description: 'Alle Meldungen von Merzenich Aktuell nach Monat sortiert.', url: '/archiv/', breadcrumbs: crumbsArchiv, bodyClass: 'archive' }, content);
}
export function searchPage(ctx) {
  const { site } = ctx;
  const content = `${C.pageHead('Suche', 'Meldungen, Termine, Vereine durchsuchen', 'Die Suche arbeitet direkt im Browser und überträgt keine Daten an Dritte.', '', [{ name: 'Start', url: '/' }, { name: 'Suche' }])}
<section class="section"><div class="shell search-wrap"><h2 class="sr-only">Suchformular und Ergebnisse</h2><form class="search-input" data-search role="search" action="/suche/"><input type="search" name="q" placeholder="Zum Beispiel Feuerwehr, Ortsfest, Schützen, LEADER" aria-label="Suchbegriff" autocomplete="off"><button class="btn" type="submit">Suchen</button></form><div data-results aria-live="polite"><p class="no-result">Bitte einen Suchbegriff eingeben. Beliebte Themen: ${ctx.topTags.slice(0, 6).map(t => `<a href="${t.url}">${esc(t.name)}</a>`).join(', ')}.</p></div></div></section>`;
  return layout(site, ctx, { title: 'Suche', description: 'Durchsuchen Sie alle Meldungen, Termine und Vereine auf Merzenich Aktuell.', url: '/suche/', bodyClass: 'search', noindex: true }, content);
}

/* ============================================================ Statische Seiten */
export function staticPage(ctx, p) {
  const { site } = ctx;
  const crumbs = [{ name: 'Start', url: '/' }, ...(p.parent ? [{ name: p.parent.name, url: p.parent.url }] : []), { name: p.title }];
  const form = p.form ? formBlock(ctx, p.form) : '';
  const wide = p.layout === 'wide';
  const side = p.layout === 'sidebar' || p.form;
  // Auf Formularseiten steht das Formular oben. Vorher lief der komplette
  // Erklaertext davor, dadurch lag das erste Eingabefeld rund anderthalb
  // Bildschirme unter der Falz. Der Text bleibt vollstaendig erhalten, er
  // rueckt nur unter das Formular, wo er als Nachschlagetext funktioniert.
  const prosa = p.html ? `<div class="info-prose">${p.html}</div>` : '';
  const inner = p.form
    ? `${form}${prosa ? `<div class="form-info">${prosa}</div>` : ''}${p.after ? p.after : ''}`
    : `${prosa}${form}${p.after ? p.after : ''}`;
  const content = `${C.pageHead(p.eyebrow || 'Service', p.title, p.description, '', crumbs)}
<section class="section"><div class="shell">${side ? `<div class="content-grid"><div>${inner}</div><aside class="sidebar">${p.sideHtml || ''}${p.form ? C.ablaufBox() : C.tipBox(site)}${C.serviceBox(site)}${C.adSlot('side', site)}</aside></div>` : wide ? inner : `<div class="narrow">${inner}</div>`}</div></section>`;
  const ld = p.jsonld || [];
  return layout(site, ctx, { title: p.title, description: p.description, url: p.url, breadcrumbs: crumbs, jsonld: ld, noindex: !!p.noindex, bodyClass: 'page', nav: p.nav }, content);
}

function formBlock(ctx, kind) {
  const { site } = ctx;
  const orte = `<select name="ortsteil" required><option value="">Ortsteil wählen …</option>${ctx.places.map(p => `<option>${esc(p.name)}</option>`).join('')}<option>Gesamte Gemeinde</option><option>Außerhalb / Region</option></select>`;
  const hp = `<p class="hp" aria-hidden="true"><label>Website<input name="bot-field" tabindex="-1" autocomplete="off"></label></p>`;
  const consent = `<label class="check"><input type="checkbox" name="einwilligung" required value="ja"><span>Ich habe die <a href="/datenschutz/">Datenschutzhinweise</a> gelesen und bin mit der Verarbeitung meiner Angaben zur Bearbeitung meiner Anfrage einverstanden.</span></label>`;
  const rights = `<label class="check"><input type="checkbox" name="bildrechte" value="ja"><span>Ich habe das Bild selbst aufgenommen oder besitze die Rechte und erlaube die Veröffentlichung mit Namensnennung.</span></label>`;
  const common = (name, action) => `<form class="form" name="${name}" method="POST" action="${action}" data-netlify="true" netlify-honeypot="bot-field" enctype="multipart/form-data"><input type="hidden" name="form-name" value="${name}">${hp}`;
  switch (kind) {
    case 'meldung': return `${common('meldung', '/meldung-senden/danke/')}
      <fieldset class="fgroup"><legend><b>1</b> Worum geht es</legend>
        <label><span class="lbl">Art der Meldung <span class="req" aria-hidden="true">*</span></span><select name="art" required><option value="">Bitte wählen …</option><option>Hinweis auf ein Ereignis</option><option>Vereinsmeldung</option><option>Termin</option><option>Blaulicht / Einsatz</option><option>Leserbrief</option><option>Lob, Kritik, Korrektur</option><option>Sonstiges</option></select></label>
        <label><span class="lbl">Überschrift <span class="req" aria-hidden="true">*</span></span><input name="titel" required maxlength="120" placeholder="Was ist passiert?"></label>
        <label><span class="lbl">Ihre Meldung <span class="req" aria-hidden="true">*</span></span><textarea name="text" rows="8" required placeholder="Wer, was, wann, wo, warum? Je konkreter, desto schneller können wir veröffentlichen."></textarea></label>
        <label><span class="lbl">Quelle oder Link <span class="hint">optional</span></span><input name="quelle" placeholder="https://"></label>
      </fieldset>
      <fieldset class="fgroup"><legend><b>2</b> Bild</legend>
        <div class="filebox"><input type="file" name="bild" accept="image/*" aria-label="Bild auswählen"><b>Bild auswählen oder hierher ziehen</b><span>JPG oder PNG, bis 8 MB. Freiwillig.</span></div>
        ${rights}
      </fieldset>
      <fieldset class="fgroup"><legend><b>3</b> Ihr Kontakt</legend>
        <div class="grid2"><label><span class="lbl">Ihr Name <span class="req" aria-hidden="true">*</span></span><input name="name" required autocomplete="name"></label><label><span class="lbl">E-Mail <span class="req" aria-hidden="true">*</span></span><input type="email" name="email" required autocomplete="email"></label></div>
        <div class="grid2"><label><span class="lbl">Telefon <span class="hint">optional, für Rückfragen</span></span><input type="tel" name="telefon" autocomplete="tel"></label><label><span class="lbl">Ortsteil <span class="req" aria-hidden="true">*</span></span>${orte}</label></div>
        <label class="check"><input type="checkbox" name="veroeffentlichung" value="ja"><span>Mein Name darf als Quelle genannt werden.</span></label>
        ${consent}
      </fieldset>
      <button class="btn" type="submit">Meldung absenden</button>
      <p class="form-note"><span class="req" aria-hidden="true">*</span> Pflichtangabe. Die Redaktion prüft jede Einsendung gegen die Originalquelle und meldet sich bei Rückfragen per E-Mail.</p></form>`;
    case 'termin': return `${common('termin', '/termine/melden/danke/')}
      <label>Titel der Veranstaltung<input name="titel" required maxlength="120"></label>
      <div class="grid2"><label>Beginn<input type="datetime-local" name="beginn" required></label><label>Ende (optional)<input type="datetime-local" name="ende"></label></div>
      <div class="grid2"><label>Veranstaltungsort<input name="ort" required placeholder="z. B. Schützenhalle Golzheim"></label><label>Ortsteil${orte}</label></div>
      <div class="grid2"><label>Veranstalter<input name="veranstalter" required></label><label>Kategorie<select name="kategorie" required><option value="">Bitte wählen …</option><option>Fest</option><option>Sport</option><option>Kirche</option><option>Rathaus</option><option>Kultur</option><option>Musik</option><option>Karneval</option><option>Brauchtum</option><option>Kinder &amp; Familie</option><option>Info</option><option>Markt</option><option>Umwelt</option><option>Senioren</option><option>Jugend</option><option>Sonstiges</option></select></label></div>
      <label>Beschreibung<textarea name="text" rows="5" required placeholder="Programm, Eintritt, Anmeldung, Besonderheiten"></textarea></label>
      <div class="grid2"><label>Eintritt<input name="eintritt" placeholder="frei / 5 Euro"></label><label>Link zur Veranstaltung<input name="link" placeholder="https://"></label></div>
      <div class="grid2"><label>Kontakt E-Mail<input type="email" name="email" required autocomplete="email"></label><label>Kontakt Telefon (optional)<input type="tel" name="telefon" autocomplete="tel"></label></div>
      <label>Plakat oder Bild (optional)<input type="file" name="bild" accept="image/*"></label>
      ${rights}${consent}
      <button class="btn" type="submit">Termin einreichen</button>
      <p class="form-note">Termine erscheinen nach Prüfung im Kalender, in der Regel innerhalb eines Werktags. Kostenlos für Vereine, Kirchen, Gemeinde und gemeinnützige Veranstalter.</p></form>`;
    case 'verein': return `${common('verein', '/vereine/eintragen/danke/')}
      <div class="grid2"><label>Name des Vereins<input name="verein" required></label><label>Ortsteil${orte}</label></div>
      <div class="grid2"><label>Kategorie<select name="kategorie" required><option value="">Bitte wählen …</option><option>Fußball</option><option>Tischtennis</option><option>Sport</option><option>Schützen</option><option>Karneval</option><option>Feuerwehr</option><option>Kirche</option><option>Musik &amp; Kultur</option><option>Heimat &amp; Geschichte</option><option>Soziales</option><option>Jugend</option><option>Fanclub</option><option>Sonstiges</option></select></label><label>Gründungsjahr (optional)<input name="gruendung" inputmode="numeric"></label></div>
      <label>Kurzbeschreibung (2 bis 4 Sätze)<textarea name="text" rows="4" required></textarea></label>
      <div class="grid2"><label>Website (optional)<input name="website" placeholder="https://" autocomplete="url"></label><label>Ansprechpartner<input name="ansprechpartner" required autocomplete="name"></label></div>
      <div class="grid2"><label>E-Mail<input type="email" name="email" required autocomplete="email"></label><label>Telefon (optional)<input type="tel" name="telefon" autocomplete="tel"></label></div>
      <label>Logo oder Foto (optional)<input type="file" name="bild" accept="image/*"></label>
      ${rights}${consent}
      <button class="btn" type="submit">Verein eintragen</button><p class="form-note">Der Eintrag ist kostenlos und wird vor Veröffentlichung geprüft.</p></form>`;
    case 'betrieb': return `${common('betrieb', '/betriebe/eintragen/danke/')}
      <div class="grid2"><label>Name des Betriebs<input name="betrieb" required></label><label>Ortsteil${orte}</label></div>
      <div class="grid2"><label>Branche<select name="branche" required><option value="">Bitte wählen …</option><option>Bäckerei &amp; Lebensmittel</option><option>Gastronomie</option><option>Handwerk</option><option>Gesundheit &amp; Pflege</option><option>Einzelhandel</option><option>Dienstleistung</option><option>Auto &amp; Mobilität</option><option>Bau &amp; Immobilien</option><option>Landwirtschaft &amp; Hofladen</option><option>Sonstiges</option></select></label><label>Adresse<input name="adresse" required placeholder="Straße Nr., 52399 Merzenich"></label></div>
      <label>Kurzbeschreibung<textarea name="text" rows="4" required></textarea></label>
      <div class="grid2"><label>Öffnungszeiten<input name="oeffnungszeiten" placeholder="Mo–Fr 8–18 Uhr"></label><label>Website (optional)<input name="website" placeholder="https://" autocomplete="url"></label></div>
      <div class="grid2"><label>E-Mail<input type="email" name="email" required autocomplete="email"></label><label>Telefon<input type="tel" name="telefon" autocomplete="tel"></label></div>
      <label>Logo oder Foto (optional)<input type="file" name="bild" accept="image/*"></label>
      <label class="check"><input type="checkbox" name="hervorheben" value="ja"><span>Ich interessiere mich für einen hervorgehobenen Eintrag oder ein Firmenporträt (kostenpflichtig, Angebot folgt).</span></label>
      ${rights}${consent}
      <button class="btn" type="submit">Betrieb eintragen</button><p class="form-note">Basis-Einträge sind kostenlos. Hervorgehobene Einträge und Firmenporträts sind als Anzeige gekennzeichnet.</p></form>`;
    case 'korrektur': return `${common('korrektur', '/korrekturen/danke/')}
      <label>Link zur Meldung<input type="url" name="url" required placeholder="https://merzenich-aktuell.de/..."></label>
      <label>Was ist falsch oder fehlt?<textarea name="text" rows="6" required></textarea></label>
      <label>Beleg oder Quelle (optional)<input name="quelle" placeholder="https://"></label>
      <div class="grid2"><label>Name (optional)<input name="name" autocomplete="name"></label><label>E-Mail für Rückmeldung<input type="email" name="email" required autocomplete="email"></label></div>
      ${consent}
      <button class="btn" type="submit">Korrektur melden</button><p class="form-note">Wir antworten in der Regel innerhalb von 24 Stunden. Korrekturen werden im Artikel mit Datum ausgewiesen.</p></form>`;
    case 'werbung': return `${common('werbung', '/werben/danke/')}
      <div class="grid2"><label>Firma / Organisation<input name="firma" required autocomplete="organization"></label><label>Ansprechpartner<input name="name" required autocomplete="name"></label></div>
      <div class="grid2"><label>E-Mail<input type="email" name="email" required autocomplete="email"></label><label>Telefon<input type="tel" name="telefon" autocomplete="tel"></label></div>
      <label>Interesse an<select name="format" required><option value="">Bitte wählen …</option><option>Banner Startseite</option><option>Sidebar-Anzeige</option><option>Firmenporträt (Sponsored Post)</option><option>Hervorgehobener Betriebseintrag</option><option>Stellenanzeige</option><option>Sonstiges</option></select></label>
      <label>Nachricht<textarea name="text" rows="5" placeholder="Ziel, Zeitraum, Budgetrahmen"></textarea></label>
      ${consent}
      <button class="btn" type="submit">Mediadaten anfragen</button></form>`;
    case 'whatsapp': return `<div class="wa-page" id="whatsapp"><h2>So funktioniert der Kanal</h2><p>Ein WhatsApp-Kanal ist ein Broadcast: Sie sehen unsere Meldungen, niemand sieht Ihre Nummer, niemand kann Ihnen über den Kanal schreiben. Abbestellen jederzeit über „Kanal verlassen“.</p>${site.social.whatsapp ? `<a class="btn wa-btn" href="${esc(site.social.whatsapp)}" target="_blank" rel="noopener">${ICONS.whatsapp} Kanal auf WhatsApp öffnen</a>` : '<p class="notice">Der Kanal startet mit dem Livegang. Der Link wird hier und im Seitenkopf eingetragen.</p>'}</div><div class="wa-page"><h2>Lieber RSS oder Kalender?</h2><p>Alle Meldungen: <a href="/feed.xml">feed.xml</a>. Nur Blaulicht: <a href="/blaulicht/feed.xml">blaulicht/feed.xml</a>. Termine als Feed <a href="/termine/feed.xml">termine/feed.xml</a> oder als Kalender <a href="/termine/kalender.ics">kalender.ics</a>.</p></div>`;
    case 'kontakt': return `${common('kontakt', '/ueber-uns/danke/')}
      <div class="grid2"><label>Name<input name="name" required autocomplete="name"></label><label>E-Mail<input type="email" name="email" required autocomplete="email"></label></div>
      <label>Nachricht<textarea name="text" rows="6" required></textarea></label>${consent}<button class="btn" type="submit">Nachricht senden</button></form>`;
    default: return '';
  }
}

export function notFoundPage(ctx) {
  const { site } = ctx;
  const content = `${C.pageHead('Fehler 404', 'Diese Seite gibt es nicht', 'Vielleicht wurde die Meldung verschoben oder der Link enthält einen Tippfehler.', '', null)}
<section class="section"><div class="shell narrow"><form class="search-input" role="search" action="/suche/"><input type="search" name="q" placeholder="Suchbegriff" aria-label="Suchbegriff"><button class="btn" type="submit">Suchen</button></form>
<div class="info-prose"><h2>Neueste Meldungen</h2><ul>${ctx.articles.slice(0, 6).map(a => `<li><a href="${a.url}">${esc(a.title)}</a></li>`).join('')}</ul><p><a href="/">Zur Startseite</a> · <a href="/archiv/">Archiv</a> · <a href="/termine/">Termine</a></p></div></div></section>`;
  return layout(site, ctx, { title: 'Seite nicht gefunden', description: 'Seite nicht gefunden.', url: '/404.html', noindex: true, bodyClass: 'e404' }, content);
}
export function thanksPage(ctx, p) {
  const { site } = ctx;
  const content = `${C.pageHead('Danke', p.title, p.description, '', null)}<section class="section"><div class="shell narrow"><h2 class="sr-only">Bestätigung</h2><div class="info-prose">${p.html}</div><p><a class="btn" href="/">Zur Startseite</a> <a class="btn ghost" href="/nachrichten/">Aktuelle Meldungen</a></p></div></section>`;
  return layout(site, ctx, { title: p.title, description: p.description, url: p.url, noindex: true, bodyClass: 'thanks' }, content);
}
export function offlinePage(ctx) {
  const { site } = ctx;
  const content = `${C.pageHead('Offline', 'Keine Verbindung', 'Diese Seite ist gerade nicht erreichbar. Sobald Sie wieder online sind, laden wir die aktuellen Meldungen.', '', null)}<section class="section"><div class="shell narrow"><h2 class="sr-only">Erneut verbinden</h2><p><a class="btn" href="/">Erneut versuchen</a></p></div></section>`;
  return layout(site, ctx, { title: 'Offline', description: 'Keine Verbindung.', url: '/offline/', noindex: true, bodyClass: 'offline' }, content);
}

/* ============================================================ SC 1919 Merzenich */
export function fussballBox(ctx) {
  const f = ctx.fussball; if (!f) return '';
  const me = f.tabelle.find(t => t.wir);
  const next = f.spiele.filter(s => new Date(s.datum) >= new Date(ctx.now.getTime() - 3 * 3600000)).slice(0, 4);
  return `<div class="sidebox sc-box"><h3>SC 1919 Merzenich<a href="/sc-1919-merzenich/">Vereinsseite</a></h3>
  ${me ? `<div class="sc-stand"><span><b>${me.platz}.</b>Platz</span><span><b>${me.punkte}</b>Punkte</span><span><b>${esc(me.tore)}</b>Tore</span></div>` : ''}
  <ul class="linklist">${next.map(s => `<li><a href="/sc-1919-merzenich/">${s.heim ? 'SCM' : esc(s.gegner)} – ${s.heim ? esc(s.gegner) : 'SCM'}</a><small>${esc(fmt.wd(s.datum))} ${esc(fmt.short(s.datum))} · ${esc(fmt.time(s.datum))} Uhr · ${s.heim ? 'Heim' : 'Auswärts'}</small></li>`).join('')}</ul>
  <p class="src-line">${esc(f.liga)} · Stand ${esc(fmt.date(f.stand))} · Quelle FUSSBALL.DE</p></div>`;
}

export function fussballPage(ctx) {
  const { site } = ctx; const f = ctx.fussball;
  const club = ctx.clubs.find(c => c.slug === 'sc-1919-merzenich');
  const news = ctx.articles.filter(a => (a.tags || []).some(t => /SC 1919 Merzenich|Kreisliga/i.test(t))).slice(0, 6);
  const crumbs = [{ name: 'Start', url: '/' }, { name: 'Sport', url: '/sport/' }, { name: 'SC 1919 Merzenich' }];
  const content = `
${C.pageHead('Fußball · Kreisliga A', 'SC 1919 Merzenich', `Tabelle, Spielplan und Meldungen zur ersten Mannschaft des SC 1919 Merzenich in der ${f.liga}, dazu die zweite und dritte Mannschaft.`, `<p class="count-line">Stand ${esc(fmt.date(f.stand))} · Quelle <a href="${esc(f.quelle)}" target="_blank" rel="noopener nofollow">FUSSBALL.DE</a>${club ? ` · <a href="${club.url}">Vereinsprofil</a>` : ''}</p>`, crumbs)}
<section class="section"><div class="shell">
  <div class="content-grid">
    <div>
      ${f.logo ? `<div class="sc-crest"><img src="${esc(f.logo)}" alt="Wappen des SC 1919 Merzenich" width="96" height="96" loading="eager"><span>Wappen: ${esc(f.logoCredit || 'SC 1919 Merzenich')}</span></div>` : ''}<div class="sc-stats">${(() => { const me = f.tabelle.find(t => t.wir); return me ? `<div><b>${me.platz}.</b><span>Tabellenplatz</span></div><div><b>${me.punkte}</b><span>Punkte</span></div><div><b>${esc(me.tore)}</b><span>Torverhältnis</span></div><div><b>${me.spiele}</b><span>Spiele</span></div>` : ''; })()}</div>
      ${C.sectionHead('Tabelle', f.liga, null, '', { h: 'h2' })}
      <div class="table-wrap"><table class="sc-table"><thead><tr><th>Pl.</th><th>Mannschaft</th><th class="n">Sp.</th><th class="n">S</th><th class="n">U</th><th class="n">N</th><th class="n">Tore</th><th class="n">Diff.</th><th class="n">Pkt.</th></tr></thead><tbody>${f.tabelle.map(t => `<tr${t.wir ? ' class="mark"' : ''}><td>${t.platz}</td><td>${esc(t.team)}</td><td class="n">${t.spiele}</td><td class="n">${t.siege ?? ''}</td><td class="n">${t.unentschieden ?? ''}</td><td class="n">${t.niederlagen ?? ''}</td><td class="n">${esc(t.tore)}</td><td class="n">${t.diff ?? ''}</td><td class="n">${t.punkte}</td></tr>`).join('')}</tbody></table></div>
      ${f.tabelleVollstaendig ? '' : `<p class="src-line">Auszug: Tabellenspitze und die Merzenicher Nachbarn. Die vollständige Tabelle steht bei <a href="${esc(f.quelle)}" target="_blank" rel="noopener nofollow">FUSSBALL.DE</a>.</p>`}
      ${C.sectionHead('Spielplan', 'Die nächsten Spiele', null, '', { h: 'h2' })}
      <div class="event-list">${f.spiele.map(s => `<article class="event-row">${C.dateBox(s.datum)}<div class="info"><span class="eyebrow">${esc(fmt.wdLong(s.datum))} · ${esc(fmt.time(s.datum))} Uhr · ${s.heim ? 'Heimspiel' : 'Auswärts'}</span><h3>${s.heim ? 'SC 1919 Merzenich' : esc(s.gegner)} – ${s.heim ? esc(s.gegner) : 'SC 1919 Merzenich'}</h3><div class="meta"><span>${s.heim ? esc(f.heimspielort || 'Merzenich') : 'bei ' + esc(s.gegner)}</span></div></div></article>`).join('')}</div>
      <p class="src-line">${esc(f.hinweis)}</p>
      ${(f.gespielt || []).length ? `${C.sectionHead('Bisher', 'Gespielte Partien', null, '', { h: 'h2' })}<ul class="linklist plainlist">${f.gespielt.map(g => `<li><b>${g.heim ? 'SC 1919 Merzenich' : esc(g.gegner)} – ${g.heim ? esc(g.gegner) : 'SC 1919 Merzenich'}${g.ergebnis ? ` <span class="res">${esc(g.ergebnis)}</span>` : ''}</b><small>${esc(fmt.date(g.start || g.datum))}${g.wettbewerb ? ' · ' + esc(g.wettbewerb) : ''}</small></li>`).join('')}</ul>` : ''}
      ${C.sectionHead('Weitere Mannschaften', 'Zweite, Dritte und Jugend', null, '', { h: 'h2' })}
      <ul class="linklist plainlist">${f.weitere.map(w => `<li><b>${w.url ? `<a href="${esc(w.url)}" target="_blank" rel="noopener nofollow">${esc(w.team)}</a>` : esc(w.team)}</b><small>${esc(w.liga)} · ${esc(w.stand)}${w.naechstes ? ` · nächstes Spiel ${esc(fmt.short(w.naechstes.start))} ${w.naechstes.heim ? 'gegen' : 'bei'} ${esc(w.naechstes.gegner)}` : ''}</small></li>`).join('')}</ul>
    </div>
    <aside class="sidebar">
      ${club ? `<div class="sidebox"><h3>Der Verein</h3><p class="p">${esc(club.description)}</p><a class="btn ghost block" href="${club.url}">Vereinsprofil</a>${club.website ? `<a class="btn ghost block" href="${esc(club.website)}" target="_blank" rel="noopener">Offizielle Website ↗</a>` : ''}</div>` : ''}
      ${news.length ? `<div class="sidebox"><h3>Meldungen<a href="/sport/">Sport</a></h3><ul class="linklist">${news.map(a => `<li><a href="${a.url}">${esc(a.title)}</a><small>${a.undated ? 'o. D.' : esc(fmt.short(a.date))}</small></li>`).join('')}</ul></div>` : ''}
      <div class="sidebox dark"><h3>Spielbericht einsenden</h3><p>Trainer, Betreuer und Fans: Ergebnis, Torschützen und ein Foto genügen. Die Redaktion macht daraus die Meldung.</p><a class="btn gold block" href="/meldung-senden/">Spielbericht senden</a></div>
      ${C.whatsappBox(site)}
      ${C.adSlot('sidebar', site)}
    </aside>
  </div>
</div></section>`;
  const ld = [{ '@context': 'https://schema.org', '@type': 'SportsTeam', name: 'SC 1919 Merzenich', sport: 'Fußball', url: site.url + '/sc-1919-merzenich/', memberOf: { '@type': 'SportsOrganization', name: f.liga }, location: { '@type': 'Place', name: f.heimspielort || 'Merzenich', address: { '@type': 'PostalAddress', addressLocality: 'Merzenich', postalCode: '52399', addressCountry: 'DE' } }, ...(club && club.website ? { sameAs: [club.website] } : {}) },
    ...f.spiele.map(s => ({ '@context': 'https://schema.org', '@type': 'SportsEvent', name: `${s.heim ? 'SC 1919 Merzenich' : s.gegner} – ${s.heim ? s.gegner : 'SC 1919 Merzenich'}`, startDate: isoLocal(s.datum), eventStatus: 'https://schema.org/EventScheduled', eventAttendanceMode: 'https://schema.org/OfflineEventAttendanceMode', ...(s.heim ? { location: { '@type': 'Place', name: f.heimspielort || 'Merzenich', address: { '@type': 'PostalAddress', addressLocality: 'Merzenich', postalCode: '52399', addressCountry: 'DE' } } } : {}), homeTeam: { '@type': 'SportsTeam', name: s.heim ? 'SC 1919 Merzenich' : s.gegner }, awayTeam: { '@type': 'SportsTeam', name: s.heim ? s.gegner : 'SC 1919 Merzenich' }, organizer: { '@type': 'Organization', name: 'Fußballkreis Düren' }, isAccessibleForFree: true }))];
  return layout(site, ctx, { title: 'SC 1919 Merzenich: Tabelle, Spielplan, Meldungen', description: `Kreisliga A: Tabelle und die nächsten Spiele des SC 1919 Merzenich, Stand ${fmt.date(f.stand)}. Dazu zweite und dritte Mannschaft und alle Meldungen.`, url: '/sc-1919-merzenich/', jsonld: ld, breadcrumbs: crumbs, nav: '/sport/', ort: 'merzenich', bodyClass: 'fussball' }, content);
}

/* ============================================================ Stellenmarkt */
function jobLd(site, j, ctx) {
  const place = ctx.placeBy(j.ort);
  return { '@context': 'https://schema.org', '@type': 'JobPosting', title: j.title, description: j.html || j.description, datePosted: isoLocal(j.posted), ...(j.validUntil ? { validThrough: isoLocal(j.validUntil) } : {}),
    employmentType: ({ Vollzeit: 'FULL_TIME', Teilzeit: 'PART_TIME', Ausbildung: 'INTERN', Minijob: 'PART_TIME', Praktikum: 'INTERN', Ehrenamt: 'VOLUNTEER' })[j.art] || 'OTHER',
    hiringOrganization: { '@type': 'Organization', name: j.betrieb, ...(j.url ? { sameAs: j.url } : {}) },
    jobLocation: { '@type': 'Place', address: { '@type': 'PostalAddress', streetAddress: j.address || undefined, addressLocality: place ? place.name : 'Merzenich', postalCode: '52399', addressRegion: 'NRW', addressCountry: 'DE' } },
    ...(j.gehalt ? { baseSalary: { '@type': 'MonetaryAmount', currency: 'EUR', value: { '@type': 'QuantitativeValue', value: j.gehalt, unitText: 'MONTH' } } } : {}), directApply: !!j.url, url: site.url + j.url };
}
export function jobsPage(ctx) {
  const { site, jobs } = ctx;
  const content = `${C.pageHead('Stellenmarkt', 'Jobs in Merzenich und Umgebung', 'Stellen, Ausbildungsplätze und Minijobs von Betrieben, Gemeinde, Kitas und Vereinen aus der Gemeinde Merzenich. Anzeigen sind gekennzeichnet; Ehrenamt und gemeinnützige Stellen sind kostenlos.', `<p class="count-line">${jobs.length} ${jobs.length === 1 ? 'Stelle' : 'Stellen'} · <a href="/werben/">Stelle inserieren</a></p>`, [{ name: 'Start', url: '/' }, { name: 'Jobs' }])}
<section class="section"><div class="shell"><div class="content-grid"><div class="event-list"><h2 class="sr-only">Stellenangebote</h2>
  ${jobs.map(j => `<article class="event-row job-row"><span class="d job-d"><b>${esc((j.art || 'Job').slice(0, 9))}</b></span><div class="info"><span class="eyebrow">${esc(j.betrieb)}${j.ort ? ' · ' + esc(ctx.placeName(j.ort)) : ''}</span><h3><a href="${j.url}">${esc(j.title)}</a></h3><div class="meta"><span>Veröffentlicht ${esc(fmt.date(j.posted))}</span>${j.validUntil ? `<span>bis ${esc(fmt.date(j.validUntil))}</span>` : ''}${j.start ? `<span>Beginn ${esc(j.start)}</span>` : ''}</div><p class="ev-desc">${esc(truncate(j.description, 170))}</p></div><div class="act"><a href="${j.url}">Details</a>${j.url_extern || j.url_ext ? '' : ''}</div></article>`).join('')}
  ${jobs.length === 0 ? '<p class="empty">Noch keine Stellen eingetragen. Betriebe, Gemeinde und Vereine aus Merzenich inserieren hier; gemeinnützige Stellen und Ehrenamt kostenlos. <a href="/werben/">Stelle inserieren</a></p>' : ''}
</div><aside class="sidebar"><div class="sidebox"><h3>Stelle inserieren</h3><p class="p">Stellenanzeigen für Betriebe aus der Gemeinde: 30 Tage im Stellenmarkt, im WhatsApp-Kanal und im Ressort Wirtschaft. Ehrenamt und gemeinnützige Träger kostenlos.</p><a class="btn block" href="/werben/">Anfrage senden</a></div>${C.whatsappBox(site)}${C.adSlot('sidebar', site)}</aside></div></div></section>`;
  return layout(site, ctx, { title: 'Jobs in Merzenich: Stellenmarkt', description: 'Stellen, Ausbildungsplätze und Minijobs aus der Gemeinde Merzenich und dem Kreis Düren.', url: '/jobs/', nav: '/wirtschaft/', breadcrumbs: [{ name: 'Start', url: '/' }, { name: 'Jobs', url: '/jobs/' }], bodyClass: 'jobs', jsonld: jobs.length ? [{ '@context': 'https://schema.org', '@type': 'ItemList', itemListElement: jobs.map((j, i) => ({ '@type': 'ListItem', position: i + 1, url: site.url + j.url, name: j.title })) }] : [] }, content);
}
export function jobPage(ctx, j) {
  const { site } = ctx; const place = ctx.placeBy(j.ort);
  const crumbs = [{ name: 'Start', url: '/' }, { name: 'Jobs', url: '/jobs/' }, { name: j.title }];
  const content = `<article class="article"><div class="article-head"><div class="shell">${C.crumbs(crumbs)}<div class="kick-row"><span class="kicker">${esc(j.art || 'Stelle')}${place ? `<span class="dist">${esc(place.name)}</span>` : ''}</span>${j.sponsored !== false ? '<span class="fbadge anzeige">Anzeige</span>' : ''}</div><h1>${esc(j.title)}</h1><p class="dek">${esc(j.betrieb)}${j.description ? ' · ' + esc(j.description) : ''}</p></div></div>
<div class="shell article-grid"><div class="article-body"><div class="event-facts"><div class="ef"><span class="ef-ic">${ICONS.bell}</span><div><b>${esc(j.betrieb)}</b><span>Arbeitgeber</span></div></div><div class="ef"><span class="ef-ic">${ICONS.pin}</span><div><b>${esc(j.address || (place ? place.name : 'Merzenich'))}</b><span>Arbeitsort</span></div></div>${j.start ? `<div class="ef"><span class="ef-ic">${ICONS.calendar}</span><div><b>${esc(j.start)}</b><span>Beginn</span></div></div>` : ''}${j.validUntil ? `<div class="ef"><span class="ef-ic">${ICONS.calendar}</span><div><b>${esc(fmt.date(j.validUntil))}</b><span>Bewerbung bis</span></div></div>` : ''}</div>
<div class="prose">${j.html}</div><div class="cta-row">${j.url ? `<a class="btn" href="${esc(j.url)}" target="_blank" rel="noopener sponsored">Zur Bewerbung ↗</a>` : ''}${j.email ? `<a class="btn ghost" href="mailto:${esc(j.email)}">Per E-Mail bewerben</a>` : ''}${j.phone ? `<a class="btn ghost" href="tel:${esc(j.phone.replace(/[^\d+]/g, ''))}">${esc(j.phone)}</a>` : ''}</div><div class="source-box"><b>Stellenanzeige.</b> Angaben laut Arbeitgeber, veröffentlicht ${esc(fmt.date(j.posted))}. Gekennzeichnete Anzeige ohne Einfluss auf die Redaktion.</div></div>
<aside class="sidebar">${C.whatsappBox(site)}${C.adSlot('sidebar', site)}</aside></div></article>`;
  return layout(site, ctx, { title: `${j.title} · ${j.betrieb}`, description: `${j.art || 'Stelle'} bei ${j.betrieb} in ${place ? place.name : 'Merzenich'}: ${j.description}`, url: j.url, breadcrumbs: crumbs, nav: '/wirtschaft/', ort: j.ort, bodyClass: 'job', jsonld: [jobLd(site, j, ctx)] }, content);
}
