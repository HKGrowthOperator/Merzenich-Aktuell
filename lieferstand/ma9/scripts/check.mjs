/* Prüft dist/: interne Links, JSON-LD, Pflichtfelder. Aufruf: node scripts/check.mjs (nach dem Build) */
import { readdirSync, readFileSync, statSync, existsSync } from 'node:fs';
import { join } from 'node:path';
const DIST = 'dist', files = [];
(function walk(d) { for (const f of readdirSync(d)) { const p = join(d, f); statSync(p).isDirectory() ? walk(p) : files.push(p); } })(DIST);
const exists = u => { u = u.split('#')[0].split('?')[0]; if (!u.startsWith('/')) return true; const p = join(DIST, u); return existsSync(p) || existsSync(join(p, 'index.html')); };
let broken = [], ld = 0, ldErr = 0, pages = 0, noDesc = [];
for (const f of files.filter(f => f.endsWith('.html'))) {
  pages++; const s = readFileSync(f, 'utf8');
  if (!/<meta name="description" content="[^"]{20,}"/.test(s)) noDesc.push(f);
  for (const m of s.matchAll(/(?:href|src)="([^"]+)"/g)) { const u = m[1]; if (u.startsWith('/') && !u.startsWith('//') && !u.startsWith('/.netlify') && !exists(u)) broken.push(`${f} → ${u}`); }
  for (const m of s.matchAll(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/g)) { ld++; try { JSON.parse(m[1]); } catch (e) { ldErr++; console.log('JSON-LD-Fehler in', f); } }
}
console.log(`Seiten: ${pages} · JSON-LD: ${ld} (${ldErr} fehlerhaft) · kaputte Links: ${broken.length} · ohne Description: ${noDesc.length}`);
if (broken.length) console.log([...new Set(broken)].join('\n'));
if (noDesc.length) console.log(noDesc.join('\n'));
process.exit(broken.length || ldErr ? 1 : 0);
