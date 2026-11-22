#!/usr/bin/env node
/**
 * Zurückgezogene Beiträge und Termine aus der statischen Kopie entfernen (18.11.2026).
 *   node deploy/zurueckziehen.mjs          aufräumen
 *   node deploy/zurueckziehen.mjs --check  nichts schreiben, Exit 2, wenn noch etwas zu tun wäre
 *
 * Liste: deploy/zurueckgezogen.json (pfad, datum, grund). Live liegen die Beiträge im
 * WordPress-Papierkorb; der Abgleich holt sie nicht zurück. Die Generatoren hängen
 * Zeilen aber nur an und entfernen keine, daher räumt dieses Werkzeug auf:
 *   - Seitenordner des Pfads
 *   - Feed-Zeilen (<article … class="feed-row"> und "feed-lead"), Karten (<article> mit „card“ oder „karte“ in der Klasse, ohne Verschachtelung)
 *     und Listenpunkte (<li>) mit Link auf den Pfad
 *   - ListItem-Einträge im JSON-LD, danach neu durchnummeriert
 *   - Weiterleitungen auf den Pfad in _redirects
 *   - Zähler <p class="count-line"> um die entfernten Feed-Zeilen verringert
 *   - Themenseiten ohne verbleibende Feed-Zeile, außer Zielen des Ressort-Menüs
 * Verweise in anderer Form (etwa ein Link im Fließtext) meldet es als Fehler, damit sie
 * von Hand gelöst werden.
 */
