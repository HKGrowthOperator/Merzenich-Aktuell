/*
 * Merzenich Aktuell — globale Bild-Fallbacks und Symbolbild-Rotation.
 *
 * Regeln:
 * 1. Vorhandene redaktionelle Originalbilder bleiben unangetastet.
 * 2. Fehlt ein Bild, bekommt der Beitrag automatisch ein thematisch passendes Symbolbild.
 * 3. Bereits als Symbolbild gekennzeichnete Markt-/Teaserbilder werden stabil variiert.
 * 4. Die Auswahl ist deterministisch pro Beitrag: kein Flackern bei Reloads.
 * 5. Externe Commons-Dateien laufen über /api/bild; Quellenlinks bleiben sichtbar.
 * Rechte-Check fuer neu ergaenzte Commons-Motive: 17.09.2026.
 */
(() => {
  'use strict';

  const commons = (file, width = 1400) =>
    `https://commons.wikimedia.org/wiki/Special:FilePath/${encodeURIComponent(file).replace(/%2F/g, '/')}?width=${width}`;

  const BILDER = {
    default: [
      {
        src: '/assets/places/merzenich-1440.webp',
        alt: 'Ortsansicht aus Merzenich',
        credit: 'Ortsmotiv · Karl-Heinz Meurer / Wikimedia Commons',
        source: '/assets/places/merzenich-1440.webp'
      },
      {
        src: '/assets/places/golzheim-1440.webp',
        alt: 'Ortsansicht aus Golzheim',
        credit: 'Ortsmotiv · Karl-Heinz Meurer / Wikimedia Commons',
        source: '/assets/places/golzheim-1440.webp'
      },
      {
        src: '/assets/places/girbelsrath-1440.webp',
        alt: 'Ortsansicht aus Girbelsrath',
        credit: 'Ortsmotiv · Käthe und Bernd Limburg / Wikimedia Commons',
        source: '/assets/places/girbelsrath-1440.webp'
      },
      {
        src: '/assets/places/morschenich-1440.webp',
        alt: 'Ortsansicht aus Morschenich',
        credit: 'Ortsmotiv · Papa1234 / Wikimedia Commons',
        source: '/assets/places/morschenich-1440.webp'
      },
      {
        src: '/assets/places/buergewald-1440.webp',
        alt: 'Ortsansicht aus Bürgewald',
        credit: 'Ortsmotiv · Antisyntagmatarchos / Wikimedia Commons',
        source: '/assets/places/buergewald-1440.webp'
      }
    ],

    polizei: [
      {
        src: commons('Neue Streifenwagen für die Polizei vorgestellt.jpg'),
        alt: 'Streifenwagen der Polizei Nordrhein-Westfalen',
        credit: 'Symbolbild · IM NRW / Wikimedia Commons · CC0 1.0',
        source: 'https://commons.wikimedia.org/wiki/File:Neue_Streifenwagen_f%C3%BCr_die_Polizei_vorgestellt.jpg'
      },
      {
        src: commons('Police car NRW 5-6357.jpg'),
        alt: 'Polizeiwagen aus Nordrhein-Westfalen',
        credit: 'Symbolbild · Kaap bij Sneeuw / Wikimedia Commons · CC0 1.0',
        source: 'https://commons.wikimedia.org/wiki/File:Police_car_NRW_5-6357.jpg'
      }
    ],

    feuerwehr: [
      {
        src: commons('Feuerwehrmänner im Einsatz.jpg'),
        alt: 'Feuerwehrkräfte bei einem Löscheinsatz',
        credit: 'Symbolbild · AK-Bino / Wikimedia Commons · CC BY-SA 4.0',
        source: 'https://commons.wikimedia.org/wiki/File:Feuerwehrm%C3%A4nner_im_Einsatz.jpg'
      },
      {
        src: commons('Feuerwehr im Einsatz.jpg'),
        alt: 'Feuerwehr im Einsatz',
        credit: 'Symbolbild · Bärwinkel, Klaus / Wikimedia Commons · CC BY 4.0',
        source: 'https://commons.wikimedia.org/wiki/File:Feuerwehr_im_Einsatz.jpg'
      }
    ],

    gemeinde: [
      {
        src: commons('Merzenich Rathaus HDR.jpg'),
        alt: 'Rathaus der Gemeinde Merzenich',
        credit: 'Symbolbild · Karl-Heinz Meurer / Wikimedia Commons · CC BY-SA 3.0',
        source: 'https://commons.wikimedia.org/wiki/File:Merzenich_Rathaus_HDR.jpg'
      },
      {
        src: '/assets/uploads/buergersprechstunde.webp',
        alt: 'Bürgersprechstunde in Merzenich',
        credit: 'Lokales Redaktionsmotiv · Merzenich Aktuell',
        source: '/assets/uploads/buergersprechstunde.webp'
      },
      {
        src: '/assets/uploads/faire-gemeinde.webp',
        alt: 'Lokale Information aus der Gemeinde Merzenich',
        credit: 'Lokales Redaktionsmotiv · Merzenich Aktuell',
        source: '/assets/uploads/faire-gemeinde.webp'
      }
    ],

    leben: [
      {
        src: commons('Merzenich Alte Pfarrkirche.jpg'),
        alt: 'Alte Pfarrkirche in Merzenich',
        credit: 'Symbolbild · Karl-Heinz Meurer / Wikimedia Commons · CC BY-SA 3.0',
        source: 'https://commons.wikimedia.org/wiki/File:Merzenich_Alte_Pfarrkirche.jpg'
      },
      {
        src: '/assets/uploads/ortsfest.webp',
        alt: 'Lokales Gemeinschaftsleben in Merzenich',
        credit: 'Lokales Redaktionsmotiv · Merzenich Aktuell',
        source: '/assets/uploads/ortsfest.webp'
      },
      {
        src: '/assets/uploads/trauung-alte-kirche.webp',
        alt: 'Alte Kirche in Merzenich als lokales Motiv',
        credit: 'Lokales Redaktionsmotiv · Merzenich Aktuell',
        source: '/assets/uploads/trauung-alte-kirche.webp'
      }
    ],

    termine: [
      {
        src: commons('Merzenich Denkmal-Nr. 18, Lindenplatz (1235).jpg'),
        alt: 'Lindenplatz in Merzenich',
        credit: 'Symbolbild · Käthe und Bernd Limburg / Wikimedia Commons · CC BY-SA 3.0 DE',
        source: 'https://commons.wikimedia.org/wiki/File:Merzenich_Denkmal-Nr._18,_Lindenplatz_(1235).jpg'
      },
      {
        src: '/assets/uploads/ortsfest.webp',
        alt: 'Veranstaltung in Merzenich',
        credit: 'Lokales Redaktionsmotiv · Merzenich Aktuell',
        source: '/assets/uploads/ortsfest.webp'
      },
      {
        src: '/assets/uploads/faire-gemeinde.webp',
        alt: 'Veranstaltung und Aktion in Merzenich',
        credit: 'Lokales Redaktionsmotiv · Merzenich Aktuell',
        source: '/assets/uploads/faire-gemeinde.webp'
      }
    ],

    sport: [
      {
        src: commons('2026-08-30 Fußballplatz Trogen HOF8480 RAW-Export.png'),
        alt: 'Fußballplatz als Symbolbild für Lokalsport',
        credit: 'Symbolbild · PantheraLeo1359531 / Wikimedia Commons · CC BY-SA 4.0',
        source: 'https://commons.wikimedia.org/wiki/File:2026-08-30_Fu%C3%9Fballplatz_Trogen_HOF8480_RAW-Export.png'
      },
      {
        src: '/assets/uploads/sc-1919-merzenich-logo.webp',
        alt: 'SC 1919 Merzenich',
        credit: 'Vereinsmotiv · SC 1919 Merzenich',
        source: '/assets/uploads/sc-1919-merzenich-logo.webp'
      },
      {
        src: '/assets/uploads/fanclub-saisonauftakt.webp',
        alt: 'Fußball und Vereinsleben in Merzenich',
        credit: 'Lokales Sportmotiv · Merzenich Aktuell',
        source: '/assets/uploads/fanclub-saisonauftakt.webp'
      }
    ],

    jobs: [
      {
        src: commons('LOOM office workspaces.jpg'),
        alt: 'Arbeitsplätze in einem Büro als Symbolbild für Stellenangebote',
        credit: 'Symbolbild · Loominade / Wikimedia Commons · CC0 1.0',
        source: 'https://commons.wikimedia.org/wiki/File:LOOM_office_workspaces.jpg'
      },
      {
        src: commons('Desk-office-workspace-coworking (23699033283).jpg'),
        alt: 'Moderner Arbeitsplatz als Symbolbild für Stellenangebote',
        credit: 'Symbolbild · Pixel.la Free Stock Photos / Wikimedia Commons · CC0 1.0',
        source: 'https://commons.wikimedia.org/wiki/File:Desk-office-workspace-coworking_(23699033283).jpg'
      },
      {
        src: commons('Hands-desk-office-working (24326654535).jpg'),
        alt: 'Arbeit am Schreibtisch als Symbolbild für Stellenangebote',
        credit: 'Symbolbild · Pixel.la Free Stock Photos / Wikimedia Commons · CC0 1.0',
        source: 'https://commons.wikimedia.org/wiki/File:Hands-desk-office-working_(24326654535).jpg'
      }
    ],

    immobilien: [
      {
        src: commons('Merzenich Denkmal-Nr. 20, Lindenplatz 5 (1238).jpg'),
        alt: 'Wohnhaus in Merzenich als Symbolbild für Immobilienangebote',
        credit: 'Symbolbild · Käthe und Bernd Limburg / Wikimedia Commons · CC BY-SA 3.0 DE',
        source: 'https://commons.wikimedia.org/wiki/File:Merzenich_Denkmal-Nr._20,_Lindenplatz_5_(1238).jpg'
      },
      {
        src: commons('4 story apartment building on Sofioter Straße in Erfurt.jpg'),
        alt: 'Wohngebäude als Symbolbild für Immobilienangebote',
        credit: 'Symbolbild · Robert von Oliva / Wikimedia Commons · CC0 1.0',
        source: 'https://commons.wikimedia.org/wiki/File:4_story_apartment_building_on_Sofioter_Stra%C3%9Fe_in_Erfurt.jpg'
      },
      {
        src: commons('Laubenheimer Straße 28 Berlin-Wilmersdorf 2024-09-08 01.jpg'),
        alt: 'Wohngebäude als Symbolbild für Immobilienangebote',
        credit: 'Symbolbild · Leonhard Lenz / Wikimedia Commons · CC0 1.0',
        source: 'https://commons.wikimedia.org/wiki/File:Laubenheimer_Stra%C3%9Fe_28_Berlin-Wilmersdorf_2024-09-08_01.jpg'
      }
    ],

    familie: [
      {
        src: '/assets/uploads/trauung-alte-kirche.webp',
        alt: 'Trauung in der Alten Kirche in Merzenich',
        credit: 'Lokales Redaktionsmotiv · Merzenich Aktuell',
        source: '/assets/uploads/trauung-alte-kirche.webp'
      },
      {
        src: commons('Traditional Wedding Ring.jpg'),
        alt: 'Ehering als Symbolbild für Hochzeit und Familienanzeigen',
        credit: 'Symbolbild · A Minty Penguin / Wikimedia Commons · CC0 1.0',
        source: 'https://commons.wikimedia.org/wiki/File:Traditional_Wedding_Ring.jpg'
      },
      {
        src: commons('Two golden wedding rings.jpg'),
        alt: 'Zwei Eheringe als Symbolbild für Familienanzeigen',
        credit: 'Symbolbild · Rgaudin / Wikimedia Commons · CC0 1.0',
        source: 'https://commons.wikimedia.org/wiki/File:Two_golden_wedding_rings.jpg'
      }
    ],

    trauer: [
      {
        src: commons('020 Grave candles.JPG'),
        alt: 'Grablicht als dezentes Symbolbild für Traueranzeigen',
        credit: 'Symbolbild · Usien / Wikimedia Commons · CC0 1.0',
        source: 'https://commons.wikimedia.org/wiki/File:020_Grave_candles.JPG'
      },
      {
        src: commons('Candles memorial.jpg'),
        alt: 'Gedenkkerzen als Symbolbild für Trauer und Erinnerung',
        credit: 'Symbolbild · Fabebk / Wikimedia Commons · CC BY-SA 4.0',
        source: 'https://commons.wikimedia.org/wiki/File:Candles_memorial.jpg'
      },
      {
        src: commons('Merzenich Alte Pfarrkirche.jpg'),
        alt: 'Alte Pfarrkirche in Merzenich als stilles Ortsmotiv',
        credit: 'Symbolbild · Karl-Heinz Meurer / Wikimedia Commons · CC BY-SA 3.0',
        source: 'https://commons.wikimedia.org/wiki/File:Merzenich_Alte_Pfarrkirche.jpg'
      }
    ],

    vereine: [
      {
        src: '/assets/uploads/ortsfest.webp',
        alt: 'Vereins- und Gemeinschaftsleben in Merzenich',
        credit: 'Lokales Redaktionsmotiv · Merzenich Aktuell',
        source: '/assets/uploads/ortsfest.webp'
      },
      {
        src: '/assets/uploads/fanclub-saisonauftakt.webp',
        alt: 'Vereinsleben und Fans in Merzenich',
        credit: 'Lokales Redaktionsmotiv · Merzenich Aktuell',
        source: '/assets/uploads/fanclub-saisonauftakt.webp'
      },
      {
        src: '/assets/places/girbelsrath-1440.webp',
        alt: 'Girbelsrath als lokales Vereins- und Ortsmotiv',
        credit: 'Ortsmotiv · Käthe und Bernd Limburg / Wikimedia Commons',
        source: '/assets/places/girbelsrath-1440.webp'
      }
    ],

    wirtschaft: [
      {
        src: commons('LOOM office workspaces.jpg'),
        alt: 'Arbeitsplätze als Symbolbild für Wirtschaft',
        credit: 'Symbolbild · Loominade / Wikimedia Commons · CC0 1.0',
        source: 'https://commons.wikimedia.org/wiki/File:LOOM_office_workspaces.jpg'
      },
      {
        src: '/assets/uploads/e-tour.webp',
        alt: 'Lokale Wirtschafts- und Mobilitätsaktivität',
        credit: 'Lokales Redaktionsmotiv · Merzenich Aktuell',
        source: '/assets/uploads/e-tour.webp'
      },
      {
        src: '/assets/uploads/leader-region.webp',
        alt: 'Regionale Entwicklung rund um Merzenich',
        credit: 'Lokales Redaktionsmotiv · Merzenich Aktuell',
        source: '/assets/uploads/leader-region.webp'
      }
    ],

    verkehr: [
      {
        src: '/assets/places/merzenich-1440.webp',
        alt: 'Straßen- und Ortsraum in Merzenich',
        credit: 'Ortsmotiv · Karl-Heinz Meurer / Wikimedia Commons',
        source: '/assets/places/merzenich-1440.webp'
      },
      {
        src: '/assets/places/golzheim-1440.webp',
        alt: 'Straßen- und Ortsraum in Golzheim',
        credit: 'Ortsmotiv · Karl-Heinz Meurer / Wikimedia Commons',
        source: '/assets/places/golzheim-1440.webp'
      }
    ]
  };

  const text = (el) => (el?.textContent || '').trim();

  function kontextText(root = document) {
    const section = document.querySelector('meta[property="article:section"]')?.content || '';
    const keywords = document.querySelector('meta[name="news_keywords"]')?.content || '';
    const title = text(root.querySelector?.('h1,h2,h3')) || document.title;
    const kicker = text(root.querySelector?.('.kicker,.eyebrow,.markt-art'));
    const body = text(root.querySelector?.('.dek,.prose,.ev-desc,.meta,.location-line'));
    const classes = root instanceof HTMLElement ? root.className : '';
    return `${section} ${keywords} ${title} ${kicker} ${body} ${classes} ${location.pathname}`.toLocaleLowerCase('de-DE');
  }

  function bildKey(root = document) {
    const explicit = root instanceof HTMLElement ? root.dataset.fallbackKey : '';
    if (explicit && BILDER[explicit]) return explicit;

    const t = kontextText(root);
    const path = location.pathname.toLocaleLowerCase('de-DE');

    if (path.includes('/traueranzeigen/') || /\btrauer|todesanzeige|nachruf|gedenk|verstorben/.test(t)) return 'trauer';
    if (path.includes('/familienanzeigen/') || /hochzeit|trauung|heirat|geburt|jubiläum|jubilaeum|familienanzeige/.test(t)) return 'familie';
    if (path.includes('/jobs/') || /stellenmarkt|stellenangebot|karriere|arbeitgeber|ausbildung|vollzeit|teilzeit|minijob/.test(t)) return 'jobs';
    if (path.includes('/immobilien/') || /immobilienmarkt|wohnung|haus|grundstueck|grundstück|kaltmiete|warmmiete|wohnfläche|wohnflaeche/.test(t)) return 'immobilien';

    if (path.includes('/blaulicht/') || /\bblaulicht\b/.test(t)) {
      if (/feuerwehr|lösch|loesch|brand|brennt|rauch|drehleiter|tierrettung|technische hilfe|rettungsdienst/.test(t)) return 'feuerwehr';
      if (/polizei|kriminalpolizei|einbruch|fahndung|zeugen|unfallflucht|diebstahl|verkehrskontrolle|kripo/.test(t)) return 'polizei';
      return /brand|feuer|rettung/.test(t) ? 'feuerwehr' : 'polizei';
    }

    if (/polizei|kriminalpolizei|einbruch|fahndung|zeugen|diebstahl|kripo/.test(t)) return 'polizei';
    if (/feuerwehr|lösch|loesch|brand|brennt|rauch|drehleiter|rettungseinsatz/.test(t)) return 'feuerwehr';
    if (path.includes('/sport/') || /sport|fußball|fussball|sc 1919|fc golzheim|spieltag|tabelle|kreisliga/.test(t)) return 'sport';
    if (path.includes('/vereine/') || /verein|schützen|schuetzen|karneval|fanclub|ehrenamt/.test(t)) return 'vereine';
    if (path.includes('/termine/') || /veranstaltung|termin|fest|konzert|markt|wochenende/.test(t)) return 'termine';
    if (path.includes('/wirtschaft/') || /wirtschaft|unternehmen|betrieb|gewerbe|energie|förderung|foerderung/.test(t)) return 'wirtschaft';
    if (/verkehr|sperrung|straße|strasse|baustelle|umleitung|öpnv|oepnv|bahn|bus/.test(t)) return 'verkehr';
    if (path.includes('/leben/') || path.includes('/menschen/') || /kirche|schule|kultur|soziales|menschen/.test(t)) return 'leben';
    if (path.includes('/rathaus/') || /politik|gemeinde|verwaltung|ratssitzung|bürgermeister|buergermeister/.test(t)) return 'gemeinde';
    return 'default';
  }

  function hashString(value) {
    let h = 2166136261;
    const s = String(value || '');
    for (let i = 0; i < s.length; i += 1) {
      h ^= s.charCodeAt(i);
      h = Math.imul(h, 16777619);
    }
    return h >>> 0;
  }

  function seedFuer(root, extra = '') {
    if (!(root instanceof Element)) return `${location.pathname}|${document.title}|${extra}`;
    const story = root.getAttribute('data-story') || root.id || '';
    const link = root.querySelector('h1 a,h2 a,h3 a,a[href]')?.getAttribute('href') || '';
    const heading = text(root.querySelector('h1,h2,h3'));
    const index = Array.prototype.indexOf.call(root.parentElement?.children || [], root);
    return `${location.pathname}|${story}|${link}|${heading}|${index}|${extra}`;
  }

  function poolFuer(key) {
    return BILDER[key] || BILDER.default;
  }

  function waehleBild(key, seed, versatz = 0) {
    const pool = poolFuer(key);
    const index = (hashString(seed) + Math.max(0, versatz)) % pool.length;
    return { bild: pool[index], index };
  }

  function browserSrc(src) {
    if (!src || src.startsWith('/') || src.startsWith('data:')) return src;
    return `/api/bild?u=${encodeURIComponent(src)}`;
  }

  function quelleLink(bild) {
    const a = document.createElement('a');
    a.href = bild.source || bild.src;
    if (!String(a.href).startsWith(location.origin)) {
      a.target = '_blank';
      a.rel = 'noopener noreferrer';
    }
    a.textContent = 'Bildquelle';
    return a;
  }

  function setzeCaption(figure, bild) {
    let caption = figure.querySelector('figcaption');
    if (!caption) {
      caption = document.createElement('figcaption');
      figure.append(caption);
    }
    caption.replaceChildren();

    const beschreibung = document.createElement('span');
    const badge = document.createElement('span');
    badge.className = 'figure-badge';
    badge.textContent = 'Symbolbild';
    beschreibung.append(badge, document.createTextNode(` · ${bild.alt}`));

    const nachweis = document.createElement('span');
    nachweis.append(document.createTextNode(`Bild: ${bild.credit} · `), quelleLink(bild));
    caption.append(beschreibung, nachweis);
  }

  function macheSichtbar(img) {
    const media = img.closest('.media,.markt-thumb,.ma-auto-thumb');
    if (media) media.hidden = false;
    const article = img.closest('article');
    if (article) article.classList.remove('no-image');
  }

  function setzeBild(img, bild, key, seed, attempt = 0) {
    if (!(img instanceof HTMLImageElement)) return;
    img.dataset.maSymbolbild = '1';
    img.dataset.maFallbackKey = key;
    img.dataset.maFallbackSeed = seed;
    img.dataset.maFallbackAttempt = String(attempt);
    img.dataset.editorialImage = '';
    img.removeAttribute('srcset');
    img.removeAttribute('sizes');
    img.src = browserSrc(bild.src);
    img.alt = bild.alt;
    img.decoding = 'async';
    if (!img.loading) img.loading = 'lazy';

    const media = img.closest('.media,.markt-thumb,.ma-auto-thumb');
    const badge = media?.querySelector('.badge,.markt-thumb__badge,.ma-auto-badge');
    if (badge) badge.textContent = 'Symbolbild';

    const figure = img.closest('.art-figure');
    if (figure) setzeCaption(figure, bild);

    img.addEventListener('load', () => macheSichtbar(img), { once: true });
    requestAnimationFrame(() => macheSichtbar(img));
  }

  function bildElement(bild, key, seed, eager = false) {
    const img = document.createElement('img');
    img.loading = eager ? 'eager' : 'lazy';
    img.decoding = 'async';
    if (eager) img.fetchPriority = 'high';
    setzeBild(img, bild, key, seed, 0);
    return img;
  }

  function badgeElement(klasse = 'badge') {
    const badge = document.createElement('span');
    badge.className = klasse;
    badge.textContent = 'Symbolbild';
    return badge;
  }

  function erzeugeArtikelFigure(bild, key, seed) {
    const figure = document.createElement('figure');
    figure.className = 'art-figure ma-symbolbild';
    const media = document.createElement('div');
    media.className = 'media';
    media.append(bildElement(bild, key, seed, true));
    figure.append(media);
    setzeCaption(figure, bild);
    return figure;
  }

  function artikelStartbild() {
    if (document.documentElement.dataset.page !== 'article') return;
    const body = document.querySelector('.article-body');
    if (!body || body.querySelector('.art-figure')) return;
    const key = bildKey(body);
    const seed = seedFuer(body, 'article');
    const { bild } = waehleBild(key, seed);
    const figure = erzeugeArtikelFigure(bild, key, seed);
    const anker = body.querySelector('.facts,.prose,.source-box');
    if (anker) body.insertBefore(figure, anker);
    else body.prepend(figure);
  }

  function ergaenzeFeedBild(article) {
    if (!(article instanceof HTMLElement)) return;
    if (article.querySelector('.feed-img,.media img')) return;
    const link = article.querySelector('h2 a,h3 a');
    if (!link) return;

    const key = bildKey(article);
    const seed = seedFuer(article, 'feed');
    const { bild } = waehleBild(key, seed);
    const a = document.createElement('a');
    a.className = article.classList.contains('feed-lead') ? 'ma-feed-lead-image' : 'feed-img';
    a.href = link.href;
    a.tabIndex = -1;
    a.setAttribute('aria-hidden', 'true');

    const media = document.createElement('div');
    media.className = 'media';
    media.append(bildElement(bild, key, seed, article.classList.contains('feed-lead')));
    media.append(badgeElement());
    a.append(media);
    article.prepend(a);

    const copy = article.querySelector('.feed-copy,.lead-copy');
    if (copy && !copy.querySelector('.creditline')) {
      const credit = document.createElement('div');
      credit.className = 'creditline';
      const c = document.createElement('span');
      c.textContent = bild.credit;
      const q = document.createElement('span');
      q.className = 'src';
      q.append(quelleLink(bild));
      credit.append(c, q);
      copy.append(credit);
    }
  }

  function ergaenzeTextStory(article) {
    if (!(article instanceof HTMLElement) || article.dataset.maAutoImage === '1') return;
    if (article.querySelector('img,.ma-auto-thumb')) return;
    const link = article.querySelector('h2 a,h3 a');
    if (!link) return;

    const key = bildKey(article);
    const seed = seedFuer(article, 'text-story');
    const { bild } = waehleBild(key, seed);

    const copy = document.createElement('div');
    copy.className = 'ma-auto-copy';
    while (article.firstChild) copy.append(article.firstChild);

    const a = document.createElement('a');
    a.className = 'ma-auto-thumb';
    a.href = link.href;
    a.tabIndex = -1;
    a.setAttribute('aria-hidden', 'true');
    a.append(bildElement(bild, key, seed, false), badgeElement('ma-auto-badge'));

    article.append(a, copy);
    article.classList.add('ma-has-auto-image');
    article.dataset.maAutoImage = '1';
  }

  function ergaenzeDeskCard(article) {
    if (!(article instanceof HTMLElement) || article.dataset.maAutoImage === '1') return;
    if (article.querySelector('img,.media')) return;
    const link = article.querySelector('h2 a,h3 a');
    if (!link) return;

    const key = bildKey(article);
    const seed = seedFuer(article, 'desk-card');
    const { bild } = waehleBild(key, seed);

    const a = document.createElement('a');
    a.className = 'ma-auto-card-image';
    a.href = link.href;
    a.tabIndex = -1;
    a.setAttribute('aria-hidden', 'true');
    const media = document.createElement('div');
    media.className = 'media';
    media.append(bildElement(bild, key, seed, false), badgeElement());
    a.append(media);
    article.prepend(a);
    article.dataset.maAutoImage = '1';
  }

  function ergaenzeMarktBild(article) {
    if (!(article instanceof HTMLElement)) return;
    const link = article.querySelector('h3 a');
    if (!link) return;

    const existing = article.querySelector('.markt-thumb img');
    if (existing) return;

    const key = bildKey(article);
    const seed = seedFuer(article, 'markt');
    const { bild } = waehleBild(key, seed);

    const a = document.createElement('a');
    a.className = 'markt-thumb';
    a.href = link.href;
    a.tabIndex = -1;
    a.setAttribute('aria-hidden', 'true');
    if (link.target) a.target = link.target;
    if (link.rel) a.rel = link.rel;
    a.append(bildElement(bild, key, seed, false), badgeElement('markt-thumb__badge'));

    const marker = article.querySelector('.d');
    if (marker?.nextSibling) article.insertBefore(a, marker.nextSibling);
    else if (marker) article.append(a);
    else article.prepend(a);

    article.classList.add('markt-row--thumb');
    article.dataset.maAutoImage = '1';
  }

  function istSymbolbild(img) {
    if (!(img instanceof HTMLImageElement)) return false;
    if (img.dataset.maSymbolbild === '1') return true;
    const box = img.closest('.media,.markt-thumb,.ma-auto-thumb');
    const badge = box?.querySelector('.badge,.markt-thumb__badge,.ma-auto-badge');
    return /symbolbild/i.test(text(badge));
  }

  function variiereSymbolbild(img) {
    if (!(img instanceof HTMLImageElement) || img.dataset.maRotationDone === '1') return;
    if (!istSymbolbild(img)) return;
    const root = img.closest('article,.feed-row,.feed-lead,.markt-row') || document.body;
    const key = bildKey(root);
    const seed = seedFuer(root, 'rotation');
    const { bild } = waehleBild(key, seed);
    img.dataset.maRotationDone = '1';
    setzeBild(img, bild, key, seed, 0);
  }

  function ersetzeDefektesBild(img) {
    if (!(img instanceof HTMLImageElement)) return;
    const root = img.closest('article,.feed-row,.feed-lead,.markt-row') || document.body;
    const key = img.dataset.maFallbackKey || bildKey(root);
    const seed = img.dataset.maFallbackSeed || seedFuer(root, 'broken');
    const pool = poolFuer(key);
    const attempt = Number(img.dataset.maFallbackAttempt || 0) + 1;

    if (attempt <= pool.length + BILDER.default.length) {
      const fallbackKey = attempt <= pool.length ? key : 'default';
      const offset = attempt <= pool.length ? attempt : attempt - pool.length;
      const { bild } = waehleBild(fallbackKey, seed, offset);
      setzeBild(img, bild, fallbackKey, seed, attempt);
      return;
    }

    const { bild } = waehleBild('default', seed, 0);
    img.dataset.maFallbackAttempt = String(attempt);
    img.src = browserSrc(bild.src);
    img.alt = bild.alt;
    requestAnimationFrame(() => macheSichtbar(img));
  }

  function pruefeBereitsDefekteBilder(root = document) {
    root.querySelectorAll?.('img[data-editorial-image],img[data-ma-symbolbild="1"]').forEach((img) => {
      if (img.complete && img.naturalWidth === 0) ersetzeDefektesBild(img);
    });
  }

  function ergaenzeAlleTeaser(root = document) {
    root.querySelectorAll?.('article.feed-row,article.feed-lead').forEach(ergaenzeFeedBild);
    root.querySelectorAll?.('article.text-story').forEach(ergaenzeTextStory);
    root.querySelectorAll?.('article.desk-card').forEach(ergaenzeDeskCard);
    root.querySelectorAll?.('article.markt-row').forEach(ergaenzeMarktBild);

    root.querySelectorAll?.('.markt-thumb img,.media img[data-ma-symbolbild="1"],.ma-auto-thumb img').forEach(variiereSymbolbild);
    root.querySelectorAll?.('.markt-thumb img').forEach((img) => {
      const badge = img.closest('.markt-thumb')?.querySelector('.markt-thumb__badge');
      if (/symbolbild/i.test(text(badge))) variiereSymbolbild(img);
    });
  }

  function styleEinbauen() {
    if (document.querySelector('style[data-ma-fallback-style]')) return;
    const style = document.createElement('style');
    style.dataset.maFallbackStyle = '1';
    style.textContent = `
      article.text-story.ma-has-auto-image{
        display:grid!important;
        grid-template-columns:minmax(112px,160px) minmax(0,1fr);
        gap:16px;
        align-items:start;
      }
      .ma-auto-copy{min-width:0}
      .ma-auto-thumb,.ma-auto-card-image{
        display:block;
        position:relative;
        min-width:0;
        overflow:hidden;
        background:#eee9e3;
        text-decoration:none;
      }
      .ma-auto-thumb{aspect-ratio:16/10}
      .ma-auto-card-image{margin-bottom:12px}
      .ma-auto-card-image .media{aspect-ratio:16/9}
      .ma-auto-thumb img,.ma-auto-card-image img{
        width:100%;
        height:100%;
        display:block;
        object-fit:cover;
      }
      .ma-auto-badge{
        position:absolute;
        left:8px;
        bottom:8px;
        z-index:2;
        padding:3px 6px;
        background:rgba(20,20,20,.78);
        color:#fff;
        font-size:10px;
        line-height:1.1;
        letter-spacing:.04em;
        text-transform:uppercase;
      }
      @media (max-width:640px){
        article.text-story.ma-has-auto-image{
          grid-template-columns:96px minmax(0,1fr);
          gap:12px;
        }
      }
    `;
    document.head.append(style);
  }

  document.addEventListener('error', (event) => {
    const img = event.target;
    if (!(img instanceof HTMLImageElement)) return;
    if (img.matches('[data-editorial-image],[data-ma-symbolbild="1"]') || istSymbolbild(img)) {
      ersetzeDefektesBild(img);
    }
  }, true);

  function start() {
    styleEinbauen();
    artikelStartbild();
    ergaenzeAlleTeaser();
    pruefeBereitsDefekteBilder();

    const observer = new MutationObserver((mutations) => {
      let relevant = false;
      for (const mutation of mutations) {
        if (mutation.addedNodes?.length) {
          relevant = true;
          break;
        }
      }
      if (!relevant) return;
      requestAnimationFrame(() => {
        ergaenzeAlleTeaser();
        pruefeBereitsDefekteBilder();
      });
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();

  window.MerzenichStartbilder = Object.freeze({
    checkedAt: '2026-09-17',
    pools: Object.freeze(Object.fromEntries(Object.entries(BILDER).map(([key, value]) => [key, value.length]))),
    bilder: BILDER
  });
})();
