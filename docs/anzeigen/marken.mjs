/**
 * Stil „marke“ für docs/anzeigen/bauen.mjs (Vorgabe Betreiber 20.11.2026: Anzeigen sollen echt
 * wirken, nicht gestellt). Jede Anzeige zeigt die Firma selbst: echte Fotos der Firma, wo es sie
 * gibt, sonst Logo, Firmenfarben, Schrift der Firmenwebsite und belegte Angaben. Keine KI-Szenen,
 * Bewegung nur ruhig (Kamerafahrt über ein Foto, Lichtstreif, Laufband), Anfang = Ende.
 *
 * Layouts („layout“ im Motiv):
 *   foto              echtes Foto mit langsamer Fahrt; breit wahlweise mit Farbfläche und Logo (panel)
 *   almera            Firmenfarben Almera Clean; variante „leistungen“ oder „kontakt“
 *   merzlicht_wand    Videowall als Zeichnung, auf dem Bildschirm wechseln Logo und Angebot
 *   merzlicht_marke   Logo groß auf Nachtblau mit LED-Raster, Angebot und Ort
 *   aj                AJ Sports: Logo, Schrift, Streifen wie am Trikot
 *   hk_referenz       echte Referenz-Website (laut hk-growthoperator.de) im Browser mit Adressleiste und auf dem Handy
 * Rückgabe je Layout: { dateien, inhalt, stil, anim } (dateien relativ zu material/).
 */

const SCHRIFT = {
  poppins: [['Poppins', 600, 'poppins-600.woff2'], ['Poppins', 700, 'poppins-700.woff2'], ['Poppins', 800, 'poppins-800.woff2']],
  raleway: [['Raleway', '100 900', 'raleway.woff2']],
  barlow: [['Barlow Condensed', 700, 'barlowcondensed-700.woff2'], ['Barlow Condensed', 800, 'barlowcondensed-800.woff2']],
  hk: [['Inter', '100 900', 'inter.woff2'], ['Fraunces', '100 900', 'fraunces.woff2']],
};
const schrift = (namen) => {
  const liste = namen.flatMap((n) => SCHRIFT[n]);
  return {
    dateien: liste.map(([, , d]) => 'schrift/' + d),
    css: liste.map(([fam, w, d]) => `@font-face{font-family:'${fam}';font-weight:${w};font-style:normal;src:url('${d}') format('woff2')}`).join('\n'),
  };
};
const datei = (p) => p.split('/').pop();
const istBreit = (fmt) => fmt.w / fmt.h > 2.5;
// Lichtstreif (zweimal je Schleife), startet und endet außerhalb des Bildes.
const streifCss = (deckkraft = 0.45) => `.streif{position:absolute;top:-20%;bottom:-20%;left:0;width:26%;background:linear-gradient(90deg,rgba(255,255,255,0),rgba(255,255,255,${deckkraft}),rgba(255,255,255,0));transform:skewX(-18deg);mix-blend-mode:soft-light;pointer-events:none}`;
const streifAnim = (T) => `tl.fromTo('.streif', { xPercent: -160 }, { xPercent: 520, duration: 2.2, ease: 'power1.inOut' }, 1.1);\ntl.fromTo('.streif', { xPercent: -160 }, { xPercent: 520, duration: 2.2, ease: 'power1.inOut' }, ${T / 2 + 1.1});`;
// Langsame Fahrt (heran und zurück), Anfang = Ende.
const fahrt = (sel, T, z) => `tl.fromTo('${sel}', { scale: 1 }, { scale: ${z}, duration: ${T / 2}, ease: 'sine.inOut' }, 0);\ntl.to('${sel}', { scale: 1, duration: ${T / 2}, ease: 'sine.inOut' }, ${T / 2});`;

