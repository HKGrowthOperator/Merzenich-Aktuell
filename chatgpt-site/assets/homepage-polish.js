/*
 * Merzenich Aktuell — Bild- und Anzeigenqualitaet
 *
 * Regel: Ein zu kleines Motiv wird nicht grossgezogen. Auf Listen/Startseite
 * wird die Bildflaeche entfernt und die Meldung bewusst textlich gesetzt.
 * Ein Vereinslogo darf in einer kleinen Sportkarte stehen, aber nie als grosser
 * Aufmacher. Text-only "Anzeigen" werden nicht als bezahlte Werbung simuliert.
 */
(() => {
  'use strict';

  const qsa = (selector, root = document) => [...root.querySelectorAll(selector)];

  function maxSourceWidth(img) {
    const widths = [...String(img.getAttribute('srcset') || '').matchAll(/(?:^|,|\s)(\d+)w(?:\s|,|$)/g)]
      .map((match) => Number(match[1]))
      .filter(Number.isFinite);
    const queryWidth = String(img.currentSrc || img.src || '').match(/[?&]width=(\d+)/i);
    const declared = Number(img.getAttribute('width')) || 0;
    const natural = Number(img.naturalWidth) || 0;
    return Math.max(0, declared, natural, queryWidth ? Number(queryWidth[1]) : 0, ...widths);
  }

  function isLogo(img) {
    return /(?:logo|wappen|vereinslogo|crest)/i.test(`${img.currentSrc || ''} ${img.src || ''} ${img.alt || ''}`);
  }

  function minimumWidth(img, media) {
    const article = media.closest('article');
    const rendered = Math.max(media.getBoundingClientRect().width, img.getBoundingClientRect().width, 1);

    if (article?.classList.contains('front-lead')) return Math.max(1200, rendered * 1.5);
    if (article?.classList.contains('feed-lead')) return Math.max(1000, rendered * 1.5);
    if (article?.classList.contains('mosaic-lead') || article?.classList.contains('people-major') || article?.classList.contains('fire-major')) {
      return Math.max(760, rendered * 1.5);
    }
    if (article?.classList.contains('front-brief')) return Math.max(280, rendered * 1.5);
    return Math.max(560, rendered * 1.5);
  }

  function removeMedia(media, reason) {
    if (!media?.isConnected) return;
    const article = media.closest('article');
    if (!article) return;

    const holder = media.parentElement?.tagName === 'A' && media.parentElement.children.length === 1
      ? media.parentElement
      : media;
    holder.remove();
    article.classList.add('ma-no-image');
    article.dataset.imageRemoved = reason;
  }

  function auditImage(img) {
    if (!(img instanceof HTMLImageElement) || !img.isConnected) return;
    const media = img.closest('.media');
    if (!media) return;
    const article = media.closest('article');
    if (!article) return;

    const available = maxSourceWidth(img);
    const required = minimumWidth(img, media);
    const heroLogo = article.classList.contains('front-lead') && isLogo(img);

    // naturalWidth === 0 kann kurz waehrend des Ladens auftreten. Nur entfernen,
    // wenn wir aus srcset/width/URL bereits eine belastbare Quellbreite kennen.
    if (heroLogo || (available > 0 && available < required)) {
      removeMedia(media, heroLogo ? 'logo-not-hero' : `low-resolution-${Math.round(available)}-of-${Math.round(required)}`);
      return;
    }

    // Kleine Logos in Sport-/Vereinskarten bleiben erlaubt, werden aber nie gecroppt.
    if (isLogo(img)) media.classList.add('contain');
  }

  function auditImages() {
    const selectors = [
      '.home .front-lead .media img',
      '.home .front-brief .media img',
      '.home .news-mosaic .media img',
      '.home .people-grid .media img',
      '.home .fire-desk .media img',
      'article.feed-lead .media img',
      'article.feed-row .media img'
    ];
    qsa(selectors.join(',')).forEach(auditImage);
  }

  function removeTextOnlyAds() {
    // Der alte Recovery-Stand hatte KBS/AJ als Textkarten ohne Creative. Das
    // sieht wie eine Fake-Anzeige aus und wird deshalb auf allen Seiten entfernt.
    qsa('.managed-ad').forEach((ad) => {
      if (!ad.querySelector('img,picture,video')) ad.remove();
    });
    qsa('.ad-row-body').forEach((row) => {
      if (!row.querySelector('.managed-ad,.ma-ad,img,picture,video')) row.remove();
    });
  }

  function bookingSlot() {
    const a = document.createElement('a');
    a.className = 'ma-ad-booking-slot';
    a.href = '/werben/';
    a.setAttribute('aria-label', 'Werbeflaeche auf Merzenich Aktuell anfragen');
    a.innerHTML = `
      <span class="ma-ad-booking-slot__label">Werbeflaeche</span>
      <span class="ma-ad-booking-slot__body">
        <strong>Hier werben</strong>
        <span>Display-Banner im lokalen Nachrichtenumfeld</span>
        <small>Bannerplatz & Mediadaten anfragen</small>
      </span>`;
    return a;
  }

  function polishHomepageAds() {
    if (!document.body.classList.contains('home')) return;
    const rail = document.querySelector('.portal-ads');
    if (!rail || rail.dataset.maQualityAds === '1') return;

    // Ein echtes Motiv bleibt bestehen. Nur die alten Textanzeigen werden durch
    // einen klar als buchbar gekennzeichneten Slot ersetzt.
    const realCreative = rail.querySelector('img,picture,video');
    if (realCreative) return;

    rail.dataset.maQualityAds = '1';
    rail.replaceChildren(bookingSlot());
  }

  let resizeTimer = 0;
  function scheduleAudit(delay = 0) {
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(auditImages, delay);
  }

  function start() {
    removeTextOnlyAds();
    polishHomepageAds();

    // v20.js ersetzt Aufmacher/Zweitmeldung asynchron. Danach erst endgueltig
    // bewerten, damit kein gutes aktuelles Motiv wegen des alten HTML-Fallbacks
    // entfernt wird.
    scheduleAudit(1400);
    window.setTimeout(auditImages, 3600);

    const main = document.querySelector('main');
    if (main && 'MutationObserver' in window) {
      const observer = new MutationObserver(() => scheduleAudit(120));
      observer.observe(main, { childList: true, subtree: true, attributes: true, attributeFilter: ['src', 'srcset', 'class'] });
      window.setTimeout(() => observer.disconnect(), 6000);
    }

    window.addEventListener('resize', () => scheduleAudit(180), { passive: true });
    window.addEventListener('load', () => scheduleAudit(100), { once: true });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
})();
