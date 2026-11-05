#!/usr/bin/env node
/**
 * Termin-Automatik (deploy/automatik-termine.mjs) mit echten Detailseiten des Heimat-Info-
 * Veranstaltungskalenders vom 02.11.2026 (qa/fixtures/automatik-termine.json):
 * Detailseite lesen (Titel, Veranstalter, Beginn, Ort, Text, Kategorie), Zeitangaben mit
 * Ende und ganztägig, Ortsteil und Umgebung, Zitat ohne Telefon/E-Mail, Termin im Schema von
 * deploy/termine.mjs ohne "entwurf" (seit 03.11.2026 direkt), Regeln für neu, doppelt, privat, vergangen.
 * Aufruf: node qa/automatik-termine-test.mjs
 */
import { readFileSync } from 'node:fs';
import { detailLesen, zeitLesen, ortsteilVon, kategorieVon, zitat, aehnlich, terminBauen, berlinVersatz, bestand, sauber } from '../deploy/automatik-termine.mjs';

const roh = JSON.parse(readFileSync(new URL('./fixtures/automatik-termine.json', import.meta.url), 'utf8'));
let fehler = 0;
const pruefe = (name, ist, soll) => { const ok = JSON.stringify(ist) === JSON.stringify(soll); if (!ok) fehler++; console.log(`  ${name.padEnd(74)} ${ok ? 'ok' : 'FEHLER (ist: ' + JSON.stringify(ist) + ')'}`); };

console.log('Hilfen');
pruefe('Sommer- und Winterzeit (Europe/Berlin)', [berlinVersatz(2026, 10, 24), berlinVersatz(2026, 10, 25, 12), berlinVersatz(2026, 11, 8), berlinVersatz(2026, 3, 29, 12)], ['+02:00', '+01:00', '+01:00', '+02:00']);
pruefe('Beginn mit Uhrzeit', zeitLesen('08.11.2026 10:30'), { tag: '2026-11-08', start: '2026-11-08T10:30:00+01:00', ende: '', ganztags: false });
pruefe('Beginn und Ende am selben Tag', zeitLesen('11.11.2026 17:30 - 20:00').ende, '2026-11-11T20:00:00+01:00');
pruefe('Mehrtägig ohne Uhrzeit (ganztägig)', zeitLesen('28.11.2026 - 29.11.2026'), { tag: '2026-11-28', start: '2026-11-28T00:00:00+01:00', ende: '2026-11-29T23:59:00+01:00', ganztags: true });
pruefe('Kein Datum: nicht lesbar', zeitLesen('Golzheim'), null);
pruefe('Emojis aus dem Titel', sauber('🏮 Sankt Martinsumzug in Golzheim 🏮'), 'Sankt Martinsumzug in Golzheim');
pruefe('Zitat: höchstens drei Sätze, ohne Telefonnummer, E-Mail, Link', zitat(['Vor dem Gottesdienst gibt es ein gemeinsames Frühstück.', 'Anmeldung unter 02421 / 37713 bitte.', 'Fragen an info@example.de schicken.', 'Mehr unter https://example.de lesen.', 'Alle Kinder sind herzlich eingeladen.']), 'Vor dem Gottesdienst gibt es ein gemeinsames Frühstück. Alle Kinder sind herzlich eingeladen.');
pruefe('Zitat: „Kontakt:“ mit Name und kurzer Nummer ohne Satzende fällt weg (05.11.2026)', zitat(['Mittwochs von 9 - 11 Uhr Kontakt: Küsterin Inge Eismar, Tel.: 37713 Gerne zum Frühstück anmelden!']), 'Mittwochs von 9 - 11 Uhr');
pruefe('Zitat: „Tel. 37713“ fällt weg, übriger Satz bleibt', zitat(['Wir freuen uns auch über neue Gesichter!', 'Kontakt: Inge Eismar, Tel. 37713']), 'Wir freuen uns auch über neue Gesichter!');
pruefe('Ähnliche Titel (gleiche Wörter), andere nicht', [aehnlich('Martinszug in Golzheim', 'Sankt Martinsumzug in Golzheim am 11. November 2026'), aehnlich('Gottesdienst mit Abendmahl', 'Ev. Gottesdienst: Gedenken an Verstorbene mit Abendmahl'), aehnlich('Weihnachtsfeier', 'Seniorennachmittag')], [true, true, false]);

console.log('\nEchte Detailseiten (02.11.2026)');
const d = roh.veranstaltungen.map(detailLesen);
pruefe('Alle sechs Seiten lesbar', d.filter(Boolean).length, 6);
pruefe('Kindergottesdienst: Titel, Veranstalter, Beginn, Ort, Kategorie', [d[0].titel, d[0].veranstalter, d[0].zeit.start, d[0].ort, d[0].kategorie],
  ['Kindergottesdienst mit Frühstück vorher', 'Evangelisches Gemeindezentrum Merzenich', '2026-11-08T10:30:00+01:00', 'Evangelisches Gemeindehaus, Serverin-Böhr-Str. 15 Merzenich', 'Kirchen']);
