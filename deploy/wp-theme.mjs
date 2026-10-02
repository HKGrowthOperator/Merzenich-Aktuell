#!/usr/bin/env node
/**
 * WordPress-Theme aus der gebauten Seite nachziehen (30.09.2026).
 *
 * Die WordPress-Seite (merzenich-aktuell.de) soll genau so aussehen wie die
 * statische Seite. Statt Kopf, Fuß und Startseite ein zweites Mal von Hand in
 * PHP zu pflegen, schneidet dieses Skript sie aus chatgpt-site/index.html aus
 * und legt sie als Vorlagen ins Theme:
 *
 *   vorlagen/kopf-assets.html  Schriften-Preload, Icons, Stylesheets
 *   vorlagen/kopf.html         Masthead, Ressort-Navigation, Ortswahl, Schublade
 *   vorlagen/fuss.html         Merzenich-Linie, Fuß, Skripte
 *   vorlagen/startseite.html   Inhalt von <main> mit Platzhaltern {{ma:oben}},
 *                              {{ma:gemeinde}} … für die Nachrichtenblöcke, die
 *                              WordPress aus seinen Beiträgen setzt
 *                              (theme/inc/startseite.php)
 *
 * Alles andere auf der Startseite (Merzenich jetzt, Servicespalte, Märkte,
 * Orte, Umkreis, Foto des Tages) kommt unverändert mit. Die Werbeflächen
 * werden zu Platzhaltern {{ma:werbung:…}} mit dem Muster als eigener Vorlage.
 * Aufruf: node deploy/wp-theme.mjs [--check]
 */
