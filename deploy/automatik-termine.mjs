#!/usr/bin/env node
/**
 * Merzenich Aktuell - Termine aus dem Veranstaltungskalender der Gemeinde (Heimat-Info)
 * automatisch, seit 03.11.2026 direkt veröffentlicht (Entscheidung Luis Kummer; vorher Entwurf, 02.11.2026).
 *
 * Liest die Rohdaten des Quellenabrufs (deploy/quellen-abruf.mjs, imports/quellen/heimatinfo.json):
 * die Liste https://www.heimat-info.de/gemeinden/merzenich/veranstaltungen (kalender) und je
 * Termin die Detailseite (veranstaltungen). Eine Detailseite hat im Lesetext diese Zeilen:
 *   Zurück / Titel / Veranstalter / veröffentlicht „TT.MM.JJJJ, HH:MM“ / Beginn „TT.MM.JJJJ HH:MM“
 *   (optional mit Ende) / Ort / Text ... / Veranstalter / Kategorie (Vereine, Kirchen, ...)
 * Daraus entstehen Termine in inhalte/termine/<heute>-heimatinfo-automatik.json, Schema wie die
 * übrigen Terminpakete (deploy/termine.mjs), ohne "entwurf": Über den Abgleich erscheinen sie
 * direkt im Kalender (deploy/wordpress-import.mjs); der Redaktionslauf prüft sie in der
 * Kalenderpflege an der Quelle nach.
 *
 * Regeln:
 * - Nur was in der Quelle steht: Titel, Beginn, Ort, Veranstalter und Kategorie aus der
 *   Detailseite; der Text ist ein sachlicher Satz aus diesen Feldern, dazu höchstens drei
 *   Sätze der Ankündigung als Zitat. Sätze mit Telefonnummer, E-Mail oder Link bleiben weg.
 * - Nur Kommendes: Beginn ab heute, höchstens TAGE_VORAUS Tage voraus.
 * - Keine privaten Termine: Hochzeit, Polterabend, Geburtstag, Taufe, Trauerfeier, „privat“.
 * - Keine Dubletten: Quelle (URL) noch in keinem Terminpaket und nicht im Stand
 *   (deploy/automatik-stand.json, Abschnitt „termine“); kein Termin am selben Tag mit
 *   ähnlichem Titel (gleiche Wörter) in inhalte/termine/.
 * - Ortsteil aus Ort, Veranstalter, Titel, Text; Orte außerhalb der Gemeinde (Niederzier,
 *   Düren usw.) als „Umgebung“, sonst „Merzenich“.
 *
 * Aufruf:
 *   node deploy/automatik-termine.mjs              neue Termine anlegen
 *   node deploy/automatik-termine.mjs --trocken    nur anzeigen, nichts schreiben
 *   node deploy/automatik-termine.mjs --heute=2026-11-02   Stichtag (Tests)
 */
import { readFileSync, writeFileSync, existsSync, readdirSync, appendFileSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
export const TAGE_VORAUS = 120;
const QUELLE_NAME = 'Heimat-Info, Veranstaltungskalender der Gemeinde Merzenich';
const ORTSTEILE = ['Merzenich', 'Golzheim', 'Girbelsrath', 'Morschenich', 'Bürgewald'];
const UMGEBUNG = ['Niederzier', 'Düren', 'Nörvenich', 'Elsdorf', 'Kerpen', 'Buir', 'Jülich', 'Huchem-Stammeln', 'Ellen', 'Oberzier', 'Arnoldsweiler'];
const PRIVAT = /\b(privat\w*|hochzeit\w*|polterabend|trauung|geburtstag\w*|taufe|trauerfeier|beerdigung|exequien)\b/i;
const MONATE = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
const WOCHENTAGE = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];

const json = (p, ersatz) => { try { return JSON.parse(readFileSync(p, 'utf8')); } catch { return ersatz; } };
const berlinHeute = (jetzt = new Date()) => new Intl.DateTimeFormat('en-CA', { timeZone: 'Europe/Berlin', year: 'numeric', month: '2-digit', day: '2-digit' }).format(jetzt);
const tageZwischen = (a, b) => Math.round((Date.parse(`${b}T12:00:00Z`) - Date.parse(`${a}T12:00:00Z`)) / 86400000);
export const slugTeil = (t) => String(t || '').toLowerCase()
  .replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss')
  .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
/** Emojis und Zierzeichen raus, Leerraum glätten. */
export const sauber = (t) => String(t || '').replace(/[\p{Extended_Pictographic}\u{FE0F}\u{200D}]/gu, '').replace(/\s+/g, ' ').trim();

