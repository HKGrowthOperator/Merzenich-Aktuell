#!/usr/bin/env node
/**
 * Baut die Vermarktungsseite /werben/ aus den aktuell angebotenen Formaten.
 * Ziel: keine erfundenen Werbeplaetze, sondern dieselben Demo-Motive und
 * Darstellungen wie im ausgelieferten Werbesystem.
 *
 * Aufruf: node deploy/werben.mjs [--check]
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const pfad = join(wurzel, 'chatgpt-site', 'werben', 'index.html');
const nurPruefen = process.argv.includes('--check');
const daten = JSON.parse(readFileSync(join(wurzel, 'deploy', 'anzeigen.json'), 'utf8'));
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
// Bildnachweise aus derselben Datei wie die Credits unter den Werbeflaechen
// (deploy/werben-bilder.mjs schreibt sie), statt einer zweiten Handkopie.
const credits = JSON.parse(readFileSync(join(wurzel, 'chatgpt-site', 'assets', 'werben', 'credits.json'), 'utf8')).images;
const autor = (a) => String(a || '').replace(/\s*\(Photography\).*$/s, '').replace(/,\s*https?:\/\/\S+/g, '').trim();
const bildnachweise = credits.map((c) => `<p><strong>${esc(String(c.purpose || c.id).replace(/^(Auswahlkarte|Demo) /, ''))}:</strong> ${esc(String(c.sourceTitle || '').replace(/^File:/, '').replace(/\.[a-z]+$/i, ''))} · ${esc(autor(c.author))} · <a href="${esc(c.sourceUrl)}" target="_blank" rel="noopener">${esc(c.source || 'Quelle')}</a> · <a href="${esc(c.licenseUrl)}" target="_blank" rel="noopener license">${esc(c.license)}</a>.</p>`).join('\n');

function karte(m, format) {
  return `<a class="ma-ad-card ma-ad-card--${format} ma-ad-theme--${esc(m.typ)}" href="/anzeigen/aufgeben/" data-motiv="${esc(m.id)}" aria-label="Musteranzeige: ${esc(m.kunde)} – ${esc(m.headline)}"><span class="ma-ad-art ma-ad-art--${esc(m.typ)}"><img src="${esc(m.bild)}" alt="${esc(m.bildAlt)}" loading="lazy" decoding="async"></span><span class="ma-ad-copy"><span class="ma-ad-eyebrow">${esc(m.eyebrow)}</span><strong>${esc(m.headline)}</strong><span class="ma-ad-text">${esc(m.text)}</span><span class="ma-ad-cta">${esc(m.cta)}</span>${format === 'gap' ? '<span class="ma-ad-muster">Musteranzeige</span>' : ''}</span></a>`;
}
function preview(format, versatz) {
  const m = daten.motive[versatz % daten.motive.length];
  return `<aside class="werbung werbung--preview" data-werbung="preview" data-format="${format}" data-versatz="${versatz}" aria-label="Musteranzeige"><span class="werbung-label">Musteranzeige · so kann die Fläche aussehen</span><div class="werbung-flaeche ma-ad-rotator ma-ad-rotator--${format}">${karte(m, format)}</div></aside>`;
}

const alt = readFileSync(pfad, 'utf8');
const form = alt.match(/<section><h2>Mediadaten anfragen<\/h2><form class="form"[\s\S]*?<\/form><\/section>/);
if (!form) {
  console.error('Werben: Mediadaten-Formular nicht gefunden.');
  process.exit(2);
}

const haupt = `<main id="main">
<section class="werben-entry" aria-labelledby="werben-weg"><div class="shell"><div class="werben-entry__panel"><div class="werben-entry__head"><span class="eyebrow">Einfach sichtbar werden</span><h2 id="werben-weg">Wie möchten Sie sichtbar werden?</h2><p>Drei Wege, die tatsächlich auf Merzenich Aktuell vorgesehen sind. Jede bezahlte Platzierung wird klar gekennzeichnet und vor Veröffentlichung geprüft.</p></div><div class="werben-choice-grid">
<a class="werben-choice" href="#werbeflaechen"><span class="werben-choice__media"><img src="/assets/werben/werbebanner.jpg" alt="Helles Café-Interieur als Beispiel für lokale Werbesichtbarkeit" loading="eager" decoding="async"></span><span class="werben-choice__body"><strong>Werbebanner</strong><span>Feste, responsive Werbeflächen zwischen redaktionellen Bereichen und im Artikelumfeld.</span><span class="werben-choice__cta">Platzierungen ansehen</span></span></a>
<a class="werben-choice" href="/anzeigen/aufgeben/"><span class="werben-choice__media"><img src="/assets/werben/tipp-sponsoring.jpg" alt="Veranstaltung mit Fahrgeschäften als Beispiel für Tipp und Sponsoring" loading="lazy" decoding="async"></span><span class="werben-choice__body"><strong>Tipp / Sponsoring</strong><span>Gekennzeichnete Präsenz für Veranstaltungen, Vereine, Angebote und lokale Partner.</span><span class="werben-choice__cta">Anfrage starten</span></span></a>
<a class="werben-choice" href="/anzeigen/aufgeben/"><span class="werben-choice__media"><img src="/assets/werben/unternehmensprofil.jpg" alt="Modernes Büro-Interieur als Beispiel für ein Unternehmensprofil" loading="lazy" decoding="async"></span><span class="werben-choice__body"><strong>Unternehmensprofil</strong><span>Dauerhafte lokale Präsenz mit Bild, Informationen und Kontakt im Wirtschaftsbereich.</span><span class="werben-choice__cta">Profil anfragen</span></span></a>
</div></div></div></section>
<div class="page-head"><div class="shell"><nav class="crumbs" aria-label="Brotkrumen"><a href="/">Start</a><span class="sep">›</span><span aria-current="page">Werben auf Merzenich Aktuell</span></nav><span class="eyebrow">Mediadaten</span><h1>Lokal sichtbar. Klar gekennzeichnet.</h1><p class="desc">Werbung für Unternehmen, Vereine und Veranstalter aus Merzenich und der Region – in den Flächen, die auf der Seite wirklich vorgesehen sind.</p></div></div>
<section class="section" id="werbeflaechen"><div class="shell media-kit"><div class="media-kit-intro"><span class="eyebrow">Echte Platzierungen</span><h2>So erscheint Ihre Werbung auf Merzenich Aktuell.</h2><p>Die Vorschauen verwenden dasselbe Demo-Anzeigensystem wie die Website. Keine erfundenen Bannergrößen und keine zusätzlichen Flächen nur für die Mediadaten.</p></div>
<div class="media-formats media-formats--real">
<article class="media-format"><div class="media-slot-preview">${preview('band', 0)}</div><span class="eyebrow">Zwischen den Nachrichten</span><h3>Startseiten-Werbeband</h3><p>Responsive Werbefläche zwischen redaktionellen Bereichen. Die vorhandenen Startseitenplätze nutzen dieselbe Darstellung.</p><span class="media-format__meta">Desktop und Mobile · klar als Anzeige gekennzeichnet</span></article>
<article class="media-format"><div class="media-slot-preview">${preview('band', 1)}</div><span class="eyebrow">Im Artikelumfeld</span><h3>Artikelanzeige</h3><p>Breite, responsive Platzierung im Umfeld von Beiträgen, ohne den eigentlichen Lesetext zu überdecken.</p><span class="media-format__meta">Responsive · redaktionell getrennt</span></article>
<article class="media-format media-format--desktop-only"><div class="media-slot-preview media-slot-preview--sidebar">${preview('gap', 2)}</div><span class="eyebrow">Desktop-Seitenspalte</span><h3>Sidebar</h3><p>Kompakte Platzierung in dafür vorgesehenen Seitenspalten. Auf kleinen Displays wird sie nicht erzwungen.</p><span class="media-format__meta">Desktop · nur dort, wo eine Seitenspalte vorgesehen ist</span></article>
</div>
<p class="werben-demo-note"><strong>Hinweis:</strong> Die gezeigten Unternehmen sind Musteranzeigen. Sie zeigen ausschließlich die spätere Darstellung. Echte Kampagnen erscheinen nur nach Buchung und redaktioneller Freigabe.</p>
<details class="werben-bildnachweise"><summary>Bildnachweise der Musterfotos</summary><div class="werben-bildnachweise__grid">
${bildnachweise}
</div></details>
<div class="media-principles"><section><h2>Redaktion bleibt unabhängig.</h2><p>Werbung und Finanzierung bestimmen weder die Themenauswahl noch die Berichterstattung. Jede bezahlte Platzierung wird gekennzeichnet.</p><p>Merzenich Aktuell wird von KBS Management GmbH und AJ Sports Entertainment getragen. <a href="/ueber-uns/#finanzierung">Mehr zur Finanzierung</a></p></section><section><h2>Reichweite & Konditionen</h2><p>Für die Startphase liegen noch keine belastbaren Reichweitenzahlen vor. Wir nennen keine geschätzten Leserzahlen. Verfügbare Platzierungen und Konditionen erhalten Sie auf Anfrage.</p><p>Freie Werbeflächen werden nur dort angeboten, wo die Website bereits einen dafür vorgesehenen Platz hat.</p></section></div>
${form[0]}</div></section>
`;

const mainTag = alt.match(/<main[^>]*id="main"[^>]*>/);
const start = mainTag ? alt.indexOf(mainTag[0]) : -1;
const marker = start >= 0 ? alt.indexOf('<!-- werbung:artikel:start -->', start) : -1;
const mainEnd = start >= 0 ? alt.indexOf('</main>', start) : -1;
const ende = marker >= 0 ? marker : mainEnd;
if (start < 0 || ende < 0) {
  console.error('Werben: Hauptbereich konnte nicht ersetzt werden.');
  process.exit(2);
}
let neu = alt.slice(0, start) + haupt + alt.slice(ende);
const geaendert = neu !== alt;
if (geaendert && !nurPruefen) writeFileSync(pfad, neu);
console.log(`Werben: reale Formate + Foto-Vorschauen ${geaendert ? (nurPruefen ? 'nicht aktuell' : 'geschrieben') : 'aktuell'}.`);
if (nurPruefen && geaendert) process.exit(2);
