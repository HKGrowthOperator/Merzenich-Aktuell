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

/* Fallback fuer alte Browser-Caches: selbst wenn noch eine alte theme.js aus
 * dem Cache kommt, bleibt oben nur der kompakte Sonne/Mond-Schalter sichtbar. */
(() => {
  const sun='<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3.4" fill="none" stroke="currentColor" stroke-width="1.65"/><path d="M12 2.5v2.2M12 19.3v2.2M2.5 12h2.2M19.3 12h2.2M5.3 5.3l1.5 1.5M17.2 17.2l1.5 1.5M18.7 5.3l-1.5 1.5M6.8 17.2l-1.5 1.5" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round"/></svg>';
  const moon='<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19.35 15.35A8.15 8.15 0 0 1 8.65 4.65a8.2 8.2 0 1 0 10.7 10.7Z" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linejoin="round"/></svg>';
  function init(){
    const actions=document.querySelector('.masthead .mast-actions');if(!actions)return;
    let b=actions.querySelector('.theme-toggle');
    if(!b){b=document.createElement('button');b.type='button';b.className='theme-toggle';const before=actions.querySelector('.mobile-search')||actions.querySelector('.menu-btn');before?actions.insertBefore(b,before):actions.append(b)}
    const render=()=>{const dark=document.documentElement.dataset.theme==='dark';b.innerHTML=dark?sun:moon;b.setAttribute('aria-label',dark?'Helle Ansicht einschalten':'Dunkle Ansicht einschalten');b.title=dark?'Helle Ansicht':'Dunkle Ansicht'};
    if(!b.dataset.maBound){b.dataset.maBound='1';b.addEventListener('click',()=>{const next=document.documentElement.dataset.theme==='dark'?'light':'dark';document.documentElement.dataset.theme=next;document.documentElement.style.colorScheme=next;try{localStorage.setItem('merzenich-theme',next)}catch(e){}render()})}
    render();
  }
  document.readyState==='loading'?document.addEventListener('DOMContentLoaded',init,{once:true}):init();
})();

(() => {
  if (document.querySelector('script[data-ma-startbilder]')) return;
  const script = document.createElement('script');
  script.src = '/assets/bild-fallbacks.js?v=b5065bee27';
  script.async = false;
  script.dataset.maStartbilder = '1';
  document.head.append(script);
})();

(() => {
  if (!document.querySelector('link[data-ma-home-polish]')) {
    const style = document.createElement('link');
    style.rel = 'stylesheet';
    style.href = '/assets/homepage-polish.css?v=90d54ca469';
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
    style.href = '/assets/editorial-audit.css?v=204fc06168';
    style.dataset.maEditorialAudit = '1';
    document.head.append(style);
  }
  if (!document.querySelector('script[data-ma-editorial-audit]')) {
    const script = document.createElement('script');
    script.src = '/assets/editorial-audit.js?v=672b5cbfc7';
    script.async = false;
    script.dataset.maEditorialAudit = '1';
    document.head.append(script);
  }
})();
