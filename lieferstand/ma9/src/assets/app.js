/* Merzenich Aktuell v9 – Client-Skript. Vanilla JS, keine Abhängigkeiten, keine Cookies. */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  function store(k, v) { try { if (v === undefined) return localStorage.getItem(k); localStorage.setItem(k, v); } catch (e) { return null; } }

  /* ---------- Fokusfalle für modale Dialoge (Menü, Bildergalerie) ---------- */
  function trapFocus(e, container) {
    if (e.key !== 'Tab' || !container) return;
    var f = $$('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])', container);
    if (!f.length) return;
    var first = f[0], last = f[f.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  }

  /* ---------- Mobile-Menü ---------- */
  // Die klebende Ressortleiste zeigt jetzt an, dass sie klebt.
  var nav = $('.mainnav');
  if (nav) {
    var navOben = nav.offsetTop;
    var pruefeNav = function () { nav.classList.toggle('klebt', window.scrollY > navOben); };
    window.addEventListener('scroll', pruefeNav, { passive: true });
    pruefeNav();
  }

  var drawer = $('[data-drawer]'), openBtn = $('[data-menu]');
  function setDrawer(open) {
    if (!drawer) return;
    drawer.classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
    Array.prototype.forEach.call(document.body.children, function (el) { if (el !== drawer) el.inert = open; });
    if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) { var f = $('a, button, input', drawer); if (f) f.focus(); } else if (openBtn) openBtn.focus();
  }
  if (openBtn) openBtn.addEventListener('click', function () { setDrawer(true); });
  if (drawer) drawer.addEventListener('click', function (e) { if (e.target.classList.contains('scrim') || e.target.closest('[data-close]')) setDrawer(false); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { setDrawer(false); closeLb(); }
    else if (e.key === 'Tab') {
      if (drawer && drawer.classList.contains('open')) trapFocus(e, $('.panel', drawer));
      else if (lb && lb.classList.contains('on')) trapFocus(e, lb);
    }
  });

  /* ---------- Bild-Fallback: Quellbilder können ablaufen ---------- */
  function fallback(img) {
    var ph = img.getAttribute('data-ph');
    var m = img.closest('.media');
    if (m && m.dataset.ressort) {
      // Quellbild nicht erreichbar: dieselbe gesetzte Textkachel wie bei
      // Meldungen ganz ohne Bild, statt der schwarzen Platzhaltergrafik.
      var q = m.dataset.quelle;
      m.className = 'media none';
      m.innerHTML = '<div class="textcard"><span class="tc-ressort"></span><span class="tc-rule" aria-hidden="true"></span>' +
        (q ? '<span class="tc-quelle"></span>' : '') + '<span class="tc-hint">kein Foto zum Ereignis</span></div>';
      m.querySelector('.tc-ressort').textContent = m.dataset.ressort;
      if (q) m.querySelector('.tc-quelle').textContent = q;
      return;
    }
    if (ph && img.src.indexOf(ph) === -1) { img.removeAttribute('srcset'); img.removeAttribute('sizes'); img.src = ph; img.classList.add('ph-img'); if (m) m.classList.add('failed'); }
  }
  $$('img[data-ph]').forEach(function (img) {
    img.addEventListener('error', function () { fallback(img); });
    if (img.complete && img.naturalWidth === 0 && img.src) fallback(img);
  });

  /* ---------- Sticky-Navigation: aktuelle Rubrik in Sicht scrollen ---------- */
  var cur = $('.navscroll a[aria-current]');
  if (cur && cur.scrollIntoView) { try { cur.scrollIntoView({ inline: 'center', block: 'nearest' }); window.scrollTo(0, 0); } catch (e) { } }

  /* ---------- Teilen ---------- */
  $$('[data-share]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var url = location.href, title = document.title;
      if (navigator.share) { navigator.share({ title: title, url: url }).catch(function () { }); }
      else if (navigator.clipboard) { navigator.clipboard.writeText(url).then(function () { btn.classList.add('done'); btn.setAttribute('title', 'Link kopiert'); var st = btn.parentElement.querySelector('[data-share-status]'); if (st) { st.textContent = 'Link kopiert'; setTimeout(function () { st.textContent = ''; }, 2000); } setTimeout(function () { btn.classList.remove('done'); }, 2000); }).catch(function () { }); }
    });
  });

  /* ---------- Drucken ---------- */
  $$('[data-print]').forEach(function (b) { b.addEventListener('click', function () { window.print(); }); });

  /* ---------- Formulare: Sende-Status ---------- */
  $$('form[data-netlify]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var b = $('button[type=submit]', form); if (b) { b.disabled = true; b.dataset.label = b.textContent; b.textContent = 'Wird gesendet …'; }
    });
  });

  /* ---------- Umfrage (lokal, ohne Server) ---------- */
  var poll = $('[data-poll]');
  if (poll) {
    var key = 'ma-poll-' + poll.getAttribute('data-poll');
    var opts = $$('.opt', poll), note = $('small', poll);
    var st = null; try { st = JSON.parse(store(key) || 'null'); } catch (e) { }
    function render(state) {
      var total = state.counts.reduce(function (a, b) { return a + b; }, 0) || 1;
      opts.forEach(function (btn, i) {
        var pct = Math.round((state.counts[i] / total) * 100);
        $('.bar', btn).style.transform = 'scaleX(' + (pct / 100) + ')'; $('.pct', btn).textContent = pct + '%'; btn.disabled = true;
        if (i === state.choice) btn.classList.add('mine');
      });
      if (note) note.textContent = 'Danke. Diese Auswertung liegt in Ihrem Browser; die Redaktion veröffentlicht Gesamtergebnisse in der nächsten Ausgabe.';
    }
    if (st && st.counts) render(st);
    else opts.forEach(function (btn, i) { btn.addEventListener('click', function () { var s = { choice: i, counts: opts.map(function (_, j) { return j === i ? 1 : 0; }) }; store(key, JSON.stringify(s)); render(s); }); });
  }

  /* ---------- Filter-Chips (Termine, Vereine, Betriebe) ---------- */
  $$('[data-filter-root]').forEach(function (root) {
    var list = $('[data-filter-list]'), empty = $('[data-filter-empty]'), cnt = $('[data-filter-count]', root);
    if (!list) return;
    $$('.chip', root).forEach(function (chip) {
      chip.addEventListener('click', function () {
        $$('.chip', root).forEach(function (c) { c.classList.remove('on'); c.setAttribute('aria-pressed', 'false'); }); chip.classList.add('on'); chip.setAttribute('aria-pressed', 'true');
        var f = chip.getAttribute('data-filter'), n = 0;
        $$('[data-ort]', list).forEach(function (el) {
          var show = f === '*' || (f.indexOf('ort:') === 0 && el.getAttribute('data-ort') === f.slice(4)) || (f.indexOf('cat:') === 0 && el.getAttribute('data-cat') === f.slice(4));
          el.hidden = !show; if (show) n++;
        });
        $$('.month-h', list).forEach(function (h) { var sib = h.nextElementSibling, any = false; while (sib && !sib.classList.contains('month-h')) { if (sib.hasAttribute('data-ort') && !sib.hidden) any = true; sib = sib.nextElementSibling; } h.hidden = !any; });
        if (empty) empty.hidden = n > 0;
        if (cnt) cnt.textContent = f === '*' ? '' : n + (list.classList.contains('event-list') ? ' Termine' : ' Einträge') + ' angezeigt';
      });
    });
  });

  /* ---------- Suche über den statischen Index ---------- */
  var sf = $('[data-search]');
  if (sf) {
    var input = $('input', sf), outEl = $('[data-results]'), idx = null;
    function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
    function norm(s) { return s.toLowerCase().replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss'); }
    function run() {
      var q = norm((input.value || '').trim());
      if (!outEl) return;
      if (q.length < 2) { outEl.innerHTML = '<p class="no-result">Bitte mindestens zwei Zeichen eingeben.</p>'; return; }
      if (!idx) { outEl.innerHTML = '<p class="no-result">Index wird geladen …</p>'; return; }
      var words = q.split(/\s+/);
      var hits = idx.map(function (it) {
        var hay = norm(it.t + ' ' + it.d + ' ' + (it.k || '') + ' ' + (it.g || '') + ' ' + (it.b || ''));
        var ok = words.every(function (w) { return hay.indexOf(w) !== -1; });
        if (!ok) return null;
        var score = words.reduce(function (s, w) { return s + (norm(it.t).indexOf(w) !== -1 ? 5 : 0) + (norm(it.g || '').indexOf(w) !== -1 ? 2 : 0) + 1; }, 0);
        return { it: it, score: score };
      }).filter(Boolean).sort(function (a, b) { return b.score - a.score; });
      if (!hits.length) { outEl.innerHTML = '<p class="no-result">Keine Treffer für „' + esc(input.value) + '“. Versuchen Sie ein anderes Stichwort, zum Beispiel Feuerwehr, Ortsfest oder Schützen.</p>'; return; }
      outEl.innerHTML = '<p class="count-line">' + hits.length + ' Treffer</p>' + hits.slice(0, 50).map(function (h) {
        var it = h.it;
        return '<article class="result"><span class="kicker">' + esc(it.typ) + (it.k && it.k !== it.typ ? ' · ' + esc(it.k) : '') + (it.dt ? '<span class="dist">' + esc(it.dt) + '</span>' : '') + '</span><h3><a href="' + esc(it.u) + '">' + esc(it.t) + '</a></h3><p>' + esc(it.d) + '</p></article>';
      }).join('');
    }
    fetch('/suche-index.json').then(function (r) { return r.json(); }).then(function (j) { idx = j; run(); }).catch(function () { idx = []; run(); });
    sf.addEventListener('submit', function (e) { e.preventDefault(); run(); });
    input.addEventListener('input', run);
    var pre = new URLSearchParams(location.search).get('q');
    if (pre) input.value = pre;
    input.focus();
  }

  /* ---------- Wetter für Merzenich (Open-Meteo, ohne Schlüssel, ohne Cookies) ---------- */
  var weatherBox = $('[data-weather]') || $('[data-weather-mini]') && document.createElement('div');
  if (weatherBox) {
    var SYM = {
      sun: '<circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.4M12 19.6V22M4.2 4.2l1.7 1.7M18.1 18.1l1.7 1.7M2 12h2.4M19.6 12H22M4.2 19.8l1.7-1.7M18.1 5.9l1.7-1.7"/>',
      cloudsun: '<circle cx="8.5" cy="8" r="3"/><path d="M8.5 1.6v1.6M3.4 8H1.8M4.9 4.4L3.8 3.3M12.1 4.4l1.1-1.1"/><path d="M7 20h10.4a3.6 3.6 0 000-7.2h-.5a5 5 0 00-9.6 1.3A3 3 0 007 20z"/>',
      cloud: '<path d="M7 19h10.4a3.6 3.6 0 000-7.2h-.5a5 5 0 00-9.6 1.3A3 3 0 007 19z"/>',
      rain: '<path d="M7 15h10.4a3.6 3.6 0 000-7.2h-.5a5 5 0 00-9.6 1.3A3 3 0 007 15z"/><path d="M8.5 18.4l-1 2.4M12.5 18.4l-1 2.4M16.5 18.4l-1 2.4"/>',
      snow: '<path d="M7 15h10.4a3.6 3.6 0 000-7.2h-.5a5 5 0 00-9.6 1.3A3 3 0 007 15z"/><path d="M9 19h.01M12.5 20.5h.01M16 19h.01"/>',
      storm: '<path d="M7 14h10.4a3.6 3.6 0 000-7.2h-.5a5 5 0 00-9.6 1.3A3 3 0 007 14z"/><path d="M13 16l-3 4h3l-1 3"/>',
      fog: '<path d="M7 13h10.4a3.6 3.6 0 000-7.2h-.5a5 5 0 00-9.6 1.3A3 3 0 007 13z"/><path d="M5 17h14M7 20.5h10"/>'
    };
    var CODES = { 0: ['sun', 'Klar'], 1: ['sun', 'Überwiegend klar'], 2: ['cloudsun', 'Teils bewölkt'], 3: ['cloud', 'Bedeckt'], 45: ['fog', 'Nebel'], 48: ['fog', 'Reifnebel'], 51: ['rain', 'Leichter Nieselregen'], 53: ['rain', 'Nieselregen'], 55: ['rain', 'Dichter Nieselregen'], 56: ['rain', 'Gefrierender Niesel'], 57: ['rain', 'Gefrierender Niesel'], 61: ['rain', 'Leichter Regen'], 63: ['rain', 'Regen'], 65: ['rain', 'Starker Regen'], 66: ['rain', 'Gefrierender Regen'], 67: ['rain', 'Gefrierender Regen'], 71: ['snow', 'Leichter Schneefall'], 73: ['snow', 'Schneefall'], 75: ['snow', 'Starker Schneefall'], 77: ['snow', 'Schneegriesel'], 80: ['rain', 'Schauer'], 81: ['rain', 'Kräftige Schauer'], 82: ['rain', 'Heftige Schauer'], 85: ['snow', 'Schneeschauer'], 86: ['snow', 'Schneeschauer'], 95: ['storm', 'Gewitter'], 96: ['storm', 'Gewitter mit Hagel'], 99: ['storm', 'Schweres Gewitter'] };
    var DAYS = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
    function svg(n, cls) { return '<span class="' + cls + '"><svg viewBox="0 0 24 24" aria-hidden="true">' + (SYM[n] || SYM.cloud) + '</svg></span>'; }
    function look(c) { return CODES[c] || ['cloud', 'Wechselhaft']; }
    var cached = null; try { cached = JSON.parse(store('ma-weather') || 'null'); } catch (e) { }
    function renderW(d) {
      var nowW = look(d.current.weather_code), temp = Math.round(d.current.temperature_2m), feels = Math.round(d.current.apparent_temperature);
      var html = '<div class="weather-now">' + svg(nowW[0], 'icon') + '<div><span class="temp">' + temp + '°</span></div><div class="what">' + nowW[1] + '<small>gefühlt ' + feels + '° · Wind ' + Math.round(d.current.wind_speed_10m) + ' km/h</small></div></div><div class="weather-days">';
      for (var i = 1; i < Math.min(4, d.daily.time.length); i++) {
        var day = look(d.daily.weather_code[i]), when = new Date(d.daily.time[i] + 'T12:00:00');
        html += '<div class="weather-day"><b>' + DAYS[when.getDay()] + '</b>' + svg(day[0], 'd-ic') + '<span class="hi">' + Math.round(d.daily.temperature_2m_max[i]) + '°</span> <span class="lo">' + Math.round(d.daily.temperature_2m_min[i]) + '°</span><small>' + Math.round(d.daily.precipitation_probability_max[i]) + '% Regen</small></div>';
      }
      html += '</div><p class="weather-src">Merzenich · Daten: <a href="https://open-meteo.com/" target="_blank" rel="noopener">Open-Meteo</a>, ohne Cookies</p>';
      weatherBox.innerHTML = html;
      var mini = $('[data-weather-mini]'); if (mini) mini.textContent = temp + '°C ' + nowW[1] + ' · Merzenich';
    }
    if (cached && cached.t && Date.now() - cached.t < 20 * 60000) {
      renderW(cached.d);
    } else {
      fetch('https://api.open-meteo.com/v1/forecast?latitude=50.8317&longitude=6.5361&current=temperature_2m,apparent_temperature,weather_code,wind_speed_10m&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max&timezone=Europe%2FBerlin&forecast_days=4', { referrerPolicy: 'no-referrer' })
        .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(function (d) { renderW(d); store('ma-weather', JSON.stringify({ t: Date.now(), d: d })); })
        .catch(function () { if (cached && cached.d) renderW(cached.d); else weatherBox.innerHTML = '<p class="weather-skel">Die Wetterdaten sind gerade nicht erreichbar.</p>'; });
    }
  }

  /* ---------- Bildergalerie ---------- */
  var shots = $$('[data-shot]'), lb = null, lbIndex = 0, lastFocus = null;
  function closeLb() { if (lb && lb.classList.contains('on')) { lb.classList.remove('on'); document.body.style.overflow = ''; if (lastFocus) lastFocus.focus(); } }
  if (shots.length) {
    lb = document.createElement('div'); lb.className = 'lightbox'; lb.setAttribute('role', 'dialog'); lb.setAttribute('aria-modal', 'true'); lb.setAttribute('aria-label', 'Bild in Großansicht');
    lb.innerHTML = '<div class="lb-scrim" data-lb-close></div><div class="lb-stage"><img alt=""><div class="lb-cap"><b></b><span></span><span class="lb-count"></span></div></div><button class="lb-btn lb-prev" type="button" aria-label="Vorheriges Bild">‹</button><button class="lb-btn lb-next" type="button" aria-label="Nächstes Bild">›</button><button class="lb-btn lb-close" type="button" aria-label="Schließen" data-lb-close>✕</button>';
    document.body.appendChild(lb);
    var lbImg = $('img', lb), lbT = $('.lb-cap b', lb), lbX = $('.lb-cap span', lb), lbC = $('.lb-count', lb);
    function show(i) { lbIndex = (i + shots.length) % shots.length; var b = shots[lbIndex], im = $('img', b); lbImg.src = im ? (im.currentSrc || im.src) : ''; lbImg.alt = im ? im.alt : ''; lbT.textContent = b.getAttribute('data-credit') || ''; lbX.textContent = im ? im.alt : ''; lbC.textContent = 'Bild ' + (lbIndex + 1) + ' von ' + shots.length; }
    shots.forEach(function (b, i) { b.addEventListener('click', function () { lastFocus = document.activeElement; show(i); lb.classList.add('on'); document.body.style.overflow = 'hidden'; $('.lb-close', lb).focus(); }); });
    $('.lb-next', lb).addEventListener('click', function () { show(lbIndex + 1); });
    $('.lb-prev', lb).addEventListener('click', function () { show(lbIndex - 1); });
    $$('[data-lb-close]', lb).forEach(function (el) { el.addEventListener('click', closeLb); });
    document.addEventListener('keydown', function (e) { if (!lb.classList.contains('on')) return; if (e.key === 'ArrowRight') show(lbIndex + 1); if (e.key === 'ArrowLeft') show(lbIndex - 1); });
  }

  /* ---------- Lesefortschritt ---------- */
  var article = $('[data-readable]');
  if (article) {
    var bar = document.createElement('div'); bar.className = 'readbar'; document.body.appendChild(bar);
    var ticking = false;
    function upd() { var box = article.getBoundingClientRect(), total = box.height - window.innerHeight; bar.style.width = ((total > 0 ? Math.min(1, Math.max(0, -box.top / total)) : 0) * 100).toFixed(1) + '%'; ticking = false; }
    window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(upd); } }, { passive: true });
    upd();
  }

  /* ---------- Relative Zeitangaben für frische Meldungen ---------- */
  $$('time[datetime]').forEach(function (t) {
    var d = new Date(t.getAttribute('datetime')), diff = (Date.now() - d) / 60000;
    if (isNaN(diff) || diff < 0 || diff > 1440 || !t.closest('.meta, .ticker')) return;
    t.setAttribute('title', t.textContent);
    t.textContent = diff < 60 ? 'vor ' + Math.max(1, Math.round(diff)) + ' Min.' : 'vor ' + Math.round(diff / 60) + ' Std.';
  });

  /* ---------- Service Worker (Offline-Fallback) ---------- */
  if ('serviceWorker' in navigator && location.protocol === 'https:') {
    window.addEventListener('load', function () { navigator.serviceWorker.register('/sw.js').catch(function () { }); });
  }

  /* ---------- Netlify Identity: Einladungs-/Recovery-Links auf /admin/ weiterleiten ---------- */
  if (/^#(invite_token|recovery_token|confirmation_token|email_change_token)=/.test(location.hash) && location.pathname.indexOf('/admin') !== 0) {
    location.replace('/admin/' + location.hash);
  }
})();
