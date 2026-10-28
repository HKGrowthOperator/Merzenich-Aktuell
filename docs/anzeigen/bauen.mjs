#!/usr/bin/env node
/**
 * Anzeigenvideos der Firmen (HyperFrames, 960 × 540, 8 s, stumm, nahtlose Schleife).
 *
 * Je Motiv aus docs/anzeigen/motive.json: das Standbild aus data/banner/<id>.jpg mit
 * langsamer Kamerafahrt (hin und zurück, daher ohne Sprung), dazu leuchtende
 * Bildschirme (Farbverlauf in den Flächen), pulsierende Lichter und ein Lichtstreif
 * (glanz). Keine Schrift im Video: Text und Kennzeichnung bleiben HTML.
 * Motive mit "quelle": "video" (KBS) nehmen ein vorhandenes Video (--intro) und
 * blenden es ein und aus.
 *
 * Aufruf (Arbeitsordner außerhalb des Repos, z. B. im Scratchpad):
 *   node docs/anzeigen/bauen.mjs <arbeitsordner> [--nur <id>] [--intro <kbs-intro.mp4>]
 * Ergebnis: data/banner/<id>.mp4 (H.264) und <id>.webm (VP9), je etwa 100 bis 700 KB.
 * Mit --nur-kodieren werden vorhandene Renderings (<arbeitsordner>/<id>/roh.mp4) nur neu kodiert.
 */
