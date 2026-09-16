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

// Homepage-Priorisierung: kein neues Layout, nur Inhalt der bestehenden Slots.
// Ein Hero, der aelter als sieben Tage ist, wird mit dem aktuellen redaktionell
// geprueften Prioritaetsdatensatz ersetzt. Schlaegt der Abruf fehl, bleibt das
// ausgelieferte HTML unveraendert als stabiler Fallback stehen.
(()=>{
 'use strict';
 if(!document.body.classList.contains('home'))return;
 const hero=document.querySelector('.front-lead');
 if(!hero)return;
 const safe=s=>String(s??'');
 // safe() ist fuer textContent gedacht und darf dort nicht escapen. Wo unten
 // innerHTML geschrieben wird, braucht es die escapende Fassung - die beiden
 // anderen Bloecke dieser Datei fuehren sie bereits unter demselben Namen.
 const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 const staticTime=hero.querySelector('.meta time');
 const staticDate=staticTime?.dateTime?new Date(staticTime.dateTime):null;
 const stale=!staticDate||!Number.isFinite(+staticDate)||(Date.now()-staticDate.getTime())>7*86400000;
 const setText=(selector,value)=>{const el=hero.querySelector(selector);if(el&&value!=null)el.textContent=safe(value);};
 function applyHero(h){
  if(!h?.url||!h?.title||!h?.published)return;
  hero.dataset.story=h.id||'';
  const mediaLink=hero.querySelector(':scope > a');
  if(mediaLink)mediaLink.href=h.url;
  const media=hero.querySelector('.media');
  if(media){media.classList.toggle('contain',h.imageFit==='contain');const img=media.querySelector('img');if(img&&h.image){img.src=h.image;img.alt=h.imageAlt||h.title;if(h.imageSrcset){img.srcset=h.imageSrcset;if(h.imageSizes)img.sizes=h.imageSizes;}else{img.removeAttribute('srcset');img.removeAttribute('sizes');}if(h.imageWidth&&h.imageHeight){img.width=h.imageWidth;img.height=h.imageHeight;}else{img.removeAttribute('width');img.removeAttribute('height');}}const badge=media.querySelector('.badge');if(badge){if(h.imageBadge){badge.textContent=h.imageBadge;badge.hidden=false;}else badge.hidden=true;}}
  const location=hero.querySelector('.location-line');if(location&&h.location)location.innerHTML=`<span class="location-brand">${esc(h.location)}</span>`;
  setText('.kicker',h.kicker);setText('.eyebrow',h.eyebrow);
  const title=hero.querySelector('h1 a');if(title){title.textContent=h.title;title.href=h.url;}
  const teaser=hero.querySelector('.front-lead-copy > p');if(teaser)teaser.textContent=h.teaser||'';
  const time=hero.querySelector('.meta time');if(time){time.dateTime=h.published;time.textContent=h.timeLabel||new Intl.DateTimeFormat('de-DE',{timeZone:'Europe/Berlin',day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'}).format(new Date(h.published))+' Uhr';}
  const read=hero.querySelector('.meta span');if(read&&h.readTime)read.textContent=h.readTime;
  const more=hero.querySelector('.read-more');if(more){more.href=h.url;more.childNodes.forEach(n=>{if(n.nodeType===Node.TEXT_NODE)n.textContent='Mehr lesen';});const sr=more.querySelector('.sr-only');if(sr)sr.textContent=': '+h.title;}
 }
 function applySecondary(s){
  if(!s?.url||!s?.title)return;
  const side=document.querySelector('.front-side');if(!side)return;
  let card=side.querySelector('[data-editorial-secondary]');
  if(!card){card=document.createElement('article');card.className='front-brief editorial-secondary';card.dataset.editorialSecondary='';const marker=side.querySelector(':scope > .eyebrow');marker?.insertAdjacentElement('afterend',card);}
  card.dataset.story=s.id||'';
  card.innerHTML=`<div><div class="location-line"><span class="location-brand">${esc(s.location||'MERZENICH')}</span></div><span class="kicker">${esc(s.kicker||'Aktuell')}</span><h3><a href="${esc(s.url)}">${esc(s.title)}</a></h3><p>${esc(s.teaser||'')}</p><div class="meta"><time datetime="${esc(s.published||'')}">${esc(s.timeLabel||'')}</time></div><div class="story-actions"><a class="read-more" href="${esc(s.url)}">Mehr lesen<span class="sr-only">: ${esc(s.title)}</span></a></div></div>`;
 }
 fetch('/api/editorial-current.json',{cache:'no-store'}).then(r=>{if(!r.ok)throw new Error('editorial '+r.status);return r.json();}).then(data=>{if(stale&&data.hero)applyHero(data.hero);if(data.secondary)applySecondary(data.secondary);}).catch(()=>{});
})();

// Archive-Priorisierung: dieselben redaktionell geprueften Meldungen muessen
// nach dem Refresh nicht nur auf der Startseite, sondern auch in den echten
// Ressortarchiven sichtbar sein. Bestehende Archive bleiben strukturell gleich.
(()=>{
 'use strict';
 const path=location.pathname.replace(/\/+$/,'/')||'/';
 if(!['/nachrichten/','/sport/','/blaulicht/'].includes(path))return;
 const feed=document.querySelector('.content-grid .feed');if(!feed)return;
 const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 function updateLead(s){
  const lead=feed.querySelector('.feed-lead');if(!lead||!s?.url||!s?.title)return false;
  lead.dataset.story=s.id||'';
  const mediaLink=lead.querySelector(':scope > a');if(mediaLink)mediaLink.href=s.url;
  const media=lead.querySelector('.media');if(media&&s.image){media.classList.toggle('contain',s.imageFit==='contain');const img=media.querySelector('img');if(img){img.src=s.image;img.removeAttribute('srcset');img.removeAttribute('sizes');img.alt=s.imageAlt||s.title;img.removeAttribute('width');img.removeAttribute('height');}const badge=media.querySelector('.badge');if(badge&&s.imageBadge)badge.textContent=s.imageBadge;}
  const copy=lead.querySelector('.lead-copy');if(copy){const loc=copy.querySelector('.location-line');if(loc)loc.innerHTML=`<span class="location-brand">${esc(s.location||'MERZENICH')}</span>`;const kicker=copy.querySelector('.kicker');if(kicker)kicker.textContent=s.kicker||'Aktuell';const a=copy.querySelector('h2 a');if(a){a.href=s.url;a.textContent=s.title;}const dek=copy.querySelector('.dek');if(dek)dek.textContent=s.teaser||'';const time=copy.querySelector('.meta time');if(time){time.dateTime=s.published||'';time.textContent=s.timeLabel||'';}}
  return true;
 }
 function row(s){
  if(!s?.url||!s?.title||feed.querySelector(`a[href="${CSS.escape(s.url)}"]`))return null;
  const el=document.createElement('article');el.className='feed-row editorial-current-row no-media';el.dataset.story=s.id||'';
  el.innerHTML=`<div class="feed-copy"><div class="location-line"><span class="location-brand">${esc(s.location||'MERZENICH')}</span></div><span class="kicker">${esc(s.kicker||'Aktuell')}</span><h3><a href="${esc(s.url)}">${esc(s.title)}</a></h3><p class="dek">${esc(s.teaser||'')}</p><div class="meta"><time datetime="${esc(s.published||'')}">${esc(s.timeLabel||'')}</time></div><div class="story-actions"><a class="read-more" href="${esc(s.url)}">Mehr lesen<span class="sr-only">: ${esc(s.title)}</span></a></div></div>`;
  return el;
 }
 function bumpCount(add){const c=document.querySelector('.count-line');if(!c||!add)return;const m=c.textContent.match(/^\s*(\d+)/);if(!m)return;c.firstChild.textContent=c.firstChild.textContent.replace(m[1],String(Number(m[1])+add));}
 fetch('/api/editorial-current.json',{cache:'no-store'}).then(r=>{if(!r.ok)throw new Error('editorial '+r.status);return r.json();}).then(data=>{
  let added=0;
  if(path==='/sport/'&&data.hero){updateLead(data.hero);}
  if(path==='/nachrichten/'&&data.hero){updateLead(data.hero);if(data.secondary){const second=row(data.secondary);if(second){feed.querySelector('.feed-lead')?.insertAdjacentElement('afterend',second);added++;}}}
  if(path==='/blaulicht/'&&data.secondary){const current=row(data.secondary);if(current){feed.prepend(current);added++;}}
  bumpCount(added);
 }).catch(()=>{});
})();
