/* Merzenich-Cockpit (deploy/cockpit.mjs): Rathaus-Status, "in N Tagen" und
 * Wetter im Browser, nach Berliner Uhrzeit. Ohne Daten bleibt der Ausgangstext. */
(() => {
  'use strict';
  const cockpit = document.querySelector('.cockpit');
  if (!cockpit) return;
  const teile = (d) => Object.fromEntries(new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/Berlin', weekday: 'short', hour: '2-digit', minute: '2-digit', hour12: false, year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(d).map((p) => [p.type, p.value]));
  const WT = { Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6, Sun: 7 };
  const NAME = ['', 'Mo.', 'Di.', 'Mi.', 'Do.', 'Fr.', 'Sa.', 'So.'];
  const uhr = (hhmm) => hhmm.replace(/^0/, '').replace(/:00$/, '');
  const setze = (k, wert, klein) => { const el = cockpit.querySelector(`[data-cockpit="${k}"]`); if (!el) return; if (wert != null) el.querySelector('[data-cockpit-wert]').textContent = wert; if (klein != null) el.querySelector('[data-cockpit-klein]').textContent = klein; return el; };

  function rathaus() {
    const el = cockpit.querySelector('[data-cockpit="rathaus"]');
    if (!el) return;
    let zeiten; try { zeiten = JSON.parse(el.dataset.zeiten); } catch { return; }
    const t = teile(new Date());
    const wt = WT[t.weekday]; const jetzt = `${t.hour}:${t.minute}`;
    const heute = zeiten[wt] || [];
    const offen = heute.find(([a, b]) => jetzt >= a && jetzt < b);
    el.classList.toggle('ist-offen', !!offen);
    if (offen) { setze('rathaus', `Geöffnet bis ${uhr(offen[1])} Uhr`); return; }
    const spaeter = heute.find(([a]) => jetzt < a);
    if (spaeter) { setze('rathaus', `Geschlossen · öffnet ${uhr(spaeter[0])} Uhr`); return; }
    for (let i = 1; i <= 7; i++) { const w = ((wt - 1 + i) % 7) + 1; if (zeiten[w] && zeiten[w].length) { setze('rathaus', `Geschlossen · öffnet ${NAME[w]} ${uhr(zeiten[w][0][0])} Uhr`); return; } }
  }
  function termin() {
    const el = cockpit.querySelector('[data-cockpit="termin"]');
    if (!el || !el.dataset.start) return;
    const heute = teile(new Date()); const start = teile(new Date(el.dataset.start));
    const tage = Math.round((Date.UTC(start.year, start.month - 1, start.day) - Date.UTC(heute.year, heute.month - 1, heute.day)) / 864e5);
    const bis = tage <= 0 ? 'heute' : tage === 1 ? 'morgen' : `in ${tage} Tagen`;
    const klein = el.querySelector('[data-cockpit-klein]');
    if (klein && !klein.dataset.basis) klein.dataset.basis = klein.textContent;
    if (klein) klein.textContent = `${bis} · ${klein.dataset.basis}`;
  }
  function wetter() {
    const el = cockpit.querySelector('[data-cockpit="wetter"]');
    if (!el) return;
    const text = (c) => c === 0 ? 'klar' : [1, 2].includes(c) ? 'heiter' : c === 3 ? 'bedeckt' : [45, 48].includes(c) ? 'Nebel' : c >= 51 && c <= 57 ? 'Nieselregen' : (c >= 61 && c <= 67) || (c >= 80 && c <= 82) ? 'Regen' : (c >= 71 && c <= 77) || c === 85 || c === 86 ? 'Schnee' : c >= 95 ? 'Gewitter' : 'wechselhaft';
    fetch('/api/weather.json', { headers: { Accept: 'application/json' } }).then((r) => r.ok ? r.json() : null).then((d) => {
      const c = d && d.current; if (!c || !Number.isFinite(Number(c.temperature_2m))) return;
      const max = d.daily && d.daily.temperature_2m_max ? Math.round(d.daily.temperature_2m_max[0]) : null;
      const min = d.daily && d.daily.temperature_2m_min ? Math.round(d.daily.temperature_2m_min[0]) : null;
      setze('wetter', `${Math.round(c.temperature_2m)} °C, ${text(Number(c.weather_code))}`, max != null && min != null ? `heute ${min} bis ${max} °C · Open-Meteo` : 'Open-Meteo');
      el.hidden = false;
    }).catch(() => {});
  }
  rathaus(); termin(); wetter();
  setInterval(() => { rathaus(); termin(); }, 60000);
})();
