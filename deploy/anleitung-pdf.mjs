#!/usr/bin/env node
/**
 * Merzenich Aktuell - Bedienungsanleitung als PDF.
 *
 * Liest docs/UEBERGABE-KBS.md (die Quelle der Anleitung) und schreibt
 * docs/Merzenich-Aktuell-Bedienungsanleitung-KBS.pdf: Titelseite,
 * Inhaltsverzeichnis mit Seitenzahlen, Bildschirmfotos aus
 * docs/anleitung/bilder/, Hausschriften und -farben der Seite.
 *
 * Der Markdown-Umsetzer kennt nur, was die Anleitung braucht: Überschriften,
 * Absätze, Listen (auch eingerückt und als Checkliste), Tabellen, Zitate,
 * fett, Code, Bilder und Trennlinien.
 *
 * Seitenzahlen im Inhaltsverzeichnis: erster Druck mit Platzhaltern, dann
 * liest pdftotext (poppler-utils) die Seiten, zweiter Druck mit Zahlen.
 *
 * Aufruf: NODE_PATH=$(npm root -g) node deploy/anleitung-pdf.mjs
 * Braucht Playwright mit Chromium und pdftotext.
 */
import { existsSync, readFileSync, writeFileSync, mkdtempSync, rmSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { createRequire } from 'node:module';
import { dirname, join, resolve } from 'node:path';
import { tmpdir } from 'node:os';
import { fileURLToPath, pathToFileURL } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const QUELLE = join(wurzel, 'docs', 'UEBERGABE-KBS.md');
const ZIEL = join(wurzel, 'docs', 'Merzenich-Aktuell-Bedienungsanleitung-KBS.pdf');
const FONTS = join(wurzel, 'chatgpt-site', 'assets', 'fonts');
const LOGO = join(wurzel, 'chatgpt-site', 'assets', 'img', 'logo-on-light.png');
const url = (p) => pathToFileURL(p).href;

const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
// E-Mail- und Web-Adressen nicht trennen (sonst liest man „info@kbs-“ + „management.tv“).
const adressen = (s) => s.replace(/[\w.+-]+@[\w-]+(?:\.[\w-]+)+|\b(?:www\.)?merzenich-aktuell\.de[^\s,;)“”]*/g, (m) => `<span class="u">${m}</span>`);
const inline = (s) => adressen(esc(s))
  .replace(/!\[([^\]]*)\]\(([^)]+)\)/g, (_, alt, src) => `<img src="${url(join(wurzel, 'docs', src))}" alt="${alt}">`)
  .replace(/`([^`]+)`/g, '<code>$1</code>')
  .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
const anker = (t) => 'k-' + t.toLowerCase().replace(/[^a-z0-9äöüß]+/g, '-').replace(/^-|-$/g, '');

/** Markdown → { html, kapitel: [{ ebene, text, id }] } */
export function umsetzen(md) {
  const zeilen = md.replace(/\r/g, '').split('\n');
  const aus = []; const kapitel = [];
  let i = 0;
  const istListe = (z) => /^(\s*)([-*]|\d+\.)\s+/.test(z);
  while (i < zeilen.length) {
    const z = zeilen[i];
    if (!z.trim()) { i++; continue; }
    let m;
    if ((m = z.match(/^(#{1,3})\s+(.*)$/))) {
      const ebene = m[1].length, text = m[2].trim(), id = anker(text);
      if (ebene <= 2) kapitel.push({ ebene, text, id });
      aus.push(`<h${ebene} id="${id}">${inline(text)}</h${ebene}>`); i++; continue;
    }
    if (/^---+\s*$/.test(z)) { aus.push('<hr>'); i++; continue; }
    if (z.startsWith('|')) {
      const reihen = [];
      while (i < zeilen.length && zeilen[i].startsWith('|')) reihen.push(zeilen[i++]);
      const zellen = (r) => r.replace(/^\||\|\s*$/g, '').split('|').map((c) => c.trim());
      const kopf = zellen(reihen[0]); const rumpf = reihen.slice(2).map(zellen);
      aus.push(`<table${rumpf.length <= 6 ? ' class="klein"' : ''}><thead><tr>${kopf.map((c) => `<th>${inline(c)}</th>`).join('')}</tr></thead><tbody>${rumpf.map((r) => `<tr>${r.map((c) => `<td>${inline(c)}</td>`).join('')}</tr>`).join('')}</tbody></table>`);
      continue;
    }
    if (z.startsWith('>')) {
      const block = [];
      while (i < zeilen.length && zeilen[i].startsWith('>')) block.push(zeilen[i++].replace(/^>\s?/, ''));
      const absaetze = block.join('\n').split(/\n\s*\n/).map((a) => `<p>${a.split('\n').map(inline).join('<br>')}</p>`);
      aus.push(`<blockquote>${absaetze.join('')}</blockquote>`);
      continue;
    }
    if (istListe(z)) {
      // Liste mit Einrückung: Stapel aus [Einrückung, Tag]
      const stapel = []; let html = '';
      while (i < zeilen.length && (istListe(zeilen[i]) || (zeilen[i].trim() && /^\s{2,}\S/.test(zeilen[i]) && !istListe(zeilen[i])))) {
        const zeile = zeilen[i];
        const lm = zeile.match(/^(\s*)([-*]|\d+\.)\s+(.*)$/);
        if (!lm) { html += ' ' + inline(zeile.trim()); i++; continue; }
        const tiefe = lm[1].length, tag = /\d/.test(lm[2]) ? 'ol' : 'ul';
        let text = lm[3];
        while (stapel.length && stapel[stapel.length - 1][0] > tiefe) html += `</li></${stapel.pop()[1]}>`;
        if (!stapel.length || stapel[stapel.length - 1][0] < tiefe) { stapel.push([tiefe, tag]); html += `<${tag}${/^\[[ x]\]/.test(text) ? ' class="check"' : ''}>`; }
        else html += '</li>';
        const check = text.match(/^\[([ x])\]\s+(.*)$/);
        if (check) text = check[2];
        html += `<li>${check ? `<span class="kaestchen">${check[1] === 'x' ? '✓' : ''}</span>` : ''}${inline(text)}`;
        i++;
      }
      while (stapel.length) html += `</li></${stapel.pop()[1]}>`;
      aus.push(html); continue;
    }
    // Absatz (bis Leerzeile oder Blockbeginn). Absatz nur aus Bildern: Bildreihe.
    const teile = [];
    while (i < zeilen.length && zeilen[i].trim() && !/^(#{1,3}\s|\||>|---+\s*$)/.test(zeilen[i]) && !istListe(zeilen[i])) teile.push(zeilen[i++].trim());
    const text = teile.join(' ');
    if (/^(!\[[^\]]*\]\([^)]+\)\s*)+$/.test(text)) {
      const bilder = [...text.matchAll(/!\[([^\]]*)\]\(([^)]+)\)/g)];
      aus.push(`<figure class="bilder n${bilder.length}">${bilder.map(([, alt, src]) => `<div><img src="${url(join(wurzel, 'docs', src))}" alt="${esc(alt)}"><figcaption>${esc(alt)}</figcaption></div>`).join('')}</figure>`);
    } else aus.push(`<p>${inline(text)}</p>`);
  }
  return { html: aus.join('\n'), kapitel };
}

function seite(md, seitenzahlen) {
  const titel = (md.match(/^#\s+(.*)$/m) || [, 'Bedienungsanleitung'])[1];
  const stand = (md.match(/^Stand (.*)$/m) || [, ''])[1];
  // Titelzeile und Standzeile kommen auf die Titelseite, nicht in den Text.
  const rumpf = md.replace(/^#\s+.*\n/, '').replace(/^Stand .*\n/m, '');
  const { html, kapitel } = umsetzen(rumpf);
  const toc = kapitel.map((k) => `<li class="e${k.ebene}"><a href="#${k.id}"><span>${inline(k.text)}</span><b>${seitenzahlen?.[k.id] ?? '00'}</b></a></li>`).join('');
  return `<!doctype html><html lang="de"><head><meta charset="utf-8"><title>${esc(titel)}</title><style>
