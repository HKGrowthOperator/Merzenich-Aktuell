#!/usr/bin/env node
/**
 * Merzenich Aktuell - Lagebild aus dem Recherche-Register (26.10.2026).
 *
 * docs/redaktion/register.json fuehrt inzwischen weit ueber hundert Themen, die meisten
 * davon historisch (archiv_refresh_noetig). Jeder Redaktionslauf las die ganze Datei, um
 * die wenigen offenen Themen zu finden, und prüfte den Artikelbestand je Thema von Hand.
 *
 * Dieses Skript schreibt docs/redaktion/offen.md: die offenen Themen (neu, in_bearbeitung,
 * zur_abnahme) nach Prioritaet und Textstatus, je Thema den Hinweis auf moeglichen Bestand
 * (Artikel, deren Slug oder Titel die Stichwoerter der Themen-ID enthaelt) und die Quellen
 * des Registers; dazu die Zahlen je Status und die juengsten Laeufe. Das Register selbst
 * wird nicht veraendert.
 *
 * --check prueft die Konsistenz: Themen mit Status uebernommen brauchen Artikelpfad und
 * Commit, jede Datei im Artikelpfad muss existieren, jedes latest_dossier/dossier muss
 * existieren; Verstoesse brechen mit Code 2 ab (deploy/kette.mjs ruft das nicht auf,
 * der Redaktionslauf schon).
 *
 * Aufruf: node deploy/register-pflege.mjs [--check] [--stdout]
 */
