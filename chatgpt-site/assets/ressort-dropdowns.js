/*
 * Merzenich Aktuell – Ressort-Dropdowns.
 * Daten: /assets/ressort-menue.json, erzeugt von deploy/ressort-menue.mjs aus
 * deploy/ressort-menue.json (nur Ziele, die es gibt) und dem Inhaltsindex
 * (zwei neueste Beiträge je Ressort, bei Termine die zwei nächsten Termine).
 *
 * Desktop: erster Klick auf ein Ressort öffnet das Menü unter der Leiste,
 * zweiter Klick öffnet die Ressortseite. Immer nur ein Menü; Escape, Klick
 * daneben oder Tab aus dem Menü heraus schließen es. Pfeil runter springt
 * ins Menü. Mobil: im Menü-Drawer klappt jedes Ressort als Akkordeon auf.
 * Ohne JavaScript oder ohne Daten bleiben es normale Links.
 */
(() => {
  'use strict';

  const MENUE_URL = '/assets/ressort-menue.json?v=fad36cba5e';
  const desktop = () => matchMedia('(min-width: 768px)').matches;
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const tz = { timeZone: 'Europe/Berlin' };
  const kurzDatum = (iso) => new Intl.DateTimeFormat('de-DE', { ...tz, day: '2-digit', month: '2-digit' }).format(new Date(iso));
  const terminDatum = (iso) => {
    const d = new Date(iso);
    const tag = new Intl.DateTimeFormat('de-DE', { ...tz, weekday: 'short', day: '2-digit', month: '2-digit' }).format(d);
    const zeit = new Intl.DateTimeFormat('de-DE', { ...tz, hour: '2-digit', minute: '2-digit' }).format(d);
    return zeit === '00:00' ? tag : `${tag} · ${zeit} Uhr`;
  };

  function beitragHtml(b) {
    const marke = b.termin
      ? `<span class="rd-marke">${esc(terminDatum(b.datum))}</span>`
      : `<span class="rd-marke"><b>MERZENICH</b>${b.ort && b.ort !== 'Merzenich' ? ` · ${esc(b.ort)}` : ''}<time datetime="${esc(b.datum)}">${esc(kurzDatum(b.datum))}</time></span>`;
    const bild = b.bild ? `<img src="${esc(b.bild.src)}" alt="" width="96" height="64" loading="lazy" decoding="async">` : '';
    const wo = b.termin && b.ort ? `<span class="rd-wo">${esc(b.ort)}</span>` : '';
    return `<li><a class="rd-beitrag${b.bild ? '' : ' ohne-bild'}" href="${esc(b.url)}">${bild}<span class="rd-text">${marke}<span class="rd-titel">${esc(b.titel)}</span>${wo}</span></a></li>`;
  }

  function panelHtml(href, r) {
    const gruppen = r.gruppen.map((g) => `<section><h3>${esc(g.titel)}</h3><ul>${g.links.map(([label, url]) => `<li><a href="${esc(url)}">${esc(label)}</a></li>`).join('')}</ul></section>`).join('');
    const neu = r.neu.length
      ? `<div class="rd-neu"><h3>${href === '/termine/' ? 'Als Nächstes' : 'Aktuell'}</h3><ul>${r.neu.map(beitragHtml).join('')}</ul></div>`
      : '';
    return `<div class="rd-kopf"><span class="rd-ressort">${esc(r.titel)}</span><a class="ressort-dropdown__all" href="${esc(href)}">${esc(r.alle)}</a></div>`
      + `<div class="ressort-dropdown__groups" style="--rd-spalten:${r.gruppen.length}">${gruppen}</div>${neu}`;
  }

  function desktopMenue(nav, row, links, daten) {
    const panel = document.createElement('div');
    panel.className = 'ressort-dropdown';
    panel.id = 'ressort-dropdown';
    panel.hidden = true;
    panel.innerHTML = '<div class="shell ressort-dropdown__inner"></div>';
    row.insertAdjacentElement('afterend', panel);
    const inner = panel.firstElementChild;
    let aktiv = null;

    function schliessen({ fokus = false } = {}) {
      if (!aktiv) return;
      aktiv.classList.remove('ressort-open');
      aktiv.setAttribute('aria-expanded', 'false');
      panel.hidden = true;
      panel.classList.remove('is-open');
      const alt = aktiv;
      aktiv = null;
      if (fokus) alt.focus();
    }
    function oeffnen(link) {
      if (aktiv) { aktiv.classList.remove('ressort-open'); aktiv.setAttribute('aria-expanded', 'false'); }
      aktiv = link;
      const href = link.getAttribute('href');
      inner.innerHTML = panelHtml(href, daten.ressorts[href]);
      panel.setAttribute('aria-label', `${daten.ressorts[href].titel}: Unterseiten und aktuelle Beiträge`);
      link.classList.add('ressort-open');
      link.setAttribute('aria-expanded', 'true');
      panel.hidden = false;
      requestAnimationFrame(() => panel.classList.add('is-open'));
    }

    for (const link of links) {
      link.setAttribute('aria-haspopup', 'true');
      link.setAttribute('aria-controls', panel.id);
      link.setAttribute('aria-expanded', 'false');
      link.addEventListener('click', (e) => {
        if (!desktop()) return;
        // Zweiter Klick auf das offene Ressort: Seite normal öffnen.
        if (aktiv === link) return;
        e.preventDefault();
        oeffnen(link);
      });
      link.addEventListener('keydown', (e) => {
        if (!desktop() || e.key !== 'ArrowDown') return;
        e.preventDefault();
        if (aktiv !== link) oeffnen(link);
        requestAnimationFrame(() => panel.querySelector('a')?.focus());
      });
    }
    document.addEventListener('click', (e) => { if (aktiv && !nav.contains(e.target)) schliessen(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && aktiv) schliessen({ fokus: true }); });
    nav.addEventListener('focusout', (e) => { if (aktiv && e.relatedTarget && !nav.contains(e.relatedTarget)) schliessen(); });
    addEventListener('resize', () => { if (!desktop()) schliessen(); }, { passive: true });
    nav.classList.add('ressort-dropdowns-ready');
  }

  function drawerAkkordeon(daten) {
    const gruppe = document.querySelector('[data-drawer] .drawer-group');
    if (!gruppe) return;
    let n = 0;
    for (const a of [...gruppe.querySelectorAll(':scope > a')]) {
      const r = daten.ressorts[a.getAttribute('href')];
      if (!r) continue;
      const id = `drawer-ressort-${++n}`;
      const zeile = document.createElement('div');
      zeile.className = 'drawer-ressort';
      a.replaceWith(zeile);
      const knopf = document.createElement('button');
      knopf.type = 'button';
      knopf.className = 'drawer-ressort__auf';
      knopf.setAttribute('aria-expanded', 'false');
      knopf.setAttribute('aria-controls', id);
      knopf.innerHTML = `<span class="sr-only">${esc(r.titel)}: Unterseiten</span>`;
      const liste = document.createElement('div');
      liste.className = 'drawer-ressort__liste';
      liste.id = id;
      liste.hidden = true;
      liste.innerHTML = r.gruppen.map((g) => `<div class="drawer-ressort__gruppe">${esc(g.titel)}</div>${g.links.map(([label, url]) => `<a href="${esc(url)}">${esc(label)}</a>`).join('')}`).join('');
      zeile.append(a, knopf, liste);
      knopf.addEventListener('click', () => {
        const auf = knopf.getAttribute('aria-expanded') !== 'true';
        for (const k of gruppe.querySelectorAll('.drawer-ressort__auf[aria-expanded="true"]')) {
          if (k !== knopf) { k.setAttribute('aria-expanded', 'false'); document.getElementById(k.getAttribute('aria-controls')).hidden = true; }
        }
        knopf.setAttribute('aria-expanded', String(auf));
        liste.hidden = !auf;
      });
    }
  }

  function start() {
    const nav = document.querySelector('.mainnav');
    const row = nav?.querySelector('.navrow');
    if (!nav || !row) return;
    fetch(MENUE_URL).then((r) => (r.ok ? r.json() : null)).then((daten) => {
      if (!daten?.ressorts) return;
      const links = [...nav.querySelectorAll('.navscroll > a')].filter((a) => daten.ressorts[a.getAttribute('href')]);
      if (links.length) desktopMenue(nav, row, links, daten);
      drawerAkkordeon(daten);
    }).catch(() => { /* ohne Daten bleiben es normale Links */ });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
})();
