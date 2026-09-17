/* Merzenich Aktuell — Homepage-Qualität 17.09.2026
 * Grundregel: Ein vorhandenes Motiv wird nicht wegen Pixel-Schwellen aus dem
 * Layout gelöscht. Fehlt ein brauchbares Motiv, kommt ein geprüftes thematisches
 * Symbolbild zum Einsatz. Dadurch bleibt die redaktionelle Bildhierarchie stabil.
 */
(() => {
  'use strict';
  const q=(s,r=document)=>r.querySelector(s), qa=(s,r=document)=>[...r.querySelectorAll(s)];
  const BERLIN='Europe/Berlin';

  const FALLBACKS={
    sport:{src:'https://commons.wikimedia.org/wiki/Special:FilePath/2026-08-30%20Fu%C3%9Fballplatz%20Trogen%20HOF8480%20RAW-Export.png?width=1600',alt:'Fußballplatz als Symbolbild für Lokalsport'},
    polizei:{src:'https://commons.wikimedia.org/wiki/Special:FilePath/Neue%20Streifenwagen%20f%C3%BCr%20die%20Polizei%20vorgestellt.jpg?width=1600',alt:'Streifenwagen der Polizei Nordrhein-Westfalen'},
    feuerwehr:{src:'https://commons.wikimedia.org/wiki/Special:FilePath/Feuerwehrm%C3%A4nner%20im%20Einsatz.jpg?width=1600',alt:'Feuerwehrkräfte bei einem Einsatz'},
    rathaus:{src:'https://commons.wikimedia.org/wiki/Special:FilePath/Merzenich%20Rathaus%20HDR.jpg?width=1600',alt:'Rathaus der Gemeinde Merzenich'},
    leben:{src:'https://commons.wikimedia.org/wiki/Special:FilePath/Merzenich%20Alte%20Pfarrkirche.jpg?width=1600',alt:'Ortsmotiv aus Merzenich'},
    termine:{src:'https://commons.wikimedia.org/wiki/Special:FilePath/Merzenich%20Denkmal-Nr.%2018%2C%20Lindenplatz%20%281235%29.jpg?width=1600',alt:'Lindenplatz in Merzenich'}
  };

  function berlinNow(){
    const d=new Date();
    return {
      label:new Intl.DateTimeFormat('de-DE',{timeZone:BERLIN,weekday:'long',day:'2-digit',month:'long',year:'numeric'}).format(d),
      short:new Intl.DateTimeFormat('de-DE',{timeZone:BERLIN,day:'2-digit',month:'2-digit'}).format(d).replace(/\s/g,''),
      iso:new Intl.DateTimeFormat('en-CA',{timeZone:BERLIN,year:'numeric',month:'2-digit',day:'2-digit'}).format(d)
    };
  }
  function refreshVisibleDate(){
    const d=berlinNow();
    qa('[data-today]').forEach(t=>{t.textContent=d.short;t.dateTime=d.iso;t.title=d.label});
  }
  function addDateline(){
    if(!document.body.classList.contains('home')||q('.ma-home-dateline'))return;
    const edition=q('.edition-label'), portal=q('.portal-top'); if(!portal)return;
    const d=berlinNow(), section=document.createElement('section');
    section.className='ma-home-dateline shell';
    section.innerHTML=`<div class="ma-home-dateline__copy"><span class="ma-home-dateline__eyebrow">Lokaler Überblick</span><h1>Heute in Merzenich</h1></div><time datetime="${d.iso}">${d.label}</time>`;
    edition?.after(section);
  }
  function expireAgenda(){
    const now=Date.now();
    qa('.agenda-row[data-event-end]').forEach(row=>{const end=Date.parse(row.dataset.eventEnd||'');if(Number.isFinite(end)&&end<now)row.remove()});
  }
  function keyFor(article){
    const text=`${article?.textContent||''} ${article?.querySelector('a')?.getAttribute('href')||''}`.toLocaleLowerCase('de-DE');
    if(/feuerwehr|brand|lösch|einsatz/.test(text))return'feuerwehr';
    if(/blaulicht|polizei|einbruch|zeugen|verkehr/.test(text))return'polizei';
    if(/sport|fußball|fussball|sc merzenich|kreisliga/.test(text))return'sport';
    if(/rathaus|politik|gemeinde|rat/.test(text))return'rathaus';
    if(/termin|veranstaltung|verein/.test(text))return'termine';
    return'leben';
  }
  function fallback(key){
    return window.MerzenichStartbilder?.bilder?.[key]||FALLBACKS[key]||FALLBACKS.leben;
  }
  function setImage(img,bild){
    img.removeAttribute('srcset');img.removeAttribute('sizes');
    if(window.maExtern)window.maExtern.setze(img,bild.src);else img.src=bild.src;
    img.alt=bild.alt||'Symbolbild';img.dataset.editorialImage='';img.dataset.maSymbolbild='1';
  }
  function ensureHeroImage(hero){
    if(!hero)return;
    const key=keyFor(hero), bild=fallback(key);
    let media=q(':scope > a > .media',hero)||q(':scope > .media',hero), img=media&&q('img',media);
    const looksLikeLogo=img&&/(logo|wappen|vereinslogo|crest)/i.test(`${img.src} ${img.alt}`);
    if(!media){
      const title=q('h1 a',hero); if(!title)return;
      const a=document.createElement('a');a.href=title.href;a.tabIndex=-1;a.setAttribute('aria-hidden','true');
      media=document.createElement('div');media.className='media';img=document.createElement('img');img.loading='eager';img.fetchPriority='high';media.append(img);a.append(media);hero.prepend(a);
    }
    if(!img){img=document.createElement('img');img.loading='eager';img.fetchPriority='high';media.append(img)}
    if(looksLikeLogo||!img.getAttribute('src')){setImage(img,bild);media.classList.remove('contain');let badge=q('.badge',media);if(!badge){badge=document.createElement('span');badge.className='badge';media.append(badge)}badge.textContent='Symbolbild';}
  }
  function ensureBriefImage(article){
    if(!article||q('.media img',article))return;
    const title=q('h2 a,h3 a',article);if(!title)return;
    const bild=fallback(keyFor(article));
    const a=document.createElement('a');a.href=title.href;a.className='front-brief-media';a.tabIndex=-1;a.setAttribute('aria-hidden','true');
    const media=document.createElement('div');media.className='media';
    const img=document.createElement('img');img.loading='lazy';img.decoding='async';setImage(img,bild);media.append(img);a.append(media);article.prepend(a);
  }
  function ensureHomepageVisuals(){
    if(!document.body.classList.contains('home'))return;
    ensureHeroImage(q('.front-lead'));
    qa('.front-side .front-brief').forEach(ensureBriefImage);
  }
  function removeTextOnlyAds(){
    qa('.managed-ad').forEach(ad=>{if(!q('img,picture,video',ad))ad.remove()});
    qa('.ad-row-body').forEach(row=>{if(!q('.managed-ad,.ma-ad,img,picture,video',row))row.remove()});
  }
  function start(){
    refreshVisibleDate();addDateline();expireAgenda();removeTextOnlyAds();ensureHomepageVisuals();
    const grid=q('.frontpage-grid');
    if(grid&&'MutationObserver'in window){let timer;const obs=new MutationObserver(()=>{clearTimeout(timer);timer=setTimeout(ensureHomepageVisuals,60)});obs.observe(grid,{childList:true,subtree:true});setTimeout(()=>obs.disconnect(),8000)}
    setTimeout(ensureHomepageVisuals,500);setTimeout(ensureHomepageVisuals,1800);
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start,{once:true});else start();
})();
