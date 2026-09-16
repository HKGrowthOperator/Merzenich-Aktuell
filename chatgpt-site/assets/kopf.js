/*
 * Kopf klappt beim Scrollen ein (Desktop): Der grosse Kopf mit Logo, Suche
 * und Uhrzeit schiebt sich nach oben weg, die Ressortleiste bleibt oben
 * stehen und zeigt links das kleine Logo. Beim Zurueckscrollen kommt der
 * Kopf wieder. Die Klasse kopf-kompakt am <html> steuert alles per CSS
 * (korrekturen.css). Mobil bleibt der schmale Kopf mit Logo wie er ist.
 */
(() => {
  const html = document.documentElement;
  const mast = document.querySelector('.masthead');
  if (!mast) return;

  let kompakt = null;
  let angefordert = false;

  function aktualisieren() {
    angefordert = false;
    const schwelle = mast.offsetHeight + 40;
    const naechster = window.innerWidth > 767 && window.scrollY > schwelle;
    if (naechster === kompakt) return;
    kompakt = naechster;
    html.classList.toggle('kopf-kompakt', naechster);
  }

  function anfordern() {
    if (angefordert) return;
    angefordert = true;
    window.requestAnimationFrame(aktualisieren);
  }

  aktualisieren();
  window.addEventListener('scroll', anfordern, { passive: true });
  window.addEventListener('resize', anfordern, { passive: true });
  window.addEventListener('pageshow', anfordern);
})();
