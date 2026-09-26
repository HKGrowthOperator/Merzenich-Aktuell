/*
 * Kopf klappt beim Scrollen ein (Desktop): Der grosse Kopf mit Logo, Suche
 * und Uhrzeit schiebt sich nach oben weg, die Ressortleiste bleibt oben.
 * Im kompakten Zustand steht links das Monogramm (assets/marke/monogramm.svg).
 * Mobil bleibt der Kopf kompakt.
 */
(() => {
  const html = document.documentElement;
  const mast = document.querySelector('.masthead');
  if (!mast) return;
  let kompakt = null, angefordert = false;
  function aktualisieren() {
    angefordert = false;
    const schwelle = mast.offsetHeight + 40;
    const naechster = window.innerWidth > 767 && window.scrollY > schwelle;
    if (naechster === kompakt) return;
    kompakt = naechster;
    html.classList.toggle('kopf-kompakt', naechster);
  }
  function anfordern() { if (angefordert) return; angefordert = true; requestAnimationFrame(aktualisieren); }
  aktualisieren();
  addEventListener('scroll', anfordern, { passive: true });
  addEventListener('resize', anfordern, { passive: true });
  addEventListener('pageshow', anfordern);
})();

(() => {
  if (document.querySelector('script[data-ma-startbilder]')) return;
  const script = document.createElement('script');
  script.src = '/assets/bild-fallbacks.js?v=7e174a59c1';
  script.async = false;
  script.dataset.maStartbilder = '1';
  document.head.append(script);
})();

(() => {
  if (!document.querySelector('link[data-ma-home-polish]')) {
    const style = document.createElement('link');
    style.rel = 'stylesheet';
    style.href = '/assets/homepage-polish.css?v=b6dbe62a97';
    style.dataset.maHomePolish = '1';
    document.head.append(style);
  }
  if (!document.querySelector('script[data-ma-home-polish]')) {
    const script = document.createElement('script');
    script.src = '/assets/homepage-polish.js?v=5174c2bc37';
    script.async = false;
    script.dataset.maHomePolish = '1';
    document.head.append(script);
  }
})();

(() => {
  if (document.querySelector('script[data-ma-content-refresh]')) return;
  const script = document.createElement('script');
  script.src = '/assets/content-refresh-2026-09-17.js?v=9e2ce18de7';
  script.async = false;
  script.dataset.maContentRefresh = '1';
  document.head.append(script);
})();

(() => {
  if (!document.querySelector('link[data-ma-editorial-audit]')) {
    const style = document.createElement('link');
    style.rel = 'stylesheet';
    style.href = '/assets/editorial-audit.css?v=4e7a3dd037';
    style.dataset.maEditorialAudit = '1';
    document.head.append(style);
  }
  if (!document.querySelector('script[data-ma-editorial-audit]')) {
    const script = document.createElement('script');
    script.src = '/assets/editorial-audit.js?v=d3a1d02094';
    script.async = false;
    script.dataset.maEditorialAudit = '1';
    document.head.append(script);
  }
  // Ortswahl: das Aufklappen kann <details> allein. JavaScript ergaenzt nur,
  // was ohne Skript niemand erwartet - Schliessen bei Klick daneben und mit
  // Escape. Faellt das Skript aus, bleibt die Wahl voll bedienbar.
  {
    const wahl = document.querySelector('.ortswahl-schalter');
    if (wahl) {
      document.addEventListener('click', (e) => { if (wahl.open && !wahl.contains(e.target)) wahl.open = false; });
      document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape' || !wahl.open) return;
        wahl.open = false;
        const s = wahl.querySelector('summary'); if (s) s.focus();
      });
    }
  }
})();

// MERZENICH · JETZT (Anhang A4.3): Die Zeile steht fertig im HTML (juengste
// Meldung mit Uhrzeit). Hier kommen nur die zeitabhaengigen Teile dazu:
// relative Zeit, Zahl der Meldungen von heute und der naechste Termin aus der
// Servicespalte. Ohne Skript bleibt die ausgelieferte Zeile stehen.
(() => {
  const zeile = document.querySelector('.jetzt');
  if (!zeile) return;
  const tag = (d) => new Intl.DateTimeFormat('de-DE', { timeZone: 'Europe/Berlin', year: 'numeric', month: '2-digit', day: '2-digit' }).format(d);
  const jetzt = new Date();
  const zeit = zeile.querySelector('.jetzt-neu time');
  if (zeit) {
    const d = new Date(zeit.dateTime); const min = Math.round((jetzt - d) / 60000);
    if (Number.isFinite(min) && min >= 0) {
      if (min < 60) zeit.textContent = `vor ${Math.max(min, 1)} Min.`;
      else if (min < 24 * 60) zeit.textContent = `vor ${Math.round(min / 60)} Std.`;
    }
  }
  const heute = zeile.querySelector('.jetzt-heute');
  if (heute) {
    const n = (heute.dataset.daten || '').split(' ').filter((iso) => iso && tag(new Date(iso)) === tag(jetzt)).length;
    if (n) { heute.textContent = `Heute ${n} ${n === 1 ? 'neue Meldung' : 'neue Meldungen'}`; heute.hidden = false; }
  }
  const termin = zeile.querySelector('.jetzt-termin');
  const reihe = [...document.querySelectorAll('.portal-service .agenda-row')]
    .find((r) => !r.hidden && !(Date.parse(r.dataset.eventEnd || '') < jetzt.getTime()));
  const titel = reihe && reihe.querySelector('h3 a, h3');
  const wann = reihe && reihe.querySelector('p');
  if (termin && titel) {
    const a = document.createElement('a');
    a.href = titel.getAttribute('href') || '/termine/';
    a.textContent = titel.textContent.trim();
    termin.textContent = 'Nächster Termin ';
    termin.append(a);
    const erst = wann ? wann.textContent.trim().split(' · ')[0] : '';
    const datum = reihe.querySelector('.agenda-date');
    const tagText = datum ? `${(datum.querySelector('b') || {}).textContent || ''}. ${(datum.querySelector('span') || {}).textContent || ''}`.trim() : '';
    const angabe = /^bis /.test(erst) ? erst : [tagText, erst].filter(Boolean).join(', ');
    if (angabe) termin.append(` · ${angabe}`);
    termin.hidden = false;
  }
})();


/* Anzeigenrotation: seit 26.09. assets/werbung.js, im Kopf jeder Seite (deploy/kopf-theme-einbinden.mjs). */


(() => {
  if (!document.querySelector('link[data-ma-ressort-dropdowns]')) {
    const style = document.createElement('link');
    style.rel = 'stylesheet';
    style.href = '/assets/ressort-dropdowns.css?v=34999e2883';
    style.dataset.maRessortDropdowns = '1';
    document.head.append(style);
  }
  if (!document.querySelector('script[data-ma-ressort-dropdowns]')) {
    const script = document.createElement('script');
    script.src = '/assets/ressort-dropdowns.js?v=5e50b84980';
    script.defer = true;
    script.dataset.maRessortDropdowns = '1';
    document.head.append(script);
  }
})();
