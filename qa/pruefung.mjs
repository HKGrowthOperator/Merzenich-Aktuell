#!/usr/bin/env node
// Ausfuehrbare Fassung der Release-Gates aus qa/README.md.
//
// Ohne Fremdpakete und ohne Browser, damit die Pruefung ueberall laeuft und
// auch dann noch etwas wert ist, wenn gerade niemand einen Kopf zum Rendern
// hat. Sie faengt die Fehlerklasse ab, die bei paralleler Arbeit am
// wahrscheinlichsten ist: Markup und Code laufen auseinander, ohne dass es
// jemandem auffaellt.
//
//   node qa/pruefung.mjs            Bericht, Rueckgabewert 1 bei Fehlern
//   node qa/pruefung.mjs --json     maschinenlesbar

import { readFileSync, existsSync, readdirSync, statSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const alsJson = process.argv.includes('--json');
const befunde = [];

const melde = (schwere, bereich, text) => befunde.push({ schwere, bereich, text });
const fehler = (bereich, text) => melde('fehler', bereich, text);
const hinweis = (bereich, text) => melde('hinweis', bereich, text);

const lies = (pfad) => readFileSync(join(wurzel, pfad), 'utf8');
const gibtEs = (pfad) => existsSync(join(wurzel, pfad));

function dateienUnter(verzeichnis, endung) {
  const start = join(wurzel, verzeichnis);
  if (!existsSync(start)) return [];
  const treffer = [];
  const lauf = (d) => {
    for (const eintrag of readdirSync(d)) {
      const pfad = join(d, eintrag);
      if (statSync(pfad).isDirectory()) lauf(pfad);
      else if (pfad.endsWith(endung)) treffer.push(pfad);
    }
  };
  lauf(start);
  return treffer;
}

// ---------------------------------------------------------------- 1. Markup
// app.js greift ueber Kennungen und Klassen ins Markup. Benennt jemand im
// Markup etwas um, faellt das erst beim Benutzen auf, und auch dann nur als
// stiller Ausfall einer einzelnen Funktion.
function pruefeMarkupGegenCode() {
  if (!gibtEs('index.html') || !gibtEs('app.js')) {
    fehler('Markup', 'index.html oder app.js fehlt.');
    return;
  }
  const html = lies('index.html');
  const js = lies('app.js');

  const imMarkup = new Set([...html.matchAll(/id="([^"]+)"/g)].map((m) => m[1]));
  // Kennungen, die app.js selbst in Vorlagen erzeugt, zaehlen als vorhanden.
  const vonCodeErzeugt = new Set([...js.matchAll(/id="([A-Za-z0-9_-]+)"/g)].map((m) => m[1]));
  const verlangt = new Set([...js.matchAll(/\$\(\s*['"`]#([A-Za-z0-9_-]+)['"`]/g)].map((m) => m[1]));

  for (const kennung of verlangt) {
    if (!imMarkup.has(kennung) && !vonCodeErzeugt.has(kennung)) {
      fehler('Markup', `app.js sucht #${kennung}, im Markup steht es nicht.`);
    }
  }

  const vorlage = (html.match(/<template id="newsCardTemplate">([\s\S]*?)<\/template>/) || [])[1] || '';
  if (!vorlage) {
    fehler('Markup', 'Die Vorlage newsCardTemplate fehlt, buildCard kann keine Karte bauen.');
  } else {
    const inVorlage = new Set(
      [...vorlage.matchAll(/class="([^"]+)"/g)].flatMap((m) => m[1].split(/\s+/))
    );
    const ausVorlage = new Set(
      [...js.matchAll(/\$\(\s*['"`]\.([A-Za-z0-9_-]+)['"`]\s*,\s*node\s*\)/g)].map((m) => m[1])
    );
    for (const klasse of ausVorlage) {
      if (!inVorlage.has(klasse)) {
        fehler('Markup', `buildCard greift auf .${klasse} zu, die Vorlage hat die Klasse nicht.`);
      }
    }
  }
}

// ------------------------------------------------------------- 2. Inhalte
// Ein fehlendes Feld in content.json soll die Seite nicht mehr umwerfen, aber
// auffallen soll es trotzdem.
const PFLICHTFELDER = ['id', 'headline', 'teaser', 'category', 'status', 'publishedAt'];
const BOESE_SCHEMATA = /^\s*(javascript|data|vbscript):/i;

function jedeAdresse(wert, pfad, gefunden) {
  if (typeof wert === 'string') {
    if (/^(https?:|mailto:|tel:|javascript:|data:|vbscript:)/i.test(wert.trim())) {
      gefunden.push({ pfad, wert: wert.trim() });
    }
    return;
  }
  if (Array.isArray(wert)) {
    wert.forEach((w, i) => jedeAdresse(w, `${pfad}[${i}]`, gefunden));
    return;
  }
  if (wert && typeof wert === 'object') {
    for (const [k, v] of Object.entries(wert)) jedeAdresse(v, `${pfad}.${k}`, gefunden);
  }
}

function pruefeInhalte() {
  if (!gibtEs('content.json')) {
    fehler('Inhalte', 'content.json fehlt.');
    return null;
  }
  let daten;
  try {
    daten = JSON.parse(lies('content.json'));
  } catch (error) {
    fehler('Inhalte', `content.json ist kein gueltiges JSON: ${error.message}`);
    return null;
  }

  for (const schluessel of ['articles', 'events', 'sport', 'ads', 'meta']) {
    if (!(schluessel in daten)) hinweis('Inhalte', `Block ${schluessel} fehlt in content.json.`);
  }

  const artikel = Array.isArray(daten.articles) ? daten.articles : [];
  if (!artikel.length) fehler('Inhalte', 'content.json enthaelt keine Artikel.');

  const kennungen = new Map();
  artikel.forEach((a, i) => {
    const name = a.id || `Artikel ${i}`;
    for (const feld of PFLICHTFELDER) {
      if (a[feld] === undefined || a[feld] === '') fehler('Inhalte', `${name}: Feld ${feld} fehlt.`);
    }
    if (a.publishedAt !== undefined && Number.isNaN(new Date(a.publishedAt).getTime())) {
      fehler('Inhalte', `${name}: publishedAt ist kein lesbares Datum (${a.publishedAt}).`);
    }
    if (a.status && !['published', 'draft', 'review'].includes(a.status)) {
      hinweis('Inhalte', `${name}: unbekannter status ${a.status}.`);
    }
    if (a.id) kennungen.set(a.id, (kennungen.get(a.id) || 0) + 1);
  });
  for (const [kennung, anzahl] of kennungen) {
    if (anzahl > 1) fehler('Inhalte', `Kennung ${kennung} kommt ${anzahl} mal vor, Verweise sind mehrdeutig.`);
  }

  (Array.isArray(daten.events) ? daten.events : []).forEach((e, i) => {
    const name = e.title || `Termin ${i}`;
    if (!e.start) fehler('Inhalte', `${name}: start fehlt.`);
    else if (Number.isNaN(new Date(e.start).getTime())) {
      fehler('Inhalte', `${name}: start ist kein lesbares Datum (${e.start}).`);
    }
  });

  const adressen = [];
  jedeAdresse(daten, 'content', adressen);
  for (const { pfad, wert } of adressen) {
    if (BOESE_SCHEMATA.test(wert)) {
      fehler('Inhalte', `${pfad} traegt ein ausfuehrbares Schema: ${wert.slice(0, 48)}`);
    }
  }
  return daten;
}

// --------------------------------------------------- 3. Oertliche Verweise
function pruefeOertlicheVerweise() {
  if (!gibtEs('index.html')) return;
  const html = lies('index.html');
  const verweise = [...html.matchAll(/(?:src|href)="([^"]+)"/g)].map((m) => m[1]);
  for (const verweis of verweise) {
    if (/^(https?:|mailto:|tel:|data:|#|\/\/)/i.test(verweis)) continue;
    const ziel = verweis.split(/[?#]/)[0];
    if (!ziel) continue;
    if (!gibtEs(ziel)) fehler('Verweise', `index.html verweist auf ${ziel}, die Datei fehlt.`);
  }
}

// ------------------------------------------------------------- 4. PHP
function pruefePhp() {
  const dateien = [...dateienUnter('wordpress', '.php')];
  if (!dateien.length) return;
  try {
    execFileSync('php', ['--version'], { stdio: 'ignore' });
  } catch {
    hinweis('PHP', `${dateien.length} PHP-Dateien nicht geprueft, php steht hier nicht bereit.`);
    return;
  }
  for (const datei of dateien) {
    try {
      execFileSync('php', ['-l', datei], { stdio: 'pipe' });
    } catch (error) {
      const meldung = String(error.stdout || error.message).split('\n')[0];
      fehler('PHP', `${datei.replace(wurzel + '/', '')}: ${meldung}`);
    }
  }
}

// ------------------------------------------------- 5. Platzhalterinhalte
const PLATZHALTER = [
  [/lorem ipsum/i, 'Lorem ipsum'],
  [/\bTODO\b/, 'TODO'],
  [/\bFIXME\b/, 'FIXME'],
  [/\bxxx+\b/i, 'XXX'],
  [/\bmusterm(ann|ustermann)\b/i, 'Mustermann'],
  [/beispiel@/i, 'beispiel@'],
];

function pruefePlatzhalter() {
  for (const datei of ['content.json', 'index.html']) {
    if (!gibtEs(datei)) continue;
    const text = lies(datei);
    for (const [muster, name] of PLATZHALTER) {
      if (muster.test(text)) fehler('Platzhalter', `${datei} enthaelt ${name}.`);
    }
  }
}

// ------------------------------------------------------- 6. Pruefsummen
// docs/SHA256SUMS.txt ist der Integritaetsnachweis der Lieferstaende. Er lief
// schon einmal still auseinander, weil die Summendatei eine Minute vor der
// Datei committet wurde, die sie beschreibt.
function pruefePruefsummen() {
  const liste = 'docs/SHA256SUMS.txt';
  if (!gibtEs(liste)) return;
  for (const zeile of lies(liste).split('\n')) {
    const treffer = zeile.trim().match(/^([0-9a-f]{64})\s+(.+)$/);
    if (!treffer) continue;
    const [, erwartet, datei] = treffer;
    if (!gibtEs(datei)) {
      fehler('Pruefsummen', `${datei} ist in ${liste} aufgefuehrt, liegt aber nicht vor.`);
      continue;
    }
    let ist;
    try {
      ist = execFileSync('sha256sum', [join(wurzel, datei)]).toString().split(/\s+/)[0];
    } catch {
      hinweis('Pruefsummen', 'sha256sum steht hier nicht bereit, Abgleich uebersprungen.');
      return;
    }
    if (ist !== erwartet) {
      fehler('Pruefsummen', `${datei} weicht ab: hinterlegt ${erwartet.slice(0, 12)}…, tatsaechlich ${ist.slice(0, 12)}…`);
    }
  }
}

pruefeMarkupGegenCode();
pruefeInhalte();
pruefeOertlicheVerweise();
pruefePhp();
pruefePlatzhalter();
pruefePruefsummen();

const fehlerZahl = befunde.filter((b) => b.schwere === 'fehler').length;
const hinweisZahl = befunde.length - fehlerZahl;

if (alsJson) {
  console.log(JSON.stringify({ fehler: fehlerZahl, hinweise: hinweisZahl, befunde }, null, 2));
} else {
  if (!befunde.length) {
    console.log('Alle Pruefungen bestanden.');
  } else {
    for (const b of befunde) {
      console.log(`${b.schwere === 'fehler' ? 'FEHLER ' : 'Hinweis'}  ${b.bereich.padEnd(12)} ${b.text}`);
    }
    console.log(`\n${fehlerZahl} Fehler, ${hinweisZahl} Hinweise.`);
  }
}

process.exit(fehlerZahl ? 1 : 0);
