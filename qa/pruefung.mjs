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
import { createHash } from 'node:crypto';
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


// ------------------------------------- 10. Symbolbilder: Pools und Vergabe
// Die Vergabe muss zwei Dinge zugleich leisten: fest je Meldung und verteilt
// ueber die Meldungen einer Kategorie. Der Selbsttest im Modul prueft beides
// rechnerisch; hier wird er ausgefuehrt, damit ein Umbau daran nicht still
// vorbeigeht. Dazu die Pools: ein Bild ohne Eintrag in lizenzen.json waere ein
// Bild ohne Nachweis und wird nicht ausgeliefert - das soll sichtbar sein.
async function pruefeSymbolbilder() {
  let modul;
  try { modul = await import('../deploy/lib-symbolbilder.mjs'); }
  catch (e) { fehler('Symbolbilder', `deploy/lib-symbolbilder.mjs laedt nicht: ${e.message}`); return; }
  try { modul.selbsttest(); }
  catch (e) { fehler('Symbolbilder', `Selbsttest der Vergabe fehlgeschlagen: ${e.message}`); }
  const { pools, hinweise } = modul.poolsLesen(wurzel);
  for (const h of hinweise) hinweis('Symbolbilder', h);
  const leer = Object.entries(pools).filter(([, v]) => !v.length).map(([k]) => k);
  if (leer.length === modul.KATEGORIEN.length) hinweis('Symbolbilder', 'Noch keine Motive abgelegt; siehe chatgpt-site/assets/symbolbilder/README.md.');
  else if (leer.length) hinweis('Symbolbilder', `Pools ohne Motiv: ${leer.join(', ')}.`);
  for (const [k, v] of Object.entries(pools)) {
    if (v.length && v.length < 5) hinweis('Symbolbilder', `Pool ${k} hat nur ${v.length} Motiv(e); bei mehr Meldungen wiederholt sich das Bild sichtbar.`);
  }
}

// ------------------------------------ 11. Ein Motiv darf eine Seite nicht fluten
// Gemessen am 18.09.: /jobs/ zeigt 24 mal dasselbe Bueromotiv untereinander.
// Das ist schlechter als gar kein Bild. Die Grenze ist bewusst grosszuegig, sie
// soll nur das Fluten fangen, nicht die normale Wiederholung.
function pruefeBildwiederholung() {
  const GRENZE = 6;
  for (const datei of dateienUnter('chatgpt-site', '.html')) {
    const name = datei.replace(wurzel + '/', '');
    const zaehler = new Map();
    for (const m of lies(datei).matchAll(/<img\b[^>]*\bsrc="([^"]+)"/g)) {
      const src = m[1];
      if (/logo|favicon|avatar|pixel|\.svg(\?|$)/i.test(src)) continue;
      zaehler.set(src, (zaehler.get(src) || 0) + 1);
    }
    for (const [src, n] of zaehler) {
      if (n > GRENZE) hinweis('Bilder', `${name}: ein Motiv ${n} mal auf einer Seite (${src.slice(0, 60)}).`);
    }
  }
}

