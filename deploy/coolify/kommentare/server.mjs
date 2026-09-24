#!/usr/bin/env node
/**
 * Merzenich Aktuell - Kommentare und Diskussion.
 *
 * Kleiner Dienst ohne Abhaengigkeiten, laeuft im Coolify-Container neben
 * nginx (siehe deploy/coolify/50-kommentare.sh). nginx leitet
 * /api/kommentare/ hierher. Ablage: eine JSON-Datei unter /data, das in
 * Coolify als persistenter Speicher eingehaengt sein muss - sonst sind die
 * Kommentare nach jedem Deploy weg (Warnung im Log und unter /status).
 *
 * Endpunkte (alle unter /api/kommentare/, Antworten JSON):
 *   GET  status                       Zustand, Zaehler, ob Speicher persistent
 *   GET  themen                       Diskussionsthemen, neueste Aktivitaet zuerst
 *   GET  liste?thema=<id>             sichtbare Kommentare eines Themas
 *   POST neu     {thema,name,text,email?,regeln,hp}   Kommentar
 *   POST thema   {titel,name,text,regeln,hp}          neues Diskussionsthema
 *   POST melden  {id}                                  Kommentar melden
 *   GET  admin/liste                  alles, auch verborgen   (X-Admin-Token)
 *   POST admin/status {id,status}     sichtbar|verborgen|geloescht (X-Admin-Token)
 *   POST admin/thema-status {id,status}  offen|geschlossen        (X-Admin-Token)
 *
 * Themen-IDs: Artikelpfad (z. B. /sport/sc-merzenich-oberzier-3-2/) oder
 * eine zufaellige ID fuer Diskussionsthemen.
 *
 * Spam-Schutz ohne Konto: Honeypot-Feld, Laengen, hoechstens zwei Links,
 * Regeln muessen bestaetigt sein, je Absender hoechstens 6 Beitraege pro
 * Stunde. Absender werden nur als Tages-Hash der IP gespeichert (fuer das
 * Limit und gegen Mehrfachmeldungen), nie im Klartext. E-Mail ist optional,
 * wird gespeichert, aber nie ausgegeben.
 */

import http from 'node:http';
import { readFileSync, writeFileSync, renameSync, mkdirSync, existsSync, appendFileSync } from 'node:fs';
import { createHash, randomUUID, randomBytes, timingSafeEqual } from 'node:crypto';
import { join } from 'node:path';

const PORT = Number(process.env.KOMMENTARE_PORT || 8787);
const PERSISTENT_DIR = process.env.KOMMENTARE_DATA || '/data';
const persistent = existsSync(PERSISTENT_DIR);
const DATA_DIR = persistent ? PERSISTENT_DIR : '/tmp/merzenich-kommentare';
const DATEI = join(DATA_DIR, 'kommentare.json');
const ADMIN_TOKEN = String(process.env.KOMMENTARE_ADMIN_TOKEN || '');
const SALZ = process.env.KOMMENTARE_SALZ || randomBytes(16).toString('hex');
const WETTER_URL = 'https://api.open-meteo.com/v1/forecast?latitude=50.8317&longitude=6.5361&current=temperature_2m,weather_code&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max&timezone=Europe%2FBerlin&forecast_days=3';

const GRENZEN = { name: [2, 40], text: [3, 2000], titel: [5, 120], proStunde: 6, meldungenBisVerborgen: 3, maxLinks: 2, body: 32 * 1024 };
const STATUS_KOMMENTAR = new Set(['sichtbar', 'verborgen', 'geloescht']);
const STATUS_THEMA = new Set(['offen', 'geschlossen']);
const ARTIKELPFAD = /^\/[a-z0-9-]+\/[a-z0-9-]+\/$/;

// ------------------------------------------------------------------ Ablage
let daten = { version: 1, themen: {}, kommentare: [] };
function laden() {
  try {
    if (existsSync(DATEI)) {
      const d = JSON.parse(readFileSync(DATEI, 'utf8'));
      if (d && typeof d === 'object' && Array.isArray(d.kommentare)) daten = { version: 1, themen: d.themen || {}, kommentare: d.kommentare };
    }
  } catch (e) { console.error('kommentare: Ablage nicht lesbar, starte leer:', e.message); }
}
let schreibTimer = null;
function speichern() {
  if (schreibTimer) return;
  schreibTimer = setTimeout(() => {
    schreibTimer = null;
    try {
      mkdirSync(DATA_DIR, { recursive: true });
      const tmp = DATEI + '.tmp';
      writeFileSync(tmp, JSON.stringify(daten));
      renameSync(tmp, DATEI);
    } catch (e) { console.error('kommentare: Schreiben fehlgeschlagen:', e.message); }
  }, 150);
}

