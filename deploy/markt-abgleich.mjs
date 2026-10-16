#!/usr/bin/env node
/**
 * Merzenich Aktuell - Stellen- und Immobilienmarkt abgleichen.
 *
 * Die Stellen in market.json (Wurzelverzeichnis) sind redaktionell ausgewaehlt
 * (Stand 16.09.2026). Dieses Skript prueft jede einzelne Stelle bei ihrer
 * Quelle nach und nimmt keine neuen auf:
 *
 *   - Bundesagentur fuer Arbeit: Jobdetail ueber die oeffentliche Schnittstelle
 *     der Jobboerse (rest.arbeitsagentur.de, v4/jobdetails/<Referenznummer als
 *     Base64>). 200 = online, 404 = nicht mehr veroeffentlicht.
 *   - andere Quellen (Karriereseiten): die Anzeige selbst; 404/410 = entfernt.
 *
 * Online: checkedAt der Stelle wird auf jetzt gesetzt. Entfernt: die Stelle
 * faellt aus der Liste und steht mit Datum in meta.jobsRemoved. Unklar
 * (Zeitueberschreitung, 5xx, gesperrt): die Stelle bleibt mit altem Datum.
 * Ist mehr als die Haelfte unklar oder angeblich entfernt, schreibt das
 * Skript nichts, damit ein Ausfall der Schnittstelle den Markt nicht leert.
 *
 * Immobilien (seit 16.10.2026): jede Anzeige wird bei ihrer Quelle (immowelt,
 * ImmobilienScout24) abgerufen. 200 auf derselben Anzeige = online, 404/410 =
 * entfernt (faellt heraus, steht in meta.propertiesRemoved), alles andere
 * (gesperrt, Fehler, Weiterleitung auf eine andere Seite) = unklar, die Anzeige
 * behaelt ihr Datum. Ist bei den Immobilien mehr als die Haelfte unklar oder
 * entfernt, bleiben sie unveraendert; die Stellen werden trotzdem geschrieben.
 * Entscheidungen in deploy/lib-markt-abgleich.mjs (Test qa/markt-abgleich-test.mjs).
 *
 * Laeuft nur in GitHub Actions (der Container der Redaktion erreicht die
 * Quellen nicht). Aufruf: node deploy/markt-abgleich.mjs [--dry]
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { klassifiziere, schreibbar } from './lib-markt-abgleich.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
// Quelle ist market.json im Wurzelverzeichnis; markt-prerender kopiert sie nach /api/.
const pfad = join(wurzel, 'market.json');
const trocken = process.argv.includes('--dry');
const BA = 'https://rest.arbeitsagentur.de/jobboerse/jobsuche-service/pc/v4/jobdetails/';
// Oeffentlicher Schluessel der Jobboerse (dokumentiert u. a. bei bund.dev).
const BA_KEY = 'jobboerse-jobsuche';
const UA = 'MerzenichAktuell-Marktabgleich/1.0 (https://merzenich-aktuell.de)';

const daten = JSON.parse(readFileSync(pfad, 'utf8'));
const jetzt = new Date();
// Ortszeit wie die uebrigen Zeitstempel in market.json (+02:00 im Sommer).
const zeitstempel = (() => {
  const teile = Object.fromEntries(new Intl.DateTimeFormat('en-CA', { timeZone: 'Europe/Berlin', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23', timeZoneName: 'longOffset' })
    .formatToParts(jetzt).map((p) => [p.type, p.value]));
  const off = (teile.timeZoneName || 'GMT+01:00').replace('GMT', '') || '+00:00';
  return `${teile.year}-${teile.month}-${teile.day}T${teile.hour}:${teile.minute}:${teile.second}${off}`;
})();

async function abrufen(url, headers = {}) {
  for (let versuch = 1; versuch <= 2; versuch++) {
    try {
      const r = await fetch(url, { headers: { 'User-Agent': UA, ...headers }, redirect: 'follow', signal: AbortSignal.timeout(20000) });
      if (r.status >= 500 && versuch === 1) { await new Promise((ok) => setTimeout(ok, 1500)); continue; }
      return { status: r.status, url: r.url || url };
    } catch (e) {
      if (versuch === 2) return { status: 'fehler: ' + (e.cause?.code || e.name), url };
    }
  }
  return { status: 'fehler', url };
}

function refnrAus(url) {
  const m = /arbeitsagentur\.de\/jobsuche\/jobdetail\/([^/?#]+)/.exec(String(url || ''));
  return m ? decodeURIComponent(m[1]) : null;
}

async function pruefe(job) {
  const refnr = refnrAus(job.sourceUrl);
  const { status } = refnr
    ? await abrufen(BA + Buffer.from(refnr).toString('base64'), { 'X-API-Key': BA_KEY, Accept: 'application/json' })
    : await abrufen(job.sourceUrl, { Accept: 'text/html' });
  return { ergebnis: klassifiziere(status), status };
}

async function pruefeAngebot(angebot) {
  const { status, url } = await abrufen(angebot.sourceUrl, { Accept: 'text/html', 'Accept-Language': 'de-DE,de;q=0.9' });
  return { ergebnis: klassifiziere(status, angebot.sourceUrl, url), status, url };
}

const jobs = daten.jobs || [];
const befunde = [];
for (const job of jobs) {
  befunde.push({ job, ...(await pruefe(job)) });
  await new Promise((ok) => setTimeout(ok, 400));
}

const zahl = (e) => befunde.filter((b) => b.ergebnis === e).length;
for (const b of befunde) console.log(`${b.ergebnis.padEnd(9)} ${String(b.status).padEnd(18)} ${b.job.municipality.padEnd(10)} ${b.job.title} (${b.job.employer || ''})`);
console.log(`Stellen: ${jobs.length}, online ${zahl('online')}, entfernt ${zahl('entfernt')}, unklar ${zahl('unklar')}.`);

const angebote = daten.properties || [];
const angebotBefunde = [];
for (const angebot of angebote) {
  angebotBefunde.push({ angebot, ...(await pruefeAngebot(angebot)) });
  await new Promise((ok) => setTimeout(ok, 800));
}
const zahlA = (e) => angebotBefunde.filter((b) => b.ergebnis === e).length;
for (const b of angebotBefunde) console.log(`${b.ergebnis.padEnd(9)} ${String(b.status).padEnd(18)} ${b.angebot.municipality.padEnd(10)} ${b.angebot.title} (${b.angebot.sourceName})${b.ergebnis === 'unklar' && b.url !== b.angebot.sourceUrl ? ' -> ' + b.url : ''}`);
console.log(`Immobilien: ${angebote.length}, online ${zahlA('online')}, entfernt ${zahlA('entfernt')}, unklar ${zahlA('unklar')}.`);
const immobilienOk = schreibbar(angebotBefunde.map((b) => b.ergebnis));
if (!immobilienOk.ok) console.error(`Immobilien: ${immobilienOk.grund}, Quelle vermutlich gesperrt oder gestoert; Immobilien bleiben unveraendert.`);

// Stellen: mehr als die Haelfte unklar (Schnittstelle gestoert) oder "entfernt"
// (ein falscher Endpunkt antwortet fuer alles mit 404 und wuerde den Markt
// leeren): nichts schreiben, das entscheidet die Redaktion.
const stellenOk = schreibbar(befunde.map((b) => b.ergebnis));
if (!stellenOk.ok) {
  console.error(`Stellen: ${stellenOk.grund}, bitte von Hand pruefen; market.json bleibt unveraendert.`);
  process.exit(1);
}
if (trocken) { console.log('Trockenlauf: nichts geschrieben.'); process.exit(0); }

for (const b of befunde) if (b.ergebnis === 'online') b.job.checkedAt = zeitstempel;
const entfernt = befunde.filter((b) => b.ergebnis === 'entfernt');
daten.jobs = befunde.filter((b) => b.ergebnis !== 'entfernt').map((b) => b.job);
daten.meta = daten.meta || {};
daten.meta.jobsCheckedAt = zeitstempel;
daten.meta.jobsRemoved = [
  ...entfernt.map((b) => ({ id: b.job.id, title: b.job.title, employer: b.job.employer || '', removedAt: zeitstempel, status: b.status })),
  ...(daten.meta.jobsRemoved || []),
].slice(0, 100);

if (immobilienOk.ok) {
  for (const b of angebotBefunde) if (b.ergebnis === 'online') b.angebot.checkedAt = zeitstempel;
  const weg = angebotBefunde.filter((b) => b.ergebnis === 'entfernt');
  daten.properties = angebotBefunde.filter((b) => b.ergebnis !== 'entfernt').map((b) => b.angebot);
  daten.meta.propertiesCheckedAt = zeitstempel;
  daten.meta.propertiesRemoved = [
    ...weg.map((b) => ({ id: b.angebot.id, title: b.angebot.title, removedAt: zeitstempel, status: b.status })),
    ...(daten.meta.propertiesRemoved || []),
  ].slice(0, 100);
  console.log(`Immobilien: ${daten.properties.length} behalten, ${weg.length} entfernt.`);
}
writeFileSync(pfad, JSON.stringify(daten, null, 2) + '\n');
console.log(`market.json geschrieben: ${daten.jobs.length} Stellen, ${entfernt.length} entfernt.`);
