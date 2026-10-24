// Aufnahmen für Anleitung (PDF) und Erklärvideo, nur in einem lokalen WordPress mit Muster-Zugängen
// (muster-firma: Unternehmens-Partner mit Profil „Muster GmbH“, muster-verein: Vereins-Partner).
// Nie gegen die Live-Seite: das Skript legt Beiträge an und reicht sie ein.
// Aufruf: AUFNAHME_DIR=<ordner> WP_URL=http://127.0.0.1:8090 MUSTER_PW_DATEI=<datei> node aufnahme.mjs <kapitel|alle>
// Braucht im Ordner material/golzheim.jpg (Testfoto). Ergebnis: shots/<name>.png (+ marken.json mit
// markierten Bereichen), clips/<kapitel>.webm und clips/zeiten.json; weiter mit bauen.mjs.
import { createRequire } from 'module'; const require = createRequire(process.env.NODE_PATH ? process.env.NODE_PATH + '/' : import.meta.url);
const { chromium } = require('playwright');
import { readFileSync, writeFileSync, mkdirSync, renameSync, existsSync } from 'fs';
const H = process.env.AUFNAHME_DIR || process.cwd();
const B = process.env.WP_URL || 'http://127.0.0.1:8090';
if (!/^http:\/\/(127\.0\.0\.1|localhost)(:\d+)?$/.test(B)) { console.error('Nur lokal (127.0.0.1/localhost).'); process.exit(2); }
const PW = readFileSync(process.env.MUSTER_PW_DATEI || `${H}/.muster-pw`, 'utf8').trim();
const W = 1440, HH = 900;
for (const d of ['shots', 'clips', 'state']) mkdirSync(`${H}/${d}`, { recursive: true });
const MARKEN = existsSync(`${H}/shots/marken.json`) ? JSON.parse(readFileSync(`${H}/shots/marken.json`, 'utf8')) : {};
const browser = await chromium.launch({ args: ['--lang=de-DE'], env: { ...process.env, LANG: 'de_DE.UTF-8', LANGUAGE: 'de' } });
const fehler = [];
// Zeitmarken je Clip (Sekunden ab Start der Aufnahme) für die Einblendungen im Video.
const ZEITEN = existsSync(`${H}/clips/zeiten.json`) ? JSON.parse(readFileSync(`${H}/clips/zeiten.json`, 'utf8')) : {};
let AKT = null;
const zeit = (was) => { if (AKT?.name) (ZEITEN[AKT.name] ||= []).push([was, Math.round((Date.now() - AKT.t0) / 100) / 10]); };

// Sichtbarer Mauszeiger (Playwright zeichnet keinen) mit Klick-Welle.
const ZEIGER = `(() => { const mach = () => { if (document.getElementById('ma-zeiger') || !document.body) return;
  const z = document.createElement('div'); z.id = 'ma-zeiger';
  z.innerHTML = '<svg width="26" height="26" viewBox="0 0 24 24"><path d="M4 2l15 9-6.5 1.6L16 20l-2.6 1.3-3.4-7.3L4 18z" fill="#151412" stroke="#fff" stroke-width="1.6" stroke-linejoin="round"/></svg>';
  z.style.cssText = 'position:fixed;left:0;top:0;z-index:2147483647;pointer-events:none;transform:translate(720px,450px);transition:transform .7s cubic-bezier(.22,1,.36,1)';
  document.body.appendChild(z);
  const w = document.createElement('div'); w.id = 'ma-welle';
  w.style.cssText = 'position:fixed;left:0;top:0;width:36px;height:36px;margin:-18px 0 0 -18px;border-radius:50%;border:3px solid #971725;opacity:0;z-index:2147483646;pointer-events:none';
  document.body.appendChild(w); };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mach); else mach(); })();`;

