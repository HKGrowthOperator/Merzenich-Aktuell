/*
 * Kommentare und Diskussion (Frontend).
 * - Auf Artikelseiten (html[data-page="article"]): Kommentarbereich unter dem
 *   Text, Thema = Seitenpfad.
 * - Auf /diskussion/: Themenliste, neues Thema eroeffnen, Beitraege je Thema.
 * Spricht /api/kommentare/ (same-origin). Ohne Dienst bleibt die Seite still
 * und zeigt nur einen Hinweis; nichts bricht.
 */
(() => {
  'use strict';
  const API = '/api/kommentare/';
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const zeit = (iso) => { const d = new Date(iso); return Number.isNaN(d.getTime()) ? '' : new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(d) + ' Uhr'; };
  const absaetze = (t) => esc(t).split(/\n{2,}/).map((p) => '<p>' + p.replace(/\n/g, '<br>') + '</p>').join('');
  const gemerkterName = () => { try { return localStorage.getItem('merzenich-kommentar-name') || ''; } catch (e) { return ''; } };
  const merkeName = (n) => { try { localStorage.setItem('merzenich-kommentar-name', n); } catch (e) { /* egal */ } };

  async function api(pfad, body) {
    const r = await fetch(API + pfad, body ? { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) } : { cache: 'no-store' });
    let d = null; try { d = await r.json(); } catch (e) { /* unten */ }
    if (!d) throw new Error('Der Kommentar-Dienst antwortet nicht.');
    if (!r.ok || !d.ok) { const e = new Error(d.fehler || 'Fehler'); e.feld = d.feld; throw e; }
    return d;
  }

  function kommentarHtml(k) {
    return `<article class="kommentar" data-id="${esc(k.id)}"><div class="kommentar__kopf"><strong>${esc(k.name)}</strong><time datetime="${esc(k.erstellt)}">${esc(zeit(k.erstellt))}</time></div><div class="kommentar__text">${absaetze(k.text)}</div><div class="kommentar__fuss"><button type="button" class="kommentar__melden" data-melden="${esc(k.id)}">Melden</button></div></article>`;
  }

  function formularHtml({ titel = false, knopf = 'Kommentar absenden' } = {}) {
    const name = gemerkterName();
    return `<form class="kommentar-form" novalidate>
      ${titel ? '<label>Titel des Themas<input name="titel" maxlength="120" required placeholder="Worum geht es?"></label>' : ''}
      <div class="kommentar-form__zeile">
        <label>Name<input name="name" maxlength="40" required value="${esc(name)}" autocomplete="nickname"></label>
        <label>E-Mail <small>(optional, wird nicht veröffentlicht)</small><input name="email" type="email" maxlength="120" autocomplete="email"></label>
      </div>
      <label>${titel ? 'Ihr Beitrag' : 'Ihr Kommentar'}<textarea name="text" rows="5" maxlength="2000" required></textarea></label>
      <label class="kommentar-form__hp" aria-hidden="true">Website<input name="hp" tabindex="-1" autocomplete="off"></label>
      <label class="kommentar-form__regeln"><input type="checkbox" name="regeln" required><span>Ich habe die <a href="/kommentarregeln/" target="_blank" rel="noopener">Kommentarrichtlinien</a> gelesen. Mein Name und mein Text werden öffentlich angezeigt.</span></label>
      <div class="kommentar-form__aktion"><button type="submit" class="btn">${esc(knopf)}</button><span class="kommentar-form__status" role="status" aria-live="polite"></span></div>
    </form>`;
  }

  function leseFormular(form) {
    const f = new FormData(form);
    return { titel: f.get('titel'), name: String(f.get('name') || '').trim(), email: String(f.get('email') || '').trim(), text: String(f.get('text') || ''), hp: f.get('hp') || '', regeln: form.querySelector('[name=regeln]').checked };
  }

  function bindeMelden(root) {
    root.addEventListener('click', async (e) => {
      const b = e.target.closest('[data-melden]'); if (!b) return;
      if (!window.confirm('Diesen Kommentar der Redaktion melden?')) return;
      b.disabled = true;
      try { const d = await api('melden', { id: b.dataset.melden }); b.textContent = d.verborgen ? 'Ausgeblendet' : 'Gemeldet'; if (d.verborgen) b.closest('.kommentar')?.remove(); }
      catch (err) { b.disabled = false; window.alert(err.message); }
    });
  }

  function zeigeStatus(form, text, fehler, feld) {
    const s = form.querySelector('.kommentar-form__status'); s.textContent = text; s.classList.toggle('is-fehler', !!fehler);
    form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
    if (feld) form.querySelector(`[name=${feld}]`)?.classList.add('is-invalid');
  }

  // ------------------------------------------------------------ Artikel
  async function artikel() {
    const body = document.querySelector('.article-body'); if (!body) return;
    const thema = location.pathname;
    const sektion = document.createElement('section');
    sektion.id = 'kommentare'; sektion.className = 'kommentare';
    sektion.innerHTML = `<h2>Kommentare <span class="kommentare__anzahl"></span></h2><div class="kommentare__liste"><p class="kommentare__leer">Kommentare werden geladen …</p></div><h3>Mitdiskutieren</h3>${formularHtml()}<p class="kommentare__hinweis">Alle Themen, die nicht zu diesem Artikel passen, gehören in die <a href="/diskussion/">offene Diskussion</a>.</p>`;
    const anker = body.parentElement.querySelector('.cta-row') || body;
    anker.insertAdjacentElement('afterend', sektion);
    const liste = sektion.querySelector('.kommentare__liste'), anzahl = sektion.querySelector('.kommentare__anzahl'), form = sektion.querySelector('form');
    bindeMelden(sektion);
    const render = (ks) => { anzahl.textContent = ks.length ? `(${ks.length})` : ''; liste.innerHTML = ks.length ? ks.map(kommentarHtml).join('') : '<p class="kommentare__leer">Noch keine Kommentare. Schreiben Sie den ersten.</p>'; };
    try { render((await api('liste?thema=' + encodeURIComponent(thema))).kommentare); }
    catch (e) { liste.innerHTML = '<p class="kommentare__leer">Kommentare sind gerade nicht erreichbar. <button type="button" class="btn ghost kommentare__retry">Erneut versuchen</button></p>'; liste.querySelector('.kommentare__retry').addEventListener('click', () => { sektion.remove(); artikel(); }); }
    form.addEventListener('submit', async (e) => {
      e.preventDefault(); const b = leseFormular(form); zeigeStatus(form, 'Wird gesendet …');
      form.querySelector('button[type=submit]').disabled = true;
      try { await api('neu', { ...b, thema }); merkeName(b.name); form.reset(); form.querySelector('[name=name]').value = b.name; zeigeStatus(form, 'Danke. Ihr Kommentar wartet auf Freigabe durch die Redaktion.'); }
      catch (err) { zeigeStatus(form, err.message, true, err.feld); }
      form.querySelector('button[type=submit]').disabled = false;
    });
  }

  // ------------------------------------------------------------ Diskussion
  async function diskussion() {
    const app = document.getElementById('diskussion-app'); if (!app) return;
    app.innerHTML = `<div class="diskussion__neu"><h2>Neues Thema eröffnen</h2><p>Was bewegt Sie in Merzenich? Jedes Thema ist willkommen, solange es den <a href="/kommentarregeln/">Kommentarrichtlinien</a> entspricht.</p>${formularHtml({ titel: true, knopf: 'Thema eröffnen' })}</div><h2>Themen <span class="diskussion__anzahl"></span></h2><div class="diskussion__liste"><p class="kommentare__leer">Themen werden geladen …</p></div>`;
    const liste = app.querySelector('.diskussion__liste'), anzahl = app.querySelector('.diskussion__anzahl'), neuForm = app.querySelector('.diskussion__neu form');
    bindeMelden(app);
    let themen = [];
    const themaHtml = (t) => `<details class="thema" data-id="${esc(t.id)}"${t.status === 'geschlossen' ? ' data-geschlossen' : ''}><summary><span class="thema__titel">${esc(t.titel)}</span><span class="thema__meta">${esc(t.name)} · ${esc(zeit(t.erstellt))} · ${t.anzahl} ${t.anzahl === 1 ? 'Beitrag' : 'Beiträge'}${t.status === 'geschlossen' ? ' · geschlossen' : ''}</span></summary><div class="thema__body"><div class="kommentare__liste"><p class="kommentare__leer">Beiträge werden geladen …</p></div>${t.status === 'geschlossen' ? '<p class="kommentare__leer">Dieses Thema ist geschlossen.</p>' : '<h3>Antworten</h3>' + formularHtml({ knopf: 'Antwort absenden' })}</div></details>`;
    const renderListe = () => { anzahl.textContent = themen.length ? `(${themen.length})` : ''; liste.innerHTML = themen.length ? themen.map(themaHtml).join('') : '<p class="kommentare__leer">Noch kein Thema. Eröffnen Sie das erste.</p>'; };
    async function ladeThema(det) {
      if (det.dataset.geladen) return; det.dataset.geladen = '1';
      const kl = det.querySelector('.kommentare__liste');
      try { const d = await api('liste?thema=' + encodeURIComponent(det.dataset.id)); kl.innerHTML = d.kommentare.length ? d.kommentare.map(kommentarHtml).join('') : '<p class="kommentare__leer">Noch keine Beiträge.</p>'; }
      catch (e) { kl.innerHTML = '<p class="kommentare__leer">Beiträge sind gerade nicht erreichbar.</p>'; }
    }
    liste.addEventListener('toggle', (e) => { const det = e.target.closest('details.thema'); if (det?.open) { ladeThema(det); history.replaceState(null, '', '#thema=' + encodeURIComponent(det.dataset.id)); } }, true);
    liste.addEventListener('submit', async (e) => {
      const form = e.target.closest('form'); if (!form) return; e.preventDefault();
      const det = form.closest('details.thema'), b = leseFormular(form), kl = det.querySelector('.kommentare__liste');
      zeigeStatus(form, 'Wird gesendet …'); form.querySelector('button[type=submit]').disabled = true;
      try { await api('neu', { ...b, thema: det.dataset.id }); merkeName(b.name); form.reset(); form.querySelector('[name=name]').value = b.name; zeigeStatus(form, 'Danke. Ihre Antwort wartet auf Freigabe durch die Redaktion.'); }
      catch (err) { zeigeStatus(form, err.message, true, err.feld); }
      form.querySelector('button[type=submit]').disabled = false;
    });
    neuForm.addEventListener('submit', async (e) => {
      e.preventDefault(); const b = leseFormular(neuForm); zeigeStatus(neuForm, 'Wird gesendet …'); neuForm.querySelector('button[type=submit]').disabled = true;
      try { await api('thema', b); merkeName(b.name); neuForm.reset(); neuForm.querySelector('[name=name]').value = b.name; zeigeStatus(neuForm, 'Danke. Ihr Thema wartet auf Freigabe durch die Redaktion.'); }
      catch (err) { zeigeStatus(neuForm, err.message, true, err.feld); }
      neuForm.querySelector('button[type=submit]').disabled = false;
    });
    try { themen = (await api('themen')).themen; renderListe(); const m = location.hash.match(/#thema=([^&]+)/); if (m) { const det = liste.querySelector(`details.thema[data-id="${CSS.escape(decodeURIComponent(m[1]))}"]`); if (det) { det.open = true; det.scrollIntoView({ block: 'start' }); } } }
    catch (e) { liste.innerHTML = '<p class="kommentare__leer">Die Diskussion ist gerade nicht erreichbar. <button type="button" class="btn ghost kommentare__retry">Erneut versuchen</button></p>'; liste.querySelector('.kommentare__retry').addEventListener('click', () => diskussion()); }
  }

  function start() { if (document.documentElement.dataset.page === 'article') artikel(); diskussion(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true }); else start();
})();
