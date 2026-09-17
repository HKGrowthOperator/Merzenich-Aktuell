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

// 2. Eintritt als ein Feld: "€ | frei | Eintritt" -> "Eintritt: frei".
(function lauf(d) { for (const e of readdirSync(d)) { const p = join(d, e); if (statSync(p).isDirectory()) lauf(p); else if (e === 'index.html') {
  const alt = readFileSync(p, 'utf8');
  const neu = alt.replace(/<div class="ef"><span class="ef-ic">€<\/span><div><b>([^<]*)<\/b><span>Eintritt<\/span><\/div><\/div>/g, (m, wert) => { eintritt++; return `<div class="ef ef--eintritt"><span class="ef-ic" aria-hidden="true">€</span><div><b>Eintritt: ${wert}</b></div></div>`; });
  if (neu !== alt) { geaendert++; if (!nurPruefen) writeFileSync(p, neu); }
} } })(join(site, 'termine'));

console.log(`Termine: ${entfernt} abgelaufene Zeile(n), ${eintritt} Eintritt-Felder, ${geaendert} Seite(n) ${nurPruefen ? 'nicht aktuell' : 'geaendert'}.`);
if (nurPruefen && geaendert) process.exit(2);