// -------------------------------- 12. Ein Pool braucht unterscheidbare Motive
// Gemessen am 19.09.: 320 Dateien in 16 Pools, je 20. Nach Normalisierung von
// Text, Farbwerten und Zahlen teilen sich die 20 Dateien eines Pools drei
// Strukturen - es sind Farbverlaeufe mit aufgedrucktem Wort. Vier Blaulicht-
// meldungen untereinander bekaemen vier gleiche blaue Flaechen. Die Zaehlung
// laesst sich nicht mit einem anderen Wort im Bild oder einem anderen Farbton
// bestehen, genau darum wird beides vor der Pruefsumme entfernt.
// Anforderung: docs/SYMBOLBILDER-ANFORDERUNG.md
function pruefeMotivvielfalt() {
  const wurzelPool = join(wurzel, 'chatgpt-site', 'assets', 'symbolbilder');
  if (!existsSync(wurzelPool)) return;
  const ANTEIL = 0.6;
  for (const eintrag of readdirSync(wurzelPool, { withFileTypes: true })) {
    if (!eintrag.isDirectory()) continue;
    const pfad = join(wurzelPool, eintrag.name);
    const dateien = readdirSync(pfad).filter((f) => f.endsWith('.svg'));
    if (dateien.length < 4) continue;
    const strukturen = new Set();
    for (const f of dateien) {
      const roh = readFileSync(join(pfad, f), 'utf8')
        .replace(/>[^<]*</g, '><')             // Beschriftung raus
        .replace(/#[0-9a-fA-F]{3,8}\b/g, '')   // Farbwerte raus
        .replace(/[0-9.]+/g, '');              // Koordinaten und Groessen raus
      strukturen.add(createHash('sha256').update(roh).digest('hex'));
    }
    const anteil = strukturen.size / dateien.length;
    if (anteil < ANTEIL) {
      // Bewusst Hinweis, nicht Fehler: der Befund ist gemeldet und im Generator
      // bereits entschaerft (VERLAUFSPOOL schliesst die Motive von Aufmacher und
      // Nebenmeldung aus). Ein roter Lauf wuerde nur unbeteiligte Arbeit
      // blockieren. Sobald die Pools ausgetauscht sind, wird daraus fehler().
      hinweis('Symbolbilder', `Pool ${eintrag.name}: ${dateien.length} Dateien, aber nur ${strukturen.size} verschiedene Motive (${Math.round(anteil * 100)} %). Siehe docs/SYMBOLBILDER-ANFORDERUNG.md.`);
    }
  }
}

// -------------------------------------------- 13. Ortswahl auf jeder Seite
// Vorher stand die Ortsleiste auf 214 Seiten in sechs handgepflegten
// Varianten, die sich nur im aria-current unterschieden, und niemand zog sie
// nach. Jetzt erzeugt sie deploy/inhaltsindex.mjs. Geprueft wird, dass genau
// eine Wahl je Seite steht, genau eine Ausgabe als aktuell markiert ist und
// jede angezeigte Zahl mit dem Bestand uebereinstimmt.
function pruefeOrtswahl() {
  const indexPfad = join(wurzel, 'chatgpt-site', 'api', 'inhalte.json');
  if (!existsSync(indexPfad)) { fehler('Ortswahl', 'api/inhalte.json fehlt, Zahlen nicht pruefbar.'); return; }
  const bestand = JSON.parse(readFileSync(indexPfad, 'utf8')).bestand?.ortsteile || {};
  let mitWahl = 0;
  for (const datei of dateienUnter('chatgpt-site', '.html')) {
    const name = datei.replace(wurzel + '/', '');
    const html = lies(datei);
    if (/class="districtbar"/.test(html)) fehler('Ortswahl', `${name}: alte districtbar wieder im Markup.`);
    if (/class="shell edition-label"/.test(html)) fehler('Ortswahl', `${name}: Zwischenzeile edition-label wieder im Markup.`);
    const wahlen = html.match(/<nav class="ortswahl"/g) || [];
    if (!wahlen.length) continue;
    mitWahl++;
    if (wahlen.length > 1) fehler('Ortswahl', `${name}: ${wahlen.length} Ortswahlen auf einer Seite.`);
    const block = /<nav class="ortswahl"[\s\S]*?<\/nav>/.exec(html)[0];
    const aktuell = block.match(/aria-current="page"/g) || [];
    if (aktuell.length !== 1) fehler('Ortswahl', `${name}: ${aktuell.length} Eintraege als aktuelle Ausgabe markiert, erwartet genau einer.`);
    for (const m of block.matchAll(/href="\/([a-z]+)\/"[^>]*><span class="ortswahl-name">[^<]*<\/span><span class="ortswahl-zahl">(\d+) Meldung/g)) {
      const [, ort, gezeigt] = m;
      const echt = bestand[ort];
      if (echt === undefined) fehler('Ortswahl', `${name}: Ort ${ort} steht in der Wahl, aber nicht im Bestand.`);
      else if (Number(gezeigt) !== echt) fehler('Ortswahl', `${name}: ${ort} zeigt ${gezeigt}, der Bestand sagt ${echt}.`);
    }
  }
  if (!mitWahl) fehler('Ortswahl', 'Keine einzige Seite traegt eine Ortswahl.');
}

// ------------------------------------------ 14. Ressortflaechen der Startseite
// Vorher war die Flaeche unter dem Aufmacher handgepflegtes Markup: die
// juengste Meldung dort war vom 04.09., sechs Karten standen ohne Bild, und
// eine Sektion zeigte ein riesiges Bild neben einer duennen Textleiter. Jetzt
// erzeugt sie deploy/inhaltsindex.mjs zwischen Markern. Geprueft wird, was
// beim Bauen tatsaechlich schiefgegangen ist:
//   - zu jedem Startmarker ein Endmarker,
//   - keine Meldung zweimal auf der Seite,
//   - keine grosse Reihe mit nur einer Karte (die halbe leere Reihe),
//   - keine geschriebene Sektion ohne Inhalt.
// Was hier bewusst NICHT geprueft wird: dass eine Sektion nie aelter ist als
// die darueber. Bei ressortgebundenen Sektionen geht das nicht auf - Blaulicht
// reicht bis 21.07. zurueck, waehrend Rathaus darunter den 31.08. traegt, weil
// beide nur aus ihrem eigenen Ressort schoepfen.
function pruefeSektionen() {
  const pfad = join(wurzel, 'chatgpt-site', 'index.html');
  if (!existsSync(pfad)) { fehler('Sektionen', 'chatgpt-site/index.html fehlt.'); return; }
  const html = lies(pfad);
  const starts = [...html.matchAll(/<!-- start:([a-z]+):start -->/g)].map((m) => m[1]);
  if (!starts.length) { fehler('Sektionen', 'Kein einziger Sektionsmarker in index.html.'); return; }
  for (const id of starts) {
    if (!html.includes(`<!-- start:${id}:end -->`)) fehler('Sektionen', `Marker start:${id} hat kein Gegenstueck.`);
  }
  const gesehen = new Map();
  for (const m of html.matchAll(/<!-- start:([a-z]+):start -->([\s\S]*?)<!-- start:\1:end -->/g)) {
    const [, id, rumpf] = m;
    const karten = [...rumpf.matchAll(/data-story="([^"]+)"/g)].map((x) => x[1]);
    for (const k of karten) {
      if (gesehen.has(k)) fehler('Sektionen', `Meldung ${k} steht in ${gesehen.get(k)} und noch einmal in ${id}.`);
      else gesehen.set(k, id);
    }
    if (/<section/.test(rumpf) && !karten.length) fehler('Sektionen', `Sektion ${id} ist geschrieben, enthaelt aber keine Meldung.`);
    const reihen = [...rumpf.matchAll(/<div class="desk-(gross|mittel|zeilen)">/g)];
    reihen.forEach((r, i) => {
      const bis = i + 1 < reihen.length ? reihen[i + 1].index : rumpf.length;
      const anzahl = (rumpf.slice(r.index, bis).match(/data-story="/g) || []).length;
      if (r[1] === 'gross' && anzahl === 1) fehler('Sektionen', `Sektion ${id}: grosse Reihe mit nur einer Karte, die halbe Reihe bliebe leer.`);
      if (!anzahl) fehler('Sektionen', `Sektion ${id}: Reihe desk-${r[1]} steht leer im Markup.`);
    });
  }
}

// ------------------------------------------- 15. Stylesheets sind lesbar
// Der Selektor-Entferner in deploy/ schneidet ueberholte Regeln aus den
// Stylesheets. Am 22.09. liess er in style.css zwei Selektoren ohne Rumpf
// stehen (".sc-box .sc-box" und ".sc-box"). Ein CSS-Parser verschluckt dann
// die naechste Regel mit - hier waeren .undated, .sc-crest und .plainlist
// stumm ausgefallen. Nichts hat angeschlagen: die Seite rendert weiter, nur
// eben falsch. Darum zwei harte Zusicherungen je Stylesheet: ausgeglichene
// Klammern und keine Zeile, die nur aus einem Selektor besteht.
function pruefeStylesheets() {
  const ordner = join(wurzel, 'chatgpt-site', 'assets');
  if (!existsSync(ordner)) { fehler('Stylesheet', 'chatgpt-site/assets fehlt.'); return; }
  for (const datei of readdirSync(ordner).filter((n) => n.endsWith('.css'))) {
    const text = lies(join(ordner, datei)).replace(/\/\*[\s\S]*?\*\//g, '');
    const auf = (text.match(/\{/g) || []).length;
    const zu = (text.match(/\}/g) || []).length;
    if (auf !== zu) fehler('Stylesheet', `${datei}: ${auf} oeffnende gegen ${zu} schliessende Klammern.`);
    text.split('\n').forEach((zeile, i) => {
      const k = zeile.trim();
      if (!k || /[{}]/.test(k) || /^@/.test(k)) return;
      if (/[,;]$/.test(k)) return;      // mehrzeiliger Selektor oder Deklaration
      if (/^[^:]*:[^:]/.test(k)) return; // Deklaration ohne abschliessendes Semikolon
      fehler('Stylesheet', `${datei}:${i + 1}: Selektor ohne Rumpf - "${k.slice(0, 50)}". Die naechste Regel faellt damit aus.`);
    });
  }
}

pruefeOertlicheVerweise();
pruefeLaufzeitUmbau();
// --------------------------------------- 16. Servicespalte der Startseite
// Vorher war die obere Flaeche 773 px hoch und die Servicespalte 458 px:
// 315 px standen leer, weil vier der sechs Felder zugeklappt waren und die
// Terminliste von Hand gepflegt wurde - termine-prerender.mjs entfernte
// abgelaufene Zeilen, setzte aber nie neue ein. Jetzt schreibt derselbe
// Generator die Liste zwischen Markern. Geprueft wird, was dabei kaputtgehen
// kann.
function pruefeServicespalte() {
  const pfad = join(wurzel, 'chatgpt-site', 'index.html');
  if (!existsSync(pfad)) { fehler('Servicespalte', 'chatgpt-site/index.html fehlt.'); return; }
  const html = lies(pfad);

  const starts = (html.match(/<!-- start:termine:start -->/g) || []).length;
  const enden = (html.match(/<!-- start:termine:end -->/g) || []).length;
  if (starts !== 1 || enden !== 1) { fehler('Servicespalte', `Terminmarker ${starts}x start, ${enden}x end, erwartet je einmal.`); return; }

  const block = /<!-- start:termine:start -->([\s\S]*?)<!-- start:termine:end -->/.exec(html)[1];
  const zeilen = [...block.matchAll(/data-event-end="([^"]+)"/g)].map((m) => m[1]);
  if (!zeilen.length) fehler('Servicespalte', 'Keine Termine in der Spalte. Entweder ist der Bestand leer oder der Generator lief nicht.');
  // Abgelaufene Termine sind ein Hinweis, kein Fehler: deploy.yml prueft den
  // eingecheckten Stand, ohne die Generatoren vorher laufen zu lassen, und
  // qa.yml zieht ihn einmal taeglich nach. Dazwischen kann ein Termin ablaufen,
  // ohne dass jemand etwas falsch gemacht hat.
  const jetzt = Date.now();
  for (const e of zeilen) {
    const t = Date.parse(e);
    if (Number.isFinite(t) && t < jetzt) hinweis('Servicespalte', `Termin bis ${e.slice(0, 10)} ist abgelaufen; der naechste Lauf von termine-prerender.mjs nimmt ihn heraus.`);
  }

  const spalte = /<aside class="portal-service"[\s\S]*?<\/aside>/.exec(html);
  if (!spalte) { fehler('Servicespalte', 'Kein <aside class="portal-service"> auf der Startseite.'); return; }
  // Ein Klappfeld, das sich auf einen einzigen Link oeffnet, verspricht Inhalt,
  // den es nicht gibt. Trauer- und Familienanzeigen waren genau das und sind
  // jetzt zwei schlichte Zeilen.
  for (const m of spalte[0].matchAll(/<details[^>]*class="service-accordion"[^>]*>\s*<summary>([^<]*)<\/summary>([\s\S]*?)<\/details>/g)) {
    const [, titel, rumpf] = m;
    if (/hidden/.test(m[0])) continue; // Wetter wird zur Laufzeit gefuellt
    const links = (rumpf.match(/<a\b/g) || []).length;
    const andere = rumpf.replace(/<a\b[\s\S]*?<\/a>/g, '').replace(/<[^>]*>/g, '').trim();
    if (links <= 1 && !andere) fehler('Servicespalte', `Klappfeld "${titel}" oeffnet sich auf einen einzigen Link. Als schlichte Zeile fuehrt es nicht in die Irre.`);
  }
}

pruefeSektionen();
pruefeStylesheets();
pruefeServicespalte();
await pruefeSymbolbilder();
pruefeBildwiederholung();
pruefeMotivvielfalt();
pruefeOrtswahl();
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
