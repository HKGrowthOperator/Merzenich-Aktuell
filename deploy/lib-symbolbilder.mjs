/**
 * Symbolbilder: Pools laden und verteilen.
 *
 * Aufgabe dieser Datei ist genau eine Frage: Welches Ersatzmotiv bekommt eine
 * Meldung, die kein eigenes Bild hat? Zwei Forderungen stehen dabei gegeneinander
 * und muessen beide gelten:
 *
 *   fest     Dieselbe Meldung zeigt ueberall dasselbe Motiv - auf der Startseite,
 *            in der Ressortliste, in der Suche und auf der Artikelseite. Kein
 *            Wechsel beim Neuladen.
 *   verteilt Zwei verschiedene Meldungen derselben Kategorie zeigen verschiedene
 *            Motive, solange der Pool gross genug ist. Nicht dreimal dasselbe
 *            Polizeiauto untereinander.
 *
 * Geloest wird das nicht ueber Zufall und nicht ueber einen Hash, sondern ueber
 * eine Reihenfolge: Die Meldungen einer Kategorie werden stabil sortiert und
 * bekommen der Reihe nach das naechste Motiv aus dem Pool. Das ist reproduzierbar
 * (gleiche Eingabe, gleiche Ausgabe) und verteilt maximal gleichmaessig: bei
 * n Meldungen und p Motiven zeigt kein Motiv oefter als aufgerundet n/p.
 *
 * Ablage der Motive:
 *   chatgpt-site/assets/symbolbilder/<kategorie>/<datei>.webp
 *   chatgpt-site/assets/symbolbilder/<kategorie>/lizenzen.json
 *
 * lizenzen.json beschreibt je Datei, was unter dem Bild stehen muss:
 *   { "platz-01.webp": { "alt": "...", "credit": "...", "lizenz": "...", "quelle": "..." } }
 *
 * Ein Motiv ohne Eintrag in lizenzen.json wird NICHT ausgeliefert. Ein Bild ohne
 * Nachweis ist ein Rechtsrisiko, und ein Alt-Text, den niemand geschrieben hat,
 * waere eine Behauptung ueber ein Bild, das wir nicht gesehen haben.
 */
import { readFileSync, readdirSync, existsSync, statSync } from 'node:fs';
import { join } from 'node:path';

export const SYMBOL_WURZEL = 'chatgpt-site/assets/symbolbilder';

/** Kategorien, die die Seite kennt. Reihenfolge = Reihenfolge im Bericht. */
export const KATEGORIEN = [
  'polizei', 'feuerwehr', 'verkehr', 'sport', 'vereine', 'gemeinde',
  'leben', 'termine', 'wirtschaft', 'jobs', 'immobilien', 'familie', 'trauer',
];

/**
 * Kategorie einer Meldung. Erst der Pfad, dann der Text - der Pfad ist die
 * verlaesslichere Angabe, weil er von der Redaktion gesetzt wurde.
 */
export function kategorieFuer(a) {
  const pfad = String(a?.url || '').toLowerCase();
  const text = `${a?.kicker || ''} ${a?.titel || ''} ${a?.teaser || ''} ${(a?.themen || []).map((t) => t.label || t).join(' ')}`.toLowerCase();
  if (pfad.includes('/traueranzeigen/') || /\btrauer|nachruf|gedenk|verstorben/.test(text)) return 'trauer';
  if (pfad.includes('/familienanzeigen/') || /hochzeit|trauung|heirat|geburt|jubil/.test(text)) return 'familie';
  if (pfad.includes('/jobs/') || /stellenmarkt|stellenangebot|ausbildung|vollzeit|teilzeit/.test(text)) return 'jobs';
  if (pfad.includes('/immobilien/') || /kaltmiete|wohnfl|grundst/.test(text)) return 'immobilien';
  if (pfad.includes('/blaulicht/')) {
    if (/feuerwehr|l(ö|oe)sch|brand|brennt|rauch|drehleiter|tierrettung|technische hilfe/.test(text)) return 'feuerwehr';
    if (/polizei|einbruch|zeugen|diebstahl|fahndung|unfallflucht/.test(text)) return 'polizei';
    return /brand|feuer|rettung/.test(text) ? 'feuerwehr' : 'polizei';
  }
  if (/verkehr|sperrung|baustelle|umleitung|stra(ß|ss)e|bahn|bus/.test(text)) return 'verkehr';
  if (pfad.includes('/sport/') || /fu(ß|ss)ball|kreisliga|spieltag|tabelle|sc 1919/.test(text)) return 'sport';
  if (pfad.includes('/vereine/') || /verein|sch(ü|ue)tzen|karneval|fanclub|ehrenamt/.test(text)) return 'vereine';
  if (pfad.includes('/termine/') || /veranstaltung|fest|konzert|markt|wochenende/.test(text)) return 'termine';
  if (pfad.includes('/wirtschaft/') || /unternehmen|betrieb|gewerbe|f(ö|oe)rder/.test(text)) return 'wirtschaft';
  if (pfad.includes('/rathaus/') || /gemeinde|rat|verwaltung|b(ü|ue)rgermeister|beschluss/.test(text)) return 'gemeinde';
  return 'leben';
}