// ------------------------------------------------------------------ Helfer
const jetzt = () => new Date().toISOString();
const saeubere = (s, max) => String(s ?? '').replace(/\r\n?/g, '\n').replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007F]/g, '').replace(/[ \t]+/g, ' ').replace(/\n{3,}/g, '\n\n').trim().slice(0, max);
const zaehleLinks = (s) => (String(s).match(/https?:\/\/|www\./gi) || []).length;
function absenderHash(req) {
  const xff = String(req.headers['x-forwarded-for'] || '').split(',')[0].trim();
  const ip = xff || String(req.headers['x-real-ip'] || req.socket.remoteAddress || '');
  const tag = new Date().toISOString().slice(0, 10);
  return createHash('sha256').update(`${SALZ}|${tag}|${ip}`).digest('hex').slice(0, 24);
}
function oeffentlich(k) { return { id: k.id, thema: k.thema, name: k.name, text: k.text, erstellt: k.erstellt }; }
function themaOeffentlich(t) {
  const sichtbar = daten.kommentare.filter((k) => k.thema === t.id && k.status === 'sichtbar');
  const letzter = sichtbar.length ? sichtbar[sichtbar.length - 1].erstellt : t.erstellt;
  return { id: t.id, titel: t.titel, art: t.art, url: t.url || null, name: t.name, erstellt: t.erstellt, status: t.status, anzahl: sichtbar.length, letzter };
}
function antwort(res, code, body, extra = {}) {
  const text = JSON.stringify(body);
  res.writeHead(code, { 'Content-Type': 'application/json; charset=utf-8', 'Cache-Control': 'no-store', 'X-Content-Type-Options': 'nosniff', ...extra });
  res.end(text);
}
function fehler(res, code, meldung, feld) { antwort(res, code, { ok: false, fehler: meldung, ...(feld ? { feld } : {}) }); }
function leseBody(req) {
  return new Promise((resolve, reject) => {
    let groesse = 0; let teile = [];
    req.on('data', (c) => {
      if (!teile) return;
      groesse += c.length;
      if (groesse > GRENZEN.body) { teile = null; reject({ code: 413, meldung: 'Anfrage zu groß.' }); return; }
      teile.push(c);
    });
    req.on('end', () => { if (!teile) return; try { resolve(teile.length ? JSON.parse(Buffer.concat(teile).toString('utf8')) : {}); } catch { reject({ code: 400, meldung: 'Ungültige Anfrage (kein JSON).' }); } });
    req.on('error', () => reject({ code: 400, meldung: 'Ungültige Anfrage.' }));
  });
}
function istAdmin(req) { return ADMIN_TOKEN !== '' && String(req.headers['x-admin-token'] || '') === ADMIN_TOKEN; }
function limitErreicht(hash) {
  const seit = Date.now() - 3600 * 1000;
  return daten.kommentare.filter((k) => k.absender === hash && Date.parse(k.erstellt) > seit).length >= GRENZEN.proStunde;
}

/** Gemeinsame Pruefung fuer Kommentar und Themenstart. Gibt {name,text} oder wirft {code,meldung,feld}. */
function pruefeBeitrag(b) {
  if (String(b.hp || '').trim() !== '') throw { code: 400, meldung: 'Beitrag abgelehnt.' };
  if (b.regeln !== true && b.regeln !== 'true' && b.regeln !== 1) throw { code: 400, meldung: 'Bitte die Kommentarrichtlinien bestätigen.', feld: 'regeln' };
  const name = saeubere(b.name, GRENZEN.name[1]).replace(/\n/g, ' ');
  if (name.length < GRENZEN.name[0]) throw { code: 400, meldung: 'Bitte einen Namen mit mindestens 2 Zeichen angeben.', feld: 'name' };
  if (zaehleLinks(name) || /@/.test(name)) throw { code: 400, meldung: 'Der Name darf keine Links oder E-Mail-Adressen enthalten.', feld: 'name' };
  const text = saeubere(b.text, GRENZEN.text[1]);
  if (text.length < GRENZEN.text[0]) throw { code: 400, meldung: 'Der Beitrag ist zu kurz.', feld: 'text' };
  if (zaehleLinks(text) > GRENZEN.maxLinks) throw { code: 400, meldung: 'Höchstens zwei Links pro Beitrag.', feld: 'text' };
  const email = saeubere(b.email, 120).replace(/\n/g, '');
  if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) throw { code: 400, meldung: 'Die E-Mail-Adresse sieht nicht gültig aus.', feld: 'email' };
  return { name, text, email };
}

