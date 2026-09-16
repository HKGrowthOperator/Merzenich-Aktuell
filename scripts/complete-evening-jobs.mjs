#!/usr/bin/env node
import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const file = join(root, 'chatgpt-site/jobs/index.html');
let html = readFileSync(file, 'utf8');

if (html.includes('Kaufmännische Mitarbeitende Vertriebsinnendienst')) {
  console.log('Complete jobs already integrated.');
  process.exit(0);
}

html = html.replace('5 aktuell geprüfte Stellen', '7 aktuell geprüfte Stellen');
const marker = '</article>\n</div><aside class="sidebar">';
if (!html.includes(marker)) throw new Error('Jobs insertion marker not found');

const extra = `</article>
<article class="event-row job-row"><span class="d job-d"><b>Teil/Voll</b></span><div class="info"><span class="eyebrow">Thermopor Glas GmbH · Girbelsrath</span><h3><a href="https://www.thermopor-glas.de/karriere/" target="_blank" rel="noopener">Kaufmännische Mitarbeitende Vertriebsinnendienst (m/w/d)</a></h3><div class="meta"><span>Teil- oder Vollzeit</span><span>geprüft 16.09.2026</span></div><p class="ev-desc">Unterstützung des Vertriebsaußendienstes, Kundenkorrespondenz, Angebote und Stammdatenpflege.</p></div><div class="act"><a href="https://www.thermopor-glas.de/karriere/" target="_blank" rel="noopener">Originalquelle ↗</a></div></article>
<article class="event-row job-row"><span class="d job-d"><b>Ausbildung</b></span><div class="info"><span class="eyebrow">Thermopor Glas GmbH · Girbelsrath</span><h3><a href="https://www.thermopor-glas.de/karriere/" target="_blank" rel="noopener">Ausbildung Industriekauffrau / Industriekaufmann</a></h3><div class="meta"><span>Ausbildung</span><span>geprüft 16.09.2026</span></div><p class="ev-desc">Kaufmännische Ausbildung mit Einblick in Auftragsabwicklung, Vertrieb, Einkauf, Produktionsplanung und Logistik.</p></div><div class="act"><a href="https://www.thermopor-glas.de/karriere/" target="_blank" rel="noopener">Originalquelle ↗</a></div></article>
</div><aside class="sidebar">`;
html = html.replace(marker, extra);
writeFileSync(file, html);
console.log('Jobs completed: 7 verified current vacancies/training offers.');
