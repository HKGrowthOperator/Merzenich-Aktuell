/**
 * Merzenich Aktuell - kleiner SMTP-Client ohne Abhaengigkeiten.
 *
 * Wird vom Kommentar-Dienst (server.mjs) fuer die Freigabe-Mail an
 * Kommentierende und den Hinweis an die Redaktion benutzt. Nur node:net und
 * node:tls, kein npm-Paket.
 *
 * Umgebungsvariablen:
 *   SMTP_HOST    Mailserver (ohne: kein Versand, sendeMail wirft NICHT_KONFIGURIERT)
 *   SMTP_PORT    Standard 587 (STARTTLS); 465 = TLS von Anfang an
 *   SMTP_SECURE  "true" = TLS von Anfang an, "false" = erst Klartext und dann
 *                STARTTLS, falls der Server es anbietet. Ohne Angabe: true bei
 *                Port 465, sonst false.
 *   SMTP_USER / SMTP_PASS   Anmeldung (AUTH PLAIN, sonst AUTH LOGIN)
 *   SMTP_FROM    Absender, z. B. "Merzenich Aktuell <redaktion@example.org>"
 *
 * Zugangsdaten werden nie unverschluesselt verschickt: bietet der Server kein
 * STARTTLS an, bricht der Versand ab (Ausnahme: Server auf localhost).
 */

import net from 'node:net';
import tls from 'node:tls';
import { hostname } from 'node:os';
import { randomUUID } from 'node:crypto';

const ZEITLIMIT = 20000;
const ADRESSE = /^[^\s@<>()",;:\\[\]]+@[^\s@<>()",;:\\[\]]+\.[^\s@<>()",;:\\[\]]+$/;

/** Liest die Konfiguration aus der Umgebung (bei jedem Aufruf, damit Aenderungen ohne Neustart greifen). */
export function smtpAusUmgebung(env = process.env) {
  const host = String(env.SMTP_HOST || '').trim();
  const port = Number(env.SMTP_PORT || 0) || 587;
  const s = String(env.SMTP_SECURE ?? '').trim().toLowerCase();
  const secure = s === '' ? port === 465 : ['1', 'true', 'ja', 'yes', 'on'].includes(s);
  return { host, port, secure, user: String(env.SMTP_USER || ''), pass: String(env.SMTP_PASS || ''), from: String(env.SMTP_FROM || '').trim() };
}

/** true, wenn Host und Absender gesetzt sind und der Absender eine Adresse enthaelt. */
export function smtpKonfiguriert(konfig = smtpAusUmgebung()) {
  return Boolean(konfig.host && adresseAus(konfig.from));
}

/** Zieht die reine Adresse aus "Name <a@b.de>" oder "a@b.de". */
export function adresseAus(wert) {
  const s = String(wert || '').replace(/[\r\n]/g, '').trim();
  const m = /<([^<>]+)>\s*$/.exec(s);
  const a = (m ? m[1] : s).trim();
  return ADRESSE.test(a) ? a : '';
}

const ohneZeilenumbruch = (s) => String(s ?? '').replace(/[\r\n]+/g, ' ').trim();

/** RFC 2047: Nicht-ASCII als =?UTF-8?B?...?=, in Stuecken von hoechstens 75 Zeichen, ohne Mehrbyte-Zeichen zu zerteilen. */
export function kodiereWort(text) {
  const s = ohneZeilenumbruch(text);
  if (/^[\x20-\x7e]*$/.test(s)) return s;
  const stuecke = []; let aktuell = '';
  for (const zeichen of s) {
    if (Buffer.byteLength(aktuell + zeichen, 'utf8') > 45) { stuecke.push(aktuell); aktuell = ''; }
    aktuell += zeichen;
  }
  if (aktuell) stuecke.push(aktuell);
  return stuecke.map((t) => `=?UTF-8?B?${Buffer.from(t, 'utf8').toString('base64')}?=`).join('\r\n ');
}

/** "Name <adresse>" mit kodiertem Namen, sonst nur die Adresse. */
function kopfAdresse(wert) {
  const s = ohneZeilenumbruch(wert);
  const adresse = adresseAus(s);
  const name = s.includes('<') ? s.slice(0, s.lastIndexOf('<')).trim().replace(/^"|"$/g, '') : '';
  if (!name) return adresse;
  const kodiert = kodiereWort(name);
  return `${kodiert === name ? `"${name.replace(/["\\]/g, '')}"` : kodiert} <${adresse}>`;
}

