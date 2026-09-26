/* Foto des Tages (deploy/foto-des-tages.mjs): waehlt im Browser das Foto fuer
 * das heutige Datum nach Berliner Kalender. Datierte Einsendung vor Ortsansicht. */
(() => {
  'use strict';
  const sektion = document.querySelector('[data-foto-des-tages]');
  if (!sektion) return;
  const heute = new Intl.DateTimeFormat('en-CA', { timeZone: 'Europe/Berlin' }).format(new Date());
  const zeit = document.getElementById('foto-des-tages-datum');
  if (zeit && zeit.getAttribute('datetime') === heute) return;
  fetch('/assets/foto-des-tages.json', { credentials: 'same-origin' }).then((r) => r.ok ? r.json() : null).then((d) => {
    if (!d || !Array.isArray(d.reihe) || !d.reihe.length) return;
    const tag = Math.floor(Date.parse(heute + 'T00:00:00Z') / 864e5);
    const f = (d.eintraege || []).find((e) => e.datum === heute) || d.reihe[tag % d.reihe.length];
    const img = document.getElementById('foto-des-tages-bild');
    if (img) { img.src = f.src; img.alt = f.alt; }
    const setze = (sel, t) => { const el = sektion.querySelector(sel); if (el) el.textContent = t; };
    setze('[data-ansicht-ort]', f.ort || 'Gemeinde Merzenich');
    setze('[data-ansicht-text]', f.alt + '.');
    setze('#foto-des-tages-credit', 'Foto: ' + f.credit);
    if (zeit) { zeit.setAttribute('datetime', heute); zeit.textContent = new Intl.DateTimeFormat('de-DE', { timeZone: 'UTC', weekday: 'long', day: 'numeric', month: 'long' }).format(new Date(heute + 'T12:00:00Z')); }
  }).catch(() => {});
})();
