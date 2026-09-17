/*
 * Werbefrei lesen (Abo 2,50 Euro im Monat) und globaler Werbeschalter.
 *
 * - html[data-werbung="aus"] (gesetzt beim Bauen der Seiten) blendet alle
 *   Anzeigenflaechen aus. Solange das gilt, ist Werbung fuer alle aus.
 * - Wer werbefrei abonniert hat, traegt im Browser einen signierten Nachweis
 *   (localStorage "merzenich-werbefrei"), den der Dienst unter /api/abo/
 *   ausgestellt hat: nach einer Zahlung (Stripe Checkout, Session-ID auf der
 *   Danke-Seite) oder ueber einen Einloesecode der Redaktion. Ist der
 *   Nachweis gueltig, wird html[data-werbefrei="ja"] gesetzt.
 * - Auf /werbefrei/ zeigt das Skript Zahlungslink, Code-Formular und Status.
 */
(() => {
  'use strict';
  const SPEICHER = 'merzenich-werbefrei';
  const html = document.documentElement;
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  function tokenLesen() { try { return localStorage.getItem(SPEICHER) || ''; } catch (e) { return ''; } }
  function tokenSchreiben(t) { try { t ? localStorage.setItem(SPEICHER, t) : localStorage.removeItem(SPEICHER); } catch (e) { /* egal */ } }
  function nutzlast(t) { try { const p = t.split('.')[0].replace(/-/g, '+').replace(/_/g, '/'); return JSON.parse(atob(p)); } catch (e) { return null; } }
  function lokalGueltig(t) { const n = nutzlast(t); return !!(n && n.bis && Date.parse(n.bis) > Date.now()); }
  const datum = (iso) => { const d = new Date(iso); return Number.isNaN(d.getTime()) ? '' : new Intl.DateTimeFormat('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(d); };

  async function api(pfad, body) {
    const r = await fetch('/api/abo/' + pfad, body ? { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) } : { cache: 'no-store' });
    let d = null; try { d = await r.json(); } catch (e) { /* unten */ }
    if (!d) throw new Error('Der Abo-Dienst antwortet nicht.');
    if (!r.ok || !d.ok) throw new Error(d.fehler || 'Fehler');
    return d;
  }

  function anwenden() {
    const t = tokenLesen();
    html.dataset.werbefrei = t && lokalGueltig(t) ? 'ja' : 'nein';
    // Einmal am Tag beim Dienst gegenpruefen (Signatur, Ablauf).
    if (t && lokalGueltig(t)) {
      let geprueft = ''; try { geprueft = localStorage.getItem(SPEICHER + '-geprueft') || ''; } catch (e) { /* egal */ }
      if (geprueft.slice(0, 10) !== new Date().toISOString().slice(0, 10)) {
        api('status', { token: t }).then((d) => { if (!d.gueltig) { tokenSchreiben(''); html.dataset.werbefrei = 'nein'; } try { localStorage.setItem(SPEICHER + '-geprueft', new Date().toISOString()); } catch (e) { /* egal */ } }).catch(() => { /* offline: lokal weiter */ });
      }
    }
  }

  async function seite() {
    const app = document.getElementById('werbefrei-app'); if (!app) return;
    const params = new URLSearchParams(location.search);
    const session = params.get('session_id');
    const t = tokenLesen();
    let konf = { zahlungslink: '', preis: '2,50 € im Monat', stripe: false, codes: false };
    try { konf = (await api('konfiguration')).konfiguration; } catch (e) { /* Standardwerte */ }

    const status = t && lokalGueltig(t) ? `<div class="werbefrei__status is-aktiv"><strong>Werbefrei ist aktiv</strong> auf diesem Gerät bis ${esc(datum(nutzlast(t).bis))}. <button type="button" class="werbefrei__ab">Auf diesem Gerät beenden</button></div>` : '';
    const zahlung = konf.zahlungslink
      ? `<a class="btn" href="${esc(konf.zahlungslink)}" rel="noopener">Werbefrei abonnieren · ${esc(konf.preis)}</a><p class="werbefrei__klein">Die Zahlung läuft über unseren Zahlungsanbieter; nach der Zahlung kommen Sie automatisch hierher zurück und Werbefrei wird auf diesem Gerät freigeschaltet. Monatlich kündbar.</p>`
      : `<p class="werbefrei__klein"><strong>Der Zahlungslink wird gerade eingerichtet.</strong> Sobald er steht, erscheint hier der Knopf „Werbefrei abonnieren · ${esc(konf.preis)}“. Wer schon jetzt unterstützen möchte, meldet sich bei der <a href="/kontakt/">Redaktion</a> und erhält einen Einlösecode.</p>`;
    app.innerHTML = `${status}
      <section class="werbefrei__abschnitt"><h2>So funktioniert es</h2><ul><li>Für ${esc(konf.preis)} lesen Sie Merzenich Aktuell ohne Anzeigenflächen.</li><li>Kein Konto, kein Login: Nach der Zahlung wird das Gerät freigeschaltet, auf dem Sie gezahlt haben. Weitere Geräte schalten Sie mit Ihrem Einlösecode frei.</li><li>Die Redaktion bleibt dieselbe, die Berichterstattung auch. Das Abo finanziert Lokaljournalismus für Merzenich.</li></ul>${zahlung}</section>
      <section class="werbefrei__abschnitt"><h2>Einlösecode</h2><p class="werbefrei__klein">Sie haben einen Code von der Redaktion oder wollen ein weiteres Gerät freischalten?</p><form class="werbefrei__code"><label>Code<input name="code" maxlength="64" autocomplete="off" required></label><button type="submit" class="btn ghost">Einlösen</button><span class="werbefrei__meldung" role="status"></span></form></section>`;
    const meld = app.querySelector('.werbefrei__meldung');
    app.querySelector('.werbefrei__code').addEventListener('submit', async (e) => {
      e.preventDefault(); meld.textContent = 'Wird geprüft …';
      try { const d = await api('code', { code: e.target.code.value.trim() }); tokenSchreiben(d.token); anwenden(); meld.textContent = 'Freigeschaltet bis ' + datum(nutzlast(d.token).bis) + '.'; setTimeout(() => location.reload(), 900); }
      catch (err) { meld.textContent = err.message; }
    });
    app.querySelector('.werbefrei__ab')?.addEventListener('click', () => { tokenSchreiben(''); anwenden(); location.reload(); });
    if (session) {
      const box = document.createElement('div'); box.className = 'werbefrei__status'; box.textContent = 'Zahlung wird geprüft …'; app.prepend(box);
      try { const d = await api('pruefen?session=' + encodeURIComponent(session)); tokenSchreiben(d.token); anwenden(); box.classList.add('is-aktiv'); box.innerHTML = `<strong>Danke!</strong> Werbefrei ist auf diesem Gerät aktiv bis ${esc(datum(nutzlast(d.token).bis))}.${d.code ? ` Ihr Einlösecode für weitere Geräte: <code>${esc(d.code)}</code>` : ''}`; history.replaceState(null, '', location.pathname); }
      catch (err) { box.classList.add('is-fehler'); box.textContent = 'Die Zahlung konnte nicht bestätigt werden: ' + err.message + ' Bitte melden Sie sich bei der Redaktion.'; }
    }
  }

  anwenden();
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', seite, { once: true }); else seite();
})();
