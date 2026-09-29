#!/usr/bin/env node
/**
 * Eine Generator-Kette fuer CI und lokal. Reihenfolge wie bisher in qa.yml.
 *   node deploy/kette.mjs          alle Generatoren im Schreibmodus
 *   node deploy/kette.mjs --check  zweiter Lauf: kein Generator darf etwas aendern
 * Bricht beim ersten Fehler ab und nennt den Generator.
 */
import { spawnSync } from 'node:child_process';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const pruefen = process.argv.includes('--check');
const SCHREIBEN = ['meldungen', 'termine', 'gemeinde', 'marke', 'symbolbilder', 'markt-prerender', 'trauer-prerender', 'sport-prerender', 'suche-index', 'thema-prerender',
  'teaser-bilder', 'ortsmarke', 'inhaltsindex', 'umkreis', 'termine-prerender', 'ressort-menue', 'cockpit', 'unternehmen', 'anzeigen', 'werben', 'foto-des-tages', 'anzeigen-assistent', 'kopf-theme-einbinden', 'sitemaps', 'wordpress-import'];
const PRUEFEN = ['werben-bilder', 'symbolbilder', 'markt-prerender', 'trauer-prerender', 'sport-prerender', 'suche-index', 'thema-prerender', 'teaser-bilder', 'ortsmarke',
  'meldungen', 'termine', 'gemeinde', 'marke', 'ressort-menue', 'inhaltsindex', 'umkreis', 'termine-prerender', 'cockpit', 'unternehmen', 'werben', 'anzeigen', 'foto-des-tages', 'anzeigen-assistent', 'kopf-theme-einbinden', 'sitemaps', 'wordpress-import'];

for (const name of pruefen ? PRUEFEN : SCHREIBEN) {
  const r = spawnSync(process.execPath, [join(wurzel, 'deploy', `${name}.mjs`), ...(pruefen ? ['--check'] : [])], { cwd: wurzel, stdio: 'inherit' });
  if (r.status !== 0) { console.error(`kette: ${name}${pruefen ? ' --check' : ''} endete mit ${r.status}`); process.exit(r.status || 1); }
}
console.log(`kette: ${pruefen ? 'alle Generatoren unveraendert' : 'alle Generatoren gelaufen'}.`);
