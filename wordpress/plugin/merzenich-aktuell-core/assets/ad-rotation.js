/*
 * Rotation der Anzeigen auf den Werbeplätzen einer Seite (Merzenich Aktuell Core).
 * Alle Anzeigen stehen im HTML; sichtbar ist je Platz eine. Ohne JavaScript bleibt
 * die erste sichtbar. Bei Hover/Fokus pausiert ein Platz, damit ein Klick die
 * gemeinte Anzeige trifft.
 *
 * Abwechslung (Vorgabe Betreiber 02.11.2026: „nicht 2 mal hintereinander die
 * gleiche Firma“): Jede Karte trägt data-ma-firma. Ein gemeinsamer Koordinator
 * wählt für alle Plätze der Seite:
 * - Start: eine Firma, die auf den zuletzt eingerichteten Plätzen noch nicht steht,
 *   so zeigen benachbarte Plätze verschiedene Firmen.
 * - Wechsel: die nächste Anzeige einer anderen Firma, die gerade auf keinem anderen
 *   Platz im Bild steht; notfalls nur eine andere Firma als bisher.
 * - Takt: die Plätze wechseln versetzt, nicht alle zugleich.
 * Die Auswahl steckt in reinen Funktionen (window.maAdWahl), damit sie testbar ist.
 *
 * Video-Schleifen (28.10.2026): Ein <video data-src> lädt erst, wenn seine Anzeige
 * sichtbar und der Platz im Bild ist, und pausiert sonst; eingeblendet wird es erst,
 * wenn es läuft (bis dahin steht das Bild darunter). Kein Video bei „Bewegung
 * reduzieren“, Datensparmodus oder schmalem Bildschirm (bis 560 px).
 * window.maAdPlatz(platz) richtet nachgeladene Plätze ein (endlos.js).
 */
(function () {
  'use strict';
  var bewegung = !(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
  var sparen = !!(navigator.connection && navigator.connection.saveData);
  var videoErlaubt = function () { return bewegung && !sparen && window.innerWidth > 560; };

  var wahl = {
    // Startanzeige: ab einer zufälligen Stelle die erste, deren Firma nicht belegt ist.
    start: function (firmen, belegt, zufall) {
      var n = firmen.length;
      if (!n) return 0;
      var o = Math.min(n - 1, Math.floor((zufall || 0) * n));
      for (var s = 0; s < n; s++) {
        var i = (o + s) % n;
        if (belegt.indexOf(firmen[i]) < 0) return i;
      }
      return o;
    },
    // Nächste Anzeige: andere Firma als jetzt und nicht belegt; sonst nur andere Firma.
    naechste: function (firmen, aktiv, belegt) {
      var n = firmen.length;
      if (n < 2) return aktiv;
      var jetzt = firmen[aktiv], s, i;
      for (s = 1; s < n; s++) {
        i = (aktiv + s) % n;
        if (firmen[i] !== jetzt && belegt.indexOf(firmen[i]) < 0) return i;
      }
      for (s = 1; s < n; s++) {
        i = (aktiv + s) % n;
        if (firmen[i] !== jetzt) return i;
      }
      return (aktiv + 1) % n;
    }
  };
  window.maAdWahl = wahl;

  var plaetze = [];

  function videos(platz, aktivesItem, imBild) {
    Array.prototype.forEach.call(platz.querySelectorAll('video.ma-ad-video'), function (v) {
      var spielen = imBild && videoErlaubt() && v.closest('.ma-ad__item') === aktivesItem;
      if (spielen) {
        if (!v.getAttribute('src')) {
          // Breite Fassung im Band ab 901 px, sonst 16:9; WebM (VP9), wo der Browser es kann, sonst MP4.
          var breit = window.innerWidth > 900 && v.getAttribute('data-src-breit');
          var webm = breit ? v.getAttribute('data-src-breit-webm') : v.getAttribute('data-src-webm');
          var mp4 = breit ? v.getAttribute('data-src-breit') : v.getAttribute('data-src');
          var quelle = webm && v.canPlayType('video/webm; codecs="vp9"') ? webm : mp4;
          if (!quelle) return;
          v.addEventListener('playing', function () { v.classList.add('ma-ad-video--an'); });
          v.setAttribute('src', quelle);
        }
        var p = v.play();
        if (p && p.catch) p.catch(function () { v.classList.remove('ma-ad-video--an'); });
      } else if (!v.paused) {
        v.pause();
      }
    });
  }

  // Firmen, die andere Plätze gerade zeigen (nur im Bild, wenn imBild gesetzt).
  function belegt(ohne, nurImBild) {
    var raus = [];
    plaetze.forEach(function (p) {
      if (p === ohne || (nurImBild && !p.imBild)) return;
      raus.push(p.firmen[p.aktiv]);
    });
    return raus;
  }

  function einrichten(el) {
    if (el.getAttribute('data-ma-ad-bereit')) return;
    var items = el.querySelectorAll('.ma-ad__item');
    if (!items.length) return;
    el.setAttribute('data-ma-ad-bereit', '1');
    var firmen = Array.prototype.map.call(items, function (it, n) { return it.getAttribute('data-ma-firma') || ('#' + n); });
    var p = { el: el, items: items, firmen: firmen, aktiv: 0, imBild: false };

    function zeige(i) {
      p.aktiv = i;
      Array.prototype.forEach.call(items, function (it, n) { it.hidden = n !== i; });
      el.setAttribute('data-ma-ad-current', String(i));
      videos(el, items[i], p.imBild);
    }
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (eintraege) {
        p.imBild = eintraege[eintraege.length - 1].isIntersecting;
        videos(el, items[p.aktiv], p.imBild);
      }, { rootMargin: '120px 0px' }).observe(el);
    } else {
      p.imBild = true;
    }
    var rotiert = items.length > 1 && el.hasAttribute('data-ma-ad-rotate');
    if (rotiert) {
      // Benachbarte Plätze: die zuletzt eingerichteten (so viele, wie dieser Platz Firmen kennt) sind tabu.
      var verschieden = firmen.filter(function (f, i) { return firmen.indexOf(f) === i; }).length;
      var nachbarn = plaetze.slice(-Math.max(0, verschieden - 1)).map(function (q) { return q.firmen[q.aktiv]; });
      p.aktiv = wahl.start(firmen, nachbarn, Math.random());
    }
    plaetze.push(p);
    zeige(p.aktiv);
    if (!rotiert) return;

    var takt = Math.max(3000, parseInt(el.getAttribute('data-ma-ad-rotate'), 10) || 8000);
    var pause = false;
    el.addEventListener('mouseenter', function () { pause = true; });
    el.addEventListener('mouseleave', function () { pause = false; });
    el.addEventListener('focusin', function () { pause = true; });
    el.addEventListener('focusout', function () { pause = false; });
    // Versatz: jeder weitere Platz wechselt etwas später als der vorige.
    var versatz = (plaetze.length - 1) * 1300 % takt;
    window.setTimeout(function () {
      window.setInterval(function () {
        if (pause || document.hidden) return;
        zeige(wahl.naechste(firmen, p.aktiv, belegt(p, true)));
      }, takt);
    }, versatz);
  }

  window.maAdPlatz = einrichten;
  Array.prototype.forEach.call(document.querySelectorAll('.ma-ad'), einrichten);
})();
