# Merzenich Aktuell – Bedienung für die Redaktion (Übergabe an KBS)

Stand 7. Oktober 2026 · Theme 21.15.1 · Core-Plugin 1.25.1

Die Seite läuft als WordPress auf **merzenich-aktuell.de** (IONOS). Alles Redaktionelle passiert im Backend unter **merzenich-aktuell.de/wp-admin**, linkes Menü **Merzenich Aktuell**. Diese Anleitung beschreibt die täglichen Handgriffe. Technik und Versionen stehen in `WORDPRESS-LIEFERUNG.md`, die Einrichtung in `README-INSTALLATION.md`.

## Auf einen Blick

| Aufgabe | Wo | Wie oft |
|---|---|---|
| Überblick: was heute zu tun ist | **Dashboard**, Kachel „Heute zu tun“ | täglich |
| Neue Meldungen und Termine prüfen und freigeben | Merzenich Aktuell → **Freigaben** | täglich |
| Einsendungen von Lesern, Vereinen, Anzeigenkunden | **Eingang** | täglich |
| Kommentare freigeben | Kommentare → **Sammelfreigabe** | täglich |
| Ergebnis, nächstes Spiel und Tabelle des SC 1919 | Merzenich Aktuell → **Sport** | nach jedem Spiel |
| Startseite anordnen, Aufmacher festsetzen | Merzenich Aktuell → **Startseite & Ressorts** | bei Bedarf |
| Stellen und Immobilien | **Stellen**, **Immobilien** (kommen von selbst) | wöchentlich ansehen |
| Fotos in die Bildpools | Medien → **Bildpools** | bei Bedarf |
| Werbung schalten | **Werbung** und Werbung → **Werbeplätze** | bei Buchung |
| Zahlen zu Aufrufen und Anzeigen | Merzenich Aktuell → **Statistik** | wöchentlich |

## 1. Freigaben (täglich)

Neue Meldungen und Termine aus der Recherche kommen stündlich automatisch als **Entwurf** ins Backend (Abgleich). Öffentlich wird nichts ohne Freigabe.

1. **Merzenich Aktuell → Freigaben** öffnen. Die Zahl am Menüpunkt zeigt, wie viel wartet.
2. Je Eintrag die verlinkte **Quelle** öffnen und Datum, Uhrzeit, Ort und Namen vergleichen.
3. Bei Meldungen die **Relevanz** (1 bis 10) wählen und ankreuzen, ob sie auf die **Startseite** darf.
4. **Freigeben** klicken. Der Eintrag ist sofort online, Startseite, Ressort, Feed und Google-News-Sitemap ziehen nach.
5. Was nicht mehr aktuell oder falsch ist: **nicht** freigeben. Es bleibt Entwurf und schadet nicht.

Tipp: Überschriften mit „heute“ oder „morgen“ vor der Freigabe auf das Datum umstellen („am 4. Oktober“), wenn der Tag schon vorbei ist.

## 2. Eingang: Einsendungen

Alles, was über die Formulare kommt (Meldung senden, Termin melden, Anzeige aufgeben, Foto des Tages, Partner-Antrag), steht unter **Eingang**. Eine Einsendung wird nie automatisch veröffentlicht.

- **Meldung oder Termin:** prüfen, daraus einen Beitrag bzw. Termin anlegen, dann wie oben freigeben.
- **Foto des Tages:** In der Einsendung im Kasten „Foto des Tages“ ein Datum wählen. An diesem Tag zeigt die Startseite das Foto (auf dem Computer und Tablet). Nur mit bestätigten Bildrechten.
- **Anzeigen (Immobilie, Stelle, Traueranzeige, Familienanzeige):** Angaben prüfen, beim Anzeigenkunden Preis und Laufzeit klären, dann als Eintrag im passenden Bereich anlegen. „Anzeige endet“ setzen; danach verschwindet sie von selbst aus allen Listen.
- **Partner-Antrag** (Unternehmen, Vereine): Ein Zugang wird nicht automatisch angelegt. Nach Prüfung unter Benutzer → Neu mit der passenden Rolle anlegen.

