/* Merzenich jetzt (deploy/cockpit.mjs): Datum, Rathaus-Status, „heute/morgen“
 * beim Termin und Wetter im Browser, nach Berliner Uhrzeit. Ohne Daten bleibt
 * der Ausgangstext aus dem HTML stehen. */
(() => {
  'use strict';
  const jetzt = document.querySelector('.cockpit');
  if (!jetzt) return;
  const TZ = 'Europe/Berlin';
  const teile = (d) => Object.fromEntries(new Intl.DateTimeFormat('en-GB', { timeZone: TZ, weekday: 'short', hour: '2-digit', minute: '2-digit', hour12: false, year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(d).map((p) => [p.type, p.value]));
  const WT = { Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6, Sun: 7 };
  const NAME = ['', 'Mo.', 'Di.', 'Mi.', 'Do.', 'Fr.', 'Sa.', 'So.'];
  const uhr = (hhmm) => hhmm.replace(/^0/, '').replace(/:00$/, '');
  const text = (el, sel, wert) => { const z = el && el.querySelector(sel); if (z && wert != null) z.textContent = wert; };

  function datum() {
    text(jetzt, '[data-cockpit-datum]', new Intl.DateTimeFormat('de-DE', { timeZone: TZ, weekday: 'long', day: 'numeric', month: 'long' }).format(new Date()));
  }
  function rathaus() {
    const el = jetzt.querySelector('[data-cockpit="rathaus"]');
    if (!el) return;
    let zeiten; try { zeiten = JSON.parse(el.dataset.zeiten); } catch { return; }
    const t = teile(new Date());
    const wt = WT[t.weekday]; const nun = `${t.hour}:${t.minute}`;
    const heute = zeiten[wt] || [];
    const offen = heute.find(([a, b]) => nun >= a && nun < b);
    el.classList.toggle('ist-offen', !!offen);
    el.classList.add('ist-bekannt');
    if (offen) { text(el, '[data-cockpit-wert]', 'Rathaus geöffnet'); text(el, '[data-cockpit-naechst]', `heute bis ${uhr(offen[1])} Uhr`); return; }
    text(el, '[data-cockpit-wert]', 'Rathaus geschlossen');
    const spaeter = heute.find(([a]) => nun < a);
    if (spaeter) { text(el, '[data-cockpit-naechst]', `öffnet heute um ${uhr(spaeter[0])} Uhr`); return; }
    for (let i = 1; i <= 7; i++) {
      const w = ((wt - 1 + i) % 7) + 1;
      if (zeiten[w] && zeiten[w].length) { text(el, '[data-cockpit-naechst]', `öffnet ${i === 1 ? 'morgen' : NAME[w]} um ${uhr(zeiten[w][0][0])} Uhr`); return; }
    }
  }
  function termin() {
    const el = jetzt.querySelector('[data-cockpit="termin"]');
    if (!el || !el.dataset.start) return;
    const s = new Date(el.dataset.start);
    const heute = teile(new Date()); const start = teile(s);
    const tage = Math.round((Date.UTC(start.year, start.month - 1, start.day) - Date.UTC(heute.year, heute.month - 1, heute.day)) / 864e5);
    const zeit = `${uhr(`${start.hour}:${start.minute}`)} Uhr`;
    if (tage <= 0) text(el, '[data-cockpit-wert]', `Heute, ${zeit}`);
    else if (tage === 1) text(el, '[data-cockpit-wert]', `Morgen, ${zeit}`);
    else if (tage < 7) text(el, '[data-cockpit-wert]', `${new Intl.DateTimeFormat('de-DE', { timeZone: TZ, weekday: 'long' }).format(s)}, ${zeit}`);
  }
  function wetter() {
    const el = jetzt.querySelector('[data-cockpit="wetter"]');
    if (!el) return;
    const himmel = (c) => c === 0 ? 'klar' : [1, 2].includes(c) ? 'heiter' : c === 3 ? 'bedeckt' : [45, 48].includes(c) ? 'Nebel' : c >= 51 && c <= 57 ? 'Nieselregen' : (c >= 61 && c <= 67) || (c >= 80 && c <= 82) ? 'Regen' : (c >= 71 && c <= 77) || c === 85 || c === 86 ? 'Schnee' : c >= 95 ? 'Gewitter' : 'wechselhaft';
    fetch('/api/weather.json', { headers: { Accept: 'application/json' } }).then((r) => r.ok ? r.json() : null).then((d) => {
      const c = d && d.current; if (!c || !Number.isFinite(Number(c.temperature_2m))) return;
      const max = d.daily && d.daily.temperature_2m_max ? Math.round(d.daily.temperature_2m_max[0]) : null;
      const min = d.daily && d.daily.temperature_2m_min ? Math.round(d.daily.temperature_2m_min[0]) : null;
      text(el, '[data-cockpit-wert]', `${Math.round(c.temperature_2m)}°`);
      const klein = el.querySelector('[data-cockpit-klein]');
      if (klein) {
        // Zwei Teile, damit „16 bis 28 °C“ nie auseinanderbricht.
        const a = document.createElement('span'); a.textContent = himmel(Number(c.weather_code));
        klein.replaceChildren(a);
        if (max != null && min != null) { const b = document.createElement('span'); b.className = 'mj-spanne'; b.textContent = `heute ${min} bis ${max} °C`; klein.append(b); }
      }
      el.title = 'Wetterdaten: Open-Meteo';
      el.hidden = false;
    }).catch(() => {});
  }
  datum(); rathaus(); termin(); wetter();
  setInterval(() => { datum(); rathaus(); termin(); }, 60000);
})();
