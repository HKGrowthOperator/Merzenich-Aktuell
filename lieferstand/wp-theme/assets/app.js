/* Merzenich Aktuell v8 — Client-Skript. Vanilla JS, keine Abhängigkeiten. */
(function () {
  'use strict';

  /* ---------- Mobile-Drawer ---------- */
  // Die klebende Ressortleiste zeigt an, dass sie klebt. Gleiche Logik wie in
  // der Netlify-Fassung (ma9/src/assets/app.js).
  var nav = document.querySelector('.mainnav');
  if (nav) {
    var navOben = nav.offsetTop;
    var pruefeNav = function () { nav.classList.toggle('klebt', window.scrollY > navOben); };
    window.addEventListener('scroll', pruefeNav, { passive: true });
    pruefeNav();
  }

  var drawer = document.querySelector('[data-drawer]');
  var openBtn = document.querySelector('[data-menu]');
  function setDrawer(open) {
    if (!drawer) return;
    drawer.classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
    if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  if (openBtn) openBtn.addEventListener('click', function () { setDrawer(true); });
  if (drawer) {
    drawer.addEventListener('click', function (e) {
      if (e.target.classList.contains('scrim') || e.target.closest('[data-close]')) setDrawer(false);
    });
  }
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setDrawer(false); });

  /* ---------- Bild-Fallback: externe Quellbilder können ablaufen ---------- */
  function markFailed(img) {
    var m = img.closest('.media');
    if (m) m.classList.add('failed');
  }
  Array.prototype.forEach.call(document.querySelectorAll('.media img'), function (img) {
    img.addEventListener('error', function () { markFailed(img); });
    if (img.complete && img.naturalWidth === 0) markFailed(img);
  });

  /* ---------- Umfrage (lokal, ohne Server) ---------- */
  var poll = document.querySelector('[data-poll]');
  if (poll) {
    var key = 'ma8-poll-' + (poll.getAttribute('data-poll') || 'default');
    var store = null;
    try { store = JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) { store = null; }
    var opts = Array.prototype.slice.call(poll.querySelectorAll('.opt'));
    var note = poll.querySelector('small');

    function render(state) {
      var total = state.counts.reduce(function (a, b) { return a + b; }, 0) || 1;
      opts.forEach(function (btn, i) {
        var pct = Math.round((state.counts[i] / total) * 100);
        var bar = btn.querySelector('.bar');
        var lbl = btn.querySelector('.pct');
        if (bar) bar.style.width = pct + '%';
        if (lbl) lbl.textContent = pct + '%';
        btn.disabled = true;
        if (i === state.choice) btn.style.borderColor = 'var(--gold)';
      });
      if (note) note.textContent = 'Ihre Stimme ist gespeichert. Diese Auswertung liegt nur in Ihrem Browser, im Livebetrieb zählt sie serverseitig.';
    }

    if (store && store.counts) {
      render(store);
    } else {
      opts.forEach(function (btn, i) {
        btn.addEventListener('click', function () {
          var counts = opts.map(function (_, j) { return j === i ? 1 : 0; });
          var state = { choice: i, counts: counts };
          try { localStorage.setItem(key, JSON.stringify(state)); } catch (e) { /* Privatmodus */ }
          render(state);
        });
      });
    }
  }

  /* ---------- Teilen ---------- */
  Array.prototype.forEach.call(document.querySelectorAll('[data-share]'), function (btn) {
    btn.addEventListener('click', function () {
      var url = location.href, title = document.title;
      if (navigator.share) {
        navigator.share({ title: title, url: url }).catch(function () {});
      } else if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function () {
          btn.setAttribute('title', 'Link kopiert');
          btn.style.borderColor = 'var(--gold)';
        }).catch(function () {});
      }
    });
  });

  /* ---------- Suche über den statischen Index ---------- */
  var sf = document.querySelector('[data-search]');
  if (sf && window.MA_INDEX) {
    var input = sf.querySelector('input');
    var out = document.querySelector('[data-results]');
    function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
    function run() {
      var q = (input.value || '').trim().toLowerCase();
      if (!out) return;
      if (q.length < 2) { out.innerHTML = '<p class="no-result">Bitte mindestens zwei Zeichen eingeben.</p>'; return; }
      var words = q.split(/\s+/);
      var hits = window.MA_INDEX.filter(function (it) {
        var hay = (it.t + ' ' + it.d + ' ' + (it.k || '') + ' ' + (it.g || '')).toLowerCase();
        return words.every(function (w) { return hay.indexOf(w) !== -1; });
      });
      if (!hits.length) { out.innerHTML = '<p class="no-result">Keine Treffer für „' + esc(q) + '“. Versuchen Sie ein anderes Stichwort, zum Beispiel Feuerwehr, Ortsfest oder LEADER.</p>'; return; }
      out.innerHTML = '<p class="count-line">' + hits.length + (hits.length === 1 ? ' Treffer' : ' Treffer') + '</p>' + hits.map(function (it) {
        return '<article class="result"><span class="kicker">' + esc(it.k || '') + '</span>' +
          '<h3><a href="' + esc(it.u) + '">' + esc(it.t) + '</a></h3>' +
          '<p>' + esc(it.d) + '</p></article>';
      }).join('');
    }
    sf.addEventListener('submit', function (e) { e.preventDefault(); run(); });
    input.addEventListener('input', run);
    var pre = new URLSearchParams(location.search).get('q');
    if (pre) { input.value = pre; run(); }
    input.focus();
  }
})();