// foto: echtes Firmenfoto, ruhige Fahrt zum Zentrum (zentrum: Punkt im Foto, z. B. Logo auf dem Shirt).
function foto(m, fmt, h) {
  const p = istBreit(fmt) && m.panel ? m.panel : null;
  const pw = p ? Math.round(fmt.h * (p.breite || 1.25)) : 0, rw = fmt.w - pw;
  const s = p ? schrift([p.schrift || 'poppins']) : { dateien: [], css: '' };
  const rechts = p && p.seite === 'rechts';
  const inhalt = `<div class="foto" style="left:${rechts ? 0 : pw}px;width:${rw}px">${h.welt('w0', m.bild, rw, fmt.h, m.fokus, m.zentrum || 'fokus')}</div>`
    + (p ? `<div class="panel" style="${rechts ? 'right' : 'left'}:0;width:${pw}px;background:${p.farbe}"><img class="logo" src="${datei(p.logo)}" alt="" style="height:${p.logo_h || 150}px">${p.zeile ? `<span class="zeile">${p.zeile}</span>` : ''}</div>` : '');
  const stil = `${s.css}
.foto{position:absolute;top:0;bottom:0;overflow:hidden}
.panel{position:absolute;top:0;bottom:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:22px;box-shadow:inset -1px 0 0 rgba(255,255,255,.08)}
.panel .logo{display:block;width:auto}
.panel .zeile{font:600 22px/1.2 'Poppins',sans-serif;letter-spacing:.06em;text-transform:uppercase;color:rgba(255,255,255,.82)}`;
  return { dateien: [m.bild, ...(p ? [p.logo] : []), ...s.dateien], inhalt, stil, anim: fahrt('#w0', h.T, m.zoom || 1.07) };
}

// almera: Farben und Schrift der Website (Petrol #157372, Mint #e4f0ed, Nachtblau #2c344c, Raleway).
function almera(m, fmt, h) {
  const b = istBreit(fmt), s = schrift(['raleway']);
  const PETROL = '#157372', MINT = '#e4f0ed', NACHT = '#2c344c';
  let inhalt, stil;
  if (m.variante === 'kontakt') {
    inhalt = b
      ? `<div class="flaeche"></div><img class="logo" src="almera-weiss.png" alt="" style="left:64px;top:${(fmt.h - 236) / 2}px;height:236px">
<div class="block" style="left:560px;top:70px"><span class="klein">Angebot anfragen</span><span class="tel">0176 814 124 41</span><span class="web">almera-clean.de · Kettenweg 1, Merzenich</span></div>`
      : `<div class="flaeche"></div><img class="logo" src="almera-weiss.png" alt="" style="left:${(fmt.w - 368) / 2}px;top:44px;height:210px">
<div class="block mitte" style="left:0;right:0;top:282px"><span class="klein">Angebot anfragen</span><span class="tel">0176 814 124 41</span><span class="web">almera-clean.de · Kettenweg 1, Merzenich</span></div>`;
    stil = `${s.css}
.flaeche{position:absolute;inset:0;background:radial-gradient(120% 140% at 30% 20%,#1b8583 0%,${PETROL} 48%,#0f5a59 100%)}
.logo{position:absolute;width:auto;display:block}
.block{position:absolute;display:flex;flex-direction:column;gap:${b ? 10 : 8}px;font-family:'Raleway',sans-serif;color:#fff}
.block.mitte{align-items:center;text-align:center}
.klein{font-weight:700;font-size:${b ? 30 : 30}px;letter-spacing:.16em;text-transform:uppercase;color:#bfe3dc}
.tel{font-weight:800;font-size:${b ? 92 : 76}px;line-height:1.02;letter-spacing:.01em;font-variant-numeric:lining-nums}
.web{font-weight:600;font-size:${b ? 28 : 28}px;color:#d7eeea;letter-spacing:.02em}
${streifCss(0.5)}`;
  } else {
    inhalt = b
      ? `<div class="links"></div><img class="logo" src="almera-weiss.png" alt="" style="left:${(470 - 350) / 2}px;top:${(fmt.h - 200) / 2}px;height:200px">
<div class="worte" style="left:540px;top:40px"><span>Gebäude</span><span>Fenster</span><span>Wäsche</span></div><span class="ort" style="left:544px;top:${fmt.h - 66}px">Kettenweg 1 · Merzenich</span>`
      : `<img class="logo" src="almera-dunkel.png" alt="" style="left:${fmt.w - 392}px;top:150px;height:190px">
<div class="worte" style="left:70px;top:70px"><span>Gebäude</span><span>Fenster</span><span>Wäsche</span></div><span class="ort" style="left:72px;top:${fmt.h - 82}px">Kettenweg 1 · Merzenich</span>`;
    stil = `${s.css}
#root{background:${MINT}}
.links{position:absolute;left:0;top:0;bottom:0;width:470px;background:${PETROL}}
.logo{position:absolute;width:auto;display:block}
.worte{position:absolute;display:flex;flex-direction:column;font:800 ${b ? 72 : 92}px/1.04 'Raleway',sans-serif;letter-spacing:.01em;text-transform:uppercase;color:${PETROL}}
.worte span{display:block;position:relative}
.worte span::after{content:'';position:absolute;left:2px;bottom:${b ? 6 : 8}px;height:${b ? 5 : 6}px;width:100%;background:${NACHT};opacity:.14;transform-origin:0 50%;transform:scaleX(var(--s,0))}
.ort{position:absolute;font:700 ${b ? 30 : 30}px/1.2 'Raleway',sans-serif;color:${NACHT};letter-spacing:.02em}
${streifCss(0.7)}`;
  }
  const anim = [streifAnim(h.T),
    ...(m.variante === 'kontakt' ? [] : [0, 1, 2].map((i) => `tl.fromTo('.worte span:nth-child(${i + 1})', { '--s': 0 }, { '--s': 1, duration: 0.5, ease: 'power2.out', yoyo: true, repeat: 1, repeatDelay: 1.2 }, ${3.6 + i * 0.35});`))].join('\n');
  return { dateien: ['logos/almera-weiss.png', 'logos/almera-dunkel.png', ...s.dateien], inhalt: inhalt + '<i class="streif"></i>', stil, anim };
}