/** Zeitzone Europe/Berlin: Sommerzeit vom letzten Sonntag im März (2 Uhr) bis zum letzten Sonntag im Oktober (3 Uhr). */
export function berlinVersatz(j, m, t, h = 12) {
  const letzterSonntag = (mon) => { const d = new Date(Date.UTC(j, mon, 31)); return 31 - d.getUTCDay(); };
  const ms = Date.UTC(j, m - 1, t, h);
  const anfang = Date.UTC(j, 2, letzterSonntag(2), 2), ende = Date.UTC(j, 9, letzterSonntag(9), 3);
  return ms >= anfang && ms < ende ? '+02:00' : '+01:00';
}
const iso = (j, m, t, h, mi) => `${j}-${String(m).padStart(2, '0')}-${String(t).padStart(2, '0')}T${String(h).padStart(2, '0')}:${String(mi).padStart(2, '0')}:00${berlinVersatz(+j, +m, +t, +h)}`;

/** „11.11.2026 17:30“, „11.11.2026 17:30 - 20:00“, „11.11.2026 - 12.11.2026“ → { tag, start, ende, ganztags }. */
export function zeitLesen(z) {
  const m = /^(\d{2})\.(\d{2})\.(\d{4})(?:,?\s+(\d{1,2}):(\d{2}))?(?:\s*Uhr)?(?:\s*(?:-|–|bis)\s*(?:(\d{2})\.(\d{2})\.(\d{4}))?,?\s*(?:(\d{1,2}):(\d{2}))?(?:\s*Uhr)?)?$/.exec(sauber(z));
  if (!m) return null;
  const [, t, mo, j, h, mi, t2, mo2, j2, h2, mi2] = m;
  const tag = `${j}-${mo}-${t}`;
  const ganztags = h === undefined;
  const start = iso(j, mo, t, ganztags ? 0 : h, ganztags ? 0 : mi);
  let ende = '';
  if (h2 !== undefined) ende = iso(j2 || j, mo2 || mo, t2 || t, h2, mi2);
  else if (t2) ende = iso(j2, mo2, t2, 23, 59);
  if (ende && Date.parse(ende) <= Date.parse(start)) ende = '';
  return { tag, start, ende, ganztags };
}

/** Lesetext einer Detailseite → { titel, veranstalter, zeit, ort, text[], kategorie } oder null. */
export function detailLesen(seite) {
  if (!seite || seite.status !== 200 || !seite.text) return null;
  const z = seite.text.split('\n').map((s) => s.trim()).filter((s) => s && !/^Bilder \(\d+\)$/.test(s));
  const p = z.findIndex((s) => /^\d{2}\.\d{2}\.\d{4}, \d{2}:\d{2}$/.test(s));
  if (p < 2) return null;
  const zeit = zeitLesen(z[p + 1] || '');
  if (!zeit) return null;
  const veranstalter = sauber(z[p - 1]);
  const titel = sauber(z.slice(z[0] === 'Zurück' ? 1 : 0, p - 1).join(' '));
  let i = p + 2;
  const ort = z[i] && !/[.!?]$/.test(z[i]) && z[i].length <= 120 ? sauber(z[i++]) : '';
  const text = [];
  for (; i < z.length; i++) {
    if (z[i] === 'Weitere Beiträge des Profils' || z[i] === 'Beschreibung' || sauber(z[i]) === veranstalter) break;
    text.push(sauber(z[i]));
  }
  const nachProfil = z.findIndex((s, k) => k > p && sauber(s) === veranstalter);
  const kategorie = nachProfil > 0 ? sauber(z[nachProfil + 1] || '') : '';
  return { titel, veranstalter, zeit, ort, text: text.filter(Boolean), kategorie, url: (seite.url || '').split('#')[0] };
}

/** Ortsteil aus Ort, Veranstalter, Titel und Text. */
export function ortsteilVon(d) {
  for (const feld of [d.ort, d.veranstalter, d.titel, d.text.join(' ')]) {
    const s = String(feld || '');
    const treffer = ORTSTEILE.find((o) => new RegExp(`\\b${o}\\b`, 'i').test(s));
    // „Merzenich-Golzheim“: der Ortsteil zählt vor der Gemeinde.
    if (treffer === 'Merzenich') { const t2 = ORTSTEILE.slice(1).find((o) => new RegExp(`\\b${o}\\b`, 'i').test(s)); if (t2) return t2; }
    if (treffer) return treffer;
    if (feld === d.ort && UMGEBUNG.some((o) => new RegExp(`\\b${o}\\b`, 'i').test(s))) return 'Umgebung';
  }
  return 'Merzenich';
}

