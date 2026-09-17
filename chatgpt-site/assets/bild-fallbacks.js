/*
 * Merzenich Aktuell — globale Startbild-Fallbacks fuer den ausgelieferten Stand.
 *
 * Ein vorhandenes redaktionelles Bild bleibt immer unangetastet. Fehlt auf
 * einer Artikelseite ein Startbild (oder kann ein vorhandenes Bild technisch
 * nicht geladen werden), erscheint ein thematisch passendes, lizenzgeprueftes
 * Symbolbild. Rechte-Check: 16.09.2026.
 */
(() => {
  'use strict';

  const BILDER = {
    polizei: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/Neue%20Streifenwagen%20f%C3%BCr%20die%20Polizei%20vorgestellt.jpg?width=1600',
      alt: 'Streifenwagen der Polizei Nordrhein-Westfalen',
      credit: 'Symbolbild · IM NRW / Wikimedia Commons · CC0 1.0',
      source: 'https://commons.wikimedia.org/wiki/File:Neue_Streifenwagen_f%C3%BCr_die_Polizei_vorgestellt.jpg'
    },
    feuerwehr: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/Feuerwehrm%C3%A4nner%20im%20Einsatz.jpg?width=1600',
      alt: 'Feuerwehrkraefte bei einem Loescheinsatz',
      credit: 'Symbolbild · AK-Bino / Wikimedia Commons · CC BY-SA 4.0',
      source: 'https://commons.wikimedia.org/wiki/File:Feuerwehrm%C3%A4nner_im_Einsatz.jpg'
    },
    gemeinde: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/Merzenich%20Rathaus%20HDR.jpg?width=1600',
      alt: 'Rathaus der Gemeinde Merzenich',
      credit: 'Symbolbild · Karl-Heinz Meurer / Wikimedia Commons · CC BY-SA 3.0',
      source: 'https://commons.wikimedia.org/wiki/File:Merzenich_Rathaus_HDR.jpg'
    },
    leben: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/Merzenich%20Alte%20Pfarrkirche.jpg?width=1600',
      alt: 'Alte Pfarrkirche in Merzenich',
      credit: 'Symbolbild · Karl-Heinz Meurer / Wikimedia Commons · CC BY-SA 3.0',
      source: 'https://commons.wikimedia.org/wiki/File:Merzenich_Alte_Pfarrkirche.jpg'
    },
    termine: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/Merzenich%20Denkmal-Nr.%2018%2C%20Lindenplatz%20%281235%29.jpg?width=1600',
      alt: 'Lindenplatz in Merzenich',
      credit: 'Symbolbild · Kaethe und Bernd Limburg / Wikimedia Commons · CC BY-SA 3.0 DE',
      source: 'https://commons.wikimedia.org/wiki/File:Merzenich_Denkmal-Nr._18,_Lindenplatz_(1235).jpg'
    },
    sport: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/2026-08-30%20Fu%C3%9Fballplatz%20Trogen%20HOF8480%20RAW-Export.png?width=1600',
      alt: 'Fussballplatz als Symbolbild fuer Lokalsport',
      credit: 'Symbolbild · PantheraLeo1359531 / Wikimedia Commons · CC BY-SA 4.0',
      source: 'https://commons.wikimedia.org/wiki/File:2026-08-30_Fu%C3%9Fballplatz_Trogen_HOF8480_RAW-Export.png'
    },
    jobs: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/LOOM%20office%20workspaces.jpg?width=1600',
      alt: 'Arbeitsplaetze in einem Buero als Symbolbild fuer Stellenangebote',
      credit: 'Symbolbild · Loominade / Wikimedia Commons · CC0 1.0',
      source: 'https://commons.wikimedia.org/wiki/File:LOOM_office_workspaces.jpg'
    },
    immobilien: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/Merzenich%20Denkmal-Nr.%2020%2C%20Lindenplatz%205%20%281238%29.jpg?width=1600',
      alt: 'Wohnhaus in Merzenich als Symbolbild fuer Immobilienangebote',
      credit: 'Symbolbild · Kaethe und Bernd Limburg / Wikimedia Commons · CC BY-SA 3.0 DE',
      source: 'https://commons.wikimedia.org/wiki/File:Merzenich_Denkmal-Nr._20,_Lindenplatz_5_(1238).jpg'
    }
  };

  const text = (el) => (el?.textContent || '').trim();

  function kontextText(root = document) {
    const section = document.querySelector('meta[property="article:section"]')?.content || '';
    const keywords = document.querySelector('meta[name="news_keywords"]')?.content || '';
    const title = text(root.querySelector?.('h1,h2,h3')) || document.title;
    const kicker = text(root.querySelector?.('.kicker'));
    const body = text(root.querySelector?.('.dek,.prose'));
    return `${section} ${keywords} ${title} ${kicker} ${body} ${location.pathname}`.toLocaleLowerCase('de-DE');
  }

  function bildKey(root = document) {
    const t = kontextText(root);
    const path = location.pathname.toLocaleLowerCase('de-DE');

    // Einsatzseiten zuerst klassifizieren. So kann z. B. das Wort "Gemeinde"
    // in einem Polizeibericht niemals ein Rathausmotiv vor Blaulicht ziehen.
    if (path.includes('/blaulicht/') || /\bblaulicht\b/.test(t)) {
      if (/feuerwehr|loesch|lösch|brand|brennt|rauch|drehleiter|tierrettung|person hinter t[uü]r|technische hilfe|einsatznummer/.test(t)) return 'feuerwehr';
      if (/polizei|kriminalpolizei|einbruch|fahndung|zeugen|unfallflucht|diebstahl|verkehrskontrolle/.test(t)) return 'polizei';
      return path.includes('/blaulicht/einsatz-') ? 'feuerwehr' : 'polizei';
    }

    if (path.includes('/jobs/') || /stellenmarkt|stellenangebot|karriere|arbeitgeber/.test(t)) return 'jobs';
    if (path.includes('/immobilien/') || /immobilienmarkt|wohnung|haus|grundstueck|grundstück/.test(t)) return 'immobilien';
    if (path.includes('/sport/') || /\bsport\b|fussball|fußball|sc 1919/.test(t)) return 'sport';
    if (path.includes('/termine/') || path.includes('/vereine/') || /veranstaltung|termin|vereinsleben/.test(t)) return 'termine';
    if (path.includes('/leben/') || path.includes('/menschen/') || /kirche|schule|kultur|soziales/.test(t)) return 'leben';
    if (path.includes('/rathaus/') || /politik|gemeinde|verwaltung|ratssitzung/.test(t)) return 'gemeinde';
    return 'gemeinde';
  }

  function quelleLink(bild) {
    const a = document.createElement('a');
    a.href = bild.source;
    a.target = '_blank';
    a.rel = 'noopener noreferrer';
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

  function bildElement(bild, eager = false) {
    const img = document.createElement('img');
    // Externe Adresse nur mit Einwilligung (einwilligung.js), sonst Platzhalter.
    if (window.maExtern) window.maExtern.setze(img, bild.src); else img.src = bild.src;
    img.alt = bild.alt;
    img.loading = eager ? 'eager' : 'lazy';
    img.decoding = 'async';
    img.dataset.editorialImage = '';
    img.dataset.maSymbolbild = '1';
    if (eager) img.fetchPriority = 'high';
    return img;
  }

  function erzeugeArtikelFigure(bild) {
    const figure = document.createElement('figure');
    figure.className = 'art-figure ma-symbolbild';
    const media = document.createElement('div');
    media.className = 'media';
    media.append(bildElement(bild, true));
    figure.append(media);
    setzeCaption(figure, bild);
    return figure;
  }

  function artikelStartbild() {
    if (document.documentElement.dataset.page !== 'article') return;
    const body = document.querySelector('.article-body');
    if (!body || body.querySelector('.art-figure')) return;
    const bild = BILDER[bildKey(body)] || BILDER.gemeinde;
    const figure = erzeugeArtikelFigure(bild);
    const anker = body.querySelector('.facts,.prose,.source-box');
    if (anker) body.insertBefore(figure, anker);
    else body.prepend(figure);
  }

  function ergaenzeFeedBild(article) {
    if (!(article instanceof HTMLElement)) return;
    if (article.querySelector('.feed-img,.media img')) return;
    const link = article.querySelector('h2 a,h3 a');
    if (!link) return;
    const bild = BILDER[bildKey(article)] || BILDER.gemeinde;
    const a = document.createElement('a');
    a.className = article.classList.contains('feed-lead') ? '' : 'feed-img';
    a.href = link.href;
    a.tabIndex = -1;
    a.setAttribute('aria-hidden', 'true');
    const media = document.createElement('div');
    media.className = 'media';
    media.append(bildElement(bild, article.classList.contains('feed-lead')));
    const badge = document.createElement('span');
    badge.className = 'badge';
    badge.textContent = 'Symbolbild';
    media.append(badge);
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

  function feedStartbilder() {
    document.querySelectorAll('article.feed-row,article.feed-lead').forEach(ergaenzeFeedBild);
  }

  function ersetzeDefektesBild(img) {
    if (!(img instanceof HTMLImageElement) || img.dataset.maSymbolbild === '1' || img.dataset.maFallbackTried === '1') return;
    const root = img.closest('article,.feed-row,.feed-lead') || document;
    const bild = BILDER[bildKey(root)] || BILDER.gemeinde;
    img.dataset.maFallbackTried = '1';
    img.dataset.maSymbolbild = '1';
    img.removeAttribute('srcset');
    img.removeAttribute('sizes');
    if (window.maExtern) window.maExtern.setze(img, bild.src); else img.src = bild.src;
    img.alt = bild.alt;
    const media = img.closest('.media');
    const badge = media?.querySelector('.badge');
    if (badge) badge.textContent = 'Symbolbild';
    const figure = img.closest('.art-figure');
    if (figure) setzeCaption(figure, bild);
  }

  function pruefeBereitsDefekteBilder() {
    document.querySelectorAll('img[data-editorial-image]').forEach((img) => {
      if (img.complete && img.naturalWidth === 0) ersetzeDefektesBild(img);
    });
  }

  document.addEventListener('error', (event) => {
    if (event.target instanceof HTMLImageElement && event.target.matches('[data-editorial-image]')) {
      ersetzeDefektesBild(event.target);
    }
  }, true);

  function start() {
    artikelStartbild();
    feedStartbilder();
    pruefeBereitsDefekteBilder();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();

  window.MerzenichStartbilder = Object.freeze({ checkedAt: '2026-09-16', bilder: BILDER });
})();
