#!/usr/bin/env node
/**
 * Merzenich Aktuell - Feuerwehr-Einsaetze automatisch als Meldungsentwurf (13.10.2026).
 *
 * Liest die Rohdaten aus dem taeglichen Quellenabruf (deploy/quellen-abruf.mjs):
 *   imports/quellen/feuerwehr.json   Einsatzseiten von feuerwehr-merzenich.de
 *                                    (Nummer, Ort - Strasse, Stichwort / Kategorie,
 *                                    Alarmzeit, Bericht, Eingesetzte Kraefte)
 *   imports/quellen/heimatinfo.json  Beitraege der Feuerwehr in der Heimat-Info-App
 *                                    (Einsatz-Nr, Datum, Stichwort, Meldung, Einsatzort,
 *                                    Eingesetzte Kraefte, Bericht; kommen oft frueher)
 * und schreibt fuer jeden neuen Einsatz eine Meldung nach
 * inhalte/meldungen/<heute>-feuerwehr-automatik.json.
 *
 * Regeln (docs/RECHERCHE-QUELLEN.md, docs/REDAKTIONELLER-STANDARD-V2.md):
 * - Nur was in der Quelle steht: Der Text entsteht ausschliesslich aus den Feldern
 *   der Quelle, dazu hoechstens drei Saetze des Feuerwehrberichts als Zitat.
 *   Saetze mit Ziffern (Hausnummern, Alter, Uhrzeiten) bleiben aus dem Zitat.
 * - Widersprechen sich die Quellen bei Datum oder Ort, nennt der Text beide.
 * - Datum = Alarmierung laut Feuerwehr; ohne Einsatzseite die Beitragszeit in Heimat-Info.
 * - Ueberspringen statt raten: kein Ortsteil (Autobahn, Landstrasse), keine passende
 *   Bildklasse, kein Bericht oder zu kurz (Kurzmeldung: 80 Woerter).
 * - Nur Neues: Einsatznummer noch in keiner Meldung, keinem Artikel und keiner Seite,
 *   nicht schon einmal automatisch angelegt (deploy/automatik-stand.json), Alarm
 *   hoechstens 14 Tage alt.
 *
 * Veroeffentlicht nichts: Jede Meldung kommt ueber den Abgleich als Entwurf in die
 * Freigaben in WordPress (wordpress-import.mjs setzt Meldungen immer auf draft,
 * Option "sofort veroeffentlichen" aus). Die Redaktion prueft und gibt frei.
 *
 * Aufruf:
 *   node deploy/automatik-feuerwehr.mjs              neue Einsaetze anlegen
 *   node deploy/automatik-feuerwehr.mjs --trocken    nur anzeigen, nichts schreiben
 *   node deploy/automatik-feuerwehr.mjs --heute=2026-10-13   Stichtag (Tests)
 * Ist GITHUB_STEP_SUMMARY gesetzt, kommt eine Uebersicht in die Job-Zusammenfassung,
 * mit den uebrigen neuen Rohdaten (Heimat-Info, Polizei), die nicht automatisiert sind.
 */