import { existsSync, readFileSync, readdirSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');
const pfade = JSON.parse(readFileSync(join(wurzel, 'deploy', 'zurueckgezogen.json'), 'utf8')).eintraege.map((e) => e.pfad);

const esc = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
const linkRe = new RegExp(`href="(?:https?://[^"/]+)?(?:${pfade.map(esc).join('|')})"`);
const trefferRe = new RegExp(pfade.map(esc).join('|'));
// Themenseiten, die das Ressort-Menü verlinkt, bleiben auch leer stehen (live zeigt WordPress dann „noch keine Meldung“).
const menueZiele = new Set((readFileSync(join(wurzel, 'deploy', 'ressort-menue.json'), 'utf8').match(/"\/thema\/[^"]+\/"/g) || []).map((z) => z.slice(1, -1)));
const zielGleich = (u) => typeof u === 'string' && pfade.some((p) => u === p || u.endsWith(p));

function htmlDateien(ordner, liste = []) {
  for (const e of readdirSync(ordner, { withFileTypes: true })) {
    const p = join(ordner, e.name);
    if (e.isDirectory()) { if (e.name !== 'assets' && e.name !== 'node_modules') htmlDateien(p, liste); }
    else if (e.name.endsWith('.html')) liste.push(p);
  }
  return liste;
}

// JSON-LD: ListItems mit Ziel auf einen zurückgezogenen Pfad entfernen, Positionen neu zählen.
function ldBereinigen(wert) {
  let geaendert = false;
  const gehen = (x) => {
    if (Array.isArray(x)) {
      const istListe = x.some((el) => el && el['@type'] === 'ListItem');
      if (istListe) {
        const rest = x.filter((el) => !(el && el['@type'] === 'ListItem' && [el.url, el.item, el.item?.url, el.item?.['@id']].some(zielGleich)));
        if (rest.length !== x.length) {
          geaendert = true;
          rest.forEach((el, i) => { if (el && typeof el.position === 'number') el.position = i + 1; });
          x.splice(0, x.length, ...rest);
        }
      }
      x.forEach(gehen);
    } else if (x && typeof x === 'object') {
      for (const v of Object.values(x)) gehen(v);
      if (Array.isArray(x.itemListElement) && typeof x.numberOfItems === 'number' && x.numberOfItems !== x.itemListElement.length && geaendert) x.numberOfItems = x.itemListElement.length;
    }
  };
  gehen(wert);
  return geaendert;
}

let seitenWeg = 0, zeilenWeg = 0, punkteWeg = 0, kartenWeg = 0, ldWeg = 0, themenWeg = 0, umleitungenWeg = 0, dateienGeaendert = 0;
const reste = [];

for (const p of pfade) {
  const ordner = join(site, p);
  if (existsSync(ordner)) { seitenWeg++; if (!nurPruefen) rmSync(ordner, { recursive: true, force: true }); }
}

for (const datei of htmlDateien(site)) {
  if (pfade.some((p) => datei.startsWith(join(site, p)))) continue;
  const alt = readFileSync(datei, 'utf8');
  if (!trefferRe.test(alt)) continue;
  let neu = alt, zeilen = 0;
  neu = neu.replace(/(\n[ \t]*)?<article\b[^>]*class="feed-(?:row|lead)"[^>]*>[\s\S]*?<\/article>/g, (m) => (linkRe.test(m) ? (zeilen++, '') : m));
  neu = neu.replace(/<li\b[^>]*>(?:(?!<\/?li\b)[\s\S])*?<\/li>/g, (m) => (linkRe.test(m) ? (punkteWeg++, '') : m));
  // Karten (<article class="news-card"> u. a., Klasse mit „card“ oder „karte“, ohne verschachtelte <article>) mit Link auf den Pfad (22.11.2026).
  neu = neu.replace(/(\n[ \t]*)?<article\b[^>]*class="[^"]*(?:card|karte)[^"]*"[^>]*>(?:(?!<\/?article\b)[\s\S])*?<\/article>/g, (m) => (linkRe.test(m) ? (kartenWeg++, '') : m));
  neu = neu.replace(/(<script type="application\/ld\+json">)([\s\S]*?)(<\/script>)/g, (m, a, json, z) => {
    if (!trefferRe.test(json)) return m;
    let wert;
    try { wert = JSON.parse(json); } catch { return m; }
    if (JSON.stringify(wert) !== json.trim()) return m; // nur kompakt geschriebenes JSON-LD anfassen
    if (!ldBereinigen(wert)) return m;
    ldWeg++;
    return a + JSON.stringify(wert) + z;
  });
  if (zeilen) {
    zeilenWeg += zeilen;
    neu = neu.replace(/<p class="count-line">(\d+) Meldung(?:en)?<\/p>/, (m, n) => {
      const r = Math.max(0, Number(n) - zeilen);
      return `<p class="count-line">${r} Meldung${r === 1 ? '' : 'en'}</p>`;
    });
  }
  const themenOrdner = dirname(datei);
  if (zeilen && dirname(themenOrdner) === join(site, 'thema') && !/class="feed-(?:row|lead)"/.test(neu)
    && !menueZiele.has(themenOrdner.replace(site, '') + '/')) {
    themenWeg++;
    if (!nurPruefen) rmSync(themenOrdner, { recursive: true, force: true });
    continue;
  }
  if (trefferRe.test(neu)) reste.push(datei.replace(site, 'chatgpt-site'));
  if (neu !== alt) { dateienGeaendert++; if (!nurPruefen) writeFileSync(datei, neu); }
}

const umleitungen = join(site, '_redirects');
if (existsSync(umleitungen)) {
  const alt = readFileSync(umleitungen, 'utf8');
  const neu = alt.split('\n').filter((z) => { const ziel = z.trim().split(/\s+/)[1]; const weg = zielGleich(ziel); if (weg) umleitungenWeg++; return !weg; }).join('\n');
  if (neu !== alt) { dateienGeaendert++; if (!nurPruefen) writeFileSync(umleitungen, neu); }
}

const zuTun = seitenWeg + dateienGeaendert + themenWeg;
console.log(`Zurückgezogen: ${pfade.length} Pfade; ${nurPruefen ? 'offen' : 'entfernt'}: ${seitenWeg} Seiten, ${zeilenWeg} Feed-Zeilen, ${punkteWeg} Listenpunkte, ${kartenWeg} Karten, ${ldWeg} JSON-LD-Listen, ${umleitungenWeg} Weiterleitungen, ${themenWeg} leere Themenseiten (${dateienGeaendert} Dateien).`);
if (reste.length) {
  console.error(`Zurückgezogen: noch Verweise in anderer Form, von Hand lösen:\n  ${reste.join('\n  ')}`);
  process.exit(1);
}
if (nurPruefen && zuTun) process.exit(2);
