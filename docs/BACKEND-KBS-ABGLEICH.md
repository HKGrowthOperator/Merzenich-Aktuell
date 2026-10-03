# Backend: Abgleich mit den KBS-Anforderungen

Stand 03.10.2026 · Core-Plugin 1.19.0 · Theme 21.9.0
Grundlage: „Vollständige Backend-Anforderungen aus den bisherigen KBS-Besprechungen“ (23.09., 01.10. und technische Festlegungen 10.–12.09.).

Legende: **vorhanden** = war schon umgesetzt · **neu** = mit 1.19.0 umgesetzt · **offen** = bewusst noch nicht oder braucht eine Entscheidung.

| Nr | Anforderung | Stand | Wo im Backend |
|---|---|---|---|
| 1 | Drei Ebenen (KBS-Admin, Redaktion, externe Organisationen), keine Umgehung der Freigabe | vorhanden | Administrator, Redakteur (WordPress-Rollen), Partnerrollen; Einreichungen immer „Zur Prüfung“ |
| 2 | Benutzergruppen | vorhanden, **neu**: Werbepartner | Benutzer → Partner-Zugänge / Vereinszugänge. Rollen: Polizei-, Feuerwehr-, Rathaus-, Sport-, Vereins-, Unternehmens-, Immobilien-Partner, **Werbepartner** |
| 2 | Externe sehen nur eigene Inhalte | vorhanden, **neu**: auch Medien | Partner sehen in Mediathek, Medien-Dialog und Schnittstelle nur eigene Uploads (vorher alle, auch Trauer- und Familienfotos aus dem Eingang) |
| 2 | Alle Vereine, nicht nur Fußball | vorhanden | 63 Vereine im Verzeichnis, 58 Profile, je Verein ein Zugang; Partner-Antrag jetzt „Sportverein (alle Sportarten)“ |
| 3.1 | Status Entwurf → eingereicht → in Prüfung → Freigabe/Änderungen → veröffentlichen/planen → archivieren | vorhanden, **neu**: Archivieren bedienbar | Merzenich Aktuell → Freigaben; Beiträge: Zeilenaktion „Archivieren“ / „Wieder veröffentlichen“, Mehrfachaktion „Archivieren / zurückziehen“ |
| 3.1 | Nicht Freigegebenes nie öffentlich (Suche, RSS, Sitemaps, Schnittstelle) | vorhanden | eigene Status nicht öffentlich, Sitemaps und Feeds gefiltert |
| 3.2 | Zwei getrennte Freigaben: Veröffentlichung und Startseite | **neu** | Eigenes Feld „Startseiten-Freigabe“ (ja/nein, wer, wann). Ohne Entscheidung nur Rubrik. Box „Startseite“ im Beitrag (ohne Vorauswahl), Freigabeseite, Mehrfachaktionen „Startseite: freigeben / nicht freigeben“. Bestehende Meldungen behalten ihren Stand. |
| 3.2 | Relevanz 1–10 | vorhanden | Freigabeseite, Box „Startseite“, Spalte in der Beitragsliste |
| 3.2 | Priorität 1–10 | **neu** | eigenes Feld, ordnet innerhalb derselben Relevanz (höchstens ±0,45 Punkte) |
| 4 | Artikelfelder: Titel, Unterzeile, Inhalt, Autor, Ressort, Ort, Quelle, Quellen-URL, Quelldatum, Veröffentlichung, Beitragsbild, Bildnachweis, Bildrechte, Status | vorhanden | Editor, Kasten „Redaktion & Quelle“ (jetzt deutsch beschriftet) |
| 4 | Organisation (einreichende Stelle) | **neu** | bei Partner-Einreichungen automatisch (Verein bzw. Zugang), sonst Redaktion |
| 4 | Aktualisierungsdatum (öffentlich) | **neu** | „Aktualisiert am“, erscheint auf der Meldung und in den Daten für Suchmaschinen |
| 4 | Hervorhebung und „Hervorhebung bis“ | **neu**: läuft wirklich ab | fester Platz (Box „Startseite“); „Hervorhebung bis“ beendet ihn automatisch, Hinweis im Board |
| 4 | Keine JSON-Dateien, Shortcodes oder Code nötig | vorhanden | alles über Kästen, Freigabeseite und Board |
| 5 | Bildrechte vor jeder Einreichung bestätigen, nicht vorausgewählt | vorhanden | Editor (Block und klassisch), alle Formulare; Server prüft |
| 5 | Je Medium: Fotograf, Quelle, Nutzungsgrundlage, Nachweis, Beschreibung, Alt-Text, Beitrag | vorhanden | Mediathek, Medienfelder |
| 5 | Rechteprüfung je Medium (redaktioneller Status) | **neu** | „Bildrechte: redaktionelle Prüfung“ (offen / geprüft / abgelehnt, mit Protokoll) für jedes Bild; vorher nur Sportbilder für Admins. Nachweis geht beim Setzen des Beitragsbilds an den Beitrag. |
| 6 | Vereinsverzeichnis, Profile, Vereinsredakteure, Meldungen, Sport, Fotos, zentrale Verwaltung | vorhanden | Vereine, Benutzer → Vereinszugänge, Merzenich Aktuell → Sport |
| 7 | Unternehmenskanäle (5–10), Profile, eigene Inhalte, Werbung, Freigabe | vorhanden | Unternehmen, Unternehmens-Partner, Werbekontingent am Profil |
| 8 | Werbung global an, Plätze einzeln, Musteranzeigen, Freigabe Pflicht, `hero_clubs_expanded_right` | vorhanden | Werbung → Werbeplätze (17 Plätze) |
| 8 | Mehrere Banner / Kampagnen | **neu** | „Weitere Plätze (Kampagne)“: ein Motiv, eine Freigabe, mehrere Plätze |
| 9 | Geführte Formulare: Immobilien, Trauer (Telefon und E-Mail Pflicht), Familie, Werbung, Meldung, Termin | vorhanden | Formulare auf der Website, Merzenich Aktuell → Eingang |
| 9 | Stellenanzeigen | **neu** | eigenes Formular (Ausbildung, Voll-/Teilzeit, Minijob, Praktikum) |
| 9 | Eingang zentral bearbeiten | **neu** | Filter nach Art; „Als Entwurf übernehmen“ legt Immobilie, Stelle, Trauer-/Familienanzeige, Meldung, Termin, Werbemittel usw. als Entwurf an. Kontaktdaten bleiben intern. Freigabeseite auch für Immobilien, Stellen, Trauer und Familie. |
| 10 | Kommentare: nie automatisch, einzeln/mehrere/alle genehmigen, abwählen, Mail nach Freigabe | vorhanden | Kommentare → Sammelfreigabe |
| 11 | Statistik: Website, Redaktion, Vereine, Werbung; Zeiträume, Diagramme; nur echte Zahlen | vorhanden, **neu**: Schutz vor künstlichen Zahlen | Merzenich Aktuell → Statistik. Zähl-Schnittstellen jetzt je Besucher begrenzt. |
| 12 | Quelle getrennt von Veröffentlichung, SEO pflegbar, Vorschau geschützt, Verlauf | vorhanden, **neu**: SEO-Titel und -Beschreibung je Meldung; Verlauf auch für Stellen, Trauer, Familie | Kasten „Redaktion & Quelle“, Kasten „Verlauf“ |
| 13 | Einladungsfunktion vorerst weglassen | **neu**: entfernt | Zugänge ohne E-Mail anlegen, Passwort im Profil |
| 13 | Kein Werbefrei-Abo, keine automatische Anzeigenfreigabe, Musteranzeigen bleiben, keine Selbstveröffentlichung | vorhanden | |
| — | Bildpools | **neu** | Medien → Bildpools: 23 Pools, 308 gesichtete Commons-Fotos mit Nachweis; Symbolbild aus dem Pool für Meldungen ohne eigenes Bild |

## Offen

- **Bildpools ergänzen:** Polizei hat 13 statt 20 Fotos; geprüft sind bei Blaulicht 12 von 20, bei Verkehr 10 von 20; Detailpools Technik, Rettung, Unfall teils ungeprüft, Flächenbrand leer. Nur mit echten, lizenzierten Motiven ergänzen und sichten (docs/POOLFOTOS-PRUEFUNG.md).
- **Märkte leer:** Immobilien, Stellen, Trauer- und Familienanzeigen haben in WordPress noch keine Einträge (die Startseite zeigt Angebote der statischen Seite).
- **Alte Vorlage:** Listen und Detailseiten von Terminen, Märkten, Trauer, Familie und Tipps nutzen noch die ältere Darstellung (funktioniert mobil, sieht aber anders aus).
- **Externe Konten:** Externe immer als Partnerrolle anlegen, nie als WordPress-„Autor“ (der dürfte selbst veröffentlichen).
- **Kampagnen und Kontingent:** Eine Kampagne über mehrere Plätze zählt als eine Anzeige im Kontingent.
- **Eigene Rollen „KBS“ und „Redaktion“:** genutzt werden Administrator und Redakteur von WordPress; eigene Rollennamen nur bei Bedarf.