import { existsSync, mkdirSync, readFileSync, writeFileSync, copyFileSync, statSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const HIER = dirname(fileURLToPath(import.meta.url));
const BANNER = resolve(HIER, '../../wordpress/plugin/merzenich-aktuell-core/data/banner');
const arg = (n) => { const i = process.argv.indexOf(n); return i > 0 ? process.argv[i + 1] : ''; };
const ARBEIT = resolve(process.argv[2] || '');
if (!process.argv[2] || process.argv[2].startsWith('--')) { console.error('Arbeitsordner angeben.'); process.exit(2); }
const { dauer: DAUER, motive: MOTIVE } = JSON.parse(readFileSync(join(HIER, 'motive.json'), 'utf8'));
const NUR = arg('--nur');
const W = 960, H = 540, FPS = 24;

// Zwei Fassungen: MP4/H.264 (Safari, Chrome, Firefox, Edge) und WebM/VP9 (Browser ohne H.264, meist kleiner).
function kodieren(ein, aus, filter = '', crf = 28) {
  const vf = `${filter ? filter + ',' : ''}scale=${W}:${H}:flags=lanczos,fps=${FPS},format=yuv420p`;
  execFileSync('ffmpeg', ['-v', 'error', '-y', '-i', ein, '-an', '-vf', vf, '-c:v', 'libx264', '-profile:v', 'main', '-preset', 'slow', '-crf', String(crf), '-movflags', '+faststart', aus]);
  execFileSync('ffmpeg', ['-v', 'error', '-y', '-i', ein, '-an', '-vf', vf, '-c:v', 'libvpx-vp9', '-b:v', '450k', '-crf', String(crf + 6), '-row-mt', '1', '-deadline', 'good', '-cpu-used', '2', aus.replace(/\.mp4$/, '.webm')]);
}

function seite(m) {
  const pct = (v) => `${v}%`;
  const flaechen = (m.flaechen || []).map((punkte, i) =>
    `<img class="flaeche" id="f${i}" src="bild.jpg" alt="" style="clip-path:polygon(${punkte.map(([x, y]) => `${x}% ${y}%`).join(',')})">`).join('');
  const lichter = (m.lichter || []).map(([x, y, r], i) =>
    `<div class="licht" id="l${i}" style="left:${pct(x - r)};top:calc(${pct(y)} - ${r * W / H / 2}%);width:${pct(2 * r)};height:${r * 2 * W / H}%"></div>`).join('');
  const glanz = m.glanz ? '<div class="glanz" id="glanz"></div>' : '';
  const anim = [
    `tl.fromTo('#welt', { scale: 1.03, xPercent: -1.2, yPercent: 0.4 }, { scale: 1.11, xPercent: 1.2, yPercent: -0.4, duration: ${DAUER / 2}, ease: 'sine.inOut' }, 0);`,
    `tl.to('#welt', { scale: 1.03, xPercent: -1.2, yPercent: 0.4, duration: ${DAUER / 2}, ease: 'sine.inOut' }, ${DAUER / 2});`,
    ...(m.flaechen || []).map((_, i) => `tl.fromTo('#f${i}', { filter: 'hue-rotate(0deg) saturate(1.15) brightness(1.05)' }, { filter: 'hue-rotate(360deg) saturate(1.15) brightness(1.05)', duration: ${DAUER}, ease: 'none' }, 0);`),
    ...(m.lichter || []).map((_, i) => `tl.fromTo('#l${i}', { opacity: 0.35 }, { opacity: 0.85, duration: ${DAUER / 4}, ease: 'sine.inOut', repeat: 3, yoyo: true }, ${(i * 0.37) % 1});`),
    m.glanz ? `tl.fromTo('#glanz', { xPercent: -160, opacity: 0 }, { xPercent: 160, opacity: 0, duration: 2.2, ease: 'power1.inOut', keyframes: { opacity: [0, 0.55, 0] } }, 1.4);` : '',
  ].filter(Boolean).join('\n');
  return `<!doctype html>
<html lang="de"><head><meta charset="utf-8"><title>Anzeige ${m.id}</title>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<style>
body{margin:0;background:#111}
#root{position:relative;width:${W}px;height:${H}px;overflow:hidden;background:#111}
#welt{position:absolute;inset:0;transform-origin:50% 50%}
#welt > img:first-child{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.flaeche{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.licht{position:absolute;border-radius:50%;background:radial-gradient(closest-side,rgba(255,250,235,.9),rgba(255,244,214,.35) 45%,rgba(255,240,200,0) 100%);mix-blend-mode:screen}
.glanz{position:absolute;top:-20%;bottom:-20%;left:20%;width:38%;transform:skewX(-18deg);background:linear-gradient(90deg,rgba(255,255,255,0),rgba(255,255,255,.55),rgba(255,255,255,0));mix-blend-mode:soft-light}
</style></head>
<body>
<div id="root" data-composition-id="root" data-width="${W}" data-height="${H}" data-duration="${DAUER}">
<div id="welt"><img src="bild.jpg" alt="">${flaechen}${lichter}</div>${glanz}
</div>
<script>
const tl = gsap.timeline({ paused: true });
${anim}
tl.to({}, { duration: 0.01 }, ${DAUER - 0.01});
window.__timelines = { root: tl };
</script>
</body></html>
`;
}

for (const m of MOTIVE) {
  if (NUR && m.id !== NUR) continue;
  const ziel = join(BANNER, `${m.id}.mp4`);
  if (m.quelle === 'video') {
    const intro = arg('--intro');
    if (!intro || !existsSync(intro)) { console.error(`${m.id}: Video mit --intro angeben.`); continue; }
    const d = Number(execFileSync('ffprobe', ['-v', 'error', '-show_entries', 'format=duration', '-of', 'csv=p=0', intro]).toString().trim());
    kodieren(intro, ziel, `fade=t=in:st=0:d=0.4,fade=t=out:st=${(d - 0.4).toFixed(2)}:d=0.4`, m.crf || 28);
  } else {
    const ordner = join(ARBEIT, m.id);
    mkdirSync(ordner, { recursive: true });
    copyFileSync(join(BANNER, `${m.id}.jpg`), join(ordner, 'bild.jpg'));
    writeFileSync(join(ordner, 'index.html'), seite(m));
    if (!process.argv.includes('--nur-kodieren') || !existsSync(join(ordner, 'roh.mp4'))) execFileSync('npx', ['--yes', 'hyperframes@0.8.192', 'render', '-o', 'roh.mp4'], { cwd: ordner, stdio: 'inherit' });
    kodieren(join(ordner, 'roh.mp4'), ziel, '', m.crf || 28);
  }
  console.log(`${m.id}: MP4 ${Math.round(statSync(ziel).size / 1024)} KB, WebM ${Math.round(statSync(ziel.replace(/\.mp4$/, '.webm')).size / 1024)} KB`);
}