/** Quoted-Printable (RFC 2045) fuer den UTF-8-Text, Zeilen hoechstens 76 Zeichen. */
export function quotedPrintable(text) {
  const zeilen = String(text ?? '').replace(/\r\n?/g, '\n').split('\n');
  return zeilen.map((zeile) => {
    let aus = '';
    const bytes = Buffer.from(zeile, 'utf8');
    for (let i = 0; i < bytes.length; i++) {
      const b = bytes[i];
      const letztes = i === bytes.length - 1;
      const roh = (b >= 33 && b <= 126 && b !== 61) || ((b === 32 || b === 9) && !letztes);
      aus += roh ? String.fromCharCode(b) : '=' + b.toString(16).toUpperCase().padStart(2, '0');
    }
    // Weiche Umbrueche, ohne eine =XX-Folge zu zerschneiden.
    const teile = [];
    while (aus.length > 76) {
      let schnitt = 75;
      const eq = aus.lastIndexOf('=', schnitt - 1);
      if (eq > schnitt - 3) schnitt = eq;
      teile.push(aus.slice(0, schnitt) + '=');
      aus = aus.slice(schnitt);
    }
    teile.push(aus);
    return teile.join('\r\n');
  }).join('\r\n');
}

/** Punkt-Maskierung (RFC 5321, 4.5.2): Zeilen, die mit "." beginnen, bekommen einen zweiten Punkt. */
export function punktMaskieren(daten) {
  return String(daten).replace(/\r?\n/g, '\r\n').split('\r\n').map((z) => (z.startsWith('.') ? '.' + z : z)).join('\r\n');
}

/** Baut die komplette Nachricht (Kopf + Text) als CRLF-Text. */
export function baueNachricht({ von, an, betreff, text, antwortAn = '', kopfExtra = {} }) {
  const absenderAdresse = adresseAus(von);
  const domain = absenderAdresse.split('@')[1] || 'localhost';
  const kopf = [
    `From: ${kopfAdresse(von)}`,
    `To: ${adresseAus(an)}`,
    `Subject: ${kodiereWort(betreff)}`,
    `Date: ${new Date().toUTCString().replace(/GMT$/, '+0000')}`,
    `Message-ID: <${randomUUID()}@${domain}>`,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=utf-8',
    'Content-Transfer-Encoding: quoted-printable',
    'Auto-Submitted: auto-generated',
  ];
  if (antwortAn && adresseAus(antwortAn)) kopf.push(`Reply-To: ${adresseAus(antwortAn)}`);
  for (const [k, v] of Object.entries(kopfExtra)) if (/^[A-Za-z][A-Za-z0-9-]*$/.test(k)) kopf.push(`${k}: ${kodiereWort(v)}`);
  return kopf.join('\r\n') + '\r\n\r\n' + quotedPrintable(text);
}

/** Verbindung mit zeilenweisem Lesen der (mehrzeiligen) SMTP-Antworten. */
class Verbindung {
  constructor(socket) { this.puffer = ''; this.wartende = []; this.antworten = []; this.fehler = null; this.binde(socket); }
  binde(socket) {
    this.socket = socket;
    socket.setEncoding('utf8');
    socket.on('data', (d) => { this.puffer += d; this.zerlege(); });
    socket.on('error', (e) => this.abbruch(e));
    socket.on('close', () => this.abbruch(new Error('Verbindung vom Mailserver geschlossen')));
  }
  zerlege() {
    let zeilen = [];
    for (;;) {
      const i = this.puffer.indexOf('\n'); if (i === -1) break;
      const zeile = this.puffer.slice(0, i).replace(/\r$/, ''); this.puffer = this.puffer.slice(i + 1);
      zeilen.push(zeile);
      if (/^\d{3}(?: |$)/.test(zeile)) {
        const antwort = { code: Number(zeile.slice(0, 3)), zeilen: zeilen.map((z) => z.slice(4)) };
        zeilen = [];
        const w = this.wartende.shift(); if (w) w.ok(antwort); else this.antworten.push(antwort);
      }
    }
    if (zeilen.length) this.puffer = zeilen.join('\n') + '\n' + this.puffer;
  }
  abbruch(e) {
    if (this.fehler) return;
    this.fehler = e;
    for (const w of this.wartende.splice(0)) w.nein(e);
  }
  lies() {
    if (this.antworten.length) return Promise.resolve(this.antworten.shift());
    if (this.fehler) return Promise.reject(this.fehler);
    return new Promise((ok, nein) => {
      const t = setTimeout(() => nein(new Error('Zeitueberschreitung beim Mailserver')), ZEITLIMIT);
      this.wartende.push({ ok: (a) => { clearTimeout(t); ok(a); }, nein: (e) => { clearTimeout(t); nein(e); } });
    });
  }
  async befehl(zeile, erwartet, ohneLog) {
    if (zeile !== null) this.socket.write(zeile + '\r\n');
    const a = await this.lies();
    const liste = Array.isArray(erwartet) ? erwartet : [erwartet];
    if (!liste.includes(a.code)) throw new Error(`Mailserver: ${ohneLog || (zeile || 'Begruessung').split(' ')[0]} -> ${a.code} ${a.zeilen.join(' ')}`.slice(0, 300));
    return a;
  }
}

