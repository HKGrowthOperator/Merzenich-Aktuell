(() => {
  'use strict';

  // Wird von deploy/ressort-menue.mjs bei jedem Build auf den aktuellen
  // Inhalts-Hash gesetzt. Nicht entfernen: verhindert alte Menues im Browser-Cache.
  const MENUE_URL = '/assets/ressort-menue.json?v=7fa26cb833';
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

  // Jeder Ressort-Teaser bekommt ein Bild. Fehlt beim aktuellen Beitrag ein
  // eigenes Foto, kommt ein freigegebenes Motiv aus dem passenden Editorial-Pool.
  const FALLBACK_IMAGES = {
    '/nachrichten/': [
      ['/assets/editorial-pools/aktuell/aktuell-01-0b6bcf033a-480.webp', 'Aktuelle Meldungen aus Merzenich'],
      ['/assets/places/merzenich-720.webp', 'Merzenich und seine Ortsteile']
    ],
    '/blaulicht/': [
      ['/assets/editorial-pools/blaulicht/blaulicht-01-77105a0ca0-480.webp', 'Blaulicht'],
      ['/assets/editorial-pools/polizei/polizei-01-975326909c-480.webp', 'Polizei und Sicherheit']
    ],
    '/sport/': [
      ['/assets/editorial-pools/sport/sport-01-4ab6e95eab-480.webp', 'Sport'],
      ['/assets/editorial-pools/sport/sport-02-11b84171a8-480.webp', 'Fußball und Vereine']
    ],
    '/termine/': [
      ['/assets/editorial-pools/termine/termine-01-3e5529edd1-480.webp', 'Termine'],
      ['/assets/editorial-pools/termine/termine-02-1278784fc5-480.webp', 'Veranstaltungen']
    ],
    '/vereine/': [
      ['/assets/editorial-pools/vereine/vereine-01-9c345e6bd7-480.webp', 'Vereinsleben'],
      ['/assets/editorial-pools/vereine/vereine-02-51d0635a6b-480.webp', 'Ehrenamt und Gemeinschaft']
    ],
    '/rathaus/': [
      ['/assets/places/merzenich-720.webp', 'Gemeinde Merzenich'],
      ['/assets/uploads/buergersprechstunde.webp', 'Bürgerservice und Beteiligung']
    ],
    '/leben/': [
      ['/assets/editorial-pools/leben/leben-01-1091fb6a9c-480.webp', 'Leben in der Gemeinde'],
      ['/assets/editorial-pools/leben/leben-02-87ffe0734e-480.webp', 'Freizeit und Alltag']
    ],
    '/wirtschaft/': [
      ['/assets/editorial-pools/wirtschaft/wirtschaft-01-561359542b-480.webp', 'Wirtschaft'],
      ['/assets/editorial-pools/wirtschaft/wirtschaft-02-d6eb71c3ef-480.webp', 'Infrastruktur und Wandel']
    ],
    '/tipp/': [
      ['/assets/editorial-pools/tipp/tipp-01-b85a6ff84c-480.webp', 'Tipps aus der Region'],
      ['/assets/editorial-pools/tipp/tipp-02-3752acb551-480.webp', 'Freizeit und Ausflüge']
    ],
    '/menschen/': [
      ['/assets/editorial-pools/menschen/menschen-01-686bb6fb4c-480.webp', 'Menschen aus der Gemeinde'],
      ['/assets/editorial-pools/menschen/menschen-02-7999ac1cce-480.webp', 'Ehrenamt und Geschichten']
    ]
  };

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

  function story(route, item, index) {
    const fallback = (FALLBACK_IMAGES[route] || [])[index] || (FALLBACK_IMAGES[route] || [])[0] || ['', ''];
    const src = item && item.bild && item.bild.src ? item.bild.src : fallback[0];
    const alt = item && item.bild && item.bild.alt ? item.bild.alt : fallback[1];
    const title = item && item.titel ? item.titel : (fallback[1] || 'Mehr aus dem Ressort');
    const url = item && item.url ? item.url : route;
    const meta = [item && item.ort, fmtDate(item && item.datum, !!(item && item.termin))].filter(Boolean).join(' · ');
    return '<a class="ressort-dropdown__story" href="' + esc(url) + '">' +
      '<span class="ressort-dropdown__story-image"><img src="' + esc(src) + '" alt="' + esc(alt) + '" width="480" height="270" loading="eager" decoding="async"></span>' +
      '<span class="ressort-dropdown__story-copy">' +
        (meta ? '<span class="ressort-dropdown__story-meta">' + esc(meta) + '</span>' : '') +
        '<strong>' + esc(title) + '</strong>' +
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

    const newest = [0, 1].map((i) => story(route, (def.neu || [])[i] || null, i)).join('');
    inner.innerHTML =
      '<div class="ressort-dropdown__head">' +
        '<span class="ressort-dropdown__eyebrow">Ressort</span>' +
        '<strong>' + esc(def.titel || link.textContent.trim()) + '</strong>' +
        '<p>' + esc(INTROS[route] || '') + '</p>' +
        '<a class="ressort-dropdown__all" href="' + esc(route) + '">' + esc(def.alle || 'Alle Meldungen') + '</a>' +
      '</div>' +
      '<div class="ressort-dropdown__groups">' + groups + '</div>' +
      '<aside class="ressort-dropdown__latest" aria-label="Neu im Ressort">' +
        '<span class="ressort-dropdown__eyebrow">Neu im Ressort</span>' +
        newest +
      '</aside>';
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
      if (active === link && !panel.hidden) {
        close();
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
    addEventListener('resize', () => {
      if (!desktop()) close();
    }, { passive: true });

    nav.classList.add('ressort-dropdowns-ready');
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
})();
