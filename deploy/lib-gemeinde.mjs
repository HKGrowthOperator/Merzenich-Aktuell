/**
 * Gemeindedaten (deploy/gemeinde.json) lesen und als Text ausgeben. Eine
 * Quelle fuer /service/, das Startseiten-Cockpit und die WordPress-Ausgabe.
 */
import { readFileSync } from 'node:fs';

export const GEMEINDE = JSON.parse(readFileSync(new URL('./gemeinde.json', import.meta.url), 'utf8'));
const TAG = { 1: 'Montag', 2: 'Dienstag', 3: 'Mittwoch', 4: 'Donnerstag', 5: 'Freitag', 6: 'Samstag', 7: 'Sonntag' };
const uhr = (s) => { const [h, m] = s.split(':'); return m === '00' ? String(Number(h)) : `${Number(h)}:${m}`; };
const spanne = ([von, bis]) => `${uhr(von)} bis ${uhr(bis)}`;

/** "Montag 8 bis 12:30 und 14 bis 16:30 Uhr, Dienstag geschlossen, ..." (Mo-Fr). */
export function zeitenText(zeiten = GEMEINDE.rathaus.zeiten) {
  return [1, 2, 3, 4, 5].map((t) => {
    const z = zeiten[t];
    return z && z.length ? `${TAG[t]} ${z.map(spanne).join(' und ')} Uhr` : `${TAG[t]} geschlossen`;
  }).join(', ');
}

export const standText = (iso) => (iso ? iso.split('-').reverse().join('.') : '');
