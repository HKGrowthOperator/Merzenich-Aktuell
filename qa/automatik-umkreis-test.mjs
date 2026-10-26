#!/usr/bin/env node
/**
 * Umkreis-Automatik (deploy/automatik-umkreis.mjs) mit echten Rohdaten vom 26.10.2026
 * (qa/fixtures/automatik-umkreis.json, Presseportal-Listen Merzenich und Golzheim):
 * Listen zerlegen (Datum, Bildzeile, Schlagzeile, Vorspann, Link), Orte aus dem Vorspann,
 * Zuordnung Umkreis / Kreis Düren / Kernort / außerhalb, Eintragsschema, Dubletten.
 * Aufruf: node qa/automatik-umkreis-test.mjs
 */
import { readFileSync } from 'node:fs';
import { listeZerlegen, meldungenLesen, eintragBauen, orteAusVorspann, umkreisOrt, schlagzeile, gedeckteUrls } from '../deploy/automatik-umkreis.mjs';

const roh = JSON.parse(readFileSync(new URL('./fixtures/automatik-umkreis.json', import.meta.url), 'utf8'));
let fehler = 0;
const pruefe = (name, ist, soll) => { const ok = JSON.stringify(ist) === JSON.stringify(soll); if (!ok) fehler++; console.log(`  ${name.padEnd(74)} ${ok ? 'ok' : 'FEHLER (ist: ' + JSON.stringify(ist) + ')'}`); };

console.log('Hilfen');
pruefe('Schlagzeile ohne Dienststellenkürzel', [schlagzeile('POL-DN: Pkw entwendet - die Polizei bittet um Hinweise'), schlagzeile('POL-AC:  Steinwürfe  auf Radlader')], ['Pkw entwendet - die Polizei bittet um Hinweise', 'Steinwürfe auf Radlader']);
pruefe('Orte aus dem Vorspann (Schrägstrich, Bindestrich, Komma, Kreis)', [orteAusVorspann('Düren / Merzenich (ots) - x'), orteAusVorspann('Kerpen-Buir (ots) - y'), orteAusVorspann('Kreis Düren (ots) - z'), orteAusVorspann('Aachen/ Hambach/ Morschenich (ots) - e'), orteAusVorspann('ohne Ortsangabe')],
  [['Düren', 'Merzenich'], ['Kerpen', 'Buir'], ['Kreis Düren'], ['Aachen', 'Hambach', 'Morschenich'], []]);
pruefe('Umkreis-Ort direkt', umkreisOrt(['Niederzier']), { ort: 'Niederzier', praefix: '' });
pruefe('Kreis Düren mit Ort vor der Schlagzeile', [umkreisOrt(['Titz']), umkreisOrt(['Jülich', 'Linnich']), umkreisOrt(['Kreis Düren'])], [{ ort: 'Kreis Düren', praefix: 'Titz: ' }, { ort: 'Kreis Düren', praefix: 'Jülich/Linnich: ' }, { ort: 'Kreis Düren', praefix: '' }]);
pruefe('Kernort nie als Umkreis, auch nicht neben Düren oder Aachen', [umkreisOrt(['Düren', 'Merzenich']).kernort, umkreisOrt(['Merzenich', 'Golzheim']).kernort, umkreisOrt(['Aachen', 'Hambach', 'Morschenich']).kernort], [true, true, true]);
pruefe('Außerhalb des Kreises und ohne Ort: übersprungen', [/außerhalb/.test(umkreisOrt(['Köln']).grund), /kein Ort/.test(umkreisOrt([]).grund)], [true, true]);

console.log('\nEchte Rohdaten (Listen Merzenich und Golzheim)');
const merzenich = listeZerlegen(roh.listen.find((l) => l.url.endsWith('/Merzenich')));
pruefe('Merzenich-Liste: 30 Meldungen, jede mit Link zur Einzelmeldung', [merzenich.length, merzenich.filter((m) => /\/blaulicht\/pm\/8\/\d+$/.test(m.url)).length], [30, 30]);
pruefe('Erste Meldung: Datum, Zeit, Dienststelle, Schlagzeile, Vorspann, Link', [merzenich[0].datum, merzenich[0].zeit, merzenich[0].dienststelle, merzenich[0].titel, merzenich[0].vorspann.slice(0, 16), merzenich[0].url],
  ['2026-09-26', '07:20', 'Polizei Düren', 'Abbiegeunfall fordert schwer verletzten Zweiradfahrer', 'Merzenich (ots) ', 'https://www.presseportal.de/blaulicht/pm/8/6359466']);