async function neu(name, state) {
  const ctx = await browser.newContext({ viewport: { width: W, height: HH }, locale: 'de-DE', ...(state ? { storageState: state } : {}),
    ...(name ? { recordVideo: { dir: `${H}/clips/roh`, size: { width: W, height: HH } } } : {}) });
  await ctx.addInitScript(ZEIGER);
  const p = await ctx.newPage(); p.setDefaultTimeout(30000);
  p.on('console', (m) => { if (m.type() === 'error') fehler.push(`${name}: ${m.text().slice(0, 140)}`); });
  p.on('response', (r) => { if (r.status() >= 400) fehler.push(`${name}: HTTP ${r.status()} ${r.request().method()} ${r.url().replace(B, '')} ${r.request().postData()?.slice(0, 80) || ''}`); });
  p.on('pageerror', (e) => fehler.push(`${name}: ${e.message.slice(0, 140)}`));
  const k = { ctx, p, name, t0: Date.now() }; if (name) { AKT = k; ZEITEN[name] = []; } return k;
}
async function fertig(k) {
  const v = k.p.video(); await k.ctx.close();
  if (v) { const pfad = await v.path(); renameSync(pfad, `${H}/clips/${k.name}.webm`); console.log('Clip', k.name); }
  if (k.name) { ZEITEN[k.name].push(['ende', Math.round((Date.now() - k.t0) / 100) / 10]); writeFileSync(`${H}/clips/zeiten.json`, JSON.stringify(ZEITEN, null, 1)); AKT = null; }
}
const ruhe = (p, ms = 900) => p.waitForTimeout(ms);
async function zeiger(p, x, y) {
  await p.evaluate(([x, y]) => { const z = document.getElementById('ma-zeiger'); if (z) z.style.transform = `translate(${x}px,${y}px)`; }, [x, y]).catch(() => {});
  await p.mouse.move(x, y, { steps: 8 }); await p.waitForTimeout(720);
}
async function hin(p, loc) { await loc.scrollIntoViewIfNeeded().catch(() => {}); const b = await loc.boundingBox(); if (!b) throw new Error('nicht sichtbar'); await zeiger(p, b.x + Math.min(b.width / 2, 60), b.y + b.height / 2); return b; }
async function klick(p, loc, warten = 900) {
  const b = await hin(p, loc); zeit('klick');
  await p.evaluate(([x, y]) => { const w = document.getElementById('ma-welle'); if (!w) return; w.style.transition = 'none'; w.style.left = x + 'px'; w.style.top = y + 'px'; w.style.opacity = '1'; w.style.transform = 'scale(.4)';
    requestAnimationFrame(() => { w.style.transition = 'transform .5s ease-out, opacity .5s ease-out'; w.style.transform = 'scale(1.6)'; w.style.opacity = '0'; }); }, [b.x + Math.min(b.width / 2, 60), b.y + b.height / 2]).catch(() => {});
  await loc.click(); await ruhe(p, warten);
}
async function tippe(p, loc, text, delay = 28) { await klick(p, loc, 300); await p.keyboard.type(text, { delay }); await ruhe(p, 500); }
async function bild(p, name, marken = []) {
  zeit(name);
  const boxes = [];
  for (const [nr, loc] of marken) { const b = await loc.boundingBox().catch(() => null); if (b) boxes.push({ nr, x: Math.round(b.x), y: Math.round(b.y), w: Math.round(b.width), h: Math.round(b.height) }); }
  await p.evaluate(() => { const z = document.getElementById('ma-zeiger'); if (z) z.style.visibility = 'hidden'; }).catch(() => {});
  await p.screenshot({ path: `${H}/shots/${name}.png` });
  await p.evaluate(() => { const z = document.getElementById('ma-zeiger'); if (z) z.style.visibility = 'visible'; }).catch(() => {});
  MARKEN[name] = boxes; writeFileSync(`${H}/shots/marken.json`, JSON.stringify(MARKEN, null, 1));
  console.log('Bild', name, boxes.length ? `(${boxes.length} Marken)` : '');
}
async function anmelden(login) {
  const k = await neu('', null);
  await k.p.goto(B + '/wp-login.php'); await k.p.fill('#user_login', login); await k.p.fill('#user_pass', PW);
  await Promise.all([k.p.waitForLoadState('load'), k.p.click('#wp-submit')]);
  await k.ctx.storageState({ path: `${H}/state/${login}.json` }); await k.ctx.close();
  return `${H}/state/${login}.json`;
}
// Willkommens-Fenster des Block-Editors schließen (falls es kommt).
async function willkommenZu(p) { const z = p.locator('.components-modal__screen-overlay .components-guide button[aria-label="Schließen"], .edit-post-welcome-guide button[aria-label="Schließen"]'); if (await z.count()) await z.first().click().catch(() => {}); }
async function tourAus(p) { await p.waitForSelector('.ma-tour-karte', { timeout: 2500 }).catch(() => {}); const a = p.locator('.ma-tour-karte .ma-tour-aus'); if (await a.count()) await a.click(); await p.waitForTimeout(400); }
const editorText = (p) => p.frameLocator('iframe[name="editor-canvas"]');

