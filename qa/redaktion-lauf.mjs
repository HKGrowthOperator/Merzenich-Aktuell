#!/usr/bin/env node
/**
 * Merzenich Aktuell - Pruefung eines Redaktionslaufs der KI-Redaktion (27.10.2026).
 *
 * Prueft maschinell, was docs/redaktion/CLAUDE-LAUF.md und docs/redaktion/SCHREIBREGELN.md
 * verlangen und was deploy/meldungen.mjs, deploy/termine.mjs und qa/pruefung.mjs nicht
 * pruefen. Laeuft im Redaktionslauf nach der Kette und vor jedem Commit; nicht in der CI.
 *
 * Aufruf:
 *   node qa/redaktion-lauf.mjs                                  Bestand pruefen
 *   node qa/redaktion-lauf.mjs --datum 2026-10-27 --lauf morgens  zusaetzlich Laufabschluss
 *   Optionen: --wurzel <repo>  --json  --streng  --hilfe
 *
 * Fehler (Exit 1):
 *   A Meldungen: Pflichtform je Artikeltyp; quelle/weitereQuellen mit https und stand
 *     (YYYY-MM-DD, nicht in der Zukunft); Slug eindeutig ueber alle Pakete; bildklasse in
 *     deploy/bildklassen.json; keine Quell-URL doppelt; in Claude-Paketen (Dateiname enthaelt
 *     "recherche-claude") mindestens zwei verschiedene Quell-URLs ausserhalb merzenich-aktuell.de
 *     (Ausnahme: Feld quellenausnahme mit Begruendung). Claude-Pakete vor REGEL_AB melden das
 *     nur als Hinweis (--streng: Fehler). Updates in Nicht-Claude-Dateien prueft das Skript nicht.
 *     Ab REGEL_V3_AB (29.10.2026) in Claude-Paketen ausserdem: mindestens drei verschiedene
 *     Quellen, Standardtext mindestens 450 Woerter, und die Artikelseite zeigt keine Ortsansicht,
 *     wenn die Bildklasse Motive nennt (Bild muss zum Thema passen). Davor nur Hinweise.
 *   B Termine: Pflichtfelder, quelle.url https, quelle.stand, start/ende mit Zeitzone,
 *     ende nicht vor start, Slug eindeutig; im Paket des Laufs kein Start vor --datum.
 *     Claude-Termine gehen direkt online; "entwurf": true nur, wenn der Termin in die Freigabe soll.
 *   C Umkreis: ort aus der Liste in deploy/umkreis.mjs, url https, datum YYYY-MM-DD, titel,
 *     rubrik, quelle.
 *   D Register: node deploy/register-pflege.mjs --check muss gruen sein (ohne --datum ist
 *     „offen.md nicht aktuell“ nur ein Hinweis, weil offen.md erst in Phase 5 entsteht); mit --datum/--lauf:
 *     Dossier docs/redaktion/dossiers/<datum>-claude-<lauf>.md vorhanden, research_runs mit
 *     date und edition, latest_dossier zeigt darauf, latest.md nennt das Dossier.
 *     Ausserdem Fehler: Meldungspaket des Laufs fehlt und das Dossier meldet keinen Nullfund.
 *   E Geheimnisse in docs/redaktion und inhalte (Passwortzuweisungen, Tokens).
 * Hinweise: Muster „Strasse + Hausnummer“ in Blaulicht-Texten, nur eine Quell-Domain,
 *   E-Mail-Adressen ausser Funktionsadressen in Dateien mit „claude“ im Namen, doppelte Umkreis-URLs.
 */
