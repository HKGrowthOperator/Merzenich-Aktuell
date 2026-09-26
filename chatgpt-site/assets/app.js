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
        // Meldungen tragen die Ortsmarke (A3.1) wie auf allen Listen, andere Typen die Dachzeile.
        var kopf = it.typ === 'Meldung'
          ? '<p class="marke"><span class="marke-ort">Merzenich</span>' + (it.o ? '<span class="marke-teil"> · ' + esc(it.o) + '</span>' : '') + (it.k ? '<span class="marke-rubrik">' + esc(it.k) + '</span>' : '') + (it.dt ? '<span class="marke-rubrik">' + esc(it.dt) + '</span>' : '') + '</p>'
          : '<span class="kicker">' + esc(it.typ) + (it.k && it.k !== it.typ ? ' · ' + esc(it.k) : '') + (it.dt ? '<span class="dist">' + esc(it.dt) + '</span>' : '') + '</span>';
        return '<article class="result">' + kopf + '<h3><a href="' + esc(it.u) + '">' + highlight(it.t) + '</a></h3><p>' + highlight(it.d) + '</p></article>';
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


/* KBS / Ordin 23.09.2026 – globale Portal-UX */
(function(){
  'use strict';

  /* Foto des Tages: tägliche, deterministische Rotation aus bereits
     dokumentierten lokalen Projektbildern – mit sichtbarem Credit. */
  var foto=document.getElementById('foto-des-tages-bild');
  var credit=document.getElementById('foto-des-tages-credit');
  if(foto&&credit){
    var pool=[
      ['/assets/places/merzenich-1440.webp','Historisches Fachwerkhaus im Ortskern von Merzenich','Karl-Heinz Meurer / Wikimedia Commons'],
      ['/assets/places/golzheim-1440.webp','Blick über den Wenauer Hof auf St. Gregorius in Golzheim','Karl-Heinz Meurer / Wikimedia Commons'],
      ['/assets/places/girbelsrath-1440.webp','Denkmalgeschütztes Fachwerkhaus an der Hauptstraße in Girbelsrath','Käthe und Bernd Limburg / Wikimedia Commons'],
      ['/assets/places/morschenich-1440.webp','Archivaufnahme vom Aufbau des neuen Morschenich im Februar 2015','Papa1234 / Wikimedia Commons'],
      ['/assets/places/buergewald-1440.webp','Luftbild von Bürgewald, dem früheren Morschenich-Alt','Antisyntagmatarchos / Wikimedia Commons']
    ];
    var now=new Date(), start=new Date(now.getFullYear(),0,0);
    var day=Math.floor((now-start)/86400000);
    var pick=pool[day%pool.length];
    foto.src=pick[0]; foto.alt=pick[1]; credit.textContent='Foto: '+pick[2];
    /* Ansichten aus der Gemeinde (Anhang A7): Ort und Beschreibung zum Bild. */
    var orte={merzenich:'Merzenich',golzheim:'Golzheim',girbelsrath:'Girbelsrath',morschenich:'Morschenich',buergewald:'Bürgewald'};
    var slug=(pick[0].match(/places\/([a-z]+)-/)||[])[1];
    var ortEl=document.querySelector('[data-ansicht-ort]'), textEl=document.querySelector('[data-ansicht-text]');
    if(ortEl&&orte[slug])ortEl.textContent=orte[slug];
    if(textEl)textEl.textContent=pick[1]+'.';
  }

  /* Geführter Einstieg für Anzeigen und Immobilien. */
  function icon(type){
    if(type==='haus')return '<svg viewBox="0 0 64 48" aria-hidden="true"><path d="M8 25 32 7l24 18v18H39V31H25v12H8Z" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/></svg>';
    if(type==='kerze')return '<svg viewBox="0 0 64 48" aria-hidden="true"><path d="M28 18h8v25h-8zM32 5c6 6 6 10 0 14-6-4-6-8 0-14Z" fill="none" stroke="currentColor" stroke-width="2.4"/></svg>';
    if(type==='megafon')return '<svg viewBox="0 0 64 48" aria-hidden="true"><path d="M8 22h12l28-12v28L20 28H8zM20 28l6 14h8l-5-11" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/></svg>';
    if(type==='familie')return '<svg viewBox="0 0 64 48" aria-hidden="true"><circle cx="24" cy="16" r="6" fill="none" stroke="currentColor" stroke-width="2.4"/><circle cx="42" cy="18" r="5" fill="none" stroke="currentColor" stroke-width="2.4"/><path d="M10 42c1-11 7-16 14-16s13 5 14 16M34 42c1-8 5-12 10-12 6 0 10 4 11 12" fill="none" stroke="currentColor" stroke-width="2.4"/></svg>';
    return '<svg viewBox="0 0 64 48" aria-hidden="true"><rect x="8" y="10" width="48" height="30" rx="2" fill="none" stroke="currentColor" stroke-width="2.4"/><path d="M14 18h36M14 25h24M14 32h18" stroke="currentColor" stroke-width="2.4"/></svg>';
  }
  function guided(title,intro,cards){
    return '<section class="publish-guide"><div class="publish-guide__head"><span class="eyebrow">Einfach veröffentlichen</span><h2>'+title+'</h2><p>'+intro+'</p></div><div class="publish-guide__grid">'+cards.map(function(c){return '<a class="publish-card" href="'+c.href+'"><span class="publish-card__visual">'+icon(c.icon)+'</span><span class="publish-card__copy"><strong>'+c.title+'</strong><small>'+c.text+'</small></span></a>'}).join('')+'</div></section>';
  }
  var path=location.pathname.replace(/\/+$/,'/') ;
  if(path==='/immobilien/'){
    var host=document.querySelector('main .content-grid, main .shell .content-grid, main section .shell');
    if(host&&!document.querySelector('.publish-guide')){
      var wrap=document.createElement('div');
      wrap.innerHTML=guided('Immobilienanzeige aufgeben','Wählen Sie zuerst, was Sie anbieten möchten. Danach führt Sie die Redaktion durch die benötigten Angaben.',[
        {icon:'haus',title:'Immobilie verkaufen',text:'Haus, Wohnung, Grundstück oder Gewerbeobjekt.',href:'/anzeigen/aufgeben/?art=Immobilie&angebot=Verkauf'},
        {icon:'haus',title:'Immobilie vermieten',text:'Wohnung, Haus oder Gewerbefläche zur Miete.',href:'/anzeigen/aufgeben/?art=Immobilie&angebot=Vermietung'},
        {icon:'megafon',title:'Makler & Partner',text:'Mehrere Objekte oder regelmäßige Veröffentlichung.',href:'/werben/?thema=immobilien'}
      ]);
      host.insertBefore(wrap.firstElementChild,host.firstChild);
    }
  }
  if(path==='/anzeigen/'){
    var main=document.querySelector('main .shell')||document.querySelector('main');
    if(main&&!document.querySelector('.publish-guide')){
      var wrap2=document.createElement('div');
      wrap2.innerHTML=guided('Was möchten Sie veröffentlichen?','Ein Bereich, ein klarer Weg. Wählen Sie die passende Anzeigenart.',[
        {icon:'haus',title:'Immobilien',text:'Verkauf, Vermietung und gewerbliche Objekte.',href:'/immobilien/'},
        {icon:'kerze',title:'Traueranzeige',text:'Trauerfall würdevoll veröffentlichen.',href:'/traueranzeigen/'},
        {icon:'familie',title:'Familienanzeige',text:'Geburt, Hochzeit, Jubiläum und weitere Anlässe.',href:'/familienanzeigen/'},
        {icon:'megafon',title:'Werbung',text:'Banner, Sponsoring, Tipp und Unternehmenspräsenz.',href:'/werben/'}
      ]);
      main.insertBefore(wrap2.firstElementChild,main.firstChild);
    }
  }

  if(path==='/traueranzeigen/'){
    var trauer=document.querySelector('main .shell')||document.querySelector('main');
    if(trauer&&!document.querySelector('.publish-guide')){
      var w3=document.createElement('div');
      w3.innerHTML=guided('Traueranzeige aufgeben','Wählen Sie zuerst die passende Form. Die Redaktion prüft sensible Angaben vor der Veröffentlichung.',[
        {icon:'kerze',title:'Traueranzeige',text:'Einen Trauerfall würdevoll veröffentlichen.',href:'/anzeigen/aufgeben/?art=Traueranzeige&trauerform=Traueranzeige'},
        {icon:'kerze',title:'Danksagung',text:'Für Anteilnahme und Unterstützung danken.',href:'/anzeigen/aufgeben/?art=Traueranzeige&trauerform=Danksagung'},
        {icon:'kerze',title:'Jahrgedächtnis',text:'Erinnerung an einen verstorbenen Menschen.',href:'/anzeigen/aufgeben/?art=Traueranzeige&trauerform=Jahrged%C3%A4chtnis'}
      ]);
      trauer.insertBefore(w3.firstElementChild,trauer.firstChild);
    }
  }

  if(path==='/familienanzeigen/'){
    var familie=document.querySelector('main .shell')||document.querySelector('main');
    if(familie&&!document.querySelector('.publish-guide')){
      var w4=document.createElement('div');
      w4.innerHTML=guided('Familienanzeige aufgeben','Anlass wählen und anschließend die Angaben an die Redaktion senden.',[
        {icon:'familie',title:'Geburt',text:'Willkommen heißen und Freude teilen.',href:'/anzeigen/aufgeben/?art=Familienanzeige&anlass=Geburt'},
        {icon:'familie',title:'Hochzeit',text:'Hochzeit oder Verlobung veröffentlichen.',href:'/anzeigen/aufgeben/?art=Familienanzeige&anlass=Hochzeit'},
        {icon:'familie',title:'Jubiläum',text:'Geburtstag, Hochzeitstag oder Jubiläum.',href:'/anzeigen/aufgeben/?art=Familienanzeige&anlass=Jubil%C3%A4um'}
      ]);
      familie.insertBefore(w4.firstElementChild,familie.firstChild);
    }
  }

  /* Anzeige aufgeben: Art aus dem Link vorwählen, nur den passenden Teil zeigen. */
  if(path==='/anzeigen/aufgeben/'){
    var af=document.querySelector('.anz-form');
    if(af){
      var q=new URLSearchParams(location.search);
      var teile=af.querySelectorAll('.anz-teil');
      var zeig=function(){
        var gew=af.querySelector('input[name="art"]:checked');
        teile.forEach(function(t){var an=!gew||t.getAttribute('data-art')===gew.value;t.hidden=!an;t.disabled=!an;});
      };
      if(q.get('art')){var r=af.querySelector('input[name="art"][value="'+q.get('art').replace(/"/g,'')+'"]');if(r)r.checked=true;}
      ['angebot','trauerform','anlass','format'].forEach(function(k){var v=q.get(k),sel=af.querySelector('select[name="'+k+'"]');if(v&&sel)Array.prototype.forEach.call(sel.options,function(o){if(o.value===v)sel.value=v;});});
      af.addEventListener('change',function(e){if(e.target&&e.target.name==='art')zeig();});
      zeig();
    }
  }

  if(path==='/werben/'){
    var werben=document.querySelector('main .shell')||document.querySelector('main');
    if(werben&&!document.querySelector('.publish-guide')){
      var w5=document.createElement('div');
      w5.innerHTML=guided('Wie möchten Sie sichtbar werden?','Wählen Sie die passende Werbeform. Jede Schaltung wird vor Veröffentlichung redaktionell geprüft.',[
        {icon:'megafon',title:'Werbebanner',text:'Klassische Werbeflächen zwischen redaktionellen Bereichen.',href:'/anzeigen/aufgeben/?art=Werbung&format=Werbebanner'},
        {icon:'megafon',title:'Tipp / Sponsoring',text:'Eigene Tipp-Rubrik für Vereine, Unternehmen und Sponsoren.',href:'/anzeigen/aufgeben/?art=Werbung&format=Tipp%20(bezahlter%20Beitrag)'},
        {icon:'haus',title:'Unternehmensprofil',text:'Dauerhafte lokale Präsenz im Wirtschaftsbereich.',href:'/anzeigen/aufgeben/?art=Werbung&format=Unternehmenspr%C3%A4senz'}
      ]);
      werben.insertBefore(w5.firstElementChild,werben.firstChild);
    }
  }
})();
