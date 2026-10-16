#!/usr/bin/env node
/**
 * Merzenich Aktuell — echte Foto-Pools für Editorial Image System V2.
 *
 * Quelle: ausschließlich Wikimedia Commons (freie Lizenzen, kommerzielle
 * Nutzung und Bearbeitung zulässig). Zuerst werden Merzenich/Kreis Düren/NRW
 * gesucht, danach erst allgemeine deutsche Motive.
 *
 * Ziel: je Haupt- und Detailpool 20 unterschiedliche und thematisch passende Fotos.
 * Die Original-Dateiseite, Lizenz, Urheber und Download-URL bleiben im
 * Manifest nachvollziehbar. Für die Website wird eine max. 1600px breite
 * Commons-Ableitung gespeichert, damit das Repository nicht mit mehreren
 * hundert Megabyte Originaldateien explodiert.
 */
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, unlinkSync, writeFileSync } from 'node:fs';
import { dirname, extname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const SITE = join(ROOT, 'chatgpt-site');
const ASSET_ROOT = join(SITE, 'assets', 'editorial-pools');
const MANIFEST = join(SITE, 'data', 'editorial-images', 'editorial-photo-pools.json');
// Ergebnis der Sichtprüfung: ausgeschlossene Commons-Titel (mit Grund) und
// freigegebene Titel. Ausgeschlossenes wird gelöscht und nie wieder geladen.
const PRUEFUNG = join(ROOT, 'deploy', 'editorial-photo-review.json');
// Diese Pools haben keine gezeichneten Reservemotive und brauchen volle 20 Fotos.
// Die übrigen Pools fallen auf die V2-Symbolgrafiken zurück, dort ist eine
// kleinere, dafür geprüfte Fotoauswahl besser als aufgefüllter Ausschuss.
const NUR_FOTO = new Set(['aktuell', 'blaulicht', 'brand', 'termine', 'tipp', 'menschen']);
const SERIE_MAX = 3; // Detailpools erlauben hoechstens zwei Motive derselben Fotoreihe.
const PRO_POOL = 20;
const REFRESH = process.argv.includes('--refresh');
const HEUTE = new Date().toISOString().slice(0, 10);
const UA = 'MerzenichAktuell-EditorialImageImporter/2.2 (https://merzenich-aktuell.de)';

const POOLS = {

  // Hauptressorts mit eigenen Fotopools. Die vorhandenen 20 SVGs bleiben
  // als gepruefte, neutrale Reserve erhalten. Fremde Orte nie als Merzenich
  // ausgeben; Fotos sind erkennbar als Symbolbilder gekennzeichnet.
  gemeinde: {
    tags: ['gemeinde','rathaus','verwaltung','kommunalpolitik','rat'],
    max: 20,
    pflicht: /(rathaus|gemeindeverwaltung|bürgerbüro|buergerbuero|ratssaal|sitzungssaal|gemeinderat|verwaltungsg[ea]b[aä]ude)/i,
    queries: ['Merzenich Rathaus','Rathaus Kreis Düren','Rathaus NRW','Gemeindeverwaltung Nordrhein-Westfalen','Ratssaal Nordrhein-Westfalen','Sitzungssaal Rathaus','Bürgerbüro Nordrhein-Westfalen','Rathaus Eingang Deutschland','Gemeinderatssaal Deutschland','Kommunalverwaltung Rathaus','Rathaus Düren','Rathaus Innenansicht','Rathaus Sitzungsraum','Gemeinderat Rathaus']
  },
  veranstaltungen: {
    tags: ['veranstaltungen','dorfveranstaltung','fest','konzert','brauchtum'],
    max: 20,
    pflicht: /(dorf.?fest|stadtfest|kirmes|weihnachtsmarkt|adventsmarkt|sch[uü]tzenfest|konzertb[uü]hne|festplatz|martinszug|festzelt|flohmarkt)/i,
    queries: ['Dorffest Nordrhein-Westfalen','Stadtfest Nordrhein-Westfalen','Festplatz Nordrhein-Westfalen','Schützenfest Nordrhein-Westfalen','Martinszug NRW','Kirmes Düren','Weihnachtsmarkt Kreis Düren','Adventsmarkt Rheinland','Volksfest Nordrhein-Westfalen','Festzelt Deutschland','Konzertbühne Dorf','Weihnachtsmarkt Deutschland','Flohmarkt NRW','Schützenfest Zelt']
  },
  jobs: {
    tags: ['jobs','arbeit','ausbildung','stellenmarkt','beruf'],
    max: 20,
    pflicht: /(arbeitsplatz|werkbank|b[uü]roarbeitsplatz|werkstatt|ausbildung|schreibtisch|bewerbung|arbeitsplatzrechner|handwerk|produktionshalle|workplace|office desk)/i,
    queries: ['Arbeitsplatz Werkbank Deutschland','Büroarbeitsplatz Deutschland','Werkstatt Arbeitsplatz Nordrhein-Westfalen','Schreibtisch Arbeitsplatz','Ausbildung Werkstatt','Job Bewerbung Schreibtisch','Office desk Germany','Production workshop NRW','Ausbildung Handwerk','Leerer Arbeitsplatz Deutschland','Schreibtisch Computer Arbeit','Werkbank Werkzeug','Stellenmarkt Bewerbung Unterlagen','Büro Arbeitsplätze']
  },
  immobilien: {
    tags: ['immobilien','wohnen','haus','miete','bau','architektur'],
    max: 20,
    // Nur neutrale Architektur-Details, nie eine fremde Immobilie als Angebotsfoto.
    pflicht: /(fenster|haust[uü]r|treppenhaus|fassadendetail|dachziegel|schl[uü]ssel|ziegelwand|window detail|staircase|building facade detail|front door)/i,
    queries: ['Haustür Detail Deutschland','Treppenhaus Nordrhein-Westfalen','Fenster Fassade Haus Deutschland','Ziegelwand Architektur','Dachziegel Gebäude','Haus Schlüssel neutral','Staircase Germany building','Wohnhaus Fenster Detail','Klinker Fassade Detail','Treppe Haus Innenraum','Bau Architektur Detail Deutschland','Hausfassade Fenster NRW','Eingangstür Mehrfamilienhaus','Fensterrahmen Wohngebäude']
  },
  familie: {
    tags: ['familie','kinder','alltag','gemeinschaft','freizeit'],
    max: 20,
    // Keine fremden Kinder-/Familienportraets als lokales Ereignisbild.
    pflicht: /(spielplatz|kinderwagen|kinderspielplatz|spielger[aä]t|rutsche|schaukel|familienzentrum|playground|pram|stroller)/i,
    queries: ['Spielplatz Nordrhein-Westfalen','Spielplatz Kreis Düren','Spielgeräte Spielplatz','Kinderspielplatz Deutschland','Spielplatz Rutsche','Kinderwagen im Park ohne Menschen','Playground Germany','Spielplatz Schaukel','Familienzentrum NRW Außen','Spielplatz Klettergerüst','Kinderspielplatz Natur','Öffentlicher Spielplatz Düren','Spielplatz Park NRW','Stroller Park Germany']
  },
  trauer: {
    tags: ['trauer','gedenken','abschied','friedhof','blumen'],
    max: 20,
    pflicht: /(trauerkerze|gedenkkerze|grablicht|friedhof|grabstein|blumenstrauß|blumenstrauss|kerze|memorial candle|cemetery|grave light)/i,
    queries: ['Trauerkerze Gedenken','Grablicht Deutschland','Friedhof Kerzen','Gedenkkerze Deutschland','Friedhof Blumen','Grabstein Friedhof Deutschland','Kerze Stilleben','Friedhof Nordrhein-Westfalen','Blumenstrauß Gedenken','Friedhof Weg Deutschland','Memorial candle Germany','Kerzen Gedenken Kirche','Grablicht Friedhof','Friedhof Herbst NRW']
  },
  kultur: {
    tags: ['kultur','kunst','musik','theater','literatur'],
    max: 20,
    pflicht: /(theaterb[uü]hne|b[uü]hnenraum|b[uü]hnentechnik|klavier|konzertsaal|bibliothek|lesesaal|musikinstrument|stage curtain|theatre stage|piano|library)/i,
    queries: ['Theaterbühne Deutschland leer','Bühnenraum Theater','Konzertsaal Nordrhein-Westfalen','Klavier Kulturzentrum','Bibliothek Lesesaal NRW','Musikinstrumente Konzertsaal','Bühnentechnik Deutschland','Theatre stage Germany','Piano on stage Germany','Theatervorhang','Theater Bühne Beleuchtung','Bibliothek Bücherregal Deutschland','Musiksaal Deutschland','Kulturhaus Bühne NRW']
  },
  schule: {
    tags: ['schule','bildung','unterricht','klassenraum','lernen'],
    max: 20,
    // Neutrale Klassenzimmer und Lernmaterialien ohne fremde Schulgebäude.
    pflicht: /(klassenraum|klassenzimmer|schulbank|schultafel|schulheft|schulb[uü]cher|stifte|classroom|blackboard|chalkboard|school desk)/i,
    queries: ['Klassenzimmer Nordrhein-Westfalen leer','Klassenraum Deutschland','Schultafel Klassenzimmer','Schulbank Deutschland','Schulheft Stifte','Schulbücher Lernmaterial','Classroom Germany empty','Blackboard classroom Germany','Klassenzimmer Grundschule','Schultafel Unterricht','Schulhefte Bücher','Schulbank leer','Schreibtafel Klassenraum','Unterrichtsraum NRW']
  },
  kirche: {
    tags: ['kirche','gottesdienst','gemeinde','pfarrleben','kirchenraum'],
    max: 20,
    pflicht: /(kircheninneres|kirchenraum|kirchenschiff|kirchenbank|altarraum|kirchenfenster|orgel|church interior|church pew|stained glass)/i,
    queries: ['Kircheninneres Nordrhein-Westfalen','Kirchenbänke Kirche','Altarraum Kirche NRW','Kirchenschiff Nordrhein-Westfalen','Kirchenraum Deutschland','Kirchenfenster Kreis Düren','Kirchenorgel Deutschland','Church interior NRW','Church pew Germany','Kirchenfenster Nordrhein-Westfalen','Kirchenraum evangelisch NRW','St Laurentius Merzenich Kirche Innen','Kirchenschiff leer Deutschland','Orgel Kirche Nordrhein-Westfalen']
  },
  aktuell: {
    tags: ['aktuell','merzenich','dueren','ort','strasse','gemeinde'],
    // Runde 20 (07.10.2026): zwei belegte Ansichten von St. Laurentius Merzenich (Kilind).
    max: 22,
    titel: ['File:St. Laurentius Merzenich.jpg', 'File:St. Laurentius Merzenich Hauptportal.JPG'],
    queries: ['Merzenich Kreis Düren', 'Merzenich Denkmal', 'Golzheim Merzenich', 'Girbelsrath', 'Morschenich', 'Merzenich Rathaus', 'Kreis Düren Dorf']
  },
  blaulicht: {
    tags: ['blaulicht','einsatz','rettung','notfall'],
    queries: ['deepcat:"Ambulances in North Rhine-Westphalia"', 'deepcat:"Rescue stations in North Rhine-Westphalia"', 'Rettungshubschrauber Christoph', 'Notarzt Nordrhein-Westfalen', 'Rettungswache Kreis Düren', 'Rettungswache Köln', 'Rettungswagen Seitenansicht', 'Notarztwagen', 'deepcat:"Emergency medical services in North Rhine-Westphalia"', 'deepcat:"Ambulances in Germany"', 'Rettungswache Nordrhein-Westfalen', 'Rettungswagen Nordrhein-Westfalen', 'Rettungshubschrauber Nordrhein-Westfalen', 'Rettungsdienst Kreis Düren', 'Notarzteinsatzfahrzeug Deutschland']
  },
  polizei: {
    tags: ['polizei','streifenwagen','einsatz','kontrolle'],
    // Motivregeln (24.09.): Streifenwagen zuerst; Wachen lesen sich oft als Wohnhaus.
    queries: ['Funkstreifenwagen Nordrhein-Westfalen', 'Streifenwagen Polizei Nordrhein-Westfalen', 'Polizeifahrzeug Nordrhein-Westfalen', 'Polizeiwache Nordrhein-Westfalen', 'Polizeipräsidium Nordrhein-Westfalen', 'Polizeihubschrauber Nordrhein-Westfalen', 'Polizei Düren', 'Polizei Nordrhein-Westfalen Streifenwagen', 'Polizei Nordrhein-Westfalen']
  },
  feuerwehr: {
    tags: ['feuerwehr','loeschfahrzeug','einsatz','rettung'],
    queries: ['Feuerwehrgerätehaus Kreis Düren', 'Feuerwehr Düren', 'Feuerwehrgerätehaus Nordrhein-Westfalen', 'Feuerwehr Nordrhein-Westfalen', 'Feuerwehr Übung Nordrhein-Westfalen']
  },
  brand: {
    tags: ['brand','feuer','rauch','loeschen'],
    // Deutsche Fachwoerter stehen in den Dateititeln deutscher Fotografen;
    // die Kategoriesuche ueber "Fires" lief ueber Flammen zu Kerzen.
    queries: ['Waldbrand Hürtgenwald', 'Brand Kreis Düren', 'Feuerwehr Düren Brand', 'Dachstuhlbrand', 'Scheunenbrand', 'Wohnhausbrand', 'Großbrand Feuerwehr', 'Wohnungsbrand', 'Lagerhallenbrand', 'Kellerbrand', 'Fahrzeugbrand', 'PKW-Brand', 'Flächenbrand Feuerwehr', 'Waldbrand Nordrhein-Westfalen', 'Brandeinsatz', 'Löscharbeiten', 'Feuerwehreinsatz Brand', 'Brandruine']
  },
  sport: {
    tags: ['sport','fussball','amateur','spiel','platz'],
    queries: ['deepcat:"Football venues in North Rhine-Westphalia"', 'deepcat:"Association football pitches in Germany"', 'Fußballplatz Nordrhein-Westfalen', 'Kunstrasenplatz', 'Sporthalle Nordrhein-Westfalen', 'Tennisanlage Nordrhein-Westfalen']
  },
  tennisdetail: {
    tags: ['sport','tennis','tennisplatz','tennistraining'],
    max: 20,
    pflicht: /tennis/i,
    queries: ['Tennisplätze Nordrhein-Westfalen','Tennisanlage Nordrhein-Westfalen','Tennisclub Nordrhein-Westfalen','Tennisplatz Sandplatz Deutschland','Tennis court NRW','Tennishalle Nordrhein-Westfalen','Tennis Training Deutschland','Tennisnetz Platz','Tennisverein Deutschland','Tennisplätze Kreis Düren','Tennisplatz Freiluft Deutschland','Tenniscourt Germany','Tennis doubles court Germany','Tennis Sportanlage Düren']
  },
  digitaldetail: {
    tags: ['digital','smartphone','senioren','bildung','tablet','medienkompetenz'],
    max: 20,
    pflicht: /(senior.*smartphone|smartphone.*senior|seniorenhandy|mobiltelefon|smartphone|tablet computer|tablet device|handy senior|phone for seniors|xl-viewer)/i,
    queries: ['Senioren Smartphone Kurs','Senior smartphone XL-Viewer 5000','Smartphone Senioren Deutschland','Smartphone Bedienung','Android Smartphone Germany','Doro phone for seniors','Senioren Tablet','Tablet Computer Hand','Handy für Senioren','Digitalisierung Smartphone Schule','Smartphone auf Tisch','Smartphone Bildschirm Android','Tablet Computer Germany','Medienkompetenz Handy']
  },
  tanzdetail: {
    tags: ['tanz','vortanzen','tanzsaal','tanzverein','tanzstudio'],
    max: 20,
    pflicht: /(tanzhaus|tanzsaal|tanzstudio|tanzschule|tanzraum|dance hall|dance studio|dance floor|ballettsaal|ballet studio)/i,
    queries: ['Tanzhaus nrw Großer Saal Foto2 Katja Illner','Tanzsaal Nordrhein-Westfalen','Tanzstudio Deutschland','Tanzraum Deutschland','Tanzschule Saal NRW','Tanzproberaum','Dance studio Germany','Dance hall Germany','Ballettsaal Germany','Tanzparkett Tanzsaal','Tanzhaus Düsseldorf','Uetzing Tanzsaal','Tanzhalle','Tanzsport Verein Trainingssaal']
  },
  naturdetail: {
    tags: ['natur','nistkasten','voegel','vogelschutz','naturschutz'],
    max: 20,
    pflicht: /(nistk[aä]ste?n|nest.?box|bird.?house|bird nest box|vogelkasten|brutkasten|vogelhaus)/i,
    queries: ['Nistkasten mit Marderschutz','Nistkasten Holz Deutschland','Nistkasten Baum Deutschland','Nistkasten für Meisen','Nistkasten im Garten','Nistkasten Montage','Nistkasten Vogelschutz','Meisenkasten Deutschland','Bird nest box Germany','Nestbox trees Germany','Vogelhaus Brutkasten','Nistkasten bauen','Nistkasten Naturpark Nordrhein-Westfalen']
  },
  vereinsdetail: {
    tags: ['verein','vereinsheim','sportlerheim','gemeinschaftshaus','ehrenamt'],
    max: 20,
    pflicht: /(vereinsheim|clubhouse|club house|sch[uü]tzenheim|sportlerheim|gemeinschaftshaus|dorfgemeinschaftshaus|clubheim)/i,
    queries: ['Vereinsheim Nordrhein-Westfalen','Sportlerheim Nordrhein-Westfalen','Schützenheim Nordrhein-Westfalen','Dorfgemeinschaftshaus Kreis Düren','Gemeinschaftshaus Nordrhein-Westfalen','Clubhouse Germany sports','Vereinsheim Sportverein','Kleingarten Vereinsheim NRW','Vereinsheim des KGV Knipprather Busch 1969 eV In der Loheck','Schützenheim Deutschland','Sportlerheim Außenansicht','Vereinsheim Deutschland','Dorfgemeinschaftshaus Gemeinde','Vereinsheim Fußball NRW']
  },
  kirchedetail: {
    tags: ['kirche','gottesdienst','kirchenfenster','bleiglas','sakral'],
    max: 20,
    pflicht: /(kirchenfenster|church window|stained.?glass|glasmalerei|bleiglasfenster|kirchen.?glasfenster)/i,
    queries: ['Kirchenfenster Der Herr ist mein Hirte','Kirchenfenster Nordrhein-Westfalen','Kirchenfenster Deutschland','Bleiglasfenster Kirche NRW','Kirchenfenster St Laurentius Merzenich','Glasmalerei Kirchenfenster Deutschland','Church stained glass Germany','Stained glass NRW church','Kirchenfenster Kirche Düren','Kirchenfenster Christuskirche','Kirchenfenster Heiliger Geist','Stained glass window church Germany','Kirchenfenster Altarkirche','Kirchenfenster evangelische Kirche NRW']
  },
  termine: {
    tags: ['termine','veranstaltung','event','fest','markt'],
    queries: ['Dorffest Nordrhein-Westfalen', 'Kirmes Nordrhein-Westfalen', 'Weihnachtsmarkt Nordrhein-Westfalen', 'Schützenfest Nordrhein-Westfalen', 'Martinsfeuer Nordrhein-Westfalen', 'Veranstaltung Deutschland']
  },
  vereine: {
    tags: ['vereine','verein','ehrenamt','gemeinschaft'],
    // Stadion fuer Fanclub-Meldungen (Motivregel fanclub).
    queries: ['RheinEnergieStadion', 'Müngersdorfer Stadion', 'Vereinsheim Nordrhein-Westfalen', 'Schützenhalle Nordrhein-Westfalen', 'Dorfgemeinschaftshaus Nordrhein-Westfalen', 'Maibaum Nordrhein-Westfalen', 'Karnevalswagen Nordrhein-Westfalen', 'Bücherschrank Nordrhein-Westfalen']
  },
  leben: {
    tags: ['leben','dorfleben','alltag','nachbarschaft','familie'],
    // Runde 20: Herbstlaub fuer Laubannahme und Herbstthemen, kein Ortsfoto.
    max: 21,
    titel: ['File:Herbstlaub auf einer Bank.jpg'],
    queries: ['Dorfplatz Nordrhein-Westfalen', 'Dorfstraße Kreis Düren', 'Kreis Düren Dorf', 'Spielplatz Nordrhein-Westfalen', 'Wochenmarkt Nordrhein-Westfalen', 'Park Düren', 'Dorfleben Nordrhein-Westfalen']
  },
  wirtschaft: {
    tags: ['wirtschaft','handel','strukturwandel','gewerbe','handwerk','industrie'],
    queries: ['Tagebau Hambach', 'Wirtschaft Düren', 'Gewerbegebiet Kreis Düren', 'Handwerk Nordrhein-Westfalen', 'Einzelhandel Deutschland', 'Industrie Nordrhein-Westfalen']
  },
  // Editorial Image System V3: Ereignis-Pools. Pflichtwort im Dateititel, sonst
  // kommt Beifang; Sichtung und Motiv (deploy/bildmotive.json) wie bei allen.
  technik: {
    tags: ['feuerwehr','technische-hilfe','einsatz','oelspur','brandmeldeanlage'],
    max: 24,
    pflicht: /(ölspur|oelspur|ölbinde|oelbinde|bindemittel|ölsperre|hydrauliköl|brandmelde|brandmelder|rauchmelder|rauchwarnmelder|smoke detector|fire alarm|oil spill)/i,
    queries: ['Ölspur Feuerwehr Nordrhein-Westfalen','Ölbindemittel Feuerwehr','Ölspur Straße','Ölspur Beseitigung','Ölsperre Feuerwehr','Oil spill fire brigade Germany','Bindemittel Feuerwehr','Brandmeldeanlage Feuerwehr','Brandmeldezentrale','Brandmelder','Handfeuermelder','Rauchmelder','Rauchwarnmelder','Smoke detector ceiling Germany','Fire alarm button Germany','Rauchwarnmelder an Decke']
  },
  rettung: {
    tags: ['feuerwehr','rettung','tier','tuer','technische-hilfe'],
    max: 20,
    pflicht: /(tierrettung|tier in not|tierfang|katze.*(baum|feuerwehr)|türöffnung|tueroeffnung|wohnungsöffnung|haustüröffnung|animal rescue|cat rescue|feuerwehr.*tier)/i,
    queries: ['Tierrettung Feuerwehr Nordrhein-Westfalen','Tierrettung Feuerwehr','Tierrettung Katze Feuerwehr','Katze auf Baum Feuerwehr','Tierfang Feuerwehr','Katze Drehleiter Feuerwehr','Animal rescue German fire brigade','Cat rescue fire brigade','Türöffnung Feuerwehr Nordrhein-Westfalen','Türöffnung Feuerwehr','Wohnungsöffnung Feuerwehr','Türöffnungswerkzeug Feuerwehr','Haustüröffnung Feuerwehr','Firefighters animal rescue Germany','Feuerwehr Türrettung']
  },
  unfall: {
    tags: ['verkehr','unfall','einsatz','absperrung','unfallstelle'],
    max: 20,
    pflicht: /(verkehrsunfall|unfallstelle|unfall|absperrung|fußgängerbrücke|fussgaengerbruecke|fußgängerüberführung|footbridge|traffic accident|crash scene)/i,
    queries: ['Verkehrsunfall Feuerwehr Nordrhein-Westfalen','Verkehrsunfall Kreis Düren','Unfallstelle Nordrhein-Westfalen','Verkehrsunfall Absicherung','Unfallabsperrung Feuerwehr','Unfallstelle Polizei Nordrhein-Westfalen','Straßenunfall Absperrung','Verkehrsunfall Landstraße Deutschland','Fahrzeugunfall Nordrhein-Westfalen','Fußgängerbrücke Landstraße','Fußgängerbrücke Nordrhein-Westfalen','Fußgängerüberführung Straße','Traffic accident Germany fire brigade','Verkehrsunfall Sperrung']
  },
  flaeche: {
    tags: ['brand','feld','flaeche','vegetationsbrand','feuerwehr'],
    max: 20,
    pflicht: /(feldbrand|flächenbrand|flaechenbrand|stoppelfeld|böschungsbrand|boeschungsbrand|strohballenbrand|vegetationsbrand|grasbrand|mülleimerbrand|muelleimerbrand|containerbrand|brush fire|grass fire)/i,
    queries: ['Feldbrand Nordrhein-Westfalen','Flächenbrand Kreis Düren','Stoppelfeldbrand Feuerwehr','Böschungsbrand NRW','Böschungsbrand','Strohballenbrand Deutschland','Vegetationsbrand Feuerwehr Nordrhein-Westfalen','Grasbrand Deutschland','Flächenbrand Feuerwehr','Feldbrand Feuerwehr','Mülleimerbrand Feuerwehr','Containerbrand Feuerwehr','Böschungsbrand Straße','Grass fire Germany','Vegetationsbrand Ausbildung']
  },
  verkehr: {
    // Motivregeln geschwindigkeit und fahrrad-reparatur (24.09.): Messanlagen
    // und Schilder ohne Kennzeichen, Reparaturstationen fuer Fahrraeder.
    tags: ['verkehr','geschwindigkeit','blitzer','fahrrad','reparaturstation','strasse'],
    // Runde 20: 16 geprüfte Fotos reichen; jeder Nachlauf brachte nur weitere
    // Reparaturstationen aus dem Ausland oder mit Aufdruck.
    max: 20,
    queries: ['Starenkasten', 'Blitzer Nordrhein-Westfalen', 'Geschwindigkeitsmessanlage', 'Geschwindigkeitsüberwachung Deutschland', 'Radarfalle Deutschland', 'Fahrradreparaturstation', 'Fahrrad-Reparaturstation', 'Fahrradservicestation', 'bicycle repair station', 'bike repair station Germany', 'Fahrrad Reparatursäule', 'Radservicestation']
  },
  tipp: {
    tags: ['tipp','freizeit','ausflug','wandern','radfahren','natur'],
    max: 21,
    titel: ['File:Herbstlaub im Naturschutzgebiet Wahner Heide.jpg'],
    queries: ['Sophienhöhe', 'Wandern Kreis Düren', 'Radweg Kreis Düren', 'Rurtalsperre', 'Naturschutzgebiet Kreis Düren', 'Rur Kreis Düren', 'Radweg Nordrhein-Westfalen']
  },
  menschen: {
    // Keine Porträts: erkennbare Personen sind als Symbolbild unzulässig.
    tags: ['menschen','portraet','community','senioren','handwerk'],
    queries: ['Parkbank Nordrhein-Westfalen', 'Bürgerhaus Nordrhein-Westfalen', 'Dorfgemeinschaftshaus Kreis Düren', 'Gemeinschaftsgarten Deutschland', 'Seniorenheim Nordrhein-Westfalen', 'Nachbarschaftshilfe', 'Werkstatt Handwerk Deutschland']
  }
};

const BAD_TITLE = /(logo|wappen|coat of arms|flag|karte|map of|locator|diagram|poster|plakat|flyer|icon|scan|seite \d|page \d|screenshot|symbol|svg)/i;
// Strengere Filter nach der Sichtprüfung vom 24.09.2026.
const BAD_TITLE_STRENG = /(satellit|sentinel|modis|copernicus|nasa|landsat|viirs|olci|chart|diagramm|statistik|mitglieder|karte|openstreetmap|abzeichen|patch|badge|kennz|kennzeichen|license plate|nummernschild|portr[aä]it|politik|minister|polizeipr[aä]sident|army|soldat|soldier|military|milit[aä]r|bundeswehr|painting|gem[aä]lde|museum|hdri|poly haven|render|demonstration|protest|kundgebung|pride|parade|b[aä]ckerei|bakery|fire suppression|halon|argonite|fm-200|l[oö]schanlage|model car|modellauto|dosimeter|sound level|zivilschutz|belohnung)/i;
const BAD_KATEGORIE = /(portrait|people of|politicians|athletes|players|footballers|musicians|actors|soldiers|military|united states army|demonstrations|protests|satellite|maps of|logos|coats of arms|patches|license plates|vehicle registration|paintings|diagrams|charts|crowds|children)/i;
const AUSLAND = /(croatia|kroatien|hrvatska|switzerland|schweiz|austria|österreich|california|united states|\busa\b|england|london|united kingdom|scotland|shetland|faroe|venezuela|new zealand|afghanistan|poland|polska|netherlands|niederlande|belgium|belgien|france|frankreich|manchester|austria|japan|tokyo|osaka|italy|italia|italien|canada|kanada|china|korea|arizona|oregon|nevada|montana|colorado|utah|idaho|new mexico|texas|florida|national forest|forest service|bush ?fire|sweden|schweden|norway|norwegen|finland|denmark|d[aä]nemark|nyn[aä]shamn|israel|kibbutz|gaza|ukrain|estonia|eesti|tartu|latvia|lithuania|indonesia|philippin|mexico|colombia|brazil|india\b|steiermark|styria|mureck|russia|russland|moscow|moskau|m[aá]laga|spain|spanien|espa[nñ]a|hungary|ungarn|moh[aá]cs|massacre|massaker|anschlag)/i;
// Titel in nicht-lateinischer Schrift stammen fast immer aus dem Ausland.
const FREMDE_SCHRIFT = /[\u0370-\u03ff\u0400-\u04ff\u0590-\u06ff\u3040-\u30ff\u3400-\u9fff\uac00-\ud7af]/;
// Sportplatz-Suchen treffen Wegekreuze "hinter dem Sportplatz".
const SPORT_FREMD = /(bildstock|wegekreuz|kreuz|kapelle|denkmal|gedenk|heiligenh)/i;
const BRAND_FREMD = /(kerze|candle|kapelle|kirche|church|dom\b|grabst|tabernakel|advent|osterfeuer|lagerfeuer|campfire|grill)/i;
// Deutsche Brandfotos tragen deutsche Titel; "Fire in ..." war stets Ausland.
// Jahreszahlen bis 1969 im Titel sind historische Aufnahmen.
// Kieler Stadtarchiv ("(Kiel 57.459)") und Schwarzweiss sind historische Aufnahmen.
const ARCHIV = /(\(kiel \d|stadtarchiv|black and white|schwarzwei)/i;
const BRAND_TITEL_FREMD = /(\bfire\b|incendi|incendie|\bbrann|\b1[89]\d\d\b(?<!\b19[7-9]\d)(?<!\b20\d\d))/i;
// Einsatzkräfte tragen je Bundesland eigene Farben und Wappen.
const ANDERES_LAND = /(baden-württemberg|baden-wuerttemberg|bayern|bavaria|hessen|hamburg|saarland|niedersachsen|berlin|sachsen|thüringen|brandenburg|rheinland-pfalz|schleswig|mecklenburg|bremen|heidelberg|karlsruhe|stuttgart|münchen|munich|fulda)/i;
const EINSATZ_POOLS = new Set(['polizei', 'blaulicht', 'feuerwehr', 'brand', 'technik', 'rettung', 'unfall', 'flaeche']);
// Namensgleichheit: Merzenich bei Zülpich und die Kölner Bäckerei „Merzenich“.
const FALSCHES_MERZENICH = /(z[uü]e?lpich|euskirchen|k[oö]ln|cologne|eigelstein|schildergasse)/i;

const VERKEHR_PFLICHT = /(blitzer|starenkasten|geschwindigkeitsmess|radarfalle|radarkontrolle|speed camera|traffic enforcement camera|fotoradar|fahrrad.?reparatur|reparaturstation|bicycle repair|bike repair|repair station|servicestation|fahrradservice)/i;
// Reparaturstationen mit Firmen- oder Clubaufdruck (Runde 18/19): Werbung im Bild.
const VERKEHR_FREMD = /(hornbach|bike ?broz|adac)/i;
const ALLOWED_MIME = new Set(['image/jpeg', 'image/png', 'image/webp']);

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const hash = (x) => createHash('sha256').update(x).digest('hex');
const stripHtml = (s = '') => String(s)
  .replace(/<br\s*\/?>/gi, ' ')
  .replace(/<[^>]+>/g, ' ')
  .replace(/&nbsp;/g, ' ')
  .replace(/&amp;/g, '&')
  .replace(/&quot;/g, '"')
  .replace(/&#039;|&apos;/g, "'")
  .replace(/\s+/g, ' ')
  .trim();

function licenceAllowed(name = '') {
  const s = String(name);
  if (/NC|ND/i.test(s)) return false;
  return /CC0|CC BY(?:-SA)?|Public domain|PDM|Public Domain/i.test(s);
}

function extensionFor(info) {
  if (info.mime === 'image/jpeg') return 'jpg';
  if (info.mime === 'image/png') return 'png';
  if (info.mime === 'image/webp') return 'webp';
  const e = extname(new URL(info.thumburl || info.url).pathname).toLowerCase().replace('.', '');
  return e === 'jpeg' ? 'jpg' : e;
}

function localityFor(text) {
  const s = String(text).toLocaleLowerCase('de-DE');
  if (FALSCHES_MERZENICH.test(s)) return /nordrhein-westfalen|north rhine-westphalia| nrw/.test(s) ? 'NRW' : 'Deutschland / überregional';
  if (s.includes('merzenich') || s.includes('golzheim') || s.includes('girbelsrath') || s.includes('morschenich') || s.includes('bürgewald')) return 'Merzenich';
  if (s.includes('düren') || s.includes('dueren')) return 'Kreis Düren';
  if (s.includes('nordrhein-westfalen') || s.includes('north rhine-westphalia') || s.includes(' nrw')) return 'NRW';
  return 'Deutschland / überregional';
}

async function commonsSearch(query) {
  return commonsAbfrage({ generator: 'search', gsrsearch: query, gsrnamespace: '6', gsrlimit: '50' }, query);
}

// Gezielt beschaffte Dateien (POOLS[pool].titel): genau diese Titel, in dieser
// Reihenfolge, vor den Suchbegriffen. Lizenz- und Motivfilter gelten wie immer.
async function commonsTitel(titel) {
  const liste = await commonsAbfrage({ titles: titel.join('|') }, titel.join(', '));
  return titel.map((t) => liste.find((x) => x.title === t)).filter(Boolean);
}

async function commonsAbfrage(auswahl, wofuer) {
  const params = new URLSearchParams({
    action: 'query',
    format: 'json',
    ...auswahl,
    prop: 'imageinfo',
    iiprop: 'url|mime|size|extmetadata',
    iiurlwidth: '1600'
  });
  const url = 'https://commons.wikimedia.org/w/api.php?' + params.toString();
  const res = await fetch(url, { headers: { 'User-Agent': UA, 'Accept': 'application/json' } });
  if (!res.ok) throw new Error('Commons ' + res.status + ' für ' + wofuer);
  const json = await res.json();
  return Object.values(json?.query?.pages || {}).filter((page) => page.imageinfo).map((page) => {
    const info = page.imageinfo?.[0];
    const meta = info?.extmetadata || {};
    return {
      pageid: page.pageid,
      title: page.title,
      info,
      license: stripHtml(meta.LicenseShortName?.value || ''),
      licenseUrl: meta.LicenseUrl?.value || '',
      artist: stripHtml(meta.Artist?.value || meta.Credit?.value || ''),
      description: stripHtml(meta.ImageDescription?.value || meta.ObjectName?.value || ''),
      categories: stripHtml(meta.Categories?.value || '')
    };
  });
}

const serie = (title = '') => String(title).replace(/^File:/, '').replace(/\.[^.]+$/, '')
  .toLocaleLowerCase('de-DE').replace(/[^a-zäöüß]+/g, ' ').trim().split(' ').slice(0, 3).join(' ');

function usable(c, pool) {
  if (!c?.info || !ALLOWED_MIME.has(c.info.mime)) return false;
  if (BAD_TITLE.test(c.title)) return false;
  if (AUSGESCHLOSSEN.has(c.title)) return false;
  const text = [c.title, c.description, c.categories].join(' ');
  if (BAD_TITLE_STRENG.test(c.title) || BAD_TITLE_STRENG.test(c.description)) return false;
  // Unfallsymbolbilder duerfen Unfall im Dateititel tragen; andere Pools nicht.
  if (pool !== 'unfall' && /(unfall|accident|crash)/i.test(c.title + ' ' + c.description)) return false;
  if (BAD_KATEGORIE.test(c.categories)) return false;
  if (AUSLAND.test(text) || FREMDE_SCHRIFT.test(c.title)) return false;
  if (pool === 'sport' && SPORT_FREMD.test(c.title)) return false;
  // Pool verkehr bedient nur die Motivregeln geschwindigkeit und fahrrad-reparatur;
  // ohne Pflichtwort im Titel kam Beifang wie 'GNT'-Ausstellungen und Zuege.
  if (pool === 'verkehr' && !VERKEHR_PFLICHT.test(c.title)) return false;
  if (pool === 'verkehr' && VERKEHR_FREMD.test(text)) return false;
  if (POOLS[pool].pflicht && !POOLS[pool].pflicht.test(c.title)) return false;
  // "Feldbrand" ist auch Strassen- und Nachname (Stolpersteine, Strasse Am Feldbrand).
  if (pool === 'flaeche' && /(stolperstein|am feldbrand|strassenbahnhaltestelle|hummel)/i.test(c.title)) return false;
  if (pool === 'brand' && (BRAND_FREMD.test(text) || BRAND_TITEL_FREMD.test(c.title) || ARCHIV.test(text))) return false;
  if (EINSATZ_POOLS.has(pool) && ANDERES_LAND.test(text)) return false;
  if (/merzenich/i.test(text) && FALSCHES_MERZENICH.test(text)) return false;
  if (pool === 'aktuell' && !['Merzenich', 'Kreis Düren'].includes(localityFor(text))) return false;
  if (!licenceAllowed(c.license)) return false;
  if ((c.info.width || 0) < 800 || (c.info.height || 0) < 600) return false;
  if (!c.info.thumburl && !c.info.url) return false;
  return true;
}

async function download(candidate, pool, index) {
  const url = candidate.info.thumburl || candidate.info.url;
  const res = await fetch(url, { headers: { 'User-Agent': UA } });
  if (!res.ok) throw new Error('image ' + res.status);
  const bytes = Buffer.from(await res.arrayBuffer());
  if (bytes.length < 10_000 || bytes.length > 18_000_000) throw new Error('unexpected image size ' + bytes.length);
  const ext = extensionFor(candidate.info);
  if (!['jpg','png','webp'].includes(ext)) throw new Error('unsupported extension ' + ext);
  const file = pool + '-' + String(index).padStart(2, '0') + '-' + hash(candidate.title).slice(0, 10) + '.' + ext;
  const dir = join(ASSET_ROOT, pool);
  mkdirSync(dir, { recursive: true });
  const abs = join(dir, file);
  writeFileSync(abs, bytes);

  const titleClean = candidate.title.replace(/^File:/, '').replace(/\.[^.]+$/, '').replace(/_/g, ' ');
  const alt = (candidate.description || titleClean).slice(0, 180);
  const artist = candidate.artist || 'Urheber siehe Quelldatei';
  const sourceUrl = 'https://commons.wikimedia.org/wiki/' + encodeURIComponent(candidate.title.replace(/ /g, '_'));
  const textForLocality = [candidate.title, candidate.description, candidate.categories].join(' ');
  const cfg = POOLS[pool];

  return {
    id: 'web-' + pool + '-' + String(index).padStart(2, '0') + '-' + hash(candidate.title).slice(0, 8),
    pool,
    src: '/assets/editorial-pools/' + pool + '/' + file,
    alt,
    credit: 'Symbolbild · ' + artist + ' / Wikimedia Commons · ' + candidate.license,
    source: 'Wikimedia Commons',
    sourceUrl,
    sourceTitle: candidate.title,
    downloadUrl: url,
    license: candidate.license,
    licenseUrl: candidate.licenseUrl,
    rightsCheckedAt: HEUTE,
    tags: [...new Set([...cfg.tags, ...titleClean.toLocaleLowerCase('de-DE').split(/[^a-zäöüß0-9]+/i).filter((x) => x.length >= 5).slice(0, 8)])],
    width: candidate.info.thumbwidth || candidate.info.width,
    height: candidate.info.thumbheight || candidate.info.height,
    format: ext,
    symbol: true,
    photo: true,
    locality: localityFor(textForLocality),
    geprueft: FREIGEGEBEN.has(candidate.title),
    checksum: hash(bytes)
  };
}

function loadExisting() {
  if (!existsSync(MANIFEST)) return { images: [] };
  try {
    const x = JSON.parse(readFileSync(MANIFEST, 'utf8'));
    return { images: Array.isArray(x.images) ? x.images : [] };
  } catch {
    return { images: [] };
  }
}

function pruefungLesen() {
  if (!existsSync(PRUEFUNG)) return { ausgeschlossen: [], freigegeben: [] };
  const x = JSON.parse(readFileSync(PRUEFUNG, 'utf8'));
  return { ausgeschlossen: x.ausgeschlossen || [], freigegeben: x.freigegeben || [] };
}

const pruefung = pruefungLesen();
const AUSGESCHLOSSEN = new Map(pruefung.ausgeschlossen.map((x) => [x.titel, x.grund]));
const FREIGEGEBEN = new Set(pruefung.freigegeben);
const dateiDa = (x) => existsSync(join(SITE, String(x.src || '').replace(/^\//, '')));

const existing = loadExisting();
const finalImages = [];
const usedTitles = new Set();
// Titel und Pruefsummen aller behaltenen Fotos vorab, ueber alle Pools: sonst
// laedt ein frueher bearbeiteter Pool dieselbe Datei, die ein spaeterer behaelt.
const behalten = REFRESH ? [] : existing.images.filter((x) => !AUSGESCHLOSSEN.has(x.sourceTitle) && dateiDa(x));
behalten.forEach((x) => x.sourceTitle && usedTitles.add(x.sourceTitle));
const belegteSummen = new Set(behalten.map((x) => x.checksum).filter(Boolean));

for (const pool of Object.keys(POOLS)) {
  const eigene = REFRESH ? [] : existing.images.filter((x) => x.pool === pool);
  const keep = [];
  for (const x of eigene) {
    if (AUSGESCHLOSSEN.has(x.sourceTitle)) {
      if (dateiDa(x)) unlinkSync(join(SITE, x.src.replace(/^\//, '')));
      console.log(pool + ': entfernt ' + x.sourceTitle + ' (' + AUSGESCHLOSSEN.get(x.sourceTitle) + ')');
      continue;
    }
    if (!dateiDa(x) || keep.length >= (POOLS[pool].max || PRO_POOL)) continue;
    const text = [x.sourceTitle, x.alt].join(' ');
    keep.push({ ...x, locality: localityFor(text), geprueft: FREIGEGEBEN.has(x.sourceTitle) });
  }
  keep.forEach((x) => x.sourceTitle && usedTitles.add(x.sourceTitle));
  finalImages.push(...keep);
  if (keep.length >= (POOLS[pool].max || PRO_POOL)) {
    console.log(pool + ': vorhandene ' + keep.length + ' Fotos bleiben bestehen');
    continue;
  }

  const fehlend = (POOLS[pool].max || PRO_POOL) - keep.length;
  const serien = new Map();
  for (const x of keep) serien.set(serie(x.sourceTitle), (serien.get(serie(x.sourceTitle)) || 0) + 1);
  const chosen = [];
  const quellen = [...(POOLS[pool].titel ? [{ titel: POOLS[pool].titel }] : []), ...POOLS[pool].queries];
  for (const query of quellen) {
    let candidates = [];
    try {
      candidates = typeof query === 'string' ? await commonsSearch(query) : await commonsTitel(query.titel);
    } catch (e) {
      console.warn(pool + ': Suche fehlgeschlagen (' + (query.titel || query) + '): ' + e.message);
      continue;
    }
    for (const candidate of candidates) {
      // Reserve für verworfene Downloads.
      if (chosen.length >= fehlend + 8) break;
      if (!usable(candidate, pool) || usedTitles.has(candidate.title) || chosen.some((x) => x.title === candidate.title)) continue;
      const s = serie(candidate.title);
      if ((serien.get(s) || 0) >= (pool.endsWith('detail') || ['technik','rettung','unfall','flaeche'].includes(pool) ? 2 : SERIE_MAX)) continue;
      serien.set(s, (serien.get(s) || 0) + 1);
      chosen.push(candidate);
    }
    if (chosen.length >= fehlend + 8) break;
    await sleep(180);
  }

  const belegt = new Set(keep.map((x) => Number(String(x.src).split('/').pop().split('-')[1])));
  let neu = 0;
  for (const candidate of chosen) {
    if (neu >= fehlend) break;
    let index = 1;
    while (belegt.has(index)) index++;
    let record;
    try {
      record = await download(candidate, pool, index);
    } catch (e) {
      console.warn(pool + ': Download verworfen ' + candidate.title + ': ' + e.message);
      continue;
    }
    if (belegteSummen.has(record.checksum) || finalImages.some((x) => x.checksum === record.checksum)) {
      unlinkSync(join(SITE, record.src.replace(/^\//, '')));
      console.warn(pool + ': Dublette verworfen ' + candidate.title);
      continue;
    }
    belegt.add(index);
    finalImages.push(record);
    usedTitles.add(candidate.title);
    neu++;
    console.log(pool + ': neu (ungeprüft) ' + candidate.title);
    await sleep(120);
  }

  const n = finalImages.filter((x) => x.pool === pool).length;
  if (n < PRO_POOL && NUR_FOTO.has(pool)) throw new Error(pool + ': nur ' + n + ' gültige Fotos, benötigt ' + PRO_POOL);
  if (n < PRO_POOL) console.warn(pool + ': ' + n + ' Fotos, Rest übernehmen die Symbolgrafiken');
  console.log(pool + ': ' + keep.length + ' behalten, ' + neu + ' neu');
}

mkdirSync(dirname(MANIFEST), { recursive: true });
const counts = Object.fromEntries(Object.keys(POOLS).map((p) => [p, finalImages.filter((x) => x.pool === p).length]));
writeFileSync(MANIFEST, JSON.stringify({
  version: 1,
  generated: HEUTE,
  source: 'Wikimedia Commons',
  minimumPerPool: PRO_POOL,
  pools: counts,
  images: finalImages.sort((x, y) => Object.keys(POOLS).indexOf(x.pool) - Object.keys(POOLS).indexOf(y.pool) || x.src.localeCompare(y.src))
}, null, 2) + '\n');

console.log('Editorial photo pools:', counts);
console.log('Total photos:', finalImages.length);
