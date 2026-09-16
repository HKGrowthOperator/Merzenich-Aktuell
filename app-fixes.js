/*
 * Small compatibility layer for the static GitHub preview.
 * Keep production logic in WordPress; this file only hardens preview rendering.
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
