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
  script.src = '/assets/bild-fallbacks.js?v=a047bce21a';
  script.async = false;
  script.dataset.maStartbilder = '1';
  document.head.append(script);
})();

(() => {
  if (!document.querySelector('link[data-ma-home-polish]')) {
    const style = document.createElement('link');
    style.rel = 'stylesheet';
    style.href = '/assets/homepage-polish.css?v=20260917-3';
    style.dataset.maHomePolish = '1';
    document.head.append(style);
  }
  if (!document.querySelector('script[data-ma-home-polish]')) {
    const script = document.createElement('script');
    script.src = '/assets/homepage-polish.js?v=20260917-3';
    script.async = false;
    script.dataset.maHomePolish = '1';
    document.head.append(script);
  }
})();

(() => {
  if (document.querySelector('script[data-ma-content-refresh]')) return;
  const script = document.createElement('script');
  script.src = '/assets/content-refresh-2026-09-17.js?v=fd8dd27040';
  script.async = false;
  script.dataset.maContentRefresh = '1';
  document.head.append(script);
})();

(() => {
  if (!document.querySelector('link[data-ma-editorial-audit]')) {
    const style = document.createElement('link');
    style.rel = 'stylesheet';
    style.href = '/assets/editorial-audit.css?v=20260917-4';
    style.dataset.maEditorialAudit = '1';
    document.head.append(style);
  }
  if (!document.querySelector('script[data-ma-editorial-audit]')) {
    const script = document.createElement('script');
    script.src = '/assets/editorial-audit.js?v=20260917-4';
    script.async = false;
    script.dataset.maEditorialAudit = '1';
    document.head.append(script);
  }
})();
