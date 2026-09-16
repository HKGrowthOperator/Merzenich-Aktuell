(() => {
 'use strict';
 const $=s=>document.querySelector(s),$$=s=>[...document.querySelectorAll(s)];
 const day=d=>new Intl.DateTimeFormat('sv-SE',{timeZone:'Europe/Berlin',year:'numeric',month:'2-digit',day:'2-digit'}).format(d);
 function clock(){const now=new Date();$$('[data-breaking-until]').forEach(el=>el.hidden=new Date(el.dataset.breakingUntil)<=now);$$('[data-event-end]').forEach(el=>el.hidden=new Date(el.dataset.eventEnd)<now);$$('.today-desk').forEach(section=>{const rows=[...section.querySelectorAll('[data-event-end]')];if(rows.length)section.hidden=rows.every(el=>el.hidden);});}
 clock();setInterval(clock,60000);
 const motion=matchMedia('(prefers-reduced-motion: reduce)');motion.addEventListener('change',()=>{if(motion.matches)$$('.reveal-pending').forEach(el=>el.classList.remove('reveal-pending'));});
 const nav=$('.mainnav'),anchor=$('.nav-anchor');
 const updateNav=()=>{const compact=!!anchor&&innerWidth>767&&anchor.getBoundingClientRect().top<=0;nav?.classList.toggle('is-scrolled',compact);};
 addEventListener('scroll',updateNav,{passive:true});addEventListener('resize',updateNav,{passive:true});updateNav();
 const filter=$('[data-event-filters]');
 if(filter){let period='all';const rows=$$('[data-event-row]'),place=$('[data-event-place]'),category=$('[data-event-category]');
 const run=()=>{const now=new Date(),today=day(now),berlinDay=new Date(today+'T12:00:00Z').getUTCDay(),satOffset=berlinDay===0?-1:(6-berlinDay+7)%7,sat=day(new Date(+now+satOffset*86400000)),sun=day(new Date(+now+(satOffset+1)*86400000));let n=0;
 rows.forEach(el=>{const start=new Date(el.dataset.start),end=new Date(el.dataset.end),d=day(start);const inPeriod=period==='all'||period==='today'&&(d<=today&&day(end)>=today)||period==='weekend'&&(d<=sun&&day(end)>=sat)||period==='14'&&(start<=new Date(+now+14*86400000));const show=end>=now&&inPeriod&&(!place.value||place.value===el.dataset.place)&&(!category.value||category.value===el.dataset.category);el.hidden=!show;if(show)n++;});$('[data-event-count]').textContent=n+(n===1?' Termin':' Termine');$('[data-event-empty]').hidden=n>0;};
 filter.querySelectorAll('[data-period]').forEach(b=>b.addEventListener('click',()=>{period=b.dataset.period;filter.querySelectorAll('[data-period]').forEach(x=>x.setAttribute('aria-pressed',String(x===b)));run();}));place.addEventListener('change',run);category.addEventListener('change',run);run();}
 if('IntersectionObserver' in window&&!matchMedia('(prefers-reduced-motion: reduce)').matches){const observer=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.remove('reveal-pending');e.target.classList.add('reveal-visible');observer.unobserve(e.target);}}),{threshold:0,rootMargin:'0px 0px 24px 0px'});$$('.desk-heading,.feed-row,.agenda-row').forEach((el,i)=>{if(el.getBoundingClientRect().top>innerHeight+80){el.classList.add('reveal-pending');el.style.transitionDelay=(i%3)*40+'ms';observer.observe(el);}});addEventListener('beforeprint',()=>$$('.reveal-pending').forEach(el=>el.classList.remove('reveal-pending')));}
})();

