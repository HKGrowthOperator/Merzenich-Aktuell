const state = {
  content: null,
  weather: null,
  weatherFromCache: false
};

const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

const viewConfig = {
  aktuell: { title: 'Aktuell', intro: 'Die neuesten Meldungen aus Merzenich und den Ortsteilen.', filter: () => true },
  blaulicht: { title: 'Blaulicht', intro: 'Polizei, Feuerwehr und relevante Einsatzmeldungen mit lokalem Bezug.', filter: a => a.category === 'Blaulicht' },
  sport: { title: 'Sport', intro: 'Ergebnisse, offene Spielstände, nächste Begegnungen und Tabellenstand.', special: 'sport' },
  termine: { title: 'Termine', intro: 'Kommende Veranstaltungen in Merzenich und den Ortsteilen.', special: 'events' },
  vereine: { title: 'Vereine', intro: 'Nachrichten und Termine aus dem lokalen Vereinsleben.', filter: a => a.category === 'Vereine' },
  rathaus: { title: 'Rathaus & Politik', intro: 'Informationen aus Gemeinde, Verwaltung und lokaler Politik.', filter: a => ['Gemeinde','Rathaus & Politik'].includes(a.category) },
  leben: { title: 'Leben', intro: 'Gemeindeleben, Umwelt, Bildung, Kirchen und soziale Themen.', filter: a => a.category === 'Leben' },
  wirtschaft: { title: 'Wirtschaft', intro: 'Unternehmen, Handel, Infrastruktur und wirtschaftliche Entwicklungen.', filter: a => a.category === 'Wirtschaft' },
  menschen: { title: 'Menschen', intro: 'Menschen, Initiativen und Geschichten aus der Gemeinde.', filter: a => a.category === 'Menschen' },
  immobilien: { title: 'Immobilienmarkt', intro: 'Immobilienanzeigen aus Merzenich und Umgebung.', special: 'empty', empty: 'Derzeit sind keine Immobilienanzeigen veröffentlicht.' },
  stellen: { title: 'Stellenmarkt', intro: 'Stellenangebote aus Merzenich und Umgebung.', special: 'empty', empty: 'Derzeit sind keine Stellenanzeigen veröffentlicht.' },
  trauer: { title: 'Traueranzeigen', intro: 'Traueranzeigen aus der Gemeinde.', special: 'empty', empty: 'Aktuell liegen keine veröffentlichten Traueranzeigen vor.' },
  familie: { title: 'Familienanzeigen', intro: 'Geburt, Hochzeit, Jubiläum und weitere Familienanzeigen.', special: 'empty', empty: 'Aktuell liegen keine veröffentlichten Familienanzeigen vor.' },
  kontakt: { title: 'Kontakt', intro: 'Kontakt zur Redaktion von Merzenich Aktuell.', special: 'info', body: 'Hinweise, Termine und redaktionelle Themen können über die Redaktion eingereicht werden. Die endgültigen Kontaktdaten werden vor dem öffentlichen Produktionsstart gepflegt.' },
  anzeigen: { title: 'Anzeigen', intro: 'Anzeigenbereiche von Merzenich Aktuell.', special: 'info', body: 'Immobilien, Stellen, Trauer- und Familienanzeigen werden getrennt verwaltet. Es werden keine Fake-Inhalte dargestellt.' },
  werben: { title: 'Werben & Mediadaten', intro: 'Werbemöglichkeiten auf Merzenich Aktuell.', special: 'info', body: 'Werbeslots sind getrennt vom redaktionellen Inhalt aufgebaut. Deaktivierte Slots erzeugen keinen Leerraum.' },
  unterstuetzen: { title: 'Unterstützen', intro: 'Lokalen Journalismus unterstützen.', special: 'info', body: 'Dieser Bereich bleibt als bestehende Funktion erhalten und wird vor dem Produktionsstart mit dem finalen Unterstützungsmodell verbunden.' },
  archiv: { title: 'Archiv', intro: 'Veröffentlichte Beiträge chronologisch durchsuchen.', filter: () => true },
  'ueber-uns': { title: 'Über uns', intro: 'Merzenich Aktuell ist eine lokale Internet-Zeitung für die Gemeinde Merzenich und Umkreis.', special: 'info', body: 'Der Schwerpunkt liegt auf nachvollziehbaren lokalen Informationen, klaren Quellen und einer ruhigen, redaktionellen Darstellung.' },
  redaktion: { title: 'Redaktion', intro: 'Redaktionelle Verantwortung und Arbeitsweise.', special: 'info', body: 'Gefundene Inhalte werden nicht automatisch veröffentlicht. Quelle, Datum, Ort, Bildrechte und redaktionelle Prüfung gehören zum Veröffentlichungsprozess.' },
  grundsaetze: { title: 'Grundsätze', intro: 'Quellen, Transparenz und lokale Relevanz.', special: 'info', body: 'Primärquellen werden bevorzugt. Inhalte anderer Medien werden nicht kopiert. Bilder werden nur mit geklärten Nutzungsrechten eingesetzt.' },
  'ki-redaktion': { title: 'KI & Redaktion', intro: 'Transparenter Umgang mit KI-Unterstützung.', special: 'info', body: 'KI kann bei Recherche, Strukturierung und Vorarbeit unterstützen. Veröffentlichung und redaktionelle Verantwortung bleiben bei Menschen.' }
};