pruefe('Text endet vor dem Profil (keine Kontaktdaten)', d[0].text.some((z) => /Weitere Beiträge|Kontakt/.test(z)), false);
pruefe('Martinszug: Titel ohne Emojis, Ort Golzheim', [d[1].titel, d[1].ort, ortsteilVon(d[1])], ['Sankt Martinsumzug in Golzheim am 11. November 2026', 'Golzheim', 'Golzheim']);
pruefe('Gottesdienst in Niederzier: Umgebung, nicht Merzenich', ortsteilVon(d[4]), 'Umgebung');
pruefe('Weihnachtsfeier (Ort „BWZ“): Ortsteil aus dem Veranstalter', ortsteilVon(d[5]), 'Morschenich');
pruefe('Kategorien: Kirche, Brauchtum, Vereine', [kategorieVon(d[0]), kategorieVon(d[1]), kategorieVon(d[5])], ['Kirche', 'Brauchtum', 'Vereine']);

console.log('\nTermine');
const leer = terminBauen(d[5], '2026-11-02', [], {});
const t = leer.termin;
pruefe('Schema von deploy/termine.mjs (Pflichtfelder), direkt veröffentlicht (ohne "entwurf", seit 03.11.2026)', ['slug', 'titel', 'start', 'ort', 'ortsteil', 'kategorie', 'veranstalter', 'beschreibung', 'absaetze', 'quelle'].every((f) => t[f] && (!Array.isArray(t[f]) || t[f].length)) && t.entwurf === undefined, true);
pruefe('Slug mit Ortsteil bei kurzem Titel, Quelle mit Link und Stand', [t.slug, t.quelle.url, t.quelle.stand], ['weihnachtsfeier-morschenich-2026-12-06', 'https://www.heimat-info.de/veranstaltungen/1e0ecb73-2d3e-4763-ab13-613cf1188cc2', '2026-11-02']);
pruefe('Text nur aus den Feldern der Quelle', t.absaetze[0], 'St. Lambertus Schützenbruderschaft Morschenich e.V. lädt am Sonntag, 6. Dezember 2026, 15:30 Uhr zu „Weihnachtsfeier“ ein. Ort: BWZ.');
const kigo = terminBauen(d[0], '2026-11-02', [], {}).termin;
pruefe('Zitat der Ankündigung ohne die Telefonnummer', kigo.absaetze[1], 'Aus der Ankündigung im Veranstaltungskalender: „Vor dem Gottesdienst um 11 Uhr gemeinsames Frühstück ab 10:30 Uhr.“');
pruefe('vergangen und zu weit voraus: übersprungen', [terminBauen(d[0], '2026-11-09', [], {}).grund, terminBauen(d[5], '2026-07-01', [], {}).grund], ['liegt in der Vergangenheit', 'mehr als 120 Tage voraus']);
const privat = { ...d[5], titel: 'Polterabend', text: ['Wir feiern privat im Bürgerhaus.'] };
pruefe('privater Termin: übersprungen', terminBauen(privat, '2026-11-02', [], {}).grund, 'privater Termin');
pruefe('schon automatisch angelegt (Stand): übersprungen', terminBauen(d[5], '2026-11-02', [], { [d[5].url]: { am: '2026-11-01' } }).grund, 'schon einmal automatisch angelegt');
pruefe('gleicher Tag, ähnlicher Titel im Kalender: übersprungen', /steht schon im Kalender/.test(terminBauen(d[1], '2026-11-02', [{ tag: '2026-11-11', titel: 'Martinszug in Golzheim', url: '', slug: 'martinszug-golzheim-2026-11-11' }], {}).grund || ''), true);
pruefe('gleiche Quelle im Kalender: übersprungen', /steht schon/.test(terminBauen(d[5], '2026-11-02', [{ tag: '2026-01-01', titel: 'x', url: d[5].url, slug: 'y' }], {}).grund || ''), true);

console.log('\nBestand (inhalte/termine)');
const b = bestand(new URL('..', import.meta.url).pathname);
pruefe('Termine aller Pakete mit Tag, Titel und Quelle', b.length > 50 && b.every((x) => /^\d{4}-\d{2}-\d{2}$/.test(x.tag) || x.tag === ''), true);

console.log('\n' + (fehler ? `${fehler} Fehler` : 'Alle Prüfungen bestanden'));
process.exit(fehler ? 1 : 0);
