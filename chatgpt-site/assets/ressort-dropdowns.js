(() => {
  'use strict';

  // Wird von deploy/ressort-menue.mjs bei jedem Build auf den aktuellen
  // Inhalts-Hash gesetzt. Nicht entfernen: verhindert alte Menues im Browser-Cache.
  const MENUE_URL = '/assets/ressort-menue.json?v=b8f8d3a7d0';
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
    '/unternehmen/': 'Betriebe aus der Gemeinde, Unternehmenskanäle und Wirtschaftsmeldungen.',
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

  let werbeDaten = null;
  const werbePromise = document.documentElement.dataset.werbung === 'aus' ? Promise.resolve(null)
    : fetch('/assets/werbung.json', { credentials: 'same-origin' }).then((r) => r.ok ? r.json() : null).catch(() => null);
  werbePromise.then((d) => { werbeDaten = d; });
  function werbungHtml() {
    // WordPress (merzenich-aktuell.de) liefert die Anzeigen dieses Platzes aus
    // der Werbeverwaltung (Platz hero_clubs_expanded_right). Ohne laufende
    // Anzeige bleibt die Flaeche weg, ohne Muster- oder Ersatzmotiv.
    const wp = window.maWerbungPlaetze;
    if (wp && typeof wp === 'object') {
      const liste = Array.isArray(wp.hero_clubs_expanded_right) ? wp.hero_clubs_expanded_right : [];
      if (!liste.length) return '';
      const a = liste[Math.floor(Date.now() / 14000) % liste.length];
      return '<aside class="ressort-dropdown__werbung werbung" aria-label="Anzeige" data-ma-anzeige="' + Number(a.id) + '" data-ma-platz="hero_clubs_expanded_right"><span class="werbung-label">Anzeige</span>' +
        '<div class="werbung-flaeche ma-ad-rotator ma-ad-rotator--gap">' + a.html + '</div></aside>';
    }
    const motive = werbeDaten && Array.isArray(werbeDaten.motive) ? werbeDaten.motive : [];
    if (!motive.length) return '';
    const takt = Math.max(6, Number(werbeDaten.rotationSekunden) || 14) * 1000;
    const m = motive[Math.floor(Date.now() / takt) % motive.length];
    return '<aside class="ressort-dropdown__werbung werbung" aria-label="Anzeige"><span class="werbung-label">Anzeige</span>' +
      '<div class="werbung-flaeche ma-ad-rotator ma-ad-rotator--gap">' + (m.gap || m.band) + '</div>' +
      (m.credit ? '<span class="werbung-credit">' + m.credit + '</span>' : '') + '</aside>';
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
    const newest = (def.neu || []).filter((x) => x && x.url && x.titel).slice(0, 4).map(story).join('');
    // Weitere Bildmeldungen links unter dem Ressortnamen (Sport).
    const mehr = (def.mehr || []).filter((x) => x && x.url && x.titel && x.bild).map(story).join('');
    // Anzeige in der freien Flaeche neben den Linkgruppen (Vereine). Motiv im
    // selben Takt wie alle Werbeflaechen der Seite (assets/werbung.js).
    const werbung = def.werbung ? werbungHtml() : '';
    inner.innerHTML =
      '<div class="ressort-dropdown__head">' +
        '<span class="ressort-dropdown__eyebrow">Ressort</span>' +
        '<strong>' + esc(def.titel || link.textContent.trim()) + '</strong>' +
        '<p>' + esc(INTROS[route] || '') + '</p>' +
        '<a class="ressort-dropdown__all" href="' + esc(route) + '">' + esc(def.alle || 'Alle Meldungen') + '</a>' +
        (mehr ? '<div class="ressort-dropdown__mehr" aria-label="Weitere Meldungen">' + mehr + '</div>' : '') +
      '</div>' +
      '<div class="ressort-dropdown__groups' + (werbung ? ' mit-werbung' : '') + '">' + groups + werbung + '</div>' +
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
    await werbePromise;
    let active = null;
    let schwebeAuf = 0, schwebeZu = 0, schwebeGeoeffnet = 0;

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
        // Ein Klick ist eine klare Absicht: ein laufendes Schliessen nach dem
        // Verlassen mit der Maus wird verworfen.
        clearTimeout(schwebeZu);
        // Gerade per Ueberfahren geoeffnet: der Klick bestaetigt nur, er fuehrt
        // nicht sofort auf die Ressortseite (sonst wirkt der erste Klick wie ein zweiter).
        if (active === link && !panel.hidden && Date.now() - schwebeGeoeffnet < 800) { schwebeGeoeffnet = 0; return; }
        open(link);
      });
      // Oeffnen beim Ueberfahren mit kurzer Verzoegerung (KBS 26.09.2026, wie
      // Oberberg Aktuell); wer nur ueber die Leiste streicht, loest nichts aus.
      // Nur echte Mausbewegung zaehlt: rollt die Seite den Link unter den
      // ruhenden Zeiger, oeffnet nichts.
      link.addEventListener('pointermove', (e) => {
        if (e.pointerType !== 'mouse' || schwebeAuf || active === link) return;
        if (!desktop() || !matchMedia('(hover: hover)').matches || !data[link.getAttribute('href')]) return;
        clearTimeout(schwebeZu);
        schwebeAuf = setTimeout(() => { schwebeAuf = 0; if (active !== link) { open(link); schwebeGeoeffnet = Date.now(); } }, active ? 60 : 180);
      });
      link.addEventListener('mouseleave', () => { clearTimeout(schwebeAuf); schwebeAuf = 0; });
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
    // Verlaesst die Maus Leiste und Panel, schliesst es nach einer Pause.
    const bereich = [nav, panel];
    for (const el of bereich) {
      el.addEventListener('mouseleave', (e) => {
        if (!desktop() || !matchMedia('(hover: hover)').matches) return;
        if (bereich.some((x) => x.contains(e.relatedTarget))) return;
        clearTimeout(schwebeAuf); schwebeAuf = 0;
        schwebeZu = setTimeout(() => close(), 260);
      });
      el.addEventListener('mouseenter', () => clearTimeout(schwebeZu));
    }
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
