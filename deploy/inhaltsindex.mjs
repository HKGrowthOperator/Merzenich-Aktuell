#!/usr/bin/env node
/**
 * Zentraler Inhaltsindex: ein Artikel -> ein Index -> alle Ansichten.
 *
 * Liest alle Artikelseiten (lib-artikel.mjs) und schreibt daraus:
 *   - api/inhalte.json           kanonischer Index
 *   - api/latest.json            items (events bleiben unangetastet)
 *   - /<ressort>/ + /seite/N/    Ressortlisten mit Blaetterung
 *   - /nachrichten/ + /seite/N/  Gesamtarchiv
 *   - /<ortsteil>/               Ortsseiten-Feed
 *   - index.html                 Aufmacher + Nebenmeldungen (Aufmacher und
 *                                zweite Meldung aus api/editorial-current.json)
 *   - feed.xml, atom.xml, feed.json, Ressort-/Ortsfeeds, news-sitemap.xml,
 *     sitemap-artikel.xml
 *   - Sidebox "Neueste Meldungen" auf allen Seiten
 *   - Ortswahl unter dem Kopf auf allen Seiten
 * Der Index traegt zusaetzlich den Bestand: je Ressort und je Ortsteil die
 * echte Anzahl der Meldungen und den Zustand der zugehoerigen Listenseite.
 * Damit muss niemand nachzaehlen - weder ein spaeterer Generator noch die
 * Pruefung. Eine Liste ohne Meldung bekommt keine leere Flaeche, sondern einen
 * benannten Leerzustand, und am Ende des Laufs steht der Bestand auf der
 * Konsole, mit einer Warnzeile je Bereich ohne Meldung.
 * Vorher wurden diese Ansichten getrennt gepflegt; ein neuer Artikel fehlte
 * dann in Ressort, Archiv, Ort, Feed und auf der Startseite (Audit 17.09.).
 * Idempotent. Aufruf: node deploy/inhaltsindex.mjs [--check]
 */
