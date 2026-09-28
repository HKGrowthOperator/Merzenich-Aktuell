#!/usr/bin/env node
/**
 * Sichtpruefung ueber Breiten (Audit 28.09.2026): jede Seitenart bei 320, 390,
 * 768, 1024 und 1440 px im echten Browser.
 *   - kein horizontaler Seiten-Overflow (Scrollen nur in dafuer gebauten
 *     Bereichen wie Ressortleiste oder Tabelle)
 *   - genau ein H1, Sprunglink zum Inhalt, jedes Bild mit alt
 * Routen: je Seitentyp bis zu vier aus qa/routen.json plus alle Hauptseiten.
 *   node qa/sicht/breiten.mjs [basis-url]      (Standard http://127.0.0.1:4173)
 * Lokal ohne Playwright-Browser-Download: PW_CHROMIUM=/pfad/zu/chrome setzen.
 */
import { chromium } from 'playwright';
import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const WURZEL = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const BASIS = (process.argv[2] || 'http://127.0.0.1:4173').replace(/\/$/, '');
const matrix = JSON.parse(readFileSync(join(WURZEL, 'qa', 'routen.json'), 'utf8')).matrix;
const routen = new Set(['/', '/nachrichten/', '/blaulicht/', '/sport/', '/termine/', '/rathaus/', '/leben/', '/wirtschaft/', '/vereine/', '/tipp/', '/menschen/',
  '/merzenich/', '/golzheim/', '/girbelsrath/', '/morschenich/', '/buergewald/', '/anzeigen/', '/anzeigen/aufgeben/', '/immobilien/', '/jobs/', '/unternehmen/', '/betriebe/',
  '/traueranzeigen/', '/familienanzeigen/', '/service/', '/suche/', '/werben/', '/diskussion/', '/archiv/', '/sc-1919-merzenich/', '/impressum/', '/datenschutz/', '/whatsapp/', '/kontakt/']);
const nachTyp = {};
for (const z of matrix) (nachTyp[z.typ] ||= []).push(z.route);
for (const liste of Object.values(nachTyp)) for (const r of liste.slice(0, 4)) if (!/\.html$/.test(r)) routen.add(r);

const browser = await chromium.launch(process.env.PW_CHROMIUM ? { executablePath: process.env.PW_CHROMIUM } : {});
const fehler = [];
for (const breite of [320, 390, 768, 1024, 1440]) {
  const ctx = await browser.newContext({ viewport: { width: breite, height: 900 } });
  const page = await ctx.newPage();
  for (const r of routen) {
    try {
      const antwort = await page.goto(BASIS + r, { waitUntil: 'load', timeout: 30000 });
      if (!antwort || antwort.status() >= 400) { fehler.push(`${breite}px ${r}: HTTP ${antwort && antwort.status()}`); continue; }
      await page.waitForTimeout(200);
      const e = await page.evaluate(() => {
        const cw = document.documentElement.clientWidth;
        const ueber = document.documentElement.scrollWidth - cw;
        let taeter = '';
        if (ueber > 1) for (const el of document.querySelectorAll('body *')) {
          const rc = el.getBoundingClientRect(); if (rc.right <= cw + 1 || !rc.width || getComputedStyle(el).position === 'fixed') continue;
          let x = el.parentElement, geschnitten = false;
          while (x && x !== document.body) { if (/(hidden|auto|scroll|clip)/.test(getComputedStyle(x).overflowX)) { geschnitten = true; break; } x = x.parentElement; }
          if (!geschnitten) { taeter = `${el.tagName.toLowerCase()}.${String(el.className).split(' ')[0]} "${el.textContent.trim().slice(0, 30)}"`; break; }
        }
        return {
          ueber, taeter,
          h1: document.querySelectorAll('h1').length,
          sprung: !!document.querySelector('a[href="#main"], a.skip, a.skip-link'),
          ohneAlt: [...document.querySelectorAll('main img')].filter((i) => !i.hasAttribute('alt')).length,
        };
      });
      if (e.ueber > 1) fehler.push(`${breite}px ${r}: ${e.ueber}px horizontaler Overflow durch ${e.taeter}`);
      if (breite === 1440) {
        if (e.h1 !== 1 && r !== '/') fehler.push(`${r}: ${e.h1} H1`);
        if (!e.sprung) fehler.push(`${r}: kein Sprunglink zum Inhalt`);
        if (e.ohneAlt) fehler.push(`${r}: ${e.ohneAlt} Bild(er) ohne alt`);
      }
    } catch (err) { fehler.push(`${breite}px ${r}: ${err.message.split('\n')[0]}`); }
  }
  await ctx.close();
}
await browser.close();
console.log(`${routen.size} Routen bei 5 Breiten geprueft.`);
for (const f of fehler) console.log('FEHLER ' + f);
process.exit(fehler.length ? 1 : 0);
