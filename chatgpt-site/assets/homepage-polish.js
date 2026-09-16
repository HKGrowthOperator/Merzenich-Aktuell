/*
 * Homepage polish 16.09.2026
 * Nur progressive Korrekturen am ausgelieferten Stand. Reale CMS-Anzeigen mit
 * Bild werden niemals ueberschrieben.
 */
(() => {
  'use strict';

  function maxSrcsetWidth(img) {
    const srcset = img.getAttribute('srcset') || '';
    const widths = [...srcset.matchAll(/\s(\d+)w(?:,|$)/g)].map((m) => Number(m[1]));
    if (widths.length) return Math.max(...widths);
    const w = Number(img.getAttribute('width'));
    return Number.isFinite(w) ? w : 0;
  }

  function markImageQuality() {
    if (!document.body.classList.contains('home')) return;
    document.querySelectorAll('.front-lead .media img,.news-mosaic .media img,.people-grid .media img').forEach((img) => {
      const media = img.closest('.media');
      if (!media) return;
      const max = maxSrcsetWidth(img);
      const isLogo = /logo|wappen|vereinslogo/i.test(`${img.currentSrc} ${img.src} ${img.alt}`);
      if ((max > 0 && max < 640) || isLogo) media.classList.add('ma-lowres-media');
    });
  }

  function polishAds() {
    if (!document.body.classList.contains('home')) return;
    const rail = document.querySelector('.portal-ads');
    if (!rail || rail.dataset.maPolished === '1') return;

    // Sobald das CMS ein echtes Creative liefert, bleibt es unangetastet.
    if (rail.querySelector('img')) return;

    rail.dataset.maPolished = '1';
    rail.innerHTML = `
      <div class="ma-display-ad" data-ad-slot="homepage_sidebar_top">
        <span class="ma-display-ad__label">Anzeige</span>
        <a href="https://kbs-management.tv/" target="_blank" rel="noopener sponsored" aria-label="Anzeige von KBS Management GmbH">
          <span class="ma-display-ad__creative">
            <img src="https://kbs-management.tv/wp-content/themes/kbs/kbs-logo.png" alt="KBS Management GmbH" loading="lazy" decoding="async" referrerpolicy="no-referrer">
            <span class="ma-display-ad__copy">
              <strong>KBS Management</strong>
              <span>App · Software · Broadcast · IT Consultancy</span>
              <em>Unternehmen ansehen</em>
            </span>
          </span>
        </a>
      </div>
      <a class="ma-ad-booking" href="/werben/" data-ad-slot="homepage_sidebar_middle">
        <span>Dieser Platz wird im WordPress-Backend mit einem echten Banner belegt.</span>
        <strong>Werbefläche anfragen</strong>
      </a>`;
  }

  function start() {
    markImageQuality();
    polishAds();

    // v20.js priorisiert redaktionelle Slots asynchron. Nach dessen Aktualisierung
    // die Bildqualitaetsklasse erneut setzen, ohne Inhalte umzuschreiben.
    const lead = document.querySelector('.front-lead');
    if (lead && 'MutationObserver' in window) {
      const observer = new MutationObserver(markImageQuality);
      observer.observe(lead, { childList: true, subtree: true, attributes: true, attributeFilter: ['src', 'srcset', 'alt', 'class'] });
      window.setTimeout(() => observer.disconnect(), 5000);
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
})();
