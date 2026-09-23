/*
 * Kopf klappt beim Scrollen ein (Desktop): Der grosse Kopf mit Logo, Suche
 * und Uhrzeit schiebt sich nach oben weg, die Ressortleiste bleibt oben.
 * Im kompakten Zustand rendert der Design-Layer nur das M-Monogramm mit
 * goldener Signatur. Mobil bleibt der Kopf kompakt.
 */
(() => {
  const html = document.documentElement;
  const mast = document.querySelector('.masthead');
  if (!mast) return;
  let kompakt = null, angefordert = false;
  function aktualisieren() {
    angefordert = false;
    const schwelle = mast.offsetHeight + 40;
    const naechster = window.innerWidth > 767 && window.scrollY > schwelle;
    if (naechster === kompakt) return;
    kompakt = naechster;
    html.classList.toggle('kopf-kompakt', naechster);
  }
  function anfordern() { if (angefordert) return; angefordert = true; requestAnimationFrame(aktualisieren); }
  aktualisieren();
  addEventListener('scroll', anfordern, { passive: true });
  addEventListener('resize', anfordern, { passive: true });
  addEventListener('pageshow', anfordern);
})();

(() => {
  if (document.querySelector('script[data-ma-startbilder]')) return;
  const script = document.createElement('script');
  script.src = '/assets/bild-fallbacks.js?v=b3aac0a841';
  script.async = false;
  script.dataset.maStartbilder = '1';
  document.head.append(script);
})();

(() => {
  if (!document.querySelector('link[data-ma-home-polish]')) {
    const style = document.createElement('link');
    style.rel = 'stylesheet';
    style.href = '/assets/homepage-polish.css?v=e5229c53bf';
    style.dataset.maHomePolish = '1';
    document.head.append(style);
  }
  if (!document.querySelector('script[data-ma-home-polish]')) {
    const script = document.createElement('script');
    script.src = '/assets/homepage-polish.js?v=5174c2bc37';
    script.async = false;
    script.dataset.maHomePolish = '1';
    document.head.append(script);
  }
})();

(() => {
  if (document.querySelector('script[data-ma-content-refresh]')) return;
  const script = document.createElement('script');
  script.src = '/assets/content-refresh-2026-09-17.js?v=3e02d9c2c7';
  script.async = false;
  script.dataset.maContentRefresh = '1';
  document.head.append(script);
})();

(() => {
  if (!document.querySelector('link[data-ma-editorial-audit]')) {
    const style = document.createElement('link');
    style.rel = 'stylesheet';
    style.href = '/assets/editorial-audit.css?v=9985bfebfe';
    style.dataset.maEditorialAudit = '1';
    document.head.append(style);
  }
  if (!document.querySelector('script[data-ma-editorial-audit]')) {
    const script = document.createElement('script');
    script.src = '/assets/editorial-audit.js?v=09ba7ce9a9';
    script.async = false;
    script.dataset.maEditorialAudit = '1';
    document.head.append(script);
  }
  // Ortswahl: das Aufklappen kann <details> allein. JavaScript ergaenzt nur,
  // was ohne Skript niemand erwartet - Schliessen bei Klick daneben und mit
  // Escape. Faellt das Skript aus, bleibt die Wahl voll bedienbar.
  {
    const wahl = document.querySelector('.ortswahl-schalter');
    if (wahl) {
      document.addEventListener('click', (e) => { if (wahl.open && !wahl.contains(e.target)) wahl.open = false; });
      document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape' || !wahl.open) return;
        wahl.open = false;
        const s = wahl.querySelector('summary'); if (s) s.focus();
      });
    }
  }
})();