import { readFileSync, writeFileSync, existsSync, readdirSync, appendFileSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { ORTSTEILE } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const FENSTER_TAGE = 14;
const MIN_WOERTER = 80;
const ORGANISATION = 'Freiwillige Feuerwehr Merzenich';

/* ------------------------------------------------------------ Hilfen */

const slugTeil = (t) => String(t || '').toLowerCase()
  .replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss')
  .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');

/** Ortsteil-Schluessel aus einer Ortsangabe der Feuerwehr, mit den Schreibfehlern aus den Quellen. */
const ORT_ALIAS = { mereznich: 'merzenich', girbelsrtath: 'girbelsrath', burgewald: 'buergewald', buergewald: 'buergewald' };
export function ortsteilVon(ort) {
  const s = slugTeil(ort).replace(/-/g, '');
  const k = ORT_ALIAS[s] || s;
  return ORTSTEILE[k] ? k : '';
}

/** "Merzenich - Steinweg", "Merzenich B264/Valdersweg", "BAB4" -> { ort, strasse }. */
export function einsatzortZerlegen(text) {
  const t = String(text || '').replace(/_/g, '-').replace(/\s+/g, ' ').trim();
  let m = t.match(/^(\S+)\s+-\s*(.+)$/) || t.match(/^(\S+)\s*-\s+(.+)$/);
  if (m) return { ort: m[1], strasse: m[2].trim() };
  m = t.match(/^(\S+)\s+(.+)$/);
  if (m && ortsteilVon(m[1])) return { ort: m[1], strasse: m[2].trim() };
  return { ort: t, strasse: '' };
}

const DATUM_RE = /(\d{2})\.(\d{2})\.(\d{4})/;

/** Zeitstempel in Ortszeit mit richtigem Versatz (+02:00 Sommer, +01:00 Winter). */
export function berlinIso(datum, zeit = '') {
  const m = String(datum).match(DATUM_RE);
  if (!m) return '';
  const [, d, mo, y] = m;
  const [h, mi] = (zeit || '12:00').split(':');
  for (const off of ['+02:00', '+01:00']) {
    const iso = `${y}-${mo}-${d}T${h.padStart(2, '0')}:${mi.padStart(2, '0')}:00${off}`;
    const t = new Date(iso);
    const teile = Object.fromEntries(new Intl.DateTimeFormat('en-CA', { timeZone: 'Europe/Berlin', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' })
      .formatToParts(t).map((p) => [p.type, p.value]));
    if (Number(teile.hour) === Number(h) && Number(teile.minute) === Number(mi)) return iso;
  }
  return '';
}

/** "Sonntag, 4. Oktober 2026" fuer ein Datum TT.MM.JJJJ. */
export function tagLang(datum) {
  const m = String(datum).match(DATUM_RE);
  if (!m) return '';
  const t = new Date(`${m[3]}-${m[2]}-${m[1]}T12:00:00Z`);
  return new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(t);
}
const tagOhneWochentag = (datum) => tagLang(datum).replace(/^[^,]+,\s*/, '');

const berlinHeute = (jetzt = new Date()) => new Intl.DateTimeFormat('en-CA', { timeZone: 'Europe/Berlin', year: 'numeric', month: '2-digit', day: '2-digit' }).format(jetzt);
const isoTag = (datum) => { const m = String(datum).match(DATUM_RE); return m ? `${m[3]}-${m[2]}-${m[1]}` : ''; };
const tageZwischen = (a, b) => Math.round((Date.parse(`${b}T12:00:00Z`) - Date.parse(`${a}T12:00:00Z`)) / 86400000);

export const listeDeutsch = (l) => (l.length < 2 ? l.join('') : `${l.slice(0, -1).join(', ')} und ${l[l.length - 1]}`);

/* ------------------------------------------------------------ Quellen zerlegen */

/** Einsatzseite von feuerwehr-merzenich.de (Zeilenaufbau siehe Kopf). */
export function feuerwehrSeiteZerlegen(seite) {
  if (!seite || seite.status !== 200 || !seite.text) return null;
  const z = seite.text.split('\n').map((s) => s.trim()).filter(Boolean);
  const k = (z[0] || '').match(/^(\d{1,3})\/(\d{2})\s+(.*)$/);
  const zeit = (z[2] || '').match(/^(\d{2}\.\d{2}\.\d{4})\s*\/\s*(\d{1,2}:\d{2})/);
  if (!k || !zeit) return null;
  const [stichwort, kategorie = ''] = (z[1] || '').split('/').map((s) => s.trim());
  const ende = z.indexOf('Eingesetzte Kräfte');
  const bericht = z.slice(3, ende < 0 ? z.length : ende).join(' ');
  const kraefte = ende < 0 ? [] : z.slice(ende + 1);
  return { quelle: 'feuerwehr', url: seite.url, nr: Number(k[1]), jahr: k[2], ...einsatzortZerlegen(k[3]), stichwort, kategorie, datum: zeit[1], zeit: zeit[2].padStart(5, '0'), bericht, kraefte };
}

/** Beitrag der Feuerwehr in der Heimat-Info-App mit den Feldern "Einsatz-Nr", "Datum" usw. */
export function heimatInfoZerlegen(seite) {
  if (!seite || seite.status !== 200 || !seite.text || !/Einsatz-Nr:/.test(seite.text)) return null;
  const z = seite.text.split('\n').map((s) => s.trim());
  if (!z.includes(ORGANISATION)) return null;
  // Nur Zeilen, die mit dem Feld beginnen (vor dem Namen hoechstens Emoji): Zeile 1 ist eine
  // gekuerzte Vorschau mit allen Feldern ("Meldung: Person hinter...").
  const feld = (name) => { const re = new RegExp(`^[^\\p{L}\\p{N}]*${name}:\\s*`, 'u'); const l = z.find((s) => re.test(s)); return l ? l.replace(re, '').trim() : ''; };
  const nr = feld('Einsatz-Nr').match(/^(\d{1,3})\/(\d{2})/);
  const datum = (feld('Datum').match(DATUM_RE) || [])[0] || '';
  if (!nr || !datum) return null;
  const beitrag = z.find((s) => /^\d{2}\.\d{2}\.\d{4}, \d{1,2}:\d{2}$/.test(s)) || '';
  const iKraefte = z.findIndex((s) => /Eingesetzte Kräfte:/.test(s));
  const iEnde = z.findIndex((s, i) => i > iKraefte && (s.startsWith('#') || s === 'Symbolfoto' || s === 'Weitere Beiträge des Profils'));
  const bericht = iKraefte < 0 ? '' : z.slice(iKraefte + 1, iEnde < 0 ? undefined : iEnde).filter(Boolean).join(' ');
  const kraefte = feld('Eingesetzte Kräfte').split(',').map((s) => s.trim()).filter(Boolean).map(kraftLang);
  return {
    quelle: 'heimatinfo', url: seite.url, nr: Number(nr[1]), jahr: nr[2], ...einsatzortZerlegen(feld('Einsatzort')),
    stichwort: feld('Stichwort'), meldung: feld('Meldung'), datum, beitragDatum: beitrag.split(', ')[0] || '', beitragZeit: beitrag.split(', ')[1] || '', bericht, kraefte,
  };
}

/** Abkuerzungen aus Heimat-Info ausschreiben, wie auf der Einsatzseite der Feuerwehr. */
export function kraftLang(k) {
  return String(k).replace(/^LG\s+/, 'Löschgruppe ').replace(/^FTZ\b\s*/, 'Feuerschutztechnisches Zentrum ').replace(/^Fw\s+/, 'Feuerwehr ').trim();
}

/** Einsatzseite und Heimat-Info-Beitrag einer Nummer zusammenfuehren, Widersprueche festhalten. */
export function zusammenfuehren(fw, hi) {
  const a = fw || hi;
  if (!a) return null;
  const e = {
    nr: a.nr, jahr: a.jahr, fw: fw || null, hi: hi || null,
    ort: fw?.ort || hi?.ort || '', strasse: fw?.strasse || hi?.strasse || '',
    stichwort: fw?.stichwort || hi?.stichwort || '', kategorie: fw?.kategorie || '', meldung: hi?.meldung || '',
    datum: fw?.datum || hi?.datum || '', zeit: fw?.zeit || '',
    bericht: fw?.bericht || hi?.bericht || '',
    kraefte: (fw?.kraefte?.length ? fw.kraefte : hi?.kraefte || []).map((k) => (k === 'Tagesalarm' ? 'Tagesalarmgruppe' : k)),
    widersprueche: [],
  };
  if (fw && hi && fw.datum !== hi.datum) e.widersprueche.push('datum');
  if (fw && hi && ortsteilVon(fw.ort) && ortsteilVon(hi.ort) && ortsteilVon(fw.ort) !== ortsteilVon(hi.ort)) e.widersprueche.push('ort');
  e.ortsteil = ortsteilVon(e.ort);
  return e;
}

/* ------------------------------------------------------------ Bildklasse, Zitat */

const BILDKLASSEN = [
  [/person hinter t[üu]r|eingeschlossene person|^pet$|t[üu]r[öo]ffnung/i, 'blaulicht.rettung.tuer'],
  [/[öo]lspur|betriebsmittel/i, 'blaulicht.technik.oelspur'],
  [/tier/i, 'blaulicht.rettung.tier'],
  [/brandmeldeanlage|automatischer brandalarm/i, 'blaulicht.alarm.brandmeldeanlage'],
  [/rauchmelder/i, 'blaulicht.alarm.rauchmelder'],
  [/wald|fl[äa]che/i, 'blaulicht.feuer.flaeche'],
  [/th-vu|verkehrsunfall/i, 'verkehr.unfall.rettung'],
  [/e-?call/i, 'blaulicht.technik.ecall'],
  [/brand|^bd\d?$|rauch|feuer|unrat/i, 'blaulicht.feuer'],
];
export function bildklasseVon(e, gueltig) {
  for (const feld of [e.meldung, e.stichwort, e.kategorie]) {
    if (!feld) continue;
    for (const [re, klasse] of BILDKLASSEN) if (re.test(feld) && (!gueltig || gueltig.has(klasse))) return klasse;
  }
  return '';
}

/** Hoechstens drei Saetze des Berichts ohne Ziffern; innere Anfuehrungszeichen werden ‚…‘. */
export function zitatAus(bericht, maxZeichen = 420) {
  const saetze = String(bericht || '').replace(/\s+/g, ' ').trim().split(/(?<=[.!?])\s+(?=[A-ZÄÖÜ„"])/)
    .map((s) => s.trim()).filter((s) => s.length > 20 && !/\d/.test(s) && /[.!?]$/.test(s));
  // Saetze mit relativen Zeitangaben ("am vergangenen Sonntag") nur, wenn sonst nichts bleibt.
  const relativ = /\b(vergangen\w*|gestern|heute|vorgestern|letzte[nmrs]?)\b/i;
  const ohne = saetze.filter((s) => !relativ.test(s));
  const raus = [];
  for (const s of ohne.length ? ohne : saetze) {
    if (raus.length >= 3 || raus.join(' ').length + s.length > maxZeichen) break;
    raus.push(s);
  }
  return raus.join(' ').replace(/"([^"]+)"/g, '‚$1‘').replace(/[„“]([^„“]+)“/g, '‚$1‘');
}

const woerter = (m) => [m.dek, ...m.absaetze].join(' ').split(/\s+/).filter(Boolean).length;

/* ------------------------------------------------------------ Meldung bauen */

/** Meldung nach dem Schema von deploy/meldungen.mjs oder { grund } zum Ueberspringen. */
export function meldungBauen(e, { stand, gueltigeKlassen } = {}) {
  if (!e.ortsteil) return { grund: `Einsatzort „${[e.ort, e.strasse].filter(Boolean).join(' - ')}“ liegt in keinem Ortsteil (Autobahn, Landstraße oder Nachbarort)` };
  const bildklasse = bildklasseVon(e, gueltigeKlassen);
  if (!bildklasse) return { grund: `keine Bildklasse für das Stichwort „${e.meldung || e.stichwort}“` };
  const zitat = zitatAus(e.bericht);
  if (!zitat) return { grund: 'kein verwendbarer Einsatzbericht der Feuerwehr' };

  const ortName = ORTSTEILE[e.ortsteil];
  const nummer = `${e.nr}/${e.jahr}`;
  const was = e.meldung || e.stichwort;
  const tag = tagLang(e.datum);
  const ortText = e.strasse ? `${ortName}, ${e.strasse}` : ortName;

  const titel = `${was} in ${ortName}: Einsatz ${nummer} der Feuerwehr`;
  const dek = `Die Freiwillige Feuerwehr Merzenich ist am ${tag}${e.zeit ? `, um ${e.zeit} Uhr` : ','} nach ${ortName} alarmiert worden. Die Meldung lautete „${was}“.`;

  const widerspruch = [];
  if (e.widersprueche.includes('datum')) widerspruch.push(`Beim Datum unterscheiden sich die Quellen: Die Einsatzseite der Feuerwehr nennt ${tagLang(e.fw.datum)}${e.fw.zeit ? `, ${e.fw.zeit} Uhr` : ''}, der Beitrag der Feuerwehr in der Heimat-Info-App den ${tagOhneWochentag(e.hi.datum)}.`);
  if (e.widersprueche.includes('ort')) widerspruch.push(`Beim Einsatzort unterscheiden sich die Quellen: Die Einsatzseite der Feuerwehr nennt ${[e.fw.ort, e.fw.strasse].filter(Boolean).join(', ')}, der Beitrag in der Heimat-Info-App ${[e.hi.ort, e.hi.strasse].filter(Boolean).join(', ')}.`);
  const stichwortText = e.stichwort && e.stichwort !== was ? ` Das Einsatzstichwort lautete „${e.stichwort}“${e.kategorie && e.kategorie !== e.stichwort ? ` (${e.kategorie})` : ''}.` : '';
  const absatz1 = `Einsatzort war ${ortText}.${stichwortText}${e.kraefte.length ? ` Im Einsatz ${e.kraefte.length > 1 ? 'waren' : 'war'}: ${listeDeutsch(e.kraefte)}.` : ''}${widerspruch.length ? ' ' + widerspruch.join(' ') : ''}`;
  const absatz2 = `Die Feuerwehr schreibt dazu: „${zitat}“ Es war der ${e.nr}. Einsatz der Freiwilligen Feuerwehr Merzenich im Jahr 20${e.jahr}.`;

  const fakten = [
    `Einsatz ${nummer} der Freiwilligen Feuerwehr Merzenich`,
    `Alarmierung: ${tag}${e.zeit ? `, ${e.zeit} Uhr` : ''}`,
    `Einsatzort: ${ortText}`,
    `Meldung: ${was}`,
  ];
  if (e.kraefte.length) fakten.push(`Eingesetzte Kräfte: ${e.kraefte.join(', ')}`);

  const datum = berlinIso(e.datum, e.zeit) || (e.hi?.beitragDatum ? berlinIso(e.hi.beitragDatum, e.hi.beitragZeit) : '');
  const quellen = [];
  if (e.fw) quellen.push({ name: `${ORGANISATION}, Einsatzbericht ${nummer}`, url: e.fw.url, stand });
  if (e.hi) quellen.push({ name: `${ORGANISATION}, Beitrag in der Heimat-Info-App vom ${e.hi.beitragDatum || e.hi.datum}`, url: e.hi.url, stand });
  const themen = [['feuerwehr', 'Feuerwehr']];
  if (e.kraefte.some((k) => /rettungsdienst/i.test(k))) themen.push(['rettungsdienst', 'Rettungsdienst']);

  const m = {
    slug: `einsatz-${e.nr}-${slugTeil(was)}-${e.ortsteil}`,
    ressort: 'blaulicht', ortsteil: e.ortsteil, kicker: `Feuerwehr · ${nummer}`,
    titel, dek, datum, artikeltyp: 'kurzmeldung', fakten, absaetze: [absatz1, absatz2], themen, bildklasse,
    quelle: quellen[0], ...(quellen.length > 1 ? { weitereQuellen: quellen.slice(1) } : {}),
  };
  if (!datum) return { grund: 'kein Datum in den Quellen' };
  const n = woerter(m);
  if (n < MIN_WOERTER) return { grund: `nur ${n} Wörter (Kurzmeldung verlangt ${MIN_WOERTER})` };
  return { meldung: m };
}

/* ------------------------------------------------------------ Bestand */

/** Einsatznummern (Jahr jj), die schon eine Meldung, einen Artikel oder eine Seite haben. */
export function gedeckteNummern(basis, jj) {
  const nummern = new Set();
  const re = new RegExp(`\\b(\\d{2,3})\\/${jj}\\b`, 'g');
  const lies = (datei) => {
    const t = readFileSync(datei, 'utf8');
    for (const m of t.matchAll(re)) nummern.add(Number(m[1]));
    for (const m of t.matchAll(/einsatz-(\d{2,3})-/g)) nummern.add(Number(m[1]));
  };
  const ordner = (pfad, filter) => (existsSync(pfad) ? readdirSync(pfad, { withFileTypes: true }) : []).flatMap((d) => {
    const p = join(pfad, d.name);
    return d.isDirectory() ? ordner(p, filter) : (filter(d.name) ? [p] : []);
  });
  ordner(join(basis, 'inhalte', 'meldungen'), (n) => n.endsWith('.json')).forEach(lies);
  ordner(join(basis, 'site-source', 'content', 'artikel'), (n) => n.endsWith('.md')).forEach(lies);
  ordner(join(basis, 'chatgpt-site', 'blaulicht'), (n) => n === 'index.html').forEach(lies);
  return nummern;
}

const json = (p, ersatz) => { try { return JSON.parse(readFileSync(p, 'utf8')); } catch { return ersatz; } };

/** Alle Einsaetze aus beiden Rohdateien, je Nummer zusammengefuehrt. */
export function einsaetzeLesen(feuerwehr, heimatinfo) {
  const fw = new Map(); const hi = new Map();
  for (const s of feuerwehr?.details || []) { const e = feuerwehrSeiteZerlegen(s); if (e) fw.set(`${e.nr}/${e.jahr}`, e); }
  for (const s of heimatinfo?.details || []) { const e = heimatInfoZerlegen(s); if (e) hi.set(`${e.nr}/${e.jahr}`, e); }
  return [...new Set([...fw.keys(), ...hi.keys()])].map((k) => zusammenfuehren(fw.get(k), hi.get(k))).filter(Boolean).sort((a, b) => a.nr - b.nr);
}

/* ------------------------------------------------------------ Lauf */

function lauf() {
  const args = process.argv.slice(2);
  const trocken = args.includes('--trocken');
  const heute = (args.find((a) => a.startsWith('--heute=')) || '').slice(8) || berlinHeute();
  const feuerwehr = json(join(wurzel, 'imports', 'quellen', 'feuerwehr.json'), {});
  const heimatinfo = json(join(wurzel, 'imports', 'quellen', 'heimatinfo.json'), {});
  const standDatei = join(wurzel, 'deploy', 'automatik-stand.json');
  const stand = json(standDatei, { hinweis: 'Automatisch angelegte Meldungen (deploy/automatik-feuerwehr.mjs). Eine Nummer hier wird nie wieder angelegt, auch wenn die Meldung gelöscht wurde.', feuerwehr: {} });
  const klassenRoh = json(join(wurzel, 'deploy', 'bildklassen.json'), {});
  const gueltigeKlassen = new Set(Object.keys(klassenRoh.klassen || klassenRoh));
  const abrufTag = (iso) => (iso ? berlinHeute(new Date(iso)) : heute);

  const neu = []; const uebersprungen = [];
  const gedeckt = new Map();
  for (const e of einsaetzeLesen(feuerwehr, heimatinfo)) {
    const nummer = `${e.nr}/${e.jahr}`;
    const alter = tageZwischen(isoTag(e.datum), heute);
    if (!(alter >= 0 && alter <= FENSTER_TAGE)) continue;
    if (!gedeckt.has(e.jahr)) gedeckt.set(e.jahr, gedeckteNummern(wurzel, e.jahr));
    if (gedeckt.get(e.jahr).has(e.nr) || stand.feuerwehr[nummer]) continue;
    const r = meldungBauen(e, { stand: abrufTag(e.fw ? feuerwehr.abgerufen : heimatinfo.abgerufen), gueltigeKlassen });
    if (r.meldung) neu.push(r.meldung); else uebersprungen.push({ nummer, grund: r.grund });
  }

  const zielName = `${heute}-feuerwehr-automatik.json`;
  const ziel = join(wurzel, 'inhalte', 'meldungen', zielName);
  for (const m of neu) console.log(`neu       ${m.kicker.replace('Feuerwehr · ', '')}  ${m.slug}  (${m.bildklasse})`);
  for (const u of uebersprungen) console.log(`übersprungen ${u.nummer}: ${u.grund}`);
  console.log(`Automatik Feuerwehr: ${neu.length} neue Entwürfe, ${uebersprungen.length} übersprungen${trocken ? ' (trocken, nichts geschrieben)' : ''}.`);

  if (!trocken && neu.length) {
    const paket = json(ziel, { hinweis: 'Automatisch aus den Quellen der Feuerwehr Merzenich angelegt (deploy/automatik-feuerwehr.mjs). Text nur aus den Feldern der Quelle und einem gekennzeichneten Zitat. Erscheint in WordPress als Entwurf in den Freigaben; die Redaktion prüft und gibt frei.', redaktionsstandard: 2, meldungen: [] });
    paket.meldungen.push(...neu);
    writeFileSync(ziel, JSON.stringify(paket, null, 2) + '\n');
    for (const m of neu) stand.feuerwehr[m.kicker.replace('Feuerwehr · ', '')] = { slug: m.slug, datei: `inhalte/meldungen/${zielName}`, am: heute };
    writeFileSync(standDatei, JSON.stringify(stand, null, 2) + '\n');
  }

  if (process.env.GITHUB_STEP_SUMMARY) appendFileSync(process.env.GITHUB_STEP_SUMMARY, zusammenfassung({ neu, uebersprungen, heute, heimatinfo, trocken }));
}

/** Uebersicht fuer die Job-Zusammenfassung: angelegt, uebersprungen, uebrige neue Rohdaten. */
function zusammenfassung({ neu, uebersprungen, heute, heimatinfo, trocken }) {
  const zeilen = [`## Automatik Feuerwehr ${heute}${trocken ? ' (trocken)' : ''}`, ''];
  zeilen.push(neu.length ? '**Neue Entwürfe** (kommen über den Abgleich in die Freigaben):' : 'Keine neuen Einsätze.');
  for (const m of neu) zeilen.push(`- ${m.kicker}: ${m.titel} (${m.quelle.url})`);
  if (uebersprungen.length) { zeilen.push('', '**Übersprungen** (bitte von Hand prüfen):'); for (const u of uebersprungen) zeilen.push(`- ${u.nummer}: ${u.grund}`); }
  const polizei = json(join(wurzel, 'imports', 'quellen', 'polizei.json'), {});
  const pListen = polizei.listen || [];
  zeilen.push('', '**Nicht automatisiert, neu in den Rohdaten** (für ChatGPT oder die Redaktion):');
  const seit = new Date(Date.parse(`${heute}T00:00:00Z`) - 3 * 86400000);
  let n = 0;
  for (const s of heimatinfo.details || []) {
    const z = (s.text || '').split('\n').map((x) => x.trim());
    const org = z[2] || ''; const zeit = z.find((x) => /^\d{2}\.\d{2}\.\d{4}, \d{1,2}:\d{2}$/.test(x)) || '';
    if (!zeit || org === ORGANISATION) continue;
    const t = new Date(`${isoTag(zeit)}T12:00:00Z`);
    if (t < seit) continue;
    zeilen.push(`- Heimat-Info, ${org}, ${zeit}: ${(z[1] || '').slice(0, 90)} (${s.url})`); n++;
  }
  if (!n) zeilen.push('- Heimat-Info: keine neuen Beiträge anderer Organisationen in den letzten drei Tagen.');
  zeilen.push(`- Polizei (Presseportal): ${pListen.filter((l) => l.status === 200).length} von ${pListen.length} Listen abrufbar${pListen.some((l) => l.status !== 200) ? ` (Status ${[...new Set(pListen.map((l) => l.status))].join(', ')})` : ''}.`);
  return zeilen.join('\n') + '\n';
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) lauf();
