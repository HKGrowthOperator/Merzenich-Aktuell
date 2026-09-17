#!/usr/bin/env node
/**
 * Bindet Darstellung (theme.css/theme.js), einklappenden Kopf (kopf.js) und
 * Kommentare (kommentare.js) in alle Seiten von chatgpt-site/ ein, die
 * korrekturen.css laden, und setzt den Link zur Diskussion ins Mehr-Menue.
 * Idempotent: was schon drin ist, wird nicht doppelt eingefuegt.
 * Aufruf: node deploy/kopf-theme-einbinden.mjs [--check]
 *
 * --check prueft absichtlich die STRUKTUR (Assets/Loader vorhanden), nicht
 * Cache-Busting-Hashes. Die Hashes werden im echten Coolify-Build unmittelbar
 * vor der Auslieferung aktualisiert; sonst wuerde jede legitime Asset-Aenderung
 * den QA-Lauf vor dem Docker-Build stoppen.
 */
import { readFileSync, writeFileSync, readdirSync, statSync, existsSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { bildUrl } from './symbolbilder.mjs';
const esc = (t) => String(t ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const V = 'v=20260917b';
// Werbung global aus (Entscheidung 17.09.): html[data-werbung="aus"] blendet alle Anzeigenflaechen aus.
const WERBUNG_AN = false;
// Assets werden ein Jahr 'immutable' gecacht - deshalb traegt jeder lokale
// Asset-Link einen Inhalts-Hash. Aendert sich die Datei, aendert sich die URL.
const hashCache = new Map();
function assetHash(pfadImWeb) {
  if (hashCache.has(pfadImWeb)) return hashCache.get(pfadImWeb);
  const datei = join(wurzel, 'chatgpt-site', pfadImWeb.replace(/^\//, ''));
  const h = existsSync(datei) ? createHash('sha256').update(readFileSync(datei)).digest('hex').slice(0, 10) : null;
  hashCache.set(pfadImWeb, h); return h;
}
function versioniere(html) {
  return html.replace(/(<(?:link|script)\b[^>]*?\b(?:href|src)=")(\/assets\/[^"?]+\.(?:css|js))(?:\?[^"]*)?(")/g, (m, a, pfad, z) => { const h = assetHash(pfad); return h ? `${a}${pfad}?v=${h}${z}` : m; });
}
const CSS_ANKER = /<link rel="stylesheet" href="\/assets\/korrekturen\.css[^"]*">/;
const JS_ANKER = /<script src="\/assets\/v20\.js[^"]*" defer><\/script>/;
const INLINE = '<script>try{document.documentElement.dataset.theme=localStorage.getItem("merzenich-theme")==="dark"?"dark":"light"}catch(e){document.documentElement.dataset.theme="light"}</script>';
const CSS = `<link rel="stylesheet" href="/assets/theme.css?${V}">`;
const JS = `<script src="/assets/kopf.js?${V}" defer></script><script src="/assets/theme.js?${V}" defer></script>`;
const KOMMENTARE = `<script src="/assets/kommentare.js?${V}" defer></script>`;
const EINWILLIGUNG = `<script src="/assets/einwilligung.js?${V}" defer></script><script src="/assets/werbefrei.js?${V}" defer></script>`;
const LINK_WERBEFREI = '<a href="/werbefrei/">Werbefrei lesen</a>';
const LINK_MEHR = '<a href="/kontakt/">Kontakt</a>';
const LINK_DISKUSSION = '<a href="/diskussion/">Diskussion</a>';

const seiten = [];
(function lauf(d) { for (const e of readdirSync(d)) { const p = join(d, e); statSync(p).isDirectory() ? lauf(p) : p.endsWith('.html') && seiten.push(p); } })(join(wurzel, 'chatgpt-site'));

let geaendert = 0, uebersprungen = 0, fehler = 0;
// Auch die Loader in kopf.js (bild-fallbacks, homepage-polish, Editorial-Audit)
// per Hash versionieren. Im --check-Modus werden Cache-Hashes nicht bewertet;
// die Struktur wird weiter unten geprueft.
{
  const kp = join(wurzel, 'chatgpt-site', 'assets', 'kopf.js');
  const alt = readFileSync(kp, 'utf8');
  const neu = alt.replace(/(['"])(\/assets\/[a-z0-9-]+\.(?:js|css))\?v=[^'"]*\1/g, (m, q, pfad) => { const h = assetHash(pfad); return h ? `${q}${pfad}?v=${h}${q}` : m; });
  if (neu !== alt && !nurPruefen) { geaendert++; writeFileSync(kp, neu); hashCache.delete('/assets/kopf.js'); }
  // Der aktuelle globale QA-Layer muss vom zentralen Kopf geladen werden.
  if (!alt.includes('/assets/editorial-audit.css') || !alt.includes('/assets/editorial-audit.js')) {
    fehler++; console.error('kopf.js: Editorial-Audit-Layer fehlt');
  }
}
// Service Worker: VERSION aus dem Inhalt aller Assets ableiten. Der SW cacht
// CSS/JS ohne Query-String (ignoreSearch); nur ein neuer VERSION-Wert leert
// diesen Cache. Im reinen Strukturcheck wird die Versionsdifferenz ignoriert.
{
  const sw = join(wurzel, 'chatgpt-site', 'sw.js');
  const assetsDir = join(wurzel, 'chatgpt-site', 'assets');
  const dateien = readdirSync(assetsDir).filter((f) => /\.(?:css|js)$/.test(f)).sort();
  const gesamt = createHash('sha256');
  for (const f of dateien) gesamt.update(f).update('\0').update(readFileSync(join(assetsDir, f)));
  const version = 'a-' + gesamt.digest('hex').slice(0, 12);
  const alt = readFileSync(sw, 'utf8');
  const neu = alt.replace(/^const VERSION = '[^']*';/m, `const VERSION = '${version}';`);
  if (neu === alt && !alt.includes(`'${version}'`) && !/^const VERSION = '[^']*';/m.test(alt)) { fehler++; console.error('sw.js: keine Zeile "const VERSION = \'...\';" gefunden'); }
  else if (neu !== alt && !nurPruefen) { geaendert++; writeFileSync(sw, neu); }
}
for (const pfad of seiten) {
  let html = readFileSync(pfad, 'utf8');
  if (!CSS_ANKER.test(html)) { uebersprungen++; continue; }
  const alt = html;
  if (!html.includes('/assets/theme.css')) html = html.replace(CSS_ANKER, (m) => m + CSS);
  if (!html.includes('merzenich-theme')) html = html.replace('<head>', '<head>' + INLINE);
  if (!html.includes('/assets/kopf.js')) {
    if (!JS_ANKER.test(html)) { fehler++; console.error('kein v20.js-Anker: ' + pfad); continue; }
    html = html.replace(JS_ANKER, (m) => m + JS);
  }
  if (!html.includes('/assets/kommentare.js')) html = html.replace(/<script src="\/assets\/theme\.js[^"]*" defer><\/script>/, (m) => m + KOMMENTARE);
  // "Diskussion" im Mehr-Menue und in der Schublade, direkt hinter Kontakt.
  if (!html.includes('href="/diskussion/"')) html = html.split(LINK_MEHR).join(LINK_MEHR + LINK_DISKUSSION);
  if (!html.includes('/assets/einwilligung.js')) html = html.replace(/<script src="\/assets\/kommentare\.js[^"]*" defer><\/script>/, (m) => m + EINWILLIGUNG);
  if (!html.includes('href="/werbefrei/"')) html = html.split(LINK_DISKUSSION).join(LINK_DISKUSSION + LINK_WERBEFREI);
  // Werbeschalter am <html>
  html = html.replace(/<html\b[^>]*>/, (m) => m.replace(/\s+data-werbung="[^"]*"/g, '').replace(/>$/, ` data-werbung="${WERBUNG_AN ? 'an' : 'aus'}">`));
  // Aeltere Seiten: Einwilligungs-Platzhalter auf den Bildproxy umstellen.
  html = html.replace(/src="\/assets\/img\/extern-platzhalter\.svg" data-extern-src="([^"]*)"(?: class="extern-gesperrt")?/g, (m, u) => `src="${esc(bildUrl(u.replace(/&amp;/g, '&')))}"`);
  // Cache-Busting ist Build-Aufgabe, kein Strukturfehler. Dadurch kann --check
  // vor dem Docker-Build gruen sein, waehrend der Docker-Build trotzdem jeden
  // geaenderten Asset-Link mit dem aktuellen Inhalts-Hash ausliefert.
  if (!nurPruefen) html = versioniere(html);
  if (html !== alt) { geaendert++; if (!nurPruefen) writeFileSync(pfad, html); }
}
console.log(`${seiten.length} Seiten, ${geaendert} ${nurPruefen ? 'strukturell nicht aktuell' : 'geaendert'}, ${uebersprungen} ohne korrekturen.css uebersprungen, ${fehler} Fehler.`);
if (fehler || (nurPruefen && geaendert)) process.exit(2);
