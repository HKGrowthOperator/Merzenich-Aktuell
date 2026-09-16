<?php
/**
 * Fußballdaten des SC 1919 Merzenich. Erzeugt aus ma9/content/daten/fussball.json — nicht von Hand pflegen.
 * Quelle: FUSSBALL.DE, abgerufen am 2026-09-04.
 *
 * @package MerzenichAktuell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Liefert Tabelle, Spielplan und Stand als Array. */
function ma8_fussball() {
	static $data = null;
	if ( null === $data ) {
		$data = json_decode( <<<'JSON'
{
  "club": "SC 1919 Merzenich",
  "logo": "assets/img/sc-1919-merzenich-logo.webp",
  "logoCredit": "SC 1919 Merzenich",
  "verband": "Fußball-Verband Mittelrhein",
  "kreis": "Kreis Düren",
  "saison": "2026/2027",
  "abgerufen": "2026-09-04",
  "vereinUrl": "https://www.fussball.de/verein/sc-merzenich-mittelrhein/-/id/00ES8GN9A0000054VV0AG08LVUPGND5I",
  "teamUrl": "https://www.fussball.de/mannschaft/sc-1919-merzenich-sc-merzenich-mittelrhein/-/saison/2627/team-id/011MIEKMN4000000VTVG0001VTR8C1K7",
  "teamsGesamt": 17,
  "teamsJunioren": 14,
  "teamsSenioren": 3,
  "liga": "Kreisliga A",
  "stand": {
    "platz": 1,
    "spiele": 2,
    "siege": 2,
    "unentschieden": 0,
    "niederlagen": 0,
    "tore": "7:2",
    "diff": 5,
    "punkte": 6
  },
  "tabelle": [
    {
      "pl": 1,
      "team": "SC 1919 Merzenich",
      "sp": 2,
      "g": 2,
      "u": 0,
      "v": 0,
      "tore": "7:2",
      "diff": 5,
      "pkt": 6,
      "self": true
    },
    {
      "pl": 2,
      "team": "SC Jülich 1910/97/Hoengen",
      "sp": 2,
      "g": 1,
      "u": 1,
      "v": 0,
      "tore": "6:1",
      "diff": 5,
      "pkt": 4
    },
    {
      "pl": 3,
      "team": "Hambacher Spielverein 1",
      "sp": 2,
      "g": 1,
      "u": 1,
      "v": 0,
      "tore": "4:2",
      "diff": 2,
      "pkt": 4
    },
    {
      "pl": 4,
      "team": "BC Oberzier",
      "sp": 2,
      "g": 1,
      "u": 1,
      "v": 0,
      "tore": "2:0",
      "diff": 2,
      "pkt": 4
    },
    {
      "pl": 5,
      "team": "SV Siersdorf",
      "sp": 2,
      "g": 1,
      "u": 1,
      "v": 0,
      "tore": "3:2",
      "diff": 1,
      "pkt": 4
    },
    {
      "pl": 6,
      "team": "Jugendsport Wenau",
      "sp": 2,
      "g": 1,
      "u": 0,
      "v": 1,
      "tore": "6:5",
      "diff": 1,
      "pkt": 3
    },
    {
      "pl": 7,
      "team": "Spvg. SW 1896 Düren",
      "sp": 2,
      "g": 1,
      "u": 0,
      "v": 1,
      "tore": "6:6",
      "diff": 0,
      "pkt": 3
    },
    {
      "pl": 8,
      "team": "SV Grün-Weiß Welldorf-Güsten",
      "sp": 2,
      "g": 1,
      "u": 0,
      "v": 1,
      "tore": "5:6",
      "diff": -1,
      "pkt": 3
    },
    {
      "pl": 9,
      "team": "Borussia Freialdenhoven",
      "sp": 2,
      "g": 1,
      "u": 0,
      "v": 1,
      "tore": "7:10",
      "diff": -3,
      "pkt": 3
    },
    {
      "pl": 10,
      "team": "FC Golzheim",
      "sp": 1,
      "g": 0,
      "u": 1,
      "v": 0,
      "tore": "0:0",
      "diff": 0,
      "pkt": 1
    },
    {
      "pl": 11,
      "team": "SC Salingia 08 Barmen",
      "sp": 2,
      "g": 0,
      "u": 1,
      "v": 1,
      "tore": "1:4",
      "diff": -3,
      "pkt": 1
    },
    {
      "pl": 12,
      "team": "SV SW Huchem-Stammeln",
      "sp": 0,
      "g": 0,
      "u": 0,
      "v": 0,
      "tore": "0:0",
      "diff": 0,
      "pkt": 0
    },
    {
      "pl": 13,
      "team": "FC Düren 77",
      "sp": 1,
      "g": 0,
      "u": 0,
      "v": 1,
      "tore": "1:2",
      "diff": -1,
      "pkt": 0
    },
    {
      "pl": 14,
      "team": "JSV Frenz",
      "sp": 1,
      "g": 0,
      "u": 0,
      "v": 1,
      "tore": "4:6",
      "diff": -2,
      "pkt": 0
    },
    {
      "pl": 15,
      "team": "SG Nörvenich-Hochkirchen",
      "sp": 1,
      "g": 0,
      "u": 0,
      "v": 1,
      "tore": "0:2",
      "diff": -2,
      "pkt": 0
    },
    {
      "pl": 16,
      "team": "SV Viktoria Koslar",
      "sp": 2,
      "g": 0,
      "u": 0,
      "v": 2,
      "tore": "1:5",
      "diff": -4,
      "pkt": 0
    }
  ],
  "spielplan": [
    {
      "start": "2026-09-06T15:00:00+02:00",
      "gegner": "FC Golzheim",
      "heim": false,
      "wettbewerb": "Kreisliga A"
    },
    {
      "start": "2026-09-13T15:00:00+02:00",
      "gegner": "BC Oberzier",
      "heim": true,
      "wettbewerb": "Kreisliga A"
    },
    {
      "start": "2026-09-18T19:30:00+02:00",
      "gegner": "SC Jülich 1910/97/Hoengen",
      "heim": false,
      "wettbewerb": "Kreisliga A"
    },
    {
      "start": "2026-09-27T15:00:00+02:00",
      "gegner": "SG Nörvenich-Hochkirchen",
      "heim": true,
      "wettbewerb": "Kreisliga A"
    },
    {
      "start": "2026-10-02T19:00:00+02:00",
      "gegner": "SC Salingia 08 Barmen",
      "heim": false,
      "wettbewerb": "Kreisliga A"
    },
    {
      "start": "2026-10-09T19:30:00+02:00",
      "gegner": "Borussia Freialdenhoven",
      "heim": false,
      "wettbewerb": "Kreisliga A"
    },
    {
      "start": "2026-10-18T15:00:00+02:00",
      "gegner": "Jugendsport Wenau",
      "heim": true,
      "wettbewerb": "Kreisliga A"
    },
    {
      "start": "2026-10-25T15:00:00+01:00",
      "gegner": "Hambacher Spielverein 1",
      "heim": false,
      "wettbewerb": "Kreisliga A"
    },
    {
      "start": "2026-11-08T15:00:00+01:00",
      "gegner": "FC Düren 77",
      "heim": true,
      "wettbewerb": "Kreisliga A"
    },
    {
      "start": "2026-11-15T15:00:00+01:00",
      "gegner": "JSV Frenz",
      "heim": false,
      "wettbewerb": "Kreisliga A"
    }
  ],
  "gespielt": [
    {
      "start": "2026-08-23T17:30:00+02:00",
      "gegner": "SC Alemannia Lendersdorf",
      "heim": true,
      "wettbewerb": "Kreispokal"
    },
    {
      "start": "2026-08-30T15:00:00+02:00",
      "gegner": "Spvg. SW 1896 Düren",
      "heim": true,
      "wettbewerb": "Kreisliga A"
    },
    {
      "start": "2026-09-02T19:30:00+02:00",
      "gegner": "SV Grün-Weiß Welldorf-Güsten",
      "heim": false,
      "wettbewerb": "Kreisliga A"
    }
  ],
  "reserven": [
    {
      "name": "SC 1919 Merzenich II",
      "liga": "Kreisliga C, Staffel 2",
      "platz": 13,
      "punkte": 0,
      "tore": "5:10",
      "naechstes": {
        "start": "2026-09-06T15:00:00+02:00",
        "gegner": "BSV Wissersheim",
        "heim": false
      },
      "url": "https://www.fussball.de/mannschaft/sc-1919-merzenich-2-sc-merzenich-mittelrhein/-/saison/2627/team-id/02BDRS8RDG000000VS5489B2VU21J0QC"
    },
    {
      "name": "SC 1919 Merzenich III",
      "liga": "Kreisliga C, Staffel 1",
      "platz": 9,
      "punkte": 3,
      "tore": "4:13",
      "naechstes": {
        "start": "2026-09-06T12:00:00+02:00",
        "gegner": "Rasensport Tetz",
        "heim": true
      },
      "url": "https://www.fussball.de/mannschaft/sc-1919-merzenich-3-sc-merzenich-mittelrhein/-/saison/2627/team-id/0316V67EV8000000VS5489BSVSCPI5U4"
    }
  ]
}
JSON
		, true );
	}
	return $data;
}
