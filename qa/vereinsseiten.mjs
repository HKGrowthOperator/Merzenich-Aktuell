#!/usr/bin/env node
/**
 * Breitenrecherche (Entscheidung Luis Kummer 03.11.2026: „mehr researchen … Landing-Pages … vor allem für Events“):
 * liest die Websites aller Vereine aus deploy/vereinsverzeichnis.json, dazu deren Seiten „Termine“, „Veranstaltungen“,
 * „Events“, „Aktuelles“ und bekannte Kalender (ICS), und listet jedes Datum der nächsten 120 Tage mit Textstelle.
 * Ergebnis ist eine Arbeitsliste, kein Beleg: Jeder Termin wird an der Detailseite des Veranstalters gelesen und mit
 * inhalte/termine/ abgeglichen (CLAUDE-LAUF Phase 1, Schritt 3a). Instagram und Facebook verlangen aus der Sitzung eine
 * Anmeldung; die liest ChatGPT (vorlagen/chatgpt-auftrag.md). Sperren werden nicht umgangen.
 * Aufruf: node qa/vereinsseiten.mjs [--tage=120] [--json]
 */
import { readFileSync, readdirSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = join(dirname(fileURLToPath(import.meta.url)), '..');
const args = process.argv.slice(2);
const TAGE = Number((args.find((a) => a.startsWith('--tage=')) || '').slice(7)) || 120;
const UA = 'Mozilla/5.0 (compatible; MerzenichAktuell-Redaktion)';
const ZUSATZ = [ // Kalender, die das Verzeichnis nicht als Website führt
  { name: 'KG Jonge vom Berg (Kalender)', website: 'https://jonge-vom-berg.de/events/?ical=1' },
];
const MONATE = { januar: 1, februar: 2, 'märz': 3, april: 4, mai: 5, juni: 6, juli: 7, august: 8, september: 9, oktober: 10, november: 11, dezember: 12 };
const heute = new Date(new Intl.DateTimeFormat('en-CA', { timeZone: 'Europe/Berlin' }).format(new Date()) + 'T00:00:00Z');
const bis = new Date(heute.getTime() + TAGE * 86400000);

async function holen(url) {
  try {
    const r = await fetch(url, { headers: { 'user-agent': UA }, redirect: 'follow', signal: AbortSignal.timeout(20000) });
    return { status: r.status, text: r.ok ? await r.text() : '' };
  } catch (e) { return { status: 0, text: '' }; }
}
const lesetext = (h) => h.replace(/<script[\s\S]*?<\/script>|<style[\s\S]*?<\/style>/g, ' ').replace(/<[^>]+>/g, ' ')
  .replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&#8211;|&ndash;/g, '–').replace(/\s+/g, ' ');

/** Alle Datumsangaben (TT.MM.JJJJ, TT. Monat JJJJ, ICS JJJJMMTT) im Fenster, mit Textstelle. */
export function datumsTreffer(text, von = heute, bisDatum = bis) {
  const raus = [];
  const add = (j, m, t, i) => { const d = new Date(Date.UTC(j, m - 1, t)); if (d >= von && d <= bisDatum) raus.push({ datum: d.toISOString().slice(0, 10), stelle: text.slice(Math.max(0, i - 80), i + 120).trim() }); };
  for (const m of text.matchAll(/\b(\d{1,2})\.(\d{1,2})\.(20\d\d)\b/g)) add(+m[3], +m[2], +m[1], m.index);
  for (const m of text.matchAll(/\b(\d{1,2})\.\s?(Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember)\s(20\d\d)\b/gi)) add(+m[3], MONATE[m[2].toLowerCase()], +m[1], m.index);
  for (const m of text.matchAll(/DTSTART[^:\n]*:(20\d\d)(\d\d)(\d\d)/g)) add(+m[1], +m[2], +m[3], m.index);
  // Terminlisten unter einer Jahresüberschrift („Termine 2026 … 15.11. Volkstrauertag“): Jahr aus der
  // letzten Jahreszahl der vorigen 600 Zeichen (Schützenbruderschaft Morschenich, 04.11.2026).
  for (const m of text.matchAll(/(?<![\d.])(\d{1,2})\.(\d{1,2})\.(?![\d])/g)) {
    const t = +m[1], mo = +m[2];
    if (t < 1 || t > 31 || mo < 1 || mo > 12) continue;
    const jahre = [...text.slice(Math.max(0, m.index - 600), m.index).matchAll(/\b(20\d\d)\b/g)];
    if (jahre.length) add(+jahre[jahre.length - 1][1], mo, t, m.index);
  }
  return raus;
}

const verz = JSON.parse(readFileSync(join(wurzel, 'deploy', 'vereinsverzeichnis.json'), 'utf8')).vereine || [];
const seiten = [...verz.filter((v) => v.website).map((v) => ({ name: v.name, website: v.website })), ...ZUSATZ];
const bekannt = new Set(readdirSync(join(wurzel, 'inhalte', 'termine')).filter((f) => f.endsWith('.json'))
  .flatMap((f) => (JSON.parse(readFileSync(join(wurzel, 'inhalte', 'termine', f), 'utf8')).termine || []).map((t) => String(t.start || '').slice(0, 10))));

const ergebnis = [];
await Promise.all(seiten.map(async (s) => {
  const start = await holen(s.website);
  const urls = [s.website];
  if (start.text) {
    const host = new URL(s.website).host;
    for (const m of start.text.matchAll(/href="([^"#]+)"/g)) {
      try { const u = new URL(m[1].replace(/&amp;/g, '&'), s.website); if (u.host === host && /termin|veranstalt|event|aktuell|news|kalender|programm/i.test(u.pathname)) urls.push(u.href); } catch {}
    }
  }
  const treffer = [];
  for (const u of [...new Set(urls)].slice(0, 5)) {
    const r = u === s.website ? start : await holen(u);
    for (const t of datumsTreffer(r.text.includes('BEGIN:VCALENDAR') ? r.text : lesetext(r.text))) treffer.push({ ...t, url: u });
  }
  const eindeutig = [...new Map(treffer.map((t) => [t.datum + t.stelle.slice(70, 110), t])).values()];
  ergebnis.push({ name: s.name, website: s.website, status: start.status, treffer: eindeutig.map((t) => ({ ...t, imKalender: bekannt.has(t.datum) })) });
}));
ergebnis.sort((a, b) => a.name.localeCompare(b.name));
if (args.includes('--json')) { console.log(JSON.stringify(ergebnis, null, 2)); process.exit(0); }
for (const e of ergebnis) {
  console.log(`\n## ${e.name} (${e.website}) HTTP ${e.status}, ${e.treffer.length} Datum/Daten bis ${bis.toISOString().slice(0, 10)}`);
  for (const t of e.treffer.sort((a, b) => a.datum.localeCompare(b.datum)).slice(0, 20)) console.log(`- ${t.datum} ${t.imKalender ? '(Tag im Kalender belegt)' : '(Tag NICHT im Kalender)'} ${t.url}\n    ${t.stelle}`);
}
console.log(`\n${ergebnis.length} Seiten, ${ergebnis.reduce((n, e) => n + e.treffer.length, 0)} Datumsangaben. Arbeitsliste, kein Beleg: Detailseite lesen, dann Termin anlegen oder bestätigen.`);