@font-face{font-family:'Hanken Grotesk';src:url('${url(join(FONTS, 'hanken-grotesk-latin-wght-normal.woff2'))}') format('woff2');font-weight:100 900;font-style:normal}
@font-face{font-family:'Hanken Grotesk';src:url('${url(join(FONTS, 'hanken-grotesk-latin-wght-italic.woff2'))}') format('woff2');font-weight:100 900;font-style:italic}
@font-face{font-family:'Newsreader';src:url('${url(join(FONTS, 'newsreader-latin-wght-normal.woff2'))}') format('woff2');font-weight:200 800}
:root{--rot:#971725;--tinte:#151412;--grau:#5b5853;--linie:#e3dfd8;--papier:#fbfbfa}
@page{size:A4;margin:20mm 18mm 22mm 18mm}
*{box-sizing:border-box}
body{margin:0;font-family:'Hanken Grotesk',Arial,sans-serif;font-size:10.5pt;line-height:1.5;color:var(--tinte);hyphens:auto;-webkit-hyphens:auto}
h1,h2,h3{font-family:'Newsreader',Georgia,serif;font-weight:600;line-height:1.2;break-after:avoid;page-break-after:avoid}
h1{font-size:24pt;color:var(--rot);margin:0 0 12pt;padding-bottom:8pt;border-bottom:2pt solid var(--rot);break-before:page;page-break-before:always}
h2{font-size:15pt;margin:20pt 0 6pt}
h3{font-size:12pt;margin:14pt 0 4pt}
p{margin:0 0 7pt}
ul,ol{margin:0 0 8pt;padding-left:16pt}
li{margin:2pt 0}
ul.check{list-style:none;padding-left:2pt}
.kaestchen{display:inline-block;width:10pt;height:10pt;border:1pt solid var(--tinte);margin-right:6pt;vertical-align:-1pt;font-size:8pt;line-height:9pt;text-align:center}
strong{font-weight:700}
code{font-family:'DejaVu Sans Mono',monospace;font-size:9pt;background:#f1eee9;padding:0 2pt;border-radius:2pt}
table{width:100%;border-collapse:collapse;margin:6pt 0 12pt;font-size:9.5pt;break-inside:auto}
tr{break-inside:avoid;page-break-inside:avoid}
table.klein{break-inside:avoid;page-break-inside:avoid}
.u{white-space:nowrap;hyphens:none}
h2+table,h2+p,h3+p{break-before:avoid}
th{text-align:left;background:#f4efe8;border-bottom:1.5pt solid var(--rot);padding:5pt 6pt;font-weight:700}
td{border-bottom:.6pt solid var(--linie);padding:5pt 6pt;vertical-align:top}
blockquote{margin:6pt 0 12pt;padding:8pt 12pt;border-left:3pt solid var(--rot);background:#f7f4ef;font-size:9.8pt;break-inside:avoid}
blockquote p{margin:0 0 6pt}
hr{display:none}
img{max-width:100%}
figure.bilder{margin:8pt 0 12pt;display:flex;gap:10pt;align-items:flex-start;break-inside:avoid;page-break-inside:avoid}
figure.bilder>div{flex:1;min-width:0}
figure.bilder img{display:block;max-height:150mm;max-width:100%;width:auto;margin:0 auto;border:.6pt solid #cfc9c0;border-radius:3pt;box-shadow:0 1pt 3pt rgba(0,0,0,.08)}
figure.bilder.n2 img{max-height:120mm}
figcaption{font-size:8.5pt;color:var(--grau);margin-top:4pt;text-align:center}
.titel{height:250mm;display:flex;flex-direction:column;justify-content:space-between;padding:10mm 0}
.titel img{width:70mm}
.titel .gross{font-family:'Newsreader',Georgia,serif;font-size:34pt;line-height:1.1;color:var(--tinte);margin:0}
.titel .rot{color:var(--rot)}
.titel .unter{font-size:13pt;color:var(--grau);margin-top:10pt}
.titel .fuss{font-size:10pt;color:var(--grau);border-top:1pt solid var(--linie);padding-top:8pt}
.toc{break-before:page;page-break-before:always;break-after:page;page-break-after:always}
p:has(>strong:only-child),p:has(+blockquote),p:has(+figure){break-after:avoid;page-break-after:avoid}
.toc h2{font-size:20pt;color:var(--rot);margin-top:0}
.toc ol{list-style:none;padding:0;margin:0}
.toc li{margin:0}
.toc a{display:flex;color:inherit;text-decoration:none;gap:6pt;align-items:baseline}
.toc a span{flex:1;border-bottom:.6pt dotted #b9b2a7}
.toc li.e1{font-family:'Newsreader',Georgia,serif;font-weight:600;font-size:12pt;margin-top:9pt}
.toc li.e2{font-size:10pt;padding-left:12pt;margin-top:2.5pt}
.toc b{font-weight:600;font-variant-numeric:tabular-nums;min-width:16pt;text-align:right}
a{color:var(--rot)}
</style></head><body>
<section class="titel"><div><img src="${url(LOGO)}" alt="Merzenich Aktuell"></div>
<div><p class="gross">Bedienungsanleitung<br><span class="rot">für die Redaktion</span></p><p class="unter">So arbeiten Sie mit dem Backend von merzenich-aktuell.de: freigeben, schreiben, anordnen, Anzeigen und Konten.</p></div>
<div class="fuss">${esc(stand)}</div></section>
<nav class="toc"><h2>Inhalt</h2><ol>${toc}</ol></nav>
${html}
</body></html>`;
}

async function drucken(browser, html, pfad) {
  const tmp = mkdtempSync(join(tmpdir(), 'anleitung-'));
  const datei = join(tmp, 'anleitung.html');
  writeFileSync(datei, html);
  const page = await browser.newPage();
  await page.goto(url(datei), { waitUntil: 'load' });
  await page.evaluate(() => document.fonts.ready);
  const fuss = `<div style="width:100%;font-family:Arial,sans-serif;font-size:7.5pt;color:#7a766f;padding:0 18mm;display:flex;justify-content:space-between"><span>Merzenich Aktuell · Bedienungsanleitung für die Redaktion</span><span>Seite <span class="pageNumber"></span> von <span class="totalPages"></span></span></div>`;
  await page.pdf({ path: pfad, format: 'A4', printBackground: true, displayHeaderFooter: true, headerTemplate: '<span></span>', footerTemplate: fuss, margin: { top: '20mm', bottom: '22mm', left: '18mm', right: '18mm' }, outline: true, tagged: true });
  await page.close();
  rmSync(tmp, { recursive: true, force: true });
}

/** Seite je Kapitel aus dem gedruckten PDF (erste Fundstelle nach dem Inhaltsverzeichnis). */
function seitenLesen(pdf, kapitel) {
  const seiten = execFileSync('pdftotext', ['-layout', pdf, '-'], { encoding: 'utf8', maxBuffer: 64 * 1024 * 1024 }).split('\f');
  const norm = (s) => s.replace(/\s+/g, ' ').trim();
  // Im Probedruck enden die Zeilen des Inhaltsverzeichnisses auf „00“; der Text beginnt danach.
  const istToc = (s) => s.split('\n').filter((z) => /\s00\s*$/.test(z)).length >= 3;
  let ab = 1; while (ab < seiten.length && (istToc(seiten[ab]) || ab === 1)) ab++;
  const aus = {};
  for (const k of kapitel) {
    const t = norm(k.text.replace(/\*\*/g, '').replace(/`/g, ''));
    for (let n = ab; n < seiten.length; n++) {
      if (seiten[n].split('\n').some((z) => norm(z).startsWith(t))) { aus[k.id] = n + 1; break; }
    }
  }
  return aus;
}

async function main() {
  const require = createRequire(import.meta.url);
  let chromium;
  try { ({ chromium } = require('playwright')); } catch { console.error('Playwright fehlt: NODE_PATH=$(npm root -g) setzen oder playwright installieren.'); process.exit(2); }
  const md = readFileSync(QUELLE, 'utf8');
  const { kapitel } = umsetzen(md.replace(/^#\s+.*\n/, '').replace(/^Stand .*\n/m, ''));
  const exe = existsSync('/opt/pw-browsers/chromium') ? '/opt/pw-browsers/chromium' : undefined;
  const browser = await chromium.launch({ executablePath: exe });
  const probe = join(mkdtempSync(join(tmpdir(), 'anleitung-probe-')), 'probe.pdf');
  await drucken(browser, seite(md, null), probe);
  const nummern = seitenLesen(probe, kapitel);
  const fehlen = kapitel.filter((k) => !nummern[k.id]).map((k) => k.text);
  await drucken(browser, seite(md, nummern), ZIEL);
  await browser.close();
  const n = execFileSync('pdfinfo', [ZIEL], { encoding: 'utf8' }).match(/Pages:\s+(\d+)/)?.[1];
  console.log(`Anleitung: ${ZIEL.replace(wurzel + '/', '')}, ${n} Seiten, ${kapitel.length} Kapitel im Inhaltsverzeichnis${fehlen.length ? `; ohne Seitenzahl: ${fehlen.join(', ')}` : ''}.`);
  if (fehlen.length) process.exitCode = 1;
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) await main();
