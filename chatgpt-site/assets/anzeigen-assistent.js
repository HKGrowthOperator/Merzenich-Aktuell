/* Anzeige-Assistent (deploy/anzeigen-assistent.mjs): ein Schritt nach dem
 * anderen. Der Stand steht in der Adresse (?art=...&schritt=3), so fuehrt die
 * Zurueck-Taste des Browsers einen Schritt zurueck. Direktlinks wie
 * /anzeigen/aufgeben/?art=Traueranzeige&trauerform=Danksagung waehlen vor. */
(() => {
  'use strict';
  const form = document.querySelector('form[data-assistent]');
  if (!form) return;
  form.noValidate = true;
  const schritte = [...form.querySelectorAll('.anz-schritt[data-schritt]')];
  const fortschritt = form.querySelector('.anz-fortschritt');
  const nav = form.querySelector('.anz-navigation');
  const zurueck = form.querySelector('[data-zurueck]');
  const weiter = form.querySelector('[data-weiter]');
  const uebersicht = form.querySelector('.anz-uebersicht');
  const letzte = schritte.length;
  let aktuell = 1;

  const art = () => { const r = form.querySelector('input[name="art"]:checked'); return r ? r.value : ''; };
  const teileZeigen = () => {
    const a = art();
    form.querySelectorAll('.anz-teil').forEach((t) => {
      const an = t.dataset.art === a;
      t.hidden = !an;
      t.querySelectorAll('input,select,textarea').forEach((f) => { f.disabled = !an; });
    });
  };
  const gruppenOffen = () => [...form.querySelectorAll(`.anz-teil[data-art="${CSS.escape(art())}"] .anz-gruppe`)]
    .filter((g) => !g.querySelector('input:checked'));

  function pruefe(n) {
    const s = schritte[n - 1];
    if (n === 1 && !art()) { melde(s, 'Bitte wählen Sie aus, was Sie veröffentlichen möchten.'); return false; }
    if (n === 2) {
      const offen = gruppenOffen();
      if (offen.length) { melde(offen[0], 'Bitte wählen Sie eine Möglichkeit.'); return false; }
    }
    for (const f of s.querySelectorAll('input,select,textarea')) {
      if (f.disabled || f.closest('[hidden]')) continue;
      if (!f.checkValidity()) { f.reportValidity(); return false; }
    }
    melde(s, '');
    return true;
  }
  function melde(el, text) {
    let p = el.querySelector(':scope > .anz-fehler');
    if (!text) { if (p) p.remove(); return; }
    if (!p) { p = document.createElement('p'); p.className = 'anz-fehler'; p.setAttribute('role', 'alert'); el.append(p); }
    p.textContent = text;
    (el.querySelector('input') || el).focus?.();
  }

  function zusammenfassen() {
    if (!uebersicht) return;
    const zeilen = [];
    const wert = (n) => { const f = form.querySelector(`[name="${n}"]:checked, select[name="${n}"], input[name="${n}"]:not([type=radio]):not([type=checkbox])`); return f && !f.disabled ? f.value.trim() : ''; };
    const a = art(); if (a) zeilen.push(['Art', a]);
    for (const n of ['angebot', 'objekt', 'trauerform', 'anlass', 'format', 'ortsteil', 'verstorben', 'namen', 'firma']) { const v = wert(n); if (v) zeilen.push([form.querySelector(`[name="${n}"]`)?.closest('fieldset.anz-gruppe')?.querySelector('legend')?.textContent || form.querySelector(`[name="${n}"]`)?.closest('label')?.firstChild?.textContent?.trim() || n, v]); }
    const bilder = form.querySelector('input[name="bild"]'); if (bilder && bilder.files && bilder.files.length) zeilen.push(['Bilder', `${bilder.files.length} ausgewählt`]);
    uebersicht.innerHTML = '';
    const h = document.createElement('p'); h.className = 'anz-uebersicht__titel'; h.textContent = 'Ihre Angaben im Überblick'; uebersicht.append(h);
    const dl = document.createElement('dl');
    for (const [k, v] of zeilen) { const dt = document.createElement('dt'); dt.textContent = k; const dd = document.createElement('dd'); dd.textContent = v; dl.append(dt, dd); }
    uebersicht.append(dl);
    uebersicht.hidden = false;
  }

  function zeige(n, { adresse = 'push', fokus = true } = {}) {
    aktuell = Math.min(Math.max(1, n), letzte);
    teileZeigen();
    schritte.forEach((s, i) => { s.hidden = i + 1 !== aktuell; });
    fortschritt.querySelectorAll('li').forEach((li) => {
      const k = Number(li.dataset.fuer);
      li.classList.toggle('ist-aktuell', k === aktuell);
      li.classList.toggle('ist-fertig', k < aktuell);
      if (k === aktuell) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current');
    });
    zurueck.hidden = aktuell === 1;
    weiter.hidden = aktuell === letzte;
    if (aktuell === letzte) zusammenfassen();
    if (adresse) {
      const q = new URLSearchParams(location.search);
      const a = art(); if (a) q.set('art', a); else q.delete('art');
      q.set('schritt', String(aktuell));
      const url = `${location.pathname}?${q}#formular`;
      if (adresse === 'push') history.pushState({ schritt: aktuell }, '', url); else history.replaceState({ schritt: aktuell }, '', url);
    }
    if (fokus) { const l = schritte[aktuell - 1].querySelector('legend'); l.tabIndex = -1; l.focus({ preventScroll: true }); form.scrollIntoView({ block: 'start', behavior: 'smooth' }); }
  }

  // Vorauswahl aus der Adresse (Direktlinks von Trauer-, Familien-, Immobilien- und Werbeseite).
  const q = new URLSearchParams(location.search);
  const setze = (name, wert) => { if (!wert) return false; const r = [...form.querySelectorAll(`input[name="${name}"]`)].find((x) => x.value === wert); if (r) { r.checked = true; return true; } return false; };
  let start = 1;
  if (setze('art', q.get('art'))) {
    start = 2;
    teileZeigen();
    const gewaehlt = ['angebot', 'objekt', 'trauerform', 'anlass', 'format'].map((n) => setze(n, q.get(n))).some(Boolean);
    if (gewaehlt && !gruppenOffen().length) start = 3;
  }
  const s = Number(q.get('schritt'));
  if (s > 1 && s <= letzte && art()) start = Math.min(s, start === 1 ? 1 : letzte);

  fortschritt.hidden = false;
  nav.hidden = false;
  form.classList.add('ist-assistent');
  zeige(start, { adresse: 'replace', fokus: false });

  weiter.addEventListener('click', () => { if (pruefe(aktuell)) zeige(aktuell + 1); });
  zurueck.addEventListener('click', () => history.back());
  window.addEventListener('popstate', (e) => { const n = (e.state && e.state.schritt) || Number(new URLSearchParams(location.search).get('schritt')) || 1; zeige(n, { adresse: false }); });
  // Art gewaehlt: direkt weiter, der Wunsch war "gefuehrt".
  form.addEventListener('change', (e) => {
    const t = e.target;
    if (t.name === 'art') { teileZeigen(); setTimeout(() => zeige(2), 160); return; }
    if (aktuell === 2 && t.type === 'radio' && !gruppenOffen().length) setTimeout(() => zeige(3), 200);
  });
  form.addEventListener('submit', (e) => {
    for (let n = 1; n <= letzte; n++) { if (!pruefe(n)) { e.preventDefault(); zeige(n); pruefe(n); return; } }
  });
})();