// merzlicht: Nachtblau #071c24, Gelb #f2b84b, Creme #f3efe4 (Logo von hk-growthoperator.de).
const MZ = { nacht: '#071c24', gelb: '#f2b84b', creme: '#f3efe4' };
function wand({ x, y, w, h, pfosten = 16 }) {
  // Traverse (zwei Pfosten, Querträger) und LED-Fläche; Bildschirm 30 px Rand zum Gestell.
  return `<div class="pfosten" style="left:${x}px;top:${y}px;width:${pfosten}px;height:${h + 60}px"></div>
<div class="pfosten" style="left:${x + w + 60 - pfosten}px;top:${y}px;width:${pfosten}px;height:${h + 60}px"></div>
<div class="traeger" style="left:${x}px;top:${y}px;width:${w + 60}px;height:${pfosten}px"></div>
<div class="schirm" style="left:${x + 30}px;top:${y + 28}px;width:${w}px;height:${h}px">`;
}
const MZ_CSS = `#root{background:radial-gradient(120% 100% at 50% 0%,#123543 0%,${MZ.nacht} 62%)}
.glow{position:absolute;border-radius:50%;filter:blur(40px);background:rgba(255,236,190,.16)}
.rasen{position:absolute;left:0;right:0;bottom:0;background:linear-gradient(#1e5732,#0f3a21)}
.rasen::before{content:'';position:absolute;left:0;right:0;top:0;height:3px;background:rgba(255,255,255,.55)}
.pfosten,.traeger{position:absolute;background:repeating-linear-gradient(45deg,rgba(176,188,192,.85) 0 2px,transparent 2px 11px),repeating-linear-gradient(-45deg,rgba(176,188,192,.85) 0 2px,transparent 2px 11px);box-shadow:inset 0 0 0 2px #aab5b9}
.schirm{position:absolute;overflow:hidden;background:#05090b;box-shadow:0 0 0 3px #1d272b,0 0 70px rgba(242,184,75,.18)}
.schirm::after{content:'';position:absolute;inset:0;background-image:radial-gradient(rgba(0,0,0,.55) 1.1px,transparent 1.4px);background-size:5px 5px;pointer-events:none}
.folie{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;font-family:'Poppins',sans-serif;color:${MZ.creme};opacity:0}
.folie.erste{opacity:1}
.folie b{font-weight:800;color:${MZ.gelb};letter-spacing:.04em;line-height:1}
.folie span{font-weight:700;letter-spacing:.14em;line-height:1.1}
.mzlogo{display:block;width:auto}`;
const folienAnim = (T, n) => {
  // n Folien, gleich lang; die erste kommt am Ende wieder (Anfang = Ende).
  const seg = T / n, d = 0.35, z = [];
  for (let i = 1; i < n; i++) z.push(`tl.to('.f${i - 1}', { opacity: 0, duration: ${d} }, ${seg * i - d});`, `tl.to('.f${i}', { opacity: 1, duration: ${d} }, ${seg * i - d});`);
  z.push(`tl.to('.f${n - 1}', { opacity: 0, duration: ${d} }, ${T - d - 0.05});`, `tl.to('.f0', { opacity: 1, duration: ${d} }, ${T - d - 0.05});`);
  z.push(`tl.fromTo('.schirm', { boxShadow: '0 0 0 3px #1d272b, 0 0 50px rgba(242,184,75,.14)' }, { boxShadow: '0 0 0 3px #1d272b, 0 0 90px rgba(242,184,75,.30)', duration: ${T / 4}, ease: 'sine.inOut', yoyo: true, repeat: 3 }, 0);`);
  return z.join('\n');
};
function merzlicht_wand(m, fmt, h) {
  const b = istBreit(fmt), s = schrift(['poppins']);
  let inhalt;
  if (b) {
    inhalt = `<div class="glow" style="left:560px;top:-120px;width:300px;height:200px"></div><div class="glow" style="left:1180px;top:-120px;width:300px;height:200px"></div>
<div class="rasen" style="height:34px"></div>
<img class="mzlogo" src="merzlicht-hell.png" alt="" style="position:absolute;left:58px;top:104px;width:430px">
<div style="position:absolute;left:62px;top:172px;font:600 31px/1.3 'Poppins',sans-serif;color:${MZ.creme};letter-spacing:.01em">Videowall am Sportplatz<br>Bürgewaldzentrum</div>
${wand({ x: 640, y: 16, w: 560, h: 290, pfosten: 15 })}
<div class="folie erste f0"><b style="font-size:88px">IHRE</b><b style="font-size:88px">WERBUNG</b></div>
<div class="folie f1"><span style="font-size:40px">CLIPS UND</span><span style="font-size:40px">BILDER</span><b style="font-size:40px;margin-top:14px">IM WECHSEL</b></div>
<div class="folie f2"><span style="font-size:30px">SICHTBAR</span><b style="font-size:38px;margin-top:12px">ZUM SPIELFELD</b><b style="font-size:38px;margin-top:6px">UND ZUM MERZPARK</b></div>
</div>`;
  } else {
    inhalt = `<div class="glow" style="left:60px;top:-100px;width:260px;height:200px"></div><div class="glow" style="left:660px;top:-100px;width:260px;height:200px"></div>
<div class="rasen" style="height:62px"></div>
${wand({ x: 165, y: 36, w: 570, h: 330, pfosten: 16 })}
<div class="folie erste f0"><img class="mzlogo" src="merzlicht-hell.png" alt="" style="width:470px"><span style="font-size:24px;margin-top:22px;color:rgba(243,239,228,.8)">VIDEOWALL BÜRGEWALDZENTRUM</span></div>
<div class="folie f1"><span style="font-size:46px">IHRE</span><b style="font-size:104px">WERBUNG</b><span style="font-size:46px">HIER</span></div>
<div class="folie f2"><span style="font-size:40px">SICHTBAR</span><b style="font-size:44px;margin-top:14px">ZUM SPIELFELD</b><b style="font-size:44px;margin-top:6px">UND ZUM MERZPARK</b></div>
</div>`;
  }
  return { dateien: ['logos/merzlicht-hell.png', ...s.dateien], inhalt, stil: `${s.css}\n${MZ_CSS}`, anim: folienAnim(h.T, 3) };
}

