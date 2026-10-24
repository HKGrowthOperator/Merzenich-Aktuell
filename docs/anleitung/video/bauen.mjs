#!/usr/bin/env node
/**
 * Erklärvideo „So geht's: Ihr Zugang bei Merzenich Aktuell“ (HyperFrames, 1920 × 1080, ohne Ton).
 *
 * Liest die Aufnahmen aus aufnahme.mjs (Ordner mit clips/*.webm, clips/zeiten.json,
 * shots/marken.json), wandelt sie in MP4 (clips/, nicht im Repo) und schreibt index.html:
 * Vorspann, je Kapitel eine Titelkarte und den Clip mit Texteinblendungen links,
 * roten Rahmen auf dem Bedienelement und Zoom auf kleine Felder, dann den Abspann.
 *
 * Aufruf:
 *   node docs/anleitung/video/bauen.mjs <aufnahme-ordner>
 *   cd docs/anleitung/video && npx hyperframes@0.8.192 render -o anleitung.mp4
 * (Chrome: HYPERFRAMES_BROWSER_PATH, sonst lädt HyperFrames seinen eigenen.)
 */
import { existsSync, mkdirSync, readFileSync, writeFileSync, copyFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const HIER = dirname(fileURLToPath(import.meta.url));
const WURZEL = resolve(HIER, '../../..');
const QUELLE = resolve(process.argv[2] || '');
if (!process.argv[2] || !existsSync(join(QUELLE, 'clips', 'zeiten.json'))) { console.error('Aufnahme-Ordner mit clips/zeiten.json angeben.'); process.exit(2); }
const ZEITEN = JSON.parse(readFileSync(join(QUELLE, 'clips', 'zeiten.json'), 'utf8'));
const MARKEN = JSON.parse(readFileSync(join(QUELLE, 'shots', 'marken.json'), 'utf8'));

// Die ersten Zehntelsekunden jeder Aufnahme sind leer (Seite lädt).
const VORLAUF = 0.6;
const KARTE = 3.2, VORSPANN = 6, ABSPANN = 7;
// Aufnahme 1440 × 900, im Video 1408 × 880 rechts.
const S = 1408 / 1440, VX = 448, VY = 100;

/** Kapitel: Titel, Einblendungen je Zeitmarke (Name des Bildschirmfotos), Zoom auf Marke. */
const KAPITEL = [
  { clip: '01-anmelden', titel: 'Anmelden', texte: [
    ['start', 'Im Browser <b>merzenich-aktuell.de/wp-admin</b> öffnen.'],
    ['01-anmeldung', 'Benutzername und Passwort eintragen, dann <b>Anmelden</b>.'],
    ['02-dashboard', 'Ihr Dashboard: oben die Kurzanleitung, darunter <b>Ihr Bereich</b>.'],
  ] },
  { clip: '02-tour', titel: 'Die Tour im Editor', texte: [
    ['start', 'Beim ersten Mal führt eine kurze Tour durch den Editor.'],
    ['03-tour-1', '<b>Titel:</b> wer, was, wo, wann.'],
    ['03-tour-2', '<b>Text:</b> das Wichtigste in den ersten Satz.'],
    ['03-tour-3', '<b>Beitragsbild:</b> das Foto für Startseite und Meldung.'],
    ['03-tour-4', '<b>Bildrechte</b> bestätigen, sonst geht nichts raus.'],
    ['03-tour-5', '<b>Ortsteil</b> wählen.'],
    ['03-tour-6', '<b>Zur Überprüfung einreichen.</b> Die Redaktion prüft.'],
  ] },
  { clip: '03-schreiben', titel: 'Schreiben, Bild, Einreichen', texte: [
    ['start', 'Titel und Text schreiben. Datum immer mit Tag und Monat.'],
    ['05-beitragsbild-knopf', 'Rechts: <b>Beitragsbild festlegen</b>.', true],
    ['06-hochladen', '<b>Dateien auswählen</b> und das Foto hochladen.'],
    ['07-bildangaben', '<b>Fotograf</b> und <b>Nutzungsgrundlage</b> eintragen.', true],
    ['08-bildrechte', 'Erklärung lesen, <b>Haken setzen</b>.', true],
    ['09-ortsteil', 'Den <b>Ortsteil</b> wählen.', true],
    ['10-einreichen-knopf', 'Oben rechts: <b>Zur Überprüfung einreichen</b>.', true],
    ['11-einreichen-pruefen', 'Noch einmal bestätigen. Fertig.'],
    ['12-eingereicht', 'Die Redaktion prüft. Sie bekommen eine <b>E-Mail</b>, sobald die Meldung online ist.'],
  ] },
  { clip: '04-rueckmeldung', titel: 'Rückmeldungen', texte: [
    ['start', 'Im Dashboard steht der Stand jeder Meldung.'],
    ['13-status', 'In Rot: was die Redaktion noch braucht.', true],
    ['14-aenderung-angefordert', 'Die Bitte steht auch oben im Editor. Ändern und erneut einreichen.', true],
    ['15-aenderung-vorschlagen', 'Veröffentlichtes ändern: <b>Änderung vorschlagen</b>. Das Original bleibt, bis die Redaktion freigibt.'],
  ] },
  { clip: '05-profil', titel: 'Profil und Hilfe', texte: [
    ['start', 'Unter <b>Profil</b>: E-Mail-Adresse prüfen.'],
    ['17-passwort', '<b>Neues Passwort festlegen</b>, dann speichern.', true],
    ['18-hilfe-seite', 'Unter <b>Hilfe</b>: Anleitung als PDF, Video, Tour und häufige Fragen.'],
  ] },
  { clip: '06-firma', titel: 'Für Unternehmen', texte: [
    ['start', 'Unternehmen: Profil bearbeiten und den eigenen <b>Kanal</b> ansehen.'],
    ['20-anzeige', '<b>Anzeige</b>: Titel, Platz, Laufzeit, Bannerbild. Die Redaktion schaltet frei.'],
  ] },
  { clip: '07-verein', titel: 'Für Vereine', texte: [
    ['start', 'Vereine: <b>Termin einreichen</b> für den Kalender.'],
    ['22-termin', '<b>Beginn</b> und <b>Ort</b> eintragen, dann einreichen.', true],
  ] },
];

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
const r1 = (x) => Math.round(x * 100) / 100;

// Clips umwandeln (H.264, 30 fps), ohne den leeren Vorlauf.
mkdirSync(join(HIER, 'clips'), { recursive: true });
mkdirSync(join(HIER, 'medien'), { recursive: true });
for (const k of KAPITEL) {
  const ziel = join(HIER, 'clips', `${k.clip}.mp4`);
  execFileSync('ffmpeg', ['-loglevel', 'error', '-y', '-ss', String(VORLAUF), '-i', join(QUELLE, 'clips', `${k.clip}.webm`), '-an', '-r', '30', '-c:v', 'libx264', '-preset', 'medium', '-crf', '18', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', ziel]);
  k.dauer = Number(execFileSync('ffprobe', ['-v', 'error', '-show_entries', 'format=duration', '-of', 'csv=p=0', ziel], { encoding: 'utf8' }).trim());
}
for (const f of ['hanken-grotesk-latin-wght-normal.woff2', 'newsreader-latin-wght-normal.woff2']) copyFileSync(join(WURZEL, 'chatgpt-site/assets/fonts', f), join(HIER, 'medien', f));
copyFileSync(join(WURZEL, 'chatgpt-site/assets/img/logo-on-light.png'), join(HIER, 'medien', 'logo.png'));

// Zeitleiste
let t = VORSPANN; const teile = []; const anim = []; const videos = [];
KAPITEL.forEach((k, i) => {
  const nr = i + 1, karte = t, clip = t + KARTE, ende = clip + k.dauer;
  teile.push(`<div class="clip karte" data-start="${r1(karte)}" data-duration="${KARTE}"><p class="kn">${nr}</p><h2>${esc(k.titel)}</h2></div>`);
  anim.push(`tl.from('#k${nr} ', {}, 0);`);
  // Zeitmarken relativ zum Clip
  // Die Aufnahme läuft etwas langsamer als die Uhr (Bilder kommen unter Last später):
  // Zeitmarke × (Länge der Aufnahme / gemessene Dauer), dazu der Weg vom Klick zum Bild.
  const roh = Number(execFileSync('ffprobe', ['-v', 'error', '-show_entries', 'format=duration', '-of', 'csv=p=0', join(QUELLE, 'clips', `${k.clip}.webm`)], { encoding: 'utf8' }).trim());
  const uhr = (ZEITEN[k.clip] || []).find(([n]) => n === 'ende')?.[1] || roh;
  const marken = (ZEITEN[k.clip] || []).map(([n, z]) => [n, z * (roh / uhr) + 0.15 - VORLAUF]);
  const zeiten = Object.fromEntries(marken);
  const klicks = marken.filter(([n]) => n === 'klick').map(([, z]) => z);
  // Text erscheint mit der Marke „vor:<Bild>“ (Beginn des Schritts in aufnahme.mjs), sonst mit dem
  // Klick, der zu dieser Ansicht führt (bis 6 s vorher), sonst kurz vor dem Bild.
  const beginn = (n) => {
    if (zeiten['vor:' + n] !== undefined) return Math.max(0, zeiten['vor:' + n] - 0.2);
    const ze = zeiten[n] ?? 0; const kl = klicks.filter((z) => z < ze - 0.5 && ze - z < 6).pop(); return Math.max(0, (kl ?? ze) - 0.4);
  };
  const texte = k.texte.map(([n, txt, zoom]) => ({ n, txt, zoom, z: n === 'start' ? 0 : beginn(n), zz: n === 'start' ? 0 : Math.max(0, (zeiten[n] ?? 0) - 0.4) })).sort((a, b) => a.z - b.z);
  // Jeder Text steht mindestens 2,8 Sekunden, sonst ist er nicht zu lesen.
  texte.forEach((x, j) => { if (j) x.z = Math.max(x.z, texte[j - 1].z + 2.8); });
  const seite = texte.map((x, j) => {
    const von = clip + x.z, bis = j + 1 < texte.length ? clip + texte[j + 1].z : ende;
    return `<p class="clip text" data-start="${r1(von)}" data-duration="${r1(Math.max(0.5, bis - von))}">${x.txt}</p>`;
  }).join('');
  // Rahmen auf die erste Marke und Zoom
  const rahmen = texte.filter((x) => x.n !== 'start' && MARKEN[x.n]?.length).map((x) => {
    const b = MARKEN[x.n][0], von = clip + x.zz + 0.3;
    const st = `left:${r1(b.x * S - 6)}px;top:${r1(b.y * S - 6)}px;width:${r1(b.w * S + 12)}px;height:${r1(b.h * S + 12)}px`;
    if (x.zoom) {
      const cx = (b.x + b.w / 2) * S, cy = (b.y + b.h / 2) * S;
      const z = Math.min(1.7, Math.max(1.25, 300 / Math.max(b.w * S, 120)));
      anim.push(`tl.to('#v${nr}', {scale:${r1(z)}, transformOrigin:'${r1(cx)}px ${r1(cy)}px', duration:0.7, ease:'power3.out', overwrite:'auto'}, ${r1(von)});`);
      anim.push(`tl.to('#v${nr}', {scale:1, duration:0.7, ease:'power3.inOut', overwrite:'auto'}, ${r1(von + 2.6)});`);
    }
    return `<div class="clip rahmen" data-start="${r1(von)}" data-duration="2.6" style="${st}"></div>`;
  }).join('');
  // Links die Texte (eine Zeitspanne je Kapitel), rechts im gemeinsamen Rahmen der Clip:
  // ein <video> darf nicht in einem zeitgesteuerten Element liegen (hyperframes lint).
  teile.push(`<aside class="clip" data-start="${r1(clip)}" data-duration="${r1(k.dauer)}"><img class="logo" src="medien/logo.png" alt=""><p class="nr">Kapitel ${nr} von ${KAPITEL.length}</p><h3>${esc(k.titel)}</h3><div class="texte">${seite}</div>
  <ol class="fort">${KAPITEL.map((_, j) => `<li class="${j < i ? 'ok' : j === i ? 'jetzt' : ''}"></li>`).join('')}</ol></aside>`);
  videos.push(`<div class="zoom" id="v${nr}"><video id="clip${nr}" src="clips/${k.clip}.mp4" muted playsinline data-start="${r1(clip)}" data-duration="${r1(k.dauer)}"></video>${rahmen}</div>`);
  t = ende;
});
const GESAMT = r1(t + ABSPANN);

const html = `<!doctype html>
<html lang="de"><head><meta charset="utf-8"><title>So geht's: Ihr Zugang bei Merzenich Aktuell</title>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<style>
@font-face{font-family:'Hanken Grotesk';src:url('medien/hanken-grotesk-latin-wght-normal.woff2') format('woff2');font-weight:100 900}
@font-face{font-family:'Newsreader';src:url('medien/newsreader-latin-wght-normal.woff2') format('woff2');font-weight:200 800}
:root{--rot:#971725;--tinte:#151412;--leise:#5f5b55;--papier:#f4f1ec;--linie:#e3dfd8}
body{margin:0;background:var(--papier)}
#root{position:relative;width:1920px;height:1080px;overflow:hidden;background:var(--papier);color:var(--tinte);font-family:'Hanken Grotesk',Arial,sans-serif}
.clip{position:absolute}
.voll{inset:0;z-index:5;background:var(--papier);display:flex;flex-direction:column;justify-content:center;padding:0 160px}
.voll .logo{width:360px;margin-bottom:64px}
.voll h1{font-family:'Newsreader',Georgia,serif;font-weight:600;font-size:132px;line-height:1.02;margin:0}
.voll h1 span{color:var(--rot)}
.voll p{font-size:40px;color:var(--leise);margin:36px 0 0;max-width:1300px;line-height:1.35}
.karte{inset:0;z-index:5;display:flex;flex-direction:column;justify-content:center;padding:0 160px;background:var(--tinte);color:#f4f1ec}
.karte .kn{font-family:'Newsreader',Georgia,serif;font-size:220px;line-height:1;color:#d9a3a8;margin:0}
.karte h2{font-family:'Newsreader',Georgia,serif;font-weight:600;font-size:96px;margin:12px 0 0}
.szene{inset:0}
aside{position:absolute;left:64px;top:100px;width:340px;height:880px}
aside .logo{width:200px;display:block;margin-bottom:56px}
aside .nr{font-size:22px;letter-spacing:.06em;text-transform:uppercase;color:var(--rot);font-weight:700;margin:0 0 10px}
aside h3{font-family:'Newsreader',Georgia,serif;font-weight:600;font-size:46px;line-height:1.1;margin:0}
.texte{position:absolute;left:0;right:0;top:330px}
.text{position:absolute;left:0;right:0;top:0;margin:0;font-size:36px;line-height:1.32;font-weight:450}
.text b{font-weight:750;color:var(--rot)}
.fort{position:absolute;bottom:0;left:0;margin:0;padding:0;list-style:none;display:flex;gap:10px}
.fort li{width:30px;height:6px;border-radius:3px;background:var(--linie)}
.fort li.ok{background:#c9a3a6}.fort li.jetzt{background:var(--rot)}
.rahmen-video{position:absolute;left:${VX}px;top:${VY}px;width:1408px;height:880px;border-radius:14px;overflow:hidden;box-shadow:0 2px 6px rgba(21,20,18,.12),0 18px 48px rgba(21,20,18,.14);background:#fff}
.zoom{position:absolute;inset:0}
.zoom video{position:absolute;inset:0;width:1408px;height:880px;object-fit:cover}
.rahmen{border:5px solid var(--rot);border-radius:10px;box-shadow:0 0 0 6px rgba(151,23,37,.18)}
</style></head>
<body>
<div id="root" data-composition-id="root" data-width="1920" data-height="1080" data-duration="${GESAMT}">
<div class="clip voll" id="vorspann" data-start="0" data-duration="${VORSPANN}"><img class="logo" src="medien/logo.png" alt=""><h1>So geht's:<br><span>Ihr Zugang bei Merzenich Aktuell</span></h1><p>Anmelden, schreiben, Bild einfügen, einreichen. In ${KAPITEL.length} kurzen Kapiteln.</p></div>
<div class="rahmen-video">${videos.join('\n')}</div>
${teile.join('\n')}
<div class="clip voll" id="abspann" data-start="${r1(t)}" data-duration="${ABSPANN}"><img class="logo" src="medien/logo.png" alt=""><h1>Fragen?<br><span>info@kbs-management.tv</span></h1><p>Die Anleitung als PDF, dieses Video und die Tour finden Sie im Backend unter <b>Hilfe</b>.</p></div>
</div>
<script>
const tl = gsap.timeline({ paused: true });
tl.from('#vorspann > *', { opacity: 0, y: 30, duration: 0.8, stagger: 0.25, ease: 'power3.out' }, 0.2);
document.querySelectorAll('.karte').forEach((k) => { const s = +k.dataset.start; tl.from(k.children, { opacity: 0, y: 40, duration: 0.7, stagger: 0.15, ease: 'power3.out' }, s + 0.1); });
document.querySelectorAll('.text').forEach((e) => { tl.from(e, { opacity: 0, y: 16, duration: 0.45, ease: 'power3.out' }, +e.dataset.start); });
document.querySelectorAll('.rahmen').forEach((e) => { const s = +e.dataset.start; tl.from(e, { opacity: 0, scale: 1.25, duration: 0.4, ease: 'power3.out' }, s); tl.to(e, { opacity: 0, duration: 0.4 }, s + 2.2); });
${anim.filter((a) => !a.includes("from('#k")).join('\n')}
tl.from('#abspann > *', { opacity: 0, y: 30, duration: 0.8, stagger: 0.25, ease: 'power3.out' }, ${r1(t + 0.2)});
tl.to({}, { duration: 0.01 }, ${GESAMT - 0.01});
window.__timelines = { root: tl };
</script>
</body></html>
`;
writeFileSync(join(HIER, 'index.html'), html);
console.log(`index.html: ${KAPITEL.length} Kapitel, ${Math.floor(GESAMT / 60)}:${String(Math.round(GESAMT % 60)).padStart(2, '0')} Minuten`);
