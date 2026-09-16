/*
 * Small compatibility layer for the static GitHub preview.
 * Keep production logic in WordPress; this file hardens preview rendering
 * and adds the current, source-linked local market modules.
 */

function locationMarkup(article) {
  const location = String(article.location || 'MERZENICH').trim();
  const normalized = location.toLocaleUpperCase('de-DE');

  if (normalized === 'MERZENICH') {
    const district = article.district
      ? ` <span class="district">· ${escapeHtml(article.district)}</span>`
      : '';
    return `<span class="main-location">MERZENICH</span>${district}`;
  }

  return `<span class="district outside-location">${escapeHtml(location)}</span>`;
}

function renderSport(root = $('#sportPreview'), compact = false) {
  const sport = state.content.sport || {};
  const checked = sport.dataCheckedAt ? new Date(sport.dataCheckedAt) : null;
  const boxes = [];

  if (sport.lastConfirmed) {
    const match = sport.lastConfirmed;
    boxes.push(`
      <div class="sport-box">
        <span class="sport-label">Letztes Spiel</span>
        <strong>${escapeHtml(match.home || '')} <b>${escapeHtml(match.result || '')}</b> ${escapeHtml(match.away || '')}</strong>
        ${match.date ? `<span>${formatDate(match.date)} · ${formatTime(match.date)} Uhr</span>` : ''}
      </div>`);
  }

  if (sport.pending) {
    const match = sport.pending;
    boxes.push(`
      <div class="sport-box">
        <span class="sport-label">Ergebnis noch offen</span>
        <strong>${escapeHtml(match.home || '')} – ${escapeHtml(match.away || '')}</strong>
        <span class="pending-status">${escapeHtml(match.status || 'Noch nicht bestätigt')}</span>
        ${match.date ? `<span>${formatDate(match.date)} · ${formatTime(match.date)} Uhr</span>` : ''}
      </div>`);
  }

  if (sport.next) {
    const match = sport.next;
    boxes.push(`
      <div class="sport-box">
        <span class="sport-label">Nächstes Spiel</span>
        <strong>${escapeHtml(match.home || '')} – ${escapeHtml(match.away || '')}</strong>
        ${match.date ? `<span>${formatDate(match.date)} · ${formatTime(match.date)} Uhr</span>` : ''}
      </div>`);
  }

  if (sport.table) {
    boxes.push(`
      <div class="sport-box">
        <span class="sport-label">Tabelle</span>
        <strong>Platz ${escapeHtml(sport.table.position ?? '–')} · ${escapeHtml(sport.table.points ?? '–')} Punkte</strong>
        <span>Torverhältnis ${escapeHtml(sport.table.goals ?? '–')}</span>
      </div>`);
  }

  if (!boxes.length) {
    root.innerHTML = '<div class="notice-box">Noch kein freigegebener Sport-Datenstand.</div>';
    return;
  }

  const dataState = checked && !Number.isNaN(checked.getTime())
    ? `Datenstand: ${formatDate(checked)}, ${formatTime(checked)} Uhr${sport.sourceName ? ` · ${escapeHtml(sport.sourceName)}` : ''}`
    : (sport.sourceName ? escapeHtml(sport.sourceName) : 'Redaktionell gepflegter Datenstand');

  root.innerHTML = `
    <div class="sport-preview-head">
      <div><p class="eyebrow">Sport</p><h2 id="sportPreviewTitle">${escapeHtml(sport.team || 'Lokalsport')}</h2></div>
      <div class="sport-data-stand">${dataState}</div>
    </div>
    <div class="sport-grid">${boxes.join('')}</div>
    ${compact || !sport.sourceUrl ? '' : `<p style="margin:12px 0 0;font-size:12px;color:#686868">Quelle: <a class="text-link" href="${escapeHtml(sport.sourceUrl)}" target="_blank" rel="noopener noreferrer">${escapeHtml(sport.sourceName || 'Quelle')}</a></p>`}`;
}

/* Markt: Immobilien + Stellen */
const marketState = {
  data: null,
  error: null
};

const marketPlaceOrder = ['Merzenich', 'Düren', 'Niederzier', 'Nörvenich', 'Elsdorf', 'Kerpen'];
const originalRenderArchive = renderArchive;

