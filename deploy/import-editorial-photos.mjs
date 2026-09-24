#!/usr/bin/env node
/**
 * Merzenich Aktuell — echte Foto-Pools für Editorial Image System V2.
 *
 * Quelle: ausschließlich Wikimedia Commons (freie Lizenzen, kommerzielle
 * Nutzung und Bearbeitung zulässig). Zuerst werden Merzenich/Kreis Düren/NRW
 * gesucht, danach erst allgemeine deutsche Motive.
 *
 * Je Pool werden exakt 20 verschiedene Fotos lokal materialisiert.
 * Die Original-Dateiseite, Lizenz, Urheber und Download-URL bleiben im
 * Manifest nachvollziehbar. Für die Website wird eine max. 1600px breite
 * Commons-Ableitung gespeichert, damit das Repository nicht mit mehreren
 * hundert Megabyte Originaldateien explodiert.
 */
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, unlinkSync, writeFileSync } from 'node:fs';
import { dirname, extname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const SITE = join(ROOT, 'chatgpt-site');
const ASSET_ROOT = join(SITE, 'assets', 'editorial-pools');
const MANIFEST = join(SITE, 'data', 'editorial-images', 'editorial-photo-pools.json');
// Ergebnis der Sichtprüfung: ausgeschlossene Commons-Titel (mit Grund) und
// freigegebene Titel. Ausgeschlossenes wird gelöscht und nie wieder geladen.
const PRUEFUNG = join(ROOT, 'deploy', 'editorial-photo-review.json');
// Diese Pools haben keine gezeichneten Reservemotive und brauchen volle 20 Fotos.
// Die übrigen Pools fallen auf die V2-Symbolgrafiken zurück, dort ist eine
// kleinere, dafür geprüfte Fotoauswahl besser als aufgefüllter Ausschuss.
const NUR_FOTO = new Set(['aktuell', 'blaulicht', 'brand', 'termine', 'tipp', 'menschen']);
const SERIE_MAX = 3;
const PRO_POOL = 20;
const REFRESH = process.argv.includes('--refresh');
const HEUTE = new Date().toISOString().slice(0, 10);
const UA = 'MerzenichAktuell-EditorialImageImporter/2.1 (https://merzenichaktuell.hk-growthoperator.de)';

const POOLS = {
  aktuell: {
    tags: ['aktuell','merzenich','dueren','ort','strasse','gemeinde'],
    queries: ['Merzenich Kreis Düren', 'Merzenich Denkmal', 'Golzheim Merzenich', 'Girbelsrath', 'Morschenich', 'Merzenich Rathaus', 'Kreis Düren Dorf']
  },
  blaulicht: {
    tags: ['blaulicht','einsatz','rettung','notfall'],
    queries: ['deepcat:"Ambulances in North Rhine-Westphalia"', 'deepcat:"Emergency medical services in North Rhine-Westphalia"', 'deepcat:"Ambulances in Germany"', 'Rettungswache Nordrhein-Westfalen', 'Rettungswagen Nordrhein-Westfalen', 'Rettungshubschrauber Nordrhein-Westfalen', 'Rettungsdienst Kreis Düren', 'Notarzteinsatzfahrzeug Deutschland']
  },
  polizei: {
    tags: ['polizei','streifenwagen','einsatz','kontrolle'],
    queries: ['Polizeiwache Nordrhein-Westfalen', 'Polizeipräsidium Nordrhein-Westfalen', 'Polizeihubschrauber Nordrhein-Westfalen', 'Polizei Düren', 'Polizei Nordrhein-Westfalen Streifenwagen', 'Polizei Nordrhein-Westfalen']
  },
  feuerwehr: {
    tags: ['feuerwehr','loeschfahrzeug','einsatz','rettung'],
    queries: ['Feuerwehrgerätehaus Kreis Düren', 'Feuerwehr Düren', 'Feuerwehrgerätehaus Nordrhein-Westfalen', 'Feuerwehr Nordrhein-Westfalen', 'Feuerwehr Übung Nordrhein-Westfalen']
  },
  brand: {
    tags: ['brand','feuer','rauch','loeschen'],
    queries: ['deepcat:"Fires in North Rhine-Westphalia"', 'deepcat:"Structure fires in Germany"', 'deepcat:"Fires in Germany"', 'Dachstuhlbrand Feuerwehr', 'Wohnhausbrand Feuerwehr', 'Großbrand Feuerwehr Nordrhein-Westfalen', 'Brandeinsatz Feuerwehr Deutschland', 'Löscharbeiten Feuerwehr']
  },
  sport: {
    tags: ['sport','fussball','amateur','spiel','platz'],
    queries: ['deepcat:"Football venues in North Rhine-Westphalia"', 'deepcat:"Association football pitches in Germany"', 'Fußballplatz Nordrhein-Westfalen', 'Kunstrasenplatz', 'Sporthalle Nordrhein-Westfalen', 'Tennisanlage Nordrhein-Westfalen']
  },
  termine: {
    tags: ['termine','veranstaltung','event','fest','markt'],
    queries: ['Dorffest Nordrhein-Westfalen', 'Kirmes Nordrhein-Westfalen', 'Weihnachtsmarkt Nordrhein-Westfalen', 'Schützenfest Nordrhein-Westfalen', 'Martinsfeuer Nordrhein-Westfalen', 'Veranstaltung Deutschland']
  },
  vereine: {
    tags: ['vereine','verein','ehrenamt','gemeinschaft'],
    queries: ['Vereinsheim Nordrhein-Westfalen', 'Schützenhalle Nordrhein-Westfalen', 'Dorfgemeinschaftshaus Nordrhein-Westfalen', 'Maibaum Nordrhein-Westfalen', 'Karnevalswagen Nordrhein-Westfalen', 'Bücherschrank Nordrhein-Westfalen']
  },
  leben: {
    tags: ['leben','dorfleben','alltag','nachbarschaft','familie'],
    queries: ['Dorfplatz Nordrhein-Westfalen', 'Dorfstraße Kreis Düren', 'Kreis Düren Dorf', 'Spielplatz Nordrhein-Westfalen', 'Wochenmarkt Nordrhein-Westfalen', 'Park Düren', 'Dorfleben Nordrhein-Westfalen']
  },
  wirtschaft: {
    tags: ['wirtschaft','handel','strukturwandel','gewerbe','handwerk','industrie'],
    queries: ['Tagebau Hambach', 'Wirtschaft Düren', 'Gewerbegebiet Kreis Düren', 'Handwerk Nordrhein-Westfalen', 'Einzelhandel Deutschland', 'Industrie Nordrhein-Westfalen']
  },
  tipp: {
    tags: ['tipp','freizeit','ausflug','wandern','radfahren','natur'],
    queries: ['Sophienhöhe', 'Wandern Kreis Düren', 'Radweg Kreis Düren', 'Rurtalsperre', 'Naturschutzgebiet Kreis Düren', 'Rur Kreis Düren', 'Radweg Nordrhein-Westfalen']
  },
  menschen: {
    // Keine Porträts: erkennbare Personen sind als Symbolbild unzulässig.
    tags: ['menschen','portraet','community','senioren','handwerk'],
    queries: ['Parkbank Nordrhein-Westfalen', 'Bürgerhaus Nordrhein-Westfalen', 'Dorfgemeinschaftshaus Kreis Düren', 'Gemeinschaftsgarten Deutschland', 'Seniorenheim Nordrhein-Westfalen', 'Nachbarschaftshilfe', 'Werkstatt Handwerk Deutschland']
  }
};

const BAD_TITLE = /(logo|wappen|coat of arms|flag|karte|map of|locator|diagram|poster|plakat|flyer|icon|scan|seite \d|page \d|screenshot|symbol|svg)/i;
// Strengere Filter nach der Sichtprüfung vom 24.09.2026.
const BAD_TITLE_STRENG = /(satellit|sentinel|modis|copernicus|nasa|landsat|viirs|olci|chart|diagramm|statistik|mitglieder|karte|openstreetmap|abzeichen|patch|badge|kennz|kennzeichen|license plate|nummernschild|portr[aä]it|politik|minister|army|soldat|soldier|military|milit[aä]r|bundeswehr|painting|gem[aä]lde|museum|hdri|poly haven|render|demonstration|protest|kundgebung|unfall|accident|crash|pride|parade|b[aä]ckerei|bakery|fire suppression|halon|argonite|fm-200|l[oö]schanlage|model car|modellauto|dosimeter|sound level|zivilschutz|belohnung)/i;
const BAD_KATEGORIE = /(portrait|people of|politicians|athletes|players|footballers|musicians|actors|soldiers|military|united states army|demonstrations|protests|satellite|maps of|logos|coats of arms|patches|license plates|vehicle registration|paintings|diagrams|charts|crowds|children)/i;
const AUSLAND = /(croatia|kroatien|hrvatska|switzerland|schweiz|austria|österreich|california|united states|\busa\b|england|london|united kingdom|scotland|shetland|faroe|venezuela|new zealand|afghanistan|poland|polska|netherlands|niederlande|belgium|belgien|france|frankreich|manchester|austria|japan|tokyo|osaka|italy|italia|italien|canada|kanada|china|korea)/i;
// Titel in nicht-lateinischer Schrift stammen fast immer aus dem Ausland.
const FREMDE_SCHRIFT = /[\u0370-\u03ff\u0400-\u04ff\u0590-\u06ff\u3040-\u30ff\u3400-\u9fff\uac00-\ud7af]/;
// Sportplatz-Suchen treffen Wegekreuze "hinter dem Sportplatz".
const SPORT_FREMD = /(bildstock|wegekreuz|kreuz|kapelle|denkmal|gedenk|heiligenh)/i;
// Einsatzkräfte tragen je Bundesland eigene Farben und Wappen.
const ANDERES_LAND = /(baden-württemberg|baden-wuerttemberg|bayern|bavaria|hessen|hamburg|saarland|niedersachsen|berlin|sachsen|thüringen|brandenburg|rheinland-pfalz|schleswig|mecklenburg|bremen|heidelberg|karlsruhe|stuttgart|münchen|munich|fulda)/i;
const EINSATZ_POOLS = new Set(['polizei', 'blaulicht', 'feuerwehr', 'brand']);
// Namensgleichheit: Merzenich bei Zülpich und die Kölner Bäckerei „Merzenich“.
const FALSCHES_MERZENICH = /(z[uü]e?lpich|euskirchen|k[oö]ln|cologne|eigelstein|schildergasse)/i;

const ALLOWED_MIME = new Set(['image/jpeg', 'image/png', 'image/webp']);

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const hash = (x) => createHash('sha256').update(x).digest('hex');
const stripHtml = (s = '') => String(s)
  .replace(/<br\s*\/?>/gi, ' ')
  .replace(/<[^>]+>/g, ' ')
  .replace(/&nbsp;/g, ' ')
  .replace(/&amp;/g, '&')
  .replace(/&quot;/g, '"')
  .replace(/&#039;|&apos;/g, "'")
  .replace(/\s+/g, ' ')
  .trim();

function licenceAllowed(name = '') {
  const s = String(name);
  if (/NC|ND/i.test(s)) return false;
  return /CC0|CC BY(?:-SA)?|Public domain|PDM|Public Domain/i.test(s);
}

function extensionFor(info) {
  if (info.mime === 'image/jpeg') return 'jpg';
  if (info.mime === 'image/png') return 'png';
  if (info.mime === 'image/webp') return 'webp';
  const e = extname(new URL(info.thumburl || info.url).pathname).toLowerCase().replace('.', '');
  return e === 'jpeg' ? 'jpg' : e;
}

function localityFor(text) {
  const s = String(text).toLocaleLowerCase('de-DE');
  if (FALSCHES_MERZENICH.test(s)) return /nordrhein-westfalen|north rhine-westphalia| nrw/.test(s) ? 'NRW' : 'Deutschland / überregional';
  if (s.includes('merzenich') || s.includes('golzheim') || s.includes('girbelsrath') || s.includes('morschenich') || s.includes('bürgewald')) return 'Merzenich';
  if (s.includes('düren') || s.includes('dueren')) return 'Kreis Düren';
  if (s.includes('nordrhein-westfalen') || s.includes('north rhine-westphalia') || s.includes(' nrw')) return 'NRW';
  return 'Deutschland / überregional';
}

async function commonsSearch(query) {
  const params = new URLSearchParams({
    action: 'query',
    format: 'json',
    generator: 'search',
    gsrsearch: query,
    gsrnamespace: '6',
    gsrlimit: '50',
    prop: 'imageinfo',
    iiprop: 'url|mime|size|extmetadata',
    iiurlwidth: '1600'
  });
  const url = 'https://commons.wikimedia.org/w/api.php?' + params.toString();
  const res = await fetch(url, { headers: { 'User-Agent': UA, 'Accept': 'application/json' } });
  if (!res.ok) throw new Error('Commons search ' + res.status + ' for ' + query);
  const json = await res.json();
  return Object.values(json?.query?.pages || {}).map((page) => {
    const info = page.imageinfo?.[0];
    const meta = info?.extmetadata || {};
    return {
      pageid: page.pageid,
      title: page.title,
      info,
      license: stripHtml(meta.LicenseShortName?.value || ''),
      licenseUrl: meta.LicenseUrl?.value || '',
      artist: stripHtml(meta.Artist?.value || meta.Credit?.value || ''),
      description: stripHtml(meta.ImageDescription?.value || meta.ObjectName?.value || ''),
      categories: stripHtml(meta.Categories?.value || '')
    };
  });
}

const serie = (title = '') => String(title).replace(/^File:/, '').replace(/\.[^.]+$/, '')
  .toLocaleLowerCase('de-DE').replace(/[^a-zäöüß]+/g, ' ').trim().split(' ').slice(0, 3).join(' ');

function usable(c, pool) {
  if (!c?.info || !ALLOWED_MIME.has(c.info.mime)) return false;
  if (BAD_TITLE.test(c.title)) return false;
  if (AUSGESCHLOSSEN.has(c.title)) return false;
  const text = [c.title, c.description, c.categories].join(' ');
  if (BAD_TITLE_STRENG.test(c.title) || BAD_TITLE_STRENG.test(c.description)) return false;
  if (BAD_KATEGORIE.test(c.categories)) return false;
  if (AUSLAND.test(text) || FREMDE_SCHRIFT.test(c.title)) return false;
  if (pool === 'sport' && SPORT_FREMD.test(c.title)) return false;
  if (EINSATZ_POOLS.has(pool) && ANDERES_LAND.test(text)) return false;
  if (/merzenich/i.test(text) && FALSCHES_MERZENICH.test(text)) return false;
  if (pool === 'aktuell' && !['Merzenich', 'Kreis Düren'].includes(localityFor(text))) return false;
  if (!licenceAllowed(c.license)) return false;
  if ((c.info.width || 0) < 800 || (c.info.height || 0) < 600) return false;
  if (!c.info.thumburl && !c.info.url) return false;
  return true;
}

async function download(candidate, pool, index) {
  const url = candidate.info.thumburl || candidate.info.url;
  const res = await fetch(url, { headers: { 'User-Agent': UA } });
  if (!res.ok) throw new Error('image ' + res.status);
  const bytes = Buffer.from(await res.arrayBuffer());
  if (bytes.length < 10_000 || bytes.length > 18_000_000) throw new Error('unexpected image size ' + bytes.length);
  const ext = extensionFor(candidate.info);
  if (!['jpg','png','webp'].includes(ext)) throw new Error('unsupported extension ' + ext);
  const file = pool + '-' + String(index).padStart(2, '0') + '-' + hash(candidate.title).slice(0, 10) + '.' + ext;
  const dir = join(ASSET_ROOT, pool);
  mkdirSync(dir, { recursive: true });
  const abs = join(dir, file);
  writeFileSync(abs, bytes);

  const titleClean = candidate.title.replace(/^File:/, '').replace(/\.[^.]+$/, '').replace(/_/g, ' ');
  const alt = (candidate.description || titleClean).slice(0, 180);
  const artist = candidate.artist || 'Urheber siehe Quelldatei';
  const sourceUrl = 'https://commons.wikimedia.org/wiki/' + encodeURIComponent(candidate.title.replace(/ /g, '_'));
  const textForLocality = [candidate.title, candidate.description, candidate.categories].join(' ');
  const cfg = POOLS[pool];

  return {
    id: 'web-' + pool + '-' + String(index).padStart(2, '0') + '-' + hash(candidate.title).slice(0, 8),
    pool,
    src: '/assets/editorial-pools/' + pool + '/' + file,
    alt,
    credit: 'Symbolbild · ' + artist + ' / Wikimedia Commons · ' + candidate.license,
    source: 'Wikimedia Commons',
    sourceUrl,
    sourceTitle: candidate.title,
    downloadUrl: url,
    license: candidate.license,
    licenseUrl: candidate.licenseUrl,
    rightsCheckedAt: HEUTE,
    tags: [...new Set([...cfg.tags, ...titleClean.toLocaleLowerCase('de-DE').split(/[^a-zäöüß0-9]+/i).filter((x) => x.length >= 5).slice(0, 8)])],
    width: candidate.info.thumbwidth || candidate.info.width,
    height: candidate.info.thumbheight || candidate.info.height,
    format: ext,
    symbol: true,
    photo: true,
    locality: localityFor(textForLocality),
    geprueft: FREIGEGEBEN.has(candidate.title),
    checksum: hash(bytes)
  };
}

function loadExisting() {
  if (!existsSync(MANIFEST)) return { images: [] };
  try {
    const x = JSON.parse(readFileSync(MANIFEST, 'utf8'));
    return { images: Array.isArray(x.images) ? x.images : [] };
  } catch {
    return { images: [] };
  }
}

function pruefungLesen() {
  if (!existsSync(PRUEFUNG)) return { ausgeschlossen: [], freigegeben: [] };
  const x = JSON.parse(readFileSync(PRUEFUNG, 'utf8'));
  return { ausgeschlossen: x.ausgeschlossen || [], freigegeben: x.freigegeben || [] };
}

const pruefung = pruefungLesen();
const AUSGESCHLOSSEN = new Map(pruefung.ausgeschlossen.map((x) => [x.titel, x.grund]));
const FREIGEGEBEN = new Set(pruefung.freigegeben);
const dateiDa = (x) => existsSync(join(SITE, String(x.src || '').replace(/^\//, '')));

const existing = loadExisting();
const finalImages = [];
const usedTitles = new Set();
// Titel und Pruefsummen aller behaltenen Fotos vorab, ueber alle Pools: sonst
// laedt ein frueher bearbeiteter Pool dieselbe Datei, die ein spaeterer behaelt.
const behalten = REFRESH ? [] : existing.images.filter((x) => !AUSGESCHLOSSEN.has(x.sourceTitle) && dateiDa(x));
behalten.forEach((x) => x.sourceTitle && usedTitles.add(x.sourceTitle));
const belegteSummen = new Set(behalten.map((x) => x.checksum).filter(Boolean));

for (const pool of Object.keys(POOLS)) {
  const eigene = REFRESH ? [] : existing.images.filter((x) => x.pool === pool);
  const keep = [];
  for (const x of eigene) {
    if (AUSGESCHLOSSEN.has(x.sourceTitle)) {
      if (dateiDa(x)) unlinkSync(join(SITE, x.src.replace(/^\//, '')));
      console.log(pool + ': entfernt ' + x.sourceTitle + ' (' + AUSGESCHLOSSEN.get(x.sourceTitle) + ')');
      continue;
    }
    if (!dateiDa(x) || keep.length >= PRO_POOL) continue;
    const text = [x.sourceTitle, x.alt].join(' ');
    keep.push({ ...x, locality: localityFor(text), geprueft: FREIGEGEBEN.has(x.sourceTitle) });
  }
  keep.forEach((x) => x.sourceTitle && usedTitles.add(x.sourceTitle));
  finalImages.push(...keep);
  if (keep.length >= PRO_POOL) {
    console.log(pool + ': vorhandene ' + keep.length + ' Fotos bleiben bestehen');
    continue;
  }

  const fehlend = PRO_POOL - keep.length;
  const serien = new Map();
  for (const x of keep) serien.set(serie(x.sourceTitle), (serien.get(serie(x.sourceTitle)) || 0) + 1);
  const chosen = [];
  for (const query of POOLS[pool].queries) {
    let candidates = [];
    try {
      candidates = await commonsSearch(query);
    } catch (e) {
      console.warn(pool + ': Suche fehlgeschlagen (' + query + '): ' + e.message);
      continue;
    }
    for (const candidate of candidates) {
      // Reserve für verworfene Downloads.
      if (chosen.length >= fehlend + 8) break;
      if (!usable(candidate, pool) || usedTitles.has(candidate.title) || chosen.some((x) => x.title === candidate.title)) continue;
      const s = serie(candidate.title);
      if ((serien.get(s) || 0) >= SERIE_MAX) continue;
      serien.set(s, (serien.get(s) || 0) + 1);
      chosen.push(candidate);
    }
    if (chosen.length >= fehlend + 8) break;
    await sleep(180);
  }

  const belegt = new Set(keep.map((x) => Number(String(x.src).split('/').pop().split('-')[1])));
  let neu = 0;
  for (const candidate of chosen) {
    if (neu >= fehlend) break;
    let index = 1;
    while (belegt.has(index)) index++;
    let record;
    try {
      record = await download(candidate, pool, index);
    } catch (e) {
      console.warn(pool + ': Download verworfen ' + candidate.title + ': ' + e.message);
      continue;
    }
    if (belegteSummen.has(record.checksum) || finalImages.some((x) => x.checksum === record.checksum)) {
      unlinkSync(join(SITE, record.src.replace(/^\//, '')));
      console.warn(pool + ': Dublette verworfen ' + candidate.title);
      continue;
    }
    belegt.add(index);
    finalImages.push(record);
    usedTitles.add(candidate.title);
    neu++;
    console.log(pool + ': neu (ungeprüft) ' + candidate.title);
    await sleep(120);
  }

  const n = finalImages.filter((x) => x.pool === pool).length;
  if (n < PRO_POOL && NUR_FOTO.has(pool)) throw new Error(pool + ': nur ' + n + ' gültige Fotos, benötigt ' + PRO_POOL);
  if (n < PRO_POOL) console.warn(pool + ': ' + n + ' Fotos, Rest übernehmen die Symbolgrafiken');
  console.log(pool + ': ' + keep.length + ' behalten, ' + neu + ' neu');
}

mkdirSync(dirname(MANIFEST), { recursive: true });
const counts = Object.fromEntries(Object.keys(POOLS).map((p) => [p, finalImages.filter((x) => x.pool === p).length]));
writeFileSync(MANIFEST, JSON.stringify({
  version: 1,
  generated: HEUTE,
  source: 'Wikimedia Commons',
  minimumPerPool: PRO_POOL,
  pools: counts,
  images: finalImages.sort((x, y) => Object.keys(POOLS).indexOf(x.pool) - Object.keys(POOLS).indexOf(y.pool) || x.src.localeCompare(y.src))
}, null, 2) + '\n');

console.log('Editorial photo pools:', counts);
console.log('Total photos:', finalImages.length);