import { readFileSync, writeFileSync, mkdirSync, existsSync, readdirSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { createHash } from 'node:crypto';
import { BUNDLE_START_TEILE } from './css-bundle.mjs';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const ziel = join(wurzel, 'wordpress', 'theme', 'merzenich-aktuell', 'vorlagen');
const html = readFileSync(join(wurzel, 'chatgpt-site', 'index.html'), 'utf8');

const zwischen = (start, ende, text = html) => {
  const a = text.indexOf(start);
  const b = text.indexOf(ende, a + start.length);
  if (a < 0 || b < 0) throw new Error(`wp-theme: Abschnitt ${start} … ${ende} nicht gefunden`);
  return text.slice(a, b);
};

// Kopf-Assets: Preloads, Icons, Stylesheets. Titel, Meta, JSON-LD und
// Canonical setzt WordPress selbst (wp_head), sie hängen am Inhalt.
const head = zwischen('<head>', '</head>');
// Stylesheets: WordPress laedt statt der neun Einzeldateien ein Buendel
// (deploy/css-bundle.mjs): bundle.css auf Unterseiten, bundle-start.css auf der
// Startseite. Beide stehen in der Vorlage, markiert mit data-ma-css; das Theme
// (ma21_kopf_assets) laesst nur das passende stehen. Die Reihenfolge der
// Einzeldateien in index.html muss der Buendelreihenfolge entsprechen, sonst
// saehe WordPress anders aus als die statische Seite.
const einzel = [...head.matchAll(/<link rel="stylesheet" href="\/assets\/([a-z0-9-]+\.css)(?:\?[^"]*)?"[^>]*>/g)].map((m) => m[1]);
if (einzel.join(' ') !== BUNDLE_START_TEILE.join(' ')) throw new Error(`wp-theme: Stylesheets in index.html (${einzel.join(', ')}) passen nicht zum CSS-Buendel (${BUNDLE_START_TEILE.join(', ')})`);
const hashVon = (name) => createHash('sha256').update(readFileSync(join(wurzel, 'chatgpt-site', 'assets', name))).digest('hex').slice(0, 10);
const buendel = `<link rel="stylesheet" href="/assets/bundle.css?v=${hashVon('bundle.css')}" data-ma-css="seite">\n<link rel="stylesheet" href="/assets/bundle-start.css?v=${hashVon('bundle-start.css')}" data-ma-css="start">`;
const assets = [...head.matchAll(/<link rel="(?:preload|icon|apple-touch-icon)"[^>]*>/g)].map((m) => m[0]).join('\n') + '\n' + buendel;

// Kopf: vom Masthead bis vor <main>. Fuß: nach </main> bis vor </body>.
const koerper = zwischen('<body', '</body>');
const kopf = zwischen('<header class="masthead"', '<main id="main">', koerper).trim();
// Tagesdatum im Kopf: die statische Seite laesst nginx das Datum per SSI
// einsetzen; auf WordPress fuellt es theme/inc/ma21.php (ma21_kopf) zur
// Laufzeit. Der SSI-Block wird zum Platzhalter {{ma:datum}}.
const ohneSsiDatum = (h) => h.replace(/<time data-today datetime="<!--#[\s\S]*?<\/time>/g, '{{ma:datum}}');
const fuss = koerper.slice(koerper.indexOf('</main>') + '</main>'.length).trim();

// Startseite: Inhalt von <main>, Nachrichtenblöcke als Platzhalter.
let start = zwischen('<main id="main">', '</main>', koerper).slice('<main id="main">'.length).trim();
// Anzeige in der Buehne (dritte Karte unten): liegt im Block start:oben, den
// WordPress selbst baut; deshalb als eigene Vorlage.
const anzeigeBuehne = (/<!-- werbung:buehne:start -->[\s\S]*?<!-- werbung:buehne:end -->/.exec(start) || [''])[0];
if (!anzeigeBuehne) throw new Error('wp-theme: Anzeige werbung:buehne fehlt in index.html');
// Werbeflaeche der Unternehmensseite (rechte Spalte), fuer theme/unternehmen.php.
const anzeigeUnternehmen = (/<!-- werbung:unternehmen:start -->[\s\S]*?<!-- werbung:unternehmen:end -->/.exec(readFileSync(join(wurzel, 'chatgpt-site', 'unternehmen', 'index.html'), 'utf8')) || [''])[0];
if (!anzeigeUnternehmen) throw new Error('wp-theme: Anzeige werbung:unternehmen fehlt in unternehmen/index.html');
// Sportseite: Spielstand-Ecke und Spiel-/Tabellenmodul aus demselben Datenstand
// wie die statische Seite (sport-prerender), fuer theme/archive.php (Kategorie sport).
const sportSeite = readFileSync(join(wurzel, 'chatgpt-site', 'sport', 'index.html'), 'utf8');
const sportEcke = (/<aside class="sport-ecke"[\s\S]*?<!--\/sport-ecke--><\/aside>/.exec(sportSeite) || [''])[0];
const sportModul = (/<div class="sports-module"[\s\S]*?<\/section><\/div>(?=\s*<div class="content-grid">)/.exec(sportSeite) || [''])[0];
if (!sportModul) throw new Error('wp-theme: sports-module fehlt in sport/index.html');
// Werbeflächen der Startseite (Bänder 1–6, Servicespalte): als Platzhalter
// {{ma:werbung:<marker>}}; das Muster-HTML wird je Fläche Vorlage, damit
// WordPress dort eine laufende Anzeige der Werbeverwaltung zeigen kann und
// sonst die Musteranzeige (theme/inc/ma21.php, ma21_werbung()).
const werbeVorlagen = {};
start = start.replace(/<!-- werbung:(band-[1-6]|spalte):start -->[\s\S]*?<!-- werbung:\1:end -->/g, (m, marker) => { werbeVorlagen[`werbung-${marker}.html`] = m; return `{{ma:werbung:${marker}}}`; });
for (const marker of ['band-1', 'band-2', 'band-3', 'band-4', 'band-5', 'band-6', 'spalte']) if (!werbeVorlagen[`werbung-${marker}.html`]) throw new Error(`wp-theme: Werbefläche werbung:${marker} fehlt in index.html`);
// Werbefläche der Sportseite (Seitenspalte) und der Meldungsseiten (unter dem Text).
const anzeigeSport = (/<!-- werbung:sport:start -->[\s\S]*?<!-- werbung:sport:end -->/.exec(readFileSync(join(wurzel, 'chatgpt-site', 'sport', 'index.html'), 'utf8')) || [''])[0];
if (!anzeigeSport) throw new Error('wp-theme: Anzeige werbung:sport fehlt in sport/index.html');
const anzeigeArtikel = (/<!-- werbung:artikel:start -->[\s\S]*?<!-- werbung:artikel:end -->/.exec(readFileSync(join(wurzel, 'chatgpt-site', 'blaulicht', 'einsatz-118-rosspfad', 'index.html'), 'utf8')) || [''])[0];
if (!anzeigeArtikel) throw new Error('wp-theme: Anzeige werbung:artikel fehlt in blaulicht/einsatz-118-rosspfad/index.html');
const SLOTS = ['oben', 'gemeinde', 'blaulicht', 'rathaus', 'wirtschaft', 'vereine'];
for (const s of SLOTS) {
  const re = new RegExp(`<!-- start:${s}:start -->[\\s\\S]*?<!-- start:${s}:end -->`);
  if (!re.test(start)) throw new Error(`wp-theme: Block start:${s} fehlt in index.html`);
  start = start.replace(re, `{{ma:${s}}}`);
}

// Unterseiten tragen einen Kopf ohne „Merzenich jetzt“-Zeile. Vorlage ist das
// Impressum (keine Rubrik hervorgehoben).
const seite = readFileSync(join(wurzel, 'chatgpt-site', 'impressum', 'index.html'), 'utf8');
const kopfSeite = zwischen('<header class="masthead"', '<main id="main">', zwischen('<body', '</body>', seite)).trim().replace(/ aria-current="page"/g, '');

const dateien = {
  'kopf-assets.html': assets,
  'kopf.html': ohneSsiDatum(kopf),
  'kopf-seite.html': ohneSsiDatum(kopfSeite),
  'fuss.html': fuss,
  'startseite.html': start,
  'werbung-buehne.html': anzeigeBuehne,
  'werbung-unternehmen.html': anzeigeUnternehmen,
  'werbung-sport.html': anzeigeSport,
  'werbung-artikel.html': anzeigeArtikel,
  ...werbeVorlagen,
  'sport-ecke.html': sportEcke,
  'sport-modul.html': sportModul,
};
// Vereinsverzeichnis fuer die Vereinszugaenge im Plugin (includes/vereinszugaenge.php).
const vzZiel = join(wurzel, 'wordpress', 'plugin', 'merzenich-aktuell-core', 'data', 'vereinsverzeichnis.json');
const vzNeu = readFileSync(join(wurzel, 'deploy', 'vereinsverzeichnis.json'), 'utf8');
let vzGeaendert = 0;
if (!existsSync(vzZiel) || readFileSync(vzZiel, 'utf8') !== vzNeu) { vzGeaendert = 1; if (!nurPruefen) { mkdirSync(dirname(vzZiel), { recursive: true }); writeFileSync(vzZiel, vzNeu); } }
// Geprüfte Vereinsprofile der statischen Seite (site-source/content/vereine/*.md):
// Beschreibung, Gründung, Adresse, Logo. Das Plugin übernimmt sie beim Anlegen
// der WordPress-Profile (includes/vereine.php), passend über den Slug.
const vpQuelle = join(wurzel, 'site-source', 'content', 'vereine');
const vpProfile = {};
for (const datei of existsSync(vpQuelle) ? readdirSync(vpQuelle).filter((d) => d.endsWith('.md')).sort() : []) {
  const roh = readFileSync(join(vpQuelle, datei), 'utf8');
  const m = roh.match(/^---\n([\s\S]*?)\n---\n?([\s\S]*)$/);
  if (!m) continue;
  const fm = {}; let block = null;
  for (const zeile of m[1].split('\n')) {
    const tief = zeile.match(/^ {2}([a-z_]+):\s*(.*)$/);
    const flach = zeile.match(/^([a-z_]+):\s*(.*)$/);
    const wert = (v) => v.trim().replace(/^"(.*)"$/, '$1');
    if (tief && block) fm[block][tief[1]] = wert(tief[2]);
    else if (flach) { block = flach[2].trim() === '' ? flach[1] : null; fm[flach[1]] = block ? {} : wert(flach[2]); }
  }
  if (!fm.slug) continue;
  vpProfile[fm.slug] = { name: fm.name || '', kategorie: fm.category || '', beschreibung: fm.description || '', gegruendet: fm.founded || '', adresse: fm.address || '',
    website: fm.website || '', stand: fm.updated || '', text: m[2].trim(), bild: typeof fm.image === 'object' ? fm.image : null };
}
const vpZiel = join(wurzel, 'wordpress', 'plugin', 'merzenich-aktuell-core', 'data', 'vereinsprofile.json');
const vpNeu = JSON.stringify({ _hinweis: 'Erzeugt aus site-source/content/vereine/*.md (deploy/wp-theme.mjs). Nicht von Hand ändern.', profile: vpProfile }, null, 1) + '\n';
if (!existsSync(vpZiel) || readFileSync(vpZiel, 'utf8') !== vpNeu) { vzGeaendert++; if (!nurPruefen) writeFileSync(vpZiel, vpNeu); }
if (!nurPruefen) mkdirSync(ziel, { recursive: true });
let geaendert = vzGeaendert;
for (const [name, inhalt] of Object.entries(dateien)) {
  const pfad = join(ziel, name);
  const neu = inhalt + '\n';
  if (existsSync(pfad) && readFileSync(pfad, 'utf8') === neu) continue;
  geaendert++;
  if (!nurPruefen) writeFileSync(pfad, neu);
}
console.log(`WP-Theme: ${Object.keys(dateien).length} Vorlagen + Vereinsverzeichnis, ${geaendert} ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}.`);
if (nurPruefen && geaendert) process.exit(2);
