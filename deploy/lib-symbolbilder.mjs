import { readFileSync, writeFileSync, readdirSync, existsSync, statSync, mkdirSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { createHash } from 'node:crypto';

export const SYMBOL_WURZEL = 'chatgpt-site/assets/symbolbilder';
export const DATEN_WURZEL = 'chatgpt-site/data/editorial-images';
export const BIBLIOTHEK_DATEI = `${DATEN_WURZEL}/editorial-images.json`;
export const ZUORDNUNGEN_DATEI = `${DATEN_WURZEL}/editorial-image-assignments.json`;
export const RECHTE_GEPRUEFT_AM = '2026-09-18';
export const MINDEST_POOL = 20;
export const KATEGORIEN = [
  'sport', 'polizei', 'feuerwehr', 'verkehr', 'vereine', 'gemeinde',
  'veranstaltungen', 'leben', 'wirtschaft', 'jobs', 'immobilien', 'familie',
  'trauer', 'kultur', 'schule', 'kirche',
];

const LABELS = {
  sport: 'Sport / Fußball', polizei: 'Polizei', feuerwehr: 'Feuerwehr', verkehr: 'Verkehr',
  vereine: 'Vereine / Ehrenamt', gemeinde: 'Rathaus / Gemeinde', veranstaltungen: 'Veranstaltungen',
  leben: 'Leben / Menschen', wirtschaft: 'Wirtschaft', jobs: 'Jobs / Arbeit', immobilien: 'Immobilien',
  familie: 'Familie', trauer: 'Trauer', kultur: 'Kultur / Freizeit', schule: 'Schule / Bildung', kirche: 'Kirche / religiöses Leben',
};

const PALETTEN = {
  sport: ['#143b2d','#d6b56d','#f5f4ef'], polizei: ['#16324b','#5ba0d0','#eef3f7'], feuerwehr: ['#5a1717','#d54d3f','#f8efeb'],
  verkehr: ['#373a40','#f2b84b','#f5f5f2'], vereine: ['#49364b','#c28f5a','#f6f1ec'], gemeinde: ['#4d1525','#c7a85d','#f6f2ec'],
  veranstaltungen: ['#4b2142','#d58b60','#f6efe8'], leben: ['#29433d','#8ab3a0','#f4f3ee'], wirtschaft: ['#26394a','#9d8257','#f3f1ed'],
  jobs: ['#2d3542','#cf9a57','#f5f2ed'], immobilien: ['#4a3a32','#b99168','#f5f1ec'], familie: ['#6d3544','#d7a0a9','#f9f0f2'],
  trauer: ['#2b2b30','#8e8a83','#f0efec'], kultur: ['#3d2b52','#d29b55','#f5f0e8'], schule: ['#25485a','#75a8ba','#f0f5f6'], kirche: ['#403b49','#b7a07c','#f5f1eb'],
};

// 20 eigenständige Motive je Pool. Jeder Eintrag: slug|sichtbare Motivbezeichnung|Such-Tags.
// Die Grafiken sind bewusst neutrale redaktionelle Symbolgrafiken: kein reales Ereignis,
// keine erkennbare Person, kein Kennzeichen, kein Vereinslogo, keine fremde Ortsaufnahme.
const RAW = {
  sport: `spiel|Fußballspiel|fussball,spiel,spieltag
zweikampf|Zweikampf|fussball,zweikampf,duell
fussballplatz|Fußballplatz|fussball,platz,rasen
tor|Fußballtor|fussball,tor
ball|Fußball|fussball,ball
eckfahne|Eckfahne|fussball,ecke,eckfahne
torwart|Torwart|fussball,torwart
schiedsrichter|Schiedsrichter|fussball,schiedsrichter
trainerbank|Trainerbank|fussball,trainer
training|Fußballtraining|fussball,training
mannschaft|Mannschaft|fussball,mannschaft,team
jugendfussball|Jugendfußball|fussball,jugend
amateurfussball|Amateurfußball|fussball,amateur,kreisliga
zuschauer|Zuschauer|fussball,zuschauer,fans
tribuene|Tribüne|fussball,tribuene,zuschauer
flutlicht|Flutlicht|fussball,flutlicht,nacht
stadion|Stadion|fussball,stadion
fussballschuhe|Fußballschuhe|fussball,schuhe
tornetz|Tornetz|fussball,tor,netz
spielvorbereitung|Spielvorbereitung|fussball,vorbereitung,spieltag`,
  polizei: `streifenwagen|Streifenwagen|polizei,streifenwagen
polizeibeamte|Polizeibeamte|polizei,beamte
einsatz|Polizeieinsatz|polizei,einsatz
blaulicht|Blaulicht|polizei,blaulicht,einsatz
absperrung|Absperrung|polizei,absperrung
polizeiband|Polizeiband|polizei,absperrung,band
spurensicherung|Spurensicherung|polizei,spurensicherung,kripo
einbruch|Einbruchermittlung|polizei,einbruch,ermittlung
tatort|Neutraler Tatortkontext|polizei,tatort,ermittlung
verkehrskontrolle|Verkehrskontrolle|polizei,verkehrskontrolle
unfallaufnahme|Unfallaufnahme|polizei,unfall,aufnahme
polizeiwache|Polizeiwache|polizei,wache
fussstreife|Fußstreife|polizei,fussstreife
funk|Polizeifunk|polizei,funk
ausruestung|Polizeiausrüstung|polizei,ausruestung
kriminalpolizei|Kriminalpolizei-Kontext|polizei,kripo,ermittlung
nachteinsatz|Polizeieinsatz bei Nacht|polizei,nacht,einsatz
kontrolle|Polizeiliche Kontrolle|polizei,kontrolle
zeugenaufruf|Zeugenaufruf|polizei,zeugen
fahndung|Fahndung|polizei,fahndung`,
  feuerwehr: `loeschfahrzeug|Löschfahrzeug|feuerwehr,fahrzeug
feuerwehrleute|Feuerwehrkräfte|feuerwehr,kraefte
atemschutz|Atemschutz|feuerwehr,atemschutz
loeschschlauch|Löschschlauch|feuerwehr,schlauch,loeschen
drehleiter|Drehleiter|feuerwehr,drehleiter
blaulicht|Blaulicht der Feuerwehr|feuerwehr,blaulicht
dtechnischehilfe|Technische Hilfeleistung|feuerwehr,technische,hilfe
verkehrsunfallhilfe|Hilfe nach Verkehrsunfall|feuerwehr,unfall,hilfe
einsatzabsicherung|Einsatzabsicherung|feuerwehr,absicherung
feuerwehrwache|Feuerwehrwache|feuerwehr,wache
fahrzeughalle|Fahrzeughalle|feuerwehr,halle,fahrzeuge
rettungsgeraet|Rettungsgerät|feuerwehr,rettung,geraet
ausruestung|Feuerwehrausrüstung|feuerwehr,ausruestung
vegetationsbrand|Vegetationsbrand-Kontext|feuerwehr,vegetation,brand
nachteinsatz|Feuerwehreinsatz bei Nacht|feuerwehr,nacht,einsatz
einsatzvorbereitung|Einsatzvorbereitung|feuerwehr,vorbereitung
loeschangriff|Löschangriff|feuerwehr,loeschen,einsatz
gruppenbereitschaft|Feuerwehrgruppe in Bereitschaft|feuerwehr,gruppe,bereitschaft
fahrzeugkolonne|Feuerwehrfahrzeuge|feuerwehr,fahrzeuge
einsatzstelle|Neutrale Einsatzstelle|feuerwehr,einsatzstelle`,
  verkehr: `strasse|Straße|verkehr,strasse
baustelle|Baustelle|verkehr,baustelle
sperrung|Straßensperrung|verkehr,sperrung
umleitung|Umleitung|verkehr,umleitung
baustellenampel|Baustellenampel|verkehr,ampel,baustelle
verkehrsschild|Verkehrsschild|verkehr,schild
fahrbahn|Fahrbahn|verkehr,fahrbahn
strassenarbeiten|Straßenarbeiten|verkehr,strassenbau,arbeiten
baustellenfahrzeug|Baustellenfahrzeug|verkehr,baustelle,fahrzeug
stau|Stau|verkehr,stau
autobahn|Autobahn|verkehr,autobahn
landstrasse|Landstraße|verkehr,landstrasse
kreuzung|Kreuzung|verkehr,kreuzung
verkehrsfluss|Fließender Verkehr|verkehr,autos
opnv|ÖPNV|verkehr,oepnv
bus|Bus|verkehr,bus
haltestelle|Haltestelle|verkehr,haltestelle,bus
bahn|Bahn|verkehr,bahn
bahnuebergang|Bahnübergang|verkehr,bahnuebergang
strassenschaden|Straßenschaden|verkehr,strassenschaden`,
  vereine: `vereinsleben|Vereinsleben|verein,vereinsleben
ehrenamt|Ehrenamt|verein,ehrenamt
vereinsheim|Vereinsheim|verein,vereinsheim
versammlung|Vereinsversammlung|verein,versammlung
gruppe|Vereinsgruppe|verein,gruppe
gemeinschaft|Gemeinschaft|verein,gemeinschaft
veranstaltung|Vereinsveranstaltung|verein,veranstaltung
brauchtum|Brauchtum|verein,brauchtum
schuetzen|Schützenwesen|verein,schuetzen
karneval|Karneval|verein,karneval
musikverein|Musikverein|verein,musik
sportverein|Sportverein|verein,sport
buergerverein|Bürgerverein|verein,buergerverein
jugendgruppe|Jugendgruppe|verein,jugend
vorstand|Vereinsvorstand|verein,vorstand
freiwilligesengagement|Freiwilliges Engagement|verein,ehrenamt,engagement
gemeinschaftsarbeit|Gemeinschaftsarbeit|verein,arbeit,engagement
festvorbereitung|Festvorbereitung|verein,fest,vorbereitung
lokaleinitiative|Lokale Initiative|verein,initiative
helferteam|Helferteam|verein,helfer,ehrenamt`,
  gemeinde: `rathaus|Rathaus|gemeinde,rathaus
gemeinderat|Gemeinderat|gemeinde,rat
sitzungssaal|Sitzungssaal|gemeinde,sitzung
verwaltung|Verwaltung|gemeinde,verwaltung
akten|Akten und Dokumente|gemeinde,dokumente,akten
buergerbuero|Bürgerbüro|gemeinde,buergerbuero
gemeindegebaeude|Gemeindegebäude|gemeinde,gebaeude
ortsentwicklung|Ortsentwicklung|gemeinde,entwicklung
infrastruktur|Kommunale Infrastruktur|gemeinde,infrastruktur
bauplanung|Bauplanung|gemeinde,bau,planung
oeffentlichesitzung|Öffentliche Sitzung|gemeinde,sitzung,rat
kommunalesprojekt|Kommunales Projekt|gemeinde,projekt
strassenbau|Kommunaler Straßenbau|gemeinde,strassenbau
oeffentlicheeinrichtung|Öffentliche Einrichtung|gemeinde,einrichtung
buergermeisteramt|Bürgermeisteramt|gemeinde,buergermeister
politischeberatung|Kommunale Beratung|gemeinde,beratung
verwaltungsservice|Gemeindeverwaltung|gemeinde,verwaltung
baugenehmigung|Bauakte|gemeinde,bau,baugenehmigung
haushalt|Gemeindehaushalt|gemeinde,haushalt
buergerinformation|Bürgerinformation|gemeinde,information`,
  veranstaltungen: `publikum|Publikum|veranstaltung,publikum
buehne|Bühne|veranstaltung,buehne
konzert|Konzert|veranstaltung,konzert,musik
markt|Markt|veranstaltung,markt
fest|Fest|veranstaltung,fest
saal|Veranstaltungssaal|veranstaltung,saal
outdoor|Veranstaltung im Freien|veranstaltung,outdoor
kulturabend|Kulturabend|veranstaltung,kultur
familienfest|Familienfest|veranstaltung,familie
infostand|Infostand|veranstaltung,information
flohmarkt|Flohmarkt|veranstaltung,flohmarkt
ausstellung|Ausstellung|veranstaltung,ausstellung
lesung|Lesung|veranstaltung,lesung
workshop|Workshop|veranstaltung,workshop
vereinsfest|Vereinsfest|veranstaltung,verein
kirmes|Kirmes|veranstaltung,kirmes
openair|Open-Air-Veranstaltung|veranstaltung,openair
food|Gastronomie-Veranstaltung|veranstaltung,essen
adventsmarkt|Adventsmarkt|veranstaltung,advent,markt
sportevent|Sportveranstaltung|veranstaltung,sport`,
  leben: `menschen|Menschen im Ort|leben,menschen
nachbarschaft|Nachbarschaft|leben,nachbarschaft
begegnung|Begegnung|leben,begegnung
dorfleben|Dorfleben|leben,ort
senioren|Seniorenleben|leben,senioren
jugend|Jugend im Ort|leben,jugend
familienalltag|Familienalltag|leben,familie
freizeit|Freizeit|leben,freizeit
spaziergang|Spaziergang|leben,freizeit
park|Grünfläche|leben,natur
cafe|Café und Begegnung|leben,cafe
wochenmarkt|Wochenmarkt|leben,markt
gespraech|Lokales Gespräch|leben,menschen
engagement|Bürgerschaftliches Engagement|leben,engagement
gesundheit|Gesundheit im Alltag|leben,gesundheit
natur|Natur rund um den Ort|leben,natur
alltag|Alltag in der Gemeinde|leben,alltag
ortsmitte|Ortsmitte|leben,ort
community|Gemeinschaft|leben,gemeinschaft
portraet|Neutrales Menschenporträt|leben,menschen,portraet`,
  wirtschaft: `betrieb|Betrieb|wirtschaft,betrieb
buero|Büro|wirtschaft,buero
handwerk|Handwerk|wirtschaft,handwerk
produktion|Produktion|wirtschaft,produktion
gewerbe|Gewerbe|wirtschaft,gewerbe
logistik|Logistik|wirtschaft,logistik
arbeitsplatz|Arbeitsplatz|wirtschaft,arbeit
werkstatt|Werkstatt|wirtschaft,werkstatt
lager|Lager|wirtschaft,lager
lieferung|Lieferung|wirtschaft,lieferung
maschinen|Maschinen|wirtschaft,maschinen
einzelhandel|Einzelhandel|wirtschaft,handel
dienstleistung|Dienstleistung|wirtschaft,dienstleistung
gruendung|Gründung|wirtschaft,gruendung
meeting|Geschäftliches Meeting|wirtschaft,meeting
digitalisierung|Digitalisierung|wirtschaft,digital
energie|Energie und Betrieb|wirtschaft,energie
bauunternehmen|Bauunternehmen|wirtschaft,bau
landwirtschaft|Landwirtschaft|wirtschaft,landwirtschaft
industrie|Industrie|wirtschaft,industrie`,
  jobs: `buerojob|Büroarbeitsplatz|jobs,buero
handwerk|Handwerksarbeit|jobs,handwerk
werkstatt|Werkstattarbeit|jobs,werkstatt
produktion|Produktionsarbeit|jobs,produktion
verkauf|Verkauf|jobs,verkauf
pflege|Pflegeberuf|jobs,pflege
it|IT-Arbeitsplatz|jobs,it
verwaltung|Verwaltungsarbeit|jobs,verwaltung
lager|Lagerarbeit|jobs,lager
logistik|Logistikarbeit|jobs,logistik
gastronomie|Gastronomiearbeit|jobs,gastronomie
bau|Bauarbeit|jobs,bau
ausbildung|Ausbildung|jobs,ausbildung
teammeeting|Teamarbeit|jobs,team
bewerbung|Bewerbung|jobs,bewerbung
schichtarbeit|Schichtarbeit|jobs,schicht
kundenservice|Kundenservice|jobs,kundenservice
technik|Technischer Beruf|jobs,technik
fahrer|Fahrdienst|jobs,fahrer
sozialarbeit|Soziale Arbeit|jobs,sozial`,
  immobilien: `wohnhaus|Wohnhaus|immobilien,haus
wohnung|Wohnung|immobilien,wohnung
mehrfamilienhaus|Mehrfamilienhaus|immobilien,mehrfamilienhaus
schluessel|Hausschlüssel|immobilien,schluessel
neubau|Neubau|immobilien,neubau
baustelle|Wohnungsbaustelle|immobilien,baustelle
innenraum|Innenraum|immobilien,innenraum
grundriss|Grundriss|immobilien,grundriss
einfamilienhaus|Einfamilienhaus|immobilien,haus
balkon|Balkon|immobilien,balkon
kueche|Küche|immobilien,kueche
wohnzimmer|Wohnzimmer|immobilien,wohnzimmer
garten|Garten|immobilien,garten
sanierung|Sanierung|immobilien,sanierung
beratung|Immobilienberatung|immobilien,beratung
miete|Mietwohnung|immobilien,miete
eigentum|Wohneigentum|immobilien,eigentum
rohbau|Rohbau|immobilien,rohbau
fassade|Hausfassade|immobilien,fassade
haustuer|Haustür|immobilien,haustuer`,
  familie: `hochzeit|Hochzeit|familie,hochzeit
ringe|Eheringe|familie,ringe,hochzeit
familie|Familie|familie
geburt|Geburt|familie,geburt,baby
jubilaeum|Jubiläum|familie,jubilaeum
feier|Familienfeier|familie,feier
taufe|Taufe im Familienkreis|familie,taufe
geburtstag|Geburtstag|familie,geburtstag
elternkind|Eltern und Kind|familie,eltern,kind
baby|Baby|familie,baby
paar|Paar|familie,paar
blumen|Blumen zur Feier|familie,blumen
herz|Herzsymbol|familie,liebe
gratulation|Gratulation|familie,glueckwunsch
familienfest|Familienfest|familie,fest
hochzeitsstrauss|Hochzeitsstrauß|familie,hochzeit,blumen
kinder|Kinder|familie,kinder
generationen|Generationen|familie,generationen
festtafel|Festtafel|familie,feier
glueckwunsch|Glückwunsch|familie,glueckwunsch`,
  trauer: `kerze|Kerze|trauer,kerze
gedenkkerze|Gedenkkerze|trauer,gedenken,kerze
kirche|Kirchenraum in stiller Atmosphäre|trauer,kirche
friedhof|Friedhof|trauer,friedhof
blumen|Blumen der Erinnerung|trauer,blumen
erinnerung|Erinnerung|trauer,erinnerung
trauerflor|Trauerflor|trauer,flor
rose|Rose|trauer,rose
stilleslicht|Stilles Licht|trauer,licht
gedenkplatz|Gedenkplatz|trauer,gedenken
kreuz|Neutrales Kreuzsymbol|trauer,kreuz
kondolenz|Kondolenz|trauer,kondolenz
erinnerungsbuch|Buch der Erinnerung|trauer,buch,erinnerung
friedhofstor|Friedhofstor|trauer,friedhof
grablicht|Grablicht|trauer,grablicht
stille|Stille|trauer,stille
abschied|Abschied|trauer,abschied
trauerkarte|Trauerkarte|trauer,karte
kranz|Trauerkranz|trauer,kranz
abendlicht|Ruhiges Abendlicht|trauer,licht,erinnerung`,
  kultur: `theater|Theater|kultur,theater
kunst|Kunst|kultur,kunst
musik|Musik|kultur,musik
buch|Buch und Literatur|kultur,buch
kino|Film und Kino|kultur,kino
ausstellung|Ausstellung|kultur,ausstellung
buehne|Kulturbühne|kultur,buehne
museum|Museum|kultur,museum
fotografie|Fotografie|kultur,fotografie
tanz|Tanz|kultur,tanz
literatur|Literatur|kultur,literatur
konzert|Konzert|kultur,konzert
kunsthandwerk|Kunsthandwerk|kultur,handwerk
kulturhaus|Kulturhaus|kultur,haus
kreativwerkstatt|Kreativwerkstatt|kultur,kreativ
chor|Chor|kultur,chor
instrument|Musikinstrument|kultur,instrument
galerie|Galerie|kultur,galerie
lesung|Lesung|kultur,lesung
openair|Kultur unter freiem Himmel|kultur,openair`,
  schule: `klassenraum|Klassenraum|schule,klasse
schulgebaeude|Schulgebäude|schule,gebaeude
lernen|Lernen|schule,lernen
buecher|Schulbücher|schule,buecher
schulhof|Schulhof|schule,schulhof
bildung|Bildung|schule,bildung
tafel|Tafel|schule,tafel
stifte|Stifte und Schreibmaterial|schule,stifte
rucksack|Schulrucksack|schule,rucksack
naturwissenschaft|Naturwissenschaftlicher Unterricht|schule,naturwissenschaft
computerraum|Computerraum|schule,computer
grundschule|Grundschule|schule,grundschule
jugendbildung|Jugendbildung|schule,jugend
lehrkraft|Unterricht|schule,unterricht
hausaufgaben|Hausaufgaben|schule,hausaufgaben
sporthalle|Schulsporthalle|schule,sport
schulweg|Schulweg|schule,schulweg
bibliothek|Schulbibliothek|schule,bibliothek
projektarbeit|Projektarbeit|schule,projekt
abschluss|Schulabschluss|schule,abschluss`,
  kirche: `kirche|Kirche|kirche,gebaeude
kirchenraum|Kirchenraum|kirche,raum
glocke|Kirchenglocke|kirche,glocke
kreuz|Kreuz|kirche,kreuz
gottesdienst|Gottesdienst|kirche,gottesdienst
gemeinde|Kirchengemeinde|kirche,gemeinde
altar|Altar|kirche,altar
bibel|Bibel|kirche,bibel
kerze|Kerze im Kirchenraum|kirche,kerze
gebet|Gebet|kirche,gebet
kirchturm|Kirchturm|kirche,turm
orgel|Orgel|kirche,orgel
kirchenbank|Kirchenbank|kirche,bank
taufe|Taufe|kirche,taufe
konfirmation|Konfirmation|kirche,konfirmation
chor|Kirchenchor|kirche,chor
gemeindesaal|Gemeindesaal|kirche,saal
fenster|Kirchenfenster|kirche,fenster
seelsorge|Seelsorge|kirche,seelsorge
festgottesdienst|Festgottesdienst|kirche,gottesdienst,fest`,
};

const xml = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&apos;' }[c]));
const norm = (s) => String(s || '').toLocaleLowerCase('de-DE').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/ß/g, 'ss');
const sha = (s) => createHash('sha256').update(s).digest('hex');
const entries = (pool) => RAW[pool].trim().split('\n').map((line, i) => {
  const [slug, name, tagRoh = ''] = line.split('|');
  const tags = [...new Set([pool, ...tagRoh.split(',').map((x) => x.trim()).filter(Boolean), ...slug.split('-')])];
  return { id: `${pool}-${String(i + 1).padStart(2, '0')}-${slug}`, pool, slug, name, tags };
});