/** Kategorie der Terminseiten (Kirche, Vereine, Brauchtum, Info, Senioren, Sport, Kultur, Jugend). */
export function kategorieVon(d) {
  const s = `${d.titel} ${d.text.slice(0, 3).join(' ')}`;
  // Erst der Titel (ein Martinszug mit Wortgottesdienst bleibt Brauchtum), dann der Text.
  if (/martin|karneval|kirmes|brauchtum|maibaum|sessionser/i.test(d.titel)) return 'Brauchtum';
  if (/gottesdienst|messe\b|andacht|gräbersegnung|kirche/i.test(d.titel) || /kirche/i.test(d.kategorie)) return 'Kirche';
  if (/martin|karneval|kirmes|brauchtum|maibaum/i.test(s)) return 'Brauchtum';
  if (/senior/i.test(s)) return 'Senioren';
  if (/turnier|fußball|lauf\b|sport/i.test(s)) return 'Sport';
  if (/konzert|lesung|theater|ausstellung|kultur/i.test(s)) return 'Kultur';
  if (/kinder|jugend|schule|kita/i.test(s) && !/verein/i.test(d.kategorie)) return 'Jugend';
  if (/verein/i.test(d.kategorie)) return 'Vereine';
  return 'Info';
}

/** Höchstens drei Sätze der Ankündigung, ohne Telefonnummern, E-Mail-Adressen und Links. */
export function zitat(text) {
  const saetze = text.join(' ').split(/(?<=[.!?])\s+/).map((s) => s.trim()).filter((s) => s.length >= 20);
  return saetze.filter((s) => !/@|https?:|www\.|\b0\d{2,5}\s*[/-]?\s*\d{2,}|\+49|telefon/i.test(s)).slice(0, 3).join(' ');
}

/** Wörter eines Titels für den Dublettenvergleich. */
const woerter = (t) => new Set(slugTeil(t).split('-').filter((w) => w.length > 3 && !/^(\d+|uhr|november|dezember|oktober|januar|februar|merzenich)$/.test(w)));
export function aehnlich(a, b) {
  const x = woerter(a), y = woerter(b);
  if (!x.size || !y.size) return false;
  let gleich = 0; for (const w of x) if (y.has(w)) gleich++;
  return gleich / Math.min(x.size, y.size) >= 0.5;
}

/** Alle Termine der Pakete unter inhalte/termine: [{ tag, titel, url, slug }]. */
export function bestand(basis) {
  const ordner = join(basis, 'inhalte', 'termine');
  const aus = [];
  for (const d of existsSync(ordner) ? readdirSync(ordner).filter((n) => n.endsWith('.json')) : []) {
    for (const t of json(join(ordner, d), {}).termine || []) aus.push({ tag: String(t.start || '').slice(0, 10), titel: t.titel || '', url: String(t.quelle?.url || '').split('#')[0], slug: t.slug });
  }
  return aus;
}

/** Ein Termin (Schema deploy/termine.mjs) oder { grund }. */
export function terminBauen(d, heute, vorhanden, stand = {}) {
  if (!d) return { grund: 'Detailseite nicht lesbar' };
  if (!d.titel || d.titel.length < 4) return { grund: 'kein Titel' };
  const alter = tageZwischen(heute, d.zeit.tag);
  if (alter < 0) return { grund: 'liegt in der Vergangenheit' };
  if (alter > TAGE_VORAUS) return { grund: `mehr als ${TAGE_VORAUS} Tage voraus` };
  if (PRIVAT.test(`${d.titel} ${d.text.join(' ')}`)) return { grund: 'privater Termin' };
  if (stand[d.url]) return { grund: 'schon einmal automatisch angelegt' };
  const gleich = vorhanden.find((v) => (d.url && v.url === d.url) || (v.tag === d.zeit.tag && aehnlich(v.titel, d.titel)));
  if (gleich) return { grund: `steht schon im Kalender (${gleich.slug})` };
  const ortsteil = ortsteilVon(d);
  const [j, m, t] = d.zeit.tag.split('-').map(Number);
  const wtag = WOCHENTAGE[new Date(Date.UTC(j, m - 1, t)).getUTCDay()];
  const uhr = d.zeit.ganztags ? '' : `, ${d.zeit.start.slice(11, 16).replace(/^0/, '')} Uhr`;
  const ort = d.ort || (ortsteil === 'Umgebung' ? 'laut Veranstalter' : ortsteil);
  const ortSatz = d.ort ? ` Ort: ${d.ort.replace(/[.\s]+$/, '')}.` : '';
  const beschreibung = `„${d.titel}“ am ${wtag}, ${t}. ${MONATE[m - 1]}${uhr}.${ortSatz}`;
  const einladung = `${d.veranstalter || 'Der Veranstalter'} lädt am ${wtag}, ${t}. ${MONATE[m - 1]} ${j}${uhr} zu „${d.titel}“ ein.${ortSatz}`;
  const z = zitat(d.text);
  return {
    termin: {
      // Ortsteil im Slug, wenn er nicht schon im Titel steht (kurze Titel wie „Weihnachtsfeier“).
      slug: `${slugTeil(d.titel).slice(0, 70).replace(/-+$/, '')}${ortsteil !== 'Umgebung' && !new RegExp(slugTeil(ortsteil)).test(slugTeil(d.titel)) ? '-' + slugTeil(ortsteil) : ''}-${d.zeit.tag}`,
      titel: d.titel,
      start: d.zeit.start,
      ...(d.zeit.ende ? { ende: d.zeit.ende } : {}),
      ort,
      ...(/\d/.test(d.ort) ? { adresse: d.ort } : {}),
      ortsteil,
      kategorie: kategorieVon(d),
      veranstalter: d.veranstalter || 'laut Heimat-Info',
      beschreibung,
      absaetze: [einladung, ...(z ? [`Aus der Ankündigung im Veranstaltungskalender: „${z}“`] : [])],
      quelle: { name: QUELLE_NAME, url: d.url, stand: heute },
      // Seit 03.11.2026 direkt online (Entscheidung Luis Kummer); der Redaktionslauf prüft in der Kalenderpflege nach.
    },
  };
}

