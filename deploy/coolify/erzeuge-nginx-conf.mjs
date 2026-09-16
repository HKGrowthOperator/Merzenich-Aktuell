#!/usr/bin/env node
/**
 * Erzeugt deploy/coolify/nginx.conf aus chatgpt-site/_redirects und
 * chatgpt-site/_headers. Beide Dateien sind fuer Cloudflare Pages geschrieben;
 * auf einem eigenen Server (Coolify) muss dieselbe Logik in nginx stehen,
 * sonst laufen die Altlinks ins Leere und die Sicherheitskopfzeilen fehlen.
 *
 * Aufruf: node deploy/coolify/erzeuge-nginx-conf.mjs
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const hier = dirname(fileURLToPath(import.meta.url));
const wurzel = join(hier, '..', '..');
const quelle = join(wurzel, 'chatgpt-site');

const redirects = readFileSync(join(quelle, '_redirects'), 'utf8');
const headers = readFileSync(join(quelle, '_headers'), 'utf8');

/* ---------- _redirects ---------- */
const exakt = [];
const muster = [];
let fangAlles = null;

for (const zeile of redirects.split('\n')) {
  const t = zeile.trim();
  if (!t || t.startsWith('#')) continue;
  const [von, nach, code = '301'] = t.split(/\s+/);
  if (von === '/*') { fangAlles = { nach, code }; continue; }
  if (von === '/api/*') continue;            // wird direkt ausgeliefert
  if (von.includes('*')) {
    const prefix = von.replace(/\/\*$/, '');
    const ziel = nach.includes(':splat')
      ? nach.replace(':splat', '$1')
      : nach;
    muster.push({ prefix, ziel, code, splat: nach.includes(':splat') });
    continue;
  }
  exakt.push({ von, nach, code });
}

/* ---------- _headers ---------- */
const bloecke = [];
let aktuell = null;
for (const zeile of headers.split('\n')) {
  if (!zeile.trim()) continue;
  if (!/^\s/.test(zeile)) {
    aktuell = { pfad: zeile.trim(), felder: [] };
    bloecke.push(aktuell);
  } else if (aktuell) {
    const i = zeile.indexOf(':');
    if (i > 0) aktuell.felder.push([zeile.slice(0, i).trim(), zeile.slice(i + 1).trim()]);
  }
}
const global = bloecke.find(b => b.pfad === '/*')?.felder ?? [];
const spezifisch = bloecke.filter(b => b.pfad !== '/*');

const addHeader = ([k, v]) => `    add_header ${k} "${v.replace(/"/g, '\\"')}" always;`;
const globaleZeilen = global.map(addHeader).join('\n');

/** Cloudflare-Pfadmuster -> nginx-location */
function alsLocation(pfad) {
  if (pfad === '/sw.js') return { art: 'exakt', ausdruck: '= /sw.js' };
  if (pfad === '/feed.xml') return { art: 'exakt', ausdruck: '= /feed.xml' };
  if (pfad === '/*/feed.xml') return { art: 'regex', ausdruck: '~ ^/[^/]+/feed\\.xml$' };
  if (pfad === '/termine/*/termin.ics') return { art: 'regex', ausdruck: '~ ^/termine/[^/]+/termin\\.ics$' };
  if (pfad.endsWith('/*')) return { art: 'prefix', ausdruck: pfad.slice(0, -1) };
  if (pfad.startsWith('/assets/*.')) {
    const endung = pfad.split('*.')[1];
    return { art: 'regex', ausdruck: `~ ^/assets/.*\\.${endung}$` };
  }
  return { art: 'exakt', ausdruck: `= ${pfad}` };
}