// merzlicht_marke: Logo groß auf Nachtblau, LED-Raster wie auf der Wall, Lichtstreif über das Logo.
function merzlicht_marke(m, fmt, h) {
  const b = istBreit(fmt), s = schrift(['poppins']);
  const inhalt = b
    ? `<div class="raster"></div><div class="glow" style="left:-80px;top:-140px;width:520px;height:280px"></div>
<div class="logo" style="left:64px;top:118px;width:600px"><img class="mzlogo" src="merzlicht-hell.png" alt=""><i class="streif"></i></div>
<div class="spalte" style="left:744px;top:92px"><span class="eins">Werbung auf der Videowall</span><b>Sportplatz Bürgewaldzentrum</b><span class="drei">sichtbar zum Spielfeld und zum Merzpark</span></div>`
    : `<div class="raster"></div><div class="glow" style="left:180px;top:-150px;width:600px;height:300px"></div>
<div class="logo" style="left:110px;top:150px;width:740px"><img class="mzlogo" src="merzlicht-hell.png" alt=""><i class="streif"></i></div>
<div class="spalte mitte" style="left:0;right:0;top:300px"><span class="eins">Werbung auf der Videowall</span><b>Sportplatz Bürgewaldzentrum</b><span class="drei">sichtbar zum Spielfeld und zum Merzpark</span></div>`;
  const stil = `${s.css}
#root{background:radial-gradient(110% 120% at 50% 0%,#12394a 0%,${MZ.nacht} 66%)}
.raster{position:absolute;inset:0;background-image:radial-gradient(rgba(243,239,228,.07) 1.2px,transparent 1.6px);background-size:7px 7px}
.glow{position:absolute;border-radius:50%;filter:blur(50px);background:rgba(242,184,75,.16)}
.logo{position:absolute;overflow:hidden}
.mzlogo{display:block;width:100%}
.spalte{position:absolute;display:flex;flex-direction:column;gap:${b ? 12 : 10}px;font-family:'Poppins',sans-serif}
.spalte.mitte{align-items:center;text-align:center}
.spalte .eins{font-weight:700;font-size:${b ? 26 : 28}px;letter-spacing:.14em;text-transform:uppercase;color:rgba(243,239,228,.78)}
.spalte b{font-weight:800;font-size:${b ? 40 : 46}px;color:${MZ.gelb};line-height:1.05}
.spalte .drei{font-weight:600;font-size:${b ? 26 : 28}px;color:${MZ.creme}}
.streif{position:absolute;top:-30%;bottom:-30%;left:0;width:22%;background:linear-gradient(90deg,rgba(255,255,255,0),rgba(255,248,225,.55),rgba(255,255,255,0));transform:skewX(-18deg);mix-blend-mode:overlay}`;
  const T = h.T;
  const anim = [`tl.fromTo('.streif', { xPercent: -160 }, { xPercent: 560, duration: 2.4, ease: 'power1.inOut' }, 1.2);`,
    `tl.fromTo('.streif', { xPercent: -160 }, { xPercent: 560, duration: 2.4, ease: 'power1.inOut' }, ${T / 2 + 1.2});`,
    `tl.fromTo('.glow', { opacity: .7 }, { opacity: 1, duration: ${T / 4}, ease: 'sine.inOut', yoyo: true, repeat: 3 }, 0);`].join('\n');
  return { dateien: ['logos/merzlicht-hell.png', ...s.dateien], inhalt, stil, anim };
}

