/* Werbesystem: rotiert alle Werbeflaechen einer Seite im selben Takt, mit den
 * Motiven und der Gestaltung der Anzeigenrotation vom 24.09.2026. Die
 * Zeile .werbung-credit (Fotonachweis) wechselt mit. Motive und
 * Takt kommen aus /assets/werbung.json (deploy/anzeigen.mjs), je Motiv als
 * fertiges HTML fuer das Format band (Baender, Artikel) und gap (Spalten).
 * Der Takt ist zeitbasiert: Ein Reload beginnt nicht wieder bei Motiv 1.
 * Eine Flaeche pausiert, solange die Maus darauf steht oder ein Link darin den
 * Fokus hat. Ohne JavaScript bleibt das Startmotiv aus dem HTML stehen.
 * Vom Endlos-Scrollen nachgeladene Flaechen (Ereignis ma:nachgeladen) laufen mit. */
(() => {
  'use strict';
  if (document.documentElement.dataset.werbung === 'aus') return;
  const ruhig = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  const zustand = [];
  let daten = null, holen = null;
  // Endlos-Scrollen (endlos.js): nachgeladene Flaechen laufen im selben Takt mit.
  function flaechenIn(knoten) {
    return knoten.flatMap((k) => !k.querySelectorAll ? [] : k.matches('aside[data-werbung]') ? [k] : [...k.querySelectorAll('aside[data-werbung]')]);
  }
  function zeige(z, schritt, sofort) {
    const m = daten.motive[((schritt + z.versatz) % daten.motive.length + daten.motive.length) % daten.motive.length];
    if (!m || m.id === z.aktuell) return;
    z.aktuell = m.id;
    const html = m[z.format] || m.band;
    // Urhebernachweis des Fotos wechselt mit dem Motiv (CC BY-SA).
    if (z.credit) z.credit.innerHTML = m.credit || '';
    if (sofort || ruhig) { z.flaeche.innerHTML = html; return; }
    z.flaeche.classList.add('is-changing');
    setTimeout(() => { z.flaeche.innerHTML = html; requestAnimationFrame(() => z.flaeche.classList.remove('is-changing')); }, 180);
  }
  function weiter(sofort) {
    if (document.hidden || !daten) return;
    const schritt = Math.floor(Date.now() / daten.takt);
    for (const z of zustand) if (!z.pause) zeige(z, schritt, sofort);
  }
  function aufnehmen(els) {
    const neu = [];
    for (const el of els) {
      if (zustand.some((z) => z.el === el)) continue;
      const z = { el, flaeche: el.querySelector('.werbung-flaeche'), credit: el.querySelector('.werbung-credit'),
        format: el.dataset.format === 'gap' ? 'gap' : 'band', versatz: Number(el.dataset.versatz) || 0, pause: false, aktuell: '' };
      if (!z.flaeche) continue;
      const erstes = z.flaeche.querySelector('[data-motiv]');
      z.aktuell = erstes ? erstes.dataset.motiv : '';
      el.addEventListener('mouseenter', () => { z.pause = true; });
      el.addEventListener('mouseleave', () => { z.pause = false; });
      el.addEventListener('focusin', () => { z.pause = true; });
      el.addEventListener('focusout', () => { z.pause = false; });
      zustand.push(z); neu.push(z);
    }
    if (!neu.length) return;
    if (daten) { const schritt = Math.floor(Date.now() / daten.takt); for (const z of neu) zeige(z, schritt, true); return; }
    if (holen) return;
    holen = fetch('/assets/werbung.json', { credentials: 'same-origin' })
      .then((r) => { if (!r.ok) throw new Error('werbung.json ' + r.status); return r.json(); })
      .then((d) => {
        const motive = Array.isArray(d.motive) ? d.motive : [];
        if (motive.length < 2) return;
        daten = { motive, takt: Math.max(6, Number(d.rotationSekunden) || 14) * 1000 };
        weiter(true);
        setTimeout(() => { weiter(false); setInterval(() => weiter(false), daten.takt); }, daten.takt - (Date.now() % daten.takt) + 30);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) weiter(true); });
      })
      .catch(() => { /* Startmotiv aus dem HTML bleibt stehen */ });
  }
  aufnehmen([...document.querySelectorAll('aside[data-werbung]')]);
  document.addEventListener('ma:nachgeladen', (e) => aufnehmen(flaechenIn((e.detail && e.detail.knoten) || [])));
})();
