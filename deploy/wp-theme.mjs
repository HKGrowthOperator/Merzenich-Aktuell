#!/usr/bin/env node
/**
 * WordPress-Theme aus der gebauten Seite nachziehen (30.09.2026).
 *
 * Die WordPress-Seite (merzenich-aktuell.de) soll genau so aussehen wie die
 * statische Seite. Statt Kopf, Fuß und Startseite ein zweites Mal von Hand in
 * PHP zu pflegen, schneidet dieses Skript sie aus chatgpt-site/index.html aus
 * und legt sie als Vorlagen ins Theme:
 *
 *   vorlagen/kopf-assets.html  Schriften-Preload, Icons, Stylesheets
 *   vorlagen/kopf.html         Masthead, Ressort-Navigation, Ortswahl, Schublade
 *   vorlagen/fuss.html         Merzenich-Linie, Fuß, Skripte
 *   vorlagen/startseite.html   Inhalt von <main> mit Platzhaltern {{ma:oben}},
 *                              {{ma:gemeinde}} … für die Nachrichtenblöcke, die
 *                              WordPress aus seinen Beiträgen setzt
 *                              (theme/inc/startseite.php)
 *
 * Alles andere auf der Startseite (Merzenich jetzt, Servicespalte, Märkte,
 * Werbebänder, Orte, Umkreis, Foto des Tages) kommt unverändert mit.
 * Aufruf: node deploy/wp-theme.mjs [--check]
 */
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const ziel = join(wurzel, 'wordpress', 'theme', 'merzenich-aktuell', 'vorlagen');
const html = readFileSync(join(wurzel, 'chatgpt-site', 'index.html'), 'utf8');

const zwischen = (start, ende, text = html) => {
  const a = text.indexOf(start);
  const b = text.indexOf(ende, a + start.length);
  if (a < 0 || b < 0) throw new Error(`wp-theme: Abschnitt ${start} … ${ende} nicht gefunden`);
  return text.slice(a, b);
};

// Kopf-Assets: Preloads, Icons, Stylesheets. Titel, Meta, JSON-LD und
// Canonical setzt WordPress selbst (wp_head), sie hängen am Inhalt.
const head = zwischen('<head>', '</head>');
const assets = [...head.matchAll(/<link rel="(?:preload|icon|apple-touch-icon|stylesheet)"[^>]*>/g)].map((m) => m[0]).join('\n');

// Kopf: vom Masthead bis vor <main>. Fuß: nach </main> bis vor </body>.
const koerper = zwischen('<body', '</body>');
const kopf = zwischen('<header class="masthead"', '<main id="main">', koerper).trim();
const fuss = koerper.slice(koerper.indexOf('</main>') + '</main>'.length).trim();

// Startseite: Inhalt von <main>, Nachrichtenblöcke als Platzhalter.
let start = zwischen('<main id="main">', '</main>', koerper).slice('<main id="main">'.length).trim();
const SLOTS = ['oben', 'gemeinde', 'blaulicht', 'rathaus', 'wirtschaft', 'vereine'];
for (const s of SLOTS) {
  const re = new RegExp(`<!-- start:${s}:start -->[\\s\\S]*?<!-- start:${s}:end -->`);
  if (!re.test(start)) throw new Error(`wp-theme: Block start:${s} fehlt in index.html`);
  start = start.replace(re, `{{ma:${s}}}`);
}

// Unterseiten tragen einen Kopf ohne „Merzenich jetzt“-Zeile. Vorlage ist das
// Impressum (keine Rubrik hervorgehoben).
const seite = readFileSync(join(wurzel, 'chatgpt-site', 'impressum', 'index.html'), 'utf8');
const kopfSeite = zwischen('<header class="masthead"', '<main id="main">', zwischen('<body', '</body>', seite)).trim().replace(/ aria-current="page"/g, '');

const dateien = {
  'kopf-assets.html': assets,
  'kopf.html': kopf,
  'kopf-seite.html': kopfSeite,
  'fuss.html': fuss,
  'startseite.html': start,
};
if (!nurPruefen) mkdirSync(ziel, { recursive: true });
let geaendert = 0;
for (const [name, inhalt] of Object.entries(dateien)) {
  const pfad = join(ziel, name);
  const neu = inhalt + '\n';
  if (existsSync(pfad) && readFileSync(pfad, 'utf8') === neu) continue;
  geaendert++;
  if (!nurPruefen) writeFileSync(pfad, neu);
}
console.log(`WP-Theme: ${Object.keys(dateien).length} Vorlagen, ${geaendert} ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}.`);
if (nurPruefen && geaendert) process.exit(2);