// Berlin date/clock, semantic details and overflow navigation.
(()=>{
 const clock=()=>{const d=new Date();document.querySelectorAll('[data-clock]').forEach(el=>{el.textContent=new Intl.DateTimeFormat('de-DE',{timeZone:'Europe/Berlin',hour:'2-digit',minute:'2-digit'}).format(d)+' Uhr';el.dateTime=d.toISOString();});document.querySelectorAll('[data-today]').forEach(el=>{el.textContent=new Intl.DateTimeFormat('de-DE',{timeZone:'Europe/Berlin',weekday:'short',day:'2-digit',month:'2-digit',year:'numeric'}).format(d);el.dateTime=d.toISOString();});};clock();setInterval(clock,30000);
 document.querySelectorAll('.service-accordion,.nav-more').forEach((el,i)=>{const summary=el.querySelector('summary'),body=summary?.nextElementSibling;if(!summary||!body)return;body.id=body.id||'expand-content-'+i;summary.setAttribute('aria-controls',body.id);const update=()=>summary.setAttribute('aria-expanded',String(el.open));el.addEventListener('toggle',update);update();});
 const nav=document.querySelector('.mainnav .navscroll'),more=document.querySelector('.nav-more .more-links');if(nav&&more){const links=[...nav.children];const fit=()=>{links.forEach(el=>nav.append(el));if(innerWidth<768)return;let guard=0;while(nav.scrollWidth>nav.clientWidth+1&&nav.children.length>1&&guard++<20){more.prepend(nav.lastElementChild);}};new ResizeObserver(fit).observe(nav);fit();}
 document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.nav-more[open]').forEach(el=>{el.open=false;el.querySelector('summary').focus();});});
 document.addEventListener('click',e=>document.querySelectorAll('.nav-more[open]').forEach(el=>{if(!el.contains(e.target))el.open=false;}));
})();

// Wetter: same-origin Coolify proxy -> Open-Meteo. Bei Providerfehlern wird der
// letzte gueltige Browser-Cache verwendet; ohne valide Daten verschwindet nur
// das Wettermodul, nie Uhrzeit oder Datum.
(()=>{
 'use strict';
 const KEY='ma-weather-v2',MAX_STALE=12*60*60*1000;
 const weatherCode=code=>{
  if(code===0)return['☀️','Klar'];
  if(code===1)return['🌤️','Überwiegend klar'];
  if(code===2)return['⛅','Teilweise bewölkt'];
  if(code===3)return['☁️','Bewölkt'];
  if([45,48].includes(code))return['🌫️','Nebel'];
  if([51,53,55,56,57].includes(code))return['🌦️','Nieselregen'];
  if([61,63,65,66,67,80,81,82].includes(code))return['🌧️','Regen'];
  if([71,73,75,77,85,86].includes(code))return['🌨️','Schnee'];
  if([95,96,99].includes(code))return['⛈️','Gewitter'];
  return['🌥️','Wetter'];
 };
 const fmtTime=stamp=>new Intl.DateTimeFormat('de-DE',{timeZone:'Europe/Berlin',hour:'2-digit',minute:'2-digit'}).format(new Date(stamp))+' Uhr';
 const round=n=>Number.isFinite(Number(n))?Math.round(Number(n)):null;
 const findWeatherDetails=()=>[...document.querySelectorAll('.service-accordion')].find(el=>el.querySelector('summary')?.textContent.trim()==='Wetter');
 function render(payload,cachedAt,isFallback){
  if(!payload?.daily?.time?.length)return false;
  const cur=payload.current||{};const [emoji,label]=weatherCode(Number(cur.weather_code));const temp=round(cur.temperature_2m);
  const header=document.querySelector('.header-local');
  if(header&&temp!==null){let w=header.querySelector('.header-weather');if(!w){w=document.createElement('span');w.className='header-weather';header.prepend(w);}w.textContent=`${emoji} ${temp} °C`;w.title=`${label} · Stand ${fmtTime(cachedAt)}${isFallback?' · letzter erfolgreicher Abruf':''}`;}
  const details=findWeatherDetails();
  if(details){const body=details.querySelector('summary')?.nextElementSibling;if(body){const names=['Heute','Morgen','Übermorgen'];const d=payload.daily;body.innerHTML=`<div class="weather-days">${d.time.slice(0,3).map((_,i)=>{const [icon,desc]=weatherCode(Number(d.weather_code?.[i]));const hi=round(d.temperature_2m_max?.[i]);const lo=round(d.temperature_2m_min?.[i]);const rain=round(d.precipitation_probability_max?.[i]);return `<div><b>${names[i]||'Tag '+(i+1)}</b><div>${icon} ${desc}</div><small>${lo!==null&&hi!==null?`${lo}–${hi} °C`:''}${rain!==null?` · Regen ${rain} %`:''}</small></div>`;}).join('')}</div><small>Stand ${fmtTime(cachedAt)}${isFallback?' · letzter erfolgreicher Abruf':''}</small>`;details.hidden=false;}}
  return true;
 }
 function cached(){try{const c=JSON.parse(localStorage.getItem(KEY)||'null');if(c&&c.at&&c.data&&Date.now()-c.at<=MAX_STALE)return c;}catch(e){}return null;}
 const old=cached();if(old)render(old.data,old.at,true);
 fetch('/api/weather.json',{headers:{Accept:'application/json'}}).then(r=>{if(!r.ok)throw new Error('weather '+r.status);return r.json();}).then(data=>{if(!data?.daily?.time?.length)throw new Error('weather payload');const at=Date.now();try{localStorage.setItem(KEY,JSON.stringify({at,data}));}catch(e){}render(data,at,false);}).catch(()=>{if(old)return;const details=findWeatherDetails();if(details)details.hidden=true;});
})();

