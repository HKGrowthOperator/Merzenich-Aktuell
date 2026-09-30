/* /unternehmen/: weitere Meldungen nachladen, wie bei Oberberg Aktuell beim
 * Scrollen ans Listenende, dazu ein Knopf fuer Tastatur und ohne Scrollen.
 * Alle Karten stehen im HTML; ohne JavaScript bleibt der Knopf wirkungslos
 * und die ersten acht Meldungen stehen da. */
(() => {
  'use strict';
  const liste = document.querySelector('[data-u-karten]');
  const knopf = document.querySelector('[data-u-mehr]');
  if (!liste || !knopf) return;
  const SCHRITT = 4;
  function mehr() {
    const naechste = [...liste.querySelectorAll('[data-nachladen][hidden]')].slice(0, SCHRITT);
    naechste.forEach((k) => { k.hidden = false; });
    if (naechste[0]) { const link = naechste[0].querySelector('h2 a'); if (link && document.activeElement === knopf) link.focus(); }
    if (!liste.querySelector('[data-nachladen][hidden]')) { knopf.closest('.u-mehr').hidden = true; beobachter && beobachter.disconnect(); }
  }
  knopf.addEventListener('click', mehr);
  const beobachter = 'IntersectionObserver' in window ? new IntersectionObserver((e) => { if (e.some((x) => x.isIntersecting)) mehr(); }, { rootMargin: '0px 0px 300px 0px' }) : null;
  if (beobachter) beobachter.observe(knopf);
})();
