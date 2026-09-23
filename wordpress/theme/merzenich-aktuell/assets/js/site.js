(()=>{const update=()=>{const d=new Date();document.querySelectorAll('[data-ma-clock]').forEach(n=>n.textContent=d.toLocaleTimeString('de-DE',{hour:'2-digit',minute:'2-digit'})+' Uhr');document.querySelectorAll('[data-ma-date]').forEach(n=>n.textContent=d.toLocaleDateString('de-DE',{weekday:'short',day:'2-digit',month:'2-digit'}));};update();setInterval(update,30000);

const b=document.querySelector('[data-ma-menu]'),n=document.querySelector('[data-ma-nav]');
if(b&&n)b.addEventListener('click',()=>{const on=n.classList.toggle('is-open');b.setAttribute('aria-expanded',on?'true':'false')});

const mega=document.querySelector('[data-ma-sport-mega]');
const sport=[...document.querySelectorAll('.nav-inner a')].find(a=>/\/sport\/?$/.test(new URL(a.href,location.href).pathname));
if(mega&&sport){
  let timer;
  sport.setAttribute('aria-haspopup','true');sport.setAttribute('aria-expanded','false');
  const open=()=>{clearTimeout(timer);mega.classList.add('is-open');mega.setAttribute('aria-hidden','false');sport.setAttribute('aria-expanded','true')};
  const close=()=>{timer=setTimeout(()=>{mega.classList.remove('is-open');mega.setAttribute('aria-hidden','true');sport.setAttribute('aria-expanded','false')},120)};
  sport.addEventListener('mouseenter',open);sport.addEventListener('focus',open);sport.addEventListener('mouseleave',close);
  mega.addEventListener('mouseenter',open);mega.addEventListener('mouseleave',close);
  mega.addEventListener('focusin',open);mega.addEventListener('focusout',e=>{if(!mega.contains(e.relatedTarget)&&e.relatedTarget!==sport)close()});
}
})();