// aj: Logo AJ Sports, Barlow Condensed, rot-weiße Streifen wie am Trikot-Entwurf (SC-Rot).
function aj(m, fmt, h) {
  const b = istBreit(fmt), s = schrift(['barlow']), P = 36;
  const inhalt = b
    ? `<img class="logo" src="aj-sports-weiss.png" alt="" style="left:70px;top:${(fmt.h - 300) / 2}px;height:300px">
<div class="text" style="left:400px;top:62px"><b style="font-size:132px">TRIKOTS</b><span style="font-size:58px">UND SPORTBEKLEIDUNG</span></div>
<div class="band senkrecht" style="left:1170px;top:-40px;width:110px;height:${fmt.h + 80}px"><i class="muster"></i></div>`
    : `<img class="logo" src="aj-sports-weiss.png" alt="" style="left:64px;top:60px;height:330px">
<div class="text" style="left:388px;top:86px"><b style="font-size:142px">TRIKOTS</b><span style="font-size:52px">UND SPORT-</span><span style="font-size:52px">BEKLEIDUNG</span></div>
<div class="band" style="left:-40px;right:-40px;top:${fmt.h - 92}px;height:56px"><i class="muster"></i></div>`;
  const stil = `${s.css}
#root{background:radial-gradient(130% 120% at 20% 10%,#262626 0%,#0e0e0e 60%)}
.logo{position:absolute;width:auto;display:block}
.text{position:absolute;display:flex;flex-direction:column;font-family:'Barlow Condensed',sans-serif;color:#fff;line-height:.95}
.text b{font-weight:800;letter-spacing:.01em}
.text span{font-weight:700;letter-spacing:.04em;color:#d8d8d8}
.band{position:absolute;overflow:hidden;transform:skewY(-4deg)}
.band.senkrecht{transform:skewX(-12deg)}
.muster{position:absolute;inset:0 -${P * 8}px 0 0;background:repeating-linear-gradient(90deg,#d42a2e 0 ${P / 2}px,#f4f1ea ${P / 2}px ${P}px)}
.band.senkrecht .muster{inset:-${P * 8}px 0 0 0;background:repeating-linear-gradient(0deg,#d42a2e 0 ${P / 2}px,#f4f1ea ${P / 2}px ${P}px)}`;
  // Streifen laufen genau 8 Perioden je Schleife: Anfang = Ende.
  const anim = b ? `tl.fromTo('.muster', { y: 0 }, { y: ${P * 8}, duration: ${h.T}, ease: 'none' }, 0);` : `tl.fromTo('.muster', { x: 0 }, { x: ${-P * 8}, duration: ${h.T}, ease: 'none' }, 0);`;
  return { dateien: ['logos/aj-sports-weiss.png', ...s.dateien], inhalt, stil, anim };
}

