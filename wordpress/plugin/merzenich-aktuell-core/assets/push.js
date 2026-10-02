/* Merzenich Aktuell: Benachrichtigungen aufs Handy (Web Push), ohne Fremddienst.
 * Läuft nur, wenn „Live-Meldungen“ erlaubt sind (einwilligung.js, Speicher
 * merzenich-einwilligung) und der Browser Benachrichtigungen zugelassen hat.
 * Dann: Service Worker /sw.js registrieren, Push-Abo beim Browser holen und
 * dem eigenen Server melden (REST ma/v1/push). Widerruf: Abo beenden und
 * löschen lassen. Gemerkt wird nur der Endpoint (localStorage merzenich-push),
 * damit nicht bei jedem Aufruf gesendet wird. */
(function () {
  'use strict';
  var cfg = window.maPush;
  if (!cfg || !cfg.key || !('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) return;
  var SPEICHER = 'merzenich-push';
  function stand() { try { return JSON.parse(localStorage.getItem('merzenich-einwilligung') || 'null'); } catch (e) { return null; } }
  function gemerkt() { try { return localStorage.getItem(SPEICHER) || ''; } catch (e) { return ''; } }
  function merken(v) { try { if (v) localStorage.setItem(SPEICHER, v); else localStorage.removeItem(SPEICHER); } catch (e) { /* egal */ } }
  function schluessel(b64) {
    var s = (b64 + '='.repeat((4 - b64.length % 4) % 4)).replace(/-/g, '+').replace(/_/g, '/');
    var roh = atob(s); var a = new Uint8Array(roh.length);
    for (var i = 0; i < roh.length; i++) a[i] = roh.charCodeAt(i);
    return a;
  }
  function senden(methode, daten) {
    return fetch(cfg.api, { method: methode, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(daten), credentials: 'omit' });
  }
  var laeuft = false;
  function abgleichen() {
    if (laeuft) return; laeuft = true;
    var s = stand();
    var erlaubt = !!(s && s.liveMeldungen) && Notification.permission === 'granted';
    navigator.serviceWorker.register(cfg.sw || '/sw.js').then(function (reg) {
      return reg.pushManager.getSubscription().then(function (abo) {
        if (erlaubt) {
          if (abo) return abo;
          return reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: schluessel(cfg.key) });
        }
        if (abo) { var ep = abo.endpoint; return abo.unsubscribe().then(function () { merken(''); return senden('DELETE', { endpoint: ep }); }).then(function () { return null; }); }
        merken(''); return null;
      });
    }).then(function (abo) {
      if (!abo) return;
      var j = abo.toJSON();
      if (gemerkt() === j.endpoint) return;
      return senden('POST', { endpoint: j.endpoint, keys: j.keys }).then(function (r) { if (r.ok) merken(j.endpoint); });
    }).catch(function () { /* kein Push: Polling in einwilligung.js bleibt */ }).then(function () { laeuft = false; });
  }
  document.addEventListener('ma:einwilligung', abgleichen);
  if (document.readyState === 'complete') abgleichen(); else window.addEventListener('load', abgleichen);
})();
