/*
 * Redaktionsfelder speichern beim Tippen.
 *
 * WordPress' eigene Zwischenspeicherung sichert Titel und Text - Metafelder
 * nicht. Wer die Quellenangaben ausfuellt und dann die Sitzung verliert, hat
 * sie komplett neu einzugeben. Genau das ist mehrfach passiert.
 *
 * Absichtlich klein gehalten: kein Framework, kein Zustand, keine Warteschlange
 * ueber mehrere Felder. Jedes Feld schickt seinen eigenen Wert, eine Sekunde
 * nachdem das Tippen aufgehoert hat. Schlaegt das fehl, passiert nichts
 * Schlimmes - beim normalen Speichern geht alles den bisherigen Weg.
 */
(() => {
  'use strict';
  if (typeof maAutosave === 'undefined' || !maAutosave.postId) return;

  const wartezeit = 1000;
  const timer = new Map();

  function melden(feld, ok) {
    feld.classList.toggle('ma-feld-gespeichert', ok);
    if (ok) setTimeout(() => feld.classList.remove('ma-feld-gespeichert'), 1200);
  }

  function sichern(feld) {
    const key = feld.getAttribute('name');
    if (!key || key.indexOf('ma_') !== 0) return;

    const daten = new URLSearchParams();
    daten.set('action', 'ma_autosave_meta');
    daten.set('nonce', maAutosave.nonce);
    daten.set('post_id', String(maAutosave.postId));
    daten.set('key', key);
    daten.set('value', feld.value);

    fetch(maAutosave.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: daten.toString(),
    })
      .then((r) => (r.ok ? r.json() : null))
      .then((d) => melden(feld, !!(d && d.success)))
      .catch(() => {});
  }

  function beobachten(feld) {
    const key = feld.getAttribute('name') || '';
    if (key.indexOf('ma_') !== 0) return;
    // Haken bleiben bewusst aussen vor: eine Freigabe soll ein bewusster Klick
    // auf "Speichern" sein, kein Nebeneffekt des Tippens.
    if (feld.type === 'checkbox' || feld.type === 'radio') return;

    const ausloesen = () => {
      clearTimeout(timer.get(feld));
      timer.set(feld, setTimeout(() => sichern(feld), wartezeit));
    };
    feld.addEventListener('input', ausloesen);
    feld.addEventListener('change', () => { clearTimeout(timer.get(feld)); sichern(feld); });
  }

  document.querySelectorAll('input[name^="ma_"], select[name^="ma_"], textarea[name^="ma_"]').forEach(beobachten);
})();