function verbinde(konfig) {
  return new Promise((ok, nein) => {
    const optionen = { host: konfig.host, port: konfig.port, servername: net.isIP(konfig.host) ? undefined : konfig.host };
    const socket = konfig.secure ? tls.connect(optionen) : net.connect(optionen);
    const t = setTimeout(() => { socket.destroy(); nein(new Error('Mailserver nicht erreichbar (Zeitueberschreitung)')); }, ZEITLIMIT);
    socket.once(konfig.secure ? 'secureConnect' : 'connect', () => { clearTimeout(t); socket.removeAllListeners('error'); ok(socket); });
    socket.once('error', (e) => { clearTimeout(t); nein(e); });
  });
}

function starttls(socket, konfig) {
  return new Promise((ok, nein) => {
    socket.removeAllListeners('data'); socket.removeAllListeners('error'); socket.removeAllListeners('close');
    const sicher = tls.connect({ socket, servername: net.isIP(konfig.host) ? undefined : konfig.host });
    sicher.once('secureConnect', () => { sicher.removeAllListeners('error'); ok(sicher); });
    sicher.once('error', nein);
  });
}

const istLokal = (host) => ['localhost', '127.0.0.1', '::1'].includes(host);

/**
 * Verschickt eine Text-Mail. nachricht: {an, betreff, text, antwortAn?}.
 * Wirft bei jedem Fehler (Aufrufer faengt ab); Fehler mit code "NICHT_KONFIGURIERT",
 * wenn SMTP_HOST oder SMTP_FROM fehlen.
 */
export async function sendeMail(nachricht, konfig = smtpAusUmgebung()) {
  if (!smtpKonfiguriert(konfig)) { const e = new Error('SMTP ist nicht eingerichtet (SMTP_HOST, SMTP_FROM).'); e.code = 'NICHT_KONFIGURIERT'; throw e; }
  const von = adresseAus(konfig.from);
  const an = adresseAus(nachricht.an);
  if (!an) throw new Error('Empfaengeradresse ungueltig.');
  const daten = baueNachricht({ ...nachricht, von: konfig.from, an });
  const ehloName = (hostname() || 'localhost').replace(/[^A-Za-z0-9.-]/g, '') || 'localhost';

  let socket = await verbinde(konfig);
  let v = new Verbindung(socket);
  try {
    await v.befehl(null, 220);
    let ehlo = await v.befehl(`EHLO ${ehloName}`, 250);
    let faehig = ehlo.zeilen.map((z) => z.toUpperCase());
    let verschluesselt = konfig.secure;
    if (!verschluesselt && faehig.some((z) => z.startsWith('STARTTLS'))) {
      await v.befehl('STARTTLS', 220);
      socket = await starttls(socket, konfig);
      v = new Verbindung(socket); verschluesselt = true;
      ehlo = await v.befehl(`EHLO ${ehloName}`, 250);
      faehig = ehlo.zeilen.map((z) => z.toUpperCase());
    }
    if (konfig.user) {
      if (!verschluesselt && !istLokal(konfig.host)) throw new Error('Der Mailserver bietet kein STARTTLS an; Zugangsdaten werden nicht unverschluesselt gesendet. SMTP_PORT=465 oder SMTP_SECURE=true pruefen.');
      const auth = (faehig.find((z) => z.startsWith('AUTH')) || 'AUTH PLAIN').split(/[\s=]+/).slice(1);
      if (auth.includes('PLAIN') || !auth.includes('LOGIN')) {
        await v.befehl('AUTH PLAIN ' + Buffer.from(`\u0000${konfig.user}\u0000${konfig.pass}`, 'utf8').toString('base64'), 235, 'AUTH PLAIN');
      } else {
        await v.befehl('AUTH LOGIN', 334);
        await v.befehl(Buffer.from(konfig.user, 'utf8').toString('base64'), 334, 'AUTH LOGIN (Benutzer)');
        await v.befehl(Buffer.from(konfig.pass, 'utf8').toString('base64'), 235, 'AUTH LOGIN (Passwort)');
      }
    }
    await v.befehl(`MAIL FROM:<${von}>`, 250);
    await v.befehl(`RCPT TO:<${an}>`, [250, 251]);
    await v.befehl('DATA', 354);
    socket.write(punktMaskieren(daten) + '\r\n.\r\n');
    await v.befehl(null, 250, 'DATA (Inhalt)');
    try { socket.write('QUIT\r\n'); await v.lies(); } catch { /* Verbindung darf hier schon zu sein */ }
    return { ok: true };
  } finally {
    socket.end(); setTimeout(() => socket.destroy(), 1000).unref();
  }
}
