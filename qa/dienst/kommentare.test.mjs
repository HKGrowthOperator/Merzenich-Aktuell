#!/usr/bin/env node
/**
 * Kommentardienst: Vorab-Freigabe und Freigabe-Mail.
 *
 * Startet deploy/coolify/kommentare/server.mjs auf einem freien Port mit einem
 * temporaeren Datenordner (KOMMENTARE_DATA) und einem kleinen Schein-SMTP-Server
 * (node:net), der Nachrichten annimmt und aufzeichnet. Keine Abhaengigkeiten.
 *
 *   node qa/dienst/kommentare.test.mjs
 *
 * Exit-Code 1 bei jedem Fehler.
 */

import net from 'node:net';
import { spawn } from 'node:child_process';
import { mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { sendeMail, quotedPrintable, kodiereWort } from '../../deploy/coolify/kommentare/smtp.mjs';

const WURZEL = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const SERVER = join(WURZEL, 'deploy', 'coolify', 'kommentare', 'server.mjs');
const TOKEN = 'test-token-' + Math.random().toString(36).slice(2);
const ARTIKEL = '/sport/testspiel-freigabe/';
const ALT_ARTIKEL = '/sport/altbestand/';

let fehlschlaege = 0, bestanden = 0;
function pruefe(bedingung, text, detail) {
  if (bedingung) { bestanden += 1; console.log('  ok   ' + text); }
  else { fehlschlaege += 1; console.log('  FEHL ' + text + (detail !== undefined ? ' -> ' + JSON.stringify(detail).slice(0, 400) : '')); }
}
const warte = (ms) => new Promise((r) => setTimeout(r, ms));

// ------------------------------------------------------------ Schein-SMTP
function scheinSmtp() {
  const mails = [];
  const server = net.createServer((sock) => {
    sock.setEncoding('utf8');
    let puffer = '', inDaten = false, aktuell = null;
    const neu = () => ({ von: '', an: [], daten: '', auth: null });
    aktuell = neu();
    sock.write('220 schein.test ESMTP bereit\r\n');
    sock.on('data', (d) => {
      puffer += d;
      for (;;) {
        if (inDaten) {
          const ende = puffer.indexOf('\r\n.\r\n');
          if (ende === -1) return;
          aktuell.daten = puffer.slice(0, ende); puffer = puffer.slice(ende + 5);
          mails.push(aktuell); aktuell = { ...neu(), auth: aktuell.auth }; inDaten = false;
          sock.write('250 2.0.0 angenommen\r\n');
          continue;
        }
        const i = puffer.indexOf('\r\n'); if (i === -1) return;
        const zeile = puffer.slice(0, i); puffer = puffer.slice(i + 2);
        const befehl = zeile.toUpperCase();
        if (befehl.startsWith('EHLO') || befehl.startsWith('HELO')) sock.write('250-schein.test\r\n250-AUTH PLAIN LOGIN\r\n250 8BITMIME\r\n');
        else if (befehl.startsWith('AUTH PLAIN ')) { aktuell.auth = Buffer.from(zeile.slice(11), 'base64').toString('utf8').split('\u0000'); sock.write('235 2.7.0 angemeldet\r\n'); }
        else if (befehl.startsWith('MAIL FROM:')) { aktuell.von = zeile.slice(10).replace(/[<>]/g, ''); sock.write('250 ok\r\n'); }
        else if (befehl.startsWith('RCPT TO:')) { aktuell.an.push(zeile.slice(8).replace(/[<>]/g, '')); sock.write('250 ok\r\n'); }
        else if (befehl === 'DATA') { inDaten = true; sock.write('354 Daten bitte\r\n'); }
        else if (befehl === 'QUIT') { sock.end('221 tschuess\r\n'); return; }
        else if (befehl === 'RSET' || befehl === 'NOOP') sock.write('250 ok\r\n');
        else sock.write('502 unbekannt\r\n');
      }
    });
    sock.on('error', () => {});
  });
  return new Promise((ok) => server.listen(0, '127.0.0.1', () => ok({ server, port: server.address().port, mails })));
}

// ------------------------------------------------------------ Dienst
function freierPort() {
  return new Promise((ok, nein) => { const s = net.createServer(); s.once('error', nein); s.listen(0, '127.0.0.1', () => { const p = s.address().port; s.close(() => ok(p)); }); });
}
const prozesse = [];
async function starteDienst(env) {
  const port = await freierPort();
  const daten = mkdtempSync(join(tmpdir(), 'kommentare-test-'));
  if (env.fixture) writeFileSync(join(daten, 'kommentare.json'), JSON.stringify(env.fixture));
  const umgebung = { PATH: process.env.PATH, KOMMENTARE_PORT: String(port), KOMMENTARE_DATA: daten, KOMMENTARE_SALZ: 'testsalz', ...env.vars };
  const kind = spawn(process.execPath, [SERVER], { env: umgebung, stdio: ['ignore', 'pipe', 'pipe'] });
  let log = '';
  kind.stdout.on('data', (d) => { log += d; }); kind.stderr.on('data', (d) => { log += d; });
  prozesse.push({ kind, daten });
  const basis = `http://127.0.0.1:${port}/api/kommentare/`;
  for (let i = 0; i < 100; i++) {
    try { const r = await fetch(basis + 'status'); if (r.ok) break; } catch { /* startet noch */ }
    await warte(50);
  }
  return { basis, log: () => log };
}
let ipZaehler = 10;
async function anfrage(basis, pfad, { methode = 'GET', body, token, kopf = {} } = {}) {
  const headers = { 'X-Real-IP': '10.0.0.' + (ipZaehler++ % 250), 'X-Forwarded-For': '10.1.0.' + (ipZaehler % 250), ...kopf };
  if (body !== undefined) headers['Content-Type'] = 'application/json';
  if (token) headers.Authorization = 'Bearer ' + token;
  const r = await fetch(basis + pfad, { method: methode, headers, body: body !== undefined ? JSON.stringify(body) : undefined });
  const text = await r.text();
  let json = null; try { json = JSON.parse(text); } catch { /* HTML oder JS */ }
  return { status: r.status, json, text, kopf: r.headers };
}
const kommentar = (name, text, email) => ({ thema: ARTIKEL, name, text, email, regeln: true, hp: '' });
const oeffentlicheIds = async (basis, thema = ARTIKEL) => (await anfrage(basis, 'liste?thema=' + encodeURIComponent(thema))).json.kommentare.map((k) => k.id);
async function warteAufMails(mails, anzahl, ms = 5000) {
  const bis = Date.now() + ms;
  while (mails.length < anzahl && Date.now() < bis) await warte(50);
  await warte(300); // kurz nachwarten, damit eine ueberzaehlige Mail auffaellt
}
function dekodiereBetreff(kopf) {
  const m = /^Subject: ((?:.*)(?:\r\n[ \t].*)*)/m.exec(kopf); if (!m) return '';
  return m[1].replace(/\r\n[ \t]/g, ' ').replace(/=\?UTF-8\?B\?([^?]+)\?=\s*/gi, (_, b) => Buffer.from(b, 'base64').toString('utf8')).trim();
}
function dekodiereQp(text) {
  const bytes = []; const s = text.replace(/=\r\n/g, '');
  for (let i = 0; i < s.length; i++) {
    if (s[i] === '=' && /^[0-9A-F]{2}$/.test(s.slice(i + 1, i + 3))) { bytes.push(parseInt(s.slice(i + 1, i + 3), 16)); i += 2; }
    else bytes.push(...Buffer.from(s[i], 'utf8'));
  }
  return Buffer.from(bytes).toString('utf8');
}

// ------------------------------------------------------------ Ablauf
async function main() {
  const smtp = await scheinSmtp();

  console.log('\n[1] SMTP-Client direkt: Punkt-Maskierung, Kodierung, AUTH PLAIN');
  const vorher = smtp.mails.length;
  await sendeMail({ an: 'leser@example.org', betreff: 'Prüfung Größe äöü', text: '.eine Zeile mit Punkt\nzweite Zeile\n.' },
    { host: '127.0.0.1', port: smtp.port, secure: false, user: 'nutzer', pass: 'geheim', from: 'Merzenich Aktuell <absender@example.org>' });
  const m0 = smtp.mails[vorher];
  pruefe(m0 && m0.an.length === 1 && m0.an[0] === 'leser@example.org', 'RCPT an die richtige Adresse', m0 && m0.an);
  pruefe(m0 && m0.von === 'absender@example.org', 'MAIL FROM ist die reine Absenderadresse', m0 && m0.von);
  pruefe(m0 && m0.auth && m0.auth[1] === 'nutzer' && m0.auth[2] === 'geheim', 'AUTH PLAIN mit Benutzer und Passwort', m0 && m0.auth);
  pruefe(m0 && /\r\n\.\.eine Zeile/.test(m0.daten), 'Zeile mit fuehrendem Punkt ist verdoppelt (Dot-Stuffing)');
  pruefe(m0 && m0.daten.endsWith('\r\n..') && !/\r\n\.\r\n/.test(m0.daten), 'Zeile aus nur einem Punkt wird zu ".." (kein vorzeitiges Ende)', m0 && m0.daten.slice(-20));
  pruefe(m0 && dekodiereBetreff(m0.daten) === 'Prüfung Größe äöü', 'Betreff als RFC-2047-Wort korrekt', m0 && dekodiereBetreff(m0.daten));
  pruefe(m0 && /^Content-Type: text\/plain; charset=utf-8$/m.test(m0.daten), 'Content-Type text/plain; charset=utf-8');
  pruefe(m0 && /^Date: .+\+0000$/m.test(m0.daten) && /^Message-ID: <[^@\s]+@example\.org>$/m.test(m0.daten), 'Date- und Message-ID-Kopf vorhanden');
  pruefe(quotedPrintable('x'.repeat(200)).split('\r\n').every((z) => z.length <= 76), 'Quoted-Printable-Zeilen hoechstens 76 Zeichen');
  pruefe(kodiereWort('ä'.repeat(60)).split('\r\n ').every((w) => w.length <= 75), 'kodierte Woerter hoechstens 75 Zeichen');

  console.log('\n[2] Dienst mit Token und SMTP');
  const altId = '11111111-2222-4333-8444-555555555555';
  const alt2Id = '11111111-2222-4333-8444-666666666666';
  const fixture = { version: 1, themen: { [ALT_ARTIKEL]: { id: ALT_ARTIKEL, titel: ALT_ARTIKEL, art: 'artikel', url: ALT_ARTIKEL, name: '', erstellt: '2026-09-01T10:00:00.000Z', status: 'offen' } },
    kommentare: [
      { id: altId, thema: ALT_ARTIKEL, name: 'Altleser', text: 'Kommentar aus der Zeit vor der Freigabe, ohne Status.', email: '', erstellt: '2026-09-01T10:00:00.000Z', meldungen: 0, gemeldetVon: [], absender: 'x' },
      { id: alt2Id, thema: ALT_ARTIKEL, name: 'Altleserin', text: 'Kommentar mit altem Status sichtbar.', email: '', erstellt: '2026-09-02T10:00:00.000Z', status: 'sichtbar', meldungen: 0, gemeldetVon: [], absender: 'y' },
    ] };
  const d = await starteDienst({ fixture, vars: { KOMMENTARE_ADMIN_TOKEN: TOKEN, SMTP_HOST: '127.0.0.1', SMTP_PORT: String(smtp.port), SMTP_SECURE: 'false', SMTP_FROM: 'Merzenich Aktuell <redaktion@example.org>', KOMMENTARE_SEITE_URL: 'https://test.example' } });

  const alt = await oeffentlicheIds(d.basis, ALT_ARTIKEL);
  pruefe(alt.includes(altId) && alt.includes(alt2Id), 'alte Kommentare (ohne Status / sichtbar) bleiben oeffentlich', alt);

  const a = await anfrage(d.basis, 'neu', { methode: 'POST', body: kommentar('Anna Test', 'Der wird freigegeben.', 'anna@example.org') });
  const b = await anfrage(d.basis, 'neu', { methode: 'POST', body: kommentar('Bernd Test', 'Der wird abgelehnt.', 'bernd@example.org') });
  const c = await anfrage(d.basis, 'neu', { methode: 'POST', body: kommentar('Clara Test', 'Freigabe ohne E-Mail.', '') });
  pruefe(a.status === 201 && a.json.status === 'wartend' && a.json.benachrichtigung === true, 'neuer Kommentar: 201, Status wartend, Benachrichtigung zugesagt', a.json);
  pruefe(c.status === 201 && c.json.benachrichtigung === false, 'ohne E-Mail keine Benachrichtigung zugesagt', c.json);
  pruefe(!('kommentar' in a.json) && !a.text.includes('anna@example.org'), 'Antwort enthaelt weder Kommentar noch E-Mail');
  const falsch = await anfrage(d.basis, 'neu', { methode: 'POST', body: kommentar('Dora Test', 'Ungueltige Adresse.', 'kein-at-zeichen') });
  pruefe(falsch.status === 400 && falsch.json.feld === 'email', 'ungueltige E-Mail wird abgelehnt', falsch.json);
  const [idA, idB, idC] = [a.json.id, b.json.id, c.json.id];

  let pub = await oeffentlicheIds(d.basis);
  pruefe(![idA, idB, idC].some((id) => pub.includes(id)), 'wartende Kommentare sind nicht in der oeffentlichen Liste', pub);

  const ohne = await anfrage(d.basis, 'admin/liste?status=wartend');
  pruefe(ohne.status === 401, 'Admin-Liste ohne Token -> 401', ohne.status);
  const falschesToken = await anfrage(d.basis, 'admin/liste?status=wartend', { token: TOKEN + 'x' });
  pruefe(falschesToken.status === 401, 'Admin-Liste mit falschem Token -> 401', falschesToken.status);
  const freigabeOhne = await anfrage(d.basis, 'admin/freigabe', { methode: 'POST', body: { freigeben: [idA] } });
  pruefe(freigabeOhne.status === 401, 'Freigabe ohne Token -> 401', freigabeOhne.status);

  const mit = await anfrage(d.basis, 'admin/liste?status=wartend', { token: TOKEN });
  const wartend = mit.json && mit.json.kommentare || [];
  const ka = wartend.find((k) => k.id === idA);
  pruefe(mit.status === 200 && [idA, idB, idC].every((id) => wartend.some((k) => k.id === id)), 'Admin-Liste mit Token enthaelt alle wartenden', mit.status);
  pruefe(!wartend.some((k) => k.id === altId), 'Admin-Liste status=wartend enthaelt keine freigegebenen');
  pruefe(ka && ka.name === 'Anna Test' && ka.text === 'Der wird freigegeben.' && ka.erstellt && ka.hatEmail === true && ka.link === ARTIKEL + '#kommentare' && ka.thema === ARTIKEL, 'Eintrag hat Name, Text, Datum, Artikelbezug, hatEmail', ka);
  pruefe(!mit.text.includes('anna@example.org') && !mit.text.includes('bernd@example.org'), 'Admin-Liste gibt keine E-Mail-Adressen aus');
  const xadmin = await anfrage(d.basis, 'admin/liste?status=wartend', { kopf: { 'X-Admin-Token': TOKEN } });
  pruefe(xadmin.status === 200, 'bisherige Kopfzeile X-Admin-Token funktioniert weiter', xadmin.status);

  const mailsVorher = smtp.mails.length;
  const fg = await anfrage(d.basis, 'admin/freigabe', { methode: 'POST', token: TOKEN, body: { freigeben: [idA, idC], ablehnen: [idB] } });
  pruefe(fg.status === 200 && fg.json.freigegeben === 2 && fg.json.abgelehnt === 1 && fg.json.mails.eingereiht === 1 && fg.json.mails.ohneAdresse === 1, 'Sammelfreigabe: 2 freigegeben, 1 abgelehnt, 1 Mail', fg.json);

  pub = await oeffentlicheIds(d.basis);
  pruefe(pub.includes(idA) && pub.includes(idC), 'freigegebene Kommentare erscheinen oeffentlich', pub);
  pruefe(!pub.includes(idB), 'abgelehnter Kommentar ist nicht oeffentlich', pub);

  await warteAufMails(smtp.mails, mailsVorher + 1);
  const neueMails = smtp.mails.slice(mailsVorher);
  pruefe(neueMails.length === 1, 'genau eine Mail verschickt', neueMails.map((m) => m.an));
  const m1 = neueMails[0];
  pruefe(m1 && m1.an.length === 1 && m1.an[0] === 'anna@example.org', 'Mail geht an die Adresse der Kommentierenden', m1 && m1.an);
  pruefe(m1 && /^To: anna@example\.org$/m.test(m1.daten), 'To-Kopf ist die Adresse der Kommentierenden');
  pruefe(m1 && dekodiereBetreff(m1.daten) === 'Ihr Kommentar auf Merzenich Aktuell ist freigegeben', 'Betreff der Freigabe-Mail', m1 && dekodiereBetreff(m1.daten));
  const koerper = m1 ? dekodiereQp(m1.daten.split('\r\n\r\n').slice(1).join('\r\n\r\n')) : '';
  pruefe(koerper.includes('https://test.example' + ARTIKEL + '#kommentare') && koerper.includes('Anna Test'), 'Mail enthaelt Artikel-Link und Namen', koerper);
  pruefe(!smtp.mails.some((m) => m.an.includes('bernd@example.org')), 'keine Mail an die abgelehnte Person');

  const nochmal = await anfrage(d.basis, 'admin/freigabe', { methode: 'POST', token: TOKEN, body: { freigeben: [idA] } });
  await warte(300);
  pruefe(nochmal.json.unveraendert === 1 && smtp.mails.length === mailsVorher + 1, 'erneute Freigabe verschickt keine zweite Mail', nochmal.json);
  const leer = await anfrage(d.basis, 'admin/liste?status=wartend', { token: TOKEN });
  pruefe(leer.json.kommentare.length === 0, 'danach wartet nichts mehr', leer.json.kommentare.length);
  const doppelt = await anfrage(d.basis, 'admin/freigabe', { methode: 'POST', token: TOKEN, body: { freigeben: [idB], ablehnen: [idB] } });
  pruefe(doppelt.status === 400, 'dieselbe ID in freigeben und ablehnen -> 400', doppelt.status);

  console.log('\n[3] Diskussionsthema wartet ebenfalls');
  const t = await anfrage(d.basis, 'thema', { methode: 'POST', body: { titel: 'Testthema zur Freigabe', name: 'Erik Test', text: 'Eroeffnungsbeitrag.', email: 'erik@example.org', regeln: true, hp: '' } });
  pruefe(t.status === 201 && t.json.status === 'wartend', 'neues Thema: wartend', t.json);
  let themen = (await anfrage(d.basis, 'themen')).json.themen.map((x) => x.id);
  pruefe(!themen.includes(t.json.thema), 'wartendes Thema erscheint nicht in der Themenliste');
  const antwort = await anfrage(d.basis, 'neu', { methode: 'POST', body: { thema: t.json.thema, name: 'Fritz Test', text: 'Antwort auf ein nicht freigegebenes Thema.', regeln: true, hp: '' } });
  pruefe(antwort.status === 400, 'Antwort auf ein nicht freigegebenes Thema wird abgewiesen', antwort.status);
  const mailsVorThema = smtp.mails.length;
  await anfrage(d.basis, 'admin/freigabe', { methode: 'POST', token: TOKEN, body: { freigeben: [t.json.id] } });
  themen = (await anfrage(d.basis, 'themen')).json.themen.map((x) => x.id);
  pruefe(themen.includes(t.json.thema), 'nach Freigabe des Eroeffnungsbeitrags erscheint das Thema');
  await warteAufMails(smtp.mails, mailsVorThema + 1);
  pruefe(smtp.mails.length === mailsVorThema + 1 && smtp.mails[mailsVorThema].an[0] === 'erik@example.org', 'Freigabe-Mail fuer den Eroeffnungsbeitrag');

  console.log('\n[4] Freigabeseite');
  const seite = await anfrage(d.basis, 'moderation');
  const csp = seite.kopf.get('content-security-policy') || '';
  pruefe(seite.status === 200 && /text\/html/.test(seite.kopf.get('content-type')), 'moderation liefert HTML', seite.status);
  pruefe(/noindex/.test(seite.kopf.get('x-robots-tag') || '') && /<meta name="robots" content="noindex/.test(seite.text), 'moderation ist noindex');
  pruefe(!/<script(?![^>]*\bsrc=)[^>]*>/i.test(seite.text) && /<script src="\/api\/kommentare\/moderation\.js"/.test(seite.text), 'kein Inline-Skript, externes moderation.js');
  pruefe(/script-src 'self'/.test(csp) && /style-src 'sha256-/.test(csp) && !/unsafe-inline/.test(csp), 'CSP: script-src self, Stil per Hash', csp);
  pruefe(/Ausgewählte freigeben, abgewählte ablehnen/.test(seite.text) && /Alle auswählen/.test(seite.text) && /Keine auswählen/.test(seite.text), 'Knoepfe der Sammelfreigabe vorhanden');
  const js = await anfrage(d.basis, 'moderation.js');
  pruefe(js.status === 200 && /javascript/.test(js.kopf.get('content-type')) && js.text.includes('sessionStorage') && js.text.includes('checked = true'), 'moderation.js wird ausgeliefert (Vorauswahl, sessionStorage)');

  console.log('\n[5] Ohne SMTP: Freigabe klappt, Log sagt, dass keine Mail ging');
  const o = await starteDienst({ vars: { KOMMENTARE_ADMIN_TOKEN: TOKEN } });
  const g = await anfrage(o.basis, 'neu', { methode: 'POST', body: kommentar('Gerd Test', 'Ohne Mailserver.', 'gerd@example.org') });
  const og = await anfrage(o.basis, 'admin/freigabe', { methode: 'POST', token: TOKEN, body: { freigeben: [g.json.id] } });
  pruefe(og.status === 200 && og.json.freigegeben === 1 && og.json.mails.nichtKonfiguriert === 1 && og.json.mail === false, 'Freigabe ohne SMTP erfolgreich, Mail als nicht konfiguriert gemeldet', og.json);
  pruefe((await oeffentlicheIds(o.basis)).includes(g.json.id), 'Kommentar ist danach oeffentlich');
  await warte(100);
  pruefe(/SMTP nicht eingerichtet, keine Mail verschickt/.test(o.log()), 'Log meldet: keine Mail verschickt', o.log().slice(-400));

  console.log('\n[6] Ohne Admin-Token: 503');
  const n = await starteDienst({ vars: {} });
  const n1 = await anfrage(n.basis, 'admin/liste?status=wartend', { token: 'irgendwas' });
  pruefe(n1.status === 503 && /KOMMENTARE_ADMIN_TOKEN/.test(n1.json.fehler), 'Admin-Endpunkt ohne gesetztes Token -> 503 mit Hinweis', n1.json);
  const n2 = await anfrage(n.basis, 'admin/freigabe', { methode: 'POST', body: { freigeben: [] } });
  pruefe(n2.status === 503, 'Freigabe ohne gesetztes Token -> 503', n2.status);

  smtp.server.close();
}

try { await main(); }
catch (e) { fehlschlaege += 1; console.error('Abbruch:', e); }
finally {
  for (const p of prozesse) { p.kind.kill(); try { rmSync(p.daten, { recursive: true, force: true }); } catch { /* egal */ } }
}
console.log(`\nKommentardienst: ${bestanden} bestanden, ${fehlschlaege} fehlgeschlagen`);
process.exit(fehlschlaege ? 1 : 0);
