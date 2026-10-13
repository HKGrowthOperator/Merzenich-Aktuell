#!/usr/bin/env node
/**
 * Feuerwehr-Automatik (deploy/automatik-feuerwehr.mjs) mit echten Rohdaten vom 12.10.2026
 * (qa/fixtures/automatik-feuerwehr.json): Zerlegen, Zusammenführen, Widersprüche,
 * Ortsteil, Bildklasse, Zitat, Mindestlänge, Dubletten.
 * Aufruf: node qa/automatik-feuerwehr-test.mjs
 */
import { readFileSync } from 'node:fs';
import { einsaetzeLesen, meldungBauen, ortsteilVon, einsatzortZerlegen, berlinIso, zitatAus, gedeckteNummern, heimatInfoZerlegen } from '../deploy/automatik-feuerwehr.mjs';

const roh = JSON.parse(readFileSync(new URL('./fixtures/automatik-feuerwehr.json', import.meta.url), 'utf8'));
const klassen = JSON.parse(readFileSync(new URL('../deploy/bildklassen.json', import.meta.url), 'utf8'));
const gueltig = new Set(Object.keys(klassen.klassen || klassen));
let fehler = 0;
const pruefe = (name, ist, soll) => { const ok = JSON.stringify(ist) === JSON.stringify(soll); if (!ok) fehler++; console.log(`  ${name.padEnd(70)} ${ok ? 'ok' : 'FEHLER (ist: ' + JSON.stringify(ist) + ')'}`); };
const woerter = (m) => [m.dek, ...m.absaetze].join(' ').split(/\s+/).filter(Boolean).length;

console.log('Hilfen');
pruefe('Ortsteile mit Schreibfehlern der Quelle', ['Merzenich', 'Mereznich', 'Girbelsrtath', 'Bürgewald', 'burgewald', 'BAB4', 'A4'].map(ortsteilVon), ['merzenich', 'merzenich', 'girbelsrath', 'buergewald', 'buergewald', '', '']);
pruefe('Einsatzort zerlegen', [einsatzortZerlegen('Merzenich - Steinweg'), einsatzortZerlegen('Merzenich B264/Valdersweg'), einsatzortZerlegen('Merzenich - S_Bahnhaltestelle').strasse, einsatzortZerlegen('BAB4')],
  [{ ort: 'Merzenich', strasse: 'Steinweg' }, { ort: 'Merzenich', strasse: 'B264/Valdersweg' }, 'S-Bahnhaltestelle', { ort: 'BAB4', strasse: '' }]);
pruefe('Ortszeit mit Versatz: Sommer +02:00, Winter +01:00', [berlinIso('04.10.2026', '11:03'), berlinIso('05.11.2026', '9:15')], ['2026-10-04T11:03:00+02:00', '2026-11-05T09:15:00+01:00']);
pruefe('Zitat ohne Sätze mit Ziffern, relative Zeitangaben nachrangig', zitatAus('Am vergangenen Sonntag wurde alarmiert. Vor Ort in Hausnummer 12 war niemand. Die Tür wurde geöffnet. Der Einsatz war schnell beendet.'), 'Die Tür wurde geöffnet. Der Einsatz war schnell beendet.');

console.log('\nEchte Rohdaten');
const einsaetze = einsaetzeLesen(roh.feuerwehr, roh.heimatinfo);
pruefe('Drei Einsätze, je Nummer zusammengeführt', einsaetze.map((e) => [e.nr, !!e.fw, !!e.hi]), [[133, true, true], [136, true, true], [138, true, true]]);
const hi138 = heimatInfoZerlegen(roh.heimatinfo.details.find((s) => s.text.includes('138/26')));
pruefe('Heimat-Info: Felder aus den Feldzeilen, nicht aus der gekürzten Vorschau', [hi138.meldung, hi138.stichwort, hi138.datum, hi138.beitragDatum, hi138.beitragZeit], ['Person hinter Tür', 'PET', '04.10.2026', '06.10.2026', '19:09']);
const e = Object.fromEntries(einsaetze.map((x) => [x.nr, x]));
pruefe('136: Datumswiderspruch erkannt, Ort gleich', e[136].widersprueche, ['datum']);

const r138 = meldungBauen(e[138], { stand: '2026-10-12', gueltigeKlassen: gueltig });
const m = r138.meldung;
pruefe('138: Entwurf entsteht', !!m, true);
pruefe('138: Slug, Ressort, Ortsteil, Kicker', [m.slug, m.ressort, m.ortsteil, m.kicker], ['einsatz-138-person-hinter-tuer-merzenich', 'blaulicht', 'merzenich', 'Feuerwehr · 138/26']);
pruefe('138: Datum = Alarmierung laut Einsatzseite', m.datum, '2026-10-04T11:03:00+02:00');
pruefe('138: Bildklasse Türöffnung, Kurzmeldung mit mindestens 80 Wörtern', [m.bildklasse, m.artikeltyp, woerter(m) >= 80, m.absaetze.length >= 2, m.fakten.length >= 2], ['blaulicht.rettung.tuer', 'kurzmeldung', true, true, true]);
pruefe('138: Quelle Einsatzseite, weitere Quelle Heimat-Info mit Datum', [m.quelle.url, m.weitereQuellen[0].name], ['https://feuerwehr-merzenich.de/einsaetze/13826-merzenich-steinweg', 'Freiwillige Feuerwehr Merzenich, Beitrag in der Heimat-Info-App vom 06.10.2026']);
pruefe('138: Zitat gekennzeichnet, Text ohne Hausnummern', [/Die Feuerwehr schreibt dazu: „[^“]+“/.test(m.absaetze[1]), /Steinweg \d/.test(m.absaetze.join(' '))], [true, false]);
pruefe('138: Titel 10 bis 110 Zeichen, Vorspann 60 bis 200', [m.titel.length >= 10 && m.titel.length <= 110, m.dek.length >= 60 && m.dek.length <= 200], [true, true]);

const m136 = meldungBauen(e[136], { stand: '2026-10-12', gueltigeKlassen: gueltig }).meldung;
pruefe('136: beide Datumsangaben mit Quelle im Text', /Einsatzseite der Feuerwehr nennt Freitag, 2\. Oktober 2026, 21:36 Uhr, der Beitrag der Feuerwehr in der Heimat-Info-App den 3\. Oktober 2026/.test(m136.absaetze[0]), true);
pruefe('136: Meldung aus Heimat-Info, Bildklasse Feuer', [m136.titel, m136.bildklasse], ['Unklare Rauchentwicklung in Merzenich: Einsatz 136/26 der Feuerwehr', 'blaulicht.feuer']);

const r133 = meldungBauen(e[133], { stand: '2026-10-12', gueltigeKlassen: gueltig });
pruefe('133 (Autobahn): übersprungen mit Grund', [!!r133.meldung, /keinem Ortsteil/.test(r133.grund || '')], [false, true]);

console.log('\nBestand');
const gedeckt = gedeckteNummern(new URL('..', import.meta.url).pathname, '26');
pruefe('Vorhandene Meldungen gelten als gedeckt (137, 134, 128 aus site-source)', [gedeckt.has(137), gedeckt.has(134), gedeckt.has(128)], [true, true, true]);

console.log('\n' + (fehler ? `${fehler} Fehler` : 'Alle Prüfungen bestanden'));
process.exit(fehler ? 1 : 0);
