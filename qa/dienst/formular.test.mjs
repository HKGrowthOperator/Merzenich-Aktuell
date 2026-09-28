#!/usr/bin/env node
/**
 * Formulardienst POST /api/formular (Audit 28.09.2026, Punkt 40): jeder
 * Formulartyp im Testmodus, ohne Versand nach aussen (kein Webhook, kein SMTP).
 *
 * 1. Statisch: jedes <form action="/api/formular"> der ausgelieferten Seite
 *    nennt einen Formulartyp, den der Dienst kennt, fuehrt jedes Pflichtfeld
 *    des Dienstes als benanntes Feld, hat Honeypot und Einwilligung, und seine
 *    Danke-Seite existiert mit noindex.
 * 2. Dienst (deploy/coolify/kommentare/server.mjs mit temporaerem Datenordner):
 *    je Typ gueltige Einsendung (gespeichert), fehlendes Pflichtfeld, Honeypot
 *    (nicht gespeichert), Doppelsendung (einmal gespeichert), ungueltige
 *    E-Mail, fehlende Einwilligung, Bild ueber 6 MB, falscher Dateityp,
 *    Weiterleitung auf die Danke-Seite, Rate-Limit.
 *
 *   node qa/dienst/formular.test.mjs      Exit-Code 1 bei jedem Fehler.
 */