import { readFileSync, writeFileSync, existsSync, readdirSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const REGISTER = join(wurzel, 'docs', 'redaktion', 'register.json');
const ZIEL = join(wurzel, 'docs', 'redaktion', 'offen.md');
const OFFEN = ['neu', 'in_bearbeitung', 'zur_abnahme'];
const PRIO = { P1: 1, P2: 2, P3: 3 };
const nurPruefen = process.argv.includes('--check');
const stdout = process.argv.includes('--stdout');
const json = (p) => JSON.parse(readFileSync(p, 'utf8'));

/** Stichwoerter einer Themen-ID ("MA-SC3-GEREONSWEILER-2510" -> ["sc3", "gereonsweiler"]). */
export function stichwoerter(id) {
  const stopp = new Set(['ma', 'kd', 'gr', 'go', 'nz', 'dn', 'els', 'noe', 'buir', 'neu', 'job', 'erg', 'laeuft', 'tops', 'details', 'status', 'index', 'qa']);
  return String(id).toLowerCase().split('-').filter((t) => t.length >= 3 && !/^\d+$/.test(t) && !stopp.has(t) && !/^\d{4}$/.test(t));
}

/** Artikelbestand: Slug, Titel, Datei und Quellen-URLs aller Meldungen und Termine. */
export function bestandLesen(basis) {
  const aus = [];
  for (const art of ['meldungen', 'termine']) {
    const ordner = join(basis, 'inhalte', art);
    for (const d of existsSync(ordner) ? readdirSync(ordner).filter((n) => n.endsWith('.json')).sort() : []) {
      const paket = json(join(ordner, d));
      for (const m of paket.meldungen || paket.termine || []) {
        aus.push({ art, datei: `inhalte/${art}/${d}`, slug: m.slug || m.id || '', titel: m.titel || '', datum: String(m.datum || m.start || '').slice(0, 10), urls: [m.quelle, ...(m.weitereQuellen || [])].map((q) => q?.url).filter(Boolean) });
      }
    }
  }
  return aus;
}

/** Moegliche Dubletten: gleiche Quellen-URL oder Stichwort der ID in Slug/Titel. */
export function bestandZu(thema, bestand) {
  const urls = new Set((thema.research_sources || []).map((q) => q.url).filter(Boolean));
  const woerter = stichwoerter(thema.id);
  const norm = (s) => String(s).toLowerCase().replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss');
  return bestand.filter((b) => b.urls.some((u) => urls.has(u)) || woerter.some((w) => w.length >= 5 && (norm(b.slug).includes(norm(w)) || norm(b.titel).includes(norm(w)))))
    .map((b) => ({ ...b, grund: b.urls.some((u) => urls.has(u)) ? 'gleiche Quelle' : 'Stichwort' }));
}

export function lagebild(register, bestand) {
  const topics = register.topics || [];
  const zaehler = {};
  for (const t of topics) zaehler[t.workflow_status || '?'] = (zaehler[t.workflow_status || '?'] || 0) + 1;
  const offen = topics.filter((t) => OFFEN.includes(t.workflow_status))
    .sort((a, b) => (PRIO[a.priority] || 9) - (PRIO[b.priority] || 9) || String(a.text_status || 'Z').localeCompare(String(b.text_status || 'Z')) || a.id.localeCompare(b.id));
  const z = ['# Offene Themen aus dem Register', '', `Automatisch aus [register.json](register.json) erzeugt (deploy/register-pflege.mjs). Stand des Registers: ${register.latest_dossier || '?'}. Nicht von Hand bearbeiten; Status im Register pflegen, dann neu erzeugen.`, '',
    '## Zahlen', '', '| Status | Themen |', '|---|---:|', ...Object.entries(zaehler).sort((a, b) => b[1] - a[1]).map(([k, v]) => `| ${k} | ${v} |`), '',
    `## Offen (${offen.length})`, '', 'Textstatus: T1 belegter Entwurf (Endabnahme nötig), T1-B Bestandsentwurf ohne erneute Primärprüfung, T2 Freigabebedingung offen, T0 gesperrt/unvollständig. „Möglicher Bestand“ nennt Artikel mit gleicher Quelle oder gleichem Stichwort; vor Umsetzung lesen (Update statt Doppelartikel).', ''];
  for (const t of offen) {
    const bestandHier = bestandZu(t, bestand);
    z.push(`### ${t.id} · ${t.priority || '–'} · ${t.text_status || '–'}/${t.image_status || '–'} · ${t.workflow_status}`, '');
    z.push(`- Ort/Ressort: ${t.location_section || '–'}; Art: ${t.change_type || '–'}; zuletzt geprüft: ${t.latest_research_checked_at || t.last_checked_at || '–'}${t.refresh_note ? `; Refresh: ${t.refresh_note}` : ''}`);
    const dossiers = [...new Set([t.latest_research_dossier, t.dossier, ...(t.dossier_history || []).map((h) => h.dossier)].filter(Boolean))];
    if (dossiers.length) z.push(`- Dossier: ${dossiers.map((d) => `[${d.replace(/^dossiers\//, '')}](${d})`).join(', ')}`);
    for (const q of t.research_sources || []) if (q?.url) z.push(`- Quelle: ${q.url}${q.publisher ? ` (${q.publisher})` : ''}`);
    if (t.article_path) z.push(`- Artikelpfad: ${t.article_path}`);
    if (bestandHier.length) for (const b of bestandHier.slice(0, 4)) z.push(`- Möglicher Bestand (${b.grund}): \`${b.slug}\` – ${b.titel} (${b.datum}, ${b.datei})`);
    else z.push('- Möglicher Bestand: keiner gefunden');
    if (t.notes) z.push(`- Notiz: ${String(t.notes).replace(/\s+/g, ' ').slice(0, 300)}`);
    z.push('');
  }
  const laeufe = (register.research_runs || []).slice(-4).reverse();
  if (laeufe.length) { z.push('## Jüngste Läufe', ''); for (const l of laeufe) z.push(`- ${l.date} ${l.edition || ''} (${l.by || '?'}): ${l.summary || ''}${l.implementation_commit ? ` – Commit ${l.implementation_commit}` : ''}`); z.push(''); }
  return z.join('\n');
}

/** Konsistenz: Pfade und Pflichtfelder. Liefert Liste der Verstoesse. */
export function pruefen(register, basis) {
  const f = [];
  for (const t of register.topics || []) {
    if (t.workflow_status === 'uebernommen' && !t.article_path) f.push(`${t.id}: uebernommen ohne Artikelpfad`);
    if (t.workflow_status === 'uebernommen' && !t.implementation_commit) f.push(`${t.id}: uebernommen ohne Commit`);
    for (const d of [t.dossier, t.latest_research_dossier].filter(Boolean)) if (!existsSync(join(basis, 'docs', 'redaktion', d))) f.push(`${t.id}: Dossier fehlt: ${d}`);
    const datei = /^(inhalte\/[^\s→]+\.json)/.exec(t.article_path || '')?.[1];
    if (datei && !existsSync(join(basis, datei))) f.push(`${t.id}: Artikeldatei fehlt: ${datei}`);
  }
  if (register.latest_dossier && !existsSync(join(basis, 'docs', 'redaktion', register.latest_dossier))) f.push(`latest_dossier fehlt: ${register.latest_dossier}`);
  return f;
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  const register = json(REGISTER);
  const fehler = pruefen(register, wurzel);
  for (const x of fehler) console.error(`Register: ${x}`);
  const text = lagebild(register, bestandLesen(wurzel));
  const alt = existsSync(ZIEL) ? readFileSync(ZIEL, 'utf8') : '';
  if (stdout) process.stdout.write(text);
  else if (!nurPruefen && text !== alt) writeFileSync(ZIEL, text);
  const offen = (register.topics || []).filter((t) => OFFEN.includes(t.workflow_status)).length;
  console.log(`Register: ${(register.topics || []).length} Themen, ${offen} offen, ${fehler.length} Verstöße; offen.md ${text !== alt ? (nurPruefen ? 'nicht aktuell' : 'geschrieben') : 'aktuell'}.`);
  if (fehler.length || (nurPruefen && text !== alt)) process.exit(2);
}