## 3. Kommentare

Jeder Kommentar wartet auf Freigabe. Unter **Kommentare → Sammelfreigabe** stehen alle wartenden, jeder ist vorausgewählt. Problematische abwählen, dann in einem Schritt freigeben. Wer eine E-Mail-Adresse angegeben hat, bekommt nach der Freigabe eine Nachricht. Es gelten die Kommentarrichtlinien auf `/kommentarregeln/`.

## 4. Sport (nach jedem Spiel)

**Merzenich Aktuell → Sport**: letztes Spiel mit Ergebnis und Link zum Spielbericht, nächstes Spiel mit Datum und Uhrzeit, Tabelle der Kreisliga A, Quelle (FUSSBALL.DE) und Prüfzeitpunkt. Speichern. Die Seite `/sport/` (Spielstand-Ecke, Sportmodul, Tabelle) zeigt den neuen Stand sofort.

Ist ein Spiel seit drei Stunden vorbei und kein Ergebnis eingetragen, erscheint ein Hinweis im Dashboard. Nur belegte Werte eintragen; ein automatischer Abruf von FUSSBALL.DE findet nicht statt.

Darunter zeigt `/sport/` unter „Wer spielt wo“ alle **aktiven** Vereine der Gemeinde mit ihren Mannschaften, Liga, Platz und Punkten (Fußball, Tischtennis, Tennis, Billard, Schach). Vereine ohne Mannschaft im Spielbetrieb stehen nur im Vereinsverzeichnis. Den Tabellenstand pflegt HK Growth Operator im Repository (`data/sport-aktiv.json`); die Seite holt ihn einmal täglich. Fehlt eine Mannschaft, genügt ein Hinweis mit Link zur Tabelle.

## 5. Startseite und Ressorts anordnen

Die Startseite füllt sich selbst aus den freigegebenen Meldungen (Aktualität, Ortsbezug, Relevanz). Unter **Merzenich Aktuell → Startseite & Ressorts** lässt sich das von Hand ändern. Das geht auch auf der Seite selbst über „Bearbeiten“ in der schwarzen Leiste oben.

- **Ziehen:** Karte am Griff **⠿** packen und auf eine andere Karte ziehen. Die beiden tauschen die Plätze. Mit der Maus geht auch die ganze Karte. Zieht man an den oberen oder unteren Rand, scrollt die Seite mit. Esc bricht ab.
- **Meldung einsetzen:** Auf einem Platz „Ersetzen“ wählen und eine Meldung aussuchen.
  - Jede veröffentlichte Meldung geht. Ist sie „Nur in der Rubrik“ und soll auf die Startseite, fragt das Board kurz nach und gibt sie für die Startseite frei.
  - Entwürfe und wartende Meldungen lassen sich **vormerken**. Der Platz bleibt reserviert; sobald die Meldung unter Freigaben freigegeben ist, erscheint sie dort.
  - Der Filter „Stand“ zeigt nur freigegebene, offene, Rubrik-Meldungen oder Entwürfe.
- **Hervorhebung bis:** Ein fest gesetzter Beitrag bleibt bis zu diesem Datum, danach rückt die automatische Auswahl nach.

Sport erscheint auf Wunsch von KBS nicht auf der Startseite, sondern unter `/sport/`.

Von selbst aktuell, ohne Pflege: Terminspalte und „Merzenich jetzt“ (nächster Termin, letzter Feuerwehreinsatz), Wetter, Foto des Tages, Umkreis, Stellen- und Immobilienmarkt.

**Beiträge-Liste:** Über der Liste filtern nach Startseite (freigegeben, offen, nur Rubrik), Relevanz und Bild (ohne Bild, Bildrechte offen). Ein Klick auf die Spalte „Relevanz“ sortiert. Beiträge ohne Relevanz stehen dann am Ende.

## 6. Werbung

Jeder Werbeplatz zeigt eine gekennzeichnete **Musteranzeige**, solange dort nichts gebucht ist. So sieht ein Kunde, wo seine Anzeige erscheint.

