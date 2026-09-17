#!/usr/bin/env node
/**
 * Rendert das Sportmodul (SC 1919 Merzenich) auf Startseite und Sportseite
 * serverseitig aus chatgpt-site/api/sport-current.json: Kacheln Platz/Punkte/
 * Tore, letztes Spiel, naechstes Spiel, Tabellenauszug, Datenstand.
 * Vorher stand der Datenstand fest im HTML und veraltete, waehrend v20.js im
 * Browser nur einen Teil nachzog. Jetzt zeigt schon das ausgelieferte HTML den
 * Stand der JSON; v20.js bleibt als Auffrischung im Browser.
 * Idempotent. Aufruf: node deploy/sport-prerender.mjs [--check]
 */
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const site = join(wurzel, 'chatgpt-site');
const daten = JSON.parse(readFileSync(join(site, 'api', 'sport-current.json'), 'utf8'));
for (const k of ['generated', 'table']) if (!(k in daten)) { console.error(`sport-current.json: ${k} fehlt`); process.exit(2); }

const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const dmy = (iso) => new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(iso));
const hm = (iso) => new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', hour: '2-digit', minute: '2-digit' }).format(new Date(iso)) + ' Uhr';
const istMerzenich = (name) => /Merzenich/i.test(name || '');
const team = (name) => `<div class="match-team">${istMerzenich(name) ? '<img src="/assets/uploads/sc-1919-merzenich-logo.webp" width="58" height="58" alt="Vereinslogo SC 1919 Merzenich" loading="lazy">' : ''}<b>${esc(name)}</b></div>`;
const stand = `Datenstand ${dmy(daten.generated)}, ${hm(daten.generated)}`;
const quelle = daten.source || 'FUSSBALL.DE';

function panelLetztes(m) {
  if (!m) return '';
  const bestaetigt = m.confirmed !== false && m.score;
  const quelleLink = m.reportUrl
    ? `<a class="match-source" href="${esc(m.reportUrl)}" target="_blank" rel="noopener">${/fussball\.de/i.test(m.reportUrl) ? 'Spielbericht bei FUSSBALL.DE' : 'Spielbericht beim Verein'}</a>`
    : '';
  if (bestaetigt) {
    return `<section class="match-panel" aria-label="Letztes Spielergebnis"><div class="match-heading"><b>Letztes Spiel</b><time datetime="${esc(m.date)}">${dmy(m.date)} · Endstand</time></div><div class="match-grid">${team(m.home)} <strong class="match-score">${esc(m.score)}</strong> ${team(m.away)}</div>${quelleLink}</section>`;
  }
  return `<section class="match-panel offen" aria-label="Begegnung mit offenem Ergebnis"><div class="match-heading"><b>Ergebnis noch offen</b><time datetime="${esc(m.date)}">${dmy(m.date)} · ${hm(m.date)}</time></div><div class="match-grid">${team(m.home)} <span class="match-versus">gegen</span> ${team(m.away)}</div><span class="match-offen-hinweis">Ergebnis noch nicht bestätigt</span></section>`;
}
function panelNaechstes(m) {
  if (!m) return '';
  return `<section class="match-panel" aria-label="Nächstes Spiel"><div class="match-heading"><b>Nächstes Spiel</b><time datetime="${esc(m.date)}">${dmy(m.date)} · ${hm(m.date)}</time></div><div class="match-grid">${team(m.home)} <span class="match-versus">gegen</span> ${team(m.away)}</div><a class="match-source" href="/sc-1919-merzenich/">Zum Vereinskanal</a></section>`;
}
function tabelle() {
  const zeilen = daten.table;
  const heim = zeilen.find((r) => r.homeTeam || /Merzenich/i.test(r.team));
  const auszug = zeilen.slice(0, 5);
  const heimAusserhalb = heim && !auszug.includes(heim);
  const zeile = (r) => `<tr${r === heim ? ' class="home-team"' : ''}><td>${r.place}</td><th scope="row">${esc(r.team)}</th><td>${r.played}</td><td>${r.wins}</td><td>${r.draws}</td><td>${r.losses}</td><td>${esc(r.goals)}</td><td>${r.diff > 0 ? '+' : ''}${r.diff}</td><td>${r.points}</td></tr>`;
  const body = auszug.map(zeile).join('') + (heimAusserhalb ? `<tr class="league-gap" aria-hidden="true"><td colspan="9">···</td></tr>${zeile(heim)}` : '');
  const beschreibung = heimAusserhalb ? 'erste fünf Mannschaften und SC 1919 Merzenich' : 'erste fünf Mannschaften';
  return `<section class="league-panel"><div class="league-heading"><h3>Kreisliga A · Tabellenauszug</h3><span>${stand}</span></div><div class="league-scroll" tabindex="0" role="region" aria-label="Fußballtabelle, horizontal scrollbar"><table class="league-table"><caption class="sr-only">Kreisliga A, ${beschreibung}. ${stand}</caption><thead><tr><th scope="col">Pl.</th><th scope="col">Mannschaft</th><th scope="col">Sp.</th><th scope="col">S</th><th scope="col">U</th><th scope="col">N</th><th scope="col">Tore</th><th scope="col">Diff.</th><th scope="col">Pkt.</th></tr></thead><tbody>${body}</tbody></table></div><a href="/sc-1919-merzenich/">Vollständige Tabelle &amp; Spielplan</a></section>`;
}
const modul = `<div class="sports-module" data-generated="${esc(daten.generated)}">${panelLetztes(daten.lastMatch)}${panelNaechstes(daten.nextMatch)}${tabelle()}</div>`;

