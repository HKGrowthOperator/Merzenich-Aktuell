#!/usr/bin/env node
/**
 * Produktionsbereinigung nach dem statischen Generator.
 * Der historische Generator kann weiterhin CMS-Arbeitsdateien erzeugen; ein
 * oeffentlicher Release darf /admin/ und den Link dorthin aber nicht enthalten.
 */
import { existsSync, readdirSync, readFileSync, rmSync, statSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';

const dist=resolve('dist');
if(!existsSync(dist)) throw new Error('dist fehlt – zuerst build.mjs ausfuehren');

const admin=join(dist,'admin');
if(existsSync(admin)) rmSync(admin,{recursive:true,force:true});

const files=[];
(function walk(dir){
  for(const name of readdirSync(dir)){
    const p=join(dir,name), s=statSync(p);
    if(s.isDirectory()) walk(p); else if(p.endsWith('.html')) files.push(p);
  }
})(dist);

let changed=0;
for(const p of files){
  const before=readFileSync(p,'utf8');
  let html=before
    .replace(/<p>\s*<a href="\/admin\/"[^>]*>[^<]*<\/a>\s*<\/p>/g,'')
    .replace(/<a href="\/admin\/"[^>]*>Redaktion anmelden<\/a>/g,'')
    .replace(/Namentlich gezeichnete Beiträge folgen, sobald das Team steht\./g,'Beiträge werden redaktionell geprüft und transparent gekennzeichnet.');
  if(html!==before){writeFileSync(p,html);changed++}
}
console.log(`release-cleanup: /admin entfernt, ${changed} oeffentliche HTML-Dateien bereinigt.`);
