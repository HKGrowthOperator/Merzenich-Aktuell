# Partner-Zugänge – Rollen, Freigabe, Werbung

Stand: Core-Plugin 1.6.0, Theme 20.5.0 · Grundlage: Entscheidung KBS vom 26.09.2026

Polizei, Feuerwehr, Rathaus, Makler, der Fußballverein (Sport liegt beim SC 1919), Vereine und Unternehmen schreiben ihre Beiträge selbst. Die Redaktion (Administratoren) schreibt nicht mit, sie **gibt nur frei**. Kein Partner kann selbst veröffentlichen.

## 1. Rollen und Rechte

| Rolle (WordPress) | Für | Darf anlegen | Landet in |
|---|---|---|---|
| Polizei-Partner (`ma_polizei_partner`) | Polizei / Kreispolizeibehörde | Beiträge | Rubrik Blaulicht |
| Feuerwehr-Partner (`ma_feuerwehr_partner`) | Feuerwehr / Löschgruppe | Beiträge | Rubrik Blaulicht |
| Rathaus-Partner (`ma_rathaus_partner`) | Gemeinde / Rathaus | Beiträge | Rubrik Rathaus & Politik |
| Sport-Partner (`ma_sport_partner`) | Fußballverein (SC 1919) | Beiträge | Rubrik Sport |
| Vereins-Partner (`ma_vereine_partner`) | Verein / Initiative | Beiträge | Rubrik Vereine |
| Unternehmens-Partner (`ma_wirtschaft_partner`) | Unternehmen | Beiträge, Unternehmensprofil, Tipp, Werbeanzeige | Eigener Unternehmenskanal `/unternehmen/<firma>/` (seit 1.28.0: Zugang mit Profil verknüpft, Beiträge automatisch im Kanal), `/unternehmen/`, Menü „Unternehmen“, `/betriebe/`, Werbeplätze |
| Immobilien-Partner (`ma_immobilien_partner`) | Makler / Immobilien-Verantwortliche | Immobilien-Inserate | `/immobilien/` |
| Blaulicht-Partner (alt) | frühere gemeinsame Rolle Polizei/Feuerwehr | Beiträge | Blaulicht – nur für bestehende Zugänge, wird nicht mehr neu vergeben |

Für alle Partner gilt:

- Sie sehen im Backend nur ihre eigenen Inhalte und nur die Inhaltsarten ihrer Rolle.
- Statt zu veröffentlichen, reichen sie zur Prüfung ein (Status *Ausstehend*). Auch ein Veröffentlichungsversuch, etwa über die Programmierschnittstelle, landet als *Ausstehend*.
- Veröffentlichte Beiträge können sie nicht mehr ändern oder löschen.
- Redaktionelle Prüfhaken (Quelle, Datum, Ort, Bildrechte, Human Review, Anzeige aktiv, Freigabe) können sie nicht setzen; sie werden beim Speichern entfernt.
- Die Rubrik wird nach der Rolle gesetzt, nicht nach der Auswahl des Partners.

### Hilfe für Partner (seit 1.32.0)

Jeder Partner sieht im Dashboard zuerst den Kasten „So geht's“ und links den Menüpunkt **Hilfe**: Erklärvideo, Anleitung als PDF (`assets/hilfe/anleitung-zugang.pdf`), häufige Fragen, Kontakt und den Knopf für die geführte Tour im Editor. Veröffentlichte Beiträge ändern Partner über „Änderung vorschlagen“; die veröffentlichte Fassung bleibt, bis die Redaktion den Vorschlag freigibt. Das Video wird einmal in die Mediathek geladen und unter der Option `ma_hilfe_video` (Medien-ID) eingetragen.

## 2. Zugang anlegen (Administration)

1. Anfrage prüfen. Anträge kommen über das Formular `[ma_partner_antrag]` per E-Mail an die Administrationsadresse (Einstellungen → Allgemein → „E-Mail-Adresse der Administration“). Ein Konto wird dabei **nicht** automatisch angelegt.
2. Im Backend **Benutzer → Partner-Zugänge** öffnen.
3. Name / Organisation, E-Mail-Adresse und Rolle eintragen, bei Unternehmen das Profil wählen, „Zugang anlegen (ohne E-Mail)“.
4. WordPress schickt **keine** E-Mail. Benutzername ist der Teil der E-Mail-Adresse vor dem @, das Passwort sind 24 Zufallszeichen; es wird im Profil neu gesetzt und persönlich weitergegeben (nie per Mail). Die Einladung verschickt die Redaktion selbst, Vorlage `docs/vorlagen/einladung-partnerzugang.md`, für die Unternehmen vom Oktober 2026 `docs/vorlagen/mails-firmen-2026-10.md`.
5. Bei Unternehmen: Werbekontingent am Benutzerprofil prüfen (siehe Abschnitt 4).

