/*
 * Merzenich Aktuell – Anzeigenrotation 24.09.2026 (aus, bis echte Kunden eingetragen sind)
 *
 * Werbung belegt nur bereits vorhandene Werbeflaechen oder echten Restplatz.
 * Sie ersetzt keine redaktionellen Bilder und erzeugt keine dritte Desktop-
 * Spalte. Die Rotation ist zeitbasiert: Reloads starten nicht bei Motiv 1.
 */
(() => {
  'use strict';

  const START = () => {
    if (!document.body || !document.body.classList.contains('home')) return;
    if (window.__maAnzeigenRotation) return;
    window.__maAnzeigenRotation = true;

    const ROTATION_MS = 14000;
    const REDUCED_MOTION = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Nur echte, gebuchte Werbekunden eintragen (KBS 24.09.: keine
       erfundenen Demo-Kunden auf der Live-Seite). Solange die Liste leer ist,
       bleibt die Rotation aus und die Werbebaender zeigen unveraendert KBS
       Management und AJ Sports. Format je Eintrag:
       { id, kunde, typ: 'bank'|'sport', eyebrow, headline, text, cta, ziel } */
    const MOTIVE = [];
    if (!MOTIVE.length) return;

    const slots = new Map();
    let letzterTakt = -1;

    const esc = (wert) => String(wert ?? '').replace(/[&<>"']/g, (zeichen) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[zeichen]);

    function kunst(typ) {
      if (typ === 'sport') {
        return '<span class="ma-ad-art ma-ad-art--sport" aria-hidden="true"><span class="ma-ad-ball"></span><span class="ma-ad-art-word">SV</span></span>';
      }
      return '<span class="ma-ad-art ma-ad-art--bank" aria-hidden="true"><span class="ma-ad-x"></span><span class="ma-ad-art-word">X</span></span>';
    }

    function motivHtml(motiv, format) {
      return `<a class="ma-ad-card ma-ad-card--${esc(format)} ma-ad-theme--${esc(motiv.typ)}" href="${esc(motiv.ziel)}" data-ma-ad-id="${esc(motiv.id)}" aria-label="Anzeige: ${esc(motiv.kunde)} – ${esc(motiv.headline)}">`
        + `${kunst(motiv.typ)}`
        + '<span class="ma-ad-copy">'
        + `<span class="ma-ad-eyebrow">${esc(motiv.eyebrow)}</span>`
        + `<strong>${esc(motiv.headline)}</strong>`
        + `<span class="ma-ad-text">${esc(motiv.text)}</span>`
        + `<span class="ma-ad-cta">${esc(motiv.cta)}</span>`
        + '</span></a>';
    }

    function motivIndex(takt, offset) {
      return ((takt + offset) % MOTIVE.length + MOTIVE.length) % MOTIVE.length;
    }

    function renderSlot(slot, takt, sofort = false) {
      if (!slot || !slot.root || slot.root.hidden) return;
      if (slot.root.matches(':hover') || slot.root.contains(document.activeElement)) return;
      const motiv = MOTIVE[motivIndex(takt, slot.offset)];
      if (slot.root.dataset.maCurrent === motiv.id) return;

      const wechseln = () => {
        slot.root.innerHTML = motivHtml(motiv, slot.format);
        slot.root.dataset.maCurrent = motiv.id;
        slot.root.dataset.maKunde = motiv.kunde;
        if (!REDUCED_MOTION) requestAnimationFrame(() => slot.root.classList.remove('is-changing'));
      };

      if (sofort || REDUCED_MOTION || !slot.root.dataset.maCurrent) {
        wechseln();
        return;
      }
      slot.root.classList.add('is-changing');
      window.setTimeout(wechseln, 180);
    }

    function taktJetzt() {
      return Math.floor(Date.now() / ROTATION_MS);
    }

    function alleRotieren(sofort = false) {
      if (document.hidden) return;
      const takt = taktJetzt();
      if (!sofort && takt === letzterTakt) return;
      letzterTakt = takt;
      slots.forEach((slot) => renderSlot(slot, takt, sofort));
    }

    /* Vorhandenes Werbeband: nur Inhalt tauschen, keine neue Layoutflaeche. */
    const band = document.querySelector('[data-kbs-ad-band]');
    if (band) {
      band.classList.add('ma-ad-band');
      band.innerHTML = '<span class="home-ad-band__label">Anzeige</span><div class="ma-ad-rotator ma-ad-rotator--band" data-ma-ad-slot="band"></div>';
      const root = band.querySelector('[data-ma-ad-slot="band"]');
      if (root) slots.set('band', { root, format: 'band', offset: 0 });
    }

    /* Zweiter Slot nur bei realem Restplatz links. Fuer die Messung wird er
       aus dem Layout genommen. Seine Hoehe + Abstand bleibt <= freie Hoehe. */
    const service = document.querySelector('.portal-top .portal-service');
    const strom = document.querySelector('.portal-top .portal-strom');
    const MIN_GAP = 220;
    const GAP_ABSTAND = 14;
    let gapWrap = null;
    let gapRoot = null;
    let anpassungAngefordert = false;

    function entferneGap() {
      if (!gapWrap) return;
      gapWrap.hidden = true;
      if (gapRoot) slots.delete('service-gap');
    }

    function gapAnpassen() {
      anpassungAngefordert = false;
      if (!service || !strom || window.innerWidth < 960) {
        entferneGap();
        return;
      }

      if (gapWrap) gapWrap.hidden = true;
      const serviceHoehe = service.getBoundingClientRect().height;
      const stromHoehe = strom.getBoundingClientRect().height;
      const frei = Math.floor(stromHoehe - serviceHoehe - GAP_ABSTAND);

      if (frei < MIN_GAP) {
        entferneGap();
        return;
      }

      if (!gapWrap) {
        gapWrap = document.createElement('div');
        gapWrap.className = 'ma-service-gap-ad';
        gapWrap.setAttribute('role', 'group');
        gapWrap.setAttribute('aria-label', 'Anzeige');
        gapWrap.innerHTML = '<span class="ma-service-gap-ad__label">Anzeige</span><div class="ma-ad-rotator ma-ad-rotator--gap" data-ma-ad-slot="service-gap"></div>';
        service.append(gapWrap);
        gapRoot = gapWrap.querySelector('[data-ma-ad-slot="service-gap"]');
      }

      gapWrap.style.setProperty('--ma-gap-height', `${Math.min(frei, 300)}px`);
      gapWrap.hidden = false;
      if (gapRoot) {
        slots.set('service-gap', { root: gapRoot, format: 'gap', offset: 1 });
        renderSlot(slots.get('service-gap'), taktJetzt(), !gapRoot.dataset.maCurrent);
      }
    }

    function gapAnfordern() {
      if (anpassungAngefordert) return;
      anpassungAngefordert = true;
      requestAnimationFrame(gapAnpassen);
    }

    if (service && strom) {
      service.addEventListener('toggle', gapAnfordern, true);
      window.addEventListener('resize', gapAnfordern, { passive: true });
      if ('ResizeObserver' in window) {
        const ro = new ResizeObserver(gapAnfordern);
        ro.observe(strom);
      }
      gapAnpassen();
      window.setTimeout(gapAnfordern, 600);
      window.setTimeout(gapAnfordern, 1800);
    }

    alleRotieren(true);

    /* Globale Taktgrenze: Reload setzt die Kampagne nicht zurueck. */
    const bisZumNaechstenTakt = ROTATION_MS - (Date.now() % ROTATION_MS) + 30;
    window.setTimeout(() => {
      alleRotieren();
      window.setInterval(alleRotieren, ROTATION_MS);
    }, bisZumNaechstenTakt);

    document.addEventListener('visibilitychange', () => {
      if (!document.hidden) alleRotieren(true);
    });
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', START, { once: true });
  else START();
})();
