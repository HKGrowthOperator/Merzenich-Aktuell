(() => {
  'use strict';

  const desktop = () => matchMedia('(min-width: 768px)').matches;
  const defs = {
    '/nachrichten/': {
      title: 'Aktuell',
      intro: 'Alle aktuellen Meldungen aus der Gemeinde Merzenich.',
      groups: [
        ['Nachrichten', [
          ['Alle Meldungen','/nachrichten/'], ['Archiv','/archiv/'], ['Blaulicht','/blaulicht/'], ['Rathaus & Politik','/rathaus/']
        ]],
        ['Orte', [
          ['Merzenich','/merzenich/'], ['Golzheim','/golzheim/'], ['Girbelsrath','/girbelsrath/'], ['Morschenich','/morschenich/'], ['Bürgewald','/buergewald/']
        ]]
      ]
    },
    '/blaulicht/': {
      title: 'Blaulicht',
      intro: 'Polizei, Feuerwehr, Rettung und Verkehr – nach Thema aufgeschlüsselt.',
      groups: [
        ['Einsatzlagen', [
          ['Alle Blaulichtmeldungen','/blaulicht/'], ['Polizei','/thema/polizei/'], ['Feuerwehr','/thema/feuerwehr/'], ['Brand','/thema/brand/']
        ]],
        ['Verkehr & Sicherheit', [
          ['Geschwindigkeit','/thema/geschwindigkeit/'], ['Verkehrssicherheit','/thema/verkehrssicherheit/'], ['Verkehrsunfall','/thema/verkehrsunfall/'], ['L 264','/thema/l264/']
        ]]
      ]
    },
    '/sport/': {
      title: 'Sport',
      intro: 'Vereine, Mannschaften, Spieltage und Ergebnisse aus der Gemeinde.',
      groups: [
        ['Vereine', [
          ['Alle Sportmeldungen','/sport/'], ['SC 1919 Merzenich','/sc-1919-merzenich/'], ['FC Golzheim','/thema/fc-golzheim/'], ['SV Morschenich','/thema/sv-morschenich/']
        ]],
        ['Fußball', [
          ['Fußball','/thema/fussball/'], ['Kreisliga A','/thema/kreisliga-a/'], ['Kreisliga B','/thema/kreisliga-b/'], ['Kreisliga C','/thema/kreisliga-c/']
        ]]
      ]
    },
    '/termine/': {
      title: 'Termine',
      intro: 'Was in Merzenich und den Ortsteilen ansteht.',
      groups: [
        ['Kalender', [
          ['Alle Termine','/termine/'], ['Veranstaltungen','/thema/veranstaltungen/'], ['Ortsfest','/thema/ortsfest/'], ['Oldieabend','/thema/oldieabend/']
        ]],
        ['Mitmachen', [
          ['Termin melden','/termine/melden/'], ['Meldung senden','/meldung-senden/'], ['Vereine','/vereine/'], ['Service','/service/']
        ]]
      ]
    },
    '/vereine/': {
      title: 'Vereine',
      intro: 'Ehrenamt, Gemeinschaft, Feuerwehr, Fanclubs und Vereinsleben.',
      groups: [
        ['Vereinsleben', [
          ['Alle Vereinsmeldungen','/vereine/'], ['Ehrenamt','/thema/ehrenamt/'], ['Feuerwehr','/thema/feuerwehr/'], ['Fanclub','/thema/fanclub/']
        ]],
        ['Direkt', [
          ['SC 1919 Merzenich','/sc-1919-merzenich/'], ['FC Golzheim','/thema/fc-golzheim/'], ['Meldung senden','/meldung-senden/'], ['Termin melden','/termine/melden/']
        ]]
      ]
    },
    '/rathaus/': {
      title: 'Rathaus & Politik',
      intro: 'Verwaltung, Rat, Beteiligung und kommunale Entscheidungen – sachlich gebündelt.',
      groups: [
        ['Rathaus', [
          ['Alle Rathausmeldungen','/rathaus/'], ['Gemeinderat','/thema/gemeinderat/'], ['Bürgermeister','/thema/buergermeister/'], ['Haushalt','/thema/haushalt/']
        ]],
        ['Kommunale Themen', [
          ['Kommunalpolitik','/thema/kommunalpolitik/'], ['Beteiligung','/thema/beteiligung/'], ['Mobilität','/thema/mobilitaet/'], ['Service','/service/']
        ]]
      ]
    },
    '/leben/': {
      title: 'Leben',
      intro: 'Schule, Freizeit, Umwelt, Familie und Alltag in der Gemeinde.',
      groups: [
        ['Gemeindeleben', [
          ['Alle Meldungen','/leben/'], ['Grundschule','/thema/grundschule/'], ['Freizeit','/thema/freizeit/'], ['Umwelt','/thema/umwelt/']
        ]],
        ['Menschen & Natur', [
          ['Naturschutz','/thema/naturschutz/'], ['Jubiläum','/thema/jubilaeum/'], ['Hochzeit','/thema/hochzeit/'], ['Menschen','/menschen/']
        ]]
      ]
    },
    '/wirtschaft/': {
      title: 'Wirtschaft',
      intro: 'Unternehmen, Infrastruktur und Strukturwandel rund um Merzenich.',
      groups: [
        ['Wirtschaft', [
          ['Alle Wirtschaftsmeldungen','/wirtschaft/'], ['Strukturwandel','/thema/strukturwandel/'], ['Rheinisches Revier','/thema/rheinisches-revier/'], ['Glasfaser','/thema/glasfaser/']
        ]],
        ['Infrastruktur', [
          ['Wärmeplanung','/thema/waermeplanung/'], ['Stromnetz','/thema/stromnetz/'], ['Jobs','/jobs/'], ['Immobilien','/immobilien/']
        ]]
      ]
    },
    '/tipp/': {
      title: 'Tipp',
      intro: 'Freizeitideen, Ausflüge und klar gekennzeichnete Empfehlungen.',
      groups: [
        ['Freizeit', [
          ['Alle Tipps','/tipp/'], ['Ausflug','/thema/ausflug/'], ['Freizeit','/thema/freizeit/'], ['Fahrrad','/thema/fahrrad/']
        ]],
        ['Service', [
          ['Termine','/termine/'], ['Orte','/nachrichten/'], ['Anzeige aufgeben','/anzeigen/aufgeben/'], ['Werben','/werben/']
        ]]
      ]
    },
    '/menschen/': {
      title: 'Menschen',
      intro: 'Porträts, Ehrungen, Jubiläen und Geschichten aus der Gemeinde.',
      groups: [
        ['Menschen', [
          ['Alle Menschen-Themen','/menschen/'], ['Jubiläum','/thema/jubilaeum/'], ['Ehrenamt','/thema/ehrenamt/'], ['Hochzeit','/thema/hochzeit/']
        ]],
        ['Aus der Gemeinde', [
          ['Merzenich','/merzenich/'], ['Golzheim','/golzheim/'], ['Girbelsrath','/girbelsrath/'], ['Morschenich & Bürgewald','/morschenich/']
        ]]
      ]
    }
  };

  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  function start() {
    const nav = document.querySelector('.mainnav');
    const row = nav?.querySelector('.navrow');
    const links = [...(nav?.querySelectorAll('.navscroll > a') || [])];
    if (!nav || !row || !links.length) return;

    const panel = document.createElement('div');
    panel.className = 'ressort-dropdown';
    panel.id = 'ressort-dropdown';
    panel.hidden = true;
    panel.innerHTML = '<div class="shell ressort-dropdown__inner"></div>';
    row.insertAdjacentElement('afterend', panel);
    const inner = panel.querySelector('.ressort-dropdown__inner');
    let active = null;

    function close({focus=false} = {}) {
      if (!active) return;
      active.classList.remove('ressort-open');
      active.setAttribute('aria-expanded','false');
      panel.hidden = true;
      panel.classList.remove('is-open');
      const old = active;
      active = null;
      if (focus) old.focus();
    }

    function render(link) {
      const href = link.getAttribute('href');
      const def = defs[href];
      if (!def) return;
      inner.innerHTML = `
        <div class="ressort-dropdown__head">
          <div><span>Ressort</span><strong>${esc(def.title)}</strong></div>
          <p>${esc(def.intro)}</p>
          <a class="ressort-dropdown__all" href="${href}">Alle ${esc(def.title)}-Meldungen</a>
        </div>
        <div class="ressort-dropdown__groups">
          ${def.groups.map(([name, items]) => `
            <section>
              <h3>${esc(name)}</h3>
              <div class="ressort-dropdown__links">
                ${items.map(([label,url]) => `<a href="${url}">${esc(label)}</a>`).join('')}
              </div>
            </section>`).join('')}
        </div>`;
    }

    function open(link) {
      if (!desktop()) { location.href = link.href; return; }
      if (active === link && !panel.hidden) { close(); return; }
      if (active) {
        active.classList.remove('ressort-open');
        active.setAttribute('aria-expanded','false');
      }
      active = link;
      render(link);
      link.classList.add('ressort-open');
      link.setAttribute('aria-expanded','true');
      panel.hidden = false;
      requestAnimationFrame(() => panel.classList.add('is-open'));
    }

    for (const link of links) {
      if (!defs[link.getAttribute('href')]) continue;
      link.setAttribute('aria-haspopup','true');
      link.setAttribute('aria-controls', panel.id);
      link.setAttribute('aria-expanded','false');
      link.addEventListener('click', (e) => {
        if (!desktop()) return;
        e.preventDefault();
        open(link);
      });
      link.addEventListener('keydown', (e) => {
        if (!desktop()) return;
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          open(link);
          requestAnimationFrame(() => panel.querySelector('a')?.focus());
        }
      });
    }

    panel.addEventListener('click', (e) => {
      if (e.target.closest('a')) close();
    });
    document.addEventListener('click', (e) => {
      if (active && !nav.contains(e.target)) close();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && active) close({focus:true});
    });
    addEventListener('resize', () => { if (!desktop()) close(); }, {passive:true});

    // Das alte nur auf Sport zugeschnittene Hover-Mega wird durch das einheitliche
    // Klick-Dropdown ersetzt. Es bleibt im HTML als Rückfall, wird aber visuell deaktiviert.
    nav.classList.add('ressort-dropdowns-ready');
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, {once:true});
  else start();
})();
