/**
 * Szenenrezepte fuer die 320 Symbolbilder, je Pool 20.
 *
 * Ein Rezept ist eine Grundflaeche (B Bodenband, H Horizont, I Innenraum,
 * N nichts) und eine Folge von Platzierungen [form, x, y, massstab] aus
 * lib-motiv-formen.mjs. Die Slugs entsprechen RAW in lib-symbolbilder.mjs;
 * die IDs der Motive haengen an ihnen, deshalb wird hier nichts umbenannt.
 *
 * Zwei Motive gelten als verschieden, wenn ihre Elementfolge nach der
 * Normalisierung aus qa/pruefung.mjs (Text, Farben, Zahlen entfernt) verschieden
 * ist. pruefeEindeutigkeit() stellt das fuer alle 320 sicher; der Generator
 * ruft sie vor dem Schreiben auf.
 */
import { createHash } from 'node:crypto';
import { FORMEN, SZENE, setze, FARBE, STRICH } from './lib-motiv-formen.mjs';

const xml = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&apos;' }[c]));

export const SZENEN = {
  sport: {
    spiel: ['B', ['tor', 1150, 700, 1.4], ['personSpielend', 650, 700, 1.4], ['ballKlein', 900, 560, 2]],
    zweikampf: ['B', ['personSpielend', 620, 700, 1.5], ['personGehend', 900, 700, 1.5], ['ballKlein', 760, 700, 2.5]],
    fussballplatz: ['H', ['flutlicht', 300, 700, 0.9], ['flutlicht', 1300, 700, 0.9], ['spielfeld', 800, 700, 1.5]],
    tor: ['B', ['tor', 800, 700, 2.6], ['ballKlein', 640, 700, 3]],
    ball: ['B', ['ball', 800, 700, 4]],
    eckfahne: ['B', ['eckfahne', 800, 700, 2], ['gras', 1100, 700, 2]],
    torwart: ['B', ['tor', 800, 700, 2.6], ['personArmeHoch', 800, 700, 1.3]],
    schiedsrichter: ['N', ['pfeife', 700, 450, 2.2], ['karteRot', 1050, 500, 2]],
    trainerbank: ['B', ['bankDach', 800, 700, 2], ['personSitzend', 700, 700, 1], ['personSitzend', 900, 700, 1]],
    training: ['B', ['huetchen', 450, 700, 1.6], ['huetchen', 700, 700, 1.6], ['huetchen', 950, 700, 1.6], ['ballKlein', 580, 700, 2.5], ['personGehend', 1250, 700, 1.4]],
    mannschaft: ['B', ['gruppe', 550, 700, 1.4], ['gruppe', 1050, 700, 1.4], ['ballKlein', 800, 700, 2]],
    jugendfussball: ['B', ['personKind', 640, 700, 1.8], ['ballKlein', 850, 700, 3], ['tor', 1200, 700, 1.2]],
    amateurfussball: ['B', ['spielfeld', 700, 700, 1.3], ['hausKlein', 1350, 700, 1.2], ['zaun', 350, 700, 1]],
    zuschauer: ['B', ['menge', 800, 700, 1.6], ['zaun', 800, 700, 1.6]],
    tribuene: ['B', ['tribuene', 800, 700, 1.4], ['fahne', 1250, 700, 1]],
    flutlicht: ['B', ['mond', 1250, 200, 0.9], ['flutlicht', 800, 700, 1.3], ['tor', 450, 700, 1.2]],
    stadion: ['H', ['flutlicht', 330, 700, 0.6], ['flutlicht', 1270, 700, 0.6], ['stadion', 800, 700, 1.4]],
    fussballschuhe: ['B', ['schuh', 800, 700, 2.6]],
    tornetz: ['N', ['tornetz', 800, 700, 1.6], ['ballKlein', 900, 700, 3]],
    spielvorbereitung: ['B', ['sporttasche', 600, 700, 1.6], ['schuh', 1000, 700, 1.4], ['klemmbrett', 1250, 700, 1.2]],
  },
  polizei: {
    streifenwagen: ['B', ['auto', 800, 700, 2], ['lichtbalken', 800, 360, 2], ['streifen', 800, 700, 2]],
    polizeibeamte: ['B', ['personKappe', 700, 700, 1.6], ['personKappe', 920, 700, 1.6]],
    einsatz: ['B', ['auto', 450, 700, 1.5], ['lichtbalken', 450, 445, 1.5], ['absperrgitter', 1100, 700, 1.5], ['personKappe', 1420, 700, 1.3]],
    blaulicht: ['N', ['mond', 300, 220, 1], ['lichtbalken', 800, 520, 3.5]],
    absperrung: ['B', ['absperrgitter', 800, 700, 2.2], ['huetchen', 400, 700, 1.8], ['huetchen', 1200, 700, 1.8]],
    polizeiband: ['B', ['bandPfosten', 800, 700, 2.2]],
    spurensicherung: ['B', ['lupe', 700, 520, 2.2], ['beweismarker', 1050, 700, 1.8]],
    einbruch: ['B', ['fensterKaputt', 800, 700, 2], ['brecheisen', 1150, 700, 1.2]],
    tatort: ['B', ['bandPfosten', 800, 700, 2], ['beweismarker', 500, 700, 1.6], ['beweismarker', 800, 700, 1.6], ['beweismarker', 1100, 700, 1.6]],
    verkehrskontrolle: ['B', ['auto', 550, 700, 1.5], ['personKappe', 1000, 700, 1.5], ['kelle', 1130, 700, 1.1]],
    unfallaufnahme: ['B', ['auto', 500, 700, 1.5], ['autoVorn', 1000, 700, 1.5], ['warndreieck', 1350, 700, 1.1]],
    polizeiwache: ['B', ['gebaeude', 800, 700, 1.6], ['schild', 1250, 700, 1.2]],
    fussstreife: ['B', ['hausKlein', 350, 700, 1.2], ['personKappe', 700, 700, 1.5], ['personKappe', 900, 700, 1.5], ['laterne', 1250, 700, 1.1]],
    funk: ['N', ['funkgeraet', 800, 620, 2.2]],
    ausruestung: ['N', ['handschellen', 550, 450, 2], ['funkgeraet', 1000, 620, 1.6]],
    kriminalpolizei: ['I', ['ordner', 620, 700, 1.6], ['lupe', 1000, 560, 1.6], ['dokument', 1300, 700, 1.2]],
    nachteinsatz: ['B', ['mond', 300, 200, 1], ['stern', 600, 160, 1], ['auto', 900, 700, 1.8], ['lichtbalken', 900, 394, 1.8]],
    kontrolle: ['B', ['auto', 1250, 700, 1.2], ['schild', 500, 700, 1.5], ['kelle', 900, 700, 1.6]],
    zeugenaufruf: ['N', ['megafon', 650, 450, 2], ['sprechblase', 1100, 450, 1.4]],
    fahndung: ['N', ['landkarte', 800, 500, 2], ['stecknadel', 800, 420, 1.4], ['lupe', 1200, 650, 1.4]],
  },
  feuerwehr: {
    loeschfahrzeug: ['B', ['loeschfahrzeug', 800, 700, 2]],
    feuerwehrleute: ['B', ['personHelm', 650, 700, 1.6], ['personHelm', 850, 700, 1.6], ['personHelm', 1050, 700, 1.6]],
    atemschutz: ['B', ['atemschutz', 800, 700, 2.2]],
    loeschschlauch: ['B', ['schlauch', 800, 700, 2.4]],
    drehleiter: ['B', ['drehleiter', 800, 700, 1.7]],
    blaulicht: ['B', ['helm', 600, 700, 2], ['lichtbalken', 1100, 600, 2]],
    dtechnischehilfe: ['B', ['auto', 450, 700, 1.3], ['spreizer', 1000, 700, 2]],
    verkehrsunfallhilfe: ['B', ['loeschfahrzeug', 500, 700, 1.3], ['autoVorn', 1050, 700, 1.4], ['huetchen', 1350, 700, 1.5]],
    einsatzabsicherung: ['B', ['huetchen', 450, 700, 1.8], ['huetchen', 700, 700, 1.8], ['warndreieck', 1100, 700, 1.5]],
    feuerwehrwache: ['B', ['halle', 800, 700, 1.6], ['fahne', 250, 700, 1]],
    fahrzeughalle: ['B', ['halle', 800, 700, 1.7], ['loeschfahrzeug', 800, 700, 1.1]],
    rettungsgeraet: ['B', ['leiter', 600, 700, 1.8], ['axt', 1000, 600, 1.6], ['schlauch', 1250, 700, 1.1]],
    ausruestung: ['B', ['helm', 550, 700, 2], ['stiefel', 900, 700, 1.4], ['jacke', 1250, 700, 1.4]],
    vegetationsbrand: ['B', ['flamme', 600, 700, 1.8], ['flamme', 800, 700, 1.3], ['baumTanne', 1150, 700, 1.5], ['gras', 400, 700, 2]],
    nachteinsatz: ['B', ['mond', 300, 200, 1], ['loeschfahrzeug', 900, 700, 1.8]],
    einsatzvorbereitung: ['I', ['jacke', 600, 700, 1.6], ['helm', 950, 700, 1.6], ['stiefel', 1250, 700, 1.3]],
    loeschangriff: ['B', ['personHelm', 500, 700, 1.6], ['strahlrohr', 700, 600, 1.6], ['flamme', 1150, 700, 2]],
    gruppenbereitschaft: ['B', ['helm', 500, 700, 1.4], ['helm', 700, 700, 1.4], ['helm', 900, 700, 1.4], ['helm', 1100, 700, 1.4]],
    fahrzeugkolonne: ['B', ['loeschfahrzeug', 400, 700, 1.1], ['loeschfahrzeug', 800, 700, 1.1], ['loeschfahrzeug', 1200, 700, 1.1]],
    einsatzstelle: ['B', ['absperrgitter', 500, 700, 1.6], ['flamme', 1000, 700, 1.8], ['hydrant', 1300, 700, 1.4]],
  },
  verkehr: {
    strasse: ['B', ['strasse', 800, 700, 2.2], ['baumRund', 300, 700, 1.2], ['baumRund', 1300, 700, 1.2]],
    baustelle: ['B', ['bagger', 700, 700, 1.6], ['huetchen', 1150, 700, 1.6], ['absperrgitter', 1350, 700, 1]],
    sperrung: ['B', ['absperrgitter', 800, 700, 1.6], ['sperrschild', 800, 700, 1.5]],
    umleitung: ['B', ['strasse', 800, 700, 1.6], ['pfeilschild', 800, 700, 1.4]],
    baustellenampel: ['B', ['ampel', 800, 700, 1.6], ['huetchen', 1100, 700, 1.5]],
    verkehrsschild: ['B', ['dreieckschild', 800, 700, 1.7]],
    fahrbahn: ['H', ['fahrbahn', 800, 650, 2.4]],
    strassenarbeiten: ['B', ['personHelm', 600, 700, 1.6], ['schaufel', 750, 700, 1.4], ['huetchen', 1000, 700, 1.6], ['absperrgitter', 1300, 700, 1.2]],
    baustellenfahrzeug: ['B', ['bagger', 800, 700, 2]],
    stau: ['B', ['auto', 400, 700, 1.3], ['auto', 800, 700, 1.3], ['auto', 1200, 700, 1.3]],
    autobahn: ['B', ['strasse', 800, 700, 1.8], ['bruecke', 800, 540, 1.5]],
    landstrasse: ['B', ['sonne', 1350, 180, 0.9], ['strasse', 800, 700, 2], ['baumTanne', 350, 700, 1.4], ['baumTanne', 1250, 700, 1.4]],
    kreuzung: ['N', ['kreuzung', 800, 450, 1.6]],
    verkehrsfluss: ['B', ['auto', 550, 700, 1.6], ['lieferwagen', 1050, 700, 1.5]],
    opnv: ['B', ['bus', 750, 700, 1.4], ['hSchild', 1300, 700, 1.4]],
    bus: ['B', ['bus', 800, 700, 1.9]],
    haltestelle: ['B', ['hSchild', 600, 700, 1.8], ['bank', 950, 700, 1.6], ['personGehend', 1250, 700, 1.3]],
    bahn: ['B', ['zug', 800, 700, 1.9]],
    bahnuebergang: ['B', ['andreaskreuz', 500, 700, 1.5], ['schranke', 900, 700, 1.6]],
    strassenschaden: ['B', ['warndreieck', 500, 700, 1.2], ['schlagloch', 800, 700, 2.2], ['huetchen', 1100, 700, 1.6]],
  },
  vereine: {
    vereinsleben: ['I', ['runderTisch', 800, 700, 1.7], ['personSitzend', 560, 700, 1.2], ['personSitzend', 1040, 700, 1.2]],
    ehrenamt: ['N', ['haendeHalten', 800, 640, 1.6], ['herz', 800, 260, 1.2]],
    vereinsheim: ['B', ['haus', 800, 700, 1.8], ['fahne', 1200, 700, 1.2]],
    versammlung: ['I', ['rednerpult', 500, 700, 1.4], ['stuhl', 800, 700, 1.3], ['stuhl', 950, 700, 1.3], ['stuhl', 1100, 700, 1.3], ['stuhl', 1250, 700, 1.3]],
    gruppe: ['B', ['gruppe', 800, 700, 2]],
    gemeinschaft: ['B', ['person', 500, 700, 1.4], ['person', 680, 700, 1.4], ['person', 860, 700, 1.4], ['person', 1040, 700, 1.4], ['herz', 800, 250, 1]],
    veranstaltung: ['B', ['wimpelkette', 800, 560, 1.2], ['zelt', 800, 700, 1.6]],
    brauchtum: ['B', ['maibaum', 800, 700, 1.5], ['menge', 800, 700, 1.1]],
    schuetzen: ['B', ['zielscheibe', 800, 700, 1.6], ['fahne', 1200, 700, 1.1]],
    karneval: ['B', ['konfetti', 800, 480, 1.6], ['narrenkappe', 800, 700, 2]],
    musikverein: ['B', ['trommel', 650, 700, 1.5], ['trompete', 1100, 600, 1.5]],
    sportverein: ['B', ['wimpel', 500, 700, 1.5], ['ball', 900, 700, 2.2]],
    buergerverein: ['B', ['ortsschild', 500, 700, 1.5], ['gruppe', 1000, 700, 1.6]],
    jugendgruppe: ['B', ['personKind', 550, 700, 1.6], ['personKind', 750, 700, 1.6], ['personKind', 950, 700, 1.6], ['drachen', 1250, 500, 1.1]],
    vorstand: ['I', ['tisch', 800, 700, 1.8], ['hammerGavel', 800, 376, 1], ['dokument', 550, 376, 0.8]],
    freiwilligesengagement: ['B', ['weste', 800, 700, 1.7], ['herz', 1150, 450, 1]],
    gemeinschaftsarbeit: ['B', ['busch', 300, 700, 1.3], ['schubkarre', 650, 700, 1.6], ['harke', 1100, 700, 1.4]],
    festvorbereitung: ['B', ['wimpelkette', 800, 520, 1.4], ['bierbank', 800, 700, 1.6]],
    lokaleinitiative: ['B', ['wegweiser', 500, 700, 1.5], ['gruppe', 1000, 700, 1.5]],
    helferteam: ['B', ['weste', 550, 700, 1.4], ['kiste', 900, 700, 1.5], ['kiste', 1150, 700, 1.5]],
  },
  gemeinde: {
    rathaus: ['B', ['rathaus', 800, 700, 1.5], ['baumRund', 300, 700, 1.2]],
    gemeinderat: ['I', ['runderTisch', 800, 700, 2], ['personSitzend', 450, 700, 1.1], ['personSitzend', 800, 700, 1.1], ['personSitzend', 1150, 700, 1.1]],
    sitzungssaal: ['I', ['stuhl', 550, 700, 1.3], ['stuhl', 850, 700, 1.3], ['tisch', 700, 700, 2], ['rednerpult', 1300, 700, 1.3]],
    verwaltung: ['I', ['schreibtisch', 800, 700, 1.7], ['monitor', 800, 428, 1.2], ['ordnerStapel', 1130, 428, 0.9]],
    akten: ['I', ['ordnerStapel', 800, 700, 2.4]],
    buergerbuero: ['I', ['schalter', 800, 700, 1.7], ['nummernzettel', 1000, 360, 1.2]],
    gemeindegebaeude: ['B', ['gebaeude', 800, 700, 1.7], ['fahne', 1250, 700, 1.3]],
    ortsentwicklung: ['N', ['landkarte', 800, 520, 2.2], ['stecknadel', 700, 380, 1.2], ['stecknadel', 950, 460, 1.2]],
    infrastruktur: ['B', ['bagger', 1300, 700, 1], ['rohr', 800, 700, 1.8]],
    bauplanung: ['I', ['bauplan', 650, 480, 1.6], ['lineal', 1280, 640, 1]],
    oeffentlichesitzung: ['I', ['rednerpult', 450, 700, 1.5], ['menge', 1000, 700, 1.3]],
    kommunalesprojekt: ['B', ['kran', 1100, 700, 1.1], ['hausRohbau', 550, 700, 1.6]],
    strassenbau: ['B', ['walze', 700, 700, 1.6], ['huetchen', 1150, 700, 1.6]],
    oeffentlicheeinrichtung: ['B', ['gebaeude', 700, 700, 1.6], ['infoTafel', 1250, 700, 1.2]],
    buergermeisteramt: ['I', ['fahne', 1300, 700, 1.3], ['schreibtisch', 800, 700, 1.7], ['dokument', 800, 428, 0.9]],
    politischeberatung: ['N', ['sprechblase', 600, 450, 1.4], ['sprechblase', 1100, 500, 1.1]],
    verwaltungsservice: ['I', ['stempel', 700, 700, 2], ['dokument', 1050, 700, 1.6]],
    baugenehmigung: ['I', ['bauplan', 700, 520, 1.6], ['stempel', 1200, 700, 1.5]],
    haushalt: ['I', ['muenzen', 600, 700, 2], ['balkendiagramm', 1100, 700, 1.5]],
    buergerinformation: ['B', ['infoTafel', 800, 700, 1.8], ['personGehend', 1150, 700, 1.2]],
  },
  veranstaltungen: {
    publikum: ['B', ['buehne', 800, 520, 1.2], ['menge', 800, 700, 1.9]],
    buehne: ['B', ['buehne', 800, 700, 1.5], ['vorhang', 800, 700, 1.5]],
    konzert: ['B', ['mikrofon', 700, 700, 1.7], ['noten', 1050, 450, 1.5]],
    markt: ['B', ['marktstand', 800, 700, 1.6], ['korb', 1250, 700, 1.3]],
    fest: ['B', ['wimpelkette', 800, 500, 1.5], ['zelt', 550, 700, 1.3], ['bierbank', 1150, 700, 1.1]],
    saal: ['I', ['buehne', 800, 540, 1], ['stuhl', 450, 700, 1.3], ['stuhl', 650, 700, 1.3], ['stuhl', 850, 700, 1.3], ['stuhl', 1050, 700, 1.3]],
    outdoor: ['B', ['sonne', 300, 200, 1], ['baumRund', 350, 700, 1.4], ['zelt', 900, 700, 1.5], ['baumRund', 1400, 700, 1.2]],
    kulturabend: ['I', ['lampion', 500, 600, 1.4], ['stuhl', 800, 700, 1.5], ['lampion', 1100, 600, 1.4]],
    familienfest: ['B', ['luftballons', 500, 700, 1.6], ['personKind', 850, 700, 1.6], ['person', 1050, 700, 1.3]],
    infostand: ['B', ['banner', 800, 700, 1.5], ['tisch', 800, 700, 1.8], ['dokument', 800, 376, 0.7]],
    flohmarkt: ['B', ['tisch', 800, 700, 1.8], ['tasse', 650, 376, 0.8], ['buecherstapel', 900, 376, 0.8], ['bilderrahmen', 1100, 376, 0.6]],
    ausstellung: ['I', ['bilderrahmen', 450, 450, 1.5], ['bilderrahmen', 800, 450, 1.5], ['bilderrahmen', 1150, 450, 1.5], ['person', 1300, 700, 1.3]],
    lesung: ['I', ['stuhl', 650, 700, 1.5], ['mikrofon', 950, 700, 1.4], ['buchOffen', 1300, 700, 0.9]],
    workshop: ['I', ['werkbank', 800, 700, 1.6], ['werkzeuge', 1250, 420, 1]],
    vereinsfest: ['B', ['fahne', 400, 700, 1.3], ['bierbank', 850, 700, 1.5], ['wimpel', 1350, 700, 1.2]],
    kirmes: ['B', ['riesenrad', 800, 700, 1.3], ['zelt', 1350, 700, 0.9]],
    openair: ['B', ['sonne', 1400, 180, 0.9], ['baumRund', 250, 700, 1.4], ['buehne', 800, 700, 1.4], ['baumRund', 1350, 700, 1.4]],
    food: ['B', ['grill', 700, 700, 1.8], ['teller', 1150, 500, 1]],
    adventsmarkt: ['B', ['stern', 400, 250, 1.5], ['marktstand', 700, 700, 1.5], ['tanne', 1250, 700, 1.3]],
    sportevent: ['B', ['podest', 800, 700, 1.7], ['fahne', 1250, 700, 1.2]],
  },
  leben: {
    menschen: ['B', ['menge', 800, 700, 1.8]],
    nachbarschaft: ['B', ['haus', 500, 700, 1.3], ['haus', 1100, 700, 1.3], ['person', 750, 700, 1.1], ['person', 880, 700, 1.1]],
    begegnung: ['B', ['person', 700, 700, 1.6], ['personWinkend', 950, 700, 1.6]],
    dorfleben: ['B', ['kirche', 800, 700, 1.2], ['hausKlein', 450, 700, 1.3], ['hausKlein', 1200, 700, 1.3], ['baumRund', 1400, 700, 1.2]],
    senioren: ['B', ['bank', 800, 700, 1.7], ['personSitzend', 700, 700, 1.1], ['gehstock', 1000, 700, 1.2]],
    jugend: ['B', ['personGehend', 700, 700, 1.5], ['skateboard', 1000, 700, 1.5]],
    familienalltag: ['B', ['haus', 1100, 700, 1.5], ['kinderwagen', 600, 700, 1.5], ['person', 750, 700, 1.3]],
    freizeit: ['B', ['sonne', 1300, 200, 0.9], ['fahrrad', 800, 700, 2]],
    spaziergang: ['B', ['weg', 400, 700, 1.2], ['baumRund', 600, 700, 1.4], ['personGehend', 900, 700, 1.5], ['baumRund', 1300, 700, 1.2]],
    park: ['B', ['baumRund', 400, 700, 1.6], ['bank', 800, 700, 1.6], ['laterne', 1150, 700, 1.2], ['busch', 1400, 700, 1.2]],
    cafe: ['I', ['tisch', 800, 700, 1.5], ['tasse', 750, 430, 1.1], ['tasse', 900, 430, 0.9]],
    wochenmarkt: ['B', ['marktstand', 700, 700, 1.5], ['korb', 1200, 700, 1.4], ['person', 1350, 700, 1.2]],
    gespraech: ['B', ['person', 650, 700, 1.5], ['person', 950, 700, 1.5], ['sprechblase', 620, 380, 0.7], ['sprechblase', 980, 380, 0.7]],
    engagement: ['N', ['haendeHalten', 800, 660, 1.5], ['herzKontur', 800, 260, 1]],
    gesundheit: ['N', ['herz', 650, 450, 1.6], ['pulslinie', 1150, 450, 1.1]],
    natur: ['B', ['sonne', 300, 200, 0.9], ['huegel', 800, 700, 1.6], ['baumTanne', 500, 700, 1.5], ['baumRund', 1150, 700, 1.5], ['vogel', 1000, 250, 1]],
    alltag: ['B', ['uhr', 400, 350, 1], ['hausKlein', 850, 700, 1.5], ['fahrrad', 1250, 700, 1.1]],
    ortsmitte: ['B', ['hausKlein', 400, 700, 1.4], ['hausKlein', 1250, 700, 1.4], ['brunnen', 800, 700, 1.6]],
    community: ['B', ['gruppe', 800, 700, 1.6], ['herz', 800, 280, 0.9]],
    portraet: ['B', ['kopf', 800, 700, 1.9]],
  },
  wirtschaft: {
    betrieb: ['B', ['fabrik', 800, 700, 1.6]],
    buero: ['I', ['schreibtisch', 800, 700, 1.7], ['monitor', 800, 428, 1.1], ['lampeSchreibtisch', 1150, 428, 0.6]],
    handwerk: ['N', ['werkzeuge', 800, 450, 2.2]],
    produktion: ['I', ['foerderband', 800, 700, 2]],
    gewerbe: ['B', ['ladenfront', 800, 700, 1.7]],
    logistik: ['B', ['lkw', 800, 700, 1.6], ['paket', 1350, 700, 1.2]],
    arbeitsplatz: ['I', ['schreibtisch', 800, 700, 1.7], ['stuhl', 800, 700, 1.3], ['laptop', 800, 428, 0.9]],
    werkstatt: ['I', ['werkbank', 800, 700, 1.8], ['schraubenschluessel', 1250, 400, 0.9]],
    lager: ['I', ['regal', 800, 700, 1.6]],
    lieferung: ['B', ['lieferwagen', 750, 700, 1.7], ['paket', 1250, 700, 1.4]],
    maschinen: ['N', ['zahnrad', 650, 450, 2], ['zahnrad', 950, 550, 1.5]],
    einzelhandel: ['B', ['ladenfront', 700, 700, 1.4], ['einkaufstasche', 1250, 700, 1.4]],
    dienstleistung: ['N', ['klemmbrett', 650, 600, 1.5], ['schraubenschluessel', 1050, 450, 1.2]],
    gruendung: ['B', ['sprossling', 700, 700, 1.8], ['balkendiagramm', 1150, 700, 1.3]],
    meeting: ['I', ['tisch', 800, 700, 1.9], ['personSitzend', 500, 700, 1.1], ['personSitzend', 1100, 700, 1.1], ['balkendiagramm', 800, 400, 0.6]],
    digitalisierung: ['I', ['laptop', 700, 700, 1.8], ['wolkeIT', 1200, 350, 1.2]],
    energie: ['B', ['sonne', 1350, 180, 0.8], ['windrad', 600, 700, 1.3], ['solar', 1200, 700, 1.5]],
    bauunternehmen: ['B', ['kran', 1000, 700, 1.1], ['hausRohbau', 500, 700, 1.5], ['bauhelm', 1350, 700, 1.1]],
    landwirtschaft: ['B', ['sonne', 300, 200, 0.9], ['huegel', 1300, 700, 1], ['traktor', 750, 700, 1.6]],
    industrie: ['B', ['fabrik', 600, 700, 1.4], ['schornstein', 1150, 700, 1.2], ['schornstein', 1300, 700, 1]],
  },
  jobs: {
    buerojob: ['I', ['schreibtisch', 800, 700, 1.6], ['monitor', 800, 444, 1], ['personSitzend', 560, 700, 1.2]],
    handwerk: ['I', ['werkzeugkasten', 800, 700, 2], ['werkzeuge', 1200, 400, 1]],
    werkstatt: ['I', ['hebebuehne', 800, 700, 1.6], ['auto', 800, 380, 1.2]],
    produktion: ['I', ['foerderband', 1100, 700, 1], ['bauhelm', 550, 700, 1.6], ['zahnrad', 950, 450, 1.6]],
    verkauf: ['I', ['kasse', 700, 700, 1.8], ['einkaufstasche', 1150, 700, 1.4]],
    pflege: ['N', ['kreuzMed', 650, 450, 1.2], ['herzKontur', 1000, 450, 1.4]],
    it: ['I', ['laptop', 700, 700, 1.8], ['codeKlammern', 1200, 450, 1]],
    verwaltung: ['I', ['ordner', 600, 700, 1.6], ['stempel', 950, 700, 1.6], ['dokument', 1250, 700, 1.3]],
    lager: ['I', ['regal', 1250, 700, 1], ['gabelstapler', 700, 700, 1.6]],
    logistik: ['B', ['lkw', 700, 700, 1.5], ['paket', 1200, 700, 1.2], ['paket', 1350, 700, 1.2]],
    gastronomie: ['I', ['kochmuetze', 550, 700, 1.6], ['teller', 1050, 600, 1.3]],
    bau: ['B', ['ziegelwand', 1250, 700, 0.8], ['bauhelm', 600, 700, 1.8], ['kelleMaurer', 1000, 600, 1.4]],
    ausbildung: ['I', ['hutAbschluss', 650, 450, 1.4], ['buecherstapel', 1100, 700, 1.5]],
    teammeeting: ['I', ['tisch', 800, 700, 1.8], ['personSitzend', 450, 700, 1.1], ['personSitzend', 750, 700, 1.1], ['personSitzend', 1150, 700, 1.1]],
    bewerbung: ['I', ['dokument', 650, 700, 1.7], ['stift', 1050, 550, 1], ['umschlag', 1300, 650, 0.8]],
    schichtarbeit: ['N', ['uhr', 700, 450, 1.6], ['sonne', 1150, 300, 0.9], ['mond', 1200, 600, 0.8]],
    kundenservice: ['N', ['headset', 800, 480, 2]],
    technik: ['N', ['schraubenschluessel', 650, 450, 1.6], ['zahnrad', 1050, 450, 1.6]],
    fahrer: ['B', ['lieferwagen', 800, 700, 1.7], ['lenkrad', 1350, 450, 0.8]],
    sozialarbeit: ['B', ['person', 600, 700, 1.5], ['personKind', 800, 700, 1.5], ['person', 1000, 700, 1.5], ['herz', 800, 250, 0.9]],
  },
  immobilien: {
    wohnhaus: ['B', ['haus', 800, 700, 2], ['baumRund', 350, 700, 1.3]],
    wohnung: ['B', ['wohnblock', 800, 700, 1.6], ['schluessel', 1250, 450, 1]],
    mehrfamilienhaus: ['B', ['wohnblock', 800, 700, 1.7], ['baumRund', 300, 700, 1.2], ['baumRund', 1300, 700, 1.2]],
    schluessel: ['N', ['schluessel', 800, 450, 2.6]],
    neubau: ['B', ['kran', 1150, 700, 1], ['haus', 600, 700, 1.6]],
    baustelle: ['B', ['betonmischer', 650, 700, 1.7], ['ziegelwand', 1150, 700, 1.2], ['schaufel', 1400, 700, 1.1]],
    innenraum: ['I', ['fenster', 800, 420, 1.4], ['sofa', 800, 700, 1.5], ['wohnzimmerLampe', 1250, 700, 1.1]],
    grundriss: ['N', ['grundriss', 800, 450, 1.8]],
    einfamilienhaus: ['B', ['haus', 650, 700, 1.7], ['garage', 1150, 700, 1.3]],
    balkon: ['B', ['balkon', 800, 700, 1.7]],
    kueche: ['I', ['kueche', 800, 700, 1.6]],
    wohnzimmer: ['I', ['bilderrahmen', 750, 400, 0.8], ['sofa', 750, 700, 1.7], ['wohnzimmerLampe', 1200, 700, 1.3]],
    garten: ['B', ['hausKlein', 1100, 700, 1.5], ['baumRund', 1350, 700, 1.3], ['zaun', 500, 700, 1.5], ['busch', 800, 700, 1.3]],
    sanierung: ['B', ['haus', 800, 700, 1.7], ['geruest', 560, 700, 1.2]],
    beratung: ['I', ['hausKlein', 650, 700, 1.5], ['klemmbrett', 1100, 700, 1.6]],
    miete: ['I', ['schluessel', 650, 450, 1.6], ['kalender', 1100, 700, 1.3]],
    eigentum: ['B', ['haus', 700, 700, 1.6], ['schluessel', 1250, 450, 1.4]],
    rohbau: ['B', ['ziegelwand', 800, 700, 1.6], ['betonmischer', 1300, 700, 1.2]],
    fassade: ['B', ['fassade', 800, 700, 1.6]],
    haustuer: ['B', ['tuer', 800, 700, 2]],
  },
  familie: {
    hochzeit: ['B', ['sektglaeser', 600, 700, 1.5], ['ringe', 1100, 450, 1.2]],
    ringe: ['N', ['ringe', 800, 450, 2.4]],
    familie: ['B', ['person', 600, 700, 1.5], ['person', 800, 700, 1.5], ['personKind', 960, 700, 1.5]],
    geburt: ['I', ['wiege', 800, 700, 1.9]],
    jubilaeum: ['I', ['sektglaeser', 650, 700, 1.6], ['schleife', 1150, 420, 1.4]],
    feier: ['I', ['konfetti', 800, 600, 1.5], ['sektglaeser', 800, 700, 1.7]],
    taufe: ['I', ['taufbecken', 800, 700, 1.6], ['kerze', 1150, 700, 1.2]],
    geburtstag: ['I', ['torte', 800, 700, 2]],
    elternkind: ['B', ['person', 720, 700, 1.6], ['personKind', 900, 700, 1.6]],
    baby: ['I', ['kinderwagen', 800, 700, 1.9]],
    paar: ['B', ['person', 700, 700, 1.6], ['person', 900, 700, 1.6]],
    blumen: ['I', ['blumenstrauss', 800, 700, 2.2]],
    herz: ['N', ['herz', 800, 480, 2.6]],
    gratulation: ['I', ['umschlag', 650, 450, 1.4], ['blumenstrauss', 1100, 700, 1.5]],
    familienfest: ['I', ['luftballons', 350, 700, 1.4], ['luftballons', 1300, 700, 1.2], ['tisch', 800, 700, 1.8], ['torte', 800, 376, 0.8]],
    hochzeitsstrauss: ['I', ['blumenstrauss', 800, 700, 2], ['schleife', 1150, 500, 1.2]],
    kinder: ['B', ['personKind', 650, 700, 1.7], ['personKind', 950, 700, 1.7], ['ballKlein', 800, 700, 2.5]],
    generationen: ['B', ['person', 550, 700, 1.7], ['person', 750, 700, 1.5], ['personKind', 930, 700, 1.4], ['gehstock', 620, 700, 1.4]],
    festtafel: ['I', ['tisch', 800, 700, 1.9], ['glas', 600, 358, 1], ['glas', 750, 358, 1], ['glas', 900, 358, 1], ['glas', 1050, 358, 1]],
    glueckwunsch: ['I', ['geschenk', 800, 700, 2]],
  },
  trauer: {
    kerze: ['I', ['kerze', 800, 700, 1.7]],
    gedenkkerze: ['I', ['kerzeGlas', 800, 700, 2.2]],
    kirche: ['I', ['kirchenfenster', 800, 500, 1.2], ['kirchenbank', 800, 700, 1.3]],
    friedhof: ['B', ['grabstein', 550, 700, 1.5], ['grabstein', 800, 700, 1.6], ['grabstein', 1050, 700, 1.5], ['baumTanne', 1350, 700, 1.3]],
    blumen: ['I', ['lilie', 800, 700, 1.5]],
    erinnerung: ['I', ['bilderrahmen', 700, 700, 1.8], ['kerze', 1100, 700, 1.4]],
    trauerflor: ['N', ['schleife', 800, 480, 2.4]],
    rose: ['I', ['rose', 800, 700, 2.2]],
    stilleslicht: ['I', ['mond', 1200, 220, 1], ['kerze', 800, 700, 1.8]],
    gedenkplatz: ['B', ['baumRund', 400, 700, 1.4], ['grabstein', 1150, 700, 1.3], ['bank', 700, 700, 1.6]],
    kreuz: ['B', ['kreuz', 800, 700, 1.8]],
    kondolenz: ['I', ['umschlag', 700, 450, 1.5], ['stift', 1100, 500, 0.9]],
    erinnerungsbuch: ['I', ['buchOffen', 800, 700, 2.2]],
    friedhofstor: ['B', ['baumTanne', 300, 700, 1.3], ['gartentor', 800, 700, 1.7]],
    grablicht: ['B', ['grablicht', 800, 700, 2.4]],
    stille: ['N', ['blatt', 800, 450, 2]],
    abschied: ['B', ['sonne', 300, 200, 0.9], ['weg', 700, 700, 1.6], ['baumRund', 1200, 700, 1.5]],
    trauerkarte: ['I', ['dokument', 700, 700, 1.7], ['rose', 1100, 700, 1.4]],
    kranz: ['N', ['kranz', 800, 450, 2.4]],
    abendlicht: ['B', ['sonne', 800, 600, 1.4], ['huegel', 800, 700, 1.7], ['baumRund', 1200, 700, 1.3]],
  },
  kultur: {
    theater: ['N', ['theatermasken', 800, 500, 2]],
    kunst: ['N', ['palette', 800, 450, 2.2]],
    musik: ['N', ['notenzeile', 800, 450, 2]],
    buch: ['I', ['buecherstapel', 800, 700, 2.2]],
    kino: ['N', ['filmklappe', 800, 500, 1.8]],
    ausstellung: ['I', ['bilderrahmen', 500, 450, 1.4], ['bilderrahmen', 900, 450, 1.6], ['personGehend', 1250, 700, 1.3]],
    buehne: ['B', ['buehne', 800, 700, 1.6], ['spot', 800, 640, 1.5]],
    museum: ['B', ['museum', 800, 700, 1.6]],
    fotografie: ['N', ['kamera', 800, 500, 2.4]],
    tanz: ['B', ['personArmeHoch', 650, 700, 1.6], ['personSpielend', 950, 700, 1.6], ['noten', 1250, 350, 1]],
    literatur: ['I', ['buchOffen', 750, 700, 1.8], ['stift', 1200, 550, 1]],
    konzert: ['B', ['gitarre', 650, 700, 1.4], ['mikrofon', 1000, 700, 1.5], ['noten', 1300, 350, 1]],
    kunsthandwerk: ['I', ['topf', 800, 700, 2.2], ['topf', 1150, 700, 1.6]],
    kulturhaus: ['B', ['gebaeude', 750, 700, 1.6], ['banner', 1250, 700, 1]],
    kreativwerkstatt: ['I', ['staffelei', 700, 700, 1.5], ['schere', 1150, 450, 1.2], ['palette', 1200, 650, 0.7]],
    chor: ['B', ['gruppe', 650, 700, 1.5], ['gruppe', 1000, 700, 1.5], ['noten', 1300, 300, 1.2]],
    instrument: ['I', ['geige', 800, 700, 1.8]],
    galerie: ['I', ['spot', 800, 720, 1.7], ['bilderrahmen', 800, 520, 1.6]],
    lesung: ['I', ['buchOffen', 650, 700, 1.6], ['mikrofon', 1050, 700, 1.5], ['stuhl', 1300, 700, 1.2]],
    openair: ['B', ['mond', 1350, 200, 1], ['stern', 500, 180, 1.2], ['baumTanne', 300, 700, 1.3], ['buehne', 800, 700, 1.4], ['baumTanne', 1350, 700, 1.3]],
  },
  schule: {
    klassenraum: ['I', ['tafel', 800, 460, 1.2], ['schulbank', 550, 700, 1.3], ['schulbank', 1050, 700, 1.3]],
    schulgebaeude: ['B', ['gebaeude', 800, 700, 1.7], ['uhr', 800, 380, 0.5], ['fahne', 1300, 700, 1.2]],
    lernen: ['I', ['buchOffen', 700, 700, 1.6], ['lampeSchreibtisch', 1150, 700, 1.3]],
    buecher: ['I', ['buecherstapel', 650, 700, 1.8], ['buecherstapel', 1050, 700, 1.4]],
    schulhof: ['B', ['baumRund', 1150, 700, 1.5], ['zaun', 1350, 700, 1.2], ['huepfkaestchen', 600, 700, 1.3]],
    bildung: ['N', ['hutAbschluss', 800, 450, 2]],
    tafel: ['I', ['tafel', 800, 700, 1.6]],
    stifte: ['I', ['stifteBecher', 800, 700, 2.2]],
    rucksack: ['I', ['rucksack', 800, 700, 2.2]],
    naturwissenschaft: ['I', ['kolben', 700, 700, 1.7], ['atom', 1150, 450, 1.3]],
    computerraum: ['I', ['schreibtisch', 800, 700, 1.7], ['monitor', 620, 428, 0.9], ['monitor', 980, 428, 0.9]],
    grundschule: ['B', ['gebaeude', 750, 700, 1.5], ['personKind', 1150, 700, 1.4], ['personKind', 1300, 700, 1.4]],
    jugendbildung: ['I', ['laptop', 700, 700, 1.5], ['buecherstapel', 1150, 700, 1.3]],
    lehrkraft: ['I', ['tafel', 800, 460, 1.2], ['person', 1150, 700, 1.4]],
    hausaufgaben: ['I', ['schreibtisch', 800, 700, 1.6], ['buchOffen', 750, 444, 0.8], ['lampeSchreibtisch', 1150, 444, 0.6]],
    sporthalle: ['I', ['korbBasketball', 1200, 700, 1.3], ['ballKlein', 700, 700, 3]],
    schulweg: ['B', ['zebrastreifen', 800, 700, 1.6], ['dreieckschild', 1300, 700, 1.2], ['personKind', 600, 700, 1.5]],
    bibliothek: ['I', ['regal', 650, 700, 1.5], ['regal', 1150, 700, 1.5]],
    projektarbeit: ['I', ['staffelei', 600, 700, 1.4], ['personKind', 1000, 700, 1.4], ['personKind', 1200, 700, 1.4]],
    abschluss: ['I', ['hutAbschluss', 650, 450, 1.5], ['diplom', 1100, 500, 1.4]],
  },
  kirche: {
    kirche: ['B', ['kirche', 800, 700, 1.6], ['baumRund', 300, 700, 1.3]],
    kirchenraum: ['I', ['kirchenfenster', 500, 500, 1.1], ['kirchenfenster', 800, 480, 1.3], ['kirchenfenster', 1100, 500, 1.1], ['kirchenbank', 800, 700, 1.2]],
    glocke: ['N', ['glocke', 800, 560, 1.5]],
    kreuz: ['I', ['kreuz', 800, 700, 1.6]],
    gottesdienst: ['I', ['altar', 800, 700, 1.7], ['bibel', 800, 360, 0.7]],
    gemeinde: ['B', ['kirche', 800, 700, 1.3], ['gruppe', 1250, 700, 1.2]],
    altar: ['I', ['altar', 800, 700, 2]],
    bibel: ['I', ['bibel', 800, 700, 2.2]],
    kerze: ['I', ['kirchenfenster', 1150, 500, 1], ['kerze', 700, 700, 1.8]],
    gebet: ['I', ['haendeGebet', 800, 700, 2.2]],
    kirchturm: ['B', ['kirchturm', 800, 700, 1.3], ['baumTanne', 350, 700, 1.4], ['vogel', 1200, 250, 1]],
    orgel: ['I', ['orgel', 800, 700, 1.7]],
    kirchenbank: ['I', ['kirchenbank', 800, 560, 1.4], ['kirchenbank', 800, 700, 1.7]],
    taufe: ['I', ['taufbecken', 800, 700, 1.8], ['taube', 1200, 300, 1.2]],
    konfirmation: ['I', ['kerze', 600, 700, 1.7], ['bibel', 1050, 700, 1.5]],
    chor: ['I', ['gruppe', 800, 700, 1.6], ['notenzeile', 800, 200, 0.8]],
    gemeindesaal: ['B', ['gemeindesaal', 800, 700, 1.6]],
    fenster: ['I', ['kirchenfenster', 800, 700, 1.7]],
    seelsorge: ['I', ['person', 700, 700, 1.5], ['person', 950, 700, 1.5], ['herzKontur', 825, 300, 0.8]],
    festgottesdienst: ['B', ['wimpelkette', 800, 520, 1.3], ['kirche', 750, 700, 1.4], ['fahne', 1350, 700, 1.2]],
  },
};

