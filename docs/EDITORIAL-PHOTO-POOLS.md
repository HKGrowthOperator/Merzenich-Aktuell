# Editorial Photo Pools

Seit 24.09.2026 ergänzt Merzenich Aktuell das bestehende Editorial Image System V2 um echte, frei lizenzierte Foto-Pools.

## Zielpools

Aktuell, Blaulicht, Polizei, Feuerwehr, Brand, Sport/Fußball, Termine/Veranstaltungen, Vereine, Leben, Wirtschaft/Handel/Strukturwandel, Tipp/Freizeit und Menschen – jeweils mindestens 20 Fotos.

## Quellen- und Rechteprinzip

Die automatische Materialisierung verwendet ausschließlich Wikimedia Commons. Die Suche priorisiert Merzenich, Kreis Düren und NRW und fällt erst danach auf allgemeine Deutschland-Motive zurück. Pro Bild werden Urheber, Quelldatei, Lizenz, Lizenz-URL, Download-URL und Prüftag im Manifest dokumentiert. Auf der Website bleiben diese Fotos ausdrücklich **Symbolbilder** und behaupten nicht, das konkrete Ereignis zu zeigen.

Manifest: `chatgpt-site/data/editorial-images/editorial-photo-pools.json`

Assets: `chatgpt-site/assets/editorial-pools/<pool>/`

Importer: `node deploy/import-editorial-photos.mjs`

## Hauptpools und Detailpools (Stand 28.09.2026)

Es gibt zwei Arten von Pools:

- **Hauptpools** (`ALLE_KATEGORIEN` in `deploy/lib-symbolbilder.mjs`, z. B. aktuell, blaulicht, feuerwehr, polizei, sport): mindestens 20 gesichtete Motive je Pool. `bibliothekAudit()` meldet jede Unterschreitung als Fehler.
- **Detailpools** (`EREIGNIS_KATEGORIEN`: technik, rettung, unfall, flaeche, tennisdetail, digitaldetail, tanzdetail, naturdetail, vereinsdetail, kirchedetail): kleine semantische Spezialpools für einzelne Bildklassen, etwa Ölspur, Brandmeldeanlage, Tennisplatz oder Kirchenfenster. Sie haben bewusst keine Mindestgröße. Sie füllen nur die Bildklassen, deren Motivregel genau ihr Motiv verlangt. Findet sich kein freies Motiv, greift die nächste Stufe: Elternpool der Klasse, danach die gekennzeichnete Ortsansicht.

`editorial-images.json` nennt beide Listen (`minimumGiltFuer`, `detailPools`), damit niemand „jeder Pool hat mindestens 20“ daraus liest. Stand heute: naturdetail 1, vereinsdetail 1, tanzdetail 2, unfall 3, tennisdetail 4, digitaldetail 4, kirchedetail 4, rettung 12. Neue Detailfotos kommen über `deploy/import-editorial-photos.mjs` mit Sichtung hinzu (Bildregeln: keine erkennbaren Gesichter, keine Kennzeichen, keine Hausnummern oder Firmenschilder).
