/* Merzenich Aktuell — direkter Hell/Dunkel-Schalter.
 * Kein generischer Einstellungen-/Darstellung-Button mehr: oben sitzt nur ein
 * ruhiger Theme-Schalter. Light bleibt Standard, Auswahl wird lokal gespeichert.
 */
(() => {
  'use strict';
  const STORAGE='merzenich-theme', LIGHT='light', DARK='dark';
  const LOGO_LIGHT='/assets/img/logo-on-light.png', LOGO_DARK='/assets/img/logo.png';
  const sun='<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="3.4" fill="none" stroke="currentColor" stroke-width="1.65"/><path d="M12 2.5v2.2M12 19.3v2.2M2.5 12h2.2M19.3 12h2.2M5.28 5.28l1.55 1.55M17.17 17.17l1.55 1.55M18.72 5.28l-1.55 1.55M6.83 17.17l-1.55 1.55" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round"/></svg>';
  const moon='<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19.35 15.35A8.15 8.15 0 0 1 8.65 4.65a8.2 8.2 0 1 0 10.7 10.7Z" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linejoin="round"/></svg>';

  function read(){try{return localStorage.getItem(STORAGE)===DARK?DARK:LIGHT}catch(e){return LIGHT}}
  function apply(theme,save=true){
    const next=theme===DARK?DARK:LIGHT;
    document.documentElement.dataset.theme=next;
    document.documentElement.style.colorScheme=next;
    const meta=document.querySelector('meta[name="theme-color"]');
    if(meta)meta.content=next===DARK?'#111111':'#ffffff';
    document.querySelectorAll('.masthead .logo img').forEach(img=>img.src=next===DARK?LOGO_DARK:LOGO_LIGHT);
    const button=document.querySelector('.theme-toggle');
    if(button){
      const dark=next===DARK;
      button.innerHTML=dark?sun:moon;
      button.setAttribute('aria-label',dark?'Helle Ansicht einschalten':'Dunkle Ansicht einschalten');
      button.title=dark?'Helle Ansicht':'Dunkle Ansicht';
      button.setAttribute('aria-pressed',String(dark));
    }
    if(save){try{localStorage.setItem(STORAGE,next)}catch(e){}}
  }
  function setup(){
    const actions=document.querySelector('.masthead .mast-actions');
    if(!actions)return;
    document.querySelectorAll('.darstellung-knopf,.einstellungen,.einstellungen-schleier').forEach(el=>el.remove());
    let button=actions.querySelector('.theme-toggle');
    if(!button){
      button=document.createElement('button');
      button.type='button';
      button.className='theme-toggle';
      const before=actions.querySelector('.mobile-search')||actions.querySelector('.menu-btn');
      before?actions.insertBefore(button,before):actions.append(button);
      button.addEventListener('click',()=>apply(document.documentElement.dataset.theme===DARK?LIGHT:DARK,true));
    }
    apply(read(),false);
  }
  apply(read(),false);
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',setup,{once:true});else setup();
})();
