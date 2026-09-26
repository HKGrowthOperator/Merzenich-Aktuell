#!/usr/bin/env node
/**
 * Anzeige-Assistent /anzeigen/aufgeben/ (KBS/Ordin 26.09.2026): fuenf Schritte
 * statt einer langen Seite, zu jeder Auswahl ein passendes Bild.
 *   1 Art   2 Welche genau (Bildkarten)   3 Angaben   4 Text & Bilder   5 Kontakt & Uebersicht
 * Daten: deploy/anzeigen-assistent.json. Die Feldnamen bleiben die, die
 * /api/formular kennt. Ohne JavaScript steht das ganze Formular untereinander
 * und laesst sich absenden; assets/anzeigen-assistent.js zeigt Schritt fuer
 * Schritt, merkt den Stand in der Adresse und macht "Zurueck" im Browser moeglich.
 * Aufruf: node deploy/anzeigen-assistent.mjs [--check]
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { join, dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { bild } from './lib-piktogramme.mjs';

const wurzel = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const pfad = join(wurzel, 'chatgpt-site', 'anzeigen', 'aufgeben', 'index.html');
const nurPruefen = process.argv.includes('--check');
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const daten = JSON.parse(readFileSync(join(wurzel, 'deploy', 'anzeigen-assistent.json'), 'utf8'));

const karte = (name, o, pflicht) => `<label class="anz-karte"><input type="radio" name="${esc(name)}" value="${esc(o.wert)}"${pflicht ? ' required' : ''}>`
  + bild(o.bild) + `<span class="anz-karte__titel">${esc(o.titel)}</span>` + (o.text ? `<span class="anz-karte__text">${esc(o.text)}</span>` : '') + '</label>';

// Angaben je Art (Schritt 3): dieselben Felder wie bisher.
const ORTSTEIL = '<label>Ortsteil<select name="ortsteil"><option value="">Ortsteil wählen …</option><option>Merzenich</option><option>Golzheim</option><option>Girbelsrath</option><option>Morschenich</option><option>Bürgewald</option><option>Außerhalb / Region</option></select></label>';
const ANGABEN = {
  Immobilie: `<div class="grid2">${ORTSTEIL}<label>Preis oder Miete (optional)<input name="preis" placeholder="z. B. 890 € kalt"></label></div><div class="grid2"><label>Wohnfläche (optional)<input name="flaeche" placeholder="z. B. 95 m²"></label><label>Zimmer (optional)<input name="zimmer" inputmode="decimal"></label></div>`,
  Traueranzeige: '<label>Name der verstorbenen Person<input name="verstorben"></label><div class="grid2"><label>Lebensdaten (optional)<input name="lebensdaten" placeholder="z. B. 12.3.1941 bis 18.9.2026"></label><label>Trauerfeier (optional)<input name="trauerfeier" placeholder="Datum, Uhrzeit, Ort"></label></div><label class="check"><input type="checkbox" name="angehoerig" value="ja"><span>Ich bin Angehörige oder Angehöriger oder handle in deren Auftrag.</span></label>',
  Familienanzeige: '<div class="grid2"><label>Datum des Anlasses (optional)<input type="date" name="anlassdatum"></label><label>Namen, die genannt werden sollen<input name="namen" placeholder="z. B. Anna und Jonas Beispiel"></label></div><label class="check"><input type="checkbox" name="einverstaendnis" value="ja"><span>Alle genannten und abgebildeten Personen sind mit der Veröffentlichung einverstanden.</span></label>',
  Werbung: '<div class="grid2"><label>Firma oder Verein<input name="firma" autocomplete="organization"></label><label>Gewünschter Zeitraum (optional)<input name="zeitraum" placeholder="z. B. Oktober bis Dezember"></label></div><p class="anz-hinweis">Alle Werbeformen: Preis auf Anfrage. Werbung ist auf Merzenich Aktuell immer als Anzeige gekennzeichnet.</p>',
};

const SCHRITTE = ['Art', 'Auswahl', 'Angaben', 'Text & Bilder', 'Kontakt'];
const schritt = (n, titel, innen) => `<fieldset class="anz-schritt" data-schritt="${n}"><legend><span class="anz-nr">${n}</span>${esc(titel)}</legend>${innen}</fieldset>`;

const fehler = [];
for (const a of daten.arten) { if (!ANGABEN[a.wert]) fehler.push(`Angaben fuer ${a.wert} fehlen`); bild(a.bild); for (const g of a.auswahl) for (const o of g.optionen) bild(o.bild); }

const formular = '<!-- assistent:start -->'
  + '<form class="form anz-form anz-assistent" name="anzeige" method="POST" action="/api/formular" enctype="multipart/form-data" id="formular" data-assistent>'
  + '<input type="hidden" name="weiter" value="/anzeigen/aufgeben/danke/"><input type="hidden" name="form-name" value="anzeige">'
  + '<p class="hp" aria-hidden="true"><label>Website<input name="bot-field" tabindex="-1" autocomplete="off"></label></p>'
  + `<ol class="anz-fortschritt" hidden>${SCHRITTE.map((t, i) => `<li data-fuer="${i + 1}"><span>${i + 1}</span>${esc(t)}</li>`).join('')}</ol>`
  + schritt(1, 'Was möchten Sie veröffentlichen?', `<div class="anz-karten anz-karten--gross">${daten.arten.map((a) => karte('art', a, true)).join('')}</div>`)
  + schritt(2, 'Was genau?', daten.arten.map((a) => `<div class="anz-teil" data-art="${esc(a.wert)}">`
      + a.auswahl.map((g) => `<fieldset class="anz-gruppe"><legend>${esc(g.frage)}</legend><div class="anz-karten">${g.optionen.map((o) => karte(g.feld, o, false)).join('')}</div></fieldset>`).join('')
      + '</div>').join(''))
  + schritt(3, 'Angaben zur Anzeige', daten.arten.map((a) => `<div class="anz-teil" data-art="${esc(a.wert)}"><p class="anz-teil__titel">${esc(a.titel)}</p>${ANGABEN[a.wert]}</div>`).join(''))
  + schritt(4, 'Text und Bilder', '<label>Anzeigentext<textarea name="text" rows="7" required placeholder="Was soll in der Anzeige stehen? Bei Werbung: Was möchten Sie erreichen?"></textarea></label>'
      + '<div class="grid2"><label>Gewünschter Erscheinungstermin (optional)<input type="date" name="erscheinen"></label><label>Bilder (optional, bis zu drei, je höchstens 6 MB)<input type="file" name="bild" accept="image/*" multiple></label></div>'
      + '<label class="check"><input type="checkbox" name="bildrechte" value="ja"><span>Ich besitze die Rechte an den Bildern und erlaube die Veröffentlichung in dieser Anzeige.</span></label>')
  + schritt(5, 'Ihre Kontaktdaten', '<div class="grid2"><label>Ihr Name<input name="name" required autocomplete="name"></label><label>E-Mail<input type="email" name="email" required autocomplete="email"></label></div>'
      + '<label>Telefon (optional, für Rückfragen)<input type="tel" name="telefon" autocomplete="tel"></label>'
      + '<div class="anz-uebersicht" hidden aria-live="polite"></div>'
      + '<label class="check"><input type="checkbox" name="einwilligung" required value="ja"><span>Ich habe die <a href="/datenschutz/">Datenschutzhinweise</a> gelesen und bin mit der Verarbeitung meiner Angaben zur Bearbeitung meiner Anzeige einverstanden.</span></label>'
      + '<button class="btn" type="submit">Anzeige an die Redaktion senden</button>'
      + '<p class="form-note">Nichts erscheint automatisch. Die Redaktion prüft jede Anzeige und meldet sich per E-Mail, bevor sie veröffentlicht wird.</p>')
  + '<div class="anz-navigation" hidden><button type="button" class="btn ghost" data-zurueck>Zurück</button><button type="button" class="btn" data-weiter>Weiter</button></div>'
  + '</form><!-- assistent:end -->';

const alt = readFileSync(pfad, 'utf8');
let neu;
const MARKE = /<!-- assistent:start -->[\s\S]*?<!-- assistent:end -->/;
if (MARKE.test(alt)) neu = alt.replace(MARKE, () => formular);
else {
  // Einmalige Umstellung vom bisherigen Formular.
  const i = alt.indexOf('<form class="form anz-form"');
  const j = i >= 0 ? alt.indexOf('</form>', i) : -1;
  if (j < 0) fehler.push('anzeigen/aufgeben: Formular nicht gefunden');
  else neu = alt.slice(0, i) + formular + alt.slice(j + '</form>'.length);
}
if (neu) {
  neu = neu.replace('In drei Schritten: Art wählen, Angaben machen, Kontakt hinterlassen. Die Redaktion meldet sich vor der Veröffentlichung.', 'In fünf kurzen Schritten: Art wählen, Details, Angaben, Text und Kontakt. Die Redaktion meldet sich vor der Veröffentlichung.');
  if (!neu.includes('/assets/anzeigen-assistent.js')) neu = neu.replace(/<script src="\/assets\/app\.js[^"]*" defer><\/script>/, (m) => m + '<script src="/assets/anzeigen-assistent.js" defer></script>');
}
if (fehler.length) { console.error('Anzeige-Assistent: ' + fehler.join('; ')); process.exit(2); }
const geaendert = neu !== alt;
if (geaendert && !nurPruefen) writeFileSync(pfad, neu);
console.log(`Anzeige-Assistent: ${daten.arten.length} Arten, ${daten.arten.reduce((n, a) => n + a.auswahl.reduce((m, g) => m + g.optionen.length, 0), 0)} Bildkarten; ${geaendert ? (nurPruefen ? 'nicht aktuell' : 'geschrieben') : 'aktuell'}.`);
if (nurPruefen && geaendert) process.exit(2);