1. **Werbung → Neu:** Titel, Bild mit Alternativtext, „Sponsor / Firma“, Ziel-URL, Start, Ende und „Platzierung“ wählen, „Schaltung aktiv“ ankreuzen, veröffentlichen. Unter Werbung → **Werbeplätze** müssen Werbung insgesamt und der gewählte Platz eingeschaltet sein.
2. Ab dem Startdatum ersetzt die echte Anzeige die Musteranzeige an diesem Platz, nach dem Ende kommt die Musteranzeige zurück.
3. **Bezahlte Beiträge** (Tipp, Sponsoring, Unternehmensbeitrag) sind automatisch als „Anzeige“ gekennzeichnet und nennen den Auftraggeber statt der Redaktion als Absender.
4. **Unternehmensprofile** nur mit schriftlicher Einwilligung des Betriebs. Bis drei echte Profile da sind, füllen gekennzeichnete Musterprofile die Spalte unter `/unternehmen/` und `/betriebe/`.

Klicks und Einblendungen je Anzeige stehen unter **Statistik**.

## 7. Stellen und Immobilien

Die Stellen und Immobilien aus der täglichen Marktprüfung stehen als Einträge unter **Stellen** und **Immobilien**. Jeder Eintrag nennt seine Quelle und das Prüfdatum. Sie werden täglich mit dem Abgleich übernommen; „Probelauf“ und „Übernehmen“ unter Merzenich Aktuell → Abgleich stoßen das sofort an.

- Ein Eintrag endet von selbst: Stellen 30 Tage, Immobilien 35 Tage nach der letzten Prüfung. Fällt ein Angebot aus der Quelle, endet es am selben Tag. Gelöscht wird nichts.
- Eigene Anzeigen von Kunden (aus dem Eingang) stehen getrennt darunter und werden nicht angefasst.
- Immobilienportale (immowelt, ImmoScout24) sperren automatische Prüfungen. Deren Angebote tragen deshalb ihr letztes Prüfdatum und laufen nach 35 Tagen aus, wenn sie niemand neu prüft.
- Trauer- und Familienanzeigen gibt es nur mit Einwilligung der Familie. Sie werden über `/anzeigen/aufgeben/` aufgegeben.

## 8. Bilder und Bildpools

Jede Meldung braucht ein Bild mit geklärten Rechten. Fehlt ein eigenes Foto, nimmt die Redaktion ein Symbolfoto aus dem passenden **Bildpool** (Medien → Bildpools zeigt den Stand). Selbstgemachte Grafiken gibt es nicht mehr: ohne Foto bleibt die Karte ohne Bild.

- In der Mediathek oben „Alle Bildpools“ wählen, um nur einen Pool zu sehen. Das geht auch im Fenster „Beitragsbild festlegen“.
- Neue Fotos: hochladen, Urheber, Quelle und Nutzungsgrundlage eintragen, Pool eintragen, Rechteprüfung auf „Geprüft“ stellen. Was fehlt, steht in `FOTOWUNSCHLISTE.md`.
- Fotos mit „Abgelehnt – nicht verwenden“ stehen in keinem Pool mehr und werden nicht benutzt.

## 9. Partner- und Vereinskonten

Die Konten der Partner und Vereine hatten Platzhalter-Adressen der Agentur. Diese Adressen sind geleert; die Partner tragen unter **Profil** ihre eigene E-Mail-Adresse ein und sehen bis dahin einen Hinweis. Name, Rolle, Passwort und Vereinszuordnung bleiben. Unter Benutzer → Vereinszugänge zeigt ein gelber Kasten, falls wieder Platzhalter-Adressen auftauchen.

## 10. Was die Seite selbst erledigt

