/* Merzenich Aktuell — Editorial QA runtime, 17.09.2026 */
(() => {
  'use strict';
  const q=(s,r=document)=>r.querySelector(s), qa=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const path=location.pathname.replace(/\/+$/,'')||'/';

  /* 1) Tagesdatum darf nie am Build-Tag hängen bleiben. */
  const berlinParts=()=>new Intl.DateTimeFormat('de-DE',{timeZone:'Europe/Berlin',day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit',hour12:false}).formatToParts(new Date()).reduce((o,p)=>(o[p.type]=p.value,o),{});
  function refreshClock(){
    const p=berlinParts(), short=`${p.day}.${p.month}.`, full=`${p.year}-${p.month}-${p.day}T${p.hour}:${p.minute}:00+02:00`;
    qa('[data-today]').forEach(el=>{el.textContent=short;el.setAttribute('datetime',full)});
    qa('[data-clock]').forEach(el=>{el.textContent=`${p.hour}:${p.minute} Uhr`;el.setAttribute('datetime',full)});
  }
  refreshClock(); setInterval(refreshClock,60000);

  /* 2) Hauptnavigation auf sechs redaktionelle Ziele reduzieren. */
  function simplifyNav(){
    qa('.mainnav').forEach(nav=>{
      const row=q('.navscroll',nav), more=q('.more-links',nav); if(!row||!more)return;
      const wanted=['/nachrichten/','/blaulicht/','/sport/','/leben/','/rathaus/','/termine/'];
      const labels={
        '/nachrichten/':'Aktuell','/blaulicht/':'Blaulicht','/sport/':'Sport','/leben/':'Leben','/rathaus/':'Rathaus','/termine/':'Termine'
      };
      const byHref=new Map(qa(':scope > a',row).map(a=>[a.getAttribute('href'),a]));
      wanted.forEach(h=>{const a=byHref.get(h);if(a){a.textContent=labels[h];row.append(a)}});
      qa(':scope > a',row).filter(a=>!wanted.includes(a.getAttribute('href'))).forEach(a=>{
        const h=a.getAttribute('href'); if(!q(`a[href="${CSS.escape(h)}"]`,more)) more.prepend(a); else a.remove();
      });
    });
    qa('.districtbar .lbl').forEach(x=>x.textContent='Ortsteile');
  }
  simplifyNav();

  /* 3) Vergangene Termine verschwinden aus „Heute / nächste Tage“, nicht aus Archiven. */
  function expireAgenda(){
    const now=Date.now();
    qa('[data-event-end]').forEach(el=>{
      if(el.closest('.past'))return;
      const end=Date.parse(el.getAttribute('data-event-end')||'');
      if(Number.isFinite(end)&&end<now) el.remove();
    });
    if(path==='/termine'){
      const startToday=new Date(); startToday.setHours(0,0,0,0);
      qa('.event-list article,.event-list .event-row,.events-grid article').forEach(el=>{
        if(el.closest('.past'))return;
        const t=q('time[datetime]',el); if(!t)return;
        const d=Date.parse(t.getAttribute('datetime')); if(Number.isFinite(d)&&d<startToday.getTime()&&!el.hasAttribute('data-event-end')) el.setAttribute('data-editorial-hidden','1');
      });
    }
  }
  expireAgenda();

  /* 4) Tote Wettermodule beanspruchen keinen prominenten Platz. */
  function suppressDeadWeather(){
    qa('[data-weather], [data-weather-mini], .weather, .weather-box, .weather-card').forEach(el=>{
      const txt=(el.textContent||'').toLowerCase();
      if(/nicht verfügbar|nicht verfuegbar|konnte nicht geladen|wetterdaten.*derzeit/.test(txt)){
        const box=el.closest('.service-accordion,.sidebox,.weather-box,.weather-card')||el;
        box.setAttribute('data-editorial-hidden','1');
      }
    });
  }
  suppressDeadWeather(); setTimeout(suppressDeadWeather,2200); setTimeout(suppressDeadWeather,7000);

  /* 5) „Wird geladen …“ bekommt einen belastbaren Fehlerzustand. */
  setTimeout(()=>{
    if(!['/diskussion','/werbefrei'].includes(path))return;
    qa('main p, main div').forEach(el=>{
      if(el.children.length) return;
      if(!/^\s*(wird geladen|die diskussion wird geladen)[….\.]*\s*$/i.test(el.textContent||''))return;
      const box=document.createElement('div'); box.className='editorial-error';
      box.innerHTML='<h2>Inhalt konnte nicht geladen werden</h2><p>Die Verbindung zum Dienst ist gerade nicht verfügbar. Die übrige Website funktioniert weiter.</p><button type="button">Erneut versuchen</button>';
      q('button',box).addEventListener('click',()=>location.reload()); el.replaceWith(box);
    });
  },8000);

  /* 6) Leeres Menschen-Ressort zeigt keine fachfremden Sport-Fallbacks. */
  if(path==='/menschen'){
    const count=q('.count-line');
    if(count&&/^\s*0\s+Meldung/.test(count.textContent||'')){
      const feed=q('.feed');
      if(feed) feed.innerHTML='<div class="editorial-empty"><h2>Menschen & Familien aus der Gemeinde</h2><p>Hier erscheinen veröffentlichte Jubiläen, Hochzeiten, Ehrungen, Nachrufe und Porträts. Solange keine redaktionell bestätigten Meldungen vorliegen, zeigen wir keine fachfremden Ersatzartikel.</p><div class="editorial-empty-actions"><a href="/familienanzeigen/">Familienanzeigen</a><a href="/traueranzeigen/">Traueranzeigen</a><a href="/meldung-senden/">Meldung einsenden</a></div></div>';
    }
  }

  /* 7) Themenübersicht: redaktionelle Top-Themen statt endloser CMS-Wolke. */
  if(path==='/thema'){
    const cloud=q('.tagcloud');
    if(cloud){
      const links=qa(':scope > a',cloud);
      links.sort((a,b)=>{
        const n=x=>Number((q('small',x)?.textContent||'0').replace(/\D/g,''))||0;
        return n(b)-n(a)||a.textContent.localeCompare(b.textContent,'de');
      }).forEach((a,i)=>{cloud.append(a);if(i>=20)a.dataset.editorialExtra='1'});
      if(links.length>20){
        cloud.classList.add('is-collapsed');
        const btn=document.createElement('button'); btn.type='button'; btn.className='editorial-topic-toggle'; btn.textContent='Alle Themen A–Z anzeigen';
        btn.addEventListener('click',()=>{const open=cloud.classList.toggle('is-collapsed');btn.textContent=open?'Alle Themen A–Z anzeigen':'Top-Themen anzeigen'});
        cloud.after(btn);
      }
    }
  }

  /* 8) Veraltete interne Projekttexte gehören nicht in Artikelseiten. */
  qa('.author-box p,.info-prose p').forEach(p=>{
    if((p.textContent||'').includes('Namentlich gezeichnete Beiträge folgen, sobald das Team steht')){
      p.textContent=(p.textContent||'').replace('Namentlich gezeichnete Beiträge folgen, sobald das Team steht.','Beiträge werden redaktionell geprüft und transparent gekennzeichnet.');
    }
  });

  /* 9) Öffentlich sichtbare Redaktion nutzt eine Markenadresse. Rechtliche Datenschutzkontakte bleiben unverändert. */
  if(['/kontakt','/redaktion','/ueber-uns','/meldung-senden'].includes(path)){
    qa('a[href="mailto:info@kbs-management.tv"]').forEach(a=>{a.href='mailto:redaktion@merzenich-aktuell.de';a.textContent='redaktion@merzenich-aktuell.de'});
  }
})();
