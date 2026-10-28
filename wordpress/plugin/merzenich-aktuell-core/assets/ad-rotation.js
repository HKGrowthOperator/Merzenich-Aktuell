/*
 * Rotation der Anzeigen eines Werbeplatzes (Merzenich Aktuell Core).
 * Alle Anzeigen stehen im HTML; sichtbar ist jeweils eine. Beim Laden beginnt
 * die Rotation an einer zufaelligen Stelle, danach wechselt sie im Takt aus
 * data-ma-ad-rotate. Bei Hover/Fokus pausiert sie, damit ein Klick die
 * gemeinte Anzeige trifft. Ohne JavaScript bleibt die erste sichtbar.
 *
 * Video-Schleifen (28.10.2026): Ein <video data-src> laedt erst, wenn seine
 * Anzeige sichtbar und der Platz im Bild ist, und pausiert sonst; eingeblendet
 * wird es erst, wenn es laeuft (bis dahin steht das Bild darunter). Kein Video
 * bei "Bewegung reduzieren", Datensparmodus oder schmalem Bildschirm (bis
 * 560 px). window.maAdPlatz(platz) richtet nachgeladene Plaetze ein (endlos.js).
 */
(function () {
  'use strict';
  var bewegung = !(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
  var sparen = !!(navigator.connection && navigator.connection.saveData);
  var videoErlaubt = function () { return bewegung && !sparen && window.innerWidth > 560; };

  function videos(platz, aktivesItem, imBild) {
    Array.prototype.forEach.call(platz.querySelectorAll('video.ma-ad-video'), function (v) {
      var spielen = imBild && videoErlaubt() && v.closest('.ma-ad__item') === aktivesItem;
      if (spielen) {
        if (!v.getAttribute('src')) {
          // WebM (VP9), wo der Browser es kann, sonst MP4 (H.264).
          var webm = v.getAttribute('data-src-webm');
          var quelle = webm && v.canPlayType('video/webm; codecs="vp9"') ? webm : v.getAttribute('data-src');
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

  function einrichten(platz) {
    if (platz.getAttribute('data-ma-ad-bereit')) return;
    var items = platz.querySelectorAll('.ma-ad__item');
    if (!items.length) return;
    platz.setAttribute('data-ma-ad-bereit', '1');
    var aktiv = 0;
    var imBild = false;
    function zeige(i) {
      Array.prototype.forEach.call(items, function (el, n) { el.hidden = n !== i; });
      platz.setAttribute('data-ma-ad-current', String(i));
      videos(platz, items[i], imBild);
    }
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (eintraege) {
        imBild = eintraege[eintraege.length - 1].isIntersecting;
        videos(platz, items[aktiv], imBild);
      }, { rootMargin: '120px 0px' }).observe(platz);
    } else {
      imBild = true;
    }
    if (items.length < 2 || !platz.hasAttribute('data-ma-ad-rotate')) { zeige(0); return; }
    var takt = Math.max(3000, parseInt(platz.getAttribute('data-ma-ad-rotate'), 10) || 8000);
    aktiv = Math.floor(Math.random() * items.length);
    var pause = false;
    zeige(aktiv);
    platz.addEventListener('mouseenter', function () { pause = true; });
    platz.addEventListener('mouseleave', function () { pause = false; });
    platz.addEventListener('focusin', function () { pause = true; });
    platz.addEventListener('focusout', function () { pause = false; });
    window.setInterval(function () {
      if (pause || document.hidden) return;
      aktiv = (aktiv + 1) % items.length;
      zeige(aktiv);
    }, takt);
  }

  window.maAdPlatz = einrichten;
  Array.prototype.forEach.call(document.querySelectorAll('.ma-ad'), einrichten);
})();
