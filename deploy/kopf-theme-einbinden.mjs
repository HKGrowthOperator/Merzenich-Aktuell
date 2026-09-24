#!/usr/bin/env node
/**
 * Bindet Fundament-Tokens (system.css, vor allen Projektdateien),
 * den reinen Hellmodus, einklappenden Kopf (kopf.js) und Kommentare
 * (kommentare.js) in alle Seiten von chatgpt-site/ ein, die
 * korrekturen.css laden, und setzt den Link zur Diskussion ins Mehr-Menue.
 * Idempotent: was schon drin ist, wird nicht doppelt eingefuegt.
 * Aufruf: node deploy/kopf-theme-einbinden.mjs [--check]
 */
import { readFileSync, writeFileSync, readdirSync, statSync, existsSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { SITE_URL } from './lib-artikel.mjs';
const KONTAKT_MAIL = JSON.parse(readFileSync(join(resolve(dirname(fileURLToPath(import.meta.url)), '..'), 'deploy', 'site.json'), 'utf8')).kontaktMail;
const KONTAKT_SEITEN = ['kontakt', 'ueber-uns', 'meldung-senden', 'korrekturen', 'redaktion'];
const ALTE_DOMAIN = 'https://merzenich-aktuell.de';
const SSI_DATUM = '<time data-today datetime="<!--# config timefmt="%Y-%m-%dT%H:%M:%S%z" --><!--# echo var="date_local" -->"><!--# config timefmt="%d.%m." --><!--# echo var="date_local" --></time>';
const esc = (t) => String(t ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
// Nur technischer URL-Proxy fuer alte externe Platzhalter. Editoriale Bildauswahl
// liegt seit V2 ausschliesslich in deploy/symbolbilder.mjs + lib-symbolbilder.mjs.
const bildUrl = (u) => {
  const s = String(u || '').trim();
  if (!s || s.startsWith('/')) return s;
  if (!/^https?:\/\//i.test(s)) return '';
  return `/api/bild?u=${encodeURIComponent(s)}`;
};

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const V = 'v=20260916b';
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
// system.css traegt nur die Tokens (Abstand, Typo, Radius, Breite) und muss
// deshalb VOR allen Projektdateien stehen, damit style.css und alles danach
// sie nutzen kann. style.css ist auf jeder Seite, die korrekturen.css laedt,
// die erste Projektdatei - und damit der verlaessliche Anker zum Davorhaengen.
const SYSTEM_ANKER = /<link rel="stylesheet" href="\/assets\/style\.css[^"]*">/;
const JS_ANKER = /<script src="\/assets\/v20\.js[^"]*" defer><\/script>/;
const INLINE = '<script>document.documentElement.dataset.theme="light";document.documentElement.style.colorScheme="light";try{localStorage.removeItem("merzenich-theme")}catch(e){}</script>';
const SYSTEM = `<link rel="stylesheet" href="/assets/system.css?${V}">`;
const CSS = `<link rel="stylesheet" href="/assets/theme.css?${V}">`;
// startseite.css besitzt die obere Flaeche der Startseite und laedt blockierend
// nach theme.css. Sie gilt nur dort, wo body.home steht - also auf index.html.
const STARTSEITE = `<link rel="stylesheet" href="/assets/startseite.css?${V}">`;
const JS = `<script src="/assets/kopf.js?${V}" defer></script>`;
const KOMMENTARE = `<script src="/assets/kommentare.js?${V}" defer></script>`;
const EINWILLIGUNG = `<script src="/assets/einwilligung.js?${V}" defer></script>`;
const LINK_MEHR = '<a href="/kontakt/">Kontakt</a>';
const LINK_DISKUSSION = '<a href="/diskussion/">Diskussion</a>';

const seiten = [];
(function lauf(d) { for (const e of readdirSync(d)) { const p = join(d, e); statSync(p).isDirectory() ? lauf(p) : p.endsWith('.html') && seiten.push(p); } })(join(wurzel, 'chatgpt-site'));

let geaendert = 0, uebersprungen = 0, fehler = 0;
// Auch die Loader in kopf.js (bild-fallbacks, homepage-polish) per Hash versionieren.
{
  const kp = join(wurzel, 'chatgpt-site', 'assets', 'kopf.js');
  const alt = readFileSync(kp, 'utf8');
  const neu = alt.replace(/(['"])(\/assets\/[a-z0-9-]+\.(?:js|css))\?v=[^'"]*\1/g, (m, q, pfad) => { const h = assetHash(pfad); return h ? `${q}${pfad}?v=${h}${q}` : m; });
  if (neu !== alt) { geaendert++; if (!nurPruefen) writeFileSync(kp, neu); hashCache.delete('/assets/kopf.js'); }
  // Der globale Editorial-Audit-Layer (ChatGPT, 17.09.) muss vom zentralen Kopf geladen werden.
  if (!alt.includes('/assets/editorial-audit.css') || !alt.includes('/assets/editorial-audit.js')) { fehler++; console.error('kopf.js: Editorial-Audit-Layer fehlt'); }
}
// Service Worker: VERSION aus dem Inhalt aller Assets ableiten. Der SW cacht
// CSS/JS ohne Query-String (ignoreSearch); nur ein neuer VERSION-Wert leert
// diesen Cache. So bekommt jeder Deploy mit geaenderten Assets automatisch
// einen neuen SW, ohne dass jemand die Zahl von Hand hochsetzt.
{
  const sw = join(wurzel, 'chatgpt-site', 'sw.js');
  const assetsDir = join(wurzel, 'chatgpt-site', 'assets');
  const dateien = readdirSync(assetsDir).filter((f) => /\.(?:css|js)$/.test(f)).sort();
  const gesamt = createHash('sha256');
  for (const f of dateien) gesamt.update(f).update('\0').update(readFileSync(join(assetsDir, f)));
  const version = 'a-' + gesamt.digest('hex').slice(0, 12);
  const alt = readFileSync(sw, 'utf8');
  const neu = alt.replace(/^const VERSION = '[^']*';/m, `const VERSION = '${version}';`);
  if (neu === alt && !alt.includes(`'${version}'`)) { fehler++; console.error('sw.js: keine Zeile "const VERSION = \'...\';" gefunden'); }
  else if (neu !== alt) { geaendert++; if (!nurPruefen) writeFileSync(sw, neu); }
}
// Mega-Menue "Mehr" (Anhang A, Vorbild Oberberg Aktuell): ein festes Panel ueber
// die volle Breite statt einer Linkliste, die v20.js zur Laufzeit befuellt.
// Vier Spalten wie die Schublade auf dem Telefon, darunter die drei Wege, etwas
// an die Redaktion zu geben. Unter 768 px bleibt es bei der Schublade.
const MEGA_SPALTEN = [
  ['Ressorts', [['/nachrichten/', 'Aktuell'], ['/blaulicht/', 'Blaulicht'], ['/sport/', 'Sport'], ['/termine/', 'Termine'], ['/vereine/', 'Vereine'], ['/rathaus/', 'Rathaus & Politik'], ['/leben/', 'Leben'], ['/wirtschaft/', 'Wirtschaft'], ['/tipp/', 'Tipp'], ['/menschen/', 'Menschen']]],
  ['Orte', [['/merzenich/', 'Merzenich'], ['/golzheim/', 'Golzheim'], ['/girbelsrath/', 'Girbelsrath'], ['/morschenich/', 'Morschenich'], ['/buergewald/', 'Bürgewald']]],
  ['Anzeigen & Service', [['/immobilien/', 'Immobilienmarkt'], ['/jobs/', 'Stellenmarkt'], ['/traueranzeigen/', 'Traueranzeigen'], ['/familienanzeigen/', 'Familienanzeigen'], ['/service/', 'Notdienste & Rathaus'], ['/sc-1919-merzenich/', 'SC 1919 Merzenich'], ['/diskussion/', 'Diskussion'], ['/archiv/', 'Archiv']]],
  ['Redaktion', [['/ueber-uns/', 'Über uns'], ['/kontakt/', 'Kontakt'], ['/grundsaetze/', 'Grundsätze'], ['/ki-redaktion/', 'KI & Redaktion'], ['/kommentarregeln/', 'Kommentarrichtlinien'], ['/korrekturen/', 'Korrekturen'], ['/werben/', 'Werben & Mediadaten'], ['/unterstuetzen/', 'Unterstützen']]],
];
const MEGA_WEGE = [['/anzeigen/aufgeben/', 'Anzeige aufgeben'], ['/meldung-senden/', 'Meldung senden'], ['/termine/melden/', 'Termin melden']];
const MEGA = '<details class="nav-more mega"><summary>Mehr</summary><div class="mega-panel"><div class="mega-spalten">'
  + MEGA_SPALTEN.map(([titel, links]) => `<div class="mega-spalte"><p class="mega-titel">${esc(titel)}</p><ul>${links.map(([u, t]) => `<li><a href="${u}">${esc(t)}</a></li>`).join('')}</ul></div>`).join('')
  + `</div><p class="mega-wege"><span class="mega-titel">Für die Redaktion</span>${MEGA_WEGE.map(([u, t], i) => `<a class="${i ? 'mega-weg' : 'mega-weg primaer'}" href="${u}">${esc(t)}</a>`).join('')}</p></div></details>`;
// Sport-Untermenue: stand bisher nur zur Laufzeit (app.js), jetzt im HTML.
const SPORT_MEGA = '<div class="sport-mega"><div class="shell sport-mega__inner"><div><span class="sport-mega__eyebrow">Sport in Merzenich</span><strong>Vereine, Spiele und Ergebnisse</strong></div><nav aria-label="Sport Untermenü"><a href="/sport/">Alle Sportmeldungen</a><a href="/sc-1919-merzenich/">SC 1919 Merzenich</a><a href="/vereine/">Vereine</a><a href="/meldung-senden/">Sportmeldung senden</a></nav></div></div><!--/sport-mega-->';
// Scroll-Kopf: das Monogramm aus der Wortmarke (deploy/marke.mjs) statt des
// verkleinerten Logos bzw. eines Schrift-M. Der Link traegt den Namen, das
// Bild ist Schmuck (alt=""), damit Screenreader nicht doppelt vorlesen.
const MONO_V = (() => { const h = assetHash('/assets/marke/monogramm.svg'); return h ? `?v=${h}` : ''; })();
const MONOGRAMM_LINK = `<a class="compact-brand" href="/" aria-label="Merzenich Aktuell – Startseite"><img class="ma-monogramm" src="/assets/marke/monogramm.svg${MONO_V}" width="35" height="35" alt=""></a>`;
const MONOGRAMM_RE = /<a class="compact-brand" href="\/"(?: aria-label="[^"]*")?>\s*<img\b[^>]*>\s*<\/a>/;
const MEHR_RE = /<details class="nav-more[^"]*">[\s\S]*?<\/details>/;
const SPORT_RE = /<div class="sport-mega">[\s\S]*?<!--\/sport-mega-->/;
const LINIE = '<div class="merzenich-linie" aria-hidden="true"><i></i><svg viewBox="0 0 240 48" focusable="false"><path d="M0 24 H118 L124 20 L130 29 L136 21 L144 4 L152 44 L158 25 L166 24 L174 24 L180 18 L186 30 L192 24 H240" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"/></svg><i></i></div>';
for (const pfad of seiten) {
  let html = readFileSync(pfad, 'utf8');
  if (!CSS_ANKER.test(html)) { uebersprungen++; continue; }
  const alt = html;
  // FAVICON-PNG-FALLBACK: aktuelles M.-Markenzeichen aus der Wortmarke.\n  // SVG + PNG werden ueber ihren Inhalts-Hash versioniert, damit Safari/Chrome/Google\n  // nach Markenupdates nicht ein altes, lang gecachtes Icon behalten.\n  html = html.replace(/<link rel="icon" href="\\/assets\\/img\\/avatar-1024\\.png(?:\\?[^"]*)?" type="image\\/png" sizes="1024x1024">\\s*/g, '');\n  html = html.replace(/<link rel="icon" href="\\/assets\\/img\\/favicon\\.svg(?:\\?[^"]*)?" type="image\\/svg\\+xml"(?: sizes="any")?>/, '<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml" sizes="any">\\n<link rel="icon" href="/assets/img/avatar-1024.png" type="image/png" sizes="1024x1024">');\n  // Fundament zuerst: vor style.css, nicht dahinter (siehe SYSTEM_ANKER).
  if (!html.includes('/assets/system.css')) {
    if (!SYSTEM_ANKER.test(html)) { fehler++; console.error('kein style.css-Anker: ' + pfad); continue; }
    html = html.replace(SYSTEM_ANKER, (m) => SYSTEM + m);
  }
  if (!html.includes('/assets/theme.css')) html = html.replace(CSS_ANKER, (m) => m + CSS);
  // Nur die Startseite: erkannt am ausgelieferten Markup, nicht am Dateinamen.
  if (/<body[^>]*class="[^"]*\bhome\b/.test(html) && !html.includes('/assets/startseite.css')) {
    html = html.replace(/<link rel="stylesheet" href="\/assets\/theme\.css[^"]*">/, (m) => m + STARTSEITE);
  }
  if (!html.includes('merzenich-theme')) html = html.replace('<head>', '<head>' + INLINE);
  if (!html.includes('/assets/kopf.js')) {
    if (!JS_ANKER.test(html)) { fehler++; console.error('kein v20.js-Anker: ' + pfad); continue; }
    html = html.replace(JS_ANKER, (m) => m + JS);
  }
  if (!html.includes('/assets/kommentare.js')) html = html.replace(/<script src="\/assets\/kopf\.js[^"]*" defer><\/script>/, (m) => m + KOMMENTARE);
  // "Diskussion" im Mehr-Menue und in der Schublade, direkt hinter Kontakt.
  if (!html.includes('href="/diskussion/"')) html = html.split(LINK_MEHR).join(LINK_MEHR + LINK_DISKUSSION);
  if (!html.includes('/assets/einwilligung.js')) html = html.replace(/<script src="\/assets\/kommentare\.js[^"]*" defer><\/script>/, (m) => m + EINWILLIGUNG);
  // KBS/Ordin 23.09.2026: eigener Tipp-Kanal nach Wirtschaft.
  html = html.replace(/(<a href="\/wirtschaft\/"(?: aria-current="page")?>Wirtschaft<\/a>)(?!<a href="\/tipp\/")/g, '$1<a href="/tipp/">Tipp</a>');
  // KBS/Ordin 23.09.2026: Werbefrei-Abo und Dunkelmodus sind vollständig entfernt.
  html = html.replace(/<a href="\/werbefrei\/">Werbefrei lesen<\/a>/g, '');
  html = html.replace(/<script src="\/assets\/(?:theme|werbefrei)\.js[^"]*" defer><\/script>/g, '');
  // Werbeschalter am <html>
  html = html.replace(/<html\b[^>]*>/, (m) => m.replace(/\s+data-werbung="[^"]*"/g, '').replace(/>$/, ` data-werbung="${WERBUNG_AN ? 'an' : 'aus'}">`));
  // Tagesdatum im Kopf: nginx rendert es serverseitig (SSI, Ortszeit); JS
  // (homepage-polish) aktualisiert es zusaetzlich. Vorher stand ein fester
  // Bauzeitpunkt ("16.09.") im HTML, sichtbar fuer alle ohne JS und in Crawlern.
  html = html.replace(/<time data-today(?: datetime="[^"]*")?>[^<]*<\/time>/g, SSI_DATUM);
  // Oeffentliche Adresse: canonical, og:url, JSON-LD, Feedlinks auf die Live-URL (deploy/site.json).
  html = html.split(ALTE_DOMAIN).join(SITE_URL);
  // Eine Redaktionsadresse nach aussen (deploy/site.json), nur auf den Redaktionsseiten;
  // das Impressum behaelt die Adresse der Gesellschaft.
  if (KONTAKT_SEITEN.some((k) => pfad.includes(`/chatgpt-site/${k}/`))) html = html.split('redaktion@merzenich-aktuell.de').join(KONTAKT_MAIL).split('info@kbs-management.tv').join(KONTAKT_MAIL);
  // Das Redaktionshandbuch (/redaktion/) ist nicht oeffentlich; der Fusslink fuehrt zur Ueber-uns-Seite.
  html = html.replace(/<a href="\/redaktion\/">Redaktion<\/a>/g, '<a href="/ueber-uns/">Redaktion</a>');
  // Wetter-Akkordeon: bleibt verborgen, bis v20.js echte Daten hat (kein sichtbarer "nicht verfuegbar"-Zustand).
  html = html.replace(/<details class="service-accordion"(?: hidden)?><summary>Wetter<\/summary><div><p>[^<]*<\/p><\/div><\/details>/g, '<details class="service-accordion" hidden><summary>Wetter</summary><div><p>Wetter wird geladen …</p></div></details>');
  // Aeltere Seiten: Einwilligungs-Platzhalter auf den Bildproxy umstellen.
  html = html.replace(/src="\/assets\/img\/extern-platzhalter\.svg" data-extern-src="([^"]*)"(?: class="extern-gesperrt")?/g, (m, u) => `src="${esc(bildUrl(u.replace(/&amp;/g, '&')))}"`);
  if (MONOGRAMM_RE.test(html)) html = html.replace(MONOGRAMM_RE, () => MONOGRAMM_LINK);
  // Mega-Menue und Sport-Untermenue (siehe MEGA): nur im Kopf mit Ressortleiste.
  if (MEHR_RE.test(html) && html.includes('<nav class="mainnav"')) {
    html = html.replace(MEHR_RE, () => MEGA);
    html = html.replace(SPORT_RE, '');
    const i = html.indexOf('<details class="nav-more mega">');
    const ende = html.indexOf('</div></nav>', i);
    if (ende < 0) { fehler++; console.error('Ressortleiste ohne Abschluss: ' + pfad); continue; }
    html = html.slice(0, ende + 6) + SPORT_MEGA + html.slice(ende + 6);
  }
  // Merzenich-Linie (Anhang A): die Pulslinie aus dem Logo als ruhige Trennung
  // ueber dem Fuss. Ein Element, einmal je Seite, keine Animation.
  if (!html.includes('class="merzenich-linie"')) html = html.replace('<footer class="compact-footer">', LINIE + '<footer class="compact-footer">');
  html = versioniere(html);
  if (html !== alt) { geaendert++; if (!nurPruefen) writeFileSync(pfad, html); }
}
// Feeds, Sitemaps, robots.txt, JSON: dieselbe Domain-Umstellung.
{
  const dateien = []; (function lauf(d) { for (const e of readdirSync(d)) { const p = join(d, e); statSync(p).isDirectory() ? lauf(p) : /\.(?:xml|json|txt|webmanifest|ics|yml)$/.test(e) && dateien.push(p); } })(join(wurzel, 'chatgpt-site'));
  for (const p of dateien) { const alt = readFileSync(p, 'utf8'); if (!alt.includes(ALTE_DOMAIN)) continue; geaendert++; if (!nurPruefen) writeFileSync(p, alt.split(ALTE_DOMAIN).join(SITE_URL)); }
}
console.log(`${seiten.length} Seiten, ${geaendert} ${nurPruefen ? 'nicht aktuell' : 'geaendert'}, ${uebersprungen} ohne korrekturen.css uebersprungen, ${fehler} Fehler.`);
if (fehler || (nurPruefen && geaendert)) process.exit(2);
