#!/usr/bin/env node
/**
 * P0-Synchronisierung für den statisch ausgelieferten Stand.
 * Quelle der Wahrheit sind die tatsächlich vorhandenen Artikelseiten in
 * chatgpt-site/<ressort>/<slug>/. Daraus werden Ressort-, Orts-, Archiv- und
 * Such-/Themenansichten beim Coolify-Build nachgezogen.
 */
import { readFileSync, writeFileSync, existsSync, readdirSync, statSync, rmSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { artikelSammeln, esc, dmyLang } from './lib-artikel.mjs';

const wurzel=resolve(dirname(fileURLToPath(import.meta.url)),'..');
const site=join(wurzel,'chatgpt-site');
const artikel=artikelSammeln(site);
const ressorts=['nachrichten','blaulicht','sport','rathaus','leben','wirtschaft','menschen','vereine'];
const orte=[['merzenich','MERZENICH'],['golzheim','GOLZHEIM'],['girbelsrath','GIRBELSRATH'],['morschenich','MORSCHENICH'],['buergewald','BÜRGEWALD']];
let changed=0;

function lesen(rel){const p=join(site,rel);return existsSync(p)?readFileSync(p,'utf8'):null}
function schreiben(rel,html){writeFileSync(join(site,rel),html);changed++}
function zeile(a){
  const dt=a.datum||'';
  return `<article data-story="${esc(a.id)}" class="feed-row no-media editorial-synced"><div class="feed-copy"><div class="location-line"><span class="location-brand">${esc(a.ort||'MERZENICH')}</span></div><span class="kicker">${esc(a.ressortLabel)}</span><h3><a href="${esc(a.url)}">${esc(a.titel)}</a></h3><p class="dek">${esc(a.teaser)}</p><div class="meta"><time datetime="${esc(dt)}">${esc(a.zeitLabel||dmyLang(dt))}</time></div><div class="story-actions"><a class="read-more" href="${esc(a.url)}">Mehr lesen<span class="sr-only">: ${esc(a.titel)}</span></a></div></div></article>`;
}
function setCount(html,n){
  return html.replace(/(<p class="count-line">)\d+(\s+Meldung(?:en)?)/,`$1${n}$2`).replace(/(<p class="count-line">)\d+(\s+Meldung)/,`$1${n}$2`);
}
function syncFeed(rel,wanted){
  let html=lesen(rel); if(!html)return;
  const missing=wanted.filter(a=>!html.includes(`href="${a.url}"`));
  if(!missing.length){const counted=setCount(html,wanted.length);if(counted!==html)schreiben(rel,counted);return}
  const marker='<div class="feed">';
  const at=html.indexOf(marker);
  if(at<0){console.warn(`content-sync: kein Feed in ${rel}`);return}
  const pos=at+marker.length;
  html=html.slice(0,pos)+'\n      '+missing.map(zeile).join('\n      ')+html.slice(pos);
  html=setCount(html,wanted.length);
  schreiben(rel,html);
  console.log(`content-sync: ${rel} +${missing.length}`);
}

// Alle Meldungen und Ressorts.
syncFeed('nachrichten/index.html',artikel);
for(const r of ressorts.filter(x=>x!=='nachrichten')) syncFeed(`${r}/index.html`,artikel.filter(a=>a.ressort===r));

// Ortsseiten. Kombinierte Ortszeilen (z. B. MERZENICH · GIRBELSRATH) werden
// bewusst beiden passenden Ortskanälen zugeordnet.
for(const [slug,label] of orte) syncFeed(`${slug}/index.html`,artikel.filter(a=>(a.ort||'').toLocaleUpperCase('de-DE').includes(label)));

// Archiv komplett aus dem gleichen Artikelbestand ergänzen.
{
  const rel='archiv/index.html'; let html=lesen(rel);
  if(html){
    const months=new Map();
    for(const a of artikel){
      if(!a.datum)continue; const d=new Date(a.datum); if(Number.isNaN(d.getTime()))continue;
      const y=String(d.getFullYear()), m=String(d.getMonth()+1).padStart(2,'0'), key=`${y}-${m}`;
      if(!months.has(key))months.set(key,[]); months.get(key).push(a);
    }
    for(const [key,list] of months){
      const id=`id="${key}"`, blockStart=html.indexOf(id);
      const rows=list.filter(a=>!html.includes(`href="${a.url}"`)).map(a=>`<li class="editorial-synced"><time datetime="${esc(a.datum)}">${esc(new Intl.DateTimeFormat('de-DE',{day:'2-digit',month:'2-digit',timeZone:'Europe/Berlin'}).format(new Date(a.datum)))}.</time><a href="${esc(a.url)}">${esc(a.titel)}</a><span class="rs">${esc(a.ressortLabel)}</span></li>`).join('');
      if(!rows)continue;
      if(blockStart>=0){
        const ul=html.indexOf('<ul class="archive-list">',blockStart); if(ul>=0){const p=ul+'<ul class="archive-list">'.length;html=html.slice(0,p)+rows+html.slice(p)}
      } else {
        const d=new Date(`${key}-01T12:00:00+02:00`); const monthName=new Intl.DateTimeFormat('de-DE',{month:'long',year:'numeric',timeZone:'Europe/Berlin'}).format(d);
        const year=key.slice(0,4), yearMarker=`<div class="archive-year"><h2>${year}</h2>`;
        const block=`<div class="archive-month" id="${key}"><h3>${esc(monthName)}<small>${list.length} Meldungen</small></h3><ul class="archive-list">${rows}</ul></div>`;
        const yi=html.indexOf(yearMarker); if(yi>=0){const p=yi+yearMarker.length;html=html.slice(0,p)+block+html.slice(p)}
      }
    }
    html=html.replace(/<p class="count-line">\d+ Meldungen/,`<p class="count-line">${artikel.length} Meldungen`);
    for(const [key,list] of months){
      const start=html.indexOf(`id="${key}"`); if(start<0)continue; const end=html.indexOf('</div>',start); if(end<0)continue;
      const part=html.slice(start,end); const n=new Set([...part.matchAll(/href="(\/(?:nachrichten|blaulicht|sport|rathaus|leben|wirtschaft|menschen|vereine)\/[^\"]+\/)"/g)].map(m=>m[1])).size;
      if(n) html=html.slice(0,start)+part.replace(/<small>\d+ Meldungen?<\/small>/,`<small>${n} Meldung${n===1?'':'en'}</small>`)+html.slice(end);
    }
    schreiben(rel,html);
  }
}

// Release-Texte aus dem Prototyp entfernen. Keine nicht bestätigte natürliche
// Person für §18 MStV erfinden; dieses Feld bleibt bewusst als Prüfpunkt bestehen.
function replaceIn(rel,replacements){
  let html=lesen(rel); if(!html)return; const before=html;
  for(const [a,b] of replacements) html=html.split(a).join(b);
  if(html!==before)schreiben(rel,html);
}
replaceIn('datenschutz/index.html',[
  ['Diese Paketfassung ist für Netlify vorgesehen. Der Betreiber muss die konkrete Hostinggesellschaft, Vertragsdaten und Speicherfristen seiner Installation ergänzen.','Merzenich Aktuell wird in der aktuellen Produktionsumgebung über einen nginx-Webserver in einer Coolify-Installation ausgeliefert. Beim Abruf verarbeitet die technische Infrastruktur die für die Auslieferung und Absicherung erforderlichen Verbindungsdaten.'],
  ['Der Betreiber muss die konkreten Speicherfristen, Auftragsverarbeitungsverträge und gegebenenfalls Drittlandgarantien anhand seines Hostingvertrags vor dem öffentlichen Start ergänzen.','Server- und Proxy-Protokolle werden ausschließlich für Betrieb, Sicherheit und Fehleranalyse verwendet. Details zu Aufbewahrung und eingesetzten Infrastruktur-Dienstleistern sind von der verantwortlichen Stelle anhand der produktiven Serverkonfiguration zu dokumentieren und regelmäßig zu prüfen.']
]);
replaceIn('unterstuetzen/index.html',[
  ['vor dem Livegang festgelegt','vor einer Aktivierung transparent ausgewiesen'],
  ['vor dem Livegang','vor einer Aktivierung']
]);

// Interne Projektformulierungen auf sämtlichen öffentlichen Seiten entfernen.
(function cleanPublic(){
  const files=[]; (function walk(d){for(const e of readdirSync(d)){const p=join(d,e);const s=statSync(p);s.isDirectory()?walk(p):p.endsWith('.html')&&files.push(p)}})(site);
  const needle='Namentlich gezeichnete Beiträge folgen, sobald das Team steht.';
  for(const p of files){let h=readFileSync(p,'utf8');if(!h.includes(needle))continue;h=h.split(needle).join('Beiträge werden redaktionell geprüft und transparent gekennzeichnet.');writeFileSync(p,h);changed++}
})();

// /admin ist in Coolify ohne Identity nicht funktionsfähig und darf nicht als
// öffentliches Redaktionshandbuch ausgeliefert werden.
const admin=join(site,'admin'); if(existsSync(admin)){rmSync(admin,{recursive:true,force:true});changed++;console.log('content-sync: /admin aus öffentlichem Build entfernt')}

console.log(`content-sync: ${artikel.length} Artikelseiten geprüft, ${changed} Dateien/Strukturen aktualisiert.`);