/* ============================================================
   Erweiterungen: Wetter, Bildergalerie, Lesefortschritt
   ============================================================ */
(function () {
  'use strict';

  /* ---------- Wetter für Merzenich ----------
     Open-Meteo braucht keinen Schlüssel, setzt keine Cookies und überträgt
     keine Besucherdaten. Nur Koordinaten gehen raus. */
  var weatherBox = document.querySelector('[data-weather]');
  if (weatherBox) {
    var SYMBOLS = {
      sun: '<circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.4M12 19.6V22M4.2 4.2l1.7 1.7M18.1 18.1l1.7 1.7M2 12h2.4M19.6 12H22M4.2 19.8l1.7-1.7M18.1 5.9l1.7-1.7"/>',
      cloudsun: '<circle cx="8.5" cy="8" r="3"/><path d="M8.5 1.6v1.6M3.4 8H1.8M4.9 4.4L3.8 3.3M12.1 4.4l1.1-1.1"/><path d="M7 20h10.4a3.6 3.6 0 000-7.2h-.5a5 5 0 00-9.6 1.3A3 3 0 007 20z"/>',
      cloud: '<path d="M7 19h10.4a3.6 3.6 0 000-7.2h-.5a5 5 0 00-9.6 1.3A3 3 0 007 19z"/>',
      rain: '<path d="M7 15h10.4a3.6 3.6 0 000-7.2h-.5a5 5 0 00-9.6 1.3A3 3 0 007 15z"/><path d="M8.5 18.4l-1 2.4M12.5 18.4l-1 2.4M16.5 18.4l-1 2.4"/>',
      snow: '<path d="M7 15h10.4a3.6 3.6 0 000-7.2h-.5a5 5 0 00-9.6 1.3A3 3 0 007 15z"/><path d="M9 19h.01M12.5 20.5h.01M16 19h.01"/>',
      storm: '<path d="M7 14h10.4a3.6 3.6 0 000-7.2h-.5a5 5 0 00-9.6 1.3A3 3 0 007 14z"/><path d="M13 16l-3 4h3l-1 3"/>',
      fog: '<path d="M7 13h10.4a3.6 3.6 0 000-7.2h-.5a5 5 0 00-9.6 1.3A3 3 0 007 13z"/><path d="M5 17h14M7 20.5h10"/>'
    };
    // WMO-Wettercodes auf Symbol und Klartext abbilden
    var CODES = {
      0: ['sun', 'Klar'], 1: ['sun', 'Überwiegend klar'], 2: ['cloudsun', 'Teils bewölkt'], 3: ['cloud', 'Bedeckt'],
      45: ['fog', 'Nebel'], 48: ['fog', 'Reifnebel'],
      51: ['rain', 'Leichter Nieselregen'], 53: ['rain', 'Nieselregen'], 55: ['rain', 'Dichter Nieselregen'],
      56: ['rain', 'Gefrierender Niesel'], 57: ['rain', 'Gefrierender Niesel'],
      61: ['rain', 'Leichter Regen'], 63: ['rain', 'Regen'], 65: ['rain', 'Starker Regen'],
      66: ['rain', 'Gefrierender Regen'], 67: ['rain', 'Gefrierender Regen'],
      71: ['snow', 'Leichter Schneefall'], 73: ['snow', 'Schneefall'], 75: ['snow', 'Starker Schneefall'],
      77: ['snow', 'Schneegriesel'],
      80: ['rain', 'Schauer'], 81: ['rain', 'Kräftige Schauer'], 82: ['rain', 'Heftige Schauer'],
      85: ['snow', 'Schneeschauer'], 86: ['snow', 'Schneeschauer'],
      95: ['storm', 'Gewitter'], 96: ['storm', 'Gewitter mit Hagel'], 99: ['storm', 'Schweres Gewitter']
    };
    var DAYS = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
    function svg(name, cls) {
      return '<span class="' + cls + '"><svg viewBox="0 0 24 24" aria-hidden="true">' + (SYMBOLS[name] || SYMBOLS.cloud) + '</svg></span>';
    }
    function look(code) { return CODES[code] || ['cloud', 'Wechselhaft']; }

    // Merzenich, Kreis Düren
    var url = 'https://api.open-meteo.com/v1/forecast?latitude=50.8317&longitude=6.5361' +
      '&current=temperature_2m,apparent_temperature,weather_code' +
      '&daily=weather_code,temperature_2m_max,temperature_2m_min' +
      '&timezone=Europe%2FBerlin&forecast_days=4';

    fetch(url, { referrerPolicy: 'no-referrer' })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (d) {
        var now = look(d.current.weather_code);
        var temp = Math.round(d.current.temperature_2m);
        var feels = Math.round(d.current.apparent_temperature);
        var html = '<div class="weather-now">' + svg(now[0], 'icon') +
          '<div><span class="temp">' + temp + '°</span></div>' +
          '<div class="what">' + now[1] + '<small>gefühlt ' + feels + '°</small></div></div>';

        html += '<div class="weather-days">';
        for (var i = 1; i < Math.min(4, d.daily.time.length); i++) {
          var day = look(d.daily.weather_code[i]);
          var when = new Date(d.daily.time[i] + 'T12:00:00');
          html += '<div class="weather-day"><b>' + DAYS[when.getDay()] + '</b>' +
            svg(day[0], 'd-ic') +
            '<span class="hi">' + Math.round(d.daily.temperature_2m_max[i]) + '°</span> ' +
            '<span class="lo">' + Math.round(d.daily.temperature_2m_min[i]) + '°</span></div>';
        }
        html += '</div><p class="weather-src">Daten: <a href="https://open-meteo.com/" target="_blank" rel="noopener">Open-Meteo</a>, ohne Cookies</p>';
        weatherBox.innerHTML = html;

        // Kompakte Wetteranzeige im Kopfbereich befüllen, falls vorhanden (z. B. Header).
        var weatherMini = document.querySelector('[data-weather-mini]');
        if (weatherMini) {
          weatherMini.textContent = temp + '°C ' + now[1] + ' · Merzenich';
        }
      })
      .catch(function () {
        weatherBox.innerHTML = '<p class="weather-skel">Die Wetterdaten sind gerade nicht erreichbar.</p>';
      });
  }

  /* ---------- Bildergalerie in Großansicht ---------- */
  var shots = Array.prototype.slice.call(document.querySelectorAll('[data-shot]'));
  if (shots.length) {
    var lb = document.createElement('div');
    lb.className = 'lightbox';
    lb.setAttribute('role', 'dialog');
    lb.setAttribute('aria-modal', 'true');
    lb.setAttribute('aria-label', 'Bild in Großansicht');
    lb.innerHTML =
      '<div class="lb-scrim" data-lb-close></div>' +
      '<div class="lb-stage">' +
      '<img alt="">' +
      '<div class="lb-cap"><b></b><span></span><span class="lb-count"></span></div>' +
      '</div>' +
      '<button class="lb-btn lb-prev" type="button" aria-label="Vorheriges Bild">‹</button>' +
      '<button class="lb-btn lb-next" type="button" aria-label="Nächstes Bild">›</button>' +
      '<button class="lb-btn lb-close" type="button" aria-label="Schließen" data-lb-close>✕</button>';
    document.body.appendChild(lb);

    var lbImg = lb.querySelector('img');
    var lbTitle = lb.querySelector('.lb-cap b');
    var lbText = lb.querySelector('.lb-cap span');
    var lbCount = lb.querySelector('.lb-count');
    var index = 0, lastFocus = null;

    function show(i) {
      index = (i + shots.length) % shots.length;
      var btn = shots[index];
      var img = btn.querySelector('img');
      lbImg.src = img ? img.src : '';
      lbImg.alt = img ? img.alt : '';
      lbTitle.textContent = btn.getAttribute('data-credit') || '';
      lbText.textContent = img ? img.alt : '';
      lbCount.textContent = 'Bild ' + (index + 1) + ' von ' + shots.length;
    }
    function open(i) {
      lastFocus = document.activeElement;
      show(i);
      lb.classList.add('on');
      document.body.style.overflow = 'hidden';
      lb.querySelector('.lb-close').focus();
    }
    function close() {
      lb.classList.remove('on');
      document.body.style.overflow = '';
      if (lastFocus) lastFocus.focus();
    }
    shots.forEach(function (btn, i) { btn.addEventListener('click', function () { open(i); }); });
    lb.querySelector('.lb-next').addEventListener('click', function () { show(index + 1); });
    lb.querySelector('.lb-prev').addEventListener('click', function () { show(index - 1); });
    Array.prototype.forEach.call(lb.querySelectorAll('[data-lb-close]'), function (el) {
      el.addEventListener('click', close);
    });
    document.addEventListener('keydown', function (e) {
      if (!lb.classList.contains('on')) return;
      if (e.key === 'Escape') close();
      if (e.key === 'ArrowRight') show(index + 1);
      if (e.key === 'ArrowLeft') show(index - 1);
    });
  }

  /* ---------- Lesefortschritt ---------- */
  var article = document.querySelector('[data-readable]');
  if (article) {
    var bar = document.createElement('div');
    bar.className = 'readbar';
    bar.setAttribute('role', 'presentation');
    document.body.appendChild(bar);
    var ticking = false;
    function update() {
      var box = article.getBoundingClientRect();
      var total = box.height - window.innerHeight;
      var done = total > 0 ? Math.min(1, Math.max(0, -box.top / total)) : 0;
      bar.style.width = (done * 100).toFixed(1) + '%';
      ticking = false;
    }
    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; window.requestAnimationFrame(update); }
    }, { passive: true });
    window.addEventListener('resize', update, { passive: true });
    update();
  }
})();