const KAPITEL = {
  // 1. Anmelden und Ihr Bereich
  async anmelden() {
    const k = await neu('01-anmelden', null), p = k.p;
    await p.goto(B + '/wp-login.php', { waitUntil: 'load' }); await ruhe(p, 1200);
    await bild(p, '01-anmeldung', [[1, p.locator('#user_login')], [2, p.locator('#user_pass')], [3, p.locator('#wp-submit')]]);
    await tippe(p, p.locator('#user_login'), 'muster-firma', 60);
    await klick(p, p.locator('#user_pass'), 200); await p.keyboard.type(PW.slice(0, 12), { delay: 40 }); await p.fill('#user_pass', PW);
    await klick(p, p.locator('#wp-submit'), 300); await p.waitForLoadState('load');
    if (!p.url().includes('/wp-admin')) await p.goto(B + '/wp-admin/index.php', { waitUntil: 'load' });
    zeit('vor:02-dashboard');
    await ruhe(p, 1500);
    await bild(p, '02-dashboard', [[1, p.locator('#ma_hilfe_sogehts')], [2, p.locator('#ma_partner_bereich')], [3, p.locator('#toplevel_page_ma-hilfe')]]);
    await hin(p, p.locator('#ma_hilfe_sogehts .ma-hilfe-schritte li').nth(0)); await ruhe(p, 1500);
    await hin(p, p.locator('#ma_hilfe_sogehts .ma-hilfe-schritte li').nth(3)); await ruhe(p, 1200);
    await hin(p, p.locator('#ma_partner_bereich .ma-pd__zahlen')); await ruhe(p, 1500);
    await hin(p, p.locator('#ma_partner_bereich a.button-primary')); await ruhe(p, 1200);
    await fertig(k);
  },
  // 2. Neue Meldung: geführte Tour
  async tour() {
    const k = await neu('02-tour', STATE), p = k.p;
    await p.goto(B + '/wp-admin/index.php', { waitUntil: 'load' }); await ruhe(p, 600);
    await klick(p, p.locator('#ma_partner_bereich a.button-primary'), 300); await p.waitForLoadState('load');
    await p.waitForSelector('.ma-tour-karte', { timeout: 20000 }); zeit('vor:03-tour-1'); await willkommenZu(p); await ruhe(p, 1500);
    for (let i = 1; i <= 6; i++) {
      await ruhe(p, 900);
      await bild(p, `03-tour-${i}`);
      await ruhe(p, 1800);
      const w = p.locator('.ma-tour-karte .button-primary');
      if (i < 6) { await klick(p, w, 0); zeit(`vor:03-tour-${i + 1}`); await ruhe(p, 500); } else await klick(p, w, 600);
    }
    await fertig(k);
  },
  // 3. Schreiben, Bild, Rechte, Ortsteil, Einreichen
  async schreiben() {
    const k = await neu('03-schreiben', STATE), p = k.p;
    await p.goto(B + '/wp-admin/post-new.php', { waitUntil: 'load' });
    await p.waitForSelector('iframe[name="editor-canvas"]'); await willkommenZu(p); await tourAus(p); await ruhe(p, 800);
    const f = editorText(p);
    await tippe(p, f.locator('.editor-post-title, .wp-block-post-title').first(), 'Herbstfest in Golzheim: Muster GmbH lädt am 8. November ein');
    await p.keyboard.press('Enter'); await ruhe(p, 400);
    await p.keyboard.type('Die Muster GmbH lädt am Samstag, 8. November, von 11 bis 17 Uhr zum Herbstfest in ihre Werkstatt an der Aachener Straße ein.', { delay: 18 });
    await p.keyboard.press('Enter');
    await p.keyboard.type('Es gibt Führungen durch die Werkstatt, Kaffee und Kuchen. Der Eintritt ist frei.', { delay: 18 });
    await ruhe(p, 1000);
    await bild(p, '04-text', [[1, f.locator('.editor-post-title, .wp-block-post-title').first()], [2, f.locator('.is-root-container p').first()]]);
    // Beitragsbild
    zeit('vor:05-beitragsbild-knopf');
    await p.evaluate(() => { try { wp.data.dispatch('core/edit-post').openGeneralSidebar('edit-post/document'); const es = wp.data.select('core/editor'), ed = wp.data.dispatch('core/editor'); ['featured-image', 'taxonomy-panel-ma_location'].forEach((x) => { if (!es.isEditorPanelOpened(x)) ed.toggleEditorPanelOpened(x); }); } catch (e) {} });
    await ruhe(p, 800);
    const fb = p.locator('.editor-post-featured-image__toggle, .editor-post-featured-image button').first();
    await bild(p, '05-beitragsbild-knopf', [[1, fb]]); await ruhe(p, 1800);
    zeit('vor:06-hochladen'); await klick(p, fb, 1200);
    const tab = p.locator('.media-modal .media-menu-item, .media-modal [role="tab"]').filter({ hasText: 'Dateien hochladen' }).first();
    if (await tab.count()) await klick(p, tab, 800);
    await bild(p, '06-hochladen', [[1, p.locator('.media-modal .upload-ui .browser, .media-modal button.browser').first()]]);
    await hin(p, p.locator('.media-modal .upload-ui .browser, .media-modal button.browser').first());
    await p.locator('.media-modal input[type=file]').first().setInputFiles(`${H}/material/golzheim.jpg`);
    await p.waitForSelector('.media-modal .attachment.selected, .media-modal .attachment-details', { timeout: 60000 }); await ruhe(p, 1500);
    zeit('vor:07-bildangaben');
    const alt = p.locator('.media-modal #attachment-details-alt-text, .media-modal [data-setting="alt"] textarea').first();
    if (await alt.count()) await tippe(p, alt, 'Blick auf Golzheim', 30);
    const feld = (n) => p.locator(`.media-modal input[name$="[${n}]"], .media-modal textarea[name$="[${n}]"]`).first();
    if (await feld('ma_credit').count()) { await tippe(p, feld('ma_credit'), 'Karl-Heinz Meurer', 30); }
    if (await feld('ma_quelle').count()) { await tippe(p, feld('ma_quelle'), 'Wikimedia Commons', 30); }
    if (await feld('ma_nutzung').count()) { await tippe(p, feld('ma_nutzung'), 'Lizenz CC BY-SA 3.0', 30); }
    await p.keyboard.press('Tab'); await ruhe(p, 900);
    await feld('ma_credit').scrollIntoViewIfNeeded().catch(() => {}); await ruhe(p, 400);
    await bild(p, '07-bildangaben', [[1, feld('ma_credit')], [2, feld('ma_nutzung')], [3, p.locator('.media-modal .media-button-select').first()]]); await ruhe(p, 1800);
    await klick(p, p.locator('.media-modal .media-button-select').first(), 0); zeit('vor:08-bildrechte'); await ruhe(p, 1500);
    // Bild- und Nutzungsrechte
    const rechte = p.locator('.components-panel__body-toggle').filter({ hasText: 'Bild- und Nutzungsrechte' }).first();
    if ((await rechte.getAttribute('aria-expanded')) === 'false') await klick(p, rechte, 700);
    const haken = p.locator('.ma-bildrechte input[type=checkbox]').first();
    await hin(p, p.locator('.ma-bildrechte').first()); await ruhe(p, 1200);
    await bild(p, '08-bildrechte', [[1, p.locator('.ma-bildrechte').first()], [2, haken]]); await ruhe(p, 1500);
    await klick(p, haken, 1600);
    // Ortsteil
    zeit('vor:09-ortsteil');
    const orte = p.locator('.components-panel__body-toggle').filter({ hasText: 'Orte' }).first();
    if (await orte.count() && (await orte.getAttribute('aria-expanded')) === 'false') await klick(p, orte, 700);
    const golz = p.locator('.components-panel__body').filter({ has: p.locator('.components-panel__body-toggle', { hasText: 'Orte' }) }).locator('label', { hasText: 'Golzheim' }).first();
    if (await golz.count()) { await klick(p, golz, 900); await bild(p, '09-ortsteil', [[1, golz]]); await ruhe(p, 2200); }
    // Einreichen
    const senden = p.locator('.editor-post-publish-panel__toggle, .editor-post-publish-button__button').first();
    zeit('vor:10-einreichen-knopf'); await hin(p, senden); await ruhe(p, 400);
    await bild(p, '10-einreichen-knopf', [[1, senden]]); await ruhe(p, 1800);
    await klick(p, senden, 0); zeit('vor:11-einreichen-pruefen'); await ruhe(p, 1500);
    const vor = p.locator('.editor-post-publish-panel');
    if (await vor.count()) { await bild(p, '11-einreichen-pruefen', [[1, vor.locator('.editor-post-publish-button').first()]]); await klick(p, vor.locator('.editor-post-publish-button').first(), 0); zeit('vor:12-eingereicht'); await ruhe(p, 2500); }
    await ruhe(p, 1500);
    await bild(p, '12-eingereicht');
    await fertig(k);
  },
  // 4. Status, Änderungen angefordert, Änderung vorschlagen
  async rueckmeldung() {
    const k = await neu('04-rueckmeldung', STATE), p = k.p;
    await p.goto(B + '/wp-admin/index.php', { waitUntil: 'load' }); await ruhe(p, 1200);
    const tab = p.locator('#ma_partner_bereich table');
    zeit('vor:13-status'); await hin(p, tab); await ruhe(p, 800);
    await bild(p, '13-status', [[1, p.locator('#ma_partner_bereich .ma-pd__zahlen')], [2, p.locator('#ma_partner_bereich .ma-pd__notiz').first()], [3, p.locator('#ma_partner_bereich a', { hasText: 'Änderung vorschlagen' }).first()]]);
    await hin(p, p.locator('#ma_partner_bereich .ma-pd__notiz').first()); await ruhe(p, 2200);
    zeit('vor:14-aenderung-angefordert'); await klick(p, p.locator('#ma_partner_bereich td a', { hasText: 'Neue Ausbildungsplätze' }).first(), 300); await p.waitForLoadState('load');
    await p.waitForSelector('iframe[name="editor-canvas"]'); await willkommenZu(p); await tourAus(p); await ruhe(p, 1500);
    await bild(p, '14-aenderung-angefordert', [[1, p.locator('.components-notice, .notice').filter({ hasText: 'bittet um Änderungen' }).first()]]);
    await ruhe(p, 2400);
    zeit('vor:15-aenderung-vorschlagen'); await p.goto(B + '/wp-admin/index.php', { waitUntil: 'load' }); await ruhe(p, 800);
    await klick(p, p.locator('#ma_partner_bereich a', { hasText: 'Änderung vorschlagen' }).first(), 300); await p.waitForLoadState('load');
    await p.waitForSelector('iframe[name="editor-canvas"]').catch(() => {}); await willkommenZu(p); await tourAus(p); await ruhe(p, 1500);
    await bild(p, '15-aenderung-vorschlagen', [[1, p.locator('.components-notice, .notice').filter({ hasText: 'Änderungsvorschlag' }).first()]]);
    await ruhe(p, 1200);
    await fertig(k);
  },
  // 5. Profil und Passwort, Hilfe-Seite
  async profil() {
    const k = await neu('05-profil', STATE), p = k.p;
    await p.goto(B + '/wp-admin/profile.php', { waitUntil: 'load' }); await ruhe(p, 1000);
    await hin(p, p.locator('#email')); await ruhe(p, 1000);
    await bild(p, '16-profil-mail', [[1, p.locator('#email')]]);
    zeit('vor:17-passwort'); const pwk = p.locator('.wp-generate-pw').first();
    await hin(p, pwk); await ruhe(p, 900);
    await bild(p, '17-passwort', [[1, pwk]]); await ruhe(p, 1800);
    zeit('vor:18-hilfe-seite'); await klick(p, p.locator('#toplevel_page_ma-hilfe > a'), 300); await p.waitForLoadState('load'); await ruhe(p, 1200);
    await bild(p, '18-hilfe-seite', [[1, p.locator('.ma-hilfe-knoepfe').first()]]);
    await hin(p, p.locator('.ma-hilfe__fragen details').first()); await ruhe(p, 1500);
    await fertig(k);
  },
  // 6. Unternehmen: Kanal und Anzeige
  async firma() {
    const k = await neu('06-firma', STATE), p = k.p;
    await p.goto(B + '/wp-admin/index.php', { waitUntil: 'load' }); await ruhe(p, 800);
    const kanal = p.locator('#ma_partner_bereich a', { hasText: 'Kanal ansehen' }).first();
    await bild(p, '19-firma-dashboard', [[1, p.locator('#ma_partner_bereich a', { hasText: 'Unternehmensprofil' }).first()], [2, kanal]]);
    await hin(p, kanal); await ruhe(p, 1200);
    zeit('vor:20-anzeige'); await p.goto(B + '/wp-admin/post-new.php?post_type=ma_ad', { waitUntil: 'load' }); await tourAus(p); await ruhe(p, 1200);
    await tippe(p, p.locator('#title'), 'Herbstaktion Muster GmbH', 40);
    const box = p.locator('#ma_ad_details, .postbox').filter({ hasText: 'Werbeschaltung' }).first();
    await p.evaluate(() => window.scrollTo(0, 0)); await ruhe(p, 800);
    await bild(p, '20-anzeige', [[1, p.locator('#title')], [2, box], [3, p.locator('.postbox').filter({ hasText: 'Werbemittel' }).first()], [4, p.locator('#publish')]]);
    await hin(p, p.locator('.postbox').filter({ hasText: 'Werbemittel' }).first()); await ruhe(p, 1500);
    await fertig(k);
  },
  // 7. Verein: Termin einreichen
  async verein() {
    const st = await anmelden('muster-verein');
    const k = await neu('07-verein', st), p = k.p;
    await p.goto(B + '/wp-admin/index.php', { waitUntil: 'load' }); await ruhe(p, 1000);
    const t = p.locator('#ma_partner_bereich a', { hasText: 'Termin einreichen' }).first();
    await bild(p, '21-verein-dashboard', [[1, p.locator('#ma_partner_bereich a.button-primary').first()], [2, t]]);
    await klick(p, t, 300); await p.waitForLoadState('load');
    await p.waitForSelector('iframe[name="editor-canvas"], #title', { timeout: 30000 }).catch(() => {}); await willkommenZu(p); await tourAus(p); await ruhe(p, 1200);
    const titel = editorText(p).locator('.editor-post-title, .wp-block-post-title').first();
    await tippe(p, titel, 'Herbstwanderung des Muster-Vereins', 40);
    await p.keyboard.press('Enter'); await p.keyboard.type('Treffpunkt ist der Parkplatz am Sportplatz. Gäste sind willkommen.', { delay: 18 }); await ruhe(p, 600);
    zeit('vor:22-termin'); const daten = p.locator('#ma-event-details');
    await daten.scrollIntoViewIfNeeded().catch(() => {}); await ruhe(p, 600);
    const beginn = daten.locator('input[type="datetime-local"]').first();
    await klick(p, beginn, 300); await beginn.fill('2026-11-15T10:00'); await ruhe(p, 600);
    const ort = daten.locator('input[type="text"]').first();
    await tippe(p, ort, 'Sportplatz Merzenich', 40);
    await bild(p, '22-termin', [[1, beginn], [2, ort]]);
    await ruhe(p, 1500);
    await fertig(k);
  },
};

const STATE = await anmelden('muster-firma');
const wahl = process.argv[2] || 'alle';
for (const [n, f] of Object.entries(KAPITEL)) {
  if (wahl !== 'alle' && wahl !== n) continue;
  try { await f(); } catch (e) { console.log('FEHLER in', n, ':', e.message.split('\n')[0], (e.stack || '').split('\n').filter((z) => z.includes('aufnahme.mjs')).slice(0, 3).join(' | ')); }
}
console.log('Konsolenfehler:', fehler.length); fehler.slice(0, 8).forEach((x) => console.log('  ', x));
await browser.close();