// ------------------------------------------------------------------ Wetter
// Same-origin Proxy fuer Open-Meteo mit 10-Minuten-Cache. Node loest DNS ueber
// /etc/resolv.conf des Containers auf; das ist robuster als ein resolver in nginx.
let wetterCache = { zeit: 0, body: '' };

// ---------------------------------------------------------------- Bildproxy
// /api/bild?u=<https://commons.wikimedia.org/...> laedt Symbolbilder ueber
// unseren Server und cacht sie unter DATA_DIR/bilder. Leser sprechen nie
// direkt mit Wikimedia (keine IP-Weitergabe), deshalb braucht es dafuer keine
// Einwilligung und keinen grauen Platzhalter mehr. Nur Hosts der Allowlist.
const BILD_DIR = join(DATA_DIR, 'bilder');
const BILD_HOSTS = (process.env.BILD_HOSTS || 'commons.wikimedia.org,upload.wikimedia.org').split(',').map((h) => h.trim()).filter(Boolean);
const BILD_MAX = 8 * 1024 * 1024;
const BILD_PLATZHALTER = '<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="1000" viewBox="0 0 1600 1000"><rect width="1600" height="1000" fill="#ecebe7"/><text x="800" y="520" font-family="system-ui,sans-serif" font-size="40" fill="#8a8781" text-anchor="middle">Bild derzeit nicht verfügbar</text></svg>';
const bildLaufend = new Map();
function bildErlaubt(u) {
  let url; try { url = new URL(u); } catch { return false; }
  if (!/^https?:$/.test(url.protocol)) return false;
  return BILD_HOSTS.includes(url.host);
}
async function bildHolen(u) {
  const schluessel = createHash('sha256').update(u).digest('hex');
  const datei = join(BILD_DIR, schluessel + '.bin'), meta = join(BILD_DIR, schluessel + '.json');
  if (existsSync(datei) && existsSync(meta)) {
    try { const m = JSON.parse(readFileSync(meta, 'utf8')); return { typ: m.typ, body: readFileSync(datei) }; } catch { /* neu laden */ }
  }
  if (bildLaufend.has(schluessel)) return bildLaufend.get(schluessel);
  const lauf = (async () => {
    const ac = new AbortController(); const t = setTimeout(() => ac.abort(), 12000);
    try {
      const r = await fetch(u, { redirect: 'follow', signal: ac.signal, headers: { 'User-Agent': 'MerzenichAktuell/1.0 (Bildproxy; https://merzenichaktuell.hk-growthoperator.de)', Accept: 'image/*' } });
      if (!r.ok) throw new Error('Quelle antwortet ' + r.status);
      const typ = String(r.headers.get('content-type') || '').split(';')[0].trim();
      if (!/^image\//.test(typ)) throw new Error('kein Bild: ' + typ);
      const laenge = Number(r.headers.get('content-length') || 0);
      if (laenge > BILD_MAX) throw new Error('zu gross');
      const body = Buffer.from(await r.arrayBuffer());
      if (body.length > BILD_MAX) throw new Error('zu gross');
      mkdirSync(BILD_DIR, { recursive: true });
      const tmp = datei + '.' + process.pid + '.tmp';
      writeFileSync(tmp, body); renameSync(tmp, datei);
      writeFileSync(meta, JSON.stringify({ typ, quelle: u, zeit: new Date().toISOString(), laenge: body.length }));
      return { typ, body };
    } finally { clearTimeout(t); bildLaufend.delete(schluessel); }
  })();
  bildLaufend.set(schluessel, lauf);
  return lauf;
}
async function bildProxy(req, res, url) {
  const u = url.searchParams.get('u') || '';
  if (!bildErlaubt(u)) return fehler(res, 400, 'Bildquelle nicht erlaubt.');
  try {
    const { typ, body } = await bildHolen(u);
    res.writeHead(200, { 'Content-Type': typ, 'Content-Length': body.length, 'Cache-Control': 'public, max-age=31536000, immutable', 'X-Content-Type-Options': 'nosniff', 'Cross-Origin-Resource-Policy': 'same-origin' });
    return res.end(req.method === 'HEAD' ? undefined : body);
  } catch (e) {
    console.error('bild:', u, e.message);
    res.writeHead(502, { 'Content-Type': 'image/svg+xml; charset=utf-8', 'Cache-Control': 'no-store', 'X-Content-Type-Options': 'nosniff' });
    return res.end(BILD_PLATZHALTER);
  }
}


// ------------------------------------------------------------- Formulare
// POST /api/formular (multipart/form-data oder urlencoded) nimmt die sieben
// Redaktionsformulare an (Korrektur, Meldung, Termin, Verein, Betrieb,
// Kontakt, Werbung). Die Seite lief vorher mit data-netlify, was auf nginx
// nur ein 405 ergab. Ablage: DATA_DIR/formulare.jsonl plus Bilddateien unter
// DATA_DIR/formulare/. Optional FORMULAR_WEBHOOK (URL, bekommt JSON je Eingang).
// Erfolg: 303 auf die Danke-Seite (Feld "weiter"). Fehler: 303 zurueck mit
// ?fehler=<code>. Mit Accept: application/json antwortet der Dienst als JSON.
const FORM_DATEI = join(DATA_DIR, 'formulare.jsonl');
const FORM_DIR = join(DATA_DIR, 'formulare');
const FORM_MAX_BODY = 12 * 1024 * 1024, FORM_MAX_DATEI = 6 * 1024 * 1024, FORM_PRO_STUNDE = Number(process.env.FORMULAR_PRO_STUNDE || 5);
const FORMULARE = {
  korrektur: ['url', 'text', 'email', 'einwilligung'],
  meldung: ['art', 'titel', 'text', 'name', 'ortsteil', 'email', 'einwilligung'],
  termin: ['titel', 'beginn', 'ort', 'ortsteil', 'veranstalter', 'kategorie', 'text', 'email', 'einwilligung'],
  verein: ['verein', 'kategorie', 'ortsteil', 'ansprechpartner', 'text', 'email', 'einwilligung'],
  betrieb: ['betrieb', 'branche', 'adresse', 'ortsteil', 'text', 'email', 'einwilligung'],
  kontakt: ['name', 'text', 'email', 'einwilligung'],
  werbung: ['firma', 'name', 'format', 'email', 'einwilligung'],
};
const FORM_WEBHOOK = process.env.FORMULAR_WEBHOOK || '';
const formLetzte = new Map(); // hash -> Zeit (Doppelversand)
const formZaehler = new Map(); // absender -> [Zeiten]
function leseRoh(req, max) {
  return new Promise((resolve, reject) => {
    let groesse = 0; let teile = [];
    req.on('data', (c) => { if (!teile) return; groesse += c.length; if (groesse > max) { teile = null; reject({ code: 413, meldung: 'gross' }); return; } teile.push(c); });
    req.on('end', () => { if (teile) resolve(Buffer.concat(teile)); });
    req.on('error', () => reject({ code: 400, meldung: 'unbekannt' }));
  });
}
function multipart(buf, boundary) {
  const delim = Buffer.from('--' + boundary); const teile = [];
  let start = buf.indexOf(delim);
  while (start !== -1) {
    const ende = buf.indexOf(delim, start + delim.length); if (ende === -1) break;
    const teil = buf.subarray(start + delim.length, ende);
    if (teil.subarray(0, 2).toString() === '--') break;
    const kopfEnde = teil.indexOf('\r\n\r\n');
    if (kopfEnde !== -1) {
      const kopf = teil.subarray(0, kopfEnde).toString('utf8');
      const body = teil.subarray(kopfEnde + 4, Math.max(kopfEnde + 4, teil.length - 2));
      const name = (/name="([^"]*)"/.exec(kopf) || [])[1]; const dateiname = (/filename="([^"]*)"/.exec(kopf) || [])[1];
      const typ = ((/content-type:\s*([^\r\n]+)/i.exec(kopf) || [])[1] || '').trim();
      if (name) teile.push({ name, dateiname, typ, body });
    }
    start = ende;
  }
  return teile;
}
function formAbsender(req) {
  const ip = String(req.headers['x-real-ip'] || String(req.headers['x-forwarded-for'] || '').split(',')[0] || req.socket.remoteAddress || '').trim();
  return createHash('sha256').update((process.env.KOMMENTARE_SALZ || 'merzenich') + ip + new Date().toISOString().slice(0, 10)).digest('hex').slice(0, 16);
}
function formUmleiten(req, res, ziel, json) {
  if (json) return antwort(res, json.ok ? 200 : (json.code || 400), json);
  res.writeHead(303, { Location: ziel, 'Cache-Control': 'no-store' }); return res.end();
}
async function formularAnnehmen(req, res) {
  const willJson = String(req.headers.accept || '').includes('application/json');
  let zurueck = '/'; try { const r = new URL(String(req.headers.referer || ''), 'http://localhost'); if (/^\/[a-z0-9/-]*$/.test(r.pathname)) zurueck = r.pathname; } catch { /* Standard */ }
  const fehlschlag = (code, meldung) => formUmleiten(req, res, `${zurueck}?fehler=${code}#formular`, willJson ? { ok: false, code: code === 'gross' ? 413 : code === 'limit' ? 429 : 400, fehler: meldung, grund: code } : null);
  let buf; try { buf = await leseRoh(req, FORM_MAX_BODY); } catch (e) { return fehlschlag(e.meldung === 'gross' ? 'gross' : 'unbekannt', 'Die Anfrage ist zu groß (Bilder bitte unter 6 MB).'); }
  const ct = String(req.headers['content-type'] || '');
  const felder = {}; const dateien = [];
  if (ct.startsWith('multipart/form-data')) {
    const boundary = (/boundary=("?)([^";]+)\1/.exec(ct) || [])[2]; if (!boundary) return fehlschlag('unbekannt', 'Ungültige Anfrage.');
    for (const t of multipart(buf, boundary)) {
      if (t.dateiname) { if (t.body.length) dateien.push(t); continue; }
      const wert = t.body.toString('utf8').trim();
      felder[t.name] = felder[t.name] ? felder[t.name] + ', ' + wert : wert;
    }
  } else if (ct.startsWith('application/x-www-form-urlencoded')) {
    for (const [k, v] of new URLSearchParams(buf.toString('utf8'))) felder[k] = felder[k] ? felder[k] + ', ' + v.trim() : v.trim();
  } else return fehlschlag('unbekannt', 'Ungültiger Inhaltstyp.');
  const name = String(felder['form-name'] || '').toLowerCase();
  const pflicht = FORMULARE[name]; if (!pflicht) return fehlschlag('unbekannt', 'Unbekanntes Formular.');
  const weiter = /^\/[a-z0-9/-]+\/danke\/$/.test(felder.weiter || '') ? felder.weiter : zurueck;
  // Honeypot: Bots bekommen die Danke-Seite, aber nichts wird gespeichert.
  if (felder['bot-field']) return formUmleiten(req, res, weiter, willJson ? { ok: true, verworfen: true } : null);
  const fehlend = pflicht.filter((f) => !String(felder[f] || '').trim());
  if (fehlend.length) return fehlschlag('felder', 'Bitte alle Pflichtfelder ausfüllen: ' + fehlend.join(', ') + '.');
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(felder.email)) return fehlschlag('email', 'Bitte eine gültige E-Mail-Adresse angeben.');
  if (!/^(ja|on|true|1)$/i.test(felder.einwilligung)) return fehlschlag('einwilligung', 'Bitte der Verarbeitung zustimmen.');
  for (const k of Object.keys(felder)) if (felder[k].length > 5000) felder[k] = felder[k].slice(0, 5000);
  const absender = formAbsender(req);
  const jetzt = Date.now();
  const zeiten = (formZaehler.get(absender) || []).filter((t) => jetzt - t < 3600e3);
  if (zeiten.length >= FORM_PRO_STUNDE) return fehlschlag('limit', 'Zu viele Einsendungen in kurzer Zeit. Bitte später erneut versuchen.');
  const inhalt = Object.fromEntries(Object.entries(felder).filter(([k]) => !['bot-field', 'form-name', 'weiter'].includes(k)));
  const hash = createHash('sha256').update(name + JSON.stringify(inhalt)).digest('hex');
  if (formLetzte.has(hash) && jetzt - formLetzte.get(hash) < 10 * 60e3) return formUmleiten(req, res, weiter, willJson ? { ok: true, doppelt: true } : null);
  const id = new Date(jetzt).toISOString().replace(/[-:.TZ]/g, '').slice(0, 14) + '-' + randomBytes(4).toString('hex');
  const abgelegt = [];
  for (const d of dateien.slice(0, 3)) {
    if (!/^image\//.test(d.typ)) return fehlschlag('datei', 'Als Anhang sind nur Bilder möglich.');
    if (d.body.length > FORM_MAX_DATEI) return fehlschlag('gross', 'Ein Bild ist größer als 6 MB.');
    const sicher = d.dateiname.replace(/[^A-Za-z0-9._-]/g, '_').slice(-80) || 'bild';
    mkdirSync(FORM_DIR, { recursive: true });
    const pfad = join(FORM_DIR, `${id}-${sicher}`); writeFileSync(pfad, d.body);
    abgelegt.push({ feld: d.name, dateiname: d.dateiname, typ: d.typ, groesse: d.body.length, datei: `${id}-${sicher}` });
  }
  const eintrag = { id, formular: name, zeit: new Date(jetzt).toISOString(), absender, felder: inhalt, dateien: abgelegt, persistent };
  try { mkdirSync(DATA_DIR, { recursive: true }); appendFileSync(FORM_DATEI, JSON.stringify(eintrag) + '\n'); }
  catch (e) { console.error('formular: Ablage fehlgeschlagen:', e.message); return fehlschlag('unbekannt', 'Die Einsendung konnte nicht gespeichert werden. Bitte per E-Mail an die Redaktion.'); }
  formLetzte.set(hash, jetzt); zeiten.push(jetzt); formZaehler.set(absender, zeiten);
  if (!persistent) console.error('formular: /data fehlt, Einsendung ' + id + ' liegt nur im Container');
  if (FORM_WEBHOOK) {
    const ac = new AbortController(); const t = setTimeout(() => ac.abort(), 8000);
    fetch(FORM_WEBHOOK, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(eintrag), signal: ac.signal }).catch((e) => console.error('formular: Webhook:', e.message)).finally(() => clearTimeout(t));
  }
  console.log(`formular: ${name} ${id} (${abgelegt.length} Datei(en))`);
  return formUmleiten(req, res, weiter, willJson ? { ok: true, id } : null);
}
function formularAdmin(req, res, url, unterpfad) {
  const token = process.env.KOMMENTARE_ADMIN_TOKEN || '';
  const geliefert = String(req.headers['x-admin-token'] || '');
  if (!token || geliefert.length !== token.length || !timingSafeEqual(Buffer.from(geliefert), Buffer.from(token))) return fehler(res, 401, 'Admin-Token fehlt oder ist falsch.');
  if (unterpfad === 'admin/liste') {
    const limit = Math.min(500, Math.max(1, Number(url.searchParams.get('limit') || 100)));
    let zeilen = []; try { zeilen = existsSync(FORM_DATEI) ? readFileSync(FORM_DATEI, 'utf8').trim().split('\n').filter(Boolean) : []; } catch { /* leer */ }
    const eintraege = zeilen.slice(-limit).reverse().map((z) => { try { return JSON.parse(z); } catch { return null; } }).filter(Boolean);
    return antwort(res, 200, { ok: true, anzahl: zeilen.length, eintraege });
  }
  if (unterpfad === 'admin/datei') {
    const datei = String(url.searchParams.get('datei') || '');
    if (!/^[0-9]{14}-[0-9a-f]{8}-[A-Za-z0-9._-]+$/.test(datei) || !existsSync(join(FORM_DIR, datei))) return fehler(res, 404, 'Datei nicht gefunden.');
    const body = readFileSync(join(FORM_DIR, datei));
    res.writeHead(200, { 'Content-Type': 'application/octet-stream', 'Content-Length': body.length, 'Content-Disposition': `attachment; filename="${datei}"`, 'Cache-Control': 'no-store' }); return res.end(body);
  }
  return fehler(res, 404, 'Unbekannter Pfad.');
}

