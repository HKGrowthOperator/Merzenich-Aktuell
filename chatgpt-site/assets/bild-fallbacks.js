/*
 * Merzenich Aktuell — Rückfall für Bilder, die im Browser nicht laden.
 *
 * Harte Regel: Auf einer fehlerfrei geladenen Seite verändert dieses Skript kein
 * einziges img-Element. Was ausgeliefert wurde, bleibt genau so stehen.
 *
 * 1. Ein vorhandenes, ladbares Bild wird niemals ersetzt — keine Rotation, keine
 *    Variation, kein Austausch von Motiven.
 * 2. Bilder werden nicht nachträglich in Teaser, Karten oder Artikel eingefügt.
 *    Welches Bild zu einer Meldung gehört, entscheiden die Generatoren unter deploy/,
 *    nicht der Browser. Vorher hat dieses Skript hier Motive nachgeschoben und dabei
 *    unter eine Sportmeldung das Vereinsfoto einer anderen Meldung gesetzt.
 * 3. Es bleibt genau eine Aufgabe: Lädt ein Bild nachweislich nicht (Fehlerereignis
 *    oder complete mit naturalWidth 0), tritt ein Motiv aus dem passenden Pool an
 *    seine Stelle — deterministisch pro Meldung, sichtbar als "Symbolbild".
 * 4. Für polizei, feuerwehr und verkehr gilt weiterhin: kein Ortsmotiv als Rückfall,
 *    dann lieber bildlos. Ein Dorffoto unter "Einbruch" behauptet still einen Tatort.
 * 5. Externe Commons-Dateien laufen über /api/bild; Quellenlinks bleiben sichtbar.
 * Rechte-Check für die Commons-Motive: 17.09.2026.
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

  // Der Seed haengt nur an der Meldung (data-story oder Ziel-Link), nicht an Seite,
  // Position oder Kontext: dieselbe Meldung zeigt ueberall dasselbe Symbolbild.
  function seedFuer(root, extra = '') {
    if (!(root instanceof Element)) return `${location.pathname}|${document.title}`;
    const story = root.getAttribute('data-story') || root.id || '';
    if (story) return `story|${story}`;
    const link = root.querySelector('h1 a,h2 a,h3 a,a[href]')?.getAttribute('href') || '';
    if (link) return `link|${link}`;
    const heading = text(root.querySelector('h1,h2,h3'));
    return `${location.pathname}|${heading}|${extra}`;
  }

  // Ortsmotive (Pool default) duerfen nie unter Blaulicht- oder Verkehrsmeldungen stehen:
  // ein Dorffoto unter "Einbruch" behauptet still einen Tatort (Design Standard, Bildregel 3).
  const OHNE_ORTSMOTIV = new Set(['polizei', 'feuerwehr', 'verkehr']);
  const ORTS_INDEX = [['golzheim', 1], ['girbelsrath', 2], ['morschenich', 3], ['bürgewald', 4], ['buergewald', 4], ['merzenich', 0]];
  function ortIndexFuer(root) {
    const t = `${text(root?.querySelector?.('.location-line'))} ${root instanceof HTMLElement ? root.className : ''} ${location.pathname}`.toLocaleLowerCase('de-DE');
    for (const [name, i] of ORTS_INDEX) if (t.includes(name)) return i;
    return 0;
  }
  function poolFuer(key) {
    return BILDER[key] || BILDER.default;
  }

  function waehleBild(key, seed, versatz = 0, root = null) {
    const pool = poolFuer(key);
    if (!BILDER[key] || key === 'default') {
      // Ortsmotiv: immer das Foto des Orts der Meldung (Merzenich, wenn unbekannt), keine Rotation.
      const index = ortIndexFuer(root) % pool.length;
      return { bild: pool[index], index };
    }
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
    const media = img.closest('.media,.markt-thumb');
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

    const media = img.closest('.media,.markt-thumb');
    const badge = media?.querySelector('.badge,.markt-thumb__badge');
    if (badge) badge.textContent = 'Symbolbild';

    const figure = img.closest('.art-figure');
    if (figure) setzeCaption(figure, bild);

    img.addEventListener('load', () => macheSichtbar(img), { once: true });
    requestAnimationFrame(() => macheSichtbar(img));
  }

  function istSymbolbild(img) {
    if (!(img instanceof HTMLImageElement)) return false;
    if (img.dataset.maSymbolbild === '1') return true;
    const box = img.closest('.media,.markt-thumb');
    const badge = box?.querySelector('.badge,.markt-thumb__badge');
    return /symbolbild/i.test(text(badge));
  }

  // Der einzige Eingriff dieses Skripts: ein Bild, das der Browser nicht laden konnte.
  // Versuch 0 nimmt das Motiv, das deterministisch zur Meldung gehört; jeder weitere
  // Versuch rückt eine Stelle im Pool weiter, bis der Pool durch ist. Danach bildlos.
  function ersetzeDefektesBild(img) {
    if (!(img instanceof HTMLImageElement)) return;
    const root = img.closest('article,.feed-row,.feed-lead,.markt-row') || document.body;
    const key = img.dataset.maFallbackKey || bildKey(root);
    const seed = img.dataset.maFallbackSeed || seedFuer(root, 'defekt');
    const pool = poolFuer(key);
    // Fehlt der Zähler oder ist er beschädigt, beginnt die Kette sauber bei 0.
    // Ohne diese Prüfung liefe ein NaN endlos durch dieselbe Ersetzung.
    const gezaehlt = Number(img.dataset.maFallbackAttempt);
    const versuch = Number.isFinite(gezaehlt) ? gezaehlt + 1 : 0;

    if (versuch < pool.length) {
      const { bild } = waehleBild(key, seed, versuch, root);
      setzeBild(img, bild, key, seed, versuch);
      return;
    }

    img.dataset.maFallbackAttempt = String(versuch);
    if (OHNE_ORTSMOTIV.has(key) || key === 'default' || versuch > pool.length) {
      // Kein Motiv des Pools erreichbar: lieber bildlos als ein Dorffoto unter einer Einsatzmeldung.
      const box = img.closest('.media,.markt-thumb,.feed-img,.art-figure');
      if (box) box.hidden = true;
      const article = img.closest('article');
      if (article) article.classList.add('no-image');
      return;
    }

    const { bild } = waehleBild('default', seed, 0, root);
    setzeBild(img, bild, 'default', seed, versuch);
  }

  function pruefeBereitsDefekteBilder(root = document) {
    root.querySelectorAll?.('img[data-editorial-image],img[data-ma-symbolbild="1"]').forEach((img) => {
      if (img.complete && img.naturalWidth === 0) ersetzeDefektesBild(img);
    });
  }

  document.addEventListener('error', (event) => {
    const img = event.target;
    if (!(img instanceof HTMLImageElement)) return;
    if (img.matches('[data-editorial-image],[data-ma-symbolbild="1"]') || istSymbolbild(img)) {
      ersetzeDefektesBild(img);
    }
  }, true);

  function start() {
    // Nachzügler: Bilder, die schon vor dem Laden dieses Skripts gescheitert sind,
    // feuern kein error-Ereignis mehr und fallen nur über diese Prüfung auf.
    pruefeBereitsDefekteBilder();

    // Andere Skripte schreiben Listen per innerHTML neu. Der Beobachter prüft die
    // nachgereichten Bilder auf denselben Defekt — mehr nicht. Laden sie, bleibt alles,
    // wie es ausgeliefert wurde.
    const observer = new MutationObserver((mutations) => {
      let relevant = false;
      for (const mutation of mutations) {
        if (mutation.addedNodes?.length) {
          relevant = true;
          break;
        }
      }
      if (!relevant) return;
      requestAnimationFrame(() => pruefeBereitsDefekteBilder());
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();

  // Bleibt unverändert: homepage-polish.js liest die Pools über diesen Zugang.
  window.MerzenichStartbilder = Object.freeze({
    checkedAt: '2026-09-17',
    pools: Object.freeze(Object.fromEntries(Object.entries(BILDER).map(([key, value]) => [key, value.length]))),
    bilder: BILDER
  });
})();
