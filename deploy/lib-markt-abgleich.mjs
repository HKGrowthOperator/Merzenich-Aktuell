/**
 * Merzenich Aktuell - Entscheidungen des Markt-Abgleichs, ohne Netz und ohne
 * Dateien, damit qa/markt-abgleich-test.mjs sie mit Rohdaten pruefen kann.
 */

/**
 * Ergebnis einer Abfrage bei der Quelle.
 * status: HTTP-Status oder 'fehler: ...'; vorher/nachher: angefragte und
 * zuletzt erreichte Adresse (nach Weiterleitungen).
 *   200 auf derselben Anzeige  -> online
 *   404/410                    -> entfernt
 *   alles andere               -> unklar (gesperrt, Fehler, Weiterleitung
 *                                 auf eine andere Seite wie die Suche)
 */
export function klassifiziere(status, vorher = '', nachher = '') {
  if (status === 404 || status === 410) return 'entfernt';
  if (status !== 200) return 'unklar';
  if (vorher && nachher && !gleicheAnzeige(vorher, nachher)) return 'unklar';
  return 'online';
}

/** Bleibt die Weiterleitung bei derselben Anzeige (gleicher Pfad, nur Schreibweise/Abfrage anders)? */
export function gleicheAnzeige(vorher, nachher) {
  try {
    const a = new URL(vorher), b = new URL(nachher);
    const pfad = (u) => u.pathname.replace(/\/+$/, '').toLowerCase();
    return a.hostname.replace(/^www\./, '') === b.hostname.replace(/^www\./, '') && pfad(a) === pfad(b);
  } catch {
    return false;
  }
}

/**
 * Darf das Ergebnis geschrieben werden? Nein, wenn mehr als die Haelfte unklar
 * oder angeblich entfernt ist: Dann ist eher die Quelle gestoert oder gesperrt,
 * und der Markt soll nicht leerlaufen.
 */
export function schreibbar(ergebnisse) {
  const n = ergebnisse.length;
  const zahl = (e) => ergebnisse.filter((x) => x === e).length;
  if (!n) return { ok: false, grund: 'keine Eintraege' };
  if (zahl('unklar') > n / 2) return { ok: false, grund: 'mehr als die Haelfte unklar' };
  if (zahl('entfernt') > n / 2) return { ok: false, grund: 'mehr als die Haelfte angeblich entfernt' };
  return { ok: true, grund: '' };
}