async function wetter() {
  if (wetterCache.body && Date.now() - wetterCache.zeit < 10 * 60 * 1000) return wetterCache.body;
  const r = await fetch(WETTER_URL, { signal: AbortSignal.timeout(6000) });
  if (!r.ok) throw new Error('open-meteo ' + r.status);
  const body = await r.text(); JSON.parse(body);
  wetterCache = { zeit: Date.now(), body };
  return body;
}

// ------------------------------------------------------------------ Server
laden();
const server = http.createServer(async (req, res) => {
  const url = new URL(req.url, 'http://localhost');
  const bereich = (url.pathname.match(/^\/api\/([a-z]+)/) || [])[1] || '';
  const pfad = url.pathname.replace(/^\/api\/[a-z]+\/?/, '').replace(/\/+$/, '');
  try {
    if (bereich === 'bild' && (req.method === 'GET' || req.method === 'HEAD')) return bildProxy(req, res, url);
    if (bereich === 'formular') {
      const unter = url.pathname.replace(/^\/api\/formular\/?/, '');
      if (!unter && req.method === 'POST') return formularAnnehmen(req, res);
      if (unter.startsWith('admin/') && req.method === 'GET') return formularAdmin(req, res, url, unter);
      return fehler(res, 405, 'Formulare nur per POST.');
    }
    if (bereich === 'weather' || (bereich === 'wetter')) {
      try { const body = await wetter(); res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8', 'Cache-Control': 'public, max-age=600, stale-while-revalidate=1800', 'X-Content-Type-Options': 'nosniff' }); return res.end(body); }
      catch (e) { if (wetterCache.body) { res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8', 'Cache-Control': 'no-store' }); return res.end(wetterCache.body); } console.error('wetter:', e.message); return fehler(res, 502, 'Wetterdienst nicht erreichbar.'); }
    }
    if (bereich !== 'kommentare') return fehler(res, 404, 'Unbekannter Endpunkt.');
    if (req.method === 'GET' && pfad === 'status') {
      return antwort(res, 200, { ok: true, persistent, speicher: DATA_DIR, themen: Object.keys(daten.themen).length, kommentare: daten.kommentare.filter((k) => k.status === 'sichtbar').length, hinweis: persistent ? null : 'KEIN persistenter Speicher unter /data - Kommentare gehen beim naechsten Deploy verloren.' });
    }
    if (req.method === 'GET' && pfad === 'themen') {
      const liste = Object.values(daten.themen).filter((t) => t.art === 'diskussion').map(themaOeffentlich).sort((a, b) => b.letzter.localeCompare(a.letzter));
      return antwort(res, 200, { ok: true, themen: liste });
    }
    if (req.method === 'GET' && pfad === 'liste') {
      const id = String(url.searchParams.get('thema') || '');
      if (!id) return fehler(res, 400, 'thema fehlt.');
      const thema = daten.themen[id] || null;
      const kommentare = daten.kommentare.filter((k) => k.thema === id && k.status === 'sichtbar').map(oeffentlich);
      return antwort(res, 200, { ok: true, thema: thema ? themaOeffentlich(thema) : null, kommentare });
    }
    if (req.method === 'POST' && pfad === 'neu') {
      const b = await leseBody(req);
      const id = String(b.thema || '');
      const thema = daten.themen[id];
      if (!thema && !ARTIKELPFAD.test(id)) return fehler(res, 400, 'Unbekanntes Thema.');
      if (thema && thema.status === 'geschlossen') return fehler(res, 403, 'Dieses Thema ist geschlossen.');
      const { name, text, email } = pruefeBeitrag(b);
      const hash = absenderHash(req);
      if (limitErreicht(hash)) return fehler(res, 429, 'Zu viele Beiträge in kurzer Zeit. Bitte später noch einmal.');
      if (!thema) daten.themen[id] = { id, titel: id, art: 'artikel', url: id, name: '', erstellt: jetzt(), status: 'offen' };
      const k = { id: randomUUID(), thema: id, name, text, email, erstellt: jetzt(), status: 'sichtbar', meldungen: 0, gemeldetVon: [], absender: hash };
      daten.kommentare.push(k); speichern();
      console.log(`kommentar neu ${id} von ${name}`);
      return antwort(res, 201, { ok: true, kommentar: oeffentlich(k) });
    }
    if (req.method === 'POST' && pfad === 'thema') {
      const b = await leseBody(req);
      const titel = saeubere(b.titel, GRENZEN.titel[1]).replace(/\n/g, ' ');
      if (titel.length < GRENZEN.titel[0]) throw { code: 400, meldung: 'Bitte einen Titel mit mindestens 5 Zeichen angeben.', feld: 'titel' };
      if (zaehleLinks(titel)) throw { code: 400, meldung: 'Der Titel darf keine Links enthalten.', feld: 'titel' };
      const { name, text, email } = pruefeBeitrag(b);
      const hash = absenderHash(req);
      if (limitErreicht(hash)) return fehler(res, 429, 'Zu viele Beiträge in kurzer Zeit. Bitte später noch einmal.');
      const t = { id: randomUUID(), titel, art: 'diskussion', url: null, name, erstellt: jetzt(), status: 'offen' };
      daten.themen[t.id] = t;
      const k = { id: randomUUID(), thema: t.id, name, text, email, erstellt: t.erstellt, status: 'sichtbar', meldungen: 0, gemeldetVon: [], absender: hash };
      daten.kommentare.push(k); speichern();
      console.log(`thema neu "${titel}" von ${name}`);
      return antwort(res, 201, { ok: true, thema: themaOeffentlich(t), kommentar: oeffentlich(k) });
    }
    if (req.method === 'POST' && pfad === 'melden') {
      const b = await leseBody(req);
      const k = daten.kommentare.find((x) => x.id === String(b.id || ''));
      if (!k || k.status !== 'sichtbar') return fehler(res, 404, 'Kommentar nicht gefunden.');
      const hash = absenderHash(req);
      if (!k.gemeldetVon.includes(hash)) { k.gemeldetVon.push(hash); k.meldungen += 1; }
      if (k.meldungen >= GRENZEN.meldungenBisVerborgen) k.status = 'verborgen';
      speichern();
      console.log(`kommentar gemeldet ${k.id} (${k.meldungen})`);
      return antwort(res, 200, { ok: true, verborgen: k.status === 'verborgen' });
    }
    if (pfad.startsWith('admin/')) {
      if (!istAdmin(req)) return fehler(res, ADMIN_TOKEN ? 403 : 503, ADMIN_TOKEN ? 'Kein Zugriff.' : 'Kein Admin-Token gesetzt (KOMMENTARE_ADMIN_TOKEN).');
      if (req.method === 'GET' && pfad === 'admin/liste') {
        return antwort(res, 200, { ok: true, themen: Object.values(daten.themen).map(themaOeffentlich), kommentare: daten.kommentare.map(({ email, absender, gemeldetVon, ...rest }) => ({ ...rest, meldungen: gemeldetVon.length })) });
      }
      if (req.method === 'POST' && pfad === 'admin/status') {
        const b = await leseBody(req);
        const k = daten.kommentare.find((x) => x.id === String(b.id || ''));
        if (!k) return fehler(res, 404, 'Kommentar nicht gefunden.');
        if (!STATUS_KOMMENTAR.has(b.status)) return fehler(res, 400, 'status muss sichtbar, verborgen oder geloescht sein.');
        k.status = b.status; if (b.status === 'geloescht') { k.text = ''; k.email = ''; }
        speichern(); return antwort(res, 200, { ok: true });
      }
      if (req.method === 'POST' && pfad === 'admin/thema-status') {
        const b = await leseBody(req);
        const t = daten.themen[String(b.id || '')];
        if (!t) return fehler(res, 404, 'Thema nicht gefunden.');
        if (!STATUS_THEMA.has(b.status)) return fehler(res, 400, 'status muss offen oder geschlossen sein.');
        t.status = b.status; speichern(); return antwort(res, 200, { ok: true });
      }
    }
    return fehler(res, 404, 'Unbekannter Endpunkt.');
  } catch (e) {
    // Erwartbare Client-Fehler tragen code+meldung und landen nicht im Log.
    if (e && e.code && e.meldung) { fehler(res, e.code, e.meldung, e.feld); if (e.code === 413) req.resume(); return; }
    console.error('kommentare:', e);
    return fehler(res, 500, 'Interner Fehler.');
  }
});

server.listen(PORT, '127.0.0.1', () => {
  console.log(`kommentare: laeuft auf 127.0.0.1:${PORT}, Ablage ${DATEI}${persistent ? '' : ' (NICHT persistent - in Coolify Persistent Storage /data anlegen)'}, Admin-Token ${ADMIN_TOKEN ? 'gesetzt' : 'NICHT gesetzt'}`);
});
