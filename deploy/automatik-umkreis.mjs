#!/usr/bin/env node
/**
 * Merzenich Aktuell - Polizeimeldungen aus dem Umkreis automatisch als Umkreis-Eintrag (26.10.2026).
 *
 * Liest die Rohdaten des Quellenabrufs (deploy/quellen-abruf.mjs, imports/quellen/polizei.json):
 * die Listenseiten des Presseportals (Gesamtliste der Polizei Dueren plus die Ortslisten
 * Merzenich und Ortsteile). Jede Listenzeile besteht aus Datum/Uhrzeit, Dienststelle,
 * Schlagzeile ("POL-DN: ...") und dem Vorspann, der mit dem Ort beginnt ("Niederzier (ots) - ...").
 *
 * Daraus entstehen Eintraege fuer den Block "Direkter Umkreis" der Startseite
 * (inhalte/umkreis/<heute>-polizei-automatik.json, Schema wie deploy/umkreis.mjs):
 *   ort     Dueren, Niederzier, Noervenich, Elsdorf, Kerpen oder "Kreis Dueren" (uebrige
 *           Kommunen des Kreises; der Ort steht dann vor der Schlagzeile)
 *   titel   die amtliche Schlagzeile der Polizei ohne "POL-DN:" - kein eigener Text
 *   url     die Einzelmeldung im Presseportal
 *   datum   Veroeffentlichungstag laut Liste
 *
 * Regeln:
 * - Nur Umkreis: Meldungen, deren Vorspann Merzenich, Golzheim, Girbelsrath, Morschenich oder
 *   Buergewald nennt, werden NICHT angelegt. Sie gehoeren als eigene Meldung in die Redaktion
 *   (Claude-Lauf) und stehen dafuer in der Zusammenfassung.
 * - Nur Neues: Die URL steht noch in keiner Datei unter inhalte/umkreis/ und nicht im
 *   Stand deploy/automatik-stand.json; hoechstens FENSTER_TAGE alt.
 * - Ueberspringen statt raten: ohne Datum, ohne Link, ohne erkennbaren Ort oder wenn der
 *   Ort ausserhalb des Kreises Dueren liegt (z. B. Polizei Aachen in der Ortsliste).
 * - Keine Namen: Polizeischlagzeilen nennen keine Privatpersonen; der Vorspann wird nicht
 *   uebernommen.
 *
 * Der Umkreis-Block erscheint auf merzenich-aktuell.de ohne Freigabe (das Theme liest den
 * Stand der Startseite aus dem Repository, siehe wordpress/theme/.../inc/ma21.php). Deshalb
 * stehen hier nur Schlagzeile, Ort, Datum und Link der amtlichen Quelle, nie ein eigener Text.
 *
 * Aufruf:
 *   node deploy/automatik-umkreis.mjs              neue Eintraege anlegen
 *   node deploy/automatik-umkreis.mjs --trocken    nur anzeigen, nichts schreiben
 *   node deploy/automatik-umkreis.mjs --heute=2026-10-26   Stichtag (Tests)
 */