function escapeHtml(value = '') {
  return String(value).replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));
}

// Intl.DateTimeFormat.format wirft bei einem ungueltigen Datum einen
// RangeError. Da die Formatierer mitten im Rendern stecken, riss ein einziger
// Artikel ohne publishedAt die komplette Startseite mit: kein Aufmacher, keine
// Karten, keine Termine. Fehlt das Datum, bleibt jetzt die Stelle leer.
function isValidDate(date) {
  return date instanceof Date && !Number.isNaN(date.getTime());
}

function formatDate(dateValue, options = {}) {
  const date = new Date(dateValue);
  if (!isValidDate(date)) return '';
  return new Intl.DateTimeFormat('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric', ...options }).format(date);
}

function formatTime(dateValue) {
  const date = new Date(dateValue);
  if (!isValidDate(date)) return '';
  return new Intl.DateTimeFormat('de-DE', { hour: '2-digit', minute: '2-digit' }).format(date);
}

// Adressen aus content.json landen in href-Attributen. escapeHtml haelt das
// Markup heil, verhindert aber kein javascript:-Schema. Erlaubt sind relative
// Ziele, Sprungmarken und die ueblichen Netzschemata.
function safeUrl(value) {
  const raw = String(value ?? '').trim();
  if (!raw) return '#';
  if (/^(#|\/|\.\.?\/)/.test(raw)) return raw;
  try {
    const url = new URL(raw, location.href);
    return ['http:', 'https:', 'mailto:', 'tel:'].includes(url.protocol) ? raw : '#';
  } catch {
    return '#';
  }
}

function locationMarkup(article) {
  const district = article.district ? ` <span class="district">· ${escapeHtml(article.district)}</span>` : '';
  return `<span class="main-location">${escapeHtml(article.location || 'MERZENICH')}</span>${district}`;
}

function articleHref(article) {
  return `#article/${encodeURIComponent(article.id)}`;
}

function publishedArticles() {
  return [...state.content.articles]
    .filter(a => a.status === 'published')
    .sort((a,b) => new Date(b.publishedAt) - new Date(a.publishedAt));
}

function heroScore(article, now = new Date()) {
  const ageHours = Math.max(0, (now - new Date(article.publishedAt)) / 36e5);
  const ageDays = ageHours / 24;
  if (ageDays > 7 && !article.heroOverride) return -Infinity;
  const freshness = Math.max(0, 100 - ageDays * 14);
  return freshness * .4 + Number(article.localRelevance || 0) * .3 + Number(article.editorialPriority || 0) * .3;
}

function pickHero() {
  const now = new Date();
  // publishedArticles() liefert bereits eine eigene, nach Datum sortierte Liste.
  // Der fruehere Rueckfall auf publishedArticles()[0] konnte nie greifen, weil
  // [0] einer nichtleeren Liste nie falsy ist, und sortierte die Liste ein
  // zweites Mal.
  const artikel = publishedArticles();
  if (!artikel.length) return null;
  return artikel.sort((a,b) => heroScore(b, now) - heroScore(a, now))[0];
}

function renderHero() {
  const article = pickHero();
  const root = $('#heroStory');
  if (!article) return;
  root.innerHTML = `
    <a class="hero-media" href="${articleHref(article)}">
      <img src="${escapeHtml(article.image)}" alt="${escapeHtml(article.imageAlt)}" fetchpriority="high">
      <span class="image-credit">${escapeHtml(article.imageCredit)}</span>
    </a>
    <div class="location-line">${locationMarkup(article)}</div>
    <h1><a href="${articleHref(article)}">${escapeHtml(article.headline)}</a></h1>
    <p class="hero-teaser">${escapeHtml(article.teaser)}</p>
    <div class="meta-row"><span class="category">${escapeHtml(article.category)}</span><span>${formatDate(article.publishedAt)}</span><span>${article.comments || 0} Kommentare</span></div>
    <a class="more-button" href="${articleHref(article)}">Mehr lesen</a>`;
}

function buildCard(article) {
  const template = $('#newsCardTemplate');
  const node = template.content.cloneNode(true);
  $('.news-image-link', node).href = articleHref(article);
  // Ohne Bildadresse wurde der String "undefined" als src gesetzt und der
  // Browser holte sich dafuer eine 404.
  const bild = $('.news-image', node);
  if (article.image) {
    bild.src = article.image;
  } else {
    bild.removeAttribute('src');
    bild.hidden = true;
  }
  bild.alt = article.imageAlt || '';
  $('.image-credit', node).textContent = article.imageCredit || '';
  $('.location-line', node).innerHTML = locationMarkup(article);
  $('.category', node).textContent = article.category;
  $('.date', node).textContent = formatDate(article.publishedAt);
  $('.comments', node).textContent = `${article.comments || 0} Kommentare`;
  $('.headline-link', node).textContent = article.headline;
  $('.headline-link', node).href = articleHref(article);
  $('.teaser', node).textContent = article.teaser;
  $('.more-button', node).href = articleHref(article);
  return node;
}

function renderNewsGrid(hero = pickHero()) {
  const grid = $('#newsGrid');
  grid.innerHTML = '';
  const artikel = publishedArticles();
  artikel.filter(a => a.id !== hero?.id).slice(0,6).forEach(article => grid.append(buildCard(article)));

  const compact = $('#compactNews');
  compact.innerHTML = artikel.slice(0,4).map(article => `
    <div class="compact-item">
      <div class="compact-meta">${escapeHtml(article.category)} · ${formatDate(article.publishedAt)}</div>
      <a href="${articleHref(article)}">${escapeHtml(article.headline)}</a>
    </div>`).join('');
}

function upcomingEvents() {
  const now = new Date();
  return [...(state.content.events || [])]
    .filter(event => new Date(event.end || event.start) >= now)
    .sort((a,b) => new Date(a.start) - new Date(b.start));
}

function renderEvents() {
  const root = $('#eventList');
  const events = upcomingEvents().slice(0,6);
  root.innerHTML = events.length ? events.map(event => {
    const date = new Date(event.start);
    const day = new Intl.DateTimeFormat('de-DE',{day:'2-digit'}).format(date);
    const month = new Intl.DateTimeFormat('de-DE',{month:'short'}).format(date).replace('.','');
    return `<a class="event-row" href="${escapeHtml(safeUrl(event.sourceUrl))}" target="_blank" rel="noopener noreferrer">
      <div class="event-date"><span class="event-day">${day}</span><span class="event-month">${month}</span></div>
      <div class="event-info"><strong>${escapeHtml(event.title)}</strong><span>${formatTime(event.start)} Uhr · ${escapeHtml(event.place)}</span></div>
    </a>`;
  }).join('') : '<div class="empty-state">Derzeit sind keine kommenden Termine eingetragen.</div>';
}

function weatherCode(code) {
  if (code === 0) return ['☀️','Klar'];
  if ([1,2].includes(code)) return ['🌤️','Heiter'];
  if (code === 3) return ['☁️','Bewölkt'];
  if ([45,48].includes(code)) return ['🌫️','Nebel'];
  if ([51,53,55,56,57].includes(code)) return ['🌦️','Nieselregen'];
  if ([61,63,65,66,67,80,81,82].includes(code)) return ['🌧️','Regen'];
  if ([71,73,75,77,85,86].includes(code)) return ['🌨️','Schnee'];
  if ([95,96,99].includes(code)) return ['⛈️','Gewitter'];
  return ['🌥️','Wetter'];
}

function normalizeWeather(payload) {
  const daily = payload.daily || {};
  const current = payload.current || payload.current_weather || {};
  return {
    fetchedAt: new Date().toISOString(),
    current: {
      temperature: current.temperature_2m ?? current.temperature,
      code: current.weather_code ?? current.weathercode
    },
    days: (daily.time || []).slice(0,3).map((date,index) => ({
      date,
      max: daily.temperature_2m_max?.[index],
      min: daily.temperature_2m_min?.[index],
      rain: daily.precipitation_probability_max?.[index],
      code: daily.weather_code?.[index]
    }))
  };
}

// localStorage wirft im privaten Fenster und bei gesperrten Websitedaten schon
// beim Lesen, und ein beschaedigter Eintrag wirft in JSON.parse. Beides lag
// ausserhalb des try-Blocks und beendete loadWeather mit einem unbehandelten
// Fehler.
function cacheLesen(key) {
  try {
    return JSON.parse(localStorage.getItem(key) || 'null');
  } catch {
    return null;
  }
}

function cacheSchreiben(key, wert) {
  try {
    localStorage.setItem(key, JSON.stringify(wert));
  } catch {
    // Kein Zwischenspeicher verfuegbar. Das Wetter wird dann bei jedem Aufruf
    // neu geholt, was die Anzeige nicht beeintraechtigt.
  }
}

async function loadWeather() {
  const cacheKey = 'ma-weather-v1';
  const cached = cacheLesen(cacheKey);
  const cacheAge = cached ? Date.now() - new Date(cached.fetchedAt).getTime() : Infinity;
  if (cached && cacheAge < 20 * 60 * 1000) {
    state.weather = cached;
    state.weatherFromCache = true;
    renderWeather();
    return;
  }

  const url = 'https://api.open-meteo.com/v1/forecast?latitude=50.8265&longitude=6.5240&current=temperature_2m,weather_code&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max,weather_code&timezone=Europe%2FBerlin&forecast_days=3';
  try {
    const response = await fetch(url, { headers: { 'Accept':'application/json' } });
    if (!response.ok) throw new Error(`Weather HTTP ${response.status}`);
    const weather = normalizeWeather(await response.json());
    if (!Number.isFinite(Number(weather.current.temperature))) throw new Error('Invalid weather data');
    state.weather = weather;
    state.weatherFromCache = false;
    cacheSchreiben(cacheKey, weather);
  } catch (error) {
    if (cached && Number.isFinite(Number(cached.current?.temperature))) {
      state.weather = cached;
      state.weatherFromCache = true;
    } else {
      state.weather = null;
      console.warn('Weather unavailable and no valid cache exists', error);
    }
  }
  renderWeather();
}

function renderWeather() {
  const inline = $('#headerWeather');
  const summary = $('#weatherSummary');
  const panel = $('#weatherPanel');
  const module = $('.weather-module');
  if (!state.weather) {
    inline.textContent = '';
    module.hidden = true;
    return;
  }
  module.hidden = false;
  const [icon, condition] = weatherCode(Number(state.weather.current.code));
  const temp = Math.round(Number(state.weather.current.temperature));
  inline.textContent = `${icon} ${temp} °C`;
  summary.textContent = `${temp} °C`;
  const dayLabels = ['Heute','Morgen','Übermorgen'];
  panel.innerHTML = `
    <div class="weather-current"><strong>${icon} ${temp}°</strong><div class="weather-condition">${condition}</div></div>
    <div class="weather-days">${state.weather.days.map((day,index) => {
      const [dayIcon, dayCondition] = weatherCode(Number(day.code));
      const rain = Number.isFinite(Number(day.rain)) ? `${Math.round(day.rain)} % Regen` : 'Regen –';
      const spanne = [day.max, day.min].every(w => Number.isFinite(Number(w)))
        ? `${Math.round(day.max)}° / ${Math.round(day.min)}°`
        : '–';
      return `<div class="weather-day"><strong>${dayLabels[index]} ${dayIcon}</strong><span>${spanne}</span><span>${dayCondition}</span><span>${rain}</span></div>`;
    }).join('')}</div>
    <div class="weather-updated">Stand ${formatTime(state.weather.fetchedAt)} Uhr${state.weatherFromCache ? ' · letzter gültiger Datenstand' : ''}</div>`;
}

function updateClock() {
  const now = new Date();
  $('#headerTime').textContent = `${new Intl.DateTimeFormat('de-DE',{hour:'2-digit',minute:'2-digit'}).format(now)} Uhr`;
  $('#headerDate').textContent = new Intl.DateTimeFormat('de-DE',{weekday:'short',day:'2-digit',month:'2-digit'}).format(now);
  $('#currentYear').textContent = now.getFullYear();
}

function renderSport(root = $('#sportPreview'), compact = false) {
  const sport = state.content.sport;
  // Fehlt der Sportdatensatz, wird der Abschnitt ausgeblendet statt beim
  // Zugriff auf dataCheckedAt zu werfen.
  if (!sport || !sport.pending || !sport.next || !sport.table) {
    root.innerHTML = '';
    root.hidden = true;
    return;
  }
  root.hidden = false;
  const checked = new Date(sport.dataCheckedAt);
  root.innerHTML = `
    <div class="sport-preview-head">
      <div><p class="eyebrow">Sport</p><h2 id="sportPreviewTitle">${escapeHtml(sport.team)}</h2></div>
      <div class="sport-data-stand">Datenstand: ${formatDate(checked)}, ${formatTime(checked)} Uhr · ${escapeHtml(sport.sourceName)}</div>
    </div>
    <div class="sport-grid">
      <div class="sport-box"><span class="sport-label">Offenes Spiel</span><strong>${escapeHtml(sport.pending.home)} – ${escapeHtml(sport.pending.away)}</strong><span class="pending-status">${escapeHtml(sport.pending.status)}</span><span>${formatDate(sport.pending.date)} · ${formatTime(sport.pending.date)} Uhr</span></div>
      <div class="sport-box"><span class="sport-label">Nächstes Spiel</span><strong>${escapeHtml(sport.next.home)} – ${escapeHtml(sport.next.away)}</strong><span>${formatDate(sport.next.date)} · ${formatTime(sport.next.date)} Uhr</span></div>
      <div class="sport-box"><span class="sport-label">Tabelle</span><strong>Platz ${sport.table.position} · ${sport.table.points} Punkte</strong><span>Torverhältnis ${escapeHtml(sport.table.goals)}</span></div>
    </div>
    ${compact ? '' : `<p style="margin:12px 0 0;font-size:12px;color:#686868">Quelle: <a class="text-link" href="${escapeHtml(safeUrl(sport.sourceUrl))}" target="_blank" rel="noopener noreferrer">FUSSBALL.DE</a></p>`}`;
}

function renderAds() {
  ['homepage_sidebar_top','homepage_sidebar_middle'].forEach((slotId,index) => {
    const root = $(index === 0 ? '#adSlotTop' : '#adSlotMiddle');
    const ad = (state.content.ads || []).find(item => item.id === slotId);
    if (!ad?.active) {
      root.innerHTML = '';
      root.hidden = true;
      return;
    }
    root.hidden = false;
    root.innerHTML = `<a class="ad-slot" href="${escapeHtml(safeUrl(ad.target))}"><span class="ad-label">${escapeHtml(ad.label || 'ANZEIGE')}</span><strong>${escapeHtml(ad.headline)}</strong></a>`;
  });
}

function renderArchive(key) {
  const config = viewConfig[key] || viewConfig.aktuell;
  $('#archiveEyebrow').textContent = 'Merzenich Aktuell';
  $('#archiveTitle').textContent = config.title;
  $('#archiveIntro').textContent = config.intro;
  const root = $('#archiveGrid');
  root.innerHTML = '';

  if (config.special === 'sport') {
    const sportWrap = document.createElement('section');
    sportWrap.className = 'sport-preview';
    sportWrap.style.gridColumn = '1 / -1';
    root.append(sportWrap);
    renderSport(sportWrap);
    return;
  }

  if (config.special === 'events') {
    const events = upcomingEvents();
    root.innerHTML = events.length ? events.map(event => `<article class="news-card"><div class="news-card-body"><div class="location-line"><span class="main-location">MERZENICH</span>${event.district ? ` <span class="district">· ${escapeHtml(event.district)}</span>` : ''}</div><div class="news-meta-line"><span class="category">Termin</span><span>${formatDate(event.start)} · ${formatTime(event.start)} Uhr</span></div><h3>${escapeHtml(event.title)}</h3><p class="teaser">${escapeHtml(event.place)}</p><a class="more-button" href="${escapeHtml(safeUrl(event.sourceUrl))}" target="_blank" rel="noopener noreferrer">Quelle öffnen</a></div></article>`).join('') : '<div class="notice-box">Keine kommenden Veranstaltungen.</div>';
    return;
  }

  if (config.special === 'empty' || config.special === 'info') {
    root.innerHTML = `<div class="notice-box" style="grid-column:1/-1"><strong>${escapeHtml(config.title)}</strong><p>${escapeHtml(config.empty || config.body || '')}</p></div>`;
    return;
  }

  const articles = publishedArticles().filter(config.filter || (() => true));
  if (!articles.length) {
    root.innerHTML = '<div class="notice-box" style="grid-column:1/-1">Für dieses Ressort liegen aktuell keine veröffentlichten Meldungen vor.</div>';
    return;
  }
  articles.forEach(article => root.append(buildCard(article)));
}

function renderArticle(id) {
  const article = state.content.articles.find(item => item.id === id && item.status === 'published');
  if (!article) {
    $('#articleView').innerHTML = '<div class="notice-box">Der Beitrag wurde nicht gefunden.</div>';
    return;
  }
  $('#articleView').innerHTML = `
    <div class="article-location">${locationMarkup(article)}</div>
    <h1>${escapeHtml(article.headline)}</h1>
    <p class="article-lead">${escapeHtml(article.teaser)}</p>
    <div class="meta-row"><span class="category">${escapeHtml(article.category)}</span><span>${formatDate(article.publishedAt)}</span><span>Redaktion Merzenich Aktuell</span><a href="#comments">${article.comments || 0} Kommentare</a></div>
    <figure class="article-hero"><img src="${escapeHtml(article.image)}" alt="${escapeHtml(article.imageAlt)}"><figcaption class="image-credit">${escapeHtml(article.imageCredit)}</figcaption></figure>
    <div class="article-box"><h2>Das Wichtigste in Kürze</h2><p>${escapeHtml(article.teaser)}</p></div>
    <div class="article-body">${(article.body || []).map(p => `<p>${escapeHtml(p)}</p>`).join('')}</div>
    <div class="article-box"><h2>Quelle &amp; Transparenz</h2><p>Quelle geprüft · Datum geprüft · Ort geprüft · redaktionell zusammengefasst.</p><p><a href="${escapeHtml(safeUrl(article.sourceUrl))}" target="_blank" rel="noopener noreferrer">Originalquelle: ${escapeHtml(article.sourceName)}</a></p><p>Bild: ${escapeHtml(article.imageCredit)}</p></div>
    <section class="comment-section" id="comments"><p class="eyebrow">Diskussion</p><h2>${article.comments || 0} Kommentare</h2><p class="comment-note">Kommentare werden moderiert. Beleidigungen, persönliche Daten und unbelegte Anschuldigungen werden nicht veröffentlicht.</p><form class="comment-form" id="commentForm"><input name="name" required placeholder="Name"><input name="email" type="email" required placeholder="E-Mail (wird nicht veröffentlicht)"><textarea name="comment" required placeholder="Kommentar"></textarea><button type="submit">Kommentar absenden</button><p class="comment-note" id="commentFormStatus">Der Kommentar-Endpunkt wird erst auf der verbundenen Produktionsplattform aktiviert.</p></form></section>`;

  $('#commentForm')?.addEventListener('submit', event => {
    event.preventDefault();
    $('#commentFormStatus').textContent = 'Kommentar noch nicht gesendet: Für die GitHub-Preview ist noch kein persistenter Kommentar-Backend-Endpunkt verbunden.';
  });
}

function showOnly(view) {
  $('#homeView').hidden = view !== 'home';
  $('#archiveView').hidden = view !== 'archive';
  $('#articleView').hidden = view !== 'article';
  $('#searchView').hidden = view !== 'search';
  window.scrollTo({top:0,behavior:'instant'});
}

function handleRoute() {
  if (!state.content) return;
  // Ein einzelnes Prozentzeichen in der Adresse (etwa #100%) liess
  // decodeURIComponent mit einem URIError abbrechen. Der Fehler beendete den
  // Start, wodurch das Wetter fuer den ganzen Besuch ausfiel.
  const roh = location.hash.replace(/^#/,'');
  let raw;
  try {
    raw = decodeURIComponent(roh) || 'home';
  } catch {
    raw = roh || 'home';
  }
  if (raw === 'home') {
    showOnly('home');
    return;
  }
  if (raw.startsWith('article/')) {
    showOnly('article');
    renderArticle(raw.split('/').slice(1).join('/'));
    return;
  }
  const key = raw in viewConfig ? raw : 'aktuell';
  showOnly('archive');
  renderArchive(key);
}

function performSearch(query) {
  const q = query.trim().toLocaleLowerCase('de-DE');
  showOnly('search');
  const results = q ? publishedArticles().filter(article => [article.headline,article.teaser,article.category,article.location,article.district,...(article.body || [])].join(' ').toLocaleLowerCase('de-DE').includes(q)) : [];
  $('#searchResultMeta').textContent = q ? `${results.length} Treffer für „${query.trim()}“` : 'Bitte Suchbegriff eingeben.';
  const root = $('#searchResults');
  root.innerHTML = '';
  results.forEach(article => root.append(buildCard(article)));
  if (!results.length && q) root.innerHTML = '<div class="notice-box" style="grid-column:1/-1">Keine passenden Meldungen gefunden.</div>';
}

function bindUi() {
  $('#mobileMenuToggle').addEventListener('click', () => {
    const nav = $('#mainNav');
    const isOpen = nav.classList.toggle('is-open');
    $('#mobileMenuToggle').setAttribute('aria-expanded', String(isOpen));
  });
  $$('#mainNav a').forEach(link => link.addEventListener('click', () => {
    $('#mainNav').classList.remove('is-open');
    $('#mobileMenuToggle').setAttribute('aria-expanded','false');
  }));
  $('#searchForm').addEventListener('submit', event => {
    event.preventDefault();
    performSearch($('#siteSearch').value);
  });
  window.addEventListener('hashchange', handleRoute);
}

// Kapselt einen Startschritt. Faellt einer aus, laufen die uebrigen weiter und
// die Ursache steht mit Namen in der Konsole.
function schritt(name, fn) {
  try {
    fn();
  } catch (error) {
    console.error(`[Merzenich Aktuell] ${name}: Schritt fehlgeschlagen`, error);
  }
}

function renderDataStand() {
  const stand = state.content.meta?.lastEditorialCheck;
  const datum = formatDate(stand);
  const zeit = formatTime(stand);
  $('#footerDataStand').textContent = datum && zeit
    ? `Redaktioneller Datenstand: ${datum}, ${zeit} Uhr`
    : 'Redaktioneller Datenstand: nicht hinterlegt';
}

async function init() {
  try {
    const response = await fetch('content.json', { cache: 'no-store' });
    if (!response.ok) throw new Error(`Content HTTP ${response.status}`);
    state.content = await response.json();
  } catch (error) {
    document.body.innerHTML = '<main class="shell" style="padding:40px 0"><h1>Merzenich Aktuell</h1><p>Die redaktionellen Daten konnten nicht geladen werden.</p></main>';
    console.error(error);
    return;
  }

  // Der Start war eine ungesicherte Kette: warf ein Schritt, blieb alles
  // dahinter liegen. Ein fehlender Block in content.json (sport, ads, events,
  // meta) reichte, damit bindUi und handleRoute nie liefen. Die Seite sah dann
  // vollstaendig aus, war aber tot: Uhr auf Strich, Ressortklicks ohne
  // Wirkung, Suche ohne Funktion. Jeder Schritt steht jetzt fuer sich.
  schritt('Aufmacher', renderHero);
  schritt('Nachrichtenraster', renderNewsGrid);
  schritt('Termine', renderEvents);
  schritt('Sport', () => renderSport());
  schritt('Werbeplaetze', renderAds);
  schritt('Uhr', updateClock);
  setInterval(() => schritt('Uhr', updateClock), 30000);
  schritt('Datenstand', renderDataStand);
  schritt('Bedienung', bindUi);
  schritt('Routenwahl', handleRoute);
  loadWeather().catch(error => console.error('[Merzenich Aktuell] Wetter: Schritt fehlgeschlagen', error));
}

document.addEventListener('DOMContentLoaded', init);
