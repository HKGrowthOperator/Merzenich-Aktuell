# Aktueller Recherche-Einstieg

Jüngstes Dossier: [03.11.2026, Claude-Nachmittagslauf](dossiers/2026-11-03-claude-nachmittags.md), Vergleichsbasis [ChatGPT 03.11. mittags](dossiers/2026-11-03-mittags.md) und [Claude 03.11. morgens](dossiers/2026-11-03-claude-morgens.md). Offene Themen: [offen.md](offen.md).

**Nachlauf 03.11. abends (Auftrag Luis Kummer: breiter recherchieren, länger und eigenständig schreiben):** Standardtext „Karneval in Merzenich 2026/27“ (861 Wörter, live 18:56 Uhr), 6 neue Karnevalstermine von den Vereinsseiten, Bücher- und Spielemarkt bestätigt. Neu: `node qa/vereinsseiten.mjs` (Phase 1, Schritt 3a), Instagram/Facebook über ChatGPT, Kurzmeldung ab 04.11. 250 bis 450 Wörter, strengere Eigenformulierung. Offen: Orte der KG Jonge vom Berg. Commit `a1dc22b`.

**Umgesetzt am 03.11. nachmittags:** 1 neue Kurzmeldung („Solarpark an der A4 in Merzenich: Bauausschuss berät über 36 Hektar Photovoltaik mit Batteriespeicher“, Vorlage 144/2026, live 14:54 Uhr), Nachtrag Martinszüge (Golzheim 17.30/18 Uhr laut Förderverein), Pluriversum 17.11. bestätigt, Heimat-Info-Kalender 6 Einträge alle bei uns. Mindesttakt 03.11.: 3 Meldungen. Commits `1df9f20`, `bd7317d`. Eil-Prüfung 12:41 Uhr ohne Fund.

**Umgesetzt am 03.11. morgens:** 1 neue Kurzmeldung („Jugendgremium Merzenich tagt am 4. November“, Rathaus, live 09:20 Uhr), 21 Termine bis 16.11. an der Quelle bestätigt, Heimat-Info-Kalender unverändert. Aufmacher bleibt die Folgemeldung zum Verkehrstod auf der L 264 (Stufe 1 vom 02.11.). Commits `7bd157f`, `7fb625f`.

**Systembefunde (erledigt 03.11. vormittags):** Feuerwehr-Automatik datiert Meldungen jetzt mit der Anlagezeit statt Einsatztag 12 Uhr; 140/26, 141/26 und 144/26 werden per `datumKorrektur` korrigiert; Freigabe eines Abgleich-Entwurfs setzt die Freigabezeit (Plugin 1.43.0, Commit `e2da1cf`). SEO-KEYWORDS Regel 5 mit SCHREIBREGELN 0a abgestimmt (`515af69`). `/umgebung/` kommt mit dem Selbst-Update (1.42.0 wurde nie gebaut, 1.43.0 enthält es). SEO/GEO-Bericht mit Anleitung: [docs/SEO-GEO-BERICHT-2026-11-03.md](../SEO-GEO-BERICHT-2026-11-03.md). gemeinde-merzenich.de antwortete dieser Sitzung mit HTTP 428, vhs-rur-eifel.de mit HTTP 404.

**Sichtbarkeit (03.11. mittags):** Google-Probe „merzenich“: wir fehlen unter „Alle“ und „News“. Plan: [docs/SICHTBARKEITSPLAN-2026-11.md](../SICHTBARKEITSPLAN-2026-11.md). Ab sofort Pflicht: „Merzenich“ in jedem Titel (`qa/redaktion-lauf.mjs` prüft ab 03.11.); Blaulicht mit Einordnung aus dem Archiv; die beiden L-264-Titel sind umgestellt.

**Automatisch online (03.11. mittags, Entscheidung Luis Kummer):** Feuerwehr-Automatik und Heimat-Info-Termine ohne Freigabe, Relevanz automatisch, „Neu in Merzenich“ oben in „Nachrichten aus Merzenich“ (Theme 21.26.0 live 11:18 Uhr). Feuerwehr 144/26 seit 11:13 Uhr online und in der Nachrichten-Sitemap. Eil-Prüfung alle zwei Stunden (06:40 bis 20:40 Uhr). Freigabe nur noch bei rechtlichem Risiko (CLAUDE-LAUF 3.8).

**Offen:** Jugendgremium-Update nach der Sitzung am 04.11., Bauausschuss 12.11. (Solarpark, Poolplatz, Bau-Turbo, Vorlage 92/2026) als Folgemeldung, Bücher- und Spielemarkt 8.11. an einer Quelle prüfen (gemeinde-merzenich.de HTTP 428 für Claude), VHS-Vortrag 10.11. (HTTP 404), Wochenvorschau 9.–15.11. mit den Martinszügen, Bildpool Solarenergie, Unfall, Rettung, Tischtennis.

[Register](register.json) · [Regeln](README.md) · [Archiv](archiv.md)