Zugang sperren: unter **Benutzer** die Rolle auf „Kein Rang für diese Website“ setzen oder den Benutzer löschen (Inhalte dabei einem Redaktionskonto zuordnen).

### Formular für Anträge einbinden

Eine Seite anlegen (Vorschlag: „Partner-Zugang“) und den Shortcode einfügen:

```
[ma_partner_antrag]
```

Mit Vorauswahl der Art: `[ma_partner_antrag typ="verein"]` – möglich sind `polizei`, `feuerwehr`, `rathaus`, `sport`, `verein`, `immobilien`, `unternehmen`. Das Formular fragt Organisation, Art, Ansprechperson, E-Mail, Telefon (freiwillig), Nachricht (freiwillig) und die Einwilligung zur Datenverarbeitung ab; es hat Nonce- und Spam-Schutz (verstecktes Feld, Mindestzeit). Die Unternehmensübersicht `/betriebe/` enthält das Formular bereits (Art „Unternehmen“ vorausgewählt).

## 3. Freigabe-Ablauf

```
Partner schreibt  →  reicht zur Prüfung ein  →  Status Ausstehend
                                                    │
                    Redaktion bekommt E-Mail „Neue Partner-Einreichung zur Freigabe“
                                                    │
                  ┌─────────────────────────────────┴───────────────────────────┐
          Freigeben: „Veröffentlichen“                        Ablehnen: Kasten „Partner-Einreichung ablehnen“
                  │                                            (Begründung + Haken) oder Papierkorb
   Partner erhält „Ihr Beitrag ist freigegeben“ + Link          Partner erhält „Ihr Beitrag wurde nicht freigegeben“
                                                               mit der eingetragenen Begründung
```

- **Übersicht offener Einreichungen:** Benutzer → Partner-Zugänge (Tabelle „Offene Partner-Einreichungen“) und Merzenich Aktuell → Aktualitätscheck.
- **Freigeben:** Beitrag öffnen, prüfen, redaktionelle Haken setzen (bei Nachrichten: Quelle, Datum, Ort, Human Review, bei Bild zusätzlich Bildrechte), „Veröffentlichen“. Fehlt ein Pflichthaken, bleibt der Beitrag Entwurf und der Editor nennt den fehlenden Haken.
- **Ablehnen:** Im Editor rechts den Kasten „Partner-Einreichung ablehnen“ nutzen: Begründung eintragen, Haken „Beim Speichern ablehnen und Partner benachrichtigen“ setzen, speichern. Der Beitrag bekommt den Status *Abgelehnt*. Der Partner kann ihn überarbeiten und erneut einreichen; dann steht er wieder unter *Ausstehend*.
- **Papierkorb:** Wird eine ausstehende Einreichung in den Papierkorb gelegt, geht ebenfalls eine Ablehnungs-Mail raus – mit der Begründung aus dem Kasten, falls eingetragen, sonst mit dem Hinweis, dass keine Begründung eingetragen wurde.
- Die E-Mails an Partner tragen als Antwortadresse die Redaktionsadresse (Option `ma_editorial_email`, sonst die Administrationsadresse).

**Voraussetzung:** Der Server muss E-Mails versenden können (SMTP-Plugin oder Mailversand des Hosters). Ohne funktionierenden Versand erfahren Partner nichts von Freigabe oder Ablehnung.

## 4. Werbung für Unternehmen

Unternehmen buchen ihre Anzeigen selbst – immer mit Freigabe der Redaktion.

