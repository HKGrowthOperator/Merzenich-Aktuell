/* Merzenich Aktuell – redaktioneller Refresh 17.09.2026
 * Quellenstand: 17.09.2026. Keine Demo-Inhalte. Externe Marktangebote bleiben
 * bei den Originalanbietern; Traueranzeigen werden nicht als fremde Anzeigen
 * kopiert, sondern nur über verifizierte Quellen erschlossen.
 */
(() => {
  'use strict';
  const path = location.pathname.replace(/\/+$/, '') || '/';
  const q = (s, r=document) => r.querySelector(s);
  const qa = (s, r=document) => Array.from(r.querySelectorAll(s));

  /* Stilllegung 17.09.2026 — Gewerk 3: Laufzeit-Skripte schreiben kein Markup mehr um.
   * Grund: Der ausgelieferte Stand ist der sichtbare Stand. Diese Datei war ein
   * kompletter Redaktionsstand in JavaScript-Form: Sie hat Termine, Familien- und
   * Traueranzeigen, Marktverweise und einen Umkreis-Block erst nach dem Laden in die
   * Seite geschrieben, dazu ein eigenes Stylesheet in den Kopf gehaengt. Damit stand
   * echter Inhalt nur im Skript und nicht in der Seite, und die Gestaltung kam aus
   * einer zehnten Quelle. Beides gehoert in die Generatoren unter deploy/*.mjs.
   * Alle Einstiege haengen jetzt an dieser Konstante und bleiben aus. Der Inhalt
   * bleibt im Quelltext stehen, damit die Redaktion ihn uebernehmen kann, ohne ihn
   * neu zu recherchieren; wohin er gehoert, steht in den naechsten Schritten.
   * true setzen ist nur zum Vergleichen gedacht, nicht fuer den Betrieb. */
  const LAUFZEIT_UMBAU = false;

  const style = document.createElement('style');
  style.textContent = `
    .ma-refresh-note{border-top:3px solid #8e1f2d;border-bottom:1px solid var(--line,#ddd);padding:12px 0;margin:0 0 20px;font-size:.88rem;color:var(--muted,#666)}
    .ma-live-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin:18px 0 30px}.ma-live-card{border-top:2px solid #8e1f2d;padding-top:12px}.ma-live-card h3{margin:0 0 7px;font-size:1.05rem}.ma-live-card p{margin:0 0 9px;line-height:1.45}.ma-live-card .meta{font-size:.82rem;color:var(--muted,#666)}
    .ma-market-more{margin:32px 0;padding-top:20px;border-top:1px solid var(--line,#ddd)}.ma-market-more h2{margin:0 0 6px}.ma-market-more>p{margin:0 0 18px;color:var(--muted,#666)}.ma-market-city{padding:14px 0;border-top:1px solid var(--line,#ddd)}.ma-market-city strong{display:block;margin-bottom:7px}.ma-market-links{display:flex;flex-wrap:wrap;gap:8px}.ma-market-links a{display:inline-flex;padding:7px 10px;border:1px solid currentColor;border-radius:3px;text-decoration:none;font-size:.88rem}
    .ma-family-list,.ma-trauer-list{display:grid;gap:0;margin-top:20px}.ma-family-item,.ma-trauer-item{padding:18px 0;border-top:1px solid var(--line,#ddd)}.ma-family-item h2,.ma-trauer-item h2{font-size:1.18rem;margin:0 0 6px}.ma-family-item p,.ma-trauer-item p{margin:4px 0}.ma-source{font-size:.86rem;color:var(--muted,#666)}
    .ma-umkreis{margin:36px auto}.ma-umkreis-head{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:18px}.ma-umkreis-head h2{margin:2px 0 0}.ma-umkreis-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}.ma-umkreis-card{border-top:2px solid #8e1f2d;padding-top:12px}.ma-umkreis-card h3{font-size:1.12rem;margin:5px 0 7px}.ma-umkreis-card p{margin:0 0 9px}.ma-umkreis-card .eyebrow{display:block}
    @media(max-width:800px){.ma-live-grid,.ma-umkreis-grid{grid-template-columns:1fr}.ma-umkreis-head{display:block}}
  `;
  /* Das Stylesheet formatiert ausschliesslich Markup, das diese Datei selbst erzeugt
   * hat. Ohne dieses Markup waere es toter Ballast und eine weitere Schicht im
   * ohnehin ueberfuellten CSS-Stapel — deshalb haengt auch die Einbindung am Schalter. */
  if (LAUFZEIT_UMBAU) document.head.append(style);

  function a(url, text){ return `<a href="${url}" target="_blank" rel="noopener noreferrer">${text}</a>`; }

  // Der Startseiten-Block dieses Layers verlinkt vier Artikel, die noch nicht als
  // Seiten im Repo liegen (/blaulicht/grundschule-geschwindigkeitskontrolle/,
  // /wirtschaft/bluetenliebe-couture-eroeffnet/, /sport/damen-30-aufstieg/,
  // /blaulicht/einsaetze-126-127-september/) und laedt ein Wikimedia-Bild am
  // Bildproxy vorbei. Bis die Artikel als Seiten existieren, bleibt der Block aus;
  // Aufmacher und Nebenmeldungen kommen dann automatisch aus dem Inhaltsindex
  // (deploy/inhaltsindex.mjs) und api/editorial-current.json.
  const STARTSEITE_AUS_LAYER = false;
  function refreshHome(){
    const grid = STARTSEITE_AUS_LAYER ? q('.frontpage-grid') : null;
    if (grid) {
      grid.innerHTML = `
        <article class="front-lead" data-story="2026-09-16-grundschule-geschwindigkeit">
          <a href="/blaulicht/grundschule-geschwindigkeitskontrolle/" tabindex="-1" aria-hidden="true"><div class="media"><img src="https://commons.wikimedia.org/wiki/Special:FilePath/Neue%20Streifenwagen%20f%C3%BCr%20die%20Polizei%20vorgestellt.jpg?width=1600" alt="Streifenwagen der Polizei Nordrhein-Westfalen" loading="eager" fetchpriority="high" data-editorial-image><span class="badge">Symbolbild</span></div></a>
          <div class="front-lead-copy"><div class="location-line"><span class="location-brand">MERZENICH</span></div><span class="kicker">Verkehrssicherheit · 16.09.</span><h1><a href="/blaulicht/grundschule-geschwindigkeitskontrolle/">37 Zitronen für Temposünder: Viertklässler kontrollieren mit Polizei</a></h1><p>Grundschulkinder unterstützten Polizei und Ordnungsamt bei einer Geschwindigkeitskontrolle. 37 Fahrer erhielten eine Zitrone, in drei Fällen wurden Verwarngelder fällig.</p><div class="meta"><time datetime="2026-09-16T11:14:00+02:00">16.09. · 11:14 Uhr</time><span>Quelle: Polizei Düren</span></div><div class="story-actions"><a class="read-more" href="/blaulicht/grundschule-geschwindigkeitskontrolle/">Mehr lesen</a></div></div>
        </article>
        <div class="front-side"><span class="eyebrow">Neu aus der Gemeinde</span>
          <article class="front-brief"><div><div class="location-line"><span class="location-brand">MERZENICH</span></div><span class="kicker">Wirtschaft · 15.09.</span><h2><a href="/wirtschaft/bluetenliebe-couture-eroeffnet/">Neue Floristik-Boutique „blütenliebe couture“ eröffnet</a></h2><p>Michelle Junglas und Isabelle Bohland haben an der Lindenstraße 14 eröffnet.</p><div class="story-actions"><a class="read-more" href="/wirtschaft/bluetenliebe-couture-eroeffnet/">Mehr lesen</a></div></div></article>
          <article class="front-brief"><div><div class="location-line"><span class="location-brand">MERZENICH</span></div><span class="kicker">Tennis · 14.09.</span><h2><a href="/sport/damen-30-aufstieg/">Damen 30 des SC Merzenich steigen auf</a></h2><p>Ein 5:1 gegen den VfL Bardenberg sicherte Rang zwei und den Aufstieg.</p><div class="story-actions"><a class="read-more" href="/sport/damen-30-aufstieg/">Mehr lesen</a></div></div></article>
          <article class="front-brief"><div><div class="location-line"><span class="location-brand">MERZENICH</span> · MORSCHENICH / GIRBELSRATH</div><span class="kicker">Feuerwehr · 14.09.</span><h2><a href="/blaulicht/einsaetze-126-127-september/">eCall in Morschenich und Ölspur in Girbelsrath</a></h2><p>Zwei Einsätze am Montag: kein Unfall am eCall-Ort, anschließend Ölspur beseitigt.</p><div class="story-actions"><a class="read-more" href="/blaulicht/einsaetze-126-127-september/">Mehr lesen</a></div></div></article>
        </div>`;
    }

    // Die Terminliste der Startseite schreibt deploy/termine-prerender.mjs aus den
    // echten Terminseiten. Das fest eingetragene Ersatzprogramm unten ueberschrieb
    // sie im Browser mit einem Stand vom 17.09. (gefunden 23.09.). Es bleibt nur
    // aktiv, wenn dieser Layer die Startseite ausdruecklich uebernehmen soll.
    const service = STARTSEITE_AUS_LAYER ? q('.portal-service') : null;
    if (service) {
      const events = q('.agenda-list', service);
      if (events) events.innerHTML = `
        <article class="agenda-row" data-event-end="2026-09-25T23:59:00+02:00"><time class="agenda-date" datetime="2026-09-11"><b>11</b><span>Sep</span></time><div><span class="eyebrow">Gemeinde</span><h3><a href="/termine/faire-woche-2026/">Faire Woche</a></h3><p>bis 25.09. · Gemeindegebiet</p></div></article>
        <article class="agenda-row" data-event-end="2026-09-20T13:00:00+02:00"><time class="agenda-date" datetime="2026-09-20T11:00:00+02:00"><b>20</b><span>Sep</span></time><div><span class="eyebrow">Mitmachen</span><h3>${a('https://www.heimat-info.de/beitraege/fdb48f37-4d9d-4e4d-8a5b-533bc9832951','Merzenich räumt auf')}</h3><p>11:00–13:00 Uhr · gesamtes Gemeindegebiet</p></div></article>
        <article class="agenda-row" data-event-end="2026-09-21T17:00:00+02:00"><time class="agenda-date" datetime="2026-09-21T14:30:00+02:00"><b>21</b><span>Sep</span></time><div><span class="eyebrow">Kinder</span><h3>${a('https://www.heimat-info.de/beitraege/fdb48f37-4d9d-4e4d-8a5b-533bc9832951','Naturdetektive im Pützpark')}</h3><p>14:30–17:00 Uhr · 6–10 Jahre</p></div></article>
        <article class="agenda-row" data-event-end="2026-09-28T17:00:00+02:00"><time class="agenda-date" datetime="2026-09-28T15:00:00+02:00"><b>28</b><span>Sep</span></time><div><span class="eyebrow">Natur</span><h3>${a('https://www.heimat-info.de/beitraege/fdb48f37-4d9d-4e4d-8a5b-533bc9832951','Nistkästen für Vögel bauen')}</h3><p>15:00–17:00 Uhr · Grundschulkinder</p></div></article>`;
      const acc = qa('.service-accordion', service);
      const trauer = acc.find(x => q('summary',x)?.textContent.includes('Traueranzeigen'));
      const familie = acc.find(x => q('summary',x)?.textContent.includes('Familienanzeigen'));
      if (trauer) q('div',trauer).innerHTML = '<p>Regionale Trauerquellen sind jetzt gebündelt.</p><a href="/traueranzeigen/">Traueranzeigen & Quellen</a>';
      if (familie) q('div',familie).innerHTML = '<p>Neue öffentliche Hochzeitsmeldungen aus Merzenich.</p><a href="/familienanzeigen/">Familienmeldungen</a>';
    }

    const district = q('.district-desk');
    if (district && !q('.ma-umkreis')) {
      const sec = document.createElement('section'); sec.className='desk shell ma-umkreis';
      sec.innerHTML = `<div class="ma-umkreis-head"><div><span class="eyebrow">Direkter Umkreis · aktuell</span><h2>Was rund um Merzenich ansteht</h2></div><span>Stand 17.09.2026</span></div><div class="ma-umkreis-grid">
        <article class="ma-umkreis-card"><span class="eyebrow">Düren · 18.–20.09.</span><h3>${a('https://www.dueren.de/kultur-tourismus/veranstaltungen/veranstaltungskalender/veranstaltung/stadtfest','Dürener Stadtfest mit Partnerschaftsmeile')}</h3><p>Live-Musik, Familienprogramm und verkaufsoffener Sonntag in der Innenstadt.</p><div class="meta">Quelle: Stadt Düren</div></article>
        <article class="ma-umkreis-card"><span class="eyebrow">Düren · 26.09.</span><h3>${a('https://www.dueren.de/de/verwaltung-politik/stadtverwaltung/presse/pressemitteilung/kultur-bewegt-theater-dueren-startet-mit-vielfaeltigem-programm-in-die-neue-spielzeit','Theater Düren startet in die neue Spielzeit')}</h3><p>Auftakt mit „Altes Land“ am 26. September im Haus der Stadt.</p><div class="meta">Quelle: Stadt Düren</div></article>
        <article class="ma-umkreis-card"><span class="eyebrow">Elsdorf · 18./19.09.</span><h3>${a('https://www.elsdorf.de/rathaus-service/verwaltung/aktuell/2674/zwei-partynaechte-fuer-zwei-generationen-in-der-festhalle-elsdorf','Zwei Partynächte in der Festhalle')}</h3><p>Ü60-Party am Freitag, „BACK FOR GOOD“ für Millennials am Samstag.</p><div class="meta">Quelle: Stadt Elsdorf</div></article>
      </div>`;
      district.parentNode.insertBefore(sec, district);
    }
  }

  function marketMore(kind){
    const list = q('.markt-liste-body') || q('.event-list');
    if (!list || q('.ma-market-more')) return;
    const sec=document.createElement('section'); sec.className='ma-market-more';
    if(kind==='jobs'){
      sec.innerHTML=`<h2>Noch mehr aktuelle Stellen im direkten Umkreis</h2><p>Die Einzelangebote oben werden redaktionell geprüft. Für den vollständigen, laufend wechselnden Markt führen diese Links direkt zu den Original-Suchen der Bundesagentur für Arbeit.</p>${[
        ['Merzenich','https://www.arbeitsagentur.de/jobsuche/suche?wo=Merzenich%2C+Kreis+D%C3%BCren'],['Düren','https://www.arbeitsagentur.de/jobsuche/suche?wo=D%C3%BCren%2C+Rheinland'],['Niederzier','https://www.arbeitsagentur.de/jobsuche/suche?wo=Niederzier'],['Nörvenich','https://www.arbeitsagentur.de/jobsuche/suche?wo=N%C3%B6rvenich'],['Elsdorf','https://www.arbeitsagentur.de/jobsuche/suche?wo=Elsdorf%2C+Rheinland'],['Kerpen','https://www.arbeitsagentur.de/jobsuche/suche?wo=Kerpen%2C+Rheinland']
      ].map(([n,u])=>`<div class="ma-market-city"><strong>${n}</strong><div class="ma-market-links">${a(u,'Alle aktuellen Stellen öffnen')}</div></div>`).join('')}`;
    } else {
      const data=[
        ['Merzenich','merzenich-52399/ad08de2200'],['Düren','duren-52349/ad08de2192'],['Niederzier','niederzier-52382/ad08de2202'],['Nörvenich','norvenich-52388/ad08de2203'],['Elsdorf','elsdorf-50189/ad08de2209'],['Kerpen','kerpen-50169/ad08de2213']
      ];
      sec.innerHTML=`<h2>Immobilienmarkt deutlich breiter durchsuchen</h2><p>Zusätzlich zu den redaktionell ausgewählten Einzelangeboten: Kauf- und Mietmärkte je Ort direkt beim Anbieter. So bleiben auch neue oder kurzfristig entfernte Inserate korrekt.</p>${data.map(([n,s])=>`<div class="ma-market-city"><strong>${n}</strong><div class="ma-market-links">${a('https://www.immowelt.de/suche/kaufen/immobilien/nordrhein-westfalen/'+s,'Kaufen')} ${a('https://www.immowelt.de/suche/mieten/immobilien/nordrhein-westfalen/'+s,'Mieten')}</div></div>`).join('')}`;
    }
    list.append(sec);
  }

  function family(){
    const prose=q('.info-prose'); if(!prose) return;
    prose.innerHTML=`<p class="ma-refresh-note"><strong>Neu gebündelt:</strong> öffentlich von der Gemeinde Merzenich veröffentlichte Familienmeldungen. Das sind redaktionelle Hinweise, keine als privat aufgegebenen Anzeigen.</p><div class="ma-family-list">
      <article class="ma-family-item"><span class="eyebrow">Hochzeit · veröffentlicht 08.09.</span><h2>Bettina zur Oven-Krockhaus und Jürgen Zeyen</h2><p>Das Paar aus Merzenich heiratete am 18. Juli 2026 in der Alten Kirche.</p><p class="ma-source">Quelle: ${a('https://www.heimat-info.de/beitraege/f297ede9-5841-4f18-9225-f34e8f3871db','Gemeinde Merzenich / Heimat-Info')}</p></article>
      <article class="ma-family-item"><span class="eyebrow">Hochzeit · veröffentlicht 01.09.</span><h2>Michaela Bauer und Karl-Heinz Mai</h2><p>Das Paar aus Merzenich gab sich am 21. August 2026 in der Alten Kirche das Ja-Wort.</p><p class="ma-source">Quelle: ${a('https://www.heimat-info.de/beitraege/89169b8b-441a-4af3-bcdf-c164b8bbeee8','Gemeinde Merzenich / Heimat-Info')}</p></article>
      <article class="ma-family-item"><span class="eyebrow">Hochzeit · veröffentlicht 04.09.</span><h2>Lara Sommerfeld und Patrick Brings</h2><p>Das Paar aus Kreuzau heiratete am 26. Juni 2026 in der Alten Kirche Merzenich.</p><p class="ma-source">Quelle: ${a('https://www.heimat-info.de/beitraege/046b4b45-85d8-4d69-a447-f31a6686243a','Gemeinde Merzenich / Heimat-Info')}</p></article>
      <article class="ma-family-item"><span class="eyebrow">Hochzeit · veröffentlicht 10.08.</span><h2>Lisa-Marie Hölzer und Simon Weimbs</h2><p>Das Paar aus Düren heiratete am 26. Juni 2026 in Merzenich.</p><p class="ma-source">Quelle: ${a('https://www.heimat-info.de/beitraege/d1d2cd37-39dd-4c55-be6f-4391a6b7a4a8','Gemeinde Merzenich / Heimat-Info')}</p></article>
    </div><p><a href="/kontakt/">Familienanzeige bei Merzenich Aktuell aufgeben</a></p>`;
  }

  function trauer(){
    const prose=q('.info-prose'); if(!prose) return;
    prose.innerHTML=`<p class="ma-refresh-note"><strong>Trauerportal:</strong> Fremde Traueranzeigen werden aus Urheber- und Persönlichkeitsgründen nicht als Bild oder vollständiger Anzeigentext kopiert. Wir verlinken stattdessen die regionalen Originalquellen und übernehmen nur eindeutig verifizierbare Basisdaten.</p><div class="ma-trauer-list">
      <article class="ma-trauer-item"><span class="eyebrow">Region Merzenich</span><h2>Trauerfälle bei Aachen gedenkt</h2><p>Die regionale Suche enthält Trauerfälle, die mit Merzenich verknüpft sind.</p><p class="ma-source">${a('https://www.aachen-gedenkt.de/traueranzeigen-suche/ringstra%C3%9Fe-7%2C-52399-merzenich','Originalsuche öffnen')}</p></article>
      <article class="ma-trauer-item"><span class="eyebrow">Suchbegriff Merzenich</span><h2>Traueranzeigen bei WirTrauern</h2><p>Die Suche kann sowohl Ortsbezüge als auch Personen mit dem Nachnamen Merzenich enthalten; deshalb werden Treffer nicht automatisch als lokale Fälle ausgegeben.</p><p class="ma-source">${a('https://www.wirtrauern.de/traueranzeigen-suche/merzenich','Originalsuche öffnen')}</p></article>
      <article class="ma-trauer-item"><span class="eyebrow">Direkter Umkreis · Düren</span><h2>Regionale Traueranzeigen</h2><p>Für den Umkreis verlinken wir zusätzlich die Dürener Suche beim Originalportal.</p><p class="ma-source">${a('https://www.wirtrauern.de/traueranzeigen-suche/d%C3%BCren','Düren bei WirTrauern')}</p></article>
    </div><p><a href="/kontakt/">Traueranzeige bei Merzenich Aktuell aufgeben</a></p>`;
  }

  /* Stillgelegt: jeder dieser Einstiege ersetzt oder ergaenzt Markup nach dem Laden.
   * Die Startseite kommt aus deploy/inhaltsindex.mjs, die Terminliste aus
   * deploy/termine-prerender.mjs, die Marktseiten aus deploy/markt-prerender.mjs.
   * Familien- und Traueranzeigen haben bisher keinen Generator; ihr Inhalt steht
   * oben in family() und trauer() und muss in den Redaktionsbestand uebernommen
   * werden, bevor die Seiten ihn zeigen koennen. */
  if(LAUFZEIT_UMBAU){
    if(path==='/') refreshHome();
    if(path==='/jobs') marketMore('jobs');
    if(path==='/immobilien') marketMore('immobilien');
    if(path==='/familienanzeigen') family();
    if(path==='/traueranzeigen') trauer();
  }
})();
