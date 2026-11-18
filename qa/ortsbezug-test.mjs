#!/usr/bin/env node
/**
 * Ortsbezug (qa/lib-ortsbezug.mjs): gültige Sätze, fehlendes Feld, allgemeine Wendungen.
 * Aufruf: node qa/ortsbezug-test.mjs
 */
import { ortsbezugPruefen } from './lib-ortsbezug.mjs';
let fehler = 0;
const pruefe = (name, ist, soll) => { const ok = JSON.stringify(ist) === JSON.stringify(soll); if (!ok) fehler++; console.log(`  ${name.padEnd(76)} ${ok ? 'ok' : 'FEHLER (ist: ' + JSON.stringify(ist) + ')'}`); };
const gilt = (s) => ortsbezugPruefen(s) === '';
console.log('Gültig');
pruefe('Ereignis in einem Ortsteil', gilt('Der Bürgewald-Ausschuss tagt am 26. November im Ratssaal in Merzenich.'), true);
pruefe('Merzenicher Verein beteiligt, auch wenn der Ort anderswo liegt', gilt('Der SC 1919 spielt am Sonntag auswärts in Frenz um Platz drei.'), true);
pruefe('Kreis-Service, der Merzenicher ausdrücklich betrifft', gilt('Familien aus Merzenich melden den Kita-Bedarf für 2027/28 im Kita-Navigator des Kreises an.'), true);
pruefe('Einrichtung ohne Ortsnamen', gilt('Die Löschgruppe rückte am Abend zu einem Brand an der Bahnstrecke aus.'), true);
pruefe('Ortsteil Golzheim', gilt('In Golzheim ist die Schützenhalle am Donnerstag Haltestelle des Jugendbusses.'), true);
console.log('\nNicht gültig');
pruefe('Feld fehlt', /fehlt/.test(ortsbezugPruefen(undefined)), true);
pruefe('Leer', /fehlt/.test(ortsbezugPruefen('  ')), true);
pruefe('Zu kurz', /kurz/.test(ortsbezugPruefen('Merzenich betroffen.')), true);
pruefe('„für Merzenich und Region“ ohne konkreten Anlass (Tamil-Meldung 17.11.)', /allgemein/.test(ortsbezugPruefen('Tamilische Festtage für Merzenich und Region bis April 2027 im Überblick.')), true);
pruefe('„auch für Merzenich“', /allgemein/.test(ortsbezugPruefen('Die IHK-Roadshow in Düren ist auch für Merzenich interessant.')), true);
pruefe('„Kreis Düren, also auch Merzenich“', /allgemein/.test(ortsbezugPruefen('Betriebe aus dem Kreis Düren, also auch aus Merzenich, können teilnehmen.')), true);
pruefe('Kein Ort der Gemeinde', /allgemein/.test(ortsbezugPruefen('Exkursion zu den Wildpferden auf der Sophienhöhe am 29. Oktober.')), true);
console.log(fehler ? `\n${fehler} Fehler` : '\nAlle Prüfungen bestanden');
process.exit(fehler ? 1 : 0);