import { readFileSync, existsSync, readdirSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { spawnSync } from 'node:child_process';

const args = process.argv.slice(2);
const opt = (n) => { const i = args.indexOf(n); return i >= 0 ? args[i + 1] : undefined; };
if (args.includes('--hilfe') || args.includes('--help')) {
  console.log(readFileSync(fileURLToPath(import.meta.url), 'utf8').split('*/')[0].replace(/^#!.*\n\/\*\*?/, '').replace(/^ \* ?/gm, ''));
  process.exit(0);
}
const wurzel = resolve(opt('--wurzel') || join(dirname(fileURLToPath(import.meta.url)), '..'));
const DATUM = opt('--datum');
const LAUF = opt('--lauf');
const STRENG = args.includes('--streng');
const ALS_JSON = args.includes('--json');
// Ab diesem Paketdatum gilt die Zwei-Quellen-Regel als Fehler (Regel seit 26./27.10.2026).
const REGEL_AB = '2026-10-27';
// Ab diesem Paketdatum (Entscheidung Luis Kummer 28.10.2026, Claude-Beitraege gehen direkt live):
// Standardtext mindestens 450 Woerter, mindestens drei verschiedene Quellen, Bild passend zum Thema.
const REGEL_V3_AB = '2026-10-29';
const V3_STANDARD_WOERTER = 450;

const ARTIKELTYPEN = { kurzmeldung: [80, 2, 2], standard: [300, 4, 4], vertiefung: [450, 5, 5], hintergrund: [700, 7, 5] };
const RESSORTS = ['blaulicht', 'sport', 'rathaus', 'leben', 'wirtschaft', 'menschen', 'vereine'];
const ORTSTEILE = ['merzenich', 'golzheim', 'girbelsrath', 'morschenich', 'buergewald'];
const UMKREIS_ORTE = ['Düren', 'Niederzier', 'Nörvenich', 'Elsdorf', 'Kerpen', 'Kreis Düren'];
const TERMIN_PFLICHT = ['slug', 'titel', 'start', 'ort', 'ortsteil', 'kategorie', 'veranstalter', 'beschreibung', 'absaetze', 'quelle'];
const EIGEN = /(^|\.)merzenich-aktuell\.de$/;

const fehler = [];
const hinweise = [];
const F = (b, t) => fehler.push(`${b}: ${t}`);
const H = (b, t) => hinweise.push(`${b}: ${t}`);

const heute = DATUM || new Intl.DateTimeFormat('sv-SE', { timeZone: 'Europe/Berlin' }).format(new Date());
const plusTag = (d, n) => { const x = new Date(`${d}T12:00:00Z`); x.setUTCDate(x.getUTCDate() + n); return x.toISOString().slice(0, 10); };
const ISO_TAG = /^\d{4}-\d{2}-\d{2}$/;
const MIT_ZONE = /T\d{2}:\d{2}(:\d{2})?([+-]\d{2}:\d{2}|Z)$/;
const host = (u) => { try { return new URL(u).hostname.toLowerCase(); } catch { return ''; } };
const woerter = (m) => [m.dek, ...(m.absaetze || [])].join(' ').trim().split(/\s+/).filter(Boolean).length;
const paketDatum = (datei) => (/^(\d{4}-\d{2}-\d{2})/.exec(datei) || [])[1] || '';

function jsonDateien(rel) {
  const ordner = join(wurzel, rel);
  if (!existsSync(ordner)) { H(rel, 'Ordner fehlt'); return []; }
  return readdirSync(ordner).filter((d) => d.endsWith('.json')).sort().map((d) => {
    try { return { datei: d, daten: JSON.parse(readFileSync(join(ordner, d), 'utf8')) }; }
    catch (e) { F(`${rel}/${d}`, `kein gueltiges JSON (${e.message})`); return null; }
  }).filter(Boolean);
}

function quelleOk(b, q, name) {
  if (!q || typeof q !== 'object') { F(b, `${name} fehlt`); return false; }
  if (!q.name) F(b, `${name}.name fehlt`);
  if (!/^https:\/\//.test(q.url || '')) F(b, `${name}.url ohne https`);
  if (!ISO_TAG.test(q.stand || '')) F(b, `${name}.stand nicht im Format YYYY-MM-DD (${q.stand})`);
  else if (q.stand > plusTag(heute, 1)) F(b, `${name}.stand ${q.stand} liegt in der Zukunft`);
  return true;
}

// ------------------------------------------------------------------ A Meldungen
let bildklassen = {};
try { bildklassen = JSON.parse(readFileSync(join(wurzel, 'deploy', 'bildklassen.json'), 'utf8')).klassen || {}; }
catch { H('deploy/bildklassen.json', 'nicht lesbar, Bildklassen ungeprueft'); }
// Bild passend zum Thema (Entscheidung Luis Kummer 28.10.2026): zeigt die erzeugte Artikelseite eine
// Ortsansicht, obwohl die Bildklasse Motive nennt, fehlt ein passendes Foto. Fehler ab REGEL_V3_AB.
const SEITE = join(wurzel, 'chatgpt-site');
const seitenOrdner = existsSync(SEITE) ? readdirSync(SEITE, { withFileTypes: true }).filter((d) => d.isDirectory()).map((d) => d.name) : [];
function bildPruefen(b, m, streng) {
  const ordner = seitenOrdner.find((o) => existsSync(join(SEITE, o, m.slug || '-', 'index.html')));
  if (!ordner) { H(b, 'Artikelseite nicht gefunden (Kette gelaufen?), Bild ungeprueft'); return; }
  const html = readFileSync(join(SEITE, ordner, m.slug, 'index.html'), 'utf8');
  if (!/data-bildstufe="O"/.test(html)) return;
  const motive = (bildklassen[m.bildklasse] || {}).motive || [];
  if (!m.bildklasse) (streng ? F : H)(b, 'Ortsansicht statt Themenbild: bildklasse fehlt');
  else if (motive.length) (streng ? F : H)(b, `Ortsansicht statt Themenbild: kein gesichtetes Foto fuer ${m.bildklasse} (${motive.join('/')}); Foto importieren oder passendere Bildklasse`);
  else H(b, `Ortsansicht: Bildklasse ${m.bildklasse} hat kein Motiv; Themenfoto ergaenzen, wenn es eines gibt`);
}
const slugs = new Map();
for (const { datei, daten } of jsonDateien('inhalte/meldungen')) {
  const claude = datei.includes('recherche-claude');
  const regel = claude && (paketDatum(datei) >= REGEL_AB || STRENG);
  const v3 = claude && (paketDatum(datei) >= REGEL_V3_AB || STRENG);
  const v2 = Number(daten.redaktionsstandard) >= 2;
  for (const m of daten.meldungen || []) {
    const b = `meldungen/${datei}/${m.slug || '?'}`;
    if (slugs.has(m.slug)) F(b, `Slug doppelt (auch in ${slugs.get(m.slug)})`); else slugs.set(m.slug, datei);
    if (!claude && !v2) continue; // Altbestand ohne V2 prueft deploy/meldungen.mjs
    if (!RESSORTS.includes(m.ressort)) F(b, `Ressort ${m.ressort} unbekannt`);
    if (!ORTSTEILE.includes(m.ortsteil)) F(b, `Ortsteil ${m.ortsteil} unbekannt`);
    if (m.bildklasse && Object.keys(bildklassen).length && !bildklassen[m.bildklasse]) F(b, `Bildklasse ${m.bildklasse} fehlt in deploy/bildklassen.json`);
    if (Number.isNaN(Date.parse(m.datum)) || !MIT_ZONE.test(m.datum || '')) F(b, `datum ohne Zeitzone oder unlesbar (${m.datum})`);
    else if (m.datum.slice(0, 10) > plusTag(heute, 1)) F(b, `datum ${m.datum} liegt mehr als einen Tag in der Zukunft`);
    if (v2) {
      const typ = ARTIKELTYPEN[m.artikeltyp];
      if (!typ) F(b, `artikeltyp ${m.artikeltyp} unbekannt`);
      else {
        const [w, a, f] = typ;
        if (woerter(m) < w) F(b, `${m.artikeltyp} mit ${woerter(m)} Woertern (mindestens ${w})`);
        if ((m.absaetze || []).length < a) F(b, `${m.artikeltyp} mit ${(m.absaetze || []).length} Absaetzen (mindestens ${a})`);
        if ((m.fakten || []).length < f) F(b, `${m.artikeltyp} mit ${(m.fakten || []).length} Fakten (mindestens ${f})`);
      }
    }
    quelleOk(b, m.quelle, 'quelle');
    (m.weitereQuellen || []).forEach((q, i) => quelleOk(b, q, `weitereQuellen[${i}]`));
    const alle = [m.quelle, ...(m.weitereQuellen || [])].filter((q) => q && q.url);
    const urls = alle.map((q) => q.url.replace(/[#?].*$/, '').replace(/\/$/, ''));
    if (new Set(urls).size < urls.length) (claude ? F : H)(b, 'dieselbe Quell-URL mehrfach eingetragen');
    const fremd = [...new Map(alle.filter((q) => !EIGEN.test(host(q.url))).map((q) => [q.url.replace(/[#?].*$/, '').replace(/\/$/, ''), q])).values()];
    if (claude && fremd.length < 2 && !m.quellenausnahme) {
      (regel ? F : H)(b, `nur ${fremd.length} Quelle(n) ausser merzenich-aktuell.de; Zwei-Quellen-Regel (oder quellenausnahme mit Begruendung)`);
    }
    if (claude && m.artikeltyp !== 'kurzmeldung' && new Set(fremd.map((q) => host(q.url))).size === 1) H(b, `alle Quellen von ${host(fremd[0].url)}; unabhaengige Zweitquelle pruefen`);
    if (claude) {
      // Ab REGEL_V3_AB: laenger, mehr Quellen (SCHREIBREGELN.md Abschnitt Laenge und Quellen).
      const alleUrls = new Set(urls);
      if (alleUrls.size < 3 && !m.quellenausnahme) (v3 ? F : H)(b, `${alleUrls.size} Quelle(n); mindestens drei verschiedene Quellen (oder quellenausnahme mit Begruendung)`);
      if (m.artikeltyp === 'standard' && woerter(m) < V3_STANDARD_WOERTER) (v3 ? F : H)(b, `Standardtext mit ${woerter(m)} Woertern; Claude-Standard mindestens ${V3_STANDARD_WOERTER}`);
      if (m.artikeltyp === 'kurzmeldung' && m.ressort !== 'blaulicht' && !m.service) H(b, 'Kurzmeldung ausserhalb Blaulicht/Service; Standardtext ist der Regelfall');
      bildPruefen(b, m, v3);
    }
    if (m.ressort === 'blaulicht') {
      const treffer = [m.dek, ...(m.absaetze || [])].join(' ').match(/\b[A-ZÄÖÜ][a-zäöüß]+(straße|str\.|weg|gasse|platz|allee|ring)\s+\d+[a-z]?\b/);
      if (treffer && !/^(Land|Bundes|Kreis)stra/.test(treffer[0])) H(b, `moegliche Hausnummer im Blaulicht-Text: "${treffer[0]}"`);
    }
  }
}

// ------------------------------------------------------------------ B Termine
const terminSlugs = new Map();
for (const { datei, daten } of jsonDateien('inhalte/termine')) {
  const claude = datei.includes('recherche-claude');
  const laufPaket = DATUM && LAUF && datei === `${DATUM}-recherche-claude-${LAUF}.json`;
  for (const t of daten.termine || []) {
    const b = `termine/${datei}/${t.slug || '?'}`;
    if (terminSlugs.has(t.slug)) F(b, `Slug doppelt (auch in ${terminSlugs.get(t.slug)}); die spaetere Datei ueberschreibt still`);
    else terminSlugs.set(t.slug, datei);
    if (!claude) continue; // Altbestand: deploy/termine.mjs
    const fehlt = TERMIN_PFLICHT.filter((k) => t[k] === undefined || t[k] === '' || (Array.isArray(t[k]) && !t[k].length));
    if (fehlt.length) F(b, `Pflichtfelder fehlen: ${fehlt.join(', ')}`);
    // Seit 28.10.2026 gehen Claude-Termine direkt online (Entscheidung Luis Kummer); "entwurf": true
    // setzt der Lauf nur, wenn er unsicher ist und der Termin gezielt in die Freigabe soll.
    if (t.entwurf === true) H(b, 'Termin als Entwurf markiert: geht nicht online, sondern in die Freigabe');
    if (!MIT_ZONE.test(t.start || '') || Number.isNaN(Date.parse(t.start))) F(b, `start ohne Zeitzone oder unlesbar (${t.start})`);
    if (t.ende && (!MIT_ZONE.test(t.ende) || Date.parse(t.ende) < Date.parse(t.start))) F(b, `ende ohne Zeitzone oder vor start (${t.ende})`);
    if (laufPaket && String(t.start).slice(0, 10) < DATUM) F(b, `Termin liegt vor dem Laufdatum ${DATUM}`);
    if (t.quelle && typeof t.quelle === 'object') quelleOk(b, t.quelle, 'quelle');
    else F(b, 'quelle muss {name, url, stand} sein');
  }
}

// ------------------------------------------------------------------ C Umkreis
const umkreisUrls = new Map();
for (const { datei, daten } of jsonDateien('inhalte/umkreis')) {
  if (!Array.isArray(daten.eintraege)) { F(`umkreis/${datei}`, 'eintraege fehlt'); continue; }
  daten.eintraege.forEach((e, i) => {
    const b = `umkreis/${datei}#${i + 1}`;
    if (!UMKREIS_ORTE.includes(e.ort)) F(b, `Ort "${e.ort}" nicht erlaubt (${UMKREIS_ORTE.join(', ')})`);
    for (const k of ['rubrik', 'titel', 'quelle']) if (!e[k]) F(b, `${k} fehlt`);
    if (!/^https:\/\//.test(e.url || '')) F(b, 'url ohne https');
    if (!ISO_TAG.test(e.datum || '')) F(b, `datum nicht YYYY-MM-DD (${e.datum})`);
    if (e.url && umkreisUrls.has(e.url)) H(b, `URL schon in ${umkreisUrls.get(e.url)}`); else if (e.url) umkreisUrls.set(e.url, datei);
  });
}

// ------------------------------------------------------------------ D Register und Laufabschluss
const pflege = join(wurzel, 'deploy', 'register-pflege.mjs');
if (existsSync(pflege)) {
  const r = spawnSync(process.execPath, [pflege, '--check'], { cwd: wurzel, encoding: 'utf8' });
  if (r.status !== 0) {
    const zeilen = (r.stdout + r.stderr).trim().split('\n');
    const verstoesse = zeilen.filter((z) => /^Register: [^0-9]/.test(z) && !/Themen,/.test(z));
    // offen.md haengt vom Bestand ab und wird erst in Phase 5 neu geschrieben: ohne Laufparameter nur Hinweis.
    if (verstoesse.length || DATUM) F('register', `deploy/register-pflege.mjs --check: ${zeilen.slice(-3).join(' | ')}`);
    else H('register', 'offen.md nicht aktuell; node deploy/register-pflege.mjs ausfuehren (Phase 5)');
  }
} else H('register', 'deploy/register-pflege.mjs fehlt, Register ungeprueft');

if (DATUM || LAUF) {
  if (!DATUM || !LAUF) F('lauf', '--datum und --lauf nur gemeinsam');
  else {
    const dossierRel = `dossiers/${DATUM}-claude-${LAUF}.md`;
    const dossier = join(wurzel, 'docs', 'redaktion', dossierRel);
    if (!existsSync(dossier)) F('lauf', `Dossier docs/redaktion/${dossierRel} fehlt`);
    let reg = {};
    try { reg = JSON.parse(readFileSync(join(wurzel, 'docs', 'redaktion', 'register.json'), 'utf8')); } catch { F('lauf', 'register.json nicht lesbar'); }
    const lauf = (reg.research_runs || []).find((r) => r.date === DATUM && r.edition === LAUF && r.by === 'Claude');
    if (!lauf) F('lauf', `research_runs ohne Eintrag date=${DATUM}, edition=${LAUF}, by=Claude`);
    else if (!lauf.implementation_commit) H('lauf', 'research_runs-Eintrag ohne implementation_commit (nur bei Nullfund zulaessig)');
    if (reg.latest_dossier !== dossierRel) F('lauf', `latest_dossier zeigt auf ${reg.latest_dossier}, erwartet ${dossierRel}`);
    const latest = existsSync(join(wurzel, 'docs', 'redaktion', 'latest.md')) ? readFileSync(join(wurzel, 'docs', 'redaktion', 'latest.md'), 'utf8') : '';
    if (!latest.includes(`${DATUM}-claude-${LAUF}.md`)) F('lauf', 'latest.md nennt das Dossier des Laufs nicht');
    if (['morgens', 'nachmittags'].includes(LAUF)) {
      const paket = join(wurzel, 'inhalte', 'meldungen', `${DATUM}-recherche-claude-${LAUF}.json`);
      const text = existsSync(dossier) ? readFileSync(dossier, 'utf8') : '';
      const ohnePaketBegruendet = /Nullfund|0 \| keine neue|Neue Meldungen \| 0/.test(text);
      if (!existsSync(paket) && !ohnePaketBegruendet) F('lauf', `kein Meldungspaket ${DATUM}-recherche-claude-${LAUF}.json und kein Nullfund im Dossier`);
    }
  }
}

// ------------------------------------------------------------------ E Geheimnisse
const MUSTER = [
  [/passw(?:or)?[dt]\s*[:=]\s*\S{4,}/i, 'Passwortzuweisung'],
  [/\bBearer\s+[A-Za-z0-9._-]{16,}/, 'Bearer-Token'],
  [/\bghp_[A-Za-z0-9]{20,}/, 'GitHub-Token'],
  [/\bAKIA[0-9A-Z]{16}\b/, 'AWS-Schluessel'],
  [/\bsk-(?:ant-)?[A-Za-z0-9_-]{20,}/, 'API-Schluessel'],
  [/wp-login\.php\?[^\s"]*(?:pwd|log)=/i, 'Login-Parameter'],
];
const ERLAUBTE_MAIL = /^(info@kbs-management\.tv|[^@\s]+@merzenich-aktuell\.de|buergermeister@gemeinde-merzenich\.de|amt51@kreis-dueren\.de|vhs-rur-eifel@dueren\.de)$/i;
function durchsuche(rel, endungen) {
  const ordner = join(wurzel, rel);
  if (!existsSync(ordner)) return;
  for (const e of readdirSync(ordner, { withFileTypes: true })) {
    const pfad = `${rel}/${e.name}`;
    if (e.isDirectory()) { durchsuche(pfad, endungen); continue; }
    if (!endungen.some((x) => e.name.endsWith(x))) continue;
    const text = readFileSync(join(wurzel, pfad), 'utf8');
    for (const [re, name] of MUSTER) if (re.test(text)) F(pfad, `${name} gefunden`);
    const mails = [...new Set(text.match(/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/g) || [])].filter((m) => !ERLAUBTE_MAIL.test(m));
    if (mails.length && /claude/.test(e.name)) H(pfad, `E-Mail-Adressen: ${mails.slice(0, 3).join(', ')}${mails.length > 3 ? ' …' : ''}`);
  }
}
durchsuche('docs/redaktion', ['.md', '.json']);
durchsuche('inhalte', ['.json']);

// ------------------------------------------------------------------ Ausgabe
if (ALS_JSON) console.log(JSON.stringify({ fehler, hinweise }, null, 2));
else {
  for (const f of fehler) console.log(`Fehler  ${f}`);
  for (const h of hinweise) console.log(`Hinweis  ${h}`);
  console.log(`\n${fehler.length} Fehler, ${hinweise.length} Hinweise.`);
}
process.exit(fehler.length ? 1 : 0);
