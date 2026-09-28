#!/usr/bin/env node
import { readFileSync, writeFileSync } from 'node:fs';
import { resolve, dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root=resolve(dirname(fileURLToPath(import.meta.url)),'..');
const dataPath=join(root,'traueranzeigen.json');
const pagePath=join(root,'chatgpt-site','traueranzeigen','index.html');
const data=JSON.parse(readFileSync(dataPath,'utf8'));
const esc=(s)=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const safe=(u)=>/^https?:\/\//i.test(String(u||''))?String(u):'';

async function sourceImage(item){
  try{
    const r=await fetch(item.sourceUrl,{headers:{'User-Agent':'MerzenichAktuell-Trauer/1.0 (+https://merzenichaktuell.hk-growthoperator.de)','Accept':'text/html','Accept-Language':'de-DE,de;q=0.9'},redirect:'follow',signal:AbortSignal.timeout(15000)});
    if(!r.ok)return '';
    const h=await r.text();
    const imgs=[...h.matchAll(/<img\b[^>]*(?:src|data-src)=["']([^"']+)["'][^>]*>/gi)].map(m=>m[1]);
    const media=imgs.find(u=>/MEDIASERVER\/content\//i.test(u));
    if(media)return new URL(media,r.url).href;
    for(const tag of h.match(/<meta\b[^>]*>/gi)||[]){
      const prop=/(?:property|name)=["'](?:og:image|twitter:image)["']/i.test(tag);
      const m=/content=["']([^"']+)["']/i.exec(tag);
      if(prop&&m&&!/logo|placeholder|favicon/i.test(m[1]))return new URL(m[1],r.url).href;
    }
  }catch{}
  return '';
}

for(const item of data.notices||[]){
  if(!item.verifiedLocal)continue;
  const found=await sourceImage(item);
  if(found){
    item.imageUrl=found;
    item.imageCredit=`Bild: ${item.sourceName} · Originalanzeige`;
    item.imageCheckedAt=new Date().toISOString();
  }
}
writeFileSync(dataPath,JSON.stringify(data,null,2)+'\n');

const fallback='/assets/symbolbilder/trauer/trauer-01-kerze.svg';
function card(i){
  const img=safe(i.imageUrl)||fallback;
  const original=!!safe(i.imageUrl);
  const credit=original?(i.imageCredit||`Bild: ${i.sourceName} · Originalanzeige`):'Symbolbild · Merzenich Aktuell';
  return `<article class="news-card"><a class="media contain" href="${esc(safe(i.sourceUrl))}" target="_blank" rel="noopener noreferrer nofollow"><img src="${esc(img)}" alt="" loading="lazy" decoding="async"${original?' referrerpolicy="no-referrer"':''} style="object-fit:contain;background:#f3f1ed"><span class="badge">${esc(credit)}</span></a><div class="news-card-body"><span class="eyebrow">Traueranzeige · Ortsbezug geprüft</span><h3><a href="${esc(safe(i.sourceUrl))}" target="_blank" rel="noopener noreferrer nofollow">${esc(i.name)}</a></h3><p class="meta">* ${esc(i.born)} · † ${esc(i.died)} · veröffentlicht ${esc(i.published)}</p><p>Quelle: ${esc(i.sourceName)}</p><p><a href="${esc(safe(i.sourceUrl))}" target="_blank" rel="noopener noreferrer nofollow">Originalanzeige öffnen</a></p></div></article>`;
}
const notices=(data.notices||[]).filter(i=>i.verifiedLocal).sort((a,b)=>String(b.published).localeCompare(String(a.published)));
const body=notices.length
 ? `<div class="info-prose"><p>Hier erscheinen nur Trauerfälle, deren Ortsbezug zur Gemeinde redaktionell geprüft wurde. Das Bild stammt – soweit technisch verfügbar – direkt aus der verlinkten Originalanzeige und ist im Bild gekennzeichnet.</p></div><div class="cards-3">${notices.map(card).join('')}</div><div class="info-prose"><p><a href="/anzeigen/aufgeben/">Traueranzeige aufgeben</a></p><p class="muted">Externe Suchtreffer werden nicht allein wegen des Suchworts „Merzenich“ veröffentlicht.</p></div>`
 : '<div class="info-prose"><p>Zurzeit liegen keine redaktionell verifizierten Traueranzeigen vor.</p><p><a href="/anzeigen/aufgeben/">Traueranzeige aufgeben</a></p></div>';

let page=readFileSync(pagePath,'utf8');
const a='<!-- trauer:liste:start -->',b='<!-- trauer:liste:end -->';
if(!page.includes(a)||!page.includes(b))throw new Error('Trauer-Marker fehlen');
page=page.replace(new RegExp(a+'[\\s\\S]*?'+b),a+body+b);
writeFileSync(pagePath,page);
console.log(`Traueranzeigen: ${notices.length} verifizierte Eintraege gerendert.`);
