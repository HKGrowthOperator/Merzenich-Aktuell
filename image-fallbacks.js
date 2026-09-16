/*
 * Merzenich Aktuell — zentrale Bild-Fallbacks
 *
 * Regel: Ein verifiziertes Bild zur konkreten Meldung hat immer Vorrang.
 * Fehlt ein solches Bild, wird ein rechtlich geprüftes Symbolbild eingesetzt.
 * Die Symbolbilder werden sichtbar als solche gekennzeichnet.
 */

(() => {
  const rightsCheckedAt = '2026-09-16T20:17:00+02:00';

  const FALLBACK_IMAGES = {
    police: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/Neue%20Streifenwagen%20f%C3%BCr%20die%20Polizei%20vorgestellt.jpg?width=1600',
      alt: 'Streifenwagen der Polizei Nordrhein-Westfalen',
      credit: 'Symbolbild · IM NRW / Wikimedia Commons · CC0 1.0',
      sourceUrl: 'https://commons.wikimedia.org/wiki/File:Neue_Streifenwagen_f%C3%BCr_die_Polizei_vorgestellt.jpg',
      license: 'CC0 1.0'
    },
    fire: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/Feuerwehrm%C3%A4nner%20im%20Einsatz.jpg?width=1600',
      alt: 'Feuerwehrkräfte bei einem Löscheinsatz',
      credit: 'Symbolbild · AK-Bino / Wikimedia Commons · CC BY-SA 4.0',
      sourceUrl: 'https://commons.wikimedia.org/wiki/File:Feuerwehrm%C3%A4nner_im_Einsatz.jpg',
      license: 'CC BY-SA 4.0'
    },
    municipality: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/Merzenich%20Rathaus%20HDR.jpg?width=1600',
      alt: 'Rathaus der Gemeinde Merzenich',
      credit: 'Symbolbild · Karl-Heinz Meurer / Wikimedia Commons · CC BY-SA 3.0',
      sourceUrl: 'https://commons.wikimedia.org/wiki/File:Merzenich_Rathaus_HDR.jpg',
      license: 'CC BY-SA 3.0'
    },
    life: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/Merzenich%20Alte%20Pfarrkirche.jpg?width=1600',
      alt: 'Alte Pfarrkirche in Merzenich',
      credit: 'Symbolbild · Karl-Heinz Meurer / Wikimedia Commons · CC BY-SA 3.0',
      sourceUrl: 'https://commons.wikimedia.org/wiki/File:Merzenich_Alte_Pfarrkirche.jpg',
      license: 'CC BY-SA 3.0'
    },
    events: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/Merzenich%20Denkmal-Nr.%2018%2C%20Lindenplatz%20%281235%29.jpg?width=1600',
      alt: 'Lindenplatz in Merzenich',
      credit: 'Symbolbild · Käthe und Bernd Limburg / Wikimedia Commons · CC BY-SA 3.0 DE',
      sourceUrl: 'https://commons.wikimedia.org/wiki/File:Merzenich_Denkmal-Nr._18,_Lindenplatz_(1235).jpg',
      license: 'CC BY-SA 3.0 DE'
    },
    sport: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/2026-08-30%20Fu%C3%9Fballplatz%20Trogen%20HOF8480%20RAW-Export.png?width=1600',
      alt: 'Fußballplatz als Symbolbild für Lokalsport',
      credit: 'Symbolbild · PantheraLeo1359531 / Wikimedia Commons · CC BY-SA 4.0',
      sourceUrl: 'https://commons.wikimedia.org/wiki/File:2026-08-30_Fu%C3%9Fballplatz_Trogen_HOF8480_RAW-Export.png',
      license: 'CC BY-SA 4.0'
    },
    jobs: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/LOOM%20office%20workspaces.jpg?width=1600',
      alt: 'Arbeitsplätze in einem Büro als Symbolbild für Stellenangebote',
      credit: 'Symbolbild · Loominade / Wikimedia Commons · CC0 1.0',
      sourceUrl: 'https://commons.wikimedia.org/wiki/File:LOOM_office_workspaces.jpg',
      license: 'CC0 1.0'
    },
    realEstate: {
      src: 'https://commons.wikimedia.org/wiki/Special:FilePath/Merzenich%20Denkmal-Nr.%2020%2C%20Lindenplatz%205%20%281238%29.jpg?width=1600',
      alt: 'Wohnhaus in Merzenich als Symbolbild für Immobilienangebote',
      credit: 'Symbolbild · Käthe und Bernd Limburg / Wikimedia Commons · CC BY-SA 3.0 DE',
      sourceUrl: 'https://commons.wikimedia.org/wiki/File:Merzenich_Denkmal-Nr._20,_Lindenplatz_5_(1238).jpg',
      license: 'CC BY-SA 3.0 DE'
    }
  };

  const knownGenericLocalImages = [
    /Merzenich(?:%20|\s)Rathaus(?:%20|\s)HDR\.jpg/i,
    /Merzenich_Rathaus_HDR\.jpg/i
  ];

  function fallbackKeyForArticle(article = {}) {
    const category = String(article.category || '').toLocaleLowerCase('de-DE');
    const text = [article.category, article.headline, article.teaser, ...(article.body || [])]
      .filter(Boolean)
      .join(' ')
      .toLocaleLowerCase('de-DE');

    if (category === 'blaulicht') {
      if (/feuerwehr|brand|brennt|rauch|lösch|drehleiter|person hinter tür|rettungsdienst|technische hilfe/.test(text)) return 'fire';
      return 'police';
    }
    if (category === 'sport') return 'sport';
    if (category === 'termine' || category === 'vereine') return 'events';
    if (category === 'leben' || category === 'menschen') return 'life';
    if (category === 'wirtschaft') return 'jobs';
    if (category === 'gemeinde' || category === 'rathaus & politik' || category === 'rathaus') return 'municipality';
    if (category === 'stellen') return 'jobs';
    if (category === 'immobilien') return 'realEstate';
    return 'municipality';
  }

  function hasKnownGenericPlaceholder(article = {}) {
    const src = String(article.image || '');
    return String(article.category || '').toLocaleLowerCase('de-DE') === 'blaulicht'
      && knownGenericLocalImages.some(pattern => pattern.test(src));
  }

  function shouldUseFallback(article = {}) {
    return !String(article.image || '').trim() || hasKnownGenericPlaceholder(article);
  }

  function ensureArticleImage(article) {
    if (!article || !shouldUseFallback(article)) return article;
    const fallback = FALLBACK_IMAGES[fallbackKeyForArticle(article)] || FALLBACK_IMAGES.municipality;
    article.image = fallback.src;
    article.imageAlt = fallback.alt;
    article.imageCredit = fallback.credit;
    article.imageType = 'symbol';
    article.imageSourceUrl = fallback.sourceUrl;
    article.imageLicense = fallback.license;
    article.imageRightsCheckedAt = rightsCheckedAt;
    return article;
  }

  const originalRenderHero = renderHero;
  renderHero = function renderHeroWithFallback() {
    ensureArticleImage(pickHero());
    return originalRenderHero();
  };

  const originalBuildCard = buildCard;
  buildCard = function buildCardWithFallback(article) {
    ensureArticleImage(article);
    return originalBuildCard(article);
  };

  const originalRenderArticle = renderArticle;
  renderArticle = function renderArticleWithFallback(id) {
    const article = state.content?.articles?.find(item => item.id === id && item.status === 'published');
    ensureArticleImage(article);
    const result = originalRenderArticle(id);
    if (article?.imageType === 'symbol' && article.imageSourceUrl) {
      const credit = $('#articleView .article-hero .image-credit');
      if (credit) {
        credit.innerHTML = `${escapeHtml(article.imageCredit)} · <a href="${escapeHtml(safeUrl(article.imageSourceUrl))}" target="_blank" rel="noopener noreferrer">Bildquelle</a>`;
      }
    }
    return result;
  };

  if (typeof renderMarketCard === 'function') {
    const originalRenderMarketCard = renderMarketCard;
    renderMarketCard = function renderMarketCardWithFallback(item, kind) {
      const fallback = kind === 'jobs' ? FALLBACK_IMAGES.jobs : FALLBACK_IMAGES.realEstate;
      const verifiedImage = item?.image && item?.imageRightsVerified === true
        ? {
            src: item.image,
            alt: item.imageAlt || item.title || '',
            credit: item.imageCredit || ''
          }
        : fallback;
      const media = `
        <figure class="market-card-media">
          <img src="${escapeHtml(verifiedImage.src)}" alt="${escapeHtml(verifiedImage.alt)}" loading="lazy">
          <figcaption>${escapeHtml(verifiedImage.credit)}${verifiedImage.sourceUrl ? ` · <a href="${escapeHtml(safeUrl(verifiedImage.sourceUrl))}" target="_blank" rel="noopener noreferrer">Bildquelle</a>` : ''}</figcaption>
        </figure>`;
      return originalRenderMarketCard(item, kind).replace('<article class="market-card">', `<article class="market-card">${media}`);
    };
  }

  function injectFallbackImageStyles() {
    if ($('#fallbackImageStyles')) return;
    const style = document.createElement('style');
    style.id = 'fallbackImageStyles';
    style.textContent = `
      .market-card-media{position:relative;margin:0 0 14px;overflow:hidden;background:#eceae6;aspect-ratio:16/9}
      .market-card-media img{width:100%;height:100%;object-fit:cover;display:block}
      .market-card-media figcaption{position:absolute;left:8px;bottom:7px;max-width:calc(100% - 16px);padding:3px 6px;background:rgba(0,0,0,.72);color:#fff;font-size:9px;line-height:1.25}
      .market-card-media figcaption a,.article-hero .image-credit a{color:inherit;text-decoration:underline;text-underline-offset:2px}
      [data-theme="dark"] .market-card-media{background:#1f1f1f}
    `;
    document.head.append(style);
  }

  // Falls ein externes Bild trotz verifizierter Quelle technisch nicht lädt,
  // bleibt die Seite nicht bildlos. Einmalig auf das Gemeinde-Symbolbild
  // zurückfallen; danach keine Endlosschleife erzeugen.
  document.addEventListener('error', event => {
    const image = event.target;
    if (!(image instanceof HTMLImageElement) || image.dataset.maFallbackTried === '1') return;
    image.dataset.maFallbackTried = '1';
    image.src = FALLBACK_IMAGES.municipality.src;
  }, true);

  document.addEventListener('DOMContentLoaded', injectFallbackImageStyles);

  window.MerzenichImageFallbacks = Object.freeze({
    rightsCheckedAt,
    images: FALLBACK_IMAGES,
    resolveArticle: fallbackKeyForArticle
  });
})();
