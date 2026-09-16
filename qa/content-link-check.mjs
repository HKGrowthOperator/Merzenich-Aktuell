#!/usr/bin/env node
import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { join, resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(root, 'chatgpt-site');
const htmlFiles = [];
const walk = d => {
  for (const name of readdirSync(d)) {
    const p = join(d, name);
    if (statSync(p).isDirectory()) walk(p);
    else if (p.endsWith('.html')) htmlFiles.push(p);
  }
};
walk(site);

const broken = [];
const externals = new Set();
const resolveInternal = raw => {
  const clean = raw.split('#')[0].split('?')[0];
  if (!clean || clean.startsWith('mailto:') || clean.startsWith('tel:') || clean.startsWith('data:') || clean.startsWith('javascript:')) return null;
  if (/^https?:\/\//i.test(clean)) { externals.add(clean); return null; }
  if (clean.startsWith('//')) { externals.add('https:' + clean); return null; }
  const rel = clean.startsWith('/') ? clean.slice(1) : clean;
  const candidates = [join(site, rel)];
  if (!rel || rel.endsWith('/')) candidates.push(join(site, rel, 'index.html'));
  else if (!rel.includes('.')) candidates.push(join(site, rel, 'index.html'));
  return candidates.some(existsSync);
};

for (const file of htmlFiles) {
  const text = readFileSync(file, 'utf8');
  for (const m of text.matchAll(/(?:href|src)=["']([^"']+)["']/gi)) {
    const raw = m[1].trim();
    if (!raw || raw.startsWith('#')) continue;
    const ok = resolveInternal(raw);
    if (ok === false) broken.push(`${file.slice(site.length + 1)} -> ${raw}`);
  }
}

if (broken.length) {
  console.error(`BROKEN INTERNAL LINKS/ASSETS: ${broken.length}`);
  broken.slice(0, 100).forEach(x => console.error(' - ' + x));
  process.exit(1);
}

console.log(`Internal QA: ${htmlFiles.length} HTML files checked, no broken local href/src targets.`);
console.log(`External URLs discovered: ${externals.size}. Reachability is checked separately for release-critical original sources.`);
