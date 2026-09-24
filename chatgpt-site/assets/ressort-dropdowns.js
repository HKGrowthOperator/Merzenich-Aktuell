(() => {
  'use strict';

  const desktop = () => matchMedia('(min-width: 768px)').matches;
  const defs = {
    '/nachrichten/': {
      title: 'Aktuell',
      intro: 'Alle aktuellen Meldungen aus der Gemeinde Merzenich.',
      images: [
        ['/assets/editorial-pools/aktuell/aktuell-01-0b6bcf033a-480.webp', 'Aktuelle Meldungen', '/nachrichten/'],
        ['/assets/places/merzenich-720.webp', 'Aus Merzenich und den Ortsteilen', '/merzenich/']
      ],
      groups: [
        ['Nachrichten', [['Alle Meldungen','/nachrichten/'], ['Archiv','/archiv/'], ['Blaulicht','/blaulicht/'], ['Rathaus & Politik','/rathaus/']]],
        ['Orte', [['Merzenich','/merzenich/'], ['Golzheim','/golzheim/'], ['Girbelsrath','/girbelsrath/'], ['Morschenich','/morschenich/'], ['Bürgewald','/buergewald/']]]
      ]
    },
    '/blaulicht/': {
      title: 'Blaulicht',
      intro: 'Polizei, Feuerwehr, Rettung und Verkehr – nach Thema aufgeschlüsselt.',
      images: [
        ['/assets/editorial-pools/blaulicht/blaulicht-01-77105a0ca0-480.webp', 'Blaulichtmeldungen', '/blaulicht/'],
        ['/assets/editorial-pools/polizei/polizei-01-975326909c-480.webp', 'Polizei & Sicherheit', '/thema/polizei/']
      ],
      groups: [
        ['Einsatzlagen', [['Alle Blaulichtmeldungen','/blaulicht/'], ['Polizei','/thema/polizei/'], ['Feuerwehr','/thema/feuerwehr/'], ['Brand','/thema/brand/']]],
        ['Verkehr & Sicherheit', [['Geschwindigkeit','/thema/geschwindigkeit/'], ['Verkehrssicherheit','/thema/verkehrssicherheit/'], ['Verkehrsunfall','/thema/verkehrsunfall/'], ['L 264','/thema/l264/']]]
      ]
    },
    '/sport/': {
      title: 'Sport',
      intro: 'Vereine, Mannschaften, Spieltage und Ergebnisse aus der Gemeinde.',
      images: [
        ['/assets/editorial-pools/sport/sport-01-4ab6e95eab-480.webp', 'Sport aus der Gemeinde', '/sport/'],
        ['/assets/editorial-pools/sport/sport-02-11b84171a8-480.webp', 'Vereine & Fußball', '/sc-1919-merzenich/']
      ],
      groups: [
        ['Vereine', [['Alle Sportmeldungen','/sport/'], ['SC 1919 Merzenich','/sc-1919-merzenich/'], ['FC Golzheim','/thema/fc-golzheim/'], ['SV Morschenich','/thema/sv-morschenich/']]],
        ['Fußball', [['Fußball','/thema/fussball/'], ['Kreisliga A','/thema/kreisliga-a/'], ['Kreisliga B','/thema/kreisliga-b/'], ['Kreisliga C','/thema/kreisliga-c/']]]
      ]
    },
    '/termine/': {
      title: 'Termine',
      intro: 'Was in Merzenich und den Ortsteilen ansteht.',
      images: [
        ['/assets/editorial-pools/termine/termine-01-3e5529edd1-480.webp', 'Termine in Merzenich', '/termine/'],
        ['/assets/editorial-pools/termine/termine-02-1278784fc5-480.webp', 'Veranstaltungen & Ortsleben', '/thema/veranstaltungen/']
      ],
      groups: [
        ['Kalender', [['Alle Termine','/termine/'], ['Veranstaltungen','/thema/veranstaltungen/'], ['Ortsfest','/thema/ortsfest/'], ['Oldieabend','/thema/oldieabend/']]],
        ['Mitmachen', [['Termin melden','/termine/melden/'], ['Meldung senden','/meldung-senden/'], ['Vereine','/vereine/'], ['Service','/service/']]]
      ]
    },
    '/vereine/': {
      title: 'Vereine',
      intro: 'Ehrenamt, Gemeinschaft, Feuerwehr, Fanclubs und Vereinsleben.',
      images: [
        ['/assets/editorial-pools/vereine/vereine-01-9c345e6bd7-480.webp', 'Vereinsleben', '/vereine/'],
        ['/assets/editorial-pools/vereine/vereine-02-51d0635a6b-480.webp', 'Ehrenamt & Gemeinschaft', '/thema/ehrenamt/']
      ],
      groups: [
        ['Vereinsleben', [['Alle Vereinsmeldungen','/vereine/'], ['Ehrenamt','/thema/ehrenamt/'], ['Feuerwehr','/thema/feuerwehr/'], ['Fanclub','/thema/fanclub/']]],
        ['Direkt', [['SC 1919 Merzenich','/sc-1919-merzenich/'], ['FC Golzheim','/thema/fc-golzheim/'], ['Meldung senden','/meldung-senden/'], ['Termin melden','/termine/melden/']]]
      ]
    },
    '/rathaus/': {
      title: 'Rathaus & Politik',
      intro: 'Verwaltung, Rat, Beteiligung und kommunale Entscheidungen – sachlich gebündelt.',
      images: [
        ['/assets/places/merzenich-720.webp', 'Rathaus & Gemeinde', '/rathaus/'],
        ['/assets/uploads/buergersprechstunde.webp', 'Beteiligung & Bürgerservice', '/thema/beteiligung/']
      ],
      groups: [
        ['Rathaus', [['Alle Rathausmeldungen','/rathaus/'], ['Gemeinderat','/thema/gemeinderat/'], ['Bürgermeister','/thema/buergermeister/'], ['Haushalt','/thema/haushalt/']]],
        ['Kommunale Themen', [['Kommunalpolitik','/thema/kommunalpolitik/'], ['Beteiligung','/thema/beteiligung/'], ['Mobilität','/thema/mobilitaet/'], ['Service','/service/']]]
      ]
    },
    '/leben/': {
      title: 'Leben',
      intro: 'Schule, Freizeit, Umwelt, Familie und Alltag in der Gemeinde.',
      images: [
        ['/assets/editorial-pools/leben/leben-01-1091fb6a9c-480.webp', 'Leben in der Gemeinde', '/leben/'],
        ['/assets/editorial-pools/leben/leben-02-87ffe0734e-480.webp', 'Freizeit & Alltag', '/thema/freizeit/']
      ],
      groups: [
        ['Gemeindeleben', [['Alle Meldungen','/leben/'], ['Grundschule','/thema/grundschule/'], ['Freizeit','/thema/freizeit/'], ['Umwelt','/thema/umwelt/']]],
        ['Menschen & Natur', [['Naturschutz','/thema/naturschutz/'], ['Jubiläum','/thema/jubilaeum/'], ['Hochzeit','/thema/hochzeit/'], ['Menschen','/menschen/']]]
      ]
    },
    '/wirtschaft/': {
      title: 'Wirtschaft',
      intro: 'Unternehmen, Infrastruktur und Strukturwandel rund um Merzenich.',
      images: [
        ['/assets/editorial-pools/wirtschaft/wirtschaft-01-561359542b-480.webp', 'Wirtschaft in Merzenich', '/wirtschaft/'],
        ['/assets/editorial-pools/wirtschaft/wirtschaft-02-d6eb71c3ef-480.webp', 'Infrastruktur & Wandel', '/thema/strukturwandel/']
      ],
      groups: [
        ['Wirtschaft', [['Alle Wirtschaftsmeldungen','/wirtschaft/'], ['Strukturwandel','/thema/strukturwandel/'], ['Rheinisches Revier','/thema/rheinisches-revier/'], ['Glasfaser','/thema/glasfaser/']]],
        ['Infrastruktur', [['Wärmeplanung','/thema/waermeplanung/'], ['Stromnetz','/thema/stromnetz/'], ['Jobs','/jobs/'], ['Immobilien','/immobilien/']]]
      ]
    },
    '/tipp/': {
      title: 'Tipp',
      intro: 'Freizeitideen, Ausflüge und klar gekennzeichnete Empfehlungen.',
      images: [
        ['/assets/editorial-pools/tipp/tipp-01-b85a6ff84c-480.webp', 'Tipps aus der Region', '/tipp/'],
        ['/assets/editorial-pools/tipp/tipp-02-3752acb551-480.webp', 'Freizeit & Ausflüge', '/thema/ausflug/']
      ],
      groups: [
        ['Freizeit', [['Alle Tipps','/tipp/'], ['Ausflug','/thema/ausflug/'], ['Freizeit','/thema/freizeit/'], ['Fahrrad','/thema/fahrrad/']]],
        ['Service', [['Termine','/termine/'], ['Orte','/nachrichten/'], ['Anzeige aufgeben','/anzeigen/aufgeben/'], ['Werben','/werben/']]]
      ]
    },
    '/menschen/': {
      title: 'Menschen',
      intro: 'Porträts, Ehrungen, Jubiläen und Geschichten aus der Gemeinde.',
      images: [
        ['/assets/editorial-pools/menschen/menschen-01-686bb6fb4c-480.webp', 'Menschen aus der Gemeinde', '/menschen/'],
        ['/assets/editorial-pools/menschen/menschen-02-7999ac1cce-480.webp', 'Ehrenamt & Geschichten', '/thema/ehrenamt/']
      ],
      groups: [
        ['Menschen', [['Alle Menschen-Themen','/menschen/'], ['Jubiläum','/thema/jubilaeum/'], ['Ehrenamt','/thema/ehrenamt/'], ['Hochzeit','/thema/hochzeit/']]],
        ['Aus der Gemeinde', [['Merzenich','/merzenich/'], ['Golzheim','/golzheim/'], ['Girbelsrath','/girbelsrath/'], ['Morschenich & Bürgewald','/morschenich/']]]
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

    function close(options = {}) {
      const focus = !!options.focus;
      if (!active) return;
      active.classList.remove('ressort-open');
      active.setAttribute('aria-expanded','false');
      panel.hidden = true;
      panel.classList.remove('is-open');
      const old = active;
      active = null;
      if (focus) old.focus();
    }

    function visual(image) {
      if (!image) return '';
      const src = image[0], label = image[1], url = image[2];
      return '<a class="ressort-dropdown__visual" href="' + esc(url) + '">' +
        '<span class="ressort-dropdown__image"><img src="' + esc(src) + '" alt="" width="480" height="270" loading="lazy" decoding="async"></span>' +
        '<span class="ressort-dropdown__visual-label">' + esc(label) + '</span>' +
      '</a>';
    }

    function render(link) {
      const href = link.getAttribute('href');
      const def = defs[href];
      if (!def) return;
      const groups = def.groups.map((group, index) => {
        const name = group[0], items = group[1];
        return '<section>' +
          visual(def.images && def.images[index]) +
          '<h3>' + esc(name) + '</h3>' +
          '<div class="ressort-dropdown__links">' +
            items.map((item) => '<a href="' + esc(item[1]) + '">' + esc(item[0]) + '</a>').join('') +
          '</div>' +
        '</section>';
      }).join('');
      inner.innerHTML =
        '<div class="ressort-dropdown__head">' +
          '<div><span>Ressort</span><strong>' + esc(def.title) + '</strong></div>' +
          '<p>' + esc(def.intro) + '</p>' +
          '<a class="ressort-dropdown__all" href="' + esc(href) + '">Alle ' + esc(def.title) + '-Meldungen</a>' +
        '</div>' +
        '<div class="ressort-dropdown__groups">' + groups + '</div>';
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
    nav.classList.add('ressort-dropdowns-ready');
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, {once:true});
  else start();
})();
