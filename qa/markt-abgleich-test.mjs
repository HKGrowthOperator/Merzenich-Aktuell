// Entscheidungen des Markt-Abgleichs (deploy/lib-markt-abgleich.mjs) mit Rohdaten.
// Aufruf: node qa/markt-abgleich-test.mjs
import { klassifiziere, gleicheAnzeige, schreibbar } from '../deploy/lib-markt-abgleich.mjs';

let fehler = 0;
const pruefe = (name, ist, soll) => {
  const ok = JSON.stringify(ist) === JSON.stringify(soll);
  if (!ok) fehler++;
  console.log(`  ${name.padEnd(70)} ${ok ? 'ok' : 'FEHLER (ist: ' + JSON.stringify(ist) + ')'}`);
};
const IW = 'https://www.immowelt.de/expose/d9867af1-23ab-42f6-9bae-511771be3e77';
const IS = 'https://www.immobilienscout24.de/expose/170213703';

console.log('Klassifizierung');
pruefe('200 auf derselben Anzeige: online', klassifiziere(200, IW, IW), 'online');
pruefe('200 nach Weiterleitung nur mit Abfrage/Schrägstrich: online', klassifiziere(200, IS, IS + '/?referrer=x'), 'online');
pruefe('200 nach Weiterleitung auf die Suche: unklar', klassifiziere(200, IS, 'https://www.immobilienscout24.de/Suche/de/nordrhein-westfalen/dueren-kreis/merzenich'), 'unklar');
pruefe('200 nach Weiterleitung auf andere Domain: unklar', klassifiziere(200, IW, 'https://www.example.org/expose/x'), 'unklar');
pruefe('404 und 410: entfernt', [klassifiziere(404, IW, IW), klassifiziere(410, IS, IS)], ['entfernt', 'entfernt']);
pruefe('401/403 (gesperrt), 503, Fehler: unklar', [klassifiziere(401), klassifiziere(403), klassifiziere(503), klassifiziere('fehler: ETIMEDOUT')], ['unklar', 'unklar', 'unklar', 'unklar']);
pruefe('Stellen ohne Adressvergleich: 200 online', klassifiziere(200), 'online');
pruefe('www-Präfix zählt nicht als andere Seite', gleicheAnzeige('https://immowelt.de/expose/a', 'https://www.immowelt.de/expose/a'), true);
pruefe('kaputte Adresse ist nicht dieselbe Anzeige', gleicheAnzeige('kaputt', IW), false);

console.log('Schreiben oder nicht');
pruefe('alle online: schreiben', schreibbar(['online', 'online', 'online']).ok, true);
pruefe('einzelne unklar/entfernt: schreiben', schreibbar(['online', 'online', 'unklar', 'entfernt']).ok, true);
pruefe('genau die Hälfte unklar: noch schreiben', schreibbar(['online', 'unklar']).ok, true);
pruefe('mehr als die Hälfte unklar (Portal blockt): nicht schreiben', schreibbar(['unklar', 'unklar', 'online']), { ok: false, grund: 'mehr als die Haelfte unklar' });
pruefe('mehr als die Hälfte entfernt: nicht schreiben', schreibbar(['entfernt', 'entfernt', 'online']), { ok: false, grund: 'mehr als die Haelfte angeblich entfernt' });
pruefe('leer: nicht schreiben', schreibbar([]).ok, false);

console.log(fehler ? `\n${fehler} Fehler` : '\nAlle Prüfungen bestanden');
process.exit(fehler ? 1 : 0);
