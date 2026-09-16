(() => {
 'use strict';
 const $=s=>document.querySelector(s),$$=s=>[...document.querySelectorAll(s)];
 const berlin=new Intl.DateTimeFormat('de-DE',{timeZone:'Europe/Berlin',weekday:'long',day:'numeric',month:'long',year:'numeric'});
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
 // Enhancement is optional: no JS, reduced motion, print, or missing observer leave content visible.
 if('IntersectionObserver' in window&&!matchMedia('(prefers-reduced-motion: reduce)').matches){const observer=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.remove('reveal-pending');e.target.classList.add('reveal-visible');observer.unobserve(e.target);}}),{threshold:0,rootMargin:'0px 0px 24px 0px'});$$('.desk-heading,.feed-row,.agenda-row').forEach((el,i)=>{if(el.getBoundingClientRect().top>innerHeight+80){el.classList.add('reveal-pending');el.style.transitionDelay=(i%3)*40+'ms';observer.observe(el);}});addEventListener('beforeprint',()=>$$('.reveal-pending').forEach(el=>el.classList.remove('reveal-pending')));}
})();

// Berlin date/clock, semantic details and overflow navigation; no client weather provider.
(()=>{
 const clock=()=>{const d=new Date();document.querySelectorAll('[data-clock]').forEach(el=>{el.textContent=new Intl.DateTimeFormat('de-DE',{timeZone:'Europe/Berlin',hour:'2-digit',minute:'2-digit'}).format(d)+' Uhr';el.dateTime=d.toISOString();});document.querySelectorAll('[data-today]').forEach(el=>{el.textContent=new Intl.DateTimeFormat('de-DE',{timeZone:'Europe/Berlin',weekday:'short',day:'2-digit',month:'2-digit',year:'numeric'}).format(d);el.dateTime=d.toISOString();});};clock();setInterval(clock,30000);
 document.querySelectorAll('.service-accordion,.nav-more').forEach((el,i)=>{const summary=el.querySelector('summary'),body=summary?.nextElementSibling;if(!summary||!body)return;body.id=body.id||'expand-content-'+i;summary.setAttribute('aria-controls',body.id);const update=()=>summary.setAttribute('aria-expanded',String(el.open));el.addEventListener('toggle',update);update();});
 const nav=document.querySelector('.mainnav .navscroll'),more=document.querySelector('.nav-more .more-links');if(nav&&more){const links=[...nav.children];const fit=()=>{links.forEach(el=>nav.append(el));if(innerWidth<768)return;let guard=0;while(nav.scrollWidth>nav.clientWidth+1&&nav.children.length>1&&guard++<20){more.prepend(nav.lastElementChild);}};new ResizeObserver(fit).observe(nav);fit();}
 document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.nav-more[open]').forEach(el=>{el.open=false;el.querySelector('summary').focus();});});
 document.addEventListener('click',e=>document.querySelectorAll('.nav-more[open]').forEach(el=>{if(!el.contains(e.target))el.open=false;}));
})();