const bild = merzenich.find((m) => m.datum === '2026-03-19');
pruefe('Bildanzahl-Zeile zwischen Dienststelle und Schlagzeile wird übersprungen', [bild?.titel?.slice(0, 40), bild?.url?.endsWith('/pm/8/')], ['Drei Verletzte bei Frontalzusammenstoß a', false]);
pruefe('Meldung ohne Vorspann ("mehr" folgt direkt) bleibt ohne Vorspann', merzenich.find((m) => m.datum === '2026-09-16').vorspann, '');
const alle = meldungenLesen(roh);
pruefe('Beide Listen zusammen, je URL einmal, neueste zuerst', [alle.length, alle.length === new Set(alle.map((m) => m.url)).size, alle[0].datum >= alle[alle.length - 1].datum], [33, true, true]);

console.log('\nEinträge');
pruefe('Kernort-Meldung (Merzenich) geht an die Redaktion, nie in den Umkreis', eintragBauen(merzenich[0]).kernort, true);
pruefe('Meldung mit Düren und Merzenich im Vorspann ebenso', eintragBauen(merzenich.find((m) => /^Düren \/ Merzenich/.test(m.vorspann))).kernort, true);
const golzheim = listeZerlegen(roh.listen.find((l) => l.url.endsWith('/Golzheim')));
pruefe('Golzheim/Merzenich (ots) ist Kernort', eintragBauen(golzheim[0]).kernort, true);
// Umkreis-Fall mit derselben Zeilenstruktur: Vorspann einer Nachbarkommune.
const nachbar = { ...merzenich[2], vorspann: 'Niederzier (ots) - Ein Einfamilienhaus wurde aufgebrochen.' };
pruefe('Niederzier: Eintrag im Schema von deploy/umkreis.mjs, Schlagzeile unverändert', eintragBauen(nachbar).eintrag, { ort: 'Niederzier', rubrik: 'Blaulicht', titel: 'Zeugensuche nach Einbruch in Einfamilienhaus', url: 'https://www.presseportal.de/blaulicht/pm/8/6349975', datum: '2026-09-11', quelle: 'Polizei Düren' });
const titz = { ...merzenich[2], vorspann: 'Titz (ots) - x' };
pruefe('Titz: Ort vor der Schlagzeile, Eintrag unter Kreis Düren', [eintragBauen(titz).eintrag.ort, eintragBauen(titz).eintrag.titel], ['Kreis Düren', 'Titz: Zeugensuche nach Einbruch in Einfamilienhaus']);
const juelich = { ...merzenich[2], titel: 'Jülich: Einbruch in Wohnhaus', vorspann: 'Jülich (ots) - x' };
pruefe('Steht der Ort schon in der Schlagzeile, kein doppelter Ort', eintragBauen(juelich).eintrag.titel, 'Jülich: Einbruch in Wohnhaus');
pruefe('Andere Dienststelle (Polizei Aachen) wird übersprungen', /Dienststelle/.test(eintragBauen({ ...nachbar, dienststelle: 'Polizei Aachen' }).grund), true);
pruefe('Ohne Link übersprungen', /Link/.test(eintragBauen({ ...nachbar, url: '' }).grund), true);

console.log('\nBestand');
const gedeckt = gedeckteUrls(new URL('..', import.meta.url).pathname);
pruefe('URLs aus inhalte/umkreis und Quellen der Meldungen gelten als gedeckt', [gedeckt.has('https://www.presseportal.de/blaulicht/pm/8/6377797'), gedeckt.has('https://www.presseportal.de/blaulicht/pm/8/6359466')], [true, true]);

console.log('\n' + (fehler ? `${fehler} Fehler` : 'Alle Prüfungen bestanden'));
process.exit(fehler ? 1 : 0);
