#!/usr/bin/env node
/**
 * Termine: abgelaufene Veranstaltungen aus den ausgelieferten Listen nehmen
 * (Terminseite "Heute & die naechsten Tage", Startseite "Vor Ort") und das
 * Eintritt-Element auf Detailseiten als ein Feld schreiben ("Eintritt: frei").
 *
 * Der Vergleich laeuft gegen die aktuelle Uhrzeit (Europe/Berlin). Deshalb ist
 * dieses Skript NICHT Teil der --check-Gates, sondern laeuft im taeglichen
 * CI-Lauf (qa.yml, schedule) und schreibt das Ergebnis zurueck ins Repo.
 * Im Browser entfernt homepage-polish.js abgelaufene Zeilen zusaetzlich.
 * Aufruf: node deploy/termine-prerender.mjs [--check]
 */
import { readFileSync, writeFileSync, readdirSync, existsSync, statSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const site = join(wurzel, 'chatgpt-site');
const jetzt = Date.now();
let geaendert = 0, entfernt = 0, eintritt = 0;

// 1. Abgelaufene Termine (data-event-end in der Vergangenheit) aus Listen entfernen.
for (const rel of ['index.html', 'termine/index.html']) {
  const pfad = join(site, rel); if (!existsSync(pfad)) continue;
  const alt = readFileSync(pfad, 'utf8');
  const neu = alt.replace(/<article class="(?:agenda-row|event-row)[^"]*"[^>]*data-event-end="([^"]+)"[^>]*>[\s\S]*?<\/article>\s*/g, (m, ende) => {
    const t = Date.parse(ende); if (Number.isFinite(t) && t < jetzt) { entfernt++; return ''; } return m;
  });
  if (neu !== alt) { geaendert++; if (!nurPruefen) writeFileSync(pfad, neu); }
}

// 1b. Terminseite: Zeilen tragen kein data-event-end; Ende = "bis dd.mm.yyyy" im
// Zeitfeld, sonst der Starttag (Ortszeit, Tagesende).
{
  const pfad = join(site, 'termine', 'index.html');
  if (existsSync(pfad)) {
    const alt = readFileSync(pfad, 'utf8');
    const neu = alt.replace(/<article class="event-row"[^>]*>[\s\S]*?<\/article>\s*/g, (m) => {
      const t = /<time datetime="([^"]+)">([^<]*)<\/time>/.exec(m); if (!t) return m;
      const bis = /bis (\d{2})\.(\d{2})\.(\d{4})/.exec(t[2]);
      const start = new Date(t[1]); if (Number.isNaN(start.getTime())) return m;
      const ende = bis ? Date.parse(`${bis[3]}-${bis[2]}-${bis[1]}T23:59:59+02:00`) : Date.parse(new Intl.DateTimeFormat('en-CA', { timeZone: 'Europe/Berlin', year: 'numeric', month: '2-digit', day: '2-digit' }).format(start) + 'T23:59:59+02:00');
      if (ende < jetzt) { entfernt++; return ''; } return m;
    });
    if (neu !== alt) { geaendert++; if (!nurPruefen) writeFileSync(pfad, neu); }
  }
}

// 1c. Kompakte Zeilen mit data-event-row/data-end (Terminseite, Startseite).
for (const rel of ['index.html', 'termine/index.html']) {
  const pfad = join(site, rel); if (!existsSync(pfad)) continue;
  const alt = readFileSync(pfad, 'utf8');
  const neu = alt.replace(/<(article|li|div|a)\b([^>]*data-event-row[^>]*)>[\s\S]*?<\/\1>\s*/g, (m, tag, attrs) => {
    const e = /data-end="([^"]+)"/.exec(attrs); if (!e) return m;
    const t = Date.parse(e[1]); if (Number.isFinite(t) && t < jetzt) { entfernt++; return ''; } return m;
  });
  if (neu !== alt) { geaendert++; if (!nurPruefen) writeFileSync(pfad, neu); }
}

