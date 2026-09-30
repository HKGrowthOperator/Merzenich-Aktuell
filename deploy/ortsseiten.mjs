#!/usr/bin/env node
/**
 * Ortsseiten und Vereinsverzeichnis (29.09.2026).
 *
 * Vorher stand in der Seitenleiste der fünf Ortsseiten ein eingefrorener
 * Kasten „Vereine in …“ aus dem alten Seitengenerator (höchstens zwei
 * Profile, in Merzenich, Bürgewald und teils Girbelsrath gar keiner), und
 * keine Termine. Das Vereinsverzeichnis /vereine/ kannte nur die elf Vereine
 * mit eigenem Profil.
 *
 * Jetzt:
 * - Ortsseiten: „Nächste Termine in <Ort>“ aus den Terminseiten
 *   (deploy/lib-termine.mjs, höchstens 4, nur laufende und kommende) und
 *   „Vereine in <Ort>“ aus den Profilen plus dem Vereinsverzeichnis
 *   (deploy/vereinsverzeichnis.json, belegt aus Bürgerbroschüre, Amtsblättern,
 *   Heimat-Info und Vereinswebsites).
 *   Zwischen <!-- ort:seitenleiste:start/end --> in der Seitenleiste.
 * - /vereine/: „Weitere Vereine im Gemeindeverzeichnis“, alle Vereine ohne
 *   eigenes Profil, nach Ort gruppiert, mit Link auf ihre Website.
 *   Zwischen <!-- vereine:weitere:start/end -->.
 *
 * Ort nur, wenn er belegt ist; sonst „gemeindeweit“. Keine Kontaktdaten:
 * nur Name, Website und Art (bei Sportvereinen die Sportart).
 *
 * Aufruf: node deploy/ortsseiten.mjs [--check]
 */
