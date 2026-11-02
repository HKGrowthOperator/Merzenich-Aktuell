// Abwechslung der Werbung im Browser (Vorgabe Betreiber 02.11.2026): ad-rotation.js
// mit nachgebautem DOM. Geprüft: Start ohne doppelte Firmen auf Nachbarplätzen,
// nie dieselbe Firma zweimal nacheinander in einem Platz, nie gleichzeitig in zwei
// sichtbaren Plätzen, solange es genug Firmen gibt.
// Aufruf: node qa/werbung-rotation-test.mjs
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const quelle = readFileSync(new URL('../wordpress/plugin/merzenich-aktuell-core/assets/ad-rotation.js', import.meta.url), 'utf8');
let fehler = 0;
const pruefe = (name, ist, soll) => {
  const ok = JSON.stringify(ist) === JSON.stringify(soll);
  if (!ok) fehler++;
  console.log(`  ${name.padEnd(76)} ${ok ? 'ok' : 'FEHLER (ist: ' + JSON.stringify(ist) + ')'}`);
};

// Kleiner DOM-Nachbau: Plätze mit Karten (data-ma-firma), Timer per Hand.
function welt(plaetzeFirmen, zufall) {
  const timer = [];
  const element = (attrs) => ({
    attrs: { ...attrs }, hidden: false, kinder: [],
    getAttribute(k) { return k in this.attrs ? this.attrs[k] : null; },
    setAttribute(k, v) { this.attrs[k] = String(v); },
    hasAttribute(k) { return k in this.attrs; },
    addEventListener() {},
    querySelectorAll(sel) { return sel === '.ma-ad__item' ? this.kinder : []; },
  });
  const plaetze = plaetzeFirmen.map((firmen) => {
    const p = element({ 'data-ma-ad-rotate': '8000' });
    p.kinder = firmen.map((f) => element({ 'data-ma-firma': f }));
    return p;
  });
  let z = 0;
  const fenster = {
    matchMedia: () => ({ matches: false }), innerWidth: 1440,
    setTimeout: (fn, ms) => timer.push({ fn, ms, einmal: true }),
    setInterval: (fn, ms) => timer.push({ fn, ms, einmal: false }),
  };
  const kontext = {
    window: fenster, navigator: {}, Math: Object.assign(Object.create(Math), { random: () => zufall[z++ % zufall.length] }),
    document: { hidden: false, querySelectorAll: (sel) => (sel === '.ma-ad' ? plaetze : []) },
    parseInt, Array,
  };
  vm.createContext(kontext);
  vm.runInContext(quelle, kontext);
  // Versatz-Timer auslösen, dann laufen die Intervalle.
  timer.filter((t) => t.einmal).forEach((t) => t.fn());
  const intervalle = timer.filter((t) => !t.einmal);
  const sicht = () => plaetze.map((p) => p.kinder.find((k) => !k.hidden).getAttribute('data-ma-firma'));
  return { plaetze, sicht, takt: () => intervalle.forEach((t) => t.fn()), wahl: fenster.maAdWahl };
}

console.log('Auswahl (reine Funktionen)');
{
  const { wahl } = welt([['a']], [0]);
  pruefe('Start überspringt belegte Firmen', wahl.start(['a', 'b', 'a', 'c'], ['a', 'b'], 0), 3);
  pruefe('Nächste: andere Firma, nicht belegt', wahl.naechste(['a', 'b', 'c', 'a'], 0, ['b']), 2);
  pruefe('Nächste: notfalls nur andere Firma', wahl.naechste(['a', 'b', 'a'], 0, ['b']), 1);
  pruefe('Nächste: eine Firma allein dreht weiter', wahl.naechste(['a', 'a'], 0, []), 1);
}

console.log('Rotation über alle Plätze (6 Firmen, 13 Anzeigen, 8 Plätze, 200 Wechsel)');
// Reihenfolge wie vom Server (gemischt): nie zwei gleiche nebeneinander.
const reihe = ['hk', 'alm', 'kbs', 'ass', 'mer', 'aj', 'hk', 'alm', 'kbs', 'ass', 'mer', 'aj', 'hk'];
for (const zufall of [[0], [0.5], [0.13, 0.77, 0.41, 0.9, 0.05, 0.62, 0.33, 0.21]]) {
  const w = welt(Array.from({ length: 8 }, () => reihe), zufall);
  const start = w.sicht();
  const nachbarnGleich = start.slice(1).filter((f, i) => f === start[i]).length;
  let gleichNacheinander = 0, gleichzeitig = 0;
  let vorher = start;
  for (let t = 0; t < 200; t++) {
    w.takt();
    const jetzt = w.sicht();
    jetzt.forEach((f, i) => { if (f === vorher[i]) gleichNacheinander++; });
    // Mehr Plätze (8) als Firmen (6): zwei Paare dürfen sich teilen, mehr nicht.
    const doppelt = jetzt.length - new Set(jetzt).size;
    if (doppelt > jetzt.length - 6) gleichzeitig++;
    vorher = jetzt;
  }
  pruefe(`Zufall ${JSON.stringify(zufall).slice(0, 20)}: Start ohne gleiche Firma auf Nachbarplätzen`, nachbarnGleich, 0);
  pruefe(`Zufall ${JSON.stringify(zufall).slice(0, 20)}: nie dieselbe Firma zweimal nacheinander`, gleichNacheinander, 0);
  pruefe(`Zufall ${JSON.stringify(zufall).slice(0, 20)}: gleichzeitig nicht mehr Doppelte als nötig`, gleichzeitig, 0);
}

console.log(fehler ? `${fehler} Fehler` : 'Alle Prüfungen bestanden.');
process.exit(fehler ? 1 : 0);
