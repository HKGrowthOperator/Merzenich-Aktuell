#!/usr/bin/env node
/**
 * Merzenich Aktuell - Google Search Console und PageSpeed per API.
 *
 * Damit die Redaktion (oder Claude in einer Sitzung) ohne Klicken in Googles
 * Oberflächen sieht, wie die Seite in der Suche und in Google News läuft.
 * Schreibt nichts in WordPress und nichts ins Repository.
 *
 *   zugang               Welche Properties das Dienstkonto sieht, mit Berechtigung.
 *   sitemaps             Reicht wp-sitemap.xml und news-sitemap.xml ein und zeigt,
 *                        was Google gelesen hat (eingereicht, indexiert, Fehler).
 *   leistung [--tage=28] Klicks, Impressionen, Klickrate und Position je Suchanfrage
 *                        und je Seite, getrennt nach Websuche, News-Reiter, Discover
 *                        und Google News. Kurzfassung auf dem Bildschirm, alles als JSON.
 *   pruefen <url> [...]  URL-Prüfung: im Index ja/nein, Grund, Canonical, letzter Crawl,
 *                        erkannte Rich Results (NewsArticle, Breadcrumb …).
 *   tempo [url]          PageSpeed mobil und Desktop: Leistung, SEO, Barrierefreiheit,
 *                        LCP, CLS, INP (Messwerte echter Besucher, wenn Google welche hat).
 *   selbsttest           Prüft die Anmeldung (JWT-Signatur) offline, ohne Schlüssel.
 *
 * Schlüssel kommen nur aus der Umgebung, nie aus dem Repository:
 *   GOOGLE_SERVICE_ACCOUNT_JSON  Inhalt der JSON-Schlüsseldatei des Dienstkontos
 *                                (oder der Pfad zu dieser Datei)
 *   PAGESPEED_API_KEY            API-Schlüssel für PageSpeed Insights (nur tempo;
 *                                ohne Schlüssel geht es mit kleinem Kontingent)
 *   GSC_PROPERTY                 optional, z. B. sc-domain:merzenich-aktuell.de;
 *                                sonst wird die Property der Domain gesucht
 *   GOOGLE_SEO_AUSGABE           optional: Ordner für die JSON-Dateien (--aus=…)
 * Einrichtung Schritt für Schritt: docs/GOOGLE-NEWS.md, „Zugriff per API“.
 *
 * Aufruf: node deploy/google-seo.mjs <befehl> [optionen]
 * Hinter einem Proxy (Container der Redaktion): NODE_USE_ENV_PROXY=1 davor.
 */
import { createSign, createVerify, generateKeyPairSync } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { pathToFileURL } from 'node:url';

export const DOMAIN = 'merzenich-aktuell.de';
export const START = `https://${DOMAIN}/`;
export const SITEMAPS = ['wp-sitemap.xml', 'news-sitemap.xml'];
const SCOPE = 'https://www.googleapis.com/auth/webmasters';
const TOKEN_URL = 'https://oauth2.googleapis.com/token';
const GSC = 'https://www.googleapis.com/webmasters/v3';
const ANLEITUNG = 'Anleitung: docs/GOOGLE-NEWS.md, Abschnitt „Zugriff per API“.';
/** Suchtypen der Search Console; Discover und Google News kennen keine Suchanfragen. */
export const SUCHTYPEN = [
  { typ: 'web', name: 'Websuche', anfragen: true },
  { typ: 'news', name: 'Reiter „News“ der Suche', anfragen: true },
  { typ: 'discover', name: 'Discover', anfragen: false },
  { typ: 'googleNews', name: 'Google News (App und news.google.com)', anfragen: false },
];

class Hinweis extends Error {}