import { readFileSync, writeFileSync, readdirSync, existsSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { esc } from './lib-artikel.mjs';
import { termineAusSeiten, berliner } from './lib-termine.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(wurzel, 'chatgpt-site');
const nurPruefen = process.argv.includes('--check');
const ORTE = { merzenich: 'Merzenich', golzheim: 'Golzheim', girbelsrath: 'Girbelsrath', morschenich: 'Morschenich', buergewald: 'Bürgewald' };
const vergleich = new Intl.Collator('de');

// Profile: Frontmatter aus site-source/content/vereine, nur wenn die Seite existiert.
function profile() {
  const ordner = join(wurzel, 'site-source', 'content', 'vereine');
  const raus = [];
  for (const datei of readdirSync(ordner).filter((d) => d.endsWith('.md'))) {
    const slug = datei.slice(0, -3);
    if (!existsSync(join(site, 'vereine', slug, 'index.html'))) continue;
    const kopf = /^---\n([\s\S]*?)\n---/.exec(readFileSync(join(ordner, datei), 'utf8'))?.[1] || '';
    const feld = (n) => (new RegExp(`^${n}:\\s*"?(.*?)"?\\s*$`, 'm').exec(kopf) || [])[1] || '';
    if (!ORTE[feld('ort')]) throw new Error(`Vereinsprofil ${slug}: Ort "${feld('ort')}" unbekannt`);
    raus.push({ name: feld('name'), ort: feld('ort'), kategorie: feld('category'), url: `/vereine/${slug}/`, profil: slug });
  }
  return raus;
}

// Vereinsverzeichnis der Gemeinde (deploy/vereinsverzeichnis.json, 30.09.2026):
// 63 belegte Vereine aus Bürgerbroschüre, Amtsblättern, Heimat-Info und
// Vereinswebsites. Ersetzt die Heimat-Info-Liste allein (ihr fehlten u. a.
// SV Morschenich, TTC, TV Golzheim, Schützen Merzenich).
const VZ = JSON.parse(readFileSync(join(wurzel, 'deploy', 'vereinsverzeichnis.json'), 'utf8'));
function verzeichnis(profilSlugs) {
  const raus = [];
  for (const v of VZ.vereine) {
    if (v.ort && !ORTE[v.ort]) throw new Error(`Vereinsverzeichnis: "${v.name}" mit unbekanntem Ort ${v.ort}`);
    if (!v.quelle) throw new Error(`Vereinsverzeichnis: "${v.name}" ohne Quelle`);
    if (v.website && !/^https?:\/\/[^\s"<>]+$/.test(v.website)) throw new Error(`Vereinsverzeichnis: "${v.name}" website muss http(s):// sein`);
    if (v.profil && !profilSlugs.has(v.profil)) throw new Error(`Vereinsverzeichnis: Profil ${v.profil} für "${v.name}" fehlt`);
    raus.push({ name: v.name, ort: v.ort || '', kategorie: v.kategorie === 'Sport' && v.sportart ? v.sportart : v.kategorie, url: v.website || '', profil: v.profil || '' });
  }
  return { liste: raus, stand: VZ.stand };
}

const prof = profile();
const { liste: hiListe, stand } = verzeichnis(new Set(prof.map((p) => p.profil)));
if (!/^\d{4}-\d{2}-\d{2}$/.test(stand)) throw new Error('vereinsverzeichnis.json: stand fehlt');
const standText = `${stand.slice(8, 10)}.${stand.slice(5, 7)}.${stand.slice(0, 4)}`;
const ohneProfil = hiListe.filter((v) => !v.profil);
const nachName = (a, b) => vergleich.compare(a.name, b.name);

const extLink = (v) => (v.url ? `<a href="${esc(v.url)}" target="_blank" rel="noopener">${esc(v.name)}</a>` : `<span>${esc(v.name)}</span>`);
const wochentag = new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', weekday: 'short' });
function terminZeit(t) {
  const b = berliner(t.start);
  const tag = `${wochentag.format(t.start).replace('.', '')}, ${b.tag}.${b.monat}.`;
  return t.ohneEnde && b.stunde === '00' && b.minute === '00' ? tag : `${tag}, ${b.stunde}:${b.minute} Uhr`;
}

const jetzt = new Date();
const termine = termineAusSeiten(site).filter((t) => t.ende >= jetzt).sort((a, b) => a.start - b.start);

function seitenleiste(slug) {
  const ort = ORTE[slug];
  const tl = termine.filter((t) => t.ortsteil === ort).slice(0, 4);
  const terminBox = `<div class="sidebox ort-termine"><h3>Nächste Termine in ${esc(ort)}<a href="/termine/">alle</a></h3>`
    + (tl.length
      ? `<ul class="linklist">${tl.map((t) => `<li><a href="/termine/${esc(t.slug)}/">${esc(t.titel)}</a><small>${esc(terminZeit(t))}${t.ort ? ` · ${esc(t.ort)}` : ''}</small></li>`).join('')}</ul>`
      : `<p class="ort-leer">Zurzeit ist kein Termin in ${esc(ort)} angekündigt. <a href="/termine/melden/">Termin melden</a></p>`)
    + '</div>';
  const vereine = [
    ...prof.filter((p) => p.ort === slug).sort(nachName).map((p) => `<li><a href="${esc(p.url)}">${esc(p.name)}</a><small>${esc(p.kategorie)}</small></li>`),
    ...ohneProfil.filter((v) => v.ort === slug).sort(nachName).map((v) => `<li>${extLink(v)}<small>${esc(v.kategorie)}${v.url ? ' · Website ↗' : ''}</small></li>`),
  ];
  const vereinBox = `<div class="sidebox ort-vereine"><h3>Vereine in ${esc(ort)}<a href="/vereine/">alle</a></h3>`
    + (vereine.length ? `<ul class="linklist">${vereine.join('')}</ul>` : `<p class="ort-leer">Noch kein Verein aus ${esc(ort)} im Verzeichnis.</p>`)
    + `<p class="ort-leer">Vereinsverzeichnis der Gemeinde, Stand ${standText}. ↗ führt zur Website des Vereins. <a href="/vereine/eintragen/">Verein eintragen</a></p></div>`;
  return terminBox + vereinBox;
}

function weitereVereine() {
  const gruppen = [...Object.entries(ORTE), ['', 'Gemeindeweit']]
    .map(([slug, name]) => [name, ohneProfil.filter((v) => v.ort === slug).sort(nachName)])
    .filter(([, l]) => l.length);
  return '<section class="vereine-weitere" aria-labelledby="vereine-weitere-titel">'
    + '<h2 id="vereine-weitere-titel">Weitere Vereine im Gemeindeverzeichnis</h2>'
    + `<p class="vereine-weitere-dek">${ohneProfil.length} weitere Vereine und Gruppen aus der Gemeinde Merzenich (Stand ${standText}; Quelle: <a href="${esc(VZ.quelleUrl)}" target="_blank" rel="noopener">Bürgerbroschüre der Gemeinde</a>, Amtsblätter, Heimat-Info). Verlinkt ist die eigene Website des Vereins, wo es eine gibt. Jeder Verein bekommt auf Wunsch einen eigenen Redaktionszugang: Meldungen, Termine und Fotos einreichen, die Redaktion gibt frei. <a href="/vereine/eintragen/">Verein eintragen oder Zugang anfragen</a>.</p>`
    + `<div class="vereine-weitere-orte">${gruppen.map(([name, l]) => `<div><h3>${esc(name)}</h3><ul class="linklist">${l.map((v) => `<li>${extLink(v)}${v.kategorie ? `<small>${esc(v.kategorie)}</small>` : ''}</li>`).join('')}</ul></div>`).join('')}</div>`
    + '</section>';
}

let geaendert = 0;
function schreibe(pfad, marke, inhalt, einsetzen) {
  const alt = readFileSync(pfad, 'utf8');
  const re = new RegExp(`<!-- ${marke}:start -->[\\s\\S]*?<!-- ${marke}:end -->`);
  let basis = alt;
  if (!re.test(basis)) {
    basis = einsetzen(basis);
    if (!re.test(basis)) throw new Error(`${pfad}: Marker ${marke} fehlt und lässt sich nicht einsetzen`);
  }
  const neu = basis.replace(re, () => `<!-- ${marke}:start -->${inhalt}<!-- ${marke}:end -->`);
  if (neu !== alt) { geaendert++; if (!nurPruefen) writeFileSync(pfad, neu); }
}

// Einmalige Umstellung: den eingefrorenen Seitenleisten-Inhalt durch die Marker ersetzen.
const leisteEinsetzen = (h) => h.replace(/<aside class="sidebar">[\s\S]*?<\/aside>/, '<aside class="sidebar"><!-- ort:seitenleiste:start --><!-- ort:seitenleiste:end --></aside>');
for (const slug of Object.keys(ORTE)) schreibe(join(site, slug, 'index.html'), 'ort:seitenleiste', seitenleiste(slug), leisteEinsetzen);

const leerZeile = '<p class="no-result" data-filter-empty hidden>Keine Einträge für diese Auswahl.</p>';
schreibe(join(site, 'vereine', 'index.html'), 'vereine:weitere', weitereVereine(), (h) => h.replace(leerZeile, `${leerZeile}\n  <!-- vereine:weitere:start --><!-- vereine:weitere:end -->`));

console.log(`Ortsseiten: ${prof.length} Profile, ${hiListe.length} Vereine im Verzeichnis (${ohneProfil.length} ohne Profil), ${termine.length} kommende Termine; ${geaendert} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}.`);
if (nurPruefen && geaendert) process.exit(2);
