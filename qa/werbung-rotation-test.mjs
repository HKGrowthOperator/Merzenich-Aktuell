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
function welt(plaetzeFirmen, zufall, mitBeobachter = false) {
  const timer = [];
  const beobachtet = new Map();
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
  // Nachgebauter IntersectionObserver: die Rückrufe je Platz löst der Test selbst aus.
  if (mitBeobachter) {
    kontext.IntersectionObserver = fenster.IntersectionObserver = class { constructor(cb) { this.cb = cb; } observe(el) { beobachtet.set(el, this.cb); } };
  }
  vm.createContext(kontext);
  vm.runInContext(quelle, kontext);
  // Versatz-Timer auslösen, dann laufen die Intervalle.
  timer.filter((t) => t.einmal).forEach((t) => t.fn());
  const intervalle = timer.filter((t) => !t.einmal);
  const sicht = () => plaetze.map((p) => p.kinder.find((k) => !k.hidden).getAttribute('data-ma-firma'));
  const imBild = (i, ja) => beobachtet.get(plaetze[i])([{ isIntersecting: ja }]);
  return { plaetze, sicht, imBild, takt: () => intervalle.forEach((t) => t.fn()), taktEinzeln: (i) => intervalle[i].fn(), wahl: fenster.maAdWahl };
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

console.log('Platz kommt ins Bild (IntersectionObserver)');
{
  // Zwei Plätze mit denselben drei Firmen; Platz 1 im Bild, Platz 2 noch nicht.
  const w = welt([['a', 'b', 'c'], ['a', 'b', 'c']], [0], true);
  w.imBild(0, true); w.imBild(1, false);
  // Platz 1 darf auf die Firma des unsichtbaren Platzes 2 wechseln: nur Platz 1 drehen, bis es so ist.
  let n = 0; while (w.sicht()[0] !== w.sicht()[1] && n++ < 20) w.taktEinzeln(0);
  pruefe('außerhalb des Bildes ist dieselbe Firma erlaubt', w.sicht()[0], w.sicht()[1]);
  w.imBild(1, true);
  pruefe('beim Eintritt ins Bild wechselt Platz 2 zu einer anderen Firma', w.sicht()[0] !== w.sicht()[1], true);
}

console.log(fehler ? `${fehler} Fehler` : 'Alle Prüfungen bestanden.');
process.exit(fehler ? 1 : 0);