// 1d. Terminliste der Startseite schreiben statt beschneiden.
// Vorher war sie handgepflegtes Markup: die Schritte oben entfernen abgelaufene
// Zeilen, aber niemand setzt neue ein. Gemessen am 23.09. standen zwei von vier
// kuenftigen Terminen in der Spalte, und mit jedem abgelaufenen Termin waere sie
// weiter geschrumpft, bis die Servicespalte leer neben dem Aufmacher steht.
// Quelle sind die Terminseiten selbst: Titel, Beginn, Ende und Ort stehen dort
// im JSON-LD, die Kategorie im Kicker.
const MONATE = ['Jan', 'Feb', 'Mrz', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
const HOECHSTENS = 4; // vier Zeilen fuellen die Spalte, ohne sie zur Liste zu machen

function escapeHtml(t) {
  return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

/** Teile in Europe/Berlin, unabhaengig von der Zeitzone des Laufzeitrechners. */
function berliner(d) {
  const f = new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false });
  const t = Object.fromEntries(f.formatToParts(d).map((p) => [p.type, p.value]));
  return { jahr: t.year, monat: t.month, tag: t.day, stunde: t.hour, minute: t.minute };
}

function termineLesen() {
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

function agendaZeile(t) {
  const b = berliner(t.start);
  const be = berliner(t.ende);
  const mehrtaegig = `${b.jahr}${b.monat}${b.tag}` !== `${be.jahr}${be.monat}${be.tag}`;
  const zeit = mehrtaegig ? `bis ${be.tag}.${be.monat}.`
    : (b.stunde === '00' && b.minute === '00' ? 'Ganztägig' : `${b.stunde}:${b.minute} Uhr`);
  const zeile = [zeit, t.ort].filter(Boolean).join(' · ');
  const kalender = t.ics
    ? `<a class="calendar-save" href="/termine/${escapeHtml(t.slug)}/termin.ics" aria-label="${escapeHtml(t.titel)} im Kalender speichern" download><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></a>`
    : '';
  return `<article class="agenda-row" data-event-end="${t.ende.toISOString()}">`
    + `<time class="agenda-date" datetime="${t.start.toISOString()}"><b>${Number(b.tag)}</b><span>${MONATE[Number(b.monat) - 1]}</span></time>`
    + `<div>${t.kategorie ? `<span class="eyebrow">${escapeHtml(t.kategorie)}</span>` : ''}`
    + `<h3><a href="/termine/${escapeHtml(t.slug)}/">${escapeHtml(t.titel)}</a></h3>`
    + `<p>${escapeHtml(zeile)}</p></div>${kalender}</article>`;
}

(function startseitenTermine() {
  const pfad = join(site, 'index.html');
  if (!existsSync(pfad)) return;
  const alt = readFileSync(pfad, 'utf8');
  const MARKER = /<!-- start:termine:start -->[\s\S]*?<!-- start:termine:end -->/;
  if (!MARKER.test(alt)) {
    console.error('Termine: Marker start:termine fehlt in index.html.');
    process.exitCode = 2;
    return;
  }
  const kuenftig = termineLesen()
    .filter((t) => t.ende.getTime() >= jetzt)
    .sort((a, b) => a.start - b.start)
    .slice(0, HOECHSTENS);
  // Kein Termin, keine Liste. Der Leerzustand ist das Weglassen, wie bei den
  // Ressortflaechen; das umgebende Klappfeld faellt dann in der Pruefung auf.
  const block = kuenftig.map(agendaZeile).join('');
  const neu = alt.replace(MARKER, () => `<!-- start:termine:start -->${block}<!-- start:termine:end -->`);
  if (neu !== alt) { geaendert++; if (!nurPruefen) writeFileSync(pfad, neu); }
  console.log(`Termine: ${kuenftig.length} kuenftige(r) Termin(e) in der Servicespalte.`);
})();

// 2. Eintritt als ein Feld: "€ | frei | Eintritt" -> "Eintritt: frei".
(function lauf(d) { for (const e of readdirSync(d)) { const p = join(d, e); if (statSync(p).isDirectory()) lauf(p); else if (e === 'index.html') {
  const alt = readFileSync(p, 'utf8');
  const neu = alt.replace(/<div class="ef"><span class="ef-ic">€<\/span><div><b>([^<]*)<\/b><span>Eintritt<\/span><\/div><\/div>/g, (m, wert) => { eintritt++; return `<div class="ef ef--eintritt"><span class="ef-ic" aria-hidden="true">€</span><div><b>Eintritt: ${wert}</b></div></div>`; });
  if (neu !== alt) { geaendert++; if (!nurPruefen) writeFileSync(p, neu); }
} } })(join(site, 'termine'));

console.log(`Termine: ${entfernt} abgelaufene Zeile(n), ${eintritt} Eintritt-Felder, ${geaendert} Seite(n) ${nurPruefen ? 'nicht aktuell' : 'geaendert'}.`);
if (nurPruefen && geaendert) process.exit(2);
