/* Endlos-Scrollen (Wunsch Betreiber 15.10.2026: keine Seiten 1, 2, man scrollt
 * einfach weiter, an manchen Tagen kommen 10 Meldungen).
 *
 * Listen (Ressorts, Aktuell, Ortsteile, Themen, Sammelseiten, Suche, Autoren)
 * laden kurz vor ihrem Ende die nächste Seite nach, ohne Obergrenze, bis keine
 * Meldung mehr kommt. Quelle der nächsten Seite:
 *   - WordPress: .feed[data-endlos-naechste]; dieselbe Adresse mit ?ma_endlos=1
 *     liefert nur die Meldungen als Teilstück [data-endlos-teil] (inc/ma21.php);
 *   - statische Kopie: der Link a[rel=next] im Blättern, aus der ganzen Seite.
 * Die Startseite hat nach den Blöcken „Weitere Meldungen“ ([data-endlos-quelle]),
 * gespeist aus /nachrichten/ ohne Sport (?ma_ohne_sport=1); was oben schon steht, kommt nicht noch einmal.
 *
 * Ohne JavaScript, im Bearbeitungsmodus der Redaktion und bei Fehlern bleibt
 * das Blättern bzw. der Knopf „Weitere Meldungen laden“. Nachgeladene Teile
 * merkt sich die Sitzung 30 Minuten: Die Zurück-Taste führt an dieselbe Stelle.
 * Neue Inhalte melden sich mit dem Ereignis „ma:nachgeladen“ (app.js, werbung.js). */