/** Einen Pool von der Platte lesen. Nur Motive mit vollstaendigem Nachweis. */
export function poolLesen(wurzel, kategorie) {
  const ordner = join(wurzel, SYMBOL_WURZEL, kategorie);
  if (!existsSync(ordner) || !statSync(ordner).isDirectory()) return { motive: [], uebersprungen: [] };
  const lizenzPfad = join(ordner, 'lizenzen.json');
  let lizenzen = {};
  if (existsSync(lizenzPfad)) {
    try { lizenzen = JSON.parse(readFileSync(lizenzPfad, 'utf8')); }
    catch (e) { return { motive: [], uebersprungen: [`${kategorie}/lizenzen.json ist kein gueltiges JSON: ${e.message}`] }; }
  }
  const motive = []; const uebersprungen = [];
  for (const datei of readdirSync(ordner).sort()) {
    if (!/\.(webp|jpg|jpeg|png|avif)$/i.test(datei)) continue;
    const eintrag = lizenzen[datei];
    if (!eintrag || !eintrag.alt || !eintrag.credit) { uebersprungen.push(`${kategorie}/${datei}: kein vollstaendiger Eintrag in lizenzen.json (alt und credit noetig)`); continue; }
    motive.push({
      src: `/${SYMBOL_WURZEL.replace(/^chatgpt-site\//, '')}/${kategorie}/${datei}`,
      alt: String(eintrag.alt),
      credit: String(eintrag.credit),
      lizenz: eintrag.lizenz ? String(eintrag.lizenz) : '',
      quelle: eintrag.quelle ? String(eintrag.quelle) : '',
      kategorie,
    });
  }
  return { motive, uebersprungen };
}

/** Alle Pools lesen. */
export function poolsLesen(wurzel) {
  const pools = {}; const hinweise = [];
  for (const k of KATEGORIEN) {
    const { motive, uebersprungen } = poolLesen(wurzel, k);
    pools[k] = motive;
    hinweise.push(...uebersprungen);
  }
  return { pools, hinweise };
}

/**
 * Stabile Reihenfolge innerhalb einer Kategorie. Sortiert wird nach Datum
 * aufsteigend und bei Gleichstand nach Kennung, damit eine neue Meldung die
 * Zuordnung der aelteren nicht verschiebt, solange sie neuer ist.
 */
function stabilSortiert(liste) {
  return [...liste].sort((a, b) => {
    const da = String(a.datum || a.abgerufen || ''), db = String(b.datum || b.abgerufen || '');
    if (da !== db) return da < db ? -1 : 1;
    const ia = String(a.id || a.url || ''), ib = String(b.id || b.url || '');
    return ia < ib ? -1 : ia > ib ? 1 : 0;
  });
}

/**
 * Vergabe. Gibt eine Map von Meldungskennung auf Motiv zurueck.
 * Meldungen mit eigenem Bild bekommen keinen Eintrag.
 */
export function vergibSymbolbilder(artikel, pools) {
  const nachKategorie = new Map();
  for (const a of artikel) {
    if (a.bild && a.bild.src) continue;
    const k = kategorieFuer(a);
    if (!nachKategorie.has(k)) nachKategorie.set(k, []);
    nachKategorie.get(k).push(a);
  }
  const zuordnung = new Map(); const warnungen = [];
  for (const [k, liste] of [...nachKategorie.entries()].sort()) {
    const pool = pools[k] || [];
    if (!pool.length) { warnungen.push(`Pool ${k} ist leer, ${liste.length} Meldung(en) bleiben ohne Symbolbild.`); continue; }
    const sortiert = stabilSortiert(liste);
    sortiert.forEach((a, i) => zuordnung.set(a.id || a.url, pool[i % pool.length]));
    if (liste.length > pool.length) {
      const max = Math.ceil(liste.length / pool.length);
      warnungen.push(`Pool ${k}: ${liste.length} Meldungen auf ${pool.length} Motive, ein Motiv erscheint bis zu ${max} mal.`);
    }
  }
  return { zuordnung, warnungen };
}

/** Selbstpruefung der Vergabe. Wirft, wenn eine der beiden Forderungen bricht. */
export function selbsttest() {
  const pool = Array.from({ length: 5 }, (_, i) => ({ src: `/m${i}.webp`, alt: `M${i}`, credit: 'Test' }));
  const artikel = Array.from({ length: 12 }, (_, i) => ({ id: `a${String(i).padStart(2, '0')}`, url: `/sport/a${i}/`, datum: `2026-09-${String(i + 1).padStart(2, '0')}T10:00:00+02:00`, titel: 'Fussball' }));
  const eins = vergibSymbolbilder(artikel, { sport: pool });
  const zwei = vergibSymbolbilder([...artikel].reverse(), { sport: pool });
  for (const a of artikel) {
    if (eins.zuordnung.get(a.id).src !== zwei.zuordnung.get(a.id).src) throw new Error('Vergabe ist nicht stabil: Reihenfolge der Eingabe aendert das Ergebnis.');
  }
  const zaehler = {};
  for (const m of eins.zuordnung.values()) zaehler[m.src] = (zaehler[m.src] || 0) + 1;
  const max = Math.max(...Object.values(zaehler)), soll = Math.ceil(artikel.length / pool.length);
  if (max > soll) throw new Error(`Vergabe ist nicht gleichmaessig: ${max} statt hoechstens ${soll}.`);
  const sortiert = stabilSortiert(artikel);
  for (let i = 1; i < sortiert.length; i++) {
    if (eins.zuordnung.get(sortiert[i].id).src === eins.zuordnung.get(sortiert[i - 1].id).src) throw new Error('Zwei benachbarte Meldungen zeigen dasselbe Motiv.');
  }
  return { geprueft: artikel.length, pool: pool.length, hoechsteWiederholung: max };
}