/* ------------------------------------------------------------ Lauf */

function lauf() {
  const args = process.argv.slice(2);
  const trocken = args.includes('--trocken');
  const heute = (args.find((a) => a.startsWith('--heute=')) || '').slice(8) || berlinHeute();
  const hi = json(join(wurzel, 'imports', 'quellen', 'heimatinfo.json'), {});
  const standDatei = join(wurzel, 'deploy', 'automatik-stand.json');
  const stand = json(standDatei, { feuerwehr: {} });
  stand.termine ||= {};
  const vorhanden = bestand(wurzel);

  const neu = []; const uebersprungen = [];
  for (const seite of hi.veranstaltungen || []) {
    const d = detailLesen(seite);
    const r = terminBauen(d, heute, [...vorhanden, ...neu.map((t) => ({ tag: t.start.slice(0, 10), titel: t.titel, url: t.quelle.url, slug: t.slug }))], stand.termine);
    if (r.termin) neu.push(r.termin);
    else uebersprungen.push({ titel: d?.titel || seite.url, grund: r.grund });
  }

  const zielName = `${heute}-heimatinfo-automatik.json`;
  const ziel = join(wurzel, 'inhalte', 'termine', zielName);
  for (const t of neu) console.log(`neu          ${t.start.slice(0, 16)}  ${t.ortsteil.padEnd(11)}  ${t.titel}`);
  for (const u of uebersprungen) console.log(`übersprungen ${u.titel}: ${u.grund}`);
  console.log(`Automatik Termine: ${neu.length} neue Entwürfe, ${uebersprungen.length} übersprungen${trocken ? ' (trocken, nichts geschrieben)' : ''}.`);

  if (!trocken && neu.length) {
    const paket = json(ziel, { hinweis: 'Automatisch aus dem Veranstaltungskalender der Gemeinde in der Heimat-Info-App angelegt (deploy/automatik-termine.mjs). Die Termine erscheinen direkt (Entscheidung Luis Kummer 03.11.2026); die Redaktion prüft sie in der Kalenderpflege nach. Titel, Beginn, Ort und Veranstalter laut Quelle; private Termine werden nie angelegt.', termine: [] });
    paket.termine.push(...neu);
    writeFileSync(ziel, JSON.stringify(paket, null, 2) + '\n');
    for (const t of neu) stand.termine[t.quelle.url] = { slug: t.slug, datei: `inhalte/termine/${zielName}`, am: heute };
    writeFileSync(standDatei, JSON.stringify(stand, null, 2) + '\n');
  }

  if (process.env.GITHUB_STEP_SUMMARY) {
    const zeilen = [`## Automatik Termine (Heimat-Info) ${heute}${trocken ? ' (trocken)' : ''}`, ''];
    zeilen.push(neu.length ? '**Neue Termine als Entwurf** (Freigaben in WordPress):' : 'Keine neuen Termine im Veranstaltungskalender.');
    for (const t of neu) zeilen.push(`- ${t.start.slice(0, 16).replace('T', ' ')} ${t.ortsteil}: ${t.titel} (${t.quelle.url})`);
    if (uebersprungen.length) { zeilen.push('', '**Übersprungen:**'); for (const u of uebersprungen) zeilen.push(`- ${u.titel}: ${u.grund}`); }
    appendFileSync(process.env.GITHUB_STEP_SUMMARY, zeilen.join('\n') + '\n');
  }
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) lauf();
