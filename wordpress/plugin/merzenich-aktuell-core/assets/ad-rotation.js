/*
 * Rotation der Anzeigen eines Werbeplatzes (Merzenich Aktuell Core).
 * Alle Anzeigen stehen im HTML; sichtbar ist jeweils eine. Beim Laden beginnt
 * die Rotation an einer zufaelligen Stelle, danach wechselt sie im Takt aus
 * data-ma-ad-rotate. Bei Hover/Fokus pausiert sie, damit ein Klick die
 * gemeinte Anzeige trifft. Ohne JavaScript bleibt die erste sichtbar.
 */
(function () {
  'use strict';
  var plaetze = document.querySelectorAll('.ma-ad[data-ma-ad-rotate]');
  Array.prototype.forEach.call(plaetze, function (platz) {
    var items = platz.querySelectorAll('.ma-ad__item');
    if (items.length < 2) return;
    var takt = Math.max(3000, parseInt(platz.getAttribute('data-ma-ad-rotate'), 10) || 8000);
    var aktiv = Math.floor(Math.random() * items.length);
    var pause = false;
    function zeige(i) {
      Array.prototype.forEach.call(items, function (el, n) { el.hidden = n !== i; });
      platz.setAttribute('data-ma-ad-current', String(i));
    }
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
  });
})();