- Anzeige anlegen unter **Werbung → Werbemittel hinzufügen**: Titel, Bannerbild (Beitragsbild), Platzierung, Sponsor/Firma, Ziel-URL, Start, Ende. Einreichen schickt sie zur Prüfung.
- Die Redaktion prüft, setzt **„Schaltung aktiv“** und veröffentlicht. Erst dann läuft die Anzeige.
- **Kontingent:** Jeder Unternehmens-Zugang hat ein Kontingent aus gleichzeitig laufenden bzw. eingereichten Anzeigen und erlaubten Plätzen. Voreinstellung: **1 Anzeige, alle Werbebänder der Startseite (1 bis 6)**. Einstellen kann das nur ein Administrator am **Benutzerprofil** des Unternehmens (Abschnitt „Werbekontingent“). Für die Firmen aus `data/firmen-entwuerfe.json` setzt sich das Kontingent beim Verknüpfen des Zugangs mit dem Profil selbst (seit 1.35.0): Merzlicht und AJ Sports **5 Anzeigen auf allen Werbeplätzen**, Almera Clean, KBS und ASS Elektrotechnik **0** (nur Eintrag unter „Unternehmen“).
- Reicht ein Unternehmen über das Kontingent hinaus oder auf einem nicht freigeschalteten Platz ein, bleibt die Anzeige **Entwurf**; der Editor erklärt den Grund. Abgelaufene und pausierte Anzeigen zählen nicht zum Kontingent.
- **Rotation:** Laufen mehrere Anzeigen auf demselben Platz, wechseln sie sich ab. Anzeigen dürfen sich auf der Seite wiederholen. Die Reihenfolge beginnt bei der höchsten „Priorität“.
- **Plätze:** Werbebänder 1 bis 6 auf der Startseite (je eines nach zwei Meldungen), rechte Spalte oben/Mitte, Tipp · Sponsoring, Ende der Meldungsliste, Artikel im Text, Artikel-Seitenspalte, Kopf-Billboard. Jeder Platz muss unter **Merzenich Aktuell → Werbung** eingeschaltet sein, dazu der globale Schalter.
- Jede Anzeige ist sichtbar als „Anzeige“ gekennzeichnet; Links sind als gesponsert markiert.
- **Preise** werden nirgends auf der Seite genannt – immer „Preis auf Anfrage“.

### Unternehmensprofil (`/betriebe/`)

Unternehmen können ein Profil einreichen (Unternehmen → Hinzufügen): Branche, Adresse, Telefon, Website, Öffnungszeiten, Ortsteil (Merzenich, Golzheim, Girbelsrath, Morschenich, Bürgewald), Quelle und Stand. **Pflicht ist der Haken „Einwilligung“**: Ohne ihn wird das Profil weder eingereicht noch veröffentlicht, der Editor zeigt den Grund. Die Übersicht zeigt bis zu acht Plätze; sind weniger Profile veröffentlicht, stehen gekennzeichnete Felder „Ihr Unternehmen hier – Platz anfragen“ darin. Es werden keine Firmen erfunden.

## 5. Fotoerlaubnis

Hat ein Partner-Beitrag ein Bild, muss der Partner im Kasten „Bildherkunft“ bzw. „Redaktion & Quelle“ den Haken **Fotoerlaubnis** setzen:

> Wir haben die Fotos selbst aufgenommen oder dürfen sie veröffentlichen. Kennzeichen, erkennbare Gesichter und Hausnummern sind nicht zu sehen oder unkenntlich gemacht.

Ohne diesen Haken bleibt der Beitrag Entwurf und der Partner sieht einen Hinweis. Die Redaktion sieht, wer die Erklärung wann abgegeben hat, schaut das Foto trotzdem an und setzt danach selbst „Bildrechte geprüft“.

## 6. Kommentare

Kommentare sind vorab moderiert: Jeder neue Kommentar wartet auf Freigabe. Unter **Kommentare → Sammelfreigabe** sind alle wartenden Kommentare vorausgewählt; problematische abwählen, dann „Alle ausgewählten genehmigen“ (oder „Alle wartenden genehmigen“ über alle Seiten). Nach der Freigabe erhält der Verfasser eine E-Mail. Partner haben keinen Zugriff auf die Kommentarverwaltung.

## 7. Startseite ohne Sport

Die Startseite zeigt keine Sportmeldungen. Rubrik Sport ist immer Sport; Blaulicht, Rathaus und Wirtschaft nie. In den übrigen Rubriken erkennt ein Muster Sportbezug in Titel, Auszug und Schlagworten (z. B. Fußball, Kreisliga, SC 1919, Fanclub, Sportplatz, Tennis, Turnverein). Sportmeldungen bleiben unter `/sport/` sichtbar.

## 8. Was der Auftraggeber einrichten muss

- E-Mail-Versand (SMTP) einrichten und mit einer Test-Einreichung prüfen.
- Administrations- und ggf. Redaktionsadresse (`ma_editorial_email`) prüfen.
- Seite mit `[ma_partner_antrag]` anlegen und verlinken; Datenschutzerklärung als Datenschutzseite in WordPress hinterlegen (sie wird im Formular verlinkt).
- Werbung global und die gewünschten Plätze unter Merzenich Aktuell → Werbung einschalten.
- Kontingente der Unternehmen am jeweiligen Benutzerprofil setzen, falls von der Voreinstellung abweichend.
- Nach dem Update einmal **Einstellungen → Permalinks → Speichern** aufrufen, damit `/betriebe/` sicher erreichbar ist.

Vorlagen für die Kommunikation: `docs/vorlagen/rundmail-vereine.md`, `docs/vorlagen/einladung-partnerzugang.md`, `docs/vorlagen/antwort-antrag.md`.