function marketLocationMarkup(item) {
  const municipality = String(item.municipality || '').trim();
  const district = String(item.district || '').trim();

  if (municipality === 'Merzenich') {
    const districtSuffix = district && district.toLocaleLowerCase('de-DE') !== 'merzenich'
      ? ` · ${escapeHtml(district.toLocaleUpperCase('de-DE'))}`
      : '';
    return `<span class="market-place market-place-primary">MERZENICH${districtSuffix}</span>`;
  }

  const districtSuffix = district && district.toLocaleLowerCase('de-DE') !== municipality.toLocaleLowerCase('de-DE')
    ? ` · ${escapeHtml(district.toLocaleUpperCase('de-DE'))}`
    : '';
  return `<span class="market-place market-place-neighbor">UMKREIS · ${escapeHtml(municipality.toLocaleUpperCase('de-DE'))}${districtSuffix}</span>`;
}

function marketCheckedMarkup(value) {
  if (!value) return '';
  const date = formatDate(value);
  const time = formatTime(value);
  if (!date) return '';
  return `Geprüft ${date}${time ? ` · ${time} Uhr` : ''}`;
}

function renderMarketCard(item, kind) {
  const isJob = kind === 'jobs';
  const factLine = isJob ? item.employment : item.details;
  const secondary = isJob ? item.details : item.offerType;
  const highlight = isJob ? item.employer : item.price;

  return `
    <article class="market-card">
      <div class="market-location-line">${marketLocationMarkup(item)}</div>
      <div class="market-card-meta">
        <span>${escapeHtml(secondary || (isJob ? 'Stellenangebot' : 'Immobilie'))}</span>
        <span>${escapeHtml(marketCheckedMarkup(item.checkedAt))}</span>
      </div>
      <h3>${escapeHtml(item.title || '')}</h3>
      ${highlight ? `<p class="market-highlight">${escapeHtml(highlight)}</p>` : ''}
      ${factLine ? `<p class="market-facts">${escapeHtml(factLine)}</p>` : ''}
      <div class="market-source-row">
        <span>Quelle: ${escapeHtml(item.sourceName || 'Originalquelle')}</span>
        <a class="more-button" href="${escapeHtml(safeUrl(item.sourceUrl))}" target="_blank" rel="noopener noreferrer nofollow">Originalanzeige öffnen</a>
      </div>
    </article>`;
}

function renderMarketSources(place, kind) {
  const key = kind === 'jobs' ? 'jobSources' : 'propertySources';
  const sources = (marketState.data?.[key] || []).filter(source => source.municipality === place);
  if (!sources.length) return '';

  return `
    <div class="market-live-sources">
      <strong>Laufende Gesamtübersicht</strong>
      <div>${sources.map(source => `
        <a href="${escapeHtml(safeUrl(source.sourceUrl))}" target="_blank" rel="noopener noreferrer nofollow">
          ${escapeHtml(source.label)} <span>· ${escapeHtml(source.sourceName)}</span>
        </a>`).join('')}</div>
    </div>`;
}

function marketPlaceDescription(place) {
  if (place === 'Merzenich') {
    return 'Gemeinde Merzenich zuerst · einschließlich Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald.';
  }
  return `${place} gehört hier zum direkten Umkreis. Diese Angebote befinden sich ausdrücklich nicht in Merzenich.`;
}

