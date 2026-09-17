/*
 * Einwilligung (Datenschutz, lokale Speicherung, externe Inhalte, Live-Meldungen).
 *
 * Die Seite setzt keine Tracking-Cookies. Im Browser gespeichert werden nur:
 * Darstellung (hell/dunkel), der Kommentarname, der Werbefrei-Nachweis und
 * diese Einwilligung. Was eine Zustimmung braucht:
 *   - (externeBilder entfaellt: Symbolbilder laufen ueber /api/bild, den
 *     eigenen Bildproxy; das Feld bleibt im Speicherformat, wird ignoriert.)
 *   - liveMeldungen: waehrend die Seite offen ist, wird alle fuenf Minuten
 *     /api/latest.json (eigener Server) abgefragt; neue Meldungen erscheinen
 *     als Hinweis oben und, wenn erlaubt, als Browser-Benachrichtigung.
 *
 * Bilder: window.maExtern.url(adresse) liefert die Proxy-Adresse; aeltere
 * Seiten mit data-extern-src werden beim Start umgeschrieben.
 */
(() => {
  'use strict';
  const SPEICHER = 'merzenich-einwilligung';
  const VERSION = 1;
  const html = document.documentElement;
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  function lesen() {
    try { const d = JSON.parse(localStorage.getItem(SPEICHER) || 'null'); return d && d.version === VERSION ? d : null; } catch (e) { return null; }
  }
  function schreiben(d) { try { localStorage.setItem(SPEICHER, JSON.stringify(d)); } catch (e) { /* privater Modus */ } }
  let stand = lesen();

  // ---------------------------------------------------------------- Bilder
  // Externe Symbolbilder (Wikimedia Commons) laufen ueber unseren eigenen
  // Bildproxy (/api/bild, siehe deploy/coolify/kommentare/server.mjs). Der
  // Browser spricht nie mit Wikimedia, deshalb ist keine Einwilligung noetig.
  const PROXY_HOSTS = ['commons.wikimedia.org', 'upload.wikimedia.org'];
  function bildUrl(url) {
    try { const u = new URL(url, location.href); if (/^https?:$/.test(u.protocol) && PROXY_HOSTS.includes(u.host)) return '/api/bild?u=' + encodeURIComponent(u.href); } catch (e) { /* unten */ }
    return url;
  }
  function bilderAnwenden() {
    html.dataset.extern = 'proxy';
    document.querySelectorAll('img[data-extern-src]').forEach((img) => { img.src = bildUrl(img.getAttribute('data-extern-src')); img.removeAttribute('data-extern-src'); img.classList.remove('extern-gesperrt'); });
  }
  // Fuer andere Skripte (v20.js, bild-fallbacks.js): Adresse ueber den Proxy.
  window.maExtern = {
    erlaubt: () => true,
    url: bildUrl,
    attribute: (url) => `src="${esc(bildUrl(url))}"`,
    setze: (img, url) => { img.src = bildUrl(url); },
  };

  // ---------------------------------------------------------- Live-Meldungen
  let liveTimer = null;
  async function livePruefen() {
    if (!(stand && stand.liveMeldungen) || document.hidden) return;
    try {
      const r = await fetch('/api/latest.json', { cache: 'no-store' }); if (!r.ok) return;
      const d = await r.json(); const neuste = (d.items || [])[0]; if (!neuste) return;
      let letzte = ''; try { letzte = localStorage.getItem('merzenich-live-letzte') || ''; } catch (e) { /* egal */ }
      if (!letzte) { try { localStorage.setItem('merzenich-live-letzte', neuste.date); } catch (e) { /* egal */ } return; }
      if (neuste.date > letzte) {
        try { localStorage.setItem('merzenich-live-letzte', neuste.date); } catch (e) { /* egal */ }
        const url = String(neuste.url || '').replace(/^https?:\/\/merzenich-aktuell\.de/, '');
        zeigeHinweis(neuste.title, url);
        if ('Notification' in window && Notification.permission === 'granted') {
          try { const n = new Notification('Merzenich Aktuell', { body: neuste.title, tag: 'merzenich-live' }); n.onclick = () => { window.focus(); location.href = url; }; } catch (e) { /* egal */ }
        }
      }
    } catch (e) { /* offline: still */ }
  }
  function liveStarten() {
    if (liveTimer) clearInterval(liveTimer); liveTimer = null;
    if (!(stand && stand.liveMeldungen)) return;
    livePruefen(); liveTimer = setInterval(livePruefen, 5 * 60 * 1000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) livePruefen(); });
  }
  function zeigeHinweis(titel, url) {
    document.querySelector('.live-hinweis')?.remove();
    const el = document.createElement('div'); el.className = 'live-hinweis'; el.setAttribute('role', 'status');
    el.innerHTML = `<span class="live-hinweis__label">Neu</span><a href="${esc(url)}">${esc(titel)}</a><button type="button" aria-label="Hinweis schließen">×</button>`;
    el.querySelector('button').addEventListener('click', () => el.remove());
    document.body.prepend(el);
  }

  // ---------------------------------------------------------------- Banner
  function speichernUndAnwenden(externeBilder, liveMeldungen) {
    stand = { version: VERSION, zeit: new Date().toISOString(), notwendig: true, externeBilder: !!externeBilder, liveMeldungen: !!liveMeldungen };
    schreiben(stand); bilderAnwenden(); liveStarten();
    if (stand.liveMeldungen && 'Notification' in window && Notification.permission === 'default') { try { Notification.requestPermission(); } catch (e) { /* egal */ } }
    document.querySelector('.einwilligung')?.remove();
    document.body.classList.remove('einwilligung-offen');
  }
  function bannerHtml(details) {
    return `<div class="einwilligung__box" role="dialog" aria-modal="false" aria-labelledby="einwilligung-titel">
      <h2 id="einwilligung-titel">Datenschutz und Einstellungen</h2>
      <p>Diese Seite setzt keine Tracking-Cookies und keine Werbe-Tracker. Im Browser bleiben nur Ihre Einstellungen (Darstellung, Kommentarname, diese Auswahl). Symbolbilder laden wir über unseren eigenen Server, nicht von Dritten. Eine Funktion braucht Ihre Zustimmung, weil Ihr Browser Sie dabei benachrichtigt:</p>
      <form class="einwilligung__form">
        <label><input type="checkbox" checked disabled> <span><strong>Notwendig</strong> · Darstellung, Kommentare, diese Einstellung. Immer aktiv.</span></label>
        <label><input type="checkbox" name="liveMeldungen" ${details ? '' : 'checked'}> <span><strong>Live-Meldungen</strong> · Neue Meldungen erscheinen automatisch, solange die Seite offen ist, auf Wunsch als Browser-Benachrichtigung. Abgefragt wird nur unser eigener Server.</span></label>
      </form>
      <div class="einwilligung__aktionen">
        <button type="button" class="btn" data-ew="alle">Alle akzeptieren</button>
        <button type="button" class="btn ghost" data-ew="auswahl">${details ? 'Auswahl speichern' : 'Nur notwendige'}</button>
        ${details ? '' : '<button type="button" class="einwilligung__mehr" data-ew="details">Auswählen</button>'}
      </div>
      <p class="einwilligung__werbefrei"><strong>Werbung entfernen</strong> · Merzenich Aktuell ohne Anzeigen für 2,50 € im Monat, monatlich kündbar. <a href="/werbefrei/">Werbefrei lesen</a></p>
      <p class="einwilligung__fuss"><a href="/datenschutz/">Datenschutzerklärung</a> · <a href="/impressum/">Impressum</a> · Änderbar jederzeit unten auf jeder Seite unter „Datenschutz-Einstellungen“.</p>
    </div>`;
  }
  function bannerZeigen(details = false) {
    document.querySelector('.einwilligung')?.remove();
    const el = document.createElement('aside'); el.className = 'einwilligung'; el.innerHTML = bannerHtml(details);
    document.body.append(el); document.body.classList.add('einwilligung-offen');
    el.addEventListener('click', (e) => {
      const b = e.target.closest('[data-ew]'); if (!b) return;
      const form = el.querySelector('form');
      if (b.dataset.ew === 'alle') speichernUndAnwenden(false, true);
      else if (b.dataset.ew === 'details') bannerZeigen(true);
      else if (details) speichernUndAnwenden(false, form.liveMeldungen.checked);
      else speichernUndAnwenden(false, false);
    });
  }
  function fusslinkSetzen() {
    const ziel = document.querySelector('.compact-footer .shell, footer .shell, footer'); if (!ziel || ziel.querySelector('.einwilligung__link')) return;
    const a = document.createElement('a'); a.href = '#datenschutz-einstellungen'; a.className = 'einwilligung__link'; a.textContent = 'Datenschutz-Einstellungen';
    a.addEventListener('click', (e) => { e.preventDefault(); bannerZeigen(true); if (stand) { const f = document.querySelector('.einwilligung form'); if (f) { f.liveMeldungen.checked = !!stand.liveMeldungen; } } });
    ziel.append(a);
  }
  window.maEinwilligungOeffnen = () => document.querySelector('.einwilligung__link')?.click();

  function start() {
    bilderAnwenden(); liveStarten(); fusslinkSetzen();
    if (!stand) bannerZeigen(false);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true }); else start();
})();
