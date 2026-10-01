/* Layout-Editor von Merzenich Aktuell (02.10.2026): Board im Backend
 * (modus "board") und Bearbeitungsmodus direkt auf der Seite (modus "seite").
 * Beide sprechen mit /ma/v1/layout (includes/layout-rest.php). Jede Aktion
 * speichert sofort; die Antwort bringt die neue Belegung und das fertige
 * Markup der Blöcke, die Seite tauscht sie ohne Neuladen aus. */
(function () {
  'use strict';
  var cfg = window.maLayout || {};
  if (!cfg.api || !cfg.seite) return;
  var st = { seite: cfg.seite, rev: 0, plaetze: {}, struktur: [], fest: {}, auswahl: null, undo: null, name: '', vorschau: '', beschaeftigt: false };

  /* ------------------------------------------------------------ Hilfen */
  function el(tag, attrs) {
    var e = document.createElement(tag), i, k;
    if (attrs) for (k in attrs) {
      if (k === 'class') e.className = attrs[k];
      else if (k === 'text') e.textContent = attrs[k];
      else if (k === 'html') e.innerHTML = attrs[k];
      else if (k.indexOf('on') === 0) e.addEventListener(k.slice(2), attrs[k]);
      else if (attrs[k] !== null && attrs[k] !== undefined) e.setAttribute(k, attrs[k]);
    }
    for (i = 2; i < arguments.length; i++) if (arguments[i] !== null && arguments[i] !== undefined) e.append(arguments[i]);
    return e;
  }
  function anfrage(method, pfad, body) {
    var o = { method: method, credentials: 'same-origin', headers: { 'X-WP-Nonce': cfg.nonce, Accept: 'application/json' } };
    if (body && !(body instanceof FormData)) { o.headers['Content-Type'] = 'application/json'; o.body = JSON.stringify(body); }
    else if (body) o.body = body;
    return fetch(pfad, o).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (j) { if (!r.ok) { var e = new Error(j.meldung || ('Fehler ' + r.status)); e.status = r.status; e.daten = j; throw e; } return j; });
    });
  }
  function url(teil, params) {
    var u = cfg.api.replace(/\/?$/, '/') + teil, q = [], k;
    if (params) for (k in params) if (params[k] !== '' && params[k] !== null && params[k] !== undefined) q.push(encodeURIComponent(k) + '=' + encodeURIComponent(params[k]));
    return u + (q.length ? (u.indexOf('?') > -1 ? '&' : '?') + q.join('&') : '');
  }
  var toastEl = null, toastTimer = 0;
  function toast(text, art) {
    if (!toastEl) { toastEl = el('div', { class: 'ma-le__toast', role: 'status', 'aria-live': 'polite' }); document.body.append(toastEl); }
    toastEl.textContent = text; toastEl.className = 'ma-le__toast ist-' + (art || 'ok') + ' ist-offen';
    clearTimeout(toastTimer); toastTimer = setTimeout(function () { toastEl.classList.remove('ist-offen'); }, art === 'fehler' ? 6000 : 2600);
  }
  function uebernehmen(d) {
    if (!d) return;
    if (typeof d.rev === 'number') st.rev = d.rev;
    if (d.plaetze) st.plaetze = d.plaetze;
    if (d.struktur) st.struktur = d.struktur;
    if (d.fest) st.fest = d.fest;
    if (d.auswahl) st.auswahl = d.auswahl;
    if (d.name) st.name = d.name;
    if (d.vorschau) st.vorschau = d.vorschau;
    if (d.rueckgaengig) st.undo = d.rueckgaengig;
  }
  function slotLabel(slot) { return (st.plaetze[slot] && st.plaetze[slot].label) || slot; }
  function alleSlots() {
    var raus = [];
    st.struktur.forEach(function (g) { g.reihen.forEach(function (r) { r.plaetze.forEach(function (s) { raus.push({ slot: s, gruppe: g.titel }); }); }); });
    return raus;
  }

  /* ------------------------------------------------------------ Dialog */
  var dialog = null;
  function schliesseDialog() { if (dialog) { dialog.remove(); dialog = null; } document.removeEventListener('keydown', escDialog); }
  function escDialog(e) { if (e.key === 'Escape') schliesseDialog(); }
  function oeffneDialog(titel, inhalt, breit) {
    schliesseDialog();
    var box = el('div', { class: 'ma-le__dialog' + (breit ? ' ma-le__dialog--breit' : ''), role: 'dialog', 'aria-modal': 'true', 'aria-label': titel },
      el('div', { class: 'ma-le__dialog-kopf' }, el('strong', { text: titel }), el('button', { type: 'button', class: 'ma-le__x', 'aria-label': 'Schließen', text: '✕', onclick: schliesseDialog })),
      inhalt);
    dialog = el('div', { class: 'ma-le__hinter', onclick: function (e) { if (e.target === dialog) schliesseDialog(); } }, box);
    document.body.append(dialog);
    document.addEventListener('keydown', escDialog);
    var f = box.querySelector('input,select,textarea,button:not(.ma-le__x)'); if (f) f.focus();
    return box;
  }

  /* ------------------------------------------------------------ Karte (Vorschau) */
  function karteHtml(k, klein) {
    if (!k) return el('div', { class: 'ma-le__karte ma-le__karte--leer', text: 'Keine Meldung' });
    var w = el('div', { class: 'ma-le__karte' + (klein ? ' ma-le__karte--klein' : '') });
    w.append(k.bild ? el('img', { src: k.bild.src, alt: '', loading: 'lazy' }) : el('span', { class: 'ma-le__kein-bild', text: 'ohne Bild' }));
    var t = el('div', { class: 'ma-le__karte-text' });
    t.append(el('small', { text: (k.kicker || k.ressort.name) + ' · ' + k.ort + (k.sport ? ' · Sport' : '') }));
    t.append(el('strong', { text: k.titel }));
    t.append(el('span', { class: 'ma-le__zeit', text: k.datum + ' · Relevanz ' + k.relevanz }));
    w.append(t);
    return w;
  }

  /* ------------------------------------------------------------ Auswahl einer Meldung */
  function waehleMeldung(opt) {
    var liste = el('div', { class: 'ma-le__liste', 'aria-live': 'polite' });
    var q = el('input', { type: 'search', placeholder: 'Titel suchen …', class: 'ma-le__suche', 'aria-label': 'Suche' });
    var ressort = el('select', { 'aria-label': 'Rubrik' }, el('option', { value: '', text: 'Alle Rubriken' }));
    var ort = el('select', { 'aria-label': 'Ortsteil' }, el('option', { value: '', text: 'Alle Orte' }));
    ((st.auswahl || {}).rubriken || []).forEach(function (r) { ressort.append(el('option', { value: r.slug, text: r.name })); });
    ((st.auswahl || {}).orte || []).forEach(function (o) { ort.append(el('option', { value: o.slug, text: o.name })); });
    if (opt.rubrik) ressort.value = opt.rubrik;
    var timer = 0;
    function laden() {
      liste.textContent = 'Lädt …';
      anfrage('GET', url('layout/' + st.seite + '/kandidaten', { q: q.value, ressort: ressort.value, ort: ort.value })).then(function (d) {
        liste.textContent = '';
        if (!d.kandidaten.length) { liste.append(el('p', { class: 'description', text: 'Keine Meldung gefunden.' })); return; }
        d.kandidaten.forEach(function (k) {
          var b = el('button', { type: 'button', class: 'ma-le__wahl', onclick: function () { schliesseDialog(); opt.onPick(k); } }, karteHtml(k, true));
          if (k.plaetze && k.plaetze.length) b.append(el('span', { class: 'ma-le__chip', text: 'steht fest: ' + k.plaetze.map(slotLabel).join(', ') }));
          liste.append(b);
        });
      }).catch(function (e) { liste.textContent = e.message; });
    }
    q.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(laden, 250); });
    ressort.addEventListener('change', laden); ort.addEventListener('change', laden);
    var kopf = el('div', { class: 'ma-le__filter' }, q, ressort, ort);
    var fuss = opt.neu ? el('p', { class: 'ma-le__fuss' }, el('button', { type: 'button', class: 'button', text: '+ Neue Meldung schreiben', onclick: function () { schliesseDialog(); opt.neu(); } })) : null;
    oeffneDialog(opt.titel || 'Meldung einsetzen', el('div', {}, kopf, liste, fuss), true);
    laden();
  }

  /* ------------------------------------------------------------ Auswahl eines Platzes */
  function waehlePlatz(opt) {
    var liste = el('div', { class: 'ma-le__plaetze' });
    st.struktur.forEach(function (g) {
      var box = el('fieldset', { class: 'ma-le__gruppe' }, el('legend', { text: g.titel }));
      g.reihen.forEach(function (r) {
        r.plaetze.forEach(function (s) {
          if (s === opt.ausser) return;
          var p = st.plaetze[s] || {}, k = p.post;
          box.append(el('button', { type: 'button', class: 'ma-le__platzwahl' + (p.fest ? ' ist-fest' : ''), onclick: function () { schliesseDialog(); opt.onPick(s); } },
            el('strong', { text: p.label || s }), el('span', { text: k ? (p.fest ? 'fest: ' : 'automatisch: ') + k.titel : 'frei (automatisch)' })));
        });
      });
      liste.append(box);
    });
    oeffneDialog(opt.titel || 'Platz wählen', liste, true);
  }

  /* ------------------------------------------------------------ Neue Meldung */
  function neueMeldung(opt) {
    var a = st.auswahl || {}, bildId = 0;
    var f = el('form', { class: 'ma-le__form', novalidate: '' });
    function feld(label, input, hinweis) { return el('label', { class: 'ma-le__feld' }, el('span', { text: label }), input, hinweis ? el('small', { text: hinweis }) : null); }
    var titel = el('input', { type: 'text', name: 'titel', required: '', maxlength: '200' });
    var kicker = el('input', { type: 'text', name: 'kicker', maxlength: '60', placeholder: 'z. B. Feuerwehr, Gemeinderat' });
    var anriss = el('textarea', { name: 'anriss', rows: '2', required: '' });
    var rubrik = el('select', { name: 'rubrik' });
    (a.rubriken || []).forEach(function (r) { rubrik.append(el('option', { value: r.slug, text: r.name })); });
    if (opt.rubrik) rubrik.value = opt.rubrik;
    var ort = el('select', { name: 'ort' });
    (a.orte || []).forEach(function (o) { ort.append(el('option', { value: o.slug, text: o.name })); });
    ort.value = 'merzenich';
    var text = el('textarea', { name: 'text', rows: '7', placeholder: 'Absätze durch Leerzeilen trennen.' });
    var datei = el('input', { type: 'file', accept: 'image/*' });
    var vorschau = el('div', { class: 'ma-le__bildvorschau' });
    var bildStatus = el('small', { text: 'Kein Bild gewählt.' });
    function zeigeBild(src, name) { vorschau.textContent = ''; if (src) vorschau.append(el('img', { src: src, alt: '' })); bildStatus.textContent = name ? 'Bild: ' + name : 'Kein Bild gewählt.'; }
    datei.addEventListener('change', function () {
      var file = datei.files && datei.files[0]; if (!file) return;
      bildStatus.textContent = 'Lädt hoch …'; bildId = 0;
      var fd = new FormData(); fd.append('file', file);
      anfrage('POST', a.medien, fd).then(function (m) { bildId = m.id; zeigeBild(m.source_url, file.name); return anfrage('POST', a.medien + '/' + m.id, { alt_text: titel.value || file.name }); })
        .catch(function (e) { bildStatus.textContent = 'Upload fehlgeschlagen: ' + e.message; });
    });
    var mediathek = (window.wp && window.wp.media) ? el('button', { type: 'button', class: 'button', text: 'Aus der Mediathek', onclick: function () {
      var frame = window.wp.media({ title: 'Bild wählen', multiple: false, library: { type: 'image' } });
      frame.on('select', function () { var m = frame.state().get('selection').first().toJSON(); bildId = m.id; zeigeBild((m.sizes && m.sizes.medium ? m.sizes.medium.url : m.url), m.title || m.filename); });
      frame.open();
    } }) : null;
    var bildtyp = el('select', { name: 'bildtyp' });
    (a.bildtypen || []).forEach(function (t) { bildtyp.append(el('option', { value: t.wert, text: t.name })); });
    var credit = el('input', { type: 'text', name: 'bildcredit', placeholder: 'Foto: Name / Quelle' });
    var rechte = el('input', { type: 'checkbox', name: 'bildrechte' });
    var rechteBox = el('details', { class: 'ma-le__rechte' }, el('summary', { text: 'Erklärung zu Bild- und Nutzungsrechten lesen' }), el('div', { html: a.bildrechte || '' }));
    var quelle = el('input', { type: 'url', name: 'quelle', placeholder: 'https://…' });
    var relevanz = el('select', { name: 'relevanz' });
    for (var i = 10; i >= 1; i--) relevanz.append(el('option', { value: String(i), text: i + ' · ' + ((a.relevanz && a.relevanz[i]) ? a.relevanz[i].name : '') }));
    relevanz.value = '5';
    var status = el('p', { class: 'ma-le__status', role: 'status' });
    var btnVeroeff = el('button', { type: 'submit', class: 'button button-primary', text: opt.slot ? 'Veröffentlichen und einsetzen' : 'Veröffentlichen' });
    var btnEntwurf = el('button', { type: 'button', class: 'button', text: 'Als Entwurf speichern und im Editor öffnen' });
    if (a.darfVeroeffentlichen === false) btnVeroeff.disabled = true;
    f.append(
      feld('Titel *', titel), feld('Kicker', kicker, 'Kurze Dachzeile; leer = Rubrik'), feld('Anriss *', anriss),
      el('div', { class: 'ma-le__zwei' }, feld('Rubrik', rubrik), feld('Ortsteil', ort)),
      feld('Text', text),
      el('fieldset', { class: 'ma-le__bild' }, el('legend', { text: 'Bild' }), el('div', { class: 'ma-le__bildzeile' }, datei, mediathek, bildStatus), vorschau,
        el('div', { class: 'ma-le__zwei' }, feld('Bildtyp', bildtyp), feld('Bildcredit', credit)),
        el('label', { class: 'ma-le__haken' }, rechte, el('span', { text: a.bildrechteHaken || 'Ich bestätige die Bild- und Nutzungsrechte.' })), rechteBox),
      el('div', { class: 'ma-le__zwei' }, feld('Quelle (Link)', quelle), feld('Relevanz', relevanz)),
      opt.slot ? el('p', { class: 'description', text: 'Platz: ' + slotLabel(opt.slot) }) : null,
      status, el('div', { class: 'ma-le__knoepfe' }, btnVeroeff, btnEntwurf));
    function senden(veroeffentlichen) {
      if (!titel.value.trim()) { status.textContent = 'Bitte einen Titel eingeben.'; titel.focus(); return; }
      if (veroeffentlichen && !anriss.value.trim()) { status.textContent = 'Bitte einen Anriss eingeben.'; anriss.focus(); return; }
      if (veroeffentlichen && bildId && !rechte.checked) { status.textContent = 'Bitte die Bild- und Nutzungsrechte bestätigen.'; rechte.focus(); return; }
      if (datei.files && datei.files[0] && !bildId) { status.textContent = 'Das Bild wird noch hochgeladen …'; return; }
      status.textContent = veroeffentlichen ? 'Wird veröffentlicht …' : 'Wird gespeichert …';
      btnVeroeff.disabled = true; btnEntwurf.disabled = true;
      anfrage('POST', url('meldung'), {
        titel: titel.value, kicker: kicker.value, anriss: anriss.value, text: text.value, rubrik: rubrik.value, ort: ort.value,
        bild: bildId, bildtyp: bildtyp.value, bildcredit: credit.value, bildrechte: rechte.checked, quelle: quelle.value, relevanz: relevanz.value,
        status: veroeffentlichen ? 'publish' : 'draft', seite: st.seite, slot: opt.slot || ''
      }).then(function (d) {
        if (!veroeffentlichen || !d.veroeffentlicht) { window.open(d.bearbeiten, '_blank'); status.textContent = d.meldung; btnVeroeff.disabled = false; btnEntwurf.disabled = false; if (!veroeffentlichen) schliesseDialog(); return; }
        schliesseDialog(); toast(d.meldung, d.platziert === false ? 'fehler' : 'ok');
        if (d.layout) uebernehmen(d.layout);
        if (opt.onDone) opt.onDone(d);
      }).catch(function (e) { status.textContent = e.message; btnVeroeff.disabled = false; btnEntwurf.disabled = false; });
    }
    f.addEventListener('submit', function (e) { e.preventDefault(); senden(true); });
    btnEntwurf.addEventListener('click', function () { senden(false); });
    oeffneDialog('Neue Meldung', f, true);
  }

  /* ------------------------------------------------------------ Aktionen */
  var nachAktion = function () {};
  function aktion(body, text) {
    if (st.beschaeftigt) return Promise.resolve();
    st.beschaeftigt = true; document.body.classList.add('ma-le-wartet');
    body.rev = st.rev;
    return anfrage('POST', url('layout/' + st.seite), body).then(function (d) {
      uebernehmen(d); toast(text || d.meldung || 'Gespeichert.'); nachAktion(d);
    }).catch(function (e) {
      if (e.status === 409 && e.daten && e.daten.code === 'ma_layout_konflikt') { toast(e.message, 'fehler'); return neuLaden(); }
      toast(e.message, 'fehler');
    }).then(function () { st.beschaeftigt = false; document.body.classList.remove('ma-le-wartet'); });
  }
  function neuLaden() {
    return anfrage('GET', url('layout/' + st.seite, { html: cfg.modus === 'seite' ? '1' : '' })).then(function (d) { uebernehmen(d); nachAktion(d); });
  }
  function rueckgaengig() {
    if (!st.undo) { toast('Nichts rückgängig zu machen.'); return; }
    aktion({ aktion: 'wiederherstellen', karte: st.undo }, 'Rückgängig gemacht.');
  }
  function zuruecksetzen() {
    if (!confirm('Alle festen Plätze dieser Seite freigeben? Die Automatik belegt dann alles neu.')) return;
    aktion({ aktion: 'wiederherstellen', karte: {} }, 'Alle Plätze wieder automatisch.');
  }
  /* Menü-Aktionen für einen Platz (Board und Seite gleich). */
  function menueFuer(slot, k, p) {
    var raus = [];
    raus.push({ text: 'Ersetzen …', tue: function () { waehleMeldung({ titel: 'Meldung für „' + slotLabel(slot) + '“', rubrik: '', onPick: function (neu) { aktion({ aktion: 'setzen', slot: slot, post: neu.id }); }, neu: function () { neueMeldung({ slot: slot }); } }); } });
    if (k) raus.push({ text: 'Nochmal einsetzen …', tue: function () { waehlePlatz({ titel: '„' + k.titel + '“ zusätzlich auf …', ausser: slot, onPick: function (ziel) { aktion({ aktion: 'doppelt', slot: ziel, post: k.id }); } }); } });
    if (k) raus.push({ text: 'Verschieben nach …', tue: function () { waehlePlatz({ titel: '„' + k.titel + '“ verschieben nach …', ausser: slot, onPick: function (ziel) { var z = st.plaetze[ziel]; aktion({ aktion: 'tauschen', slot: slot, ziel: ziel, post: k.id, zielPost: z && z.post ? z.post.id : 0 }); } }); } });
    if (p && p.fest) raus.push({ text: 'Platz freigeben (automatisch)', tue: function () { aktion({ aktion: 'entfernen', slot: slot }); } });
    if (k && st.seite === 'startseite') raus.push({ text: 'Nur in der Rubrik zeigen', tue: function () { if (confirm('„' + k.titel + '“ von der Startseite nehmen? Sie bleibt in ihrer Rubrik.')) aktion({ aktion: 'nur-rubrik', post: k.id }); } });
    raus.push({ text: 'Neue Meldung hier …', tue: function () { neueMeldung({ slot: slot }); } });
    if (k) raus.push({ text: 'Im Editor bearbeiten ↗', href: k.bearbeiten });
    if (k) raus.push({ text: 'Meldung ansehen ↗', href: k.ansehen });
    return raus;
  }
  function menueElement(eintraege, klasse) {
    var box = el('div', { class: 'ma-le__menu-liste' });
    eintraege.forEach(function (m) {
      box.append(m.href ? el('a', { href: m.href, target: '_blank', rel: 'noopener', text: m.text }) : el('button', { type: 'button', text: m.text, onclick: function (e) { e.preventDefault(); var d = e.target.closest('details'); if (d) d.open = false; m.tue(); } }));
    });
    return el('details', { class: 'ma-le__menu ' + (klasse || '') }, el('summary', { 'aria-label': 'Weitere Aktionen', text: '⋯' }), box);
  }
  function warnChips(p) {
    var w = el('span', { class: 'ma-le__chips' });
    if (p && p.post) w.append(el('span', { class: 'ma-le__chip ' + (p.fest ? 'ist-fest' : 'ist-auto'), text: p.fest ? 'fest' : 'automatisch' }));
    if (p && p.doppelt) w.append(el('span', { class: 'ma-le__chip ist-doppelt', text: '2×' }));
    (p && p.warnungen || []).forEach(function (x) { w.append(el('span', { class: 'ma-le__chip ist-warnung', text: x.text, title: x.text })); });
    return w;
  }

  /* ------------------------------------------------------------ Drag & Drop (gemeinsam) */
  var zieh = null;
  function dragStart(e, slot, postId) {
    zieh = { slot: slot, post: postId };
    e.dataTransfer.effectAllowed = 'move';
    try { e.dataTransfer.setData('text/plain', slot); } catch (x) {}
    e.currentTarget.classList.add('ist-zieh');
  }
  function dragEnde(e) { e.currentTarget.classList.remove('ist-zieh'); document.querySelectorAll('.ist-ziel').forEach(function (x) { x.classList.remove('ist-ziel'); }); }
  function dropAuf(zielSlot) {
    if (!zieh || zieh.slot === zielSlot) return;
    var z = st.plaetze[zielSlot];
    aktion({ aktion: 'tauschen', slot: zieh.slot, ziel: zielSlot, post: zieh.post, zielPost: z && z.post ? z.post.id : 0 }, 'Verschoben.');
    zieh = null;
  }
  function zielHandler(node, slot) {
    node.addEventListener('dragover', function (e) { if (!zieh || zieh.slot === slot) return; e.preventDefault(); e.dataTransfer.dropEffect = 'move'; node.classList.add('ist-ziel'); });
    node.addEventListener('dragleave', function () { node.classList.remove('ist-ziel'); });
    node.addEventListener('drop', function (e) { e.preventDefault(); node.classList.remove('ist-ziel'); dropAuf(slot); });
  }

  /* ============================================================ Board (Backend) */
  function board() {
    var wurzel = document.getElementById('ma-layout-board');
    if (!wurzel) return;
    var kopf = document.getElementById('ma-layout-kopf');
    function render() {
      wurzel.textContent = '';
      if (kopf) {
        kopf.textContent = '';
        kopf.append(el('a', { class: 'button', href: st.vorschau, target: '_blank', rel: 'noopener', text: 'Seite ansehen ↗' }),
          el('button', { type: 'button', class: 'button', text: 'Rückgängig', onclick: rueckgaengig, disabled: st.undo ? null : '' }),
          el('button', { type: 'button', class: 'button', text: 'Alle Plätze automatisch', onclick: zuruecksetzen }),
          el('button', { type: 'button', class: 'button button-primary', text: '+ Neue Meldung', onclick: function () { waehlePlatz({ titel: 'Neue Meldung auf welchen Platz?', onPick: function (s) { neueMeldung({ slot: s }); } }); } }),
          el('span', { class: 'ma-lb__stand', text: 'Stand ' + st.rev }));
      }
      st.struktur.forEach(function (g) {
        var gruppe = el('section', { class: 'ma-lb__gruppe ma-lb__gruppe--' + g.art }, el('h2', { text: g.titel }));
        g.reihen.forEach(function (r) {
          var reihe = el('div', { class: 'ma-lb__reihe ma-lb__reihe--' + r.art });
          r.plaetze.forEach(function (slot) {
            var p = st.plaetze[slot] || { label: slot }, k = p.post;
            var platz = el('div', { class: 'ma-lb__platz' + (k ? '' : ' ist-frei') + (p.fest ? ' ist-fest' : ''), 'data-slot': slot });
            platz.append(el('div', { class: 'ma-lb__platzname' }, el('span', { text: p.label }), warnChips(p)));
            if (k) {
              var karte = karteHtml(k); karte.setAttribute('draggable', 'true'); karte.classList.add('ma-lb__karte');
              karte.addEventListener('dragstart', function (e) { dragStart(e, slot, k.id); });
              karte.addEventListener('dragend', dragEnde);
              platz.append(karte);
            } else platz.append(el('div', { class: 'ma-lb__frei' }, el('span', { text: 'frei – füllt die Automatik' }), el('button', { type: 'button', class: 'button button-small', text: 'Einsetzen …', onclick: function () { menueFuer(slot, null, p)[0].tue(); } })));
            platz.append(menueElement(menueFuer(slot, k, p), 'ma-lb__menu'));
            zielHandler(platz, slot);
            reihe.append(platz);
          });
          if (r.anzeige) reihe.append(el('div', { class: 'ma-lb__platz ma-lb__platz--anzeige' }, el('div', { class: 'ma-lb__platzname' }, el('span', { text: r.anzeige })), el('a', { href: cfg.werbeplaetze || '#', text: 'Werbeplätze verwalten' })));
          gruppe.append(reihe);
        });
        wurzel.append(gruppe);
      });
    }
    nachAktion = render;
    wurzel.textContent = 'Lädt …';
    neuLaden().catch(function (e) { wurzel.textContent = 'Fehler: ' + e.message; });
  }

  /* ============================================================ Bearbeitungsmodus auf der Seite */
  function seite() {
    var an = false, bar = null, geladen = false;
    function bloecke() {
      return st.seite === 'startseite' ? Array.prototype.slice.call(document.querySelectorAll('section.buehne, section.desk[data-sektion]')) : Array.prototype.slice.call(document.querySelectorAll('.feed[data-ma-seite]'));
    }
    function karten() { var r = []; bloecke().forEach(function (b) { r = r.concat(Array.prototype.slice.call(b.querySelectorAll('article[data-post]'))); }); return r; }
    function postVon(a) { return parseInt(a.getAttribute('data-post'), 10) || 0; }
    function dekorieren() {
      karten().forEach(function (a) {
        if (a.querySelector(':scope > .ma-le__leiste')) return;
        var slot = a.getAttribute('data-slot') || '', id = postVon(a), p = slot ? st.plaetze[slot] : null, k = p && p.post && p.post.id === id ? p.post : null;
        if (!k) k = { id: id, titel: (a.querySelector('h1,h2,h3') || {}).textContent || '', bearbeiten: (cfg.editUrl || '').replace('%d', id), ansehen: (a.querySelector('h1 a,h2 a,h3 a') || {}).href || '#' };
        var leiste = el('div', { class: 'ma-le__leiste' });
        if (slot) {
          // Ziehen über Pointer-Ereignisse (Maus und Touch): Karte unter dem Zeiger ist das Ziel.
          var griff = el('span', { class: 'ma-le__griff', title: 'Ziehen und auf einer anderen Karte ablegen', text: '⠿' });
          griff.addEventListener('pointerdown', function (e) {
            if (e.button && e.button !== 0) return;
            e.preventDefault();
            var start = { x: e.clientX, y: e.clientY }, ziel = null, aktiv = false;
            try { griff.setPointerCapture(e.pointerId); } catch (x) {}
            function move(ev) {
              if (!aktiv && Math.hypot(ev.clientX - start.x, ev.clientY - start.y) < 6) return;
              if (!aktiv) { aktiv = true; a.classList.add('ist-zieh'); document.body.classList.add('ma-le-zieht'); }
              var unter = document.elementFromPoint(ev.clientX, ev.clientY), art = unter && unter.closest ? unter.closest('article[data-slot]') : null;
              if (art === a) art = null;
              if (ziel && ziel !== art) ziel.classList.remove('ist-ziel');
              ziel = art; if (ziel) ziel.classList.add('ist-ziel');
            }
            function ende() {
              griff.removeEventListener('pointermove', move); griff.removeEventListener('pointerup', up); griff.removeEventListener('pointercancel', ende);
              a.classList.remove('ist-zieh'); document.body.classList.remove('ma-le-zieht');
              if (ziel) ziel.classList.remove('ist-ziel');
            }
            function up() { var z = ziel; ende(); if (aktiv && z) { zieh = { slot: slot, post: id }; dropAuf(z.getAttribute('data-slot')); } }
            griff.addEventListener('pointermove', move); griff.addEventListener('pointerup', up); griff.addEventListener('pointercancel', ende);
          });
          leiste.append(griff, el('span', { class: 'ma-le__slotname', text: slotLabel(slot) }), warnChips(p));
          leiste.append(el('button', { type: 'button', text: 'Ersetzen', onclick: function () { menueFuer(slot, k, p)[0].tue(); } }));
          leiste.append(el('button', { type: 'button', text: 'Nochmal', title: 'Nochmal einsetzen', onclick: function () { menueFuer(slot, k, p)[1].tue(); } }));
          if (p && p.fest) leiste.append(el('button', { type: 'button', text: 'Freigeben', title: 'Platz wieder automatisch belegen', onclick: function () { aktion({ aktion: 'entfernen', slot: slot }); } }));
        }
        leiste.append(el('button', { type: 'button', text: 'Text', title: 'Titel und Anriss direkt ändern', onclick: function () { textBearbeiten(a, id); } }));
        leiste.append(menueElement(slot ? menueFuer(slot, k, p) : [{ text: 'Im Editor bearbeiten ↗', href: k.bearbeiten }], 'ma-le__menu--seite'));
        a.prepend(leiste);
      });
    }
    function entdekorieren() { document.querySelectorAll('.ma-le__leiste').forEach(function (l) { l.remove(); }); }
    function ersetzeBloecke(html) {
      if (!html) return;
      Object.keys(html).forEach(function (name) {
        if (st.seite !== 'startseite') {
          var feed = document.querySelector('.feed[data-ma-seite]'); if (!feed) return;
          Array.prototype.slice.call(feed.children).forEach(function (c) { if (c.matches('article, .bildraster, .no-result')) c.remove(); });
          feed.insertAdjacentHTML('afterbegin', html[name]);
          return;
        }
        var tw = document.createTreeWalker(document.body, NodeFilter.SHOW_COMMENT), n, start = null, ende = null;
        while ((n = tw.nextNode())) { var t = n.nodeValue.trim(); if (t === 'start:' + name + ':start') start = n; else if (t === 'start:' + name + ':end') { ende = n; break; } }
        if (!start || !ende) return;
        var range = document.createRange(); range.setStartBefore(start); range.setEndAfter(ende);
        var frag = range.createContextualFragment(html[name]);
        if (name === 'oben') { var alt = document.querySelector('aside[data-werbung="buehne"]'), neu = frag.querySelector('aside[data-werbung="buehne"]'); if (alt && neu) neu.replaceWith(alt); }
        range.deleteContents(); range.insertNode(frag);
      });
      entdekorieren(); dekorieren();
    }
    function textBearbeiten(a, id) {
      if (a.classList.contains('ist-text')) return;
      a.classList.add('ist-text');
      var titel = a.querySelector('h1 a, h2 a, h3 a'), anriss = a.querySelector('.front-lead-copy > p:not(.marke), .dek'), kicker = a.matches('.feed-lead, .feed-row, .bildraster-karte') ? a.querySelector('.marke-rubrik') : null;
      var felder = [titel, anriss, kicker].filter(Boolean), alt = felder.map(function (f) { return f.textContent; });
      felder.forEach(function (f) { f.setAttribute('contenteditable', 'true'); f.classList.add('ma-le__edit'); });
      if (titel) titel.focus();
      var box = el('div', { class: 'ma-le__textknoepfe' },
        el('button', { type: 'button', class: 'ist-primaer', text: 'Speichern', onclick: speichern }),
        el('button', { type: 'button', text: 'Abbrechen', onclick: abbrechen }),
        el('small', { text: 'Titel, Anriss' + (kicker ? ', Kicker' : '') + ' direkt im Text ändern. Enter speichert, Escape bricht ab.' }));
      a.append(box);
      function ende() { felder.forEach(function (f) { f.removeAttribute('contenteditable'); f.classList.remove('ma-le__edit'); }); box.remove(); a.classList.remove('ist-text'); }
      function abbrechen() { felder.forEach(function (f, i) { f.textContent = alt[i]; }); ende(); }
      function speichern() {
        var body = { seite: st.seite };
        if (titel) body.titel = titel.textContent.trim();
        if (anriss) body.anriss = anriss.textContent.trim();
        if (kicker) body.kicker = kicker.textContent.trim();
        document.body.classList.add('ma-le-wartet');
        anfrage('PATCH', url('meldung/' + id), body).then(function (d) { toast(d.meldung); ende(); if (d.layout) uebernehmen(d.layout); ersetzeBloecke(d.html); })
          .catch(function (e) { toast(e.message, 'fehler'); }).then(function () { document.body.classList.remove('ma-le-wartet'); });
      }
      felder.forEach(function (f) { f.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); speichern(); } if (e.key === 'Escape') { e.preventDefault(); abbrechen(); } }); });
    }
    function linksSperren(e) {
      if (!an) return;
      var a = e.target.closest('a');
      if (!a || !a.closest('article[data-post]') || a.closest('.ma-le__leiste, .ma-le__textknoepfe, .ma-le__menu')) return;
      e.preventDefault();
    }
    function einschalten() {
      an = true; document.body.classList.add('ma-bearbeiten');
      if (!bar) {
        bar = el('div', { class: 'ma-le__bar', role: 'region', 'aria-label': 'Bearbeitungsmodus' },
          el('strong', { text: 'Seite bearbeiten' }), el('span', { class: 'ma-le__bar-name', text: cfg.name || '' }),
          el('span', { class: 'ma-le__bar-hinweis', text: 'Karten mit ⠿ ziehen und auf eine andere Karte legen. Jede Änderung wird sofort gespeichert; Leser sehen sie nach spätestens einer Stunde.' }),
          el('button', { type: 'button', text: '+ Neue Meldung', onclick: function () { waehlePlatz({ titel: 'Neue Meldung auf welchen Platz?', onPick: function (s) { neueMeldung({ slot: s, onDone: function (d) { ersetzeBloecke(d.html); } }); } }); } }),
          el('button', { type: 'button', text: 'Einsetzen …', onclick: function () { waehlePlatz({ titel: 'Welchen Platz belegen?', onPick: function (s) { menueFuer(s, null, st.plaetze[s])[0].tue(); } }); } }),
          el('button', { type: 'button', text: 'Rückgängig', onclick: rueckgaengig }),
          el('a', { href: cfg.board || '#', text: 'Im Backend anordnen' }),
          el('button', { type: 'button', class: 'ist-primaer', text: 'Fertig', onclick: ausschalten }));
        document.body.append(bar);
      }
      bar.hidden = false;
      document.addEventListener('click', linksSperren, true);
      if (!geladen) { neuLaden().then(function () { geladen = true; }).catch(function (e) { toast(e.message, 'fehler'); }); } else dekorieren();
    }
    function ausschalten() {
      an = false; document.body.classList.remove('ma-bearbeiten'); if (bar) bar.hidden = true;
      document.removeEventListener('click', linksSperren, true); entdekorieren(); schliesseDialog();
    }
    nachAktion = function (d) { if (d && d.html) ersetzeBloecke(d.html); else { entdekorieren(); dekorieren(); } };
    document.addEventListener('click', function (e) {
      var t = e.target.closest('#wp-admin-bar-ma-layout > a, #wp-admin-bar-ma-layout-an > a, .ma-layout-toggle');
      if (!t) return;
      e.preventDefault(); an ? ausschalten() : einschalten();
    });
    if (/[?&]bearbeiten=1/.test(location.search)) einschalten();
  }

  if (cfg.modus === 'board') { if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', board); else board(); }
  else seite();
})();
