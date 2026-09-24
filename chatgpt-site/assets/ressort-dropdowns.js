(() => {
  'use strict';

  // Wird von deploy/ressort-menue.mjs bei jedem Build auf den aktuellen
  // Inhalts-Hash gesetzt. Nicht entfernen: verhindert alte Menues im Browser-Cache.
  const MENUE_URL = '/assets/ressort-menue.json?v=2eb65d384b';
  const desktop = () => matchMedia('(min-width: 768px)').matches;

  const INTROS = {
    '/nachrichten/': 'Aktuelle Meldungen aus Merzenich und den Ortsteilen.',
    '/blaulicht/': 'Polizei, Feuerwehr, Rettung und Verkehr aus der Gemeinde.',
    '/sport/': 'Vereine, Mannschaften, Spieltage und Ergebnisse aus der Gemeinde.',
    '/termine/': 'Was in Merzenich und den Ortsteilen ansteht.',
    '/vereine/': 'Ehrenamt, Gemeinschaft, Feuerwehr, Fanclubs und Vereinsleben.',
    '/rathaus/': 'Verwaltung, Rat, Beteiligung und kommunale Entscheidungen.',
    '/leben/': 'Schule, Freizeit, Umwelt, Familie und Alltag in der Gemeinde.',
    '/wirtschaft/': 'Unternehmen, Infrastruktur und Strukturwandel rund um Merzenich.',
    '/tipp/': 'Freizeitideen, Ausflüge und klar gekennzeichnete Empfehlungen.',
    '/menschen/': 'Porträts, Ehrungen, Jubiläen und Geschichten aus der Gemeinde.'
  };

  // Bilder nur vom Beitrag selbst: Ohne eigenes, passendes Bild steht der Teaser
  // als Text (Editorial Image System V3, docs/BILD-MOTIVREGELN.md). Die
  // Auswahl bebilderter Beitraege trifft deploy/ressort-menue.mjs.
  const esc = (s) => String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({
    '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'
  }[c]));

  const menuPromise = fetch(MENUE_URL, { credentials: 'same-origin' })
    .then((r) => {
      if (!r.ok) throw new Error('Ressort-Menue HTTP ' + r.status);
      return r.json();
    })
    .then((data) => data && data.ressorts ? data.ressorts : {})
    .catch((err) => {
      console.error('Ressort-Menue konnte nicht geladen werden:', err);
      return {};
    });

  function fmtDate(value, isEvent) {
    if (!value) return '';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return '';
    return new Intl.DateTimeFormat('de-DE', {
      day: '2-digit',
      month: '2-digit',
      ...(isEvent ? { hour: '2-digit', minute: '2-digit' } : {})
    }).format(d);
  }

  function story(item) {
    const bild = item.bild && item.bild.src
      ? '<span class="ressort-dropdown__story-image"><img src="' + esc(item.bild.src) + '" alt="' + esc(item.bild.alt || '') + '" width="480" height="270" loading="eager" decoding="async"></span>'
      : '';
    const meta = [item.ort, fmtDate(item.datum, !!item.termin)].filter(Boolean).join(' · ');
    return '<a class="ressort-dropdown__story' + (bild ? '' : ' ohne-bild') + '" href="' + esc(item.url) + '">' + bild +
      '<span class="ressort-dropdown__story-copy">' +
        (meta ? '<span class="ressort-dropdown__story-meta">' + esc(meta) + '</span>' : '') +
        '<strong>' + esc(item.titel) + '</strong>' +
      '</span>' +
    '</a>';
  }

  function render(inner, link, def) {
    const route = link.getAttribute('href');
    const groups = (def.gruppen || []).map((g) =>
      '<section class="ressort-dropdown__group">' +
        '<h3>' + esc(g.titel) + '</h3>' +
        '<div class="ressort-dropdown__links">' +
          (g.links || []).map((item) => '<a href="' + esc(item[1]) + '">' + esc(item[0]) + '</a>').join('') +
        '</div>' +
      '</section>'
    ).join('');

    // Nur echte Beitraege; ohne Beitraege bleibt die Spalte weg.
    const newest = (def.neu || []).filter((x) => x && x.url && x.titel).slice(0, 2).map(story).join('');
    inner.innerHTML =
      '<div class="ressort-dropdown__head">' +
        '<span class="ressort-dropdown__eyebrow">Ressort</span>' +
        '<strong>' + esc(def.titel || link.textContent.trim()) + '</strong>' +
        '<p>' + esc(INTROS[route] || '') + '</p>' +
        '<a class="ressort-dropdown__all" href="' + esc(route) + '">' + esc(def.alle || 'Alle Meldungen') + '</a>' +
      '</div>' +
      '<div class="ressort-dropdown__groups">' + groups + '</div>' +
      (newest ? '<aside class="ressort-dropdown__latest" aria-label="Neu im Ressort">' +
        '<span class="ressort-dropdown__eyebrow">Neu im Ressort</span>' +
        newest +
      '</aside>' : '');
  }

  async function start() {
    const nav = document.querySelector('.mainnav');
    const row = nav && nav.querySelector('.navrow');
    const links = [...((nav && nav.querySelectorAll('.navscroll > a')) || [])];
    if (!nav || !row || !links.length) return;

    const panel = document.createElement('div');
    panel.className = 'ressort-dropdown';
    panel.id = 'ressort-dropdown';
    panel.hidden = true;
    panel.innerHTML = '<div class="shell ressort-dropdown__inner"></div>';
    row.insertAdjacentElement('afterend', panel);
    const inner = panel.querySelector('.ressort-dropdown__inner');
    const data = await menuPromise;
    let active = null;

    function close(options = {}) {
      if (!active) return;
      const old = active;
      active.classList.remove('ressort-open');
      active.setAttribute('aria-expanded', 'false');
      panel.hidden = true;
      panel.classList.remove('is-open');
      active = null;
      if (options.focus) old.focus();
    }

    function open(link) {
      const route = link.getAttribute('href');
      const def = data[route];
      if (!desktop() || !def) {
        location.href = link.href;
        return;
      }
      // Zweiter Klick auf das offene Ressort: Ressortseite oeffnen.
      if (active === link && !panel.hidden) {
        location.href = link.href;
        return;
      }
      if (active) {
        active.classList.remove('ressort-open');
        active.setAttribute('aria-expanded', 'false');
      }
      active = link;
      render(inner, link, def);
      link.classList.add('ressort-open');
      link.setAttribute('aria-expanded', 'true');
      panel.hidden = false;
      requestAnimationFrame(() => panel.classList.add('is-open'));
    }

    for (const link of links) {
      const route = link.getAttribute('href');
      if (!data[route]) continue;
      link.setAttribute('aria-haspopup', 'true');
      link.setAttribute('aria-controls', panel.id);
      link.setAttribute('aria-expanded', 'false');
      link.addEventListener('click', (e) => {
        if (!desktop()) return;
        e.preventDefault();
        open(link);
      });
      link.addEventListener('keydown', (e) => {
        if (!desktop() || e.key !== 'ArrowDown') return;
        e.preventDefault();
        open(link);
        requestAnimationFrame(() => panel.querySelector('a') && panel.querySelector('a').focus());
      });
    }

    panel.addEventListener('click', (e) => {
      if (e.target.closest('a')) close();
    });
    document.addEventListener('click', (e) => {
      if (active && !nav.contains(e.target)) close();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && active) close({ focus: true });
    });
    nav.addEventListener('focusout', (e) => {
      if (active && e.relatedTarget && !nav.contains(e.relatedTarget)) close();
    });
    addEventListener('resize', () => {
      if (!desktop()) close();
    }, { passive: true });

    nav.classList.add('ressort-dropdowns-ready');
    drawerAkkordeon(data);
  }

  // Mobil: im Menue-Drawer klappt jedes Ressort als Akkordeon auf (Link bleibt
  // Link, der Knopf daneben oeffnet die Unterseiten). Immer nur eines offen.
  function drawerAkkordeon(data) {
    const gruppe = document.querySelector('[data-drawer] .drawer-group');
    if (!gruppe || gruppe.querySelector('.drawer-ressort')) return;
    let n = 0;
    for (const a of [...gruppe.querySelectorAll(':scope > a')]) {
      const def = data[a.getAttribute('href')];
      if (!def) continue;
      const id = 'drawer-ressort-' + (++n);
      const zeile = document.createElement('div');
      zeile.className = 'drawer-ressort';
      a.replaceWith(zeile);
      const knopf = document.createElement('button');
      knopf.type = 'button';
      knopf.className = 'drawer-ressort__auf';
      knopf.setAttribute('aria-expanded', 'false');
      knopf.setAttribute('aria-controls', id);
      knopf.innerHTML = '<span class="sr-only">' + esc(def.titel) + ': Unterseiten</span>';
      const liste = document.createElement('div');
      liste.className = 'drawer-ressort__liste';
      liste.id = id;
      liste.hidden = true;
      liste.innerHTML = (def.gruppen || []).map((g) => '<div class="drawer-ressort__gruppe">' + esc(g.titel) + '</div>' +
        (g.links || []).map((l) => '<a href="' + esc(l[1]) + '">' + esc(l[0]) + '</a>').join('')).join('');
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

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
})();
