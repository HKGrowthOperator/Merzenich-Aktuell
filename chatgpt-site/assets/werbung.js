/* Werbesystem: rotiert alle Werbeflaechen einer Seite im selben Takt, mit den
 * Motiven und der Gestaltung der Anzeigenrotation vom 24.09.2026. Motive und
 * Takt kommen aus /assets/werbung.json (deploy/anzeigen.mjs), je Motiv als
 * fertiges HTML fuer das Format band (Baender, Artikel) und gap (Spalten).
 * Der Takt ist zeitbasiert: Ein Reload beginnt nicht wieder bei Motiv 1.
 * Eine Flaeche pausiert, solange die Maus darauf steht oder ein Link darin den
 * Fokus hat. Ohne JavaScript bleibt das Startmotiv aus dem HTML stehen. */
(() => {
  'use strict';
  if (document.documentElement.dataset.werbung === 'aus') return;
  const flaechen = [...document.querySelectorAll('aside[data-werbung]')];
  if (!flaechen.length) return;
  const ruhig = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  fetch('/assets/werbung.json', { credentials: 'same-origin' })
    .then((r) => { if (!r.ok) throw new Error('werbung.json ' + r.status); return r.json(); })
    .then((d) => {
      const motive = Array.isArray(d.motive) ? d.motive : [];
      if (motive.length < 2) return;
      const takt = Math.max(6, Number(d.rotationSekunden) || 14) * 1000;
      const zustand = flaechen.map((el) => ({
        el, flaeche: el.querySelector('.werbung-flaeche'),
        format: el.dataset.format === 'gap' ? 'gap' : 'band',
        versatz: Number(el.dataset.versatz) || 0, pause: false, aktuell: '',
      })).filter((z) => z.flaeche);
      for (const z of zustand) {
        const erstes = z.flaeche.querySelector('[data-motiv]');
        z.aktuell = erstes ? erstes.dataset.motiv : '';
        z.el.addEventListener('mouseenter', () => { z.pause = true; });
        z.el.addEventListener('mouseleave', () => { z.pause = false; });
        z.el.addEventListener('focusin', () => { z.pause = true; });
        z.el.addEventListener('focusout', () => { z.pause = false; });
      }
      function zeige(z, schritt, sofort) {
        const m = motive[((schritt + z.versatz) % motive.length + motive.length) % motive.length];
        if (!m || m.id === z.aktuell) return;
        z.aktuell = m.id;
        const html = m[z.format] || m.band;
        if (sofort || ruhig) { z.flaeche.innerHTML = html; return; }
        z.flaeche.classList.add('is-changing');
        setTimeout(() => { z.flaeche.innerHTML = html; requestAnimationFrame(() => z.flaeche.classList.remove('is-changing')); }, 180);
      }
      function weiter(sofort) {
        if (document.hidden) return;
        const schritt = Math.floor(Date.now() / takt);
        for (const z of zustand) if (!z.pause) zeige(z, schritt, sofort);
      }
      weiter(true);
      setTimeout(() => { weiter(false); setInterval(() => weiter(false), takt); }, takt - (Date.now() % takt) + 30);
      document.addEventListener('visibilitychange', () => { if (!document.hidden) weiter(true); });
    })
    .catch(() => { /* Startmotiv aus dem HTML bleibt stehen */ });
})();