function renderMarketArchive(kind) {
  const root = $('#archiveGrid');
  const isJob = kind === 'jobs';
  root.classList.add('market-archive-grid');
  $('#archiveEyebrow').textContent = 'Merzenich Aktuell · Markt';
  $('#archiveTitle').textContent = isJob ? 'Stellenmarkt' : 'Immobilienmarkt';
  $('#archiveIntro').textContent = isJob
    ? 'Aktuelle Stellenangebote: Merzenich steht immer zuerst. Danach folgen ausschließlich klar gekennzeichnete Angebote aus den direkt angrenzenden Städten und Gemeinden.'
    : 'Aktuelle Immobilienangebote: Merzenich steht immer zuerst. Danach folgen ausschließlich klar gekennzeichnete Angebote aus dem direkten Umkreis. Fremde Immobilienfotos werden ohne geklärte Nutzungsrechte nicht übernommen.';

  if (marketState.error) {
    root.innerHTML = '<div class="notice-box">Die aktuellen Marktdaten konnten nicht geladen werden. Bitte die Originalquellen später erneut öffnen.</div>';
    return;
  }

  if (!marketState.data) {
    root.innerHTML = '<div class="notice-box">Aktuelle Marktdaten werden geladen …</div>';
    return;
  }

  const items = marketState.data[isJob ? 'jobs' : 'properties'] || [];
  const checkedAt = marketState.data.meta?.checkedAt;
  const methodNote = marketState.data.meta?.methodNote || '';

  const status = `
    <div class="market-status">
      <strong>Markt geprüft: ${escapeHtml(formatDate(checkedAt))} · ${escapeHtml(formatTime(checkedAt))} Uhr</strong>
      <p>${escapeHtml(methodNote)}</p>
    </div>`;

  const sections = marketPlaceOrder.map(place => {
    const placeItems = items.filter(item => item.municipality === place);
    return `
      <section class="market-place-section">
        <div class="market-section-head">
          <div>
            <p class="eyebrow">${place === 'Merzenich' ? 'Hauptgebiet' : 'Direkter Umkreis'}</p>
            <h2>${escapeHtml(place)}</h2>
            <p>${escapeHtml(marketPlaceDescription(place))}</p>
          </div>
          <span class="market-count">${placeItems.length} redaktionell geprüfte ${placeItems.length === 1 ? 'Anzeige' : 'Anzeigen'}</span>
        </div>
        ${placeItems.length
          ? `<div class="market-list-grid">${placeItems.map(item => renderMarketCard(item, kind)).join('')}</div>`
          : '<div class="notice-box">Derzeit ist hier kein einzelnes Angebot redaktionell verifiziert.</div>'}
        ${renderMarketSources(place, kind)}
      </section>`;
  }).join('');

  root.innerHTML = status + sections;
}

renderArchive = function patchedRenderArchive(key) {
  const root = $('#archiveGrid');
  if (key === 'stellen') {
    return renderMarketArchive('jobs');
  }
  if (key === 'immobilien') {
    return renderMarketArchive('properties');
  }
  root?.classList.remove('market-archive-grid');
  return originalRenderArchive(key);
};

function findServiceModule(label) {
  return $$('#serviceStack .service-module').find(module => {
    const firstLabel = $('summary span:first-child', module);
    return firstLabel?.textContent.trim() === label;
  });
}

function marketMiniItem(item, kind) {
  const isJob = kind === 'jobs';
  const main = isJob ? item.employer : item.price;
  return `
    <a class="market-mini" href="${escapeHtml(safeUrl(item.sourceUrl))}" target="_blank" rel="noopener noreferrer nofollow">
      <span class="market-mini-place">${marketLocationMarkup(item)}</span>
      <strong>${escapeHtml(item.title)}</strong>
      <span>${escapeHtml(main || '')}</span>
    </a>`;
}

function hydrateMarketService(kind) {
  if (!marketState.data) return;
  const isJob = kind === 'jobs';
  const module = findServiceModule(isJob ? 'Stellen' : 'Immobilien');
  if (!module) return;

  const items = marketState.data[isJob ? 'jobs' : 'properties'] || [];
  const merzenich = items.filter(item => item.municipality === 'Merzenich');
  const surrounding = items.filter(item => item.municipality !== 'Merzenich');
  const meta = $('.summary-meta', module);
  const body = $('.service-body', module);

  if (meta) meta.textContent = `${merzenich.length} Merzenich · +${surrounding.length} Umkreis`;
  if (!body) return;

  body.classList.remove('empty-state');
  body.innerHTML = merzenich.length
    ? `${merzenich.slice(0, 3).map(item => marketMiniItem(item, kind)).join('')}
       <div class="market-neighbor-note"><strong>Danach im Umkreis:</strong> Düren · Niederzier · Nörvenich · Elsdorf · Kerpen</div>`
    : '<div class="empty-state">Derzeit kein einzelnes Angebot in Merzenich verifiziert. Angebote aus dem Umkreis finden Sie über den vollständigen Marktbereich.</div>';
}

