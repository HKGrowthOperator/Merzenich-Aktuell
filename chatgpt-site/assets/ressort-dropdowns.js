(() => {
  'use strict';

  // Wird von deploy/ressort-menue.mjs bei jedem Build auf den aktuellen
  // Inhalts-Hash gesetzt. Nicht entfernen: verhindert alte Menues im Browser-Cache.
  const MENUE_URL = '/assets/ressort-menue.json?v=001742cc8f';
  const desktop = () => matchMedia('(min-width: 768px)').matches;

  const INTROS = {
    '/nachrichten/': 'Aktuelle Meldungen aus Merzenich und den Ortsteilen.',
    '/blaulicht/': 'Feuerwehr, Polizei und Verkehr aus der Gemeinde.',
    '/sport/': 'Vereine, Mannschaften, Spieltage und Ergebnisse aus der Gemeinde.',
    '/termine/': 'Was in Merzenich und den Ortsteilen ansteht.',
    '/rathaus/': 'Verwaltung, Rat, Beteiligung und kommunale Entscheidungen.',
    '/leben/': 'Schule, Freizeit, Umwelt, Familie und Alltag in der Gemeinde.',
    '/wirtschaft/': 'Unternehmen, Infrastruktur und Strukturwandel rund um Merzenich.',
    '/unternehmen/': 'Betriebe aus der Gemeinde und ihre Unternehmenskanäle.',
    // Nur mit eingeschalteter Ausbaustufe „Vereine in der Ressortleiste“ (inc/ausbau.php).
    '/vereine/': 'Ehrenamt, Gemeinschaft, Feuerwehr, Fanclubs und Vereinsleben.',
    '/tipp/': 'Freizeitideen, Ausflüge und klar gekennzeichnete Empfehlungen.',
    '/menschen/': 'Porträts, Ehrungen, Jubiläen und Geschichten aus der Gemeinde.'
  };

  // Einleitung der vollen Fassung (Ausbaustufe, inc/ausbau.php), wie bis 14.10.2026.
  const INTROS_VOLL = {
    '/blaulicht/': 'Polizei, Feuerwehr, Rettung und Verkehr aus der Gemeinde.',
    '/unternehmen/': 'Betriebe aus der Gemeinde, Unternehmenskanäle und Wirtschaftsmeldungen.'
  };
  const intro = (route, def) => (gruppenVon(def).length && INTROS_VOLL[route]) || INTROS[route] || '';

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
    .then((data) => {
      const ressorts = data && data.ressorts ? data.ressorts : {};
      // Ausgeblendete Ressorts (Ausbaustufe, inc/ausbau.php): Die statische Kopie
      // traegt sie mit "ausbau" in den Daten, WordPress liefert sie nur
      // eingeschaltet und dann ohne dieses Merkmal.
      for (const k of Object.keys(ressorts)) if (!ressorts[k] || ressorts[k].ausbau) delete ressorts[k];
      return ressorts;
    })
    .catch((err) => {
      console.error('Ressort-Menue konnte nicht geladen werden:', err);
      return {};
    });

  // Immer Berliner Zeit: Ohne Zeitzone rechnete der Browser in seiner eigenen
  // (ein Termin um 18 Uhr stand in einem UTC-Browser als 16:00 da). Termine
  // ohne Uhrzeit (00:00) zeigen nur das Datum.
  const ZONE = 'Europe/Berlin';
  function fmtDate(value, isEvent) {
    if (!value) return '';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return '';
    const tag = new Intl.DateTimeFormat('de-DE', { timeZone: ZONE, day: '2-digit', month: '2-digit' }).format(d);
    if (!isEvent) return tag;
    const zeit = new Intl.DateTimeFormat('de-DE', { timeZone: ZONE, hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }).format(d);
    return zeit === '00:00' ? tag : tag + ', ' + zeit + ' Uhr';
  }

  // Rote Titel (Wunsch Betreiber 15.10.2026): Jedes Menue zeigt nur noch die
  // roten Titel, ohne kleine Unterpunkte. Ein Klick zeigt alle Meldungen dazu.
  function punkteVon(def) {
    return Array.isArray(def.punkte) ? def.punkte.filter((p) => p && p.titel && p.href) : [];
  }

  // Volle Fassung (Ausbaustufe „Unterpunkte in den Aufklappmenüs“,
  // inc/ausbau.php): WordPress liefert dann "gruppen" mit den kleinen
  // Unterpunkten und "werbung" (Vereine) wie bis zum 14.10.2026. Die
  // einfache Fassung sieht diese Daten nie.
  function gruppenVon(def) {
    return (Array.isArray(def.gruppen) ? def.gruppen : [])
      .map((g) => ({ titel: g && g.titel, links: ((g && g.links) || []).filter((l) => Array.isArray(l) && l[0] && l[1]) }))
      .filter((g) => g.titel && g.links.length);
  }
  function gruppenHtml(gruppen) {
    return gruppen.map((g) =>
      '<section class="ressort-dropdown__group">' +
        '<h3>' + esc(g.titel) + '</h3>' +
        '<div class="ressort-dropdown__links">' +
          g.links.map((item) => '<a href="' + esc(item[1]) + '">' + esc(item[0]) + '</a>').join('') +
        '</div>' +
      '</section>'
    ).join('');
  }

  // Anzeige in der freien Flaeche neben den Linkgruppen (Vereine, volle
  // Fassung). WordPress liefert die Anzeigen des Platzes
  // hero_clubs_expanded_right aus der Werbeverwaltung; ohne laufende Anzeige
  // erscheint wie auf allen Werbeplaetzen die gekennzeichnete Musteranzeige
  // (Vorgabe Betreiber 07.10.2026). Motiv im selben Takt wie assets/werbung.js.
  let werbeDaten = null, werbeLaden = null;
  function werbungVorbereiten() {
    if (document.documentElement.dataset.werbung === 'aus') return Promise.resolve();
    if (!werbeLaden) werbeLaden = fetch('/assets/werbung.json', { credentials: 'same-origin' })
      .then((r) => r.ok ? r.json() : null).catch(() => null)
      .then((d) => { werbeDaten = d; });
    return werbeLaden;
  }
  function werbungHtml() {
    if (document.documentElement.dataset.werbung === 'aus') return '';
    const wp = window.maWerbungPlaetze;
    const liste = wp && typeof wp === 'object' && Array.isArray(wp.hero_clubs_expanded_right) ? wp.hero_clubs_expanded_right : [];
    if (liste.length) {
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
  function punkteHtml(def, label) {
    const punkte = punkteVon(def);
    if (!punkte.length) return '';
    return '<nav class="ressort-dropdown__punkte' + (punkte.length > 4 ? ' zwei-spalten' : '') + '" aria-label="' + esc(label) + '"><ul>' +
      punkte.map((p) => '<li><a class="ressort-dropdown__punkt" href="' + esc(p.href) + '">' + esc(p.titel) + '</a></li>').join('') +
    '</ul></nav>';
  }

  // Bildnachweis mit Lizenzlink („Foto: Name, CC BY-SA 4.0“). Steht neben dem
  // Kartenlink, nicht darin: ein Link im Link ist ungueltig.
  function nachweis(b, klasse) {
    if (!b || !b.foto) return '';
    const lizenz = b.lizenz ? ', ' + (b.lizenz_url ? '<a href="' + esc(b.lizenz_url) + '" target="_blank" rel="license noopener">' + esc(b.lizenz) + '</a>' : esc(b.lizenz)) : '';
    return '<span class="' + klasse + '">Foto: ' + esc(b.foto) + lizenz + '</span>';
  }

  function story(item) {
    // Musterbeitraege (Unternehmen, bis echte Betriebe buchen) sichtbar markiert,
    // im Bild und in der Metazeile (auch ohne Bild), mit Bildnachweis.
    // Symbolbilder tragen wie auf den Seiten ihr Etikett (Vorführ-Check 15.10.2026: im Termine-Menü wirkte
    // ein Kirmes-Karussell wie ein Foto vom Seniorennachmittag).
    const marke = item.muster ? '<span class="ressort-dropdown__muster">Musterbeitrag</span>'
      // Ortsansichten heißen wie auf den Seiten „Ortsansicht“, nicht „Symbolbild“ (Review 16.10.2026).
      : (item.bild && (item.bild.etikett || item.bild.symbol) ? '<span class="ressort-dropdown__muster ressort-dropdown__symbol">' + esc(item.bild.etikett || 'Symbolbild') + '</span>' : '');
    const bild = item.bild && item.bild.src
      ? '<span class="ressort-dropdown__story-image' + (item.bild.logo ? ' ressort-dropdown__story-image--logo' : '') + '"><img src="' + esc(item.bild.src) + '" alt="' + esc(item.bild.alt || '') + '" width="480" height="270" loading="eager" decoding="async">' + marke + '</span>'
      : '';
    const meta = [item.muster ? 'Musterbeitrag' : '', item.ort, fmtDate(item.datum, !!item.termin)].filter(Boolean).join(' · ');
    const karte = '<a class="ressort-dropdown__story' + (bild ? '' : ' ohne-bild') + '" href="' + esc(item.url) + '">' + bild +
      '<span class="ressort-dropdown__story-copy">' +
        (meta ? '<span class="ressort-dropdown__story-meta">' + esc(meta) + '</span>' : '') +
        '<strong>' + esc(item.titel) + '</strong>' +
      '</span>' +
    '</a>';
    const credit = item.muster && bild ? nachweis(item.bild, 'ressort-dropdown__credit') : '';
    return credit ? '<div class="ressort-dropdown__story-box">' + karte + credit + '</div>' : karte;
  }

  // Unternehmen wie Oberberg Aktuell (Auftrag Betreiber 13.10.2026): links die
  // roten Titel, daneben die Firmen, rechts zuerst die neuesten Firmenbeitraege;
  // beim Ueberfahren einer Firma zeigt der rechte Bereich ihre Beitraege und den
  // Weg in ihren Unternehmenskanal. Musterfirmen sind als solche markiert.
  const RECHTS_ID = 'ressort-firmen-rechts';
  // Freigegebenes Logo ganz auf hellem Grund (20.10.2026), sonst das Kürzel auf der Firmenfarbe.
  function firmenLogo(f) {
    if (f.logo) return '<span class="ressort-firmen__logo ressort-firmen__logo--bild" aria-hidden="true"><img src="' + esc(f.logo) + '" alt="" width="72" height="36" loading="lazy" decoding="async"></span>';
    return '<span class="ressort-firmen__logo" style="background:' + esc(f.farbe || '#5f5b55') + '" aria-hidden="true">' + esc(f.kuerzel || '?') + '</span>';
  }
  // Kennzeichnung aus den Daten: Musterbeitrag einer erfundenen Firma, Anzeige
  // (gesponserter Beitrag einer echten Firma) oder nur der Firmenname.
  function firmenEtikett(f, b) {
    return (b.muster ? 'Musterbeitrag · ' : b.gesponsert !== false ? 'Anzeige · ' : '') + f.name;
  }
  function firmenKarte(f, b) {
    const bild = b.bild && b.bild.src
      ? '<span class="ressort-firmen__bild' + (b.bild.logo ? ' ressort-firmen__bild--logo' : '') + '"><img src="' + esc(b.bild.src) + '" alt="' + esc(b.bild.alt || '') + '" width="480" height="270" loading="lazy" decoding="async">' +
        (b.muster ? '<span class="ressort-dropdown__muster">Musterbeitrag</span>' : '') + '</span>'
      : '';
    return '<div class="ressort-firmen__beitrag"><a class="ressort-firmen__karte' + (bild ? '' : ' ohne-bild') + '" href="' + esc(b.url) + '">' + bild +
      '<span class="ressort-firmen__meta">' + esc(firmenEtikett(f, b)) + (b.zeit ? ' · ' + esc(b.zeit) : '') + '</span>' +
      '<strong>' + esc(b.titel) + '</strong>' +
    '</a>' + (bild && !b.bild.logo ? nachweis(b.bild, 'ressort-firmen__credit') : '') + '</div>';
  }
  function firmenAnsicht(f) {
    const karten = (f.beitraege || []).filter((b) => b && b.url && b.titel).slice(0, 3).map((b) => firmenKarte(f, b)).join('');
    return '<div class="ressort-firmen__ansicht">' +
      '<span class="ressort-dropdown__eyebrow">' + esc(f.name) + (f.muster ? ' · Beispielfirma' : ' · Unternehmenskanal') + '</span>' +
      '<div class="ressort-firmen__karten">' + karten +
        // Firma ohne Beitrag (24.10.2026): kurzer Hinweis statt leerer Fläche.
        (karten ? '' : '<p class="ressort-firmen__leer">Noch keine Beiträge von ' + esc(f.name) + '.</p>') +
        '<a class="ressort-firmen__kanal" href="' + esc(f.url) + '">' + firmenLogo(f) +
          '<strong>' + esc(f.name) + '</strong><span>' + esc(f.zeile || '') + '</span>' +
          (f.muster ? '<em>Beispielfirma · kein echtes Unternehmen</em>' : f.hinweis ? '<em>' + esc(f.hinweis) + '</em>' : '') +
          '<span class="ressort-firmen__kanal-cta">Zum Unternehmenskanal</span>' +
        '</a>' +
      '</div>' +
    '</div>';
  }
  function standardRechts(def) {
    const newest = (def.neu || []).filter((x) => x && x.url && x.titel).slice(0, 4).map(story).join('');
    return newest ? '<aside class="ressort-dropdown__latest" aria-label="Neu von Unternehmen"><span class="ressort-dropdown__eyebrow">Neu von Unternehmen</span>' + newest + '</aside>' : '';
  }
  function renderFirmen(inner, link, def) {
    const route = link.getAttribute('href');
    const firmen = def.firmen.filter((f) => f && f.name && f.url);
    const gruppen = gruppenVon(def);
    const punkte = gruppen.length ? '' : punkteHtml(def, (def.titel || 'Unternehmen') + ': Bereiche');
    inner.classList.add('mit-firmen');
    inner.innerHTML =
      '<div class="ressort-dropdown__head">' +
        '<span class="ressort-dropdown__eyebrow">Ressort</span>' +
        '<strong>' + esc(def.titel || link.textContent.trim()) + '</strong>' +
        '<p>' + esc(intro(route, def)) + '</p>' +
        '<a class="ressort-dropdown__all" href="' + esc(route) + '">' + esc(def.alle || 'Alle Meldungen') + '</a>' +
        (gruppen.length ? '<div class="ressort-firmen__gruppen ist-voll">' + gruppenHtml(gruppen) + '</div>'
          : punkte ? '<div class="ressort-firmen__gruppen">' + punkte + '</div>' : '') +
      '</div>' +
      '<nav class="ressort-firmen__liste" aria-label="Unternehmen">' +
        '<span class="ressort-dropdown__eyebrow">Unternehmen</span>' +
        firmen.map((f, i) => '<a class="ressort-firmen__firma" href="' + esc(f.url) + '" data-firma="' + i + '" aria-controls="' + RECHTS_ID + '" aria-expanded="false">' +
          firmenLogo(f) + '<span class="ressort-firmen__name"><strong>' + esc(f.name) + '</strong><small>' + esc(f.zeile || '') + '</small>' +
          (f.muster ? '<em>Beispielfirma</em>' : '') + '</span></a>').join('') +
        (firmen.some((f) => f.muster) ? '<p class="ressort-firmen__hinweis">Beispielfirmen zeigen, wie Betriebe hier erscheinen. Es sind keine echten Unternehmen.</p>' : '') +
      '</nav>' +
      '<section class="ressort-firmen__rechts" id="' + RECHTS_ID + '" aria-label="Beiträge der Unternehmen">' + standardRechts(def) + '</section>';
    firmenVerhalten(inner, def, firmen);
  }

  // Firma waehlen wie das Menue selbst: mit der Maus nach kurzem Verweilen
  // (160 ms), wer nur darueber streicht, wechselt nichts. Zieht die Maus schraeg
  // zu den Beitraegen rechts, bleibt die gewaehlte Firma stehen (Richtung zaehlt,
  // wie bei Amazons Menue). Tippen: erster Tipp zeigt die Beitraege, zweiter
  // oeffnet den Kanal. Tastatur: Fokus zeigt die Beitraege, Pfeile wandern.
  function firmenVerhalten(inner, def, firmen) {
    const rechts = inner.querySelector('.ressort-firmen__rechts');
    const eintraege = [...inner.querySelectorAll('[data-firma]')];
    let aktiv = -1, warte = 0, spur = [];
    const zeige = (i) => {
      clearTimeout(warte); warte = 0;
      if (i === aktiv) return;
      aktiv = i;
      eintraege.forEach((a, n) => {
        a.classList.toggle('ist-aktiv', n === i);
        a.setAttribute('aria-expanded', String(n === i));
        if (n === i) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current');
      });
      rechts.innerHTML = firmenAnsicht(firmen[i]);
      rechts.classList.remove('ist-neu'); void rechts.offsetWidth; rechts.classList.add('ist-neu');
    };
    // Zielt die Maus auf den rechten Bereich? Steigung der letzten Bewegung
    // liegt zwischen den Linien zur oberen und unteren linken Ecke. Nur
    // Bewegung der letzten 100 ms zaehlt: steht der Zeiger, gilt die Firma
    // unter ihm sofort (sonst wirkte eine alte Spur noch nach).
    const zielt = () => {
      const frisch = spur.filter((p) => performance.now() - p[2] < 100);
      if (frisch.length < 2 || aktiv < 0) return false;
      const r = rechts.getBoundingClientRect();
      const [x0, y0] = frisch[0], [x1, y1] = frisch[frisch.length - 1];
      if (x1 <= x0 || r.left <= x0) return false;
      const s = (y1 - y0) / (x1 - x0);
      return s > (r.top - 40 - y0) / (r.left - x0) && s < (r.bottom + 40 - y0) / (r.left - x0);
    };
    const liste = inner.querySelector('.ressort-firmen__liste');
    liste.addEventListener('pointermove', (e) => {
      if (e.pointerType !== 'mouse') return;
      spur.push([e.clientX, e.clientY, e.timeStamp]);
      if (spur.length > 6) spur.shift();
    });
    eintraege.forEach((a, i) => {
      let tipp = false;
      a.addEventListener('pointerenter', (e) => {
        if (e.pointerType !== 'mouse') return;
        clearTimeout(warte);
        let versuche = 0;
        const pruefe = () => { if (zielt() && versuche++ < 3) { warte = setTimeout(pruefe, 100); return; } zeige(i); };
        warte = setTimeout(pruefe, 160);
      });
      a.addEventListener('pointerleave', (e) => { if (e.pointerType === 'mouse') { clearTimeout(warte); warte = 0; } });
      a.addEventListener('pointerdown', (e) => { tipp = e.pointerType === 'touch' || e.pointerType === 'pen'; });
      // Fokus per Tastatur zeigt die Beitraege; beim Tippen entscheidet der Klick.
      a.addEventListener('focus', () => { if (!tipp && !a.matches(':hover')) zeige(i); });
      a.addEventListener('click', (e) => {
        if (tipp && aktiv !== i) { e.preventDefault(); zeige(i); }
        tipp = false;
      });
      a.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
          e.preventDefault();
          const n = eintraege[(i + (e.key === 'ArrowDown' ? 1 : eintraege.length - 1)) % eintraege.length];
          n.focus();
        } else if (e.key === 'ArrowRight' || (e.key === 'Tab' && !e.shiftKey && aktiv === i)) {
          // Tab fuehrt wie Pfeil rechts zu den Beitraegen dieser Firma (Aufklapp-Muster),
          // von deren letztem Link weiter zur naechsten Firma.
          const ziel = (zeige(i), rechts.querySelector('a'));
          if (ziel) { e.preventDefault(); ziel.focus(); }
        }
      });
    });
    rechts.addEventListener('keydown', (e) => {
      const links = [...rechts.querySelectorAll('a')];
      const zurueck = eintraege[aktiv >= 0 ? aktiv : 0];
      if (e.key === 'ArrowLeft' || (e.key === 'Tab' && e.shiftKey && e.target === links[0])) {
        if (zurueck) { e.preventDefault(); zurueck.focus(); }
      } else if (e.key === 'Tab' && !e.shiftKey && e.target === links[links.length - 1] && aktiv >= 0 && aktiv < eintraege.length - 1) {
        e.preventDefault(); eintraege[aktiv + 1].focus();
      }
    });
  }

  function render(inner, link, def) {
    inner.classList.remove('mit-firmen');
    if (Array.isArray(def.firmen) && def.firmen.length) { renderFirmen(inner, link, def); return; }
    const route = link.getAttribute('href');
    const gruppen = gruppenVon(def);
    const punkte = gruppen.length ? '' : punkteHtml(def, (def.titel || link.textContent.trim()) + ': Bereiche');
    const werbung = gruppen.length && def.werbung ? werbungHtml() : '';

    // Nur echte Beitraege; ohne Beitraege bleibt die Spalte weg.
    const newest = (def.neu || []).filter((x) => x && x.url && x.titel).slice(0, 4).map(story).join('');
    // Weitere Bildmeldungen links unter dem Ressortnamen (Sport).
    const mehr = (def.mehr || []).filter((x) => x && x.url && x.titel && x.bild).map(story).join('');
    // Termine und Tipp zeigen rechts kommende Termine, nicht die neuesten Beitraege (16.10.2026).
    const spalte = route === '/termine/' ? 'Nächste Termine' : route === '/tipp/' ? 'Unsere Tipps' : 'Neu im Ressort';
    inner.innerHTML =
      '<div class="ressort-dropdown__head">' +
        '<span class="ressort-dropdown__eyebrow">Ressort</span>' +
        '<strong>' + esc(def.titel || link.textContent.trim()) + '</strong>' +
        '<p>' + esc(intro(route, def)) + '</p>' +
        '<a class="ressort-dropdown__all" href="' + esc(route) + '">' + esc(def.alle || 'Alle Meldungen') + '</a>' +
        (mehr ? '<div class="ressort-dropdown__mehr" aria-label="Weitere Meldungen">' + mehr + '</div>' : '') +
      '</div>' +
      (gruppen.length
        ? '<div class="ressort-dropdown__groups ist-voll' + (werbung ? ' mit-werbung' : '') + '">' + gruppenHtml(gruppen) + werbung + '</div>'
        : '<div class="ressort-dropdown__groups">' + punkte + '</div>') +
      (newest ? '<aside class="ressort-dropdown__latest" aria-label="' + spalte + '">' +
        '<span class="ressort-dropdown__eyebrow">' + spalte + '</span>' +
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
    // Wie bis 14.10.2026: Anzeigedaten abwarten, damit schon das erste Aufklappen die Anzeige zeigt.
    if (Object.values(data).some((d) => d.werbung && gruppenVon(d).length)) await werbungVorbereiten();
    let active = null;
    let schwebeAuf = 0, schwebeZu = 0, zeiger = '';
    // Spur der Maus in der Leiste: Wer bei offenem Menue schraeg nach unten
    // ins Panel zieht (etwa von „Unternehmen“ zu den Firmen) und dabei ein
    // anderes Ressort streift, behaelt das offene Menue (Richtung zaehlt wie
    // bei der Firmenliste). Steht der Zeiger, wird gewechselt.
    let navSpur = [];
    nav.addEventListener('pointermove', (e) => {
      if (e.pointerType !== 'mouse') return;
      navSpur.push([e.clientX, e.clientY, e.timeStamp]);
      if (navSpur.length > 8) navSpur.shift();
    });
    const zieltInsPanel = () => {
      const frisch = navSpur.filter((p) => performance.now() - p[2] < 100);
      if (panel.hidden || frisch.length < 2) return false;
      const [x0, y0] = frisch[0], [x1, y1] = frisch[frisch.length - 1];
      if (y1 - y0 < 1) return false;
      const r = panel.getBoundingClientRect();
      const xOben = x1 + (r.top - y1) * (x1 - x0) / (y1 - y0);
      return xOben >= r.left && xOben <= r.right;
    };
    const wechsle = (link, n) => {
      schwebeAuf = 0;
      if (active === link) return;
      if (active && n < 3 && zieltInsPanel()) { schwebeAuf = setTimeout(() => wechsle(link, n + 1), 100); return; }
      open(link);
    };

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
      // Zweiter Tipp auf das offene Ressort: Ressortseite oeffnen.
      if (active === link && !panel.hidden) {
        location.href = link.href;
        return;
      }
      if (active) {
        active.classList.remove('ressort-open');
        active.setAttribute('aria-expanded', 'false');
      }
      active = link;
      render(inner, link, def, data);
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
      link.addEventListener('pointerdown', (e) => { zeiger = e.pointerType; });
      // Wunsch Betreiber 05.10.2026: Mit Maus oder Trackpad oeffnet das Menue
      // beim Ueberfahren, ein Klick fuehrt sofort auf die Ressortseite (kein
      // zweiter Klick). Nur beim Tippen auf Touchscreens oeffnet der erste Tipp
      // das Menue und der zweite die Seite. Tastatur: Enter oeffnet die Seite,
      // Pfeil nach unten das Menue.
      link.addEventListener('click', (e) => {
        const tipp = e.detail !== 0 && (zeiger === 'touch' || zeiger === 'pen');
        zeiger = '';
        clearTimeout(schwebeAuf); schwebeAuf = 0;
        clearTimeout(schwebeZu);
        // Handy: Unternehmen oeffnet per Tippen die Menuespalte mit den Firmen
        // (ein Aufklappmenue gibt es unter 768 px nicht).
        if (!desktop() && e.detail !== 0 && Array.isArray(data[route].firmen) && data[route].firmen.length && drawerFirmenOeffnen(route)) { e.preventDefault(); return; }
        if (!desktop() || !tipp) return;
        e.preventDefault();
        open(link);
      });
      // Oeffnen beim Ueberfahren mit kurzer Verzoegerung (wie Oberberg
      // Aktuell); wer nur ueber die Leiste streicht, loest nichts aus. Nur
      // echte Mausbewegung zaehlt, auch vom Trackpad am iPad (dort meldet der
      // Browser kein "hover: hover", deshalb hier keine solche Pruefung).
      link.addEventListener('pointermove', (e) => {
        if (e.pointerType !== 'mouse' || schwebeAuf || active === link) return;
        if (!desktop() || !data[link.getAttribute('href')]) return;
        clearTimeout(schwebeZu);
        schwebeAuf = setTimeout(() => wechsle(link, 0), active ? 60 : 160);
      });
      link.addEventListener('pointerleave', () => { clearTimeout(schwebeAuf); schwebeAuf = 0; });
      link.addEventListener('keydown', (e) => {
        if (!desktop() || e.key !== 'ArrowDown') return;
        e.preventDefault();
        open(link);
        requestAnimationFrame(() => panel.querySelector('a') && panel.querySelector('a').focus());
      });
    }

    panel.addEventListener('click', (e) => {
      if (!e.defaultPrevented && e.target.closest('a')) close();
    });
    // Verlaesst die Maus Leiste und Panel, schliesst es nach einer Pause. Wer
    // kurz darueber hinaus faehrt und zurueckkommt, behaelt das Menue offen
    // (Auftrag Betreiber 13.10.2026: nicht sofort zuklappen). Das Panel liegt in
    // der Leiste, beide melden das Verlassen: nur ein Zeitgeber, sonst schloss
    // der erste trotz Rueckkehr.
    const bereich = [nav, panel];
    for (const el of bereich) {
      el.addEventListener('pointerleave', (e) => {
        if (e.pointerType !== 'mouse' || !desktop()) return;
        if (bereich.some((x) => x.contains(e.relatedTarget))) return;
        clearTimeout(schwebeAuf); schwebeAuf = 0;
        clearTimeout(schwebeZu);
        schwebeZu = setTimeout(() => close(), 400);
      });
      el.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') clearTimeout(schwebeZu); });
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
  // Link, der Knopf daneben oeffnet die Bereiche, wie oben nur die Titel).
  // Immer nur eines offen.
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
      knopf.innerHTML = '<span class="sr-only">' + esc(def.titel) + (gruppenVon(def).length ? ': Unterseiten' : ': Bereiche') + '</span>';
      const liste = document.createElement('div');
      liste.className = 'drawer-ressort__liste';
      liste.id = id;
      liste.hidden = true;
      const gruppen = gruppenVon(def);
      liste.innerHTML = (gruppen.length
        ? gruppen.map((g) => '<div class="drawer-ressort__gruppe">' + esc(g.titel) + '</div>' + g.links.map((l) => '<a href="' + esc(l[1]) + '">' + esc(l[0]) + '</a>').join('')).join('')
        : punkteVon(def).map((p) => '<a href="' + esc(p.href) + '">' + esc(p.titel) + '</a>').join('')) +
        drawerFirmen(def, id);
      zeile.append(a, knopf, liste);
      drawerFirmenVerhalten(liste);
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

  // Firmen im Drawer: Name fuehrt in den Kanal, der Knopf daneben klappt die
  // Beitraege auf (immer nur eine Firma offen).
  function drawerFirmen(def, id) {
    const firmen = (def.firmen || []).filter((f) => f && f.name && f.url);
    if (!firmen.length) return '';
    return '<div class="drawer-ressort__gruppe">Unternehmen</div>' +
      firmen.map((f, i) => {
        const fid = id + '-firma-' + i;
        return '<div class="drawer-firma"><a href="' + esc(f.url) + '">' + esc(f.name) + '<small>' + esc(f.zeile || '') + (f.muster ? ' · Beispielfirma' : '') + '</small></a>' +
          '<button type="button" class="drawer-ressort__auf drawer-firma__auf" aria-expanded="false" aria-controls="' + fid + '"><span class="sr-only">' + esc(f.name) + ': Beiträge</span></button>' +
          '<div class="drawer-firma__liste" id="' + fid + '" hidden>' +
            (f.beitraege || []).filter((b) => b && b.url && b.titel).slice(0, 3).map((b) => '<a href="' + esc(b.url) + '">' + esc(b.titel) + (b.muster ? '<small>Musterbeitrag</small>' : b.gesponsert !== false ? '<small>Anzeige</small>' : '') + '</a>').join('') +
            '<a class="drawer-firma__kanal" href="' + esc(f.url) + '">Zum Unternehmenskanal</a>' +
          '</div></div>';
      }).join('') +
      (firmen.some((f) => f.muster) ? '<p class="drawer-firma__hinweis">Beispielfirmen sind keine echten Unternehmen.</p>' : '');
  }
  function drawerFirmenVerhalten(liste) {
    const knoepfe = [...liste.querySelectorAll('.drawer-firma__auf')];
    for (const k of knoepfe) {
      k.addEventListener('click', () => {
        const auf = k.getAttribute('aria-expanded') !== 'true';
        for (const x of knoepfe) { x.setAttribute('aria-expanded', String(x === k && auf)); document.getElementById(x.getAttribute('aria-controls')).hidden = !(x === k && auf); }
      });
    }
  }
  // Handy: Menuespalte oeffnen und das Ressort aufklappen (assets/app.js oeffnet sie ueber den Menueknopf).
  function drawerFirmenOeffnen(route) {
    const knopf = document.querySelector('[data-menu]');
    const zeile = [...document.querySelectorAll('[data-drawer] .drawer-ressort')].find((z) => z.querySelector(':scope > a') && z.querySelector(':scope > a').getAttribute('href') === route);
    if (!knopf || !zeile) return false;
    knopf.click();
    const auf = zeile.querySelector('.drawer-ressort__auf');
    if (auf && auf.getAttribute('aria-expanded') !== 'true') auf.click();
    // Der Seitenkopf liegt ueber der Menuespalte: mit seinem Abstand scrollen.
    const kopf = document.querySelector('.masthead');
    zeile.style.scrollMarginTop = (kopf ? Math.max(0, Math.round(kopf.getBoundingClientRect().bottom)) + 8 : 8) + 'px';
    requestAnimationFrame(() => { zeile.scrollIntoView({ block: 'start' }); const ziel = zeile.querySelector('.drawer-firma > a') || auf; if (ziel) ziel.focus({ preventScroll: true }); });
    return true;
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
})();
