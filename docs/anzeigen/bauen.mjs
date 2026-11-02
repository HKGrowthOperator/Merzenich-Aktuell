#!/usr/bin/env node
/**
 * Anzeigenvideos der Firmen, zweite Fassung (Vorgabe Betreiber 02.11.2026: mehr
 * Abwechslung, nicht verzerrt, mehr Bild). Je Motiv aus docs/anzeigen/motive.json
 * zwei Formate, 8 s, stumm, nahtlose Schleife, 30 fps, keine Schrift im Video:
 *   16:9   960 × 540, Standbild 1600 × 900  → data/banner/<id>.{mp4,webm,jpg}
 *   breit  1344 × 384 (3,5 : 1), Standbild 1680 × 480 → data/banner/<id>-breit.{mp4,webm,jpg}
 * Die breite Fassung läuft in den breiten Werbebändern (werbung.css, ad-rotation.js).
 *
 * Stile („stil“ im Motiv):
 *   sequenz  drei Fotos mit Überblendung, je eigene Kamerabewegung (heran, schwenk, zurueck)
 *   split    drei Fotos als Bahnen, die sich schrittweise verschieben (16:9 zwei, breit drei sichtbar)
 *   detail   ein Foto: heran an den Fokuspunkt und zurück
 *   licht    ein Foto mit ruhigen Licht- und Bildschirmeffekten (flaechen, lichter), ohne Farbkreisel
 *   film     vorhandenes Video der Firma (KBS-Intro), ausgeschnitten ohne Schwarzblende, Schleife mit Überblendung
 *   geraet   Website im Browserfenster und auf dem Handy (HK Growth Webstudio), nur breit
 * Fotos liegen in docs/anzeigen/material/ (verkleinert, Nutzung laut Nachweis in firmen-entwuerfe.json).
 * Das Foto deckt das Bild wie object-fit: cover mit dem Fokuspunkt (object-position); die
 * Ausschnitte rechnet dieses Skript selbst, damit Licht und Flächen genau auf dem Foto sitzen.
 * Erstes Bild = letztes Bild (Schleife) = Standbild.
 *
 * Aufruf (Arbeitsordner außerhalb des Repos, z. B. im Scratchpad):
 *   node docs/anzeigen/bauen.mjs <arbeitsordner> [--nur <id>] [--format 16x9|breit] [--nur-kodieren]
 * Braucht ffmpeg/ffprobe und npx hyperframes@0.8.192 (Chromium).
 */
