/**
 * deploy/google-seo.mjs ohne Google: Schlüssel lesen, Anmeldung signieren,
 * Property wählen, Zeitraum, Aufbereitung und Hinweise bei Fehlern.
 * Aufruf: node qa/google-seo-test.mjs
 */
import { createVerify, generateKeyPairSync } from 'node:crypto';
import { dienstkontoLesen, jwtBauen, propertyWaehlen, zeitraum, zeilenAufbereiten, fehlerHinweis, tempoAufbereiten } from '../deploy/google-seo.mjs';

let fehler = 0;
const pruefe = (name, ist, soll) => {
  const ok = JSON.stringify(ist) === JSON.stringify(soll);
  if (!ok) fehler++;
  console.log(`  ${name.padEnd(70)} ${ok ? 'ok' : `FEHLER (ist: ${JSON.stringify(ist)})`}`);
};
const wirft = (fn) => { try { fn(); return ''; } catch (e) { return e.message; } };

const { privateKey, publicKey } = generateKeyPairSync('rsa', { modulusLength: 2048, privateKeyEncoding: { type: 'pkcs8', format: 'pem' }, publicKeyEncoding: { type: 'spki', format: 'pem' } });
const json = JSON.stringify({ type: 'service_account', client_email: 'seo@projekt.iam.gserviceaccount.com', private_key: privateKey.replace(/\n/g, '\\n'), project_id: 'projekt' });

console.log('Schlüssel lesen');
pruefe('JSON-Text mit \\n im Schlüssel', dienstkontoLesen(json).schluessel === privateKey, true);
pruefe('auch als Base64', dienstkontoLesen(Buffer.from(json).toString('base64')).email, 'seo@projekt.iam.gserviceaccount.com');
pruefe('fehlt: klarer Hinweis', wirft(() => dienstkontoLesen('')).includes('nicht gesetzt'), true);
pruefe('API-Schlüssel statt Dienstkonto: Hinweis', wirft(() => dienstkontoLesen('{"type":"authorized_user"}')).includes('kein Dienstkonto'), true);
pruefe('Kaputtes JSON: Hinweis', wirft(() => dienstkontoLesen('{abc')).includes('kein gültiges JSON'), true);

console.log('\nAnmeldung (JWT)');
const konto = dienstkontoLesen(json);
const [k, i, s] = jwtBauen(konto, 'https://www.googleapis.com/auth/webmasters', 1_800_000_000).split('.');
const v = createVerify('RSA-SHA256'); v.update(`${k}.${i}`);
pruefe('Signatur RS256 stimmt', v.verify(publicKey, Buffer.from(s, 'base64url')), true);
pruefe('Inhalt: Aussteller, Ziel, eine Stunde gültig', (({ iss, aud, iat, exp }) => [iss, aud, exp - iat])(JSON.parse(Buffer.from(i, 'base64url'))), ['seo@projekt.iam.gserviceaccount.com', 'https://oauth2.googleapis.com/token', 3600]);

console.log('\nProperty wählen');
const liste = [
  { siteUrl: 'https://merzenich-aktuell.de/', permissionLevel: 'siteFullUser' },
  { siteUrl: 'sc-domain:merzenich-aktuell.de', permissionLevel: 'siteOwner' },
  { siteUrl: 'sc-domain:andere.de', permissionLevel: 'siteOwner' },
];
pruefe('Domain-Property vor URL-Präfix', propertyWaehlen(liste).siteUrl, 'sc-domain:merzenich-aktuell.de');
pruefe('nur URL-Präfix vorhanden', propertyWaehlen(liste.slice(0, 1)).siteUrl, 'https://merzenich-aktuell.de/');
pruefe('unbestätigt zählt nicht', propertyWaehlen([{ siteUrl: 'sc-domain:merzenich-aktuell.de', permissionLevel: 'siteUnverifiedUser' }]), null);

console.log('\nZeitraum und Aufbereitung');
pruefe('28 Tage bis gestern', zeitraum(28, new Date('2026-10-07T10:00:00Z')), { startDate: '2026-09-09', endDate: '2026-10-06' });
pruefe('Klickrate in Prozent, Position gerundet', zeilenAufbereiten([{ keys: ['merzenich'], clicks: 3, impressions: 40, ctr: 0.075, position: 4.26 }]), [{ schluessel: 'merzenich', klicks: 3, impressionen: 40, klickrate: 7.5, position: 4.3 }]);
pruefe('PageSpeed: Punkte und Messwerte', tempoAufbereiten({ lighthouseResult: { categories: { performance: { score: 0.91 }, seo: { score: 1 } }, audits: { 'largest-contentful-paint': { displayValue: '2,1 s' } } }, loadingExperience: { overall_category: 'FAST', metrics: { INTERACTION_TO_NEXT_PAINT: { percentile: 120, category: 'FAST' } } } }).leistung, 91);

console.log('\nHinweise bei Fehlern');
pruefe('API nicht aktiviert: Link zum Aktivieren', fehlerHinweis(403, JSON.stringify({ error: { message: 'Google Search Console API has not been used in project 1 before or it is disabled.' } })).includes('apis/library/searchconsole.googleapis.com'), true);
pruefe('Keine Berechtigung: Nutzer „Uneingeschränkt“ eintragen', fehlerHinweis(403, JSON.stringify({ error: { message: 'User does not have sufficient permission for site' } }), 'seo@p.iam').includes('seo@p.iam mit der Berechtigung „Uneingeschränkt“'), true);
pruefe('Konto gelöscht: neuen Schlüssel erstellen', fehlerHinweis(400, JSON.stringify({ error: 'invalid_grant', error_description: 'Invalid grant: account not found' })).includes('Neuen JSON-Schlüssel'), true);

console.log(fehler ? `\n${fehler} Fehler` : '\nAlle Prüfungen bestanden');
process.exit(fehler ? 1 : 0);
