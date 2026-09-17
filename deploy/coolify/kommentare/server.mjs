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
import { readFileSync, writeFileSync, renameSync, mkdirSync, existsSync } from 'node:fs';
import { createHash, randomUUID, randomBytes } from 'node:crypto';
import { join } from 'node:path';

const PORT = Number(process.env.KOMMENTARE_PORT || 8787);
const PERSISTENT_DIR = process.env.KOMMENTARE_DATA || '/data';
const persistent = existsSync(PERSISTENT_DIR);
const DATA_DIR = persistent ? PERSISTENT_DIR : '/tmp/merzenich-kommentare';
const DATEI = join(DATA_DIR, 'kommentare.json');
const ADMIN_TOKEN = String(process.env.KOMMENTARE_ADMIN_TOKEN || '');
const SALZ = process.env.KOMMENTARE_SALZ || randomBytes(16).toString('hex');

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

// ------------------------------------------------------------------ Server
laden();
const server = http.createServer(async (req, res) => {
  const url = new URL(req.url, 'http://localhost');
  const pfad = url.pathname.replace(/^\/api\/kommentare\/?/, '').replace(/\/+$/, '');
  try {
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
