#!/usr/bin/env node
/**
 * Merzenich Aktuell - Waechter fuer news-sitemap.xml (02.11.2026).
 *
 * Google News liest nur Meldungen der letzten 48 Stunden aus news-sitemap.xml. Ist sie leer,
 * wirkt die Seite fuer Google eingeschlafen (so geschehen vom 28.10. bis 02.11.2026). Der
 * Waechter liest die Sitemap und meldet Alarm, wenn sie leer ist oder die neueste Meldung
 * aelter als --stunden ist (Mindesttakt, SCHREIBREGELN.md Abschnitt 0).
 *
 * Aufruf:
 *   node qa/news-sitemap-waechter.mjs                 Ergebnis als Text, Exit 1 bei Alarm
 *   node qa/news-sitemap-waechter.mjs --json          Ergebnis als JSON
 *   Optionen: --url <sitemap>  --stunden 24
 * Exit 2: Sitemap nicht abrufbar oder kein gueltiges XML.
 */
const args = process.argv.slice(2);
const opt = (n, d) => { const i = args.indexOf(n); return i >= 0 ? args[i + 1] : d; };
const URL_SITEMAP = opt('--url', 'https://merzenich-aktuell.de/news-sitemap.xml');
const STUNDEN = Number(opt('--stunden', '24'));

let xml;
try {
  const antwort = await fetch(`${URL_SITEMAP}${URL_SITEMAP.includes('?') ? '&' : '?'}waechter=${Date.now()}`, {
    headers: { 'User-Agent': 'MerzenichAktuell-Waechter/1.0', 'Cache-Control': 'no-cache' },
    signal: AbortSignal.timeout(60000),
  });
  if (!antwort.ok) throw new Error(`HTTP ${antwort.status}`);
  xml = await antwort.text();
} catch (e) {
  console.error(`news-sitemap.xml nicht abrufbar: ${e.message}`);
  process.exit(2);
}
if (!/<urlset[\s>]/.test(xml)) { console.error('news-sitemap.xml ohne <urlset>'); process.exit(2); }

const eintraege = [...xml.matchAll(/<url>([\s\S]*?)<\/url>/g)].map(([, block]) => ({
  url: (/<loc>([^<]+)<\/loc>/.exec(block) || [])[1] || '',
  titel: (/<news:title>([^<]+)<\/news:title>/.exec(block) || [])[1] || '',
  datum: (/<news:publication_date>([^<]+)<\/news:publication_date>/.exec(block) || [])[1] || '',
}));
const zeiten = eintraege.map((e) => Date.parse(e.datum)).filter((t) => !Number.isNaN(t));
const neueste = zeiten.length ? Math.max(...zeiten) : null;
const alterStunden = neueste === null ? null : Math.floor((Date.now() - neueste) / 3600000);

const gruende = [];
if (!eintraege.length) gruende.push('news-sitemap.xml ist leer: seit mindestens 48 Stunden keine Meldung, Google News sieht nichts Neues');
else if (alterStunden !== null && alterStunden >= STUNDEN) gruende.push(`neueste Meldung ist ${alterStunden} Stunden alt (Mindesttakt: mindestens zwei Meldungen je Tag)`);

const ergebnis = {
  sitemap: URL_SITEMAP,
  eintraege: eintraege.length,
  neueste: neueste === null ? null : new Date(neueste).toISOString(),
  alterStunden,
  alarm: gruende.length > 0,
  gruende,
  meldungen: eintraege.slice(0, 10),
};

if (args.includes('--json')) console.log(JSON.stringify(ergebnis, null, 2));
else {
  console.log(`${URL_SITEMAP}: ${eintraege.length} Eintraege, neueste ${ergebnis.neueste || '–'} (${alterStunden ?? '–'} Stunden)`);
  for (const e of eintraege.slice(0, 10)) console.log(`  ${e.datum}  ${e.titel}`);
  console.log(ergebnis.alarm ? `ALARM: ${gruende.join('; ')}` : 'In Ordnung.');
}
// exitCode statt process.exit: laesst offene fetch-Verbindungen sauber schliessen (unter Windows sonst libuv-Absturz).
process.exitCode = ergebnis.alarm ? 1 : 0;