const out = [];
out.push('# ERZEUGT - nicht von Hand bearbeiten.');
out.push('# Quelle: chatgpt-site/_redirects und chatgpt-site/_headers');
out.push('# Neu erzeugen: node deploy/coolify/erzeuge-nginx-conf.mjs');
out.push('');
out.push('server {');
out.push('  listen 80;');
out.push('  server_name _;');
out.push('  root /usr/share/nginx/html;');
out.push('  index index.html;');
out.push('  charset utf-8;');
out.push('  absolute_redirect off;');
out.push('  server_tokens off;');
out.push('');
out.push('  gzip on;');
out.push('  gzip_vary on;');
out.push('  gzip_min_length 512;');
out.push("  gzip_types text/plain text/css text/xml application/javascript application/json application/rss+xml application/atom+xml image/svg+xml text/calendar;");
out.push('');
out.push('  # --- Weiterleitungen mit Platzhalter (aus _redirects) ---');
for (const m of muster) {
  const re = `^${m.prefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}/(.*)$`;
  const ziel = m.splat ? m.ziel : m.ziel;
  out.push(`  rewrite "${re}" ${ziel} ${m.code === '302' ? 'redirect' : 'permanent'};`);
}
out.push('');
out.push('  # --- Kopfzeilen fuer alle Antworten (aus _headers /*) ---');
out.push(globaleZeilen.replace(/^ {4}/gm, '  '));
out.push('');
out.push('  # --- Einzelweiterleitungen (aus _redirects) ---');
for (const r of exakt) {
  const flag = r.code === '302' ? '302' : '301';
  if (r.von === '/index.html') {
    // $request_uri statt $uri: interne Umleitungen duerfen nicht greifen,
    // sonst laeuft "/" -> "/index.html" -> "/" im Kreis.
    out.push('  location = /index.html {');
    out.push(`    if ($request_uri ~ "^/index\\.html") { return ${flag} ${r.nach}; }`);
    out.push('    try_files $uri =404;');
    out.push('  }');
  } else {
    out.push(`  location = ${r.von} { return ${flag} ${r.nach}; }`);
  }
}
out.push('');
out.push('  # --- Pfadbezogene Kopfzeilen (aus _headers) ---');
for (const b of spezifisch) {
  const loc = alsLocation(b.pfad);
  const kopf = loc.art === 'regex' ? `location ${loc.ausdruck}` : `location ${loc.ausdruck}`;
  out.push(`  ${kopf} {`);
  // add_header wird nicht vererbt, sobald ein Block eigene setzt: global wiederholen.
  out.push(global.map(addHeader).join('\n'));
  // Content-Type laesst sich in nginx NICHT per add_header setzen - das haengt nur
  // eine zweite Kopfzeile an. Der Typ kommt aus der types-Tabelle; ein leeres
  // types{} loescht die Zuordnung, danach greift default_type.
  const ct = b.felder.find(([k]) => k.toLowerCase() === 'content-type');
  if (ct) out.push(`    types { } default_type "${ct[1]}";`);
  const rest = b.felder.filter(([k]) => k.toLowerCase() !== 'content-type');
  if (rest.length) out.push(rest.map(addHeader).join('\n'));
  out.push('    try_files $uri $uri/ =404;');
  out.push('  }');
}
out.push('');
out.push('  # --- Auslieferung ---');
out.push('  location / {');
out.push(global.map(addHeader).join('\n'));
out.push('    try_files $uri $uri/ =404;');
out.push('  }');
out.push('');
if (fangAlles) {
  out.push(`  # aus _redirects: ${'/*'} ${fangAlles.nach} ${fangAlles.code}`);
  out.push(`  error_page 404 ${fangAlles.nach};`);
  out.push(`  location = ${fangAlles.nach} { internal; }`);
}
out.push('}');
out.push('');

const ziel = join(hier, 'nginx.conf');
writeFileSync(ziel, out.join('\n'), 'utf8');
console.log(`nginx.conf geschrieben: ${exakt.length} Einzelweiterleitungen, ${muster.length} Muster, ${spezifisch.length} Kopfzeilenbloecke`);