// Sport: alle sichtbaren Werte kommen aus genau einem geprueften Datenstand.
(()=>{
 'use strict';
 const module=document.querySelector('.sports-module');if(!module)return;
 const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 const dmy=iso=>new Intl.DateTimeFormat('de-DE',{timeZone:'Europe/Berlin',day:'2-digit',month:'2-digit',year:'numeric'}).format(new Date(iso));
 const hm=iso=>new Intl.DateTimeFormat('de-DE',{timeZone:'Europe/Berlin',hour:'2-digit',minute:'2-digit'}).format(new Date(iso))+' Uhr';
 const team=(name,withLogo)=>`<div class="match-team">${withLogo?'<img src="/assets/uploads/sc-1919-merzenich-logo.webp" width="58" height="58" alt="Vereinslogo SC 1919 Merzenich" loading="lazy">':''}<b>${esc(name)}</b></div>`;
 fetch('/api/sport-current.json',{cache:'no-store'}).then(r=>{if(!r.ok)throw new Error('sport '+r.status);return r.json();}).then(data=>{
  const panels=module.querySelectorAll('.match-panel');const last=data.lastMatch,next=data.nextMatch;
  if(panels[0]&&last){const time=panels[0].querySelector('.match-heading time');if(time){time.dateTime=last.date;time.textContent=`${dmy(last.date)} · ${last.confirmed?'Endstand':'noch nicht bestätigt'}`;}const grid=panels[0].querySelector('.match-grid');if(grid)grid.innerHTML=`${team(last.home,/Merzenich/i.test(last.home))}<strong class="match-score">${esc(last.score||'–')}</strong>${team(last.away,/Merzenich/i.test(last.away))}`;const source=panels[0].querySelector('.match-source');if(source&&last.reportUrl){source.href=last.reportUrl;source.target='_blank';source.rel='noopener';source.textContent='Spielbericht bei FUSSBALL.DE';}}
  if(panels[1]&&next){const time=panels[1].querySelector('.match-heading time');if(time){time.dateTime=next.date;time.textContent=`${dmy(next.date)} · ${hm(next.date)}`;}const grid=panels[1].querySelector('.match-grid');if(grid)grid.innerHTML=`${team(next.home,/Merzenich/i.test(next.home))}<span class="match-versus">gegen</span>${team(next.away,/Merzenich/i.test(next.away))}`;}
  const league=module.querySelector('.league-panel');if(league&&Array.isArray(data.table)){const h=league.querySelector('.league-heading h3');if(h)h.textContent='Kreisliga A · Tabelle';const stamp=league.querySelector('.league-heading span');if(stamp)stamp.textContent=`Datenstand ${dmy(data.generated)}, ${hm(data.generated)}`;const caption=league.querySelector('caption');if(caption)caption.textContent=`Kreisliga A. Datenstand ${dmy(data.generated)}, ${hm(data.generated)}`;const tbody=league.querySelector('tbody');if(tbody)tbody.innerHTML=data.table.map(r=>`<tr${r.homeTeam?' class="home-team"':''}><td>${r.place}</td><th scope="row">${esc(r.team)}</th><td>${r.played}</td><td>${r.wins}</td><td>${r.draws}</td><td>${r.losses}</td><td>${esc(r.goals)}</td><td>${r.diff>0?'+':''}${r.diff}</td><td>${r.points}</td></tr>`).join('');}
 }).catch(()=>{});
})();