function injectMarketStyles() {
  if ($('#marketStyles')) return;
  const style = document.createElement('style');
  style.id = 'marketStyles';
  style.textContent = `
    .market-archive-grid{display:block}
    .market-status{border-top:3px solid var(--ink);border-bottom:1px solid var(--line);padding:14px 0 17px;margin-bottom:40px}
    .market-status strong{display:block;font-size:12px;text-transform:uppercase;letter-spacing:.06em}
    .market-status p{max-width:850px;margin:6px 0 0;color:var(--muted);font-size:12px}
    .market-place-section{margin-bottom:52px}
    .market-section-head{display:flex;justify-content:space-between;gap:28px;align-items:end;border-bottom:2px solid var(--ink);padding-bottom:14px;margin-bottom:4px}
    .market-section-head h2{font-family:"Source Serif 4",Georgia,serif;font-size:31px;line-height:1.08;margin:0}
    .market-section-head p:not(.eyebrow){font-size:12px;color:var(--muted);margin:7px 0 0;max-width:720px}
    .market-count{font-size:11px;color:var(--muted);white-space:nowrap}
    .market-list-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));column-gap:28px}
    .market-card{padding:20px 0 22px;border-bottom:1px solid var(--line);min-width:0}
    .market-location-line{min-height:18px;margin-bottom:7px}
    .market-place{font-size:10px;font-weight:800;letter-spacing:.09em;text-transform:uppercase}
    .market-place-primary{color:var(--red)}
    .market-place-neighbor{color:#3e3e3e}
    .market-card-meta{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em}
    .market-card h3{font-family:"Source Serif 4",Georgia,serif;font-size:22px;line-height:1.15;margin:7px 0 4px}
    .market-highlight{font-weight:800;font-size:14px;margin:0 0 4px}
    .market-facts{font-size:13px;color:#4f4f4f;margin:0 0 14px}
    .market-source-row{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:13px}
    .market-source-row>span{font-size:10px;color:var(--muted)}
    .market-source-row .more-button{flex:none}
    .market-live-sources{display:flex;align-items:center;gap:16px;flex-wrap:wrap;padding:14px 0;border-bottom:1px solid var(--ink)}
    .market-live-sources>strong{font-size:11px;text-transform:uppercase;letter-spacing:.06em}
    .market-live-sources>div{display:flex;gap:10px 18px;flex-wrap:wrap}
    .market-live-sources a{color:var(--red);font-size:12px;font-weight:700;border-bottom:1px solid currentColor}
    .market-live-sources a span{color:var(--muted);font-weight:500}
    .market-mini{display:block;padding:9px 0;border-top:1px solid #ececec}
    .market-mini:first-child{border-top:0}
    .market-mini .market-mini-place{display:block;line-height:1.2;margin-bottom:4px}
    .market-mini strong{display:block;font-family:"Source Serif 4",Georgia,serif;font-size:14px;line-height:1.25}
    .market-mini>span:last-child{display:block;font-size:10px;color:var(--muted);margin-top:3px}
    .market-mini:hover strong{text-decoration:underline;text-underline-offset:2px}
    .market-neighbor-note{font-size:10px;color:var(--muted);border-top:1px solid var(--line);padding-top:9px;margin-top:2px}
    .market-neighbor-note strong{color:var(--ink)}
    @media (max-width:760px){
      .market-list-grid{grid-template-columns:1fr}
      .market-section-head{display:block}
      .market-count{display:block;margin-top:10px}
      .market-source-row{align-items:flex-start;flex-direction:column}
    }
  `;
  document.head.append(style);
}

async function loadMarketData() {
  try {
    const response = await fetch('market.json', { cache: 'no-store' });
    if (!response.ok) throw new Error(`Market HTTP ${response.status}`);
    marketState.data = await response.json();
    marketState.error = null;
    hydrateMarketService('properties');
    hydrateMarketService('jobs');

    const footer = $('#footerDataStand');
    const checkedAt = marketState.data.meta?.checkedAt;
    if (footer && checkedAt && !footer.dataset.marketStand) {
      footer.dataset.marketStand = '1';
      footer.textContent += ` · Markt geprüft: ${formatDate(checkedAt)}, ${formatTime(checkedAt)} Uhr`;
    }

    const route = location.hash.replace(/^#/, '');
    if (route === 'stellen' || route === 'immobilien') handleRoute();
  } catch (error) {
    marketState.error = error;
    console.error('[Merzenich Aktuell] Markt: Daten konnten nicht geladen werden', error);
    const route = location.hash.replace(/^#/, '');
    if ((route === 'stellen' || route === 'immobilien') && state.content) handleRoute();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  injectMarketStyles();
  loadMarketData();
});
