/*
 * Kommentar-Freigabe (Redaktion). Wird vom Kommentar-Dienst unter
 * /api/kommentare/moderation.js ausgeliefert; die Seite hat kein Inline-Skript.
 * Ablauf: Token eingeben (bleibt nur im sessionStorage), wartende Kommentare
 * laden, alle sind vorausgewaehlt; abgewaehlte werden beim Absenden abgelehnt.
 * Es werden nur die Kommentare geschickt, die gerade angezeigt werden.
 */
(() => {
  'use strict';
  const API = '/api/kommentare/';
  const SCHLUESSEL = 'merzenich-moderation-token';
  const $ = (id) => document.getElementById(id);
  const el = {
    anmeldung: $('anmeldung'), form: $('anmeldung-form'), token: $('token'), meldung: $('meldung'),
    bereich: $('bereich'), anzahl: $('anzahl'), mailHinweis: $('mail-hinweis'), werkzeug: $('werkzeug'),
    liste: $('liste'), leer: $('leer'), zaehler: $('zaehler'), alle: $('alle'), keine: $('keine'),
    absenden: $('absenden'), neuLaden: $('neu-laden'), abmelden: $('abmelden'),
  };

  const leseToken = () => { try { return sessionStorage.getItem(SCHLUESSEL) || ''; } catch (e) { return ''; } };
  const merkeToken = (t) => { try { if (t) sessionStorage.setItem(SCHLUESSEL, t); else sessionStorage.removeItem(SCHLUESSEL); } catch (e) { /* ohne Speicher: Token nur bis zum Neuladen */ } };
  let token = leseToken();

  const zeit = (iso) => { const d = new Date(iso); return Number.isNaN(d.getTime()) ? '' : new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(d) + ' Uhr'; };
  const plural = (n, eins, mehr) => `${n} ${n === 1 ? eins : mehr}`;

  function melde(text, art) {
    el.meldung.textContent = text || '';
    el.meldung.className = 'meldung' + (art ? ' ' + art : '');
  }

  async function api(pfad, body) {
    const r = await fetch(API + pfad, {
      method: body ? 'POST' : 'GET',
      cache: 'no-store',
      headers: { Authorization: 'Bearer ' + token, ...(body ? { 'Content-Type': 'application/json' } : {}) },
      body: body ? JSON.stringify(body) : undefined,
    });
    let d = null; try { d = await r.json(); } catch (e) { /* unten */ }
    if (!d) { const e = new Error(`Der Kommentar-Dienst antwortet nicht (HTTP ${r.status}).`); e.status = r.status; throw e; }
    if (!r.ok || !d.ok) { const e = new Error(d.fehler || `Fehler (HTTP ${r.status}).`); e.status = r.status; throw e; }
    return d;
  }

  function zeigeAnmeldung(hinweis, art) {
    el.bereich.hidden = true; el.anmeldung.hidden = false;
    melde(hinweis || '', art);
    el.token.value = ''; el.token.focus();
  }

  function aktualisiereZaehler() {
    const kaesten = [...el.liste.querySelectorAll('input[type=checkbox]')];
    const an = kaesten.filter((k) => k.checked).length, ab = kaesten.length - an;
    for (const k of kaesten) {
      const li = k.closest('li'); li.classList.toggle('abgewaehlt', !k.checked);
      li.querySelector('.zustand').textContent = k.checked ? '(wird freigegeben)' : '(wird abgelehnt)';
    }
    el.zaehler.textContent = `${an} zur Freigabe ausgewählt, ${ab} ${ab === 1 ? 'wird' : 'werden'} abgelehnt`;
  }

  function eintrag(k, i) {
    const li = document.createElement('li');
    const kopfzeile = document.createElement('label'); kopfzeile.className = 'auswahl';
    const box = document.createElement('input'); box.type = 'checkbox'; box.checked = true; box.value = k.id; box.id = 'k-' + i;
    kopfzeile.htmlFor = box.id;
    const beschriftung = document.createElement('span'); beschriftung.textContent = 'Freigeben ';
    const zustand = document.createElement('span'); zustand.className = 'zustand';
    kopfzeile.append(box, beschriftung, zustand);

    const kopf = document.createElement('p'); kopf.className = 'kopf';
    const name = document.createElement('strong'); name.textContent = k.name;
    const wo = document.createElement('a'); wo.href = k.link || '/'; wo.target = '_blank'; wo.rel = 'noopener noreferrer';
    const info = k.themaInfo || {};
    wo.textContent = info.art === 'diskussion' ? `Diskussion: ${info.titel || 'Thema'}${info.eroeffnung ? ' (neues Thema)' : ''}` : `Artikel ${k.thema}`;
    kopf.append(name, ` · ${zeit(k.erstellt)} · `, wo, ` · ${k.hatEmail ? 'E-Mail hinterlegt, bekommt eine Freigabe-Mail' : 'keine E-Mail'}`);

    const text = document.createElement('p'); text.className = 'text'; text.textContent = k.text;
    kopf.id = `k-${i}-kopf`; text.id = `k-${i}-text`;
    box.setAttribute('aria-describedby', `${kopf.id} ${text.id}`);
    li.append(kopfzeile, kopf, text);
    return li;
  }

  async function laden() {
    melde('Wartende Kommentare werden geladen …');
    el.neuLaden.disabled = true;
    try {
      const d = await api('admin/liste?status=wartend');
      el.anmeldung.hidden = true; el.bereich.hidden = false;
      const liste = d.kommentare.slice().sort((a, b) => String(a.erstellt).localeCompare(String(b.erstellt)));
      el.anzahl.textContent = `(${liste.length})`;
      el.liste.replaceChildren(...liste.map(eintrag));
      el.werkzeug.hidden = liste.length === 0; el.leer.hidden = liste.length !== 0;
      el.mailHinweis.hidden = !!d.mail;
      el.mailHinweis.textContent = d.mail ? '' : 'Hinweis: Der Mailversand (SMTP) ist nicht eingerichtet. Freigaben funktionieren, es werden aber keine Benachrichtigungen verschickt.';
      aktualisiereZaehler();
      melde('');
    } catch (e) {
      if (e.status === 401) { token = ''; merkeToken(''); zeigeAnmeldung(e.message + ' Bitte erneut anmelden.', 'fehler'); }
      else if (e.status === 503 || e.status === 429) { el.bereich.hidden = true; el.anmeldung.hidden = e.status === 503; melde(e.message, 'fehler'); }
      else melde('Laden fehlgeschlagen: ' + e.message, 'fehler');
    } finally { el.neuLaden.disabled = false; }
  }

  async function absenden() {
    const kaesten = [...el.liste.querySelectorAll('input[type=checkbox]')];
    if (!kaesten.length) return;
    const freigeben = kaesten.filter((k) => k.checked).map((k) => k.value);
    const ablehnen = kaesten.filter((k) => !k.checked).map((k) => k.value);
    const frage = `${plural(freigeben.length, 'Kommentar', 'Kommentare')} freigeben und ${plural(ablehnen.length, 'Kommentar', 'Kommentare')} ablehnen?`;
    if (!window.confirm(frage)) return;
    el.absenden.disabled = true; melde('Wird gespeichert …');
    try {
      const d = await api('admin/freigabe', { freigeben, ablehnen });
      const teile = [`${plural(d.freigegeben, 'Kommentar', 'Kommentare')} freigegeben, ${d.abgelehnt} abgelehnt.`];
      if (d.mails.eingereiht) teile.push(`${plural(d.mails.eingereiht, 'Benachrichtigung wird', 'Benachrichtigungen werden')} per E-Mail verschickt.`);
      if (d.mails.nichtKonfiguriert) teile.push(`${plural(d.mails.nichtKonfiguriert, 'Benachrichtigung wurde', 'Benachrichtigungen wurden')} nicht verschickt, weil der Mailversand (SMTP) nicht eingerichtet ist.`);
      if (d.unveraendert) teile.push(`${plural(d.unveraendert, 'Kommentar war', 'Kommentare waren')} bereits bearbeitet und ${d.unveraendert === 1 ? 'blieb' : 'blieben'} unverändert.`);
      if (d.unbekannt && d.unbekannt.length) teile.push(`${plural(d.unbekannt.length, 'Kommentar wurde', 'Kommentare wurden')} nicht gefunden.`);
      await laden();
      melde(teile.join('\n'), 'erfolg');
    } catch (e) {
      if (e.status === 401) { token = ''; merkeToken(''); zeigeAnmeldung(e.message + ' Es wurde nichts gespeichert.', 'fehler'); }
      else melde('Speichern fehlgeschlagen, es wurde nichts geändert: ' + e.message, 'fehler');
    } finally { el.absenden.disabled = false; }
  }

  el.form.addEventListener('submit', (e) => {
    e.preventDefault();
    token = el.token.value.trim(); if (!token) return;
    merkeToken(token); el.token.value = '';
    laden();
  });
  el.liste.addEventListener('change', aktualisiereZaehler);
  el.alle.addEventListener('click', () => { el.liste.querySelectorAll('input[type=checkbox]').forEach((k) => { k.checked = true; }); aktualisiereZaehler(); });
  el.keine.addEventListener('click', () => { el.liste.querySelectorAll('input[type=checkbox]').forEach((k) => { k.checked = false; }); aktualisiereZaehler(); });
  el.absenden.addEventListener('click', absenden);
  el.neuLaden.addEventListener('click', laden);
  el.abmelden.addEventListener('click', () => { token = ''; merkeToken(''); el.liste.replaceChildren(); zeigeAnmeldung('Abgemeldet.'); });

  if (token) laden(); else zeigeAnmeldung();
})();