function kacheln() {
  const heim = daten.table.find((r) => r.homeTeam || /Merzenich/i.test(r.team));
  if (!heim) return null;
  return `<div class="sc-stand" aria-label="Tabellenstand SC 1919 Merzenich"><div class="sc-kachel"><span class="sc-wert">${heim.place}.</span><span class="sc-label">Platz</span></div><div class="sc-kachel"><span class="sc-wert">${heim.points}</span><span class="sc-label">Punkte</span></div><div class="sc-kachel"><span class="sc-wert">${esc(heim.goals)}</span><span class="sc-label">Tore</span></div></div><p class="sc-stand-quelle">Kreisliga A, Kreis Düren · ${stand} · Quelle ${esc(quelle)}</p>`;
}

// ------------------------------------------------ Vereinskanal /sc-1919-merzenich/
// Kacheln, komplette Tabelle, Datenstand und Spielplan (gespielte Partien wandern
// mit Ergebnis in "Gespielte Partien") aus derselben JSON.
const MONATE = { Jan: 1, Feb: 2, 'Mär': 3, Mrz: 3, Apr: 4, Mai: 5, Jun: 6, Jul: 7, Aug: 8, Sep: 9, Okt: 10, Nov: 11, Dez: 12 };
const norm = (t) => String(t || '').toLowerCase().replace(/e\.\s*v\./g, '').replace(/[^a-zäöüß]/g, '');
const istGleich = (titel, m) => !!(m && norm(titel).includes(norm(m.home)) && norm(titel).includes(norm(m.away)));
function vereinskanal() {
  const pfad = join(site, 'sc-1919-merzenich', 'index.html');
  if (!existsSync(pfad)) return false;
  const alt = readFileSync(pfad, 'utf8'); let neu = alt;
  const heim = daten.table.find((r) => r.homeTeam || /Merzenich/i.test(r.team));
  if (heim) neu = neu.replace(/<div class="sc-stats">(?:<div><b>[^<]*<\/b><span>[^<]*<\/span><\/div>)+<\/div>/, () => `<div class="sc-stats"><div><b>${heim.place}.</b><span>Tabellenplatz</span></div><div><b>${heim.points}</b><span>Punkte</span></div><div><b>${esc(heim.goals)}</b><span>Torverhältnis</span></div><div><b>${heim.played}</b><span>Spiele</span></div></div>`);
  neu = neu.replace(/(<table class="sc-table">[\s\S]*?<tbody>)[\s\S]*?(<\/tbody>)/, (m, a, z) => a + daten.table.map((r) => `<tr${r === heim ? ' class="mark"' : ''}><td>${r.place}</td><td>${esc(r.team)}</td><td class="n">${r.played}</td><td class="n">${r.wins}</td><td class="n">${r.draws}</td><td class="n">${r.losses}</td><td class="n">${esc(r.goals)}</td><td class="n">${r.diff}</td><td class="n">${r.points}</td></tr>`).join('') + z);
  neu = neu.replace(/Stand \d{2}\.\d{2}\.\d{4}/g, `Stand ${dmy(daten.generated)}`);
  const lang = new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(daten.generated));
  neu = neu.replace(/Tabelle am [^<]*? bei FUSSBALL\.DE geprüft\./, `Tabelle am ${lang} bei FUSSBALL.DE geprüft.`);
  // Spielplan: Partien vor dem Datenstand (und das letzte Spiel) in "Gespielte Partien".
  const gen = new Date(daten.generated); const last = daten.lastMatch;
  const rowRe = /<article class="event-row"><span class="d"><b>(\d+)<\/b><span>([A-Za-zäöü]+)<\/span><\/span><div class="info"><span class="eyebrow">([^<]*)<\/span><h3>([^<]*)<\/h3><div class="meta"><span>[^<]*<\/span><\/div><\/div><\/article>/g;
  const gespielt = [];
  neu = neu.replace(rowRe, (m, tag, mon, eyebrow, titel) => {
    const monat = MONATE[mon]; if (!monat) return m;
    let jahr = gen.getFullYear(); if (monat < gen.getMonth() + 1 - 6) jahr++;
    const datum = Date.UTC(jahr, monat - 1, Number(tag), 12);
    const gleich = istGleich(titel, last);
    if (!gleich && datum > gen.getTime()) return m;
    if (neu.includes(`<li><b>${titel}`)) return '';
    const wettbewerb = /pokal/i.test(eyebrow) ? 'Kreispokal' : 'Kreisliga A';
    const score = gleich && last.confirmed !== false && last.score ? ` <span class="res">${esc(String(last.score).replace(/\s/g, ''))}</span>` : '';
    const d = gleich ? dmy(last.date) : `${String(tag).padStart(2, '0')}.${String(monat).padStart(2, '0')}.${jahr}`;
    gespielt.push(`<li><b>${titel}${score}</b><small>${d} · ${wettbewerb}</small></li>`);
    return '';
  });
  if (gespielt.length) neu = neu.replace(/(<h2>Gespielte Partien<\/h2>[\s\S]*?<ul class="linklist plainlist">[\s\S]*?)(<\/ul>)/, (m, a, z) => a + gespielt.join('') + z);
  if (neu !== alt) { if (!nurPruefen) writeFileSync(pfad, neu); return true; }
  return false;
}

const MODUL_RE = /<div class="sports-module"[^>]*>[\s\S]*?<\/section><\/div>/;
const KACHEL_RE = /<div class="sc-stand"[\s\S]*?<\/div><p class="sc-stand-quelle">[\s\S]*?<\/p>/;
let geaendert = 0, fehler = 0;
for (const rel of ['index.html', 'sport/index.html']) {
  const pfad = join(site, rel);
  const alt = readFileSync(pfad, 'utf8');
  if (!MODUL_RE.test(alt)) { fehler++; console.error(`${rel}: kein Sportmodul gefunden`); continue; }
  let neu = alt.replace(MODUL_RE, () => modul);
  const k = kacheln();
  if (k && KACHEL_RE.test(neu)) neu = neu.replace(KACHEL_RE, () => k);
  if (neu !== alt) { geaendert++; if (!nurPruefen) writeFileSync(pfad, neu); }
}
if (vereinskanal()) geaendert++;
console.log(`Sportmodul: ${stand}; ${geaendert} Seite(n) ${nurPruefen ? 'nicht aktuell' : 'geaendert'}, ${fehler} Fehler.`);
if (fehler || (nurPruefen && geaendert)) process.exit(2);