- **Abgleich** (Merzenich Aktuell → Abgleich): holt stündlich neue Meldungen und Termine als Entwurf, einmal täglich Stellen, Immobilien und den Tabellenstand der Sportvereine. „Jetzt abgleichen“ stößt ihn sofort an. Der Schalter „Neue Meldungen: Sofort veröffentlichen“ bleibt aus, damit jede Meldung durch die Freigabe geht.
- **Rechtstexte und Redaktionsseiten** (Impressum, Datenschutz, Über uns, Werben usw.): pflegt das Plugin. Wurde eine Seite von Hand geändert, überschreibt es sie nicht und zeigt einen Hinweis zum Abgleichen.
- **Google und Teilen:** Titel, Beschreibung, Vorschaubild (16:9, 4:3, 1:1), News-Sitemap `/news-sitemap.xml`, Feed `/feed/` und Benachrichtigungen ans Handy laufen automatisch.
- **Sicherheit:** Die Seite ist nur noch verschlüsselt erreichbar (`http://` leitet auf `https://` um).

## 11. Was nur der Betreiber erledigen kann

- [ ] Passwort des Kontos „HK Growth“ ändern und eigene Konten für die Redaktion anlegen (Benutzer → Neu).
- [ ] Mailversand einrichten, damit Formular- und Kommentar-Mails zuverlässig ankommen: Postfach im IONOS-Kundenkonto anlegen, unter **Merzenich Aktuell → Mailversand** die E-Mail-Adresse als Benutzer und das Postfach-Passwort eintragen (Server `smtp.ionos.de` und Port 587 sind vorbelegt), „eingeschaltet“ ankreuzen, speichern, **Test-Mail schicken**. Danach in der Datenschutzerklärung ergänzen, dass die Mails über den Mailserver der IONOS SE gehen.
- [ ] Postfach **redaktion@merzenich-aktuell.de** einrichten und auf der Seite **Merzenich Aktuell** (Dashboard, Feld „Redaktions-E-Mail“) eintragen. Dorthin gehen dann alle Formulare und Partner-Anträge; bis dahin an die Admin-E-Mail.
- [ ] Vertrag zur Auftragsverarbeitung (AV-Vertrag) mit IONOS im IONOS-Kundenkonto abschließen.
- [ ] Google Search Console: News-Sitemap `https://merzenich-aktuell.de/news-sitemap.xml` einreichen. Google Publisher Center: Publikation anlegen (Schritte in `GOOGLE-NEWS.md`).
- [ ] WhatsApp-Kanal anlegen und den Link auf der Seite `/whatsapp/` eintragen.
- [ ] Echte Werbekunden und Unternehmensprofile gewinnen (nur mit schriftlicher Einwilligung).
- [ ] Bildfreigaben der Vereine einholen (Vereinsfotos nur mit Nutzungserlaubnis).
- [ ] Fotos nach `FOTOWUNSCHLISTE.md` von ChatGPT besorgen lassen und in die Bildpools laden (vor allem Polizei, Verkehr, Rettung).
- [ ] Nach dem Spiel am 09.10. (Freialdenhoven – Merzenich) das Ergebnis unter Sport eintragen.
- [ ] iPhone: Wer die Seite schon auf dem Home-Bildschirm hat, sieht das neue „M“-Symbol erst, wenn er das Symbol neu anlegt oder die Website-Daten löscht.

## 12. Hilfe bei Problemen

| Was | Was tun |
|---|---|
| Neue Meldungen kommen nicht an | Merzenich Aktuell → Abgleich → „Jetzt abgleichen“. Die Seite zeigt den letzten Lauf und Fehler. |
| Eine veröffentlichte Meldung ist nach dem Speichern verschwunden | Sie steht wieder in den **Freigaben**: Einigen älteren Meldungen aus der Startphase fehlen die Prüfhäkchen (Datum, Ort, Human Review), und die Veröffentlichungssperre hält sie beim Speichern an. Dort prüfen und freigeben. Das Datum bleibt erhalten. |
| Eine Seite zeigt Altes | Ein paar Minuten warten (Zwischenspeicher), dann im Browser neu laden. |
| Formular-Mails kommen nicht an | Merzenich Aktuell → Mailversand: Stand prüfen und „Test-Mail schicken“; die Seite zeigt den Fehler (etwa falsches Passwort). Spam-Ordner prüfen. Einsendungen stehen trotzdem im Eingang. |
| Etwas sieht falsch aus | Bildschirmfoto mit Adresse an HK Growth Operator. |
