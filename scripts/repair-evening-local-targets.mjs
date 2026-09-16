#!/usr/bin/env node
import { cpSync, existsSync, mkdirSync, readFileSync, readdirSync, statSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(root, 'chatgpt-site');
const dist = join(root, 'site-source/dist');

const htmlFiles = () => {
  const out = [];
  const walk = d => {
    for (const name of readdirSync(d)) {
      const p = join(d, name);
      if (statSync(p).isDirectory()) walk(p);
      else if (p.endsWith('.html')) out.push(p);
    }
  };
  walk(site);
  return out;
};

const targetCandidates = raw => {
  const clean = raw.split('#')[0].split('?')[0];
  if (!clean || !clean.startsWith('/') || clean.startsWith('//')) return [];
  const rel = clean.slice(1);
  if (!rel) return [];
  if (rel.endsWith('/')) return [join(rel, 'index.html')];
  if (rel.split('/').pop()?.includes('.')) return [rel];
  return [join(rel, 'index.html'), rel];
};

let repaired = 0;
for (let pass = 0; pass < 4; pass++) {
  let changed = 0;
  for (const file of htmlFiles()) {
    const text = readFileSync(file, 'utf8');
    for (const m of text.matchAll(/(?:href|src)=["']([^"']+)["']/gi)) {
      const raw = m[1].trim();
      if (/^(?:https?:|mailto:|tel:|data:|javascript:|#|\/\/)/i.test(raw)) continue;
      for (const rel of targetCandidates(raw)) {
        const dst = join(site, rel);
        if (existsSync(dst)) break;
        const src = join(dist, rel);
        if (!existsSync(src)) continue;
        mkdirSync(dirname(dst), { recursive: true });
        cpSync(src, dst, { recursive: statSync(src).isDirectory() });
        changed++;
        repaired++;
        break;
      }
    }
  }
  if (!changed) break;
}

console.log(`Recovered ${repaired} missing local targets from the canonical generated dist without replacing existing recovered pages.`);
