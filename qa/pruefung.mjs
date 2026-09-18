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
import { join, dirname, resolve, isAbsolute } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const alsJson = process.argv.includes('--json');
const befunde = [];

const melde = (schwere, bereich, text) => befunde.push({ schwere, bereich, text });
const fehler = (bereich, text) => melde('fehler', bereich, text);
const hinweis = (bereich, text) => melde('hinweis', bereich, text);

const lies = (pfad) => readFileSync(isAbsolute(pfad) ? pfad : join(wurzel, pfad), 'utf8');
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

// ------------------------------------------------- 7. Service-Inhalte
// Immobilien, Stellen, Termine und Bilder: nichts Erfundenes, nichts
// Abgelaufenes, kein Bild ohne Nachweis. Prueft die Quellen des Generators
// (site-source/content) und die JSON-Datenstaende der ausgelieferten Seite.
const DEMO_MARKER = [
  [/\bdemo(?:daten|-daten|eintrag|-eintrag)?\b/i, 'Demo'],
  [/\bbeispieldaten\b/i, 'Beispieldaten'],
  [/\btesteintrag\b/i, 'Testeintrag'],
  [/\bdummy\b/i, 'Dummy'],
  [/\bplatzhaltertext\b/i, 'Platzhaltertext'],
];

function frontmatter(text) {
  const m = text.match(/^---\n([\s\S]*?)\n---/);
  if (!m) return {};
  const out = {};
  for (const zeile of m[1].split('\n')) {
    const t = zeile.match(/^([a-zA-Z_]+):\s*(.*)$/);
    if (t) out[t[1]] = t[2].replace(/^["']|["']$/g, '');
  }
  return out;
}

function pruefeServiceInhalte() {
  const jetzt = Date.now();
  const md = (ordner) => [...dateienUnter(ordner, '.md')];
  const slugs = new Map();

  // Artikel: Bild nur mit Credit, keine doppelten Slugs, keine Demo-Marker.
  for (const datei of md('site-source/content/artikel')) {
    const text = lies(datei);
    const name = datei.replace(wurzel + '/', '');
    const fm = frontmatter(text);
    if (fm.slug) slugs.set(fm.slug, (slugs.get(fm.slug) || 0) + 1);
    const bild = text.match(/^image:\n((?:[ \t]+.*\n?)+)/m);
    if (bild && /^\s+src:\s*\S/m.test(bild[1]) && !/^\s+credit:\s*\S/m.test(bild[1])) {
      fehler('Service', `${name}: Bild ohne credit.`);
    }
    for (const [muster, label] of DEMO_MARKER) if (muster.test(text)) fehler('Service', `${name}: enthaelt ${label}.`);
  }
  for (const [slug, n] of slugs) if (n > 1) fehler('Service', `Artikel-Slug ${slug} kommt ${n} mal vor.`);

  // Stellen: abgelaufene duerfen nicht mehr veroeffentlicht sein.
  for (const datei of md('site-source/content/stellen')) {
    const fm = frontmatter(lies(datei));
    const name = datei.replace(wurzel + '/', '');
    if (fm.validUntil && !Number.isNaN(Date.parse(fm.validUntil)) && Date.parse(fm.validUntil) < jetzt && fm.draft !== 'true') {
      fehler('Service', `${name}: Stelle ist seit ${fm.validUntil} abgelaufen, steht aber noch auf veroeffentlicht.`);
    }
  }

  // Termine: ein Termin ohne start ist keiner; start muss lesbar sein.
  for (const datei of md('site-source/content/termine')) {
    const fm = frontmatter(lies(datei));
    const name = datei.replace(wurzel + '/', '');
    if (!fm.start) fehler('Service', `${name}: start fehlt.`);
    else if (Number.isNaN(Date.parse(fm.start))) fehler('Service', `${name}: start ist kein lesbares Datum (${fm.start}).`);
  }

  // Ausgelieferte Seite: kein vergangener Termin unter "kommend", keine
  // Demo-Marker, JSON-Datenstaende lesbar.
  const termine = 'chatgpt-site/termine/index.html';
  if (gibtEs(termine)) {
    const html = lies(termine);
    // Jede Zeile traegt data-start/data-end; die Seite blendet Vergangenes
    // erst im Browser aus. Zur Bauzeit darf trotzdem nichts Vergangenes
    // hinein, das aelter ist als der Datenstand selbst.
    const stand = Date.parse((html.match(/data-today datetime="([^"]+)"/) || [])[1] || '') || jetzt;
    let zeilen = 0;
    for (const m of html.matchAll(/<[^>]*data-event-row[^>]*data-end="([^"]+)"[^>]*>/g)) {
      zeilen++;
      const ende = Date.parse(m[1]);
      if (Number.isNaN(ende)) fehler('Service', `termine/: data-end ist kein lesbares Datum (${m[1]}).`);
      else if (ende < stand) fehler('Service', `termine/: Termin mit Ende ${m[1]} liegt vor dem Datenstand der Seite, steht aber unter kommend.`);
    }
    if (zeilen === 0) hinweis('Service', 'termine/: keine data-event-row gefunden - Terminliste leer oder Markup geaendert.');
  }
  for (const datei of ['chatgpt-site/index.html', termine, 'chatgpt-site/immobilien/index.html', 'chatgpt-site/jobs/index.html']) {
    if (!gibtEs(datei)) continue;
    const text = lies(datei);
    for (const [muster, label] of DEMO_MARKER) if (muster.test(text)) fehler('Service', `${datei} enthaelt ${label}.`);
  }
  for (const datei of dateienUnter('chatgpt-site/api', '.json')) {
    try { JSON.parse(lies(datei)); } catch (e) { fehler('Service', `${datei.replace(wurzel + '/', '')}: kein gueltiges JSON (${e.message}).`); }
  }

  // Sport-Datenstand: Pflichtteile vorhanden, nichts Erfundenes im Ergebnis.
  const sport = 'chatgpt-site/api/sport-current.json';
  if (gibtEs(sport)) {
    try {
      const d = JSON.parse(lies(sport));
      for (const k of ['generated', 'sourceUrl', 'table']) if (!(k in d)) fehler('Service', `${sport}: ${k} fehlt.`);
      if (d.lastMatch && d.lastMatch.confirmed === false && d.lastMatch.score) {
        fehler('Service', `${sport}: lastMatch traegt ein Ergebnis, ist aber nicht bestaetigt.`);
      }
      if (Array.isArray(d.table)) for (const r of d.table) if (!r || !r.team) fehler('Service', `${sport}: Tabellenzeile ohne team.`);
    } catch { /* oben gemeldet */ }
  }
}

// ------------------------------------------------------------ 8. JavaScript
function pruefeJavaScript() {
  const dateien = [
    ...dateienUnter('chatgpt-site/assets', '.js'),
    ...dateienUnter('wordpress/plugin/merzenich-aktuell-core/assets', '.js'),
    ...dateienUnter('wordpress/theme/merzenich-aktuell/assets/js', '.js'),
  ];
  for (const datei of dateien) {
    try { execFileSync('node', ['--check', datei], { stdio: 'pipe' }); }
    catch (error) { fehler('JavaScript', `${datei.replace(wurzel + '/', '')}: ${String(error.stderr || error.message).split('\n')[0]}`); }
  }
}


// ------------------------------------------------------------ 9. Interne Links
// Jeder interne Verweis (href="/...") muss auf eine ausgelieferte Seite oder
// Datei zeigen. Das Audit vom 17.09. fand drei Themenseiten, die ins Leere liefen.
function pruefeInterneLinks() {
  const site = join(wurzel, 'chatgpt-site');
  const tot = new Map();
  for (const datei of dateienUnter('chatgpt-site', '.html')) {
    const html = lies(datei);
    for (const m of html.matchAll(/href="(\/[^"#?]*)/g)) {
      const h = m[1];
      if (h.startsWith('//') || h.startsWith('/api/') || h.startsWith('/admin') || h.startsWith('/redaktion')) continue;
      if (/\.(?:xml|ics|json|css|js|svg|png|webp|jpg|jpeg|pdf|txt|ico|woff2?)$/.test(h)) continue;
      const ziel = join(site, h);
      const ok = (existsSync(ziel) && (h.endsWith('/') ? existsSync(join(ziel, 'index.html')) : true)) || existsSync(ziel + '/index.html') || existsSync(ziel.replace(/\/$/, '') + '.html');
      if (!ok) { if (!tot.has(h)) tot.set(h, []); tot.get(h).push(datei.replace(wurzel + '/', '')); }
    }
  }
  for (const [h, seiten] of [...tot.entries()].slice(0, 40)) fehler('Links', `${h} fuehrt ins Leere (${seiten.length}x, z. B. ${seiten[0]}).`);
}

pruefeMarkupGegenCode();
pruefeInterneLinks();
pruefeInhalte();
pruefeServiceInhalte();
pruefeJavaScript();

// ------------------------------------------ 9. Laufzeit-Umbau bleibt aus
// Beschluss 17.09.: Der ausgelieferte Stand ist der sichtbare Stand. Die drei
// Layer-Skripte duerfen kein Markup mehr umschreiben; ihr Schalter steht auf
// false. Faellt er weg oder steht er auf true, sieht der Leser wieder etwas
// anderes als im HTML steht - und genau das war die Ursache fuer wandernde
// Bilder und doppelte Ueberschriften. Hinweis, kein Fehler: eine bewusste
// Rueckkehr soll moeglich bleiben, aber nie unbemerkt.
function pruefeLaufzeitUmbau() {
  for (const datei of ['editorial-audit.js', 'homepage-polish.js', 'content-refresh-2026-09-17.js']) {
    const rel = `chatgpt-site/assets/${datei}`;
    if (!gibtEs(rel)) continue;
    const text = lies(rel);
    const m = /const\s+LAUFZEIT_UMBAU\s*=\s*(true|false)/.exec(text);
    if (!m) hinweis('Laufzeit', `${datei}: Schalter LAUFZEIT_UMBAU fehlt - schreibt die Datei wieder Markup um?`);
    else if (m[1] === 'true') hinweis('Laufzeit', `${datei}: LAUFZEIT_UMBAU steht auf true, der Browser weicht wieder vom ausgelieferten HTML ab.`);
  }
}

pruefeOertlicheVerweise();
pruefeLaufzeitUmbau();
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