import { readFileSync, writeFileSync, existsSync, readdirSync, rmSync, mkdirSync, statSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { artikelSammeln, entschaerfen, esc, dmyLang, ORTSTEILE, SITE_URL } from './lib-artikel.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const nurPruefen = process.argv.includes('--check');
const site = join(wurzel, 'chatgpt-site');
const SEITENGROESSE = 12;
// Alle Ressorts, die die Seite anbietet - auch die ohne eigene Meldung. Nur so
// faellt auf, wenn ein Bereich etwas verspricht, das es redaktionell nicht gibt.
// Reihenfolge = Reihenfolge der Navigation.
const RESSORT_ORDNUNG = ['nachrichten', 'blaulicht', 'sport', 'rathaus', 'leben', 'wirtschaft', 'tipp', 'menschen', 'vereine', 'kultur'];
// /nachrichten/ ist das Gesamtarchiv und sammelt alle Meldungen; unter dem
// Ordner selbst liegt kein Beitrag. Ein Zaehlerstand 0 ist dort also richtig
// und keine Luecke - die Liste fuehrt trotzdem alle Meldungen.
const ARCHIV_RESSORT = 'nachrichten';
// 'region' vergibt lib-artikel.mjs, wenn die Ortszeile einen Ort nennt, der
// kein Ortsteil der Gemeinde ist. Redaktionell gibt es diesen Bereich noch
// nicht; er wird mitgezaehlt, damit das sichtbar bleibt.
const ORT_ORDNUNG = [...Object.keys(ORTSTEILE), 'region'];
const geaendert = []; const geloescht = [];
function schreibe(rel, inhalt) {
  const pfad = join(site, rel);
  const alt = existsSync(pfad) ? readFileSync(pfad, 'utf8') : null;
  if (alt === inhalt) return false;
  geaendert.push(rel);
  if (!nurPruefen) { mkdirSync(dirname(pfad), { recursive: true }); writeFileSync(pfad, inhalt); }
  return true;
}
const x = (s) => String(s ?? '').replace(/[<>&"]/g, (c) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;' }[c]));
const abs = (u) => (u && /^https?:/.test(u) ? u : SITE_URL + u);
// Klartext aus einer ausgelieferten Seite lesen (Seitenkopf, nicht der Feed).
const seitenText = (html, re) => entschaerfen(String((re.exec(html) || ['', ''])[1]).replace(/<[^>]+>/g, ' ')).replace(/\s+/g, ' ').trim();
// Zeitzeile nur, wenn es ein Veroeffentlichungsdatum gibt. Ein leeres
// <time datetime=""></time> ist ungueltiges Markup (das Attribut muss, wenn
// es dasteht, ein gueltiges Datum tragen) und behauptet eine Angabe, die die
// Meldung nicht hat. Die bewusst undatierten Hintergrundstuecke - im
// Artikelkopf als "Quelle ohne Veroeffentlichungsdatum" gekennzeichnet -
// erscheinen in den Listen deshalb ganz ohne Zeitzeile.
const zeitHtml = (a, fmt) => (a.datum ? `<time datetime="${esc(a.datum)}">${fmt(a.datum)}</time>` : '');
const kurzZeit = (iso) => { const d = new Date(iso); return new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit' }).format(d) + ' · ' + new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', hour: '2-digit', minute: '2-digit' }).format(d) + ' Uhr'; };

// ------------------------------------------------------------------ Index
const artikel = artikelSammeln(site);
// Bezahlte Tipp-Inhalte sind ein eigener, klar gekennzeichneter Kanal und
// werden nicht als redaktionelle Nachrichten in Homepage/Newsfeed vermischt.
const redaktionell = artikel.filter((a) => a.ressort !== 'tipp');
const neuester = artikel.reduce((m, a) => (a.aktualisiert || a.datum) > m ? (a.aktualisiert || a.datum) : m, '');
// Bestand je Ressort und je Ortsteil: einmal zaehlen, in den Index schreiben.
// Unbekannte Schluessel werden mitgezaehlt statt verschluckt, damit die Summe
// der Zaehler immer der Zahl der Artikel entspricht.
function zaehlen(feld, ordnung) {
  const z = Object.fromEntries(ordnung.map((k) => [k, 0]));
  for (const a of artikel) { const k = a[feld] || ''; z[k] = (z[k] || 0) + 1; }
  return z;
}
const bestandRessort = zaehlen('ressort', RESSORT_ORDNUNG);
const bestandOrt = zaehlen('ortsteil', ORT_ORDNUNG);
// Zustand jeder Listenseite; fuellt listeSchreiben() waehrend des Laufs.
const bestandListen = {};
const index = { generated: neuester, anzahl: artikel.length, bestand: null, hinweis: 'Kanonischer Inhaltsindex, erzeugt von deploy/inhaltsindex.mjs aus den Artikelseiten. Alle Listen, Feeds und die Startseite werden daraus gebaut.', artikel: artikel.map((a) => ({ id: a.id, url: a.url, titel: a.titel, teaser: a.teaser, kicker: a.kicker, ressort: a.ressort, ressortLabel: a.ressortLabel, ort: a.ort, ortsteil: a.ortsteil, datum: a.datum, aktualisiert: a.aktualisiert || a.datum, lesezeit: a.lesezeit, themen: a.themen, bild: a.bild, undatiert: !!a.undatiert, abgerufen: a.abgerufen || '' })) };
// Geschrieben wird der Index erst nach den Listen: bestand.listen haelt fest,
// welche Listenseite der Generator wirklich gepflegt hat.

// ------------------------------------------------------------ Bausteine
const locHtml = (a) => `<div class="location-line"><span class="location-brand">${esc(a.ort)}</span>${a.ortsteilLabel ? ' · ' + esc(a.ortsteilLabel) : ''}</div>`;
const imgHtml = (b, sizes, eager) => `<img src="${esc(b.src)}"${b.srcset ? ` srcset="${esc(b.srcset)}"` : ''} sizes="${sizes}" alt="${esc(b.alt)}"${b.width && b.height ? ` width="${b.width}" height="${b.height}"` : ''} loading="${eager ? 'eager' : 'lazy'}"${eager ? ' fetchpriority="high"' : ''} decoding="async" data-editorial-image class="">`;
const badgeHtml = (b) => (b.badge ? `<span class="badge">${esc(b.badge)}</span>` : '');
const disclosureHtml = (a) => a.ressort === 'tipp' ? '<span class="fbadge anzeige">Bezahlte Platzierung</span>' : '';
const mehr = (a) => `<div class="story-actions"><a class="read-more" href="${esc(a.url)}">Mehr lesen<span class="sr-only">: ${esc(a.titel)}</span></a></div>`;
function leadHtml(a) {
  const b = a.bild;
  return `<article class="feed-lead" data-story="${esc(a.id)}">${b ? `<a href="${esc(a.url)}" tabindex="-1" aria-hidden="true"><div class="media${b.fit ? ' contain' : ''}">${imgHtml(b, '(max-width: 640px) 100vw, 800px', true)}${badgeHtml(b)}</div></a>` : ''}<div class="lead-copy">${locHtml(a)}<span class="kicker">${esc(a.kicker)}</span>${disclosureHtml(a)}<h2><a href="${esc(a.url)}">${esc(a.titel)}</a></h2><p class="dek">${esc(a.teaser)}</p><div class="meta">${zeitHtml(a, dmyLang)}${a.lesezeit ? `<span class="readtime">${esc(a.lesezeit.replace(' Lesezeit', ''))}</span>` : ''}</div></div></article>`;
}
function rowHtml(a) {
  const b = a.bild;
  return `<article data-story="${esc(a.id)}" class="feed-row${b ? '' : ' no-media no-image'}">${b ? `<a class="feed-img" href="${esc(a.url)}" tabindex="-1" aria-hidden="true"><div class="media${b.fit ? ' contain' : ''}">${imgHtml(b, '(max-width: 640px) 120px, 240px', false)}${badgeHtml(b)}</div></a>` : ''}<div class="feed-copy">${locHtml(a)}<span class="kicker">${esc(a.kicker)}</span>${disclosureHtml(a)}<h3><a href="${esc(a.url)}">${esc(a.titel)}</a></h3><p class="dek">${esc(a.teaser)}</p><div class="meta">${zeitHtml(a, dmyLang)}${a.lesezeit ? `<span class="readtime">${esc(a.lesezeit.replace(' Lesezeit', ''))}</span>` : ''}</div>${mehr(a)}${b && b.credit ? `<div class="creditline"><span>${esc(b.badge || 'Bild')} · ${esc(b.credit)}</span></div>` : ''}</div></article>`;
}
/**
 * Leerzustand einer Liste: Ueberschrift, ein Satz, was hier erscheinen wird,
 * und der Weg zur Redaktion. Eine Liste ohne Meldung zeigte bisher nur eine
 * einzelne graue Zeile - das liest sich wie ein Fehler, nicht wie eine Aussage.
 *
 * Der beschreibende Satz stammt aus der Seite selbst (<p class="desc"> im
 * Seitenkopf). Damit steht im Leerzustand nur, was die Redaktion dort ohnehin
 * ankuendigt; erfunden wird nichts. Fehlt die Beschreibung, bleibt der Satz weg.
 *
 * Ausgezeichnet wird mit der vorhandenen .facts-Box, damit keine neue
 * CSS-Schicht entsteht; data-leerzustand ist die Marke, an der ersetzeFeed()
 * den Block beim naechsten Lauf wiederfindet (Idempotenz).
 */
function leerHtml(html, basis) {
  if (basis === '/tipp/') {
    return '<div data-leerzustand="/tipp/" class="facts"><h2>Aktuell keine gebuchte Tipp-Platzierung</h2><p>Sobald ein freigegebener Tipp vorliegt, erscheint er hier. Bezahlte Inhalte werden klar als Tipp, Anzeige oder Sponsoring gekennzeichnet.</p><p><a href="/werben/">Tipp oder Sponsoring anfragen</a>.</p></div>';
  }
  const desc = seitenText(html, /<p class="desc">([\s\S]*?)<\/p>/);
  const satz = (desc ? esc(desc) + ' ' : '') + 'Sobald die erste Meldung vorliegt, steht sie an dieser Stelle.';
  return `<div data-leerzustand="${esc(basis)}" class="facts"><h2>Noch keine Meldung</h2><p>${satz}</p><p>Sie haben einen Hinweis für die Redaktion? <a href="/meldung-senden/">Meldung senden</a>.</p></div>`;
}
function paginationHtml(basis, n, k) {
  if (k < 2) return '';
  const prev = n === 2 ? basis : `${basis}seite/${n - 1}/`;
  return `<nav class="pagination" aria-label="Seiten">${n > 1 ? `<a href="${prev}" rel="prev">← Neuer</a>` : ''}<span>Seite ${n} von ${k}</span>${n < k ? `<a href="${basis}seite/${n + 1}/" rel="next">Älter →</a>` : ''}</nav>`;
}
// Feed-Container einer Listenseite ersetzen; Einleitungsboxen vor dem ersten Artikel bleiben.
function ersetzeFeed(html, neuInnen) {
  const start = html.indexOf('<div class="feed">'); if (start < 0) return null;
  let ende = html.indexOf('<aside class="sidebar">', start); if (ende < 0) ende = html.indexOf('</section>', start); if (ende < 0) return null;
  const schluss = html.lastIndexOf('</div>', ende); if (schluss < start) return null;
  const innen = html.slice(start + '<div class="feed">'.length, schluss);
  // '<p class="empty"' ist der alte, einzeilige Leerzustand: die Marke bleibt,
  // damit er beim ersten Lauf ersetzt und nicht als Vorspann bewahrt wird.
  const marken = ['<article class="feed-lead"', '<article data-story=', '<div data-leerzustand', '<p class="empty"', '<nav class="pagination"'].map((m) => innen.indexOf(m)).filter((i) => i >= 0);
  const praefix = (marken.length ? innen.slice(0, Math.min(...marken)) : innen).replace(/\s+$/, '');
  return html.slice(0, start) + '<div class="feed">' + praefix + neuInnen + '\n    ' + html.slice(schluss);
}
const zaehler = (html, n, basis='') => basis === '/tipp/'
  ? html.replace(/<p class="count-line">[^<]*/, `<p class="count-line">${n} Platzierung${n === 1 ? '' : 'en'}`)
  : html.replace(/<p class="count-line">\d+ Meldung(?:en)?/, `<p class="count-line">${n} Meldung${n === 1 ? '' : 'en'}`);

function listeSchreiben(basis, items, { seiten = true } = {}) {
  const rel1 = basis.replace(/^\//, '') + 'index.html';
  // Buchfuehrung fuer den Bestand: 'keine Seite' und 'kein Feed' sind keine
  // Fehler, sondern Bereiche, die dieser Generator nicht pflegen kann. Sie
  // muessen trotzdem im Index stehen, sonst sieht niemand die Luecke.
  const buchen = (zustand) => { bestandListen[basis] = { anzahl: items.length, liste: zustand }; };
  const pfad1 = join(site, rel1); if (!existsSync(pfad1)) { buchen('keine Seite'); return; }
  const html1 = readFileSync(pfad1, 'utf8'); if (!html1.includes('<div class="feed">')) { buchen('kein Feed'); return; }
  buchen('gepflegt');
  const k = seiten ? Math.max(1, Math.ceil(items.length / SEITENGROESSE)) : 1;
  const seite1 = items.length ? (seiten ? items.slice(0, SEITENGROESSE) : items) : [];
  const innen1 = seite1.length ? '\n      ' + leadHtml(seite1[0]) + seite1.slice(1).map((a) => '\n      ' + rowHtml(a)).join('') + (k > 1 ? '\n      ' + paginationHtml(basis, 1, k) : '') : '\n      ' + leerHtml(html1, basis);
  const neu1 = ersetzeFeed(zaehler(html1, items.length, basis), innen1); if (neu1) schreibe(rel1, neu1);
  if (!seiten) return;
  const vorlagePfad = join(site, basis.replace(/^\//, ''), 'seite', '2', 'index.html');
  const vorlage = existsSync(vorlagePfad) ? readFileSync(vorlagePfad, 'utf8') : html1.replace(/<title>([^<|]*?)\s*\|/, '<title>$1 – Seite 2 |').replace(/(<link rel="canonical" href="[^"]*?)("\s*>)/, '$1seite/2/$2');
  for (let n = 2; n <= k; n++) {
    const teil = items.slice((n - 1) * SEITENGROESSE, n * SEITENGROESSE);
    const innen = teil.map((a) => '\n      ' + rowHtml(a)).join('') + '\n      ' + paginationHtml(basis, n, k);
    let html = vorlage.replace(/seite\/2\//g, `seite/${n}/`).replace(/Seite 2\b/g, `Seite ${n}`);
    html = ersetzeFeed(zaehler(html, items.length, basis), innen); if (html) schreibe(`${basis.replace(/^\//, '')}seite/${n}/index.html`, html);
  }
  const seitenDir = join(site, basis.replace(/^\//, ''), 'seite');
  if (existsSync(seitenDir)) for (const d of readdirSync(seitenDir)) {
    if (!/^\d+$/.test(d)) continue;
    if (Number(d) > k || k < 2) { geloescht.push(`${basis.replace(/^\//, '')}seite/${d}/`); if (!nurPruefen) rmSync(join(seitenDir, d), { recursive: true, force: true }); }
  }
  if (k < 2 && existsSync(seitenDir) && !nurPruefen && readdirSync(seitenDir).length === 0) rmSync(seitenDir, { recursive: true, force: true });
}

// ----------------------------------------------------- Listen und Archiv
const ressorts = [...new Set(artikel.map((a) => a.ressort))];
// Jedes Ressort laeuft durch listeSchreiben(), auch eines ohne Seite: die
// Funktion bricht selbst ab und vermerkt den Zustand im Bestand.
for (const r of RESSORT_ORDNUNG) {
  listeSchreiben(`/${r}/`, r === ARCHIV_RESSORT ? redaktionell : artikel.filter((a) => a.ressort === r));
}
for (const ort of Object.keys(ORTSTEILE)) listeSchreiben(`/${ort}/`, artikel.filter((a) => a.ortsteil === ort), { seiten: false });

// --------------------------------------------------------------- Bestand
// Jetzt stehen alle Zaehler fest. Sie gehen in den Index, damit spaetere
// Generatoren und die Pruefung den Bestand lesen koennen, ohne erneut alle
// Artikelseiten zu oeffnen. 'leer' ist die daraus abgeleitete Liste der
// Bereiche ohne Meldung - das Gesamtarchiv zaehlt dort nicht mit, es hat
// keine eigenen Beitraege und ist trotzdem gefuellt (siehe ARCHIV_RESSORT).
const leerRessorts = RESSORT_ORDNUNG.filter((r) => r !== ARCHIV_RESSORT && !bestandRessort[r]);
const leerOrte = ORT_ORDNUNG.filter((o) => !bestandOrt[o]);
const leerListen = Object.keys(bestandListen).filter((b) => !bestandListen[b].anzahl);
index.bestand = {
  gesamt: artikel.length,
  ressorts: bestandRessort,
  ortsteile: bestandOrt,
  listen: bestandListen,
  leer: { ressorts: leerRessorts, ortsteile: leerOrte, listen: leerListen },
};

// ----------------------------------------------------------- Startseite
// Die obere Flaeche wird als Ganzes zwischen zwei Markern geschrieben.
// Vorher ersetzte eine Regex den Aufmacher und ein Index-Scan die rechte
// Leiste; beides brach still, sobald sich das Markup daneben aenderte. Die
// Startseite fror dann auf dem letzten Treffer ein (Audit 18.09.).
{
  const rel = 'index.html'; const alt = readFileSync(join(site, rel), 'utf8'); let html = alt;
  const ed = JSON.parse(readFileSync(join(site, 'api', 'editorial-current.json'), 'utf8'));
  const nachUrl = new Map(artikel.map((a) => [a.url, a]));

  // Groesste ausgelieferte Bildvariante. Der Schwellwert je Platz ergibt sich
  // aus der Breite der Flaeche, nicht aus Geschmack: ein 300px-Motiv auf 780px
  // hochgezogen sieht aus wie ein Fehler. Externe Motive ueber /api/bild
  // tragen ihre Breite im Abfrageteil der URL statt als Attribut.
  const BREITE_XL = 768;  // Aufmacherflaeche rund 780 CSS-Pixel
  const BREITE_M = 360;   // Nebenmeldung rund 360 CSS-Pixel
  const bildBreite = (b) => {
    if (!b) return 0;
    const ausSrcset = (String(b.srcset || '').match(/(\d+)w/g) || []).map((s) => parseInt(s, 10));
    const ausUrl = /width(?:%3D|=)(\d+)/i.exec(String(b.src || ''));
    return Math.max(b.width || 0, 0, ausUrl ? parseInt(ausUrl[1], 10) : 0, ...ausSrcset);
  };
  // Ein Vereinslogo oder Wappen fuellt nie eine Bildflaeche (Designstandard 7b).
  // Solche Artikel bekommen keinen Bildplatz, bis ein Symbolbild vorliegt.
  // Die Symbolbild-Pools unter assets/symbolbilder tragen derzeit Farbverlaeufe
  // mit aufgedrucktem Wort: 20 Dateien je Pool teilen sich drei Strukturen, der
  // Sport-Pool laeuft auf Gold, Gemeinde und Wirtschaft auf Beige (gemessen
  // 19.09.). Als Aufmacher oder Nebenmeldung taugt das nicht, sonst steht ein
  // goldenes Rechteck an der wichtigsten Stelle der Seite. Die Zeile entfaellt,
  // sobald der Pool unterscheidbare Motive enthaelt (docs/SYMBOLBILDER-ANFORDERUNG.md).
  const VERLAUFSPOOL = /\/assets\/symbolbilder\//;
  // Was die Redaktion selbst setzt, prueft der Generator nicht nach Geschmack:
  // ein Symbolbild aus dem passenden Pool ist eine bewusste Entscheidung. Nur
  // ein Vereinslogo oder Wappen bleibt gesperrt, das fuellt keine Bildflaeche.
  // Die Automatik weiter unten waehlt dagegen nur echte Fotos - sonst stuende
  // ein Farbverlauf an der wichtigsten Stelle, ohne dass es jemand wollte.
  const redaktionellBebildert = (a) => {
    const b = a && a.bild;
    if (!b || !b.src) return false;
    if (b.fit === 'contain') return false;
    return !/logo|wappen/i.test(`${b.badge || ''} ${b.alt || ''} ${b.src}`);
  };

  // Fotos aus den Commons-Pools (assets/editorial-pools) sind Symbolbilder.
  // Aufmacher nach KBS-Entscheid 24.09.: eigenes Foto > Ortsansicht > lokales
  // Poolfoto. Lokal heisst: das Manifest verortet das Foto in der Gemeinde
  // Merzenich oder im Kreis Dueren, und es hat die Sichtpruefung bestanden.
  // Nebenmeldungen und Ressortflaechen duerfen jedes Poolfoto tragen.
  const POOLFOTO = /\/assets\/editorial-pools\//;
  const poolManifest = join(site, 'data', 'editorial-images', 'editorial-photo-pools.json');
  const POOL_EINTRAG = new Map(existsSync(poolManifest) ? (JSON.parse(readFileSync(poolManifest, 'utf8')).images || []).map((x) => [x.src, x]) : []);
  const lokalesPoolfoto = (a) => {
    const m = a && a.bild && POOL_EINTRAG.get(a.bild.src);
    return !!m && m.geprueft === true && ['Merzenich', 'Kreis Düren'].includes(m.locality);
  };

  const echtesBild = (a) => {
    const b = a && a.bild;
    if (!b || !b.src) return false;
    if (b.fit === 'contain') return false;
    if (VERLAUFSPOOL.test(b.src)) return false;
    return !/logo|wappen/i.test(`${b.badge || ''} ${b.alt || ''} ${b.src}`);
  };
  const eigenesFoto = (a) => echtesBild(a) && !POOLFOTO.test(a.bild.src);
  const aufmacherBild = (a) => eigenesFoto(a) || (echtesBild(a) && lokalesPoolfoto(a));

  // Jede Bildflaeche schneidet auf Querformat zu: die grosse Karte auf 16:9,
  // die mittlere auf 3:2. Zwei Motivsorten ueberleben das nicht.
  //
  // Erstens Plakate und Veranstaltungsgrafiken. Sie tragen Schrift, und der
  // Zuschnitt schneidet Saetze mitten durch. Gemessen am 22.09. stand das
  // Ortsfest-Plakat ("Samstag, 3. Oktober 2026") in der Sektion Gemeinde mit
  // abgeschnittener Zeile; dasselbe gilt fuer den 1440x372 breiten Banner zum
  // Seniorennachmittag, bei dem 16:9 die Raender wegnimmt. Solche Motive
  // bekommen keinen zuschneidenden Platz, sondern die kompakte Zeile.
  //
  // Zweitens hochkant aufgenommene Motive in der grossen Karte: bei 16:9
  // bliebe von einem 768x1098-Motiv gut ein Drittel der Hoehe uebrig. In der
  // mittleren Karte bei 3:2 ist der Beschnitt normaler redaktioneller
  // Zuschnitt, dort bleibt Hochformat erlaubt.
  const PLAKAT_RE = /veranstaltungsbild|plakat|flyer|banner/i;
  const seitenverhaeltnis = (b) => (b && b.width && b.height ? b.width / b.height : 0);
  const passtInPlatz = (a, groesse) => {
    const b = a && a.bild;
    if (!b) return false;
    if (PLAKAT_RE.test(String(b.badge || ''))) return false;
    if (groesse !== 'l') return true;
    const v = seitenverhaeltnis(b);
    return v === 0 || v >= 1.2;
  };

  // Aufmacher: redaktionell gesetzt schlaegt automatisch - aber nur mit echtem
  // Bild. Frueher wurde ein Logo-Aufmacher still zum Textblock; genau das hat
  // die Mitte der Startseite leer aussehen lassen.
  const ORTSANSICHT = {
    merzenich: { alt: 'Historisches Fachwerkhaus im Ortskern', credit: 'Karl-Heinz Meurer / Wikimedia Commons' },
    golzheim: { alt: 'Blick über den Wenauer Hof auf St. Gregorius', credit: 'Karl-Heinz Meurer / Wikimedia Commons' },
    girbelsrath: { alt: 'Denkmalgeschütztes Fachwerkhaus', credit: 'Käthe und Bernd Limburg / Wikimedia Commons' },
    morschenich: { alt: 'Archivaufnahme vom Aufbau des neuen Ortes', credit: 'Papa1234 / Wikimedia Commons' },
    buergewald: { alt: 'Luftbild des früheren Morschenich-Alt', credit: 'Antisyntagmatarchos / Wikimedia Commons' },
  };
  function aufmacherWaehlen() {
    const gesetzt = ed.hero && ed.hero.url ? nachUrl.get(ed.hero.url) : null;
    if (ed.hero && ed.hero.url) {
      if (!gesetzt) {
        console.error(`Startseite: Aufmacher ${ed.hero.url} steht nicht im Inhaltsindex.`);
        process.exitCode = 2;
      } else if (gesetzt.ressort === 'sport' || gesetzt.ressort === 'tipp') {
        console.error(`Startseite: Aufmacher ${ed.hero.url} gehoert zu ${gesetzt.ressort} und darf nicht auf die Startseite.`);
        process.exitCode = 2;
      } else if (!redaktionellBebildert(gesetzt)) {
        console.error(`Startseite: Aufmacher ${ed.hero.url} traegt ein Logo oder Wappen als Bild. editorial-current.json korrigieren oder Eintrag entfernen.`);
        process.exitCode = 2;
      } else {
        return gesetzt;
      }
    }
    // Stil B (Freigabe 23.09.2026): Der Aufmacher ist hoechstens sieben Tage
    // aelter als die juengste Meldung. Gemessen am 23.09. stand dort eine
    // Bilanz vom 17.08., weil nur sie ein grosses echtes Foto hatte; die
    // Meldungen des Tages lagen darunter. Gibt es in dieser Woche kein echtes
    // Foto, traegt die juengste Meldung die Ortsansicht ihres Ortes - ein
    // echtes Foto mit Nachweis, als "Ortsansicht" beschriftet, nie ein
    // Symbolbild. Bezugspunkt ist die juengste Meldung, nicht die Uhr: so
    // bleibt der Lauf wiederholbar (--check).
    const grenze = new Date(new Date(neuester).getTime() - 7 * 864e5).toISOString();
    const frisch = redaktionell
      .filter((a) => a.ressort !== 'sport' && a.datum && !a.undatiert && new Date(a.datum).toISOString() >= grenze)
      .sort((x, y) => String(y.datum).localeCompare(String(x.datum)));
    const frischMitFoto = frisch.find((a) => eigenesFoto(a) && passtInPlatz(a, 'l') && bildBreite(a.bild) >= BREITE_XL);
    if (frischMitFoto) return frischMitFoto;
    if (frisch.length) {
      const a = frisch[0]; const o = ORTSANSICHT[a.ortsteil] || ORTSANSICHT.merzenich;
      const slug = ORTSANSICHT[a.ortsteil] ? a.ortsteil : 'merzenich';
      console.log(`Startseite: Aufmacher ${a.url} ohne eigenes grosses Foto, traegt die Ortsansicht ${ORTSTEILE[slug]}.`);
      return { ...a, bild: { src: `/assets/places/${slug}-1440.webp`, srcset: `/assets/places/${slug}-720.webp 720w, /assets/places/${slug}-1440.webp 1440w`, width: 1440, height: 960, alt: `Ortsansicht ${ORTSTEILE[slug]}: ${o.alt}`, badge: `Ortsansicht · Foto: ${o.credit}` } };
    }
    return redaktionell.find((a) => a.ressort !== 'sport' && eigenesFoto(a) && passtInPlatz(a, 'l') && bildBreite(a.bild) >= BREITE_XL)
      || redaktionell.find((a) => a.ressort !== 'sport' && aufmacherBild(a) && passtInPlatz(a, 'l') && bildBreite(a.bild) >= BREITE_XL)
      || redaktionell.find((a) => a.ressort !== 'sport' && aufmacherBild(a) && passtInPlatz(a, 'l'))
      || redaktionell.find((a) => a.ressort !== 'sport' && aufmacherBild(a))
      || null;
  }

  // Stil B (23.09.2026): Ein Hinweisschild steht nur, wo das Bild nicht das
  // eigene Foto der Meldung ist. "Originalbild" auf jedem Foto war Rauschen
  // (Critique 23.09.), und interne Begriffe wie "Quellenmotiv" versteht kein
  // Leser. Die Artikelseiten behalten ihre Angaben unveraendert.
  const HINWEIS_START = { Originalbild: '', Quellenmotiv: 'Bild: Quelle', 'Offizielles Veranstaltungsbild': 'Bild: Veranstalter' };
  const hinweisStart = (b) => {
    const t = Object.prototype.hasOwnProperty.call(HINWEIS_START, b.badge || '') ? HINWEIS_START[b.badge] : (b.badge || '');
    return t ? `<span class="badge">${esc(t)}</span>` : '';
  };
  const bildFlaeche = (a, sizes, eager) => `<a class="karte-bild" href="${esc(a.url)}" tabindex="-1" aria-hidden="true"><div class="media">${imgHtml(a.bild, sizes, eager)}${hinweisStart(a.bild)}</div></a>`;
  // Ortsmarke nach Anhang A3: MERZENICH als wiederkehrender Bordeaux-Marker,
  // der Ortsteil zurueckhaltend dahinter, die Rubrik in normaler Schreibung.
  // Eine Zeile statt zweier Versalienzeilen (Ort, Kicker). Die Zeit steht in .meta.
  const markeHtml = (a) => `<p class="marke"><span class="marke-ort">Merzenich</span>`
    + (a.ortsteil && a.ortsteil !== 'merzenich' && ORTSTEILE[a.ortsteil] ? `<span class="marke-teil"> · ${esc(ORTSTEILE[a.ortsteil])}</span>` : '')
    + `<span class="marke-rubrik">${esc(a.ressortLabel || a.kicker)}</span></p>`;

  // Eine einzige Komponente: Bild, Kategorie, Headline, Zeit. In drei Groessen.
  function karte(a, groesse, tag) {
    if (groesse === 'xl') {
      return `<article class="front-lead" data-story="${esc(a.id)}">`
        + bildFlaeche(a, '(max-width: 760px) 100vw, 780px', true)
        + `<div class="front-lead-copy">${markeHtml(a)}`
        + `<h1><a href="${esc(a.url)}">${esc(a.titel)}</a></h1>`
        + `<p>${esc(a.teaser)}</p>`
        + `<div class="meta">${zeitHtml(a, kurzZeit)}${a.lesezeit ? `<span>${esc(a.lesezeit)}</span>` : ''}</div>`
        + `</div></article>`;
    }
    // In einer Sektion steht ein h2 als Sektionstitel, die Karten darunter
    // muessen deshalb h3 tragen. Im Aufmacherblock gibt es keinen Sektionskopf.
    const h = tag || 'h2';
    const kopf = (a2) => `<${h}><a href="${esc(a2.url)}">${esc(a2.titel)}</a></${h}>`;
    if (groesse === 'l') {
      return `<article class="desk-karte desk-karte--gross" data-story="${esc(a.id)}">`
        + bildFlaeche(a, '(max-width: 900px) 100vw, 600px', false)
        + `<div class="karte-text">${markeHtml(a)}`
        + kopf(a)
        + `<p class="dek">${esc(a.teaser)}</p>`
        + `<div class="meta">${zeitHtml(a, kurzZeit)}</div>`
        + `</div></article>`;
    }
    if (groesse === 'm') {
      return `<article class="${tag ? 'desk-karte desk-karte--mittel' : 'front-neben-story'}" data-story="${esc(a.id)}">`
        + bildFlaeche(a, '(max-width: 1100px) 46vw, 390px', false)
        + `<div class="karte-text">${markeHtml(a)}`
        + kopf(a)
        + `<div class="meta">${zeitHtml(a, kurzZeit)}</div>`
        + `</div></article>`;
    }
    return `<article class="front-zeile front-zeile--bild" data-story="${esc(a.id)}">`
      + bildFlaeche(a, '(max-width: 640px) 120px, 220px', false)
      + `<div class="karte-text">${markeHtml(a)}`
      + kopf(a)
      + `<div class="meta">${zeitHtml(a, kurzZeit)}</div>`
      + `</div></article>`;
  }

  // Eine Meldung steht hoechstens einmal auf der Seite. Solange die Sektionen
  // handgepflegt waren, musste der Aufmacher ausweichen; jetzt greift die obere
  // Flaeche zuerst und die Sektionen nehmen, was uebrig ist. Das haelt die
  // Reihenfolge von oben nach unten aktuell.
  const aufmacher = aufmacherWaehlen();
  if (!aufmacher) { console.error('Startseite: kein Artikel mit echtem Bild gefunden, Aufmacher nicht gebaut.'); process.exitCode = 2; }
  const vergeben = new Set(aufmacher ? [aufmacher.url] : []);
  // KBS/Ordin 23.09.2026: Der erste Blick soll deutlich dichter werden.
  // Eine grosse Highlight-News wird von fuenf kleineren Bildmeldungen rechts
  // und darunter ergaenzt. Keine Sportmeldung darf in dieser Startbuehne landen.
  // Redaktionell gesetzte Nebenmeldungen stehen vorne und in ihrer Reihenfolge.
  // Das Feld lag in editorial-current.json, wurde aber seit dem Umbau der
  // oberen Flaeche nicht mehr gelesen - die Fixierung war damit wirkungslos.
  // Es nimmt ein einzelnes Objekt oder eine Liste, jeweils mit url.
  const gesetzteNeben = []
    .concat(ed.secondary || [])
    .map((e) => (e && e.url ? nachUrl.get(e.url) : null))
    .filter((a, i, alle) => {
      if (!a) return false;
      if (vergeben.has(a.url) || alle.indexOf(a) !== i) return false;
      if (a.ressort === 'sport' || a.ressort === 'tipp') {
        console.error(`Startseite: Nebenmeldung ${a.url} gehoert zu ${a.ressort} und darf nicht auf die Startseite.`);
        process.exitCode = 2;
        return false;
      }
      if (!redaktionellBebildert(a)) {
        console.error(`Startseite: Nebenmeldung ${a.url} traegt ein Logo oder Wappen als Bild.`);
        process.exitCode = 2;
        return false;
      }
      return true;
    });
  for (const a of gesetzteNeben) vergeben.add(a.url);
  // Hoechstens zwei Blaulichtmeldungen unter den ersten sechs (Aufmacher plus
  // fuenf Nebenmeldungen). Am 23.09. waren es vier von sechs, die Seite las
  // sich wie ein Polizeiticker (Critique 23.09., Freigabe Stil B).
  let blaulicht = aufmacher && aufmacher.ressort === 'blaulicht' ? 1 : 0;
  for (const a of gesetzteNeben) if (a.ressort === 'blaulicht') blaulicht++;
  const neben = gesetzteNeben.concat(
    redaktionell.filter((a) => {
      if (vergeben.has(a.url) || a.ressort === 'sport' || !echtesBild(a) || !passtInPlatz(a, 'm') || bildBreite(a.bild) < BREITE_M) return false;
      if (a.ressort === 'blaulicht') { if (blaulicht >= 2) return false; blaulicht++; }
      return true;
    }),
  ).slice(0, 5);
  for (const a of neben) vergeben.add(a.url);
  if (gesetzteNeben.length) console.log(`Startseite: ${gesetzteNeben.length} Nebenmeldung(en) redaktionell gesetzt.`);

  // Buehne nach Anhang A4.4: rechts oben die zweite Meldung groesser, darunter
  // vier kompakte im 2x2. Keine kuenstliche Gleichheit der fuenf Highlights.
  const [zweit, ...vier] = neben;
  const inhaltOben = (aufmacher ? karte(aufmacher, 'xl') : '')
    + (zweit ? `<div class="front-neben">${karte(zweit, 'm').replace('class="front-neben-story"', 'class="front-neben-story front-zweit"')}`
      + (vier.length ? `<div class="front-vier">${vier.map((a) => karte(a, 'm')).join('')}</div>` : '') + '</div>' : '');
  const oben = '<!-- start:oben:start -->'
    + `<section class="shell frontpage-grid" data-editorial-verified="1" aria-label="Die wichtigsten Nachrichten">${inhaltOben}</section>`
    + '<!-- start:oben:end -->';

  const MARKER_OBEN = /<!-- start:oben:start -->[\s\S]*?<!-- start:oben:end -->/;
  if (!MARKER_OBEN.test(html)) { console.error('Startseite: Marker start:oben fehlt in index.html.'); process.exitCode = 2; }
  else html = html.replace(MARKER_OBEN, () => oben);


  // ---------------------------------------------------------- Orte-Buehne
  // Stil B (23.09.2026): Die fuenf Orte stehen direkt unter der oberen Flaeche
  // als Bildbuehne, nicht mehr als handgepflegte Kacheln fuenf Bildschirme
  // tiefer. Zahl und juengste Meldung kommen aus demselben Index wie die
  // Ortswahl - vorher zeigte die Kachel 29/2/6/3/2, die Ortswahl 35/3/7/4/2.
  // Die Ortsfotos sind echte Aufnahmen mit Nachweis (Wikimedia Commons).
  const ORTE_BUEHNE = [
    ['merzenich', 'Hauptort mit Rathaus, St. Laurentius und S-Bahn-Halt an der Strecke Köln–Aachen.', 'Historisches Fachwerkhaus im Ortskern von Merzenich', 'Karl-Heinz Meurer / Wikimedia Commons', '6% 50%'],
    ['golzheim', 'Im Norden der Gemeinde, mit St. Gregorius, Grundschule und Schützenbruderschaft.', 'Blick über den Wenauer Hof auf St. Gregorius in Golzheim', 'Karl-Heinz Meurer / Wikimedia Commons', '50% 50%'],
    ['girbelsrath', 'Im Süden, mit St. Amandus, eigener Löschgruppe und Karnevalsverein.', 'Denkmalgeschütztes Fachwerkhaus an der Hauptstraße in Girbelsrath', 'Käthe und Bernd Limburg / Wikimedia Commons', '50% 50%'],
    ['morschenich', 'Der Umsiedlungsort „Zwischen den Höfen“, bis Juli 2024 Morschenich-Neu.', 'Archivaufnahme vom Aufbau des neuen Morschenich im Februar 2015', 'Papa1234 / Wikimedia Commons', '50% 50%'],
    ['buergewald', 'Das alte Morschenich am Hambacher Forst, seit Juli 2024 Bürgewald.', 'Luftbild von Bürgewald, dem früheren Morschenich-Alt', 'Antisyntagmatarchos / Wikimedia Commons', '50% 50%'],
  ];
  const datumOrt = (iso) => new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: 'numeric', month: 'long' }).format(new Date(iso));
  const neuesteImOrt = (slug) => redaktionell
    .filter((a) => a.ortsteil === slug && a.ressort !== 'sport' && a.datum && !a.undatiert)
    .sort((x, y) => String(y.datum).localeCompare(String(x.datum)))[0];
  const orteHtml = '<section class="orte-buehne" aria-labelledby="orte-titel"><div class="shell orte-kopf"><h2 id="orte-titel">Ihre fünf Orte</h2></div><div class="orte-reihe">'
    + ORTE_BUEHNE.map(([slug, zeile, alt, credit, fokus]) => {
      const n = bestandOrt[slug] || 0; const j = neuesteImOrt(slug);
      return `<a class="ort" href="/${slug}/">`
        + `<span class="ort-bild"><img src="/assets/places/${slug}-720.webp" srcset="/assets/places/${slug}-720.webp 720w, /assets/places/${slug}-1440.webp 1440w" sizes="(max-width: 760px) 100vw, 20vw" width="1440" height="960" loading="lazy" decoding="async" alt="${esc(alt)}"${fokus !== '50% 50%' ? ` style="object-position:${fokus}"` : ''}><span class="ort-credit">Foto: ${esc(credit)}</span></span>`
        + `<span class="ort-name">${esc(ORTSTEILE[slug])}</span><span class="ort-zeile">${esc(zeile)}</span>`
        + (j ? `<span class="ort-neu"><em>Zuletzt am ${esc(datumOrt(j.datum))}</em>${esc(j.titel)}</span>` : '')
        + `<span class="ort-zahl">${n} Meldung${n === 1 ? '' : 'en'}</span></a>`;
    }).join('')
    + '</div><p class="shell orte-foto"><a href="/meldung-senden/#formular">Ihr Foto aus einem der fünf Orte an die Redaktion senden</a></p></section>';
  const MARKER_ORTE = /<!-- start:orte:start -->[\s\S]*?<!-- start:orte:end -->/;
  if (!MARKER_ORTE.test(html)) { console.error('Startseite: Marker start:orte fehlt in index.html.'); process.exitCode = 2; }
  else html = html.replace(MARKER_ORTE, () => `<!-- start:orte:start -->${orteHtml}<!-- start:orte:end -->`);

  // ----------------------------------------------------- Ressortflaechen
  // Vorher war die Flaeche unter dem Aufmacher handgepflegtes Markup: kein
  // Generator zog sie nach, die juengste Meldung dort war vom 04.09., sechs
  // Karten standen ohne Bild, und eine Sektion zeigte ein riesiges Bild links
  // neben einer duennen Textleiter. Jetzt erzeugt sie derselbe Index wie alles
  // andere, im immer gleichen Muster: zwei grosse Meldungen nebeneinander,
  // darunter drei mittlere (Designstandard 7b).
  const BREITE_L = 480; // grosse Karte rund 600 CSS-Pixel, zwei nebeneinander
  const SEKTIONEN = [
    { id: 'gemeinde', kat: 'Aus der Gemeinde', titel: 'Nachrichten aus Merzenich', mehr: '/nachrichten/', mehrText: 'Alle Meldungen', nimm: (a) => a.ressort !== 'sport' && a.ressort !== 'tipp', jeRessort: 3, fenster: 44, zuletzt: true },
    { id: 'blaulicht', kat: 'Feuerwehr · Polizei · Verkehr', titel: 'Blaulicht', mehr: '/blaulicht/', mehrText: 'Alle Einsatzmeldungen', nimm: (a) => a.ressort === 'blaulicht' },
    { id: 'rathaus', kat: 'Rathaus · Beschlüsse · Projekte', titel: 'Politik & Gemeinde', mehr: '/rathaus/', mehrText: 'Zum Rathaus', nimm: (a) => a.ressort === 'rathaus' },
    { id: 'wirtschaft', kat: 'Arbeit · Infrastruktur · Zukunft', titel: 'Wirtschaft', mehr: '/wirtschaft/', mehrText: 'Zur Wirtschaft', nimm: (a) => a.ressort === 'wirtschaft' },
    { id: 'vereine', kat: 'Gemeinschaft · Kultur · Engagement', titel: 'Vereine & Menschen', mehr: '/vereine/', mehrText: 'Zu den Vereinen', nimm: (a) => a.ressort === 'vereine' || a.ressort === 'menschen' },
  ];

  function sektionHtml(s, gross, mittel, zeilen) {
    // Ohne dreiteilige Ueberzeile ("Feuerwehr · Polizei · Verkehr"): das war
    // eine Schreibgewohnheit, keine Information (Critique 23.09.).
    const kopf = '<div class="desk-heading"><div>'
      + `<h2>${esc(s.titel)}</h2>`
      + `</div><a class="desk-more" href="${esc(s.mehr)}">${esc(s.mehrText)}</a></div>`;
    const reiheGross = gross.length ? `<div class="desk-gross">${gross.map((a) => karte(a, 'l', 'h3')).join('')}</div>` : '';
    const reiheMittel = mittel.length ? `<div class="desk-mittel">${mittel.map((a) => karte(a, 'm', 'h3')).join('')}</div>` : '';
    const reiheZeilen = zeilen.length ? `<div class="desk-zeilen">${zeilen.map((a) => karte(a, 's', 'h3')).join('')}</div>` : '';
    return `<section class="desk shell" data-sektion="${esc(s.id)}">${kopf}${reiheGross}${reiheMittel}${reiheZeilen}</section>`;
  }

  // Vergeben und rendern sind zwei Durchgaenge. Gerendert wird in
  // Seitenreihenfolge, vergeben aber zuerst an die ressortgebundenen
  // Sektionen: sie koennen nur aus ihrem eigenen Ressort schoepfen, waehrend
  // "Nachrichten aus Merzenich" aus allen greift. Lief die Leitsektion zuerst,
  // nahm sie am 22.09. die letzten drei Rathausmotive mit, und "Politik &
  // Gemeinde" blieb mit zwei Textzeilen stehen.
  const vergabeReihe = [...SEKTIONEN].sort((a, b) => (a.zuletzt ? 1 : 0) - (b.zuletzt ? 1 : 0));
  const belegung = new Map();
  for (const s of vergabeReihe) {
    // Erst die juengsten Meldungen der Sektion auswaehlen, dann innerhalb dieser
    // Auswahl die Plaetze nach Bildgroesse verteilen. Andersherum bestimmt das
    // Bild die Reihenfolge: gemessen am 22.09. stand eine Meldung vom 13.08.
    // oben und die vom 14.09. als Zeile darunter, weil nur die aeltere ein
    // grosses Motiv hatte. Die Auswahl bleibt dadurch auf sieben Meldungen
    // begrenzt, der Abstand zwischen erster und letzter Karte klein.
    // Zwoelf statt sieben, weil sonst eine Sektion leer bliebe, sobald die
    // juengsten Meldungen nur Verlaufs-Platzhalter tragen: gemessen am 22.09.
    // fiel "Nachrichten aus Merzenich" dadurch auf zwei Zeilen ohne Bild
    // zusammen. Die Zeilen unten nehmen trotzdem die juengsten Meldungen.
    // Das Fenster begrenzt, wie weit eine Sektion zurueckgreift. Zwanzig reicht
    // einem Ressort. "Nachrichten aus Merzenich" braucht mehr: unter den
    // juengsten zwanzig Meldungen stehen zehn Blaulicht-Meldungen, fuenf
    // Sport-Meldungen mit Verlaufs-Platzhalter und fuenf weitere ohne
    // brauchbares Motiv (gemessen 22.09.). Mit Ressortgrenze und kleinem
    // Fenster fiele die Leitsektion auf zwei Karten zusammen.
    const frei = artikel.filter((a) => !vergeben.has(a.url) && s.nimm(a)).slice(0, s.fenster || 20);

    // "Nachrichten aus Merzenich" zieht aus allen Ressorts.
    // Ohne Obergrenze nahm sie am 22.09. sechs von sieben Karten aus dem
    // Blaulicht - direkt ueber der Sektion Blaulicht. Die Leitsektion ist aber
    // die Mischung, nicht das staerkste Ressort. Drei ist die gemessene Grenze:
    // bei zwei reicht es nicht mehr fuer die grosse Reihe, bei vier kippt die
    // Sektion wieder ins Blaulicht. Die Grenze zaehlt erst beim
    // Belegen eines Platzes, nicht beim Vorsortieren: sonst verbraucht sie sich
    // an den juengsten Meldungen, und die tragen derzeit Verlaufs-Platzhalter.
    const jeRessort = new Map();
    const passtInsRessort = (a) => !s.jeRessort || (jeRessort.get(a.ressort) || 0) < s.jeRessort;
    const belege = (liste) => { for (const a of liste) { jeRessort.set(a.ressort, (jeRessort.get(a.ressort) || 0) + 1); vergeben.add(a.url); } };
    // Waehlt der Reihe nach so viele aus, wie die Reihe braucht, und haelt dabei
    // die Ressortgrenze ein - ohne sie zu belegen, denn die Reihe wird nur
    // gebaut, wenn sie voll wird.
    const waehle = (pool, anzahl, schon) => {
      const gezaehlt = new Map(jeRessort);
      for (const a of schon) gezaehlt.set(a.ressort, (gezaehlt.get(a.ressort) || 0) + 1);
      const raus = [];
      for (const a of pool) {
        if (raus.length >= anzahl) break;
        if (schon.includes(a)) continue;
        if (s.jeRessort && (gezaehlt.get(a.ressort) || 0) >= s.jeRessort) continue;
        gezaehlt.set(a.ressort, (gezaehlt.get(a.ressort) || 0) + 1);
        raus.push(a);
      }
      return raus;
    };

    // Nur vollstaendige Reihen. Gemessen am 22.09.: "Nachrichten aus Merzenich"
    // hatte eine grosse Reihe mit einer Karte, "Politik & Gemeinde" eine
    // mittlere Reihe mit einer von drei - in beiden Faellen stand der Rest der
    // Reihe leer. Tote Flaeche ist genau das, was die Startseite nicht mehr
    // haben soll. Also: entweder die Reihe ist voll, oder sie wird nicht
    // gebaut. Reicht es nicht fuer beide, baut die Sektion die Reihe, die
    // aufgeht - drei mittlere schlagen zwei grosse, weil drei Motive mehr
    // Meldungen zeigen als zwei.
    const lFaehig = frei.filter((a) => echtesBild(a) && passtInPlatz(a, 'l') && bildBreite(a.bild) >= BREITE_L);
    const mFaehig = frei.filter((a) => echtesBild(a) && passtInPlatz(a, 'm') && bildBreite(a.bild) >= BREITE_M);
    let gross = waehle(lFaehig, 2, []);
    let mittel = gross.length === 2 ? waehle(mFaehig, 3, gross) : [];
    if (gross.length !== 2 || mittel.length !== 3) {
      const nurMittel = waehle(mFaehig, 3, []);
      if (nurMittel.length === 3) { gross = []; mittel = nurMittel; }
      else if (gross.length === 2) { mittel = []; }
      else { gross = []; mittel = []; }
    }
    belege(gross);
    belege(mittel);
    // Kompakte Meldungen behalten ebenfalls ihr zugewiesenes Bild (inklusive
    // verifiziertem Symbolbild). Hoechstens zwei, sonst waechst die Sektion
    // zu einer Liste aus, die niemand zu Ende liest.
    const zeilen = frei.filter((a) => !vergeben.has(a.url)).slice(0, 2);
    for (const a of zeilen) vergeben.add(a.url);

    belegung.set(s.id, { gross, mittel, zeilen });
  }

  // Zweiter Durchgang: schreiben in Seitenreihenfolge.
  let sektionenGeschrieben = 0;
  for (const s of SEKTIONEN) {
    const MARKER = new RegExp(`<!-- start:${s.id}:start -->[\\s\\S]*?<!-- start:${s.id}:end -->`);
    if (!MARKER.test(html)) { console.error(`Startseite: Marker start:${s.id} fehlt in index.html.`); process.exitCode = 2; continue; }
    const { gross, mittel, zeilen } = belegung.get(s.id) || { gross: [], mittel: [], zeilen: [] };
    if (!(gross.length + mittel.length + zeilen.length)) {
      // Keine Meldung, keine Flaeche. Der Leerzustand ist das Weglassen.
      html = html.replace(MARKER, () => `<!-- start:${s.id}:start --><!-- start:${s.id}:end -->`);
      console.log(`Sektion ${s.id}: keine Meldung uebrig, Sektion entfaellt.`);
      continue;
    }
    html = html.replace(MARKER, () => `<!-- start:${s.id}:start -->${sektionHtml(s, gross, mittel, zeilen)}<!-- start:${s.id}:end -->`);
    sektionenGeschrieben++;
    console.log(`Sektion ${s.id}: ${gross.length} gross, ${mittel.length} mittel, ${zeilen.length} Zeile(n).`);
  }
  console.log(`Startseite: ${sektionenGeschrieben} von ${SEKTIONEN.length} Sektionen gefuellt, ${vergeben.size} Meldungen vergeben.`);

  // Der Aufmacher steht im Index. Die Sichtpruefung in der CI vergleicht die
  // ausgelieferte Seite dagegen, nicht gegen editorial-current.json - sonst
  // schlaegt sie an, sobald der Aufmacher automatisch gewaehlt wurde.
  index.aufmacher = aufmacher ? { id: aufmacher.id, url: aufmacher.url, titel: aufmacher.titel } : null;
  index.nebenmeldungen = neben.map((a) => ({ id: a.id, url: a.url, titel: a.titel }));

  if (html !== alt) schreibe(rel, html);
}

// Der Index wird nach der Startseite geschrieben: er traegt den gewaehlten
// Aufmacher, damit die Sichtpruefung die ausgelieferte Seite dagegen haelt.
schreibe('api/inhalte.json', JSON.stringify(index, null, 1) + '\n');

// --------------------------------------------------------- Archiv /archiv/
{
  const rel = 'archiv/index.html'; const pfad = join(site, rel);
  if (existsSync(pfad)) {
    const alt = readFileSync(pfad, 'utf8'); let html = alt;
    const monate = new Map();
    for (const a of artikel) { const k = String(a.datum || '').slice(0, 7); if (!/^\d{4}-\d{2}$/.test(k)) continue; if (!monate.has(k)) monate.set(k, []); monate.get(k).push(a); }
    const kurz = (iso) => new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit' }).format(new Date(iso)) + '.';
    const li = (a) => `<li><time datetime="${esc(a.datum)}">${kurz(a.datum)}</time><a href="${esc(a.url)}">${esc(a.titel)}</a><span class="rs">${esc(a.ressortLabel)}</span></li>`;
    for (const [k, liste] of [...monate.entries()].sort((a, b) => b[0].localeCompare(a[0]))) {
      const n = liste.length; const block = `<div class="archive-month" id="${k}"><h3>${new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', month: 'long', year: 'numeric' }).format(new Date(k + '-15T12:00:00+02:00'))}<small>${n} Meldung${n === 1 ? '' : 'en'}</small></h3><ul class="archive-list">${liste.map(li).join('')}</ul></div>`;
      const re = new RegExp(`<div class="archive-month" id="${k}">[\\s\\S]*?<\\/ul><\\/div>`);
      if (re.test(html)) html = html.replace(re, () => block);
      else { const jahr = `<div class="archive-year"><h2>${k.slice(0, 4)}</h2>`; const i = html.indexOf(jahr); if (i >= 0) html = html.slice(0, i + jahr.length) + block + html.slice(i + jahr.length); }
    }
    html = zaehler(html, artikel.length);
    if (html !== alt) schreibe(rel, html);
  }
}

// ------------------------------------------------------------- Sidebox
{
  const top = artikel.slice(0, 5).map((a) => `<li><a href="${esc(a.url)}">${esc(a.titel)}</a></li>`).join('');
  const re = /<div class="sidebox"><h3>(?:Aus den Ortsteilen|Neueste Meldungen)<a href="\/nachrichten\/">alle<\/a><\/h3><ol class="ranked">[\s\S]*?<\/ol><\/div>/g;
  const neu = `<div class="sidebox"><h3>Neueste Meldungen<a href="/nachrichten/">alle</a></h3><ol class="ranked">${top}</ol></div>`;
  // Ohne Meldung bliebe auf jeder Seite eine leere <ol> stehen - genau die
  // leere Flaeche, die dieser Umbau abschafft. Dann bleibt die vorhandene
  // Sidebox unveraendert und der Lauf sagt es auf der Konsole.
  if (!top) console.log('Warnung: keine Meldung vorhanden - Sidebox "Neueste Meldungen" bleibt unveraendert.');
  else (function lauf(d) { for (const e of readdirSync(d)) { const p = join(d, e); if (statSync(p).isDirectory()) lauf(p); else if (e.endsWith('.html')) { const alt = readFileSync(p, 'utf8'); if (re.test(alt)) { re.lastIndex = 0; const n = alt.replace(re, () => neu); if (n !== alt) schreibe(p.slice(site.length + 1), n); } re.lastIndex = 0; } } })(site);
}

// ------------------------------------------------ Ortswahl auf allen Seiten
// Vorher stand unter dem Kopf eine flache Reihe mit fuenf gleich grossen
// Ortslinks, auf 214 Seiten in sechs handgepflegten Varianten, die sich nur im
// aria-current unterschieden. Sie las sich wie eine zweite Hauptnavigation und
// versprach fuenf gleich volle Orte; tatsaechlich haben vier davon zwei bis
// sechs Meldungen. Jetzt ein geschlossener Schalter mit der aktuellen Ausgabe,
// aufklappbar, mit den echten Zahlen aus dem Bestand.
// Bewusst <details>: Tastatur, Screenreader und Seiten ohne JavaScript
// funktionieren dadurch ohne Zutun. JS ergaenzt nur das Schliessen bei Klick
// daneben und mit Escape.
{
  const ORTSWAHL_RE = /<nav class="(?:districtbar|ortswahl)"[\s\S]*?<\/nav>/;
  const EDITION_RE = /<div class="shell edition-label">[\s\S]*?<\/div>/g;
  const orte = Object.entries(ORTSTEILE)
    .map(([slug, label]) => ({ slug, label, anzahl: bestandOrt[slug] || 0 }))
    .filter((o) => o.anzahl > 0);
  const ohne = Object.keys(ORTSTEILE).filter((s) => !(bestandOrt[s] > 0));
  for (const s of ohne) console.log(`Ortswahl: ${ORTSTEILE[s]} hat keine Meldung und erscheint nicht in der Liste.`);

  // Die Gruppe "Nachbarschaft" erscheint erst, wenn ein Nachbarort eine
  // veroeffentlichte Meldung hat. Heute erscheint sie nicht (Standard 2).
  const nachbarn = [];

  // MERZENICH · JETZT (Anhang A4.3): eine Zeile rechts neben der Ortswahl,
  // nur auf der Startseite. Aus dem Index steht hier die juengste Meldung
  // (ohne Sport und Tipp, wie die Startseite) und die Zeitpunkte der letzten
  // Meldungen; kopf.js rechnet daraus "vor 18 Min." und "heute: 3 neue" und
  // setzt den naechsten Termin aus der Servicespalte ein. Fehlt ein Wert,
  // bleibt das Feld verborgen.
  const jetztBasis = redaktionell.filter((a) => a.ressort !== 'sport' && a.datum && !a.undatiert)
    .sort((x, y) => String(y.datum).localeCompare(String(x.datum)));
  const jetztHtml = () => {
    const n = jetztBasis[0];
    if (!n) return '';
    return '<div class="jetzt" role="group" aria-label="Merzenich jetzt">'
      + '<span class="jetzt-marke"><span class="jetzt-punkt" aria-hidden="true"></span>Merzenich · Jetzt</span>'
      + `<span class="jetzt-feld jetzt-neu">Neu <time datetime="${esc(n.datum)}">${esc(kurzZeit(n.datum))}</time> <a href="${esc(n.url)}">${esc(n.titel)}</a></span>`
      + `<span class="jetzt-feld jetzt-heute" data-daten="${esc(jetztBasis.slice(0, 12).map((a) => a.datum).join(' '))}" hidden></span>`
      + '<span class="jetzt-feld jetzt-termin" hidden></span>'
      + '</div>';
  };

  function ortswahlHtml(aktuellSlug, mitJetzt) {
    const aktuell = ORTSTEILE[aktuellSlug] || ORTSTEILE.merzenich;
    const zeile = (o) => `<a href="/${o.slug}/"${o.slug === aktuellSlug ? ' aria-current="page"' : ''}><span class="ortswahl-name">${esc(o.label)}</span><span class="ortswahl-zahl">${o.anzahl} Meldung${o.anzahl === 1 ? '' : 'en'}</span></a>`;
    const gruppe = (titel, liste) => (liste.length ? `<p class="ortswahl-gruppe">${esc(titel)}</p>${liste.map(zeile).join('')}` : '');
    return '<nav class="ortswahl" aria-label="Ausgabe waehlen"><div class="shell">'
      + '<details class="ortswahl-schalter">'
      + `<summary><span class="ortswahl-marke">Ausgabe</span><span class="ortswahl-aktuell">${esc(aktuell)}</span><svg class="ortswahl-pfeil" viewBox="0 0 16 16" aria-hidden="true"><path d="M4 6l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>`
      + `<div class="ortswahl-liste">${gruppe('Gemeinde Merzenich', orte)}${gruppe('Nachbarschaft', nachbarn)}</div>`
      + '</details>' + (mitJetzt ? jetztHtml() : '') + '</div></nav>';
  }

  // Welche Ausgabe die Seite zeigt, steht im Pfad. Eine Artikelseite gehoert
  // zur Gemeinde, auch wenn die Meldung aus einem Ortsteil kommt: die Ausgabe
  // ist die Gemeinde, gefiltert wird nichts (Standard 2).
  const ortAusPfad = (rel) => {
    const erstes = rel.split('/')[0];
    return Object.prototype.hasOwnProperty.call(ORTSTEILE, erstes) ? erstes : 'merzenich';
  };

  let gesetzt = 0, ohneLeiste = 0;
  (function lauf(d) {
    for (const e of readdirSync(d)) {
      const p = join(d, e);
      if (statSync(p).isDirectory()) { lauf(p); continue; }
      if (!e.endsWith('.html')) continue;
      const rel = p.slice(site.length + 1);
      const alt = readFileSync(p, 'utf8');
      if (!ORTSWAHL_RE.test(alt)) { ohneLeiste++; continue; }
      let neu = alt.replace(ORTSWAHL_RE, () => ortswahlHtml(ortAusPfad(rel), rel === 'index.html'));
      // "Merzenich & seine Ortsteile / Die lokale Ausgabe" war eine eigene
      // Zwischenebene ueber dem Inhalt. Die Aussage steckt jetzt im Wort
      // "Ausgabe" im Schalter darueber; die Zeile kostete rund 40 px.
      neu = neu.replace(EDITION_RE, '');
      if (neu !== alt) schreibe(rel, neu);
      gesetzt++;
    }
  })(site);
  console.log(`Ortswahl: ${gesetzt} Seiten, ${orte.length} Orte gelistet, ${ohneLeiste} Seiten ohne Leiste.`);
}

// ------------------------------- Vereinslogos raus aus den Bildflaechen
// Designstandard 7b: ein Vereinslogo oder Wappen fuellt nie eine Bildflaeche,
// der Verein erscheint als kleine Textmarke. Gemessen am 23.09. standen 17
// solche Flaechen auf sechs Seiten, alle mit demselben SC-Logo. Sie stammen aus
// der Zeit, als die betroffenen Sportmeldungen noch kein eigenes Motiv hatten -
// heute tragen sie ein Symbolbild aus dem Sport-Pool. Der Schritt setzt das
// Bild ein, das im Index zum verlinkten Artikel steht.
{
  const nachUrlAlle = new Map(artikel.map((a) => [a.url, a]));
  // Die Aufmacherzeile der Themenseiten (article.feed-lead) traegt ihren Link
  // ohne Klasse; ein Muster auf feed-img/karte-bild allein liess sie stehen.
  // Deshalb ueber jeden Link, der direkt eine .media-Flaeche umschliesst.
  const LOGO_FLAECHE = /<a ([^>]*?)href="([^"]+)"([^>]*)><div class="media[^"]*">[\s\S]*?<\/div><\/a>/g;
  const klasseVon = (vor, nach) => (/class="([^"]*)"/.exec(`${vor} ${nach}`) || [, ''])[1];
  // "media contain" heisst nur "nicht beschneiden" und steht auch an Plakaten
  // und Veranstaltungsbildern - als Kriterium war es falsch und hat beim ersten
  // Versuch 31 statt 17 Flaechen erwischt. Entscheidend ist, was das Bild
  // zeigt: Vereinslogo, Vereinswappen oder das Zeichen der Gemeinde.
  const LOGO_MOTIV = /vereinslogo|vereinswappen|\bwappen\b|zeichen der gemeinde/i;
  const istLogo = (t) => {
    const badge = /<span class="badge">([^<]*)<\/span>/.exec(t);
    const alt = /\salt="([^"]*)"/.exec(t);
    return LOGO_MOTIV.test(`${badge ? badge[1] : ''} ${alt ? alt[1] : ''}`);
  };
  let ersetzt = 0, ohneErsatz = 0;
  (function lauf(d) {
    for (const e of readdirSync(d)) {
      const p = join(d, e);
      if (statSync(p).isDirectory()) { lauf(p); continue; }
      if (!e.endsWith('.html')) continue;
      const rel = p.slice(site.length + 1);
      const alt = readFileSync(p, 'utf8');
      if (!/logo|wappen|media contain/i.test(alt)) continue;
      const neu = alt.replace(LOGO_FLAECHE, (treffer, vor, url, nach) => {
        const klasse = klasseVon(vor, nach);
        if (!istLogo(treffer)) return treffer;
        const a = nachUrlAlle.get(url);
        const b = a && a.bild;
        // Ohne Ersatzmotiv bleibt das Markup stehen: lieber ein Logo als ein
        // Loch. Die Zeile faellt dann in der Zaehlung auf.
        if (!b || !b.src || LOGO_MOTIV.test(`${b.badge || ''} ${b.alt || ''}`)) { ohneErsatz++; return treffer; }
        ersetzt++;
        const srcset = b.srcset ? ` srcset="${esc(b.srcset)}"` : '';
        const masse = b.width && b.height ? ` width="${b.width}" height="${b.height}"` : '';
        const badge = b.badge ? `<span class="badge">${esc(b.badge)}</span>` : '';
        return `<a${klasse ? ` class="${klasse}"` : ''} href="${esc(url)}" tabindex="-1" aria-hidden="true"><div class="media">`
          + `<img src="${esc(b.src)}"${srcset} sizes="(max-width: 640px) 120px, 240px" alt="${esc(b.alt || '')}"${masse}`
          + ` loading="lazy" decoding="async" data-editorial-image>${badge}</div></a>`;
      });
      if (neu !== alt) schreibe(rel, neu);
    }
  })(site);
  console.log(`Vereinslogos: ${ersetzt} Bildflaeche(n) durch das Artikelbild ersetzt, ${ohneErsatz} ohne Ersatzmotiv belassen.`);
}

// ----------------------------------------------------------- latest.json
{
  const rel = 'api/latest.json'; const alt = JSON.parse(readFileSync(join(site, rel), 'utf8'));
  alt.generated = neuester; alt.stand = neuester;
  alt.items = artikel.slice(0, 20).map((a) => ({ title: a.titel, url: abs(a.url), date: a.datum, ressort: a.ressort, ort: a.ortsteil, teaser: a.teaser, image: a.bild ? abs(a.bild.src) : null }));
  schreibe(rel, JSON.stringify(alt, null, 2) + '\n');
}

// ---------------------------------------------------------------- Feeds
// Ein Feedeintrag ist eine datierte Ankuendigung: RSS sortiert ueber <pubDate>,
// Atom verlangt in <published> ein gueltiges Datum. Die bewusst undatierten
// Hintergrundstuecke haben kein Erscheinungsdatum und duerfen auch keines
// bekommen - das Abrufdatum waere der Erfassungstag der Redaktion, nicht der
// Tag der Veroeffentlichung. Frueher lieferten sie deshalb
// <pubDate>Invalid Date</pubDate> und ein leeres <published></published> aus.
// Statt ein Datum zu erfinden, bleiben sie aus den Feeds heraus; auf der
// Seite, in den Ressortlisten, im Archiv und in sitemap-artikel.xml stehen
// sie unveraendert (dort traegt <lastmod> das Aenderungsdatum, das es gibt).
// Beitraege, deren Quelle kein Veroeffentlichungsdatum nennt, fallen nicht aus
// den Feeds. Der Tag, an dem der Beitrag hier erschienen ist (abgerufen), ist
// ein echtes, belegtes Datum und traegt Sortierung und Zeitstempel im Feed.
// Die Seite selbst behauptet weiterhin kein Quelldatum: dort steht keine
// Zeitzeile, sondern der Hinweis im Artikelkopf.
const feedZeit = (a) => a.datum || (a.abgerufen ? `${a.abgerufen}T00:00:00+02:00` : '');
const ohneZeit = artikel.filter((a) => !feedZeit(a));
if (ohneZeit.length) { console.log(`  Feeds: ${ohneZeit.length} Meldung(en) ohne jedes Datum ausgelassen.`); for (const a of ohneZeit) console.log(`    ${a.url}`); }
const feedArtikel = artikel.filter((a) => feedZeit(a)).sort((a, b) => (feedZeit(a) < feedZeit(b) ? 1 : feedZeit(a) > feedZeit(b) ? -1 : 0));
const undatiertImFeed = feedArtikel.filter((a) => !a.datum).length;
if (undatiertImFeed) console.log(`  Feeds: ${undatiertImFeed} Meldung(en) ohne Quelldatum, Zeitstempel ist der Erscheinungstag hier.`);
const rssItem = (a) => `<item>\n<title>${x(a.titel)}</title>\n<link>${abs(a.url)}</link>\n<guid isPermaLink="true">${abs(a.url)}</guid>\n<pubDate>${new Date(feedZeit(a)).toUTCString()}</pubDate>\n<category>${x(a.ressortLabel)}</category>\n<category>${x(ORTSTEILE[a.ortsteil] || 'Region')}</category>\n<dc:creator>Redaktion Merzenich Aktuell</dc:creator>\n<description>${x(a.teaser)}</description>${a.bild ? `\n<media:content url="${x(abs(a.bild.src))}" medium="image">${a.bild.credit ? `<media:credit>${x(a.bild.credit)}</media:credit>` : ''}<media:description>${x(a.bild.alt)}</media:description></media:content>` : ''}\n</item>`;
const atomEntry = (a) => `<entry>\n<title>${x(a.titel)}</title>\n<link href="${abs(a.url)}"/>\n<id>${abs(a.url)}</id>\n<published>${x(feedZeit(a))}</published>\n<updated>${x(a.aktualisiert || feedZeit(a))}</updated>\n<summary>${x(a.teaser)}</summary>\n<content type="html">${x(`<p>${esc(a.teaser)}</p><p><a href="${abs(a.url)}">Zum Beitrag</a></p>`)}</content>\n<category term="${x(a.ressortLabel)}"/>\n</entry>`;
function ersetzeBlock(rel, tagStart, tagEnde, items, datumTag) {
  const pfad = join(site, rel); if (!existsSync(pfad)) return;
  const alt = readFileSync(pfad, 'utf8');
  const i = alt.indexOf(tagStart); const j = alt.lastIndexOf(tagEnde);
  let kopf = i >= 0 ? alt.slice(0, i) : alt.slice(0, alt.lastIndexOf('</channel>') >= 0 ? alt.lastIndexOf('</channel>') : alt.lastIndexOf('</feed>'));
  const fuss = j >= 0 ? alt.slice(j + tagEnde.length) : alt.slice(kopf.length);
  if (datumTag === 'rss') kopf = kopf.replace(/<lastBuildDate>[^<]*<\/lastBuildDate>/, `<lastBuildDate>${new Date(neuester).toUTCString()}</lastBuildDate>`).replace(/<pubDate>[^<]*<\/pubDate>/, `<pubDate>${new Date(neuester).toUTCString()}</pubDate>`);
  if (datumTag === 'atom') kopf = kopf.replace(/<updated>[^<]*<\/updated>/, `<updated>${x(neuester)}</updated>`);
  const neu = kopf.replace(/\s*$/, '\n') + items.join('\n') + '\n' + fuss.replace(/^\s*/, '');
  schreibe(rel, neu);
}
ersetzeBlock('feed.xml', '<item>', '</item>', feedArtikel.filter((a) => a.ressort !== 'tipp').slice(0, 30).map(rssItem), 'rss');
ersetzeBlock('atom.xml', '<entry>', '</entry>', feedArtikel.filter((a) => a.ressort !== 'tipp').slice(0, 30).map(atomEntry), 'atom');
ersetzeBlock('nachrichten/feed.xml', '<item>', '</item>', feedArtikel.filter((a) => a.ressort !== 'tipp').slice(0, 30).map(rssItem), 'rss');
for (const r of ressorts) ersetzeBlock(`${r}/feed.xml`, '<item>', '</item>', feedArtikel.filter((a) => a.ressort === r).slice(0, 30).map(rssItem), 'rss');
for (const ort of Object.keys(ORTSTEILE)) ersetzeBlock(`${ort}/feed.xml`, '<item>', '</item>', feedArtikel.filter((a) => a.ortsteil === ort).slice(0, 30).map(rssItem), 'rss');
{
  const rel = 'feed.json'; if (existsSync(join(site, rel))) {
    const alt = JSON.parse(readFileSync(join(site, rel), 'utf8'));
    alt.items = feedArtikel.filter((a) => a.ressort !== 'tipp').slice(0, 30).map((a) => ({ id: abs(a.url), url: abs(a.url), title: a.titel, summary: a.teaser, content_html: `<p>${esc(a.teaser)}</p>`, date_published: feedZeit(a), date_modified: a.aktualisiert || feedZeit(a), ...(a.bild ? { image: abs(a.bild.src) } : {}), tags: [a.ressortLabel, ORTSTEILE[a.ortsteil] || 'Region', ...a.themen.map((t) => t.label)] }));
    schreibe(rel, JSON.stringify(alt, null, 2) + '\n');
  }
}
{
  const frisch = redaktionell.filter((a) => Date.parse(neuester) - Date.parse(a.datum) <= 2 * 86400e3);
  const urls = frisch.map((a) => `<url><loc>${abs(a.url)}</loc><news:news><news:publication><news:name>Merzenich Aktuell</news:name><news:language>de</news:language></news:publication><news:publication_date>${x(a.datum)}</news:publication_date><news:title>${x(a.titel)}</news:title></news:news></url>`);
  schreibe('news-sitemap.xml', `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">\n${urls.join('\n')}\n</urlset>\n`);
  const alle = artikel.map((a) => `<url><loc>${abs(a.url)}</loc><lastmod>${x(a.aktualisiert || a.datum)}</lastmod><changefreq>weekly</changefreq><priority>0.8</priority>${a.bild ? `<image:image><image:loc>${x(abs(a.bild.src))}</image:loc><image:title>${x(a.bild.alt)}</image:title></image:image>` : ''}</url>`);
  schreibe('sitemap-artikel.xml', `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">\n${alle.join('\n')}\n</urlset>\n`);
}

// ------------------------------------------------- Bestand auf der Konsole
// Nach jedem Lauf soll die Redaktion sehen, was die Seite wirklich hat: je
// Ressort und je Ortsteil die Anzahl, und fuer jeden leeren Bereich eine
// eigene Warnzeile. Bewusst console.log und kein Fehler-Exit: ein leerer
// Bereich ist keine kaputte Seite, sondern eine redaktionelle Luecke.
{
  const listeZusatz = (basis) => {
    const l = bestandListen[basis];
    if (!l) return 'keine Liste';
    if (l.liste === 'keine Seite') return 'keine Seite vorhanden';
    if (l.liste === 'kein Feed') return 'Seite ohne Meldungsliste';
    return `Liste ${basis} mit ${l.anzahl}`;
  };
  // Die Warnzeile sagt nicht nur die Zahl, sondern was daraus auf der Seite wird.
  const warnZusatz = (basis) => {
    const l = bestandListen[basis];
    if (!l) return 'es gibt dafuer keine Liste';
    if (l.liste === 'keine Seite') return 'es gibt dafuer keine Seite';
    if (l.liste === 'kein Feed') return `die Seite ${basis} fuehrt keine Meldungsliste`;
    return `die Liste ${basis} zeigt den Leerzustand`;
  };
  const zeile = (art, name, anzahl, zusatz) => `  ${`${art} ${name}`.padEnd(24)}${String(anzahl).padStart(3)} Meldung${anzahl === 1 ? ' ' : 'en'}  ${zusatz}`;
  console.log(`Bestand: ${artikel.length} Meldungen.`);
  for (const r of RESSORT_ORDNUNG) {
    const zusatz = r === ARCHIV_RESSORT ? `Gesamtarchiv, ${listeZusatz(`/${r}/`)}` : listeZusatz(`/${r}/`);
    console.log(zeile('Ressort', r, bestandRessort[r], zusatz));
  }
  for (const o of ORT_ORDNUNG) console.log(zeile('Ortsteil', o, bestandOrt[o], listeZusatz(`/${o}/`)));
  for (const r of leerRessorts) console.log(`  Warnung: Ressort ${r} hat 0 Meldungen, ${warnZusatz(`/${r}/`)}.`);
  for (const o of leerOrte) console.log(`  Warnung: Ortsteil ${o} hat 0 Meldungen, ${warnZusatz(`/${o}/`)}.`);
  if (!leerRessorts.length && !leerOrte.length) console.log('  Kein Bereich ohne Meldung.');
}

console.log(`Inhaltsindex: ${artikel.length} Artikel, Stand ${neuester}; ${geaendert.length} Datei(en) ${nurPruefen ? 'nicht aktuell' : 'geschrieben'}${geloescht.length ? `, ${geloescht.length} Seitenordner ${nurPruefen ? 'ueberzaehlig' : 'entfernt'}` : ''}.`);
if (geaendert.length && geaendert.length <= 40) console.log('  ' + geaendert.join('\n  '));
if (nurPruefen && (geaendert.length || geloescht.length)) process.exit(2);