const GRUND = { B: () => SZENE.boden(), H: () => SZENE.horizont(), I: () => SZENE.innen(), N: () => '' };

/** Vollstaendiges SVG fuer ein Motiv. Kein Zeilenumbruch am Ende (Checksumme). */
export function motivSvg(pool, slug, name, label) {
  const rezept = SZENEN[pool]?.[slug];
  if (!rezept) throw new Error(`Kein Szenenrezept fuer ${pool}/${slug}`);
  const [grund, ...teile] = rezept;
  if (!GRUND[grund]) throw new Error(`${pool}/${slug}: Grundflaeche ${grund} unbekannt`);
  const inhalt = GRUND[grund]() + teile.map(([form, x, y, s]) => setze(form, x, y, s)).join('');
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 900" width="1600" height="900" role="img" aria-labelledby="t d">`
    + `<title id="t">${xml(name)}</title>`
    + `<desc id="d">Neutrales redaktionelles Symbolbild für ${xml(label)}: ${xml(name)}. Gezeichnete Symbolgrafik, kein Foto eines konkreten Ereignisses.</desc>`
    + `<rect width="1600" height="900" fill="${FARBE.flaeche}"/>`
    + `<g stroke="${FARBE.kontur}" stroke-width="${STRICH}" stroke-linecap="round" stroke-linejoin="round" fill="${FARBE.flaeche}">${inhalt}</g>`
    + SZENE.marke()
    + `</svg>`;
}

