/* Werbesystem (KBS/Ordin 26.09.2026): rotiert alle Werbeflaechen einer Seite im
 * selben Takt. Motive und Takt kommen aus /assets/werbung.json (deploy/anzeigen.mjs).
 * Das ausgelieferte HTML traegt den Startzustand; ohne JavaScript bleibt er stehen.
 * Eine Flaeche pausiert, solange die Maus darauf steht oder ein Link darin den
 * Fokus hat. Unsichtbare Flaechen wechseln ohne Ueberblendung. */
(() => {
  'use strict';
  if (document.documentElement.dataset.werbung === 'aus') return;
  const flaechen = [...document.querySelectorAll('[data-werbung]')];
  if (!flaechen.length) return;
  const ruhig = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  fetch('/assets/werbung.json', { credentials: 'same-origin' })
    .then((r) => { if (!r.ok) throw new Error('werbung.json ' + r.status); return r.json(); })
    .then((d) => {
      const motive = Array.isArray(d.motive) ? d.motive : [];
      if (motive.length < 2) return;
      const takt = Math.max(6, Number(d.rotationSekunden) || 12) * 1000;
      const start = Math.floor(Date.now() / takt);
      const zustand = flaechen.map((el) => ({
        el, flaeche: el.querySelector('.werbung-flaeche'),
        versatz: Number(el.dataset.versatz) || 0, anzahl: Number(el.dataset.anzahl) || 1,
        sichtbar: false, pause: false, schritt: 0,
      })).filter((z) => z.flaeche);
      const beobachter = 'IntersectionObserver' in window
        ? new IntersectionObserver((e) => e.forEach((x) => { const z = zustand.find((y) => y.el === x.target); if (z) z.sichtbar = x.isIntersecting; }), { threshold: 0.2 })
        : null;
      for (const z of zustand) {
        if (beobachter) beobachter.observe(z.el); else z.sichtbar = true;
        z.el.addEventListener('mouseenter', () => { z.pause = true; });
        z.el.addEventListener('mouseleave', () => { z.pause = false; });
        z.el.addEventListener('focusin', () => { z.pause = true; });
        z.el.addEventListener('focusout', () => { z.pause = false; });
      }
      const html = (z, schritt) => Array.from({ length: z.anzahl }, (_, i) => motive[(z.versatz + schritt * z.anzahl + i) % motive.length].html).join('');
      function zeige(z, schritt) {
        if (z.schritt === schritt) return;
        z.schritt = schritt;
        const neu = html(z, schritt);
        if (!z.sichtbar || ruhig) { z.flaeche.innerHTML = neu; return; }
        z.flaeche.classList.add('wechselt');
        setTimeout(() => { z.flaeche.innerHTML = neu; requestAnimationFrame(() => z.flaeche.classList.remove('wechselt')); }, 230);
      }
      function weiter() {
        if (document.hidden) return;
        const schritt = Math.floor(Date.now() / takt) - start;
        for (const z of zustand) if (!z.pause) zeige(z, schritt);
      }
      setTimeout(() => { weiter(); setInterval(weiter, takt); }, takt - (Date.now() % takt) + 40);
      document.addEventListener('visibilitychange', weiter);
    })
    .catch(() => { /* Startzustand aus dem HTML bleibt stehen */ });
})();