import net from 'node:net';
import { spawn } from 'node:child_process';
import { mkdtempSync, readFileSync, readdirSync, statSync, existsSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const WURZEL = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const SITE = join(WURZEL, 'chatgpt-site');
const SERVER = join(WURZEL, 'deploy', 'coolify', 'kommentare', 'server.mjs');
let fehl = 0, ok = 0;
const pruefe = (b, t, d) => { if (b) { ok++; console.log('  ok   ' + t); } else { fehl++; console.log('  FEHL ' + t + (d !== undefined ? ' -> ' + JSON.stringify(d).slice(0, 300) : '')); } };
const warte = (ms) => new Promise((r) => setTimeout(r, ms));

// Pflichtfelder des Dienstes direkt aus dem Quelltext (eine Quelle).
const quelltext = readFileSync(SERVER, 'utf8');
const FORMULARE = Object.fromEntries([...quelltext.slice(quelltext.indexOf('const FORMULARE = {'), quelltext.indexOf('};', quelltext.indexOf('const FORMULARE = {'))).matchAll(/^\s+(\w+): \[([^\]]*)\]/gm)].map((m) => [m[1], m[2].split(',').map((s) => s.trim().replace(/'/g, '')).filter(Boolean)]));

// ------------------------------------------------------ 1. Formulare der Seite
console.log('Formulare der ausgelieferten Seite');
const formulare = [];
(function lauf(d) { for (const e of readdirSync(d)) { const p = join(d, e); if (statSync(p).isDirectory()) { if (!['admin', 'redaktion', 'redaktionshandbuch'].includes(e)) lauf(p); } else if (e.endsWith('.html')) { const h = readFileSync(p, 'utf8'); for (const m of h.matchAll(/<form\b[^>]*action="\/api\/formular"[^>]*>[\s\S]*?<\/form>/g)) formulare.push({ seite: p.slice(SITE.length), html: m[0] }); } } })(SITE);
pruefe(formulare.length >= 7, `${formulare.length} Formulare mit action=/api/formular gefunden`);
const typen = new Set();
for (const f of formulare) {
  const name = (/name="form-name" value="([^"]+)"/.exec(f.html) || [])[1];
  const weiter = (/name="weiter" value="([^"]+)"/.exec(f.html) || [])[1];
  typen.add(name);
  pruefe(!!FORMULARE[name], `${f.seite}: Formulartyp "${name}" ist dem Dienst bekannt`);
  if (!FORMULARE[name]) continue;
  const felder = new Set([...f.html.matchAll(/\bname="([^"]+)"/g)].map((m) => m[1]));
  const fehlend = FORMULARE[name].filter((x) => !felder.has(x));
  pruefe(!fehlend.length, `${f.seite}: alle Pflichtfelder des Dienstes vorhanden`, fehlend);
  pruefe(felder.has('bot-field'), `${f.seite}: Honeypot bot-field vorhanden`);
  pruefe(/method="POST"/i.test(f.html), `${f.seite}: method=POST`);
  if (/type="file"/.test(f.html)) pruefe(/enctype="multipart\/form-data"/.test(f.html), `${f.seite}: Datei-Upload mit multipart`);
  if (weiter) {
    const danke = join(SITE, weiter, 'index.html');
    pruefe(existsSync(danke), `${f.seite}: Danke-Seite ${weiter} existiert`);
    if (existsSync(danke)) pruefe(/<meta name="robots" content="[^"]*noindex/.test(readFileSync(danke, 'utf8')), `${weiter}: noindex`);
  }
}

// ------------------------------------------------------------ 2. Dienst
console.log('Dienst POST /api/formular');
const freierPort = () => new Promise((r, n) => { const s = net.createServer(); s.once('error', n); s.listen(0, '127.0.0.1', () => { const p = s.address().port; s.close(() => r(p)); }); });
const port = await freierPort();
const daten = mkdtempSync(join(tmpdir(), 'formular-test-'));
const kind = spawn(process.execPath, [SERVER], { env: { PATH: process.env.PATH, KOMMENTARE_PORT: String(port), KOMMENTARE_DATA: daten, KOMMENTARE_SALZ: 'testsalz', FORMULAR_PRO_STUNDE: '5' }, stdio: ['ignore', 'pipe', 'pipe'] });
let log = ''; kind.stdout.on('data', (d) => { log += d; }); kind.stderr.on('data', (d) => { log += d; });
const basis = `http://127.0.0.1:${port}`;
for (let i = 0; i < 100; i++) { try { if ((await fetch(basis + '/api/kommentare/status')).ok) break; } catch { /* startet */ } await warte(50); }

let ip = 1;
const beispiel = (name) => Object.fromEntries(FORMULARE[name].map((f) => [f, f === 'email' ? 'test@example.org' : f === 'einwilligung' ? 'ja' : f === 'beginn' ? '2026-10-10T18:00' : f === 'url' ? '/nachrichten/' : `Test ${f} ${name}`]));
async function sende(felder, { dateien = [], json = true, neueIp = true, referer = '/kontakt/' } = {}) {
  const fd = new FormData();
  for (const [k, v] of Object.entries(felder)) fd.append(k, v);
  for (const d of dateien) fd.append('bild', new Blob([d.inhalt], { type: d.typ }), d.name);
  const headers = { 'X-Real-IP': `10.9.0.${neueIp ? ip++ : ip}`, Referer: 'https://test.example' + referer };
  if (json) headers.Accept = 'application/json';
  const r = await fetch(basis + '/api/formular', { method: 'POST', body: fd, headers, redirect: 'manual' });
  let j = null; try { j = await r.json(); } catch { /* Weiterleitung */ }
  return { status: r.status, json: j, ort: r.headers.get('location') };
}
const gespeichert = () => (existsSync(join(daten, 'formulare.jsonl')) ? readFileSync(join(daten, 'formulare.jsonl'), 'utf8').trim().split('\n').filter(Boolean).map((z) => JSON.parse(z)) : []);

for (const name of Object.keys(FORMULARE)) {
  const vorher = gespeichert().length;
  const g = await sende({ 'form-name': name, ...beispiel(name) });
  pruefe(g.status === 200 && g.json && g.json.ok && g.json.id, `${name}: gueltige Einsendung angenommen`, g);
  pruefe(gespeichert().length === vorher + 1, `${name}: in formulare.jsonl gespeichert`);
  const erste = FORMULARE[name][0];
  const ohne = beispiel(name); delete ohne[erste];
  const f = await sende({ 'form-name': name, ...ohne, text: 'anders ' + name });
  pruefe(f.status === 400 && f.json && f.json.grund === 'felder', `${name}: fehlendes Pflichtfeld ${erste} abgelehnt`, f);
}
{
  const n = gespeichert().length;
  const h = await sende({ 'form-name': 'kontakt', ...beispiel('kontakt'), 'bot-field': 'http://spam.example' });
  pruefe(h.status === 200 && h.json && h.json.verworfen, 'Honeypot: Bot bekommt Erfolg', h);
  pruefe(gespeichert().length === n, 'Honeypot: nichts gespeichert');
  const d1 = await sende({ 'form-name': 'kontakt', ...beispiel('kontakt'), text: 'Doppelsendung' });
  const d2 = await sende({ 'form-name': 'kontakt', ...beispiel('kontakt'), text: 'Doppelsendung' });
  pruefe(d1.json && d1.json.ok && d2.json && d2.json.doppelt, 'Doppelsendung wird erkannt', [d1, d2]);
  pruefe(gespeichert().filter((e) => e.felder.text === 'Doppelsendung').length === 1, 'Doppelsendung einmal gespeichert');
  const e = await sende({ 'form-name': 'kontakt', ...beispiel('kontakt'), email: 'keine-adresse' });
  pruefe(e.status === 400 && e.json.grund === 'email', 'ungueltige E-Mail abgelehnt', e);
  const w = await sende({ 'form-name': 'kontakt', ...beispiel('kontakt'), einwilligung: 'nein', text: 'ohne Einwilligung' });
  pruefe(w.status === 400 && w.json.grund === 'einwilligung', 'fehlende Einwilligung abgelehnt', w);
  const u = await sende({ 'form-name': 'unbekannt', ...beispiel('kontakt') });
  pruefe(u.status === 400, 'unbekannter Formulartyp abgelehnt', u);
  const gross = await sende({ 'form-name': 'meldung', ...beispiel('meldung'), text: 'grosses Bild' }, { dateien: [{ name: 'gross.jpg', typ: 'image/jpeg', inhalt: new Uint8Array(6 * 1024 * 1024 + 10) }] });
  pruefe(gross.status === 413 && gross.json.grund === 'gross', 'Bild ueber 6 MB abgelehnt', gross);
  const typ = await sende({ 'form-name': 'meldung', ...beispiel('meldung'), text: 'falscher Typ' }, { dateien: [{ name: 'skript.html', typ: 'text/html', inhalt: new TextEncoder().encode('<script>alert(1)</script>') }] });
  pruefe(typ.status === 400 && typ.json.grund === 'datei', 'Anhang, der kein Bild ist, abgelehnt', typ);
  pruefe(!existsSync(join(daten, 'formulare')) || !readdirSync(join(daten, 'formulare')).some((x) => /skript/.test(x)), 'abgelehnter Anhang nicht abgelegt');
  const bild = await sende({ 'form-name': 'meldung', ...beispiel('meldung'), text: 'mit Bild' }, { dateien: [{ name: 'foto.jpg', typ: 'image/jpeg', inhalt: new Uint8Array([0xff, 0xd8, 0xff, 0xd9]) }] });
  pruefe(bild.json && bild.json.ok && gespeichert().some((x) => x.id === bild.json.id && x.dateien.length === 1), 'Bild angenommen und abgelegt', bild);
  const umleitung = await sende({ 'form-name': 'meldung', ...beispiel('meldung'), text: 'Umleitung', weiter: '/meldung-senden/danke/' }, { json: false });
  pruefe(umleitung.status === 303 && umleitung.ort === '/meldung-senden/danke/', 'ohne JavaScript: 303 auf die Danke-Seite', umleitung);
  pruefe(!/[?&](email|name|text)=/.test(umleitung.ort || ''), 'Danke-URL enthaelt keine Formulardaten');
  const fremd = await sende({ 'form-name': 'meldung', ...beispiel('meldung'), text: 'fremdes Ziel', weiter: 'https://boese.example/' }, { json: false });
  pruefe(fremd.status === 303 && !/boese/.test(fremd.ort || ''), 'fremdes Weiterleitungsziel wird ignoriert', fremd);
  // Rate-Limit: fuenf Einsendungen je Stunde und Absender (FORMULAR_PRO_STUNDE=5).
  ip += 1;
  const antworten = [];
  for (let i = 0; i < 6; i++) antworten.push(await sende({ 'form-name': 'kontakt', ...beispiel('kontakt'), text: `Limit ${i}` }, { neueIp: false }));
  pruefe(antworten.slice(0, 5).every((a) => a.json && a.json.ok) && antworten[5].status === 429, 'Rate-Limit greift bei der sechsten Einsendung', antworten.map((a) => a.status));
}

kind.kill();
rmSync(daten, { recursive: true, force: true });
console.log(`\nFormulare: ${ok} bestanden, ${fehl} fehlgeschlagen.`);
if (fehl) { console.log(log.slice(-2000)); process.exit(1); }