// hk_referenz: echte Website (Bildschirmfoto ohne Anzeigen) im Browser mit Adressleiste und auf dem Handy.
function hk_referenz(m, fmt, h) {
  const b = istBreit(fmt), s = schrift(['hk']);
  const bw = b ? 800 : 640, bh = b ? 330 : 400, hw = b ? 150 : 150, hh = bh;
  const gruppe = bw + 36 + hw, links = Math.round((fmt.w - gruppe) / 2), top = Math.round((fmt.h - bh) / 2) + (b ? 0 : 8);
  const leiste = b ? 32 : 32;
  const bild = h.masse(m.bild), hoehe = Math.round(bild.h * bw / bild.w), lauf = Math.max(0, Math.min(m.lauf || 1400, hoehe - (bh - leiste)));
  const handy = h.masse(m.handy), sw = hw - 14, hmax = Math.max(0, Math.round(handy.h * sw / handy.w) - (hh - 14)), hl = Math.min(m.handyLauf || 700, hmax);
  const inhalt = `<div class="glow" id="g1"></div><div class="glow" id="g2"></div>
<div id="fenster" style="left:${links}px;top:${top}px;width:${bw}px;height:${bh}px"><div id="leiste" style="height:${leiste}px"><i style="left:14px"></i><i style="left:30px"></i><i style="left:46px"></i><span id="adresse"><em></em>${m.adresse}</span></div><div id="bildschirm" style="top:${leiste}px;height:${bh - leiste}px"><img id="desk" src="${datei(m.bild)}" alt=""></div></div>
<div id="phone" style="left:${links + bw + 36}px;top:${top}px;width:${hw}px;height:${hh}px"><div class="scr" style="width:${sw}px;height:${hh - 14}px"><img id="hand" src="${datei(m.handy)}" alt=""></div></div>
<span id="marke" style="left:${links - 14}px;top:${top - 18}px">Referenz</span>`;
  const stil = `${s.css}
#root{background:#0d0e0c}
.glow{position:absolute;border-radius:50%;filter:blur(50px)}
#g1{left:${links + bw * 0.45}px;top:-170px;width:620px;height:360px;background:rgba(43,115,83,.55)}
#g2{left:-140px;top:${fmt.h * 0.45}px;width:560px;height:300px;background:rgba(158,245,181,.10)}
#fenster{position:absolute;border-radius:10px;overflow:hidden;background:#1d1f1c;box-shadow:0 26px 54px rgba(0,0,0,.55),inset 0 0 0 1px rgba(255,255,255,.07)}
#leiste{position:absolute;left:0;top:0;right:0;background:#1d1f1c}
#leiste i{position:absolute;top:12px;width:9px;height:9px;border-radius:50%;background:#3d403b}
#adresse{position:absolute;left:${Math.round(bw / 2 - 170)}px;top:6px;width:340px;height:20px;border-radius:10px;background:#2a2d28;color:#c9c8c0;font:500 13px/20px 'Inter',sans-serif;text-align:center;letter-spacing:.01em}
#adresse em{display:inline-block;width:8px;height:9px;margin-right:7px;border:1.5px solid #9ef5b5;border-radius:2px;vertical-align:-1px}
#bildschirm{position:absolute;left:0;right:0;overflow:hidden;background:#f6f5f0}
#bildschirm img{position:absolute;left:0;top:0;width:100%;display:block}
#phone{position:absolute;background:#1d1f1c;border-radius:26px;box-shadow:0 24px 46px rgba(0,0,0,.6),inset 0 0 0 1px rgba(255,255,255,.08)}
#phone .scr{position:absolute;left:7px;top:7px;overflow:hidden;border-radius:20px;background:#f6f5f0}
#phone img{position:absolute;left:0;top:0;width:100%;display:block}
#marke{position:absolute;padding:7px 14px;border-radius:999px;background:#9ef5b5;color:#0b1a10;font:700 ${b ? 20 : 22}px/1 'Inter',sans-serif;letter-spacing:.06em;text-transform:uppercase;box-shadow:0 8px 20px rgba(0,0,0,.35)}`;
  const T = h.T;
  const anim = [
    `tl.fromTo('#desk', { y: 0 }, { y: ${-lauf}, duration: ${T / 2}, ease: 'sine.inOut' }, 0);`,
    `tl.to('#desk', { y: 0, duration: ${T / 2}, ease: 'sine.inOut' }, ${T / 2});`,
    `tl.fromTo('#hand', { y: 0 }, { y: ${-hl}, duration: ${T / 2}, ease: 'sine.inOut' }, 0);`,
    `tl.to('#hand', { y: 0, duration: ${T / 2}, ease: 'sine.inOut' }, ${T / 2});`,
    `tl.fromTo('#phone', { y: 0, rotation: -1.2 }, { y: -6, rotation: 0.8, duration: ${T / 2}, ease: 'sine.inOut' }, 0);`,
    `tl.to('#phone', { y: 0, rotation: -1.2, duration: ${T / 2}, ease: 'sine.inOut' }, ${T / 2});`,
  ].join('\n');
  return { dateien: [m.bild, m.handy, ...s.dateien], inhalt, stil, anim };
}

export const LAYOUTS = { foto, almera, merzlicht_wand, merzlicht_marke, aj, hk_referenz };