import { existsSync, mkdirSync, readFileSync, writeFileSync, copyFileSync, statSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { basename, dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const HIER = dirname(fileURLToPath(import.meta.url));
const MATERIAL = join(HIER, 'material');
const BANNER = resolve(HIER, '../../wordpress/plugin/merzenich-aktuell-core/data/banner');
const arg = (n) => { const i = process.argv.indexOf(n); return i > 0 ? process.argv[i + 1] : ''; };
if (!process.argv[2] || process.argv[2].startsWith('--')) { console.error('Arbeitsordner angeben.'); process.exit(2); }
const ARBEIT = resolve(process.argv[2]);
const { dauer: T, motive: MOTIVE } = JSON.parse(readFileSync(join(HIER, 'motive.json'), 'utf8'));
const FPS = 30;
const FORMATE = {
  '16x9': { w: 960, h: 540, poster: [1600, 900], endung: '' },
  breit: { w: 1344, h: 384, poster: [1680, 480], endung: '-breit' },
};
const r2 = (v) => Math.round(v * 100) / 100;

function masse(datei) {
  const [w, h] = execFileSync('ffprobe', ['-v', 'error', '-select_streams', 'v:0', '-show_entries', 'stream=width,height', '-of', 'csv=p=0:s=x', datei]).toString().trim().split('x').map(Number);
  return { w, h };
}
const fokus = (f) => { const m = /^(\d{1,3})% (\d{1,3})%$/.exec(f || '50% 50%'); return m ? [m[1] / 100, m[2] / 100] : [0.5, 0.5]; };

// Deckung wie object-fit: cover + object-position: der Punkt (fx, fy) des Fotos liegt bei (fx, fy) des Rahmens.
function deckung(bw, bh, rw, rh, [fx, fy]) {
  const s = Math.max(rw / bw, rh / bh);
  const w = bw * s, h = bh * s;
  return { left: r2((rw - w) * fx), top: r2((rh - h) * fy), width: r2(w), height: r2(h) };
}
// Ausschnitt im Foto für ein Seitenverhältnis (für die Standbilder, gleiche Regel wie oben).
function ausschnitt(bw, bh, a, [fx, fy]) {
  if (bw / bh > a) { const cw = Math.round(bh * a); return { x: Math.round((bw - cw) * fx), y: 0, w: cw, h: bh }; }
  const ch = Math.round(bw / a); return { x: 0, y: Math.round((bh - ch) * fy), w: bw, h: ch };
}

// Ein Foto als „Welt“ im Rahmen rw × rh; Überlagerungen (extra) in Prozent des Fotos.
function welt(id, datei, rw, rh, f, ursprung, extra = '', klasse = 'welt') {
  const { w, h } = masse(join(MATERIAL, datei));
  const d = deckung(w, h, rw, rh, fokus(f));
  const [fx, fy] = fokus(f);
  // Drehpunkt: Fokus (heran, zurueck, detail) oder Mitte des Rahmens (schwenk).
  const o = ursprung === 'mitte' ? [r2((rw / 2 - d.left) / d.width * 100), r2((rh / 2 - d.top) / d.height * 100)] : [r2(fx * 100), r2(fy * 100)];
  return `<div class="${klasse}" id="${id}" style="left:${d.left}px;top:${d.top}px;width:${d.width}px;height:${d.height}px;transform-origin:${o[0]}% ${o[1]}%"><img src="${basename(datei)}" alt="">${extra}</div>`;
}

function seite(fmt, inhalt, stil, anim) {
  return `<!doctype html>
<html lang="de"><head><meta charset="utf-8"><title>Anzeige</title>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<style>
body{margin:0;background:#111}
#root{position:relative;width:${fmt.w}px;height:${fmt.h}px;overflow:hidden;background:#111}
.schuss,.bahn{position:absolute;overflow:hidden}
.schuss{inset:0}
.welt,.pw{position:absolute}
.welt>img,.pw>img{position:absolute;inset:0;width:100%;height:100%;display:block}
${stil}
</style></head>
<body>
<div id="root" data-composition-id="root" data-width="${fmt.w}" data-height="${fmt.h}" data-duration="${T}">
${inhalt}
</div>
<script>
const tl = gsap.timeline({ paused: true });
${anim}
tl.to({}, { duration: 0.01 }, ${T - 0.01});
window.__timelines = { root: tl };
</script>
</body></html>
`;
}

// sequenz: drei Fotos, Überblendung 0,6 s, am Ende wieder das erste (Anfang = Ende).
const BEWEGUNG = {
  heran: [{ scale: 1 }, { scale: 1.1 }],
  zurueck: [{ scale: 1.1 }, { scale: 1 }],
};
function sequenz(m, fmt) {
  const n = m.bilder.length, seg = T / n, d = 0.6, a = r2(fmt.w * 0.035);
  let inhalt = '', anim = '';
  m.bilder.forEach(([datei, f, bew], i) => {
    const schwenk = /^schwenk/.test(bew || '');
    inhalt += `<div class="schuss" id="s${i}">${welt('w' + i, datei, fmt.w, fmt.h, f, schwenk ? 'mitte' : 'fokus')}</div>`;
    const start = i === 0 ? 0 : r2(i * seg - d / 2), ende = i === n - 1 ? T : r2((i + 1) * seg + d / 2);
    let [von, bis] = schwenk ? [{ scale: 1.12, x: bew === 'schwenk-' ? a : -a }, { scale: 1.12, x: bew === 'schwenk-' ? -a : a }] : (BEWEGUNG[bew] || BEWEGUNG.heran);
    if (i === 0) von = { scale: 1, x: 0 };
    if (i > 0) anim += `tl.fromTo('#s${i}', { opacity: 0 }, { opacity: 1, duration: ${d}, ease: 'none' }, ${start});\n`;
    anim += `tl.fromTo('#w${i}', ${JSON.stringify(von)}, { ...${JSON.stringify(bis)}, duration: ${r2(ende - start)}, ease: 'none' }, ${start});\n`;
  });
  const [datei, f] = m.bilder[0];
  inhalt += `<div class="schuss" id="se">${welt('we', datei, fmt.w, fmt.h, f, 'fokus')}</div>`;
  // Ein Bild vor dem Ende voll sichtbar: letztes Bild = erstes Bild, die Schleife springt nicht.
  anim += `tl.fromTo('#se', { opacity: 0 }, { opacity: 1, duration: ${d}, ease: 'none' }, ${r2(T - d - 1 / FPS)});\n`;
  return seite(fmt, inhalt, '', anim);
}

// split: Bahnen [A B C A B (C)] rücken in drei Schritten um je eine Bahn; nach drei Schritten wie am Anfang.
function split(m, fmt) {
  const k = m.bilder.length, sicht = fmt.w / fmt.h > 2.5 ? 3 : 2, gap = 6;
  const pw = r2((fmt.w - gap * (sicht - 1)) / sicht), schritt = r2(pw + gap);
  let bahnen = '';
  for (let i = 0; i < k + sicht; i++) {
    const [datei, f] = m.bilder[i % k];
    bahnen += `<div class="bahn" style="left:${r2(i * schritt)}px;top:0;width:${pw}px;height:${fmt.h}px">${welt('p' + i, datei, pw, fmt.h, f, 'fokus', '', 'pw')}</div>`;
  }
  let anim = '';
  for (let s = 0; s < k; s++) anim += `tl.to('#band', { x: ${-r2((s + 1) * schritt)}, duration: 0.75, ease: 'power2.inOut' }, ${r2((s + 1) * T / k - 0.95)});\n`;
  anim += `tl.fromTo('.pw', { scale: 1 }, { scale: 1.06, duration: ${T / 2}, ease: 'sine.inOut' }, 0);\ntl.to('.pw', { scale: 1, duration: ${T / 2}, ease: 'sine.inOut' }, ${T / 2});\n`;
  return seite(fmt, `<div id="band" style="position:absolute;left:0;top:0;width:${r2((k + sicht) * schritt)}px;height:${fmt.h}px">${bahnen}</div>`, '', anim);
}

// detail: heran an den Fokus und zurück (Anfang = Ende).
function detail(m, fmt) {
  const [datei, f] = m.bilder[0], z = m.zoom || 1.45;
  const anim = `tl.fromTo('#w0', { scale: 1 }, { scale: ${z}, duration: ${T / 2}, ease: 'sine.inOut' }, 0);\ntl.to('#w0', { scale: 1, duration: ${T / 2}, ease: 'sine.inOut' }, ${T / 2});\n`;
  return seite(fmt, `<div class="schuss">${welt('w0', datei, fmt.w, fmt.h, f, 'fokus')}</div>`, '', anim);
}

// licht: Bildschirmflächen mit ruhigem Farb- und Helligkeitswechsel, Lichter pulsieren, ein Lichtstreif über die Fläche.
function licht(m, fmt) {
  const [datei, f] = m.bilder[0];
  const { w, h } = masse(join(MATERIAL, datei));
  const poly = (p) => `clip-path:polygon(${p.map(([x, y]) => `${x}% ${y}%`).join(',')})`;
  const extra = (m.flaechen || []).map((p, i) => `<div class="wand" id="f${i}" style="${poly(p)}"><img src="${basename(datei)}" alt=""></div><div class="wand" style="${poly(p)}"><i class="streif" id="g${i}"></i></div>`).join('')
    + (m.lichter || []).map(([x, y, r], i) => `<div class="licht" id="l${i}" style="left:${x - r}%;top:${r2(y - r * w / h)}%;width:${2 * r}%;height:${r2(2 * r * w / h)}%"></div>`).join('');
  const stil = `.wand{position:absolute;inset:0}
.wand>img{position:absolute;inset:0;width:100%;height:100%}
.streif{position:absolute;top:-10%;bottom:-10%;left:0;width:34%;background:linear-gradient(90deg,rgba(255,255,255,0),rgba(255,255,255,.32),rgba(255,255,255,0));mix-blend-mode:screen}
.licht{position:absolute;border-radius:50%;background:radial-gradient(closest-side,rgba(255,250,235,.9),rgba(255,244,214,.35) 45%,rgba(255,240,200,0) 100%);mix-blend-mode:screen}`;
  const anim = [
    `tl.fromTo('#w0', { scale: 1 }, { scale: 1.06, duration: ${T / 2}, ease: 'sine.inOut' }, 0);`,
    `tl.to('#w0', { scale: 1, duration: ${T / 2}, ease: 'sine.inOut' }, ${T / 2});`,
    ...(m.flaechen || []).flatMap((_, i) => [
      `tl.fromTo('#f${i}', { filter: 'hue-rotate(0deg) brightness(1.04) saturate(1.05)' }, { filter: 'hue-rotate(-16deg) brightness(1.18) saturate(1.12)', duration: ${T / 4}, ease: 'sine.inOut', repeat: 3, yoyo: true }, 0);`,
      `tl.fromTo('#g${i}', { xPercent: -120 }, { xPercent: 320, duration: 3.2, ease: 'power1.inOut' }, ${1 + i * 0.4});`,
    ]),
    ...(m.lichter || []).map((_, i) => `tl.fromTo('#l${i}', { opacity: 0.45 }, { opacity: 0.9, duration: ${T / 4}, ease: 'sine.inOut', repeat: 3, yoyo: true }, 0);`),
  ].join('\n');
  return seite(fmt, `<div class="schuss">${welt('w0', datei, fmt.w, fmt.h, f, 'fokus', extra)}</div>`, stil, anim);
}

// geraet: Browserfenster mit der Website und Handy daneben (HK Growth Webstudio), Bildlauf hin und zurück.
function geraet(m, fmt) {
  const bw = 820, bh = 324, top = Math.round((fmt.h - bh) / 2), gruppe = bw + 40 + 150, links = Math.round((fmt.w - gruppe) / 2);
  const bild = masse(join(MATERIAL, m.bild));
  const hoehe = Math.round(bild.h * bw / bild.w), lauf = Math.max(0, Math.min(m.lauf || 1400, hoehe - (bh - 24)));
  // Handy: Bildlauf nur innerhalb des Bildes (136 px breit), sonst bleibt der Bildschirm weiß.
  const handy = masse(join(MATERIAL, m.handy)), hmax = Math.max(0, Math.round(handy.h * 136 / handy.w) - (bh - 14));
  const hs = Math.min(m.handyStart ?? 300, Math.max(0, hmax - 500)), hl = Math.min(500, hmax - hs);
  const stil = `.glow{position:absolute;border-radius:50%;filter:blur(46px)}
#g1{left:${links + 420}px;top:-150px;width:620px;height:360px;background:rgba(43,115,83,.55)}
#g2{left:-120px;top:200px;width:560px;height:300px;background:rgba(158,245,181,.10)}
#fenster{position:absolute;left:${links}px;top:${top}px;width:${bw}px;height:${bh}px;border-radius:10px;overflow:hidden;background:#1d1f1c;box-shadow:0 26px 54px rgba(0,0,0,.55),inset 0 0 0 1px rgba(255,255,255,.07)}
#leiste{position:absolute;left:0;top:0;right:0;height:24px;background:#1d1f1c}
#leiste i{position:absolute;top:8px;width:8px;height:8px;border-radius:50%;background:#3d403b}
#adresse{position:absolute;left:${bw / 2 - 120}px;top:5px;width:240px;height:14px;border-radius:7px;background:#2a2d28}
#bildschirm{position:absolute;left:0;top:24px;width:${bw}px;height:${bh - 24}px;overflow:hidden;background:#f6f5f0}
#bildschirm img{position:absolute;left:0;top:0;width:100%;display:block}
#phone{position:absolute;left:${links + bw + 40}px;top:${top}px;width:150px;height:${bh}px;background:#1d1f1c;border-radius:26px;box-shadow:0 24px 46px rgba(0,0,0,.6),inset 0 0 0 1px rgba(255,255,255,.08)}
#phone .scr{position:absolute;left:7px;top:7px;width:136px;height:${bh - 14}px;overflow:hidden;border-radius:20px;background:#f6f5f0}
#phone img{position:absolute;left:0;top:0;width:100%;display:block}
#linie{position:absolute;left:${links}px;top:${top + bh + 14}px;width:120px;height:3px;border-radius:2px;background:#9ef5b5;transform-origin:0 50%}`;
  const inhalt = `<div class="glow" id="g1"></div><div class="glow" id="g2"></div>
<div id="fenster"><div id="leiste"><i style="left:12px"></i><i style="left:26px"></i><i style="left:40px"></i><span id="adresse"></span></div><div id="bildschirm"><img id="desk" src="${basename(m.bild)}" alt=""></div></div>
<div id="phone"><div class="scr"><img id="hand" src="${basename(m.handy)}" alt=""></div></div>`;
  const anim = [
    `tl.fromTo('#desk', { y: 0 }, { y: ${-lauf}, duration: ${T / 2}, ease: 'sine.inOut' }, 0);`,
    `tl.to('#desk', { y: 0, duration: ${T / 2}, ease: 'sine.inOut' }, ${T / 2});`,
    `tl.fromTo('#hand', { y: ${-hs} }, { y: ${-hs - hl}, duration: ${T / 2}, ease: 'sine.inOut' }, 0);`,
    `tl.to('#hand', { y: ${-hs}, duration: ${T / 2}, ease: 'sine.inOut' }, ${T / 2});`,
    `tl.fromTo('#phone', { y: 0, rotation: -1.2 }, { y: -6, rotation: 0.8, duration: ${T / 2}, ease: 'sine.inOut' }, 0);`,
    `tl.to('#phone', { y: 0, rotation: -1.2, duration: ${T / 2}, ease: 'sine.inOut' }, ${T / 2});`,
    `tl.fromTo('#g1', { xPercent: 0, opacity: .9 }, { xPercent: -8, opacity: 1, duration: ${T / 2}, ease: 'sine.inOut' }, 0);`,
    `tl.to('#g1', { xPercent: 0, opacity: .9, duration: ${T / 2}, ease: 'sine.inOut' }, ${T / 2});`,
  ].join('\n');
  return seite(fmt, inhalt, stil, anim);
}

const STILE = { sequenz, split, detail, licht, geraet };

// Zwei Fassungen: MP4/H.264 (alle Browser) und WebM/VP9 (meist kleiner).
function kodieren(ein, aus, fmt, crf, fps = FPS) {
  const vf = `scale=${fmt.w}:${fmt.h}:flags=lanczos,fps=${fps},format=yuv420p`;
  execFileSync('ffmpeg', ['-v', 'error', '-y', '-i', ein, '-an', '-vf', vf, '-c:v', 'libx264', '-profile:v', 'main', '-preset', 'slow', '-crf', String(crf), '-g', String(fps * 2), '-movflags', '+faststart', aus]);
  execFileSync('ffmpeg', ['-v', 'error', '-y', '-i', ein, '-an', '-vf', vf, '-c:v', 'libvpx-vp9', '-b:v', '600k', '-crf', String(crf + 7), '-row-mt', '1', '-deadline', 'good', '-cpu-used', '2', aus.replace(/\.mp4$/, '.webm')]);
}

// Standbild = erstes Bild der Schleife, aus dem Originalfoto in voller Auflösung.
function standbild(m, fmt, aus, roh) {
  const [pw, ph] = fmt.poster;
  const q = ['-q:v', '5'];
  if (m.stil === 'geraet') { execFileSync('ffmpeg', ['-v', 'error', '-y', '-i', roh, '-frames:v', '1', ...q, aus]); return; }
  if (m.stil === 'film') {
    const a = pw / ph, src = join(MATERIAL, m.video), { w, h } = masse(src), c = ausschnitt(w, h, a, fokus(m.fokus));
    execFileSync('ffmpeg', ['-v', 'error', '-y', '-ss', String(m.standbild ?? 4), '-i', src, '-frames:v', '1', '-vf', `crop=${c.w}:${c.h}:${c.x}:${c.y},scale=${pw}:${ph}:flags=lanczos`, ...q, aus]);
    return;
  }
  if (m.stil === 'split') {
    const sicht = fmt.w / fmt.h > 2.5 ? 3 : 2, s = pw / fmt.w, gap = Math.round(6 * s), bw = Math.floor((pw - gap * (sicht - 1)) / sicht);
    const ein = [], filter = [], lagen = [];
    for (let i = 0; i < sicht; i++) {
      const [datei, f] = m.bilder[i % m.bilder.length], src = join(MATERIAL, datei), { w, h } = masse(src), c = ausschnitt(w, h, bw / ph, fokus(f));
      ein.push('-i', src);
      filter.push(`[${i}:v]crop=${c.w}:${c.h}:${c.x}:${c.y},scale=${bw}:${ph}:flags=lanczos,setsar=1[b${i}]`);
      lagen.push(`${i * (bw + gap)}_0`);
    }
    filter.push(`${Array.from({ length: sicht }, (_, i) => `[b${i}]`).join('')}xstack=inputs=${sicht}:layout=${lagen.join('|')}:fill=0x111111,pad=${pw}:${ph}:0:0:0x111111`);
    execFileSync('ffmpeg', ['-v', 'error', '-y', ...ein, '-filter_complex', filter.join(';'), '-frames:v', '1', ...q, aus]);
    return;
  }
  const [datei, f] = m.bilder[0], src = join(MATERIAL, datei), { w, h } = masse(src), c = ausschnitt(w, h, pw / ph, fokus(f));
  execFileSync('ffmpeg', ['-v', 'error', '-y', '-i', src, '-frames:v', '1', '-vf', `crop=${c.w}:${c.h}:${c.x}:${c.y},scale=${pw}:${ph}:flags=lanczos`, ...q, aus]);
}

// film: Ausschnitt [ab, bis] des Videos als Schleife: das Ende wird ins Anfangsstück überblendet,
// dann beginnt die Schleife bei „start“ (Sekunden im Original), z. B. beim stehenden Logo.
function film(m, fmt, roh) {
  const src = join(MATERIAL, m.video), { w, h } = masse(src), a = fmt.w / fmt.h, c = ausschnitt(w, h, a, fokus(m.fokus));
  const ab = m.ab, bis = m.bis, d = m.ueberblendung || 0.5, L = r2(bis - ab), dreh = r2((m.start ?? ab + d) - (ab + d));
  // Bildrate des Originals behalten (KBS: 25 fps), sonst ruckelt es durch doppelte Bilder.
  const zu = `crop=${c.w}:${c.h}:${c.x}:${c.y},scale=${fmt.w}:${fmt.h}:flags=lanczos,fps=${m.fps || FPS},format=yuv420p,setsar=1`;
  const f = [
    `[0:v]trim=start=${ab + d}:end=${bis + d},setpts=PTS-STARTPTS,${zu}[haupt]`,
    `[0:v]trim=start=${ab}:end=${ab + d},setpts=PTS-STARTPTS,${zu}[kopf]`,
    `[haupt][kopf]xfade=transition=fade:duration=${d}:offset=${r2(L - d)}[schleife]`,
    ...(dreh > 0 ? [
      `[schleife]split[x][y]`,
      `[x]trim=start=${dreh}:end=${L},setpts=PTS-STARTPTS[t1]`,
      `[y]trim=start=0:end=${dreh},setpts=PTS-STARTPTS[t2]`,
      `[t1][t2]concat=n=2:v=1[aus]`,
    ] : ['[schleife]null[aus]']),
  ];
  execFileSync('ffmpeg', ['-v', 'error', '-y', '-i', src, '-filter_complex', f.join(';'), '-map', '[aus]', '-an', '-c:v', 'libx264', '-crf', '14', '-preset', 'fast', roh]);
}

for (const m of MOTIVE) {
  if (arg('--nur') && m.id !== arg('--nur')) continue;
  for (const [name, fmt] of Object.entries(FORMATE)) {
    if (arg('--format') && arg('--format') !== name) continue;
    if (m.nur && m.nur !== name) continue;
    const id = m.id + fmt.endung, ordner = join(ARBEIT, id), roh = join(ordner, 'roh.mp4');
    mkdirSync(ordner, { recursive: true });
    const nurKodieren = process.argv.includes('--nur-kodieren') && existsSync(roh);
    if (m.stil === 'film') { if (!nurKodieren) film(m, fmt, roh); }
    else {
      for (const d of [...(m.bilder || []).map((b) => b[0]), m.bild, m.handy].filter(Boolean)) copyFileSync(join(MATERIAL, d), join(ordner, basename(d)));
      writeFileSync(join(ordner, 'index.html'), STILE[m.stil](m, fmt));
      if (!nurKodieren) execFileSync('npx', ['--yes', 'hyperframes@0.8.192', 'render', '-o', 'roh.mp4', '--fps', String(FPS), '--quiet'], { cwd: ordner, stdio: 'inherit' });
    }
    const ziel = join(BANNER, `${id}.mp4`);
    kodieren(roh, ziel, fmt, m.crf || 28, m.fps || FPS);
    standbild(m, fmt, join(BANNER, `${id}.jpg`), roh);
    const kb = (p) => Math.round(statSync(p).size / 1024);
    console.log(`${id}: MP4 ${kb(ziel)} KB, WebM ${kb(ziel.replace(/\.mp4$/, '.webm'))} KB, Standbild ${kb(join(BANNER, `${id}.jpg`))} KB`);
  }
}