import { readFileSync, writeFileSync, existsSync, readdirSync, appendFileSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const FENSTER_TAGE = 3;
/** Orte des Umkreis-Blocks (deploy/umkreis.mjs) */
export const UMKREIS_ORTE = ['Düren', 'Niederzier', 'Nörvenich', 'Elsdorf', 'Kerpen'];
/** Weitere Kommunen des Kreises Dueren: Eintrag unter "Kreis Dueren", Ort vor der Schlagzeile. */
export const KREIS_DUEREN = ['Aldenhoven', 'Heimbach', 'Hürtgenwald', 'Inden', 'Jülich', 'Kreuzau', 'Langerwehe', 'Linnich', 'Nideggen', 'Titz', 'Vettweiß', 'Kreis Düren'];
/** Kernorte: nie als Umkreis, sondern Hinweis an die Redaktion. */
export const KERNORTE = ['Merzenich', 'Golzheim', 'Girbelsrath', 'Morschenich', 'Bürgewald'];

const DATUM_RE = /^(\d{2})\.(\d{2})\.(\d{4}) – (\d{2}):(\d{2})$/;
const isoTag = (d, m, y) => `${y}-${m}-${d}`;
const berlinHeute = (jetzt = new Date()) => new Intl.DateTimeFormat('en-CA', { timeZone: 'Europe/Berlin', year: 'numeric', month: '2-digit', day: '2-digit' }).format(jetzt);
const tageZwischen = (a, b) => Math.round((Date.parse(`${b}T12:00:00Z`) - Date.parse(`${a}T12:00:00Z`)) / 86400000);
const json = (p, ersatz) => { try { return JSON.parse(readFileSync(p, 'utf8')); } catch { return ersatz; } };

/* ------------------------------------------------------------ Liste zerlegen */

/** Schlagzeile ohne Kuerzel der Dienststelle ("POL-DN: ", "POL-AC: "). */
export const schlagzeile = (t) => String(t || '').replace(/^POL-[A-Z]{2,4}:\s*/, '').replace(/\s+/g, ' ').trim();

/**
 * Ortsangabe des Vorspanns ("Düren / Merzenich (ots) - ...") -> Liste der genannten Orte.
 * Bindestrich-Orte ("Merzenich-Golzheim", "Kerpen-Buir") zaehlen beide Teile.
 */
export function orteAusVorspann(vorspann) {
  const m = /^(.{2,80}?)\s*\(ots\)/.exec(String(vorspann || '').trim());
  if (!m) return [];
  return m[1].split(/\s*[\/,+]\s*|\s+und\s+|\s*-\s*/).map((s) => s.replace(/^(Kreis|Stadt|Gemeinde)\s+(?!Düren)/, '').trim()).filter(Boolean);
}

/** Orte -> { ort, praefix } fuer den Umkreis-Block oder { grund } zum Ueberspringen. */
export function umkreisOrt(orte) {
  if (!orte.length) return { grund: 'kein Ort im Vorspann' };
  if (orte.some((o) => KERNORTE.includes(o))) return { grund: 'Kernort (Merzenich oder Ortsteil): gehört als Meldung in die Redaktion', kernort: true };
  const direkt = orte.find((o) => UMKREIS_ORTE.includes(o));
  if (direkt) return { ort: direkt, praefix: '' };
  const kreis = orte.filter((o) => KREIS_DUEREN.includes(o));
  if (kreis.length) {
    const namen = kreis.filter((o) => o !== 'Kreis Düren');
    return { ort: 'Kreis Düren', praefix: namen.length ? `${namen.join('/')}: ` : '' };
  }
  return { grund: `Ort „${orte.join('/')}“ liegt außerhalb des Kreises Düren` };
}

/**
 * Eine Listenseite des Presseportals (Text + Links aus deploy/quellen-abruf.mjs) in Meldungen zerlegen:
 * [{ datum: 'YYYY-MM-DD', zeit, dienststelle, titel, vorspann, url }].
 */
export function listeZerlegen(seite) {
  if (!seite || seite.status !== 200 || !seite.text) return [];
  const z = seite.text.split('\n').map((s) => s.trim());
  const links = new Map();
  for (const l of seite.links || []) if (/\/blaulicht\/pm\/\d+\/\d+/.test(l.href) && l.text) links.set(l.text.replace(/\s+/g, ' ').trim(), l.href.split('#')[0]);
  const aus = [];
  for (let i = 0; i < z.length; i++) {
    const d = DATUM_RE.exec(z[i]);
    if (!d) continue;
    let j = i + 1;
    const dienststelle = z[j] || ''; j++;
    while (j < z.length && /^\d+$/.test(z[j])) j++; // Bildanzahl
    const titelRoh = z[j] || ''; j++;
    if (!/^POL-/.test(titelRoh)) continue;
    const vorspann = z[j] && z[j] !== 'mehr' && !DATUM_RE.test(z[j]) ? z[j] : '';
    const url = links.get(titelRoh) || [...links].find(([t]) => t.startsWith(titelRoh.slice(0, 60)))?.[1] || '';
    aus.push({ datum: isoTag(d[1], d[2], d[3]), zeit: `${d[4]}:${d[5]}`, dienststelle, titel: schlagzeile(titelRoh), titelRoh, vorspann, url });
  }
  return aus;
}

/** Alle Meldungen aller Listen, je URL einmal (Ortslisten und Gesamtliste ueberschneiden sich). */
export function meldungenLesen(polizei) {
  const je = new Map();
  for (const l of polizei?.listen || []) for (const m of listeZerlegen(l)) if (m.url && !je.has(m.url)) je.set(m.url, m);
  return [...je.values()].sort((a, b) => b.datum.localeCompare(a.datum) || b.zeit.localeCompare(a.zeit));
}

/** Umkreis-Eintrag (Schema deploy/umkreis.mjs) oder { grund }. */
export function eintragBauen(m) {
  if (!m.url) return { grund: 'kein Link zur Einzelmeldung' };
  if (!m.titel || m.titel.length < 8) return { grund: 'keine Schlagzeile' };
  if (!/^Polizei Düren$/.test(m.dienststelle)) return { grund: `Dienststelle „${m.dienststelle}“ (nur Polizei Düren)` };
  const o = umkreisOrt(orteAusVorspann(m.vorspann));
  if (o.grund) return o;
  // Steht der Ort schon in der Schlagzeile, kein Praefix.
  const praefix = o.praefix && new RegExp(o.praefix.slice(0, -2).split('/').join('|'), 'i').test(m.titel) ? '' : o.praefix;
  return { eintrag: { ort: o.ort, rubrik: 'Blaulicht', titel: `${praefix}${m.titel}`, url: m.url, datum: m.datum, quelle: m.dienststelle } };
}

/* ------------------------------------------------------------ Bestand */

/** URLs, die schon in inhalte/umkreis/*.json oder als Quelle einer Meldung stehen. */
export function gedeckteUrls(basis) {
  const urls = new Set();
  const ordner = join(basis, 'inhalte', 'umkreis');
  for (const d of existsSync(ordner) ? readdirSync(ordner).filter((n) => n.endsWith('.json')) : []) {
    for (const e of json(join(ordner, d), {}).eintraege || []) if (e.url) urls.add(e.url.split('#')[0]);
  }
  const meldungen = join(basis, 'inhalte', 'meldungen');
  for (const d of existsSync(meldungen) ? readdirSync(meldungen).filter((n) => n.endsWith('.json')) : []) {
    for (const m of json(join(meldungen, d), {}).meldungen || []) for (const q of [m.quelle, ...(m.weitereQuellen || [])]) if (q?.url) urls.add(q.url.split('#')[0]);
  }
  return urls;
}

/* ------------------------------------------------------------ Lauf */

function lauf() {
  const args = process.argv.slice(2);
  const trocken = args.includes('--trocken');
  const heute = (args.find((a) => a.startsWith('--heute=')) || '').slice(8) || berlinHeute();
  const polizei = json(join(wurzel, 'imports', 'quellen', 'polizei.json'), {});
  const standDatei = join(wurzel, 'deploy', 'automatik-stand.json');
  const stand = json(standDatei, { feuerwehr: {} });
  stand.umkreis ||= {};
  const gedeckt = gedeckteUrls(wurzel);

  const neu = []; const uebersprungen = []; const redaktion = [];
  for (const m of meldungenLesen(polizei)) {
    const alter = tageZwischen(m.datum, heute);
    if (!(alter >= 0 && alter <= FENSTER_TAGE)) continue;
    if (!m.url || gedeckt.has(m.url) || stand.umkreis[m.url]) continue;
    const r = eintragBauen(m);
    if (r.eintrag) neu.push(r.eintrag);
    else if (r.kernort) redaktion.push(m);
    else uebersprungen.push({ titel: m.titel, url: m.url, grund: r.grund });
  }

  const zielName = `${heute}-polizei-automatik.json`;
  const ziel = join(wurzel, 'inhalte', 'umkreis', zielName);
  for (const e of neu) console.log(`neu          ${e.datum}  ${e.ort.padEnd(12)}  ${e.titel}`);
  for (const m of redaktion) console.log(`Redaktion    ${m.datum}  Kernort: ${m.titel}  ${m.url}`);
  for (const u of uebersprungen) console.log(`übersprungen ${u.titel}: ${u.grund}`);
  console.log(`Automatik Umkreis: ${neu.length} neue Einträge, ${redaktion.length} für die Redaktion (Kernort), ${uebersprungen.length} übersprungen${trocken ? ' (trocken, nichts geschrieben)' : ''}.`);

  if (!trocken && neu.length) {
    const paket = json(ziel, { hinweis: 'Automatisch aus der Liste der Polizei Düren im Presseportal angelegt (deploy/automatik-umkreis.mjs): amtliche Schlagzeile, Ort, Datum und Link zur Einzelmeldung, kein eigener Text. Meldungen aus Merzenich und den Ortsteilen werden hier nie angelegt.', eintraege: [] });
    paket.eintraege.push(...neu);
    writeFileSync(ziel, JSON.stringify(paket, null, 2) + '\n');
    for (const e of neu) stand.umkreis[e.url] = { datei: `inhalte/umkreis/${zielName}`, am: heute };
    writeFileSync(standDatei, JSON.stringify(stand, null, 2) + '\n');
  }

  if (process.env.GITHUB_STEP_SUMMARY) {
    const zeilen = [`## Automatik Umkreis (Polizei) ${heute}${trocken ? ' (trocken)' : ''}`, ''];
    zeilen.push(neu.length ? '**Neue Umkreis-Einträge** (Startseite, Block „Direkter Umkreis“):' : 'Keine neuen Umkreis-Meldungen der Polizei Düren.');
    for (const e of neu) zeilen.push(`- ${e.datum} ${e.ort}: ${e.titel} (${e.url})`);
    if (redaktion.length) { zeilen.push('', '**Für die Redaktion** (Kernort, nicht automatisiert):'); for (const m of redaktion) zeilen.push(`- ${m.datum}: ${m.titel} (${m.url})`); }
    if (uebersprungen.length) { zeilen.push('', '**Übersprungen:**'); for (const u of uebersprungen) zeilen.push(`- ${u.titel}: ${u.grund}`); }
    appendFileSync(process.env.GITHUB_STEP_SUMMARY, zeilen.join('\n') + '\n');
  }
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) lauf();
