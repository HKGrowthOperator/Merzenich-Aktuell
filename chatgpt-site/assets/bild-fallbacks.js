/*
 * Merzenich Aktuell — technischer Browser-Fallback für bereits zugewiesene Bilder.
 *
 * Redaktionelle Bildentscheidungen fallen ausschließlich beim Build.
 * Dieses Skript rotiert NICHT beim Reload und fügt keine Bilder nachträglich ein.
 * Nur wenn ein ausgeliefertes Bild technisch nicht lädt, wird innerhalb desselben
 * thematischen Pools das nächste gültige Symbolbild versucht.
 */
(() => {
  'use strict';

  const LIBRARY_URL = '/data/editorial-images/editorial-images.json';
  let libraryPromise = null;

  const text = (el) => (el?.textContent || '').trim();
  const norm = (s) => String(s || '').toLocaleLowerCase('de-DE').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/ß/g, 'ss');

  function library() {
    if (!libraryPromise) {
      libraryPromise = fetch(LIBRARY_URL, { credentials: 'same-origin', cache: 'force-cache' })
        .then((r) => { if (!r.ok) throw new Error(`Image library ${r.status}`); return r.json(); })
        .then((d) => Array.isArray(d.images) ? d.images : []);
    }
    return libraryPromise;
  }

  function kontext(root) {
    const section = document.querySelector('meta[property="article:section"]')?.content || '';
    const keywords = document.querySelector('meta[name="news_keywords"]')?.content || '';
    const heading = text(root?.querySelector?.('h1,h2,h3')) || document.title;
    const kicker = text(root?.querySelector?.('.kicker,.eyebrow,.markt-art'));
    const body = text(root?.querySelector?.('.dek,.prose,.ev-desc,.meta,.location-line'));
    return norm(`${section} ${keywords} ${heading} ${kicker} ${body} ${location.pathname}`);
  }

  function poolFuer(img) {
    const explizit = img.dataset.editorialPool || img.closest('[data-editorial-pool]')?.dataset.editorialPool;
    if (explizit) return explizit;
    const m = String(img.currentSrc || img.src || '').match(/\/assets\/symbolbilder\/([^/]+)\//);
    if (m) return m[1];
    const t = kontext(img.closest('article,.feed-row,.feed-lead,.markt-row') || document.body);
    const p = norm(location.pathname);
    if (p.includes('/traueranzeigen/') || /trauer|nachruf|gedenk|verstorben/.test(t)) return 'trauer';
    if (p.includes('/familienanzeigen/') || /hochzeit|trauung|geburt|jubilaum/.test(t)) return 'familie';
    if (p.includes('/jobs/') || /stellenmarkt|stellenangebot|ausbildung|karriere/.test(t)) return 'jobs';
    if (p.includes('/immobilien/') || /immobil|wohnung|wohnhaus|miete/.test(t)) return 'immobilien';
    if (p.includes('/blaulicht/')) return /feuerwehr|brand|losch|rauch|drehleiter/.test(t) ? 'feuerwehr' : 'polizei';
    if (/verkehr|sperrung|baustelle|umleitung|strasse|bahn|bus|opnv/.test(t)) return 'verkehr';
    if (p.includes('/sport/') || /fussball|spieltag|kreisliga|sc 1919|fc golzheim/.test(t)) return 'sport';
    if (p.includes('/vereine/') || /verein|ehrenamt|karneval|schutzen/.test(t)) return 'vereine';
    if (p.includes('/termine/') || /veranstaltung|konzert|markt|kirmes|fest/.test(t)) return 'veranstaltungen';
    if (p.includes('/kultur/') || /kultur|theater|kunst|ausstellung|lesung/.test(t)) return 'kultur';
    if (/schule|kita|bildung|unterricht/.test(t)) return 'schule';
    if (/kirche|gottesdienst|pfarr|konfirmation|seelsorge/.test(t)) return 'kirche';
    if (p.includes('/wirtschaft/') || /wirtschaft|unternehmen|betrieb|gewerbe/.test(t)) return 'wirtschaft';
    if (p.includes('/rathaus/') || /gemeinde|verwaltung|rat|burgermeister/.test(t)) return 'gemeinde';
    return 'leben';
  }

  function setzeCaption(img, motiv) {
    const figure = img.closest('.art-figure');
    if (!figure) return;
    figure.dataset.symbolbild = motiv.pool;
    figure.dataset.editorialImageId = motiv.id;
    figure.dataset.editorialPool = motiv.pool;
    let caption = figure.querySelector('figcaption');
    if (!caption) { caption = document.createElement('figcaption'); figure.append(caption); }
    caption.replaceChildren();
    const a = document.createElement('span');
    const badge = document.createElement('span'); badge.className = 'figure-badge'; badge.textContent = 'Symbolbild';
    a.append(badge, document.createTextNode(` · ${motiv.alt}. Kein Foto vom Ereignis.`));
    const b = document.createElement('span'); b.textContent = `Bild: ${motiv.credit} · ${motiv.license}`;
    caption.append(a, b);
  }

  function verstecke(img) {
    img.dataset.maFallbackExhausted = '1';
    const box = img.closest('.media,.markt-thumb,.feed-img,.art-figure'); if (box) box.hidden = true;
    const article = img.closest('article'); if (article) article.classList.add('no-image');
  }

  async function ersetzeDefektesBild(img) {
    if (!(img instanceof HTMLImageElement) || img.dataset.maFallbackBusy === '1' || img.dataset.maFallbackExhausted === '1') return;
    img.dataset.maFallbackBusy = '1';
    try {
      const alle = await library();
      const pool = poolFuer(img);
      const motive = alle.filter((m) => m.pool === pool && m.src && m.id);
      if (!motive.length) { verstecke(img); return; }
      const aktuelleId = img.dataset.editorialImageId || img.closest('[data-editorial-image-id]')?.dataset.editorialImageId || '';
      const start = Math.max(-1, motive.findIndex((m) => m.id === aktuelleId));
      const bisher = Number(img.dataset.maFallbackAttempt || '0');
      if (!Number.isFinite(bisher) || bisher >= motive.length) { verstecke(img); return; }
      const motiv = motive[(start + bisher + 1) % motive.length];
      img.dataset.maFallbackAttempt = String(bisher + 1);
      img.dataset.editorialPool = pool;
      img.dataset.editorialImageId = motiv.id;
      img.dataset.maSymbolbild = '1';
      img.removeAttribute('srcset'); img.removeAttribute('sizes');
      img.alt = motiv.alt; img.src = motiv.src; img.decoding = 'async';
      const badge = img.closest('.media,.markt-thumb')?.querySelector('.badge,.markt-thumb__badge'); if (badge) badge.textContent = 'Symbolbild';
      setzeCaption(img, motiv);
    } catch {
      verstecke(img);
    } finally {
      img.dataset.maFallbackBusy = '0';
    }
  }

  function pruefe(root = document) {
    root.querySelectorAll?.('img[data-editorial-image],img[data-editorial-image-id],img[data-ma-symbolbild="1"]').forEach((img) => {
      if (img.complete && img.naturalWidth === 0) ersetzeDefektesBild(img);
    });
  }

  document.addEventListener('error', (event) => {
    const img = event.target;
    if (!(img instanceof HTMLImageElement)) return;
    if (img.matches('[data-editorial-image],[data-editorial-image-id],[data-ma-symbolbild="1"]')) ersetzeDefektesBild(img);
  }, true);

  function start() {
    pruefe();
    const observer = new MutationObserver((mutations) => {
      if (mutations.some((m) => m.addedNodes?.length)) requestAnimationFrame(() => pruefe());
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();

  window.MerzenichStartbilder = Object.freeze({
    checkedAt: '2026-09-18',
    libraryUrl: LIBRARY_URL,
    load: library,
    runtimeRotation: false,
  });
})();
