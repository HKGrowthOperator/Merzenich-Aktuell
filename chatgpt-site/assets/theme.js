/*
 * Darstellung: Hell (Standard) oder Dunkel. Die Wahl bleibt im Browser
 * (localStorage "merzenich-theme"). Der Schalter sitzt im Kopf neben dem
 * Menue und oeffnet eine kleine Einstellungs-Schublade. Beim Wechsel wird
 * das Logo getauscht: logo-on-light.png auf hellem, logo.png auf dunklem
 * Grund. Ein Inline-Skript im <head> setzt data-theme schon vor dem ersten
 * Rendern, damit nichts flackert; hier wird nur noch bedient.
 */
(() => {
  const SPEICHER = 'merzenich-theme';
  const HELL = 'light';
  const DUNKEL = 'dark';
  const LOGO_HELL = '/assets/img/logo-on-light.png';
  const LOGO_DUNKEL = '/assets/img/logo.png';

  function gespeichert() {
    try { return localStorage.getItem(SPEICHER) === DUNKEL ? DUNKEL : HELL; } catch (e) { return HELL; }
  }

  function anwenden(thema, merken) {
    const naechstes = thema === DUNKEL ? DUNKEL : HELL;
    document.documentElement.dataset.theme = naechstes;
    document.documentElement.style.colorScheme = naechstes;

    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute('content', naechstes === DUNKEL ? '#121212' : '#ffffff');

    document.querySelectorAll('.masthead .logo img, .compact-brand img').forEach((img) => {
      img.src = naechstes === DUNKEL ? LOGO_DUNKEL : LOGO_HELL;
    });

    document.querySelectorAll('[data-theme-option]').forEach((knopf) => {
      const aktiv = knopf.dataset.themeOption === naechstes;
      knopf.classList.toggle('is-active', aktiv);
      knopf.setAttribute('aria-pressed', String(aktiv));
    });

    if (merken) {
      try { localStorage.setItem(SPEICHER, naechstes); } catch (e) { /* privater Modus: gilt nur fuer diese Seite */ }
    }
  }

  const iconEinstellungen = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 8.25A3.75 3.75 0 1 0 12 15.75 3.75 3.75 0 0 0 12 8.25Zm8.25 3.75a6.7 6.7 0 0 0-.09-1.08l2.02-1.57-2-3.46-2.52 1a8.2 8.2 0 0 0-1.87-1.08L15.4 3h-4l-.39 2.81a8.2 8.2 0 0 0-1.87 1.08l-2.52-1-2 3.46 2.02 1.57A6.7 6.7 0 0 0 6.55 12c0 .37.03.73.09 1.08l-2.02 1.57 2 3.46 2.52-1c.57.45 1.2.81 1.87 1.08L11.4 21h4l.39-2.81a8.2 8.2 0 0 0 1.87-1.08l2.52 1 2-3.46-2.02-1.57c.06-.35.09-.71.09-1.08Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>';
  const iconSonne = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="3.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M12 2.5v2M12 19.5v2M4.5 12h-2M21.5 12h-2M5.28 5.28 6.7 6.7M17.3 17.3l1.42 1.42M18.72 5.28 17.3 6.7M6.7 17.3l-1.42 1.42" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>';
  const iconMond = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19.25 15.5A8.2 8.2 0 0 1 8.5 4.75 8.25 8.25 0 1 0 19.25 15.5Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>';

  function einrichten() {
    const aktionen = document.querySelector('.masthead .mast-actions');
    if (!aktionen || document.getElementById('einstellungen')) return;

    const knopf = document.createElement('button');
    knopf.type = 'button';
    knopf.className = 'darstellung-knopf';
    knopf.setAttribute('aria-label', 'Darstellung einstellen');
    knopf.setAttribute('aria-controls', 'einstellungen');
    knopf.setAttribute('aria-expanded', 'false');
    knopf.title = 'Darstellung';
    knopf.innerHTML = iconEinstellungen + '<span class="darstellung-knopf__text">Darstellung</span>';
    const menue = aktionen.querySelector('.mobile-search') || aktionen.querySelector('.menu-btn');
    menue ? aktionen.insertBefore(knopf, menue) : aktionen.append(knopf);

    const schleier = document.createElement('div');
    schleier.className = 'einstellungen-schleier';
    schleier.hidden = true;

    const lade = document.createElement('aside');
    lade.className = 'einstellungen';
    lade.id = 'einstellungen';
    lade.setAttribute('role', 'dialog');
    lade.setAttribute('aria-modal', 'true');
    lade.setAttribute('aria-labelledby', 'einstellungen-titel');
    lade.setAttribute('aria-hidden', 'true');
    lade.innerHTML =
      '<div class="einstellungen__kopf"><h2 id="einstellungen-titel">Einstellungen</h2>' +
      '<button type="button" class="einstellungen__zu" aria-label="Einstellungen schließen">×</button></div>' +
      '<div class="einstellungen__abschnitt"><div class="einstellungen__titel">Darstellung</div>' +
      '<div class="thema-wahl" role="group" aria-label="Farbschema">' +
      '<button type="button" data-theme-option="light" aria-pressed="false">' + iconSonne + '<span>Hell</span></button>' +
      '<button type="button" data-theme-option="dark" aria-pressed="false">' + iconMond + '<span>Dunkel</span></button>' +
      '</div><p class="einstellungen__hilfe">Hell ist die Standardansicht. Ihre Auswahl bleibt auf diesem Gerät gespeichert.</p></div>';

    document.body.append(schleier, lade);

    const zu = lade.querySelector('.einstellungen__zu');
    let ausloeser = null;

    function oeffnen() {
      ausloeser = document.activeElement;
      schleier.hidden = false;
      window.requestAnimationFrame(() => { schleier.classList.add('is-open'); lade.classList.add('is-open'); });
      lade.setAttribute('aria-hidden', 'false');
      knopf.setAttribute('aria-expanded', 'true');
      document.body.classList.add('einstellungen-offen');
      zu.focus();
    }

    function schliessen() {
      schleier.classList.remove('is-open');
      lade.classList.remove('is-open');
      lade.setAttribute('aria-hidden', 'true');
      knopf.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('einstellungen-offen');
      window.setTimeout(() => { schleier.hidden = true; }, 200);
      if (ausloeser && typeof ausloeser.focus === 'function') ausloeser.focus();
    }

    knopf.addEventListener('click', oeffnen);
    zu.addEventListener('click', schliessen);
    schleier.addEventListener('click', schliessen);
    lade.querySelectorAll('[data-theme-option]').forEach((b) => b.addEventListener('click', () => anwenden(b.dataset.themeOption, true)));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && lade.classList.contains('is-open')) schliessen(); });

    anwenden(gespeichert(), false);
  }

  anwenden(gespeichert(), false);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', einrichten, { once: true });
  else einrichten();
})();
