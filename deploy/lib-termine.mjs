/**
 * Termine gemeinsam einlesen (termine-prerender, sport-prerender). Quelle sind
 * die Terminseiten unter chatgpt-site/termine/<slug>/: Titel, Beginn, Ende und
 * Ort aus dem JSON-LD (Event), die Kategorie aus dem Kicker.
 */
import { readFileSync, readdirSync, existsSync } from 'node:fs';
import { join } from 'node:path';

/** Teile in Europe/Berlin, unabhaengig von der Zeitzone des Laufzeitrechners. */
export function berliner(d) {
  const f = new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false });
  const t = Object.fromEntries(f.formatToParts(d).map((p) => [p.type, p.value]));
  return { jahr: t.year, monat: t.month, tag: t.day, stunde: t.hour, minute: t.minute };
}

export function termineAusSeiten(site) {
  const ordner = join(site, 'termine');
  if (!existsSync(ordner)) return [];
  const raus = [];
  for (const slug of readdirSync(ordner)) {
    const pfad = join(ordner, slug, 'index.html');
    if (!existsSync(pfad)) continue;
    const html = readFileSync(pfad, 'utf8');
    let ev = null;
    for (const m of html.matchAll(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/g)) {
      try {
        const d = JSON.parse(m[1]);
        const typ = d['@type'];
        if (typ === 'Event' || (Array.isArray(typ) && typ.includes('Event'))) { ev = d; break; }
      } catch { /* kein Termin-Datenblock, naechster */ }
    }
    if (!ev || !ev.startDate || !ev.name) continue;
    const start = new Date(ev.startDate);
    if (Number.isNaN(start.getTime())) continue;
    // Ohne endDate laeuft der Termin bis zum Ende seines Tages.
    let ende;
    if (ev.endDate && !Number.isNaN(new Date(ev.endDate).getTime())) ende = new Date(ev.endDate);
    else { const b = berliner(start); ende = new Date(`${b.jahr}-${b.monat}-${b.tag}T23:59:59+02:00`); }
    const kicker = /class="kicker"[^>]*>([^<]{1,40})</.exec(html);
    raus.push({
      slug, titel: ev.name, start, ende,
      ort: (ev.location && ev.location.name) || '',
      kategorie: kicker ? kicker[1].trim() : '',
      ics: existsSync(join(ordner, slug, 'termin.ics')),
    });
  }
  return raus;
}