/** Dieselbe Normalisierung wie pruefeMotivvielfalt in qa/pruefung.mjs. */
export function struktur(svg) {
  const roh = String(svg)
    .replace(/>[^<]*</g, '><')
    .replace(/#[0-9a-fA-F]{3,8}\b/g, '')
    .replace(/[0-9.]+/g, '');
  return createHash('sha256').update(roh).digest('hex');
}

/**
 * Alle 320 Motive muessen strukturell und byteweise verschieden sein; jede
 * Form in einem Rezept muss existieren. Wirft bei der ersten Verletzung.
 */
export function pruefeEindeutigkeit(eintraegeJePool) {
  const strukturen = new Map(); const rohe = new Map(); const fehler = [];
  for (const [pool, eintraege] of Object.entries(eintraegeJePool)) {
    const rezepte = SZENEN[pool] || {};
    for (const e of eintraege) if (!rezepte[e.slug]) fehler.push(`${pool}/${e.slug}: kein Szenenrezept`);
    for (const slug of Object.keys(rezepte)) if (!eintraege.some((e) => e.slug === slug)) fehler.push(`${pool}/${slug}: Rezept ohne Motiv in RAW`);
    for (const e of eintraege) {
      if (!rezepte[e.slug]) continue;
      for (const [form] of rezepte[e.slug].slice(1)) if (!FORMEN[form]) fehler.push(`${pool}/${e.slug}: Form ${form} fehlt`);
      const svg = motivSvg(pool, e.slug, e.name, pool);
      const s = struktur(svg); const r = createHash('sha256').update(svg).digest('hex');
      if (strukturen.has(s)) fehler.push(`${pool}/${e.slug}: gleiche Struktur wie ${strukturen.get(s)}`);
      if (rohe.has(r)) fehler.push(`${pool}/${e.slug}: byteidentisch mit ${rohe.get(r)}`);
      strukturen.set(s, `${pool}/${e.slug}`); rohe.set(r, `${pool}/${e.slug}`);
    }
  }
  if (fehler.length) throw new Error(`Szenenrezepte: ${fehler.slice(0, 10).join('; ')}${fehler.length > 10 ? ` (+${fehler.length - 10})` : ''}`);
  return strukturen.size;
}