function svgFuer(e, index) {
  const [dunkel, akzent, hell] = PALETTEN[e.pool];
  const h = parseInt(sha(e.id).slice(0, 8), 16);
  const v = index % 5;
  const x1 = 180 + (h % 360), y1 = 140 + ((h >>> 4) % 260), r1 = 110 + ((h >>> 9) % 120);
  const x2 = 980 + ((h >>> 13) % 320), y2 = 170 + ((h >>> 17) % 320), r2 = 90 + ((h >>> 21) % 160);
  const linie = v === 0 ? `M120 680 C420 520 820 760 1480 500` : v === 1 ? `M80 500 L1520 250` : v === 2 ? `M160 710 Q800 280 1450 620` : v === 3 ? `M120 360 Q650 760 1500 430` : `M140 610 C520 240 1040 280 1480 640`;
  const kurz = LABELS[e.pool].toUpperCase();
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 900" width="1600" height="900" role="img" aria-labelledby="t d"><title id="t">${xml(e.name)}</title><desc id="d">Neutrales redaktionelles Symbolbild für ${xml(LABELS[e.pool])}: ${xml(e.name)}. Kein Foto eines konkreten Ereignisses.</desc><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="${dunkel}"/><stop offset="1" stop-color="${akzent}"/></linearGradient></defs><rect width="1600" height="900" fill="url(#g)"/><circle cx="${x1}" cy="${y1}" r="${r1}" fill="${hell}" opacity=".13"/><circle cx="${x2}" cy="${y2}" r="${r2}" fill="${hell}" opacity=".09"/><path d="${linie}" fill="none" stroke="${hell}" stroke-width="18" opacity=".18"/><rect x="90" y="90" width="1420" height="720" rx="42" fill="none" stroke="${hell}" stroke-width="3" opacity=".28"/><rect x="115" y="120" width="185" height="42" rx="21" fill="${hell}" opacity=".92"/><text x="207" y="148" text-anchor="middle" font-family="Arial,sans-serif" font-size="20" font-weight="700" fill="${dunkel}">SYMBOLBILD</text><text x="120" y="575" font-family="Arial,sans-serif" font-size="28" font-weight="700" letter-spacing="3" fill="${hell}" opacity=".78">${xml(kurz)}</text><text x="120" y="665" font-family="Arial,sans-serif" font-size="68" font-weight="800" fill="${hell}">${xml(e.name)}</text><text x="120" y="730" font-family="Arial,sans-serif" font-size="24" fill="${hell}" opacity=".82">MERZENICH AKTUELL · Redaktionelle Symbolgrafik</text></svg>`;
}

function schreibeWennAnders(pfad, inhalt, schreiben, geaendert) {
  const alt = existsSync(pfad) ? readFileSync(pfad, 'utf8') : null;
  if (alt === inhalt) return;
  geaendert.push(pfad);
  if (schreiben) { mkdirSync(dirname(pfad), { recursive: true }); writeFileSync(pfad, inhalt); }
}

export function bibliothekErzeugen(wurzel, { schreiben = true } = {}) {
  const geaendert = [];
  const alle = [];
  for (const pool of KATEGORIEN) {
    const liz = {};
    entries(pool).forEach((e, index) => {
      const datei = `${e.id}.svg`;
      const rel = `${SYMBOL_WURZEL}/${pool}/${datei}`;
      const svg = svgFuer(e, index);
      const record = {
        id: e.id, pool, src: `/${rel.replace(/^chatgpt-site\//, '')}`, alt: `${e.name} als neutrales Symbolbild`,
        credit: 'Merzenich Aktuell · redaktionelle Symbolgrafik', source: 'Merzenich Aktuell / Editorial Image System V2',
        license: 'Projektasset – Nutzung durch Merzenich Aktuell', rightsCheckedAt: RECHTE_GEPRUEFT_AM,
        tags: e.tags, width: 1600, height: 900, format: 'svg', symbol: true, checksum: sha(svg),
      };
      alle.push(record);
      liz[datei] = { id: record.id, alt: record.alt, credit: record.credit, source: record.source, license: record.license, rightsCheckedAt: record.rightsCheckedAt, tags: record.tags, checksum: record.checksum };
      schreibeWennAnders(join(wurzel, rel), svg + '\n', schreiben, geaendert);
    });
    schreibeWennAnders(join(wurzel, SYMBOL_WURZEL, pool, 'lizenzen.json'), JSON.stringify(liz, null, 2) + '\n', schreiben, geaendert);
  }
  const payload = { version: 2, generated: RECHTE_GEPRUEFT_AM, minimumPerPool: MINDEST_POOL, sourceOfTruth: 'deploy/lib-symbolbilder.mjs', images: alle };
  schreibeWennAnders(join(wurzel, BIBLIOTHEK_DATEI), JSON.stringify(payload, null, 2) + '\n', schreiben, geaendert);
  return { geaendert, library: payload };
}

export function bibliothekLesen(wurzel) {
  const pfad = join(wurzel, BIBLIOTHEK_DATEI);
  if (!existsSync(pfad)) return { version: 2, images: [] };
  return JSON.parse(readFileSync(pfad, 'utf8'));
}

export function poolLesen(wurzel, kategorie) {
  const lib = bibliothekLesen(wurzel);
  const motive = []; const uebersprungen = []; const hashes = new Set();
  for (const m of (lib.images || []).filter((x) => x.pool === kategorie)) {
    const pfad = join(wurzel, 'chatgpt-site', String(m.src || '').replace(/^\//, ''));
    const fehlt = ['id','src','alt','credit','source','license','rightsCheckedAt','tags'].filter((k) => !m[k] || (k === 'tags' && !Array.isArray(m[k])));
    if (fehlt.length) { uebersprungen.push(`${m.id || kategorie}: Metadaten fehlen: ${fehlt.join(', ')}`); continue; }
    if (!existsSync(pfad)) { uebersprungen.push(`${m.id}: Datei fehlt (${m.src})`); continue; }
    const inhalt = readFileSync(pfad, 'utf8'); const check = sha(inhalt.trimEnd());
    if (m.checksum && m.checksum !== check) { uebersprungen.push(`${m.id}: Checksumme passt nicht`); continue; }
    if (hashes.has(check)) { uebersprungen.push(`${m.id}: exakte Bilddublette im Pool`); continue; }
    hashes.add(check); motive.push(m);
  }
  return { motive, uebersprungen };
}

export function poolsLesen(wurzel) {
  const pools = {}; const hinweise = [];
  for (const k of KATEGORIEN) { const { motive, uebersprungen } = poolLesen(wurzel, k); pools[k] = motive; hinweise.push(...uebersprungen); }
  return { pools, hinweise };
}

export function kategorieFuer(a) {
  const pfad = String(a?.url || '').toLowerCase();
  const text = norm(`${a?.kicker || ''} ${a?.titel || ''} ${a?.teaser || ''} ${(a?.themen || []).map((t) => t.label || t).join(' ')} ${a?.text || ''}`);
  if (pfad.includes('/traueranzeigen/') || /\btrauer|nachruf|gedenk|verstorben|kondolenz/.test(text)) return 'trauer';

  // Starker redaktioneller Kontext schlägt allgemeine Wörter wie „Jubiläum“.
  // Sonst landet z.B. ein Feuerwehr-Jubiläum im Familien-Pool.
  if (/feuerwehr|loschgruppe|loschfahrzeug|drehleiter|tierrettung|brandbekampfung|technische hilfe/.test(text)) return 'feuerwehr';

  if (pfad.includes('/familienanzeigen/') || /hochzeit|trauung|heirat|geburt|jubilaum|familienanzeige/.test(text)) return 'familie';
  if (pfad.includes('/jobs/') || /stellenmarkt|stellenangebot|ausbildung|vollzeit|teilzeit|karriere/.test(text)) return 'jobs';
  if (pfad.includes('/immobilien/') || /immobil|kaltmiete|wohnfl|grundst|wohnung|wohnhaus/.test(text)) return 'immobilien';
  if (pfad.includes('/blaulicht/')) return 'polizei';

  // Ein klar benanntes Fest bleibt eine Veranstaltung, auch wenn der Text
  // zusätzlich Sperrungen oder Verkehrsregeln für den Veranstaltungsbereich nennt.
  if (pfad.includes('/termine/') || /\b(oldieabend|ortsfest|trodelmarkt|flohmarkt|kirmes|konzert|festwochenende)\b/.test(text)) return 'veranstaltungen';

  if (/verkehr|sperrung|baustelle|umleitung|strasse|bahn|bus|opnv|fahrbahn/.test(text)) return 'verkehr';
  if (pfad.includes('/sport/') || /fussball|kreisliga|spieltag|tabelle|sc 1919|fc golzheim/.test(text)) return 'sport';
  if (pfad.includes('/vereine/') || /verein|schutzen|karneval|fanclub|ehrenamt/.test(text)) return 'vereine';
  if (/veranstaltung|fest|konzert|markt|wochenende|kirmes/.test(text)) return 'veranstaltungen';
  if (pfad.includes('/kultur/') || /kultur|theater|ausstellung|lesung|museum|kunst/.test(text)) return 'kultur';
  if (/schule|kita|kindergarten|unterricht|bildung|schuler/.test(text)) return 'schule';
  if (/kirche|gottesdienst|pfarr|gemeindehaus|konfirmation|seelsorge/.test(text)) return 'kirche';
  if (pfad.includes('/wirtschaft/') || /unternehmen|betrieb|gewerbe|forderung|wirtschaft/.test(text)) return 'wirtschaft';
  if (pfad.includes('/rathaus/') || /gemeinde|rat|verwaltung|burgermeister|beschluss/.test(text)) return 'gemeinde';
  return 'leben';
}

export function istUnzulaessigesLogo(a) {
  const bild = a?.bild || {};
  const s = norm(`${bild.src || ''} ${bild.alt || ''} ${bild.credit || ''}`);
  if (!/logo|wappen|sc[-_ ]?1919|vereinszeichen/.test(s)) return false;
  const artikel = norm(`${a?.titel || ''} ${a?.teaser || ''} ${a?.text || ''}`);
  return !/logo|wappen|vereinsprofil|vereinsidentitat|neues vereinszeichen/.test(artikel);
}

export function brauchtSymbolbild(a) {
  if (!a?.bild?.src) return true;
  if (a.bild.symbol) return true;
  return istUnzulaessigesLogo(a);
}

export function zuordnungenLesen(wurzel) {
  const pfad = join(wurzel, ZUORDNUNGEN_DATEI);
  if (!existsSync(pfad)) return { version: 2, updatedAt: '', assignments: {} };
  try { const x = JSON.parse(readFileSync(pfad, 'utf8')); return { version: 2, updatedAt: x.updatedAt || '', assignments: x.assignments || {} }; }
  catch { return { version: 2, updatedAt: '', assignments: {} }; }
}

export function zuordnungenSchreiben(wurzel, state) {
  const pfad = join(wurzel, ZUORDNUNGEN_DATEI); mkdirSync(dirname(pfad), { recursive: true });
  const neu = JSON.stringify(state, null, 2) + '\n'; const alt = existsSync(pfad) ? readFileSync(pfad, 'utf8') : '';
  if (alt === neu) return false; writeFileSync(pfad, neu); return true;
}

const keyFuer = (a) => String(a?.url || a?.id || '');
const artikelText = (a) => norm(`${a?.titel || ''} ${a?.teaser || ''} ${a?.kicker || ''} ${(a?.themen || []).map((t) => t.label || t).join(' ')} ${a?.text || ''}`);

function treffer(m, text) { return (m.tags || []).reduce((n, t) => n + (text.includes(norm(t)) ? 1 : 0), 0); }

export function vergibSymbolbilder(artikel, pools, bestand = { assignments: {} }) {
  const alt = bestand?.assignments || {}; const assignments = { ...alt };
  const gueltig = new Map(); for (const [pool, motive] of Object.entries(pools)) for (const m of motive) gueltig.set(m.id, m);
  const usage = new Map();
  for (const [k, z] of Object.entries(assignments)) {
    const m = gueltig.get(z.imageId); if (!m || z.pool !== m.pool) { delete assignments[k]; continue; }
    usage.set(m.id, (usage.get(m.id) || 0) + 1);
  }
  let dirty = JSON.stringify(assignments) !== JSON.stringify(alt);
  const zuordnung = new Map(); const warnungen = [];
  const sortiert = [...artikel].sort((a, b) => String(a.datum || a.abgerufen || '').localeCompare(String(b.datum || b.abgerufen || '')) || keyFuer(a).localeCompare(keyFuer(b)));
  for (const a of sortiert) {
    if (!brauchtSymbolbild(a)) continue;
    const key = keyFuer(a); const pool = kategorieFuer(a); const liste = pools[pool] || [];
    if (!liste.length) { warnungen.push(`${key}: Pool ${pool} ist leer`); continue; }
    const vorhanden = assignments[key];
    if (vorhanden && vorhanden.pool === pool && gueltig.has(vorhanden.imageId)) { zuordnung.set(key, gueltig.get(vorhanden.imageId)); continue; }
    const text = artikelText(a);
    const kandidaten = [...liste].sort((x, y) => {
      const sx = treffer(x, text) * 3 - (usage.get(x.id) || 0) * 4;
      const sy = treffer(y, text) * 3 - (usage.get(y.id) || 0) * 4;
      return sy - sx || (usage.get(x.id) || 0) - (usage.get(y.id) || 0) || x.id.localeCompare(y.id);
    });
    const m = kandidaten[0]; usage.set(m.id, (usage.get(m.id) || 0) + 1);
    assignments[key] = { imageId: m.id, pool, assignedAt: new Date().toISOString(), articleId: a.id || '', articleDate: a.datum || a.abgerufen || '', tagsMatched: (m.tags || []).filter((t) => text.includes(norm(t))).slice(0, 6) };
    zuordnung.set(key, m); dirty = true;
  }
  // Echte Bilder gewinnen dauerhaft. Eine alte Symbolzuordnung wird entfernt, sobald ein echtes Bild vorhanden ist.
  for (const a of artikel) { const key = keyFuer(a); if (!brauchtSymbolbild(a) && assignments[key]) { delete assignments[key]; dirty = true; } }
  return { zuordnung, state: { version: 2, updatedAt: dirty ? new Date().toISOString() : (bestand?.updatedAt || ''), assignments }, dirty, warnungen };
}

export function selbsttest() {
  const pool = Array.from({ length: 5 }, (_, i) => ({ id:`sport-${i}`, pool:'sport', src:`/m${i}.svg`, alt:`M${i}`, credit:'Test', source:'Test', license:'Test', rightsCheckedAt:RECHTE_GEPRUEFT_AM, tags:['sport','fussball'] }));
  const artikel = Array.from({ length: 8 }, (_, i) => ({ id:`a${i}`, url:`/sport/a${i}/`, datum:`2026-09-${String(i+1).padStart(2,'0')}T10:00:00+02:00`, titel:'Fußball Spieltag' }));
  const eins = vergibSymbolbilder(artikel, { sport: pool }, { assignments:{} });
  const verschiedene = new Set([...eins.zuordnung.values()].map((m) => m.id));
  if (verschiedene.size < 5) throw new Error('Rotation verteilt Testmeldungen nicht über den Pool.');
  const altMap = Object.fromEntries(artikel.map((a) => [a.url, eins.zuordnung.get(a.url).id]));
  const zwei = vergibSymbolbilder([{ id:'neu', url:'/sport/neu/', datum:'2026-08-01T10:00:00+02:00', titel:'Fußball Training' }, ...artikel], { sport: pool }, eins.state);
  for (const a of artikel) if (zwei.zuordnung.get(a.url)?.id !== altMap[a.url]) throw new Error('Persistente Zuordnung wurde durch neue Meldung verschoben.');
  const drei = vergibSymbolbilder([...artikel].reverse(), { sport: pool }, eins.state);
  for (const a of artikel) if (drei.zuordnung.get(a.url)?.id !== altMap[a.url]) throw new Error('Reload/Build-Reihenfolge ändert eine bestehende Zuordnung.');

  // Regressionen aus dem Content-Refresh 23.09.2026:
  // Fachkontext muss generische Wörter wie „Jubiläum“ bzw. „Verkehr“ überstimmen.
  const feuerwehrJubilaeum = {
    url:'/vereine/feuerwehr-girbelsrath-10-jahre/',
    kicker:'Feuerwehr',
    titel:'Löschgruppe Girbelsrath feiert zehnjähriges Bestehen',
    teaser:'Jubiläum der Löschgruppe mit ehrenamtlichem Engagement',
  };
  if (kategorieFuer(feuerwehrJubilaeum) !== 'feuerwehr') throw new Error('Feuerwehr-Jubiläum wird nicht dem Feuerwehr-Pool zugeordnet.');

  const ortsfestMitSperrung = {
    url:'/leben/oldieabend-ortsfest-2026/',
    kicker:'Ortsfest 2026',
    titel:'Oldieabend und Ortsfest',
    teaser:'Fest mit Musik, Markt und Familienprogramm',
    text:'Für den Veranstaltungsbereich gelten Sperrungen und geänderte Verkehrsregeln.',
  };
  if (kategorieFuer(ortsfestMitSperrung) !== 'veranstaltungen') throw new Error('Ortsfest mit Verkehrshinweis wird nicht dem Veranstaltungs-Pool zugeordnet.');

  return { meldungen: artikel.length, motive: pool.length, unterschiedlicheMotive: verschiedene.size, stableAssignment: true, categoryPriority: true };
}

export function bibliothekAudit(wurzel) {
  const { pools, hinweise } = poolsLesen(wurzel); const fehler = [...hinweise]; const status = {};
  for (const k of KATEGORIEN) { const n = pools[k]?.length || 0; status[k] = n; if (n < MINDEST_POOL) fehler.push(`Pool ${k}: ${n} gültige Motive, benötigt mindestens ${MINDEST_POOL}`); }
  const sport = pools.sport || []; if (sport.some((m) => /logo|wappen/i.test(`${m.id} ${m.alt} ${(m.tags || []).join(' ')}`))) fehler.push('Sport-Pool enthält ein Vereinslogo/Wappen.');
  return { pools, status, fehler };
}
