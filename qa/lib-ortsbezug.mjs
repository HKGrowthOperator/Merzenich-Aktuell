/**
 * Ortsbezug (Pflicht seit 19.11.2026, Entscheidung des Betreibers am 18.11.2026):
 * Jede Claude-Meldung trägt im Feld `ortsbezug` einen Satz, was konkret in der Gemeinde
 * Merzenich passiert oder wen in Merzenich es betrifft (docs/redaktion/SCHREIBREGELN.md 0b).
 * Der Satz muss nach Abzug allgemeiner Wendungen („für Merzenich und Region“, „auch für
 * Merzenich“, „Kreis Düren, also auch Merzenich“) noch einen Ortsnamen der Gemeinde oder eine
 * Merzenicher Einrichtung nennen. Anlass: Meldung „Tamilische Feste für Merzenich und Region“
 * (17.11.2026) ohne jeden Bezug zum Ort, auf Wunsch des Betreibers zurückgezogen.
 */
export const ORTE = /\b(Merzenich(?:er|s)?|Golzheim(?:er)?|Girbelsrath(?:er)?|Morschenich(?:er)?|Bürgewald(?:er)?|Buergewald)\b/i;
// Merzenicher Einrichtungen, die ohne Ortsnamen genannt werden.
export const EINRICHTUNGEN = /\b(Rathaus am Valdersweg|Bürgerhaus|Gemeinderat|Gemeindeverwaltung|Löschgruppe|KGS Am Weinberg|SC 1919|Heimatmuseum|Maarhalle|Lindenplatz)\b/i;
// Allgemeine Wendungen, die keinen Ortsbezug herstellen.
const ALLGEMEIN = [
  /\b(?:für|in|aus)\s+(?:die\s+)?(?:Gemeinde\s+)?Merzenich(?:er)?\s+(?:und|sowie)\s+(?:die\s+)?(?:Region|Umgebung|Umland|Umkreis|Nachbarschaft|Kreis(?:\s+Düren)?)\b/gi,
  /\bMerzenich(?:er)?\s+(?:und|sowie)\s+(?:die\s+)?(?:Region|Umgebung|Umland|Umkreis)\b/gi,
  /\bauch\s+(?:für|in|aus|nach)\s+(?:die\s+)?(?:Gemeinde\s+)?Merzenich(?:er)?\b/gi,
  /\b(?:also|damit|somit)\s+auch\s+(?:aus|in|für)?\s*(?:der\s+Gemeinde\s+)?Merzenich(?:er)?\b/gi,
  /\b(?:wie|etwa|zum Beispiel|z\.\s?B\.)\s+(?:auch\s+)?Merzenich\b/gi,
];

/** Fehlertext oder '' wenn der Ortsbezug trägt. */
export function ortsbezugPruefen(text) {
  const t = String(text ?? '').trim();
  if (!t) return 'Feld ortsbezug fehlt: ein Satz, was konkret in der Gemeinde Merzenich passiert oder wen in Merzenich es betrifft';
  if (t.split(/\s+/).length < 6) return 'ortsbezug zu kurz: ein ganzer Satz mit Ort, Anlass und Betroffenen';
  let rest = t;
  for (const re of ALLGEMEIN) rest = rest.replace(re, ' ');
  if (!ORTE.test(rest) && !EINRICHTUNGEN.test(rest)) return 'ortsbezug zu allgemein: kein Ort der Gemeinde (Merzenich, Golzheim, Girbelsrath, Morschenich, Bürgewald) und keine Merzenicher Einrichtung außer in Wendungen wie „für Merzenich und Region“';
  return '';
}