const b64url = (b) => Buffer.from(b).toString('base64').replace(/=+$/, '').replace(/\+/g, '-').replace(/\//g, '_');

/** Dienstkonto aus dem Umgebungswert: JSON-Text, Pfad zur Datei oder Base64. */
export function dienstkontoLesen(wert) {
  const roh = String(wert ?? '').trim();
  if (!roh) throw new Hinweis(`GOOGLE_SERVICE_ACCOUNT_JSON ist nicht gesetzt. ${ANLEITUNG}`);
  let text = roh;
  if (!roh.startsWith('{')) {
    if (existsSync(roh)) text = readFileSync(roh, 'utf8');
    else text = Buffer.from(roh, 'base64').toString('utf8');
  }
  let k;
  try { k = JSON.parse(text); } catch {
    throw new Hinweis('GOOGLE_SERVICE_ACCOUNT_JSON ist kein gültiges JSON. Bitte den kompletten Inhalt der Schlüsseldatei eintragen, von { bis }.');
  }
  if (k.type !== 'service_account' || !k.client_email || !k.private_key) {
    throw new Hinweis('GOOGLE_SERVICE_ACCOUNT_JSON ist kein Dienstkonto-Schlüssel (type "service_account" mit client_email und private_key). Schlüssel neu erstellen: Dienstkonto → Schlüssel → Schlüssel hinzufügen → JSON.');
  }
  // Manche Oberflächen speichern Zeilenumbrüche als \n-Text.
  return { email: k.client_email, schluessel: k.private_key.replace(/\\n/g, '\n'), tokenUrl: k.token_uri || TOKEN_URL, projekt: k.project_id || '' };
}

/** Signierte Anmeldung (JWT, RS256) für den Token-Tausch bei Google. */
export function jwtBauen(konto, scope = SCOPE, jetzt = Math.floor(Date.now() / 1000)) {
  const kopf = b64url(JSON.stringify({ alg: 'RS256', typ: 'JWT' }));
  const inhalt = b64url(JSON.stringify({ iss: konto.email, scope, aud: konto.tokenUrl, iat: jetzt, exp: jetzt + 3600 }));
  const s = createSign('RSA-SHA256');
  s.update(`${kopf}.${inhalt}`);
  return `${kopf}.${inhalt}.${b64url(s.sign(konto.schluessel))}`;
}

/** Property der Domain wählen: Domain-Property vor URL-Präfix, nur bestätigte. */
export function propertyWaehlen(liste, domain = DOMAIN) {
  const ok = (liste || []).filter((p) => p && p.permissionLevel && p.permissionLevel !== 'siteUnverifiedUser');
  const reihenfolge = [`sc-domain:${domain}`, `https://${domain}/`, `https://www.${domain}/`, `http://${domain}/`];
  for (const url of reihenfolge) { const p = ok.find((x) => x.siteUrl === url); if (p) return p; }
  return null;
}

/** Zeitraum der letzten n Tage bis gestern (Google liefert frische Daten mit dataState "all"). */
export function zeitraum(tage, heute = new Date()) {
  const tag = (d) => d.toISOString().slice(0, 10);
  const ende = new Date(Date.UTC(heute.getUTCFullYear(), heute.getUTCMonth(), heute.getUTCDate() - 1));
  const start = new Date(ende); start.setUTCDate(ende.getUTCDate() - Math.max(1, tage) + 1);
  return { startDate: tag(start), endDate: tag(ende) };
}

/** Zeilen der Search Console in lesbare Form (Klickrate in Prozent, Position gerundet). */
export function zeilenAufbereiten(rows) {
  return (rows || []).map((r) => ({
    schluessel: (r.keys || []).join(' · '),
    klicks: r.clicks || 0,
    impressionen: r.impressions || 0,
    klickrate: Math.round((r.ctr || 0) * 1000) / 10,
    position: Math.round((r.position || 0) * 10) / 10,
  }));
}

/** Fehlermeldung von Google mit dem passenden Hinweis, was zu tun ist. */
export function fehlerHinweis(status, text, email = '') {
  let msg = text;
  try { const j = JSON.parse(text); msg = j.error_description || j.error?.message || (typeof j.error === 'string' ? j.error : text); } catch { /* Klartext */ }
  msg = String(msg).slice(0, 400);
  if (/has not been used|is disabled|SERVICE_DISABLED/i.test(msg)) {
    const api = /pagespeed/i.test(msg) ? 'pagespeedonline' : 'searchconsole';
    return `${msg}\n→ Die API ist im Cloud-Projekt noch nicht aktiviert: https://console.cloud.google.com/apis/library/${api}.googleapis.com → „Aktivieren“, dann wenige Minuten warten.`;
  }
  if (status === 403 || /permission|not have sufficient/i.test(msg)) {
    return `${msg}\n→ In der Search Console unter Einstellungen → Nutzer und Berechtigungen das Dienstkonto ${email || '(E-Mail aus der Schlüsseldatei)'} mit der Berechtigung „Uneingeschränkt“ hinzufügen.`;
  }
  if (/invalid[_ ]grant|Invalid JWT|account not found/i.test(msg)) return `${msg}\n→ Schlüssel ungültig oder gelöscht, oder die Uhr des Rechners geht falsch. Neuen JSON-Schlüssel erstellen. ${ANLEITUNG}`;
  if (status === 429) return `${msg}\n→ Kontingent erschöpft. Später erneut versuchen (bei tempo: PAGESPEED_API_KEY setzen).`;
  return msg;
}

async function abruf(url, opt = {}, email = '') {
  const res = await fetch(url, { ...opt, signal: AbortSignal.timeout(opt.zeit || 60000) });
  const text = await res.text();
  if (!res.ok) throw new Hinweis(`Google antwortet ${res.status}: ${fehlerHinweis(res.status, text, email)}`);
  return text ? JSON.parse(text) : {};
}

async function anmelden() {
  const konto = dienstkontoLesen(process.env.GOOGLE_SERVICE_ACCOUNT_JSON);
  const body = new URLSearchParams({ grant_type: 'urn:ietf:params:oauth:grant-type:jwt-bearer', assertion: jwtBauen(konto) });
  const t = await abruf(konto.tokenUrl, { method: 'POST', headers: { 'content-type': 'application/x-www-form-urlencoded' }, body }, konto.email);
  const kopf = { authorization: `Bearer ${t.access_token}`, 'content-type': 'application/json' };
  const api = (url, opt = {}) => abruf(url, { ...opt, headers: { ...kopf, ...(opt.headers || {}) } }, konto.email);
  return { konto, api };
}

async function propertyFinden(api, konto) {
  const liste = (await api(`${GSC}/sites`)).siteEntry || [];
  if (process.env.GSC_PROPERTY) {
    const p = liste.find((x) => x.siteUrl === process.env.GSC_PROPERTY);
    if (!p) throw new Hinweis(`GSC_PROPERTY ${process.env.GSC_PROPERTY} ist für ${konto.email} nicht sichtbar. Sichtbar: ${liste.map((x) => x.siteUrl).join(', ') || 'keine'}.`);
    return { property: p, liste };
  }
  const p = propertyWaehlen(liste);
  if (!p) throw new Hinweis(`Das Dienstkonto ${konto.email} sieht keine bestätigte Property für ${DOMAIN}. In der Search Console unter Einstellungen → Nutzer und Berechtigungen mit „Uneingeschränkt“ hinzufügen. ${ANLEITUNG}`);
  return { property: p, liste };
}

const site = (p) => encodeURIComponent(p.siteUrl);
const ausgabeOrdner = (args) => resolve(args.aus || process.env.GOOGLE_SEO_AUSGABE || join(tmpdir(), 'merzenich-google'));
function speichern(args, name, daten) {
  const ordner = ausgabeOrdner(args);
  mkdirSync(ordner, { recursive: true });
  const datei = join(ordner, `${name}-${new Date().toISOString().slice(0, 10)}.json`);
  writeFileSync(datei, JSON.stringify(daten, null, 2) + '\n');
  return datei;
}
const tabelle = (zeilen, n = 15) => zeilen.slice(0, n).map((z) => `    ${String(z.klicks).padStart(5)} Klicks ${String(z.impressionen).padStart(7)} Impr. ${String(z.klickrate).padStart(5)} % Pos. ${String(z.position).padStart(5)}  ${z.schluessel}`).join('\n');

async function befehlZugang() {
  const { konto, api } = await anmelden();
  const { property, liste } = await propertyFinden(api, konto);
  console.log(`Dienstkonto: ${konto.email}${konto.projekt ? ` (Projekt ${konto.projekt})` : ''}`);
  for (const p of liste) console.log(`  ${p.siteUrl === property.siteUrl ? '→' : ' '} ${p.siteUrl}  ${p.permissionLevel}`);
  if (!['siteOwner', 'siteFullUser'].includes(property.permissionLevel)) console.log('  Hinweis: Zum Einreichen von Sitemaps und für die URL-Prüfung braucht das Konto „Uneingeschränkt“.');
}

async function befehlSitemaps() {
  const { konto, api } = await anmelden();
  const { property } = await propertyFinden(api, konto);
  for (const s of SITEMAPS) {
    const url = START + s;
    await api(`${GSC}/sites/${site(property)}/sitemaps/${encodeURIComponent(url)}`, { method: 'PUT' });
    console.log(`eingereicht: ${url}`);
  }
  const liste = (await api(`${GSC}/sites/${site(property)}/sitemaps`)).sitemap || [];
  console.log(`\nSitemaps in der Search Console (${property.siteUrl}):`);
  for (const s of liste) {
    const inhalt = (s.contents || []).map((c) => `${c.type} ${c.submitted ?? '?'} eingereicht${c.indexed !== undefined ? `, ${c.indexed} indexiert` : ''}`).join('; ');
    console.log(`  ${s.path}\n    zuletzt eingereicht ${s.lastSubmitted || '–'}, zuletzt gelesen ${s.lastDownloaded || 'noch nicht'}${s.isPending ? ', in Bearbeitung' : ''}, Fehler ${s.errors || 0}, Warnungen ${s.warnings || 0}${inhalt ? `\n    ${inhalt}` : ''}`);
  }
}

async function befehlLeistung(args) {
  const { konto, api } = await anmelden();
  const { property } = await propertyFinden(api, konto);
  const tage = Number(args.tage) || 28;
  const zr = zeitraum(tage);
  const bericht = { property: property.siteUrl, ...zr, abgerufen: new Date().toISOString(), suchtypen: {} };
  console.log(`Leistung ${zr.startDate} bis ${zr.endDate} (${property.siteUrl})`);
  for (const t of SUCHTYPEN) {
    const abfrage = (dimensions, rowLimit) => api(`${GSC}/sites/${site(property)}/searchAnalytics/query`, {
      method: 'POST', body: JSON.stringify({ ...zr, type: t.typ, dimensions, rowLimit, dataState: 'all' }),
    });
    const eintrag = {};
    try {
      eintrag.gesamt = zeilenAufbereiten((await abfrage([], 1)).rows)[0] || { klicks: 0, impressionen: 0, klickrate: 0, position: 0 };
      eintrag.tage = zeilenAufbereiten((await abfrage(['date'], 500)).rows);
      eintrag.seiten = zeilenAufbereiten((await abfrage(['page'], 250)).rows);
      if (t.anfragen) eintrag.anfragen = zeilenAufbereiten((await abfrage(['query'], 250)).rows);
    } catch (e) {
      eintrag.fehler = e.message;
    }
    bericht.suchtypen[t.typ] = eintrag;
    const g = eintrag.gesamt;
    console.log(`\n${t.name}: ${eintrag.fehler ? `nicht abrufbar (${eintrag.fehler.split('\n')[0]})` : `${g.klicks} Klicks, ${g.impressionen} Impressionen, Klickrate ${g.klickrate} %, Position ${g.position}`}`);
    if (eintrag.anfragen?.length) console.log(`  Suchanfragen:\n${tabelle(eintrag.anfragen)}`);
    if (eintrag.seiten?.length) console.log(`  Seiten:\n${tabelle(eintrag.seiten, 10)}`);
  }
  console.log(`\nAlles als JSON: ${speichern(args, 'leistung', bericht)}`);
}

async function befehlPruefen(args) {
  const urls = args._.length ? args._ : [START];
  const { konto, api } = await anmelden();
  const { property } = await propertyFinden(api, konto);
  const ergebnisse = [];
  for (const roh of urls) {
    const url = /^https?:\/\//.test(roh) ? roh : START + roh.replace(/^\//, '');
    const r = (await api('https://searchconsole.googleapis.com/v1/urlInspection/index:inspect', {
      method: 'POST', body: JSON.stringify({ inspectionUrl: url, siteUrl: property.siteUrl, languageCode: 'de-DE' }),
    })).inspectionResult || {};
    ergebnisse.push({ url, ...r });
    const i = r.indexStatusResult || {};
    const rich = (r.richResultsResult?.detectedItems || []).map((d) => `${d.richResultType} (${(d.items || []).length})`).join(', ');
    console.log(`\n${url}\n  Ergebnis: ${i.verdict || '?'} · ${i.coverageState || '?'}\n  robots.txt: ${i.robotsTxtState || '?'} · Indexierung: ${i.indexingState || '?'} · Abruf: ${i.pageFetchState || '?'}\n  letzter Crawl: ${i.lastCrawlTime || 'nie'} (${i.crawledAs || '?'})\n  Canonical Google: ${i.googleCanonical || '–'}${i.userCanonical && i.userCanonical !== i.googleCanonical ? `\n  Canonical der Seite: ${i.userCanonical}` : ''}\n  Rich Results: ${rich || 'keine erkannt'}${r.richResultsResult?.verdict === 'FAIL' ? ' (mit Fehlern)' : ''}${r.inspectionResultLink ? `\n  In der Search Console: ${r.inspectionResultLink}` : ''}`);
  }
  console.log(`\nAlles als JSON: ${speichern(args, 'pruefen', ergebnisse)}`);
}

/** PageSpeed-Antwort auf die Werte kürzen, die zählen. */
export function tempoAufbereiten(j) {
  const kat = j.lighthouseResult?.categories || {};
  const audit = j.lighthouseResult?.audits || {};
  const feld = j.loadingExperience?.metrics || {};
  const punkte = (k) => (kat[k]?.score == null ? null : Math.round(kat[k].score * 100));
  const messung = (k) => (feld[k] ? { wert: feld[k].percentile, bewertung: feld[k].category } : null);
  return {
    leistung: punkte('performance'), seo: punkte('seo'), barrierefreiheit: punkte('accessibility'), praxis: punkte('best-practices'),
    labor: { lcp: audit['largest-contentful-paint']?.displayValue || null, cls: audit['cumulative-layout-shift']?.displayValue || null, tbt: audit['total-blocking-time']?.displayValue || null },
    besucher: { lcp_ms: messung('LARGEST_CONTENTFUL_PAINT_MS'), cls_x100: messung('CUMULATIVE_LAYOUT_SHIFT_SCORE'), inp_ms: messung('INTERACTION_TO_NEXT_PAINT'), gesamt: j.loadingExperience?.overall_category || null },
  };
}

async function befehlTempo(args) {
  const url = args._[0] ? (/^https?:\/\//.test(args._[0]) ? args._[0] : START + args._[0].replace(/^\//, '')) : START;
  const key = process.env.PAGESPEED_API_KEY || '';
  if (!key) console.log('Hinweis: PAGESPEED_API_KEY ist nicht gesetzt, Abruf mit kleinem gemeinsamem Kontingent.');
  const bericht = { url, abgerufen: new Date().toISOString() };
  for (const strategie of ['mobile', 'desktop']) {
    const q = new URLSearchParams({ url, strategy: strategie, locale: 'de' });
    for (const c of ['PERFORMANCE', 'SEO', 'ACCESSIBILITY', 'BEST_PRACTICES']) q.append('category', c);
    if (key) q.set('key', key);
    const j = await abruf(`https://www.googleapis.com/pagespeedonline/v5/runPagespeed?${q}`, { zeit: 120000 });
    const t = tempoAufbereiten(j);
    bericht[strategie] = t;
    const b = t.besucher;
    console.log(`\n${strategie === 'mobile' ? 'Mobil' : 'Desktop'} ${url}\n  Leistung ${t.leistung} · SEO ${t.seo} · Barrierefreiheit ${t.barrierefreiheit} · Best Practices ${t.praxis}\n  Labor: LCP ${t.labor.lcp} · CLS ${t.labor.cls} · TBT ${t.labor.tbt}\n  Echte Besucher (28 Tage): ${b.gesamt ? `${b.gesamt}, LCP ${b.lcp_ms?.wert} ms, CLS ${(b.cls_x100?.wert ?? 0) / 100}, INP ${b.inp_ms?.wert ?? '–'} ms` : 'noch zu wenige Besuche für Messwerte'}`);
  }
  console.log(`\nAlles als JSON: ${speichern(args, 'tempo', bericht)}`);
}

function befehlSelbsttest() {
  const { privateKey, publicKey } = generateKeyPairSync('rsa', { modulusLength: 2048, privateKeyEncoding: { type: 'pkcs8', format: 'pem' }, publicKeyEncoding: { type: 'spki', format: 'pem' } });
  const json = JSON.stringify({ type: 'service_account', client_email: 'test@projekt.iam.gserviceaccount.com', private_key: privateKey.replace(/\n/g, '\\n'), project_id: 'projekt' });
  const konto = dienstkontoLesen(json);
  const jwt = jwtBauen(konto, SCOPE, 1_800_000_000);
  const [k, i, s] = jwt.split('.');
  const v = createVerify('RSA-SHA256'); v.update(`${k}.${i}`);
  const inhalt = JSON.parse(Buffer.from(i, 'base64url').toString());
  const ok = v.verify(publicKey, Buffer.from(s, 'base64url')) && inhalt.aud === TOKEN_URL && inhalt.exp - inhalt.iat === 3600 && inhalt.scope === SCOPE;
  console.log(ok ? 'Selbsttest: Anmeldung (JWT RS256) korrekt signiert.' : 'Selbsttest: FEHLER bei der Signatur.');
  return ok;
}

function argumente(argv) {
  const a = { _: [] };
  for (const x of argv) { const m = x.match(/^--([^=]+)(?:=(.*))?$/); if (m) a[m[1]] = m[2] ?? true; else a._.push(x); }
  return a;
}

async function main() {
  const [befehl, ...rest] = process.argv.slice(2);
  const args = argumente(rest);
  const befehle = { zugang: befehlZugang, sitemaps: befehlSitemaps, leistung: befehlLeistung, pruefen: befehlPruefen, tempo: befehlTempo };
  if (befehl === 'selbsttest') process.exit(befehlSelbsttest() ? 0 : 1);
  if (!befehle[befehl]) {
    console.log('Aufruf: node deploy/google-seo.mjs zugang | sitemaps | leistung [--tage=28] | pruefen <url> … | tempo [url] | selbsttest\nOptionen: --aus=<ordner> für die JSON-Dateien. ' + ANLEITUNG);
    process.exit(befehl ? 2 : 0);
  }
  try {
    await befehle[befehl](args);
  } catch (e) {
    if (e instanceof Hinweis) { console.error(e.message); process.exit(2); }
    if (e?.name === 'TimeoutError') { console.error('Google hat nicht rechtzeitig geantwortet. Später erneut versuchen.'); process.exit(3); }
    if (e?.cause?.code) { console.error(`Keine Verbindung zu Google (${e.cause.code}). Hinter einem Proxy: NODE_USE_ENV_PROXY=1 davorsetzen.`); process.exit(3); }
    throw e;
  }
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) await main();
