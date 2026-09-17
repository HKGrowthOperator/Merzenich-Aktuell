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
  var drawer = $('[data-drawer]'), openBtn = $('[data-menu]');
  function setDrawer(open) {
    if (!drawer) return;
    drawer.inert = !open;
    drawer.classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
    Array.prototype.forEach.call(document.body.children, function (el) { if (el !== drawer) el.inert = open; });
    if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) { var f = $('a, button, input', drawer); if (f) f.focus(); } else if (openBtn) openBtn.focus();
  }
  if (openBtn) openBtn.addEventListener('click', function () { setDrawer(true); });
  if (drawer) drawer.addEventListener('click', function (e) { if (e.target.classList.contains('scrim') || e.target.closest('[data-close]')) setDrawer(false); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { if(drawer && drawer.classList.contains('open'))setDrawer(false); closeLb(); }
    else if (e.key === 'Tab') {
      if (drawer && drawer.classList.contains('open')) trapFocus(e, $('.panel', drawer));
      else if (lb && lb.classList.contains('on')) trapFocus(e, lb);
    }
  });

  /* A broken source image collapses; it never becomes a fabricated picture. */
  $$('img[data-editorial-image]').forEach(function(img) {
    function collapse(){var box=img.closest('.media');if(box)box.hidden=true;var row=img.closest('.feed-row');if(row)row.classList.add('no-image');}
    img.addEventListener('error',collapse);
    if(img.complete && !img.naturalWidth)collapse();
  });

  /* ---------- Sticky-Navigation: aktuelle Rubrik in Sicht scrollen ---------- */
  var cur = null; // Preserve anchor and back-navigation scroll positions.
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
  $$('form[action="/api/formular"]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var b = $('button[type=submit]', form); if (b) { b.disabled = true; b.dataset.label = b.textContent; b.textContent = 'Wird gesendet …'; }
    });
    // Der Dienst leitet bei einem Fehler mit ?fehler=<code> hierher zurueck.
    var code = new URLSearchParams(location.search).get('fehler');
    if (code) {
      var texte = { felder: 'Bitte füllen Sie alle Pflichtfelder aus.', email: 'Bitte geben Sie eine gültige E-Mail-Adresse an.', einwilligung: 'Bitte stimmen Sie der Verarbeitung Ihrer Angaben zu.', limit: 'Zu viele Einsendungen in kurzer Zeit. Bitte versuchen Sie es später noch einmal.', datei: 'Als Anhang sind nur Bilder möglich.', gross: 'Ein Bild ist zu groß (höchstens 6 MB).', unbekannt: 'Das Formular konnte nicht gesendet werden. Bitte versuchen Sie es erneut oder schreiben Sie der Redaktion.' };
      var p = document.createElement('p'); p.className = 'form-fehler'; p.setAttribute('role', 'alert'); p.textContent = texte[code] || texte.unbekannt;
      form.prepend(p); p.scrollIntoView({ block: 'center' });
    }
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
    var input = $('input', sf), outEl = $('[data-results]'), idx = null, searchType = $('[data-search-type]'), searchFailed=false;
    function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
    function highlight(s){var q=(input.value||'').trim();if(!q)return esc(s);var text=String(s),at=text.toLocaleLowerCase('de').indexOf(q.toLocaleLowerCase('de'));return at<0?esc(text):esc(text.slice(0,at))+'<mark>'+esc(text.slice(at,at+q.length))+'</mark>'+esc(text.slice(at+q.length));}
    function norm(s) { return s.toLowerCase().replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss'); }
    function run() {
      var q = norm((input.value || '').trim());
      if (!outEl) return;
      if (!q.length) {outEl.innerHTML='<p class="no-result">Suchen Sie nach einem Ort, Verein, Thema oder einer Veranstaltung.</p>';return;} if(q.length<2){outEl.innerHTML='<p class="no-result">Bitte mindestens zwei Zeichen eingeben.</p>';return;} if(searchFailed){outEl.innerHTML='<p class="no-result">Die Suche konnte nicht geladen werden. Bitte laden Sie die Seite erneut.</p>';return;}
      if (!idx) { outEl.innerHTML = '<p class="no-result">Index wird geladen …</p>'; return; }
      var words = q.split(/\s+/);
      var hits = idx.map(function (it) {
        if(searchType && searchType.value && it.typ!==searchType.value)return null;
        var hay = norm(it.t + ' ' + it.d + ' ' + (it.k || '') + ' ' + (it.g || '') + ' ' + (it.b || ''));
        var ok = words.every(function (w) { return hay.indexOf(w) !== -1; });
        if (!ok) return null;
        var score = words.reduce(function (s, w) { return s + (norm(it.t).indexOf(w) !== -1 ? 5 : 0) + (norm(it.g || '').indexOf(w) !== -1 ? 2 : 0) + 1; }, 0);
        return { it: it, score: score };
      }).filter(Boolean).sort(function (a, b) { return b.score - a.score; });
      if (!hits.length) { outEl.innerHTML = '<p class="no-result">Keine Treffer für „' + esc(input.value) + '“. Versuchen Sie ein anderes Stichwort, zum Beispiel Feuerwehr, Ortsfest oder Schützen.</p>'; return; }
      outEl.innerHTML = '<p class="count-line">' + hits.length + ' Treffer</p>' + hits.slice(0, 50).map(function (h) {
        var it = h.it;
        return '<article class="result"><span class="kicker">' + esc(it.typ) + (it.k && it.k !== it.typ ? ' · ' + esc(it.k) : '') + (it.dt ? '<span class="dist">' + esc(it.dt) + '</span>' : '') + '</span><h3><a href="' + esc(it.u) + '">' + highlight(it.t) + '</a></h3><p>' + highlight(it.d) + '</p></article>';
      }).join('');
    }
    fetch('/suche-index.json').then(function (r) { if(!r.ok)throw new Error('search');return r.json(); }).then(function (j) { idx = j; run(); }).catch(function () { searchFailed=true;idx = []; run(); });
    sf.addEventListener('submit', function (e) { e.preventDefault(); run(); });
    input.addEventListener('input', run);
    if(searchType)searchType.addEventListener('change',run);
    input.addEventListener('keydown',function(e){if(e.key==='ArrowDown'){var first=$('[data-results] a');if(first){e.preventDefault();first.focus();}}});
    outEl.addEventListener('keydown',function(e){if(!['ArrowDown','ArrowUp'].includes(e.key))return;var links=$$('a',outEl),i=links.indexOf(document.activeElement);if(i<0)return;e.preventDefault();var next=links[i+(e.key==='ArrowDown'?1:-1)];if(next)next.focus();else if(e.key==='ArrowUp')input.focus();});
    var pre = new URLSearchParams(location.search).get('q');
    if (pre) input.value = pre;
    input.focus();
  }

  /* ---------- Wetter für Merzenich (Open-Meteo, ohne Schlüssel, ohne Cookies) ---------- */
  // Weather is rendered by the WordPress server. No third-party request from readers.

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

// Preview forms prepare a real email draft; they do not pretend to submit data.
document.querySelectorAll('form[data-mail-draft]').forEach(function(form){
  var note=document.createElement('p');note.className='form-note';note.textContent='Vorschau: Öffnet einen E-Mail-Entwurf. Bilder bitte im E-Mail-Programm anhängen.';form.prepend(note);
  form.querySelectorAll('input[type=file]').forEach(function(el){el.disabled=true;});
  var submit=form.querySelector('[type=submit]');if(submit)submit.textContent='E-Mail-Entwurf öffnen';
  form.addEventListener('submit',function(e){e.preventDefault();if(!form.reportValidity())return;var lines=[];
    new FormData(form).forEach(function(value,key){if(!['bot-field','form-name'].includes(key)&&typeof value==='string')lines.push(key+': '+value);});
    window.location.href=form.getAttribute('action')+'?subject='+encodeURIComponent('Merzenich Aktuell · '+form.name)+'&body='+encodeURIComponent(lines.join('\n\n'));
  });
});