(() => {
  'use strict';
  const d = document;
  if (!('fetch' in window) || !('DOMParser' in window)) return;
  const bearbeiten = () => Boolean(window.maLayout) || d.body.classList.contains('ma-bearbeiten') || /[?&]bearbeiten=1(?:&|$)/.test(location.search);
  if (bearbeiten()) return;
  // Ausbaustufe „Endlos-Scrollen“ ausgeschaltet (inc/ausbau.php): Blättern wie bis 14.10.2026.
  if (d.documentElement.dataset.endlos === 'aus') return;

  const SPEICHER_MS = 30 * 60 * 1000;
  const ARCHIV = '/archiv/';

  /** Adresse auf dieselbe Herkunft ziehen (WordPress nennt volle Adressen). */
  function adresse(href) {
    try { const u = new URL(href, location.href); return u.pathname + u.search; } catch (e) { return ''; }
  }
  function mitTeilmodus(pfad) {
    const u = new URL(pfad, location.origin);
    u.searchParams.set('ma_endlos', '1');
    return u.pathname + u.search;
  }

  /** Was schon auf der Seite steht: Story-Kennungen und Artikeladressen. */
  function bekannt() {
    const ids = new Set(), pfade = new Set();
    for (const el of d.querySelectorAll('[data-story]')) {
      ids.add(el.getAttribute('data-story'));
      const a = el.querySelector('h2 a[href], h3 a[href]');
      if (a) pfade.add(new URL(a.getAttribute('href'), location.href).pathname);
    }
    return { ids, pfade };
  }
  function doppelt(knoten, schon) {
    if (!knoten.hasAttribute || !knoten.hasAttribute('data-story')) return false;
    if (schon.ids.has(knoten.getAttribute('data-story'))) return true;
    const a = knoten.querySelector('h2 a[href], h3 a[href]');
    return Boolean(a && schon.pfade.has(new URL(a.getAttribute('href'), location.href).pathname));
  }

  /** Antwort lesen: Teilstück aus WordPress oder ganze statische Seite. */
  function lesen(text) {
    const doc = new DOMParser().parseFromString(text, 'text/html');
    const teil = doc.querySelector('[data-endlos-teil]');
    if (teil) {
      const art = teil.getAttribute('data-art') === 'raster' ? 'raster' : 'reihen';
      const quelle = art === 'raster' ? (teil.querySelector('.bildraster--ressort') || teil) : teil;
      return { art, knoten: [...quelle.children], naechste: teil.getAttribute('data-naechste') || '' };
    }
    const feed = doc.querySelector('.feed');
    const weiter = feed && feed.querySelector('a[rel="next"]');
    return { art: 'reihen', knoten: feed ? [...feed.querySelectorAll(':scope > article[data-story]')] : [], naechste: weiter ? weiter.getAttribute('href') : '' };
  }

  /** Anzeige im Kartenraster an einen Zeilenanfang rücken (keine Lücke daneben). */
  function werbungAusrichten(raster) {
    const spalten = (getComputedStyle(raster).gridTemplateColumns || '').split(' ').filter(Boolean).length;
    if (spalten < 2) return;
    for (const w of raster.querySelectorAll(':scope > .bildraster-werbung')) {
      let davor = 0;
      for (let el = w.previousElementSibling; el; el = el.previousElementSibling) if (el.classList.contains('bildraster-karte')) davor++;
      let fehlt = (spalten - (davor % spalten)) % spalten, ziel = w;
      while (fehlt > 0 && ziel.nextElementSibling && ziel.nextElementSibling.classList.contains('bildraster-karte')) { ziel = ziel.nextElementSibling; fehlt--; }
      if (ziel !== w) ziel.after(w);
    }
  }

  /** Anzeigen des Plugins mit mehreren Motiven wechseln wie ad-rotation.js. */
  function rotation(wurzel) {
    for (const platz of wurzel.querySelectorAll('.ma-ad[data-ma-ad-rotate]')) {
      const items = platz.querySelectorAll('.ma-ad__item');
      if (items.length < 2 || platz.dataset.maEndlosRotation) continue;
      platz.dataset.maEndlosRotation = '1';
      let aktiv = Math.floor(Math.random() * items.length), pause = false;
      const zeige = (i) => { items.forEach((el, n) => { el.hidden = n !== i; }); platz.setAttribute('data-ma-ad-current', String(i)); };
      zeige(aktiv);
      platz.addEventListener('mouseenter', () => { pause = true; });
      platz.addEventListener('mouseleave', () => { pause = false; });
      platz.addEventListener('focusin', () => { pause = true; });
      platz.addEventListener('focusout', () => { pause = false; });
      setInterval(() => { if (!pause && !d.hidden) zeige(aktiv = (aktiv + 1) % items.length); }, Math.max(3000, parseInt(platz.getAttribute('data-ma-ad-rotate'), 10) || 8000));
    }
  }

  function einrichten(liste, start) {
    const schluessel = 'ma-endlos:' + location.pathname + location.search;
    const blaettern = liste.querySelector(':scope > .pagination, :scope > nav.navigation');
    const steuer = d.createElement('div');
    steuer.className = 'endlos';
    steuer.innerHTML = '<p class="sr-only" role="status" aria-live="polite"></p>'
      + '<button type="button" class="endlos-knopf">Weitere Meldungen laden</button>'
      + `<p class="endlos-ende" tabindex="-1" hidden>Alle Meldungen geladen. <a href="${ARCHIV}">Ältere Meldungen im Archiv</a></p>`;
    liste.insertBefore(steuer, blaettern);
    if (blaettern) blaettern.hidden = true;
    const status = steuer.querySelector('[role="status"]');
    const knopf = steuer.querySelector('.endlos-knopf');
    const ende = steuer.querySelector('.endlos-ende');
    let naechste = start, laedt = false, fehler = false, teile = [];

    function speichern(y) {
      try { sessionStorage.setItem(schluessel, JSON.stringify({ t: Date.now(), teile, naechste, y: y == null ? window.scrollY : y })); } catch (e) { /* Speicher voll oder gesperrt: dann eben ohne */ }
    }

    /** Knoten einfügen; gibt die tatsächlich neuen zurück. */
    function einfuegen(art, knoten) {
      const schon = bekannt();
      const neu = knoten.filter((k) => !doppelt(k, schon));
      if (!neu.some((k) => k.hasAttribute('data-story'))) return [];
      const fertig = neu.map((k) => d.importNode(k, true));
      if (art === 'raster') {
        let raster = [...liste.querySelectorAll('.bildraster--ressort')].pop();
        if (!raster) { raster = d.createElement('div'); raster.className = 'bildraster bildraster--ressort'; liste.insertBefore(raster, steuer); }
        for (const k of fertig) raster.appendChild(k);
        werbungAusrichten(raster);
      } else {
        for (const k of fertig) if (k.hasAttribute('data-story')) liste.insertBefore(k, steuer);
      }
      // Nur die neuen Anzeigen: die der ersten Seite rotiert schon ad-rotation.js (Plugin).
      for (const k of fertig) if (k.querySelectorAll) rotation(k);
      d.dispatchEvent(new CustomEvent('ma:nachgeladen', { detail: { knoten: fertig } }));
      return fertig;
    }

    function zustand() {
      // Verschwindet der Knopf mit dem Tastaturfokus, wandert der Fokus zum Hinweis am Ende.
      const hatteFokus = d.activeElement === knopf;
      knopf.hidden = !naechste;
      ende.hidden = Boolean(naechste);
      knopf.removeAttribute('aria-disabled');
      knopf.textContent = fehler ? 'Erneut versuchen' : 'Weitere Meldungen laden';
      if (hatteFokus && knopf.hidden) ende.focus();
    }

    async function laden(vonKnopf) {
      if (laedt || !naechste || bearbeiten()) return;
      laedt = true; fehler = false;
      knopf.setAttribute('aria-disabled', 'true');
      knopf.textContent = 'Meldungen werden geladen …';
      liste.setAttribute('aria-busy', 'true');
      let neu = [];
      try {
        const r = await fetch(mitTeilmodus(naechste), { credentials: 'same-origin', cache: 'no-cache', headers: { Accept: 'text/html' } });
        if (r.status === 404) { naechste = ''; }
        else if (!r.ok) throw new Error('Status ' + r.status);
        else {
          const teil = lesen(await r.text());
          neu = einfuegen(teil.art, teil.knoten);
          const folgt = teil.naechste ? adresse(teil.naechste) : '';
          // Nennt die Antwort sich selbst als Folgeseite, ist hier Schluss (keine Schleife).
          naechste = folgt === naechste ? '' : folgt;
          // Gemerkt wird ohne Rotationsmerkmal, sonst wechseln wiederhergestellte Anzeigen nicht mehr.
          if (neu.length) teile.push({ art: teil.art, html: neu.map((k) => k.outerHTML).join('').replace(/ data-ma-endlos-rotation="1"/g, '') });
        }
        const zahl = neu.filter((k) => k.hasAttribute('data-story')).length;
        status.textContent = !naechste ? 'Alle Meldungen geladen.' : `${zahl} weitere Meldung${zahl === 1 ? '' : 'en'} geladen.`;
        speichern();
      } catch (e) {
        fehler = true;
        status.textContent = 'Die nächsten Meldungen konnten nicht geladen werden.';
      }
      laedt = false;
      liste.removeAttribute('aria-busy');
      zustand();
      // Mit der Tastatur geladen: Fokus auf die erste neue Meldung.
      if (vonKnopf && neu.length) { const a = neu.find((k) => k.hasAttribute('data-story'))?.querySelector('h2 a, h3 a'); if (a) a.focus(); }
      // Steht das Listenende noch im Bild (kurze Seite oder nur Doppelte): gleich weiter.
      if (!fehler && naechste && nahe()) requestAnimationFrame(() => laden(false));
    }

    const nahe = () => steuer.getBoundingClientRect().top < window.innerHeight + 800;
    knopf.addEventListener('click', () => laden(true));

    // Zurück-Taste: gemerkte Teile wieder einsetzen und an dieselbe Stelle.
    const nav = performance.getEntriesByType && performance.getEntriesByType('navigation')[0];
    let alt = null;
    try { alt = JSON.parse(sessionStorage.getItem(schluessel) || 'null'); } catch (e) { alt = null; }
    if (alt && nav && nav.type === 'back_forward' && Date.now() - alt.t < SPEICHER_MS && Array.isArray(alt.teile)) {
      for (const t of alt.teile) {
        const tpl = d.createElement('template');
        tpl.innerHTML = t.html;
        if (einfuegen(t.art, [...tpl.content.children]).length) teile.push(t);
      }
      naechste = alt.naechste || '';
      if (typeof alt.y === 'number') {
        if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
        const y = alt.y;
        window.scrollTo(0, y);
        window.addEventListener('load', () => window.scrollTo(0, y), { once: true });
      }
    } else {
      try { sessionStorage.removeItem(schluessel); } catch (e) { /* egal */ }
    }
    // Stelle merken, wenn eine Meldung geöffnet oder die Seite verlassen wird.
    liste.addEventListener('click', (e) => { if (e.target.closest && e.target.closest('a[href]')) speichern(); });
    window.addEventListener('pagehide', () => { if (teile.length) speichern(); });

    zustand();
    if ('IntersectionObserver' in window) {
      new IntersectionObserver((eintraege) => {
        if (eintraege.some((e) => e.isIntersecting) && !fehler) laden(false);
      }, { rootMargin: '0px 0px 800px 0px' }).observe(steuer);
    }
  }

  // WordPress-Listen
  const listen = [...d.querySelectorAll('.feed[data-endlos-naechste]')];
  for (const l of listen) einrichten(l, adresse(l.getAttribute('data-endlos-naechste')));
  // Statische Kopie: Blättern mit rel=next
  for (const l of d.querySelectorAll('.feed')) {
    if (listen.includes(l) || l.hasAttribute('data-endlos-quelle')) continue;
    const weiter = l.querySelector(':scope > .pagination a[rel="next"], :scope > nav.navigation a[rel="next"]');
    if (weiter) einrichten(l, adresse(weiter.getAttribute('href')));
  }
  // Startseite: „Weitere Meldungen“
  for (const l of d.querySelectorAll('[data-endlos-quelle]')) {
    for (const hinweis of l.querySelectorAll('.weitere-meldungen__ohne-js')) hinweis.remove();
    einrichten(l, adresse(l.getAttribute('data-endlos-quelle')));
  }
})();
