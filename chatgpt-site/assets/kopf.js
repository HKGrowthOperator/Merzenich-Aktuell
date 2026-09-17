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

/*
 * Der ausgelieferte Coolify-Stand bindet kopf.js auf allen redaktionellen
 * Seiten ein. Darueber wird die zentrale Startbild-Absicherung geladen, ohne
 * 213 statische HTML-Dateien einzeln anfassen zu muessen.
 */
(() => {
  if (document.querySelector('script[data-ma-startbilder]')) return;
  const script = document.createElement('script');
  script.src = '/assets/bild-fallbacks.js?v=a047bce21a';
  script.async = false;
  script.dataset.maStartbilder = '1';
  document.head.append(script);
})();

/*
 * Qualitaets-Layer fuer Startseite/Listen. Laedt zuletzt, damit die Regeln fuer
 * Bildschaerfe und Anzeigen nicht von den Recovery-Styles ueberschrieben werden.
 */
(() => {
  if (!document.querySelector('link[data-ma-home-polish]')) {
    const style = document.createElement('link');
    style.rel = 'stylesheet';
    style.href = '/assets/homepage-polish.css?v=07e9d64577';
    style.dataset.maHomePolish = '1';
    document.head.append(style);
  }
  if (!document.querySelector('script[data-ma-home-polish]')) {
    const script = document.createElement('script');
    script.src = '/assets/homepage-polish.js?v=d1fb2f7039';
    script.defer = true;
    script.dataset.maHomePolish = '1';
    document.head.append(script);
  }
})();

/*
 * Redaktioneller Tages-Refresh. Der Layer aktualisiert die schnell wechselnden
 * Startseiten-/Markt-/Anzeigenbereiche, ohne den statischen Gesamtbuild zu
 * duplizieren. Inhalte bleiben quellengebunden und datiert.
 */
(() => {
  if (document.querySelector('script[data-ma-content-refresh]')) return;
  const script = document.createElement('script');
  script.src = '/assets/content-refresh-2026-09-17.js?v=20260917-1';
  script.defer = true;
  script.dataset.maContentRefresh = '1';
  document.head.append(script);
})();
